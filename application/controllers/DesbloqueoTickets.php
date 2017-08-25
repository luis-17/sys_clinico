<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class DesbloqueoTickets extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('fechas_helper','otros_helper'));
		$this->load->model(array('model_desbloqueoTickets','model_venta'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_pacientes_bloqueados_especialidad(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_desbloqueoTickets->m_cargar_pacientes_bloqueados($paramPaginate,$paramDatos);
		$totalRows = $this->model_desbloqueoTickets->m_count_pacientes_bloqueados($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			$fecha_venta = date_create(date('Y-m-d',strtotime($row['fecha_venta'])));
			$hoy = date_create(date('Y-m-d'));
			$intervalo = date_diff($fecha_venta, $hoy);
			$bloquea = FALSE;
			if( ($intervalo->days > $row['dias_libres']) && ($intervalo->invert == 0) ){
				$bloquea = TRUE;
			}
			$objEstado = array();
			if( $row['paciente_atendido_det'] == '2'){
				$clase = 'label-default';
				$string = 'SIN ATENDER';
			}else{
				$clase = 'label-success';
				$string = 'ATENDIDO';
			}
			if( $row['paciente_atendido_det'] == '2' ){ // NO ESTA ATENDIDO SE MUESTRA EL SWITCH
				if( $row['tiene_autorizacion'] == '1' ){
					$objEstado['claseIcon'] = 'fa-check';
					$objEstado['claseLabel'] = 'label-success';
					$objEstado['labelText'] = 'AUTORIZADO';
					$objEstado['display'] = TRUE;
					$objEstado['boolBloqueo'] = FALSE;
				}elseif( $row['tiene_autorizacion'] == '2' && $bloquea ){
					$objEstado['claseIcon'] = 'fa-ban';
					$objEstado['claseLabel'] = 'label-danger';
					$objEstado['labelText'] = 'BLOQUEADO';
					$objEstado['display'] = TRUE;
					$objEstado['boolBloqueo'] = TRUE;
				}
			}else{
				$objEstado['claseIcon'] = '';
				$objEstado['claseLabel'] = '';
				$objEstado['labelText'] = '';
				$objEstado['display'] = FALSE;
				$objEstado['boolBloqueo'] = TRUE;
			}

			
			array_push($arrListado, array(
				'idventa' => $row['idventa'],
				'orden_venta' => $row['orden_venta'],
				'ticket_venta' => $row['ticket_venta'],
				'fecha_venta' => formatoFechaReporte4($row['fecha_venta']),
				'idcliente' => $row['idcliente'],
				'idhistoria' => $row['idhistoria'],
				'paciente' => $row['paciente'],
				// 'nombres' => $row['nombres'],
				// 'apellido_paterno' => $row['apellido_paterno'],
				// 'apellido_materno' => $row['apellido_materno'],
				'edadActual' => $row['edad'],
				'iddetalle' => $row['iddetalle'],
				'fecha_atencion_det' => $row['fecha_atencion_det'],
				'cantidad' => $row['cantidad'],
				'producto' => $row['producto'],
				'paciente_atendido_det' => $row['paciente_atendido_det'],
				'tiene_autorizacion' => $row['tiene_autorizacion'],
				'nombre_tp' => $row['nombre_tp'],
				'estado' => array(
					'clase'=> $clase,
					'string'=> $string
					),
				'estado_bloq' => $objEstado
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
	public function bloquea_desbloquea_venta_paciente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ocurrió un error, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_desbloqueoTickets->m_desbloquear_venta_paciente($allInputs)) {
			if( $allInputs['tiene_autorizacion'] == 2 ){
				$arrData['message'] = 'Se desbloqueó la venta correctamente';
			}else{
				$arrData['message'] = 'Se bloqueó la venta correctamente';
			}
			
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}