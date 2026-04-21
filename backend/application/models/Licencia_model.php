<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo: Licencia_model
 * Gestiona las licencias de ausencia y sus archivos adjuntos.
 */
class Licencia_model extends CI_Model
{
    private const TABLA          = 'licencias';
    private const TABLA_ARCHIVOS = 'archivos_adjuntos';

    /**
     * Lista licencias con filtros opcionales, incluyendo datos del estudiante.
     */
    public function listar(?string $rude, ?string $estado, ?string $fecha): array
    {
        $this->db->select('l.*, e.nombre_completo, e.ci');
        $this->db->from(self::TABLA . ' l');
        $this->db->join('estudiantes e', 'e.rude = l.rude', 'left');

        if ($rude) {
            $this->db->where('l.rude', $rude);
        }
        if ($estado) {
            $this->db->where('l.estado', $estado);
        }
        if ($fecha) {
            $this->db->where('l.fecha_inicio', $fecha);
        }

        $this->db->order_by('l.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Obtiene una licencia con sus archivos adjuntos.
     */
    public function obtenerConArchivos(int $id): ?array
    {
        $this->db->select('l.*, e.nombre_completo, e.ci, e.foto');
        $this->db->from(self::TABLA . ' l');
        $this->db->join('estudiantes e', 'e.rude = l.rude', 'left');
        $this->db->where('l.id', $id);

        $licencia = $this->db->get()->row_array();

        if (! $licencia) {
            return null;
        }

        // Cargar los archivos adjuntos de esta licencia
        $archivos   = $this->db->get_where(self::TABLA_ARCHIVOS, ['licencia_id' => $id])->result_array();
        $licencia['archivos'] = $archivos;

        return $licencia;
    }

    /**
     * Lista el historial de licencias de un estudiante, con archivos adjuntos.
     */
    public function porEstudiante(string $rude, $desde = null, $hasta = null): array
    {
        $this->db->where('rude', $rude);
        if ($desde) {
            $this->db->where('fecha_inicio >=', $desde);
        }
        if ($hasta) {
            $this->db->where('fecha_inicio <=', $hasta);
        }
        
        $licencias = $this->db
            ->order_by('fecha_inicio', 'DESC')
            ->order_by('id', 'DESC')
            ->get(self::TABLA)
            ->result_array();

        // Agregar archivos a cada licencia
        foreach ($licencias as &$licencia) {
            $licencia['archivos'] = $this->db
                ->get_where(self::TABLA_ARCHIVOS, ['licencia_id' => $licencia['id']])
                ->result_array();
        }

        return $licencias;
    }

    /**
     * Calcula la fecha de fin saltando fines de semana
     */
    private function calcularFechaFin($fecha_inicio, $dias_habiles)
    {
        $fecha = new DateTime($fecha_inicio);
        $count = 0;
        
        // El primer día hábil
        $dow = (int)$fecha->format('w');
        while ($dow == 0 || $dow == 6) {
            $fecha->modify('+1 day');
            $dow = (int)$fecha->format('w');
        }

        $count = 1;
        while ($count < $dias_habiles) {
            $fecha->modify('+1 day');
            $dow = (int)$fecha->format('w');
            if ($dow != 0 && $dow != 6) {
                $count++;
            }
        }
        return $fecha->format('Y-m-d');
    }

    /**
     * Crea una nueva licencia y devuelve su ID.
     */
    public function crear(array $data): int
    {
        $campos = ['rude', 'fecha_inicio', 'dias', 'motivo', 'solicitante', 'ci_solicitante', 'estado', 'observaciones', 'registrado_por'];
        $insert = array_intersect_key($data, array_flip($campos));
        
        // Calcular fecha_fin basada en dias hábiles
        if (isset($insert['fecha_inicio']) && isset($insert['dias'])) {
            $insert['fecha_fin'] = $this->calcularFechaFin($insert['fecha_inicio'], $insert['dias']);
        }

        $this->db->insert(self::TABLA, $insert);
        return $this->db->insert_id();
    }

    /**
     * Actualiza el estado u observaciones de una licencia.
     */
    public function actualizar(int $id, array $data): bool
    {
        $campos = ['estado', 'observaciones'];
        $update = array_intersect_key($data, array_flip($campos));
        $this->db->where('id', $id);
        return $this->db->update(self::TABLA, $update);
    }

    /**
     * Guarda un archivo adjunto y devuelve su ID.
     */
    public function guardarArchivo(array $data): int
    {
        $campos = ['licencia_id', 'nombre_original', 'nombre_archivo', 'tipo', 'tamanio', 'ruta'];
        $insert = array_intersect_key($data, array_flip($campos));
        $this->db->insert(self::TABLA_ARCHIVOS, $insert);
        return $this->db->insert_id();
    }

    /**
     * Elimina una licencia y sus archivos físicos asociados.
     */
    public function eliminar(int $id): bool
    {
        // 1. Buscar archivos para eliminarlos del disco
        $archivos = $this->db->get_where(self::TABLA_ARCHIVOS, ['licencia_id' => $id])->result_array();
        foreach ($archivos as $arc) {
            $path = FCPATH . $arc['ruta'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // 2. Eliminar registros (la DB debería tener ON DELETE CASCADE, pero lo hacemos manual por seguridad)
        $this->db->delete(self::TABLA_ARCHIVOS, ['licencia_id' => $id]);
        $this->db->where('id', $id);
        return $this->db->delete(self::TABLA);
    }
}
