<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reporte_model extends CI_Model
{
    public function get_monitor_rendimiento($gestion = null, $mes = null, $tipo = null)
    {
        $this->db->select("c.id as curso_id, CONCAT(c.nombre, ' ', c.paralelo) as curso_nombre");
        $this->db->select("COALESCE(SUM(CASE WHEN ni.tipo = 'académico' THEN 1 ELSE 0 END), 0) as total_academicas", FALSE);
        $this->db->select("COALESCE(SUM(CASE WHEN ni.tipo = 'conductual' THEN 1 ELSE 0 END), 0) as total_conductuales", FALSE);
        $this->db->select("COUNT(ni.id) as total_novedades", FALSE);
        $this->db->from('cursos c');
        $this->db->join('estudiante_curso ec', 'ec.curso_id = c.id', 'left');
        
        $join_novedades = "n.rude = ec.rude";
        if (!empty($mes) && $mes > 0) {
            $join_novedades .= " AND MONTH(n.created_at) = " . (int)$mes;
            if (!empty($gestion)) {
                  $join_novedades .= " AND YEAR(n.created_at) = " . (int)$gestion;
            }
        }
        $this->db->join('novedades n', $join_novedades, 'left');
        
        $join_indicadores = "ni.novedad_id = n.id";
        if (!empty($tipo)) {
            $join_indicadores .= " AND ni.tipo = " . $this->db->escape($tipo);
        }
        $this->db->join('novedad_indicadores ni', $join_indicadores, 'left');
        
        if (!empty($gestion)) {
            $this->db->where('c.gestion', $gestion);
        }
        
        $this->db->group_by('c.id');
        $this->db->order_by('c.nivel', 'ASC');
        $this->db->order_by('c.nombre', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_detalle_curso($curso_id, $gestion = null, $mes = null, $tipo = null)
    {
        $this->db->select("e.rude, e.nombre_completo as nombre_estudiante");
        $this->db->select("MAX(n.created_at) as ultima_novedad");
        $this->db->select("COALESCE(SUM(CASE WHEN ni.tipo = 'académico' THEN 1 ELSE 0 END), 0) as total_academicas", FALSE);
        $this->db->select("COALESCE(SUM(CASE WHEN ni.tipo = 'conductual' THEN 1 ELSE 0 END), 0) as total_conductuales", FALSE);
        $this->db->select("COUNT(ni.id) as total_novedades", FALSE);
        $this->db->from('estudiantes e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude');
        $this->db->where('ec.curso_id', $curso_id);
        
        $join_novedades = "n.rude = e.rude";
        if (!empty($mes) && $mes > 0) {
            $join_novedades .= " AND MONTH(n.created_at) = " . (int)$mes;
            if (!empty($gestion)) {
                  $join_novedades .= " AND YEAR(n.created_at) = " . (int)$gestion;
            }
        }
        $this->db->join('novedades n', $join_novedades, 'left');
        
        $join_indicadores = "ni.novedad_id = n.id";
        if (!empty($tipo)) {
            $join_indicadores .= " AND ni.tipo = " . $this->db->escape($tipo);
        }
        $this->db->join('novedad_indicadores ni', $join_indicadores, 'left');
        
        $this->db->group_by('e.rude');
        $this->db->order_by('total_novedades', 'DESC');
        $this->db->order_by('e.nombre_completo', 'ASC');
        
        return $this->db->get()->result_array();
    }

    public function get_cursos_con_novedades_stats($limit = 12)
    {
        $this->db->select("CONCAT(c.nombre, ' ', c.paralelo) as curso_nombre");
        $this->db->select("COUNT(DISTINCT n.rude) as cant_estudiantes");
        $this->db->from('cursos c');
        $this->db->join('estudiante_curso ec', 'ec.curso_id = c.id');
        $this->db->join('novedades n', 'n.rude = ec.rude');
        $this->db->group_by('c.id');
        $this->db->order_by('cant_estudiantes', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_licencias_activas_hoy($fecha = null)
    {
        $fecha = $fecha ?: date('Y-m-d');
        $this->db->select("l.*, e.nombre_completo as nombre_estudiante, c.nombre as nombre_curso, c.paralelo as paralelo_curso");
        $this->db->select("(SELECT ruta FROM archivos_adjuntos WHERE licencia_id = l.id LIMIT 1) as adjunto_ruta", FALSE);
        $this->db->select("(SELECT tipo FROM archivos_adjuntos WHERE licencia_id = l.id LIMIT 1) as adjunto_tipo", FALSE);
        $this->db->from('licencias l');
        $this->db->join('estudiantes e', 'l.rude = e.rude');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude');
        $this->db->join('cursos c', 'ec.curso_id = c.id');
        $this->db->where('l.estado', 'aprobada');
        $this->db->where($this->db->escape($fecha) . ' >= l.fecha_inicio', null, false);
        $this->db->where($this->db->escape($fecha) . ' < DATE_ADD(l.fecha_inicio, INTERVAL l.dias DAY)', null, false);
        $this->db->order_by('c.id', 'ASC');
        $this->db->order_by('e.nombre_completo', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_retrasos_stats($gestion = null, $mes = null)
    {
        $this->db->select("c.id as curso_id, CONCAT(c.nombre, ' ', c.paralelo) as curso_nombre");
        $this->db->select("COUNT(r.id) as total_retrasos", FALSE);
        $this->db->select("COUNT(DISTINCT r.rude) as total_estudiantes", FALSE);
        $this->db->from('cursos c');
        $this->db->join('estudiante_curso ec', 'ec.curso_id = c.id', 'left');
        
        $join_asis = "r.rude = ec.rude";
        if (!empty($mes) && $mes > 0) {
            $join_asis .= " AND MONTH(r.fecha) = " . (int)$mes;
            if (!empty($gestion)) {
                  $join_asis .= " AND YEAR(r.fecha) = " . (int)$gestion;
            }
        }
        $this->db->join('retrasos r', $join_asis, 'left');
        
        if (!empty($gestion)) {
            $this->db->where('c.gestion', $gestion);
        }
        
        $this->db->group_by('c.id');
        $this->db->order_by('c.nivel', 'ASC');
        $this->db->order_by('c.nombre', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_detalle_retrasos_curso($curso_id, $gestion = null, $mes = null)
    {
        $this->db->select("e.rude, e.nombre_completo as nombre_estudiante");
        $this->db->select("MAX(r.fecha) as ultimo_retraso");
        $this->db->select("COUNT(r.id) as total_retrasos", FALSE);
        $this->db->from('estudiantes e');
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude');
        $this->db->where('ec.curso_id', $curso_id);
        
        $join_asis = "r.rude = e.rude";
        if (!empty($mes) && $mes > 0) {
            $join_asis .= " AND MONTH(r.fecha) = " . (int)$mes;
            if (!empty($gestion)) {
                  $join_asis .= " AND YEAR(r.fecha) = " . (int)$gestion;
            }
        }
        $this->db->join('retrasos r', $join_asis, 'left');
        
        $this->db->group_by('e.rude');
        $this->db->order_by('total_retrasos', 'DESC');
        $this->db->order_by('e.nombre_completo', 'ASC');
        
        return $this->db->get()->result_array();
    }

    public function get_acceso_padres_stats($gestion = null)
    {
        $this->db->select("e.ci as ci_estudiante, e.nombre_completo as nombre_estudiante, COALESCE(CONCAT(c.nombre, ' ', c.paralelo), 'Sin Curso') as curso_nombre");
        $this->db->select("COUNT(l.id) as total_accesos", FALSE);
        $this->db->select("MAX(l.fecha_acceso) as ultimo_acceso");
        $this->db->from('estudiantes e');
        $this->db->join('log_acceso_padres l', 'l.ci_estudiante = e.ci', 'left');
        
        $this->db->join('estudiante_curso ec', 'ec.rude = e.rude', 'left');
        
        $join_curso = 'c.id = ec.curso_id';
        if (!empty($gestion)) {
            $join_curso .= ' AND c.gestion = ' . $this->db->escape($gestion);
        }
        $this->db->join('cursos c', $join_curso, 'left');

        $this->db->group_by(['e.ci', 'e.nombre_completo', 'c.nombre', 'c.paralelo']);
        $this->db->order_by('total_accesos', 'DESC');
        $this->db->order_by('e.nombre_completo', 'ASC');
        
        return $this->db->get()->result_array();
    }
}
