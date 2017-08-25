<?php
class Model_modulo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_modulos($datos = FALSE){ 
		$this->db->select('idmodulo, descripcion_mod, estado_mod');
		$this->db->from('modulo');
		$this->db->where('estado_mod', 1);
		$this->db->order_by('idmodulo');
		return $this->db->get()->result_array();
	}
}