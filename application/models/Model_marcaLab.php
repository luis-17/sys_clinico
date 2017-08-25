<?php
class Model_marcalab extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_marca_cbo($datos=FALSE)
	{
		$this->db->select('idmarca, descripcion_m');
		$this->db->from('marcalab');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_marca($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('marcalab');
		$this->db->where('estado_m <>', 0);
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
	public function m_count_marca($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('marcalab');
		$this->db->where('estado_m <>', 0);
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
			'descripcion_m' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idmarca',$datos['id']);
		return $this->db->update('marcalab', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_m' => strtoupper($datos['descripcion']),
			'estado_m' => 1,
		);
		return $this->db->insert('marcalab', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_m' => 0
		);
		$this->db->where('idmarca',$id);
		if($this->db->update('marcalab', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_m' => 1
		);
		$this->db->where('idmarca',$id);
		if($this->db->update('marcalab', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_m' => 2
		);
		$this->db->where('idmarca',$id);
		if($this->db->update('marcalab', $data)){
			return true;
		}else{
			return false;
		}
	}



}