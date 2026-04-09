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
     * POST /api/novedades
     * Registra una nueva novedad
     */
    public function store(): void
    {
        $input = $this->getJsonBody();

        $rude = $input['rude'] ?? null;
        $materia_id = $input['materia_id'] ?? null;
        $comentario_docente = $input['comentario_docente'] ?? null;
        $usuario_id = $input['usuario_id'] ?? 1; // Default to admin or fetch from token if implemented
        $indicadores = $input['indicadores'] ?? [];

        if (!$rude || !$materia_id) {
            $this->error('El estudiante y la materia son obligatorios.', 400);
            return;
        }

        try {
            $novedad_id = $this->Novedad_model->insert([
                'rude' => $rude,
                'materia_id' => $materia_id,
                'usuario_id' => $usuario_id,
                'comentario_docente' => $comentario_docente,
            ]);

            // Insertar indicadores
            if ($novedad_id && !empty($indicadores)) {
                $this->Novedad_model->insert_indicadores($novedad_id, $indicadores);
            }

            $this->success(['id' => $novedad_id, 'message' => 'Novedad registrada con éxito.']);
        } catch (Exception $e) {
            $this->error('Error al registrar novedad: ' . $e->getMessage(), 500);
        }
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
            $limit = (int) $this->input->get('limit') ?: 10;
            $offset = (int) $this->input->get('offset') ?: 0;
            
            $historial = $this->Novedad_model->get_by_student_paginated($rude, $limit, $offset);
            $total = $this->Novedad_model->count_by_student($rude);
            
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
