<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Profesor_model
 * Gestiona la información del personal docente.
 */
class Profesor_model extends CI_Model
{
    private const TABLA = 'profesores';

    /**
     * Lista todos los profesores.
     */
    public function listar(): array
    {
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get(self::TABLA)->result_array();
    }

    /**
     * Obtiene un profesor por su ID.
     */
    public function obtener_por_id(int $id): ?array
    {
        return $this->db->get_where(self::TABLA, ['id' => $id])->row_array();
    }

    /**
     * Crea un nuevo profesor.
     */
    public function crear(array $data): int
    {
        $this->db->insert(self::TABLA, [
            'nombre'    => $data['nombre'],
            'telefono'  => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'activo'    => 1
        ]);
        return $this->db->insert_id();
    }

    /**
     * Actualiza los datos de un profesor.
     */
    public function actualizar(int $id, array $data): bool
    {
        $campos = ['nombre', 'telefono', 'direccion', 'activo'];
        $update = array_intersect_key($data, array_flip($campos));
        
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, $update);
    }

    /**
     * Elimina (o marca como inactivo) un profesor.
     */
    public function eliminar(int $id): bool
    {
        // En este caso, permitimos eliminación física o lógica. 
        // Usaremos lógica por seguridad si se prefiere, 
        // pero el requerimiento pide "dar de baja" (activo=0).
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, ['activo' => 0]);
    }
}
