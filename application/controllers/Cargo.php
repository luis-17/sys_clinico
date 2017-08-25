<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cargo extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros'));
		$this->load->model(array('model_cargo'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_cargo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_cargo->m_cargar_cargos_cbo($allInputs);
		}else{
			$lista = $this->model_cargo->m_cargar_cargos_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcargo'],
					'descripcion' => $row['descripcion_ca']
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

	public function lista_cargos_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_cargo->m_cargar_cargos_por_autocompletado($allInputs);
		}else{
			$lista = $this->model_cargo->m_cargar_cargos_por_autocompletado();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcargo'], 
					'descripcion' => $row['descripcion_ca'] 
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
	public function lista_cargo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_cargo->m_cargar_cargo($paramPaginate);
		$totalRows = $this->model_cargo->m_count_cargo($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcargo'],
					'descripcion' => $row['descripcion_ca'],
					'agrega_horario_especial' => $row['agrega_horario_especial'] == 1? TRUE: FALSE,
					'agrega_horario_especial_string' => $row['agrega_horario_especial'] == 1? 'SI': 'NO',
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
		$this->load->view('Cargo/cargo_formView');
	}	
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_cargo->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
		    $arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
    	if( ($allInputs['newValue'] == $allInputs['oldValue']) && ($allInputs['newValueAgrega'] == $allInputs['oldValueAgrega'])){
    		$arrData['message'] = 'No se realizaron cambios';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
    		return;
    	}
		if($this->model_cargo->m_editar($allInputs)){
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

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_cargo->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}