<?php
class Model_tipo_documento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_documento_venta_cbo($datos = FALSE){ 
		$this->db->select('idtipodocumento, descripcion_td, abreviatura');
		$this->db->from('tipo_documento');
		$this->db->where('estado_td', 1); // habilitado
		$this->db->where_in('destino', array(1,3));
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}
		$this->db->order_by('idtipodocumento'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_documento_venta($datos = FALSE){ 
		$this->db->select('idtipodocumento, descripcion_td, abreviatura');
		$this->db->from('tipo_documento');
		$this->db->where('estado_td', 1); // habilitado
		$this->db->where_in('destino', array(1,3,4));
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}
		$this->db->order_by('idtipodocumento'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_documento_contabilidad(){ 
		$this->db->select('td.idtipodocumento, td.descripcion_td, td.abreviatura, td.porcentaje_imp, td.nombre_impuesto, td.codigo_plan');
		$this->db->from('tipo_documento td');
		$this->db->where('estado_conta', 1); // habilitado
		$this->db->order_by('idtipodocumento','ASC'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_tipo_documento_venta($datos)
	{
		//var_dump($datos); exit();
		$this->db->select('idtipodocumento, descripcion_td, abreviatura');
		$this->db->from('tipo_documento');
		$this->db->where('estado_td', 1); // habilitado
		$this->db->where_in('destino', array(1,3,4));
		$this->db->ilike($datos['searchColumn'], $datos['searchText']);
		$this->db->limit(1);
		return $this->db->get()->row_array();

	}
	public function m_cargar_tipo_documento_almacenlab_cbo()
	{
		//var_dump($datos); exit();
		$this->db->select('idtipodocumento, descripcion_td, abreviatura');
		$this->db->from('tipo_documento');
		$this->db->where('estado_td', 1); // habilitado
		$this->db->where_in('idtipodocumento', array(2,11));
		$this->db->order_by('idtipodocumento'); 
		return $this->db->get()->result_array();

	}

	// ==========================================
	// OBTENER TODOS LOS TIPOS DE DOCS DE VENTA
	// ==========================================
	public function m_cargar_tipo_documento($paramPaginate){ 
		//$this->db->select('idprecio, nombre, descripcion, porcentaje, tipo_precio, estado_pr');
		$this->db->from('tipo_documento');
		$this->db->where('estado_td !=', 0); // habilitado
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']); 
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	// ==========================================
	// CANTIDAD DE REGISTROS EN TIPOS DE DOC
	// ==========================================
	public function m_count_tipos()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_documento');
		$this->db->where('estado_td !=', 0); // habilitado
		
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_habilitar($id)
	{
		$data = array(
			'estado_td' => 1
		);
		$this->db->where('idtipodocumento',$id);
		if($this->db->update('tipo_documento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_td' => 2
		);
		$this->db->where('idtipodocumento',$id);
		if($this->db->update('tipo_documento', $data)){
			return true;
		}else{
			return false;
		}
	}

}