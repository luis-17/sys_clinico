<?php
class Model_plan_contable extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_plan_contable_cbo(){ 
		$this->db->select('idplancontable, descripcion, codigo_plan, estado_plan');
		$this->db->from('ct_plan_contable');
		$this->db->where('estado_plan', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_plan_contable($paramPaginate){ 
		$this->db->select('idplancontable, descripcion, codigo_plan, estado_plan');
		$this->db->from('ct_plan_contable');
		$this->db->where('estado_plan', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_plan_contable()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_plan_contable');
		$this->db->where('estado_plan', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idplancontable',$datos['id']);
		return $this->db->update('ct_plan_contable', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('ct_plan_contable', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_plan' => 0
		);
		$this->db->where('idplancontable',$id);
		if($this->db->update('ct_plan_contable', $data)){
			return true;
		}else{
			return false;
		}
	}
}