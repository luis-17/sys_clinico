<?php
class Model_ambiente extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_ambiente_cbo($datos){ 
		$this->db->select('idambiente, numero_ambiente');
		$this->db->from('pa_ambiente');
		$this->db->where('estado_amb', 1); // activo
		$this->db->where('idsede', $datos['idsede']);
		// if( $datos ){
		// 	$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		// }else{
		// 	$this->db->limit(100);
		// }
		$this->db->order_by('numero_ambiente ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ambiente($paramPaginate){ 
		$this->db->select('idambiente, numero_ambiente, piso, comentario, estado_amb, amb.idsede, (se.descripcion) AS descripcion_sede , (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco');
		$this->db->from('pa_ambiente amb');
		$this->db->where('estado_amb <>', 0); // activo			
		$this->db->join('pa_sub_categoria_consul subcat','subcat.idsubcategoriaconsul = amb.idsubcategoriaconsul','left');
		$this->db->join('pa_categoria_consul cat','cat.idcategoriaconsul = subcat.idcategoriaconsul','left');
		$this->db->join('sede se','se.idsede = amb.idsede','left');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);

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

		$this->db->order_by('idambiente','DESC');

		return $this->db->get()->result_array();
	}

	public function m_count_ambiente($paramPaginate = FALSE){ 
		$this->db->select('idambiente, numero_ambiente, piso, comentario, estado_amb, amb.idsede, (se.descripcion) AS descripcion_sede , (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco');
		$this->db->from('pa_ambiente amb');
		$this->db->where('estado_amb <>', 0); // activo			
		$this->db->join('pa_sub_categoria_consul subcat','subcat.idsubcategoriaconsul = amb.idsubcategoriaconsul','left');
		$this->db->join('pa_categoria_consul cat','cat.idcategoriaconsul = subcat.idcategoriaconsul','left');
		$this->db->join('sede se','se.idsede = amb.idsede','left');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);

		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->ilike('CAST('.$key. ' AS TEXT )' ,$value );
				}				
			}
		}

		$this->db->order_by('amb.idambiente','ASC');

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}

	public function m_registrar($datos){
		$data = array(
			'numero_ambiente' => strtoupper($datos['numero_ambiente']),
			'piso' => strtoupper($datos['piso']),
			'comentario' => strtoupper($datos['comentario']),
			'idsede' => $datos['idsede'],
			'idcategoriaconsul' => $datos['categoriaConsul']['id'],
			'idsubcategoriaconsul' => $datos['subCategoriaConsul']['id']	
		);
		return $this->db->insert('pa_ambiente', $data);
	}

	public function m_cambiar_estatus($id, $estatus){
		$data = array(
			'estado_amb' => $estatus
		);
		$this->db->where('idambiente',$id);
		if($this->db->update('pa_ambiente', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_editar($datos){
		$data = array(
			'numero_ambiente' => strtoupper($datos['numero_ambiente']),
			'piso' => strtoupper($datos['piso']),
			'comentario' => strtoupper($datos['comentario']),
			'idcategoriaconsul' => $datos['categoriaConsul']['id'],
			'idsubcategoriaconsul' => $datos['subCategoriaConsul']['id']	
		);

		$this->db->where('idambiente',$datos['id']);
		return $this->db->update('pa_ambiente', $data);
	}

	public function m_cargar_ambiente_por_sede($idsede, $datos){ 
		$this->db->select('amb.idambiente, numero_ambiente, piso, comentario, amb.orden_ambiente, amb.idsede, (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco');
		$this->db->from('pa_ambiente amb');
		$this->db->where('estado_amb ', 1); // activo			
		$this->db->join('pa_sub_categoria_consul subcat','subcat.idsubcategoriaconsul = amb.idsubcategoriaconsul','left');
		$this->db->join('pa_categoria_consul cat','cat.idcategoriaconsul = subcat.idcategoriaconsul','left');
		$this->db->where('amb.idsede', $idsede);
		if(!empty($datos['itemAmbiente']) && $datos['itemAmbiente']['id'] != 0){
			$this->db->where('cat.idcategoriaconsul', $datos['itemAmbiente']['id']); //ver solo un tipo
		}		 
		$this->db->order_by('numero_ambiente','ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_ambiente_por_sede_session(){ 
		$this->db->select('amb.idambiente, numero_ambiente, piso, comentario, amb.orden_ambiente, amb.idsede, (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco');
		$this->db->from('pa_ambiente amb');
		$this->db->where('estado_amb ', 1); // activo			
		$this->db->join('pa_sub_categoria_consul subcat','subcat.idsubcategoriaconsul = amb.idsubcategoriaconsul','left');
		$this->db->join('pa_categoria_consul cat','cat.idcategoriaconsul = subcat.idcategoriaconsul','left');
		$this->db->where('amb.idsede', $this->sessionHospital['idsede'] );
		$this->db->order_by('numero_ambiente','ASC');
		return $this->db->get()->result_array();
	}
}