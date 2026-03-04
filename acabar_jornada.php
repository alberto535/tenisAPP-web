<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 19-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge la liga desde el URL. En este archivo, se calculan los puntos que han de sumarse
    a cada jugador por los partidos jugados esta jornada. Se calcula tambien el M_Buchholz y Buchholz. Se cierra la jornada actual, se 
    establece fecha de fin, se genera una nueva jornada y se marcan los partidos como procesados.
*/

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ✅ Asegurar que se recibe la liga por POST
if (!isset($_POST['liga']) || empty($_POST['liga'])) {
    die("Liga no especificada.");
}
$liga = $conn->real_escape_string($_POST['liga']);

// Obtener todos los partidos aceptados de esta liga
$sql = "SELECT resultado, nombre_participantes FROM partidos WHERE estado = 'aceptado' AND liga = '$liga'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $participantes = explode(" vs ", $row['nombre_participantes']);
        $resultado = $row['resultado'];

        if (count($participantes) == 2) {
            $jugador1 = trim($participantes[0]);
            $jugador2 = trim($participantes[1]);

            $puntos_jugador1 = 0;
            $puntos_jugador2 = 0;

            if ($resultado == "2-0") {
                $puntos_jugador1 = 4;
                $puntos_jugador2 = 1;
            } elseif ($resultado == "2-1") {
                $puntos_jugador1 = 3;
                $puntos_jugador2 = 2;
            } elseif ($resultado == "1-2") {
                $puntos_jugador1 = 2;
                $puntos_jugador2 = 3;
            } elseif ($resultado == "0-2") {
                $puntos_jugador1 = 1;
                $puntos_jugador2 = 4;
            }

            $conn->query("UPDATE clasificacion SET puntuaje = puntuaje + $puntos_jugador1 WHERE nombre = '$jugador1'");
            $conn->query("UPDATE clasificacion SET puntuaje = puntuaje + $puntos_jugador2 WHERE nombre = '$jugador2'");
        }
    }
} else {
    // Si no hay partidos aceptados, dar 0 puntos a los jugadores de esta liga
    $sql_jugadores = "SELECT DISTINCT nombre_participantes FROM jornada_partidos WHERE liga = '$liga'";
    $result_jugadores = $conn->query($sql_jugadores);

    while ($row = $result_jugadores->fetch_assoc()) {
        $participantes = explode(" vs ", $row['nombre_participantes']);
        foreach ($participantes as $jugador) {
            $jugador = trim(str_replace(" descansa", "", $jugador));
            $conn->query("UPDATE clasificacion SET puntuaje = puntuaje + 0 WHERE nombre = '$jugador'");
        }
    }
}

// ✅ Calcular Buchholz
$sqlClasificacion = "SELECT nombre FROM clasificacion";
$resultClasificacion = $conn->query($sqlClasificacion);

while ($row = $resultClasificacion->fetch_assoc()) {
    $jugador = $row['nombre'];
    
    $sqlOponentes = "SELECT nombre_participantes FROM partidos 
                     WHERE estado IN ('aceptado', 'procesado') 
                     AND liga = '$liga' 
                     AND (nombre_participantes LIKE '$jugador vs %' OR nombre_participantes LIKE '% vs $jugador')";
    $resultOponentes = $conn->query($sqlOponentes);

    $buchholz = 0;
    $oponentesPuntos = [];

    while ($partido = $resultOponentes->fetch_assoc()) {
        $participantes = explode(" vs ", $partido['nombre_participantes']);
        $oponente = ($participantes[0] == $jugador) ? $participantes[1] : $participantes[0];

        $sqlPuntosOponente = "SELECT puntuaje FROM clasificacion WHERE nombre = '$oponente'";
        $resultPuntosOponente = $conn->query($sqlPuntosOponente);

        if ($resultPuntosOponente->num_rows > 0) {
            $puntos = $resultPuntosOponente->fetch_assoc()['puntuaje'] ?? 0;
            $buchholz += $puntos;
            $oponentesPuntos[] = $puntos;
        }
    }

    $conn->query("UPDATE clasificacion SET buch = $buchholz WHERE nombre = '$jugador'");

    if (count($oponentesPuntos) > 2) {
        sort($oponentesPuntos);
        array_shift($oponentesPuntos);
        array_pop($oponentesPuntos);
        $m_buch = array_sum($oponentesPuntos);
    } else {
        $m_buch = $buchholz;
    }

    $conn->query("UPDATE clasificacion SET `m-buch` = $m_buch WHERE nombre = '$jugador'");
}

// ✅ Cerrar la jornada activa de esta liga
$conn->query("UPDATE jornada SET fecha_fin = NOW(), estado = 'cerrada' WHERE estado = 'activo' AND liga = '$liga'");

// ✅ Obtener ID siguiente dentro de la misma liga
$sqlUltimaJornada = "SELECT MAX(id) AS ultimo_id FROM jornada WHERE liga = '$liga'";
$resultUltima = $conn->query($sqlUltimaJornada);
$rowUltima = $resultUltima->fetch_assoc();
$nuevoId = ($rowUltima['ultimo_id'] ?? 0) + 1;

// ✅ Crear nueva jornada activa
$conn->query("INSERT INTO jornada (id, fecha_inicio, fecha_fin, estado, liga) 
              VALUES ($nuevoId, NOW(), '0000-00-00', 'activo', '$liga')");

// ✅ Marcar partidos como procesados
$conn->query("UPDATE partidos SET estado = 'procesado' WHERE estado = 'aceptado' AND liga = '$liga'");

echo "<script>alert('Jornada finalizada y nueva jornada iniciada para la liga $liga.'); window.location.href='administrar-ligas-activas.php';</script>";

$conn->close();
?>
