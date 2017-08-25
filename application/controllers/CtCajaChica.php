<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CtCajaChica extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros'));
		$this->load->model(array('model_ct_caja_chica','model_caja_chica'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_caja_chica_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_ct_caja_chica->m_cargar_caja_chica_cbo($allInputs);
		}else{
			$lista = $this->model_ct_caja_chica->m_cargar_caja_chica_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcajachica'],
					'descripcion' => $row['nombre'],
					'idcentrocosto' => $row['idcentrocosto'],
					
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
	public function lista_caja_chica()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_ct_caja_chica->m_cargar_caja_chica($paramPaginate);
		$totalRows = $this->model_ct_caja_chica->m_count_caja_chica();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_cch'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_cch'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idcajachica'],
					'idcajachica' => $row['idcajachica'],
					'descripcion' => $row['nombre'],
					'idsedeempresa' => $row['idsedeempresaadmin'],
					'idcentrocosto' => $row['idcentrocosto'],
					'idsubcatcentrocosto' => $row['idsubcatcentrocosto'],
					'centro_costo' => $row['centro_costo'],
					'codigo_cc' => $row['codigo_cc'],
					'empresa_admin' => strtoupper_total($row['empresa_admin']),
					'empresa' => strtoupper_total($row['empresa']),
					'sede' => strtoupper_total($row['sede']),
					'idresponsable' => $row['idusuarioresponsable'],
					'numero_cheque' => $row['numero_cheque'],
					'monto_cheque' => $row['monto_cheque'],
					'responsable' => strtoupper($row['nombres'] . ' ' . $row['apellido_paterno']. ' ' . $row['apellido_paterno']),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_cch']
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
		$this->load->view('ct-caja-chica/cajaChica_formView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_ct_caja_chica->m_registrar($allInputs)){
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
		if($this->model_ct_caja_chica->m_editar($allInputs)){
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
			if( $this->model_ct_caja_chica->m_anular($row['id']) ){
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
			if( $this->model_ct_caja_chica->m_habilitar($row['id']) ){
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
			if( $this->model_ct_caja_chica->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_formulario_asignar(){
		$this->load->view('ct-caja-chica/asignarCajaChica_formView');
	}

	public function asignar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo asignar la caja';
    	$arrData['flag'] = 0;

    	$esta_aperturada = $this->model_caja_chica->m_esta_aperturada_caja($allInputs); //abuerta o liquidada
    	if($esta_aperturada){
    		$arrData['message'] = 'No puede modificar una caja aperturada o liquidada.';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 

    	$tiene_caja_asignada = $this->model_caja_chica->m_tiene_caja_asignada($allInputs);
    	if($tiene_caja_asignada){
    		$arrData['message'] = 'El responsable ya tiene asignada una caja en la Sede/Empresa.';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}  	

    	if(empty($allInputs['numero_cheque'])){
    		$arrData['message'] = 'Debe registrar un Número de cheque';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(empty($allInputs['monto_cheque'])){
    		$arrData['message'] = 'Debe registrar el monto del cheque';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(empty($allInputs['idresponsable'])){
    		$arrData['message'] = 'Debe registrar un Responsable';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if( $this->model_ct_caja_chica->m_asignar($allInputs) ){
			$arrData['message'] = 'Se asignaron los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}