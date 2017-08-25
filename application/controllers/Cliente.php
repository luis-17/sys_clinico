<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cliente extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper','otros_helper','fechas_helper'));
		$this->load->model(array('model_cliente','model_ubigeo','model_zona','model_tipo_via','model_producto','model_convenio'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_clientes()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_cliente->m_cargar_clientes($paramPaginate);
		$totalRows = $this->model_cliente->m_count_clientes($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			$fechaNacimiento = $row['fecha_nacimiento'];
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'idhistoria' => trim($row['idhistoria']),
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'telefono' => $row['telefono'],
					'celular' => $row['celular'],
					'email' => $row['email'],
					'fecha_nacimiento' => darFormatoDMY("$fechaNacimiento"),
					'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'sexo' => strtoupper_total($row['sexo']),
					'idtipocliente' => $row['idtipocliente'],
					'tipocliente' => $row['descripcion_tc'],
					'sede_convenio' => $row['idsedeempresaadmin'],
					'idtipozona' => $row['idtipozona'],
					'idtipovia' => $row['idtipovia'],
					'tipovia' => $row['descripcion_tv'],
					'nombre_via' => $row['nombre_via'],
					'idzona' => $row['idzona'],
					'zona' => $row['descripcion_zo'],
					'numero' => $row['dir_numero'],
					'kilometro' => $row['dir_kilometro'],
					'manzana' => $row['dir_manzana'],
					'interior' => $row['dir_interior'],
					'numero_departamento' => $row['dir_departamento'],
					'lote' => $row['dir_lote'],
					'referencia' => $row['referencia'],
					'direccion' => $row['direccion'],
					'iddepartamento' => trim($row['iddepartamento']),
					'departamento' => $row['departamento'],
					'idprovincia' => trim($row['idprovincia']),
					'provincia' => $row['provincia'],
					'iddistrito' => trim($row['iddistrito']),
					'distrito' => $row['distrito'],
					'si_afiliado_puntos' => $row['si_afiliado_puntos'],
					'pertenece_salud_ocup'=> (int)$row['si_salud_ocupacional'],
					'idempresacliente' => $row['idempresacliente_cli'],
					'empresacliente' => $row['empresa_salud_ocup'],
					'idprocedencia' => $row['idprocedencia'],
					// 'empresacliente'=> array(

					// )
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
	public function lista_empresas_cliente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_cliente->m_cargar_empresas_cliente($paramPaginate);
		$totalRows = $this->model_cliente->m_count_empresas_cliente($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => trim($row['idempresacliente']),
					'descripcion' => trim($row['descripcion']),
					'ruc_empresa' => trim($row['ruc_empresa']),
					'telefono' => trim($row['telefono']),
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
	public function listar_este_cliente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cliente->m_cargar_este_cliente($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'idhistoria' => $row['idhistoria'],
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'telefono' => $row['telefono'],
					'celular' => $row['celular'],
					'email' => $row['email'],
					'fecha_nacimiento' => darFormatoDMY($row['fecha_nacimiento']),
					'edadEnAtencion' => strtoupper(devolverEdadDetalle($row['fecha_nacimiento'])),
					'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'sexo' => strtoupper_total($row['sexo']),
					'idtipocliente' => $row['idtipocliente'],
					'tipocliente' => $row['descripcion_tc'],
					'sede_convenio' => $row['idsedeempresaadmin'],
					'idtipozona' => $row['idtipozona'],
					'idtipovia' => $row['idtipovia'],
					'tipovia' => $row['descripcion_tv'],
					'nombre_via' => $row['nombre_via'],
					'idzona' => $row['idzona'],
					'zona' => $row['descripcion_zo'],
					'numero' => $row['dir_numero'],
					'kilometro' => $row['dir_kilometro'],
					'manzana' => $row['dir_manzana'],
					'interior' => $row['dir_interior'],
					'numero_departamento' => $row['dir_departamento'],
					'lote' => $row['dir_lote'],
					'referencia' => $row['referencia'],
					'direccion' => $row['direccion'],
					'iddepartamento' => trim($row['iddepartamento']),
					'departamento' => $row['departamento'],
					'idprovincia' => trim($row['idprovincia']),
					'provincia' => $row['provincia'],
					'iddistrito' => trim($row['iddistrito']),
					'distrito' => $row['distrito'],
					'si_afiliado_puntos' => $row['si_afiliado_puntos']
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
	public function listar_este_cliente_por_num_doc()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cliente->m_cargar_este_cliente_por_num_documento($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'idhistoria' => trim($row['idhistoria']),
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'telefono' => $row['telefono'],
					'celular' => $row['celular'],
					'email' => $row['email'],
					'fecha_nacimiento' => darFormatoDMY($row['fecha_nacimiento']),
					'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'sexo' => strtoupper_total($row['sexo']),
					'idtipocliente' => $row['idtipocliente'],
					'tipocliente' => $row['descripcion_tc'],
					'sede_convenio' => $row['idsedeempresaadmin'],
					'idtipozona' => $row['idtipozona'],
					'idtipovia' => $row['idtipovia'],
					'tipovia' => $row['descripcion_tv'],
					'nombre_via' => $row['nombre_via'],
					'idzona' => $row['idzona'],
					'zona' => $row['descripcion_zo'],
					'numero' => $row['dir_numero'],
					'kilometro' => $row['dir_kilometro'],
					'manzana' => $row['dir_manzana'],
					'interior' => $row['dir_interior'],
					'numero_departamento' => $row['dir_departamento'],
					'lote' => $row['dir_lote'],
					'referencia' => $row['referencia'],
					'direccion' => $row['direccion'],
					'iddepartamento' => trim($row['iddepartamento']),
					'departamento' => $row['departamento'],
					'idprovincia' => trim($row['idprovincia']),
					'provincia' => $row['provincia'],
					'iddistrito' => trim($row['iddistrito']),
					'distrito' => $row['distrito'],
					'si_afiliado_puntos' => $row['si_afiliado_puntos']
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
	public function listar_este_cliente_por_historia(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cliente->m_cargar_este_cliente_por_historia($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'idhistoria' => trim($row['idhistoria']),
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'telefono' => $row['telefono'],
					'celular' => $row['celular'],
					'email' => $row['email'],
					'fecha_nacimiento' => darFormatoDMY($row['fecha_nacimiento']),
					'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'sexo' => strtoupper_total($row['sexo']),
					'idtipocliente' => $row['idtipocliente'],
					'tipocliente' => $row['descripcion_tc'],
					'sede_convenio' => $row['idsedeempresaadmin'],
					'idtipozona' => $row['idtipozona'],
					'idtipovia' => $row['idtipovia'],
					'tipovia' => $row['descripcion_tv'],
					'nombre_via' => $row['nombre_via'],
					'idzona' => $row['idzona'],
					'zona' => $row['descripcion_zo'],
					'numero' => $row['dir_numero'],
					'kilometro' => $row['dir_kilometro'],
					'manzana' => $row['dir_manzana'],
					'interior' => $row['dir_interior'],
					'numero_departamento' => $row['dir_departamento'],
					'lote' => $row['dir_lote'],
					'referencia' => $row['referencia'],
					'direccion' => $row['direccion'],
					'iddepartamento' => trim($row['iddepartamento']),
					'departamento' => $row['departamento'],
					'idprovincia' => trim($row['idprovincia']),
					'provincia' => $row['provincia'],
					'iddistrito' => trim($row['iddistrito']),
					'distrito' => $row['distrito'],
					'si_afiliado_puntos' => $row['si_afiliado_puntos']
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
	public function lista_clientes_con_historia_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); //tipoBusqueda
		$lista = array();
		if( isset($allInputs['searchText']) ){
			$lista = $this->model_cliente->m_cargar_clientes_con_historia_autocomplete($allInputs);
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idcliente'],
					'descripcion' => strtoupper_total($row['paciente']),
					'idhistoria' => $row['idhistoria']
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
	public function lista_clientes_venta_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); //tipoBusqueda 
		$lista = array();
		if( isset($allInputs['searchText']) ){
			$lista = $this->model_cliente->m_cargar_clientes_venta_autocomplete($allInputs);
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'descripcion' => strtoupper_total($row['paciente'])
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
	public function lista_clientes_ocupacional_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cliente->m_cargar_clientes_ocupacional_autocomplete($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idcliente'],
					'descripcion' => strtoupper_total($row['paciente'])
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
	public function lista_clientes_perfiles_ocupacional_autocomplete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cliente->m_cargar_clientes_ocupacional_con_perfiles_autocomplete($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idcliente'],
					'descripcion' => strtoupper_total($row['paciente'])
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
		$this->load->view('cliente/cliente_formView');
	}
	public function ver_popup_busqueda_cliente()
	{
		$this->load->view('cliente/busquedaClienteFormView');
	}
	public function ver_popup_busqueda_empresa_cliente()
	{
		$this->load->view('cliente/busquedaEmpresaClienteFormView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
    	if(!empty($allInputs['num_documento'])){
	    	/* VALIDAR SI EL DNI YA EXISTE */
	    	$arrParams = array('numero_documento' => $allInputs['num_documento']);
	    	$fCliente = $this->model_cliente->m_cargar_este_cliente_por_num_documento($arrParams);
	    	if( !empty($fCliente) ) {
	    		$arrData['message'] = 'El DNI ingresado, ya existe.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	   		}
	   	}
	   	// VALIDAR UBIGEO
   		if(!empty($allInputs['iddepartamento'])){
   			$allInputs['ubigeo'] = trim($allInputs['iddepartamento']).trim($allInputs['idprovincia']).trim($allInputs['iddistrito']);
   			if( $ubigeo = $this->model_ubigeo->m_cargar_ubigeo_concatenado($allInputs) ){
   				$allInputs['idubigeo'] = $ubigeo['idubigeo'];
   			}else{
   				$arrData['message'] = 'Ingrese el departamento, provincia y/o distrito correctamente';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
   			}
   		}
	   	// var_dump($allInputs['idubigeo']);
	   	// exit();
   		if(empty($allInputs['celular'])){
    		$arrData['message'] = 'El campo celular no puede ser vacio';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(!is_numeric($allInputs['celular']) || strlen($allInputs['celular'])>9 ){
    		$arrData['message'] = 'Debe ingresar número de celular válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(!empty($allInputs['telefono']) && (!is_numeric($allInputs['telefono']) || strlen($allInputs['telefono'])>7) ){
    		$arrData['message'] = 'Debe ingresar número de télefono válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$this->db->trans_start();
		if($this->model_cliente->m_registrar($allInputs)) { // registro de cliente
			$allInputs['idcliente'] = GetLastId('idcliente','cliente');
			$this->model_cliente->m_registrar_historia($allInputs); // registro de historia
			// $arrParams = array(
			// 	'codigo_historia' => GetLastId('idhistoria','cliente')
			// 	'idhistoria' =>
			// );
			// $this->model_cliente->m_actualizar_codigo_historia($arrParams);
			$arrData['idcliente'] = $allInputs['idcliente'];
			$arrData['message'] = 'Se registraron los datos correctamente';
			$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
	    	if(!empty($allInputs['num_documento'])){
		    	/* VALIDAR SI EL DNI YA EXISTE */
		    	// $arrParams = array('numero_documento' => $allInputs['num_documento']);
		    	$fCliente = $this->model_cliente->m_buscar_dni_cliente_con_excepcion($allInputs,TRUE);
		    	if( $fCliente ) {
		    		$arrData['message'] = 'El DNI ingresado, ya existe.';
					$arrData['flag'] = 0;
					$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
					return;
		   		}
		   	}
	   	// VALIDAR UBIGEO
	   		if(!empty($allInputs['iddepartamento'])){
	   			$allInputs['ubigeo'] = trim($allInputs['iddepartamento']).trim($allInputs['idprovincia']).trim($allInputs['iddistrito']);
	   			if( $ubigeo = $this->model_ubigeo->m_cargar_ubigeo_concatenado($allInputs) ){
	   				$allInputs['idubigeo'] = $ubigeo['idubigeo'];
	   			}else{
	   				$arrData['message'] = 'Ingrese el departamento, provincia y/o distrito correctamente';
					$arrData['flag'] = 0;
					$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
					return;
	   			}
	   		}

	   		if(empty($allInputs['celular'])){
	    		$arrData['message'] = 'El campo celular no puede ser vacio';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}

	    	if(!is_numeric($allInputs['celular']) || strlen($allInputs['celular'])>9 ){
	    		$arrData['message'] = 'Debe ingresar número de celular válido';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}

	    	if(!empty($allInputs['telefono']) && (!is_numeric($allInputs['telefono']) || strlen($allInputs['telefono'])>7) ){
	    		$arrData['message'] = 'Debe ingresar número de télefono válido';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
	    	
		if($this->model_cliente->m_editar($allInputs)){
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
			if( $this->model_cliente->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function buscar_dni()
	{
		//return false; // hasta arreglar la conexión con SQL SERVER.
		$this->load->model('model_empleado');
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$sujeto = 'DNI';
		// buscar si el dni ya lo tenemos en la base de datos del servidor antes de buscar en la de RENIEC

		/* DESCOMENTAR CUANDO ARREGLEMOS LA LECTURA POR DNI. 
		if(empty($allInputs['procedencia'])){
			$allInputs['procedencia'] = 'personal';
		}
		if( $allInputs['procedencia'] == 'cliente' ){
			$sujeto = 'CLIENTE';
			if($this->model_cliente->m_buscar_dni_cliente_con_excepcion($allInputs) ){
				$arrData['message'] = $sujeto . ' YA EXISTE';
			    $arrData['flag'] = 0;
			    $this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			}
		}elseif( $allInputs['procedencia'] == 'personal' ){
			$sujeto = 'EMPLEADO';
			if( ($this->model_empleado->m_verificar_si_existe_empleado_por_numero_documento($allInputs['num_documento'])) > 0){
				$arrData['message'] = $sujeto . ' YA EXISTE';
			    $arrData['flag'] = 0;
			    $this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			}
		}
		$fArray = $this->model_cliente->m_cargar_por_dni($allInputs);
		if($fArray === 0){
			$arrData['message'] = 'NO SE PUDO CONECTAR CON LA BASE DE DATOS DE LOS DNIs.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}else{
			if(empty($fArray)){
				$arrData['message'] = $sujeto . ' NO FUE ENCONTRADO EN LA BD RENIEC.';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;

			}else{
				//$fArray['dni'] = trim($fArray['dni']);
				$nombres = iconv("windows-1252", "utf-8", $fArray['Nombres']);
				$ape_pat = iconv("windows-1252", "utf-8", $fArray['Ape_Pat']);
				$ape_mat = iconv("windows-1252", "utf-8", $fArray['Ape_Mat']);
				$fArray['Nombres'] = $nombres;
				$fArray['Ape_Pat'] = $ape_pat;
				$fArray['Ape_Mat'] = $ape_mat;
				$año = substr($fArray['Fec_Nac'], 0, 4);
				$mes = substr($fArray['Fec_Nac'], 4, 2);
				$dia = substr($fArray['Fec_Nac'], 6, 2);
				$fArray['fecha_nacimiento'] = $dia.'-'.$mes.'-'.$año;
				$fArray['sexo'] = $fArray['Sexo'] == 1 ? 'M' : 'F';
		    	$arrData['datos'] = $fArray;
		    	$arrData['message'] = $sujeto . ' ENCONTRADO EN LA BD RENIEC';
		    	$arrData['flag'] = 1;
			}
		}
		*/

		/* QUITAR CUANDO ARREGLEMOS LA LECTURA POR DNI */ 
		$arrData['message'] = 'NO SE PUDO CONECTAR CON LA BASE DE DATOS DE LOS DNIs.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function validar_si_cliente_existe(){

		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		if($this->model_cliente->m_validar_si_cliente_existe($allInputs))
		{
			//$arrData['message'] = 'CLIENTE ENCONTRADO EN LA BD';
			$arrData['flag'] = 1;
		}else $arrData['flag'] = 0;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/********************* TIPO DE CLIENTE ******* PARA BORRAR********************/
	public function lista_tipo_cliente() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_cliente->m_cargar_tipo_cliente($paramPaginate);
		$totalRows = $this->model_cliente->m_count_tipo_cliente($paramPaginate);

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idtipocliente' => $row['idtipocliente'],
					'descripcion' => strtoupper_total($row['descripcion_tc']),
					'contrato' => strtoupper_total($row['numero_contrato']),
					'sede_convenio' => $row['idsedeempresaadmin'],
					'fec_inicial' => date('d-m-Y',strtotime($row['fecha_inicial'])),
					'fec_vigencia' => date('d-m-Y',strtotime($row['fecha_vigencia']))
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
	public function lista_tipo_cliente_cbo() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = array();
		$lista = $this->model_convenio->m_cargar_convenio_cbo();

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idtipocliente'],
					'descripcion' => strtoupper_total($row['descripcion_tc'])
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
	/* para borrar */
	/*
	public function lista_tipo_cliente_descuento() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		if( empty($allInputs['idtipocliente']) ){ // idtipocliente
			//$lista = $this->model_cliente->m_cargar_tipo_producto($allInputs); // para registro
			$lista = $this->model_producto->m_cargar_productos_cbo();
		}else{
			$lista = $this->model_cliente->m_cargar_tipo_cliente_descuento($allInputs); // para edicion
		}

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'],
					'producto' => strtoupper_total($row['descripcion']),
					'idespecialidad' => $row['idespecialidad'],
					// 'id' => $row['idtipoproducto'],
					// 'descripcion' => strtoupper_total($row['nombre_tp']),
					// 'porcentaje' => empty($row['porcentaje_dcto'])? null : $row['porcentaje_dcto'],
					// 'idtipoclientedescuento' => empty($row['idtipoclientedescuento'])? null : $row['idtipoclientedescuento'],
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
	}*/
	public function obtener_convenio_cliente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$row = $this->model_cliente->m_cargar_convenio_cliente($allInputs);

		$arrData['datos'] = $row;
    	$arrData['message'] = 'Descuento: ' . $row['porcentaje_dcto'] . '%';
    	$arrData['flag'] = 1;
		if(empty($row)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró el descuento para este tipo de cliente';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/*para borrar*/
	/*
	public function obtener_descuento_por_tipo_producto()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$row = $this->model_cliente->m_cargar_descuento_tipocliente_por_tipoproducto($allInputs);
		// $arrListado = array();
		// foreach ($lista as $row) {
		// 	array_push($arrListado,
		// 		array(
		// 			'id' => trim($row['idcliente']),

		// 	);
		// }
    	$arrData['datos'] = $row;
    	$arrData['message'] = 'Descuento: ' . $row['porcentaje_dcto'] . '%';
    	$arrData['flag'] = 1;
		if(empty($row)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró el descuento para este tipo de cliente';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	*/
	public function ver_popup_tipo_cliente()
	{
		$this->load->view('cliente/tipoClienteFormView');
	}
	public function registrarEditarTipoCliente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//$arrData['idtipocliente'] = null;

    	$this->db->trans_start();
    	if( empty($allInputs['idtipocliente']) ){
	    	if($this->model_cliente->m_registrar_tipo_cliente($allInputs)){
	    		$arrData['idtipocliente'] = GetLastId('idtipocliente','tipo_cliente');
	    		foreach ($allInputs['detalle'] as $row) {
	    			/*
	    			if( !empty($row['porcentaje']) ){
	    				$row['idtipocliente'] = $arrData['idtipocliente'];
	    				$row['idempresaadmin'] = $allInputs['empresaadmin']['id'];
	    				if($this->model_cliente->m_registrar_tipo_cliente_descuento($row)){
							$arrData['message'] = 'Se registraron los datos correctamente';
				    		$arrData['flag'] = 1;
	    				}
	    			}
	    			*/
	    		}
			}
    	}else{
    		if($this->model_cliente->m_editar_tipo_cliente($allInputs)){
    			foreach ($allInputs['detalle'] as $row) {
    				/*
    				if( empty($row['idtipoclientedescuento']) ){ // se registra nuevo descuento
	    				if( !empty($row['porcentaje']) ){
		    				$row['idtipocliente'] = $allInputs['idtipocliente'];
		    				$row['idempresaadmin'] = $allInputs['empresaadmin']['id'];
		    				if($this->model_cliente->m_registrar_tipo_cliente_descuento($row)){
		    					$arrData['idtipocliente'] = $allInputs['idtipocliente'];
								$arrData['message'] = 'Se registraron los datos correctamente';
					    		$arrData['flag'] = 1;
		    				}
		    			}
    				}else{ // se edita el descuento por el idtipoclientedescuento
    					if($this->model_cliente->m_editar_tipo_cliente_descuento($row)){
    						$arrData['idtipocliente'] = $allInputs['idtipocliente'];
							$arrData['message'] = 'Se editaron los datos correctamente';
				    		$arrData['flag'] = 1;
	    				}
    				}
    				*/


	    		}
		  //   	$arrData['message'] = 'Se editaron los datos correctamente';
				// $arrData['flag'] = 1;
			}

    	}


		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anularTipoCliente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_cliente->m_anular_tipo_cliente($row['idtipocliente']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function afiliar_a_puntos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo afiliar';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_cliente->m_afiliar_cliente_a_puntos($allInputs) ){
			if( $this->model_cliente->m_iniciar_puntaje_cliente($allInputs) ){
				$arrData['message'] = 'Se afilió correctamente';
    			$arrData['flag'] = 1;
			}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function comprobar_afiliacion_puntos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'Cliente NO está afiliado al Sistema de Puntos';
    	$arrData['flag'] = 0;
		$arrData['datos'] = '';
		if( $cliente = $this->model_cliente->m_comprobar_afiliacion_puntos($allInputs) ){
			if( $datos_puntos = $this->model_cliente->m_obtener_puntaje_cliente($cliente) ){
				$cliente['puntos_acumulados'] = $datos_puntos['puntos_acumulados'];
				$arrData['datos'] = $cliente;
				$arrData['message'] = 'Cliente está afiliado al Sistema de Puntos. Acumula ' . number_format($cliente['puntos_acumulados']) . ' Punto(s)';
	    		$arrData['flag'] = 1;
			}

		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_actualiza_cliente(){
		$this->load->view('cliente/actualizaCliente_formView');
	}

	public function actualizar_datos_cliente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo actualizar el cliente.';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();

    	if(empty($allInputs['celular'])){
    		$arrData['message'] = 'El campo celular no puede ser vacio';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(!is_numeric($allInputs['celular']) || strlen($allInputs['celular'])>9 ){
    		$arrData['message'] = 'Debe ingresar número de celular válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(!empty($allInputs['telefono']) && (!is_numeric($allInputs['telefono']) || strlen($allInputs['telefono'])>7) ){
    		$arrData['message'] = 'Debe ingresar número de télefono válido';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		if( $this->model_cliente->m_actualizar_datos_cliente($allInputs) ){
			$arrData['message'] = 'Se actualizó correctamente';
    		$arrData['flag'] = 1;			
		}

		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
}