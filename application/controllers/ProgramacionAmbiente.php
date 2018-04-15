<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProgramacionAmbiente extends CI_Controller {

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security','fechas_helper'));
		$this->load->model(array('model_programacion_ambiente','model_ambiente'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function lista_horas_dia_ambiente()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramDatos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$paramDatos['fecha_evento'] = date('Y-m-d', strtotime($paramDatos['activeDateEdit']));
		$totalRows = 0;
		$lista = $this->model_programacion_ambiente->m_cargar_horas_dia_ambiente($paramDatos,$paramPaginate);
		$totalRows = $this->model_programacion_ambiente->m_count_horas_dia_ambiente($paramDatos,$paramPaginate);
		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'comentario' => $row['comentario'],
					'hora_evento' => date('g:i a', strtotime($row['hora_evento'])),
					'idambientefecha' => $row['idambientefecha'],
					'idambiente' => $row['idambiente'],
					'fecha_evento' => $row['fecha_evento'],
					'estado_fecha' => $row['estado_fecha'],
					'ambiente' => $paramDatos['ambiente']['descripcion'],
				)
			);
		}
		// var_dump($arrListado); exit();
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay horas guardadas';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario(){
		$this->load->view('programacion-asistencial/programacion_ambiente_formView');
	}	

	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	foreach ($allInputs['arrFechas'] as $rowFecha) {
	    	foreach ($allInputs['horas1'] as $row) {
	    		$row['idambiente'] = $allInputs['ambiente']['id'];
	    		$row['fecha_evento'] = date('Y-m-d',$rowFecha/1000);
	    		$row['idresponsable'] = $this->sessionHospital['idempleado'];
	    		$row['comentario'] = empty($allInputs['comentario'])? NULL : $allInputs['comentario'];
		    	if( !($idambientefecha = $this->model_programacion_ambiente->m_verificar_si_existe_programacion($row)) ){
		    			if($this->model_programacion_ambiente->m_registrar($row)){
						$arrData['message'] = 'Se registraron los datos correctamente';
			    		$arrData['flag'] = 1;
					}else{
						$arrData['message'] = 'Error';
						$arrData['flag'] = 0;
						break;
					}
	    		}else{
	    			$arrData['message'] = 'Ya existe programación';
					$arrData['flag'] = 0;
					break;
	    		}
	    		
	    	}
    	}
    	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function agregar_horas_dia_ambiente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//var_dump($allInputs['activeDateEdit']);
    	$allInputs['fecha_evento'] = date('Y-m-d', strtotime($allInputs['activeDateEdit']));

    	foreach ($allInputs['horas1'] as $row) {
    		$row['idambiente'] = $allInputs['ambiente']['id'];
    		$row['fecha_evento'] = $allInputs['fecha_evento'];
    		$row['idresponsable'] = $this->sessionHospital['idempleado'];
    		$row['comentario'] = empty($allInputs['comentario'])? NULL : $allInputs['comentario'];
    		// verificar si ya existe el horario
    		if( $idambientefecha = $this->model_programacion_ambiente->m_verificar_si_existe_programacion($row) ){
    			$arrData['message'] = 'Ya existe programación';
				$arrData['flag'] = 0;
    		}else{
	    		if($this->model_programacion_ambiente->m_registrar($row)){
					$arrData['message'] = 'Se registraron los datos correctamente';
		    		$arrData['flag'] = 1;
				}else{
					$arrData['message'] = 'Ocurrió un error al registrar los datos';
					$arrData['flag'] = 0;
					break;
				}
    		}
    		
    	}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo actualizar los datos';
    	$arrData['flag'] = 0;

    	//VALIDAR FECHA PROGRAMADA 
		if(strtotime($allInputs['fecha_evento']) < strtotime(date("d-m-Y"))){
			$arrData['message'] = 'No se permite modificar programaciones de fechas pasadas.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		if( $this->model_programacion_ambiente->m_editar($allInputs) ){
			$arrData['message'] = 'Se actualizaron los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;

    	//VALIDAR FECHA PROGRAMADA 
		if(strtotime($allInputs['fecha_evento']) < strtotime(date("d-m-Y"))){
			$arrData['message'] = 'No se permite modificar programaciones de fechas pasadas.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		if( $this->model_programacion_ambiente->m_anular($allInputs) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	

	public function listar_plannig_dias(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$datetime1 = date_create($allInputs['fecha1']);
	    $datetime2 = date_create($allInputs['fecha2']);		    
	    $interval = date_diff($datetime1, $datetime2);
	    $totalDias = intval($interval->format('%R%a'));

	    if($totalDias > 30){
	    	$fecha2 = strtotime($allInputs['fecha1']. "+ 29 days" );
	    	$fechaDate = new DateTime();
			$fechaDate->setTimestamp($fecha2);
			$datetime2 = $fechaDate;
			$allInputs['fecha2'] = date_format($fechaDate,'Y-m-d');
	    }

	    $datetime1 = new DateTime($allInputs['horaInicio']);
		$datetime2 = new DateTime($allInputs['horaFin']);
		$interval = $datetime1->diff($datetime2);
		$totalHoras = $interval->format('%h');

		$lista = $this->model_programacion_ambiente->m_listar_plannig_dias($allInputs);	

		$arrFechas = get_rangofechas($allInputs['fecha1'], $allInputs['fecha2'], true);			
		$arrHeader = array();
		foreach ($arrFechas as $fecha) {
			$fechaTime = strtotime($fecha);	
			$fechaDateTime = new DateTime();
			$fechaDateTime->setTimestamp($fechaTime);

			array_push($arrHeader, 
					array(
						'dato' =>  date_format($fechaDateTime,'d-m-Y'),
						'class' => 'fecha-header',
						'fecha' => $fecha,
						'formatFecha'=> formatoConDiaYNombreDia($fecha), 
						'mesAbv'=> '('.date('M',strtotime($fecha)).')',
					)
				);
		}

		//print_r($arrFechas);
		//print_r($arrHeader);

		$listAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede( $allInputs['idsede'], null);
		//print_r($arrAmbientes);

		$arrListado = array();
		$arrGrid = array();
		$arrGridTotal = array();
		$arrAmbientes = array();
		$ambienteAnterior = '';
		$indFechas = 0;

		$countFechas = count($arrFechas);
		$countAmb = count($listAmbientes);

		$ind = 0;
		$i = 0;
		$j = 0;
		while($j < $countAmb){
			$i = 0;
			$tag = substr($listAmbientes[$j]['descripcion_cco'], 0,2);
			$arrItemAmb = array(
					'dato' => $listAmbientes[$j]['numero_ambiente'],
					'class' => 'nombre-amb',
					'idambiente' => $listAmbientes[$j]['idambiente'],
					'piso'=> $listAmbientes[$j]['piso'],
					'orden'=> $listAmbientes[$j]['orden_ambiente'],
					'tag' => $tag,
					'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
				);
			array_push($arrAmbientes, $arrItemAmb);

			while ($i < $countFechas) {
				$encontro = false;
				$idambiente = $listAmbientes[$j]['idambiente'];
				$tipo = '';

				if($ind < count($lista) && $lista[$ind]['fecha_evento']==$arrFechas[$i] && $lista[$ind]['idambiente'] == $listAmbientes[$j]['idambiente']){
					$encontro = true;
					$primera = '';
					$ultima = '';
					if($i == 0){
						$primera = ' first-cell';
					}

					if($i == $totalDias){
						$ultima = ' final-cell';
					}

					if($totalHoras == $lista[$ind]['total_horas']){
						$tipo = 'total';
					}else{
						$tipo = 'parcial';
					}

					array_push($arrGrid, 
						array(
							'dato' => $lista[$ind]['estado_fecha'],
							'class' => 'cell-amb ' . $primera . $ultima,
							'idambiente' => $lista[$ind]['idambiente'],
							'ambiente' => $lista[$ind]['ambiente'],
							'fecha' => $lista[$ind]['fecha_evento'],
							'fecha_formato' => $arrHeader[$i]['dato'],
							'total_horas' => $lista[$ind]['total_horas'],
							'tipo_evento' => $tipo,
							'categoria' => $listAmbientes[$j]['descripcion_cco'],
							'subcategoria' => $listAmbientes[$j]['descripcion_scco'],
						)
					);

					array_push($arrListado, 
						array(
							'estado_fecha' => $lista[$ind]['estado_fecha'],
							'idambiente' => $lista[$ind]['idambiente'],
							'ambiente' => $lista[$ind]['ambiente'],
							'fecha' => $lista[$ind]['fecha_evento'],
							'fecha_formato' => $arrHeader[$i]['dato'],
							'total_horas' => $lista[$ind]['total_horas'],
							'tipo_evento' => $tipo,
							'categoria' => $listAmbientes[$j]['descripcion_cco'],
							'subcategoria' => $listAmbientes[$j]['descripcion_scco'],
						)
					);
					$ind++;
				}
				
				if(!$encontro){
					array_push($arrGrid,
							array(
								'dato' => '',
								'class' => 'cell-vacia'
							)
						);
				}

				$i++;
			}

			array_push($arrGridTotal, $arrGrid);
			$arrGrid = array();
			$j++;
		}		
		
		$arrData['planning']['datos'] = $arrListado;
    	$arrData['planning']['header'] = $arrHeader;
    	$arrData['planning']['gridTotal'] = $arrGridTotal;
    	$arrData['planning']['ambientes'] = $arrAmbientes;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay registros almacenados';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_detalle_dias(){
		$this->load->view('programacion-asistencial/programacion_ambiente_detalleFormView');
	}

	public function listar_plannig_detalle_dias(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_programacion_ambiente->m_listar_detalle_plannig_dias($allInputs);
		$arrListado = array();
		$arrHeader = array();
		$arrGrid = array();

		$number = intval(explode(":",$allInputs['horaFin'])[0]);
		$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';

		$horas = get_rangohoras($allInputs['horaInicio'], $hora_fin);	

		foreach ($horas as $item => $hora) {
			array_push($arrHeader, 
				array(
					'hora' => $hora,
					'hora_formato' => darFormatoHora($hora),
					'class' => 'item-hora'
				)
			);			
		}

		$count = count($horas);
		$ind = 0;
		$i = 0;
		while ($i < $count) {
			$encontre = false;					

			if($ind < count($lista) && $lista[$ind]['hora_evento']==$arrHeader[$i]['hora']){
				$tooltip = false;
				if(!empty($lista[$ind]['comentario']) && $lista[$ind]['comentario'] != ''){
					$tooltip = true;
				}
		
				array_push($arrListado, 
					array(
						'idambiente' => $lista[$ind]['idambiente'],
						'fecha_evento' => $lista[$ind]['fecha_evento'],
						'estado' => $lista[$ind]['estado_fecha'],
						'hora' => $lista[$ind]['hora_evento'],
						'comentario' => $lista[$ind]['comentario'],
						'idresponsable' => $lista[$ind]['idresponsable'],
						'responsable' => $lista[$ind]['responsable'],
						'tooltip' => $tooltip
					)
				);

				array_push($arrGrid,
					array(
						'dato' => $lista[$ind]['estado_fecha'],
						'clase' => 'cell-amb',
						'comentario' => $lista[$ind]['comentario'],
						'idresponsable' => $lista[$ind]['idresponsable'],
						'responsable' => $lista[$ind]['responsable'],
						'tooltip' => $tooltip
					)
				);
				$encontre = true;
				$ind++;
			}
			
			if(!$encontre){
				array_push($arrGrid,
						array(
							'dato' => '',
							'clase' => 'cell-vacia'
						)
					);
			}			
			$i++;
		}				
						

		
		

		$arrData['planning_detalle']['datos'] = $arrListado;
    	$arrData['planning_detalle']['header'] = $arrHeader;
    	$arrData['planning_detalle']['gridTotal'] = $arrGrid;

    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay registros almacenados';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_plannig_horas(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$number = intval(explode(":",$allInputs['horaFin'])[0]);
		$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';
		$horas = get_rangohoras($allInputs['horaInicio'], $hora_fin);
		$arrHeader = array();

		foreach ($horas as $item => $hora) {
			array_push($arrHeader, 
				array(
					'hora' => $hora,
					'dato' => darFormatoHora($hora),
					'class' => 'hora-sidebar'
				)
			);			
		}

		$listAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede( $allInputs['idsede'],null);

		$lista = $this->model_programacion_ambiente->m_listar_plannig_horas($allInputs);	

		//print_r($lista);
		//print_r($arrHeader);
		//print_r($this->sessionHospital);

		$arrListado = array();
		$arrGrid = array();
		$arrGridTotal = array();
		$arrAmbientes = array();
		$ambienteAnterior = '';
		$indFechas = 0;

		$countHoras = count($horas);
		$countAmb = count($listAmbientes);

		$ind = 0;
		$i = 0;
		$j = 0;
		while($i < $countHoras){
			$j = 0;
			while ($j < $countAmb) {
				if($i == 0){
					$tag = substr($listAmbientes[$j]['descripcion_cco'], 0,2);
					$arrItemAmb = array(
							'dato' => $listAmbientes[$j]['numero_ambiente'],
							'class' => 'nombre-amb',
							'idambiente' => $listAmbientes[$j]['idambiente'],
							'piso'=> $listAmbientes[$j]['piso'],
							'orden'=> $listAmbientes[$j]['orden_ambiente'],
							'tag' => $tag,
							'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
						);
					array_push($arrAmbientes, $arrItemAmb);
				}				

				$encontro = false;
				$idambiente = $listAmbientes[$j]['idambiente'];
				$tipo = '';

				if($ind < count($lista) && $lista[$ind]['hora_evento']==$arrHeader[$i]['hora'] && $lista[$ind]['idambiente'] == $listAmbientes[$j]['idambiente']){
					$encontro = true;

					/*if($totalHoras == $lista[$ind]['total_horas']){
						$tipo = 'total';
					}else{
						$tipo = 'parcial';
					}*/

					$tooltip = false;
					if(!empty($lista[$ind]['comentario']) && $lista[$ind]['comentario'] != ''){
						$tooltip = true;
					}

					array_push($arrGrid, 
						array(
							'dato' => $lista[$ind]['estado_fecha'],
							'class' => 'cell-amb ',
							'idambiente' => $lista[$ind]['idambiente'],
							'ambiente' => $lista[$ind]['ambiente'],
							'fecha' => $lista[$ind]['fecha_evento'],
							'hora_formato' => $arrHeader[$i]['dato'],
							'hora_evento' => $lista[$ind]['hora_evento'],
							'tipo_evento' => $tipo,
							'idresponsable' => $lista[$ind]['idresponsable'],
							'responsable' => $lista[$ind]['responsable'],
							'comentario' => $lista[$ind]['comentario'],
							'tooltip' => $tooltip
						)
					);

					array_push($arrListado, 
						array(
							'estado_fecha' => $lista[$ind]['estado_fecha'],
							'idambiente' => $lista[$ind]['idambiente'],
							'ambiente' => $lista[$ind]['ambiente'],
							'fecha' => $lista[$ind]['fecha_evento'],
							'hora_formato' => $arrHeader[$i]['dato'],
							'hora_evento' => $lista[$ind]['hora_evento'],
							'tipo_evento' => $tipo,
							'idresponsable' => $lista[$ind]['idresponsable'],
							'responsable' => $lista[$ind]['responsable'],
							'comentario' => $lista[$ind]['comentario'],
							'tooltip' => $tooltip
						)
					);
					$ind++;
				}
				
				if(!$encontro){
					array_push($arrGrid,
							array(
								'dato' => '',
								'class' => 'cell-vacia'
							)
						);
				}

				$j++;
			}

			array_push($arrGridTotal, $arrGrid);
			$arrGrid = array();
			$i++;
		}	
		
		$arrData['planning']['datos'] = $arrListado;
    	$arrData['planning']['horas'] = $arrHeader;
    	$arrData['planning']['gridTotal'] = $arrGridTotal;
    	$arrData['planning']['ambientes'] = $arrAmbientes;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay registros almacenados';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function verificar_disponibilidad_ambiente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = '';
    	$arrData['flag'] = 1;

    	$lista = $this->model_programacion_ambiente->m_cargar_horas_dia_ambiente($allInputs,null);    	

    	if(!empty($lista)){	
    		$msj = 'El ambiente seleccionado no estará disponible el día: '. $allInputs['fecha_formato'] .' en los horarios: '; 
    		$totalHoras = count($lista);
    		if($totalHoras == 1){
    			$number = intval(explode(":",$lista[0]['hora_evento'])[0]);
    			$hora_fin = str_pad($number+1,2,"0",STR_PAD_LEFT) . ':00:00';
    			$msj .= date('g:i a', strtotime($lista[0]['hora_evento'])). '-'.date('g:i a', strtotime($hora_fin));
    		}else{
    			$anterior = intval(explode(":",$lista[0]['hora_evento'])[0]);
				$inicio = $anterior;
    			foreach ($lista as $key => $value) {    				
    				if($key != 0){
						$actual = intval(explode(":",$value['hora_evento'])[0]);			

						if($actual == $anterior+1){
							$anterior = $anterior + 1;
						}else{
							$formato_inicio = str_pad($inicio,2,"0",STR_PAD_LEFT) . ':00:00';
							$formato_fin = str_pad(($anterior+1),2,"0",STR_PAD_LEFT) . ':00:00';		

							$msj .= darFormatoHora($formato_inicio). '-'.  darFormatoHora($formato_fin) . ', ';	
							$anterior = $actual;
							$inicio = $anterior;
						}

						if($key == ($totalHoras-1)){
							$formato_inicio = str_pad($inicio,2,"0",STR_PAD_LEFT) . ':00:00';
							$formato_fin = str_pad(($anterior+1),2,"0",STR_PAD_LEFT) . ':00:00';
							$msj .= darFormatoHora($formato_inicio). '-'.  darFormatoHora($formato_fin);
						}
					}
    			}
    		}   		
    		
    		$arrData['message'] = $msj;
    		$arrData['flag'] = 0;
       	}
   	
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));

	}	
}