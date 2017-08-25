<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SolicitudCitt extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_solicitud_citt'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_citt_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_solicitud_citt->m_cargar_solicitud_citt_paciente($paramPaginate,$paramDatos);
		//var_dump($lista); exit();
		$totalRows = $this->model_solicitud_citt->m_count_solicitud_citt_paciente($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 

			array_push($arrListado, 
				array(
					'id' => $row['id'],
					'acto_medico' => $row['idatencionmedica'],
					'tipoatencion' => strtoupper($row['descripcion_aho']),
					'contingencia' => strtoupper($row['descripcion_ctg']),
					'tiposolicitud' => 3, // IMPORTANTE PARA SABER A CUAL DE LAS TRES TABLAS IR [1:examen auxiliar; 2:procedimiento; 3:documento] 
					// falta asignarle producto y especialidad
					'idespecialidad' => $row['idespecialidad'], 
					'especialidad' => $row['nombre'],
					'idtipoproducto' => $row['idtipoproducto'], 
					'tipo_producto' => strtoupper($row['nombre_tp']), 
					'idproducto' => $row['idproductomaster'], 
					'producto' => strtoupper($row['producto']),
					'cantidad' => 1,
					'precio' => $row['precio'], 
					'fecha_otorgamiento' =>  date('d-m-Y', strtotime($row['fec_otorgamiento'])),
					'fecha_inicio' => date('d-m-Y', strtotime($row['fec_iniciodescanso'])), 
					'dias' => (int)$row['total_dias'],
					'estado' => $row['estado_citt']
					//if($row['idtipoatencion']==1)
					//{
						//'tipoatencion' => 'CONSULTA EXTERNA';
					//}
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
	// =========================================================== 
	public function obtener_producto_citt(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump("<pre>",$allInputs); exit();
		$idespecialidad = $allInputs['idespecialidad'];
		$lista = $this->model_solicitud_citt->m_get_producto_citt($idespecialidad);
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idproducto' => $row['idproductomaster'], 
					'producto' => strtoupper($row['descripcion']),
					'idespecialidad' => $row['idespecialidad']
				
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

	public function registrar_solicitud_citt()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_solicitud_citt->m_registrar_solicitud_citt($allInputs) ) { 
			$arrData['message'] = 'Se registraron los datos correctamente'; 
			$arrData['flag'] = 1; 
		}else{ 
			$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
			$arrData['flag'] = 0; 
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_solicitud_citt()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudieron anular los datos';
    	$arrData['flag'] = 0;
		if( $this->model_solicitud_citt->m_anular($allInputs['id']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
	    	$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}


}