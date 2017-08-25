<?php
class Model_presentacion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_presentacion_cbo($datos=FALSE)
	{
		$this->db->select('idpresentacion, descripcion_pr');
		$this->db->from('presentacion');
		$this->db->where('estado_pr <>', 0);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_presentacion($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('presentacion');
		$this->db->where('estado_pr <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_presentacion($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('presentacion');
		$this->db->where('estado_pr <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_pr' => strtoupper($datos['descripcion']),
			'abreviatura' => strtoupper($datos['abreviatura']),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idpresentacion',$datos['id']);
		return $this->db->update('presentacion', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_pr' => strtoupper($datos['descripcion']),
			'abreviatura' => strtoupper($datos['abreviatura']),
			'estado_pr' => 1,
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('presentacion', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pr' => 0
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_pr' => 1
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_pr' => 2
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}



}