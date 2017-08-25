<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ControlEvento extends CI_Controller { 

	public function __construct()	{
		parent::__construct();
		$this->load->helper(array('security','otros_helper'));
		$this->load->model(array('model_control_evento', 'model_usuario', 'model_grupo'));
		$this->load->library(array('ci_pusher'));
		
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function listar_control_evento(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_control_evento->m_listar_control_evento($allInputs['datos'], $allInputs['paginate']);
		$totalRows = $this->model_control_evento->m_contar_control_evento($allInputs['datos'], $allInputs['paginate']);
		$arrListado = array();
		foreach ($lista as $key => $item) {	
			$estado_ce = ''; 
			$clase = '';
			if($item['estado_ce'] == 1){
				$estado_ce = 'VISIBLE';
				$clase = 'label-success';
			}

			if($item['estado_ce'] == 2){
				$estado_ce = 'OCULTA';
				$clase = 'label-default';
			}			

			array_push($arrListado, 
				array(
					'idcontrolevento' => $item['idcontrolevento'],
					'fecha_evento' => $item['fecha_evento'],
					'idresponsable' => $item['idresponsable'],
					'responsable' => $item['nombres'].' '. $item['apellido_paterno'].' '. $item['apellido_materno'],
					'comentario' => $item['comentario'],
					'idtipoevento' => $item['idtipoevento'],
					'estado_te' => $item['estado_te'],
					'identificador' => $item['identificador'],
					'texto_notificacion' => $item['texto_notificacion'],					
					'texto_log' => $item['texto_log'],
					'descripcion_te' => $item['descripcion_te'],
					'si_notificacion' => $item['si_notificacion'],
					'estado_ce' => $item['estado_ce'],
					'key_evento' => $item['key_evento'],
					'estado' => array(
						'string' => $estado_ce,
						'clase' =>$clase,
						'bool' =>$item['estado_ce']
					)
				)
			);
		}

		$arrData['datos'] = $arrListado;
		$arrData['paginate']['totalRows'] = $totalRows;
		$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_formulario(){
		$this->load->view('control-evento/crearNotificacion_formView');
	}

	public function registrar_notificacion(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'La notificación no pudo ser registrada. Intente nuevamente';
    	$arrData['flag'] = 0;

    	foreach ($allInputs as $key => $row) {
    		$idgrupos = array();
    		$keysgrupos = array();

			foreach ($row['listaGrupos'] as $grupo) {
				if($grupo['checked'] == 1){
					array_push($idgrupos, $grupo['id']);
					array_push($keysgrupos, $grupo['key_group']);
				}
			}

			if(count($idgrupos) < 1){
				$arrData['message'] = 'Debe seleccionar al menos 1 grupo de usuarios';
				$arrData['flag'] = 0;
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;	    
			}
			$allInputs[$key]['idgrupos'] = $idgrupos;
			$allInputs[$key]['keysgrupos'] = $keysgrupos;
    	}
		

		$this->db->trans_start();
		foreach ($allInputs as $key => $row) {
			$usuarios = $this->model_usuario->m_cargar_usuarios_notificacion($row);
			$data = array(
						'fecha_evento' => date('Y-m-d H:i:s'),
						'idresponsable' => $this->sessionHospital['idempleado'],
						'comentario' =>  empty($row['comentario']) ? NULL : $row['comentario'],				
						'idtipoevento' => $row['idtipoevento'],
						'identificador' => empty($row['identificador']) ? NULL : $row['identificador'],
						'texto_notificacion' => $row['texto_notificacion'],
						'estado_ce' => intval($row['estado_ce']),
						'texto_log' => empty($row['texto_log']) ? NULL : $row['texto_log'],						
						);
			//print_r($usuarios);
			if($this->model_control_evento->m_registrar_evento($data)){
				$idcontrolevento = GetLastId('idcontrolevento','control_evento');
				$error = FALSE;
				foreach ($usuarios as  $usuario) {
					$data2 = array(
						'fecha_notificado' => date('Y-m-d H:i:s'),
						'idcontrolevento' => $idcontrolevento,
						'idusers' =>  $usuario['idusers'],						
						);
					if(!$this->model_control_evento->m_registrar_notificacion_evento($data2)){
						$error = TRUE;
					}
				}

				if(!$error){
					$arrData['message'] = 'Notificación registrada correctamente';
	    			$arrData['flag'] = 1;
	    			//$this->load->library('ci_pusher');
	    			//NOTIFICACION PUSH
					$pusher = $this->ci_pusher->get_pusher();
					
					$dataPush = $data;
					$dataPush['keysgrupos'] = $row['keysgrupos'];
					$dataPush['idcontrolevento'] = $idcontrolevento;
					// Send message
					$event = $pusher->trigger('test_channel', 'my_event', $dataPush);

					if ($event === TRUE)
						$arrData['messagePush'] = 'Event triggered successfully!';					
					else
						$arrData['messagePush'] = 'Ouch, something happend. Could not trigger event.';
				}
			}
		}
		$this->db->trans_complete();  
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ha ocurrido un error al anular. Intente nuevamente';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
    	$error = FALSE;
    	foreach ($allInputs as $row) {
    		if($this->model_control_evento->m_anular($row['idcontrolevento'])){
    			if(!$this->model_control_evento->m_anular_todo_detalle($row['idcontrolevento'])){
    				$error = TRUE;
    			}
    		}else{
    			$error = TRUE;
    		}
    	}
    	if(!$error){
			$arrData['message'] = 'Anulación realizada correctamente';
			$arrData['flag'] = 1;
		}
    	$this->db->trans_complete();
    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cambiar_estado(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Ha ocurrido un error al actualizar las notificaciones. Intente nuevamente';
    	$arrData['flag'] = 0;

    	$this->db->trans_start();
    	$error = FALSE;
    	foreach ($allInputs['lista'] as $row) {
    		if(!$this->model_control_evento->m_cambiar_estado($row['idcontrolevento'], $allInputs['nuevo_estado'])){
    			$error = TRUE;    			
    		}   		
    	}

    	if(!$error){
			$arrData['message'] = 'Notificaciones actualizadas correctamente';
			$arrData['flag'] = 1;
		}		

    	$this->db->trans_complete();

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_grupos_notificacion_desde_usuarios(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_grupo->m_cargar_grupos_cbo($allInputs);
		$listaGruposOld = $this->model_control_evento->m_cargar_grupos_notificacion_desde_usuarios($allInputs['idcontrolevento']);
		$listaOld = array();
		foreach ($listaGruposOld as $value) {
			array_push($listaOld, $value['idgroup']);
		}

		$arrListado = array(); 
		foreach ($lista as $row) {
			$checked = false;
			if(in_array($row['idgroup'], $listaOld))
				$checked = true;
			array_push($arrListado, 
				array(
					'id' => $row['idgroup'],
					'descripcion' => $row['name'],
					'checked'=> $checked,
					'key_group' => $row['key_group']
				)
			);
		}

		$arrData['message'] = '';
		$arrData['flag'] = 1;
		if(count($arrListado) == 0){
			$arrData['flag'] = 0;
		}
		$arrData['datos'] = $arrListado;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function update_leido_notificacion(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = '';
		$arrData['flag'] = 1;
		
		if($allInputs['estado_ceu'] === 1){
			if(!$this->model_control_evento->m_update_leido_notificacion($allInputs['idcontroleventousuario'])){
				$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
				$arrData['flag'] = 0;
			}
		}				

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_notificacion_evento(){
		$this->load->view('control-evento/viewDetalleNotificacion_formView');
	}

	public function genera_notificacion_pusher(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		foreach ($allInputs['lista'] as $row) {
			$listkeysgrupos = $this->model_control_evento->m_cargar_grupos_notificacion_desde_usuarios($row['idcontrolevento']);
    		//print_r($listkeysgrupos);
    		$keysgrupos = array();
    		foreach ($listkeysgrupos as $key => $group) {
    			array_push($keysgrupos, $group['key_group']);
    		}
    		$pusher = $this->ci_pusher->get_pusher();
				
			$dataPush = $row;
			$dataPush['estado_ce'] = $allInputs['nuevo_estado'];
			$dataPush['keysgrupos'] = $keysgrupos;
			// Send message
			$event = $pusher->trigger('test_channel', 'my_event', $dataPush);

			if ($event === TRUE)
				$arrData['messagePush'] = 'Event triggered successfully!';					
			else
				$arrData['messagePush'] = 'Ouch, something happend. Could not trigger event.';	 		
    	}
	}
}