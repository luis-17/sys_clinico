<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpresaAdmin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper'));
		$this->load->model(array('model_empresa_admin'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_empresa_admin_cbo()
	{
		$lista = $this->model_empresa_admin->m_cargar_empresas_admin_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresaadmin'],
					'descripcion' => $row['razon_social']
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
	public function lista_empresa_admin_por_sede_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empresa_admin->m_cargar_empresas_admin_por_sede_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresaadmin'],
					'descripcion' => $row['razon_social']
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
	public function lista_sede_empresa_admin_cbo()
	{
		$lista = $this->model_empresa_admin->m_cargar_sede_empresas_admin_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsedeempresaadmin'],
					'idempresaadmin' => $row['idempresaadmin'],
					'idsede' => $row['idsede'],
					'ruc' => $row['ruc'],
					'descripcion' => strtoupper($row['razon_social']).' - '.strtoupper($row['descripcion']),
					'name' => strtoupper($row['razon_social']).' - '.strtoupper($row['descripcion']),
					'ticked' => false
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
	/*-------------------------------------------------------------*/
	public function lista_sede_empresa_admin()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_empresa_admin->m_cargar_sede_empresas_admin($paramPaginate);
		$totalRows = $this->model_empresa_admin->m_count_sede_empresas_admin();		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'descripcion' => strtoupper($row['descripcion']), // sede
					'razon_social' => strtoupper($row['razon_social']), // empresaadmin
					'idempresaadmin' => $row['idempresaadmin'],
					'idsede' => $row['idsede']
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
	public function lista_sede_empresa_admin_precio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_empresa_admin->m_cargar_sede_empresas_admin_precio($paramPaginate,$datos);
		$totalRows = $this->model_empresa_admin->m_count_sede_empresas_admin_precio($datos);		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'descripcion' => strtoupper($row['descripcion']), // sede
					'razon_social' => strtoupper($row['razon_social']), // empresaadmin
					'idempresaadmin' => $row['idempresaadmin'],
					'idsede' => $row['idsede'],
					'precio_sede' => $row['precio_sede'],
					'idproductopreciosede' => $row['idproductopreciosede']
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
	public function lista_sede_empresa_admin_usuario() // lista_empresa_admin_cbo
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_empresa_admin->m_cargar_sede_empresa_admin_session($datos['id']);
		//$totalRows = $this->model_empresa_admin->m_count_sede_empresas_admin();		
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_ups'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_ups'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}

			array_push($arrListado, 
				array(
					'idusersporsede' => $row['idusersporsede'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'idsede' => $row['idsede'],
					'sede' => strtoupper($row['sede']), // sede
					'empresa' => strtoupper($row['empresa_admin']), // empresaadmin
					'idempresaadmin' => $row['idempresaadmin'],
					'es_temporal' => false,
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_ups']
					)
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
	/*-------------------------------------------------------------*/

	public function lista_empresa_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_empresa_admin->m_cargar_esta_empresa_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idempresaadmin']);
			$fArray['razon_social'] = strtoupper($fArray['razon_social']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_agregar_sede()
	{
		$this->load->view('empresaAdmin/popupAgregarSedeView');
	}
	// ==========================================
	// LISTADO 
	// ==========================================
	public function lista_empresa_admin()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_empresa_admin->m_cargar_empresas_admin($paramPaginate);
		$totalRows = $this->model_empresa_admin->m_count_empresas_admin();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresaadmin'],
					'razon_social' => $row['razon_social'],
					'nombre_legal' => $row['nombre_legal'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'direccion' => $row['direccion'],
					'ruc' => $row['ruc'],
					'nombre_logo' => $row['nombre_logo'],
					'rs_facebook' => $row['rs_facebook'],
					'rs_twitter' => $row['rs_twitter'],
					'rs_youtube' => $row['rs_youtube'],
					'redes_sociales' => array(
						array('url' => $row['rs_facebook'], 'nombre_red'  => 'facebook', 'clase' => 'ti ti-facebook'),
						array('url' => $row['rs_twitter'], 'nombre_red'  => 'twitter', 'clase' => 'ti ti-twitter'),
						array('url' => $row['rs_youtube'], 'nombre_red'  => 'youtube', 'clase' => 'ti ti-youtube')
						// 'twitter' => $row['rs_twitter'],
						// 'youtube' => $row['rs_youtube']
					),
					'cantidad_puntos' => $row['cantidad_puntos'],
					'cantidad_soles' => $row['cantidad_soles'],
					'estado' => $row['estado_emp']
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
	// ==========================================
	// CRUD
	// ==========================================
	public function ver_popup_formulario()
	{
		$this->load->view('empresaAdmin/empresaadmin_formView');
	}	
	public function registrar()
	{
		// var_dump(  ); exit(); 
		$allInputs['razon_social'] = $this->input->post('razon_social');
		$allInputs['nombre_legal'] = $this->input->post('nombre_legal');
		$allInputs['domicilio_fiscal'] = $this->input->post('domicilio_fiscal');
		$allInputs['direccion'] = $this->input->post('direccion');
		$allInputs['ruc'] = $this->input->post('ruc');
		$allInputs['rs_facebook'] = $this->input->post('rs_facebook');
		$allInputs['rs_twitter'] = $this->input->post('rs_twitter');
		$allInputs['rs_youtube'] = $this->input->post('rs_youtube');
				
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0; 
    	$this->db->trans_start();
    	if( empty($_FILES) ){
			$allInputs['nombre_logo'] = 'noimage.jpg'; 
			if($this->model_empresa_admin->m_registrar($allInputs)){ 
				
				$arrData['message'] = 'Se registraron los datos correctamente'; 
    			$arrData['flag'] = 1; 
			}
		}else{
			if( subir_fichero('assets/img/dinamic/empresa','fotoEmpresa') ){ 
				$allInputs['nombre_logo'] = $_FILES['fotoEmpresa']['name']; 
				if($this->model_empresa_admin->m_registrar($allInputs)){ 
					
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1; 
				}
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{	
		$allInputs['id'] = $this->input->post('id');
		$allInputs['razon_social'] = $this->input->post('razon_social');
		$allInputs['nombre_legal'] = $this->input->post('nombre_legal');
		$allInputs['domicilio_fiscal'] = $this->input->post('domicilio_fiscal');
		$allInputs['direccion'] = $this->input->post('direccion');
		$allInputs['ruc'] = $this->input->post('ruc');
		$allInputs['rs_facebook'] = $this->input->post('rs_facebook');
		$allInputs['rs_twitter'] = $this->input->post('rs_twitter');
		$allInputs['rs_youtube'] = $this->input->post('rs_youtube');
		$allInputs['cantidad_puntos'] = $this->input->post('cantidad_puntos');
		$allInputs['cantidad_soles'] = $this->input->post('cantidad_soles');
		foreach ($allInputs as $key => $value) {
			if($value == '' || $value == 'null'){
				$allInputs[$key] = null;
			}
			
		}
		// var_dump($allInputs);
		// exit();		
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0; 
    	//$this->db->trans_start();
    	if( empty($_FILES) ){
			$allInputs['nombre_logo'] = $this->input->post('nombre_logo');
			if($this->model_empresa_admin->m_editar($allInputs)){ 
				
				$arrData['message'] = 'Se registraron los datos correctamente'; 
    			$arrData['flag'] = 1; 
			}
		}else{
			if( subir_fichero('assets/img/dinamic/empresa','fotoEmpresa') ){ 
				$allInputs['nombre_logo'] = $_FILES['fotoEmpresa']['name']; 
				if($this->model_empresa_admin->m_editar($allInputs)){ 
					
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1; 
				}
			}
		}
		//$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_empresa_admin->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_sede()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// verificar si existe la sede y si esta activa
    	$sea = $this->model_empresa_admin->m_obtener_sede_empresa($allInputs);
    	if( $sea ){
    		if( $sea['estado_sea'] == 0 ){ // si ya existe una sede-empresa pero esta anulado
    			if($this->model_empresa_admin->m_activar_sede_empresa($sea['idsedeempresaadmin'])){ 
					$arrData['message'] = 'Se editaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}
    		}
    	}else{ // si no existe la sede-empresa se registra uno nuevo
	    	if($this->model_empresa_admin->m_agregar_sede_a_empresa($allInputs)){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
    	}
		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function quitar_sede()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//foreach ($allInputs['roles'] as $row) { 
		//$row['groupId'] = $allInputs['idRolPorGrupo'];
		if($this->model_empresa_admin->m_quitar_sede_a_empresa($allInputs['idsedeempresaadmin'])){ 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
    	//}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}