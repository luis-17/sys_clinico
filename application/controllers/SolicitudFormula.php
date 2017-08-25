<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SolicitudFormula  extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','fechas_helper','contable'));
		$this->load->model(array('model_solicitud_formula','model_medicamento'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
	}
	public function lista_solicitud_formula_por_id()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$arrData['message'] = '';
    	$arrData['flag'] = 1;

		$lista = $this->model_solicitud_formula->m_cargar_solicitud_formula_por_id($paramPaginate,$paramDatos);
		$totalRows = $this->model_solicitud_formula->m_count_sum_detalle_solicitud_formula_por_id($paramPaginate,$paramDatos); 
		$arrListado = array();
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'La solicitud no existe o ya no está disponible';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		
		$fecha_solicitud = date_create(date('Y-m-d',strtotime($lista[0]['fecha_solicitud'])));
		$hoy = date_create(date('Y-m-d'));
		$intervalo = date_diff($fecha_solicitud, $hoy);
		if( $intervalo->days > 0 && ($intervalo->invert == 0) ){
			$arrData['flag'] = 0;
			$arrData['message'] = 'La solicitud ya no está disponible. Se debe generar nuevamente la solicitud.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;

		}
		foreach ($lista as $row) {
			if( $row['estado_detalle_sol'] == 1 ){ // PEDIDO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = '	DISPONIBLE';
				$estado = 1;
			}
			elseif( $row['estado_detalle_sol'] == 2 ){ // ENTREGADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'NO DISPONIBLE';
				$estado = 2;
			}
			$fecha_solicitud = date_create(date('Y-m-d',strtotime($row['fecha_solicitud'])));
			// $fecha_solicitud = date_create('2017-08-24');
			$hoy = date_create(date('Y-m-d'));
			$intervalo = date_diff($fecha_solicitud, $hoy);
			if( $intervalo->days > 0 && ($intervalo->invert == 0) ){
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'NO DISPONIBLE';
				$estado = 2;
			}
			array_push($arrListado,
				array(
					'idsolicitudformula' => $row['idsolicitudformula'],
					'iddetallesolicitud' => $row['iddetallesolicitud'],
					'total_solicitud' => $row['total_solicitud'],
					'fecha_solicitud' => $row['fecha_solicitud'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['medicamento'],
					'precio_unitario' => $row['precio_unitario'],
					'precio_unitario_sf' => $row['precio_unitario_sf'],
					'cantidad' => $row['cantidad'],
					'total_detalle' => $row['total_detalle_solicitud'],
					'idcliente' => $row['idcliente'],
					'paciente' => $row['paciente'],
					'num_documento' => $row['num_documento'],
					'estado_detalle_sol' => $estado,
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'codigo_jj' => $row['codigo_jj'],
					'fecha_asigna_codigo_jj' => $row['fecha_asigna_codigo_jj'],
					'idformula_jj' => $row['idformula_jj'],
					'fecha_asigna_idformula_jj' => $row['fecha_asigna_idformula_jj'],
					'categoria' => $row['categoria_jj'],
					'estado' => $objEstado,
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	// var_dump($arrListado); exit();
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontraron los datos de la solicitud';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_solicitud()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar la solicitud, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idventaregister'] = NULL;
    	if( empty($allInputs['cliente']) ){
	    	$arrData['message'] = 'Seleccione un DNI válido de cliente.';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return;
		}
		if( empty($allInputs['idmedico']) && !$allInputs['esMedLibre']){
	    	$arrData['message'] = 'Seleccione un médico.';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return;
		}
		if( empty($allInputs['detalle'][0]['precio_costo'])){
			$arrData['message'] = 'Actualice la página con CTRL + F5.';
	    	$arrData['flag'] = 0;
	    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return;
		}
		foreach ($allInputs['detalle'] as $key => $row) {
			if( empty($row['categoria']) || @$row['categoria'] == '' ){
				$arrData['message'] = 'No ha seleccionado una categoria en la formula: ' . $row['descripcion'];
		    	$arrData['flag'] = 0;
		    	$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;
			}
			if( empty($row['uso']) || @$row['uso'] == '' ){
				$arrData['message'] = 'No ha seleccionado un uso en la formula: ' . $row['descripcion'];
		    	$arrData['flag'] = 0;
		    	$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;
			}
			// verifica si la formula ya cuenta con categoria, si no tiene se almacena en un array para luego registrarlos
			if( $this->model_medicamento->m_verificar_medicamento_con_categoria_jj($row) ){
				$allInputs['detalle'][$key]['categoria_nueva'] = TRUE;
			}else{
				$allInputs['detalle'][$key]['categoria_nueva'] = FALSE;
			}
			// verifica si la formula ya cuenta con categoria, si no tiene se almacena en un array para luego registrarlos
			if( $this->model_medicamento->m_verificar_medicamento_con_uso_jj($row) ){
				$allInputs['detalle'][$key]['uso_nuevo'] = TRUE;
			}else{
				$allInputs['detalle'][$key]['uso_nuevo'] = FALSE;
			}
		}	
    	$fProducto = array();    	   	
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if( $this->model_solicitud_formula->m_registrar_solicitud($allInputs)){ // REGISTRAR CABECERA
			$allInputs['idsolicitudformula'] = GetLastId('idsolicitudformula','far_solicitud_formula');			
			$rowAux = array();
			foreach ($allInputs['detalle'] as $key => $row) {
				$row['idsolicitudformula'] = $allInputs['idsolicitudformula'];
				
				if($row['categoria_nueva']){ // si la formula no tiene categoria le agregamos
					$this->model_medicamento->m_asignar_categoria_jj($row);
				}
				if($row['uso_nuevo']){ // si la formula no tiene categoria le agregamos
					$this->model_medicamento->m_asignar_uso_jj($row);
				}
				if( $this->model_solicitud_formula->m_registrar_detalle($row) ) {				
					$arrData['idsolicitudformula'] = str_pad($allInputs['idsolicitudformula'], 6, '0', STR_PAD_LEFT);
					$arrData['message'] = 'Se registró la solicitud correctamente!<br>El <b>N° de Solicitud es:<span class="f-18"> ' . $arrData['idsolicitudformula'] . '</span></b>'; 
	    			$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'Error al registrar la solicitud, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}//FIN FOREACH
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}