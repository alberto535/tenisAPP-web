<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$participantes = $_POST['nombre_participantes'] ?? '';
$resultado     = $_POST['resultado'] ?? '';
$division      = $_POST['division'] ?? '';
$liga          = $_POST['liga'] ?? '';
$fecha         = $_POST['fecha'] ?? '';
$sets          = $_POST['sets'] ?? ''; // Campo para sets

if (empty($participantes) || empty($resultado) || empty($division) || empty($liga) || empty($fecha)) {
    echo json_encode(["error" => "Todos los campos son obligatorios"]);
    exit;
}

// 1. Obtener el id_jornada de la jornada actual para la liga indicada
$sql_jornada = "SELECT id FROM jornada WHERE liga = ? AND fecha_fin = '0000-00-00' AND estado = 'activo' LIMIT 1";
$stmt_jornada = $conn->prepare($sql_jornada);
$stmt_jornada->bind_param("s", $liga);
$stmt_jornada->execute();
$result_jornada = $stmt_jornada->get_result();
$jornada = $result_jornada->fetch_assoc();
$stmt_jornada->close();

if (!$jornada) {
    echo json_encode(["error" => "No se encontró la jornada actual para la liga"]);
    exit;
}
$id_jornada = $jornada['id'];

// 2. Obtener el siguiente id para la tabla partidos (suponiendo que 'id' no es auto_increment)
$sql_max = "SELECT MAX(id) AS maxid FROM partidos";
$result_max = $conn->query($sql_max);
$row_max = $result_max->fetch_assoc();
$next_id = ($row_max['maxid'] !== null) ? $row_max['maxid'] + 1 : 1;

// 3. Insertar el partido en la tabla partidos con estado 'pendiente'
$sql = "INSERT INTO partidos (id, id_jornada, nombre_participantes, resultado, division, liga, fecha, sets, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssss", $next_id, $id_jornada, $participantes, $resultado, $division, $liga, $fecha, $sets);

if ($stmt->execute()) {
    echo json_encode(["message" => "Partido registrado con éxito"]);
} else {
    echo json_encode(["error" => "Error al registrar el partido"]);
}

$stmt->close();
$conn->close();
?>
