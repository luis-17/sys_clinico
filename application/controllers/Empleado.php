<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empleado extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper','otros_helper','fechas_helper'));
		$this->load->model(array('model_empleado','model_pariente', 'model_nivel_estudios', 'model_area_empresa','model_historial_contrato'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_empleados()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_empleado->m_cargar_empleados($paramPaginate,$paramDatos);
		$totalRows = $this->model_empleado->m_count_empleados($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $key => $row) {
			$estado = null;
			$clase = null;
			if( $row['si_activo'] == 1 ){
				$estado = 'ACTIVO';
				$clase = 'label-success';
			}
			if( $row['si_activo'] == 2 ){
				$estado = 'CESADO';
				$clase = 'label-warning';
			}
			$extensionFile = strtolower(pathinfo($row['nombre_archivo'], PATHINFO_EXTENSION)); 
			if($extensionFile == 'doc' || $extensionFile == 'docx' ){
				$strIcono = 'word-icon.png'; 
			}elseif($extensionFile == 'xls' || $extensionFile == 'xlsx' ){
				$strIcono = 'excel-icon.png'; 
			}elseif($extensionFile == 'jpg' || $extensionFile == 'png' || $extensionFile == 'jpeg' ){
				$strIcono = 'imagen-icon.png'; 
			}elseif($extensionFile == 'pdf' ){
				$strIcono = 'pdf-icon.png'; 
			}else{
				$strIcono = 'other-icon.png'; 
			}
			$hayArchivo = TRUE;
			if( empty($row['nombre_archivo']) ){
				$hayArchivo = FALSE;
			}
			//VALIDAMOS LA EXTENSIÓN QUE TIENE EL ARCHIVO
			$extensionCV = strtolower(pathinfo($row['nombre_cv'], PATHINFO_EXTENSION));

			if($extensionCV == 'doc' || $extensionCV == 'docx' ){
				$cvIcono = 'word-icon.png';
			} elseif($extensionCV == 'pdf' ){
				$cvIcono = 'pdf-icon.png'; 
			} else{
				$cvIcono = 'other-icon.png'; 
			}

			$hayCV = TRUE;
			if( empty($row['nombre_cv']) ){
				$hayCV = FALSE;
			}
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'idtipodocumentorh' => $row['idtipodocumentorh'],
					'tipo_documento' => $row['descripcion_rtd'],
					'num_documento' => $row['numero_documento'],
					'personal'=> strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'carnet_extranjeria' => $row['carnet_extranjeria'],
					'ruc' => $row['ruc_empleado'],
					'codigo_essalud' => $row['codigo_essalud'],
					'centro_essalud' => $row['centro_essalud'],
					'grupo_sanguineo' => $row['grupo_sanguineo'],
					'fecha_nacimiento' => darFormatoDMY($row['fecha_nacimiento']),
					'telefono' => @$row['telefono'],
					'operador_movil' => @$row['operador_movil'],
					'telefono_fijo' => @$row['telefono_fijo'],
					'email' => @$row['correo_electronico'],
					'sexo' => @$row['sexo'],
					'direccion' => @$row['direccion'],
					'iddepartamento' => @$row['iddepartamento'],
					'departamento' => @$row['departamento'],
					'idprovincia' => @$row['idprovincia'],
					'provincia' => @$row['provincia'],
					'iddistrito' => @$row['iddistrito'],
					'distrito' => @$row['distrito'],
					'referencia' => $row['referencia'],
					'estado_civil' => (int)$row['estado_civil'],
					'nombre_foto' => $row['nombre_foto'],
					'idcargo' => $row['idcargo'],
					'cargo' => $row['descripcion_ca'],
					'idcargosup' => $row['idcargosuperior'],
					'cargo_sup' => $row['cargo_superior'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'idsede' => $row['idsedeempleado'],
					'sede' => $row['sede'],
					'idcentrocosto' => $row['idcentrocosto'],
					'idsubcatcentrocosto' => $row['idsubcatcentrocosto'],
					'idespecialidad' => $row['idespecialidad'],
					'soloEspecialidad' => $row['especialidad'],
					'idusuario' => $row['idusers'],
					'usuario' => $row['username'],
					'idmedico' => $row['idmedico'],
					'colegiatura_profesional' => $row['colegiatura_profesional'], // SOLO MEDICO 
					'colegiatura_profesional_emp' => $row['colegiatura_profesional_emp'], // EMPLEADO NO MEDICO 
					// 'registro_nacional_especialista' => $row['reg_nac_especialista'],
					'personalSalud' => ($row['es_personal_salud'] == 1 ? true : false ),
					'personalFarmacia' => ($row['es_personal_farmacia'] == 1 ? true : false ), 
					'personalAdministrativo' => ($row['es_personal_administrativo'] == 1 ? true : false ), 
					'tercero_propio' => ($row['es_tercero'] == 1 ? true : false ),
					'si_asistencia' => ($row['marca_asistencia'] == 1 ? true : false ),
					'personalIPRESS' => ($row['es_ipress'] == 1 ? true : false ),
					'personalPrivado' => ($row['es_privado'] == 1 ? true : false ),
					'idalmacenfarmacia' => ($row['idalmacenfarmacia'] == null ? 0 : $row['idalmacenfarmacia']),
					'idsubalmacenfarmacia' => ($row['idsubalmacenfarmacia'] == null ? 0 : $row['idsubalmacenfarmacia']),
					'nombres_cy' => $row['nombres_cy'],
					'apellido_paterno_cy' => $row['apellido_paterno_cy'],
					'apellido_materno_cy' => $row['apellido_materno_cy'],
					'fecha_nacimiento_cy' => darFormatoDMY($row['fecha_nacimiento_cy']),
					'lugar_labores_cy' => $row['lugar_labores_cy'],
					'reg_pensionario' => $row['reg_pensionario'],
					'comision_afp' => array( 
						'id'=> (empty($row['tipo_comision']) ? 'NONE' : $row['tipo_comision'] ),
						'descripcion'=> NULL,
					),
					'afp' => array( 
						'id'=> $row['idafp'],
						'descripcion'=> $row['descripcion_afp'],
					),
					'area_empresa' => array( 
						'id'=> $row['idareaempresa'],
						'descripcion'=> $row['area'],
					),
					'profesion' => array( 
						'id'=> $row['idprofesion'],
						'descripcion'=> strtoupper($row['descripcion_prf']),
					),
					'idprofesion' => $row['idprofesion'],
					'condicion_laboral' => $row['condicion_laboral'],
					'fecha_ingreso' => empty($row['fecha_ingreso']) || $row['fecha_ingreso'] == 'null' ? null:darFormatoDMY($row['fecha_ingreso']),
					'fecha_inicio_contrato' => empty($row['fecha_inicio_contrato']) || $row['fecha_inicio_contrato'] == 'null' ? null:darFormatoDMY($row['fecha_inicio_contrato']), 
					'fecha_fin_contrato' => empty($row['fecha_fin_contrato']) || $row['fecha_fin_contrato'] == 'null' ? null:darFormatoDMY($row['fecha_fin_contrato']), // darFormatoDMY($row['fecha_fin_contrato']),
					'cuspp' => $row['cuspp'],
					'fecha_afiliacion' => darFormatoDMY($row['fecha_afiliacion']), 
					'fecha_caducidad_coleg' => darFormatoDMY($row['fecha_caducidad_coleg']),
					'documento_afiliacion' => ($row['documento_afiliacion'] == 'null' ? NULL : $row['documento_afiliacion']),  
					'salario_basico' => $row['salario_basico'],
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono,
						'hay_archivo'=> $hayArchivo
					),
					'nombre_cv' => array(
						'documento' => $row['nombre_cv'],
						'icono' => $cvIcono,
						'hay_cv' => $hayCV
					),
					'estado_activo' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['si_activo']
					),
					'jefe' => array( 
						'id'=> $row['idempleadojefe'],
						'descripcion'=> strtoupper($row['jefe_inmediato']),
					),
					'idempleadojefe' => $row['idempleadojefe'],
					'jefe_inmediato' => $row['jefe_inmediato'],
					'idcategoriapersonalsalud' => $row['idcategoriapersonalsalud'],
					'idbanco' => $row['idbanco'],
					'cuenta_corriente' => $row['cuenta_corriente'],
				)
			);
		}
		// var_dump("<pre>",$arrListado[4]['fecha_ingreso'],$lista[4]['fecha_ingreso']); exit();
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
	public function lista_empleados_por_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$empresa = FALSE;
		if( !empty($allInputs['empresa']) ){
			$empresa = TRUE;
		}
		$lista = $this->model_empleado->m_cargar_empleado_todos_autocomplete($allInputs,$empresa);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'descripcion' => $row['empleado']
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
	public function lista_empleados_cumpleaneros()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		if( $allInputs['mes'] == date('m') ){
			$listaDia = $this->model_empleado->m_cargar_empleados_cumpleaneros_dia();
		}else{
			$listaDia = [];	
		}
		
		$listaMes = $this->model_empleado->m_cargar_empleados_cumpleaneros_mes($allInputs['mes']);
		$arrListado = array();
		foreach ($listaDia as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'empleado' => $row['empleado'],
					'fecha_cumpleanos' => darFechaCumple($row['fecha_nacimiento']),
					'cargo' => $row['cargo'],
					'empresa' => $row['empresa'],
					'nombre_foto' => $row['nombre_foto'],
					'estilo'=> 'background-image: url("assets/img/hb_trans.png"); background-size: 46% auto; background-position: center -15px; background-repeat: no-repeat; background-color: #263238;',
					'tipo'=> 'dia',
					'clase'=> 'info-tile-altg'
				)
			);
		} 
		foreach ($listaMes as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'empleado' => $row['empleado'],
					'fecha_cumpleanos' => darFechaCumple($row['fecha_nacimiento']),
					'cargo' => $row['cargo'],
					'empresa' => $row['empresa'],
					'nombre_foto' => $row['nombre_foto'],
					'estilo'=> 'background-color: #263238;',
					'tipo'=> 'mes',
					'clase'=> 'info-tile-altg'
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
	public function lista_empleados_telefono() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empleado->m_cargar_empleados_telefono();
		$arrListado = array();
		foreach ($lista as $row) { 
			if( empty($row['telefono']) ){ 
				$row['telefono'] = '[sin número]';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'empleado' => $row['empleado'],
					'telefono' => $row['telefono'],
					'cargo' => $row['cargo'],
					'empresa' => $row['empresa'],
					'nombre_foto' => $row['nombre_foto'],
					'empleado_celular'=> $row['empleado'].' '.$row['telefono']
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
	public function lista_empleados_salud()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_empleado->m_cargar_empleados_salud($paramPaginate,$paramDatos);
		$totalRows = $this->model_empleado->m_count_empleados_salud($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmedico'],
					'idempleado' => $row['idempleado'],
					'num_documento' => $row['numero_documento'],
					'nombre' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'personal_salud' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'telefono' => $row['telefono'],
					'email' => $row['correo_electronico'],
					'nombre_foto' => $row['nombre_foto'],
					'idusuario' => $row['idusers'],
					'usuario' => $row['username'],
					'idmedico' => $row['idmedico'],
					'colegiatura' => $row['colegiatura_profesional'],
					// 'rne' => $row['reg_nac_especialista'], 
					'fecha_caducidad_coleg' => $row['fecha_caducidad_coleg']
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
	public function lista_empleados_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$empresa = FALSE;
		if( !empty($allInputs['empresa']) ){
			$empresa = TRUE;
		}
		$lista = $this->model_empleado->m_cargar_empleado_cbo($allInputs,$empresa);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempleado'],
					'descripcion' => $row['empleado']
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
    public function lista_empleados_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_empleado->m_cargar_este_empleado_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idempleado']);
			$fArray['descripcion'] = strtoupper($fArray['empleado']);
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades_personal_salud()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_empleado->m_cargar_especialidades_del_medico($allInputs);
		$arrListado = array();
		$objEstado = array(); 
		foreach ($lista as $row) {
			/*if( $row['estado_emme'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'ANULADO';
			}else*/
			if( $row['estado_emme'] == 1 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'HABILITADO';
			}elseif( $row['estado_emme'] == 2 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-power-off';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'DESHABILITADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idempresamedico'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					//'idsede' => $row['idsede'],
					//'sede' => $row['sede'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'rne' => $row['reg_nacional_esp'],
					'situacion' => $row['idsituacionacademica'],
					'idmedicoespecialidad' => $row['idmedicoespecialidad'],
					'estado' => $objEstado 
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
	public function lista_medicos_empresa_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$paramDatos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_empleado->m_cargar_medicos_de_empresa_especialidad($paramDatos,$paramPaginate);
		$totalRows = $this->model_empleado->m_count_medicos_de_empresa_especialidad($paramDatos,$paramPaginate);

		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_emme'] == 1 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'HABILITADO';
			}elseif( $row['estado_emme'] == 2 ){ // ACTIVO 
				$objEstado['claseIcon'] = 'fa-power-off';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'DESHABILITADO';
			}
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'id' => $row['idempresamedico'],
					'idespecialidad' => $row['idespecialidad'],
					'rne' => $row['rne'],
					'colegiatura' => $row['colegiatura_profesional'],
					'situacion' => $row['idsituacionacademica'],
					'idempresamedico' => $row['idempresamedico'],
					'estado' => $objEstado,
					'numero_documento' => $row['numero_documento'],
					'categoria_ps' => $row['idcategoriapersonalsalud'],
					'idmedicoespecialidad' => $row['idmedicoespecialidad'],
					'estado_emme' => $row['estado_emme'],

				)
			);
		}
		// var_dump($lista); exit();
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
	public function lista_medicos_empresa_especialidad_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_empleado->m_cargar_medicos_de_empresa_especialidad_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico']
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
	public function lista_medicos_empresa_especialidad_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_empleado->m_cargar_medicos_de_empresa_especialidad_cbo($allInputs, $allInputs['search'] );
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'descripcion' => $row['medico']
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
	public function lista_medicos_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_empleado->m_cargar_medicos_de_especialidad($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico']
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
	public function lista_medico_no_agreg_empresa_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_empleado->m_cargar_medico_no_agreg_empresa_autocomplete($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'descripcion' => $row['medico']
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
	public function lista_medicos_atencion_todos_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_empleado->m_cargar_medicos_atencion_todos_autocomplete($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico']
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
	public function lista_empleados_salud_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_empleado->m_cargar_empleado_salud_cbo($allInputs);
		}else{
			$lista = $this->model_empleado->m_cargar_empleado_salud_cbo();
		}

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmedico'],
					'idmedico' => $row['idmedico'],
					'descripcion' => $row['medico'],
					'medico_externo' => $row['medico_externo']
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
	public function lista_especialidades_no_agregados_a_medico()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$listaEspecialidadesNoAgregados = $this->model_empleado->m_cargar_especialidades_no_agregados_a_medico($paramPaginate,$datos);
		$totalRows = $this->model_empleado->m_count_especialidades_no_agregados_a_medico($paramPaginate,$datos);
		$arrListado = array();
		foreach ($listaEspecialidadesNoAgregados as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idempresaespecialidad'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					// 'idsede' => $row['idsede'],
					// 'sede' => $row['sede'],
					'idempresadetalle' => $row['idempresadetalle'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa']
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
	public function ver_popup_formulario()
	{
		$this->load->view('empleado/empleado_formView');
	}
	public function ver_popup_agregar_especialidad() 
	{
		$this->load->view('empleado-salud/popupAgregarEspecialidadView');
	}
	public function ver_popup_consultar_especialidad()
	{
		$this->load->view('empleado-salud/popupConsultarEspecialidadView');
	}
	public function ver_popup_dar_baja()
	{
		$this->load->view('empleado/popupDarBaja_view');
	}
	public function registrar()
	{ 
		$objectContratos = json_decode($this->input->post('contratos'));
		$objectParientes = json_decode($this->input->post('parientes'));
		$objectEstudios = json_decode($this->input->post('estudios'));
		$arrParientes = array();
		$arrEstudios = array();
		$arrContratos = array();

		foreach ($objectParientes as $key => $row) {
			$arrParientes[] = get_object_vars($row);
		}
		foreach ($arrParientes as $key => $row) {
			$arrParientes[$key]['estado_civil_obj'] = get_object_vars($row['estado_civil_obj']);
			$arrParientes[$key]['vive_obj'] = get_object_vars($row['vive_obj']);
		}
		foreach ($objectEstudios as $key => $row) {
			$arrEstudios[] = get_object_vars($row);
		}
		foreach ($objectContratos as $key => $row) {
			$arrContratos[] = get_object_vars($row);
		}

		$contratosValidate = FALSE;
		if(count($arrContratos)>0){ 
			$contratosValidate = TRUE;
			// var_dump($arrContratos); exit();
			foreach ($arrContratos as $key => $row) { 
				$arrContratos[$key]['empresa_obj'] = get_object_vars($row['empresa_obj']);
				$arrContratos[$key]['cargo_obj'] = get_object_vars($row['cargo_obj']);
				$arrContratos[$key]['condicion_laboral_obj'] = get_object_vars($row['condicion_laboral_obj']); 
				if( empty($arrContratos[$key]['empresa_obj']['id']) || empty($arrContratos[$key]['idcargo']) || empty($arrContratos[$key]['fecha_ini_contrato']) 
					|| empty($arrContratos[$key]['fecha_ingreso']) || empty($arrContratos[$key]['fecha_ini_contrato']) ){ 
					$contratosValidate = FALSE;
				}
			}
		}
		if( $contratosValidate === TRUE ){
			$arrData['message'] = 'Se encontró campos vacios que tienen que llenarse obligatoriamente. Revise y vuelva a guardar.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		// var_dump($arrContratos); exit();
		$fecha_nacimiento = $this->input->post('fecha_nacimiento');
		if($fecha_nacimiento === 'undefined'){ 
			$fecha_nacimiento = null;
		}
		
		$num_documento = $this->input->post('num_documento');
		$inputTipoDocumento = $this->input->post('tipoDocumento');
	   	$allInputs['tipo_documento'] = get_object_vars(json_decode($inputTipoDocumento));

		if(!empty($num_documento)){
	    	/* VALIDAR SI EL DNI YA EXISTE */
	    	$rows = $this->model_empleado->m_verificar_si_existe_empleado_por_numero_documento($num_documento);
	    	if( $rows > 0 ) {
	    		$arrData['message'] = 'El DNI ingresado, ya existe.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}
	   	if(empty($num_documento)){
	   		$arrData['message'] = 'Se encontró campos vacios que tienen que llenarse obligatoriamente. Revise y vuelva a guardar.';
			$arrData['flag'] = 0;
			$this->output
				  ->set_content_type('application/json')
				  ->set_output(json_encode($arrData));
				return;
	   	} else if((strlen($num_documento) != $allInputs['tipo_documento']['longitud']) || !ctype_digit($num_documento) ){
	   		$arrData['message'] = 'Ingrese un Número de Documento válido';
			$arrData['flag'] = 0;
			$this->output
				  ->set_content_type('application/json')
				  ->set_output(json_encode($arrData));
				return;
	   	}
	   	//PROFESION
	   	$idprofesion = $this->input->post('idprofesion');
	   	if(empty($idprofesion)){
	   		//VALIDAMOS SI EL CAMPO ESTÁ VACIO
	   		$arrData['message'] = 'Se encontró campos vacios que tienen que llenarse obligatoriamente. Revise y vuelva a guardar.';
			$arrData['flag'] = 0;
			$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   	}
	   	// CENTRO DE COSTO --- COMENTADO HASTA NUEVO AVISO
		  //  	$idcentrocosto = $this->input->post('idcentrocosto');
		  //  	if(empty($idcentrocosto)){
		  //  		//VALIDAMOS SI EL CAMPO ESTÁ VACIO
		  //  		$arrData['message'] = 'El Centro de Costo es un campo obligatorio.';
				// $arrData['flag'] = 0;
				// $this->output
				// 	    ->set_content_type('application/json')
				// 	    ->set_output(json_encode($arrData));
				// 	return;
		  //  	}

	   	$inputTipoDocumento = $this->input->post('tipoDocumento');
	   	$allInputs['tipo_documento'] = get_object_vars(json_decode($inputTipoDocumento));
		$allInputs['num_documento'] = $this->input->post('num_documento');
		$allInputs['nombre'] = $this->input->post('nombres');
		$allInputs['apellido_paterno'] = $this->input->post('apellido_paterno');
		$allInputs['apellido_materno'] = $this->input->post('apellido_materno');
		$allInputs['direccion'] = $this->input->post('direccion');
		$allInputs['iddepartamento'] = $this->input->post('iddepartamento');
		$allInputs['idprovincia'] = $this->input->post('idprovincia');
		$allInputs['iddistrito'] = $this->input->post('iddistrito');
		$allInputs['telefono'] = $this->input->post('telefono');
		$allInputs['operador_movil'] = $this->input->post('operador_movil');
		$allInputs['telefono_fijo'] = $this->input->post('telefono_fijo');
		$allInputs['sexo'] = ($this->input->post('sexo') == 'null' ? NULL : $this->input->post('sexo') );
		$allInputs['email'] = $this->input->post('email');
		$allInputs['fecha_nacimiento'] = $fecha_nacimiento;
		$allInputs['idcargo'] = $this->input->post('idcargo'); 
		$allInputs['idprofesion'] = $this->input->post('idprofesion'); 
		$allInputs['idusuario'] = $this->input->post('idusuario'); 
		// $allInputs['registro_nacional_especialista'] = $this->input->post('registro_nacional_especialista'); 
		$allInputs['colegiatura_profesional'] = $this->input->post('colegiatura_profesional') == 'null' ? NULL : $this->input->post('colegiatura_profesional');
		$allInputs['colegiatura_profesional_emp'] = $this->input->post('colegiatura_profesional_emp') == 'null' ? NULL : $this->input->post('colegiatura_profesional_emp');
		$allInputs['id'] = $this->input->post('idempresaespecialidad');
		$allInputs['personalSalud'] = ($this->input->post('personalSalud') === 'true' ? 1 : 2);
		$allInputs['personalFarmacia'] = ($this->input->post('personalFarmacia') === 'true' ? 1 : 2);
		$allInputs['personalAdministrativo'] = ($this->input->post('personalAdministrativo') === 'true' ? 1 : 2);

		$allInputs['codigo_essalud'] = $this->input->post('codigo_essalud');
		$allInputs['carnet_extranjeria'] = $this->input->post('carnet_extranjeria');
		$allInputs['referencia'] = $this->input->post('referencia');
		$allInputs['estado_civil'] = $this->input->post('estado_civil'); 
		$allInputs['grupo_sanguineo'] = $this->input->post('grupo_sanguineo');
		$allInputs['ruc_empleado'] = $this->input->post('ruc'); 
		$allInputs['centro_essalud'] = $this->input->post('centro_essalud');
		$allInputs['nombres_cy'] = $this->input->post('nombres_cy');
		$allInputs['apellido_paterno_cy'] = $this->input->post('apellido_paterno_cy');
		$allInputs['apellido_materno_cy'] = $this->input->post('apellido_materno_cy');
		$allInputs['fecha_nacimiento_cy'] = $this->input->post('fecha_nacimiento_cy');
		$allInputs['lugar_labores_cy'] = $this->input->post('lugar_labores_cy');
		$allInputs['reg_pensionario'] = $this->input->post('reg_pensionario');
		$allInputs['salario_basico'] = ($this->input->post('salario_basico') == 'null' ? NULL : $this->input->post('salario_basico'));
		$allInputs['fecha_caducidad_coleg'] = ($this->input->post('fecha_caducidad_coleg') == 'null' ? NULL : $this->input->post('fecha_caducidad_coleg'));
		$allInputs['afp'] = array();
		$inputAFP = $this->input->post('afp');
		if( !empty($inputAFP) ){ 
			$allInputs['afp'] = get_object_vars(json_decode($inputAFP));
		}
		$allInputs['comision_afp'] = array();
		$inputComisionAFP = $this->input->post('comision_afp');
		if( !empty($inputComisionAFP) ){ 
			$allInputs['comision_afp'] = get_object_vars(json_decode($inputComisionAFP));
		}
		$inputAreaEmpresa = $this->input->post('area_empresa');
		if( !empty($inputAreaEmpresa) ){ 
			$allInputs['area_empresa'] = get_object_vars(json_decode($inputAreaEmpresa));
		}
		$inputBanco = $this->input->post('banco');
		if( !empty($inputBanco) ){ 
			$allInputs['banco'] = get_object_vars(json_decode($inputBanco));
		}
		$allInputs['cuenta_corriente'] = ($this->input->post('cuenta_corriente') == 'null' ? NULL : $this->input->post('cuenta_corriente')); 
		$allInputs['condicion_laboral'] = $this->input->post('condicion_laboral');
		$allInputs['fecha_ingreso'] = $this->input->post('fecha_ingreso');
		$allInputs['fecha_inicio_contrato'] = $this->input->post('fecha_inicio_contrato');
		$allInputs['fecha_fin_contrato'] = $this->input->post('fecha_fin_contrato');
		$allInputs['cuspp'] = $this->input->post('cuspp');
		$allInputs['fecha_afiliacion'] = $this->input->post('fecha_afiliacion');
		$allInputs['documento_afiliacion'] = $this->input->post('documento_afiliacion');
		$allInputs['es_tercero'] = ($this->input->post('tercero_propio') === 'true' ? 1 : 2);
		$allInputs['marca_asistencia'] = ($this->input->post('si_asistencia') === 'true' ? 1 : 2);
		$allInputs['es_privado'] = ($this->input->post('personalPrivado') === 'true' ? 1 : 2);
		$allInputs['es_ipress'] = ($this->input->post('personalIPRESS') === 'true' ? 1 : 2);
		if($this->input->post('personalFarmacia')){
			$allInputs['idalmacenfarmacia'] = $this->input->post('idalmacenfarmacia');
			$allInputs['idsubalmacenfarmacia'] = $this->input->post('idsubalmacenfarmacia');
		}else{
			$allInputs['idalmacenfarmacia'] = NULL;
			$allInputs['idsubalmacenfarmacia'] = NULL;
		}
		$allInputs['idsedeempleado'] = @$this->input->post('idsede'); 
		$allInputs['idempresa'] = @$this->input->post('idempresa'); 
		$allInputs['idespecialidad'] = @$this->input->post('idespecialidad'); 
		$allInputs['idcentrocosto'] = $this->input->post('idcentrocosto');
		// var_dump($allInputs['idcargo']); exit();
	   	if( empty($allInputs['nombre']) || empty($allInputs['apellido_materno']) || empty($allInputs['apellido_paterno']) || empty($allInputs['idcargo'])|| $allInputs['nombre'] == 'undefined' || $allInputs['apellido_materno'] == 'undefined' || $allInputs['apellido_paterno'] == 'undefined' || $allInputs['idcargo'] == 'undefined' || $allInputs['nombre'] == 'null' || $allInputs['apellido_materno'] == 'null' || $allInputs['apellido_paterno'] == 'null' || $allInputs['idcargo'] == 'null' || $allInputs['idsedeempleado'] == 'null'){ 
	    	/* VALIDAR CAMPOS OBLIGATORIOS */
    		$arrData['message'] = 'Llenar los campos marcados como obligatorios.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
	   	}

	   	if($allInputs['condicion_laboral'] != NULL && $allInputs['condicion_laboral'] == 'EN PLANILLA'){
	   		if($allInputs['reg_pensionario'] == 'NONE'){
	   			$arrData['message'] = 'Llenar los campos marcados como obligatorios.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}

	   	if($allInputs['reg_pensionario'] != NULL && $allInputs['reg_pensionario'] == 'AFP'){
	   		if(empty($allInputs['cuspp']) || empty($allInputs['afp']['id']) || empty($allInputs['comision_afp']['id']) || $allInputs['cuspp'] == 'null' || $allInputs['afp']['id'] == 'all' || $allInputs['comision_afp']['id'] == 'NONE'){
	   			$arrData['message'] = 'Llenar los campos de AFP marcados como obligatorios.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}
	   	
	   	//categoria personal salud m_registrar_medico
	   	// $inputCategoriaPerSalud = $this->input->post('categoriaPersonalSalud');
	   	// $allInputs['idcategoriapersonalsalud'] = get_object_vars(json_decode($inputCategoriaPerSalud))['idcategoriapersonalsalud'];

	   	// var_dump($allInputs); exit();
	   // 	if( $allInputs['personalSalud'] == 1 ){ 
	   // 		if( empty($allInputs['id']) || $allInputs['id'] == 'undefined' ||  $allInputs['id'] == 'null' ) {
	   // 			/* VALIDAR CAMPOS OBLIGATORIOS */
	   //  		$arrData['message'] = 'Agregar Especialidad.';
				// $arrData['flag'] = 0;
				// $this->output
				//     ->set_content_type('application/json')
				//     ->set_output(json_encode($arrData));
				// return;
	   // 		}

	   // 		if( empty($allInputs['idcategoriapersonalsalud']) || $allInputs['idcategoriapersonalsalud'] == 'undefined' ||  
	   // 			$allInputs['idcategoriapersonalsalud'] == 'null' || $allInputs['idcategoriapersonalsalud'] == 'NONE') {
	   // 			/* VALIDAR CAMPOS OBLIGATORIOS */
	   //  		$arrData['message'] = 'Agregar Categoría de Personal de Salud.';
				// $arrData['flag'] = 0;
				// $this->output
				//     ->set_content_type('application/json')
				//     ->set_output(json_encode($arrData));
				// return;
	   // 		}
	   // 	}
	   	$allInputs['idempleadojefe'] = ( $this->input->post('idempleadojefe') == 'null' ? NULL : $this->input->post('idempleadojefe') );
	   	$allInputs['idcargosup'] = ( $this->input->post('idcargosup') == 'null' ? NULL : $this->input->post('idcargosup') );

	   	$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0;
    	$this->db->trans_start(); 
    	if( empty($_FILES) ){ 
			$allInputs['nombre_foto'] = 'noimage.jpg'; 
			if($this->model_empleado->m_registrar($allInputs)){ // registro de empleado 
				$allInputs['idempleado'] = GetLastId('idempleado','rh_empleado');
				if( $this->input->post('personalSalud') === 'true' ){ 
					$this->model_empleado->m_registrar_medico($allInputs);
					// $allInputs['idmedico'] = GetLastId('idmedico','medico');
					// $arrayEmpresaEspecialidad = $this->model_empleado->m_get_empresa_especialidad($allInputs['id']);
					
					// $allInputs['idempresa'] = $arrayEmpresaEspecialidad['idempresa'];
					// $allInputs['idespecialidad'] = $arrayEmpresaEspecialidad['idespecialidad'];
					// $allInputs['idsede'] = $arrayEmpresaEspecialidad['idsede'];
					// $this->model_empleado->m_agregar_especialidad_medico($allInputs);
					/*  */
					// $allInputs['rne'] = null;
		   //  		$allInputs['situacion'] = null;
		   //  		$boolMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($allInputs);
		   //  		if( !$boolMedicoEspecialidad ){
		   //  			if($this->model_empleado->m_agregar_situacion_rne_especialidad($allInputs)){ 
					// 		$arrData['message'] = 'Se registraron los datos correctamente';
				 //    		$arrData['flag'] = 1;
					// 	} 
		   //  		}
		    		/* */
				}
				$arrData['message'] = 'Se registraron los datos correctamente.'; 
    			$arrData['flag'] = 1; 
			}
		}else{
			$allInputs['extension'] = pathinfo($_FILES['fotoEmpleado']['name'], PATHINFO_EXTENSION);
    		$allInputs['nuevoNombreArchivo'] = $allInputs['num_documento'].'.'.$allInputs['extension'];
			if( subir_fichero('assets/img/dinamic/empleado','fotoEmpleado',$allInputs['nuevoNombreArchivo']) ){ 
				//$allInputs['nombre_foto'] = $_FILES['fotoEmpleado']['name']; // anterior
				$allInputs['nombre_foto'] = $allInputs['nuevoNombreArchivo'];				
				if($this->model_empleado->m_registrar($allInputs)){ 
					$allInputs['idempleado'] = GetLastId('idempleado','rh_empleado');
					if( $this->input->post('personalSalud') === 'true' ){ 
						$this->model_empleado->m_registrar_medico($allInputs); 
						// $allInputs['idmedico'] = GetLastId('idmedico','medico');
						// $arrayEmpresaEspecialidad = $this->model_empleado->m_get_empresa_especialidad($allInputs['id']);
						
						// $allInputs['idempresa'] = $arrayEmpresaEspecialidad['idempresa'];
						// $allInputs['idespecialidad'] = $arrayEmpresaEspecialidad['idespecialidad'];
						// $allInputs['idsede'] = $arrayEmpresaEspecialidad['idsede'];
						// $this->model_empleado->m_agregar_especialidad_medico($allInputs);
						// /*  */
						// $allInputs['rne'] = null;
			   //  		$allInputs['situacion'] = null;
			   //  		$boolMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($allInputs);
			   //  		if( !$boolMedicoEspecialidad ){
			   //  			if($this->model_empleado->m_agregar_situacion_rne_especialidad($allInputs)){ 
						// 		$arrData['message'] = 'Se registraron los datos correctamente';
					 //    		$arrData['flag'] = 1;
						// 	} 
			   //  		}
			    		/* */
					}
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1;
	    			
				}
			}
		}
		// PARIENTES 
		if( !empty($arrParientes) && !empty($allInputs['idempleado']) ){ 
			foreach ($arrParientes as $key => $row) { 
				$row['idempleado'] = $allInputs['idempleado'];
				$this->model_pariente->m_agregar_pariente($row);
			}
		}
		// ESTUDIOS 
		if( !empty($arrEstudios) && !empty($allInputs['idempleado']) ){ 
			foreach ($arrEstudios as $key => $row) { 
				$row['idempleado'] = $allInputs['idempleado'];
				$this->model_nivel_estudios->m_agregar_estudio_a_empleado($row);
			}
		}
		// CONTRATOS 
		if( !empty($arrContratos) && !empty($allInputs['idempleado']) ){ 
			foreach ($arrContratos as $key => $row) { 
				$row['idempleado'] = $allInputs['idempleado']; 
				$this->model_historial_contrato->m_registrar($row); 
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{	
		//var_dump($_POST); exit();
		$objectParientes = json_decode($this->input->post('parientes'));
		$objectEstudios = json_decode($this->input->post('estudios'));
		$objectBanco = json_decode($this->input->post('banco'));
		$arrParientes = array();
		$arrEstudios = array();
		$parienteRepetido = FALSE;
		foreach ($objectParientes as $key => $row) { 
			$arrParientes[] = get_object_vars($row);
		}
		foreach ($arrParientes as $key => $row) { 
			if( @$row['es_temporal'] === TRUE ){
				// validacion si ya existe pariente
				if( $this->model_pariente->m_verificar_si_existe_pariente($row) ){
					$parienteRepetido = TRUE;
				}
				$arrParientes[$key]['estado_civil_obj'] = get_object_vars($row['estado_civil_obj']);
				$arrParientes[$key]['vive_obj'] = get_object_vars($row['vive_obj']);
			}
		}
		foreach ($objectEstudios as $key => $row) {
			$arrEstudios[] = get_object_vars($row);
		}
		if ( $parienteRepetido ){
			$arrData['message'] = 'Ya existe Pariente'; 
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

		//var_dump("<pre>",$arrParientes); exit();
		$fecha_nacimiento = $this->input->post('fecha_nacimiento');
		if($fecha_nacimiento === 'null'){
			$fecha_nacimiento = null;
		}
		$arrEmpleado = array();
		$arrEmpleado['num_documento'] = $this->input->post('num_documento');
		$arrEmpleado['idempleado'] = $this->input->post('id');
		if(!empty($arrEmpleado['num_documento'])){ 
	    	/* VALIDAR SI EL DNI YA EXISTE */
	    	// var_dump($arrEmpleado); exit();
	    	$rows = $this->model_empleado->m_verificar_si_existe_otro_empleado_por_numero_documento($arrEmpleado);
	    	if( $rows > 0 ) {
	    		$arrData['message'] = 'El DNI ingresado, ya existe.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}

		$allInputs['id'] = $this->input->post('id');
		$inputTipoDocumento = $this->input->post('tipoDocumento');
		$allInputs['tipo_documento'] = get_object_vars(json_decode($inputTipoDocumento));
		$allInputs['num_documento'] = ($this->input->post('num_documento') == 'null' ? NULL : $this->input->post('num_documento')); 
		$allInputs['nombre'] = $this->input->post('nombres');
		$allInputs['apellido_paterno'] = $this->input->post('apellido_paterno');
		$allInputs['apellido_materno'] = $this->input->post('apellido_materno');
		$allInputs['direccion'] = ($this->input->post('direccion') == 'null' ? NULL : $this->input->post('direccion'));
		$allInputs['salario_basico'] = ($this->input->post('salario_basico') == 'null' ? NULL : $this->input->post('salario_basico'));
		
		$allInputs['iddepartamento'] = ($this->input->post('iddepartamento') == 'null' ? NULL : $this->input->post('iddepartamento'));
		$allInputs['idprovincia'] = ($this->input->post('idprovincia') == 'null' ? NULL : $this->input->post('idprovincia'));
		$allInputs['iddistrito'] = ($this->input->post('iddistrito') == 'null' ? NULL : $this->input->post('iddistrito'));

		$allInputs['telefono'] = ($this->input->post('telefono') == 'null' ? NULL : $this->input->post('telefono'));
		$allInputs['operador_movil'] = ($this->input->post('operador_movil') == 'null' ? NULL : $this->input->post('operador_movil') );
		$allInputs['telefono_fijo'] = ($this->input->post('telefono_fijo') == 'null' ? NULL : $this->input->post('telefono_fijo') );
		$allInputs['sexo'] = ($this->input->post('sexo') == 'null' ? NULL : $this->input->post('sexo') );
		$allInputs['email'] = ($this->input->post('email') == 'null' ? NULL : $this->input->post('email'));
		$allInputs['fecha_nacimiento'] = $fecha_nacimiento;
		$allInputs['idcargo'] = ( $this->input->post('idcargo') == 'null' ? NULL : $this->input->post('idcargo') ); 
		$allInputs['idprofesion'] = ( $this->input->post('idprofesion') == 'null' ? NULL : $this->input->post('idprofesion') ); 
		$allInputs['idusuario'] = ( $this->input->post('idusuario') == 'null' ? NULL : $this->input->post('idusuario') ); 
		// var_dump($this->input->post('idusuario')); exit();
		//$allInputs['registro_nacional_especialista'] = ($this->input->post('registro_nacional_especialista') == 'null' ? NULL : $this->input->post('registro_nacional_especialista') ); 
		$allInputs['colegiatura_profesional'] = ($this->input->post('colegiatura_profesional') == 'null' ? NULL : $this->input->post('colegiatura_profesional') ); 
		$allInputs['colegiatura_profesional_emp'] = ($this->input->post('colegiatura_profesional_emp') == 'null' ? NULL : $this->input->post('colegiatura_profesional_emp') ); 
		$allInputs['personalSalud'] = ($this->input->post('personalSalud') === 'true' ? 1 : 2);
		$allInputs['personalFarmacia'] = ($this->input->post('personalFarmacia') === 'true' ? 1 : 2);
		$allInputs['personalAdministrativo'] = ($this->input->post('personalAdministrativo') === 'true' ? 1 : 2);

		$allInputs['es_tercero'] = ($this->input->post('tercero_propio') === 'true' ? 1 : 2);
		$allInputs['marca_asistencia'] = ($this->input->post('si_asistencia') === 'true' ? 1 : 2);
		$allInputs['es_privado'] = ($this->input->post('personalPrivado') === 'true' ? 1 : 2);
		$allInputs['es_ipress'] = ($this->input->post('personalIPRESS') === 'true' ? 1 : 2);
		
		$allInputs['idalmacenfarmacia'] = $this->input->post('idalmacenfarmacia');
		$allInputs['idsubalmacenfarmacia'] = $this->input->post('idsubalmacenfarmacia');
		
		$allInputs['idempresa'] = @$this->input->post('idempresa'); 
		$allInputs['idsedeempleado'] = @$this->input->post('idsede'); 
		$allInputs['idespecialidad'] = @$this->input->post('idespecialidad'); 
		$allInputs['idcentrocosto'] = $this->input->post('idcentrocosto');
		$allInputs['codigo_essalud'] = ($this->input->post('codigo_essalud') == 'null' ? NULL : $this->input->post('codigo_essalud'));
		$allInputs['carnet_extranjeria'] = ($this->input->post('carnet_extranjeria') == 'null' ? NULL : $this->input->post('carnet_extranjeria'));
		$allInputs['referencia'] = ($this->input->post('referencia') == 'null' ? NULL : $this->input->post('referencia'));
		$allInputs['estado_civil'] = ($this->input->post('estado_civil') == 'null' ? NULL : $this->input->post('estado_civil')); 
		$allInputs['grupo_sanguineo'] = ($this->input->post('grupo_sanguineo') == 'null' ? NULL : $this->input->post('grupo_sanguineo'));
		$allInputs['ruc_empleado'] = ($this->input->post('ruc') == 'null' ? NULL : $this->input->post('ruc')); 
		$allInputs['centro_essalud'] = ($this->input->post('centro_essalud') == 'null' ? NULL : $this->input->post('centro_essalud')); 
		$allInputs['nombres_cy'] = ($this->input->post('nombres_cy') == 'null' ? NULL : $this->input->post('nombres_cy'));
		$allInputs['apellido_paterno_cy'] = ($this->input->post('apellido_paterno_cy') == 'null' ? NULL : $this->input->post('apellido_paterno_cy')); 
		$allInputs['apellido_materno_cy'] = ($this->input->post('apellido_materno_cy') == 'null' ? NULL : $this->input->post('apellido_materno_cy')); 
		$allInputs['fecha_nacimiento_cy'] = ($this->input->post('fecha_nacimiento_cy') == 'null' ? NULL : $this->input->post('fecha_nacimiento_cy'));
		$allInputs['lugar_labores_cy'] = ($this->input->post('lugar_labores_cy') == 'null' ? NULL : $this->input->post('lugar_labores_cy'));
		$allInputs['reg_pensionario'] = ($this->input->post('reg_pensionario') == 'null' ? NULL : $this->input->post('reg_pensionario'));
		$allInputs['fecha_caducidad_coleg'] = ($this->input->post('fecha_caducidad_coleg') == 'null' ? NULL : $this->input->post('fecha_caducidad_coleg'));
		$allInputs['afp'] = array();
		$inputAFP = $this->input->post('afp'); // var_dump($inputAFP); exit();
		if( !empty($inputAFP) ){ 
			$allInputs['afp'] = get_object_vars(json_decode($inputAFP));
		}
		$allInputs['comision_afp'] = array();
		$inputComisionAFP = $this->input->post('comision_afp');
		if( !empty($inputComisionAFP) ){ 
			$allInputs['comision_afp'] = get_object_vars(json_decode($inputComisionAFP));
		}
		$inputAreaEmpresa = $this->input->post('area_empresa');
		if( !empty($inputAreaEmpresa) ){ 
			$allInputs['area_empresa'] = get_object_vars(json_decode($inputAreaEmpresa));
		}
		$inputBanco = $this->input->post('banco');
		if( !empty($inputBanco) ){ 
			$allInputs['banco'] = get_object_vars(json_decode($inputBanco));
		}
    	// var_dump($allInputs); exit();

		$allInputs['condicion_laboral'] = ($this->input->post('condicion_laboral') == 'null' ? NULL : $this->input->post('condicion_laboral')); 
		$allInputs['fecha_ingreso'] = ($this->input->post('fecha_ingreso') == 'null' ? NULL : $this->input->post('fecha_ingreso')); 
		$allInputs['fecha_inicio_contrato'] = ($this->input->post('fecha_inicio_contrato') == 'null' ? NULL : $this->input->post('fecha_inicio_contrato')); 
		$allInputs['fecha_fin_contrato'] = ($this->input->post('fecha_fin_contrato') == 'null' ? NULL : $this->input->post('fecha_fin_contrato')); 
		$allInputs['cuspp'] = ($this->input->post('cuspp') == 'null' ? NULL : $this->input->post('cuspp')); 
		$allInputs['fecha_afiliacion'] = ($this->input->post('fecha_afiliacion') == 'null' ? NULL : $this->input->post('fecha_afiliacion')); 
		$allInputs['documento_afiliacion'] = ($this->input->post('documento_afiliacion') == 'null' ? NULL : $this->input->post('documento_afiliacion')); 
		$allInputs['cuenta_corriente'] = ($this->input->post('cuenta_corriente') == 'null' ? NULL : $this->input->post('cuenta_corriente')); 

		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0; 
    	// var_dump($_FILES); exit();
		if( empty($allInputs['num_documento']) || empty($allInputs['idprofesion']) || empty($allInputs['nombre']) || empty($allInputs['apellido_materno']) || empty($allInputs['apellido_paterno']) || empty($allInputs['idcargo']) || 
			$allInputs['num_documento'] == 'undefined' || $allInputs['nombre'] == 'undefined' || $allInputs['apellido_materno'] == 'undefined' || $allInputs['apellido_paterno'] == 'undefined' || $allInputs['idcargo'] == 'undefined' || $allInputs['idprofesion'] == 'undefined' || 
			 $allInputs['num_documento'] == 'null' || $allInputs['nombre'] == 'null' || $allInputs['apellido_materno'] == 'null' || $allInputs['apellido_paterno'] == 'null' || $allInputs['idcargo'] == 'null' || $allInputs['idprofesion'] == 'null' || $allInputs['idsedeempleado'] == 'null'){ 
	    	/* VALIDAR CAMPOS OBLIGATORIOS */
	    		$arrData['message'] = 'Llenar los campos marcados como obligatorios.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   	}
	   	//VALIDACIÓN PARA EL CAMBIO DE TIPO DE DOCUMENTO (DNI - CARNET EXTRANJERÍA - PASAPORTE)
	   	$num_documento = $this->input->post('num_documento');
	   	if( empty($num_documento) || !ctype_digit($num_documento) ){
	   		$arrData['message'] = 'Ingrese un Número de Documento válido';
			$arrData['flag'] = 0;
			$this->output
				  ->set_content_type('application/json')
				  ->set_output(json_encode($arrData));
				return;
	   	} else if ((strlen($num_documento) != $allInputs['tipo_documento']['longitud']) || !ctype_digit($num_documento) ) {
	   		$arrData['message'] = 'Ingrese un Número de Documento válido';
			$arrData['flag'] = 0;
			$this->output
				  ->set_content_type('application/json')
				  ->set_output(json_encode($arrData));
				return;
	   	}

	   	if($allInputs['condicion_laboral'] != NULL && $allInputs['condicion_laboral'] == 'EN PLANILLA'){
	   		if($allInputs['reg_pensionario'] == 'NONE'){
	   			$arrData['message'] = 'Llenar los campos marcados como obligatorios.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}

	   	if($allInputs['reg_pensionario'] != NULL && $allInputs['reg_pensionario'] == 'AFP'){
	   		if(empty($allInputs['cuspp']) || empty($allInputs['afp']['id']) || empty($allInputs['comision_afp']['id']) || $allInputs['cuspp'] == 'null' || $allInputs['afp']['id'] == 'all' || $allInputs['comision_afp']['id'] == 'NONE'){
	   			$arrData['message'] = 'Llenar los campos de AFP marcados como obligatorios.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}

	   	//categoria personal salud
	   	// $inputCategoriaPerSalud = $this->input->post('categoriaPersonalSalud');
	   	// $allInputs['idcategoriapersonalsalud'] = get_object_vars(json_decode($inputCategoriaPerSalud))['idcategoriapersonalsalud'];

	   // 	if( $allInputs['personalSalud'] == 1 ){
	   // 		if( empty($allInputs['idcategoriapersonalsalud']) || $allInputs['idcategoriapersonalsalud'] == 'undefined' ||  
	   // 			$allInputs['idcategoriapersonalsalud'] == 'null' || $allInputs['idcategoriapersonalsalud'] == 'NONE') {
	   // 			/* VALIDAR CAMPOS OBLIGATORIOS */
	   //  		$arrData['message'] = 'Seleccionar Categoría de Personal de Salud.';
				// $arrData['flag'] = 0;
				// $this->output
				//     ->set_content_type('application/json')
				//     ->set_output(json_encode($arrData));
				// return;
	   // 		}
	   // 	}
	   	// CENTRO DE COSTO --- COMENTADO HASTA NUEVO AVISO
		  //  	$idcentrocosto = $this->input->post('idcentrocosto');
		  //  	if(empty($idcentrocosto)){
		  //  		//VALIDAMOS SI EL CAMPO ESTÁ VACIO
		  //  		$arrData['message'] = 'El Centro de Costo es un campo obligatorio.';
				// $arrData['flag'] = 0;
				// $this->output
				// 	    ->set_content_type('application/json')
				// 	    ->set_output(json_encode($arrData));
				// 	return;
		  //  	}
	   	$allInputs['idempleadojefe'] = ( $this->input->post('idempleadojefe') == 'null' ? NULL : $this->input->post('idempleadojefe') );
	   	$allInputs['idcargosup'] = ( $this->input->post('idcargosup') == 'null' ? NULL : $this->input->post('idcargosup') );
    	$this->db->trans_start();
    	if( empty($_FILES) ){
    		$allInputs['nombre_foto'] = $this->input->post('nombre_foto');
    		if($this->model_empleado->m_editar($allInputs)){ 
    			if( $this->input->post('personalSalud') === 'true' ){
    				//var_dump($this->input->post('idmedico')); exit();
    				if( $this->input->post('idmedico') === 'null' ) {
    					$allInputs['idempleado'] = $allInputs['id'];
    					$this->model_empleado->m_quitar_num_documento_medico($allInputs['num_documento']);
    					$this->model_empleado->m_registrar_medico($allInputs);
    				}else{
    					$this->model_empleado->m_editar_medico($allInputs);
    				}
				}
				$arrData['message'] = 'Se editaron los datos correctamente'; 
    			$arrData['flag'] = 1; 
			}
    	}else{
			$allInputs['extension'] = pathinfo($_FILES['fotoEmpleado']['name'], PATHINFO_EXTENSION);
    		$allInputs['nuevoNombreArchivo'] = $allInputs['num_documento'].'.'.$allInputs['extension'];    		
    		if( subir_fichero('assets/img/dinamic/empleado','fotoEmpleado',$allInputs['nuevoNombreArchivo']) ){ 
				$allInputs['nombre_foto'] = $allInputs['nuevoNombreArchivo']; 
				if($this->model_empleado->m_editar($allInputs)){ 
					if( $this->input->post('personalSalud') === 'true' ){
						if( $this->input->post('idmedico') === 'null' ) {
	    					$allInputs['idempleado'] = $allInputs['id']; 
	    					$this->model_empleado->m_quitar_num_documento_medico($allInputs['num_documento']);
	    					$this->model_empleado->m_registrar_medico($allInputs);
	    				}else{
	    					$this->model_empleado->m_editar_medico($allInputs);
	    				}
					}
					$arrData['message'] = 'Se editaron los datos correctamente'; 
	    			$arrData['flag'] = 1; 
				}
			}
    	}
    	// var_dump($arrParientes); exit();
    	if( !empty($arrParientes) && !empty($allInputs['id']) ){ 
			foreach ($arrParientes as $key => $row) { 
				if( @$row['es_temporal'] === TRUE ){ 
					$row['idempleado'] = $allInputs['id'];
					$this->model_pariente->m_agregar_pariente($row);
				}
			}
		}
		if( !empty($arrEstudios) && !empty($allInputs['id']) ){ 
			foreach ($arrEstudios as $key => $row) {
				if( @$row['es_temporal'] === TRUE ){ 
					$row['idempleado'] = $allInputs['id'];
					$this->model_nivel_estudios->m_agregar_estudio_a_empleado($row);
				}
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$boolEspecialidad = false;
    	// validadcion empresa_especialidad_medico
    	foreach ($allInputs['especialidades'] as $row) {
    		$row['idmedico'] = $allInputs['idmedico'];
    		$boolEspecialidad = $this->model_empleado->m_verificar_empresa_especialidad_medico($row);
    		if( $boolEspecialidad ){
    			$arrData['message'] = 'La especialidad ' . $row['especialidad'] . 'ya ha sido agregada';
    			$arrData['flag'] = 0;
    			$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}
    	}

    	$this->db->trans_start();
    	foreach ($allInputs['especialidades'] as $row) { 
    		$row['idmedico'] = $allInputs['idmedico'];
    		if($this->model_empleado->m_agregar_especialidad_medico($row)){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
    	}

    	// validacion y registro medico_especialidad en tabla pa_medico_especialidad
    	
    	foreach ($allInputs['especialidades'] as $row) {
    		$row['idmedico'] = $allInputs['idmedico'];
    		$row['rne'] = null;
    		$row['situacion'] = null;
    		$boolMedicoEspecialidad = false;
    		$boolMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($row);
    		if( !$boolMedicoEspecialidad ){
    			if($this->model_empleado->m_agregar_situacion_rne_especialidad($row)){ 
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
	
	public function agregar_situacion_rne_inicialmente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	
    	// verificar si existe el medico y la especialidad en la tabla pa_medico_especialidad
    	// si existe editar si no hay que registrar una nueva fila.
    	// foreach ($allInputs['especialidades'] as $row) {
    	// 	$row['idmedico'] = $allInputs['idmedico'];
    	// 	$idMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($allInputs);
    	// }
    	
    	// VERIFICAR QUE EL RNE NO SEA REPETIDO
    	if( !empty($allInputs['rne']) ){
    		if( $this->model_empleado->m_verificar_rne_medico_esp($allInputs) ){
    			$arrData['message'] = 'Ya se ha registrado un RNE igual.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}
    	}
    	$this->db->trans_start();
    	foreach ($allInputs['especialidades'] as $row) {
    		$row['idmedico'] = $allInputs['idmedico'];
    		$idMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($row);
	    	if( $idMedicoEspecialidad ){ // editar el rne
	    		$allInputs['idmedicoespecialidad'] = $idMedicoEspecialidad;
	    		
	    		if($this->model_empleado->m_editar_situacion_rne_especialidad($row)){ 
					$arrData['message'] = 'Se editaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}
	    	}else{ // registrar -- solo para los medicos que ya tienen una empresa_especialidad agregada
	    		// Para los nuevos ya no debe ocurrir xq al agregar una empresa_especialidad ya se registra tmb en pa_medico_especialidad
	    		// 
	    		
	    		if($this->model_empleado->m_agregar_situacion_rne_especialidad($row)){ 
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
	public function agregar_situacion_rne()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();

    	$boolEspecialidad = false;
		$boolEspecialidad = $this->model_empleado->m_verificar_empresa_especialidad_medico_anulado($allInputs);
		if( $boolEspecialidad ){
			$arrData['message'] = 'No se puede registrar los datos porque la empresa-especialidad fue anulada.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

    	// verificar si existe el medico y la especialidad en la tabla pa_medico_especialidad
    	// si existe editar si no hay que registrar una nueva fila.
    	$idMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($allInputs);
    	// VERIFICAR QUE EL RNE NO SEA REPETIDO
    	if( !empty($allInputs['rne']) ){
    		if( $this->model_empleado->m_verificar_rne_medico_esp($allInputs) ){
    			$arrData['message'] = 'Ya se ha registrado un RNE igual.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}
    	}
    	$this->db->trans_start();
    	if( $idMedicoEspecialidad ){ // editar el rne
    		$allInputs['idmedicoespecialidad'] = $idMedicoEspecialidad;
    		
    		if($this->model_empleado->m_editar_situacion_rne_especialidad($allInputs)){ 
				$arrData['message'] = 'Se editaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
    	}else{ // registrar -- solo para los medicos que ya tienen una empresa_especialidad agregada
    		// Para los nuevos ya no debe ocurrir xq al agregar una empresa_especialidad ya se registra tmb en pa_medico_especialidad
    		// 

    		if($this->model_empleado->m_agregar_situacion_rne_especialidad($allInputs)){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}

    	}
    	// EDITAR COLEGIATURA 
    	if($this->model_empleado->m_editar_colegiatura_prof($allInputs)){ 
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		// EDITAR CATEGORIA PERSONAL DE SALUD 
		if($this->model_empleado->m_editar_categoria_ps($allInputs)){ 
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_medico_a_empresa_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	$boolMedico = false;
		$boolMedico = $this->model_empleado->m_verificar_empresa_especialidad_medico($allInputs);
		if( $boolMedico ){
			$arrData['message'] = 'El médico ya fue agregado.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
    	// verificar si existe el medico y la especialidad en la tabla pa_medico_especialidad
    	// si existe editar si no hay que registrar una nueva fila.
    	// var_dump($allInputs); exit();
    	$idMedicoEspecialidad = $this->model_empleado->m_verificar_especialidad_medico($allInputs);
    	$this->db->trans_start();
    	if($this->model_empleado->m_agregar_especialidad_medico($allInputs)){ 
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}else{
			$idMedicoEspecialidad = TRUE;
		}

    	if( $idMedicoEspecialidad == FALSE ){ // 
    		if($this->model_empleado->m_agregar_situacion_rne_especialidad($allInputs)){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
    	}
    	

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function darBaja()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo dar de baja. Consulte con Luchito de Sistemas';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_empleado->m_dar_baja($row['id']) ){
				$arrData['message'] = 'Se dio de baja correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function revertirBaja()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo revertir baja. Consulte con Luchito de Sistemas';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_empleado->m_revertir_baja($row['id']) ){
				$arrData['message'] = 'Se revirtió baja correctamente';
	    		$arrData['flag'] = 1;
			}
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
    	// var_dump($allInputs); exit();
    	if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ // estas validaciones no tienen efecto en sistemas
			// verificar si el empleado tiene asistencia
	    	if( $this->model_empleado->m_verificar_si_tiene_asistencia_por_id($allInputs['id']) ){
	    		$arrData['message'] = 'La acción no puede ejecutarse, el empleado ha marcado asistencia';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
	    	// verificar si es medico y tiene atencion medica 
	    	if(!empty($allInputs['idmedico']) ){ 
				if( $this->model_empleado->m_verificar_si_tiene_atencion_medica_idmedico($allInputs['idmedico']) ){
		    		$arrData['message'] = 'La acción no puede ejecutarse, el médico tiene atenciones';
		    		$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
					return;
		    	}
	    	}
    	}
    	// var_dump($allInputs); exit();
		if( $this->model_empleado->m_deshabilitar($allInputs['id']) ){ 
			$this->model_empleado->m_quitar_num_documento_medico($allInputs['num_documento']); 
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_especialidad_medico()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	// verificar si es medico y tiene atencion medica
    	foreach ($allInputs['especialidades'] as $row) {
    		$row['idmedico'] = $allInputs['idmedico'];
			if( $this->model_empleado->m_verificar_si_tiene_atencion_medica_por_especialidad($row) ){
	    		$arrData['message'] = 'La acción no puede ejecutarse, el médico tiene atenciones';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
    	}

    	foreach ($allInputs['especialidades'] as $row) {
	    	if( $this->model_empleado->m_anular_especialidad_medico($row['id']) ){ 
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}	
    	}
		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar_especialidad_medico()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo habilitar los datos';
    	$arrData['flag'] = 0;

    	foreach ($allInputs as $row) {
	    	if( $this->model_empleado->m_habilitar_especialidad_medico($row['id']) ){ 
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}	
    	}
		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar_especialidad_medico()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo Deshabilitar los datos';
    	$arrData['flag'] = 0;

    	foreach ($allInputs as $row) {
	    	if( $this->model_empleado->m_deshabilitar_especialidad_medico($row['id']) ){ 
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}	
    	}
		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_medico_de_empresa_especialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	// verificar si los medicos tienen atencion medica
    	foreach ($allInputs['medicos'] as $row) {
    		$row['idempresa'] = $allInputs['idempresa'];
    		$row['idempresadetalle'] = $allInputs['idempresadetalle'];
    		if($this->model_empleado->m_verificar_medico_tiene_programacion($row) ){ 
				$arrData['message'] = 'La acción no puede ejecutarse, el médico tiene Programación asistencial';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}

	    	if( $this->model_empleado->m_verificar_si_tiene_atencion_medica_por_especialidad($row) ){
	    		$arrData['message'] = 'La acción no puede ejecutarse, el médico tiene atenciones';
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}	    	
    	}
		foreach ($allInputs['medicos'] as $row) {
			if( $this->model_empleado->m_anular_especialidad_medico($row['idempresamedico']) ){ 
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// VACACIONES
	public function ver_popup_vacaciones()
	{
		$this->load->view('empleado/popupVacaciones_view');
	}
	
	public function lista_vacaciones_por_empleado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramDatos = $allInputs['datos'];
		// var_dump($allInputs); exit();
		$lista = $this->model_empleado->m_cargar_vacaciones_empleado($paramDatos['id']);
		// var_dump($lista); exit();
		$arrListado = array();
		$i = 0;
		$idvacaciones = 0;
		$fecha_temporal = NULL;
		$tamaño_lista = count($lista);
		foreach ($lista as $row) {
			if($i == 0){
				// $fecha_inicial_temporal = $row['fecha_especial'];
				$fecha_temporal =  $row['fecha_especial'];
				array_push($arrListado,
						array(
						'id' => $idvacaciones+1,
						'descripcion' => empty($row['descripcion_smh'])? $row['descripcion_mh'] : $row['descripcion_smh'],
						'fecha_inicial' => darFormatoDMY($row['fecha_especial']),
						'fecha_final' => NULL,
						'cantidad_dias' => 0,
					)
				);
			}else{
				if( diferenciaFechas($row['fecha_especial'],$fecha_temporal) > 1 ){
					// con esto se corta la lista y se agrega la fecha final, para luego continuar con una nueva vacacion
					$arrListado[$idvacaciones]['fecha_final'] = darFormatoDMY($fecha_temporal);
					$arrListado[$idvacaciones]['cantidad_dias'] = diferenciaFechas($arrListado[$idvacaciones]['fecha_final'],$arrListado[$idvacaciones]['fecha_inicial']) + 1;
					$idvacaciones++;
					array_push($arrListado,
						array(
							'id' => $idvacaciones+1,
							'descripcion' => empty($row['descripcion_smh'])? $row['descripcion_mh'] : $row['descripcion_smh'],
							'fecha_inicial' => darFormatoDMY($row['fecha_especial']),
							'fecha_final' => NULL,
							'cantidad_dias' => 0,
						)
					);
				}
			}
			$fecha_temporal =  $row['fecha_especial'];
			$i++;
			if( $i == $tamaño_lista ){
				$arrListado[$idvacaciones]['fecha_final'] = darFormatoDMY($row['fecha_especial']);
				$arrListado[$idvacaciones]['cantidad_dias'] = diferenciaFechas($arrListado[$idvacaciones]['fecha_final'],$arrListado[$idvacaciones]['fecha_inicial']) + 1;
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
	public function lista_medicos_especialidad_autocomplete(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$allInputs['ruc'] = $this->sessionHospital['ruc_empresa_admin'];
		// var_dump($this->sessionHospital); exit(); 
		$lista = $this->model_empleado->m_cargar_medicos_especialidad_autocomplete($allInputs);

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'], 
					'idespecialidad' => $row['idespecialidad'], 
					'especialidad' => $row['especialidad'],	
					'descripcion' => $row['medico'] . ' / ' . $row['especialidad'] . ' / '.	$row['empresa'],
					'idempresamedico' => $row['idempresamedico'],				
					'idempresatercera' => $row['idempresatercera'],	
					'correo_electronico' => $row['correo_electronico'],					
					'tiene_prog_cita' => $row['tiene_prog_cita'],
					'tiene_prog_proc' => $row['tiene_prog_proc'],		
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

	public function lista_medicos_filtro_autocomplete(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$allInputs['ruc'] = $this->sessionHospital['ruc_empresa_admin'];
		$lista = $this->model_empleado->m_cargar_medicos_filtro_autocomplete($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],		
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

	public function lista_medicos_especialidad_prog(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$allInputs['datos']['ruc'] = $this->sessionHospital['ruc_empresa_admin'];
		$lista = $this->model_empleado->m_cargar_medicos_especialidad_prog($allInputs['paginate'],$allInputs['datos']);
		$totalRows = $this->model_empleado->m_count_medicos_especialidad_prog($allInputs['paginate'],$allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'], 
					'idespecialidad' => $row['idespecialidad'], 
					'especialidad' => $row['especialidad'],
					'idempresamedico' => $row['idempresamedico'],		
					'correo_electronico' => $row['correo_electronico'],	
					'tiene_prog_cita' => $row['tiene_prog_cita'],	
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
	public function lista_medicos_especialidad_prog_info(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$allInputs['datos']['ruc'] = $this->sessionHospital['ruc_empresa_admin'];
		$lista = $this->model_empleado->m_cargar_medicos_especialidad_prog_info($allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmedico'],
					'medico' => $row['medico']
					//'idempresa' => $row['idempresa'],
					//'empresa' => $row['empresa'], 
					//'idespecialidad' => $row['idespecialidad'], 
					//'especialidad' => $row['especialidad'],
					//'idempresamedico' => $row['idempresamedico'],		
					//'correo_electronico' => $row['correo_electronico'],	
					//'tiene_prog_cita' => $row['tiene_prog_cita'],	
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
	public function lista_tipo_documento(){
		$lista = $this->model_empleado->m_cargar_tipo_documento();
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
					array(
					'id' => $row['idtipodocumentorh'],
					'descripcion' => $row['descripcion_rtd'],
					'estado' => $row['estado_td'],
					'longitud' => $row['longitud_numero']
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
	public function verifica_cc_empleado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['flag'] = 0;
    	$arrData['message'] = 'Debe ser registrado su centro de costo previamente.';

    	$cc = $this->model_empleado->m_carga_cc_empleado();
    	if(!empty($cc)){
    		$arrData['flag'] = 1;
    		$arrData['message'] = 'Centro de costo registrado.';
    	}   	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	} 

	public function actualizar_fecha_caducidad(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo actualizar los datos';
    	$arrData['flag'] = 0;
 
   		$result = $this->model_empleado->m_consultar_empleado($allInputs['idempleado'],'si');
   		//var_dump($result); exit();
   		$fechaAnterior = strtotime($result['fecha_caducidad_coleg']);
    	$fechaNueva = strtotime($allInputs['fecha_caducidad']);
    	$fechaActual = strtotime(date('Y-m-d'));

   		if($fechaNueva <= $fechaAnterior){
   			$arrData['message'] = 'La fecha de caducidad no puede ser menor o igual a la actual';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
   		}

    	if( $this->model_empleado->m_actualizar_fecha_caducidad($allInputs) ){ 
			$arrData['message'] = 'Se actualizaron los datos correctamente';
    		$arrData['flag'] = 1;
		}	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function actualizar_contrato(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se agregaron los datos correctamente';
    	$arrData['flag'] = 1;
 

   		if(empty($allInputs['empresaadmin']) || $allInputs['empresaadmin']['id'] == '0' ){
   			$arrData['message'] = 'Debe seleccionar una Empresa';
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
   		}

   		if(empty($allInputs['condicion_laboral']) || $allInputs['condicion_laboral']['id'] == 'NONE'){
	   		$arrData['message'] = 'Debe seleccionar una Condicion Laboral';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
	   	}

	   	if(strtotime($allInputs['fecha_ini_contrato']) < strtotime($allInputs['fecha_ing'])){
	   		$arrData['message'] = 'La fecha de inicio de contrato es menor a la fecha de ingreso.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
	   	}

	   	if(strtotime($allInputs['fecha_ini_contrato']) >= strtotime($allInputs['fecha_fin_contrato'])){
    		$arrData['message'] = 'La fecha de fin de contrato es menor a la fecha de inicio de contrato.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		$this->db->trans_start();
    				
		$allInputs['vigenteBool'] = 1;
		$allInputs['empresa_obj']['id'] = $allInputs['empresaadmin']['id'];
		$allInputs['condicion_laboral_obj']['id'] = $allInputs['condicion_laboral']['id'];
		
		if($this->model_historial_contrato->m_registrar($allInputs)){ 	
			$id = GetLastId('idhistorialcontrato', 'rh_historial_contrato');			
			if(!$this->model_historial_contrato->m_actualizar_contratos_antiguos($allInputs, $id)){
				$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
				$arrData['flag'] = 0;
			}			
		}else{
			$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
			$arrData['flag'] = 0;
		}		
			
		$this->db->trans_complete();	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}