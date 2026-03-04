<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener la liga desde la URL y sanitizarla
$ligaSeleccionada = isset($_GET['liga']) ? $_GET['liga'] : '';

// Obtener divisiones de la liga seleccionada
$stmtDivisiones = $conn->prepare("SELECT DISTINCT division FROM partidos WHERE liga = ?");
$stmtDivisiones->bind_param("s", $ligaSeleccionada);
$stmtDivisiones->execute();
$divisionesResult = $stmtDivisiones->get_result();

// Obtener jornadas de la liga seleccionada
$stmtJornadas = $conn->prepare("SELECT DISTINCT id_jornada FROM partidos WHERE liga = ? ORDER BY id_jornada DESC");
$stmtJornadas->bind_param("s", $ligaSeleccionada);
$stmtJornadas->execute();
$jornadasResult = $stmtJornadas->get_result();

// Obtener valores seleccionados
$divisionSeleccionada = isset($_GET['division']) ? $_GET['division'] : '';
$jornadaSeleccionada = isset($_GET['jornada']) ? $_GET['jornada'] : '';

// Construir consulta de partidos con filtros
$sql = "SELECT * FROM partidos WHERE (estado = 'procesado' OR estado = 'aceptado') AND liga = ?";
$params = [$ligaSeleccionada];
$types = "s";

if (!empty($divisionSeleccionada)) {
    $sql .= " AND division = ?";
    $params[] = $divisionSeleccionada;
    $types .= "s";
}
if (!empty($jornadaSeleccionada)) {
    $sql .= " AND id_jornada = ?";
    $params[] = $jornadaSeleccionada;
    $types .= "s";
}
$sql .= " ORDER BY division ASC, id_jornada DESC";

// Preparar y ejecutar consulta
$stmtPartidos = $conn->prepare($sql);
$stmtPartidos->bind_param($types, ...$params);
$stmtPartidos->execute();
$result = $stmtPartidos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Partidos - <?php echo htmlspecialchars($ligaSeleccionada); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        select, button {
            padding: 8px;
            margin: 5px;
        }
        .volver {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        .volver:hover {
            background: darkred;
        }
    </style>
</head>
<body>

    <h2>Resultados de Partidos - Liga: <?php echo htmlspecialchars($ligaSeleccionada); ?></h2>

    <form method="GET">
        <input type="hidden" name="liga" value="<?php echo htmlspecialchars($ligaSeleccionada); ?>">

        <label for="division">Seleccionar División:</label>
        <select name="division" id="division">
            <option value="">Todas</option>
            <?php while ($row = $divisionesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['division']); ?>" 
                    <?php if ($row['division'] == $divisionSeleccionada) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['division']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="jornada">Seleccionar Jornada:</label>
        <select name="jornada" id="jornada">
            <option value="">Todas</option>
            <?php while ($row = $jornadasResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['id_jornada']); ?>" 
                    <?php if ($row['id_jornada'] == $jornadaSeleccionada) echo 'selected'; ?>>
                    Jornada <?php echo htmlspecialchars($row['id_jornada']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>División</th>
                <th>Jornada</th>
                <th>Participantes</th>
                <th>Resultado</th>
                <th>Sets</th> 
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($row['division']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_jornada']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_participantes']); ?></td>
                        <td><?php echo htmlspecialchars($row['resultado']); ?></td>
                        <td><?php echo isset($row['sets']) ? htmlspecialchars($row['sets']) : 'N/A'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No hay partidos registrados para esta liga.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <button class="volver" onclick="window.location.href='consultar-ligas-pasadas.php'">Volver atrás</button>

</body>
</html>

<?php
// Cerrar conexiones
$stmtDivisiones->close();
$stmtJornadas->close();
$stmtPartidos->close();
$conn->close();
?>
