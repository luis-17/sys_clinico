<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Especialidad extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_especialidad','model_empleado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_especialidades_bloqueadas_dia()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_especialidad->m_cargar_especialidades_bloqueadas_dia($paramPaginate);
		// $totalRows = $this->model_especialidad->m_count_especialidades();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idespecialidad'],
					'descripcion' => strtoupper($row['especialidad']),
					'consultorio' => $row['nro_consultorio'],
					//'idtipoespecialidad' => $row['idtipoespecialidad']
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	// $arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_especialidad->m_cargar_especialidades($paramPaginate);
		$totalRows = $this->model_especialidad->m_count_especialidades();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['atencion_dia'] == '1' ){
				$objEstado['claseSwitch'] = 'danger';
				$objEstado['labelText'] = 'BLOQUEADO';
				$objEstado['display'] = TRUE;
				$objEstado['boolBloqueo'] = TRUE;
			}elseif( $row['atencion_dia'] == '2'){
				$objEstado['claseSwitch'] = 'success';
				$objEstado['labelText'] = 'DESBLOQUEADO';
				$objEstado['display'] = TRUE;
				$objEstado['boolBloqueo'] = FALSE;
			}
			array_push($arrListado, 
				array(
					'id' => $row['idespecialidad'],
					'nombre' => strtoupper($row['nombre']),
					'descripcion' => strtoupper($row['descripcion']),
					'idtipoespecialidad' => $row['idtipoespecialidad'],
					'atencion_dia' => $row['atencion_dia'],
					'dias_libres' => $row['dias_libres'],
					'estado_bloq' => $objEstado
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
	public function lista_especialidades_busqueda()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_especialidad->m_cargar_especialidades_busqueda($paramPaginate);
		$totalRows = $this->model_especialidad->m_count_especialidades_busqueda();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idtipoespecialidad' => $row['idtipoespecialidad'],
					'tipoespecialidad' => $row['descripcion']
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
	public function lista_especialidades_sedes_empresas_de_session() // PARA "NUEVA VENTA" 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// if( isset($allInputs['search']) ){
			$lista = $this->model_especialidad->m_cargar_especialidades_sedes_empresas_de_session($allInputs);
		// }else{
		// 	$lista = $this->model_especialidad->m_cargar_especialidades_sedes_empresas_de_session();
		// }
		$arrListado = array(); 
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idempresaespecialidad'], 
					'idespecialidad' => $row['idespecialidad'], 
					'descripcion' => $row['especialidad'], 
					'tiene_prog_cita' => ($row['tiene_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_cita' => ($row['tiene_venta_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_prog_proc' => ($row['tiene_prog_proc'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_proc' => ($row['tiene_venta_prog_proc'] == 1) ? TRUE : FALSE,
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
	public function lista_especialidades_restricciones()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// if(!empty($allInputs)){
		// 	var_dump($allInputs); exit();
		// }
		$lista = $this->model_especialidad->m_cargar_empresa_especialidades_con_restricciones($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idempresaespecialidad'], 
					'idespecialidad' => $row['idespecialidad'], 
					'especialidad' => $row['especialidad'], 
					'descripcion' => $row['especialidad'].' - '.$row['empresa'], 
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'idempresaadmin' => $row['id_empresa_admin'], 
					'empresa_admin' => $row['empresa_admin'] 
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
	public function lista_especialidades_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$lista = $this->model_especialidad->m_cargar_especialidades_por_empresa_admin_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'], 
					'descripcion' => $row['especialidad'],
					'name' => '<b>'.strtoupper($row['especialidad']).'</b> ',
					'ticked' => FALSE
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
	public function lista_especialidades_con_programacion() 
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_especialidad->m_cargar_especialidades_con_programacion($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'], 
					'descripcion' => $row['especialidad']
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
	public function ver_popup_formulario()
	{
		$this->load->view('especialidad/especialidad_formView');
	}	
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_especialidad->m_registrar($allInputs)){
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
		if($this->model_especialidad->m_editar($allInputs)){
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
			if( $this->model_especialidad->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_especialidad->m_cargar_especialidades_por_autocompletado($allInputs);
		}else{
			$lista = $this->model_especialidad->m_cargar_especialidades_por_combo();
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'],
					'idempresaespecialidad' => $row['idempresaespecialidad'],
					'descripcion' => $row['nombre'].' - '.$row['empresa'],
					// 'descripcion' => $row['nombre'].' - '.$row['empresa'].' - '.$row['sede']
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
	public function lista_solo_especialidades_por_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_especialidad->m_cargar_solo_especialidades_por_autocompletado($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'],
					'descripcion' => $row['especialidad']
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
	public function bloqueaDesbloqueaEspecialidad()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ocurrió un error, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_especialidad->m_cambiar_atencion_dia($allInputs)) {
			if( $allInputs['atencion_dia'] == 1 ){
				$arrData['message'] = 'Se desbloqueó la especialidad correctamente';
			}else{
				$arrData['message'] = 'Se bloqueó la especialidad correctamente';
			}
			
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_demanda_especialidad_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_especialidad->m_cargar_demanda_especialidad_sede($paramPaginate, $datos);
		$totalRows = $this->model_especialidad->m_count_demanda_especialidad_sede($paramPaginate, $datos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['demanda'] == 'A' ){
				$estadoMed = 'ALTA';
				$claseMed = 'label-success';
			}else if( $row['demanda'] == 'B' ){
				$estadoMed = 'BAJA';
				$claseMed = 'label-warning';
			}else{
				$estadoMed = 'NO ASIGNADA';
				$claseMed = 'label-default';
			}

			if( $row['tiene_prog_cita'] == '1'){
				$objEstado['claseSwitch'] = 'success';
				$objEstado['labelText'] = 'HABILITADO';
				$objEstado['value'] = $row['tiene_prog_cita'];
				$objEstado['boolBloqueo'] = FALSE;
			}else {
				$objEstado['claseSwitch'] = 'danger';
				$objEstado['labelText'] = 'DESHABILITADO';
				$objEstado['value'] = $row['tiene_prog_cita'];
				$objEstado['boolBloqueo'] = TRUE;
			}

			if( $row['tiene_venta_prog_cita'] == '1'){
				$objEstado2['claseSwitch'] = 'success';
				$objEstado2['labelText'] = 'HABILITADO';
				$objEstado2['value'] = $row['tiene_venta_prog_cita'];
				$objEstado2['boolBloqueo'] = FALSE;
			}else {
				$objEstado2['claseSwitch'] = 'danger';
				$objEstado2['labelText'] = 'DESHABILITADO';
				$objEstado2['value'] = $row['tiene_venta_prog_cita'];
				$objEstado2['boolBloqueo'] = TRUE;
			}

			if( $row['tiene_prog_proc'] == '1'){
				$objEstado3['claseSwitch'] = 'success';
				$objEstado3['labelText'] = 'HABILITADO';
				$objEstado3['value'] = $row['tiene_prog_proc'];
				$objEstado3['boolBloqueo'] = FALSE;
			}else {
				$objEstado3['claseSwitch'] = 'danger';
				$objEstado3['labelText'] = 'DESHABILITADO';
				$objEstado3['value'] = $row['tiene_prog_proc'];
				$objEstado3['boolBloqueo'] = TRUE;
			}

			if( $row['tiene_venta_prog_proc'] == '1'){
				$objEstado4['claseSwitch'] = 'success';
				$objEstado4['labelText'] = 'HABILITADO';
				$objEstado4['value'] = $row['tiene_venta_prog_proc'];
				$objEstado4['boolBloqueo'] = FALSE;
			}else {
				$objEstado4['claseSwitch'] = 'danger';
				$objEstado4['labelText'] = 'DESHABILITADO';
				$objEstado4['value'] = $row['tiene_venta_prog_proc'];
				$objEstado4['boolBloqueo'] = TRUE;
			}

			array_push($arrListado, 
				array( 
					'id' => $row['idsedeespecialidad'],
					'idespecialidad' => $row['idespecialidad'],
					'nombre_esp' => $row['nombre_esp'],
					'idsede' => $row['idsede'],
					'descripcion_sede' => $row['descripcion_sede'],
					'estado_sees' => $row['estado_sees'],	
					'demanda' => array(
						'string' => $estadoMed,
						'clase' => $claseMed,
						'bool' => $row['demanda']
					),
					'tiene_prog_cita' => $objEstado,
					'tiene_venta_prog_cita' =>$objEstado2,
					'tiene_prog_proc' => $objEstado3,
					'tiene_venta_prog_proc' =>$objEstado4,

				)
			);
		}
    	$arrData['datos'] = $arrListado;    	
    	$arrData['paginate']['totalRows'] = $totalRows;
    	//print_r(count($arrListado));
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar_demanda_en_grid(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;
		if( $this->model_especialidad->m_editar_demanda_en_grid($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar_prog_asistencial_especialidad_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;

    	if($allInputs['tiene_prog_cita']['value'] == 1 && $this->model_especialidad->m_tiene_programaciones_cargadas($allInputs)){
    		$arrData['message'] = 'Solo puede deshabilitar Especialidades sin Programaciones registradas';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if( $this->model_especialidad->m_editar_prog_asistencial_especialidad_sede($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function editar_venta_prog_asistencial_especialidad_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;

		if( $this->model_especialidad->m_editar_venta_prog_asistencial_especialidad_sede($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_prog_proc_especialidad_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs);exit();
    	if($allInputs['tiene_prog_proc']['value'] == 1 && $this->model_especialidad->m_tiene_programaciones_cargadas($allInputs)){
    		$arrData['message'] = 'Solo puede deshabilitar Especialidades sin Programaciones registradas';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if( $this->model_especialidad->m_editar_prog_proc_especialidad_sede($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function editar_venta_prog_proc_especialidad_sede(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs);exit();
		if( $this->model_especialidad->m_editar_venta_prog_proc_especialidad_sede($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_especialidades_prog_asistencial(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_especialidad->m_cargar_especialidades_prog_asistencial();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idespecialidad'],
					'descripcion' => $row['especialidad']
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
	public function verificar_tiene_programacion(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['idespecialidad'] = $this->sessionHospital['idespecialidad'];
		// VERIFICAR SI TIENE PROGRAMACIÓN 
		if($allInputs['tipo_atencion'] == 'P'){
			$arrData['datos'] = $this->model_especialidad->m_tiene_prog_asistencial_proc($allInputs);
		}else{
			$arrData['datos'] = $this->model_especialidad->m_tiene_prog_asistencial($allInputs);	
		}
    	// VERIFICAR SI EL MEDICO PUEDE ATENDER A DESTIEMPO 
    	$arrMedico = array( 
    		$this->sessionHospital['idmedico']
    	);
    	$listaMed = $this->model_empleado->m_cargar_medico_especialidad_por_arrId($arrMedico);
    	$arrData['fMedico'] = @$listaMed[0];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}