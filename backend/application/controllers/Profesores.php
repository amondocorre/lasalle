<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Controlador: Profesores
 * Gestión de personal docente.
 */
class Profesores extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Profesor_model');
    }

    /**
     * GET /api/profesores
     */
    public function index(): void
    {
        $lista = $this->Profesor_model->listar();
        $this->success($lista);
    }

    /**
     * GET /api/profesores/(:num)
     */
    public function show(int $id): void
    {
        $profesor = $this->Profesor_model->obtener_por_id($id);
        $profesor 
            ? $this->success($profesor)
            : $this->error('Profesor no encontrado', 404);
    }

    /**
     * GET /api/profesores/(:num)/materias
     */
    public function materias(int $id): void
    {
        $this->db->select('m.id, m.nombre');
        $this->db->from('profesor_materia pm');
        $this->db->join('materias m', 'm.id = pm.materia_id');
        $this->db->where('pm.profesor_id', $id);
        $this->db->group_by('m.id');
        $materias = $this->db->get()->result();
        
        $this->success($materias);
    }
    /**
     * GET /api/profesores/materia/(:num)
     */
    public function por_materia(int $id): void
    {
        $this->db->select('p.id, p.nombre');
        $this->db->from('profesores p');
        $this->db->join('profesor_materia pm', 'pm.profesor_id = p.id');
        $this->db->where('pm.materia_id', $id);
        $this->db->where('p.activo', 1);
        $this->db->order_by('p.nombre', 'ASC');
        $profesores = $this->db->get()->result();
        
        $this->success($profesores);
    }

    /**
     * POST /api/profesores
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        if (empty($data['nombre'])) {
            $this->error('El nombre es obligatorio', 400);
            return;
        }

        $id = $this->Profesor_model->crear($data);
        if ($id) {
            $nuevo = $this->Profesor_model->obtener_por_id($id);
            $this->success($nuevo, 'Profesor registrado correctamente', 201);
        } else {
            $this->error('No se pudo registrar al profesor', 500);
        }
    }

    /**
     * PUT /api/profesores/(:num)
     */
    public function update(int $id): void
    {
        $data = $this->getJsonBody();
        
        if ($this->Profesor_model->actualizar($id, $data)) {
            $actualizado = $this->Profesor_model->obtener_por_id($id);
            $this->success($actualizado, 'Datos actualizados correctamente');
        } else {
            $this->error('Error al actualizar los datos', 500);
        }
    }

    /**
     * DELETE /api/profesores/(:num)
     * En este sistema "eliminar" suele ser dar de baja (activo=0)
     */
    public function destroy(int $id): void
    {
        if ($this->Profesor_model->eliminar($id)) {
            $this->success(null, 'Profesor dado de baja correctamente');
        } else {
            $this->error('No se pudo procesar la baja', 500);
        }
    }
}
