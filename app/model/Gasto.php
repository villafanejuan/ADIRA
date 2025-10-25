<?php
namespace App\Models;

use mysqli;

class Gasto {
    private mysqli $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): ?\mysqli_result {
        $sql = "SELECT g.*, c.NombreCategoria 
                FROM Gastos g
                LEFT JOIN Categorias_Gasto c ON g.ID_Categoria = c.ID_Categoria
                ORDER BY g.Fecha DESC";
        return $this->conexion->query($sql);
    }

    public function crear(array $data): bool {
        $sql = "INSERT INTO Gastos (Fecha, Monto, Descripcion, ID_Categoria, ID_Evento, Proveedor, Comprobante)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "sdsiiss",
            $data['Fecha'],
            $data['Monto'],
            $data['Descripcion'],
            $data['ID_Categoria'],
            $data['ID_Evento'],
            $data['Proveedor'],
            $data['Comprobante']
        );
        return $stmt->execute();
    }

    public function eliminar(int $id): bool {
        $sql = "DELETE FROM Gastos WHERE ID_Gasto = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
