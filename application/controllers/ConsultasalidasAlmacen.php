<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConsultasalidasAlmacen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_almacen'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_salidas_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];

		$lista = $this->model_almacen->m_cargar_salidaAlmacen($paramPaginate);
		$totalRows = $this->model_almacen->m_count_salidaAlmacen($paramPaginate);
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
					'fecha' => $row['fecha'],
					'empleado' => $row['empleado'],
					'doc_referencia' => $row['doc_referencia'],
					'descripcion_mm' => $row['descripcion_mm'],
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

	public function lista_detalle_salidas_almacen()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_almacen->m_cargar_detallesalidaAlmacen($paramPaginate,$paramDatos);
		$totalRows = $this->model_almacen->m_count_detallesalidaAlmacen($paramDatos);
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
					'cantidad' => $row['cantidad'],
					'unidad' => $row['unidad'],
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

	public function anular_salidas_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = null ;	
		$paramDatos = $allInputs;
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		$lista = $this->model_almacen->m_cargar_detallesalidaAlmacen($paramPaginate,$paramDatos);

    	$this->db->trans_start();
		if($this->model_almacen->m_anular_movimientoAlmacen($allInputs)){ //  Anulamos el ingreso
			$arrData['message'] = 'Se Anularon los datos correctamente'; 
			$arrData['flag'] = 1;
		}
		$est = 1 ;
		foreach ($lista as $row) {
			$row['id'] = $row['idreactivoinsumo'];
			$row['precio'] = null ;
				if( $this->model_almacen->m_actualizar_stock_precio($row,$est) ){ //  Actualizamos el stock
					$arrData['message'] = 'Se actualizaron los datos correctamente';
		    		$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'El stock del Insumo '.$row['descripcion'].' no se puede actualizar';
		    		$arrData['flag'] = 0;
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

	public function anular_detalle_salidas_almacen()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = null ;	
		$paramDatos = $allInputs;
		$paramDat = array();
		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
		$est = 1 ;
		$paramDat['id'] = $paramDatos['idreactivoinsumo'];
		$paramDat['precio'] = null ;
		$paramDat['cantidad'] = $paramDatos['cantidad'];

		if( $this->model_almacen->m_actualizar_stock_precio($paramDat,$est) ){ //  Actualizamos el stock
			$arrData['message'] = 'Se actualizaron los datos correctamente';
			$arrData['flag'] = 1;
		}else{
			$arrData['message'] = 'No se pudieron actualizar los datos correctamente';
			$arrData['flag'] = 0;
		}

		if($arrData['flag'] === 1){
			if($this->model_almacen->m_anular_detalle_movimientoAlmacen($paramDatos['id'])){ // Anulamos todo el detalle del ingreso
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

	public function ver_popup_detalle_salida()
	{
		$this->load->view('consulta-salida/consultaSalida_formView');
	}

}