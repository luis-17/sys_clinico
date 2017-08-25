<?php 
class Model_asiento_contable extends CI_Model { 
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_asiento_contable_egreso($paramDatos){ // 
		
		$this->db->select('ac.idasientocontable, ac.idmovimiento, ac.monto, ac.fecha_emision, ac.glosa, ac.codigo_plan, ac.debe_haber'); 
		$this->db->from('ct_asiento_contable ac');
		$this->db->join('ct_movimiento m','ac.idmovimiento = m.idmovimiento');
		$this->db->where('m.idmovimiento', $paramDatos); 

		return $this->db->get()->result_array();
	}
	public function m_count_asiento_contable_egreso($paramDatos)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('ct_asiento_contable ac');
		$this->db->join('ct_movimiento m','ac.idmovimiento = m.idmovimiento');
		$this->db->where('m.idmovimiento', $paramDatos); 

		return $this->db->get()->row_array();
	}
	public function m_cargar_asiento_contable_planilla($paramDatos){ // 
		
		$this->db->select('ac.idasientocontable, ac.idplanilla, ac.monto, ac.fecha_emision, ac.glosa, ac.codigo_plan, ac.debe_haber, (ac.monto::numeric) AS monto_formato'); 
		$this->db->from('ct_asiento_contable ac');
		$this->db->where('ac.idplanilla', $paramDatos); 
		$this->db->order_by('ac.debe_haber','ASC');
		$this->db->order_by('ac.codigo_plan','ASC');
		return $this->db->get()->result_array();
	}
}
