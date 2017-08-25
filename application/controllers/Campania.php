<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campania extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','security','fechas_helper'));
		$this->load->model(array('model_campania','model_feriado'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
	}
	public function lista_campanias()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_campania->m_cargar_campanias($paramPaginate,$paramDatos);
		$totalRows = $this->model_campania->m_count_campanias($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idcampania'],
					'campania' => strtoupper($row['descripcion']),
					'especialidad'=>$row['nombre'],
					'idespecialidad' => $row['idespecialidad'],
					'fecha_inicio' => empty($row['fecha_inicio']) ? null : date('d-m-Y', strtotime($row['fecha_inicio'])) ,
					'fecha_final' => empty($row['fecha_final']) ?  null : date('d-m-Y',strtotime($row['fecha_final'])),
					//'desdeHora' => date('H', strtotime($row['fecha_inicio'])),
					//'desdeMinuto' => date('i', strtotime($row['fecha_inicio'])),
					//'hastaHora' => date('H', strtotime($row['fecha_final'])),
					//'hastaMinuto' => date('i', strtotime($row['fecha_final'])),
					'tipo' => ($row['tipo_campania'] == 1 ? 'CAMPAÑA':'CUPON'),
					'tipocampania' => (int)$row['tipo_campania'],
					'sedeempresa' => $row['ca_idsedeempresaadmin'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado']
					),
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
	public function lista_detalle_campanias()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		$lista = $this->model_campania->m_cargar_detalle_campanias($paramPaginate,$paramDatos);
		$totalRows = $this->model_campania->m_count_detalle_campanias($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array(
					'id' => $row['iddetallepaquete'],
					'campania' => strtoupper($row['campania']),
					'idcampania' =>$row['idcampania'],
					'paquete' =>$row['paquete'],
					'idpaquete' =>$row['idpaquete'],
					'producto' => $row['producto'],
					'precio' => $row['precio'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado']
					),
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
	public function lista_detalle_paquetes_id()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$datos = $allInputs['idpaquete'];
		$lista = $this->model_campania->m_cargar_detalle_paquetes_id($datos);
		$arrListado = array();
		foreach ($lista as $row) { 
			if( $row['estado'] == 1 ){
				$estado = 'HABILITADO';
				$clase = 'label-success';
			}
			if( $row['estado'] == 2 ){
				$estado = 'DESHABILITADO';
				$clase = 'label-default';
			}
			array_push($arrListado, 
				array(
					'id' => $row['iddetallepaquete'],
					'idpaquete' => $row['idpaquete'],
					'descripcion' => $row['descripcion'],
					'precio' => $row['precio'],
					'idproductomaster'=>$row['idproductomaster'],
					'especialidad'=>$row['especialidad'],
					'monto' => $row['monto_total'],
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado']
					),
					'boolProductoNuevo' => FALSE,
					'boolSelect' => TRUE,
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
	public function lista_campanias_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['nameColumn'] = (empty($allInputs['nameColumn']) ? 'descripcion' : $allInputs['nameColumn'] );
		if( isset($allInputs['search']) ){
			$lista = $this->model_campania->m_cargar_campanias_cbo($allInputs);
		}else{
			$lista = $this->model_campania->m_cargar_campanias_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id'=>$row['idcampania'],
					'descripcion' => $row['descripcion']
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
	public function lista_paquetes_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_campania->m_cargar_paquetes_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id'=>$row['idpaquete'],
					'descripcion' => $row['descripcion'],
					'paquete' => $row['descripcion'],
					'es_nuevo' => FALSE,
					'monto_total' => $row['monto_total'],
					'boolSeleccionado' => TRUE,
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
	public function lista_fechas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_campania->m_cargar_fechas($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array( 
					'id'=>$row['idfechacampania'],
					'fecha' => $row['fecha'],
					'tipo_fecha' => $row['tipo_fecha'],
					'es_nuevo' => FALSE
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
	public function ver_popup_formulario()
	{
		$this->load->view('campania/campania_formView');
	}
	public function ver_popup_formulario_clonar()
	{
		$this->load->view('campania/campania_clonar_formView');
	}	
	public function ver_popup_agregar_especialidad()
	{
		$this->load->view('campania/campania_formView');
	}

	public function ver_popup_formulario_fechas()
	{
		$this->load->view('campania/popupAgregarFechasCampania');
	}
	public function registrar(){ // registrar campaña y paquetes
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	if( empty($allInputs['fechasventa']) || empty($allInputs['fechasatencion'])){
			$arrData['message'] = 'Faltan ingresar fechas a la campaña.';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;    		
    	}

    	foreach ($allInputs['fechasventa'] as $key => $row) {
    		# code...
    		if($row['fecha'] < date('d-m-Y')){
				$arrData['message'] = 'Las fechas de venta no pueden ser menor a la fecha de hoy.';
	    		$arrData['flag'] = 2;
	    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return; 
    		}
    	}
    	
    	foreach ($allInputs['fechasatencion'] as $key => $row) {
    		# code...
    		if($row['fecha'] < date('d-m-Y')){
				$arrData['message'] = 'Las fechas de atención no pueden ser menor a la fecha de hoy.';
	    		$arrData['flag'] = 2;
	    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return; 
    		}
    	}    	

    	$campania = strtoupper($allInputs['campania']);
    	if( $this->model_campania->m_verificar($campania) ){ 
			$arrData['message'] = 'La Campaña: '.$campania.' ya existe en la Base de Datos.';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$arrData['idcampania'] = NULL;
    	// SI TODO ESTA BIEN PROCEDEMOS A REGISTRAR
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_campania->m_registrar_campania($allInputs)){
			$id = GetLastId('idcampania','campania');
			$arrData['idcampania'] = $id;
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
    		// REGISTRO DE PAQUETES
			foreach ($allInputs['paquetes'] as $key => $row) { 
				$row['idcampania'] = $id;
				if(empty($row['monto_total'])){
					$row['monto_total'] = NULL;
				}
				if( $this->model_campania->m_registrar_paquete($row) ) { 

				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}
			// REGISTRO DE FECHAS VENTA
			foreach ($allInputs['fechasventa'] as $key => $row) { 
				$row['idcampania'] = $id;
				if( $this->model_campania->m_registrar_fechas($row) ) { 

				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}
			// REGISTRO DE FECHAS ATENCION
			foreach ($allInputs['fechasatencion'] as $key => $row) { 
				$row['idcampania'] = $id;
				if( $this->model_campania->m_registrar_fechas($row) ) { 

				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}						
			
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	//------------------------------------------------

	public function clonar(){ // clonar campaña y paquetes y productos
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$hayError = TRUE;

    	if( empty($allInputs['paquetes']) || $allInputs['paquetes'] == null){
			$arrData['message'] = 'Faltan ingresar Paquetes a la campaña.';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;    		
    	}

    	if( empty($allInputs['fechasventa']) || empty($allInputs['fechasatencion'])){
			$arrData['message'] = 'Faltan ingresar fechas a la campaña.';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;    		
    	}
    	// VALIDACIONES AL MENOS 1 FECHA DE AMBOS TIPOS
    	if(empty($allInputs['fechasventa']) || empty($allInputs['fechasatencion'])){
			$arrData['message'] = 'Debe al menos tener una fecha para la atención y al menos una para la venta.';
    		$arrData['flag'] = 0;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;    		
    	}
    	// VALIDACIONES DE FERIADO
    	$listaferiados = $this->model_feriado->m_lista_feriados_cbo($allInputs);
    	foreach ($listaferiados as $key => $row) {
    		foreach ($allInputs['fechasventa'] as $key => $row2) {
    			if(darFormatoDMY($row['fecha']) ==  $row2['fecha']){
					$arrData['message'] = 'Hay una fecha de venta en un dia feriado revise la información.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;     				
    			}
    		}
    		foreach ($allInputs['fechasatencion'] as $key => $row3) {
    			if(darFormatoDMY($row['fecha']) ==  $row3['fecha']){
					$arrData['message'] = 'Hay una fecha de atención en un dia feriado revise la información.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;     				
    			}
    		}    		
    	}    	

    	$campania = strtoupper($allInputs['campania']);
    	if( $this->model_campania->m_verificar($campania) ){ 
			$arrData['message'] = 'La Campaña: '.$campania.' ya existe en la Base de Datos.';
    		$arrData['flag'] = 2;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$arrData['idcampania'] = NULL;
    	// SI TODO ESTA BIEN PROCEDEMOS A REGISTRAR
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_campania->m_registrar_campania($allInputs)){
			$id = GetLastId('idcampania','campania');
			$arrData['idcampania'] = $id;
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
    		// REGISTRO DE PAQUETES
			foreach ($allInputs['paquetes'] as $key => $row) { 
				if($row['boolSeleccionado']){
					// ojo solo seleccionados
					$row['idcampania'] = $id;
					if(empty($row['monto_total'])){
						$row['monto_total'] = NULL;
					}
					if( $this->model_campania->m_registrar_paquete($row) ) { 
						$idpaq = GetLastId('idpaquete','paquete');
						//$arrData['idpaquete'] = $id;
						// Registro de los productos del paquete
						foreach ($row['productos'] as $keypr => $rowpr) { 
							if($rowpr['boolSelect']){
								$rowpr['idpaquete'] = $idpaq;
								$rowpr['boolProductoNuevo'] = true ;
								if( $rowpr['boolProductoNuevo'] ){ // registro nuevo
									if( $this->model_campania->m_registrar_paquete_detalle($rowpr) ) { 
										$arrData['message'] = 'Se registraron los datos correctamente'; 
						    			$arrData['flag'] = 1;
									}else{
										$arrData['message'] = 'Error al registrar los datos (x1)';
					    				$arrData['flag'] = 0;
					    				$hayError = TRUE;
									}
								}else{ // actualizar precio de un producto
									if($this->model_campania->m_actualizar_paquete_detalle($rowpr)){ // solo precio
										$arrData['message'] = 'Se editaron los datos correctamente';
								   		$arrData['flag'] = 1;
									}
								}								
							}
							
						}
						if( $arrData['flag'] == 1 && !$hayError ){
							if($this->model_campania->m_actualiza_monto_paquete($row)){

				    		}else{
				    			$arrData['message'] = 'Error al actualizar los datos (x2)';
			    				$arrData['flag'] = 0;
				    		}
						}
						// Fin de registro de productos
					}else{
						$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
	    				$arrData['flag'] = 0;
					}
					// fin de seleccionados
				}
			}
			// REGISTRO DE FECHAS VENTA
			foreach ($allInputs['fechasventa'] as $key => $row) { 
				$row['idcampania'] = $id;
				if( $this->model_campania->m_registrar_fechas($row) ) { 

				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}
			// REGISTRO DE FECHAS ATENCION
			foreach ($allInputs['fechasatencion'] as $key => $row) { 
				$row['idcampania'] = $id;
				if( $this->model_campania->m_registrar_fechas($row) ) { 

				}else{
					$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    				$arrData['flag'] = 0;
				}
			}						
			
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	//------------------------------------------------

	public function registrar_detalle()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos (x0)';
    	$arrData['flag'] = 0;
    	$hayError = FALSE;

    	$this->db->trans_start();

			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idpaquete'] = $allInputs['idpaquete'];
				if( $row['boolProductoNuevo'] ){ // registro nuevo
					if( $this->model_campania->m_registrar_paquete_detalle($row) ) { 
						$arrData['message'] = 'Se registraron los datos correctamente'; 
		    			$arrData['flag'] = 1;
					}else{
						$arrData['message'] = 'Error al registrar los datos (x1)';
	    				$arrData['flag'] = 0;
	    				$hayError = TRUE;
					}
				}else{ // actualizar precio de un producto
					if($this->model_campania->m_actualizar_paquete_detalle($row)){ // solo precio
						$arrData['message'] = 'Se editaron los datos correctamente';
				   		$arrData['flag'] = 1;
					}
				}
				
			}
			if( $arrData['flag'] == 1 && !$hayError ){
				if($this->model_campania->m_actualiza_monto_paquete($allInputs)){

	    		}else{
	    			$arrData['message'] = 'Error al actualizar los datos (x2)';
    				$arrData['flag'] = 0;
	    		}
			}
			
			// $arrData['idpaqueteregister'] = $allInputs['idpaquete'];

		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_detalle(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//$arrData['idpaqueteregister'] = NULL;
   		if($this->model_campania->m_editar_paquete($allInputs)){ 
			$arrData['message'] = 'Se editaron los datos correctamente';
	   		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_fechas(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos (x0)';
    	$arrData['flag'] = 0;
    	$idcampania = $allInputs['idcampania'];
    	$hayError = false ;

    	// VALIDACIONES AL MENOS 1 FECHA DE AMBOS TIPOS
    	if(empty($allInputs['fechasventa']) || empty($allInputs['fechasatencion'])){
			$arrData['message'] = 'Debe al menos tener una fecha para la atención y al menos una para la venta.';
    		$arrData['flag'] = 0;
    		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;    		
    	}
    	// VALIDACIONES DE FERIADO
    	$listaferiados = $this->model_feriado->m_lista_feriados_cbo($allInputs);
    	foreach ($listaferiados as $key => $row) {
    		foreach ($allInputs['fechasventa'] as $key => $row2) {
    			if(darFormatoDMY($row['fecha']) ==  $row2['fecha']){
					$arrData['message'] = 'Hay una fecha de venta en un dia feriado revise la información.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;     				
    			}
    		}
    		foreach ($allInputs['fechasatencion'] as $key => $row3) {
    			if(darFormatoDMY($row['fecha']) ==  $row3['fecha']){
					$arrData['message'] = 'Hay una fecha de atención en un dia feriado revise la información.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				    return;     				
    			}
    		}    		
    	}
    	$this->db->trans_start();
    	// BUSCAMOS LAS FECHAS DE LA CAMPAÑA TIPO VENTA
    	$listafec = $this->model_campania->m_buscar_fechas_campania_venta($allInputs['idcampania']);
    	// COMPARAMOS CON LAS FECHAS DEL FORMULARIO (PARA ANULAR LAS QUE YA NO ESTAN)
    	if($listafec){
	    	foreach ($listafec as $key => $row) {
	    		$existe = false ;
	    		foreach ($allInputs['fechasventa'] as $key => $row2) {
	    			if(darFormatoDMY($row['fecha']) ==  $row2['fecha']){
	    				$existe = true ;
	    			}
	    		}
	    		if($existe == false){
	    			if($this->model_campania->m_anular_fecha($row['idfechacampania'])){
						$arrData['message'] = 'Se editaron los datos correctamente';
				   		$arrData['flag'] = 1;	    				
	    			}else{
						$arrData['message'] = 'Error al registrar los datos (x1)';
	    				$arrData['flag'] = 0;
	    				$hayError = true ;	    					    				
	    			}
	    		}
	    	}
	    	// COMPARAMOS CON LAS FECHAS DEL FORMULARIO (PARA AGREGAR LAS QUE FALTAN)
		   	foreach ($allInputs['fechasventa'] as $key => $row3) {
		   		$existe = false ;
	    		foreach ($listafec as $key => $row4) {
	    			if($row3['fecha'] ==  darFormatoDMY($row4['fecha'])){
	    				$existe = true ;
	    			}
	    		}
	    		if($existe == false){
	    			$row3['idcampania'] = $idcampania ;
	    			$row3['tipo_fecha'] = 1 ;

	    			$Searchfec['fecha'] = darFormatoYMD($row3['fecha']);
	    			$Searchfec['tipo_fecha'] = 1 ; 
	    			$Searchfec['idcampania'] = $idcampania ;

	    			if($this->model_campania->m_lista_fecha_anulada($Searchfec)){
		    			if($this->model_campania->m_actualizar_fecha($row3)){
							$arrData['message'] = 'Se editaron los datos correctamente';
					   		$arrData['flag'] = 1;	    				
		    			}else{
							$arrData['message'] = 'Error al registrar los datos (x1)';
		    				$arrData['flag'] = 0;
		    				$hayError = true ;    				
		    			}
	    			}else{
		    			if($this->model_campania->m_registrar_fecha($row3)){
							$arrData['message'] = 'Se editaron los datos correctamente';
					   		$arrData['flag'] = 1;	    				
		    			}else{
							$arrData['message'] = 'Error al registrar los datos (x1)';
		    				$arrData['flag'] = 0;
		    				$hayError = true ;    				
		    			}	    				
	    			}

	    		}    		
			}
    	}else{ 	// SI NO EXISTEN FECHAS EN LA TABLA FECHA_CAMPANIA
    		foreach ($allInputs['fechasventa'] as $key => $row) {
    			# code...
    			$row['idcampania'] = $idcampania ;
	    		$row['tipo_fecha'] = 1 ;
			    if($this->model_campania->m_registrar_fecha($row)){
					$arrData['message'] = 'Se editaron los datos correctamente';
			   		$arrData['flag'] = 1;	    				
				}else{
					$arrData['message'] = 'Error al registrar los datos (x1)';
					$arrData['flag'] = 0;
					$hayError = true ;    				
				}
    		}
    	}
    	// BUSCAMOS LAS FECHAS DE LA CAMPAÑA TIPO ATENCION
    	$listafec2 = $this->model_campania->m_buscar_fechas_campania_atencion($allInputs['idcampania']);
    	// COMPARAMOS CON LAS FECHAS DEL FORMULARIO (PARA ANULAR LAS QUE YA NO ESTAN)
    	if($listafec2){
	    	foreach ($listafec2 as $key => $row) {
	    		$existe = false ;
	    		foreach ($allInputs['fechasatencion'] as $key => $row2) {
	    			if(darFormatoDMY($row['fecha']) ==  $row2['fecha']){
	    				$existe = true ;
	    			}
	    		}
	    		if($existe == false){
	    			if($this->model_campania->m_anular_fecha($row['idfechacampania'])){
						$arrData['message'] = 'Se editaron los datos correctamente';
				   		$arrData['flag'] = 1;	    				
	    			}else{
						$arrData['message'] = 'Error al registrar los datos (x1)';
	    				$arrData['flag'] = 0;
	    				$hayError = true ;	    					    				
	    			}
	    		}
	    	}    		
	    	// COMPARAMOS CON LAS FECHAS DEL FORMULARIO (PARA AGREGAR LAS QUE FALTAN)
		   	foreach ($allInputs['fechasatencion'] as $key => $row3) {
		   		$existe = false ;
	    		foreach ($listafec2 as $key => $row4) {
	    			if($row3['fecha'] ==  darFormatoDMY($row4['fecha'])){
	    				$existe = true ;
	    			}
	    		}
	    		if($existe == false){
	    			$row3['idcampania'] = $idcampania ;
	    			$row3['tipo_fecha'] = 2 ;

	    			$Searchfec['fecha'] = darFormatoYMD($row3['fecha']);
	    			$Searchfec['tipo_fecha'] = 2 ; 
	    			$Searchfec['idcampania'] = $idcampania ;

	    			if($this->model_campania->m_lista_fecha_anulada($Searchfec)){
		    			if($this->model_campania->m_actualizar_fecha($row3)){
							$arrData['message'] = 'Se editaron los datos correctamente';
					   		$arrData['flag'] = 1;	    				
		    			}else{
							$arrData['message'] = 'Error al registrar los datos (x1)';
		    				$arrData['flag'] = 0;
		    				$hayError = true ;	
		    			}
	    			}else{
		    			if($this->model_campania->m_registrar_fecha($row3)){
							$arrData['message'] = 'Se editaron los datos correctamente';
					   		$arrData['flag'] = 1;	    				
		    			}else{
							$arrData['message'] = 'Error al registrar los datos (x1)';
		    				$arrData['flag'] = 0;
		    				$hayError = true ;	
		    			}
	    			}
	    		}    		
			}
    	}else{
    		foreach ($allInputs['fechasatencion'] as $key => $row) {
    			# code...
    			$row['idcampania'] = $idcampania ;
	    		$row['tipo_fecha'] = 2 ;
			    if($this->model_campania->m_registrar_fecha($row)){
					$arrData['message'] = 'Se editaron los datos correctamente';
			   		$arrData['flag'] = 1;	    				
				}else{
					$arrData['message'] = 'Error al registrar los datos (x1)';
					$arrData['flag'] = 0;
					$hayError = true ;    				
				}
    		}    		
    	}

    	if ($hayError == false){
			$arrData['message'] = 'Se editaron los datos correctamente';
			$arrData['flag'] = 1;
    	}

		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	/*NO SE USA - ES PARA REGISTRAR UN SOLO DETALLE DESDE EL EDITAR*/
	public function registrar_detalle_paquete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// $allInputs['tiene_descuento'] = 2;
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// SI TODO ESTA BIEN PROCEDEMOS A REGISTRAR
  		$this->db->trans_start();
  		if($this->model_campania->m_registrar_paquete_detalle($allInputs)){ 
    		if($this->model_campania->m_actualiza_monto_paquete($allInputs)){
				$arrData['message'] = 'Se registraron los datos correctamente';
    			$arrData['flag'] = 1;
    		}
		}
    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar(){ // edita campaña y paquetes
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// if( empty($allInputs['descripcion']) ){
    	// 	$allInputs['descripcion'] = $allInputs['campania'];
    	// }
		if($this->model_campania->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
    		foreach ($allInputs['paquetes'] as $key => $row) {
    			if( $row['es_nuevo']){
	    			$row['idcampania'] = $allInputs['id'];
					if(empty($row['monto_total'])){
						$row['monto_total'] = NULL;
					}
					if( $this->model_campania->m_registrar_paquete($row) ) { 

					}else{
						$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
	    				$arrData['flag'] = 0;
					}
    			}
				
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo habilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_campania->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar el registro';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_campania->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_campania->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_detalle()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
    	if( $this->model_campania->m_anular_detalle($allInputs['id']) ){
    		if( $this->model_campania->m_actualiza_monto_paquete($allInputs) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
    		}
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_paquete()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
    	if( $this->model_campania->m_anular_paquete($allInputs['idpaquete']) ){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	/* ======================================*/
	/* DETALLE CAMPANIA 					 */
	/*=======================================*/


	public function ver_popup_formulario_Detalle()
	{
		$this->load->view('campania/detalle_campania_formView');
	}


	/* luis - ventas */ 
	public function lista_campanias_paquetes_cbo() // SOLO CAMPAÑAS DE LA EMPRESA ADMIN SELECCIONADA 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump( $allInputs ); exit();
		$lista = $this->model_campania->m_cargar_campanias_paquetes_cbo($allInputs['datos']);
		$arrListado = array(); 
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idpaquete'],
					'idcampania' => $row['idcampania'],
					'descripcion' => strtoupper($row['campania'].' - '.$row['paquete'])
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
	public function lista_campanias_paquetes_detalle()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump( $allInputs ); exit();
		$lista = $this->model_campania->m_cargar_campanias_paquetes_detalle($allInputs['datos']);
		$arrListado = array(); 
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['iddetallepaquete'],
					'idpaquete' => $row['idpaquete'],
					'paquete' => $row['paquete'],
					'idtipocampania' => $row['idtipocampania'],
					'tipocampania' => $row['tipo_campania'],
					'idcampania' => $row['idcampania'],
					'campania' => $row['campania'],
					'idproductomaster' => $row['idproductomaster'],
					'idtipoproducto' => $row['idtipoproducto'],
					'tipoproducto' => $row['nombre_tp'],
					'producto' => $row['producto'],
					'precio' => $row['precio'],
					'idespecialidad' => $row['idespecialidad'],
					'tiene_prog_cita' => ($row['tiene_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_cita' => ($row['tiene_venta_prog_cita'] == 1) ? TRUE : FALSE, 
					'tiene_prog_proc' => ($row['tiene_prog_proc'] == 1) ? TRUE : FALSE, 
					'tiene_venta_prog_proc' => ($row['tiene_venta_prog_proc'] == 1) ? TRUE : FALSE, 
					'especialidad' => $row['nombre'] 
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

	public function ver_popup_formulario_paquete_detalle(){
		$this->load->view('campania/productos_campania_formView');
	}

	public function ver_popup_formulario_clonar_paquete_detalle(){
		$this->load->view('campania/productos_campania_clonar_formView');
	}	
}