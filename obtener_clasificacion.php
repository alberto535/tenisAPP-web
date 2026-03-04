<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 05-03-2025
    Resumen: Se conecta a la base de datos, recoge la liga y división del archivo anterior, y se realiza un cargado de los datos de la liga del usuario logueado
    de la tabla clasificacion y se muestra en una tabla
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

if (!isset($_POST['liga']) || !isset($_POST['division'])) {
    $sqlUsuario = "SELECT liga, division FROM usuarios WHERE correo = ?";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->bind_param("s", $nombreUsuario);
    $stmtUsuario->execute();
    $resultUsuario = $stmtUsuario->get_result();

    if ($resultUsuario->num_rows > 0) {
        $rowUsuario = $resultUsuario->fetch_assoc();
        $liga = $rowUsuario['liga'];
        $division = $rowUsuario['division'];
    } else {
        die("Error: No se encontró información del usuario.");
    }
} else {
    // Si se reciben por POST, usarlos directamente
    $liga = $_POST['liga'];
    $division = $_POST['division'];
}

// Evitar SQL Injection asegurando que sean cadenas seguras
$liga = $conn->real_escape_string($liga);
$division = $conn->real_escape_string($division);

// Obtener los jugadores y calcular Buch y M-Buch
$sql = "SELECT nombre, apellidos, puntuaje, buch, `m-buch` FROM clasificacion WHERE liga = ? AND division = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $liga, $division);
$stmt->execute();
$result = $stmt->get_result();

$jugadores = [];

while ($row = $result->fetch_assoc()) {
    $nombre_completo = $row['nombre'] . " " . $row['apellidos'];
    $jugadores[$nombre_completo] = [
        'nombre' => $row['nombre'],
        'apellidos' => $row['apellidos'],
        'puntuaje' => $row['puntuaje'],
        'buch' => $row['buch'],
        'm_buch' => $row['m-buch'] // Uso correcto de backticks en SQL para `m-buch`
    ];
}

// Ordenar los jugadores por puntuaje DESC, luego por Buch DESC y finalmente por M-Buch DESC
usort($jugadores, function ($a, $b) {
    if ($a['puntuaje'] != $b['puntuaje']) {
        return $b['puntuaje'] - $a['puntuaje'];
    }
    if ($a['buch'] != $b['buch']) {
        return $b['buch'] - $a['buch'];
    }
    return $b['m_buch'] - $a['m_buch'];
});

// Mostrar la tabla de clasificación
echo '<table border="1">
        <tr>
            <th>Posición</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Puntos</th>
            <th>Buchholz</th>
            <th>M-Buchholz</th>
        </tr>';

$posicion = 1;

foreach ($jugadores as $jugador) {
    echo "<tr>
            <td>{$posicion}</td>
            <td>{$jugador['nombre']}</td>
            <td>{$jugador['apellidos']}</td>
            <td>{$jugador['puntuaje']}</td>
            <td>{$jugador['buch']}</td>
            <td>{$jugador['m_buch']}</td>
          </tr>";
    $posicion++;
}

echo '</table>';

// Botón para volver a home.php
echo '<br><div style="text-align: center;">
        <button onclick="window.location.href=\'home-user.php\'" 
                style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer;">
            Volver a Inicio
        </button>
      </div>';

$conn->close();
?>
