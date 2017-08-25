<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Producto extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security', 'otros_helper','fechas_helper'));
		$this->load->model(array('model_producto','model_convenio'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_productos()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_producto->m_cargar_productos($paramPaginate,$paramDatos);
		$totalRows = $this->model_producto->m_count_productos($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$estado = null;
			$clase = null;
			if( $row['estado_pps'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_pps'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idproductomaster'],
					'idproductopreciosede' => $row['idproductopreciosede'],
					'nombre' => strtoupper($row['producto']),
					'precio' => $row['precio'],
					'producto' => $row['producto'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tp' => $row['nombre_tp'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_pps']
					),
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
	public function lista_productos_indicadores()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// $allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		$lista = $this->model_producto->m_cargar_productos_indicadores($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id' => $row['key_indicador'],
					'name' => '<b>'.$row['str_indicador'].'</b> ',
					//'maker' => $row['str_indicador'],
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
	public function lista_productos_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_producto->m_cargar_productos_cbo($allInputs);
		}else{
			$lista = $this->model_producto->m_cargar_productos_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'nombre' => $row['descripcion']
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
	public function lista_productos_cbo_campania()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_producto->m_cargar_productos_cbo_campania($allInputs);
		}else{
			$lista = $this->model_producto->m_cargar_productos_cbo_campania();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'nombre' => $row['descripcion']
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
	public function lista_productos_de_session()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_producto->m_cargar_productos_de_session($allInputs);
		}else{
			$lista = $this->model_producto->m_cargar_productos_de_session();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'],
					'descripcion' => $row['descripcion'],
					'idespecialidad' => $row['idespecialidad'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'especialidad' => $row['nombre'],
					'precio' => $row['precio'],
					'precio_costo' => $row['costo_producto'],
					'precioSF' => $row['precio_sf'],
					'edicion_precio_en_venta' => $row['edicion_precio_en_venta'],
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
	public function lista_productos_de_sede_empresa_admin()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		$lista = $this->model_producto->m_cargar_productos_de_sede_empresa_admin($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'],
					'descripcion' => $row['descripcion'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['nombre'],
					'precio' => $row['precio'],
					'precioSF' => $row['precio_sf'],
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
	public function lista_productos_de_sede_empresa_admin_campania()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		$lista = $this->model_producto->m_cargar_productos_de_sede_empresa_admin_campania($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'],
					'descripcion' => $row['descripcion'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['nombre'],
					'precio' => $row['precio'],
					'precioSF' => $row['precio_sf'],
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
	public function lista_productos_de_sede_empresa_admin_campania_id()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		$lista = $this->model_producto->m_cargar_productos_de_sede_empresa_admin_campania_id($allInputs);
		$arrListado = array();
		
		array_push($arrListado,
			array(
				'id' => $lista['idproductomaster'],
				'descripcion' => $lista['descripcion'],
				'idespecialidad' => $lista['idespecialidad'],
				'especialidad' => $lista['nombre'],
				'precio' => $lista['precio'],
				'precioSF' => $lista['precio_sf'],
				'edicion_precio_en_venta' => $lista['edicion_precio_en_venta']
			)
		);
		
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
	public function lista_productos_convenio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_producto->m_cargar_productos_convenio($allInputs);
		}else{
			$lista = $this->model_producto->m_cargar_productos_convenio();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			$clase = '';
			if($row['estado_cps'] == 2){
				$clase = 'deshabilitado';
			}
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'],
					'descripcion' => $row['descripcion'],
					'idespecialidad' => $row['idespecialidad'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'especialidad' => $row['nombre'],
					'precio' => $row['precio'],
					'precioSF' => $row['precio_sf'],
					'precio_costo' => $row['costo_producto'],
					'edicion_precio_en_venta' => $row['edicion_precio_en_venta'],
					'estado_cps' => $row['estado_cps'],
					'clase' => $clase,
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

	/*S.O.*/
	public function lista_productos_salud_ocup()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_producto->m_cargar_productos_salud_ocup_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id' => $row['idproductomaster'],
					'descripcion' => $row['descripcion']
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
		$this->load->view('producto/producto_formView');
	}
	public function ver_popup_agregar_especialidad(){
		$this->load->view('producto/producto_formView');
	}
	public function ver_popup_historial_precios(){
		$this->load->view('producto/popupHistorialPrecios_view');
	}
	public function cargar_historial_precios(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);

		$paramDatos = $allInputs['datos'];
		$lista = $this->model_producto->m_cargar_historial_precios($paramDatos);

		$arrListado = array();
		foreach ($lista as $row) {
			if( end($lista) == $row ){
				$arrData['precio_inicial'] = !empty($row['precio_venta_anterior'])? $row['precio_venta_anterior'] : $row['precio_sede'];
			}
			array_push($arrListado, array(
				'precio_venta' => $row['precio_sede'],
				'precio_venta_anterior' => $row['precio_venta_anterior'],
				'precio_venta_actual' => $row['precio_venta_actual'],
				// 'motivo' => $row['motivo'],
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
			$arrData['datos'] = '';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();
    	$producto = strtoupper($allInputs['producto']);
    	$idespecialidad = $allInputs['especialidad']['id'];
    	if( $this->model_producto->m_verificar($producto,$idespecialidad) ){ 
			$arrData['message'] = 'El Producto: '.$producto.' ya existe en la Base de Datos.';
    		$arrData['flag'] = 2;
		}
    	// SI TODO ESTA BIEN PROCEDEMOS A REGISTRAR
    	if($arrData['flag'] === 0){
    		if($this->model_producto->m_registrar_master($allInputs)){
    			$arrData['idproductomaster'] = GetLastId('idproductomaster','producto_master');
    			$arrData['message'] = 'Se registraron los datos correctamente';
				$arrData['flag'] = 1;
	    		foreach ($allInputs['detalle'] as $row) {
	    			if( !empty($row['precio_sede']) || @$row['precio_sede'] === '0' ){
	    				$row['idproductomaster'] = $arrData['idproductomaster'];
	    				if($this->model_producto->m_registrar_producto_precio_sede($row)){
							$arrData['message'] = 'Se registraron los datos correctamente';
				    		$arrData['flag'] = 1;
				    		//$arrData['id'] = $row['idproductomaster'];
	    				}
	    			}
	    		}
			}
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
    	$this->db->trans_start();
		if($this->model_producto->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente pero no el precio';
    		$arrData['flag'] = 1;
    		foreach ($allInputs['detalle'] as $row) {
				if( empty($row['idproductopreciosede']) ){
					$row['idproductomaster'] = $allInputs['id'];
    				if($this->model_producto->m_registrar_producto_precio_sede($row)){
						$arrData['message'] = 'Se registraron los datos correctamente';
			    		$arrData['flag'] = 1;
    				}
				}else{
					$precio_sede_anterior = $this->model_producto->m_listar_precio_sede($row['idproductopreciosede']);
					if( $precio_sede_anterior['precio_sede'] != $row['precio_sede'] ){
						$row['precio_venta_anterior'] = $precio_sede_anterior['precio_sede'];
						$row['precio_venta'] = $row['precio_sede'];
						$this->model_producto->m_registrar_historial_precio($row);

						$listaProductoConvenios = $this->model_convenio->m_consulta_producto_convenios($row);
						foreach ($listaProductoConvenios as $key => $producto) {
							$producto['precio_convenio'] = $row['precio_venta'] - (($row['precio_venta'] * $producto['porcentaje'])/100);
							$this->model_convenio->m_actualizar_producto_convenio($producto);
						}
					}

					if( $this->model_producto->m_editar_producto_precio_sede($row) ){
						$arrData['message'] = 'Se editaron los datos correctamente';
			    		$arrData['flag'] = 1;
    				}
				}
    		}			
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs['seleccion'] as $row) {
    			$row['idsedeempresaadmin'] = $allInputs['sedeempresa'];	
			if( $this->model_producto->m_habilitar($row) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs['seleccion'] as $row) {
			$row['idsedeempresaadmin'] = $allInputs['sedeempresa']; 
			if( $this->model_producto->m_deshabilitar($row) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
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
    	foreach ($allInputs as $row) {
			if( $this->model_producto->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}