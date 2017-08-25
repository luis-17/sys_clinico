<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConsultaingresosAlmacen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_almacen'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
	}
	public function lista_ingresos_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_almacen->m_cargar_ingresoAlmacen($paramPaginate);
		$totalRows = $this->model_almacen->m_count_ingresoAlmacen($paramPaginate);
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
					'id' => $row['idkardex'],
					'fecha' => date('d-m-Y', strtotime($row['fecha'])),
					'descripcion' => $row['descripcion'],
					'proveedor' => $row['proveedor'],
					'descripcion_td' => $row['descripcion_td'],
					'doc_referencia' => $row['doc_referencia'],
					'descripcion_mm' => $row['descripcion_mm'],
					'costo_total' => $row['costo_total'],
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

	public function lista_detalle_ingresos_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_almacen->m_cargar_detalleingresoAlmacen($paramPaginate,$paramDatos);
		$totalRows = $this->model_almacen->m_count_detalleingresoAlmacen($paramDatos);
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
					'idkardex'=>$row['idkardex'],
					'idreactivoinsumo' => $row['idreactivoinsumo'],
					'descripcion' => $row['descripcion'],
					'precio' => $row['precio'],
					'cantidad' => $row['cantidad'],
					'importe' => $row['importe'],
					'costo_total' =>$row['costo_total'],
					'fechavencimiento' => date('d-m-Y', strtotime($row['fecha_vencimiento'])),
					'numerolote' => $row['numero_lote'],
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

	public function anular_ingresos_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = null ;	
		$paramDatos = $allInputs;
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		$lista = $this->model_almacen->m_cargar_detalleingresoAlmacen($paramPaginate,$paramDatos);

    	$this->db->trans_start();
		if($this->model_almacen->m_anular_movimientoAlmacen($allInputs)){ //  Anulamos el ingreso
			$arrData['message'] = 'Se Anularon los datos correctamente'; 
			$arrData['flag'] = 1;
		}
		$est = 2 ;
		foreach ($lista as $row) {
			$row['id'] = $row['idreactivoinsumo'];
			$row['precio'] = null ;
			$stoact = $this->model_almacen->m_stock_actual_reactivo_insumo($row['id']);
			if(($row['cantidad']) <= ($stoact)){
				if( $this->model_almacen->m_actualizar_stock_precio($row,$est) ){ //  Actualizamos el stock
					$arrData['message'] = 'Se actualizaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'No se pudieron actualizar los datos correctamente';
		    		$arrData['flag'] = 0;
				}

			}else{
				$arrData['message'] = 'El stock del Insumo '.$row['descripcion'].' no se puede actualizar';
		    	$arrData['flag'] = 0;
		    	break;
			}

		}
		if($arrData['flag'] === 1){
			if($this->model_almacen->m_anular_todo_detalle_movimientoAlmacen($allInputs)){ // Anulamos todo el detalle del ingreso
				$arrData['message'] = 'Se Anularon los datos correctamente'; 
			}
			$this->db->trans_complete();
		}else{
			$this->db->trans_rollback();
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));

	}

	public function anular_detalle_ingresos_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = null ;	
		$paramDatos = $allInputs;
		$paramDat = array();
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
		$est = 2 ;
		$paramDat['id'] = $paramDatos['idreactivoinsumo'];
		$paramDat['precio'] = null ;
		$paramDat['cantidad'] = $paramDatos['cantidad'];
		$stoact = $this->model_almacen->m_stock_actual_reactivo_insumo($paramDat['id']);

		if(($paramDatos['cantidad']) <= ($stoact)){
			if( $this->model_almacen->m_actualizar_stock_precio($paramDat,$est) ){ //  Actualizamos el stock
				$arrData['message'] = 'Se actualizaron los datos correctamente';
				$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'No se pudieron actualizar los datos correctamente';
				$arrData['flag'] = 0;
			}

		}else{
			$arrData['message'] = 'El stock del Insumo '.$paramDatos['descripcion'].' no se puede actualizar';
			$arrData['flag'] = 0;
		}

		if($arrData['flag'] === 1){
			if($this->model_almacen->m_anular_detalle_movimientoAlmacen($paramDatos['id'])){ // Anulamos todo el detalle del ingreso
				$arrData['message'] = 'Se Anularon los datos correctamente'; 
			}
			if($this->model_almacen->m_actualizar_costo_total($paramDatos,$est)){ // Anulamos todo el detalle del ingreso
				$arrData['message'] = 'Se Anularon los datos correctamente'; 
			}

			$this->db->trans_complete();
		}else{
			$this->db->trans_rollback();
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_detalle_ingreso()
	{
		$this->load->view('consulta-ingreso/consultaIngreso_formView');
	}

}