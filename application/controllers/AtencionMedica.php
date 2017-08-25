<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionMedica extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','otros_helper','imagen'));
		$this->load->model(array('model_atencion_medica','model_venta','model_empresa', 'model_receta_medica', 'model_prog_cita','model_venta_web', 'model_especialidad'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima"); 
	}
	public function lista_pacientes_no_atendidos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 

		$arrFilter = array();
		$arrFilter['searchTipo'] = FALSE;
		if( $allInputs['tipoBusqueda'] === 'PNO' ){ 
			$arrFilter['searchColumn'] = 'orden_venta';
			$arrFilter['searchText'] = $allInputs['numeroOrden']; 
		}elseif( $allInputs['tipoBusqueda'] === 'PH' ){ 
			$arrFilter['searchColumn'] = 'h.idhistoria';
			$arrFilter['searchText'] = $allInputs['numeroHistoria']; 
		}elseif( $allInputs['tipoBusqueda'] === 'PP' ){ 
			$arrFilter['searchColumn'] = "UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))";
			$arrFilter['searchText'] = $allInputs['paciente']; 
			$arrFilter['searchTipo'] = $allInputs['tipoBusqueda']; 
		}
		$arrFilter['arrTipoProductos'] = $allInputs['arrTipoProductos'];
		$lista = $this->model_atencion_medica->m_cargar_esta_venta_por_busqueda_atencion($arrFilter); // var_dump("<pre>",$arrFilter); exit(); 
		$arrListado = array();
		foreach ($lista as $row) { 
			$rowFechaVenta = $row['fecha_venta'];
			$objEstado = array();
			$fecha_venta = date_create(date('Y-m-d',strtotime($row['fecha_venta']))); 
			$hoy = date_create(date('Y-m-d'));
			$intervalo = date_diff($fecha_venta, $hoy);
			$bloquea = FALSE;
			/* SE DESACTIVA HASTA CORREGIR LAS FECHAS DE LAS CAMPAÑAS*/
			// if($row['si_tipo_campania'] == 1){
			// 	if($listcampania = $this->model_atencion_medica->cargar_fechas_atencion_campania($row['idcampania'])){
			// 		$bloquea = false;
			// 	}else{
			// 		$bloquea = true ;
			// 	}
			// }else{
			if( ($intervalo->days > $row['dias_libres']) && ($intervalo->invert == 0) ){
				$bloquea = TRUE;
			} 
			if( $row['paciente_atendido_det'] == 2 ){ // NO  
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR ATENDER';
				$objEstado['autorizado'] = 1; // SI
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea ){ // SI NO ES EL DIA DE HOY   
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'BLOQUEADO';
				$objEstado['autorizado'] = 2; // NO
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea && $row['tiene_autorizacion'] == 1 ){ // SI NO ES EL DIA DE HOY PERO TIENE AUTORIZACION  
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'AUTORIZADO';
				$objEstado['autorizado'] = 1; // SI
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea && 
				(
					$row['idtipoproducto'] == 11 || $row['idtipoproducto'] == 15 || $row['atencion_dia'] == 2 
				) 
			){ 
				// SI NO ES EL DIA DE HOY PERO ANATOMIA PATOLOGICA(11) O LABORATORIO(15) 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'AUTORIZADO';
				$objEstado['autorizado'] = 1; // SI
			}

			if( $row['paciente_atendido_det'] == 2 && !empty($row['idnotacreditodetalle']) ){ 
				$objEstado['claseIcon'] = 'fa-check'; 
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'NOTA DE CRÉDITO';
				$objEstado['autorizado'] = 2; // NO 
			}

			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			$especialidad = $this->model_atencion_medica->m_get_especialidad_by_id($row['idespecialidad']);
			//var_dump($objEstado);exit();
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'],
					'edadEnAtencion' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'situacion' => $objEstado,
					'idsolicitudprocedimiento' => $row['idsolicitudprocedimiento'],
					'idsolicitudexamen' => $row['idsolicitudexamen'],
					'idsolicitudcitt' => $row['idsolicitudcitt'],
					'indicaciones' => $row['indicaciones'],
					'observacion' => $row['observacion'],
					'cantidad' => $row['cantidad'],
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $especialidad['nombre']
				)
			);
		} 
		// var_dump($arrListado);exit();
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
	public function lista_pacientes_atendidos_del_dia() 
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_atencion_medica->m_cargar_ventas_atendidas_por_busqueda_del_dia($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) { 
			$rowFechaVenta = $row['fecha_venta'];
			$objEstado = array();
			if( $row['paciente_atendido_v'] == 2 ){ // NO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR ATENDER';
			}else if( $row['paciente_atendido_v'] == 1 ){ // SI 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'ATENDIDO';
			}
			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			array_push($arrListado, // medico
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'num_acto_medico' => $row['idatencionmedica'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_atencion' => formatoFechaReporte($row['fecha_atencion']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'],
					'edadEnAtencion' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
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
					'frec_cardiaca' => $row['frec_cardiaca'],
					'frec_respiratoria' => $row['frec_respiratoria'],
					'temperatura_corporal' => $row['temperatura_corporal'],
					'peso' => $row['peso'],
					'talla' => $row['talla'],
					'imc' => $row['imc'],
					'perimetro_abdominal' => $row['perimetro_abdominal'],
					'antecedentes' => $row['antecedentes'],
					'observaciones' => $row['observaciones'],
					'atencion_control' => $row['atencion_control'],
					'fecha_atencion' => $row['fecha_atencion'],
					'fechaAtencion' => darFormatoFecha($row['fecha_atencion']),
					'horaAtencion' => darFormatoHora($row['fecha_atencion']),
					'orden_venta' => $row['orden_venta'],
					'ticket_venta' => $row['ticket_venta'],
					'situacion' => $objEstado, 
					'personalatencion' => array(
						'id' => $row['idmedicoatencion'],
						'descripcion' => $row['medicoatencion']
					),
					'fInputs' => array( // PARA PINTAR EN EL EDITAR 
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
						'orden_venta' => $row['orden_venta'],
						'ticket_venta' => $row['ticket_venta'],
						'situacion' => $objEstado
					),
					/* PARA PROCEDIMIENTO */
					'observacion' => $row['proc_observacion'],
					'proc_informe' => strip_tags($row['proc_informe']),
					/* PARA EXAMEN AUXILIAR */
					'indicaciones' => $row['ex_indicaciones'],
					'ex_informe' => strip_tags($row['ex_informe']),
					'tipoResultado' => (int)$row['ex_tipo_resultado'],
					'personal' => array(
						'id' => $row['ex_responsable_medico'],
						'descripcion' => $row['medico']
					),
					/* PARA DOCUMENTOS */
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'doc_informe' => @strip_tags($row['doc_informe'])
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
	public function lista_paciente_programado_sin_atender()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// $arrFilter = array();
		// $arrFilter['searchTipo'] = FALSE;
		// if( $allInputs['tipoBusqueda'] === 'PNO' ){ 
		// 	$arrFilter['searchColumn'] = 'orden_venta';
		// 	$arrFilter['searchText'] = $allInputs['numeroOrden']; 
		// }elseif( $allInputs['tipoBusqueda'] === 'PH' ){ 
		// 	$arrFilter['searchColumn'] = 'h.idhistoria';
		// 	$arrFilter['searchText'] = $allInputs['numeroHistoria']; 
		// }elseif( $allInputs['tipoBusqueda'] === 'PP' ){ 
		// 	$arrFilter['searchColumn'] = "UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))";
		// 	$arrFilter['searchText'] = $allInputs['paciente']; 
		// 	$arrFilter['searchTipo'] = $allInputs['tipoBusqueda']; 
		// }
		// $arrFilter['arrTipoProductos'] = $allInputs['arrTipoProductos'];
		$lista = $this->model_atencion_medica->m_cargar_paciente_programado_sin_atender($allInputs); // var_dump("<pre>",$arrFilter); exit(); 
		
		$arrListado = array();
		foreach ($lista as $row) { 
			$rowFechaVenta = $row['fecha_venta'];
			$objEstado = array();
			$fecha_venta = date_create(date('Y-m-d',strtotime($row['fecha_venta'])));
			// $fecha_venta = date_create('2017-08-24');
			$hoy = date_create(date('Y-m-d'));
			$intervalo = date_diff($fecha_venta, $hoy);
			
			$bloquea = FALSE;
			// if( ($intervalo->days > $row['dias_libres']) && ($intervalo->invert == 0) ){
			// 	$bloquea = TRUE;
			// }
			// var_dump($intervalo);exit();
			if( $row['paciente_atendido_det'] == 2 ){ // NO  
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR ATENDER';
				$objEstado['autorizado'] = 1; // SI
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea ){ // SI NO ES EL DIA DE HOY   
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-default';
				$objEstado['labelText'] = 'BLOQUEADO';
				$objEstado['autorizado'] = 2; // NO
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea && $row['tiene_autorizacion'] == 1 ){ // SI NO ES EL DIA DE HOY PERO TIENE AUTORIZACION  
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'AUTORIZADO';
				$objEstado['autorizado'] = 1; // SI
			}
			if( $row['paciente_atendido_det'] == 2 && $bloquea && 
				(
					$row['idtipoproducto'] == 11 || $row['idtipoproducto'] == 15 || $row['atencion_dia'] == 2 
				) 
			){ 
				// SI NO ES EL DIA DE HOY PERO ANATOMIA PATOLOGICA(11) O LABORATORIO(15) 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-info';
				$objEstado['labelText'] = 'AUTORIZADO';
				$objEstado['autorizado'] = 1; // SI
			}
			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			$especialidad = $this->model_atencion_medica->m_get_especialidad_by_id($row['idespecialidad']);
			
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'origen_venta' => $allInputs['origen_venta'],
					'idprogcita' => empty($row['idprogcita'])? null: $row['idprogcita'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'],
					'edadEnAtencion' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipo_producto' => $row['nombre_tp'],
					'situacion' => $objEstado,
					'idsolicitudprocedimiento' => empty($row['idsolicitudprocedimiento']) ? null : $row['idsolicitudprocedimiento'],
					'idsolicitudexamen' => empty($row['idsolicitudexamen']) ? null : $row['idsolicitudexamen'],
					'idsolicitudcitt' => empty($row['idsolicitudcitt']) ? null : $row['idsolicitudcitt'],
					'indicaciones' => empty($row['indicaciones']) ? null : $row['indicaciones'],
					'observacion' => empty($row['observacion']) ? null : $row['observacion'],
					'cantidad' => $row['cantidad'],
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $especialidad['nombre']
				)
			);
		} 
		// var_dump($arrListado);exit();
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
	public function lista_paciente_programado_atendido_del_dia() 
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_atencion_medica->m_cargar_paciente_programado_atendido($allInputs);
		// var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) { 
			$rowFechaVenta = $row['fecha_venta'];
			$objEstado = array();
			if( $row['paciente_atendido_v'] == 2 ){ // NO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-warning';
				$objEstado['labelText'] = 'POR ATENDER';
			}else if( $row['paciente_atendido_v'] == 1 ){ // SI 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'ATENDIDO';
			}
			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			array_push($arrListado, // medico
				array(
					'id' => $row['idventa'],
					'iddetalle' => $row['iddetalle'],
					'num_acto_medico' => $row['idatencionmedica'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'fecha_atencion' => formatoFechaReporte($row['fecha_atencion']),
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'cliente' => strtoupper_total($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'],
					'edadEnAtencion' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
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
					'frec_cardiaca' => $row['frec_cardiaca'],
					'frec_respiratoria' => $row['frec_respiratoria'],
					'temperatura_corporal' => $row['temperatura_corporal'],
					'peso' => $row['peso'],
					'talla' => $row['talla'],
					'imc' => $row['imc'],
					'perimetro_abdominal' => $row['perimetro_abdominal'],
					'antecedentes' => $row['antecedentes'],
					'observaciones' => $row['observaciones'],
					'atencion_control' => $row['atencion_control'],
					'fecha_atencion' => $row['fecha_atencion'],
					'fechaAtencion' => darFormatoFecha($row['fecha_atencion']),
					'horaAtencion' => darFormatoHora($row['fecha_atencion']),
					'orden_venta' => $row['orden_venta'],
					'ticket_venta' => $row['ticket_venta'],
					'situacion' => $objEstado, 
					'personalatencion' => array(
						'id' => $row['idmedicoatencion'],
						'descripcion' => $row['medicoatencion']
					),
					'fInputs' => array( // PARA PINTAR EN EL EDITAR 
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
						'orden_venta' => $row['orden_venta'],
						'ticket_venta' => $row['ticket_venta'],
						'situacion' => $objEstado
					),
					/* PARA PROCEDIMIENTO */
					'observacion' => $row['proc_observacion'],
					'proc_informe' => strip_tags($row['proc_informe']),
					/* PARA EXAMEN AUXILIAR */
					'indicaciones' => $row['ex_indicaciones'],
					'ex_informe' => strip_tags($row['ex_informe']),
					'tipoResultado' => (int)$row['ex_tipo_resultado'],
					'personal' => array(
						'id' => $row['ex_responsable_medico'],
						'descripcion' => $row['medico']
					),
					/* PARA DOCUMENTOS */
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'doc_informe' => @strip_tags($row['doc_informe'])
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
	public function lista_historial_pacientes()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$lista = $this->model_atencion_medica->m_cargar_historial_atenciones_paciente($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) { 
			$rowFechaVenta = $row['fecha_venta'];
			$strSexo = '-';
			if( $row['sexo'] == 'M' ){ 
				$strSexo = 'MASCULINO';
			}elseif( $row['sexo'] == 'F' ){
				$strSexo = 'FEMENINO';
			}
			$strTipoResultado = '-';
			if( $row['ex_tipo_resultado'] == 1 ){
				$strTipoResultado = 'NORMAL';
			}elseif ( $row['ex_tipo_resultado'] == 2 ) {
				$strTipoResultado = 'PATOLOGICO';
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
					'cliente' => strtoupper_total($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
					'sexo' => $strSexo,
					'boolSexo' => $row['sexo'],
					'numero_documento' => $row['num_documento'],
					'edad' => $row['edad'],
					'edadEnAtencion' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
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
					'atencion_control' => $row['atencion_control'],
					//'fecha_atencion' => $row['fecha_atencion'],
					'fechaAtencion' => darFormatoFecha($row['fecha_atencion']),
					'horaAtencion' => darFormatoHora($row['fecha_atencion']),
					'orden_venta' => $row['orden_venta'],
					'ticket_venta' => $row['ticket_venta'],
					'personalatencion' => array( 
						'id' => $row['idmedicoatencion'],
						'descripcion' => $row['medicoatencion']
					),
					/* PARA PROCEDIMIENTO */
					'observacion' => strip_tags($row['proc_observacion']),
					'proc_informe' => strip_tags($row['proc_informe']),

					/* PARA EXAMEN AUXILIAR */
					'indicaciones' => strip_tags($row['ex_indicaciones']),
					'ex_informe' => strip_tags($row['ex_informe']),
					'tipoResultado' => (int)$row['ex_tipo_resultado'],
					'strTipoResultado' => $strTipoResultado,
					'personal' => array(
						'id' => $row['ex_responsable_medico'],
						'descripcion' => $row['medico']
					),
					/* PARA DOCUMENTOS */
					'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
					'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
					'dias' => empty($row['total_dias'])? null: $row['total_dias'],
					'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
					'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
					'doc_informe' => strip_tags(@$row['doc_informe']),
					'sede_empresa_admin' => $row['sede_empresa_admin'],
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
	public function lista_diagnosticos_de_atencion_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_atencion_medica->m_cargar_diagnosticos_de_atencion($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['iddiagnosticocie'],
					'codigo_diagnostico' => strtoupper_total($row['codigo_cie']),
					'diagnostico' => strtoupper_total($row['descripcion_cie']),
					'tipo' => $row['tipo_diagnostico']
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
	public function lista_recetas_de_atencion_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_atencion_medica->m_cargar_recetas_de_atencion($allInputs); 
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idrecetamedicamento'],
					'acto_medico' => $row['idatencionmedica'],
					'idreceta' => $row['idreceta'],
					'medicamento' => strtoupper_total($row['denominacion'].' '.$row['descripcion']),
					'cantidad' => (int)$row['cantidad'],
					'indicaciones' => $row['indicaciones'],
					'unidad' => $row['idunidadmedida'],
					'fecha' => formatoFechaReporte($row['fecha_receta'])
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
	public function Mostrar_Vista_Impresion()
	{
		$return = $this->load->view('atencionMedica/vista_impresion_documentos', '', TRUE);

		$arrData['html'] = $return;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario_afecciones()
	{
		$this->load->view('atencionMedica/afecciones_formView');
	}
	public function ver_popup_ficha_atencion_ambulatoria()
	{
		$this->load->view('atencionMedica/atencionMedicaAmb_fichaView');
	}
	public function ver_popup_formulario_atencion_ambulatoria()
	{
		$this->load->view('atencionMedica/atencionMedicaAmb_formView');
	}
	public function ver_popup_produccion_multiple()
	{
		$this->load->view('atencionMedica/produccion_multiple_formView');
	}
	public function ver_popup_cambiar_empresa()
	{
		$this->load->view('atencionMedica/cambioEmpresa_formView');
	}
	public function ver_popup_por_programacion()
	{
		$this->load->view('atencionMedica/por_programacion_formView');
	}
	public function ver_popup_por_programacion_procedimiento()
	{
		$this->load->view('atencionMedica/por_programacion_proc_formView');
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
	public function calcular_IMC()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrListado = array();
		$peso = $allInputs['peso'];
		$talla = $allInputs['talla'];
		$arrData['flag'] = 1; 
		if( $allInputs['peso'] > 0 && $allInputs['talla'] > 0 ){
			$imc = ((float)$allInputs['talla'] * (float)$allInputs['talla']) / (float)$allInputs['peso'] ;
			// var_dump(((float)$allInputs['talla'] * (float)$allInputs['talla'])); exit();
			$arrListado['imc'] = round($imc,2);
			$arrData['datos'] = $arrListado;
		}else{
			$arrData['flag'] = 0;
		}
		
		// if(empty($arrListado['semanasTranscurridas'])){ 
		// 	$arrData['flag'] = 0; 
		// }
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function calcular_FPP()
	{
		// CALCULO DE LA FECHA PROBABLE DE PARTO 
		// FPP = FUR + 1 año - 3 meses + 7 días
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrListado = array();
		$fur = $allInputs['fur'];
		$arrData['flag'] = 1; 
		// $arrFur = explode("-", $fur); // var_dump($arrFur); exit(); 
		$furMasUnAnio = date('Y-m-d',strtotime("$fur+1year")); 
		$furMasUnAnioMenosTresMeses = date('Y-m-d',strtotime("$furMasUnAnio-3months")); 
		$fpp = date('Y-m-d',strtotime("$furMasUnAnioMenosTresMeses+7days"));
		$arrListado['fpp'] = $fpp;
		if(empty($fpp)){ 
			$arrData['flag'] = 0; 
		}else{
			$arrData['datos'] = $arrListado; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_atencion_medica_ambulatoria()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL;
    	if(empty($allInputs['origen_venta'])){
    		$allInputs['origen_venta'] = 'C';
    	}
    	// VERIFICAR SI LA ESPECIALIDAD TIENE PROGRAMACION
    		if($this->model_especialidad->m_tiene_prog_citas($allInputs['idespecialidad'], $this->sessionHospital['idsede'])){
    			$tiene_programacion = TRUE;
    		}else{
    			$tiene_programacion = FALSE;
    		}
    	// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 	
    	if( $this->model_venta->m_validar_venta_con_nota_credito_atencion($allInputs)){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas, no se puede aprobar.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( empty($allInputs['fInputs']['gridDiagnostico']) || empty($allInputs['fInputs']['gridDiagnostico'][0]['id']) ){ 
    		$arrData['message'] = 'No se ha agregado ningún diagnóstico, a la atención médica.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	} 
    	
		// var_dump($allInputs['orden']); exit(); 
    	if(@$allInputs['origen_venta'] == 'W'){
    		$fCountValidate = $this->model_atencion_medica->m_validar_iddetalle_ventas_web_atendidas_hoy($allInputs['iddetalle']); 
    	}else{
    		$fCountValidate = $this->model_atencion_medica->m_validar_iddetalle_ventas_atendidas_hoy($allInputs['iddetalle']); 
    	} 
    	
    	if( !empty($fCountValidate['contador']) || $fCountValidate['contador'] > 0 ){ 
    		$arrData['message'] = 'Ya ha registrado la Atencion Médica.';
    		$arrData['flag'] = 2;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	/*print_r($allInputs);
    	exit();*/
    	
    	// VERIFICAR SI TIENE UN ODONTOGRAMA GUARDADO SIN ATENCION MEDICA 
    	if($datos_odontograma = $this->model_atencion_medica->m_verificar_odontograma_sin_atencion_medica($allInputs)){
    		$allInputs['idodontograma'] = $datos_odontograma[0]['idodontograma'];
    	}else{
    		$allInputs['idodontograma'] = 0; // el odontograma ya tiene atencion medica, no es necesario actualizar
    	}
    	$this->db->trans_start();
    	if($allInputs['origen_venta'] == 'W' && empty($allInputs['ticket'])){
    		$allInputs['ticket'] = 'EN PROCESO';
    		//cuando la boleta de electronica sea emitida se actualizara este campo tambien.
    	} 

		if($this->model_atencion_medica->m_registrar_atencion_medica($allInputs)){ // REGISTRAR CABECERA 
			$allInputs['idatencionmedica'] = GetLastId('idatencionmedica','atencion_medica'); 
			$arrData['flag'] = 1; 
			foreach ($allInputs['fInputs']['gridDiagnostico'] as $key => $row) { 
				$row['idatencionmedica'] = $allInputs['idatencionmedica']; 
				if( $this->model_atencion_medica->m_registrar_atencion_diagnostico($row,$allInputs['idatencionmedica']) ) { 
					$arrData['message'] = 'Se registraron los datos correctamente'; 
	    			$arrData['flag'] = 1; 
				}else{ 
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
    				$arrData['flag'] = 0; 
				} 
			} 
			// IMPORTANTE: ACTUALIZAMOS EL CAMPO "paciente_atendido_v"  DE LA TABLA VENTA 
			if($arrData['flag'] === 1) {
				if($allInputs['idodontograma'] != 0){
					$this->model_atencion_medica->m_actualizar_odontograma($allInputs['idodontograma'], $allInputs['idatencionmedica']);
				}
				
				if($allInputs['origen_venta'] == 'W'){
		    		$this->model_venta_web->m_actualizar_venta_web_a_atendido($allInputs['id']); 
					$this->model_venta_web->m_actualizar_detalle_venta_web_a_atendido($allInputs['iddetalle']); 
					$this->model_venta_web->m_actualizar_empresa_especialidad_de_venta_web($allInputs['iddetalle']); /* IMPORTANTE */  
		    	}else{
		    		$this->model_venta->m_actualizar_venta_a_atendido($allInputs['id']); 
					$this->model_venta->m_actualizar_detalle_venta_a_atendido($allInputs['iddetalle']); 
					$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['id']); /* IMPORTANTE */ 
		    	}				
		    	if($tiene_programacion){
					$this->model_prog_cita->m_actualizar_cita_a_atendida_new($allInputs['idprogcita']); //CITA A ATENDIDA
		    	}

			} 
			// REGISTRAMOS EL HISTORICO DE EMBARAZO 
			if( $allInputs['fInputs']['gestando'] == 1 ){ // SI
				$this->model_atencion_medica->m_registrar_historico_embarazo($allInputs); 
			}
			$arrData['idatencionmedica'] = $allInputs['idatencionmedica'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_atencion_medica_ambulatoria()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// $arrData['idatencionmedica'] = NULL;
    	if( empty($allInputs['fInputs']['gridDiagnostico']) ){ 
    		$arrData['message'] = 'No se ha agregado ningún diagnóstico, a la atención médica.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	if( empty($allInputs['num_acto_medico']) ){ 
    		$arrData['message'] = 'No se pudo obtener el N° de Acto Médico. Vuelva a entrar al formulario.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_editar_atencion_medica($allInputs)){ // EDITAR CABECERA 
			foreach ($allInputs['fInputs']['gridDiagnostico'] as $key => $row) { 
				$idDiagnostico = $row['id'] ;
				// REGISTRO Y/O EDICION DE DIAGNOSTICOS 
				$fDiagnostico = $this->model_atencion_medica->m_cargar_este_diagnostico_de_atencion($idDiagnostico,$allInputs['num_acto_medico']); 
				//var_dump("<pre>",$fDiagnostico); exit(); 
				if( empty($fDiagnostico) ){ 
					// var_dump($fDiagnostico); //exit(); 
					if( $this->model_atencion_medica->m_registrar_atencion_diagnostico($row,$allInputs['num_acto_medico']) ) { 
						$arrData['message'] = 'Se grabaron los datos correctamente'; 
		    			$arrData['flag'] = 1; 
					}else{ 
						$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
	    				$arrData['flag'] = 0; 
					} 
				}else{ 
					if( $this->model_atencion_medica->m_editar_atencion_diagnostico($row,$allInputs['num_acto_medico']) ) { 
						$arrData['message'] = 'Se grabaron los datos correctamente'; 
		    			$arrData['flag'] = 1; 
					}else{ 
						$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente'; 
	    				$arrData['flag'] = 0; 
					} 
				}
				// LA ELIMINACION DE HACE POR  CADA CLICK EN EL BOTON ELIMINAR 
			}
			$arrData['idatencionmedica'] = $allInputs['num_acto_medico'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_atencion_procedimiento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente'; 
    	$arrData['flag'] = 0; 
    	$arrData['idatencionmedica'] = NULL; 
    	$fCountValidate = $this->model_atencion_medica->m_validar_iddetalle_ventas_atendidas_hoy($allInputs['iddetalle']); 
    	
    	// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 	
    	if( $this->model_venta->m_validar_venta_con_nota_credito_atencion($allInputs)){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas, no se puede aprobar.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	if( !empty($fCountValidate['contador']) || $fCountValidate['contador'] > 0 ){ 
    		$arrData['message'] = 'Ya ha registrado la Atencion Médica.';
    		$arrData['flag'] = 2;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	} 

    	// var_dump($allInputs['orden']); exit(); 
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_registrar_atencion_medica_procedimiento($allInputs)){ // REGISTRAR ATENCION PRCOEDIMIENTO   
			$arrData['flag'] = 1;
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$allInputs['idatencionmedica'] = GetLastId('idatencionmedica','atencion_medica'); 
			// IMPORTANTE: ACTUALIZAMOS EL CAMPO "paciente_atendido_v"  DE LA TABLA VENTA 
			if($arrData['flag'] === 1) { 
				$this->model_venta->m_actualizar_venta_a_atendido($allInputs['id']); // IDVENTA
				$this->model_venta->m_actualizar_detalle_venta_a_atendido($allInputs['iddetalle']); 
				$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['id']); /* IMPORTANTE IDVENTA */  
			} 
			$arrData['idatencionmedica'] = $allInputs['idatencionmedica'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_atencion_procedimiento() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL; 
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_editar_atencion_medica_procedimiento($allInputs)){ // EDITAR ATENCION PRCOEDIMIENTO 
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$arrData['flag'] = 1;
			$arrData['idatencionmedica'] = $allInputs['num_acto_medico'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_atencion_examen_auxiliar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL; 
    	$fCountValidate = $this->model_atencion_medica->m_validar_iddetalle_ventas_atendidas_hoy($allInputs['iddetalle']); 
    	if( !empty($fCountValidate['contador']) || $fCountValidate['contador'] > 0 ){ 
    		$arrData['message'] = 'Ya ha registrado la Atencion Médica.';
    		$arrData['flag'] = 2;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 	
    	if( $this->model_venta->m_validar_venta_con_nota_credito_atencion($allInputs)){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas, no se puede aprobar.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// var_dump($fValidateNC); exit(); 
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_registrar_atencion_medica_examen_auxiliar($allInputs)){ // REGISTRAR EXAMEN AUXILIAR 
			$arrData['flag'] = 1;
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$allInputs['idatencionmedica'] = GetLastId('idatencionmedica','atencion_medica'); 
			// IMPORTANTE: ACTUALIZAMOS EL CAMPO "paciente_atendido_v"  DE LA TABLA VENTA 
			if($arrData['flag'] === 1) { 
				$this->model_venta->m_actualizar_venta_a_atendido($allInputs['id']); // IDVENTA
				$this->model_venta->m_actualizar_detalle_venta_a_atendido($allInputs['iddetalle']); 
				$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['id']); /* IMPORTANTE IDVENTA */  
			} 
			$arrData['idatencionmedica'] = $allInputs['idatencionmedica'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_atencion_examen_auxiliar() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL;
    	//var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_editar_atencion_medica_examen_auxiliar($allInputs['datos'])){ // EDITAR EXAMEN AUXILIAR 
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$arrData['flag'] = 1;
			$arrData['idatencionmedica'] = $allInputs['datos']['num_acto_medico'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_atencion_documentos() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL; 
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_editar_atencion_documentos($allInputs)){ // EDITAR EXAMEN AUXILIAR 
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$arrData['flag'] = 1;
			$arrData['idatencionmedica'] = $allInputs['num_acto_medico'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function eliminar_diagnostico_atencion_medica()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'No se pudieron eliminar los datos'; 
    	$arrData['flag'] = 0;
		if( $this->model_atencion_medica->m_eliminar_atencion_diagnostico($allInputs['id'],$allInputs['actoMedico']) ){ 
			$arrData['message'] = 'Se eliminaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_dias()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$fecha_i=$allInputs['fecha_inicio'];
		$dias = $allInputs['dias'];
		$data = agregardiasfecha($fecha_i,$dias);
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($data));
	}
	public function registrar_atencion_documentos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['idatencionmedica'] = NULL; 
    	$fCountValidate = $this->model_atencion_medica->m_validar_iddetalle_ventas_atendidas_hoy($allInputs['iddetalle']); 
    	if( !empty($fCountValidate['contador']) || $fCountValidate['contador'] > 0 ){ 
    		$arrData['message'] = 'Ya ha registrado la Atencion Médica.';
    		$arrData['flag'] = 2;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 	
    	if( $this->model_venta->m_validar_venta_con_nota_credito_atencion($allInputs)){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas, no se puede aprobar.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
    	$this->db->trans_start();
		if($this->model_atencion_medica->m_registrar_atencion_documentos($allInputs)){ // REGISTRAR DOCUMENTOS 
			$arrData['flag'] = 1;
			$arrData['message'] = 'Se grabaron los datos correctamente'; 
			$allInputs['idatencionmedica'] = GetLastId('idatencionmedica','atencion_medica'); 
			// IMPORTANTE: ACTUALIZAMOS EL CAMPO "paciente_atendido_v"  DE LA TABLA VENTA 
			if($arrData['flag'] === 1) { 
				$this->model_venta->m_actualizar_venta_a_atendido($allInputs['id']); // IDVENTA
				$this->model_venta->m_actualizar_detalle_venta_a_atendido($allInputs['iddetalle']); 
				$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['id']); /* IMPORTANTE IDVENTA */
				if(isset($allInputs['idsolicitudcitt'])){
					$this->model_atencion_medica->m_actualizar_solicitudCitt($allInputs['idsolicitudcitt'],$allInputs['idatencionmedica']);
				}
				
			} 
			$arrData['idatencionmedica'] = $allInputs['idatencionmedica'];
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function imprimir_descanso_medico()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$arrData['message'] = 'Error';
    	$arrData['flag'] = 0;

    	$listaDetalle = $this->model_atencion_medica->m_cargar_descanso_medico_para_impresion($allInputs['num_acto_medico']);
		var_dump("<pre>",$listaDetalle); exit(); 
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/* ======================================= */ 
	/*               CONSULTAS                 */ 
	/* ======================================= */ 

	public function lista_resumen_atenciones()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$lista = $this->model_atencion_medica->m_cargar_resumen_atencion_medica($allInputs['datos']); 
		// var_dump($lista); exit(); 
		$arrListado = array();
		$sumCountCancelados = 0;
		$sumCountAtendidos = 0;
		$sumCountRestantes = 0;
		$sumSumIngresos = 0;
		foreach ($lista as $row) { 
			if( $this->sessionHospital['key_group'] === 'key_informes' ){
				$row['sum_ingresos_numeric'] = '-';
			}
			array_push($arrListado, 
				array( 
					'idespecialidad' => $row['idespecialidad'],
					'especialidad' => $row['nombre'],
					'countCancelados' => $row['count_cancelados'],
					'countAtendido' => $row['count_atendido'],
					'countRestante' => $row['count_restante'],
					'sumIngresos' => $row['sum_ingresos_numeric'] 
				)
			); 
			$sumCountCancelados += $row['count_cancelados'];
			$sumCountAtendidos += $row['count_atendido'];
			$sumCountRestantes += $row['count_restante'];
			$sumSumIngresos += $row['sum_ingresos_numeric'];
		}
		// var_dump($sumSumIngresos); exit(); 
		$arrData['countCancelados'] = $sumCountCancelados;
		$arrData['countAtendido'] = $sumCountAtendidos;
		$arrData['countRestante'] = $sumCountRestantes;
		if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
			$sumSumIngresos = '-';
		}
		if($sumSumIngresos !== '-'){ 
			$sumSumIngresos = number_format($sumSumIngresos,2);
		}
		$arrData['sumIngresos'] = $sumSumIngresos;

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
	public function ver_popup_detalle_atenciones(){
		$this->load->view('atencionMedica/detalle_atencion_formView');
	}
	public function lista_productos_por_especialidad()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$rango = @$allInputs['rango'];
		//var_dump($rango); exit();
		$lista = $this->model_atencion_medica->m_cargar_producto_por_especialidad($paramPaginate,$paramDatos,$rango);
		$totalRows = $this->model_atencion_medica->m_count_producto_por_especialidad($paramPaginate,$paramDatos,$rango);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['paciente_atendido_det'] == 1 ){ // PACIENTE ATENDIDO
				$estado = 'ATENDIDO';
				$clase = 'label-success';
			}
			if( $row['paciente_atendido_det'] == 2 ){
				$estado = 'NO ATENDIDO'; // PACIENTE SIN ATENDER
				$clase = 'label-default';
			}
			// Se verifica si el usuario logueado es de informes, no le muestra el precio
			if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
				$importe = '-';
			}else{
				$importe = $row['total_detalle'];
			}
			$importe = 
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'fecha_venta' => ($row['fecha_venta']),
					'idtipoproducto' => $row['idtipoproducto'],
					'tipoproducto' => $row['nombre_tp'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'], // EMPRESA ADMIN 
					'importe' => $importe,
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['paciente_atendido_det']
					)
					

				)
			);
		}
		if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
			$arrData['suma'] = '-';
		}else{
			$arrData['suma'] = $totalRows['sum_ventas_especialidad'];
		}
		
		
		//var_dump($arrData['suma']); exit();
    	$arrData['datos'] = $arrListado;
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
	public function lista_resumen_pacientes()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit();
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_atencion_medica->m_cargar_resumen_pacientes($paramPaginate,$paramDatos);
		$totalRows = $this->model_atencion_medica->m_count_resumen_pacientes($paramPaginate,$paramDatos);
		$totalVentas = $this->model_atencion_medica->m_count_resumen_ventas_pacientes($paramPaginate,$paramDatos);
		// var_dump($lista); exit(); 
		$arrListado = array();
		$sumCountCancelados = 0;
		$sumCountAtendidos = 0;
		$sumCountRestantes = 0;
		$sumSumIngresos = 0;
		foreach ($lista as $row) { 
			if( $this->sessionHospital['key_group'] === 'key_informes' ){
				$row['sum_ingresos_numeric'] = '-';
			}
			array_push($arrListado, 
				array( 
					'idcliente' => $row['idcliente'],
					'idhistoria' => $row['idhistoria'],
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'countCancelados' => $row['count_cancelados'],
					'countAtendido' => $row['count_atendido'],
					'countRestante' => $row['count_restante'],
					'sumIngresos' => $row['sum_ingresos_numeric'] 
				)
			); 
			$sumCountCancelados += $row['count_cancelados'];
			$sumCountAtendidos += $row['count_atendido'];
			$sumCountRestantes += $row['count_restante'];
			//$sumSumIngresos += $row['sum_ingresos_numeric'];
		}
		$sumCountCancelados = $totalVentas['count_cancelados'];
		$sumCountAtendidos = $totalVentas['count_atendido'];
		$sumCountRestantes = $totalVentas['count_restante'];
		$sumSumIngresos = $totalVentas['sum_ingresos_numeric'];
		// var_dump($sumSumIngresos); exit(); 
		$arrData['paginate']['totalRows'] = $totalRows;
		$arrData['countCancelados'] = $sumCountCancelados;
		$arrData['countAtendido'] = $sumCountAtendidos;
		$arrData['countRestante'] = $sumCountRestantes;
		if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
			$sumSumIngresos = '-';
		}
		if($sumSumIngresos !== '-'){ 
			$sumSumIngresos = number_format($sumSumIngresos,2);
		}
		$arrData['sumIngresos'] = $sumSumIngresos;

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
	public function lista_atenciones_por_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$rango = @$allInputs['rango'];
		//var_dump($rango); exit();
		$lista = $this->model_atencion_medica->m_cargar_producto_por_paciente($paramPaginate,$paramDatos,$rango); 
		$totalRows = $this->model_atencion_medica->m_count_producto_por_paciente($paramPaginate,$paramDatos,$rango);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['paciente_atendido_det'] == 1 ){ // PACIENTE ATENDIDO
				$estado = 'ATENDIDO';
				$clase = 'label-success';
			}
			if( $row['paciente_atendido_det'] == 2 ){
				$estado = 'NO ATENDIDO'; // PACIENTE SIN ATENDER
				$clase = 'label-default';
			}
			// Se verifica si el usuario logueado es de informes, no le muestra el precio
			if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
				$importe = '-';
			}else{
				$importe = $row['total_detalle'];
			}
			$importe = 
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'fecha_venta' => ($row['fecha_venta']),
					'idtipoproducto' => $row['idtipoproducto'],
					'tipoproducto' => $row['nombre_tp'],
					'idproductomaster' => $row['idproductomaster'],
					'producto' => $row['producto'], // EMPRESA ADMIN 
					'importe' => $importe,
					'medico' => $row['medico'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['paciente_atendido_det']
					)
					

				)
			);
		}
		if( $this->sessionHospital['key_group'] === 'key_informes' ){ 
			$arrData['suma'] = '-';
		}else{
			$arrData['suma'] = $totalRows['sum_ventas_especialidad'];
		}
		
		
		//var_dump($arrData['suma']); exit();
    	$arrData['datos'] = $arrListado;
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
	public function obtener_totales_produccion_terceros()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrListado = array();
		$arrData['datos'] = $arrListado;
	    $arrMainArray = array();
	    $fEmpresa = $this->model_empresa->m_cargar_esta_empresa_por_ruc($allInputs);
	    if( empty($fEmpresa) ){
	    	$arrData['message'] = 'No se encontró la empresa con el ruc Num. '.$allInputs['ruc']; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
	    }
	    $arrParams = array( 
	    	'idempresa'=> $fEmpresa['idempresa'],
	    	'idespecialidad'=> $allInputs['servicio']['id']
	    );
	    $fEmpresaEsp = $this->model_empresa->m_validar_empresa_especialidad($arrParams);
	    if( empty($fEmpresaEsp) ){ 
	    	$arrData['message'] = 'No se encontró la empresa-especialidad'; 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
	    }
		// var_dump($allInputs); exit(); 
		$arrFechas = get_fecha_inicio_y_fin($allInputs['anio'],$allInputs['mes']['id']);
		// var_dump($arrFechas); exit();
		$arrParams = array();
		$arrParams['desde'] = $arrFechas['inicio'];
		$arrParams['desdeHora'] = '00';
		$arrParams['desdeMinuto'] = '00';
		$arrParams['hasta'] = $arrFechas['fin'];
		$arrParams['hastaHora'] = '23';
		$arrParams['hastaMinuto'] = '59';
		$arrParams['empresaespecialidad']['id'] = $fEmpresaEsp['idempresaespecialidad'];
		$arrParams['idempresaadmin'] = $this->sessionHospital['idempresaadmin'];
		$lista = $this->model_atencion_medica->m_cargar_ventas_atendidas_para_terceros($arrParams); 
		$sumTotalProd = 0; 
		$sumTotalProd100 = 0; 
		// $arrVisas = array();
		foreach ($lista as $key => $row) { 
			$montoTotalizado = $row['total_detalle_str'];
          	if( $row['descripcion_med'] == 'VISA' ){ 
          		/*$arrVisas[] = */ $montoVISA = $montoTotalizado * (0.05); 
				// echo $montoVISA.'<br/>';
            	$montoTotalizado = ($montoTotalizado - $montoVISA); 

          	}
			if( $row['pertenece_tercero'] == 2 ){ 
				$sumTotalProd += $montoTotalizado;
			}
			if( $row['pertenece_tercero'] == 1 ){ 
				$sumTotalProd100 += $montoTotalizado;
			}
			
		}
		// var_dump($arrVisas); 
		// exit();
		// DEFINIMOS HAY PRODUCTOS TERCEROS 
		if( $sumTotalProd > 0 && $sumTotalProd100 > 0 && $fEmpresaEsp['productos_tercero'] == 1 ){ 
			$totalTercero = ($fEmpresaEsp['porcentaje'] / 100) * $sumTotalProd ; 
			$totalTercero100 = $sumTotalProd100; 
			$arrListado = array(
				array(
					'item'=> 1,
					'descripcion'=> 'PRODUCCIÓN AL '.$fEmpresaEsp['porcentaje'].'%',
					'importe'=> round($sumTotalProd,2),
					'importe_tercero'=> round($totalTercero,2)
				),
				array(
					'item'=> 1,
					'descripcion'=> 'PRODUCCIÓN AL 100%',
					'importe'=> round($sumTotalProd100,2),
					'importe_tercero'=> round($totalTercero100,2)
				)
			); 
			$arrData['datos'] = $arrListado;
			$arrData['message'] = 'Se encontró mas de un importe'; 
    		$arrData['flag'] = 2; 
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}
		
		$totalTercero = ($fEmpresaEsp['porcentaje'] / 100) * $sumTotalProd; 

    	$arrData['message'] = '';
    	$arrData['datos'] = array(
			array(
				'item'=> 1,
				'descripcion'=> 'PRODUCCIÓN AL '.$fEmpresaEsp['porcentaje'].'%',
				'importe'=> $sumTotalProd,
				'importe_tercero'=> round($totalTercero,2)
			)
		); 
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function imprimir_receta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// RECUPERACION DE DATOS
	    $receta = $this->model_receta_medica->m_cargar_receta_medica_para_imprimir($allInputs['id']); // id = idreceta
	    $detalle = $this->model_receta_medica->m_cargar_detalle_receta_medica_para_imprimir($allInputs['id']); // id = idreceta
	    $diagnosticos = $this->model_atencion_medica->m_cargar_diagnosticos_de_atencion($allInputs);
	    // var_dump($diagnosticos); exit();
	    $top_titulo = '50';
	    $left_titulo = '45';
	    $width_titulo = '1132';

	    $top_paciente = '110';
	    $left_paciente = '220';
	    $width_paciente = '688';
	    $top_edad = '110';
	    $left_edad = '970';
	    $width_edad = '190';
	    $top_dni = '148';
	    $left_dni = '82';
	    $width_dni = '305';
	    $top_especialidad = '148';
	    $left_especialidad = '518';
	    $width_especialidad = '285';

	    $top_fecha_dia = '710';
	    $left_fecha_dia = '250';
	    $width_fecha_dia = '60';
	    $top_fecha_mes = '710';
	    $left_fecha_mes = '318';
	    $width_fecha_mes = '60';
	    $top_fecha_anyo = '710';
	    $left_fecha_anyo = '385';
	    $width_fecha_anyo = '60';
		// $arrConfig['campo2']['x'];
	    $top_usuario = '810';
	    $left_usuario = '35';
	    $width_usuario = '405';
	    
    	$arrData['flag'] = 1;
    	$arrData['html'] = '';
    	$htmlData = '<style>.caja{position: absolute;}
    		.center{text-align:center;}
    		.left{text-align:left;}
    		.right{text-align:right;}
    		.pl{padding-left:5px;}
    		.fs{font-size:12px;}
    	</style>';
    	if(  $this->sessionHospital['id_empresa_admin'] == 38 ){
    		$htmlData .= '<div style="background-image: url(assets/img/plantilla_receta_lurin.jpg); min-height:847px; background-repeat:no-repeat">';	
    	}else{
    		$htmlData .= '<div style="background-image: url(assets/img/plantilla_receta_villa.jpg); min-height:847px; background-repeat:no-repeat">';	
    	}
    	
    	
    	$htmlData .= '<div class="caja center" style="top: '. $top_titulo . 'px; left: ' . $left_titulo. 'px;letter-spacing:1.5px; width:'. $width_titulo.'px;font-size:18px;font-weight:bold">';
    	$htmlData .= 'RECETA N° ' . $receta['idreceta'];
    	$htmlData .= '</div>';

    	$htmlData .= '<div class="caja center" style="top: '. $top_paciente . 'px; left: ' . $left_paciente. 'px;letter-spacing:0.5px; width:'. $width_paciente.'px;">';
    	$htmlData .= $receta['paciente'];
    	$htmlData .= '</div>';
    	$htmlData .= '<div class="caja center" style="top: '. $top_edad . 'px; left: ' . $left_edad. 'px;letter-spacing:0.5px; width:'. $width_edad.'px;">';
    	$htmlData .= devolverEdad($receta['fecha_nacimiento']) . ' años';
    	$htmlData .= '</div>';
    	$htmlData .= '<div class="caja center" style="top: '. $top_dni . 'px; left: ' . $left_dni. 'px;letter-spacing:2px; width:'. $width_dni.'px;">';
    	$htmlData .= $receta['num_documento'];
    	$htmlData .= '</div>';
    	$htmlData .= '<div class="caja center" style="top: '. $top_especialidad . 'px; left: ' . $left_especialidad. 'px;letter-spacing:1px; width:'. $width_especialidad.'px;">';
    	$htmlData .= $receta['especialidad'];
    	$htmlData .= '</div>';

    	$htmlData .= '<div class="caja center" style="top: '. $top_fecha_dia . 'px; left: ' . $left_fecha_dia. 'px;letter-spacing:2px; width:'. $width_fecha_dia.'px;">';
    	$htmlData .= date('d',strtotime($receta['fecha_receta']));
    	$htmlData .= '</div>';
    	$htmlData .= '<div class="caja center" style="top: '. $top_fecha_mes . 'px; left: ' . $left_fecha_mes. 'px;letter-spacing:2px; width:'. $width_fecha_mes.'px;">';
    	$htmlData .= date('F',strtotime($receta['fecha_receta']));
    	$htmlData .= '</div>';
    	$htmlData .= '<div class="caja center" style="top: '. $top_fecha_anyo . 'px; left: ' . $left_fecha_anyo. 'px;letter-spacing:2px; width:'. $width_fecha_anyo.'px;">';
    	$htmlData .= date('Y',strtotime($receta['fecha_receta']));
    	$htmlData .= '</div>';

    	$htmlData .= '<div class="caja left pl" style="top: '. $top_usuario . 'px; left: ' . $left_usuario. 'px;letter-spacing:1px; width:'. $width_usuario.'px;">';
    	$htmlData .= $this->sessionHospital['username'] . ' - ' . date('d/M/Y - H:i:s');
    	$htmlData .= '</div>';
    	
    	/* DIAGNOSTICOS */
    	$caja1 = array(
    		'0' => array( 
    			'top' => '220',
    			'left' => '40',
    			'width' => '415' ),
    		'1' => array( 
    			'top' => '220',
    			'left' => '690',
    			'width' => '415' ),
    		'2' => array( 
    			'top' => '260',
    			'left' => '40',
    			'width' => '415' ),
    		'3' => array(
    			'top' => '260',
    			'left' => '690',
    			'width' => '415' ),
    	);
    	$caja2 = array(
    		'0' => array( 
    			'top' => '220',
    			'left' => '470',
    			'width' => '162' ),
    		'1' => array( 
    			'top' => '220',
    			'left' => '1015',
    			'width' => '162' ),
    		'2' => array( 
    			'top' => '260',
    			'left' => '470',
    			'width' => '162' ),
    		'3' => array(
    			'top' => '260',
    			'left' => '1015',
    			'width' => '162' ),
    	);
    	$index = 0;
    	foreach ($diagnosticos as $row) {
    		$htmlData .= '<div class="caja left pl" style="top: ' . $caja1[$index]['top'] . 'px; left: ' . $caja1[$index]['left'] . 'px;letter-spacing:1px; width:'. $caja1[$index]['width'] .'px;">';
	    	$htmlData .= $row['descripcion_cie'];
	    	$htmlData .= '</div>';
	    	$htmlData .= '<div class="caja center pl" style="top: ' . $caja2[$index]['top'] . 'px; left: ' . $caja2[$index]['left'] . 'px;letter-spacing:1px; width:'. $caja2[$index]['width'] .'px;">';
	    	$htmlData .= $row['codigo_cie'];
	    	$htmlData .= '</div>';
	    	$index++;
    	}
    	/* DETALLE DE MEDICAMENTOS */
		$top_grilla = '385';
		$left_grilla = '40';
    	$height_row = '54'; // alto de las filas
    	$arrCol = array('35','405','110','110','112','385'); // ancho de las columnas

    	$item = 1;
    	foreach ($detalle as $row) {
    		$posY = $left_grilla;
    		$htmlData .= '<div class="caja center" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[0] .'px;">';
	    	$htmlData .= $item++;
	    	$htmlData .= '</div>';
	    	$posY = (int)$posY + (int)$arrCol[0];
	    	$htmlData .= '<div class="caja left pl" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[1] .'px;">';
	    	$htmlData .= $row['denominacion'];
	    	$htmlData .= '</div>';
	    	$posY = (int)$posY + (int)$arrCol[1];
	    	$htmlData .= '<div class="caja center" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[2] .'px;">';
	    	$htmlData .= $row['concentracion'];
	    	$htmlData .= '</div>';
	    	$posY = (int)$posY + (int)$arrCol[2];
	    	$htmlData .= '<div class="caja left pl fs" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[3] .'px;">';
	    	$htmlData .= $row['forma_farmaceutica'];
	    	$htmlData .= '</div>';
	    	$posY = (int)$posY + (int)$arrCol[3];
	    	$htmlData .= '<div class="caja center" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[4] .'px;">';
	    	$htmlData .= $row['cantidad'];
	    	$htmlData .= '</div>';
	    	$posY = (int)$posY + (int)$arrCol[4];
	    	$htmlData .= '<div class="caja left pl" style="top: ' . $top_grilla . 'px; left: ' . $posY . 'px;letter-spacing:1px; width:'. $arrCol[5] .'px;">';
	    	$htmlData .= $row['indicaciones'];
	    	$htmlData .= '</div>';

	    	$top_grilla = (int)$top_grilla + (int)$height_row;

    	}
    	if( empty($receta) ){ 
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}

		$htmlData .= '</div>';
		$arrData['html'] = $htmlData;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function subir_archivos_atencion_examen_auxiliar(){
		//$allInputs = json_decode(trim(->input->raw_input_stream),true);
		$allInputs['num_acto_medico'] = $this->input->post('num_acto_medico');
		$allInputs['titulo'] = $this->input->post('titulo');
		// $allInputs['archivos'] = $this->input->post('archivos');

		$arrData['message'] = '';
	    $arrData['flag'] = 0;
		if( empty($_FILES) ){
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}
    	$allInputs['extension'] = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    	$allInputs['nuevoNombreArchivo'] = 'ea_'.date('YmdHis').'.'.$allInputs['extension'];
		//var_dump($allInputs); exit();
    	if( subir_fichero('assets/img/dinamic/atencion_medica/examenes_auxiliares/','archivo',$allInputs['nuevoNombreArchivo']) ){ 
			//var_dump($_FILES['archivo']['error']); var_dump('hjs'); exit();
			//$allInputs['nombre_archivo'] = $_FILES['archivo']['name']; 
			$allInputs['nombre_archivo'] = $allInputs['nuevoNombreArchivo']; 
			if($this->model_atencion_medica->m_registrar_documento($allInputs)){ 
				$arrData['message'] = 'Se subió el archivo correctamente.'; 
				$arrData['flag'] = 1; 
			}
		}
    	
	    $this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	
	}
	public function lista_archivos_atencion_examen_auxiliar()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		//var_dump("<pre>",$paramDatos); exit(); 
		$lista = $this->model_atencion_medica->m_cargar_archivos_atencion_examen_auxiliar($paramPaginate,$paramDatos);
		$totalRows = $this->model_atencion_medica->m_count_archivos_atencion_examen_auxiliar($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			$extensionFile = strtolower(pathinfo($row['nombre_archivo'], PATHINFO_EXTENSION)); 
			if($extensionFile == 'doc' || $extensionFile == 'docx' ){
				$strIcono = 'word-icon.png'; 
			}elseif($extensionFile == 'xls' || $extensionFile == 'xlsx' ){
				$strIcono = 'excel-icon.png'; 
			}elseif($extensionFile == 'jpg' || $extensionFile == 'png' || $extensionFile == 'jpeg' ){
				$strIcono = 'imagen-icon.png'; 
			}elseif($extensionFile == 'pdf' ){
				$strIcono = 'pdf-icon.png'; 
			}else{
				$strIcono = 'other-icon.png'; 
			}
			array_push($arrListado, 
				array(
					'idatencionarchivo' => $row['idatencionarchivo'],
					'titulo' => $row['titulo'],
					'username' => $row['username'],
					'fecha_subida' => formatoFechaReporte3($row['fecha_subida']),
					'archivo' => array(
						'documento'=> $row['nombre_archivo'],
						'icono'=> $strIcono
					)
					//'estado' => $row['estado_de']
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
	public function ver_popup_subida_documentos_formulario()
	{
		$this->load->view('atencionMedica/documentoAtencionEA_formView');
	}
	public function anular_archivos_atencion_examen_auxiliar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_atencion_medica->m_anular_documento($row['idatencionarchivo']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ULTIMOS EXAMENES
	public function lista_ultimos_examenes_de_paciente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_atencion_medica->m_cargar_ultimos_examenes_paciente($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'acto_medico' => $row['idatencionmedica'],
					'producto' => strtoupper($row['descripcion']),
					'fecha' => formatoFechaReporte3($row['fecha_atencion'])
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

}