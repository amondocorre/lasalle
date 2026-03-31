<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador Público: Estudiantes_Controller
 * Endpoints públicos para la consulta de información de estudiantes (como el padre de familia).
 */
class Estudiantes_Controller extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Estudiante_model');
    }

    /**
     * POST /api/estudiantes_controller/consultar
     * Busca los datos de un estudiante mediante su CI y devuelve su RUDE/Código bancario.
     */
    public function consultar() {
        $data = $this->getJsonBody();
        $ci = $data['ci'] ?? $this->input->post('ci');

        if (empty(trim((string)$ci))) {
            $this->error('El CI es requerido', 400);
            return;
        }

        $ciStr = trim((string)$ci);

        $this->db->select('e.nombre_completo, e.codigo_banco as codigo, c.nombre as grado, c.paralelo');
        $this->db->from('estudiantes e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        $this->db->join('cursos c', 'c.id = ec.curso_id', 'left');
        $this->db->where('e.ci', $ciStr);
        $this->db->where('e.activo', 1);
        $this->db->limit(1);

        $query = $this->db->get();
        $estudiante = $query->row_array();

        if ($estudiante) {
            $this->success($estudiante, 'Estudiante encontrado');
        } else {
            $this->error('Estudiante no encontrado', 404);
        }
    }
}
