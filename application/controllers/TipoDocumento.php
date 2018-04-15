<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TipoDocumento extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_tipo_documento'));
		// cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); 
	}
	public function lista_tipo_documento_venta_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_venta_cbo($allInputs);
		}else{
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_venta_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idtipodocumento'],
					'descripcion' => $row['descripcion_td'],
					'numero'=> 0
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

	public function lista_tipo_documento_almacenlab_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_almacenlab_cbo($allInputs);
		}else{
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_almacenlab_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idtipodocumento'],
					'descripcion' => $row['descripcion_td'],
					'numero'=> 0
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

	public function lista_tipo_documento_venta()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_venta($allInputs);
		}else{
			$lista = $this->model_tipo_documento->m_cargar_tipo_documento_venta();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idtipodocumento'],
					'descripcion' => $row['descripcion_td'],
					'numero'=> 0
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
	public function lista_tipo_documento_contabilidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_tipo_documento->m_cargar_tipo_documento_contabilidad(); 
		$arrListado = array(); 
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idtipodocumento'],
					'descripcion' => $row['descripcion_td'],
					'porcentaje'=> $row['porcentaje_imp'],
					'nombre_impuesto'=> $row['nombre_impuesto'],
					'codigo_plan'=> $row['codigo_plan']
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
	// LISTADO DE TIPOS DE DOCUMENTOS
	// ==========================================
	public function lista_tipo_documento()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_tipo_documento->m_cargar_tipo_documento($paramPaginate);
		$totalRows = $this->model_tipo_documento->m_count_tipos();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_td'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_td'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idtipodocumento'],
					'descripcion_td' => strtoupper($row['descripcion_td']),
					'abreviatura' => $row['abreviatura'],
					'estado_td' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_td']
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
	// ==========================================
	// FUNCION PARA HABILITAR
	// ==========================================
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_tipo_documento->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ==========================================
	// FUNCION PARA DESHABILITAR
	// ==========================================
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_tipo_documento->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}