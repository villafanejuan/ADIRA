<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bd.php';
require_once __DIR__ . '/../Models/Evento.php';

use App\DatabaseConnection;
use App\Models\Evento;

class EventoController {
    private Evento $eventoModel;

    public function __construct() {
        // Obtener la conexión a la BD (PDO)
        $conexion = DatabaseConnection::getConnection();
        $this->eventoModel = new Evento($conexion);
    }

    // Muestra todos los eventos
    public function index() {
        $eventos = $this->eventoModel->obtenerTodos();
        require __DIR__ . '/../../resources/views/eventos/index.php';
    }

    // Formulario para crear un evento
    public function crearForm() {
        require __DIR__ . '/../../resources/views/eventos/crear.php';
    }

    // Crear un nuevo evento
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Método no permitido.");
        }

        $data = [
            'Localidad' => filter_input(INPUT_POST, 'localidad', FILTER_SANITIZE_SPECIAL_CHARS),
            'Contratista' => filter_input(INPUT_POST, 'contratista', FILTER_SANITIZE_SPECIAL_CHARS),
            'NombreEvento' => filter_input(INPUT_POST, 'nombre_evento', FILTER_SANITIZE_SPECIAL_CHARS),
            'Modalidad' => filter_input(INPUT_POST, 'modalidad', FILTER_SANITIZE_SPECIAL_CHARS),
            'Establecimiento' => filter_input(INPUT_POST, 'establecimiento', FILTER_SANITIZE_SPECIAL_CHARS),
            'FechaInicio' => filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_SPECIAL_CHARS),
            'FechaFin' => filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_SPECIAL_CHARS),
            'MontoCobrarEstimado' => filter_input(INPUT_POST, 'monto_estimado', FILTER_VALIDATE_FLOAT),
            'Moneda' => filter_input(INPUT_POST, 'moneda', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'ARS'
        ];

        if ($this->eventoModel->crear($data)) {
            header('Location: /adira/eventos?success=evento_creado');
            exit();
        } else {
            header('Location: /adira/eventos/crear?error=fallo_registro');
            exit();
        }
    }

    // Formulario para editar un evento
    public function editarForm(int $id) {
        $evento = $this->eventoModel->obtenerPorId($id);

        if (!$evento) {
            http_response_code(404);
            exit("Evento no encontrado.");
        }

        require __DIR__ . '/../../resources/views/eventos/editar.php';
    }

    // Actualizar un evento
    public function actualizar(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            http_response_code(405);
            exit("Solicitud inválida.");
        }

        $data = [
            'Localidad' => filter_input(INPUT_POST, 'localidad', FILTER_SANITIZE_SPECIAL_CHARS),
            'Contratista' => filter_input(INPUT_POST, 'contratista', FILTER_SANITIZE_SPECIAL_CHARS),
            'NombreEvento' => filter_input(INPUT_POST, 'nombre_evento', FILTER_SANITIZE_SPECIAL_CHARS),
            'Modalidad' => filter_input(INPUT_POST, 'modalidad', FILTER_SANITIZE_SPECIAL_CHARS),
            'Establecimiento' => filter_input(INPUT_POST, 'establecimiento', FILTER_SANITIZE_SPECIAL_CHARS),
            'FechaInicio' => filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_SPECIAL_CHARS),
            'FechaFin' => filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_SPECIAL_CHARS),
            'MontoCobrarEstimado' => filter_input(INPUT_POST, 'monto_estimado', FILTER_VALIDATE_FLOAT),
            'Moneda' => filter_input(INPUT_POST, 'moneda', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'ARS'
        ];

        if ($this->eventoModel->actualizar($id, $data)) {
            header("Location: /adira/eventos/ver?id=$id&success=actualizado");
            exit();
        } else {
            header("Location: /adira/eventos/editar?id=$id&error=fallo_actualizacion");
            exit();
        }
    }

    // Eliminar un evento
    public function eliminar(int $id) {
        if ($id <= 0) {
            header('Location: /adira/eventos?error=id_invalido');
            exit();
        }

        if ($this->eventoModel->eliminar($id)) {
            header('Location: /adira/eventos?success=eliminado');
            exit();
        } else {
            header('Location: /adira/eventos?error=fallo_eliminacion');
            exit();
        }
    }
}
