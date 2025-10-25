<?php
require_once __DIR__ . '/config/bd.php';

// ==========================
// CONSULTAS PRINCIPALES
// ==========================

// Total de eventos
$sqlEventos = "SELECT COUNT(*) AS total FROM Eventos";
$totalEventos = $conexion->query($sqlEventos)->fetch_assoc()['total'];

// Total de empleados
$sqlEmpleados = "SELECT COUNT(*) AS total FROM Empleados";
$totalEmpleados = $conexion->query($sqlEmpleados)->fetch_assoc()['total'];

// Total de gastos
$sqlGastos = "SELECT COUNT(*) AS total FROM Gastos";
$totalGastos = $conexion->query($sqlGastos)->fetch_assoc()['total'];

// Monto total de gastos
$sqlMontoGastos = "SELECT IFNULL(SUM(Monto),0) AS total FROM Gastos";
$montoGastos = $conexion->query($sqlMontoGastos)->fetch_assoc()['total'];

// Total facturado (estimado) por eventos
$sqlIngresos = "SELECT IFNULL(SUM(MontoCobrarEstimado),0) AS total FROM Eventos";
$totalIngresos = $conexion->query($sqlIngresos)->fetch_assoc()['total'];

// Rentabilidad estimada
$rentabilidad = $totalIngresos - $montoGastos;

// ==========================
// CONSULTA DETALLE DE EVENTOS
// ==========================
$sqlDetalle = "
SELECT e.ID_Evento, e.NombreEvento, e.Localidad, e.Contratista,
       IFNULL(SUM(g.Monto),0) AS TotalGastos,
       e.MontoCobrarEstimado,
       (e.MontoCobrarEstimado - IFNULL(SUM(g.Monto),0)) AS Rentabilidad
FROM Eventos e
LEFT JOIN Gastos g ON g.ID_Evento = e.ID_Evento
GROUP BY e.ID_Evento, e.NombreEvento, e.Localidad, e.Contratista, e.MontoCobrarEstimado
ORDER BY e.ID_Evento DESC
";
$detalleEventos = $conexion->query($sqlDetalle);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | ADIRA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .card { border: none; border-radius: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .card h3 { font-weight: 600; }
    .table thead { background-color: #0d6efd; color: #fff; }
  </style>
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4 text-center fw-bold text-primary">📊 Dashboard General - ADIRA</h1>

    <!-- Tarjetas resumen -->
    <div class="row g-4 mb-4 text-center">
      <div class="col-md-3">
        <div class="card p-3 bg-primary text-white">
          <h3><?php echo $totalEventos; ?></h3>
          <p>Eventos</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 bg-success text-white">
          <h3><?php echo $totalEmpleados; ?></h3>
          <p>Empleados</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 bg-warning text-white">
          <h3><?php echo $totalGastos; ?></h3>
          <p>Registros de Gastos</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 bg-danger text-white">
          <h3>$<?php echo number_format($montoGastos,2,',','.'); ?></h3>
          <p>Gasto Total</p>
        </div>
      </div>
    </div>

    <!-- Rentabilidad -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card p-4 text-center">
          <h4>💰 Rentabilidad Estimada</h4>
          <h2 class="<?php echo $rentabilidad >= 0 ? 'text-success' : 'text-danger'; ?>">
            $<?php echo number_format($rentabilidad,2,',','.'); ?>
          </h2>
        </div>
      </div>
    </div>

    <!-- Tabla de eventos -->
    <div class="card p-4">
      <h4 class="mb-3 text-primary">Eventos recientes</h4>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Evento</th>
              <th>Localidad</th>
              <th>Contratista</th>
              <th>Ingresos Estimados</th>
              <th>Gastos</th>
              <th>Rentabilidad</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $detalleEventos->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['ID_Evento']; ?></td>
              <td><?php echo htmlspecialchars($row['NombreEvento']); ?></td>
              <td><?php echo htmlspecialchars($row['Localidad']); ?></td>
              <td><?php echo htmlspecialchars($row['Contratista']); ?></td>
              <td>$<?php echo number_format($row['MontoCobrarEstimado'],2,',','.'); ?></td>
              <td>$<?php echo number_format($row['TotalGastos'],2,',','.'); ?></td>
              <td class="<?php echo $row['Rentabilidad'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                $<?php echo number_format($row['Rentabilidad'],2,',','.'); ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
