<?php
class Model_proveedor_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_proveedor_farmacia_cbo($datos=FALSE)
	{
		//$this->db->select('idproveedor,razon_social');
		$this->db->from('far_proveedor');
		$this->db->where('estado_prov <>', 0);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_proveedor_farmacia($paramPaginate){
		$this->db->select('p.*, tp.descripcion_tprov');
		$this->db->from('far_proveedor p');
		$this->db->join('far_tipo_proveedor tp','p.idtipoproveedor = tp.idtipoproveedor'); 
		$this->db->where('estado_prov <>', 0);
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
	public function m_cargar_este_proveedor_farmacia_por_codigo($datos)
	{
		$this->db->select('idproveedor,razon_social');
		$this->db->from('far_proveedor');
		$this->db->where('idproveedor', $datos['id']);
		$this->db->where('estado_prov',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_proveedor_farmacia_por_ruc($datos)
	{
		$this->db->select('idproveedor, razon_social, representante, ruc, direccion_fiscal, telefono, celular, email');
		$this->db->from('far_proveedor');
		$this->db->where('ruc', $datos['ruc']);
		$this->db->where('estado_prov',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_proveedor_farmacia_por_id($datos)
	{
		$this->db->select('idproveedor, razon_social, representante, ruc, direccion_fiscal, telefono, celular, email');
		$this->db->from('far_proveedor');
		$this->db->where('idproveedor', $datos['idproveedor']);
		$this->db->where('estado_prov',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_count_proveedor_farmacia()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_proveedor');
		$this->db->where('estado_prov <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'razon_social' => strtoupper($datos['razon_social']),
			'ruc' => $datos['ruc'],
			'idtipoproveedor' => $datos['idtipoproveedor'],
			'representante' => (empty($datos['representante']) ? NULL : $datos['representante']),
			'nombre_comercial' => (empty($datos['nombre_comercial']) ? NULL : strtoupper($datos['nombre_comercial'])),
			'direccion_fiscal' => (empty($datos['direccion_fiscal']) ? NULL : $datos['direccion_fiscal']),
			'celular' => (empty($datos['celular']) ? NULL : $datos['celular']),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'fax' => (empty($datos['fax']) ? NULL : $datos['fax']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'forma_pago' => $datos['forma_pago'],
			'moneda' => $datos['moneda'],
			'updatedAt' => date('Y-m-d H:i:s'),
			'observaciones_prov' => (empty($datos['observaciones']) ? NULL : $datos['observaciones']),

		);
		$this->db->where('idproveedor',$datos['id']);
		return $this->db->update('far_proveedor', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'razon_social' => strtoupper($datos['razon_social']),
			'ruc' => $datos['ruc'],
			'idtipoproveedor' => $datos['idtipoproveedor'],
			'representante' => (empty($datos['representante']) ? NULL : $datos['representante']),
			'nombre_comercial' => (empty($datos['nombre_comercial']) ? NULL : strtoupper($datos['nombre_comercial'])),
			'direccion_fiscal' => (empty($datos['direccion_fiscal']) ? NULL : $datos['direccion_fiscal']),
			'celular' => (empty($datos['celular']) ? NULL : $datos['celular']),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'fax' => (empty($datos['fax']) ? NULL : $datos['fax']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'forma_pago' => $datos['forma_pago'],
			'moneda' => $datos['moneda'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_prov' => 1,
			'observaciones_prov' => (empty($datos['observaciones']) ? NULL : $datos['observaciones']),
		);
		return $this->db->insert('far_proveedor', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_prov' => 0
		);
		$this->db->where('idproveedor',$id);
		if($this->db->update('far_proveedor', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_prov' => 1
		);
		$this->db->where('idproveedor',$id);
		if($this->db->update('far_proveedor', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_prov' => 2
		);
		$this->db->where('idproveedor',$id);
		if($this->db->update('far_proveedor', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_carga_proveedor_farmacia_por_rs($datos){
		$this->db->from('far_proveedor');
		$this->db->where('estado_prov <>', 0);
		$this->db->where('idproveedor <>', $datos['id']);
		$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));		
		return $this->db->get()->result_array();
	}
}