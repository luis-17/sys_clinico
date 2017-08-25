<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ubigeo extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_ubigeo'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_departamentos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_ubigeo->m_cargar_departamentos_cbo($allInputs);
		}else{
			$lista = $this->model_ubigeo->m_cargar_departamentos_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => trim($row['iddepartamento']),
					'idubigeo' => $row['idubigeo'],
					'descripcion' => strtoupper($row['descripcion_ubig'])
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
	public function lista_departamento_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_ubigeo->m_cargar_este_departamento_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['iddepartamento']);
			$fArray['descripcion'] = strtoupper($fArray['descripcion_ubig']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_provincias()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ubigeo->m_cargar_provincias_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => trim($row['idprovincia']),
					'idubigeo' => $row['idubigeo'],
					'descripcion' => strtoupper($row['descripcion_ubig'])
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
	public function lista_provincia_departamento_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_ubigeo->m_cargar_esta_provincia_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idprovincia']);
			$fArray['descripcion'] = strtoupper($fArray['descripcion_ubig']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_distritos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ubigeo->m_cargar_distritos_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => trim($row['iddistrito']),
					'idubigeo' => $row['idubigeo'],
					'descripcion' => strtoupper($row['descripcion_ubig'])
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
	public function lista_distrito_provincia_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_ubigeo->m_cargar_este_distrito_por_codigo($allInputs);
		//var_dump($fArray); exit();
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['iddistrito']);
			$fArray['descripcion'] = strtoupper($fArray['descripcion_ubig']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_dptos_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_ubigeo->m_cargar_dptos_por_autocompletado($allInputs);
		}else{
			$lista = $this->model_ubigeo->m_cargar_dptos_por_autocompletado();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['iddepartamento'], 
					'descripcion' => strtoupper($row['descripcion_ubig'] )
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

	public function lista_prov_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_ubigeo->m_cargar_prov_por_autocompletado($allInputs);
		}else{
			$lista = $this->model_ubigeo->m_cargar_prov_por_autocompletado();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idprovincia'], 
					'descripcion' => strtoupper($row['descripcion_ubig']) 
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

	public function lista_distr_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ubigeo->m_cargar_distr_por_autocompletado($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) { 
			$boolTicked = FALSE;
			if( (int)$row['iddistrito'] == 42 ){ // var_dump('aqui xd'); exit();
				$boolTicked = TRUE;
			}
			array_push($arrListado, 
				array( 
					'idubigeo' => $row['idubigeo'], 
					'id' => $row['iddistrito'], 
					'descripcion' => strtoupper($row['descripcion_ubig']), 
					'name' => '<b>'.strtoupper($row['descripcion_ubig']).'</b> ',
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
	public function FunctionName($value='')
	{
		# code...
	}
}