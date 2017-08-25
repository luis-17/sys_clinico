<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Egresos extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_egresos','model_empresa','model_config'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); 
	}
	public function lista_egresos() { 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_egresos->m_cargar_egresos($datos, $paramPaginate);
		$fContador = $this->model_egresos->m_count_egresos($datos, $paramPaginate);
		$arrListado = array(); 
		$totalSumatoriaMontos = 0; 
		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_movimiento'] == 1 ){ // REGISTRADO (gris)
				$objEstado['claseIcon'] = 'ti ti-write';
				$objEstado['claseLabel'] = 'label-inverse';
				$objEstado['labelText'] = 'REGISTRADO';
			}elseif( $row['estado_movimiento'] == 2 ){ // OBSERVADO (amarillo) 
				$objEstado['claseIcon'] = 'ti ti-eye';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'OBSERVADO';
			}elseif( $row['estado_movimiento'] == 3 ){ // APROBADO (verde)
				$objEstado['claseIcon'] = 'ti ti-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'APROBADO';
			}elseif( $row['estado_movimiento'] == 4 ){ // PAGADO (azul)
				$objEstado['claseIcon'] = 'ti ti-money';
				$objEstado['claseLabel'] = 'label-primary';
				$objEstado['labelText'] = 'PAGADO';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			} 
			$rowTotalAPagar = $row['total_a_pagar'];
			$rowTotalAPagarStr = $row['total_a_pagar_str'];
			// SI ES RECIBO POR HONORARIO 
			if( $row['idtipodocumento'] == 4 ){ // RxH 
				$rowTotalAPagar = $row['sub_total']; 
				$rowTotalAPagarStr = $row['sub_total_str']; 
			}
			if( $row['estado_movimiento'] == 1 ){ 
				$totalSumatoriaMontos += $rowTotalAPagarStr; 
			} 
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idempresa' => $row['idempresa'], // proveedor // total 
					'empresa' => strtoupper($row['empresa']),
					'servicio_asignado' => strtoupper($row['servicio_asignado']),
					'ruc' => $row['ruc_empresa'],
					'serie_documento' => $row['serie_documento'],
					'numero_documento'=> $row['serie_documento'].' - '.$row['numero_documento'],
					'idoperacion'=> $row['idoperacion'],
					'operacion'=> $row['descripcion_op'],
					'idsuboperacion'=> $row['idsuboperacion'],
					'suboperacion'=> $row['descripcion_sop'],
					'orden_compra' => $row['orden_compra'],
					'cuenta_contable'=> $row['codigo_plan'],
					'idtipodocumento'=> $row['idtipodocumento'],
					'descripcion_td'=> $row['descripcion_td'],
					'porcentaje_imp'=> $row['porcentaje_imp'],
					'fecha_registro' => formatoFechaReporte($row['fecha_registro']),
					'fecha_emision' => formatoFechaReporte3($row['fecha_emision']),
					'fecha_aprobacion' => formatoFechaReporte($row['fecha_aprobacion']),
					'fecha_pago' => formatoFechaReporte($row['fecha_pago']),
					'fecha_credito'=> formatoFechaReporte($row['fecha_credito']),
					'forma_pago'=> $row['forma_pago'],
					'periodo_asignado'=> $row['periodo_asignado'],
					'modo_igv' => $row['modo_igv'],
					'sub_total' => $row['sub_total'],
					'total_impuesto' => $row['total_impuesto'],
					'total_a_pagar' => $rowTotalAPagar,
					'total_a_pagar_str' => $rowTotalAPagarStr,
					'detraccion' => $row['detraccion'],
					'deposito' => $row['deposito'],
					'usuario' => $row['empleado'],
					'estado_movimiento' => $row['estado_movimiento'],
					'estado' => $objEstado
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = $totalSumatoriaMontos;
    	$arrData['paginate']['totalRows'] = $fContador['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_egreso_autocomplete() { 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_egresos->m_cargar_egreso_autocomplete($allInputs);
		// var_dump($allInputs); exit();

		$arrListado = array(); 
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idmovimiento'],
					'idmovimiento' => $row['idmovimiento'],
					'descripcion'=> $row['numero_documento'].' - '.$row['glosa'],
					'idoperacion'=> $row['idoperacion'],
					'idsuboperacion'=> $row['idsuboperacion'],
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
	public function lista_detalle_egresos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_egresos->m_cargar_detalle_egresos($datos, $paramPaginate);
		$fContador = $this->model_egresos->m_count_detalle_egresos($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			$objEstado = array();
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idempresa' => $row['idempresa'],
					'numero_documento' => $row['numero_documento'],
					'serie_documento' => $row['serie_documento'],
					'empresa' => strtoupper($row['descripcion']),
					'idcentrocosto' => $row['idcentrocosto'],
					'centro_costo' => strtoupper($row['nombre_cc']),
					'sub_cat_centro_costo' => strtoupper($row['descripcion_scc']),
					'cat_centro_costo' => strtoupper($row['descripcion_ccc']),
					'ruc' => $row['ruc_empresa'],
					'direccion_fiscal' => $row['domicilio_fiscal'],
					'telefono' => $row['telefono'],
					'fecha_registro' => formatoFechaReporte($row['fecha_registro']),
					'fecha_emision' => formatoFechaReporte($row['fecha_emision']),
					'fecha_aprobacion' => formatoFechaReporte($row['fecha_aprobacion']),
					'fecha_pago' => formatoFechaReporte($row['fecha_pago']),
					'total_detalle' => $row['importe_local_con_igv'],
					'glosa' => $row['glosa'] 
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($fContador['suma_total']) ? 0 : $fContador['suma_total'];
    	$arrData['paginate']['totalRows'] = $fContador['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_detalle_de_un_egreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$datos = $allInputs['datos'];
		// $notaCredito = $allInputs['sensor']; //para saber si es para una nota de credito 
		$lista = $this->model_egresos->m_cargar_detalle_de_un_egreso($datos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'item' => $row['iddetallemovimiento'],
					'codigo' => $row['codigo_plan'],
					'idcentrocosto' => $row['idcentrocosto'],
					'codigo_cc' => $row['codigo_cc'],
					'nombre_cc' => strtoupper($row['nombre_cc']),
					'glosa' => strtoupper($row['glosa']),
					
					'importe' => $row['num_importe_local'],
					'importe_base' => $row['num_importe_local'],
					'num_importe_local' => $row['num_importe_local'],
					'importe_local_con_igv' => $row['importe_local_con_igv'],
					'sub_total_documento' => $row['sub_total'],
					'inafecto' => $row['inafecto'],
					'porc_doc_referencia' => $row['porcentaje_imp'],
					'doc_abreviatura' => $row['abreviatura'],
					'nombre_impuesto_referencia' => $row['nombre_impuesto'],
					'codigo_plan_referencia'=> $row['codigo_plan_referencia'],
					'total_doc_referencia'=> $row['total_a_pagar'] 

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
	public function listar_seguimiento_estados()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$fEgreso = $this->model_egresos->m_cargar_este_egreso($datos);
		$arrListado = array();
		$arrAuxRegistrado = array();
		$arrAuxObservado = array();
		$arrAuxAprobado = array();
		$arrAuxPagado = array();
		$arrAuxAnulado = array();
		$classBtn = array();
		// var_dump('$arrListado',$fEgreso); exit();
		if( !empty($fEgreso['fecha_movimiento']) ){ // registrado 
			$arrAuxRegistrado = array(
				'usuario'=> $fEgreso['reg_nombres'].' '.$fEgreso['reg_apellido_paterno'].' '.$fEgreso['reg_apellido_materno'],
				'string'=> 'creó el egreso en el sistema con estado: <span class="text-bold text-info">REGISTRADO</span>',
				'estado'=> 'REGISTRADO',
				'momento'=> $fEgreso['fecha_movimiento']
			);
			$classBtn = array( 
				'disabledObservado'=> 'enabled',
				'disabledAprobado'=> 'enabled',
				'disabledPagado'=> 'disabled',
				'disabledAnulado'=> 'enabled'
			);
			array_push($arrListado,$arrAuxRegistrado); 
		}
		if( !empty($fEgreso['fecha_observacion']) ){ // observado  
			$arrAuxObservado = array(
				'usuario'=> $fEgreso['obs_nombres'].' '.$fEgreso['obs_apellido_paterno'].' '.$fEgreso['obs_apellido_materno'],
				'string'=> 'cambió estado de <span class="text-bold text-info"> REGISTRADO </span> a <span class="text-bold text-info">OBSERVADO</span>',
				'estado'=> 'OBSERVADO',
				'momento'=> $fEgreso['fecha_observacion']
			);
			$classBtn = array( 
				'disabledObservado'=> 'disabled',
				'disabledAprobado'=> 'enabled',
				'disabledPagado'=> 'disabled',
				'disabledAnulado'=> 'enabled'
			);
			array_push($arrListado,$arrAuxObservado); 
		}
		if( !empty($fEgreso['fecha_aprobacion']) ){ // aprobado 
			if( empty($fEgreso['fecha_observacion']) ){ 
				$otroTexto = 'cambió de estado de <span class="text-bold text-info">REGISTRADO</span> a <span class="text-bold text-info">APROBADO</span>'; 
			}else{
				$otroTexto = 'cambió de estado de <span class="text-bold text-info">OBSERVADO</span> a <span class="text-bold text-info">APROBADO</span>'; 
			}
			$arrAuxAprobado = array(
				'usuario'=> $fEgreso['apro_nombres'].' '.$fEgreso['apro_apellido_paterno'].' '.$fEgreso['apro_apellido_materno'],
				'string'=> $otroTexto,
				'estado'=> 'APROBADO',
				'momento'=> $fEgreso['fecha_aprobacion']
			);
			$classBtn = array(
				'disabledObservado'=> 'disabled',
				'disabledAprobado'=> 'disabled',
				'disabledPagado'=> 'enabled',
				'disabledAnulado'=> 'enabled'
			);
			array_push($arrListado,$arrAuxAprobado); 
		}
		if( !empty($fEgreso['fecha_pago']) ){ // pagado 
			$arrAuxPagado = array(
				'usuario'=> $fEgreso['pag_nombres'].' '.$fEgreso['pag_apellido_paterno'].' '.$fEgreso['pag_apellido_materno'],
				'string'=> 'cambió de estado de <span class="text-bold text-info">APROBADO</span> a <span class="text-bold text-info">PAGADO</span>',
				'estado'=> 'PAGADO',
				'momento'=> $fEgreso['fecha_pago']
			);
			$classBtn = array(
				'disabledObservado'=> 'disabled',
				'disabledAprobado'=> 'disabled',
				'disabledPagado'=> 'disabled',
				'disabledAnulado'=> 'enabled'
			);
			array_push($arrListado,$arrAuxPagado); 
		}
		// var_dump($fEgreso); exit();
		if( !empty($fEgreso['fecha_anulacion']) ){ // anulado 
			$arrAuxAnulado = array(
				'usuario'=> $fEgreso['anu_nombres'].' '.$fEgreso['anu_apellido_paterno'].' '.$fEgreso['anu_apellido_materno'],
				'string'=> 'cambió estado a <span class="text-bold text-danger">ANULADO</span>',
				'estado'=> 'ANULADO',
				'momento'=> $fEgreso['fecha_anulacion']
			);
			$classBtn = array(
				'disabledObservado'=> 'disabled',
				'disabledAprobado'=> 'disabled',
				'disabledPagado'=> 'disabled',
				'disabledAnulado'=> 'enabled'
			);
			array_push($arrListado,$arrAuxAnulado); 
		} 
    	$arrData['datos'] = $arrListado;
    	$arrData['class_btn'] = $classBtn;
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($arrListado)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_busqueda_centro_costo()
	{
		$this->load->view('egreso/popup_busqueda_centro_costo');
	}
	public function ver_popup_nuevo_egreso_servicio()
	{
		$this->load->view('egreso/egreso_formview');
	}
	public function ver_popup_filtro_periodo()
	{
		$this->load->view('egreso/filtrosReporteTercero_formView');
	}
	public function ver_popup_detalle_egreso()
	{
		$this->load->view('egreso/detalleEgreso_formView');
	}
	public function ver_popup_asientos_contables()
	{
		$this->load->view('egreso/asientosContables_formView');
	}
	public function generar_codigo_orden() { 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// "PREFIJO_CATEGORIA DE CONCEPTO" + AÑO + '-' + CORRELATIVO 
		$codigoOrden = $allInputs['categoria'];
		$codigoOrden .= date('y');
		$codigoOrden .= '-';
		// OBTENER ULTIMA ORDEN DE COMPRA 
		$fUltimoEgreso = $this->model_egresos->m_cargar_ultimo_egreso($allInputs);
		if( empty($fUltimoEgreso) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($fUltimoEgreso['orden_compra'], -6, 6);
			if( substr($fUltimoEgreso['orden_compra'], -11, 2) == date('y') ){ 
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
	public function registrar_egreso()
	{
		ini_set('xdebug.var_display_max_depth', 5);
	    ini_set('xdebug.var_display_max_children', 256);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit(); 
		if( empty($allInputs['inafecto']) ){
	    	$allInputs['inafecto'] = FALSE;
	    }
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		/* VALIDACIONES */
		if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún egreso';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
		if(strlen($allInputs['ruc']) != 11){
			$arrData['message'] = 'Ingrese un RUC válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}

		$empresa = $this->model_empresa->m_cargar_esta_empresa_por_ruc($allInputs);
		if(empty($empresa['idempresa'])){
			$arrData['message'] = 'Ingrese un RUC válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$allInputs['proveedor']['idempresa'] = $empresa['idempresa'];
		
    	if( $allInputs['total'] == 'NaN' || empty($allInputs['total']) ){
    		$arrData['message'] = 'No se puede calcular el importe. Corrija los montos e intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	$errorEnBucle = 'no';
    	foreach ($allInputs['detalle'] as $key => $row) {
    		if( empty($row['importe']) ){
    			$errorEnBucle = 'si'; 
    		}
    	}
    	if( $errorEnBucle === 'si' ){ 
    		$arrData['message'] = 'No se puede calcular el importe. Corrija los montos e intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	// Validar que la fecha de emisión no sea mayor a la fecha actual
    	$tsFechaEmision = strtotime($allInputs['fecha_emision']);
    	$tsFechaActual = strtotime(date('Y-m-d'));
    	if( $tsFechaActual < $tsFechaEmision ){ 
    		$arrData['message'] = 'La Fecha de Emisión no puede ser a futuro.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}
    	if( $allInputs['tipodocumento']['id'] == 7 || $allInputs['tipodocumento']['id'] == 14 ){ 

    		if(empty($allInputs['numero_egreso']['idmovimiento'])){
    			$arrData['message'] = 'No se ha seleccionado un documento de referencia.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}

    		// verificar notas de creditos creadas de un documento (diferencias de montos)
			/*foreach ($allInputs['detalle'] as $key => $value2) {
				$notasCreditos = $this->model_egresos->m_cargar_notas_credito_existentes_egreso($value2); 
				$suma = $value2['importe'] + $notasCreditos;

				if($value2['importe'] > $value2['num_importe_local']){
	    			$arrData['message'] = 'El importe del item '.$value2['codigo'].'-'.$value2['codigo_cc'].' no puede ser mayor al del documento inicial';
	    			
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
	    		}

				if($suma > $value2['num_importe_local']){
	    			$arrData['message'] = 'El importe del item '.$value2['codigo'].'-'.$value2['codigo_cc'].' ya posee notas de credito';
	    			
		    		$arrData['flag'] = 0;
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
	    		}			
	    	}*/
	    	if( empty( $allInputs['total'] ) ) { 
    			$arrData['message'] = 'No puede ingresar una nota de crédito/débito con un valor nulo.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
	    	if($allInputs['subtotal'] > $allInputs['detalle'][0]['sub_total_documento']){
				$arrData['message'] = 'El Total de la Nota de Credito/Débito no debe ser mayor al de Documento origen';

	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			}
			if( !(is_numeric($allInputs['total'])) ){
    			$arrData['message'] = 'No se puede ingresar una cadena como monto. Revisa e intentálo nuevamente. ';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
    		}
    	}
    	
		/* CALCULOS */ 
		$fTipoCambio = ObtenerTipoCambio(); 
		$allInputs['detraccion'] = NULL;
		$allInputs['deposito'] = $allInputs['total'];
		if( $allInputs['total'] > 700 ){ 
			$allInputs['detraccion']  = $allInputs['total'] * ( 10 / 100);
			$allInputs['deposito'] = $allInputs['total'] - $allInputs['detraccion'];
		}
		$allInputs['idmoneda'] = 0; 
		if($allInputs['moneda'] === 'soles'){ 
			$allInputs['idmoneda'] = 1; 
		}elseif($allInputs['moneda'] === 'dolares'){
			$allInputs['idmoneda'] = 2;

			// CONVERTIR A SOLES Y GUARDAR COMO SOLES. 
			$allInputs['subtotal'] = round($allInputs['subtotal'] * $fTipoCambio['venta'],2);
			$allInputs['impuesto'] = round($allInputs['impuesto'] * $fTipoCambio['venta'],2);
			$allInputs['total'] = round($allInputs['total'] * $fTipoCambio['venta'],2);
		} 

		$allInputs['idtipocambio'] = $fTipoCambio['idtipocambio'];
		$allInputs['compra'] = $fTipoCambio['compra'];
		$allInputs['venta'] = $fTipoCambio['venta'];
		$allInputs['codigo_plan'] = $allInputs['detalle'][0]['codigo'];
		$allInputs['glosa'] = $allInputs['detalle'][0]['glosa']; 

		$this->db->trans_start();
		if($allInputs['tipodocumento']['id'] == 7 ){ // NOTA DE CRÉDITO 
			$signo = -1;
		}else{
			$signo = 1;
		}
		$allInputs['subtotal'] = $allInputs['subtotal'] * $signo;
		$allInputs['impuesto'] = $allInputs['impuesto'] * $signo;
		$allInputs['total'] = $allInputs['total'] * $signo;
		if( $this->model_egresos->m_registrar_egreso($allInputs) ){ 
			$arrData['message'] = 'Se vá registrando sólo la cabecera...';
			$arrData['flag'] = 1;
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','ct_movimiento');
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idmovimiento'] = $allInputs['idmovimiento'];
				if(@$allInputs['idmoneda'] == 2){
					$row['importe'] = round($row['importe'] * $fTipoCambio['venta'],2);
				}
				$row['importe'] = $row['importe'] * $signo;
				$row['importe_local_con_igv'] = $row['importe_local_con_igv'] * $signo;

				$row['compra'] = $fTipoCambio['compra'];
				$row['venta'] = $fTipoCambio['venta'];
				if( $allInputs['tipodocumento']['id'] == 7 || $allInputs['tipodocumento']['id'] == 14 ){
					$row['importe_local_con_igv'] = $allInputs['total']; 
					$row['importe'] = $allInputs['subtotal'];
				}
				if( $this->model_egresos->m_registrar_detalle_egreso($row) ){ 
					$arrData['message'] = 'Los datos se registaron correctamente';
					$arrData['flag'] = 1;
				} // registramos con el idmedicamentoalmacen del destino glosa
			} 
			/* REGISTRAR ASIENTO CONTABLE */ 
			// SI ES NOTA DE CRÉDITO AGREGAMOS EL NOMBRE DEL IMPUESTO DE REFERENECIA. 
			if( $allInputs['tipodocumento']['id'] == 7 ){ 
				$allInputs['tipodocumento']['nombre_impuesto'] = $allInputs['nombre_impuesto_referencia']; 
			} 
			if( $allInputs['tipodocumento']['nombre_impuesto'] == 'IGV' ){
				$debeHaber = 'D'; 
				$total = $allInputs['total']; 
				$subtotal = $allInputs['subtotal']; 
			} 
			if( $allInputs['tipodocumento']['nombre_impuesto'] == 'RETENCION' ){ 
				$debeHaber = 'H'; 
				$total = $allInputs['subtotal'];
				$subtotal = $allInputs['total'];
			} 
			if( $allInputs['tipodocumento']['nombre_impuesto'] == 'NOHAY' ){ 
				$debeHaber = NULL; 
				$total = $allInputs['total']; 
				$subtotal = $allInputs['subtotal']; 
			} 
			// AGREGAR EN DEBE MONTO SIN IMPUESTO 
			$arrAsientoContable = array(); 
			$arrDataAC = array( 
				'idmovimiento'=> $allInputs['idmovimiento'],
				'codigo_plan'=> $allInputs['codigo_plan'],
				'glosa'=> $allInputs['glosa'],
				'monto'=> $subtotal,
				'fecha_emision'=> $allInputs['fecha_emision'],
				'debe_haber'=> 'D'
			); 
			$arrAsientoContable[] = $arrDataAC;

			// AGREGAR EL IMPUESTO 
			if( $debeHaber || $allInputs['inafecto'] ){ 
				$arrDataAC = array( 
					'idmovimiento'=> $allInputs['idmovimiento'],
					'codigo_plan'=> $allInputs['tipodocumento']['codigo_plan'],
					'glosa'=> $allInputs['glosa'],
					'monto'=> $allInputs['impuesto'],
					'fecha_emision'=> $allInputs['fecha_emision'],
					'debe_haber'=> $debeHaber 
				); 
				$arrAsientoContable[] = $arrDataAC; 
			} 
			
			// AGREGAR EN HABER EL TOTAL 
			$arrDataAC = array(
				'idmovimiento'=> $allInputs['idmovimiento'],
				'codigo_plan'=> $allInputs['operacion']['codigo_amarre'],
				'glosa'=> $allInputs['glosa'],
				'monto'=> $total,
				'fecha_emision'=> $allInputs['fecha_emision'],
				'debe_haber'=> 'H'
			); 
			$arrAsientoContable[] = $arrDataAC; 
			
			foreach ($arrAsientoContable as $key => $row) {
				if( $this->model_egresos->m_registrar_asiento($row) ){ 
					$arrData['message'] = 'Los datos se registaron correctamente';
					$arrData['flag'] = 1;
				}
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cambiar_estado_egreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo cambiar el estado';
    	$arrData['flag'] = 0;
    	if( $allInputs['num_estado'] == '2' ){ // A OBSERVADO 
    		if( $this->model_egresos->m_cambiar_estado_egreso_a_observado($allInputs) ){ 
				$arrData['message'] = 'Se cambió de estado correctamente'; 
	    		$arrData['flag'] = 1;
			}
    	}elseif( $allInputs['num_estado'] == '3' ){ // A APROBADO 
    		if( $this->model_egresos->m_cambiar_estado_egreso_a_aprobado($allInputs) ){ 
				$arrData['message'] = 'Se cambió de estado correctamente'; 
	    		$arrData['flag'] = 1;
			}
    	}elseif( $allInputs['num_estado'] == '4' ){ // A PAGADO 
    		if( $this->model_egresos->m_cambiar_estado_egreso_a_pagado($allInputs) ){ 
				$arrData['message'] = 'Se cambió de estado correctamente'; 
	    		$arrData['flag'] = 1;
			}
    	}elseif( $allInputs['num_estado'] == '0' ){ // A ANULADO 
    		if( $this->model_egresos->m_anular_egreso($allInputs['idmovimiento']) ){ 
				$arrData['message'] = 'Se cambió de estado correctamente'; 
	    		$arrData['flag'] = 1;
			}
    	} 
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_egreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
    	// foreach ($allInputs as $row) {
		if( $this->model_egresos->m_anular_egreso($allInputs['idmovimiento']) ){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		} 
		// }
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
	public function exportar_excel(){
		ini_set('max_execution_time', 300);
	    ini_set('memory_limit','160M');
	    $this->load->library(array('excel'));
	    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No hay datos';
    	$arrData['flag'] = 0;

    	$arrListado = array();
    	if(!empty($allInputs['datos'])){
	    	foreach ($allInputs['datos'] as $key => $row) {
	    		$list = array(
	    		 	$row['numero_documento'],
	    		 	$row['empresa'],
	    		 	$row['ruc'],
		            $row['fecha_registro'],
		            $row['fecha_emision'],
		            $row['fecha_pago'],
		            $row['periodo_asignado'],
		            $row['cuenta_contable'],
		            str_replace('S/. ', '', $row['sub_total']) ,
		            str_replace('S/. ', '', $row['total_impuesto']),
		            $row['total_a_pagar_str'],
		            /*$row['detraccion'],
		            $row['deposito'],*/
		            $row['estado']['labelText'],
	    		);
	    		array_push($arrListado, $list);
	    	}
		}
    	$dataColumnsTP = array(
    					'Nº FACTURA', 
    					'EMPRESA/PROVEEDOR', 
    					'RUC', 
    					'FECHA DE REGISTRO', 
    					'FECHA DE EMISION', 
    					'FECHA DE PAGO', 
    					'PERIODO', 
    					'CUENTA CONTABLE', 
    					'SUBTOTAL', 
    					'IMPUESTO', 
    					'IMPORTE',
    					/*'DETRACCIÓN',
    					'DEPÓSITO',*/
    					'ESTADO'
    				);

    	$i = 0;
    	$cont = 0;
    	$currentCellEncabezado = 5;

    	//titulo
		$this->excel->setActiveSheetIndex($cont);
    	$this->excel->getActiveSheet()->setTitle($allInputs['titulo']); 
    	$styleArrayTitle = array(
		    'font'=>  array(
		        'bold'  => true,
		        'size'  => 14,
		        'name'  => 'Verdana',
		        'color' => array('rgb' => '000000')
		    ),
		    'alignment' => array(
		        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    ),
	    );
    	$this->excel->getActiveSheet()->getCell('D1')->setValue($allInputs['titulo']); 
	    $this->excel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('D1:G1');    
	    
	    $string1 = 'Fecha Desde: ' . $allInputs['filtros']['desde'] . ' ' . $allInputs['filtros']['desdeHora']. ':' . $allInputs['filtros']['desdeMinuto'] . ':00';
	    $string2 = 'Fecha Hasta: ' . $allInputs['filtros']['hasta'] . ' ' . $allInputs['filtros']['hastaHora']. ':' . $allInputs['filtros']['hastaMinuto'] . ':00';
	    $string3 = 'Operación: ' . $allInputs['filtros']['operacion']['descripcion'];
	    $this->excel->getActiveSheet()->getCell('C3')->setValue($string1);
	    $this->excel->getActiveSheet()->getCell('E3')->setValue($string2);
	    $this->excel->getActiveSheet()->getCell('G3')->setValue($string3);	   

	    $styleArrayHeader = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),
	      'alignment' => array(
	          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	      ),
	      'font'=>  array(
	          'bold'  => true,
	          'size'  => 10,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => '0790a2') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => '9de5ee', ),
	       ),
	    );  	    
    	$this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);    	

		$ultimaColumna = 'L';

		//merge y aplicar estilos		
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->applyFromArray($styleArrayHeader);

	    //cuerpo
    	$styleArrayProd = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 10,
	          'name'  => 'Verdana',
	      ),
  
	    );	
	    $cellTotal = count($arrListado) + $currentCellEncabezado; 
    	$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
    	
    	//estilo cuerpo
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->applyFromArray($styleArrayProd);	    
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	    //estilo celdas general
    	$this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
    	$this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

    	for ($i=$currentCellEncabezado+1; $i <= $cellTotal ; $i++) { 
    		$this->excel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('J'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('K'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('L'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    	}

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/listadoEgresos_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/listadoEgresos_'.$dateTime.'.xls',
	      'flag'=> 1
	    );    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
}
?>