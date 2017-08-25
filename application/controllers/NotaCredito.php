<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotaCredito extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_nota_credito','model_venta','model_caja'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_nota_credito()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos']; // var_dump("<pre>",$allInputs); exit(); 
		$lista = $this->model_nota_credito->m_cargar_notas_credito($paramPaginate,$paramDatos);
		$totalRows = $this->model_nota_credito->m_count_notas_credito($paramPaginate,$paramDatos);
		$arrListado = array(); 
		$sumTotalNC = 0;
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idnotacredito'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'], 
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'ticket_nc' => $row['ticket_nc'],
					'tipo_documento' => $row['descripcion_td'],
					'especialidad' => $row['especialidad'],
					'monto' => $row['monto'],
					'saldo' => $row['total_a_pagar'],
					'monto_format' => $row['monto_format'],
					'fecha_emision' => formatoFechaReporte($row['fecha_creacion_nc']),
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'numero_caja' => $row['numero_caja'],
					'serie_caja' => $row['serie_caja'],
					'sede' => $row['sede'],
					'empresa_admin' => $row['empresa_admin'],
					'usuario' => $row['username']
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
	public function lista_detalle_venta_nc()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = $this->model_nota_credito->m_cargar_detalle_venta_nc($allInputs);
		$arrListado = array(); 
		foreach ($lista as $row) {
			$objEstado = null;
			if( $row['paciente_atendido_det'] == 1 ){ // ATENDIDO 
				$objEstado['claseIcon'] = 'fa-thumbs-up';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'ATENDIDO';
				$objEstado['atendido'] = $row['paciente_atendido_det'];
				$objEstado['nota_credito'] = 2;
			}elseif( !empty($row['idnotacreditodetalle']) ){ // NO ATENDIDO 
				$objEstado['claseIcon'] = 'fa-exclamation';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'NOTA DE CREDITO';
				$objEstado['atendido'] = 2;
				$objEstado['nota_credito'] = 1; 
			}else{
				$objEstado['claseIcon'] = null;
				$objEstado['atendido'] = 2;
				$objEstado['nota_credito'] = 2;
			}

			array_push($arrListado, 
				array(
					'iddetalle' => $row['iddetalle'],
					'total_detalle' => $row['total_detalle'],
					'total' => $row['total'], 
					'descripcion' => $row['descripcion'],
					'estado' => $objEstado,
					'nota_credito' => !empty($row['idnotacreditodetalle']) ? TRUE:FALSE 
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
		$this->load->view('nota-credito/notaCredito_formView');
	}	
	public function registrar()
	{
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$datos = array(
			'searchColumn' => 'orden_venta',
			'searchText' => $allInputs['orden']['orden']
		);
		
		$esExtorno = FALSE;
		$listaVentaDetalle = $this->model_venta->m_cargar_esta_venta_con_detalle_por_columna($datos);
		if( @$listaVentaDetalle[0]['idtipodocumento'] == 3 ){ // OP 
			$esExtorno = TRUE;
			$allInputs['ticket'] = NULL;
		}

		$idventa = $listaVentaDetalle[0]['idventa'];
		$venta = $this->model_venta->m_cargar_esta_venta_por_id($idventa);
		if($venta['estado'] == 0){
			$arrData['message'] = 'No se puede crear nota de credito por que la orden de venta '. $allInputs['orden']['orden'] .' ha sido anulada';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}

		// OBTENER CAJA ACTUAL DEL USUARIO 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario();
		if( empty($fCaja) ){ 
			$arrData['message'] = 'No se encontró la orden de venta en la Base de Datos';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}  //var_dump($allInputs['orden']['saldo_format'], $allInputs['monto_format']); exit();
		if( $allInputs['orden']['saldo_format'] < $allInputs['monto_format'] ){ 
			$arrData['message'] = 'El monto no debe ser mayor al saldo de la venta.';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
		}
		$allInputs['idcaja'] = $fCaja['idcaja'];
		$allInputs['idventa'] = $listaVentaDetalle[0]['idventa'];
		$allInputs['idespecialidad'] = $listaVentaDetalle[0]['idespecialidad'];
		$allInputs['tipo_salida'] = ($esExtorno ? 2 : 1);
		 
		$this->db->trans_start();
		if($this->model_nota_credito->m_registrar($allInputs)){ 	

    		/* Agregar la insercion de nota credito detalle*/
    		$allInputs['idnotacredito'] = GetLastId('idnotacredito','nota_credito');
    		foreach ($allInputs['detalle'] as $key => $row) {
    			$data = array(
					'iddetalle' => $row['iddetalle'],
					'orden' => $allInputs['orden']['orden'],
				);

    			if($this->model_nota_credito->m_consultar_detalle_nc($data)){
    				$arrData['message'] = 'El item '. $row['iddetalle'] .' ya posee nota de credito';
		    		$arrData['flag'] = 0;
					$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
    			}

    			if( $this->model_nota_credito->m_consultar_detalle_nc_atendido($data)){
    				$arrData['message'] = 'El item '. $row['iddetalle'] .' ya fue atendido';
		    		$arrData['flag'] = 0;
					$this->output
					    ->set_content_type('application/json')
					    ->set_output(json_encode($arrData));
				    return;
    			}

    			$data = array(
					'idnotacredito' => $allInputs['idnotacredito'],
					'iddetalle' => $row['iddetalle'],
					'createdat' => date('Y-m-d H:i:s'),
					'updatedat' => date('Y-m-d H:i:s'),
					'monto_detalle_nc' => $row['total_detalle'],
				);

				if($this->model_nota_credito->m_registrar_detalle_nc($data)){ 
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
			$this->model_caja->m_editar_numero_serie($params);
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
			if( $this->model_nota_credito->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}