<?php
class Model_tipo_proveedor_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_proveedor_farmacia_cbo($datos = FALSE){ 
		$this->db->select('idtipoproveedor, descripcion_tprov, estado_tprov'); 
		$this->db->from('far_tipo_proveedor'); 
		$this->db->where('estado_tprov', 1); // activo 
		return $this->db->get()->result_array();
	}
}