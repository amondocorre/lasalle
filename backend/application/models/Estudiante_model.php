<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Estudiante_model
 *
 * Gestiona todas las operaciones de base de datos para la tabla `estudiantes`.
 */
class Estudiante_model extends CI_Model
{
    /** Nombre de la tabla principal */
    private const TABLA = 'estudiantes';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lista estudiantes con búsqueda y paginación.
     *
     * @param string|null $search  Texto libre para buscar en nombre_completo/RUDE/CI
     * @param int|null    $cursoId Filtrar por curso
     * @param int         $page    Página actual
     * @param int         $limit   Resultados por página
     */
    public function listar(?string $search, $cursoId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        // --- 1. Obtener conteo total para paginación ---
        $this->db->select('COUNT(DISTINCT e.rude) as total', FALSE);
        $this->db->from(self::TABLA . ' e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        $this->db->where('e.activo', 1);

        if (! empty($search)) {
            $this->db->group_start();
            $this->db->like('e.nombre_completo', $search);
            $this->db->or_like('e.rude', $search);
            $this->db->or_like('e.ci', $search);
            $this->db->or_like('e.codigo_banco', $search);
            $this->db->group_end();
        }

        if ($cursoId === 'none') {
            $this->db->where('ec.curso_id IS NULL');
        } elseif (! empty($cursoId)) {
            $this->db->where('ec.curso_id', $cursoId);
        }

        $queryTotal = $this->db->get();
        $rowTotal   = $queryTotal->row();
        $total      = $rowTotal ? (int) $rowTotal->total : 0;

        // --- 2. Obtener los datos reales ---
        $this->db->select('e.*, c.id as curso_id, c.nombre as nombre_curso, c.turno, c.paralelo');
        $this->db->from(self::TABLA . ' e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        $this->db->join('cursos c', 'c.id = ec.curso_id', 'left');
        $this->db->where('e.activo', 1);

        if (! empty($search)) {
            $this->db->group_start();
            $this->db->like('e.nombre_completo', $search);
            $this->db->or_like('e.rude', $search);
            $this->db->or_like('e.ci', $search);
            $this->db->or_like('e.codigo_banco', $search);
            $this->db->group_end();
        }

        if ($cursoId === 'none') {
            $this->db->where('ec.curso_id IS NULL');
        } elseif (! empty($cursoId)) {
            $this->db->where('ec.curso_id', $cursoId);
        }

        // Agrupar por RUDE y columnas de curso para evitar el error only_full_group_by
        $this->db->group_by(['e.rude', 'c.id', 'c.nombre', 'c.turno', 'c.paralelo']);
        $this->db->order_by('e.nombre_completo', 'ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        
        return [
            'data'  => $query->result_array(),
            'total' => $total
        ];
    }

    /**
     * Obtiene un estudiante por su RUDE, incluyendo los datos de su curso.
     */
    public function obtenerPorRude(string $rude): ?array
    {
        $this->db->select('e.*, c.id as curso_id, c.nombre as nombre_curso, c.turno, c.paralelo, c.nivel');
        $this->db->from(self::TABLA . ' e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        $this->db->join('cursos c', 'c.id = ec.curso_id', 'left');
        $this->db->where('e.rude', $rude);
        $this->db->limit(1);

        $query  = $this->db->get();
        $result = $query->row_array();

        return $result ?: null;
    }

    /**
     * Verifica si un CI ya está registrado.
     */
    public function existeCI(string $ci, string $excludeRude = ''): bool
    {
        $this->db->where('ci', $ci);
        if (! empty($excludeRude)) {
            $this->db->where('rude !=', $excludeRude);
        }
        return $this->db->count_all_results(self::TABLA) > 0;
    }

    /**
     * Inserta un nuevo estudiante.
     *
     * @return bool TRUE si se insertó correctamente
     */
    public function crear(array $data): bool
    {
        $this->db->trans_start();

        // 1. Insertar en tabla estudiantes
        $campos = ['rude', 'ci', 'nombre_completo', 'codigo_banco', 'fecha_nac', 'sexo', 'foto'];
        $insert = array_intersect_key($data, array_flip($campos));
        $this->db->insert(self::TABLA, $insert);

        // 2. Insertar en tabla estudiante_curso si hay curso_id
        if (! empty($data['curso_id'])) {
            $this->db->insert('estudiante_curso', [
                'rude'     => $data['rude'],
                'curso_id' => $data['curso_id'],
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Actualiza los datos de un estudiante.
     */
    public function actualizar(string $rude, array $data): bool
    {
        $this->db->trans_start();

        // 1. Actualizar datos básicos
        $campos = ['ci', 'nombre_completo', 'codigo_banco', 'fecha_nac', 'sexo', 'foto', 'activo'];
        $update = array_intersect_key($data, array_flip($campos));

        if (! empty($update)) {
            $this->db->where('rude', $rude);
            $this->db->update(self::TABLA, $update);
        }

        // 2. Actualizar curso (estudiante_curso)
        if (isset($data['curso_id'])) {
            // Borrar asignación previa
            $this->db->where('rude', $rude);
            $this->db->delete('estudiante_curso');

            // Si no es nulo, insertar nueva
            if (! empty($data['curso_id'])) {
                $this->db->insert('estudiante_curso', [
                    'rude'     => $rude,
                    'curso_id' => $data['curso_id'],
                ]);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    /**
     * Soft delete: desactiva un estudiante sin borrar sus datos.
     */
    public function desactivar(string $rude): bool
    {
        $this->db->where('rude', $rude);
        return $this->db->update(self::TABLA, ['activo' => 0]);
    }
}
