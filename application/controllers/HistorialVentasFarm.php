<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialVentasFarm extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_historial_venta_farm'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_historial_ventas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = @$allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_historial_venta_farm->m_cargar_ventas_historial($paramPaginate,$paramDatos);
		$totalRows = $this->model_historial_venta_farm->m_count_sum_ventas_historial($paramPaginate,$paramDatos);
		//var_dump($totalRows); exit(); 
		$arrListado = array();
		// var_dump($lista); exit(); 
		foreach ($lista as $row) { 
			$objEstado = array();
			$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}

			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					// 'idmedico' => $row['idmedico'],
					// 'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado,
					'es_preparado' => $row['es_preparado'] == 1? TRUE : FALSE,
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
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
	public function lista_historial_ventas_por_medicamento(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = @$allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_historial_venta_farm->m_cargar_ventas_historial_medicamento($paramPaginate,$paramDatos);
		$totalRows = $this->model_historial_venta_farm->m_count_sum_ventas_historial_medicamento($paramPaginate,$paramDatos);
		//var_dump($totalRows); exit(); 
		$arrListado = array();
		// var_dump($lista); exit(); 
		foreach ($lista as $row) { 
			$objEstado = array();
			$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}

			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['denominacion'],
					'laboratorio' => $row['laboratorio'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					// 'idmedico' => $row['idmedico'],
					// 'medico' => $strMedico,
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'total_detalle' => $row['total_detalle'],
					'estado' => $objEstado
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
    	$arrData['paginate']['sumCantidad'] = empty($totalRows['sumacantidad']) ? 0 : $totalRows['sumacantidad'];
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

	public function lista_historial_ventas_por_preparado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$paramPaginate = @$allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_historial_venta_farm->m_cargar_ventas_historial_preparado($paramPaginate,$paramDatos);
		$totalRows = $this->model_historial_venta_farm->m_count_sum_ventas_historial_preparado($paramPaginate,$paramDatos);
		//var_dump($totalRows); exit(); 
		$arrListado = array();
		// var_dump($lista); exit(); 
		foreach ($lista as $row) { 
			/*$objEstado = array();
			$objEstado['claseIconAtendido'] = '';
			// $htmlAtendido = '';
			if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['idtipodocumento'] == 7 ){ // NOTA DE CRÉDITO  
				$objEstado['claseIcon'] = ' fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'OK';
			}*/

			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'receta_referencia' => $row['idsolicitudformula'],
					'cliente' => $row['cliente'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idmedicamento' => $row['idmedicamento'],
					'medicamento' => $row['denominacion'],
					'laboratorio' => $row['laboratorio'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					// 'idmedico' => $row['idmedico'],
					// 'medico' => $strMedico,
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'total_detalle' => $row['total_detalle'],
					'saldo' =>  $row['saldo']
				)
			);
			// $sumTotal += $row['total_a_pagar_format'];
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
    	$arrData['paginate']['sumCantidad'] = empty($totalRows['sumacantidad']) ? 0 : $totalRows['sumacantidad'];
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
}