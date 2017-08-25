<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpresaHistorialContrato extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper(array('security','imagen_helper', 'otros_helper', 'fechas_helper'));
		$this->load->model(array('model_empresa_historial_contrato', 'model_empresa'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function agregar_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos. Por favor intente nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($allInputs['codigo']) || $allInputs['codigo'] == NULL){
    		$arrData['message'] = 'Debe ingresar el codigo del contrato. Por favor intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	if( empty($allInputs['fecha_inicio']) || $allInputs['fecha_inicio'] == NULL  ||  empty($allInputs['fecha_fin']) || $allInputs['fecha_fin'] == NULL ){
    		$arrData['message'] = 'Debe ingresar ambas fechas. Por favor intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}    	

		$this->db->trans_start();
		$datos = array(
			'idempresadetalle' => $allInputs['idempresadetalle'],
			'nuevo_estado' => $allInputs['contrato_formal'],
		);
		if($this->model_empresa_historial_contrato->m_agregar_contrato($allInputs) && $this->model_empresa->m_cambiar_estado_contrato($datos)){
			if($allInputs['contrato_actual']==1){
				$allInputs['idempresahistorialcontrato'] = GetLastId('idempresahistorialcontrato','pa_empresa_historial_contrato');
				if($this->model_empresa_historial_contrato->m_update_contrato_actual($allInputs)){
					$arrData['message'] = 'Se registró el contrato correctamente.'; 
					$arrData['flag'] = 1;
				}
			}else{
				$arrData['message'] = 'Se registró el contrato correctamente.'; 
				$arrData['flag'] = 1;
			}			 
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));			
	}

	public function lista_historial_contratos_linea(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_empresa_historial_contrato->m_cargar_historial_contratos(FALSE,$paramDatos);
		$arrListado = array();
		foreach ($lista as $key => $row) {
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
					'idcontrato'=> $row['idempresahistorialcontrato'],
					'idempresadetalle'=> $row['idempresadetalle'],
					'empresa'=> $row['razon_social'],
					'fecha_registro_str'=> formatoFechaReporte3($row['fecha_registro']),
					'fecha_inicio_str'=> formatoFechaReporte3($row['fecha_inicio']),
					'fecha_fin_str'=> formatoFechaReporte3($row['fecha_fin']),
					'fecha_inicio'=> date('d-m-Y', strtotime($row['fecha_inicio'])),
					'fecha_fin'=> date('d-m-Y', strtotime($row['fecha_fin'])),
					'contrato_actual'=> (int)$row['contrato_actual'],
					'clase_contrato_actual'=> $classContratoActual,
					'contrato_formal'=> (int)$row['tiene_contrato'] ,
					'condiciones' => $row['condiciones'] ,
					'codigo' => $row['codigo'] ,
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono,
						'hay_archivo'=> $hayArchivo
					),
					'item' => $key+1
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

	public function lista_historial_adendas_contratos_linea(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_empresa_historial_contrato->m_cargar_historial_adendas_contratos($allInputs);
		$totalRows = $this->model_empresa_historial_contrato->m_count_historial_adendas_contratos($allInputs);
		$arrListado = array();
		foreach ($lista as $key => $row) {
			array_push($arrListado,
				array(
					'idadenda'=> $row['idadenda'],
					'idempresahistorialcontrato'=> $row['idempresahistorialcontrato'],
					'fecha_fin'=> darFormatoDMY($row['fecha_fin']),
					'condiciones' => $row['condiciones']
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
	public function editar_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos. Por favor intente nuevamente';
    	$arrData['flag'] = 0;

    	if( empty($allInputs['fecha_inicio']) || $allInputs['fecha_inicio'] == NULL  ||  empty($allInputs['fecha_fin']) || $allInputs['fecha_fin'] == NULL ){
    		$arrData['message'] = 'Debe ingresar ambas fechas. Por favor intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	$this->db->trans_start();
		if($this->model_empresa_historial_contrato->m_editar_contrato($allInputs)){
			$this->model_empresa_historial_contrato->m_editar_detalle_contrato($allInputs);
			if($allInputs['contrato_actual']==1){
				$allInputs['idempresahistorialcontrato'] = $allInputs['idcontrato'];
				//print_r($allInputs);
				if($this->model_empresa_historial_contrato->m_update_contrato_actual($allInputs)){
					$arrData['message'] = 'Se editó el contrato correctamente.'; 
					$arrData['flag'] = 1;
				}
			}else{
				$arrData['message'] = 'Se editó el contrato correctamente.'; 
				$arrData['flag'] = 1;
			}			 
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular el contrato. Por favor intente nuevamente';
    	$arrData['flag'] = 0;
    	    	
		if($this->model_empresa_historial_contrato->m_anular_contrato($allInputs)){ 			
			$arrData['message'] = 'Se anuló el contrato correctamente.'; 
			$arrData['flag'] = 1;		 
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
			
	}

	public function subir_archivo_contrato(){
		$allInputs['idcontrato'] = $this->input->post('idcontrato');
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
		if( subir_fichero('assets/img/dinamic/ema/contratos/','archivo',$allInputs['nuevoNombreArchivo']) ){ 
			$allInputs['nombre_archivo'] = $_FILES['archivo']['name']; 
			//print_r($allInputs);
			if($this->model_empresa_historial_contrato->m_subir_documento_contrato($allInputs)){ 
				$arrData['message'] = 'Se subió el archivo correctamente.'; 
				$arrData['flag'] = 1; 
			}
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	

	public function quitar_archivo_contrato(){
		$idcontrato = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudieron eliminar los datos';
    	$arrData['flag'] = 0;
		if( $this->model_empresa_historial_contrato->m_quitar_documento_contrato($idcontrato) ){
			$arrData['message'] = 'Se eliminó el archivo correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function agregar_adenda_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos. Por favor intente nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($allInputs['fecha_fin']) || $allInputs['fecha_fin'] == NULL ){
    		$arrData['message'] = 'Debe ingresar la fecha de la adenda. Por favor intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

		$this->db->trans_start();

		if($this->model_empresa_historial_contrato->m_agregar_adenda($allInputs)){
			$arrData['message'] = 'Se registró la Adenda correctamente.'; 
			$arrData['flag'] = 1;
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));			
	}

	public function editar_adenda_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos. Por favor intente nuevamente';
    	$arrData['flag'] = 0;

    	if(empty($allInputs['fecha_fin']) || $allInputs['fecha_fin'] == NULL ){
    		$arrData['message'] = 'Debe ingresar la fecha de la Adenda. Por favor intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	$this->db->trans_start();
		if($this->model_empresa_historial_contrato->m_editar_adenda($allInputs)){
			$arrData['message'] = 'Se editó la Adenda correctamente.'; 
			$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_adenda_contrato_empresa(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$arrData['message'] = 'Error al anular el contrato. Por favor intente nuevamente';
    	//$arrData['flag'] = 0;
    	    	
		//if($this->model_empresa_historial_contrato->m_anular_adenda($allInputs)){ 			
		//	$arrData['message'] = 'Se anuló el contrato correctamente.'; 
		//	$arrData['flag'] = 1;		 
		//}
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_empresa_historial_contrato->m_anular_adenda($row['idadenda']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
			
	}

}