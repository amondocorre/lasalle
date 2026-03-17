<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Curso_model
 * Gestiona operaciones sobre la tabla `cursos` y la inscripción de estudiantes.
 */
class Curso_model extends CI_Model
{
    private const TABLA = 'cursos';

    /**
     * Lista cursos filtrados por gestión y turno.
     */
    public function listar(string $gestion, ?string $turno, ?int $profesorId = null): array
    {
        $this->db->select('c.*');
        $this->db->from(self::TABLA . ' c');
        $this->db->where('c.gestion', $gestion);
        $this->db->where('c.activo', 1);

        if ($turno) {
            $this->db->where('c.turno', $turno);
        }

        if ($profesorId) {
            // Filtrar cursos vinculados a este profesor vía asignaciones o horarios
            $this->db->group_start();
            $this->db->where("EXISTS (SELECT 1 FROM asignaciones a WHERE a.curso_id = c.id AND a.profesor_id = $profesorId AND a.activo = 1)", NULL, FALSE);
            $this->db->or_where("EXISTS (SELECT 1 FROM horarios h WHERE h.curso_id = c.id AND h.profesor_id = $profesorId AND h.activo = 1)", NULL, FALSE);
            $this->db->group_end();
        }

        $this->db->order_by('c.nombre', 'ASC');
        return $this->db->get()->result_array();
    }

    /** Obtiene un curso por ID. */
    public function obtener(int $id): ?array
    {
        $result = $this->db->get_where(self::TABLA, ['id' => $id])->row_array();
        return $result ?: null;
    }

    /** Crea un nuevo curso y devuelve su ID. */
    public function crear(array $data): int
    {
        $campos = ['nombre', 'nivel', 'turno', 'paralelo', 'gestion'];
        $insert = array_intersect_key($data, array_flip($campos));
        $this->db->insert(self::TABLA, $insert);
        return $this->db->insert_id();
    }

    /** Actualiza un curso. */
    public function actualizar(int $id, array $data): bool
    {
        $campos = ['nombre', 'nivel', 'turno', 'paralelo', 'gestion', 'activo'];
        $update = array_intersect_key($data, array_flip($campos));
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, $update);
    }

    /** Soft delete del curso. */
    public function eliminar(int $id): bool
    {
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, ['activo' => 0]);
    }

    /**
     * Lista los estudiantes inscritos en un curso específico.
     */
    public function listarEstudiantes(int $cursoId): array
    {
        $this->db->select('e.*');
        $this->db->from('estudiantes e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude');
        $this->db->where('ec.curso_id', $cursoId);
        $this->db->where('e.activo', 1);
        $this->db->order_by('e.apellidos', 'ASC');
        return $this->db->get()->result_array();
    }
}
