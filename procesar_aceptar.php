<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 08-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge los usuarios seleccionados en el anterior archivo, y actualiza el estado
    a activo si se dio en aceptar y borra los usuarios a los que se le dio a rechazar.
*/

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Comprobar si se han enviado usuarios y una acción
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['usuarios']) && isset($_POST['accion'])) {
    $usuarios = $_POST['usuarios'];
    $accion = $_POST['accion'];

    if ($accion === 'aceptar') {
        // Aceptar usuarios: cambiar el estado a "aceptado"
        $correos = implode(",", array_map(function($correo) use ($conn) {
            return "'" . $conn->real_escape_string($correo) . "'";
        }, $usuarios));

        $sql = "UPDATE usuarios SET estado = 'activo' WHERE correo IN ($correos)";
    } elseif ($accion === 'rechazar') {
        // Rechazar usuarios: eliminar de la base de datos
        $correos = implode(",", array_map(function($correo) use ($conn) {
            return "'" . $conn->real_escape_string($correo) . "'";
        }, $usuarios));

        $sql = "DELETE FROM usuarios WHERE correo IN ($correos)";
    } else {
        echo "Acción no válida.";
        exit;
    }

    if ($conn->query($sql) === TRUE) {
        if ($accion === 'aceptar') {
            echo "Usuarios aceptados correctamente.";
            $conn->close();
            include 'home.php';
        } elseif ($accion === 'rechazar') {
            echo "Usuarios rechazados y eliminados correctamente.";
            $conn->close();
            include 'home.php';
        }
    } else {
        echo "Error al procesar la solicitud: " . $conn->error;
        $conn->close();
    }
    
} else {
    echo "No se seleccionaron usuarios o acción no válida.";
    $conn->close();
    include 'home.php';
}


?>
