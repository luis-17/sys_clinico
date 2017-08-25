<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Acceso extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','contable'));
		$this->load->model(array('model_acceso','model_empleado','model_medicamento_almacen','model_empresa_admin','model_usuario','model_centro_costo','model_config'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function index()
	{
		//$this->load->library('encrypt'); 
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		if($allInputs){ 
			$loggedUser = $this->model_acceso->m_logging_user($allInputs);
			//var_dump($loggedUser); exit();
			if( $loggedUser['key_grupo'] == 'key_salud' || 
				$loggedUser['key_grupo'] == 'key_dir_esp' || 
				$loggedUser['key_grupo'] =='key_lab' || 
				$loggedUser['key_grupo'] =='key_coord_salud' || 
				$loggedUser['key_grupo'] =='key_salud_caja' || 
				$loggedUser['key_grupo'] =='key_salud_ocup' || 
				$loggedUser['key_grupo'] =='key_dir_salud' 
			){  /* SI ES SALUD */ 
				if( isset($loggedUser['logged']) && $loggedUser['logged'] > 0 ) { 
	    			// Validar la sede 
					$arrData['flag'] = 1; 
					/* PROBLEMAS OCURRIDOS */ 
					/* C1.- NO SE REGISTRÓ LA ESPECIALIDAD DEL MEDICO, POR ESE MOTIVO NO GUARDA EN PERFIL DEL USUARIO */ 
					$arrPerfilUsuario = $this->model_acceso->m_listar_perfil_usuario_salud($loggedUser['id']);
					// var_dump($loggedUser,$arrPerfilUsuario); exit();
					$arrPerfilUsuario['username'] = ucwords($arrPerfilUsuario['username']); 
					$arrPerfilUsuario['colegiatura'] = ucwords(@$arrPerfilUsuario['colegiatura_profesional']);
					$arrPerfilUsuario['profesional'] = strtoupper(@$arrPerfilUsuario['med_nombres'].' '.@$arrPerfilUsuario['med_apellido_paterno'].' '.@$arrPerfilUsuario['med_apellido_materno']); 
					if( isset($arrPerfilUsuario['idusers']) ){ 
						// GUARDAMOS EN EL LOG DE LOGEO LA SESION INICIADA. 
						$this->model_acceso->m_registrar_log_sesion($arrPerfilUsuario);
						// ACTUALIZAMOS EL ULTIMO LOGEO DEL USUARIO. 
						$this->model_acceso->m_actualizar_fecha_ultima_sesion($arrPerfilUsuario);
						$arrData['message'] = 'Usuario inició sesión correctamente';
						$this->session->set_userdata('sess_vs_'.substr(base_url(),-8,7),$arrPerfilUsuario);
					}else{
						$arrData['flag'] = 0;
	    				$arrData['message'] = 'No se encontró los datos del usuario.';
					}
	    		}else{ 
	    			$arrData['flag'] = 0;
	    			$arrData['message'] = 'Usuario o contraseña invalida. Inténtelo nuevamente.';
	    		} 
			}else{ /* SI NO ES SALUD */ 
				if( isset($loggedUser['logged']) && $loggedUser['logged'] > 0 ){ // var_dump('2'); exit();
					$arrData['flag'] = 1;
					$arrPerfilUsuario = $this->model_acceso->m_listar_perfil_usuario($loggedUser['id']); // var_dump($arrPerfilUsuario); exit(); 
					$arrPerfilUsuario['username'] = ucwords($arrPerfilUsuario['username']);
					// GUARDAMOS EN EL LOG DE LOGEO LA SESION INICIADA. 
					$this->model_acceso->m_registrar_log_sesion($arrPerfilUsuario);
					// ACTUALIZAMOS EL ULTIMO LOGEO DEL USUARIO. 
					$this->model_acceso->m_actualizar_fecha_ultima_sesion($arrPerfilUsuario);

					// TIPO DE CAMBIO
					$arrTipoCambio = ObtenerTipoCambio();
					if( !empty($arrTipoCambio) ){
						$arrTipoCambio['compra'] = number_format($arrTipoCambio['compra'],2);
						$arrTipoCambio['venta'] = number_format($arrTipoCambio['venta'],2);
						$arrPerfilUsuario['tc_compra'] =$arrTipoCambio['compra']; 
						$arrPerfilUsuario['tc_venta'] = $arrTipoCambio['venta']; 
					}
					$arrData['message'] = 'Usuario inició sesión correctamente';
					if( isset($arrPerfilUsuario['idusers']) ){ 
						$this->session->set_userdata('sess_vs_'.substr(base_url(),-8,7),$arrPerfilUsuario);
					}else{
						$arrData['flag'] = 0;
	    				$arrData['message'] = 'No se encontró los datos del usuario.';
					}
				}else{ 
	    			$arrData['flag'] = 0;
	    			$arrData['message'] = 'Usuario o contraseña invalida. Inténtelo nuevamente.';
	    		}
			}
			// Validar usuario y clave 
		}else{
			$arrData['flag'] = 0;
    		$arrData['message'] = 'No se encontraron datos.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// $arrData['datos']['id'] = $allInputs['idmedico'];
		// var_dump($this->sessionHospital); exit(); 
		$arrDataMedico = array( 
			'idmedico'=> $this->sessionHospital['idmedico'],
			'id_empresa_admin'=> $this->sessionHospital['id_empresa_admin']
		);
		$lista = $this->model_empleado->m_cargar_especialidades_del_medico_combo_master($arrDataMedico);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idempresamedico'],
					'idempresaespecialidad' => $row['idempresaespecialidad'],
					'id_empresa_admin' => $row['id_empresa_admin'],
					'descripcion' => $row['especialidad'] . ' / ' . $row['empresa'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa']
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
	public function lista_sede_empresa_admin_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		$lista = $this->model_empresa_admin->m_cargar_sede_empresa_admin_matriz_session();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => @$row['idsedeempresaadmin'],
					'descripcion' => @$row['empresa_admin'] . ' / ' . $row['sede'],
					'idsedeempresaadmin' => @$row['idsedeempresaadmin'],
					'idsede' => $row['idsede'],
					'descripcion_sede' => $row['sede'],
					'idempresaadmin' => @$row['idempresaadmin'],
					'descripcion_empresa_admin' => @$row['empresa_admin']
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
	public function lista_empresa_admin_matriz_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		$lista = $this->model_acceso->m_cargar_empresa_admin_matriz_session();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => @$row['id_empresa_admin'],
					'id_empresa_admin' => @$row['id_empresa_admin'],
					'descripcion' => @$row['aleas_empresa'],
					'empresa'=> $row['empresa_admin'],
					'ruc_empresa' => @$row['ruc_empresa_admin']
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
	public function recargar_usuario_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['datos'] = array();
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//var_dump($this->sessionHospital); // exit();
		if( $this->sessionHospital['idempresamedico'] ){ 
			// if( $this->sessionHospital['vista_sede_empresa'] == 1 ){ 
			// 	$allInputs['idsedeempresaadmin'] = NULL;
			// }
			$arrPerfilUsuario = $this->model_acceso->m_listar_perfil_usuario_salud($this->sessionHospital['idusers'],$allInputs['idempresamedico']/*,$allInputs['idsedeempresaadmin'],$allInputs['idsede']*/); 
			//VALIDAR QUE EMPRESA Y EMPRESA_ADMIN SEAN IGUALES SI EMPRESA ES EMPRESA_ADMIN 
			
			// var_dump( $arrPerfilUsuario ); exit();
			$arrPerfilUsuario['username'] = ucwords($arrPerfilUsuario['username']);
			$arrPerfilUsuario['colegiatura'] = ucwords($arrPerfilUsuario['colegiatura_profesional']);
			$arrPerfilUsuario['profesional'] = strtoupper($arrPerfilUsuario['med_nombres'].' '.$arrPerfilUsuario['med_apellido_paterno'].' '.$arrPerfilUsuario['med_apellido_materno']);
			if( isset($arrPerfilUsuario['idusers']) ){ 
				$arrData['message'] = 'Ha cambiado su empresa-especialidad a: <strong>'.strtoupper($arrPerfilUsuario['empresa']).'-'.strtoupper($arrPerfilUsuario['especialidad']).'</strong>'; 
				$this->session->set_userdata('sess_vs_'.substr(base_url(),-8,7),$arrPerfilUsuario);
				$arrData['datos']['idempresamedico'] = $arrPerfilUsuario['idempresamedico'];
				$arrData['flag'] = 1;
			}else{ 
				$arrData['flag'] = 0;
				$arrData['message'] = 'Ocurrió un problema, al querer cambiar de especialidad.';
			}
		}else{
			$arrData['flag'] = 0;
			$arrData['message'] = 'Inicie sesión para continuar.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cambiar_sede_session(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs['datos']['idsedeempresaadmin']); exit(); 
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		if( $this->sessionHospital['vista_sede_empresa'] == 1 ){ 
			// $fila = $this->model_acceso->m_obtener_sede($allInputs['datos']['idsede']); 
			$fila = $this->model_acceso->m_listar_perfil_usuario_salud($this->sessionHospital['idusers'],NULL,$allInputs['datos']['id_empresa_admin']); 
		}else{ 
			// var_dump($allInputs['datos']['idsedeempresaadmin']); exit(); 
			$fila = $this->model_acceso->m_obtener_sede_empresa($allInputs['datos']['idsedeempresaadmin']); 
		}
		foreach ($fila as $key => $val) {
			$_SESSION['sess_vs_'.substr(base_url(),-8,7)][$key] = $val;
		} 
		// $_SESSION['sess_vs_'.substr(base_url(),-8,7)]['idsedeempresaadmin'] = $allInputs['datos']['idsedeempresaadmin'];
		// $_SESSION['sess_vs_'.substr(base_url(),-8,7)]['idsede'] = $allInputs['datos']['idsede'];
		// $_SESSION['sess_vs_'.substr(base_url(),-8,7)]['idempresaadmin'] = $allInputs['datos']['idempresaadmin'];
		// var_dump($fila,$_SESSION); exit();
		if($allInputs['datos']){
			$arrData['flag'] = 1;
			$arrData['message'] = 'Sede cambiada.';
		}else{
			$arrData['flag'] = 0;
			$arrData['message'] = 'Ocurrio un error.';
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function getSessionCI()
	{
		$arrData['flag'] = 0;
		$arrData['datos'] = array();
		if( $this->session->has_userdata( 'sess_vs_'.substr(base_url(),-8,7) ) && !empty($_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['idusers']) ){
			$arrData['flag'] = 1;
			$arrData['datos'] = $_SESSION['sess_vs_'.substr(base_url(),-8,7) ];
			$arrData['datos']['listaEspecialidadesSession'] = array(); 
			$arrData['datos']['listaNotificaciones'] = array();
			//$arrData['datos']['listaNotificaciones'] = $this->lista_notificaciones();

			if( !empty($arrData['datos']['idmedico']) ){ 
				$arrDataMedico = array( 
					'idmedico'=> $arrData['datos']['idmedico'],
					'id_empresa_admin'=> $arrData['datos']['id_empresa_admin']
				);
				// var_dump($_SESSION['sess_vs_'.substr(base_url(),-8,7) ]); exit();
				$lista = $this->model_empleado->m_cargar_especialidades_del_medico_combo_master($arrDataMedico);
				$arrListado = array();
				foreach ($lista as $row) {
					array_push($arrListado, 
						array(
							'id' => $row['idempresamedico'],
							'idempresaespecialidad' => $row['idempresaespecialidad'],
							'id_empresa_admin' => $row['id_empresa_admin'],
							'descripcion' => $row['especialidad'] . ' / ' . $row['empresa'],
							'idespecialidad' => $row['idespecialidad'],
							'especialidad' => $row['especialidad'],
							'idempresa' => $row['idempresa'],
							'empresa' => $row['empresa']
						)
					);
				}
				$arrData['datos']['listaEspecialidadesSession'] = $arrListado;
			}
			$arrParams['id'] = $_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['idusers'];
			$fila = $this->model_usuario->m_cargar_este_usuario($arrParams); 
			if( !empty($fila) ){ 
				$_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['real_time_huella'] = $fila['real_time_huella']; 
			}
			// TIPO DE CAMBIO
			$arrTipoCambio = ObtenerTipoCambio();
			if( !empty($arrTipoCambio) ){
				$arrTipoCambio['compra'] = number_format($arrTipoCambio['compra'],2);
				$arrTipoCambio['venta'] = number_format($arrTipoCambio['venta'],2);
				$_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['tc_compra'] =$arrTipoCambio['compra']; 
				$_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['tc_venta'] = $arrTipoCambio['venta']; 
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function logoutSessionCI()
	{
		// var_dump("<pre>",$_SESSION);
		//unset($_SESSION['sess_vs_'.substr(base_url(),-8,7]);
		$this->session->unset_userdata('sess_vs_'.substr(base_url(),-8,7));
        /*$this->session->sess_destroy();*/
        $this->cache->clean();
	}
}
