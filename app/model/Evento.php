<?php
namespace App\Models;
// Incluir la conexión a la BD
require_once __DIR__ . '/../../config/bd.php';
use App\DatabaseConnection;
use PDO;

class Evento {
    private PDO $db;

    public function __construct() {
        // Conectar automáticamente usando DatabaseConnection
        $this->db = DatabaseConnection::getConnection();
    }

    // Obtener todos los eventos
    public function obtenerTodos(): array {
        $stmt = $this->db->query("SELECT * FROM Eventos ORDER BY ID_Evento DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un evento por ID
    public function obtenerPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM Eventos WHERE ID_Evento = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Crear un nuevo evento

    // Crear un evento con empleados y gastos
    public function crearEventoCompleto(array $eventoData, array $empleados = [], array $gastos = []): bool {
        try {
            $this->db->beginTransaction();

            // 1️⃣ Insertar el evento
            $sqlEvento = "INSERT INTO eventos 
                (Localidad, Contratista, NombreEvento, Modalidad, Establecimiento, FechaInicio, FechaFin, MontoCobrarEstimado, Moneda)
                VALUES 
                (:Localidad, :Contratista, :NombreEvento, :Modalidad, :Establecimiento, :FechaInicio, :FechaFin, :MontoCobrarEstimado, :Moneda)";
            $stmtEvento = $this->db->prepare($sqlEvento);
            $stmtEvento->execute($eventoData);

            $idEvento = $this->db->lastInsertId();

            // 2️⃣ Insertar empleados asignados al evento
            if (!empty($empleados)) {
                $sqlEmpleado = "INSERT INTO empleados_eventos 
                    (ID_Empleado, ID_Evento, RolEnEvento, FechaAsignacion, HorasAsignadas, Observaciones)
                    VALUES 
                    (:ID_Empleado, :ID_Evento, :RolEnEvento, :FechaAsignacion, :HorasAsignadas, :Observaciones)";
                $stmtEmp = $this->db->prepare($sqlEmpleado);

                foreach ($empleados as $emp) {
                    $stmtEmp->execute([
                        'ID_Empleado' => $emp['ID_Empleado'],
                        'ID_Evento' => $idEvento,
                        'RolEnEvento' => $emp['RolEnEvento'] ?? '',
                        'FechaAsignacion' => $emp['FechaAsignacion'] ?? date('Y-m-d'),
                        'HorasAsignadas' => $emp['HorasAsignadas'] ?? 0,
                        'Observaciones' => $emp['Observaciones'] ?? ''
                    ]);
                }
            }

            // 3️⃣ Insertar gastos asociados
            if (!empty($gastos)) {
                $sqlGasto = "INSERT INTO gastos 
                    (IdentificadorUnico, Fecha, Cantidad, PrecioUnitario, Monto, Descripcion, ID_Categoria, ID_Evento, Proveedor, Comprobante)
                    VALUES 
                    (:IdentificadorUnico, :Fecha, :Cantidad, :PrecioUnitario, :Monto, :Descripcion, :ID_Categoria, :ID_Evento, :Proveedor, :Comprobante)";
                $stmtGasto = $this->db->prepare($sqlGasto);

                foreach ($gastos as $gasto) {
                    $stmtGasto->execute([
                        'IdentificadorUnico' => $gasto['IdentificadorUnico'],
                        'Fecha' => $gasto['Fecha'] ?? date('Y-m-d'),
                        'Cantidad' => $gasto['Cantidad'] ?? 1,
                        'PrecioUnitario' => $gasto['PrecioUnitario'] ?? 0,
                        'Monto' => $gasto['Monto'] ?? 0,
                        'Descripcion' => $gasto['Descripcion'] ?? '',
                        'ID_Categoria' => $gasto['ID_Categoria'] ?? null,
                        'ID_Evento' => $idEvento,
                        'Proveedor' => $gasto['Proveedor'] ?? '',
                        'Comprobante' => $gasto['Comprobante'] ?? ''
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error creando evento completo: ".$e->getMessage());
            return false;
        }
    }

    // Actualizar un evento existente
    public function actualizar(int $id, array $data): bool {
        $data['ID_Evento'] = $id;
        $sql = "UPDATE Eventos SET 
                    Localidad = :Localidad,
                    Contratista = :Contratista,
                    NombreEvento = :NombreEvento,
                    Modalidad = :Modalidad,
                    Establecimiento = :Establecimiento,
                    FechaInicio = :FechaInicio,
                    FechaFin = :FechaFin,
                    MontoCobrarEstimado = :MontoCobrarEstimado,
                    Moneda = :Moneda
                WHERE ID_Evento = :ID_Evento";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Eliminar un evento
    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Eventos WHERE ID_Evento = :id");
        return $stmt->execute(['id' => $id]);
    }
}


?>