<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sede extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_sede'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_sedes()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_sede->m_cargar_sedes($paramPaginate);
		$totalRows = $this->model_sede->m_count_sedes();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['tiene_prog_cita'] == '1'){
				$objEstado['claseSwitch'] = 'success';
				$objEstado['labelText'] = 'HABILITADO';
				$objEstado['value'] = $row['tiene_prog_cita'];
				$objEstado['boolBloqueo'] = FALSE;
			}else {
				$objEstado['claseSwitch'] = 'danger';
				$objEstado['labelText'] = 'DESHABILITADO';
				$objEstado['value'] = $row['tiene_prog_cita'];
				$objEstado['boolBloqueo'] = TRUE;
			}
			array_push($arrListado, 
				array(
					'id' => $row['idsede'],
					'descripcion' => strtoupper($row['descripcion']),
					'estado' => $row['estado_se'],
					'hora_inicio' => $row['hora_inicio_atencion'],
					'hora_fin' => $row['hora_final_atencion'],
					'hora_inicio_formato' => darFormatoHora($row['hora_inicio_atencion']),
					'hora_fin_formato' => darFormatoHora($row['hora_final_atencion']),
					'intervalo_sede' => $row['intervalo_sede'],
					'tiene_prog_cita' => $objEstado						
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
	public function lista_sedes_cbo()
	{

		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_sede->m_cargar_sedes_cbo($allInputs);
		}else{
			$lista = $this->model_sede->m_cargar_sedes_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsede'],
					'idsede' => $row['idsede'],
					'descripcion' => $row['descripcion'],
					'hora_inicio' => $row['hora_inicio_atencion'],
					'hora_final' => $row['hora_final_atencion']
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
	public function lista_sedes_por_empresa_cbo()
	{

		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_sede->m_cargar_sedes_por_empresa_cbo($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsedeempresaadmin'],
					'descripcion' => $row['descripcion'],
					'idsede' => $row['idsede'],
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
	public function lista_sedes_no_agregadas_a_empresa_admin(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];

		$listaSedesNoAgregados = $this->model_sede->m_cargar_sedes_no_agregados_a_empresa_admin($paramPaginate,$datos);
		 
		$totalRows = $this->model_sede->m_count_sedes_no_agregados_a_empresa_admin($datos);
		//var_dump($totalRows); exit();
		$arrListado = array();
		foreach ($listaSedesNoAgregados as $row) {

			array_push($arrListado, 
				array(
					'idsede' => $row['idsede'],
					'sede' => $row['descripcion'],
					
				)
			);
		}
		// var_dump($arrListado); exit();
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaSedesNoAgregados)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_sedes_agregadas_a_empresa_admin(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];

		$listaSedesAgregadas = $this->model_sede->m_cargar_sedes_agregados_a_empresa_admin($paramPaginate,$datos);
		 
		$totalRows = $this->model_sede->m_count_sedes_agregados_a_empresa_admin($datos);
		//var_dump($totalRows); exit();
		$arrListado = array();
		foreach ($listaSedesAgregadas as $row) {

			array_push($arrListado, 
				array(
					'idsede' => $row['idsede'],
					'sede' => $row['descripcion'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					
				)
			);
		}
		// var_dump($arrListado); exit();
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaSedesAgregadas)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_horario_sede()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Error';
    	$arrData['flag'] = 0;
		$rowSede = $this->model_sede->m_cargar_sede_por_id($allInputs['idsede']);
		$arrHorario = array();
		$rango = get_rangohoras_am_pm($rowSede['hora_inicio_atencion'], $rowSede['hora_final_atencion']);
		
		if(empty($rango)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'La sede no tiene rango de atencion';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		foreach ($rango as $key => $value) {
			array_push($arrHorario, array(
				'id' => $key,
				'descripcion' => $value,
				)
			);
		}
		// var_dump($arrHorario); exit();
    	$arrData['datos'] = $arrHorario;
		$arrData['flag'] = 1;
		$arrData['message'] = '';
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('sede/sede_formView');
	}	
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_sede->m_registrar($allInputs)){
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
		if($this->model_sede->m_editar($allInputs)){
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
			if( $this->model_sede->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function consultar(){
		$row = $this->model_sede->m_consultar($this->sessionHospital['idsede']);
		$arrListado = array(
			'idsede' => $row['idsede'],
			'hora_inicio' => $row['hora_inicio_atencion'],
			'hora_fin' => $row['hora_final_atencion'],
			'intervalo' => $row['intervalo_sede'],		
			'descripcion' => $row['descripcion'],		
		);	
			
		$arrData['datos'] = $arrListado;
		$arrData['message'] = '';
	    $arrData['flag'] = 1;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		
	}

	public function update_tiene_prog_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo actualizar los datos';
    	$arrData['flag'] = 0;

		if( $this->model_sede->m_update_tiene_prog_cita($allInputs) ){
			$arrData['message'] = 'Se actualizaron los datos correctamente';
			$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}