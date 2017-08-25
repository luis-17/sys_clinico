<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empresa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_empresa'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_empresas()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		
		if(isset($allInputs['datos'])){
			$datos = $allInputs['datos'];
			$datos['ruc'] = $this->sessionHospital['ruc_empresa_admin'];
		}else{
			$datos = FALSE;
		}

		if(!empty($allInputs['tipoEmpresa']['id'])){
			$tipoEmpresa = $allInputs['tipoEmpresa'];
		}else{
			$tipoEmpresa = FALSE;
		}

		$lista = $this->model_empresa->m_cargar_empresas($paramPaginate,$datos,$tipoEmpresa);
		$totalRows = $this->model_empresa->m_count_empresas($paramPaginate,$datos,$tipoEmpresa);
		$arrListado = array();
		foreach ($lista as $row) {
			// $arrIdEmpresaPorEspec = explode(",",$row['idempresaespecialidades']);
			// $arrIdEspecialidad = explode(",",$row['idespecialidades']);
			// $arrEspecialidades = explode(",",$row['especialidades']);
			// $arrDetalle = array();
			/*foreach ($arrIdEmpresaPorEspec as $key => $value) { 
				if(!empty($arrIdEmpresaPorEspec[$key])){
					array_push($arrDetalle, 
						array(
							'idEmpresaPorEspec' => $arrIdEmpresaPorEspec[$key], 
							'idEspecialidad' => $arrIdEspecialidad[$key],
							'especialidad' => strtoupper($arrEspecialidades[$key])
						)
					);
				}
			}*/
			if( $row['estado_em'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_em'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}

			$idempresaadmin = null;
			$idempresadetalle = null;
			$estado_ed = null;
			$objEstado = null;
			if($datos){
				$idempresaadmin = $row['idempresaadmin'];
				$idempresadetalle = $row['idempresadetalle'];
				if( $row['estado_ed'] == 1 ){
					$estado_ed = 'HABILITADO';
					$clase_ed = 'label-success';
				}
				if( $row['estado_ed'] == 2 ){
					$estado_ed = 'DESHABILITADO';
					$clase_ed = 'label-default';
				}
				$estado_ed = array(
						'string' => $estado_ed,
						'clase' =>$clase_ed,
						'bool' =>$row['estado_ed']
					);

				if( $row['estado_ed'] == 1 ){ // ACTIVO 
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-success';
					$objEstado['labelText'] = 'HABILITADO';
				}elseif( $row['estado_ed'] == 2 ){ // ACTIVO 
					$objEstado['claseIcon'] = 'fa-power-off';
					$objEstado['claseLabel'] = 'label-default';
					$objEstado['labelText'] = 'DESHABILITADO';
				}

			}
			$es_empresa_admin = ($row['es_empresa_admin'] == 1) ? true : false;
			$tipo_empresa = array();
			if( $es_empresa_admin ){ 
				$tipo_empresa['clase'] = 'label-success';
				$tipo_empresa['string'] = 'SI';
			}else{ 
				$tipo_empresa['clase'] = 'label-info';
				$tipo_empresa['string'] = 'NO';
			}
			// $es_empresa_admin = ($row['es_empresa_admin'] == 1) ? true : false;
			// $tipo_empresa = array();
			// if($es_empresa_admin){
			// 	$tipo_empresa['clase']  = 'label-warning';
			// 	$tipo_empresa['icono']  = 'fa fa-check';
			// }else{
			// 	$tipo_empresa['clase']  = 'label-danger';
			// 	$tipo_empresa['icono']  = 'fa fa-times';
			// }

			array_push($arrListado, 				
				array(
					//'idsede' => $row['idsede'],
					'idempresa' => $row['idempresa'],
					'idempresaadmin' => $idempresaadmin,
					'idempresadetalle' => $idempresadetalle,					
					//'sede' => $row['sede'],
					'empresa' => $row['empresa'],
					'nombre_corto' => $row['descripcion_corta'],
					'ruc_empresa' => $row['ruc_empresa'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'representante_legal' => $row['representante_legal'],
					'telefono' => $row['telefono'],
					'cuenta_detraccion'=> $row['num_cuenta_detraccion'],
					'cuenta'=> $row['num_cuenta'],
					'banco'=> array( 
						'id' => $row['idbanco'],
						'descripcion' => $row['descripcion_banco']
					),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_em']
					),
					'estado_ema' => $objEstado,
					'estado_ed' => $estado_ed,
					'es_empresa_admin' => $es_empresa_admin,
					'tipo_empresa' => $tipo_empresa,
					//'tiene_contrato' => ($row['tiene_contrato'] == 1) ? true : false
					// 'especialidades' => $arrDetalle
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
	public function lista_empresas_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(!empty($allInputs)){
			$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		}
		$lista = $this->model_empresa->m_cargar_empresas_hab_deshab_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresa'],
					'descripcion' => $row['descripcion'],				
					'name' => '<b>'.strtoupper($row['descripcion']).'</b> ',
					'nombre_corto' => $row['descripcion_corta'],
					'ticked' => FALSE,
					'ruc_empresa' => $row['ruc_empresa'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'representante_legal' => $row['representante_legal'], 
					'telefono' => $row['telefono'], 
					'idbanco' => $row['idbanco'], 
					'num_cuenta' => $row['num_cuenta'], 
					'num_cuenta_detraccion' => $row['num_cuenta_detraccion'], 
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
	public function lista_empresas_solo_admin_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empresa->m_cargar_empresas_solo_admin_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresa'],
					'descripcion' => $row['descripcion'],
					'ruc' => $row['ruc_empresa'],
					'descripcion_corta' => $row['descripcion_corta'],
					'regimen' => $row['regimen'],
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
	public function lista_empresa_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_empresa->m_cargar_esta_empresa_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idempresa']);
			$fArray['razon_social'] = strtoupper($fArray['descripcion']);
			$fArray['empresa'] = strtoupper($fArray['descripcion']);
			$fArray['ruc'] = $fArray['ruc_empresa'];
			$fArray['banco']= array('id'=>$fArray['idbanco'],'descripcion'=>$fArray['descripcion_banco']);
			$fArray['nombre_corto'] = $fArray['descripcion_corta'];
			$fArray['cuenta_detraccion'] = strtoupper($fArray['num_cuenta_detraccion']);
			$fArray['cuenta'] = strtoupper($fArray['num_cuenta']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_empresa_por_ruc()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_empresa->m_cargar_esta_empresa_por_ruc($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idempresa']);
			$fArray['razon_social'] = strtoupper($fArray['descripcion']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades_no_agregados_a_empresa_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empresa->m_cargar_especialidades_no_agregados_a_empresa_autocompletado($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'],
					'descripcion' => $row['nombre']
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
	public function lista_especialidades_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$listaEspecialidadesNoAgregados = $this->model_empresa->m_cargar_especialidades_empresa($paramPaginate,$datos);
		$totalRows = $this->model_empresa->m_count_especialidades_empresa($paramPaginate,$datos);
		$arrListado = array();
		foreach ($listaEspecialidadesNoAgregados as $row) {
			if( $row['estado_emes'] == 1 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'HABILITADO';
			}elseif( $row['estado_emes'] == 2 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-power-off';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'DESHABILITADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idespecialidad'],
					'nombre' => $row['nombre'],
					'porcentaje' => $row['porcentaje'],
					'idempresaespecialidad' => $row['idempresaespecialidad'],
					'idempresa' => $row['idempresa'],
					'idempresadetalle' => $row['idempresadetalle'],
					'estado_emes' => $row['estado_emes'],
					'estado' => $objEstado,
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaEspecialidadesNoAgregados)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_empresas_de_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		// var_dump($datos); exit();
		$lista = $this->model_empresa->m_cargar_empresas_de_especialidad($datos); 
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idempresaespecialidad'],
					'descripcion'=> $row['empresa'].' / '.$row['nombre'],
					'idespecialidad' => $row['idespecialidad'],
					'nombre' => $row['nombre'],
					'porcentaje' => $row['porcentaje'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'idempresadetalle' => $row['idempresadetalle'] 
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

	public function listar_proveedores_contabilidad()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_empresa->m_cargar_empresas($paramPaginate);
		$totalRows = $this->model_empresa->m_count_empresas($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_em'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_em'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'idempresa' => $row['idempresa'],
					'razon_social' => $row['empresa'],
					'ruc' => $row['ruc_empresa'],
					'telefono'=>  $row['telefono'],
					'banco'=> array( 
						'id' => $row['idbanco'],
						'descripcion' => $row['descripcion_banco']
					),
					'nombre_corto' => $row['descripcion_corta'],
					'domicilio_fiscal' => $row['domicilio_fiscal'],
					'representante_legal' => $row['representante_legal'],
					'cuenta_detraccion'=> $row['num_cuenta_detraccion'],
					'cuenta'=> $row['num_cuenta'],
					'empresa' => $row['empresa'],
					'es_empresa_admin' => $row['es_empresa_admin'],
					'ruc_empresa' => $row['ruc_empresa'],
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
	public function ver_popup_formulario()
	{
		$this->load->view('empresa/empresa_formView');
	}
	public function ver_popup_agregar_especialidad()
	{
		$this->load->view('empresa/popupEspecialidadMedicoView');
		// $this->load->view('empresa/popupAgregarEspecialidadView');
	}
	public function ver_popup_busqueda_proveedor()
	{
		$this->load->view('empresa/popup_busqueda_proveedor');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$allInputs['empresa'] = strtoupper_total(preg_replace('/\s+/', ' ', $allInputs['empresa']));
    	$filtro = array(
			'search' => $allInputs['empresa'],
			'nameColumn' => 'descripcion',
    	);
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual nombre.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$filtro['search'] = $allInputs['ruc_empresa'];
		$filtro['nameColumn'] = 'ruc_empresa';
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual RUC.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		// BUSCAR EMPRESAS CON EL MISMO NOMBRE 
		$allInputs['search'] = $allInputs['empresa'];
		$allInputs['nameColumn'] = 'descripcion';
		$listaEmpresa = $this->model_empresa->m_cargar_empresas_cbo($allInputs);
		if( !empty($listaEmpresa) ){
			$idempresa = $listaEmpresa[0]['idempresa'];
		}	
    	
		if(!empty($idempresa)){
    		$arrData['message'] = 'Existe empresa con igual nombre.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$allInputs['search'] = $allInputs['ruc_empresa'];
		$allInputs['nameColumn'] = 'ruc_empresa';
		$listaEmpresa = $this->model_empresa->m_cargar_empresas_cbo($allInputs);
		if( !empty($listaEmpresa) ){
			$idempresa = $listaEmpresa[0]['idempresa'];
		}	
    	
		if(!empty($idempresa)){
    		$arrData['message'] = 'Existe empresa con igual RUC.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	
    	//$data['idsede'] = $allInputs['idsede'];
		$data['descripcion'] = strtoupper($allInputs['empresa']);
		$data['descripcion_corta'] = empty($allInputs['nombre_corto'])? NULL : strtoupper_total($allInputs['nombre_corto']);
		$data['ruc_empresa'] = $allInputs['ruc_empresa'];
		$data['domicilio_fiscal'] = $allInputs['domicilio_fiscal'];
		$data['representante_legal'] = $allInputs['representante_legal'];
		$data['telefono'] = @$allInputs['telefono'];
		$data['idbanco'] = empty($allInputs['banco']['id'])? NULL : $allInputs['banco']['id'];
		$data['num_cuenta_detraccion'] = empty($allInputs['cuenta_detraccion'])? NULL : $allInputs['cuenta_detraccion'];
		$data['num_cuenta'] = empty($allInputs['cuenta'])? NULL : $allInputs['cuenta'];
		$data['es_empresa_admin'] = ($allInputs['es_empresa_admin']) ? 1 : 2 ;
		$data['createdAt'] = date('Y-m-d H:i:s');
		$data['updatedAt'] = date('Y-m-d H:i:s');
		// var_dump($allInputs); exit();
		$this->db->trans_start();
    	if($this->model_empresa->m_registrar($data)){    		
			$idempresa = GetLastId('idempresa','empresa');
			if($allInputs['es_empresa_admin']){
				$dataDet['idempresatercera'] = $idempresa;			
				$dataDet['idempresaadmin'] = $idempresa;
				$dataDet['tiene_contrato'] = 0;
				if($this->model_empresa->m_registrar_empresa_det($dataDet)){
					$arrData['message'] = 'Se registraron los datos correctamente';
					$arrData['flag'] = 1;
				}				 
			}else{
				$arrData['message'] = 'Se registraron los datos correctamente';
				$arrData['flag'] = 1;
			}						 						  		
		}
    	$this->db->trans_complete();				
    	$arrData['idempresa'] = $idempresa;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_especialidad_a_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//print_r($allInputs);
    	// verificar si ya se ha agregado la especialidad a la empresa
    	if( $this->model_empresa->m_validar_empresa_especialidad($allInputs) ){
    		$arrData['message'] = 'La especialidad ya fue agregada a la empresa.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		$this->db->trans_start();
    	if($this->model_empresa->m_agregar_especialidad_empresa($allInputs)){ 
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function quitar_especialidad_de_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),TRUE);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$eliminarMedico = FALSE;
    	//print_r($allInputs);

    	if($this->model_empresa->m_especialidad_tiene_medico($allInputs)){ 
    		$arrData['message'] = 'No se puede anular la especialidad porque hay médicos habilitados';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	// validar si los medicos tienen atenciones o no
    	if($this->model_empresa->m_validar_si_hay_atencion_medica_con_empresa_especialidad($allInputs)) { 
			$arrData['message'] = 'No se puede anular la especialidad porque hay atenciones médicas realizadas';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

    	// primero anular los medico agregados a esa empresa-especialidad
    	if($this->model_empresa->m_quitar_todo_medico_empresa_especialidad($allInputs['idempresaespecialidad'])) { 
			$eliminarMedico = TRUE;
		}
		
		if($this->model_empresa->m_quitar_especialidad_empresa($allInputs['idempresaespecialidad']) && $eliminarMedico) { 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar_especialidad_de_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),TRUE);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		$deshabilitarMedico = FALSE;
    	// primero anular los medico agregados a esa empresa-especialidad
    	if($this->model_empresa->m_deshabilitar_todo_medico_empresa_especialidad($allInputs['idempresaespecialidad'])) { 
			$deshabilitarMedico = TRUE;
		}
		if( $this->model_empresa->m_deshabiitar_especialidad_empresa($allInputs['idempresaespecialidad']) && $deshabilitarMedico ) { 
			$arrData['message'] = 'Se deshabilitaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar_especialidad_de_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),TRUE);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if( $this->model_empresa->m_habilitar_especialidad_empresa($allInputs['idempresaespecialidad']) ) { 
			$arrData['message'] = 'Se habilitaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),TRUE);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;    	   	
    	// BUSCAR EMPRESAS CON EL MISMO NOMBRE
    	$allInputs['empresa'] = strtoupper_total(preg_replace('/\s+/', ' ', $allInputs['empresa']));
    	$filtro = array(
			'search' => $allInputs['empresa'],
			'nameColumn' => 'descripcion',
			'excepto' => 'idempresa',
			'valor_excepto' => $allInputs['idempresa'],
    	);
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual nombre.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$filtro['search'] = $allInputs['ruc_empresa'];
		$filtro['nameColumn'] = 'ruc_empresa';
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual RUC.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	$allInputs['es_empresa_admin'] = ($allInputs['es_empresa_admin']) ? 1 : 2 ;
    	//$allInputs['tiene_contrato'] =  ($allInputs['tiene_contrato']) ? 1 : 2 ;
		$this->db->trans_begin();
		if($this->model_empresa->m_editar($allInputs)){
			//if($allInputs['idempresaadmin'] != null){
			if(!empty($allInputs['idempresaadmin']) ){
				$this->model_empresa->m_editar_empresa_det($allInputs);				
			}						
		}

		if ($this->db->trans_status() == FALSE){
			$this->db->trans_rollback();
		}else{
			$this->db->trans_commit();
			$arrData['message'] = 'Se editaron los datos correctamente';
	    	$arrData['flag'] = 1;
		}

		$allInputs['search'] = $allInputs['empresa'];
		$allInputs['nameColumn'] = 'descripcion';
		$listaEmpresa = $this->model_empresa->m_cargar_empresas_cbo($allInputs);
		if( !empty($listaEmpresa) ){
			$idempresa = $listaEmpresa[0]['idempresa'];
		}
		$arrData['idempresa'] = $idempresa;
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
			if( $this->model_empresa->m_anular($row['idsede'],$row['idempresa']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_porcentaje()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( $this->sessionHospital['key_group'] != 'key_sistemas'){
    		$arrData['message'] = 'Lo sentimos, Ud. no tiene permisos para editar este campo';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
		if($this->model_empresa->m_editar_porcentaje($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_empresas_admin_cbo(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		// var_dump($this->sessionHospital); exit(); 
		$datos = array( 
			'ruc' => $this->sessionHospital['ruc_empresa_admin']
		);

		$lista = $this->model_empresa->m_cargar_empresas_admin($datos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'ruc_empresa' => $row['ruc_empresa'],
					'key_group' => $this->sessionHospital['key_group']
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

	public function registrar_empresa_det(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
    	$allInputs['empresa'] = strtoupper_total(preg_replace('/\s+/', ' ', $allInputs['empresa']));
    	 // DATA PARA EL REGISTRO DE LA EMPRESA
    	//$data['idsede'] = $allInputs['idsede'];
		$data['descripcion'] = strtoupper($allInputs['empresa']);
		$data['descripcion_corta'] = empty($allInputs['nombre_corto'])? NULL : strtoupper_total($allInputs['nombre_corto']);
		$data['ruc_empresa'] = $allInputs['ruc_empresa'];
		$data['domicilio_fiscal'] = $allInputs['domicilio_fiscal'];
		$data['representante_legal'] = $allInputs['representante_legal'];
		$data['telefono'] = @$allInputs['telefono'];
		$data['idbanco'] = empty($allInputs['banco']['id'])? NULL : $allInputs['banco']['id'];
		$data['num_cuenta_detraccion'] = empty($allInputs['cuenta_detraccion'])? NULL : $allInputs['cuenta_detraccion'];
		$data['num_cuenta'] = empty($allInputs['cuenta'])? NULL : $allInputs['cuenta'];
		//$data['tiene_contrato'] =  ($allInputs['tiene_contrato']) ? 1 : 2 ; 
		$data['createdAt'] = date('Y-m-d H:i:s');
		$data['updatedAt'] = date('Y-m-d H:i:s');

		// DATA PARA EL REGISTRO DE LA RELACION EMA - EMPRESA ADMIN (EMPRESA DETALLE)
		$dataDet['idempresaadmin'] = $allInputs['idempresaadmin'];
		$dataDet['iduser_asigna'] = $this->sessionHospital['idusers'];
		$dataDet['createdAt'] = date('Y-m-d H:i:s');
		$dataDet['updatedAt'] = date('Y-m-d H:i:s');
		// PARA EMPRESAS EXISTENTES
		if( !$allInputs['esnueva'] && !empty($allInputs['idempresa']) && $allInputs['idempresaadmin'] != null){
			if(!$this->model_empresa->m_validar_empresa_empresaadmin( $allInputs['empresaAdmin']['idempresa'], $allInputs['idempresa'])){
				$arrData['message'] = 'Relación Empresa Admin - Empresa ya existente.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
			$dataDet['idempresatercera'] = $allInputs['idempresa'];			
			$this->db->trans_start();
			if( $this->model_empresa->m_registrar_empresa_det($dataDet) ){
				$arrData['message'] = 'Se registraron los datos correctamente';
		    	$arrData['flag'] = 1;
			}
			
	    	$this->db->trans_complete();
	    	$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
		}
		// SI ES EMPRESA NUEVA SE TIENE QUE VALIDAR ANTES DE REGISTRAR
		
    	$filtro = array(
			'search' => $allInputs['empresa'],
			'nameColumn' => 'descripcion',
    	);
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual nombre.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$filtro['search'] = $allInputs['ruc_empresa'];
		$filtro['nameColumn'] = 'ruc_empresa';
		$rowEmpresa = $this->model_empresa->m_cargar_empresa_por_columna($filtro);
		if(!empty($rowEmpresa['idempresa'])){
    		$arrData['message'] = 'Existe empresa con igual RUC.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		$this->db->trans_start();
		if($this->model_empresa->m_registrar($data)){
			if($allInputs['idempresaadmin'] != null){
				$idempresa = GetLastId('idempresa','empresa');
				$dataDet['idempresatercera'] = $idempresa;			
				$this->model_empresa->m_registrar_empresa_det($dataDet);
				$arrData['message'] = 'Se registraron los datos correctamente';
    			$arrData['flag'] = 1; 
			} 						  		
		}			

		$this->db->trans_complete();	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cambiar_estado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo editar los datos';
    	$arrData['flag'] = 0;
    	//print_r($allInputs);
    	if($allInputs['nuevo_estado']<>1){
    		if($this->model_empresa->m_es_empresaadmin($allInputs)){
				$arrData['message'] = 'No puede deshabilitar/Anular Empresa con Empresas EMA Habilitadas.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
    	}
    	$this->db->trans_start();
    	if( $this->model_empresa->m_cambiar_estado($allInputs) ){
			if($this->model_empresa->m_cambiar_estado_relaciones_empresa_det($allInputs)){
				$arrData['message'] = 'Se editaron los datos correctamente';
    			$arrData['flag'] = 1;
			}			
		}
    	$this->db->trans_complete();		
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cambiar_estado_empresa_det(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	// if($allInputs['nuevo_estado']==0){
    	if($allInputs['nuevo_estado']<>1){
    		if($this->model_empresa->m_empresa_tiene_especialidades($allInputs)){
				$arrData['message'] = 'No puede deshabilitar/Anular Empresa con Especialidades Habilitadas.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
    	}

		if( $this->model_empresa->m_cambiar_estado_empresa_det($allInputs) ){
			switch ($allInputs['nuevo_estado']) {
				case '1': $arrData['message'] = 'Se habilitó correctamente'; break;
				case '2': $arrData['message'] = 'Se deshabilitó correctamente'; break;
				case '0': $arrData['message'] = 'Se anuló correctamente'; break;
				default:  $arrData['message'] = ''; break;
			}
			// $arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_gestion_contratos(){
		$this->load->view('empresa/popupContratos_formView');
	}	

	public function ver_popup_subir_contratos(){
		$this->load->view('empresa/popupSubirArchivosContratos_formView');
	}

	public function ver_popup_Agregar_Adenda(){
		$this->load->view('empresa/popupAgregarAdenda_formView');
	}	

	public function validacion_empresa_admin(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if($this->model_empresa->m_es_empresaadmin($allInputs) && !$allInputs['es_empresa_admin']){
    		$arrData['flag'] = 0;
    		$arrData['message'] = 'No puede cambiar tipo de empresa si tiene EMAs Habilitadas.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
	}
}