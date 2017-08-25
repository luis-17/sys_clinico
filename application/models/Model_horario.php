<?php
class Model_horario extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_horario($datos){ 
		$this->db->select('idhorario, descripcion, estado_h');
		$this->db->from('rh_horario');
		$this->db->where('estado_h', 1); // habilitado 
		if( !empty($datos['arrIdHorario']) ){
			$this->db->where_in('idhorario', $datos['arrIdHorario']);
		}
		return $this->db->get()->result_array();
	}
}