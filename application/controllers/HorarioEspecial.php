<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HorarioEspecial extends CI_Controller { 
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_horario_especial'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_horario_especial_empleado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_horario_especial->m_cargar_horario_especial($allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) { 
			//var_dump($row['hora_desde_entrada']); exit(); idmotivo
			$strFecha = $row['fecha_especial'];
			$strTiempoTolerancia = NULL;
			$strComentarioAsis = NULL;
			if( $row['si_licencia'] == 2 ){
				$strTiempoTolerancia = $row['tiempo_tolerancia'].' Min.';
				$strComentarioAsis = 'NO ASISTIRÁ AL TRABAJO';
			}
			if( $row['si_licencia'] == 1 ){
				$strTiempoTolerancia = $row['tiempo_tolerancia'].' Min.';
				$strComentarioAsis = 'SI ASISTIRÁ AL TRABAJO';
			}
			array_push($arrListado,
				array(
					'id' => $row['idhorarioespecial'],
					'idempleado' => $row['idempleado'],
					'idmotivohe'=> $row['idmotivohe'],
					'idsubmotivohe'=> $row['idsubmotivohe'],
					'fecha_especial_sf'=> date('d-m-Y',strtotime($strFecha)),
					'fecha_especial'=> formatoFechaReporte3($row['fecha_especial']),
					'fecha_especial_formato'=> formatoConDiaYano($row['fecha_especial']),
					'motivo' => strtoupper($row['descripcion_mh']).' - '.strtoupper($row['descripcion_smh']),
					'submotivo' => $row['descripcion_smh'],
					'desde_entrada' => darFormatoHora($row['hora_desde_entrada']),
					'entrada' => darFormatoHora($row['hora_entrada']),
					'hasta_entrada' => darFormatoHora($row['hora_hasta_entrada']),
					'desde_salida' => darFormatoHora($row['hora_desde_salida']),
					'salida' => darFormatoHora($row['hora_salida']),
					'hasta_salida' => darFormatoHora($row['hora_hasta_salida']),
					'tiempo_tolerancia' => $strTiempoTolerancia,
					'horas_trabajadas' => darFormatoHora2($row['horas_trabajadas']).' H',
					'si_licencia' => $row['si_licencia'],
					'comentario'=> $strComentarioAsis,
					'idmotivo'=> $row['idmotivohe'], // se utiliza para edicion
				)
			);
		}
	  	$arrData['datos'] = $arrListado;
	  	$arrData['message'] = '';
	  	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('horario/horarioEspecial_formView');
	}
	public function ver_popup_actualizar_marcacion()
	{
		$this->load->view('horario/verMarcacionesEsp_formView');
	}
	public function ver_popup_fecha_especial()
	{
		$this->load->view('horario/verFechaEspecial_formView');
	}
	public function registrar_horario_especial()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		$arrData['flag'] = 0; 
		// var_dump($allInputs['arrHorarios']); exit();
		$contador = 0;
		$arrSemanaGuardado = array();
		$arrSemana = array();
		foreach ($allInputs['arrHorarios'] as $key => $row) {
			if( empty($row['fecha_especial_sf']) ){
				if( $row['asistencia'] == 'SA' ){
					$arrDesdeEntradaHM = explode(':', $row['desde_entrada']); 
					if( empty($arrDesdeEntradaHM[0]) || count($arrDesdeEntradaHM[0]) > 2 || $arrDesdeEntradaHM[0] > 23 || $arrDesdeEntradaHM[1] > 59 ){ 
						$arrData['message'] = 'Error al obtener el campo: DESDE ENTRADA. Verifique e inténtelo nuevamente.';
						$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
						return;
					}
					$arrEntradaHM = explode(':', $row['entrada']); // var_dump($arrEntradaHM); exit();
					if( empty($arrEntradaHM[0]) || count($arrEntradaHM[0]) > 2 || $arrEntradaHM[0] > 23 || $arrEntradaHM[1] > 59 ){ 
						$arrData['message'] = 'Error al obtener el campo: ENTRADA. Verifique e inténtelo nuevamente.';
						$this->output
						    ->set_content_type('application/json')
						    ->set_output(json_encode($arrData));
						return;
					}
					$arrHastaEntradaHM = explode(':', $row['hasta_entrada']); 
					if( empty($arrHastaEntradaHM[0]) || count($arrHastaEntradaHM[0]) > 2 || $arrHastaEntradaHM[0] > 23 || $arrHastaEntradaHM[1] > 59 ){ 
						$arrData['message'] = 'Error al obtener el campo: HASTA ENTRADA. Verifique e inténtelo nuevamente.';
						$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
						return;
					}

					$arrDesdeSalidaHM = explode(':', $row['desde_salida']); 
					if( empty($arrDesdeSalidaHM[0]) || count($arrDesdeSalidaHM[0]) > 2 || $arrDesdeSalidaHM[0] > 23 || $arrDesdeSalidaHM[1] > 59 ){ 
						$arrData['message'] = 'Error al obtener el campo: DESDE SALIDA. Verifique e inténtelo nuevamente.';
						$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
						return;
					}
					$arrSalidaHM = explode(':', $row['salida']); 
					if( empty($arrSalidaHM[0]) || count($arrSalidaHM[0]) > 2 || $arrSalidaHM[0] > 23 || $arrSalidaHM[1] > 59 ){ 
						$arrData['message'] = 'Error al obtener el campo: SALIDA. Verifique e inténtelo nuevamente.';
						$this->output
						    ->set_content_type('application/json')
						    ->set_output(json_encode($arrData));
						return;
					}
					$arrHastaSalidaHM = explode(':', $row['hasta_salida']); 
					if( empty($arrHastaSalidaHM[0]) || count($arrHastaSalidaHM[0]) > 2 || $arrHastaSalidaHM[0] > 23 || $arrHastaSalidaHM[1] > 59 ){ 
						//var_dump($arrHastaSalidaHM[0]); exit();
						$arrData['message'] = 'Error al obtener el campo: HASTA SALIDA. Verifique e inténtelo nuevamente.';
						$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
						return;
					}
					if( empty($row['tiempo_tolerancia']) ){ 
						$arrData['message'] = 'Error al obtener el campo: TOLERANCIA. Verifique e inténtelo nuevamente.';
						$this->output
						    ->set_content_type('application/json')
						    ->set_output(json_encode($arrData));
						return;
					}
					
				}
			}else{

				// $allInputs['arrHorarios'][$key]['fecha_especial'] = $row['fecha_especial_sf'];
			}
		}
		foreach ($allInputs['arrHorarios'] as $row) {
			//  esto es para restringir a una tarde libre por semana
			if($row['idmotivo'] == '9'){
				if( empty($row['fecha_especial_sf']) ){ // nuevo
					array_push($arrSemana , array(
							'semana' => date('W',strtotime($row['fecha_especial'])) . date('Y',strtotime($row['fecha_especial'])),
						)
					);
				}else{ // horarios guardados
					array_push($arrSemanaGuardado , array(
							'semana' => date('W',strtotime($row['fecha_especial_sf'])) . date('Y',strtotime($row['fecha_especial_sf'])),
						)
					);
				}
				
			}
		}
		
		$contar = array();
		/* COMENTADO HASTA NUEVO AVISO */
		// $boolSemanaRepetida = FALSE;
		// $semana = '';
		// foreach ($arrSemana as $value) {
		// 	// Solo para turnos libres nuevos; con esto verifico si ya existe una semana XX en el array $contar
		// 	if(isset($contar[$value['semana']]))
		// 	{
		// 		// si ya existe, lanzamos la alerta
		// 		$contar[$value['semana']]+=1;
		// 		$boolSemanaRepetida = TRUE;
		// 		$semana = $value['semana'];
		// 	}else{
		// 		// si no existe lo añadimos al array
		// 		$contar[$value['semana']]=1;
		// 	}
		// 	// Para verificar una semana nueva contrastada con las semanas guardadas
		// 	foreach ($arrSemanaGuardado as $key => $valueOld) {
		// 		if( $value['semana'] == $valueOld['semana'] ){
		// 			$boolSemanaRepetida = TRUE;
		// 			$semana = $value['semana'];
		// 		}
		// 	}
		// }
		// if( $boolSemanaRepetida ){
		// 	$arrData['message'] = 'Ya se existe una TARDE LIBRE en la semana '. $semana;
		// 	$arrData['flag'] = 2;
		// 	$this->output
		// 	    ->set_content_type('application/json')
		// 	    ->set_output(json_encode($arrData));
		// 	return;
		// }

		//var_dump($arrSemanaGuardado);
		// var_dump($arrSemana); exit();
	  	$this->db->trans_start();
	  	foreach ($allInputs['arrHorarios'] as $row) { 
	  		$row['idempleado'] = $allInputs['idempleado']; 
	  		if( empty($row['fecha_especial_sf']) ){ 
		  		$fHorarioEspecialEmp = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($row['idempleado'],$row['fecha_especial']); 
		  		if( empty($fHorarioEspecialEmp) ){ 
					if($this->model_horario_especial->m_agregar_horario_empleado($row)){ 
						$arrData['message'] = 'Se registraron los datos correctamente';
			    		$arrData['flag'] = 1;
					}
		  		}else{
		  			$arrData['message'] = 'Se registraron los datos correctamente';
				    $arrData['flag'] = 1;
		  		}
	  		}else{
	  			$arrData['message'] = 'Se registraron los datos correctamente';
			    $arrData['flag'] = 1;
	  		}
	  	}
	  	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function eliminar_horario_especial_de_empleado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
	    $arrData['flag'] = 0;
	    if( @$allInputs['modo'] == 'inline' ){
				if($this->model_horario_especial->m_eliminar_horario_de_empleado($allInputs['id'])){ 
					$arrData['message'] = 'Se anularon los datos correctamente';
		    		$arrData['flag'] = 1;
				}
	    }else{
	    	foreach ($allInputs as $row) {
	    		if($this->model_horario_especial->m_eliminar_horario_de_empleado($row['id'])){ 
						$arrData['message'] = 'Se anularon los datos correctamente';
			    		$arrData['flag'] = 1;
					}
	    	}
	    }
			
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}