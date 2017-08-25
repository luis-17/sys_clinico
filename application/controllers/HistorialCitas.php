<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialCitas extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_historial_citas','model_prog_medico','model_prog_cita'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_historial_citas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_historial_citas->m_cargar_citas_historial($paramPaginate,$paramDatos); 
		$totalRows = $this->model_historial_citas->m_count_sum_citas_historial($paramPaginate,$paramDatos); 
		$arrListado = array(); 
		foreach ($lista as $row) { 
			$objEstado = array();
			// $objEstado['claseIconAtendido'] = ''; 
			if( $row['estado_cita'] == 0 || $row['estado'] == 0){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			if( $row['estado_cita'] == 1 ){ // RESERVADO  
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-inverse';
				$objEstado['labelText'] = 'RESERVADO';
			}
			if( $row['estado_cita'] == 2 || $row['paciente_atendido_det'] == 2){ // CONFIRMADO 
				$objEstado['claseIcon'] = 'fa-check'; 
				$objEstado['claseLabel'] = 'label-success'; 
				$objEstado['labelText'] = 'CONFIRMADO'; 
			}
			if( $row['estado_cita'] == 3 ){ // CANCELADO 
				$objEstado['claseIcon'] = 'fa-exclamation-triangle'; 
				$objEstado['claseLabel'] = 'label-warning'; 
				$objEstado['labelText'] = 'CANCELADO'; 
			}
			if( $row['estado_cita'] == 4 ){ // REPROGRAMADO 
				$objEstado['claseIcon'] = 'fa-check'; 
				$objEstado['claseLabel'] = 'label-primary'; 
				$objEstado['labelText'] = 'REPROGRAMADO'; 
			}
			if( $row['estado_cita'] == 5 || $row['paciente_atendido_det'] == 1){ // ATENDIDO 
				$objEstado['claseIcon'] = 'fa-check'; 
				$objEstado['claseLabel'] = 'label-info'; 
				$objEstado['labelText'] = 'ATENDIDO'; 
			}

			$strTurno = darFormatoHora($row['hora_inicio_det']).' a '.darFormatoHora($row['hora_fin_det']); 
			$strSiAdicional = '';
			$preStr = '';
			if( $row['si_adicional'] == 1 ){ // SI
				$strSiAdicional = 'SI'; 
				$preStr.= '+ ';
			}elseif($row['si_adicional'] == 2 ){ // NO 
				$strSiAdicional = 'NO'; 
				$preStr.= 'nº ';
			}

			array_push($arrListado, 
				array(
					'idprogcita' => $row['idprogcita'],
					'fecha_reg_cita' => $row['fecha_reg_cita'],
					'fecha_reg_reserva' => $row['fecha_reg_reserva'],
					'fecha_atencion_cita' => $row['fecha_atencion_cita'],
					'turno' => $strTurno,
					'si_adicional_str' => $strSiAdicional,
					'si_adicional' => ($row['si_adicional'] == 1)? TRUE:FALSE, 
					'numero_cupo' => $preStr.$row['numero_cupo'],
					'estado_cupo' => $row['estado_cupo'],
					'idcanal' => $row['idcanal'], 
					'canal' => $row['descripcion_can'],
					'idprogmedico'=> $row['idprogmedico'],
					'tipo_atencion_medica'=> $row['tipo_atencion_medica'],
					'idambiente' => $row['idambiente'],
					'ambiente' => $row['numero_ambiente'],
					'piso' => $row['piso'],
					'idventa' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'nombres' => $row['nombres'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'email' => $row['email'],
					'edad' => devolverEdad($row['fecha_nacimiento']),
					'producto'=> $row['descripcion'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idmedico' => $row['idmedico'],
					'medico' => $row['medico'],
					'monto' => $row['total_detalle'],
					'estado' => $objEstado,
					'estado_cita' => $row['estado_cita'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'fecha_programada' => date('d-m-Y',strtotime($row['fecha_atencion_cita'])),
					'hora_inicio_formato' => darFormatoHora($row['hora_inicio_det']),
					'hora_fin_formato' => darFormatoHora($row['hora_fin_det']),
					'intervalo_hora_int' => date('i',strtotime($row['intervalo_hora'])),
					'numero_ambiente' => $row['numero_ambiente'],
					'empresa' => $row['empresa'],
					'iddetalleprogmedico' => $row['iddetalleprogmedico'],
					'idempresacliente' => $row['idempresacliente'],
					'idproductomaster' => $row['idproductomaster'],
					'iddetalle' => $row['iddetalle'],
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : number_format($totalRows['sumatotal'],2);
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function verifica_estado_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$cita = $this->model_prog_cita->m_conculta_cita_cupo($allInputs['cita']['iddetalleprogmedico']);
		if($cita['estado_cita'] != 2){
    		$arrData['message'] = 'Solo puede modificar citas en estado CONFIRMADO.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if($this->model_prog_cita->m_cita_tiene_atencion($allInputs['cita'])){
    		$arrData['message'] = 'No puede modificar una cita con atención registrada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 

    	$arrData['flag'] = 1;
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	    return;
	}

}