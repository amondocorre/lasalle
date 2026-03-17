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
     * @param string|null $search  Texto libre para buscar en nombres/apellidos/RUDE/CI
     * @param int|null    $cursoId Filtrar por curso
     * @param int         $page    Página actual
     * @param int         $limit   Resultados por página
     */
    public function listar(?string $search, ?int $cursoId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('e.*, c.nombre as nombre_curso, c.turno, c.paralelo');
        $this->db->from(self::TABLA . ' e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        $this->db->join('cursos c', 'c.id = ec.curso_id', 'left');
        $this->db->where('e.activo', 1);

        // Búsqueda por texto libre
        if (! empty($search)) {
            $this->db->group_start();
            $this->db->like('e.nombres', $search);
            $this->db->or_like('e.apellidos', $search);
            $this->db->or_like('e.rude', $search);
            $this->db->or_like('e.ci', $search);
            $this->db->group_end();
        }

        // Filtro por curso
        if ($cursoId) {
            $this->db->where('ec.curso_id', $cursoId);
        }

        $this->db->order_by('e.apellidos', 'ASC');
        $this->db->order_by('e.nombres', 'ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result_array();
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
        // Campos permitidos para inserción
        $campos = ['rude', 'ci', 'nombres', 'apellidos', 'fecha_nac', 'sexo', 'foto'];
        $insert = array_intersect_key($data, array_flip($campos));

        return $this->db->insert(self::TABLA, $insert);
    }

    /**
     * Actualiza los datos de un estudiante.
     */
    public function actualizar(string $rude, array $data): bool
    {
        // Campos permitidos para actualización
        $campos  = ['ci', 'nombres', 'apellidos', 'fecha_nac', 'sexo', 'foto', 'activo'];
        $update  = array_intersect_key($data, array_flip($campos));

        if (empty($update)) {
            return false;
        }

        $this->db->where('rude', $rude);
        return $this->db->update(self::TABLA, $update);
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
