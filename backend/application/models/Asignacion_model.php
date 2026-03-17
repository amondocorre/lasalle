<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asignacion_model extends CI_Model {

    private const TABLA = 'asignaciones';

    public function listar_por_curso(int $curso_id) {
        $this->db->select('a.*, m.nombre as materia_nombre, p.nombre as profesor_nombre');
        $this->db->from(self::TABLA . ' a');
        $this->db->join('materias m', 'm.id = a.materia_id');
        $this->db->join('profesores p', 'p.id = a.profesor_id');
        $this->db->where('a.curso_id', $curso_id);
        $this->db->where('a.activo', 1);
        return $this->db->get()->result();
    }

    public function crear_batch(array $data) {
        return $this->db->insert_batch(self::TABLA, $data);
    }

    public function eliminar(int $id) {
        // Antes de eliminar, verificar si tiene horarios vinculados
        $this->db->where('asignacion_id', $id);
        if ($this->db->count_all_results('horarios') > 0) {
            return false; // No se puede eliminar si tiene horarios
        }
        return $this->db->delete(self::TABLA, ['id' => $id]);
    }

    /**
     * Valida si un profesor tiene choque de horarios.
     * @return bool True si hay choque, False si está libre.
     */
    public function verificar_choque_profesor(int $profesor_id, string $dia, string $inicio, string $fin, ?int $horario_id_actual = null) {
        $this->db->select('h.id');
        $this->db->from('horarios h');
        $this->db->join('asignaciones a', 'a.id = h.asignacion_id');
        $this->db->where('a.profesor_id', $profesor_id);
        $this->db->where('h.dia', $dia);
        
        // Lógica de solapamiento de tiempo
        $this->db->group_start();
        $this->db->where("('$inicio' < h.hora_fin AND '$fin' > h.hora_inicio)");
        $this->db->group_end();

        if ($horario_id_actual) {
            $this->db->where('h.id !=', $horario_id_actual);
        }

        return $this->db->count_all_results() > 0;
    }
}
