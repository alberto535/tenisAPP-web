<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 02-03-2025
    Resumen: Realiza la conexión a la base de datos y obtiene y muestra los datos del usuario logueado en nuestro sistema, en un formulario
    para su edición y subida de nuevo a las tablas de usuarios y clasificacion.
*/

session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conexión con la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    die("Error: No has iniciado sesión.");
}

$nombreUsuario = $_SESSION['correo'];
$usuario = null;
$mensaje = "";

// Obtener los datos del usuario logueado (incluyendo liga)
$sql = "SELECT nombre, apellidos, correo, fecha_nacimiento, telefono, dni, liga FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nombreUsuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    die("Error: No se encontró información del usuario.");
}

// Guardar correo anterior y valor de liga antes de actualizar
$correoAnterior = $usuario['correo'];
$ligaUsuario = $usuario['liga'];

// Actualizar datos del usuario con validaciones
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $correo = $_POST['correo'];
    $fechanacimiento = $_POST['fechanacimiento'];
    $telefono = $_POST['telefono'];
    $dni = $_POST['dni'];

    // Validaciones
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || 
        !(str_ends_with($correo, ".com") || str_ends_with($correo, ".es"))) {
        $mensaje = "Correo inválido. Debe contener '@' y terminar en .com o .es.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fechanacimiento)) {
        $mensaje = "Fecha de nacimiento inválida. Formato requerido: YYYY-MM-DD.";
    } elseif (!preg_match("/^\d{9}$/", $telefono)) {
        $mensaje = "Teléfono inválido. Debe contener exactamente 9 dígitos.";
    } elseif (!preg_match("/^\d{8}[A-Z]$/", $dni)) {
        $mensaje = "DNI inválido. Debe tener 8 números seguidos de una letra mayúscula.";
    } else {
        $sql = "UPDATE usuarios SET nombre=?, apellidos=?, correo=?, fecha_nacimiento=?, telefono=? WHERE dni=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nombre, $apellidos, $correo, $fechanacimiento, $telefono, $dni);

        if ($stmt->execute()) {
            // Si el usuario tiene una liga asociada, actualizar también en clasificacion
            if (!is_null($ligaUsuario)) {
                $sqlClasificacion = "UPDATE clasificacion SET nombre=?, apellidos=?, correo=? WHERE correo=? AND liga=?";
                $stmtClasificacion = $conn->prepare($sqlClasificacion);
                $stmtClasificacion->bind_param("sssss", $nombre, $apellidos, $correo, $correoAnterior, $ligaUsuario);
                $stmtClasificacion->execute();
            }

            $mensaje = "Perfil actualizado correctamente.";
            $usuario = [
                "nombre" => $nombre,
                "apellidos" => $apellidos,
                "correo" => $correo,
                "fecha_nacimiento" => $fechanacimiento,
                "telefono" => $telefono,
                "dni" => $dni,
                "liga" => $ligaUsuario
            ];
        } else {
            $mensaje = "Error al actualizar el perfil.";
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 70%;
            max-width: 800px;
            background: white;
            padding: 20px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }
        .guardar {
            background: #28a745;
        }
        .guardar:hover {
            background: #218838;
        }
        .volver {
            background: #007bff;
        }
        .volver:hover {
            background: #0056b3;
        }
        .mensaje {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .mensaje.exito {
            color: green;
        }
        .mensaje.error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Perfil</h2>

    <?php
    if ($mensaje) {
        $esError = strpos($mensaje, "correctamente") === false;
        $claseMensaje = $esError ? "mensaje error" : "mensaje exito";
        echo "<p class='$claseMensaje'>$mensaje</p>";
    }
    ?>

    <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>

        <label>Correo:</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>

        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fechanacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>">

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">

        <label>DNI (no editable):</label>
        <input type="text" name="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>

        <button type="submit" name="actualizar" class="guardar">Guardar Cambios</button>
    </form>

    <!-- Botón para volver al Home -->
    <button class="volver" onclick="window.location.href='home-user.php'">Volver al inicio</button>
</div>

</body>
</html>
