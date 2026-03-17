<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador para las estadísticas del Dashboard.
 */
class Dashboard extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cargamos los modelos necesarios para contar registros
        $this->load->model(['Estudiante_model', 'Licencia_model', 'Asistencia_model', 'Horario_model']);
    }

    /**
     * GET /api/dashboard/stats
     * Retorna un resumen de estadísticas para la pantalla principal.
     */
    public function stats(): void
    {
        try {
            // Estudiantes activos
            $estudiantesCount = $this->db->where('activo', 1)->count_all_results('estudiantes');

            // Asistencias de hoy
            $asistenciasHoy = $this->db->where('fecha', date('Y-m-d'))->count_all_results('asistencias');

            // Licencias de hoy
            $licenciasHoy = $this->db->where('fecha_inicio', date('Y-m-d'))->count_all_results('licencias');

            // Cursos activos (gestión actual)
            $gestionActual = date('Y');
            $cursosActivos = $this->db->where('activo', 1)
                                      ->where('gestion', $gestionActual)
                                      ->count_all_results('cursos');

            $this->success([
                'estudiantes' => $estudiantesCount,
                'asistencias_hoy' => $asistenciasHoy,
                'licencias_hoy' => $licenciasHoy,
                'cursos_activos' => $cursosActivos
            ]);
        } catch (Exception $e) {
            $this->error('No se pudieron obtener las estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
