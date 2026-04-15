<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retraso_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function listar($filtros = []) {
        $this->db->select('r.*, e.nombre_completo, c.nombre as nombre_curso');
        $this->db->from('retrasos r');
        $this->db->join('estudiantes e', 'r.rude = e.rude');
        $this->db->join('cursos c', 'e.curso_id = c.id', 'left');

        if (!empty($filtros['fecha'])) {
            $this->db->where('r.fecha', $filtros['fecha']);
        }
        if (!empty($filtros['rude'])) {
            $this->db->where('r.rude', $filtros['rude']);
        }

        $this->db->order_by('r.fecha', 'DESC');
        $this->db->order_by('r.hora', 'DESC');
        
        return $this->db->get()->result_array();
    }

    public function obtenerPorEstudiante($rude, $filtros = []) {
        $this->db->select('r.*');
        $this->db->from('retrasos r');
        $this->db->where('r.rude', $rude);

        if (!empty($filtros['fecha_desde'])) {
            $this->db->where('r.fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $this->db->where('r.fecha <=', $filtros['fecha_hasta']);
        }

        $this->db->order_by('r.fecha', 'DESC');
        $this->db->order_by('r.hora', 'DESC');
        return $this->db->get()->result_array();
    }

    public function registrar($data) {
        $this->db->insert('retrasos', $data);
        return $this->db->insert_id();
    }

    public function eliminar($id) {
        return $this->db->delete('retrasos', ['id' => $id]);
    }
}
