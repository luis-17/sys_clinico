<?php 
class Model_atencion_medica extends CI_Model { 
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_esta_venta_por_busqueda_atencion($datos) 
	{
		// var_dump($datos); exit();
		$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE); 
		$this->db->select('v.idventa, d.iddetalle, d.cantidad, d.si_tipo_campania ,d.idcampania ,v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
			paciente_atendido_v, paciente_atendido_det, fecha_atencion_v, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
			pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, 
			sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
			sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion, 
			tiene_autorizacion, e.idespecialidad, e.nombre, e.atencion_dia, e.dias_libres, nc.idnotacredito, ncd.idnotacreditodetalle 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
		$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
		$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
		$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('historia h','c.idcliente = h.idcliente'); 
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede sed','sea.idsede = sed.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa AND nc.estado_nc = 1','left');
		$this->db->join('nota_credito_detalle ncd','d.iddetalle = ncd.iddetalle AND nc.idnotacredito = ncd.idnotacredito AND ncd.estado_ncd = 1','left'); 
		if( $datos['searchTipo'] == 'PP' ) { 
			$this->db->where($datos['searchColumn'].' = ', $datos['searchText']); 
		}else{
			$this->db->where($datos['searchColumn'], $datos['searchText']); 
		}
		if($datos['arrTipoProductos']){ 
			$this->db->where_in('tp.idtipoproducto', $datos['arrTipoProductos']); 
		}
		// if($this->sessionHospital['es_empresa_admin'] === '1'){ 
		// $this->db->where('ea.ruc', $this->sessionHospital['ruc_tercero']); 
		$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']);
		$this->db->where('v.idespecialidad', $this->sessionHospital['idespecialidad']);  
		// } 
		$this->db->where('v.estado', 1); // ACTIVO 
		$this->db->where('paciente_atendido_det', 2); // NO 
		
		// $this->db->where('sed.idsede', $this->sessionHospital['idsede']); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_ventas_atendidas_por_busqueda_del_dia($datos=FALSE)
	{
		//Ventas por caja
			$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, d.iddetalle, d.cantidad, v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
				paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
				c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
				am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
				temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
				am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
				aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe, 
				pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad, sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
				sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion'); 
			$this->db->from('venta v'); 
			$this->db->join('cliente c','v.idcliente = c.idcliente'); 
			$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('detalle d','v.idventa = d.idventa');
			$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
			$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
			$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
			$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('atencion_medica am',"d.iddetalle = am.iddetalle AND am.origen_venta = 'C'"); 
			$this->db->join('medico med','am.ex_responsable_medico = med.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
			$this->db->join('medico m','am.idmedico = m.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
			$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 

			if(!empty($datos['idTipoAtencion']) && $datos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $datos['idTipoAtencion']);
				
			}
			if(!empty($datos['idespecialidad'])){
				$this->db->where('v.idespecialidad', $datos['idespecialidad']);
			}
			$this->db->where('paciente_atendido_det', 1); // SI 
			
			$this->db->where('estado_am', 1); // ATENDIDO 
			$this->db->where('v.estado', 1); // ACTIVO 
			// Como ya es una venta atendida, entonces ahora se filtrara con EMPRESA/ESPECIALIDAD 
			// $this->db->where('idempresaespecialidad', $this->sessionHospital['idempresaespecialidad']); -- innecesario, porque ahi tendrá todas las ventas de sus especialidades 
			$this->db->where('am.idmedico', @$this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
		$sqlAtenSinWeb = $this->db->get_compiled_select();

		//Ventas por web	
			$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, d.iddetalle, d.cantidad, v.orden_venta, v.estado, NULL AS tiene_impresion, NULL AS tiene_reimpresion, NULL AS solicita_impresion, 
				paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
				c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
				am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
				temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
				am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
				aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe, 
				pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad, NULL AS idsolicitudprocedimiento, NULL AS observacion, NULL AS idsolicitudexamen, NULL AS indicaciones, 
				NULL AS idsolicitudcitt, NULL AS idcontingencia, NULL AS contingencia, NULL AS fec_otorgamiento, NULL AS fec_iniciodescanso, NULL AS total_dias, NULL AS idtipoatencion',FALSE);

			$this->db->from('ce_venta v'); 			
			$this->db->join('ce_detalle d','v.idventa = d.idventa');
			$this->db->join('pa_prog_cita ppc','ppc.idprogcita = d.idprogcita');
			$this->db->join('cliente c','ppc.idcliente = c.idcliente'); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad');
			/*$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
			$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
			$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
			$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); */
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('atencion_medica am',"d.iddetalle = am.iddetalle AND am.origen_venta = 'W'"); 
			$this->db->join('medico med','am.ex_responsable_medico = med.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
			$this->db->join('medico m','am.idmedico = m.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
			$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 
			$this->db->where('paciente_atendido_det', 1); // SI 			
			$this->db->where('estado_am', 1); // ATENDIDO 
			$this->db->where('v.estado', 1); // ACTIVO 
			$this->db->where('am.idmedico', @$this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			if(!empty($datos['idTipoAtencion']) && $datos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $datos['idTipoAtencion']);
				
			}
			if(!empty($datos['idespecialidad'])){
				$this->db->where('d.idespecialidad', $datos['idespecialidad']);
			}
		$sqlAtenWeb = $this->db->get_compiled_select();

		$sqlMaster = 'select * from (' . $sqlAtenSinWeb.' UNION ALL '.$sqlAtenWeb . ' ) a ';
		$query = $this->db->query($sqlMaster);
		/*print_r($sqlMaster);
		exit();*/
		return $query->result_array();
		

		// if( $this->sessionHospital['vista_sede_empresa'] == '1' ){ 
		// 	$this->db->where('sed.idsede', $this->sessionHospital['idsede']);
		// }else{ 
		// 	$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		// $this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		//$this->db->group_by('am.idatencionmedica'); 
		//return $this->db->get()->result_array();
	}
	public function m_cargar_paciente_programado_sin_atender($datos) 
	{

		if($datos['origen_venta'] == 'W'){			 
			$this->db->select('v.idventa, d.iddetalle, d.cantidad,v.orden_venta, v.estado, v.fecha_venta, v.ticket_venta, ppc.idprogcita');
			$this->db->select('d.tiene_autorizacion, d.paciente_atendido_det');
			$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, c.sexo, c.fecha_nacimiento');
			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('h.idhistoria'); 
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp' );
			$this->db->select(' e.idespecialidad, e.nombre, e.atencion_dia, e.dias_libres'); 
			$this->db->from('ce_venta v'); 
			$this->db->join('ce_detalle d','v.idventa = d.idventa'); 
			$this->db->join('pa_prog_cita ppc','ppc.idprogcita = d.idprogcita'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('cliente c','ppc.idcliente = c.idcliente AND ppc.idcliente = '. $datos['idcliente']); 
			$this->db->join('historia h','c.idcliente = h.idcliente'); 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
			$this->db->where('d.idproductomaster', $datos['idproductomaster']);			
			$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']);
			$this->db->where('d.iddetalle', $datos['iddetalle']);
			$this->db->where('d.idespecialidad', $this->sessionHospital['idespecialidad']);  
			$this->db->where('v.estado', 1); // ACTIVO 
			$this->db->where('d.paciente_atendido_det', 2); // NO
		}else{

			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE); 
			$this->db->select('v.idventa, d.iddetalle, d.cantidad,v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, ppc.idprogcita,
				paciente_atendido_v, paciente_atendido_det, fecha_atencion_v, 
				sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, 
				c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
				pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, 
				sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
				sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion, 
				tiene_autorizacion, e.idespecialidad, e.nombre, e.atencion_dia, e.dias_libres, 
			'); 
			$this->db->from('venta v'); 
			$this->db->join('detalle d','v.idventa = d.idventa'); 
			$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left');
			$this->db->join('pa_prog_cita ppc','ppc.idprogcita = d.idprogcita','left');	 
			$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
			$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
			$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('cliente c','v.idcliente = c.idcliente'); 
			$this->db->join('historia h','c.idcliente = h.idcliente'); 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
			// if( $datos['searchTipo'] == 'PP' ) { 
			// 	$this->db->where($datos['searchColumn'].' = ', $datos['searchText']); 
			// }else{
			// 	$this->db->where($datos['searchColumn'], $datos['searchText']); 
			// }
			// if($datos['arrTipoProductos']){ 
			// 	$this->db->where_in('tp.idtipoproducto', $datos['arrTipoProductos']); 
			// }
			// if($this->sessionHospital['es_empresa_admin'] === '1'){ 
			// $this->db->where('ea.ruc', $this->sessionHospital['ruc_tercero']); 
			$this->db->where('d.idproductomaster', $datos['idproductomaster']);
			$this->db->where('d.iddetalle', $datos['iddetalle']);
			$this->db->where('v.idcliente', $datos['idcliente']);
			$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']);
			$this->db->where('v.idespecialidad', $this->sessionHospital['idespecialidad']);  
			// } 
			$this->db->where('v.estado', 1); // ACTIVO 
			$this->db->where('paciente_atendido_det', 2); // NO
		}
		
		
		// $this->db->where('sed.idsede', $this->sessionHospital['idsede']); 
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_paciente_programado_atendido($datos=FALSE)
	{
		
		if($datos['origen_venta'] == 'W'){			 
			$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, d.iddetalle, d.cantidad, v.orden_venta, v.estado, v.paciente_atendido_v, paciente_atendido_det, 
				sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta');
			$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, 
				h.idhistoria');
			$this->db->select('am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
				temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
				am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
				aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe');
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp');
			$this->db->select('e.idespecialidad, (e.nombre) AS especialidad');
			/*$this->db->select('sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
				sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion'); 
			*/
			$this->db->from('ce_venta v'); 			
			$this->db->join('ce_detalle d','v.idventa = d.idventa');
			$this->db->join('pa_prog_cita ppc','ppc.idprogcita = d.idprogcita');
			$this->db->join('cliente c','ppc.idcliente = c.idcliente AND ppc.idcliente = '. $datos['idcliente']); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad');
			/*$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
			$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
			$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
			$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); */
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
			$this->db->join('medico m','am.ex_responsable_medico = m.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
			$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 
			$this->db->where('d.idproductomaster', $datos['idproductomaster']);
			$this->db->where('d.iddetalle', $datos['iddetalle']);
			$this->db->where('paciente_atendido_det', 1); // SI 			
			$this->db->where('estado_am', 1); // ATENDIDO 
			$this->db->where('v.estado', 1); // ACTIVO 
			$this->db->where('am.idmedico', @$this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			$this->db->limit(1);
		}else{
			$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, d.iddetalle, d.cantidad, v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
				paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
				c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
				am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
				temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
				am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
				aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe, 
				pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad, sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
				sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion'); 
			$this->db->from('venta v'); 
			$this->db->join('cliente c','v.idcliente = c.idcliente'); 
			$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('detalle d','v.idventa = d.idventa');
			$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
			$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
			$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
			$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
			$this->db->join('medico m','am.ex_responsable_medico = m.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
			$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 

			// if(!empty($datos['idTipoAtencion']) && $datos['idTipoAtencion'] !== 'ALL' ) { 
			// 	$this->db->where('am.tipo_atencion_medica', $datos['idTipoAtencion']);
				
			// }
			// if(!empty($datos['idespecialidad'])){
			// 	$this->db->where('v.idespecialidad', $datos['idespecialidad']);
			// }
			$this->db->where('d.idproductomaster', $datos['idproductomaster']);
			$this->db->where('d.iddetalle', $datos['iddetalle']);
			$this->db->where('v.idcliente', $datos['idcliente']);
			$this->db->where('paciente_atendido_det', 1); // SI 
			
			$this->db->where('estado_am', 1); // ATENDIDO 
			$this->db->where('v.estado', 1); // ACTIVO 
			// Como ya es una venta atendida, entonces ahora se filtrara con EMPRESA/ESPECIALIDAD 
			// $this->db->where('idempresaespecialidad', $this->sessionHospital['idempresaespecialidad']); -- innecesario, porque ahi tendrá todas las ventas de sus especialidades 
			$this->db->where('am.idmedico', @$this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			// if( $this->sessionHospital['vista_sede_empresa'] == '1' ){ 
			// 	$this->db->where('sed.idsede', $this->sessionHospital['idsede']);
			// }else{ 
			// 	$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
			// }
			// $this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
			//$this->db->group_by('am.idatencionmedica'); 
			$this->db->limit(1);
		}
		return $this->db->get()->result_array();
	}

	public function m_cargar_esta_atencion_medica($arrNumActoMedico)
	{
		
		$this->db->select("c.idcliente, CONCAT_WS(' ',c.nombres,c.apellido_paterno,c.apellido_materno) AS cliente", FALSE);
		$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
		$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
		$this->db->select("v.idventa, d.iddetalle, d.cantidad, d.total_detalle, v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
			paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
			am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
			temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
			am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
			aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe, 
			pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad, sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
			sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion"); 

		$this->db->from('venta v'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('historia h','c.idcliente = h.idcliente'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
		$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
		$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
		$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->join('medico m','am.ex_responsable_medico = m.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
		$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
		$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where_in('am.idatencionmedica', $arrNumActoMedico); // SI 
		// $this->db->where('estado_am', 1); // ATENDIDO 
		// $this->db->where('v.estado', 1); // ACTIVO 
		// $this->db->limit(1); 
		return $this->db->get()->result_array();
	}
	// corregido 
	public function m_cargar_historial_ventas_atendidas($paramPaginate,$paramDatos=FALSE) // total_detalle_sf 
	{
		//venta por caja
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, v.orden_venta, v.estado, v.paciente_atendido_v, v.fecha_venta, v.ticket_venta, v.idempresaespecialidad');
			$this->db->select('d.iddetalle, d.paciente_atendido_det, (d.total_detalle::numeric) AS total_detalle_sf');
			$this->db->select("CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.idcliente, c.num_documento, sexo, 
				c.fecha_nacimiento, h.idhistoria,am.idatencionmedica, am.fecha_atencion");
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp');
			$this->db->select('e.idespecialidad, (e.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa');
			$this->db->from('atencion_medica am');
			$this->db->join('detalle d','am.iddetalle = d.iddetalle AND d.paciente_atendido_det = 1');
			$this->db->join('venta v','d.idventa = v.idventa AND v.estado = 1');
			$this->db->join('cliente c','v.idcliente = c.idcliente'); 
			$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad');
			$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad'); 
			$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1'); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			// $this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('am.estado_am', 1); // ATENDIDO 
			$this->db->where('am.origen_venta', 'C'); // que no sea web
			$this->db->where('am.fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 		
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 
			if(!empty($paramDatos['idTipoAtencion']) && $paramDatos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $paramDatos['idTipoAtencion']); 
			}
			if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
				$this->db->where('v.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
			}
			if(!empty($paramDatos['medico']) && $paramDatos['medico']['idmedico'] !== 'ALL' ) { 
				$this->db->where('med.idmedico', $paramDatos['medico']['idmedico']); 
			}			
		$sqlAtenSinWeb = $this->db->get_compiled_select();

		//venta por web
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, v.orden_venta, v.estado, v.paciente_atendido_v, v.fecha_venta, v.ticket_venta, d.idempresaespecialidad');
			$this->db->select('d.iddetalle, d.paciente_atendido_det, (d.total_detalle::numeric) AS total_detalle_sf');
			$this->db->select("CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.idcliente, c.num_documento, sexo, 
				c.fecha_nacimiento, h.idhistoria,am.idatencionmedica, am.fecha_atencion");
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp');
			$this->db->select('e.idespecialidad, (e.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa');
			$this->db->from('atencion_medica am');
			$this->db->join('historia h','am.idhistoria = h.idhistoria');
			$this->db->join('cliente c','h.idcliente = c.idcliente');
			$this->db->join('ce_detalle d','am.iddetalle = d.iddetalle AND d.paciente_atendido_det = 1');
			$this->db->join('ce_venta v','d.idventa = v.idventa AND v.estado = 1');
			$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad');
			$this->db->join('empresa_especialidad ee','d.idempresaespecialidad = ee.idempresaespecialidad'); 
			$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			// $this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('am.estado_am', 1); // ATENDIDO 		
			$this->db->where('am.origen_venta', 'W'); //web	
			$this->db->where('am.fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 
			if(!empty($paramDatos['idTipoAtencion']) && $paramDatos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $paramDatos['idTipoAtencion']); 
			}
			if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
				$this->db->where('d.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
			}
			if(!empty($paramDatos['medico']) && $paramDatos['medico']['idmedico'] !== 'ALL' ) { 
				$this->db->where('med.idmedico', $paramDatos['medico']['idmedico']); 
			}
		$sqlAtenWeb = $this->db->get_compiled_select();		
	
		// if( $this->sessionHospital['vista_sede_empresa'] == '1' ){ 
		// 	$this->db->where('sed.idsede', $this->sessionHospital['idsede']);
		// }else{ 
		// 	$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		$sqlMaster = 'select * from (' . $sqlAtenSinWeb.' UNION ALL '.$sqlAtenWeb . ' ) a';
		$where = null;
		if( !empty($paramPaginate['search']) ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					if(empty($where)){
						$where = " where CAST(".$key." AS TEXT) ILIKE '%".$value."%' ";
					}else{
						$where .= " AND CAST(".$key." AS TEXT) ILIKE '%".$value."%' ";
					}
				}
			}
		} 
		$sqlMaster .= $where;

		$order = '';
		if( !empty($paramPaginate['sortName']) ){
			$order = ' ORDER BY '. $paramPaginate['sortName'] .' ' . $paramPaginate['sort'];
		}

		if( !empty($paramDatos['reporte']) && $paramDatos['reporte']){
			$order = ' ORDER BY a.especialidad, a.idmedicoatencion, a.fecha_atencion DESC ';
		}
		$sqlMaster .= $order;

		$limit = '';
		if( !empty($paramPaginate['firstRow']) || !empty($paramPaginate['pageSize']) ){
			$limit = ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '. $paramPaginate['firstRow'];
		}
		$sqlMaster .= $limit;

		/*print_r($sqlMaster);
		exit();
		*/

		$query = $this->db->query($sqlMaster);
		return $query->result_array();

		//return $this->db->get()->result_array();
	}
	public function m_count_historial_ventas_atendidas($paramPaginate,$paramDatos=FALSE)
	{
		//venta por caja
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, v.orden_venta, v.estado, v.paciente_atendido_v, v.fecha_venta, v.ticket_venta, v.idempresaespecialidad');
			$this->db->select('d.iddetalle, d.paciente_atendido_det, (d.total_detalle::numeric) AS total_detalle_sf');
			$this->db->select("CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.idcliente, c.num_documento, sexo, 
				c.fecha_nacimiento, h.idhistoria,am.idatencionmedica, am.fecha_atencion");
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp');
			$this->db->select('e.idespecialidad, (e.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa');
			$this->db->from('atencion_medica am');
			$this->db->join('detalle d','am.iddetalle = d.iddetalle AND d.paciente_atendido_det = 1');
			$this->db->join('venta v','d.idventa = v.idventa AND v.estado = 1');
			$this->db->join('cliente c','v.idcliente = c.idcliente'); 
			$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad');
			$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad'); 
			$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1'); 
			$this->db->join('historia h','c.idcliente = h.idcliente');
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			// $this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('am.estado_am', 1); // ATENDIDO 
			$this->db->where('am.origen_venta', 'C'); //caja
			$this->db->where('am.fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 		
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 
			if(!empty($paramDatos['idTipoAtencion']) && $paramDatos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $paramDatos['idTipoAtencion']); 
			}
			if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
				$this->db->where('v.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
			}
			if(!empty($paramDatos['medico']) && $paramDatos['medico']['idmedico'] !== 'ALL' ) { 
				$this->db->where('med.idmedico', $paramDatos['medico']['idmedico']); 
			}			
		$sqlAtenSinWeb = $this->db->get_compiled_select();

		//venta por web
			$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
			$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
			$this->db->select('v.idventa, v.orden_venta, v.estado, v.paciente_atendido_v, v.fecha_venta, v.ticket_venta, d.idempresaespecialidad');
			$this->db->select('d.iddetalle, d.paciente_atendido_det, (d.total_detalle::numeric) AS total_detalle_sf');
			$this->db->select("CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.idcliente, c.num_documento, sexo, 
				c.fecha_nacimiento, h.idhistoria,am.idatencionmedica, am.fecha_atencion");
			$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp');
			$this->db->select('e.idespecialidad, (e.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa');
			$this->db->from('atencion_medica am');
			$this->db->join('historia h','am.idhistoria = h.idhistoria');
			$this->db->join('cliente c','h.idcliente = c.idcliente');
			$this->db->join('ce_detalle d','am.iddetalle = d.iddetalle AND d.paciente_atendido_det = 1');
			$this->db->join('ce_venta v','d.idventa = v.idventa AND v.estado = 1');
			$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad');
			$this->db->join('empresa_especialidad ee','d.idempresaespecialidad = ee.idempresaespecialidad'); 
			$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1'); 
			$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
			$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
			$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
			$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
			$this->db->join('sede sed','sea.idsede = sed.idsede'); 
			// $this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
			$this->db->where('am.estado_am', 1); // ATENDIDO 		
			$this->db->where('am.origen_venta', 'W'); //web	
			$this->db->where('am.fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
			if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
				$this->db->where('med.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
			} 
			if(!empty($paramDatos['idTipoAtencion']) && $paramDatos['idTipoAtencion'] !== 'ALL' ) { 
				$this->db->where('am.tipo_atencion_medica', $paramDatos['idTipoAtencion']); 
			}
			if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
				$this->db->where('d.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
			}
			if(!empty($paramDatos['medico']) && $paramDatos['medico']['idmedico'] !== 'ALL' ) { 
				$this->db->where('med.idmedico', $paramDatos['medico']['idmedico']); 
			}
		$sqlAtenWeb = $this->db->get_compiled_select();		
	
		// if( $this->sessionHospital['vista_sede_empresa'] == '1' ){ 
		// 	$this->db->where('sed.idsede', $this->sessionHospital['idsede']);
		// }else{ 
		// 	$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		$sqlMaster = 'select COUNT(*) AS contador, SUM(total_detalle_sf) AS sumaTotal from (' . $sqlAtenSinWeb.' UNION ALL '.$sqlAtenWeb . ' ) a';
		$where = null;
		if( !empty($paramPaginate['search']) ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					if(empty($where)){
						$where = " where CAST(".$key." AS TEXT) ILIKE '%".$value."%' ";
					}else{
						$where .= " AND CAST(".$key." AS TEXT) ILIKE '%".$value."%' ";
					}
				}
			}
		} 
		$sqlMaster .= $where;

		$query = $this->db->query($sqlMaster);
		return get_object_vars ($query->row()); 

		/*$fData = $this->db->get()->row_array(); 
		return $fData; */
	}
	public function m_cargar_atencion_medica_por_id($id)
	{
		
		$this->db->select("c.idcliente, CONCAT_WS(' ',c.nombres,c.apellido_paterno,c.apellido_materno) AS cliente", FALSE);
		$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
		$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(am.fecha_atencion,c.fecha_nacimiento)) AS edad",FALSE);
		$this->db->select("v.idventa, d.iddetalle, d.cantidad, d.total_detalle, v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
			paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
			am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
			temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
			am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, 
			aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, doc_informe, 
			pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad, sp.idsolicitudprocedimiento, sp.observacion, se.idsolicitudexamen, se.indicaciones, 
			sc.idsolicitudcitt, sc.idcontingencia, (ctg.descripcion_ctg) AS contingencia, sc.fec_otorgamiento, sc.fec_iniciodescanso, sc.total_dias, sc.idtipoatencion"); 

		$this->db->from('venta v'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('historia h','c.idcliente = h.idcliente'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('solicitud_procedimiento sp','d.idsolicitud = sp.idsolicitudprocedimiento AND tiposolicitud = 2','left'); 
		$this->db->join('solicitud_examen se','d.idsolicitud = se.idsolicitudexamen AND tiposolicitud = 1','left'); 
		$this->db->join('solicitud_citt sc','d.idsolicitud = sc.idsolicitudcitt AND tiposolicitud = 3','left'); 
		$this->db->join('contingencia ctg','sc.idcontingencia = ctg.idcontingencia', 'left'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->join('medico m','am.ex_responsable_medico = m.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
		$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
		$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('am.idatencionmedica', $id); // SI 
		// $this->db->where('estado_am', 1); // ATENDIDO 
		// $this->db->where('v.estado', 1); // ACTIVO 
		// $this->db->limit(1); 
		return $this->db->get()->result_array();
	} 
	// fin corregido 
	public function m_cargar_ventas_atendidas_para_terceros($paramDatos) 
	{
		if($paramDatos['empresaSoloAdmin']['id'] == '38'){ // medicina integral
			$idsedeempresaadmin = 9;// lurin
		}elseif($paramDatos['empresaSoloAdmin']['id'] == '39'){ // gm
			$idsedeempresaadmin = 8; // gm - villa
		}else{
			$idsedeempresaadmin = 1; // g & m - villa
		}
		$this->db->distinct();
		$this->db->select("(d.total_detalle::numeric) AS total_detalle_str",FALSE); 
		$this->db->select("v.idventa, v.ticket_venta, v.idempresaespecialidad, d.iddetalle, am.idatencionmedica, d.fecha_atencion_det, v.orden_venta,pm.idproductomaster, (pm.descripcion) AS producto, pm.pertenece_tercero, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, mp.idmediopago, mp.descripcion_med, e.idespecialidad, (e.nombre) AS especialidad, (emp_terc.descripcion) AS empresa, 
			ee.porcentaje, ee.productos_tercero,ee.porcentaje_o_fijo");
		$this->db->select('CASE WHEN(ee.porcentaje_o_fijo = 2) THEN
			(d.precio_costo::NUMERIC)*d.cantidad ELSE 0 END AS total_detalle_costo');
		$this->db->from('venta v'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente','left'); // PUEDE QUE NO TENGA CLIENTE, SI ES SALUD OCUPACIONAL 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad');
		$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle');

		$this->db->join('empresa emp_adm','ed.idempresaadmin = emp_adm.idempresa AND emp_adm.estado_em = 1'); 
		$this->db->join('empresa emp_terc','ed.idempresatercera = emp_terc.idempresa AND emp_terc.estado_em = 1'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle AND estado_am = 1','left'); // PARA SALUD OCUPACIONAL 
		$this->db->where('d.fecha_atencion_det BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
			$this->db->where('v.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
		}
		$this->db->where('emp_adm.idempresa', $paramDatos['empresaSoloAdmin']['id']);
		$this->db->where('v.idsedeempresaadmin', $idsedeempresaadmin);
		$this->db->where('d.paciente_atendido_det', 1); // SI 
		$this->db->where('v.estado', 1); // ACTIVO 
		$this->db->order_by('d.fecha_atencion_det ASC');
		return $this->db->get()->result_array();

	}
	public function m_cargar_ventas_on_line_atendidas($paramDatos) 
	{
		if($paramDatos['empresaSoloAdmin']['id'] == '38'){ // medicina integral
			$idsedeempresaadmin = 9;// lurin
		}elseif($paramDatos['empresaSoloAdmin']['id'] == '39'){ // gm
			$idsedeempresaadmin = 8; // gm - villa
		}else{
			$idsedeempresaadmin = 1; // g & m - villa
		}
		$this->db->distinct();
		$this->db->select("(d.total_detalle::numeric) AS total_detalle_str",FALSE); 
		$this->db->select("v.idventa, v.ticket_venta, d.idempresaespecialidad, d.iddetalle, am.idatencionmedica, d.fecha_atencion_det, v.orden_venta,pm.idproductomaster, (pm.descripcion) AS producto, pm.pertenece_tercero, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno,
			ee.porcentaje, ee.productos_tercero,ee.porcentaje_o_fijo");
		$this->db->select("3 AS idmediopago, 'web' AS descripcion_med",FALSE);
		// $this->db->select('mp.idmediopago, mp.descripcion_med');
		$this->db->select('e.idespecialidad,(e.nombre) AS especialidad,(emp_terc.descripcion) AS empresa');
		$this->db->select('0 AS total_detalle_costo');
		
		$this->db->from('ce_venta v'); 
		$this->db->join('ce_detalle d','v.idventa = d.idventa');
		$this->db->join('pa_prog_cita ppc','ppc.idprogcita = d.idprogcita');
		$this->db->join('cliente c','ppc.idcliente = c.idcliente', 'left'); // PUEDE QUE NO TENGA CLIENTE, SI ES SALUD OCUPACIONAL 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('especialidad e','d.idespecialidad = e.idespecialidad'); 
		$this->db->join('empresa_especialidad ee','d.idempresaespecialidad = ee.idempresaespecialidad');
		$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle');
		$this->db->join('empresa emp_adm','ed.idempresaadmin = emp_adm.idempresa AND emp_adm.estado_em = 1'); 
		$this->db->join('empresa emp_terc','ed.idempresatercera = emp_terc.idempresa AND emp_terc.estado_em = 1'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle AND estado_am = 1','left'); // PARA SALUD OCUPACIONAL 
		$this->db->where('d.fecha_atencion_det BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
			$this->db->where('d.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
		}
		$this->db->where('emp_adm.idempresa', $paramDatos['empresaSoloAdmin']['id']);
		$this->db->where('v.idsedeempresaadmin', $idsedeempresaadmin);
		$this->db->where('d.paciente_atendido_det', 1); // SI 
		$this->db->where('v.estado', 1); // ACTIVO 
		$this->db->order_by('d.fecha_atencion_det ASC');
		return $this->db->get()->result_array();

	}
	public function m_cargar_consolidado_atendidos_para_terceros($paramDatos)
	{ 
		$sql = 'SELECT sc_tr.idempresaespecialidad, sc_tr.idempresa, sc_tr.especialidad, sc_tr.empresa, sc_tr.ruc, sc_tr.representante_legal, sc_tr.pertenece_tercero, 
			SUM( sc_tr.monto_total_sin_comision ) * (
				( CASE WHEN pertenece_tercero = 2 THEN sc_tr.porcentaje::NUMERIC ELSE 100::NUMERIC END )  / 100 
			) AS monto_total_tercero 
			FROM (
				SELECT 
					"ee"."idempresaespecialidad",
					SUM (d.total_detalle::NUMERIC) AS monto_total,
					SUM (
						CASE
						WHEN (descripcion_med) = ? THEN
							(
								d.total_detalle::NUMERIC - (d.total_detalle::NUMERIC * 0.05)
							)
						ELSE
							d.total_detalle::NUMERIC
						END
					) AS monto_total_sin_comision,
					"mp"."idmediopago",
					"e"."idespecialidad",
					(e.nombre) AS especialidad,
					emp.idempresa,
					(emp.descripcion) AS empresa,
					("emp"."ruc_empresa") AS ruc,
					"emp"."representante_legal",
					("ee"."porcentaje") AS porcentaje,
					"ee"."productos_tercero",
					pm.pertenece_tercero
				FROM
					"venta" "v"
				JOIN "medio_pago" "mp" ON "v"."idmediopago" = "mp"."idmediopago"
				JOIN "especialidad" "e" ON "v"."idespecialidad" = "e"."idespecialidad"
				JOIN "empresa_especialidad" "ee" ON "v"."idempresaespecialidad" = "ee"."idempresaespecialidad"
				JOIN "empresa" "emp" ON "ee"."idempresa" = "emp"."idempresa" AND "estado_em" = 1
				JOIN "detalle" "d" ON "v"."idventa" = "d"."idventa" 
				JOIN "producto_master" "pm" ON "d"."idproductomaster" = "pm"."idproductomaster" 
				JOIN "atencion_medica" "am" ON "d"."iddetalle" = "am"."iddetalle"
				WHERE 
					fecha_atencion BETWEEN ?
				AND ?
				AND "estado_am" = 1
				AND "v"."estado" = 1
				AND "idsedeempresaadmin" = ? 
				AND estado_emes = 1 
				GROUP BY
					"ee"."idempresaespecialidad", ee.porcentaje, pm.pertenece_tercero, mp.idmediopago, e.idespecialidad, emp.idempresa, emp.descripcion, emp.ruc_empresa, "emp"."representante_legal", "ee"."productos_tercero"
				ORDER BY
					e.nombre ASC 
			) AS sc_tr 
			GROUP BY sc_tr.idempresaespecialidad, sc_tr.especialidad, sc_tr.empresa, sc_tr.idempresa, sc_tr.ruc, sc_tr.representante_legal, sc_tr.porcentaje, pertenece_tercero 
			HAVING (SUM( sc_tr.monto_total_sin_comision ) * (sc_tr.porcentaje::NUMERIC / 100))::NUMERIC > 0 
			ORDER BY
			sc_tr.especialidad ASC 
		';
		$query = $this->db->query($sql,
			array(
				'VISA',
				$paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto'],
				$paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'],
				$this->sessionHospital['idsedeempresaadmin']
			)
		);
		//return $this->db->get()->result_array();
		return $query->result_array();
		// $fEmpresa = $query->result_array();
		// // $this->db->distinct(); 
		// $this->db->select("SUM(d.total_detalle) AS monto_total, SUM( CASE WHEN descripcion_med = 'VISA' THEN (d.total_detalle - ( d.total_detalle * 0.05 )) ELSE d.total_detalle END ) AS monto_total_sin_comision",FALSE);
		// $this->db->select("SUM( (d.total_detalle - ( d.total_detalle * 0.05 )) ) * ( ee.porcentaje / 100 ) AS monto_total_tercero",FALSE); 
		// // $this->db->select("(d.total_detalle::numeric) AS total_detalle_str",FALSE); 
		// $this->db->select("ee.idempresaespecialidad, mp.idmediopago, e.idespecialidad, (e.nombre) AS especialidad, (emp.descripcion) AS empresa, emp.ruc, emp.representante_legal, ee.porcentaje, ee.productos_tercero"); 
		// $this->db->from('venta v'); 
		// // $this->db->join('cliente c','v.idcliente = c.idcliente'); 
		// $this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		// $this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		// $this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad'); 
		// $this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1'); 
		// $this->db->join('detalle d','v.idventa = d.idventa'); 
		// // $this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		// $this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		// $this->db->where('fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		// // if(!empty($paramDatos['empresaespecialidad']) && $paramDatos['empresaespecialidad']['id'] !== 'ALL' ){ 
		// // 	$this->db->where('v.idempresaespecialidad', $paramDatos['empresaespecialidad']['id']);
		// // }
		// // $this->db->where('paciente_atendido_det', 1); // SI 
		// $this->db->where('estado_am', 1); // ATENDIDO 
		// $this->db->where('v.estado', 1); // ACTIVO 
		
		// $this->db->group_by('ee.idempresaespecialidad');
		// $this->db->order_by('fecha_atencion ASC');
		// return $this->db->get()->result_array();
	}
	public function m_validar_iddetalle_ventas_atendidas_hoy($iddetalle)
	{
		$this->db->select("COUNT(*) AS contador",FALSE);
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 
		$this->db->where('v.idespecialidad', @$this->sessionHospital['idespecialidad']); 
		$this->db->where('paciente_atendido_det', 1); // SI 
		
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		// Como ya es una venta atendida, entonces ahora se filtrara con EMPRESA/ESPECIALIDAD 
		// $this->db->where('idempresaespecialidad', $this->sessionHospital['idempresaespecialidad']); -- innecesario, porque ahi tendrá todas las ventas de sus especialidades 

		//$this->db->where('am.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
		//$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('am.iddetalle', $iddetalle); 
		//$this->db->group_by('am.idatencionmedica'); 
		return $this->db->get()->row_array();
	}
	public function m_validar_iddetalle_ventas_web_atendidas_hoy($iddetalle){
		$this->db->select("COUNT(*) AS contador",FALSE);
		$this->db->from('ce_venta v'); 
		$this->db->join('ce_detalle d','v.idventa = d.idventa');
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 
		$this->db->where('d.idespecialidad', @$this->sessionHospital['idespecialidad']); 
		$this->db->where('paciente_atendido_det', 1); // SI 
		
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		// Como ya es una venta atendida, entonces ahora se filtrara con EMPRESA/ESPECIALIDAD 
		// $this->db->where('idempresaespecialidad', $this->sessionHospital['idempresaespecialidad']); -- innecesario, porque ahi tendrá todas las ventas de sus especialidades 

		//$this->db->where('am.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE 
		//$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('am.iddetalle', $iddetalle); 
		//$this->db->group_by('am.idatencionmedica'); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_resumen_atencion_medica($datos)
	{
		$this->db->select('COUNT(*) AS count_cancelados',FALSE); 
		$this->db->select('SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE); 
		$this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE); 
		$this->db->select('SUM( total_a_pagar::numeric ) AS sum_ingresos_numeric',FALSE); 
		$this->db->select('e.idespecialidad, e.nombre'); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		if(!empty($datos['idTipoAtencion']) && $datos['idTipoAtencion'] !== 'ALL' ) { 
			$this->db->where('am.tipo_atencion_medica', $datos['idTipoAtencion']); 
		}
		// var_dump($datos); exit(); 
		if(!empty($datos['idespecialidad']) && $datos['idespecialidad'] !== 'ALL'){
			$this->db->where('v.idespecialidad', $datos['idespecialidad']);
		}
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		// var_dump($datos['desde']); exit();
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) .' AND ' . $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->group_by('e.idespecialidad'); 
		//$this->db->group_by('am.idatencionmedica'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_producto_por_especialidad($paramPaginate,$paramDatos=FALSE, $rango)
	{
		$this->db->select('v.idventa, orden_venta, fecha_venta, tp.idtipoproducto, tp.nombre_tp, pm.idproductomaster, pm.descripcion AS producto, d.total_detalle, d.paciente_atendido_det');
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		$this->db->where('d.idespecialidad', $paramDatos['idespecialidad']); //  
		$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($rango['desde'].' '.$rango['desdeHora'].':'.$rango['desdeMinuto']) .' AND ' . $this->db->escape($rango['hasta'].' '.$rango['hastaHora'].':'.$rango['hastaMinuto'])); 
		// if( $paramPaginate['search'] ){ 
		// 	foreach ($paramPaginate['searchColumn'] as $key => $value) { 
		// 		if( !empty($value) ){ 
		// 			$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
		// 		} 
		// 	} 
		// } 
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_producto_por_especialidad($paramPaginate,$paramDatos=FALSE,$rango)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('SUM( d.total_detalle::numeric ) AS sum_ventas_especialidad',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		$this->db->where('d.idespecialidad', $paramDatos['idespecialidad']); //  
		$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($rango['desde'].' '.$rango['desdeHora'].':'.$rango['desdeMinuto']) .' AND ' . $this->db->escape($rango['hasta'].' '.$rango['hastaHora'].':'.$rango['hastaMinuto'])); 
		
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_resumen_pacientes($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS count_cancelados',FALSE); 
		$this->db->select('SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE); 
		$this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE); 
		$this->db->select('SUM( total_detalle::numeric ) AS sum_ingresos_numeric',FALSE); 
		$this->db->select('c.idcliente, c.num_documento, h.idhistoria, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento'); 
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('historia h','c.idcliente = h.idcliente');
		// if(!empty($datos['idTipoAtencion']) && $datos['idTipoAtencion'] !== 'ALL' ) { 
		// 	$this->db->where('am.tipo_atencion_medica', $datos['idTipoAtencion']); 
		// }
		// var_dump($datos); exit(); 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		// var_dump($datos['desde']); exit();
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->group_by('c.idcliente, h.idhistoria');
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}

		return $this->db->get()->result_array();
	}
	public function m_count_resumen_ventas_pacientes($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS count_cancelados',FALSE);
		
		$this->db->select('SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE); 
		$this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE);
		$this->db->select('SUM( total_detalle::numeric ) AS sum_ingresos_numeric',FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		//$this->db->join('historia h','c.idcliente = h.idcliente');
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_count_resumen_pacientes($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		
		// $this->db->select('SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END ) AS count_atendido',FALSE); 
		// $this->db->select('(COUNT(*)) - (SUM( CASE WHEN (paciente_atendido_v = 1) THEN 1 ELSE 0 END )) AS count_restante',FALSE);
		// $this->db->select('SUM( total_a_pagar::numeric ) AS sum_ingresos_numeric',FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('historia h','c.idcliente = h.idcliente');
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('v.estado <>', 0); // NO ANULADO 

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->group_by('c.idcliente', 'h.idhistoria');
		//$fData = $this->db->get()->row_array();
		$fData = $this->db->get()->num_rows();
		return $fData;
	}
	public function m_cargar_producto_por_paciente($paramPaginate,$paramDatos=FALSE, $rango)
	{
		$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->select('v.idventa, v.orden_venta, v.fecha_venta, tp.idtipoproducto, tp.nombre_tp, pm.idproductomaster, pm.descripcion AS producto, d.total_detalle, d.paciente_atendido_det');
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle AND am.estado_am = 1','left'); 
		$this->db->join('medico m','am.idmedico = m.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		$this->db->where('v.idcliente', $paramDatos['idcliente']); //  
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($rango['desde'].' '.$rango['desdeHora'].':'.$rango['desdeMinuto']) .' AND ' . $this->db->escape($rango['hasta'].' '.$rango['hastaHora'].':'.$rango['hastaMinuto'])); 
		// if( $paramPaginate['search'] ){ 
		// 	foreach ($paramPaginate['searchColumn'] as $key => $value) { 
		// 		if( !empty($value) ){ 
		// 			$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
		// 		} 
		// 	} 
		// } 
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_producto_por_paciente($paramPaginate,$paramDatos=FALSE,$rango)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('SUM( d.total_detalle::numeric ) AS sum_ventas_especialidad',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle AND am.estado_am = 1','left'); 
		$this->db->join('medico m','am.idmedico = m.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->where('v.estado <>', 0); // NO ANULADO 
		$this->db->where('v.idcliente', $paramDatos['idcliente']); //  
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($rango['desde'].' '.$rango['desdeHora'].':'.$rango['desdeMinuto']) .' AND ' . $this->db->escape($rango['hasta'].' '.$rango['hastaHora'].':'.$rango['hastaMinuto'])); 
		
		// if( $paramPaginate['search'] ){ 
		// 	foreach ($paramPaginate['searchColumn'] as $key => $value) { 
		// 		if( !empty($value) ){ 
		// 			$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
		// 		} 
		// 	} 
		// }
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_historial_atenciones_paciente($allInputs)
	{

		$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->select("(med.idmedico) AS idmedicoatencion, CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion", FALSE);
		$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('v.idventa, d.iddetalle, v.orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
			paciente_atendido_v, paciente_atendido_det, sub_total, total_igv, total_a_pagar, fecha_venta, v.ticket_venta, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, sexo, c.fecha_nacimiento, h.idhistoria, 
			am.idatencionmedica, anamnesis,presion_arterial_mm,presion_arterial_hg,frec_cardiaca,frec_respiratoria, examen_clinico, 
			temperatura_corporal, peso, talla, imc, perimetro_abdominal, antecedentes, observaciones, atencion_control, fecha_atencion, 
			aho.idareahospitalaria, descripcion_aho, he.fecha_ultima_regla, he.fecha_probable_parto, 
			pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp, e.idespecialidad, (e.nombre) AS especialidad,
			am.proc_observacion, am.proc_informe, am.ex_indicaciones, am.ex_informe, am.ex_tipo_resultado, am.ex_responsable_medico, doc_informe, 
			');
		$this->db->select("CONCAT_WS(' - ', ea.razon_social, s.descripcion) AS sede_empresa_admin");
		$this->db->from('venta v'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('historia h','c.idcliente = h.idcliente');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		//$this->db->join('atencion_medica am','v.orden_venta = am.orden_venta'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->join('medico m','am.ex_responsable_medico = m.idmedico','left'); // MEDICO QUE HIZO EL EXAMEN AUXILIAR // MEDICO RESPONSABLE 
		$this->db->join('medico med','am.idmedico = med.idmedico','left'); // MEDICO QUE ATENDIÓ AL PACIENTE 
		$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
		$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		// $this->db->where('DATE(fecha_atencion)', date('Y-m-d')); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('h.idhistoria', $allInputs['idhistoria']); // SI 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		// Como ya es una venta atendida, entonces ahora se filtrará con EMPRESA/ESPECIALIDAD 
		// $this->db->where('idempresaespecialidad', $this->sessionHospital['idempresaespecialidad']); -- innecesario, porque ahi tendrá todas las ventas de sus especialidades 
		// $this->db->where('am.idmedico', $this->sessionHospital['idmedico']); // MEDICO DE ATENCION MEDICA IMPORTANTE

		//innecesario en sistema multisede (09/09/2016)
		//$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		
		// $this->db->group_by('am.idatencionmedica'); 
		$this->db->order_by('am.fecha_atencion','DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_atenciones_reporte($paramDatos)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		if($paramDatos['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select(); 
		$this->db->reset_query(); 

		$this->db->select('d.total_detalle::NUMERIC AS total_detalle,e.nombre AS especialidad,te.descripcion AS tipo_especialidad,pm.descripcion AS producto,
			emp.descripcion AS empresa, sed.descripcion AS sede, cp.descripcion AS campania, pq.descripcion AS paquete, pq.monto_total AS total_paquete',FALSE);
		$this->db->select("CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente",FALSE);
		$this->db->select("CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medicoatencion",FALSE);
		$this->db->select('am.idatencionmedica, v.orden_venta, v.ticket_venta, am.fecha_atencion, v.fecha_venta, c.idcliente, h.idhistoria, 
		e.idespecialidad,emp.idempresa,pm.idproductomaster,tp.idtipoproducto,tp.nombre_tp,
		d.idempleado_desbloqueo,d.fecha_desbloqueo,d.tiene_autorizacion, cp.idcampania, pq.idpaquete'); 
		$this->db->from('atencion_medica am');
		$this->db->join('detalle d','am.iddetalle = d.iddetalle AND "d"."paciente_atendido_det" = 1');
		$this->db->join('venta v','d.idventa = v.idventa AND v.estado = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_especialidad te','e.idtipoespecialidad = te.idtipoespecialidad');
		$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad');
		$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1');
		$this->db->join('historia h','c.idcliente = h.idcliente'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('medico med','am.idmedico = med.idmedico','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede sed','sea.idsede = sed.idsede'); 
		$this->db->join('campania cp','d.idcampania = cp.idcampania','left'); 
		$this->db->join('paquete pq','d.idpaquete = pq.idpaquete','left'); 
		$this->db->where('am.fecha_atencion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		$this->db->where('am.estado_am',1);
		$this->db->where('v.estado',1);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa.')'); 
		$this->db->order_by('am.fecha_atencion','DESC');
		return $this->db->get()->result_array();

	}
	public function m_cargar_diagnosticos_de_atencion($datos)
	{
		$this->db->select('am.idatencionmedica, anamnesis, fecha_atencion, am.idareahospitalaria, descripcion_aho, tipo_diagnostico, codigo_cie, descripcion_cie, dc.iddiagnosticocie'); 
		$this->db->from('atencion_medica am'); 
		$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
		$this->db->join('atencion_por_diagnostico apd','am.idatencionmedica = apd.idatencionmedica'); 
		$this->db->join('diagnostico_cie dc','apd.iddiagnosticocie = dc.iddiagnosticocie'); 
		$this->db->where('am.idatencionmedica', $datos['idatencionmedica']); 
		$this->db->where('estado_am', 1);
		$this->db->limit(4);
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_recetas_de_atencion($datos)
	{
		$this->db->select('r.idreceta, indicaciones_generales, fecha_receta, idrecetamedicamento, cantidad, indicaciones, 
			m.idmedicamento, denominacion, descripcion, idunidadmedida, r.idatencionmedica'); 
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->where('estado_rem', 1);
		$this->db->where('r.idatencionmedica', $datos['idatencionmedica']);
		// if( $paramPaginate['sortName'] ){
		// 	$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		// }
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_diagnostico_de_atencion($idDiagnostico,$idAtencionMedica)
	{
		$this->db->select('dc.iddiagnosticocie, codigo_cie, descripcion_cie, estado_cie');
		$this->db->from('diagnostico_cie dc');
		$this->db->join('atencion_por_diagnostico apd','dc.iddiagnosticocie = apd.iddiagnosticocie'); 
		$this->db->join('atencion_medica am','apd.idatencionmedica = am.idatencionmedica'); 
		$this->db->where('dc.iddiagnosticocie', $idDiagnostico);
		$this->db->where('am.idatencionmedica', $idAtencionMedica);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_get_especialidad_by_id($id){
		$this->db->select('nombre');
		$this->db->from('especialidad');
		$this->db->where('idespecialidad',$id);
		return $this->db->get()->row_array();

	}
	public function m_verificar_odontograma_sin_atencion_medica($datos){
		$this->db->from('odontograma');
		$this->db->where('idhistoria',$datos['idhistoria']);
		$this->db->where('idatencionmedica',null);
		$this->db->where('tipo',1);
		$this->db->order_by('idodontograma', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_actualizar_odontograma($idodontograma, $idatencionmedica){
		$data = array( 
			'idatencionmedica' => $idatencionmedica,
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idodontograma',$idodontograma);
		return $this->db->update('odontograma', $data);
	}
	public function m_registrar_atencion_medica($datos)
	{
		$data = array( 
			'idhistoria' => $datos['idhistoria'],
			'idmedico' => $this->sessionHospital['idmedico'],
			'idempresa' => $this->sessionHospital['idempresa'],
			'idespecialidad' => $this->sessionHospital['idespecialidad'], 
			'idsede' => NULL, // $this->sessionHospital['idsede'], 
			'idareahospitalaria' => $datos['id_area_hospitalaria'], 
			'anamnesis' => $datos['fInputs']['anamnesis'],
			'presion_arterial_mm' => (empty($datos['fInputs']['presion_arterial_mm']) ? NULL : $datos['fInputs']['presion_arterial_mm']),
			'presion_arterial_hg' => (empty($datos['fInputs']['presion_arterial_hg']) ? NULL : $datos['fInputs']['presion_arterial_hg']),
			'frec_cardiaca' => (empty($datos['fInputs']['frecuencia_cardiaca_lxm']) ? NULL : $datos['fInputs']['frecuencia_cardiaca_lxm']),
			'frec_respiratoria' => (empty($datos['fInputs']['frecuencia_respiratoria']) ? NULL : $datos['fInputs']['frecuencia_respiratoria']),
			'temperatura_corporal' => (empty($datos['fInputs']['temperatura_corporal']) ? NULL : $datos['fInputs']['temperatura_corporal']),
			'peso' => (empty($datos['fInputs']['peso']) ? NULL : $datos['fInputs']['peso']),
			'talla' => (empty($datos['fInputs']['talla']) ? NULL : $datos['fInputs']['talla']),
			'imc' => (empty($datos['fInputs']['imc']) ? NULL : $datos['fInputs']['imc']),
			'perimetro_abdominal' => (empty($datos['fInputs']['perimetro_abdominal']) ? NULL : $datos['fInputs']['perimetro_abdominal']),
			'antecedentes' => (empty($datos['fInputs']['antecedentes']) ? NULL : $datos['fInputs']['antecedentes']),
			'observaciones' => (empty($datos['fInputs']['observaciones']) ? NULL : $datos['fInputs']['observaciones']),
			'examen_clinico' => $datos['fInputs']['examen_clinico'],
			'atencion_control' => $datos['fInputs']['atencion_control'],
			'fecha_atencion' => date('Y-m-d H:i:s'),
			'tipo_atencion_medica' => 'CM',
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden'],
			'ticket_venta' => $datos['ticket'], 
			'iddetalle' => $datos['iddetalle'],
			'origen_venta' => $datos['origen_venta']
		);
		return $this->db->insert('atencion_medica', $data);
	}
	public function m_registrar_atencion_medica_procedimiento($datos)
	{
		$data = array( 
			'idhistoria' => $datos['idhistoria'],
			'idmedico' => $this->sessionHospital['idmedico'],
			'idempresa' => $this->sessionHospital['idempresa'],
			'idespecialidad' => $this->sessionHospital['idespecialidad'], 
			'idsede' => NULL, // $this->sessionHospital['idsede'], 
			'idareahospitalaria' => $datos['id_area_hospitalaria'], 
			'proc_observacion' => (empty($datos['observacion']) ? NULL : $datos['observacion']),
			'proc_informe' => nl2br($datos['proc_informe']),
			'fecha_atencion' => date('Y-m-d H:i:s'),
			'tipo_atencion_medica' => 'P',
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden'],
			'ticket_venta' => $datos['ticket'], 
			'iddetalle' => $datos['iddetalle']
		);
		return $this->db->insert('atencion_medica', $data);
	}
	public function m_registrar_atencion_medica_examen_auxiliar($datos)
	{
		$data = array( 
			'idhistoria' => $datos['idhistoria'],
			'idmedico' => $this->sessionHospital['idmedico'],
			'idempresa' => $this->sessionHospital['idempresa'],
			'idespecialidad' => $this->sessionHospital['idespecialidad'], 
			'idsede' => NULL, // $this->sessionHospital['idsede'], 
			'idareahospitalaria' => $datos['id_area_hospitalaria'], 
			'ex_indicaciones' => (empty($datos['indicaciones']) ? NULL : $datos['indicaciones']),
			'ex_informe' => nl2br($datos['ex_informe']),
			'ex_tipo_resultado' => $datos['tipoResultado'],
			'ex_responsable_medico' => $datos['personal']['id'],
			'fecha_atencion' => date('Y-m-d H:i:s'),
			'tipo_atencion_medica' => 'EA',
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden'],
			'ticket_venta' => $datos['ticket'], 
			'iddetalle' => $datos['iddetalle']
		);
		return $this->db->insert('atencion_medica', $data);
	}
	public function m_registrar_atencion_documentos($datos)
	{
		$data = array( 
			'idhistoria' => $datos['idhistoria'],
			'idmedico' => $this->sessionHospital['idmedico'],
			'idempresa' => $this->sessionHospital['idempresa'],
			'idespecialidad' => $this->sessionHospital['idespecialidad'], 
			'idsede' => NULL, // $this->sessionHospital['idsede'], 
			'idareahospitalaria' => $datos['id_area_hospitalaria'], 
			'fecha_atencion' => date('Y-m-d H:i:s'),
			'tipo_atencion_medica' => 'DO',
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden'],
			'ticket_venta' => $datos['ticket'], 
			'iddetalle' => $datos['iddetalle'],
			'doc_informe' => $datos['doc_informe'],
			'doc_idcontingencia' => empty($datos['idcontingencia'])? null : $datos['idcontingencia'],
			'doc_fec_otorgamiento' => empty($datos['fecha_otorgamiento'])? null : $datos['fecha_otorgamiento'],
			'doc_fec_inicio_descanso' => empty($datos['fecha_iniciodescanso'])? null : $datos['fecha_iniciodescanso'],
			'doc_total_dias' => empty($datos['dias'])? null : $datos['dias'],
			'tipodocumento' => $datos['tipodocumento']
		);
		return $this->db->insert('atencion_medica', $data);
	}
	function m_actualizar_solicitudCitt($idsolicitudcitt, $idatencionmedica){

	}
	public function m_registrar_atencion_diagnostico($datos,$idAtencionMedica)
	{
		$data = array( 
			'idatencionmedica' => $idAtencionMedica,
			'iddiagnosticocie' => $datos['id'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'tipo_diagnostico' => $datos['tipo']
		);
		return $this->db->insert('atencion_por_diagnostico', $data);
	}
	public function m_registrar_historico_embarazo($datos)
	{
		$data = array( 
			'idhistoria' => $datos['idhistoria'],
			'idatencionmedica' => $datos['idatencionmedica'],
			'fecha_ultima_regla' => (empty($datos['fInputs']['fur']) ? NULL : $datos['fInputs']['fur']),
			'fecha_probable_parto' => (empty($datos['fInputs']['fpp']) ? NULL : $datos['fInputs']['fpp']), // fecha probable de parto 
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('historico_embarazo', $data);
	}
	public function m_editar_atencion_medica($datos)
	{ 
		if(@$datos['desdeHistorial'] === 'si'){ // EDICION DESDE HISTORIAL DE ATENCIONES
			$data = array( 
				'anamnesis' => $datos['anamnesis'],
				'presion_arterial_mm' => (empty($datos['presion_arterial_mm']) ? NULL : $datos['presion_arterial_mm']),
				'presion_arterial_hg' => (empty($datos['presion_arterial_hg']) ? NULL : $datos['presion_arterial_hg']),
				'frec_cardiaca' => (empty($datos['frecuencia_cardiaca_lxm']) ? NULL : $datos['frecuencia_cardiaca_lxm']),
				'frec_respiratoria' => (empty($datos['frecuencia_respiratoria']) ? NULL : $datos['frecuencia_respiratoria']),
				'temperatura_corporal' => (empty($datos['temperatura_corporal']) ? NULL : $datos['temperatura_corporal']),
				'peso' => (empty($datos['peso']) ? NULL : $datos['peso']),
				'talla' => (empty($datos['talla']) ? NULL : $datos['talla']),
				'imc' => (empty($datos['imc']) ? NULL : $datos['imc']),
				'perimetro_abdominal' => (empty($datos['perimetro_abdominal']) ? NULL : $datos['perimetro_abdominal']),
				'antecedentes' => (empty($datos['antecedentes']) ? NULL : $datos['antecedentes']),
				'observaciones' => (empty($datos['observaciones']) ? NULL : $datos['observaciones']),
				'examen_clinico' => $datos['examen_clinico'],
				'atencion_control' => $datos['atencion_control'],
				'updatedAt' => date('Y-m-d H:i:s')
			); 
		}else{
			$data = array( 
				'anamnesis' => $datos['fInputs']['anamnesis'],
				'presion_arterial_mm' => (empty($datos['fInputs']['presion_arterial_mm']) ? NULL : $datos['fInputs']['presion_arterial_mm']),
				'presion_arterial_hg' => (empty($datos['fInputs']['presion_arterial_hg']) ? NULL : $datos['fInputs']['presion_arterial_hg']),
				'frec_cardiaca' => (empty($datos['fInputs']['frecuencia_cardiaca_lxm']) ? NULL : $datos['fInputs']['frecuencia_cardiaca_lxm']),
				'frec_respiratoria' => (empty($datos['fInputs']['frecuencia_respiratoria']) ? NULL : $datos['fInputs']['frecuencia_respiratoria']),
				'temperatura_corporal' => (empty($datos['fInputs']['temperatura_corporal']) ? NULL : $datos['fInputs']['temperatura_corporal']),
				'peso' => (empty($datos['fInputs']['peso']) ? NULL : $datos['fInputs']['peso']),
				'talla' => (empty($datos['fInputs']['talla']) ? NULL : $datos['fInputs']['talla']),
				'imc' => (empty($datos['fInputs']['imc']) ? NULL : $datos['fInputs']['imc']),
				'perimetro_abdominal' => (empty($datos['fInputs']['perimetro_abdominal']) ? NULL : $datos['fInputs']['perimetro_abdominal']),
				'antecedentes' => (empty($datos['fInputs']['antecedentes']) ? NULL : $datos['fInputs']['antecedentes']),
				'observaciones' => (empty($datos['fInputs']['observaciones']) ? NULL : $datos['fInputs']['observaciones']),
				'examen_clinico' => $datos['fInputs']['examen_clinico'],
				'atencion_control' => $datos['fInputs']['atencion_control'],
				'updatedAt' => date('Y-m-d H:i:s')
			); 
		}
		
		$this->db->where('idatencionmedica',$datos['num_acto_medico']);
		return $this->db->update('atencion_medica', $data);
	}
	public function m_editar_atencion_medica_procedimiento($datos)
	{
		$data = array( 
			'proc_observacion' => (empty($datos['observacion']) ? NULL : $datos['observacion']),
			'proc_informe' => nl2br($datos['proc_informe']),
			'updatedAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden']
		);
		$this->db->where('idatencionmedica',$datos['num_acto_medico']);
		return $this->db->update('atencion_medica', $data);
	}
	public function m_editar_atencion_medica_examen_auxiliar($datos)
	{
		$data = array( 
			'ex_indicaciones' => (empty($datos['indicaciones']) ? NULL : $datos['indicaciones']),
			'ex_informe' => nl2br($datos['ex_informe']),
			'ex_tipo_resultado' => $datos['tipoResultado'],
			'ex_responsable_medico' => $datos['personal']['id'],
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		// var_dump("<pre>",$datos); exit();
		$this->db->where('idatencionmedica',$datos['num_acto_medico']);
		return $this->db->update('atencion_medica', $data);
	}
	public function m_editar_atencion_documentos($datos)
	{
		$data = array( 
			'updatedAt' => date('Y-m-d H:i:s'),
			'doc_informe' => nl2br($datos['doc_informe'])
		);
		$this->db->where('idatencionmedica',$datos['num_acto_medico']);
		return $this->db->update('atencion_medica', $data);
	}
	public function m_editar_atencion_diagnostico($datos,$idAtencionMedica)
	{
		$data = array( 
			'updatedAt' => date('Y-m-d H:i:s'),
			'tipo_diagnostico' => $datos['tipo']
		);
		$this->db->where('iddiagnosticocie',$datos['id']);
		$this->db->where('idatencionmedica',$idAtencionMedica);
		return $this->db->update('atencion_por_diagnostico', $data);
	}
	public function m_anular_atencion_medica($id)
	{
		$data = array( 
			'estado_am' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser_anula' => $this->sessionHospital['idusers'],
			'fecha_anulacion' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idatencionmedica',$id);
		return $this->db->update('atencion_medica', $data);
	}
	public function m_actualizar_venta_a_no_atendido($idVenta)
	{
		$data = array( 
			'fecha_atencion_v' => NULL,
			'paciente_atendido_v' => 2,
			'idempresaespecialidad' => NULL,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idventa',$idVenta);
		return $this->db->update('venta', $data);
	}
	public function m_actualizar_detalle_venta_a_no_atendido($idDetalle)
	{
		$data = array( 
			'fecha_atencion_det' => NULL,
			'paciente_atendido_det' => 2,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('iddetalle',$idDetalle); 
		return $this->db->update('detalle', $data); 
	}
	public function m_eliminar_atencion_diagnostico($idDiagnostico,$idAtencionMedica)
	{
		$query_result = $this->db->delete('atencion_por_diagnostico', array('idatencionmedica' => $idAtencionMedica, 'iddiagnosticocie' => $idDiagnostico )); 
		if(!$query_result) {
		    return false;
		}else{
			return true;
		}
	}
	public function m_cargar_descanso_medico_para_impresion($id_atencion_medica)
	{
		$this->db->select('idhistoria, idmedico'); 
		$this->db->from('atencion_medica'); 
		//$this->db->join('',''); 
		$this->db->where('idatencionmedica', $id_atencion_medica); 
		return $this->db->get()->result_array(); 
	}
	/* CONSULTA PARA REPORTES */
	public function lista_especialidades_vendidas_entre_fechas($allInputs)
	{
		
		$this->db->distinct(); 
		$this->db->select('e.idespecialidad, (e.nombre) AS especialidad'); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		if( empty($allInputs['sedeempresa']) ){
			$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{
			$this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']); 
		}
		
		$this->db->order_by('e.nombre'); // ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_medicos_atenciones_entre_fechas_de_especialidad($allInputs,$fila)
	{
		$this->db->distinct(); 
		$this->db->select("m.idmedico, CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->join('medico m','am.idmedico = m.idmedico'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('e.idespecialidad', $fila['idespecialidad']); // ATENDIDO 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		if( empty($allInputs['sedeempresa']) ){
			$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{
			$this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']); 
		}
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// $this->db->order_by('e.nombre'); // ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_totalizado_atenciones_entre_fechas_de_especialidad($allInputs,$filaEsp,$filaMed) 
	{

		$this->db->select("fecha_atencion,idatencionmedica,TO_CHAR(fecha_atencion,'HH24MI') AS hora, DATE(fecha_atencion) AS fecha, d.total_detalle::numeric, d.cantidad", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		$this->db->join('medico m','am.idmedico = m.idmedico'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta'])); 
		// $this->db->where("TO_CHAR(fecha_atencion,'HH24MI') BETWEEN ".$this->db->escape($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) .' AND ' . $this->db->escape($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('e.idespecialidad', $filaEsp['idespecialidad']); // ATENDIDO 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( empty($allInputs['sedeempresa']) ){
			$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{
			$this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']); 
		}
		$this->db->where('m.idmedico', $filaMed['idmedico']); 
		// $this->db->group_by("am.fecha_atencion, ROUND(extract('epoch' FROM to_timestamp( TO_CHAR(DATE(am.fecha_atencion),'YYYYMMDDHH24MISS')+'08:00','YYYYMMDDHH24') ) / 300)");
		$this->db->order_by('DATE(fecha_atencion) DESC'); // ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_totalizado_atenciones_entre_fechas_por_especialidades($allInputs,$filaEsp) 
	{
		$this->db->select("fecha_atencion,idatencionmedica,TO_CHAR(fecha_atencion,'HH24MI') AS hora, DATE(fecha_atencion) AS fecha, d.total_detalle::numeric, d.cantidad", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 
		//$this->db->join('medico m','am.idmedico = m.idmedico'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta'])); 
		// $this->db->where("TO_CHAR(fecha_atencion,'HH24MI') BETWEEN ".$this->db->escape($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) .' AND ' . $this->db->escape($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('e.idespecialidad', $filaEsp['idespecialidad']); // ATENDIDO 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		 $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		
		// $this->db->group_by("am.fecha_atencion, ROUND(extract('epoch' FROM to_timestamp( TO_CHAR(DATE(am.fecha_atencion),'YYYYMMDDHH24MISS')+'08:00','YYYYMMDDHH24') ) / 300)");
		$this->db->order_by('DATE(fecha_atencion) DESC'); // ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_totalizado_producto_de_especialidad($allInputs,$filaEsp,$filaMed) 
	{
		// $this->db->select_distinct();
		$this->db->select("CASE WHEN (cp.idcampania IS NULL) THEN (pm.descripcion) ELSE (pm.descripcion || ' (*' || cp.descripcion || '*)' ) END  AS descripcion", FALSE);
		$this->db->select("SUM(d.total_detalle) AS monto, SUM(d.total_detalle::numeric) AS monto_num, SUM(cantidad) AS cantidad", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 

		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('medico m','am.idmedico = m.idmedico'); 
		$this->db->join('detalle_paquete dp','pm.idproductomaster = dp.idproductomaster AND dp.idpaquete = d.idpaquete AND dp.estado = 1','left'); 
		$this->db->join('paquete pq','dp.idpaquete = pq.idpaquete AND pq.estado = 1','left'); 
		$this->db->join('campania cp','pq.idcampania = cp.idcampania AND cp.estado = 1','left'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta'])); 
		// $this->db->where("TO_CHAR(fecha_atencion,'HH24MI') BETWEEN ".$this->db->escape($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) .' AND ' . $this->db->escape($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('e.idespecialidad', $filaEsp['idespecialidad']); // ATENDIDO 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( empty($allInputs['sedeempresa']) ){
			$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{
			$this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']); 
		}
		$this->db->where('m.idmedico', $filaMed['idmedico']); 
		$this->db->group_by('pm.idproductomaster,cp.idcampania'); 
		// $this->db->group_by("am.fecha_atencion, ROUND(extract('epoch' FROM to_timestamp( TO_CHAR(DATE(am.fecha_atencion),'YYYYMMDDHH24MISS')+'08:00','YYYYMMDDHH24') ) / 300)");
		// $this->db->order_by('cantidad','DESC'); // ACTIVO 
		$this->db->order_by('pm.descripcion','ASC');// ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_totalizado_producto_por_especialidades($allInputs,$filaEsp) 
	{
		$this->db->select("pm.descripcion, SUM(d.total_detalle) AS monto, SUM(d.total_detalle::numeric) AS monto_num, SUM(cantidad) AS cantidad", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 

		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		//$this->db->join('medico m','am.idmedico = m.idmedico'); 
		$this->db->where('DATE(fecha_atencion) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta'])); 
		// $this->db->where("TO_CHAR(fecha_atencion,'HH24MI') BETWEEN ".$this->db->escape($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) .' AND ' . $this->db->escape($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']));
		$this->db->where('paciente_atendido_det', 1); // SI 
		$this->db->where('e.idespecialidad', $filaEsp['idespecialidad']); // ATENDIDO 
		$this->db->where('estado_am', 1); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		//$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( empty($allInputs['sedeempresa']) ){
			$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{
			$this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']); 
		}
		
		$this->db->group_by('pm.idproductomaster'); 
		// $this->db->group_by("am.fecha_atencion, ROUND(extract('epoch' FROM to_timestamp( TO_CHAR(DATE(am.fecha_atencion),'YYYYMMDDHH24MISS')+'08:00','YYYYMMDDHH24') ) / 300)");
		// $this->db->order_by('cantidad','DESC'); // ACTIVO 
		$this->db->order_by('pm.descripcion','ASC');// ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_productos_vendidos_por_especialidad_entre_fechas($allInputs)
	{
		$this->db->distinct(); 
		$this->db->select('e.idespecialidad, (e.nombre) AS especialidad'); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->where('DATE(fecha_venta) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('v.estado', 1); // ACTIVO 
		 $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->order_by('e.nombre'); // ACTIVO 
		return $this->db->get()->result_array();
	}
	public function lista_totalizado_producto_vendido_por_especialidades($allInputs,$filaEsp) 
	{
		$this->db->select("pm.descripcion, SUM(d.total_detalle) AS monto, SUM(d.total_detalle::numeric) AS monto_num, SUM(cantidad) AS cantidad", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('especialidad e','v.idespecialidad = e.idespecialidad'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		//$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle'); 

		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->where('DATE(fecha_venta) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta'])); 

		$this->db->where('e.idespecialidad', $filaEsp['idespecialidad']); // ATENDIDO 
		$this->db->where('v.estado', 1); // ACTIVO 
		 $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		
		$this->db->group_by('pm.idproductomaster'); 
		$this->db->order_by('monto','DESC');// ACTIVO 
		return $this->db->get()->result_array();
	}
	/* SUBIDA DE DOCUMENTOS A LA ATENCION EXAMEN AUXILIAR */
	public function m_cargar_archivos_atencion_examen_auxiliar($paramPaginate,$paramDatos){ 
		$this->db->select('idatencionarchivo, u.idusers, nombre_archivo, fecha_subida, u.username, titulo'); 
		$this->db->from('atencion_medica_archivo ama');
		$this->db->join('users u','ama.idusersubida = u.idusers'); 
		$this->db->where('estado_dea', 1); // activo 
		$this->db->where('idatencionmedica', $paramDatos['idatencionmedica']);
		if( !empty($paramPaginate['search']) ){ 
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
	public function m_count_archivos_atencion_examen_auxiliar($paramPaginate,$paramDatos) 
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('atencion_medica_archivo ama');
		$this->db->join('users u','ama.idusersubida = u.idusers'); 
		//$this->db->where('estado_de', 1); // activo 
		$this->db->where('idatencionmedica', $paramDatos['idatencionmedica']);
		if( !empty($paramPaginate['search']) ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_registrar_documento($datos)
	{
		$data = array( 
			// 'idusersubida' => $this->sessionHospital['idusers'],
			// 'idempleadosubida' => $this->sessionHospital['idempleado'],
			'idatencionmedica' => $datos['num_acto_medico'],
			'titulo' => $datos['titulo'],
			'nombre_archivo' => $datos['nombre_archivo'],
			'tipoarchivo' => $datos['extension'],
			'fecha_subida' => date('Y-m-d H:i:s'),
			'idusersubida' => $this->sessionHospital['idusers'],
		);
		return $this->db->insert('atencion_medica_archivo', $data);
	}
	public function m_anular_documento($id)
	{
		$data = array(
			'estado_dea' => 0,
			'fecha_eliminacion' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idatencionarchivo',$id);
		if($this->db->update('atencion_medica_archivo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_bloquear_tickets_atencion()
	{
		$data = array(
			'tiene_autorizacion' => 2
		);
		$this->db->where('fecha_atencion_det IS NULL');
		$this->db->where('tiene_autorizacion',1);
		if($this->db->update('detalle', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_modificar_empresa_de_venta($datos)
	{
		$data = array(
			'idempresaespecialidad' => $datos['empresa']['id'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idventa',$datos['idventa']);
		if($this->db->update('venta', $data)){
			return true;
		}else{
			return false;
		}
	} 
	public function m_modificar_empresa_de_atencion($datos)
	{
		$data = array(
			'idempresa' => $datos['empresa']['idempresa'],
			'updatedAt' => date('Y-m-d H:i:s'),
			'motivo_cambio_empresa' => @$datos['motivo']
		);
		$this->db->where('idatencionmedica',$datos['idatencionmedica']);
		if($this->db->update('atencion_medica', $data)){ 
			return true;
		}else{
			return false;
		}
	}
	public function m_cargar_ultimos_examenes_paciente($datos)
	{
		$this->db->select('idatencionmedica, am.fecha_atencion, pm.descripcion');
		$this->db->from('atencion_medica am');
		$this->db->join('detalle d', 'am.iddetalle = d.iddetalle');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->where('estado_am', 1);
		$this->db->where('idhistoria', $datos['idhistoria']);
		$this->db->where('tipo_atencion_medica', 'EA');
		$this->db->order_by('fecha_atencion','DESC');
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}

	public function cargar_fechas_atencion_campania($datos){
		$this->db->select('idfechacampania, idcampania ,fecha, tipo_fecha'); 
		$this->db->from('fecha_campania');
		$this->db->where('idcampania', $datos); // activo 
		$this->db->where('estado', 1);
		$this->db->where('tipo_fecha', 2);		
		$this->db->where('fecha', date('Y-m-d'));
		return $this->db->get()->result_array();		
	} 

	public function m_verifica_tiene_atencion($datos){
		$this->db->select('am.idatencionmedica'); 
		$this->db->from('atencion_medica am');
		$this->db->where('am.iddetalle', $datos['iddetalle']); 
		$this->db->where('am.estado_am',1); // activo 
		$this->db->where('am.origen_venta','W'); // web 
		return $this->db->get()->result_array();
	}

	public function m_actualizar_comprobante_web_en_atencion($datos){
		$data = array(
			'ticket_venta' => $datos['nro_comprobante'], 
		);
		$this->db->where('idatencionmedica',$datos['idatencionmedica']);
		return $this->db->update('atencion_medica', $data);
	}
}