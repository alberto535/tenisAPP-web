<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? null;
    $nueva_contrasena = $_POST['contraseña'] ?? null;

    if (!$token || !$nueva_contrasena) {
        echo json_encode(['message' => 'Token o contraseña no proporcionados']);
        exit;
    }

    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'mi_base_datos');
    if ($conexion->connect_error) {
        echo json_encode(['message' => 'Error al conectar con la base de datos']);
        exit;
    }

    // Verificar el token
    $query = $conexion->prepare('SELECT * FROM usuarios WHERE token = ?');
    $query->bind_param('s', $token);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(['message' => 'Token inválido']);
        exit;
    }

    // Actualizar contraseña y eliminar token
    $hash_password = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
    $update = $conexion->prepare('UPDATE usuarios SET contraseña = ?, token = NULL WHERE token = ?');
    $update->bind_param('ss', $hash_password, $token);
    $update->execute();

    echo json_encode(['message' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['message' => 'Método no permitido']);
}
?>
