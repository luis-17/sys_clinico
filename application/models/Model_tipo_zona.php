<?php
class Model_tipo_zona extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_zona_cbo($datos=FALSE)
	{
		$this->db->select('idtipozona, descripcion_tz, abreviatura_tz');
		$this->db->from('tipo_zona');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipoZona($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('tipo_zona');
		$this->db->where('estado_tz <>', 0);
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
	public function m_count_tipoZona()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_zona');
		$this->db->where('estado_tz <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_tz' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idtipozona',$datos['id']);
		return $this->db->update('tipo_zona', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_tz' => strtoupper($datos['descripcion']),
			'estado_tz' => 1,
		);
		return $this->db->insert('tipo_zona', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_tz' => 0
		);
		$this->db->where('idtipozona',$id);
		if($this->db->update('tipo_zona', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_tz' => 1
		);
		$this->db->where('idtipozona',$id);
		if($this->db->update('tipo_zona', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_tz' => 2
		);
		$this->db->where('idtipozona',$id);
		if($this->db->update('tipo_zona', $data)){
			return true;
		}else{
			return false;
		}
	}



}