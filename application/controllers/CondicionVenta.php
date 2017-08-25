<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CondicionVenta extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_condicion_venta'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_condicion_venta_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_condicion_venta->m_cargar_condicion_venta_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcondicionventa'], 
					'descripcion' => strtoupper($row['descripcion_cv']) 
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
	public function lista_condicion_venta_cbo_reporte()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_condicion_venta->m_cargar_condicion_venta_cbo($allInputs);
		$arrListado = array();
		$boolTicked = FALSE;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcondicionventa'], 
					'descripcion' => strtoupper($row['descripcion_cv']),
					'name' => '<b>'.strtoupper($row['descripcion_cv']).'</b> ',
					'ticked' => $boolTicked
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
	public function lista_condicion_venta()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_condicion_venta->m_cargar_condicion_venta($paramPaginate);
		$totalRows = $this->model_condicion_venta->m_count_condicion_venta();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_cv'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_cv'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idcondicionventa'],
					'descripcion' => $row['descripcion_cv'],
					'estado' => array( 
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_cv']
					)
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

	public function ver_popup_formulario()
	{
		$this->load->view('condicion-venta/condicionVenta_formView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		$arrData['flag'] = 0;
		/* VALIDAR SI LA CONDICION YA EXISTE */
		$arrParams = array('descripcion' => $allInputs['descripcion']);
		$fCondicion = $this->model_condicion_venta->m_buscar_condicion_venta($arrParams);
		if( $fCondicion > 0 ) {
			$arrData['message'] = 'La condicion de Venta , ya existe.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		if($this->model_condicion_venta->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_condicion_venta->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_condicion_venta->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_condicion_venta->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_condicion_venta->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}




}