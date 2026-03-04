<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 07-04-2025
    Resumen: Establece una fecha de finalizacion para la liga seleccionada.
 */

$conexion = new mysqli("localhost", "root", "", "l&r");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se recibió el nombre de la liga por URL
if (!isset($_GET['liga']) || empty($_GET['liga'])) {
    die("Error: No se especificó una liga.");
}

$liga = $conexion->real_escape_string($_GET['liga']);
$fecha_actual = date('Y-m-d');

// Actualizar la fecha de finalización de la liga
$sql = "UPDATE ligas SET fecha_finalizacion = '$fecha_actual' WHERE nombre = '$liga'";

if ($conexion->query($sql) === TRUE) {
    echo "La liga '$liga' ha sido finalizada correctamente.";
} else {
    echo "Error al finalizar la liga: " . $conexion->error;
}

$conexion->close();

// Redirigir a la página de ligas pasadas
header("Location: home.php");
exit();
?>
