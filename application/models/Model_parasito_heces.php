<?php
class Model_parasito_heces extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_parasito_heces_cbo($datos=FALSE)
	{
		$this->db->select('idparasito, descripcion');
		$this->db->from('parasito_heces');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		$this->db->order_by('idparasito', 'ASC');
		$this->db->where('estado_p', 1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_parasitoHeces($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('parasito_heces');
		$this->db->where('estado_p <>', 0);
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
	public function m_count_parasitoHeces()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('parasito_heces');
		$this->db->where('estado_p <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idparasito',$datos['id']);
		return $this->db->update('parasito_heces', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'estado_p' => 1,
		);
		return $this->db->insert('parasito_heces', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_p' => 0
		);
		$this->db->where('idparasito',$id);
		if($this->db->update('parasito_heces', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_p' => 1
		);
		$this->db->where('idparasito',$id);
		if($this->db->update('parasito_heces', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_p' => 2
		);
		$this->db->where('idparasito',$id);
		if($this->db->update('parasito_heces', $data)){
			return true;
		}else{
			return false;
		}
	}

}