<?php
class Model_procedencia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_procedencias($paramPaginate){ 
		$this->db->select('idprocedencia, descripcion, estado_pro');
		$this->db->from('procedencia');
		$this->db->where('estado_pro', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_procedencias()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('procedencia');
		$this->db->where('estado_pro', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_procedencias_cbo($datos = FALSE){ 
		$this->db->select('idprocedencia, descripcion, estado_pro');
		$this->db->from('procedencia');
		$this->db->where('estado_pro', 1); // activo
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		$this->db->order_by('idprocedencia','ASC');
		return $this->db->get()->result_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprocedencia',$datos['id']);
		return $this->db->update('procedencia', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('procedencia', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pro' => 0
		);
		$this->db->where('idprocedencia',$id);
		if($this->db->update('procedencia', $data)){
			return true;
		}else{
			return false;
		}
	}
}