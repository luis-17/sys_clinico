<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class ResumenSolicitudFormula extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('fechas_helper'));
		$this->load->model(array('model_resumen_solicitud_formula'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function ver_popup_detalle_solicitud()
	{
		$this->load->view('resumenSolicitudFormula/popupVerDetalleSolicitud');
	}
	public function lista_resumen_solicitud()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_resumen_solicitud_formula->m_cargar_resumen_solicitud($paramPaginate,$paramDatos); 
		$totalRows = $this->model_resumen_solicitud_formula->m_count_resumen_solicitud($paramPaginate,$paramDatos); 

		$arrListado = array(); 
		foreach ($lista as $row) {
			$objEstado = array();
			$objEstado['claseIconEstado'] = '';
			if($row['estado_preparado'] == 1 || $row['estado_preparado'] == 4 || $row['estado_preparado'] == 3 || empty($row['estado_preparado'])){// PEDIDO
				if(empty($row['estado_acuenta'])){ //CUANDO AUN NO EXISTE MOVIMIENTOS
					$objEstado['claseIcon'] = 'fa-clock-o';
					$objEstado['claseLabel'] = 'label-default';
					$objEstado['labelText'] = 'PENDIENTE';
				}
				if( $row['estado_acuenta'] == 1 ){ // A CUENTA 
					$objEstado['claseIcon'] = 'fa-ban';
					$objEstado['claseLabel'] = 'label-warning';
					$objEstado['labelText'] = 'A CUENTA';
				}
				if( $row['estado_acuenta'] == 2 ){ // CANCELADO 
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-success';
					$objEstado['labelText'] = 'CANCELADO';
				}

			} elseif($row['estado_preparado'] == 2) { //ENTREGADO
				$objEstado['claseIcon'] = 'fa-thumbs-o-up';
				$objEstado['claseLabel'] = 'label-primary';
				$objEstado['labelText'] = 'ENTREGADO';
			}
			array_push($arrListado, 
				array(
					'idsolicitudformula' => $row['idsolicitudformula'],
					'iduser_creacion' => $row['iduser_creacion'],
					'encargado' => $row['encargado'],
					'idcliente' => $row['idcliente'],
					'paciente' => $row['paciente'],
					'num_documento' => $row['num_documento'],
					'total_solicitud' => $row['total_solicitud'],
					'fecha_solicitud' => formatoFechaReporte($row['fecha_solicitud']),
					'estado_preparado' => $row['estado_preparado'],
					'estado_acuenta' => $row['estado_acuenta'],
					'estado' => $objEstado,
					'es_anulable' => empty($row['estado_acuenta']) ? 1 : 2, // 1 = SI, 2 = NO => PARA VALIDAR QUE SOLICITUDES PUEDEN SER ANULADOS		
					'idmovimiento' => $row['idmovimiento']
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

	public function entregar_preparado()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo realizar la operación';
    	$arrData['flag'] = 0;
    	
    	$this->db->trans_start();
    	if($this->model_resumen_solicitud_formula->m_entregar_preparado( $allInputs[0]['idmovimiento'] ) ){
    		$arrData['message'] = 'La operación se realizó correctamente!';
    		$arrData['flag'] = 1;
    	} else {
    		$arrData['message'] = 'No se pudo realizar la operación';
    		$arrData['flag'] = 0;
    	}
		
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_solicitud()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular la solicitud';
    	$arrData['flag'] = 0;
    	
    	$this->db->trans_start();
    	$esAnulable = $this->model_resumen_solicitud_formula->m_estado_solicitud( $allInputs[0]['idsolicitudformula'] );
    	if(empty($esAnulable['estado_acuenta'])){//SIGNIFICA QUE NO EXISTE MOVIMIENTOS POR LO QUE ES ANULABLE
    		if( $this->model_resumen_solicitud_formula->m_anular_solicitud( $allInputs[0]['idsolicitudformula'] ) ){
    			$arrData['message'] = 'La solicitud se anuló correctamente';
    			$arrData['flag'] = 1;
			}
    	} else {
    		$arrData['message'] = 'No se pudo anular la solicitud';
    		$arrData['flag'] = 0;
    	}
		
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function lista_detalle_solicitud()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_resumen_solicitud_formula->m_cargar_detalle_solicitud($paramPaginate,$paramDatos);
		$totalRows = $this->model_resumen_solicitud_formula->m_count_detalle_solicitud($paramPaginate,$paramDatos);

		$arrListado = array();

		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'idsolicitudformula' => $row['idsolicitudformula'],
					'idmedicamento' => $row['idmedicamento'],
					'denominacion' => $row['denominacion'],
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'total_detalle' => $row['total_detalle_solicitud'],
					'estado' => $row['estado_detalle_sol']
				)
			);
		}
		$arrData['datos'] = $arrListado;    	
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