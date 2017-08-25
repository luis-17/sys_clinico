<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EntradasFarm extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_entrada_farmacia','model_orden_compra','model_config', 'model_medicamento','model_venta_farmacia','model_almacen_farmacia'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_movimientos_entradas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_entrada_farmacia->m_cargar_entradas($datos, $paramPaginate);
		$totalRows = $this->model_entrada_farmacia->m_count_sum_entradas($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			$objEstado = array(); 
			if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['tipo_movimiento'] == 2 ){
				$estado = 'COMPRA';
				$clase = 'label-success';
			}elseif( $row['tipo_movimiento'] == 4 ){
				$estado = 'REGALO';
				$clase = 'label-info';
			}elseif( $row['tipo_movimiento'] == 6 ){
				$estado = 'REINGRESO';
				$clase = 'label-warning';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idproveedor' => $row['idproveedor'],
					'razon_social' => strtoupper($row['razon_social']),
					'ruc' => $row['ruc'],
					'direccion_fiscal' => $row['direccion_fiscal'],
					'telefono' => $row['telefono'],
					'orden_compra' => $row['orden_compra'],
					'factura' => $row['factura'],
					'guia_remision' => $row['guia_remision'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fecha_compra' => $row['fecha_compra'],
					'fecha_vence_factura' => formatoFechaReporte3($row['fecha_vence_factura']),
					'fmovimiento' => $row['fecha_movimiento'],
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'tipo_entrada' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['tipo_movimiento']
					),
					'estado_movimiento' => $row['estado_movimiento'],
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
	public function lista_entradas_anuladas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_entrada_farmacia->m_cargar_entradas_anuladas($datos, $paramPaginate);
		$totalRows = $this->model_entrada_farmacia->m_count_sum_entradas_anuladas($datos, $paramPaginate);
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
			

			if( $row['tipo_movimiento'] == 2 ){
				$estado = 'COMPRA';
				$clase = 'label-success';
			}elseif( $row['tipo_movimiento'] == 4 ){
				$estado = 'REGALO';
				$clase = 'label-info';
			}elseif( $row['tipo_movimiento'] == 6 ){
				$estado = 'REINGRESO';
				$clase = 'label-warning';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idproveedor' => $row['idproveedor'],
					'razon_social' => strtoupper($row['razon_social']),
					'ruc' => $row['ruc'],
					'direccion_fiscal' => $row['direccion_fiscal'],
					'telefono' => $row['telefono'],
					'factura' => $row['factura'],
					'guia_remision' => $row['guia_remision'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fmovimiento' => $row['fecha_movimiento'],
					'fecha_compra' => $row['fecha_compra'],
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'tipo_entrada' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['tipo_movimiento']
					),
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
	public function lista_detalle_entrada()	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_entrada_farmacia->m_cargar_detalle_entrada($datos); 
		$totalRows = $this->model_entrada_farmacia->m_count_sum_detalle_entrada($datos); 
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) {
			if( $row['caja_unidad'] == 'UNIDAD' && $row['acepta_caja_unidad'] == 1 ){
				$cantidadf = $row['cantidad'] . ' (F)';
			}else{
				$cantidadf = $row['cantidad'];
			}
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'factura' => $row['ticket_venta'],
					// 'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'unidad_medida' => $row['presentacion'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'descuento' => $row['descuento_asignado'],
					'total_detalle' =>  $row['total_detalle'],
					'fecha_vencimiento' => formatoFechaReporte3($row['fecha_vencimiento']),
					'num_lote' => $row['num_lote'],
					'caja_unidad' => $row['caja_unidad'],
					'cantidadf' => $cantidadf
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
    	//$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_productos_entrada() { 
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_entrada_farmacia->m_cargar_producto_entrada($datos, $paramPaginate);
		$totalRows = $this->model_entrada_farmacia->m_count_sum_producto_entrada($datos, $paramPaginate); // var_dump($totalRows); exit();
		$arrListado = array();
		// $sumTotal = 0;
		foreach ($lista as $row) { 
			$objEstado = array();
			array_push($arrListado, 
				array(
					'idmovimiento' => $row['idmovimiento'],
					'factura' => $row['ticket_venta'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idproveedor' => $row['idproveedor'],
					'proveedor' => $row['razon_social'],
					'ruc' => $row['ruc'],
					'idmedicamento' => $row['idmedicamento'],
					'producto' => $row['medicamento'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'lote' => $row['num_lote'],
					'fecha_vencimiento' => $row['fecha_vencimiento'],
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
	public function ver_popup_detalle_entrada(){
		$this->load->view('compraFarmacia/popupVerDetalleCompra');
	}
	public function registrar_entrada(){
		$this->load->model('model_medicamento_almacen');
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['arrIdMedicamentos'] = null;
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0; 
    	// var_dump($allInputs); exit(); 
		/* VALIDACIONES */
		if($allInputs['almacen']['id'] == null){
    		$arrData['message'] = 'Debe tener asignado un almacen para poder registrar los datos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		if($allInputs['idtipoentrada'] == 2){ // 2: compra; 4: regalo; 6: reingreso
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
    		if( !IsDate($allInputs['fecha_vence_factura']) || !strtotime( $allInputs['fecha_vence_factura'] ) ){
				$arrData['message'] = 'La fecha de vencimiento de la factura ' . $allInputs['fecha_vence_factura'] . ' no es válida';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			} 
			if(!empty($allInputs['fecha_vence_factura'])){
				$allInputs['fecha_vence_factura'] = $allInputs['fecha_vence_factura'] . ' ' . date('H:i:s') ;
	    	}else{
	    		$arrData['message'] = 'Debe ingresar la fecha del vencimiento de la factura';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
	    	}
    	}
		if($allInputs['orden_compra']['id'] != 0 ){
			/* verificar si orden de compra esta pendiente */
	    	$ordenPendiente = $this->model_entrada_farmacia->m_verificar_estado($allInputs['orden_compra']['id']);
	    	if ($ordenPendiente['estado_movimiento'] != 2){
	    		$arrData['message'] = 'La Orden de Compra no está disponible, porque ya ha sido atendida o anulada';
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
			}
		}else{
			$allInputs['orden_compra'] = NULL;
		}

    	//var_dump($allInputs); exit();
    	if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún producto/medicamento';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	/* validaciones de fecha de vencimiento y estado*/
    	foreach ($allInputs['detalle'] as $key => $row) {
    		if(empty($row['fecha_vencimiento'])){
    			$arrData['message'] = 'El producto de código: ' . $row['id'] .' no tiene fecha de vencimiento.';
	    		$arrData['flag'] = 0;
	    		//var_dump(diferenciaFechas( $row['fecha_vencimiento'],date('d-m-Y') ));exit();
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if(empty($row['lote'])){ 
    			$arrData['message'] = 'El producto de código: ' . $row['id'] .' no tiene un N° de Lote.';
	    		$arrData['flag'] = 0;
	    		//var_dump(diferenciaFechas( $row['fecha_vencimiento'],date('d-m-Y') ));exit();
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if( !IsDate($row['fecha_vencimiento']) || !strtotime( $row['fecha_vencimiento'] ) ){
    			$arrData['message'] = 'La fecha de vencimiento  ' . $row['fecha_vencimiento'] . ' no es válida';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		$dif = diferenciaFechas( $row['fecha_vencimiento'],date('d-m-Y') );
    		if( $dif <= 1 ){
    			$arrData['message'] = 'La fecha de vencimiento ' . $row['fecha_vencimiento'] . ' ha caducado' ;
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    		if($allInputs['orden_compra']['id'] != 0 ){
    			$detalle = $this->model_orden_compra->m_obtener_estado_detalle_orden_compra($row['iddetallemovimiento']);

    			if( $detalle['estado_detalle'] == 2 ){
    				$arrData['message'] = 'Existen items que no estan disponibles. Cierre la ventana y vuelva a intentarlo' ;
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
    			}
    			$row['orden_compra'] = $allInputs['orden_compra']['descripcion'];
    			// var_dump($row); exit();
    			$cantidad_ingresada = $this->model_entrada_farmacia->m_obtener_cantidad_ingresada_con_orden_compra($row);
    			if( ($cantidad_ingresada + (int)$row['cantidad']) >= (int)$row['cantidad_total'] ){
    				$allInputs['arrIdMedicamentos'][] = $row['idmedicamento']; // para saber que medicamentos de la orden se van a registrar
    			}
    			
    		}
    		// var_dump($allInputs['arrIdMedicamentos']); exit();
    	}

    	/* VALIDAR SUMA DE COMPRAS POR ORDEN DE COMPRA*/
    	if($allInputs['idtipoentrada'] == 2 && $allInputs['orden_compra']['id'] != 0 ){

    		/* TOTAL A PAGAR + SUMA DE LAS COMPRAS ANTERIORES < AL TOTAL A PAGAR DE LA ORDEN DE COMPRA + AJUSTE CONTABLE*/
    		$ajuste_contable = $this->sessionHospital['ajuste_contable'];
			$suma_detalle_compras = $this->model_orden_compra->m_sum_total_entrada_farmacia($allInputs['orden_compra']);
			if(((float)$suma_detalle_compras['total'] + (float)$allInputs['total']) > ((float)$allInputs['orden_compra']['total_a_pagar'] + (int)$ajuste_contable)){
				$arrData['message'] = 'La suma de las compras no puede ser mayor al total de la orden de compra' ;
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			} 			
		}

    	/* OBTENCION DE DATOS REQUERIDOS*/
    	if(!empty($allInputs['fecha_entrada'])){
			$allInputs['fecha_entrada'] = $allInputs['fecha_entrada'] . ' ' . date('H:i:s') ;
    	}else{
    		$arrData['message'] = 'Debe ingresar la fecha del ingreso';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if(!empty($allInputs['fecha_compra'])){
			$allInputs['fecha_compra'] = $allInputs['fecha_compra'] . ' ' . date('H:i:s') ;
    	}else{
    		$arrData['message'] = 'Debe ingresar la fecha de compra que figura en la factura';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if(empty($allInputs['proveedor']['id'])){
			$allInputs['proveedor']['id'] = $allInputs['proveedor']['idproveedor'];
		}
    	
    	// PARA ELIMINA RESTRICCION DE Nº DE FACTURA IGUAL PARA UN MISMO PROVEEDOR
		// if( !empty($allInputs['factura']) && $allInputs['idtipoentrada'] == 2 ){ // COMPRA
		// 	if( $this->model_entrada_farmacia->m_verificar_factura_proveedor($allInputs) ){
		// 		$arrData['message'] = 'Ya se ha registrado la factura: ' . $allInputs['factura'] . ' para el proveedor ' . $allInputs['proveedor']['razon_social'];
		// 		$arrData['flag'] = 0;
		// 		$this->output
		// 			->set_content_type('application/json')
		// 			->set_output(json_encode($arrData));
		// 		return;
		// 	}
		// }
    	// PARA ELIMINAR LA RESTRICCION DE Nº DE FACTURA PARA REGALOS, ESTE Nº PUEDE SER INDEPENDIENTE DE LA FACTURA DE COMPRA
		// if(  !empty($allInputs['factura']) && $allInputs['idtipoentrada'] == 4 ){ // REGALOS
		// 	if( !$this->model_entrada_farmacia->m_verificar_factura_proveedor($allInputs) ){
		// 		$arrData['message'] = 'No existe la factura: ' . $allInputs['factura'] . ' para el proveedor ' . $allInputs['proveedor']['razon_social'] . ' Por favor ingrese un Nº de factura válido.';
		// 		$arrData['flag'] = 0;
		// 		$this->output
		// 			->set_content_type('application/json')
		// 			->set_output(json_encode($arrData));
		// 		return;
		// 	}
		// }
		
		$subAlmacenPrincipal = $this->model_entrada_farmacia->m_obtener_subalmacen_principal($allInputs['almacen']['id']);
		$allInputs['idsubalmacen'] = $subAlmacenPrincipal['idsubalmacen'];

		$this->db->trans_start();  // COMIENZA LA JARANA
		// CREANDO LA ENTRADA 
		if( $this->model_entrada_farmacia->m_registrar_entrada($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento');
			$arrData['idmovimiento'] = $allInputs['idmovimiento'];
			foreach ($allInputs['detalle'] as $key => $producto) {
				// if( $allInputs['orden_compra']['id'] != 0 ){ // si tiene orden de compra 
				// 	$allInputs['arrIdMedicamentos'][] = $producto['idmedicamento']; // para saber que medicamentos de la orden se van a registrar
				// } 
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
				$producto['idsubalmacen'] = $allInputs['idsubalmacen']; // necesario para registrar un ingreso de medicamento m_registrar_medicamento_almacen_entrada
				$producto['subtotal'] = $producto['importe'];
				// VERIFICAR SI EL PRODUCTO EXISTE EN SUB ALMACEN DESTINO
				if( $medicamentoalmacen = $this->model_entrada_farmacia->m_verificar_producto_destino($producto) ){
					$producto['idmedicamentoalmacen'] = $medicamentoalmacen['idmedicamentoalmacen'];
					$this->model_entrada_farmacia->m_registrar_detalle_entrada($producto); // registramos con el idmedicamentoalmacen del destino

					$this->model_entrada_farmacia->m_actualizar_medicamento_almacen_entrada($producto);
					// ACTUALIZAR ULTIMO PRECIO DE COMPRA 
					// ACTUALIZAR SOLO CUANDO ES UNA COMPRA 
					if( $allInputs['idtipoentrada'] == 2 ){ // COMPRA 
						$this->model_entrada_farmacia->m_actualizar_ultimo_precio_compra($producto);
						// ACTUALIZAR UTILIDADES EN MEDICAMENTO - ALMACEN 
						// buscamos medicamento almacen de tipo FARMACIA: 
						$fMedicamentoAlmacen = $this->model_medicamento_almacen->m_cargar_este_medicamento_almacen_tipo_farmacia($producto['idalmacen'],$producto['idmedicamento']); 
						// var_dump($fMedicamentoAlmacen); // exit(); 
						if( !empty($fMedicamentoAlmacen) ){
							if( $fMedicamentoAlmacen['precio_ultima_compra_num'] > 0 && $fMedicamentoAlmacen['precio_venta_num'] > 0 ){
								$utilidadValor = $fMedicamentoAlmacen['precio_venta_num'] - $fMedicamentoAlmacen['precio_ultima_compra_num'];
								$utilidadPorcentaje = (($fMedicamentoAlmacen['precio_venta_num'] - $fMedicamentoAlmacen['precio_ultima_compra_num']) / $fMedicamentoAlmacen['precio_ultima_compra_num']) * 100; 
								$arrUtilidades = array( 
									'idmedicamento'=> $producto['idmedicamento'],
									'idalmacen'=> $producto['idalmacen'],
									'utilidad_valor'=> $utilidadValor, 
									'utilidad_porcentaje'=> $utilidadPorcentaje 
								); 
								$this->model_entrada_farmacia->m_actualizar_utilidades_compra($arrUtilidades);
							}
						} 
					} 
				}else{
					// si no existe el producto primero lo ingresamos luego creamos el detalle 
					$this->model_entrada_farmacia->m_registrar_medicamento_almacen_entrada($producto);
					$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
					$this->model_entrada_farmacia->m_registrar_detalle_entrada($producto);
					
					// AHORA AGREGAMOS ESE PRODUCTO A LOS DEMÁS SUBALMACENES Y ACTUALIZAMOS EL ULTIMO PRECIO DE COMPRA 
					$otrosAlmacenes = $this->model_almacen_farmacia->m_cargar_subalmacenes_sin_central($producto); 
					foreach ($otrosAlmacenes as $key => $row) {
						$productoOtrosAlm = array(
							'idalmacen' => $row['idalmacen'],
							'idsubalmacen' => $row['idsubalmacen'],
							'idmedicamento' => $producto['idmedicamento'] 
						);
						$medicamentoalmacenOtrosAlm = $this->model_entrada_farmacia->m_verificar_producto_destino($productoOtrosAlm);
						if( empty($medicamentoalmacenOtrosAlm) ){ 
							$productoOtrosAlm['precio'] = $producto['precio']; 
							$productoOtrosAlm['cantidad'] = 0; 
							$productoOtrosAlm['estemporal'] = FALSE;
							$this->model_entrada_farmacia->m_registrar_medicamento_almacen_entrada($productoOtrosAlm);
						}
					}
					$this->model_entrada_farmacia->m_actualizar_ultimo_precio_compra($producto); 
				} 
				// CALCULAR STOCK DEL MEDICAMENTO 
				$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($producto['idmedicamento']);
	    		if(!(empty($listaMedicamento))){ 
	    			$rowAux['stock_actual_modificado'] = 0;
	    			foreach ($listaMedicamento as $key => $rowLM) {
	    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
	    			}
	    			$rowAux['idmedicamento'] = $producto['idmedicamento'];
					if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
		    			$arrData['message'] = 'Se registraron los datos correctamente';
						$arrData['flag'] = 1;
		    		}
	    		} 
			}
			/* solo para entradas con orden de compra */
			if($allInputs['idtipoentrada'] == 2 && $allInputs['orden_compra']['id'] != 0 ){
				// con esto se actualiza el estado del detalle de la orden para que no este disponible en una proxima entrada
				if( !empty($allInputs['arrIdMedicamentos']) ){
					$this->model_orden_compra->m_actualizar_estado_detalle_orden_compra($allInputs);
				}
				
				// verificar si no hay mas detalles de la orden que esten disponibles
				$disponibles = $this->model_orden_compra->m_verificar_detalles_disponibles_orden_compra($allInputs);
				if( $disponibles == 0 ){
					$this->model_orden_compra->m_actualizar_estado_orden_compra($allInputs);
				}
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_entrada() {
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
    		// RECUPERAR LOS DETALLES DEL MOVIMIENTO Y ACTUALIZAR MEDICAMENTOALMACEN
	    	$listaDetalle = $this->model_entrada_farmacia->m_cargar_detalle_movimiento($allInputs[0]['idmovimiento']);
	    	foreach ($listaDetalle as $key => $row) {
	    		$this->model_entrada_farmacia->m_anular_entrada_medicamento_almacen($row);
	    	}
    		$arrData['message'] = 'La entrada se anuló correctamente';
    		$arrData['flag'] = 1;
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// FORMULAS Y PREPARADOS
	public function recibir_formula(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se recepcionaron las formulas correctamente';
    	$arrData['flag'] = 1;
    	$fecha_recepcion = $allInputs['fecha_recepcion'] . ' ' . $allInputs['hora'] . ':' .$allInputs['minuto'] . ':00';
    	if ( !IsDate($allInputs['fecha_recepcion']) ) {
    		$arrData['message'] = 'Ingrese una fecha válida';
		    $arrData['flag'] = 0;
		    $this->db->trans_complete();
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	if( !IsTime($allInputs['hora'] . ':' .$allInputs['minuto'] . ':00') ){
    		$arrData['message'] = 'Ingrese una hora válida';
		    $arrData['flag'] = 0;
		    $this->db->trans_complete();
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	foreach ($allInputs['detalle'] as $row) {
    		if( strtotime($fecha_recepcion) < strtotime($row['fecha_movimiento']) ){
				$arrData['message'] = 'La fecha de recepción de '. $row['formula'] .' no puede ser anterior a la fecha de venta';
			    $arrData['flag'] = 0;
			    $this->db->trans_complete();
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    			
    		}
    	}

    	
		// var_dump($fecha_recepcion);
		// exit();
    	$this->db->trans_start();
    	foreach ($allInputs['detalle'] as $row) {
    		$row['guia_remision'] = $allInputs['guia'];
    		$row['fecha_recepcion'] = $fecha_recepcion;
			if( $this->model_entrada_farmacia->m_recepcionar_formulas($row) ){
				if( !$this->model_entrada_farmacia->m_actualizar_medicamento_almacen_entrada($row) ){
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

	public function recibir_formula_tecnica(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se recepcionaron las formulas correctamente';
    	$arrData['flag'] = 1;
    	$fecha_recepcion = $allInputs['fecha_recepcion'] . ' ' . $allInputs['hora'] . ':' .$allInputs['minuto'] . ':00';
    	if ( !IsDate($allInputs['fecha_recepcion']) ) {
    		$arrData['message'] = 'Ingrese una fecha válida';
		    $arrData['flag'] = 0;
		    $this->db->trans_complete();
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	if( !IsTime($allInputs['hora'] . ':' .$allInputs['minuto'] . ':00') ){
    		$arrData['message'] = 'Ingrese una hora válida';
		    $arrData['flag'] = 0;
		    $this->db->trans_complete();
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	foreach ($allInputs['detalle'] as $row) {
    		if( strtotime($fecha_recepcion) < strtotime($row['fecha_movimiento']) ){
				$arrData['message'] = 'La fecha de recepción de '. $row['formula'] .' no puede ser anterior a la fecha de venta';
			    $arrData['flag'] = 0;
			    $this->db->trans_complete();
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    			
    		}
    	}

    	
		// var_dump($fecha_recepcion);
		// exit();
    	$this->db->trans_start();
    	foreach ($allInputs['detalle'] as $row) {
    		$row['guia_remision'] = $allInputs['guia'];
    		$row['fecha_recepcion'] = $fecha_recepcion;
			if(!$this->model_entrada_farmacia->m_recepcionar_formulas_tecnica($row) ){
				$arrData['message'] = 'No se pudo recepcionar';
		    	$arrData['flag'] = 0;
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function confirmar_recepcion_formula(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se confirmaron las formulas correctamente';
    	$arrData['flag'] = 1;

   		// VALIDACION
    	foreach ($allInputs as $row) {
    		$detalle = NULL;
    		$detalle = $this->model_venta_farmacia->m_cargar_detalle_por_id($row['iddetallemovimiento']);
    		if( $detalle['estado_preparado'] != 4 ){
    			$arrData['message'] = 'Algunos items de la selección no están en espera de confirmación. Quítelos de la selección e inténtelo nuevamente';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
	    		return;
    		}
    	}

    	$this->db->trans_start();
    	foreach ($allInputs as $row) {
			if( $this->model_entrada_farmacia->m_confirmar_recepcion_formulas($row) ){
				if( !$this->model_entrada_farmacia->m_actualizar_medicamento_almacen_entrada($row) ){
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

	public function ver_popup_entrada_formula(){
		$this->load->view('almacenFarmacia/recepcion_formula_formView');
	}
}