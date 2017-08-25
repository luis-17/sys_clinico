<?php
class Model_tipo_especialidad extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_especialidad_cbo($datos = FALSE){ 
		$this->db->select('idtipoespecialidad, descripcion, estado_te');
		$this->db->from('tipo_especialidad');
		$this->db->where('estado_te', 1); // activo
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
}