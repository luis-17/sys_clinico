<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CategoriaConsul extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_categoria_consul'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function lista_categoria_consul(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_categoria_consul->m_cargar_categoria_consul($paramPaginate);
		$totalRows = $this->model_categoria_consul->m_count_categoria_consul($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_cco'])
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
		$this->load->view('categoria-consul/categoria-consul_formView');
	}

	public function registrar()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_categoria_consul->m_registrar($allInputs)){
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
		if($this->model_categoria_consul->m_editar($allInputs)){
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
			if( $this->model_categoria_consul->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}


	public function ver_popup_formulario_subcategoria(){
		$this->load->view('categoria-consul/subcategoria-consul_formView');
	}

	public function lista_subcategoria_consul(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_categoria_consul->m_cargar_subcategoria_consul($paramPaginate,$paramDatos);
		$totalRows = $this->model_categoria_consul->m_count_subcategoria_consul($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsubcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_scco'])
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

	public function registrar_subcategoria()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_categoria_consul->m_registrar_subcategoria($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar_subcategoria_en_grid()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;
		if( $this->model_categoria_consul->m_editar_subcategoria_en_grid($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_subcategoria(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_categoria_consul->m_anular_subcategoria($allInputs) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_categorias_consul_cbo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit();
		$lista = $this->model_categoria_consul->m_cargar_categoria_consul_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => (int)$row['idcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_cco'])
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

	public function lista_subcategoria_consul_cbo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['idCategoria'];

		$lista = $this->model_categoria_consul->m_cargar_subcategoria_consul_cbo($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => (int)$row['idsubcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_scco'])
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
	public function lista_subcategoria_consul_asistencia()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_categoria_consul->m_cargar_subcategoria_consul_cbo(2);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => (int)$row['idsubcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_scco'])
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