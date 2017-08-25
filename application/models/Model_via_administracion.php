<?php
class Model_via_administracion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_via_administracion_cbo($datos = FALSE){ 
		$this->db->select('idviaadministracion, descripcion_va, estado_va'); 
		$this->db->from('far_via_administracion'); 
		$this->db->where('estado_va', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_via_administracion($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('far_via_administracion');
		$this->db->where('estado_va <>', 0);
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
	public function m_buscar_via_administracion($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_via_administracion'); 
		$this->db->where('descripcion_va', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}
	public function m_count_via_administracion()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_via_administracion');
		$this->db->where('estado_va <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_va' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idviaadministracion',$datos['id']);
		return $this->db->update('far_via_administracion', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_va' => strtoupper($datos['descripcion']),
			'estado_va' => 1,
		);
		return $this->db->insert('far_via_administracion', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_va' => 0
		);
		$this->db->where('idviaadministracion',$id);
		if($this->db->update('far_via_administracion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_va' => 1
		);
		$this->db->where('idviaadministracion',$id);
		if($this->db->update('far_via_administracion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_va' => 2
		);
		$this->db->where('idviaadministracion',$id);
		if($this->db->update('far_via_administracion', $data)){
			return true;
		}else{
			return false;
		}
	}

}