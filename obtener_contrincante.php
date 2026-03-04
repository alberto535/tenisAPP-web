<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enfrentamiento = $_POST['enfrentamiento'];
    $usuario = $_POST['usuario'];

    // Separar los nombres
    $jugadores = explode(" vs ", $enfrentamiento);
    if (count($jugadores) != 2) {
        echo json_encode(['error' => 'Formato de enfrentamiento inválido.']);
        exit;
    }

    $j1 = trim($jugadores[0]);
    $j2 = trim($jugadores[1]);

    // Determinar quién es el contrincante
    $contrincante = (strcasecmp($j1, $usuario) === 0) ? $j2 : $j1;

    // Separar nombre y apellidos
    $nombre_parts = explode(" ", $contrincante);
    $nombre = array_shift($nombre_parts);
    $apellidos = implode(" ", $nombre_parts);

    // Buscar al contrincante en la base de datos
    $sql = "SELECT nombre, apellidos, telefono FROM usuarios WHERE nombre = ? AND apellidos = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombre, $apellidos);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'nombre' => $row['nombre'],
            'apellidos' => $row['apellidos'],
            'telefono' => $row['telefono']
        ]);
    } else {
        echo json_encode(['error' => 'Contrincante no encontrado.']);
    }
} else {
    echo json_encode(['error' => 'Acceso denegado.']);
}
?>
