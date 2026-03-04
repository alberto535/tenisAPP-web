<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 13-03-2025
    Resumen: Realiza la conexión a la base de datos y muestra una tabla con los usuarios con estado activo y sin liga. 
    Establece a los usuarios seleccionados una division y una liga, donde se establece tambien el nombre de la liga.
*/
$conn = new mysqli("localhost", "root", "", "l&r");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener usuarios sin liga
$sql = "SELECT correo, nombre, apellidos, telefono, dni, division FROM usuarios WHERE division IS NULL AND (liga IS NULL OR liga = '')";
$result = $conn->query($sql);

// Obtener ligas existentes
$sql_ligas = "SELECT nombre FROM ligas";
$result_ligas = $conn->query($sql_ligas);

// Procesar envío del formulario (divisiones y creación de liga)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar divisiones si se enviaron
    if (isset($_POST['division'])) {
        foreach ($_POST['division'] as $correo => $division) {
            $sql_update_division = "UPDATE usuarios SET division = ? WHERE correo = ?";
            $stmt = $conn->prepare($sql_update_division);
            $stmt->bind_param("is", $division, $correo);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Crear liga si se envió nombre y usuarios seleccionados
    if (isset($_POST['nombre_liga']) && isset($_POST['usuarios_seleccionados']) && count($_POST['usuarios_seleccionados']) > 0) {
        $nombre_liga = $_POST['nombre_liga'];
        $fecha_creacion = date("Y-m-d H:i:s");

        // Crear liga
        $sql_insert_liga = "INSERT INTO ligas (nombre, fecha_creacion) VALUES (?, ?) ON DUPLICATE KEY UPDATE fecha_creacion=?";
        $stmt = $conn->prepare($sql_insert_liga);
        $stmt->bind_param("sss", $nombre_liga, $fecha_creacion, $fecha_creacion);
        $stmt->execute();
        $stmt->close();

        // Asignar liga a usuarios seleccionados e insertarlos en clasificación
        foreach ($_POST['usuarios_seleccionados'] as $correo_usuario) {
            $liga_stmt = $conn->prepare("UPDATE usuarios SET liga = ? WHERE correo = ?");
            $liga_stmt->bind_param("ss", $nombre_liga, $correo_usuario);
            $liga_stmt->execute();
            $liga_stmt->close();

            $nombre_stmt = $conn->prepare("SELECT nombre, apellidos, division FROM usuarios WHERE correo = ?");
            $nombre_stmt->bind_param("s", $correo_usuario);
            $nombre_stmt->execute();
            $nombre_result = $nombre_stmt->get_result()->fetch_assoc();
            $nombre_stmt->close();

            $clasif_stmt = $conn->prepare("INSERT INTO clasificacion (liga, nombre, apellidos, correo, puntuaje, division) VALUES (?, ?, ?, ?, 0, ?)");
            $clasif_stmt->bind_param("ssssi", $nombre_liga, $nombre_result['nombre'], $nombre_result['apellidos'], $correo_usuario, $nombre_result['division']);
            $clasif_stmt->execute();
            $clasif_stmt->close();
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['nombre_liga'])) {
        // Si no se seleccionó ningún usuario pero se puso nombre de liga
        echo "<script>alert('Debes seleccionar al menos un usuario para crear la liga'); window.history.back();</script>";
        exit;
    }

    // Redirección general
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Usuarios a Liga</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0fdf4;
            margin: 20px;
            text-align: center;
        }
        h1, h2, h3 {
            color: #2d6a4f;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0px 5px 15px rgba(0, 128, 0, 0.3);
            background: white;
        }
        th {
            background-color: #40916c;
            color: white;
            padding: 15px;
            text-transform: uppercase;
            font-weight: bold;
        }
        td, th {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #d8f3dc;
        }
        tr:hover {
            background-color: #95d5b2;
            transition: 0.3s;
        }
        input[type="text"], select {
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #f0fdf4;
        }
        button {
            background-color: #1b4332;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        button:hover {
            background-color: #081c15;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #b7e4c7;
            padding: 12px;
            margin: 5px;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Asignar Usuarios a una Liga</h1>

    <?php if ($result->num_rows > 0): ?>
        <form action="" method="POST">
            <table>
                <tr>
                    <th>Seleccionar</th>
                    <th>Correo</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Teléfono</th>
                    <th>DNI</th>
                    <th>División</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="usuarios_seleccionados[]" value="<?= htmlspecialchars($row['correo']) ?>">
                        </td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['apellidos']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['dni']) ?></td>
                        <td>
                            <select name='division[<?= htmlspecialchars($row['correo']) ?>]'>
                                <option value='0'>Sin división</option>
                                <option value='1'>División 1</option>
                                <option value='2'>División 2</option>
                                <option value='3'>División 3</option>
                            </select>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <h2>Crear Nueva Liga</h2>
            <label for="nombre_liga">Nombre de la Liga:</label>
            <input type="text" id="nombre_liga" name="nombre_liga" required>
            <button type="submit">Crear Liga y Asignar Usuarios</button>
        </form>
    <?php else: ?>
        <p>No hay usuarios sin liga.</p>
    <?php endif; ?>

    <h3>Ligas Existentes</h3>
    <ul>
        <?php while ($liga = $result_ligas->fetch_assoc()): ?>
            <li><?= htmlspecialchars($liga['nombre']) ?></li>
        <?php endwhile; ?>
    </ul>

    <a href="h_administrar_ligas.php"><button>Volver Atrás</button></a>
</body>
</html>

<?php
$conn->close();
?>
