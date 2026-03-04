<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "l&r");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit;
}

$correo = $_GET['correo'] ?? '';

if (!empty($correo)) {
    // Obtener división, liga y nombre completo del usuario
    $sql_usuario = "SELECT division, liga, nombre, apellidos FROM usuarios WHERE correo = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("s", $correo);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $usuario = $result_usuario->fetch_assoc();
    $stmt_usuario->close();

    if ($usuario) {
        // Concatenar nombre y apellidos
        $nombreCompletoUsuario = trim($usuario['nombre'] . ' ' . $usuario['apellidos']);

        // Obtener todos los partidos de la jornada actual
        $sql_partidos = "SELECT jp.nombre_participantes
                         FROM jornada_partidos AS jp
                         JOIN jornada AS j ON jp.id_jornada = j.id
                         WHERE jp.liga = ?
                           AND j.fecha_fin = '0000-00-00'
                           AND j.estado = 'activo'";
        $stmt_partidos = $conn->prepare($sql_partidos);
        $stmt_partidos->bind_param("s", $usuario['liga']);
        $stmt_partidos->execute();
        $result_partidos = $stmt_partidos->get_result();

        $partido_encontrado = null;

        while ($partido = $result_partidos->fetch_assoc()) {
            $nombreParticipantesCompleto = $partido['nombre_participantes'];
            $array_nombres = array_map('trim', explode(" vs ", $nombreParticipantesCompleto));
            $primerParticipante = $array_nombres[0];

            error_log("Comparando '{$primerParticipante}' con '{$nombreCompletoUsuario}'");

            if (strcasecmp($primerParticipante, $nombreCompletoUsuario) == 0) {
                $partido_encontrado = $nombreParticipantesCompleto;
                break;
            }
        }

        $stmt_partidos->close();

        if ($partido_encontrado) {
            $respuesta = [
                "division" => $usuario['division'],
                "liga" => $usuario['liga'],
                "nombre_participantes" => $partido_encontrado
            ];
        } else {
            $respuesta = [
                "division" => $usuario['division'],
                "liga" => $usuario['liga'],
                "nombre_participantes" => "",
                "mensaje" => "El usuario no es el primer participante en ningún partido de la jornada actual"
            ];
        }

        ob_end_clean();
        echo json_encode($respuesta);
    } else {
        ob_end_clean();
        echo json_encode(["error" => "Usuario no encontrado"]);
    }
} else {
    ob_end_clean();
    echo json_encode(["error" => "Falta el correo del usuario"]);
}

$conn->close();
?>
