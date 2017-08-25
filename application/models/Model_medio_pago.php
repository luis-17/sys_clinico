<?php
class Model_medio_pago extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_medio_pago_cbo($datos = FALSE){ 
		$this->db->select('idmediopago, descripcion_med, estado_med');
		$this->db->from('medio_pago');
		$this->db->where('estado_med', 1); // activo
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(25);
		}
		return $this->db->get()->result_array();
	}
	// ==========================================
	// OBTENER TODOS LOS MEDIOS DE PAGO
	// ==========================================
	public function m_cargar_medio_pago_mixto_cbo($datos = FALSE){ 
		$this->db->select('idmediopago, descripcion_med, estado_med');
		$this->db->from('medio_pago');
		$this->db->where_in('estado_med', array(1,2));
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(25);
		}
		$this->db->order_by('idmediopago', 'ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_medio_pago($paramPaginate){ 
		//$this->db->select('idprecio, nombre, descripcion, porcentaje, tipo_precio, estado_pr');
		$this->db->from('medio_pago');
		$this->db->where('estado_med', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	// ==========================================
	// CANTIDAD DE REGISTROS EN PRECIOS
	// ==========================================
	public function m_count_medio_pago()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medio_pago');
		$this->db->where('estado_med', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	// ==========================================
	// CRUD
	// ==========================================
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_med' => strtoupper($datos['descripcion'])
			
		);
		$this->db->where('idmediopago',$datos['id']);
		return $this->db->update('medio_pago', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_med' => strtoupper($datos['descripcion'])
		);
		return $this->db->insert('medio_pago', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_med' => 0
		);
		$this->db->where('idmediopago',$id);
		if($this->db->update('medio_pago', $data)){
			return true;
		}else{
			return false;
		}
	}
}