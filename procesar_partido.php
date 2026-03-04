<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 10-03-2025
    Resumen: Procesa la acción de aceptar o rechazar un partido. En caso de rechazo, envía un correo al contrincante.
*/

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");
$conn->autocommit(TRUE); // Ejecuta consultas sin necesidad de commit

// Validar parámetros
if (!isset($_POST['id']) || !isset($_POST['accion'])) {
    die("Datos incompletos.");
}

$id_partido = $_POST['id'];
$accion = $_POST['accion'];

// Primero, si se rechaza, obtenemos el correo ANTES de hacer el UPDATE
if ($accion === 'rechazar') {
    $query = "SELECT u.correo 
              FROM usuarios u 
              INNER JOIN partidos p ON u.nombre = TRIM(SUBSTRING_INDEX(p.nombre_participantes, ' vs ', -1))
              WHERE p.id = ?";
    $stmtCorreo = $conn->prepare($query);
    if ($stmtCorreo) {
        $stmtCorreo->bind_param("i", $id_partido);
        $stmtCorreo->execute();
        $result = $stmtCorreo->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $correo_destinatario = $row['correo'];

            // Enviar correo
            $asunto = "⚽ Partido Rechazado - Reenvío de Resultado";
            $mensaje = "El resultado del partido ha sido rechazado. Por favor, vuelve a ingresar el resultado.";
            $cabeceras = "From: control9864@gmail.com\r\n";
            $cabeceras .= "Reply-To: control9864@gmail.com\r\n";
            $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($correo_destinatario, $asunto, $mensaje, $cabeceras);
        }

        $stmtCorreo->close(); // ⚠️ Liberar recursos del SELECT
    }
}

// Segundo, realizamos el UPDATE
$estado_nuevo = ($accion === 'aceptar') ? 'aceptado' : 'rechazado';
$sql = "UPDATE partidos SET estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("si", $estado_nuevo, $id_partido);
    
    if ($stmt->execute()) {
        header("Location: home-user.php");
        exit();
    } else {
        echo "<script>alert('Error al actualizar el estado del partido.'); window.location.href='home-user.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Error en la preparación de la consulta.'); window.location.href='home-user.php';</script>";
}

$conn->close();
?>
