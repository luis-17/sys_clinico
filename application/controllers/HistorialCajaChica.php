<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialCajaChica extends CI_Controller {

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_historial_caja_chica', 'model_empleado','model_config','model_caja_chica'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function ver_popup_detalle_movimiento()
	{
		$this->load->view('caja-chica/detalleMovimiento_formView');
	}
	public function ver_popup_abir_conversacion()
	{
		$this->load->view('caja-chica/conversacion_formView');
	}
	public function lista_historial_caja_chica(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		
		$lista = $this->model_historial_caja_chica->m_cargar_caja_chica_historial($paramPaginate);
		$fContador = $this->model_historial_caja_chica->m_count_caja_chica_historial($paramPaginate);
		$arrListado = array(); 
		setlocale(LC_MONETARY,"es_PE");

		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_acc'] == 3 ){ // CERRADA (gris)
				$objEstado['claseIcon'] = 'ti ti-write';
				$objEstado['claseLabel'] = 'label-inverse';
				$objEstado['labelText'] = 'CERRADA';
			}elseif( $row['estado_acc'] == 2 ){ // LIQUIDADA (amarillo) 
				$objEstado['claseIcon'] = 'ti ti-eye';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'LIQUIDADA';
			}elseif( $row['estado_acc'] == 1 ){ // ABIERTA (verde)
				$objEstado['claseIcon'] = 'ti ti-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'ABIERTA';
			}else if(empty($row['idaperturacajachica'])){
				$objEstado['claseIcon'] = 'ti ti-eye';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NO APERTURADA';
			}		

			array_push($arrListado, 
				array( 
					'idaperturacajachica' => $row['idaperturacajachica'],
					'idcajachica' => $row['idcajachica'],					
					'nombre_caja' => strtoupper($row['nombre_caja']),
					'responsable' => strtoupper($row['responsable']), 					
					'fecha_apertura' => formatoFechaReporte3($row['fecha_apertura']),
					'fecha_liquidacion' => formatoFechaReporte4($row['fecha_liquidacion']),
					'responsable_cierre' => strtoupper($row['responsable_cierre']),
					'fecha_cierre' => formatoFechaReporte4($row['fecha_cierre']), 
					'nombre_cc' => $row['nombre_cc'],
					'codigo_cc' => $row['codigo_cc'],
					'numero_cheque' => $row['numero_cheque'],
					'monto_inicial' => $row['monto_inicial'],
					'monto_inicial_numeric' => (float)$row['monto_inicial_numeric'],
					'importe_total' => 'S/. '. number_format($row['monto_inicial_numeric'] - $row['saldo_numeric'],2,'.',','),					
					'importe_total_numeric' => $row['monto_inicial_numeric'] - $row['saldo_numeric'],					
					'estado' => $objEstado,
					'estado_acc' =>  $row['estado_acc'],
					'saldo' =>  $row['saldo'],
					'saldo_numeric' =>  $row['saldo_numeric']
				)
			);
		} 

    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $fContador[0]['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_movimientos_una_caja(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_caja_chica->m_cargar_movimientos_una_caja($datos, $paramPaginate);
		$fContador = $this->model_caja_chica->m_count_movimientos_una_caja($datos, $paramPaginate);
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
			}elseif( $row['estado_movimiento'] == 0 ){ // PAGADO (azul)
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
					'idaperturacajachica' => $row['idaperturacajachica'],
					'idempresa' => $row['idempresa'], // proveedor v numero_documento 
					'empresa' => strtoupper($row['empresa']),
					'servicio_asignado' => strtoupper($row['servicio_asignado']),
					'ruc' => $row['ruc_empresa'],
					'idempleado'=> $row['idempleado'],
					'empleado'=> $row['empleado'],
					'glosa' => $row['glosa'],
					'importe_local' => $row['importe_local'],
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
					'fecha_emision' => formatoFechaReporte3($row['fecha_emision']),
					'fecha_registro' => formatoFechaReporte($row['fecha_registro']),
					'fecha_aprobacion' => formatoFechaReporte4($row['fecha_aprobacion']),
					'fecha_pago' => formatoFechaReporte4($row['fecha_pago']),
					'fecha_credito'=> formatoFechaReporte4($row['fecha_credito']),
					'forma_pago'=> $row['forma_pago'],
					'periodo_asignado'=> $row['periodo_asignado'],
					'modo_igv' => $row['modo_igv'],
					'sub_total' => $row['sub_total'],
					'total_impuesto' => $row['total_impuesto'],
					'total_a_pagar' => $row['total_a_pagar'],
					'detraccion' => $row['detraccion'],
					'deposito' => $row['deposito'],
					'estado_movimiento' => $row['estado_movimiento'],
					'estado' => $objEstado,
					'estado_color_obj' => $objEstadoColor,
					'importe_local_con_igv' => $row['importe_local_con_igv'],
					'saldo_caja' => $row['saldo_caja'],
				)
			);
		} 
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($fContador['suma_total']) ? 0 : $fContador['suma_total'];
    	$arrData['paginate']['totalRows'] = $fContador['contador'];
    	$arrData['saldo_caja'] = (float)$allInputs['datos']['saldo'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_esta_apertura_caja_chica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fData = $this->model_historial_caja_chica->m_cargar_esta_apertura_caja_chica_historial($allInputs);
		$arrListado = array(); 
		setlocale(LC_MONETARY,"es_PE");


		$objEstado = array();
		if( $fData['estado_acc'] == 3 ){ // CERRADA (gris)
			$objEstado['claseIcon'] = 'ti ti-write';
			$objEstado['claseLabel'] = 'label-inverse';
			$objEstado['labelText'] = 'CERRADA';
		}elseif( $fData['estado_acc'] == 2 ){ // LIQUIDADA (amarillo) 
			$objEstado['claseIcon'] = 'ti ti-eye';
			$objEstado['claseLabel'] = 'label-warning';
			$objEstado['labelText'] = 'LIQUIDADA';
		}elseif( $fData['estado_acc'] == 1 ){ // ABIERTA (verde)
			$objEstado['claseIcon'] = 'ti ti-check';
			$objEstado['claseLabel'] = 'label-success';
			$objEstado['labelText'] = 'ABIERTA';
		}		

		array_push($arrListado, 
			array( 
				'idaperturacajachica' => $fData['idaperturacajachica'],
				'idcajachica' => $fData['idcajachica'],					
				'nombre_caja' => strtoupper($fData['nombre_caja']),
				'responsable' => strtoupper($fData['responsable']), 					
				'fecha_apertura' => formatoFechaReporte3($fData['fecha_apertura']),
				'fecha_liquidacion' => formatoFechaReporte4($fData['fecha_liquidacion']),
				'responsable_cierre' => strtoupper($fData['responsable_cierre']),
				'fecha_cierre' => formatoFechaReporte4($fData['fecha_cierre']), 
				'nombre_cc' => $fData['nombre_cc'],
				'codigo_cc' => $fData['codigo_cc'],
				'numero_cheque' => $fData['numero_cheque'],
				'monto_inicial' => $fData['monto_inicial'],
				'monto_inicial_numeric' => (float)$fData['monto_inicial_numeric'],
				'importe_total' => 'S/. '. number_format($fData['monto_inicial_numeric'] - $fData['saldo_numeric'],2,'.',','),					
				'importe_total_numeric' => $fData['monto_inicial_numeric'] - $fData['saldo_numeric'],					
				'estado' => $objEstado,
				'estado_acc' =>  $fData['estado_acc'],
				'saldo' =>  $fData['saldo'],
				'saldo_numeric' =>  $fData['saldo_numeric']
			)
		);

    	$arrData['fData'] = $arrListado[0];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	// COMENTARIOS
	public function listar_comentarios_de_movimiento()
	{
	 	$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
	 	$lista = $this->model_historial_caja_chica->m_cargar_comentarios_estados_movimiento($allInputs); 
	 	$arrListado = array(); 
	 	foreach ($lista as $key => $row) { 
	 		if( $row['color_estado'] == 1 ){ // verde 
				$objEstadoColor['nombre_img'] = 'verde.png';
				$objEstadoColor['label'] = 'APROBADO';
				$objEstadoColor['flag'] = 1;
			}elseif ( $row['color_estado'] == 2 ) { // amarillo 
				$objEstadoColor['nombre_img'] = 'amarillo.png';
				$objEstadoColor['label'] = 'OBSERVADO';
				$objEstadoColor['flag'] = 2;
			}elseif ( $row['color_estado'] == 3 ) { // rojo 
				$objEstadoColor['nombre_img'] = 'rojo.png';
				$objEstadoColor['label'] = 'ANULADO';
				$objEstadoColor['flag'] = 3;
			}else{
				$objEstadoColor = NULL; 
			}
	 		array_push($arrListado, 
	 			array(
	 				'idmovimiento' => $row['idmovimiento'],
	 				'fecha_registro' => formatoFechaReporte($row['fecha_registro']),
	 				'idcomentario' => $row['idcomentario'],
	 				'comentario' => $row['comentario'],
	 				'color_estado' => $row['color_estado'],
	 				'responsable' => $row['responsable'],
	 				'idusuario' => $row['idusers'],
	 				'estado_color_obj'=> $objEstadoColor
	 			)
	 		); 
	 	}
	 	$arrData['datos']['comentarios'] = $arrListado;
	 	$arrData['datos']['comentarios'] = $arrListado;
	 	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
	 	if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
	 	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 
	private function actualizar_saldo_esta_caja_chica($fCaja,$operacion){
		$arrParams['idaperturacajachica'] = $fCaja['idaperturacajachica']; 
		$fConsolidado = $this->model_caja_chica->m_count_movimientos($arrParams,NULL); 
		if( $operacion === 'suma' ){
			$arrParams['saldo'] = $fCaja['monto_inicial_numeric'] + $fConsolidado['suma_total']; 
		}elseif ( $operacion === 'resta' ) { 
			$arrParams['saldo'] = $fCaja['monto_inicial_numeric'] - $fConsolidado['suma_total']; 
		}
		
		if( $this->model_caja_chica->m_actualizar_saldo_caja($arrParams) ){ 
			return true;
		}else{
			return false;
		}
	}
	public function agregar_comentario_estado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit(); 
		$arrConfig = $this->model_config->m_cargar_empresa_activa();
		$arrConfigEmp = $this->model_config->m_cargar_empresa_sede_activa();

		if( empty($allInputs['comentario']) && empty($allInputs['estado_color_obj']['flag']) ){
			$arrData['message'] = 'Registre al menos un valor o cambio de estado.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		if( $allInputs['estado_color_obj']['label'] == $allInputs['estado_color_obj']['label_cambio'] ){
			$allInputs['estado_color_obj']['flag'] = NULL;
		}
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start(); 
		if($this->model_historial_caja_chica->m_agregar_comentario_estado($allInputs)){ 
			$operacion = NULL; 
			if( !empty($allInputs['estado_color_obj']['flag']) ){ 
				if($allInputs['estado_color_obj']['flag'] === 3){ // ROJO 
					$this->model_caja_chica->m_anular_movimiento($allInputs['idmovimiento']); 
					$operacion = 'resta';
				}
				if($allInputs['estado_color_obj']['flag'] === 1){ // VERDE 
					$this->model_caja_chica->m_revertir_anular_movimiento($allInputs['idmovimiento']);
					$operacion = 'suma';
				}
				// ACTUALIZAR EL SEMAFORO DE MOVIMIENTO 
				$this->model_historial_caja_chica->m_actualizar_semaforo_mov($allInputs); 

				// ACTUALIZAR SALDOS 
				$cch = $this->model_caja_chica->m_cargar_esta_apertura_caja_chica($allInputs['idaperturacajachica']);
				$this->actualizar_saldo_esta_caja_chica($cch,$operacion);
			}
			// MANDAR CORREO A LOS INVOLUCRADOS 
			$this->load->library('My_PHPMailer'); 
			$hoydate = date("Y-m-d H:i:s");
			date_default_timezone_set('UTC');
			define('SMTP_HOST','mail.villasalud.pe');

			define('SMTP_PORT',25);
			define('SMTP_USERNAME','notificaciones@villasalud.pe');
			define('SMTP_PASSWORD','franzsheskoli');
			$setFromAleas = $this->sessionHospital['nombres'].' '.$this->sessionHospital['apellido_paterno'].' '.$this->sessionHospital['apellido_materno'];
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
			$mail->Subject = 'TIENES UN NUEVO COMENTARIO EN TU MÓDULO DE CAJA CHICA';

			$cuerpo = '<html> 
				<style>

				</style>
				<head>
				  <title>NUEVO COMENTARIO EN TU MÓDULO DE CAJA CHICA</title> 
				</head>
				<body style="font-family: sans-serif;padding: 10px 40px;" > 
				<div style="text-align: right;">
					<img style="width: 160px;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/'.$arrConfig['nombre_logo']).'">
				</div> <br />';
			$cuerpo .= '<h2> CONTROL DE MOVIMIENTOS DE CAJA CHICA </h2> <br />'; 
			$cuerpo .= '<div style="font-size:16px;">  
					Estimado(a): <br /> <br />'; 
			if( !empty($allInputs['estado_color_obj']['flag']) ){
				$cuerpo .= 'Se cambió el estado del movimiento por concepto de <u>"'. strtoupper($allInputs['fila']['glosa']) . '"</u> a <b>'.$allInputs['estado_color_obj']['label_cambio'].'. </b> <br />'; 
			}
			
			if( !empty($allInputs['fila']['glosa']) ){
				$cuerpo .= 'Se ha agregado un nuevo comentario al movimiento por concepto de <u>'.strtoupper($allInputs['fila']['glosa']).'.</u> <br /> <br />'; 
				$cuerpo .= '<div style="background-color: #e8e8e8;font-style: italic;margin-top: 20px;padding: 16px;"> "'.@$allInputs['comentario'].'" </div>'; 
			} 
			$cuerpo .= '<br /> <br />  
				<span style="font-size: 12px; color: #9c9c9c;float:right; ">ATTE: <br /> 
							ENVÍO AUTOMÁTICO DE CORREO GENERADO POR EL AREA DE TECNOLOGÍAS DE LA INFORMACIÓN.  </span>
			</div>';
			// $cuerpo .= '<div style="width: 100%; display: block; font-size: 14px; text-align: right; line-height: 5; color: #a9b9c1;"> Atte: Área de Sistemas y Desarrollo </div>';
			$cuerpo .= '</body></html>';
			$mail->AltBody = $cuerpo;
			$mail->MsgHTML($cuerpo);
			// foreach ($arrMails as $key => $val) { 
			if( $this->sessionHospital['idempleado'] == $allInputs['fila']['idempleado'] ){ // ES LA PERSONA QUE REGISTRÓ EL MOVIMIENTO(ADMINISTRADOR)
				$mail->AddAddress($arrConfigEmp['correo_gerencia_finanzas'], ' GERENCIA DE FINANZAS'); 
			}else{	
				$mail->AddAddress( $arrConfigEmp['correo_administrador'], ' ADMINISTRACIÓN '.strtoupper($this->sessionHospital['sede']) ); 
			}
			// Activo condificación utf-8
			$mail->CharSet = 'UTF-8';
			// echo $cuerpo; 
			if( $mail->Send() ){ 
				$arrData['message'] = ' <br /> Se envió el correo a los destinatarios correctamente.'; 
		    	$arrData['flag'] = 1;
			}else{
				$arrData['message'] .= ' <br /> Surgió un inconveniente al enviar el correo al destinatario. Verifique que el correo sea válido.';
			}
			$arrData['message'] .= 'Se agregaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete(); 
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
