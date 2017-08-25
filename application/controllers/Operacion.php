<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Operacion extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security'));
		$this->load->model(array('model_operacion'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario); 
	}
	public function lista_operaciones()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$datos = $allInputs['datos'];
		$lista = $this->model_operacion->m_cargar_operaciones($paramPaginate,$datos);
		$totalRows = $this->model_operacion->m_count_operaciones($paramPaginate,$datos);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['tipo_operacion'] == 1){
				$rowobj = 'GASTO';
			}else {
				$rowobj = 'COMPRA';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idoperacion'],
					'descripcion' => strtoupper($row['descripcion_op']),
					'estado' => $row['estado_op'],
					'tipo' => $rowobj,
					'tipo_operacion' => $row['tipo_operacion']
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
	
	public function lista_operaciones_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// if( $allInputs['categoria'] == 'all' ){
		// 	$allInputs['categoria'] = NULL;
		// }
		$allInputs['tipo_operacion'] = NULL; 
		if( $allInputs['origen'] === 'gasto'){
			$allInputs['tipo_operacion'] = 1;
		}
		if( $allInputs['origen'] === 'compra'){
			$allInputs['tipo_operacion'] = 2;
		} 
		if( $allInputs['origen'] === 'cajaChica'){
			$allInputs['tipo_operacion'] = 3;
		} 
		$lista = $this->model_operacion->m_cargar_operacion_cbo($allInputs['tipo_operacion']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idoperacion'],
					'descripcion' => $row['descripcion_op'],
					'codigo_amarre'=> $row['codigo_amarre_cc']
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
		$this->load->view('operacion/operacion_formView');
	}
	public function ver_popup_subOperaciones()
	{
		$this->load->view('operacion/operacion_formView_subOperaciones');
	}			
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
    	if($this->model_operacion->m_buscar_operacion($allInputs['descripcion'])){
			$arrData['message'] = 'Ya existe otra operación con el mismo nombre';
    		$arrData['flag'] = 0; 
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;    
    	}
		if($this->model_operacion->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
    	if(!empty($allInputs['Change'])){
	    	if($this->model_operacion->m_buscar_operacion($allInputs['descripcion'])){
				$arrData['message'] = 'Ya existe otra operación con el mismo nombre';
	    		$arrData['flag'] = 0; 
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;    
	    	}    	    		
    	}
		if($this->model_operacion->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
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
			if( $this->model_operacion->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_operaciones_so()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$id = $allInputs['id'];
		$lista = $this->model_operacion->m_cargar_operaciones_so($paramPaginate,$id);
		$totalRows = $this->model_operacion->m_count_operaciones_so($paramPaginate,$id);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idsuboperacion'],
					'idoperacion' => $row['idoperacion'],
					'descripcion' => strtoupper($row['descripcion_sop']),
					'codigo' => strtoupper($row['codigo_plan']),					
					'estado' => $row['estado_sop']
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
	public function registrar_so()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
    	if($this->model_operacion->m_buscar_operacion_so($allInputs)){
			$arrData['message'] = 'Ya existe otra Sub Operación con el mismo nombre';
    		$arrData['flag'] = 0; 
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;    
    	}

    	if($this->model_operacion->m_buscar_operacion_so_codigo($allInputs)){
			$arrData['message'] = 'Ya existe otra Sub Operación con el mismo codigo';
    		$arrData['flag'] = 0; 
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;    
    	}      	
		if($this->model_operacion->m_registrar_so($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function editar_so()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// VALIDACIONES
    	if(!empty($allInputs['Change_des'])){
	    	if($this->model_operacion->m_buscar_operacion_so($allInputs)){
				$arrData['message'] = 'Ya existe otra Sub Operación con el mismo nombre';
	    		$arrData['flag'] = 0; 
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;    
	    	}
    	}
    	if(!empty($allInputs['Change_cod'])){
	    	if($this->model_operacion->m_buscar_operacion_so_codigo($allInputs)){
				$arrData['message'] = 'Ya existe otra Sub Operación con el mismo codigo';
	    		$arrData['flag'] = 0; 
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;    
	    	}    	
    	}
		if($this->model_operacion->m_editar_so($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_so()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;

		if( $this->model_operacion->m_anular_so($allInputs['id']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}		


}

?>

