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
        // Antes de eliminar, verificar si tiene horarios ACTIVOS vinculados
        $this->db->where('asignacion_id', $id);
        $this->db->where('activo', 1);
        if ($this->db->count_all_results('horarios') > 0) {
            return false; // No se puede eliminar si tiene horarios activos
        }
        
        // Si hay horarios inactivos (soft-deleted), los borramos físicamente 
        // para que dejen de bloquear la eliminación de la asignación
        $this->db->where('asignacion_id', $id);
        $this->db->delete('horarios');

        return $this->db->delete(self::TABLA, ['id' => $id]);
    }

    /**
     * Valida si un profesor tiene choque de horarios.
     * @return bool True si hay choque, False si está libre.
     */
    public function verificar_choque_profesor(int $profesor_id, string $dia, string $inicio, string $fin, ?int $horario_id_actual = null) {
        $this->db->select('h.id');
        $this->db->from('horarios h');
        // Unimos con asignaciones para saber de quién es el horario
        $this->db->join('asignaciones a', 'a.id = h.asignacion_id', 'left');
        
        $this->db->where('h.activo', 1);
        $this->db->where('h.dia', $dia);
        
        // El choque ocurre si:
        // 1. La asignación vinculada es del mismo profesor y está activa
        // 2. O si el horario tiene el profesor_id asignado manualmente (legacy)
        $this->db->group_start();
            $this->db->group_start();
                $this->db->where('a.profesor_id', $profesor_id);
                $this->db->where('a.activo', 1);
            $this->db->group_end();
            $this->db->or_where('h.profesor_id', $profesor_id);
        $this->db->group_end();
        
        // Lógica de solapamiento de tiempo: (Inicio1 < Fin2) AND (Fin1 > Inicio2)
        $this->db->where('h.hora_inicio <', $fin);
        $this->db->where('h.hora_fin >', $inicio);

        if ($horario_id_actual) {
            $this->db->where('h.id !=', $horario_id_actual);
        }

        $query = $this->db->get();
        return $query->num_rows() > 0;
    }
}
