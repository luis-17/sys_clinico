<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CentroCosto extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas','contable'));
		$this->load->model(array('model_centro_costo','model_config'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function listar_centro_costo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_centro_costo->m_listar_centro_costo($paramPaginate);
		$totalRows = $this->model_centro_costo->m_count_centro_costo($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcentrocosto'],
					'nombre' => $row['nombre_cc'],
					'codigo' => $row['codigo_cc'],
					'descripcion' => $row['descripcion_cc'],
					'cat' => $row['descripcion_ccc'],
					'subcat' => $row['descripcion_scc'],
					'idsubcat' => $row['idsubcatcentrocosto'],
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
	public function lista_centro_costo_grilla()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		// var_dump($paramDatos['id']); exit();
		$lista = $this->model_centro_costo->m_cargar_centro_costo_grilla($paramPaginate, $paramDatos);
		$totalRows = $this->model_centro_costo->m_count_centro_costo($paramPaginate, $paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idcentrocosto' => $row['idcentrocosto'],
					'codigo_centro_costo' => $row['codigo_cc'],
					'centro_costo' => $row['nombre_cc'],
					'codigo' => $row['codigo_cc'],
					'descripcion' => $row['nombre_cc']
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	//$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function lista_centro_costo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_centro_costo->m_cargar_centro_costo_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcentrocosto'],
					'descripcion' => $row['nombre_cc'],
					'codigo' => $row['codigo_cc'],
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
	public function listar_categoria_centro_costo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_centro_costo->m_cargar_cat_centro_costo_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idcatcentrocosto'],
					'descripcion' => $row['descripcion_ccc'],
					'codigo_cat_centro_costo' => $row['codigo_ccc']
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
	public function listar_subcategoria_centro_costo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_centro_costo->m_cargar_subcat_centro_costo_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsubcatcentrocosto'],
					'descripcion' => $row['descripcion_scc'],
					'codigo_subcat_centro_costo' => $row['codigo_scc']
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
	public function listar_categoria_subcat_centro_costo_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_centro_costo->m_cargar_categoria_subcat_centro_costo_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsubcatcentrocosto'],
					'descripcion' => $row['descripcion_ccc'] . ' - ' . $row['descripcion_scc'] ,
					'codigo_subcat_centro_costo' => $row['codigo_scc']
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
	// TIPO DE CAMBIO
	public function ver_popup_tipo_cambio()
	{
		$this->load->view('centro-costo/popup_tipo_cambio');
	}
	public function listar_tipo_cambio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fila = ObtenerTipoCambio();
		//$fila = CalculoRentaQuinta('10000.00','5000.00','450.00','0');
		$arrListado = array(
			'id' => $fila['idtipocambio'],
			'fecha_cambio' => darFormatoDMY($fila['fecha_cambio']),
			'compra' => number_format($fila['compra'],2),
			'venta' => number_format($fila['venta'],2),
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
	public function registrar_tipo_cambio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if( $allInputs['fecha_cambio'] == $allInputs['oldFecha'] && $allInputs['compra'] == $allInputs['oldCompra'] && $allInputs['venta'] == $allInputs['oldVenta']){
			$arrData['message'] = '';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$this->db->trans_start();
			if($this->model_centro_costo->m_registrar_tipo_cambio($allInputs)){
				$arrData['message'] = 'Se registraron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
			$this->model_centro_costo->m_actualizar_tipo_cambio_vigente($allInputs);
			// TIPO DE CAMBIO
			$arrTipoCambio = ObtenerTipoCambio();
			$arrTipoCambio['compra'] = number_format($arrTipoCambio['compra'],2);
			$arrTipoCambio['venta'] = number_format($arrTipoCambio['venta'],2);
			if( !empty($arrTipoCambio) ){
				$_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['tc_compra'] =$arrTipoCambio['compra']; 
				$_SESSION['sess_vs_'.substr(base_url(),-8,7) ]['tc_venta'] = $arrTipoCambio['venta']; 
			}
		$arrData['datos'] = $arrTipoCambio;
		$this->db->trans_complete();
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	// Mantenimiento de Centro Costo
	public function ver_popup_formulario()
	{
		$this->load->view('centro-costo/centroCosto_formView');
	}

	public function registrar_centro_costo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_centro_costo->m_registrar_centro_costo($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_centro_costo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_centro_costo->m_editar_centro_costo($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_centro_costo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_centro_costo->m_anular_centro_costo($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>