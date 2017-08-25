<?php
class Model_urocultivo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_urocultivo_cbo($datos=FALSE)
	{
		$this->db->select('idurocultivo, descripcion_uro');
		$this->db->from('urocultivo');
		$this->db->where('estado_uro', 1);
		$this->db->order_by('descripcion_uro', 'ASC');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_urocultivo($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('urocultivo');
		$this->db->where('estado_uro <>', 0);
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
	public function m_count_urocultivo($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('urocultivo');
		$this->db->where('estado_uro <>', 0);
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
			'descripcion_uro' => strtoupper($datos['descripcion']),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idurocultivo',$datos['id']);
		return $this->db->update('urocultivo', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_uro' => strtoupper($datos['descripcion']),
			'estado_uro' => 1,
			// 'createdAt' => date('Y-m-d H:i:s'), 
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('urocultivo', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_uro' => 0
		);
		$this->db->where('idurocultivo',$id);
		if($this->db->update('urocultivo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_uro' => 1
		);
		$this->db->where('idurocultivo',$id);
		if($this->db->update('urocultivo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_uro' => 2
		);
		$this->db->where('idurocultivo',$id);
		if($this->db->update('urocultivo', $data)){
			return true;
		}else{
			return false;
		}
	}



}