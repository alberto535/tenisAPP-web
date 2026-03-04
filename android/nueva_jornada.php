<?php
header('Content-Type: application/json');

$conexion = new mysqli("localhost", "root", "", "l&r");
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => "Error de conexión: " . $conexion->connect_error]);
    exit;
}

$liga = isset($_GET['liga']) ? $conexion->real_escape_string($_GET['liga']) : null;
if (!$liga) {
    echo json_encode(['success' => false, 'message' => 'Liga no especificada.']);
    exit;
}

// ===================== VALIDACIONES =====================
$stmtJornada = $conexion->prepare("SELECT fecha_fin FROM jornada WHERE liga = ? AND estado = 'cerrada' ORDER BY id DESC LIMIT 1");
$stmtJornada->bind_param("s", $liga);
$stmtJornada->execute();
$stmtJornada->bind_result($fechaFin);
$stmtJornada->fetch();
$stmtJornada->close();

if ($fechaFin !== null && !(strtotime($fechaFin) >= strtotime("-3 days"))) {
    echo json_encode(['success' => false, 'message' => 'Debes esperar al menos 3 días desde la última jornada cerrada.']);
    exit;
}

$stmtPartidos = $conexion->prepare("SELECT COUNT(*) FROM jornada_partidos WHERE id_jornada IN (SELECT id FROM jornada WHERE liga = ? AND fecha_fin = '0000-00-00')");
$stmtPartidos->bind_param("s", $liga);
$stmtPartidos->execute();
$stmtPartidos->bind_result($numPartidos);
$stmtPartidos->fetch();
$stmtPartidos->close();

if ($numPartidos > 0) {
    echo json_encode(['success' => false, 'message' => 'No puedes generar una nueva jornada mientras haya partidos pendientes.']);
    exit;
}

// ===================== GENERACIÓN DE JORNADA =====================

$sqlUltimaJornada = "SELECT MAX(id) as ultima FROM jornada WHERE liga = '$liga'";
$resultUltimaJornada = $conexion->query($sqlUltimaJornada);
$filaUltimaJornada = $resultUltimaJornada->fetch_assoc();
$id_jornada = is_numeric($filaUltimaJornada['ultima']) ? (int)$filaUltimaJornada['ultima'] + 1 : 1;

$fecha_actual = date('Y-m-d');

if ($id_jornada === 1) {
    $sqlCheck = "SELECT COUNT(*) as total FROM jornada WHERE liga = '$liga'";
    $resCheck = $conexion->query($sqlCheck);
    $filaCheck = $resCheck->fetch_assoc();

    if ($filaCheck['total'] == 0) {
        $sqlInsertJornada = "INSERT INTO jornada (id, fecha_inicio, estado, liga) VALUES (1, '$fecha_actual', 'activo', '$liga')";
        if (!$conexion->query($sqlInsertJornada)) {
            echo json_encode(['success' => false, 'message' => 'Error al insertar la jornada inicial: ' . $conexion->error]);
            exit;
        }
    }
}

// Crear la nueva jornada
$sqlNuevaJornada = "INSERT INTO jornada (id, fecha_inicio, estado, liga) VALUES ($id_jornada, '$fecha_actual', 'activo', '$liga')";
if (!$conexion->query($sqlNuevaJornada)) {
    echo json_encode(['success' => false, 'message' => 'Error al crear la nueva jornada: ' . $conexion->error]);
    exit;
}

// Obtener participantes por división
$sql = "
    SELECT u.nombre AS nombre_usuario, u.apellidos, c.division
    FROM clasificacion c
    JOIN usuarios u ON c.correo = u.correo
    WHERE c.liga = '$liga'
    ORDER BY c.division, c.puntuaje DESC
";

$resultado = $conexion->query($sql);
$participantes = [];
while ($fila = $resultado->fetch_assoc()) {
    $nombreCompleto = $fila['nombre_usuario'] . ' ' . $fila['apellidos'];
    $participantes[$fila['division']][] = $nombreCompleto;
}

$descansos = obtenerDescansos($conexion, $liga);
$ultimosLocales = obtenerUltimosLocales($conexion, $liga);

foreach ($participantes as $division => $jugadores) {
    $noEmparejados = $jugadores;

    if (count($jugadores) % 2 != 0) {
        $jugadorDescanso = obtenerJugadorParaDescanso($jugadores, $descansos);
        $descansos[] = $jugadorDescanso;
        unset($noEmparejados[array_search($jugadorDescanso, $noEmparejados)]);
        $noEmparejados = array_values($noEmparejados);
        registrarDescanso($conexion, $jugadorDescanso, $id_jornada, $fecha_actual, $liga);
    }

    $totalJugadores = count($noEmparejados);
    for ($i = 0; $i < $totalJugadores - 1; $i += 2) {
        $jugador1 = $noEmparejados[$i];
        $jugador2 = $noEmparejados[$i + 1] ?? null;
        if ($jugador2) {
            $localPrimero = determinarLocalidad($jugador1, $jugador2, $ultimosLocales);
            registrarPartido($conexion, $localPrimero[0], $localPrimero[1], $division, $id_jornada, $fecha_actual, $liga);
        }
    }
}

$conexion->close();
echo json_encode(['success' => true, 'message' => 'Nueva jornada generada correctamente.']);

// ========================= FUNCIONES =========================

function obtenerDescansos($conexion, $liga = null) {
    $sql = "SELECT nombre_participantes FROM jornada_partidos WHERE nombre_participantes LIKE '%descansa%'";
    if ($liga) {
        $sql .= " AND liga = '$liga'";
    }
    $resultado = $conexion->query($sql);

    $descansos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $descansos[] = str_replace(" descansa", "", $fila['nombre_participantes']);
    }
    return $descansos;
}

function obtenerUltimosLocales($conexion, $liga = null) {
    $sql = "SELECT nombre_participantes FROM jornada_partidos";
    if ($liga) {
        $sql .= " WHERE liga = '$liga'";
    }
    $resultado = $conexion->query($sql);

    $ultimosLocales = [];
    while ($fila = $resultado->fetch_assoc()) {
        $partes = explode(" vs ", $fila['nombre_participantes']);
        if (count($partes) == 2) {
            $ultimosLocales[$partes[0]] = true;
            $ultimosLocales[$partes[1]] = false;
        }
    }
    return $ultimosLocales;
}

function determinarLocalidad($jugador1, $jugador2, $ultimosLocales) {
    if (isset($ultimosLocales[$jugador1]) && !$ultimosLocales[$jugador1]) {
        return [$jugador1, $jugador2];
    } elseif (isset($ultimosLocales[$jugador2]) && !$ultimosLocales[$jugador2]) {
        return [$jugador2, $jugador1];
    }
    return [$jugador1, $jugador2];
}

function obtenerJugadorParaDescanso($jugadores, $descansos) {
    foreach ($jugadores as $jugador) {
        if (!in_array($jugador, $descansos)) {
            return $jugador;
        }
    }
    return $jugadores[array_rand($jugadores)];
}

function registrarDescanso($conexion, $jugador, $id_jornada, $fecha, $liga = null) {
    $sql = "INSERT INTO jornada_partidos (id_jornada, nombre_participantes, fecha_jornada";
    if ($liga) {
        $sql .= ", liga";
    }
    $sql .= ") VALUES ('$id_jornada', '$jugador descansa', '$fecha'";
    if ($liga) {
        $sql .= ", '$liga'";
    }
    $sql .= ")";
    $conexion->query($sql);
}

function registrarPartido($conexion, $jugador1, $jugador2, $division, $id_jornada, $fecha, $liga = null) {
    $enfrentamiento = "$jugador1 vs $jugador2";
    $sql = "INSERT INTO jornada_partidos (id_jornada, nombre_participantes, division, fecha_jornada";
    if ($liga) {
        $sql .= ", liga";
    }
    $sql .= ") VALUES ('$id_jornada', '$enfrentamiento', '$division', '$fecha'";
    if ($liga) {
        $sql .= ", '$liga'";
    }
    $sql .= ")";
    $conexion->query($sql);
}
?>
