<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AreasOrdenCompra extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_areas_oc'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); estado 
	}
	public function lista_areas_oc()
	{
		$lista = $this->model_areas_oc->m_cargar_areas_oc();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idareaoc'],
					'descripcion' => $row['descripcion'],
					'field' => strtolower($row['descripcion']),
					'mail_1' => $row['mail_1'],
					'mail_2' => $row['mail_2']
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
	public function lista_areas_oc_cbo()
	{
		$lista = $this->model_areas_oc->m_cargar_areas_oc();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idareaoc'],
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
}
?>