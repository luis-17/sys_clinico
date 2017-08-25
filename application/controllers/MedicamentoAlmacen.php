<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MedicamentoAlmacen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper', 'fechas_helper','bd_helper'));
		$this->load->model(array('model_medicamento_almacen','model_medicamento','model_cliente'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_medicamentos_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento_almacen->m_cargar_medicamentos_almacen($paramPaginate,$paramDatos); 
		$totalRows = $this->model_medicamento_almacen->m_count_medicamentos_almacen($paramPaginate,$paramDatos); 
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_fma'] == 1 ){
				$estadoMed = 'HABILITADO';
				$claseMed = 'label-success';
			}
			if( $row['estado_fma'] == 2 ){ // precio_compra_ult precio_ultima_compra
				$estadoMed = 'DESHABILITADO';
				$claseMed = 'label-default';
			}
			array_push($arrListado,
				array(
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'], 
					'idmedicamento' => $row['idmedicamento'], 
					'medicamento' => strtoupper($row['denominacion']),
					'precio_compra' => $row['precio_compra'],
					'precio_compra_str' => $row['precio_compra_str'],
					'precio_ultima_compra' => $row['precio_ultima_compra'],
					'precio_ultima_compra_str' => $row['precio_ultima_compra_str'],
					'utilidad_porcentaje' => $row['utilidad_porcentaje'],
					'utilidad_valor' => $row['utilidad_valor'],
					'utilidad_valor_str' => $row['utilidad_valor_str'],
					'precio_venta' => $row['precio_venta'],
					'precio_venta_kairos' => $row['precio_venta_kairos'],
					'precio_venta_str' => $row['precio_venta_str'],
					'precio_venta_kairos_str' => $row['precio_venta_kairos_str'],
					'porcentaje_venta_kairos_str' => $row['porcentaje_venta_kairos_str'],
					'stock_actual' => $row['stock_actual'],
					'stock_inicial' => $row['stock_inicial'],
					'stock_entradas' => $row['stock_entradas'],
					'stock_salidas' => $row['stock_salidas'],
					'stock_actual_malm' => $row['stock_actual_malm'],
					'stock_minimo' => $row['stock_minimo'],
					'stock_critico' => $row['stock_critico'],
					'stock_maximo' => $row['stock_maximo'],
					'costo_medio_malm' => $row['costo_medio_malm'],
					'costo_min_malm' => $row['costo_min_malm'],
					'costo_max_malm' => $row['costo_max_malm'],
					'margen_utilidad' => $row['margen_utilidad'],
					'estadoMed' => array(
						'string' => $estadoMed,
						'clase' => $claseMed,
						'bool' => $row['estado_fma']
					)
				)
			);
		}
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
	public function lista_medicamento_subalmacen_venta(){ // PARA REPORTE DE CONTABILIDAD
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_subalmacen_venta($paramPaginate,$paramDatos); 
		$totalRows = $this->model_medicamento_almacen->m_count_medicamento_subalmacen_venta($paramPaginate,$paramDatos);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idmedicamento' => $row['idmedicamento'], 
					'medicamento' => strtoupper($row['medicamento']),
					'unidad_medida' => $row['presentacion'],
				)
			);
		}
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
	public function lista_preparados_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento_almacen->m_cargar_preparados_almacen($paramPaginate,$paramDatos); 
		$totalRows = $this->model_medicamento_almacen->m_count_preparados_almacen($paramPaginate,$paramDatos); 
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_fma'] == 1 ){
				$estadoMed = 'HABILITADO';
				$claseMed = 'label-success';
			}
			if( $row['estado_fma'] == 2 ){
				$estadoMed = 'DESHABILITADO';
				$claseMed = 'label-default';
			}
			array_push($arrListado,
				array(
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'], 
					'idmedicamento' => $row['idmedicamento'], 
					'medicamento' => strtoupper($row['medicamento']),
					'stock_inicial' => $row['stock_inicial'],
					'stock_entradas' => $row['stock_entradas'],
					'stock_salidas' => $row['stock_salidas'],
					'stock_actual_malm' => $row['stock_actual_malm'],
					'precio_compra' => $row['precio_compra'],
					'precio_compra_sf' => $row['precio_compra_sf'],					
					'precio_venta' => $row['precio_venta'],
					
					'precio_venta_sf' => $row['precio_venta_sf'],
					'utilidad_porcentaje' => $row['precio_venta_sf'] == 0? 0: (($row['precio_venta_sf'] - $row['precio_compra_sf'] ) / $row['precio_compra_sf'] * 100),
					'estadoMed' => array(
						'string' => $estadoMed,
						'clase' => $claseMed,
						'bool' => $row['estado_fma']
					)
				)
			);
		}
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

	public function ver_popup_agregar_medicamentos_al_almacen()
	{
		$this->load->view('medicamento/popupAgregarMedicamentoAlmacen'); 
	}
	public function ver_popup_kardex()
	{
		$this->load->view('kardex-farm/popupKardex_view'); 
	}
	public function ver_popup_formulario_stocks()
	{
		$this->load->view('almacenFarmacia/popupStocksMedicamento_view'); 
	}
	public function ver_popup_historial_precios(){
		$this->load->view('almacenFarmacia/popupHistorialPrecios_view'); 
	}
	public function ver_popup_motivo_margen_utilidad(){
		$this->load->view('almacenFarmacia/popupMotivoMargenUtilidad'); 
	}
	public function lista_medicamentos_sin_este_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento->m_cargar_medicamentos_sin_este_almacen($paramPaginate,$paramDatos); 
		$totalRows = $this->model_medicamento->m_count_medicamentos_sin_este_almacen($paramPaginate,$paramDatos); 
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['generico'] == 1 ){
				$estadoGen = 'GENERICO';
				$claseGen = 'label-info';
			}
			if( $row['generico'] == 2 ){
				$estadoGen = 'DE MARCA';
				$claseGen = 'label-warning';
			}
			array_push($arrListado,
				array(
					'idmedicamento' => $row['idmedicamento'], 
					'codigo_barra' => $row['codigo_barra'], 
					'medicamento' => strtoupper($row['medicamento']),
					'idpresentacion' => ( $row['generico'] == 1 ) ? $row['idunidadmedida'] : $row['idpresentacion'],
					'presentacion' => $row['presentacion'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'idmedidaconcentracion' => $row['idmedidaconcentracion'],
					'medidaconcentracion' => $row['descripcion_mc'],
					'codigo_barra'=> $row['codigo_barra'],
					// 'contenido' => $row['contenido'],
					'generico' => (int)$row['generico'],
					'estadoGen' => array(
						'string' => $estadoGen,
						'clase' =>$claseGen,
						'bool' =>$row['generico']
					)
				)
			);
		}
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
	public function lista_medicamento_almacen_venta()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		//var_dump($allInputs); exit(); 
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_venta_session($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idunidadmedida' => $row['presentacion'],
					'descripcion' => strtoupper($row['medicamento']), 
					'precio' => $row['precio_venta'],
					'precioSF' => (float)$row['precio_venta_sf'],
					'stockActual' => (int)$row['stock_actual_malm'],
					'stockTemporal' => (int)$row['stock_temporal'],   // SE AÑADIO EL STOCK TEMPORAL
					'stockMinimo' => (int)$row['stock_minimo'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idempresaadmin' => $row['idempresaadmin']
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = 'Producto encontrado';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró el producto.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function lista_medicamento_almacen_venta_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); // ['searchColumn'],$allInputs['searchText']
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_venta_session_autocomplete($allInputs);
		// $fCliente = $this->model_cliente->m_cargar_este_cliente(); 
		// var_dump($allInputs); exit(); 
		$arrListado = array();
		foreach ($lista as $row) { 
			
			array_push($arrListado,
				array( 
					'id' => $row['idmedicamento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idunidadmedida' => $row['presentacion'],
					'descripcion_stock' => strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . $row['stock_actual_malm'] . ' UND.</span> |' . $row['precio_venta'], 
					'descripcion' => strtoupper($row['medicamento']),
					'precio' => $row['precio_venta'],
					'precioSF' => (float)$row['precio_venta_sf'],
					'stockActual' => (int)$row['stock_actual_malm'],
					'stockTemporal' => (int)$row['stock_temporal'],   // SE AÑADIO EL STOCK TEMPORAL
					'stockMinimo' => (int)$row['stock_minimo'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idempresaadmin' => $row['idempresaadmin'],
					'excluye_igv' => $row['excluye_igv'],
					'laboratorio' => $row['nombre_lab'],
					'si_bonificacion' => $row['si_bonificacion'],
					'edicion_precio_en_venta' => $row['edicion_precio_en_venta']
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
	public function lista_preparado_almacen_venta_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_medicamento_almacen->m_cargar_preparado_almacen_venta_session_autocomplete($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array( 
					'id' => $row['idmedicamento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					//'idunidadmedida' => $row['presentacion'],
					'descripcion_stock' => strtoupper($row['medicamento']) . ' | ' . $row['precio_venta'], 
					'descripcion' => strtoupper($row['medicamento']),
					'precio' => $row['precio_venta'],
					'precioSF' => (float)$row['precio_venta_sf'],
					'stockActual' => (int)$row['stock_actual_malm'],
					'stockTemporal' => (int)$row['stock_temporal'],   // SE AÑADIO EL STOCK TEMPORAL
					'stockMinimo' => (int)$row['stock_minimo'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idempresaadmin' => $row['idempresaadmin'],
					'excluye_igv' => $row['excluye_igv'],
					'si_bonificacion' => $row['si_bonificacion'],
					'edicion_precio_en_venta' => $row['edicion_precio_en_venta'],
					'precio_costo' => $row['precio_compra'],
					'categoria' => $row['categoria_jj'],
					'uso' => $row['uso_jj'],
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
	public function cargar_stocks_medicamento(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_medicamento_almacen->m_cargar_stocks_medicamento($paramPaginate,$paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_stocks_medicamento($paramPaginate,$paramDatos); 
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, array(
				'sede' => $row['sede'],
				'empresa' => $row['empresa'],
				'almacen' => $row['nombre_alm'],
				'subalmacen' => $row['nombre_salm'],
				'stock' => $row['stock_actual_malm'],
				'stock_total' => $row['stock_actual']
				)
			);
		}
		
		//var_dump($lista); exit();
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
	public function cargar_historial_precios(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);

		$paramDatos = $allInputs['datos'];
		$lista = $this->model_medicamento_almacen->m_cargar_historial_precios($paramDatos);

		$arrListado = array();
		foreach ($lista as $row) {
			if( end($lista) == $row ){
				$arrData['precio_inicial'] = !empty($row['precio_venta_anterior'])? $row['precio_venta_anterior'] : $row['precio_venta'];
			}
			array_push($arrListado, array(
				'precio_venta' => $row['precio_venta'],
				'precio_venta_anterior' => $row['precio_venta_anterior'],
				'precio_venta_actual' => $row['precio_venta_actual'],
				'motivo' => $row['motivo'],
				'iduser' => $row['idusers'],
				'usuario' => $row['nombres'] . ', ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'],
				'fecha_cambio' => formatoFechaReporte($row['fecha_cambio'])

				)
			);
		}
		
		//var_dump($lista); exit();
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
	public function lista_medicamento_almacen_busqueda_venta(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_busqueda_venta($paramPaginate,$paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_medicamento_almacen_busqueda_venta($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'stock' => $row['stock_actual_malm'],
					'stock_central' => $row['stock_central'],
					'precio' => $row['precio_venta'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab']
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
	public function lista_medicamento_almacen_busqueda_atencion_medica(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_busqueda_atencion_medica($paramPaginate,$paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_medicamento_almacen_busqueda_atencion_medica($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'stock' => $row['stock_actual_malm'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab']
				)
			);
		}
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
	/* ============ MANTENIMIENTO ===============*/
	public function agregar_medicamento_a_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0; 
    	// $precioVacio = FALSE;
    	// foreach ($allInputs['medicamentos'] as $key => $row) {
    	// 	if( empty($row['precio']) || !(is_numeric($row['precio'])) ){
    	// 		$precioVacio = TRUE;
    	// 	}
    	// }
    	// if( $precioVacio ){ 
    	// 	$arrData['message'] = 'Falta agregar el precio de venta';
    	// 	$arrData['flag'] = 0;
    	// 	$this->output
		   //  	->set_content_type('application/json')
		   //  	->set_output(json_encode($arrData));
		   //  return;
    	// }
    	$this->db->trans_start();
    	foreach ($allInputs['medicamentos'] as $key => $row) { 
    		$row['id'] = $allInputs['idalmacen'];
    		$row['idsubalmacen'] = $allInputs['idsubalmacen'];
    		if( $this->model_medicamento->m_registrar_medicamento_en_almacen($row) ){ 
				$arrData['message'] = 'Se registraron los datos correctamente';
				$arrData['flag'] = 1;
			}
    	}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_medicamento_almacen_inline()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// solo sistemas modifica stock manualmente 
    	if( $this->sessionHospital['key_group'] <> 'key_sistemas' && ( $allInputs['column'] == 'stock_inicial' || $allInputs['column'] == 'stock_entradas' || $allInputs['column'] == 'stock_salidas' ) ){ 
    		$arrData['message'] = 'Usuario no autorizado para modificar estos datos.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
    	}
    	// solo sistemas, logística, gerencia y gestor de farmacia pueden modificar precios 
    	if( !($this->sessionHospital['key_group'] == 'key_sistemas') && !($this->sessionHospital['key_group'] == 'key_gerencia') && 
    		!($this->sessionHospital['key_group'] == 'key_admin_far') && !($this->sessionHospital['key_group'] == 'key_logistica') 
    	){ 
    		$arrData['message'] = 'Usuario no autorizado para modificar estos datos.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
    	}

    	$boolHistorial = FALSE;
    	$allInputs['precio_venta_anterior'] = substr($allInputs['precio_venta'], 4);

    	if( $allInputs['column'] == 'stock_inicial' || $allInputs['column'] == 'stock_entradas' || $allInputs['column'] == 'stock_salidas' ){ 
    		$allInputs['stock_actual_malm'] = ($allInputs['stock_inicial'] + $allInputs['stock_entradas']) - $allInputs['stock_salidas']; 
    	}else{
    		switch ($allInputs['column']) { 
    			case 'utilidad_porcentaje':
	    			$allInputs['precio_venta_str'] = (($allInputs['utilidad_porcentaje'] / 100) * $allInputs['precio_ultima_compra_str'] + $allInputs['precio_ultima_compra_str']) ; 
		    		$allInputs['utilidad_valor_str'] = $allInputs['precio_venta_str'] - $allInputs['precio_ultima_compra_str'];
		    		break;
		    	case 'utilidad_valor_str':
			    	$allInputs['precio_venta_str'] = $allInputs['precio_ultima_compra_str'] + $allInputs['utilidad_valor_str'];
		    		if( $allInputs['precio_ultima_compra_str'] == 0 ){
		    			$allInputs['utilidad_porcentaje'] = 100;
		    		}else{
						$allInputs['utilidad_porcentaje'] = (($allInputs['precio_venta_str'] - $allInputs['precio_ultima_compra_str']) / $allInputs['precio_ultima_compra_str']) * 100; 
		    		}
		    		break;
		    	case 'precio_venta_str': 
			    	$allInputs['utilidad_valor_str'] = $allInputs['precio_venta_str'] - $allInputs['precio_ultima_compra_str'];
		    		if( $allInputs['precio_venta_kairos_str'] == 0){
		    			$allInputs['porcentaje_venta_kairos_str'] = 0;
		    		}else{
			    		$allInputs['porcentaje_venta_kairos_str'] = ($allInputs['precio_venta_str'] / $allInputs['precio_venta_kairos_str']) * 100 ;
		    		}
		    		if( $allInputs['precio_ultima_compra_str'] == 0 ){
		    			$allInputs['utilidad_porcentaje'] = 100;
		    		}else{
						$allInputs['utilidad_porcentaje'] = (($allInputs['precio_venta_str'] - $allInputs['precio_ultima_compra_str']) / $allInputs['precio_ultima_compra_str']) * 100;
		    		}
		    		break;
		    	case 'precio_venta_kairos_str': 
			    	if( $allInputs['precio_venta_str'] == 0){
		    			$allInputs['porcentaje_venta_kairos_str'] = 0;
		    		}else{
			    		$allInputs['porcentaje_venta_kairos_str'] = ($allInputs['precio_venta_str'] / $allInputs['precio_venta_kairos_str']) * 100 ;
		    		}
		    		break;
		    	case 'porcentaje_venta_kairos_str': 
			    	if($allInputs['precio_venta_kairos_str'] > 0){
			    		$allInputs['precio_venta_str'] = ($allInputs['porcentaje_venta_kairos_str'] / 100) * $allInputs['precio_venta_kairos_str'] ;
		    		}
		    		break;
    		}

	    	if( $allInputs['precio_venta_str'] != $allInputs['precio_venta_anterior'] ){ 
	    		// Cambió el Precio de venta, ingresar al historial');
	    		$boolHistorial = TRUE; 
	    	}
	    	if( empty($allInputs['motivo']) ){ 
		    	if( $allInputs['utilidad_porcentaje'] < $allInputs['margen_utilidad'] ){
					$arrData['message'] = 'El Porcentaje de Utilidad de ' . number_format($allInputs['utilidad_porcentaje'],0) . ' % es menor que el margen de utilidad de este almacen ( ' . number_format($allInputs['margen_utilidad'], 0) .'%).  Por favor ingrese un motivo si aún desea editar los valores de este producto';
					$arrData['flag'] = 2;
					$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
					return;
				}
	    	}
    	}
    	$allInputs['utilidad_valor'] = $allInputs['utilidad_valor_str'];
    	$allInputs['precio_venta'] = $allInputs['precio_venta_str'];
    	$allInputs['precio_compra'] = $allInputs['precio_compra_str'];
    	
    	// var_dump($allInputs); exit();

    	$this->db->trans_start();
    	if( $this->model_medicamento_almacen->m_editar_inline_medicamento_en_almacen($allInputs) ){ 
			if( $boolHistorial ){
				$this->model_medicamento_almacen->m_registrar_historial_precio($allInputs);
			}
    		$listaMedicamento = $this->model_medicamento_almacen->m_listar_este_medicamento_en_almacenes($allInputs['idmedicamento']);
    		if(!(empty($listaMedicamento))){ 
    			$allInputs['stock_actual_modificado'] = 0;
    			foreach ($listaMedicamento as $key => $row) {
    				$allInputs['stock_actual_modificado'] += $row['stock_actual_malm'];
    			}
				if($this->model_medicamento->m_actualizar_stock_medicamento($allInputs)){
	    			$arrData['message'] = 'Se editaron los registros correctamente';
					$arrData['flag'] = 1;
	    		}
    		}
		}
    	$this->db->trans_complete();
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar_deshabilitar_medicamento_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
    	if( $allInputs['estadoMed']['bool'] == 1 ){
			if( $this->model_medicamento_almacen->m_deshabilitar_medicamento_almacen($allInputs) ){ 
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
				$arrData['flag'] = 1;
			}
    	}
    	if( $allInputs['estadoMed']['bool'] == 2 ){
			if( $this->model_medicamento_almacen->m_habilitar_medicamento_almacen($allInputs) ){ 
				$arrData['message'] = 'Se habilitaron los datos correctamente';
				$arrData['flag'] = 1;
			}
    	}
    	$this->db->trans_complete();
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_medicamento_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	$existeMedicamentoMovimiento = false ;
    	$medicamentos = '';
    	foreach ($allInputs as $row) {
			if( $lista=$this->model_medicamento_almacen->m_buscar_medicamento_movimiento_detalle($row['idmedicamento']) ){
		    	$existeMedicamentoMovimiento = true ;
		    	$medicamentos .= $lista['medicamento'].'<br/> ';
			}
		}
    	if( $existeMedicamentoMovimiento === true ){ 
    		$arrData['message'] = 'Los siguientes medicamentos no se pueden anular: <br/>'.$medicamentos;
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	foreach ($allInputs as $row) {
			if( $this->model_medicamento_almacen->m_anular_medicamento_almacen($row['idmedicamentoalmacen']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function verificar_subalmacen_venta_a_cliente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		//$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['venta_a_cliente'] = false;
    	$lista=$this->model_medicamento_almacen->m_verificar_subalmacen_venta_a_cliente($allInputs['datos']);
    	if($lista['venta_a_cliente'] == 1){
    		$arrData['venta_a_cliente'] = true;
    	}
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cargar_stock_inicial (){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( $row = $this->model_medicamento_almacen->m_cargar_stock_inicial($allInputs['datos']) ){
			$row['valor_entrada'] = $row['precio_compra'] * $row['stock_inicial'];
			$arrData['datos'] = $row;
		}
		//$arrData['datos'] = $arrListado;
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_kardex(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = ''; 
    	$arrData['flag'] = 0; 
    	// $arrListado = array(); 
    	//var_dump($allInputs); exit(); 
    	$arrParams = array( 
    		'idmedicamentoalmacen'=> $allInputs['datos']['idmedicamentoalmacen'] 
    	);
		$arrListado = obtenerKardexValorizado($arrParams);

		$arrFiltrado = array();
		if( !empty($allInputs['busqueda']['desde']) && !empty($allInputs['busqueda']['hasta']) ){ 
			foreach ($arrListado as $row2) {
				//var_dump($row2['fecha']);
				if( strtotime($row2['fecha']) >= strtotime($allInputs['busqueda']['desde']) &&
					strtotime($row2['fecha']) <= strtotime($allInputs['busqueda']['hasta']) ){
					array_push($arrFiltrado, array(
						'fecha_movimiento' => $row2['fecha_movimiento'],
						'fecha' => $row2['fecha'],
						'tipo_movimiento' => $row2['tipo_movimiento'],
						'entrada' => $row2['entrada'],
						'salida' => $row2['salida'],
						'precio_unitario' => $row2['precio_unitario'],
						'valor_entrada' => $row2['valor_entrada'],
						'valor_salida' => $row2['valor_salida'],
						'cantidad_saldo' => $row2['cantidad_saldo'],
						'valor_saldo' => $row2['valor_saldo'],
						'promedio' => $row2['promedio']
						)
					);
				}
			}
			$arrData['datos'] = $arrFiltrado;
		}else{
			$arrData['datos'] = $arrListado;
		}

		if( !empty($arrData['datosFiltrados']) ){ 
			$arrData['message'] = 'OK';
    		$arrData['flag'] = 1;
		}
		// $arrData['datosFiltrados'] = $arrFiltrado;
		
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ================================ ALERTAS ==========================================
	public function lista_medicamento_por_vencer(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		//var_dump($allInputs); exit();
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_vencer($paramPaginate, $paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_medicamento_almacen_por_vencer($paramPaginate, $paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_vencer'] == 1 ){
				$string = 'VENCIDO';
				$claseMed = 'label-danger';
			}elseif( $row['estado_vencer'] == 2 ){
				$string = 'MES ACTUAL';
				$claseMed = 'label-warning';
			}elseif( $row['estado_vencer'] == 3 ){
				$string = 'DE 2 A 3 MESES';
				$claseMed = 'label-info';
			}
			else{
				$string = '';
				$claseMed = '';
				$estadoMed = 0;
			}
			array_push($arrListado,
				array(
					'num_lote' => $row['num_lote'],
					'idmovimiento' => $row['idmovimiento'],
					'iddetallemovimiento' => $row['iddetallemovimiento'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'medicamento' => $row['denominacion'],
					'almacen' => $row['almacen'],
					'medida' => $row['descripcion_pres'],
					'cantidad' => $row['cantidad'],
					'laboratorio' => $row['nombre_lab'],
					'fecha_vencimiento' => darFormatoDMY($row['fecha_vencimiento']),
					'estado_vencer' => $row['estado_vencer'],
					'estadoMed' => array(
						'string' => $string,
						'clase' => $claseMed,
						'bool' => $row['estado_vencer']
					)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = 'Producto encontrado';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró el producto.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function lista_medicamento_por_agotarse(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_agotarse($paramPaginate,$paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_medicamento_almacen_por_agotarse($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			switch ($row['estado']) {
				case 1:
					$string = 'CRITICO';
					$claseMed = 'label-info';
					break;
				case 2:
					$string = 'MINIMO';
					$claseMed = 'label-warning';
					break;
				case 3:
					$string = 'AGOTADO';
					$claseMed = 'label-danger';
					break;
				default:
					break;
			}
			array_push($arrListado,
				array(

					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['denominacion'],
					'laboratorio' => $row['nombre_lab'],
					'almacen' => $row['almacen'],
					'stock_actual_malm' => $row['stock_actual_malm'],
					'stock_minimo' => $row['stock_minimo'],
					'stock_critico' => $row['stock_critico'],
					'stock_maximo' => $row['stock_maximo'],
					'estadoStock' => array(
						'string' => $string,
						'clase' => $claseMed
					)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = 'Producto encontrado';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontró el producto.';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function quitarAlerta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo quitar';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_medicamento_almacen->m_quitar_alerta($row['iddetallemovimiento']) ){
				$arrData['message'] = 'Se quitaron las alertas';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// PREPARADOS
	public function lista_preparados_almacen_busqueda_venta(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; 
		$lista = $this->model_medicamento_almacen->m_cargar_preparado_almacen_busqueda_venta($paramPaginate,$paramDatos);
		$totalRows = $this->model_medicamento_almacen->m_count_preparado_almacen_busqueda_venta($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					// 'stock' => $row['stock_actual_malm'],
					// 'stock_central' => $row['stock_central'],
					'precio' => $row['precio_venta'],
					'precio_venta_sf' => $row['precio_venta_sf'],
					// 'idlaboratorio' => $row['idlaboratorio'],
					// 'laboratorio' => $row['nombre_lab']
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
}