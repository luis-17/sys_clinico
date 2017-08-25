<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConfigVariable extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas','contable'));
		$this->load->model(array('model_config_variable'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	// VARIABLES DE LEY 
	public function ver_popup_config_variable()
	{
		$this->load->view('planilla/popup_config_variable');
	}
	public function listar_config_variable()
	{
		$lista = $this->model_config_variable->m_listar_config_variable();

		$arrListado = array(
			'id' => $lista['idconfigvariable'],
			'essalud' => (int)$lista['essalud'],
			'asignacion_familiar' => (int)$lista['asignacion_familiar'],
			'rmv' => floatval($lista['rmv']),
			'uit' => floatval($lista['uit']),
			'onp' => (int)$lista['onp'],
			'rma' => floatval($lista['rma']),
			'fecha_registro' => darFormatoDMY($lista['fecha_registro']),
		);
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
	public function registrar_config_variable()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

		if($allInputs['uit'] == $allInputs['oldUit'] && $allInputs['rmv'] == $allInputs['oldRmv'] && $allInputs['essalud'] == $allInputs['oldEssalud'] && $allInputs['asignacion_familiar'] == $allInputs['oldAsignacion_familiar'] && $allInputs['onp'] == $allInputs['oldOnp'] && $allInputs['rma'] == $allInputs['oldRma']){
			$arrData['message'] = 'Es igual al anterior';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		
		if($this->model_config_variable->m_registrar_config_variable($allInputs)){
			$this->model_config_variable->m_actualizar_config_variable_vigente($allInputs);
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
				
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}	