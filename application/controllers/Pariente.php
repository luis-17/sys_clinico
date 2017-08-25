<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pariente extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_pariente'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_parientes()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_pariente->m_cargar_parientes_de_empleado($paramPaginate,$paramDatos);
		$totalRows = $this->model_pariente->m_count_parientes_de_empleado($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strEstadoCivil = '-';
			if( $row['estado_civil'] == 1 ){
				$strEstadoCivil = 'SOLTERO';
			}elseif( $row['estado_civil'] == 2 ) {
				$strEstadoCivil = 'CASADO';
			}elseif( $row['estado_civil'] == 3 ) {
				$strEstadoCivil = 'VIUDO';
			}elseif( $row['estado_civil'] == 4 ) {
				$strEstadoCivil = 'DIVORCIADO';
			}elseif( $row['estado_civil'] == 5 ) {
				$strEstadoCivil = 'CONVIVIENTE';
			}elseif( $row['estado_civil'] == 6 ) {
				$strEstadoCivil = 'CONVIVIENTE NO REG.';
			}
			array_push($arrListado, 
				array(
					'idpariente' => $row['idpariente'],
					'idempleado' => $row['idempleado'],
					'pariente' => strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'nombres' => $row['nombres'],
					'ap_paterno' => $row['apellido_paterno'],
					'ap_materno' => $row['apellido_materno'],
					'parentesco' => $row['parentesco'],
					'direccion' => $row['direccion'],
					'telefono' => $row['telefono'],
					'fecha_nac' => darFormatoDMY($row['fecha_nacimiento']),
					'ocupacion' => $row['ocupacion'],
					'estado_civil_num' => $row['estado_civil'],
					'estado_civil' => $strEstadoCivil,
					'vive' => ($row['vive'] == 1) ? 'SI' : 'NO',
					'notificar_emergencia' => ($row['notificar_emergencia'] == 1) ? 'SI' : 'NO'
				)
			);
		}
	  	$arrData['datos'] = $arrListado;
	  	$arrData['paginate']['totalRows'] = $totalRows['contador'];
	  	$arrData['message'] = '';
	  	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
		// $arrConfigReceta = array(
		// 	'key_nombres_ap' => array( 
		// 		'x'=> 20,
		// 		'y'=> 10,
		// 		'w'=> 100
		// 	),
		// 	'key_dni' => array(
		// 		'value'=> 'juan perez',
		// 		'x'=> 20,
		// 		'y'=> 10,
		// 		'w'=> 100
		// 	)
		// )
		// '<div style="width:'.$arrConfigReceta['key_nombres_ap']['w'].'px"></div>'
	}
	public function agregar_pariente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se agregaron los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
		if( $this->model_pariente->m_agregar_pariente($allInputs) ){ 
			$arrData['message'] = 'Se agregaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_pariente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se editaron los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
		if( $this->model_pariente->m_editar_pariente($allInputs['datos']) ){ 
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_pariente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// foreach ($allInputs as $row) { 
		if( $this->model_pariente->m_anular_pariente($allInputs['idpariente']) ){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		// }
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}