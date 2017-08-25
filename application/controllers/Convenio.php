<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Convenio extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper','otros_helper','fechas_helper'));
		$this->load->model(array('model_convenio','model_ubigeo','model_zona','model_tipo_via','model_producto'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_convenio() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_convenio->m_cargar_convenio($paramPaginate,$paramDatos);
		$totalRows = $this->model_convenio->m_count_convenio($paramPaginate,$paramDatos);

		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idtipocliente' => $row['idtipocliente'],
					'descripcion' => strtoupper_total($row['descripcion_tc']),
					'contrato' => strtoupper_total($row['numero_contrato']),
					'sede_convenio' => $row['idsedeempresaadmin'],
					'fec_inicial' => date('d-m-Y',strtotime($row['fecha_inicial'])),
					'fec_vigencia' => date('d-m-Y',strtotime($row['fecha_vigencia'])),
					'sede_empresa' =>  strtoupper($row['razon_social']).' - '.strtoupper($row['descripcion']),					
					'porcentaje' =>  $row['porcentaje'],					
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
	public function lista_convenio_cbo() {
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
	public function lista_cliente_convenio() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_convenio->m_cargar_cliente_convenio($paramPaginate,$paramDatos);
		$totalRows = $this->model_convenio->m_count_cliente_convenio($paramPaginate,$paramDatos);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idcliente' => $row['idcliente'],
					'num_documento' => $row['num_documento'],
					'nombres' => strtoupper_total($row['nombres']),
					'apellido_paterno' => strtoupper_total($row['apellido_paterno']),
					'apellido_materno' => strtoupper_total($row['apellido_materno']),
					'sexo' => strtoupper_total($row['sexo']),
					// 'edad' => devolverEdad($row['fecha_nacimiento']),
					'edad' => $row['edad'],
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
	public function lista_producto_convenio() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$paramDatos['soloDecimales'] = $allInputs['soloDecimales'];
		$lista = $this->model_convenio->m_cargar_producto_convenio($paramPaginate,$paramDatos);
		$totalRows = $this->model_convenio->m_count_producto_convenio($paramPaginate,$paramDatos);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			$estado = array();
			if($row['estado_cps'] == 1){
				$estado['string'] = 'HABILITADO';
				$estado['clase'] = 'label-success';
				$estado['bool'] = (int)$row['estado_cps'];
			}else if($row['estado_cps'] == 2){
				$estado['string'] = 'DESHABILITADO';
				$estado['clase'] = 'label-default';
				$estado['bool'] = (int)$row['estado_cps'];
			}

			$redondear = false;
			$ajustar = false;
			if($row['precio_variable'] % 1 != 0){
				$redondear = true;
			}

			if($row['porcentaje'] < 0){
				$ajustar = true;
			}				

			array_push($arrListado, 
				array( 
					'idconvenioproductosede' => $row['idconvenioproductosede'],
					'idproductopreciosede' => $row['idproductopreciosede'],
					'idproducto' => $row['idproductomaster'],
					'producto' => strtoupper_total($row['producto']),
					'precio_regular' => $row['precio_sede_sf'],
					'precio_convenio' => $row['precio_variable_sf'],
					//'precio_regular' => $row['precio_sede'],
					//'precio_convenio' => $row['precio_variable'],
					'porcentaje' => number_format($row['porcentaje'],2),
					// 'porcentaje' => $row['precio_sede_sf'] == 0 ? 0:number_format((1 - ($row['precio_variable_sf'] / $row['precio_sede_sf']))*100,2),
					'especialidad' => strtoupper_total($row['especialidad']),
					'tipo_producto' => strtoupper_total($row['tipo_producto']),
					'estado' => $estado,
					'temporal' => false,
					'redondear' =>  $redondear,
					'ajustar' =>  $ajustar,
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

	public function lista_producto_no_estan_convenio() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_convenio->m_cargar_producto_no_estan_convenio($paramPaginate,$paramDatos);
		$totalRows = $this->model_convenio->m_count_producto_no_estan_convenio($paramPaginate,$paramDatos);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idproductopreciosede' => $row['idproductopreciosede'],
					'idproducto' => $row['idproductomaster'],
					'producto' => strtoupper_total($row['producto']),
					//'precio_regular' => $row['precio_sede'],
					'precio_regular' => $row['precio_sede_sf'],
					'especialidad' => strtoupper_total($row['especialidad']),
					'tipo_producto' => strtoupper_total($row['tipo_producto']),
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
	// public function obtener_convenio_cliente(){
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
	// 	$row = $this->model_convenio->m_cargar_convenio_cliente($allInputs);

	// 	$arrData['datos'] = $row;
 //    	$arrData['message'] = 'Descuento: ' . $row['porcentaje_dcto'] . '%';
 //    	$arrData['flag'] = 1;
	// 	if(empty($row)){
	// 		$arrData['flag'] = 0;
	// 		$arrData['message'] = 'No se encontró el descuento para este tipo de cliente';
	// 	}
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
	public function ver_popup_convenio()
	{
		$this->load->view('convenio/convenioFormView');
	}
	public function ver_popup_clientes_convenio()
	{
		$this->load->view('convenio/popup_pacientes_convenio');
	}	
	public function ver_popup_productos_convenio(){
		$this->load->view('convenio/popup_productos_convenio');
	}
	public function registrarEditarConvenio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
    	if( empty($allInputs['idtipocliente']) ){
	    	if($this->model_convenio->m_registrar_convenio($allInputs)){
	    		$arrData['idtipocliente'] = GetLastId('idtipocliente','tipo_cliente');
	    		$arrData['message'] = 'Los datos se registraron correctamente';
    			$arrData['flag'] = 1;
			}
    	}else{
    		if($this->model_convenio->m_editar_convenio($allInputs)){
    			$arrData['message'] = 'Los datos se editaron correctamente';
    			$arrData['flag'] = 1;
			}
    		
    	}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	
	public function anularConvenio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_convenio->m_anular_convenio($row['idtipocliente']) ){
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
		if( $this->model_convenio->m_afiliar_cliente_a_puntos($allInputs) ){
			if( $this->model_convenio->m_iniciar_puntaje_cliente($allInputs) ){
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
		if( $cliente = $this->model_convenio->m_comprobar_afiliacion_puntos($allInputs) ){
			if( $datos_puntos = $this->model_convenio->m_obtener_puntaje_cliente($cliente) ){
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

	public function cambiar_estado_producto_convenio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No pudo ser actualizado. Intente nuevamente';
    	$arrData['flag'] = 0;
		
		if(!empty($allInputs['idconvenioproductosede'])){
			$allInputs['idconvenioproductosede'] = (int)$allInputs['idconvenioproductosede'];
			if($this->model_convenio->m_actualizar_estado_producto_convenio($allInputs) ){
				$arrData['message'] = 'Ha sido actualizado correctamente';
	    		$arrData['flag'] = 1;
			}			
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function guardar_productos_convenio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No pudo ser actualizado. Intente nuevamente';
    	$arrData['flag'] = 0;

    	foreach ($allInputs['listaProductos'] as $key => $row) {
    		if((empty($row['precio_convenio']) && $row['precio_convenio'] != 0) 
    			|| (empty($row['porcentaje']) && $row['porcentaje'] != 0) ){
    			
    			$arrData['message'] = 'Debe llenar los campos Precio y Procentaje';
    			$arrData['flag'] = 0;
    			$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}

    		$row['precio_convenio'] = str_replace('S/.', '', $row['precio_convenio']); 

    		if(!is_numeric($row['precio_convenio']) ||  !is_numeric($row['porcentaje']) ){
    			$arrData['message'] = 'Los campos Precio y Procentaje deben ser númericos';
    			$arrData['flag'] = 0;
    			$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}
    	}
		
		$error = FALSE;
		$this->db->trans_start();
		if($this->model_convenio->m_update_porcentaje_convenio($allInputs['cliente'])){
    		foreach ($allInputs['listaProductos'] as $key => $row) {
	    		if(empty($row['idconvenioproductosede'])){
	    			$datos = array(
	    				'idproductopreciosede' => $row['idproductopreciosede'],
	    				'precio_convenio' => $row['precio_convenio'],
	    				'idtipocliente' => $allInputs['cliente']['idtipocliente'],
	    			);
	    			if(!$this->model_convenio->m_registrar_producto_convenio($datos) ){
						$error = TRUE;
					}
	    		}else{
					if(!$this->model_convenio->m_actualizar_producto_convenio($row) ){
						$error = TRUE;
					}
	    		}
	    	}			
			$this->db->trans_complete();

			if(!$error){
				$arrData['message'] = 'Ha sido guardado correctamente';
	    		$arrData['flag'] = 1;
			}
    	}
    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_producto(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No pudo ser actualizado. Intente nuevamente';
    	$arrData['flag'] = 0;
		
		$error = FALSE;
		$this->db->trans_start();
		foreach ($allInputs as $key => $row) {
			if(!empty($row['idconvenioproductosede'])){
				$data = array(
					'idconvenioproductosede' => (int)$row['idconvenioproductosede'],
					'estado' => 0
				);

				if(!$this->model_convenio->m_actualizar_estado_producto_convenio($data) ){
					$error = TRUE;
				}
			}			
		}
		$this->db->trans_complete();

		if(!$error){
			$arrData['message'] = 'Ha sido actualizado correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));	
	}

	public function cambiar_estado_lista_productos_convenio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No pudo ser actualizado. Intente nuevamente';
    	$arrData['flag'] = 0;
		
		$error = FALSE;
		$this->db->trans_start();
		foreach ($allInputs['listaProductos'] as $key => $row) {
			if(!empty($row['idconvenioproductosede'])){
				$data = array(
					'idconvenioproductosede' => (int)$row['idconvenioproductosede'],
					'estado' => $allInputs['estado']
				);

				if(!$this->model_convenio->m_actualizar_estado_producto_convenio($data) ){
					$error = TRUE;
				}
			}			
		}
		$this->db->trans_complete();

		if(!$error){
			$arrData['message'] = 'Ha sido actualizado correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));	
	}

	public function lista_cliente_no_agre_convenio_autocompletar() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 		
		$lista = $this->model_convenio->m_cargar_cliente_no_agre_convenio_autocompletar($allInputs);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idcliente' => $row['idcliente'],
					'num_documento' => $row['num_documento'],
					'nombres' => strtoupper_total($row['nombres']),
					'apellido_paterno' => strtoupper_total($row['apellido_paterno']),
					'apellido_materno' => strtoupper_total($row['apellido_materno']),
					'sexo' => strtoupper_total($row['sexo']),
					// 'edad' => devolverEdad($row['fecha_nacimiento']),
					'edad' => $row['edad'],
					'paciente' => strtoupper_total($row['nombres']) . ' ' . strtoupper_total($row['apellido_paterno']) . ' ' . strtoupper_total($row['apellido_materno']),
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

	
	public function update_cliente_convenio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'No pudo ser agregado el cliente. Intente nuevamente';
    	$arrData['flag'] = 0;		

		if($this->model_convenio->m_update_cliente_convenio($allInputs) ){
			$arrData['message'] = 'Ha sido agregado el cliente correctamente';
    		$arrData['flag'] = 1;
		}			
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}