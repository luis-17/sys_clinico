<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rol extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_rol','model_modulo','model_config'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_roles()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_rol->m_cargar_roles($paramPaginate);
		$totalRows = $this->model_rol->m_count_roles();
		$arrListado = array();
		foreach ($lista as $row) {
			$rol = NULL;
			$icono = NULL;
			if($row['idparent'] == NULL){
				$rol = $row['descripcion_rol'];
				$icono = $row['icono_rol'];
			}
			array_push($arrListado,
				array(
					'id' => $row['idrol'],
					'orden' => $row['orden'],
					'rol' => $rol,
					'subrol' => $row['descripcion_rol'],
					'url' => $row['url_rol'],
					'icono' => $icono
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
	private function generate_menu_session($tipomodulo,$idmodulo=false)
	{
		ini_set('xdebug.var_display_max_depth', 5);
	    ini_set('xdebug.var_display_max_children', 256);
	    ini_set('xdebug.var_display_max_data', 1024);

		$idespecialidad = isset($allInputs['idespecialidad']);
		$lista = $this->model_rol->m_cargar_roles_session($idmodulo);

		// CONSULTA PARA DETERMINAR TIPO DE VENTA
    	$arrConfig = array();
    	$ventaNormal = FALSE;
    	$listaConf = $this->model_config->m_listar_configuraciones();
    	foreach ($listaConf as $key => $rowConfig) {
    		$arrConfig[$rowConfig['key_cf']] = $rowConfig['valor_cf'];
    	}
    	// if($arrConfig['modo_venta_far'] == 'VN'){
    	// 	$ventaNormal = TRUE;
    	// }

    	$arrListado = array();

    	// OBTENEMOS LOS MODULOS
    	foreach ($lista as $key => $row) {
    		if( $tipomodulo == $row['es_unidad_negocio']){ // UNIDAD DE NEGOCIO 1: SI, 2: NO
	    		$rowAux = array(
	    			'idmodulo' => $row['idmodulo'],
	    			'modulo' => $row['modulo'],
	    			'abreviatura' => $row['abreviatura'],
	    			'roles' => array()
	    		);
	    		$arrListado[$row['idmodulo']] = $rowAux;
    		}
    		
    	}

    	// OBTENEMOS ROLES DEL MODULO
    	foreach ($lista as $key => $row) {
    		if( $tipomodulo == $row['es_unidad_negocio']){
	    		$rowAux = array(
	    			'idrol' => $row['idrol'],
	    			'rol' => $row['rol'],
	    			'url' => $row['url'],
	    			'icono' => $row['icono'],
	    			'orden' => $row['orden'],
	    			'subroles'=> array()
	    		);
	    		$arrListado[$row['idmodulo']]['roles'][$row['idrol']] = $rowAux;
    		}
    		
    	}
    	// OBTENEMOS SUBROLES DEL ROL
    	foreach ($lista as $key => $row) {
    		if( $tipomodulo == $row['es_unidad_negocio']){
	    		if( !empty($row['subidrol']) ){
		    		$condicional = 'si';
		    		// var_dump($row); exit();
		    		if( $row['modulo'] == 'FARMACIA' && $row['subrol'] == 'Nuevo Pedido' && $arrConfig['modo_venta_far'] == 'VN' ){
		    			//unset($row);
		    			$condicional = 'no';
		    		}elseif( $row['modulo'] == 'FARMACIA' && $row['subrol'] == 'Nueva Venta' && $arrConfig['modo_venta_far'] == 'VP' ){
		    			//unset($row);
		    			$condicional = 'no';
		    		}
		    		if($condicional === 'si'){
		    			$rowAux = array(
			    			'subidrol' => $row['subidrol'],
			    			'subrol' => $row['subrol'],
			    			'url'=> $row['suburl'],
			    			'suborden' => $row['suborden']
			    		);
			    		$arrListado[ $row['idmodulo'] ] ['roles'][$row['idrol'] ] ['subroles'][$row['subidrol'] ] = $rowAux;
		    		}
	    		}else{
	    			unset($arrListado[ $row['idmodulo'] ] ['roles'][$row['idrol'] ]['subroles']);
	    		}
    		}
    		
    	}
    	//var_dump($arrListado[1]['roles']); exit();
    	return $arrListado;
	}
	public function lista_roles_favoritos_usuario(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_rol->m_cargar_roles_favoritos_usuario($allInputs['idusers']);
		$arrListado = array();

		foreach ($lista as $row) {
			array_push($arrListado, array(
				'label' => $row['descripcion_rol'],
				'url' => $row['url_rol'],
				'iconClasses' => 'fa fa-star',
				'idrolfavorito' => $row['idrolfavorito'],
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
	public function agregar_rol_a_favoritos(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
	   	$arrData['message'] = '';
    	$arrData['flag'] = 0;
    	$cantidad_favoritos = 5;
    	// SOLO SE PUEDEN AGREGAR 5 FAVORITOS, VERIFICAMOS SI SE LLEGO A ESA CIFRA.
    	if( $this->model_rol->m_count_favoritos_usuario($allInputs['iduser']) >= $cantidad_favoritos ){
    		$arrData['message'] = 'Solo se permiten '. $cantidad_favoritos .' favoritos.';
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
    	}
    	// buscar el rol por la url
    	if( $data = $this->model_rol->m_cargar_rol_por_url($allInputs) ){
			$allInputs['idrol'] = $data['idrol'];
    	}else{
    		$arrData['message'] = 'La página que desea agregar no se encuentra en su menu. Comuniquese con el Area de Sistemas';
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
    	}
    	// verificar que el rol aun no se ha agregado a favoritos
    	if($this->model_rol->m_verificar_si_existe_rol_favorito($allInputs)){
    		$arrData['message'] = 'La página ya ha sido agregada a Favoritos';
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
    	}

    	//var_dump($allInputs); exit();
    	if($this->model_rol->m_registrar_rol_favorito($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function eliminar_rol_de_favorito(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_rol->m_eliminar_favorito($allInputs)){
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_unidades_negocio_session(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idgroup = $allInputs['idgroup'];

		$lista = $this->generate_menu_session(1); // 1: UNIDAD DE NEGOCIO
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, array(
				'id' => $row['idmodulo'],
				'descripcion' => $row['modulo'],
				'abreviatura' => $row['abreviatura'],
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
	public function lista_roles_unidad_negocio_session(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idgroup = $allInputs['idgroup'];
		$idmodulo = $allInputs['idunidadnegocio'];
		// $idespecialidad = isset($allInputs['idespecialidad']);
		$lista = $this->generate_menu_session(1,$idmodulo); // 1: UNIDAD DE NEGOCIO
		// var_dump($lista); exit();
		$arrListado = array();

		foreach ($lista[$idmodulo]['roles'] as $key => $value) {
			if( !empty($value['subroles'])){
				$arrSubroles = array();
				foreach ($value['subroles'] as $row) {
					array_push($arrSubroles, array(
						'label' => $row['subrol'],
						'url' => $row['url'],
						)
					);
				}
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono'],
					'children' => $arrSubroles
					)
				);
			}else{
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono']
					)
				);
			}

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
	public function lista_modulos_session(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idgroup = $allInputs['idgroup'];

		$lista = $this->generate_menu_session(2); // 2: MODULO
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, array(
				'id' => $row['idmodulo'],
				'descripcion' => $row['modulo'],
				'abreviatura' => $row['abreviatura'],
				)
			);
		}
		// var_dump($arrListado); exit();
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
	public function lista_roles_session(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idgroup = $allInputs['idgroup'];
		$idmodulo = $allInputs['idmodulo'];
		// $idespecialidad = isset($allInputs['idespecialidad']);
		$lista = $this->generate_menu_session(2,$idmodulo);
		//var_dump($arrListado); exit();
		$arrListado = array();
		//print_r($lista[$idmodulo]['roles']);

		foreach ($lista[$idmodulo]['roles'] as $key => $value) {
			if( !empty($value['subroles'])){
				$arrSubroles = array();
				foreach ($value['subroles'] as $row) {
					array_push($arrSubroles, array(
						'label' => $row['subrol'],
						'url' => $row['url'],
						)
					);
				}
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono'],
					'children' => $arrSubroles
					)
				);
			}else{
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono'],
					)
				);
			} 
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

	public function lista_roles_externos_session(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$idgroup = $allInputs['idgroup'];
		$posicion = 1 ; // 1: Arriba, 2: Abajo
		$lista = $this->model_rol->m_cargar_roles_menu_externo_session($posicion);
		// var_dump($lista); exit();
		$arrListado = array();

		foreach ($lista as $key => $value) {
			if( !empty($value['subroles'])){
				$arrSubroles = array();
				foreach ($value['subroles'] as $row) {
					array_push($arrSubroles, array(
						'label' => $row['subrol'],
						'url' => $row['url'],
						)
					);
				}
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono'],
					'children' => $arrSubroles,
					'posicion' => $value['pos_fuera_modulo']
					)
				);
			}else{
				array_push($arrListado, array(
					'label' => $value['rol'],
					'url' => $value['url'],
					'iconClasses' => $value['icono'],
					'posicion' => $value['pos_fuera_modulo']
					)
				);
			}

		}
		// var_dump($arrListado); exit();
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

	public function ver_popup_formulario()
	{
		$this->load->view('seguridad/rol_formView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_rol->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_rol->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
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
			if( $this->model_rol->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}