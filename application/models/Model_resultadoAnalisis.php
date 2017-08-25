<?php
class Model_resultadoAnalisis extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	/*
	public function m_cargar_paciente_sin_resultado($datos){
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('idanalisispaciente, ap.idanalisis, ap.idmuestrapaciente, descripcion_anal, descripcion_sec as seccion, ap.iddetalle, h.idhistoria, ap.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, cl.sexo, estado_ap, orden_lab, mp.orden_venta, v.ticket_venta, v.idventa');
		$this->db->from('analisis_paciente ap');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('muestra_paciente mp','ap.idmuestrapaciente = mp.idmuestrapaciente');
		$this->db->join('venta v', 'mp.orden_venta = v.orden_venta');
		$this->db->where('estado_ap', 1);
		if( $datos['searchTipo'] == 'PP' ) { 
			$this->db->where($datos['searchColumn'].' = ', $datos['searchText']); 
		}else{
			$this->db->where($datos['searchColumn'], $datos['searchText']); 
		}
		return $this->db->get()->result_array();
	}*/
	public function m_cargar_pacientes_laboratorio($paramPaginate){
		$this->db->select('mp.idmuestrapaciente, h.idhistoria, cl.num_documento, cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, fecha_nacimiento, cl.sexo, mp.orden_lab');
		$this->db->from('muestra_paciente mp');
		$this->db->join('cliente cl','mp.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->where('estado_mp <>', 0);
		$this->db->where('mp.idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
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
	public function m_count_pacientes_laboratorio($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('muestra_paciente mp');
		$this->db->join('cliente cl','mp.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->where('estado_mp <>', 0);
		$this->db->where('mp.idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
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
	public function m_cargar_analisis_por_orden($orden_lab){
		$this->db->select('ap.idanalisispaciente, ap.idanalisis, ap.idmuestrapaciente, anal.descripcion_anal');

		$this->db->from('muestra_paciente mp');
		$this->db->join('analisis_paciente ap','mp.idmuestrapaciente = ap.idmuestrapaciente');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->where('orden_lab', $orden_lab);
		// $this->db->where('mp.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		// $this->db->order_by('idanalisispaciente', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_verificar_estructura($idanalisis,$idsedeempresaadmin){
		$this->db->from('analisis_parametro apar');
		$this->db->where('idanalisis', $idanalisis);
		$this->db->where('idsedeempresaadmin', $idsedeempresaadmin);
		$this->db->where('estado_apar', 1);
		// $this->db->order_by('idanalisispaciente', 'ASC');
		return $this->db->get()->result_array();
	}
	// para grilla - modelo anterior
	public function m_cargar_analisis_paciente($paramPaginate){
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('idanalisispaciente, ap.idanalisis, ap.idmuestrapaciente, descripcion_anal, descripcion_sec as seccion, h.idhistoria, ap.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, cl.sexo, estado_ap');
		$this->db->from('analisis_paciente ap');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->where('estado_ap <>', 0);
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
	public function m_count_analisis_paciente($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('analisis_paciente ap');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->where('estado_ap <>', 0);
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
	//---------------------------
	public function m_cargar_parametros_analisis($datos){
		$this->db->select('idanalisisparametro, par.idparametro, apar.idanalisis, descripcion_par, valor_normal_h, valor_normal_m, valor_ambos, met.descripcion as metodo, par.separador');
	    $this->db->from('analisis_parametro apar');
	    $this->db->join('analisis anal','apar.idanalisis = anal.idanalisis');
	    $this->db->join('parametro par','apar.idparametro = par.idparametro');
	    $this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin'], 'left' );
	    $this->db->join('metodo met','anal.idmetodo = met.idmetodo','left');
	    $this->db->where('apar.idanalisis', $datos['idanalisis']);
	    
	    return $this->db->get()->result_array();

	}

	// FUNCION PRINCIPAL ---- NO BORRAR¡¡¡¡¡¡¡¡¡¡¡
	public function m_cargar_parametros_analisis_por_orden($datos,$idsedeempresaadmin){
		$this->db->select('h.idhistoria, ap.idcliente, , cl.nombres, cl.apellido_paterno, cl.apellido_materno, cl.sexo, fecha_nacimiento');
		$this->db->select('mp.orden_lab, mp.orden_venta, v.ticket_venta, v.idventa, mp.fecha_recepcion');
		$this->db->select('s.idseccion, s.descripcion_sec as seccion, apar.idanalisis, anal.descripcion_anal, cantidad,
			(CASE WHEN (par.separador = 0 ) THEN apar.idanalisisparametro ELSE apar2.idanalisisparametro END), 
			par.idparametro AS idparametro,	par.descripcion_par AS parametro, par2.idparametro AS idsubparametro, par2.descripcion_par AS subparametro, par.combo, par2.combo AS subcombo, par.nombre_combo, par2.nombre_combo AS nombre_subcombo,
			(CASE WHEN (par.separador = 0 ) THEN apar.orden_parametro ELSE apar2.orden_subparametro END),
			(CASE WHEN (par.separador = 0 ) THEN par.autocalculable ELSE par2.autocalculable END),
			(CASE WHEN (par.separador = 0 ) THEN par.formula ELSE par2.formula END),
			(CASE WHEN (par.separador = 0 ) THEN par.requiere_texto_adicional ELSE par2.requiere_texto_adicional END),
			(CASE WHEN (par.separador = 0 ) THEN par.texto_adicional ELSE par2.texto_adicional END),
			(CASE WHEN (par.separador = 0 ) THEN dr.resultado ELSE dr2.resultado END),
			(CASE WHEN (par.separador = 0 ) THEN dr.iddetalleresultado ELSE dr2.iddetalleresultado END),
			(CASE WHEN (par.separador = 0 ) THEN pvs.valor_normal_h ELSE pvs2.valor_normal_h END),
			(CASE WHEN (par.separador = 0 ) THEN pvs.valor_normal_m ELSE pvs2.valor_normal_m END),
			(CASE WHEN (par.separador = 0 ) THEN pvs.valor_ambos ELSE pvs2.valor_ambos END),
			(CASE WHEN (par.separador = 0 ) THEN pvs.valor_json ELSE pvs2.valor_json END),
		 	met.descripcion AS metodo, par.separador, apar.idparent, apar2.idparent, 
		 	ap.idanalisispaciente, ap.idmuestrapaciente, ap.iddetalle,pm.descripcion as producto, d.paciente_atendido_det, ap.fecha_resultado, ap.numero_impresiones, apar.orden_parametro, apar2.orden_subparametro ,estado_ap',FALSE);
	    $this->db->from('analisis_parametro apar');
	    $this->db->join('analisis anal','apar.idanalisis = anal.idanalisis');
	    $this->db->join('seccion s','anal.idseccion = s.idseccion');
	    
	    $this->db->join('parametro par','apar.idparametro = par.idparametro');
	    $this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$idsedeempresaadmin, 'left' );
	    $this->db->join('metodo met','anal.idmetodo = met.idmetodo','left');
	    $this->db->join('analisis_paciente ap','anal.idanalisis = ap.idanalisis');
	    $this->db->join('detalle_resultado dr','ap.idanalisispaciente = dr.idanalisispaciente AND apar.idanalisisparametro = dr.idanalisisparametro','left');
	    $this->db->join('detalle d','ap.iddetalle = d.iddetalle');
	    $this->db->join('producto_master pm','ap.idproductomaster = pm.idproductomaster');
		$this->db->join('muestra_paciente mp','ap.idmuestrapaciente = mp.idmuestrapaciente');
		$this->db->join('venta v', 'mp.orden_venta = v.orden_venta');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('analisis_parametro apar2','apar.idanalisisparametro = apar2.idparent AND apar2.estado_apar = 1
			AND apar2.idsedeempresaadmin = '.$idsedeempresaadmin, 'left');
		$this->db->join('detalle_resultado dr2','ap.idanalisispaciente = dr2.idanalisispaciente AND apar2.idanalisisparametro = dr2.idanalisisparametro','left');
	    $this->db->join('parametro par2','apar2.idparametro = par2.idparametro', 'left');
	    $this->db->join('parametro_valor_sede pvs2', 'par2.idparametro = pvs2.idparametro AND pvs2.idsedeempresaadmin = '.$idsedeempresaadmin, 'left' );
		//$this->db->where('mp.orden_lab', $datos['orden_lab']);
		$this->db->where('apar.idparent', 0);
		$this->db->where('apar.estado_apar', 1);
		$this->db->where('v.estado', 1);
		$this->db->where('anal.estado_anal <> 0');
		$this->db->where('mp.idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
		$this->db->where('apar.idsedeempresaadmin', $idsedeempresaadmin);
		if( $datos['searchTipo'] == 'PP' ) { 
			$this->db->where($datos['searchColumn'].' = ', $datos['searchText']); 
		}else{
			$this->db->where($datos['searchColumn'], $datos['searchText']);
		}
		$this->db->order_by('cl.idcliente','ASC');
		$this->db->order_by('anal.idseccion','ASC');
		$this->db->order_by('apar.idanalisis','ASC');
		$this->db->order_by('apar.orden_parametro','ASC');
		$this->db->order_by('apar2.orden_subparametro','ASC');
		$this->db->order_by('apar.idanalisisparametro','ASC');
		$this->db->order_by('apar2.idanalisisparametro','ASC');
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_resumen_analisis($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS count_ingresados',FALSE); 
		$this->db->select('SUM( CASE WHEN (paciente_atendido_det = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE);
		$this->db->select('SUM( CASE WHEN (estado_ap = 4) THEN 1 ELSE 0 END ) AS count_entregados',FALSE); 
		$this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_det = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE); 
		$this->db->select('anal.idanalisis, anal.descripcion_anal, s.descripcion_sec as seccion'); 
		$this->db->from('analisis_paciente ap');
		$this->db->join('detalle d','ap.iddetalle = d.iddetalle');
		$this->db->join('analisis anal', 'ap.idanalisis = anal.idanalisis');
		$this->db->join('seccion s', 'anal.idseccion = s.idseccion');
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1');
		$this->db->join('sede_empresa_admin sea', 'v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea', 'sea.idempresaadmin = ea.idempresaadmin');
		// var_dump($datos); exit(); 
		$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']);
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('ap.estado_ap <>', 0);
		// var_dump($datos['desde']); exit();
		$this->db->where('fecha_examen BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
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
		$this->db->group_by('anal.idanalisis, s.descripcion_sec'); 
		//$this->db->group_by('am.idatencionmedica'); 
		return $this->db->get()->result_array();
	}
	public function m_count_resumen_analisis($paramPaginate,$paramDatos=FALSE)
	{
		//$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('COUNT(*) AS count_ingresados',FALSE); 
		$this->db->select('SUM( CASE WHEN (paciente_atendido_det = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE);
		$this->db->select('SUM( CASE WHEN (estado_ap = 4) THEN 1 ELSE 0 END ) AS count_entregados',FALSE); 
		$this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_det = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE); 
		$this->db->from('analisis_paciente ap');
		$this->db->join('detalle d','ap.iddetalle = d.iddetalle');
		$this->db->join('analisis anal', 'ap.idanalisis = anal.idanalisis');
		$this->db->join('seccion s', 'anal.idseccion = s.idseccion');
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1');
		// var_dump($datos); exit(); 
		$this->db->join('sede_empresa_admin sea', 'v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea', 'sea.idempresaadmin = ea.idempresaadmin');
		// var_dump($datos); exit(); 
		$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']);
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('ap.estado_ap <>', 0);
		// var_dump($datos['desde']); exit();
		$this->db->where('fecha_examen BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}

		$this->db->group_by('anal.idanalisis, s.descripcion_sec'); 
		$fData = $this->db->get()->result_array();
		// $fData = $this->db->get()->num_rows();
		return $fData;
	}
	public function m_cargar_detalle_resumen_analisis($paramPaginate,$paramDatos=FALSE)
	{
		if(!empty($this->sessionHospital['id_empresa_admin'])){
			$idempresaadminmatriz = $this->sessionHospital['id_empresa_admin'];
		}else{
			if($this->sessionHospital['idsedeempresaadmin'] == 9){ // MEDICINA INTEGRAL
				$idempresaadminmatriz = 38;
			}elseif( $this->sessionHospital['idsedeempresaadmin'] == 8 ){ // GM GESTORES
				$idempresaadminmatriz = 39;
			}
		}
		$this->db->select('orden_lab, ap.idhistoria, c.apellido_paterno, c.apellido_materno, c.nombres, 
			fecha_examen, fecha_atencion_det, fecha_entrega, estado_ap'); 
		
		$this->db->from('analisis_paciente ap');
		$this->db->join('cliente c','ap.idcliente = c.idcliente');
		$this->db->join('detalle d','ap.iddetalle = d.iddetalle');
		$this->db->join('venta v','d.idventa = v.idventa');
		$this->db->join('muestra_paciente mp','ap.idmuestrapaciente = mp.idmuestrapaciente');
		// var_dump($datos); exit(); 
		$this->db->where('mp.idempresaadminmatriz', $idempresaadminmatriz);
		$this->db->where('ap.estado_ap <>', 0);
		$this->db->where('v.estado', 1);
		$this->db->where('ap.idanalisis', $paramDatos['idanalisis']);
		// var_dump($datos['desde']); exit();
		$this->db->where('fecha_examen BETWEEN '. $this->db->escape($paramDatos['rango']['desde'].' '.$paramDatos['rango']['desdeHora'].':'.$paramDatos['rango']['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['rango']['hasta'].' '.$paramDatos['rango']['hastaHora'].':'.$paramDatos['rango']['hastaMinuto'])); 
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
		//$this->db->group_by('am.idatencionmedica'); 
		return $this->db->get()->result_array();
	}
	public function m_count_detalle_resumen_analisis($paramPaginate,$paramDatos=FALSE)
	{
		if(!empty($this->sessionHospital['id_empresa_admin'])){
			$idempresaadminmatriz = $this->sessionHospital['id_empresa_admin'];
		}else{
			if($this->sessionHospital['idsedeempresaadmin'] == 9){ // MEDICINA INTEGRAL
				$idempresaadminmatriz = 38;
			}elseif( $this->sessionHospital['idsedeempresaadmin'] == 8 ){ // GM GESTORES
				$idempresaadminmatriz = 39;
			}
		}
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('analisis_paciente ap');
		$this->db->join('cliente c','ap.idcliente = c.idcliente');
		$this->db->join('detalle d','ap.iddetalle = d.iddetalle');
		$this->db->join('venta v','d.idventa = v.idventa');
		$this->db->join('muestra_paciente mp','ap.idmuestrapaciente = mp.idmuestrapaciente');
		// var_dump($datos); exit(); 
		$this->db->where('mp.idempresaadminmatriz', $idempresaadminmatriz);
		$this->db->where('ap.estado_ap <>', 0);
		$this->db->where('v.estado', 1);
		$this->db->where('ap.idanalisis', $paramDatos['idanalisis']);
		// var_dump($datos['desde']); exit();
		$this->db->where('fecha_examen BETWEEN '. $this->db->escape($paramDatos['rango']['desde'].' '.$paramDatos['rango']['desdeHora'].':'.$paramDatos['rango']['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['rango']['hasta'].' '.$paramDatos['rango']['hastaHora'].':'.$paramDatos['rango']['hastaMinuto']));

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
	/*
	public function m_cargar_resultados_parametros_analisis_por_orden($datos){
		$this->db->select('apar.idanalisisparametro, par.idparametro, apar.idanalisis, descripcion_par, valor_normal_h, valor_normal_m, valor_ambos, met.descripcion as metodo, par.separador, orden_lab, resultado');
	    $this->db->from('analisis_parametro apar');
	    $this->db->join('analisis anal','apar.idanalisis = anal.idanalisis');
	    $this->db->join('parametro par','apar.idparametro = par.idparametro');
	    $this->db->join('metodo met','anal.idmetodo = met.idmetodo','left');
	    $this->db->join('analisis_paciente ap','anal.idanalisis = ap.idanalisis');
	    $this->db->join('detalle_resultado dr','ap.idanalisispaciente = dr.idanalisispaciente AND apar.idanalisisparametro = dr.idanalisisparametro','left');
		$this->db->join('muestra_paciente mp','ap.idmuestrapaciente = mp.idmuestrapaciente');
		$this->db->where('mp.orden_lab', $datos['orden_lab']);
		$this->db->order_by('apar.idanalisisparametro','ASC');
		return $this->db->get()->result_array();
	}*/
	public function m_cargar_parametros_analisis_res($datos){
		$this->db->select('apar.idanalisisparametro, par.idparametro, apar.idanalisis, descripcion_par, valor_normal_h, valor_normal_m, valor_ambos, met.descripcion as metodo, par.separador, resultado');
	    $this->db->from('analisis_parametro apar');
	    $this->db->join('analisis anal','apar.idanalisis = anal.idanalisis');
	    $this->db->join('parametro par','apar.idparametro = par.idparametro');
	    $this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin'], 'left' );
	    $this->db->join('metodo met','anal.idmetodo = met.idmetodo','left');
	    $this->db->join('analisis_paciente ap','anal.idanalisis = ap.idanalisis');
	    $this->db->join('detalle_resultado dr','ap.idanalisispaciente = dr.idanalisispaciente AND apar.idanalisisparametro = dr.idanalisisparametro');
	    $this->db->where('apar.idanalisis', $datos['idanalisis']);
	    $this->db->where('ap.idanalisispaciente', $datos['id']);
	    return $this->db->get()->result_array();

	}
	public function m_cargar_pacientes_autocomplete($datos)
	{
		$this->db->distinct();
		$this->db->select("cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, cl.num_documento, UPPER(CONCAT(cl.nombres,' ',cl.apellido_paterno,' ',cl.apellido_materno)) AS paciente", FALSE);
		$this->db->from('analisis_paciente ap');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->where('ap.estado_ap', 1); // ACTIVO
		$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		$this->db->limit(5); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_pacientes_res_autocomplete($datos)
	{
		$this->db->distinct();
		$this->db->select("cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, cl.num_documento, UPPER(CONCAT(cl.nombres,' ',cl.apellido_paterno,' ',cl.apellido_materno)) AS paciente", FALSE);
		$this->db->from('analisis_paciente ap');
		$this->db->join('cliente cl','ap.idcliente = cl.idcliente');
		$this->db->where('ap.estado_ap', 2); // ACTIVO
		$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		$this->db->limit(5); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_lista_combo($datos)
	{
		$this->db->select('nombre_combo, elemento_combo');
		$this->db->from('combo_laboratorio');
		$this->db->where('nombre_combo', $datos);
		$this->db->where('estado_combo', 1);
		$this->db->order_by('elemento_combo');
		return $this->db->get()->result_array(); 	
	}
	public function m_cargar_parasito_heces_cbo()
	{
		$this->db->select('idparasito, descripcion as elemento_combo');
		$this->db->from('parasito_heces');
		$this->db->order_by('idparasito', 'ASC');
		$this->db->where('estado_p', 1);
		return $this->db->get()->result_array();
	}

	public function m_verificar_detalle($iddetalle)
	{
		$this->db->select('*');
		$this->db->from('atencion_medica');
		$this->db->where('iddetalle', $iddetalle);
		$count = $this->db->get()->num_rows();
		if($count > 0){
			return false;
		}else{
			return true;
		}
		 
	}
	public function m_registrar_resultado($parametro, $datos)
	{	
		if($parametro['requiere_texto_adicional'] == 1){
			$parametro['resultado'] = $parametro['resultado'] . ' ' . $parametro['texto_adicional'];
		}
		$data = array(
			'resultado' => $parametro['resultado'],
			'idanalisisparametro' => $parametro['idanalisisparametro'],
			'idanalisis' => $datos['idanalisis'],
			'idanalisispaciente' => $datos['idanalisispaciente'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser_cr' => $this->sessionHospital['idusers']
		);
		return $this->db->insert('detalle_resultado', $data);
	}
	public function m_actualizar_resultado($parametro)
	{	
		// if($parametro['requiere_texto_adicional'] == 1){
		// 	$parametro['resultado'] = $parametro['resultado'] . ' ' . $parametro['texto_adicional'];
		// }
		$data = array(
			'resultado' => $parametro['resultado'],
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser_up' => $this->sessionHospital['idusers']
		);
		$this->db->where('iddetalleresultado',$parametro['iddetalleresultado']);
		return $this->db->update('detalle_resultado', $data);
	}
	public function m_actualizar_estado_analisis($datos)
	{
		$data = array(
			'estado_ap' => 2, // con resultados
			'fecha_resultado' => date('Y-m-d H:i:s'),
			'iduser_resultado' => $this->sessionHospital['idusers']
		);
		$this->db->where('idanalisispaciente',$datos['idanalisispaciente']);
		return $this->db->update('analisis_paciente', $data);
	}
	public function m_actualizar_estado_analisis_a_entregado($datos)
	{
		$data = array(
			'estado_ap' => 4, // entregado
			'fecha_entrega' => date('Y-m-d H:i:s'),
			'iduser_entrega' => $this->sessionHospital['idusers']
		);
		$this->db->where('idanalisispaciente',$datos['idanalisispaciente']);
		return $this->db->update('analisis_paciente', $data);
	}
	public function m_actualizar_detalle_venta($datos)
	{
		$data = array(
			'paciente_atendido_det' => 1, // atendido
			'fecha_atencion_det' => date('Y-m-d H:i:s')

		);
		$this->db->where('iddetalle',$datos['iddetalle']);
		return $this->db->update('detalle', $data);
	}
	public function m_actualizar_numero_impresiones($datos)
	{
		$data = array(
			'numero_impresiones' => $datos['numero_impresiones']
			
		);
		$this->db->where('idanalisispaciente',$datos['idanalisispaciente']);
		return $this->db->update('analisis_paciente', $data);
	}
	  /* ************************************** */
	 /*  Resultados desde SIgelab / Sql Server */
	/* ************************************** */
	public function m_generar_resultados_sqlserver($datos) // NO BORRAR¡¡¡ 
	{
		$serverName = "161.132.104.156";  
		$connectionInfo = array( "Database"=>"Sigelab", "UID"=>"sa", "PWD"=>"lab.2016" ); 
		$conn = sqlsrv_connect( $serverName, $connectionInfo); 
		if($conn){
			// echo "Conectado a la Base de Datos."; die();
	    	$sql = "EXEC ht_wsResultados ?";
	    	$params = array($datos['orden_lab']);
	    	$result = sqlsrv_query( $conn, $sql, $params );
	    	$arrResultado = array();
	  		if( $result === false ) {
			    die( print_r( sqlsrv_errors(), true));
			}
			while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)){
		      $arrResultado[] = $row; 
		   	}

			return $arrResultado;
		}else{
			// echo "NO se puede conectar a la Base de Datos."; die( print_r( sqlsrv_errors(), true)); 
			return 0;
		}
	}
	
}
