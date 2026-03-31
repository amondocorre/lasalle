<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Estudiantes
 *
 * Maneja el CRUD completo de estudiantes y la subida de fotos.
 * Endpoint base: /api/estudiantes
 */
class Estudiantes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Estudiante_model');
        $this->load->library('upload');
        $this->load->helper(['form', 'url']);
    }

    /**
     * GET /api/estudiantes
     * Lista todos los estudiantes con filtros opcionales.
     */
    public function index(): void
    {
        // Parámetros de búsqueda opcionales
        $search  = $this->input->get('q');
        $cursoId = $this->input->get('curso_id');
        $page    = (int) ($this->input->get('page') ?? 1);
        $limit   = (int) ($this->input->get('limit') ?? 20);

        $result = $this->Estudiante_model->listar($search, $cursoId, $page, $limit);
        $this->success($result);
    }

    /**
     * GET /api/estudiantes/{rude}
     * Obtiene un estudiante por su RUDE.
     */
    public function show(string $rude): void
    {
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);

        if (! $estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $this->success($estudiante);
    }

    /**
     * POST /api/estudiantes
     * Crea un nuevo estudiante.
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        // Validar campos requeridos
        $errores = $this->validarEstudiante($data);
        if (! empty($errores)) {
            $this->error('Datos inválidos', 422, $errores);
            return;
        }

        // Verificar unicidad del RUDE
        if ($this->Estudiante_model->obtenerPorRude($data['rude'])) {
            $this->error('El RUDE ya está registrado', 409);
            return;
        }

        // Verificar unicidad del CI
        if ($this->Estudiante_model->existeCI($data['ci'])) {
            $this->error('El CI ya está registrado', 409);
            return;
        }

        $id = $this->Estudiante_model->crear($data);

        if ($id) {
            $nuevo = $this->Estudiante_model->obtenerPorRude($data['rude']);
            $this->success($nuevo, 'Estudiante registrado correctamente', 201);
        } else {
            $this->error('Error al registrar el estudiante', 500);
        }
    }

    /**
     * PUT /api/estudiantes/{rude}
     * Actualiza los datos de un estudiante.
     */
    public function update(string $rude): void
    {
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);
        if (! $estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $data = $this->getJsonBody();
        // No se permite cambiar el RUDE
        unset($data['rude']);

        $ok = $this->Estudiante_model->actualizar($rude, $data);

        if ($ok) {
            $actualizado = $this->Estudiante_model->obtenerPorRude($rude);
            $this->success($actualizado, 'Estudiante actualizado correctamente');
        } else {
            $this->error('Error al actualizar el estudiante', 500);
        }
    }

    /**
     * DELETE /api/estudiantes/{rude}
     * Desactiva (soft delete) a un estudiante.
     */
    public function destroy(string $rude): void
    {
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);
        if (! $estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $ok = $this->Estudiante_model->desactivar($rude);

        if ($ok) {
            $this->success(null, 'Estudiante desactivado correctamente');
        } else {
            $this->error('Error al desactivar el estudiante', 500);
        }
    }

    /**
     * POST /api/estudiantes/{rude}/foto
     * Sube la foto de perfil del estudiante.
     */
    public function uploadFoto(string $rude): void
    {
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);
        if (! $estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $uploadPath = FCPATH . 'uploads/fotos/';

        // Crear directorio si no existe
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size'      => 2048, // 2MB
            'file_name'     => 'est_' . $rude,
            'overwrite'     => true,
        ];

        $this->upload->initialize($config);

        if (! $this->upload->do_upload('foto')) {
            $this->error($this->upload->display_errors('', ''), 400);
            return;
        }

        $uploadData = $this->upload->data();
        $rutaFoto   = 'uploads/fotos/' . $uploadData['file_name'];

        $this->Estudiante_model->actualizar($rude, ['foto' => $rutaFoto]);
        $this->success(['foto' => $rutaFoto], 'Foto subida correctamente');
    }

    /**
     * Valida los campos del formulario de estudiante.
     *
     * @return array Lista de errores (vacío si no hay)
     */
    private function validarEstudiante(array $data): array
    {
        $errores = [];

        if (empty($data['rude']) || strlen($data['rude']) > 20) {
            $errores['rude'] = 'El RUDE es obligatorio (máx. 20 caracteres)';
        }

        if (empty($data['ci']) || strlen($data['ci']) > 15) {
            $errores['ci'] = 'El CI es obligatorio (máx. 15 caracteres)';
        }

        if (empty($data['nombre_completo'])) {
            $errores['nombre_completo'] = 'El nombre completo es obligatorio';
        }

        if (empty($data['fecha_nac'])) {
            $errores['fecha_nac'] = 'La fecha de nacimiento es obligatoria';
        } elseif (! strtotime($data['fecha_nac'])) {
            $errores['fecha_nac'] = 'Formato de fecha inválido (YYYY-MM-DD)';
        }

        if (empty($data['sexo']) || ! in_array($data['sexo'], ['M', 'F'])) {
            $errores['sexo'] = 'El sexo debe ser M o F';
        }

        return $errores;
    }
}
