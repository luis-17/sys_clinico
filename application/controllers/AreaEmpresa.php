<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AreaEmpresa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_area_empresa'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_area_empresa_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_area_empresa->m_cargar_area_empresa_cbo($allInputs);
		$arrListado = array();
		if(!empty($lista)){
			foreach ($lista as $row) {
				array_push($arrListado, 
					array(
						'id' => $row['idareaempresa'],
						'descripcion' => $row['descripcion_ae']
					)
				);
			}
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