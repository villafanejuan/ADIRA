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
    <style>
        /* Paleta Minimalista (Replicada de index.php) */
        :root {
            --primary-color: #212529; /* Negro suave para texto principal */
            --accent-color: #007bff; /* Azul sutil para enlaces y botones */
            --background-light: #ffffff;
            --border-color: #e9ecef; /* Borde muy claro */
            --text-dark: #495057;
            --text-muted: #6c757d;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background-color: var(--border-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 900px; /* Ancho ajustado para formularios */
            margin: 0 auto;
            background-color: var(--background-light);
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-top: 0;
            margin-bottom: 30px;
            font-weight: 300;
            font-size: 2rem;
        }
        
        /* SECCIONES Y GRUPOS DE INPUT */
        .section { 
            margin-bottom: 30px; 
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .section:last-of-type {
            border-bottom: none;
        }
        .section h2 { 
            margin-bottom: 15px; 
            font-size: 1.3em; 
            color: var(--primary-color); 
            font-weight: 400;
        }
        
        .input-group { 
            display: flex; 
            align-items: center;
            margin-bottom: 15px; 
        }
        .input-group label { 
            width: 180px; /* Ancho fijo para labels */
            font-weight: 500;
            color: var(--text-dark);
            flex-shrink: 0;
        }
        .input-group input:not([type="checkbox"]), 
        .input-group select { 
            flex: 1; 
            padding: 8px 12px; 
            border-radius: 4px; 
            border: 1px solid var(--border-color); 
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .input-group input:focus, 
        .input-group select:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        /* ESTILOS DE TABLA INTERNA (EMPLEADOS/GASTOS) */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            font-size: 0.9em;
        }
        th {
            background-color: var(--background-light);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8em;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }
        td { 
            border: none;
            border-bottom: 1px solid var(--border-color);
            padding: 8px 12px; 
        }
        td:last-child {
            width: 1%;
            white-space: nowrap;
        }
        #empleados-table tbody tr:last-child td,
        #gastos-table tbody tr:last-child td {
            border-bottom: none;
        }
        #empleados-table input, #gastos-table input {
            padding: 6px;
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 3px;
        }

        /* ESTILOS DE BOTONES (Outline Minimalista) */
        .btn-base {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.9em;
            margin-right: 5px;
        }

        /* Botón de creación principal (Submit) */
        .btn-new {
            background-color: var(--accent-color);
            color: white;
            border: 1px solid var(--accent-color);
            margin-top: 15px;
            display: block;
            width: 100%;
            text-align: center;
        }
        .btn-new:hover {
            background-color: #0056b3;
            color: white;
        }
        
        /* Botón de Agregar (Outline Azul) */
        .btn-add { 
            color: var(--accent-color);
            border: 1px solid var(--accent-color);
            background: transparent;
        }
        .btn-add:hover { 
            background-color: var(--accent-color);
            color: white;
        }

        /* Botón de Eliminar (Outline Rojo) */
        .btn-remove { 
            color: #dc3545;
            border: 1px solid #dc3545;
            background: transparent;
            font-size: 0.8em;
        }
        .btn-remove:hover { 
            background-color: #dc3545;
            color: white;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Para botones pequeños dentro de la tabla */
        .btn-remove.in-table {
            padding: 4px 8px;
        }
    </style>
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
