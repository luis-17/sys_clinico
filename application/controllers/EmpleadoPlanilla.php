<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpleadoPlanilla extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas','otros','contable_helper'));
		$this->load->model(array('model_empleado_planilla','model_config', 'model_planilla', 'model_empleado',
								 'model_asistencia','model_feriado','model_horario_especial','model_horario_general'));
		$this->load->library(array('excel'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function lista_empleados_planilla(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramEmpresa = $allInputs['planilla'];

		$lista = $this->model_empleado_planilla->m_cargar_empleados_planilla($paramPaginate, $paramEmpresa);
		$totalRows = $this->model_empleado_planilla->m_count_empleados_planilla($paramPaginate,$paramEmpresa);
		
		$arrListado = array();
		foreach ($lista as $key => $row) {	
			$estado = array();
			if(!empty($row['concepto_valor_json'])){
				$lista[$key]['concepto_valor_json'] = objectToArray(json_decode($row['concepto_valor_json']));	
				if(!empty($lista[$key]['concepto_valor_json']['configuracion'])){
					$estado = array(
					'label' => 'label-success',
					'str_estado' => 'VER CONFIGURACION',
					);
				}else{
					$estado = array(
						'label' => 'label-default',
						'str_estado' => 'NO CONFIGURADO',
						);
				}
			}else{
				$estado = array(
					'label' => 'label-default',
					'str_estado' => 'NO CONFIGURADO',
					);
			} 	

			$resultado = CalculoFaltasTardanzasEmpleado($row,  
														date('Y-m-d', strtotime($allInputs['planilla']['fecha_apertura'])), 
														date('Y-m-d', strtotime($allInputs['planilla']['fecha_cierre'])));	


			array_push($arrListado, 
				array(
					'id' => $row['idplanillaempleado'],
					'idempleado' => $row['idempleado'],
					'numero_documento' => $row['numero_documento'],
					'idtipodocumentorh' => $row['idtipodocumentorh'],
					'idplanilla' => $row['idplanilla'],
					'empleado' => strtoupper($row['empleado']),
					'fecha_ingreso' => date('d-m-Y', strtotime($row['fecha_ingreso'])),
					'concepto_valor_json' => $lista[$key]['concepto_valor_json'],
					'reg_pensionario' => $row['reg_pensionario'],
					'tipo_comision' => $row['tipo_comision'],
					'idafp' => $row['idafp'],
					'descripcion_afp' => $row['descripcion_afp'],
					'a_oblig' => $row['a_oblig'],
					'comision' => $row['comision'],
					'p_seguro' => $row['p_seguro'],
					'comision_m' => $row['comision_m'], 
					'estado_afp' => $row['estado_afp'], 
					'cuspp' => $row['cuspp'],
					'fecha_inicio_contrato' => $row['fecha_inicio_contrato'], 
					'fecha_fin_contrato' => $row['fecha_fin_contrato'], 
					'sueldo_contrato' => $row['sueldo_contrato'], 
					'descripcion_cargo' => $row['descripcion_ca'], 
					'centro_costo' => $row['centro_costo'], 					 
					'sede' => $row['sede'],
					'tardanza' => $resultado['tardanza'],
					'faltas' => $resultado['falta'],
					'total_remuneraciones' => $row['total_remuneraciones'],
					'total_descuentos' => $row['total_descuentos'],
					'neto_a_pagar' => $row['neto_a_pagar'],
					'tardanzaBreak' => $resultado['tardanzaBreak'],				 
					'estado' => $estado,
					'calculos_asistencia' => $resultado 
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

	public function calcular_renta_quinta(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		
		$quinta = $this->calculo_renta_quinta($allInputs);
		$arrData['datos'] = $quinta;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 

	private function calculo_renta_quinta($allInputs){
		$idempleado = $allInputs['empleado']['idempleado'];
		$anio = date("Y");
		$mes =  '01';
		// var_dump($allInputs); exit();
		/*CALCULO REMUNERACIONES Y RETENCIONES ANTERIORES*/
		$planillaOld = $this->model_empleado_planilla->m_cargar_planillas_anteriores($idempleado, $anio, $mes);
		$remuneracion_anterior =  0;
		$retencion_anterior =  0;
		foreach ($planillaOld as $index => $planilla) {
			$planillaOld[$index]['concepto_valor_json'] = objectToArray(json_decode($planillaOld[$index]['concepto_valor_json']));
			if(!empty($planillaOld[$index]['concepto_valor_json']['configuracion']['rem_renta_quinta']) && 
				!empty($planillaOld[$index]['concepto_valor_json']['calculos']['rentaQuinta'])){
				$remuneracion_anterior += (float)$planillaOld[$index]['concepto_valor_json']['configuracion']['rem_renta_quinta'];
				$retencion_anterior += (float)$planillaOld[$index]['concepto_valor_json']['calculos']['rentaQuinta'];
			}
		}

		$remuneracion_anterior += empty($allInputs['remuneracion_acum']) ? 0 : (float)$allInputs['remuneracion_acum'];
		$retencion_anterior += empty($allInputs['retencion_acum']) ? 0 : (float)$allInputs['retencion_acum'];
		/*FALTA EL CALCULO DE GRATIFICACIONES*/
		$allInputs['planilla']['fecha_cierre'];
		$mes = intval(date('m',strtotime($allInputs['planilla']['fecha_cierre'])));		
		// var_dump($remuneracion_anterior);
		// var_dump($retencion_anterior); 
		// var_dump($mes); 
		// exit();

		$gratificaciones = 0;
		$quinta = CalculoRentaQuinta($mes, $allInputs['remuneracionRentaQuinta'], $gratificaciones, $remuneracion_anterior, $retencion_anterior);
		// var_dump($mes);
		// var_dump($allInputs['remuneracionRentaQuinta']);
		// var_dump($remuneracion_anterior);
		// var_dump($retencion_anterior);
		// var_dump($quinta);
		//  exit();
		return $quinta;
	}

	public function registrar_configuracion_conceptos(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Ha ocurrido un error actualizando la configuración de Conceptos.';
    	$arrData['flag'] = 0; 

		if(empty($allInputs['sueldo_base'])){
    		$arrData['message'] = 'Debe ingresar la Remuneración Básica.';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	/** Validar Planilla Cerrada **/
    	$datos = array(
			'idplanilla' => $allInputs['planilla']['id'],
		);
    	$result = $this->model_planilla->m_consulta_estado_planilla($datos);
    	if($result['estado_pl'] == 2){
    		$arrData['message'] = 'La planilla se encuentra cerrada';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}elseif($result['estado_pl'] == 0){
    		$arrData['message'] = 'La planilla está anulada';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$JSON = array(
    		'configuracion' => array(
    			'horas_diarias' 		=> $allInputs['horas_diarias'],
    			'sueldo_base' 			=> $allInputs['sueldo_base'],
    			'movilidad' 			=> empty($allInputs['movilidad']) ? 0 : $allInputs['movilidad'],
    			'condicion_trabajo' 	=> empty($allInputs['condicion_trabajo']) ? 0 : $allInputs['condicion_trabajo'],
    			'refrigerio' 			=> empty($allInputs['refrigerio']) ? 0 : $allInputs['refrigerio'],
    			'horas_extras25' 		=> empty($allInputs['horas_extras25']) ? 0 : $allInputs['horas_extras25'],
    			'horas_extras35' 		=> empty($allInputs['horas_extras35']) ? 0 : $allInputs['horas_extras35'],
    			'rem_reg_pensionario' 	=> empty($allInputs['remuneracionRegPensionario']) ? 0 : $allInputs['remuneracionRegPensionario'],
    			'rem_renta_quinta' 		=> empty($allInputs['remuneracionRentaQuinta']) ? 0 : $allInputs['remuneracionRentaQuinta'],
    			'remuneracion_dada' 	=> empty($allInputs['remuneracion_dada']) ? 0 : $allInputs['remuneracion_dada'],
    			'remuneracion_acum' 	=> empty($allInputs['remuneracion_acum']) ? 0 : $allInputs['remuneracion_acum'],
    			'retencion_acum' 		=> empty($allInputs['retencion_acum']) ? 0 : $allInputs['retencion_acum'], 
    			'faltas' 				=> empty($allInputs['faltas']) ? 0 : $allInputs['faltas'], 
    			'tardanzas' 			=> empty($allInputs['tardanzas']) ? 0 : $allInputs['tardanzas'], 
    			'dias_trabajados' 		=> empty($allInputs['dias_trabajados']) ? 0 : $allInputs['dias_trabajados'], 
    		),
    		'conceptos' => $allInputs['planilla']['conceptos'],
    	);
    	$JSON = json_encode($JSON);

    	if(empty($allInputs['empleado']['id'])){
    		/*registrar json*/
    		$tipoOperacion = 'registrar';
    		$datos = array(
    			'idplanilla' => $allInputs['planilla']['id'],
    			'idempleado' => $allInputs['empleado']['idempleado'],
    			'concepto_valor_json' => $JSON,
    		);
    		if($this->model_empleado_planilla->m_registrar($datos)){
    			$arrData['message'] = 'Configuración de Conceptos actualizada exitosamente.';
    			$arrData['flag'] = 1; 
    			$arrData['tipoOperacion'] = $tipoOperacion;
    		}
    	}else{
    		/*actualizar conceptos*/
    		$tipoOperacion = 'actualizar';
    		$datos = array(
    			'idplanillaempleado' => $allInputs['empleado']['id'],
    			'concepto_valor_json' => $JSON,
    		);
    		if($this->model_empleado_planilla->m_actualizar($datos)){
    			$arrData['message'] = 'Configuración de Conceptos actualizada exitosamente.';
    			$arrData['flag'] = 1; 
    			$arrData['tipoOperacion'] = $tipoOperacion; 
    		}
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function calcular_planilla_empleado(){
		ini_set('xdebug.var_display_max_depth', 10); 
        ini_set('xdebug.var_display_max_children', 1024); 
        ini_set('xdebug.var_display_max_data', 1024);
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['message']='Se registraron los cálculos correctamente.';
		$arrData['flag']=1;
		$planilla = $allInputs['planilla'];
		$variablesDeLey = GetVariableLey();
		/*print_r($allInputs);
		exit();*/

		$calcularVacaciones = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0118');
		$calcularGratificaciones = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0406');
		$calcularCTS = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0904');

		$empleados = array();
		$hayNulo=FALSE;
		foreach ($allInputs['empleados'] as $key => $empl) {
			array_push($empleados, $empl['idempleado']);
			if(empty($empl['concepto_valor_json']) || empty($empl['concepto_valor_json']['configuracion'])){
				$hayNulo=TRUE;
			}
		}

		if($hayNulo){
			$arrData['message']='Algunos empleados seleccionados no tienen configuración de conceptos.';
			$arrData['flag']=0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}

		$listaEmpleados = $this->model_empleado_planilla->m_cargar_estos_empleados_planilla($planilla, $empleados);
		//$listaEmpleados = $allInputs['empleados'];
		//var_dump($listaEmpleados); exit();
		$arrayExcel = array();
		$item = 0;
		$hayError = FALSE;

		foreach ($listaEmpleados as $key => $empl) {
			$result = array();
			$listaEmpleados[$key]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));	
			
			/*----------------- ASISTENCIA ------------------*/
				$diasTrabajados = empty($listaEmpleados[$key]['concepto_valor_json']['configuracion']['dias_trabajados']) ? 30 : (int)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['dias_trabajados'];
				$result['diasTrabajados'] = $diasTrabajados;

				$asistenciaEmpleado = CalculoFaltasTardanzasEmpleado($listaEmpleados[$key],  
															date('Y-m-d', strtotime($allInputs['planilla']['fecha_apertura'])), 
															date('Y-m-d', strtotime($allInputs['planilla']['fecha_cierre'])));

				$listaEmpleados[$key]['calculos_asistencia'] = $asistenciaEmpleado;

				if((int)$listaEmpleados[$key]['calculos_asistencia']['diasVacaciones']>0){
					$dataVacaciones = array(
						'empresa' => $allInputs['empresa'],
						'planilla' => $allInputs['planilla'],
						'empleado' => $listaEmpleados[$key],
					);
					$calculo_vacaciones = $this->calculo_vacaciones_empleado($dataVacaciones);
					$listaEmpleados[$key]['calculoVacaciones'] = $calculo_vacaciones;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0118',1);				
				}else{
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0118',2);					
				}

				// $result['faltas'] = 0
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0704') == 1){
					$result['tardanzas'] = (int)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['tardanzas'];
					// $result['faltas'] = $asistenciaEmpleado['falta'];
				}else{
					$result['tardanzas'] = 0;
				}

				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0705') == 1){
					$result['faltas'] = (int)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['faltas'];
					// $result['faltas'] = $asistenciaEmpleado['falta'];
				}else{
					$result['faltas'] = 0;
				}
				// $result['tardanza'] = $asistenciaEmpleado['tardanza'];	
				$result['tardanzaBreak'] = $asistenciaEmpleado['tardanzaBreak'];

				// $listaEmpleados[$key]['concepto_valor_json']['configuracion']['faltas'] = $asistenciaEmpleado['falta'];	
				// $listaEmpleados[$key]['concepto_valor_json']['configuracion']['tardanzas'] = $asistenciaEmpleado['tardanza'];	
				$listaEmpleados[$key]['concepto_valor_json']['configuracion']['tardanzaBreak'] = $asistenciaEmpleado['tardanzaBreak'];	

			/*----------------- VACACIONES ------------------*/
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0118') == 1){
					$result['vacacionesComputables'] = (float)$listaEmpleados[$key]['calculoVacaciones']['total_computable'];
					$result['vacacionesNoComputables'] = (float)$listaEmpleados[$key]['calculoVacaciones']['total_no_computable'];
					$suma_vacaciones=(float)$result['vacacionesComputables'] + (float)$result['vacacionesNoComputables'];
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0118',round($suma_vacaciones,2));
				}else{
					$result['vacacionesComputables'] = 0;
					$result['vacacionesNoComputables'] = 0;
				}

			/*------------- VACACIONES TRUNCAS --------------*/
				if($calcularVacaciones){
					if(!empty($allInputs['tipo_calculo']) && $allInputs['tipo_calculo']== 'liquidacion'){					
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0114',1);
						$dataVacaciones = array(
							'empresa' => $allInputs['empresa'],
							'planilla' => $allInputs['planilla'],
							'empleado' => $listaEmpleados[$key],
						);
						//acumulado
							$calculo_vacaciones = $this->calculo_vacaciones_empleado($dataVacaciones, $allInputs['tipo_calculo']);
							$listaEmpleados[$key]['calculoVacaciones'] = $calculo_vacaciones;
							$result['vacacionesNoComputables'] = (float)$listaEmpleados[$key]['calculoVacaciones']['total_vacaciones']['no_computable'];
							$result['vacacionesComputables'] = (float)$listaEmpleados[$key]['calculoVacaciones']['total_vacaciones']['computable'];
						//proporcion mes
							if($allInputs['empresa']['regimen'] == 1){
								$total_meses = 24;				
							}else if($allInputs['empresa']['regimen'] == 3){
								$total_meses = 12;
							}else{
								$total_meses = 12;
							}
							$rem_dada = $listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_dada'];
							$rem_comp = $listaEmpleados[$key]['concepto_valor_json']['configuracion']['sueldo_base'];
							$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['computable'] = ($rem_comp/$total_meses) / 30 * (int)$result['diasTrabajados'];
							$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['no_computable'] = ($rem_dada -  $rem_comp)/$total_meses / 30 * (int)$result['diasTrabajados'];
							$result['vacacionesComputables'] += (float)$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['computable'];
							$result['vacacionesNoComputables'] += (float)$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['no_computable'];
							$suma_vacaciones=(float)$result['vacacionesComputables'] + (float)$result['vacacionesNoComputables'];
							$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0114',round($suma_vacaciones,2));
					
					}else{
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['computable'] = 0;
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['no_computable'] = 0;
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0114',0);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0114',2);
					}
				}			

			/*------------ GRATIFICACION TRUNCA -------------*/
				if($calcularGratificaciones){
					if(!empty($allInputs['tipo_calculo']) && $allInputs['tipo_calculo']== 'liquidacion'){					
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0407',1);
						$gratificacion = (float)($listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_dada']/30 * ($result['diasTrabajados'] - $result['faltas']) )/6; 
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['gratificacion'] = 	$gratificacion;	

						$dataGratificaciones = array(
							'empresa' => $allInputs['empresa'],
							'planilla' => $allInputs['planilla'],
							'empleado' => $listaEmpleados[$key],							
						);
						$calculo_gratificaciones = $this->calculo_pago_gratificaciones_liquidacion($dataGratificaciones);
						$mes_planilla = date('n', strtotime($allInputs['planilla']['fecha_cierre']));
						if($listaEmpleados[$key]['concepto_valor_json']['configuracion']['dias_trabajados'] == 30){							
							$suma_gratificacion = (float)$calculo_gratificaciones['total_gratificacion'] + (float)$listaEmpleados[$key]['concepto_valor_json']['provisiones']['gratificacion'];
						}else{
							$suma_gratificacion = (float)$calculo_gratificaciones['total_gratificacion'];
						}
						$result['gratificacionTrunca'] = round($suma_gratificacion);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0407',round($suma_gratificacion,2));	

						$bonificacion = $suma_gratificacion * 9 / 100;
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0313',1);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0313',round($bonificacion,2));	


					}else{
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['gratificacion'] = 	0;
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0407',0);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0313',0);						
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0407',2);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0313',2);
					}
				}
			
			/*---------------- REMUNERACIONES ---------------*/
				$result['remComputable'] = (float)obtenerValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0121');

				if($result['faltas'] > 0 && obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0705') == 1){
					$result['importeFaltas'] = $result['remComputable']/ 30 *(int)$result['faltas']; 
					$result['remBasica'] = (float)($result['remComputable']/30 * $result['diasTrabajados']) - $result['importeFaltas'];
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0705',$result['importeFaltas']);
				}else{
					$result['remBasica'] = (float)($result['remComputable']/30 * $result['diasTrabajados']);
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0705',0);
				}
	   			
	   			$result['costoHoraTrabajada'] = $result['remBasica']/$result['diasTrabajados']/$listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_diarias'];

	   			$result['totalHorasEx'] = 0;
				$result['costoHora25'] = (float)$result['costoHoraTrabajada'] * 1.25;
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0105') == 1){
					$horas25 = empty($listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_extras25']) ? 0 : (int)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_extras25'];
					$result['totalHorasEx'] += $horas25;
					
					$result['importeHoras25'] = $horas25 * $result['costoHora25'];
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0105',$result['importeHoras25'] );
				}else{
					$horas25 = 0;
					$result['importeHoras25']  =0;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0105',0 );
				}

				$result['costoHora35'] = (float)$result['costoHoraTrabajada'] * 1.35;
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0106') == 1){
					$horas35 = empty($listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_extras35']) ? 0 : (int)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_extras35'];
					$result['totalHorasEx'] += $horas35;							
					$result['importeHoras35'] = $horas35 * $result['costoHora35'];
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0106',$result['importeHoras35']);
				}else{
					$horas35 = 0;
					$result['importeHoras35'] = 0;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0106',0 );
				}

				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0201') == 1){
					$result['asignacionFamiliar'] = $variablesDeLey['rmv'] * $variablesDeLey['asignacion_familiar'] / 100;				
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0201',$result['asignacionFamiliar']); 
				}else{
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0201',0);
				}
				if( $result['tardanzas'] > 0 ){
					$result['importeTardanzas'] = $result['costoHoraTrabajada']/60 * $result['tardanzas'];
				}
				$result['totalRemuneracionComputable'] = (
					((float)$result['remBasica']) 
					- (empty($result['importeTardanzas']) ? 0 : (float)$result['importeTardanzas']) 
					+ (empty($result['importeHoras25']) ? 0 : (float)$result['importeHoras25']) 
					+ (empty($result['importeHoras35']) ? 0 : (float)$result['importeHoras35'])
					+ (empty($result['asignacionFamiliar']) ? 0 : (float)$result['asignacionFamiliar'])
					+ (empty($result['vacacionesComputables']) ? 0 : (float)$result['vacacionesComputables']) 
					);

				$result['noDeducibles'] = 0;
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0909') == 1){
					$result['movilidad'] = ($listaEmpleados[$key]['concepto_valor_json']['configuracion']['movilidad']/30) * ($result['diasTrabajados'] - $result['faltas']);
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0909',$result['movilidad']);
					$result['noDeducibles'] += (($listaEmpleados[$key]['concepto_valor_json']['configuracion']['movilidad']/30) * $result['diasTrabajados']) - (float)$result['movilidad'];
				}else{
					$result['movilidad'] = 0;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0909',0);
				}
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0914') == 1){
					$result['refrigerio'] = ($listaEmpleados[$key]['concepto_valor_json']['configuracion']['refrigerio']/30) * ($result['diasTrabajados'] - $result['faltas']);
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0914',$result['refrigerio']);
					$result['noDeducibles'] += (($listaEmpleados[$key]['concepto_valor_json']['configuracion']['refrigerio']/30) * $result['diasTrabajados']) - (float)$result['refrigerio'];
				}else{
					$result['refrigerio'] = 0;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0914',0);
				}

				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0917') == 1){
					$result['condicion_trabajo'] = ($listaEmpleados[$key]['concepto_valor_json']['configuracion']['condicion_trabajo']/30) * ($result['diasTrabajados'] - $result['faltas']);
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0917',$result['condicion_trabajo']);
					$result['noDeducibles'] += (($listaEmpleados[$key]['concepto_valor_json']['configuracion']['condicion_trabajo']/30) * $result['diasTrabajados']) - (float)$result['condicion_trabajo'];
				}else{
					$result['condicion_trabajo'] = 0;
					$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0917',0);
				}

				$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0706',$result['noDeducibles']);

				$result['remuneracionRegPensionario'] = (
					((float)$result['remBasica']) 
					- (empty($result['importeTardanzas']) ? 0 : (float)$result['importeTardanzas']) 
					+ (empty($result['importeHoras25']) ? 0 : (float)$result['importeHoras25']) 
					+ (empty($result['importeHoras35']) ? 0 : (float)$result['importeHoras35'])
					+ (empty($result['asignacionFamiliar']) ? 0 : (float)$result['asignacionFamiliar']) 
					+ (empty($result['vacacionesComputables']) ? 0 : (float)$result['vacacionesComputables']) 
					);

				$result['remuneracionRentaQuinta'] = (
					((float)$result['remComputable']) 
					+ (empty($result['importeHoras25']) ? 0 : (float)$result['importeHoras25']) 
					+ (empty($result['importeHoras35']) ? 0 : (float)$result['importeHoras35'])
					+ (empty($result['asignacionFamiliar']) ? 0 : (float)$result['asignacionFamiliar']) 
					+ (empty($result['vacacionesComputables']) ? 0 : (float)$result['vacacionesComputables']) 
					+ (empty($result['movilidad']) ? 0 : (float)$result['movilidad']) 
					+ (empty($result['refrigerio']) ? 0 : (float)$result['refrigerio']) 
					);

				$listaEmpleados[$key]['concepto_valor_json']['configuracion']['rem_reg_pensionario'] = $result['remuneracionRegPensionario'];
				$listaEmpleados[$key]['concepto_valor_json']['configuracion']['rem_renta_quinta'] = $result['remuneracionRentaQuinta'];

				$result['totalRemuneracion'] =  (
					((float)$result['remBasica']) 
					- (empty($result['importeTardanzas']) ? 0 : (float)$result['importeTardanzas']) 
					+ (empty($result['importeHoras25']) ? 0 : (float)$result['importeHoras25']) 
					+ (empty($result['importeHoras35']) ? 0 : (float)$result['importeHoras35'])
					+ (empty($result['asignacionFamiliar']) ? 0 : (float)$result['asignacionFamiliar']) 
					+ (empty($result['vacacionesComputables']) ? 0 : (float)$result['vacacionesComputables']) 
					+ (empty($result['vacacionesNoComputables']) ? 0 : (float)$result['vacacionesNoComputables']) 
					+ (empty($result['movilidad']) ? 0 : (float)$result['movilidad']) 
					+ (empty($result['condicion_trabajo']) ? 0 : (float)$result['condicion_trabajo']) 
					+ (empty($result['refrigerio']) ? 0 : (float)$result['refrigerio']) 
					+ (empty($result['gratificacionTrunca']) ? 0 : (float)$result['gratificacionTrunca'])
					);

				$result['totalRemuneracionProvision'] =  (
					((float)$result['remBasica']) 
					+ (empty($result['importeHoras25']) ? 0 : (float)$result['importeHoras25']) 
					+ (empty($result['importeHoras35']) ? 0 : (float)$result['importeHoras35'])
					+ (empty($result['asignacionFamiliar']) ? 0 : (float)$result['asignacionFamiliar']) 
					+ (empty($result['movilidad']) ? 0 : (float)$result['movilidad']) 
					+ (empty($result['condicion_trabajo']) ? 0 : (float)$result['condicion_trabajo']) 
					+ (empty($result['refrigerio']) ? 0 : (float)$result['refrigerio']) 
					);

			/*----------------- DESCUENTOS ------------------*/
				//ONP
					if($empl['reg_pensionario'] == 'ONP' && 
						obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0607') == 1){
						$result['aporteONP'] = ((float)$result['remuneracionRegPensionario']
												* $variablesDeLey['onp'] / 100);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0607',$result['aporteONP']);
					}else{
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0607',0);
					}

				//AFP
					if($empl['reg_pensionario'] == 'AFP' && 
						obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0608') == 1){
						$result['aporteObligatorio'] = (float)$result['remuneracionRegPensionario'] * (float)$empl['a_oblig'] / 100;
						$result['seguro'] = (float)$result['remuneracionRegPensionario'] * (float)$empl['p_seguro'] / 100;
						$result['comision'] = 0;
						if($empl['tipo_comision'] == 'MIXTA'){
							$result['comision'] = (float)$result['remuneracionRegPensionario'] * (float)$empl['comision_m'] / 100;
						}else if($empl['tipo_comision'] == 'FLUJO'){
							$result['comision'] = (float)$result['remuneracionRegPensionario'] * (float)$empl['comision'] / 100;
						}
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0608',$result['aporteObligatorio']);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0601',$result['comision']);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0606',$result['seguro']);
						$result['totalAporteAFP'] = (
							(float)$result['aporteObligatorio'] +
							(float)$result['comision'] +
							(float)$result['seguro'] 
							);
					}else{
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0608',0);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0601',0);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0606',0);
					}

				//RENTA QUINTA
					if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0605') == 1){
						$datosQuinta = array(
							'empleado' => $empl,
							'remuneracionRentaQuinta' => $result['remuneracionRentaQuinta'],
							'remuneracion_acum' => empty($listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_acum']) ? 0 : (float)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_acum'],
							'retencion_acum' => empty($listaEmpleados[$key]['concepto_valor_json']['configuracion']['retencion_acum']) ? 0 : (float)$listaEmpleados[$key]['concepto_valor_json']['configuracion']['retencion_acum'],
							'planilla' => $planilla,
							'gratificacion_trunca' => empty($result['gratificacionTrunca']) ? 0 : (float)$result['gratificacionTrunca'],
						);

						$result['rentaQuinta'] = $this->calculo_renta_quinta($datosQuinta);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0605',$result['rentaQuinta']);
					}else{
						$result['rentaQuinta'] = 0;
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0605', 0);
					}
					$result['total_desc_reg_pensionario'] = ((empty($result['aporteONP'])? 0 : (float)$result['aporteONP']) + 
												  			 (empty($result['totalAporteAFP'])? 0 : (float)$result['totalAporteAFP']) );
				
				$result['totalDescuentos'] = ( $result['total_desc_reg_pensionario'] + (empty($result['rentaQuinta'])? 0 : (float)$result['rentaQuinta']));
				$result['netoDepositar'] = (float)$result['totalRemuneracion'] - (float)$result['totalDescuentos'];

			/*-------------- APORTES EMPLEADOR --------------*/
				if(obtenerEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0804') == 1){
					$result['aporteEsSalud'] = 0;
					if($result['totalRemuneracionComputable'] > $variablesDeLey['rmv']){
						$result['aporteEsSalud'] = $result['totalRemuneracionComputable'] * $variablesDeLey['essalud'] / 100;
					}else{
						$result['aporteEsSalud'] = $variablesDeLey['rmv'] * $variablesDeLey['essalud'] / 100;
					}
	            	$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0804' ,$result['aporteEsSalud']);
				}

			/*---------------- PROVISIONES ------------------*/
				//NOTA: Si es calculo de liquidacion, 
				//la provision de vacacion y gratificacion se calcula previamente para sumarlo al pago.
				if(empty($allInputs['tipo_calculo'])){					
					$vacaciones = 0;
					if($calcularVacaciones){
						if($allInputs['empresa']['regimen'] == 1){
							$total_meses = 24;				
						}else if($allInputs['empresa']['regimen'] == 3){
							$total_meses = 12;
						}else{
							$total_meses = 12;
						}

						$rem_dada = $listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_dada'];
						$rem_comp = $listaEmpleados[$key]['concepto_valor_json']['configuracion']['sueldo_base'];
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['computable'] = ($rem_comp/$total_meses)/ 30 * (int)$result['diasTrabajados'] ;
						$listaEmpleados[$key]['concepto_valor_json']['provisiones']['vacaciones']['no_computable'] = (($rem_dada -  $rem_comp)/$total_meses) / 30 * (int)$result['diasTrabajados'] ;
					}

					$gratificacion = 0;
					if($calcularGratificaciones){
						$gratificacion = (float)($listaEmpleados[$key]['concepto_valor_json']['configuracion']['remuneracion_dada']/30 * ($result['diasTrabajados'] - $result['faltas']) )/6; 
					}
					$listaEmpleados[$key]['concepto_valor_json']['provisiones']['gratificacion'] = 	$gratificacion;				
				}

				$cts = 0;
				if($calcularCTS){
					$cts = (float)$result['totalRemuneracionProvision']/12;				
				}
				$listaEmpleados[$key]['concepto_valor_json']['provisiones']['cts'] = $cts;				

			/*-------- PAGO PROVISIONES LIQUIDACION ---------*/ //NOTA: vacaciones truncas y gratificacion trunca ya han sido calculado más arriba				
				if($calcularCTS){
					if(!empty($allInputs['tipo_calculo']) && $allInputs['tipo_calculo']== 'liquidacion'){					
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0904',1);
						$dataCTS = array(
							'empresa' => $allInputs['empresa'],
							'planilla' => $allInputs['planilla'],
							'empleado' => $listaEmpleados[$key],
						);
						$calculo_cts = $this->calculo_pago_cts_liquidacion($dataCTS);
						$suma_cts = (float)$calculo_cts['total_cts'] + (float)$listaEmpleados[$key]['concepto_valor_json']['provisiones']['cts'];
						$result['ctsTrunca'] = round($suma_cts);
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0904',round($suma_cts));
					}else{
						$result['ctsTrunca'] = 0;
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarValorConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'],'0904',0);						
						$listaEmpleados[$key]['concepto_valor_json']['conceptos'] = asignarEstadoConcepto($listaEmpleados[$key]['concepto_valor_json']['conceptos'], '0904',2);
					}
				}

			/*--------------- ACTUALIZACIONES ---------------*/
				$listaEmpleados[$key]['concepto_valor_json']['calculos'] = $result;
				$listaEmpleados[$key]['concepto_valor_json']['calculos']['horasDiarias'] = $listaEmpleados[$key]['concepto_valor_json']['configuracion']['horas_diarias'];

				$listaEmpleados[$key]['concepto_valor_json']['datos_bancarios'] = array(
					'idbanco' => $listaEmpleados[$key]['idbanco'],
					'descripcion_banco' => $listaEmpleados[$key]['descripcion_banco'],
					'cuenta_corriente' => $listaEmpleados[$key]['cuenta_corriente'],
				);	

				$dataActualizar = array(
					'idplanillaempleado' => $listaEmpleados[$key]['idplanillaempleado'],
					'estado_pe' => empty($allInputs['tipo_calculo']) ? 2 : 3, //2: calculo normal, 3:calculo de liquidacion
					'total_remuneraciones' => $result['totalRemuneracion'],
					'total_descuentos' => empty($result['totalDescuentos']) ? 0 : $result['totalDescuentos'],
					'neto_a_pagar' => $result['netoDepositar'],
					'total_desc_reg_pensionario' => $result['total_desc_reg_pensionario'],
					'renta_quinta' => (float)$result['rentaQuinta'],
					'aportes_empleador' => (float)$result['aporteEsSalud'],
					'concepto_valor_json' => json_encode($listaEmpleados[$key]['concepto_valor_json']),
				);

				if( !$this->model_empleado_planilla->m_actualizar($dataActualizar, TRUE) ){
					$hayError = TRUE;
				}
			
		}
		if($hayError){
			$arrData['message']='Ha ocurrido un error al calcular la planilla.';
			$arrData['flag']=0;
		}


		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function calculo_pago_gratificaciones_liquidacion($allInputs){
		$indEmpl = array($allInputs['empleado']['idempleado']);
    	$mes_planilla = date('n', strtotime($allInputs['planilla']['fecha_cierre']));

    	if($mes_planilla <= 7){
	    	$allInputs['mes_desde'] = 1;
	    	$allInputs['mes_hasta'] = 6;
	    	$allInputs['mes_desde_string'] = 'ENERO';
	    	$allInputs['mes_hasta_string'] = 'JUNIO'; 
    	}else{
	    	$allInputs['mes_desde'] = 7;
	    	$allInputs['mes_hasta'] = 11; 
	    	$allInputs['mes_desde_string'] = 'JULIO';
	    	$allInputs['mes_hasta_string'] = 'DICIEMBRE';
    	}

    	$allInputs['idempresa'] = $allInputs['empresa']['id'];

    	$listaPlanillasAnt = $this->model_empleado_planilla->m_cargar_planillas_anteriores_todos($allInputs,$indEmpl);

    	$arrGratificacion = array();
    	foreach ($listaPlanillasAnt as $ind => $empl) {
    		$listaPlanillasAnt[$ind]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));
    		$provision = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['gratificacion'];
    		$sueldo = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['remuneracion_dada'];

    		$arrGratificacion[$empl['idempleado']]['idempleado'] = $empl['idempleado'];
    		/*$arrGratificacion[$empl['idempleado']]['empleado'] = $empl['empleado'];
    		$arrGratificacion[$empl['idempleado']]['fecha_ingreso'] = $empl['fecha_ingreso'];
    		$arrGratificacion[$empl['idempleado']]['sueldo_contrato'] = $empl['sueldo_contrato'];
    		$arrGratificacion[$empl['idempleado']]['tipo_documento'] = $empl['tipo_documento'];
    		$arrGratificacion[$empl['idempleado']]['numero_documento'] = $empl['numero_documento'];
    		$arrGratificacion[$empl['idempleado']]['centro_costo'] = $empl['centro_costo'];
    		$arrGratificacion[$empl['idempleado']]['cuenta_corriente'] = $empl['cuenta_corriente'];
    		$arrGratificacion[$empl['idempleado']]['remuneracion_dada'] = $sueldo;
    		$arrGratificacion[$empl['idempleado']]['sede'] = $empl['sede'];
    		$arrGratificacion[$empl['idempleado']]['descripcion_ca'] = $empl['descripcion_ca'];
    		$arrGratificacion[$empl['idempleado']]['concepto_valor_json'] = $empl['concepto_valor_json'];*/
    		
    		if(!empty($arrGratificacion[$empl['idempleado']]['total_gratificacion'])){
    			$arrGratificacion[$empl['idempleado']]['total_gratificacion'] += $provision;
    		}else{
    			$arrGratificacion[$empl['idempleado']]['total_gratificacion'] = $provision;
    		}

    		$arrGratificacion[$empl['idempleado']]['meses'][$empl['mes']] =  $provision;

    	}

    	return $arrGratificacion[$allInputs['empleado']['idempleado']];
	}

	private function calculo_pago_cts_liquidacion($allInputs){
		$indEmpl = array($allInputs['empleado']['idempleado']);
    	$mes_planilla = date('n', strtotime($allInputs['planilla']['fecha_cierre']));
    	$anio_planilla = date('Y', strtotime($allInputs['planilla']['fecha_cierre']));

    	if($mes_planilla <= 5){
	    	$allInputs['aniomes_desde'] = ($anio_planilla-1) . '11';
	    	$allInputs['aniomes_hasta'] = $anio_planilla . '04';
    	}else if($mes_planilla >=11){
    		$allInputs['aniomes_desde'] = ($anio_planilla) . '11';
	    	$allInputs['aniomes_hasta'] = ($anio_planilla+1) . '04';
    	}else{
	    	$allInputs['aniomes_desde'] = $anio_planilla.'05';
	    	$allInputs['aniomes_hasta'] = $anio_planilla.'10';
	    	$allInputs['mes_desde'] = 5;
	    	$allInputs['mes_hasta'] = 10;
    	}

    	$allInputs['idempresa'] = $allInputs['empresa']['id'];

    	$listaPlanillasAnt = $this->model_empleado_planilla->m_cargar_planillas_anteriores_todos($allInputs,$indEmpl,TRUE);

	    	$arrGratificacion = array();
	    	foreach ($listaPlanillasAnt as $ind => $empl) {
	    		$listaPlanillasAnt[$ind]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));
	    		$cts = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['cts'];
	    		$sueldo = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['remuneracion_dada'];
	    		$faltas = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['faltas'];

	    		/*$arrGratificacion[$empl['idempleado']]['idempleado'] = $empl['idempleado'];
	    		$arrGratificacion[$empl['idempleado']]['empleado'] = $empl['empleado'];
	    		$arrGratificacion[$empl['idempleado']]['fecha_ingreso'] = $empl['fecha_ingreso'];
	    		$arrGratificacion[$empl['idempleado']]['sueldo_contrato'] = $empl['sueldo_contrato'];
	    		$arrGratificacion[$empl['idempleado']]['tipo_documento'] = $empl['tipo_documento'];
	    		$arrGratificacion[$empl['idempleado']]['numero_documento'] = $empl['numero_documento'];
	    		$arrGratificacion[$empl['idempleado']]['centro_costo'] = $empl['centro_costo'];
	    		$arrGratificacion[$empl['idempleado']]['cuenta_corriente_cts'] = $empl['cuenta_corriente_cts'];
	    		$arrGratificacion[$empl['idempleado']]['banco_cts'] = $empl['banco_cts'];
	    		$arrGratificacion[$empl['idempleado']]['remuneracion_dada'] = $sueldo;
	    		$arrGratificacion[$empl['idempleado']]['sede'] = $empl['sede'];
	    		$arrGratificacion[$empl['idempleado']]['descripcion_ca'] = $empl['descripcion_ca'];
	    		$arrGratificacion[$empl['idempleado']]['concepto_valor_json'] = $empl['concepto_valor_json'];*/
	    		
	    		if(!empty($arrGratificacion[$empl['idempleado']]['total_cts'])){
	    			$arrGratificacion[$empl['idempleado']]['total_cts'] += $cts;
	    		}else{
	    			$arrGratificacion[$empl['idempleado']]['total_cts'] = $cts;
	    		}

	    		if(!empty($arrGratificacion[$empl['idempleado']]['faltas'])){
	    			$arrGratificacion[$empl['idempleado']]['faltas'] += $faltas;
	    		}else{
	    			$arrGratificacion[$empl['idempleado']]['faltas'] = $faltas;
	    		}

	    		if(!empty($listaPlanillasAnt[$ind]['concepto_valor_json']['pago_gratificacion'])){
	    			$pago_gratificacion = $listaPlanillasAnt[$ind]['concepto_valor_json']['pago_gratificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['monto'] = $pago_gratificacion['gratificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['bonificacion'] = $pago_gratificacion['bonificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['periodo'] = $pago_gratificacion['periodo'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['periodo_string'] = $pago_gratificacion['periodo_string'];
	    		}

	    		$arrGratificacion[$empl['idempleado']]['meses'][$empl['mes']] =  $cts;

	    	}

    	return $arrGratificacion[$allInputs['empleado']['idempleado']];
	}

	public function calcular_pago_gratificaciones(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 1;
    	$arrData['message'] = 'Datos generados exitosamente';

    	$mes_planilla = date('n', strtotime($allInputs['fecha_cierre']));
    	if($mes_planilla != 7 && $mes_planilla != 12){
    		$arrData['flag'] = 0;
    		$arrData['message'] = 'Solo puede generar gratificaciones en los meses JULIO y DICIEMBRE';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	/*---------------- CALCULO DE DATOS ----------------*/
	    	$listaEmpleados = $this->model_empleado_planilla->m_cargar_estos_empleados_planilla($allInputs);

	    	$indEmpl = array();
	    	foreach ($listaEmpleados as $key => $empl) {
	    		array_push($indEmpl, $empl['idempleado']);
	    	}

	    	if($mes_planilla == 7){
		    	$allInputs['mes_desde'] = 1;
		    	$allInputs['mes_hasta'] = 6;
		    	$allInputs['mes_desde_string'] = 'ENERO';
		    	$allInputs['mes_hasta_string'] = 'JUNIO'; 
	    	}else{
		    	$allInputs['mes_desde'] = 7;
		    	$allInputs['mes_hasta'] = 11; 
		    	$allInputs['mes_desde_string'] = 'JULIO';
		    	$allInputs['mes_hasta_string'] = 'DICIEMBRE';
	    	}

	    	$listaPlanillasAnt = $this->model_empleado_planilla->m_cargar_planillas_anteriores_todos($allInputs,$indEmpl);

	    	$arrGratificacion = array();
	    	foreach ($listaPlanillasAnt as $ind => $empl) {
	    		$listaPlanillasAnt[$ind]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));
	    		$provision = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['gratificacion'];
	    		$sueldo = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['remuneracion_dada'];

	    		$arrGratificacion[$empl['idempleado']]['idempleado'] = $empl['idempleado'];
	    		$arrGratificacion[$empl['idempleado']]['empleado'] = $empl['empleado'];
	    		$arrGratificacion[$empl['idempleado']]['fecha_ingreso'] = $empl['fecha_ingreso'];
	    		$arrGratificacion[$empl['idempleado']]['sueldo_contrato'] = $empl['sueldo_contrato'];
	    		$arrGratificacion[$empl['idempleado']]['tipo_documento'] = $empl['tipo_documento'];
	    		$arrGratificacion[$empl['idempleado']]['numero_documento'] = $empl['numero_documento'];
	    		$arrGratificacion[$empl['idempleado']]['centro_costo'] = $empl['centro_costo'];
	    		$arrGratificacion[$empl['idempleado']]['cuenta_corriente'] = $empl['cuenta_corriente'];
	    		$arrGratificacion[$empl['idempleado']]['remuneracion_dada'] = $sueldo;
	    		$arrGratificacion[$empl['idempleado']]['sede'] = $empl['sede'];
	    		$arrGratificacion[$empl['idempleado']]['descripcion_ca'] = $empl['descripcion_ca'];
	    		$arrGratificacion[$empl['idempleado']]['concepto_valor_json'] = $empl['concepto_valor_json'];
	    		
	    		if(!empty($arrGratificacion[$empl['idempleado']]['total_gratificacion'])){
	    			$arrGratificacion[$empl['idempleado']]['total_gratificacion'] += $provision;
	    		}else{
	    			$arrGratificacion[$empl['idempleado']]['total_gratificacion'] = $provision;
	    		}

	    		$arrGratificacion[$empl['idempleado']]['meses'][$empl['mes']] =  $provision;

	    	}

    	/*---------------- GENERACION DE EXCEl ----------------*/
    	$cont = 0;
    	$currentCellEncabezado = 6;

    	// ESTILOS
	    	$styleArrayTitle = array(
			    'font'=>  array(
			        'bold'  => false,
			        'size'  => 14,
			        'name'  => 'Verdana',
			        'color' => array('rgb' => '000000')
			    ),
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			    ),
		    );

		    $styleArrayHeader = array(
		      'borders' => array(
		        'allborders' => array( 
		          'style' => PHPExcel_Style_Border::BORDER_THIN,
		          'color' => array('rgb' => '00bcd4') 
		        ) 
		      ),
		      'alignment' => array(
		          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		      ),
		      'font'=>  array(
		          'bold'  => true,
		          'size'  => 10,
		          'name'  => 'Verdana',
		          'color' => array('rgb' => '0790a2') 
		      ),
		      'fill' => array( 
		          'type' => PHPExcel_Style_Fill::FILL_SOLID,
		          'startcolor' => array( 'rgb' => '9de5ee', ),
		       ),
		    ); 
	    
	    	$styleArrayProd = array(
		        'borders' => array(
		          'allborders' => array( 
		            'style' => PHPExcel_Style_Border::BORDER_THIN,
		            'color' => array('rgb' => '000000') 
		          ) 
		        ),
		        'font'=>  array(
		            'bold'  => false,
		            'size'  => 10,
		            'name'  => 'calibri',
		            // 'color' => array('rgb' => '000000') 
		        ),
		      );

    	//CABECERA
	    	$arrayCabecera = array();
	    	array_push($arrayCabecera, 
	    		'N°', 'TIPO', 'Nº DOC.', 'APELLIDOS Y NOMBRES', 
	    		'FECHA DE INGRESO', 'SEDE', 'CENTRO COSTO', 
	    		'CARGO','CUENTA CORRIENTE',
	    		'REMUN. DADA');

	    	if($mes_planilla == 7){
	    		$meses = get_rangomeses_nombre($allInputs['mes_desde'],$allInputs['mes_hasta'],TRUE);
	    	}else{
	    		$meses = get_rangomeses_nombre($allInputs['mes_desde'],(int)$allInputs['mes_hasta']+1,TRUE);	    		
	    	}

	    	$arrayCabecera = array_merge($arrayCabecera, $meses['meses']); 

	    	array_push($arrayCabecera, 
	    		'TOTAL REMUNERACION','BONIFICACION 9%', 'TOTAL GRATIFICACION');

	    	$arrWidths = array(
	    		7,	10, 13,	40,			// 'N°', 'TIPO', 'Nº DOC.', 'APELLIDOS Y NOMBRES',
	    		12,	22,	28,			 	// 'FECHA DE INGRESO', 'SEDE', 'CENTRO COSTO',
	    		28,	35,					// 'CARGO','CUENTA CORRIENTE', 
	    		15,						// 'REMUN. DADA'
	    		15,	15,	15,	15,	15,	15,	// MESES * 6,
	    		20,	20,	20,				// 'TOTAL REMUNERACION','BONIFICACION 9%', 'TOTAL GRATIFICACION',
	    	);

	    //SETEO DATOS
	    	$this->excel->setActiveSheetIndex(0);
    		$this->excel->getActiveSheet()->setTitle('CALCULO DE GRATIFICACIONES');

    		$this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['empresa']['descripcion']); 
		    $this->excel->getActiveSheet()->mergeCells('A1:D1');
    		$this->excel->getActiveSheet()->getCell('A2')->setValue($allInputs['empresa']['ruc']); 
		    $this->excel->getActiveSheet()->mergeCells('A2:D2');

    		$titulo = 'CALCULO DE GRATIFICACIÓN ' . $allInputs['mes_desde_string'] . ' - ' .$allInputs['mes_hasta_string'] . ' ' . date('Y',strtotime($allInputs['fecha_cierre']));
    		$this->excel->getActiveSheet()->getCell('D3')->setValue($titulo); 

		    $this->excel->getActiveSheet()->mergeCells('D3:G3');
		    $this->excel->getActiveSheet()->getStyle('A1:D3')->applyFromArray($styleArrayTitle);

    		$this->excel->getActiveSheet()->fromArray($arrayCabecera, null, 'A'.$currentCellEncabezado);
    		$endColum = 'S';

	    	$arrListado = array();
	    	$index = 1;
	    	$this->db->trans_start();
	    	foreach ($arrGratificacion as $key => $row) {	    		
	    		$fila = array(
	    			$index,
	    			$row['tipo_documento'],
	    			$row['numero_documento'],
	    			$row['empleado'],
	    			darFormatoDMY($row['fecha_ingreso']),
	    			$row['sede'],
	    			$row['centro_costo'],
	    			$row['descripcion_ca'],	    			
	    			$row['cuenta_corriente'],
	    			$row['remuneracion_dada'],	    			
	    		);

	    		foreach ($meses['meses_num'] as $ind => $mes) {
	    			$value_mes = empty($row['meses'][$mes]) ? 0 : $row['meses'][$mes];
	    			if($mes_planilla == 12 && $mes == 12){
	    				$gratificacion_dic = $row['remuneracion_dada']/6;
	    				$value_mes = $gratificacion_dic;
	    			}

	    			array_push($fila,  round((float)$value_mes,2));
	    		}

	    		if($mes_planilla == 12){
	    			$gratificacion = $row['total_gratificacion'] + $gratificacion_dic;
	    		}else{
	    			$gratificacion = $row['total_gratificacion'];
	    		}

	    		array_push($fila, round($gratificacion,2));
	    		$bonificacion = round($gratificacion * 9/100,2);
	    		array_push($fila, $bonificacion);
	    		array_push($fila, round((float)$gratificacion + $bonificacion,2));

	    		array_push($arrListado, $fila);

	    		$array_conceptos = objectToArray(json_decode($row['concepto_valor_json']));

	    		$array_conceptos['conceptos'] = asignarEstadoConcepto($array_conceptos['conceptos'], '0406',1);
	    		$array_conceptos['conceptos'] = asignarValorConcepto($array_conceptos['conceptos'], '0406', round($gratificacion,2));

				$array_conceptos['conceptos'] = asignarEstadoConcepto($array_conceptos['conceptos'], '0312',1);
	    		$array_conceptos['conceptos'] = asignarValorConcepto($array_conceptos['conceptos'], '0312', $bonificacion);

	    		$array_conceptos['pago_gratificacion'] = array(
	    												'gratificacion' => (float)$gratificacion,
	    												'bonificacion' => (float)$bonificacion,
	    												'periodo' => date('Ym',strtotime($allInputs['fecha_cierre'])),
	    												'periodo_string' => darFormatoMesAnoPlanilla($allInputs['fecha_cierre'])
	    												);
	    		$data = array(
	    			'concepto_valor_json' => json_encode($array_conceptos),
	    			'idempleado' => $row['idempleado'],
	    			'idplanilla' => $allInputs['id'],
	    			);
	    		$this->model_empleado_planilla->m_actualizar_solo_conceptos_empl($data);

	    		$index++;
	    	}
			$this->db->trans_complete();

	    	$i = 0;
			$columnas = createColumnsArray($endColum);

			foreach($columnas  as $columnID) {
				$this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
				$i++;
			}

    		$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
		    $currentCellTotal = count($arrListado) + $currentCellEncabezado;
		    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
		    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
		    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
		    $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);

		// ALINEACIONES
		    $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':I'.$currentCellTotal)->getNumberFormat()->setFormatCode('0000-0000-0000000000');
	    	$this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':I'.$currentCellTotal)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		    $this->excel->getActiveSheet()->getStyle('J'.($currentCellEncabezado+1).':S' .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
	    	$this->excel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    	
    	//SALIDA
	    	$objWriter = new PHPExcel_Writer_Excel2007($this->excel);
		    $dateTime = date('YmdHis');
		    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');

		    $arrData = array(
		      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
		      'flag'=> 1
		    );

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function calcular_pago_cts(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 1;
    	$arrData['message'] = 'Datos generados exitosamente';

    	$mes_planilla = date('n', strtotime($allInputs['fecha_cierre']));
    	$anio_planilla = date('Y', strtotime($allInputs['fecha_cierre']));
    	if($mes_planilla != 5 && $mes_planilla != 11){
    		$arrData['flag'] = 0;
    		$arrData['message'] = 'Solo puede generar CTS en los meses MAYO y NOVIEMBRE';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	/*---------------- CALCULO DE DATOS ----------------*/
	    	$listaEmpleados = $this->model_empleado_planilla->m_cargar_estos_empleados_planilla($allInputs);

	    	$indEmpl = array();
	    	foreach ($listaEmpleados as $key => $empl) {
	    		array_push($indEmpl, $empl['idempleado']);
	    	}

	    	if($mes_planilla == 5){
		    	$allInputs['aniomes_desde'] = ($anio_planilla-1) . '11';
		    	$allInputs['aniomes_hasta'] = $anio_planilla.'04';
		    	$allInputs['mes_desde_string'] = 'NOVIEMBRE '.($anio_planilla-1);
		    	$allInputs['mes_hasta_string'] = 'ABRIL ' .$anio_planilla; 
		    	$allInputs['mes_gratificacion_string'] = 'DICIEMBRE ' .($anio_planilla-1); 
		    	$allInputs['mes_desde'] = 11;
		    	$allInputs['mes_hasta'] = 4;
		    	$meses = get_rangomeses_nombre($allInputs['mes_desde'],$allInputs['mes_hasta'],TRUE,TRUE);
	    	}else{
		    	$allInputs['aniomes_desde'] = $anio_planilla.'05';
		    	$allInputs['aniomes_hasta'] = $anio_planilla.'10';
		    	$allInputs['mes_desde_string'] = 'MAYO '.$anio_planilla;
		    	$allInputs['mes_hasta_string'] = 'OCTUBRE '.$anio_planilla;
		    	$allInputs['mes_gratificacion_string'] = 'JULIO ' .($anio_planilla); 
		    	$allInputs['mes_desde'] = 5;
		    	$allInputs['mes_hasta'] = 10;
		    	$meses = get_rangomeses_nombre($allInputs['mes_desde'],$allInputs['mes_hasta'],TRUE,FALSE);
	    	}


	    	$listaPlanillasAnt = $this->model_empleado_planilla->m_cargar_planillas_anteriores_todos($allInputs,$indEmpl,TRUE);

	    	$arrGratificacion = array();
	    	foreach ($listaPlanillasAnt as $ind => $empl) {
	    		$listaPlanillasAnt[$ind]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));
	    		$cts = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['cts'];
	    		$sueldo = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['remuneracion_dada'];
	    		$faltas = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['faltas'];

	    		$arrGratificacion[$empl['idempleado']]['idempleado'] = $empl['idempleado'];
	    		$arrGratificacion[$empl['idempleado']]['empleado'] = $empl['empleado'];
	    		$arrGratificacion[$empl['idempleado']]['fecha_ingreso'] = $empl['fecha_ingreso'];
	    		$arrGratificacion[$empl['idempleado']]['sueldo_contrato'] = $empl['sueldo_contrato'];
	    		$arrGratificacion[$empl['idempleado']]['tipo_documento'] = $empl['tipo_documento'];
	    		$arrGratificacion[$empl['idempleado']]['numero_documento'] = $empl['numero_documento'];
	    		$arrGratificacion[$empl['idempleado']]['centro_costo'] = $empl['centro_costo'];
	    		$arrGratificacion[$empl['idempleado']]['cuenta_corriente_cts'] = $empl['cuenta_corriente_cts'];
	    		$arrGratificacion[$empl['idempleado']]['banco_cts'] = $empl['banco_cts'];
	    		$arrGratificacion[$empl['idempleado']]['remuneracion_dada'] = $sueldo;
	    		$arrGratificacion[$empl['idempleado']]['sede'] = $empl['sede'];
	    		$arrGratificacion[$empl['idempleado']]['descripcion_ca'] = $empl['descripcion_ca'];
	    		$arrGratificacion[$empl['idempleado']]['concepto_valor_json'] = $empl['concepto_valor_json'];
	    		
	    		if(!empty($arrGratificacion[$empl['idempleado']]['total_cts'])){
	    			$arrGratificacion[$empl['idempleado']]['total_cts'] += $cts;
	    		}else{
	    			$arrGratificacion[$empl['idempleado']]['total_cts'] = $cts;
	    		}

	    		if(!empty($arrGratificacion[$empl['idempleado']]['faltas'])){
	    			$arrGratificacion[$empl['idempleado']]['faltas'] += $faltas;
	    		}else{
	    			$arrGratificacion[$empl['idempleado']]['faltas'] = $faltas;
	    		}

	    		if(!empty($listaPlanillasAnt[$ind]['concepto_valor_json']['pago_gratificacion'])){
	    			$pago_gratificacion = $listaPlanillasAnt[$ind]['concepto_valor_json']['pago_gratificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['monto'] = $pago_gratificacion['gratificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['bonificacion'] = $pago_gratificacion['bonificacion'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['periodo'] = $pago_gratificacion['periodo'];
	    			$arrGratificacion[$empl['idempleado']]['ult_gratificacion']['periodo_string'] = $pago_gratificacion['periodo_string'];
	    		}

	    		$arrGratificacion[$empl['idempleado']]['meses'][$empl['mes']] =  $cts;

	    	}

    	/*---------------- GENERACION DE EXCEl ----------------*/
    	$cont = 0;
    	$currentCellEncabezado = 6;

    	// ESTILOS
	    	$styleArrayTitle = array(
			    'font'=>  array(
			        'bold'  => false,
			        'size'  => 14,
			        'name'  => 'Verdana',
			        'color' => array('rgb' => '000000')
			    ),
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			    ),
		    );

		    $styleArrayHeader = array(
		      'borders' => array(
		        'allborders' => array( 
		          'style' => PHPExcel_Style_Border::BORDER_THIN,
		          'color' => array('rgb' => '00bcd4') 
		        ) 
		      ),
		      'alignment' => array(
		          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		      ),
		      'font'=>  array(
		          'bold'  => true,
		          'size'  => 10,
		          'name'  => 'Verdana',
		          'color' => array('rgb' => '0790a2') 
		      ),
		      'fill' => array( 
		          'type' => PHPExcel_Style_Fill::FILL_SOLID,
		          'startcolor' => array( 'rgb' => '9de5ee', ),
		       ),
		    ); 
	    
	    	$styleArrayProd = array(
		        'borders' => array(
		          'allborders' => array( 
		            'style' => PHPExcel_Style_Border::BORDER_THIN,
		            'color' => array('rgb' => '000000') 
		          ) 
		        ),
		        'font'=>  array(
		            'bold'  => false,
		            'size'  => 10,
		            'name'  => 'calibri',
		            // 'color' => array('rgb' => '000000') 
		        ),
		      );

    	//CABECERA
	    	$arrayCabecera = array();
	    	array_push($arrayCabecera, 
	    		'N°', 'TIPO', 'Nº DOC.', 'APELLIDOS Y NOMBRES', 
	    		'FECHA DE INGRESO', 'SEDE', 'CENTRO COSTO', 
	    		'CARGO','CUENTA CORRIENTE',
	    		'SUELDO BÁSICO', 'ASIGNACIÓN FAMILIAR', 'REMUNERACIÓN');
	    	$arrayCabecera = array_merge($arrayCabecera, $meses['meses']); 

	    	array_push($arrayCabecera, 
	    		'TOTAL PROVISIONES','TOTAL GRATIFICACION ' . $allInputs['mes_gratificacion_string'], '1/6 GRATIFICACION' ,'CTS A DEPOSITAR');

	    	$arrWidths = array(
	    		7,	10, 13,	40,			// 'N°', 'TIPO', 'Nº DOC.', 'APELLIDOS Y NOMBRES',
	    		12,	22,	28,			 	// 'FECHA DE INGRESO', 'SEDE', 'CENTRO COSTO',
	    		28,	35,					// 'CARGO','CUENTA CORRIENTE', 
	    		15,	15, 15,				// 'SUELDO BÁSICO', 'ASIGNACIÓN FAMILIAR', 'REMUNERACIÓN',
	    		15,	15,	15,	15,	15,	15,	// MESES * 6,
	    		22,	22,	22, 22, 		// 'TOTAL PROVISIONES','TOTAL GRATIFICACION ----MES----', '1/6 GRATIFICACION' , 'CTS A DEPOSITAR'
	    	);

	    //SETEO DATOS
	    	$this->excel->setActiveSheetIndex(0);
    		$this->excel->getActiveSheet()->setTitle('CALCULO DE CTS');

    		$this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['empresa']['descripcion']); 
		    $this->excel->getActiveSheet()->mergeCells('A1:D1');
    		$this->excel->getActiveSheet()->getCell('A2')->setValue($allInputs['empresa']['ruc']); 
		    $this->excel->getActiveSheet()->mergeCells('A2:D2');

    		$titulo = 'CALCULO DE CTS ' . $allInputs['mes_desde_string'] . ' - ' .$allInputs['mes_hasta_string'];
    		$this->excel->getActiveSheet()->getCell('D3')->setValue($titulo); 

		    $this->excel->getActiveSheet()->mergeCells('D3:G3');
		    $this->excel->getActiveSheet()->getStyle('A1:D3')->applyFromArray($styleArrayTitle);

    		$this->excel->getActiveSheet()->fromArray($arrayCabecera, null, 'A'.$currentCellEncabezado);
    		$endColum = 'V';

	    	$arrListado = array();
	    	$index = 1;

	    	foreach ($arrGratificacion as $key => $row) {
	    		$array_conceptos = objectToArray(json_decode($row['concepto_valor_json']));
	    		if(obtenerEstadoConcepto($array_conceptos['conceptos'], '0201') == 1){
	    			$asignacion_fam = obtenerValorConcepto($array_conceptos['conceptos'], '0201');
	    		}else{
	    			$asignacion_fam = 0;
	    		}
	    		$fila = array(
	    			$index,
	    			$row['tipo_documento'],
	    			$row['numero_documento'],
	    			$row['empleado'],
	    			darFormatoDMY($row['fecha_ingreso']),
	    			$row['sede'],
	    			$row['centro_costo'],
	    			$row['descripcion_ca'],	    			
	    			$row['cuenta_corriente_cts'],
	    			$row['remuneracion_dada']-$asignacion_fam,	    			
	    			$asignacion_fam,	    			
	    			$row['remuneracion_dada'],	    			
	    		);

	    		foreach ($meses['meses_num'] as $ind => $mes) {
	    			$value_mes = empty($row['meses'][$mes]) ? 0 : $row['meses'][$mes];
	    			array_push($fila,  round((float)$value_mes,2));
	    		}

	    		$cts = round((float)$row['total_cts'],2);
	    		array_push($fila, $cts);

	    		$gratificacion = round($row['ult_gratificacion']['monto'],2);
	    		array_push($fila, $gratificacion);
	    		$sexto_gratificacion = round($gratificacion/6, 2);
	    		array_push($fila, $sexto_gratificacion);
	    		$fraccion_gratificacion = ($sexto_gratificacion/12) * 6;
	    		array_push($fila, $cts + $fraccion_gratificacion);

	    		array_push($arrListado, $fila);    		

	    		$array_conceptos['conceptos'] = asignarEstadoConcepto($array_conceptos['conceptos'], '0904',1);
	    		$array_conceptos['conceptos'] = asignarValorConcepto($array_conceptos['conceptos'], '0904', (float)$cts + (float)$fraccion_gratificacion);

	    		$array_conceptos['pago_cts'] = array(
													'acumulado_cts' => (float)$cts,
													'sexto_gratificacion' => (float)$sexto_gratificacion,
													'fraccion_gratificacion' => (float)$fraccion_gratificacion,
													'periodo' => (date('Ym',strtotime($allInputs['fecha_cierre']))),
													'periodo_string' => darFormatoMesAnoPlanilla($allInputs['fecha_cierre']),
													'remuneracion_dada' => $row['remuneracion_dada'],
													'asignacion_fam' => $asignacion_fam,
													'faltas' => $row['faltas'],
													'cuenta_corriente_cts' => $row['cuenta_corriente_cts'],
													'banco_cts' => $row['banco_cts'],
												);
	    		$data = array(
	    			'concepto_valor_json' => json_encode($array_conceptos),
	    			'idempleado' => $row['idempleado'],
	    			'idplanilla' => $allInputs['id'],
	    			);
	    		$this->model_empleado_planilla->m_actualizar_solo_conceptos_empl($data);

	    		$index++;
	    	}

	    	$i = 0;
			$columnas = createColumnsArray($endColum);

			foreach($columnas  as $columnID) {
				$this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
				$i++;
			}

    		$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
		    $currentCellTotal = count($arrListado) + $currentCellEncabezado;
		    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
		    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
		    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
		    $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);

		// ALINEACIONES
		    $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':I'.$currentCellTotal)->getNumberFormat()->setFormatCode('0000-0000-0000000000');
	    	$this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':I'.$currentCellTotal)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		    $this->excel->getActiveSheet()->getStyle('J'.($currentCellEncabezado+1).':V' .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
	    	$this->excel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    	
    	//SALIDA
	    	$objWriter = new PHPExcel_Writer_Excel2007($this->excel);
		    $dateTime = date('YmdHis');
		    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');

		    $arrData = array(
		      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
		      'flag'=> 1
		    );

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function calculo_vacaciones_empleado($allInputs,$tipo_calculo=FALSE){
		$fecha = $allInputs['empleado']['fecha_ingreso'];
		$fechaInicialRango = date('Y', strtotime($allInputs['planilla']['fecha_cierre']) ).date('md', strtotime($fecha) );

		$mes_planilla = date('n', strtotime($allInputs['planilla']['fecha_cierre']));
		$mes_vacacion = date('n', strtotime($fecha));
		if($mes_planilla > $mes_vacacion){
			$datos = array(
				'idempresa' => $allInputs['empresa']['id'],
				'aniomes_desde' => date('Ym', strtotime($fechaInicialRango)),
				'aniomes_hasta' => date('Ym', strtotime('+1 year -1 month' , strtotime($fechaInicialRango))),
			);
		}else{
			$datos = array(
				'idempresa' => $allInputs['empresa']['id'],
				'aniomes_desde' => date('Ym', strtotime('-1year' , strtotime($fechaInicialRango))),
				'aniomes_hasta' => date('Ym', strtotime('-1 month', strtotime($fechaInicialRango))),
			);
		}

		$indEmpl = array($allInputs['empleado']['idempleado']);
		$listaPlanillasAnt = $this->model_empleado_planilla->m_cargar_planillas_anteriores_todos($datos,$indEmpl,TRUE);

    	$arrVacaciones = array();
    	foreach ($listaPlanillasAnt as $ind => $empl) {
    		$listaPlanillasAnt[$ind]['concepto_valor_json'] = objectToArray(json_decode($empl['concepto_valor_json']));

    		$provision_comp = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['vacaciones']['computable'];
    		$provision_no_comp = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['provisiones']['vacaciones']['no_computable'];
    		$sueldo = (float)$listaPlanillasAnt[$ind]['concepto_valor_json']['configuracion']['remuneracion_dada'];

    		/*$arrVacaciones[$empl['idempleado']]['idempleado'] = $empl['idempleado'];
    		$arrVacaciones[$empl['idempleado']]['empleado'] = $empl['empleado'];
    		$arrVacaciones[$empl['idempleado']]['fecha_ingreso'] = $empl['fecha_ingreso'];
    		$arrVacaciones[$empl['idempleado']]['sueldo_contrato'] = $empl['sueldo_contrato'];
    		$arrVacaciones[$empl['idempleado']]['tipo_documento'] = $empl['tipo_documento'];
    		$arrVacaciones[$empl['idempleado']]['numero_documento'] = $empl['numero_documento'];
    		$arrVacaciones[$empl['idempleado']]['centro_costo'] = $empl['centro_costo'];
    		$arrVacaciones[$empl['idempleado']]['cuenta_corriente'] = $empl['cuenta_corriente'];
    		$arrVacaciones[$empl['idempleado']]['remuneracion_dada'] = $sueldo;
    		$arrVacaciones[$empl['idempleado']]['sede'] = $empl['sede'];
    		$arrVacaciones[$empl['idempleado']]['descripcion_ca'] = $empl['descripcion_ca'];
    		$arrVacaciones[$empl['idempleado']]['concepto_valor_json'] = $empl['concepto_valor_json'];*/
    		
    		if(!empty($arrVacaciones[$empl['idempleado']]['total_vacaciones']['computable'])){
    			$arrVacaciones[$empl['idempleado']]['total_vacaciones']['computable'] += $provision_comp;
    		}else{
    			$arrVacaciones[$empl['idempleado']]['total_vacaciones']['computable'] = $provision_comp;
    		}

    		if(!empty($arrVacaciones[$empl['idempleado']]['total_vacaciones']['no_computable'])){
    			$arrVacaciones[$empl['idempleado']]['total_vacaciones']['no_computable'] += $provision_no_comp;
    		}else{
    			$arrVacaciones[$empl['idempleado']]['total_vacaciones']['no_computable'] = $provision_no_comp;
    		}

    		$arrVacaciones[$empl['idempleado']]['meses'][$empl['mes']]['computable'] =  $provision_comp;
    		$arrVacaciones[$empl['idempleado']]['meses'][$empl['mes']]['no_computable'] =  $provision_no_comp;

    	}

    	if(empty($arrVacaciones[$allInputs['empleado']['idempleado']])){
    		$arrVacaciones[$allInputs['empleado']['idempleado']]['total_vacaciones']['no_computable'] = 0;
    		$arrVacaciones[$allInputs['empleado']['idempleado']]['total_vacaciones']['computable'] = 0;
    	}

		if($allInputs['empresa']['regimen'] == 1){
			$total_dias = 15;
		}else if($allInputs['empresa']['regimen'] == 3){
			$total_dias = 30;
		}else{
			$total_dias = 30;
		}
		
		if($tipo_calculo != 'liquidacion'){			
			$meses = (int)$allInputs['empleado']['calculos_asistencia']['diasVacaciones'] * 12 / $total_dias;
			$computable = $arrVacaciones[$allInputs['empleado']['idempleado']]['total_vacaciones']['computable']/12*$meses;
			$no_computable = $arrVacaciones[$allInputs['empleado']['idempleado']]['total_vacaciones']['no_computable']/12*$meses;

			$arrVacaciones[$allInputs['empleado']['idempleado']]['total_computable'] = $computable;
			$arrVacaciones[$allInputs['empleado']['idempleado']]['total_no_computable'] = $no_computable;
		}


		return $arrVacaciones[$allInputs['empleado']['idempleado']];
	}

	public function calcular_vacaciones_empleado(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		
		$vacaciones = $this->calculo_vacaciones_empleado($allInputs);
		$arrData['datos'] = $vacaciones;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>