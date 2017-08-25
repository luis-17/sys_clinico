<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Venta extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_venta','model_caja','model_config','model_venta_farmacia', 'model_prog_cita','model_prog_medico','model_atencionMuestra', 'model_cliente'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function ver_popup_detalle_venta()
	{
		$this->load->view('caja/popupVerDetalleVenta');
	}
	public function ver_popup_agregar_campania()
	{
		$this->load->view('campania/popupAgregarCampaniaVenta');
	}
	public function ver_popup_agregar_solicitud()
	{
		$this->load->view('venta/popupAgregarSolicitudVenta');
	}
	public function ver_popup_impresion_ticket_manual()
	{
		$this->load->view('venta/popupImpresionTicketManual');
	}
	public function ver_popup_agregar_cita()
	{
		$this->load->view('venta/popupAgregarCita');
	}
	public function ver_popup_agregar_cita_proc()
	{
		$this->load->view('venta/popupAgregarCitaProc');
	}
	public function ver_popup_seleccionar_cita(){
		$this->load->view('venta/popupSeleccionarCita');
	}	
	public function ver_popup_detalle_cita(){
		$this->load->view('venta/popupDetalleCita');
	}
	public function lista_ventas_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_ventas_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_sum_ventas_caja_actual($paramPaginate,$paramDatos); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$strMedico = trim($row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno']);
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			$objEstado = array();
			$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['estado'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'VENDIDO';
			}
			if( $row['estado'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-exclamation';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NOTA DE CREDITO';
			}

			if( $row['paciente_atendido_v'] == 1 ){ 
				$objEstado['claseIconAtendido'] = 'fa-thumbs-up'; 
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'idespecialidad' => $row['idespecialidad'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
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
	public function lista_detalle_venta_caja_actual() 
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_detalle_venta_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_detalle_venta_caja_actual($paramPaginate,$paramDatos); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					//'idcliente' => $row['idcliente'],
					//'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					// 'numero_documento' => $row['num_documento'],
					'idproducto' => $row['idproductomaster'],
					'producto' => $row['producto'],
					// 'precio' => $row['precio'],
					// 'idempresaespecialidad' => $row['idempresaespecialidad'],
					// 'idempresa' => $row['idempresa'],
					// 'empresa' => $row['empresa'], // EMPRESA DE ESPECIALIDAD 
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'total_detalle' => $row['total_detalle'],
					'username' => strtoupper($row['username']),
					'numero_caja' => $row['numero_caja'],
					'serie_caja' => $row['serie_caja']
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
    	$arrData['datos'] = $arrListado;
    	//$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
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
	public function lista_ventas_anulados_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_ventas_anuladas_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_sum_ventas_anuladas_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		$sumTotal = 0;
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar']
				)
			);
			$sumTotal += $row['total_a_pagar'];
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = $totalRows['sumatotal'];
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
	public function lista_ventas_en_espera_caja_actual()
	{
		//var_dump('venta'); exit(); 
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_ventas_en_espera_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_ventas_en_espera_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar']
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
	public function lista_ventas_con_descuento_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_ventas_con_descuento_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_ventas_con_descuento_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			$objEstado = array();
			if( $row['estado'] == 2 ){ // ENTRANTE CON DSCTO 
				$objEstado['claseIcon'] = 'fa-spinner fa-spin';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR APROBAR';
			}
			if( $row['estado'] == 1 ){ // APROBADO CON DSCTO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'APROBADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
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
	public function lista_ventas_con_solicitud_impresion_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			$objEstado = array();
			if( $row['solicita_impresion'] == 1 ){ // SOLICITUD DE IMPRESION ENVIADA  
				$objEstado['claseIcon'] = 'fa-spinner fa-spin';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR APROBAR';
			}
			if( $row['solicita_impresion'] == 3 ){ // SOLICITUD DE IMPRESION APROBADA 
				$objEstado['claseIcon'] = 'fa-print';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'APROBADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
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
	public function lista_productos_venta_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta->m_cargar_producto_venta_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta->m_count_producto_venta_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno']; 
			array_push($arrListado, 
				array( 
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idproducto' => $row['idproductomaster'],
					'producto' => $row['producto'],
					// 'precio' => $row['precio'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipoproducto' => $row['nombre_tp'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'total_detalle' => $row['total_detalle'],
					'username' => strtoupper($row['username']),
					'numero_caja' => $row['numero_caja'],
					'serie_caja' => $row['serie_caja'],
					'empresa_admin' => $row['empresa_admin']
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
	public function listar_ordenes_ventas_cerradas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_venta->m_cargar_ordenes_venta_cajas_cerradas($allInputs);
		}else{
			$lista = $this->model_venta->m_cargar_ordenes_venta_cajas_cerradas();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idventa'], 
					'ticket' => $row['ticket_venta'], 
					'orden' => $row['orden_venta'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'], 
					'saldo' => $row['total_a_pagar'],
					'saldo_format' => $row['total_a_pagar_format']
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
	/* SOLICITUDES DE PRODUCTOS */
	public function listar_solicitudes_de_historia()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump( $allInputs ); exit(); 
		// if( $allInputs['datos']['tipoSolicitud'] === 'ALL' ){
		// 	$allInputs['datos']['idtipoproducto'];
		// }else
		$lista = array(); 
		if ( $allInputs['datos']['tipoSolicitud'] === 'EA' ) {
			$allInputs['datos']['idtipoproductos'] = array(11,14,15); // IMAGENOLOGIA 
			$lista = $this->model_solicitud_examen->m_cargar_solicitudes_examen_auxiliar($allInputs['datos']); 
		}elseif ( $allInputs['datos']['tipoSolicitud'] === 'PR' ) {
			$allInputs['datos']['idtipoproductos'] = array(16); // PROCEDIMIENTO 
			$lista = $this->model_solicitud_procedimiento->m_cargar_solicitudes_procedimiento($allInputs['datos']); 
		}elseif ( $allInputs['datos']['tipoSolicitud'] === 'DO' ) {
			$allInputs['datos']['idtipoproductos'] = array(13); // DOCUMENTO
			$lista = $this->model_solicitud_documento->m_cargar_solicitudes_documento($allInputs['datos']); 
		}

		//$lista = $this->model_venta->m_cargar_solicitudes_venta($allInputs['datos']);
		$arrListado = array(); 
		if(!empty($lista)){
			foreach ($lista as $row) { 
				array_push($arrListado, 
					array( 
						'id' => $row['iddetallepaquete'],
						'idpaquete' => $row['idpaquete'],
						'paquete' => $row['paquete'],
						'idtipocampania' => $row['idtipocampania'],
						'tipocampania' => $row['tipo_campania'],
						'idcampania' => $row['idcampania'],
						'campania' => $row['campania'],
						'idproductomaster' => $row['idproductomaster'],
						'producto' => $row['producto'],
						'precio' => $row['precio'],
						'idespecialidad' => $row['idespecialidad'],
						'especialidad' => $row['nombre']
					)
				);
			}
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
	public function listar_esta_venta_por_id()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fVenta = $this->model_venta->m_cargar_esta_venta_por_id($allInputs['id']);
		$arrData['datos'] = array();
		$arrData['message'] = '';
		if(empty($fVenta)){ 
			$arrData['flag'] = 0;
		}else{
			$arrData['flag'] = 1;
			if( $fVenta['estado'] == 2 ) { // EN ESPERA 
				$arrData['flag'] = 0;
			}
	    	$arrData['datos'] = $fVenta;
	    	$arrData['message'] = '';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function registrar_venta()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// $allInputs['tiene_descuento'] = 2;
		
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idventaregister'] = NULL;
    	if(empty($allInputs['pacienteExterno'])){
    		$allInputs['pacienteExterno'] = 'false';
    	}
    	// VALIDAR ORDEN DE VENTA 
    	$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs['orden']
    	);
    	if( empty($allInputs['ticket']) ){ 
    		$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['cliente']) && $allInputs['detalle'][0]['idespecialidad'] != 39 ){ // SI NO ES SALUD OCUPACIONAL 
    		$arrData['message'] = 'Seleccione un DNI válido de cliente.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $allInputs['detalle'][0]['idespecialidad'] == 39 && empty($allInputs['empresa']['id']) ){
    		$arrData['message'] = 'Seleccione Empresa/Cliente de Salud Ocupacional';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$fVenta = $this->model_venta->m_cargar_esta_venta_por_columna($arrFilters);
    	if( !empty($fVenta) ){
    		$arrData['message'] = 'Ya se a registrado una venta, usando la orden <strong>'.$allInputs['orden'].'</strong>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( !($allInputs['pacienteExterno'] == 'true') ){ // SI NO HA MARCADO EL CHECK 
    		if( empty($allInputs['medico']) ){ 
				$arrData['message'] = 'Registre al médico que solicitó la orden, o de lo contrario seleccione "Paciente Externo"';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
    		}
    	}
		// VALIDAR QUE NO PERMITA GRABAR, SI NO TIENE NUMERO DE CELULAR 
    	$arrCliente = array(
    		'idcliente'=> $allInputs['cliente']['id']
    	);
    	$fCliente = $this->model_cliente->m_cargar_este_cliente($arrCliente); 
    	// var_dump($fCliente); exit();
		if( empty($fCliente[0]['celular']) || !(strlen($fCliente[0]['celular']) == 9) ){ 
			$arrData['message'] = 'No se digitó correctamente el número de celular del paciente.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		// VALIDACION VENTA FECHAS MENOR A LA FECHA ACTUAL
		foreach ($allInputs['detalle'] as $key => $row) {
    		if( !empty($row['detalleCupo']) ){ 
    			if(date('d-m-Y',strtotime($row['detalleCupo']['fecha_programada'])) < date('d-m-Y')){
					$arrData['message'] = 'Las fechas de venta no pueden ser menor a la fecha de hoy.';
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return; 
	    		}	
    		}
    	}  

    	$countMP = 0;
    	$multipleProgramacion = FALSE; 
    	$diferenteEspecialidad = FALSE;
    	$cantidadNoValida = FALSE;
    	$cupoNoGenerado = FALSE;
    	$cupoNoDisponible = FALSE;
    	$cupoProcedimiento = FALSE;
    	foreach ($allInputs['detalle'] as $row) { 
   			if( $allInputs['detalle'][0]['idespecialidad'] !== $row['idespecialidad'] ) { 
   				$diferenteEspecialidad = TRUE;
   			}
   			// PARA LABORATORIO NO SE PERMITE CANTIDAD MAYOR DE 1
   			if( $row['idespecialidad'] == 21 && $row['cantidad'] > 1 ){
   				$cantidadNoValida = TRUE;
   			}

   			if($row['producto']['idtipoproducto'] == 12 && $row['cantidad'] > 1 ){
   				$cantidadNoValida = TRUE;
   			}
   			
   			if($row['producto']['idtipoproducto'] == 16 && empty($row['detalleCupo']) && $row['tiene_prog_proc'] == 1 && $row['tiene_venta_prog_proc'] == 1){
   				$cupoProcedimiento = TRUE;
   			}

   			if( $row['producto']['idtipoproducto'] == 12 && $row['tiene_prog_cita'] == 1 && $row['tiene_venta_prog_cita'] == 1){ 
   				if($row['tiene_cupo'] ){
   					// validacion de cupo disponible
   					$cupo = $this->model_prog_medico->m_consulta_cupo($row['detalleCupo']['iddetalleprogmedico']);
   					if( $cupo['estado_cupo'] != 2 ){
   						$cupoNoDisponible = TRUE;
   					}
   				}else{
   					$cupoNoGenerado = TRUE;
   				}
   			}
   			if( $row['tiene_cupo'] == TRUE ){
   				$countMP++;
   			}
    	} 
    	// var_dump($countMP,$cupoNoGenerado,$cupoProcedimiento); exit(); 
    	// SOLO SI TIENE PROGRAMACIÓN ASISTENCIAL, NO SE PUEDE AGREGAR DOS PROGRAMACIONES EN UN MISMO TICKET 
		if( $countMP > 1 ){ 
			$arrData['message'] = 'Solo se puede agregar una programacion en el ticket. Si hay varias programaciones, genere un ticket por cada uno de ellas';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData)); 
		    return;
		}
    	if( $cupoProcedimiento ){ 
    		$arrData['message'] = 'Debe generar cupo para el procedimiento seleccionado.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( $diferenteEspecialidad === TRUE ){ 
    		$arrData['message'] = 'No se permite registrar diferentes especialidades en un ticket.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// var_dump('fufufy'); exit();
    	if( $cantidadNoValida ){ 
    		$arrData['message'] = 'No se permite cantidad mayor de 1.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	
    	/*Validar que se haya generado cupo */ 
    	if( $cupoNoGenerado ){ 
    		$arrData['message'] = 'Debe generar cupo para las consultas indicadas.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	/*Validar si el cupo está disponible*/
		if( $cupoNoDisponible ){ 
    		$arrData['message'] = 'El cupo ya no está disponible, elija otro por favor.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	/* Validar el numero de ticket sea correlativo */
    	$numeroDeSerieValido = FALSE; 
    	$fNumeroSerie = $this->model_caja->m_cargar_caja_por_este_numero_serie($allInputs['idcajamaster'],$allInputs['idtipodocumento']);
    	$numeroSeriePad = str_pad(($fNumeroSerie['numero_serie'] + 1), 9, '0', STR_PAD_LEFT);
    	$serieActual = $fNumeroSerie['serie_caja'].'-'.$numeroSeriePad; 
    	if( $serieActual === $allInputs['ticket'] && $allInputs['numero_serie'] === $fNumeroSerie['numero_serie'] ){ 
    		$numeroDeSerieValido = TRUE; 
    	}
    	if( !$numeroDeSerieValido ){ 
    		$arrData['message'] = 'El número de serie es erróneo, por favor refresque el formulario <span class="icon-bg"><i class="ti ti-reload"></i></span>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// var_dump($allInputs['pacienteExterno']); exit(); 
    	if($allInputs['pacienteExterno'] == 'true'){ 
    		$allInputs['pacienteExterno'] = 'SI';
    	}else{ 
    		$allInputs['pacienteExterno'] = 'NO';
    	}
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_venta->m_registrar_venta($allInputs)){ // REGISTRAR CABECERA 
			$tieneDescuento = FALSE;
			$allInputs['idventa'] = GetLastId('idventa','venta'); 
			foreach ($allInputs['detalle'] as $key => $row) { 
				//$row['tiene_prog_cita'] = FALSE; // quitar despuyes de programar al medico 
				$row['idventa'] = $allInputs['idventa'];
				$row['si_tipo_campania'] = 0;
				if( !empty($row['si_campania']) ){ 
					$row['si_tipo_campania'] = $row['idtipocampania'];
				}
				//$row['si_solicitud'] = 0;
				if( empty($row['si_solicitud']) ){ 
					$row['si_solicitud'] = 0;
				}

				$result = TRUE;
				$resultDetalle = TRUE;
				$resultCanales = TRUE;
				$resultProg = TRUE;
				if( $row['producto']['idtipoproducto'] == 12 && $row['tiene_prog_cita'] == 1 && $row['tiene_venta_prog_cita'] == 1){ 
					$result = FALSE;
					$resultDetalle = FALSE;
					$resultCanales = FALSE;
					$resultProg = FALSE;
					$data = array(
						'iddetalleprogmedico' => $row['detalleCupo']['iddetalleprogmedico'],
						'fecha_reg_reserva' => date('Y-m-d H:i:s'),
						'fecha_reg_cita' => date('Y-m-d H:i:s'),
						'fecha_atencion_cita' => $row['detalleCupo']['fecha_programada']. " " . $row['detalleCupo']['hora_inicio_det'],
						'idcliente' => $allInputs['cliente']['id'],
						'idempresacliente' => (empty($datos['empresa']['id']) ? NULL : $datos['empresa']['id']),
						'estado_cita' => 2,
						'idproductomaster' => $row['id'],
						'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
						);
					$result = $this->model_prog_cita->m_registrar($data);
					$idprogcita = GetLastId('idprogcita','pa_prog_cita');
					$row['idprogcita'] = $idprogcita;

					$data = array(
						'iddetalleprogmedico' => $row['detalleCupo']['iddetalleprogmedico'],
						'estado_cupo' => 1
					);
					$resultDetalle = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);

					/*Solo debe modificar cupos si NO es adicional*/
					if(!$row['detalleCupo']['si_adicional']){
						$data = array( 
							'idprogmedico' => $row['detalleCupo']['idprogmedico'],
							'idcanal' => $row['detalleCupo']['idcanal']
						);
						$resultCanales = $this->model_prog_medico->m_cambiar_cupos_canales($data);

						$data = array(
							'idprogmedico' => $row['detalleCupo']['idprogmedico'],
						);
						$resultProg = $this->model_prog_medico->m_cambiar_cupos_programacion($data);
					}
					
				}

				if(/*$result && $resultDetalle && $resultCanales && $resultProg 
						&&*/ $this->model_venta->m_registrar_detalle($row,$allInputs) ) {
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1;
	    			
	    			if( $row['producto']['idtipoproducto'] == 12 && $row['tiene_prog_cita'] == 1 && $row['tiene_venta_prog_cita'] == 1){ 

	    				$citaPaciente = array( 
		    				'paciente' => $allInputs['cliente']['nombres'] . ' ' .$allInputs['cliente']['apellidos'],
		    				'email' => $allInputs['cliente']['email'],
		    				'especialidad' => $allInputs['temporal']['especialidad']['descripcion'],
		    				'fecha_programada' => $row['detalleCupo']['fecha_str'],
		    				'turno' => $row['detalleCupo']['turno'],
		    				'medico' => $row['detalleCupo']['medico'],
		    				'ambiente' => $row['detalleCupo']['ambiente'],
		    				'sede' => $this->sessionHospital['sede'],
		    			); 
	    				
	    				$resultMail = enviar_mail_paciente(1,$citaPaciente);
		    			$arrData['flagMail']  = $resultMail['flag'];
		    			$arrData['msgMail']  = $resultMail['msgMail'];
	    			}
	    			
				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}

				// COMPROBAR SI HAY DESCUENTO 
				if( !empty($row['descuento']) && $row['descuento'] > 0 ){ 
					$tieneDescuento = TRUE;
				}
			}
			if( $arrData['flag'] === 1 ){ 
				//Actualizamos el número de serie 
				$params = array(
					'idcajamaster' => $allInputs['idcajamaster'],
					'idtipodocumento' => $allInputs['idtipodocumento'],
					'numeroserie' => ($allInputs['numero_serie'] + 1)
				);
				$this->model_caja->m_editar_numero_serie($params);
			}
			// SI TIENE DESCUENTO LO PONEMOS EN ESPERA 
			if($tieneDescuento) { 
				$this->model_venta->m_editar_venta_a_espera($allInputs['idventa']); 
			}
			$arrData['idventaregister'] = $allInputs['idventa'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function enviar_solicitud_reimpresion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$id = @$allInputs['id'];
		if( !empty($allInputs[0]['id']) ){
			$id = $allInputs[0]['id'];
		}
		$arrData['message'] = 'No se pudo solicitar la re-impresión.';
    	$arrData['flag'] = 0;
		if( $this->model_venta->m_editar_venta_a_solicitud_impresion( $id ) ){ 
			$arrData['message'] = 'Se mandó la solicitud de re-impresión.';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function aprobar_solicitud_reimpresion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo aprobar la re-impresión.';
    	$arrData['flag'] = 0;
		if( $this->model_venta->m_editar_venta_a_solicitud_impresion_aprobada( $allInputs[0]['id'] ) ){ 
			$arrData['message'] = 'Se aprobó la solicitud de re-impresión.';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function aprobar_venta_descuento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo aprobar la venta';
    	$arrData['flag'] = 0;
		if( $this->model_venta->m_editar_venta_a_aprobado( $allInputs[0]['id'] ) ){ 
			$arrData['message'] = 'Se aprobó la venta correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_venta_caja_actual()
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular la venta';
    	$arrData['flag'] = 0;
    	//VALIDACION PARA MUESTRAS DE LABORATORIO REGISTRADAS
    	if( $allInputs['idespecialidad'] == 21 ){ // solo ventas de laboratorio
	    	$rowMuestra = $this->model_atencionMuestra->m_cargar_orden_laboratorio_por_venta($allInputs['orden']);
	    	if( !empty($rowMuestra) ){
	    		$arrData['message'] = 'NO SE PUEDE ANULAR!!. Ya se ha registrado la muestra de Laboratorio Contacte con el área de sistemas.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
    	}
    	// VALIDACION PARA VENTAS ATENDIDAS
    	$rowVenta = $this->model_venta->m_cargar_esta_venta_por_id($allInputs['id']);
    	if( $rowVenta['paciente_atendido_v'] == 1 ){
    		$arrData['message'] = 'La venta ya ha sido atendida, no se puede anular. Contacte con el área de sistemas.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$listaDetalle = $this->model_venta->m_cargar_detalle_venta( $allInputs['id'], false);
    	foreach ($listaDetalle as $detalle) {
    		if($detalle['paciente_atendido_det'] == 1){
    			$arrData['message'] = 'No se puede anular la venta. Tiene atenciones registradas';
    			$arrData['flag'] = 0;
    			$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}
    	}
    	// if( empty($allInputs[0]['id']) ){ 
    	// 	$allInputs['id'] = $allInputs[0]['id'];
    	// }
    	
    	$this->db->trans_start();
		if( $this->model_venta->m_anular_venta_caja_actual( $allInputs['id'] ) ){
			$listaDetalle = $this->model_venta->m_cargar_detalle_venta( $allInputs['id'], true);
			if(count($listaDetalle)>0) {
				foreach ($listaDetalle as $detalle) {
					$data = array(
						'estado_cupo' => 2,
						'iddetalleprogmedico' => $detalle['iddetalleprogmedico']
					);

				 	if( $this->model_prog_cita->m_anular_cita( $detalle['idprogcita'] ) && 
					 	$this->model_prog_medico->m_cambiar_estado_detalle_de_programacion( $data) ){
				 		$arrData['message'] = 'Se anuló la venta correctamente';
	    				$arrData['flag'] = 1;
				 	}else{
				 		$arrData['message'] = 'No se pudo anular la venta';
	    				$arrData['flag'] = 0;
				 	}			
				}
			}else{
				$arrData['message'] = 'Se anuló la venta correctamente';
	    		$arrData['flag'] = 1;
			}						

		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function imprimir_ticket_venta()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idventa = @$allInputs[0]['id'];
		if(!empty($allInputs['id'])){ 
			$idventa = $allInputs['id'];
		}
    	$arrData['flag'] = 1;
    	$arrData['html'] = '';
    	$htmlData = '<table>';
    	$arrParams = array(
    		'searchText' => $idventa,
    		'searchColumn' => 'v.idventa'
    	);
    	$fVenta = $this->model_venta->m_cargar_esta_venta_por_columna( $arrParams ); // var_dump($fVenta); exit();
    	if( empty($fVenta) ){ 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		if( !($this->sessionHospital['key_group'] == 'key_sistemas') ){ 
			if( $fVenta['estado'] == 2 ){ // VENTA SIN APROBAR 
	    		$arrData['flag'] = 2;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
			}
			if( $fVenta['tiene_impresion'] == 1 && $fVenta['solicita_impresion'] == 2 ){ // VENTA YA HA SIDO IMPRESA Y NO SOLICITA REIMPRESION 
	    		$arrData['flag'] = 3;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
			}
			if( $fVenta['tiene_impresion'] == 1 && $fVenta['solicita_impresion'] == 1 ){ // VENTA YA HA SIDO IMPRESA Y SOLICITA REIMPRESION, PERO AUN NO HA SIDO ACEPTADO 
	    		$arrData['flag'] = 4;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
			}
		}
		if( !empty($fVenta['si_procedimiento']) ){ // TICKET DE PROCEDIMIENTOS 
			$listaDetalles = $this->model_venta->m_cargar_esta_venta_con_detalle_por_columna_procedimiento( $arrParams );
		}else{ // LOS DEMAS TICKETS 
			$listaDetalles = $this->model_venta->m_cargar_esta_venta_con_detalle_por_columna( $arrParams );
		} 

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';

		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> F. VENTA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_venta']).' </td> </tr>'; 
		if( !empty($listaDetalles[0]['numero_ambiente']) ){
			$htmlData .= '<tr style="font-weight: bold;" > <td> F. ATENCIÓN </td> <td> : '.formatoFechaReporte4($listaDetalles[0]['fecha_atencion_cita']).' </td> </tr>';
		}
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total($fVenta['username']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		if($fVenta['idempresacliente'] != null){
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> HISTORIA </td> <td> : '.$fVenta['idhistoria'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> PACIENTE </td> <td> : '.strtoupper($fVenta['nombres']).' '.strtoupper($fVenta['apellido_paterno']).' '.strtoupper($fVenta['apellido_materno']).' </td> </tr>'; 
		$htmlData .= '<tr> <td> EDAD </td> <td> : '.$fVenta['edad'].' </td> </tr>';
		if( $fVenta['es_convenio'] == 1 ){ 
			$htmlData .= '<tr> <td> CONVENIO </td> <td> : '.$fVenta['convenio'].' </td> </tr>';
		}

		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
		$htmlData .= '<tr style="font-size: 66px;font-weight: bold; text-align:center;"> <td colspan="2"> ESPECIALIDAD : '.strtoupper($listaDetalles[0]['especialidad']).' </td> </tr>'; 
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
		if( !empty($listaDetalles[0]['numero_ambiente']) ){ 
			$htmlData .= '<tr style="font-size: 66px;font-weight: bold; text-align:center;"> <td colspan="2"> CONSULTORIO : '.strtoupper(@$listaDetalles[0]['numero_ambiente']).' </td> </tr>'; 
			if( !empty($fVenta['si_procedimiento']) ){ 
				$htmlData .= '<tr style="font-size: 40px;font-weight: bold; text-align:center;"> <td colspan="2"> TURNO : '.@$listaDetalles[0]['hora_inicio'].' - '.@$listaDetalles[0]['hora_fin'].' </td> </tr>';
			}else{
				$htmlData .= '<tr style="font-size: 40px;font-weight: bold; text-align:center;"> <td colspan="2"> TURNO : '.@$listaDetalles[0]['hora_inicio_det'].' </td> </tr>'; 
				
			}
			$htmlData .= '<tr style="font-size: 40px;font-weight: bold; text-align:center;"> <td colspan="2"> MÉDICO : '.@$listaDetalles[0]['medico'].' </td> </tr>'; 
			
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
			if( @$listaDetalles[0]['si_adicional'] == 1 && empty($fVenta['si_procedimiento'])){ 
				$htmlData .= '<tr style="font-size: 40px;font-weight: bold; text-align:center;"> <td colspan="2"> ADICIONAL : SI </td> </tr>'; 
				$htmlData .= '<tr style="font-size: 40px;font-weight: bold; text-align:center;"> <td colspan="2"> N° ADICIONAL : +'.$listaDetalles[0]['numero_cupo'].' </td> </tr>'; 
			}
		} 
		
		$htmlData .= '</table>'; 
		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;"> <td> CANT. </td> <td style="text-align: center;"> PRODUCTO </td> <td> IMPORTE </td> </tr>'; 
		foreach ($listaDetalles as $row) {
			if($row['idpaquete'] != null ){
				$row['producto'] = $row['paquete']. ' - ' . $row['producto'];
			}
			$htmlData .= '<tr style="height:160px"> <td style="text-align: center;"> '.$row['cantidad'].' </td> <td style="padding: 0px 6px;"> '.$row['producto'].' </td> <td width="60"> '.$row['total_detalle'].' </td> </tr>'; 
		} 
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		
		$htmlData .= '</table>';

		$htmlData .= '<table>';
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['sub_total_num'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['total_igv_num'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format($fVenta['total_a_pagar_num'],2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
		// $total=1234; 
		// var_dump($fVenta['total_a_pagar']); exit(); 
		// $monto = new EnLetras();
		$con_letra = ValorEnLetras($fVenta['total_a_pagar_num'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		// OJO ACA VA LA PROCEDENCIA DEL CLIENTE SOLO 17 y 35
		if(!empty($fVenta['grupo']) && ( $fVenta['idgroup'] == 17 || $fVenta['idgroup'] == 35)){
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tr style="font-size: 66px;font-weight: bold; text-align:center;"> <td colspan="3">'.strtoupper($fVenta['grupo']).' </td> </tr>'; 
		}

		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '<tr> <td colspan="3" style="padding-top:40px; font-size:66px;"> <b> <center> CENTRAL TELEFÓNICA: 399-1414 </center> </b> </td> </tr>'; 
		
		if( empty($listaDetalles[0]['numero_ambiente']) ){ 
			$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO CAMBIO O DEVOLUCION DE DINERO DURANTE LOS 5 DIAS CALENDARIOS. </td> </tr>'; 
		}

		if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
		}
		$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
		$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
		//$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';

		if( $fVenta['tiene_impresion'] == 2) { 
			$this->model_venta->m_editar_venta_a_impreso($idventa);
		}
		if( $fVenta['tiene_impresion'] == 1 && $fVenta['solicita_impresion'] == 3 ){ // VENTA YA HA SIDO IMPRESA Y SE ACEPTÓ LA SOLICITUD DE REIMPRESION 
			$this->model_venta->m_editar_venta_a_reimpreso($idventa);
			$this->model_venta->m_editar_venta_a_sin_solicitud_impresion($idventa); // VOLVER A ACTUALIZAR A SIN SOLICITUD DE IMPRESION
		}
		$arrData['html'] = $htmlData;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function imprimir_ticket_venta_manual()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$idventa = @$allInputs[0]['id'];
		if(!empty($allInputs['id'])){ 
			$idventa = $allInputs['id'];
		}
    	$arrData['flag'] = 1;
    	$arrData['html'] = '';
    	$htmlData = '<table>';
    	$arrParams = array(
    		'searchText' => $idventa,
    		'searchColumn' => 'v.idventa'
    	);
    	$fVenta = $this->model_venta->m_cargar_esta_venta_por_columna( $arrParams ); //var_dump($fVenta); exit();
    	if( empty($fVenta) ){ 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		if( !($this->sessionHospital['key_group'] == 'key_sistemas') ){ 
    		$arrData['message'] = 'Sólo el área de sistemas puede imprimir tickets manualmente';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		$fVenta['fecha_venta'] = $allInputs['fecha_venta'] . ' ' . $allInputs['hora_venta'] . ':' . $allInputs['minuto_venta'];
		//var_dump($fVenta); exit();
		$listaDetalles = $this->model_venta->m_cargar_esta_venta_con_detalle_por_columna( $arrParams );

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';

		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_venta']).' </td> </tr>';
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total($fVenta['username']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		if($fVenta['idempresacliente'] != null){
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> HISTORIA </td> <td> : '.$fVenta['idhistoria'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> PACIENTE </td> <td> : '.strtoupper($fVenta['nombres']).' '.strtoupper($fVenta['apellido_paterno']).' '.strtoupper($fVenta['apellido_materno']).' </td> </tr>'; 
		$htmlData .= '<tr> <td> EDAD </td> <td> : '.$fVenta['edad'].' </td> </tr>'; 
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
		$htmlData .= '<tr style="font-size: 66px;font-weight: bold; text-align:center;"> <td colspan="2"> ESPECIALIDAD : '.strtoupper($listaDetalles[0]['especialidad']).' </td> </tr>'; 
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
		if( !empty($listaDetalles[0]['nro_consultorio']) ){
			$htmlData .= '<tr style="font-size: 66px;font-weight: bold; text-align:center;"> <td colspan="2"> CONSULTORIO : '.strtoupper(@$listaDetalles[0]['nro_consultorio']).' </td> </tr>'; 
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>'; 
		}
		
		$htmlData .= '</table>'; 
		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;"> <td> CANT. </td> <td style="text-align: center;"> PRODUCTO </td> <td> IMPORTE </td> </tr>'; 
		foreach ($listaDetalles as $row) {
			if($row['idpaquete'] != null ){
				$row['producto'] = $row['paquete']. ' - ' . $row['producto'];
			}
			$htmlData .= '<tr style="height:160px"> <td style="text-align: center;"> '.$row['cantidad'].' </td> <td style="padding: 0px 6px;"> '.$row['producto'].' </td> <td width="60"> '.$row['total_detalle'].' </td> </tr>'; 
		} 
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
		$htmlData .= '</table>';

		$htmlData .= '<table>';
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['sub_total_num'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['total_igv_num'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format($fVenta['total_a_pagar_num'],2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
		$con_letra = ValorEnLetras($fVenta['total_a_pagar_num'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
		//$htmlData .= '<tfoot>'; 
		$htmlData .= '<tr> <td colspan="3" style="padding-top:40px; font-size:66px;"> <b> <center> CENTRAL TELEFÓNICA: 399-1414 </center> </b> </td> </tr>'; 
		$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO CAMBIO O DEVOLUCION DE DINERO DURANTE LOS 7 DIAS CALENDARIOS. </td> </tr>'; 

		if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
		}
		$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
		$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
		//$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';

		if( $fVenta['tiene_impresion'] == 2) { 
			$this->model_venta->m_editar_venta_a_impreso($idventa);
		}
		if( $fVenta['tiene_impresion'] == 1 && $fVenta['solicita_impresion'] == 3 ){ // VENTA YA HA SIDO IMPRESA Y SE ACEPTÓ LA SOLICITUD DE REIMPRESION 
			$this->model_venta->m_editar_venta_a_reimpreso($idventa);
			$this->model_venta->m_editar_venta_a_sin_solicitud_impresion($idventa); // VOLVER A ACTUALIZAR A SIN SOLICITUD DE IMPRESION
		}
		$arrData['html'] = $htmlData;
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
			if( $this->model_venta->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function generateCodigoOrden()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData = array();
		// "S" + IDSEDE + IDCAJA + DIA + MES + AÑO + CORRELATIVO 
		$codigoOrden = 'S';
		$codigoOrden .= $this->sessionHospital['idsede'];

		// OBTENER CAJA ACTUAL DEL USUARIO 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario();
		if( empty($fCaja) ){
			exit();
		}
		$codigoOrden .= $fCaja['idcaja'];
		$codigoOrden .= date('dmy');
		

		/* HALLAMOS TAMBIEN LA CAJA ACTUAL */ 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario(); // var_dump($fCaja);
		$paramDatos['idcaja'] = $fCaja['idcaja'];
		// OBTENER ULTIMA ORDEN DE VENTA CAJA 
		$fUltimaVenta = $this->model_venta->m_cargar_ultima_venta_caja($paramDatos);
		// var_dump($fUltimaVenta); exit();
		if( empty($fUltimaVenta) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($fUltimaVenta['orden_venta'], -6, 6); 
			//var_dump($numberToOrden); var_dump(substr($fUltimaVenta['orden_venta'], -12, 6)); var_dump(date('dmy')); 
			if( substr($fUltimaVenta['orden_venta'], -12, 6) == date('dmy') ){ 
				$numberToOrden = (int)$numberToOrden + 1;
			}else{
				$numberToOrden = 1;
			}
		}
		//var_dump($numberToOrden); exit(); 
		$codigoOrden .= str_pad($numberToOrden, 6, '0', STR_PAD_LEFT);
		$arrData['codigo_orden'] = $codigoOrden;
		$arrData['idcajamaster'] = $fCaja['idcajamaster'];
		$arrData['idcaja'] = $fCaja['idcaja'];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function generateCodigoTicket()
	{
		$arrData = array();
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		// OBTENER CAJA ACTUAL DEL USUARIO 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($allInputs['idmodulo']);
		
		if( empty($fCaja) ){ 
			var_dump($fCaja,'cajatual');
			exit();
		}
		// BUSCAR SERIE DE LA VENTA
		$fNumeroSerie = $this->model_caja->m_cargar_caja_por_este_numero_serie($fCaja['idcajamaster'],$allInputs['idtipodocumento']);
		// var_dump($fNumeroSerie); exit();
		if( empty($fNumeroSerie) ){ 
			var_dump($fNumeroSerie,'numeserie');
			exit();
		}
		//var_dump($fNumeroSerie); exit();
		$numeroSeriePad = str_pad(($fNumeroSerie['numero_serie'] + 1), 9, '0', STR_PAD_LEFT);
		$codigoTicketStr = $fNumeroSerie['serie_caja'].'-'.$numeroSeriePad;
		$arrData['ticket'] = $codigoTicketStr;
		$arrData['serie'] = $fNumeroSerie['serie_caja'];
		$arrData['numero_serie'] = $fNumeroSerie['numero_serie'];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cerrar_caja_usuario_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($allInputs['idmodulo']);
		$arrData['message'] = 'No se pudo cerrar la caja';
    	$arrData['flag'] = 0;
    	if( !empty($fCaja) ) {
    		if($allInputs['idmodulo'] == '3'){ // farmacia 
    			$allInputs['idcaja'] = $fCaja['idcaja'];
    			// var_dump($allInputs); exit(); 
    			if( $this->model_venta_farmacia->m_cerrar_caja_farmacia($allInputs) ){ 
					$arrData['message'] = 'Se cerró la caja correctamente';
		    		$arrData['flag'] = 1;
				}
    		}else{
	    		if( $this->model_venta->m_cerrar_caja($fCaja['idcaja']) ){ 
					$arrData['message'] = 'Se cerró la caja correctamente';
		    		$arrData['flag'] = 1;
				}	
    		}
    	}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function abrir_caja_usuario_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$fValidate = $this->model_caja->m_validar_usuario_session_caja_abierta();
    	if( !empty($fValidate) ){ // USUARIO TIENE CAJA ABIERTA 
    		$arrData['message'] = 'Ud. ya tiene una caja abierta.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	$fValidate = $this->model_caja->m_validar_caja_master_abierta($allInputs);
    	if( !empty($fValidate) ){ // LA CAJA YA ESTÁ ABIERTA 
    		$arrData['message'] = 'La caja que desea abrir, ya esta siendo usada.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	$this->db->trans_start();
		if($this->model_venta->m_abrir_caja($allInputs)){ 
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function desaprobar_venta_descuento(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo desaprobar la venta';
    	$arrData['flag'] = 0;
		if( $this->model_venta->m_editar_venta_a_desaprobado( $allInputs[0]['id'] ) ){ 
			$arrData['message'] = 'Se desaprobó la venta correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}