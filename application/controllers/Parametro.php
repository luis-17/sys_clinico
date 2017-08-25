<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parametro extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_parametro'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_parametro_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_parametro->m_cargar_parametro_cbo($allInputs);
		}else{
			$lista = $this->model_parametro->m_cargar_parametro_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idparametro'],
					'descripcion' => $row['descripcion_par']
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
	// public function lista_parametro()
	// {
	// 	$allInputs = json_decode(trim(file_get_contents('php://input')),true);
	// 	$paramPaginate = $allInputs['paginate'];

	// 	$lista = $this->model_parametro->m_cargar_parametro($paramPaginate);
	// 	$totalRows = $this->model_parametro->m_count_parametro($paramPaginate);
	// 	$arrListado = array();
	// 	foreach ($lista as $row) {
	// 		if( $row['estado_par'] == 1 ){
	// 			$estado = 'HABILITADO';
	// 			$clase = 'label-success';
	// 		}elseif( $row['estado_par'] == 2 ){
	// 			$estado = 'DESHABILITADO';
	// 			$clase = 'label-default';
	// 		}
	// 		if( $row['valor_ambos'] == 1 ){
	// 			$valor = 'SI';
				
	// 		}elseif( $row['valor_ambos'] == 0 ){
	// 			$valor = 'NO';
				
	// 		}
	// 		array_push($arrListado,
	// 			array(
	// 				'id' => $row['idparametro'],
	// 				'descripcion' => $row['descripcion_par'],
	// 				'valorNormalHombres' => $row['valor_normal_h'],
	// 				'valorNormalMujeres' => $row['valor_normal_m'],
	// 				'valorAmbos' => array(
	// 					'string' => $valor,
	// 					'bool' =>$row['valor_ambos']
	// 				),
	// 				'separador' => $row['separador'],
	// 				'estado' => array(
	// 					'string' => $estado,
	// 					'clase' =>$clase,
	// 					'bool' =>$row['estado_par']
	// 				)
	// 			)
	// 		);
	// 	}
 //    	$arrData['datos'] = $arrListado;
 //    	$arrData['paginate']['totalRows'] = $totalRows;
 //    	$arrData['message'] = '';
 //    	$arrData['flag'] = 1;
	// 	if(empty($lista)){
	// 		$arrData['flag'] = 0;
	// 	}
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
	public function lista_parametro()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_parametro->m_cargar_parametros($paramPaginate);
		$totalRows = $this->model_parametro->m_count_parametros($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_par'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}elseif( $row['estado_par'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idparametro'],
					'descripcion' => $row['descripcion_par'],
					'combo' => $row['nombre_combo'],
					'texto_agregado' => $row['texto_adicional'],
					'descripcion_adicional' => $row['descripcion_adicional'],
					'separador' => $row['separador'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_par']
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
	public function listar_valores_parametro()
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		
		$rowPar = $this->model_parametro->m_cargar_valores_parametro($paramDatos);
		$arrListado = array();
		$arrListadoJson = array();
		$arrListado = array(
			'valorNormalHombres' => $rowPar['valor_normal_h'],
			'valorNormalMujeres' => $rowPar['valor_normal_m'],
			'valorAmbos' => array(
				// 'string' => $valor,
				'bool' =>$rowPar['valor_ambos']
			),
		);
		if( empty($rowPar['valor_json']) ){
			$arrJson = NULL;
		}else{
			$arrJson = json_decode(trim($rowPar['valor_json']),true);
		}
		if( $this->sessionHospital['idsedeempresaadmin'] == 9){
			if( count($arrJson) >= 1 ){
				foreach ($arrJson as $row) {
					array_push($arrListadoJson,
						array(
							'min_rango' => $row['min_rango'],
							'max_rango' => $row['max_rango'],
							'tipo_rango' => ($row['min_rango'] == NULL && $row['max_rango'] == NULL)? NULL:  $row['tipo_rango'],
							'descripcion' => ($row['min_rango'] == NULL && $row['max_rango'] == NULL)? NULL:  $row['descripcion'],
							'valor_etario_h' => $row['valor_etario_h'],
							'valor_etario_m' => $row['valor_etario_m'],
						)
					);
				}
			}else{
				array_push($arrListadoJson,
					array(
						'min_rango' => NULL,
						'max_rango' => NULL,
						'tipo_rango' => NULL,
						'descripcion' => NULL,
						'valor_etario_h' => $rowPar['valor_normal_h'],
						'valor_etario_m' => $rowPar['valor_ambos'] == 1? $rowPar['valor_normal_h'] : $rowPar['valor_normal_m'],
					)
				);
			}
		}
		
		// var_dump($arrListado); exit();
    	$arrData['datosJSon'] = $arrListadoJson;
    	$arrData['datos'] = $arrListado;
    	// $arrData['paginate']['totalRows'] = $totalRows;
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
		$this->load->view('parametro/parametro_formView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_parametro->m_registrar($allInputs)){
			$allInputs['idparametro'] = GetLastId('idparametro', 'parametro');
			if($this->model_parametro->m_registrar_valores($allInputs)){
				$arrData['message'] = 'Se registraron los datos correctamente';
    			$arrData['flag'] = 1;
			}
			
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
    	$allInputs['valor_json'] = NULL;
    	$allInputs['valor_json'] = json_encode($allInputs['arrValores']);
		if($this->model_parametro->m_editar($allInputs)){
			if( $row = $this->model_parametro->m_buscar_valores_sede($allInputs) ){ // si se encuentra se edita
				$allInputs['idparametrovalorsede'] = $row['idparametrovalorsede'];
				if( $this->model_parametro->m_editar_valores($allInputs) ){
					$arrData['message'] = 'Se editaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}	
			}else{ // sino se registra
				$allInputs['idparametro'] = $allInputs['id'];
				if( $this->model_parametro->m_registrar_valores($allInputs) ){
					$arrData['message'] = 'Se registraron los datos correctamente';
		    		$arrData['flag'] = 1;
				}
			}
		
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
			if( $this->model_parametro->m_anular($row['id']) ){
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
			if( $this->model_parametro->m_habilitar($row['id']) ){
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
			if( $this->model_parametro->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}


}