<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador para gestionar Novedades (Observaciones académicas y conductuales)
 */
class Novedades extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Novedad_model');
    }

    /**
     * GET /api/novedades
     * Obtiene el listado de novedades
     */
    public function index(): void
    {
        try {
            $novedades = $this->Novedad_model->get_all();
            $this->success($novedades);
        } catch (Exception $e) {
            $this->error('Error al obtener novedades: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/novedades/config
     * Obtiene los indicadores configurables
     */
    public function config(): void
    {
        try {
            $this->db->where('activo', 1);
            $res = $this->db->get('config_indicadores')->result_array();
            
            $config = [
                'academic' => [],
                'behavioral' => [],
                'presentation' => []
            ];
            
            foreach ($res as $row) {
                $key = 'academic';
                if ($row['tipo'] === 'conductual') $key = 'behavioral';
                if ($row['tipo'] === 'presentación') $key = 'presentation';
                
                $config[$key][] = [
                    'id' => $row['id'],
                    'text' => $row['indicador'],
                    'icon' => $row['icono']
                ];
            }
            
            $this->success($config);
        } catch (Exception $e) {
            $this->error('Error al obtener configuración: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/novedades
     * Registra una nueva novedad
     */
    public function store(): void
    {
        $input = $this->getJsonBody();

        $rude = $input['rude'] ?? null;
        $materia_id = !empty($input['materia_id']) ? $input['materia_id'] : null;
        $comentario_docente = $input['comentario_docente'] ?? null;
        $usuario_id = $input['usuario_id'] ?? 1; 
        $indicadores = $input['indicadores'] ?? [];
        $gravedad = $input['gravedad'] ?? 'Leve';

        // Determinar si hay indicadores de presentación (flexibilidad con tildes)
        $hasPresentation = false;
        if (!empty($indicadores)) {
            foreach ($indicadores as $ind) {
                $tipo = strtolower($ind['tipo'] ?? '');
                // Comprobar variaciones comunes o coincidencia parcial
                if ($tipo === 'presentación' || $tipo === 'presentacion' || strpos($tipo, 'presen') !== false) {
                    $hasPresentation = true;
                    break;
                }
            }
        }

        if (!$rude) {
            $this->error('El estudiante es obligatorio.', 400);
            return;
        }

        if (!$hasPresentation && !$materia_id) {
            $this->error('La materia es obligatoria para este tipo de registro.', 400);
            return;
        }

        // Validación de comentario obligatorio para faltas graves
        if (($gravedad === 'Grave' || $gravedad === 'Muy Grave') && empty(trim($comentario_docente))) {
            $this->error('El campo "Observación Detallada" es obligatorio para faltas Graves o Muy Graves.', 400);
            return;
        }

        try {
            $novedad_id = $this->Novedad_model->insert([
                'rude' => $rude,
                'materia_id' => $materia_id,
                'usuario_id' => $usuario_id,
                'comentario_docente' => $comentario_docente,
                'gravedad' => $gravedad
            ]);

            // Insertar indicadores
            if ($novedad_id && !empty($indicadores)) {
                $this->Novedad_model->insert_indicadores($novedad_id, $indicadores);
            }

            // Verificar reincidencia (esto podría ser un servicio aparte)
            $alerta = $this->check_reincidencia($rude);

            $this->success([
                'id' => $novedad_id, 
                'message' => 'Novedad registrada con éxito.',
                'alerta' => $alerta
            ]);
        } catch (Exception $e) {
            $this->error('Error al registrar novedad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verifica si un estudiante tiene 3 faltas leves para emitir alerta
     */
    private function check_reincidencia($rude)
    {
        $this->db->where('rude', $rude);
        $this->db->where('gravedad', 'Leve');
        $count = $this->db->count_all_results('novedades');

        if ($count >= 3) {
            return [
                'tipo' => 'Alerta de Reincidencia',
                'mensaje' => "El estudiante ha acumulado {$count} faltas leves. Se ha notificado al Director.",
                'cantidad' => $count
            ];
        }
        return null;
    }

    /**
     * GET /api/novedades/(:num)
     * Muestra una novedad específica con sus indicadores
     */
    public function show($id): void
    {
        try {
            $novedad = $this->Novedad_model->get($id);
            if (!$novedad) {
                $this->error('Novedad no encontrada', 404);
                return;
            }
            $novedad['indicadores'] = $this->Novedad_model->get_indicadores($id);
            $this->success($novedad);
        } catch (Exception $e) {
            $this->error('Error al obtener novedad: ' . $e->getMessage(), 500);
        }
    }
    /**
     * GET /api/novedades/estudiante/(:any)
     * Muestra el historial paginado de novedades de un estudiante
     */
    public function estudiante($rude): void
    {
        try {
            $limit = (int) $this->input->get('limit') ?: 50;
            $offset = (int) $this->input->get('offset') ?: 0;
            $fecha_desde = $this->input->get('fecha_desde');
            $fecha_hasta = $this->input->get('fecha_hasta');
            
            $historial = $this->Novedad_model->get_by_student_paginated($rude, $limit, $offset, $fecha_desde, $fecha_hasta);
            $total = $this->Novedad_model->count_by_student($rude, $fecha_desde, $fecha_hasta);
            
            $this->success([
                'novedades' => $historial,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (Exception $e) {
            $this->error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }
    /**
     * DELETE /api/novedades/(:num)
     * Elimina una novedad
     */
    public function destroy($id): void
    {
        try {
            $this->Novedad_model->delete($id);
            $this->success(['message' => 'Novedad eliminada correctamente']);
        } catch (Exception $e) {
            $this->error('Error al eliminar novedad: ' . $e->getMessage(), 500);
        }
    }
}
