<?php
$conexion = new mysqli("localhost", "root", "", "l&r");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$query = "SELECT nombre, fecha_creacion FROM ligas WHERE fecha_finalizacion = '0000-00-00' ORDER BY fecha_creacion DESC";
$result = $conexion->query($query);

$ligas = [];

while ($row = $result->fetch_assoc()) {
    $ligas[] = $row;
}

echo json_encode(["ligas" => $ligas]);

$conexion->close();
?>
