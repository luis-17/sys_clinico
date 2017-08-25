<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contingencia extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_contingencia'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
	}
	public function lista_contingencia()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_contingencia->m_cargar_contingencia($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idcontingencia'],
					'descripcion' => strtoupper($row['descripcion_ctg'])
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
	public function lista_contingencia_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$lista = $this->model_contingencia->m_cargar_contingencia_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcontingencia'],
					'descripcion' => strtoupper($row['descripcion_ctg'])
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
	public function lista_contingencia_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_contingencia->m_cargar_contingencia_por_autocompletado($allInputs);
		}else{
			$lista = $this->model_contingencia->m_cargar_contingencia_por_autocompletado();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcontingencia'],
					'descripcion' => strtoupper($row['descripcion_ctg'])
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



/*	public function lista_procedimiento_de_especialidad_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_receta_medica->m_cargar_procedimientos_de_especialidad_session_autocomplete($allInputs['searchColumn'],$allInputs['searchText']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'], 
					'descripcion' => strtoupper($row['descripcion']) 
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
	public function registrar_receta_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		if( empty($allInputs['detalle']) ){ 
    		$arrData['message'] = 'No se ha agregado ningún medicamento, a la receta.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_receta_medica->m_registrar_receta_medica($allInputs) ) { 
			$allInputs['id'] = GetLastId('idreceta','receta');
			foreach ($allInputs['detalle'] as $row) { 
				$row['idreceta'] = $allInputs['id']; 
				if( $this->model_receta_medica->m_registrar_detalle_receta_medica($row) ){ 
					$arrData['message'] = 'Se registraron los datos correctamente'; 
					$arrData['flag'] = 1; 
				}
			}
		}else{ 
			$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
			$arrData['flag'] = 0; 
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_receta_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); //var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'No se pudieron eliminar los datos'; 
    	$arrData['flag'] = 0;
		if( $this->model_receta_medica->m_anular_medicamento_receta($allInputs['id']) ){ 
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
*/	// public function editar_cantidad_solicitud_procedimiento()
	// {
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
	// 	$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
 //    	$arrData['flag'] = 0;
 //    	$this->db->trans_start();
	// 	if( $this->model_receta_medica->m_editar_inline_solicitud_procedimiento($allInputs) ) { 
	// 		$arrData['message'] = 'Se registraron los datos correctamente'; 
	// 		$arrData['flag'] = 1; 
	// 	}else{ 
	// 		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
	// 		$arrData['flag'] = 0; 
	// 	} 
	// 	$this->db->trans_complete();
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
}