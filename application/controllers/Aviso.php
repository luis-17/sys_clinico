<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aviso extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_aviso'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_avisos_intranet()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_aviso->m_cargar_avisos_intranet();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idaviso'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'iduser' => $row['iduser'],
					'idempleado' => $row['idempleado'],
					'titulo' => strtoupper($row['titulo']),
					'redaccion' => $row['redaccion'],
					'fecha_creacion' => formatoConDiaYano($row['fecha_creacion']),
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
	public function lista_avisos()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_aviso->m_cargar_avisos($paramPaginate);
		$totalRows = $this->model_aviso->m_count_avisos($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_av'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_av'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idaviso'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					// 'iduser' => $row['iduser'],
					'idempleado' => $row['idempleado'],
					'empleado' => $row['empleado'],
					'titulo' => $row['titulo'],
					'redaccion' => $row['redaccion'],
					'fecha_creacion' => formatoConDiaYano($row['fecha_creacion']),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_av']
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
		$this->load->view('aviso/aviso_formView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_aviso->m_registrar($allInputs)){
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
		if($this->model_aviso->m_editar($allInputs)){
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
			if( $this->model_aviso->m_anular($row['id']) ){
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
			if( $this->model_aviso->m_habilitar($row['id']) ){
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
			if( $this->model_aviso->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}