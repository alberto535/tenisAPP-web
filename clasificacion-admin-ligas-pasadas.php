<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 14-03-2025
    Resumen: Obtiene por el URL el nombre de la liga seleccionada, muestra un menú desplegable para seleccionar la division
     y llama a otro obtener-clasificacion-admin-ligas-pasadas.php para cargar la tabla de clasificacion.
*/
session_start(); // Iniciar sesión

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener la liga desde la URL
$liga = isset($_GET['liga']) ? $_GET['liga'] : null;

if (!$liga) {
    die("Error: No se ha especificado una liga en la URL.");
}

// Obtener la división del usuario en la liga especificada
$sqlUsuario = "SELECT division FROM usuarios WHERE liga = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->bind_param("s", $liga);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

// Si no hay usuarios, mostrar mensaje y detener ejecución
if ($resultUsuario->num_rows == 0) {
    echo "<h2 style='text-align: center; color: red;'>No hay usuarios en esta liga.</h2>";
    exit();
}

$rowUsuario = $resultUsuario->fetch_assoc();
$divisionUsuario = $rowUsuario['division'];

// Obtener todas las divisiones de la liga seleccionada
$divisionesQuery = "SELECT DISTINCT division FROM clasificacion WHERE liga = ?";
$stmtDivisiones = $conn->prepare($divisionesQuery);
$stmtDivisiones->bind_param("s", $liga);
$stmtDivisiones->execute();
$resultDivisiones = $stmtDivisiones->get_result();

$divisiones = [];
while ($row = $resultDivisiones->fetch_assoc()) {
    $divisiones[] = $row['division'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clasificación - Liga <?php echo htmlspecialchars($liga); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    <h1>Clasificación de la Liga <?php echo htmlspecialchars($liga); ?></h1>

    <!-- Menú desplegable para seleccionar división -->
    <div class="filter-container">
        <label for="division">Selecciona una división: </label>
        <select id="division" onchange="cargarClasificacion()">
            <?php foreach ($divisiones as $division): ?>
                <option value="<?php echo htmlspecialchars($division); ?>" 
                    <?php echo ($division == $divisionUsuario) ? 'selected' : ''; ?>>
                    <?php echo "División " . htmlspecialchars($division); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Contenedor de la tabla -->
    <div id="tabla-clasificacion">
        <!-- Aquí se cargará la tabla con AJAX -->
    </div>

    <script>
        function cargarClasificacion() {
            var divisionSeleccionada = $("#division").val();
            var liga = "<?php echo htmlspecialchars($liga); ?>";

            $.ajax({
                url: "obtener-clasificacion-admin-ligas-pasadas.php",
                method: "GET",
                data: { liga: liga, division: divisionSeleccionada },
                success: function(response) {
                    $("#tabla-clasificacion").html(response);
                },
                error: function() {
                    $("#tabla-clasificacion").html("<p>Error al cargar la clasificación.</p>");
                }
            });
        }

        // Cargar la clasificación automáticamente al iniciar
        $(document).ready(function() {
            cargarClasificacion();
        });
    </script>

</body>
</html>
