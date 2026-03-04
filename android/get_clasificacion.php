<?php
$conexion = new mysqli("localhost", "root", "", "l&r");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$liga = $_GET['liga'];
$division = isset($_GET['division']) ? $_GET['division'] : '';

if (!empty($division)) {
    $query = "SELECT nombre, apellidos, puntuaje, buch, `m-buch`, liga, division FROM clasificacion WHERE liga = ? AND division = ? ORDER BY puntuaje DESC ,buch DESC ,`m-buch` DESC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $liga, $division);
} else {
    $query = "SELECT nombre, apellidos, puntuaje, buch, `m-buch`, liga, division FROM clasificacion WHERE liga = ? ORDER BY puntuaje DESC ,buch DESC ,`m-buch` DESC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $liga);
}

$stmt->execute();
$result = $stmt->get_result();

$clasificacion = [];
$divisiones = [];

while ($row = $result->fetch_assoc()) {
    $clasificacion[] = $row;
    if (!in_array($row['division'], $divisiones)) {
        $divisiones[] = $row['division'];
    }
}

echo json_encode(["clasificacion" => $clasificacion, "divisiones" => $divisiones]);

$stmt->close();
$conexion->close();
?>
