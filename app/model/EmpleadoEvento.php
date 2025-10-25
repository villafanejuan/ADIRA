<?php
namespace App\Models;

use mysqli;

class EmpleadoEvento {
    private mysqli $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerPorEvento(int $idEvento): ?\mysqli_result {
        $sql = "SELECT ee.*, e.Nombre, e.Apellido, e.Puesto 
                FROM Empleados_Eventos ee
                INNER JOIN Empleados e ON ee.ID_Empleado = e.ID_Empleado
                WHERE ee.ID_Evento = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idEvento);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function asignar(array $data): bool {
        $sql = "INSERT INTO Empleados_Eventos (ID_Empleado, ID_Evento, RolEnEvento, HorasAsignadas, Observaciones)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "iisss",
            $data['ID_Empleado'],
            $data['ID_Evento'],
            $data['RolEnEvento'],
            $data['HorasAsignadas'],
            $data['Observaciones']
        );
        return $stmt->execute();
    }

    public function eliminar(int $id): bool {
        $sql = "DELETE FROM Empleados_Eventos WHERE ID_EmpleadoEvento = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
