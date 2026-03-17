<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

class Asignaciones extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Asignacion_model');
    }

    /**
     * GET /api/asignaciones/curso/(:num)
     */
    public function curso(int $curso_id) {
        $data = $this->Asignacion_model->listar_por_curso($curso_id);
        $this->success($data);
    }

    /**
     * POST /api/asignaciones/batch
     */
    public function batch() {
        $data = $this->getJsonBody();
        if (empty($data) || !is_array($data)) {
            $this->error('Datos inválidos', 400);
            return;
        }

        $res = $this->Asignacion_model->crear_batch($data);
        if ($res) {
            $this->success(null, 'Carga académica guardada correctamente', 201);
        } else {
            $this->error('Error al guardar las asignaciones', 500);
        }
    }

    /**
     * POST /api/asignaciones/validar-choque
     */
    public function validar_choque() {
        $data = $this->getJsonBody();
        $choque = $this->Asignacion_model->verificar_choque_profesor(
            $data['profesor_id'],
            $data['dia'],
            $data['hora_inicio'],
            $data['hora_fin']
        );

        $this->success(['choque' => $choque]);
    }

    /**
     * DELETE /api/asignaciones/(:num)
     */
    public function destroy(int $id) {
        if ($this->Asignacion_model->eliminar($id)) {
            $this->success(null, 'Asignación eliminada');
        } else {
            $this->error('No se puede eliminar la asignación porque tiene horarios registrados', 400);
        }
    }
}
