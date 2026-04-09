<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permisos extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Permisos_model');
    }

    public function perfiles() {
        if ($this->input->method() == 'get') {
            $this->success($this->Permisos_model->obtenerPerfiles());
        } elseif ($this->input->method() == 'post') {
            $data = $this->getJsonBody();
            if (empty($data['nombre'])) {
                $this->error('Nombre requerido');
                return;
            }
            $id = $this->Permisos_model->crearPerfil($data['nombre'], $data['descripcion'] ?? '');
            if ($id) {
                $this->success(['id' => $id], 'Perfil creado', 201);
            } else {
                $this->error('Error al crear perfil');
            }
        }
    }

    public function perfil($id) {
        if ($this->input->method() == 'put') {
            $data = $this->getJsonBody();
            $this->Permisos_model->actualizarPerfil($id, $data['nombre'], $data['descripcion'] ?? '');
            $this->success(null, 'Perfil actualizado');
        } elseif ($this->input->method() == 'delete') {
            $this->Permisos_model->eliminarPerfil($id);
            $this->success(null, 'Perfil eliminado');
        }
    }

    public function menus() {
        $this->success($this->Permisos_model->obtenerMenus());
    }

    public function asignar($perfil_id) {
        if ($this->input->method() == 'get') {
            // Obtenemos los menus asignados a este perfil_id
            $menus = $this->Permisos_model->obtenerPermisosPorPerfil($perfil_id);
            $this->success($menus);
        } elseif ($this->input->method() == 'post' || $this->input->method() == 'put') {
            $data = $this->getJsonBody();
            // Data debe ser un array de id de menus
            if (isset($data['menus']) && is_array($data['menus'])) {
                $status = $this->Permisos_model->actualizarPermisos($perfil_id, $data['menus']);
                if ($status) {
                    $this->success(null, 'Permisos actualizados');
                } else {
                    $this->error('Error al actualizar permisos');
                }
            } else {
                $this->error('Datos de menú inválidos');
            }
        }
    }
}
