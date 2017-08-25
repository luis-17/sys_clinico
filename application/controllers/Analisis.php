<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analisis extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_analisis'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_analisis_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_analisis_cbo($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_analisis_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idanalisis'],
					'descripcion' => $row['descripcion_anal'],
					'abreviatura' => $row['abreviatura'],
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
	public function lista_analisis()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_analisis->m_cargar_analisis($paramPaginate);
		$totalRows = $this->model_analisis->m_count_analisis($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_anal'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado_anal'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado,
				array(
					'id' => $row['idanalisis'],
					'idseccion' => $row['idseccion'],
					'seccion' => $row['descripcion_sec'],
					'descripcion' => $row['descripcion_anal'],
					'abreviatura' => $row['abreviatura'],
					'idmetodo' => $row['idmetodo'],
					'metodo' => $row['descripcion'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'subanalisis' => $row['tiene_sub'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_anal']
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
	public function lista_pdtos_laboratorio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_pdtos_lab($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_pdtos_lab();
		}
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
	public function lista_pdtos_para_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_pdtos_lab_auto($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_pdtos_lab_auto();
		}
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
	public function lista_parametros_laboratorio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_parametros_lab($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_parametros_lab();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			if(empty($row['descripcion_adicional'])){
				$descripcion = $row['descripcion_par'];
			}else{
				$descripcion = $row['descripcion_par']. ' - ' . $row['descripcion_adicional'];
			}
			array_push($arrListado, 
				array( 
					'id' => $row['idparametro'], 
					'descripcion_par' => $descripcion,
					'descripcion' => $descripcion,
					'valor_normal_h' => $row['valor_normal_h'],
					'valor_normal_m' => $row['valor_normal_m'],
					'valor_ambos' => $row['valor_ambos'],
					'separador' => $row['separador']
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
	public function lista_parametros_para_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_parametros_lab_auto($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_parametros_lab_auto();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			if(empty($row['descripcion_adicional'])){
				$descripcion = $row['descripcion_par'];
			}else{
				$descripcion = $row['descripcion_par']. ' - <span class="text-info">' . $row['descripcion_adicional'] . '</span>';
			}
			array_push($arrListado, 
				array( 
					'id' => $row['idparametro'], 
					'descripcion' => $descripcion,
					'descripcion_par' => $row['descripcion_par'],
					'valor_normal_h' => $row['valor_normal_h'],
					'valor_normal_m' => $row['valor_normal_m'],
					'valor_ambos' => $row['valor_ambos'],
					'separador' => $row['separador']
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
	public function lista_analisis_para_autocompletado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_analisis->m_cargar_analisis_lab_auto($allInputs);
		}else{
			$lista = $this->model_analisis->m_cargar_analisis_lab_auto();
		}
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idanalisis'], 
					'descripcion' => $row['descripcion_anal'],
					'seccion' => $row['seccion']
					
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
	public function lista_parametros_analisis_by_id()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$lista = $this->model_analisis->m_cargar_parametros_analisis_id($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) {
			// if( $row['separador'] == 1 ){
			// 	$label = 'separador';
			// }
			// if( $row['separador'] == 0 ){
			// 	$label = 'no tiene';
			// }
			array_push($arrListado, 
				array(
					'idanalisisparametro' => $row['idanalisisparametro'], 
					'id' => $row['idparametro'], 
					'descripcion' => $row['descripcion_par'],
					'valor_normal_h' => $row['valor_normal_h'],
					'valor_normal_m' => $row['valor_normal_m'],
					'valor_ambos' => $row['valor_ambos'],
					'orden_parametro' => $row['orden_parametro'],
					'separador' => array(
						'separador' => $row['separador']
						//'label' => $label
					)
					
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
	public function lista_analisis_perfil(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$lista = $this->model_analisis->m_cargar_analisis_perfil($allInputs);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idanalisis'], 
					'descripcion' => $row['descripcion_anal'],
					'seccion' => $row['seccion']
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
	public function lista_parametros_separador_by_id()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$lista = $this->model_analisis->m_cargar_parametros_separador_id($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) {
			// if( $row['separador'] == 1 ){
			// 	$label = 'separador';
			// }
			// if( $row['separador'] == 0 ){
			// 	$label = 'no tiene';
			// }
			array_push($arrListado, 
				array( 
					'id' => $row['idparametro'], 
					'descripcion' => $row['descripcion_par'],
					'valor_normal_h' => $row['valor_normal_h'],
					'valor_normal_m' => $row['valor_normal_m'],
					'valor_ambos' => $row['valor_ambos'],
					'separador' => array(
						'separador' => $row['separador']
						//'label' => $label
					)
					
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
	// ==================================>		POPUPS 		<==============================
	public function ver_popup_formulario()
	{
		$this->load->view('analisis/analisis_formView');
	}
	public function ver_popup_parametros()
	{
		$this->load->view('analisis/parametros_formView');
	}
	public function ver_popup_separador()
	{
		$this->load->view('analisis/separador_formView');
	}
	public function ver_popup_agregar_analisis()
	{
		$this->load->view('analisis/agregar_analisis_formView');
	}
	// ==================================> 	MANTENIMIENTOS 	<==============================
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_analisis->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function asignarParametro()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
    	//var_dump($allInputs); exit();
    	$this->db->trans_start();
    	if($this->model_analisis->m_asignar_parametro($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function asignarAnalisisPerfil()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
    	//var_dump($allInputs); exit();
    	$this->db->trans_start();
    	if($this->model_analisis->m_asignar_analisis_a_perfil($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// public function asignarParametroSeparador()
	// {
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
 //    	//var_dump($allInputs); exit();
 //    	$this->db->trans_start();
 //    	if($this->model_analisis->m_asignar_parametro_separador($allInputs)){
	// 		$arrData['message'] = 'Se registraron los datos correctamente';
 //    		$arrData['flag'] = 1;
	// 	}
		
	// 	$this->db->trans_complete();
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_analisis->m_editar($allInputs)){
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
			if( $this->model_analisis->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anularAnalisisParametro()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;

    	//var_dump($allInputs); exit();
		if( $this->model_analisis->m_anular_analisis_parametro($allInputs) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_analisis->m_habilitar($row['id']) ){
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

		$arrData['message'] = 'No se pudo deshabilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_analisis->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}


}