<?php
// LÓGICA PHP (NO MODIFICADA, SEGÚN REQUERIMIENTO DEL USUARIO)
require_once __DIR__ . '/../../../config/bd.php';
require_once __DIR__ . '/../../../app/model/Evento.php';

use App\DatabaseConnection;
use App\Models\Evento;

// NOTA: Asumo que DatabaseConnection y Evento están correctamente definidos.
// Obtener la conexión PDO
// NOTA: Si DatabaseConnection no existe, esta línea causará un error fatal.
// Se mantiene tal cual fue provista por el usuario.
$pdo = DatabaseConnection::getConnection(); 

// Instanciar el modelo con la conexión
$model = new Evento($pdo);

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventoData = [
        'Localidad' => $_POST['Localidad'] ?? '',
        'Contratista' => $_POST['Contratista'] ?? '',
        'NombreEvento' => $_POST['NombreEvento'] ?? '',
        'Modalidad' => $_POST['Modalidad'] ?? '',
        'Establecimiento' => $_POST['Establecimiento'] ?? '',
        'FechaInicio' => $_POST['FechaInicio'] ?? '',
        'FechaFin' => $_POST['FechaFin'] ?? '',
        'MontoCobrarEstimado' => $_POST['MontoCobrarEstimado'] ?? 0,
        'Moneda' => $_POST['Moneda'] ?? 'ARS'
    ];

    $empleados = $_POST['empleados'] ?? [];
    $gastos = $_POST['gastos'] ?? [];

    if ($model->crearEventoCompleto($eventoData, $empleados, $gastos)) {
        header('Location: index.php?success=evento_creado');
        exit;
    } else {
        $error = "Error al crear el evento.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Evento | ADIRA</title>
    <!-- Se mantiene el enlace a styles.css si existe -->
    <link rel="stylesheet" href="../../../public/css/styles.css"> 
    <link rel="stylesheet" href="../../../public/css/create.css">

</head>
<body>
    <div class="container">
        <h1>Crear Nuevo Evento</h1>
        <?php if(!empty($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form method="POST">
            <div class="section">
                <h2>Datos del Evento</h2>
                <div class="input-group">
                    <label>Nombre Evento:</label>
                    <input type="text" name="NombreEvento" required>
                </div>
                <div class="input-group">
                    <label>Localidad:</label>
                    <input type="text" name="Localidad" required>
                </div>
                <div class="input-group">
                    <label>Contratista:</label>
                    <input type="text" name="Contratista" required>
                </div>
                <div class="input-group">
                    <label>Modalidad:</label>
                    <select name="Modalidad">
                        <option value="Presencial">Presencial</option>
                        <option value="Virtual">Virtual</option>
                        <option value="Mixto">Mixto</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Establecimiento:</label>
                    <input type="text" name="Establecimiento">
                </div>
                <div class="input-group">
                    <label>Fecha Inicio:</label>
                    <input type="datetime-local" name="FechaInicio">
                </div>
                <div class="input-group">
                    <label>Fecha Fin:</label>
                    <input type="datetime-local" name="FechaFin">
                </div>
                <div class="input-group">
                    <label>Monto Estimado:</label>
                    <input type="number" step="0.01" name="MontoCobrarEstimado">
                </div>
                <div class="input-group">
                    <label>Moneda:</label>
                    <input type="text" name="Moneda" value="ARS">
                </div>
            </div>

            <div class="section">
                <h2>Empleados Asignados</h2>
                <button type="button" class="btn-base btn-add" onclick="addEmpleado()">Agregar Empleado</button>
                <div class="table-responsive">
                    <table id="empleados-table">
                        <thead>
                            <tr>
                                <th>ID Empleado</th>
                                <th>Rol</th>
                                <th>Horas</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="section">
                <h2>Gastos</h2>
                <button type="button" class="btn-base btn-add" onclick="addGasto()">Agregar Gasto</button>
                <div class="table-responsive">
                    <table id="gastos-table">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Monto</th>
                                <th>Proveedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn-base btn-new">Crear Evento</button>
        </form>
    </div>

    <script>
        function addEmpleado() {
            const tbody = document.querySelector('#empleados-table tbody');
            const index = tbody.children.length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="number" name="empleados[${index}][ID_Empleado]" required></td>
                <td><input type="text" name="empleados[${index}][RolEnEvento]"></td>
                <td><input type="number" step="0.1" name="empleados[${index}][HorasAsignadas]"></td>
                <td><input type="text" name="empleados[${index}][Observaciones]"></td>
                <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
            `;
            tbody.appendChild(row);
        }

        function addGasto() {
            const tbody = document.querySelector('#gastos-table tbody');
            const index = tbody.children.length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="gastos[${index}][Descripcion]"></td>
                <td><input type="number" name="gastos[${index}][ID_Categoria]"></td>
                <td><input type="number" step="0.01" name="gastos[${index}][Cantidad]" value="1"></td>
                <td><input type="number" step="0.01" name="gastos[${index}][PrecioUnitario]" value="0"></td>
                <td><input type="number" step="0.01" name="gastos[${index}][Monto]" value="0"></td>
                <td><input type="text" name="gastos[${index}][Proveedor]"></td>
                <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
            `;
            tbody.appendChild(row);
        }
    </script>
</body>
</html>
