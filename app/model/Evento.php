<?php

namespace App\Models;
// Incluir la conexión a la BD
require_once __DIR__ . '/../../config/bd.php';

use App\DatabaseConnection;
use PDO;

class Evento
{
    private PDO $db;

    public function __construct()
    {
        // Conectar automáticamente usando DatabaseConnection
        $this->db = DatabaseConnection::getConnection();
    }

    // Obtener todos los eventos
    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT * FROM Eventos ORDER BY ID_Evento DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un evento por ID
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Eventos WHERE ID_Evento = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Crear un nuevo evento

    // Crear un evento con empleados y gastos
    public function crearEventoCompleto(array $eventoData, array $empleados = [], array $gastos = []): bool
    {
        try {
            // 1️⃣ VALIDACIÓN DE DATOS DEL EVENTO
            $camposObligatorios = ['Localidad', 'Contratista', 'NombreEvento', 'Modalidad', 'FechaInicio'];
            foreach ($camposObligatorios as $campo) {
                if (empty($eventoData[$campo])) {
                    throw new \Exception("El campo '$campo' del evento es obligatorio.");
                }
            }

            // Validar formato de fecha
            if (!strtotime($eventoData['FechaInicio'])) {
                throw new \Exception("Formato de FechaInicio inválido.");
            }
            if (!empty($eventoData['FechaFin']) && !strtotime($eventoData['FechaFin'])) {
                throw new \Exception("Formato de FechaFin inválido.");
            }

            // Validar monto
            if (!is_numeric($eventoData['MontoCobrarEstimado'] ?? 0)) {
                throw new \Exception("El MontoCobrarEstimado debe ser numérico.");
            }

            // 2️⃣ INICIAR TRANSACCIÓN
            $this->db->beginTransaction();

            // Insertar evento
            $sqlEvento = "INSERT INTO eventos 
            (Localidad, Contratista, NombreEvento, Modalidad, Establecimiento, FechaInicio, FechaFin, MontoCobrarEstimado, Moneda)
            VALUES 
            (:Localidad, :Contratista, :NombreEvento, :Modalidad, :Establecimiento, :FechaInicio, :FechaFin, :MontoCobrarEstimado, :Moneda)";
            $stmtEvento = $this->db->prepare($sqlEvento);
            $stmtEvento->execute($eventoData);
            $idEvento = $this->db->lastInsertId();

            // 3️⃣ EMPLEADOS
            if (!empty($empleados)) {
                $sqlEmpleado = "INSERT INTO empleados_eventos 
                (ID_Empleado, ID_Evento, RolEnEvento, FechaAsignacion, HorasAsignadas, Observaciones)
                VALUES 
                (:ID_Empleado, :ID_Evento, :RolEnEvento, :FechaAsignacion, :HorasAsignadas, :Observaciones)";
                $stmtEmp = $this->db->prepare($sqlEmpleado);

                foreach ($empleados as $emp) {
                    if (empty($emp['ID_Empleado'])) continue; // salta si está vacío
                    if (!is_numeric($emp['HorasAsignadas'] ?? 0)) {
                        throw new \Exception("HorasAsignadas debe ser numérico para el empleado {$emp['ID_Empleado']}.");
                    }

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

            // 4️⃣ GASTOS
            if (!empty($gastos)) {
                $sqlGasto = "INSERT INTO gastos 
                (IdentificadorUnico, Fecha, Cantidad, PrecioUnitario, Monto, Descripcion, ID_Categoria, ID_Evento, Proveedor, Comprobante)
                VALUES 
                (:IdentificadorUnico, :Fecha, :Cantidad, :PrecioUnitario, :Monto, :Descripcion, :ID_Categoria, :ID_Evento, :Proveedor, :Comprobante)";
                $stmtGasto = $this->db->prepare($sqlGasto);

                // Preparar consulta para validar categorías existentes
                $sqlCheckCat = "SELECT COUNT(*) FROM categorias_gasto WHERE ID_Categoria = ?";
                $stmtCheckCat = $this->db->prepare($sqlCheckCat);

                foreach ($gastos as $gasto) {
                    if (empty($gasto['Descripcion'])) {
                        throw new \Exception("Falta la descripción en un gasto.");
                    }

                    // Validar numéricos
                    if (!is_numeric($gasto['Cantidad'] ?? 1) || !is_numeric($gasto['PrecioUnitario'] ?? 0)) {
                        throw new \Exception("Cantidad o PrecioUnitario inválidos en el gasto '{$gasto['Descripcion']}'.");
                    }

                    // Validar categoría existente
                    if (!empty($gasto['ID_Categoria'])) {
                        $stmtCheckCat->execute([$gasto['ID_Categoria']]);
                        if ($stmtCheckCat->fetchColumn() == 0) {
                            throw new \Exception("La categoría ID {$gasto['ID_Categoria']} no existe en categorias_gasto.");
                        }
                    } else {
                        throw new \Exception("Cada gasto debe tener un ID_Categoria.");
                    }

                    $stmtGasto->execute([
                        'IdentificadorUnico' => $gasto['IdentificadorUnico'] ?? uniqid('GASTO_', true),
                        'Fecha' => $gasto['Fecha'] ?? date('Y-m-d'),
                        'Cantidad' => $gasto['Cantidad'] ?? 1,
                        'PrecioUnitario' => $gasto['PrecioUnitario'] ?? 0,
                        'Monto' => $gasto['Monto'] ?? 0,
                        'Descripcion' => $gasto['Descripcion'] ?? '',
                        'ID_Categoria' => $gasto['ID_Categoria'],
                        'ID_Evento' => $idEvento,
                        'Proveedor' => $gasto['Proveedor'] ?? '',
                        'Comprobante' => $gasto['Comprobante'] ?? ''
                    ]);
                }
            }

            // 5️⃣ CONFIRMAR TRANSACCIÓN
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("❌ Error creando evento completo: " . $e->getMessage());
            return false;
        }
    }



        // 🔍 Obtener evento completo con empleados y gastos
    public function obtenerEventoPorId(int $id): ?array
    {
        try {
            // 1️⃣ Datos principales del evento
            $stmt = $this->db->prepare("SELECT * FROM eventos WHERE ID_Evento = :id");
            $stmt->execute(['id' => $id]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evento) return null;

            // 2️⃣ Empleados asociados
            $stmtEmp = $this->db->prepare("
                SELECT ee.*, e.Nombre, e.Apellido 
                FROM empleados_eventos ee
                JOIN empleados e ON ee.ID_Empleado = e.ID_Empleado
                WHERE ee.ID_Evento = :id
            ");
            $stmtEmp->execute(['id' => $id]);
            $evento['Empleados'] = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Gastos asociados
            $stmtGasto = $this->db->prepare("
                SELECT g.*, c.NombreCategoria 
                FROM gastos g
                LEFT JOIN categorias_gasto c ON g.ID_Categoria = c.ID_Categoria
                WHERE g.ID_Evento = :id
            ");
            $stmtGasto->execute(['id' => $id]);
            $evento['Gastos'] = $stmtGasto->fetchAll(PDO::FETCH_ASSOC);

            return $evento;
        } catch (\Exception $e) {
            error_log("❌ Error al obtener evento completo: " . $e->getMessage());
            return null;
        }
    }



    // ✏️ Actualizar evento completo con empleados y gastos
    public function actualizarEventoCompleto(int $id, array $eventoData, array $empleados = [], array $gastos = []): bool
    {
        try {
            $this->db->beginTransaction();

            // 1️⃣ Actualizar datos principales del evento
            $eventoData['ID_Evento'] = $id;
            $sql = "UPDATE eventos SET 
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
            $stmt->execute($eventoData);

            // 2️⃣ Eliminar empleados y gastos anteriores
            $this->db->prepare("DELETE FROM empleados_eventos WHERE ID_Evento = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM gastos WHERE ID_Evento = ?")->execute([$id]);

            // 3️⃣ Insertar empleados actualizados
            if (!empty($empleados)) {
                $sqlEmp = "INSERT INTO empleados_eventos 
                (ID_Empleado, ID_Evento, RolEnEvento, FechaAsignacion, HorasAsignadas, Observaciones)
                VALUES (:ID_Empleado, :ID_Evento, :RolEnEvento, :FechaAsignacion, :HorasAsignadas, :Observaciones)";
                $stmtEmp = $this->db->prepare($sqlEmp);

                foreach ($empleados as $emp) {
                    if (empty($emp['ID_Empleado'])) continue;

                    $stmtEmp->execute([
                        'ID_Empleado' => $emp['ID_Empleado'],
                        'ID_Evento' => $id,
                        'RolEnEvento' => $emp['RolEnEvento'] ?? '',
                        'FechaAsignacion' => date('Y-m-d'),
                        'HorasAsignadas' => $emp['HorasAsignadas'] ?? 0,
                        'Observaciones' => $emp['Observaciones'] ?? ''
                    ]);
                }
            }

            // 4️⃣ Insertar gastos actualizados
            if (!empty($gastos)) {
                $sqlGasto = "INSERT INTO gastos 
                (IdentificadorUnico, Fecha, Cantidad, PrecioUnitario, Monto, Descripcion, ID_Categoria, ID_Evento, Proveedor, Comprobante)
                VALUES (:IdentificadorUnico, :Fecha, :Cantidad, :PrecioUnitario, :Monto, :Descripcion, :ID_Categoria, :ID_Evento, :Proveedor, :Comprobante)";
                $stmtGasto = $this->db->prepare($sqlGasto);

                foreach ($gastos as $g) {
                    if (empty($g['Descripcion'])) continue;

                    $stmtGasto->execute([
                        'IdentificadorUnico' => $g['IdentificadorUnico'] ?? uniqid('GASTO_', true),
                        'Fecha' => $g['Fecha'] ?? date('Y-m-d'),
                        'Cantidad' => $g['Cantidad'] ?? 1,
                        'PrecioUnitario' => $g['PrecioUnitario'] ?? 0,
                        'Monto' => $g['Monto'] ?? 0,
                        'Descripcion' => $g['Descripcion'],
                        'ID_Categoria' => $g['ID_Categoria'] ?? null,
                        'ID_Evento' => $id,
                        'Proveedor' => $g['Proveedor'] ?? '',
                        'Comprobante' => $g['Comprobante'] ?? ''
                    ]);
                }
            }

            // 5️⃣ Confirmar transacción
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("❌ Error al actualizar evento completo: " . $e->getMessage());
            return false;
        }
    }


    // Eliminar un evento
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Eventos WHERE ID_Evento = :id");
        return $stmt->execute(['id' => $id]);
    }
}
