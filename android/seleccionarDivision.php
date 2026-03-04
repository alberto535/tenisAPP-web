<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$database = "l&r";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    foreach ($data as $usuario) {
        $correo = $usuario['correo'];
        $division = (int)$usuario['division'];
        $liga = $usuario['liga'];

        // Actualizar la división y liga en la tabla usuarios
        $stmt = $conn->prepare("UPDATE usuarios SET division = ?, liga = ? WHERE correo = ?");
        $stmt->bind_param("iss", $division, $liga, $correo);
        $stmt->execute();

        // Insertar en la tabla clasificacion si no existe
        $stmt_check = $conn->prepare("SELECT * FROM clasificacion WHERE correo = ?");
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows == 0) {
            $stmt_insert = $conn->prepare("INSERT INTO clasificacion (nombre, apellidos, correo, puntuaje, buch, m_buch, liga, division) SELECT nombre, apellidos, correo, 0, 0, 0, liga, division FROM usuarios WHERE correo = ?");
            $stmt_insert->bind_param("s", $correo);
            $stmt_insert->execute();
        }
    }

    echo json_encode(["message" => "Divisiones y ligas actualizadas exitosamente, clasificación sincronizada"]);
} else {
    echo json_encode(["error" => "Método no permitido"]);
}

$conn->close();
?>
