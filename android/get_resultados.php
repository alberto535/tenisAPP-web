<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Obtener el nombre de la liga desde la URL
$liga = isset($_GET['liga']) ? $_GET['liga'] : '';

if (empty($liga)) {
    die(json_encode(["error" => "Liga no proporcionada"]));
}

// Consulta para obtener los partidos de la liga seleccionada
$query = "SELECT id, fecha, resultado, sets, nombre_participantes, division, liga, id_jornada
          FROM partidos
          WHERE liga = ?
          ORDER BY fecha DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $liga);
$stmt->execute();
$result = $stmt->get_result();

$partidos = [];
while ($row = $result->fetch_assoc()) {
    $partidos[] = $row;
}

echo json_encode(["partidos" => $partidos]);

$stmt->close();
$conn->close();
?>
