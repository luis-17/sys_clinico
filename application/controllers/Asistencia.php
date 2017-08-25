<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asistencia extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros'));
		$this->load->model(array('model_asistencia','model_empleado','model_horario_especial','model_horario_general','model_config'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_asistencias() 
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_asistencia->m_cargar_asistencias($allInputs['datos'],$allInputs['paginate']);
		$totalRows = $this->model_asistencia->m_count_asistencias($allInputs['datos'],$allInputs['paginate']);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strTipoAsistencia = '';
			if($row['tipo_asistencia'] == 'E' ){
				$strTipoAsistencia = 'ENTRADA';
			}elseif($row['tipo_asistencia'] == 'S' ){
				$strTipoAsistencia = 'SALIDA';
			}elseif($row['tipo_asistencia'] == 'B' ){
				$strTipoAsistencia = 'BREAK';
			}elseif($row['tipo_asistencia'] == 'V' ){
				$strTipoAsistencia = 'VISITA';
			}
			array_push($arrListado, 
				array(
					'id'=> $row['idasistencia'],
					'idempleado' => $row['idempleado'],
					'num_documento' => $row['numero_documento'],
					'personal'=> strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'fecha' => $row['fecha'],
					'hora' => darFormatoHora($row['hora']),
					'hora_sf' => $row['hora'],
					'diferencia' => $row['diferencia_tiempo'],
					'tipo' => $strTipoAsistencia,
					'estado' => array(
						'clase'=> $row['clase_css'],
						'string'=> $row['descripcion']
					),
					'descripcion' => $row['descripcion']
				)
			);
		} 
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_asistencias_de_empleado()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		// $paramPaginate = $allInputs['paginate'];
		// $paramDatos = $allInputs['datos'];
		//var_dump($allInputs['datos']); exit();
		$lista = $this->model_asistencia->m_cargar_asistencias_de_empleado($allInputs['datos'],$allInputs['paginate']);
		$totalRows = $this->model_asistencia->m_count_asistencias_de_empleado($allInputs['datos'],$allInputs['paginate']);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strTipoAsistencia = '';
			if($row['tipo_asistencia'] == 'E' ){
				$strTipoAsistencia = 'ENTRADA';
			}elseif($row['tipo_asistencia'] == 'S' ){
				$strTipoAsistencia = 'SALIDA';
			}elseif($row['tipo_asistencia'] == 'B' ){
				$strTipoAsistencia = 'BREAK';
			}elseif($row['tipo_asistencia'] == 'V' ){
				$strTipoAsistencia = 'VISITA';
			}
			array_push($arrListado, 
				array(
					'id'=> $row['idasistencia'],
					'idempleado' => $row['idempleado'],
					'idhorarioempleado' => $row['idhorarioempleado'],
					'num_documento' => $row['numero_documento'],
					'personal'=> strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'fecha' => $row['fecha'],
					'hora' => $row['hora'],
					'hora_sf' => !empty($row['hora_real'])? $row['hora_real']: $row['hora'],
					'diferencia' => $row['diferencia_tiempo'],
					'tipo' => $strTipoAsistencia,
					'estado' => array(
						'clase'=> $row['clase_css'],
						'string'=> $row['descripcion']
					),
					'descripcion' => $row['descripcion']
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
	public function actualizar_marcaciones()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrInputs = array();
		if( empty($allInputs['asistencias']) ){ 
			$arrData['message'] = 'Seleccione alguna marcación a actualizar.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		// var_dump($allInputs); exit();
		$this->db->trans_start();
		foreach ($allInputs['asistencias'] as $key => $row) { 
			$fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($row['idempleado'],$row['fecha']); 
			/* SI NO HAY FECHA ESPECIAL */ 
			if( empty($fHorarioEspecial) ){ 
				/* SI NO HAY HORARIO GENERAL ASIGNADO */ 
				$noimporta = TRUE;
				if( empty($row['idhorarioempleado']) || $noimporta ){ 
					$arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO');
					$fecha_marcacion = $row['fecha'];
					$diaSemana = date('w',strtotime($fecha_marcacion));  //var_dump($diaSemana); exit(); 
					///$diaSemana = date('w'); // var_dump($diaSemana); exit(); 
					$fHorarioGeneral = $this->model_horario_general->m_obtener_horario_general_de_empleado($row['idempleado'],$arrDiasSemana[$diaSemana]); 
					if( !empty($fHorarioGeneral) ){ 
						$arrInputs['tipo_asistencia'] = NULL; 
						$arrInputs['idestadoasistencia'] = NULL; 
						$arrInputs['idhorarioempleado'] = NULL; 
						$arrInputs['diferencia_tiempo'] = NULL;
						$arrInputs['hora_real'] = NULL; 
						$arrInputs['hora'] = $row['hora_sf'];
						$arrInputs['fecha'] = $row['fecha'];

						$arrInputs['hora_maestra_entrada'] = $fHorarioGeneral['hora_entrada'];
						$arrInputs['hora_maestra_salida'] = $fHorarioGeneral['hora_salida'];
						if( empty($fHorarioGeneral['tiempo_tolerancia']) ){
							$fHorarioGeneral['tiempo_tolerancia'] = 5;
						}
						$arrInputs['tiempo_tolerancia_maestra'] = $fHorarioGeneral['tiempo_tolerancia'];

						$fechaHoraActual = $row['fecha'].' '.$row['hora_sf']; 
						$DTFechaHoraActual = date_create($fechaHoraActual);
						$DThoraComparante = 0;
						$arrInputs['idhorarioempleado'] = $fHorarioGeneral['idhorarioempleado'];
						$arrInputs['id'] = $row['id'];
						/*SI ES ENTRADA*/
						if( $fHorarioGeneral['hora_desde_entrada'] <= $row['hora_sf'] && $fHorarioGeneral['hora_hasta_entrada'] >= $row['hora_sf'] ){ 
							//var_dump($fHorarioGeneral['hora_desde_entrada'],$row['hora_sf']); exit();
							$arrInputs['tipo_asistencia'] = 'E';
							$DThoraComparante = date_create($row['fecha'].' '.$fHorarioGeneral['hora_entrada']);
						} 
						/*SI ES BREAK*/
						if( $fHorarioGeneral['hora_hasta_entrada'] < $row['hora_sf'] && $fHorarioGeneral['hora_desde_salida'] > $row['hora_sf'] ){ 
							$arrInputs['tipo_asistencia'] = 'B';
						}
						/*SI ES SALIDA*/
						if( $fHorarioGeneral['hora_desde_salida'] <= $row['hora_sf'] && $fHorarioGeneral['hora_hasta_salida'] >= $row['hora_sf'] ){
							$arrInputs['tipo_asistencia'] = 'S';
							$DThoraComparante = date_create($row['fecha'].' '.$fHorarioGeneral['hora_salida']);
						}
						/*SI ES VISITA*/
						if( empty($arrInputs['tipo_asistencia']) ){ 
							$arrInputs['tipo_asistencia'] = 'V';
						}

						/* RESETEAR LA HORA DE SALIDA DE LAS PERSONAS, PASADO LOS 5 MINUTOS DESDE SU HORA DE SALIDA, SE RESETEA A SU HORA DE SALIDA */
						if( $arrInputs['tipo_asistencia'] == 'S' ){ // si es salida 
							$Hsalida = date_create($fHorarioGeneral['hora_salida']);
							date_add($Hsalida, date_interval_create_from_date_string('5 minutes'));
							$Hsalida = $Hsalida->format('H:i:s');
							// var_dump($arrInputs['hora'] > $Hsalida); exit();
							if( $arrInputs['hora'] > $Hsalida ){ // si salida es mayor de la Hora Maestra
								// $arrInputs['hora_real'] = $arrInputs['hora']; 
								$arrInputs['hora'] = $fHorarioGeneral['hora_salida'];
								// ponemos la nueva hora para comparar y sacar la diferencia
								$fechaHoraActual = $arrInputs['fecha'].' '.$arrInputs['hora']; 
								$DTFechaHoraActual = date_create($fechaHoraActual);
							}
						}

						if( $arrInputs['tipo_asistencia'] == 'S' || $arrInputs['tipo_asistencia'] == 'E'){ 
							if( $arrInputs['tipo_asistencia'] == 'E' ){ /* Aplicamos tolerancia */
								$tiempoTolerancia = 5+1;
								if( !empty($fHorarioGeneral['tiempo_tolerancia']) ){
									$tiempoTolerancia = ($fHorarioGeneral['tiempo_tolerancia'] + 1);
								}
								date_add($DThoraComparante, date_interval_create_from_date_string($tiempoTolerancia.' minutes'));
								$intervalFecha = date_diff($DTFechaHoraActual,$DThoraComparante);
							}else{
								$intervalFecha = date_diff($DThoraComparante,$DTFechaHoraActual);
							}
							//var_dump("<pre>",$DTFechaHoraActual,$DThoraComparante); 
							
							$minutosDiferencia = $intervalFecha->format('%R%H:%I:%S%');
							//var_dump("<pre>",$minutosDiferencia); exit(); 
							$arrInputs['diferencia_tiempo'] = $minutosDiferencia;
							if( $arrInputs['tipo_asistencia'] == 'E' ){ // CALCULAR ESTADO ASISTENCIA 
								if( $intervalFecha->format('%R%') == '+' ){ 
									$arrInputs['idestadoasistencia'] = 1; // P 
								}elseif ( $intervalFecha->format('%R%') == '-' ){ 
									$arrInputs['idestadoasistencia'] = 2; // TI 
								}
							}
						}
						$this->model_asistencia->m_actualizar_asistencias_con_horario($arrInputs); 
					}
				} 
			} 
			$arrData['message'] = 'Se actualizaron los datos correctamente.';
			$arrData['flag'] = 1;
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function actualizar_marcaciones_especiales()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrInputs = array();
		if( empty($allInputs['asistencias']) ){ 
			$arrData['message'] = 'Seleccione alguna marcación a actualizar.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		//var_dump("<pre>",$allInputs['asistencias']); // exit();
		$this->db->trans_start();
		foreach ($allInputs['asistencias'] as $key => $row) { 
			$fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($row['idempleado'],$row['fecha']); 
			// var_dump($fHorarioEspecial); exit();
			/* SI HAY FECHA ESPECIAL */ 
			if( !empty($fHorarioEspecial) ){ 
				/* SI NO HAY HORARIO GENERAL ASIGNADO */ 
				if( empty($row['idhorarioespecial']) ){ 
					$arrInputs['tipo_asistencia'] = NULL; 
					$arrInputs['idestadoasistencia'] = NULL; 
					$arrInputs['idhorarioespecial'] = NULL; 
					$arrInputs['diferencia_tiempo'] = NULL;

					$arrInputs['hora_maestra_entrada'] = $fHorarioEspecial['hora_entrada'];
					$arrInputs['hora_maestra_salida'] = $fHorarioEspecial['hora_salida'];
					if( empty($fHorarioEspecial['tiempo_tolerancia']) ){
						$fHorarioEspecial['tiempo_tolerancia'] = 5;
					}
					$arrInputs['tiempo_tolerancia_maestra'] = $fHorarioEspecial['tiempo_tolerancia'];
				
					$fechaHoraActual = $row['fecha'].' '.$row['hora_sf']; 
					$DTFechaHoraActual = date_create($fechaHoraActual);
					$DThoraComparante = 0;
					$arrInputs['idhorarioespecial'] = $fHorarioEspecial['idhorarioespecial'];
					$arrInputs['id'] = $row['id'];
					/*SI ES ENTRADA*/
					if( $fHorarioEspecial['hora_desde_entrada'] <= $row['hora_sf'] && $fHorarioEspecial['hora_hasta_entrada'] >= $row['hora_sf'] ){ 
						$arrInputs['tipo_asistencia'] = 'E';
						$DThoraComparante = date_create($fHorarioEspecial['fecha_especial'].' '.$fHorarioEspecial['hora_entrada']);
					} 
					/*SI ES BREAK*/
					if( $fHorarioEspecial['hora_hasta_entrada'] < $row['hora_sf'] && $fHorarioEspecial['hora_desde_salida'] > $row['hora_sf'] ){ 
						$arrInputs['tipo_asistencia'] = 'B';
					}
					/*SI ES SALIDA*/
					if( $fHorarioEspecial['hora_desde_salida'] <= $row['hora_sf'] && $fHorarioEspecial['hora_hasta_salida'] >= $row['hora_sf'] ){
						$arrInputs['tipo_asistencia'] = 'S';
						$DThoraComparante = date_create($fHorarioEspecial['fecha_especial'].' '.$fHorarioEspecial['hora_salida']);
					}
					/*SI ES VISITA*/
					if( empty($arrInputs['tipo_asistencia']) ){ 
						$arrInputs['tipo_asistencia'] = 'V';
					}
					if( $arrInputs['tipo_asistencia'] == 'S' || $arrInputs['tipo_asistencia'] == 'E'){ 
						if( $arrInputs['tipo_asistencia'] == 'E' ){ /* Aplicamos tolerancia */
							$tiempoTolerancia = 5+1;
							if( !empty($fHorarioEspecial['tiempo_tolerancia']) ){
								$tiempoTolerancia = ($fHorarioEspecial['tiempo_tolerancia'] + 1);
							}
							date_add($DThoraComparante, date_interval_create_from_date_string($tiempoTolerancia.' minutes'));
						}
						// var_dump($DTFechaHoraActual,$DThoraComparante); exit();
						$intervalFecha = date_diff($DTFechaHoraActual,$DThoraComparante);
						$minutosDiferencia = $intervalFecha->format('%R%H:%I:%S%');
						$arrInputs['diferencia_tiempo'] = $minutosDiferencia;
						if( $arrInputs['tipo_asistencia'] == 'E' ){ // CALCULAR ESTADO ASISTENCIA 
							if( $intervalFecha->format('%R%') == '+' ){ 
								$arrInputs['idestadoasistencia'] = 1; // P 
							}elseif ( $intervalFecha->format('%R%') == '-' ){ 
								$arrInputs['idestadoasistencia'] = 2; // TI 
							}
						}
					}
					$this->model_asistencia->m_actualizar_asistencias_especial_con_horario($arrInputs); 
				} 
			} 
			$arrData['message'] = 'Se actualizaron los datos correctamente.';
			$arrData['flag'] = 1;
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit(); 
		// CONSULTA PARA DETERMINAR TIPO DE MARCACION
		if( !empty($allInputs['modulo']) && ($this->sessionHospital['key_group'] != 'key_sistemas') ){
			$arrConfig = array();
	    	
	    	$listaConf = $this->model_config->m_listar_configuraciones();
	    	foreach ($listaConf as $key => $rowConfig) {
	    		$arrConfig[$rowConfig['key_cf']] = $rowConfig['valor_cf'];
	    	}
	    	// var_dump($listaConf); var_dump($arrConfig); exit();
	    	if( $allInputs['modulo'] == 'marcaAsistencia' && $arrConfig['marca_manual'] == 'NO'){
	    		$arrData['message'] = 'NO SE PERMITE MARCACION MANUAL. CONTACTE CON SISTEMAS';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;	
	    	}
		}
    	

		if(!solonumeros($allInputs['hora'])){
			$arrData['message'] = 'LA HORA DEBE SER NUMERICA.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		if(!solonumeros($allInputs['minuto'])){
			$arrData['message'] = 'LOS MINUTOS DEBEN SER SOLO NUMEROS.';
    		$arrData['flag'] = 0;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		$arrData['flag'] = 0;
		$arrInputs = array();
		$arrInputs['idempleado'] = NULL;
		$arrInputs['tipo_asistencia'] = NULL; 
		$arrInputs['idestadoasistencia'] = NULL; 
		$arrInputs['idhorarioempleado'] = NULL; 
		$arrInputs['idhorarioespecial'] = NULL; 
		$arrInputs['observaciones'] = NULL; 
		$arrInputs['diferencia_tiempo'] = NULL;
		$arrInputs['hora_maestra_entrada'] = NULL;
		$arrInputs['hora_maestra_salida'] = NULL;
		$arrInputs['tiempo_tolerancia_maestra'] = NULL;
		$arrInputs['hora_real'] = NULL;
		$arrInputs['iduser'] = $this->sessionHospital['idusers']; // usuario de session para este tipo de marcacion desde el aplicativo si es necesario registrarlo
		if( $this->sessionHospital['key_group'] == 'key_sistemas' ){
			$arrInputs['fecha'] = darFormatoYMD($allInputs['fecha']); 
			$arrInputs['hora'] = $allInputs['hora'] . ':' . $allInputs['minuto'] . ':00'; 
		}else{
			$arrInputs['fecha'] = date('Y-m-d'); 
			$arrInputs['hora'] = date('H:i:s');
		}
		//var_dump($arrInputs); exit();
		$strMensaje = '';
		$fEmpleado = $this->model_empleado->m_cargar_este_empleado_por_codigo_asistencia($allInputs['codigo']); 
		if( empty($fEmpleado) ){ 
			$arrInputs['observaciones'] = 'EL EMPLEADO CON EL CODIGO: ['.$allInputs['codigo'].'] AUN NO EXISTE.'; 
			$strMensaje .= '- El empleado con el código ['.$allInputs['codigo'].'] aún no existe.<br/>';
		}
		if( !empty($fEmpleado) ){ 
			$arrInputs['idempleado'] = $fEmpleado['idempleado'];
			$strMensaje .= '<div style="font-weight: bold; text-decoration: underline;">'.strtoupper($fEmpleado['empleado']).'</div>';
		}
		
		$strMensaje .= '- Se guardó la marcación correctamente.';
		/* HALLAMOS AL EMPLEADO */
		
		$fechaHoraActual = $arrInputs['fecha'].' '.$arrInputs['hora']; 
		$DTFechaHoraActual = date_create($fechaHoraActual);
		
		$arrInputs['marcado_asistencia'] = 2; // automatico 
		if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			$arrInputs['marcado_asistencia'] = 3; // manual 
		} 
		$DThoraComparante = 0;
		/* HALLAMOS AL ESTADO DE ASISTENCIA */ 
		$tieneHorarioEspecial = TRUE;
		$fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($arrInputs['idempleado'],$arrInputs['fecha']); 
		if( empty($fHorarioEspecial) ){ 
			$tieneHorarioEspecial = FALSE; 
		}
		if( $tieneHorarioEspecial === FALSE ){ 
			$arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'); 
			$diaSemana = date('w'); // var_dump($diaSemana); exit(); 
			$fHorarioGeneral = $this->model_horario_general->m_obtener_horario_general_de_empleado($arrInputs['idempleado'],$arrDiasSemana[$diaSemana]);
			if( !empty($fHorarioGeneral) ){ 
				$arrInputs['idhorarioempleado'] = $fHorarioGeneral['idhorarioempleado'];
				/*SI ES ENTRADA*/
				if( $fHorarioGeneral['hora_desde_entrada'] <= $arrInputs['hora'] && $fHorarioGeneral['hora_hasta_entrada'] >= $arrInputs['hora'] ){ 
					$arrInputs['tipo_asistencia'] = 'E';
					$DThoraComparante = date_create($arrInputs['fecha'].' '.$fHorarioGeneral['hora_entrada']);

				} 
				/*SI ES BREAK*/
				if( $fHorarioGeneral['hora_hasta_entrada'] < $arrInputs['hora'] && $fHorarioGeneral['hora_desde_salida'] > $arrInputs['hora'] ){ 
					$arrInputs['tipo_asistencia'] = 'B';
				}
				/*SI ES SALIDA*/
				if( $fHorarioGeneral['hora_desde_salida'] <= $arrInputs['hora'] && $fHorarioGeneral['hora_hasta_salida'] >= $arrInputs['hora'] ){
					$arrInputs['tipo_asistencia'] = 'S';
					$DThoraComparante = date_create($arrInputs['fecha'].' '.$fHorarioGeneral['hora_salida']);
					$strMensaje .= '<br />- ¡HASTA PRONTO! :) ';
				}
				/*SI ES VISITA*/
				if( empty($arrInputs['tipo_asistencia']) ){ 
					$arrInputs['tipo_asistencia'] = 'V';
					$arrInputs['observaciones'] .= '<br/> - EL EMPLEADO CON EL CODIGO: ['.$allInputs['codigo'].'] VINO DE VISITA. ';
				}
				/* RESETEAR A 7:00pm, la hora de salida de las personas que marcan su salida, pasado las 7:00pm */ 
				if( $arrInputs['tipo_asistencia'] == 'S' ){ // si es salida 
					// if( $arrInputs['hora'] > '19:00:00' ){ // si salida es mayor de las 7:00pm 
					// 	$arrInputs['hora_real'] = $arrInputs['hora']; 
					// 	$arrInputs['hora'] = '19:00:00'; 
					// }
					$Hsalida = date_create($fHorarioGeneral['hora_salida']);
					date_add($Hsalida, date_interval_create_from_date_string('5 minutes'));
					$Hsalida = $Hsalida->format('H:i:s');
					// var_dump($arrInputs['hora'] > $Hsalida); exit();
					if( $arrInputs['hora'] > $Hsalida ){ // si salida es mayor de la Hora Maestra
						$arrInputs['hora_real'] = $arrInputs['hora']; 
						$arrInputs['hora'] = $fHorarioGeneral['hora_salida'];
						// ponemos la nueva hora para comparar y sacar la diferencia
						$fechaHoraActual = $arrInputs['fecha'].' '.$arrInputs['hora']; 
						$DTFechaHoraActual = date_create($fechaHoraActual);
					}
				}

				$arrInputs['hora_maestra_entrada'] = $fHorarioGeneral['hora_entrada'];
				$arrInputs['hora_maestra_salida'] = $fHorarioGeneral['hora_salida'];
				if( empty($fHorarioGeneral['tiempo_tolerancia']) ){
					$fHorarioGeneral['tiempo_tolerancia'] = 5;
				}
				$arrInputs['tiempo_tolerancia_maestra'] = $fHorarioGeneral['tiempo_tolerancia'];
				if( $arrInputs['tipo_asistencia'] == 'S' || $arrInputs['tipo_asistencia'] == 'E'){ 
					if( $arrInputs['tipo_asistencia'] == 'E' ){ /* Aplicamos tolerancia */
						$tiempoTolerancia = 5+1;
						if( !empty($fHorarioGeneral['tiempo_tolerancia']) ){
							$tiempoTolerancia = ($fHorarioGeneral['tiempo_tolerancia'] + 1);
						}
						date_add($DThoraComparante, date_interval_create_from_date_string($tiempoTolerancia.' minutes'));
					}
					$intervalFecha = date_diff($DTFechaHoraActual,$DThoraComparante);
					$minutosDiferencia = $intervalFecha->format('%R%H:%I:%S%');
					$arrInputs['diferencia_tiempo'] = $minutosDiferencia;
					if( $arrInputs['tipo_asistencia'] == 'E' ){ // CALCULAR ESTADO ASISTENCIA 
						if( $intervalFecha->format('%R%') == '+' ){ 
							$arrInputs['idestadoasistencia'] = 1; // P 
							$strMensaje .= '<br />- ¡LLEGASTE PUNTUAL! :) <br /> <img class="img-responsive center-block" src="'.base_url('assets/img/asistencia/ok.png').'" />';
						}elseif ( $intervalFecha->format('%R%') == '-' ){
							$arrInputs['idestadoasistencia'] = 2; // TI
							$strMensaje .= '<br />- ¡LLEGASTE ALGO TARDE! ['.$fechaHoraActual.'] :( <br /> <img class="img-responsive center-block" src="'.base_url('assets/img/asistencia/no.png').'" />';
						}
					}
				}
			}else{
				$arrInputs['observaciones'] .= '<br/> - EL EMPLEADO CON EL CODIGO: ['.$allInputs['codigo'].'] NO TIENE HORARIO GENERAL DEFINIDO PARA ESTE DIA. ';
				$strMensaje .= '<br />- Aún no tienes horario definido para este día.';
			}
		}else{
			$arrInputs['idhorarioespecial'] = $fHorarioEspecial['idhorarioespecial'];
			/*SI ES ENTRADA*/
			if( $fHorarioEspecial['hora_desde_entrada'] <= $arrInputs['hora'] && $fHorarioEspecial['hora_hasta_entrada'] >= $arrInputs['hora'] ){ 
				$arrInputs['tipo_asistencia'] = 'E';
				$DThoraComparante = date_create($arrInputs['fecha'].' '.$fHorarioEspecial['hora_entrada']);
			} 
			/*SI ES BREAK*/
			if( $fHorarioEspecial['hora_hasta_entrada'] < $arrInputs['hora'] && $fHorarioEspecial['hora_desde_salida'] > $arrInputs['hora'] ){ 
				$arrInputs['tipo_asistencia'] = 'B';
			}
			/*SI ES SALIDA*/
			if( $fHorarioEspecial['hora_desde_salida'] <= $arrInputs['hora'] && $fHorarioEspecial['hora_hasta_salida'] >= $arrInputs['hora'] ){
				$arrInputs['tipo_asistencia'] = 'S';
				$DThoraComparante = date_create($arrInputs['fecha'].' '.$fHorarioEspecial['hora_salida']);
				$strMensaje .= '<br />- ¡HASTA PRONTO! :) ';
			}
			/*SI ES VISITA*/
			if( empty($arrInputs['tipo_asistencia']) ){ 
				$arrInputs['tipo_asistencia'] = 'V';
				$arrInputs['observaciones'] .= '<br/> - EL EMPLEADO CON EL CODIGO: ['.$allInputs['codigo'].'] VINO DE VISITA. ';
			}
			/* RESETEAR A 7:00pm, la hora de salida de las personas que marcan su salida, pasado las 7:00pm */ 
			if( $arrInputs['tipo_asistencia'] == 'S' ){ // si es salida 
				if( $arrInputs['hora'] > '19:00:00' ){ // si salida es mayor de las 7:00pm 
					$arrInputs['hora_real'] = $arrInputs['hora']; 
					$arrInputs['hora'] = '19:00:00'; 
				}
			}
			$arrInputs['hora_maestra_entrada'] = $fHorarioEspecial['hora_entrada'];
			$arrInputs['hora_maestra_salida'] = $fHorarioEspecial['hora_salida'];
			if( empty($fHorarioEspecial['tiempo_tolerancia']) ){
				$fHorarioEspecial['tiempo_tolerancia'] = 5;
			}
			$arrInputs['tiempo_tolerancia_maestra'] = $fHorarioEspecial['tiempo_tolerancia'];

			if( $arrInputs['tipo_asistencia'] == 'S' || $arrInputs['tipo_asistencia'] == 'E'){ 
				if( $arrInputs['tipo_asistencia'] == 'E' ){ /* Aplicamos tolerancia */
					$tiempoTolerancia = 5+1;
					if( !empty($fHorarioEspecial['tiempo_tolerancia']) ){
						$tiempoTolerancia = ($fHorarioEspecial['tiempo_tolerancia'] + 1);
					}
					date_add($DThoraComparante, date_interval_create_from_date_string($tiempoTolerancia.' minutes'));
				}
				$intervalFecha = date_diff($DTFechaHoraActual,$DThoraComparante);
				$minutosDiferencia = $intervalFecha->format('%R%H:%I:%S%');
				$arrInputs['diferencia_tiempo'] = $minutosDiferencia; 
				if( $arrInputs['tipo_asistencia'] == 'E' ){ // CALCULAR ESTADO ASISTENCIA  
					if( $intervalFecha->format('%R%') == '+' ){ 
						$arrInputs['idestadoasistencia'] = 1; // P 
						$strMensaje .= '<br />- ¡LLEGASTE PUNTUAL! :) <br /> <img class="img-responsive center-block" src="'.base_url('assets/img/asistencia/ok.png').'" />';
					}elseif ( $intervalFecha->format('%R%') == '-' ){
						$arrInputs['idestadoasistencia'] = 2; // TI
						$strMensaje .= '<br />- ¡LLEGASTE ALGO TARDE! ['.$fechaHoraActual.'] :( <br /> <img class="img-responsive center-block" src="'.base_url('assets/img/asistencia/no.png').'" />';
					}
				}
			}
		}
		$arrInputs['codigo_asistencia'] = $allInputs['codigo'];
		// var_dump($allInputs); exit();
		$this->db->trans_start();
		if($this->model_asistencia->m_registrar_asistencia_empleado($arrInputs)){ 
			$arrData['message'] = $strMensaje;
    		$arrData['flag'] = 1;
		}else{
			$arrData['message'] = 'CONTACTE CON EL AREA DE SISTEMAS. NO SE PUDO REGISTRAR SU MARCACIÓN.';
    		$arrData['flag'] = 0;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>