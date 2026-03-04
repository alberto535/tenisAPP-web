<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 07-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge los usuarios seleccionados del anterior archivo, y realiza un delete de los usuarios seleccionados
    de las tablas clasificacion y usuarios.
*/

// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usuarios'])) {
    // Recuperar IDs seleccionados
    $usuariosSeleccionados = $_POST['usuarios'];

    // Preparar consulta SQL para eliminar usuarios seleccionados
    $correos = array_map(function ($correo) use ($conn) {
        return "'" . $conn->real_escape_string($correo) . "'";
    }, $usuariosSeleccionados);

    // Convertir el array en una lista separada por comas
    $correosList = implode(",", $correos);
   
    $sql = "DELETE FROM usuarios WHERE correo IN ($correosList)";

    if ($conn->query($sql) === TRUE) {
        echo "Usuarios eliminados correctamente de la tabla usuarios.";
    } else {
        echo "Error al eliminar usuarios: " . $conn->error;
    }
    $sql = "DELETE FROM clasificacion WHERE correo IN ($correosList)";

    if ($conn->query($sql) === TRUE) {
        echo "Usuarios eliminados correctamente de la tabla clasificacion.";
        include 'home.php';
    } else {
        echo "Error al eliminar usuarios: " . $conn->error;
    }
} else {
    echo "No se seleccionó ningún usuario.";
    echo "<a href=eliminar-usuarios.php> Volver atras </a>";
}

$conn->close();
?>
