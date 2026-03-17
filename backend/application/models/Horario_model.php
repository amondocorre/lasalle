<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Horario_model
 * Gestiona la tabla `horarios` y la lógica de horario activo para el escaneo.
 */
class Horario_model extends CI_Model
{
    private const TABLA = 'horarios';

    /** Lista horarios filtrados por curso y/o día. */
    public function listar(?int $cursoId, ?string $dia, ?int $profesorId = null): array
    {
        $this->db->select('h.*, c.nombre as nombre_curso, 
                          COALESCE(m.nombre, h.materia) as materia, 
                          COALESCE(p.nombre, p2.nombre) as nombre_profesor,
                          COALESCE(a.color, h.color) as color');
        $this->db->from(self::TABLA . ' h');
        $this->db->join('cursos c', 'c.id = h.curso_id', 'left');
        $this->db->join('asignaciones a', 'a.id = h.asignacion_id', 'left');
        $this->db->join('materias m', 'm.id = a.materia_id', 'left');
        $this->db->join('profesores p', 'p.id = a.profesor_id', 'left');
        $this->db->join('profesores p2', 'p2.id = h.profesor_id', 'left');
        $this->db->where('h.activo', 1);

        if ($cursoId) {
            $this->db->where('h.curso_id', $cursoId);
        }
        if ($dia) {
            $this->db->where('h.dia', $dia);
        }
        if ($profesorId) {
            $this->db->group_start();
            $this->db->where('a.profesor_id', $profesorId);
            $this->db->or_where('h.profesor_id', $profesorId);
            $this->db->group_end();
        }

        $this->db->order_by('h.dia', 'ASC');
        $this->db->order_by('h.hora_inicio', 'ASC');
        return $this->db->get()->result_array();
    }

    /** Obtiene un horario por ID. */
    public function obtener(int $id): ?array
    {
        $result = $this->db->get_where(self::TABLA, ['id' => $id])->row_array();
        return $result ?: null;
    }

    /**
     * Busca el horario activo en este momento para el curso dado.
     *
     * Un horario "activo" es aquel cuyo día coincide con el día actual
     * y cuya hora_inicio <= hora_actual <= hora_fin.
     * Se añade una tolerancia de 30 min antes para registrar entrada anticipada.
     *
     * @param int|null $cursoId  ID del curso del estudiante
     * @param string   $dia      Nombre del día en español
     * @param string   $hora     Hora actual (HH:MM:SS)
     */
    public function obtenerHorarioActivo(?int $cursoId, string $dia, string $hora): ?array
    {
        if (! $cursoId) {
            return null;
        }

        // Tolerancia de 30 minutos antes del inicio del período
        $horaConTolerancia = date('H:i:s', strtotime($hora) - 1800);

        $this->db->select('h.*, COALESCE(m.nombre, h.materia) as materia');
        $this->db->from(self::TABLA . ' h');
        $this->db->join('asignaciones a', 'a.id = h.asignacion_id', 'left');
        $this->db->join('materias m', 'm.id = a.materia_id', 'left');
        $this->db->where('h.curso_id', $cursoId);
        $this->db->where('h.dia', $dia);
        $this->db->where('h.hora_inicio <=', $hora);
        $this->db->where('h.hora_fin >=', $horaConTolerancia);
        $this->db->where('h.activo', 1);
        $this->db->order_by('h.hora_inicio', 'ASC');
        $this->db->limit(1);

        $result = $this->db->get()->row_array();
        return $result ?: null;
    }

    /** Crea un horario y devuelve su ID. */
    public function crear(array $data): int
    {
        $campos = ['curso_id', 'asignacion_id', 'materia', 'dia', 'hora_inicio', 'hora_fin', 'profesor_id', 'color'];
        $insert = array_intersect_key($data, array_flip($campos));
        $this->db->insert(self::TABLA, $insert);
        return $this->db->insert_id();
    }

    /** Actualiza un horario. */
    public function actualizar(int $id, array $data): bool
    {
        $campos = ['asignacion_id', 'materia', 'dia', 'hora_inicio', 'hora_fin', 'activo', 'profesor_id', 'color'];
        $update = array_intersect_key($data, array_flip($campos));
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, $update);
    }

    /** Elimina un horario. */
    public function eliminar(int $id): bool
    {
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, ['activo' => 0]);
    }
}
