<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador de Usuarios (Administración)
 */
class Usuarios extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Usuario_model');
        
        // Verificar que sea admin (opcional: implementar middleware real)
        $this->verificar_admin();
    }

    private function verificar_admin() {
        $token = $this->input->get_request_header('Authorization');
        
        // Respaldo si el header es filtrado por el servidor
        if (empty($token)) {
            $token = $this->input->server('HTTP_AUTHORIZATION');
        }

        $token = str_replace('Bearer ', '', $token ?? '');
        $user = $this->Usuario_model->find_by_token($token);
        
        if (!$user || $user->rol !== 'admin') {
            $this->error('No tiene permisos para acceder a este módulo', 403);
            exit; // Detenemos la ejecución aquí
        }
    }

    /**
     * GET /api/usuarios
     */
    public function index() {
        $usuarios = $this->Usuario_model->listar();
        // Quitar contraseñas de la respuesta
        foreach ($usuarios as &$u) {
            unset($u->password);
        }
        $this->success($usuarios);
    }

    /**
     * POST /api/usuarios
     */
    public function store() {
        $data = $this->getJsonBody();
        
        if (empty($data['username']) || empty($data['password']) || empty($data['nombre'])) {
            $this->error('Faltan datos obligatorios', 400);
            return;
        }

        if ($this->Usuario_model->find_by_username($data['username'])) {
            $this->error('El nombre de usuario ya existe', 409);
            return;
        }

        $id = $this->Usuario_model->crear($data);
        if ($id) {
            $nuevo = $this->Usuario_model->find_by_id($id);
            unset($nuevo->password);
            $this->success($nuevo, 'Usuario creado correctamente', 201);
        } else {
            $this->error('Error al crear el usuario', 500);
        }
    }

    /**
     * PUT /api/usuarios/(:num)
     */
    public function update($id) {
        $data = $this->getJsonBody();
        
        if ($this->Usuario_model->actualizar($id, $data)) {
            $modificado = $this->Usuario_model->find_by_id($id);
            unset($modificado->password);
            $this->success($modificado, 'Usuario actualizado correctamente');
        } else {
            $this->error('Error al actualizar el usuario', 500);
        }
    }

    /**
     * DELETE /api/usuarios/(:num)
     */
    public function destroy($id) {
        // No permitir borrarse a sí mismo
        $token = $this->input->get_request_header('Authorization');
        $token = str_replace('Bearer ', '', $token);
        $me = $this->Usuario_model->find_by_token($token);

        if ($me && $me->id == $id) {
            $this->error('No puedes eliminar tu propia cuenta', 400);
            return;
        }

        if ($this->Usuario_model->eliminar($id)) {
            $this->success(null, 'Usuario eliminado correctamente');
        } else {
            $this->error('Error al eliminar el usuario', 500);
        }
    }
}
