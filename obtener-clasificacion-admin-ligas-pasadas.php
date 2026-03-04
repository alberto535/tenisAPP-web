<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 14-03-2025
    Resumen: Obtiene por el URL el nombre de la liga y la division, realiza un cargado de los datos en la tabla clasificacion. Muestra una 
    tabla con la informacion obtenida.
*/

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$liga = isset($_GET['liga']) ? $_GET['liga'] : null;
$division = isset($_GET['division']) ? $_GET['division'] : null;

if (!$liga || !$division) {
    die("Error: Falta información.");
}

// Consulta para obtener la clasificación ordenada
$sql = "SELECT nombre, apellidos, correo, puntuaje, buch, `m-buch`
        FROM clasificacion 
        WHERE liga = ? AND division = ?
        ORDER BY puntuaje DESC, buch DESC, `m-buch` DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $liga, $division);
$stmt->execute();
$result = $stmt->get_result();


$posicion = 1;
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Posicion</th><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Puntos</th><th>Buch</th><th>M-Buch</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$posicion}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['apellidos']}</td>
                <td>{$row['correo']}</td>
                <td>{$row['puntuaje']}</td>
                <td>{$row['buch']}</td>
                <td>{$row['m-buch']}</td>
              </tr>";
              $posicion++;
    }

    echo "</table>";
} else {
    echo "<p>No hay datos disponibles.</p>";
}

// Botón para volver a home.php
echo '<br><div style="text-align: center;">
        <button onclick="window.location.href=\'consultar-ligas-pasadas.php\'" 
                style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer;">
            Volver atrás
        </button>
      </div>';

$conn->close();
?>
