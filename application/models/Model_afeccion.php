<?php
class Model_afeccion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_afeccion_paciente($paramPaginate,$datos){ 
		$this->db->select('idhistoriaafeccion,idhistoria,tipo_afeccion,descripcion,estado_afe');
		$this->db->from('historia_afeccion');
		$this->db->where('estado_afe', 1);
		$this->db->where('idhistoria', $datos);
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by('historia_afeccion.'.$paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_afeccion_paciente($datos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('historia_afeccion');
		$this->db->where('estado_afe', 1); // activo
		$this->db->where('idhistoria', $datos);
		$fila = $this->db->get()->row_array();
		return $fila['contador'];
	}
	public function m_registrar_afeccion($datos)
	{
		$data = array(
			'idhistoria' => $datos['id'],
			'tipo_afeccion' => $datos['idtipoafeccion'],
			'descripcion' => strtoupper($datos['descripcion']),
			'estado_afe' => 1
		);
		return $this->db->insert('historia_afeccion', $data);
	}
	public function m_anular($id)
	{
		$data = array( 
			'estado_afe' => 0 
		);
		$this->db->where('idhistoriaafeccion',$id);
		if($this->db->update('historia_afeccion', $data)){
			return true;
		}else{
			return false;
		}
	}
}