<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProgMedico extends CI_Controller { 

	public function __construct()	{
		parent::__construct();

		$this->load->helper(array('fechas_helper','security', 'otros_helper', 'contable_helper'));
		$this->load->model(array('model_prog_medico', 'model_programacion_ambiente', 'model_ambiente', 
								'model_feriado', 'model_control_evento', 'model_canal', 'model_sede', 
								'model_empleado','model_especialidad', 'model_prog_cita', 'model_venta',
								'model_categoria_consul')
						);
		$this->load->library(array('excel'));
		
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}

	public function ver_popup_formulario(){
		$this->load->view('prog-medico/progMedico_formView');
	}

	public function ver_popup_programacion_cons(){
		$this->load->view('prog-medico/verProgramacionCons_formView');
	}
	public function ver_popup_programacion_proc(){
		$this->load->view('prog-medico/verProgramacionProc_formView');
	}

	public function ver_popup_select_tipo_atencion()
	{
		$this->load->view('prog-medico/verTiposAtencionParaProg_formView');
	}

	public function listar_planing_medicos() 
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrPrincipal = array(); 
		// ARMANDO EL HEADER 
		$arrAssocFechas = array(); 
		$arrFechas = get_rangofechas($allInputs['desde'],$allInputs['hasta'],TRUE);
		$datos = array('anyo' => date('Y',strtotime($allInputs['desde'] ))); 
		
		//obtener feriados
		$feriados = $this->model_feriado->m_lista_feriados_cbo($datos); 
		$arrFeriados = array();
		foreach ($feriados as $row) {
			array_push($arrFeriados,  $row['fecha']); 
		}

		$arrAssocFechas[] = array( 
			'strtotime'=> NULL,
			'fecha'=> NULL,
			'formatFecha'=> 'AMB./DIAS',
			'mesAbv'=> NULL,
			'clase' => 'ambiente' 
		); 
		foreach ($arrFechas as $key => $row) { 
			$arrTemp = array( 
				'strtotime'=> strtotime($row),
				'fecha'=> $row,
				'formatFecha'=> formatoConDiaYNombreDia($row), 
				'mesAbv'=> '('.date('M',strtotime($row)).')',
				'clase'=> (date("w", strtotime($row)) == 0 || in_array($row, $arrFeriados)) ? 'feriado' : NULL //verificar si es feriado o domingo
			); 
			$arrAssocFechas[] = $arrTemp; 
		}
		$arrPrincipal['cabecera'] = $arrAssocFechas;
		// ARMANDO EL CUERPO 
		$listaEspecialidadesProg = $this->model_prog_medico->m_cargar_especialidades_programadas_por_fechas($allInputs); 
		// QUITAR PROGRAMACIONES QUE NO TIENEN DETALLE 
		// foreach ($listaEspecialidadesProg as $key => $row) {
		// 	if( $row['contador'] < 1 ){
		// 		unset($listaEspecialidadesProg[$key]);
		// 	}
		// }
		$allInputs['idsede'] = $this->sessionHospital['idsede'];
		$listaAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede($allInputs['idsede'], empty($allInputs['itemAmbiente']) ? NULL : $allInputs); 
		// llenado del ambiente 
		$arrAssocCuerpo = array(); 
		foreach ($listaAmbientes as $keyAmb => $rowAmb) { 
			$tag = substr($rowAmb['descripcion_cco'], 0,2);
			$arrAssocCuerpo[$rowAmb['idambiente']] = array( 
				'idambiente'=> $rowAmb['idambiente'],
				'numero'=> $rowAmb['numero_ambiente'],
				'piso'=> $rowAmb['piso'],
				'orden'=> $rowAmb['orden_ambiente'],
				'tag' => $tag,
				'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
				'cell'=> array() 
			); 
		}
		// llenado de las celdas 
		foreach ($listaEspecialidadesProg as $key => $row) { 
			$fechaProgramada = $row['fecha_programada'];
			$arrAssocCuerpo[$row['idambiente']]['cell'][$row['fecha_programada']] = array( 
				'fecha'=> date('Y-m-d',strtotime($fechaProgramada)),
				'section' => array(),
				'es_feriado' => (date("w", strtotime($fechaProgramada)) == 0 || in_array($fechaProgramada, $arrFeriados)) ? TRUE : FALSE,
				'class_feriado' => (date("w", strtotime($fechaProgramada)) == 0 || in_array($fechaProgramada, $arrFeriados)) ? 'feriado' : NULL,

			); 
		}
		// llenado de las secciones (especialidades) ******

		foreach ($listaEspecialidadesProg as $key => $row) { 
			$arrActivos = array_map('trim',explode(",",$row['progactivo']));
			$arrAssocCuerpo[$row['idambiente']]['cell'][$row['fecha_programada']]['section'][$row['tipo_atencion_medica']][$row['idespecialidad']] = array( 
				'idespecialidad'=> $row['idespecialidad'],
				'especialidad'=> $row['nombre'],
				'total_prog'=> $row['contador'],
				'idprogramaciones'=> $row['idsempresamedico'],
				'tipoAtencion' => $row['tipo_atencion_medica'], 
				'prog_activa' => in_array('2',$arrActivos) ? FALSE : TRUE		// ******** activos de la programacion
			);
		} 
		//var_dump($arrAssocCuerpo); 
		//exit();

		// LLENAR FECHAS VACIAS A LOS AMBIENTES QUE TIENEN PROGRAMACION PARCIAL EN ESOS DIAS 
		foreach ($arrAssocFechas as $keyFecha => $rowFecha) { 
			if( !empty($rowFecha['fecha']) ){ 
				foreach ($arrAssocCuerpo as $key => $row) { 
					if( !(array_key_exists($rowFecha['fecha'],$arrAssocCuerpo[$key]['cell'])) ){ 
						$arrAssocCuerpo[$key]['cell'][$rowFecha['fecha']] = array(
							'fecha'=> $rowFecha['fecha'],
							'idespecialidad'=> NULL,
							'especialidad'=> NULL,
							'total_prog'=> 0,
							'es_feriado' => ($rowFecha['clase'] == 'feriado') ? TRUE : FALSE,
							'class_feriado' => $rowFecha['clase']
						);
					}
				}
			}
		}
		// LLENAR FECHAS VACIAS A LOS AMBIENTES QUE NO TIENEN AL MENOS UNA PROGRAMACIONES EN ESAS FECHAS
		foreach ($arrAssocCuerpo as $key => $row) { 
			if(empty($row['cell'])){
				foreach ($arrAssocFechas as $keyFecha => $rowFecha) { 
					if( !empty($rowFecha['fecha']) ){ 
						$arrAssocCuerpo[$key]['cell'][$keyFecha] = array( 
							'fecha'=> $rowFecha['fecha'],
							'idespecialidad'=> NULL,
							'especialidad'=> NULL,
							'total_prog'=> 0,
							'es_feriado' => ($rowFecha['clase'] == 'feriado') ? TRUE : FALSE,
							'class_feriado' => $rowFecha['clase']							
						); 
					}
				}
			}
		}
		if( $allInputs['filtroAmbientes']['id'] == 2 ){ 
			// QUITAR AMBIENTES QUE NO HAN SIDO PROGRAMADOS EN EL RANGO DE FECHAS 
			foreach ($arrAssocCuerpo as $key => $row) {
				$rompeFila = TRUE;
				foreach ($row['cell'] as $keyCell => $rowCell) {
					if( array_key_exists('section', $rowCell) ){
						$rompeFila = FALSE;
					}
					// if( !empty($rowCell['total_prog']) ){
					// 	$rompeFila = FALSE;
					// }
				}
				if( $rompeFila ){ 
					unset($arrAssocCuerpo[$key]);
				}
			}
		}
		
		// REORDENADO DE ARRAY 
		function fnOrderingPA($a, $b) { 
	    	return strtotime($a['fecha']) - strtotime($b['fecha']); 
	    }
	    foreach ($arrAssocCuerpo as $key => $row) {
	    	usort($arrAssocCuerpo[$key]['cell'],'fnOrderingPA'); 
	    }

		// REINDEXADO DE ARRAY 
		$arrAssocCuerpo = array_values($arrAssocCuerpo);
		foreach ($arrAssocCuerpo as $key => $row) { 
			$arrAssocCuerpo[$key]['cell'] = array_values($arrAssocCuerpo[$key]['cell']); 
		}

		$arrPrincipal['cuerpo'] = $arrAssocCuerpo; 
		// var_dump($arrAssocCuerpo); exit();
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

	public function listar_estas_programaciones_proc()
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs['ids']); exit();
		$arrIdProg = explode(",", $allInputs['ids']);
		$reprog = @$allInputs['reprog'];
		$view = empty($allInputs['view']) ? FALSE : $allInputs['view'];

		$lista = $this->model_prog_medico->m_cargar_estas_programaciones($arrIdProg, $reprog, $view, $allInputs['tipoAtencion']); 
		$listaAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede_session(); 
		$arrAmbientes = array(); 
		foreach ($listaAmbientes as $row) {
			array_push($arrAmbientes, 
				array(
					'id' => $row['idambiente'], 
					'descripcion' => strtoupper($row['numero_ambiente']) . '- Piso: ' . strtoupper($row['piso']) . '-'. strtoupper($row['descripcion_scco']),
					'numero_ambiente' => strtoupper($row['numero_ambiente']),
					'idcategoriaconsul' => $row['idcategoriaconsul'],
					'idsubcategoriaconsul' => $row['idsubcategoriaconsul'],
					'piso' => strtoupper($row['piso'])
				)
			);
		}

		//subcategorias
		$listaSubcategorias = $this->model_categoria_consul->m_cargar_subcategoria_consul_cbo(2);
		$arrSubcategorias = array(); 
		foreach ($listaSubcategorias as $row) {
			array_push($arrSubcategorias, 
				array(
					'id' => (int)$row['idsubcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_scco'])
				)
			);
		}

		// AGRUPAMIENTO POR PROGRAMACION 
		$arrPrincipal = array(); 
		foreach ($lista as $key => $row) { 
			$rowHoraInicio = $row['hora_inicio']; 
			$rowHoraFin = $row['hora_fin'];

			$rowHoraInicioEdit = date('H',strtotime($rowHoraInicio));
			$rowMinutoInicioEdit = date('i',strtotime($rowHoraInicio));
			$rowHoraFinEdit = date('H',strtotime($rowHoraFin));
			$rowMinutoFinEdit = date('i',strtotime($rowHoraFin));

			$rowHoraInicio = date('H:i a',strtotime($rowHoraInicio));
			$rowHoraFin = date('H:i a',strtotime($rowHoraFin));
			$rowTipoAtencion = NULL;
			if($row['tipo_atencion_medica'] == 'P'){ 
				$rowTipoAtencion = 'PROCEDIMIENTO';
			}
			$timestamp = strtotime($row['intervalo_hora']); 
			$intervaloHoraInt = date('i', $timestamp);
			$tsFechaProg = strtotime($row['fecha_programada']);
			$rowFechaProgramada = date('d-m-Y',$tsFechaProg);

			$arrTemp = array( 
				'idprogmedico'=> $row['idprogmedico'],
				'fecha_programada'=> $rowFechaProgramada,
				'hora_inicio_edit'=> $rowHoraInicioEdit,
				'minuto_inicio_edit'=> $rowMinutoInicioEdit,
				'hora_fin_edit'=> $rowHoraFinEdit,
				'minuto_fin_edit'=> $rowMinutoFinEdit,
				'hora_inicio'=> $rowHoraInicio,
				'hora_fin'=> $rowHoraFin,
				'tipo_atencion_medica'=> $rowTipoAtencion,
				'tipo_atencion'=> $row['tipo_atencion_medica'],
				'comentario'=> $row['comentario_pmed'],
				'activo' => (int)$row['activo'],
				'ambiente'=> array(
					'id'=> $row['idambiente'],
					'descripcion'=> $row['numero_ambiente'],
					'numero_ambiente'=> $row['numero_ambiente'],
					'piso'=> $row['piso'],
					'idcategoriaconsul'=> $row['idcategoriaconsul'], 
					'idsubcategoriaconsul'=> $row['idsubcategoriaconsul'] 
				),
				'idmedico'=> $row['idmedico'],
				'medico'=> $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'],
				'idespecialidad'=> $row['idespecialidad'],
				'especialidad'=> $row['nombre'],
				'idempresa'=> $row['idempresa'],
				'empresa'=> $row['empresa'],
				'idempresamedico'=> $row['idempresamedico'],
				'idcategoriaconsul'=> (int)$row['idcategoriaconsul'],
				'categoria'=> array( 
					'id'=> $row['idcategoriaconsul'],
					'descripcion'=> $row['descripcion_cco'],
				), 
				'idsubcategoriaconsul'=> (int)$row['idsubcategoriaconsul'],
				'subcategoria'=> array(
					'id'=> $row['idsubcategoriaconsul'],
					'descripcion'=> $row['descripcion_scco']
				),
				'si_renombrado_scc'=> ($row['si_renombrado_scc'] == 1 ? TRUE : FALSE),
				'idsubcategoriarenom'=> (int)$row['idsubcategoriarenom'], 
				'subcategoriarenom'=> array(
					'id'=> $row['idsubcategoriarenom'],
					'descripcion'=> $row['subcategoriarenom']
				),
				'turno'=> 'De '.$rowHoraInicio.' a '.$rowHoraFin, 
				'total_cupos_master'=> NULL,
				'cupos_por_hora'=> NULL,
				'canales'=> array(),
				'todos_cupos'=> array(),
				'ambientes'=> $arrAmbientes,
				'subcategorias'=> $arrSubcategorias,
				'estado_prm'=> $row['estado_prm'],
			);
			$arrPrincipal[$row['idprogmedico']] = $arrTemp; 
		}

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

	public function listar_estas_programaciones()
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs['ids']); exit();
		$arrIdProg = explode(",", $allInputs['ids']);
		$reprog = @$allInputs['reprog'];
		$view = empty($allInputs['view']) ? FALSE : $allInputs['view'];

		$lista = $this->model_prog_medico->m_cargar_estas_programaciones($arrIdProg, $reprog, $view, $allInputs['tipoAtencion']); 
		$listaAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede_session(); 
		$arrAmbientes = array(); 
		foreach ($listaAmbientes as $row) {
			array_push($arrAmbientes, 
				array(
					'id' => $row['idambiente'], 
					'descripcion' => strtoupper($row['numero_ambiente']) . '- Piso: ' . strtoupper($row['piso']) . '-'. strtoupper($row['descripcion_scco']),
					'numero_ambiente' => strtoupper($row['numero_ambiente']),
					'idcategoriaconsul' => $row['idcategoriaconsul'],
					'idsubcategoriaconsul' => $row['idsubcategoriaconsul'],
					'piso' => strtoupper($row['piso'])
				)
			);
		}

		//subcategorias cupos_adicionales
		$listaSubcategorias = $this->model_categoria_consul->m_cargar_subcategoria_consul_cbo(2);
		$arrSubcategorias = array(); 
		foreach ($listaSubcategorias as $row) {
			array_push($arrSubcategorias, 
				array(
					'id' => (int)$row['idsubcategoriaconsul'],
					'descripcion' => strtoupper($row['descripcion_scco'])
				)
			);
		}

		// AGRUPAMIENTO POR PROGRAMACION 
		$arrPrincipal = array(); 
		foreach ($lista as $key => $row) { 
			$rowHoraInicio = $row['hora_inicio']; 
			$rowHoraFin = $row['hora_fin'];

			$rowHoraInicioEdit = date('H',strtotime($rowHoraInicio));
			$rowMinutoInicioEdit = date('i',strtotime($rowHoraInicio));
			$rowHoraFinEdit = date('H',strtotime($rowHoraFin));
			$rowMinutoFinEdit = date('i',strtotime($rowHoraFin));

			$rowHoraInicio = date('H:i a',strtotime($rowHoraInicio));
			$rowHoraFin = date('H:i a',strtotime($rowHoraFin));
			$rowTipoAtencion = NULL;
			if($row['tipo_atencion_medica'] == 'CM'){ 
				$rowTipoAtencion = 'CONSULTA MÉDICA';
			}
			$timestamp = strtotime($row['intervalo_hora']); 
			$intervaloHoraInt = date('i', $timestamp);
			$tsFechaProg = strtotime($row['fecha_programada']);
			$rowFechaProgramada = date('d-m-Y',$tsFechaProg);

			$arrTemp = array( 
				'idprogmedico'=> $row['idprogmedico'],
				'fecha_programada'=> $rowFechaProgramada,
				'hora_inicio_edit'=> $rowHoraInicioEdit,
				'minuto_inicio_edit'=> $rowMinutoInicioEdit,
				'hora_fin_edit'=> $rowHoraFinEdit,
				'minuto_fin_edit'=> $rowMinutoFinEdit,
				'hora_inicio'=> $rowHoraInicio,
				'hora_fin'=> $rowHoraFin,
				'intervalo_hora'=> $row['intervalo_hora'],
				'intervalo_hora_int'=> $intervaloHoraInt,
				'cupos_adicionales'=> $row['cupos_adicionales'], 
				'tipo_atencion_medica'=> $rowTipoAtencion,
				'tipo_atencion'=> $row['tipo_atencion_medica'],
				'comentario'=> $row['comentario_pmed'],
				'activo' => (int)$row['activo'],
				'ambiente'=> array(
					'id'=> $row['idambiente'],
					'descripcion'=> $row['numero_ambiente'],
					'numero_ambiente'=> $row['numero_ambiente'],
					'piso'=> $row['piso'],
					'idcategoriaconsul'=> $row['idcategoriaconsul'], 
					'idsubcategoriaconsul'=> $row['idsubcategoriaconsul'] 
				),
				'idmedico'=> $row['idmedico'],
				'medico'=> $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'],
				'idespecialidad'=> $row['idespecialidad'],
				'especialidad'=> $row['nombre'],
				'idempresa'=> $row['idempresa'],
				'empresa'=> $row['empresa'],
				'idempresamedico'=> $row['idempresamedico'],
				'idcategoriaconsul'=> (int)$row['idcategoriaconsul'],
				'categoria'=> array( 
					'id'=> $row['idcategoriaconsul'],
					'descripcion'=> $row['descripcion_cco'],
				), 
				'idsubcategoriaconsul'=> (int)$row['idsubcategoriaconsul'],
				'subcategoria'=> array(
					'id'=> $row['idsubcategoriaconsul'],
					'descripcion'=> $row['descripcion_scco']
				),
				'si_renombrado_scc'=> ($row['si_renombrado_scc'] == 1 ? TRUE : FALSE),
				'idsubcategoriarenom'=> (int)$row['idsubcategoriarenom'], 
				'subcategoriarenom'=> array(
					'id'=> $row['idsubcategoriarenom'],
					'descripcion'=> $row['subcategoriarenom']
				),
				'turno'=> 'De '.$rowHoraInicio.' a '.$rowHoraFin, 
				'total_cupos_master'=> NULL,
				'cupos_por_hora'=> NULL,
				'canales'=> array(),
				'todos_cupos'=> array(),
				'ambientes'=> $arrAmbientes,
				'subcategorias'=> $arrSubcategorias,
				'estado_prm'=> $row['estado_prm'],
			);
			$arrPrincipal[$row['idprogmedico']] = $arrTemp; 
		}
		// AGRUPAMIENTO POR CUPOS DIRECTAMENTE HACIA LA PROGRAMACION (me sirve al editar) total_adi_vendidos
		foreach ($lista as $key => $row) { 
			$rowHoraInicioDet = $row['hora_inicio_det'];
			$rowHoraInicioDet = date('H:i a',strtotime($rowHoraInicioDet));

			$rowHoraFinDet = $row['hora_fin_det'];
			$rowHoraFinDet = date('H:i a',strtotime($rowHoraFinDet));
			$clase = '';
			$estado_cupo_str = '';
			if($row['estado_cupo'] == 1){
				$estado_cupo_str = 'ocupado';
				$clase = 'label-danger';
			}else if($row['estado_cupo'] == 2){
				$estado_cupo_str = 'disponible';
				$clase = 'label-danger';
			}else if($row['estado_cupo'] == 3){
				$estado_cupo_str = 'cancelado';
				$clase = 'label-danger';
			}else if($row['estado_cupo'] == 4){
				$estado_cupo_str = 'reprogramado';
				$clase = 'label-danger';
			}

			$arrTemp = array( 
				'iddetalleprogmedico'=> $row['iddetalleprogmedico'],
				'idcanal'=> $row['idcanal'],
				'tipoCupo'=> ($row['si_adicional'] == 2) ? '':'adicional',
				'numero_cupo'=> $row['numero_cupo'],
				'hora_inicio_det'=> $rowHoraInicioDet,
				'hora_fin_det'=> $rowHoraFinDet,
				'intervalo_hora_det'=> $row['intervalo_hora_det'],
				'si_adicional'=> ($row['si_adicional'] == 2) ? '':'<b>ADICIONAL</b>',
				'si_adicional_bool'=> $row['si_adicional'], 
				// 'estadoHtml'=> '',
				'estado_cupo' => array( 
					'string' => strtoupper($estado_cupo_str), 
					'clase' =>$clase,
					'bool' =>$row['estado_cupo']
				)
			);
			$arrPrincipal[$row['idprogmedico']]['todos_cupos'][$row['iddetalleprogmedico']] = $arrTemp; 
		}
		// AGRUPAMIENTO POR CANALES 
		foreach ($lista as $key => $row) { 
			$arrTemp = array( 
				'idcanalprogmedico'=> $row['idcanalprogmedico'],
				'idcanal'=> $row['idcanal'],
				'canal'=> $row['descripcion_can'],
				'total_cupos'=> $row['total_cupos'],
				'cupos_ocupados'=> $row['cupos_ocupados'],
				'total_adi_vendidos'=> $row['total_adi_vendidos'],
				'cupos_ocupados_todos'=> $row['cupos_ocupados'] + $row['total_adi_vendidos'], 
				'todos_los_cupos'=> $row['total_cupos'] + $row['cupos_adicionales'], 
				'textContCupos'=> 'VER CUPOS',
				'contCuposDeCanal'=> false,
				'cupos'=> array() 
			);
			$arrPrincipal[$row['idprogmedico']]['canales'][$row['idcanal']] = $arrTemp; 
		}
		// AGRUPAMIENTO POR CUPOS 
		foreach ($lista as $key => $row) { 
			$rowHoraInicioDet = $row['hora_inicio_det'];
			$rowHoraInicioDet = date('H:i a',strtotime($rowHoraInicioDet));

			$rowHoraFinDet = $row['hora_fin_det'];
			$rowHoraFinDet = date('H:i a',strtotime($rowHoraFinDet));

			$estado_cupo_str = '';
			$clase = '';
			if($row['estado_cupo'] == 1){
				$estado_cupo_str = 'ocupado';
				$clase = 'label-danger';
			}else if($row['estado_cupo'] == 2){
				$estado_cupo_str = 'disponible';
				$clase = 'label-success';
			}else if($row['estado_cupo'] == 3){
				$estado_cupo_str = 'cancelado';
				$clase = 'label-default';
			}else if($row['estado_cupo'] == 4){
				$estado_cupo_str = 'reprogramado';
				$clase = 'label-inverse';
			}
			$arrTemp = array( 
				'iddetalleprogmedico'=> $row['iddetalleprogmedico'],
				'tipoCupo'=> ($row['si_adicional'] == 2) ? '':'adicional',
				'numero_cupo'=> $row['numero_cupo'],
				'hora_inicio_det'=> $rowHoraInicioDet,
				'hora_fin_det'=> $rowHoraFinDet,
				'intervalo_hora_det'=> $row['intervalo_hora_det'],
				'si_adicional'=> ($row['si_adicional'] == 2) ? '':'<b>ADICIONAL</b>',
				'si_adicional_bool'=> $row['si_adicional'], 
				//'estadoHtml'=> '', // cuando se implemente la venta
				// 'estado_cupo' => $row['estado_cupo'],
				// 'estado_cupo_str' => $estado_cupo_str, 
				'estado_cupo' => array( 
					'string' => strtoupper($estado_cupo_str), 
					'clase' =>$clase,
					'bool' =>$row['estado_cupo']
				)
			);
			$arrPrincipal[$row['idprogmedico']]['canales'][$row['idcanal']]['cupos'][$row['iddetalleprogmedico']] = $arrTemp; 
		}

		// SUMAR TOTAL DE CUPOS y CUPOS POR HORA 
		foreach ($arrPrincipal as $key => $row) { 
			$sumCanales = 0;
			foreach ($row['canales'] as $key2 => $rowCanal) {
				$sumCuposAdicionales = 0; 
				$sumCanales += $rowCanal['total_cupos']; 
				foreach ($rowCanal['cupos'] as $key3 => $rowCupo) {
					if($rowCupo['si_adicional_bool'] == 1){
						$sumCuposAdicionales++;
					}
				}
				$arrPrincipal[$key]['canales'][$key2]['cupos_adicionales'] = $sumCuposAdicionales;
			}
			$arrPrincipal[$key]['total_cupos_master'] = $sumCanales; 
			$cuposPorHora = $arrPrincipal[$key]['total_cupos_master'] / (($arrPrincipal[$key]['total_cupos_master'] * $arrPrincipal[$key]['intervalo_hora_int']) / 60); 
			$arrPrincipal[$key]['cupos_por_hora'] = $cuposPorHora; 
		}
		// REORDENADO DE ARRAY
		// function fnOrdering($a, $b) { 
	 //    	return strtotime($a['numero_cupo']) - strtotime($b['numero_cupo']); 
	 //    }
	 //    foreach ($arrAssocCuerpo as $key => $row) {
	 //    	usort($arrAssocCuerpo[$key]['cell'],'fnOrdering'); 
	 //    }

		// // REINDEXADO DE ARRAY 
		// $arrAssocCuerpo = array_values($arrAssocCuerpo);
		// foreach ($arrAssocCuerpo as $key => $row) { 
		// 	$arrAssocCuerpo[$key]['cell'] = array_values($arrAssocCuerpo[$key]['cell']); 
		// }

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
	public function registrar()	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Se registraron los datos correctamente';
    	$arrData['flag'] = 1;
    	$outputMsg = '';
		//var_dump($allInputs); exit();
		if($allInputs['idmedico'] == null){
        	$outputMsg .= ' Debe seleccionar Médico'; 
        }else if($allInputs['idambiente'] == null or $allInputs['idambiente'] =='0'){                        
        	$outputMsg .= ' Debe seleccionar Ambiente';
        }else  if($allInputs['renombrar'] && ($allInputs['idsubcategoriaconsulrenom']=='0'|| $allInputs['idcategoriaconsulrenom']=='0')) {        
        	$outputMsg .= ' Debe seleccionar Categoría y Subcategoría para Renombrar';
        }else  if(count($allInputs['programas']) < 1){
        	$outputMsg .= ' Debe seleccionar Días y Horarios de programación';
        }else{
        	if($allInputs['renombrar']){
				$si_renombrado_scc = 1;			
				$idsubcategoriaconsulrenom = $allInputs['idsubcategoriaconsulrenom']; 
			}else{
				$si_renombrado_scc = 2;
				$idsubcategoriaconsulrenom = null;
			} 
		}
		
		if($outputMsg == ''){ 
			$arrProgMed = array();	
			$arrNotificaciones = array();		
			$this->db->trans_begin();
			foreach ($allInputs['programas'] as $key => $item) {
				$intervalo = null; $number = null; $hora_fin_comparar = '00:00:00'; $cupos_adicionales=0; 
				if($allInputs['tipoAtencion'] == 'CM'){
					$intervalo = '00:'.str_pad($item['intervalo'],2,"0",STR_PAD_LEFT) . ':00';
		        	$number = intval(explode(":",$item['hora_fin'])[0]);
	 				$hora_fin_comparar = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00'; 
	 				$cupos_adicionales = $item['cupos_adicionales'];
				}
		        
				$data = array( 
					'fecha_programada' => $item['fecha_item'],
					'hora_inicio' => $item['hora_inicio'],
					'hora_fin' => $item['hora_fin'],
					'hora_fin_comparar' => $hora_fin_comparar,
					'intervalo_hora' =>   $intervalo,
					'cupos_adicionales' => $cupos_adicionales,
					'comentario_pmed' => $allInputs['comentario'],
					'activo' => $allInputs['activo'],
					'si_renombrado_scc' => $si_renombrado_scc,
					'idempresamedico' => $allInputs['idempresamedico'],
					'idmedico' => $allInputs['idmedico'],
					'idambiente' => $allInputs['idambiente'],
					'idespecialidad' => $allInputs['idespecialidad'],
					'idsubcategoriaconsul' => $allInputs['idsubcategoriaconsul'],
					'idsubcategoriaconsulrenom' => $idsubcategoriaconsulrenom,
					'idsede' => $allInputs['idsede'],
					'tipo_atencion_medica' => $allInputs['tipoAtencion']
				);

				if(strtotime($item['fecha_item']) < strtotime(date("d-m-Y"))){
					$outputMsg .= ' No se permite ingresar programaciones con fechas pasadas.';
				}else if($this->model_prog_medico->m_verifica_planing_medico($data) > 0){
					$date = new DateTime($item['fecha_item']);
					$outputMsg .= '  No puede exitir dos médicos en el mismo ambiente mismo turno';
				}else if($this->model_programacion_ambiente->m_verificar_operatividad_ambiente($data) > 0){
					$date = new DateTime($item['fecha_item']);
					$outputMsg .= ' El ambiente no esta operativo para el turno ' . $date->format('d/m/Y') . ' - ' . darFormatoHora($item['hora_inicio']) . ' ' .darFormatoHora($item['hora_fin']);
				}else if($this->model_prog_medico->m_verifica_planing($data)>0){
					$date = new DateTime($item['fecha_item']);
					$outputMsg .= ' Ya existe programación en ese ambiente en el turno ' . $date->format('d/m/Y') . ' - ' . darFormatoHora($item['hora_inicio']) . ' ' .darFormatoHora($item['hora_fin']);
				}else if($this->model_prog_medico->m_verifica_medico($data) > 0){
					$date = new DateTime($item['fecha_item']);
					$outputMsg .= ' El médico ya tiene una programación en turno similar ' . $date->format('d/m/Y') . ' - ' . darFormatoHora($item['hora_inicio']) . ' ' .darFormatoHora($item['hora_fin']);
				}else if($this->model_prog_medico->m_registrar_prog_medico($data)){
					$idprogmedico = GetLastId('idprogmedico','pa_prog_medico');
					$canales = $item['canales'];
					$horaInicioDet = strtotime($item['hora_inicio']); 	
					$numero_cupo=1;
					$numero_cupo_adicional=1;

					$paraCorreo = array(
						'medico' => $allInputs['medico'],
						'idmedico' => $allInputs['idmedico'],
						'especialidad' => $allInputs['especialidad'],
						'ambiente' => $allInputs['ambiente'],
						'sede' => $this->sessionHospital['sede'],
						'turno' => $item['turno'],
						'fecha_item' => $item['fecha_item'],
						'idprogmedico' => $idprogmedico ,
					);

					array_push($arrProgMed, $paraCorreo);

					if($allInputs['tipoAtencion'] == 'CM'){

						foreach ($canales as $key => $canal) {						
							if($this->model_prog_medico->m_registrar_canal_prog_medico($canal, $idprogmedico)){							
								for($i = 0; $i< $canal['cant_cupos_canal']; $i++){
									$hora1 = new DateTime();
									$hora1->setTimestamp($horaInicioDet);							
									$horaFinDet = strtotime("+".$item['intervalo']." minutes", $horaInicioDet);	
									$hora2 = new DateTime();
									$hora2->setTimestamp($horaFinDet);	
									
									$data2 = array(
										'idprogmedico' => $idprogmedico,
										'idcanal' => $canal['id'],
										'hora_inicio_det' => date_format($hora1,'H:i:s'),
										'hora_fin_det' => date_format($hora2,'H:i:s'),
										'intervalo_hora_det' => $intervalo,
										'numero_cupo' => $numero_cupo,
										'si_adicional' => 2, //no es adicional
									);
									$numero_cupo++;
									$horaInicioDet = $horaFinDet;
									if(!$this->model_prog_medico->m_registrar_detalle_prog_medico ($data2)){
										$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
							    		$arrData['flag'] = 0;
									}								
								}
							}else{
								$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
		    					$arrData['flag'] = 0;
							}
						}

						foreach ($canales as $key => $canal) {		
							for($i = 0; $i< $canal['cant_cupos_adic_canal']; $i++){
								$data3 = array(
									'idprogmedico' => $idprogmedico,
									'idcanal' => $canal['id'],
									'hora_inicio_det' => date_format($hora1,'H:i:s'),
									'hora_fin_det' => date_format($hora2,'H:i:s'),
									'intervalo_hora_det' => $intervalo,
									'si_adicional' => 1, //es adicional,
									'numero_cupo' => $numero_cupo_adicional
								);
								$numero_cupo_adicional++;
								
								if(!$this->model_prog_medico->m_registrar_detalle_prog_medico($data3)){
									$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
						    		$arrData['flag'] = 0;
								}
							}
						}
					}

					$texto_notificacion = generar_notificacion_evento(1, 'key_prog_med', $paraCorreo);
					$data = array(
						'fecha_evento' => date('Y-m-d H:i:s'),
						'idresponsable' => $this->sessionHospital['idempleado'],
						'comentario' =>  null,				
						'idtipoevento' => 1,
						'identificador' => $idprogmedico,
						'texto_notificacion' => $texto_notificacion,
						'texto_log' => $texto_notificacion,
					);	
					array_push($arrNotificaciones, array(
														'texto_notificacion'=> $texto_notificacion,
														'texto_log'=> $texto_notificacion,
														'identificador'=> $idprogmedico
														));
					if(!$this->model_control_evento->m_registrar_evento($data)){
						$outputMsg = 'Ha ocurrido un error registrando el log';
					}
				}
			}	       
		}

		if ($this->db->trans_status() == FALSE || $outputMsg != ''){
			$this->db->trans_rollback();
			$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
	    	$arrData['flag'] = 0;
		}else{
			$this->db->trans_commit();
			$flagMail = $this->envia_correo_medico(1, $arrProgMed);
			$arrData['flagMail'] = $flagMail;		

			if($flagMail == 0)
				$arrData['messageMail'] = 'Notificación de correo NO enviada.';
			else if($flagMail == 1)
				$arrData['messageMail'] = 'Notificación de correo enviada exitosamente.';
			else if($flagMail == 2) 
				$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico invalido.';
			else if($flagMail == 3)				
				$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico no registrado.';		
			
			$arrData['notificaciones'] = $arrNotificaciones;
				
		}

		if($outputMsg != ''){
			$arrData['message'] = $outputMsg;
	    	$arrData['flag'] = 0;
		}
		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_horas(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);	
		$number = intval(explode(":",$allInputs['hora_fin'])[0]);
	 	$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';

		$rango = get_rangohoras($allInputs['hora_inicio'], $hora_fin);
		$arrListado = array();
		foreach ($rango as $row) {
			$number = intval(explode(":",$row)[0]);
			array_push($arrListado, 
				array(
					'hora' => $row,
					'hora_formato' => darFormatoHora($row),
					'numero'=> $number				
				)
			);

			$segundos_horaInicial=strtotime($row); 
			$segundos_minutoAnadir=30*60; 
			$nuevaHora=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);
			$number = intval(explode(":",$nuevaHora)[0]);
			array_push($arrListado, 
				array(
					'hora' => $nuevaHora,
					'hora_formato' => darFormatoHora($nuevaHora),
					'numero'=> $number + 0.5				
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

	public function editar()
	{
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
	    $arrData['flag'] = 1;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// VALIDACIONES si_adicional 
		//var_dump($allInputs); exit();
    	foreach ($allInputs['datos'] as $key => $row) { 
    		// VALIDAR QUE EN CAMPOS NUMERICOS NO SE INGRESE TEXTO 
    		if( !(is_numeric($row['hora_inicio_edit'])) || !(is_numeric($row['minuto_inicio_edit'])) || !(is_numeric($row['hora_fin_edit'])) || !(is_numeric($row['minuto_fin_edit']))
    		){ 
    			$arrData['message'] = 'No se acepta texto en campos numéricos. Corregir y guardar nuevamente.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}
    		if( $row['hora_inicio_edit'] < 0 || $row['minuto_inicio_edit'] < 0 || $row['hora_fin_edit'] < 0 || 
    			$row['minuto_fin_edit'] < 0 
    		){ 
    			$arrData['message'] = 'No se acepta numéricos negativos. Corregir y guardar nuevamente.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}
    		if($row['tipo_atencion'] == 'CM'){
    			if(  !(is_numeric($row['intervalo_hora_int'])) || !(is_numeric($row['cupos_adicionales'])) 
    			|| !(is_numeric($row['total_cupos_master']))
	    		){ 
	    			$arrData['message'] = 'No se acepta texto en campos numéricos. Corregir y guardar nuevamente.'; 
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return; 
	    		}
	    		if( $row['intervalo_hora_int'] < 0 || $row['cupos_adicionales'] < 0 || 
					$row['total_cupos_master'] < 0
	    		){ 
	    			$arrData['message'] = 'No se acepta numéricos negativos. Corregir y guardar nuevamente.'; 
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return; 
	    		}
    		}
    		//VALIDAR HORAS 
    		// if(	($row['minuto_inicio_edit'] != 0 && $row['minuto_inicio_edit'] !=  30)  
    		// 		|| ( $row['minuto_fin_edit'] != 0 && $row['minuto_fin_edit'] !=  30) ){
    		// 	$arrData['message'] = 'Sólo se aceptan horarios de horas completas o medias horas.'; 
	    	// 	$arrData['flag'] = 0;
	    	// 	$this->output
			   //  	->set_content_type('application/json')
			   //  	->set_output(json_encode($arrData));
			   //  return;
    		// }

    		//VALIDAR FECHA PROGRAMADA 
    		if(strtotime($row['fecha_programada']) < strtotime(date("d-m-Y"))){
    			$arrData['message'] = 'No se permite ingresar programaciones con fechas pasadas.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
    		}

    		// VALIDAR SI ESTA RENOMBRADO Y TIENE SUB CATEGORIA ASIGNADA 
    		if( $row['si_renombrado_scc'] === TRUE ){ 
    			if( empty($row['subcategoriarenom']['id']) ){ 
    				$arrData['message'] = 'No se ha seleccionado categoria a renombrar.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
    			}
    		}
    		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
    		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

    		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
    		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

    		
    		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00';
    		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
	 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 

	 		if($row['tipo_atencion'] == 'CM'){
	 			$intervalo = '00:'.str_pad($row['intervalo_hora_int'],2,"0",STR_PAD_LEFT) . ':00';
	 		} 
	 		// VALIDAR SI EL AMBIENTE FECHA Y HORA DONDE SE ESTÁ TRASLADANDO ESTA INOPERATIVO. 
    		$arrParams = array( 
    			'idambiente'=> $row['ambiente']['id'], 
    			'fecha_programada'=> $row['fecha_programada'], 
    			'hora_inicio'=> $horaInicioCompleto, 
    			'hora_fin_comparar'=> $horaFinComparar
    			
    		);
    		if($this->model_programacion_ambiente->m_verificar_operatividad_ambiente($arrParams) > 0){ 
    			$arrData['message'] = 'El ambiente N° '.$row['ambiente']['descripcion'].' está inoperativo para las fechas seleccionadas.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		} 

    		// VALIDAR QUE NO HAYA PROGRAMACIÓN QUE SE CRUCE EN EL MISMO AMBIENTE CON SIMILAR TURNO - EDICION 
    		$arrParams = array( 
    			'hora_inicio'=> $horaInicioCompleto,
    			'hora_fin'=> $horaFinCompleto,
    			'idmedico'=> $row['idmedico'], 
    			'idambiente'=> $row['ambiente']['id'],
    			'fecha_programada'=> $row['fecha_programada'],
    			'idprogmedico'=> $row['idprogmedico'],
    			'tipo_atencion_medica' => $row['tipo_atencion']  
    		); 
    		if($this->model_prog_medico->m_verifica_planing_medico($arrParams,TRUE) > 0){
				$arrData['message'] = 'No puede exitir dos médicos en el mismo ambiente mismo turno'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
			}
    		if($this->model_prog_medico->m_verifica_planing($arrParams,TRUE)>0){ 
    			$arrData['message'] = 'Ya existe programación en ese ambiente y en ese turno'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}

    		// VALIDAR QUE EL MEDICO NO ESTÉ PROGRAMADO PARA OTRO AMBIENTE EN LA MISMA HORA. - EDICION 
    		$arrParams = array( 
    			'hora_inicio' => $horaInicioCompleto, 
    			'hora_fin' => $horaFinCompleto, 
    			'idmedico' => $row['idmedico'], 
    			'fecha_programada' => $row['fecha_programada'],
    			'idprogmedico' => $row['idprogmedico'],
    			'tipo_atencion_medica' => $row['tipo_atencion'] 
    		); 
    		if($this->model_prog_medico->m_verifica_medico($arrParams,TRUE) > 0){ 
    			$arrData['message'] = 'El médico ya tiene una programación en turno similar'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}

    		if($row['tipo_atencion'] == 'CM'){
	    		// VALIDAR QUE NO HAYA CUPOS RESERVADOS AL EDITAR EL AMBIENTE, FECHA, TURNO, INTERVALO 
	    		$fProg = $this->model_prog_medico->m_cargar_esta_programacion($row['idprogmedico']);
	    		$totalOcupados = $this->model_prog_medico->m_count_todos_cupos_ocupados($row['idprogmedico']);
	    		if( /*!($row['ambiente']['id'] == $fProg['idambiente']) || */
	    			!( strtotime($row['fecha_programada']) == strtotime($fProg['fecha_programada']) ) || 
	    			!( strtotime($horaInicioCompleto) == strtotime($fProg['hora_inicio']) ) || 
	    			!( strtotime($horaFinCompleto) == strtotime($fProg['hora_fin']) ) || 
	    			!( strtotime($intervalo) == strtotime($fProg['intervalo_hora']) ) 
	    		){ 

	    			if( $totalOcupados > 0){ 
						$arrData['message'] = 'Ya existen cupos ocupados para esta programación.'; 
			    		$arrData['flag'] = 0;
			    		$this->output
					    	->set_content_type('application/json')
					    	->set_output(json_encode($arrData));
					    return; 
	    			} 
	    		}

    			//VALIDAR EDITAR CUPOS ADICIONALES
    		
	    		if($this->model_prog_medico->m_count_cupos_adic_ocupados($row['idprogmedico']) > $row['cupos_adicionales']) {
	    			$arrData['message'] = 'La cantidad de cupos adicionales debe ser igual o mayor a las citas adicionales confirmadas.'; 
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
	    		}
	    	}
    	}    	
    	
    	$siRenombradoScc = NULL; 
    	// TRANSACCIONES 
    	$this->db->trans_start();
    	$arrProgMed = array();
    	$arrNotificaciones = array();
    	foreach ($allInputs['datos'] as $key => $row) { 
    		// PREPARACION DE DATOS 
	    		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
	    		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

	    		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
	    		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

	    		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00'; 
	    		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
		 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 
				if($row['tipo_atencion'] == 'CM'){
		 			$intervalo = '00:'.str_pad($row['intervalo_hora_int'],2,"0",STR_PAD_LEFT) . ':00'; 	
		 		}
    		// EDICION DE AMBIENTE Y RENOMBRADO DE CONSULTORIO 
	    		if( $row['si_renombrado_scc'] === TRUE ){ 
	    			$siRenombradoScc = 1;
	    			$idSubCategoriaRenom = $row['subcategoriarenom']['id'];
	    		}else{ 
	    			$siRenombradoScc = 2;
	    			$idSubCategoriaRenom = NULL;
	    		}

		    	$arrParams = array( 
		    		'idprogmedico'=> $row['idprogmedico'],
		    		'idambiente'=> $row['ambiente']['id'],
		    		'idsubcategoriaconsul'=> $row['ambiente']['idsubcategoriaconsul'],
		    		'si_renombrado_scc' => $siRenombradoScc,
		    		'idsubcategoriaconsulrenom' => $idSubCategoriaRenom,
		    		'fecha_programada' => $row['fecha_programada'],
		    		'hora_inicio' =>  $horaInicioCompleto,
		    		'hora_fin' => $horaFinCompleto,
		    		'intervalo_hora' => ($row['tipo_atencion'] == 'CM') ? $intervalo : NULL,
		    		'cupos_adicionales' => ($row['tipo_atencion'] == 'CM') ? $row['cupos_adicionales'] : 0 ,
		    		'comentario' => $row['comentario'],
		    		'activo' => $row['activo']
		    	); 

    		// antes de editar. 
			$fProg = $this->model_prog_medico->m_cargar_esta_programacion($row['idprogmedico']); 			
	    	if($this->model_prog_medico->m_editar_programacion($arrParams)){ 
	    		if($row['tipo_atencion'] == 'P'){
	    			$arrData['message'] = 'Se modificaron los datos correctamente(1)'; 
				    $arrData['flag'] = 1;
	    		}
	    		$horaInicioDet = strtotime($arrParams['hora_inicio']);
	    		$paraCorreo = array(
						'medico' => $row['medico'],
						'idmedico' => $row['idmedico'],
						'especialidad' => $row['especialidad'],
						'ambiente' => $row['ambiente']['numero_ambiente'],
						'sede' => $this->sessionHospital['sede'],
						'turno' => $row['turno'],
						'nuevo_turno' => 'De '. darFormatoHora($horaInicioCompleto) .' a ' . darFormatoHora($horaFinCompleto),
						'fecha_item' => $row['fecha_programada'],
						'fecha_old_item' => $fProg['fecha_programada'],
					);
	    		array_push($arrProgMed, $paraCorreo);

	    		if(!($fProg['idambiente'] == $arrParams['idambiente'])){
					$texto_notificacion = generar_notificacion_evento(9, 'key_prog_med', $paraCorreo);
					$data = array(
						'fecha_evento' => date('Y-m-d H:i:s'),
						'idresponsable' => $this->sessionHospital['idempleado'],
						'comentario' =>  null,				
						'idtipoevento' => 9,
						'identificador' => $row['idprogmedico'],
						'texto_notificacion' => $texto_notificacion,
						'texto_log' => $texto_notificacion,
					);	

					array_push($arrNotificaciones, array(
														'texto_notificacion'=> $texto_notificacion,
														'texto_log'=> $texto_notificacion,
														'identificador'=> $row['idprogmedico'],
														'idtipoevento'=> 9,
														));
					
					if(!$this->model_control_evento->m_registrar_evento($data)){
						$arrData['message'] = 'Ha ocurrido un error registrando el log. Tipo evento: MODIFICAR AMBIENTE';
						$arrData['flag'] = 0;
					}
				}

				if($row['tipo_atencion'] == 'CM'){	
			    	// SI SE MODIFICÓ UNO DE ESTOS VALORES, SE EDITA LOS CANALES. 
				    	if( 
				    		!( strtotime($row['fecha_programada']) == strtotime($fProg['fecha_programada']) ) || 
			    			!( strtotime($horaInicioCompleto) == strtotime($fProg['hora_inicio']) ) || 
			    			!( strtotime($horaFinCompleto) == strtotime($fProg['hora_fin']) ) || 
			    			!( strtotime($intervalo) == strtotime($fProg['intervalo_hora']) )  
			    		){ 
				    		// EDITAR CANALES 
		    				$sumTotalCupos = 0;
				    		foreach ($row['canales'] as $key => $rowCanal) { 
				    			$totalCupos = 0; 
				    			if($rowCanal['idcanal'] == 1){ // CAJA 
				    				$totalCupos = $row['total_cupos_master']; 
				    			}
				    			// SE RESETEA LOS CUPOS A "CAJA" 
								$arrParamsDet = array( 
									'idcanalprogmedico'=> $rowCanal['idcanalprogmedico'], 
									'total_cupos'=> $totalCupos 
								); 
								if( $this->model_prog_medico->m_editar_canales_de_programacion($arrParamsDet) ){ 
									$arrData['message'] = 'Se modificaron los datos correctamente(1)'; 
					    			$arrData['flag'] = 1;
								}
								$sumTotalCupos+= $rowCanal['total_cupos']; 
							}
							// REGISTRAR/EDITAR/ANULAR DETALLE DE PROGRAMACION 
							if( $row['total_cupos_master'] == $sumTotalCupos ){ // editamos 
								foreach ($row['todos_cupos'] as $keyCupo => $rowCupo) { 
									if( $rowCupo['si_adicional_bool'] == 2){ 
										$hora1 = new DateTime();
										$hora1->setTimestamp($horaInicioDet);							
										$horaFinDet = strtotime("+".$row['intervalo_hora_int']." minutes", $horaInicioDet);	
										$hora2 = new DateTime();
										$hora2->setTimestamp($horaFinDet);	
										$arrParamsCupo = array(
											'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'],
											'idcanal'=> 1, //CAJA 
											'hora_inicio_det'=> date_format($hora1,'H:i:s'),
											'hora_fin_det'=> date_format($hora2,'H:i:s'),
											'intervalo_hora_det'=> $arrParams['intervalo_hora']
										);
										if( $this->model_prog_medico->m_editar_detalle_de_programacion($arrParamsCupo) ){ 
											$arrData['message'] = 'Se modificaron los datos correctamente(2)'; 
							    			$arrData['flag'] = 1;
										}
										$horaInicioDet = $horaFinDet; 
									}
								}
							}elseif ( $row['total_cupos_master'] > $sumTotalCupos ) { // editamos y registramos 
								$cantCupos = 0;
								foreach ($row['todos_cupos'] as $keyCupo => $rowCupo) { 
									if( $rowCupo['si_adicional_bool'] == 2){
										$hora1 = new DateTime();
										$hora1->setTimestamp($horaInicioDet); 
										$horaFinDet = strtotime("+".$row['intervalo_hora_int']." minutes", $horaInicioDet);	
										$hora2 = new DateTime();
										$hora2->setTimestamp($horaFinDet);	

										// var_dump($horaInicioDet); 
										// var_dump($horaFinDet); 
										// var_dump($arrParams['intervalo_hora']); 
										// exit(); 
										$arrParamsCupo = array( 
											'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'],
											'idcanal'=> 1, //CAJA 
											'hora_inicio_det'=> date_format($hora1,'H:i:s'),
											'hora_fin_det'=> date_format($hora2,'H:i:s'),
											'intervalo_hora_det'=> $arrParams['intervalo_hora']
										);
										if( $this->model_prog_medico->m_editar_detalle_de_programacion($arrParamsCupo) ){ 
											$arrData['message'] = 'Se modificaron los datos correctamente(3)'; 
							    			$arrData['flag'] = 1;
										}
										$horaInicioDet = $horaFinDet; 
										$cantCupos +=1; 

									}
								}
								// registramos restantes 
								$restantes = $row['total_cupos_master'] - $sumTotalCupos; 
								$i = 1; 
								while ($i <= $restantes) { 
									$cantCupos +=1; 
									$hora1 = new DateTime();
									$hora1->setTimestamp($horaInicioDet);							
									$horaFinDet = strtotime("+".$row['intervalo_hora_int']." minutes", $horaInicioDet);	
									$hora2 = new DateTime();
									$hora2->setTimestamp($horaFinDet); 
									$arrParamsProgDet = array( 
										'idprogmedico'=> $arrParams['idprogmedico'],
										'idcanal'=> 1, //CAJA 
										'hora_inicio_det'=> date_format($hora1,'H:i:s'),
										'hora_fin_det'=> date_format($hora2,'H:i:s'),
										'intervalo_hora_det'=> $arrParams['intervalo_hora'],
										'createdAt'=> date('Y-m-d H:i:s'),
										'updatedAt'=> date('Y-m-d H:i:s'),
										'si_adicional'=> 2,
										'numero_cupo'=> $cantCupos 
									); 
									if( $this->model_prog_medico->m_registrar_detalle_prog_medico($arrParamsProgDet) ){ 
										$arrData['message'] = 'Se modificaron los datos correctamente(4)'; 
						    			$arrData['flag'] = 1; 
									}
									$horaInicioDet = $horaFinDet; 
									$i++; 
								} 
							}elseif ( $row['total_cupos_master'] < $sumTotalCupos ) { // editamos y anulamos 
								//$sobrantes = $totalCupos - $row['total_cupos_master']; 
								$i = 1;
								foreach ($row['todos_cupos'] as $keyCupo => $rowCupo) { 
									if( $rowCupo['si_adicional_bool'] == 2){
										if($i <= $row['total_cupos_master']){ 
											$hora1 = new DateTime();
											$hora1->setTimestamp($horaInicioDet);							
											$horaFinDet = strtotime("+".$row['intervalo_hora_int']." minutes", $horaInicioDet);	
											$hora2 = new DateTime();
											$hora2->setTimestamp($horaFinDet); 
											$arrParamsCupo = array( 
												'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'],
												'idcanal'=> 1, //CAJA 
												'hora_inicio_det'=> date_format($hora1,'H:i:s'),
												'hora_fin_det'=> date_format($hora2,'H:i:s'),
												'intervalo_hora_det'=> $arrParams['intervalo_hora']
											);
											if( $this->model_prog_medico->m_editar_detalle_de_programacion($arrParamsCupo) ){ 
												$arrData['message'] = 'Se modificaron los datos correctamente(5)'; 
								    			$arrData['flag'] = 1;
											}
											$horaInicioDet = $horaFinDet;
											
										}else{ 
											$arrParamsCupo = array( 
												'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'] 
											);
											if( $this->model_prog_medico->m_anular_detalle_de_programacion($arrParamsCupo) ){ 
												$arrData['message'] = 'Se modificaron los datos correctamente(6)'; 
								    			$arrData['flag'] = 1;
											}
										}
										$i++; 
									}
								} 
							}else{ 
								$arrData['message'] = 'Se modificaron los datos correctamente(7)(por aquí no debe pasar.)'; 
					    		$arrData['flag'] = 1; 
							} 

							if( !( strtotime($horaInicioCompleto) == strtotime($fProg['hora_inicio']) ) || 
				    			!( strtotime($horaFinCompleto) == strtotime($fProg['hora_fin']) ) ||
				    			!( strtotime($row['fecha_programada']) == strtotime($fProg['fecha_programada']))
				    			){
								$texto_notificacion = generar_notificacion_evento(4, 'key_prog_med', $paraCorreo);
								$data = array(
									'fecha_evento' => date('Y-m-d H:i:s'),
									'idresponsable' => $this->sessionHospital['idempleado'],
									'comentario' =>  null,				
									'idtipoevento' => 4,
									'identificador' => $row['idprogmedico'],
									'texto_notificacion' => $texto_notificacion,
									'texto_log' => $texto_notificacion,
								);

								array_push($arrNotificaciones, array(
																'texto_notificacion'=> $texto_notificacion,
																'texto_log'=> $texto_notificacion,
																'identificador'=> $row['idprogmedico'],
																'idtipoevento'=> 4,
																));

								if(!$this->model_control_evento->m_registrar_evento($data)){
									$arrData['message'] = 'Ha ocurrido un error registrando el log. Tipo evento: MODIFICAR TURNO';
									$arrData['flag'] = 0;
								}
			    			}

			    			if(strtotime($intervalo) != strtotime($fProg['intervalo_hora'])){
			    				$paraCorreo['total_cupos'] = $sumTotalCupos;
			    				$paraCorreo['intervalo'] = $row['intervalo_hora_int'];
								$texto_notificacion = generar_notificacion_evento(11, 'key_prog_med', $paraCorreo);
								$data = array(
									'fecha_evento' => date('Y-m-d H:i:s'),
									'idresponsable' => $this->sessionHospital['idempleado'],
									'comentario' =>  null,				
									'idtipoevento' => 11,
									'identificador' => $row['idprogmedico'],
									'texto_notificacion' => $texto_notificacion,
									'texto_log' => $texto_notificacion,
								);

								array_push($arrNotificaciones, array(
																'texto_notificacion'=> $texto_notificacion,
																'texto_log'=> $texto_notificacion,
																'identificador'=> $row['idprogmedico'],
																'idtipoevento'=> 11,
																));

								if(!$this->model_control_evento->m_registrar_evento($data)){
									$arrData['message'] = 'Ha ocurrido un error registrando el log. Tipo evento: MODIFICAR CUPOS/INTERVALO';
									$arrData['flag'] = 0;
								}
			    			}
								
				    	}else{ 
				    		$arrData['message'] = 'Se modificaron los datos correctamente(8)'; 
					    	$arrData['flag'] = 1; 
				    	} 
			    	
			    
			    	// CUPOS ADICIONALES 
			    	if( !($row['cupos_adicionales'] == $fProg['cupos_adicionales']) ){     		

			    		// var_dump($row['cupos_adicionales'],$fProg['cupos_adicionales']);
			    		if( $fProg['cupos_adicionales'] > $row['cupos_adicionales'] ){ // ANULAR 
			    			// var_dump('aqui0'/*,$row['todos_cupos']*/);
			    			$countAnulados= 0;
			    			$countNoAnulados= 0;
			    			foreach ($row['todos_cupos'] as $keyCupo => $rowCupo) {
			    				$cupo = $this->model_prog_medico->m_consulta_cupo($rowCupo['iddetalleprogmedico']);
			    				if($rowCupo['si_adicional_bool'] == 1 && 
			    					($cupo['estado_cupo'] == 2 || $cupo['estado_cupo'] == 3 ||  $cupo['estado_cupo'] == 4 ) && 
			    					$countAnulados < ($fProg['cupos_adicionales'] - $row['cupos_adicionales'])
			    					){ 
			    					$arrParamsCupo = array( 
										'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'] 
									); 
			    					if( $this->model_prog_medico->m_anular_detalle_de_programacion($arrParamsCupo) ){ 
										$arrData['message'] = 'Se modificaron los datos correctamente(9)'; 
						    			$arrData['flag'] = 1;
						    			// var_dump('adicional');
						    			$countAnulados++;
									}								
			    				}else if($rowCupo['si_adicional_bool'] == 1){
			    					$countNoAnulados++;
			    					$arrParamsCupo = array( 
										'iddetalleprogmedico'=> $rowCupo['iddetalleprogmedico'],
										'numero_cupo'=> $countNoAnulados
									); 
			    					$this->model_prog_medico->m_update_numero_cupo($arrParamsCupo);
			    				}
			    				// var_dump('foreach');
							}
			    		}elseif( $fProg['cupos_adicionales'] < $row['cupos_adicionales'] ){ // REGISTRAR 
			    			$ultimoCupo = $this->model_prog_medico->m_carga_turno_ultimo_cupo($arrParams['idprogmedico']);			    			
			    			$difCuposAdic = $row['cupos_adicionales'] - $fProg['cupos_adicionales']; 
			    			$contAdic = 1; 
			    			// var_dump('aqui1'); 
			    			while ( $contAdic <= $difCuposAdic) { 
			    				/*$intervaloHoraInt = date('i',strtotime($arrParams['intervalo_hora'])); 
			    				$horaInicioDet = strtotime("-".$intervaloHoraInt." minutes", strtotime($horaFinCompleto)); */
			    				$arrParamsProgDet = array( 
									'idprogmedico'=> $arrParams['idprogmedico'],
									'idcanal'=> 1, //CAJA 
									/*'hora_inicio_det'=> date('H:i:s',$horaInicioDet), 
									'hora_fin_det'=> $horaFinCompleto,*/ 
									'hora_inicio_det'=> $ultimoCupo['hora_inicio_det'], 
									'hora_fin_det'=> $ultimoCupo['hora_fin_det'], 
									'intervalo_hora_det'=> $arrParams['intervalo_hora'],
									'createdAt'=> date('Y-m-d H:i:s'),
									'updatedAt'=> date('Y-m-d H:i:s'),
									'si_adicional'=> 1,
									'numero_cupo'=> $fProg['cupos_adicionales'] + $contAdic
								); 
			    				if( $this->model_prog_medico->m_registrar_detalle_prog_medico($arrParamsProgDet) ){ 
			    					$arrData['message'] = 'Se modificaron los datos correctamente(10)'; 
						    		$arrData['flag'] = 1;
			    				}
			    				$contAdic++; 
			    			} 
			    		}else{
			    			// var_dump('aqui2'); 
			    		}

			    		$paraCorreo['cupos_adicionales'] = $row['cupos_adicionales'];
			    		$texto_notificacion = generar_notificacion_evento(5, 'key_prog_med', $paraCorreo);
						$data = array(
							'fecha_evento' => date('Y-m-d H:i:s'),
							'idresponsable' => $this->sessionHospital['idempleado'],
							'comentario' =>  null,				
							'idtipoevento' => 5,
							'identificador' => $row['idprogmedico'],
							'texto_notificacion' => $texto_notificacion,
							'texto_log' => $texto_notificacion,
						);

						array_push($arrNotificaciones, array(
															'texto_notificacion'=> $texto_notificacion,
															'texto_log'=> $texto_notificacion,
															'identificador'=> $row['idprogmedico'],
															'idtipoevento'=> 5,
															));

						if(!$this->model_control_evento->m_registrar_evento($data)){
							$arrData['message'] = 'Ha ocurrido un error registrando el log. Tipo evento: MODIFICAR CUPOS ADICIONALES';
							$arrData['flag'] = 0;
						}
			    	} 
			    	
			    	// LIMPIEZA DE CUPOS ADICIONALES 
			    	$listaCA = $this->model_prog_medico->m_cargar_cupos_de_programacion($arrParams['idprogmedico'],TRUE); 
			    	if( !empty($listaCA) ){ 
			    		$ultimoCupo = $this->model_prog_medico->m_carga_turno_ultimo_cupo($arrParams['idprogmedico']);
			    		foreach ($listaCA as $keyCA => $rowCUPA) { 
		    				$intervaloHoraInt = date('i',strtotime($rowCUPA['intervalo_hora']));
		    				$horaFinDet = strtotime($rowCUPA['hora_fin']); 
		    				$horaInicioDet = strtotime("-".$intervaloHoraInt." minutes", $horaFinDet); 
							$arrParamsCA = array( 
			    				'idcanal' => $rowCUPA['idcanal'],
			    				/*'hora_inicio_det' => date('H:i:s',$horaInicioDet),
			    				'hora_fin_det' => date('H:i:s',$horaFinDet),*/
			    				'hora_inicio_det'=> $ultimoCupo['hora_inicio_det'], 
								'hora_fin_det'=> $ultimoCupo['hora_fin_det'], 
			    				'intervalo_hora_det' => $rowCUPA['intervalo_hora'],
			    				'iddetalleprogmedico' => $rowCUPA['iddetalleprogmedico']
			    			);
				    		if( $this->model_prog_medico->m_editar_detalle_de_programacion($arrParamsCA) ){ 
				    			$arrData['message'] = 'Se modificaron los datos correctamente(11)'; 
						    	$arrData['flag'] = 1;
				    		}
				    	}
			    	}
		    	}
	    	}
	    	
    	}

    	$this->db->trans_complete();

    	if($arrData['flag'] == 1){
    		$flagMail = $this->envia_correo_medico(2, $arrProgMed);
			$arrData['flagMail'] = $flagMail;

			if($flagMail == 0)
				$arrData['messageMail'] = 'Notificación de correo NO enviada.';
			else if($flagMail == 1)
				$arrData['messageMail'] = 'Notificación de correo enviada exitosamente.';
			else if($flagMail == 2) 
				$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico invalido.';
			else if($flagMail == 3)				
				$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico no registrado.';

			$arrData['notificaciones'] = $arrNotificaciones;
    	}

    	$arrData['datos'] = $allInputs['tipoAtencion'];
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function editarProc(){
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$arrData['message'] = 'Ha ocurrido un error. Intente nuevamente';
		$arrData['flag'] = 0;
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		
    	foreach ($allInputs['datos'] as $key => $row) { 
    		// VALIDAR QUE EN CAMPOS NUMERICOS NO SE INGRESE TEXTO 
    		if( !(is_numeric($row['hora_inicio_edit'])) || !(is_numeric($row['minuto_inicio_edit'])) || !(is_numeric($row['hora_fin_edit'])) || !(is_numeric($row['minuto_fin_edit']))
    		){ 
    			$arrData['message'] = 'No se acepta texto en campos numéricos. Corregir y guardar nuevamente.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}
    		if( $row['hora_inicio_edit'] < 0 || $row['minuto_inicio_edit'] < 0 || $row['hora_fin_edit'] < 0 || 
    			$row['minuto_fin_edit'] < 0
    		){ 
    			$arrData['message'] = 'No se acepta numéricos negativos. Corregir y guardar nuevamente.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}

    		//VALIDAR FECHA PROGRAMADA 
    		if(strtotime($row['fecha_programada']) < strtotime(date("d-m-Y"))){
    			$arrData['message'] = 'No se permite ingresar programaciones con fechas pasadas.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
    		}

    		// VALIDAR SI ESTA RENOMBRADO Y TIENE SUB CATEGORIA ASIGNADA 
    		if( $row['si_renombrado_scc'] === TRUE ){ 
    			if( empty($row['subcategoriarenom']['id']) ){ 
    				$arrData['message'] = 'No se ha seleccionado categoria a renombrar.';
		    		$arrData['flag'] = 0;
		    		$this->output
				    	->set_content_type('application/json')
				    	->set_output(json_encode($arrData));
				    return;
    			}
    		}
    		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
    		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

    		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
    		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

    		
    		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00';
    		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
	 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 

	 		// VALIDAR SI EL AMBIENTE FECHA Y HORA DONDE SE ESTÁ TRASLADANDO ESTA INOPERATIVO. 
    		$arrParams = array( 
    			'idambiente'=> $row['ambiente']['id'], 
    			'fecha_programada'=> $row['fecha_programada'], 
    			'hora_inicio'=> $horaInicioCompleto, 
    			'hora_fin_comparar'=> $horaFinComparar
    		);
    		if($this->model_programacion_ambiente->m_verificar_operatividad_ambiente($arrParams) > 0){ 
    			$arrData['message'] = 'El ambiente N° '.$row['ambiente']['descripcion'].' está inoperativo para las fechas seleccionadas.'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		} 
    		// VALIDAR QUE NO HAYA PROGRAMACIÓN QUE SE CRUCE EN EL MISMO AMBIENTE CON SIMILAR TURNO - EDICION 
    		$arrParams = array( 
    			'hora_inicio'=> $horaInicioCompleto,
    			'hora_fin'=> $horaFinCompleto,
    			'idmedico'=> $row['idmedico'], 
    			'idambiente'=> $row['ambiente']['id'],
    			'fecha_programada'=> $row['fecha_programada'],
    			'idprogmedico'=> $row['idprogmedico'],
    			'tipo_atencion_medica' => $row['tipo_atencion']  
    		); 
    		if($this->model_prog_medico->m_verifica_planing_medico($arrParams,TRUE) > 0){
    			
				$arrData['message'] = 'No puede exitir dos médicos en el mismo ambiente mismo turno'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
			}
    		if($this->model_prog_medico->m_verifica_planing($arrParams,TRUE)>0){ 
    			$arrData['message'] = 'Ya existe programación en ese ambiente y en ese turno'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}

    		// VALIDAR QUE EL MEDICO NO ESTÉ PROGRAMADO PARA OTRO AMBIENTE EN LA MISMA HORA. - EDICION 
    		$arrParams = array( 
    			'hora_inicio'=> $horaInicioCompleto, 
    			'hora_fin'=> $horaFinCompleto, 
    			'idmedico'=> $row['idmedico'], 
    			'fecha_programada'=> $row['fecha_programada'],
    			'idprogmedico'=> $row['idprogmedico'],
    			'tipo_atencion_medica' => $row['tipo_atencion'] 
    		); 
    		if($this->model_prog_medico->m_verifica_medico($arrParams,TRUE) > 0){ 
    			$arrData['message'] = 'El médico ya tiene una programación en turno similar'; 
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return; 
    		}
    		

    	} 

    	$siRenombradoScc = NULL; 
    	// TRANSACCIONES 
    	$this->db->trans_start();
    	$arrProgMed = array();
    	$arrNotificaciones = array();
    	foreach ($allInputs['datos'] as $key => $row) { 
    		// PREPARACION DE DATOS 
    		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
    		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

    		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
    		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

    		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00'; 
    		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
	 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 

    		// EDICION DE AMBIENTE Y RENOMBRADO DE CONSULTORIO 
    		if( $row['si_renombrado_scc'] === TRUE ){ 
    			$siRenombradoScc = 1;
    			$idSubCategoriaRenom = $row['subcategoriarenom']['id'];
    		}else{ 
    			$siRenombradoScc = 2;
    			$idSubCategoriaRenom = NULL;
    		}
	    	$arrParams = array( 
	    		'idprogmedico'=> $row['idprogmedico'],
	    		'idambiente'=> $row['ambiente']['id'],
	    		'idsubcategoriaconsul'=> $row['ambiente']['idsubcategoriaconsul'],
	    		'si_renombrado_scc' => $siRenombradoScc,
	    		'idsubcategoriaconsulrenom' => $idSubCategoriaRenom,
	    		'fecha_programada' => $row['fecha_programada'],
	    		'hora_inicio' =>  $horaInicioCompleto,
	    		'hora_fin' => $horaFinCompleto,
	    		'intervalo_hora' => NULL,
	    		'cupos_adicionales' => 0,
	    		'comentario' => $row['comentario'],
	    		'activo' => $row['activo']
	    	); 

    		// antes de editar. 
			$fProg = $this->model_prog_medico->m_cargar_esta_programacion($row['idprogmedico']); 			
	    	if($this->model_prog_medico->m_editar_programacion($arrParams)){ 
	    		$arrData['message'] = 'Se modificaron los datos correctamente'; 
			    $arrData['flag'] = 1;
	    		$horaInicioDet = strtotime($arrParams['hora_inicio']);
	    		$paraCorreo = array(
						'medico' => $row['medico'],
						'idmedico' => $row['idmedico'],
						'especialidad' => $row['especialidad'],
						'ambiente' => $row['ambiente']['numero_ambiente'],
						'sede' => $this->sessionHospital['sede'],
						'turno' => $row['turno'],
						'nuevo_turno' => 'De '. darFormatoHora($horaInicioCompleto) .' a ' . darFormatoHora($horaFinCompleto),
						'fecha_item' => $row['fecha_programada'],
						'fecha_old_item' => $fProg['fecha_programada'],
					);
	    		array_push($arrProgMed, $paraCorreo);

	    		if(!($fProg['idambiente'] == $arrParams['idambiente'])){
					$texto_notificacion = generar_notificacion_evento(9, 'key_prog_med', $paraCorreo);
					$data = array(
						'fecha_evento' => date('Y-m-d H:i:s'),
						'idresponsable' => $this->sessionHospital['idempleado'],
						'comentario' =>  null,				
						'idtipoevento' => 9,
						'identificador' => $row['idprogmedico'],
						'texto_notificacion' => $texto_notificacion,
						'texto_log' => $texto_notificacion,
					);	

					array_push($arrNotificaciones, array(
														'texto_notificacion'=> $texto_notificacion,
														'texto_log'=> $texto_notificacion,
														'identificador'=> $row['idprogmedico'],
														'idtipoevento'=> 9,
														));
					
					if(!$this->model_control_evento->m_registrar_evento($data)){
						$arrData['message'] = 'Ha ocurrido un error registrando el log. Tipo evento: MODIFICAR AMBIENTE';
						$arrData['flag'] = 0;
					}
				}
		    	
		    	
	    	}
	    	
    	}

    	$this->db->trans_complete();
    	if($arrData['flag'] == 1){
    		$flagMail = $this->envia_correo_medico(2, $arrProgMed);
			$arrData['flagMail'] = $flagMail;
			$arrData['notificaciones'] = $arrNotificaciones;
    	}
    	$arrData['datos'] = $allInputs['tipoAtencion'];

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	
	}
	public function calcular_bloques_horas(){
		$datos= json_decode(trim($this->input->raw_input_stream),true);		 
		$size = count($datos);	
		$arrListado = array();
		if ($size > 0) {
			if($size > 1){
				$i = 0;
				foreach($datos as $value) {
					if($i > 0){				
						$actual = floatval ($value['numero']);
						//print_r($actual);
						if($actual == ($anterior +0.5) ){
							$anterior = $anterior + 0.5;							
						}else{

							$formato_inicio = $datos[$inicio]['hora']; 				 		

							$segundos_horaInicial=strtotime($datos[$i-1]['hora']); 
							$segundos_minutoAnadir=30*60; 
							$formato_fin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

							array_push($arrListado, 
								array(
									'inicio' => $formato_inicio,
									'fin' => $formato_fin,
									'formato_inicio' => darFormatoHora($formato_inicio),
									'formato_fin' => darFormatoHora($formato_fin),
									'cantidad_horas' => (($datos[$i-1]['numero'] + 0.5)- $datos[$inicio]['numero'])											
								)
							);	

							$anterior = $actual;
							$inicio = $i;							
						}
						

						if($i == ($size-1)){
							$formato_inicio = $datos[$inicio]['hora']; 				 		

							$segundos_horaInicial=strtotime($value['hora']); 
							$segundos_minutoAnadir=30*60; 
							$formato_fin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

							array_push($arrListado, 
								array(
									'inicio' => $formato_inicio,
									'fin' => $formato_fin,
									'formato_inicio' => darFormatoHora($formato_inicio),
									'formato_fin' => darFormatoHora($formato_fin),
									'cantidad_horas' => (($value['numero'] + 0.5)- $datos[$inicio]['numero'])											
								)
							);	

						}
					}else{
						$anterior = floatval ($value['numero']);
						$inicio = 0;
					}
					$i++;

				}
			}else{
				$formato_inicio = $datos[0]['hora']; 							 		

				$segundos_horaInicial=strtotime($formato_inicio); 
				$segundos_minutoAnadir=30*60; 
				$formato_fin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

				array_push($arrListado, 
						array(
							'inicio' => $formato_inicio,
							'fin' => $formato_fin,
							'formato_inicio' => darFormatoHora($formato_inicio),
							'formato_fin' => darFormatoHora($formato_fin),
							'cantidad_horas' => (0.5)											
						)
					);
			}
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

	public function ver_popup_canales(){
		$this->load->view('prog-medico/canalesProgMedico_formView');
	}
	
	public function ver_popup_medico(){
		$this->load->view('prog-medico/medicosProgMedico_formView');
	}

	public function ver_popup_confirmacion(){
		$this->load->view('prog-medico/confirmacionProgMedico_formView');
	}

	public function ver_popup_anular_programacion(){
		$this->load->view('prog-medico/anularProgMedico_formView');
	}

	public function anular(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Los datos no han podido ser anulados. Intente nuevamente';
    	$arrData['flag'] = 0;

    	if($this->model_prog_medico->m_verificar_cupos_programacion($allInputs) > 0){
    		$arrData['message'] = 'No puede Anular Programación con cupos ocupados.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$this->db->trans_start();
    	$arrNotificaciones = array();
		if($this->model_prog_medico->m_anular($allInputs)){
			$allInputs['estado_cupo'] = 0;
			if($this->model_prog_medico->m_cambiar_estado_todo_detalle_prog($allInputs)){
				$texto_notificacion = generar_notificacion_evento(2, 'key_prog_med', $allInputs);
				$data = array(
					'fecha_evento' => date('Y-m-d H:i:s'),
					'idresponsable' => $this->sessionHospital['idempleado'],
					'comentario' =>  $allInputs['comentario_anular'],				
					'idtipoevento' => 2,
					'identificador' => $allInputs['idprogmedico'],
					'texto_notificacion' => $texto_notificacion,
					'texto_log' => $texto_notificacion,
					);
				array_push($arrNotificaciones, $data);
				if($this->model_control_evento->m_registrar_evento($data)){
					$arrData['message'] = 'Se anulo la Programación de Médico correctamente';
	    			$arrData['flag'] = 1;
	    			$arrData['notificaciones'] = $arrNotificaciones;
				}  
			}			  		
    	}
		$this->db->trans_complete();    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anularProc(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Los datos no han podido ser anulados. Intente nuevamente';
    	$arrData['flag'] = 0;
    	
    	if($this->model_prog_medico->m_verificar_ventas($allInputs) > 0){
    		$arrData['message'] = 'No puede Anular Programación con ventas realizadas.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$this->db->trans_start();
    	$arrNotificaciones = array();
		if($this->model_prog_medico->m_anular($allInputs)){
			$arrData['message'] = 'Se anulo la Programación de Médico correctamente';
			$arrData['flag'] = 1;				  		
    	}
		$this->db->trans_complete();    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function ver_popup_programaciones(){
		$this->load->view('prog-medico/programacionesMedico_formView');
	}

	public function ver_popup_comentario(){
		$this->load->view('prog-medico/verComentarioProgramacion_formView');
	}	

	public function listar_programaciones_por_estado(){
		ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		// var_dump($allInputs['ids']); exit();
		
		$lista = $this->model_prog_medico->m_cargar_programaciones_por_estado($allInputs['datos'], $allInputs['paginate']); 
	

		// AGRUPAMIENTO POR PROGRAMACION 
		$arrList = array(); 
		foreach ($lista as $key => $row) { 
			$rowHoraInicio = $row['hora_inicio']; 
			$rowHoraFin = $row['hora_fin'];
			$rowHoraInicio = date('H:i a',strtotime($rowHoraInicio));
			$rowHoraFin = date('H:i a',strtotime($rowHoraFin));
			$rowTipoAtencion = NULL;
			if($row['tipo_atencion_medica'] == 'CM'){ 
				$rowTipoAtencion = 'CONSULTA MÉDICA';
			}
			$timestamp = strtotime($row['intervalo_hora']); 
			$intervaloHoraInt = date('i', $timestamp);
			$tsFechaProg = strtotime($row['fecha_programada']);
			$rowFechaProgramada = date('d-m-Y',$tsFechaProg);

			$arrTemp = array( 
				'idprogmedico'=> $row['idprogmedico'],
				'fecha_programada'=> $rowFechaProgramada,

				'hora_inicio'=> $rowHoraInicio,
				'hora_fin'=> $rowHoraFin,
				'intervalo_hora'=> $row['intervalo_hora'],
				'intervalo_hora_int'=> $intervaloHoraInt,
				'cupos_adicionales'=> $row['cupos_adicionales'],
				'tipo_atencion_medica'=> $rowTipoAtencion,
				'comentario'=> $row['comentario_pmed'],
				'ambiente'=> array(
					'id'=> $row['idambiente'],
					'descripcion'=> $row['numero_ambiente'],
					'numero_ambiente'=> $row['numero_ambiente'],
					'piso'=> $row['piso'],
				),
				'idmedico'=> $row['idmedico'],
				'medico'=> $row['med_nombres'].' '.$row['med_apellido_paterno'].' '.$row['med_apellido_materno'],
				'idespecialidad'=> $row['idespecialidad'],
				'especialidad'=> $row['nombre'],
				'idempresa'=> $row['idempresa'],
				'empresa'=> $row['empresa'],
					
				'turno'=> 'De '.$rowHoraInicio.' a '.$rowHoraFin, 
				'total_cupos_master'=> $row['total_cupos'],
				'total_cupos_ocupados'=> $row['total_cupos_ocupados'],
				'estado_prm'=> $row['estado_prm'],
			);
			array_push($arrList, $arrTemp);
		}


		$numRows = $this->model_prog_medico->m_count_programaciones_por_estado($allInputs['datos'], $allInputs['paginate']); 


		$arrData['datos'] = $arrList; 
		$arrData['paginate']['totalRows'] = $numRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_datos_comentarios(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$lista = $this->model_prog_medico->m_cargar_datos_comentarios($allInputs); 
		 
		$arrPrincipal = array(); 
		foreach ($lista as $row) {
			array_push($arrPrincipal, 
				array(
					'idcontrolevento' => $row['idcontrolevento'], 
					'comentario' => strtoupper($row['comentario']),
					'fecha_evento' => date('d-m-Y', strtotime($row['fecha_evento'])),
					'nombres' => strtoupper($row['nombres']),
					'apellido_paterno' => strtoupper($row['apellido_paterno']),
					'apellido_materno' => strtoupper($row['apellido_materno'])
				)
			);
		}

		$arrData['datos'] = $arrPrincipal; 
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No se han encontrado comentarios';
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function verificar_cupos_programacion(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['flag'] = 0;
		$arrData['message'] = 'Sólo puede cancelar una Programación con cupos ocupados.';		
		if($this->model_prog_medico->m_verificar_cupos_programacion($allInputs) > 0){
    		$arrData['message'] = 'Se puede cancelar';
    		$arrData['flag'] = 1;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}		

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function registrar_reprogramacion(){
		$row = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
	    $arrData['flag'] = 0;
	    $arrProgMed = array();
	    // VALIDAR QUE EN CAMPOS NUMERICOS NO SE INGRESE TEXTO 
		if( !(is_numeric($row['hora_inicio_edit'])) || !(is_numeric($row['minuto_inicio_edit'])) || !(is_numeric($row['hora_fin_edit'])) || 
			!(is_numeric($row['minuto_fin_edit'])) || !(is_numeric($row['intervalo_hora_int'])) || !(is_numeric($row['cupos_adicionales'])) 
			|| !(is_numeric($row['total_cupos_master']))
		){ 
			$arrData['message'] = 'No se acepta texto en campos numéricos. Corregir y guardar nuevamente.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
		}
		if( $row['hora_inicio_edit'] < 0 || $row['minuto_inicio_edit'] < 0 || $row['hora_fin_edit'] < 0 || 
			$row['minuto_fin_edit'] < 0 || $row['intervalo_hora_int'] < 0 || $row['cupos_adicionales'] < 0  || 
			$row['total_cupos_master'] < 0
		){ 
			$arrData['message'] = 'No se acepta numéricos negativos. Corregir y guardar nuevamente.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
		}

		//VALIDAR ESPECIALIDAD SEDE (tiene_prog_cita )
		if(!$this->model_especialidad->m_tiene_prog_asistencial($row)){
			$arrData['message'] = 'Debe HABILITAR programación asistencial para la especialidad: ' . $row['especialidad']  . ' en la Sede: ' . $this->sessionHospital['sede']; 
    		$arrData['flag'] = 2;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		//VALIDAR FECHA PROGRAMADA 
		if(strtotime($row['fecha_programada']) < strtotime(date("d-m-Y"))){
			$arrData['message'] = 'No se permite ingresar programaciones con fechas pasadas.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return;
		}

		// VALIDAR SI ESTA RENOMBRADO Y TIENE SUB CATEGORIA ASIGNADA 
		if( $row['si_renombrado_scc'] === TRUE ){ 
			if( empty($row['subcategoriarenom']['id']) ){ 
				$arrData['message'] = 'No se ha seleccionado categoria a renombrar.';
	    		$arrData['flag'] = 0;
	    		$this->output
			    	->set_content_type('application/json')
			    	->set_output(json_encode($arrData));
			    return;
			}
		}
		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

		
		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00';
		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 

 		$intervalo = '00:'.str_pad($row['intervalo_hora_int'],2,"0",STR_PAD_LEFT) . ':00'; 
 		// VALIDAR SI EL AMBIENTE FECHA Y HORA DONDE SE ESTÁ TRASLADANDO ESTA INOPERATIVO. 
		$arrParams = array( 
			'idambiente'=> $row['ambiente']['id'], 
			'fecha_programada'=> $row['fecha_programada'], 
			'hora_inicio'=> $horaInicioCompleto, 
			'hora_fin_comparar'=> $horaFinComparar
			
		);
		if($this->model_programacion_ambiente->m_verificar_operatividad_ambiente($arrParams) > 0){ 
			$arrData['message'] = 'El ambiente N° '.$row['ambiente']['descripcion'].' está inoperativo para las fechas seleccionadas.'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
		} 

		// VALIDAR QUE EL MEDICO NO ESTÉ PROGRAMADO PARA OTRO AMBIENTE EN LA MISMA HORA. - INSERT 
		$arrParams = array( 
			'hora_inicio'=> $horaInicioCompleto, 
			'hora_fin'=> $horaFinCompleto, 
			'idmedico'=> $row['idmedico'], 
			'fecha_programada'=> $row['fecha_programada'],
			'idprogmedico'=> $row['idprogmedico'] 
		); 
		if($this->model_prog_medico->m_verifica_medico($arrParams,FALSE) > 0){ 
			$arrData['message'] = 'El médico ya tiene una programación en turno similar'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
		}
		// VALIDAR QUE NO HAYA PROGRAMACIÓN QUE SE CRUCE EN EL MISMO AMBIENTE CON SIMILAR TURNO - INSERT 
		$arrParams = array( 
			'hora_inicio'=> $horaInicioCompleto,
			'hora_fin'=> $horaFinCompleto,
			'idmedico'=> $row['idmedico'], 
			'idambiente'=> $row['ambiente']['id'],
			'fecha_programada'=> $row['fecha_programada'],
			'idprogmedico'=> $row['idprogmedico']  
		); 
		if($this->model_prog_medico->m_verifica_planing($arrParams,FALSE)>0){ 
			$arrData['message'] = 'Ya existe programación en ese ambiente y en ese turno'; 
    		$arrData['flag'] = 0;
    		$this->output
		    	->set_content_type('application/json')
		    	->set_output(json_encode($arrData));
		    return; 
		}

		$siRenombradoScc = NULL; 
    	// TRANSACCIONES 
    	$this->db->trans_start();
		// PREPARACION DE DATOS 
		$horaInicioEdit = str_pad($row['hora_inicio_edit'],STR_PAD_LEFT); 
		$minutoInicioEdit = str_pad($row['minuto_inicio_edit'],STR_PAD_LEFT); 

		$horaFinEdit = str_pad($row['hora_fin_edit'],STR_PAD_LEFT); 
		$minutoFinEdit = str_pad($row['minuto_fin_edit'],STR_PAD_LEFT); 

		$horaInicioCompleto = $horaInicioEdit.':'.$minutoInicioEdit.':00'; 
		$horaFinCompleto = $horaFinEdit.':'.$minutoFinEdit.':00'; 
 		$horaFinComparar = str_pad($row['hora_fin_edit']-1, 2, "0",STR_PAD_LEFT) . ':00:00'; 

 		$intervalo = '00:'.str_pad($row['intervalo_hora_int'],2,"0",STR_PAD_LEFT) . ':00'; 

		// EDICION DE AMBIENTE Y RENOMBRADO DE CONSULTORIO 
		if( $row['si_renombrado_scc'] === TRUE ){ 
			$siRenombradoScc = 1;
			$idSubCategoriaRenom = $row['subcategoriarenom']['id'];
		}else{ 
			$siRenombradoScc = 2;
			$idSubCategoriaRenom = NULL;
		}

    	$data = array( 
					'fecha_programada' =>  $row['fecha_programada'],
					'hora_inicio' => $horaInicioCompleto,
					'hora_fin' => $horaFinCompleto,
					'intervalo_hora' =>   $intervalo,
					'cupos_adicionales' =>  $row['cupos_adicionales'],
					'comentario_pmed' => $row['comentario'],
					'si_renombrado_scc' => $siRenombradoScc,
					'idempresamedico' => $row['idempresamedico'],
					'idmedico' => $row['idmedico'],
					'idambiente' => $row['ambiente']['id'],
					'idespecialidad' => $row['idespecialidad'],
					'idsubcategoriaconsul' =>  $row['ambiente']['idsubcategoriaconsul'],
					'idsubcategoriaconsulrenom' => $idSubCategoriaRenom,
					'idsede' => $this->sessionHospital['idsede'],
					'idprogmedico_old' => $row['idprogmedico'],
				);

    	if($this->model_prog_medico->m_registrar_prog_medico($data, true)){
    		$error = FALSE;
    		$idprogmedico = GetLastId('idprogmedico','pa_prog_medico');
			$horaInicioDet = strtotime($row['hora_inicio']); 	
			$numero_cupo = 1;
			$paraCorreo = array(
				'medico' => $row['medico'],
				'idmedico' => $row['idmedico'],
				'especialidad' => $row['especialidad'],
				'ambiente' => $row['ambiente']['numero_ambiente'],
				'sede' => $this->sessionHospital['sede'],
				'turno' => $row['turno'],
				'fecha_item' => $row['fecha_programada'],
			);
			array_push($arrProgMed, $paraCorreo);
    		$listaCanales = $this->model_canal->m_cargar_canal_cbo();
    		foreach ($listaCanales as $rowCanal) {
    			if($rowCanal['idcanal'] == 1){ //igual a caja
    				$canal = array(
	    				'id' => $rowCanal['idcanal'],
	    				'cant_cupos_canal' => $row['total_cupos_master']
	    			);
	    			if($this->model_prog_medico->m_registrar_canal_prog_medico($canal, $idprogmedico)){							
						for($i = 0; $i< $canal['cant_cupos_canal']; $i++){
							$hora1 = new DateTime();
							$hora1->setTimestamp($horaInicioDet);							
							$horaFinDet = strtotime("+".$row['intervalo_hora_int']." minutes", $horaInicioDet);	
							$hora2 = new DateTime();
							$hora2->setTimestamp($horaFinDet);	
							
							$data2 = array(
								'idprogmedico' => $idprogmedico,
								'idcanal' => $rowCanal['idcanal'],
								'hora_inicio_det' => date_format($hora1,'H:i:s'),
								'hora_fin_det' => date_format($hora2,'H:i:s'),
								'intervalo_hora_det' => $intervalo,
								'numero_cupo' => $numero_cupo,
								'si_adicional' => 2, //no es adicional
							);
							$numero_cupo++;
							$horaInicioDet = $horaFinDet;
							if(!$this->model_prog_medico->m_registrar_detalle_prog_medico ($data2)){
								$error = TRUE;
							}
						}
					}
    			}else{
    				$canal = array(
	    				'id' => $rowCanal['idcanal'],
	    				'cant_cupos_canal' => 0
	    			);
	    			if(!$this->model_prog_medico->m_registrar_canal_prog_medico($canal, $idprogmedico)){	
	    				$error = TRUE;
	    			}
    			}
    		}

		    $numero_cupo=1;
			//CUPOS ADICIONALES
			while ($numero_cupo <= $row['cupos_adicionales']) {
    			$data2 = array(
					'idprogmedico' => $idprogmedico,
					'idcanal' => 1, //por defecto caja
					'hora_inicio_det' => date_format($hora1,'H:i:s'),
					'hora_fin_det' => date_format($hora2,'H:i:s'),
					'intervalo_hora_det' => $intervalo,
					'si_adicional' => 1, //es adicional,
					'numero_cupo' => $numero_cupo
				);
				$numero_cupo++;					
				if(!$this->model_prog_medico->m_registrar_detalle_prog_medico($data2)){
					$error = TRUE;
				}
    		}
    		if(!$error){
    			$arrData['message'] = 'Reprogramación registrada correctamente.'; 
    			$arrData['flag'] = 1;
    			$flagMail = $this->envia_correo_medico(1, $arrProgMed);
				$arrData['flagMail'] = $flagMail;

				if($flagMail == 0)
					$arrData['messageMail'] = 'Notificación de correo NO enviada.';
				else if($flagMail == 1)
					$arrData['messageMail'] = 'Notificación de correo enviada exitosamente.';
				else if($flagMail == 2) 
					$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico invalido.';
				else if($flagMail == 3)				
					$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico no registrado.';
	    	}
    		
    	}
    	$this->db->trans_complete();
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_cancelar_programacion(){
		$this->load->view('prog-medico/cancelarProgMedico_formView');
	}

	public function cancelar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'La programación no ha podido ser cancelada. Intente nuevamente';
    	$arrData['flag'] = 0;

    	if(!$this->model_prog_medico->m_verificar_cupos_programacion($allInputs) > 0){
    		$arrData['message'] = 'La programación no puede ser cancelada. Debe tener al menos 1 cupo ocupado';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	if($this->model_prog_medico->m_verificar_cupos_atencion($allInputs) > 0){
    		$arrData['message'] = 'La programación no puede ser cancelada. No debe tener atenciones médicas registradas';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$arrProgMed = array();
    	$arrNotificaciones = array();
    	$this->db->trans_start();
		if($this->model_prog_medico->m_cancelar($allInputs)){
			$allInputs['estado_cupo'] = 3;
			if($this->model_prog_medico->m_cambiar_estado_todo_detalle_prog($allInputs)){				

				$data = array(
					'idprogmedico' => $allInputs['idprogmedico'],
					'estado_cita' => 3
				);
				$resultCitas = $this->model_prog_cita->m_cambiar_estado_todas_cita_prog($data); 

				$texto_notificacion = generar_notificacion_evento(3, 'key_prog_med', $allInputs);
				$data = array(
					'fecha_evento' => date('Y-m-d H:i:s'),
					'idresponsable' => $this->sessionHospital['idempleado'],
					'comentario' =>  $allInputs['comentario_cancelar'],				
					'idtipoevento' => 3,
					'identificador' => $allInputs['idprogmedico'],
					'texto_notificacion' => $texto_notificacion,
					'texto_log' => $texto_notificacion,
					);
				array_push($arrNotificaciones, $data);

				$paraCorreo = array(
					'medico' => $allInputs['medico'],
					'idmedico' => $allInputs['idmedico'],
					'especialidad' => $allInputs['especialidad'],
					'ambiente' => $allInputs['ambiente']['numero_ambiente'],
					'sede' => $this->sessionHospital['sede'],
					'turno' => $allInputs['turno'],
					'fecha_item' => $allInputs['fecha_programada'],
				);
				array_push($arrProgMed, $paraCorreo);
				
				if($resultCitas && $this->model_control_evento->m_registrar_evento($data)){
					$arrData['message'] = 'Se Cancelo la Programación de Médico correctamente';
	    			$arrData['flag'] = 1;
	    			$flagMail = $this->envia_correo_medico(3, $arrProgMed);
					$arrData['flagMail'] = $flagMail;

					if($flagMail == 0)
						$arrData['messageMail'] = 'Notificación de correo NO enviada.';
					else if($flagMail == 1)
						$arrData['messageMail'] = 'Notificación de correo enviada exitosamente.';
					else if($flagMail == 2) 
						$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico invalido.';
					else if($flagMail == 3)				
						$arrData['messageMail'] = 'Notificación de correo NO enviada. Correo de Médico no registrado.';

					$arrData['notificaciones'] = $arrNotificaciones;
				}
			}			    		
    	}
		$this->db->trans_complete();    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_cupos_por_canales(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    	$idprogmedico = $allInputs['idprogmedico'];
		$listaCupos = $this->model_prog_medico->m_cargar_cupos_por_canales($idprogmedico);
		$totalRows = $this->model_prog_medico->m_contar_cupos_por_canales($idprogmedico);
		$arrListado = array();
		$total_cupos_ocupados = 0;
		$total_cupos_disponibles = 0;
		$total_cupos_adi_ocupados = 0;
		$total_cupos_adi_disponibles = 0;
		foreach ($listaCupos as $row) {
			array_push($arrListado, 
				array(
					'idcanalprogmedico' => $row['idcanalprogmedico'],
					'descripcion' => strtoupper($row['descripcion_can']),
					'idprogmedico' => $row['idprogmedico'],
					'idcanal' => $row['idcanal'],
					'total_cupos' => $row['total_cupos'],
					'cupos_disponibles' => $row['cupos_disponibles'],
					'cupos_ocupados' => $row['cupos_ocupados'],
					'total_cupos_adicionales' => $row['total_cupos_adicionales'],
					'cupos_adicionales_ocupados' => $row['cupos_adicionales_ocupados'],
					'cupos_adicionales_disponibles' => $row['total_cupos_adicionales'] - $row['cupos_adicionales_ocupados'],
				)
			);
			$total_cupos_ocupados +=  $row['cupos_ocupados'];
			$total_cupos_disponibles +=  $row['cupos_disponibles'];
			$total_cupos_adi_ocupados +=  $row['cupos_adicionales_ocupados'];
			$total_cupos_adi_disponibles +=  ($row['total_cupos_adicionales'] - $row['cupos_adicionales_ocupados']);
		}

		$arrData['datos'] = $arrListado;
		$arrData['paginate']['totalRows'] = $totalRows;
		$arrData['total_cupos_ocupados'] = $total_cupos_ocupados;
		$arrData['total_cupos_disponibles'] = $total_cupos_disponibles;
		$arrData['total_cupos_adi_ocupados'] = $total_cupos_adi_ocupados;
		$arrData['total_cupos_adi_disponibles'] = $total_cupos_adi_disponibles;
		$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_gestion_cupos(){
		$this->load->view('prog-medico/gestionCupos_formView');
	}	

	public function guardar_gestion_cupos(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
		
		$conteoCupos = $this->model_prog_medico->m_count_cupos_canal($allInputs[0]['idprogmedico']);
		$error = FALSE;
		foreach ($conteoCupos as $ind => $canalBD) {			
			foreach ($allInputs as $key => $canal) {				
				if($canal['idcanal'] == $canalBD['idcanal']){
					if($canal['total_cupos'] < $canalBD['total_cupos_ocupados']){
						$error = TRUE;
					}

					if($canal['total_cupos_adicionales'] < $canalBD['total_cupos_ocupados_adi']){
						$error = TRUE;
					}
				}
			}
		}

		if($error){
			$arrData['message'] = 'Error al registrar los datos. Total de cupos no debe ser menor a total cupos ocupados.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		}

		$listaCupos = $this->model_prog_medico->m_listar_todos_cupos($allInputs[0]['idprogmedico'] , false);
		$size = count($listaCupos);
		$ind = 0;
		$error = FALSE;
		/*print_r($listaCupos);
		exit();	*/		
			
		//asigna canal cupos disponibles y no adicionales
		$this->db->trans_start();
		foreach ($allInputs as $key => $canal) {
			$data = array(
				'cupos_disponibles' => intval($canal['cupos_disponibles']),
				'total_cupos' => intval($canal['total_cupos']),				
				'idcanalprogmedico' => intval($canal['idcanalprogmedico']),
				'idcanal' => intval($canal['idcanal']),
				'idprogmedico' => intval($canal['idprogmedico']),				
				'cupos_adicionales_disponibles' => intval($canal['cupos_adicionales_disponibles']),		
				);
			
			$count = 0;
			while($ind < $size && $count < $canal['total_cupos']){
				if($listaCupos[$ind]['estado_cupo'] = 2 && $listaCupos[$ind]['si_adicional'] == 2 ){
					$data['iddetalleprogmedico'] = $listaCupos[$ind]['iddetalleprogmedico'];
					if(!$this->model_prog_medico->m_guardar_gestion_cupos_detalle($data)){ 
						$error = TRUE;
					}
				}
				$ind++;	
				$count++;		
			}	

		}

		$listaCupos = $this->model_prog_medico->m_listar_todos_cupos($allInputs[0]['idprogmedico'], true);
		$size = count($listaCupos);
		$ind = 0;
		//asigna canal cupos disponibles y adicionales
		foreach ($allInputs as $key => $canal) {
			$data = array(
				'cupos_disponibles' => intval($canal['cupos_disponibles']),
				'total_cupos' => intval($canal['total_cupos']),				
				'idcanalprogmedico' => intval($canal['idcanalprogmedico']),
				'idcanal' => intval($canal['idcanal']),
				'idprogmedico' => intval($canal['idprogmedico']),				
				'cupos_adicionales_disponibles' => intval($canal['cupos_adicionales_disponibles']),		
				);
			$count = 0;
			while($ind < $size && $count < $canal['total_cupos_adicionales']){
				if($listaCupos[$ind]['estado_cupo'] = 2 && $listaCupos[$ind]['si_adicional'] == 1 ){
					$data['iddetalleprogmedico'] = $listaCupos[$ind]['iddetalleprogmedico'];
					if($error  || !$this->model_prog_medico->m_guardar_gestion_cupos_detalle($data)){ 
						$error = TRUE;
					}
				}
				$ind++;	
				$count++;		
			}	

			if($error || !$this->model_prog_medico->m_guardar_gestion_cupos_canal($data)){ 
				$error = TRUE;
			}		

		}

		

		$this->db->trans_complete();
		if(!$error){
			$arrData['message'] = 'Se guardo la Gestión de Cupos correctamente';
    		$arrData['flag'] = 1;
		}
		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_planing_horas_medicos(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['hasta'] = $allInputs['desde'];
		$allInputs['itemMedico'] = null;
		$allInputs['itemAmbiente'] = null;
		$allInputs['itemEspecialidad'] = null;

		$sede = $this->model_sede->m_consultar($this->sessionHospital['idsede']);

		$number = intval(explode(":",$sede['hora_final_atencion'])[0]);
		$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';
		$horas = get_rangohoras($sede['hora_inicio_atencion'], $hora_fin);
		
		$datos = array('anyo' => date("Y"));
		$feriados = $this->model_feriado->m_lista_feriados_cbo($datos); 
		$arrFeriados = array();
		foreach ($feriados as $row) {
			array_push($arrFeriados,  $row['fecha']); 
		}

		$arrHeader = array();
		foreach ($horas as $item => $hora) {
			array_push($arrHeader, 
				array(
					'hora' => $hora,
					'dato' => darFormatoHora($hora),
					'class' =>  (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar ',
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE
				)
			);	

			$segundos_horaInicial=strtotime($hora); 
			$segundos_minutoAnadir=30*60; 
			$nuevaHora=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);
			$number = intval(explode(":",$nuevaHora)[0]);
			array_push($arrHeader, 
				array(
					'hora' => $nuevaHora,
					'dato' => darFormatoHora($nuevaHora),
					'class' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar '	,
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE		
				)
			);		
		}

		$listAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede( $this->sessionHospital['idsede'],null);

		$arrAmbientes = array();
		foreach ($listAmbientes as $ambiente) {
			$tag = substr($ambiente['descripcion_cco'], 0,2);
			$arrAmb = array(
					'dato' => $ambiente['numero_ambiente'],
					'class' => 'nombre-amb',
					'idambiente' => $ambiente['idambiente'],
					'piso'=> $ambiente['piso'],
					'orden'=> $ambiente['orden_ambiente'],
					'tag' => $tag,
					'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
					); 
			array_push($arrAmbientes,$arrAmb);
		}

		$arrListado = array();
		$arrGridTotal = array();
		$arrGrid = array();			
		$lista = $this->model_prog_medico->m_cargar_programaciones_por_fechas($allInputs);
		//var_dump($lista); exit();
		$countHoras = count($arrHeader);
		$countAmb = count($listAmbientes);
		$countProg = count($lista);

		$ind = 0;
		$i = 0;
		$j = 0;

		while($i < $countHoras){
			$j=0;
			$ind = 0;
			while ($j < $countAmb) {			
				$encontro = false;
				$idambiente = $listAmbientes[$j]['idambiente'];

				foreach ($lista as $prog_row) {
					/*$segundos_horaFin=strtotime($prog_row['hora_fin']);
					$segundos_minutosResta=30*60; 
					$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);*/

					$segundos_horaFin=strtotime($prog_row['hora_fin']);
					$number = intval(explode(":",$prog_row['hora_fin'])[1]);
					if($number == 0 || $number == 30){
						$segundos_minutosResta=30*60;
						$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);
					}else{
						$hora_fin_comparar=date("H:i:s",$segundos_horaFin);
					}

					if($arrHeader[$i]['hora'] >= $prog_row['hora_inicio'] && $arrHeader[$i]['hora'] <= $hora_fin_comparar  && $prog_row['idambiente'] == $idambiente){
						$encontro = true;
						array_push($arrGrid, 
							array(
								'dato' => $prog_row['nombre_especialidad'],
								'class' => 'cell-programacion ',
								'idambiente' => $prog_row['idambiente'],
								'fecha' => $prog_row['fecha_programada'],
								'especialidad' => $prog_row['nombre_especialidad'],							
								'idespecialidad' => $prog_row['idespecialidad'],	
								'idmedico' => $prog_row['idmedico'],	
								'idprogmedico' => $prog_row['idprogmedico'],
								'activo' => ($prog_row['activo'] == 1 ? TRUE : FALSE ),
								'ambiente'	=> $listAmbientes[$j],
								'hora' => $arrHeader[$i],	
								'rowspan' => 1,
								'unset' => FALSE,
								'tipoAtencion' => $prog_row['tipo_atencion_medica']
							)
						);
					}

				}				
			
				if(!$encontro){
					array_push($arrGrid,
							array(
								'dato' => '',
								'class' => ($arrHeader[$i]['es_feriado']) ? 'cell-vacia feriado' : 'cell-vacia',
								'ambiente'	=> $listAmbientes[$j],
								'hora' => $arrHeader[$i],
								'es_feriado' => $arrHeader[$i]['es_feriado'],
								'rowspan' => 1,
								'unset' => FALSE,
							)
						);
				}

				$j++;
			}
			
				
			array_push($arrGridTotal, $arrGrid);
			//var_dump($arrGridTotal); exit();
			$arrGrid = array();			
			$i++;

		}		

		$cellTotal = $countHoras;
		$cellColumn = count($listAmbientes);

		foreach ($listAmbientes as $i => $ambiente) {
	    	$inicio = -1;
   			$fin = -1;
   			$anterior = '';
   			$ite = 0;
	    	foreach ($arrHeader as $row => $value) {  
	    		$actual =  empty($arrGridTotal[$row][$i]['idprogmedico']) ? '' : $arrGridTotal[$row][$i]['idprogmedico']; 

	    		if($ite == 0){
	    			$anterior = $actual;
	    			$ite++;
	    		}  	

	    		if($inicio == -1)
	    			$inicio = $row;

	    		if($actual != $anterior){
	    			$fin = $row-1;
	    		}		    		

	    		if($inicio != -1 && $fin != -1){
	    			$rowspan =($fin - $inicio) + 1;
	    			$arrGridTotal[$inicio][$i]['rowspan'] = $rowspan;
					for ($fila=$inicio+1; $fila <= $fin; $fila++) { 
						$arrGridTotal[$fila][$i]['unset'] = TRUE;
					} 				
					$inicio = $row;
					$fin = -1;
	    		}else if($row == $cellTotal-1){
	    			$fin = $row;
    				$rowspan =($fin - $inicio) + 1;
					$arrGridTotal[$inicio][$i]['rowspan'] = $rowspan;
					for ($fila=$inicio+1; $fila <= $fin; $fila++) { 
    					$arrGridTotal[$fila][$i]['unset'] = TRUE;
    				}
    			}
				
				$anterior = empty($arrGridTotal[$row][$i]['idprogmedico']) ? '' : $arrGridTotal[$row][$i]['idprogmedico'];  

	    	}	    	 
	    }

		$arrData['planning']['datos'] = $lista;
    	$arrData['planning']['horas'] = $arrHeader;
    	$arrData['planning']['gridTotal'] = $arrGridTotal;
    	$arrData['planning']['ambientes'] = $arrAmbientes;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay programaciones en la fecha seleccionada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function generar_excel_vista_dias(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 0;
    	
    	//print_r($allInputs);
    	$dataColumnsTP = array();
    	$fechas = array();
    	$i = 0;
    	$cont = 0;
    	$currentCellEncabezado = 7;

    	//armando datos cabecera
    	foreach ($allInputs['planning']['cabecera'] as $itemHeader) {
    		if($i == 0)
    			array_push($fechas, 'AMB./DÍAS');
    		else
    			array_push($fechas, date('d-m-Y',$itemHeader['strtotime']) ); 

    		$i++;
    	}
    	array_push($dataColumnsTP, $fechas);

		//armando datos ambientes y programaciones 
    	$ind = 0;
    	$arrListado = array();
    	foreach ($allInputs['planning']['cuerpo'] as $fila) {
    		$arrFilas = array();
    		array_push($arrFilas, $fila['numero'] );
    		foreach ($fila['cell'] as $celda) {  
    			if(!empty($celda['section']))  {
    				$text  = '';
    				foreach ($celda['section'] as $especialidad) {    					
    					$arrIdProg = explode(",", $especialidad['idprogramaciones']);
    					$lista = $this->model_prog_medico->m_cargar_estas_programaciones_sin_detalle($arrIdProg);
    					foreach ($lista as $l => $row) {
    						$text = $text . $row['nombre'] . "\n" . $row['med_nombres']. " " . $row['med_apellido_paterno'] . "\n "; 
    						$text = $text . darFormatoHora($row['hora_inicio']). " a " . darFormatoHora($row['hora_fin']);
    						$text = $text . '-' . $row['intervalo_hora'] . '-' . $row['total_cupos'] . '-' . $row['cupos_adicionales'];
    						$text = $text . "|" ;
    					}    					
	    			}
	    			array_push($arrFilas, substr($text, 0, -1)); 
    			}else{
    				array_push($arrFilas, '');
    			}			
    			
    		}
    		array_push($arrListado, $arrFilas);
    	}

    	//titulo
		$this->excel->setActiveSheetIndex($cont);
    	$this->excel->getActiveSheet()->setTitle($allInputs['titulo']); 
    	$styleArrayTitle = array(
		    'font'=>  array(
		        'bold'  => true,
		        'size'  => 14,
		        'name'  => 'Verdana',
		        'color' => array('rgb' => '0790a2')
		    ),
		    'alignment' => array(
		        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    ),
	    );
    	$this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/'). ' ESTADO:'. $allInputs['itemEstado']['descripcion'] ); 
	    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('A1:D1');
	    $this->excel->getActiveSheet()->getCell('E1')->setValue('SEDE: ' . $this->sessionHospital['sede']); 
	    $this->excel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('E1:F1');	    
	    
	    // header (fechas)
	    $styleArrayHeader = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),
	      'alignment' => array(
	          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	      ),
	      'font'=>  array(
	          'bold'  => true,
	          'size'  => 10,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => '0790a2') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => '9de5ee', ),
	       ),
	    );  	    
    	$this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);    	

		//$ultimaColumna = substr($celda , 0, -1);
		$ultimaColumna = $this->excel->getActiveSheet()->getHighestColumn();

		//merge y aplicar estilos		
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->applyFromArray($styleArrayHeader);

    	//cuerpo
    	$styleArrayProd = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 6,
	          'name'  => 'Verdana',
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    $cellTotal = count($arrListado) + $currentCellEncabezado; 
    	$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
    	
    	//estilo celdas general
    	$this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(38);
    	$this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

    	//estilo ambientes
    	for ($i=$currentCellEncabezado+1; $i <= $cellTotal ; $i++) { 
    		$this->excel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setWrapText(true);
	    	$this->excel->getActiveSheet()->getStyle('A'.$i)->applyFromArray($styleArrayHeader);
    	}    	

	    //estilo cuerpo
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado).':'.$ultimaColumna .$cellTotal)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->applyFromArray($styleArrayProd);	    
	    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	    $color1 = '000000';
	    $color2 = '008fa1';
   	
	    foreach ($this->excel->getActiveSheet()->getRowIterator() as $index => $row) {
	        $cellIterator = $row->getCellIterator();
	        $nroMayorProg = 1;
	        if($index > $currentCellEncabezado){
	        	foreach ($cellIterator as $key => $cell) {
		        	$totalProg = 0;
		        	if($key > 0 && $cell->getValue()!=''){
		        		//print_r($cell->getValue());
						//separo cada programacion 
						$arrTextos = explode("|", $cell->getValue());
						$objRichText = new PHPExcel_RichText();
						//cada linea un color
						$colorActual=$color1;						
						foreach ($arrTextos as $ind => $text) {
							$subText = explode("-", $text);							
							$colorText = new PHPExcel_Style_Color();

							$run = $objRichText->createTextRun($subText[0]);
							if($colorActual == $color1){								
								$colorText->setRGB($color1);
								$run->getFont()->setColor($colorText);
								$colorActual = $color2;
							}else{
								$colorText->setRGB($color2);
								$run->getFont()->setColor($colorText);
								$colorActual = $color1;
							}

							$intervalo = explode(":", $subText[1])[1];	
							$runIntervalo = $objRichText->createTextRun(' - ' . $intervalo . 'min - ');
							$colorInt = new PHPExcel_Style_Color();
							$colorInt->setRGB('bdbdbd');
							$runIntervalo->getFont()->setColor($colorInt)->setBold(true);

							$runTotalCupos = $objRichText->createTextRun($subText[2] . ' - ');
							$colorCupos = new PHPExcel_Style_Color();
							$colorCupos->setRGB('8bc34a');
							$runTotalCupos->getFont()->setColor($colorCupos)->setBold(true);							

							if($ind != count($arrTextos)-1){
								$runTotalCuposAdc = $objRichText->createTextRun($subText[3]."\n"); 
							}else{
								$runTotalCuposAdc = $objRichText->createTextRun($subText[3]);
							}
							$colorCuAdi = new PHPExcel_Style_Color();
							$colorCuAdi->setRGB('03a9f4');
							$runTotalCuposAdc->getFont()->setColor($colorCuAdi)->setBold(true);

							$totalProg++;							
						}
						$cell->setValue($objRichText);					
		        	}
		        	if($totalProg > $nroMayorProg){
		        		$nroMayorProg = $totalProg ;
		        	}	        	  
		        }
		        $this->excel->getActiveSheet()->getRowDimension($index)->setRowHeight(45 * intval($nroMayorProg));		        
	        }	        	        
	    }		

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
	      'flag'=> 1
	    );

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function generar_excel_vista_horas(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 0;
    	
    	//print_r($allInputs);
    	$dataColumnsTP = array();
    	$ambientes = array();
    	$cont = 0;
    	$currentCellEncabezado = 3;

    	//armando datos cabecera
    	array_push($ambientes, 'H./DÍAS');
    	foreach ($allInputs['planning']['ambientes'] as $itemHeader) {
   			array_push($ambientes,$itemHeader['dato']); 
    	}
    	array_push($dataColumnsTP, $ambientes);

		//armando datos ambientes y programaciones 
    	$arrListado = array();
    	foreach ($allInputs['planning']['horas'] as $ind => $fila) {
    		$arrFilas = array();
    		array_push($arrFilas, $fila['dato'] );
    		foreach ($allInputs['planning']['gridTotal'][$ind] as $celda) {  
    			if(!empty($celda['idprogmedico'])){
    				array_push($arrFilas, $celda['idprogmedico']);
    			}else{
    				array_push($arrFilas, '');
    			}    			    			
    		}
    		array_push($arrListado, $arrFilas);
    	}

    	//titulo
		$this->excel->setActiveSheetIndex($cont);
    	$this->excel->getActiveSheet()->setTitle($allInputs['titulo']); 
    	$styleArrayTitle = array(
		    'font'=>  array(
		        'bold'  => true,
		        'size'  => 14,
		        'name'  => 'Verdana',
		        'color' => array('rgb' => '0790a2')
		    ),
		    'alignment' => array(
		        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    ),
	    );
	    $this->excel->getActiveSheet()->getCell('A1')->setValue('');
	    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    	$this->excel->getActiveSheet()->getCell('B1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/'). ' ESTADO:'. $allInputs['itemEstado']['descripcion'] ); 
	    $this->excel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('B1:D1');
	    $this->excel->getActiveSheet()->getCell('E1')->setValue('SEDE: ' . $this->sessionHospital['sede']); 
	    $this->excel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('E1:F1');	    
	    
	    // header (ambientes)
	    $styleArrayHeader = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),
	      'alignment' => array(
	          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	      ),
	      'font'=>  array(
	          'bold'  => true,
	          'size'  => 10,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => '0790a2') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => '9de5ee', ),
	       ),
	    ); 
	     	    
    	$this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);	
		$ultimaColumna = $this->excel->getActiveSheet()->getHighestColumn();

		//merge y aplicar estilos		
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$ultimaColumna .$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    	
    	//cuerpo
    	$styleArrayProd = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 6,
	          'name'  => 'Verdana',
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    $cellTotal = count($arrListado) + $currentCellEncabezado; 
    	$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
    	
    	//estilo celdas general
    	$this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(38);
    	$this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
    	//estilo horas
    	for ($i=$currentCellEncabezado+1; $i <= $cellTotal ; $i++) { 
    		$this->excel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setWrapText(true);
	    	$this->excel->getActiveSheet()->getStyle('A'.$i)->applyFromArray($styleArrayHeader);
    	}    	

	    //estilo cuerpo
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado).':'.$ultimaColumna .$cellTotal)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->applyFromArray($styleArrayProd);	    
	    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':'.$ultimaColumna .$cellTotal)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
   		
	    
	    for ($i=2; $i <= count($ambientes); $i++) { 
	    	$column = PHPExcel_Cell::stringFromColumnIndex($i - 1);
	    	$inicio = '';
   			$fin = '';
   			$anterior = '';
   			$ite = 0;
	    	for ($row=$currentCellEncabezado+1; $row <= $cellTotal ; $row++) { 
	    		$cell = $this->excel->getActiveSheet()->getCell($column.$row);
	    		$actual = $cell->getValue();    			    		

	    		
	    		if($actual != ''){
	    			if($ite == 0){
		    			$inicio = $row;
		    			$anterior = $actual;
		    			$ite++;
		    		}

	    			if($inicio == '')
	    				$inicio = $row;
	    			
	    			$fin = $row;

	    			if($anterior != $actual && $anterior != ''){
	    				//print_r('CELDA CONSULTA: '.$column.$inicio.'---');
	    				$rowProg = $this->model_prog_medico->m_cargar_esta_programacion($this->excel->getActiveSheet()->getCell($column.$inicio)->getValue());
	    				$concat = $column.$inicio . ':' . $column.($fin-1);
	    				$anterior = $cell->getValue();
	    				//print_r($concat . 'cuando anterior es diferente actual'.'---');
	    				$this->excel->getActiveSheet()->mergeCells($concat);

	    				$colorText = new PHPExcel_Style_Color();
	    				$objRichText = new PHPExcel_RichText();

	    				$text = $rowProg['nombre'] . "\n" . $rowProg['med_nombres']. " " . $rowProg['med_apellido_paterno'] . "\n "; 
						$text = $text . darFormatoHora($rowProg['hora_inicio']). " a " . darFormatoHora($rowProg['hora_fin']);						

						$run = $objRichText->createTextRun($text);								
						$colorText->setRGB('008fa1');
						$run->getFont()->setColor($colorText);

						$intervalo = explode(":", $rowProg['intervalo_hora'])[1];	
						$runIntervalo = $objRichText->createTextRun(' - ' . $intervalo . 'min - ');
						$colorInt = new PHPExcel_Style_Color();
						$colorInt->setRGB('bdbdbd');
						$runIntervalo->getFont()->setColor($colorInt)->setBold(true);

						$runTotalCupos = $objRichText->createTextRun($rowProg['total_cupos'] . ' - ');
						$colorCupos = new PHPExcel_Style_Color();
						$colorCupos->setRGB('8bc34a');
						$runTotalCupos->getFont()->setColor($colorCupos)->setBold(true);							
						
						$runTotalCuposAdc = $objRichText->createTextRun($rowProg['cupos_adicionales']);						
						$colorCuAdi = new PHPExcel_Style_Color();
						$colorCuAdi->setRGB('03a9f4');
						$runTotalCuposAdc->getFont()->setColor($colorCuAdi)->setBold(true);

						$this->excel->getActiveSheet()->getCell($column.$inicio)->setValue($objRichText);
						$inicio = $row;
	    			}else if($row == $cellTotal){
						//print_r('CELDA CONSULTA: '.$column.$inicio.'---');
						$rowProg = $this->model_prog_medico->m_cargar_esta_programacion($this->excel->getActiveSheet()->getCell($column.$inicio)->getValue());
	    				$concat = $column.$inicio . ':' . $column.$fin;
	    				//print_r($concat . 'cuando la celda es la ultima de la columna'.'---');
	    				$this->excel->getActiveSheet()->mergeCells($concat);

	    				$colorText = new PHPExcel_Style_Color();
	    				$objRichText = new PHPExcel_RichText();

	    				$text = $rowProg['nombre'] . "\n" . $rowProg['med_nombres']. " " . $rowProg['med_apellido_paterno'] . "\n "; 
						$text = $text . darFormatoHora($rowProg['hora_inicio']). " a " . darFormatoHora($rowProg['hora_fin']);						

						$run = $objRichText->createTextRun($text);								
						$colorText->setRGB('008fa1');
						$run->getFont()->setColor($colorText);

						$intervalo = explode(":", $rowProg['intervalo_hora'])[1];	
						$runIntervalo = $objRichText->createTextRun(' - ' . $intervalo . 'min - ');
						$colorInt = new PHPExcel_Style_Color();
						$colorInt->setRGB('bdbdbd');
						$runIntervalo->getFont()->setColor($colorInt)->setBold(true);

						$runTotalCupos = $objRichText->createTextRun($rowProg['total_cupos'] . ' - ');
						$colorCupos = new PHPExcel_Style_Color();
						$colorCupos->setRGB('8bc34a');
						$runTotalCupos->getFont()->setColor($colorCupos)->setBold(true);							
						
						$runTotalCuposAdc = $objRichText->createTextRun($rowProg['cupos_adicionales']);						
						$colorCuAdi = new PHPExcel_Style_Color();
						$colorCuAdi->setRGB('03a9f4');
						$runTotalCuposAdc->getFont()->setColor($colorCuAdi)->setBold(true);

						$this->excel->getActiveSheet()->getCell($column.$inicio)->setValue($objRichText);
	    			}

	    			
	    			$anterior = $cell->getValue();
	    		}else{
	    			$anterior = $cell->getValue();
	    			if($inicio != '' && $fin != '' ){
	    				//print_r('CELDA CONSULTA: '.$column.$inicio .'---');
	    				//print_r($this->excel->getActiveSheet()->getCell($column.$inicio)->getValue());
	    				$rowProg = $this->model_prog_medico->m_cargar_esta_programacion($this->excel->getActiveSheet()->getCell($column.$inicio)->getValue());
	    				$concat = $column.$inicio . ':' . $column.$fin;
	    				//print_r($concat . 'cuando celda es vacia pero tengo alamacenado rango'.'---');
	    				$this->excel->getActiveSheet()->mergeCells($concat);

	    				$colorText = new PHPExcel_Style_Color();
	    				$objRichText = new PHPExcel_RichText();

	    				$text = $rowProg['nombre'] . "\n" . $rowProg['med_nombres']. " " . $rowProg['med_apellido_paterno'] . "\n "; 
						$text = $text . darFormatoHora($rowProg['hora_inicio']). " a " . darFormatoHora($rowProg['hora_fin']);						

						$run = $objRichText->createTextRun($text);								
						$colorText->setRGB('008fa1');
						$run->getFont()->setColor($colorText);

						$intervalo = explode(":", $rowProg['intervalo_hora'])[1];	
						$runIntervalo = $objRichText->createTextRun(' - ' . $intervalo . 'min - ');
						$colorInt = new PHPExcel_Style_Color();
						$colorInt->setRGB('bdbdbd');
						$runIntervalo->getFont()->setColor($colorInt)->setBold(true);

						$runTotalCupos = $objRichText->createTextRun($rowProg['total_cupos'] . ' - ');
						$colorCupos = new PHPExcel_Style_Color();
						$colorCupos->setRGB('8bc34a');
						$runTotalCupos->getFont()->setColor($colorCupos)->setBold(true);							
						
						$runTotalCuposAdc = $objRichText->createTextRun($rowProg['cupos_adicionales']);						
						$colorCuAdi = new PHPExcel_Style_Color();
						$colorCuAdi->setRGB('03a9f4');
						$runTotalCuposAdc->getFont()->setColor($colorCuAdi)->setBold(true);

						$this->excel->getActiveSheet()->getCell($column.$inicio)->setValue($objRichText);

	    				$inicio = '';
   						$fin = '';    						  	   						
	    			}
	    		}	    		
	    		
	    	}	    	 
	    }

	    
	    $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
	      'flag'=> 1
	    );

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function envia_correo_medico($tipo, $arrProgMed){   
	    $this->load->library('My_PHPMailer');
	    $hoydate = date("Y-m-d H:i:s");
	    date_default_timezone_set('UTC');
	    define('SMTP_HOST','mail.villasalud.pe');
	    $correo = 'sistemas.ti@villasalud.pe';
	    $pass = 'franzsheskoli';
	    $setFromAleas = 'Dirección Médica';

	    define('SMTP_PORT',25);
	    define('SMTP_USERNAME',$correo);
	    define('SMTP_PASSWORD',$pass);
	    
	    $mail = new PHPMailer();
	    $mail->IsSMTP(true);
	    //$mail->SMTPDebug = 2;
	    $mail->SMTPAuth = true;
	    $mail->SMTPSecure = "tls";
	    $mail->Host = SMTP_HOST;
	    $mail->Port = SMTP_PORT;
	    $mail->Username =  SMTP_USERNAME;
	    $mail->Password = SMTP_PASSWORD;
	    $mail->SetFrom(SMTP_USERNAME,$setFromAleas);
	    $mail->AddReplyTo(SMTP_USERNAME,$setFromAleas);

	    if($tipo == 1)
	      	$mail->Subject = 'NUEVA PROGRAMACIÓN CARGADA';
	  	else if($tipo == 2)
	      	$mail->Subject = 'PROGRAMACIÓN MODIFICADA';
	  	else if($tipo == 3)
	    	$mail->Subject = 'PROGRAMACIÓN CANCELADA';

	    $cuerpo = '<html> 
		      <head>
		        <title>PROGRAMACIÓN MÉDICA</title> 
		      </head>
		      <body style="font-family: sans-serif;padding: 10px 40px;" > 
		      <div style="text-align: right;">
		        <img style="width: 160px;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/gm_small.png').'">
		      </div> <br />';
		$cuerpo .= '<div style="font-size:16px;">  
		        Estimado Dr(a).: '. $arrProgMed[0]['medico'].' <br /> <br /> ';

		if($tipo == 1){
			$cuerpo .= 'Mediante el presente se le informa, que han sido cargadas <u>NUEVAS PROGRAMACIONES ASISTENCIALES</u>. <br /> ';
			$cuerpo .= '<p>Especialidad: '. $arrProgMed[0]['especialidad'] .' </p>';
			$cuerpo .= '<p>Sede: <b>'.$arrProgMed[0]['sede'] .'</b></p>';
		}else if($tipo == 2){
			$cuerpo .= 'Mediante el presente se le informa, que ha sido <b>modificada</b> una de sus <u>PROGRAMACIONES ASISTENCIALES</u>. <br /> ';
			$cuerpo .= '<p>Especialidad: '. $arrProgMed[0]['especialidad'] .' </p>';
			$cuerpo .= '<p>Sede: <b>'.$arrProgMed[0]['sede'] .'</b></p>';
		}else if($tipo == 3){
			$cuerpo .= 'Mediante el presente se le informa, que ha sido <b>cancelada</b> una de sus <u>PROGRAMACIONES ASISTENCIALES</u>. <br /> ';
			$cuerpo .= '<p>Especialidad: '. $arrProgMed[0]['especialidad'] .' </p>';
			$cuerpo .= '<p>Sede: <b>'.$arrProgMed[0]['sede'] .'</b></p>';
		}
		
		foreach ($arrProgMed as $key => $item) {   
		    $cuerpo .= '<p>Fecha: <u>'. $item['fecha_item']  .'</u>, en el turno: <u>'. $item['turno'] .'</u>. <br />
		    			Ambiente: <u>'. $item['ambiente'] .'</u>.</p> ';	    
	    }

	    $cuerpo .= '<br /> Atte: <br /> <br /> DIRECCIÓN MÉDICA </div>';
		$cuerpo .= '</body></html>';
	    $mail->AltBody = $cuerpo;
	    $mail->MsgHTML($cuerpo);
	    $correoMedico = $this->model_empleado->m_get_correo_medico($arrProgMed[0])[0]['correo_electronico']; //consulta correo
	    $mail->CharSet = 'UTF-8';
	    // $mail->AddBCC("ymartinez@villasalud.pe");
	    // $mail->AddBCC("rluna@villasalud.pe");

	    if($correoMedico != null && $correoMedico != ''){
	    	if(comprobar_email($correoMedico)){
		    	$mail->AddAddress($correoMedico);
		    	if($mail->Send()){
		    		return 1; //'Notificación de correo enviada exitosamente.'
		    	}else{
		    		return 0; //'Notificación de correo NO enviada.'
		    	}
		    }else{
		    	return 2; //'Notificación de correo NO enviada. Correo de Médico invalido.'
		    }
	    }else{
	    	return 3; //'Notificación de correo NO enviada. Correo de Médico no registrado.'
	    }
	}

  	public function ver_popup_pacientes(){
		$this->load->view('prog-medico/listaPacientes_formView');
	}

	public function ver_popup_pacientes_proc(){
		$this->load->view('prog-medico/listaPacientesProc_formView');
	}

	public function listar_pacientes_por_programacion(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		//var_dump($allInputs); exit();
		$lista = $this->model_prog_medico->m_cargar_lista_pacientes_programados($allInputs['idprogmedico']);
		$arrListado = array();
		$arrCount = array();
		$consulAT = 0;
		$consulTO = 0;
		foreach ($lista as $row) {
			$paciente = trim($row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' .$row['apellido_materno']);
			$estado_cita_str = '';
			$strClase = '';
			// var_dump($row['estado_cita']); exit();
			if($row['estado_cita'] == 2 ){ // CONFIRMADO 
				$estado_cita_str = 'POR ATENDER';
				$strClase = 'label-warning';
				$estado = 2;
			}
			if($row['estado_cita'] == 5 ){ // ATENDIDO 
				$estado_cita_str = 'ATENDIDO';
				$strClase = 'label-info';
				$estado = 5; 
			}
			$preStr = 'Nº: ';
			if( $row['si_adicional'] == 1 ){
				$preStr = 'Adic Nº: ';
			}
			if( $row['estado_cita'] == 2 && !empty($row['idnotacreditodetalle']) ){ 
				$estado_cita_str = 'NOTA DE CRÉDITO';
				$strClase = 'label-danger';
				$estado = 3;
			}

			if( !empty($paciente) ){ 
				array_push($arrListado, 
					array(
						'numero_cupo' => $preStr.$row['numero_cupo'],
						'turno' => darFormatoHora($row['hora_inicio_det']) ,
						'paciente' => $paciente,
						'celular' => $row['celular'],
						'telefono' => $row['telefono'],
						'email' => $row['email'],
						'idcliente' => $row['idcliente'],
						'edad' => devolverEdad($row['fecha_nacimiento']),
						'idcanal' => $row['idcanal'],
						'idprogcita' => $row['idprogcita'],
						'origen_venta' => $row['origen_venta'],
						'iddetalle' => $row['iddetalle'],
						'idproductomaster' => $row['idproductomaster'],
						'producto' => $row['descripcion'],
						'si_adicional' => $row['si_adicional'],
						'estado_cita' => $row['estado_cita'],
						'paciente_atendido_det' => $row['paciente_atendido_det'],
						'estado_cita_str' => array(
							'clase'=> $strClase,
							'string'=> $estado_cita_str,
							'estado'=> $estado
						),
						
					)
				);
				if( $row['estado_cita'] == 5 ){
					$consulAT++;
				}
				$consulTO++;
			} 
		}

		/* contadores de procedimientos*/
		$allInputs=[];
		$allInputs['tipo_atencion_medica'] ='P';
		$allInputs['fecha'] = date('Y-m-d'); 	
		$lista_prog_proc = $this->model_prog_medico->m_cargar_programaciones_medico($allInputs);
		$arrProc = array();
		foreach ($lista_prog_proc as $row) {			
			$datos['idprogmedico']=$row['idprogmedico'];
			$lista_prog_proc_count = $this->model_prog_medico->m_cargar_lista_pacientes_programacion_proc($datos);			
			$procAT = 0; $procTO = 0;
			foreach ($lista_prog_proc_count as $rowCount) {
				 if ($rowCount['paciente_atendido_det'] == 1) {
				 	$procAT++;
				 }
				 $procTO++; 
			}
			array_push($arrProc, array(
				'id' => $row['idprogmedico'],
				'descripcion' => $row['hora_inicio'] . ' - ' . $row['hora_fin'],
				'datos' => $procAT . ' / ' . $procTO,
				)
			);
		}

		$arrCount['consult_atendidos'] = $consulAT;
		$arrCount['consult_totales'] = $consulTO;
		$arrCount['proc_atendidos'] = 0;
		$arrCount['proc_totales'] = 0;
		$arrCount['count_proc'] = $arrProc;
		$arrData['datos'] = $arrListado;
		$arrData['contadores'] = $arrCount;
		$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_pacientes_por_programacion_porc(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_prog_medico->m_cargar_lista_pacientes_programacion_proc($allInputs);
		$arrListado = array();
		$arrCount = array();
		$procAT = 0;
		$procTO = 0;
		foreach ($lista as $row) { 
			$medico = ' - ';
			if(!empty($row['idmedico'] )){
				$medico = trim($row['med_nombres'] . ' ' . $row['med_apellido_paterno'] . ' ' .$row['med_apellido_materno']);	
			}
			$paciente = ' - ';
			if( !empty($paciente) ){ 
				$paciente = trim($row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' .$row['apellido_materno']);
			} 
			$estado_cita_str = ''; $strClase = ''; 
			if($row['paciente_atendido_det'] == 1){ 
				$estado_cita_str = 'ATENDIDO';
				$strClase = 'label-info';
				$estado = $row['paciente_atendido_det'];
			}
			if($row['paciente_atendido_det'] == 2){ // ocupado-confirmado 
				$estado_cita_str = 'VENDIDO';
				$strClase = 'label-success';
				$estado = $row['paciente_atendido_det'];
			} 
			if( $row['paciente_atendido_det'] == 2 && !empty($row['idnotacreditodetalle']) ){ 
				$estado_cita_str = 'NOTA DE CRÉDITO';
				$strClase = 'label-danger';
				$estado = 3;
			}
			array_push($arrListado, 
				array(
					'orden_venta' => $row['orden_venta'],
					'fecha_venta' => darFormatoDMY($row['fecha_venta']),
					'fecha_atencion_v' => darFormatoDMY($row['fecha_atencion_v']),
					'paciente' => $paciente,
					'medico' => $medico,
					'ticket_venta' => $row['ticket_venta'],
					'idmedico' => $row['idmedico'],
					'idcliente' => $row['idcliente'],
					'idproductomaster' => $row['idproductomaster'],
					'iddetalle' => $row['iddetalle'],
					'estado' => array(
						'clase'=> $strClase,
						'string'=> strtoupper($estado_cita_str),
						'estado' =>  $estado
					),
				)
			);
			if( $row['paciente_atendido_det'] == 1 ){
				$procAT++;
			}
			$procTO++;
		}
		$arrCount['consult_atendidos'] = 0;
		$arrCount['consult_totales'] = 0;
		$arrCount['proc_atendidos'] = $procAT;
		$arrCount['proc_totales'] = $procTO;
		$arrData['datos'] = $arrListado;
		$arrData['contadores'] = $arrCount;
		$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_pacientes(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if(empty($allInputs['estado_prm'])){
			$lista = $this->model_prog_medico->m_cargar_lista_pacientes($allInputs['idprogmedico'], TRUE);
		}else{
			$lista = $this->model_prog_medico->m_cargar_lista_pacientes($allInputs['idprogmedico']);
		}
		$ultimo_sin_cancelar = $this->model_prog_medico->m_ultimo_cupo_sin_cancelar($allInputs, 1);
		if(empty($ultimo_sin_cancelar['iddetalleprogmedico'])){
			$ultimo_sin_cancelar = $this->model_prog_medico->m_ultimo_cupo_sin_cancelar($allInputs, 2);
		}

		$arrListado = array();
		foreach ($lista as $key => $item) {	
			$paciente = trim($item['nombres'] . ' ' . $item['apellido_paterno'] . ' ' .$item['apellido_materno']); 
			if( empty($paciente) ){ 
				$paciente = '-'; 
			} 
			$estado_cita_str = '';
			$strClase = '';
			if($item['estado_cita'] == 1){
				$estado_cita_str = 'reservado';
				$strClase = 'label-warning';
			}else if($item['estado_cita'] == 2){ // ocupado-confirmado 
				$estado_cita_str = 'ocupado';
				$strClase = 'label-danger';
			}else if($item['estado_cita'] == 3){
				$estado_cita_str = 'cancelado';
				$strClase = 'label-default';
			}else if($item['estado_cita'] == 4){
				$estado_cita_str = 'reprogramado';
				$strClase = 'label-inverse';
			}else if($item['estado_cita'] == 5){
				$estado_cita_str = 'atendido';
				$strClase = 'label-info'; 
			}

			if(empty($item['estado_cita'])){ 
				if($item['estado_cupo'] == 2){
					$estado_cita_str = 'disponible';
					$strClase = 'label-success';
				}else if($item['estado_cupo'] == 3){
					$estado_cita_str = 'cancelado';
					$strClase = 'label-default';
				}else if($item['estado_cupo'] == 4){
					$estado_cita_str = 'reprogramado';
					$strClase = 'label-inverse';
				}  
			}
						
			$htmlPopover = '';
			if(!empty($item['motivo_cancelacion'])){
				$htmlPopover = '<div class="col-md-12 p-n">
									<div class="col-md-12 p-n">
							          <strong class="control-label mb-n">MOTIVO CANCELACIÓN: </strong>
							          <p class="help-block m-n"> '. $item['motivo_cancelacion'] .' </p>
							        </div>
							        <div class="col-md-12 p-n">
							          <strong class="control-label mb-n">FECHA CANCELACIÓN: </strong>
							          <p class="help-block m-n"> '.  date('d-m-Y H:i:s',strtotime($item['fecha_cancelacion'])) .' </p>
							        </div>
						        </div>
						        ';		        
			}

			$preStr = '';
			if( $item['si_adicional'] == 1 ){
				$preStr.= '+ ';
			}else{
				$preStr.= 'nº ';
			} 

			array_push($arrListado, 
				array(
					'hora_inicio_formato' => darFormatoHora($item['hora_inicio_det']),
					'hora_fin_formato' => darFormatoHora($item['hora_fin_det']),
					'paciente' => $paciente,
					'turno' => darFormatoHora($item['hora_inicio_det']) . ' a ' . darFormatoHora($item['hora_fin_det']),
					'apellido_materno' => $item['apellido_materno'],
					'apellido_paterno' => $item['apellido_paterno'],
					'nombres' => $item['nombres'],
					'celular' => $item['celular'],
					'fecha_atencion_cita' => $item['fecha_atencion_cita'],
					'fecha_reg_reserva' => $item['fecha_reg_reserva'],
					'fecha_reg_cita' => $item['fecha_reg_cita'],
					'hora_inicio_det' => $item['hora_inicio_det'],
					'hora_fin_det' => $item['hora_fin_det'],
					'idcliente' => $item['idcliente'],
					'idempresacliente' => $item['idempresacliente_cli'],
					'iddetalleprogmedico' => $item['iddetalleprogmedico'],
					'idprogmedico' => $item['idprogmedico'],
					'idprogcita' => $item['idprogcita'],					
					'numero_cupo' => $preStr.$item['numero_cupo'],
					'si_adicional' => ($item['si_adicional'] == 1) ? TRUE : FALSE,
					'tipoCupo' => ($item['si_adicional'] == 1) ? 'adicional' : '',
					'telefono' => $item['telefono'],
					'email' => $item['email'],
					'estado_cupo' => $item['estado_cupo'],
					'estado_cita' => $item['estado_cita'],
					'estado_cita_str' => array(
						'clase'=> $strClase,
						'string'=> strtoupper($estado_cita_str)
					),
					'es_ultimo' => ($item['iddetalleprogmedico'] == $ultimo_sin_cancelar['iddetalleprogmedico']) ? TRUE : FALSE,
					'edad' => devolverEdad($item['fecha_nacimiento']),
					'num_documento' => $item['num_documento'],
					'idproductomaster' => $item['idproductomaster'],
					'descripcion_producto' => $item['descripcion'],
					'iddetalle' => $item['iddetalle'],
					'idcanal' => $item['idcanal'],
					'estado_prm' => intval($item['estado_prm']),
					'motivo_cancelacion' => $item['motivo_cancelacion'],
					'fecha_cancelacion' => $item['fecha_cancelacion'],
					'htmlPopover' => $htmlPopover,
				)
			);
		}
		$arrData['datos'] = $arrListado;
		$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function generar_excel_lista_pacientes(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 0;
    	
    	//print_r($allInputs['lista']);
    	$dataColumnsTP = array(); 
    	$cont = 0;
    	$currentCellEncabezado = 7;

    	//armando datos cabecera
    	array_push($dataColumnsTP, array('N° DE CUPO', 'TURNO', 'PACIENTE', 'CELULAR', 'TELEFONO', 'EMAIL', 'ESTADO'));

    	//armando cuerpo
    	$arrListado = array();
    	$arrListadoAdicional = array();
    	foreach ($allInputs['lista'] as $key => $item) {

    		$fila =  array(
    			$item['numero_cupo'],
    			$item['turno'],
    			$item['paciente'],
    			$item['celular'],
    			$item['telefono'],
    			$item['email'],
    			strtoupper($item['estado_cita_str']['string']),
    			);

    		if($item['tipoCupo'] == 'adicional'){
    			array_push($arrListadoAdicional, $fila);
    		}else{
    			array_push($arrListado, $fila);
    		}    		
    	}

		
    	//titulo
		$this->excel->setActiveSheetIndex($cont);
    	$this->excel->getActiveSheet()->setTitle($allInputs['titulo']); 
    	$styleArrayTitle = array(
		    'font'=>  array(
		        'bold'  => true,
		        'size'  => 14,
		        'name'  => 'Verdana',
		        'color' => array('rgb' => '0790a2')
		    ),
		    'alignment' => array(
		        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    ),
	    );

	     // header 
	    $styleArrayHeader = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),
	      'alignment' => array(
	          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	      ),
	      'font'=>  array(
	          'bold'  => true,
	          'size'  => 10,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => '0790a2') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => '9de5ee', ),
	       ),
	    ); 
	     	    
    	$this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);	

	    //merge y aplicar estilos
	    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
	    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('A1:F1');	   
	
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':G'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':G'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
	    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
	    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
	    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
    	$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    	$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    	$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(45);

    	$this->excel->getActiveSheet()->getCell('A3')->setValue('ESPECIALIDAD: ' . $allInputs['especialidad']);
    	$this->excel->getActiveSheet()->mergeCells('A3:B3');
    	$this->excel->getActiveSheet()->getCell('A4')->setValue('FECHA: '. $allInputs['fecha_programada']);
    	$this->excel->getActiveSheet()->mergeCells('A4:B4');
    	$this->excel->getActiveSheet()->getCell('A5')->setValue('INTERVALO ATENCIÓN: '. $allInputs['intervalo_hora_int'] . ' min.');
    	$this->excel->getActiveSheet()->mergeCells('A5:B5');

    	$this->excel->getActiveSheet()->getCell('C3')->setValue('MEDICO: ' . $allInputs['medico']);
    	$this->excel->getActiveSheet()->getCell('C4')->setValue('AMBIENTE: '.$allInputs['ambiente']['numero_ambiente'] . ' - TURNO: ' .$allInputs['turno']);

    	$this->excel->getActiveSheet()->getCell('D3')->setValue('EMPRESA: ' . $allInputs['empresa']);
    	$this->excel->getActiveSheet()->mergeCells('D3:E3');
    	$this->excel->getActiveSheet()->getCell('D4')->setValue('SEDE: ' . $this->sessionHospital['sede']);
    	$this->excel->getActiveSheet()->mergeCells('D4:E4');    	

    	//cuerpo
    	$styleArrayProd = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 8,
	          'name'  => 'Verdana',
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    $cellTotal = count($arrListado) + $currentCellEncabezado; 
    	$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
    	
    	//adicionales
    	$styleArrayAdic = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 8,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => 'd60000') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    
    	
    	//estilo celdas general
    	$this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
    	$this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
	    //estilo cuerpo
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado).':G'.$cellTotal)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':G'.$cellTotal)->applyFromArray($styleArrayProd);	    
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':G'.$cellTotal)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	    $cellTotalAdi = count($arrListadoAdicional); 
    	$this->excel->getActiveSheet()->fromArray($arrListadoAdicional, null, 'A'.($cellTotal+1));
	    //estilo adicionales
	    $this->excel->getActiveSheet()->getStyle('A'.($cellTotal+1).':G'.($cellTotal+$cellTotalAdi))->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.($cellTotal+1).':G'.($cellTotal+$cellTotalAdi))->applyFromArray($styleArrayAdic);	    
	    $this->excel->getActiveSheet()->getStyle('A'.($cellTotal+1).':G'.($cellTotal+$cellTotalAdi))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	    $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);

	    //formato columna estado

	    for ($i=$currentCellEncabezado+1; $i <= $cellTotal+$cellTotalAdi; $i++) { 
	    	$value = $this->excel->getActiveSheet()->getCell('G'.$i)->getValue();

	    	if(strtoupper($value) == 'CONFIRMADO' || strtoupper($value) == 'OCUPADO'){
	    		$color = 'FFC7CE';
	    	}else if(strtoupper($value) == 'CANCELADO' ){
	    		$color = 'bdbdbd';
	    	}else if(strtoupper($value) == 'REPROGRAMADO' ){
	    		$color = '757575';
	    	}else if(strtoupper($value) == 'ATENDIDO' ){
	    		$color = '00bcd4';
	    	}else if(strtoupper($value) == 'DISPONIBLE' ){
	    		$color = 'C6EFCE';
	    	}else if(strtoupper($value) == 'RESERVADO' ){
	    		$color = 'FFF0C5';
	    	}

	    	$this->excel->getActiveSheet()->getStyle('G'.$i)->getFill()->applyFromArray(array(
		        'type' => PHPExcel_Style_Fill::FILL_SOLID,
		        'startcolor' => array(
		             'rgb' => $color
		        )
		    ));
	    }
	    

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
	      'flag'=> 1
	    );

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function generar_excel_lista_pacientes_proc(){
		ini_set('max_execution_time', 300);
    	ini_set('memory_limit','160M');
    	$allInputs = json_decode(trim($this->input->raw_input_stream),true);
    	$arrData['flag'] = 0;
    	
    	//print_r($allInputs['lista']);
    	$dataColumnsTP = array(); 
    	$cont = 0;
    	$currentCellEncabezado = 7;

    	//armando datos cabecera
    	array_push($dataColumnsTP, array('ORDEN VENTA', 'TICKET', 'FECHA VENTA', 'FECHA ATENCIÓN', 'PACIENTE', 'ESTADO'));

    	//armando cuerpo
    	$arrListado = array();
    	$arrListadoAdicional = array();
    	foreach ($allInputs['lista'] as $key => $item) {

    		$fila =  array(
    			$item['orden_venta'],
    			$item['ticket_venta'],
    			$item['fecha_venta'],
    			$item['fecha_atencion_v'],
    			$item['paciente'],
				$item['estado']['string'],
    			);

    			array_push($arrListado, $fila);		    		
    	}
		
    	//titulo
		$this->excel->setActiveSheetIndex($cont);
    	$this->excel->getActiveSheet()->setTitle($allInputs['titulo']); 
    	$styleArrayTitle = array(
		    'font'=>  array(
		        'bold'  => true,
		        'size'  => 14,
		        'name'  => 'Verdana',
		        'color' => array('rgb' => '0790a2')
		    ),
		    'alignment' => array(
		        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    ),
	    );

	     // header 
	    $styleArrayHeader = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),
	      'alignment' => array(
	          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	      ),
	      'font'=>  array(
	          'bold'  => true,
	          'size'  => 10,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => '0790a2') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => '9de5ee', ),
	       ),
	    ); 
	     	    
    	$this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);	

	    //merge y aplicar estilos
	    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
	    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
	    $this->excel->getActiveSheet()->mergeCells('A1:F1');	   
	
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':F'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':F'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
	    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(24);
	    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
	    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
    	$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    	$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    	$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

    	$this->excel->getActiveSheet()->getCell('A3')->setValue('ESPECIALIDAD: ' . $allInputs['especialidad']);
    	$this->excel->getActiveSheet()->mergeCells('A3:B3');
    	$this->excel->getActiveSheet()->getCell('A4')->setValue('FECHA: '. $allInputs['fecha_programada']);
    	$this->excel->getActiveSheet()->mergeCells('A4:B4');
    	$this->excel->getActiveSheet()->mergeCells('A5:B5');

    	$this->excel->getActiveSheet()->getCell('C3')->setValue('MEDICO: ' . $allInputs['medico']);
    	$this->excel->getActiveSheet()->mergeCells('C3:E3');
    	$this->excel->getActiveSheet()->getCell('C4')->setValue('AMBIENTE: '.$allInputs['ambiente']['numero_ambiente'] . ' - TURNO: ' .$allInputs['turno']);
    	$this->excel->getActiveSheet()->mergeCells('C4:E4');

    	$this->excel->getActiveSheet()->getCell('F3')->setValue('EMPRESA: ' . $allInputs['empresa']);
    	$this->excel->getActiveSheet()->mergeCells('F3:G3');
    	$this->excel->getActiveSheet()->getCell('F4')->setValue('SEDE: ' . $this->sessionHospital['sede']);
    	$this->excel->getActiveSheet()->mergeCells('F4:G4');    	

    	//cuerpo
    	$styleArrayProd = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 8,
	          'name'  => 'Verdana',
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    $cellTotal = count($arrListado) + $currentCellEncabezado; 
    	$this->excel->getActiveSheet()->fromArray($arrListado, null, 'A'.($currentCellEncabezado+1));
    	
    	//adicionales
    	$styleArrayAdic = array(
	      'borders' => array(
	        'allborders' => array( 
	          'style' => PHPExcel_Style_Border::BORDER_THIN,
	          'color' => array('rgb' => '00bcd4') 
	        ) 
	      ),	      
	      'font'=>  array(
	          'bold'  => false,
	          'size'  => 8,
	          'name'  => 'Verdana',
	          'color' => array('rgb' => 'd60000') 
	      ),
	      'fill' => array( 
	          'type' => PHPExcel_Style_Fill::FILL_SOLID,
	          'startcolor' => array( 'rgb' => 'e1f9fc', ),
	       ),	      
	    );	
	    
    	
    	//estilo celdas general
    	$this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
    	$this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
	    //estilo cuerpo
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado).':F'.$cellTotal)->getAlignment()->setWrapText(true);
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':F'.$cellTotal)->applyFromArray($styleArrayProd);	    
	    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':F'.$cellTotal)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);


	    $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);

	    //formato columna estado

	    for ($i=$currentCellEncabezado+1; $i <= $cellTotal; $i++) { 
	    	$value = $this->excel->getActiveSheet()->getCell('F'.$i)->getValue();
	
	    	if(strtoupper($value) == 'ATENDIDO' ){
	    		$color = '00bcd4';
	    	}else if(strtoupper($value) == 'VENDIDO' ){
	    		$color = '8bc34a';
	    	}else{
	    		$color = 'd0181e';
	    	}

	    	$this->excel->getActiveSheet()->getStyle('F'.$i)->getFill()->applyFromArray(array(
		        'type' => PHPExcel_Style_Fill::FILL_SOLID,
		        'startcolor' => array(
		             'rgb' => $color
		        )
		    ));
	    }
	    

	    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
	    //force user to download the Excel file without writing it to server's HD 
	    $dateTime = date('YmdHis');
	    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
	    $arrData = array(
	      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
	      'flag'=> 1
	    );

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}		
	
	public function planing_horas_genera_consulta_informes(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		if(empty($allInputs['origen'])){
			$allInputs['origen'] = false;
		}
		//  var_dump($allInputs['origen']); exit(); 
		if(!$allInputs['origen'] || $allInputs['origen'] == 'next' ){ // POR DEFECTO
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		}else if($allInputs['origen'] == 'prev'){
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);			
		}else if($allInputs['origen'] == 'calendar'){
			$fecha_consulta = $allInputs['desde'];
		}	

		if(empty($fecha_consulta)){
			$allInputs['next'] = TRUE;
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
			$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;

			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
			$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;

			$arrData['fecha_consulta'] = $allInputs['desde'];

			$arrData['flag'] = 0;
			if(!empty($allInputs['medico'])){
				$arrData['message'] = 'No hay programaciones cargadas para el medico: ' . $allInputs['medico']['medico'];
			}
			if(!empty($allInputs['especialidad'])){
				$arrData['message'] = 'No hay programaciones cargadas para la especialidad: ' . $allInputs['especialidad']['descripcion'];	
			}

			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData)); 
			return;
		}else if(!$allInputs['origen'] || $allInputs['origen'] == 'next' || $allInputs['origen'] == 'prev'){
			$allInputs['desde'] = $fecha_consulta;
		}	

		$allInputs['hasta'] = $allInputs['desde'];
		$arrData['fecha_consulta'] = date('d-m-Y', strtotime( $allInputs['desde']));
		$lista = $this->model_prog_medico->m_cargar_programaciones_generar_cupo_informe($allInputs);
		$lista_cons = array();
		$lista_proc = array();
		if($allInputs['tipoAtencion']['id'] == null){
			$allInputs['tipoAtencion']['id']  = 'CM';
			$lista_cons = $this->model_prog_medico->m_cargar_programaciones_generar_cupo_informe($allInputs);
			$allInputs['tipoAtencion']['id']  = 'P';
			$lista_proc = $this->model_prog_medico->m_cargar_programaciones_generar_cupo_informe($allInputs);
		}else if($allInputs['tipoAtencion']['id'] == 'CM'){
			$lista_cons = $lista;
		}else{
			$lista_proc = $lista;
		}
				
		//$lista = $this->model_prog_medico->m_cargar_programaciones_por_fechas($allInputs);		

		$sede = $this->model_sede->m_consultar($this->sessionHospital['idsede']);
		$number = intval(explode(":",$sede['hora_final_atencion'])[0]);
		$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';
		$horas = get_rangohoras($sede['hora_inicio_atencion'], $hora_fin);
		
		$datos = array('anyo' => date("Y"));
		$feriados = $this->model_feriado->m_lista_feriados_cbo($datos); 
		$arrFeriados = array();
		foreach ($feriados as $row) {
			array_push($arrFeriados,  $row['fecha']); 
		}

		$arrHeader = array();
		foreach ($horas as $item => $hora) { 
			//$strHora = $hora; 
			
			$segundos_horaInicial=strtotime($hora); 
			$segundos_minutoAnadir=30*60; 
			$horaFin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);
			array_push($arrHeader, 
				array(
					'hora' => $hora,
					'hora_fin' => $horaFin,
					'timestamp'=> strtotime($hora),
					'dato' => darFormatoHora($hora),
					'dato_fin' => darFormatoHora($horaFin),
					'class' =>  (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar ',
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE
				)
			);

			$segundos_horaInicial=strtotime($hora); 
			$segundos_minutoAnadir=30*60; 
			$nuevaHora=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

			$segundos_horaInicial=strtotime($nuevaHora); 
			$segundos_minutoAnadir=30*60; 
			$horaFin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

			array_push($arrHeader, 
				array(
					'hora' => $nuevaHora,
					'hora_fin' => $horaFin,
					'timestamp'=> strtotime($nuevaHora),
					'dato' => darFormatoHora($nuevaHora),
					'dato_fin'=> darFormatoHora($horaFin),
					'class' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar ', 
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE 
				)
			);		
		}

		$listAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede( $this->sessionHospital['idsede'],null);
		/*
			amb.idambiente, numero_ambiente, piso, comentario, amb.orden_ambiente, amb.idsede, (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco

		*/
		// LIMPIAR AMBIENTES 
		$auxListaAmbientes = array();
		foreach ($lista as $key => $row) { 
			foreach ($listAmbientes as $keyAmb => $rowAmb) {
				if( $row['idambiente'] == $rowAmb['idambiente'] ){
					$auxListaAmbientes[$rowAmb['idambiente']] = $rowAmb; 
				}
			}
		}
		$auxListaAmbientes = array_values($auxListaAmbientes); 
		$listAmbientes = $auxListaAmbientes;


		$arrAmbientes = array();
		foreach ($listAmbientes as $ambiente) {
			$tag = substr($ambiente['descripcion_cco'], 0,2);
			$arrAmb = array(
					'dato' => $ambiente['numero_ambiente'],
					'class' => 'nombre-amb',
					'idambiente' => $ambiente['idambiente'],
					'piso'=> $ambiente['piso'],
					'orden'=> $ambiente['orden_ambiente'],
					'tag' => $tag,
					'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
					); 
			array_push($arrAmbientes,$arrAmb);
		}
	    $countHoras = count($arrHeader);
		$countAmb = count($listAmbientes);
		$countProg = count($lista);

	    $cellTotal = $countHoras;
		$cellColumn = count($listAmbientes);
		$arrGridTotal2 = array();
		$arrGrid_cons = array();
		$arrGrid_proc = array();
		$arrGrid2 = array();		
		$ind = 0;
		$i = 0;
		$j = 0;

		while( $j < $countAmb){
			$i=0;
			$ind = 0;
			while ($i < $countHoras) {			
				$encontroCons = false;
				$encontroProc = false;
				$idambiente = $listAmbientes[$j]['idambiente'];

				if(!empty($lista_cons)){
					foreach ($lista_cons as $prog_row) {

						$segundos_horaFin=strtotime($prog_row['hora_fin']);
						$number = intval(explode(":",$prog_row['hora_fin'])[1]);
						if($number == 0 || $number == 30){
							$segundos_minutosResta=30*60;
							$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);
						}else{
							$hora_fin_comparar=date("H:i:s",$segundos_horaFin);
						}

						if($arrHeader[$i]['hora'] >= $prog_row['hora_inicio'] && $arrHeader[$i]['hora'] <= $hora_fin_comparar  
							&& $prog_row['idambiente'] == $idambiente){
							$encontroCons = true;
							$total_cupos = $prog_row['total_cupos'];
							$total_cupos_ocupados = $prog_row['total_cupos_ocupados'];
							$total_cupos_no_cancelados = $prog_row['total_cupos_no_cancelados'];
							//$porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos,2); 

							if( !empty($total_cupos) ){
								$porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos,2); 
							}else{
								$porcentaje = 100;
							}

							// if( !empty($total_cupos_no_cancelados) ){
							// 	$porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos_no_cancelados,2); 
							// }else{
							// 	$porcentaje = 100;
							// }
							
							// var_dump($porcentaje,$total_cupos_ocupados,$total_cupos_no_cancelados); exit();
							$habilitada = TRUE ;
							$timestamp = strtotime($prog_row['intervalo_hora']); 
							$intervaloHoraInt = date('i', $timestamp);
							$medico = $prog_row['med_nombres'] . ' ' . $prog_row['med_apellido_paterno'] . ' ' .$prog_row['med_apellido_materno'] ;
							if($j == 0){
								$position = "right";
							}else{
								$position = "left";
							}

							$arrGrid_cons = 
								array(
									'dato' => $prog_row['nombre_especialidad'],
									'class' => (!$habilitada) ? 'cell-programacion deshabilitada' : 'cell-programacion',
									'idambiente' => $prog_row['idambiente'],
									'fecha' => $prog_row['fecha_programada'],
									'fecha_str' => date('d-m-Y',strtotime($prog_row['fecha_programada'])),
									'especialidad' => $prog_row['nombre_especialidad'], 
									'idespecialidad' => $prog_row['idespecialidad'], 
									'idmedico' => $prog_row['idmedico'],	
									'idprogmedico' => $prog_row['idprogmedico'],
									'turno' => darFormatoHora($prog_row['hora_inicio']) . ' a ' . darFormatoHora($prog_row['hora_fin']),
									'medico' => $medico,
									'hora_inicio' => $prog_row['hora_inicio'],
									'tmp_hora_inicio' => strtotime($prog_row['hora_inicio']),
									'hora_fin' => $prog_row['hora_fin'],
									'tmp_hora_fin' => strtotime($prog_row['hora_fin']),
									'total_cupos' => $total_cupos,
									'total_cupos_ocupados' => $total_cupos_ocupados,
									'total_cupos_no_cancelados' => $total_cupos_no_cancelados,
									'porcentaje' => $porcentaje,
									'cupos_adicionales' => $prog_row['cupos_adicionales'],
									'ambiente'	=> $listAmbientes[$j],
									'headerHora' => $arrHeader[$i],								
									'rowspan' => 1,
									'unset' => FALSE,	
									'detalle' => TRUE,	
									'habilitada' => $habilitada,						
									'empresa' =>  $prog_row['empresa'],					
									'intervalo_hora_int' =>  $intervaloHoraInt,
									'tooltip_position'	=> $position,
									'tooltip_enable' => TRUE,				
									'tooltip_text' => 'MÉDICO: ' . $medico,				
									'total_adi_vendidos' => $prog_row['total_adi_vendidos'],
									'tipo_atencion' => $prog_row['tipo_atencion_medica'],				
								);
						}

					}

					if(!$encontroCons){
						$arrGrid_cons =
							array(
								'dato' => '',
								'class' => ($arrHeader[$i]['es_feriado']) ? 'cell-vacia feriado' : 'cell-vacia',
								'ambiente'	=> $listAmbientes[$j],
								'hora' => $arrHeader[$i],
								'es_feriado' => $arrHeader[$i]['es_feriado'],
								'rowspan' => 1,
								'unset' => FALSE,
								'detalle' => FALSE,
								'tooltip-enable' => FALSE,	
							);
					}
				}

				if(!empty($lista_proc)){
					foreach ($lista_proc as $prog_row) {

						$segundos_horaFin=strtotime($prog_row['hora_fin']);
						$number = intval(explode(":",$prog_row['hora_fin'])[1]);
						if($number == 0 || $number == 30){
							$segundos_minutosResta=30*60;
							$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);
						}else{
							$hora_fin_comparar=date("H:i:s",$segundos_horaFin);
						}

						if($arrHeader[$i]['hora'] >= $prog_row['hora_inicio'] && $arrHeader[$i]['hora'] <= $hora_fin_comparar  
							&& $prog_row['idambiente'] == $idambiente){
							$encontroProc = true;

							/*if($allInputs['especialidad']){
								$habilitada = ($allInputs['especialidad']['id'] == $prog_row['idespecialidad']) ? TRUE : FALSE;							
							}else{							
								$habilitada = TRUE ;
							}*/
							$habilitada = TRUE ;
							$medico = $prog_row['med_nombres'] . ' ' . $prog_row['med_apellido_paterno'] . ' ' .$prog_row['med_apellido_materno'] ;
							if($j == 0){
								$position = "right";
							}else{
								$position = "left";
							}

							$arrGrid_proc = 
								array(
									'dato' => $prog_row['nombre_especialidad'],
									'class' => (!$habilitada) ? 'cell-programacion deshabilitada' : 'cell-programacion',
									'idambiente' => $prog_row['idambiente'],
									'fecha' => $prog_row['fecha_programada'],
									'fecha_str' => date('d-m-Y',strtotime($prog_row['fecha_programada'])),
									'especialidad' => $prog_row['nombre_especialidad'], 
									'idespecialidad' => $prog_row['idespecialidad'], 
									'idmedico' => $prog_row['idmedico'],	
									'idprogmedico' => $prog_row['idprogmedico'],
									'turno' => darFormatoHora($prog_row['hora_inicio']) . ' a ' . darFormatoHora($prog_row['hora_fin']),
									'medico' => $medico,
									'hora_inicio' => $prog_row['hora_inicio'],
									'tmp_hora_inicio' => strtotime($prog_row['hora_inicio']),
									'hora_fin' => $prog_row['hora_fin'],
									'tmp_hora_fin' => strtotime($prog_row['hora_fin']),
									'ambiente'	=> $listAmbientes[$j],
									'headerHora' => $arrHeader[$i],								
									'rowspan' => 1,
									'unset' => FALSE,	
									'detalle' => TRUE,	
									'habilitada' => $habilitada,						
									'empresa' =>  $prog_row['empresa'],					
									'tooltip_position'	=> $position,
									'tooltip_enable' => TRUE,				
									'tooltip_text' => 'MÉDICO: ' . $medico,				
									'tipo_atencion' => $prog_row['tipo_atencion_medica'],				
								);
						}

					}				
				
					if(!$encontroProc){
						$arrGrid_proc=
							array(
								'dato' => '',
								'class' => ($arrHeader[$i]['es_feriado']) ? 'cell-vacia feriado' : 'cell-vacia',
								'ambiente'	=> $listAmbientes[$j],
								'hora' => $arrHeader[$i],
								'es_feriado' => $arrHeader[$i]['es_feriado'],
								'rowspan' => 1,
								'unset' => FALSE,
								'detalle' => FALSE,
								'tooltip-enable' => FALSE,	
							);
					}
				}

				$i++;
				$arrGrid2[$i]['CM'] = (!empty($arrGrid_cons)) ? $arrGrid_cons : NULL;
				$arrGrid2[$i]['P'] =  (!empty($arrGrid_proc)) ? $arrGrid_proc : NULL;

				$arrGrid_cons = array();
				$arrGrid_proc = array();
			}			
			
			array_push($arrGridTotal2, $arrGrid2);

			$arrGrid2 = array();			
			$j++;
		}

		foreach ($listAmbientes as $i => $ambiente) {
		   	$countConsul = 0; $countProc = 0;
   			foreach ($arrHeader as $row => $value) {
   				$j = $row+1;
	   			//Consultas
	   			if(!empty($lista_cons)){	
		    		if($arrGridTotal2[$i][$j]['CM']['detalle']){	    			
		    			if($j == 1){
		    				$countConsul++;
		    			} 	    			
		    			if ($j != 1 && $arrGridTotal2[$i][$j]['CM']['detalle'] != $arrGridTotal2[$i][$j-1]['CM']['detalle'] ){	
		    				$countConsul++;
		    			}
		    			if($j == $countHoras){
			    			$countConsul++;
			    			$arrGridTotal2[$i][$j]['CM']['class'] = 'cell-vacia';
			    			$arrGridTotal2[$i][$j]['CM']['unset'] = true;
			    			$arrGridTotal2[$i][$j-($countConsul-1)]['CM']['rowspan'] = $countConsul;
			    			$countConsul = 0;
			    		}else if ($j != 1 && $arrGridTotal2[$i][$j]['CM']['detalle'] == $arrGridTotal2[$i][$j-1]['CM']['detalle'] && $arrGridTotal2[$i][$j]['CM']['tmp_hora_inicio'] == $arrGridTotal2[$i][$j-1]['CM']['tmp_hora_inicio']){	
		    				$countConsul++;
		    				$arrGridTotal2[$i][$j]['CM']['class'] = 'cell-vacia';
		    				$arrGridTotal2[$i][$j]['CM']['unset'] = true;
		    			}else if ($j != 1 && $arrGridTotal2[$i][$j]['CM']['detalle'] == $arrGridTotal2[$i][$j-1]['CM']['detalle']){
			    			$arrGridTotal2[$i][$row-($countConsul-1)]['CM']['rowspan'] = $countConsul;
			    			$countConsul = 1;
			    		}
	    		
		    		}else if($j != 1 && $arrGridTotal2[$i][$j-1]['CM']['detalle']){
	    				$arrGridTotal2[$i][$j]['CM']['class'] = 'cell-vacia';
	    				$arrGridTotal2[$i][$j]['CM']['unset'] = true;
		    			$arrGridTotal2[$i][$row-($countConsul-1)]['CM']['rowspan'] = $countConsul;
		    			$countConsul = 0;
		    		}
		    	}
		    	//Procedimientos
		    	if(!empty($lista_proc)){
		    		if($arrGridTotal2[$i][$j]['P']['detalle']){	    			
		    			if($j == 1){
		    				$countProc++;
		    			} 	    			
		    			if ($j != 1 && $arrGridTotal2[$i][$j]['P']['detalle'] != $arrGridTotal2[$i][$j-1]['P']['detalle'] ){	
		    				$countProc++;
		    			}
		    			if($j == $countHoras){
			    			$countProc++;
			    			$arrGridTotal2[$i][$j]['P']['class'] = 'cell-vacia';
			    			$arrGridTotal2[$i][$j]['P']['unset'] = true;
			    			$arrGridTotal2[$i][$j-($countProc-1)]['P']['rowspan'] = $countProc;
			    			$countProc = 0;
			    		}else if ($j != 1 && $arrGridTotal2[$i][$j]['P']['detalle'] == $arrGridTotal2[$i][$j-1]['P']['detalle'] && $arrGridTotal2[$i][$j]['P']['tmp_hora_inicio'] == $arrGridTotal2[$i][$j-1]['P']['tmp_hora_inicio']){	
		    				$countProc++;
		    				$arrGridTotal2[$i][$j]['P']['class'] = 'cell-vacia';
		    				$arrGridTotal2[$i][$j]['P']['unset'] = true;
		    			}else if ($j != 1 && $arrGridTotal2[$i][$j]['P']['detalle'] == $arrGridTotal2[$i][$j-1]['P']['detalle']){
			    			$arrGridTotal2[$i][$row-($countProc-1)]['P']['rowspan'] = $countProc;
			    			$countProc = 1;
			    		}
	    		
		    		}else if($j != 1 && $arrGridTotal2[$i][$j-1]['P']['detalle']){
	    				$arrGridTotal2[$i][$j]['P']['class'] = 'cell-vacia';
	    				$arrGridTotal2[$i][$j]['P']['unset'] = true;
		    			$arrGridTotal2[$i][$row-($countProc-1)]['P']['rowspan'] = $countProc;
		    			$countProc = 0;
		    		}
		    	}
	    	}	    	 
	    }

	    ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);
	    $allInputs['next'] = TRUE;
	    $fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;

		$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
		$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;

		$arrData['planning']['datos'] = $lista;
    	$arrData['planning']['horas'] = $arrHeader;
    	//$arrData['planning']['gridTotal'] = $arrGridTotal;
    	$arrData['planning']['gridTotal2'] = $arrGridTotal2;
    	$arrData['planning']['ambientes'] = $arrAmbientes;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay programaciones en la fecha seleccionada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function planing_horas_genera_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		if(empty($allInputs['origen'])){
			$allInputs['origen'] = false;
		}
		//  var_dump($allInputs['origen']); exit(); 
		if(!$allInputs['origen'] || $allInputs['origen'] == 'next' ){ // POR DEFECTO
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		}else if($allInputs['origen'] == 'prev'){
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);			
		}else if($allInputs['origen'] == 'calendar'){
			$fecha_consulta = $allInputs['desde'];
		}	

		if(empty($fecha_consulta)){
			$allInputs['next'] = TRUE;
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
			$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;

			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
			$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;

			$arrData['fecha_consulta'] = $allInputs['desde'];

			$arrData['flag'] = 0;
			if(!empty($allInputs['medico'])){
				$arrData['message'] = 'No hay programaciones cargadas para el medico: ' . $allInputs['medico']['medico'];
			}
			if(!empty($allInputs['especialidad'])){
				$arrData['message'] = 'No hay programaciones cargadas para la especialidad: ' . $allInputs['especialidad']['descripcion'];	
			}

			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData)); 
			return;
		}else if(!$allInputs['origen'] || $allInputs['origen'] == 'next' || $allInputs['origen'] == 'prev'){
			$allInputs['desde'] = $fecha_consulta;
		}	

		$allInputs['hasta'] = $allInputs['desde'];
		$arrData['fecha_consulta'] = date('d-m-Y', strtotime( $allInputs['desde']));
		$lista = $this->model_prog_medico->m_cargar_programaciones_generar_cupo($allInputs);		
		//$lista = $this->model_prog_medico->m_cargar_programaciones_por_fechas($allInputs);		

		$sede = $this->model_sede->m_consultar($this->sessionHospital['idsede']);
		$number = intval(explode(":",$sede['hora_final_atencion'])[0]);
		$hora_fin = str_pad($number-1,2,"0",STR_PAD_LEFT) . ':00:00';
		$horas = get_rangohoras($sede['hora_inicio_atencion'], $hora_fin);
		
		$datos = array('anyo' => date("Y"));
		$feriados = $this->model_feriado->m_lista_feriados_cbo($datos); 
		$arrFeriados = array();
		foreach ($feriados as $row) {
			array_push($arrFeriados,  $row['fecha']); 
		}

		$arrHeader = array();
		foreach ($horas as $item => $hora) { 
			//$strHora = $hora; 
			
			$segundos_horaInicial=strtotime($hora); 
			$segundos_minutoAnadir=30*60; 
			$horaFin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);
			array_push($arrHeader, 
				array(
					'hora' => $hora,
					'hora_fin' => $horaFin,
					'timestamp'=> strtotime($hora),
					'dato' => darFormatoHora($hora),
					'dato_fin' => darFormatoHora($horaFin),
					'class' =>  (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar ',
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE
				)
			);

			$segundos_horaInicial=strtotime($hora); 
			$segundos_minutoAnadir=30*60; 
			$nuevaHora=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

			$segundos_horaInicial=strtotime($nuevaHora); 
			$segundos_minutoAnadir=30*60; 
			$horaFin=date("H:i:s",$segundos_horaInicial+$segundos_minutoAnadir);

			array_push($arrHeader, 
				array(
					'hora' => $nuevaHora,
					'hora_fin' => $horaFin,
					'timestamp'=> strtotime($nuevaHora),
					'dato' => darFormatoHora($nuevaHora),
					'dato_fin'=> darFormatoHora($horaFin),
					'class' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? 'hora-sidebar feriado ' : 'hora-sidebar ', 
					'es_feriado' => (date("w", strtotime($allInputs['desde'])) == 0 || in_array($allInputs['desde'], $arrFeriados)) ? TRUE : FALSE 
				)
			);		
		}

		$listAmbientes = $this->model_ambiente->m_cargar_ambiente_por_sede( $this->sessionHospital['idsede'],null);
		/*
			amb.idambiente, numero_ambiente, piso, comentario, amb.orden_ambiente, amb.idsede, (amb.idcategoriaconsul) AS idcategoriaconsul, (cat.descripcion_cco) AS descripcion_cco, (amb.idsubcategoriaconsul) AS idsubcategoriaconsul,  (subcat.descripcion_scco) AS descripcion_scco

		*/
		// LIMPIAR AMBIENTES 
		$auxListaAmbientes = array();
		foreach ($lista as $key => $row) { 
			foreach ($listAmbientes as $keyAmb => $rowAmb) {
				if( $row['idambiente'] == $rowAmb['idambiente'] ){
					$auxListaAmbientes[$rowAmb['idambiente']] = $rowAmb; 
				}
			}
		}
		$auxListaAmbientes = array_values($auxListaAmbientes); 
		$listAmbientes = $auxListaAmbientes;


		$arrAmbientes = array();
		foreach ($listAmbientes as $ambiente) {
			$tag = substr($ambiente['descripcion_cco'], 0,2);
			$arrAmb = array(
					'dato' => $ambiente['numero_ambiente'],
					'class' => 'nombre-amb',
					'idambiente' => $ambiente['idambiente'],
					'piso'=> $ambiente['piso'],
					'orden'=> $ambiente['orden_ambiente'],
					'tag' => $tag,
					'classTag' => $tag == 'AD' ? 'badge-warning' : 'badge-success',
					); 
			array_push($arrAmbientes,$arrAmb);
		}

		$arrListado = array();
		$arrGridTotal = array();
		$arrGrid = array();			


		$countHoras = count($arrHeader);
		$countAmb = count($listAmbientes);
		$countProg = count($lista);

		$ind = 0;
		$i = 0;
		$j = 0;

		while($i < $countHoras){
			$j=0;
			$ind = 0;
			while ($j < $countAmb) {			
				$encontro = false;
				$idambiente = $listAmbientes[$j]['idambiente'];

				foreach ($lista as $prog_row) {
					/*$segundos_horaFin=strtotime($prog_row['hora_fin']); 
					$segundos_minutosResta=30*60; 
					$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);*/

					$segundos_horaFin=strtotime($prog_row['hora_fin']);
					$number = intval(explode(":",$prog_row['hora_fin'])[1]);
					if($number == 0 || $number == 30){
						$segundos_minutosResta=30*60;
						$hora_fin_comparar=date("H:i:s",$segundos_horaFin-$segundos_minutosResta);
					}else{
						$hora_fin_comparar=date("H:i:s",$segundos_horaFin);
					}

					if($arrHeader[$i]['hora'] >= $prog_row['hora_inicio'] && $arrHeader[$i]['hora'] <= $hora_fin_comparar  
						&& $prog_row['idambiente'] == $idambiente){
						$encontro = true;
						$total_cupos = $prog_row['total_cupos'];
						$total_cupos_ocupados = $prog_row['total_cupos_ocupados'];
						$total_cupos_no_cancelados = $prog_row['total_cupos_no_cancelados'];
						//$porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos,2); 
						if( !empty($total_cupos) ){
							$porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos,2); 
						}else{
							$porcentaje = 100;
						}

						// $porcentaje = round(($total_cupos_ocupados * 100) / $total_cupos_no_cancelados,2); 
						if($allInputs['especialidad']){
							$habilitada = ($allInputs['especialidad']['id'] == $prog_row['idespecialidad']) ? TRUE : FALSE;							
						}else{							
							$habilitada = TRUE ;
						}
						$timestamp = strtotime($prog_row['intervalo_hora']); 
						$intervaloHoraInt = date('i', $timestamp);
						$medico = $prog_row['med_nombres'] . ' ' . $prog_row['med_apellido_paterno'] . ' ' .$prog_row['med_apellido_materno'] ;
						if($j == 0){
							$position = "right";
						}else{
							$position = "left";
						}

						array_push($arrGrid, 
							array(
								'dato' => $prog_row['nombre_especialidad'],
								'class' => (!$habilitada) ? 'cell-programacion deshabilitada' : 'cell-programacion',
								'idambiente' => $prog_row['idambiente'],
								'fecha' => $prog_row['fecha_programada'],
								'fecha_str' => date('d-m-Y',strtotime($prog_row['fecha_programada'])),
								'especialidad' => $prog_row['nombre_especialidad'], 
								'idespecialidad' => $prog_row['idespecialidad'], 
								'idmedico' => $prog_row['idmedico'],	
								'idprogmedico' => $prog_row['idprogmedico'],
								'turno' => darFormatoHora($prog_row['hora_inicio']) . ' a ' . darFormatoHora($prog_row['hora_fin']),
								'medico' => $medico,
								'hora_inicio' => $prog_row['hora_inicio'],
								'tmp_hora_inicio' => strtotime($prog_row['hora_inicio']),
								'hora_fin' => $prog_row['hora_fin'],
								'tmp_hora_fin' => strtotime($prog_row['hora_fin']),
								'total_cupos' => $total_cupos,
								'total_cupos_ocupados' => $total_cupos_ocupados,
								'total_cupos_no_cancelados' => $total_cupos_no_cancelados,
								'porcentaje' => $porcentaje,
								'cupos_adicionales' => $prog_row['cupos_adicionales'],
								'ambiente'	=> $listAmbientes[$j],
								'headerHora' => $arrHeader[$i],								
								'rowspan' => 1,
								'unset' => FALSE,	
								'detalle' => TRUE,	
								'habilitada' => $habilitada,						
								'empresa' =>  $prog_row['empresa'],					
								'intervalo_hora_int' =>  $intervaloHoraInt,
								'tooltip_position'	=> $position,
								'tooltip_enable' => TRUE,				
								'tooltip_text' => 'MÉDICO: ' . $medico,				
								'total_adi_vendidos' => $prog_row['total_adi_vendidos'],				
							)
						);
					}

				}				
			
				if(!$encontro){
					array_push($arrGrid,
							array(
								'dato' => '',
								'class' => ($arrHeader[$i]['es_feriado']) ? 'cell-vacia feriado' : 'cell-vacia',
								'ambiente'	=> $listAmbientes[$j],
								'hora' => $arrHeader[$i],
								'es_feriado' => $arrHeader[$i]['es_feriado'],
								'rowspan' => 1,
								'unset' => FALSE,
								'detalle' => FALSE,
								'tooltip-enable' => FALSE,	
							)
						);
				}
				$j++;
			}			
				
			array_push($arrGridTotal, $arrGrid);
			$arrGrid = array();			
			$i++;
		}	

		$cellTotal = $countHoras;
		$cellColumn = count($listAmbientes);
		foreach ($listAmbientes as $i => $ambiente) {
		   	$inicio = -1;
   			$fin = -1;
   			$anterior = '';
   			$ite = 0;
   			foreach ($arrHeader as $row => $value) { 	
	    		$actual =  empty($arrGridTotal[$row][$i]['idprogmedico']) ? '' : $arrGridTotal[$row][$i]['idprogmedico']; 

	    		if($ite == 0){
	    			$anterior = $actual;
	    			$ite++;
	    		}  	

	    		if($inicio == -1)
	    			$inicio = $row;

	    		if($actual != $anterior){
	    			$fin = $row-1;
	    		}		    		

	    		if($inicio != -1 && $fin != -1){
	    			$rowspan =($fin - $inicio) + 1;
	    			$arrGridTotal[$inicio][$i]['rowspan'] = $rowspan;
					for ($fila=$inicio+1; $fila <= $fin; $fila++) { 
						$arrGridTotal[$fila][$i]['unset'] = TRUE;
					} 				
					$inicio = $row;
					$fin = -1;
	    		}else if($row == $cellTotal-1){
	    			$fin = $row;
    				$rowspan =($fin - $inicio) + 1;
					$arrGridTotal[$inicio][$i]['rowspan'] = $rowspan;
					for ($fila=$inicio+1; $fila <= $fin; $fila++) { 
    					$arrGridTotal[$fila][$i]['unset'] = TRUE;
    				}
    			}
				
				$anterior = empty($arrGridTotal[$row][$i]['idprogmedico']) ? '' : $arrGridTotal[$row][$i]['idprogmedico'];  			    		
	    	}	    	 
	    }

	    ini_set('xdebug.var_display_max_depth', 10);
	    ini_set('xdebug.var_display_max_children', 1024);
	    ini_set('xdebug.var_display_max_data', 1024);

	    $allInputs['next'] = TRUE;
	    $fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;

		$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
		$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;

		$arrData['planning']['datos'] = $lista;
    	$arrData['planning']['horas'] = $arrHeader;
    	$arrData['planning']['gridTotal'] = $arrGridTotal;
    	$arrData['planning']['ambientes'] = $arrAmbientes;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;    	

    	if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay programaciones en la fecha seleccionada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_programaciones_proc_fecha(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		if(empty($allInputs['origen'])){
			$allInputs['origen'] = false;
		}

		if($allInputs['producto']['idtipoproducto'] == '16'){
			$allInputs['tipo_atencion_medica'] = 'P';
		}else if($allInputs['producto']['idtipoproducto'] == '12'){
			$allInputs['tipo_atencion_medica'] = 'CM';
		}

		//  var_dump($allInputs['origen']); exit(); 
		if(!$allInputs['origen'] || $allInputs['origen'] == 'next' ){ // POR DEFECTO
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		}else if($allInputs['origen'] == 'prev'){
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);		
		}else if($allInputs['origen'] == 'calendar' || $allInputs['origen'] == 'ini'){
			$fecha_consulta = date('d-m-Y', strtotime( $allInputs['desde']));

		}

		if(empty($fecha_consulta)){
			$allInputs['next'] = TRUE;
			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
			$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;

			$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
			$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;

			$arrData['fecha_consulta'] = $allInputs['desde'];

			$arrData['flag'] = 0;
			if(!empty($allInputs['medico'])){
				$arrData['message'] = 'No hay programaciones cargadas para el medico: ' . $allInputs['medico']['medico'];
			}
			if(!empty($allInputs['especialidad'])){
				$arrData['message'] = 'No hay programaciones cargadas para la especialidad: ' . $allInputs['especialidad']['descripcion'];	
			}

			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData)); 
			return;
		}else if(!$allInputs['origen'] || $allInputs['origen'] == 'next' || $allInputs['origen'] == 'prev'){
			$allInputs['desde'] = $fecha_consulta;
		}
	
		$allInputs['hasta'] = $allInputs['desde'];
		$arrData['fecha_consulta'] = date('d-m-Y', strtotime( $allInputs['desde']));
		$lista = $this->model_prog_medico->m_cargar_programaciones_proc_fecha($allInputs);
		$arrListado = array();

		foreach ($lista as $key => $item) {	
		$medico = $item['med_nombres'].' '.$item['med_apellido_paterno'].' '.$item['med_apellido_materno'];
			array_push($arrListado, 
				array(
					'hora_inicio' => darFormatoHora($item['hora_inicio']),
					'hora_fin' => darFormatoHora($item['hora_fin']),
					'idprogmedico' => $item['idprogmedico'], 
					'fecha_programada' => $item['fecha_programada'], 
					'medico' => $medico,
					'numero_ambiente' => $item['numero_ambiente'],
				)
			);
		}

		$allInputs['next'] = TRUE;
	    $fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion($allInputs);
		$arrData['haySiguiente'] = empty($fecha_consulta) ? FALSE : TRUE;
		$fecha_consulta = $this->model_prog_medico->m_buscar_fecha_programacion_prev($allInputs);
		$arrData['hayAnterior'] = empty($fecha_consulta) ? FALSE : TRUE;
		$arrData['datos'] = $arrListado;
		$arrData['flag'] = 1;
		$arrData['message'] = '';

		if(empty($lista)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'No hay programaciones en la fecha seleccionada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_cupos_canal(){ 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_prog_medico->m_cargar_cupos_canal($allInputs['programacion']['idprogmedico'], $allInputs['canal']['idcanal']);
		$arrListado = array();

		if( @$allInputs['estado']['id'] === 1 ){ 
			foreach ($lista as $key => $row) { 
				if( !($row['estado_cupo'] == 2) ){ 
					unset($lista[$key]);
				}
			}
		}
		// var_dump($lista); exit(); 

		foreach ($lista as $key => $item) {	
			$estado_cupo = ''; 
			$clase = '';
			if($item['estado_cupo'] == 1){
				$estado_cupo = 'OCUPADO';
				$clase = 'label-danger';
			}elseif($item['estado_cupo'] == 2){
				$estado_cupo = 'DISPONIBLE';
				$clase = 'label-success';
			}elseif($item['estado_cupo'] == 3){
				$estado_cupo = 'CANCELADO';
				$clase = 'label-default';
			}elseif($item['estado_cupo'] == 4){
				$estado_cupo = 'REPROGRAMADO';
				$clase = 'label-inverse';
			} 
			$preStr = '';
			if( $item['si_adicional'] == 1 ){
				$preStr.= '+ ';
			}else{
				$preStr.= 'nº ';
			} 
			array_push($arrListado, 
				array(
					'hora_inicio_formato' => darFormatoHora($item['hora_inicio_det']),
					'hora_fin_formato' => darFormatoHora($item['hora_fin_det']),
					'turno' => darFormatoHora($item['hora_inicio_det']), 
					'hora_inicio_det' => $item['hora_inicio_det'],
					'hora_fin_det' => $item['hora_fin_det'],
					'iddetalleprogmedico' => $item['iddetalleprogmedico'],
					'idprogmedico' => $item['idprogmedico'], 
					'idcanal' => $item['idcanal'], 
					'numero_cupo' => $preStr.$item['numero_cupo'],
					'si_adicional' => ($item['si_adicional'] == 1) ? TRUE : FALSE, 
					'tipo_cupo' => ($item['si_adicional'] == 1) ? 'adicional' : 'no', 
					'cliente'=> $item['cliente'], 
					'ticket' => $item['ticket'],
					'estado_cupo' => array( 
						'string' => $estado_cupo,
						'clase' =>$clase,
						'bool' =>$item['estado_cupo']
					)
				)
			);
		}

		$arrData['datos'] = $arrListado;
		$arrData['paginate']['totalRows'] = count($arrListado);
		$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cancelar_cupo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'El cupo no pudo ser cancelado. Intente nuevamente';
    	$arrData['flag'] = 0;

    	$cupo = $this->model_prog_medico->m_consulta_cupo($allInputs['iddetalleprogmedico']);
    	if($cupo['estado_cupo'] != 2 && $cupo['estado_cupo'] != 1 && $cupo['estado_cupo'] != 4){
    		$arrData['message'] = 'Solo puede cancelar cupos en estado DISPONIBLE, CONFIRMADO o REPROGRAMADO.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	$cita = $this->model_prog_cita->m_conculta_cita_cupo($allInputs['iddetalleprogmedico']);
    	if(empty($cita)){
    		$allInputs['idprogcita'] = null;
    	}else{
    		$allInputs['idprogcita'] = $cita['idprogcita'];
	    	if($cupo['estado_cupo'] == 1 && ($cita['estado_cita'] != 2 && $cita['estado_cita'] != 4)){
	    		$arrData['message'] = 'Solo puede cancelar cupos en estado DISPONIBLE, CONFIRMADO o REPROGRAMADO.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}

	    	if($this->model_prog_cita->m_cita_tiene_atencion($allInputs)){
	    		$arrData['message'] = 'No puede cancelar una cita con atención registrada.';
	    		$arrData['flag'] = 0;
	    		$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
	    	}
    	}    	   	

    	$totalNoCancelados =  $this->model_prog_medico->m_contar_cupos_cancelables($cupo['idprogmedico']);
     	$this->db->trans_start();
		$data = array(
				'estado_cupo' => 3,
				'iddetalleprogmedico' => $allInputs['iddetalleprogmedico'],
				);
		$envioCorreo = FALSE;
		if($this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data)){	 //cancelo detalle	
			$resultCita = TRUE;
			if($allInputs['idprogcita'] != null && $cita['estado_cita'] != 4){
				$resultCita = FALSE;
				$datos = array(
					'idprogcita' => $allInputs['idprogcita'],
					'estado_cita' => 3,
					'descripcion_motivo' => $allInputs['descripcion_motivo'],
					);
				$resultCita = $this->model_prog_cita->m_cambiar_estado_cita($datos);
				$envioCorreo = TRUE;
			}

			$resultProg = TRUE;
			$resultCuposCanal = TRUE;
			$resultCuposProg = TRUE;
			if(!$allInputs['si_adicional']){
				if($totalNoCancelados == 1){
					$resultProg = $this->model_prog_medico->m_update_encabezado_prog($allInputs);
				}else{
					$resultProg = $this->model_prog_medico->m_update_prog_hora_fin($allInputs);	
					if($resultProg){
						$ultimo = $this->model_prog_medico->m_ultimo_cupo_master($allInputs);				
						if($allInputs['iddetalleprogmedico'] == $ultimo['iddetalleprogmedico']){
							$resultProg = FALSE;
							$resultProg = $this->model_prog_medico->m_update_prog_hora_fin_inicial($allInputs);				
						}
					}
				}				
				
				if($allInputs['idprogcita'] == null){
					$resultCuposCanal = $this->model_prog_medico->m_cambiar_cupos_canales($allInputs); 
					$resultCuposProg = $this->model_prog_medico->m_cambiar_cupos_programacion($allInputs);
				}
			}

			if($resultProg && $resultCita && $resultCuposCanal && $resultCuposProg){
				$arrData['message'] = 'Ha sido cancelado el cupo correctamente';
				$arrData['flag'] = 1;
				if($envioCorreo){
					$citaPaciente = array(
						'paciente' => $allInputs['paciente'],
						'email' => $allInputs['email'],
						'especialidad' => $allInputs['especialidad'],
						'medico' => $allInputs['medico'],

						'fecha_programada' => date( "d-m-Y", strtotime( $allInputs['fecha_atencion_cita'] ) ),
						'turno' => $allInputs['turno'],					
						'ambiente' => $allInputs['ambiente'],
						'sede' => $this->sessionHospital['sede'],
						);
	    			$resultMail = enviar_mail_paciente(4,$citaPaciente);
					$arrData['flagMail']  = $resultMail['flag'];
					$arrData['msgMail']  = $resultMail['msgMail'];
				}
			}
		}   	
		$this->db->trans_complete();    	

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	

	public function reprogramar_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'La cita no pudo ser reprogramada. Intente nuevamente';
    	$arrData['flag'] = 0;
    	$cupo = $this->model_prog_medico->m_consulta_cupo($allInputs['oldCita']['iddetalleprogmedico']);
    	$cita = $this->model_prog_cita->m_conculta_cita_cupo($allInputs['oldCita']['iddetalleprogmedico']);
    	$allInputs['oldCita']['idprogcita'] = $cita['idprogcita'];
    	if(($cupo['estado_cupo'] == 1 || $cupo['estado_cupo'] == 3)  && ($cita['estado_cita'] != 2 && $cita['estado_cita'] != 3)){
    		$arrData['message'] = 'Solo puede reprogramar citas en estado CONFIRMADO o CANCELADO.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if($this->model_prog_cita->m_cita_tiene_atencion($allInputs['oldCita'])){
    		$arrData['message'] = 'No puede reprogramar una cita con atención registrada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}  
    	
    	/* Motivos    
            {id:1, descripcion: 'CANCELACION POR MOTIVOS DE MEDICO'}, //cancela cupo y reprograma cita
            {id:2, descripcion: 'CANCELACION POR MOTIVOS DE PACIENTE'}, //reprograma cupo y reprograma cita
            {id:3, descripcion: 'FUE SOLO CANCELADA PREVIAMENTE o FUE CANCELADA TODA LA PROG'}, //reprograma cita
		*/

     	$this->db->trans_start();
     	if($allInputs['motivo']['id'] == 1){
     		//cupo a cancelado
			$data = array(
				'estado_cupo' => 3,
				'iddetalleprogmedico' => $allInputs['oldCita']['iddetalleprogmedico'],
				);	
			$resulDetalle = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);

			//si cancelo y no es un adicional debo actualizar encabezado programacion
			$resultProg = TRUE;
			if(!$allInputs['oldCita']['si_adicional']){
				$resultProg = FALSE;
				$resultProg = $this->model_prog_medico->m_update_prog_hora_fin($allInputs['oldCita']);	
				if($resultProg){
					$ultimo = $this->model_prog_medico->m_ultimo_cupo_master($allInputs['oldCita']);				
					if($allInputs['oldCita']['iddetalleprogmedico'] == $ultimo['iddetalleprogmedico']){						
						$resultProg = $this->model_prog_medico->m_update_prog_hora_fin_inicial($allInputs['oldCita']);				
					}
				}							
			}
     	}

		if($allInputs['motivo']['id'] == 2){
			//cupo a reprogramado
			if($allInputs['oldCita']['idcanal'] != 3){	
				$data = array(
					'estado_cupo' => 4,
					'iddetalleprogmedico' => $allInputs['oldCita']['iddetalleprogmedico'],
					);
				$resulDetalle = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);
				$resultProg = TRUE;
			}else{
			//cupo a liberado
				$data = array(
					'estado_cupo' => 2,
					'iddetalleprogmedico' => $allInputs['oldCita']['iddetalleprogmedico'],
					);
				$resulDetalle = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);
				$resultProg = FALSE;
				if(!$allInputs['oldCita']['si_adicional']){
					//actualizo cantidad de cupos disponibles y ocupados 
					$resultOldCuposCanal = $this->model_prog_medico->m_revertir_cupos_canales($allInputs['oldCita']); 
					$resultOldCuposProg = $this->model_prog_medico->m_revertir_cupos_programacion($allInputs['oldCita']);
					if($resultOldCuposProg && $resultOldCuposCanal){
						$resultProg = TRUE;
					}						
				}
			}
		}

		if($allInputs['motivo']['id'] == 3){
			$resultProg = TRUE;	
			$resulDetalle = TRUE;
		}

		if($resultProg && $resulDetalle){	
			if($allInputs['oldCita']['idcanal'] != 3){			 	
				$resultCita = FALSE;
				$datos = array(
					'idprogcita' => $allInputs['oldCita']['idprogcita'],
					'estado_cita' => 4,
					'descripcion_motivo' => empty($allInputs['descripcion_motivo'])? '' :  $allInputs['descripcion_motivo'],
					);
				$resultCita = $this->model_prog_cita->m_cambiar_estado_cita($datos); //cita a reprogramada

				$resultNuevaCita = FALSE;
				if($resultCita){
					$datos = array(
						'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
						'fecha_reg_reserva' => date('Y-m-d H:i:s'),
						'fecha_reg_cita' => date('Y-m-d H:i:s'),
						'fecha_atencion_cita' => $allInputs['seleccion']['fecha_programada']. " " . $allInputs['seleccion']['hora_inicio_det'],
						'idcliente' => $allInputs['oldCita']['idcliente'],
						'idempresacliente' => $allInputs['oldCita']['idempresacliente'],
						'estado_cita' => 2,
						'idproductomaster' => $allInputs['oldCita']['idproductomaster'],
						'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
						);

					$resultNuevaCita = $this->model_prog_cita->m_registrar($datos); //nueva cita para paciente
					
					if($resultNuevaCita){
						//actualizar cita en detalle de venta
						$resulDetalle = FALSE;
						$idprogcita = GetLastId('idprogcita','pa_prog_cita');
						$resulDetalle = $this->model_venta->m_actualizar_detalle_cita_repro($allInputs['oldCita']['iddetalle'],$idprogcita);

						//actualiza estadisticas en programacion de la cita escogida
						$data = array(
							'estado_cupo' => 1,
							'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
						);
						$resulDetalleNuevo = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);
						$resultCuposCanal = $this->model_prog_medico->m_cambiar_cupos_canales($allInputs['seleccion']); 
						$resultCuposProg = $this->model_prog_medico->m_cambiar_cupos_programacion($allInputs['seleccion']); 
					}
				}			
			}else{
				$resultCita = TRUE;
				$resulDetalle = TRUE;				
				//actualiza detalle nuevo en la cita 
				$datos = array(
					'idprogcita' => $allInputs['oldCita']['idprogcita'],
					'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
					'fecha_atencion_cita' => $allInputs['seleccion']['fecha_programada'] . ' ' . $allInputs['seleccion']['hora_inicio_det'],
					);
				$resultNuevaCita = $this->model_prog_cita->m_cambiar_datos_en_cita($datos);
				//actualiza estadisticas en programacion de la cita escogida
				$data = array(
					'estado_cupo' => 1,
					'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
				);

				$resulDetalleNuevo = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);
				if($allInputs['seleccion']['idcanal'] == 3){					
					$resultCuposCanal = $this->model_prog_medico->m_cambiar_cupos_canales($allInputs['seleccion']); 
					$resultCuposProg = $this->model_prog_medico->m_cambiar_cupos_programacion($allInputs['seleccion']); 
				}else{					
					if($this->model_prog_medico->m_ajustar_canal($allInputs['seleccion']) &&
						$this->model_prog_medico->m_agregar_uno_canal_web($allInputs['seleccion']) ){
							$data = array(
								'idcanal' => 3,
								'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
							);
							$resulDetalleNuevo = $this->model_prog_medico->m_cambiar_canal_cupo($data);

							$seleccion = array(
								'idprogmedico' => $allInputs['seleccion']['idprogmedico'],
								'idcanal' => 3,
							);
							$resultCuposCanal = $this->model_prog_medico->m_cambiar_cupos_canales($seleccion); 
							$resultCuposProg = $this->model_prog_medico->m_cambiar_cupos_programacion($seleccion); 
					} 
				}
			}

			if($resultCita && $resultNuevaCita && $resulDetalle && $resulDetalleNuevo && $resultCuposCanal && $resultCuposProg){
				$arrData['message'] = 'La cita ha sido reprogramada correctamente';
    			$arrData['flag'] = 1;
    			$citaPaciente = array(
					'paciente' => $allInputs['oldCita']['paciente'],
					'email' => $allInputs['oldCita']['email'],
					'especialidad' => $allInputs['oldCita']['especialidad'],
					'medico' => $allInputs['oldCita']['medico'],

					'fecha_programada' => $allInputs['seleccion']['fecha_str'],
					'turno' => $allInputs['seleccion']['turno'],					
					'ambiente' => $allInputs['seleccion']['ambiente'],
					'sede' => $this->sessionHospital['sede'],
					);
    			$resultMail = enviar_mail_paciente(2,$citaPaciente);
				$arrData['flagMail']  = $resultMail['flag'];
				$arrData['msgMail']  = $resultMail['msgMail'];
			}			
		}		    		
    	
		$this->db->trans_complete();   

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_confirmar_accion(){
		$this->load->view('prog-medico/cancelarReprogramarCupo_formView'); 
	}	
	public function ver_popup_motivo_accion(){
		$this->load->view('prog-medico/modalMotivoAccion_formView');
	}

	public function verifica_cupo_reprogramar(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = '';
    	$arrData['flag'] = 0;
    	$allInputs['idprogmedico'] = intval($allInputs['cupo']['idprogmedico']);

    	$cupo = $this->model_prog_medico->m_consulta_cupo($allInputs['cupo']['iddetalleprogmedico']);
    	$cita = $this->model_prog_cita->m_conculta_cita_cupo($allInputs['cupo']['iddetalleprogmedico']);
    	$ultimo_sin_cancelar = $this->model_prog_medico->m_ultimo_cupo_sin_cancelar($allInputs, 1);
		if(empty($ultimo_sin_cancelar['iddetalleprogmedico'])){
			$ultimo_sin_cancelar = $this->model_prog_medico->m_ultimo_cupo_sin_cancelar($allInputs, 2);
		}   	

    	if($allInputs['motivo']['id'] == 0){
    		$arrData['message'] = 'Debe seleccionar un motivo de Cancelación/Reprogramación';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	$es_ultimo = ($allInputs['cupo']['iddetalleprogmedico'] == $ultimo_sin_cancelar['iddetalleprogmedico']) ? TRUE : FALSE;
    	if(!$es_ultimo && $cupo['estado_cupo'] == 1 && $allInputs['motivo']['id'] == 1){
    		$arrData['message'] = 'La cancelación por motivos del médico solo puede hacerse comenzando por el último cupo sin cancelar (de abajo hacia arriba).';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}

    	if($cupo['estado_cupo'] != 2 && $cita['estado_cita'] != 2 && $allInputs['motivo']['id'] == 2){
    		$arrData['message'] = 'La reprogramación por motivos del paciente solo puede hacerse en citas con estado "CONFIRMADO".';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
		    return;
    	}  

    	if($this->model_prog_cita->m_cita_tiene_atencion($cita)){
    		$arrData['message'] = 'No puede Cancelar/Reprogramar una cita con atención registrada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 

    	$arrData['flag'] = 1;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	    return;  	
		// >>>>>>> refs/remotes/origin/b_yerita
	}

	public function cambiar_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); 
		$arrData['message'] = 'La cita no pudo ser modificada. Intente nuevamente';
    	$arrData['flag'] = 0;
    	
    	$cita = $this->model_prog_cita->m_conculta_cita_cupo($allInputs['oldCita']['iddetalleprogmedico']);
    	$allInputs['oldCita']['idprogcita'] = $cita['idprogcita'];
    	if($cita['estado_cita'] != 2){
    		$arrData['message'] = 'Solo puede modificar citas en estado CONFIRMADO.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if($this->model_prog_cita->m_cita_tiene_atencion($allInputs['oldCita'])){
    		$arrData['message'] = 'No puede modificar una cita con atención registrada.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	} 

    	$this->db->trans_start();
 		//cupo a disponible
		$data = array(
			'estado_cupo' => 2,
			'iddetalleprogmedico' => $allInputs['oldCita']['iddetalleprogmedico'],
			);	
		$resulDetalle = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);
		//si cancelo y no es un adicional debo actualizar encabezado programacion
		$resultOldCuposCanal = FALSE;
		$resultOldCuposProg = FALSE;
		if(!$allInputs['oldCita']['si_adicional']){
			//actualizo cantidad de cupos disponibles y ocupados 
			$resultOldCuposCanal = $this->model_prog_medico->m_revertir_cupos_canales($allInputs['oldCita']); 
			$resultOldCuposProg = $this->model_prog_medico->m_revertir_cupos_programacion($allInputs['oldCita']);						
		}else{
			$resultOldCuposCanal = TRUE;
			$resultOldCuposProg = TRUE;
		}
     	
		if($resulDetalle && $resultOldCuposCanal && $resultOldCuposProg){
			//cambio id de cupo en cita
			$resultCita = FALSE;
			$datos = array(
				'idprogcita' => $allInputs['oldCita']['idprogcita'],
				'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
				'fecha_atencion_cita' => $allInputs['seleccion']['fecha_programada'] . ' ' . $allInputs['seleccion']['hora_inicio_det'],
				);
			$resultCita = $this->model_prog_cita->m_cambiar_datos_en_cita($datos); //cita con nuevo iddetalleprogmedico
			if($resultCita ){
				$resultCuposCanal = $this->model_prog_medico->m_cambiar_cupos_canales($allInputs['seleccion']); 
				$resultCuposProg = $this->model_prog_medico->m_cambiar_cupos_programacion($allInputs['seleccion']);	
				$data = array(
					'estado_cupo' => 1,
					'iddetalleprogmedico' => $allInputs['seleccion']['iddetalleprogmedico'],
					);	
				$resulDetalleNuevo = $this->model_prog_medico->m_cambiar_estado_detalle_de_programacion($data);

				
				if($resulDetalle && $resultOldCuposCanal && $resultOldCuposProg && $resultCita && $resultCuposCanal && $resultCuposProg && $resulDetalleNuevo){
					$arrData['message'] = 'La cita ha sido modificada correctamente';
	    			$arrData['flag'] = 1;
	    			$citaPaciente = array(
						'paciente' => $allInputs['oldCita']['paciente'],
						'email' => $allInputs['oldCita']['email'],
						'especialidad' => $allInputs['oldCita']['especialidad'],
						'medico' => $allInputs['oldCita']['medico'],

						'fecha_programada' => $allInputs['seleccion']['fecha_str'],
						'turno' => $allInputs['seleccion']['turno'],					
						'ambiente' => $allInputs['seleccion']['ambiente'],
						'sede' => $this->sessionHospital['sede'],
						);
	    			$resultMail = enviar_mail_paciente(3,$citaPaciente);
					$arrData['flagMail']  = $resultMail['flag'];
					$arrData['msgMail']  = $resultMail['msgMail'];
				}
			}
						
		}	    		
    	
		$this->db->trans_complete();   

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	// PARA ATENCION MEDICA
	public function cargar_programacion_medico_cbo()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['fecha'] = date('Y-m-d'); 	
		$lista = $this->model_prog_medico->m_cargar_programaciones_medico($allInputs);
		// var_dump($lista); exit();
		$arrPrincipal = array();
		foreach ($lista as $row) {
			array_push($arrPrincipal, array(
				'id' => $row['idprogmedico'],
				'descripcion' => $row['hora_inicio'] . ' - ' . $row['hora_fin'],
				)
			);
		}
		// var_dump($arrPrincipal); exit();
		$arrData['flag'] = 1;
		$arrData['datos'] = $arrPrincipal;
    	$arrData['message'] = '';
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	    return; 
	}
}