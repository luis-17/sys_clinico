<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionMedicaHistorial extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper'));
		$this->load->model(array('model_atencion_medica','model_prog_cita'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_atencion_medica_historial()
	{ 	
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		ini_set('max_execution_time', 600); /* IMPORTANTE */
  		ini_set('memory_limit','3G'); /* IMPORTANTE */
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_atencion_medica->m_cargar_historial_ventas_atendidas($paramPaginate,$paramDatos); 
		$totalRows = $this->model_atencion_medica->m_count_historial_ventas_atendidas($paramPaginate,$paramDatos);
		// var_dump($lista); exit(); 
		$arrListado = array();
		// var_dump($lista); exit(); 
		foreach ($lista as $row) { 
			$objEstado = array();
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'num_acto_medico' => $row['idatencionmedica'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_atencion' => formatoFechaReporte($row['fecha_atencion']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['cliente']),
					'edad' => $row['edad'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idempresa' => $row['idempresa'],
					'empresa' => $row['empresa'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'fecha_atencion' => $row['fecha_atencion'],
					'orden_venta' => $row['orden_venta'],
					'ticket_venta' => $row['ticket_venta'],
					'importe' => $row['total_detalle_sf'],
					'fecha_venta' => $row['fecha_venta'],
					'personalatencion' => array(
						'id' => $row['idmedicoatencion'],
						'descripcion' => $row['medicoatencion']
					),
					'fecha_venta_str' => darFormatoFecha($row['fecha_venta'])
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['paginate']['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
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
	public function lista_atencion_medica_por_id()
	{ 	
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$paramDatos = $allInputs['num_acto_medico'];
		$lista = $this->model_atencion_medica->m_cargar_atencion_medica_por_id($paramDatos); 
		$arrListado = array();
		// var_dump($lista); exit(); 
		foreach ($lista as $row) { 
			//$rowFechaVenta = $row['fecha_venta'];
			$objEstado = array();
			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'num_acto_medico' => $row['idatencionmedica'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_atencion' => formatoFechaReporte($row['fecha_atencion']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['cliente']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'], // solo año
					'edadEnAtencion' => strtoupper_total(devolverEdadAtencion($row['fecha_nacimiento'],$row['fecha_atencion'])), // año y meses
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['especialidad'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'idareahospitalaria' => $row['idareahospitalaria'],
					'area_hospitalaria' => $row['descripcion_aho'],
					'anamnesis' => $row['anamnesis'],
					'presion_arterial_mm' => $row['presion_arterial_mm'],
					'presion_arterial_hg' => $row['presion_arterial_hg'],
					'frecuencia_cardiaca_lxm' => $row['frec_cardiaca'],
					'frecuencia_respiratoria' => $row['frec_respiratoria'],
					'temperatura_corporal' => $row['temperatura_corporal'],
					'peso' => $row['peso'],
					'talla' => $row['talla'],
					'imc' => $row['imc'],
					'gestando' => (int)$row['gestando'],
					'fur' => darFormatoDMY($row['fecha_ultima_regla']),
					'fpp' => darFormatoFecha($row['fecha_probable_parto']),
					'semana_gestacion' => $this->calcular_semana_gestacion($row['fecha_ultima_regla'],'other'),
					'perimetro_abdominal' => $row['perimetro_abdominal'],
					'antecedentes' => $row['antecedentes'],
					'observaciones' => $row['observaciones'],
					'examen_clinico' => $row['examen_clinico'],
					'atencion_control' => (int)$row['atencion_control'],
					//'fechaVenta' => darFormatoFecha($row['fecha_atencion']),
					'fecha_atencion' => $row['fecha_atencion'],
					'fechaAtencion' => darFormatoFecha($row['fecha_atencion']),
					'horaAtencion' => darFormatoHora($row['fecha_atencion']),
					'orden_venta' => $row['orden_venta'],
					'ticket_venta' => $row['ticket_venta'],
					/* PARA PROCEDIMIENTO */
					'proc_observacion' => $row['proc_observacion'],
					'proc_informe' => strip_tags($row['proc_informe']),
					/* PARA EXAMEN AUXILIAR */
					'indicaciones' => $row['ex_indicaciones'],
					'ex_informe' => strip_tags($row['ex_informe']),
					'tipoResultado' => (int)$row['ex_tipo_resultado'],
					'personal' => array(
						'id' => $row['ex_responsable_medico'],
						'descripcion' => $row['medico']
					),
					'personalatencion' => array(
						'id' => $row['idmedicoatencion'],
						'descripcion' => $row['medicoatencion']
					),
					/* PARA DOCUMENTOS */
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'doc_informe' => @$row['doc_informe'],
					/* VENTA / DETALLE */
					'importe' => $row['total_detalle'],
					'fecha_venta' => $row['fecha_venta'],
					'fecha_venta_str' => darFormatoFecha($row['fecha_venta'])
				)
			);
		}
		$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['datos'] = NULL;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function calcular_semana_gestacion($fur = FALSE, $param = FALSE) 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrListado = array();
		if(empty($fur)) { 
			if( empty($param) ){
				$fur = $allInputs['fur']; 
			}else{
				return; 
			}
		}
		$arrData['flag'] = 0; 
		$arrFur = explode("-", $fur); // var_dump($arrFur); exit(); 
		if( is_numeric($arrFur[1]) && is_numeric($arrFur[0]) && is_numeric($arrFur[2]) ){ 
			if(checkdate($arrFur[1], $arrFur[0], $arrFur[2])){ 
				$desde = date('Y-m-d',strtotime("$fur"));
				$hasta = date('Y-m-d');
				$arrListado['diasTranscurridos'] = get_dias_transcurridos($desde,$hasta);
				if($arrListado['diasTranscurridos'] && $arrListado['diasTranscurridos'] > 0){
					$arrListado['semanasTranscurridas'] = ($arrListado['diasTranscurridos'] / 7);
				}
				$arrListado['semanasTranscurridas'] = round($arrListado['semanasTranscurridas'],0);
		    	$arrData['datos'] = $arrListado;
		    	$arrData['message'] = '';
		    	$arrData['flag'] = 1;
			}
		}
		
		if(empty($arrListado['semanasTranscurridas'])){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_atencion_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;

    	// VALIDACION PARA QUE MANDE ALERTA SI NO ES DEL MES ACTUAL
    	foreach ($allInputs as $row) {
	    	$mes_atencion = date('m', strtotime($row['fecha_atencion']));
	    	$ano_atencion = date('Y', strtotime($row['fecha_atencion']));
	    	$mes_actual = date('m');
	    	$ano_actual = date('Y');

	    	if( ($mes_atencion != $mes_actual) || ($ano_atencion != $ano_actual) ){
	    		$arrData['message'] = 'No se puede anular. <br> La atención no corresponde al mes actual';
	    		$arrData['flag'] = 0;
	    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return;
	    	}
    	}
    	$this->db->trans_start();
    	foreach ($allInputs as $row) { 
			if( $this->model_atencion_medica->m_anular_atencion_medica($row['num_acto_medico']) ){ 
				/* TRANSFORMAR LA VENTA/DETALLE NO ATENDIDO */ 
				$this->model_atencion_medica->m_actualizar_venta_a_no_atendido($row['id']); 
				$this->model_atencion_medica->m_actualizar_detalle_venta_a_no_atendido($row['iddetalle']); 

				/*COLOCAR LA CITA COMO NO ATENDIDA*/
				$this->model_prog_cita->m_actualizar_cita_a_no_atendida($row['iddetalle']);

				$arrData['message'] = 'Se anularon los datos correctamente'; 
	    		$arrData['flag'] = 1; 
			} 
		} 
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cambiar_empresa_de_atencion()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo modificar los datos';
    	$arrData['flag'] = 0;

    	// VALIDACION PARA QUE MANDE ALERTA SI NO ES DEL MES ACTUAL
    	// foreach ($allInputs as $row) {
	    // 	$mes_atencion = date('m', strtotime($row['fecha_atencion']));
	    // 	$ano_atencion = date('Y', strtotime($row['fecha_atencion']));
	    // 	$mes_actual = date('m');
	    // 	$ano_actual = date('Y');

	    // 	if( ($mes_atencion != $mes_actual) || ($ano_atencion != $ano_actual) ){
	    // 		$arrData['message'] = 'No se puede anular. <br> La atención no corresponde al mes actual';
	    // 		$arrData['flag'] = 0;
	    // 		$this->output
			  //   ->set_content_type('application/json')
			  //   ->set_output(json_encode($arrData));
			  //   return;
	    // 	}
    	// }
    	
		if( $this->model_atencion_medica->m_modificar_empresa_de_venta($allInputs) && $this->model_atencion_medica->m_modificar_empresa_de_atencion($allInputs) ){ 
			$arrData['message'] = 'Se modificaron los datos correctamente'; 
    		$arrData['flag'] = 1; 
		} 
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function validar_permisos_edicion_atencion()
	{
		$arrData['message'] = '';
    	$arrData['flag'] = 1;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit();
    	if( ($this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud') && $allInputs['idtipoproducto'] == 16 ){ 
    		// var_dump( (strtotime($allInputs['fecha_atencion']) + (2 * 24 * 60 * 60)), time() ); exit();
    		if( strtotime($allInputs['fecha_atencion']) + (2 * 24 * 60 * 60) <= ( time() ) ){ // 2 dias con 24 horas con 60 min con 60 seg
				$arrData['message'] = 'No puede editar esta atención porque ya pasaron mas de 3 días.'; 
    			$arrData['flag'] = 2;
    		}
    	}
    	if( ($this->sessionHospital['key_group'] === 'key_salud' || $this->sessionHospital['key_group'] === 'key_dir_salud') && $allInputs['idtipoproducto'] == 12 ){ 
    		// var_dump('entro consulta' ); exit();
    		if( strtotime($allInputs['fecha_atencion']) + (24 * 60 * 60) <= ( time() ) ){ // 2 dias con 24 horas con 60 min con 60 seg
				$arrData['message'] = 'No puede editar esta atención porque ya pasó mas de 1 día.'; 
    			$arrData['flag'] = 2;
    		}
    	}
    	// F.A. = 08/07/2017  || F.H. = 11/07/2017 
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}