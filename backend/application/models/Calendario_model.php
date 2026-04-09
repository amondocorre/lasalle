<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendario_model extends CI_Model
{
    private $table = 'actividades';

    public function __construct()
    {
        parent::__construct();
        $this->crear_tabla_si_no_existe();
    }
    
    private function crear_tabla_si_no_existe()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->load->dbforge();
            
            $fields = [
                'id' => [
                    'type' => 'INT',
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'curso_id' => [
                    'type' => 'INT',
                    'unsigned' => TRUE,
                ],
                'titulo' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                ],
                'descripcion' => [
                    'type' => 'TEXT',
                    'null' => TRUE,
                ],
                'fecha' => [
                    'type' => 'DATE',
                ],
                'hora_inicio' => [
                    'type' => 'TIME',
                    'null' => TRUE,
                ],
                'hora_fin' => [
                    'type' => 'TIME',
                    'null' => TRUE,
                ],
                'usuario_id' => [
                    'type' => 'INT',
                    'unsigned' => TRUE,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                ]
            ];
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table($this->table, TRUE, ['ENGINE' => 'InnoDB', 'COMMENT' => "'Actividades y talleres del calendario por curso'"]);
            
            // Alter for foreign keys and default timestamp since dbforge doesn't fully support DEFAULT CURRENT_TIMESTAMP
            $this->db->query("ALTER TABLE ".$this->table." CHANGE created_at created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            $this->db->query("ALTER TABLE ".$this->table." ADD CONSTRAINT fk_actividades_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE");
            $this->db->query("ALTER TABLE ".$this->table." ADD CONSTRAINT fk_actividades_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE");
        }
    }

    public function get_by_curso_month($curso_id, $mes, $gestion)
    {
        $this->db->select('a.*, u.nombre as nombre_usuario');
        $this->db->from($this->table.' a');
        $this->db->join('usuarios u', 'a.usuario_id = u.id');
        $this->db->where('a.curso_id', $curso_id);
        $this->db->where('MONTH(a.fecha)', $mes);
        $this->db->where('YEAR(a.fecha)', $gestion);
        $this->db->order_by('a.fecha', 'ASC');
        $this->db->order_by('a.hora_inicio', 'ASC');
        return $this->db->get()->result_array();
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
}
