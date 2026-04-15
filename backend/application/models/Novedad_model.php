<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Novedad_model extends CI_Model
{
    private $table = 'novedades';
    private $table_indicadores = 'novedad_indicadores';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all()
    {
        $this->db->select('n.*, e.nombre_completo as nombre_estudiante, m.nombre as nombre_materia, u.nombre as nombre_usuario');
        $this->db->from($this->table . ' n');
        $this->db->join('estudiantes e', 'e.rude = n.rude');
        $this->db->join('materias m', 'm.id = n.materia_id', 'left');
        $this->db->join('usuarios u', 'u.id = n.usuario_id', 'left');
        $this->db->order_by('n.created_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get($id)
    {
        $this->db->select('n.*, e.nombre_completo as nombre_estudiante, m.nombre as nombre_materia, u.nombre as nombre_usuario');
        $this->db->from($this->table . ' n');
        $this->db->join('estudiantes e', 'e.rude = n.rude');
        $this->db->join('materias m', 'm.id = n.materia_id', 'left');
        $this->db->join('usuarios u', 'u.id = n.usuario_id', 'left');
        $this->db->where('n.id', $id);
        return $this->db->get()->row_array();
    }

    public function get_by_student_paginated($rude, $limit = 50, $offset = 0, $desde = null, $hasta = null)
    {
        $this->db->select('n.*, m.nombre as nombre_materia, u.nombre as nombre_profesor');
        $this->db->from($this->table . ' n');
        $this->db->join('materias m', 'm.id = n.materia_id', 'left');
        $this->db->join('usuarios u', 'u.id = n.usuario_id', 'left');
        $this->db->where('n.rude', $rude);

        if ($desde) {
            $this->db->where('DATE(n.created_at) >=', $desde);
        }
        if ($hasta) {
            $this->db->where('DATE(n.created_at) <=', $hasta);
        }
        $this->db->order_by('n.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $res = $query->result_array();
        
        if (!empty($res)) {
            $ids = array_column($res, 'id');
            $this->db->where_in('novedad_id', $ids);
            $inds = $this->db->get($this->table_indicadores)->result_array();
            
            $inds_grouped = [];
            foreach ($inds as $ind) {
                $inds_grouped[$ind['novedad_id']][] = $ind;
            }
            
            foreach ($res as &$r) {
                $r['indicadores'] = $inds_grouped[$r['id']] ?? [];
            }
        }
        
        return $res;
    }

    public function count_by_student($rude, $desde = null, $hasta = null)
    {
        $this->db->where('rude', $rude);
        if ($desde) {
            $this->db->where('DATE(created_at) >=', $desde);
        }
        if ($hasta) {
            $this->db->where('DATE(created_at) <=', $hasta);
        }
        return $this->db->count_all_results($this->table);
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function insert_indicadores($novedad_id, $indicadores)
    {
        $data = [];
        foreach ($indicadores as $ind) {
            $data[] = [
                'novedad_id' => $novedad_id,
                'tipo' => $ind['tipo'], // 'académico', 'conductual' o 'presentación'
                'indicador' => $ind['indicador']
            ];
        }
        if (!empty($data)) {
            $this->db->insert_batch($this->table_indicadores, $data);
        }
    }

    public function get_indicadores($novedad_id)
    {
        $this->db->where('novedad_id', $novedad_id);
        return $this->db->get($this->table_indicadores)->result_array();
    }
}
