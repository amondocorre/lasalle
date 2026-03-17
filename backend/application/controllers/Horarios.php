<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Horarios
 *
 * Gestiona los horarios de materias por curso.
 * Endpoint base: /api/horarios
 */
class Horarios extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Horario_model');
    }

    /**
     * GET /api/horarios
     * Lista horarios, con filtro por curso_id y/o día.
     */
    public function index(): void
    {
        $cursoId = $this->input->get('curso_id');
        $dia     = $this->input->get('dia');

        $horarios = $this->Horario_model->listar($cursoId, $dia);
        $this->success($horarios);
    }

    /**
     * GET /api/horarios/{id}
     */
    public function show(int $id): void
    {
        $horario = $this->Horario_model->obtener($id);
        $horario
            ? $this->success($horario)
            : $this->error('Horario no encontrado', 404);
    }

    /**
     * POST /api/horarios
     * Crea un horario para un curso específico.
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        $errores = $this->validarHorario($data);
        if (! empty($errores)) {
            $this->error('Datos inválidos', 422, $errores);
            return;
        }

        $id = $this->Horario_model->crear($data);

        if ($id) {
            $this->success($this->Horario_model->obtener($id), 'Horario creado', 201);
        } else {
            $this->error('Error al crear el horario', 500);
        }
    }

    /**
     * PUT /api/horarios/{id}
     */
    public function update(int $id): void
    {
        if (! $this->Horario_model->obtener($id)) {
            $this->error('Horario no encontrado', 404);
            return;
        }

        $data = $this->getJsonBody();
        $ok   = $this->Horario_model->actualizar($id, $data);

        $ok
            ? $this->success($this->Horario_model->obtener($id), 'Horario actualizado')
            : $this->error('Error al actualizar', 500);
    }

    /**
     * DELETE /api/horarios/{id}
     */
    public function destroy(int $id): void
    {
        if (! $this->Horario_model->obtener($id)) {
            $this->error('Horario no encontrado', 404);
            return;
        }

        $ok = $this->Horario_model->eliminar($id);
        $ok
            ? $this->success(null, 'Horario eliminado')
            : $this->error('Error al eliminar', 500);
    }

    /** Valida los campos del horario. */
    private function validarHorario(array $data): array
    {
        $errores = [];
        $diasValidos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

        if (empty($data['curso_id'])) {
            $errores['curso_id'] = 'El curso es obligatorio';
        }
        if (empty($data['materia'])) {
            $errores['materia'] = 'La materia es obligatoria';
        }
        if (empty($data['dia']) || ! in_array($data['dia'], $diasValidos)) {
            $errores['dia'] = 'Día inválido';
        }
        if (empty($data['hora_inicio'])) {
            $errores['hora_inicio'] = 'La hora de inicio es obligatoria';
        }
        if (empty($data['hora_fin'])) {
            $errores['hora_fin'] = 'La hora de fin es obligatoria';
        }
        return $errores;
    }
}
