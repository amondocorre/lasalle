<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador para la gestión de retrasos de estudiantes.
 */
class Retrasos extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Retraso_model', 'Estudiante_model']);
        date_default_timezone_set('America/La_Paz');
    }

    /**
     * GET /api/retrasos
     */
    public function index() {
        $filtros = [
            'fecha' => $this->input->get('fecha'),
            'rude'  => $this->input->get('rude')
        ];
        $retrasos = $this->Retraso_model->listar($filtros);
        $this->success($retrasos);
    }

    /**
     * POST /api/retrasos
     */
    public function store() {
        $data = $this->getJsonBody();

        if (empty($data['rude'])) {
            $this->error('El RUDE del estudiante es requerido', 400);
            return;
        }

        // Verificar existencia del estudiante
        $estudiante = $this->Estudiante_model->obtenerPorRude($data['rude']);
        if (!$estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $retrasoData = [
            'rude'           => $data['rude'],
            'fecha'          => $data['fecha'] ?? date('Y-m-d'),
            'hora'           => $data['hora'] ?? date('H:i:s'),
            'motivo'         => $data['motivo'] ?? null,
            'observacion'    => $data['observacion'] ?? null,
            'cita_padre'     => (isset($data['cita_padre']) && $data['cita_padre']) ? 1 : 0,
            'fecha_cita'     => $data['fecha_cita'] ?? null,
            'hora_cita'      => $data['hora_cita'] ?? null,
            'registrado_por' => $data['registrado_por'] ?? 'Sistema'
        ];

        $id = $this->Retraso_model->registrar($retrasoData);

        if ($id) {
            $this->success(['id' => $id], 'Retraso registrado correctamente', 201);
        } else {
            $this->error('No se pudo registrar el retraso');
        }
    }

    /**
     * DELETE /api/retrasos/(:num)
     */
    public function destroy($id) {
        if ($this->Retraso_model->eliminar($id)) {
            $this->success(null, 'Registro de retraso eliminado');
        } else {
            $this->error('No se pudo eliminar el registro');
        }
    }

    /**
     * GET /api/retrasos/estudiante/(:any)
     */
    public function porEstudiante($rude) {
        $filtros = [
            'fecha_desde' => $this->input->get('fecha_desde'),
            'fecha_hasta' => $this->input->get('fecha_hasta')
        ];
        $retrasos = $this->Retraso_model->obtenerPorEstudiante($rude, $filtros);
        $this->success($retrasos);
    }
}
