<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SolicitudProcedimiento extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_solicitud_procedimiento'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_procedimientos_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_solicitud_procedimiento->m_cargar_solicitud_procedimientos_paciente($paramPaginate,$paramDatos);
		$totalRows = $this->model_solicitud_procedimiento->m_count_solicitud_procedimientos_paciente($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 

			if( $row['estado_sp'] == 1 ){ // SOLICITADO 
				$objEstado['claseIcon'] = 'fa-wait';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'SOLICITADO';
			}elseif( $row['estado_sp'] == 2 ){ // REALIZADO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'REALIZADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idsolicitudprocedimiento'],
					'idhistoria' => $row['idhistoria'],
					'acto_medico' => $row['idatencionmedica'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['nombre'],
					'tiposolicitud' => 2, // IMPORTANTE PARA SABER A CUAL DE LAS TRES TABLAS IR [1:examen auxiliar; 2:procedimiento; 3:documento] 
					'idtipoproducto' => $row['idtipoproducto'],
					'idproducto' => $row['idproductomaster'],
					'tipo_producto' => strtoupper($row['nombre_tp']),
					'producto' => strtoupper($row['producto']),
					'cantidad' => (int)$row['cantidad'],
					'precio' => $row['precio'],
					'informe' => $row['informe'],
					'fecha_solicitud' => formatoFechaReporte($row['fecha_solicitud']), 
					'fecha_realizacion' => empty($row['fecha_realizacion']) ? '-' : formatoFechaReporte($row['fecha_realizacion']) , 
					'estado' => $objEstado,
					'tiene_prog_cita' => ($row['tiene_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_cita' => ($row['tiene_venta_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_prog_proc' => ($row['tiene_prog_proc'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_proc' => ($row['tiene_venta_prog_proc'] == 1) ? TRUE : FALSE, 
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
	public function lista_procedimiento_de_especialidad_autocomplete()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); 
		// Ya no se necesita amarrar el producto a la especialidad 14-06-2016, ya que pueden solicitar procedimientos de otras especialidades 

		if($allInputs['especialidad']['id'] == 0){
    		$arrData['message'] = 'Debe Seleccionar una Especialidad';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

		$lista = $this->model_solicitud_procedimiento->m_cargar_procedimientos_para_orden_autocomplete($allInputs['searchColumn'],$allInputs['searchText'],$allInputs['especialidad']);
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
	public function lista_solicitudes_procedimiento_session(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];

		$lista = $this->model_solicitud_procedimiento->m_cargar_solicitudes_procedimiento_session($paramPaginate,$paramDatos);
		$totalRows = $this->model_solicitud_procedimiento->m_count_solicitudes_procedimiento_session($paramPaginate,$paramDatos);
		$arrListado = array();
		//var_dump($lista); exit();
		foreach ($lista as $row) {
			if( $row['estado_sp'] == 1 ){
				if( $row['paciente_atendido_det'] == 1 || $row['paciente_atendido_det'] == 2 ){
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-info';
					$objEstado['labelText'] = 'VENDIDO';
				}else{
					$objEstado['claseIcon'] = 'fa-spin fa-circle-o-notch';
					$objEstado['claseLabel'] = 'label-warning';
					$objEstado['labelText'] = 'SOLICITADO';
				}
			}elseif( $row['estado_sp'] == 0 ){ // ANULADO
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			
			array_push($arrListado,
				array(
					'idsolicitudprocedimiento' => $row['idsolicitudprocedimiento'], 
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
					'estado_sp' => $row['estado_sp'],
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
		$this->load->view('solicitudes/solicitudProcedimiento_formView');
	}
	public function registrar_solicitud_procedimiento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_solicitud_procedimiento->m_registrar_solicitud_procedimiento($allInputs) ) { 
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
	public function editar_cantidad_solicitud_procedimiento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if( $this->model_solicitud_procedimiento->m_editar_inline_solicitud_procedimiento($allInputs) ) { 
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
	public function anular_solicitud_procedimiento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit();
		$arrData['message'] = 'No se pudieron eliminar los datos'; 
    	$arrData['flag'] = 0;
		if( $this->model_solicitud_procedimiento->m_anular_solicitud_procedimiento($allInputs['id']) ){ 
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}