<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuracion extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','config_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_config','model_empleado','model_medicamento_almacen', 'model_control_evento'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function getEmpresaActiva()
	{
		$arrConfig = $this->model_config->m_cargar_empresa_activa();
		$arrData['flag'] = 0;
    	$arrData['message'] = 'No hay empresa activa';

		if( $arrConfig ){
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Se cargó la empresa activa';
    		$arrData['datos'] = $arrConfig;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_notificaciones()
	{
		//$diamasdies = date('Y-m-d',strtotime("+10days"));
		//var_dump($diamasdies); exit();
		$arrListado = array();
		$arrData['flag'] = 0;
		// $arrData['count'] = 0;
	    $arrData['message'] = 'No hay notificaciones.';
		if( $this->sessionHospital['key_group'] == 'key_sistemas' || 
			$this->sessionHospital['key_group'] == 'key_rrhh' || 
			$this->sessionHospital['key_group'] == 'key_gerencia' || 
			$this->sessionHospital['key_group'] == 'key_rrhh_asistente' )
		{
			/* NOTIFICACIONES DE VENCIMIENTO DE COLEGIATURA */
			$listaCV = $this->model_empleado->m_cargar_empleados_colegiatura_vencimiento();
			if( empty($listaCV) ){
				//$arrData['message'] = 'No hay notificaciones.';
			}else{ 
				foreach ($listaCV as $key => $row) { 
					array_push($arrListado,
						array(
							'idempleado'=> $row['idempleado'],
							'notificacion'=> '<b>COLEGIATURA</b> del colaborador: <b>'.strtoupper($row['empleado']).'</b> ya venció, o está próxima a vencer.',
							'fecha'=> formatoFechaReporte3($row['fecha_caducidad_coleg']),
							'fecha_timestamp'=> strtotime($row['fecha_caducidad_coleg']),
							'clase'=> 'danger', // VC 
							'tipo_notif'=> 'VC',   
							'icono'=> 'fa fa-stethoscope',
							'colegiatura'=> $row['colegiatura_profesional']
						)
					);
				}
			}
			/* NOTIFICACIONES DE VENCIMIENTO DE CONTRATO */ 
			$listaVFC = $this->model_empleado->m_cargar_empleados_contrato_vencimiento();
			if( empty($listaVFC) ){
				//$arrData['message'] = 'No hay notificaciones.';
			}else{
				foreach ($listaVFC as $key => $row) {
					array_push($arrListado,
						array(
							'idempleado'=> $row['idempleado'],
							'notificacion'=> '<b>CONTRATO</b> del colaborador: <b>'.strtoupper($row['empleado']).'</b> ya venció, o está próximo a vencer.',
							'fecha'=> formatoFechaReporte3($row['fecha_fin_contrato']),
							'fecha_timestamp'=> strtotime($row['fecha_fin_contrato']),
							'clase'=> 'indigo', // VFC 
							'tipo_notif'=> 'VFC', 
							'icono'=> 'ti ti-pencil-alt'
						)
					);
				}
			}
		}	
		if( $this->sessionHospital['key_group'] == 'key_dir_far' || $this->sessionHospital['key_group'] == 'key_sistemas' ){
			/* NOTIFICACIONES DE VENCIMIENTO DE MEDICAMENTO */
			$listaVM = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_vencer();
			if( !empty($listaVM) ){
				foreach ($listaVM as $key => $row) {
					if( $row['estado_vencer'] == 1 ){
						$string = 'Vencido';
						$claseMed = 'danger';
					}elseif( $row['estado_vencer'] == 2 ){
						$string = 'Prox. a Vencer';
						$claseMed = 'warning';
					}elseif( $row['estado_vencer'] == 3 ){
						$string = 'Prox. a Vencer';
						$claseMed = 'info';
					}
					array_push($arrListado,
						array(
							'idmedicamento'=> $row['idmedicamento'],
							'notificacion'=> '<b>MEDICAMENTO</b>: <b>'.strtoupper($row['denominacion']).'</b>',
							'fecha'=> formatoFechaReporte3($row['fecha_vencimiento']),
							'fecha_timestamp'=> strtotime($row['fecha_vencimiento']),
							'string' => $string,
							'clase'=> $claseMed, // 
							'tipo_notif'=> 'VM', 
							'icono'=> 'fa fa-medkit'
						)
					);
				}
			}
			/* NOTIFICACIONES DE STOCKS */
			$listaSF = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_agotarse();
			if( !empty($listaSF) ){
				foreach ($listaSF as $row) {
					switch ($row['estado']) {
						case 1:
							$string = 'STOCK CRITICO';
							$claseMed = 'info';
							break;
						case 2:
							$string = 'STOCK MINIMO';
							$claseMed = 'warning';
							break;
						case 3:
							$string = 'STOCK AGOTADO';
							$claseMed = 'danger';
							break;
						default:
							break;
					}
					array_push($arrListado,
						array(

							'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
							'idmedicamento' => $row['idmedicamento'],
							'notificacion' => '<b>' . $string . '</b><br>' . $row['denominacion'],
							'stock_minimo' => $row['stock_minimo'],
							'stock_critico' => $row['stock_critico'],
							'stock_maximo' => $row['stock_maximo'],
							'stock_actual' => $row['stock_actual_malm'],
							'string' => $string,
							'clase' => $claseMed,
							'tipo_notif'=> 'SF', 
							'icono'=> 'fa fa-exclamation-triangle',
							'fecha_timestamp'=> date('d-m-Y'),
						)
					);
				}
			}
		}
		/*function fnOrdering($a, $b) { 
	    	return $b['fecha_timestamp'] - $a['fecha_timestamp'];
	    }*/
	    usort($arrListado,'fnOrdering');
		

		if( !empty($listaCV) || !empty($listaVFC) ){ 
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Se cargaron las notificaciones';
		}else{
			$arrData['message'] = 'No hay notificaciones.';
		}
		$arrData['datos'] = $arrListado;
    	$arrData['contador'] = count($arrListado);
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_notificaciones_eventos(){
		$arrListado = array();
		$arrListadoNoLeido = array();
		$arrData['flag'] = 0;
		// $arrData['count'] = 0;
	    $arrData['message'] = 'No hay notificaciones.';
	    $lista = $this->model_control_evento->m_cargar_notificaciones_usuario($this->sessionHospital['idusers']);
	    $contador = $this->model_control_evento->m_count_notificaciones_sin_leer_usuario($this->sessionHospital['idusers']); 
		foreach ($lista as $row) {
			$clase =  '';
			$icono =  '';
			$string =  '';
			$color_background = '';
			if($row['idtipoevento'] == 1){
				$clase = 'success';
				$icono =  'fa fa-check';
				$string =  '';
			}else if($row['idtipoevento'] == 2){
				$clase = 'default';
				$icono =  'fa fa-minus';
				$string =  '';
			}else if($row['idtipoevento'] == 3){
				$clase = 'danger';
				$icono =  'fa fa-times';
				$string =  '';
			}else if($row['idtipoevento'] == 4 || $row['idtipoevento'] == 5|| $row['idtipoevento'] == 9 || $row['idtipoevento'] == 11){
				$clase = 'warning';
				$icono =  'fa fa-pencil';
				$string =  '';
			}else if($row['idtipoevento'] == 10){
				$clase = 'info';
				$icono =  'fa fa-comments-o';
				$string =  '';
			}
			
			if($row['estado_ceu'] == 2){
				$color_background = '#fafafa';
			}else{
				$color_background = 'rgba(0, 188, 212, 0.25)';
			}			

			$array = array(
				'idcontroleventousuario' => (int)$row['idcontroleventousuario'],
				'idusers' => (int)$row['idusers'],
				'fecha_notificado' => $row['fecha_notificado'],				
				'fecha_notificado_str' => date('d-m-Y',strtotime($row['fecha_notificado'])),
				'fecha_leido' => $row['fecha_leido'],				
				'fecha_leido_str' => empty($row['fecha_leido'])? NULL : date('d-m-Y',strtotime($row['fecha_leido'])),
				'estado_ceu' => (int)$row['estado_ceu'],
				'idcontrolevento' => (int)$row['idcontrolevento'],
				'fecha_evento' => $row['fecha_evento'],
				'fecha' => empty($row['fecha_evento'])? NULL : date('d-m-Y',strtotime($row['fecha_evento'])),
				'idtipoevento' => (int)$row['idtipoevento'],
				'identificador' => $row['identificador'],
				'texto_notificacion' => $row['texto_notificacion'],
				'descripcion_te' => $row['descripcion_te'],
				'key_evento' => $row['key_evento'],
				//'notificacion' => substr($row['texto_notificacion'], 0,60),
				'notificacion' =>$row['texto_notificacion'],
				'idresponsable' => $row['idresponsable'],
				'responsable' => $row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'],
				'clase' => $clase,
				'icono' => $icono,
				'color_background' => $color_background,				
			);
			array_push($arrListado, $array);
			if($row['estado_ceu'] == 1)
				array_push($arrListadoNoLeido, $array);
		}

	    if( !empty($lista) ){ 
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Se cargaron las notificaciones';
		}else{
			$arrData['message'] = 'No hay notificaciones.';
		}
		$arrData['datos'] = $arrListado;
		$arrData['noLeidas'] = $arrListadoNoLeido;
    	$arrData['contador'] = $contador;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_combo()
	{
		$this->load->view('plantillas/popup_combo_data');
	}
	public function ver_popup_combo_grilla()
	{
		$this->load->view('plantillas/popup_combo_grilla_data');
	}
	public function getParametrosConfig()
	{
		$arrConfig = obtener_parametros_configuracion();
		$arrData['flag'] = 0;
    	$arrData['message'] = 'No hay parametros activos';

		if( $arrConfig ){
			$arrData['flag'] = 1;
    		$arrData['message'] = '';
    		$arrData['datos'] = $arrConfig;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function getParametrosPusher (){		
		if($this->load->config('pusher')){
			$arrConfig = array(
				'app_id' => $this->config->item('pusher_app_id'),
				'app_key' => $this->config->item('pusher_app_key'),
				'app_secret' => $this->config->item('pusher_app_secret'),
			);
		}		

		if( $arrConfig ){
			$arrData['flag'] = 1;
    		$arrData['message'] = '';
    		$arrData['datos'] = $arrConfig;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_notificaciones_colegiatura(){
		$this->load->view('notificaciones/popup_notificaciones_colegiatura');
	}
	public function ver_popup_notificaciones_contrato(){
		$this->load->view('notificaciones/popup_notificaciones_contrato');
	}
	public function lista_notificaciones_colegiatura(){
		//$diamasdies = date('Y-m-d',strtotime("+10days"));
		//var_dump($diamasdies); exit();
		$arrListado = array();
		$arrData['flag'] = 0;
		// $arrData['count'] = 0;
	    $arrData['message'] = 'No hay notificaciones.';
		if( $this->sessionHospital['key_group'] == 'key_sistemas' || 
			$this->sessionHospital['key_group'] == 'key_rrhh' || 
			$this->sessionHospital['key_group'] == 'key_gerencia' || 
			$this->sessionHospital['key_group'] == 'key_rrhh_asistente' )
		{
			/* NOTIFICACIONES DE VENCIMIENTO DE COLEGIATURA */
			$listaCV = $this->model_empleado->m_cargar_empleados_colegiatura_vencimiento();
			if( !empty($listaCV) ){
				foreach ($listaCV as $key => $row) { 
					array_push($arrListado,
						array(
							'idempleado'=> $row['idempleado'],
							'notificacion'=> '<b>COLEGIATURA</b> del colaborador: <b>'.strtoupper($row['empleado']).'</b> ya venció, o está próxima a vencer.',
							'fecha'=> formatoFechaReporte3($row['fecha_caducidad_coleg']),
							'fecha_timestamp'=> strtotime($row['fecha_caducidad_coleg']),
							'clase'=> 'danger', // VC 
							'tipo_notif'=> 'VC',   
							'icono'=> 'fa fa-stethoscope',
							'empleado'=> strtoupper($row['empleado']),
							'fecha_caducidad'=> darFormatoDMY($row['fecha_caducidad_coleg']),
							'colegiatura' => $row['colegiatura_profesional']
						)
					);
				}
			}

		}	

	    usort($arrListado,'fnOrdering');
		
		if( !empty($listaCV) ){ 
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Se cargaron las notificaciones';
		}else{
			$arrData['message'] = 'No hay notificaciones.';
		}
		$arrData['datos'] = $arrListado;
    	$arrData['contador'] = count($arrListado);
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_notificaciones_contrato(){
		//$diamasdies = date('Y-m-d',strtotime("+10days"));
		//var_dump($diamasdies); exit();
		$arrListado = array();
		$arrData['flag'] = 0;
		// $arrData['count'] = 0;
	    $arrData['message'] = 'No hay notificaciones.';
		if( $this->sessionHospital['key_group'] == 'key_sistemas' || 
			$this->sessionHospital['key_group'] == 'key_rrhh' || 
			$this->sessionHospital['key_group'] == 'key_gerencia' || 
			$this->sessionHospital['key_group'] == 'key_rrhh_asistente' )
		{
			/* NOTIFICACIONES DE VENCIMIENTO DE CONTRATO */ 
			$listaVFC = $this->model_empleado->m_cargar_empleados_contrato_vencimiento();
			if( !empty($listaVFC) ){
				foreach ($listaVFC as $key => $row) {
					array_push($arrListado,
						array(
							'idempleado'=> $row['idempleado'],
							'notificacion'=> '<b>CONTRATO</b> del colaborador: <b>'.strtoupper($row['empleado']).'</b> ya venció, o está próximo a vencer.',
							'fecha'=> formatoFechaReporte3($row['fecha_fin_contrato']),
							'fecha_timestamp'=> strtotime($row['fecha_fin_contrato']),
							'clase'=> 'indigo', // VFC 
							'tipo_notif'=> 'VFC', 
							'icono'=> 'ti ti-pencil-alt',
							'empleado'=> strtoupper($row['empleado']),
							'fin_contrato'=> darFormatoDMY($row['fecha_fin_contrato']),
							'idempresaadmin'=> $row['idempresaadmin']
						)
					);
				}
			}
		}	

	    usort($arrListado,'fnOrdering');
		
		if( !empty($listaVFC) ){ 
			$arrData['flag'] = 1;
    		$arrData['message'] = 'Se cargaron las notificaciones';
		}else{
			$arrData['message'] = 'No hay notificaciones.';
		}
		$arrData['datos'] = $arrListado;
    	$arrData['contador'] = count($arrListado);
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
