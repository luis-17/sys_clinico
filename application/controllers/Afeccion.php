<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Afeccion extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_afeccion'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_afecciones_de_paciente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$listaAfeccionesPaciente = $this->model_afeccion->m_cargar_afeccion_paciente($paramPaginate,$datos);
		$totalRows = $this->model_afeccion->m_count_afeccion_paciente($datos);
		$arrListado = array();
		foreach ($listaAfeccionesPaciente as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idhistoriaafeccion'],
					'idhistoria' => $row['idhistoria'],
					'tipoafeccion' => ($row['tipo_afeccion']==1?'ENFERMEDAD':'ALERGIA') ,
					'descripcion' => $row['descripcion'],
					'estado' => $row['estado_afe'],
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaAfeccionesPaciente)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_afeccion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	foreach($allInputs as $row){
			if($this->model_afeccion->m_registrar_afeccion($row)){
				$arrData['message'] = 'Se registraron los datos correctamente';
    			$arrData['flag'] = 1;
			}
    	}

    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_afeccion_edicion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_afeccion->m_registrar_afeccion($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_afeccion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_afeccion->m_anular($allInputs['id']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}