<?php
class Model_precio extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_precio_cbo($datos = FALSE){ 
		$this->db->select('idprecio, nombre, descripcion, porcentaje, tipo_precio');
		$this->db->from('precio');
		$this->db->where('estado_pr', 1); // activo
		if( $datos ) { 
			$this->db->ilike($datos['nameColumn'], $datos['search']);
		}
		$this->db->limit(25);
		return $this->db->get()->result_array();
	}
	// ==========================================
	// OBTENER TODOS LOS TIPOS DE PRECIOS
	// ==========================================
	public function m_cargar_precios($paramPaginate){ 
		//$this->db->select('idprecio, nombre, descripcion, porcentaje, tipo_precio, estado_pr');
		$this->db->from('precio');
		$this->db->where('estado_pr', 1); // activo
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
	public function m_count_precios()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('precio');
		$this->db->where('estado_pr', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'porcentaje' => $datos['porcentaje'],
			'tipo_precio' => $datos['tipo_precio']
		);
		$this->db->where('idprecio',$datos['id']);
		return $this->db->update('precio', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'porcentaje' => $datos['porcentaje'],
			'tipo_precio' => $datos['tipo_precio']
		);
		return $this->db->insert('precio', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pr' => 0
		);
		$this->db->where('idprecio',$id);
		if($this->db->update('precio', $data)){
			return true;
		}else{
			return false;
		}
	}
}