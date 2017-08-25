<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class EnvioCorreoCronJob extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_empleado','model_config','model_asistencia','model_horario_especial',
									'model_horario_general','model_atencion_medica','model_usuario', 'model_prog_medico'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function envio_correo_cumpleanero()
	{
		$listaCumpleaneros = $this->model_empleado->m_cargar_empleados_cumpleaneros_dia(); 
		$arrConfig = $this->model_config->m_cargar_empresa_activa();
		if( !empty($listaCumpleaneros) ){ 
			$this->load->library('My_PHPMailer');
			$hoydate = date("Y-m-d H:i:s");
			date_default_timezone_set('UTC');
			define('SMTP_HOST','mail.villasalud.pe');

			define('SMTP_PORT',25);
			define('SMTP_USERNAME','alertas@villasalud.pe');
			define('SMTP_PASSWORD','gestores01');
			$mail = new PHPMailer();
			$mail->IsSMTP(true);
			//$mail->SMTPDebug = 2;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "tls";
			$mail->Host = SMTP_HOST;
			$mail->Port = SMTP_PORT;
			$mail->Username =  SMTP_USERNAME;
			$mail->Password = SMTP_PASSWORD;
			$mail->SetFrom('alertas@villasalud.pe','Alertas Villa Salud');
			$mail->AddReplyTo('alertas@villasalud.pe',"Alertas Villa Salud");
			$mail->Subject = 'RECORDATORIO DE ONOMÁSTICOS DE COLABORADORES - VILLA SALUD';

			$cuerpo = '
				<html>
				<head>
				  <title>Recordatorio de Onomástico de Colaboradores</title>
				</head>
				<body style="font-family: sans-serif;background-color: #f5f5f5;padding: 40px;" >
					<div style="text-align: center;">
						<img style="width: 14%;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/'.$arrConfig['nombre_logo']).'">
					</div>
					<h2 style="text-align: center; text-transform: uppercase;">¡Estos son los Colaboradores que cumplen años hoy: 
						<small style="text-align: center; display: block; font-size: 24px; color: #a41d23;"> '.darFechaCumple(date('Y-m-d')).' </small> </h2>
					<div style="text-align:center;"> <img style="width: 16%;" alt="Feliz Cumpleaños" src="'.base_url('assets/img/hb_trans.png').'" />  </div>
					<table cellpadding="10" style="margin:auto;width: 100%;border-color: #666; color:#42525a;">
						<thead>
					    <tr style="background: #eee;"">
					      <th>COLABORADOR</th> <th>CARGO</th> <th>EMPRESA</th> <th>FOTO</th>
					    </tr>
					    </thead><tbody style="text-align: center;">';
			foreach ($listaCumpleaneros as $key => $row) { 
				$cuerpo .= '<tr>';
				$cuerpo .= '<td>'.strtoupper($row['empleado']).'</td>';
				$cuerpo .= '<td>'.strtoupper($row['cargo']).'</td>';
				$cuerpo .= '<td>'.strtoupper($row['empresa']).'</td>';
				$cuerpo .= '<td> <img style="height: 50px;" alt="'.$row['empleado'].'" src="'.base_url('assets/img/dinamic/empleado/'.$row['nombre_foto']).'" /></td>';
				$cuerpo .= '</tr>';
			}
			$cuerpo .='</tbody> </table>';
			$cuerpo .= '<div style="width: 100%; display: block; font-size: 14px; text-align: right; line-height: 5; color: #a9b9c1;"> Atte: Area de Sistemas y Desarrollo </div>';
			$cuerpo .= '</body></html>';
			$mail->AltBody = $cuerpo;
			$mail->MsgHTML($cuerpo);
			$mail->AddAddress('jcabrera@villasalud.pe', 'Juan Carlos Cabrera');
			$mail->AddAddress('fcabrera@villasalud.pe', 'Franzheskoli Cabrera'); 
			$mail->AddAddress('sgavidia@villasalud.pe', 'Sunny Gavidia');
			$mail->AddAddress('eramirez@villasalud.pe', 'Elizabeth Ramirez');
			$mail->AddAddress('administracion@villasalud.pe', 'Ruby Quijano');
			// $mail->AddAddress('kalanya@villasalud.pe', 'Karina Alanya');
			$mail->AddAddress('mcastillo@villasalud.pe', 'Marino Castillo');
			$mail->AddAddress('ovega@villasalud.pe', 'Oscar Vega');
			$mail->AddAddress('fnique@villasalud.pe', 'Fresia Ñique');
			$mail->AddAddress('chenriquez@villasalud.pe', 'Vladimir Henriquez');
			$mail->AddAddress('rluna@villasalud.pe', 'Luis Ricardo Luna Soto');
			// Activo condificacción utf-8
			$mail->CharSet = 'UTF-8';
			// echo $cuerpo; 
			$mail->Send(); // ESPERAR A PONERLO PRODUCCION

			//echo json_encode($listaCumpleaneros);
		}
	}
	public function bloquear_tickets_atencion()
	{ 
		$arrData['datos'] = array();
		$arrData['message'] = 'No se actualizaron los datos.';
    	$arrData['flag'] = 0;
		if( $this->model_atencion_medica->m_bloquear_tickets_atencion() ){ 
			$arrData['message'] = 'Datos actualizados correctamente.';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json') 
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar_usuarios_no_session_semanas()
	{ 
		// DESHABILITAR LOS USUARIOS QUE NO HAN INICIADO SESIÓN DURANTE LAS ULTIMAS 2 SEMANAS 
		$arrData['datos'] = array();
		$arrData['message'] = 'No se deshabilitaron los datos.';
    	$arrData['flag'] = 0;
    	$lista = $this->model_usuario->m_cargar_usuarios_all(); 
    	foreach ($lista as $key => $row) { 
     		$ultimoInicioSesion = $row['ultimo_inicio_sesion'];
     		if( !(strtotime("$ultimoInicioSesion+14days") >= strtotime(date('Y-m-d')) ) ){ 
     			$this->model_usuario->m_deshabilitar($row['idusers']); 
     			$arrData['message'] = 'Datos actualizados correctamente.'; 
    			$arrData['flag'] = 1;
     		}
    	} 
		$this->output
		    ->set_content_type('application/json') 
		    ->set_output(json_encode($arrData)); 
	}
	public function marcar_asistencia_desde_huellero_master()
	{
		/* LÓGICA PARA HUELLA */
		/*
		  'fecha' => string '29-09-2016' (length=10)
		  'hora' => string '09' (length=2)
		  'minuto' => string '40' (length=2)
		  'codigo' => string '47866486' (length=8)
		*/
		// var_dump(); exit(); 
		$lista = $this->model_asistencia->m_listar_asistencia_temporal_sin_marcar();
		foreach ($lista as $key => $row) {
			if( !empty($row['numero_documento']) ){ 
				$this->marcar_asistencia_desde_huellero($row['numero_documento'],$row['fecha_hora']); 
				$this->model_asistencia->m_actualizar_asistencia_temporal_a_marcado($row['idasistenciatemporal']);
			}
		}
	}
	private function marcar_asistencia_desde_huellero($dni,$fechaHora)
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
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
		$arrInputs['iduser'] = NULL; // usuario de session para este tipo de marcacion por huellero no es necesario registrarlo, siempre será NULL
		
		$arrInputs['fecha'] = date('Y-m-d',strtotime("$fechaHora")); 
		$arrInputs['hora'] = date('H:i:s',strtotime("$fechaHora"));
		$allInputs['codigo'] = $dni;

		// var_dump($arrInputs); exit();
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
		
		$arrInputs['marcado_asistencia'] = 1; // Marcado por huellero 
		// if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
		// 	$arrInputs['marcado_asistencia'] = 2; // manual 
		// } 
		$DThoraComparante = 0;
		/* HALLAMOS AL ESTADO DE ASISTENCIA */ 
		$tieneHorarioEspecial = TRUE;
		$fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($arrInputs['idempleado'],$arrInputs['fecha']); 
		if( empty($fHorarioEspecial) ){ 
			$tieneHorarioEspecial = FALSE; 
		}
		if( $tieneHorarioEspecial === FALSE ){ 
			$arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO');
			// tomar el dia de la semana de la marcacion
			$fecha_marcacion = $arrInputs['fecha'];
			$diaSemana = date('w',strtotime($fecha_marcacion));  //var_dump($diaSemana); exit(); 
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
				/* RESETEAR LA HORA DE SALIDA DE LAS PERSONAS, PASADO LOS 5 MINUTOS DESDE SU HORA DE SALIDA, SE RESETEA A SU HORA DE SALIDA */
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
		$this->db->trans_start();
		if($this->model_asistencia->m_registrar_asistencia_empleado($arrInputs)){ 
			$arrData['message'] = $strMensaje;
    		$arrData['flag'] = 1;
		}else{
			$arrData['message'] = 'CONTACTE CON EL ÁREA DE SISTEMAS. NO SE PUDO REGISTRAR SU MARCACIÓN.';
    		$arrData['flag'] = 0;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function desbloquear_cupos_prog(){ 
		$arrData['datos'] = array();
		$arrData['message'] = 'No se actualizaron los datos.';
    	$arrData['flag'] = 0;
		if( $this->model_prog_medico->m_update_cupos() ){ 
			$arrData['message'] = 'Datos actualizados correctamente.';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json') 
		    ->set_output(json_encode($arrData));
	}
}
?>