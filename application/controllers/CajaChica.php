<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CajaChica extends CI_Controller {

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_caja_chica', 'model_empleado','model_config','model_empresa','model_egresos','model_historial_caja_chica'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
		
	public function ver_popup_formulario(){
		$this->load->view('caja-chica/aperturaCajaChica_formView');
	}
	
	public function lista_caja_chica_disponible_cbo(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = $this->model_caja_chica->m_cargar_caja_chica_disponible_cbo(); 
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idcajachica' => $row['idcajachica'],
					'nombre' => $row['nombre'],
					'estado_cch' => $row['estado_cch'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],			
					'idcentrocosto' => $row['idcentrocosto'],			
					'numero_cheque' => $row['numero_cheque'],			
					'monto_cheque' => $row['monto_cheque'],				
				)
			);
		}

		$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Aún no tiene caja chica asignada.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_saldo_anterior(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(empty($allInputs['idcajachica'])){
			$arrData['flag'] = 2;
			$arrData['message'] = 'Seleccione una caja para aperturar';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$arrCaja = $this->model_caja_chica->m_cargar_saldo_anterior($allInputs);
		if(empty($arrCaja)){
			$arrData['datos'] = 0;
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró historial de caja disponible';
		}else{
			$arrData['datos'] = $arrCaja['saldo'];
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		// var_dump($lista); exit();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function registrar_apertura_caja_chica(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ocurrió un error registrando la caja';

		// VALIDACIONES
		$tieneCajaChicaUsuario = $this->model_caja_chica->m_tiene_caja_chica_usuario();
		if($tieneCajaChicaUsuario){
			$arrData['message'] = 'Ya tiene una caja aperturada o liquidada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		$cajaAbierta = $this->model_caja_chica->m_cargar_esta_caja_abierta($allInputs);
		if($cajaAbierta['idusuarioresponsable'] == $this->sessionHospital['idempleado']){
			$arrData['message'] = 'Esta Caja ya fué aperturada por su Ud.';
    		$arrData['flag'] = 2;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		if($cajaAbierta){
			$arrData['message'] = 'Esta Caja ya ha sido aperturada. Elija otra por favor';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}		

		if($this->model_caja_chica->m_registrar_apertura_caja($allInputs)){
			$arrData['message'] = 'Caja chica aperturada.';
    		$arrData['flag'] = 1;
		}
    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function verifica_caja_chica_usuario(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['flag'] = 0;
		$arrData['message'] = 'No hay caja abierta.';
		$cc = $this->model_caja_chica->m_cargar_caja_chica_usuario();
		$cc['fecha_apertura'] = formatoFechaReporte3($cc['fecha_apertura']);
		if(!empty($cc['idcajachica'])){
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Hay caja abierta';
    		$arrData['cajaChica'] = $cc;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
	public function ver_popup_nuevo_movimiento(){
		$this->load->view('caja-chica/cajaChica_formView');
	}
	public function ver_popup_liquidacion(){
		$this->load->view('caja-chica/liquidacion_formView');
	}	
	public function ver_popup_cierre(){
		$this->load->view('caja-chica/cierre_formView');
	}
	public function regitrar_mov_caja_chica(){
		ini_set('xdebug.var_display_max_depth', 5);
	    ini_set('xdebug.var_display_max_children', 256);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
	    // var_dump($allInputs); exit(); 
		
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		/* VALIDACIONES */
		if( count($allInputs['detalle']) < 1){
    		$arrData['message'] = 'No se ha agregado ningún compra';
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

    	$cajaChica = $this->model_caja_chica->m_cargar_caja_chica_usuario();
    	if($cajaChica['estado_acc'] != 1){ 
    		$arrData['message'] = 'No se puede registrar movimiento en caja liquidada o cerrada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	if($allInputs['total'] > $cajaChica['saldo_numeric']){
    		$arrData['message'] = 'No se puede registrar un movimiento mayor al saldo de caja.';
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
		/* CALCULOS */ 
		$fTipoCambio = ObtenerTipoCambio();
		$allInputs['detraccion'] = NULL;
		$allInputs['deposito'] = $allInputs['total'];			
		$allInputs['idmoneda'] = 1; 			
		$allInputs['idtipocambio'] = $fTipoCambio['idtipocambio'];
		$allInputs['compra'] = $fTipoCambio['compra'];
		$allInputs['venta'] = $fTipoCambio['venta'];
		$allInputs['codigo_plan'] = $allInputs['detalle'][0]['codigo']; 
		// var_dump($allInputs['detalle'][0]['codigo']); exit();
		$allInputs['glosa'] = $allInputs['detalle'][0]['descripcion']; 
		if( empty($allInputs['inafecto']) ){
			$allInputs['inafecto'] = NULL;
		}
		/* REGISTRO */
		$this->db->trans_start();
		$cch = $this->model_caja_chica->m_cargar_caja_chica_usuario();
		$allInputs['idaperturacajachica'] = $cch['idaperturacajachica'];
		if( $this->model_caja_chica->m_registrar($allInputs) ){ 
			$arrData['message'] = 'Se vá registrando sólo la cabecera...';
			$arrData['flag'] = 1;
			$allInputs['idmovimiento'] = GetLastId('idmovimiento','ct_movimiento');
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idmovimiento'] = $allInputs['idmovimiento'];					
				$row['compra'] = $fTipoCambio['compra'];
				$row['venta'] = $fTipoCambio['venta'];
				if( $this->model_caja_chica->m_registrar_detalle($row) ){
					$arrData['message'] = 'Los datos se registaron correctamente';
					$arrData['flag'] = 1;
				} 
			}
			if($this->actualizar_saldo_caja_chica($cch)){
				$arrData['message'] = 'Se registró el movimiento y se actualizó el saldo de caja.';
				$arrData['flag'] = 1;
			}

			/* REGISTRAR ASIENTO CONTABLE */ 
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
			// AGREGAR EN DEBE MONTO SIN IMPUESTO  importe_local_con_igv
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
			// var_dump($debeHaber,$allInputs['inafecto']); // exit();
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
			// var_dump($arrAsientoContable); exit(); 
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
	private function actualizar_saldo_caja_chica($fCajaActual){
		$arrParams['idaperturacajachica'] = $fCajaActual['idaperturacajachica']; 
		$fConsolidado = $this->model_caja_chica->m_count_movimientos($arrParams,NULL); 
		$arrParams['saldo'] = $fCajaActual['monto_inicial_numeric'] - $fConsolidado['suma_total']; 
		if( $this->model_caja_chica->m_actualizar_saldo_caja($arrParams) ){ 
			return true;
		}else{
			return false;
		}
	}
	public function listar_movimientos(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$datos['idaperturacajachica'] = $allInputs['cajaChica']['idaperturacajachica'];
		$lista = $this->model_caja_chica->m_cargar_movimientos($datos, $paramPaginate);
		$fContador = $this->model_caja_chica->m_count_movimientos($datos, $paramPaginate);
		$arrListado = array(); 
		foreach ($lista as $row) { 
			$objEstado = array();
			$objEstadoColor = array(); 
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
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO () 
				$objEstado['claseIcon'] = 'fa fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['estado_color'] == 1 ){ // verde 
				$objEstadoColor['nombre_img'] = $objEstadoColor['nombre_img_cambio'] = 'verde.png';
				$objEstadoColor['label'] = $objEstadoColor['label_cambio'] = 'APROBADO';
			}elseif ( $row['estado_color'] == 2 ) { // amarillo 
				$objEstadoColor['nombre_img'] = $objEstadoColor['nombre_img_cambio'] = 'amarillo.png';
				$objEstadoColor['label'] = $objEstadoColor['label_cambio'] = 'OBSERVADO';
			}elseif ( $row['estado_color'] == 3 ) { // rojo 
				$objEstadoColor['nombre_img'] = $objEstadoColor['nombre_img_cambio'] = 'rojo.png';
				$objEstadoColor['label'] = $objEstadoColor['label_cambio'] = 'ANULADO';
			}
			array_push($arrListado, 
				array( 
					'idmovimiento' => $row['idmovimiento'],
					'idempresa' => $row['idempresa'], // proveedor
					'idaperturacajachica' => $row['idaperturacajachica'],
					'estado_acc' => $row['estado_acc'],
					'empresa' => strtoupper($row['empresa']),
					'servicio_asignado' => strtoupper($row['servicio_asignado']),
					'ruc' => $row['ruc_empresa'],
					'glosa' => $row['glosa'],
					'importe_local' => $row['importe_local_con_igv'],
					'importe_local_con_igv' => $row['importe_local_con_igv'],
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'numero_documento'=> $row['numero_documento'],
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
					'total_a_pagar' => $row['total_a_pagar'], // importe_local_con_igv
					'detraccion' => $row['detraccion'],
					'deposito' => $row['deposito'],
					'idempleado'=> $row['idempleado'],
					'empleado'=> $row['empleado'],
					'estado_movimiento' => $row['estado_movimiento'],
					'estado' => $objEstado,
					'estado_color_obj' => $objEstadoColor
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
	public function anular_movimiento(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
    	$this->db->trans_start(); 
    	$cch = $this->model_caja_chica->m_cargar_caja_chica_usuario();
    	if( $this->model_caja_chica->m_anular_movimiento($allInputs['idmovimiento']) ){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
    		if($this->actualizar_saldo_caja_chica($cch)){
				$arrData['message'] = 'Se anularon los datos y se actualizó el saldo de caja.';
				$arrData['flag'] = 1;
			}
			// AL ANULAR MOVIMIENTO, TAMBIEN CAMBIA EL SEMAFORO A ROJO. 
			$arrParams = array(
				'comentario' => NULL,
				'estado_color_obj'=> array(
					'flag'=> 3 
				),
				'idmovimiento'=> $allInputs['idmovimiento']
			);
			// AGREGAR NUEVO ESTADO Y/O COMENTARIO
			$this->model_historial_caja_chica->m_agregar_comentario_estado($arrParams);  

			// ACTUALIZAR EL SEMAFORO DE MOVIMIENTO 
			$this->model_historial_caja_chica->m_actualizar_semaforo_mov($arrParams); 
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function liquidar_caja_chica(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;

    	// VALIDACIONES
    	$allInputs['cajaChica']= array('idcajachica'=>$allInputs['idcajachica']);
		$cajaAbierta = $this->model_caja_chica->m_cargar_esta_caja_abierta($allInputs);
		/*si la caja no es del usuario entonces no lo podrá liquidar*/
		if($cajaAbierta['idusuarioresponsable'] != $this->sessionHospital['idempleado']){
			$arrData['message'] = 'No se puede liquidar la caja. Actualice la página CTRL+F5';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		if(empty($allInputs['saldo'])){
			$arrData['message'] = 'No se puede liquidar la caja. No tiene ningun movimiento';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

    	// var_dump($allInputs); exit();
    	$this->db->trans_start(); 
    	if( $this->model_caja_chica->m_liquidar_caja_chica($allInputs) ){ 
			$arrData['message'] = 'Se liquidó la caja correctamente';
    		$arrData['flag'] = 1;
    		
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cerrar_caja_chica(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo cerrar la caja';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit(); 
    	$this->db->trans_start(); 
    	if( $this->model_caja_chica->m_cerrar_caja_chica($allInputs) ){ 
    		// ACTUALIZAR CAJA MASTER CON SUS DATOS TEMPORALES A NULL 
    		if( $this->model_caja_chica->m_actualizar_caja_maestra($allInputs['idcajachica']) ){
				$arrData['message'] = 'Se cerró la caja correctamente';
	    		$arrData['flag'] = 1;  
    		} 
		} 
		$this->db->trans_complete();
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_detalle(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;	

    	if($allInputs['estado_movimiento'] != 1 ){
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    	}

		if($this->model_caja_chica->m_anular_movimiento($allInputs['idmovimiento'])){
			if($this->model_caja_chica->m_update_encabezado($allInputs['idaperturacajachica'], $allInputs['importe_local_con_igv'])){
				$arrData['message'] = 'Se anuló el movimiento correctamente';
    			$arrData['flag'] = 1;
			}
		}

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
    	// var_dump($allInputs['datos']); exit(); 
    	$arrListado = array();
    	if(!empty($allInputs['datos'])){
	    	foreach ($allInputs['datos'] as $key => $row) {
	    		$list = array( 
	    			$row['idmovimiento'],
	    		 	$row['numero_documento'],
	    		 	$row['empresa'],
	    		 	$row['ruc'],
	    		 	$row['fecha_emision'],
		            $row['fecha_registro'],
		            $row['glosa'],
		            // $row['fecha_pago'],
		            // $row['periodo_asignado'],
		            $row['cuenta_contable'],
		            str_replace('S/. ', '', $row['sub_total']) ,
		            str_replace('S/. ', '', $row['total_impuesto']),
		            str_replace('S/. ', '', $row['total_a_pagar']),
		            /*$row['detraccion'],
		            $row['deposito'],*/
		            $row['estado']['labelText'],
	    		);
	    		array_push($arrListado, $list);
	    	}
		}
    	$dataColumnsTP = array(
    					'ID MOV.',
    					'Nº FACTURA', 
    					'EMPRESA/PROVEEDOR', 
    					'RUC', 
    					'FECHA DE EMISION', 
    					'FECHA DE REGISTRO', 
    					'GLOSA', 
    					// 'FECHA DE APROBACION', 
    					// 'FECHA DE PAGO', 
    					// 'PERIODO', 
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
	    
	    $string1 = 'Fecha Apertura: ' . date('d-m-Y H:i:s', strtotime($allInputs['filtros']['fecha_apertura']));
	    $string2 = 'Fecha Liquidación: ' . date('d-m-Y H:i:s', strtotime($allInputs['filtros']['fecha_liquidacion']));
	    $string3 = 'Monto Inicial: ' . $allInputs['filtros']['monto_inicial'];
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
    		$this->excel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('J'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    		$this->excel->getActiveSheet()->getStyle('K'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    	}

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/MovimientosCajaChica_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/MovimientosCajaChica_'.$dateTime.'.xls',
	      'flag'=> 1
	    );    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
}
