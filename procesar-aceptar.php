<!
    Autor: Alberto Ortiz Arribas
    Fecha: 08-03-2025
    Resumen: Muestra una tabla de los usuarios con estado igual a pendiente y aparece tambien un filtro para buscar a los usuarios.
    
>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        h2 {
            text-align: center;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
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

        .accept-btn, .reject-btn {
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .accept-btn {
            background-color: green;
        }

        .reject-btn {
            background-color: red;
        }

        .accept-btn:hover {
            background-color: darkgreen;
        }

        .reject-btn:hover {
            background-color: darkred;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="home.php" class="back-btn">← Atrás</a>
    <h2>Administrar Usuarios Pendientes</h2>
    <div class="btn-group" style="text-align: center;">
        <button type="button" onclick="seleccionarTodos()">Seleccionar Todos</button>
        <button type="button" onclick="deseleccionarTodos()">Deseleccionar Todos</button>
    </div>

    <!-- Campo de Búsqueda -->
    <input type="text" id="filtro" onkeyup="filtrarTabla()" placeholder="Buscar usuario...">

    <form action="procesar_aceptar.php" method="POST">
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
                // Conexión a la base de datos
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "l&r";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }

                // Obtener usuarios pendientes
                $sql = "SELECT * FROM usuarios WHERE estado = 'pendiente'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><input type='checkbox' name='usuarios[]' value='" . $row['correo'] . "'></td>";
                        echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['apellidos']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay usuarios pendientes.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        <button type="submit" name="accion" value="aceptar" class="accept-btn">Aceptar Seleccionados</button>
        <button type="submit" name="accion" value="rechazar" class="reject-btn">Rechazar Seleccionados</button>
    </form>
</div>

<script>
function filtrarTabla() {
    var input = document.getElementById("filtro");
    var filtro = input.value.toLowerCase();
    var tabla = document.getElementById("tablaUsuarios");
    var filas = tabla.getElementsByTagName("tr");

    for (var i = 1; i < filas.length; i++) { // Ignorar el encabezado
        var celdas = filas[i].getElementsByTagName("td");
        var mostrar = false;

        for (var j = 1; j < celdas.length; j++) { // Omitir el checkbox
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
