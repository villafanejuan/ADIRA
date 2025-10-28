<?php
require_once __DIR__ . '/../../../config/bd.php';
use App\DatabaseConnection;

// Conexión PDO
$pdo = DatabaseConnection::getConnection();

// Obtener todos los gastos ordenados por Localidad y Evento
$sql = "SELECT * FROM vw_gastos_detalle ORDER BY Localidad, NombreEvento, Fecha DESC";
$stmt = $pdo->query($sql);
$gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar primero por Localidad, luego por Evento
$localidades = [];
foreach ($gastos as $gasto) {
    $localidad = $gasto['Localidad'] ?? 'Sin Localidad';
    $evento = $gasto['NombreEvento'] ?? 'Sin Evento';
    $localidades[$localidad][$evento][] = $gasto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gastos por Localidad y Evento | ADIRA</title>
<link rel="stylesheet" href="../../../public/css/styles.css">
<link rel="stylesheet" href="../../../public/css/gastos.css">

</head>
<body>
<div class="container">
    <h1>Gastos por Localidad y Evento</h1>

    <?php foreach ($localidades as $localidad => $eventos): ?>
        <div class="localidad-group">
            <h2 class="localidad-title"><?= htmlspecialchars($localidad) ?></h2>

            <?php foreach ($eventos as $nombreEvento => $gastosEvento):
                $totalEvento = array_sum(array_column($gastosEvento, 'Monto'));
                $modalId = "modal-" . md5($localidad.$nombreEvento);
            ?>
            <div class="event-container">
                <div class="event-title"><?= htmlspecialchars($nombreEvento) ?></div>
                <div class="cards">
                    <?php foreach ($gastosEvento as $gasto): ?>
                    <div class="card" onclick="openModal('<?= $modalId ?>')">
                        <p><span>Descripción:</span> <span><?= htmlspecialchars($gasto['DescripcionGasto']) ?></span></p>
                        <p><span>Categoría:</span> <span><?= htmlspecialchars($gasto['Categoria']) ?></span></p>
                        <p><span>Fecha:</span> <span><?= date("d/m/Y", strtotime($gasto['Fecha'])) ?></span></p>
                        <p><span>Cantidad:</span> <span><?= $gasto['Cantidad'] ?></span></p>
                        <p><span>Precio Unitario:</span> <span>$<?= number_format($gasto['PrecioUnitario'], 2, ',', '.') ?></span></p>
                        <p><span>Monto:</span> <span>$<?= number_format($gasto['Monto'], 2, ',', '.') ?></span></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="event-summary">
                    Total Gastos del Evento: <span>$<?= number_format($totalEvento, 2, ',', '.') ?></span>
                </div>

                <!-- Modal -->
                <div class="modal" id="<?= $modalId ?>">
                    <div class="modal-content">
                        <h2><?= htmlspecialchars($nombreEvento) ?></h2>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Monto</th>
                                        <th>Costo Estimado</th>
                                        <th>Precio Unitario Ref.</th>
                                        <th>Contratista</th>
                                        <th>Monto Cobrar Estimado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gastosEvento as $gasto): ?>
                                    <tr>
                                        <td><?= date("d/m/Y", strtotime($gasto['Fecha'])) ?></td>
                                        <td><?= htmlspecialchars($gasto['DescripcionGasto']) ?></td>
                                        <td><?= htmlspecialchars($gasto['Categoria']) ?></td>
                                        <td><?= $gasto['Cantidad'] ?></td>
                                        <td>$<?= number_format($gasto['PrecioUnitario'], 2, ',', '.') ?></td>
                                        <td>$<?= number_format($gasto['Monto'], 2, ',', '.') ?></td>
                                        <td>$<?= number_format($gasto['CostoEstimado'], 2, ',', '.') ?></td>
                                        <td>$<?= number_format($gasto['PrecioUnitarioReferencia'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($gasto['Contratista']) ?></td>
                                        <td>$<?= number_format($gasto['MontoCobrarEstimado'], 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="close-button-container">
                            <button class="close-button" onclick="closeModal('<?= $modalId ?>')">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
});

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
</body>
</html>
