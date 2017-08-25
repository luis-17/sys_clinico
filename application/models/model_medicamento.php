<?php
class Model_medicamento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_medicamento($paramPaginate,$paramDatos)
	{ 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento",FALSE);  
		$this->db->select('idmedicamento, m.idtipoproducto, estado_med, generico, idunidadmedida, val_concentracion, registro_sanitario, excluye_igv, contenido, 
			lab.idlaboratorio, nombre_lab, ff.idformafarmaceutica, descripcion_ff, codigo_barra, 
			mc.idmedidaconcentracion, mc.descripcion_mc, mc.abreviatura_mc, pr.idpresentacion, 
			cv.idcondicionventa, descripcion_cv, va.idviaadministracion, descripcion_va, ff.idformafarmaceutica, descripcion_ff 
		');
		$this->db->from('medicamento m');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->join('far_medida_concentracion mc','m.idmedidaconcentracion = mc.idmedidaconcentracion','left'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left'); 
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa','left'); 
		$this->db->join('far_via_administracion va','m.idviaadministracion = va.idviaadministracion','left'); 
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->where('estado_med <>', 0);
		$this->db->where('m.idmedicamento <>', 0);
		if( !empty($paramDatos['generico']) && $paramDatos['generico'] != 'all' ){ 
			$this->db->where('generico', $paramDatos['generico']);
		}
		//var_dump($paramDatos['busquedaTipoProducto']['id']);exit();
		if ($paramDatos['busquedaTipoProducto']['id'] != '0'){
			//var_dump("llegué");exit();
			$this->db->where('m.idtipoproducto', $paramDatos['busquedaTipoProducto']['id']);	
		}
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
	public function m_count_medicamento($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento m');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->join('far_medida_concentracion mc','m.idmedidaconcentracion = mc.idmedidaconcentracion','left'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left'); 
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa','left'); 
		$this->db->join('far_via_administracion va','m.idviaadministracion = va.idviaadministracion','left'); 
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->where('estado_med <>', 0);
		$this->db->where('m.idmedicamento <>', 0);
		if( !empty($paramDatos['generico']) && $paramDatos['generico'] != 'all' ){ 
			$this->db->where('generico', $paramDatos['generico']);
		}
		if ($paramDatos['busquedaTipoProducto']['id'] != '0'){
			$this->db->where('m.idtipoproducto', $paramDatos['busquedaTipoProducto']['id']);	
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		//$this->db->where('activo', 1);
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamentos_sin_este_almacen($paramPaginate,$paramDatos)
	{ 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('m.idmedicamento, estado_med, generico, idunidadmedida, val_concentracion, codigo_barra,
			lab.idlaboratorio, nombre_lab, mc.idmedidaconcentracion, mc.descripcion_mc, mc.abreviatura_mc, pr.idpresentacion
		');
		$this->db->from('medicamento m');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->join('far_medida_concentracion mc','m.idmedidaconcentracion = mc.idmedidaconcentracion','left'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');  
		$this->db->where('m.idmedicamento NOT IN 
			(SELECT idmedicamento FROM far_medicamento_almacen WHERE idsubalmacen = '.$this->db->escape($paramDatos['idsubalmacen']).' AND idalmacen = '.$this->db->escape($paramDatos['idalmacen']).' AND estado_fma = 1)');
		$this->db->where('estado_med', 1);
		$this->db->where('m.idmedicamento <>', 0);
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
	public function m_count_medicamentos_sin_este_almacen($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento m');
		$this->db->where('m.idmedicamento NOT IN 
			(SELECT idmedicamento FROM far_medicamento_almacen WHERE idsubalmacen = '.$this->db->escape($paramDatos['idsubalmacen']).' AND idalmacen = '.$this->db->escape($paramDatos['idalmacen']).' AND estado_fma = 1)');
		$this->db->where('estado_med', 1);
		$this->db->where('idmedicamento <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		//$this->db->where('activo', 1);
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamento_autocomplete_medico($searchColumn, $searchText, $idsede = FALSE ) 
	{
		// STOCK
		$this->db->select('SUM (sub_fma1.stock_actual_malm)');
		$this->db->from('medicamento sub_m1');
		$this->db->join('far_medicamento_almacen sub_fma1', 'sub_m1.idmedicamento = sub_fma1.idmedicamento');
		$this->db->join('far_almacen sub_fa1', 'sub_fma1.idalmacen = sub_fa1.idalmacen AND sub_fa1.estado_alm = 1 AND sub_fa1.stock_consultorio = 1');
		$this->db->join('far_subalmacen sub_fsa1', 'sub_fma1.idsubalmacen = sub_fsa1.idsubalmacen AND (sub_fsa1.idtiposubalmacen IN(1,2)'); // solo farmacia y central 
		if( !empty($idsede) ){
			$this->db->join("sede_empresa_admin sub_sea1", "sub_fa1.idsedeempresaadmin = sub_sea1.idsedeempresaadmin AND sub_sea1.idsede = ". $idsede,FALSE);
		}
		$this->db->where('sub_m1.estado_med', 1);
		$this->db->where('sub_m1.idmedicamento = m.idmedicamento');
		$stock = $this->db->get_compiled_select();
		$this->db->reset_query();

		// PRINCIPIOS ACTIVOS
		$this->db->select("STRING_AGG (	sub_fpa2.descripcion,'; 'ORDER BY sub_fpa2.descripcion )",FALSE);
		$this->db->from('medicamento sub_m2');
		$this->db->join('far_medicamento_principio sub_fmp2','sub_fmp2.idmedicamento = sub_m2.idmedicamento AND sub_fmp2.estado_mp = 1');
		$this->db->join('far_principio_activo sub_fpa2','sub_fpa2.idprincipioactivo = sub_fmp2.idprincipioactivo AND sub_fpa2.estado_pa = 1');
		$this->db->where('sub_m2.estado_med', 1);	
		$this->db->where('sub_m2.idmedicamento = m.idmedicamento');
		$principios = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select("(CASE WHEN generico = 1 THEN (m.denominacion || ' ' || m.descripcion) ELSE m.denominacion END) AS medicamento",FALSE);
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(".$principios.") AS principios,",FALSE); 
		$this->db->select('m.idmedicamento, m.idtipoproducto, m.contenido, m.excluye_igv, ff.descripcion_ff, ff.acepta_caja_unidad, codigo_barra');
		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab'){
			$this->db->select('('.$stock.') AS stock', FALSE);
		}	
		$this->db->from('medicamento m');
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left');
		$this->db->join('far_medicamento_principio fmp','fmp.idmedicamento = m.idmedicamento AND fmp.estado_mp = 1');
		$this->db->join('far_principio_activo fpa','fpa.idprincipioactivo = fmp.idprincipioactivo AND fpa.estado_pa = 1');
		$this->db->where('estado_med', 1);
		$this->db->where('m.idmedicamento <>', 0);

		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){
			$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento');
			$this->db->join('far_almacen fa', 'fma.idalmacen = fa.idalmacen AND fa.estado_alm = 1 AND fa.stock_consultorio = 1');
			$this->db->where("(".$searchColumn." ILIKE '%".$searchText."%' ESCAPE '!' OR UPPER (fpa.descripcion) ILIKE '%".$searchText."%' ESCAPE '!' )");
			$this->db->group_by('medicamento, presentacion,	m.idmedicamento, m.idtipoproducto,	m.contenido, m.excluye_igv,	ff.descripcion_ff,	ff.acepta_caja_unidad,	m.codigo_barra');
			$this->db->having("MAX (fma.stock_actual_malm) > 0");
			$this->db->order_by('stock','DESC');
		}else{
			$this->db->ilike($searchColumn, $searchText);
		}
		
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamento_autocomplete_farmacia($searchColumn, $searchText, $idsede = FALSE ) 
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE);
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select('m.idmedicamento, idtipoproducto, contenido, excluye_igv, ff.descripcion_ff, ff.acepta_caja_unidad, codigo_barra');
		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab'){
			$this->db->select('SUM(fma.stock_actual_malm) AS stock', FALSE);
		}	
		$this->db->from('medicamento m');
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left');
		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){
			$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento');
			$this->db->join('far_almacen fa', 'fma.idalmacen = fa.idalmacen AND fa.estado_alm = 1 AND fa.stock_consultorio = 1');
			$this->db->join('far_subalmacen fsa', 'fma.idsubalmacen = fsa.idsubalmacen AND (idtiposubalmacen IN(1,2)'); // solo farmacia y central 
			if( !empty($idsede) ){
				$this->db->join('sede_empresa_admin sea', 'fa.idsedeempresaadmin = sea.idsedeempresaadmin AND sea.idsede = '. $idsede);
			}
			
			// $this->db->join("empresa_admin ea", "sea.idempresaadmin = ea.idempresaadmin AND ea.ruc = '".$this->sessionHospital['ruc_empresa_admin']."'");
			$this->db->group_by('medicamento, presentacion,	"m"."idmedicamento", "idtipoproducto",	"contenido",	"excluye_igv",	"ff"."descripcion_ff",	"ff"."acepta_caja_unidad",	"codigo_barra"');
			$this->db->having('SUM(fma.stock_actual_malm) > 0 ');
			$this->db->order_by('stock','DESC');
		}
		$this->db->where('estado_med', 1);
		$this->db->where('m.idmedicamento <>', 0);
		$this->db->ilike($searchColumn, $searchText);
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamento_por_codigo($id) 
	{
		$this->db->select('m.denominacion AS medicamento');
		$this->db->select('pr.descripcion_pres AS presentacion',FALSE); 
		$this->db->select('m.idmedicamento, idtipoproducto, contenido, excluye_igv, ff.descripcion_ff, ff.acepta_caja_unidad, codigo_barra');
		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab'){
			$this->db->select('SUM(fma.stock_actual_malm) AS stock', FALSE);
		}	
		$this->db->from('medicamento m');
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left');
		if( $this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud' 
			|| $this->sessionHospital['key_group'] === 'key_coord_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){
			$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento');
			$this->db->join('far_almacen fa', 'fma.idalmacen = fa.idalmacen AND fa.estado_alm = 1 AND fa.stock_consultorio = 1');
			$this->db->join('far_subalmacen fsa', 'fma.idsubalmacen = fsa.idsubalmacen AND (idtiposubalmacen IN(1,2)'); // solo farmacia y central
			$this->db->join('sede_empresa_admin sea', 'fa.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join("empresa_admin ea", "sea.idempresaadmin = ea.idempresaadmin AND ea.ruc = '".$this->sessionHospital['ruc_empresa_admin']."'");
			$this->db->group_by('medicamento, presentacion,	"m"."idmedicamento", "idtipoproducto",	"contenido",	"excluye_igv",	"ff"."descripcion_ff",	"ff"."acepta_caja_unidad",	"codigo_barra"');
			$this->db->having('('.$stock.') > 0 ');
			$this->db->order_by('stock','DESC');
		}
		$this->db->where('estado_med', 1);
		$this->db->where('m.idmedicamento', $id);
		$this->db->where('m.idmedicamento <>', 0);
		// $this->db->ilike($searchColumn, $searchText);
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_solo_medicamento_autocomplete_medico($searchColumn, $searchText)
	{ 
		// PRINCIPIOS ACTIVOS
		$this->db->select("STRING_AGG (	sub_fpa2.descripcion,'; 'ORDER BY sub_fpa2.descripcion )",FALSE);
		$this->db->from('medicamento sub_m2');
		$this->db->join('far_medicamento_principio sub_fmp2','sub_fmp2.idmedicamento = sub_m2.idmedicamento AND sub_fmp2.estado_mp = 1');
		$this->db->join('far_principio_activo sub_fpa2','sub_fpa2.idprincipioactivo = sub_fmp2.idprincipioactivo AND sub_fpa2.estado_pa = 1');
		$this->db->where('sub_m2.estado_med', 1);	
		$this->db->where('sub_m2.idmedicamento = m.idmedicamento');
		$principios = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) ELSE denominacion END) AS medicamento",FALSE);
		$this->db->select("(".$principios.") AS principios,",FALSE); 
		$this->db->select('m.idmedicamento, m.idtipoproducto, estado_med, generico, idunidadmedida, val_concentracion, registro_sanitario, excluye_igv, contenido, 
			lab.idlaboratorio, nombre_lab, ff.idformafarmaceutica, descripcion_ff, codigo_barra, 
			mc.idmedidaconcentracion, mc.descripcion_mc, mc.abreviatura_mc, pr.idpresentacion, 
			cv.idcondicionventa, descripcion_cv, va.idviaadministracion, descripcion_va, ff.idformafarmaceutica, descripcion_ff 
		');
		$this->db->from('medicamento m');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->join('far_medida_concentracion mc','m.idmedidaconcentracion = mc.idmedidaconcentracion','left'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left'); 
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa','left'); 
		$this->db->join('far_via_administracion va','m.idviaadministracion = va.idviaadministracion','left'); 
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 		
		$this->db->join('far_medicamento_principio fmp','fmp.idmedicamento = m.idmedicamento AND fmp.estado_mp = 1');
		$this->db->join('far_principio_activo fpa','fpa.idprincipioactivo = fmp.idprincipioactivo AND fpa.estado_pa = 1');
		$this->db->where('estado_med <>', 0);
		$this->db->where('m.idmedicamento <>', 0);
		$this->db->group_by('medicamento, presentacion,	m.idmedicamento, lab.idlaboratorio, ff.idformafarmaceutica, mc.idmedidaconcentracion, pr.idpresentacion, cv.idcondicionventa, va.idviaadministracion');
		//$this->db->ilike($searchColumn, $searchText);
		$this->db->where("(".$searchColumn." ILIKE '%".$searchText."%' ESCAPE '!' OR UPPER (fpa.descripcion) ILIKE '%".$searchText."%' ESCAPE '!')");
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_solo_medicamento_autocomplete($searchColumn, $searchText)
	{ 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento",FALSE);  
		$this->db->select('idmedicamento, m.idtipoproducto, estado_med, generico, idunidadmedida, val_concentracion, registro_sanitario, excluye_igv, contenido, 
			lab.idlaboratorio, nombre_lab, ff.idformafarmaceutica, descripcion_ff, codigo_barra, 
			mc.idmedidaconcentracion, mc.descripcion_mc, mc.abreviatura_mc, pr.idpresentacion, 
			cv.idcondicionventa, descripcion_cv, va.idviaadministracion, descripcion_va, ff.idformafarmaceutica, descripcion_ff 
		');
		$this->db->from('medicamento m');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->join('far_medida_concentracion mc','m.idmedidaconcentracion = mc.idmedidaconcentracion','left'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left'); 
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa','left'); 
		$this->db->join('far_via_administracion va','m.idviaadministracion = va.idviaadministracion','left'); 
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->where('estado_med <>', 0);
		$this->db->where('m.idmedicamento <>', 0);
		$this->db->ilike($searchColumn, $searchText);
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_medida_cbo($datos=FALSE)
	{
		$this->db->select('idunidadmedida, descripcion_um, abreviatura_um');
		$this->db->from('unidad_medida');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamento_por_columna($datos)
	{
		$this->db->select('idmedicamento, denominacion');
		$this->db->from('medicamento');
		$this->db->where('estado_med', 1); 
		$this->db->where($datos['searchColumn'], $datos['searchText']); 
		
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_editar($datos)
	{
		//var_dump($datos);
		$data = array(
			'denominacion' => $datos['medicamento'],
			'registro_sanitario' => empty($datos['registro_sanitario']) ? NULL:$datos['registro_sanitario'],
			'val_concentracion' => empty($datos['val_concentracion']) ? NULL:$datos['val_concentracion'],
			'contenido' => $datos['contenido'],
			'idlaboratorio' => empty($datos['idlaboratorio']) ? NULL:$datos['idlaboratorio'],
			'idmedidaconcentracion' => empty($datos['idmedidaconcentracion'])? NULL:$datos['idmedidaconcentracion'],
			'idviaadministracion' => $datos['idviaadministracion'],
			'idformafarmaceutica' => $datos['idformafarmaceutica'],
			'idcondicionventa' => $datos['idcondicionventa'],
			'codigo_barra' => $datos['codigo_barra'],
			'excluye_igv' => ($datos['excluyeigv'] == true ? 1:2),
			'generico' => $datos['generico'],
			'idtipoproducto' => $datos['idtipoproducto'],
			'updatedAt' => date('Y-m-d H:i:s')
			
		);
		if( $datos['generico'] == 1){
			$data['idunidadmedida'] = strtoupper($datos['idpresentacion']);
		}else{
			$data['idpresentacion'] = $datos['idpresentacion'];
		}
		$this->db->where('idmedicamento',$datos['id']);
		return $this->db->update('medicamento', $data);
	}
	public function m_registrar($datos)
	{		
			$data = array(
			'denominacion' => strtoupper($datos['medicamento']),
			'registro_sanitario' => empty($datos['registro_sanitario']) ? NULL:$datos['registro_sanitario'],
			'val_concentracion' => empty($datos['val_concentracion']) ? NULL:$datos['val_concentracion'],
			'contenido' => empty($datos['contenido']) ? NULL:$datos['contenido'],
			'idlaboratorio' => empty($datos['laboratorio']['id']) ? NULL:$datos['laboratorio']['id'],
			'idmedidaconcentracion' => empty($datos['idmedidaconcentracion']) ? NULL:$datos['idmedidaconcentracion'],
			'idviaadministracion' => empty($datos['idviaadministracion']) ? NULL:$datos['idviaadministracion'],
			'idformafarmaceutica' => empty($datos['idformafarmaceutica']) ? NULL:$datos['idformafarmaceutica'],
			'idcondicionventa' => empty($datos['idcondicionventa']) ? NULL:$datos['idcondicionventa'],
			'creado_en_solicitud' => empty($datos['creado_en_solicitud']) ? 2 : $datos['creado_en_solicitud'],
			'idusers_creacion' => $this->sessionHospital['idusers'],
			//'generico' => $datos['generico'],
			'codigo_barra' => @$datos['codigo_barra'],
			'excluye_igv' => (@$datos['excluyeigv'] == true ? 1:2),
			'idtipoproducto' => $datos['idtipoproducto'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'idformula_jj' => $datos['idformula_jj'],
			'fecha_asigna_idformula_jj' => $datos['fecha_asigna_idformula_jj'],
			);
		
		// if( $datos['generico'] == 1){
		// 	$data['idunidadmedida'] = strtoupper($datos['idpresentacion']); 
		// }else{
			$data['idpresentacion'] = empty($datos['idpresentacion']) ? NULL:$datos['idpresentacion']; 
		// }
		
		return $this->db->insert('medicamento', $data);
	}
	public function m_registrar_medicamento_en_almacen($datos)
	{
		$data = array(
			'idmedicamento' => $datos['idmedicamento'],
			'idalmacen' => $datos['id'],
			'idsubalmacen' => $datos['idsubalmacen'], //  POR DEFECTO EN EL ALMACEN PRINCIPAL 
			'precio_venta' => empty($datos['precio_venta']) ? '0.00' : $datos['precio_venta'],
			'precio_compra' => empty($datos['precio']) ? '0.00' : $datos['precio'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_medicamento_almacen', $data);
	}
	public function m_editar_medicamento_en_almacen($datos)
	{
		$data = array(
			'precio_venta' => $datos['precio'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		return $this->db->update('far_medicamento_almacen', $data);
	}
	public function m_actualizar_stock_medicamento($datos)
	{
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db->set('stock_actual', $datos['stock_actual_modificado'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('medicamento');
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_med' => 0
		);
		$this->db->where('idmedicamento',$id);
		if($this->db->update('medicamento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_med' => 2
		);
		$this->db->where('idmedicamento',$id);
		if($this->db->update('medicamento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_med' => 1
		);
		$this->db->where('idmedicamento',$id);
		if($this->db->update('medicamento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_editar_codigo_barra($datos)
	{
		$data = array(
			'codigo_barra' => $datos['codigo_barra'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		return $this->db->update('medicamento', $data);
	}
	public function m_buscar_medicamento_almacen($paramDatos) 
	{
		$this->db->select("m.idmedicamento,(COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) AS medicamento",FALSE); 
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento m','m.idmedicamento = fma.idmedicamento','left'); 
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idmedicamento',$paramDatos);
		return $this->db->get()->row_array();
	}
	/* PREPARADOS Y FORMULAS */
	public function m_cargar_formulas_por_arrId($arrId)
	{
		$this->db->select('med.idmedicamento, med.denominacion, med.idformula_jj, fma.precio_compra::NUMERIC, med.categoria_jj', FALSE);
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma', 'med.idmedicamento = fma.idmedicamento');
		$this->db->where('idtipoproducto', 22);
		$this->db->where_in('med.idmedicamento', $arrId);
		$this->db->where('idsubalmacen', 20);
		$this->db->order_by('med.idformula_jj', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ultimo_codigo_formula()
	{
		$this->db->select('idformula_jj');
		$this->db->from('medicamento');
		$this->db->where('idtipoproducto', 22); 
		$this->db->ilike('idformula_jj','VS','after');
		$this->db->order_by('idformula_jj','DESC');
		$this->db->limit(1);
		$row = $this->db->get()->row_array();
		return $row['idformula_jj'];
	}
	public function m_asignar_fecha_formula_nueva($datos)
	{
		$data = array(
			'fecha_asigna_idformula_jj' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedicamento',$datos['id']);
		return $this->db->update('medicamento', $data);
	}
	public function m_asignar_categoria_jj($datos)
	{
		$data = array(
			'categoria_jj' => $datos['categoria']
		);
		$this->db->where('idmedicamento',$datos['id']);
		return $this->db->update('medicamento', $data);
	}
	public function m_asignar_uso_jj($datos)
	{
		$data = array(
			'uso_jj' => $datos['uso']
		);
		$this->db->where('idmedicamento',$datos['id']);
		return $this->db->update('medicamento', $data);
	}
	public function m_verificar_medicamento_similar($datos)
	{
		$this->db->select('idmedicamento, denominacion');
		$this->db->from('medicamento');
		$this->db->where('estado_med', 1); 
		$this->db->where('idtipoproducto', 22); 
		$this->db->where("regexp_replace(\"denominacion\", ' ', '', 'g') = '" . $datos['searchText'] . "'", NULL, FALSE );
		if(!empty($datos['excepto'])){
			$this->db->where('idmedicamento <>', $datos['excepto']);
		}
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_medicamento_con_categoria_jj($datos)
	{
		$this->db->select('idmedicamento, denominacion');
		$this->db->from('medicamento');
		$this->db->where('idmedicamento', $datos['id']);
		$this->db->where('categoria_jj IS NULL');

		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_medicamento_con_uso_jj($datos)
	{
		$this->db->select('idmedicamento, denominacion');
		$this->db->from('medicamento');
		$this->db->where('idmedicamento', $datos['id']);
		$this->db->where('uso_jj IS NULL');

		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_editar_formula($datos)
	{
		$data = array(
			'denominacion' => $datos['medicamento'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedicamento',$datos['id']);
		return $this->db->update('medicamento', $data);
	}
	public function m_buscar_medicamento_solicitud($id)
	{
		$this->db->select('iddetallesolicitud');
		$this->db->from('far_detalle_solicitud fds');
		$this->db->join('far_solicitud_formula fsf', 'fds.idsolicitudformula = fsf.idsolicitudformula');
		$this->db->where('fds.idmedicamento', $id);
		$this->db->where('fsf.estado_sol', 1);
		$this->db->where_in('fds.estado_detalle_sol', array(1,2));

		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}

	public function m_cargar_medicamento_reporte($datos)
	{
		// subconsulta para obtener idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$datos['sede']['id']);
		if($datos['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$datos['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('SUM( CASE WHEN (sc_fm.tipo_movimiento = 2 OR sc_fm.tipo_movimiento = 6) THEN (sc_fdm.cantidad)::NUMERIC END	) -	SUM( CASE WHEN (sc_fm.tipo_movimiento = 1 OR sc_fm.tipo_movimiento = 5 ) THEN (sc_fdm.cantidad)::NUMERIC ELSE 0 END	)');
		$this->db->from('far_detalle_movimiento sc_fdm');
		$this->db->join('far_movimiento sc_fm','sc_fdm.idmovimiento = sc_fm.idmovimiento');
		$this->db->join('far_medicamento_almacen sc_fma','sc_fdm.idmedicamento = sc_fma.idmedicamento');
		$this->db->where('sc_fma.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->where('sc_fm.estado_movimiento', 1);
		$this->db->where('sc_fm.fecha_movimiento::TIMESTAMP <= '.$this->db->escape($datos['hasta']),NULL,FALSE); // HASTA LA FECHA DE CORTE 
		$this->db->where('sc_fdm.estado_detalle', 1);
		$stock = $this->db->get_compiled_select();
		$this->db->reset_query();
		/* consulta principal */

		// CONSULTA PRINCIPAL
		$this->db->distinct();
		$this->db->select('med.idmedicamento, med.denominacion');
		$this->db->select('('. $stock .') AS stock_actual_total');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento med', 'fdm.idmedicamento = med.idmedicamento AND med.estado_med = 1');
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('far_laboratorio lab', 'lab.idlaboratorio = med.idlaboratorio AND lab.estado_lab = 1');
		$this->db->join('tipo_producto tp', 'tp.idtipoproducto = med.idtipoproducto AND tp.idmodulo = 3');
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa .')');
		$this->db->where('fm.fecha_movimiento::TIMESTAMP <= '.$this->db->escape($datos['hasta']),NULL,FALSE); 

		if ($datos['laboratorio']['id'] != 0) {
			$this->db->where('lab.idlaboratorio', $datos['laboratorio']['id']);
		}
		if ($datos['tipoProducto']['id'] != 0) {
			$this->db->where('tp.idtipoproducto', $datos['tipoProducto']['id']);
		}

		$this->db->group_by("med.idmedicamento, fma.idmedicamentoalmacen");
		$this->db->order_by("med.denominacion");
		return $this->db->get()->result_array();
	}
	public function m_cargar_venta_medicamento_mes_anio($datos)
	{
		// subconsulta para obtener idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$datos['sede']['id']);
		if($datos['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$datos['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select("med.idmedicamento, med.denominacion, SUM(fdm.cantidad) AS cantidad, 
			SUM(fdm.total_detalle) AS monto, date_part('month', fm.fecha_movimiento) AS mes,
		    date_part('year', fm.fecha_movimiento) AS anio", FALSE);
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento med', 'fdm.idmedicamento = med.idmedicamento AND med.estado_med = 1');
		$this->db->join('far_laboratorio lab', 'lab.idlaboratorio = med.idlaboratorio AND lab.estado_lab = 1');
		$this->db->join('tipo_producto tp', 'tp.idtipoproducto = med.idtipoproducto AND tp.idmodulo = 3');
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa .')');
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($datos['desde']).' AND ' . $this->db->escape($datos['hasta']));

		if ($datos['laboratorio']['id'] != 0) {
			$this->db->where('lab.idlaboratorio', $datos['laboratorio']['id']);
		}
		if ($datos['tipoProducto']['id'] != 0) {
			$this->db->where('tp.idtipoproducto', $datos['tipoProducto']['id']);
		}

		$this->db->group_by("date_part('year', fm.fecha_movimiento), date_part('month', fm.fecha_movimiento), med.idmedicamento");
		$this->db->order_by("date_part('year', fm.fecha_movimiento), date_part('month', fm.fecha_movimiento), med.denominacion");
		return $this->db->get()->result_array();
	}

}