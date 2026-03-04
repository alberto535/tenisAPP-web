<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 13-03-2025
    Resumen: Realiza una conexión a la base de datos y establece la division en la tabla usuarios y la tabla clasificacion.
*/

$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['division'])) {
    foreach ($_POST['division'] as $correo => $division) {
        $correo = $conn->real_escape_string($correo);
        $division = intval($division);

        $sql = "UPDATE usuarios SET division = ? WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $division, $correo);
            if ($stmt->execute()) {
            } else {
                echo "Error al actualizar división para $correo: " . $conn->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta<br>";
        }
    }
} else {
    echo "No se recibieron datos para actualizar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['division'])) {
    foreach ($_POST['division'] as $correo => $division) {
        $correo = $conn->real_escape_string($correo);
        $division = (int) $division;

        // Actualizar división en la tabla usuarios
        $sql_update_user = "UPDATE usuarios SET division = ? WHERE correo = ?";
        $stmt_user = $conn->prepare($sql_update_user);
        $stmt_user->bind_param("is", $division, $correo);
        $stmt_user->execute();
        $stmt_user->close();

        // Actualizar división en la tabla clasificacion
        $sql_update_clasificacion = "UPDATE clasificacion SET division = ? WHERE correo = ?";
        $stmt_clasificacion = $conn->prepare($sql_update_clasificacion);
        $stmt_clasificacion->bind_param("is", $division, $correo);
        $stmt_clasificacion->execute();
        $stmt_clasificacion->close();
    }
}

$conn->close();
header("Location: index.php");
exit;





include 'home.php';

$conn->close();
?>
