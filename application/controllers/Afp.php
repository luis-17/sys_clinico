<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Afp extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_afp'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_afp_cbo()
	{
		$lista = $this->model_afp->m_cargar_afp_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idafp'],
					'descripcion' => $row['descripcion_afp']
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

	public function lista_afp(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_afp->m_cargar_afp($paramPaginate);
		$totalRows = $this->model_afp->m_count_afp($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idafp'],
					'descripcion' => $row['descripcion_afp'],
					'estado_afp' => $row['estado_afp'],
					'a_oblig' => $row['a_oblig'],
					'comision' => $row['comision'],
					'p_seguro' => $row['p_seguro'],
					'comision_m' => $row['comision_m'],
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

	public function ver_popup_formulario(){
		$this->load->view('Afp/afp_formView');
	}

	public function registrar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs); exit();    	
		
		if(!empty($allInputs['a_oblig']) && !is_numeric($allInputs['a_oblig'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['comision']) && !is_numeric($allInputs['comision'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['p_seguro']) && !is_numeric($allInputs['p_seguro'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['comision_m']) && !is_numeric($allInputs['comision_m'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	$this->db->trans_start();
		if($this->model_afp->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
		    $arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	if(!empty($allInputs['a_oblig']) && !is_numeric($allInputs['a_oblig'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['comision']) && !is_numeric($allInputs['comision'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['p_seguro']) && !is_numeric($allInputs['p_seguro'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(!empty($allInputs['comision_m']) && !is_numeric($allInputs['comision_m'])){
    		$arrData['message'] = 'Debe ingresar porcentajes válidos';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

		if($this->model_afp->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_afp->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>