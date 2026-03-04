<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'l&r');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Soportar GET y POST
$correo = $_POST['correo'] ?? $_GET['correo'] ?? '';

if (empty($correo)) {
    echo json_encode(["error" => "No se proporcionó correo"]);
    exit;
}

$query = "SELECT liga FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $liga = $row['liga'];

    // Obtener divisiones
    $queryDivisiones = "SELECT DISTINCT division FROM clasificacion WHERE liga = ?";
    $stmtDiv = $conn->prepare($queryDivisiones);
    $stmtDiv->bind_param("s", $liga);
    $stmtDiv->execute();
    $resultDiv = $stmtDiv->get_result();

    $divisiones = [];
    while ($rowDiv = $resultDiv->fetch_assoc()) {
        $divisiones[] = $rowDiv['division'];
    }

    echo json_encode(["liga" => $liga, "divisiones" => $divisiones]);
} else {
    echo json_encode(["error" => "Usuario no encontrado"]);
}
?>
