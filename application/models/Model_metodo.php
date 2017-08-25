<?php
class Model_metodo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_metodo_cbo($datos=FALSE)
	{
		$this->db->select('idmetodo, descripcion');
		$this->db->from('metodo');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		$this->db->where('estado_m', 1);
		$this->db->order_by('descripcion', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_metodo($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('metodo');
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
	public function m_count_metodo()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('metodo');
		$this->db->where('estado_m <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idmetodo',$datos['id']);
		return $this->db->update('metodo', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_m' => 1,
		);
		return $this->db->insert('metodo', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_m' => 0
		);
		$this->db->where('idmetodo',$id);
		if($this->db->update('metodo', $data)){
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
		$this->db->where('idmetodo',$id);
		if($this->db->update('metodo', $data)){
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
		$this->db->where('idmetodo',$id);
		if($this->db->update('metodo', $data)){
			return true;
		}else{
			return false;
		}
	}

}