<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class IngresoAlmacen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','fechas_helper'));
		$this->load->model(array('model_almacen','model_reactivo_insumo'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
	}
	public function registrar_ingreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idingresoregister'] = NULL;

    	$this->db->trans_start();
		if($this->model_almacen->m_registrar_ingreso($allInputs)){ // REGISTRAR CABECERA 
			$allInputs['idkardex'] = GetLastId('idkardex','kardex'); 
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idkardex'] = $allInputs['idkardex'];
				$est = 1 ;
				if( $this->model_almacen->m_registrar_detalle($row,$allInputs) ) { 
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
				if($this->model_almacen->m_actualizar_stock_precio($row,$est)){
					$arrData['message'] = 'Se actualizo los datos correctamente';
    				$arrData['flag'] = 1;
				}
			}
			$arrData['idingresoregister'] = $allInputs['idkardex'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function registrar_detalle_ingreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// $allInputs['tiene_descuento'] = 2;
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$row = null ;
    	$est = 1 ;

    	$this->db->trans_start();
    	if( $this->model_almacen->m_registrar_detalle($allInputs,$row) ) { 
    		$arrData['message'] = 'Se registraron los datos correctamente'; 
    		$arrData['flag'] = 1;
    	}else{
    		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    		$arrData['flag'] = 0;
    	}
    	if($this->model_almacen->m_actualizar_stock_precio($allInputs,$est)){
			$arrData['message'] = 'Se actualizo los datos correctamente';
    		$arrData['flag'] = 1;
		}
		if($this->model_almacen->m_actualizar_costo_total($allInputs,$est)){ // Anulamos todo el detalle del ingreso
			$arrData['message'] = 'Se Registraron los datos correctamente'; 
		}

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}