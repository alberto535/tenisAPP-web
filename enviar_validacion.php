<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 21-03-2025
    Resumen: Realiza la conexión a la base de datos, se comprueba que el correo existe en nuestras bases de datos.  Se genera y guarda un token para 
    recuperar la contraseña, se crea un enlace de recuperacion personalizado y se envia por correo.
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir PHPMailer
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];

    // Conexión a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "l&r";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Verificar si el correo existe en la tabla usuarios o administradores
    $sql = "SELECT 'usuario' AS tipo FROM usuarios WHERE correo = ?
            UNION
            SELECT 'admin' AS tipo FROM administradores WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $correo, $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_usuario = $row['tipo'];

        // Generar un token único
        $token = bin2hex(random_bytes(16));

        // Guardar el token en la tabla correspondiente
        if ($tipo_usuario == 'usuario') {
            $updateSql = "UPDATE usuarios SET token = ? WHERE correo = ?";
        } else {
            $updateSql = "UPDATE administradores SET token = ? WHERE correo = ?";
        }
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $token, $correo);
        $updateStmt->execute();

        // Crear el enlace de recuperación
        $enlace = "http://localhost/cambiar-password.html?token=" . $token;

        // Configurar PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'control9864@gmail.com';
            $mail->Password = 'uohj asqi ioqf nfrg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('control9864@gmail.com', 'Recuperación de Contraseña');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Recupera tu contraseña';
            $mail->Body = "Haz clic en el siguiente enlace para recuperar tu contraseña: <a href='$enlace'>$enlace</a>";

            $mail->send();
            echo "El enlace de recuperación se ha enviado a $correo.";
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        echo "El correo no está registrado.";
    }

    $stmt->close();
    $conn->close();
}
?>
