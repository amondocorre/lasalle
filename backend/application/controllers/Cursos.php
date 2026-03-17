<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Cursos
 *
 * Gestiona los cursos del colegio y la asignación de estudiantes.
 * Endpoint base: /api/cursos
 */
class Cursos extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Curso_model');
    }

    /**
     * GET /api/cursos
     * Lista todos los cursos, con filtro opcional por gestión y turno.
     */
    public function index(): void
    {
        $gestion = $this->input->get('gestion') ?? date('Y');
        $turno   = $this->input->get('turno');

        $cursos = $this->Curso_model->listar($gestion, $turno);
        $this->success($cursos);
    }

    /**
     * GET /api/cursos/{id}
     * Obtiene un curso por ID.
     */
    public function show(int $id): void
    {
        $curso = $this->Curso_model->obtener($id);
        if (! $curso) {
            $this->error('Curso no encontrado', 404);
            return;
        }
        $this->success($curso);
    }

    /**
     * POST /api/cursos
     * Crea un nuevo curso.
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        $errores = $this->validarCurso($data);
        if (! empty($errores)) {
            $this->error('Datos inválidos', 422, $errores);
            return;
        }

        $id = $this->Curso_model->crear($data);

        if ($id) {
            $nuevo = $this->Curso_model->obtener($id);
            $this->success($nuevo, 'Curso creado correctamente', 201);
        } else {
            $this->error('Error al crear el curso', 500);
        }
    }

    /**
     * PUT /api/cursos/{id}
     * Actualiza un curso.
     */
    public function update(int $id): void
    {
        if (! $this->Curso_model->obtener($id)) {
            $this->error('Curso no encontrado', 404);
            return;
        }

        $data = $this->getJsonBody();
        $ok   = $this->Curso_model->actualizar($id, $data);

        if ($ok) {
            $this->success($this->Curso_model->obtener($id), 'Curso actualizado');
        } else {
            $this->error('Error al actualizar el curso', 500);
        }
    }

    /**
     * DELETE /api/cursos/{id}
     * Elimina un curso (soft delete).
     */
    public function destroy(int $id): void
    {
        if (! $this->Curso_model->obtener($id)) {
            $this->error('Curso no encontrado', 404);
            return;
        }

        $ok = $this->Curso_model->eliminar($id);
        $ok
            ? $this->success(null, 'Curso eliminado')
            : $this->error('Error al eliminar el curso', 500);
    }

    /**
     * GET /api/cursos/{id}/estudiantes
     * Lista los estudiantes inscritos en un curso.
     */
    public function estudiantes(int $id): void
    {
        if (! $this->Curso_model->obtener($id)) {
            $this->error('Curso no encontrado', 404);
            return;
        }

        $estudiantes = $this->Curso_model->listarEstudiantes($id);
        $this->success($estudiantes);
    }

    /** Valida los campos del curso. */
    private function validarCurso(array $data): array
    {
        $errores = [];
        if (empty($data['nombre'])) {
            $errores['nombre'] = 'El nombre del curso es obligatorio';
        }
        if (empty($data['nivel']) || ! in_array($data['nivel'], ['primaria', 'secundaria'])) {
            $errores['nivel'] = 'El nivel debe ser primaria o secundaria';
        }
        if (empty($data['turno']) || ! in_array($data['turno'], ['mañana', 'tarde', 'noche'])) {
            $errores['turno'] = 'Turno inválido';
        }
        if (empty($data['paralelo'])) {
            $errores['paralelo'] = 'El paralelo es obligatorio (A, B, C...)';
        }
        if (empty($data['gestion'])) {
            $errores['gestion'] = 'La gestión (año) es obligatoria';
        }
        return $errores;
    }
}
