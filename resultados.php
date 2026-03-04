<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 06-03-2025
    Resumen: Muestra por pantalla todos los resultados que se han dado en la liga del usuario logueado, y tiene dos filtros:
    jornada y division.
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

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    die("Error: No has iniciado sesión.");
}

$nombreUsuario = $_SESSION['correo'];

// Obtener la liga del usuario logueado
$sqlLiga = "SELECT liga FROM usuarios WHERE correo = ?";
$stmtLiga = $conn->prepare($sqlLiga);
$stmtLiga->bind_param("s", $nombreUsuario);
$stmtLiga->execute();
$resultLiga = $stmtLiga->get_result();

if ($resultLiga->num_rows > 0) {
    $rowLiga = $resultLiga->fetch_assoc();
    $ligaSeleccionada = $rowLiga['liga'];
} else {
    die("Error: No se encontró la liga del usuario.");
}

// Obtener todas las divisiones únicas de la liga seleccionada
$divisiones = $conn->query("SELECT DISTINCT division FROM partidos WHERE liga = '$ligaSeleccionada'");
// Obtener todas las jornadas únicas de la liga seleccionada
$jornadas = $conn->query("SELECT DISTINCT id_jornada FROM partidos WHERE liga = '$ligaSeleccionada' ORDER BY id_jornada DESC");

$divisionSeleccionada = isset($_GET['division']) ? $_GET['division'] : '';
$jornadaSeleccionada = isset($_GET['jornada']) ? $_GET['jornada'] : '';

$sql = "SELECT * FROM partidos WHERE (estado = 'procesado' OR estado = 'aceptado') AND liga = '$ligaSeleccionada'";
if ($divisionSeleccionada) {
    $sql .= " AND division = '$divisionSeleccionada'";
}
if ($jornadaSeleccionada) {
    $sql .= " AND id_jornada = '$jornadaSeleccionada'";
}
$sql .= " ORDER BY division ASC, id_jornada DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Liga</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .filter-container {
            text-align: center;
            margin-bottom: 20px;
        }
        select {
            padding: 8px;
            font-size: 16px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Resultados de Partidos - Liga: <?php echo htmlspecialchars($ligaSeleccionada); ?></h2>
    <form method="GET">
        <label for="division">Seleccionar División:</label>
        <select name="division" id="division">
            <option value="">Todas</option>
            <?php while ($row = $divisiones->fetch_assoc()): ?>
                <option value="<?php echo $row['division']; ?>" <?php if ($row['division'] == $divisionSeleccionada) echo 'selected'; ?>>
                    <?php echo $row['division']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label for="jornada">Seleccionar Jornada:</label>
        <select name="jornada" id="jornada">
            <option value="">Todas</option>
            <?php while ($row = $jornadas->fetch_assoc()): ?>
                <option value="<?php echo $row['id_jornada']; ?>" <?php if ($row['id_jornada'] == $jornadaSeleccionada) echo 'selected'; ?>>
                    Jornada <?php echo $row['id_jornada']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filtrar</button>
    </form>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>División</th>
            <th>Jornada</th>
            <th>Participantes</th>
            <th>Resultado</th>
            <th>Sets</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['fecha']; ?></td>
                    <td><?php echo $row['division']; ?></td>
                    <td><?php echo $row['id_jornada']; ?></td>
                    <td><?php echo $row['nombre_participantes']; ?></td>
                    <td><?php echo $row['resultado']; ?></td>
                    <td><?php echo isset($row['sets']) ? $row['sets'] : 'N/A'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No hay partidos registrados para esta liga.</td>
            </tr>
        <?php endif; ?>
    </table>
    <button onclick="window.location.href='home-user.php'">Volver al inicio</button>
</body>
</html>
<?php $conn->close(); ?>
