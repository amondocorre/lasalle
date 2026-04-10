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

        // 1. Eliminar TODOS los permisos actuales para este perfil específico
        $this->db->where('perfil_id', $perfil_id);
        $this->db->delete('perfil_menu');

        // 2. Insertar los nuevos permisos seleccionados
        if (!empty($menu_ids) && is_array($menu_ids)) {
            $batch_data = [];
            foreach ($menu_ids as $m_id) {
                // Aseguramos que el ID del menú sea válido
                if (!empty($m_id)) {
                    $batch_data[] = [
                        'perfil_id' => $perfil_id,
                        'menu_id' => $m_id,
                        'acceso_lectura' => 1,
                        'acceso_escritura' => 1
                    ];
                }
            }
            if (!empty($batch_data)) {
                $this->db->insert_batch('perfil_menu', $batch_data);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function obtenerMenusUsuario($perfil_id) {
        $this->db->select('m.*');
        $this->db->from('menus m');
        $this->db->join('perfil_menu pm', 'pm.menu_id = m.id');
        $this->db->where('pm.perfil_id', $perfil_id);
        $this->db->where('m.activo', 1); // Solo menús activos
        $this->db->order_by('m.orden', 'ASC');
        
        $query = $this->db->get();
        return $query ? $query->result_array() : [];
    }
}
