<?php
class Model_contingencia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_contingencia($paramPaginate){ 
		$this->db->select('idespecialidad, te.idtipoespecialidad, descripcion, nombre');
		$this->db->from('especialidad e');
		$this->db->join('tipo_especialidad te','e.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		$this->db->where('estado', 1); // activo
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
	public function m_cargar_contingencia_cbo($datos=FALSE)
	{
		
		$this->db->from('contingencia');
		$this->db->where('estado_ctg', 1);

		return $this->db->get()->result_array();
	}
	public function m_cargar_contingencia_por_autocompletado($datos)
	{
		
		// $this->db->from('especialidad');
		$this->db->select('idcontingencia,descripcion_ctg');
		$this->db->from('contingencia');
		$this->db->where('estado_ctg', 1); // empresa 
		

		if( $datos ){ 
			$this->db->ilike('descripcion_ctg', $datos['search']);
		}
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
}