<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador de Autenticación (API REST)
 */
class Auth extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Usuario_model');
    }

    /**
     * POST /api/auth/login
     * Recibe username y password, devuelve datos del usuario y un token.
     */
    public function login() {
        $data = $this->getJsonBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->error('Usuario y contraseña son requeridos', 400);
            return;
        }

        $user = $this->Usuario_model->find_by_username($username);

        if (!$user || !password_verify($password, $user->password)) {
            $this->error('Credenciales inválidas o cuenta inactiva', 401);
            return;
        }

        // Generar un token simple (en producción usar JWT)
        $token = bin2hex(random_bytes(32));
        
        if ($this->Usuario_model->set_token($user->id, $token)) {
            $this->success([
                'user' => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'nombre'   => $user->nombre,
                    'rol'      => $user->rol,
                    'profesor_id' => $user->profesor_id
                ],
                'token' => $token
            ]);
        } else {
            $this->error('Error al iniciar sesión', 500);
        }
    }

    /**
     * POST /api/auth/login/padre
     * Login simplificado para padres usando el CI del alumno.
     */
    public function login_padre() {
        $data = $this->getJsonBody();
        $ci   = $data['ci'] ?? '';

        if (empty($ci)) {
            $this->error('El CI del alumno es requerido', 400);
            return;
        }

        $this->load->model('Estudiante_model');
        $estudiante = $this->Estudiante_model->obtenerPorCI($ci);

        if (!$estudiante) {
            $this->error('CI no encontrado o alumno inactivo', 404);
            return;
        }

        // Registrar log de acceso
        $this->db->insert('log_acceso_padres', [
            'ci_estudiante' => $estudiante['ci'],
            'ip_address'    => $this->input->ip_address(),
            'user_agent'    => $this->input->user_agent()
        ]);

        // Generamos un token temporal para el padre que incluya el CI
        $token = 'PADRE-' . $estudiante['ci'] . '-' . bin2hex(random_bytes(16));
        
        $this->success([
            'user' => [
                'id'        => 'P-' . $estudiante['ci'],
                'username'  => 'padre_' . $estudiante['ci'],
                'nombre'    => 'Padre/Madre de ' . $estudiante['nombre_completo'],
                'nombre_estudiante' => $estudiante['nombre_completo'],
                'codigo_banco' => $estudiante['codigo_banco'],
                'rude'      => $estudiante['rude'],
                'ci_estudiante' => $estudiante['ci'],
                'rol'       => 'padre',
                'curso_id'  => $estudiante['curso_id'],
                'curso_nombre' => $estudiante['nombre_curso'] . ' ' . $estudiante['paralelo']
            ],
            'token' => $token
        ]);
    }


    /**
     * GET /api/auth/me
     * Verifica el token enviado en el header Authorization.
     */
    public function me() {
        $token = $this->input->get_request_header('Authorization');
        
        // Respaldo por si el servidor filtra el header Authorization
        if (empty($token)) {
            $token = $this->input->server('HTTP_AUTHORIZATION');
        }

        // Quitar 'Bearer ' si existe
        $token = str_replace('Bearer ', '', $token ?? '');

        // CASO 1: Es un PADRE
        if (strpos($token, 'PADRE-') === 0) {
            $parts = explode('-', $token);
            $ci = $parts[1] ?? '';
            
            $this->load->model('Estudiante_model');
            $estudiante = $this->Estudiante_model->obtenerPorCI($ci);
            
            if (!$estudiante) {
                $this->error('Sesión de padre inválida', 401);
                return;
            }

            $this->success([
                'user' => [
                    'id'        => 'P-' . $estudiante['ci'],
                    'username'  => 'padre_' . $estudiante['ci'],
                    'nombre'    => 'Padre/Madre de ' . $estudiante['nombre_completo'],
                    'nombre_estudiante' => $estudiante['nombre_completo'],
                    'codigo_banco' => $estudiante['codigo_banco'],
                    'rude'      => $estudiante['rude'],
                    'ci_estudiante' => $estudiante['ci'],
                    'rol'       => 'padre',
                    'curso_id'  => $estudiante['curso_id'],
                    'curso_nombre' => $estudiante['nombre_curso'] . ' ' . $estudiante['paralelo']
                ]
            ]);
            return;
        }

        // CASO 2: Es un USUARIO normal (Admin/Regente/Profe)
        $user = $this->Usuario_model->find_by_token($token);

        if (!$user) {
            $this->error('Token inválido o expirado', 401);
            return;
        }

        $this->success([
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'nombre'   => $user->nombre,
                'rol'      => $user->rol,
                'profesor_id' => $user->profesor_id
            ]
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout() {
        $token = $this->input->get_request_header('Authorization');
        
        if (empty($token)) {
            $token = $this->input->server('HTTP_AUTHORIZATION');
        }

        $token = str_replace('Bearer ', '', $token ?? '');

        // Si es padre no hay nada que borrar en DB
        if (strpos($token, 'PADRE-') === 0) {
            $this->success(['message' => 'Sesión cerrada correctamente']);
            return;
        }

        $user = $this->Usuario_model->find_by_token($token);
        if ($user) {
            $this->Usuario_model->clear_token($user->id);
        }

        $this->success(['message' => 'Sesión cerrada correctamente']);
    }
}
