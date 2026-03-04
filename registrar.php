<?php

/*
    Autor: Alberto Ortiz Arribas
    Fecha: 01-03-2025
    Resumen: Realiza la recogida de los datos de parte del html y realiza la comprobación de los 
    datos introducidos, por consiguiente si los datos son correctos realiza la inserción en la
    tabla usuarios. Por último, envía un correo avisando de que se ha registrado en el sistema.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Si descargaste PHPMailer manualmente, incluye los archivos necesarios
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $fechanacimiento = $conn->real_escape_string($_POST['fechanacimiento']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $dni = $conn->real_escape_string($_POST['dni']);
    $passwd = $conn->real_escape_string($_POST['contraseña']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_contraseña']);

    // Verificar que las contraseñas coincidan
    if ($passwd !== $confirm_password) {
        die("Las contraseñas no coinciden.");
    }

    // Validar el formato de la fecha de nacimiento (yyyy-mm-dd)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechanacimiento)) {
        die("La fecha de nacimiento debe tener el formato yyyy-mm-dd.");
    }
    // Opcional: Validar que la fecha sea una fecha válida
    $d = DateTime::createFromFormat('Y-m-d', $fechanacimiento);
    if (!($d && $d->format('Y-m-d') === $fechanacimiento)) {
        die("La fecha de nacimiento no es una fecha válida.");
    }

    // Validar que el teléfono tenga 9 dígitos
    if (!preg_match('/^\d{9}$/', $telefono)) {
        die("El teléfono debe tener exactamente 9 dígitos.");
    }

    // Validar que el DNI tenga 8 números y una letra mayúscula al final
    if (!preg_match('/^\d{8}[A-Z]$/', $dni)) {
        die("El DNI debe tener 8 números y una letra mayúscula al final.");
    }

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Este correo ya está registrado. Por favor, usa otro.");
    }

    // Validar contraseña (mínimo 8 caracteres, una letra, un número y un carácter especial)
    $pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($pattern, $passwd)) {
        die("La contraseña debe tener al menos 8 caracteres, incluir una letra, un número y un carácter especial.");
    }

    $password_hash = password_hash($passwd, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, apellidos, correo, fecha_nacimiento, telefono, dni, contraseña, estado) 
            VALUES ('$nombre', '$apellidos', '$correo', '$fechanacimiento', '$telefono', '$dni', '$password_hash', 'pendiente')";

    if ($conn->query($sql) === TRUE) {
        enviarCorreoConfirmacion($correo, $nombre);
        echo "Registro exitoso. Se ha enviado un correo de confirmación.";
        echo '<a href="login.html">Iniciar sesión</a>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();

function enviarCorreoConfirmacion($correo, $nombre) {
    $mail = new PHPMailer(true);
    try {
         // Configuración del servidor SMTP
         $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com'; // Cambia por tu servidor SMTP
         $mail->SMTPAuth = true;
         $mail->Username = 'control9864@gmail.com'; // Tu correo
         $mail->Password = 'uohj asqi ioqf nfrg';       // Tu contraseña o token de aplicación
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('control9864@gmail.com', 'Equipo de Soporte');
        $mail->addAddress($correo, $nombre);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Registro';
        $mail->Body = "
            <h2>¡Hola, $nombre!</h2>
            <p>Gracias por registrarte en nuestra plataforma.</p>
            <p>$nombre, bienvenido a nuestro sistema. A partir de ahora podrás acceder utilizando tu correo: $correo y tu contraseña. También puedes acceder a través de nuestra aplicación.</p>
            <p>Si no solicitaste este registro, por favor ignora este mensaje.</p>
            <br>
            <p>Atentamente, <br>Equipo de Soporte</p>
        ";

        $mail->send();
        echo "Correo de confirmación enviado.";
    } catch (Exception $e) {
        echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
    }
}
?>
