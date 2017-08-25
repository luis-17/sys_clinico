<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TrabajadorPerfil extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_trabajador_perfil'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_perfiles_trabajadores()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_trabajador_perfil->m_cargar_perfiles_trabajadores($paramPaginate,$paramDatos);
		$totalRows = $this->model_trabajador_perfil->m_count_perfiles_trabajadores($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idproductocliente'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'precio' => $row['precio'],
					'idcliente' => $row['idcliente'],
					'cliente' => $row['cliente'],
					'num_documento' => $row['num_documento'],
					'ruc_empresa' => $row['ruc_empresa'],
					'empresa' => $row['empresa']
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
	// public function ver_popup_administracion_perfil()
	// {
	// 	$this->load->view('salud-ocupacional/administracionPerfilesSO_formView');
	// }
	public function agregar_cliente_a_perfil()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs,"<pre>"); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_trabajador_perfil->m_agregar_cliente_a_perfil($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// public function editar()
	// {
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
	// 	$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
 //    	$arrData['flag'] = 0;
	// 	if($this->model_aviso->m_editar($allInputs)){
	// 		$arrData['message'] = 'Se editaron los datos correctamente';
 //    		$arrData['flag'] = 1;
	// 	}
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos'; 
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_trabajador_perfil->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}