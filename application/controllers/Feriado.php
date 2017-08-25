<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feriado extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('fechas'));
		$this->load->model(array('model_feriado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	
	public function lista_feriados()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_feriado->m_cargar_feriados($paramPaginate, $paramDatos);
		$totalRows = $this->model_feriado->m_count_feriados($paramPaginate, $paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idferiado'],
					'fecha' => darFormatoDiaFecha($row['fecha']),
					'fecha_sql' =>  $row['fecha'],
					'fecha_unix' =>  strtotime($row['fecha'])*1000,
					'descripcion' =>  $row['descripcion'],
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
		$this->load->view('feriado/feriado_formView');
	}
	public function registrar()
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	foreach ($allInputs as $row) {
	    	if($this->model_feriado->m_registrar($row)){
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    			$arrData['flag'] = 0;
    			break;
			}	
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
		if($this->model_feriado->m_editar($allInputs)){
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
			if( $this->model_feriado->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function obtener_pascua()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$anyo = $allInputs['datos']['anyo']['descripcion'];

		

		//$arrData['datos'] = date("d-M-Y", easter_date($anyo));
		// LA PASCUA ES EL DOMINGO PERO LE RESTO 3 DIAS PARA OBTENER EL JUEVES SANTO.
		$fecha = date("d-M-Y", easter_date($anyo));
		$nuevafecha = strtotime ( '-3 day' , strtotime ( $fecha ) ) ;
		$nuevafecha = date ( 'd-M-Y' , $nuevafecha );
		$arrData['datos'] = $nuevafecha;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_feriados_cbo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_feriado->m_lista_feriados_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,  $row['fecha']);
			/*	array(
					'id' => $row['idferiado'],
					'fecha' => darFormatoDiaFecha($row['fecha']),
					'fecha_sql' =>  $row['fecha'],
					'fecha_unix' =>  strtotime($row['fecha'])*1000,
					'descripcion' =>  $row['descripcion'],
				)
			);*/
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