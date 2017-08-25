<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductosVendidos extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_productos_vendidos'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_productos_vendidos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_productos_vendidos->m_cargar_productos_venta($paramPaginate,$paramDatos); 
		$totalRows = $this->model_productos_vendidos->m_count_productos_venta($paramPaginate,$paramDatos); 
		$arrListado = array(); 
		foreach ($lista as $row) { 
			$strMedico = trim($row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno']);
			if(empty($strMedico)) { 
				$strMedico = '[SIN MÉDICO]'; 
			}
			$objEstado = array();
			$objEstado['claseIconAtendido'] = '';
			if( $row['estado'] == 1 ){ // HABILITADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'VENDIDO';
			}
			if( $row['estado'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['paciente_atendido_det'] == 1 ){ 
				$objEstado['claseIconAtendido'] = 'fa-thumbs-up'; 
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'producto' => $row['producto'],
					'especialidad' => $row['especialidad'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN  medico
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
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'total_detalle' => $row['total_detalle'],
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'],
					'estado' => $objEstado
				)
			);
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
}