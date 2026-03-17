<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador: Asistencias
 *
 * Maneja el registro de asistencias, especialmente
 * el endpoint de escaneo de código de barras (RUDE).
 * Endpoint base: /api/asistencias
 */
class Asistencias extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Asistencia_model', 'Estudiante_model', 'Horario_model']);
    }

    /**
     * GET /api/asistencias
     * Lista asistencias con filtros: rude, curso_id, fecha.
     */
    public function index(): void
    {
        $rude    = trim($this->input->get('rude') ?? '');
        $cursoId = $this->input->get('curso_id');
        $fecha   = $this->input->get('fecha') ?? date('Y-m-d');

        $asistencias = $this->Asistencia_model->listar($rude, $cursoId, $fecha);
        $this->success($asistencias);
    }

    /**
     * POST /api/asistencias/escanear
     *
     * ENDPOINT PRINCIPAL DE ESCANEO DE CÓDIGO DE BARRAS.
     *
     * Recibe el RUDE escaneado, verifica el horario activo en ese momento,
     * y registra la asistencia automáticamente.
     *
     * Body esperado:
     * {
     *   "rude": "2024001001",
     *   "registrado_por": "Lic. García" (opcional)
     * }
     */
    public function escanear(): void
    {
        $data = $this->getJsonBody();

        // Validar que se proporcionó el RUDE
        if (empty($data['rude'])) {
            $this->error('RUDE es requerido para registrar la asistencia', 400);
            return;
        }

        $rude = trim($data['rude']);

        // Buscar al estudiante por su RUDE
        $estudiante = $this->Estudiante_model->obtenerPorRude($rude);

        if (! $estudiante) {
            $this->error("No se encontró ningún estudiante con RUDE: {$rude}", 404);
            return;
        }

        if (! $estudiante['activo']) {
            $this->error('El estudiante está inactivo en el sistema', 403);
            return;
        }

        // Obtener fecha y hora actuales del servidor
        $fechaHoy  = date('Y-m-d');
        $horaActual = date('H:i:s');
        $diaActual  = $this->obtenerDiaEspanol(date('N')); // 1=lunes..7=domingo

        // Buscar el horario correspondiente al momento actual para el curso del estudiante
        // O usar el horario_id si fue enviado manualmente
        if (!empty($data['horario_id'])) {
            $horarioActivo = $this->Horario_model->obtener($data['horario_id']);
        } else {
            $horarioActivo = $this->Horario_model->obtenerHorarioActivo(
                $estudiante['curso_id'],
                $diaActual,
                $horaActual
            );
        }

        // Verificar si ya existe una asistencia para este período hoy
        $yaRegistrado = $this->Asistencia_model->existeRegistro(
            $rude,
            $horarioActivo ? $horarioActivo['id'] : null,
            $fechaHoy
        );

        if ($yaRegistrado) {
            // Devolver la info aunque ya esté registrado (para feedback visual)
            $this->jsonResponse([
                'success'     => true,
                'message'     => 'La asistencia ya fue registrada anteriormente hoy',
                'ya_registrado' => true,
                'estudiante'  => $estudiante,
                'horario'     => $horarioActivo,
                'fecha'       => $fechaHoy,
                'hora'        => $horaActual,
            ], 200);
            return;
        }

        // Registrar la nueva asistencia
        $registroData = [
            'rude'          => $rude,
            'curso_id'      => $estudiante['curso_id'],
            'horario_id'    => $horarioActivo ? $horarioActivo['id'] : null,
            'fecha'         => $fechaHoy,
            'hora_registro' => $horaActual,
            'estado'        => 'presente',
            'registrado_por' => $data['registrado_por'] ?? 'Sistema',
        ];

        $id = $this->Asistencia_model->registrar($registroData);

        if ($id) {
            $this->jsonResponse([
                'success'    => true,
                'message'    => '¡Asistencia registrada correctamente!',
                'ya_registrado' => false,
                'estudiante' => $estudiante,
                'horario'    => $horarioActivo,
                'fecha'      => $fechaHoy,
                'hora'       => $horaActual,
            ], 201);
        } else {
            $this->error('Error al registrar la asistencia en la base de datos', 500);
        }
    }

    /**
     * GET /api/asistencias/reporte
     * Reporte de asistencias por curso y rango de fechas.
     */
    public function reporte(): void
    {
        $cursoId    = $this->input->get('curso_id');
        $fechaInicio = $this->input->get('fecha_inicio') ?? date('Y-m-01');
        $fechaFin   = $this->input->get('fecha_fin')    ?? date('Y-m-d');

        $reporte = $this->Asistencia_model->reporte($cursoId, $fechaInicio, $fechaFin);
        $this->success($reporte);
    }

    /**
     * Convierte el número ISO de día a nombre en español.
     * ISO: 1=Lunes, 2=Martes ... 7=Domingo
     */
    private function obtenerDiaEspanol(int $isoDay): string
    {
        $dias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo',
        ];
        return $dias[$isoDay] ?? 'lunes';
    }
}
