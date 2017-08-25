<?php
class Model_banco extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_banco_cbo(){ 
		$this->db->select('idbanco, descripcion_banco, estado_banco');
		$this->db->from('ct_banco');
		$this->db->where('estado_banco', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_bancos($paramPaginate){ 
		$this->db->select('idbanco, descripcion_banco AS descripcion, estado_banco');
		$this->db->from('ct_banco');
		$this->db->where('estado_banco', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_bancos()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_banco');
		$this->db->where('estado_banco', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_banco' => strtoupper($datos['descripcion']),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idbanco',$datos['id']);
		return $this->db->update('ct_banco', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_banco' => strtoupper($datos['descripcion']),
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('ct_banco', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_banco' => 0
		);
		$this->db->where('idbanco',$id);
		if($this->db->update('ct_banco', $data)){
			return true;
		}else{
			return false;
		}
	}
}