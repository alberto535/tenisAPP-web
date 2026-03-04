<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}



$correo = $_GET['correo'] ?? null;
$division = $_GET['division'] ?? null;
$jornada = $_GET['id_jornada'] ?? null;

// Obtener la liga del usuario autenticado
$sqlLiga = "SELECT liga FROM usuarios WHERE correo = ?";
$stmtLiga = $conn->prepare($sqlLiga);
$stmtLiga->bind_param("s", $correo);
$stmtLiga->execute();
$resultLiga = $stmtLiga->get_result();
$liga = $resultLiga->fetch_assoc()['liga'] ?? null;
$stmtLiga->close();

if (!$liga) {
    echo json_encode(['success' => false, 'message' => 'No se encontró la liga del usuario']);
    exit;
}

// Construir la consulta de partidos con filtros
$sql = "SELECT * FROM partidos WHERE estado = 'procesado' AND liga = ?";
$types = "s";
$params = [$liga];

if ($division) {
    $sql .= " AND division = ?";
    $types .= "s";
    $params[] = $division;
}

if ($jornada) {
    $sql .= " AND id_jornada = ?";
    $types .= "s";
    $params[] = $jornada;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$matches = [];
while ($row = $result->fetch_assoc()) {
    $matches[] = $row;
}

echo json_encode(['success' => true, 'partidos' => $matches]);

$stmt->close();
$conn->close();
?>
