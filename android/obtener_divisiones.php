<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT division FROM clasificacion";
$result = $conn->query($sql);

$divisiones = [];
while ($row = $result->fetch_assoc()) {
    $divisiones[] = $row['division'];
}

echo json_encode($divisiones);
$conn->close();
?>
