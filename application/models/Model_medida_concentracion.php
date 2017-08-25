<?php
class Model_medida_concentracion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_medida_concentracion_cbo($datos = FALSE){ 
		$this->db->select('idmedidaconcentracion, descripcion_mc, abreviatura_mc, estado_mc'); 
		$this->db->from('far_medida_concentracion'); 
		$this->db->where('estado_mc', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_medida_concentracion($paramPaginate){
		$this->db->from('far_medida_concentracion');
		$this->db->where('estado_mc <>', 0);
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

	public function m_buscar_medida_concentracion($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_medida_concentracion'); 
		$this->db->where('descripcion_mc', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}


	public function m_count_medida_concentracion()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_medida_concentracion');
		$this->db->where('estado_mc <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_mc' => strtoupper($datos['descripcion']),
			'abreviatura_mc' => strtoupper($datos['abreviatura'])
		);
		$this->db->where('idmedidaconcentracion',$datos['id']);
		return $this->db->update('far_medida_concentracion', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_mc' => strtoupper($datos['descripcion']),
			'abreviatura_mc' => strtoupper($datos['abreviatura']),
			'estado_mc' => 1
		);
		return $this->db->insert('far_medida_concentracion', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_mc' => 0
		);
		$this->db->where('idmedidaconcentracion',$id);
		if($this->db->update('far_medida_concentracion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_mc' => 1
		);
		$this->db->where('idmedidaconcentracion',$id);
		if($this->db->update('far_medida_concentracion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_mc' => 2
		);
		$this->db->where('idmedidaconcentracion',$id);
		if($this->db->update('far_medida_concentracion', $data)){
			return true;
		}else{
			return false;
		}
	}
}