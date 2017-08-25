<?php
class Model_categoria_consul extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_categoria_consul($paramPaginate = FALSE){ 
		$this->db->select('idcategoriaconsul, descripcion_cco, estado_cco', FALSE);
		$this->db->from('pa_categoria_consul');
		$this->db->where('estado_cco ', 1);	//activo	
		
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

		$this->db->order_by('idcategoriaconsul','DESC');

		return $this->db->get()->result_array();
	}

	public function m_count_categoria_consul($paramPaginate = FALSE){ 
		$this->db->select('idcategoriaconsul', FALSE);
		$this->db->from('pa_categoria_consul');
		$this->db->where('estado_cco ', 1);	//activo	
		
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

	public function m_editar($datos){
		$data = array(
			'descripcion_cco' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idcategoriaconsul',$datos['id']);
		return $this->db->update('pa_categoria_consul', $data);
	}

	public function m_registrar($datos){
		$data = array(
			'descripcion_cco' => strtoupper($datos['descripcion'])
		);
		return $this->db->insert('pa_categoria_consul', $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_cco' => 0
		);
		$this->db->where('idcategoriaconsul',$id);
		if($this->db->update('pa_categoria_consul', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_cargar_categoria_consul_cbo(){ 
		$this->db->select('idcategoriaconsul, descripcion_cco, estado_cco', FALSE);
		$this->db->from('pa_categoria_consul');
		$this->db->where('estado_cco ', 1);	//activo

		$this->db->order_by('descripcion_cco','ASC');

		return $this->db->get()->result_array();
	}

	//-------------  SUB CATEGORIA -------------------//
	public function m_cargar_subcategoria_consul($paramPaginate, $paramDatos){ 
		$this->db->select('idsubcategoriaconsul, idcategoriaconsul, descripcion_scco, estado_scco', FALSE);
		$this->db->from('pa_sub_categoria_consul');
		$this->db->where('estado_scco ', 1);	//activo
		$this->db->where('idcategoriaconsul ', $paramDatos);	//categoria padre	
		
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
		$this->db->order_by('idsubcategoriaconsul','ASC');
		return $this->db->get()->result_array();
	}

	public function m_count_subcategoria_consul($paramPaginate,$paramDatos){ 
		$this->db->select('idsubcategoriaconsul, idcategoriaconsul, descripcion_scco, estado_scco', FALSE);
		$this->db->from('pa_sub_categoria_consul');
		$this->db->where('estado_scco ', 1);	//activo
		$this->db->where('idcategoriaconsul', $paramDatos);	//categoria padre	
		
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
		$this->db->order_by('idsubcategoriaconsul','ASC');

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}

	public function m_registrar_subcategoria($datos){
		$data = array(
			'descripcion_scco' => strtoupper($datos['descripcion_scco']),
			'idcategoriaconsul' => $datos['idcategoriaconsul']
		);
		return $this->db->insert('pa_sub_categoria_consul', $data);
	}

	public function m_editar_subcategoria_en_grid($datos){
		if($datos['column'] == 'descripcion'){
			$data = array(
				'descripcion_scco' => strtoupper($datos['descripcion']),
			);
		}
		$this->db->where('idsubcategoriaconsul',$datos['id']);
		if($this->db->update('pa_sub_categoria_consul', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_anular_subcategoria ($id)	{
		$data = array(
			'estado_scco' => 0
		);
		$this->db->where('idsubcategoriaconsul',$id);
		if($this->db->update('pa_sub_categoria_consul', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_cargar_subcategoria_consul_cbo($id){ 
		$this->db->select('idsubcategoriaconsul, descripcion_scco, estado_scco', FALSE);
		$this->db->from('pa_sub_categoria_consul');
		$this->db->where('estado_scco ', 1);	//activo
		$this->db->where('idcategoriaconsul ', $id);	
		
		$this->db->order_by('descripcion_scco','ASC');

		return $this->db->get()->result_array();
	}


}