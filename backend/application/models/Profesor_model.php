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
        // Ajustamos la consulta: en producción la relación es usuarios.profesor_id -> profesores.id
        $this->db->select('p.id, p.nombre, p.telefono, p.direccion, p.activo, p.created_at, perf.nombre as nombre_perfil, u.username, u.perfil_id');
        $this->db->from('profesores p');
        $this->db->join('usuarios u', 'u.profesor_id = p.id', 'left');
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
        $this->db->select('p.*, u.id as user_id, u.username, u.perfil_id, perf.nombre as nombre_perfil');
        $this->db->from('profesores p');
        $this->db->join('usuarios u', 'u.profesor_id = p.id', 'left');
        $this->db->join('perfiles perf', 'perf.id = u.perfil_id', 'left');
        $this->db->where('p.id', $id);
        $profesor = $this->db->get()->row_array();

        if ($profesor) {
            // Si no tiene perfil_id (porque no tiene usuario), le ponemos 0 o null explícito 
            // para que el frontend no se confunda
            $profesor['perfil_id'] = $profesor['perfil_id'] ?? null;
            $profesor['username'] = $profesor['username'] ?? '';
            // Materias: obtenemos solo los IDs para que el select múltiple del frontend los marque
            $this->db->select('materia_id');
            $this->db->where('profesor_id', $id);
            $mat = $this->db->get('profesor_materia')->result_array();
            $profesor['materias'] = array_column($mat, 'materia_id');

            // Entrevistas: traemos el horario para padres
            $this->db->where('profesor_id', $id);
            $profesor['entrevistas'] = $this->db->get('profesor_entrevistas')->result_array();

            // Carga Académica (Cursos asignados)
            $this->db->select('c.nombre as curso_nombre, c.paralelo, c.turno, m.nombre as materia_nombre');
            $this->db->from('asignaciones a');
            $this->db->join('cursos c', 'c.id = a.curso_id');
            $this->db->join('materias m', 'm.id = a.materia_id');
            $this->db->where('a.profesor_id', $id);
            $profesor['asignaciones'] = $this->db->get()->result_array();
        }
        return $profesor;
    }

    public function crear(array $data): int
    {
        // 1. Insertar profesor (Datos básicos)
        $this->db->insert(self::TABLA, [
            'nombre'    => $data['nombre'],
            'telefono'  => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'activo'    => 1
        ]);
        $id = $this->db->insert_id();

        // 2. Insertar Usuario si se proporcionó
        if (!empty($data['username']) && !empty($data['password'])) {
            $perfil_id = $data['perfil_id'] ?? 2; // Default Profesor
            $this->db->insert('usuarios', [
                'username'    => $data['username'],
                'password'    => password_hash($data['password'], PASSWORD_BCRYPT),
                'nombre'      => $data['nombre'],
                'perfil_id'   => $perfil_id,
                'profesor_id' => $id,
                'rol'         => ($perfil_id == 1) ? 'admin' : ($perfil_id == 2 ? 'profesor' : 'regente'),
                'activo'      => 1
            ]);
        }

        // 3. Materias
        if (!empty($data['materias']) && is_array($data['materias'])) {
            $batch = [];
            foreach ($data['materias'] as $m_id) {
                $batch[] = ['profesor_id' => $id, 'materia_id' => $m_id];
            }
            $this->db->insert_batch('profesor_materia', $batch);
        }
        
        // 4. Entrevistas (Horarios de atención)
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

    public function actualizar(int $id, array $data): bool
    {
        $campos = ['nombre', 'telefono', 'direccion', 'activo'];
        $update = array_intersect_key($data, array_flip($campos));
        
        $prof = $this->obtener_por_id($id);
        
        // 1. Manejo del Usuario asociado
        if (!empty($prof['user_id'])) {
            // Si ya tiene usuario, actualizamos lo que haya cambiado
            $user_update = [];
            if (!empty($data['password'])) {
                $user_update['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            if (!empty($data['username'])) {
                $user_update['username'] = $data['username'];
            }
            if (isset($data['perfil_id'])) {
                $user_update['perfil_id'] = $data['perfil_id'];
                $user_update['rol'] = ($data['perfil_id'] == 1) ? 'admin' : ($data['perfil_id'] == 2 ? 'profesor' : 'regente');
            }
            if (!empty($user_update)) {
                $this->db->where('id', $prof['user_id']);
                $this->db->update('usuarios', $user_update);
            }
        } 
        // 2. Si NO tiene usuario, pero se envió nombre de usuario AHORA
        else if (!empty($data['username'])) {
            $this->db->insert('usuarios', [
                'username'    => $data['username'],
                'password'    => password_hash($data['password'] ?? '123456', PASSWORD_BCRYPT),
                'nombre'      => $data['nombre'] ?? $prof['nombre'],
                'perfil_id'   => $data['perfil_id'] ?? 3,
                'profesor_id' => $id,
                'activo'      => 1
            ]);
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
