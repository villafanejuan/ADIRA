<?php
namespace App\Models;

use mysqli;

class CategoriaGasto {
    private mysqli $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodas(): ?\mysqli_result {
        $sql = "SELECT * FROM Categorias_Gasto ORDER BY ID_Categoria ASC";
        return $this->conexion->query($sql);
    }
}
