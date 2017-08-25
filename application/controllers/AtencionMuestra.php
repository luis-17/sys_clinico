<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionMuestra extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('model_atencionMuestra','model_venta'));
		$this->load->helper(array('otros_helper','fechas_helper'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function obtener_paciente_historia(){
		$allInputs = json_decode(trim(file_get_contents('php://input')),true);
		$lista = $this->model_atencionMuestra->m_obtener_ordenes_paciente_por_historia($allInputs);
		// $lista = $this->model_atencionMuestra->m_obtener_paciente_por_historia($allInputs);
		$arrPaciente = array();
		$arrOrdenes = array();
		
		foreach ($lista as $row) {
			$arrProductos = array();
			array_push($arrPaciente,
				array(
					'idcliente' => $row['idcliente'],
					'idhistoria' => trim($row['idhistoria']),
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					//'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'edad' => devolverEdadDetalle($row['fecha_nacimiento']),
					'sexo' => strtoupper($row['sexo'])
				)
			);
			$listaProductos = $this->model_atencionMuestra->m_obtener_productos_orden($row['orden_venta']);
			//var_dump($listaProductos); exit();
			foreach ($listaProductos as $fila) {
				array_push($arrProductos,
					array(
						'idproductomaster' => $fila['idproductomaster'],
						'producto' => trim($fila['producto']),
						'idanalisis' => $fila['idanalisis'],
						'analisis' => trim($fila['descripcion_anal']),
						'idseccion' => $fila['idseccion'],
						'seccion' => trim($fila['seccion']),
						'iddetalle' => $fila['iddetalle'],
						'cantidad' => $fila['cantidad']
					)
				);
			};
			array_push($arrOrdenes, 
				array(
					'ordenventa' => $row['orden_venta'],
					'productos' => $arrProductos
				)
			);
		}
		
		if(empty($lista)){
			$arrData['datos'] = null;
	    	$arrData['ordenes'] = null;
	     	$arrData['message'] = 'El Paciente no tiene orden de Laboratorio o ya ha sido registrado. VERIFIQUE QUE LA EMPRESA/SEDE SEA LA CORRECTA';
			$arrData['flag'] = 0;
		}else{
			$arrData['datos'] = $arrPaciente[0];
			$arrData['ordenes'] = $arrOrdenes;
	     	$arrData['message'] = 'Se encontró al paciente en el sistema.';
	    	$arrData['flag'] = 1;
		}
    	
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// public function validar_si_existe_orden_venta(){
	// 	$allInputs = json_decode(trim(file_get_contents('php://input')),true);
	// 	$lista = $this->model_atencionMuestra->m_verificar_si_existe_orden($allInputs);
 //     	$arrData['message'] = 'El Paciente ya se ha registrado';
	// 	$arrData['flag'] = 0;
	// }

	public function listar_pacientes(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_atencionMuestra->m_cargar_pacientes($paramPaginate);
		$totalRows = $this->model_atencionMuestra->m_count_pacientes($paramPaginate);
		$arrListado = array();
		foreach ($lista as $row) { 
			$fechaNacimiento = $row['fecha_nacimiento'];
			array_push($arrListado,
				array(
					'id' => trim($row['idcliente']),
					'idhistoria' => trim($row['idhistoria']),
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellidos' => $row['apellido_paterno'].' '.$row['apellido_materno'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					'fecha_nacimiento' => date('d-m-Y', strtotime("$fechaNacimiento")),
					// 'edad' => (empty($row['edad']) ? '0' : $row['edad']),
					'edad' => devolverEdadDetalle($row['fecha_nacimiento']),
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
	public function listarOrdenLaboratorio(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista = $this->model_atencionMuestra->m_cargar_orden_lab_paciente($paramPaginate,$paramDatos);
		$totalRows = $this->model_atencionMuestra->m_count_orden_lab_paciente($paramPaginate,$paramDatos);
		$arrListado = array();
		foreach ($lista as $row) {
			if( $row['prioridad'] == 1 ){
				$prioridad = 'NORMAL';
				$clase = 'label-success';
			}elseif( $row['prioridad'] == 2 ){
				$prioridad = 'URGENTE';
				$clase = 'label-warning';
			}
			if(strtoupper($row['sexo']) == 'M'){
				$sexo = 'Masculino';
			}elseif(strtoupper($row['sexo']) == 'F'){
				$sexo = 'Femenino';
			}else{
				$sexo = 'No Especifica';
			}
			array_push($arrListado,
				array(
					'id' => $row['idmuestrapaciente'],
					'orden_lab' => $row['orden_lab'],
					'idhistoria' => $row['idhistoria'],
					'idcliente' => $row['idcliente'],
					'num_documento' => $row['num_documento'],
					'nombres' => $row['nombres'],
					'apellido_paterno' => $row['apellido_paterno'],
					'apellido_materno' => $row['apellido_materno'],
					//'edad' => $row['edad'].' años',
					'edad' => strtoupper_total(devolverEdadDetalle($row['fecha_nacimiento'])),
					'sexo' => $sexo,
					'fecha_recepcion' => formatoFechaReporte($row['fecha_recepcion']),
					'observaciones' => $row['observaciones'],
					'ordenventa' => $row['orden_venta'],
					'tipo_prioridad' => $row['prioridad'],
					'prioridad' => array(
						'string' => $prioridad,
						'clase' =>$clase,
						'bool' =>$row['prioridad']
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
	public function lista_examenes_por_orden(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($allInputs); exit();
		$paramPaginate = $allInputs['paginate'];
		$paramDatos = @$allInputs['datos'];
		$lista =  $this->model_atencionMuestra->m_cargar_Examenes_por_orden($paramPaginate,$paramDatos);
		$totalRows = $this->model_atencionMuestra->m_count_Examenes_por_orden($paramPaginate,$paramDatos);
		
		$arrListado = array();
		foreach ($lista as $row) {
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
			array_push($arrListado,
				array(
					'idanalisis' => $row['idanalisis'],
					'examen' => $row['descripcion_anal'],
					'producto' => $row['descripcion'],
					'seccion' => $row['descripcion_sec'],
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
	public function ver_popup_formulario(){
		$this->load->view('analisis/generarAnalisis_formView');
	}
	public function ver_popup_detalle_orden_laboratorio(){
		$this->load->view('atencionMuestraLab/detalle_orden_laboratorio_formView');
	}
	
	public function registrarAnalisisPaciente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		
		// VALIDAR QUE NO SE PUEDA REGISTRAR ATENCIONES CUYAS VENTAS QUE TENGAN NOTA DE CRÉDITO. 
    	$fValidateNC = $this->model_venta->m_validar_venta_con_nota_credito($allInputs['ordenventa']);
    	if( !empty($fValidateNC) ){
    		$arrData['message'] = 'Esta atención tiene notas de crédito asignadas. Contacte con el area de Sistemas.';
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
    	}
		$value = $this->model_atencionMuestra->m_verificar_si_existe_orden($allInputs);
		if( !empty($value) ){
			
			$arrData['message'] = 'El Paciente ya fue registrado';
		    $arrData['flag'] = 0;
		    $this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		$arrAnalisis = $allInputs['arrAnalisis'];
		if ( $this->sessionHospital['id_empresa_admin'] == 38 ){ // 38: MEDICINA INTEGRAL
			$codigoOrden = 'LU-' . date('dmy');
		}else{
			$codigoOrden = date('dmy');
		}
		
		$codigoOrden .= '-';

		$ultimaOrden = $this->model_atencionMuestra->m_cargar_ultima_orden_laboratorio();
		if( empty($ultimaOrden) ){
			$numberToOrden = 1;
		}else{ 
			$numberToOrden = substr($ultimaOrden['orden_lab'], -3);
			if( substr($ultimaOrden['orden_lab'], -10, 6) == date('dmy') ){ 
				$numberToOrden = (int)$numberToOrden + 1;
			}else{
				$numberToOrden = 1;
			}
		}
		$codigoOrden .= str_pad($numberToOrden, 3, '0', STR_PAD_LEFT);
		$allInputs['orden_lab'] = $codigoOrden;
		$hayErrorProducto = FALSE;
		$hayErrorEstructura = FALSE;
		foreach ($arrAnalisis as $row) {
			if ( empty($row['idanalisis']) ) {
				$hayErrorProducto = TRUE;
			}elseif($row['idseccion'] == 9){
				$arrAnalisisPerfil = $this->model_atencionMuestra->m_listar_analisis_perfil($row['idanalisis']);
				// var_dump($arrAnalisisPerfil); exit();
				if(count($arrAnalisisPerfil) <= 0){
					$hayErrorEstructura = TRUE;
				}
			}
		}

		if($hayErrorProducto){
			$arrData['message'] = 'El producto NO ha sido asignado a ningún análisis';
			    $arrData['flag'] = 0;
			    $this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
		}
		if($hayErrorEstructura){
			$arrData['message'] = 'El Perfil NO no tiene asignado ningún análisis';
			    $arrData['flag'] = 0;
			    $this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
		}

		//var_dump($allInputs); exit();
		$this->db->trans_start();
		if($this->model_atencionMuestra->m_registrar_muestra($allInputs)){
			$allInputs['id'] = GetLastId('idmuestrapaciente','muestra_paciente');
			
		}

		foreach ($arrAnalisis as $row) {
			if (isset($row['idanalisis'])) {
				for ($i=1; $i <= $row['cantidad'] ; $i++) { 
					if($row['idseccion'] == 9){// si es perfil
						$arrAnalisisPerfil = $this->model_atencionMuestra->m_listar_analisis_perfil($row['idanalisis']);
						//var_dump($arrAnalisisPerfil); exit();
						foreach ($arrAnalisisPerfil as $row2) { // solo para obtener los analisis hijos
							$this->model_atencionMuestra->m_registrar_analisis_hijos($row, $row2, $allInputs);
							$arrData['orden_lab'] = $codigoOrden;
							$arrData['message'] = 'Se registraron los datos correctamente';
				    		$arrData['flag'] = 1;
						}

					}else{
						if($this->model_atencionMuestra->m_registrar_analisis_paciente($row, $allInputs)){
							$arrData['orden_lab'] = $codigoOrden;
							$arrData['message'] = 'Se registraron los datos correctamente';
				    		$arrData['flag'] = 1;
						}else{
							$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
			    			$arrData['flag'] = 0;
			    			break;
						}	
					}	
				}
				
				
			}else{
				$arrData['message'] = 'El producto no tiene asignado un analisis';
    			$arrData['flag'] = 0;
    			break;
			}
			
		}
		$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function rechazarMuestraPaciente()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$arrData['message'] = 'Error al actualizar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		if($this->model_atencionMuestra->m_rechazar_muestra($allInputs)){
			
			$arrData['message'] = 'Se Rechazó la muestra correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}