<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HorarioGeneral extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_horario_general'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_horario_general_empleado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_horario_general->m_cargar_horario_general($allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idhorarioempleado'],
					'idempleado' => $row['idempleado'],
					'idhorario'=> $row['idhorario'],
					'horario' => $row['descripcion'],
					'entrada' => array( 
						'desde_entrada' => darFormatoHora($row['hora_desde_entrada']),
						'entrada' => darFormatoHora($row['hora_entrada']),
						'hasta_entrada' => darFormatoHora($row['hora_hasta_entrada'])
					),
					'salida' => array( 
						'desde_salida' => darFormatoHora($row['hora_desde_salida']),
						'salida' => darFormatoHora($row['hora_salida']),
						'hasta_salida' => darFormatoHora($row['hora_hasta_salida'])
					),
					'tiempo_tolerancia' => $row['tiempo_tolerancia'].' Min.',
					'horas_trabajadas' => darFormatoHora2($row['horas_trabajadas']).' H'
				)
			);
		}
  	$arrData['datos'] = $arrListado;
  	//$arrData['paginate']['totalRows'] = $totalRows;
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
		$this->load->view('horario/horarioGeneral_formView');
	}
	public function ver_popup_actualizar_marcacion()
	{
		$this->load->view('horario/verMarcaciones_formView');
	}
	public function registrar_horario_generado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		$arrData['flag'] = 0; 
		// var_dump("<pre>",$allInputs['arrHorarios']); exit(); 
		foreach ($allInputs['arrHorarios'] as $row) { 
			$arrDesdeEntradaHM = explode(':', $row['entrada']['desde_entrada']); 
			if( empty($arrDesdeEntradaHM[0]) || count($arrDesdeEntradaHM[0]) > 2 || $arrDesdeEntradaHM[0] > 23 || $arrDesdeEntradaHM[1] > 59 ){ 
				$arrData['message'] = 'Error al obtener el campo: DESDE ENTRADA. Verifique e inténtelo nuevamente.';
				$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
				return;
			}
			$arrEntradaHM = explode(':', $row['entrada']['entrada']); 
			if( empty($arrEntradaHM[0]) || count($arrEntradaHM[0]) > 2 || $arrEntradaHM[0] > 23 || $arrEntradaHM[1] > 59 ){ 
				$arrData['message'] = 'Error al obtener el campo: ENTRADA. Verifique e inténtelo nuevamente.';
				$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
				return;
			}

			$arrHastaEntradaHM = explode(':', $row['entrada']['hasta_entrada']); 
			if( empty($arrHastaEntradaHM[0]) || count($arrHastaEntradaHM[0]) > 2 || $arrHastaEntradaHM[0] > 23 || $arrHastaEntradaHM[1] > 59 ){ 
				$arrData['message'] = 'Error al obtener el campo: HASTA ENTRADA. Verifique e inténtelo nuevamente.';
				$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
				return;
			}

			$arrDesdeSalidaHM = explode(':', $row['salida']['desde_salida']); 
			if( empty($arrDesdeSalidaHM[0]) || count($arrDesdeSalidaHM[0]) > 2 || $arrDesdeSalidaHM[0] > 23 || $arrDesdeSalidaHM[1] > 59 ){ 
				$arrData['message'] = 'Error al obtener el campo: DESDE SALIDA. Verifique e inténtelo nuevamente.';
				$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
				return;
			}
			$arrSalidaHM = explode(':', $row['salida']['salida']); 
			if( empty($arrSalidaHM[0]) || count($arrSalidaHM[0]) > 2 || $arrSalidaHM[0] > 23 || $arrSalidaHM[1] > 59 ){ 
				$arrData['message'] = 'Error al obtener el campo: SALIDA. Verifique e inténtelo nuevamente.';
				$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
				return;
			}
			$arrHastaSalidaHM = explode(':', $row['salida']['hasta_salida']); 
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
  		$this->db->trans_start();
		foreach ($allInputs['arrHorarios'] as $row) { 
			$row['idempleado'] = $allInputs['idempleado']; 
			$fHorarioEmp = $this->model_horario_general->m_obtener_horario_de_empleado($row['idempleado'],$row['idhorario']); 
			// var_dump($fHorarioEmp); exit();
			if( empty($fHorarioEmp) ){ 
				if($this->model_horario_general->m_agregar_horario_empleado($row)){ 
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
	public function eliminar_horario_de_empleado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit();
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
	    $arrData['flag'] = 0;
	    if( @$allInputs['modo'] == 'inline' ){
				if($this->model_horario_general->m_eliminar_horario_de_empleado($allInputs['id'])){ 
					$arrData['message'] = 'Se anularon los datos correctamente';
		    		$arrData['flag'] = 1;
				}
	    }else{
	    	foreach ($allInputs as $row) {
	    		if($this->model_horario_general->m_eliminar_horario_de_empleado($row['id'])){ 
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