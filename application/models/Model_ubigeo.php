<?php
class Model_ubigeo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_departamentos_cbo($datos=FALSE)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('idprovincia', '00');
		$this->db->where('iddistrito', '00');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_departamento_por_codigo($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('idprovincia', '00');
		$this->db->where('iddistrito', '00');
		$this->db->where('iddepartamento', $datos['codigo']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_provincias_cbo($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('iddistrito', '00');
		$this->db->where('idprovincia <>', '00');
		$this->db->where('iddepartamento', $datos['iddepartamento']);
		if( isset($datos['search']) ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_esta_provincia_por_codigo($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('iddistrito', '00');
		$this->db->where('idprovincia <>', '00');
		$this->db->where('iddepartamento', $datos['iddepartamento']);
		$this->db->where('idprovincia', $datos['codigo']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_distritos_cbo($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('idprovincia', $datos['idprovincia']);
		$this->db->where('iddepartamento', $datos['iddepartamento']);
		$this->db->where('iddistrito <>', '00');
		if( isset($datos['search']) ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_distrito_por_codigo($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig, iddepartamento, idprovincia, iddistrito');
		$this->db->from('ubigeo');
		$this->db->where('idprovincia', $datos['idprovincia']);
		$this->db->where('iddepartamento', $datos['iddepartamento']);
		$this->db->where('iddistrito <>', '00');
		$this->db->where('iddistrito', $datos['codigo']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_dptos_por_autocompletado($datos)
	{
		
		$this->db->from('ubigeo');
		$this->db->where('idprovincia', '00');
		$this->db->where('iddistrito', '00');

		if( $datos ){ 
			$this->db->ilike('descripcion_ubig', $datos['search']);
		}
		
		$this->db->limit(5);
		return $this->db->get()->result_array();
	}

	public function m_cargar_prov_por_autocompletado($datos)
	{
		
		$this->db->from('ubigeo');
		$this->db->where('iddepartamento', $datos['id']);
		$this->db->where('idprovincia <>', '00');
		$this->db->where('iddistrito', '00');

		if( $datos ){ 
			$this->db->ilike('descripcion_ubig', $datos['search']);
		}
		
		$this->db->limit(5);
		return $this->db->get()->result_array();
	}
	public function m_cargar_distr_por_autocompletado($datos)
	{
		
		$this->db->from('ubigeo');
		$this->db->where('iddepartamento', $datos['id_dpto']);
		$this->db->where('idprovincia', $datos['id_prov']);
		$this->db->where('iddistrito <>', '00');

		if( !empty($datos['search']) ){ 
			$this->db->ilike('descripcion_ubig', $datos['search']);
			$this->db->limit(5);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_ubigeo_concatenado($datos)
	{
		$this->db->select('idubigeo, descripcion_ubig');
		$this->db->from('ubigeo');
		$this->db->where("concat_ws('',trim(iddepartamento), trim(idprovincia), trim(iddistrito)) = '" . $datos['ubigeo'] ."'", '', FALSE);
		$this->db->limit(1);
		return $this->db->get()->row_array();

	}
}