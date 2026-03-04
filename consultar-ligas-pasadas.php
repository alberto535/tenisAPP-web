<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 14-03-2025
    Resumen: Extrae información de la tabla ligas, y muestra en una tabla la informacion extraida y dos enlaces:
    uno para ver la clasificacion y otro para ver los resultados.
*/
$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

date_default_timezone_set('UTC');
$fecha_actual = date('Y-m-d');

// Consulta para obtener solo las ligas finalizadas con fecha válida
$query = "SELECT nombre, fecha_creacion, fecha_finalizacion 
          FROM ligas 
          WHERE fecha_finalizacion IS NOT NULL AND fecha_finalizacion <> '0000-00-00' 
          ORDER BY fecha_finalizacion DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligas Pasadas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        a {
            text-decoration: none;
            color: #007BFF;
            margin: 0 5px;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn-back {
            background-color: #FF5733;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Ligas Finalizadas</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha de Creación</th>
                <th>Fecha de Finalización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_creacion']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_finalizacion']); ?></td>
                    <td>
                        <a href="clasificacion-admin-ligas-pasadas.php?liga=<?php echo urlencode($row['nombre']); ?>">Ver Clasificación</a>
                        <a href="resultados-admin-ligas-pasadas.php?liga=<?php echo urlencode($row['nombre']); ?>">Ver Resultados</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="h_administrar_ligas.php">
        <button class="btn-back">Volver Atrás</button>
    </a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
