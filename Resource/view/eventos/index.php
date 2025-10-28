<?php
require_once __DIR__ . '/../../../config/bd.php';
require_once __DIR__ . '/../../../app/model/Evento.php';

use App\Models\Evento;

// Instanciar modelo
$model = new Evento();

// Manejar eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $idEliminar = (int) $_GET['delete'];
    $model->eliminar($idEliminar);
    header('Location: index.php?success=evento_eliminado');
    exit;
}

// Obtener eventos
$eventos = $model->obtenerTodos();
$mensajeExito = isset($_GET['success']) && $_GET['success'] === 'evento_eliminado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <title>Listado de Eventos | ADIRA</title>
    <style>
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 4px;
            width: 400px;
            text-align: center;
        }
        .modal button {
            margin: 5px;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cancel { background-color: #ccc; }
        .btn-confirm { background-color: #dc3545; color: white; }
        .btn-success { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>
        <span>Listado de Eventos</span>
        <a href="../eventos/create.php" class="btn-new">Nuevo Evento</a>
    </h1>

    <?php if (!empty($eventos)): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Localidad</th>
                    <th>Contratista</th>
                    <th>Modalidad</th>
                    <th>Establecimiento</th>
                    <th>F. Inicio</th>
                    <th>F. Fin</th>
                    <th>Monto Est.</th>
                    <th>Moneda</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventos as $evento): ?>
                <tr>
                    <td><?= htmlspecialchars($evento['ID_Evento'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($evento['NombreEvento'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($evento['Localidad'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($evento['Contratista'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($evento['Modalidad'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($evento['Establecimiento'] ?? '-') ?></td>
                    <td><?= date("d/m/Y", strtotime($evento['FechaInicio'] ?? '')) ?></td>
                    <td><?= date("d/m/Y", strtotime($evento['FechaFin'] ?? '')) ?></td>
                    <td><?= number_format($evento['MontoCobrarEstimado'] ?? 0, 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($evento['Moneda'] ?? 'ARS') ?></td>
                    <td class="actions">
                        <a href="/ADIRA/resource/view/eventos/edit.php?id=<?= $evento['ID_Evento'] ?>" class="btn-edit">Editar</a>

                        <a href="#" class="btn-delete" 
                           onclick="confirmDelete(<?= $evento['ID_Evento'] ?>, '<?= htmlspecialchars($evento['NombreEvento']) ?>')">
                           Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="no-records">
        <p>No hay eventos registrados en el sistema.</p>
        <a href="/adira/eventos/crearForm" class="btn-new">Registrar Nuevo Evento</a>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal" id="modal-delete">
    <div class="modal-content">
        <p id="delete-text"></p>
        <button class="btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
        <button class="btn-confirm" id="confirm-delete-btn">Eliminar</button>
    </div>
</div>

<!-- Modal de éxito -->
<?php if ($mensajeExito): ?>
<div class="modal" id="modal-success">
    <div class="modal-content">
        <p>Evento eliminado correctamente</p>
        <button onclick="document.getElementById('modal-success').style.display='none'">Cerrar</button>
    </div>
</div>
<?php endif; ?>
<script src="../../../public/js/evento.js"></script>
</body>
</html>
