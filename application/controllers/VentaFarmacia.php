<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VentaFarmacia extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','fechas_helper','config_helper','contable'));
		$this->load->model(array('model_venta_farmacia','model_caja','model_config','model_medicamento_almacen','model_medicamento','model_empresa_admin', 'model_cliente', 'model_receta_medica', 'model_solicitud_formula', 'model_entrada_farmacia','model_empleado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
	}
	public function ver_popup_detalle_venta()
	{
		$this->load->view('ventaFarmacia/popupVerDetalleVenta');
	}
	public function ver_popup_procesar_venta()
	{
		$this->load->view('ventaFarmacia/popupProcesarVenta');
	}
	public function ver_popup_cerrar_caja()
	{
		$this->load->view('ventaFarmacia/popupCerrarCaja');
	}
	public function ver_popup_multi_pago()
	{
		$this->load->view('ventaFarmacia/popupMultiPago');
	}
	public function generateCodigoOrdenPedido()
	{	
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); // para traer el idsedeempresaadmin de sistemas
		// var_dump($allInputs); exit();
		$arrData = array();
		// "P" + IDSEDE + '-'  + AÑO + MES + DIA + '-' + CORRELATIVO 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
			$allInputs['idsedeempresaadmin'] = $this->sessionHospital['idsedeempresaadmin'];
		}
		$codigoOrden = 'P';
		$codigoOrden .= $allInputs['idsedeempresaadmin'];
		$codigoOrden .= '-';
		$codigoOrden .= date('ymd');
		$codigoOrden .= '-';

		// OBTENER ULTIMA ORDEN DE PEDIDO DE LA SEDE EMPRESA ADMIN 
		$fUltimoPedido = $this->model_venta_farmacia->m_cargar_ultimo_pedido_venta($allInputs);
		//var_dump($fUltimoPedido); exit();
		if( empty($fUltimoPedido) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($fUltimoPedido['orden_pedido'], -4, 4);
			if( substr($fUltimoPedido['orden_pedido'], -11, 6) == date('ymd') ){ 
				$numberToOrden = (int)$numberToOrden + 1;
			}else{
				$numberToOrden = 1;
			}
		}
		$codigoOrden .= str_pad($numberToOrden, 4, '0', STR_PAD_LEFT);
		$porciones = explode("-", $codigoOrden);

		$arrData['codigo_orden'] = $codigoOrden;
		$arrData['prefijo'] = $porciones[0] . '-' . $porciones[1];
		$arrData['correlativo'] = $porciones[2];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function generateCodigoOrdenSalida()
	{
		$arrData = array();
		// "F" + IDSEDE + IDCAJA + AÑO + MES + DIA + CORRELATIVO 
		$codigoOrden = 'F';
		$codigoOrden .= $this->sessionHospital['idsede'];

		// OBTENER CAJA ACTUAL DEL USUARIO 
		$datos['idmodulo'] = 3; 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($datos['idmodulo']);
		if( empty($fCaja) ){
			exit();
		}
		$codigoOrden .= $fCaja['idcaja'];
		$codigoOrden .= date('ymd');

		/* HALLAMOS TAMBIEN LA CAJA ACTUAL */ 
		//$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($datos['idmodulo']); // var_dump($fCaja);
		$paramDatos['idcaja'] = $fCaja['idcaja']; 
		$paramDatos['dir_movimiento'] = 2; // SALIDA 
		// OBTENER ULTIMA ORDEN DE VENTA CAJA 
		$fUltimaVenta = $this->model_venta_farmacia->m_cargar_ultima_venta_caja($paramDatos);
		
		//var_dump($fUltimaVenta); exit();
		if( empty($fUltimaVenta) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($fUltimaVenta['orden_venta'], -6, 6);
			if( substr($fUltimaVenta['orden_venta'], -12, 6) == date('ymd') ){ 
				$numberToOrden = (int)$numberToOrden + 1;
			}else{
				$numberToOrden = 1;
			}
		}
		$codigoOrden .= str_pad($numberToOrden, 6, '0', STR_PAD_LEFT);
		$arrData['codigo_orden'] = $codigoOrden;
		$arrData['idcajamaster'] = $fCaja['idcajamaster'];
		$arrData['idcaja'] = $fCaja['idcaja'];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_pedidos_ventas_por_aprobar()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$paramDatos['desde'] = date('Y-m-d') . ' 00:00';
		$paramDatos['hasta'] = date('Y-m-d') . ' 23:59';
		$lista = $this->model_venta_farmacia->m_cargar_pedidos_ventas_por_aprobar($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_pedidos_ventas_por_aprobar($paramPaginate,$paramDatos);
		//var_dump($lista); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			//$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['es_pedido'] == 1 ){ // VENDIDO 
				$objEstado['claseIcon'] = 'ti-timer';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'PEDIDO';
			}
			if( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			// if( $row['estado_movimiento'] == 3 ){ // CON DESCUENTO 
			// 	$objEstado['claseIcon'] = 'fa-spinner';
			// 	$objEstado['claseLabel'] = 'label-warning';
			// 	$objEstado['labelText'] = 'EN ESPERA';
			// }
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-exclamation';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NOTA DE CREDITO';
			}

			// if( $row['paciente_atendido_v'] == 1 ){ 
			// 	$objEstado['claseIconAtendido'] = 'fa-thumbs-up'; 
			// }
			$porciones = explode("-", $row['orden_pedido']);

			$prefijo = $porciones[0] . '-' . $porciones[1];
			$correlativo = $porciones[2];
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden_pedido' => $row['orden_pedido'],
					'prefijo' => $prefijo,
					'correlativo' => $correlativo,
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'idtipocliente' => $row['idtipocliente'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idusuario' => $row['iduser'],
					'vendedor' => strtoupper($row['nombre_vendedor']) . ' ' . strtoupper($row['apellido_vendedor']),
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado,
					'estemporal'=> ($row['es_temporal'] == 1 ? true : false)
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
		$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['suma_total']) ? 0 : $totalRows['suma_total'];
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
	public function lista_ventas_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_ventas_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_sum_ventas_caja_actual($paramPaginate,$paramDatos); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			//$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['estado_movimiento'] == 1 ){ // VENDIDO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'VENDIDO';
			}
			if( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-exclamation';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NOTA DE CREDITO';
			}

			// if( $row['paciente_atendido_v'] == 1 ){ 
			// 	$objEstado['claseIconAtendido'] = 'fa-thumbs-up'; 
			// }
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'idtipocliente' => $row['idtipocliente'],
					'tipocliente' => $row['descripcion_tc'],
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
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'total_sf' => $row['total_a_pagar_sf'],
					'estado' => $objEstado,
					'estado_movimiento' => $row['estado_movimiento'],
					'es_preparado' => $row['es_preparado'] == 1? TRUE:FALSE
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['suma_total']) ? 0 : $totalRows['suma_total'];
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
	public function lista_detalle_venta()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_detalle_venta_caja_actual($paramPaginate,$paramDatos); 
		$totalRows = $this->model_venta_farmacia->m_count_sum_detalle_venta_caja_actual($paramPaginate,$paramDatos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					// 'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'total_detalle' => $row['total_detalle']
				)
			);
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
	public function lista_detalle_pedido()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_detalle_pedido($paramPaginate,$paramDatos); 
		$totalRows = $this->model_venta_farmacia->m_count_sum_detalle_pedido($paramPaginate,$paramDatos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'id' => $row['idmedicamento'],
					'descripcion' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'cantidad' => $row['cantidad'],
					'precio' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'total' => $row['total_detalle'],
					'idtipocliente' => $row['idtipocliente']
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
	public function lista_ventas_anulados_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_ventas_anuladas_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_sum_ventas_anuladas_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();
		$sumTotal = 0;
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
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
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar']
				)
			);
			$sumTotal += $row['total_a_pagar'];
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = $totalRows['suma_total'];
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
	public function lista_ventas_con_descuento_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_ventas_en_espera_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_sum_ventas_en_espera_caja_actual($paramPaginate,$paramDatos); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_movimiento'] == 3 ){ // ENTRANTE CON DSCTO 
				$objEstado['claseIcon'] = 'fa-spinner fa-spin';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR APROBAR';
			}
			if( $row['estado_movimiento'] == 1 ){ // APROBADO CON DSCTO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'APROBADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
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
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['suma_total']) ? 0 : $totalRows['suma_total'];
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
	public function lista_productos_venta_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_producto_venta_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_sum_producto_venta_caja_actual($paramPaginate,$paramDatos); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'idmediopago' => $row['idmediopago'],
					'mediopago' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'total_detalle' => $row['total_detalle'], 
					'sede' => $row['sede'],
					'empresa_admin'=> $row['empresa_admin']
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['suma_total']) ? 0 : $totalRows['suma_total'];
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
	public function lista_solicitudes_impresion_caja_actual()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos);
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
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					//'idtipocliente' => $row['idtipocliente'],
					//'tipocliente' => $row['descripcion_tc'],
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
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
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
	public function lista_pedido_por_orden_pedido()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo encontrar el pedido';
    	$arrData['flag'] = 0;
    	//$pedido = $this->model_venta_farmacia->m_cargar_pedido_por_orden_pedido($allInputs);
    	$arrFilters = array( 
    		'searchColumn' => 'orden_pedido',
    		'searchText' => $allInputs['orden_pedido']
    	);
    	
    	if( $pedido = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters) ) {
    		if($pedido['es_pedido'] == 1){
	    		$pedido['cliente'] = $pedido['nombres'].' '.$pedido['apellido_paterno'].' '.$pedido['apellido_materno'];
	    		$arrData['message'] = 'Pedido encontrado';
	    		$arrData['flag'] = 1;
	    		$arrData['datos'] = $pedido;	
    		}else{
    			$arrData['message'] = 'El pedido ya ha sido procesado';
    			$arrData['flag'] = 0;
    		}
    		
    	}
		//var_dump($pedido); exit();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_ordenes_ventas_cerradas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_venta_farmacia->m_cargar_ordenes_venta_cajas_cerradas($allInputs);
		}else{
			$lista = $this->model_venta_farmacia->m_cargar_ordenes_venta_cajas_cerradas();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idmovimiento'], 
					'idcliente' => $row['idcliente'],
					'idempresacliente' => $row['idempresacliente'],
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
	public function listar_ordenes_ventas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_venta_farmacia->m_cargar_ordenes_venta($allInputs);
		}else{
			$lista = $this->model_venta_farmacia->m_cargar_ordenes_venta();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idmovimiento'], 
					'idcliente' => $row['idcliente'],
					'idempresacliente' => $row['idempresacliente'],
					'ticket' => $row['ticket_venta'], 
					'orden' => $row['orden_venta'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'], 
					'saldo' => $row['total_a_pagar'],
					'empresa_cliente' => $row['empresa_cliente'],
					'fecha_venta' => $row['fecha_venta'],
					'saldo_format' => $row['total_a_pagar_format'],
					'es_preparado' => $row['es_preparado']
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
	public function lista_detalle_venta_por_columna()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 

		$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs['datos']['orden']
    	);
    	$lista = $this->model_venta_farmacia->m_cargar_esta_venta_con_detalle_por_columna($arrFilters);
    	$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'cantidad_original' => $row['cantidad'],
					'cantidad' => $row['cantidad'], 
					'medicamento' => $row['medicamento'], 
					'idmedicamento' => $row['idmedicamento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'precio' => $row['precio_unitario'],
					'monto' => $row['total_detalle'],
					'monto_sf' => $row['monto_sf']
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
	public function aprobar_venta_descuento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo aprobar la venta';
    	$arrData['flag'] = 0;
    	$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs[0]['orden']
    	);

    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters); 
    	if( empty($fVenta) ){
    		$arrData['message'] = 'No existe el registro, recargue el navegador y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $fVenta['estado_movimiento'] == 1 ){
    		$arrData['message'] = 'El movimiento ya ha sido aprobado anteriormente.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		if( $this->model_venta_farmacia->m_editar_venta_a_aprobado( $allInputs[0]['id'] ) ){ 
			$arrData['message'] = 'Se aprobó la venta correctamente';
    		$arrData['flag'] = 1;

	    		// RECUPERAR EL DETALLE PARA ACTUALIZAR EL STOCK
	    		$listaDetalle = $this->model_venta_farmacia->m_cargar_detalle_venta_descuento( $allInputs[0]['id'] );
	    		foreach ($listaDetalle as $key => $row) { 
	    			// CALCULAR STOCK DEL MEDICAMENTO ALMACEN 
	    			$row['stock_salidas'] = $row['cantidad'];
					$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida($row);
					// CALCULAR STOCK DEL MEDICAMENTO 
					$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['idmedicamento']);
		    		if(!(empty($listaMedicamento))){ 
		    			$rowAux['stock_actual_modificado'] = 0;
		    			foreach ($listaMedicamento as $key => $rowLM) {
		    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
		    			}
		    			$rowAux['idmedicamento'] = $row['idmedicamento'];
						if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
			    			$arrData['message'] = 'Se registraron los datos correctamente';
							$arrData['flag'] = 1;
			    		}
		    		}
	    		}	

		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_venta_pedido()  // solo pedido
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo aprobar la venta';
    	$arrData['flag'] = 0;
    	
    	$arrFilters = array( 
    		'searchColumn' => 'orden_pedido',
    		'searchText' => $allInputs['orden_pedido']
    	);
    	//var_dump($allInputs['orden_pedido']); exit();
    	$fVenta = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters); 
    	if( empty($fVenta) ){
    		$arrData['message'] = 'No existe el Pedido, recargue el navegador y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( $fVenta['es_pedido'] == 2 ){ // 
    		$arrData['message'] = 'El Pedido ya ha sido aprobado';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['ticket']) ){ 
    		$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['orden']) ){ 
    		$arrData['message'] = 'No se ha generado una ORDEN DE VENTA. Genere la ORDEN DE VENTA';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs['orden']
    	);
    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters);
    	if( !empty($fVenta) ){
    		$arrData['message'] = 'Ya se a registrado una venta, usando la orden <strong>'.$allInputs['orden'].'</strong>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$hayEnStock = TRUE;
    	foreach ($allInputs['detalle'] as $row) { 
    		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($row['idmedicamentoalmacen']); 
    		if( $row['cantidad'] > $fProducto['stock_actual_malm'] ) { 
   				$hayEnStock = FALSE; 
   				break;
   			}
    		
    	}
    	
    	if( $hayEnStock === FALSE ){ 
    		$arrData['message'] = 'Stock Agotado para el producto: <b>'.strtoupper($fProducto['medicamento']).'</b>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// COMPROBAR SI HAY DESCUENTO 
    	$tieneDescuento = FALSE;
    	if( empty($allInputs['idtipocliente']) ){
	    	foreach ($allInputs['detalle'] as $key => $detalle) {
	    		if( !empty($detalle['descuento']) && $detalle['descuento'] > 0 ){ 
					$tieneDescuento = TRUE;
					break;
				}
	    	}
    	}
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
    	if($this->model_venta_farmacia->m_editar_estado_pedido($allInputs)){
    		if(!$tieneDescuento){ // SI NO TIENE DESCUENTO
	    		$listaDetalle = $this->model_venta_farmacia->m_cargar_detalle_venta_descuento( $allInputs['id'] );

				foreach ($listaDetalle as $key => $row) { 
					// CALCULAR STOCK DEL MEDICAMENTO ALMACEN
					$row['stock_salidas'] = $row['cantidad'];
					$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida($row);
					// CALCULAR STOCK DEL MEDICAMENTO 
					$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['idmedicamento']);
		    		if(!(empty($listaMedicamento))){ 
		    			$rowAux['stock_actual_modificado'] = 0;
		    			foreach ($listaMedicamento as $key => $rowLM) {
		    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
		    			}
		    			$rowAux['idmedicamento'] = $row['idmedicamento'];
						if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
			    			$arrData['message'] = 'Se registraron los datos correctamente';
							$arrData['flag'] = 1;
			    		}
		    		}
				}
    		}else{
    			$arrData['message'] = 'La Venta se registró, pero necesita APROBACION por tener descuento';
    			$arrData['flag'] = 1;
    			// SI TIENE DESCUENTO LO PONEMOS EN ESPERA 
    			$this->model_venta_farmacia->m_editar_venta_a_espera($allInputs['id']); 
    		}
			$arrData['idventaregister'] = $allInputs['id'];
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
    	
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/*
	public function registrar_venta_pedido_temporal()  // APRUEBA EL PEDIDO
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo aprobar la venta';
    	$arrData['flag'] = 0;
    	
    	$arrFilters = array( 
    		'searchColumn' => 'orden_pedido',
    		'searchText' => $allInputs['orden_pedido']
    	);
    	//var_dump($allInputs['orden_pedido']); exit();
    	$fVenta = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters); 
    	if( empty($fVenta) ){
    		$arrData['message'] = 'No existe el Pedido, recargue el navegador y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( $fVenta['es_pedido'] == 2 ){ // 
    		$arrData['message'] = 'El Pedido ya ha sido aprobado';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['ticket']) ){ 
    		$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['orden']) ){ 
    		$arrData['message'] = 'No se ha generado una ORDEN DE VENTA. Genere la ORDEN DE VENTA';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs['orden']
    	);
    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters);
    	if( !empty($fVenta) ){
    		$arrData['message'] = 'Ya se a registrado una venta, usando la orden <strong>'.$allInputs['orden'].'</strong>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$hayEnStock = TRUE;
    	foreach ($allInputs['detalle'] as $row) { 
    		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($row['idmedicamentoalmacen']); 
   			if( $row['cantidad'] > $fProducto['stock_temporal'] ) { 
   				$hayEnStock = FALSE; 
   				break;
   			}
    	}
    	
    	if( $hayEnStock === FALSE ){ 
    		$arrData['message'] = 'Stock Agotado para el producto: <b>'.strtoupper($fProducto['medicamento']).'</b>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// COMPROBAR SI HAY DESCUENTO 
    	$tieneDescuento = FALSE;
    	foreach ($allInputs['detalle'] as $key => $detalle) {
    		if( !empty($detalle['descuento']) && $detalle['descuento'] > 0 ){ 
				$tieneDescuento = TRUE;
				break;
			}
    	}
    	//var_dump($allInputs['detalle']); exit();
    	$this->db->trans_start();
    	if($this->model_venta_farmacia->m_editar_estado_pedido($allInputs)){
    		if(!$tieneDescuento){ // SI NO TIENE DESCUENTO
	    		$listaDetalle = $this->model_venta_farmacia->m_cargar_detalle_venta_descuento( $allInputs['id'] );

				foreach ($listaDetalle as $key => $row) { 
					// CALCULAR STOCK DEL MEDICAMENTO ALMACEN 
					$row['stock_salidas'] = $row['cantidad'];
					$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida_temporal($row);
					// CALCULAR STOCK DEL MEDICAMENTO 
					// $listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['idmedicamento']);
		   			// 	if(!(empty($listaMedicamento))){ 
		   			// 		$rowAux['stock_actual_modificado'] = 0;
		   			// 		foreach ($listaMedicamento as $key => $rowLM) {
		   			//  		$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
		   			//	}
		   			//  $rowAux['idmedicamento'] = $row['idmedicamento'];
					// 	if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
			  		$arrData['message'] = 'Se registraron los datos correctamente';
					$arrData['flag'] = 1;
			  		// 	}
		   			// }
				}
    		}else{
    			$arrData['message'] = 'La Venta se registró, pero necesita APROBACION por tener descuento';
    			$arrData['flag'] = 1;
    			// SI TIENE DESCUENTO LO PONEMOS EN ESPERA 
    			$this->model_venta_farmacia->m_editar_venta_a_espera($allInputs['id']); 
    		}
			$arrData['idventaregister'] = $allInputs['id'];
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
    	
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}*/
	public function registrar_venta()
	{
		$arrConfig = obtener_parametros_configuracion();
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idventaregister'] = NULL; 
    	// VALIDAR ORDEN DE VENTA 
	    	if($arrConfig['modo_venta_far'] == 'VN'){
		    	if( empty($allInputs['ticket']) ){ 
		    		$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}
		    	if( empty($allInputs['orden']) ){ 
		    		$arrData['message'] = 'No se ha generado una ORDEN DE VENTA. Genere la ORDEN DE VENTA';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}
		    	$arrFilters = array( 
		    		'searchColumn' => 'orden_venta',
		    		'searchText' => $allInputs['orden']
		    	);
		    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters);
		    	if( !empty($fVenta) ){
		    		$arrData['message'] = 'Ya se a registrado una venta, usando la orden <strong>'.$allInputs['orden'].'</strong>';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}	
	    	}else{ // SI ES VENTA POR PEDIDO
	    		if( empty($allInputs['orden_pedido']) ){ 
		    		$arrData['message'] = 'No se ha generado una ORDEN DE PEDIDO. Genere la ORDEN DE PEDIDO';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}
		    	$arrFilters = array( 
		    		'searchColumn' => 'orden_pedido',
		    		'searchText' => $allInputs['orden_pedido']
		    	);
		    	$fVenta = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters);
		    	if( !empty($fVenta) ){
		    		$arrData['message'] = 'Ya se a registrado un pedido, usando la orden <strong>'.$allInputs['orden_pedido'].'</strong>';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}
		    	$allInputs['iduserpedido'] = $this->sessionHospital['idusers'];
	    	}
	    	if( $allInputs['esPreparado'] ){
	    		if( empty($allInputs['cliente']) ){
		    		$arrData['message'] = 'Seleccione un cliente.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
		    	}
	    	}
    	// VALIDACIONES DEL TIPO DE DOCUMENTO
	    	if( $allInputs['idtipodocumento'] == 12 ){
	    		if( empty($allInputs['saldo']) ){
	    			$arrData['message'] = 'Comprobante de Caja solo es para pagos parciales de Fórmulas';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
	    		}
	    	}
	    	if( $allInputs['idtipodocumento'] == 2 || $allInputs['idtipodocumento'] == 3 ){
				$arrData['message'] = 'Este tipo de documento no está disponible en ventas de Farmacia';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}
    	// VALIDACIONESW DEL STOCK
	    	$hayEnStock = TRUE;
	    	$ventaSinStock = TRUE;
	    	$fProducto = array();
	    	if( !$allInputs['esPreparado'] ){
		    	if( !$allInputs['estemporal']){
			    	foreach ($allInputs['detalle'] as $row) {
			    		if ( $row['idmedicamentoalmacen'] != 0 ){
				    		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($row['idmedicamentoalmacen']);
				   			if( $row['cantidad'] > $fProducto['stock_actual_malm'] ) { 
				   				$hayEnStock = FALSE; 
				   				break;
				   			}
			    		}
			    	}
		    	}else{
		    		foreach ($allInputs['detalle'] as $row) {
			    		if ( $row['idmedicamentoalmacen'] != 0 ){
				    		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($row['idmedicamentoalmacen']);
				   			if( $fProducto['stock_actual_malm'] - $row['cantidad'] >= 0 ) { 
				   				$arrData['message'] = 'El producto: <b>'.strtoupper($fProducto['medicamento']).'</b> tiene stock. No es permitido en este tipo de venta.';
		    					$ventaSinStock = FALSE;
				   				break;
				   			}
			    		}
			    	}
		    	}	
	    	}


    	/* Validar el numero de ticket sea correlativo */ 
    	$numeroDeSerieValido = FALSE; 
    	$fNumeroSerie = $this->model_caja->m_cargar_caja_por_este_numero_serie($allInputs['idcajamaster'],$allInputs['idtipodocumento']);
    	//var_dump($fNumeroSerie); exit(); 
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
    	if( $hayEnStock === FALSE ){ 
    		$arrData['message'] = 'Stock Agotado para el producto: <b>'.strtoupper($fProducto['medicamento']).'</b>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( !$ventaSinStock ){ 
    		
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// COMPROBAR SI HAY DESCUENTO 
    	$tieneDescuento = FALSE;
    	$descuento_puntos = FALSE;
    	if( empty($allInputs['cliente']['idtipocliente']) ){ 
	    	foreach ($allInputs['detalle'] as $key => $detalle) {
	    		if( $detalle['id'] == 0 ){
	    			$descuento_puntos = TRUE;
	    		}elseif( !empty($detalle['descuento']) ){
					if( $detalle['descuento'] > 0 ){ 
						$tieneDescuento = TRUE;
					}
	    		}
	    	}
    	}
    	if( $this->sessionHospital['key_group'] != 'key_sistemas'  ){
    		$allInputs['idsedeempresaadmin'] = $this->sessionHospital['idsedeempresaadmin'];
    		$allInputs['idalmacen'] = $this->sessionHospital['idalmacenfarmacia'];
    		if( $this->sessionHospital['key_group'] != 'key_dir_far' ){
				$allInputs['idsubalmacen'] = $this->sessionHospital['idsubalmacenfarmacia'];
    		}
    	}
	// CALCULAR PUNTOS
    	if( empty($allInputs['cliente_afiliado']) ){
	    	if( !empty($allInputs['numero_documento_afiliado']) ){
	    		$allInputs['cliente_afiliado']['num_documento'] = $allInputs['numero_documento_afiliado'];
				if( $cliente_afiliado = $this->model_cliente->m_comprobar_afiliacion_puntos($allInputs['cliente_afiliado']) ){
					$allInputs['cliente_afiliado'] = $cliente_afiliado;
				}else{
					$arrData['message'] = 'Cliente con DNI: <b>'.$allInputs['numero_documento_afiliado'].'</b> No esta afiliado al sistema de Puntos';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}   		
	    	}
    	}else{
    		if( !$this->model_cliente->m_comprobar_afiliacion_puntos($allInputs['cliente_afiliado']) ){
				$arrData['message'] = 'Cliente con DNI: <b>'.$allInputs['cliente_afiliado']['num_documento'].'</b> No esta afiliado al sistema de Puntos';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
			}
    	}

    	$empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['idsedeempresaadmin']);
    	if( $empresa_admin['cantidad_puntos'] == 0 || $empresa_admin['cantidad_puntos'] == NULL ||
    			$empresa_admin['cantidad_soles'] == 0 || $empresa_admin['cantidad_soles'] == NULL ){
    		$sistema_puntos = FALSE;
    	}else{
    		$sistema_puntos = TRUE;
    	}
    	if( !empty($allInputs['cliente_afiliado']) ){
    		if( !$sistema_puntos ){
    			$arrData['message'] = 'La Empresa <b>'. $empresa_admin['razon_social'] .'</b> No tiene configurado un Sistema de Puntos';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
    		}else{
    			$allInputs['puntos_ganados'] = intval($allInputs['total']) * $empresa_admin['cantidad_puntos'] / $empresa_admin['cantidad_soles'];
    			$allInputs['puntos_no_ganados'] = NULL;
    			$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($allInputs['cliente_afiliado']);
    			$datos_puntos['puntaje_obtenido'] = $datos_puntos['puntos_acumulados'] + $allInputs['puntos_ganados'];
    		}
    		
    	}else{
    		if( $sistema_puntos ){
    			$allInputs['puntos_no_ganados'] = intval($allInputs['total']) * $empresa_admin['cantidad_puntos'] / $empresa_admin['cantidad_soles'];
    			$allInputs['puntos_ganados'] = NULL;
    		}
    	}
	// VALIDAR PAGO MIXTO
    	$noTieneMonto = FALSE;
    	$montoInvalido = FALSE;
    	$monto_mixto_total = 0;
    	if( ($allInputs['idmediopago'] == 6) ){
			foreach ($allInputs['pagoMixto'] as $row) {
				if(!isset($row['monto'])){
					$noTieneMonto = TRUE;
					$arrData['message'] = 'No ha ingresado montos de pago mixto';
				}elseif(floatval($row['monto']) <= 0){
					$montoInvalido = TRUE;
					$arrData['message'] = 'Ingrese un monto de pago mixto mayor a cero.';
				}else{
					$monto_mixto_total += $row['monto'];
				}
			}
		}
		if( $noTieneMonto || $montoInvalido ){
			$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}
		// VALIDACION DE MONTOS CORRECTOS
    	$monto_total = 0;
    	foreach ($allInputs['detalle'] as $row) {
    		$monto_total += $row['cantidad']*$row['precio'] - $row['descuento'];
    	}
    	$total_sin_redondeo = (float)($allInputs['total_sin_redondeo']);
    	$monto_total = (float)($monto_total);
    	// var_dump(strval($total_sin_redondeo));
    	// var_dump(strval($monto_total));
    	// var_dump(strval($total_sin_redondeo) == strval($monto_total));

    	// exit();
    	
    	if( strval($total_sin_redondeo) != strval($monto_total) ){
    		$arrData['message'] = 'Verifique que los montos sean correctos y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		// COMPROBAR SI YA SE REGISTRO LA SOLICITUD DE FORMULAS
		$allInputs['codigo_pedido'] = NULL;
		if( $allInputs['esPreparado'] && $allInputs['boolSolicitud'] ){
			$estado = null;
			foreach ($allInputs['detalle'] as $row) {
				$arrDetalle = $this->model_solicitud_formula->m_verificar_estado_detalle_solicitud($row);
				$estado = $arrDetalle['estado_detalle_sol'];
				if( $estado != 1 ){
					$arrData['message'] = 'No se pudo registrar. Al menos un producto de la solicitud ya no está disponible';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}
				if( $arrDetalle['idsolicitudformula'] !=  $allInputs['idsolicitudformula'] ){
					$arrData['message'] = 'No se pudo registrar. EL número de la solicitud es incorrecta. Presione [F3] y cargue nuevamente la solicitud';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}
				if( $arrDetalle['idcliente'] !=  $allInputs['cliente']['id'] ){
					$arrData['message'] = 'No se pudo registrar. EL cliente no concuerda con la solicitud. Presione [F3] y cargue nuevamente la solicitud';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}
			}
			if( !empty($allInputs['a_cuenta'])){
				if( $allInputs['a_cuenta'] < $allInputs['total']*0.5 && $allInputs['idsolicitudformula'] != 4031 ){
					$arrData['message'] = 'El monto a cuenta no puede ser menor al 50% del total';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}
				$allInputs['total'] = $allInputs['a_cuenta']; // para el 1er pago de Preparados
				$allInputs['igv'] = NULL;
				$allInputs['subtotal'] = NULL;
			}
			// var_dump($allInputs); exit();
			// GENERAR CODIGO DE PEDIDO
			if( $arrConfig['serie_formula'] <> 0 ){
				$codigoPedido = 'N'; // serie de coorporacion JJ
				$fUltimoCodigo = $this->model_venta_farmacia->m_cargar_ultimo_codigo_pedido_formula();
				
				if( empty($fUltimoCodigo) ){
					$correlativo = $arrConfig['serie_formula'];

				}else{ 
					$correlativo = substr($fUltimoCodigo['codigo_pedido'], 1);
					$boolCodigoPed = TRUE;
				}
				if( $correlativo <> 0 ){
					$codigoPedido .= str_pad(((int)$correlativo + 1), 9, '0', STR_PAD_LEFT);
					$allInputs['codigo_pedido'] = $codigoPedido;
				}
			}
			
			// var_dump($fUltimoCodigo); var_dump($allInputs['codigo_pedido']); exit();
		}
		if( $allInputs['esPreparado'] && !$allInputs['boolSolicitud'] ){// para el 2do pago de Preparados
			$allInputs['total'] = $allInputs['total_saldo'];
			
		}
		// var_dump( $allInputs['esclienteexterno'] ); exit(); 
		// VALIDAR QUE SE DIGITE EL PROFESIONAL QUE ENVIA LA RECETA SI NO SE MARCADO LA CASILLA CLIENTE EXTERNO 
		if( !(@$allInputs['esPreparado']) ){
			if( !(@$allInputs['esclienteexterno']) ){ // no marcó el check 
				if( empty($allInputs['medico']['id']) ){
					$arrData['message'] = 'Seleccione al profesional que le generó la receta. Si es cliente externo, entonces marque la casilla de "Cliente Externo".';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}
			}
		}
		// var_dump($allInputs['esPreparado'],$allInputs['esclienteexterno'],@$allInputs['medico']['id'],$allInputs); exit();
    	// INICIO DEL REGISTRO
		// var_dump($allInputs); exit();
    	$this->db->trans_start();
    	$reg_success = TRUE;
    	if( $descuento_puntos ){ // VERIFICA SI EL CLIENTE CANJEA 1000 PUNTOS POR 5 SOLES DE DESCUENTO
    		if(!$this->model_cliente->m_actualizar_puntaje_con_canje($datos_puntos)){
    			$reg_success = FALSE;
    		}elseif(!$this->model_cliente->m_iniciar_nuevo_puntaje($datos_puntos)){
    			$reg_success = FALSE;
    		}
    		
    	}elseif( !empty($allInputs['puntos_ganados']) ){ // SI SOLO ACUMULA PUNTOS SIN CANJEAR
			if( !$this->model_cliente->m_actualizar_puntaje_cliente($datos_puntos) ){
				$reg_success = FALSE;
			}
		}
		$allInputs['convenio'] = FALSE; 
		// REGISTRAR CABECERA
		if( $this->model_venta_farmacia->m_registrar_venta($allInputs) && $reg_success ){
			
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento');
			if( ($allInputs['idmediopago'] == 6) ){
				foreach ($allInputs['pagoMixto'] as $row) {
					if( !empty($row['monto']) ){
						$row['monto'] = floatval($row['monto']);
						$row['idmovimiento'] = $allInputs['idmovimiento'];
						$this->model_venta_farmacia->m_registrar_pago_mixto($row);
					}
				}
			}
			// MARCAR EL MOVIMIENTO ORIGEN - ACUENTA - COMO CANCELADO
			if( $allInputs['esPreparado'] && !$allInputs['boolSolicitud'] ){
				$this->model_venta_farmacia->m_actualizar_estado_movimiento_origen($allInputs);
			}
			// SI ES MEDICO SIN CODIGO JJ
			if( $allInputs['esPreparado'] && $allInputs['boolSolicitud'] && $arrConfig['serie_formula'] <> 0 ){
				if( $allInputs['medico']['codigo_jj'] == NULL ){
					$ultimoCodigo = $this->model_empleado->m_cargar_ultimo_codigo_medico();
					$correlativo = 0;
					if(!empty($ultimoCodigo)){
				    	$correlativo = mb_substr($ultimoCodigo,1);
					}
			    	$allInputs['medico']['codigo_jj'] = 'V' . str_pad(((int)$correlativo + 1), 3, '0', STR_PAD_LEFT);
			    	$this->model_empleado->m_asignar_codigo_jj($allInputs['medico']);
				}
			}
			// REGISTRAR DETALLE medico
			$rowAux = array();
			foreach ($allInputs['detalle'] as $key => $row) { 
				if( empty($row['tiene_convenio_detalle']) ){ 
					$row['tiene_convenio_detalle'] = 2;
				}
				if( empty($row['tiene_convenio_detalle_efectivo']) ){ 
					$row['tiene_convenio_detalle_efectivo'] = 2;
				}
				$row['idmovimiento'] = $allInputs['idmovimiento'];
				if( $allInputs['esPreparado'] ){
					$row['estado_preparado'] = 1;
				}
				if( $this->model_venta_farmacia->m_registrar_detalle($row) ) { 
					if( $row['idrecetamedicamento'] ){
						$this->model_receta_medica->m_actualizar_atencion_receta_medicamento($row['idrecetamedicamento']);
					}
					if(!$tieneDescuento && $arrConfig['modo_venta_far'] == 'VN' && !$allInputs['esPreparado'] ){ // SI NO TIENE DESCUENTO Y ES UNA VENTA NORMAL
						// CALCULAR STOCK DEL ALMACEN - MEDICAMENTO 
						$row['stock_salidas'] = $row['cantidad'];
						$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida($row);

						// CALCULAR STOCK DEL MEDICAMENTO 
						$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['id']);
			    		if(!(empty($listaMedicamento))){ 
			    			$rowAux['stock_actual_modificado'] = 0;
			    			foreach ($listaMedicamento as $key => $rowLM) {
			    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
			    			}
			    			$rowAux['idmedicamento'] = $row['id'];
							if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
				    			$arrData['message'] = 'Se registraron los datos correctamente';
								$arrData['flag'] = 1;
				    		}
			    		}
					}
					// SOLO PREPARADOS Y FORMULAS
					if( $allInputs['esPreparado'] && $allInputs['boolSolicitud'] ){
						$row['estado_detalle_sol'] = 2; // 0:anulado; 1: disponible para venta 2: No disponible (ya fue utilizado en una venta)
						$this->model_solicitud_formula->m_actualizar_estado_detalle_solicitud($row);
						// SI ES UNA FORMULA NUEVA LE ASIGNAMOS LA FECHA ACTUAL, PARA QUE SALGA EN EL REPORTE EXCEL
						if( $row['fecha_asigna_idformula_jj'] == NULL && $arrConfig['serie_formula'] <> 0 ){
							$this->model_medicamento->m_asignar_fecha_formula_nueva($row);
						}
					}
					
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}
			if($arrConfig['modo_venta_far'] == 'VN'){
				if( $arrData['flag'] === 1 ){ 
					//Actualizamos el número de serie 
					$params = array(
						'idcajamaster' => $allInputs['idcajamaster'],
						'idtipodocumento' => $allInputs['idtipodocumento'],
						'numeroserie' => ($allInputs['numero_serie'] + 1)
					);
					$this->model_caja->m_editar_numero_serie($params);
				}
			}
			
			// SI TIENE DESCUENTO LO PONEMOS EN ESPERA 
			if($tieneDescuento) { 
				$this->model_venta_farmacia->m_editar_venta_a_espera($allInputs['idmovimiento']); 
			}
			// SI ES UN PEDIDO LO PONEMOS EN ESPERA
			if($arrConfig['modo_venta_far'] == 'VP') { 
				$this->model_venta_farmacia->m_editar_venta_pedido_a_espera($allInputs['idmovimiento']); 
			}
			$arrData['idventaregister'] = $allInputs['idmovimiento'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/*public function registrar_venta_temporal()	//  registra VENTA NORMAL y registra el PEDIDO DE VENTA
	{
		$arrConfig = obtener_parametros_configuracion();
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idventaregister'] = NULL;
    	// VALIDAR ORDEN DE VENTA 
    	if($arrConfig['modo_venta_far'] == 'VN'){		// VENTA NORMAL
	    	if( empty($allInputs['ticket']) ){ 
	    		$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}
	    	if( empty($allInputs['orden']) ){ 
	    		$arrData['message'] = 'No se ha generado una ORDEN DE VENTA. Genere la ORDEN DE VENTA';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}
	    	$arrFilters = array( 
	    		'searchColumn' => 'orden_venta',
	    		'searchText' => $allInputs['orden']
	    	);
	    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters);
	    	if( !empty($fVenta) ){
	    		$arrData['message'] = 'Ya se a registrado una venta, usando la orden <strong>'.$allInputs['orden'].'</strong>';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}	

    	}else{		// VENTA CON PEDIDO
    		if( empty($allInputs['orden_pedido']) ){ 
	    		$arrData['message'] = 'No se ha generado una ORDEN DE PEDIDO. Genere la ORDEN DE PEDIDO';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}
	    	$arrFilters = array( 
	    		'searchColumn' => 'orden_pedido',
	    		'searchText' => $allInputs['orden_pedido']
	    	);
	    	$fVenta = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters);
	    	if( !empty($fVenta) ){
	    		$arrData['message'] = 'Ya se a registrado un pedido, usando la orden <strong>'.$allInputs['orden_pedido'].'</strong>';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
	    	}
	    	$allInputs['iduserpedido'] = $this->sessionHospital['idusers'];
    	}
    	// if( empty($allInputs['ticket']) ){ 
    	// 	$arrData['message'] = 'No se ha generado un TICKET. Genere el N° DE TICKET';
    	// 	$arrData['flag'] = 0;
    	// 	$this->output
		   //  	->set_content_type('application/json')
		   //  	->set_output(json_encode($arrData));
		   //  return;
    	// }
    	$hayEnStock = TRUE;
    	foreach ($allInputs['detalle'] as $row) { 
    		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($row['idmedicamentoalmacen']); 
   			if( $row['cantidad'] > $fProducto['stock_temporal'] ) { 
   				$hayEnStock = FALSE; 
   				break;
   			}
    	}
    	if( $hayEnStock === FALSE ){ 
    		$arrData['message'] = 'Stock Agotado para el producto: <b>'.strtoupper($fProducto['medicamento']).'</b>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// COMPROBAR SI HAY DESCUENTO 
    	$tieneDescuento = FALSE;
    	foreach ($allInputs['detalle'] as $key => $detalle) {
    		if( !empty($detalle['descuento']) && $detalle['descuento'] > 0 ){ 
				$tieneDescuento = TRUE;
				break;
			}
    	}
    	//if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
    		$allInputs['idsedeempresaadmin'] = $this->sessionHospital['idsedeempresaadmin'];
    		$allInputs['idalmacen'] = $this->sessionHospital['idalmacenfarmacia'];
    		$allInputs['idsubalmacen'] = $this->sessionHospital['idsubalmacenfarmacia'];
    	//}
    	// CALCULAR PUNTOS
    	if( empty($allInputs['cliente_afiliado']) ){
	    	if( !empty($allInputs['numero_documento_afiliado']) ){
	    		$allInputs['cliente_afiliado']['num_documento'] = $allInputs['numero_documento_afiliado'];
				if( $cliente_afiliado = $this->model_cliente->m_comprobar_afiliacion_puntos($allInputs['cliente_afiliado']) ){
					$allInputs['cliente_afiliado'] = $cliente_afiliado;
				}else{
					$arrData['message'] = 'Cliente con DNI: <b>'.$allInputs['numero_documento_afiliado'].'</b> No esta afiliado al sistema de Puntos';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
				}   		
	    	}
    	}else{
    		if( !$this->model_cliente->m_comprobar_afiliacion_puntos($allInputs['cliente_afiliado']) ){
				$arrData['message'] = 'Cliente con DNI: <b>'.$allInputs['cliente_afiliado']['num_documento'].'</b> No esta afiliado al sistema de Puntos';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
			}
    	}
    	

    	$empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['idsedeempresaadmin']);
    	if( $empresa_admin['cantidad_puntos'] == 0 || $empresa_admin['cantidad_puntos'] == NULL ||
    			$empresa_admin['cantidad_soles'] == 0 || $empresa_admin['cantidad_soles'] == NULL ){
    		$sistema_puntos = FALSE;
    	}else{
    		$sistema_puntos = TRUE;
    	}

    	if( !empty($allInputs['cliente_afiliado']) ){
    		if( !$sistema_puntos ){
    			$arrData['message'] = 'La Empresa <b>'. $empresa_admin['razon_social'] .'</b> No tiene configurado un Sistema de Puntos';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
    		}else{
    			$allInputs['puntos_ganados'] = intval($allInputs['total']) * $empresa_admin['cantidad_puntos'] / $empresa_admin['cantidad_soles'];
    			$allInputs['puntos_no_ganados'] = NULL;
    		}
    		
    	}else{
    		if( $sistema_puntos ){
    			$allInputs['puntos_no_ganados'] = intval($allInputs['total']) * $empresa_admin['cantidad_puntos'] / $empresa_admin['cantidad_soles'];
    			$allInputs['puntos_ganados'] = NULL;
    		}
    	}
    	// var_dump($allInputs); exit(); 
    	$this->db->trans_start();
		if($this->model_venta_farmacia->m_registrar_venta($allInputs)){ // REGISTRAR CABECERA 
			
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
			$rowAux = array();
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idmovimiento'] = $allInputs['idmovimiento'];

				if( $this->model_venta_farmacia->m_registrar_detalle($row) ) { 
					if(!$tieneDescuento && $arrConfig['modo_venta_far'] == 'VN'){ 	// SIEMPRE SERA SIN DESCUENTO POR SER TEMPORAL
						// CALCULAR STOCK DEL ALMACEN - MEDICAMENTO 
						$row['stock_salidas'] = $row['cantidad'];
						$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida_temporal($row);
					//  CALCULAR STOCK DEL MEDICAMENTO NO VA ESTO --- SE MODIFICA EL STOCK GENERAL CUANDO SE APRUEBE LA VENTA TEMPORAL
				 	//	$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['id']);
			     	//	if(!(empty($listaMedicamento))){ 
			     	//		$rowAux['stock_actual_modificado'] = 0;
			     	//		foreach ($listaMedicamento as $key => $rowLM) {
			     	//			$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
			     	//		}
			     	//		
			     	// 		$rowAux['idmedicamento'] = $row['id'];
			     	// 		if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
				   	// 			$arrData['message'] = 'Se registraron los datos correctamente';
					// 			$arrData['flag'] = 1;
					// 		}
			     	//	}
					}
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}
			if($arrConfig['modo_venta_far'] == 'VN'){
				if( $arrData['flag'] === 1 ){ 
					//Actualizamos el número de serie 
					$params = array(
						'idcajamaster' => $allInputs['idcajamaster'],
						'idtipodocumento' => $allInputs['idtipodocumento'],
						'numeroserie' => ($allInputs['numero_serie'] + 1)
					);
					$this->model_caja->m_editar_numero_serie($params);
				}
			}
			// SI ES UN PEDIDO LO PONEMOS EN ESPERA
			if($arrConfig['modo_venta_far'] == 'VP') { 
				$this->model_venta_farmacia->m_editar_venta_pedido_a_espera($allInputs['idmovimiento']); 
			}
			$arrData['idventaregister'] = $allInputs['idmovimiento'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}*/
	public function imprimir_ticket_venta() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$idventa = @$allInputs[0]['id'];
		// if(!empty($allInputs['es_preparado'])){
		// 	if($allInputs['es_preparado']){
		// 	$esPreparado = TRUE;
		// 	} else {
		// 		$esPreparado = FALSE;
		// 	}

		// }	
		if(!empty($allInputs['id'])){ 
			$idventa = $allInputs['id'];
		}
    	$arrData['flag'] = 1;
    	$arrData['html'] = '';
    	// $arrData['es_preparado'] = $esPreparado;
    	$arrParams = array(
    		'searchText' => $idventa,
    		'searchColumn' => 'fm.idmovimiento',
    		// 'es_preparado' => $esPreparado
    	);
    	// var_dump($allInputs);exit();
    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna( $arrParams );
    	$listaDetalles = $this->model_venta_farmacia->m_cargar_esta_venta_con_detalle_por_columna( $arrParams );

    	/* VALIDACIONES */ 
    	if( empty($fVenta) ) {
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}

		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			if( $fVenta['estado_movimiento'] == 3 ){ // MOVIMIENTO SIN APROBAR 
	    		$arrData['flag'] = 3;
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
		// var_dump($allInputs); exit();
		// var_dump($fVenta['idventaorigen']);exit();
		if($allInputs['es_preparado']){
			if($fVenta['saldo'] == NULL && $fVenta['idventaorigen'] == NULL){//SIGNIFICA QUE CANCELÓ EN UN SOLO PAGO | CASO 1
				$htmlData = $this->generateHTMLTicketPreparadoFarmaciaCaso1($fVenta,$listaDetalles,$idventa);
			} else if ($fVenta['saldo'] != NULL && $fVenta['idventaorigen'] == NULL) {//SIGNIFICA QUE CANCELÓ A CUENTA POR PRIMERA VEZ | CASO 2A
				$htmlData = $this->generateHTMLTicketPreparadoFarmaciaCaso2A($fVenta,$listaDetalles,$idventa);
			} else if($fVenta['saldo'] == NULL && $fVenta['idventaorigen'] != NULL) {//SIGNIFICA QUE CANCELÓ DESPUÉS DE PAGAR A CUENTA | CASO 2B 
				// var_dump($fVenta['idventaorigen'],'jss'); exit(); 
				$dataMovimientoAnterior = $this->model_venta_farmacia->m_cargar_movimiento_anterior( $fVenta['idventaorigen'] );
				// var_dump($fVenta,'jss'); exit(); 
				$htmlData = $this->generateHTMLTicketPreparadoFarmaciaCaso2B($fVenta, $listaDetalles, $idventa, $dataMovimientoAnterior);
			}
			
		} else {
			$htmlData = $this->generateHTMLTicketFarmaciaS2($fVenta,$listaDetalles,$idventa);
		}
		if( $fVenta['tiene_impresion'] == 2) { 
			$this->model_venta_farmacia->m_editar_venta_a_impreso($idventa); 
		}
		if( $fVenta['tiene_impresion'] == 1 && $fVenta['solicita_impresion'] == 3 ){ // VENTA YA HA SIDO IMPRESA Y SE ACEPTÓ LA SOLICITUD DE REIMPRESION 
			$this->model_venta_farmacia->m_editar_venta_a_reimpreso($idventa);
			$this->model_venta_farmacia->m_editar_venta_a_sin_solicitud_impresion($idventa); // VOLVER A ACTUALIZAR A SIN SOLICITUD DE IMPRESION
		}
		$arrData['html'] = $htmlData['html'];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function generateHTMLTicketFarmaciaS1($fVenta,$listaDetalles,$idventa)
	{
		$htmlData = '<table>';
    	$arrParams = array(
    		'searchText' => $idventa,
    		'searchColumn' => 'fm.idmovimiento'
    	);
    	
    	if( empty($fVenta) ) { 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			if( $fVenta['estado_movimiento'] == 3 ){ // MOVIMIENTO SIN APROBAR 
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
		$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($fVenta);
		$descuento = 0;
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';
		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_movimiento']).' </td> </tr>';
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total(substr($fVenta['nombres_vendedor'], 0,1) . $fVenta['apellido_vendedor']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';
		if( !empty($fVenta['idcliente_afiliado']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente_afiliado'] . ' UD. GANÓ ' . $fVenta['puntos_ganados']. ' PUNTO(S). 
			ACUM. ' . $datos_puntos['puntos_acumulados'] . ' PUNTO(S).</td></tr>';
		}else{
			if( $fVenta['puntos_no_ganados'] != 0){
				$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
				$htmlData .= '<tr> <td colspan="2"> UD. PUDO HABER GANADO ' . $fVenta['puntos_no_ganados']. ' PUNTO(S).';
			}
			// DEJA PROGRAMAR MIERCOLES¡¡ >< 
		}
		if( !empty($fVenta['idcliente']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente'];
			
		}
		if($fVenta['idempresacliente'] != NULL){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper_total($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:30px"> <td colspan ="2" ></td> </tr>';
		$htmlData .= '</table>'; 

		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;">
			<td colspan="2" style="padding: 0px 6px; font-size:1.1em"> Descripción </td>
			<td width="60" align="right" style="padding-right: 6px; font-size:1.1em"> Importe </td>
		</tr>';
		$htmlData .= '<tr style="font-weight:bold;">
			<td></td>
			<td align="center" style="font-size:1.1em"> Cantidad  X  P. Unitario</td>
			<td ></td>
		</tr>';
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		foreach ($listaDetalles as $row) {
		 	if($row['idmedicamento'] == 0){
		 		$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' </td>
					<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($row['descuento_asignado'], 2) .' </td>
				</tr>';
			}else{
				$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' / <span style="font-size:0.6em">' . substr($row['nombre_lab'], 0,20) .'</span> </td>
					<td width="60" align="right" style="padding-right: 6px;"> '. number_format($row['cantidad'] * $row['precio_unitario'], 2) .' </td>
				</tr>';
				if( $row['descripcion_ff'] == 'NO APLICA'){ 
					$row['descripcion_ff'] = '';
				}
				$htmlData .= '<tr style="height:30px">
					<td></td>
					<td style="text-align: center;"> '.$row['cantidad'].'F ' . $row['descripcion_ff'] . ' X '.$row['precio_unitario'] .'</td>

					<td ></td>
				</tr>';
				$descuento += $row['descuento_asignado'];	
			}
		}
		if( $descuento > 0 ){
			$htmlData .= '<tr style="height:40px">
				<td colspan="2" style="padding: 0px 6px;"> DESCUENTO </td>
				<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($descuento, 2) .' </td>
			</tr>';
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '</table>';
		$htmlData .= '<table>';
		$htmlData .= '<tr> <td style="text-align: justify;"> INAFECTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['total_igv_exonerado'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['sub_total'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['total_igv'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"><b> IMPORTE TOTAL </b></td><td><b>S/. </b></td>
			<td width="105" align="right" style="padding-right: 6px;"><b> '.number_format($fVenta['total_sin_redondeo'],2).'</b> </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> REDONDEO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['redondeo'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format($fVenta['total_a_pagar'],2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
		// $total=1234; 
		// $monto = new EnLetras();
		$con_letra = ValorEnLetras($fVenta['total_a_pagar'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
		$htmlData .= '<tfoot>'; 
		$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO CAMBIO O DEVOLUCION DE DINERO SE HARA DENTRO DE LAS 24 HORAS PREVIA PRESENTACION DEL COMPROBANTE. </td> </tr>'; 

		if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
		}
		$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
		$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
		$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';
		return array( 
			'html'=> $htmlData
		);
	}
	public function generateHTMLTicketFarmaciaS2($fVenta,$listaDetalles,$idventa)
	{
		$htmlData = '<table>';
		// var_dump($htmlData ,$fVenta,$listaDetalles,$idventa); exit();
		$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($fVenta);
		$descuento = 0;
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';
		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_movimiento']).' </td> </tr>';
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total(substr($fVenta['nombres_vendedor'], 0,1) . $fVenta['apellido_vendedor']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';
		if( !empty($fVenta['idcliente_afiliado']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente_afiliado'] . ' UD. GANÓ ' . $fVenta['puntos_ganados']. ' PUNTO(S). 
			ACUM. ' . $datos_puntos['puntos_acumulados'] . ' PUNTO(S).</td></tr>';
		}else{
			if( $fVenta['puntos_no_ganados'] != 0){
				$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
				$htmlData .= '<tr> <td colspan="2"> UD. PUDO HABER GANADO ' . $fVenta['puntos_no_ganados']. ' PUNTO(S).';
			}
			// DEJA PROGRAMAR MIERCOLES¡¡ >< 
		}
		if( !empty($fVenta['idcliente']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente'];
			
		}
		if($fVenta['idempresacliente'] != NULL){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper_total($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:30px"> <td colspan ="2" ></td> </tr>';
		$htmlData .= '</table>'; 

		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;">
			<td colspan="2" style="padding: 0px 6px; font-size:1.1em"> Descripción </td>
			<td width="60" align="right" style="padding-right: 6px; font-size:1.1em"> Importe </td>
		</tr>';
		$htmlData .= '<tr style="font-weight:bold;">
			<td></td>
			<td align="center" style="font-size:1.1em"> Cantidad  X  P. Unitario</td>
			<td ></td>
		</tr>';
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		foreach ($listaDetalles as $row) {
		 	if($row['idmedicamento'] == 0){
		 		$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' </td>
					<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($row['descuento_asignado'], 2) .' </td>
				</tr>';
			}else{
				$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' / <span style="font-size:0.6em">' . substr($row['nombre_lab'], 0,20) .'</span> </td>
					<td width="60" align="right" style="padding-right: 6px;"> '. number_format($row['cantidad'] * $row['precio_unitario'], 2) .' </td>
				</tr>';
				if( $row['descripcion_ff'] == 'NO APLICA'){ 
					$row['descripcion_ff'] = '';
				}
				$htmlData .= '<tr style="height:30px">
					<td></td>
					<td style="text-align: center;"> '.$row['cantidad'].'F ' . $row['descripcion_ff'] . ' X '.$row['precio_unitario'] .'</td>

					<td ></td>
				</tr>';
				$descuento += $row['descuento_asignado'];	
			}
		}
		if( $descuento > 0 ){
			$htmlData .= '<tr style="height:40px"> 
				<td colspan="2" style="padding: 0px 6px;"> DESCUENTO </td> 
				<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($descuento, 2) .' </td> 
			</tr>'; 
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '</table>';
		$htmlData .= '<table>';
		/* LOGICA DE IGV PARA ESTILO DE TICKET "S2" */
		$montoExonerado = $fVenta['total_monto_exonerado'];
		$totalSinExonerado = $fVenta['total_a_pagar'] - $montoExonerado;
		$subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
		$igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);
		$htmlData .= '<tr> <td style="text-align: justify;"> INAFECTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($montoExonerado,2).' </td></tr>'; 
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($subTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($igvTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"><b> IMPORTE TOTAL </b></td><td><b>S/. </b></td>
			<td width="105" align="right" style="padding-right: 6px;"><b> '.number_format($fVenta['total_sin_redondeo'],2).'</b> </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> REDONDEO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['redondeo'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format($fVenta['total_a_pagar'],2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
		// $total=1234; 
		// $monto = new EnLetras();
		$con_letra = ValorEnLetras($fVenta['total_a_pagar'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		
		if( !empty($fVenta['vuelto']) ){
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			// $htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> EFECTIVO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format(($fVenta['total_a_pagar'] + $fVenta['vuelto']),2).' </td></tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> VUELTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['vuelto'],2).' </td></tr>';
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tfoot>'; 
			$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO CAMBIO O DEVOLUCION DE DINERO SE HARA DENTRO DE LAS 24 HORAS PREVIA PRESENTACION DEL COMPROBANTE. </td> </tr>'; 
			
			if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
				$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
				$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
			}
			$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
			$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
			$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';
		return array( 
			'html'=> $htmlData
		);
	}
	public function generateHTMLTicketPreparadoFarmaciaCaso1($fVenta,$listaDetalles,$idventa) // compra crema y paga todo 
	{
		$htmlData = '<table>';
		$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($fVenta);
		$descuento = 0;
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';
		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_movimiento']).' </td> </tr>';
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total(substr($fVenta['nombres_vendedor'], 0,1) . $fVenta['apellido_vendedor']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';
		$htmlData .= '<tr> <td> N° SOLICITUD </td> <td> : '.$fVenta['idsolicitudformula'].' </td> </tr>';
		//$htmlData .= '<tr> <td> N° PEDIDO </td> <td> : '.$fVenta['codigo_pedido'].' </td> </tr>';
		
		if( !empty($fVenta['idcliente']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente'];
			
		}
		if($fVenta['idempresacliente'] != NULL){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper_total($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:30px"> <td colspan ="2" ></td> </tr>';
		$htmlData .= '</table>'; 

		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;">
			<td colspan="2" style="padding: 0px 6px; font-size:1.1em"> Descripción </td>
			<td width="60" align="right" style="padding-right: 6px; font-size:1.1em"> Importe </td>
		</tr>';
		$htmlData .= '<tr style="font-weight:bold;">
			<td></td>
			<td align="center" style="font-size:1.1em"> Cantidad  X  P. Unitario</td>
			<td ></td>
		</tr>';
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		foreach ($listaDetalles as $row) {
		 	if($row['idmedicamento'] == 0){
		 		$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' </td>
					<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($row['descuento_asignado'], 2) .' </td>
				</tr>';
			}else{
				$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].'  </td>
					<td width="60" align="right" style="padding-right: 6px;"> '. number_format($row['cantidad'] * $row['precio_unitario'], 2) .' </td>
				</tr>';
				
				$htmlData .= '<tr style="height:30px">
					<td></td>
					<td style="text-align: center;"> '.$row['cantidad'].' - '.$row['precio_unitario'] .'</td>

					<td ></td>
				</tr>';
				$descuento += $row['descuento_asignado'];	
			}
		}
		if( $descuento > 0 ){
			$htmlData .= '<tr style="height:40px"> 
				<td colspan="2" style="padding: 0px 6px;"> DESCUENTO </td> 
				<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($descuento, 2) .' </td> 
			</tr>'; 
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '</table>';
		$htmlData .= '<table>';
		/* LOGICA DE IGV PARA ESTILO DE TICKET "S2" */
		$montoExonerado = $fVenta['total_monto_exonerado'];
		$totalSinExonerado = $fVenta['total_a_pagar'] - $montoExonerado;
		$subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
		$igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);
		$htmlData .= '<tr> <td style="text-align: justify;"> INAFECTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($montoExonerado,2).' </td></tr>'; 
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($subTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($igvTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"><b> IMPORTE TOTAL </b></td><td><b>S/. </b></td>
			<td width="105" align="right" style="padding-right: 6px;"><b> '.number_format($fVenta['total_sin_redondeo'],2).'</b> </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> REDONDEO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['redondeo'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format($fVenta['total_a_pagar'],2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';

		$con_letra = ValorEnLetras($fVenta['total_a_pagar'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		
		if( !empty($fVenta['vuelto']) ){
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			// $htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> EFECTIVO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format(($fVenta['total_a_pagar'] + $fVenta['vuelto']),2).' </td></tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> VUELTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['vuelto'],2).' </td></tr>';
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tfoot>'; 
			$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO PREPARADO SE RECOGERÁ EN UN PLAZO DE 7 DÍAS DE LO CONTRARIO SERÁ DEVUELTO AL LABORATORIO SIN RECLAMO ALGUNO. </td> </tr>'; 
			
			if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
				$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
				$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
			}
			$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
			$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
			$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';
		return array( 
			'html'=> $htmlData
		);
	}
	public function generateHTMLTicketPreparadoFarmaciaCaso2A($fVenta,$listaDetalles,$idventa) // 
	{
		$htmlData = '<table>';
		$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($fVenta);
		$descuento = 0;
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';
		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_movimiento']).' </td> </tr>';
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total(substr($fVenta['nombres_vendedor'], 0,1) . $fVenta['apellido_vendedor']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';
		$htmlData .= '<tr> <td> N° SOLICITUD </td> <td> : '.$fVenta['idsolicitudformula'].' </td> </tr>';
		//$htmlData .= '<tr> <td> N° PEDIDO </td> <td> : '.$fVenta['codigo_pedido'].' </td> </tr>';

		if( !empty($fVenta['idcliente']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente'];
			
		}
		if($fVenta['idempresacliente'] != NULL){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper_total($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:30px"> <td colspan ="2" ></td> </tr>';
		$htmlData .= '</table>'; 

		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;">
			<td colspan="2" style="padding: 0px 6px; font-size:1.1em"> Descripción </td>
			<td width="60" align="right" style="padding-right: 6px; font-size:1.1em"> Importe </td>
		</tr>';
		$htmlData .= '<tr style="font-weight:bold;">
			<td></td>
			<td align="center" style="font-size:1.1em"></td>
			<td ></td>
		</tr>';
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;">PAGÓ A CUENTA </td>
					<td width="60" align="right" style="padding-right: 6px;">'. number_format($fVenta['total_a_pagar'], 2) .' </td>
				</tr>';
		if( $descuento > 0 ){
			$htmlData .= '<tr style="height:40px"> 
				<td colspan="2" style="padding: 0px 6px;"> DESCUENTO </td> 
				<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($descuento, 2) .' </td> 
			</tr>'; 
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '</table>';
		$htmlData .= '<table>';
		/* LOGICA DE IGV PARA ESTILO DE TICKET "S2" */
		$montoExonerado = $fVenta['total_monto_exonerado'];
		$totalSinExonerado = $fVenta['total_a_pagar'] - $montoExonerado;
		$subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
		$igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);
		
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>'.number_format( $fVenta['total_a_pagar'], 2).'</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';

		$con_letra = ValorEnLetras($fVenta['total_a_pagar'],"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';

		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';

		$htmlData .= '<tr> <td style="text-align: justify;"> SALDO PENDIENTE </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '. number_format( $fVenta['saldo'], 2).' </td></tr>';
		
		if( !empty($fVenta['vuelto']) ){
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			// $htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> EFECTIVO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format(($fVenta['total_a_pagar'] + $fVenta['vuelto']),2).' </td></tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> VUELTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['vuelto'],2).' </td></tr>';
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tfoot>'; 
			$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO PREPARADO SE RECOGERÁ EN UN PLAZO DE 7 DÍAS DE LO CONTRARIO SERÁ DEVUELTO AL LABORATORIO SIN RECLAMO ALGUNO. </td> </tr>'; 
			
			if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
				$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
				$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
			}
			$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
			$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
			$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';
		return array( 
			'html'=> $htmlData
		);
	}
	public function generateHTMLTicketPreparadoFarmaciaCaso2B($fVenta,$listaDetalles,$idventa, $dataMovimientoAnterior)
	{
		$htmlData = '<table>';
		$datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($fVenta);
		$descuento = 0;
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1.5em;"> '.strtoupper_total($fVenta['nombre_legal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 1em;"> '.strtoupper_total($fVenta['empresa']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.8em;"> RUC: '.$fVenta['ruc'].' </td> </tr>';

		$htmlData .= '<tr> <td colspan ="2" align="center" style="font-size: 0.6em;padding-bottom: 2em;">'.strtoupper_total($fVenta['domicilio_fiscal']).' </td> </tr>';
		$htmlData .= '<tr> <td colspan ="2" ></td> </tr>';
		$htmlData .= '<tr> <td> LOCAL. COMERCIAL </td> <td style="font-size: 0.8em;"> : '.strtoupper_total($fVenta['direccion_se']).' </td> </tr>';
		$htmlData .= '<tr> <td> MAQ. REG. </td> <td style="font-size: 0.8em;"> : ' . $fVenta['maquina_registradora'] . ' </td> </tr>';
		$htmlData .= '<tr> <td> FECHA </td> <td> : '.formatoFechaReporte4($fVenta['fecha_movimiento']).' </td> </tr>';
		$htmlData .= '<tr> <td> ORDEN </td> <td> : '.$fVenta['orden_venta'].' </td> </tr>'; 
		$htmlData .= '<tr> <td> CAJA </td> <td> : '.$fVenta['numero_caja'].' </td> </tr>';
		$htmlData .= '<tr> <td> CAJERO </td> <td> : '.strtoupper_total(substr($fVenta['nombres_vendedor'], 0,1) . $fVenta['apellido_vendedor']).'</td> </tr>';
		$htmlData .= '<tr> <td> '.$fVenta['descripcion_td'].' </td> <td> : '.$fVenta['ticket_venta'].' </td> </tr>';
		$htmlData .= '<tr> <td> N° SOLICITUD </td> <td> : '.$fVenta['idsolicitudformula'].' </td> </tr>';
		//$htmlData .= '<tr> <td> N° PEDIDO </td> <td> : '.$dataMovimientoAnterior['codigo_pedido'].' </td> </tr>';

		if( !empty($fVenta['idcliente']) ){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td colspan="2"> SR(A). ' . $fVenta['cliente'];
			
		}
		if($fVenta['idempresacliente'] != NULL){
			$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
			$htmlData .= '<tr> <td> RUC </td> <td> : '.$fVenta['ruc_cliente'].' </td> </tr>';
			$htmlData .= '<tr> <td> RAZON SOCIAL </td> <td> : '.strtoupper_total($fVenta['empresa_cliente']).' </td> </tr>';
		}
		
		$htmlData .= '<tr> <td colspan="2"> ======================================== </td> </tr>';
		$htmlData .= '<tr style="height:30px"> <td colspan ="2" ></td> </tr>';
		$htmlData .= '</table>'; 

		$htmlData .= '<table>'; 
		$htmlData .= '<tr style="font-weight:bold;">
			<td colspan="2" style="padding: 0px 6px; font-size:1.1em"> Descripción </td>
			<td width="60" align="right" style="padding-right: 6px; font-size:1.1em"> Importe </td>
		</tr>';
		$htmlData .= '<tr style="font-weight:bold;">
			<td></td>
			<td align="center" style="font-size:1.1em"> Cantidad  X  P. Unitario</td>
			<td ></td>
		</tr>';
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		foreach ($listaDetalles as $row) {
		 	if($row['idmedicamento'] == 0){
		 		$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].' </td>
					<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($row['descuento_asignado'], 2) .' </td>
				</tr>';
			}else{
				$htmlData .= '<tr style="height:40px">
					<td colspan="2" style="padding: 0px 6px;"> '.$row['medicamento'].'  </td>
					<td width="60" align="right" style="padding-right: 6px;"> '. number_format($row['cantidad'] * $row['precio_unitario'], 2) .' </td>
				</tr>';
				
				$htmlData .= '<tr style="height:30px">
					<td></td>
					<td style="text-align: center;"> '.$row['cantidad'].' x '.$row['precio_unitario'] .'</td>

					<td ></td>
				</tr>';
				$descuento += $row['descuento_asignado'];	
			}
		}
		if( $descuento > 0 ){
			$htmlData .= '<tr style="height:40px"> 
				<td colspan="2" style="padding: 0px 6px;"> DESCUENTO </td> 
				<td width="60" align="right" style="padding-right: 6px;"> -'. number_format($descuento, 2) .' </td> 
			</tr>'; 
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>';
		$htmlData .= '</table>';
		$htmlData .= '<table>';
		/* LOGICA DE IGV PARA ESTILO DE TICKET "S2" */
		// $total_venta = $dataMovimientoAnterior['total_a_pagar'] + $fVenta['total_a_pagar'];
		// $montoExonerado = $fVenta['total_monto_exonerado'];
		// $totalSinExonerado = $total_venta - $montoExonerado;
		// $subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
		// $igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);

		/* LOGICA DE IGV PARA ESTILO DE TICKET "S2" Modificado por Luis */ 
		$total_venta = $fVenta['sub_total'] + $fVenta['total_igv'];
		$montoExonerado = $fVenta['total_monto_exonerado'];
		$totalSinExonerado = $total_venta - $montoExonerado;
		$subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
		$igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);

		$htmlData .= '<tr> <td style="text-align: justify;"> INAFECTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($montoExonerado,2).' </td></tr>'; 
		$htmlData .= '<tr> <td style="text-align: justify;"> SUBTOTAL </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($subTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> IGV </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($igvTotalSinExonerado,2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"><b> IMPORTE TOTAL </b></td><td><b>S/. </b></td>
			<td width="105" align="right" style="padding-right: 6px;"><b> '.number_format($fVenta['total_sin_redondeo'],2).'</b> </td></tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"><b> PAGÓ A CUENTA </b></td><td><b>S/. </b></td>
			<td width="105" align="right" style="padding-right: 6px;"><b> -'.number_format($dataMovimientoAnterior['total_a_pagar'], 2).'</b> </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify;"> REDONDEO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['redondeo'],2).' </td></tr>';
		$htmlData .= '<tr> <td style="text-align: justify; font-size:1.1em"> <b>IMPORTE A PAGAR</b> </td><td><b>S/.</b> </td>
			<td width="105" align="right" style="padding-right: 6px; font-size:1.1em"> <b>' . number_format($fVenta['total_a_pagar'], 2) . '</b> </td></tr>';
		$htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
		
		$con_letra = ValorEnLetras($total_venta,"SOLES"); 
		$htmlData .= '<tr><td colspan ="3" >SON: '.$con_letra.' </td> </tr>';
		
		if( !empty($fVenta['vuelto']) ){
			$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			// $htmlData .= '<tr style="height:30px;"><td colspan ="3" ></td> </tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> EFECTIVO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format(($fVenta['total_a_pagar'] + $fVenta['vuelto']),2).' </td></tr>';
			$htmlData .= '<tr> <td style="text-align: justify;"> VUELTO </td><td>S/. </td>
			<td width="105" align="right" style="padding-right: 6px;"> '.number_format($fVenta['vuelto'],2).' </td></tr>';
		}
		$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
			$htmlData .= '<tfoot>'; 
			$htmlData .= '<tr> <td colspan="3" style="text-align:center"> TODO PREPARADO SE RECOGERÁ EN UN PLAZO DE 7 DÍAS DE LO CONTRARIO SERÁ DEVUELTO AL LABORATORIO SIN RECLAMO ALGUNO. </td> </tr>'; 
			
			if( $fVenta['idtipodocumento'] == 3 /* OPERACION */ || $fVenta['idtipodocumento'] == 6 /* RECIBO */ ){ 
				$htmlData .= '<tr> <td colspan="3"> ======================================== </td> </tr>'; 
				$htmlData .= '<tr> <td colspan="3" style=""> ESTE DOCUMENTO PUEDE SER CANJEADO POR BOLETA DE VENTA O FACTURA </td> </tr>';
			}
			$htmlData .= '<tr style="height:30px"> <td colspan ="3" ></td> </tr>'; 
			$htmlData .= '<tr align="center" style="height:30px"> <td colspan ="3" > GRACIAS POR SU COMPRA </td> </tr>';
			$htmlData .= '</tfoot>'; 
		$htmlData .= '</table>';
		return array( 
			'html'=> $htmlData
		);
	}
	public function anular_venta_caja_actual()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$arrData['message'] = 'No se pudo anular la venta';
    	$arrData['flag'] = 0;
    	$arrFilters = array( 
    		'searchColumn' => 'orden_venta',
    		'searchText' => $allInputs[0]['orden']
    	);
    	$fVenta = $this->model_venta_farmacia->m_cargar_esta_venta_por_columna($arrFilters); 
    	if( empty($fVenta) ){
    		$arrData['message'] = 'No existe el registro, recargue el navegador y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $fVenta['estado_movimiento'] == 0 ){
    		$arrData['message'] = 'El movimiento ya ha sido anulado anteriormente.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$this->db->trans_start();
		if( $this->model_venta_farmacia->m_anular_venta_caja_actual( $allInputs[0]['id'] ) ){
			if( $fVenta['es_preparado'] == 1 ){
				$listaProductosStock = $this->model_venta_farmacia->m_cargar_preparados_almacen_de_esta_venta($allInputs[0]['id']);
			}else{
				$listaProductosStock = $this->model_venta_farmacia->m_cargar_productos_almacen_de_esta_venta($allInputs[0]['id']);
			}
			
			foreach ($listaProductosStock as $key => $row) {
				if( $row['idrecetamedicamento'] ){ // ANULACION DE RECETA ATENDIDA
					$this->model_receta_medica->m_actualizar_atencion_receta_medicamento($row['idrecetamedicamento'], 'A');
				}
				if( $fVenta['es_preparado'] == 1 ){
					$row['estado_detalle_sol'] = 1; // 0:anulado; 1: disponible para venta 2: No disponible (ya fue utilizado en una venta)
					if($this->model_solicitud_formula->m_actualizar_estado_detalle_solicitud($row)){
						$arrData['message'] = 'Se anuló la venta correctamente';
						$arrData['flag'] = 1;
					}


				}
				else{
					// CALCULAR STOCK DEL ALMACEN - MEDICAMENTO 
					$row['stock_salidas'] = $row['cantidad'];
					$this->model_medicamento_almacen->m_actualizar_stock_medicamento_almacen_salida($row,'A');
					// CALCULAR STOCK DEL MEDICAMENTO 
					$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['idmedicamento']);
		    		if( !(empty($listaMedicamento)) ) {
		    			$rowAux['stock_actual_modificado'] = 0;
		    			foreach ($listaMedicamento as $key => $rowLM) {
		    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
		    			}
		    			$rowAux['idmedicamento'] = $row['idmedicamento'];
						if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
			    			$arrData['message'] = 'Se anuló la venta correctamente';
							$arrData['flag'] = 1;
			    		}
		    		}	
				}
				
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_pedido_venta()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$arrData['message'] = 'No se pudo anular la venta';
    	$arrData['flag'] = 0;
    	$arrFilters = array( 
    		'searchColumn' => 'orden_pedido',
    		'searchText' => $allInputs[0]['orden_pedido']
    	);
    	$fVenta = $this->model_venta_farmacia->m_cargar_este_pedido_por_columna($arrFilters); 
    	//var_dump($fVenta); exit();
    	if( empty($fVenta) ){
    		$arrData['message'] = 'No existe el registro, recargue el navegador y vuelva a intentarlo.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $fVenta['estado_movimiento'] == 0 ){
    		$arrData['message'] = 'El movimiento ya ha sido anulado anteriormente.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $fVenta['es_pedido'] == 2 ){
    		$arrData['message'] = 'El Pedido no se puede anular porque ya se ha efectuado la venta.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$this->db->trans_start();
		if( $this->model_venta_farmacia->m_anular_venta_caja_actual( $allInputs[0]['id'] ) ){ // anula tanto venta como pedido
			$arrData['message'] = 'El Pedido se anuló correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
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
		$lista = $this->model_venta_farmacia->m_cargar_ventas_en_espera_caja_actual($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_ventas_en_espera_caja_actual($paramPaginate,$paramDatos);
		$arrListado = array();

		foreach ($lista as $row) { 
			//$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			// if(empty($strMedico)) {
			// 	$strMedico = '[SIN MÉDICO]'; 
			// }
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
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
					//'idmedico' => $row['idmedico'],
					//'medico' => $strMedico,
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
	public function anular_detalle_venta_pedido()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo eliminar el producto del pedido';
    	$arrData['flag'] = 0;

    	if( $this->model_venta_farmacia->m_anular_detalle_venta_pedido( $allInputs ) ){ // anula un item del detalle
    		// actualizar el total del pedido
			if( $this->model_venta_farmacia->m_actualizar_total_pedido( $allInputs ) ){
				$arrData['message'] = 'El Producto se anuló correctamente';
    			$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'No se pudo actualizar el total del movimiento';
    			$arrData['flag'] = 0;
			}
			
		}
    	//var_dump($allInputs); exit();


    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function actualizar_detalle_venta_pedido()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo actualizar los datos del pedido';
    	$arrData['flag'] = 0;
    	$hayEnStock = TRUE;
		$fProducto = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen($allInputs['idmedicamentoalmacen']); 
		if( $allInputs['cantidad'] > $fProducto['stock_actual_malm'] ) { 
			$hayEnStock = FALSE; 
		}
		
    	if( $hayEnStock === FALSE ){ 
    		$arrData['message'] = 'La cantidad ingresada supera el Stock actual: <b>' . $fProducto['stock_actual_malm'] . '</b>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( $this->model_venta_farmacia->m_actualizar_detalle_venta_pedido( $allInputs ) ){ // anula un item del detalle
    		// actualizar el total del pedido
			if( $this->model_venta_farmacia->m_actualizar_total_pedido( $allInputs ) ){
				$arrData['message'] = 'El Producto se actualizó correctamente';
    			$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'No se pudo actualizar el total del movimiento';
    			$arrData['flag'] = 0;
			}
			
		}
    	//var_dump($allInputs); exit();


    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_ventas_con_solicitud_impresion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_ventas_con_solicitud_impresion($paramPaginate,$paramDatos);
		$fila = $this->model_venta_farmacia->m_count_ventas_con_solicitud_impresion($paramPaginate,$paramDatos);
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
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_movimiento']),
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
					'caja' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'cajero' => strtoupper( $row['caj_nombre'].' '.$row['caj_apellido_pat'] ), 
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
    	$arrData['paginate']['totalRows'] = $fila['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
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
		if( $this->model_venta_farmacia->m_editar_venta_a_solicitud_impresion( $id ) ){ 
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
		if( $this->model_venta_farmacia->m_editar_venta_a_solicitud_impresion_aprobada( $allInputs[0]['id'] ) ){ 
			$arrData['message'] = 'Se aprobó la solicitud de re-impresión.';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	  /* ************* */
	 /*  PAGOS MIXTOS */
	/* ************* */
	public function listar_pago_mixto()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = $this->model_venta_farmacia->m_cargar_pago_mixto($allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'idpagomixto' => $row['idpagomixto'],
					'idmediopago' => $row['idmediopago'],
					'mediopago' => $row['descripcion_med'],
					'monto' => $row['monto_sf'],
				)
			);
		}

		$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_pago_mixto()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo editar';
    	$arrData['flag'] = 0;
    	$error = FALSE;

		// VERIFICAR SI LA CAJA ESTA ABIERTA O CERRADA - SOLO SI NO ES USUARIO DE SISTEMAS
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
			$id =$allInputs[0]['id'];
			$result = $this->model_venta_farmacia->m_verificar_caja_por_idmovimiento($id);
			if( !$result ){ // si la caja no esta abierta
				$arrData['message'] = 'No se pueden modificar los montos porque la caja ya está cerrada.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}
		
		$this->db->trans_start();
		foreach ($allInputs as $row) {
			$result = $this->model_venta_farmacia->m_editar_pago_mixto($row);
			if( !$result ){
    			$error = TRUE;
			}

		}
		if(!$error){
			$arrData['message'] = 'Los montos se editaron correctamente';
	    	$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	  /* ************* */
	 /*  PREPARADOS   */
	/* ************* */
	public function ver_popup_cargar_receta_preparados()
	{
		$this->load->view('ventaFarmacia/popup_cargar_receta_preparados');
	}
	public function lista_detalle_venta_formula()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_detalle_venta_formula_a_cuenta($paramPaginate,$paramDatos); 
		$totalRows = $this->model_venta_farmacia->m_count_sum_detalle_venta_formula_a_cuenta($paramPaginate,$paramDatos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {

			if( $row['estado_preparado'] == 1 || $row['estado_preparado'] == 4 || $row['estado_preparado'] == 3 ){ // PEDIDO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'SIN ENTREGAR';
			}
			if( $row['estado_preparado'] == 2 ){ // ENTREGADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'ENTREGADO';
			}
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'saldo' => $row['saldo'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'precio_unitario_sf' => $row['precio_unitario_sf'],
					'cantidad' => $row['cantidad'],
					'idcliente' => $row['idcliente'],
					// 'medico' => $row['medico'],
					'paciente' => $row['paciente'],
					'num_documento' => $row['num_documento'],
					'descuento' => $row['descuento_asignado'],
					'total_detalle' => $row['total_detalle'],
					'estado' => $objEstado,
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontraron datos';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_formulas_pagadas_para_recepcion()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_formulas_pagadas_para_recepcion($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_formulas_pagadas_para_recepcion($paramPaginate,$paramDatos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {
			if( $row['estado_acuenta'] == 1 ){ // PEDIDO 
				$objEstado['claseIcon'] = 'fa-warning';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'A CUENTA';
			}
			if( $row['estado_acuenta'] == 2 ){ // ENTREGADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'CANCELADO';
			}
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'idsolicitudformula' => $row['idsolicitudformula'],
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					// 'saldo' => $row['saldo'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'fecha_movimiento' => darFormatoDMYhora($row['fecha_movimiento']),
					'idmedicamento' => $row['idmedicamento'],
					'formula' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					// se comenta porque hace muy lenta la consulta, hay que colocar precio_costo en el far_detalle _movimiento
					//'precio_costo' => $row['precio_costo'],
					'cantidad' => $row['cantidad'],
					'idcliente' => $row['idcliente'],
					'medico' => $row['medico'],
					'cliente' => $row['paciente'],
					'num_documento' => $row['num_documento'],
					'telefono' => $row['telefono'],
					// 'descuento' => $row['descuento_asignado'],
					'total_detalle' => $row['total_detalle'],
					'codigo_pedido' => $row['codigo_pedido'],
					'estado' => $objEstado,
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	// var_dump($arrData); exit();
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontraron datos';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_formulas_recibidas()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_venta_farmacia->m_cargar_formulas_recibidas($paramPaginate,$paramDatos);
		$totalRows = $this->model_venta_farmacia->m_count_formulas_recibidas($paramPaginate,$paramDatos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {
			if($row['estado_preparado'] == 1 || $row['estado_preparado'] == 3 || empty($row['estado_preparado'])){// PEDIDO
				if( $row['estado_acuenta'] == 1 ){ // A CUENTA 
					$objEstado['claseIcon'] = 'fa-ban';
					$objEstado['claseLabel'] = 'label-warning';
					$objEstado['labelText'] = 'A CUENTA';
				}elseif( $row['estado_acuenta'] == 2 ){ // CANCELADO 
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-success';
					$objEstado['labelText'] = 'CANCELADO';
				}

			} elseif($row['estado_preparado'] == 2) { //ENTREGADO
				$objEstado['claseIcon'] = 'fa-thumbs-o-up';
				$objEstado['claseLabel'] = 'label-primary';
				$objEstado['labelText'] = 'ENTREGADO';
			} elseif( $row['estado_preparado'] == 4 ){ // RECIBIDO POR CONFIRMAR 
				$objEstado['claseIcon'] = 'fa-spinner fa-spin';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'POR CONFIRMAR';
			}

			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'idsolicitudformula' => $row['idsolicitudformula'],
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					// 'saldo' => $row['saldo'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'guia_remision' => $row['guia_remision'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'fecha_venta' => darFormatoDMYhora($row['fecha_movimiento']),
					'fecha_recepcion' => darFormatoDMYhora($row['fecha_recepcion']),
					'idmedicamento' => $row['idmedicamento'],
					'formula' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'precio_costo' => $row['precio_costo'],
					'cantidad' => $row['cantidad'],
					'idcliente' => $row['idcliente'],
					'medico' => $row['medico'],
					'cliente' => $row['paciente'],
					'num_documento' => $row['num_documento'],
					'telefono' => $row['telefono'],
					// 'descuento' => $row['descuento_asignado'],
					'costo_total' => floatval($row['cantidad']) * floatval($row['precio_costo']),
					'total_detalle' => $row['total_detalle'],
					'codigo_pedido' => $row['codigo_pedido'],
					'estado' => $objEstado,
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	// var_dump($arrData); exit();
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontraron datos';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function entregar_formula(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se entregaron las formulas correctamente';
    	$arrData['flag'] = 1;
    	
   		// VALIDACION
    	foreach ($allInputs as $row) {
    		$detalle = NULL;
    		$arrIdMovimientos[] = $row['idmovimiento'];
    		$detalle = $this->model_venta_farmacia->m_cargar_detalle_por_id($row['iddetallemovimiento']);
    		if( $detalle['estado_preparado'] == 2 ){
    			$arrData['message'] = 'Algunos items de la selección ya fueron entregados. Quítelos de la selección e inténtelo nuevamente';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
    		}

    		if( $detalle['estado_preparado'] == 4 ){
    			$arrData['message'] = 'Algunos items de la selección no tienen confirmación de recibido. Quítelos de la selección e inténtelo nuevamente';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
    		}
    	}
    	$arrIdMovimientos = array_unique($arrIdMovimientos);
    	$hayPendientesPago = $this->model_venta_farmacia->m_verificar_movimiento_pendiente_pago($arrIdMovimientos);
    	
    	if( $hayPendientesPago ){
    		$arrData['message'] = 'Algunos items de la selección están pendientes de pago. Quítelos de la selección e inténtelo nuevamente';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
		// var_dump($allInputs); exit();
    	$this->db->trans_start();
    	foreach ($allInputs as $row) {
			if( $this->model_venta_farmacia->m_entregar_formula($row) ){
				if( !($this->model_entrada_farmacia->m_actualizar_medicamento_almacen_salida($row)) ){
					
					$arrData['message'] = 'No se pudo recepcionar';
		    		$arrData['flag'] = 0;
		    	}
			}else{
				$arrData['message'] = 'No se pudo recepcionar';
		    	$arrData['flag'] = 0;
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>