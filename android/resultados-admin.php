<?php
header('Content-Type: application/json'); // Indicar que la respuesta es JSON
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Consulta para obtener los partidos con estado "aceptado"
$sql = "SELECT id, fecha, resultado, nombre_participantes, division
        FROM partidos
        WHERE estado = 'aceptado'
        ORDER BY fecha DESC";

$result = $conn->query($sql);

$partidos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $partidos[] = $row;
    }
}

echo json_encode(['success' => true, 'partidos' => $partidos]);

$conn->close();
?>
