<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Precio extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_precio'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_precio_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_precio->m_cargar_precio_cbo($allInputs);
		}else{
			$lista = $this->model_precio->m_cargar_precio_cbo();
		}
		$arrListado = array(); 
		foreach ($lista as $row) { 
			$tipoPrecio  = ( $row['tipo_precio'] == 1 ? ' (+)' : ' (-)' );
			array_push($arrListado, 
				array(
					'id' => $row['idprecio'],
					'descripcion' => $row['nombre'].$tipoPrecio,
					'tipo_precio' => $row['tipo_precio'],
					'porcentaje' => $row['porcentaje']
					
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
	// ==========================================
	// LISTADO DE TIPOS DE PRECIOS
	// ==========================================
	public function lista_precio()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_precio->m_cargar_precios($paramPaginate);
		$totalRows = $this->model_precio->m_count_precios();
		$arrListado = array();
		foreach ($lista as $row) {
			/*if( $row['tipo_precio'] == 1 ){
				$estado = 'INCREMENTAL';
				$clase = 'label-success';
			}
			if( $row['tipo_precio'] == 2 ){
				$estado = 'DECREMENTAL';
				$clase = 'label-default';
			}*/
			array_push($arrListado, 
				array(
					'id' => $row['idprecio'],
					'nombre' => strtoupper($row['nombre']),
					'descripcion' => $row['descripcion'],
					'porcentaje' => $row['porcentaje'],
					
					'estado' => $row['estado_pr'],
					'str_tp' => $row['tipo_precio'] == 1? 'INCREMENTAL':'DECREMENTAL',
					'tipo_precio' => $row['tipo_precio']
					/*'tipo_precio' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['tipo_precio']
					)*/
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
	// ==========================================
	// MUESTRA EL POPUP
	// ==========================================
	public function ver_popup_formulario()
	{
		$this->load->view('precio/precio_formView');
	}
	// ==========================================
	// CRUD - REGISTRAR
	// ==========================================
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_precio->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ==========================================
	// CRUD - EDITAR
	// ==========================================
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_precio->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ==========================================
	// CRUD - ELIMINAR
	// ==========================================
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_precio->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}

