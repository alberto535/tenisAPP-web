<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 16-03-2025
    Resumen: Realiza la conexión a la base de datos y obtiene informacion de las ligas que no tengan fecha de finalizacion. Y restringe el uso de 
    generar nueva jornada, ha que se haya acabado una jornada antes y que no haya partidos en la tabla jornada_partidos.  Aparece una tabla donde 
    ademas esta opción y otras aparecen en un menu desplegable en la misma fila de la liga, opciones como: terminar liga, ver resultados, 
    ver clasificacion, revisar jornada activa y generar nueva jornada.
*/

$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
date_default_timezone_set('UTC');

// Consulta para obtener las ligas sin fecha de finalización
$query = "SELECT nombre, fecha_creacion FROM ligas WHERE fecha_finalizacion = '0000-00-00' ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligas Activas</title>
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
        select, button {
            margin-top: 10px;
            padding: 8px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h2>Ligas Activas</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
                $ligaNombre = $row['nombre'];
                $fechaFinReciente = false;
                $puedeGenerarNuevaJornada = true; // Inicializamos como true

                // Consulta para la última jornada de esta liga
                $jornadaQuery = "SELECT fecha_fin FROM jornada WHERE liga = ? and estado ='cerrada' ORDER BY id DESC LIMIT 1";
                $stmtJornada = $conn->prepare($jornadaQuery);
                $stmtJornada->bind_param("s", $ligaNombre);
                $stmtJornada->execute();
                $stmtJornada->bind_result($fechaFin);
                $stmtJornada->fetch();
                    // Comprobar si ha pasado suficiente tiempo (3 días en este caso)
                    $fechaFinReciente = (strtotime($fechaFin) >= strtotime("-3 days"));
                $stmtJornada->close();

                // Verificar si ya existen partidos en la tabla jornada_partidos para esta liga
                $partidosQuery = "SELECT COUNT(*) FROM jornada_partidos WHERE id_jornada IN (SELECT id FROM jornada WHERE liga = ? AND fecha_fin = '0000-00-00')";
                $stmtPartidos = $conn->prepare($partidosQuery);
                $stmtPartidos->bind_param("s", $ligaNombre);
                $stmtPartidos->execute();
                $stmtPartidos->bind_result($numPartidos);
                $stmtPartidos->fetch();
                if ($numPartidos > 0) {
                    $puedeGenerarNuevaJornada = false; // Si hay partidos asignados, no se puede generar una nueva jornada
                }
                $stmtPartidos->close();
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($ligaNombre); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_creacion']); ?></td>
                    <td>
                        <select onchange="handleAction(this, '<?php echo urlencode($ligaNombre); ?>', <?php echo $fechaFinReciente ? 'true' : 'false'; ?>, <?php echo $puedeGenerarNuevaJornada ? 'true' : 'false'; ?>)">
                            <option value="">Seleccionar acción</option>
                            <option value="terminar">Terminar Liga</option>
                            <option value="clasificacion">Ver Clasificación</option>
                            <option value="resultados">Ver Resultados</option>
                            <option value="jornada_activa">Revisar Jornada Activa</option>
                            <option value="nueva_jornada" <?php echo ($puedeGenerarNuevaJornada && $fechaFinReciente) ? '' : 'disabled'; ?>>Generar Nueva Jornada</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        function handleAction(select, ligaNombre, fechaFinReciente, puedeGenerarNuevaJornada) {
            let action = select.value;
            if (action) {
                let url = "";
                if (action === "nueva_jornada" && (!fechaFinReciente || !puedeGenerarNuevaJornada)) {
                    alert("No se puede generar una nueva jornada hasta que no se haya cerrado la jornada anterior o ya existen partidos en la jornada activa.");
                    return;
                }
                switch (action) {
                    case "terminar":
                        if (confirm("¿Estás seguro que quieres acabar esta liga?")) {
                            url = "terminar-liga.php?liga=" + ligaNombre;
                        } else {
                            url = "administrar-ligas-activas.php";
                        }
                        break;
                    case "clasificacion":
                        url = "clasificacion-admin.php?liga=" + ligaNombre;
                        break;
                    case "resultados":
                        url = "resultados_admin.php?liga=" + ligaNombre;
                        break;
                    case "jornada_activa":
                        url = "jornada_activa.php?liga=" + ligaNombre;
                        break;
                    case "nueva_jornada":
                        url = "nueva_jornada.php?liga=" + ligaNombre;
                        break;
                }
                if (url) {
                    window.location.href = url;
                }
            }
        }
    </script>

    <a href="h_administrar_ligas.php">
        <button style="background-color: #FF5733; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
            Volver Atrás
        </button>
    </a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
