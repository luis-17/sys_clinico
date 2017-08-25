<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TrasladosFarm extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_traslado_farmacia','model_config','model_medicamento_almacen','model_almacen_farmacia','model_entrada_farmacia', 'model_guia_remision'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}

	/* TRASLADOS */
	public function lista_traslados() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_traslado_farmacia->m_cargar_traslados($datos, $paramPaginate);
		$totalRows = $this->model_traslado_farmacia->m_count_traslados($datos, $paramPaginate);
		//var_dump($lista); exit();
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

			$guiaEstado = array();
			if( $row['guias'] != 0 ){ // NO 
				$guiaEstado['claseLabel'] = 'label-info';
				$guiaEstado['labelText'] = 'TIENE '. $row['guias'].' GUÍA DE REMISIÓN';
				$guiaEstado['numero'] = $row['guias'];
			}
			array_push($arrListado, 
				array( 
					'idmovimiento1' => $row['idmovimiento1'],
					'idmovimiento2' => $row['idmovimiento2'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idalmacen2' => $row['idalmacen2'],
					'almacen2' => strtoupper($row['nombre_alm2']),
					'idsubalmacen1' => $row['idsubalmacen1'],
					'subAlmacenOrigen' => strtoupper($row['subAlmacenOrigen']),
					'idsubalmacen2' => $row['idsubalmacen2'],
					'subAlmacenDestino' => strtoupper($row['subAlmacenDestino']),
					// ALMACEN DESTINO
					// 'id_almacen_destino' => $row['id_almacen_destino'],
					// 'almacen_destino' => strtoupper($row['almacen_destino']), 
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'motivo_movimiento' => $row['motivo_movimiento'],
					'usuario' => $row['usuario'],
					'estado_movimiento' => $row['estado_movimiento'],
					'estado' => $objEstado,	
					'idempresaadmin' => $row['idempresaadmin'],
					'nombre_legal' => $row['nombre_legal'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'ruc' => $row['ruc'],
					'razon_social' => $row['razon_social'],
					'guias' => $guiaEstado
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
	public function lista_Productos_SubAlmacen(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_traslado_farmacia->m_cargar_productos_subalmacen($datos, $paramPaginate);
		$totalRows = $this->model_traslado_farmacia->m_count_productos_subalmacen($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'producto' => $row['denominacion'],
					'stock' => $row['stock_actual_malm'],
					'precio' => number_format((float)$row['precio'], 2, '.', '')
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
	public function lista_detalle_traslado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_traslado_farmacia->m_cargar_detalle_traslado($datos, $paramPaginate);
		$totalRows = $this->model_traslado_farmacia->m_count_detalle_traslado($datos, $paramPaginate);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'idmedicamento' => $row['idmedicamento'],
					'producto' => strtoupper(trim($row['denominacion'])),
					'laboratorio' => strtoupper(trim($row['nombre_lab'])),
					'cantidad' => $row['cantidad']
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
	public function realizar_traslado()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	var_dump($allInputs); exit(); 
    	if($allInputs['idsubalmacenorigen'] == $allInputs['idsubalmacen2']){
    		$arrData['message'] = 'Error no se puede hacer traslado en un mismo sub almacen';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	foreach ($allInputs['productos'] as $key => $producto) {
    		$medicamentoalmacen = $this->model_traslado_farmacia->m_obtener_stock_producto($producto);
	    	if($producto['cantidad'] > $medicamentoalmacen['stock_actual_malm']){
	    		//var_dump($medicamentoalmacen['stock_actual_malm']);
	    		$arrData['flag'] = 0;
	    		$arrData['message'] = 'Ha ingresado una cantidad que supera el stock.';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
	    	}
    	}
    	if( count($allInputs['productos']) < 1){
    		$arrData['message'] = 'Seleccione un producto para trasladar';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if($this->sessionHospital['key_group'] != 'key_sistemas'){
    		$allInputs['fecha_traslado'] = date('Y-m-d H:i:s');
    	}else{
    		$allInputs['fecha_traslado'] = $allInputs['fecha_traslado'] . ' ' . $allInputs['hora_traslado'] ;
    	}
    	//$this->db->trans_start();
    	$this->db->trans_begin();
    	// CREAR LA SALIDA
		if( $this->model_traslado_farmacia->m_registrar_salida($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			$producto['idmovimiento'] = $allInputs['idmovimiento'];
			$producto['estemporal'] = $allInputs['estemporal'];
			$this->model_traslado_farmacia->m_registrar_detalle_salida($producto);
			$this->model_traslado_farmacia->m_actualizar_medicamento_almacen_salida($producto);
		}
		// CREAR LA ENTRADA
		$allInputs['idtrasladoorigen'] = $allInputs['idmovimiento'];
		if( $this->model_traslado_farmacia->m_registrar_entrada($allInputs) ){
			$allInputs['idmovimiento2'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			$producto['idmovimiento'] = $allInputs['idmovimiento2'];
			$producto['idalmacen'] = $allInputs['almacenDestino']['id']; // necesario para verificar y registrar un ingreso de medicamento
			$producto['idsubalmacen'] = $allInputs['idsubalmacen2']; // necesario para registrar un ingreso de medicamento
			$producto['estemporal'] = $allInputs['estemporal'];
			// VERIFICAR SI EL PRODUCTO EXISTE EN SUB ALMACEN DESTINO
			if( $medicamentoalmacen = $this->model_traslado_farmacia->m_verificar_producto_destino($producto) ){
				$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
				$this->model_traslado_farmacia->m_registrar_detalle_entrada($producto); // registramos con el idmedicamentoalmacen del destino 
				// POR AQUI YA NO SE EDITAN PRECIOS  DE VENTA 
				$producto['precio'] = NULL; 
				// if( !empty($producto['precio']) ){
				// 	$precio_venta_anterior = $this->model_medicamento_almacen->m_listar_precio_venta($producto['idmedicamentoalmacen']);
				// 	if( $precio_venta_anterior['precio_venta'] != $producto['precio'] ){
				// 		$producto['precio_venta_anterior'] = $precio_venta_anterior['precio_venta'];
				// 		$producto['precio_venta'] = $producto['precio'];
				// 		$this->model_medicamento_almacen->m_registrar_historial_precio($producto);
				// 	}
				// }
				$this->model_traslado_farmacia->m_actualizar_medicamento_almacen_entrada($producto); 
				$this->model_entrada_farmacia->m_actualizar_ultimo_precio_compra($producto);
			}else{
				// si no existe el producto primero lo ingresamos luego creamos el detalle
				$this->model_traslado_farmacia->m_registrar_medicamento_almacen_entrada($producto);
				$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
				$this->model_traslado_farmacia->m_registrar_detalle_entrada($producto);
			}	
		}
	
    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'El traslado se realizo correctamente';
    		$arrData['flag'] = 1;
		}
		//$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function realizar_traslado_temporal()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$allInputs['fecha_traslado'] = $allInputs['fecha_entrada'];
		//$allInputs['idtiposubalmacen'] = 1;
    	if($subalmacen1=$this->model_almacen_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id'])){
   			$allInputs['idsubalmacen1'] = $subalmacen1['idsubalmacen'];
   			$allInputs['idsubalmacen2'] = $this->sessionHospital['idsubalmacenfarmacia'];
    	}
    	$this->db->trans_begin();
    	// CREAR LA SALIDA
		if( $this->model_traslado_farmacia->m_registrar_salida($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			$producto['idmovimiento'] = $allInputs['idmovimiento'];
			$producto['idalmacen'] = $allInputs['almacen']['id']; // necesario para verificar y registrar un ingreso de medicamento
			$producto['idsubalmacen'] = $allInputs['idsubalmacen1']; // nec
			$producto['estemporal'] =$allInputs['estemporal'];
	
			$medicamentoalmacen = $this->model_traslado_farmacia->m_verificar_producto_destino_temporal($producto);
			$producto['idmedicamentoalmacen']=$medicamentoalmacen['idmedicamentoalmacen'];
	
			$this->model_traslado_farmacia->m_registrar_detalle_salida($producto);
			$this->model_traslado_farmacia->m_actualizar_medicamento_almacen_salida($producto);
		}
		// CREAR LA ENTRADA
		$allInputs['idtrasladoorigen'] = $allInputs['idmovimiento'];
		if( $this->model_traslado_farmacia->m_registrar_entrada($allInputs) ){
			$allInputs['idmovimiento2'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			$producto['idmovimiento'] = $allInputs['idmovimiento2'];
			$producto['idalmacen'] = $this->sessionHospital['idalmacenfarmacia']; // necesario para verificar y registrar un ingreso de medicamento
			$producto['idsubalmacen'] = $this->sessionHospital['idsubalmacenfarmacia']; // necesario para registrar un ingreso de medicamento
			$producto['estemporal'] = $allInputs['estemporal'];
			// VERIFICAR SI EL PRODUCTO EXISTE EN SUB ALMACEN DESTINO
			if( $medicamentoalmacen = $this->model_traslado_farmacia->m_verificar_producto_destino_temporal($producto) ){
				$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
				$this->model_traslado_farmacia->m_registrar_detalle_entrada($producto); // registramos con el idmedicamentoalmacen del destino
				$this->model_traslado_farmacia->m_actualizar_medicamento_almacen_entrada($producto);
			}else{
				// si no existe el producto primero lo ingresamos luego creamos el detalle
				$this->model_traslado_farmacia->m_registrar_medicamento_almacen_entrada($producto);
				$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
				$this->model_traslado_farmacia->m_registrar_detalle_entrada($producto);
			}	
		}
    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'El traslado se realizo correctamente';
    		$arrData['flag'] = 1;
		}
		//$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function validar_cantidad() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ha ingresado una cantidad que supera el stock.';
    	$arrData['flag'] = 0;
    	if( $allInputs['cantidad'] <= 0 ){
    		$arrData['message'] = 'Ingrese una cantidad mayor de cero.';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	
    	if ( !soloNumeros($allInputs['cantidad']) ) {
    		$arrData['message'] = 'Ingrese un número entero';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	$medicamentoalmacen = $this->model_traslado_farmacia->m_obtener_stock_producto($allInputs);
    	if($allInputs['cantidad'] <= $medicamentoalmacen['stock_actual_malm']){
    		$arrData['flag'] = 1;
    		$arrData['message'] = 'Cantidad Correcta'; 
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_traslado() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ocurrió un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDAR SI EL MOVIMIENTO YA ESTA ANULADO
    	$movimiento = $this->model_entrada_farmacia->m_verificar_estado($allInputs[0]['idmovimiento1']);
    	if( $movimiento['estado_movimiento'] == 0){
    		$arrData['message'] = 'El Traslado ya está anulado.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	// VALIDAR SI TIENE GUIAS DE RESIMISION GENERADAS
    	$arrFilters = array( 
    		'searchColumn' => 'idmovimiento',
    		'searchText' => $allInputs[0]['idmovimiento1']
    	);
    	$fGuia = $this->model_guia_remision->m_cargar_esta_guia($arrFilters);
    	if( !empty($fGuia) ){		
    		$arrData['message'] = 'Tiene guía de remisión asociadas';
    		$arrData['flag'] = 0; 
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;  		
    	}

    	$this->db->trans_start();
    	// ACTUALIZAR EN TABLA far_movimiento LOS 2 MOVIMIENTOS
    	if($this->model_traslado_farmacia->m_anular_movimiento($allInputs[0]['idmovimiento1'])){
	    	// RECUPERAR LOS DETALLES DEL PRIMER MOVIMIENTO Y ACTUALIZAR MEDICAMENTOALMACEN
	    	$listaDetalle = $this->model_traslado_farmacia->m_cargar_detalle_movimiento($allInputs[0]['idmovimiento1']);

	    	foreach ($listaDetalle as $key => $row) {
	    		$row['estemporal'] = FALSE;
	    		$this->model_traslado_farmacia->m_anular_salida_medicamento_almacen($row);
	    	}
	    	if($this->model_traslado_farmacia->m_anular_movimiento($allInputs[0]['idmovimiento2'])){
		    	// RECUPERAR LOS DETALLES DEL SEGUNDO MOVIMIENTO Y ACTUALIZAR MEDICAMENTOALMACEN
		    	$listaDetalle = $this->model_traslado_farmacia->m_cargar_detalle_movimiento($allInputs[0]['idmovimiento2']);
		    	foreach ($listaDetalle as $key => $row) {
		    		$row['estemporal'] = FALSE;
		    		$this->model_traslado_farmacia->m_anular_entrada_medicamento_almacen($row);
		    	}
		    	$arrData['message'] = 'El traslado se anuló correctamente';
    			$arrData['flag'] = 1;
	    	}
    	}

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}