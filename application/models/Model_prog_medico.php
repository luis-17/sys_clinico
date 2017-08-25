<?php
class Model_prog_medico extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
	public function m_cargar_especialidades_programadas_por_fechas($datos)
	{
		//var_dump($datos); exit();
		// $this->db->select('(SELECT COUNT(*) FROM pa_detalle_prog_medico sc_dpm WHERE prm.idprogmedico = sc_dpm.idprogmedico) AS contador',FALSE); 
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->select("ARRAY_TO_STRING(ARRAY_AGG(prm.idprogmedico ORDER BY prm.idprogmedico ),',') AS idsempresamedico",FALSE); 
		$this->db->select("ARRAY_TO_STRING(ARRAY_AGG(prm.activo ORDER BY prm.idprogmedico ),',') AS progactivo",FALSE);		
		$this->db->select('prm.fecha_programada, am.idambiente, am.numero_ambiente, esp.idespecialidad, esp.nombre, prm.tipo_atencion_medica'); 
		$this->db->from('pa_prog_medico prm');
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul');
		//renombrado
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');
		// $this->db->where('estado_amb', 1); // 1:habilitado 
		$this->db->join('sede_empresa_admin sea','prm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');

		$this->db->where('DATE(prm.fecha_programada) BETWEEN '. $this->db->escape($datos['desde']). ' AND '. $this->db->escape($datos['hasta'])); 

		if(!empty($datos['itemEspecialidad']) ){
			$this->db->where('esp.idespecialidad',$datos['itemEspecialidad']['id']);
		}

		if(!empty($datos['itemMedico']) && $datos['itemMedico']['idmedico'] != null){
			$this->db->where('prm.idmedico',$datos['itemMedico']['idmedico']);
		}

		if(!empty($datos['tipoAtencion']) && $datos['tipoAtencion'] != 'all'){
			$this->db->where('prm.tipo_atencion_medica',$datos['tipoAtencion']);
		}

		// if(!empty($datos['itemAmbiente']) && $datos['itemAmbiente']['id'] != 0){
		// 	$this->db->where('cac.idcategoriaconsul', $datos['itemAmbiente']['id']); //ver solo un tipo
		// }

		if(!empty($datos['itemEstado'])){
			$this->db->where('estado_prm', $datos['itemEstado']['id']); // ver por estado
		}

		$this->db->where('estado_amb', 1);		 
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // 1:habilitado 
		//$this->db->where('ea.ruc',$this->sessionHospital['ruc_empresa_admin']); 
		$this->db->group_by('prm.fecha_programada, am.numero_ambiente, am.idambiente, esp.idespecialidad, prm.tipo_atencion_medica'); 
		$this->db->order_by('am.numero_ambiente, prm.fecha_programada'); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_estas_programaciones($arrIdProg, $reprog = FALSE, $view = FALSE, $tipoAtencion) // cupos_adicionales 
	{ 		
		/*$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados,prm.activo, 
			dpm.iddetalleprogmedico ,dpm.hora_inicio_det, dpm.hora_fin_det, dpm.intervalo_hora_det, dpm.si_adicional, dpm.numero_cupo, 
			dpm.estado_cupo, cpm.idcanalprogmedico, ca.idcanal, ca.descripcion_can, (em.descripcion) AS empresa, em.idempresa, 
			cpm.total_cupos, emp.idempleado, emp.nombres, emp.apellido_paterno, emp.apellido_materno, 
			med.idmedico, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno, 
			am.idambiente, am.numero_ambiente, am.piso, esp.idespecialidad, esp.nombre, 
			cac.idcategoriaconsul, cac.descripcion_cco, scac.idsubcategoriaconsul, scac.descripcion_scco, 
			(scac_renom.idsubcategoriaconsul) AS idsubcategoriarenom, (scac_renom.descripcion_scco) AS subcategoriarenom, total_cupos cupos_adicionales
			prm.idempresamedico, prm.estado_prm'); */
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados,prm.activo');		

		$this->db->select('emp.idempleado, emp.nombres, emp.apellido_paterno, emp.apellido_materno, 
			med.idmedico, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno, 
			am.idambiente, am.numero_ambiente, am.piso, esp.idespecialidad, esp.nombre, 
			cac.idcategoriaconsul, cac.descripcion_cco, scac.idsubcategoriaconsul, scac.descripcion_scco, 
			(scac_renom.idsubcategoriaconsul) AS idsubcategoriarenom, (scac_renom.descripcion_scco) AS subcategoriarenom,
			prm.idempresamedico, prm.estado_prm'); 
		$this->db->select('(em.descripcion) AS empresa, em.idempresa'); 
		$this->db->select('( SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				LEFT JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
			) as total_cupos');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
				AND sc_dpm.estado_cupo <> 3 
			) as cupos_ocupados');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 1 
				AND sc_dpm.estado_cupo = 1 
				AND sc_pc.estado_cita <> 0 
			) as total_adi_vendidos');
		/*$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico dpm 
				WHERE dpm.idprogmedico = prm.idprogmedico 
				AND dpm.si_adicional = 2 
				AND dpm.estado_cupo = 2
			) as cupos_disponibles');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico dpm 
				WHERE dpm.idprogmedico = prm.idprogmedico 
				AND dpm.si_adicional = 2 
				AND dpm.estado_cupo <> 0 
				AND dpm.estado_cupo <> 2
			) as cupos_ocupados');*/
		
		$this->db->from('pa_prog_medico prm'); 

		if($tipoAtencion == 'CM'){
			$this->db->select('dpm.iddetalleprogmedico ,dpm.hora_inicio_det, dpm.hora_fin_det, dpm.intervalo_hora_det, dpm.si_adicional, dpm.numero_cupo, 
			dpm.estado_cupo, cpm.idcanalprogmedico, ca.idcanal, ca.descripcion_can'); //cpm.total_cupos, cpm.cupos_disponibles, cpm.cupos_ocupados

			if($view == 'notificacion_anulado'){
				$this->db->join('pa_detalle_prog_medico dpm','prm.idprogmedico = dpm.idprogmedico ');
			}else{
				$this->db->join('pa_detalle_prog_medico dpm','prm.idprogmedico = dpm.idprogmedico AND dpm.estado_cupo <> 0');
			}

			$this->db->join('pa_canal ca','dpm.idcanal = ca.idcanal'); 
			$this->db->join('pa_canal_prog_medico cpm','ca.idcanal = cpm.idcanal AND prm.idprogmedico = cpm.idprogmedico'); 
		} 

		//medico-especialidad-empresa 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa_especialidad emesp','emme.idempresaespecialidad = emesp.idempresaespecialidad AND emesp.idespecialidad = prm.idespecialidad'); 
		$this->db->join('pa_empresa_detalle emde','emesp.idempresadetalle = emde.idempresadetalle'); 
		$this->db->join('empresa em','em.idempresa = emde.idempresatercera'); 

		

		$this->db->join('rh_empleado emp','prm.idresponsable = emp.idempleado'); 

		$this->db->join('medico med','prm.idmedico = med.idmedico'); 

		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul'); 
		// renombrado 
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 

		$this->db->where('am.estado_amb', 1); 
		if($reprog){
			$this->db->where('prm.estado_prm', 2); // 2:cancelado
		}else if($view == 'notificacion_anulado'){
			$this->db->where('prm.estado_prm = ', 0); // ANULADO

			if($tipoAtencion == 'CM'){
				$this->db->where('dpm.estado_cupo =', 0); // ANULADO
			}
			
		}else {
			if(!empty($datos['itemEstado'])){
				$this->db->where('estado_prm', $datos['itemEstado']['id']); // ver por estado
				if($tipoAtencion == 'CM'){
					$this->db->where_in('dpm.estado_cupo', array(1,2,3,4)); // DISPONIBLE, OCUPADO, CANCELADO, REPROGRAMADO
				}
			}
		}	
		
		$this->db->where_in('prm.idprogmedico',$arrIdProg); 
		if($tipoAtencion == 'CM'){
			$this->db->order_by('am.numero_ambiente ASC, prm.fecha_programada ASC, dpm.si_adicional DESC, dpm.numero_cupo ASC'); 
		}else{
			$this->db->order_by('am.numero_ambiente ASC, prm.fecha_programada ASC'); 
		}
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_esta_programacion($idProgMedico)
	{
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados, prm.activo, 
			(em.descripcion) AS empresa, em.idempresa, am.idambiente, am.numero_ambiente, am.piso, 
			cac.idcategoriaconsul, cac.descripcion_cco, scac.idsubcategoriaconsul, scac.descripcion_scco, 
			(scac_renom.idsubcategoriaconsul) AS idsubcategoriarenom, (scac_renom.descripcion_scco) AS subcategoriarenom'); 
		$this->db->select('med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('esp.nombre');
		$this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0) as total_cupos');
		
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul'); 


		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		// renombrado 
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		$this->db->where('am.estado_amb', 1); 
		$this->db->where('prm.estado_prm <>', 0); // registrado  o cancelado
		$this->db->where('prm.idprogmedico',$idProgMedico); 
		return $this->db->get()->row_array(); 
	}
	public function m_editar_programacion($datos)
	{
		$data = array( 
			'idambiente'=> $datos['idambiente'], 
			'idsubcategoriaconsul'=> $datos['idsubcategoriaconsul'], 
			'si_renombrado_scc'=> $datos['si_renombrado_scc'], 
			'idsubcategoriaconsulrenom'=> $datos['idsubcategoriaconsulrenom'],
			'fecha_programada'=> $datos['fecha_programada'],
			'hora_inicio' => $datos['hora_inicio'],
			'hora_fin' => $datos['hora_fin'],
			'intervalo_hora' => $datos['intervalo_hora'],
			'cupos_adicionales' => $datos['cupos_adicionales'],
			'comentario_pmed'=> $datos['comentario'],
			'activo' => $datos['activo'],
			'updatedAt'=> date('Y-m-d H:i:s')

		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_prog_medico', $data );
	}
	public function m_editar_canales_de_programacion($datos)
	{
		$data = array( 
			//'idcanal'=> $datos['idcanal'], 
			'total_cupos'=> $datos['total_cupos'], 
			'cupos_disponibles'=> $datos['total_cupos'], 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idcanalprogmedico',$datos['idcanalprogmedico']); 
		return $this->db->update('pa_canal_prog_medico', $data ); 
	}
	public function m_editar_detalle_de_programacion($datos) 
	{
		$data = array( 
			'idcanal'=> $datos['idcanal'], 
			'hora_inicio_det'=> $datos['hora_inicio_det'], 
			'hora_fin_det'=> $datos['hora_fin_det'],
			'intervalo_hora_det'=> $datos['intervalo_hora_det'],
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('iddetalleprogmedico',$datos['iddetalleprogmedico']); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}
	public function m_anular_detalle_de_programacion($datos) 
	{
		$data = array( 
			'estado_cupo'=> 0 
		);
		$this->db->where('iddetalleprogmedico',$datos['iddetalleprogmedico']); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}
	public function m_registrar_prog_medico($datos, $reprog = FALSE){ 
		//var_dump($datos); exit();
		$data = array(
			'fecha_programada' => $datos['fecha_programada'],
			'hora_inicio' => $datos['hora_inicio'],
			'hora_fin' => $datos['hora_fin'],
			'intervalo_hora' =>   $datos['intervalo_hora'],
			'cupos_adicionales' => $datos['cupos_adicionales'],
			'comentario_pmed' => $datos['comentario_pmed'],
			'activo' => $datos['activo'],
			'si_renombrado_scc' => $datos['si_renombrado_scc'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
			'idempresamedico' => $datos['idempresamedico'],
			'idmedico' => $datos['idmedico'],
			'idresponsable' => $this->sessionHospital['idempleado'],
			'idambiente' => $datos['idambiente'],
			'idespecialidad' => $datos['idespecialidad'],
			'idsubcategoriaconsul' => $datos['idsubcategoriaconsul'],
			'idsubcategoriaconsulrenom' => $datos['idsubcategoriaconsulrenom'],
			'idsede' => $datos['idsede'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'tipo_atencion_medica' => $datos['tipo_atencion_medica']
		);

		if($reprog){
			$data['idprogmedico_old'] = $datos['idprogmedico_old'];
		}
		return $this->db->insert('pa_prog_medico', $data );
	}
	public function m_registrar_canal_prog_medico($canal, $idprogmedico){
		$data = array(
			'idprogmedico' => $idprogmedico,
			'idcanal' => $canal['id'],
			'total_cupos'=> $canal['cant_cupos_canal'],
			'cupos_disponibles'=> $canal['cant_cupos_canal'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);

		return $this->db->insert('pa_canal_prog_medico', $data);
	}	
	public function m_registrar_detalle_prog_medico($datos){
		$data = array(
			'idprogmedico' => $datos['idprogmedico'],
			'idcanal' => $datos['idcanal'],
			'hora_inicio_det' => $datos['hora_inicio_det'],
			'hora_fin_det' => $datos['hora_fin_det'],
			'intervalo_hora_det' => $datos['intervalo_hora_det'],
			'numero_cupo' => $datos['numero_cupo'],
			'si_adicional' => $datos['si_adicional'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);

		return $this->db->insert('pa_detalle_prog_medico', $data);
	}

	public function m_verifica_planing($datos,$edit=FALSE){ 
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from('pa_prog_medico');
		$this->db->where('estado_prm', 1); //registrado
		$this->db->where('tipo_atencion_medica', $datos['tipo_atencion_medica']);
		$this->db->where('fecha_programada', $datos['fecha_programada']); //fecha
		//mismo ambiente 
		$this->db->where("idambiente ", $datos['idambiente'] ); 
		if($edit){ 
			$this->db->where('idprogmedico <>',$datos['idprogmedico']); 
		}

		//mismo horario o similar
		$where1 = "( hora_inicio = '".$datos['hora_inicio']. "' AND  hora_fin = '".$datos['hora_fin'] . "' ) ";
		$where2 = "('". $datos['hora_inicio']. "' > hora_inicio AND  '".$datos['hora_inicio'] . "'  < hora_fin )" ;		
		$where3 = "('". $datos['hora_fin']. "' > hora_inicio AND  '".$datos['hora_fin'] . "'  < hora_fin )" ;
		$where4 = "(hora_inicio > '".$datos['hora_inicio'] . "' AND  hora_fin < '".$datos['hora_fin'] . "')" ;	
		$this->db->where('( ' . $where1 .' OR '. $where2 .' OR '. $where3 . ' OR ' .  $where4 .' )');

		$fData = $this->db->get()->row_array();
		return $fData['result'];
	}

	public function m_verifica_planing_medico($datos,$edit=FALSE){ 
		//var_dump($datos); exit();
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from('pa_prog_medico');
		$this->db->where('estado_prm', 1); //registrado
		$this->db->where("fecha_programada = '". $datos['fecha_programada']  . "'"); //fecha
		//mismo ambiente 
		$this->db->where("idambiente", $datos['idambiente'] ); 
		$this->db->where("idmedico <>", $datos['idmedico'] );
		if($edit){ 
			$this->db->where('idprogmedico <>',$datos['idprogmedico']); 
		}

		//mismo horario o similar
		$where1 = "( hora_inicio = '".$datos['hora_inicio']. "' AND  hora_fin = '".$datos['hora_fin'] . "' ) ";
		$where2 = "('". $datos['hora_inicio']. "' > hora_inicio AND  '".$datos['hora_inicio'] . "'  < hora_fin )" ;		
		$where3 = "('". $datos['hora_fin']. "' > hora_inicio AND  '".$datos['hora_fin'] . "'  < hora_fin )" ;
		$where4 = "(hora_inicio > '".$datos['hora_inicio'] . "' AND  hora_fin < '".$datos['hora_fin'] . "')" ;	
		$this->db->where('( ' . $where1 .' OR '. $where2 .' OR '. $where3 . ' OR ' .  $where4 .' )');

		$fData = $this->db->get()->row_array();
		return $fData['result'];
	}

	public function m_verifica_medico($datos,$edit=FALSE){
		//var_dump($datos); exit();
		$this->db->select('COUNT(*) AS result');
		$this->db->from('pa_prog_medico');
		$this->db->where('estado_prm', 1); //registrado
		$this->db->where('tipo_atencion_medica', $datos['tipo_atencion_medica']);
		$this->db->where("fecha_programada = '". $datos['fecha_programada']  . "'"); //fecha
		//mismo medico 
		$this->db->where("idmedico", $datos['idmedico'] ); 
		if($edit){
			$this->db->where('idprogmedico <>',$datos['idprogmedico']); 
		}
		
		//mismo horario o similar 
		$where1 = "( hora_inicio = '".$datos['hora_inicio']. "' AND  hora_fin = '".$datos['hora_fin'] . "' ) ";
		$where2 = "('". $datos['hora_inicio']. "' > hora_inicio AND  '".$datos['hora_inicio'] . "'  < hora_fin )" ;		
		$where3 = "('". $datos['hora_fin']. "' > hora_inicio AND  '".$datos['hora_fin'] . "'  < hora_fin )" ;
		$where4 = "(hora_inicio >= '".$datos['hora_inicio'] . "' AND  hora_fin <= '".$datos['hora_fin'] . "')" ;
		$this->db->where('( ' . $where1 .' OR '. $where2 .' OR '. $where3 . ' OR ' .  $where4 .' )');

		$fData = $this->db->get()->row_array();
		
		//print_r($fData);
		return $fData['result'];
	}

	public function m_verificar_cupos_programacion($datos){ 
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from('pa_detalle_prog_medico pmd ');	
		$this->db->where("pmd.idprogmedico", $datos['idprogmedico']); //programacion
		$this->db->where("pmd.estado_cupo", 1); //ocupado 
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_verificar_ventas($datos){ 
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from('detalle dt ');	
		$this->db->where("dt.idprogmedico_prog", $datos['idprogmedico']); //programacion
		$this->db->join('venta v','dt.idventa = v.idventa AND v.estado = 1');
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_verificar_cupos_atencion($datos){
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from('pa_detalle_prog_medico pmd, pa_prog_cita ppc, detalle d, atencion_medica am ');	
			
		$this->db->where('d.idprogcita = ppc.idprogcita AND d.paciente_atendido_det = 1');	
		$this->db->where('am.iddetalle = d.iddetalle AND estado_am = 1');	
		$this->db->where('ppc.iddetalleprogmedico = pmd.iddetalleprogmedico');
		$this->db->where("pmd.idprogmedico", $datos['idprogmedico']); //programacion
		$this->db->where("pmd.estado_cupo", 1); //ocupado 
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_anular($datos){
		$data = array( 
			'estado_prm'=> 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_prog_medico', $data );
	}

	public function m_cargar_programaciones_por_estado($datos, $paramPaginate){ 		
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados, 
			 (em.descripcion) AS empresa, em.idempresa, emp.idempleado, emp.nombres, emp.apellido_paterno, emp.apellido_materno, 
			med.idmedico, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno, 
			am.idambiente, am.numero_ambiente, am.piso, esp.idespecialidad, esp.nombre, prm.total_cupos_ocupados, prm.estado_prm
			'); 
		$this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0) as total_cupos');
		$this->db->from('pa_prog_medico prm'); 

		//medico-especialidad-empresa 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa_especialidad emesp','emme.idempresaespecialidad = emesp.idempresaespecialidad AND emesp.idespecialidad = prm.idespecialidad'); 
		$this->db->join('pa_empresa_detalle emde','emesp.idempresadetalle = emde.idempresadetalle'); 
		$this->db->join('empresa em','em.idempresa = emde.idempresatercera'); 

		$this->db->join('rh_empleado emp','prm.idresponsable = emp.idempleado'); 
		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		
		$this->db->where('am.estado_amb', 1); 
		$this->db->where('prm.fecha_programada >=', $datos['fecha_desde']); 
		$this->db->where('prm.fecha_programada <=', $datos['fecha_hasta']); 
		$this->db->where('prm.estado_prm', $datos['estado_prm']); 
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->order_by('prm.fecha_programada, am.numero_ambiente'); 

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}

		return $this->db->get()->result_array(); 
	}

	public function m_count_programaciones_por_estado($datos, $paramPaginate){
		$this->db->select('COUNT(*) AS result');

		$this->db->from('pa_prog_medico prm'); 

		//medico-especialidad-empresa 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa_especialidad emesp','emme.idempresaespecialidad = emesp.idempresaespecialidad AND emesp.idespecialidad = prm.idespecialidad'); 
		$this->db->join('pa_empresa_detalle emde','emesp.idempresadetalle = emde.idempresadetalle'); 
		$this->db->join('empresa em','em.idempresa = emde.idempresatercera'); 

		$this->db->join('rh_empleado emp','prm.idresponsable = emp.idempleado'); 
		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		
		$this->db->where('am.estado_amb', 1); 
		$this->db->where('prm.fecha_programada >=', $datos['fecha_desde']); 
		$this->db->where('prm.fecha_programada <=', $datos['fecha_hasta']); 
		$this->db->where('prm.estado_prm', $datos['estado_prm']); 
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_cargar_datos_comentarios($datos){
		$this->db->select('ce.idcontrolevento, ce.fecha_evento, ce.idresponsable, ce.comentario '); 
		$this->db->select('emp.nombres, emp.apellido_paterno, emp.apellido_materno '); 
		$this->db->from('control_evento ce'); 
		
		$this->db->join('rh_empleado emp','ce.idresponsable = emp.idempleado'); 

		$this->db->where('ce.identificador', $datos['idprogmedico']); 
		//$this->db->where('ce.idtipoevento', 2); 
		$this->db->where('ce.estado_ce', 1); 
		return $this->db->get()->result_array(); 
	}

	public function m_cancelar($datos){
		$data = array( 
			'estado_prm'=> 2,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_prog_medico', $data );

	}

	public function m_cargar_cupos_de_programacion($idProgMedico,$siAdicional){
		/* cac.idcategoriaconsul, cac.descripcion_cco, scac.idsubcategoriaconsul, scac.descripcion_scco, 
			(scac_renom.idsubcategoriaconsul) AS idsubcategoriarenom, (scac_renom.descripcion_scco) AS subcategoriarenom */ 

		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados, 
			(em.descripcion) AS empresa, em.idempresa, am.idambiente, am.numero_ambiente, am.piso, ca.idcanal, ca.descripcion_can,
			dpm.iddetalleprogmedico, dpm.hora_inicio_det, dpm.hora_fin_det, dpm.si_adicional, dpm.numero_cupo'); 
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_detalle_prog_medico dpm','prm.idprogmedico = dpm.idprogmedico');
		$this->db->join('pa_canal ca','dpm.idcanal = ca.idcanal');
		// $this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		// $this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul'); 
		// renombrado 
		// $this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		if( $siAdicional === TRUE){
			$this->db->where('dpm.si_adicional', 1); // muestra solo adicionales 
		}
		
		$this->db->where_in('dpm.estado_cupo',array(1,2)); 
		$this->db->where('am.estado_amb', 1); 
		$this->db->where('prm.idprogmedico',$idProgMedico); 
		$this->db->order_by('si_adicional','ASC');
		return $this->db->get()->result_array(); 
	}

	public function m_cargar_cupos_por_canales($idprogmedico){
		$this->db->select('cpm.idcanalprogmedico, cpm.idprogmedico, cpm.idcanal, cpm.total_cupos, cpm.cupos_disponibles, cpm.cupos_ocupados');
		$this->db->select(' (SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = cpm.idprogmedico AND idcanal = cpm.idcanal AND si_adicional = 1 AND estado_cupo = 1) as cupos_adicionales_ocupados');
		$this->db->select(' (SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = cpm.idprogmedico AND idcanal = cpm.idcanal AND si_adicional = 1 AND estado_cupo <> 0) as total_cupos_adicionales');
		$this->db->select('ca.descripcion_can');
		$this->db->from('pa_canal_prog_medico cpm'); 
		$this->db->join('pa_canal ca','cpm.idcanal = ca.idcanal AND ca.estado_can = 1');
		$this->db->where('cpm.idprogmedico', $idprogmedico);
		$this->db->order_by(' cpm.idcanal','ASC');
		return $this->db->get()->result_array();
	}

	public function m_contar_cupos_por_canales($idprogmedico){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('pa_canal_prog_medico cpm'); 
		$this->db->join('pa_canal ca','cpm.idcanal = ca.idcanal AND ca.estado_can = 1');
		$this->db->where('cpm.idprogmedico', $idprogmedico);
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_guardar_gestion_cupos_canal($datos){
		$data = array( 
			'cupos_disponibles'=> $datos['cupos_disponibles'],
			'total_cupos'=> $datos['total_cupos'],			 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idcanalprogmedico',$datos['idcanalprogmedico']); 
		return $this->db->update('pa_canal_prog_medico', $data ); 
	}

	public function m_carga_turno_ultimo_cupo($idprogmedico){
		$this->db->select('dpm.numero_cupo, dpm.iddetalleprogmedico, dpm.estado_cupo');
		$this->db->select('dpm.hora_inicio_det,dpm.hora_fin_det');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->where('dpm.si_adicional',2);
		$this->db->where('dpm.idprogmedico', $idprogmedico);
		$this->db->where('dpm.estado_cupo <> 0');
		$this->db->where('dpm.estado_cupo <> 3');
		$this->db->order_by('dpm.numero_cupo DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array(); 
	}

	public function m_listar_todos_cupos($idprogmedico, $adicionales){
		$this->db->select('iddetalleprogmedico, idprogmedico, idcanal, si_adicional, estado_cupo, numero_cupo');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->where('dpm.idprogmedico', $idprogmedico);

		if( $adicionales === TRUE){
			$this->db->where('dpm.si_adicional', 1); // muestra solo adicionales 
		}else{
			$this->db->where('dpm.si_adicional', 2); //muestra solo master
		}

		$this->db->order_by('idcanal ASC, si_adicional DESC, estado_cupo ASC, numero_cupo ASC');
		return $this->db->get()->result_array();
	}

	public function m_guardar_gestion_cupos_detalle($datos){
		$data = array( 
			'idcanal'=> $datos['idcanal'],		 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
	
		$this->db->where(" iddetalleprogmedico", $datos['iddetalleprogmedico'] ); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}

	public function m_count_cupos_canal($idprogmedico){
		$this->db->select("dpm.idcanal,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND si_adicional = 2 THEN 1 ELSE 0 END) AS total_cupos,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND dpm.estado_cupo <> 2 AND si_adicional = 2 THEN 1 ELSE 0 END) AS total_cupos_ocupados,
						   SUM(CASE WHEN dpm.estado_cupo = 2  AND si_adicional = 2 THEN 1 ELSE 0 END) AS total_cupos_disponibles,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND si_adicional = 1 THEN 1 ELSE 0 END) AS total_cupos_adi,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND dpm.estado_cupo <> 2 AND si_adicional = 1 THEN 1 ELSE 0 END) AS total_cupos_ocupados_adi,
						   SUM(CASE WHEN dpm.estado_cupo = 2  AND si_adicional = 1 THEN 1 ELSE 0 END) AS total_cupos_disponibles_adi
						   ",FALSE);
		$this->db->from('pa_detalle_prog_medico dpm');
		$this->db->where('dpm.idprogmedico', $idprogmedico);
		$this->db->group_by('dpm.idcanal');
		$this->db->order_by('dpm.idcanal ASC');
		return $this->db->get()->result_array(); 
	}

	public function m_cargar_programaciones_por_fechas($datos){
		$this->db->select("prm.idprogmedico, prm.idmedico, prm.hora_inicio, prm.hora_fin, prm.total_cupos_ocupados, prm.cupos_adicionales , prm.activo, prm.tipo_atencion_medica",FALSE); 
		$this->db->select('prm.fecha_programada, am.idambiente, am.numero_ambiente, esp.idespecialidad, esp.nombre as nombre_especialidad'); 
		$this->db->select('med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0) as total_cupos');

		$this->db->from('pa_prog_medico prm');
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul');
		//renombrado
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		$this->db->join('medico med','prm.idmedico = med.idmedico'); 

		// $this->db->where('estado_amb', 1); // 1:habilitado 
		$this->db->where('DATE(prm.fecha_programada) BETWEEN '. $this->db->escape($datos['desde']). ' AND '. $this->db->escape($datos['hasta'])); 
		
		if(!empty($datos['itemEspecialidad']) ){
			$this->db->where('esp.idespecialidad',$datos['itemEspecialidad']['id']);
		}

		if(!empty($datos['itemMedico']) && $datos['itemMedico']['idmedico'] != null){
			$this->db->where('prm.idmedico',$datos['itemMedico']['idmedico']);
		}

		if(!empty($datos['itemAmbiente']) && $datos['itemAmbiente']['id'] != 0){
			$this->db->where('cac.idcategoriaconsul', $datos['itemAmbiente']['id']); //ver solo un tipo
		}

		if(!empty($datos['tipoAtencion']) && $datos['tipoAtencion'] != 'all'){
			$this->db->where('prm.tipo_atencion_medica',$datos['tipoAtencion']);
		}

		$this->db->where('estado_amb', 1);
		//$this->db->where('estado_prm', 1); // 1:registrado 
		if(!empty($datos['itemEstado'])){
			$this->db->where('prm.estado_prm', $datos['itemEstado']['id']); //ver solo un tipo
		}		 
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // 1:habilitado 
		$this->db->order_by('am.numero_ambiente, prm.hora_inicio'); 
		return $this->db->get()->result_array(); 
	}
	// PARA ATENCION MEDICA
	public function m_cargar_programaciones_medico($datos){
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin');
		$this->db->from('pa_prog_medico prm');
		$this->db->where('estado_prm', 1);
		$this->db->where('activo', 1);
		$this->db->where('idmedico', $this->sessionHospital['idmedico']);
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('idespecialidad', $this->sessionHospital['idespecialidad']);
		$this->db->where('fecha_programada', $datos['fecha']);
		$this->db->where('tipo_atencion_medica', $datos['tipo_atencion_medica']);
		$this->db->order_by('prm.idprogmedico', 'ASC');
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_estas_programaciones_sin_detalle($arrIdProg){
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.intervalo_hora, prm.cupos_adicionales, 
			prm.tipo_atencion_medica, prm.si_renombrado_scc, prm.comentario_pmed, prm.total_cupos_ocupados, 
			(em.descripcion) AS empresa, em.idempresa, am.idambiente, am.numero_ambiente, am.piso'); 
		$this->db->select('med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('esp.nombre');
		$this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0) as total_cupos');
		
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');

		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		// renombrado 
		$this->db->where('am.estado_amb', 1); 
		$this->db->where('prm.estado_prm <>', 0); // registrado  o cancelado
		$this->db->where_in('prm.idprogmedico',$arrIdProg);  
		$this->db->order_by('prm.hora_inicio'); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_lista_pacientes_programacion_proc($datos){
		$this->db->select('v.orden_venta, v.ticket_venta, v.fecha_venta, v.fecha_atencion_v, v.idmedico,  v.idcliente'); 
		$this->db->select('c.nombres, c.apellido_paterno, c.apellido_materno'); 
 		$this->db->select('m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno');
 		$this->db->select('dt.paciente_atendido_det, dt.idproductomaster, dt.iddetalle, ncd.idnotacreditodetalle');  		
		$this->db->from('detalle dt'); 		
		$this->db->join('venta v','dt.idventa = v.idventa AND v.estado != 0'); 
		$this->db->join('medico m','m.idmedico = v.idmedico', 'left' ); 
		$this->db->join('cliente c','c.idcliente = v.idcliente', 'left');
		$this->db->join('nota_credito nc','v.idventa = nc.idventa AND nc.estado_nc = 1','left');
		$this->db->join('nota_credito_detalle ncd','dt.iddetalle = ncd.iddetalle AND nc.idnotacredito = ncd.idnotacredito AND ncd.estado_ncd = 1','left'); 
		$this->db->where('dt.idprogmedico_prog', $datos['idprogmedico']); 

		if(!empty($datos['paginate'])){
			$paramPaginate = $datos['paginate'];

			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
			if( $paramPaginate['sortName'] ){
				$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
			}
			if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}
		

		return $this->db->get()->result_array();
	}
	public function m_cargar_lista_pacientes($idprogmedico, $anulados = FALSE){
		$this->db->select('dpm.iddetalleprogmedico, dpm.idprogmedico, dpm.hora_inicio_det, dpm.hora_fin_det, dpm.numero_cupo, dpm.estado_cupo, dpm.si_adicional, dpm.idcanal'); 
		$this->db->select('pc.idprogcita, pc.fecha_reg_reserva, pc.fecha_reg_cita, pc.fecha_atencion_cita, pc.estado_cita'); 
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.telefono, c.celular, c.email, c.fecha_nacimiento, c.num_documento, c.idempresacliente_cli'); 
		$this->db->select('pm.descripcion,pm.idproductomaster, pc.motivo_cancelacion, pc.fecha_cancelacion'); 
		$this->db->select('d.iddetalle'); 
		$this->db->select('prm.estado_prm'); 
				
		$this->db->from('pa_detalle_prog_medico dpm'); 		
		$this->db->join('pa_prog_medico prm','dpm.idprogmedico = prm.idprogmedico'); 
		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico AND pc.estado_cita != 0 AND pc.estado_cita != 1', 'left'); 
		$this->db->join('cliente c','pc.idcliente = c.idcliente', 'left'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster', 'left'); 
		$this->db->join('detalle d','pc.idprogcita = d.idprogcita', 'left'); 

		$this->db->where('dpm.idprogmedico',$idprogmedico); 

		if(!$anulados){
			$this->db->where('dpm.estado_cupo <> 0');
		}else{
			$this->db->where('dpm.estado_cupo ',0);
		} 
		  
		$this->db->order_by('dpm.si_adicional DESC, dpm.numero_cupo ASC'); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_lista_pacientes_programados($idprogmedico){
		//var_dump($idprogmedico); exit();
		/*Cupos vendidos por caja*/
		$this->db->select('dpm.iddetalleprogmedico, dpm.idprogmedico, dpm.hora_inicio_det, dpm.hora_fin_det, dpm.numero_cupo, dpm.estado_cupo, dpm.si_adicional, dpm.idcanal'); 
		$this->db->select('pc.idprogcita, pc.fecha_reg_reserva, pc.fecha_reg_cita, pc.fecha_atencion_cita, pc.estado_cita'); 
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.telefono, c.celular, c.email, c.fecha_nacimiento, c.num_documento, c.idempresacliente_cli'); 
		$this->db->select('pm.descripcion,pm.idproductomaster, pc.motivo_cancelacion, pc.fecha_cancelacion, d.paciente_atendido_det'); 
		$this->db->select('d.iddetalle, ncd.idnotacreditodetalle'); 
		$this->db->select('prm.estado_prm'); 
		$this->db->select("'C' as origen_venta"); 
				
		$this->db->from('pa_detalle_prog_medico dpm'); 		
		$this->db->join('pa_prog_medico prm','dpm.idprogmedico = prm.idprogmedico'); 
		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico'); 
		$this->db->join('cliente c','pc.idcliente = c.idcliente'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster'); 
		$this->db->join('detalle d','pc.idprogcita = d.idprogcita'); 
		$this->db->join('nota_credito nc','d.idventa = nc.idventa AND nc.estado_nc = 1','left');
		$this->db->join('nota_credito_detalle ncd','d.iddetalle = ncd.iddetalle AND nc.idnotacredito = ncd.idnotacredito AND ncd.estado_ncd = 1','left');

		$this->db->where('dpm.idprogmedico',$idprogmedico); 
		$this->db->where('dpm.estado_cupo <> 0');
		$this->db->where('dpm.idcanal <> 3'); //web se carga aparte
		$this->db->where_in('pc.estado_cita', array(2,5));

		$sqlCuposMenosWeb = $this->db->get_compiled_select();

		/*Cupos vendidos por web*/
		$this->db->select('dpm.iddetalleprogmedico, dpm.idprogmedico, dpm.hora_inicio_det, dpm.hora_fin_det, dpm.numero_cupo, dpm.estado_cupo, dpm.si_adicional, dpm.idcanal'); 
		$this->db->select('pc.idprogcita, pc.fecha_reg_reserva, pc.fecha_reg_cita, pc.fecha_atencion_cita, pc.estado_cita'); 
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.telefono, c.celular, c.email, c.fecha_nacimiento, c.num_documento, c.idempresacliente_cli'); 
		$this->db->select('pm.descripcion,pm.idproductomaster, pc.motivo_cancelacion, pc.fecha_cancelacion, d.paciente_atendido_det'); 
		$this->db->select('d.iddetalle'); 
		$this->db->select("null AS idnotacreditodetalle, prm.estado_prm, 'W' as origen_venta", FALSE); 
				
		$this->db->from('pa_detalle_prog_medico dpm'); 		
		$this->db->join('pa_prog_medico prm','dpm.idprogmedico = prm.idprogmedico'); 
		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico'); 
		$this->db->join('cliente c','pc.idcliente = c.idcliente'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster'); 
		$this->db->join('ce_detalle d','pc.idprogcita = d.idprogcita'); 

		$this->db->where('dpm.idprogmedico',$idprogmedico); 
		$this->db->where('dpm.estado_cupo <> 0');
		$this->db->where('dpm.idcanal = 3'); //web se carga aparte
		$this->db->where_in('pc.estado_cita', array(2,5));
		$sqlCuposWeb = $this->db->get_compiled_select();

		$sqlMaster = 'select * from (' . $sqlCuposMenosWeb.' UNION ALL '.$sqlCuposWeb . ' ) a order by a.si_adicional DESC, a.numero_cupo ASC';
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}

	public function m_cargar_cupos_canal($idprogmedico, $idcanal){ 
		$this->db->select("CONCAT_WS(' ',cl.nombres,cl.apellido_paterno,cl.apellido_materno) AS cliente", FALSE); 
		$this->db->select('dpm.iddetalleprogmedico, dpm.idprogmedico, dpm.idcanal, dpm.si_adicional, dpm.estado_cupo, dpm.numero_cupo');
		$this->db->select('dpm.hora_inicio_det, dpm.hora_fin_det, dpm.intervalo_hora_det');
		$this->db->select('ve.ticket_venta AS ticket');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		//$this->db->join('pa_prog_medico prm','prm.idprogmedico = dpm.idprogmedico'); 
		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico AND estado_cita IN (2,5)','left'); 
		$this->db->join('detalle dv','dv.idprogcita = pc.idprogcita','left');
		$this->db->join('venta ve','dv.idventa = ve.idventa','left');
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente','left'); 
		$this->db->where('dpm.idprogmedico', $idprogmedico); 
		$this->db->where('dpm.idcanal', $idcanal); 
		$this->db->where('dpm.estado_cupo <> 0'); 

		$this->db->order_by('si_adicional DESC, numero_cupo ASC');
		return $this->db->get()->result_array();
	}

	public function m_cambiar_estado_detalle_de_programacion($datos) {
		$data = array( 
			'estado_cupo'=> $datos['estado_cupo'] 
		);
		$this->db->where('iddetalleprogmedico',$datos['iddetalleprogmedico']); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}
	
	public function m_cambiar_cupos_canales($datos) {
		return $this->db->simple_query("update pa_canal_prog_medico 
							SET cupos_ocupados = cupos_ocupados + 1, 
								cupos_disponibles = cupos_disponibles -1
							WHERE idprogmedico = ".$datos['idprogmedico'] . " AND idcanal = " . $datos['idcanal']);
	}

	public function m_cambiar_cupos_programacion($datos) {
		return $this->db->simple_query("update pa_prog_medico 
							SET total_cupos_ocupados = total_cupos_ocupados + 1 													
							WHERE idprogmedico = ".intval($datos['idprogmedico']));
	}

	public function m_ajustar_canal($datos) {
		return $this->db->simple_query("update pa_canal_prog_medico 
							SET total_cupos = total_cupos - 1,
							cupos_disponibles = cupos_disponibles - 1
							WHERE idprogmedico = ".$datos['idprogmedico'] . " AND idcanal = " . $datos['idcanal']);
	}

	public function m_agregar_uno_canal_web($datos) {
		return $this->db->simple_query("update pa_canal_prog_medico 
							SET total_cupos = total_cupos + 1,
							cupos_disponibles = cupos_disponibles +1
							WHERE idprogmedico = ".$datos['idprogmedico'] . " AND idcanal = 3");
	}

	public function m_cambiar_canal_cupo($datos) {
		$data = array( 
			'idcanal'=> $datos['idcanal'] 
		);
		$this->db->where('iddetalleprogmedico',$datos['iddetalleprogmedico']); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}

	public function m_cargar_programaciones_generar_cupo_informe($datos){ 
		$this->db->select("prm.idprogmedico, prm.idmedico, prm.hora_inicio, prm.hora_fin, prm.total_cupos_ocupados, prm.cupos_adicionales ",FALSE); 
		$this->db->select('prm.fecha_programada, prm.intervalo_hora, am.idambiente, am.numero_ambiente, esp.idespecialidad, esp.nombre as nombre_especialidad, prm.tipo_atencion_medica'); 
		$this->db->select('med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('( SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				LEFT JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
			) as total_cupos');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
				AND sc_dpm.estado_cupo <> 3 
			) as total_cupos_no_cancelados');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 1 
				AND sc_dpm.estado_cupo = 1 
				AND sc_pc.estado_cita <> 0 
			) as total_adi_vendidos');
		$this->db->select('em.idempresa, em.descripcion AS empresa'); 
		$this->db->from('pa_prog_medico prm');
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul');
		//renombrado
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		$this->db->join('medico med','prm.idmedico = med.idmedico');

		//medico-especialidad-empresa 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa_especialidad emesp','emme.idempresaespecialidad = emesp.idempresaespecialidad AND emesp.idespecialidad = prm.idespecialidad'); 
		$this->db->join('pa_empresa_detalle emde','emesp.idempresadetalle = emde.idempresadetalle'); 
		$this->db->join('empresa em','em.idempresa = emde.idempresatercera'); 	
		$this->db->where('DATE(prm.fecha_programada) = ', $this->db->escape($datos['hasta']));
		if(!empty($datos['tipoAtencion']['id'])){
			$this->db->where('prm.tipo_atencion_medica', $datos['tipoAtencion']['id']);			
		}	
		if(!empty($datos['especialidad']['id'])){
			$this->db->where('prm.idespecialidad', $datos['especialidad']['id']);			
		} 
		if(!empty($datos['medico']['id'])){
			$this->db->where('prm.idmedico', $datos['medico']['id']);			
		}		
		$this->db->where('estado_amb', 1); 
		$this->db->where('estado_prm', 1); // 1:registrado 
		$this->db->where('prm.activo', 1);
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // 1:habilitado 
		$this->db->order_by('am.numero_ambiente, prm.hora_inicio'); 
		return $this->db->get()->result_array(); 
	}

	public function m_cargar_programaciones_generar_cupo($datos){
		$this->db->select("prm.idprogmedico, prm.idmedico, prm.hora_inicio, prm.hora_fin, prm.total_cupos_ocupados, prm.cupos_adicionales ",FALSE); 
		$this->db->select('prm.fecha_programada, prm.intervalo_hora, am.idambiente, am.numero_ambiente, esp.idespecialidad, esp.nombre as nombre_especialidad'); 
		$this->db->select('med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno'); 
		$this->db->select('( SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				LEFT JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
			) as total_cupos');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico AND sc_pc.estado_cita <> 0 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 2 
				AND sc_dpm.estado_cupo <> 0 
				AND sc_dpm.estado_cupo <> 3 
			) as total_cupos_no_cancelados');
		$this->db->select('(SELECT COUNT(*) 
				FROM pa_detalle_prog_medico sc_dpm 
				INNER JOIN pa_prog_cita sc_pc ON sc_dpm.iddetalleprogmedico = sc_pc.iddetalleprogmedico 
				WHERE sc_dpm.idprogmedico = prm.idprogmedico 
				AND sc_dpm.si_adicional = 1 
				AND sc_dpm.estado_cupo = 1 
				AND sc_pc.estado_cita <> 0 
			) as total_adi_vendidos');
		// $this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0) as total_cupos');
		// $this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 2 AND estado_cupo <> 0 AND estado_cupo <> 3) as total_cupos_no_cancelados');
		// $this->db->select('(SELECT count(*) FROM pa_detalle_prog_medico WHERE idprogmedico = prm.idprogmedico AND si_adicional = 1 AND estado_cupo = 1) as total_adi_vendidos');
		$this->db->select('em.idempresa, em.descripcion AS empresa');
		//$this->db->select(" ''  AS empresa ");

		$this->db->from('pa_prog_medico prm');
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('pa_categoria_consul cac','am.idcategoriaconsul = cac.idcategoriaconsul');
		$this->db->join('pa_sub_categoria_consul scac','prm.idsubcategoriaconsul = scac.idsubcategoriaconsul');
		//renombrado
		$this->db->join('pa_sub_categoria_consul scac_renom','prm.idsubcategoriaconsulrenom = scac_renom.idsubcategoriaconsul','left'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		$this->db->join('medico med','prm.idmedico = med.idmedico');

		//medico-especialidad-empresa 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa_especialidad emesp','emme.idempresaespecialidad = emesp.idempresaespecialidad AND emesp.idespecialidad = prm.idespecialidad'); 
		$this->db->join('pa_empresa_detalle emde','emesp.idempresadetalle = emde.idempresadetalle'); 
		$this->db->join('empresa em','em.idempresa = emde.idempresatercera'); 
		$this->db->where('prm.tipo_atencion_medica', 'CM');
		$this->db->where('DATE(prm.fecha_programada) = ', $this->db->escape($datos['hasta']));	
		if(!empty($datos['especialidad']['id'])){
			$this->db->where('prm.idespecialidad', $datos['especialidad']['id']);			
		} 
		if(!empty($datos['medico']['id'])){
			$this->db->where('prm.idmedico', $datos['medico']['id']);			
		}		
		$this->db->where('estado_amb', 1); 
		$this->db->where('estado_prm', 1); // 1:registrado 
		$this->db->where('prm.activo', 1);
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // 1:habilitado 
		$this->db->order_by('am.numero_ambiente, prm.hora_inicio'); 
		return $this->db->get()->result_array(); 
	}

	public function m_buscar_fecha_programacion($datos){
		
		$this->db->select('MIN(prm.fecha_programada)'); 
		$this->db->from('pa_prog_medico prm');
		if(!empty($datos['especialidad']['id'])){
			$this->db->where('prm.idespecialidad', intval($datos['especialidad']['id']));
		}
		if(!empty($datos['medico']['id'])){
			$this->db->where('prm.idmedico', intval($datos['medico']['id']));
		}
		if(!empty($datos['tipo_atencion_medica'])){
			$this->db->where('prm.tipo_atencion_medica', $datos['tipo_atencion_medica']);
		}
		$this->db->where('prm.activo', 1);
		$this->db->where('estado_prm', 1);
		if($datos['next']){
			$this->db->where("prm.fecha_programada >", $datos['desde']);
		}else if($datos['origen'] == 'calendar'){
			$this->db->where("prm.fecha_programada = ", $datos['desde']);
		}else if(!$datos['origen']){
			$this->db->where("prm.fecha_programada >= ", $datos['desde']); // POR DEFECTO
		}	
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$fData = $this->db->get()->row_array();

		return 	$fData['min'];
	}

	public function m_buscar_fecha_programacion_prev($datos){

		$this->db->select('MAX(prm.fecha_programada)');
		$this->db->from('pa_prog_medico prm');
		if(!empty($datos['especialidad']['id'])){
			$this->db->where('prm.idespecialidad', intval($datos['especialidad']['id']));
		}
		if(!empty($datos['medico']['id'])){
			$this->db->where('prm.idmedico', intval($datos['medico']['id']));
		}
		if(!empty($datos['tipo_atencion_medica'])){
			$this->db->where('prm.tipo_atencion_medica', $datos['tipo_atencion_medica']);
		}
		$this->db->where('prm.activo', 1);
		$this->db->where("prm.fecha_programada <", $datos['desde']);		
		$this->db->where("prm.fecha_programada >=", date('d-m-Y'));		
		$this->db->where('estado_prm', 1);
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$fData = $this->db->get()->row_array();
		return 	$fData['max'];
	}

	public function m_cambiar_estado_todo_detalle_prog($datos){
		$data = array( 
			'estado_cupo'=> $datos['estado_cupo'], 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}

	public function m_ultimo_cupo_master($datos){
		$this->db->select('MAX(dpm.iddetalleprogmedico) iddetalleprogmedico, MAX(dpm.numero_cupo) as numero_cupo'); 
		$this->db->from('pa_detalle_prog_medico dpm');
		$this->db->where("dpm.idprogmedico", $datos['idprogmedico']);			
		$this->db->where('dpm.si_adicional', 2);
		//$this->db->group_by('dpm.iddetalleprogmedico');
		$fData = $this->db->get()->row_array();
		return 	$fData;
	}

	public function m_ultimo_cupo_sin_cancelar($datos, $adicional){
		$this->db->select('MAX(dpm.iddetalleprogmedico) as iddetalleprogmedico, MAX(dpm.numero_cupo) as numero_cupo'); 
		$this->db->from('pa_detalle_prog_medico dpm');
		$this->db->where("dpm.idprogmedico", $datos['idprogmedico']);			
		$this->db->where('dpm.estado_cupo IN (1,2,4)');
		$this->db->where('dpm.si_adicional',$adicional);
		$fData = $this->db->get()->row_array();
		return 	$fData;
	}

	public function m_update_prog_hora_fin_inicial($datos){
		$data = array( 
			'hora_fin_inicial' =>  $datos['hora_fin_det'],
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_prog_medico', $data ); 
	}

	public function m_update_prog_hora_fin($datos){
		$data = array( 
			'hora_fin'=> $datos['hora_inicio_det'], 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idprogmedico',$datos['idprogmedico']); 
		return $this->db->update('pa_prog_medico', $data ); 
	}

	public function m_consulta_cupo($iddetalleprogmedico){
		$this->db->select('*'); 
		$this->db->from("pa_detalle_prog_medico dpm");		
		$this->db->where("dpm.iddetalleprogmedico", intval($iddetalleprogmedico)); //cupo
		return $this->db->get()->row_array();
	}

	public function m_revertir_cupos_canales($datos) {
		return $this->db->simple_query("update pa_canal_prog_medico 
							SET cupos_ocupados = cupos_ocupados -1, 
								cupos_disponibles = cupos_disponibles +1
							WHERE idprogmedico = ".$datos['idprogmedico'] . " AND idcanal = " . $datos['idcanal']);
	}

	public function m_revertir_cupos_programacion($datos) {
		return $this->db->simple_query("update pa_prog_medico 
							SET total_cupos_ocupados = total_cupos_ocupados -1 													
							WHERE idprogmedico = ".intval($datos['idprogmedico']));
	}

	public function m_contar_cupos_cancelables($idprogmedico){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->where('dpm.estado_cupo <> 3');
		$this->db->where('dpm.idprogmedico', $idprogmedico);
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_update_encabezado_prog($datos){
		return $this->db->simple_query("update pa_prog_medico 
							SET hora_fin = hora_fin_inicial, 
								estado_prm = 2
							WHERE idprogmedico = ".$datos['idprogmedico']);
	}

	public function m_count_cupos_adic_ocupados($idprogmedico){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->where('dpm.estado_cupo = 1');
		$this->db->where('dpm.si_adicional = 1');
		$this->db->where('dpm.idprogmedico', $idprogmedico);
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_count_todos_cupos_ocupados($idprogmedico){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->where('dpm.estado_cupo = 1');
		$this->db->where('dpm.idprogmedico', $idprogmedico);
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_update_numero_cupo($datos){
		$data = array( 
			'numero_cupo'=> $datos['numero_cupo'],		 
			'updatedAt' => date('Y-m-d H:i:s') 
		);
	
		$this->db->where(" iddetalleprogmedico", $datos['iddetalleprogmedico'] ); 
		return $this->db->update('pa_detalle_prog_medico', $data ); 
	}

	public function m_cargar_programaciones_reporte($datos){
		$this->db->select('prm.idprogmedico, prm.fecha_programada, prm.hora_inicio, prm.hora_fin, prm.cupos_adicionales');
		$this->db->select('prm.estado_prm, prm.activo, prm.tipo_atencion_medica');
		$this->db->select('(em.descripcion) AS empresa, em.idempresa, am.idambiente, am.numero_ambiente, am.piso'); 
		$this->db->select("(med.med_apellido_paterno || ' ' ||  med.med_apellido_materno || ' ' || med.med_nombres) AS medico");
		$this->db->select('esp.nombre AS especialidad');

		//procedimiento		
		$this->db->select("pac.hora_inicio as hora_inicio_proc, pac.hora_fin as hora_fin_proc, pac.activo activo_proc,pac.estado_prm estado_proc");		
		$this->db->select("(em2.descripcion) AS empresa_proc, em2.idempresa as idempresa_proc");		
		$this->db->select("(med2.med_apellido_paterno || ' ' ||  med2.med_apellido_materno || ' ' || med2.med_nombres) AS medico_proc");		
		$this->db->select("pac.idprogmedico AS idprogmedico_proc");		
		
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');

		//para procedimientos
		$this->db->join('pa_prog_medico pac',"pac.idespecialidad = prm.idespecialidad 
											  AND pac.idambiente = prm.idambiente 
											  AND pac.fecha_programada = prm.fecha_programada 
											  AND pac.hora_inicio = prm.hora_inicio
											  AND pac.hora_fin = prm.hora_fin
											  AND pac.tipo_atencion_medica = 'P'",'left', FALSE);
		$this->db->join('empresa_medico emme2','pac.idempresamedico = emme2.idempresamedico','left'); 
		$this->db->join('empresa em2','emme2.idempresa = em2.idempresa','left'); 
		$this->db->join('medico med2','pac.idmedico = med2.idmedico','left'); 
		 
		$this->db->where('DATE(prm.fecha_programada) BETWEEN '. $this->db->escape($datos['fecha_desde']). ' AND '. $this->db->escape($datos['fecha_hasta'])); 
		$this->db->where('am.idsede', $datos['sede']['id']); 
		$this->db->where('prm.tipo_atencion_medica','CM'); 
		$progCitas = $this->db->get_compiled_select();

		//procedimientos sin consultas
		$this->db->select('pac.idprogmedico, pac.fecha_programada, null as hora_inicio, null as hora_fin, pac.cupos_adicionales',FALSE);
		$this->db->select('null as estado_prm, null as activo, pac.tipo_atencion_medica',FALSE);
		$this->db->select('null empresa, null as idempresa, am.idambiente, am.numero_ambiente, am.piso', FALSE); 
		$this->db->select("'' AS medico", FALSE);
		$this->db->select('esp.nombre AS especialidad',FALSE);	

		$this->db->select("pac.hora_inicio as hora_inicio_proc, pac.hora_fin as hora_fin_proc, pac.activo activo_proc,pac.estado_prm estado_proc");		
		$this->db->select("(em.descripcion) AS empresa_proc, em.idempresa as idempresa_proc");		
		$this->db->select("(med.med_apellido_paterno || ' ' ||  med.med_apellido_materno || ' ' || med.med_nombres) AS medico_proc");	
		$this->db->select("pac.idprogmedico AS idprogmedico_proc");		
		
		$this->db->from('pa_prog_medico pac'); 
		$this->db->join('empresa_medico emme','pac.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','pac.idambiente = am.idambiente');
		$this->db->join('medico med','pac.idmedico = med.idmedico'); 
		$this->db->join('especialidad esp','pac.idespecialidad = esp.idespecialidad');

		$this->db->where('DATE(pac.fecha_programada) BETWEEN '. $this->db->escape($datos['fecha_desde']). ' AND '. $this->db->escape($datos['fecha_hasta'])); 
		$this->db->where('am.idsede', $datos['sede']['id']); 
		$this->db->where('pac.tipo_atencion_medica','P');
		$this->db->where("(select COUNT(*) 	FROM pa_prog_medico prm 
										   	where pac.idespecialidad = prm.idespecialidad 
											  AND pac.idambiente = prm.idambiente 
											  AND pac.fecha_programada = prm.fecha_programada 
											  AND pac.hora_inicio = prm.hora_inicio
											  AND pac.hora_fin = prm.hora_fin 
											  AND prm.tipo_atencion_medica = 'CM'
											) < 1"); 
		$progProce = $this->db->get_compiled_select();

		$campos = 'x.idprogmedico, x.fecha_programada, x.hora_inicio, x.hora_fin, x.cupos_adicionales, 
				   x.estado_prm, x.activo, x.tipo_atencion_medica, x.empresa, x.idempresa, x.idambiente, x.numero_ambiente, x.piso,
				   x.medico, x.especialidad, x,hora_inicio_proc, x.hora_fin_proc, x.activo_proc,x.estado_proc,
				   x.empresa_proc, x.idempresa_proc,x.medico_proc, x.idprogmedico_proc';
		$query = 'select '. $campos .' 
				  FROM ( ' . $progCitas.' UNION ALL '.$progProce . ' ) x 
				  ORDER BY  x.fecha_programada ASC, x.hora_inicio ASC';

		$result = $this->db->query($query);
		return $result->result_array();
	}

	public function m_count_cupos_programacion($idprogmedico){
		$this->db->select('SUM(CASE WHEN dpm.estado_cupo <> 0 THEN 1 ELSE 0 END) AS total_cupos,
						   SUM(CASE WHEN dpm.estado_cupo = 2  THEN 1 ELSE 0 END) AS disponibles,
						   SUM(CASE WHEN dpm.estado_cupo = 1  OR dpm.estado_cupo = 4   THEN 1 ELSE 0 END) AS no_disponibles,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND dpm.idcanal = 3	   THEN 1 ELSE 0 END) AS total_cupos_web,
						   SUM(CASE WHEN dpm.estado_cupo = 2  AND dpm.idcanal = 3	   THEN 1 ELSE 0 END) AS disponibles_web,
						   SUM(CASE WHEN (dpm.estado_cupo = 1  OR dpm.estado_cupo = 4) AND dpm.idcanal = 3 THEN 1 ELSE 0 END) AS no_disponibles_web,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND dpm.si_adicional = 1 AND dpm.idcanal = 3 THEN 1 ELSE 0 END) AS adicionales_web');
		$this->db->from('pa_detalle_prog_medico dpm'); 
		$this->db->join('pa_prog_cita ppc', 'ppc.iddetalleprogmedico = dpm.iddetalleprogmedico','left'); 
		$this->db->where('dpm.idprogmedico',$idprogmedico); 
		return $this->db->get()->row_array();
	}

	public function m_count_atencion_proc_programacion($idprogmedico){
		$this->db->select('SUM(CASE WHEN v.estado = 1 THEN 1 ELSE 0 END) AS total_vendido,
						   SUM(CASE WHEN v.estado = 1 AND v.paciente_atendido_v = 1 AND d.paciente_atendido_det = 1  THEN 1 ELSE 0 END) AS total_atendido'
						,FALSE);
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('detalle d','prm.idprogmedico = d.idprogmedico_prog'); 
		$this->db->join('venta v','d.idventa = v.idventa'); 
		$this->db->join('atencion_medica at','d.iddetalle = at.iddetalle'); 
		$this->db->where('prm.idprogmedico',$idprogmedico); 
		return $this->db->get()->row_array();
	}

	public function m_cargar_programaciones_proc_fecha($datos){		 
		$this->db->select('prm.idprogmedico, prm.fecha_programada, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno, am.numero_ambiente, prm.hora_inicio, prm.hora_fin');
		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad'); 
		$this->db->join('medico med','med.idmedico = prm.idmedico');
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente'); 
		$this->db->where('prm.tipo_atencion_medica', 'P'); 
		$this->db->where('prm.fecha_programada = ', $datos['hasta']);	
		$this->db->where('prm.idespecialidad', $datos['especialidad']['id']);
		$this->db->where('prm.activo', 1);	
		$this->db->where('prm.estado_prm', 1); // ANULADO 			
		$this->db->where('prm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		return $this->db->get()->result_array();
	}

	// public function m_count_pacientes_programacion(){
	// 	$this->db->select('prm.idprogmedico');
	// 	$this->db->from('pa_prog_medico prm');
	// 	$this->db->where('estado_prm', 1);
	// 	$this->db->where('activo', 1);
	// 	$this->db->where('idmedico', $this->sessionHospital['idmedico']);
	// 	$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
	// 	$this->db->where('idespecialidad', $this->sessionHospital['idespecialidad']);
	// 	$this->db->where('tipo_atencion_medica', 'P');
	// 	$this->db->where('fecha_programada', date('Y-m-d'));
	// 	$this->db->limit(1);

	// 	$idprogmedico_proc = $this->db->get()->row_array();
	// 	$this->db->reset_query();

	// 	$this->db->select('prm.idprogmedico');
	// 	$this->db->from('pa_prog_medico prm');
	// 	$this->db->where('estado_prm', 1);
	// 	$this->db->where('activo', 1);
	// 	$this->db->where('idmedico', $this->sessionHospital['idmedico']);
	// 	$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
	// 	$this->db->where('idespecialidad', $this->sessionHospital['idespecialidad']);
	// 	$this->db->where('tipo_atencion_medica', 'CM');
	// 	$this->db->where('fecha_programada', date('Y-m-d'));
		
	// 	$idprogmedico_consult = $this->db->get()->row_array();
	// 	$this->db->reset_query();

	// 	if(!empty($idprogmedico_proc)){
	// 		$this->db->select('COUNT(*) AS proc_atendidos');   		
	// 		$this->db->from('detalle dt'); 		
	// 		$this->db->join('venta v','dt.idventa = v.idventa AND v.estado != 0');
	// 		$this->db->where('dt.idprogmedico_prog', $idprogmedico_proc['idprogmedico']); 
	// 		$this->db->where('v.paciente_atendido_v', 1);

	// 		$result[0] = $this->db->get()->row_array();
	// 		$this->db->reset_query();

	// 		$this->db->select('COUNT(*) AS proc_no_atendidos');   		
	// 		$this->db->from('detalle dt'); 		
	// 		$this->db->join('venta v','dt.idventa = v.idventa AND v.estado != 0');
	// 		$this->db->where('dt.idprogmedico_prog', $idprogmedico_proc['idprogmedico']); 
	// 		$this->db->where('v.paciente_atendido_v', 2);

	// 		$result[1] = $this->db->get()->row_array();
	// 		$this->db->reset_query();
	// 	}else{
	// 		$result[0] = ['proc_atendidos'=> 0];
	// 		$result[1] = ['proc_no_atendidos'=> 0];
	// 	}
		
	// 	if(!empty($idprogmedico_consult)){

	// 		$this->db->select('COUNT(*) AS consult_atendidos');
	// 		$this->db->from('pa_detalle_prog_medico dpm'); 		
	// 		$this->db->join('pa_prog_medico prm','dpm.idprogmedico = prm.idprogmedico'); 
	// 		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico');   
	// 		$this->db->where('dpm.idprogmedico', $idprogmedico_consult['idprogmedico']); 
	// 		$this->db->where('dpm.estado_cupo <> 0');
	// 		$this->db->where('dpm.idcanal <> 3'); //web se carga aparte
	// 		$this->db->where('pc.estado_cita', 5);

	// 		$result[2] = $this->db->get()->row_array();
	// 		$this->db->reset_query();

	// 		$this->db->select('COUNT(*) AS consult_no_atendidos'); 		
	// 		$this->db->from('pa_detalle_prog_medico dpm'); 		
	// 		$this->db->join('pa_prog_medico prm','dpm.idprogmedico = prm.idprogmedico'); 
	// 		$this->db->join('pa_prog_cita pc','dpm.iddetalleprogmedico = pc.iddetalleprogmedico');
	// 		$this->db->where('dpm.idprogmedico', $idprogmedico_consult['idprogmedico']); 
	// 		$this->db->where('dpm.estado_cupo <> 0');
	// 		$this->db->where('dpm.idcanal <> 3'); //web se carga aparte
	// 		$this->db->where('pc.estado_cita', 2);

	// 		$result[3] = $this->db->get()->row_array();
	// 		$this->db->reset_query();
	// 	}else{
	// 		$result[2] = ['consult_atendidos'=> 0];
	// 		$result[3] = ['consult_no_atendidos'=> 0];
	// 	}
	// 	/*var_dump($idprogmedico_consult['idprogmedico']); 
	// 	var_dump($idprogmedico_proc['idprogmedico']);
	// 	var_dump($result);
	// 	exit();*/
	// 	return $result;
	// }
	public function m_cargar_programaciones_cumplimiento_reporte($datos){
		$this->db->select("prm.fecha_programada, prm.idsedeempresaadmin",FALSE);
		$this->db->select("SUM(CASE WHEN dpm.estado_cupo <> 0 AND prm.hora_inicio < '12:00:00'	THEN 1 ELSE 0 END) AS total_cupos_ma,
						   SUM(CASE WHEN dpm.estado_cupo = 2  AND prm.hora_inicio < '12:00:00' 	THEN 1 ELSE 0 END) AS disponibles_ma,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND ppc.estado_cita = 5 AND prm.hora_inicio < '12:00:00' THEN 1 ELSE 0 END) AS atendidos_ma,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND prm.hora_inicio >= '12:00:00'	THEN 1 ELSE 0 END) AS total_cupos_ta,
						   SUM(CASE WHEN dpm.estado_cupo = 2  AND prm.hora_inicio >= '12:00:00' 	THEN 1 ELSE 0 END) AS disponibles_ta,
						   SUM(CASE WHEN dpm.estado_cupo <> 0 AND ppc.estado_cita = 5 AND prm.hora_inicio >= '12:00:00' THEN 1 ELSE 0 END) AS atendidos_ta,
						   ",FALSE);

		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('pa_detalle_prog_medico dpm','prm.idprogmedico = dpm.idprogmedico'); 
		//$this->db->join('pa_sede_especialidad sesp','sesp.idespecialidad = prm.idespecialidad AND sesp.idsede = prm.idsede'); 
		$this->db->join('pa_prog_cita ppc','ppc.iddetalleprogmedico = dpm.iddetalleprogmedico', 'left'); 
		
		$this->db->where('DATE(prm.fecha_programada) BETWEEN '. $this->db->escape($datos['fecha_desde']). ' AND '. $this->db->escape($datos['fecha_hasta'])); 
		$this->db->where('prm.idsede', $datos['sede']['id']);

		if($datos['especialidad']['id'] != "ALL"){
			$this->db->where('prm.idespecialidad', $datos['especialidad']['id']);
		} 

		if($datos['medico']['idmedico'] != "ALL"){
			$this->db->where('prm.idmedico', $datos['medico']['idmedico']);
		} 
		 
		$this->db->where('prm.tipo_atencion_medica','CM'); 
		$this->db->where('prm.estado_prm',1); 
		$this->db->where('prm.activo',1); 
		//$this->db->where('sesp.tiene_venta_prog_cita',1); 
		$this->db->group_by('prm.fecha_programada, prm.idsedeempresaadmin'); 
		$this->db->order_by('prm.fecha_programada ASC'); 

		return $this->db->get()->result_array();
	}

	public function m_cargar_detallado_ventas_web($datos){		
		$this->db->select('prm.idprogmedico, prm.fecha_programada, ppc.estado_cita',FALSE);
		$this->db->select('esp.nombre AS especialidad',FALSE);
		$this->db->select('(em.descripcion) AS empresa, em.idempresa, am.idambiente, am.numero_ambiente, am.piso',FALSE); 
		$this->db->select("(med.med_apellido_paterno || ' ' ||  med.med_apellido_materno || ' ' || med.med_nombres) AS medico",FALSE);
		$this->db->select("(c.apellido_paterno || ' ' ||  c.apellido_materno || ' ' || c.nombres) AS paciente",FALSE);
		$this->db->select("(u.apellido_paterno || ' ' ||  u.apellido_materno || ' ' || u.nombres) AS cliente",FALSE);
		$this->db->select("c.num_documento, c.sexo, u.si_registro_web, u.email,u.celular, u.telefono, ",FALSE);
		$this->db->select("d.precio_unitario :: NUMERIC AS precio, v.orden_venta, v.fecha_venta",FALSE);
		$this->db->select("dpm.hora_inicio_det, dpm.hora_fin_det, uwc.idparentesco, cp.descripcion as tipo_familiar",FALSE);	

		$this->db->from('pa_prog_medico prm'); 
		$this->db->join('empresa_medico emme','prm.idempresamedico = emme.idempresamedico'); 
		$this->db->join('empresa em','emme.idempresa = em.idempresa'); 
		$this->db->join('pa_ambiente am','prm.idambiente = am.idambiente');
		$this->db->join('medico med','prm.idmedico = med.idmedico'); 
		$this->db->join('especialidad esp','prm.idespecialidad = esp.idespecialidad');
		$this->db->join('pa_detalle_prog_medico dpm','prm.idprogmedico = dpm.idprogmedico AND dpm.idcanal = 3'); 
		$this->db->join('pa_prog_cita ppc','dpm.iddetalleprogmedico = ppc.iddetalleprogmedico'); 

		$this->db->join('ce_usuario_web_cita uwc','uwc.idprogcita = ppc.idprogcita'); 
		$this->db->join('ce_usuario_web_pariente uwp','uwp.idusuariowebpariente = uwc.idparentesco','left'); 
		$this->db->join('ce_parentesco cp','uwp.idparentesco = cp.idparentesco','left');

		$this->db->join('ce_detalle d','d.idprogcita = ppc.idprogcita'); 
		$this->db->join('ce_venta v','v.idventa = d.idventa'); 
		$this->db->join('cliente c','ppc.idcliente = c.idcliente'); //paciente de la cita
		$this->db->join('cliente u','v.idcliente = u.idcliente'); //usuario de compra

		$this->db->where('DATE(v.fecha_venta) BETWEEN '. $this->db->escape($datos['fecha_desde']). ' AND '. $this->db->escape($datos['fecha_hasta'])); 
		$this->db->where('prm.idsede', $datos['sede']['id']); 
		$this->db->where('prm.tipo_atencion_medica','CM'); 
		$this->db->where('prm.estado_prm',1); 
		$this->db->where('prm.activo',1); 
		$this->db->order_by('v.fecha_venta ASC, v.orden_venta ASC'); 

		return $this->db->get()->result_array();
	}

	public function m_update_cupos(){
		$formato = 'HH24:MI:SS';
		$hora = '00:05:00';
		$query = 'UPDATE pa_detalle_prog_medico
					SET estado_cupo = 2,
						"updatedAt" = \'' . date('Y-m-d H:i:s') . '\'
					WHERE iddetalleprogmedico IN (
							SELECT
								dpm.iddetalleprogmedico
							FROM
								pa_detalle_prog_medico dpm
							WHERE
								 dpm.estado_cupo = 5
							AND to_char(
								(NOW() - dpm."updatedAt"),
								 \' '.$formato.'\' 
							) >= \' '.$hora.'\' 
						)';

		return $this->db->simple_query($query); 
	}
}