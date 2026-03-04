<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 10-03-2025
    Resumen: Muestra una tabla con informacion de los partidos en los que el usuario es el visitante y estan en estado pendiente.
    Y los acepta o rechaza los partidos.
*/

session_start();

if (!isset($_SESSION['correo']) || empty($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correoUsuario = $_SESSION['correo'];

// Conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener nombre y apellidos del usuario logueado
$sqlUsuario = "SELECT nombre, apellidos FROM usuarios WHERE correo = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->bind_param("s", $correoUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows === 0) {
    die("Usuario no encontrado.");
}

$rowUsuario = $resultUsuario->fetch_assoc();
$nombreCompletoUsuario = trim($rowUsuario['nombre'] . ' ' . $rowUsuario['apellidos']);

// Obtener partidos pendientes
$sql = "SELECT * FROM partidos WHERE estado = 'pendiente'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Partidos Pendientes de Confirmación</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 20px; }
        table { width: 90%; margin: auto; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: green; color: white; }
        button { padding: 8px 12px; margin: 5px; cursor: pointer; border: none; border-radius: 4px; }
        .aceptar { background: green; color: white; }
        .rechazar { background: red; color: white; }
    </style>
</head>
<body>

<h2>Partidos Pendientes de Confirmación</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Resultado</th>
            <th>Sets</th>
            <th>Participantes</th>
            <th>División</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                    // Obtener el segundo participante
                    $partes = explode(" vs ", $row['nombre_participantes']);
                    $segundoParticipante = isset($partes[1]) ? trim($partes[1]) : '';
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['fecha']; ?></td>
                    <td><?php echo $row['resultado']; ?></td>
                    <td><?php echo $row['sets'];?></td>
                    <td><?php echo $row['nombre_participantes']; ?></td>
                    <td><?php echo $row['division']; ?></td>
                    <td>
                        <?php if (strcasecmp($nombreCompletoUsuario, $segundoParticipante) === 0): ?>
                            <form method="POST" action="procesar_partido.php">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="accion" value="aceptar" class="aceptar">Aceptar</button>
                                <button type="submit" name="accion" value="rechazar" class="rechazar">Rechazar</button>
                            </form>
                        <?php else: ?>
                            <em>Esperando confirmación</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No hay partidos pendientes</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<button onclick="window.location.href='home-user.php'">Volver a Inicio</button>

</body>
</html>

<?php
$conn->close();
?>
