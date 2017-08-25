<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_usuario','model_reporte_centralizado', 'model_acceso', 'model_grupo'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//var_dump($this->session->userdata); exit();
		date_default_timezone_set("America/Lima"); //var_dump($this->user);
		// if(!@$this->user) redirect ('#'); 
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_usuarios()
	{ 
		//$this->load->library('encrypt');
		ini_set('xdebug.var_display_max_depth', 7);
	    ini_set('xdebug.var_display_max_children', 256);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_usuario->m_cargar_usuarios($paramPaginate);
		$totalRows = $this->model_usuario->m_count_usuarios($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			$estadoUsuario = ($row['estado_usuario'] == 1 ? 'HABILITADO':'DESHABILITADO');
			$claseEstado = ($row['estado_usuario'] == 1 ? 'label-success':'label-default');

			array_push($arrListado, 
				array(
					'id' => $row['idusers'],
					'iddetalle' => $row['idusersgroups'],
					'groupId' => $row['idgroup'],
					'usuario' => $row['username'],
					'empleado' => $row['empleado'],
					'nombre_foto' => $row['nombre_foto'],
					'email' => $row['email'],
					'grupo' => $row['name'],
					'vista_sede_empresa' => $row['vista_sede_empresa'],
					'estado' => array(
						'string' => $estadoUsuario,
						'clase' =>$claseEstado,
						'bool' =>$row['estado_usuario']
					),
					
				)
			);

		}
		//var_dump($arrUser); exit();
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
	public function lista_usuario_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$listaCbo = $this->model_usuario->m_cargar_usuarios_cbo($allInputs);
		}else{
			$listaCbo = $this->model_usuario->m_cargar_usuarios_cbo();
		}
		$arrListado = array();
		foreach ($listaCbo as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idusers'],
					'descripcion' => $row['username']
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaCbo)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_usuarios_caja_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$listaCbo = $this->model_usuario->m_cargar_usuarios_caja($allInputs);
		$arrListado = array();
		foreach ($listaCbo as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idusers'],
					//'descripcion' => strtoupper($row['username']),
					'descripcion' => strtoupper( substr($row['nombres'], 0, 1) . $row['apellido_paterno']),
					'usuario' => strtoupper($row['username'])
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaCbo)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_sedes_no_agregados_a_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$listaSedesNoAgregados = $this->model_usuario->m_cargar_sedes_no_agregados_a_usuario($paramPaginate,$datos);
		$totalRows = $this->model_usuario->m_count_sedes_no_agregados_a_usuario($datos);
		$arrListado = array();
		foreach ($listaSedesNoAgregados as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsede'],
					'descripcion' => $row['descripcion']
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaSedesNoAgregados)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('seguridad/usuario_formView');
	}	
	public function ver_popup_agregar_sede()
	{
		$this->load->view('seguridad/popupAgregarSedeView');
	}
	public function ver_popup_password()
	{
		$this->load->view('seguridad/popupCambiarPasswordView');
	}
	public function ver_popup_ingreso_usuario_password()
	{
		$this->load->view('seguridad/popupUsuarioPasswordView');
	}
	public function agregar_sede_a_usuario()
	{
		/*$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//  var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
    	foreach ($allInputs['sedes'] as $row) { 
    		$row['iduser'] = $allInputs['usuarioId'];
    		if($this->model_usuario->m_agregar_sede_usuario($row)){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
    	}
    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));*/
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit(); 
		$paramDatosUser = $allInputs['dataUsuario']; // groupId
		$paramDatosSedes = $allInputs['sedesEmpresa'];
		$fGrupo = $this->model_grupo->m_cargar_este_grupo($paramDatosUser['groupId']); // var_dump($allInputs); exit(); 
		if($fGrupo['vista_sede_empresa'] == 1){
			foreach ($paramDatosSedes as $key => $row) { 
				$paramDatosSedes[$key]['idsedeempresaadmin'] = 1; // SI EL GRUPO ES SALUD U OTRO QUE TENGA VALOR 1; idsedeempresaadmin = 1 
			}
		}
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$registro = FALSE;
    	if( empty($paramDatosSedes) ){
    		$arrData['message'] = 'Falta asignar sede/empresa al Usuario';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	
    	$this->db->trans_start();
    	$datos[] = null;
		if($this->model_usuario->m_registrar($paramDatosUser)){ 
			$paramDatosUser['id'] = GetLastId('idusers','users');
			if($this->model_usuario->m_registrar_detalle($paramDatosUser)){ // registra usuarios x grupo
				$registro = TRUE;
			}
			$datos['iduser'] = $paramDatosUser['id'];
			foreach ($paramDatosSedes as $row) {
				$datos['id'] = @$row['idsede'];
				$datos['idsedeempresaadmin'] = $row['idsedeempresaadmin'];
				if($this->model_usuario->m_agregar_sede_usuario($datos)){
					$registro = TRUE;
				}else{
					$registro = FALSE;
					break;
				}
			}
			if($registro){
				$arrData['message'] = 'Se registraron los datos correctamente';
    			$arrData['flag'] = 1;
    			$arrData['idusuario'] = $paramDatosUser['id'];
    			$arrData['usuario'] = $paramDatosUser['usuario'];
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatosUser = $allInputs['dataUsuario'];
		$paramDatosSedes = $allInputs['sedesEmpresa'];
		$fGrupo = $this->model_grupo->m_cargar_este_grupo($paramDatosUser['groupId']); // var_dump($fGrupo); exit();
		if($fGrupo['vista_sede_empresa'] == 1){ 
			foreach ($paramDatosSedes as $key => $row) { 
				$paramDatosSedes[$key]['idsedeempresaadmin'] = 1; // SI EL GRUPO ES SALUD U OTRO QUE TENGA VALOR 1; idsedeempresaadmin = 1 
			}
		}
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_usuario->m_editar($paramDatosUser)){
			if($this->model_usuario->m_editar_detalle($paramDatosUser) ){

				$datos['iduser'] = $paramDatosUser['id'];
				foreach ($paramDatosSedes as $row) {
					if($row['es_temporal']){
						$datos['id'] = @$row['idsede'];
						$datos['idsedeempresaadmin'] = $row['idsedeempresaadmin'];
						if($this->model_usuario->m_agregar_sede_usuario($datos)){
							$registro = TRUE;
						}else{
							$registro = FALSE;
							break;
						}
					}else{
						if($this->model_usuario->m_actualizar_estado($row['idusersporsede'],$row['estado']['bool'])){
							$registro = TRUE;
						}else{
							$registro = FALSE;
							break;
						}
					}
				}
				$arrData['message'] = 'Se editaron los datos correctamente';
    			$arrData['flag'] = 1;
			}
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function quitar_sede_de_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_usuario->m_quitar_sede_usuario($allInputs['idusersporsede'])) { 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar al usuario';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_usuario->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitó al usuario correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar al usuario';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_usuario->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se habilitó al usuario correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function verifica_password()
	{
		//$this->load->library('encrypt');
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo actualizar la contraseña, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( @$allInputs['miclave'] === 'si' ){
    		$allInputs['id'] = $this->sessionHospital['idusers']; 
    		//$fUsuario = $this->model_usuario->m_cargar_este_usuario($allInputs); var_dump($fUsuario); exit();
    		// $allInputs['clave'] = $allInputs['password']; 
    	}
    	if($usuario = $this->model_usuario->m_verifica_password($allInputs)){
    		// if( @$allInputs['miclave'] === 'si' ){ 
    		// 	$allInputs['id'] = $this->sessionHospital['idusers']; 
    		// }
			if($this->model_usuario->m_actualizar_password($allInputs)){
	    		$arrData['message'] = 'La contraseña es actualizó correctamente';
	    		$arrData['flag'] = 1;	
    		}
    		
    	}else{
    		$arrData['message'] = 'La contraseña ingresada es incorrecta';
    		$arrData['flag'] = 2;
    	}
    	//var_dump($usuario); exit();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/* =========================== */ 
	/* ADMINISTRACION DE REPORTES  */ 
	/* =========================== */ 
	public function ver_popup_administracion_reportes()
	{
		$this->load->view('seguridad/popupAdministracionReportesView');
	}
	public function lista_reporte_de_usuario_session()
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$lista = $this->model_reporte_centralizado->m_cargar_reportes_de_usuario_session();
		$arrPrincipal = array();
		foreach ($lista as $key => $row) { 
			$rowAux = array(
				'tipoReporte'=> $row['abreviatura_trp'],
				'textReporte'=> $row['descripcion_trp'],
				'idtiporeporte' => $row['idtiporeporte'],
				'open'=> TRUE,
				'reportes' => array()
			);
			$arrPrincipal[$row['idtiporeporte']] = $rowAux;
		}
		
		foreach ($lista as $keyDet => $row) { 
			foreach ($arrPrincipal as $key => $rowDet) { 
				$rowAux = array(
					'id'=> $row['abreviatura_rp'],
					'name'=> $row['nombre_rp'],
					'idtiporeporte' => $row['idtiporeporte'],
					'tipoCuadro'=> 'report'
				);
				$arrPrincipal[$row['idtiporeporte']]['reportes'][$row['idreporte']] = $rowAux;
			}
		}
		$arrPrincipal = array_values($arrPrincipal);
		foreach ($arrPrincipal as $key => $row) {
			foreach ($row['reportes'] as $keyDet => $rowDet) {
				$arrPrincipal[$key]['reportes'] = array_values($arrPrincipal[$key]['reportes']);
			}
		}
		// var_dump($arrPrincipal); exit();
		$arrData['datos'] = $arrPrincipal;
		$arrData['flag'] = 1;
		if(empty($arrPrincipal)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_reportes_no_agregados_a_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_reporte_centralizado->m_cargar_reportes_no_agregados_al_usuario($paramPaginate,$datos);
		$totalRows = $this->model_reporte_centralizado->m_count_reportes_no_agregados_al_usuario($paramPaginate,$datos);
		$arrListado = array();
		
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idreporte'],
					'categoria' => $row['descripcion_trp'],
					'abreviatura' => $row['abreviatura_rp'],
					'nombre' => $row['nombre_rp']
				)
			);
		}
		// var_dump($arrListado); exit();
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
	public function lista_reportes_agregados_a_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_reporte_centralizado->m_cargar_reportes_agregados_al_usuario($paramPaginate,$datos);
		$totalRows = $this->model_reporte_centralizado->m_count_reportes_agregados_al_usuario($paramPaginate,$datos);
		$arrListado = array();
		
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idusersporreporte' => $row['idusersporreporte'],
					'id' => $row['idreporte'],
					'categoria' => $row['descripcion_trp'],
					'abreviatura' => $row['abreviatura_rp'],
					'nombre' => $row['nombre_rp']
				)
			);
		}
		// var_dump($arrListado); exit();
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
	public function agregar_reporte_a_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_reporte_centralizado->m_agregar_reporte_a_usuario($allInputs)){ 
			$arrData['message'] = 'Se agregaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function quitar_reporte_a_usuario()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_reporte_centralizado->m_quitar_reporte_a_usuario($allInputs['idusersporreporte'])){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function verificarUsuarioDirector()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = '';
    	$arrData['flag'] = 0;
		
		$loggedUser = $this->model_acceso->m_logging_user($allInputs);
		if( isset($loggedUser['logged']) && $loggedUser['logged'] >= 1 ) {
			if( $loggedUser['key_grupo'] == 'key_dir_far'){  /* SI ES DIRECTOR DE FARMACIA */
					$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'Debe ser un Director de Farmacia';
			}
		}else{
			$arrData['message'] = 'Usuario o contraseña incorrecta';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	
	public function confirmar_password(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No es correcta la contraseña, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		$allInputs['id'] = $this->sessionHospital['idusers'];
		$usuario = $this->model_usuario->m_confirma_password($allInputs);

    	if($usuario != null){			    		
    		$arrData['message'] = 'La contraseña ingresada es correcta';
    		$arrData['flag'] = 1;
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function reset_password(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos=$allInputs[0];
		$arrData['message'] = 'Error al restablecer la contraseña, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	
		if(is_numeric($datos['usuario'])){
			$datos['claveNueva'] = $datos['usuario'];
		}else{
			$datos['claveNueva'] = '123456789';
		}

		$usuario = $this->model_usuario->m_actualizar_password($datos);

    	if($usuario != null){			    		
    		$arrData['message'] = 'La contraseña fue restablecida correctamente';
    		$arrData['flag'] = 1;
    	}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_user_empleado_autocomplete(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$listaCbo = $this->model_usuario->m_cargar_user_empleado_autocomplete($allInputs);
		$arrListado = array();
		foreach ($listaCbo as $row) { 
			array_push($arrListado, 
				array(
					'idusers' => $row['idusers'],
					'idempleado' => $row['idempleado'],
					'usuario' => strtoupper($row['username']),
					'descripcion' => strtoupper($row['nombres'] . ' ' . $row['apellido_paterno']. ' ' . $row['apellido_paterno']),					
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaCbo)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}