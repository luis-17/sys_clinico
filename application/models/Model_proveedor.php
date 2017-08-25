<?php
class Model_proveedor extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_proveedor_cbo($datos=FALSE)
	{
		//$this->db->select('idproveedor,razon_social');
		$this->db->from('proveedor');
		$this->db->where('estado_pr <>', 0);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_proveedor($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('proveedor');
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
	
	public function m_cargar_este_proveedor_por_codigo($datos)
	{
		$this->db->select('idproveedor,razon_social');
		$this->db->from('proveedor');
		$this->db->where('idproveedor', $datos['id']);
		$this->db->where('estado_pr',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_count_proveedor($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('proveedor');
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
			'razon_social' => strtoupper($datos['razon_social']),
			'ruc' => $datos['ruc'],
			'representante_legal' => (empty($datos['representante_legal']) ? NULL : $datos['representante_legal']),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'updatedAt' => date('Y-m-d H:i:s')

		);
		$this->db->where('idproveedor',$datos['id']);
		return $this->db->update('proveedor', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'razon_social' => strtoupper($datos['razon_social']),
			'ruc' => $datos['ruc'],
			'representante_legal' => (empty($datos['representante_legal']) ? NULL : strtoupper($datos['representante_legal'])),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_pr' => 1
		);
		return $this->db->insert('proveedor', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pr' => 0
		);
		$this->db->where('idproveedor',$id);
		if($this->db->update('proveedor', $data)){
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
		$this->db->where('idproveedor',$id);
		if($this->db->update('proveedor', $data)){
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
		$this->db->where('idproveedor',$id);
		if($this->db->update('proveedor', $data)){
			return true;
		}else{
			return false;
		}
	}
}