<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

class Calendario extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Calendario_model');
    }

    public function index()
    {
        $curso_id = $this->input->get('curso_id');
        $mes = $this->input->get('mes');
        $gestion = $this->input->get('gestion') ?: date('Y');

        if (!$curso_id || !$mes || !$gestion) {
            $this->error('Faltan parámetros requeridos (curso_id, mes, gestion).');
            return;
        }

        $datos = $this->Calendario_model->get_by_curso_month($curso_id, $mes, $gestion);
        $this->success($datos);
    }
    
    public function save()
    {
        $data = $this->getJsonBody();
        
        if (empty($data['curso_id']) || empty($data['titulo']) || empty($data['fecha']) || empty($data['usuario_id'])) {
            $this->error('Los campos curso, título, fecha y usuario son obligatorios.');
            return;
        }

        $id = $this->Calendario_model->insert([
            'curso_id'    => $data['curso_id'],
            'titulo'      => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha'       => $data['fecha'],
            'hora_inicio' => !empty($data['hora_inicio']) ? $data['hora_inicio'] : null,
            'hora_fin'    => !empty($data['hora_fin']) ? $data['hora_fin'] : null,
            'usuario_id'  => $data['usuario_id'],
        ]);

        if ($id) {
            $this->success(['id' => $id], 'Actividad registrada correctamente', 201);
        } else {
            $this->error('No se pudo registrar la actividad');
        }
    }
    
    public function delete($id)
    {
        if ($this->Calendario_model->delete($id)) {
            $this->success(null, 'Actividad eliminada correctamente');
        } else {
            $this->error('Error al eliminar la actividad');
        }
    }
}
