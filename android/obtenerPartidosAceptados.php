<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se ha recibido el parámetro 'liga'
if (!isset($_GET['liga']) || empty($_GET['liga'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el nombre de la liga']);
    exit;
}

$liga = $_GET['liga'];  // Obtener el nombre de la liga desde la URL

// Consulta SQL para obtener los partidos en estado "aceptado" para la liga especificada
$sql = "SELECT * FROM partidos WHERE liga = ? AND estado = 'aceptado'";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
    exit;
}

// Vincular el parámetro de la liga
$stmt->bind_param("s", $liga);

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Array para almacenar los partidos
$partidos = [];

// Verificar si hay resultados
while ($row = $result->fetch_assoc()) {
    $partidos[] = $row;
}

// Devolver los partidos en formato JSON
echo json_encode(['success' => true, 'matches' => $partidos]);

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
