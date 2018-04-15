<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Medicamento extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper','config_helper'));
		$this->load->model(array('model_medicamento','model_laboratorio','model_almacen_farmacia','model_medicamento_almacen','model_config'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function lista_medicamento()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = $allInputs['datos'];
		//$paramDatos[1] = $allInputs['tipoProducto'];
		//var_dump(" hey " . $paramDatos['tipoProducto']); exit();
		$lista = $this->model_medicamento->m_cargar_medicamento($paramPaginate,$paramDatos); 
		$totalRows = $this->model_medicamento->m_count_medicamento($paramPaginate,$paramDatos); 
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_med'] == 1 ){
				$estadoMed = 'HABILITADO';
				$claseMed = 'label-success';
			}
			if( $row['estado_med'] == 2 ){
				$estadoMed = 'DESHABILITADO';
				$claseMed = 'label-default';
			}
			if( $row['generico'] == 1 ){
				$estadoGen = 'GENERICO';
				$claseGen = 'label-info';
			}
			if( $row['generico'] == 2 ){
				$estadoGen = 'DE MARCA';
				$claseGen = 'label-warning';
			}
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'], 
					'medicamento' => strtoupper($row['medicamento']),
					'idpresentacion' => ( $row['generico'] == 1 ) ? $row['idunidadmedida'] : $row['idpresentacion'],
					'presentacion' => $row['presentacion'],
					'idlaboratorio' => $row['idlaboratorio'],
					'laboratorio' => $row['nombre_lab'],
					'idmedidaconcentracion' => $row['idmedidaconcentracion'],
					'medidaconcentracion' => $row['descripcion_mc'],
					'idcondicionventa' => $row['idcondicionventa'],
					'condicionventa' => $row['descripcion_cv'],
					'idviaadministracion' => $row['idviaadministracion'],
					'viaadministracion' => $row['descripcion_va'],
					'idformafarmaceutica' => $row['idformafarmaceutica'],
					'formafarmaceutica' => $row['descripcion_ff'],
					'val_concentracion' => $row['val_concentracion'],
					'registro_sanitario' => $row['registro_sanitario'],
					'contenido' => $row['contenido'],
					'generico' => (int)$row['generico'],
					'codigo_barra' => $row['codigo_barra'],
					'excluyeigv' => ($row['excluye_igv'] == 1 ? true : false),
					'idtipoproducto' => $row['idtipoproducto'],
					'estadoMed' => array(
						'string' => $estadoMed,
						'clase' =>$claseMed,
						'bool' =>$row['estado_med']
					),
					'estadoGen' => array(
						'string' => $estadoGen,
						'clase' =>$claseGen,
						'bool' =>$row['generico']
					)
				)
			);
		}
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
	public function lista_medida_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( isset($allInputs['search']) ){
			$lista = $this->model_medicamento->m_cargar_medida_cbo($allInputs);
		}else{
			$lista = $this->model_medicamento->m_cargar_medida_cbo();
		}
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idunidadmedida'],
					'descripcion' => $row['descripcion_um']
					//'abreviatura_um' => $row['abreviatura_um']
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
	public function lista_medicamento_autocomplete_para_farmacia() // lista_medicamento_autocomplete_para_farmacia
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); 

		// if( @$this->sessionHospital['idsede'] == 1 ){
			// $idsede = 1; 
			$lista = $this->model_medicamento->m_cargar_medicamento_autocomplete_farmacia($allInputs['searchColumn'],$allInputs['searchText'],$this->sessionHospital['idsede']);
		// }
		$hayStock = true;
		$arrListado = array();
		// if(empty($lista) &&  @$this->sessionHospital['idsede'] == 3 ){ // LURIN 
		// 	$hayStock = false;
		// 	$lista = $this->model_medicamento->m_cargar_solo_medicamento_autocomplete($allInputs['searchColumn'],$allInputs['searchText']);
		// }

		foreach ($lista as $row) {
			// if( $this->sessionHospital['id_empresa_admin'] == 38 ){
			//	$medicamento_stock = $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento']);
			// }else{
			$medicamento_stock = strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span>';
			// }
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'presentacion' => $row['presentacion'],
					'medicamento' => trim($row['medicamento']),
					// 'medicamento_stock' => $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento']),
					'medicamento_stock' => $medicamento_stock,
					// 'medicamento_stock' => strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span>', 
					'idtipoproducto' => $row['idtipoproducto'],
					'excluye_igv' => $row['excluye_igv'],
					'contenido' => $row['contenido'],
					'acepta_caja_unidad' => $hayStock? $row['acepta_caja_unidad'] : NULL,
					'formafarmaceutica' => $row['descripcion_ff'],
					'stock' => $hayStock? @$row['stock'] : NULL
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
	public function lista_medicamento_autocomplete() 
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); 

		if( @$this->sessionHospital['id_empresa_admin'] == 39 ){
			$idsede = 1; 
			$lista = $this->model_medicamento->m_cargar_medicamento_autocomplete_medico($allInputs['searchColumn'],$allInputs['searchText'],$idsede);
		}
		$hayStock = true;
		$arrListado = array();
		if(empty($lista) &&  @$this->sessionHospital['id_empresa_admin'] == 38 ){ // LURIN 
			$hayStock = false;
			$lista = $this->model_medicamento->m_cargar_solo_medicamento_autocomplete_medico($allInputs['searchColumn'],$allInputs['searchText']);
		}
		
		foreach ($lista as $row) {
			$principios = '';
			
			if (!empty($row['principios'])) {
				$principios = ' | <span class="text-ingo-gris">(' . @$row['principios'] . ')</span>';
			}
			
			if( $this->sessionHospital['id_empresa_admin'] == 38 ){
				$medicamento_stock = $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento'] . $principios);
			}else{
				$medicamento_stock = strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span> '. $principios;
			}
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'presentacion' => $row['presentacion'],
					'medicamento' => trim($row['medicamento']),
					// 'medicamento_stock' => $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento']),
					'medicamento_stock' => $medicamento_stock,
					'long' => strlen($medicamento_stock),
					// 'medicamento_stock' => strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span>', 
					'idtipoproducto' => $row['idtipoproducto'],
					'excluye_igv' => $row['excluye_igv'],
					'contenido' => $row['contenido'],
					'acepta_caja_unidad' => $hayStock? $row['acepta_caja_unidad'] : NULL,
					'formafarmaceutica' => $row['descripcion_ff'],
					'stock' => $hayStock? @$row['stock'] : NULL					
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
	public function carga_medicamento_por_codigo()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_medicamento->m_cargar_medicamento_por_codigo($allInputs['codigo']);
		if( empty($lista) ){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se encontraron datos.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;	
		}
		$arrListado = array();
		foreach ($lista as $row) {
			// if( $this->sessionHospital['idsede'] == 3 ){
			// 	$medicamento_stock = $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento']);
			// }else{
			// 	$medicamento_stock = strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span>';
			// }
			array_push($arrListado,
				array(
					'id' => $row['idmedicamento'],
					'presentacion' => $row['presentacion'],
					'medicamento' => trim($row['medicamento']),
					// 'medicamento_stock' => $hayStock? strtoupper(trim($row['medicamento'])) . ' | <span class="text-bold text-info">STOCK: ' . @$row['stock'] . ' UND.</span>' : trim($row['medicamento']),
					// s'medicamento_stock' => $medicamento_stock,
					// 'medicamento_stock' => strtoupper($row['medicamento']) . ' | <span class="text-info">STOCK: ' . @$row['stock'] . ' UND.</span>',
					'idtipoproducto' => $row['idtipoproducto'],
					'excluye_igv' => $row['excluye_igv'],
					'contenido' => $row['contenido'],
					'acepta_caja_unidad' => $row['acepta_caja_unidad'],
					'formafarmaceutica' => $row['descripcion_ff'],
					'stock' => NULL,
				)
			);
		}
		
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}

	public function ver_popup_formulario()
	{
		$this->load->view('medicamento/medicamento_formView');
	}

	public function ver_popup_agregar_principio_activo()
	{
		$this->load->view('medicamento/popupAgregarPrincipioActivo'); 
	}
	public function ver_popup_busqueda_medicamento()
	{
		$this->load->view('medicamento/busquedaMedicamentoFormView'); 
	}
	public function ver_popup_busqueda_medicamento_atencion_medica()
	{
		$this->load->view('medicamento/busquedaMedicamentoAtencionMed'); 
	}
	public function registrar()
	{
		$arrConfig = obtener_parametros_configuracion();
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0; 
    	foreach ($allInputs['almacenes'] as $key => $row) { 
    		if( empty($row['precio']) || !(is_numeric($row['precio'])) ){ 
    			unset($allInputs['almacenes'][$key]);
    		}
    	}
    	if(!empty($allInputs['registro_por_solicitud'])){
    		$allInputs['creado_en_solicitud'] = 1;
    		$allInputs['laboratorio']['id'] = 556; // corporacion JJ
    		$allInputs['idcondicionventa'] = 2; // PRESENTA RECETA
    		$allInputs['idviaadministracion'] = 16; // VÍA TÓPICA
    		$allInputs['contenido'] = 1; // por defecto 1
	    	if( empty($allInputs['almacenes']) ){
	    		$arrData['message'] = 'Debe ingresar un precio válido';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
    		
    	}
    	// VALIDAR NOMBRE
    	$allInputs['medicamento'] = strtoupper_total(preg_replace('/\s+/', ' ', $allInputs['medicamento']));
    	$sinEspacios =  str_replace(' ', '', $allInputs['medicamento']);
    	$arrFilters = array( 
    		// 'searchColumn' => 'denominacion',
    		'searchText' => $sinEspacios
    	);
    	$medicamento = $this->model_medicamento->m_verificar_medicamento_similar($arrFilters);
    	if( $medicamento ){
    		$arrData['message'] = 'La fórmula ya existe.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	
    	// GENERAR UN CODIGO SOLO PARA FORMULAS DE JJ
		$allInputs['idformula_jj'] = NULL;
		$allInputs['fecha_asigna_idformula_jj'] = NULL;
    	if($allInputs['idtipoproducto'] == 22 ){
	    	$ultimoCodigo = $this->model_medicamento->m_cargar_ultimo_codigo_formula();
	    	$correlativo = mb_substr($ultimoCodigo,2);
	    	$allInputs['idformula_jj'] = 'VS' . str_pad(((int)$correlativo + 1), 4, '0', STR_PAD_LEFT);
	    	$allInputs['fecha_asigna_idformula_jj'] = NULL;
    	}
    	$this->db->trans_start();
		if( $this->model_medicamento->m_registrar($allInputs) ){ 
			$ultimoidmedicamento = GetLastId('idmedicamento','medicamento');
			$arrData['message'] = 'Se registraron los datos correctamente, sin el almacen.';
	    	$arrData['flag'] = 1;
			foreach ($allInputs['almacenes'] as $key => $row) {
				//AGREGAMOS EL PRECIO DE VENTA DESDE EL 20% DEL PRECIO COSTO SI ES UNA FORMULA O PREPARADO
				if(!empty($allInputs['registro_por_solicitud'])){
					$row['precio_venta'] = $row['precio']*1.20;
				}
				$fSubAlmacen = $this->model_almacen_farmacia->m_obtener_subalmacen_principal($row['id']); // idalmacen 
				if(!(empty($fSubAlmacen))){ 
					$row['idsubalmacen'] = $fSubAlmacen['idsubalmacen']; 
					$row['idmedicamento'] = $ultimoidmedicamento; 
					if( $this->model_medicamento->m_registrar_medicamento_en_almacen($row) ){ 
						$arrData['message'] = 'Se registraron los datos correctamente';
	    				$arrData['flag'] = 1;
					}
				}
				if(!(empty($row['idsubalmacen_venta']))){
					$row['idsubalmacen'] = $row['idsubalmacen_venta']; 
					$row['idmedicamento'] = $ultimoidmedicamento; 
					if( $this->model_medicamento->m_registrar_medicamento_en_almacen($row) ){ 
						$arrData['message'] = 'Se registraron los datos correctamente';
	    				$arrData['flag'] = 1;
					}
				}
			}
		}
    	
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se registraron los datos correctamente';
	    $arrData['flag'] = 1;
    	foreach ($allInputs['almacenes'] as $key => $row) {
    		if( !(is_numeric($row['precio'])) ){ 
    			unset($allInputs['almacenes'][$key]);
    		}
    	}
    	// var_dump($allInputs['almacenes']); exit(); 
		if($this->model_medicamento->m_editar($allInputs)){ 
			foreach ($allInputs['almacenes'] as $key => $row) { 
				if( empty($row['idmedicamentoalmacen']) ){ 
					$fSubAlmacen = $this->model_almacen_farmacia->m_obtener_subalmacen_principal($row['id']); // idalmacen
					// $arrSubAlmacenVenta = $this->model_almacen_farmacia->m_obtener_subalmacen_venta($row['id']);
					if(!(empty($fSubAlmacen))){ 
						$row['idsubalmacen'] = $fSubAlmacen['idsubalmacen']; 
						$row['idmedicamento'] = $allInputs['id'];
						if( $this->model_medicamento->m_registrar_medicamento_en_almacen($row) ){ 
							$arrData['message'] = 'Se registraron los datos correctamente';
		    				$arrData['flag'] = 1;
						}else{
							$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    						$arrData['flag'] = 0;
						}
						// foreach ($arrSubAlmacenVenta as $subalmacen_venta) {
						// 	$row['idsubalmacen'] = $subalmacen_venta['idsubalmacen']; 
						// 	$this->model_medicamento->m_registrar_medicamento_en_almacen($row);
						// }
					}
					if(!(empty($row['idsubalmacen_venta']))){
						$row['idsubalmacen'] = $row['idsubalmacen_venta']; 
						$row['idmedicamento'] = $allInputs['id']; 
						if( $this->model_medicamento->m_registrar_medicamento_en_almacen($row) ){ 
							$arrData['message'] = 'Se registraron los datos correctamente';
		    				$arrData['flag'] = 1;
						}
					}
					
				}else{
					if( !empty($row['precio']) ){
						$precio_venta_anterior = $this->model_medicamento_almacen->m_listar_precio_venta($row['idmedicamentoalmacen']);
						if( $precio_venta_anterior['precio_venta'] != $row['precio'] ){
							$row['precio_venta_anterior'] = $precio_venta_anterior['precio_venta'];
							$row['precio_venta'] = $row['precio'];
							$this->model_medicamento_almacen->m_registrar_historial_precio($row);
						}
						// --
						if( $this->model_medicamento->m_editar_medicamento_en_almacen($row) ){

							$arrData['message'] = 'Se registraron los datos correctamente';
		    				$arrData['flag'] = 1;
						}else{
							$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
	    					$arrData['flag'] = 0;
						}
					}
				}
			} 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function actualizar_codigo_barra_producto()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_medicamento->m_editar_codigo_barra($allInputs)){ 
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData)); 
	}
	public function deshabilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo deshabilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_medicamento->m_deshabilitar($row['id']) ){
				$arrData['message'] = 'Se deshabilitaron los datos correctamente';
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
    	$existeMedicamentoAlmacen = false ;
    	$medicamentos = '';
    	foreach ($allInputs as $row) {
			if( $lista=$this->model_medicamento->m_buscar_medicamento_almacen($row['id']) ){
		    	$existeMedicamentoAlmacen = true ;
		    	$medicamentos .= $lista['medicamento'].'<br/> ';
			}
		}
    	if( $existeMedicamentoAlmacen === true ){ 
    		$arrData['message'] = 'Los siguientes medicamentos no se pueden anular: <br/>'.$medicamentos;
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}

    	foreach ($allInputs as $row) {
			if( $this->model_medicamento->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function habilitar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$arrData['message'] = 'No se pudo habilitar los datos';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $row) {
			if( $this->model_medicamento->m_habilitar($row['id']) ){
				$arrData['message'] = 'Se habilitaron los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_formula()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se editar los datos';
    	$arrData['flag'] = 0;
    	// VALIDAR NOMBRE
    	$allInputs['medicamento'] = strtoupper_total(preg_replace('/\s+/', ' ', $allInputs['medicamento']));
    	$sinEspacios =  str_replace(' ', '', $allInputs['medicamento']);
    	$arrFilters = array( 
    		// 'searchColumn' => 'denominacion',
    		'searchText' => $sinEspacios,
    		'excepto' => $allInputs['id'],
    	);
    	$medicamento = $this->model_medicamento->m_verificar_medicamento_similar($arrFilters);
    	if( $medicamento ){
    		$arrData['message'] = 'Ya existe una fórmula con el mismo nombre.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
		if( $this->model_medicamento->m_editar_formula($allInputs) ){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function eliminar_formula()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'No se pudo anular los datos';
    	$arrData['flag'] = 0;
    	if( $this->model_medicamento->m_buscar_medicamento_solicitud($allInputs['id']) ){ 
    		$arrData['message'] = 'No se puede eliminar la fórmula. Ya está en uso';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		if( $this->model_medicamento->m_anular($allInputs['id']) ){
			$arrData['message'] = 'Se eliminó correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_tipo_producto_far_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);		
		$lista = $this->model_medicamento->m_cargar_tipo_producto_far_cbo($allInputs);
		
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado, 
				array(
					'id' => $row['idtipoproducto'],
					'descripcion' => $row['nombre_tp']					
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