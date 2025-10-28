<?php
require_once __DIR__ . '/../../../config/bd.php';
require_once __DIR__ . '/../../../app/model/Evento.php';

use App\DatabaseConnection;
use App\Models\Evento;

$pdo = DatabaseConnection::getConnection(); 
$model = new Evento($pdo);

// 📦 Cargar empleados y categorías desde la BD
$empleadosStmt = $pdo->query("SELECT ID_Empleado, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM empleados ORDER BY Nombre");
$empleadosList = $empleadosStmt->fetchAll(PDO::FETCH_ASSOC);

$categoriasStmt = $pdo->query("SELECT ID_Categoria, NombreCategoria FROM categorias_gasto ORDER BY NombreCategoria");
$categoriasList = $categoriasStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="../../../public/css/styles.css"> 
    <link rel="stylesheet" href="../../../public/css/create.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

            <!-- EMPLEADOS -->
            <div class="section">
                <h2>Empleados Asignados</h2>
                <button type="button" class="btn-base btn-add" onclick="addEmpleado()">Agregar Empleado</button>
                <div class="table-responsive">
                    <table id="empleados-table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
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

            <!-- GASTOS -->
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

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const empleadosData = <?= json_encode($empleadosList) ?>;
        const categoriasData = <?= json_encode($categoriasList) ?>;

        function addEmpleado() {
            const tbody = document.querySelector('#empleados-table tbody');
            const index = tbody.children.length;
            const row = document.createElement('tr');

            const options = empleadosData.map(emp => 
                `<option value="${emp.ID_Empleado}">${emp.NombreCompleto}</option>`
            ).join('');

            row.innerHTML = `
                <td>
                    <select class="select2" name="empleados[${index}][ID_Empleado]" required>
                        <option value="">Seleccione empleado</option>
                        ${options}
                    </select>
                </td>
                <td><input type="text" name="empleados[${index}][RolEnEvento]"></td>
                <td><input type="number" step="0.1" name="empleados[${index}][HorasAsignadas]"></td>
                <td><input type="text" name="empleados[${index}][Observaciones]"></td>
                <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
            `;
            tbody.appendChild(row);
            $(row).find('.select2').select2();
        }

        function addGasto() {
            const tbody = document.querySelector('#gastos-table tbody');
            const index = tbody.children.length;
            const row = document.createElement('tr');

            const options = categoriasData.map(cat => 
                `<option value="${cat.ID_Categoria}">${cat.NombreCategoria}</option>`
            ).join('');

            row.innerHTML = `
                <td><input type="text" name="gastos[${index}][Descripcion]"></td>
                <td>
                    <select class="select2" name="gastos[${index}][ID_Categoria]" required>
                        <option value="">Seleccione categoría</option>
                        ${options}
                    </select>
                </td>
                <td><input type="number" step="0.01" name="gastos[${index}][Cantidad]" value="1"></td>
                <td><input type="number" step="0.01" name="gastos[${index}][PrecioUnitario]" value="0"></td>
                <td><input type="number" step="0.01" name="gastos[${index}][Monto]" value="0"></td>
                <td><input type="text" name="gastos[${index}][Proveedor]"></td>
                <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
            `;
            tbody.appendChild(row);
            $(row).find('.select2').select2();
        }

        // Inicializar select2 globalmente
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
