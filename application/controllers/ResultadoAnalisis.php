<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ResultadoAnalisis extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('model_resultadoAnalisis', 'model_atencionMuestra','model_venta','model_atencion_medica'));
		$this->load->helper(array('otros_helper','fechas_helper'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function listarPacientesParaResultados(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrFilter = array();
		$arrFilter['searchTipo'] = FALSE;

		if( $allInputs['tipoBusqueda'] === 'PNO'){
			if ( $this->sessionHospital['id_empresa_admin'] == 38 ){
				$orden_lab = 'LU-' . date('dmy',strtotime($allInputs['fechaexamen'])) . '-' . str_pad($allInputs['numeroOrden'], 3, '0', STR_PAD_LEFT);
			}else{
				$orden_lab = date('dmy',strtotime($allInputs['fechaexamen'])) . '-' . str_pad($allInputs['numeroOrden'], 3, '0', STR_PAD_LEFT);
			}
			
			$arrFilter['searchColumn'] = 'mp.orden_lab';
			$arrFilter['searchText'] = $orden_lab; 
		} 
		elseif( $allInputs['tipoBusqueda'] === 'PH' ){ 
			$arrFilter['searchColumn'] = 'h.idhistoria';
			$arrFilter['searchText'] = $allInputs['numeroHistoria']; 
		}
		elseif( $allInputs['tipoBusqueda'] === 'PP' ){ 
			$arrFilter['searchColumn'] = "UPPER(CONCAT(cl.nombres,' ',cl.apellido_paterno,' ',cl.apellido_materno))";
			$arrFilter['searchText'] = $allInputs['paciente']; 
			$arrFilter['searchTipo'] = $allInputs['tipoBusqueda'];
		}
		// var_dump($orden_lab); exit();
		// VALIDACIONES
		$arrOrden = $this->model_atencionMuestra->m_verificar_si_existe_orden_laboratorio($orden_lab);
		if( empty($arrOrden) ){
			$arrData['message'] = 'La orden de laboratorio no existe. VERIFIQUE LA EMPRESA/SEDE';
	    	$arrData['flag'] = 0;
	    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}
		if ( $arrOrden['estado_mp'] == 0){
			$arrData['message'] = 'La orden de laboratorio está anulada';
	    	$arrData['flag'] = 0;
	    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}
		// var_dump($arrOrden['orden_venta']); var_dump($orden_lab); exit();
		// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 
    	$fValidateNC = $this->model_venta->m_validar_venta_con_nota_credito($arrOrden['orden_venta']);
    	if( !empty($fValidateNC) ){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas. Contacte con el area de Sistemas.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		// VERIFICAR ESTRUCTURA  m_cargar_parametros_analisis_por_orden
		$listaAnalisis = $this->model_resultadoAnalisis->m_cargar_analisis_por_orden($orden_lab);
		
		// LURIN: 9 - 38 
		// VES: 8 - 39 
		$idsedeempresaadmin = NULL;
		if( $this->sessionHospital['id_empresa_admin'] == 38 ){
			$idsedeempresaadmin = 9;
		}else if( $this->sessionHospital['id_empresa_admin'] == 39 ){
			$idsedeempresaadmin = 8;
		}
		foreach ($listaAnalisis as $key => $row) {
			$arrEstructura = $this->model_resultadoAnalisis->m_verificar_estructura($row['idanalisis'],$idsedeempresaadmin);
			if( empty( $arrEstructura ) ){
				$arrData['message'] = 'El Analisis '. $row['descripcion_anal'] . ' no tiene estructura.';
		    	$arrData['flag'] = 0;
		    	$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			    return;
			}
		}

		$lista_combo = array();
		$lista_cbo = array();
		$lista = $this->model_resultadoAnalisis->m_cargar_parametros_analisis_por_orden($arrFilter,$idsedeempresaadmin);
		
		if (!empty($lista)){
			$arrPrincipal = array( 
				'idcliente'=> null,
				'idhistoria'=> null,
				'idmuestrapaciente' => null,
				'orden_lab' => null,
				'orden_venta' => null,
				'idventa' => null,
				'ticket' => null,
				'paciente' => null,
				'edad' => null,
				'sexo' => null,
				'fecha_muestra'=> null,
				'secciones' => array()
			);
			/*CAB. PACIENTES - SECCIONES*/ 
			$pacienteObtenido = FALSE;
			// $anios = 0;
			// $meses = 0;
			// $dias  = 0;
			$arrEdad = array();
			foreach ($lista as $key => $row) { 
				if( $pacienteObtenido === FALSE ){
					$arrPrincipal['idcliente'] = $row['idcliente'];
					$arrPrincipal['idhistoria'] = $row['idhistoria'];
					$arrPrincipal['idmuestrapaciente'] = $row['idmuestrapaciente'];
					$arrPrincipal['orden_lab'] = $row['orden_lab'];
					$arrPrincipal['orden_venta'] = $row['orden_venta'];
					$arrPrincipal['idventa'] = $row['idventa'];
					$arrPrincipal['ticket'] = $row['ticket_venta'];
					$arrPrincipal['paciente'] = $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombres'];
					//$arrPrincipal['edad'] = $row['edad'].' años';
					// $arrPrincipal['edad'] = devolverEdadDetalle($row['fecha_nacimiento']);
					$arrPrincipal['edad'] = devolverEdadAtencion($row['fecha_nacimiento'],$row['fecha_recepcion']);
					$arrPrincipal['fecha_muestra'] = $row['fecha_recepcion'];
					$arrPrincipal['sexo'] = strtoupper($row['sexo']);
					$pacienteObtenido = TRUE;
					$arrEdad = devolverEdadArray($row['fecha_nacimiento'],$row['fecha_recepcion']);
					// var_dump($row['fecha_nacimiento']);
					// var_dump($row['fecha_recepcion']);
				}
				$arrAuxSeccion = array(
					'idseccion'=> $row['idseccion'],
					'seccion'=> $row['seccion'],
					'seleccionado' => FALSE,
					'analisis'=> array()
				); 
				$arrPrincipal['secciones'][$row['idseccion']] = $arrAuxSeccion;
			}
			// var_dump($arrEdad['y']); exit();
			/* ANALISIS */
			foreach ($lista as $key => $row) {
				$arrAuxAnalisis = array(
					'idanalisis'=> $row['idanalisis'],
					'descripcion_anal' => $row['descripcion_anal'],
					'idanalisispaciente' => $row['idanalisispaciente'],
					'iddetalle' => $row['iddetalle'],
					'producto' => $row['producto'],
					'paciente_atendido_det' => $row['paciente_atendido_det'],
					'cantidad' => $row['cantidad'],
					'numero_impresiones' => $row['numero_impresiones'],
					'metodo' => $row['metodo'],
					'seleccionado' => FALSE,
					'estado_ap' => $row['estado_ap'],
					'fecha_resultado' => $row['fecha_resultado'],
					'parametros'=> array()
				); 
				$arrPrincipal['secciones'][$row['idseccion']]['analisis'][$row['idanalisispaciente']] = $arrAuxAnalisis;
			}

			/* PARAMETROS */
			foreach ($lista as $key => $row) {
				if($row['combo'] == 1){
					switch ($row['idparametro']) {
						case '528': // RESULTADO
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_parasito_heces_cbo();
							break;
						case '472': // PARASITOS EN HECES
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_parasito_heces_cbo();
							break;
						default:
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_lista_combo($row['nombre_combo']);
							break;
					};
					
					$lista_cbo[0] = array('id' => '--Seleccione Opcion--', 'descripcion' => '--Seleccione Opción--');
					foreach ($lista_combo as $key => $value) {
						array_push($lista_cbo, array(
							'id' => $value['elemento_combo'],
							'descripcion' => $value['elemento_combo']
							)
						);
					}
				}
				
				$arrAuxParametros = array(
					'idanalisisparametro'=> $row['idanalisisparametro'],
					'idparametro'=> $row['idparametro'],
					'parametro'=> $row['parametro'],
					'separador' => $row['separador'],
					'combo' => $row['combo'],
					'lista_combo' => $lista_cbo,
					'orden_parametro' => $row['orden_parametro'],
					'subparametros'=> array()
				); 
				if( empty($row['idsubparametro']) ){
					/* Obtencion del valor normal de acuerdo a la edad y sexo */
					if($row['valor_ambos'] == 0 && $row['sexo'] == 'F'){
						$valor_sexo = 'm'; // mujeres
					}else{
						$valor_sexo = 'h'; // hombres
					}
					$arrJson = json_decode(trim($row['valor_json']),true);
					if(count($arrJson) >= 1 ){
						// if($row['idparametro'] == '96'){ // hematocrito

						// 	var_dump('BILI '); var_dump($arrJson); exit();
						// }
						$valornormal = NULL;
						foreach ($arrJson as $rowJson) {
							if( (int)$arrEdad['y'] > 0 && $rowJson['tipo_rango'] == '3' ){ // años
								// menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['y'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 1');
								}
								// entre min y max
								elseif(  $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['y'] <= $rowJson['max_rango'] && $arrEdad['y'] >= $rowJson['min_rango'] ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 2');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['y'] >= $rowJson['min_rango']  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 3');
								}
							}elseif( (int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] > 0 && $rowJson['tipo_rango'] == '2' ){ // meses
								// menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['m'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 4');
								}
 								// entre min y max
								elseif( $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['m'] <= $rowJson['max_rango'] && $arrEdad['m'] >= $rowJson['min_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 5');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['m'] >= $rowJson['min_rango']  ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 6');
								}
							}elseif( (int)$arrEdad['y'] == 0 &&(int)$arrEdad['m'] == 0 &&(int)$arrEdad['d'] >= 0 && $rowJson['tipo_rango'] == '1' ){ // dias
								 // menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['d'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
									$valornormal .= '<br>';
									// var_dump('seccion: 7');
								}
								// entre min y max
								elseif( $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['d'] <= $rowJson['max_rango'] && $arrEdad['d'] >= $rowJson['min_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 8');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['d'] >= $rowJson['min_rango']  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 9');
								}
							}elseif( $rowJson['tipo_rango'] == '2' && $rowJson['min_rango'] == null ){ // menores de xx meses, debe incluir a los que tienen dias
								if( (int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] == 0 && (int)$arrEdad['d'] >= 0  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 10');
									// var_dump($rowJson); exit();
								}
							}elseif( $rowJson['tipo_rango'] == '3' && $rowJson['min_rango'] == null ){ // menores de xx años, debe incluir a los que tienen meses o dias
								if( ((int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] == 0 && (int)$arrEdad['d'] >= 0) ||
								  	((int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] > 0) ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
								$valornormal .= '<br>';
								// var_dump('seccion: 11');
								}
							}elseif($rowJson['tipo_rango'] == NULL){
								$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
								$valornormal .= empty($valornormal)? NULL : '<br>';
								// var_dump('seccion: 12');
							}

							// if( $rowJson['min_rango'] == null && $edadSegunTipo <= $rowJson['max_rango'] ){ // menores de max
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }elseif( $edadSegunTipo <= $rowJson['max_rango'] && $edadSegunTipo >= $rowJson['min_rango'] ){ // entre min y max
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }elseif(  $rowJson['max_rango'] == null && $edadSegunTipo >= $rowJson['min_rango']  ){ // mayores de min
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }
						}
					}else{
						$valornormal = $valor_sexo == 'm'? $row['valor_normal_m'] : $row['valor_normal_h'];
						// if($row['valor_ambos'] == 0 && $row['sexo'] == 'F'){
						// 	$valornormal = $row['valor_normal_m'];
						// }else{
						// 	$valornormal = $row['valor_normal_h'];
						// }
					}
					// var_dump($valornormal); exit();
					$arrAuxParametros['valor_normal'] = $valornormal;
					$arrAuxParametros['valor_ambos'] = $row['valor_ambos'];
					$arrAuxParametros['iddetalleresultado'] = $row['iddetalleresultado'];
					$arrAuxParametros['autocalculable'] = $row['autocalculable'];
					$arrAuxParametros['formula'] = $row['formula'];
					$arrAuxParametros['requiere_texto_adicional'] = $row['requiere_texto_adicional'];
					$arrAuxParametros['texto_adicional'] = $row['texto_adicional'];
					$arrAuxParametros['resultado'] = ($row['combo'] == 1 && $row['resultado'] == '')? '--Seleccione Opcion--': $row['resultado'];
					unset($arrAuxParametros['subparametros']);
				}	
				$arrPrincipal['secciones'][$row['idseccion']]['analisis'][$row['idanalisispaciente']]['parametros'][$row['idparametro']] = $arrAuxParametros; 
				$lista_cbo = array();
			}

			/* SUBPARAMETROS */
			foreach ($lista as $key => $row) {
				if(!empty($row['subcombo']) && $row['subcombo'] == 1){
					switch ($row['idsubparametro']) {
						
						case '528':
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_parasito_heces_cbo();
							break;
						case '472':
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_parasito_heces_cbo();
							break;
						default:
							$lista_combo = $this->model_resultadoAnalisis->m_cargar_lista_combo($row['nombre_subcombo']);
							break;
					}
					$lista_cbo[0] = array('id' => '--Seleccione Opcion--', 'descripcion' => '--Seleccione Opción--');
					foreach ($lista_combo as $key => $value) {
						array_push($lista_cbo, array(
							'id' => $value['elemento_combo'],
							'descripcion' => $value['elemento_combo']
							)
						);
					}
				}
				$arrAuxParametros = array(
					'idanalisisparametro'=> $row['idanalisisparametro'],
					'idsubparametro'=> $row['idsubparametro'],
					'subparametro'=> $row['subparametro'],
					'subcombo' => $row['subcombo'],
					'lista_combo' => $lista_cbo,
					'orden_subparametro' => $row['orden_subparametro']
				); 
				if( !empty($row['idsubparametro']) ){
					/* Obtencion del valor normal de acuerdo a la edad y sexo */
					if($row['valor_ambos'] == 0 && $row['sexo'] == 'F'){
						$valor_sexo = 'm'; // mujeres
					}else{
						$valor_sexo = 'h'; // hombres
					}
					$arrJson = json_decode(trim($row['valor_json']),true);
					if(count($arrJson) >= 1 ){
						// if($row['idsubparametro'] == '96'){ // hematocrito

						// 	var_dump('BILI '); var_dump($arrJson); exit();
						// }
						$valornormal = NULL;
						foreach ($arrJson as $rowJson) {
							if( (int)$arrEdad['y'] > 0 && $rowJson['tipo_rango'] == '3' ){ // años
								// menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['y'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 1');
								}
								// entre min y max
								elseif(  $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['y'] <= $rowJson['max_rango'] && $arrEdad['y'] >= $rowJson['min_rango'] ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 2');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['y'] >= $rowJson['min_rango']  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 3');
								}
							}elseif( (int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] > 0 && $rowJson['tipo_rango'] == '2' ){ // meses
								// menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['m'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 4');
								}
 								// entre min y max
								elseif( $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['m'] <= $rowJson['max_rango'] && $arrEdad['m'] >= $rowJson['min_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 5');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['m'] >= $rowJson['min_rango']  ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 6');
								}
							}elseif( (int)$arrEdad['y'] == 0 &&(int)$arrEdad['m'] == 0 &&(int)$arrEdad['d'] >= 0 && $rowJson['tipo_rango'] == '1' ){ // dias
								 // menores de max
								if( $rowJson['min_rango'] == null && $arrEdad['d'] < $rowJson['max_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
									$valornormal .= '<br>';
									// var_dump('seccion: 7');
								}
								// entre min y max
								elseif( $rowJson['min_rango'] != null &&  $rowJson['max_rango'] != null &&
									$arrEdad['d'] <= $rowJson['max_rango'] && $arrEdad['d'] >= $rowJson['min_rango'] ){
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 8');
								}
								// mayores de min
								elseif(  $rowJson['max_rango'] == null && $arrEdad['d'] >= $rowJson['min_rango']  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 9');
								}
							}elseif( $rowJson['tipo_rango'] == '2' && $rowJson['min_rango'] == null ){ // menores de xx meses, debe incluir a los que tienen dias
								if( (int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] == 0 && (int)$arrEdad['d'] >= 0  ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
									$valornormal .= '<br>';
									// var_dump('seccion: 10');
									// var_dump($rowJson); exit();
								}
							}elseif( $rowJson['tipo_rango'] == '3' && $rowJson['min_rango'] == null ){ // menores de xx años, debe incluir a los que tienen meses o dias
								if( ((int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] == 0 && (int)$arrEdad['d'] >= 0) ||
								  	((int)$arrEdad['y'] == 0 && (int)$arrEdad['m'] > 0) ){ 
									$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
								$valornormal .= '<br>';
								// var_dump('seccion: 11');
								}
							}elseif($rowJson['tipo_rango'] == NULL){
								$valornormal .= $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'];
								$valornormal .= empty($valornormal)? NULL : '<br>';
								// var_dump('seccion: 12');
							}

							// if( $rowJson['min_rango'] == null && $edadSegunTipo <= $rowJson['max_rango'] ){ // menores de max
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }elseif( $edadSegunTipo <= $rowJson['max_rango'] && $edadSegunTipo >= $rowJson['min_rango'] ){ // entre min y max
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }elseif(  $rowJson['max_rango'] == null && $edadSegunTipo >= $rowJson['min_rango']  ){ // mayores de min
							// 	$valornormal = $valor_sexo == 'm'? $rowJson['valor_etario_m'] : $rowJson['valor_etario_h'] ;
							// }
						}
						
					}else{
						$valornormal = $valor_sexo == 'm'? $row['valor_normal_m'] : $row['valor_normal_h'];
					}
					// if(count($row['valor_json']) >= 1 ){
					// 	$valornormal = 'json';
					// }else{
					// 	if($row['valor_ambos'] == 0 && $row['sexo'] == 'F'){
					// 		$valornormal = $row['valor_normal_m'];
					// 	}else{
					// 		$valornormal = $row['valor_normal_h'];
					// 	}
					// }
					$arrAuxParametros['valor_normal'] = $valornormal;
					$arrAuxParametros['valor_ambos'] = $row['valor_ambos'];
					$arrAuxParametros['iddetalleresultado'] = $row['iddetalleresultado'];
					$arrAuxParametros['autocalculable'] = $row['autocalculable'];
					$arrAuxParametros['formula'] = $row['formula'];
					$arrAuxParametros['requiere_texto_adicional'] = $row['requiere_texto_adicional'];
					$arrAuxParametros['texto_adicional'] = $row['texto_adicional'];
					$arrAuxParametros['resultado'] = ($row['subcombo'] == 1 && $row['resultado'] == '')? '--Seleccione Opcion--': $row['resultado']; 

					$arrPrincipal['secciones'][$row['idseccion']]['analisis'][$row['idanalisispaciente']]['parametros'][$row['idparametro']]['subparametros'][$row['idsubparametro']] = $arrAuxParametros; 
				}
				$lista_cbo = array();	
			}
			 // exit();
			// var_dump("<pre>",$arrPrincipal); exit(); 
			$arrPaciente = $arrPrincipal;
			unset($arrPaciente['secciones']);

			$arrAnalisis = array(); // para mostrar en la grilla
			foreach ($arrPrincipal['secciones'] as $key => $value) {
				foreach ($arrPrincipal['secciones'][$key]['analisis'] as $key3 => $row) {
					if( $row['estado_ap'] == 0 ){
						$estado = 'ANULADO';
						$clase = 'label-default';
					}
					if( $row['estado_ap'] == 1 ){
						$estado = 'SIN RESULTADOS';
						$clase = 'label-default';
					}
					if( $row['estado_ap'] == 2 ){
						$estado = 'CON RESULTADOS';
						$clase = 'label-info';
					}
					if( $row['estado_ap'] == 3 ){
						$estado = 'APROBADO';
						$clase = 'label-primary';
					}
					if( $row['estado_ap'] == 4 ){
						$estado = 'ENTREGADO';
						$clase = 'label-success';
					}
					array_push($arrAnalisis, 
						array (
							'idanalisis' => $row['idanalisis'],
							'descripcion_anal' => $row['descripcion_anal'],
							'producto' => $row['producto'],
							'idseccion' => $value['idseccion'],
							'seccion' => $value['seccion'],
							'idanalisispaciente' => $row['idanalisispaciente'],
							'fecha_resultado' => $row['fecha_resultado'],
							'numero_impresiones' => $row['numero_impresiones'],
							'estado' => array(
								'string' => $estado,
								'clase' =>$clase,
								'bool' =>$row['estado_ap'] //0:anulado; 1:en proceso; 2:terminado; 3:aprobado; 4:entregado
							)
						)
					);	
				}
			}
			// APLICAMOS ARRAY_VALUES PARA REORDENAR LOS INDICES
			$arrPrincipal['secciones'] = array_values($arrPrincipal['secciones']);
			foreach ($arrPrincipal['secciones'] as $key => $row) {
				$arrPrincipal['secciones'][$key]['analisis'] = array_values($arrPrincipal['secciones'][$key]['analisis']); 
				foreach ($arrPrincipal['secciones'][$key]['analisis'] as $key2 => $row2) {
					$arrPrincipal['secciones'][$key]['analisis'][$key2]['parametros'] = array_values($arrPrincipal['secciones'][$key]['analisis'][$key2]['parametros']); 
					foreach ($arrPrincipal['secciones'][$key]['analisis'][$key2]['parametros'] as $key3 => $row3) {
						@$arrPrincipal['secciones'][$key]['analisis'][$key2]['parametros'][$key3]['subparametros'] = array_values($arrPrincipal['secciones'][$key]['analisis'][$key2]['parametros'][$key3]['subparametros'] ); 
					}
				}
			}

			$arrData['datos'] = $arrPaciente;
			$arrData['arrSecciones'] = $arrPrincipal['secciones'];
			$arrData['arrAnalisis'] = $arrAnalisis; // para la grilla
	    	$arrData['message'] = 'Paciente encontrado.';
	    	$arrData['flag'] = 1;
		}else{
			$arrData['datos'] = null;
			$arrData['arrSecciones'] = null;
	    	$arrData['message'] = 'El Paciente no tiene Exámenes para ingresar resultados';
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// ==============================================================================================================
	public function listar_pacientes_laboratorio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_resultadoAnalisis->m_cargar_pacientes_laboratorio($paramPaginate);
		$totalRows = $this->model_resultadoAnalisis->m_count_pacientes_laboratorio($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'orden_lab' => $row['orden_lab'],
					'idhistoria' => trim($row['idhistoria']),
					'nombres' => $row['nombres'],
					//'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'num_documento' => $row['num_documento'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					// 'edad' => devolverEdadDetalle($row['fecha_nacimiento']),
					'edad' => devolverEdad($row['fecha_nacimiento']),

					'sexo' => strtoupper($row['sexo']),
					
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

	public function imprimirResultadoSelPaciente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$hoy = date('d/m/Y');
		
		$arrData['flag'] = 1;
    	$arrData['html'] = '';
    	$htmlData = '<div class="caja"><table style="width:100%;" width="600">';
    	$fecha = $allInputs['arrSecciones'][0]['analisis'][0]['fecha_resultado'];
    	$htmlData .= '<tr> <td class="det"> Nombres: </td> <td colspan="3" style="font-size:1em;font-weight:600; width:400px"> '.$allInputs['paciente'].' </td> <td class="det"> Fecha: </td><td> ' . date('d/m/Y',strtotime("$fecha")) . ' </td></tr>';

		$htmlData .= '<tr> <td class="det"> Edad: </td> <td style="width:100px"> '.$allInputs['edad'].' años </td> <td class="det" style="width:100px;"> Sexo: </td> <td> '.$allInputs['sexo'].' </td> <td class="det"> Nº Exam: </td> <td> '.$allInputs['orden_lab'].' </td></tr>';

		$htmlData .= '<tr> <td class="det"> Médico: </td> <td colspan="3" >  </td> <td class="det" style="min-width:100px;"> Hist. Clín.: </td> <td> '.$allInputs['idhistoria'].' </td></tr>';
		
		$htmlData .= '<tr> <td class="det" style="width:100px"> Procedencia: </td> <td colspan="3" > HOSPITAL VILLA SALUD </td> ';
    	$htmlData .= '</table></div>';
    	$htmlData .= '<div class="caja"><table style="width:100%;" width="600" class="tableTitulo" border="1">';
    	$htmlData .= '<thead><tr style="font-weight: 600"> <th class="col_examen" style="text-align:center;"> Examen </th> <th class="col_resultado" style="text-align:left;">  Resultado </th> <th class="col_valor_normal" style="text-align:left;">  Valor Normal </th> <th class="" style="text-align:left;">  Método </th></tr></thead>';

    	$htmlData .= '</table></div>';

    	$htmlData .= '<table style="width:100%;" width="600" class="tableDetalleAnal" border="1">';
    	$secciones = $allInputs['arrSecciones'];
    	// var_dump($secciones); exit();
		$arrAnalisis = array();
		if (!empty($secciones)){
			foreach ($secciones as $seccion) {
				if($seccion['seleccionado']){
					$htmlData .= '<tr style="text-align:center;font-size:1em;"><th colspan="4" style="height: 40px;font-weight:bold;">' . $seccion['seccion'] .'</td></tr>';
					foreach ($seccion['analisis'] as $analisis) {
						if($analisis['seleccionado']){
							$htmlData .= '<tr> <td style="font-size:1em;height:25px;" colspan="4">';
							$htmlData .= '<span class="anal">[] ' . $analisis['descripcion_anal']. '</span>';
							$htmlData .= '</td>';
							$htmlData .= '</tr>';
							foreach ($analisis['parametros'] as $keyParam => $parametro) { 
								if(@trim($parametro['resultado']) == '--Seleccione Opcion--'){
									@$parametro['resultado'] = '';
								}
								$htmlData .= '<tr> <td class="col_examen" style="padding-left: 20px;font-weight: bold;">'. $parametro['parametro'] .' </td> <td class="col_resultado"> ' . @$parametro['resultado'] .' </td> <td class="col_valor_normal">'. @$parametro['valor_normal'] .' </td> <td class="col_metodo">  '. $analisis['metodo'] .' </td></tr>';

								if( !empty($parametro['subparametros']) ){
									if( $parametro['idparametro'] == 57 ){
										$arrGroupByRes = array();
										foreach ($parametro['subparametros'] as $keySP => $rowSP) { 

											if(  trim($rowSP['resultado']) != '--Seleccione Opcion--' ){
												$arrGroupByRes[$rowSP['resultado']] = array(
													// 'resultado'=> $rowSP['resultado'],
													'detalle'=> array() 
												);
											}
										}
										foreach ($parametro['subparametros'] as $keySP => $rowSP) { 
											if(  trim($rowSP['resultado']) != '--Seleccione Opcion--' ){
												$arrGroupByRes[$rowSP['resultado']]['detalle'][] = array( 
													//'idparametro' => $rowSP['idparametro'],
													'parametro' => $rowSP['subparametro']
												);
											}
										}
										$htmlData .= '<tr><td colspan="4">';
										$htmlData .= '<table>';
										$htmlData .= '<tbody>';
										foreach ($arrGroupByRes as $keyRes => $rowRes) { 
											$htmlData .= '<td style="vertical-align: top; border: 1px solid rgb(204, 204, 204); width: 33%;font-size: 0.7em;"> <div style="text-align:center;font-weight: 600;">'.$keyRes.'</div>' ; 
											foreach ($rowRes['detalle'] as $keyDet => $rowDet) { 
												$htmlData .= '<ul>';
												$htmlData .= '<li>'.$rowDet['parametro'].'</li>'; 
												$htmlData .= '</ul>';
											} 
											$htmlData .= '</td>'; 
										}
										$htmlData .= '</tbody>';
										
										$htmlData .= '</table>';
										$htmlData .= '</td></tr>';
									}else{
										foreach ($parametro['subparametros'] as $keySubParam => $subparametro) { 
											if( trim($subparametro['resultado']) !== '--Seleccione Opcion--' ){ 
												$htmlData .= '<tr> <td  class="col_examen" style="padding-left: 40px;">- '. $subparametro['subparametro'] .' </td> <td class="col_resultado"> ' . @$subparametro['resultado'] .' </td> <td class="col_valor_normal">'. @$subparametro['valor_normal'] .' </td> <td class="col_metodo">  '. $analisis['metodo'] .' </td></tr>';
											}
										}
									}
									
								}
								
							}
						}
					}	
				}
			}
			
			$htmlData .= '</table>';
			//var_dump("<pre>",$arrAnalisis); exit(); 
			$arrData['html'] = $htmlData;
	    	$arrData['flag'] = 1;
		}else{
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listarAnalisisParaResultado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_resultadoAnalisis->m_cargar_analisis_paciente($paramPaginate);
		$totalRows = $this->model_resultadoAnalisis->m_count_analisis_paciente($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['estado_ap'] == 1 ){
				$estado = 'EN PROCESO';
				$clase = 'label-default';
			}
			if( $row['estado_ap'] == 2 ){
				$estado = 'TERMINADO';
				$clase = 'label-info';
			}
			if( $row['estado_ap'] == 3 ){
				$estado = 'APROBADO';
				$clase = 'label-primary';
			}
			if( $row['estado_ap'] == 4 ){
				$estado = 'ENTREGADO';
				$clase = 'label-success';
			}
			
			array_push($arrListado,
				array(
					'id' => $row['idanalisispaciente'],
					'idanalisis' => $row['idanalisis'],
					'idmuestrapaciente' => $row['idmuestrapaciente'],
					'descripcion_anal' => $row['descripcion_anal'],
					'seccion' => $row['seccion'],
					'idhistoria' => $row['idhistoria'],
					'idcliente' => $row['idcliente'],
					'paciente' => $row['nombres'] . ', ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'],
					'edad' => $row['edad'],
					'sexo' => strtoupper($row['sexo']),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_ap'] //0:anulado; 1:en proceso; 2:terminado; 3:aprobado; 4:entregado
					)
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
	public function listarParametrosAnalisis(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_resultadoAnalisis->m_cargar_parametros_analisis($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			if($row['valor_ambos'] == 0 && $allInputs['sexo'] == 'F'){
				$valornormal = $row['valor_normal_m'];
			}else{
				$valornormal = $row['valor_normal_h'];
			}
			array_push($arrListado,
				array(
					'idanalisisparametro' => $row['idanalisisparametro'],
					'idparametro' => $row['idparametro'],
					'idanalisis' => $row['idanalisis'],
					'descripcion_par' => $row['descripcion_par'],
					'separador' => $row['separador'],
					'valor_normal' => $valornormal,
					'valor_ambos' => $row['valor_ambos'],
					'metodo' => $row['metodo']
					
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
	public function listarParametrosAnalisisRes(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$lista = $this->model_resultadoAnalisis->m_cargar_parametros_analisis_res($allInputs);
		//var_dump($lista); exit();
		$arrListado = array();
		foreach ($lista as $row) {
			if($row['valor_ambos'] == 0 && $allInputs['sexo'] == 'F'){
				$valornormal = $row['valor_normal_m'];
			}else{
				$valornormal = $row['valor_normal_h'];
			}
			array_push($arrListado,
				array(
					'idanalisisparametro' => $row['idanalisisparametro'],
					'idparametro' => $row['idparametro'],
					'idanalisis' => $row['idanalisis'],
					'descripcion_par' => $row['descripcion_par'],
					'separador' => $row['separador'],
					'valor_normal' => $valornormal,
					'valor_ambos' => $row['valor_ambos'],
					'resultado' => $row['resultado'],
					'metodo' => $row['metodo']
					
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
	public function listarPacientesAutocomplete() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = array(); 
		if( isset($allInputs['searchText']) ){
			$lista = $this->model_resultadoAnalisis->m_cargar_pacientes_autocomplete($allInputs); 
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcliente'], 
					'descripcion' => strtoupper($row['paciente']) 
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
	public function listarPacientesResAutocomplete() 
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = array(); 
		if( isset($allInputs['searchText']) ){
			$lista = $this->model_resultadoAnalisis->m_cargar_pacientes_res_autocomplete($allInputs); 
		}
		$arrListado = array();
		foreach ($lista as $row) { 
			array_push($arrListado, 
				array( 
					'id' => $row['idcliente'], 
					'descripcion' => strtoupper($row['paciente']) 
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
	public function ver_popup_formulario(){
		$this->load->view('analisis/generarResultado_formView');
	}
	public function ver_popup_detalle_resumen_analisis(){
		$this->load->view('laboratorio/detalle_resumen_analisis_formView');
	}
	public function registrarResultadosPaciente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	$arrData['reg'] = 0;
    	$this->db->trans_start();
    	foreach ($allInputs as $secciones) {
    		foreach ($secciones['analisis'] as $analisis) {
    			if($analisis['seleccionado']){
	    			foreach ($analisis['parametros'] as $parametro) {
		    			if($parametro['separador'] == 0 && isset($parametro['resultado'])){
		    				if($parametro['resultado'] != '--Seleccione Opcion--' && $parametro['iddetalleresultado'] == NULL)
		    				{
			    				if($this->model_resultadoAnalisis->m_registrar_resultado($parametro,$analisis)){
									$arrData['message'] = 'Se registraron los datos correctamente';
						    		$arrData['flag'] = 1;
						    		$arrData['reg'] = 1;
								}else{
									$arrData['message'] = 'Error al registrar los datos';
					    			$arrData['flag'] = 0;
					    			break;
								}
		    				}elseif($parametro['iddetalleresultado'] != NULL){
		    					if($this->model_resultadoAnalisis->m_actualizar_resultado($parametro)){
									$arrData['message'] = 'Se actualizaron los datos correctamente';
						    		$arrData['flag'] = 1;
								}else{
									$arrData['message'] = 'Error al actualizar los datos';
					    			$arrData['flag'] = 0;
					    			break;
								}
		    				}
		    			}
		    			elseif($parametro['separador'] == 1 && isset($parametro['subparametros']) ){
			    			foreach ($parametro['subparametros'] as $key => $subparametro) {
				    			if($subparametro['resultado'] != NULL && $subparametro['iddetalleresultado'] == NULL){
				    				if($subparametro['resultado'] != '--Seleccione Opcion--'){
					    				if($this->model_resultadoAnalisis->m_registrar_resultado($subparametro,$analisis)){
											$arrData['message'] = 'Se registraron los datos correctamente';
								    		$arrData['flag'] = 1;
								    		$arrData['reg'] = 1;
										}else{
											$arrData['message'] = 'Error al registrar los datos';
							    			$arrData['flag'] = 0;
							    			break;
										}
				    				}
				    			}elseif($subparametro['iddetalleresultado'] != NULL){
			    					if($this->model_resultadoAnalisis->m_actualizar_resultado($subparametro)){
										$arrData['message'] = 'Se actualizaron los datos correctamente';
							    		$arrData['flag'] = 1;
									}else{
										$arrData['message'] = 'Error al actualizar los datos';
						    			$arrData['flag'] = 0;
						    			break;
									}
			    				}
			    			}	
		    			}
			    	}
			    	if($arrData['flag'] == 1){
			    		$this->model_resultadoAnalisis->m_actualizar_estado_analisis($analisis);
			    		//$this->model_resultadoAnalisis->m_actualizar_detalle_venta($analisis)
			    	}else break;	
    			}
	    			
    		}
    	}
    	$this->db->trans_complete();
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function registrar_atencion_laboratorio()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al grabar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	//$arrData['idatencionmedica'] = NULL; 
    	$arrDetalle = array();
    	
    	//var_dump($allInputs);exit();
    	foreach ($allInputs['arrSecciones'] as $secciones) {
	    	foreach ($secciones['analisis'] as $key => $value) {
	    		if($value['seleccionado']){
		    		//$allInputs['iddetalle'] = $value['iddetalle'];
			    	if($value['paciente_atendido_det'] == 2){
			    		if($this->model_resultadoAnalisis->m_verificar_detalle($value['iddetalle'])){
							array_push($arrDetalle,  $value['iddetalle']);
			    		}
			    		
			    	}
	    		}
	    	}	
    	}
    	
    	$resultado = array_unique($arrDetalle);
    	$this->db->trans_start();
    	foreach ($resultado as $iddetalle) {
    		//var_dump($iddetalle);
    		$allInputs['iddetalle'] = $iddetalle;
			if($this->model_atencion_medica->m_registrar_atencion_medica_examen_auxiliar($allInputs)){ // REGISTRAR EXAMEN AUXILIAR 
				$arrData['flag'] = 1;
				$arrData['message'] = 'Se grabaron los datos correctamente'; 
				//$allInputs['idatencionmedica'] = GetLastId('idatencionmedica','atencion_medica'); 
				// IMPORTANTE: ACTUALIZAMOS EL CAMPO "paciente_atendido_v"  DE LA TABLA VENTA 
				if($arrData['flag'] === 1) { 
					$this->model_venta->m_actualizar_venta_a_atendido($allInputs['id']); // IDVENTA
					$this->model_venta->m_actualizar_detalle_venta_a_atendido($allInputs['iddetalle']); 
					$this->model_venta->m_actualizar_empresa_especialidad_de_venta($allInputs['id']); /* IMPORTANTE IDVENTA */  
				} 
				//$arrData['idatencionmedica'] = $allInputs['idatencionmedica'];
			}
			
    	}
    	$this->db->trans_complete();
    	
    	


		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	
	public function entregar_resultados()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		//var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al actualizar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	foreach ($allInputs['arrSecciones'] as $secciones) {
    		foreach ($secciones['analisis'] as $analisis) {
    			if($analisis['seleccionado']){
    				if($this->model_resultadoAnalisis->m_actualizar_estado_analisis_a_entregado($analisis)){
						$arrData['message'] = 'Se actualizaron los datos correctamente';
			    		$arrData['flag'] = 1;
					}	
    			}
    		}
    	}

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function actualizar_impresiones()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		//var_dump("<pre>",$allInputs); exit(); 
		$arrData['message'] = 'Error al actualizar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	foreach ($allInputs as $secciones) {
    		foreach ($secciones['analisis'] as $analisis) {
    			if($analisis['seleccionado']){
    				if($this->model_resultadoAnalisis->m_actualizar_numero_impresiones($analisis)){
						$arrData['message'] = 'Se actualizaron los datos correctamente';
			    		$arrData['flag'] = 1;
					}	
    			}
    		}
    	}

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	/* ======================================= */ 
	/*               CONSULTAS                 */ 
	/* ======================================= */ 

	public function listarResumenAnalisis()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump("<pre>",$allInputs); exit();
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_resultadoAnalisis->m_cargar_resumen_analisis($paramPaginate,$paramDatos);
		$totalRows = $this->model_resultadoAnalisis->m_count_resumen_analisis($paramPaginate,$paramDatos);
		// var_dump($lista); exit(); 
		$arrListado = array();
		$sumCountIngresados = 0;
		$sumCountAtendidos = 0;
		$sumCountRestantes = 0;
		$sumSumIngresos = 0;
		$sumCountEntregados = 0;
		foreach ($lista as $row) { 
			// if( $this->sessionHospital['key_group'] === 'key_informes' ){
			// 	$row['sum_ingresos_numeric'] = '-';
			// }
			array_push($arrListado, 
				array( 
					'idanalisis' => $row['idanalisis'],
					'analisis' => $row['descripcion_anal'],
					'seccion' => $row['seccion'],
					'countIngresados' => $row['count_ingresados'],
					'countAtendido' => $row['count_atendido'],
					'countRestante' => $row['count_restante'],
					'countEntregados' => $row['count_entregados']
				)
			); 
			// $sumCountIngresados += $row['count_ingresados'];
			// $sumCountAtendidos += $row['count_atendido'];
			// $sumCountRestantes += $row['count_restante'];
			// $sumCountEntregados += $row['count_entregados'];
			
		}
		//var_dump($totalRows); exit();
		foreach ($totalRows as $rowSum) {
			$sumCountIngresados += $rowSum['count_ingresados'];
			$sumCountAtendidos += $rowSum['count_atendido'];
			$sumCountRestantes += $rowSum['count_restante'];
			$sumCountEntregados += $rowSum['count_entregados'];
		}
		$arrData['countIngresados'] = $sumCountIngresados;
		$arrData['countAtendido'] = $sumCountAtendidos;
		$arrData['countRestante'] = $sumCountRestantes;
		$arrData['countEntregados'] = $sumCountEntregados;

		// $arrData['countIngresados'] = $totalRows['count_ingresados'];
		// $arrData['countAtendido'] = $totalRows['count_atendido'];
		// $arrData['countRestante'] = $totalRows['count_restante'];
		// $arrData['countEntregados'] = $totalRows['count_entregados'];

    	$arrData['datos'] = $arrListado;
    	$arrData['totalRows'] = count($totalRows);
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0; 
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_detalle_resumen_analisis()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$paramDatos['rango'] = $allInputs['rango'];
		
		$lista = $this->model_resultadoAnalisis->m_cargar_detalle_resumen_analisis($paramPaginate,$paramDatos);
		$totalRows = $this->model_resultadoAnalisis->m_count_detalle_resumen_analisis($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			if($row['estado_ap'] == 1){
				$estado = 'Sin Resultados';
				$clase = 'label-default';
			}elseif($row['estado_ap'] == 2){
				$estado = 'Con Resultados';
				$clase = 'label-info';
			}elseif($row['estado_ap'] == 4){
				$estado = 'Entregado';
				$clase = 'label-success';
			}
			array_push($arrListado, 
				array( 
					'orden_lab' => $row['orden_lab'],
					'idhistoria' => $row['idhistoria'],
					'paciente' => $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombres'],
					'fecha_examen' => formatoFechaReporte($row['fecha_examen']),
					'fecha_atencion' => formatoFechaReporte($row['fecha_atencion_det']),
					'fecha_entrega' => formatoFechaReporte($row['fecha_entrega']),
					'estado' => array(
						'string' => $estado,
						'clase' =>$clase,
						'bool' =>$row['estado_ap'] //0:anulado; 1:en proceso; 2:terminado; 3:aprobado; 4:entregado
					)
					
				)
			);
		}
		//var_dump("<pre>",$arrListado); exit();

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
	  /* ************************************** */
	 /*  Resultados desde SIgelab / Sql Server */
	/* ************************************** */
	public function generar_resultados_sqlserver(){
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 256);
	    ini_set('xdebug.var_display_max_data', 1024);
	    $arrData = array();
	    $editado = false;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_resultadoAnalisis->m_generar_resultados_sqlserver($allInputs);
		$arrPrincipal = $allInputs['arrPrincipal'];
		// var_dump($arrPrincipal); exit();
		foreach ($arrPrincipal as $key1 => $value1) {
			if( $value1['seleccionado'] ){
				foreach ($value1['analisis'] as $keyAn => $valueAn) {
					foreach ($lista as $rowRes) {
						if($valueAn['idanalisis'] == trim($rowRes['codigonew']) && $valueAn['seleccionado']){ 
							foreach ($valueAn['parametros'] as $keyPar => $valuePar) {
								if($valuePar['separador'] == 0){
									if( $valuePar['idparametro'] == trim($rowRes['abrv']) ){
										if($arrPrincipal[$key1]['analisis'][$keyAn]['parametros'][$keyPar]['resultado'] != trim($rowRes['resultado'])){
											$arrPrincipal[$key1]['analisis'][$keyAn]['parametros'][$keyPar]['resultado'] = trim($rowRes['resultado']);
											$editado = true;
										}
									}	
								}else{
									foreach ($valuePar['subparametros'] as $keySubPar => $valueSubPar) {
										if( $valueSubPar['idsubparametro'] == trim($rowRes['abrv']) ){
											if($arrPrincipal[$key1]['analisis'][$keyAn]['parametros'][$keyPar]['subparametros'][$keySubPar]['resultado'] != trim($rowRes['resultado'])){
												$arrPrincipal[$key1]['analisis'][$keyAn]['parametros'][$keyPar]['subparametros'][$keySubPar]['resultado'] = trim($rowRes['resultado']);
												$editado = true;
											}
											
										}
									}
								}
							}
						}
					}
					
				}
			}
			
			if( $editado ){
				$arrData['message'] = 'Se generaron resultados';
				$arrData['flag'] = 1;
			}else{
				$arrData['message'] = 'No se obtuvieron resultados';
				$arrData['flag'] = 0;
			}
		}
		// var_dump($arrPrincipal); exit();
    	$arrData['datos'] = $arrPrincipal;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}