<?php

/*
    Autor: Alberto Ortiz Arribas
    Fecha: 02-03-2025
    Resumen: Comprueba que el usuario es un administrador, y muestra por pantalla un menú para el administrador con tres botones que redirigen a nuevas páginas
    con las funcionalidades del administrador.
*/

session_start(); // Iniciar la sesión

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['correo'])) {
    // Si no hay correo en la sesión, redirigir al login
    header("Location: login.html");
    exit();
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el correo del usuario pertenece a un administrador
$correo = $_SESSION['correo'];
$query = "SELECT COUNT(*) FROM administradores WHERE correo = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($isAdmin);
$stmt->fetch();
$stmt->close();

// Si no es un administrador, redirigir al login
if ($isAdmin == 0) {
    header("Location: login.html"); // O cualquier otra página a la que quieras redirigir
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('imagenes/tenis2.jpg') no-repeat center center fixed; /* Imagen de fondo */
            background-size: cover; /* Ajustar para que cubra toda la pantalla */
            color: white; /* Cambiar el color del texto para que sea legible */
        }

        .navbar {
            background-color: rgba(0, 123, 255, 0.8); /* Fondo translúcido */
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            padding: 20px;
            text-align: center;
        }

        .container h1 {
            color: #ffffff;
        }

        .links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        .link {
            background-color: rgba(0, 123, 255, 0.8); /* Fondo translúcido */
            color: white;
            padding: 15px 20px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .link:hover {
            background-color: rgba(0, 86, 179, 0.8);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span><strong>TFG Tenis</strong></span>
        <div>
            <a href="login.html">Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
        <h1>Bienvenido a la página principal</h1>
        <p>Seleccione una opción:</p>

        <div class="links">
            <a href="eliminar-usuarios.php" class="link">Eliminar Usuarios</a>
            <a href="procesar-aceptar.php" class="link">Aceptar Usuarios</a>
            <a href="h_administrar_ligas.php" class="link">Administrar Ligas</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close(); // Cerrar la conexión a la base de datos
?>
