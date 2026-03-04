<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 10-03-2025
    Resumen: Substrae el id de la jornada sin fecha de fin y con estado igual a activo, y busca el partido con el nombre y apellidos
    del participante local igual al del que se ha logueado. Y muestra un formulario para introducir los sets, resultado y fecha, y 
    aparece información acerca del contrincante
*/
// Inicia la sesión para acceder a variables de sesión
session_start();

// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establece el juego de caracteres a UTF-8
$conn->set_charset("utf8mb4");

// Verifica si el usuario ha iniciado sesión (correo en sesión)
if (!isset($_SESSION['correo'])) {
    die("Error: No has iniciado sesión.");
}

// Guarda el correo del usuario logueado
$nombreUsuario = $_SESSION['correo'];

// Obtiene datos del usuario desde la tabla usuarios
$sqlUsuario = "SELECT nombre, apellidos, division, liga FROM usuarios WHERE correo = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->bind_param("s", $nombreUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

// Si se encuentra el usuario, se guardan sus datos
if ($resultUsuario->num_rows > 0) {
    $rowUsuario = $resultUsuario->fetch_assoc();
    $nombreCompletoUsuario = $rowUsuario['nombre'] . ' ' . $rowUsuario['apellidos'];
    $divisionUsuario = $rowUsuario['division'];
    $ligaUsuario = $rowUsuario['liga'];
} else {
    die("Error: Usuario no encontrado.");
}

// Busca la jornada activa (fecha_fin = 0000-00-00 y estado = activo)
$sqlJornada = "SELECT id FROM jornada WHERE fecha_fin = '0000-00-00' AND estado = 'activo' AND liga = '$ligaUsuario' LIMIT 1";
$resultJornada = $conn->query($sqlJornada);

if ($resultJornada->num_rows > 0) {
    $rowJornada = $resultJornada->fetch_assoc();
    $id_jornada = $rowJornada['id'];
} else {
    die("No hay jornadas activas. Primero debes crear una jornada.");
}

// Obtiene el último ID de la tabla partidos para generar uno nuevo
$sqlUltimoID = "SELECT MAX(id) as ultimo_id FROM partidos";
$resultUltimoID = $conn->query($sqlUltimoID);
$rowUltimoID = $resultUltimoID->fetch_assoc();
$id_nuevo = $rowUltimoID['ultimo_id'] + 1;

// Busca los enfrentamientos de la jornada activa
$sqlEnfrentamientos = "SELECT jp.nombre_participantes 
FROM jornada_partidos jp
JOIN jornada j ON jp.id_jornada = j.id
WHERE j.fecha_fin = '0000-00-00' 
AND j.id = ? AND jp.liga = ?";
$stmtEnfrentamientos = $conn->prepare($sqlEnfrentamientos);
$stmtEnfrentamientos->bind_param("is", $id_jornada, $ligaUsuario);
$stmtEnfrentamientos->execute();
$resultEnfrentamientos = $stmtEnfrentamientos->get_result();

// Inicializa variables para el enfrentamiento
$nombre_participantes_auto = "";
$contrincante = "";
$datosContrincante = null;
$puede_registrar = false;

// Recorre los enfrentamientos buscando si el usuario participa en alguno
while ($row = $resultEnfrentamientos->fetch_assoc()) {
    $enfrentamiento = $row['nombre_participantes'];
    $jugadores = explode(" vs ", $enfrentamiento);
    if (count($jugadores) === 2) {
        $j1 = trim($jugadores[0]);
        $j2 = trim($jugadores[1]);

        // Si el usuario es el jugador local
        if (strcasecmp($j1, $nombreCompletoUsuario) === 0) {
            $nombre_participantes_auto = $enfrentamiento;
            $contrincante = $j2;
            $puede_registrar = true;

        // Si el usuario es el visitante
        } elseif (strcasecmp($j2, $nombreCompletoUsuario) === 0) {
            $nombre_participantes_auto = $enfrentamiento;
            $contrincante = $j1;
            $puede_registrar = false;
        }

        // Si se encontró contrincante, busca sus datos
        if ($contrincante) {
            $nombre_parts = explode(" ", $contrincante);
            $nombre = array_shift($nombre_parts);
            $apellidos = implode(" ", $nombre_parts);
            
            $sqlContrincante = "SELECT nombre, apellidos, telefono FROM usuarios WHERE CONCAT(nombre, ' ', apellidos) = ?";
            $stmtContrincante = $conn->prepare($sqlContrincante);
            $stmtContrincante->bind_param("s", $contrincante);
            
            $stmtContrincante->execute();
            $resultContrincante = $stmtContrincante->get_result();

            if ($resultContrincante && $resultContrincante->num_rows > 0) {
                $datosContrincante = $resultContrincante->fetch_assoc();
            }
            break; // Sale del bucle al encontrar el enfrentamiento del usuario
        }
    }
}

// Si el formulario fue enviado y el usuario puede registrar el partido
if ($_SERVER["REQUEST_METHOD"] == "POST" && $puede_registrar) {
    $fecha = $_POST['fecha'];
    $resultado = $_POST['resultado'];
    $sets = $_POST['sets'];
    $nombre_participantes = $_POST['nombre_participantes'];
    $division = $_POST['division'];
    $liga = $_POST['liga'];

    // Validaciones del resultado y sets
    if (!preg_match('/^\d+-\d+$/', $resultado)) {
        die("Error: El resultado debe estar en formato 'X-Y', por ejemplo, '2-1'.");
    }

    if (!preg_match('/^(\d+-\d+\s*)+$/', $sets)) {
        die("Error: Los sets deben estar en formato 'X-Y X-Y X-Y', por ejemplo, '6-3 4-6 6-5'.");
    }

    // Validación de que el número de sets ganados coincide con el resultado
    list($sets_ganados_p1, $sets_ganados_p2) = explode("-", $resultado);
    $sets_array = explode(" ", $sets);
    $contador_p1 = 0;
    $contador_p2 = 0;

    foreach ($sets_array as $set) {
        list($p1, $p2) = explode("-", $set);
        if ($p1 > $p2) {
            $contador_p1++;
        } else {
            $contador_p2++;
        }
    }

    if ($sets_ganados_p1 != $contador_p1 || $sets_ganados_p2 != $contador_p2) {
        die("Error: La cantidad de sets ganados no coincide con el resultado ingresado.");
    }

    // Inserta el partido con estado "pendiente" (de confirmación)
    $sqlInsert = "INSERT INTO partidos (id, fecha, resultado, sets, nombre_participantes, division, liga, estado, id_jornada) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("issssssi", $id_nuevo, $fecha, $resultado, $sets, $nombre_participantes, $division, $liga, $id_jornada);

    if ($stmt->execute()) {
        echo "<script>alert('Partido registrado correctamente.');</script>";
        echo "<script>alert('El partido ha sido enviado al otro usuario para su confirmación.');</script>";
    } else {
        echo "Error al registrar el partido: " . $conn->error;
    }
}

// Cierra la conexión con la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jornada activa</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; }
        form { background: white; padding: 30px; max-width: 400px; margin: auto; border-radius: 5px; }
        input, select { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: green; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: darkgreen; }
        .back-button { margin-top: 20px; background: red; }
        .back-button:hover { background: darkred; }
        #datosContrincante { background:#e0e0e0; padding:10px; border-radius:5px; margin-top:10px; }
    </style>
</head>
<body>

    <h2>Jornada activa</h2>

    <?php if ($nombre_participantes_auto && $datosContrincante): ?>
        <?php if ($puede_registrar): ?>
            <form method="POST">
                <label for="fecha">Fecha del Partido:</label>
                <input type="date" name="fecha" required>

                <label for="resultado">Resultado (Ejemplo: 2-1):</label>
                <input type="text" name="resultado" placeholder="Ejemplo: 2-1" required>

                <label for="sets">Resultados de Sets (Ejemplo: 6-3 2-1 6-5):</label>
                <input type="text" name="sets" placeholder="Ejemplo: 6-3 2-1 6-5" required>

                <input type="hidden" name="nombre_participantes" value="<?php echo htmlspecialchars($nombre_participantes_auto); ?>">
                <p><strong>Partido actual:</strong> <?php echo htmlspecialchars($nombre_participantes_auto); ?></p>

                <div id="datosContrincante">
                    <strong>Datos del Contrincante:</strong><br>
                    Nombre: <?php echo htmlspecialchars($datosContrincante['nombre']); ?><br>
                    Apellidos: <?php echo htmlspecialchars($datosContrincante['apellidos']); ?><br>
                    Teléfono: <?php echo htmlspecialchars($datosContrincante['telefono']); ?>
                </div>

                <label for="division">División:</label>
                <p><strong><?php echo htmlspecialchars($divisionUsuario); ?></strong></p>
                <input type="hidden" name="division" value="<?php echo htmlspecialchars($divisionUsuario); ?>">

                <label for="liga">Liga:</label>
                <p><strong><?php echo htmlspecialchars($ligaUsuario); ?></strong></p>
                <input type="hidden" name="liga" value="<?php echo htmlspecialchars($ligaUsuario); ?>">

                <button type="submit">Registrar Partido</button>
            </form>
        <?php else: ?>
            <div id="datosContrincante">
                <strong>Tu rival es:</strong><br>
                Nombre: <?php echo htmlspecialchars($datosContrincante['nombre']); ?><br>
                Apellidos: <?php echo htmlspecialchars($datosContrincante['apellidos']); ?><br>
                Teléfono: <?php echo htmlspecialchars($datosContrincante['telefono']); ?>
            </div>
            <p style="color: red;">Solo tu rival puede registrar el partido. Espera a que lo haga.</p>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: orange;">No tienes enfrentamientos asignados en la jornada activa.</p>
    <?php endif; ?>

    <button class="back-button" onclick="window.location.href='home-user.php'">Volver a Inicio</button>

</body>
</html>