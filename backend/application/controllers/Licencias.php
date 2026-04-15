<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Licencias
 *
 * Gestiona las licencias de ausencia solicitadas por padres de familia.
 * Incluye registro, búsqueda y subida de archivos adjuntos.
 * Endpoint base: /api/licencias
 */
class Licencias extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Licencia_model', 'Estudiante_model']);
        $this->load->library(['upload']);
    }

    /**
     * GET /api/licencias
     * Lista licencias con filtros: estado, fecha.
     */
    public function index(): void
    {
        $estado = $this->input->get('estado');
        $fecha  = $this->input->get('fecha');
        $rude   = $this->input->get('rude');

        $licencias = $this->Licencia_model->listar($rude, $estado, $fecha);
        $this->success($licencias);
    }

    /**
     * GET /api/licencias/{id}
     * Obtiene una licencia con sus archivos adjuntos.
     */
    public function show(int $id): void
    {
        $licencia = $this->Licencia_model->obtenerConArchivos($id);
        $licencia
            ? $this->success($licencia)
            : $this->error('Licencia no encontrada', 404);
    }

    /**
     * GET /api/licencias/estudiante/{rude}
     * Lista el historial de licencias de un estudiante específico.
     */
    public function porEstudiante($rude): void
    {
        $rude = trim($rude);
        // Verificar que el estudiante existe
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);
        if (! $estudiante) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        $fecha_desde = $this->input->get('fecha_desde');
        $fecha_hasta = $this->input->get('fecha_hasta');

        $licencias = $this->Licencia_model->porEstudiante($rude, $fecha_desde, $fecha_hasta);

        $this->success([
            'estudiante' => $estudiante,
            'licencias'  => $licencias,
            'total'      => count($licencias),
        ]);
    }

    /**
     * POST /api/licencias
     * Registra una nueva licencia de ausencia.
     */
    public function store(): void
    {
        $data = $this->getJsonBody();

        // Validar campos requeridos
        $errores = $this->validarLicencia($data);
        if (! empty($errores)) {
            $this->error('Datos inválidos', 422, $errores);
            return;
        }

        // Verificar que el estudiante existe
        if (! $this->Estudiante_model->obtenerPorRude($data['rude'])) {
            $this->error('Estudiante no encontrado', 404);
            return;
        }

        // Valores por defecto
        $data['dias']   = (int) ($data['dias'] ?? 1);
        $data['estado'] = 'aprobada'; // Por requerimiento, todas se registran aprobadas
        
        // Capturar usuario que registra
        $data['registrado_por'] = $data['registrado_por'] ?? 'Sistema';

        $id = $this->Licencia_model->crear($data);

        if ($id) {
            $nueva = $this->Licencia_model->obtenerConArchivos($id);
            $this->success($nueva, 'Licencia registrada correctamente', 201);
        } else {
            $this->error('Error al registrar la licencia', 500);
        }
    }

    /**
     * PUT /api/licencias/{id}
     * Actualiza el estado de una licencia (aprobar/rechazar).
     */
    public function update(int $id): void
    {
        $licencia = $this->Licencia_model->obtenerConArchivos($id);
        if (! $licencia) {
            $this->error('Licencia no encontrada', 404);
            return;
        }

        $data = $this->getJsonBody();

        // Solo se puede cambiar estado y observaciones por este endpoint
        $updateData = [];
        if (isset($data['estado']) && in_array($data['estado'], ['pendiente', 'aprobada', 'rechazada'])) {
            $updateData['estado'] = $data['estado'];
        }
        if (isset($data['observaciones'])) {
            $updateData['observaciones'] = $data['observaciones'];
        }

        if (empty($updateData)) {
            $this->error('No se proporcionaron datos válidos para actualizar', 400);
            return;
        }

        $ok = $this->Licencia_model->actualizar($id, $updateData);
        $ok
            ? $this->success($this->Licencia_model->obtenerConArchivos($id), 'Licencia actualizada')
            : $this->error('Error al actualizar la licencia', 500);
    }

    /**
     * POST /api/licencias/{id}/upload
     * Sube un archivo adjunto (PDF, JPG) a una licencia.
     */
    public function uploadArchivo(int $id): void
    {
        $licencia = $this->Licencia_model->obtenerConArchivos($id);
        if (! $licencia) {
            $this->error('Licencia no encontrada', 404);
            return;
        }

        $uploadPath = FCPATH . 'uploads/licencias/';

        // Crear directorio si no existe
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Nombre único para el archivo basado en licencia_id + timestamp
        $nombreArchivo = 'lic_' . $id . '_' . time();

        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size'      => 5120, // 5MB
            'file_name'     => $nombreArchivo,
            'overwrite'     => false,
        ];

        $this->upload->initialize($config);

        if (! $this->upload->do_upload('archivo')) {
            $this->error($this->upload->display_errors('', ''), 400);
            return;
        }

        $uploadData = $this->upload->data();

        // Determinar el tipo del archivo
        $extension = strtolower($uploadData['file_ext']);
        $tipo      = ltrim($extension, '.');

        // Guardar referencia en la base de datos
        $archivoData = [
            'licencia_id'     => $id,
            'nombre_original' => $uploadData['orig_name'],
            'nombre_archivo'  => $uploadData['file_name'],
            'tipo'            => $tipo,
            'tamanio'         => $uploadData['file_size'] * 1024,
            'ruta'            => 'uploads/licencias/' . $uploadData['file_name'],
        ];

        $archivoId = $this->Licencia_model->guardarArchivo($archivoData);

        if ($archivoId) {
            $this->success($archivoData, 'Archivo subido correctamente', 201);
        } else {
            $this->error('Error al guardar el archivo en la base de datos', 500);
        }
    }

    /** Valida los campos de la licencia. */
    private function validarLicencia(array $data): array
    {
        $errores = [];

        if (empty($data['rude'])) {
            $errores['rude'] = 'El RUDE del estudiante es obligatorio';
        }
        if (empty($data['fecha_inicio'])) {
            $errores['fecha_inicio'] = 'La fecha de inicio es obligatoria';
        }
        if (isset($data['dias']) && ((int) $data['dias'] < 1 || (int) $data['dias'] > 365)) {
            $errores['dias'] = 'Los días deben estar entre 1 y 365';
        }
        if (empty($data['motivo'])) {
            $errores['motivo'] = 'El motivo de la licencia es obligatorio';
        }

        return $errores;
    }
}
