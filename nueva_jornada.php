<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 20-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge la liga por el URL. Comprueba si existe jornada inicial, si no al crea. 
    Se establecen los partidos que se van a jugar en la nueva jornada, los jugadores que descansan , en caso de ser impares, y se
    establece que los jugadores que hayan jugado como local, en su anterior partido, jueguen como visitantes. 
*/

$conexion = new mysqli("localhost", "root", "", "l&r");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el nombre de la liga desde la URL (si aplica)
$liga = isset($_GET['liga']) ? $conexion->real_escape_string($_GET['liga']) : null;

// Obtener el último ID de jornada existente
$sqlUltimaJornada = "SELECT MAX(id) as ultima FROM jornada WHERE liga = '$liga'";
$resultUltimaJornada = $conexion->query($sqlUltimaJornada);
$filaUltimaJornada = $resultUltimaJornada->fetch_assoc();
$id_jornada = is_numeric($filaUltimaJornada['ultima']) ? (int)$filaUltimaJornada['ultima'] : 1;

$fecha_actual = date('Y-m-d');

if ($id_jornada === 1) {
    // Verificar si no existe ninguna jornada en la tabla para esta liga
    $sqlCheck = "SELECT COUNT(*) as total FROM jornada WHERE liga = '$liga'";
    $resCheck = $conexion->query($sqlCheck);
    $filaCheck = $resCheck->fetch_assoc();

    if ($filaCheck['total'] == 0) {
        // Insertar la jornada inicial
        $sqlInsertJornada = "INSERT INTO jornada (id, fecha_inicio, estado, liga) VALUES (1, '$fecha_actual', 'activo', '$liga')";
        if (!$conexion->query($sqlInsertJornada)) {
            die("Error al insertar la jornada inicial: " . $conexion->error);
        }
    }
}

// Obtener participantes con nombre completo, ordenados por división y puntaje
$sql = "
    SELECT u.nombre AS nombre_usuario, u.apellidos, c.division 
    FROM clasificacion c 
    JOIN usuarios u ON c.correo = u.correo
";
if ($liga) {
    $sql .= " WHERE c.liga = '$liga'";
}
$sql .= " ORDER BY c.division, c.puntuaje DESC";

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


echo "<script>alert('Jornada iniciada'); window.location.href='administrar-ligas-activas.php';</script>";

?>
