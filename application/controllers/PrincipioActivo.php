<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PrincipioActivo extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_principio_activo', 'model_medicamento_almacen'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_principio_activo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_principio_activo->m_cargar_principio_activo_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idprincipioactivo'], 
					'descripcion' => strtoupper($row['descripcion']) 
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

	public function lista_principio_activo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramComponenteFormula = $allInputs['datos'];

		$lista = $this->model_principio_activo->m_cargar_principio_activo($paramPaginate, $paramComponenteFormula);
		$totalRows = $this->model_principio_activo->m_count_principio_activo($paramPaginate, $paramComponenteFormula);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_pa'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_pa'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idprincipioactivo'],
					'descripcion' => $row['descripcion'],
					'es_componente_formula' => $row['es_componente_formula'] == 2 ? 0 : 1,
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_pa']
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
		$this->load->view('principio-activo/principioActivo_formView');
	}

	public function ver_popup_formulario_principio_activo()
	{
		$this->load->view('principio-activo/principioActivo_ConsultaformView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_principio_activo->m_registrar($allInputs)){
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
		if($this->model_principio_activo->m_editar($allInputs)){
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
			if( $this->model_principio_activo->m_anular($row['id']) ){
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
			if( $this->model_principio_activo->m_habilitar($row['id']) ){
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
			if( $this->model_principio_activo->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/***********************************************************/
	/** 			PRINCIPIO ACTIVO - MEDICAMENTO *************/
	/***********************************************************/

	public function lista_principio_activo_medicamento()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramDatos = $allInputs['datos'];
		// var_dump($allInputs); exit();
		$lista = $this->model_principio_activo->m_cargar_principio_activo_medicamento($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_mp'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_mp'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idmedicamentoprincipio'],
					'descripcion' => $row['descripcion'],
					'idprincipioactivo' => $row['idprincipioactivo'],
					'idmedicamento' => $row['idmedicamento'],
					'abreviatura' => $row['abreviatura'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_mp']
					)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	//$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_sin_principio_activo_medicamento()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_principio_activo->m_cargar_sin_principio_activo_medicamento($paramPaginate,$paramDatos);
		$totalRows = $this->model_principio_activo->m_count_sin_principio_activo_medicamento($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_pa'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_pa'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idprincipioactivo'],
					'descripcion' => $row['descripcion'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_pa']
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
	public function registrar_principio_activo_medicamento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_principio_activo->m_registrar_principio_activo_medicamento($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_principio_activo_medicamento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_principio_activo->m_anular_principio_activo_medicamento($allInputs['id']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/**************** FIN PRONCIPIO ACTIVO - MEDICAMENTO ***********************************/
	/**************** Busqueda Principio Activo ********************************************/
	public function lista_busqueda_principio_activo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$idmedicamento = $allInputs['datos']['temporal']['producto']['id'];
		$listaPrincipio = $this->model_principio_activo->m_cargar_principio_activo_este_medicamento($idmedicamento);
		$concatenado = '';

		foreach ($listaPrincipio as $principio) {
			if( end($listaPrincipio) == $principio){
				$concatenado .= $principio['idprincipioactivo'];
			}else{
				$concatenado .= $principio['idprincipioactivo'] . ';'; 
			}
			
		}
		//$concatenado = implode(';', $listaPrincipio['idprincipioactivo']);
		
		$paramDatos['concatenado'] = $concatenado;
		$listamedicamentos = $this->model_principio_activo->m_cargar_principio_activo_medicamento_similar($paramPaginate,$paramDatos);
		//var_dump($listamedicamentos); exit();
		
		$totalRows = $this->model_principio_activo->m_count_principio_activo_medicamento_similar($paramPaginate,$paramDatos);
		$arrListado = array(); 
		$stock_central_producto_principal = $this->model_medicamento_almacen->m_cargar_stock_subalmacen_central($paramDatos);
		// var_dump($listaGrupos); exit();
		if( $listamedicamentos ){
			foreach ($listamedicamentos as $row) {
				array_push($arrListado, 
					array(
						'id' => $row['idmedicamento'],
						'medicamento' => $row['medicamento'],
						'idtipoproducto' => $row['idtipoproducto'],
						'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
						'stock' => $row['stock_actual_malm'],
						'stock_central' => $row['stock_central'],
						'precio' => $row['precio_venta'],
						'idlaboratorio' => $row['idlaboratorio'],
						'laboratorio' => $row['nombre_lab']
					)
				);
			}
	    	
		}
		$arrData['principios'] = $listaPrincipio;
		$arrData['producto_principal'] = $stock_central_producto_principal;
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaGrupos)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}



}