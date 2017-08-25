<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotaCreditoFarm extends CI_Controller { 

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros'));
		$this->load->model(array('model_nota_credito_farmacia','model_venta_farmacia','model_caja_farmacia'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
	}
	public function lista_nota_credito()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; // var_dump("<pre>",$allInputs); exit(); 
		$lista = $this->model_nota_credito_farmacia->m_cargar_notas_credito($paramPaginate,$paramDatos);
		$totalRows = $this->model_nota_credito_farmacia->m_count_notas_credito($paramPaginate,$paramDatos);
		$arrListado = array(); 
		$sumTotalNC = 0;
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'], 
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'orden_venta' => $row['ordenventa'],
					'ticket_venta' => $row['ticketventa'],
					'fecha_venta' => date('d-m-Y H:i:s', strtotime($row['fechaventa'])),
					'tipo_documento' => $row['descripcion_td'],
					'monto' => $row['total_a_pagar'],
					'saldo' => $row['total_a_pagar'],
					'monto_format' => $row['monto_format'],
					'fecha_movimiento' =>  date('d-m-Y H:i:s', strtotime($row['fecha_movimiento'])),
					'numero_caja' => $row['numero_caja'],
					'usuario' => $row['username'],
					'tipo_nota_credito' => ($row['tipo_nota_credito_nc'] == 1 ? 'NOTA CREDITO':'EXTORNO')
				)
			);
			$sumTotalNC += $row['monto_format'];
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = number_format(empty($sumTotalNC) ? 0 : $sumTotalNC ,2);
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

	public function ver_popup_formulario()
	{
		$this->load->view('nota-credito/notaCreditoFarm_formView');
	}	
	public function registrar()
	{
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = array(
			'searchColumn' => 'orden_venta',
			'searchText' => $allInputs['orden']['orden']
		);
		// var_dump($allInputs); exit();
		/*AQUI VAN LAS VALIDACIONES*/
		$esExtorno = FALSE; // para OPERACIONES
		$listaVentaDetalle = $this->model_venta_farmacia->m_cargar_esta_venta_con_detalle_por_columna($datos);
		if( @$listaVentaDetalle[0]['idtipodocumento'] == 3 || @$listaVentaDetalle[0]['idtipodocumento'] == 12 ){ // OP (CAJA CHICA) Y COMPROBANTE DE CAJA
			$esExtorno = TRUE;
			$allInputs['ticket'] = NULL;
		}
		// VALIDAR CANTIDADES
		foreach ($allInputs['detalle'] as $row) {
			if( $row['cantidad'] > $row['cantidad_original'] ){
				$arrData['message'] = 'La cantidad no debe ser mayor que la original';
	    		$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
			}
		}
		// OBTENER CAJA ACTUAL DEL USUARIO 
		$fCaja = $this->model_caja_farmacia->m_cargar_caja_actual_de_usuario(3);
		if( empty($fCaja) ){ 
			$arrData['message'] = 'No se encontró el usuario de venta en la Base de Datos';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		} // var_dump($allInputs['orden']['saldo_format'], $allInputs['monto']); exit();
		if( $allInputs['orden']['saldo_format'] < $allInputs['monto'] ){ 
			$arrData['message'] = 'El monto no debe ser mayor al saldo de la venta.';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$allInputs['idcaja'] = $fCaja['idcaja'];
		$allInputs['idventaorigen'] = $listaVentaDetalle[0]['idmovimiento'];
		//$allInputs['idempresacliente'] = $listaVentaDetalle[0]['idempresacliente'];
		$allInputs['tipo_nota_credito'] = ($esExtorno ? 2 : 1);

		
		$this->db->trans_start();
		if($this->model_nota_credito_farmacia->m_registrar_nc($allInputs)){ 
			$idmovimiento = GetLastId('idmovimiento','far_movimiento');
			foreach ($allInputs['detalle'] as $row) {
				$row['idmovimiento'] = $idmovimiento;
				if( $this->model_nota_credito_farmacia->m_registrar_detalle_nc($row) ) {
					$arrData['message'] = 'Se registraron los datos correctamente';
		 			$arrData['flag'] = 1;
				}
			}

		}
		if( $arrData['flag'] === 1 && !($esExtorno) ){ 
			//Actualizamos el número de serie 
			$params = array(
				'idcajamaster' => $fCaja['idcajamaster'],
				'idtipodocumento' => 7, // NOTA DE CREDITO 
				'numeroserie' => ($allInputs['numero_serie'] + 1)
			);
			$this->model_caja_farmacia->m_editar_numero_serie($params);
		}
		$this->db->trans_complete();
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
			if( $this->model_nota_credito_farmacia->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}