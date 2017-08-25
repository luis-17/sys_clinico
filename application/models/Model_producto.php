<?php
class Model_producto extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_productos($paramPaginate=FALSE,$paramDatos){
		if(empty($this->sessionHospital['id_empresa_admin'])){
			$idsedeempresaadmin = 	$this->sessionHospital['idsedeempresaadmin'];
		}else{
			if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
				$idsedeempresaadmin = 9;
			}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
				$idsedeempresaadmin = 8;
			}
		}
		$this->db->select('pm.idproductomaster, pps.idproductopreciosede, (pm.descripcion) AS producto , pps.precio_sede AS precio, estado_pps, esp.idespecialidad, (pps.precio_sede)::NUMERIC AS precio_sf,
			(esp.nombre) AS especialidad, tp.idtipoproducto, tp.nombre_tp',FALSE); 
		$this->db->from('producto_master pm');
		if($paramDatos){
			$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND estado_pps IN(1,2) AND pps.idsedeempresaadmin = '.$paramDatos['sedeempresa']);
		}else{
			$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND estado_pps IN(1,2) AND pps.idsedeempresaadmin = '.$idsedeempresaadmin);
		}
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto', 'left');
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('pm.solo_para_campania', 2); // 2 -- no es producto de campaña
		//$this->db->where('estado_pm', 1);
		//$this->db->where_in('estado_pps', array(1,2) ); // habilitado o deshabilitado 
		if($paramPaginate){
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
		}else{
			$this->db->order_by('pm.idproductomaster','ASC');
		}
		return $this->db->get()->result_array();
	}
	public function m_count_productos($paramPaginate,$paramDatos){
		if(empty($this->sessionHospital['id_empresa_admin'])){
			$idsedeempresaadmin = 	$this->sessionHospital['idsedeempresaadmin'];
		}else{
			if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
				$idsedeempresaadmin = 9;
			}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
				$idsedeempresaadmin = 8;
			}
		}
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('producto_master pm');
		if($paramDatos){
			$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND estado_pps IN(1,2) AND pps.idsedeempresaadmin = '.$paramDatos['sedeempresa']);
		}else{
			$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND estado_pps IN(1,2) AND pps.idsedeempresaadmin = ' . $idsedeempresaadmin);
		}
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto', 'left');
		$this->db->where('esp.estado', 1); // habilitado 
		$this->db->where('pm.solo_para_campania', 2); // 2 -- no es producto de campaña 		
		//$this->db->where('estado_pm', 1);
		//$this->db->where_in('estado_pps', array(1,2) ); // habilitado o deshabilitado

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		} 

		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_productos_indicadores()
	{
		// $this->db->select('pm.idproductomaster, precio, pm.descripcion, e.idespecialidad, e.nombre, (precio::numeric) AS precio_sf'); 
		// $this->db->from('producto_master pm');
		// $this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		// $this->db->where('estado_pm', 1); // solo habilitado 
		// $this->db->where('envia_orden', 1); // para orden enviado por medico / sirve para indicadores  
		// $this->db->where('si_indicador_obstetricia', 1); //
		// // $this->db->ilike('pm.descripcion', $datos['search']); 
		// $this->db->order_by('pm.descripcion','ASC'); 
		// return $this->db->get()->result_array(); 

		$this->db->select('DISTINCT pi.str_indicador, key_indicador', FALSE); 
		$this->db->from('est_producto_indicador pi');
		//$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->where('estado_ind', 1); // solo habilitado //
		$this->db->order_by('pi.str_indicador','ASC'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_de_session($datos){ 
		$this->db->select('pm.idproductomaster, pps.precio_sede AS precio, pm.descripcion, e.idespecialidad, e.nombre, (pps.precio_sede::NUMERIC) AS precio_sf, 
			pps.edicion_precio_en_venta, tp.nombre_tp, tp.idtipoproducto, pps.costo_producto::NUMERIC',FALSE); 
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where('pm.solo_para_campania', 2); // 2-- no es producto de campaña		
		$this->db->where('pps.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if( $datos ){ 
			$this->db->where('e.idespecialidad', $datos['especialidadId']);
			$this->db->ilike('pm.descripcion', $datos['search']);
		}else{
			$this->db->limit(5);
		} 
		$this->db->order_by('LENGTH(pm.descripcion)','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_de_sede_empresa_admin($datos){ 
		$this->db->select('pm.idproductomaster, pps.precio_sede AS precio, pm.descripcion, e.idespecialidad, e.nombre, (pps.precio_sede::numeric) AS precio_sf, pps.edicion_precio_en_venta'); 
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where('pm.solo_para_campania', 2); // 2-- no es producto de campaña		
		$this->db->where('pps.idsedeempresaadmin', $datos['idsedeempresaadmin']);
		if( $datos ){ 
			$this->db->where('e.idespecialidad', $datos['especialidadId']);
			$this->db->ilike('pm.descripcion', $datos['search']);
		}else{
			$this->db->limit(5);
		} 
		$this->db->order_by('LENGTH(pm.descripcion)','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_de_sede_empresa_admin_campania($datos){ 
		$this->db->select('pm.idproductomaster, pps.precio_sede AS precio, pm.descripcion, e.idespecialidad, e.nombre, (pps.precio_sede::numeric) AS precio_sf, pps.edicion_precio_en_venta'); 
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where_in('pm.solo_para_campania', array(1,2)); // 2-- no es producto de campaña		
		$this->db->where('pps.idsedeempresaadmin', $datos['idsedeempresaadmin']);
		if( $datos ){ 
			$this->db->where('e.idespecialidad', $datos['especialidadId']);
			$this->db->ilike('pm.descripcion', $datos['search']);
		}else{
			$this->db->limit(5);
		} 
		$this->db->order_by('LENGTH(pm.descripcion)','ASC');
		return $this->db->get()->result_array();
	}	
	public function m_cargar_productos_de_sede_empresa_admin_campania_id($datos){ 
		$this->db->select('pm.idproductomaster, pps.precio_sede AS precio, pm.descripcion, e.idespecialidad, e.nombre, (pps.precio_sede::numeric) AS precio_sf, pps.edicion_precio_en_venta'); 
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where_in('pm.solo_para_campania', array(1,2)); // 2-- no es producto de campaña		
		$this->db->where('pm.idproductomaster', $datos);
		//$this->db->order_by('LENGTH(pm.descripcion)','ASC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}	
	public function m_cargar_productos_convenio($datos){
		$this->db->select('pm.idproductomaster, cps.precio_variable AS precio, pm.descripcion, e.idespecialidad, e.nombre, (cps.precio_variable::NUMERIC) AS precio_sf,
			pps.edicion_precio_en_venta, tp.nombre_tp, tp.idtipoproducto,cps.estado_cps, pps.costo_producto::NUMERIC', FALSE); 
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster'); 
		$this->db->join('convenio_producto_sede cps','pps.idproductopreciosede = cps.idproductopreciosede'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where('estado_cps <>', 0); // todos menos anulados
		$this->db->where('pm.solo_para_campania', 2); // 2-- no es producto de campaña
		//$this->db->where('pps.idsedeempresaadmin', 8);
		//$this->db->where('pm.convenio', 1);
		if( $datos ){ 
			$this->db->where('e.idespecialidad', $datos['especialidadId']);
			$this->db->where('cps.idtipocliente', $datos['idtipocliente']);
			$this->db->ilike('pm.descripcion', $datos['search']);
		}else{
			$this->db->limit(5);
		} 
		$this->db->order_by('LENGTH(pm.descripcion)','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_cbo($datos = FALSE){ 
		// $this->db->distinct();
		$this->db->select('descripcion');
		$this->db->from('producto_master');
		$this->db->where('estado_pm <>', 0); // habilitado o deshabilitado 
		$this->db->where('solo_para_campania', 2); // 2-- no es producto de campaña 		
		if( $datos ){
			$this->db->ilike($datos['nameColumn'], $datos['search']);
		}else{
			$this->db->limit(10);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_cbo_campania($datos = FALSE){ 
		// $this->db->distinct();
		$this->db->select('descripcion');
		$this->db->from('producto_master');
		$this->db->where('estado_pm <>', 0); // habilitado o deshabilitado 
		$this->db->where_in('solo_para_campania', array(1,2)); // 2-- no es producto de campaña 		
		if( $datos ){
			$this->db->ilike($datos['nameColumn'], $datos['search']);
			$this->db->limit(10);
		}else{
			$this->db->limit(10);
		}
		return $this->db->get()->result_array();
	}	
	public function m_cargar_productos_generales($datos = FALSE){
		$this->db->select('descripcion');
		$this->db->from('producto_master');
		// $this->db->join
		$this->db->where('estado_pm <>', 0); // habilitado o deshabilitado 
		$this->db->where('solo_para_campania', 2); // 2 -- no es producto de campaña 		
	}
	public function m_cargar_productos_salud_ocup_cbo($datos=FALSE)
	{
		$this->db->select('pm.idproductomaster, pm.descripcion');
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster');
		$this->db->where('estado_pps', 1); // solo habilitado
		$this->db->where('pps.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		// $this->db->where('estado_pm', 1); // habilitado 
		$this->db->where('pm.idtipoproducto', 17); // SALUD OCUPACIONAL
		$this->db->where('pm.solo_para_campania', 2); // 2-- no es producto de campaña		  
		return $this->db->get()->result_array();
	}
	

	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['producto']),
			//'precio' => $datos['precio'],
			'idtipoproducto' => $datos['idtipoproducto'],
			'idespecialidad' => $datos['idespecialidad'],
			'solo_para_campania' => (@$datos['solo_para_campania'] == true ? 1:2) ,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idproductomaster',$datos['id']);
		return $this->db->update('producto_master', $data);
	}

	public function m_registrar_master($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['producto']), 
			//'precio' => $datos['precio'], 
			'idtipoproducto' => $datos['idtipoproducto'], 
			'idespecialidad' => $datos['idespecialidad'], 
			'solo_para_campania' => (@$datos['solo_para_campania'] == true ? 1:2) ,
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('producto_master', $data);
	}
	public function m_registrar_producto_precio_sede($datos)
	{
		$data = array(
			'idproductomaster' => $datos['idproductomaster'],
			'idsedeempresaadmin' => $datos['idsedeempresaadmin'],
			'precio_sede' => $datos['precio_sede'], 
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('producto_precio_sede', $data);
	}
	public function m_editar_producto_precio_sede($datos)
	{	
		$data = array(
			'precio_sede' => $datos['precio_sede'], 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idproductopreciosede',$datos['idproductopreciosede']);
		return $this->db->update('producto_precio_sede', $data);
	}
	// ================================ HISTORIAL DE PRECIOS ==========================================
	public function m_cargar_historial_precios($paramDatos){
		$this->db->select('pps.precio_sede, hp.precio_venta_anterior, hp.precio_venta_actual, hp.fecha_cambio');
		$this->db->select('u.idusers, e.idempleado, e.nombres, e.apellido_paterno, e.apellido_materno');
		$this->db->from('producto_precio_sede pps');
		$this->db->join('ho_historial_precio hp','pps.idproductopreciosede = hp.idproductopreciosede','left');

		$this->db->join('users u','hp.iduser = u.idusers','left');
		$this->db->join('rh_empleado e','hp.idempleado = e.idempleado','left');
		
		$this->db->where('pps.idproductomaster', $paramDatos['id']);
		$this->db->where('hp.idproductopreciosede', $paramDatos['idproductopreciosede']);
		$this->db->where('estado_pps', 1);
		$this->db->order_by('fecha_cambio', 'DESC');
		return $this->db->get()->result_array();
	}
	public function m_registrar_historial_precio($datos)
	{
		$data = array(
			'idproductopreciosede' => $datos['idproductopreciosede'],
			'precio_venta_anterior' => $datos['precio_venta_anterior'],
			'precio_venta_actual' => $datos['precio_venta'],
			'fecha_cambio' => date('Y-m-d H:i:s'), 
			'iduser' => $this->sessionHospital['idusers'],
			'idempleado' => $this->sessionHospital['idempleado']
		);
		return $this->db->insert('ho_historial_precio', $data);
	}
	public function m_listar_precio_sede($idproductopreciosede){
		$this->db->select('(precio_sede::NUMERIC)');
		$this->db->from('producto_precio_sede');
		$this->db->where('idproductopreciosede', $idproductopreciosede);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_habilitar($datos)
	{
		$data = array(
			'estado_pps' => 1
		);

		$this->db->where('idproductopreciosede',$datos['idproductopreciosede']);
		//$this->db->where('idsedeempresaadmin',$datos['idsedeempresaadmin']);
		if($this->db->update('producto_precio_sede', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($datos)
	{
		$data = array(
			'estado_pps' => 2
		);

		$this->db->where('idproductopreciosede',$datos['idproductopreciosede']);
		//$this->db->where('idsedeempresaadmin',$datos['idsedeempresaadmin']);
		if($this->db->update('producto_precio_sede', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular($id)
	{
		$data = array( 
			'estado_pps' => 0 
		);
		$this->db->where('idproductomaster',$id);
		if($this->db->update('producto_precio_sede', $data)){
			return true;
		}else{
			return false;
		}
	}
	function m_verificar($producto,$idespecialidad){
		$this->db->where('descripcion',$producto);
		$this->db->where('idespecialidad',$idespecialidad);
		$row = $this->db->count_all_results('producto_master');
		if($row > 0){
			return true;
		}else{
			return false;
		}
	}
}