<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Canal extends CI_Controller {

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_canal'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
		
	public function lista_canal()	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		
		$lista = $this->model_canal->m_cargar_canal($paramPaginate);
		$totalRows = $this->model_canal->m_count_canal();
		$arrListado = array();
		if(isset($allInputs['totalCupos'] ) ) {
			$totalCupos = $allInputs['totalCupos'];
		}else{
			$totalCupos = 0;
		}

		foreach ($lista as $row) {
			$cuposCanal = 0;
			if($totalCupos != 0){
				$cuposCanal = (($totalCupos * $row['porcentaje_canal']) / 100); 
			}

			array_push($arrListado, 
				array(
					'id' => $row['idcanal'],
					'descripcion' => strtoupper($row['descripcion_can']),
					'porcentaje_canal' => $row['porcentaje_canal'],
					'cant_cupos_canal' => $cuposCanal,
					'cant_cupos_adic_canal' => 0
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

	public function ver_popup_formulario(){
		$this->load->view('canal/canal_formView');
	}	

	public function registrar()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_canal->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_canal->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_canal->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_canal_cbo()	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);	
		$lista = $this->model_canal->m_cargar_canal_cbo();
		$arrListado = array();

		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idcanal' => $row['idcanal'],
					'descripcion' => strtoupper($row['descripcion_can']),
					'porcentaje_canal' => $row['porcentaje_canal'],
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

}
