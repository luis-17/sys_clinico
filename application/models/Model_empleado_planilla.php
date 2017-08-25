<?php
class Model_empleado_planilla extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_empleados_planilla($paramPaginate,$paramPlanilla){
		$this->db->select('empl.nombres, empl.apellido_paterno, empl.apellido_materno, empl.reg_pensionario, empl.tipo_comision', FALSE);
		$this->db->select('empl.numero_documento, empl.idtipodocumentorh');
		$this->db->select("concat_ws(' ', empl.nombres, empl.apellido_paterno, empl.apellido_materno) AS empleado", FALSE);
		$this->db->select("empl.cuspp", FALSE);
		$this->db->select('hh.fecha_ingreso, hh.condicion_laboral, hh.fecha_inicio_contrato, hh.fecha_fin_contrato, hh.sueldo_contrato::NUMERIC', FALSE);
		$this->db->select('pe.idplanillaempleado, empl.idempleado, pe.idplanilla, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->select('afp.idafp, afp.descripcion_afp, afp.estado_afp, afp.a_oblig, afp.comision, afp.p_seguro, afp.comision_m', FALSE); //datos afp
		$this->db->select('c.idcargo, c.descripcion_ca, cc.nombre_cc AS centro_costo, s.descripcion AS sede'); //datos cargo
		$this->db->select('pe.total_remuneraciones::NUMERIC, pe.total_descuentos::NUMERIC, pe.neto_a_pagar::NUMERIC',FALSE);
		$this->db->select('empl.fecha_nacimiento',FALSE);
				
		$this->db->from('rh_empleado empl');
		$this->db->join('empresa empr', 'empr.idempresa = '.$paramPlanilla['idempresa']);		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE);

		if($paramPlanilla['estado_pl'] == 2){ // cerrada
			$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $paramPlanilla['id'], '', FALSE); // planilla seleccionada
		}else{
			$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $paramPlanilla['id'], 'left', FALSE); // planilla seleccionada
		}
		$this->db->join('rh_afp afp', 'afp.idafp = empl.idafp','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left'); 
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto','left');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');
		//$this->db->where('empl.idempresa', $paramPlanilla['idempresa']);
		//$this->db->where('empl.condicion_laboral', 'EN PLANILLA');
		$this->db->where('empl.si_activo', 1);
		$this->db->where('empl.estado_empl <>', 0);

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

	public function m_count_empleados_planilla($paramPaginate,$paramPlanilla){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_empleado empl');
		$this->db->join('empresa empr', 'empr.idempresa = '.$paramPlanilla['idempresa']);		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE);
		if($paramPlanilla['estado_pl'] == 2){
			$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $paramPlanilla['id'], '', FALSE); // planilla seleccionada
		}else{
			$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $paramPlanilla['id'], 'left', FALSE); // planilla seleccionada
		}
		$this->db->join('rh_afp afp', 'afp.idafp = empl.idafp','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left'); 
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto','left');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');
		$this->db->where('empl.si_activo', 1);

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

	public function m_cargar_planillas_anteriores($idempleado, $anioInicio, $mesInicio){
		$this->db->select('pe.idplanillaempleado, pe.idempleado, pe.idplanilla, pe.total_remuneraciones,
							pe.total_descuentos,pe.neto_a_pagar, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->from('rh_planilla_empleado pe');
		$this->db->join('rh_planilla p', 'pe.idplanilla = p.idplanilla AND p.estado_pl = 2');
		$this->db->where('pe.idempleado',$idempleado);
		$this->db->where("DATE_PART('MONTH', p.fecha_cierre) >= ". $mesInicio); 		
		$this->db->where("DATE_PART('YEAR', p.fecha_cierre) >= ".$anioInicio); 		

		return $this->db->get()->result_array();		
	}

	public function m_cargar_estos_empleados_planilla($paramPlanilla, $paramEmpleados = FALSE){
		$this->db->select('empl.nombres, empl.apellido_paterno, empl.apellido_materno, empl.reg_pensionario, empl.tipo_comision', FALSE);
		$this->db->select('empl.idtipodocumentorh, td.descripcion_rtd AS tipo_documento, empl.numero_documento');
		$this->db->select("concat_ws(' ',empl.apellido_paterno, empl.apellido_materno, empl.nombres) AS empleado", FALSE);
		$this->db->select("empl.cuspp, empl.cuenta_corriente, empl.idbanco", FALSE);
		$this->db->select('hh.fecha_ingreso, hh.condicion_laboral, hh.fecha_inicio_contrato, hh.fecha_fin_contrato, hh.sueldo_contrato::NUMERIC', FALSE);
		$this->db->select('pe.idplanillaempleado, empl.idempleado, pe.idplanilla, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->select('afp.idafp, afp.descripcion_afp, afp.estado_afp, afp.a_oblig, afp.comision, afp.p_seguro, afp.comision_m', FALSE); //datos afp
		$this->db->select('c.idcargo, c.descripcion_ca, cc.nombre_cc AS centro_costo, s.descripcion AS sede'); //datos cargo
		$this->db->select('b.descripcion_banco'); //datos cargo
		$this->db->select('empl.fecha_nacimiento'); //fecha_nacimiento
				
		$this->db->from('rh_empleado empl');
		$this->db->join('empresa empr', 'empr.idempresa = '.$paramPlanilla['idempresa']);		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE); 
		$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $paramPlanilla['id'], 'left', FALSE); // planilla seleccionada
		$this->db->join('rh_afp afp', 'afp.idafp = empl.idafp','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left'); 
		$this->db->join('rh_tipo_documento td', 'empl.idtipodocumentorh = td.idtipodocumentorh','left'); 
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto','left');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');
		$this->db->join('ct_banco b', 'empl.idbanco = b.idbanco','left');
		$this->db->where('empl.si_activo', 1);

		if($paramEmpleados){
			$this->db->where_in('empl.idempleado', $paramEmpleados);
		}
		$this->db->order_by('empleado', 'ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_empleados_esta_planilla($idplanilla){
		$this->db->select('pe.idplanillaempleado, pe.idempleado, pe.idplanilla, pe.total_remuneraciones,
							pe.total_descuentos,pe.neto_a_pagar, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->select('pe.estado_pe');
		$this->db->from('rh_planilla_empleado pe');
		$this->db->join('rh_planilla p', 'pe.idplanilla = p.idplanilla AND p.estado_pl = 1');
		$this->db->where('pe.idplanilla', $idplanilla);

		return $this->db->get()->result_array();
	}

	public function m_cargar_empleados_calculados_planilla($idplanilla){
		$this->db->select('pe.idplanillaempleado, pe.idempleado, pe.idplanilla, pe.total_remuneraciones,
							pe.total_descuentos,pe.neto_a_pagar, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->select('pe.estado_pe');
		$this->db->select('empl.nombres, empl.apellido_paterno, empl.apellido_materno, empl.reg_pensionario, empl.tipo_comision', FALSE);
		$this->db->select('empl.idtipodocumentorh, td.descripcion_rtd AS tipo_documento, empl.numero_documento');

				
		$this->db->from('rh_empleado empl');
		$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.idplanilla = '.  $idplanilla, '', FALSE); // planilla seleccionada
		$this->db->join('rh_planilla p', 'pe.idplanilla = p.idplanilla');
		$this->db->join('empresa empr', 'empr.idempresa = p.idempresa');		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE); 
		$this->db->join('rh_afp afp', 'afp.idafp = empl.idafp','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left'); 
		$this->db->join('rh_tipo_documento td', 'empl.idtipodocumentorh = td.idtipodocumentorh','left'); 
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto','left');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');
		$this->db->join('ct_banco b', 'empl.idbanco = b.idbanco','left');
		$this->db->where('empl.si_activo', 1);

		return $this->db->get()->result_array();		
	}

	public function m_cargar_empleados_planilla_calculada($idplanilla){
		$this->db->select('empl.nombres, empl.apellido_paterno, empl.apellido_materno, empl.reg_pensionario, empl.tipo_comision', FALSE);
		$this->db->select('empl.idtipodocumentorh, td.descripcion_rtd AS tipo_documento, empl.numero_documento');
		$this->db->select("concat_ws(' ',empl.apellido_paterno, empl.apellido_materno, empl.nombres) AS empleado", FALSE);
		$this->db->select("empl.cuspp, empl.cuenta_corriente, empl.idbanco, b.descripcion_banco", FALSE);
		$this->db->select('hh.fecha_ingreso, hh.condicion_laboral, hh.fecha_inicio_contrato, hh.fecha_fin_contrato, hh.sueldo_contrato::NUMERIC', FALSE);
		$this->db->select('pe.idplanillaempleado, empl.idempleado, pe.idplanilla, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
		$this->db->select('afp.idafp, afp.descripcion_afp, afp.estado_afp, afp.a_oblig, afp.comision, afp.p_seguro, afp.comision_m, afp.cuenta_plan', FALSE);
		$this->db->select('c.idcargo, c.descripcion_ca, s.descripcion AS sede, emp.descripcion AS empresa, emp.descripcion_corta  AS alias_empresa');
		$this->db->select('cc.idcentrocosto, cc.nombre_cc AS centro_costo, scc.idsubcatcentrocosto, scc.descripcion_scc, scc.codigo_scc');
		$this->db->select('pe.total_remuneraciones::NUMERIC, pe.total_descuentos::NUMERIC, pe.neto_a_pagar::NUMERIC, p.fecha_cierre', FALSE);
		$this->db->select('pe.total_desc_reg_pensionario::NUMERIC, pe.renta_quinta::NUMERIC, pe.aportes_empleador::NUMERIC', FALSE);

		$this->db->from('rh_planilla_empleado pe');
		$this->db->join('rh_planilla p', 'pe.idplanilla = p.idplanilla');
		$this->db->join('rh_empleado empl', 'pe.idempleado = empl.idempleado');
		$this->db->join('empresa emp', 'p.idempresa = emp.idempresa');
		$this->db->join('empresa_admin emprad', 'emp.ruc_empresa = emprad.ruc');
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE);
		$this->db->join('rh_afp afp', 'afp.idafp = empl.idafp','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left'); 
		$this->db->join('rh_tipo_documento td', 'empl.idtipodocumentorh = td.idtipodocumentorh','left');  
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto');
		$this->db->join('ct_subcat_centro_costo scc', 'cc.idsubcatcentrocosto = scc.idsubcatcentrocosto');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');
		$this->db->join('ct_banco b', 'empl.idbanco = b.idbanco','left');
		$this->db->where('pe.idplanilla', $idplanilla);
		$this->db->where('estado_pe', 2); // calculada
		$this->db->order_by('empleado', 'ASC');
		return $this->db->get()->result_array();		
	}

	public function m_registrar($datos){
		$data = array(
			'idempleado' => $datos['idempleado'],
			'idplanilla' => $datos['idplanilla'],			
			'concepto_valor_json' => $datos['concepto_valor_json'],			
		);
		
		return $this->db->insert('rh_planilla_empleado', $data);		
	}

	public function m_actualizar($datos, $calculado = FALSE){
		$data = array(			
			'total_remuneraciones' => empty($datos['total_remuneraciones']) ? NULL : $datos['total_remuneraciones'],
			'total_descuentos' => empty($datos['total_descuentos']) ? NULL : $datos['total_descuentos'],
			'neto_a_pagar' => empty($datos['neto_a_pagar']) ? NULL : $datos['neto_a_pagar'],
			'total_desc_reg_pensionario' => empty($datos['total_desc_reg_pensionario']) ? NULL : $datos['total_desc_reg_pensionario'],
			'renta_quinta' => empty($datos['renta_quinta']) ? NULL : $datos['renta_quinta'],
			'aportes_empleador' => empty($datos['aportes_empleador']) ? NULL : $datos['aportes_empleador'],
			'concepto_valor_json' => $datos['concepto_valor_json'],	
			//'estado_pe' => $calculado ? 2 : 1,	
			'estado_pe' => empty($datos['estado_pe']) ? 1 : $datos['estado_pe'],	
		);
		
		$this->db->where('idplanillaempleado', $datos['idplanillaempleado']);
		return $this->db->update('rh_planilla_empleado', $data);		
	}

	public function m_cargar_empleados_planilla_anterior($paramPlanilla){
		$this->db->select('empl.idempleado, pe.idplanillaempleado, pe.idplanilla, pe.concepto_valor_json::JSON as concepto_valor_json', FALSE);
				
		$this->db->from('rh_empleado empl');
		$this->db->join('empresa empr', 'empr.idempresa = '.$paramPlanilla['idempresa']);		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado
						AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE);

		$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado AND pe.estado_pe IN (1,2) AND pe.idplanilla = '.  $paramPlanilla['id'], '', FALSE); // planilla seleccionada
		$this->db->where('empl.si_activo', 1);
		$this->db->where('empl.estado_empl', 1);
		
		return $this->db->get()->result_array();
	}

	public function m_cargar_planillas_anteriores_todos($datos, $indEmpl,$es_cts = FALSE){
		$this->db->select("empl.idempleado, pe.idplanillaempleado, pe.idplanilla, 
							pe.concepto_valor_json::JSON as concepto_valor_json,
							concat_ws(' ',empl.nombres, empl.apellido_paterno, empl.apellido_materno) AS empleado,
							hh.fecha_ingreso, hh.fecha_inicio_contrato, hh.fecha_fin_contrato, hh.sueldo_contrato,
							", FALSE);
		$this->db->select('empl.idtipodocumentorh,td.descripcion_rtd AS tipo_documento, empl.numero_documento');		
		$this->db->select('cc.nombre_cc AS centro_costo,empl.idcentrocosto');	
		$this->db->select('empl.cuenta_corriente, empl.idbanco');		
		$this->db->select("DATE_PART('month', p.fecha_cierre) as mes",FALSE);		
		$this->db->select("s.descripcion AS sede",FALSE);		
		$this->db->select('c.idcargo, c.descripcion_ca');
		$this->db->select(" ' ' as cuenta_corriente_cts, ' ' as banco_cts ");
			
		$this->db->from('rh_empleado empl');
		$this->db->join('empresa empr', 'empr.idempresa = '.$datos['idempresa']);		
		$this->db->join('empresa_admin emprad', 'emprad.ruc = empr.ruc_empresa');		
		$this->db->join("rh_historial_contrato hh", 
						"hh.idempleado = empl.idempleado
						AND hh.estado_hc = 1 
						AND hh.contrato_actual = 1 AND hh.condicion_laboral = 'EN PLANILLA' 
						AND hh.idempresaadmin = emprad.idempresaadmin", '', FALSE);
		$this->db->join('rh_planilla_empleado pe', 'pe.idempleado = empl.idempleado 
													AND pe.estado_pe IN (2)', '', FALSE); 
		$this->db->join('rh_planilla p', 'pe.idplanilla = p.idplanilla AND p.estado_pl = 2');
		$this->db->join('rh_tipo_documento td', 'empl.idtipodocumentorh = td.idtipodocumentorh','left'); 
		$this->db->join('rh_cargo c', 'c.idcargo = empl.idcargo','left');
		$this->db->join('ct_centro_costo cc', 'empl.idcentrocosto = cc.idcentrocosto','left');
		$this->db->join('sede s', 'empl.idsedeempleado = s.idsede','left');

		$this->db->where('empl.si_activo', 1);
		$this->db->where('empl.estado_empl', 1);

		$this->db->where_in('pe.idempleado', $indEmpl);
		if($es_cts){
			$this->db->where("to_char(p.fecha_cierre, 'YYYYMM') BETWEEN ". 
								$this->db->escape($datos['aniomes_desde']). " AND ". 
								$this->db->escape($datos['aniomes_hasta']));
		}else{			
			$this->db->where("DATE_PART('month', p.fecha_cierre) BETWEEN ". 
								$this->db->escape($datos['mes_desde']). " AND ". 
								$this->db->escape($datos['mes_hasta']));
		}
		
		$this->db->order_by('empl.idempleado ASC, p.fecha_cierre ASC');
		return $this->db->get()->result_array();
	}

	public function m_actualizar_solo_conceptos_empl($datos){
		$data = array(			
			'concepto_valor_json' => $datos['concepto_valor_json'],	
		);
		
		$this->db->where('idplanilla', $datos['idplanilla']);
		$this->db->where('idempleado', $datos['idempleado']);
		return $this->db->update('rh_planilla_empleado', $data);		
	}
}