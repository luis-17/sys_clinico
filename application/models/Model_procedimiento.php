<?php
class Model_procedimiento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_procedimiento($paramPaginate){
		//$this->db->select('idprocedimiento, descripcion, activo');
		$this->db->from('procedimiento');
		$this->db->where('activo <>', 0);
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
	public function m_count_procedimiento()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('procedimiento');
		$this->db->where('activo <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprocedimiento',$datos['id']);
		return $this->db->update('procedimiento', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'activo' => 1,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
			

		);
		return $this->db->insert('procedimiento', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'activo' => 0
		);
		$this->db->where('idprocedimiento',$id);
		if($this->db->update('procedimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'activo' => 1
		);
		$this->db->where('idprocedimiento',$id);
		if($this->db->update('procedimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'activo' => 2
		);
		$this->db->where('idprocedimiento',$id);
		if($this->db->update('procedimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
}