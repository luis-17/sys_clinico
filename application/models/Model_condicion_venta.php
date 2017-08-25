<?php
class Model_condicion_venta extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_condicion_venta_cbo($datos = FALSE){ 
		$this->db->select('idcondicionventa, descripcion_cv, estado_cv'); 
		$this->db->from('far_condicion_venta'); 
		$this->db->where('estado_cv', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_condicion_venta($paramPaginate){
		$this->db->from('far_condicion_venta');
		$this->db->where('estado_cv <>', 0);
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
	public function m_buscar_condicion_venta($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_condicion_venta'); 
		$this->db->where('descripcion_cv', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}

	public function m_count_condicion_venta()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_condicion_venta');
		$this->db->where('estado_cv <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_cv' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idcondicionventa',$datos['id']);
		return $this->db->update('far_condicion_venta', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_cv' => strtoupper($datos['descripcion']),
			'estado_cv' => 1,
		);
		return $this->db->insert('far_condicion_venta', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_cv' => 0
		);
		$this->db->where('idcondicionventa',$id);
		if($this->db->update('far_condicion_venta', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_cv' => 1
		);
		$this->db->where('idcondicionventa',$id);
		if($this->db->update('far_condicion_venta', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_cv' => 2
		);
		$this->db->where('idcondicionventa',$id);
		if($this->db->update('far_condicion_venta', $data)){
			return true;
		}else{
			return false;
		}
	}

}