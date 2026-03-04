<?php
ob_start(); // Inicia el búfer de salida
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'l&r');
if ($conn->connect_error) {
    ob_end_clean(); // Limpia el búfer
    echo json_encode(["error" => "Error de conexión: " . $conn->connect_error]);
    exit;
}

$liga = $_POST['liga'] ?? $_GET['liga'] ?? '';
$division = $_POST['division'] ?? $_GET['division'] ?? '';


    $query = "SELECT nombre, apellidos, puntuaje, buch, `m-buch` FROM clasificacion WHERE liga = ? AND division = ? ORDER BY puntuaje DESC, buch DESC, `m-buch` DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $liga, $division);
    $stmt->execute();
    $result = $stmt->get_result();

    $clasificacion = [];
    while ($row = $result->fetch_assoc()) {
        $clasificacion[] = $row;
    }

    ob_end_clean(); // Limpia cualquier salida previa
    echo json_encode($clasificacion);
