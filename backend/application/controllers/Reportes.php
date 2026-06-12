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
        $gravedad = $this->input->get('gravedad');
        
        $datos = $this->Reporte_model->get_monitor_rendimiento($gestion, $mes, $tipo, $gravedad);
        
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
        $gravedad = $this->input->get('gravedad');
        
        $datos = $this->Reporte_model->get_detalle_curso($curso_id, $gestion, $mes, $tipo, $gravedad);
        
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
        try {
            $gestion = $this->input->get('gestion') ?: date('Y');
            $datos = $this->Reporte_model->get_acceso_padres_stats($gestion);
            $this->success($datos);
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 500);
        } catch (\Error $e) {
            $this->error('Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 500);
        }
    }
    public function consolidado_mensual()
    {
        $curso_id = $this->input->get('curso_id');
        $mes      = $this->input->get('mes') ?: date('m');
        $anio     = $this->input->get('anio') ?: date('Y');

        if (!$curso_id) {
            $this->error('El curso_id es obligatorio', 400);
            return;
        }

        $datos = $this->Reporte_model->get_consolidado_mensual($curso_id, $anio, $mes);
        $this->success($datos);
    }

    // Obtener estadísticas agregadas de licencias
    public function licencias_stats()
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes');
        
        $datos = $this->Reporte_model->get_licencias_stats($gestion, $mes);
        
        $totalLicencias = 0;
        $maxLicencias = 0;
        $cursoMax = 'N/A';
        
        foreach ($datos as $d) {
            $totalLicencias += $d['total_licencias'];
            if ($d['total_licencias'] > $maxLicencias) {
                $maxLicencias = $d['total_licencias'];
                $cursoMax = $d['curso_nombre'];
            }
        }
        
        $this->success([
            'resumen' => [
                'total_licencias' => $totalLicencias,
                'curso_mas_licencias' => $cursoMax,
                'max_licencias' => $maxLicencias
            ],
            'cursos' => $datos
        ]);
    }

    // Obtener historial de licencias por curso
    public function historial_licencias($curso_id)
    {
        $gestion = $this->input->get('gestion') ?: date('Y');
        $mes = $this->input->get('mes');
        
        $datos = $this->Reporte_model->get_detalle_licencias_curso($curso_id, $gestion, $mes);
        $this->success($datos);
    }
}
