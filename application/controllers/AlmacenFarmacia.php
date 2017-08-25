<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AlmacenFarmacia extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_almacen_farmacia','model_config'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_almacenes_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_almacen_farmacia->m_cargar_almacenes_session($paramPaginate);
		$totalRows = $this->model_almacen_farmacia->m_count_almacenes_session($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_alm'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_alm'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array( 
					'id' => $row['idalmacen'], 
					'almacen' => strtoupper($row['nombre_alm']),
					'sede' => strtoupper($row['sede']), 
					'empresa' => strtoupper($row['empresa_admin']),
					'idsedeempresaadmin' =>$row['idsedeempresaadmin'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_alm']
					)
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
	public function lista_almacenes_para_medicamento_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(!empty($allInputs['idmedicamento'])){ // listado para edicion
			$idmedicamento = $allInputs['idmedicamento'];
			$lista = $this->model_almacen_farmacia->m_cargar_almacenes_medicamento_session($idmedicamento);
			$totalRows = $this->model_almacen_farmacia->m_count_almacenes_medicamento_session($idmedicamento);
		}else{
			$lista = $this->model_almacen_farmacia->m_cargar_almacenes_medicamento_session(); // listado para registro de nuevo medicamento
			$totalRows = $this->model_almacen_farmacia->m_count_almacenes_medicamento_session();
		}
		
		$arrListado = array();
		
		if(!empty($allInputs['idmedicamento']) ){ // edicion
			foreach ($lista as $row) {
				array_push($arrListado, 
					array( 
						'id' => $row['idalmacen'], 
						'almacen' => strtoupper($row['nombre_alm']), 
						'sede' => strtoupper($row['sede']), 
						'empresa' => strtoupper($row['empresa_admin']),
						'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
						'precio' => $row['precio_venta'],
						'idsedeempresaadmin' =>$row['idsedeempresaadmin'],
						'idsubalmacen_venta' => $row['idsubalmacen'], 
						'subalmacen' => strtoupper($row['nombre_salm'])
					)
				);
			}
		}else{
			foreach ($lista as $row) {
				if( $row['estado_alm'] == 1 ){
					$estado = 'HABILITADO';
					$clase = 'label-success';
				}
				if( $row['estado_alm'] == 2 ){
					$estado = 'DESHABILITADO';
					$clase = 'label-default';
				}
				array_push($arrListado, 
					array( 
						'id' => $row['idalmacen'], 
						'almacen' => strtoupper($row['nombre_alm']),
						'sede' => strtoupper($row['sede']), 
						'empresa' => strtoupper($row['empresa_admin']),
						'idsedeempresaadmin' =>$row['idsedeempresaadmin'],
						'idsubalmacen_venta' => $row['idsubalmacen'], 
						'subalmacen' => strtoupper($row['nombre_salm']),
						'estado' => array(
							'string' => $estado,
							'clase' =>$clase,
							'bool' =>$row['estado_alm']
						)
					)
				);
			}
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
	public function lista_almacenes_edicion_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_almacen_farmacia->m_cargar_almacenes_edicion_session($allInputs['idmedicamento']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idalmacen'], 
					'almacen' => strtoupper($row['nombre_alm']), 
					'sede' => strtoupper($row['sede']), 
					'empresa' => strtoupper($row['empresa_admin']),
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'precio' => $row['precio_venta'],
					'idsedeempresaadmin' =>$row['idsedeempresaadmin'],
					'idsubalmacen_venta' => $row['idsubalmacen'], 
					'subalmacen' => strtoupper($row['nombre_salm'])
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
	public function lista_almacenes_cbo_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_almacen_farmacia->m_cargar_almacenes_cbo_para_traslado_session();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idalmacen'],
					'idalmacen' => $row['idalmacen'],
					'descripcion' => strtoupper_total($row['nombre_alm']),
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'sede' => strtoupper_total($row['sede']),
					'direccion' => strtoupper_total($row['direccion']),
					'ruc' => $row['ruc'],
					'idempresaadmin' => $row['idempresaadmin'],
					'empresa' => strtoupper_total($row['empresa_admin'])
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
	public function lista_almacenes_destino_de_empresa()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_almacen_farmacia->m_cargar_almacenes_destino_de_empresa_para_traslado_session($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idalmacen'],
					'idalmacen' => $row['idalmacen'],
					'descripcion' => strtoupper_total($row['nombre_alm']),
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'sede' => strtoupper_total($row['sede']),
					'direccion' => strtoupper_total($row['direccion']),
					'ruc' => $row['ruc'],
					'idempresaadmin' => $row['idempresaadmin'],
					'empresa' => strtoupper_total($row['empresa_admin'])
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
	public function lista_sub_almacenes_de_almacen_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit();
		$lista = $this->model_almacen_farmacia->m_cargar_sub_almacenes_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm'])
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
	public function lista_sub_almacenes_de_almacen_preparado_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit();
		$lista = $this->model_almacen_farmacia->m_cargar_sub_almacenes_preparado_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm'])
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
	public function lista_sub_almacenes_de_almacen_excepto_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_almacen_farmacia->m_cargar_sub_almacenes_excepto_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm'])
				)
			);
		} 
    	$arrData['datos'] = $arrListado; 
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay sub Almacenes. No se puede realizar la operación';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_sub_almacenes_venta_por_id_almacen_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_almacen_farmacia->m_obtener_subalmacen_venta($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm'])
				)
			);
		} 
    	$arrData['datos'] = $arrListado; 
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay sub Almacenes.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_sub_almacenes_para_venta_por_almacen_cbo()
	{
		
		$lista = $this->model_almacen_farmacia->m_cargar_sub_almacenes_para_venta_cbo();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm']),
					'idalmacen' => $row['idalmacen'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin']
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
	public function lista_sub_almacenes_venta_sede_cbo()
	{
		
		$lista = $this->model_almacen_farmacia->m_cargar_sub_almacenes_venta_sede_cbo();
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'descripcion' => strtoupper($row['nombre_salm']),
					'idalmacen' => $row['idalmacen'],
					'idsedeempresaadmin' => $row['idsedeempresaadmin']
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
	/* TRASLADOS */
	public function lista_traslados()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_almacen_farmacia->m_cargar_traslados($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'idmovimiento1' => $row['idmovimiento1'],
					'idmovimiento2' => $row['idmovimiento2'],
					'idalmacen' => $row['idalmacen'],
					'almacen' => strtoupper($row['nombre_alm']),
					'idsubalmacen1' => $row['idsubalmacen1'],
					'subAlmacenOrigen' => strtoupper($row['subAlmacenOrigen']),
					'idsubalmacen2' => $row['idsubalmacen2'],
					'subAlmacenDestino' => strtoupper($row['subAlmacenDestino']),
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'producto' => strtoupper($row['denominacion']),
					'cantidad' => $row['cantidad']
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
	public function lista_Productos_SubAlmacen(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_almacen_farmacia->m_cargar_productos_subalmacen($datos, $paramPaginate);
		$totalRows = $this->model_almacen_farmacia->m_count_productos_subalmacen($datos, $paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'producto' => $row['denominacion'],
					'stock' => $row['stock_actual_malm']
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
	public function realizar_traslado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if($allInputs['idsubalmacen1'] !== $allInputs['idsubalmacen2']){
	    	//$this->db->trans_start();
	    	$this->db->trans_begin();
	    	// CREAR LA SALIDA
			if( $this->model_almacen_farmacia->m_registrar_salida($allInputs) ){
				$allInputs['idmovimiento'] = GetLastId('idmovimiento','far_movimiento'); 
			}
			foreach ($allInputs['productos'] as $key => $producto) {
				$producto['idmovimiento'] = $allInputs['idmovimiento'];
				$this->model_almacen_farmacia->m_registrar_detalle_salida($producto);
				$this->model_almacen_farmacia->m_actualizar_medicamento_almacen_salida($producto);
			}
			// CREAR LA ENTRADA
			$allInputs['idtrasladoorigen'] = $allInputs['idmovimiento'];
			if( $this->model_almacen_farmacia->m_registrar_entrada($allInputs) ){
				$allInputs['idmovimiento2'] = GetLastId('idmovimiento','far_movimiento'); 
			}
			foreach ($allInputs['productos'] as $key => $producto) {
				$producto['idmovimiento'] = $allInputs['idmovimiento2'];
				$producto['idalmacen'] = $allInputs['almacen']['id']; // necesario para verificar y registrar un ingreso de medicamento
				$producto['idsubalmacen'] = $allInputs['idsubalmacen2']; // necesario para registrar un ingreso de medicamento

				// VERIFICAR SI EL PRODUCTO EXISTE EN SUB ALMACEN DESTINO
				if( $idmedicamentoalmacen = $this->model_almacen_farmacia->m_verificar_producto_destino($producto) ){
					$producto['idmedicamentoalmacen'] = $idmedicamentoalmacen['idmedicamentoalmacen'];
					$this->model_almacen_farmacia->m_registrar_detalle_entrada($producto); // registramos con el idmedicamentoalmacen del destino
					$this->model_almacen_farmacia->m_actualizar_medicamento_almacen_entrada($producto);
				}else{
					// si no existe el producto primero lo ingresamos luego creamos el detalle
					$this->model_almacen_farmacia->m_registrar_medicamento_almacen_entrada($producto);
					$producto['idmedicamentoalmacen'] = GetLastId('idmedicamentoalmacen','far_medicamento_almacen');
					$this->model_almacen_farmacia->m_registrar_detalle_entrada($producto);
				}	
			}
	    	if ($this->db->trans_status() === FALSE)
			{
			    $this->db->trans_rollback();
			}
			else
			{
			    $this->db->trans_commit();
			    $arrData['message'] = 'El traslado se realizo correctamente';
	    		$arrData['flag'] = 1;
			}
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	
	public function ver_popup_formulario()
	{
		$this->load->view('almacenFarmacia/almacenFarmacia_formView');
	}
	public function ver_popup_traslado()
	{
		$this->load->view('almacenFarmacia/traslados_formView');
	}
	public function ver_popup_entrada()
	{
		$this->load->view('almacenFarmacia/entradas_formView');
	}
	public function ver_popup_salida()
	{
		$this->load->view('almacenFarmacia/salidas_formView');
	}
	public function ver_popup_detalle_salida()
	{
		$this->load->view('almacenFarmacia/detalle_salidas_formView');
	}
	public function ver_popup_detalle_traslado()
	{
		$this->load->view('almacenFarmacia/detalle_traslado_formView');
	}
	public function ver_popup_detalle_salida_medicamento()
	{
		$this->load->view('almacenFarmacia/detalle_salidas_medicamento_formView');
	}
	public function registrar_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$this->db->trans_start(); 
		if($this->model_almacen_farmacia->m_registrar_almacen($allInputs)){
			$allInputs['idalmacen'] = GetLastId('idalmacen','far_almacen'); 
			$allInputs['nombre_salm'] = 'CENTRAL'; 
			$allInputs['es_principal'] = 1 ; 
			$allInputs['tiposubalmacen'] = 1 ; 
			if($this->model_almacen_farmacia->m_registrar_subalmacen($allInputs)){
				$arrData['message'] = 'Se registraron los datos correctamente';
				$arrData['flag'] = 1;
			}

		}

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

		if($this->model_almacen_farmacia->m_editar_almacen($allInputs)){
			//$allInputs['idalmacen'] = GetLastId('idalmacen','far_almacen'); 
			$arrData['message'] = 'Se registraron los datos correctamente';
			$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_almacen_farmacia->m_anular_almacen($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_almacen_farmacia->m_habilitar_almacen($row['id']) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_almacen_farmacia->m_deshabilitar_almacen($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/*---------------------------------------------------------------------------*/
	/*--------------------       SUB ALMACEN     --------------------------------*/

	public function ver_popup_formulario_subalmacen()
	{
		$this->load->view('almacenFarmacia/subalmacenFarmacia_formView');
	}

	public function lista_subalmacenes()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_almacen_farmacia->m_cargar_subalmacenes($paramPaginate,$paramDatos);
		$totalRows = $this->model_almacen_farmacia->m_count_subalmacenes($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_salm'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_salm'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array( 
					'id' => $row['idsubalmacen'], 
					'idalmacen' => $row['idalmacen'], 
					'nombre_salm' => strtoupper($row['nombre_salm']),
					'es_principal' => ($row['es_principal'] == 1 ? 'SI':'NO'), 
					'idtiposubalmacen' => $row['idtiposubalmacen'],
					'descripcion_tsa' => strtoupper($row['descripcion_tsa']),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_salm']
					)
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

	public function registrar_subalmacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$allInputs['es_principal'] = 2;
    	//$this->db->trans_start(); 
		if($this->model_almacen_farmacia->m_registrar_subalmacen($allInputs)){
			//$allInputs['idventa'] = GetLastId('idventa','venta'); 
			$arrData['message'] = 'Se registraron los datos correctamente';
			$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar_subalmacen_en_grid()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron editar los datos';
    	$arrData['flag'] = 0;
		if( $this->model_almacen_farmacia->m_editar_subalmacen_en_grid($allInputs) ){
			$arrData['message'] = 'Se Editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_subalmacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_almacen_farmacia->m_anular_subalmacen($allInputs) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}


}