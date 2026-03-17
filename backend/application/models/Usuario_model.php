<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo para la gestión de usuarios y autenticación.
 */
class Usuario_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lista todos los usuarios.
     */
    public function listar() {
        $query = $this->db->get('usuarios');
        return $query->result();
    }

    /**
     * Obtiene un usuario por ID.
     */
    public function find_by_id($id) {
        return $this->db->get_where('usuarios', ['id' => $id])->row();
    }

    /**
     * Crea un nuevo usuario.
     */
    public function crear($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->db->insert('usuarios', $data);
        return $this->db->insert_id();
    }

    /**
     * Actualiza un usuario.
     */
    public function actualizar($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['token'] = NULL; // Forzar relogin si cambia password
        } else {
            unset($data['password']);
        }
        $this->db->where('id', $id);
        return $this->db->update('usuarios', $data);
    }

    /**
     * Elimina (o desactiva) un usuario.
     */
    public function eliminar($id) {
        $this->db->where('id', $id);
        return $this->db->delete('usuarios');
    }

    /**
     * Busca un usuario por su username.
     */
    public function find_by_username($username) {
        $query = $this->db->get_where('usuarios', [
            'username' => $username,
            'activo'   => 1
        ]);
        return $query->row();
    }

    /**
     * Busca un usuario por su token de sesión.
     */
    public function find_by_token($token) {
        if (empty($token)) return NULL;
        
        $query = $this->db->get_where('usuarios', [
            'token'  => $token,
            'activo' => 1
        ]);
        return $query->row();
    }

    /**
     * Actualiza el token y la fecha de último login para un usuario.
     */
    public function set_token($user_id, $token) {
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios', [
            'token'        => $token,
            'ultimo_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Elimina el token (logout).
     */
    public function clear_token($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios', ['token' => NULL]);
    }
}
