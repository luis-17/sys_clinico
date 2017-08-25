<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionSaludOcup extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','imagen_helper'));
		$this->load->model(array('model_atencion_salud_ocup','model_trabajador_perfil','model_venta'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function listar_ventas_salud_ocupacional()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_atencion_salud_ocup->m_cargar_ventas_salud_ocup($paramDatos,$paramPaginate);
		$totalRows = $this->model_atencion_salud_ocup->m_count_ventas_salud_ocup($paramDatos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( empty($row['nombre_archivo_so']) ){
				$row['nombre_archivo_so'] = FALSE;
				$strIcono = FALSE;
			}else{
				$extensionFile = strtolower(pathinfo($row['nombre_archivo_so'], PATHINFO_EXTENSION)); 
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
			}
			array_push($arrListado,
				array(
					'iddetalle' => $row['iddetalle'],
					'idventa' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'fecha_venta' => formatoFechaReporte3($row['fecha_venta']),
					'tipodocumento' => $row['descripcion_td'],
					'ticket' => $row['ticket_venta'],
					'empresa' => $row['empresa'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'cantidad' => $row['cantidad'],
					'precio_unit' => $row['precio_unitario'],
					'total_detalle' => $row['total_detalle'],
					'informe_texto_so' => $row['informe_texto_so'],
					'nombre_archivo_so' => $row['nombre_archivo_so'],
					'archivo' => array(
						'documento'=> $row['nombre_archivo_so'],
						'icono'=> $strIcono
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
	public function listar_atenciones_de_perfil_salud_ocupacional()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_atencion_salud_ocup->m_cargar_atenciones_perfiles_salud_ocupacional($paramDatos,$paramPaginate);
		$totalRows = $this->model_atencion_salud_ocup->m_count_atenciones_perfiles_salud_ocupacional($paramDatos,$paramPaginate);

		$arrListado = array();
		foreach ($lista as $row) { 
			if( empty($row['nombre_archivo']) ){
				$row['nombre_archivo'] = FALSE;
				$strIcono = FALSE;
			}else{
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
			}
			array_push($arrListado,
				array(
					'idatencionocupacional' => $row['idatencionocupacional'],
					'iddetalle' => $row['iddetalle'],
					'cliente' => $row['cliente'],
					'fecha_atencion' => formatoFechaReporte3($row['fecha_atencion']),
					'informe' => $row['informe'],
					'nombre_archivo' => $row['nombre_archivo'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'empresa' => $row['empresa'],
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono
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
	public function ver_popup_formulario()
	{
		$this->load->view('salud-ocupacional/atencionSaludOcup_formView');
	}
	public function ver_popup_subir_informe_general()
	{
		$this->load->view('salud-ocupacional/informeGeneralSaludOcup_formView');
	}
	public function registrar()
	{
		$allInputs['idmedico'] = $this->input->post('idmedico');
		$allInputs['informe_texto'] = $this->input->post('informe_texto');
		$allInputs['idventa'] = $this->input->post('idventa');
		$allInputs['iddetalle'] = $this->input->post('iddetalle');
		$allInputs['idproductomaster'] = $this->input->post('idproductomaster');
		$allInputs['idempresacliente'] = $this->input->post('idempresacliente');
		$arrCliente = json_decode($this->input->post('cliente'),TRUE);

		$fProductoCliente = $this->model_trabajador_perfil->m_validar_trabajador_en_perfil($arrCliente['id'],$allInputs['idproductomaster'],$allInputs['idempresacliente']); 
		if(empty($fProductoCliente)){ 
			$arrData['message'] = 'El trabajador seleccionado no está asociado al perfil: '.strtoupper($this->input->post('perfil'));
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		$allInputs['idproductocliente'] = $fProductoCliente['idproductocliente'];
		$arrData['message'] = 'Error al subir los archivos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($_FILES) && empty($allInputs['informe_texto']) ){ 
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	if( !empty($_FILES) ){ 
    		$img_type = $_FILES['archivo']['type'];
	        if ( !(strpos($img_type, "pdf")) ){ 
	        	$arrData['message'] = 'Sólo se aceptan archivos en PDF.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
	        }
    	}
    	if( empty($_FILES) ){ 
    		$allInputs['nombre_archivo'] = NULL;
    		if($this->model_atencion_salud_ocup->m_registrar($allInputs)){ 
				$arrData['message'] = 'Se registró la atencion correctamente.'; 
				$arrData['flag'] = 1; 
			}
    	}else{ 
    		$posNombreArchivo = date('_YmdHis').'.pdf';
			if( subir_fichero_solo_PDF('assets/img/dinamic/saludOcupacional/informesPorPaciente/','archivo',$posNombreArchivo) ){ 
				$allInputs['nombre_archivo'] = $_FILES['archivo']['name'].$posNombreArchivo; 
				if($this->model_atencion_salud_ocup->m_registrar($allInputs)){ 
					$arrData['message'] = 'Se registró la atencion correctamente.'; 
					$arrData['flag'] = 1; 
				}
			}
    	}
        
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function actualizar_informe_general()
	{
		// $allInputs['idmedico'] = $this->input->post('idmedico');
		// var_dump($this->sessionHospital); exit(); 
		$allInputs['informe_texto'] = $this->input->post('informe_texto');
		$allInputs['idventa'] = $this->input->post('idventa');

		$arrData['message'] = 'Error al subir los archivos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( empty($_FILES) && empty($allInputs['informe_texto']) ){ 
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	if( !empty($_FILES) ){ 
    		$img_type = $_FILES['archivo']['type'];
	        if ( !(strpos($img_type, "pdf")) ){ 
	        	$arrData['message'] = 'Sólo se aceptan archivos en PDF.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
	        }
    	}
    	$this->db->trans_start();
    	if( empty($_FILES) ){ 
    		$allInputs['nombre_archivo'] = NULL;
    		if($this->model_atencion_salud_ocup->m_actualizar_informe_general($allInputs)){ 
				$arrData['message'] = 'Se agregó el informe correctamente.'; 
				$arrData['flag'] = 1; 
			}
    	}else{ 
    		$posNombreArchivo = date('_YmdHis').'.pdf';
			if( subir_fichero_solo_PDF('assets/img/dinamic/saludOcupacional/informesPorVenta/','archivo',$posNombreArchivo) ){ 
				$allInputs['nombre_archivo'] = $_FILES['archivo']['name'].$posNombreArchivo; 
				if($this->model_atencion_salud_ocup->m_actualizar_informe_general($allInputs)){ 
					$arrData['message'] = 'Se subió el informe correctamente.'; 
					$arrData['flag'] = 1; 
				}
			}
    	}
    	if($arrData['flag'] == 1){ 
    		// REGISTRAR LA ATENCION MÉDICA 
    		// if($this->model_atencion_medica->m_registrar_atencion_medica($allInputs)){
				// AGREGAR A LA PRODUCCION DE S.O 
			$this->model_venta->m_actualizar_venta_a_atendido($allInputs['idventa']); 
			$this->model_venta->m_actualizar_detalle_venta_a_atendido_desde_venta($allInputs['idventa']); 
			$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['idventa']); /* IMPORTANTE */ 
    		// }

    	}
    	$this->db->trans_complete();
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
			if( $this->model_atencion_salud_ocup->m_anular($row['idatencionocupacional']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
}
?>