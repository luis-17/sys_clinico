<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profesion extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros'));
		$this->load->model(array('model_profesion','model_empleado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_profesiones_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		$lista = $this->model_profesion->m_cargar_profesion_por_autocompletado($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idprofesion'], 
					'descripcion' => strtoupper($row['descripcion_prf']) 
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
	public function lista_profesiones_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_profesion->m_cargar_profesion_cbo($allInputs);
		$arrListado = array();
		$boolTicked = FALSE;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idprofesion'], 
					'descripcion' => strtoupper($row['descripcion_prf']),
					'name' => '<b>'.strtoupper($row['descripcion_prf']).'</b> ',
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

	public function listar_profesion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_profesion->m_listar_profesion($paramPaginate);
		$totalRows = $this->model_profesion->m_count_profesion($paramPaginate);
		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idprofesion'], 
					'descripcion' => $row['descripcion_prf'],
				)
			);
		}
		//var_dump($arrListado); exit();
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
		$this->load->view('profesion/profesion_formView');
	}

	public function registrar_profesion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	
    	if($this->model_profesion->m_consultar_profesion($allInputs['descripcion'])){
    		$arrData['message'] = 'Ya Existe la Profesión';
    		$arrData['flag'] = 0;

    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));

			return;
    	}

		if($this->model_profesion->m_registrar_profesion($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_profesion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	
		if($this->model_profesion->m_editar_profesion($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_profesion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

    	foreach ($allInputs as $row) {
    		$result=$this->model_empleado->m_empleado_profesion($row['id']);
    		if(empty($result)){	
    			if( $this->model_profesion->m_anular_profesion($row['id']) ){
					$arrData['message'] = 'Se anularon los datos correctamente';
		    		$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'No se pudo anular los datos';
    				$arrData['flag'] = 0;
				}
    		}else{
    			$arrData['message'] = 'No se pueden anular profesiones asignadas a empleados';
    			$arrData['flag'] = 0;
    		}	
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}