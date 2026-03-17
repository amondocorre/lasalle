<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

class Periodos extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Periodo_model');
    }

    /** GET /api/periodos */
    public function index() {
        $this->success($this->Periodo_model->listar());
    }

    /** POST /api/periodos */
    public function store() {
        $data = $this->getJsonBody();
        $id = $this->Periodo_model->crear($data);
        $this->success(null, 'Periodo creado', 201);
    }

    /** PUT /api/periodos/(:num) */
    public function update(int $id) {
        $data = $this->getJsonBody();
        $this->Periodo_model->actualizar($id, $data);
        $this->success(null, 'Periodo actualizado');
    }

    /** DELETE /api/periodos/(:num) */
    public function destroy(int $id) {
        $this->Periodo_model->eliminar($id);
        $this->success(null, 'Periodo eliminado');
    }
}
