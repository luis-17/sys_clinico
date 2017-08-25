<?php
class Model_empresa_cliente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_empresas($paramPaginate){ 
		$this->db->select('*');
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo 
		// SI ES USUARIO SALUD OCUPACIONAL, SOLO VE SUS CLIENTES
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$this->db->where('si_salud_ocupacional', 1); // SI 
		}
		
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
			$this->db->order_by('descripcion','ASC');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_empresas($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo
		// SI ES USUARIO SALUD OCUPACIONAL, SOLO VE SUS CLIENTES
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$this->db->where('si_salud_ocupacional', 1); // SI 
		}
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
	
	public function m_cargar_empresas_cbo($datos = FALSE){ 
		$this->db->distinct();
		$this->db->select('idempresacliente, descripcion, ruc_empresa, estado_ec');
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo
		if( $datos ){
			$this->db->ilike('descripcion', $datos['search']);
		}else{
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresas_salud_ocupacional_cbo($datos=FALSE)
	{
		$this->db->select('idempresacliente, descripcion, ruc_empresa, estado_ec');
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo
		$this->db->where('si_salud_ocupacional', 1); // si
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresa_cliente_autocomplete_so($datos) // salud ocupacional 
	{
		$this->db->select('idempresacliente, descripcion, si_salud_ocupacional, estado_ec');
		$this->db->from('empresa_cliente');
		if( $datos ){ 
			$this->db->ilike('descripcion', $datos['search']);
		}
		$this->db->where('estado_ec', 1); // activo 
		$this->db->where('si_salud_ocupacional', 1); // activo 
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['empresa']),
			'ruc_empresa' => $datos['ruc_empresa'],
			'domicilio_fiscal' => $datos['domicilio_fiscal'],
			'telefono' => $datos['telefono'],
			'si_salud_ocupacional' => (int)$datos['pertenece_salud_ocup'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idempresacliente',$datos['idempresacliente']);
		return $this->db->update('empresa_cliente', $data);
	}
	public function m_registrar($datos)
	{
		return $this->db->insert('empresa_cliente', $datos);
	}
	
	public function m_anular($idempresacliente)
	{
		$data = array(
			'estado_ec' => 0
		);
		$this->db->where('idempresacliente',$idempresacliente);
		if($this->db->update('empresa_cliente', $data)){
			return true;
		}else{
			return false;
		}
	}
}