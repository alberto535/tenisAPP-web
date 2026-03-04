<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 02-03-2025
    Resumen: Muestra un menú con botones que dan acceso a todas las funcionalidades respectivas para el usuario en las páginas a las que mandan.
*/

session_start();


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('imagenes/tenis2.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #ffffff;
        }

        .container {
            text-align: center;
            background: rgba(0, 0, 0, 0.7); /* Fondo oscuro semitransparente */
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #ffffff;
        }

        p {
            color: #cccccc;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .links a:hover {
            background-color: #0056b3;
        }

        .logout {
            margin-top: 20px;
        }

        .logout a {
            color: #ff4d4d;
            text-decoration: none;
        }

        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenido!</h1>
        <p>Esta es tu página principal como usuario.</p>

        <div class="links">
            <a href="perfil.php">Ver Perfil</a>
            <a href="clasificacion-divisiones.php">Clasificaciones</a>
            <a href="resultados.php">Resultados</a>
            <a href="insertar_resultado.php">Insertar resultado</a>
            <a href="ver_partidos.php">Aceptar resultado</a>
        </div>

        <p class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </p>
    </div>
</body>
</html>
