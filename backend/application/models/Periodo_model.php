<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periodo_model extends CI_Model {
    private const TABLA = 'periodos';

    public function listar() {
        $this->db->where('activo', 1);
        $this->db->order_by('orden', 'ASC');
        return $this->db->get(self::TABLA)->result();
    }

    public function crear(array $data) {
        $this->db->insert(self::TABLA, $data);
        return $this->db->insert_id();
    }

    public function actualizar(int $id, array $data) {
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, $data);
    }

    public function eliminar(int $id) {
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, ['activo' => 0]);
    }
}
