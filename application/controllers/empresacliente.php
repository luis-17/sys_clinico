<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpresaCliente extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_empresa_cliente'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_empresas()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_empresa_cliente->m_cargar_empresas($paramPaginate);
		$totalRows = $this->model_empresa_cliente->m_count_empresas($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idempresacliente' => $row['idempresacliente'],
					'descripcion' => $row['descripcion'],
					'empresa' => $row['descripcion'],
					'ruc_empresa' => $row['ruc_empresa'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'telefono' => $row['telefono'],
					'pertenece_salud_ocup' => (int)$row['si_salud_ocupacional']
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
	public function lista_empresas_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_empresa_cliente->m_cargar_empresas_cbo($allInputs);
		}else{
			$lista = $this->model_empresa_cliente->m_cargar_empresas_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idempresacliente' => $row['idempresacliente'],
					'descripcion' => $row['descripcion'],
					'ruc_empresa' => $row['ruc_empresa']
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
	public function lista_empresas_salud_ocupacional_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empresa_cliente->m_cargar_empresas_salud_ocupacional_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idempresacliente' => $row['idempresacliente'],
					'descripcion' => $row['descripcion'],
					'ruc_empresa' => $row['ruc_empresa']
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
	public function lista_empresas_cliente_autocomplete() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empresa_cliente->m_cargar_empresa_cliente_autocomplete_so($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idempresacliente'], 
					'descripcion' => $row['descripcion'] 
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
	public function ver_popup_formulario()
	{
		$this->load->view('cliente/empresacliente_formView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// BUSCAR EMPRESAS CON EL MISMO NOMBRE 
		$allInputs['search'] = $allInputs['empresa'];
		$allInputs['nameColumn'] = 'descripcion';
		$listaEmpresa = $this->model_empresa_cliente->m_cargar_empresas_cbo($allInputs);
		if( !empty($listaEmpresa) ){
			$data['idempresacliente'] = $listaEmpresa[0]['idempresacliente'];
		}

		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		$data['descripcion'] = strtoupper($allInputs['empresa']);
		$data['ruc_empresa'] = $allInputs['ruc_empresa'];
		$data['domicilio_fiscal'] = $allInputs['domicilio_fiscal'];
		$data['telefono'] =  (empty($allInputs['telefono']) ? null : $allInputs['telefono']);
		$data['si_salud_ocupacional'] = (int)$allInputs['pertenece_salud_ocup'];
		$data['createdAt'] = date('Y-m-d H:i:s');
		$data['updatedAt'] = date('Y-m-d H:i:s');

		// BUSCAR EMPRESA Y SEDE QUE COINCIDAN 
		if( !empty($data['idempresacliente']) ){
			$arrData['message'] = 'Empresa ya existente.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		
		if($this->model_empresa_cliente->m_registrar($data)){
			$arrData['idempresacliente'] = GetLastId('idempresacliente','empresa_cliente');
			$arrData['ruc'] = $data['ruc_empresa'];
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
		if($this->model_empresa_cliente->m_editar($allInputs)){
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
			if( $this->model_empresa_cliente->m_anular($row['idempresacliente']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}