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
	//============================
	public function m_cargar_piezas_dentales_con_zonas(){
		
		$this->db->select('pz.idpiezadental, pz.idzonapiezadental,(descripcion_pd) AS pieza_dental , (descripcion_zp) AS zona_dental, epd.idestadopiezadental, (epd.descripcion_ep) AS estado_dental, ope.estadopordefecto');
		$this->db->from('pieza_dental pd');
		$this->db->join('pieza_por_zona pz','pd.idpiezadental = pz.idpiezadental');
		$this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental');
		$this->db->join('odontograma_pieza_zona opz','pz.idpiezadental = opz.idpiezadental AND zpd.idzonapiezadental = opz.idzonapiezadental','left');
		$this->db->join('odontograma o','opz.idodontograma = o.idodontograma','left');
		$this->db->join('odontograma_por_estado ope','opz.idodontograma = ope.idodontograma AND opz.idpiezadental = ope.idpiezadental AND opz.idzonapiezadental = ope.idzonapiezadental','left');
		$this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental','left');
		// $this->db->from('pieza_por_zona pz');
		// $this->db->join('pieza_dental pd', 'pz.idpiezadental = pd.idpiezadental');
		// $this->db->join('zona_pieza_dental zpd', 'pz.idzonapiezadental = zpd.idzonapiezadental');

		return $this->db->get()->result_array();
	}
	//===============PARA UN ODONTOGRAMA GUARDADO=============
	public function m_cargar_piezas_dentales_con_zonas_con_estados($datos){
		

		$this->db->select('o.idodontograma, o.idatencionmedica, (o.tipo) AS tipo_odontograma, pz.idpiezadental, pz.idzonapiezadental, (pd.descripcion_pd) AS pieza_dental , (zpd.descripcion_zp) AS zona_dental, epd.idestadopiezadental, (epd.descripcion_ep) AS estado_dental, ope.estadopordefecto'); 
		
		$this->db->from('odontograma_por_estado ope');
		$this->db->join('odontograma_pieza_zona opz','ope.idodontograma = opz.idodontograma AND ope.idpiezadental = opz.idpiezadental AND ope.idzonapiezadental = opz.idzonapiezadental');
		$this->db->join('estado_pieza_dental epd','ope.idestadopiezadental = epd.idestadopiezadental');
		$this->db->join('odontograma o','opz.idodontograma = o.idodontograma');

		$this->db->join('pieza_por_zona pz','opz.idpiezadental = pz.idpiezadental AND opz.idzonapiezadental = pz.idzonapiezadental');

		$this->db->join('zona_pieza_dental zpd','pz.idzonapiezadental = zpd.idzonapiezadental');
		$this->db->join('pieza_dental pd','pz.idpiezadental = pd.idpiezadental');
		$this->db->where('o.idodontograma',$datos['idodontograma']);

		return $this->db->get()->result_array();
	}
	//============================
	public function m_cargar_estado_pieza_dental_cbo()
	{
		
		$this->db->from('estado_pieza_dental');
		$this->db->order_by('idestadopiezadental','ASC');
		// if( $datos ){ 
		// 	$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		// }else{ 
		// 	$this->db->limit(100);
		// }
		return $this->db->get()->result_array();
	}
	
}