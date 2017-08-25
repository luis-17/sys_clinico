<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RecetaMedica extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_receta_medica'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_receta_por_id()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_receta_medica->m_cargar_receta_medica_por_id($paramPaginate,$paramDatos);
		$totalRows = $this->model_receta_medica->m_count_receta_medica_por_id($paramPaginate,$paramDatos);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['atendido'] == 1 ){ // VENDIDO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'ATENDIDO';
			}
			if( $row['atendido'] == 2 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'PENDIENTE';
			}
			$hayDescuento = 'no';
			$tieneConvenioDetalle = 2;
			$tieneConvenioDetalleEfectivo = 2;
			$precioSaliente = $row['precio_venta_sf'];
			/* LÓGICA DE DESCUENTO DE PRECIOS */ 
			if( !empty($row['idtipocliente']) ){ // SI HAY CONVENIO
				$tieneConvenioDetalle = 1;
				if( $row['idtipoproducto'] == 18 && $row['utilidad_porcentaje'] >= 20 ){ // medicamento 
					$decimalDcto = $row['porcentaje_farmacia'] / 100; 
					$precioSaliente = $row['precio_venta_sf'] - ($row['precio_venta_sf'] * $decimalDcto); 
					$hayDescuento = 'si';
					$tieneConvenioDetalleEfectivo = 1; 
				}
			} 
			array_push($arrListado, 
				array( 
					'id' => $row['idmedicamento'],
					'idrecetamedicamento' => $row['idrecetamedicamento'],
					'idatencionmedica' => $row['idatencionmedica'],
					'idreceta' => $row['idreceta'],
					'medicamento' => strtoupper($row['medicamento']),
					'cantidad' => (int)$row['cantidad'],
					'idmedicamentoalmacen' => $row['idmedicamentoalmacen'],
					'stockActual' => $row['stock_actual_malm'],
					'excluye_igv' => $row['excluye_igv'],
					'precio_venta_sf' => $precioSaliente, // 
					'precio_sin_convenio' => $row['precio_venta_sf'],
					'si_bonificacion' => $row['si_bonificacion'],
					'paciente' => $row['paciente'],
					'idcliente' => $row['idcliente'],
					'num_documento' => $row['num_documento'],
					'idhistoria' => $row['idhistoria'],
					'idtipoproducto' => $row['idtipoproducto'],
					'idempresaadmin' => $row['idempresaadmin'],
					'hay_descuento' => $hayDescuento,
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'atendido' => $row['atendido'],
					'idtipocliente' => $row['idtipocliente'],
					'descripcion_tc' => $row['descripcion_tc'],
					'porcentaje_farmacia' => $row['porcentaje_farmacia'],
					'utilidad_porcentaje' => $row['utilidad_porcentaje'],
					'estado' => $objEstado,
					'valor' => $row['stock_actual_malm'] > 0 ? (int)$row['cantidad'] * (float)$row['precio_venta_sf'] : '0.00',
					'tiene_convenio_detalle' => $tieneConvenioDetalle,
					'tiene_convenio_detalle_efectivo' => $tieneConvenioDetalleEfectivo, 

				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'La receta contiene medicamentos que no estan en farmacia';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_recetas_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_receta_medica->m_cargar_receta_medica($paramPaginate,$paramDatos);
		$totalRows = $this->model_receta_medica->m_count_receta_medica($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idrecetamedicamento'],
					'acto_medico' => $row['idatencionmedica'],
					'idreceta' => $row['idreceta'],
					'medicamento' => strtoupper($row['medicamento']),
					'cantidad' => (int)$row['cantidad'],
					'presentacion' => $row['presentacion'],
					'formafarmaceutica' => $row['descripcion_ff'],
					'fecha' => formatoFechaReporte($row['fecha_receta'])
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
	public function lista_ultimas_recetas_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_receta_medica->m_cargar_ultimas_recetas_medicas($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idrecetamedicamento'],
					'acto_medico' => $row['idatencionmedica'],
					'idreceta' => $row['idreceta'],
					'medicamento' => strtoupper($row['medicamento']),
					'cantidad' => (int)$row['cantidad'],
					'presentacion' => $row['presentacion'],
					'formafarmaceutica' => $row['descripcion_ff'],
					'fecha' => formatoFechaReporte3($row['fecha_receta'])
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
	public function lista_procedimiento_de_especialidad_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_receta_medica->m_cargar_procedimientos_de_especialidad_session_autocomplete($allInputs['searchColumn'],$allInputs['searchText']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'], 
					'descripcion' => strtoupper($row['descripcion']) 
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
	public function ver_popup_agregar_receta()
	{
		$this->load->view('recetaMedica/popup_agregar_receta');
	}
	public function registrar_receta_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$es_numerico = TRUE;
    	$cantidad_mala = NULL;
		if( empty($allInputs['detalle']) ){
    		$arrData['message'] = 'No se ha agregado ningún medicamento, a la receta.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	foreach ($allInputs['detalle'] as $row) {
    		if( !soloNumeros($row['cantidad']) ){
    			$es_numerico = FALSE;
    			$cantidad_mala = $row['cantidad'];
    			break;
    		}
    	}
    	if(!$es_numerico){
    		$arrData['message'] = 'Verifique que la cantidad "' . $cantidad_mala . '" sea un número entero.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;	
    	}
		

    	$this->db->trans_start();
		if( $this->model_receta_medica->m_registrar_receta_medica($allInputs) ) { 
			$allInputs['id'] = GetLastId('idreceta','receta');
			foreach ($allInputs['detalle'] as $row) { 
				$row['idreceta'] = $allInputs['id']; 
				if( $this->model_receta_medica->m_registrar_detalle_receta_medica($row) ){ 
					$arrData['message'] = 'Se registraron los datos correctamente'; 
					$arrData['flag'] = 1; 
				}
			}
		}else{ 
			$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
			$arrData['flag'] = 0; 
		}
		
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_receta_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); //var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'No se pudieron eliminar los datos'; 
    	$arrData['flag'] = 0;
		if( $this->model_receta_medica->m_anular_medicamento_receta($allInputs['id']) ){ 
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	// public function editar_cantidad_solicitud_procedimiento()
	// {
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
	// 	$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
	//	$arrData['flag'] = 0;
	//	$this->db->trans_start();
	// 	if( $this->model_receta_medica->m_editar_inline_solicitud_procedimiento($allInputs) ) { 
	// 		$arrData['message'] = 'Se registraron los datos correctamente'; 
	// 		$arrData['flag'] = 1; 
	// 	}else{ 
	// 		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
	// 		$arrData['flag'] = 0; 
	// 	} 
	// 	$this->db->trans_complete();
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
}