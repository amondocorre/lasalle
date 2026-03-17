<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Materias
 * Gestiona el catálogo de materias del colegio.
 */
class Materias extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /api/materias
     * Lista todas las materias.
     */
    public function index(): void
    {
        $materias = $this->db->get('materias')->result();
        $this->success($materias);
    }

    /**
     * POST /api/materias
     * Crea una nueva materia.
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        if (empty($data['nombre'])) {
            $this->error('El nombre de la materia es obligatorio', 422);
            return;
        }

        // Verificar si ya existe
        $existe = $this->db->get_where('materias', ['nombre' => $data['nombre']])->row();
        if ($existe) {
            $this->error('Esta materia ya está registrada', 400);
            return;
        }

        $id = $this->db->insert('materias', ['nombre' => $data['nombre']]);

        if ($id) {
            $nuevoId = $this->db->insert_id();
            $nueva = $this->db->get_where('materias', ['id' => $nuevoId])->row();
            $this->success($nueva, 'Materia creada', 201);
        } else {
            $this->error('Error al crear la materia', 500);
        }
    }

    /**
     * PUT /api/materias/{id}
     * Actualiza una materia.
     */
    public function update(int $id): void
    {
        $data = $this->getJsonBody();

        if (empty($data['nombre'])) {
            $this->error('El nombre de la materia es obligatorio', 422);
            return;
        }

        $this->db->where('id', $id);
        $ok = $this->db->update('materias', ['nombre' => $data['nombre']]);

        if ($ok) {
            $actualizada = $this->db->get_where('materias', ['id' => $id])->row();
            $this->success($actualizada, 'Materia actualizada');
        } else {
            $this->error('Error al actualizar la materia', 500);
        }
    }

    /**
     * DELETE /api/materias/{id}
     * Elimina una materia.
     */
    public function destroy(int $id): void
    {
        $this->db->where('id', $id);
        $ok = $this->db->delete('materias');
        
        $ok 
            ? $this->success(null, 'Materia eliminada')
            : $this->error('Error al eliminar materia', 500);
    }
}
