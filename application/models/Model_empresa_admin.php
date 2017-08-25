<?php
class Model_empresa_admin extends CI_Model { 
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_empresas_admin_cbo(){ 
		$this->db->select('idempresaadmin, razon_social, nombre_legal, direccion');
		$this->db->from('empresa_admin');
		$this->db->where('estado_emp <>', 0);
		$this->db->order_by('idempresaadmin', 'DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresas_admin_por_sede_cbo($sede){ 
		$this->db->select('ea.idempresaadmin, ea.razon_social, ea.nombre_legal, ea.direccion');
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea', 'ea.idempresaadmin = sea.idempresaadmin AND sea.idsede = '.$sede['id']);
		$this->db->where('estado_emp <>', 0);
		$this->db->order_by('idempresaadmin', 'DESC');
		return $this->db->get()->result_array();
	}
	/* COMBO MATRIZ PARA ACCESOS A CONSULTAS - FILTROS | SESSION */
	public function m_cargar_sede_empresas_admin_cbo()
	{
		$this->db->select('sea.idsedeempresaadmin, ea.idempresaadmin, s.idsede, razon_social, s.descripcion, ea.ruc');
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('users_por_sede ups','sea.idsedeempresaadmin = ups.idsedeempresaadmin');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$this->db->where('ups.idusers', $this->sessionHospital['idusers']);
		if( $this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_admin' 
			&& $this->sessionHospital['key_group'] != 'key_gerencia' && $this->sessionHospital['key_group'] != 'key_admin_far'){ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}
		return $this->db->get()->result_array();
	}
	
	public function m_cargar_sede_empresa_admin_session($iduser=FALSE)
	{
		$this->db->select('ups.idusersporsede, sea.idsedeempresaadmin, ea.idempresaadmin, s.idsede, ea.razon_social AS empresa_admin, s.descripcion AS sede');
		$this->db->select('ups.estado_ups');
		$this->db->from('users_por_sede ups');
		$this->db->join('sede_empresa_admin sea','ups.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','ups.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$this->db->where('estado_ups <>', 0);
		if( empty($iduser) ){
			$this->db->where('idusers', $this->sessionHospital['idusers']);
		}else{
			$this->db->where('idusers', $iduser);
		}
		
		$this->db->order_by('ea.idempresaadmin', 'DESC');
		return $this->db->get()->result_array();
	}

	/* COMBO MATRIZ ACCESO MULTISEDE */
	public function m_cargar_sede_empresa_admin_matriz_session($iduser=FALSE)
	{
		/*
		LOGICA MULTISEDE:
			1: Sólo verá combo: 
				-sede 
				-empresa tercera.
			2: Solo verá combo: 
				-sede/empresa_admin.
			3: No verá ningún combo.
			4: Sólo verá combo: 
				-sede/empresa_admin
			 	-empresa tercera.
		*/
		// var_dump($this->sessionHospital); exit(); 
		$this->db->distinct(); 
		if( $this->sessionHospital['vista_sede_empresa'] == 1 ){ 
			$this->db->select('s.idsede, s.descripcion AS sede');
			$this->db->order_by('s.descripcion', 'DESC');
		}else{
			$this->db->select('ups.idusersporsede, sea.idsedeempresaadmin, ea.idempresaadmin, s.idsede, ea.razon_social AS empresa_admin, s.descripcion AS sede');
			$this->db->order_by('ea.idempresaadmin', 'DESC');
		}
		$this->db->from('users_por_sede ups');
		$this->db->join('sede_empresa_admin sea','ups.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','ups.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$this->db->where('estado_ups', 1);
		if( empty($iduser) ){
			$this->db->where('idusers', $this->sessionHospital['idusers']);
		}else{
			$this->db->where('idusers', $iduser);
		}
		//QUITAR EMPRESAS SEDES, QUE YA NO LABORAN. 
		if( $this->sessionHospital['key_group'] == 'key_caja' || $this->sessionHospital['key_group'] == 'key_caja_far' /*|| $this->sessionHospital['key_group'] == 'key_salud'*/ ){
			$this->db->where('sea.estado_caja_atencion', 1);
		}
		
		return $this->db->get()->result_array();
	}
	/*---------------------------------------------------------*/
	public function m_cargar_sede_empresas_admin($paramPaginate)
	{
		$this->db->select('sea.idsedeempresaadmin, ea.razon_social, s.descripcion ,sea.idempresaadmin,sea.idsede');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('empresa_admin ea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sede_empresas_admin()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('empresa_admin ea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_sede_empresas_admin_precio($paramPaginate, $datos)
	{
		$this->db->select('sea.idsedeempresaadmin, ea.razon_social, s.descripcion ,sea.idempresaadmin,sea.idsede,
			pps.idproductopreciosede, (pps.precio_sede)::NUMERIC');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('empresa_admin ea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('producto_precio_sede pps','sea.idsedeempresaadmin = pps.idsedeempresaadmin AND estado_pps = 1 AND idproductomaster = '.$datos['id'],'left');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}

	public function m_count_sede_empresas_admin_precio($datos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('empresa_admin ea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('producto_precio_sede pps','sea.idsedeempresaadmin = pps.idsedeempresaadmin AND estado_pps = 1 AND idproductomaster = '.$datos['id'],'left');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	/*---------------------------------------------------------*/

	public function m_cargar_esta_sede_empresa_admin($id)
	{
		$this->db->select('idsedeempresaadmin, ea.idempresaadmin, s.descripcion AS sede,
			cantidad_puntos, (cantidad_soles)::NUMERIC');
		$this->db->select('ea.razon_social, ea.nombre_legal, ea.domicilio_fiscal, ea.direccion, ea.ruc, ea.nombre_logo,
			estado_emp, rs_facebook, rs_twitter, rs_youtube');
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('idsedeempresaadmin', $id);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_sede_empresa_admin_de_esta_caja($id)
	{
		$this->db->select('sea.idsedeempresaadmin, ea.idempresaadmin, razon_social, s.descripcion AS sede,
			cantidad_puntos, (cantidad_soles)::NUMERIC');
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja c','sea.idsedeempresaadmin = c.idsedeempresaadmin AND idcaja = ' . $id);
		// $this->db->where('idcaja', $id);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// ==========================================
	// OBTENER TODAS LAS EMPRESAS ADMIN
	// ==========================================
	public function m_cargar_empresas_admin($paramPaginate){ 
		$this->db->select('idempresaadmin, razon_social, nombre_legal, domicilio_fiscal, direccion, ruc, nombre_logo');
		$this->db->select('cantidad_puntos, (cantidad_soles)::NUMERIC, rs_facebook, rs_twitter, rs_youtube, estado_emp');
		$this->db->from('empresa_admin');
		$this->db->where('estado_emp <>', 0); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_empresas_admin()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa_admin');
		$this->db->where('estado_emp', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_esta_empresa_por_codigo($datos)
	{
		$this->db->select('idempresaadmin, razon_social, nombre_legal, domicilio_fiscal, 
			direccion, ruc, nombre_logo, estado_emp, rs_facebook, rs_twitter, rs_youtube');
		$this->db->from('empresa_admin');
		$this->db->where('idempresaadmin', $datos['id']);
		$this->db->where('estado_emp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_empresa_admin_por_ruc($datos)
	{
		$this->db->select('idempresaadmin, razon_social, nombre_legal, domicilio_fiscal, 
			direccion, ruc, nombre_logo, estado_emp, rs_facebook, rs_twitter, rs_youtube');
		$this->db->from('empresa_admin');
		$this->db->where('ruc', $datos['ruc']);
		$this->db->where('estado_emp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_empresa_admin_por_idempresa($datos)
	{
		$this->db->select('ea.idempresaadmin, ea.razon_social, ea.nombre_legal, ea.domicilio_fiscal, 
			ea.direccion, ea.ruc, ea.nombre_logo, ea.estado_emp, ea.rs_facebook, ea.rs_twitter, ea.rs_youtube');
		$this->db->select('e.descripcion_corta,e.num_cuenta, e.num_cuenta_detraccion');
		$this->db->from('empresa_admin ea');
		$this->db->join('empresa e', 'ea.ruc = e.ruc_empresa');
		$this->db->where('e.idempresa', $datos['idempresa']);
		$this->db->where('ea.estado_emp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	
	// ==========================================
	// CRUD
	// ==========================================
	public function m_editar($datos)
	{
		$data = array(
			'razon_social' => $datos['razon_social'],
			'nombre_legal' => $datos['nombre_legal'],
			'domicilio_fiscal' => $datos['domicilio_fiscal'],
			'direccion' => $datos['direccion'],
			'ruc' => $datos['ruc'],
			'nombre_logo' => $datos['nombre_logo'],
			'rs_facebook' => empty($datos['rs_facebook'])? null : $datos['rs_facebook'],
			'rs_twitter' => empty($datos['rs_twitter'])? null : $datos['rs_twitter'],
			'rs_youtube' => empty($datos['rs_youtube'])? null : $datos['rs_youtube'],
			'cantidad_puntos' => empty($datos['cantidad_puntos'])? null : $datos['cantidad_puntos'],
			'cantidad_soles' => empty($datos['cantidad_soles'])? null : $datos['cantidad_soles']
		);
		$this->db->where('idempresaadmin',$datos['id']);
		return $this->db->update('empresa_admin', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'razon_social' => $datos['razon_social'],
			'nombre_legal' => $datos['nombre_legal'],
			'domicilio_fiscal' => $datos['domicilio_fiscal'],
			'direccion' => $datos['direccion'],
			'ruc' => $datos['ruc'],
			'nombre_logo' => $datos['nombre_logo'],
			'rs_facebook' => empty($datos['rs_facebook'])? null : $datos['rs_facebook'],
			'rs_twitter' => empty($datos['rs_twitter'])? null : $datos['rs_twitter'],
			'rs_youtube' => empty($datos['rs_youtube'])? null : $datos['rs_youtube'],
			'cantidad_puntos' => empty($datos['cantidad_puntos'])? null : $datos['cantidad_puntos'],
			'cantidad_soles' => empty($datos['cantidad_soles'])? null : $datos['cantidad_soles']
		);
		return $this->db->insert('empresa_admin', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_emp' => 0
		);
		$this->db->where('idempresaadmin',$id);
		if($this->db->update('empresa_admin', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_obtener_sede_empresa($datos){
		$this->db->select('idsedeempresaadmin, idempresaadmin, idsede, estado_sea');
		$this->db->from('sede_empresa_admin');
		$this->db->where('idempresaadmin', $datos['idempresaadmin']);
		$this->db->where('idsede', $datos['idsede']);
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_agregar_sede_a_empresa($datos)
	{
		$data = array(
			'idempresaadmin' => $datos['idempresaadmin'],
			'idsede' => $datos['idsede']
		);
		return $this->db->insert('sede_empresa_admin', $data);
	}
	public function m_activar_sede_empresa($id){
		$data = array(
			'estado_sea' => 1,
		);
		$this->db->where('idsedeempresaadmin',$id);
		if($this->db->update('sede_empresa_admin', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_quitar_sede_a_empresa($id)
	{
		$data = array(
			'estado_sea' => 0,
		);
		$this->db->where('idsedeempresaadmin',$id);
		if($this->db->update('sede_empresa_admin', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_cargar_limite_guia_remision($datos){ 
		$this->db->select('limite_items_guia AS limite');
		$this->db->from('empresa_admin');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('idempresaadmin', $datos['idempresaadmin']);
		$this->db->limit(1); 
		$result = $this->db->get()->row_array();
		return $result['limite'];
	}
}