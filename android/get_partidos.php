<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Verifica que se haya recibido el parámetro nombreUsuario
if (!isset($_GET['nombreUsuario']) || empty($_GET['nombreUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el nombre de usuario']);
    exit;
}

$currentUser = trim(strtolower($_GET['nombreUsuario']));

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// 🔍 1. Obtener el nombre completo (nombre y apellidos) desde la tabla `usuarios`
$sqlUsuario = "SELECT nombre, apellidos FROM usuarios WHERE LOWER(correo) = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
if (!$stmtUsuario) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta del usuario']);
    exit;
}

$stmtUsuario->bind_param("s", $currentUser);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$rowUsuario = $resultUsuario->fetch_assoc();
$nombreUsuario = trim(strtolower($rowUsuario['nombre'] . ' ' . $rowUsuario['apellidos']));

$stmtUsuario->close();

// 🔍 2. Buscar partidos donde el usuario sea el segundo participante
$sqlPartidos = "SELECT * FROM partidos
                WHERE estado = 'pendiente'
                AND LOWER(TRIM(SUBSTRING_INDEX(nombre_participantes, ' vs ', -1))) = ?";
$stmtPartidos = $conn->prepare($sqlPartidos);
if (!$stmtPartidos) {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de partidos']);
    exit;
}

$stmtPartidos->bind_param("s", $nombreUsuario);
$stmtPartidos->execute();
$resultPartidos = $stmtPartidos->get_result();

$matches = [];
while ($row = $resultPartidos->fetch_assoc()) {
    $matches[] = $row;
}

// Devolver los resultados en formato JSON
echo json_encode(['success' => true, 'matches' => $matches]);

$stmtPartidos->close();
$conn->close();
?>