<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionMedicaAnterior extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_atencion_medica_anterior'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function lista_pacientes(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(!isset($allInputs['ApellidoPaterno'])) $allInputs['ApellidoPaterno'] = NULL;
		if(!isset($allInputs['ApellidoMaterno'])) $allInputs['ApellidoMaterno'] = NULL;
		if(!isset($allInputs['Nombres'])) $allInputs['Nombres'] = NULL;

		$lista = $this->model_atencion_medica_anterior->m_cargar_pacientes($allInputs);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			if($row['sexo'] == '01' || strtoupper($row['sexo']) == 'M'){
				$sexo = 'Masculino'; $boolSexo = 'M';
			}elseif($row['sexo'] == '02' || strtoupper($row['sexo']) == 'F'){
				$sexo = 'Femenino'; $boolSexo = 'F';
			}else{
				$sexo = 'No especifica'; $boolSexo = null;
			}
			array_push($arrListado, array(
				'idcliente' => $row['idcliente'],
				'apellido_paterno' => $row['apellido_paterno'],
				'apellido_materno' => $row['apellido_materno'],
				'nombres' => $row['nombres'],
				// 'apellido_paterno' => iconv("windows-1252", "utf-8", $row['apellido_paterno']),
				// 'apellido_materno' => iconv("windows-1252", "utf-8", $row['apellido_materno']),
				// 'nombres' => iconv("windows-1252", "utf-8", $row['nombres']),
				'idhistoria' => $row['idhistoria'],
				'num_documento' => $row['num_documento'],
				'sexo' => $sexo,
				'boolSexo' => $boolSexo,
				'edadActual' => devolverEdadDetalle($row['fecha_nacimiento']),
				'fecha_nacimiento' => $row['fecha_nacimiento']
				)
			);
		}

		$arrData['datos'] = $arrListado;
		//var_dump($arrListado); exit();
    	//$arrData['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));

	}
	public function lista_detalle_venta_paciente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$lista = $this->model_atencion_medica_anterior->m_cargar_atenciones($allInputs);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			if($row['idtipoproducto'] === '0001'){
				$row['idtipoproducto'] = 12;
			}elseif($row['idtipoproducto'] === '0002'){
				$row['idtipoproducto'] = 13;
			}elseif($row['idtipoproducto'] === '0006'){
				$row['idtipoproducto'] = 14;
			}elseif($row['idtipoproducto'] === '0007'){
				$row['idtipoproducto'] = 15;
			}elseif($row['idtipoproducto'] === '0008'){
				$row['idtipoproducto'] = 11;
			}elseif($row['idtipoproducto'] === '0011'){
				$row['idtipoproducto'] = 16;
			}elseif($row['idtipoproducto'] === '0012'){
				$row['idtipoproducto'] = 17;
			}
			if($row['gestando'] == '1'){
				$gestando = 1;
			}else{
				$gestando = 2;
			}
			if($row['atencion_control'] == '1'){
				$atencion_control = 1;
			}else{
				$atencion_control = 2;
			}

			;
			if( strpos($row['presion_arterial'], '/') !== FALSE ){
				$presiones = explode("/", $row['presion_arterial']);
				$row['presion_arterial_mm'] = $presiones[0];
				$row['presion_arterial_hg'] = $presiones[1];
			}else{
				$row['presion_arterial_mm'] = null;
				$row['presion_arterial_hg'] = null;	
			}
			
			array_push($arrListado, array(
				'orden' => $row['orden_venta'],
				'producto' => $row['producto'],
				// 'producto' => iconv("windows-1252", "utf-8", $row['producto']),
				'idtipoproducto' => $row['idtipoproducto'],
				'tipoproducto' => $row['tipoproducto'],
				'especialidad' => $row['especialidad'],
				'area_hospitalaria' => 'CONSULTA EXTERNA',
				'num_acto_medico' => $row['idatencionmedica'],
				'fechaAtencion' => $row['fecha_atencion'] == ''? null:formatoConDiaHora($row['fecha_atencion']),
				'edadEnAtencion' => $allInputs[0]['fecha_nacimiento'] == ''? null:devolverEdadAtencion($allInputs[0]['fecha_nacimiento'],$row['fecha_atencion']),
				//'fechaAtencion' => $row['fecha_atencion'],
				'anamnesis' => $row['anamnesis'],
				// 'anamnesis' => iconv("windows-1252", "utf-8", $row['anamnesis']),
				'gestando' => $gestando,
				'presion_arterial_mm' => $row['presion_arterial_mm'],
				'presion_arterial_hg' => $row['presion_arterial_hg'],
				'frecuencia_cardiaca_lxm' => $row['frec_cardiaca'],
				'temperatura_corporal' => $row['temperatura'],
				'frecuencia_respiratoria' => $row['frec_respiratoria'],
				'peso' => $row['peso'],
				'talla' => $row['talla'],
				'imc' => $row['imc'],
				'perimetro_abdominal' => $row['perimetro_abdominal'],
				// 'examen_clinico' => iconv("windows-1252", "utf-8", $row['examen_clinico']),
				// 'observaciones' => iconv("windows-1252", "utf-8", $row['observaciones']),
				// 'proc_observacion' => iconv("windows-1252", "utf-8", $row['anamnesis']),
				// 'proc_informe' => iconv("windows-1252", "utf-8", $row['observaciones']),
				// 'ex_informe' => iconv("windows-1252", "utf-8", $row['observaciones']),
				'examen_clinico' => $row['examen_clinico'],
				'observaciones' => $row['observaciones'],
				'proc_observacion' => $row['anamnesis'],
				'proc_informe' => $row['observaciones'],
				'ex_informe' => $row['observaciones'],
				'atencion_control' => $atencion_control,
				'personalatencion' => array(
					'id' => $row['idmedico'],
					'descripcion' => $row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ' ' . $row['med_nombres']
					),
				'diagnosticos' => array(trim($row['ciex']),trim($row['ciex1']),trim($row['ciex2']),trim($row['ciex3']))
				)
			);
		}
		//var_dump($presiones); exit();
		$arrData['datos'] = $arrListado;
		//var_dump($arrListado); exit();
    	//$arrData['totalRows'] = $totalRows;
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
		
		$lista = $this->model_atencion_medica_anterior->m_cargar_diagnosticos_de_atencion($allInputs); 
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => strtoupper($row['codigo_cie']),
					'codigo_diagnostico' => strtoupper($row['codigo_cie']),
					'diagnostico' => strtoupper($row['descripcion_cie']),
					// 'diagnostico' => strtoupper(iconv("windows-1252", "utf-8", $row['descripcion_cie'])),
					'tipo' => 'PRESUNTIVO'
				)
			);
		}
		//var_dump($arrListado); exit();
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
	public function ver_popup_pdf()
	{

		$this->load->view('reportes/pdfview');

	}	
}