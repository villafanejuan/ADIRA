<?php
namespace App\Models;

use mysqli;

class Empleado {
    private mysqli $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): ?\mysqli_result {
        $sql = "SELECT * FROM Empleados WHERE Activo = 1 ORDER BY Apellido";
        return $this->conexion->query($sql);
    }

    public function crear(array $data): bool {
        $sql = "INSERT INTO Empleados (Nombre, Apellido, DNI, Puesto, TipoContrato, FechaIngreso, SalarioBase)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "ssssssd",
            $data['Nombre'],
            $data['Apellido'],
            $data['DNI'],
            $data['Puesto'],
            $data['TipoContrato'],
            $data['FechaIngreso'],
            $data['SalarioBase']
        );
        return $stmt->execute();
    }

    public function desactivar(int $id): bool {
        $sql = "UPDATE Empleados SET Activo = 0 WHERE ID_Empleado = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
