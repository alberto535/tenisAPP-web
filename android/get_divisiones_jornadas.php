<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}


$correo = $_GET['correo'];

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

// Obtener divisiones disponibles en la liga del usuario
$sqlDivisiones = "SELECT DISTINCT division FROM partidos WHERE liga = ?";
$stmtDivisiones = $conn->prepare($sqlDivisiones);
$stmtDivisiones->bind_param("s", $liga);
$stmtDivisiones->execute();
$resultDivisiones = $stmtDivisiones->get_result();
$divisiones = [];
while ($row = $resultDivisiones->fetch_assoc()) {
    $divisiones[] = $row['division'];
}
$stmtDivisiones->close();

// Obtener jornadas disponibles en la liga del usuario
$sqlJornadas = "SELECT DISTINCT id_jornada FROM partidos WHERE liga = ?";
$stmtJornadas = $conn->prepare($sqlJornadas);
$stmtJornadas->bind_param("s", $liga);
$stmtJornadas->execute();
$resultJornadas = $stmtJornadas->get_result();
$jornadas = [];
while ($row = $resultJornadas->fetch_assoc()) {
    $jornadas[] = $row['id_jornada'];
}
$stmtJornadas->close();

// Responder con los datos obtenidos
echo json_encode([
    'success' => true,
    'divisiones' => $divisiones,
    'jornadas' => $jornadas
]);

$conn->close();
?>
