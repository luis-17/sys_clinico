<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PlanillaMaster extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas','otros'));
		$this->load->model(array('model_planilla_master'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function lista_planillas_master(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramEmpresa = $allInputs['empresa'];
		$lista = $this->model_planilla_master->m_cargar_planillas($paramPaginate, $paramEmpresa);
		$totalRows = $this->model_planilla_master->m_count_planillas($paramEmpresa);
		$arrListado = array();
		foreach ($lista as $row) {

			array_push($arrListado, 
				array(
					'id' => $row['idplanillamaster'],
					'idplanillamaster' => $row['idplanillamaster'],
					'descripcion' => strtoupper($row['descripcion_plm']),
					'descripcion_empresa' => strtoupper($row['empresa']),
					'idempresa' => $row['idempresa'],
					'estado_plm' => $row['estado_plm'],
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
	
	public function ver_popup_planilla_master(){
		$this->load->view('planilla/planillaMaster_formView');
	}	

	public function registrar(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
    	$arrData['flag'] = 0;

    	if(empty($allInputs['empresa']['id'])){
    		$arrData['message'] = 'Debe seleccionar empresa.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	
    	$result=$this->model_planilla_master->m_consultar($allInputs);
    	if(!empty($result)){
    		$arrData['message'] = 'Ya existe una Planilla con ese Nombre.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	if($this->model_planilla_master->m_registrar($allInputs)){
    		$arrData['message'] = 'Planilla registrada exitosamente.';
    		$arrData['flag'] = 1;
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	

	public function editar(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
    	$arrData['flag'] = 0;

    	if($this->model_planilla_master->m_editar($allInputs)){
    		$arrData['message'] = 'Planilla actualizada exitosamente.';
    		$arrData['flag'] = 1;
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
    	$arrData['flag'] = 0;

    	if($this->model_planilla_master->m_anular($allInputs)){
    		$arrData['message'] = 'Planilla anulada exitosamente.';
    		$arrData['flag'] = 1;
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_planillas_master_cbo(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
    	$arrData['flag'] = 0;

		$lista = $this->model_planilla_master->m_cargar_planillas_master_cbo($allInputs['empresa']);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idplanillamaster'],
					'idplanillamaster' => $row['idplanillamaster'],
					'descripcion' => strtoupper($row['descripcion_plm']),
					'idempresa' => $row['idempresa'],
					'estado_plm' => $row['estado_plm'],
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
?>