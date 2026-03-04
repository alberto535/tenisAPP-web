<?php
header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validación básica
if (!isset($data['nombre_liga']) || !isset($data['usuarios']) || empty($data['usuarios'])) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$nombre_liga = $data['nombre_liga'];
$usuarios = $data['usuarios'];

// Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "", "l&r");

if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexión: " . $conexion->connect_error]);
    exit;
}

// Verificar si la liga ya existe
$check = $conexion->prepare("SELECT COUNT(*) FROM ligas WHERE nombre = ?");
$check->bind_param("s", $nombre_liga);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

if ($count > 0) {
    echo json_encode(["error" => "La liga '$nombre_liga' ya existe."]);
    exit;
}

$fecha_actual = date('Y-m-d');
// Insertar liga
$stmt = $conexion->prepare("INSERT INTO ligas (nombre,fecha_creacion) VALUES (?,?)");
$stmt->bind_param("ss", $nombre_liga, $fecha_actual);
if (!$stmt->execute()) {
    echo json_encode(["error" => "Error al crear la liga: " . $stmt->error]);
    exit;
}

$liga_id = $conexion->insert_id;

// Preparar consultas
$stmt_usuario = $conexion->prepare("UPDATE usuarios SET liga = ?, division = ? WHERE correo = ?");
$stmt_clasificacion = $conexion->prepare(
    "INSERT INTO clasificacion (nombre, apellidos, correo, puntuaje, buch, `m-buch`, liga, division)
     SELECT nombre, apellidos, correo, 0, 0, 0, ?, ? FROM usuarios WHERE correo = ?"
);

foreach ($usuarios as $usuario) {
    $correo = $usuario['correo'];
    $division = $usuario['division'];

    $stmt_usuario->bind_param("sis", $nombre_liga, $division, $correo);
    if (!$stmt_usuario->execute()) {
        echo json_encode(["error" => "Error al actualizar usuario $correo: " . $stmt_usuario->error]);
        exit;
    }

    $stmt_clasificacion->bind_param("sis", $nombre_liga, $division, $correo);
    if (!$stmt_clasificacion->execute()) {
        echo json_encode(["error" => "Error al insertar en clasificación para $correo: " . $stmt_clasificacion->error]);
        exit;
    }
}

echo json_encode(["mensaje" => "Liga creada con éxito"]);
$conexion->close();
?>
