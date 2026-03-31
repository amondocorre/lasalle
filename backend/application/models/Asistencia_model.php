<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Asistencia_model
 * Gestiona el registro y consulta de asistencias.
 */
class Asistencia_model extends CI_Model
{
    private const TABLA = 'asistencias';

    /**
     * Lista asistencias con JOIN a estudiantes y horarios.
     */
    public function listar(?string $rude, ?int $cursoId, string $fecha): array
    {
        $this->db->select('a.*, e.nombre_completo, e.ci, 
                          COALESCE(m.nombre, h.materia) as materia, 
                          h.hora_inicio, h.hora_fin, 
                          c.nombre as nombre_curso, c.paralelo');
        $this->db->from(self::TABLA . ' a');
        $this->db->join('estudiantes e', 'e.rude = a.rude', 'left');
        $this->db->join('horarios h', 'h.id = a.horario_id', 'left');
        $this->db->join('asignaciones asig', 'asig.id = h.asignacion_id', 'left');
        $this->db->join('materias m', 'm.id = asig.materia_id', 'left');
        $this->db->join('cursos c', 'c.id = a.curso_id', 'left');
        $this->db->where('a.fecha', $fecha);

        if ($rude) {
            $this->db->where('a.rude', $rude);
        }
        if ($cursoId) {
            $this->db->where('a.curso_id', $cursoId);
        }

        $this->db->order_by('c.nombre', 'ASC');
        $this->db->order_by('c.paralelo', 'ASC');
        $this->db->order_by('e.nombre_completo', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Verifica si un estudiante ya tiene asistencia en ese horario/fecha.
     * Si no hay horario_id (entrada general), verifica solo por rude+fecha.
     */
    public function existeRegistro(string $rude, ?int $horarioId, string $fecha): bool
    {
        $this->db->where('rude', $rude);
        $this->db->where('fecha', $fecha);

        if ($horarioId) {
            $this->db->where('horario_id', $horarioId);
        }

        return $this->db->count_all_results(self::TABLA) > 0;
    }

    /**
     * Registra una nueva asistencia y devuelve el ID.
     */
    public function registrar(array $data): int
    {
        $campos = ['rude', 'curso_id', 'horario_id', 'fecha', 'hora_registro', 'estado', 'registrado_por', 'observacion'];
        $insert = array_intersect_key($data, array_flip($campos));
        $this->db->insert(self::TABLA, $insert);
        return $this->db->insert_id();
    }

    /**
     * Reporte de asistencias agrupado por estudiante en un rango de fechas.
     */
    public function reporte(?int $cursoId, string $fechaInicio, string $fechaFin): array
    {
        $this->db->select('
            e.rude,
            e.nombre_completo,
            COUNT(CASE WHEN a.estado = "presente"  THEN 1 END) as presentes,
            COUNT(CASE WHEN a.estado = "ausente"   THEN 1 END) as ausentes,
            COUNT(CASE WHEN a.estado = "tardanza"  THEN 1 END) as tardanzas,
            COUNT(CASE WHEN a.estado = "licencia"  THEN 1 END) as licencias,
            COUNT(a.id) as total
        ');
        $this->db->from('estudiantes e');
        $this->db->join(self::TABLA . ' a', 'a.rude = e.rude', 'left');
        $this->db->where('a.fecha >=', $fechaInicio);
        $this->db->where('a.fecha <=', $fechaFin);

        if ($cursoId) {
            $this->db->where('a.curso_id', $cursoId);
        }

        $this->db->group_by('e.rude');
        $this->db->order_by('e.nombre_completo', 'ASC');
        return $this->db->get()->result_array();
    }
}
