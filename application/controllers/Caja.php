<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Caja extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('otros_helper','fechas_helper'));
		$this->load->model(array('model_caja','model_caja_farmacia','model_tipo_documento','model_empresa_admin'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		date_default_timezone_set("America/Lima");
		// if(!@$this->sessionHospital) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function ver_popup_detalle_caja()
	{
		$this->load->view('caja/popupVerDetalleCajaView');
	}
	public function ver_popup_detalle_caja_farm()
	{
		$this->load->view('caja/popupVerDetalleCajaFarmView');
	}
	public function ver_popup_detalle_caja_por_tipo_documento()
	{
		$this->load->view('caja/popupVerDetalleCajaTipoDocumentoView');
	}
	public function lista_cajas()
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$idModulo = $allInputs['idmodulo']; 
		if( @$allInputs['empresa'] === 'all' || empty($allInputs['empresa']) ){ 
			$lista = $this->model_caja->m_cargar_cajas_master_tipo_doc();
		}else{
			$lista = $this->model_caja->m_cargar_cajas_master_tipo_doc($allInputs,$idModulo);
		}
		$arrListado = array();
		$arrListadoOrden = array();
		foreach ($lista as $row) {
			array_push($arrListadoOrden, 
				array(
					'id' => $row['idcajamaster'],
					'idempresa' => $row['idempresaadmin'],
					'empresa' => $row['razon_social'],
					'caja' => $row['descripcion_caja'],
					'numero' => $row['numero_caja'],
					'serie' => $row['serie_caja'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'iddocumentocaja' => $row['iddocumentocaja'],
					'numeroserie' => $row['numero_serie'],
					'maquina_registradora' => $row['maquina_registradora']
				)
			);
		}
		
		$arrGroupBy = array();
		foreach ($arrListadoOrden as $key => $row) { 
			$otherRow = array(
				'id' => $row['id'],
				'idempresa' => $row['idempresa'],
				'empresa' => $row['empresa'],
				'caja' => $row['caja'],
				'serie' => $row['serie'],
				'numero' => $row['numero'],
				'maquina_registradora' => $row['maquina_registradora']
				//'detalle' => array()
			);
			$arrGroupBy[$row['id']] = $otherRow;
		}
		//var_dump($arrGroupBy); exit();
		foreach ($arrGroupBy as $key => $row) { 
			foreach ($arrListadoOrden as $keyDet => $rowDet) { 
				if( $rowDet['id'] == $row['id'] ){ 
					$arrGroupBy[$key][$rowDet['tipodocumento']] = (int)$rowDet['numeroserie'];
				}
			}
		}
		//var_dump("<pre>",$arrGroupBy); exit();
		$arrListado = array_values($arrGroupBy);
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
	public function lista_cajas_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( @$allInputs['sedeempresa'] === 'all' || empty($allInputs['sedeempresa']) ){ 
			return;
		}
		/* HALLAMOS TAMBIEN LA CAJA ACTUAL */ 
		$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario(@$allInputs['idmodulo']);
		if( empty($fCaja) && ($this->sessionHospital['key_group'] == 'key_caja' || $this->sessionHospital['key_group'] == 'key_caja_far') ) { 
			return;
		}
		//$fSedeEmpresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
		//$allInputs['empresa'] = $fSedeEmpresaAdmin['idempresaadmin'];
		$lista = $this->model_caja->m_cargar_cajas_master_abiertas($allInputs);
		$arrListado = array();
		$duenoCaja = NULL;
		// var_dump("<pre>",$lista);
		// var_dump("<pre>",$fCaja);
		foreach ($lista as $row) { // var_dump($row); exit();
			$duenoCaja = NULL;
			if( $fCaja['idcaja'] == $row['idcaja'] ){ 
				$duenoCaja = ' (Mi Caja)';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idcajamaster'],
					'descripcion' => strtoupper('Caja N° '.$row['numero_caja'].' - '.$row['username'].$duenoCaja)
				)
			);
		}

		$arrData['datos'] = $arrListado;
		$arrData['cajaactual'] = $fCaja;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_todas_cajas_master_cbo()
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_caja->m_cargar_cajas_todas_cajas_master_session($allInputs);
		$arrListado = array();
		$duenoCaja = NULL;
		foreach ($lista as $row) { // var_dump($row); exit();
			array_push($arrListado, 
				array(
					'id' => $row['idcajamaster'],
					'descripcion' => 'CAJA N° '.$row['numero_caja']
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
	public function lista_todas_cajas_master_usuario_cbo() // solo las cajas abiertas por el usuario el dia dado
	{ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(!IsDate($allInputs['fecha'])){
			$arrData['flag'] = 0;
			$arrData['message'] = '';
			$arrData['datos'] = '';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}
		
		$lista = $this->model_caja->m_cargar_cajas_todas_cajas_master_usuario_session($allInputs);
		$arrListado = array();
		$duenoCaja = NULL;
		foreach ($lista as $row) { // var_dump($row); exit();
			array_push($arrListado, 
				array(
					'id' => $row['idcajamaster'],
					'descripcion' => 'CAJA N° '.$row['numero_caja']
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
	public function lista_apertura_cajas()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$allInputs['datos']['tipodocumento'] = implode(',', $allInputs['datos']['tipodocumento']);
		$lista = $this->model_caja->m_cargar_apertura_caja($allInputs['paginate'], $allInputs['datos']);
		$totalRows = $this->model_caja->m_count_sum_apertura_caja($allInputs['paginate'], $allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) { 
			$sumaTotalVenta = $row['total_venta'];
			if( !empty($row['suma_nota_credito']) || !empty($row['suma_extorno']) ) { 
		       $sumaTotalVenta = $row['total_venta'] + $row['suma_nota_credito'] + $row['suma_extorno']; 
		    } 
			array_push($arrListado, 
				array(
					'id' => $row['idcaja'], // apertura_caja 
					'idcajamaster' => $row['idcajamaster'],
					'fecha_apertura' => formatoFechaReporte($row['fecha_apertura']),
					'usuario' => strtoupper($row['username']),
					'numero_caja' => 'Caja N° '.$row['numero_caja'],
					'suma_nota_credito' => $row['suma_nota_credito'],
					'cantidad_nota_credito' => $row['cantidad_nota_credito'],
					'cantidad_anulado' => $row['cantidad_anulado'],
					'cantidad_venta' => $row['cantidad_venta'],
					'total_venta' =>  number_format($sumaTotalVenta,2)
				)
			);
		} 
    	$arrData['sumCantNC'] = empty($totalRows['cantidad_ncr']) ? 0 : $totalRows['cantidad_ncr'];
    	$arrData['sumCantA'] = empty($totalRows['cantidad_anulado']) ? 0 : $totalRows['cantidad_anulado'];
    	$arrData['sumCantV'] = empty($totalRows['cantidad_venta']) ? 0 : $totalRows['cantidad_venta'];
    	$arrData['sumTotalV'] = number_format(empty($totalRows['total_importe']) ? 0 : $totalRows['total_importe'],2);
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;

    	$listaTXTD = $this->model_caja->m_cargar_ventas_por_tipo_documento($allInputs['datos']);
		$arrListado = array();
		foreach ($listaTXTD as $row) { 
			// $sumaTotalVenta = $row['total_venta'];
			array_push($arrListado, 
				array(
					// 'descripcion_caja' => strtoupper($row['descripcion_caja']), 
					'fecha_apertura' => formatoFechaReporte($row['fecha_apertura']),
					'usuario' => strtoupper($row['username']),
					'numero_caja' => 'Caja N° '.$row['numero_caja'],
					'tipo_documento' => strtoupper($row['descripcion_td']), // apertura_caja 
					'cantidad' => $row['cantidad'],
					'total' => number_format($row['total'],2)
				)
			);
		} 
    	$arrData['datosTXTD'] = $arrListado;

		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_apertura_cajas_farm()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_caja_farmacia->m_cargar_apertura_caja($allInputs['paginate'], $allInputs['datos']);
		$totalRows = $this->model_caja_farmacia->m_count_sum_apertura_caja($allInputs['paginate'], $allInputs['datos']);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array(
					'id' => $row['idcaja'], // apertura_caja 
					'idcajamaster' => $row['idcajamaster'],
					'fecha_apertura' => formatoFechaReporte($row['fecha_apertura']),
					'usuario' => strtoupper($row['username']),
					'numero_caja' => 'Caja N° '.$row['numero_caja'],
					'suma_nota_credito' => number_format($row['total_salidas'],2),
					'cantidad_nota_credito' => $row['cantidad_salidas'],
					'cantidad_anulado' => $row['cantidad_anulado'],
					'cantidad_venta' => $row['cantidad_venta'],
					'total_venta' =>  number_format($row['total_importe'],2)
				)
			);
		} 
    	$arrData['sumCantNC'] = empty($totalRows['cantidad_salidas']) ? 0 : $totalRows['cantidad_salidas'];
    	$arrData['sumTotalNC'] = empty($totalRows['total_salidas']) ? 0 : $totalRows['total_salidas'];
    	$arrData['sumCantA'] = empty($totalRows['cantidad_anulado']) ? 0 : $totalRows['cantidad_anulado'];
    	$arrData['sumCantV'] = empty($totalRows['cantidad_venta']) ? 0 : $totalRows['cantidad_venta'];
    	$arrData['sumTotalV'] = number_format(empty($totalRows['total_importe']) ? 0 : $totalRows['total_importe'],2);
    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;

    	$listaTXTD = $this->model_caja_farmacia->m_cargar_ventas_por_tipo_documento($allInputs['datos']);
		$arrListado = array();
		foreach ($listaTXTD as $row) { 
			// $sumaTotalVenta = $row['total_venta'];
			array_push($arrListado, 
				array(
					// 'descripcion_caja' => strtoupper($row['descripcion_caja']), 
					'fecha_apertura' => formatoFechaReporte($row['fecha_apertura']),
					'usuario' => strtoupper($row['username']),
					'numero_caja' => 'Caja N° '.$row['numero_caja'],
					'tipo_documento' => strtoupper($row['descripcion_td']), // apertura_caja 
					'cantidad' => $row['cantidad'],
					'total' => number_format($row['total'],2)
				)
			);
		} 
    	$arrData['datosTXTD'] = $arrListado;

		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_apertura_caja_tipo_doc()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_caja->m_cargar_ventas_por_caja_y_tipo_documento($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			// $sumaTotalVenta = $row['total_venta'];
			array_push($arrListado, 
				array(
					'tipo_documento' => strtoupper($row['descripcion_td']), // apertura_caja 
					'cantidad' => $row['cantidad'],
					'total' => number_format($row['total'],2)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	// $arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_apertura_caja_tipo_doc_farm()
	{
		$allInputs = json_decode(trim(file_get_contents('php://input')),true); // var_dump($allInputs); exit(); 
		$lista = $this->model_caja_farmacia->m_cargar_ventas_por_caja_y_tipo_documento($allInputs);
		$arrListado = array();
		foreach ($lista as $row) { 
			// $sumaTotalVenta = $row['total_venta'];
			array_push($arrListado, 
				array(
					'tipo_documento' => strtoupper($row['descripcion_td']), // apertura_caja 
					'cantidad' => $row['cantidad'],
					'total' => number_format($row['total'],2)
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	// $arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_detalle_apertura_cajas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs); exit(); 
		$lista = $this->model_caja->m_cargar_detalle_apertura_caja($allInputs['paginate'], $allInputs['datos']); 
		$totalRows = $this->model_caja->m_count_detalle_apertura_caja($allInputs['paginate'], $allInputs['datos']); 
		$arrListado = array();
		$arrSumatoria = array( 
			// 'sumCantNC'=> null,
			// 'sumCantA'=> null,
			// 'sumCantV'=> null,
			'sumTotalV'=> null
		);
		foreach ($lista as $row) { 
			$strMedico = $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'];
			if(empty($strMedico)) {
				$strMedico = '[SIN MÉDICO]'; 
			}
			array_push($arrListado, 
				array(
					'id' => $row['idventa'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_venta' => formatoFechaReporte($row['fecha_venta']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'idmedico' => $row['idmedico'],
					'medico' => $strMedico,
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'], 
					'total_a_pagar_format' => $row['total_a_pagar_format'] 
				)
			); 
		} 
    	$arrData['sumTotalV'] = number_format(empty($arrSumatoria['sumTotalV']) ? 0 : $arrSumatoria['sumTotalV'],2);
    	$arrData['sumTotal'] = empty($totalRows['sumatotal']) ? 0 : $totalRows['sumatotal'];
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
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
	public function lista_detalle_apertura_cajas_farm()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = $this->model_caja_farmacia->m_cargar_detalle_apertura_caja($allInputs['paginate'], $allInputs['datos']); 
		$totalRows = $this->model_caja_farmacia->m_count_sum_detalle_apertura_caja($allInputs['paginate'], $allInputs['datos']); 
		// var_dump($totalRows); exit();
		$arrListado = array();
		// $arrSumatoria = array( 
		// 	'sumCantNC'=> null,
		// 	'sumCantA'=> null,
		// 	'sumCantV'=> null,
		// 	'sumTotalV'=> null
		// );
		foreach ($lista as $row) { 
			$objEstado = array();
			if( $row['estado_movimiento'] == 1 ){ // VENDIDO 
				$objEstado['claseIcon'] = 'fa-check';
				$objEstado['claseLabel'] = 'label-success';
				$objEstado['labelText'] = 'VENDIDO';
			}
			if( $row['estado_movimiento'] == 0 ){ // ANULADO 
				$objEstado['claseIcon'] = 'fa-ban';
				$objEstado['claseLabel'] = 'label-danger';
				$objEstado['labelText'] = 'ANULADO';
			}
			array_push($arrListado, 
				array(
					'id' => $row['idmovimiento'],
					'orden' => $row['orden_venta'],
					'ticket' => $row['ticket_venta'],
					'idtipodocumento' => $row['idtipodocumento'],
					'tipodocumento' => $row['descripcion_td'],
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'idcliente' => $row['idcliente'],
					'cliente' => $row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'],
					'numero_documento' => $row['num_documento'],
					'idsede' => $row['idsede'],
					'sede' => $row['sede'],
					'idempresa' => $row['idempresaadmin'],
					'empresa_admin' => $row['empresa_admin'], // EMPRESA ADMIN 
					'idmediopago' => $row['idmediopago'],
					'medio' => $row['descripcion_med'],
					'idcaja' => $row['idcaja'],
					'caja_descripcion' => $row['descripcion'],
					'idcajamaster' => $row['idcajamaster'],
					'caja_master_descripcion' => $row['descripcion_caja'],
					'serie_caja' => $row['serie_caja'],
					'numero_caja' => $row['numero_caja'],
					'idusuario' => $row['idusers'],
					'username' => strtoupper($row['username']),
					'subtotal' => $row['sub_total'],
					'igv' => $row['total_igv'],
					'total' => $row['total_a_pagar'], 
					'total_a_pagar_format' => $row['total_a_pagar_format'],
					'estado' =>  $objEstado
				)
			);
			// $arrSumatoria['sumCantNC'] += $row['cantidad_nota_credito'];
			// $arrSumatoria['sumCantA'] += $row['cantidad_anulado'];
			// $arrSumatoria['sumCantV'] += $row['cantidad_venta'];
			// $arrSumatoria['sumTotalV'] += $row['total_a_pagar_format'];
		} 
    	$arrData['sumTotalV'] = number_format(empty($totalRows['total_importe']) ? 0 : $totalRows['total_importe'],2);
    	$arrData['sumCantV'] = empty($totalRows['cantidad_venta']) ? 0 : $totalRows['cantidad_venta'];
    	$arrData['sumCantA'] = empty($totalRows['cantidad_anulado']) ? 0 : $totalRows['cantidad_anulado'];
    	$arrData['sumCantNC'] = empty($totalRows['cantidad_salidas']) ? 0 : $totalRows['cantidad_salidas'];
    	$arrData['sumTotalNC'] = number_format(empty($totalRows['total_salidas']) ? 0 : $totalRows['total_salidas'],2);
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['datos'] = $arrListado;
    	// $arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function get_caja_actual_de_usuario()
	{
		$arrData['message'] = '';
    	$arrData['flag'] = 0;
    	$datos['idmodulo'] = 1;
    	$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($datos['idmodulo']);
		if( !empty($fCaja) ){ 
			$arrData['datos'] = $fCaja;
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function get_farmacia_caja_actual_de_usuario()
	{
		$arrData['message'] = '';
    	$arrData['flag'] = 0; 
    	$datos['idmodulo'] = 3;
    	$fCaja = $this->model_caja->m_cargar_caja_actual_de_usuario($datos['idmodulo']);
		if( !empty($fCaja) ){ 
			$arrData['datos'] = $fCaja;
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_formulario()
	{
		$this->load->view('caja/caja_formView');
	}
	public function ver_popup_abrir_caja()
	{
		$this->load->view('caja/aperturaCajaFormView');
	}
	public function registrar()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit(); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$this->db->trans_start();
		if($this->model_caja->m_registrar($allInputs)){ 
			foreach ($allInputs['detalle'] as $key => $row) { 
				$row['idcajamaster'] = GetLastId('idcajamaster','caja_master');
				$this->model_caja->m_registrar_documento_caja($row);
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
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_caja->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
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
			if( $this->model_caja->m_anular($row['id']) ){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;
			}
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_numero_serie()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$allInputs['searchColumn'] = 'descripcion_td';
    	$allInputs['searchText'] = $allInputs['tipodocumento'];
    	$fTippoDoc = $this->model_tipo_documento->m_cargar_este_tipo_documento_venta($allInputs);
    	// var_dump($fTippoDoc); exit(); 
    	$allInputs['idtipodocumento'] = $fTippoDoc['idtipodocumento'];
		if($this->model_caja->m_editar_numero_serie($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// public function FunctionName($value='')
	// {
		
	// }
}