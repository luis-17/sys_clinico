<?php
class Model_tipo_via extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_via_cbo($datos=FALSE)
	{
		$this->db->select('idtipovia, descripcion_tv, abreviatura_tv');
		$this->db->from('tipo_via');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
}