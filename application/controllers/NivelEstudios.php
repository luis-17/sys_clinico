<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class NivelEstudios extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_nivel_estudios'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_nivel_estudio_por_tipo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_nivel_estudios->m_cargar_nivel_estudio_por_tipo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idnivelestudio'],
					'descripcion' => $row['descripcion_ne'],
					'tipo_ne' => $row['tipo_ne']
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
	public function cargar_estudios_empleado()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_nivel_estudios->m_cargar_estudios_empleado($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'iddetalleestudio' => $row['iddetalleestudio'],
					'id' => $row['idnivelestudio'],
					'nivel_estudio' => $row['descripcion_ne'],
					'tipo_nivel'=> $row['tipo_ne'],
					'especialidad' => $row['especialidad'],
					'centro_estudio' => $row['centro_estudio'],
					'fecha_desde' => darFormatoDMY($row['fecha_desde']),
					'fecha_hasta' => darFormatoDMY($row['fecha_hasta']),
					'estudio_completo' => $row['estudio_completo'],
					'grado_academico' => $row['grado_academico'],
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
	public function agregar_estudio_empleado()
	{ 
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		// $paramDatos = $allInputs['datos'];
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se actualizaron los datos';
    	$arrData['flag'] = 0;
		if( $this->model_nivel_estudios->m_agregar_estudio_a_empleado($allInputs) ){ 
			$arrData['message'] = 'Se agregaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_estudio_empleado()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramDatos = $allInputs['datos'];
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se actualizaron los datos';
    	$arrData['flag'] = 0;
		if( $this->model_nivel_estudios->m_editar_estudio_a_empleado($paramDatos) ){ 
			$arrData['message'] = 'Se actualizaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_estudio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// foreach ($allInputs as $row) { 
		if( $this->model_nivel_estudios->m_anular_estudio($allInputs['iddetalleestudio']) ){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		// }
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}