<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Odontograma extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper'));
		$this->load->model(array('model_odontograma'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_piezas_por_odontograma()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_odontograma->m_cargar_piezas_por_odontograma($paramPaginate);
		$totalRows = $this->model_odontograma->m_count_piezas_por_odontograma($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			
			array_push($arrListado, 
				array(
					'id' => $row['idpiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'idodontograma' => $row['idodontograma'],
					'idatencionmedica' => $row['idatencionmedica']
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
	public function lista_zonas_por_pieza_por_odontograma()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_odontograma->m_cargar_zonas_por_pieza_por_odontograma($paramPaginate);
		$totalRows = $this->model_odontograma->m_count_zonas_por_pieza_por_odontograma($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			
			array_push($arrListado, 
				array(
					'id' => $row['idpiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'idodontograma' => $row['idodontograma'],
					'idatencionmedica' => $row['idatencionmedica'],
					'zona_dental' => $row['zona_dental']
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

	public function lista_estado_por_zona_por_pieza_por_odontograma() // mostrar una grilla como resumen
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_odontograma->m_cargar_estado_por_zona_por_pieza_por_odontograma($paramPaginate);
		$totalRows = $this->model_odontograma->m_count_estado_por_zona_por_pieza_por_odontograma($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			
			array_push($arrListado, 
				array(
					'id' => $row['idpiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'idodontograma' => $row['idodontograma'],
					'idatencionmedica' => $row['idatencionmedica'],
					'zona_dental' => $row['zona_dental'],
					'estado_dental' => $row['estado_dental']
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
	public function lista_todas_las_piezas_de_odontograma()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_odontograma->m_cargar_todas_las_piezas_de_odontograma($paramPaginate);
		$totalRows = $this->model_odontograma->m_count_todas_las_piezas_de_odontograma($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			
			array_push($arrListado, 
				array(
					'id' => $row['idpiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'zona_dental' => $row['zona_dental']
					// 'estado_dental' => $row['estado_dental']
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
	/* ===================================================== */
	/* 					 ODONTOGRAMA 						 */
	/* ===================================================== */
	public function lista_piezas_con_zonas()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);

		/* buscar si la historia medica ya tiene asignado un odontograma, si lo tiene recuperar el ultimo */
		// var_dump($allInputs); exit();
		if($allInputs['tipo_odontograma'] == 'inicial'){
			$datos_odontograma = $this->model_odontograma->m_buscar_odontograma_inicial($allInputs);
		}else{
			$datos_odontograma = $this->model_odontograma->m_buscar_odontograma_procedimientos($allInputs);
			if($datos_odontograma == null){
				$datos_odontograma = $this->model_odontograma->m_buscar_odontograma_inicial($allInputs);
			}
		}
		// var_dump($datos_odontograma); exit();
		$cant_odonto = count($datos_odontograma);
		
		if($cant_odonto == 0){
			$lista = $this->model_odontograma->m_cargar_piezas_dentales_con_zonas();
		}else{
			foreach ($datos_odontograma as $data) {
				$idodontograma = $data['idodontograma'];
				$idhistoria = $data['idhistoria'];
				$idatencionmedica = $data['idatencionmedica'];
				$tipo_odontograma = $data['tipo'];
				$numodontograma = $data['numodontograma'];
				$fecha_creacion = date('d-m-Y',strtotime($data['createdAt']));
				$perdidaspermanentes = $data['perdidaspermanentes'];
				$cariespermanentes = $data['cariespermanentes'];
				$obturadaspermanentes = $data['obturadaspermanentes'];
				$perdidasdeciduas = $data['perdidasdeciduas'];
				$cariesdeciduas = $data['cariesdeciduas'];
				$obturadasdeciduas = $data['obturadasdeciduas'];
				$observaciones = $data['observaciones'];

			}
			$lista = $this->model_odontograma->m_cargar_piezas_dentales_con_zonas();
			$lista2 = $this->model_odontograma->m_cargar_piezas_dentales_con_zonas_con_estados($idodontograma);
		}
		
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'cuadrante' => substr($row['idpiezadental'],0,1),
					'id' => $row['idpiezadental'],
					'idzona' => $row['idzonapiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'zona_dental' => $row['zona_dental']
				)
			);
		}
		if($cant_odonto == 0){ // SI ES EUN ODONTOGRAMA NUEVO
			$arrPrincipal = array(
				'idodontograma'=> null,
				'tipo_odontograma'=> null,
				'idhistoria'  => null,
				'idatencionmedica' => null,
				'numodontograma' => 0,
				'fecha_creacion' => date('d-m-Y'),
				'perdidaspermanentes' => 0,
				'cariespermanentes' => 0,
				'obturadaspermanentes' => 0,
				'perdidasdeciduas' => 0,
				'cariesdeciduas' => 0,
				'obturadasdeciduas' => 0,
				'observaciones' => null,
				'cuadrantes' => array()
			);
		}else{ // SI ES UN ODONTOGRAMA GUARDADO
			$arrPrincipal = array(
				'idodontograma'=> $idodontograma,
				'tipo_odontograma'=> $tipo_odontograma,
				'idhistoria'  => $idhistoria,
				'idatencionmedica' => $idatencionmedica,
				'numodontograma' => $numodontograma,
				'fecha_creacion' =>$fecha_creacion,
				'perdidaspermanentes' => $perdidaspermanentes,
				'cariespermanentes' => $cariespermanentes,
				'obturadaspermanentes' => $obturadaspermanentes,
				'perdidasdeciduas' => $perdidasdeciduas,
				'cariesdeciduas' => $cariesdeciduas,
				'obturadasdeciduas' => $obturadasdeciduas,
				'observaciones' => $observaciones,
				'cuadrantes' => array()
			);
		}
		// ARRAY PRINCIPAL

		$arrGroupPieza = array();
		$arrGroupZona = array();

		foreach ($arrListado as $key => $row) { 
			$otherRow = array(
				'cuadrante' => $row['cuadrante'],
				'descripcion' => 'cuadrante_'.$row['cuadrante'],
				'piezas' => array()
				
			);
			$arrPrincipal['cuadrantes'][$row['cuadrante']] = $otherRow;
		}
		// var_dump($arrPrincipal); exit();
		 $arrayPrueba = array(1,2,8,7,3,4,6,5);
		array_multisort($arrayPrueba, $arrPrincipal['cuadrantes']);
		
		foreach ($arrListado as $key => $row) { 
			$otherRow = array(
				'cuadrante' => $row['cuadrante'],
				'id' => $row['id'],
				'nombre' => $row['nombre'],
				'zonas' => array()
			);
			$arrGroupPieza[$row['id']] = $otherRow;
		}
		/* MERGE ENTRE CUADRANTES Y PIEZAS */
		foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
			foreach ($arrGroupPieza as $key2 => $rowPieza) {
				if($rowCuadrante['cuadrante'] == 1 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 5 || $rowCuadrante['cuadrante'] == 8){
					// krsort($arrPrincipal['cuadrantes'][$key1]['piezas']);
						$strClasePieza = 'pull-right';
				}else{
					$strClasePieza = '';
				}
				if($rowPieza['cuadrante'] == $rowCuadrante['cuadrante']) {
					$arrPrincipal['cuadrantes'][$key1]['piezas'][$rowPieza['id']] = array(
						'id' => $rowPieza['id'],
						'nombre' => $rowPieza['nombre'],
						'marca' => 0,
						'clase_pieza' => $strClasePieza

					);
				}
				
			}
		}
		// var_dump($arrPrincipal); exit();
		/* MERGE ENTRE CUADRANTES/PIEZAS Y ZONAS */
		foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
			foreach ($rowCuadrante['piezas'] as $key2 => $rowPieza) { 
				$arrTemp=array();
				foreach ($lista as $key3 => $rowAll) {
					if( $rowCuadrante['cuadrante'] == substr($rowAll['idpiezadental'],0,1) && $rowPieza['id'] == $rowAll['idpiezadental'] ) { 
						// ZONA 
						// $rowAll['idzona'];
						if(( $rowCuadrante['cuadrante'] == 1 || $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 5 || $rowCuadrante['cuadrante'] == 6) && $rowAll['idzonapiezadental'] == 2 ){
							$strClase = 'css-shapes-up';
						}elseif(( $rowCuadrante['cuadrante'] == 1 || $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 5 || $rowCuadrante['cuadrante'] == 6) && $rowAll['idzonapiezadental'] == 3){
							$strClase = 'css-shapes-bottom';
						}elseif(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 7 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 7){
							$strClase = 'css-shapes-up';
						}elseif(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 7 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 2) {
							$strClase = 'css-shapes-bottom';
						}

						if(( $rowCuadrante['cuadrante'] == 1 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 5 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 5 ){
							$strClase = 'css-shapes-left';
						}elseif(( $rowCuadrante['cuadrante'] == 1 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 5 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 4){
							$strClase = 'css-shapes-right';
						}elseif(( $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 6 || $rowCuadrante['cuadrante'] == 7 ) && $rowAll['idzonapiezadental'] == 4){
							$strClase = 'css-shapes-left';
						}elseif(( $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 6 || $rowCuadrante['cuadrante'] == 7 ) && $rowAll['idzonapiezadental'] == 5) {
							$strClase = 'css-shapes-right';
						}

						if($rowAll['idzonapiezadental'] == 1 || $rowAll['idzonapiezadental'] == 6){
							$strClase = 'css-shapes-middle';
						}

						if(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 7 || $rowCuadrante['cuadrante'] == 8 ) && ($rowAll['idzonapiezadental'] == 2 )){
							$strClase .= ' pos_bottom_3';
						}
						if(( $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 6 ) && $rowAll['idzonapiezadental'] == 5){
							$strClase .= ' pos_left_2';
						}
						if(( $rowCuadrante['cuadrante'] == 2 || $rowCuadrante['cuadrante'] == 6 ) && $rowAll['idzonapiezadental'] == 4){
							$strClase .= ' pos_right_2';
						}
						if(( $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 7){
							$strClase .= ' pos_up_3';
						}
						if(( $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 5){
							$strClase .= ' pos_left_3';
						}
						if(( $rowCuadrante['cuadrante'] == 4 || $rowCuadrante['cuadrante'] == 8 ) && $rowAll['idzonapiezadental'] == 4){
							$strClase .= ' pos_right_3';
						}
						if(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 7 ) && $rowAll['idzonapiezadental'] == 7){
							$strClase .= ' pos_up_4';
						}
						if(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 7 ) && $rowAll['idzonapiezadental'] == 5){
							$strClase .= ' pos_right_4';
						}
						if(( $rowCuadrante['cuadrante'] == 3 || $rowCuadrante['cuadrante'] == 7 ) && $rowAll['idzonapiezadental'] == 4){
							$strClase .= ' pos_left_4';
						}
						array_push($arrTemp,
							array(
								'idzona' =>	$rowAll['idzonapiezadental'],
								'zona' =>	$rowAll['zona_dental'],
								'clase' =>	$strClase,
								'estados' => array()
							)
						);
					}
				}
				/*
					css-shapes-left f
					css-shapes-up f
					css-shapes-middle f
					css-shapes-right f
					css-shapes-bottom f
				*/
				$arrPrincipal['cuadrantes'][$key1]['piezas'][$key2]['zonas'] = $arrTemp;
			}
		}
		if($cant_odonto > 0){
			/* MERGE ENTRE CUADRANTES/PIEZAS/ZONAS Y ESTADOS */
			foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
				foreach ($rowCuadrante['piezas'] as $key2 => $rowPieza) {
					foreach ($rowPieza['zonas'] as $key3 => $rowZona) { 
						$arrTemp=array();
						foreach ($lista2 as $key => $rowAll) {
							if( $rowPieza['id'] == $rowAll['idpiezadental'] && $rowZona['idzona'] == $rowAll['idzonapiezadental']) { 
								array_push($arrTemp,
									array(
										'id' => $rowAll['idestadopiezadental'],
										'descripcion' =>	 $rowAll['estado_dental'],
										'simbolo' => $rowAll['simbolo'],
										'imagen' => $rowAll['imagen'],
										'tipoestado' => $rowAll['tipoestado']
									)
								);
							}
						}
						$arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados'] = $arrTemp;
						if(count($arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados']) == 1){
							$arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['marca'] = 1;
						}else if(count($arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados']) == 2){
							$arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['marca'] = 2;
						}

						// $arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['marca'] = count($arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados']);
						
						// $arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados'] = $arrEstados;
					}
				}
			}
		}
		// var_dump("<pre>",$arrPrincipal); exit();
		$arrData['datos'] = $arrPrincipal;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		
	}
/* ============ =================== */
	public function lista_piezas_con_zonas_con_estados()
	{	
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_odontograma->m_cargar_piezas_dentales_con_zonas_con_estados($allInputs);

		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'idodontograma'=> $row['idodontograma'],
					'tipo_odontograma'=> $row['tipo_odontograma'],
					'cuadrante' => substr($row['idpiezadental'],0,1),
					'id' => $row['idpiezadental'],
					'idzona' => $row['idzonapiezadental'],
					'nombre' => strtoupper($row['pieza_dental']),
					'zona_dental' => $row['zona_dental'],
					'estado_dental' => $row['estado_dental']
				)
			);
		}
		$arrPrincipal = array(
			// 'idodontograma'=> null,
			// 'tipo_odontograma'=> null,
			'cuadrantes' => array()
		); // ARRAY PRINCIPAL
		$arrGroupPieza = array();
		$arrGroupZona = array();

		foreach ($arrListado as $key => $row) { 
			$otherRow = array(
				'cuadrante' => $row['cuadrante'],
				'descripcion' => 'cuadrante_'.$row['cuadrante'],
				'piezas' => array()
				
			);
			$arrPrincipal['cuadrantes'][$row['cuadrante']] = $otherRow;
		}
		foreach ($arrListado as $key => $row) { 
			$otherRow = array(
				'cuadrante' => $row['cuadrante'],
				'id' => $row['id'],
				'nombre' => $row['nombre'],
				'zonas' => array()
			);
			$arrGroupPieza[$row['id']] = $otherRow;
		}
		/* MERGE ENTRE CUADRANTES Y PIEZAS */
		foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
			foreach ($arrGroupPieza as $key2 => $rowPieza) {
				if($rowPieza['cuadrante'] == $rowCuadrante['cuadrante']) {
					$arrPrincipal['cuadrantes'][$key1]['piezas'][$rowPieza['id']] = array(
						'id' => $rowPieza['id'],
						'nombre' => $rowPieza['nombre']
					);
				}
			}
		}
		/* MERGE ENTRE CUADRANTES/PIEZAS Y ZONAS */
		foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
			foreach ($rowCuadrante['piezas'] as $key2 => $rowPieza) { 
				$arrTemp=array();
				foreach ($lista as $key3 => $rowAll) {
					if( $rowCuadrante['cuadrante'] == substr($rowAll['idpiezadental'],0,1) && $rowPieza['id'] == $rowAll['idpiezadental'] ) { 
						array_push($arrTemp,
							array(
								'idzona' =>	 $rowAll['idzonapiezadental'],
								'zona' =>	 $rowAll['zona_dental'],
								'estados' => array()
							)
						);
					}
				}
				$arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'] = $arrTemp;
			}
		}
		/* MERGE ENTRE CUADRANTES/PIEZAS/ZONAS Y ESTADOS */
		foreach ($arrPrincipal['cuadrantes'] as $key1 => $rowCuadrante) {
			foreach ($rowCuadrante['piezas'] as $key2 => $rowPieza) {
				foreach ($rowPieza['zonas'] as $key3 => $rowZona) { 
					$arrTemp=array();
					foreach ($lista as $key => $rowAll) {
						if( $rowPieza['id'] == $rowAll['idpiezadental'] && $rowZona['idzona'] == $rowAll['idzonapiezadental']) { 
							array_push($arrTemp,
								array(
									'idestadopiezadental' =>	 $rowAll['idestadopiezadental'],
									'estado' =>	 $rowAll['estado_dental'],
									'tipoestado' => $rowAll['tipoestado']
								)
							);
						}
					}
					$arrPrincipal['cuadrantes'][$key1 ]['piezas'][$key2]['zonas'][$key3]['estados'] = $arrTemp;
				}
			}
		}
		//var_dump("<pre>",$arrPrincipal); exit();
		$arrData['datos'] = $arrPrincipal;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		
	}
	//=================================================================
	//=================================================================
	public function ver_pieza_dental()
	{
		$this->load->view('odontograma/verPiezaDental_formView');
	}
	//=========================== BUSCA SI EXISTE UN ODONTOGRAMA CON LA HISTORIA MEDICA ID======================================
	public function buscar_odontograma_inicial(){
    	$arrData['flag'] = 0;
		$idhistoria = json_decode(trim($this->input->raw_input_stream),true);
		
		if($arrData['data'] = $this->model_odontograma->m_buscar_odontograma_inicial($idhistoria)){
		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	//=================================================================
	public function lista_estado_pieza_dental_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_odontograma->m_cargar_estado_pieza_dental_cbo($allInputs);
		}else{
			$lista = $this->model_odontograma->m_cargar_estado_pieza_dental_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idestadopiezadental'],
					'descripcion' => $row['descripcion_ep'],
					'simbolo' => $row['simbolo'],
					'tipo' => $row['tipo_marcacion'],
					'imagen' => $row['imagen'],
					'tipoestado' => $row['tipoestado']
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
	//=================================================================
	public function lista_procedimientos_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_odontograma->m_cargar_procedimientos_cbo($allInputs);
		}else{
			$lista = $this->model_odontograma->m_cargar_procedimientos_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idestadopiezadental'],
					'descripcion' => $row['descripcion_ep'],
					'simbolo' => $row['simbolo'],
					'tipo' => $row['tipo_marcacion'],
					'imagen' => $row['imagen'],
					'tipoestado' => $row['tipoestado']
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
	public function registrar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		$datos = array();
		$this->db->trans_start();
		if($this->model_odontograma->m_registrar($allInputs)) { // registro de un nuevo odontograma
			$datos['idodontograma'] = GetLastId('idodontograma','odontograma');
			foreach ($allInputs['cuadrantes'] as $key1 => $cuadrantes) {
				foreach ($cuadrantes['piezas'] as $key2 => $piezas) {
					foreach ($piezas['zonas'] as $key3 => $zonas) {
						foreach ($zonas['estados'] as $key4 => $estados) {
							$datos['idpiezadental'] = $piezas['id'];
							$datos['idzonapiezadental'] = $zonas['idzona'];
							$datos['idestadopiezadental'] = $estados['id'];
							$datos['tipoestado'] = $estados['tipoestado'];
							$this->model_odontograma->m_registrar_odontograma_estado($datos);
						}
					}
				}
			}

			
			$arrData['idodontograma'] = $datos['idodontograma'];
			$arrData['message'] = 'Se registraron los datos correctamente';
			$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		$datos = array();
		$this->db->trans_start();
		$datos['idodontograma'] = $allInputs['idodontograma'];
		$datos['cariespermanentes'] = $allInputs['cariespermanentes'];
		$datos['perdidaspermanentes'] = $allInputs['perdidaspermanentes'];
		$datos['obturadaspermanentes'] = $allInputs['obturadaspermanentes'];
		$datos['cariesdeciduas'] = $allInputs['cariesdeciduas'];
		$datos['perdidasdeciduas'] = $allInputs['perdidasdeciduas'];
		$datos['obturadasdeciduas'] = $allInputs['obturadasdeciduas'];
		$datos['observaciones'] = $allInputs['observaciones'];
		// var_dump($datos); exit();
		$this->model_odontograma->m_actualiza_odontograma($datos);
		$this->model_odontograma->m_editar_odontograma_estado($datos); // limpia todos los estados para volverlos a registrar
		
		foreach ($allInputs['cuadrantes'] as $key1 => $cuadrantes) {
			foreach ($cuadrantes['piezas'] as $key2 => $piezas) {
				foreach ($piezas['zonas'] as $key3 => $zonas) {
					foreach ($zonas['estados'] as $key4 => $estados) {
						$datos['idpiezadental'] = $piezas['id'];
						$datos['idzonapiezadental'] = $zonas['idzona'];
						$datos['idestadopiezadental'] = $estados['id'];
						$datos['tipoestado'] = $estados['tipoestado'];
						$this->model_odontograma->m_registrar_odontograma_estado($datos);
					}
				}
			}
		}

		
		$arrData['idodontograma'] = $datos['idodontograma'];
		$arrData['message'] = 'Se registraron los datos correctamente';
		$arrData['flag'] = 1;

		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}