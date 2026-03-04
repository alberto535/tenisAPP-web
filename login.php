<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 01-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge los datos del html, se define un rol dependiendo de si esta en la 
    tabla administradores o usuarios y redirige al home de usuarios o de administradores. Tambien comprueba que los usuarios
    hayan sido aceptados por el administrador para poder usar la app.
*/


session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conn->real_escape_string($_POST['correo']);
    $password = $_POST['contraseña']; // No se escapa porque se usará en password_verify

    // 1. Verificar si el usuario es administrador
    $stmt = $conn->prepare("SELECT * FROM administradores WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // Si está en administradores
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['contraseña'])) {
            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = "admin"; // Definir rol
            $_SESSION['nombreUsuario'] = $row['nombre']; 
            header("Location: home.php");
            exit;
        } else {
            echo "Contraseña incorrecta. <a href='login.html'>Volver al Login</a>";
            exit;
        }
    }

    // 2. Si no es administrador, buscar en la tabla de usuarios
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['contraseña'])) {
            if ($row['estado'] !== 'activo') {
                echo "Todavía no ha sido validada la cuenta de usuario.";
                exit;
            }

            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = "usuario"; // Definir rol para usuarios normales
            $_SESSION['nombreUsuario'] = $row['nombre'];
            header("Location: home-user.php");
            exit;
        } else {
            echo "Contraseña incorrecta. <a href='login.html'>Volver al Login</a>";
            exit;
        }
    } else {
        echo "El correo no existe. <a href='login.html'>Volver al Login</a>";
        exit;
    }
}

$conn->close();
?>
