<?php
class Model_presentacion_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_presentacion_farmacia_cbo($datos = FALSE){ 
		$this->db->select('idpresentacion, descripcion_pres, abreviatura_pres, estado_pres'); 
		$this->db->from('far_presentacion'); 
		$this->db->where('estado_pres', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_presentacion_farmacia($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('far_presentacion');
		$this->db->where('estado_pres <>', 0);
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
	public function m_buscar_presentacion_farmacia($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_presentacion'); 
		$this->db->where('descripcion_pres', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}

	public function m_count_presentacion_farmacia()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_presentacion');
		$this->db->where('estado_pres <>', 0);
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
			'descripcion_pres' => strtoupper($datos['descripcion']),
			'abreviatura_pres' => strtoupper($datos['abreviatura'])
		);
		$this->db->where('idpresentacion',$datos['id']);
		return $this->db->update('far_presentacion', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_pres' => strtoupper($datos['descripcion']),
			'abreviatura_pres' => strtoupper($datos['abreviatura']),
			'estado_pres' => 1,
		);
		return $this->db->insert('far_presentacion', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pres' => 0
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('far_presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_pres' => 1
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('far_presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_pres' => 2
		);
		$this->db->where('idpresentacion',$id);
		if($this->db->update('far_presentacion', $data)){
			return true;
		}else{
			return false;
		}
	}
}