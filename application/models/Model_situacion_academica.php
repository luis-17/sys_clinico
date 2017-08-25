<?php
class Model_situacion_academica extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_situacion_academica($paramPaginate){ 
		$this->db->select('idsituacionacademica, descripcion_sac, estado_sac');
		$this->db->from('pa_situacion_academica');
		$this->db->where('estado_sac', 1); // activo		

		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->ilike('CAST('.$key. ' AS TEXT )' ,$value );
				}				
			}
		}
		$this->db->order_by('idsituacionacademica','DESC');
		return $this->db->get()->result_array();
	}

	public function m_count_situacion_academica(){
		$this->db->select('idsituacionacademica, descripcion_sac, estado_sac');
		$this->db->from('pa_situacion_academica');
		$this->db->where('estado_sac', 1); // activo	
		
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

	public function m_cargar_situacion_academica_cbo($datos = FALSE){ 
		$this->db->select('idsituacionacademica, descripcion_sac, estado_sac');
		$this->db->from('pa_situacion_academica');
		$this->db->where('estado_sac', 1); // activo
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		$this->db->order_by('idsituacionacademica','ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_situacion_academica_por_especialidad($datos){ 
		$this->db->select('idsituacionacademica, descripcion_sac, estado_sac');
		$this->db->from('pa_situacion_academica');
		$this->db->where('estado_sac', 1); // activo
		
		if($datos['idespecialidad'] == 65){ //medicina general
			$this->db->where('idsituacionacademica', 1);
		} else{
			$this->db->where('idsituacionacademica != ', 1);
		}
		$this->db->order_by('idsituacionacademica','ASC');
		return $this->db->get()->result_array();
	}

	public function m_editar($datos){
		$data = array(
			'descripcion_sac' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idsituacionacademica',$datos['id']);
		return $this->db->update('pa_situacion_academica', $data);
	}

	public function m_registrar($datos){
		$data = array(
			'descripcion_sac' => strtoupper($datos['descripcion'])
		);
		return $this->db->insert('pa_situacion_academica', $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_sac' => 0
		);
		$this->db->where('idsituacionacademica',$id);
		if($this->db->update('pa_situacion_academica', $data)){
			return true;
		}else{
			return false;
		}
	}
}