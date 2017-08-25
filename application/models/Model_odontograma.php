<?php
class Model_odontograma extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	
	// ========================================================================================================================
	public function m_cargar_estado_por_zona_por_pieza_por_odontograma($paramPaginate){ 
		$this->db->select('pz.idpiezadental, (pd.descripcion_pd) AS pieza_dental , (zpd.descripcion_zp) AS zona_dental, (epd.descripcion_ep) AS estado_dental, o.idodontograma, o.idatencionmedica'); 
		$this->db->from('odontograma_por_estado ope');

		$this->db->join('odontograma_pieza_zona opz','ope.idodontograma = opz.idodontograma AND ope.idpiezadental = opz.idpiezadental AND ope.idzonapiezadental = opz.idzonapiezadental');
		$this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental');
		$this->db->join('odontograma o','opz.idodontograma = o.idodontograma');

		$this->db->join('pieza_por_zona pz','opz.idpiezadental = pz.idpiezadental AND opz.idzonapiezadental = pz.idzonapiezadental');

		$this->db->join('zona_pieza_dental zpd','pz.idzonapiezadental = zpd.idzonapiezadental');
		$this->db->join('pieza_dental pd','pz.idpiezadental = pd.idpiezadental');
		
		$this->db->where('o.estado_od', 1); // activo
		
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
	public function m_count_estado_por_zona_por_pieza_por_odontograma($paramPaginate)
	{
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('odontograma_por_estado ope');

		$this->db->join('odontograma_pieza_zona opz','ope.idodontograma = opz.idodontograma AND ope.idpiezadental = opz.idpiezadental AND ope.idzonapiezadental = opz.idzonapiezadental');
		$this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental');
		$this->db->join('odontograma o','opz.idodontograma = o.idodontograma');

		$this->db->join('pieza_por_zona pz','opz.idpiezadental = pz.idpiezadental AND opz.idzonapiezadental = pz.idzonapiezadental');

		$this->db->join('zona_pieza_dental zpd','pz.idzonapiezadental = zpd.idzonapiezadental');
		$this->db->join('pieza_dental pd','pz.idpiezadental = pd.idpiezadental');
		
		$this->db->where('o.estado_od', 1); // activo
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$rows = $this->db->get()->num_rows();
		return $rows;
	}
	//============================
	public function m_cargar_todas_las_piezas_de_odontograma($paramPaginate){ 
		$this->db->select('pz.idpiezadental, pz.idzonapiezadental,(descripcion_pd) AS pieza_dental , (descripcion_zp) AS zona_dental'); 
		$this->db->from('pieza_por_zona pz');
		$this->db->join('pieza_dental pd', 'pz.idpiezadental = pd.idpiezadental');
		$this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental');
				
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
	public function m_count_todas_las_piezas_de_odontograma($paramPaginate)
	{
		$this->db->from('pieza_por_zona pz');
		$this->db->join('pieza_dental pd', 'pz.idpiezadental = pd.idpiezadental');
		$this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental'); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$rows = $this->db->get()->num_rows();
		return $rows;
	}
	//=================== PARA UN ODONTOGRAMA VACIO =========
	public function m_cargar_piezas_dentales_con_zonas(){
		
		$this->db->select('pz.idpiezadental, pz.idzonapiezadental,(descripcion_pd) AS pieza_dental , (descripcion_zp) AS zona_dental');
		// $this->db->select('pz.idpiezadental, pz.idzonapiezadental,(descripcion_pd) AS pieza_dental , (descripcion_zp) AS zona_dental, epd.idestadopiezadental, (epd.descripcion_ep) AS estado_dental, ope.tipoestado');
		$this->db->from('pieza_dental pd');
		$this->db->join('pieza_por_zona pz','pd.idpiezadental = pz.idpiezadental');
		$this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental');
		// $this->db->join('odontograma_por_estado ope','pz.idpiezadental = ope.idpiezadental AND pz.idzonapiezadental = ope.idzonapiezadental','left');
		// $this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental','left');
		// $this->db->join('odontograma o','ope.idodontograma = o.idodontograma','left');
		$this->db->order_by('pz.idpiezadental','ASC');
		$this->db->order_by('zpd.orden','ASC');
		// $this->db->distinct();
		return $this->db->get()->result_array();
	}
	//=============== PARA UN ODONTOGRAMA GUARDADO =============
	public function m_cargar_piezas_dentales_con_zonas_con_estados($idodontograma){
		

		$this->db->select('pz.idpiezadental, pz.idzonapiezadental,(descripcion_pd) AS pieza_dental , (descripcion_zp) AS zona_dental, epd.idestadopiezadental, (epd.descripcion_ep) AS estado_dental, epd.imagen, epd.simbolo, ope.tipoestado');
		
		$this->db->from('pieza_dental pd','left');
		$this->db->join('pieza_por_zona pz','pd.idpiezadental = pz.idpiezadental','left');
		$this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental','left');
		$this->db->join('odontograma_por_estado ope','pz.idpiezadental = ope.idpiezadental AND pz.idzonapiezadental = ope.idzonapiezadental','left');
		$this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental','left');
		$this->db->join('odontograma o','ope.idodontograma = o.idodontograma','left');
		$this->db->order_by('pz.idpiezadental','ASC');
		$this->db->order_by('zpd.orden','ASC');
		$this->db->order_by('ope.idestadopiezadental','DESC');
		$this->db->where('ope.idodontograma', $idodontograma);

		return $this->db->get()->result_array();
	}
	//============ COMBO DE ESTADOS ================
	public function m_cargar_estado_pieza_dental_cbo()
	{
		
		$this->db->from('estado_pieza_dental');
		$this->db->where('tipoestado',1);
		$this->db->order_by('idestadopiezadental','ASC');
		
		// $this->db->limit(5);
		return $this->db->get()->result_array();
	}
	//============ COMBO DE MARCADORES DE PROCEDIMIENTOS ================
	public function m_cargar_procedimientos_cbo()
	{
		
		$this->db->from('estado_pieza_dental');
		$this->db->where('tipoestado',2);
		$this->db->order_by('idestadopiezadental','ASC');
		
		// $this->db->limit(5);
		return $this->db->get()->result_array();
	}
	function m_buscar_odontograma_inicial($datos){
		$this->db->from('odontograma');
		$this->db->where('idhistoria',$datos['idhistoria']);
		$this->db->where('tipo',1);
		$this->db->order_by('idodontograma', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->result_array();

	}
	function m_buscar_odontograma_procedimientos($datos){
		$this->db->from('odontograma');
		$this->db->where('idhistoria',$datos['idhistoria']);
		$this->db->where('tipo',2);
		$this->db->order_by('idodontograma', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->result_array();

	}
	function m_registrar($datos){
		$data = array(
			'tipo' => $datos['tipo_odontograma'],
			'numodontograma' => $datos['numodontograma'],
			'idhistoria' => $datos['idhistoria'],
			'idatencionmedica' => $datos['idatencionmedica'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'perdidaspermanentes' => $datos['perdidaspermanentes'],
			'cariespermanentes' => $datos['cariespermanentes'],
			'obturadaspermanentes' => $datos['obturadaspermanentes'],
			'perdidasdeciduas' => $datos['perdidasdeciduas'],
			'cariesdeciduas' => $datos['cariesdeciduas'],
			'obturadasdeciduas' => $datos['obturadasdeciduas'],
			'observaciones' => $datos['observaciones']
			);
		return $this->db->insert('odontograma', $data);
	}

	function m_registrar_odontograma_estado($datos){
		$data = array(
			'idodontograma' => $datos['idodontograma'],
			'idpiezadental' => $datos['idpiezadental'],
			'idzonapiezadental' => $datos['idzonapiezadental'],
			'idestadopiezadental' => $datos['idestadopiezadental'],
			'tipoestado' => $datos['tipoestado']
			);
		return $this->db->insert('odontograma_por_estado', $data);

	}
	function m_editar_odontograma_estado($datos){
		$this->db->where('idodontograma',$datos['idodontograma']);
		return $this->db->delete('odontograma_por_estado');
	}
	function m_actualiza_odontograma($datos){
		$data = array(
			'updatedAt' => date('Y-m-d H:i:s'),
			'perdidaspermanentes' => $datos['perdidaspermanentes'],
			'cariespermanentes' => $datos['cariespermanentes'],
			'obturadaspermanentes' => $datos['obturadaspermanentes'],
			'perdidasdeciduas' => $datos['perdidasdeciduas'],
			'cariesdeciduas' => $datos['cariesdeciduas'],
			'obturadasdeciduas' => $datos['obturadasdeciduas'],
			'observaciones' => $datos['observaciones']
		);	
		$this->db->where('idodontograma',$datos['idodontograma']);
		return $this->db->update('odontograma', $data);
	}
	
}