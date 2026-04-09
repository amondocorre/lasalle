<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

class Reportes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Reporte_model');
    }

    public function monitor_rendimiento()
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes'); // Can be empty to fetch all year
        $tipo = $this->input->get('tipo_novedad'); // 'académico', 'conductual' o null
        
        $datos = $this->Reporte_model->get_monitor_rendimiento($gestion, $mes, $tipo);
        
        $umbral_alerta = 10;
        foreach ($datos as &$d) {
            if ($d['total_novedades'] == 0) {
                $d['nivel_alerta'] = 'verde';
            } else if ($d['total_novedades'] >= $umbral_alerta) {
                $d['nivel_alerta'] = 'rojo';
            } else {
                $d['nivel_alerta'] = 'amarillo';
            }
        }
        
        $this->success($datos);
    }
    
    public function detalle_curso($curso_id)
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes');
        $tipo = $this->input->get('tipo_novedad');
        
        $datos = $this->Reporte_model->get_detalle_curso($curso_id, $gestion, $mes, $tipo);
        
        $this->success($datos);
    }

    public function stats_dashboard()
    {
        $datos = $this->Reporte_model->get_cursos_con_novedades_stats(12);
        $this->success($datos);
    }

    public function licencias_monitoreo()
    {
        $fecha = $this->input->get('fecha');
        $datos = $this->Reporte_model->get_licencias_activas_hoy($fecha);
        $this->success($datos);
    }

    public function retrasos_stats()
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes');
        
        $datos = $this->Reporte_model->get_retrasos_stats($gestion, $mes);
        
        // Agregar calculos extras para el dashboard superior superior:
        $total_retrasos = 0;
        $max_retrasos = 0;
        $curso_max = 'N/A';
        
        foreach ($datos as $d) {
            $total_retrasos += $d['total_retrasos'];
            if ($d['total_retrasos'] > $max_retrasos) {
                $max_retrasos = $d['total_retrasos'];
                $curso_max = $d['curso_nombre'];
            }
        }
        
        $this->success([
            'resumen' => [
                'total_retrasos' => $total_retrasos,
                'curso_mas_retrasos' => $curso_max,
                'max_retrasos' => $max_retrasos
            ],
            'cursos' => $datos
        ]);
    }

    public function historial_retrasos($curso_id)
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes');
        
        $datos = $this->Reporte_model->get_detalle_retrasos_curso($curso_id, $gestion, $mes);
        $this->success($datos);
    }

    public function monitor_accesos_padres()
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $datos = $this->Reporte_model->get_acceso_padres_stats($gestion);
        $this->success($datos);
    }
}
