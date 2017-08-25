<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ambiente extends CI_Controller {

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_ambiente'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); 
	}
	public function lista_ambiente_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ambiente->m_cargar_ambiente_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idambiente'],
					'descripcion' => $row['numero_ambiente']
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
	public function lista_ambiente()	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_ambiente->m_cargar_ambiente($paramPaginate);
		$totalRows = $this->model_ambiente->m_count_ambiente();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_amb'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_amb'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}

			array_push($arrListado, 
				array(
					'id' => $row['idambiente'],
					'numero_ambiente' => strtoupper($row['numero_ambiente']),
					'piso' => strtoupper($row['piso']),
					'comentario' => strtoupper($row['comentario']),
					'idsede' => $row['idsede'],
					'descripcion_sede' => strtoupper($row['descripcion_sede']),
					'idcategoriaconsul' => $row['idcategoriaconsul'],
					'descripcion_cco' => strtoupper($row['descripcion_cco']),
					'idsubcategoriaconsul' => $row['idsubcategoriaconsul'],
					'descripcion_scco' => $row['descripcion_scco'],
					'estado_amb' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_amb']
					)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;

    	$arrData['descripcion_sede'] = strtoupper($this->sessionHospital['sede']);
    	$arrData['idsede'] = $this->sessionHospital['idsede'];

    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_formulario(){
		$this->load->view('ambiente/ambiente_formView');
	}	

	public function registrar()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_ambiente->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_ambiente->m_cambiar_estatus($row['id'], 0) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function habilitar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_ambiente->m_cambiar_estatus($row['id'],1) ){
				$arrData['message'] = 'Se habilito correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function deshabilitar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_ambiente->m_cambiar_estatus($row['id'],2) ){
				$arrData['message'] = 'Se deshabilito correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_ambiente->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_ambiente_por_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ambiente->m_cargar_ambiente_por_sede($allInputs, null);
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idambiente'],
					'numero_ambiente' => strtoupper($row['numero_ambiente']),
					'piso' => strtoupper($row['piso']),
					'idsede' => $row['idsede'],
					'idcategoriaconsul' => $row['idcategoriaconsul'],
					'descripcion_cco' =>  strtoupper($row['descripcion_cco']),
					'idsubcategoriaconsul' => $row['idsubcategoriaconsul'],
					'descripcion_scco' =>  strtoupper($row['descripcion_scco']),
					'descripcion_amb' => strtoupper($row['numero_ambiente']) . '- Piso: ' . strtoupper($row['piso']) . '-'. strtoupper($row['descripcion_scco'])
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
	public function lista_ambiente_por_sede_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_ambiente->m_cargar_ambiente_por_sede_session($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idambiente'],
					// 'descripcion' => strtoupper($row['numero_ambiente']),
					'descripcion' => strtoupper($row['numero_ambiente']) . '- Piso: ' . strtoupper($row['piso']) . '-'. strtoupper($row['descripcion_scco']),
					'numero_ambiente' => strtoupper($row['numero_ambiente']),
					'piso' => strtoupper($row['piso']),
					'idsede' => $row['idsede'],
					'idcategoriaconsul' => $row['idcategoriaconsul'],
					'descripcion_cco' =>  strtoupper($row['descripcion_cco']),
					'idsubcategoriaconsul' => $row['idsubcategoriaconsul'],
					'descripcion_scco' =>  strtoupper($row['descripcion_scco'])
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