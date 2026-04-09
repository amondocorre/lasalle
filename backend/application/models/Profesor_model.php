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
     * Lista todos los profesores con su perfil.
     */
    public function listar(): array
    {
        // Ajustamos la consulta: quitamos p.perfil_id porque no existe en producción
        // y perfil_id se maneja desde la tabla de usuarios.
        $this->db->select('p.id, p.nombre, p.telefono, p.direccion, p.activo, p.created_at, p.usuario_id, perf.nombre as nombre_perfil, u.username, u.perfil_id');
        $this->db->from('profesores p');
        $this->db->join('usuarios u', 'u.id = p.usuario_id', 'left');
        $this->db->join('perfiles perf', 'perf.id = u.perfil_id', 'left');
        $this->db->order_by('p.nombre', 'ASC');
        
        $query = $this->db->get();
        if (!$query) {
            $error = $this->db->error();
            // Retornamos el error detallado y la consulta que falló
            return [[
                'id' => 0,
                'nombre' => 'SQL ERROR: ' . ($error['message'] ?? 'Unknown'),
                'telefono' => 'DEBUG',
                'direccion' => $this->db->last_query(),
                'activo' => 0,
                'nombre_perfil' => 'ERROR',
                'username' => 'error'
            ]];
        }
        
        return $query->result_array();
    }

    /**
     * Obtiene un profesor por su ID.
     */
    public function obtener_por_id(int $id): ?array
    {
        $this->db->select('p.id, p.nombre, p.telefono, p.direccion, p.activo, p.usuario_id, u.username, u.perfil_id');
        $this->db->from('profesores p');
        $this->db->join('usuarios u', 'u.id = p.usuario_id', 'left');
        $this->db->where('p.id', $id);
        $profesor = $this->db->get()->row_array();

        if ($profesor) {
            // Materias
            $this->db->select('materia_id');
            $this->db->where('profesor_id', $id);
            $mat = $this->db->get('profesor_materia')->result_array();
            $profesor['materias'] = array_column($mat, 'materia_id');

            // Entrevistas
            $this->db->where('profesor_id', $id);
            $profesor['entrevistas'] = $this->db->get('profesor_entrevistas')->result_array();

            // Cursos y Materias Asignadas (Carga Académica)
            $this->db->select('c.nombre as curso_nombre, c.paralelo, c.turno, m.nombre as materia_nombre');
            $this->db->from('asignaciones a');
            $this->db->join('cursos c', 'c.id = a.curso_id');
            $this->db->join('materias m', 'm.id = a.materia_id');
            $this->db->where('a.profesor_id', $id);
            $profesor['asignaciones'] = $this->db->get()->result_array();
        }
        return $profesor;
    }

    /**
     * Crea un nuevo profesor/personal y su usuario si corresponde.
     */
    public function crear(array $data): int
    {
        $usuario_id = null;

        // Si se envió información para crear usuario
        if (!empty($data['username']) && !empty($data['password'])) {
            $user_data = [
                'username' => $data['username'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'nombre'   => $data['nombre'],
                'perfil_id'=> $data['perfil_id'] ?? 3, // Default profesor if none
                'activo'   => 1
            ];
            $this->db->insert('usuarios', $user_data);
            $usuario_id = $this->db->insert_id();
        }

        $this->db->insert(self::TABLA, [
            'nombre'    => $data['nombre'],
            'telefono'  => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'perfil_id' => $data['perfil_id'] ?? null,
            'usuario_id'=> $usuario_id,
            'activo'    => 1
        ]);
        $id = $this->db->insert_id();
        
        // Solo si es perfil profesor o tiene materias
        if (!empty($data['materias']) && is_array($data['materias'])) {
            $batch = [];
            foreach ($data['materias'] as $m_id) {
                $batch[] = ['profesor_id' => $id, 'materia_id' => $m_id];
            }
            $this->db->insert_batch('profesor_materia', $batch);
        }
        
        // Entrevistas
        if (!empty($data['entrevistas']) && is_array($data['entrevistas'])) {
            $batch_e = [];
            foreach ($data['entrevistas'] as $e) {
                if (!empty($e['dia']) && !empty($e['hora_inicio'])) {
                    $batch_e[] = [
                        'profesor_id' => $id,
                        'dia'         => $e['dia'],
                        'hora_inicio' => $e['hora_inicio'],
                        'hora_fin'    => $e['hora_fin'] ?? '00:00:00'
                    ];
                }
            }
            if (!empty($batch_e)) $this->db->insert_batch('profesor_entrevistas', $batch_e);
        }
        
        return $id;
    }

    /**
     * Actualiza los datos de un profesor.
     */
    public function actualizar(int $id, array $data): bool
    {
        $campos = ['nombre', 'telefono', 'direccion', 'activo', 'perfil_id'];
        $update = array_intersect_key($data, array_flip($campos));
        
        // Si el profesor ya tiene usuario y se envió password nuevo
        $prof = $this->obtener_por_id($id);
        if ($prof['usuario_id'] && !empty($data['password'])) {
            $this->db->where('id', $prof['usuario_id']);
            $this->db->update('usuarios', [
                'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);
        } 
        // Si no tiene usuario y se envió datos para crearlo
        else if (!$prof['usuario_id'] && !empty($data['username']) && !empty($data['password'])) {
            $user_data = [
                'username' => $data['username'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'nombre'   => $data['nombre'],
                'perfil_id'=> $data['perfil_id'] ?? 3,
                'activo'   => 1
            ];
            $this->db->insert('usuarios', $user_data);
            $update['usuario_id'] = $this->db->insert_id();
        }

        $this->db->where('id', $id);
        $res = $this->db->update(self::TABLA, $update);
        
        if (isset($data['materias']) && is_array($data['materias'])) {
            $this->db->delete('profesor_materia', ['profesor_id' => $id]);
            if (!empty($data['materias'])) {
                $batch = [];
                foreach ($data['materias'] as $m_id) {
                    $batch[] = ['profesor_id' => $id, 'materia_id' => $m_id];
                }
                $this->db->insert_batch('profesor_materia', $batch);
            }
        }

        // Entrevistas
        if (isset($data['entrevistas']) && is_array($data['entrevistas'])) {
            $this->db->delete('profesor_entrevistas', ['profesor_id' => $id]);
            $batch_e = [];
            foreach ($data['entrevistas'] as $e) {
                if (!empty($e['dia']) && !empty($e['hora_inicio'])) {
                    $batch_e[] = [
                        'profesor_id' => $id,
                        'dia'         => $e['dia'],
                        'hora_inicio' => $e['hora_inicio'],
                        'hora_fin'    => $e['hora_fin'] ?? '00:00:00'
                    ];
                }
            }
            if (!empty($batch_e)) $this->db->insert_batch('profesor_entrevistas', $batch_e);
        }
        
        return $res;
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
