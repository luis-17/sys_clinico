<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialContrato extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','imagen_helper','otros_helper'));
		$this->load->model(array('model_historial_contrato'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_historial_contratos()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_historial_contrato->m_cargar_historial_contratos($paramPaginate,$paramDatos);
		$totalRows = $this->model_historial_contrato->m_count_historial_contratos($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			$extensionFile = strtolower(pathinfo($row['nombre_archivo'], PATHINFO_EXTENSION)); 
			if($extensionFile == 'doc' || $extensionFile == 'docx' ){
				$strIcono = 'word-icon.png'; 
			}elseif($extensionFile == 'xls' || $extensionFile == 'xlsx' ){
				$strIcono = 'excel-icon.png'; 
			}elseif($extensionFile == 'jpg' || $extensionFile == 'png' || $extensionFile == 'jpeg' ){
				$strIcono = 'imagen-icon.png'; 
			}elseif($extensionFile == 'pdf' ){
				$strIcono = 'pdf-icon.png'; 
			}else{
				$strIcono = 'other-icon.png'; 
			}
			$hayArchivo = TRUE;
			if( empty($row['nombre_archivo']) ){
				$hayArchivo = FALSE;
			}
			array_push($arrListado,
				array(
					'codigo'=> $row['idhistorialcontrato'],
					'empresa'=> $row['razon_social'],
					'empresa_obj'=> array( 
						'id'=> $row['idempresaadmin'],
						'descripcion'=> $row['razon_social']
					),
					'idcargo'=> $row['idcargo'],
					'cargo'=> $row['descripcion_ca'],
					'cargo_obj'=> array( 
						'id'=> $row['idcargo'],
						'descripcion'=> $row['descripcion_ca']
					),
					'condicion_laboral'=> $row['condicion_laboral'],
					'condicion_laboral_obj'=> array( 
						'descripcion'=> $row['condicion_laboral'],
						'id'=> $row['condicion_laboral']
					), 
					//'fecha_ing_str'=> formatoFechaReporte3($row['fecha_ingreso']),
					'fecha_ing'=> darFormatoDMY($row['fecha_ingreso']),
					'fecha_cese'=> darFormatoDMY($row['fecha_cese']),
					//'fecha_ini_contrato_str'=> formatoFechaReporte3($row['fecha_inicio_contrato']),
					'fecha_ini_contrato'=> darFormatoDMY($row['fecha_inicio_contrato']),
					//'fecha_fin_contrato_str'=> formatoFechaReporte3($row['fecha_fin_contrato']),
					'fecha_fin_contrato'=> darFormatoDMY($row['fecha_fin_contrato']),
					'vigente'=> (int)$row['contrato_actual'],
					'vigente_string'=> ( $row['contrato_actual'] == 1 ? 'SI' : 'NO' ), 
					'sueldo'=> $row['sueldo_contrato'],
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono,
						'hay_archivo'=> $hayArchivo
					)
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
	}
	public function lista_historial_contratos_linea()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		if(!empty($allInputs['vigente'])){
			$paramDatos['contrato_actual'] = 1;
		}
		$lista = $this->model_historial_contrato->m_cargar_historial_contratos(FALSE,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			$extensionFile = strtolower(pathinfo($row['nombre_archivo'], PATHINFO_EXTENSION)); 
			if($extensionFile == 'doc' || $extensionFile == 'docx' ){
				$strIcono = 'word-icon.png'; 
			}elseif($extensionFile == 'xls' || $extensionFile == 'xlsx' ){
				$strIcono = 'excel-icon.png'; 
			}elseif($extensionFile == 'jpg' || $extensionFile == 'png' || $extensionFile == 'jpeg' ){
				$strIcono = 'imagen-icon.png'; 
			}elseif($extensionFile == 'pdf' ){
				$strIcono = 'pdf-icon.png'; 
			}else{
				$strIcono = 'other-icon.png'; 
			}
			$hayArchivo = TRUE;
			if( empty($row['nombre_archivo']) ){
				$hayArchivo = FALSE;
			}
			$classContratoActual = '';
			if( $row['contrato_actual'] == 1 ){ 
				$classContratoActual = 'selectedTL';
			}
			array_push($arrListado,
				array(
					'codigo'=> $row['idhistorialcontrato'],
					'empresa'=> $row['razon_social'],
					'empresa_obj'=> array( 
						'id'=> $row['idempresaadmin'],
						'descripcion'=> $row['razon_social']
					),
					'idcargo'=> $row['idcargo'],
					'cargo'=> $row['descripcion_ca'],
					'condicion_laboral'=> $row['condicion_laboral'],
					'fecha_ing_str'=> formatoFechaReporte3($row['fecha_ingreso']),
					'fecha_ini_contrato_str'=> formatoFechaReporte3($row['fecha_inicio_contrato']),
					'fecha_fin_contrato_str'=> formatoFechaReporte3($row['fecha_fin_contrato']),
					'fecha_ing'=> darFormatoDMY($row['fecha_ingreso']),
					'fecha_ini_contrato'=> darFormatoDMY($row['fecha_inicio_contrato']),
					'fecha_fin_contrato'=> darFormatoDMY($row['fecha_fin_contrato']),
					'vigente'=> (int)$row['contrato_actual'],
					'sueldo'=> $row['sueldo_contrato'],
					'clase_contrato_actual'=> $classContratoActual,
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono,
						'hay_archivo'=> $hayArchivo
					)
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
	public function ver_popup_historial_contrato()
	{
		$this->load->view('empleado/popupHistorialContrato_view');
	}
	public function ver_popup_subir_contrato()
	{
		$this->load->view('empleado/popupSubirContrato_view');
	}
	public function agregar_contrato()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se agregaron los datos correctamente';
    	$arrData['flag'] = 1;
    	

    	if( strtotime($allInputs['fecha_ini_contrato']) < strtotime($allInputs['fecha_ing']) ) {
    		$arrData['message'] = 'La fecha de inicio de contrato es menor a la fecha de ingreso.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(strtotime($allInputs['fecha_ini_contrato']) >= strtotime($allInputs['fecha_fin_contrato'])){
    		$arrData['message'] = 'La fecha de fin de contrato es menor a la fecha de inicio de contrato.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	// var_dump($allInputs); exit();
		$this->db->trans_start();
		if($this->model_historial_contrato->m_registrar($allInputs)){ 
			$id = GetLastId('idhistorialcontrato', 'rh_historial_contrato');
			if($allInputs['vigenteBool'] == 1){
				if(!$this->model_historial_contrato->m_actualizar_contratos_antiguos($allInputs, $id)){
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}			
			}

		}else{
			$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
			$arrData['flag'] = 0;
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_contrato()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Se agregaron los datos correctamente';
    	$arrData['flag'] = 1;

		if( strtotime($allInputs['fecha_inicio_contrato']) < strtotime($allInputs['fecha_ingreso']) ) {
    		$arrData['message'] = 'La fecha de inicio de contrato es menor a la fecha de ingreso.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(strtotime($allInputs['fecha_inicio_contrato']) >= strtotime($allInputs['fecha_fin_contrato'])){
    		$arrData['message'] = 'La fecha de fin de contrato es menor a la fecha de inicio de contrato.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	
    	if(!empty($allInputs['fecha_cese'])){    		
	    	if( strtotime($allInputs['fecha_cese']) < strtotime($allInputs['fecha_inicio_contrato']) ) {
	    		$arrData['message'] = 'La fecha de cese es menor a la fecha de inicio de contrato.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}

	    	if( strtotime($allInputs['fecha_cese']) < strtotime($allInputs['fecha_ingreso'])   ) {
	    		$arrData['message'] = 'La fecha de cese es menor a la fecha de ingreso.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
    	}


		$this->db->trans_start();
		if($this->model_historial_contrato->m_editar($allInputs)){
			if($allInputs['contrato_vigente'] == 1){
				if(!$this->model_historial_contrato->m_actualizar_contratos_antiguos($allInputs, $allInputs['codigo'])){
					$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
	    			$arrData['flag'] = 0;
				}			
			}
		}else{
			$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
	    	$arrData['flag'] = 0;
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function subir_archivo_contrato()
	{
		$allInputs['codigo'] = $this->input->post('codigo');
		$arrData['message'] = 'Error al subir los archivos, PESO MÁXIMO: 5MB';
    	$arrData['flag'] = 0;
    	if( empty($_FILES) ){
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	$extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    	$allInputs['nuevoNombreArchivo'] = 'Contrato_'.date('YmdHis').'.'.$extension;
		if( subir_fichero('assets/img/dinamic/empleado/documentacion/contratos/','archivo',$allInputs['nuevoNombreArchivo']) ){ 
			$allInputs['nombre_archivo'] = $_FILES['archivo']['name']; 
			if($this->model_historial_contrato->m_subir_documento_contrato($allInputs)){ 
				$arrData['message'] = 'Se subió el archivo correctamente.'; 
				$arrData['flag'] = 1; 
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function quitar_archivo_contrato()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_historial_contrato->m_quitar_documento_contrato($allInputs) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_historial_contrato->m_anular($allInputs['codigo']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}