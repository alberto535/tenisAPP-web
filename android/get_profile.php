<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['correo'])) {
    $correo = $conn->real_escape_string($_GET['correo']);

    $sql = "SELECT nombre, apellidos, correo, fechanacimiento, telefono, dni FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'usuario' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    $stmt->close();
}

$conn->close();
?>
