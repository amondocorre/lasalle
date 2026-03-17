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

        $user = $this->Usuario_model->find_by_token($token);
        if ($user) {
            $this->Usuario_model->clear_token($user->id);
        }

        $this->success(['message' => 'Sesión cerrada correctamente']);
    }
}
