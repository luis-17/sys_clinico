<?php
class Model_unidades_laboratorio extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_unidades_laboratorio_cbo($datos=FALSE)
	{
		$this->db->select('idunidadlaboratorio, descripcion');
		$this->db->from('unidad_laboratorio');
		$this->db->where('estado_ul <>', 0);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_unidadesLaboratorio($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('unidad_laboratorio');
		$this->db->where('estado_ul <>', 0);
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
	public function m_count_unidadesLaboratorio($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('unidad_laboratorio');
		$this->db->where('estado_ul <>', 0);
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

	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idunidadlaboratorio',$datos['id']);
		return $this->db->update('unidad_laboratorio', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'estado_ul' => 1,
		);
		return $this->db->insert('unidad_laboratorio', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ul' => 0
		);
		$this->db->where('idunidadlaboratorio',$id);
		if($this->db->update('unidad_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_ul' => 1
		);
		$this->db->where('idunidadlaboratorio',$id);
		if($this->db->update('unidad_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_ul' => 2
		);
		$this->db->where('idunidadlaboratorio',$id);
		if($this->db->update('unidad_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}



}