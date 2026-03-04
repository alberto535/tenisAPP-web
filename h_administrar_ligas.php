<!
    Autor: Alberto Ortiz Arribas
    Fecha: 12-03-2025
    Resumen: Muestra un menú con tres botones que dan acceso al manejo y consulta de las ligas de nuestra aplicacion
>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ligas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: url('imagenes/tenis3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 50px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.2); /* Fondo semitransparente */
            backdrop-filter: blur(10px); /* Desenfoque del fondo */
            padding: 20px;
            width: 50%;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: white;
        }

        .button {
            display: block;
            width: 80%;
            margin: 10px auto;
            padding: 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Ligas</h1>
        <button class="button" onclick="location.href='crear-liga.php'">Crear Nueva Liga</button>
        <button class="button" onclick="location.href='consultar-ligas-pasadas.php'">Consultar Ligas Pasadas</button>
        <button class="button" onclick="location.href='administrar-ligas-activas.php'">Administrar Ligas Activas</button>
        <a href="home.php">
        <button style="background-color: #FF5733; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
            Volver Atrás
        </button>
    </a>

    </div>

    
</body>
</html>
