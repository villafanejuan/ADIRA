<?php
require_once __DIR__ . '/../../../config/bd.php';
require_once __DIR__ . '/../../../app/model/Evento.php';

use App\DatabaseConnection;
use App\Models\Evento;

$pdo = DatabaseConnection::getConnection(); 
$model = new Evento();

// 📦 Cargar empleados y categorías desde la BD
$empleadosStmt = $pdo->query("SELECT ID_Empleado, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM empleados ORDER BY Nombre");
$empleadosList = $empleadosStmt->fetchAll(PDO::FETCH_ASSOC);

$categoriasStmt = $pdo->query("SELECT ID_Categoria, NombreCategoria FROM categorias_gasto ORDER BY NombreCategoria");
$categoriasList = $categoriasStmt->fetchAll(PDO::FETCH_ASSOC);

// 🧭 Obtener ID del evento
$idEvento = $_GET['id'] ?? null;
if (!$idEvento) {
    header('Location: ../index.php?error=no_id');
    exit;
}

// 🔍 Cargar datos existentes (con empleados y gastos)
$evento = $model->obtenerEventoPorId((int)$idEvento);
if (!$evento) {
    header('Location: ../index.php?error=no_encontrado');
    exit;
}

// Evitar warnings
$evento['Empleados'] = $evento['Empleados'] ?? [];
$evento['Gastos'] = $evento['Gastos'] ?? [];

// ✅ Procesar formulario (POST)
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

    if ($model->actualizarEventoCompleto($idEvento, $eventoData, $empleados, $gastos)) {
        header("Location: ../index.php?success=evento_actualizado");
        exit;
    } else {
        $error = "Error al actualizar el evento.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento | ADIRA</title>
    <link rel="stylesheet" href="../../../public/css/styles.css"> 
    <link rel="stylesheet" href="../../../public/css/create.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h1>Editar Evento</h1>
        <?php if(!empty($error)) echo "<p class='error-message'>$error</p>"; ?>

        <form method="POST">
            <div class="section">
                <h2>Datos del Evento</h2>
                <div class="input-group">
                    <label>Nombre Evento:</label>
                    <input type="text" name="NombreEvento" value="<?= htmlspecialchars($evento['NombreEvento']) ?>" required>
                </div>
                <div class="input-group">
                    <label>Localidad:</label>
                    <input type="text" name="Localidad" value="<?= htmlspecialchars($evento['Localidad']) ?>" required>
                </div>
                <div class="input-group">
                    <label>Contratista:</label>
                    <input type="text" name="Contratista" value="<?= htmlspecialchars($evento['Contratista']) ?>" required>
                </div>
                <div class="input-group">
                    <label>Modalidad:</label>
                    <select name="Modalidad">
                        <option value="Presencial" <?= $evento['Modalidad'] === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                        <option value="Virtual" <?= $evento['Modalidad'] === 'Virtual' ? 'selected' : '' ?>>Virtual</option>
                        <option value="Mixto" <?= $evento['Modalidad'] === 'Mixto' ? 'selected' : '' ?>>Mixto</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Establecimiento:</label>
                    <input type="text" name="Establecimiento" value="<?= htmlspecialchars($evento['Establecimiento']) ?>">
                </div>
                <div class="input-group">
                    <label>Fecha Inicio:</label>
                    <input type="datetime-local" name="FechaInicio" value="<?= date('Y-m-d\TH:i', strtotime($evento['FechaInicio'])) ?>">
                </div>
                <div class="input-group">
                    <label>Fecha Fin:</label>
                    <input type="datetime-local" name="FechaFin" value="<?= date('Y-m-d\TH:i', strtotime($evento['FechaFin'])) ?>">
                </div>
                <div class="input-group">
                    <label>Monto Estimado:</label>
                    <input type="number" step="0.01" name="MontoCobrarEstimado" value="<?= htmlspecialchars($evento['MontoCobrarEstimado']) ?>">
                </div>
                <div class="input-group">
                    <label>Moneda:</label>
                    <input type="text" name="Moneda" value="<?= htmlspecialchars($evento['Moneda']) ?>">
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
                        <tbody>
                            <?php foreach ($evento['Empleados'] as $i => $emp): ?>
                                <tr>
                                    <td>
                                        <select class="select2" name="empleados[<?= $i ?>][ID_Empleado]" required>
                                            <option value="">Seleccione empleado</option>
                                            <?php foreach ($empleadosList as $opt): ?>
                                                <option value="<?= $opt['ID_Empleado'] ?>" <?= $opt['ID_Empleado'] == $emp['ID_Empleado'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($opt['NombreCompleto']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="empleados[<?= $i ?>][RolEnEvento]" value="<?= htmlspecialchars($emp['RolEnEvento']) ?>"></td>
                                    <td><input type="number" step="0.1" name="empleados[<?= $i ?>][HorasAsignadas]" value="<?= htmlspecialchars($emp['HorasAsignadas']) ?>"></td>
                                    <td><input type="text" name="empleados[<?= $i ?>][Observaciones]" value="<?= htmlspecialchars($emp['Observaciones']) ?>"></td>
                                    <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
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
                        <tbody>
                            <?php foreach ($evento['Gastos'] as $i => $g): ?>
                                <tr>
                                    <td><input type="text" name="gastos[<?= $i ?>][Descripcion]" value="<?= htmlspecialchars($g['Descripcion']) ?>"></td>
                                    <td>
                                        <select class="select2" name="gastos[<?= $i ?>][ID_Categoria]" required>
                                            <option value="">Seleccione categoría</option>
                                            <?php foreach ($categoriasList as $cat): ?>
                                                <option value="<?= $cat['ID_Categoria'] ?>" <?= $cat['ID_Categoria'] == $g['ID_Categoria'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['NombreCategoria']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" name="gastos[<?= $i ?>][Cantidad]" value="<?= htmlspecialchars($g['Cantidad']) ?>"></td>
                                    <td><input type="number" step="0.01" name="gastos[<?= $i ?>][PrecioUnitario]" value="<?= htmlspecialchars($g['PrecioUnitario']) ?>"></td>
                                    <td><input type="number" step="0.01" name="gastos[<?= $i ?>][Monto]" value="<?= htmlspecialchars($g['Monto']) ?>"></td>
                                    <td><input type="text" name="gastos[<?= $i ?>][Proveedor]" value="<?= htmlspecialchars($g['Proveedor']) ?>"></td>
                                    <td><button type="button" class="btn-base btn-remove in-table" onclick="this.closest('tr').remove()">Eliminar</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn-base btn-new">Guardar Cambios</button>
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

        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
