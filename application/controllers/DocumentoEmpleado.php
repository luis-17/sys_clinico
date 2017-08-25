<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DocumentoEmpleado extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper','fechas_helper'));
		$this->load->model(array('model_documento_empleado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_documentos_de_empleado()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		//var_dump("<pre>",$paramDatos); exit(); 
		$lista = $this->model_documento_empleado->m_cargar_documento_empleado($paramPaginate,$paramDatos);
		$totalRows = $this->model_documento_empleado->m_count_documento_empleado($paramPaginate,$paramDatos);
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
			array_push($arrListado, 
				array(
					'id' => $row['iddocumentoempleado'],
					'username' => $row['username'],
					'titulo' => strtoupper($row['titulo_doc']),
					'descripcion' => $row['descripcion_doc'],
					'fecha_entrega' => formatoFechaReporte($row['fecha_entrega']), 
					'fecha_subida' => formatoFechaReporte3($row['fecha_subida']),
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono
					)
					//'estado' => $row['estado_de']
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
		$this->load->view('documento-empleado/documentoEmpleado_formView');
	}
	// CV - CURRICULUM VITAE
	public function ver_popup_subir_cv()
	{
		$this->load->view('documento-empleado/popupSubirCV_view');
	}
	public function registrar()
	{
		$allInputs['titulo'] = $this->input->post('titulo');
		$allInputs['descripcion'] = $this->input->post('descripcion');
		$allInputs['fecha_entrega'] = $this->input->post('fecha_entrega');
		$allInputs['idempleado'] = $this->input->post('idempleado'); 

		$arrData['message'] = 'Error al subir los archivos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($_FILES) ){
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	//var_dump($_FILES['archivo']['error']); exit();
		if( subir_fichero('assets/img/dinamic/empleado/documentacion/','archivo') ){ 
			//var_dump($_FILES['archivo']['error']); var_dump('hjs'); exit();
			$allInputs['nombre_archivo'] = $_FILES['archivo']['name']; 
			if($this->model_documento_empleado->m_registrar($allInputs)){ 
				$arrData['message'] = 'Se subió el archivo correctamente.'; 
				$arrData['flag'] = 1; 
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
		if($this->model_documento_empleado->m_editar($allInputs)){
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
			if( $this->model_documento_empleado->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function guardar_cv()
	{
		$allInputs['idempleado'] = $this->input->post('idempleado'); 

		$arrData['message'] = 'Error al subir los archivos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($_FILES) ){
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}    	
    	//VALIDAMOS SI EL FICHERO CV ES DE TIPO WORD O PDF
    	$permitidos =  array('pdf', 'docx', 'docs', 'doc');
    	$fichero = $_FILES['archivo']['name'];
    	$extension = pathinfo($fichero, PATHINFO_EXTENSION);
    	if(!in_array($extension, $permitidos) ) {
    		$arrData['message'] = 'Los ficheros permitidos son .pdf y/o .docx';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	//VALIDAMOS SI EL FICHERO ES DE TAMAÑO MENOS A 5MB
    	$tamanioArchivo = $_FILES['archivo']['size']; // var_dump($_FILES['archivo']['size']); exit();
    	if ($tamanioArchivo > 5000000){ // 5MB = 5000000  bytes
    		$arrData['message'] = 'Los ficheros deben pesar como máximo 5mb';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	//VALIDAMOS SI EL EMPLEADO YA TIENE UN ARCHIVO CV SUBIDO AL SERVIDOR
    	$cvEnServidor = $this->model_documento_empleado->m_cargar_cv($allInputs);

    	if ($cvEnServidor && strlen($cvEnServidor) > 0) {
    		if (file_exists('assets/img/dinamic/empleado/documentacion/cv/' . $cvEnServidor)){//VERIFICAMOS SI EXISTE EL ARCHIVO EN EL SERVIDOR
    			//ELIMINAMOS EL ARCHIVO VIEJO PARA SUBIR UNO NUEVO
    			unlink('assets/img/dinamic/empleado/documentacion/cv/' . $cvEnServidor);	
    		}    		
    	}
    	//CREAMOS UN NUEVO NOMBRE DE ARCHIVO
    	$posNombreArchivo = 'CV' . date('_YmdHis') . '.' . $extension;
    	//COPIAMOS EL ARCHIVO AL SERVIDOR
		if( subir_fichero('assets/img/dinamic/empleado/documentacion/cv/', 'archivo', $posNombreArchivo) ){ 			
			$allInputs['nombre_archivo'] = $posNombreArchivo; 
			if($this->model_documento_empleado->m_registrar_cv($allInputs)){ 
				$arrData['message'] = 'Se subió el archivo correctamente.'; 
				$arrData['flag'] = 1; 
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}