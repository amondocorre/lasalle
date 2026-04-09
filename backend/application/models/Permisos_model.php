<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permisos_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function obtenerPerfiles() {
        return $this->db->get('perfiles')->result_array();
    }

    public function crearPerfil($nombre, $descripcion) {
        $this->db->insert('perfiles', [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
        return $this->db->insert_id();
    }

    public function actualizarPerfil($id, $nombre, $descripcion) {
        $this->db->where('id', $id);
        return $this->db->update('perfiles', [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
    }

    public function eliminarPerfil($id) {
        $this->db->where('id', $id);
        return $this->db->delete('perfiles');
    }

    public function obtenerMenus() {
        $this->db->order_by('orden', 'ASC');
        $query = $this->db->get('menus');
        return $query ? $query->result_array() : [];
    }

    public function obtenerPermisosPorPerfil($perfil_id) {
        $this->db->where('perfil_id', $perfil_id);
        $query = $this->db->get('perfil_menu');
        if (!$query) return [];
        $result = $query->result_array();
        
        // Retornar un array directo de menu_ids para facilitar el frontend
        $menu_ids = [];
        foreach ($result as $row) {
            $menu_ids[] = $row['menu_id'];
        }
        return $menu_ids;
    }

    public function actualizarPermisos($perfil_id, $menu_ids) {
        $this->db->trans_start();

        // Eliminar permisos actuales
        $this->db->where('perfil_id', $perfil_id);
        $this->db->delete('perfil_menu');

        // Insertar nuevos permisos
        if (!empty($menu_ids) && is_array($menu_ids)) {
            $data = [];
            foreach ($menu_ids as $m_id) {
                $data[] = [
                    'perfil_id' => $perfil_id,
                    'menu_id' => $m_id
                ];
            }
            $this->db->insert_batch('perfil_menu', $data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function obtenerMenusUsuario($perfil_id) {
        $this->db->select('m.*');
        $this->db->from('menus m');
        $this->db->join('perfil_menu pm', 'pm.menu_id = m.id');
        $this->db->where('pm.perfil_id', $perfil_id);
        $this->db->order_by('m.orden', 'ASC');
        return $this->db->get()->result_array();
    }
}
