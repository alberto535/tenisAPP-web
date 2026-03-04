<?php
session_start();
header('Content-Type: application/json');

$response = [];

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['nombreUsuario']) || empty($_SESSION['nombreUsuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$usuarioActual = $_SESSION['nombreUsuario'];
$response['usuarioActual'] = $usuarioActual;

$conexion = new mysqli("localhost", "root", "", "l&r");
if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $conexion->connect_error]);
    exit();
}

// Obtener todos los partidos pendientes sin filtro (para depuración)
$sql = "SELECT id, nombre_participantes, resultado, division FROM partidos WHERE estado = 'pendiente'";
$resultado = $conexion->query($sql);

$partidosSinFiltro = [];
while ($fila = $resultado->fetch_assoc()) {
    $partidosSinFiltro[] = $fila;
}
$response['partidos_sin_filtro'] = $partidosSinFiltro;

// Aplicar filtro por usuario: comparar el segundo participante (después de " vs ") con el usuario actual, ignorando mayúsculas y espacios
$sqlFiltro = "SELECT id, nombre_participantes, resultado, division
        FROM partidos
        WHERE estado = 'pendiente'
        AND LOWER(TRIM(SUBSTRING_INDEX(nombre_participantes, ' vs ', -1))) = LOWER(?)";

$stmt = $conexion->prepare($sqlFiltro);
$stmt->bind_param("s", $usuarioActual);
$stmt->execute();
$resultadoFiltro = $stmt->get_result();

$partidosFiltrados = [];
while ($fila = $resultadoFiltro->fetch_assoc()) {
    $partidosFiltrados[] = $fila;
}
$response['partidos_filtrados'] = $partidosFiltrados;

$stmt->close();
$conexion->close();

echo json_encode($response);
exit();
?>
