<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 07-03-2025
    Resumen: Realiza la conexión a la base de datos. Muestra por pantalla una tabla con todos los usuarios que estan almacenados y con estado activo 
    en la tabla usuarios. Y presenta un checkbox para seleccionar a los usuarios de manera independiente.
*/


// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener usuarios de la base de datos
$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
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
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
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
        .btn-group {
            margin-bottom: 10px;
        }

        .back-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .form-group {
        text-align: center;
        margin-top: 15px;
    }

    .delete-btn {
        background-color: red;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s;
    }

    .delete-btn:hover {
        background-color: darkred;
    }
    </style>
</head>
<body>

<div class="container">
    <h2>Gestionar Usuarios</h2>
    <a href="home.php" class="back-btn">← Atrás</a>
    <!-- Botones para seleccionar o deseleccionar todos -->
    <div class="btn-group" style="text-align: center;">
        <button type="button" onclick="seleccionarTodos()">Seleccionar Todos</button>
        <button type="button" onclick="deseleccionarTodos()">Deseleccionar Todos</button>
    </div>

    <!-- 🔍 Input de búsqueda -->
    <input type="text" id="filtro" onkeyup="filtrarTabla()" placeholder="Buscar usuario por cualquier campo...">

    <form action="eliminar_usuarios.php" method="POST">
        <table id="tablaUsuarios">
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Correo</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Teléfono</th>
                    <th>DNI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><input type='checkbox' name='usuarios[]' value='" . $row['correo'] . "'></td>";
                        echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                        echo "<td>" . $row['nombre'] . "</td>";
                        echo "<td>" . $row['apellidos'] . "</td>";
                        echo "<td>" . $row['telefono'] . "</td>";
                        echo "<td>" . $row['dni'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay usuarios disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="form-group">
        <input type="submit" value="Eliminar seleccionados" class="delete-btn">
        </div>
    </form>
</div>

<!-- 🔥 Script para filtrar y seleccionar/deseleccionar usuarios -->
<script>
function filtrarTabla() {
    var input = document.getElementById("filtro");
    var filtro = input.value.toLowerCase();
    var tabla = document.getElementById("tablaUsuarios");
    var filas = tabla.getElementsByTagName("tr");

    for (var i = 1; i < filas.length; i++) {
        var celdas = filas[i].getElementsByTagName("td");
        var mostrar = false;

        for (var j = 1; j < celdas.length; j++) { // Saltamos el checkbox (posición 0)
            if (celdas[j].innerText.toLowerCase().includes(filtro)) {
                mostrar = true;
                break;
            }
        }

        filas[i].style.display = mostrar ? "" : "none";
    }
}

function seleccionarTodos() {
    var checkboxes = document.querySelectorAll("input[type='checkbox'][name='usuarios[]']");
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = true;
    });
}

function deseleccionarTodos() {
    var checkboxes = document.querySelectorAll("input[type='checkbox'][name='usuarios[]']");
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
    });
}
</script>

</body>
</html>

<?php
$conn->close();
?>
