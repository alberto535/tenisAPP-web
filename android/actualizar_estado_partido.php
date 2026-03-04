<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['id']) || !isset($input['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_partido = $input['id'];
$accion = $input['accion'];

if ($accion == 'aceptar') {
    $sql = "UPDATE partidos SET estado = 'aceptado' WHERE id = ?";
} elseif ($accion == 'rechazar') {
    $sql = "UPDATE partidos SET estado = 'rechazado' WHERE id = ?";

    // Obtener el correo del primer participante
    $query = "SELECT u.correo FROM usuarios u INNER JOIN partidos p
              ON u.nombre = TRIM(SUBSTRING_INDEX(p.nombre_participantes, ' vs ', 1))
              WHERE p.id = ?";
    $stmtCorreo = $conn->prepare($query);
    $stmtCorreo->bind_param("i", $id_partido);
    $stmtCorreo->execute();
    $resultCorreo = $stmtCorreo->get_result();

    if ($resultCorreo->num_rows > 0) {
        $rowCorreo = $resultCorreo->fetch_assoc();
        $correo_destinatario = $rowCorreo['correo'];

        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'control9864@gmail.com';
            $mail->Password = 'uohj asqi ioqf nfrg'; // Usa tu contraseña o token
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('control9864@gmail.com', 'Equipo de Soporte');
            $mail->addAddress($correo_destinatario);

            $mail->isHTML(true);
            $mail->Subject = '⚽ Partido Rechazado - Reenvío de Resultado';
            $mail->Body = "El resultado del partido ha sido rechazado. Por favor, vuelve a ingresar el resultado.";
            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar el correo: " . $mail->ErrorInfo);
        }
    }
    $stmtCorreo->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_partido);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estado del partido actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado del partido']);
}

$stmt->close();
$conn->close();
?>
