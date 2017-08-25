<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MotivoHorarioEspecial extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_motivo_horario_especial'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); m_cargar_submotivos_horario_especial_cbo
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_motivo_horario_especial_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_motivo_horario_especial->m_cargar_motivos_horario_especial_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idmotivohe'],
					'descripcion' => $row['descripcion_mh']
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
	public function lista_sub_motivo_horario_especial_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( empty($allInputs['idmotivo'] )){
			if( empty($allInputs['id']) ){ 
				$arrData = array();
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}else
				$allInputs['idmotivo'] = $allInputs['id'];	
		}
		
		$lista = $this->model_motivo_horario_especial->m_cargar_submotivos_horario_especial_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idsubmotivohe'],
					'descripcion' => $row['descripcion_smh'],
					'submotivo' => $row['descripcion_smh']
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
	public function lista_motivo_horario_especial()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_motivo_horario_especial->m_cargar_motivo_horario_especial($paramPaginate);
		$totalRows = $this->model_motivo_horario_especial->m_count_motivo_horario_especial();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmotivohe'],
					'descripcion' => strtoupper($row['descripcion']),
					'estado' => $row['estado_mh'],
					'agregarAJefes' => $row['agregar_a_jefes']  == 1? TRUE: FALSE,
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
	public function lista_sub_motivo_horario_especial()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		if( empty($allInputs['idmotivo']) ){ 
			$arrData = array();
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

		$lista = $this->model_motivo_horario_especial->m_cargar_submotivos_horario_especial_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idsubmotivohe'],
					'submotivo' => $row['descripcion_smh']
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
		$this->load->view('motivoHorarioEspecial/motivo_he_formView');
	}	
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_motivo_horario_especial->m_registrar($allInputs)){
			$id = GetLastId('idmotivohe','rh_motivo_he');
			foreach ($allInputs['submotivos'] as $row) {
				$row['idmotivo'] = $id;
				$this->model_motivo_horario_especial->m_registrar_submotivo($row);
			}
			$arrData['message'] = 'Se registraron los datos correctamente';
		    $arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
		if($this->model_motivo_horario_especial->m_editar($allInputs)){
			if( !empty($allInputs['submotivos']) ){
				foreach ($allInputs['submotivos'] as $row) {
					$row['idmotivo'] = $allInputs['id'];
					if( @$row['es_nuevo'] ){
						if( $this->model_motivo_horario_especial->m_registrar_submotivo($row) ){
							$arrData['message'] = 'Se registraron los datos correctamente';
				    		$arrData['flag'] = 1;
						}
					}
					
				}
			}
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
			if( $this->model_motivo_horario_especial->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/* ************************* SUB MOTIVOS ************************** */
	public function anular_submotivo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_motivo_horario_especial->m_anular_submotivo($allInputs['idsubmotivo']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}