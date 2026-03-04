<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 01-03-2025
    Resumen: Realiza la conexión a la base de datos y obtiene por el URL el token. Y se establece la contraseña nueva y el token a NULL, 
    para el usuario o administrador que realizo la peticion.
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

$email = "";
$tabla = ""; // Inicializar la variable

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Buscar el usuario en ambas tablas
    $sql = "SELECT correo, 'usuarios' AS tabla FROM usuarios WHERE token = ? 
            UNION 
            SELECT correo, 'administradores' AS tabla FROM administradores WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $email = $row['correo'];
        $tabla = $row['tabla']; // Saber si es de usuarios o administradores
    } else {
        die("Error: Token no válido o expirado.");
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $tabla = $_POST['tabla']; // Recuperar la tabla desde el formulario

    if ($nueva_contrasena !== $confirmar_contrasena) {
        die("Error: Las contraseñas no coinciden.");
    }

    $pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($pattern, $nueva_contrasena)) {
        die("La contraseña debe tener al menos 8 caracteres, incluir una letra, un número y un carácter especial.");
    }

    $hashed_password = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

    // Verificar en qué tabla está el usuario y actualizar
    if ($tabla === "usuarios") {
        $sql = "UPDATE usuarios SET contraseña=?, token=NULL WHERE correo=? AND token=?";
    } else {
        $sql = "UPDATE administradores SET contraseña=?, token=NULL WHERE correo=? AND token=?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $hashed_password, $email, $token);

    if ($stmt->execute()) {
        echo "La contraseña ha sido cambiada exitosamente.";
    } else {
        echo "Error al cambiar la contraseña: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
