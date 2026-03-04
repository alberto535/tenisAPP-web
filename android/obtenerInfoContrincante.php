<?php
error_reporting(0);
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "l&r");
if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit;
}

$correo = $_GET['correo'] ?? '';

if (!empty($correo)) {
    $sql_usuario = "SELECT nombre, apellidos, liga FROM usuarios WHERE correo = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("s", $correo);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $usuario = $result_usuario->fetch_assoc();
    $stmt_usuario->close();

    if ($usuario) {
        $nombre_completo = trim($usuario['nombre'] . ' ' . $usuario['apellidos']);
        $liga = $usuario['liga'];

        $sql_partidos = "SELECT jp.nombre_participantes
                         FROM jornada_partidos AS jp
                         JOIN jornada AS j ON jp.id_jornada = j.id
                         WHERE jp.liga = ? AND j.estado = 'activo' AND j.fecha_fin = '0000-00-00'";
        $stmt_partidos = $conn->prepare($sql_partidos);
        $stmt_partidos->bind_param("s", $liga);
        $stmt_partidos->execute();
        $result_partidos = $stmt_partidos->get_result();

        $contrincante = null;

        while ($partido = $result_partidos->fetch_assoc()) {
            $nombres = array_map('trim', explode(" vs ", $partido['nombre_participantes']));
            if (count($nombres) === 2) {
                if (strcasecmp($nombres[0], $nombre_completo) == 0) {
                    $contrincante = $nombres[1];
                    break;
                } elseif (strcasecmp($nombres[1], $nombre_completo) == 0) {
                    $contrincante = $nombres[0];
                    break;
                }
            }
        }

        $stmt_partidos->close();

        if ($contrincante) {
            $sql_datos_contrincante = "SELECT nombre, apellidos, telefono FROM usuarios WHERE CONCAT(nombre, ' ', apellidos) = ?";
            $stmt_contrincante = $conn->prepare($sql_datos_contrincante);
            $stmt_contrincante->bind_param("s", $contrincante);
            $stmt_contrincante->execute();
            $result_contrincante = $stmt_contrincante->get_result();
            $datos = $result_contrincante->fetch_assoc();
            $stmt_contrincante->close();

            if ($datos) {
                echo json_encode($datos);
            } else {
                echo json_encode(["error" => "Contrincante no encontrado"]);
            }
        } else {
            echo json_encode(["error" => "No se encontró partido activo del usuario"]);
        }
    } else {
        echo json_encode(["error" => "Usuario no encontrado"]);
    }
} else {
    echo json_encode(["error" => "Correo no proporcionado"]);
}

$conn->close();
?>
