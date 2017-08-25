<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SalidasFarm extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_salida_farmacia','model_config', 'model_entrada_farmacia','model_medicamento_almacen','model_medicamento'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	/* SALIDAS */
	public function lista_salidas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$arrData['flag'] = 0;
		$arrData['message'] = '';
    	if(!IsDate($datos['desde']) || !IsDate($datos['hasta']) ){
			$arrData['message'] = 'Seleccione un fecha valida. ';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		$lista = $this->model_salida_farmacia->m_cargar_salidas($datos, $paramPaginate);
		$totalRows = $this->model_salida_farmacia->m_count_salidas($datos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_movimiento'] == 1 ){
				$estadoMov = 'APROBADO';
				$claseMov = 'label-success';
				$claseIcon = 'fa-check';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$estadoMov = 'ANULADO';
				$claseMov = 'label-danger';
				$claseIcon = 'fa-ban';
			}

			if( $row['estado_movimiento'] == 3 ){
				$estadoMov = 'EN ESPERA';
				$claseMov = 'label-info';
				$claseIcon = 'fa-clock-o';
			}

			if($row['tipo_movimiento']==5){
				//$tipo = 'BAJA';
				$labeltipo = 'BAJA';
				$labelclase = 'label-default';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idsubalmacen' => $row['idsubalmacen'],
					'subAlmacen' => strtoupper($row['subAlmacen']),
					'tipomovimiento' => array(
						'string' => $labeltipo,
						'clase' => $labelclase
						),

					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'estado_movimiento' => $row['estado_movimiento'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'iduser' => $row['iduser'],
					'usuario' => $row['apellido_paterno'] . ' ' . $row['apellido_materno'] .', ' . $row['nombres'],
					'iduseraprobacion' => $row['iduseraprobacion'],
					'usuario_aprobacion' => $row['aprob_apellido_paterno'] . ' ' . $row['aprob_apellido_materno'] .', ' . $row['aprob_nombres'],
					'estadomov' => array(
						'string' => $estadoMov,
						'clase' =>$claseMov,
						'icon' => $claseIcon,
						'bool' =>$row['estado_movimiento']
					),
				)
			);
		}
    	$arrData['datos'] = $arrListado; 
    	$arrData['paginate']['totalRows'] = $totalRows;
    	
		if(!empty($lista)){ 
			$arrData['flag'] = 1; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_salidas_anuladas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$arrData['flag'] = 0;
		$arrData['message'] = '';
    	if(!IsDate($datos['desde']) || !IsDate($datos['hasta']) ){
			$arrData['message'] = 'Seleccione un fecha valida. ';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		$lista = $this->model_salida_farmacia->m_cargar_salidas_anuladas($datos, $paramPaginate);
		$totalRows = $this->model_salida_farmacia->m_count_salidas_anuladas($datos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_movimiento'] == 1 ){
				$estadoMov = 'APROBADO';
				$claseMov = 'label-success';
				$claseIcon = 'fa-check';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$estadoMov = 'ANULADO';
				$claseMov = 'label-danger';
				$claseIcon = 'fa-ban';
			}

			if( $row['estado_movimiento'] == 3 ){
				$estadoMov = 'EN ESPERA';
				$claseMov = 'label-info';
				$claseIcon = 'fa-clock-o';
			}

			if($row['tipo_movimiento']==5){
				//$tipo = 'BAJA';
				$labeltipo = 'BAJA';
				$labelclase = 'label-default';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idsubalmacen' => $row['idsubalmacen'],
					'subAlmacen' => strtoupper($row['subAlmacen']),
					'tipomovimiento' => array(
						'string' => $labeltipo,
						'clase' => $labelclase
						),

					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'estado_movimiento' => $row['estado_movimiento'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'iduser' => $row['idusers'],
					'usuario' => $row['apellido_paterno'] . ' ' . $row['apellido_materno'] .', ' . $row['nombres'],
					'estadomov' => array(
						'string' => $estadoMov,
						'clase' =>$claseMov,
						'icon' => $claseIcon,
						'bool' =>$row['estado_movimiento']
					),
				)
			);
		}
    	$arrData['datos'] = $arrListado; 
    	$arrData['paginate']['totalRows'] = $totalRows;
    	
		if(!empty($lista)){ 
			$arrData['flag'] = 1; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_producto_salidas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
    	if(!strtotime($datos['desde']) || !strtotime($datos['hasta']) ){
			$arrData['message'] = 'Seleccione un fecha valida. ';
    		$arrData['flag'] = 99;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		$lista = $this->model_salida_farmacia->m_cargar_producto_salidas($datos, $paramPaginate);
		$totalRows = $this->model_salida_farmacia->m_count_producto_salidas($datos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_movimiento'] == 1 ){
				$estadoMov = 'APROBADO';
				$claseMov = 'label-success';
				$claseIcon = 'fa-check';
			}
			if( $row['estado_movimiento'] == 3 ){
				$estadoMov = 'EN ESPERA';
				$claseMov = 'label-info';
				$claseIcon = 'fa-clock-o';
			}
			if( $row['estado_detalle'] == 0  ){
				$estadoMov = 'ANULADO';
				$claseMov= 'label-danger';
				$claseIcon = 'fa-ban';
			}
			if($row['tipo_movimiento']==5){
				//$tipo = 'BAJA';
				$labeltipo = 'BAJA';
				$labelclase = 'label-default';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idsubalmacen' => $row['idsubalmacen'],
					'subAlmacen' => strtoupper($row['subAlmacen']),
					'tipomovimiento' => array(
						'string' => $labeltipo,
						'clase' => $labelclase
						),

					'fecha_movimiento' => date('d-m-Y H:i:s', strtotime($row['fecha_movimiento'])),
					'producto' => strtoupper($row['denominacion']),
					'cantidad' => $row['cantidad'],
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'] ,
					'estado_movimiento' => $row['estado_movimiento'],
					'motivo_movimiento' => $row['motivo_movimiento'],
					'estado_detalle' => $row['estado_detalle'],
					'estadomov' => array(
						'string' => $estadoMov,
						'clase' =>$claseMov,
						'icon' => $claseIcon,
						'bool' =>$row['estado_movimiento']
					),
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
	public function lista_detalle_salidas() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		//$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_salida_farmacia->m_cargar_detalle_salidas($datos);
		//$totalRows = $this->model_salida_farmacia->m_count_salidas($datos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'idmovimiento' => $row['idmovimiento'],
					'idmedicamento' => $row['idmedicamento'],
					'cantidad' => $row['cantidad'],
					'fecha_vencimiento' => $row['fecha_vencimiento'],
					'num_lote' => $row['num_lote'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'medicamento' => strtoupper($row['denominacion'])
				)
			);
		}
    	$arrData['datos'] = $arrListado; 
    	//$arrData['paginate']['totalRows'] = $totalRows;
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
		$lista = $this->model_salida_farmacia->m_cargar_productos_subalmacen($datos, $paramPaginate);
		$totalRows = $this->model_salida_farmacia->m_count_productos_subalmacen($datos, $paramPaginate);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'producto' => $row['denominacion'],
					'stock' => $row['stock_actual_malm']
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
	public function lista_salida_en_espera() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_salida_farmacia->m_cargar_salida_en_espera($datos, $paramPaginate);
		$totalRows = $this->model_salida_farmacia->m_count_salida_en_espera($datos,$paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if($row['tipo_movimiento']==5){
				$tipo = 'BAJA';
			}
			$objEstado = array();
			$objEstado['claseIcon'] = 'ti-timer';
			$objEstado['claseLabel'] = 'badge-orange';
			$objEstado['labelText'] = 'POR APROBAR';
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idsubalmacen' => $row['idsubalmacen'],
					'subAlmacen' => strtoupper($row['subAlmacen']),
					'tipomovimiento' => $tipo,
					'motivo_movimiento' => $row['motivo_movimiento'],
					'fecha_movimiento' => date('d-m-Y H:i:s', strtotime($row['fecha_movimiento'])),
					'estado_movimiento' => $objEstado
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

	public function realizar_salida()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	foreach ($allInputs['productos'] as $key => $producto) {
    		$medicamentoalmacen = $this->model_salida_farmacia->m_obtener_stock_producto($producto);
	    	if($producto['cantidad'] > $medicamentoalmacen['stock_actual_malm']){
	    		//var_dump($medicamentoalmacen['stock_actual_malm']);
	    		$arrData['flag'] = 0;
	    		$arrData['message'] = 'Ha ingresado una cantidad que supera el stock.';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
	    	}
	    	if( empty($producto['fecha_vencimiento'])){
	    		$arrData['flag'] = 0;
	    		$arrData['message'] = 'No ha ingresado fecha de vencimiento para el producto ' . $producto['producto'];
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
	    	}else{
	    		$fecha_vencimiento = date('d-m-Y',strtotime($producto['fecha_vencimiento']));
	    		//
		    	if(!IsDate($producto['fecha_vencimiento'])){
		    		$arrData['message'] = '"' . $producto['fecha_vencimiento'] . '" No es una fecha válida. ';
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
		    	}	

	    	}
			//var_dump($fecha_vencimiento); exit();
	    	
	    	
    	}
    	if( count($allInputs['productos']) < 1){
    		$arrData['message'] = 'Seleccione un producto para dar de baja';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	if(!strtotime($allInputs['fecha_salida'])){
			$arrData['message'] = 'Seleccione un fecha valida. ';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if($this->sessionHospital['key_group'] != 'key_sistemas'){
    		$allInputs['fecha_salida'] = date('Y-m-d H:i:s');
    	}else{
    		$allInputs['fecha_salida'] = $allInputs['fecha_salida'] . ' ' . date('H:i:s') ;
    	}
    	//var_dump($allInputs['productos']); exit();
    	$this->db->trans_begin();
    	// CREAR LA SALIDA
		if( $this->model_salida_farmacia->m_registrar_salida($allInputs) ){
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
		}
		foreach ($allInputs['productos'] as $key => $producto) {
			$producto['idmovimiento'] = $allInputs['idmovimiento'];
			$this->model_salida_farmacia->m_registrar_detalle_salida($producto);
			//$this->model_salida_farmacia->m_actualizar_medicamento_almacen_salida($producto,1);
		}

    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'La salida se realizo correctamente';
    		$arrData['flag'] = 1;
		}
    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function aprobar_salida() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	
    	$fSalida = $this->model_salida_farmacia->m_cargar_esta_salida_por_solicitud($allInputs);
    	if( !empty($fSalida) ){
    		$arrData['message'] = 'Ya se aprobo una salida, usando esta solicitud : <strong>'.$allInputs['idmovimiento'].'</strong>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	$this->db->trans_begin();

    	$this->model_salida_farmacia->m_aprobar_salida($allInputs['idmovimiento']);

    	$listaProductos = $this->model_salida_farmacia->m_cargar_detalle_salidas($allInputs);
		foreach ($listaProductos as $key => $producto) {
	    	$stockgen = $this->model_salida_farmacia->m_stock_general($producto);
	    	$producto['stock_general'] = $stockgen - $producto['cantidad'];
			$this->model_salida_farmacia->m_actualizar_medicamento_stock_general($producto);
			$this->model_salida_farmacia->m_actualizar_medicamento_almacen_salida($producto,1);
		}

    	if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		    $arrData['message'] = 'El proceso se realizo correctamente';
    		$arrData['flag'] = 1;
		}
    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));

	}
	public function anular_salida()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs['idmovimiento']); exit();
		$arrData['message'] = 'Se produjo un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$mensaje_registro = '';
    	// VALIDAR SI EL MOVIMIENTO ESTA YA ESTA ANULADO
    	$movimiento = $this->model_entrada_farmacia->m_verificar_estado($allInputs['idmovimiento']);
    	if( $movimiento['estado_movimiento'] == 0){
    		$arrData['message'] = 'La salida ya está anulada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if($this->model_entrada_farmacia->m_anular_movimiento($allInputs['idmovimiento'])){
    		// RECUPERAR LOS DETALLES DEL MOVIMIENTO 
    		
    		$listaDetalle = $this->model_entrada_farmacia->m_cargar_detalle_movimiento($allInputs['idmovimiento']);
	    	foreach ($listaDetalle as $key => $row) {
	    		$this->model_salida_farmacia->m_anular_detalle_salida($row);
	    		// ACTUALIZAR MEDICAMENTOALMACEN SOLO SI ES UNA BAJA APROBADA
	    		if( $movimiento['estado_movimiento'] == 1){ 
		    		$this->model_entrada_farmacia->m_actualizar_medicamento_almacen_entrada($row);
		    		// CALCULAR STOCK DEL MEDICAMENTO 
					$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($row['idmedicamento']);
		    		if( !empty($listaMedicamento) ){ 
		    			$rowAux['stock_actual_modificado'] = 0;
		    			foreach ($listaMedicamento as $key => $rowLM) {
		    				$rowAux['stock_actual_modificado'] += $rowLM['stock_actual_malm'];
		    			}
		    			$rowAux['idmedicamento'] = $row['idmedicamento'];
						if($this->model_medicamento->m_actualizar_stock_medicamento($rowAux)){
			    			$mensaje_registro = ' y se reestableció el stock general del producto';
							$arrData['flag'] = 1;
			    		}
		    		}
	    		}
    		}
	    	
    		$arrData['message'] = 'La salida se anuló correctamente' . $mensaje_registro;
    		$arrData['flag'] = 1;
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));


		// $allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// $arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	// $arrData['flag'] = 0;
  		// $producto['idmedicamento'] = $allInputs['idmedicamento'];

  		// $this->db->trans_begin();

		// $this->model_salida_farmacia->m_anular_detalle_salida($allInputs);
		// 	$stockgen = $this->model_salida_farmacia->m_stock_general($producto);
		// 	$producto['stock_general'] = $stockgen + $allInputs['cantidad'];
		// $this->model_salida_farmacia->m_actualizar_medicamento_stock_general($producto);
		// $this->model_salida_farmacia->m_actualizar_medicamento_almacen_salida($allInputs,2);

  		// if ($this->db->trans_status() === FALSE)
		// {
		//     $this->db->trans_rollback();
		// }
		// else
		// {
		//     $this->db->trans_commit();
		//     $arrData['message'] = 'El proceso se realizo correctamente';
  		// $arrData['flag'] = 1;
		// }
    	
		// $this->output
		//     ->set_content_type('application/json')
		//     ->set_output(json_encode($arrData));
	}

	public function ver_popup_traslado() {
		$this->load->view('almacenFarmacia/salidas_formView');
	}
	public function ver_popup_detalle_salida() {
		$this->load->view('salidas-farmacia/popupVerDetalleSalida');
	}
	public function validar_cantidad() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ha ingresado una cantidad que supera el stock.';
    	$arrData['flag'] = 0;

    	if(!is_numeric($allInputs['cantidad']) || ($allInputs['cantidad']) <= 0 ){
    		$arrData['message'] = 'Ha ingresado una cantidad que no es valida.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	$medicamentoalmacen = $this->model_salida_farmacia->m_obtener_stock_producto($allInputs);
    	if($allInputs['cantidad'] <= $medicamentoalmacen['stock_actual_malm']){
    		$arrData['flag'] = 1;
    		$arrData['message'] = 'Cantidad Correcta'; 
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}