<?php
class Model_tipo_muestra extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_muestra_cbo($datos=FALSE)
	{
		$this->db->select('idtipomuestra, descripcion');
		$this->db->from('tipomuestra'); 
		$this->db->where('estado_m', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipoMuestra($paramPaginate){
		$this->db->select('idtipomuestra, descripcion, estado_m');
		$this->db->from('tipomuestra');
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
	public function m_count_tipoMuestra()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipomuestra');
		$this->db->where('estado_m <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idtipomuestra',$datos['id']);
		return $this->db->update('tipomuestra', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'estado_m' => 1,
		);
		return $this->db->insert('tipomuestra', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_m' => 0
		);
		$this->db->where('idtipomuestra',$id);
		if($this->db->update('tipomuestra', $data)){
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
		$this->db->where('idtipomuestra',$id);
		if($this->db->update('tipomuestra', $data)){
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
		$this->db->where('idtipomuestra',$id);
		if($this->db->update('tipomuestra', $data)){
			return true;
		}else{
			return false;
		}
	}
}