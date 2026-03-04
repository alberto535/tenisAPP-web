<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener los datos de la tabla usuarios
$sqlUsuarios = "SELECT nombre, apellidos, division, correo FROM usuarios";
$resultUsuarios = $conn->query($sqlUsuarios);

if ($resultUsuarios->num_rows > 0) {
    // Recorrer los usuarios y agregar a la tabla clasificacion
    while ($row = $resultUsuarios->fetch_assoc()) {
        $nombre = $conn->real_escape_string($row['nombre']);
        $apellidos = $conn->real_escape_string($row['apellidos']);
        $division = $conn->real_escape_string($row['division']);
        $correo = $conn->real_escape_string($row['correo']);

        // Insertar en la tabla clasificacion si no existe el correo
        $sqlInsert = "
            INSERT INTO clasificacion (nombre, apellidos, division, correo, puntuaje)
            VALUES ('$nombre', '$apellidos', '$division', '$correo', 0)
            ON DUPLICATE KEY UPDATE puntuaje = 0
        ";

        if ($conn->query($sqlInsert) === TRUE) {
           
        } else {
            echo "Error al insertar usuario $correo: " . $conn->error . "<br>";
        }
    }
} else {
    echo "No se encontraron usuarios en la tabla usuarios.";
}


// Cerrar conexión
$conn->close();
?>
