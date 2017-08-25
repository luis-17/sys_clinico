 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OrdenCompra extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security','pdf_helper','contable_helper'));
		$this->load->model(array('model_orden_compra','model_config','model_entrada_farmacia', 'model_almacen_farmacia','model_proveedor_farmacia','model_areas_oc'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->load->library('fpdfext');
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_tipo_material_cbo() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_orden_compra->m_cargar_tipo_material_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idtipomaterial'], 
					'descripcion' => strtoupper($row['descripcion_tm']),
					'prefijo' => $row['prefijo']
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
	public function lista_orden_compra_cbo() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$lista = $this->model_orden_compra->m_cargar_orden_compra_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idmovimiento'], 
					'descripcion' => strtoupper($row['orden_compra']),
					'idmovimiento' => $row['idmovimiento'],
					'idproveedor' => $row['idproveedor'],
					'forma_pago' => (int)$row['forma_pago'],
					'moneda' => (int)$row['moneda'],
					'letras' => $row['letras'],
					'estado_movimiento' => $row['estado_movimiento'],
					'modo_igv' => $row['modo_igv'],
					'total_a_pagar' => $row['total_a_pagar']
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
	public function generar_codigo_orden() {	
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$arrData = array();
		// "PREFIJO_MATERIAL" + AÑO + '-' + CORRELATIVO
		//var_dump( $allInputs); exit();
		$codigoOrden = $allInputs['tipoMaterial']['prefijo'];
		$codigoOrden .= date('y');
		$codigoOrden .= '-';
		$codigoOrden .= str_pad($allInputs['almacen']['id'], 2, '0', STR_PAD_LEFT);
		// OBTENER ULTIMA ORDEN DE COMPRA 
		$fUltimaOrden = $this->model_orden_compra->m_cargar_ultima_orden_compra_de_almacen($allInputs);
		if( empty($fUltimaOrden) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($fUltimaOrden['orden_compra'], -6, 6);
			if( substr($fUltimaOrden['orden_compra'], -11, 2) == date('y') ){ 
				$numberToOrden = (int)$numberToOrden + 1;
			}else{
				$numberToOrden = 1;
			}
		}
		$codigoOrden .= str_pad($numberToOrden, 6, '0', STR_PAD_LEFT);
		$arrData['codigo_orden'] = $codigoOrden;
		//var_dump($codigoOrden); exit();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/* ======================================== ORDENES DE COMPRA ====================================*/
	public function lista_movimientos_orden_compra() { 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_orden_compra->m_cargar_orden_compra($datos, $paramPaginate);
		$totalRows = $this->model_orden_compra->m_count_sum_orden_compra($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_orden_compra'] == 1 ){
				$estado = 'POR APROBAR';
				$clase = 'label-default';
			}elseif( $row['estado_orden_compra'] == 2 ){
				$estado = 'APROBADA';
				$clase = 'label-success';
			}
			if( $row['estado_movimiento'] == 1 ){ // ORDEN DE COMPRA CONVERTIDA EN COMPRA 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'COMPRA';
			}elseif( $row['estado_movimiento'] == 2 ){ // ORDEN DE COMPRA (APROBADA O SIN APROBAR)
				$objEstado['claseIcon'] = 'ti-alarm-clock';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'ORDEN DE COMPRA';
			}elseif( $row['estado_movimiento'] == 0 ){ // MOVIMIENTO ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADA';
				$estado = ''; // NECESARIO PARA QUE EN ESTADO DE LA ORDEN NO APAREZCA NADA SI LA ORDEN FUE ANULADA
				$clase = '';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idproveedor' => $row['idproveedor'],
					'razon_social' => strtoupper($row['razon_social']),
					'ruc' => $row['ruc'],
					'direccion_fiscal' => $row['direccion_fiscal'],
					'telefono' => $row['telefono'],
					'idtipomaterial' => $row['idtipomaterial'],
					'orden_compra' => $row['orden_compra'],
					'nombre_alm' => $row['nombre_alm'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fecha_aprobacion' => formatoFechaReporte($row['fecha_aprobacion']),
					'fecha_entrega' => formatoFechaReporte($row['fecha_entrega']),
					'fecha_entrega_real' => formatoFechaReporte($row['fecha_entrega_real']),
					'fecha_movimiento_or' => darFormatoDMY($row['fecha_movimiento']),
					'fecha_aprobacion_or' => darFormatoDMY($row['fecha_aprobacion']),
					'fecha_entrega_or' => darFormatoDMY($row['fecha_entrega']),
					'fmovimiento' => $row['fecha_movimiento'],
					'faprobacion' => $row['fecha_aprobacion'],
					'fentrega' => $row['fecha_entrega'],
					'subtotal' => substr($row['sub_total'], 4),
					'igv' => substr($row['total_igv'], 4),
					'total' => substr($row['total_a_pagar'], 4),
					'motivo_movimiento' => $row['motivo_movimiento'],
					'forma_pago' => (int)$row['forma_pago'],
					'moneda' => (int)$row['moneda'],
					'letras' => $row['letras'],
					'usuario' => $row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'],
					'estado_movimiento' => $row['estado_movimiento'],
					'estado' => $objEstado,
					'estado_orden' => (int)$row['estado_orden_compra'],
					'estado_obj' => array(
						'string' => $estado,
						'clase' =>$clase
					),
					'modo_igv' => $row['modo_igv']
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
		//var_dump($totalRows); exit();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_orden_compra_anulada() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_orden_compra->m_cargar_ordenes_anuladas($datos, $paramPaginate);
		$totalRows = $this->model_orden_compra->m_count_sum_ordenes_anuladas($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 

			$objEstado = array();

			if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}
			elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'orden_compra' => $row['orden_compra'],
					'idproveedor' => $row['idproveedor'],
					'razon_social' => strtoupper($row['razon_social']),
					'ruc' => $row['ruc'],
					'direccion_fiscal' => $row['direccion_fiscal'],
					'telefono' => $row['telefono'],
					'fecha_anulacion' => formatoFechaReporte($row['fecha_anulacion']),
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fecha_aprobacion' => formatoFechaReporte($row['fecha_aprobacion']),
					'fecha_entrega' => formatoFechaReporte($row['fecha_entrega']),
					'fmovimiento' => $row['fecha_movimiento'],
					'faprobacion' => $row['fecha_aprobacion'],
					'fentrega' => $row['fecha_entrega'],
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],

					'estado' => $objEstado
				)
			);
		}
		//var_dump($arrListado); exit();

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
	public function lista_detalle_orden_compra()
	{
		
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_orden_compra->m_cargar_detalle_entrada($datos); 
		$totalRows = $this->model_orden_compra->m_count_sum_detalle_entrada($datos);
		$proveedor = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_id($datos);
		//var_dump($proveedor); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {
			$objEstado = array();

			if( $row['estado_detalle'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'DISPONIBLE';
			}
			elseif( $row['estado_detalle'] == 2 ){ // NO DISPONIBLE 
				$objEstado['claseIcon'] = 'fa-power-off';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NO DISPONIBLE';
			}
			elseif( $row['estado_detalle'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if(empty($row['caja_unidad'])){
				if($row['acepta_caja_unidad'] == '1'){
					$row['caja_unidad'] = 'CAJA';
				}else{
					$row['caja_unidad'] = 'UNIDAD';
				}
			}
			array_push($arrListado, 
				array(
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'id' => $row['idmedicamento'],
					'descripcion' => $row['medicamento'],
					'precio' => $row['precio_unitario'],
					'igv' => $row['excluye_igv'] == 1? '0.00' : round( (floatval($row['total_detalle'])*0.18/1.18), 2 ),
					'valor' => round( floatval( $row['precio_unitario'] ) * $row['cantidad'], 2 ),
					'importe_sin' => $row['excluye_igv'] == 1? $row['total_detalle'] : round( (floatval($row['total_detalle'])/1.18), 2 ),

					'idmovimiento' => $row['idmovimiento'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					//'factura' => $row['ticket_venta'],
					// 'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'unidad_medida' => $row['presentacion'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => round($row['descuento_porcentaje'],2),
					'descuento_valor' => empty($row['descuento_asignado'])? 0 : $row['descuento_asignado'],
					'importe' => $row['total_detalle'],
					'excluye_igv' => $row['excluye_igv'],
					'contenido' => $row['contenido'],
					'caja_unidad' => $row['caja_unidad'],
					'estado' => $objEstado,


				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['proveedor'] = $proveedor;
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
	public function lista_ingresos_por_orden_compra(){		
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$paramDatos = $allInputs['datos'];
		$lista = $this->model_orden_compra->m_cargar_ingresos_por_orden_compra($paramDatos,$paramPaginate); 
		
		$totalRows = $this->model_orden_compra->m_count_ingresos_por_orden_compra($paramDatos);
		// var_dump($lista); exit();
		$proveedor = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_id($paramDatos);
		
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {
			
			array_push($arrListado, 
				array(
					//'iddetallemovimiento' => $row['iddetallemovimiento'],
					
					'idmovimiento' => $row['idmovimiento'],
					'orden_compra' => $row['orden_compra'],
					'factura' => $row['factura'],
					'guia_remision' => $row['guia_remision'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),



				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['proveedor'] = $proveedor;
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
	public function lista_detalle_orden_en_cbo()
	{ 
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$datos = $allInputs['datos'];
		$lista = $this->model_orden_compra->m_cargar_detalle_orden_cbo($datos); 
		$proveedor = $this->model_proveedor_farmacia->m_cargar_este_proveedor_farmacia_por_id($datos);
		$arrListado = array();
		foreach ($lista as $row) {
			if(empty($row['caja_unidad'])){
				if($row['acepta_caja_unidad'] == '1'){
					$row['caja_unidad'] = 'CAJA';
				}else{
					$row['caja_unidad'] = 'UNIDAD';
				}
			}
			$row['orden_compra'] = $datos['descripcion'];
			$row['id'] = $row['idmedicamento'];
			$cantidad_ingresada = $this->model_entrada_farmacia->m_obtener_cantidad_ingresada_con_orden_compra($row);
			array_push($arrListado, 
				array(
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'id' => $row['idmedicamento'],
					'descripcion' => $row['medicamento'],
					'precio' => $row['precio_unitario'],
					'igv' => $row['excluye_igv'] == 1? '0.00' : round( (floatval($row['total_detalle'])*0.18/1.18), 2 ),
					'valor' => round( floatval( $row['precio_unitario'] ) * $row['cantidad'], 2 ),
					'importe_sin' => $row['excluye_igv'] == 1? $row['total_detalle'] : round( (floatval($row['total_detalle'])/1.18), 2 ),
					'idmovimiento' => $row['idmovimiento'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'unidad_medida' => $row['presentacion'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'cantidad' => (int)$row['cantidad'] - $cantidad_ingresada,
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => round($row['descuento_porcentaje'],2),
					'descuento_valor' => empty($row['descuento_asignado'])? 0 : $row['descuento_asignado'],
					'importe' => $row['total_detalle'],
					'excluye_igv' => $row['excluye_igv'],
					'contenido' => $row['contenido'],
					'caja_unidad' => $row['caja_unidad'],
					'numero_lotes' => '1',
					'cantidad_ingresada' => $cantidad_ingresada,
					'cantidad_total' => $row['cantidad'],
					'cant_ingr_de_total' => $cantidad_ingresada . '/' . $row['cantidad'],
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['proveedor'] = $proveedor;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_productos_orden_compra()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_orden_compra->m_cargar_producto_entrada($datos, $paramPaginate);
		$totalRows = $this->model_orden_compra->m_count_sum_producto_entrada($datos, $paramPaginate); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'orden_compra' => $row['orden_compra'],
					'fecha_aprobacion' => formatoFechaReporte($row['fecha_movimiento']),
					'idproveedor' => $row['idproveedor'],
					'proveedor' => $row['razon_social'],
					'ruc' => $row['ruc'],
					'idmedicamento' => $row['idmedicamento'],
					'producto' => $row['medicamento'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'total_detalle' => $row['total_detalle']
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
	public function lista_ultimo_precio_medicamento()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$datos = NULL;
		if($row = $this->model_orden_compra->m_obtener_ultimo_precio_compra($allInputs)){
			if( $allInputs['caja_unidad'] == 'CAJA' ){
				$datos = $row['precio_unitario_por_caja'];
			}else{
				$datos = $row['precio_unitario'];
			}
		}
    	$arrData['datos'] = $datos;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($row)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/* SEGUIMIENTO ORDEN DE COMPRA */
	public function ver_popup_estado_oc()
	{
		$this->load->view('orden-compra/popup_estados_oc');
	}
	public function ver_popup_detalle_estado_oc()
	{
		$this->load->view('orden-compra/popup_detalle_estados_oc');
	}
	public function ver_popup_enviar_correo_oc()
	{
		$this->load->view('orden-compra/popup_enviar_correo_oc');
	}
	public function lista_ordenes_compra_etapas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_orden_compra->m_cargar_solo_orden_compra_habilitadas($datos, $paramPaginate);
		$listaAreasOC = $this->model_orden_compra->m_cargar_etapas_oc($datos, $paramPaginate);
		$totalRows = $this->model_orden_compra->m_count_sum_orden_compra($datos, $paramPaginate);
		$listaSoloAreasOC = $this->model_areas_oc->m_cargar_areas_oc();
		$lenghtAreasOC = count($listaSoloAreasOC); // var_dump($listaSoloAreasOC,count($listaSoloAreasOC)); exit(); 
		$arrListado = array();
		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_orden_compra'] == 1 ){
				$estado = 'POR APROBAR';
				$clase = 'label-default';
			}elseif( $row['estado_orden_compra'] == 2 ){
				$estado = 'APROBADA';
				$clase = 'label-success';
			}
			if( $row['estado_movimiento'] == 1 ){ // ORDEN DE COMPRA CONVERTIDA EN COMPRA 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'COMPRA';
			}elseif( $row['estado_movimiento'] == 2 ){ // ORDEN DE COMPRA (APROBADA O SIN APROBAR)
				$objEstado['claseIcon'] = 'ti-alarm-clock';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'ORDEN DE COMPRA';
			}elseif( $row['estado_movimiento'] == 0 ){ // MOVIMIENTO ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADA';
				$estado = ''; // NECESARIO PARA QUE EN ESTADO DE LA ORDEN NO APAREZCA NADA SI LA ORDEN FUE ANULADA
				$clase = '';
			}
			$arrColumnsTemporal = array( 
				'idmovimiento' => $row['idmovimiento'],
				'idproveedor' => $row['idproveedor'],
				'razon_social' => strtoupper($row['razon_social']),
				'ruc' => $row['ruc'],
				'direccion_fiscal' => $row['direccion_fiscal'],
				'telefono' => $row['telefono'],
				'idtipomaterial' => $row['idtipomaterial'],
				'orden_compra' => $row['orden_compra'],
				'nombre_alm' => $row['nombre_alm'],
				'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
				'fecha_aprobacion' => formatoFechaReporte($row['fecha_aprobacion']),
				'fecha_entrega' => formatoFechaReporte($row['fecha_entrega']),
				// 'fecha_entrega_real' => formatoFechaReporte($row['fecha_entrega_real']),
				'fecha_movimiento_or' => darFormatoDMY($row['fecha_movimiento']),
				'fecha_aprobacion_or' => darFormatoDMY($row['fecha_aprobacion']),
				'fecha_entrega_or' => darFormatoDMY($row['fecha_entrega']),
				'fmovimiento' => $row['fecha_movimiento'],
				'faprobacion' => $row['fecha_aprobacion'],
				'fentrega' => $row['fecha_entrega'],
				'subtotal' => substr($row['sub_total'], 4),
				'igv' => substr($row['total_igv'], 4),
				'total' => substr($row['total_a_pagar'], 4),
				'motivo_movimiento' => $row['motivo_movimiento'],
				'forma_pago' => (int)$row['forma_pago'],
				'moneda' => (int)$row['moneda'],
				'letras' => $row['letras'],
				'usuario' => $row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'],
				'estado_movimiento' => $row['estado_movimiento'],
				'estado' => $objEstado,
				'estado_orden' => (int)$row['estado_orden_compra'],
				'estado_obj' => array( 
					'string' => $estado,
					'clase' =>$clase
				)
			);
			$enviaMensaje = TRUE; 
			$tieneSeguimiento = FALSE;
			$iterateAreasInOC = 0;
			foreach ($listaAreasOC as $keyDet => $rowDet) { 
				if( $row['idmovimiento'] == $rowDet['idmovimiento'] && $rowDet['control_cambios'] == 1 ){ 
					$tieneSeguimiento = TRUE;
					$strHTML = '';
					if( $rowDet['descripcion_estado'] == 'APROBADO' ){ 
						$strHTML = '<i class="fa fa-check-square-o text-success va-top"></i>'; 
					}
					if( $rowDet['descripcion_estado'] == 'OBSERVADO' ){ 
						$strHTML = '<i class="fa fa-exclamation-triangle text-warning va-top"></i>'; 
					}
					if( $rowDet['descripcion_estado'] == 'RECHAZADO' ){ 
						$strHTML = '<i class="fa fa-ban text-danger va-top"></i>'; 
					}
					$arrColumnsTemporal[strtolower($rowDet['descripcion'])] = array( 
						'idordencompraestado' => $rowDet['idordencompraestado'],
						'idestadoporarea' => $rowDet['idestadoporarea'],
						'idareaoc' => $rowDet['idareaoc'],
						'descripcion' => $rowDet['descripcion'],
						'estado' => $rowDet['descripcion_estado'],
						'control_cambios' => $rowDet['control_cambios'],
						'fecha_estado' => formatoFechaReporte($rowDet['fecha_estado']),
						'comentario' => $rowDet['comentario'],
						'strHtml' => $strHTML
					);
					if( $rowDet['descripcion_estado'] !== 'APROBADO' ){ 
						$enviaMensaje = FALSE;
					}
					$iterateAreasInOC++;
				}
			}
			$tieneTodosLosEstados = FALSE;
			if($lenghtAreasOC == $iterateAreasInOC){ 
				$tieneTodosLosEstados = TRUE;
			}
			if( $enviaMensaje && ($tieneSeguimiento) && $tieneTodosLosEstados ){ 
				$arrColumnsTemporal['enviar_mensaje'] = array( 
					'strHtml'=> '<i class="fa fa-envelope text-default va-top"></i>',
					'bool'=> TRUE,
					'conteo'=> $row['conteo_mensaje_oc']
				); 
			}
			if( !$enviaMensaje && ($tieneSeguimiento) ){ 
				$arrColumnsTemporal['enviar_mensaje'] = array( 
					'strHtml'=> '<i class="fa fa-envelope text-default op-3"></i>',
					'bool'=> FALSE,
					'conteo'=> $row['conteo_mensaje_oc']
				); 
			}
			array_push($arrListado, $arrColumnsTemporal);
			/* Agregar areas por cada movimiento */ 

		} //exit();
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['suma_total']) ? 0 : $totalRows['suma_total'];
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){
			$arrData['flag'] = 0; 
		}
		//var_dump($totalRows); exit();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_esta_orden_compra_etapas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$listaAreasOC = $this->model_orden_compra->m_cargar_etapas_de_esta_oc($allInputs);
		$arrListado = array();
		foreach ($listaAreasOC as $keyDet => $rowDet) { 
			if( $rowDet['control_cambios'] == 1 ){ 
				$strHTML = '';
				if( $rowDet['descripcion_estado'] == 'APROBADO' ){ 
					$strHTML = '<i class="fa fa-check-square-o text-success va-top"></i>'; 
				}
				if( $rowDet['descripcion_estado'] == 'OBSERVADO' ){ 
					$strHTML = '<i class="fa fa-exclamation-triangle text-warning va-top"></i>'; 
				}
				if( $rowDet['descripcion_estado'] == 'RECHAZADO' ){ 
					$strHTML = '<i class="fa fa-ban text-danger va-top"></i>'; 
				}
				$arrListado[] = array( 
					'idordencompraestado' => $rowDet['idordencompraestado'],
					'idestadoporarea' => $rowDet['idestadoporarea'],
					'idareaoc' => $rowDet['idareaoc'],
					'area' => $rowDet['descripcion'],
					'estado' => $rowDet['descripcion_estado'],
					'correo_proveedor' => $rowDet['email'],
					'fecha_estado' => formatoFechaReporte($rowDet['fecha_estado']),
					'comentario' => $rowDet['comentario'],
					'strHtml' => $strHTML
				);
			}
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($listaAreasOC)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function enviar_correo_proveedor()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // 
		$arrConfig = $this->model_config->m_cargar_empresa_activa();
		$listaSoloAreasOC = $this->model_areas_oc->m_cargar_areas_oc();
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit(); 
    	// VALIDAR CAMPOS VACIOS 
    	if( empty($allInputs['idmovimiento']) ){ 
    		$arrData['message'] = 'No se encontró el movimiento seleccionado. Recargue la página y vuelve a intentarlo.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 
    	if(empty($allInputs['correo_proveedor'])){ 
    		$arrData['message'] = 'No se encontró ningún correo en el formulario. Rellene ese campo por favor.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData)); 
			return;
    	}
    	$fValidate = $this->model_orden_compra->m_comprobar_varios_estados_oc($allInputs['idmovimiento'],array('RECHAZADO','OBSERVADO')); 
    	if( !empty($fValidate) ){ 
    		$arrData['message'] = 'Esta Orden de Compra aún no ha sido aprobada por todas las áreas.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	$arrMails = explode(";",$allInputs['correo_proveedor']);
    	$validateMail = TRUE; 
    	foreach ($arrMails as $key => $val) { 
    		$arrMails[$key] = trim($arrMails[$key]);
    		$mailLimpio = trim($val);
    		$rptaValidateMail = comprobar_email($mailLimpio); 
    		if( $rptaValidateMail === FALSE ){ 
    			$validateMail = FALSE; 
    		}
    	}
    	if( $validateMail === FALSE ){ 
    		$arrData['message'] = 'Verifique la correcta escritura de los correos electrónicos y la separación por punto y coma.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	// exit();
    	$allInputs['id'] = 2; // LOGISTICA 
    	$fLogistica = $this->model_areas_oc->m_cargar_esta_area_oc($allInputs);
    	// ACTUALIZAMOS LA FECHA DE ENVIO DE LA ORDEN DE COMPRA 
    	$this->model_orden_compra->m_actualizar_fecha_envio_correo_oc($allInputs);

    	/* ENVÍO DE CORREO ELECTRÓNICO */ 
    	$this->load->library('My_PHPMailer');
		$hoydate = date("Y-m-d H:i:s");
		date_default_timezone_set('UTC');
		define('SMTP_HOST','mail.villasalud.pe');

		define('SMTP_PORT',25);
		define('SMTP_USERNAME',$fLogistica['mail_1']);
		define('SMTP_PASSWORD',$fLogistica['clave_mail_1']);
		$setFromAleas = $fLogistica['descripcion'];
		$mail = new PHPMailer();
		$mail->IsSMTP(true);
		//$mail->SMTPDebug = 2;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "tls";
		$mail->Host = SMTP_HOST;
		$mail->Port = SMTP_PORT;
		$mail->Username =  SMTP_USERNAME;
		$mail->Password = SMTP_PASSWORD;
		$mail->SetFrom(SMTP_USERNAME,$setFromAleas);
		$mail->AddReplyTo(SMTP_USERNAME,$setFromAleas);
		$mail->Subject = 'ENVÍO DE ORDEN DE COMPRA APROBADA - VILLA SALUD';

		$cuerpo = '<html> 
			<head>
			  <title>ENVÍO DE LA ORDEN DE COMPRA</title> 
			</head>
			<body style="font-family: sans-serif;padding: 10px 40px;" > 
			<div style="text-align: right;">
				<img style="width: 160px;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/'.$arrConfig['nombre_logo']).'">
			</div> <br />';
		$cuerpo .= '<div style="font-size:16px;">  
				Estimado: <br /> <br /> 
				Mediante la presente informo, que el <u> ÁREA DE LOGÍSTICA</u> ha <u> APROBADO </u> la O.C. con <u> N° '.$allInputs['orden_compra'].
				'</u>, del proveedor <u>'.$allInputs['razon_social'].'</u>, con fecha <u>'.$allInputs['fecha_movimiento'].'</u> , 
				la cual le remito en este mensaje como un archivo adjunto para su oportuno despacho. <br /> 
				Atte: <br /> <br /> 
					AREA DE LOGÍSTICA 
			</div>';
		// $cuerpo .= '<div style="width: 100%; display: block; font-size: 14px; text-align: right; line-height: 5; color: #a9b9c1;"> Atte: Área de Sistemas y Desarrollo </div>';
		$cuerpo .= '</body></html>';
		$mail->AltBody = $cuerpo;
		$mail->MsgHTML($cuerpo);
		foreach ($arrMails as $key => $val) { 
			$mail->AddAddress($val, 'PROVEEDOR');  
		}
		$mail->AddAddress($fLogistica['mail_1'], 'LOGÍSTICA');
		$arrParams['resultado'] = $allInputs['idmovimiento'];
		$arrParams['idmovimiento'] = $allInputs['idmovimiento'];
		//$arrParams['estado'] = ;
		$PDFTemporal = $this->report_orden_compra_para_correo($arrParams); 
		// var_dump($PDFTemporal); exit(); 
		$mail->AddStringAttachment($PDFTemporal, 'ORDEN DE COMPRA N° '.$allInputs['orden_compra'].'.pdf', 'base64', 'application/pdf'); 
		// Activo condificación utf-8
		$mail->CharSet = 'UTF-8';
		// echo $cuerpo; 
		if( $mail->Send() ){ 
			$arrData['message'] = ' <br /> Se envió el correo a los destinatarios correctamente.'; 
			if($this->model_orden_compra->m_contar_mensajes_enviados_proveedor($allInputs)){ 
	    		$arrData['message'] .= 'La orden de compra se actualizó correctamente';
	    		$arrData['flag'] = 1;
	    	}
		}else{
			$arrData['message'] .= ' <br /> Surgió un inconveniente al enviar el correo al destinatario. Verifique que el correo sea válido.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_estado_orden()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs,'<pre>'); exit(); 
		$arrConfig = $this->model_config->m_cargar_empresa_activa();
		$listaSoloAreasOC = $this->model_areas_oc->m_cargar_areas_oc();
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDAR CAMPOS VACIOS 
    	if($allInputs['area_interes']['id'] === 'none'){ 
    		$arrData['message'] = 'Campo: Área de Interés, está vacío'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	if(empty($allInputs['comentario'])){ 
    		$arrData['message'] = 'Campo: Comentario/Observaciones, está vacío'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 
		// VALIDAR QUE NO SE REGISTREN ESTADOS REPETIDOS 
		$fValidate = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],$allInputs['area_interes']['id'],$allInputs['estado_cambio']);
		if( !empty($fValidate) ){ 
			$arrData['message'] = 'Ya se ha registrado el estado: <b>'.strtoupper($allInputs['estado_cambio']).'</b> para el Área de Interés: <b>'.strtoupper($allInputs['area_interes']['descripcion']).'</b>'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
    	// VALIDAR QUE SI HA SIDO APROBADO NO PUEDA ASIGNAR OTRO ESTADO - PARA CUALQUIER AREA DE INTERES 
    	$fValidate = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],$allInputs['area_interes']['id'],'APROBADO');
		if( !empty($fValidate) ){ 
			$arrData['message'] = 'Esta O.C ya ha sido aprobada, no se puede [RECHAZAR] ni [OBSERVAR]. Deshaga la acción para poder [RECHAZAR] o [OBSERVAR]'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		// VALIDAR QUE: SI HA SIDO RECHAZADO NO PUEDA ASIGNAR OTRO ESTADO - PARA CUALQUIER AREA DE INTERES 
    	$fValidate = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],$allInputs['area_interes']['id'],'RECHAZADO');
		if( !empty($fValidate) ){ 
			$arrData['message'] = 'Esta O.C ya ha sido rechazada, no se puede [APROBAR] ni [OBSERVAR]. Deshaga la acción para poder [APROBAR] o [OBSERVAR]'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		// VALIDAR QUE SI HA SIDO OBSERVADO NO PUEDA ASIGNAR OTRO ESTADO - PARA CUALQUIER AREA DE INTERES 
    	$fValidate = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],$allInputs['area_interes']['id'],'OBSERVADO');
		if( !empty($fValidate) ){ 
			$arrData['message'] = 'Esta O.C ya ha sido observada, no se puede [RECHAZAR] ni [APROBAR]. Deshaga la acción para poder [RECHAZAR] o [APROBAR]'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

		// VALIDAR QUE: SE APRUEBE POR ORDEN DE AREA Y NO DESORDENADAMENTE
		// SI ES FARMACIA; LOGISTICA TIENE QUE APROBARLO
		if( $allInputs['area_interes']['descripcion'] == 'FARMACIA' ){ 
			$fValidate = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],2,'APROBADO');
			if( empty($fValidate) ){ 
				$arrData['message'] = 'Esta O.C aún no ha sido aprobada por LOGISTICA.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}
		// SI ES ADMINISTRACION Y FINANZAS; FARMACIA Y LOGISTICA TIENE QUE APROBARLO
		if( $allInputs['area_interes']['descripcion'] == 'ADMINISTRACION Y FINANZAS' ){ 
			$fValidateLOG = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],2,'APROBADO');
			$fValidateFAR = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],4,'APROBADO');
			if( empty($fValidateLOG) || empty($fValidateFAR) ){ 
				$arrData['message'] = 'Esta O.C aún no ha sido aprobada por LOGISTICA y/o FARMACIA.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}

		// var_dump($allInputs); exit(); 
		$allInputs['idestadoporarea'] = NULL;
		$listaAreasInteres = $this->model_areas_oc->m_cargar_areas_oc(); 
		/*DATOS DE CORREO */ 
		$mailDeArea = NULL;
		$claveDeArea = NULL;
		$setFromAleas = NULL;
		$palabraClave = NULL;
		$palabraClaveEstil = NULL;
		foreach ($listaAreasInteres as $key => $row) { 
			if($allInputs['area_interes']['id'] == $row['idareaoc'] ){ 
				if( $allInputs['abv_estado_cambio'] == 'a' ){ // APROBADO 
					$palabraClave = 'APROBADO';
					$palabraClaveEstil = '<span style="color: green; font-weight: bold;">APROBADO</span>';
					$fAreaEstadoOC = $this->model_areas_oc->m_cargar_este_estado_area_oc($allInputs['area_interes']['id'],'APROBADO');
					$allInputs['idestadoporarea'] = $fAreaEstadoOC['idestadoporarea'];
				}elseif ( $allInputs['abv_estado_cambio'] == 'r' ) { // RECHAZADO 
					$fAreaEstadoOC = $this->model_areas_oc->m_cargar_este_estado_area_oc($allInputs['area_interes']['id'],'RECHAZADO');
					$allInputs['idestadoporarea'] = $fAreaEstadoOC['idestadoporarea'];
					$palabraClave = 'RECHAZADO';
					$palabraClaveEstil = '<span style="color: red; font-weight: bold;">RECHAZADO</span>';
				}elseif ( $allInputs['abv_estado_cambio'] == 'o' ) { // OBSERVADO 
					$fAreaEstadoOC = $this->model_areas_oc->m_cargar_este_estado_area_oc($allInputs['area_interes']['id'],'OBSERVADO');
					$allInputs['idestadoporarea'] = $fAreaEstadoOC['idestadoporarea'];
					$palabraClave = 'OBSERVADO';
					$palabraClaveEstil = '<span style="color: #ffb300; font-weight: bold;">OBSERVADO</span>';
				} 
				$mailDeArea = trim($row['mail_1']);
				$claveDeArea = trim($row['clave_mail_1']);
				$setFromAleas = strtoupper($row['descripcion']);
			}
		} 
		if( empty($allInputs['idestadoporarea']) ){ 
			$arrData['message'] = 'Opción inválida para la Orden de Compra'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
    	if($this->model_orden_compra->m_cambiar_estado_oc($allInputs)){ 
    		$arrData['message'] = 'La orden de compra se actualizó correctamente'; 
    		$arrData['flag'] = 1; 
    		/* ACTUALIZAR ESTADO GENERAL ORDEN DE COMPRA */ 
    		$listaEstadoOC = $this->model_orden_compra->m_comprobar_varios_estados_oc($allInputs['idmovimiento'],array('APROBADO')); 
    		$listaAreas = $this->model_areas_oc->m_cargar_areas_oc(); 
    		// SI LA ORDEN ESTÁ APROBADA POR LAS 3 AREAS 
    		if( count($listaEstadoOC) === count($listaAreas) ){ 
    			// SE APRUEBA LA ORDEN DE COMPRA EN GENERAL 
    			$this->model_orden_compra->m_aprobar_orden_compra($allInputs);
    		}
    	}
    	if( $arrData['flag'] === 1 ){ 
    		/* ENVÍO DE CORREO ELECTRÓNICO */ 
	    	$this->load->library('My_PHPMailer');
			$hoydate = date("Y-m-d H:i:s");
			date_default_timezone_set('UTC');
			define('SMTP_HOST','mail.villasalud.pe');

			define('SMTP_PORT',25);
			define('SMTP_USERNAME',$mailDeArea);
			define('SMTP_PASSWORD',$claveDeArea);
			$mail = new PHPMailer();
			$mail->IsSMTP(true);
			//$mail->SMTPDebug = 2;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "tls";
			$mail->Host = SMTP_HOST;
			$mail->Port = SMTP_PORT;
			$mail->Username =  SMTP_USERNAME;
			$mail->Password = SMTP_PASSWORD;
			$mail->SetFrom(SMTP_USERNAME,$setFromAleas);
			$mail->AddReplyTo(SMTP_USERNAME,$setFromAleas);
			$mail->Subject = $setFromAleas.' A '.$palabraClave.' EL ESTADO DE LA ORDEN N° '.$allInputs['orden_compra'].' - SEGUIMIENTO.';

			$cuerpo = '<html> 
				<head>
				  <title>Seguimiento de Órdenes de Compra</title> 
				</head>
				<body style="font-family: sans-serif;padding: 10px 40px;" > 
				<div style="text-align: right;">
					<img style="width: 160px;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/'.$arrConfig['nombre_logo']).'">
				</div> <br />';
			
			// $cuerpo .='</tbody> </table>';
			$cuerpo .= '<div style="font-size:16px;">  
				Estimado: <br /> <br /> 
				Mediante la presente informo, que el <u> ÁREA DE '.$setFromAleas.'</u> ha <u>'.$palabraClaveEstil.'</u> la O.C. con <u> N° '.$allInputs['orden_compra'].'</u>, del proveedor <u>'.$allInputs['proveedor'].'</u>, con fecha <u>'.$allInputs['fecha_movimiento'].'</u> , 
				la cual le remito para su oportuna evaluación en el Sistema. <br /> 
				<h4>COMENTARIOS/OBSERVACIONES: </h4> 
				<p style="font-style: oblique;font-size: 12px;">"'.nl2br($allInputs['comentario']).'"</p> <br /> 
				Atte: 
			</div>';
			// $cuerpo .= '<div style="width: 100%; display: block; font-size: 14px; text-align: right; line-height: 5; color: #a9b9c1;"> Atte: Área de Sistemas y Desarrollo </div>';
			$cuerpo .= '</body></html>';
			$mail->AltBody = $cuerpo;
			$mail->MsgHTML($cuerpo);
			$idAreaReceptor = NULL;
			foreach ($listaSoloAreasOC as $key => $row) { 
				if($allInputs['area_interes']['id'] == $row['idareaoc'] ){ 
					$mail->AddAddress($row['mail_receptor'], $row['aleas_receptor']); 
					$idAreaReceptor = $row['idareaoc'];
				}
			}
			// foreach ($listaSoloAreasOC as $key => $row) { 
			// 	if( $allInputs['area_interes']['id'] != $row['idareaoc'] && $idAreaReceptor != $row['idareaoc'] && !empty($idAreaReceptor) ){ 
			// 		$mail->AddCC($row['mail_1'], $row['descripcion']);
			// 	}
			// }
			// Activo condificación utf-8
			$mail->CharSet = 'UTF-8';
			// echo $cuerpo; 
			if( $mail->Send() ){ 
				$arrData['message'] .= ' <br /> Se envió el correo a los destinatarios correctamente.';
			}else{
				$arrData['message'] .= ' <br /> Surgió un inconveniente al enviar el correo al destinatario. Verifique que el correo sea válido.';
			}
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshacer_accion_estado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit(); 
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente.';
    	$arrData['flag'] = 0;

		// VALIDAR QUE CAMPO A COMPARAR ESTÉ LLENO. 
		if( empty($allInputs['idordencompraestado']) ){ 
			$arrData['message'] = 'No existe el estado. Refresque el sistema y vuelva a intentarlo.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		// SI YA SE ENVIÓ EL CORREO AL PROVEEDOR, NO SE PUEDE DESHACER NINGÚN CAMBIO. 
		$fOrden = $this->model_orden_compra->m_verificar_oc_enviada($allInputs['idmovimiento']); 
		if( empty( $fOrden ) ){ 
			$arrData['message'] = 'No se pueden deshacer las acciones, cuando el correo ya ha sido enviado al proveedor.'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		//SI NO ES SISTEMAS 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			// VALIDAR QUE SOLO DESHAGA EL CAMBIO EL GRUPO INDICADO 
			if( strtoupper($allInputs['descripcion']) == 'LOGISTICA' && ($this->sessionHospital['key_group'] != 'key_logistica') ){ 
				$arrData['message'] = 'Sólo puede deshacer las acciones el área de interés indicado.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
			if( strtoupper($allInputs['descripcion']) == 'FARMACIA' && ($this->sessionHospital['key_group'] != 'key_admin_far') ){ 
				// var_dump('farma',$this->sessionHospital['key_group']); exit();
				$arrData['message'] = 'Sólo puede deshacer las acciones el área de interés indicado.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
			if( strtoupper($allInputs['descripcion']) == 'ADMINISTRACION Y FINANZAS' && ($this->sessionHospital['key_group'] != 'key_gerencia') ){ 
				$arrData['message'] = 'Sólo puede deshacer las acciones el área de interés indicado.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}
		
    	if($this->model_orden_compra->m_deshacer_cambio_estado_oc($allInputs)){ 
    		$arrData['message'] = 'Se deshizo los cambios correctamente.';
    		$arrData['flag'] = 1;
    		/* ACTUALIZAR ESTADO GENERAL ORDEN DE COMPRA */
    		$listaEstadoOC = $this->model_orden_compra->m_comprobar_varios_estados_oc($allInputs['idmovimiento'],array('APROBADO')); 
    		$listaAreas = $this->model_areas_oc->m_cargar_areas_oc();
    		// SI LA ORDEN ESTÁ APROBADO POR LAS 3 AREAS 
    		if( !(count($listaEstadoOC) === count($listaAreas)) ){ 
    			// SE DESHACE LA APROBACION DE LA ORDEN DE COMPRA EN GENERAL 
    			$this->model_orden_compra->m_deshacer_aprobar_orden_compra($allInputs); 
    		}
    	}
    	/* ENVÍO DE CORREO ELECTRÓNICO */ 
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_orden_compra(){
		$this->load->view('orden-compra/orden_compra_formView');
	}
	public function ver_popup_detalle_orden_compra(){
		$this->load->view('orden-compra/popupVerDetalleOC');
	}
	public function ver_popup_ingresos_con_orden_compra(){
		$this->load->view('orden-compra/popupVerIngresosOC');
	}
	public function registrar_orden_compra(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		/* VALIDACIONES */
		if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún producto/medicamento';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		if(strlen($allInputs['ruc'])  != 11){
			$arrData['message'] = 'Ingrese un RUC válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
    	if($allInputs['almacen']['id'] == null){
    		$arrData['message'] = 'Debe tener asignado un almacen para poder registrar los datos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if( $allInputs['total'] == 'NaN' || empty($allInputs['total']) ){
    		$arrData['message'] = 'No se puedo calcular el precio total de venta. Corrija los montos e intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	$errorEnBucle = 'no'; 
    	foreach ($allInputs['detalle'] as $key => $row) {
    		if( empty($row['precio']) ){
    			$errorEnBucle = 'si';
    			break;
    		}

    	}
    	if( $errorEnBucle === 'si' ){ 
    		$arrData['message'] = 'No se puedo calcular el precio total de venta. Corrija los montos e intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	//var_dump($allInputs); exit();
    	$allInputs['fecha_movimiento'] = $allInputs['fecha_movimiento'] . ' ' . date('H:i:s');
    	$allInputs['fecha_aprobacion'] = empty($allInputs['fecha_aprobacion'])? NULL : $allInputs['fecha_aprobacion'] . ' ' . date('H:i:s');
    	$allInputs['fecha_entrega'] = $allInputs['fecha_entrega'] . ' ' . date('H:i:s');
		$subAlmacenPrincipal = $this->model_almacen_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id']);
		$allInputs['idsubalmacen'] = $subAlmacenPrincipal['idsubalmacen'];
		// var_dump($allInputs); exit();
		// verificar si numero de orden de compra ya fue registrada
		if( $this->model_orden_compra->m_verificar_existe_orden_compra($allInputs) ){
			$arrData['message'] = 'Ya se registró una orden de compra con el numero: ' . $allInputs['orden_compra'] . ' Por favor Actualice el número de orden';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$this->db->trans_start();

		if( $this->model_orden_compra->m_registrar_orden_compra($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento');
			$arrData['idmovimiento'] = $allInputs['idmovimiento'];
			foreach ($allInputs['detalle'] as $key => $producto) {
				if( $producto['caja_unidad'] == 'CAJA' ){
					$producto['cantidad_caja'] = $producto['cantidad'];
					$producto['precio_unitario_por_caja'] = $producto['precio'];
					if(!empty($producto['contenido'])){
						$producto['cantidad'] = $producto['cantidad'] * $producto['contenido'];
						$producto['precio'] = $producto['precio'] / $producto['contenido'];
					}
				}
				$producto['idmovimiento'] = $allInputs['idmovimiento'];
				$producto['idalmacen'] = $allInputs['almacen']['id']; // necesario para verificar y registrar un ingreso de medicamento
				$producto['idsubalmacen'] = $allInputs['idsubalmacen']; // necesario para registrar un ingreso de medicamento
				$producto['idmedicamento'] = $producto['id'];

				// VERIFICAR SI EL PRODUCTO EXISTE EN ALMACEN CENTRAL
				if( $medicamentoalmacen = $this->model_entrada_farmacia->m_verificar_producto_destino($producto) ){
					$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
					if( $this->model_orden_compra->m_registrar_detalle_orden_compra($producto) ){
						$arrData['message'] = 'Los datos se registaron correctamente';
    					$arrData['flag'] = 1;
					} // registramos con el idmedicamentoalmacen del destino
				}else{
					// si no existe el producto primero lo ingresamos luego creamos el detalle
					$this->model_entrada_farmacia->m_registrar_medicamento_nuevo_almacen_central($producto);
					$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
					if( $this->model_orden_compra->m_registrar_detalle_orden_compra($producto) ){
						$arrData['message'] = 'Los datos se registaron correctamente';
    					$arrData['flag'] = 1;
					}
				}
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_orden_compra(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		/* VALIDACIONES */
		if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún producto/medicamento';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		
    	//var_dump($allInputs); exit();
    	$allInputs['fecha_movimiento'] = $allInputs['fecha_movimiento'] . ' ' . date('H:i:s');
    	if(!empty($allInputs['fecha_aprobacion'])){
    		$allInputs['fecha_aprobacion'] = $allInputs['fecha_aprobacion'] . ' ' . date('H:i:s');
    	}
    	
    	$allInputs['fecha_entrega'] = $allInputs['fecha_entrega'] . ' ' . date('H:i:s');
    	$subAlmacenPrincipal = $this->model_almacen_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id']);
		$allInputs['idsubalmacen'] = $subAlmacenPrincipal['idsubalmacen'];
		// var_dump($allInputs); exit();
		$errorEnBucle = 'no'; 
    	foreach ($allInputs['detalle'] as $key => $row) {
    		if( @$row['es_nuevo'] ){
	    		if( $this->model_orden_compra->m_verificar_existe_medicamento_en_orden_compra($row['id'],$allInputs['idmovimiento']) ){
	    			$errorEnBucle = 'si';
	    			$arrData['message'] = 'El medicamento ya ha sido agregado a la orden. Vuelva a cargar la orden';
	    			break;
	    		}
    		}
    		if( empty($row['precio']) ){
    			$errorEnBucle = 'si';
    			$arrData['message'] = 'No se puede calcular el precio total de venta. Corrija los montos e intente nuevamente.';
    			break;
    		}
    	}
    	if( $errorEnBucle === 'si' ){ 
    		
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	} // 	fecha_entrega
		$this->db->trans_start();
		if( $this->model_orden_compra->m_editar_orden_compra($allInputs) ){
			foreach ($allInputs['detalle'] as $key => $producto) {
				if( $producto['caja_unidad'] == 'CAJA' ){
					$producto['cantidad_caja'] = $producto['cantidad'];
					$producto['precio_unitario_por_caja'] = $producto['precio'];
					if(!empty($producto['contenido'])){
						$producto['cantidad'] = $producto['cantidad'] * $producto['contenido'];
						$producto['precio'] = $producto['precio'] / $producto['contenido'];
					}
				}
				if( @$producto['es_nuevo'] ){ // si es un detalle nuevo se registra
					
					$producto['idmovimiento'] = $allInputs['idmovimiento'];
					$producto['idalmacen'] = $allInputs['almacen']['id']; // necesario para verificar y registrar un ingreso de medicamento
					$producto['idsubalmacen'] = $allInputs['idsubalmacen']; // necesario para registrar un ingreso de medicamento
					$producto['idmedicamento'] = $producto['id'];

					// VERIFICAR SI EL PRODUCTO EXISTE EN ALMACEN CENTRAL
					if( $medicamentoalmacen = $this->model_entrada_farmacia->m_verificar_producto_destino($producto) ){
						$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
						if( $this->model_orden_compra->m_registrar_detalle_orden_compra($producto) ){
							$arrData['message'] = 'Los datos se registaron correctamente';
	    					$arrData['flag'] = 1;
						} // registramos con el idmedicamentoalmacen del destino
					}else{
						// si no existe el producto primero lo ingresamos luego creamos el detalle
						$this->model_entrada_farmacia->m_registrar_medicamento_nuevo_almacen_central($producto);
						$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
						if( $this->model_orden_compra->m_registrar_detalle_orden_compra($producto) ){
							$arrData['message'] = 'Los datos se registaron correctamente';
	    					$arrData['flag'] = 1;
						}
					}
				}else{

					if( $this->model_orden_compra->m_editar_detalle_orden_compra($producto) ){
						$arrData['message'] = 'Los datos se guardaron correctamente';
    					$arrData['flag'] = 1;
					} // registramos con el idmedicamentoalmacen del destino
				}
				
			}
			
			$arrData['message'] = 'Los datos se guardaron correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_orden_compra() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDAR SI EL MOVIMIENTO YA ESTA ANULADO
    	$movimiento = $this->model_entrada_farmacia->m_verificar_estado($allInputs[0]['idmovimiento']);
    	if( $movimiento['estado_movimiento'] == 0){
    		$arrData['message'] = 'La entrada ya está anulada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	if($this->model_entrada_farmacia->m_anular_movimiento($allInputs[0]['idmovimiento'])){
    		$arrData['message'] = 'La entrada se anuló correctamente';
    		$arrData['flag'] = 1;
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function aprobar_orden_compra()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDAR SI EL MOVIMIENTO YA ESTA APROBADO
    	$fila = $this->model_orden_compra->m_verificar_aprobacion_oc($allInputs[0]);
    	if( $fila['estado_orden_compra'] == 2){ 
    		$arrData['message'] = 'La Orden de Compra ya está aprobada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	if($this->model_orden_compra->m_aprobar_orden_compra($allInputs[0])){ 
    		$arrData['message'] = 'La entrada se aprobó correctamente';
    		$arrData['flag'] = 1;
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_detalle() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
    	if($this->model_entrada_farmacia->m_anular_detalle($allInputs['iddetallemovimiento'])){
    		if($this->model_entrada_farmacia->actualizar_movimiento($allInputs)){
    			$arrData['message'] = 'La entrada se anuló correctamente';
    			$arrData['flag'] = 1;
    		}
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	private function report_orden_compra_para_correo($arrParams){ 
	    if( !empty($arrParams) ){ 
	      $allInputs = $arrParams;
	    }else{
	    	return;
	    }
	    $allInputs['salida'] = 'pdf';
	    $allInputs['tituloAbv'] = 'O/C';
	    $allInputs['titulo'] = 'ORDEN DE COMPRA';
	    // var_dump($allInputs); exit(); 
	    /* 
			  'resultado' => string '7088' (length=4)
			  'estado' => string '2' (length=1)
	    */
	    // RECUPERACION DE DATOS
	    $orden = $this->model_orden_compra->m_cargar_orden_compra_por_id($allInputs['resultado']);
	    // CONFIGURACION DEL PDF
	    $this->pdf = new Fpdfext();
	    $this->pdf->setNombreEmpresaFarm($orden['nombreEmpresaFarm']);
	    $this->pdf->setRucEmpresaFarm($orden['rucEmpresaFarm']);
	    $this->pdf->setIdEmpresaFarm($orden['idempresaadmin']);
	    $arrConfig = array(
	      'razon_social' => $orden['nombreEmpresaFarm'],
	      'domicilio_fiscal' => $orden['domicilio_fiscal'],
	      'nombre_logo' => $orden['nombre_logo'],
	      'mode_report' => 'F',
	      'estado' => $orden['estado_orden_compra']
	    );
	    
	    // SETEO DE DATOS DEL USUARIO
	    $orden['usuario'] = $orden['nombres'] . ' ' .$orden['apellido_paterno'] . ' ' .$orden['apellido_materno'];

	    switch ($orden['forma_pago']) {
	      case '1': $forma_pago = 'AL CONTADO';
	        break;
	      case '2': $orden['letras'] == NULL? $forma_pago = 'CREDITO' : $forma_pago = 'CREDITO EN ' . $orden['letras'] . ' DÍAS';
	        break;
	      case '3': $forma_pago = 'EN '. $orden['letras'] . ' LETRAS';
	        break;
	      default: $forma_pago = '';
	        break;
	    }
	    switch ($orden['moneda']) {
	      case '1':
	        $moneda = 'SOLES';
	        $simbolo = 'S/. ';
	        break;
	      case '2':
	        $moneda = 'DÓLARES';
	        $simbolo = 'US$ ';
	        break;
	      default:
	        $moneda = '';
	        $simbolo = '';
	        break;
	    }

	    /* P.O.O. */ 
	    $this->pdf->setRazonSocialOC(utf8_decode($orden['razon_social']));
	    $this->pdf->setRucOC($orden['ruc']);
	    $this->pdf->setNombreComercialOC(utf8_decode($orden['nombre_comercial']));
	    $this->pdf->setDireccionFiscalOC(utf8_decode($orden['direccion_fiscal']));
	    $this->pdf->setTelefonoOC($orden['telefono']);
	    $this->pdf->setFaxOC($orden['fax']);
	    $this->pdf->setFormaPagoOC(utf8_decode($forma_pago));
	    $this->pdf->setMonedaOC(utf8_decode($moneda));
	    $this->pdf->setOrdenCompraOC($orden['orden_compra']);
	    $this->pdf->setFechaMovimientoOC(formatoFechaReporte3($orden['fecha_movimiento']));
	    $this->pdf->setFechaEmisionCorreoOC(formatoFechaReporte3($orden['fecha_emision_correo']));
	    $this->pdf->setFechaEntregaOC(formatoFechaReporte3($orden['fecha_entrega']));
	    $this->pdf->setNombreAlmOC(utf8_decode($orden['nombre_alm']));
	    $this->pdf->setUsuarioRespOC(utf8_decode($orden['usuario']));

	    $fValidateLOG = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],2,'APROBADO');
	    $fValidateFAR = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],4,'APROBADO');
	    $fValidateAF = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],6,'APROBADO');
	    $this->pdf->setFirmaLogistica(NULL);
	    if( !empty($fValidateLOG['firma_del_area']) ){ 
	      $this->pdf->setFirmaLogistica('assets/img/dinamic/firmaEmpleado/'.$fValidateLOG['firma_del_area']);
	    }
	    $this->pdf->setFirmaFarmacia(NULL);
	    if( !empty($fValidateFAR['firma_del_area']) ){ 
	      $this->pdf->setFirmaFarmacia('assets/img/dinamic/firmaEmpleado/'.$fValidateFAR['firma_del_area']);
	    }
	    $this->pdf->setFirmaFinanzas(NULL);
	    if( !empty($fValidateAF['firma_del_area']) ){ 
	      $this->pdf->setFirmaFinanzas('assets/img/dinamic/firmaEmpleado/'.$fValidateAF['firma_del_area']);
	    }

	    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$arrConfig);

	    $this->pdf->AddPage('P','A4');//var_dump($allInputs['tituloAbv']); exit();
	    $this->pdf->AliasNbPages();
	    $this->pdf->SetAutoPageBreak(true,70);

	    // APARTADO: DATOS DEL DETALLE
	    $i = 1;
	    $allInputs['idmovimiento'] = $allInputs['resultado'];
	    $detalle = $this->model_orden_compra->m_cargar_detalle_entrada($allInputs);
	    //var_dump($detalle); exit();
	    $exonerado = 0;
	    $fill = TRUE;
	    $this->pdf->SetDrawColor(204,204,204); // gris fill
	    $this->pdf->SetLineWidth(.2);

	    foreach ($detalle as $key => $value) {
	      // if( $value['caja_unidad'] == 'UNIDAD' &&  $value['acepta_caja_unidad'] == '1'){
	      //   $value['cantidad'] = $value['cantidad'] . ' (F)';
	      // }
	      $igv = 0;
	      if($value['excluye_igv'] == 2){
	        $importe_sin = round( floatval($value['total_detalle'])/1.18, 2 );
	        $igv = round( floatval($value['total_detalle'])*0.18/1.18, 2 );
	        $inafecto = ' ';
	      }else{
	        $exonerado += round( floatval($value['total_detalle'])*0.18, 2 );
	        $importe_sin = $value['total_detalle'];
	        $inafecto = 'X';
	      }
	      if( strlen($value['nombre_lab']) >= 17){
	        $laboratorio = substr($value['nombre_lab'], 0,17) . '...';
	      }else{
	        $laboratorio = $value['nombre_lab'];
	      }
	      $fill = !$fill;
	      $this->pdf->SetWidths(array(8, 53, 27, 15, 10, 12, 12, 16, 13, 16, 8));
	      $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C', 'R', 'R', 'R','R','R','C'));
	      //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
	      $this->pdf->SetFillColor(230, 240, 250);
	      $this->pdf->SetFont('Arial','',6);
	      $this->pdf->RowSmall( 
	        array(
	          $i,
	          utf8_decode($value['medicamento']),
	          utf8_decode($laboratorio),
	          utf8_decode($value['caja_unidad']),
	          $value['cantidad'],
	          $value['precio_unitario'],
	          $value['descuento_asignado'],
	          $importe_sin,
	          $igv,
	          $value['total_detalle'],
	          $inafecto
	        ),
	        $fill,1
	      );
	      $i++;
	    }
	    $this->pdf->Ln(1);
	    $this->pdf->SetFont('Arial','B',9);
	    $this->pdf->Cell(140,5,'Observaciones');
	    $this->pdf->Ln(5);
	    $this->pdf->SetFont('Arial','',8);

	    $this->pdf->SetWidths(array(138));
	    $this->pdf->TextArea(array(empty($orden['motivo_movimiento'])? '':$orden['motivo_movimiento']),0,0,FALSE,5,20);

	    $this->pdf->Cell(2,20,'');
	    $this->pdf->Cell(20,6,'SUBTOTAL:','LT',0,'R');
	    $this->pdf->SetFont('Arial','',8);
	    $this->pdf->Cell(30,6,$orden['sub_total'],'TR',0,'R');
	    $this->pdf->Ln(6);
	    $this->pdf->SetFont('Arial','',8);
	    $this->pdf->Cell(140,6,'');
	    $this->pdf->Cell(20,6,'IGV:','L',0,'R');
	    $this->pdf->SetFont('Arial','',8);
	    $this->pdf->Cell(30,6,$orden['total_igv'],'R',0,'R');
	    $this->pdf->Ln(6);
	    $this->pdf->SetFont('Arial','B',9);
	    $this->pdf->Cell(140,8,'');
	    $this->pdf->Cell(20,8,'TOTAL:','TLB',0,'R');
	    $this->pdf->Cell(30,8,$simbolo . $orden['total_a_pagar'],'TRB',0,'R');
	    // $this->pdf->Cell(30,8,$simbolo . substr($orden['total_a_pagar'], 4),'TRB',0,'R');
	    $this->pdf->Ln(15);
	    // $monto = new EnLetras();
	    $en_letra = ValorEnLetras($orden['total_a_pagar'],$moneda);
	    $this->pdf->Cell(0,8,'TOTAL SON: ' . $en_letra ,'',0);

	    $arrData['message'] = 'ERROR';
	    $arrData['flag'] = 2;
	    $timestamp = date('YmdHis');
	    return $this->pdf->Output( 'S','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' );
	}
}