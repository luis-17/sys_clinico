<?php
class Model_canal extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_canal($paramPaginate){ 
		$this->db->select('idcanal, descripcion_can, estado_can, porcentaje_canal');
		$this->db->from('pa_canal');
		$this->db->where('estado_can', 1); // activo		
		
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->ilike('CAST('.$key. ' AS TEXT )' ,$value );
				}				
			}
		}

		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}

		$this->db->order_by('idcanal','ASC');
		return $this->db->get()->result_array();
	}

	public function m_count_canal(){
		$this->db->select('idcanal, descripcion_can, estado_can');
		$this->db->from('pa_canal');
		$this->db->where('estado_can', 1); // activo	
		
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->ilike('CAST('.$key. ' AS TEXT )' ,$value );
				}				
			}
		}

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}

	public function m_registrar($datos){
		$data = array(
			'descripcion_can' => strtoupper($datos['descripcion']),
			'porcentaje_canal' => $datos['porcentaje']
		);
		return $this->db->insert('pa_canal', $data);
	}

	public function m_editar($datos){
		$data = array(
			'descripcion_can' => strtoupper($datos['descripcion']),
			'porcentaje_canal' => $datos['porcentaje']
		);
		$this->db->where('idcanal',$datos['id']);
		return $this->db->update('pa_canal', $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_can' => 0
		);
		$this->db->where('idcanal',$id);
		if($this->db->update('pa_canal', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_cargar_canal_cbo(){ 
		$this->db->select('idcanal, descripcion_can, estado_can, porcentaje_canal');
		$this->db->from('pa_canal');
		$this->db->where('estado_can', 1); // activo		
		
		$this->db->order_by('idcanal','ASC');
		return $this->db->get()->result_array();
	}
}