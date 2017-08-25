<?php
class Model_area_empresa extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_area_empresa_cbo($datos = FALSE){ 
		$this->db->select('idareaempresa, descripcion_ae, estado_ae');
		$this->db->from('rh_area_empresa');
		$this->db->where('estado_ae', 1); // activo
		
		return $this->db->get()->result_array();
	}
}