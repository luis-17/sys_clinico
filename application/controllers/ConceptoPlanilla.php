<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConceptoPlanilla extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_concepto_planilla'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function lista_conceptos(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_concepto_planilla->m_cargar_conceptos($paramPaginate);
		$totalRows = $this->model_concepto_planilla->m_count_conceptos($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idconcepto'],
					'idconcepto' => $row['idconcepto'],
					'descripcion' => $row['descripcion_co'],
					'idcategoriaconcepto' => $row['idcategoriaconcepto'],
					'nivel_reporte' => $row['nivel_reporte'],
					'si_snp' => ($row['si_snp'] == 0) ? FALSE : TRUE ,
					'si_spp' => ($row['si_spp'] == 0) ? FALSE : TRUE ,
					'si_5cat' => ($row['si_5cat'] == 0) ? FALSE : TRUE ,
					'si_essalud' => ($row['si_essalud'] == 0) ? FALSE : TRUE ,
					'si_sctr' => ($row['si_sctr'] == 0) ? FALSE : TRUE ,
					'si_senati' => ($row['si_senati'] == 0) ? FALSE : TRUE ,
					'es_calculable' => ($row['es_calculable'] == 0) ? FALSE : TRUE ,
					'abreviatura' => $row['abreviatura'],
					'formula' => $row['formula'],
					'codigo_plan' => $row['codigo_plan'],
					'estado_co' => $row['estado_co'],
					'descripcion_categoria_concepto' => $row['categoria_concepto'],
					'tipo_concepto' => $row['tipo_concepto'],
					'codigo_plame' => $row['codigo_plame'],
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
	public function lista_conceptos_agregados(){
	  $allInputs = json_decode(trim(file_get_contents('php://input')),true);
	  //var_dump($allInputs);exit();
	  $paramPaginate = $allInputs['paginate'];
	  $datos = $allInputs['datos'][0];
	  $cat_concepto = $allInputs['cat_concepto'];
	  $lista = $this->model_concepto_planilla->m_cargar_conceptos_agregados($paramPaginate,$datos['id'],$cat_concepto);
	  $totalRows = $this->model_concepto_planilla->m_count_conceptos_agregados($paramPaginate,$datos['id'],$cat_concepto);
	  $arrListado = array();
	  
	  foreach ($lista as $row) {
	  	if( $row['estado_pc'] == '1' ){
			$objEstado['claseSwitch'] = 'success';
			$objEstado['labelText'] = 'HABILITADO';
			$objEstado['boolBloqueo'] = TRUE;
		}else{
			$objEstado['claseSwitch'] = 'danger';
			$objEstado['labelText'] = 'DESHABILITADO';
			$objEstado['boolBloqueo'] = FALSE;
		}
	    array_push($arrListado, 
	      array(
	      	'id' => $row['idplanillaconcepto'],
	        'codigo_plame' => $row['codigo_plame'],
	        'idconcepto' => $row['idconcepto'],
	        'descripcion' => $row['descripcion_co'],
	        'valor_referencial' => $row['valor_referencial'],
	        'estado' => $row['estado_pc'],
	        'estado_bloq' => $objEstado,
	        
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
	public function lista_conceptos_no_agregados(){
	  $allInputs = json_decode(trim(file_get_contents('php://input')),true);
	  $paramPaginate = $allInputs['paginate'];
	  $datos = $allInputs['datos'][0];
	  $cat_concepto = $allInputs['cat_concepto'];
	  $lista = $this->model_concepto_planilla->m_cargar_conceptos_no_agregados($paramPaginate,$datos['id'],$cat_concepto);
	  $totalRows = $this->model_concepto_planilla->m_count_conceptos_no_agregados($paramPaginate,$datos['id'],$cat_concepto);
	  $arrListado = array();
	  foreach ($lista as $row) {
	    array_push($arrListado, 
	      array(
	        'codigo_plame' => $row['codigo_plame'],
	        'id' => $row['idconcepto'],
	        'descripcion' => $row['descripcion_co'],
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
	public function ver_popup_formulario(){
		$this->load->view('concepto-planilla/concepto_formView');
	}
	public function ver_popup_concepto_planilla(){
		$this->load->view('concepto-planilla/conceptoPlanilla_formView');
	}
	public function registrar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();

    	if($allInputs['categoria_concepto']['tipo_concepto'] != 1){
    		$allInputs['si_snp'] = 0;
	    	$allInputs['si_spp'] = 0;
	    	$allInputs['si_5cat'] = 0;
	    	$allInputs['si_essalud'] = 0;
	    	$allInputs['si_sctr'] = 0;
	    	$allInputs['si_senati'] = 0;
    	}   	

		if($this->model_concepto_planilla->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
		    $arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	if($allInputs['categoria_concepto']['tipo_concepto'] != 1){
    		$allInputs['si_snp'] = 0;
	    	$allInputs['si_spp'] = 0;
	    	$allInputs['si_5cat'] = 0;
	    	$allInputs['si_essalud'] = 0;
	    	$allInputs['si_sctr'] = 0;
	    	$allInputs['si_senati'] = 0;
    	}   

		if($this->model_concepto_planilla->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
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
			if( $this->model_concepto_planilla->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_concepto(){
	    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
	    $arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		$arrData['flag'] = 0;
		$result = $this->model_concepto_planilla->m_consultar_concepto($allInputs);
		
		if(!empty($result)){ // si ya existe la relacion pero está anulado activamos esa relación para no generar una nueva.
			if($this->model_concepto_planilla->m_quitar_activar_concepto($result['idplanillaconcepto'], 1)){
				$arrData['message'] = 'Se agregó el concepto correctamente';
			  	$arrData['flag'] = 1;
			}	
		}else{
			$this->db->trans_start();
			if($this->model_concepto_planilla->m_agregar_concepto($allInputs)){
			    $arrData['message'] = 'Se agregó el concepto correctamente';
			    $arrData['flag'] = 1;
			}
			$this->db->trans_complete();
		}
	    
	    $this->output
	        ->set_content_type('application/json')
	        ->set_output(json_encode($arrData));
	}
	public function quitar_concepto(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo quitar el cocepto';
		$arrData['flag'] = 0;

		if($this->model_concepto_planilla->m_quitar_activar_concepto($allInputs['id'], 0) ){	
			$arrData['message'] = 'Se quitaron los datos correctamente';
			$arrData['flag'] = 1;	
		}

		$this->output
		  ->set_content_type('application/json')
		  ->set_output(json_encode($arrData));
	}

	public function bloquea_desbloquea_concepto()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ocurrió un error, inténtelo nuevamente';
    	$arrData['flag'] = 0;

		if( $allInputs['estado'] == 1 ){
			if($this->model_concepto_planilla->m_quitar_activar_concepto($allInputs['id'], 2)){
				$arrData['message'] = 'Por defecto el estado del concepto estará habilitado';
			}	
		}else{
			if($this->model_concepto_planilla->m_quitar_activar_concepto($allInputs['id'], 1)){
				$arrData['message'] = 'Por defecto el estado del concepto estará deshabilitado';				
			}
		}
		
		$arrData['flag'] = 1;
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function actualizar_valor_referencial(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo actualizar los datos';
		$arrData['flag'] = 0;

		if($this->model_concepto_planilla->m_actualizar_valor_referencial($allInputs) ){	
			$arrData['message'] = 'Se actualizó el dato correctamente';
			$arrData['flag'] = 1;	
		}

		$this->output
		  ->set_content_type('application/json')
		  ->set_output(json_encode($arrData));
	}
}
?>