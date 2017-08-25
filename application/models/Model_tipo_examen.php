<?php
class Model_tipo_examen extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	public function m_cargar_tipoExamen_cbo($datos=FALSE)
	{
		//$this->db->select('idtipovia, descripcion_tv, abreviatura_tv');
		$this->db->from('tipo_examen');
		$this->db->where('estado_tex', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipoExamen($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('tipo_examen');
		$this->db->where('estado_tex <>', 0);
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
	public function m_count_tipoExamen()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_examen');
		$this->db->where('estado_tex <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion_tex' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idtipoexamen',$datos['id']);
		return $this->db->update('tipo_examen', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_tex' => strtoupper($datos['descripcion']),
			'estado_tex' => 1,
		);
		return $this->db->insert('tipo_examen', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_tex' => 0
		);
		$this->db->where('idtipoexamen',$id);
		if($this->db->update('tipo_examen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_tex' => 1
		);
		$this->db->where('idtipoexamen',$id);
		if($this->db->update('tipo_examen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_tex' => 2
		);
		$this->db->where('idtipoexamen',$id);
		if($this->db->update('tipo_examen', $data)){
			return true;
		}else{
			return false;
		}
	}
}