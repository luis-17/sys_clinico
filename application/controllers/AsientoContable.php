<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AsientoContable extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper','fechas_helper','contable_helper'));
		$this->load->model(array('model_asiento_contable'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); 
	}

	public function listar_asiento_contable_egreso()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$lista = $this->model_asiento_contable->m_cargar_asiento_contable_egreso($datos);
		$fContador = $this->model_asiento_contable->m_count_asiento_contable_egreso($datos);
		$arrListado = array();
		foreach ($lista as $row) { 
			
			if($row['debe_haber']=="D"){
				$debe = $row['monto'];
				$haber = "";
			}else{
				$debe = "";
				$haber = $row['monto'];
			}

			$fecha_emision = explode(' ', $row['fecha_emision']);
			array_push($arrListado, 
				array( 
					'idasientocontable' => $row['idasientocontable'],
					'idmovimiento' => $row['idmovimiento'],
					'codigo_plan' => $row['codigo_plan'],
					'glosa' => strtoupper($row['glosa']),
					'monto' => $row['monto'],
					'fecha_emision' => $fecha_emision[0],
					'debe_haber' => $row['debe_haber'],
					'debe' => $debe,
					'haber' => $haber,	
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['sumTotal'] = empty($fContador['suma_total']) ? 0 : $fContador['suma_total'];
    	$arrData['paginate']['totalRows'] = $fContador['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_asientos_contables_planilla()
	{
		$this->load->view('planilla/popup_asientoContablePlanilla');
	}
	public function listar_asiento_contable_planilla()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$lista = $this->model_asiento_contable->m_cargar_asiento_contable_planilla($datos);
		$total_pagar = 0;
		$arrListado = array();
		foreach ($lista as $row) { 
			
			if($row['debe_haber']=="D"){
				$debe = $row['monto_formato'];
				$haber = "";
				$total_pagar = $total_pagar + floatval($row['monto_formato']);
			}else{
				$debe = "";
				$haber = $row['monto_formato'];
			}

			$fecha_emision = explode(' ', $row['fecha_emision']);
			array_push($arrListado, 
				array( 
					'idasientocontable' => $row['idasientocontable'],
					'idplanilla' => $row['idplanilla'],
					'codigo_plan' => $row['codigo_plan'],
					'glosa' => strtoupper($row['glosa']),   
					'monto' => $row['monto'],
					'fecha_emision' => $fecha_emision[0],
					'debe_haber' => $row['debe_haber'],
					'debe' => $debe,
					'haber' => $haber,	
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['total_pagar'] = 'S/. ' . number_format($total_pagar, 2, '.', ',');;
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
