<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProveedorFarmacia extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_proveedor_farmacia'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_proveedor_farmacia_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'razon_social' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_proveedor_farmacia->m_cargar_proveedor_farmacia_cbo($allInputs);
		}else{
			$lista = $this->model_proveedor_farmacia->m_cargar_proveedor_farmacia_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idproveedor'],
					'razon_social' => $row['razon_social']
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
	public function lista_proveedor_farmacia()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_proveedor_farmacia->m_cargar_proveedor_farmacia($paramPaginate);
		$totalRows = $this->model_proveedor_farmacia->m_count_proveedor_farmacia();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_prov'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_prov'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idproveedor'],
					'razon_social' => $row['razon_social'],
					'ruc' => $row['ruc'],
					'idtipoproveedor' => $row['idtipoproveedor'],
					'descripcion_tprov' =>$row['descripcion_tprov'],
					'nombre_comercial' =>$row['nombre_comercial'],
					'direccion_fiscal' => $row['direccion_fiscal'],
					'representante' => $row['representante'],
					'telefono' => $row['telefono'],
					'fax' => $row['fax'],
					'celular' => $row['celular'],
					'email' => $row['email'],
					'forma_pago' => (int)$row['forma_pago'],
					'moneda' => (int)$row['moneda'],
					'observaciones' => $row['observaciones_prov'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_prov']
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
	public function lista_proveedor_farmacia_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idproveedor']);
			$fArray['razon_social'] = strtoupper($fArray['razon_social']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_este_proveedor_por_ruc()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_ruc($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idproveedor']);
			$fArray['razon_social'] = strtoupper($fArray['razon_social']);
			// $fArray['direccion_fiscal'] = $fArray['direccion_fiscal'];
			// $fArray['representante'] = $fArray['representante'];
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('proveedor-farmacia/proveedorFarmacia_formView');
	}
	public function ver_popup_busqueda_proveedor(){
		$this->load->view('proveedor-farmacia/busquedaProveedorFarmacia_formView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$prov = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_ruc($allInputs);
    	if(!empty($prov['idproveedor'])){
    		$arrData['message'] = 'Error al registrar los datos, RUC duplicado';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$consulta = array(
	    		'nameColumn' => 'razon_social',
	    		'search' => $allInputs['razon_social']
    		);
    	$prov = $this->model_proveedor_farmacia->m_cargar_proveedor_farmacia_cbo($consulta);
    	if(count($prov)>0){
    		$arrData['message'] = 'Error al registrar los datos, Razón Social duplicada';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if($this->model_proveedor_farmacia->m_registrar($allInputs)){
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

    	$prov = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_ruc($allInputs);
    	if(!empty($prov['idproveedor']) && $prov['idproveedor'] != $allInputs['id']){
    		$arrData['message'] = 'Error al registrar los datos, RUC duplicado';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$consulta = array(
	    		'nameColumn' => 'razon_social',
	    		'search' => $allInputs['razon_social'],
	    		'id' => $allInputs['id']
    		);
    	$prov = $this->model_proveedor_farmacia->m_carga_proveedor_farmacia_por_rs($consulta);
    	if(count($prov)>0){
    		$arrData['message'] = 'Error al registrar los datos, Razón Social duplicada';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if($this->model_proveedor_farmacia->m_editar($allInputs)){
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
			if( $this->model_proveedor_farmacia->m_anular($row['id']) ){
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
			if( $this->model_proveedor_farmacia->m_habilitar($row['id']) ){
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
			if( $this->model_proveedor_farmacia->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}