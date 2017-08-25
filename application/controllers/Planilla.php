<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Planilla extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas','otros','contable_helper'));
		$this->load->model(array('model_planilla','model_planilla_master','model_concepto_planilla','model_config','model_empleado_planilla','model_sub_operacion'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_planillas(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramEmpresa = $allInputs['empresa'];
		$lista = $this->model_planilla->m_cargar_planillas($paramPaginate, $paramEmpresa);
		$totalRows = $this->model_planilla->m_count_planillas($paramEmpresa);
		$arrListado = array();
		foreach ($lista as $row) {
			$estado = array();
			if($row['estado_pl'] == 1){
				$estado = array(
					'label' => 'label-success',
					'str_estado' => 'ABIERTA',
					'boolean' => $row['estado_pl']
					);
			}else if($row['estado_pl'] == 2){
				$estado = array(
					'label' => 'label-default',
					'str_estado' => 'CERRADA',
					'boolean' => $row['estado_pl']
					);
			}
			$conceptos_json = objectToArray(json_decode($row['conceptos_json']));
			// var_dump($conceptos_json); exit();
			array_push($arrListado, 
				array(
					'id' => $row['idplanilla'],
					'idplanillamaster' => $row['idplanillamaster'],
					'idempresa' => $row['idempresa'],
					'descripcion_empresa' => $row['empresa'],
					'fecha_apertura' => date('d-m-Y', strtotime($row['fecha_apertura'])),
					'fecha_cierre' => date('d-m-Y', strtotime($row['fecha_cierre'])),
					'descripcion' => strtoupper($row['descripcion_pl']),
					'estado_pl' => $row['estado_pl'],
					'tiene_cts' => $row['tiene_cts'],
					'tiene_gratificacion' => $row['tiene_gratificacion'],
					'estado' => $estado,
					'conceptos_json' => $conceptos_json, //ya se generan varios niveles
					
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	
	public function ver_popup_planilla(){
		$this->load->view('planilla/planilla_formView');
	}	
	private function generar_asiento_contable($empleados_planilla){
		$datos = array( 'idoperacion' => 16 );
		$arrSubOperaciones = $this->model_sub_operacion->m_cargar_suboperacion_cbo($datos);
		$remuneraciones = 0;
		$netos_a_pagar = 0;
		$renta_quinta = 0;
		$aport_empleador = 0;
		$onp = 0;
		$arrAux = array();
		$arrCC = array();
		$arrRemCC = array();
		$arrAtePerCC = array();
		$arrAporCC = array();
		$arrRP = array();
		$arrAsiento = array();
		$periodo = darFormatoMesAno($empleados_planilla[0]['fecha_cierre']);
		$idplanilla = $empleados_planilla[0]['idplanilla'];
		foreach ($empleados_planilla as $row) {
			$conceptos = objectToArray(json_decode($row['concepto_valor_json']));
			$atencion_personal = 0;
			$vacaciones = 0;
			$vacaciones_truncas = 0;
			$gratificacion = 0;
			$bonificacion = 0;
			$gratificacion_trunc = 0;
			$bonificacion_trunc = 0;
			$cts = 0;
			$remuneraciones += $row['total_remuneraciones'];
			$netos_a_pagar += $row['neto_a_pagar'];
			$renta_quinta += $row['renta_quinta'];
			$aport_empleador += $row['aportes_empleador'];
			// var_dump($conceptos['calculos']);
			
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0705') == 1 && $conceptos['calculos']['faltas'] >= 1 ){
				$atencion_personal = ( (float)$conceptos['calculos']['importeFaltas'] ) + ( (float)$conceptos['calculos']['noDeducibles'] );
									 // ( (float)$conceptos['configuracion']['movilidad'] - (float)$conceptos['calculos']['movilidad'] ) +
									 // ( (float)$conceptos['configuracion']['refrigerio'] - (float)$conceptos['calculos']['refrigerio'] ) +
									 // ( (float)$conceptos['configuracion']['condicion_trabajo'] - (float)$conceptos['calculos']['condicion_trabajo'] );
				
			}
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0704') == 1 && $conceptos['calculos']['tardanzas'] > 0 ){
				$atencion_personal += (float)$conceptos['calculos']['importeTardanzas'];
			}

			if(obtenerEstadoConcepto($conceptos['conceptos'], '0118') == 1 ){
				$vacaciones = (float)obtenerValorConcepto($conceptos['conceptos'], '0118');
			}
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0114') == 1 ){
				$vacaciones_truncas = (float)obtenerValorConcepto($conceptos['conceptos'], '0114');
			}
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0406') == 1 ){
				$gratificacion = (float)obtenerValorConcepto($conceptos['conceptos'], '0406');
				$bonificacion = 0.09 * $gratificacion;
			}
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0407') == 1 ){
				$gratificacion_trunc = (float)obtenerValorConcepto($conceptos['conceptos'], '0407');
				$bonificacion_trunc = 0.09 * $gratificacion_trunc;
			}
			if(obtenerEstadoConcepto($conceptos['conceptos'], '0904') == 1 ){
				$cts = (float)obtenerValorConcepto($conceptos['conceptos'], '0904');
			}
			// var_dump($atencion_personal);
			$arrAux[$row['idcentrocosto']][] = array(
				'idempleado' 			=> $row['idempleado'],
				'empleado' 				=> $row['empleado'],
				'idplanilla' 			=> $row['idplanilla'],
				'total_remuneraciones'	=> (float)$row['total_remuneraciones'],
				'atencion_personal'		=> $atencion_personal,
				'vacaciones'			=> $vacaciones,
				'vacaciones_truncas'	=> $vacaciones_truncas,
				'gratificacion'			=> $gratificacion,
				'bonificacion'			=> $bonificacion,
				'gratificacion_trunc'	=> $gratificacion_trunc,
				'bonificacion_trunc'	=> $bonificacion_trunc,
				'cts'					=> $cts,
				'aportes_empleador' 	=> $row['aportes_empleador'],
				'idcentrocosto' 		=> $row['idcentrocosto'],
				'centro_costo'			=> $row['codigo_scc'].'-'.$row['centro_costo'],
				'empresa' 				=> $row['alias_empresa'],
			);
			if(!empty($row['idafp'])){
				$arrRP[$row['idafp']][] = array(
					'idafp' => $row['idafp'],
					'afp' => $row['descripcion_afp'],
					'codigo_plan' => $row['cuenta_plan'],
					'monto' => $row['total_desc_reg_pensionario'],
				); 
			}
			if( $row['reg_pensionario'] == 'ONP' ){
				$onp += $row['total_desc_reg_pensionario'];
			}
		}
		//var_dump($arrAux); exit();
		$total_vacaciones = 0;
		$total_vacaciones_truncas = 0;
		$total_gratificacion = 0;
		$total_bonificacion = 0;
		$total_cts = 0;
		foreach ($arrAux as $key => $row) {
			$remuneracion_cc = 0;
			$atencion_personal_cc = 0;
			$aportes_cc = 0;
			$vacaciones_cc = 0;
			$vacaciones_truncas_cc = 0;
			$gratificacion_cc = 0;
			$bonificacion_cc = 0;
			$gratificacion_trunc_cc = 0;
			$bonificacion_trunc_cc = 0;
			$cts_cc = 0;
			$empleados_rem = array();
			$empleados_vac = array();
			$empleados_vac_trunc = array();
			$empleados_grat = array();
			$empleados_bon = array();
			$empleados_grat_trunc = array();
			$empleados_bon_trunc = array();
			$empleados_cts = array();
			$empleados_atp = array();
			$empleados_apor = array();
			foreach ($row as $key => $value) {
				$remuneracion_cc += $value['total_remuneraciones'];
				$atencion_personal_cc += $value['atencion_personal'];
				$aportes_cc += $value['aportes_empleador'];
				$vacaciones_cc += $value['vacaciones'];
				$vacaciones_truncas_cc += $value['vacaciones_truncas'];
				$gratificacion_cc += $value['gratificacion'];
				$bonificacion_cc += $value['bonificacion'];
				$gratificacion_trunc_cc += $value['gratificacion_trunc'];
				$bonificacion_trunc_cc += $value['bonificacion_trunc'];
				$idcentrocosto = $value['idcentrocosto'];
				$centro_costo = $value['centro_costo'];
				$empresa = $value['empresa'];
				if( $value['total_remuneraciones'] > 0 ){
					array_push($empleados_rem, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['total_remuneraciones'],
						)
					);
				}
				if( $value['vacaciones'] > 0 ){
					array_push($empleados_vac, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['vacaciones'],
						)
					);
				}	
				if( $value['vacaciones_truncas'] > 0 ){
					array_push($empleados_vac_trunc, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['vacaciones_truncas'],
						)
					);
				}	
				if( $value['gratificacion'] > 0 ){
					array_push($empleados_grat, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['gratificacion'],
						)
					);
				}	
				if( $value['bonificacion'] > 0 ){
					array_push($empleados_bon, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['bonificacion'],
						)
					);
				}	
				if( $value['gratificacion_trunc'] > 0 ){
					array_push($empleados_grat_trunc, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['gratificacion_trunc'],
						)
					);
				}	
				if( $value['bonificacion_trunc'] > 0 ){
					array_push($empleados_bon_trunc, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['bonificacion_trunc'],
						)
					);
				}	
				if( $value['cts'] > 0 ){
					array_push($empleados_cts, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['cts'],
						)
					);
				}
				if( $value['atencion_personal'] > 0 ){
					array_push($empleados_atp, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['atencion_personal'],
						)
					);
				}	
				if( $value['aportes_empleador'] > 0 ){
					array_push($empleados_apor, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['aportes_empleador'],
						)
					);
				}	
			}
			// PONEMOS TODO EN LAS CUENTAS DE GRATIFICACION Y BONIFICACION, NO SE CREA PARA TRUNCOS
			$empleados_grat = array_merge($empleados_grat,$empleados_grat_trunc);
			$empleados_bon = array_merge($empleados_bon,$empleados_bon_trunc);
			$arrCC[] = array(
				'idcentrocosto' => $idcentrocosto,
				'centro_costo' => $centro_costo,
				'importe_local' => $remuneracion_cc,
				'codigo_plan' => '621101', // Sueldos y salarios
				'empleados' => $empleados_rem,
			);
			if( $vacaciones_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $vacaciones_cc,
					'codigo_plan' => '621501', // Vacaciones
					'empleados' => $empleados_vac
				);
			}
			if( $vacaciones_truncas_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $vacaciones_truncas_cc,
					'codigo_plan' => '621502', // Vacaciones truncas
					'empleados' => $empleados_vac_trunc
				);
			}
			if( $gratificacion_cc > 0 || $gratificacion_trunc_cc > 0){
				$gratificacion_cc = $gratificacion_cc + $gratificacion_trunc_cc;
				$bonificacion_cc = $bonificacion_cc +$bonificacion_trunc_cc;
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $gratificacion_cc,
					'codigo_plan' => '621401', // Gratificacion
					'empleados' => $empleados_grat
				);
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $bonificacion_cc,
					'codigo_plan' => '622102', // bonificacion extraordinaria
					'empleados' => $empleados_bon
				);
			}
			
			if( $cts_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $cts_cc,
					'codigo_plan' => '625101', // Atención al Personal
					'empleados' => $empleados_cts
				);
			}
			if( $atencion_personal_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $atencion_personal_cc,
					'codigo_plan' => '625101', // Atención al Personal
					'empleados' => $empleados_atp
				);
			}
			$arrCC[] = array(
				'idcentrocosto' => $idcentrocosto,
				'centro_costo' => $centro_costo,
				'importe_local' => $aportes_cc,
				'codigo_plan' => '627101', // Regimen de prestaciones de salud - essalud
				'empleados' => $empleados_apor
			);
			
		}
		$arrPrincipal = array();

		foreach ($arrSubOperaciones as $key1 => $value) {
			$monto = 0;
			$detalle = array();
			foreach ($arrCC as $key2 => $row) {
				if( $row['codigo_plan'] == $value['codigo_plan']){
					$monto += $row['importe_local'];
					$detalle[] = $row;
				}
			}
			if( $monto > 0 ){
				array_push($arrPrincipal, array(
					'idsuboperacion' 	=> $value['idsuboperacion'],
					'suboperacion' 		=> $value['descripcion_sop'],
					'codigo_plan' 		=> $value['codigo_plan'],
					'total_a_pagar' 	=> $monto,
					'detalle' 			=> $detalle,
					)
				);
				// array para tabla de movimiento
				/*array_push($arrPrincipal, array(
					'idoperacion' 		=> $datos['idoperacion'],
					'idsuboperacion' 	=> $value['idsuboperacion'],
					'suboperacion' 		=> $value['descripcion_sop'],
					'idusuario' 		=> $this->sessionHospital['idusers'],
					'dir_movimiento' 	=> 2,
					'fecha_registro'	=> date('Y-m-d H:i:s'),
					'total_a_pagar' 	=> $monto,
					'createdat' 		=> date('Y-m-d H:i:s'),
					'updatedat' 		=> date('Y-m-d H:i:s'),
					'codigo_plan' 		=> $value['codigo_plan'],
					'idplanilla' 		=> $idplanilla,
					'detalle' 			=> $detalle,
					)
				);*/
			}
		}
		$totalTributos = 0;
		$totalEstimulo = 0;
		$arrTributos = array();
		$arrRemPorPagar = array();
		if($renta_quinta > 0){
			$arrTributos[] = array(
				'idplanilla'=> $idplanilla,
				'monto' => $renta_quinta,
				'codigo_plan' => '401701',
				'glosa' => 'PL ' . $periodo . '-' . $empresa . ' - QUINTA CAT.',
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'H'
				
			);
		}
		if($aport_empleador > 0){
			$arrTributos[] = array(
				'idplanilla'=> $idplanilla,
				'monto' => $aport_empleador,
				'codigo_plan' => '403101',
				'glosa' => 'PL ' . $periodo . '-' . $empresa . ' - ESSALUD',
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'H'
			);
		}

		if($onp > 0){
			$arrTributos[] = array(
				'idplanilla'=> $idplanilla,
				'monto' => $onp,
				'codigo_plan' => '403201',
				'glosa' => 'PL ' . $periodo . '-' . $empresa . ' - ONP',
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'H'
			);
		}
		$totalTributos = $renta_quinta + $aport_empleador + $onp;
		foreach ($arrRP as $key => $row) {
			$monto = 0;
			foreach ($row as $key => $value) {
				if($key = $value['idafp']){
					$monto += $value['monto'];
					$codigo_plan = $value['codigo_plan'];
				}
			}
			$arrTributos[] = array(
				'idplanilla'=> $idplanilla,
				'monto' => $monto,
				'codigo_plan' => $codigo_plan,
				'glosa' => 'PL ' . $periodo . '-' . $empresa . ' - ' . $value['afp'],
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'H'
			);
			$totalTributos += $monto;
		}

		$arrAsientoContable = array(); 
		$totalDebe = 0;
		$total_vacaciones = 0;
		$total_vacaciones_truncas = 0;
		$total_gratificacion = 0;
		$total_bonificacion = 0;
		$total_cts = 0;
		$arrFondoEst = array();
		$arrVacaciones = array();
		$arrVacacionesTruncas = array();
		$arrGratificaciones = array();
		$arrBonificacion = array();
		$arrCts = array();
		 // exit();
		foreach ($arrPrincipal as $row) {
			$arrDataAC = array();
			$arrAux = array();
			$arrDataAC = array( 
				'idplanilla'=> $idplanilla,
				'codigo_plan'=> $row['codigo_plan'],
				'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . ' - ' . $row['suboperacion'],
				'monto'=> $row['total_a_pagar'],
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'D',
				'detalle' => $row['detalle']
			); 
			$arrAsientoContable[] = $arrDataAC;
			/*
				621101			SUELDOS Y SALARIOS
				625101	441901	ATENCION AL PERSONAL
				627101			REGIMEN PRESTACIONES DE SALUD
				621501	411501	VACACIONES
				621502	411502	VACACIONES TRUNCAS
				621401	411401	GRATIFICACION
				622102	411601	BONIFICACION EXTRAORDINARIA
				629101	415101	CTS
			*/
			if( $row['codigo_plan'] == '625101' ){ //
				$totalEstimulo = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '441901',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- FONDO ESTIMULO',
					'monto'=> $totalEstimulo,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrFondoEst[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '621501' ){ //
				$total_vacaciones = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411501',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- VACACIONES',
					'monto'=> $total_vacaciones,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrVacaciones[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '621502' ){ //
				$total_vacaciones_truncas = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411502',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- VACIONES TRUNCAS',
					'monto'=> $total_vacaciones_truncas,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrVacacionesTruncas[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '621401' ){ //
				$total_gratificacion = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411401',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- GRATIFICACIONES',
					'monto'=> $total_gratificacion,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrGratificaciones[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '622102' ){ //
				$total_bonificacion = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411601',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- BONIFICACION EXTRAORDINARIA',
					'monto'=> $total_bonificacion,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrBonificacion[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '629101' ){ //
				$total_cts = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '415101',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- CTS',
					'monto'=> $total_cts,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrCts[] = $arrAux;
			}
			$totalDebe += $row['total_a_pagar'];
			
		}

		$totalRemPorPagar = $totalDebe - $totalEstimulo - $totalTributos -
			$total_vacaciones - $total_vacaciones_truncas - $total_gratificacion - $total_bonificacion - $total_cts;
		$arrRemPorPagar[] = array(
			'idplanilla'=> $idplanilla,
			'codigo_plan'=> '411101',
			'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- SUELDOS',
			'monto'=> $totalRemPorPagar,
			'fecha_emision'=> date('Y-m-d H:i:s'),
			'debe_haber'=> 'H'
			);
		

		$arrAsientoContable = array_merge($arrAsientoContable,$arrTributos,$arrFondoEst,$arrRemPorPagar,$arrVacaciones,$arrVacacionesTruncas,
			$arrGratificaciones, $arrBonificacion,$arrCts);
		return $arrAsientoContable;
	}
	private function generar_asiento_provisiones($empleados_planilla){
		$datos = array( 'idoperacion' => 16 );
		$arrSubOperaciones = $this->model_sub_operacion->m_cargar_suboperacion_cbo($datos);
		$remuneraciones = 0;
		$netos_a_pagar = 0;
		$renta_quinta = 0;
		$aport_empleador = 0;
		$onp = 0;
		$arrAux = array();
		$arrCC = array();
		$arrRemCC = array();
		$arrAtePerCC = array();
		$arrAporCC = array();
		$arrRP = array();
		$arrAsientoProvisiones = array();
		$periodo = darFormatoMesAno($empleados_planilla[0]['fecha_cierre']);
		$idplanilla = $empleados_planilla[0]['idplanilla'];
		foreach ($empleados_planilla as $row) {
			$conceptos = objectToArray(json_decode($row['concepto_valor_json']));
			$atencion_personal = 0;
			$vacaciones = 0;
			$vacaciones_truncas = 0;
			$gratificacion = 0;
			$bonificacion = 0;
			$gratificacion_trunc = 0;
			$bonificacion_trunc = 0;
			$cts = 0;
			$remuneraciones += $row['total_remuneraciones'];
			$netos_a_pagar += $row['neto_a_pagar'];
			$renta_quinta += $row['renta_quinta'];
			$aport_empleador += $row['aportes_empleador'];

			$vacaciones = (float)$conceptos['provisiones']['vacaciones']['computable'] + (float)$conceptos['provisiones']['vacaciones']['no_computable'];

			$gratificacion = $conceptos['provisiones']['gratificacion'];


			$cts = (float)$conceptos['provisiones']['cts'];
			// var_dump($atencion_personal);
			$arrAux[$row['idcentrocosto']][] = array(
				'idempleado' 			=> $row['idempleado'],
				'empleado' 				=> $row['empleado'],
				'idplanilla' 			=> $row['idplanilla'],
				'vacaciones'			=> $vacaciones,
				'gratificacion'			=> $gratificacion,
				'cts'					=> $cts,
				'idcentrocosto' 		=> $row['idcentrocosto'],
				'centro_costo'			=> $row['codigo_scc'].'-'.$row['centro_costo'],
				'empresa' 				=> $row['alias_empresa'],
			);
			
		}
			
		foreach ($arrAux as $key => $row) {
			$vacaciones_cc = 0;
			$gratificacion_cc = 0;
			$cts_cc = 0;
			$empleados_vac = array();
			$empleados_grat = array();
			$empleados_cts = array();
			foreach ($row as $key => $value) {
				$vacaciones_cc += $value['vacaciones'];
				$gratificacion_cc += $value['gratificacion'];
				$cts_cc += $value['cts'];
				$idcentrocosto = $value['idcentrocosto'];
				$centro_costo = $value['centro_costo'];
				$empresa = $value['empresa'];
				
				if( $value['vacaciones'] > 0 ){
					array_push($empleados_vac, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['vacaciones'],
						)
					);
				}	
				if( $value['gratificacion'] > 0 ){
					array_push($empleados_grat, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['gratificacion'],
						)
					);
				}	
				if( $value['cts'] > 0 ){
					array_push($empleados_cts, array(
						'idempleado' => $value['idempleado'],
						'empleado' => $value['empleado'],
						'valor_empleado' => $value['cts'],
						)
					);
				}
			}
			// PONEMOS TODO EN LAS CUENTAS DE GRATIFICACION Y BONIFICACION, NO SE CREA 


			if( $vacaciones_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $vacaciones_cc,
					'codigo_plan' => '621501', // Vacaciones
					'empleados' => $empleados_vac
				);
			}
			if( $gratificacion_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $gratificacion_cc,
					'codigo_plan' => '621401', // Gratificacion
					'empleados' => $empleados_grat
				);
			}
			
			if( $cts_cc > 0){
				$arrCC[] = array(
					'idcentrocosto' => $idcentrocosto,
					'centro_costo' => $centro_costo,
					'importe_local' => $cts_cc,
					'codigo_plan' => '629101', // cts
					'empleados' => $empleados_cts
				);
			}
			
		}
		$arrPrincipal = array();
		

		foreach ($arrSubOperaciones as $key1 => $value) {
			$monto = 0;
			$detalle = array();
			foreach ($arrCC as $key2 => $row) {
				if( $row['codigo_plan'] == $value['codigo_plan']){
					$monto += $row['importe_local'];
					$detalle[] = $row;
				}
			}
			if( $monto > 0 ){
				array_push($arrPrincipal, array(
					'idsuboperacion' 	=> $value['idsuboperacion'],
					'suboperacion' 		=> $value['descripcion_sop'],
					'codigo_plan' 		=> $value['codigo_plan'],
					'total_a_pagar' 	=> $monto,
					'detalle' 			=> $detalle,
					)
				);
			}
		}
		$arrVacaciones = array();
		$arrGratificaciones = array();
		$arrCts = array();
		foreach ($arrPrincipal as $row) {
			$arrDataAC = array();
			$arrAux = array();
			$arrDataAC = array( 
				'idplanilla'=> $idplanilla,
				'codigo_plan'=> $row['codigo_plan'],
				'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '-' . $row['suboperacion'],
				'monto'=> $row['total_a_pagar'],
				'fecha_emision'=> date('Y-m-d H:i:s'),
				'debe_haber'=> 'D',
				'detalle' => $row['detalle']
			); 
			$arrAsientoProvisiones[] = $arrDataAC;
			/*	621501	411501	VACACIONES
				621401	411401	GRATIFICACION
				629101	415101	CTS
			*/
			if( $row['codigo_plan'] == '621501' ){ //
				$total_vacaciones = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411501',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- VACACIONES',
					'monto'=> $total_vacaciones,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrVacaciones[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '621401' ){ //
				$total_gratificacion = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '411401',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- GRATIFICACIONES',
					'monto'=> $total_gratificacion,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrGratificaciones[] = $arrAux;
			}
			elseif( $row['codigo_plan'] == '629101' ){ //
				$total_cts = $row['total_a_pagar'];
				$arrAux = array( 
					'idplanilla'=> $idplanilla,
					'codigo_plan'=> '415101',
					'glosa'=> 'PL ' . $periodo . ' - ' . $empresa . '- CTS',
					'monto'=> $total_cts,
					'fecha_emision'=> date('Y-m-d H:i:s'),
					'debe_haber'=> 'H'
				); 
				$arrCts[] = $arrAux;
			}
			
		}
		

		$arrAsientoProvisiones = array_merge($arrAsientoProvisiones,$arrVacaciones,	$arrGratificaciones, $arrCts);
		// var_dump($arrAsientoProvisiones); exit();
		return $arrAsientoProvisiones;
	}
	
	public function cierre_planilla(){
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Error al cerrar Planilla. Intente nuevamente.';
    	$arrData['flag'] = 0;
    	$noValida = FALSE;	
		// VALIDAR ESTADO DE LA PLANILLA A CERRAR
			$allInputs['idplanilla'] = $allInputs['id'];
			$planilla = $this->model_planilla->m_consulta_estado_planilla($allInputs);
		
			if( $planilla['estado_pl'] == 2 ){
				$arrData['message'] = 'No se puede realizar la acción. La planilla ya está cerrada.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}elseif( $planilla['estado_pl'] == 0 ){
				$arrData['message'] = 'La planilla está anulada.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		// VALIDAR QUE LA PLANILLA ESTE CALCULADA COMPLETAMENTE
			$lista_empleados = $this->model_empleado_planilla->m_cargar_empleados_calculados_planilla($allInputs['id']);
			foreach ($lista_empleados as $row) {
				if( $row['estado_pe'] != 2 ){
					$arrData['message'] = 'Debe calcular toda la planilla de empleados para cerrarla.';
					$noValida = TRUE;
				}
			}
			if($noValida){
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		// VALIDAR CENTRO DE COSTO
			$empleados_planilla = $this->model_empleado_planilla->m_cargar_empleados_planilla_calculada($allInputs['id']);
			foreach ($empleados_planilla as $key => $row) {
				if( empty($row['idcentrocosto']) ){
					$arrData['message'] = $row['empleado'] . ' no tiene un Centro de Costo. Revise que todos los empleados tengan Centro de Costo.';
					$noValida = TRUE;
				}
			}
			if($noValida){
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}

		$arrAsientoContable = $this->generar_asiento_contable($empleados_planilla);
		$arrAsientoProvisiones = $this->generar_asiento_provisiones($empleados_planilla);
		
		$allInputs['conceptos_json'] = json_encode(array(
			'conceptos' => $allInputs['conceptos_json']['conceptos'],
			'variables_ley' => GetVariableLey(),
			'asiento_contable' => $arrAsientoContable,
			'asiento_provisiones' => $arrAsientoProvisiones,
		));
		// var_dump($arrAsientoContable); exit();
		$this->db->trans_start();
		foreach ($arrAsientoContable as $key => $rowAC) {
			if( $this->model_planilla->m_registrar_asiento_planilla($rowAC) ){ 
				$arrData['message'] = 'Los datos se registaron correctamente';
				$arrData['flag'] = 1;
			}
		}
		//Cierro la planilla
		if($this->model_planilla->m_cerrar_planilla($allInputs)){
			$arrData['message'] = 'Planilla generada exitosamente.';
    		$arrData['flag'] = 1;    			
		}
		$this->db->trans_complete(); 

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_empleados_planilla(){
		$this->load->view('planilla/empleadoPlanilla_formView');
	}

	public function apertura_planilla(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Error al aperturar Planilla. Intente nuevamente.';
    	$arrData['flag'] = 0;

    	/*consulta la planilla master activa*/
    	if(empty($allInputs['planillaMaster']['id'])){
    		$arrData['message'] = 'Debe seleccionar planilla.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	/*Valida que no haya planilla activa*/
		$planilla = $this->model_planilla->m_consulta_planilla_activa($allInputs);
		if(!empty($planilla)){
    		$arrData['message'] = 'Ya existe una planilla abierta.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	/*valida planilla periodo duplicado*/
    	if($this->model_planilla->m_es_planilla_duplicada($allInputs)){
    		$arrData['message'] = 'Ya existe una planilla en ese periodo.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	/*creacion de JSON conceptos*/
    	$conceptos = $this->model_concepto_planilla->m_cargar_conceptos_planilla_master($allInputs['planillaMaster']['id']);


    	if(empty($conceptos)){
    		$arrData['message'] = 'No han sido configurados conceptos en la planilla master.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$dataConceptos = array();
    	foreach ($conceptos as $key => $row) {
    		if($row['indice'] == 0){
    			$indice = $row['idconcepto'];
    		}else{
    			$indice = $row['indice'];    			
    		}

    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice] = $row;
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice]['estado_pc_empleado'] = $row['estado_pc'];
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice]['valor_empleado'] = 0;    		
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['descripcion_categoria'] = $row['categoria'];
    	}

    	$dataConceptos[1]['descripcion_tipo'] = 'Remuneraciones';
    	$dataConceptos[2]['descripcion_tipo'] = 'Descuentos';
    	$dataConceptos[3]['descripcion_tipo'] = 'Aportaciones del Empleador';    	

    	$allInputs['conceptos_json'] = json_encode( array('conceptos' => $dataConceptos ));

    	$planillaAnterior = $this->model_planilla->m_consultar_planilla_anterior($allInputs['planillaMaster']);

		$this->db->trans_start();
		if($this->model_planilla->m_aperturar_planilla($allInputs)){
			$idplanilla = GetLastId('idplanilla','rh_planilla');
			/*registro de empleados*/
			if(empty($planillaAnterior)){
				if($this->model_planilla->m_agregar_empleados($allInputs['empresa'], $idplanilla)){
					$arrData['message'] = 'Planilla aperturada exitosamente.';
					$arrData['flag'] = 1;
				}
			}else{
				$parametros = array(
					'estado_pl' => 2,
					'id' => $planillaAnterior['idplanilla'],
					'idempresa' => $allInputs['empresa']['id'],
				);

		    	$empleados = $this->model_empleado_planilla->m_cargar_empleados_planilla_anterior($parametros);
		    	$error = FALSE;
		    	foreach ($empleados as $ind => $empl) {
		    		$concepto_valor_json = NULL;
		    		if(!empty($empl['concepto_valor_json'])){
		    			$json_empleado = objectToArray(json_decode($empl['concepto_valor_json']));
		    			if(!empty($json_empleado['calculos'])){
		    				unset($json_empleado['calculos']);
		    			}

		    			if(!empty($json_empleado['provisiones'])){
		    				unset($json_empleado['provisiones']);
		    			}
		    		}else{
		    			$json_empleado = NULL;
		    		}
		    		$dataEmpl = array(
		    			'idempleado' => $empl['idempleado'],
		    			'idplanilla' => $idplanilla,
		    			'concepto_valor_json' => ($json_empleado == NULL ) ? NULL : json_encode($json_empleado),
		    		);

		    		if(!$this->model_empleado_planilla->m_registrar($dataEmpl)){
		    			$error = TRUE;
		    		}
		    	}

		    	if(!$error){
		    		$arrData['message'] = 'Planilla aperturada exitosamente.';
					$arrData['flag'] = 1;
		    	}
			}							
		}
		$this->db->trans_complete(); 

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));		
	}	

	public function ver_popup_apertura_planilla(){
		$this->load->view('planilla/aperturaPlanilla_formView');
	}	

	public function ver_popup_concepto_empl_planilla(){
		$this->load->view('planilla/conceptoEmpleadoPlanilla_formView');
	}	

	public function obtener_variables_ley(){
		$arrData['datos'] = GetVariableLey();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function actualizar_json_planilla(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Error al actualizar Planilla. Intente nuevamente.';
    	$arrData['flag'] = 0;

    	/*creacion de JSON conceptos*/
    	$conceptos = $this->model_concepto_planilla->m_cargar_conceptos_planilla_master($allInputs['idplanillamaster']);

    	if(empty($conceptos)){
    		$arrData['message'] = 'No han sido configurados conceptos en la planilla master.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$dataConceptos = array();
    	foreach ($conceptos as $key => $row) {
    		if($row['indice'] == 0){
    			$indice = $row['idconcepto'];
    		}else{
    			$indice = $row['indice'];    			
    		}

    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice] = $row;
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice]['estado_pc_empleado'] = $row['estado_pc'];
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['conceptos'][$indice]['valor_empleado'] = 0;    		
    		$dataConceptos[$row['tipo_concepto']]['categorias'][$row['idcategoriaconcepto']]['descripcion_categoria'] = $row['categoria'];
    	}

    	$dataConceptos[1]['descripcion_tipo'] = 'Remuneraciones';
    	$dataConceptos[2]['descripcion_tipo'] = 'Descuentos';
    	$dataConceptos[3]['descripcion_tipo'] = 'Aportaciones del Empleador';    	

    	$allInputs['conceptos_json']['conceptos'] = $dataConceptos;
    	$allInputs['conceptos_json'] = json_encode($allInputs['conceptos_json']);

		$this->db->trans_start();
		if($this->model_planilla->m_actualizar_conceptos_planilla($allInputs)){
			$empleados = $this->model_empleado_planilla->m_cargar_empleados_esta_planilla($allInputs['id']);
			$error  = FALSE;
			foreach ($empleados as $key => $empl) {
				if($empleados[$key]['concepto_valor_json']!= null){
					$empleados[$key]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));
					$empleados[$key]['concepto_valor_json']['conceptos'] = $dataConceptos;
					$empleados[$key]['concepto_valor_json'] =  json_encode($empleados[$key]['concepto_valor_json']);
				}else{
					$empleados[$key]['concepto_valor_json']['conceptos'] = $dataConceptos;
					$empleados[$key]['concepto_valor_json'] =  json_encode($empleados[$key]['concepto_valor_json']);
				}
				
				if(!$this->model_empleado_planilla->m_actualizar($empleados[$key],FALSE)){
					$error = TRUE;
				}
			}
			if(!$error){
				$arrData['message'] = 'Planilla actualizada exitosamente.';
				$arrData['flag'] = 1;
			}					
		}
		$this->db->trans_complete(); 

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_asiento_contable(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);

		function sort_by_monto ($a, $b) {
		    return $b['importe_local'] - $a['importe_local'];
		}

		function sort_by_cuenta ($a, $b) {
		    return (int)$a['codigo_plan'] - (int)$b['codigo_plan'];
		}

		function sort_by_tipo ($a, $b) {
		    return $a['debe_haber'] - $b['debe_haber'];
		}

		$sum_debe = 0;
		$sum_haber = 0;

		$sum_debeProv = 0;
		$sum_haberProv = 0;

		$asiento_contable = $allInputs['conceptos_json']['asiento_contable'];
		$asiento_provisiones = $allInputs['conceptos_json']['asiento_provisiones'];
		usort($asiento_contable, 'sort_by_tipo');		
		usort($asiento_provisiones, 'sort_by_tipo');		
		
		$arrayDebe = array();
		$arrayHaber = array();
		$arrayDebeProv = array();
		$arrayHaberProv = array();
		foreach ($asiento_contable as $key => $asiento) {
			if(!empty($asiento['detalle'])){
				usort($asiento['detalle'], 'sort_by_monto');
				$asiento_contable[$key]['detalle'] = $asiento['detalle'];
			}

			if($asiento['debe_haber'] == 'D'){
				$sum_debe += (float)$asiento['monto'];
				array_push($arrayDebe, $asiento);
			}

			if($asiento['debe_haber'] == 'H'){
				$sum_haber += (float)$asiento['monto'];
				array_push($arrayHaber, $asiento);
			}
		}
		// PROVISIONES
		foreach ($asiento_provisiones as $key => $asiento) {
			if(!empty($asiento['detalle'])){
				usort($asiento['detalle'], 'sort_by_monto');
				$asiento_provisiones[$key]['detalle'] = $asiento['detalle'];
			}

			if($asiento['debe_haber'] == 'D'){
				$sum_debeProv += (float)$asiento['monto'];
				array_push($arrayDebeProv, $asiento);
			}

			if($asiento['debe_haber'] == 'H'){
				$sum_haberProv += (float)$asiento['monto'];
				array_push($arrayHaberProv, $asiento);
			}
		}

		usort($arrayDebe, 'sort_by_cuenta');
		usort($arrayHaber, 'sort_by_cuenta');
		usort($arrayDebeProv, 'sort_by_cuenta');
		usort($arrayHaberProv, 'sort_by_cuenta');

		$asiento_contable = array_merge($arrayDebe, $arrayHaber);
		$asiento_provisiones = array_merge($arrayDebeProv, $arrayHaberProv);

		$arrData['asiento_contable'] = $asiento_contable;
		$arrData['asiento_provisiones'] = $asiento_provisiones;
		$arrData['total_debe'] = $sum_debe;
		$arrData['total_haber'] = $sum_haber;
		$arrData['total_debe_prov'] = $sum_debeProv;
		$arrData['total_haber_prov'] = $sum_haberProv;
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>