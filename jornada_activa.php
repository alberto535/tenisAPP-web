<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 19-03-2025
    Resumen: Realiza la conexión a la base de datos y recoge la liga desde el URL. Se muestran los partidos con estado aceptado de esa liga,
    donde la jornada este en estado activo, es decir la jornada actual. Y los muestra en una tabla. Pudiendo pinchar en el boton de acabar
    jornada que finalizaria la jornada actual.
*/

session_start();

// Verificar sesión
if (!isset($_SESSION['correo']) || empty($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

// Verificar que se haya recibido la liga por GET
if (!isset($_GET['liga']) || empty($_GET['liga'])) {
    die("Liga no especificada en los parámetros.");
}

$liga = $_GET['liga'];

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener la jornada activa de esa liga
$sqlJornada = "SELECT id FROM jornada WHERE estado = 'activo' AND liga = ? LIMIT 1";
$stmtJornada = $conn->prepare($sqlJornada);
$stmtJornada->bind_param("s", $liga);
$stmtJornada->execute();
$resultJornada = $stmtJornada->get_result();

$idJornadaActiva = null;
if ($resultJornada->num_rows > 0) {
    $rowJornada = $resultJornada->fetch_assoc();
    $idJornadaActiva = $rowJornada['id'];
}

// Buscar partidos aceptados de la jornada activa en esa liga
$partidosAceptados = [];
if ($idJornadaActiva !== null) {
    $sqlPartidos = "SELECT * FROM partidos WHERE estado = 'aceptado' AND id_jornada = ? AND liga = ?";
    $stmtPartidos = $conn->prepare($sqlPartidos);
    $stmtPartidos->bind_param("is", $idJornadaActiva, $liga);
    $stmtPartidos->execute();
    $resultPartidos = $stmtPartidos->get_result();

    while ($row = $resultPartidos->fetch_assoc()) {
        $partidosAceptados[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Partidos Aceptados - Jornada Actual</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 20px; }
        table { width: 90%; margin: auto; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007B00; color: white; }
        .boton-jornada {
            background-color: #ff9900;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>Partidos Aceptados - Jornada Actual (Liga: <?php echo htmlspecialchars($liga); ?>)</h2>

<?php if ($idJornadaActiva === null): ?>
    <p>No hay ninguna jornada activa para esta liga.</p>
<?php elseif (count($partidosAceptados) === 0): ?>
    <p>No hay partidos aceptados en esta jornada.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID Partido</th>
                <th>Fecha</th>
                <th>Participantes</th>
                <th>Resultado</th>
                <th>Sets</th>
                <th>División</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partidosAceptados as $partido): ?>
                <tr>
                    <td><?php echo $partido['id']; ?></td>
                    <td><?php echo $partido['fecha']; ?></td>
                    <td><?php echo $partido['nombre_participantes']; ?></td>
                    <td><?php echo $partido['resultado']; ?></td>
                    <td><?php echo $partido['sets']; ?></td>     
                    <td><?php echo $partido['division']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Botones con espacio -->
<div style="margin-top: 30px;">
    <?php if ($idJornadaActiva !== null): ?>
        <!-- Botón para acabar jornada -->
        <form action="acabar_jornada.php?" method="post" style="display: inline-block; margin-right: 20px;">
            <input type="hidden" name="id_jornada" value="<?php echo $idJornadaActiva; ?>">
            <input type="hidden" name="liga" value="<?php echo htmlspecialchars($liga); ?>">
            <button type="submit" class="boton-jornada">Acabar Jornada</button>
        </form>
    <?php endif; ?>

    <!-- Botón de volver siempre disponible -->
    <a href="h_administrar_ligas.php" style="text-decoration: none;">
        <button style="background-color: #FF5733; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
            Volver Atrás
        </button>
    </a>
</div>

</body>
</html>

<?php $conn->close(); ?>
