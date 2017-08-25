<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReactivoInsumo extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_reactivo_insumo','model_almacen'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
	}
	public function lista_reactivo_insumo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_reactivo_insumo->m_cargar_reactivo_insumo_cbo($allInputs);
		}else{
			$lista = $this->model_reactivo_insumo->m_cargar_reactivo_insumo_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idreactivoinsumo'],
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
	public function lista_reactivo_insumo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_reactivo_insumo->m_cargar_reactivoInsumo($paramPaginate);
		$totalRows = $this->model_reactivo_insumo->m_count_reactivoInsumo($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_ri'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_ri'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idreactivoinsumo'],
					'descripcion' => $row['descripcion'],
					'idpresentacion'=>$row['idpresentacion'],
					'presentacion' => $row['presentacion'],
					'idtipo'=>$row['tipo'],
					'tipo'=>($row['tipo']=1 ? 'REACTIVO' : 'INSUMO'),
					'stock' => $row['stock'],
					'stockminimo'=> $row['stock_minimo'],
					'stockmaximo'=> $row['stock_maximo'],
					'precio'=> $row['precio'],
					'idunidadlaboratorio' => $row['idunidadlaboratorio'],
					'unidad' => $row['unidad'],
					'marca' => $row['marca'],
					'idmarca' => $row['idmarca'],
					'pruebas'=> $row['pruebas_presentacion'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_ri']
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

	public function lista_reactivoInsumo_por_codigo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fArray = $this->model_reactivo_insumo->m_cargar_este_reactivoInsumo_por_codigo($allInputs);
		
		if(empty($fArray)){
			$arrData['flag'] = 0;
		}else{
			$fArray['id'] = trim($fArray['idreactivoinsumo']);
			$fArray['descripcion'] = strtoupper($fArray['descripcion']);
			$fArray['nombre_unidad'] = strtoupper($fArray['nombreunidad']);
			$fArray['stock'] = $fArray['stock'];
	    	$arrData['datos'] = $fArray;
	    	$arrData['message'] = '';
	    	$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_formulario()
	{
		$this->load->view('reactivo-Insumo/reactivoInsumo_formView');
	}

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_reactivo_insumo->m_registrar($allInputs)){
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
		if($this->model_reactivo_insumo->m_editar($allInputs)){
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
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_reactivo_insumo->m_busca_registro_detkardex($row['id']) ){
				$arrData['message'] = 'El Reactivo Insumo no se puede anular por contener datos en la tabla kardex';
	    		$arrData['flag'] = 2;
	    		//return false ;
			}else{
				if( $this->model_reactivo_insumo->m_anular($row['id']) ){
					$arrData['message'] = 'Se anularon los datos correctamente';
		    		$arrData['flag'] = 1;
				}
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron habilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_reactivo_insumo->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudieron deshabilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_reactivo_insumo->m_busca_registro_detkardex($row['id']) ){
				$arrData['message'] = 'El Reactivo Insumo no se puede deshabilitar por contener datos en la tabla kardex';
	    		$arrData['flag'] = 2;
	    		//return false ;
			}else{
				if( $this->model_reactivo_insumo->m_deshabilitar($row['id']) ){
					$arrData['message'] = 'Se deshabilitaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_reactivo_insumos_vencidos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$totalRows = $this->model_almacen->m_count_reactivoInsumo_vencidos();

    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_reactivo_insumos_stock_minimo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$totalRows = $this->model_reactivo_insumo->m_count_reactivoInsumo_stock_minimo();

    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_ri_vencidos()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_almacen->m_cargar_rivencidosAlmacen($paramPaginate);
		$totalRows = $this->model_almacen->m_count_reactivoInsumo_vencidos();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_k'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_k'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['iddetallekardex'],
					'fecha' => date('d-m-Y', strtotime($row['fecha'])),
					'idreactivoinsumo' => $row['idreactivoinsumo'],
					'descripcion' => $row['descripcion'],
					'fechavencimiento' => $row['fecha_vencimiento'],
					'cantidad' => $row['cantidad'],
					'estado_k' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_k']
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
	public function lista_ri_stock_minimo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		//$paramDatos = $allInputs['datos'];

		$lista = $this->model_reactivo_insumo->m_cargar_reactivoInsumo_stock_minimo($paramPaginate);
		$totalRows = $this->model_reactivo_insumo->m_count_reactivoInsumo_stock_minimo();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_ri'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_ri'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idreactivoinsumo'],
					'descripcion' => $row['descripcion'],
					'stock' => $row['stock'],
					'stock_minimo' => $row['stock_minimo'],
					'estado_ri' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_ri']
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

	public function ver_popup_ri_vencidos()
	{
		$this->load->view('reactivo-insumo/consultaRiVencidos_formView');
	}
	public function ver_popup_ri_stock_minimo()
	{
		$this->load->view('reactivo-insumo/consultaRiStockMinimo_formView');
	}
	public function tratamiento_reactivoinsumo_vencido()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	if( $this->model_almacen->m_tratamiento_reactivoinsumo_vencido($allInputs) ) { 
    		$arrData['message'] = 'Se registraron los datos correctamente'; 
    		$arrData['flag'] = 1;
    	}else{
    		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    		$arrData['flag'] = 0;
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}