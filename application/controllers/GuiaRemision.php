<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GuiaRemision extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->helper(array('security'));
		$this->load->helper(array('otros_helper','fechas_helper','security'));
		$this->load->model(array('model_guia_remision','model_traslado_farmacia','model_caja','model_empresa_admin'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}
	public function listar_traslado_guia_limite()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['limite'] = $this->model_empresa_admin->m_cargar_limite_guia_remision($allInputs['almacenDestino']);
		$lista = $this->model_guia_remision->m_cargar_traslados_para_guia_limite($allInputs);
		$item = $this->model_guia_remision->m_cargar_correlativo($allInputs)+ 1;		
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'item' => $item,
					'codigo' => $row['idmedicamento'],
					'cantidad' => $row['cantidad'], 
					'caja_unidad' => $row['caja_unidad'], 
					'descripcion' => strtoupper($row['denominacion']),
					'en_guia_remision' => $row['en_guia_remision'],		
					'iddetallemovimiento' => $row['iddetallemovimiento'],		
					'nombre_lab' => $row['nombre_lab']		
				)
			);
			$item++;
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
	public function listar_detalle_guia()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_guia_remision->m_cargar_detalle_guia($allInputs);	
		$limite = $this->model_empresa_admin->m_cargar_limite_guia_remision($allInputs['almacenDestino']);	
		$num = $lista[0]['numero_guia'];
		$arrListado = array();
		$item = (($num * $limite) - $limite) + 1;
		/*var_dump($lista);
		var_dump($limite);
		var_dump($num);
		var_dump($item);*/
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'item' => $item,
					'codigo' => $row['idmedicamento'],
					'cantidad' => $row['cantidad'],  
					'descripcion' => strtoupper($row['denominacion']),
					'nombre_lab' => $row['nombre_lab']			
				)
			);
			$item++;
		}

		$items = $this->model_guia_remision->m_count_items_traslado($allInputs);
		$cantidad_guias = $items / $limite;
		if ($cantidad_guias > intval($cantidad_guias)) {
			$cantidad_guias = intval($cantidad_guias) + 1;
		}

    	$arrData['datos'] = $arrListado;
    	$arrData['guia'] = $num;
    	$arrData['cantidad_guias'] = $cantidad_guias;
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_numero_serie(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['idempresaadmin'] = $this->sessionHospital['idempresaadmin'];
		$lista = $this->model_guia_remision->m_cargar_numero_serie($allInputs);

		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id' => $row['serie_caja'],
					'descripcion' => $row['serie_caja'],
					'numero_serie' => str_pad($row['numero_serie'] +1 , 6, "0", STR_PAD_LEFT),
					'idcajamaster' => $row['idcajamaster']
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
	public function ver_popup_lista_guias_remision(){
		$this->load->view('traslado/listaGuiaRemision_view');
	}
	public function ver_popup_guia_remision()
	{
		$this->load->view('traslado/guiaRemision_formView');
	}
	public function lista_guias_remision() { 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$datos = $allInputs['datos'];
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_guia_remision->m_cargar_guias_remision($datos, $paramPaginate);
		$fContador = $this->model_guia_remision->m_count_guias_remision($datos, $paramPaginate);
		$arrListado = array(); 

		foreach ($lista as $row) { 

			if($row['estado_gr'] == 1){
				$estado = 'POR ENVIAR';
			}elseif($row['estado_gr'] == 2){
				$estado = 'ENVIADO';
			}else{
				$estado = 'ANULADO';
			}

			array_push($arrListado, 
				array( 
					'idguiaremision' => $row['idguiaremision'],
					'idmovimiento' => $row['idmovimiento'],
					'codigo' => $row['numero_serie'] .' - ' . $row['numero_correlativo'], 
					'numero_serie' => $row['numero_serie'], 
					'numero_correlativo' => $row['numero_correlativo'],
					'motivo_traslado' => $row['idmotivotraslado'],
					'marca_vehiculo' => strtoupper($row['marca_transporte']),
					'placa_vehiculo' => strtoupper($row['placa_transporte']),
					'constancia_inscripcion' => strtoupper($row['num_constancia_inscripcion']),
					'licencia_conducir' => strtoupper($row['num_licencia_conducir']),
					'razon_social_nombre' => strtoupper($row['nombres_razon_social']),
					'punto_partida' => strtoupper($row['punto_partida']),
					'punto_llegada' => strtoupper($row['punto_llegada']),
					'estado' => $row['estado_gr'],
					'estado_gr' => $estado,
					'fecha_guia' => darFormatoDMY($row['fecha_inicio_traslado']),
					'costo_minimo' => $row['costo_minimo'],
					'motivo_otros' => $row['motivo_otros']					
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $fContador['contador'];
    	$arrData['message'] = ''; 
    	$arrData['flag'] = 1; 
		if(empty($lista)){ 
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function generar_numero_serie(){
		$arrData = array();
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['idempresaadmin'] = $this->sessionHospital['idempresaadmin'];
		$fNumeroSerie = $this->model_guia_remision->m_cargar_numero_serie($allInputs);
		
		$arrData['numero_serie'] = str_pad(($fNumeroSerie[0]['numero_serie'] + 1), 6, '0', STR_PAD_LEFT);
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function consultar_guia_remision(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No existe una guía de remisión';
    	$arrData['flag'] = 0;

		$arrFilters = array( 
    		'searchColumn' => 'idmovimiento',
    		'searchText' => $allInputs['idmovimiento']
    	);
    	$fGuia = $this->model_guia_remision->m_cargar_esta_guia($arrFilters);
    	if( !empty($fGuia) ){		
    		$arrData['message'] = 'Ya existe una guía de remisión';
    		$arrData['flag'] = 1;   		
    	}

    	$this->output
	    	->set_content_type('application/json')
	    	->set_output(json_encode($arrData));
	    return;
	}
	public function lista_items_detalle_traslados(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No existen items para una guía de remisión';
    	$arrData['flag'] = 0;

    	$fGuia = $this->model_guia_remision->m_cargar_items_detalle_traslados($allInputs);
    	if( !empty($fGuia) ){		
    		$arrData['message'] = 'Si existen items para una guía de remisión';
    		$arrData['flag'] = 1;   		
    	}

    	$this->output
	    	->set_content_type('application/json')
	    	->set_output(json_encode($arrData));
	    return;
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$list_detalle = $allInputs['detalle'];
    	

    	if($allInputs['motivo_traslado'] == 0){
    		$arrData['message'] = 'Debe seleccionar un movito de traslado ';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	foreach ($list_detalle as $key => $value) {
    		if($value['en_guia_remision'] == 1){
	    		$arrData['message'] = 'Exiten items que ya poseen guía de remisión ';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
			    return;
	    	}
    	}
    	$arrFilters = array( 
    		'numero_serie' => $allInputs['serie']['id'],
    		'numero_correlativo' => $allInputs['numero_serie']
    	);
    	$fGuia = $this->model_guia_remision->m_consultar_serie_guia($arrFilters);
    	if( !empty($fGuia) ){		
    		$arrData['message'] = 'Ya se a registrado una guía, usando el <strong> '.$allInputs['serie']['id'].' N° '.$allInputs['numero_serie'].'</strong>';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	$this->db->trans_start();
    	$allInputs['idusers'] = $this->sessionHospital['idusers'];
		if($this->model_guia_remision->m_registrar($allInputs)){ 
			// Actualizar numero de serie
			$datos = array( 
				'numeroserie' => (int)$allInputs['numero_serie'],
				'idcajamaster' => (int)$allInputs['idcajamaster'],
				'idtipodocumento' => (int)$allInputs['idtipodocumento']
				);
			$this->model_caja->m_editar_numero_serie($datos);
			
			//Registrar Detalle
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idguiaremision'] = GetLastId('idguiaremision','guia_remision');
				if($this->model_guia_remision->m_registrar_detalle($row)){
					$row['en_guia_remision'] = 1;
					$this->model_traslado_farmacia->m_actualizar_movimiento_en_guia_remision($row);							
				}
			}
			$arrData['message'] = 'Se registraron los datos correctamente';
	    	$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$arrData['message'] = 'Error al actualizar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	// VALIDAR SI EL MOVIMIENTO YA ESTA ANULADO
    	$estado_guia = $this->model_guia_remision->m_verificar_estado_guia($allInputs['idguiaremision']);
    	if ($estado_guia['estado_gr'] == 2) {
    		$arrData['message'] = 'La guía ya fue enviada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	
    	if($allInputs['motivo_traslado'] == 0){
    		$arrData['message'] = 'Debe seleccionar un movito de traslado ';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$this->db->trans_start();
		if($this->model_guia_remision->m_editar($allInputs)){ 
			$arrData['message'] = 'Se actualizaron los datos correctamente';
	    	$arrData['flag'] = 1;			
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular() {
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ocurrió un error. Inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	$almacenDestino = $allInputs['almacenDestino'];
    	$mySelection = $allInputs['mySelectionGridGR'];

    	// VALIDAR SI EL MOVIMIENTO YA ESTA ANULADO
    	$estado_guia = $this->model_guia_remision->m_verificar_estado_guia($mySelection['idguiaremision']);
    	if( $estado_guia['estado_gr'] == 0){
    		$arrData['message'] = 'La guía ya está anulada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}elseif ($estado_guia['estado_gr'] == 2) {
    		$arrData['message'] = 'La guía ya fue enviada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$this->db->trans_start();
    	// ACTUALIZAR EN TABLA GUIA_RESMISION

    	if($this->model_guia_remision->m_anular($mySelection['idguiaremision'])){
    		// Actualizar  numero_guia de las otras guias
    		$guias = $this->model_guia_remision->m_cargar_guias_mayores($mySelection['idguiaremision']);
    		foreach ($guias as $key => $value) { 
    			$value['numero_guia'] =  $value['numero_guia'] - 1; 			
    			$this->model_guia_remision->m_actualizar_numero_guia($value);
    		}
	    	// ACTUALIZAR EN TABLA GUIA_RESMISION_DETALLE
	    	$mySelection['limite'] = $this->model_empresa_admin->m_cargar_limite_guia_remision($almacenDestino);
	    	$listaDetalle = $this->model_guia_remision->m_cargar_items_detalle_guia($mySelection['idguiaremision']);
	    	foreach ($listaDetalle as $key => $row) {	
	    		$row['idmovimiento'] = $mySelection['idmovimiento'];    		
	    		$this->model_guia_remision->m_anular_detalle_guia($row['idguiaremisiondetalle']);
	    		$item = $this->model_guia_remision->m_cargar_detalle_traslado_liberar($row);
	    		// ACTUALIZAR EN TABLA FAR_DETALLE_MOVIMIENTO
	    		$item['en_guia_remision'] = 2;
				$this->model_traslado_farmacia->m_actualizar_movimiento_en_guia_remision($item);
	    	}

	    	$arrData['message'] = 'La guía se anuló correctamente';
    		$arrData['flag'] = 1;
    	}

    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function imprimir_guia_remision(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 1;
		$arrData['html'] = '';    	
		
  		/* ESTILOS */
	    	//$htmlData = '<link rel="stylesheet" type="text/css" href="assets/css/print.css" />';
	    	$htmlData = '<style type="text/css">
	    		@media print{ 
	    			@page{  
	    				size: A4 portrait;
					}
	    			@page :bottom { margin: 0cm; }

	    			.general{
	    				letter-spacing:5px;
					   	width: 1270px;
					   	height: 1000px;
					   	font-size:10px; 
					   	font-family: sans-serif;
	    			}
				}
				/** { outline: 2px dotted red }
				* * { outline: 2px dotted green }
				* * * { outline: 2px dotted orange }
				* * * * { outline: 2px dotted blue }
				* * * * * { outline: 1px solid red }
				* * * * * * { outline: 1px solid green }
				* * * * * * * { outline: 1px solid orange }
				* * * * * * * * { outline: 1px solid blue }*/
				.general{
    				letter-spacing:4px;
				   	width: 1270px;
				   	height: 1000px;
				   	font-size:10px; 
				   	font-family: sans-serif;
    			}
				body { margin: 0; padding: 0; }
				.row {	width: 100%; height: 25px; text-align:left; }
				.row-xs { width: 100%; height: 20px; text-align:left; }
				.rowdt { width: 100%; text-align:left; }
				.columna { float: left; display:inline-block; padding: 0 10px; }
				.panel{ width: 100%; padding: 0 20px; }
				.panel_detalle{ clear: left; width: 100%; margin-top: 220px; 
					padding-left: 45px;}
				.panel_mediano{ height: auto; float: left; padding: 0 20px; margin-top: 26px;}
				.guia{ margin-top: 158px; font-size:22px; text-align: right; 
					font-weight:bold; padding-right:68px; visibility: hidden;}
				.nombre_empr { width: 510px; padding-left:190px; }
				.direc_empr { width: 510px; padding-left:160px; }
				.ruc {width: 500px; padding-left:150px; margin-top: 2px; }
				.fecha { width: 150px; text-align: center; padding-left:25px; }
				.punto_partida { width: 380px; padding-left:200px; }
				.costo { width: 380px; padding-left:210px; margin-top: 2px; }
				.marca { width: 130px; padding-left: 260px; }
				.inscrip { width: 200px; padding-left: 300px; }
				.licencia { width: 186px; padding-left: 316px; }
				.empr { width: 210px; padding-left: 330px; margin-top: 22px; }
				.detalle{ display: inline-block; line-height: 22.5px;}
				.motivo{ width:5px; font-weight:bold; }
				.hidden{ visibility: hidden; }
	    	</style>';

    	$htmlData .= '<div class="general">';	
    	
    	
	    /* ENCABEZADO */
	    $htmlData .= '<div class="panel">';
	   		$htmlData .= '<div class="row">';
		    	$htmlData .= '<div class="guia">';
		    	$htmlData .= $allInputs['guia_remision'];
	    		$htmlData .= '</div>';
	    	$htmlData .= '</div>';
	    	$htmlData .= '<div class="row" style="margin-top: 44px;">';
		    	$htmlData .= '<div class="columna nombre_empr">';
		    	$htmlData .= $allInputs['destinatario']['razon_social'];
		    	$htmlData .= '</div>';

		    	$htmlData .= '<div class="columna fecha ">';
		    	$htmlData .= date('d',strtotime($allInputs['fecha_guia']));
		    	$htmlData .= '</div>';

		    	$htmlData .= '<div class="columna fecha">';
		    	$htmlData .= darFormatoMes($allInputs['fecha_guia']);
		    	$htmlData .= '</div>';

		    	$htmlData .= '<div class="columna fecha">';
		    	$htmlData .= date('Y',strtotime($allInputs['fecha_guia']));
		    	$htmlData .= '</div>';
			$htmlData .= '</div>';

			$htmlData .= '<div class="row">';
		    	$htmlData .= '<div class="columna direc_empr">';
		    	$htmlData .= $allInputs['destinatario']['domicilio'];
		    	$htmlData .= '</div>';

		    	$htmlData .= '<div class="columna punto_partida">';
		    	$htmlData .= strtoupper($allInputs['punto_partida']);
		    	$htmlData .= '</div>';
	    	$htmlData .= '</div>';

	    	$htmlData .= '<div class="row">';
		    	$htmlData .= '<div class="columna punto_partida" style="margin-left:680px;">';    	
		    	$htmlData .= strtoupper($allInputs['punto_llegada']);
		    	$htmlData .= '</div>';
			$htmlData .= '</div>';
			
	    	$htmlData .= '<div class="row">';
		    	$htmlData .= '<div class="columna ruc" >';
		    	$htmlData .= $allInputs['destinatario']['ruc'];
		    	$htmlData .= '</div>';

		    	$htmlData .= '<div class="columna costo" >';
		    	$htmlData .= !empty($allInputs['costo_minimo']) ? number_format($allInputs['costo_minimo'], 2, '.', ',') : '';
		    	$htmlData .= '</div>';
			$htmlData .= '</div>';
		$htmlData .= '</div>';

		$htmlData .= '<div class="panel">';
			$htmlData .= '<div class="panel_mediano" style="width: 680px;">';
			/* MOTIVO TRASLADO */

		    	$htmlData .= '<div class="row-xs" style="margin-top: 4px;"> ';
		    		if($allInputs['motivo_traslado'] == '1') {
			    		$htmlData .= '<div class="columna motivo" style="margin-left: 172px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 172px;">';}
			    			$htmlData .= 'X';			    	
			    		$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '6') {
				    	$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '11') {
				    	$htmlData .= '<div class="columna motivo" style="margin-left: 259px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-top: 2px; margin-left: 259px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';
			   $htmlData .= '</div>';
			    	
			   $htmlData .= '<div class="row-xs" > ';
		    		if($allInputs['motivo_traslado'] == '2') {
			    		$htmlData .= '<div class="columna motivo" style="margin-left: 172px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 172px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '7') {
				    	$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '12') {
				    	$htmlData .= '<div class="columna motivo" style="margin-top: 10px; margin-left: 259px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-top: 11px; margin-left: 259px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';
			   $htmlData .= '</div>';
		    	
			   $htmlData .= '<div class="row-xs" style="margin-top: 3px;"> ';
		    		if($allInputs['motivo_traslado'] == '3') {
		    			$htmlData .= '<div class="columna motivo" style="margin-left: 172px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 172px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '8') {
				    	$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';
		    		
			    	$htmlData .= '<div class="columna motivo" style="visibility: hidden; margin-top: 3px; margin-left: 259px;">';			    
			    		$htmlData .= 'X';
			    	$htmlData .= '</div>';
			   $htmlData .= '</div>';

			   $htmlData .= '<div class="row-xs" style="margin-top: 4px;"> ';
		    		if($allInputs['motivo_traslado'] == '4') {
			    		$htmlData .= '<div class="columna motivo" style="margin-left: 172px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 172px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '9') {
				    	$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		$htmlData .= '<div class="columna motivo" style="visibility: hidden; margin-top: 3px; margin-left: 259px;">';
			    		$htmlData .= 'X';
			    	$htmlData .= '</div>';
			   $htmlData .= '</div>';

				$htmlData .= '<div class="row-xs" style="margin-top: 4px;"> ';
		    		if($allInputs['motivo_traslado'] == '5') {
			    		$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

		    		if($allInputs['motivo_traslado'] == '10') {
			    		$htmlData .= '<div class="columna motivo" style="margin-left: 171px;">';
			    	}else{ $htmlData .= '<div class="columna motivo hidden" style="margin-left: 171px;">';}
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';

				    	$htmlData .= '<div class="columna motivo" style="visibility: hidden; margin-top: 5px; margin-left: 259px;">';
				    		$htmlData .= 'X';
				    	$htmlData .= '</div>';
			   $htmlData .= '</div>';

			   	if($allInputs['motivo_traslado'] == '13') {
		    		$htmlData .= '<div class="columna motivo" style="position: relative; bottom: 43px; left: 496px; width: 185px;">';
		    		$htmlData .= strtoupper($allInputs['motivo_otros']);
		    		$htmlData .= '</div>';
		    	}


			$htmlData .= '</div>';
				
		    $htmlData .= '<div class="panel_mediano" style="width: 510px;">';

		    	$htmlData .= '<div class="row-xs">';
			    	$htmlData .= '<div class="columna marca" >';
			    	$htmlData .= !empty($allInputs['marca_vehiculo']) ? strtoupper($allInputs['marca_vehiculo']) : '';
			    	$htmlData .= '</div>';

			    	$htmlData .= '<div class="columna" style="width: 90px;">';
			    	$htmlData .= !empty($allInputs['placa_vehiculo']) ? strtoupper($allInputs['placa_vehiculo']) : '';
			    	$htmlData .= '</div>';
				$htmlData .= '</div>';

		    	$htmlData .= '<div class="row-xs">';
			    	$htmlData .= '<div class="columna inscrip" >';
			    	$htmlData .=  !empty($allInputs['constancia_inscripcion']) ? strtoupper($allInputs['constancia_inscripcion']) : '';
			    	$htmlData .= '</div>';
				$htmlData .= '</div>';
		    	$htmlData .= '<div class="row-xs">';
			    	$htmlData .= '<div class="columna licencia" >';
			    	$htmlData .= !empty($allInputs['licencia_conducir']) ? strtoupper($allInputs['licencia_conducir']) : '';
			    	$htmlData .= '</div>';
				$htmlData .= '</div>';

		    	$htmlData .= '<div class="row-xs">';
			    	$htmlData .= '<div class="columna empr" >';
			    	$htmlData .= !empty($allInputs['razon_social_nombre']) ? strtoupper($allInputs['razon_social_nombre']) : '';
			    	$htmlData .= '</div>';
				$htmlData .= '</div>';

			$htmlData .= '</div>';
		$htmlData .= '</div>';
    	/* DETALLE DE MEDICAMENTOS */
    	$arrCol = array('85','130','150','845'); // ancho de las columnas
    	$htmlData .= '<div class="panel_detalle" >';
    	$i=1;
	    	foreach ($allInputs['detalle'] as $row) {
	    		
	    		$htmlData .= '<div class="rowdt">';
	    		    		
		    		$htmlData .= '<div class="detalle" style="width:'. $arrCol[0] .'px; text-align:center">';
			    	$htmlData .= $row['item'];
			    	$htmlData .= '</div>';
			    	
			    	$htmlData .= '<div class="detalle" style="width:'. $arrCol[1] .'px; padding-left: 30px;">';
			    	$htmlData .= $row['cantidad'];
			    	$htmlData .= '</div>';
			    	
			    	$htmlData .= '<div class="detalle" style="width:'. $arrCol[2] .'px; text-align:center">';
			    	$htmlData .= $row['codigo'];
			    	$htmlData .= '</div>';
			    	
			    	$htmlData .= '<div class="detalle" style="padding-left: 30px; width:'. $arrCol[3] .'px; text-align:left;">';
			    	if( strlen($row['descripcion']) >= 60){
			          	$descripcion = substr($row['descripcion'], 0,60) . '...';
			        }else{
			          	$descripcion = $row['descripcion'];
			        }
			    	$htmlData .= $descripcion;
			    	$htmlData .= '</div>';
		    	$htmlData .= '</div>';
		    	$i++;
	    	}
	    $htmlData .= '</div>';

		$htmlData .= '</div>';
		$arrData['html'] = $htmlData;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function cantidad_items_guias(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$limite = $this->model_empresa_admin->m_cargar_limite_guia_remision($allInputs['almacenDestino']);
		$items = $this->model_guia_remision->m_count_items_traslado($allInputs);

		$cantidad_guias = $items / $limite;
		if ($cantidad_guias > intval($cantidad_guias)) {
			$cantidad_guias = intval($cantidad_guias) + 1;
		}

		$arrData['guia'] = $this->model_guia_remision->m_count_guias($allInputs) + 1;
		$arrData['cantidad_guias'] = $cantidad_guias;
		$arrData['items'] = $items;

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}