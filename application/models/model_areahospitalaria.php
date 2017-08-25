<?php
class Model_areaHospitalaria extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_areahospitalaria($paramPaginate){
		//$this->db->select('idareahospitalaria, descripcion_aho, estado_aho');
		$this->db->from('area_hospitalaria');
		//$this->db->where('estado_aho', 1);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_areahospitalaria()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('area_hospitalaria');
		//$this->db->where('estado_aho', 1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_aho' => strtoupper($datos['descripcion'])

		);
		$this->db->where('idareahospitalaria',$datos['id']);
		return $this->db->update('area_hospitalaria', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_aho' => strtoupper($datos['descripcion'])

		);
		return $this->db->insert('area_hospitalaria', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_aho' => 0
		);
		$this->db->where('idareahospitalaria',$id);
		if($this->db->update('area_hospitalaria', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_aho' => 1
		);
		$this->db->where('idareahospitalaria',$id);
		if($this->db->update('area_hospitalaria', $data)){
			return true;
		}else{
			return false;
		}
	}
}