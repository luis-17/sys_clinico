<?php
class Model_forma_farmaceutica extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_formas_farmaceuticas_cbo($datos = FALSE){ 
		$this->db->select('idformafarmaceutica, descripcion_ff, acepta_caja_unidad, estado_ff'); 
		$this->db->from('far_forma_farmaceutica'); 
		$this->db->where('estado_ff', 1); // activo 
		$this->db->order_by('descripcion_ff');
		return $this->db->get()->result_array();
	}
	public function m_cargar_formas_farmaceuticas($paramPaginate){
		$this->db->select('idformafarmaceutica, descripcion_ff, acepta_caja_unidad, estado_ff'); 
		$this->db->from('far_forma_farmaceutica');
		$this->db->where('estado_ff <>', 0);

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
	public function m_buscar_forma_farmaceutica($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_forma_farmaceutica'); 
		$this->db->where('descripcion_ff', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}
	public function m_count_formas_farmaceuticas()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_forma_farmaceutica');
		$this->db->where('estado_ff <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_ff' => strtoupper($datos['descripcion']),
			'acepta_caja_unidad' =>$datos['cajaUnidad']
		);
		$this->db->where('idformafarmaceutica',$datos['id']);
		return $this->db->update('far_forma_farmaceutica', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_ff' => strtoupper($datos['descripcion']),
			'acepta_caja_unidad' => $datos['cajaUnidad'],
			'estado_ff' => 1,
		);
		return $this->db->insert('far_forma_farmaceutica', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ff' => 0
		);
		$this->db->where('idformafarmaceutica',$id);
		if($this->db->update('far_forma_farmaceutica', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_ff' => 1
		);
		$this->db->where('idformafarmaceutica',$id);
		if($this->db->update('far_forma_farmaceutica', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_ff' => 2
		);
		$this->db->where('idformafarmaceutica',$id);
		if($this->db->update('far_forma_farmaceutica', $data)){
			return true;
		}else{
			return false;
		}
	}
}