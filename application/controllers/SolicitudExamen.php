<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SolicitudExamen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_solicitud_examen'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_solicitudes_examen_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_solicitud_examen->m_cargar_solicitud_examenes_paciente($paramPaginate,$paramDatos);
		$totalRows = $this->model_solicitud_examen->m_count_solicitud_examenes_paciente($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado_sex'] == 1 ){ // SOLICITADO 
				$objEstado['claseIcon'] = 'fa-wait';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'SOLICITADO';
			}elseif( $row['estado_sex'] == 2 ){ // REALIZADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'REALIZADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idsolicitudexamen'], 
					'idhistoria' => $row['idhistoria'], 
					'acto_medico' => $row['idatencionmedica'], 
					'idespecialidad' => $row['idespecialidad'], 
					'especialidad' => $row['nombre'], 
					'idtipoproducto' => $row['idtipoproducto'], 
					'tiposolicitud' => 1, // IMPORTANTE PARA SABER A CUAL DE LAS TRES TABLAS IR [1:examen auxiliar; 2:procedimiento; 3:documento] 
					'tipo_producto' => strtoupper($row['nombre_tp']), 
					'idproducto' => $row['idproductomaster'], 
					'producto' => $row['idproductomaster'].'.- '.strtoupper($row['producto']), 
					'cantidad' => 1, 
					'precio' => $row['precio'], 
					'indicaciones' => $row['indicaciones'], 
					'fecha_solicitud' => formatoFechaReporte($row['fecha_solicitud']), 
					'fecha_realizacion' => empty($row['fecha_realizacion']) ? '-' : formatoFechaReporte($row['fecha_realizacion']), 
					'estado' => $objEstado 
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
	public function lista_examen_auxiliar_de_especialidad_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		if($allInputs['tipoExamen'] === 'I') { 
			$tipoProducto = 14; // IMAGENOLOGIA 
		}elseif ($allInputs['tipoExamen'] === 'PC') { 
			$tipoProducto = 15; // LABORATORIO 
		}elseif ($allInputs['tipoExamen'] === 'AP') { 
			$tipoProducto = 11; // ANATOMIA PATOLOGICA 
		}

		$lista = $this->model_solicitud_examen->m_cargar_examen_auxiliar_de_especialidad_session_autocomplete($allInputs['searchColumn'],$allInputs['searchText'],$tipoProducto,$allInputs['especialidad']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => $row['idproductomaster'], 
					'idespecialidad' => $row['idespecialidad'], 
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
	public function lista_solicitudes_examen_session(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_solicitud_examen->m_cargar_solicitudes_examen_session($paramPaginate,$paramDatos);
		$totalRows = $this->model_solicitud_examen->m_count_solicitudes_examen_session($paramPaginate,$paramDatos);
		$arrListado = array();
		//var_dump($lista); exit();
		foreach ($lista as $row) {
			if( $row['estado_sex'] == 1 ){
				if( $row['paciente_atendido_det'] == 1 ){
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-info';
					$objEstado['labelText'] = 'REALIZADO';
				}else{
					$objEstado['claseIcon'] = 'fa-spin fa-circle-o-notch';
					$objEstado['claseLabel'] = 'label-warning';
					$objEstado['labelText'] = 'SOLICITADO';
				}
			}elseif( $row['estado_sex'] == 0 ){ // ANULADO
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			
			array_push($arrListado,
				array(
					'idsolicitudexamen' => $row['idsolicitudexamen'], 
					'fecha_solicitud' => formatoFechaReporte($row['fecha_solicitud']), 
					'idhistoria' => strtoupper($row['idhistoria']),
					'paciente' => $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', '. $row['nombres'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'medico' => $row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', '. $row['med_nombres'],
					'fecha_realizacion' => empty($row['fecha_atencion_det']) ? '-' : formatoFechaReporte($row['fecha_atencion_det']), 
					'paciente_atendido_det' => $row['paciente_atendido_det'],
					'idatencionmedica' => $row['idatencionmedica'],
					'estado_sp' => $row['estado_sex'],
					'estado' => $objEstado 

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
	public function ver_popup_solicitud()
	{
		$this->load->view('solicitudes/solicitudExamen_formView');
	}
	public function registrar_solicitud_examen_auxiliar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_solicitud_examen->m_registrar_solicitud_examen_auxiliar($allInputs) ) { 
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
	public function anular_solicitud_examen_auxiliar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit();
		$arrData['message'] = 'No se pudieron eliminar los datos'; 
    	$arrData['flag'] = 0;
		if( $this->model_solicitud_examen->m_anular_solicitud_examen_auxiliar($allInputs['id']) ){ 
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// public function editar_cantidad_solicitud_procedimiento()
	// {
	// 	$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
	// 	$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
 //    	$arrData['flag'] = 0;
 //    	$this->db->trans_start();
	// 	if( $this->model_solicitud_examen->m_editar_inline_solicitud_procedimiento($allInputs) ) { 
	// 		$arrData['message'] = 'Se registraron los datos correctamente'; 
	// 		$arrData['flag'] = 1; 
	// 	}else{ 
	// 		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
	// 		$arrData['flag'] = 0; 
	// 	} 
	// 	$this->db->trans_complete();
	// 	$this->output
	// 	    ->set_content_type('application/json')
	// 	    ->set_output(json_encode($arrData));
	// }
}