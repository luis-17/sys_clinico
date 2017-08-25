<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Diagnostico extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->model(array('model_diagnostico'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_diagnostico()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_diagnostico->m_cargar_diagnostico($paramPaginate);
		$totalRows = $this->model_diagnostico->m_count_diagnostico($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_cie'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_cie'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['iddiagnosticocie'],
					'codigo' => strtoupper($row['codigo_cie']),
					'descripcion' => $row['descripcion_cie'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_cie']
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
	public function lista_diagnostico_grilla_modal()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_diagnostico->m_cargar_diagnostico_grilla_modal($paramPaginate);
		$totalRows = $this->model_diagnostico->m_count_diagnostico_grilla_modal($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_cie'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_cie'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['iddiagnosticocie'],
					'codigo' => strtoupper($row['codigo_cie']),
					'descripcion' => $row['descripcion_cie'],
					'cantidad_caracteres' => $row['cantidad_caracteres'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_cie']
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
	public function lista_diagnostico_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);

		$lista = $this->model_diagnostico->m_cargar_diagnostico_autocomplete($allInputs['searchColumn'],$allInputs['searchText']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['iddiagnosticocie'],
					'codigo' => strtoupper($row['codigo_cie']),
					'descripcion_cie' => strtoupper($row['descripcion_cie']),
					'descripcion' => strtoupper($row['codigo_cie'].' - '.$row['descripcion_cie'])
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
	public function lista_este_diagnostico() 
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$allInputs['codigo'] = strtoupper($allInputs['codigo']);
		$fila = $this->model_diagnostico->m_cargar_este_diagnostico_por_codigo($allInputs['codigo']);
		$fArray = array();
		if( !empty($fila) ){
			$fArray = array( 
				'id' => $fila['iddiagnosticocie'],
				'codigo' => strtoupper($fila['codigo_cie']),
				'descripcion_cie' => strtoupper($fila['descripcion_cie']),
				'descripcion' => strtoupper($fila['codigo_cie'].' - '.$fila['descripcion_cie'])
			);
		}
    	$arrData['datos'] = $fArray;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($fila)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('diagnostico/diagnostico_formView');
	}
	public function ver_popup_CIE10()
	{
		$this->load->view('diagnostico/diagnosticoCIE10_formView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_diagnostico->m_registrar($allInputs)){
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
		if($this->model_diagnostico->m_editar($allInputs)){
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
			if( $this->model_diagnostico->m_anular($row['id']) ){
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
			if( $this->model_diagnostico->m_habilitar($row['id']) ){
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
			if( $this->model_diagnostico->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}