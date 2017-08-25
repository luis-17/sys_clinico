<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CajaTemporalFarm extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_caja_temporal_farmacia','model_config','model_almacen_farmacia','model_entrada_farmacia','model_traslado_farmacia'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	/* 	MOVIMIENTOS TEMPORALES  */
	public function lista_movimientos_temporales(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_caja_temporal_farmacia->m_cargar_movimientos($paramDatos,$paramPaginate);
		$totalRows = $this->model_caja_temporal_farmacia->m_count_movimientos($paramDatos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			/*---- ESTADO ----*/
			if( $row['es_temporal'] == 1 ){
				$estadoMov = 'POR REGULARIZAR';
				$claseMov = 'label-orange';
				$claseIcon = 'fa-clock-o';
			}
			if( $row['es_temporal'] == 3 ){
				$estadoMov = 'REGULARIZADO';
				$claseMov = 'label-success';
				$claseIcon = 'fa-check';
			}
			/*---- TIPO ------*/
			if( $row['tipo_movimiento'] == 1 ){
				$tipMov = 'VENTA';
				$claseMovT = 'label-midnightblue';
				$claseIconT = 'fa-money';
			}elseif($row['tipo_movimiento'] == 2){
				$tipMov = 'COMPRA';
				$claseMovT = 'label-primary';
				$claseIconT = 'fa-shopping-cart';
			}elseif($row['tipo_movimiento'] == 3){
				$tipMov = 'TRASLADOS';
				$claseMovT = 'label-info';
				$claseIconT = 'fa-exchange';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idtipomovimiento' => $row['tipo_movimiento'],
					'tipo_movimiento' => array(
						'string' => $tipMov,
						'clase' =>$claseMovT,
						'icon' => $claseIconT
					),
					'fecha_movimiento' => $row['fecha_movimiento'],
					'usuario' => $row['usuario'],
					'es_temporal' => $row['es_temporal'],
					'total_a_pagar' => $row['total_a_pagar'],
					'razon_social' => $row['razon_social'],
					'ticket_venta' => $row['ticket_venta'],
					'guia_remision' => $row['guia_remision'],
					'idalmacen' => $row['idalmacen'],
					'nombre_alm' => $row['nombre_alm'],
					'idsubalmacen' => $row['idsubalmacen'],
					'nombre_salm' => $row['nombre_salm'],
					'idtrasladoorigen' => $row['idtrasladoorigen'],
					'orden_venta' => $row['orden_venta'] == null? '' : $row['orden_venta'],
					'estado' => array(
						'string' => $estadoMov,
						'clase' =>$claseMov,
						'icon' => $claseIcon,
						'bool' =>$row['es_temporal']
					)
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
	/* LISTADO PRODUCTOS MOVIMIENTOS TEMPORALES */
	public function lista_productos_movimientos_temporales() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_caja_temporal_farmacia->m_cargar_productos_movimientos_temporales($paramDatos,$paramPaginate);
		$totalRows = $this->model_caja_temporal_farmacia->m_count_productos_movimientos_temporales($paramDatos,$paramPaginate);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			/*---- ESTADO ----*/
			// if( $row['es_temporal'] == 1 ){
			// 	$estadoMov = 'POR REGULARIZAR';
			// 	$claseMov = 'label-orange';
			// 	$claseIcon = 'fa-clock-o';
			// }
			// if( $row['es_temporal'] == 3 ){
			// 	$estadoMov = 'REGULARIZADO';
			// 	$claseMov = 'label-success';
			// 	$claseIcon = 'fa-check';
			// }
			
			array_push($arrListado, 
				array( 
					'id' => $row['iddetallemovimiento'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],

					// 'tipo_movimiento' => array(
					// 	'string' => $tipMov,
					// 	'clase' =>$claseMovT,
					// 	'icon' => $claseIconT
					// ),
					'laboratorio' => $row['laboratorio'],
					'precio_unitario_sf' => $row['precio_unitario_sf'],
					'precio_unitario' => $row['precio_unitario'],
					'cantidad' => $row['cantidad'],
					'total_detalle_sf' => $row['total_detalle_sf'],
					'total_detalle' => $row['total_detalle'],
					'stock_actual' => $row['stock_actual_malm'],
					'fecha_movimiento' => $row['fecha_movimiento'],
					
					// 'estado' => array(
					// 	'string' => $estadoMov,
					// 	'clase' =>$claseMov,
					// 	'icon' => $claseIcon,
					// 	'bool' =>$row['es_temporal']
					// )
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

	public function lista_detalle_movimientos_temporales() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['datos'];
		//$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_caja_temporal_farmacia->m_cargar_detalle_movimientos($paramDatos);
		$totalRows = $this->model_caja_temporal_farmacia->m_count_detalle_movimientos($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'cantidad' => $row['cantidad'],
					'total_detalle' => $row['total_detalle'],
					'stock_actual' => $row['stock_actual_malm'],
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

	public function lista_este_movimiento_temporal_almacen() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['datos'];
		//$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_caja_temporal_farmacia->m_cargar_este_movimiento_temporal_almacen($paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'nombre_alm' => $row['nombre_alm'],
					'nombre_salm' => $row['nombre_salm']
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

	public function ver_popup_detalle_movimiento()
	{
		$this->load->view('cajaTemporalFarm/popupVerDetalleMovimiento');
	}

	public function aprobar_movimiento_temporal() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$fMovimiento = $this->model_caja_temporal_farmacia->m_cargar_este_movimiento_temporal($allInputs);
    	$fDetalle = $this->model_caja_temporal_farmacia->m_cargar_detalle_movimientos($allInputs);
    	//var_dump($fDetalle); exit();
    	if($fMovimiento['es_temporal'] != 1){
    		$arrData['message'] = 'Ya se a aprobado un movimiento con este id';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$conStock = TRUE;
    	foreach ($fDetalle as $row) {
    		$row['idalmacen'] = $allInputs['idalmacen'];
    		$row['idsubalmacen'] = $allInputs['idsubalmacen'];
    		$stock_medicamento = $this->model_caja_temporal_farmacia->m_stock_medicamento($row);
    		if( $stock_medicamento['stock_actual_malm'] < 0 ){
    			$conStock = FALSE;
    			break;
    		}
    	}
    	if( !$conStock ){
    		$arrData['message'] = 'El stock aun no se ha regularizado.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	
    	if( $this->model_caja_temporal_farmacia->m_actualizar_movimiento_temporal($allInputs) ){
    		$arrData['message'] = 'La aprobación se realizo correctamente !!!';
    		$arrData['flag'] = 1;
    	}else{
    		$arrData['message'] = 'Ocurrió un error.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	/*
		$lista = $this->model_caja_temporal_farmacia->m_cargar_detalle_movimiento_almacen($allInputs);

		$this->db->trans_begin();
		//---------PROCESO DE APROBACION DE UN MOVIMIENTO (COMPRA o VENTA) --------
		$this->model_caja_temporal_farmacia->m_actualizar_movimiento_temporal($allInputs);
		foreach ($lista as $row) { 
			$row['stock_general']=$this->model_caja_temporal_farmacia->m_stock_general($row);
			$this->model_caja_temporal_farmacia->m_actualizar_stock_medicamento_almacen($row);
			$this->model_caja_temporal_farmacia->m_actualizar_stock_medicamento_general($row);
		}
		//--------FIN DEL PROCESO -------
		//--------SI ES UN TRASLADO ... BUSCAMOS SU TRASLADO ORIGEN---
    	if($fMovimiento['tipo_movimiento'] == 3){
    		$paramDatos['idmovimiento'] = $fMovimiento['idtrasladoorigen'];
    		$lista2 = $this->model_caja_temporal_farmacia->m_cargar_detalle_movimiento_almacen($paramDatos);

			$this->model_caja_temporal_farmacia->m_actualizar_movimiento_temporal($paramDatos);
			foreach ($lista2 as $row) { 
				$row['stock_general']=$this->model_caja_temporal_farmacia->m_stock_general($row);
				$this->model_caja_temporal_farmacia->m_actualizar_stock_medicamento_almacen($row);
				$this->model_caja_temporal_farmacia->m_actualizar_stock_medicamento_general($row);
			}

    	}
    	//--------FIN DEL PROCESO -------
    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'La aprobación se realizo correctamente !!!';
    		$arrData['flag'] = 1;
		}*/
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/*
	public function registrar_movimiento_temporal(){			// REGISTRO DE ENTRADA Y TRASLADOS TEMPORALES
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$allInputs['fecha_traslado'] = $allInputs['fecha_entrada']. ' ' . date('H:i:s');
		// VALIDACIONES 
		if($allInputs['almacen']['id'] == null){
    		$arrData['message'] = 'Debe tener asignado un almacen para poder registrar los datos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		if($allInputs['idtipoentrada'] == 2){
			if(strlen($allInputs['ruc'])  != 11){
    			$arrData['message'] = 'Ingrese un RUC válido';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if($allInputs['factura'] == null || $allInputs['factura'] == ''){
    			$arrData['message'] = 'Ingrese el numero de la Factura';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    	}
    	if($allInputs['idtipoentrada'] == 2 && !$allInputs['estemporal'] ){
    		if($allInputs['orden_compra']['id'] == 0 ){
    			$arrData['message'] = 'No se ha ingresado la orden de compra';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		// verificar si orden de compra esta pendiente 
	    	$ordenPendiente = $this->model_entrada_farmacia->m_verificar_estado($allInputs['orden_compra']['id']);
	    	if ($ordenPendiente['estado_movimiento'] != 2){
	    		$arrData['message'] = 'La Orden de Compra no está disponible, porque ya ha sido atendida o anulada';
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
	    	}
    	}
    	if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún producto/medicamento';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	// validaciones de fecha de vencimiento 
    	foreach ($allInputs['detalle'] as $key => $row) {
    		if(empty($row['fecha_vencimiento'])){
    			$arrData['message'] = 'En uno o mas productos no ha ingresado la fecha de vencimiento.';
	    		$arrData['flag'] = 0;
	    		//var_dump(diferenciaFechas( $row['fecha_vencimiento'],date('d-m-Y') ));exit();
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if( !IsDate($row['fecha_vencimiento']) || !strtotime( $row['fecha_vencimiento'] ) ){
    			$arrData['message'] = 'La fecha de vencimiento no es válida';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if( diferenciaFechas( $row['fecha_vencimiento'],date('d-m-Y') ) <= 1 ){
    			$arrData['message'] = 'La fecha de vencimiento ha caducado.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    	}
    	
    	/* OBTENCION DE DATOS REQUERIDOS
		if($this->sessionHospital['key_group'] != 'key_sistemas'){
    		$allInputs['fecha_entrada'] = date('Y-m-d H:i:s');
    	}else{
    		$allInputs['fecha_entrada'] = $allInputs['fecha_entrada'] . ' ' . date('H:i:s') ;
    	}
		$subAlmacenPrincipal = $this->model_entrada_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id']);
		$allInputs['idsubalmacen'] = $subAlmacenPrincipal['idsubalmacen'];

		if(empty($allInputs['proveedor']['id'])){
			$allInputs['proveedor']['id'] = $allInputs['proveedor']['idproveedor'];
		}
		
		$this->db->trans_begin();
		// CREANDO LA ENTRADA
		if( $this->model_entrada_farmacia->m_registrar_entrada($allInputs) ){
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
				}else{
					$producto['precio_unitario_por_caja'] = null;
					$producto['cantidad_caja'] = null;
				}
				$producto['idmovimiento'] = $allInputs['idmovimiento'];
				$producto['idalmacen'] = $allInputs['almacen']['id']; // necesario para verificar y registrar un ingreso de medicamento
				$producto['idsubalmacen'] = $allInputs['idsubalmacen']; // necesario para registrar un ingreso de medicamento
				
				//$producto['precio'] = $producto['precio_unitario'];
				$producto['subtotal'] = $producto['importe'];
				$producto['estemporal'] = $allInputs['estemporal'];
				// VERIFICAR SI EL PRODUCTO EXISTE EN SUB ALMACEN DESTINO
				if( $medicamentoalmacen = $this->model_entrada_farmacia->m_verificar_producto_destino($producto) ){
					$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
					$this->model_entrada_farmacia->m_registrar_detalle_entrada($producto); // registramos con el idmedicamentoalmacen del destino
					$this->model_entrada_farmacia->m_actualizar_medicamento_almacen_entrada($producto);
				}else{
					// si no existe el producto primero lo ingresamos luego creamos el detalle
					$this->model_entrada_farmacia->m_registrar_medicamento_almacen_entrada($producto);
					$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
					$this->model_entrada_farmacia->m_registrar_detalle_entrada($producto);
				}

				if($this->model_entrada_farmacia->m_actualizar_stock_medicamento($producto)){
					$arrData['message'] = 'Los Productos se registraron correctamente.';
    				$arrData['flag'] = 1;	
				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
    				break;
				}
			}
		}

		//---------------AGREGANDO EL TRASLADO-------------------------------------
		if($subalmacen1=$this->model_almacen_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id'])){
   			$allInputs['idsubalmacen1'] = $subalmacen1['idsubalmacen'];
   			$allInputs['idsubalmacen2'] = $this->sessionHospital['idsubalmacenfarmacia'];
    	}

    	// CREAR LA SALIDA
		if( $this->model_traslado_farmacia->m_registrar_salida($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			if( $producto['caja_unidad'] == 'CAJA' ){
				$producto['cantidad_caja'] = $producto['cantidad'];
				$producto['precio_unitario_por_caja'] = $producto['precio'];
				if(!empty($producto['contenido'])){
					$producto['cantidad'] = $producto['cantidad'] * $producto['contenido'];
					$producto['precio'] = $producto['precio'] / $producto['contenido'];
				}
			}else{
				$producto['precio_unitario_por_caja'] = null;
				$producto['cantidad_caja'] = null;
			}
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
			if( $producto['caja_unidad'] == 'CAJA' ){
				$producto['cantidad_caja'] = $producto['cantidad'];
				$producto['precio_unitario_por_caja'] = $producto['precio'];
				if(!empty($producto['contenido'])){
					$producto['cantidad'] = $producto['cantidad'] * $producto['contenido'];
					$producto['precio'] = $producto['precio'] / $producto['contenido'];
				}
			}else{
				$producto['precio_unitario_por_caja'] = null;
				$producto['cantidad_caja'] = null;
			}
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
		//---------------FIN DE TRASLADO ------------------------------------------
    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'El Movimiento se realizó correctamente';
    		$arrData['flag'] = 1;
		}
		//$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));

	}
	*/



}