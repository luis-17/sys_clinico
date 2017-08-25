<?php
class Model_zona extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_zona($paramPaginate){
		//$this->db->select('idexamen, descripcion, estado_ex');
		$this->db->from('zona zo');
		$this->db->join('tipo_zona tzo','zo.idtipozona = tzo.idtipozona', 'left');
		$this->db->where('estado_zo <>', 0);
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
	public function m_count_zona()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('zona');
		$this->db->where('estado_zo <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_cargar_zona_cbo($datos=FALSE)
	{
		$this->db->select('idzona, descripcion_zo');
		$this->db->from('zona');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_zona_por_autocompletado($search)
	{
		$this->db->select('*');
		$this->db->from('zona');
		$this->db->ilike('descripcion_zo', $search);
		$this->db->where('estado_zo', 1);
		$this->db->limit(5);
		return $this->db->get()->result_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'idtipozona' => $datos['idtipozona'],
			//'nombre' => strtoupper($datos['nombre']),
			'descripcion_zo' => $datos['nombre']
		);
		$this->db->where('idzona',$datos['id']);
		return $this->db->update('zona', $data);
	}

	public function m_registrar($datos)
	{
		$data = array(
			'idtipozona' => $datos['idtipozona'],
			//'nombre' => strtoupper($datos['nombre']),
			'descripcion_zo' => strtoupper($datos['nombre']),
			'estado_zo' => 1

		);
		return $this->db->insert('zona', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_zo' => 0
		);
		$this->db->where('idzona',$id);
		if($this->db->update('zona', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_zo' => 1
		);
		$this->db->where('idzona',$id);
		if($this->db->update('zona', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_zo' => 2
		);
		$this->db->where('idzona',$id);
		if($this->db->update('zona', $data)){
			return true;
		}else{
			return false;
		}
	}

}