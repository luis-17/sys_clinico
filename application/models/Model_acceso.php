<?php
class Model_acceso extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	// ACCESO AL SISTEMA
	public function m_logging_user($data){ 
		$this->db->select(' (SELECT key_group FROM "group" g INNER JOIN users_groups ug ON ug.idgroup = "g"."idgroup" AND ug.idusers = u.idusers WHERE estado_g = 1 LIMIT 1) AS key_grupo',FALSE);
		$this->db->select('COUNT(*) AS logged, u.idusers AS id',FALSE);
		$this->db->from('users u');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers');
		$this->db->join('sede s','ups.idsede = s.idsede');
		$this->db->where('username', $data['usuario']);
		$this->db->where('password', do_hash($data['clave'] , 'md5'));
		$this->db->where('estado_usuario <>', '0');
		$this->db->where('estado_se <>', '0');
		$this->db->where('estado_ups <>', '0');
		$this->db->group_by('u.idusers');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// validar cuantas sedes tiene un usuario 
	public function m_validar_sedes_usuario($userId)
	{
		$this->db->select('COUNT(*) AS sedes, MIN(s.idsede) AS idsede',FALSE);
		$this->db->from('users u');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers');
		$this->db->join('sede s','ups.idsede = s.idsede');
		$this->db->where('u.idusers', $userId);
		$this->db->where('estado_usuario <>', '0');
		$this->db->where('estado_se <>', '0');
		$this->db->where('estado_ups <>', '0');
		$this->db->group_by('u.idusers');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// validar cuantas empresa-sede tiene un usuario salud 
	public function m_validar_empresa_sede_usuario_salud($userId)
	{
		$this->db->select('COUNT(*) AS sedes, MIN(s.idsede) AS idsede',FALSE);
		$this->db->from('users u');
		$this->db->join('medico med','u.idusers = med.iduser');
		$this->db->join('empresa_medico eme','med.idmedico = eme.idmedico');
		$this->db->join('empresa_especialidad ee','eme.idempresaespecialidad = ee.idempresaespecialidad 
			AND eme.idsede = ee.idsede AND eme.idempresa = ee.idempresa AND eme.idespecialidad = ee.idespecialidad'); 
		$this->db->join('empresa e','ee.idempresa = e.idempresa AND ee.idsede = e.idsede'); 
		$this->db->join('sede s','e.idsede = s.idsede');
		$this->db->where('u.idusers', $userId);
		$this->db->where('estado_usuario <>', '0');
		$this->db->where('estado_se <>', '0');
		$this->db->group_by('u.idusers');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// si hay varias sedes mostrarlas en un combo 
	public function m_listar_sedes_usuario($userId)
	{
		$this->db->select('s.idsede AS id, descripcion');
		$this->db->from('users u');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers');
		$this->db->join('sede s','ups.idsede = s.idsede');
		$this->db->where('u.idusers', $userId);
		$this->db->where('estado_usuario <>', '0');
		$this->db->where('estado_se <>', '0');
		$this->db->where('estado_ups <>', '0');
		return $this->db->get()->result_array();
	}

	/* 
		AL LOGEARTE COMO USUARIO SISTEMAS, CAJA, GERENCIA, ADMINISTRACIÓN 
		LA VARIABLE SESSION "idsedeempresaadmin" sirve para registrar una venta, y anexarla a una SEDE-EMPRESA 
		LA VARIABLE SESSION "idempresamedico" NO SIRVE PARA NADA, NI EXISTE 
	*/
	public function m_listar_perfil_usuario($userId, $sedeId = FALSE ) 
	{
		$this->db->select('u.idusers, ip_address, username, email, 
			s.idsede, s.descripcion AS sede, "g"."name" AS grupo, "g"."idgroup", g.vista_sede_empresa, 
			ea.idempresaadmin, razon_social, nombre_legal, ea.ruc, ea.ruc AS ruc_empresa_admin, nombre_logo, nombre_foto, sea.idsedeempresaadmin, em.idempleado, em.idcargo, em.idcargosuperior, 
			em.nombres, apellido_paterno, apellido_materno, key_group, em.idalmacenfarmacia, em.idsubalmacenfarmacia, fsa.nombre_salm, u.real_time_huella, ca.agrega_horario_especial, ea.ajuste_contable',FALSE); 
		$this->db->select('em.idcentrocosto');
		$this->db->from('users u');
		$this->db->join('rh_empleado em','u.idusers = em.iduser');
		$this->db->join('rh_cargo ca','em.idcargo = ca.idcargo','left');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers'); 
		$this->db->join('sede_empresa_admin sea','ups.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','ups.idsede = s.idsede'); 
		$this->db->join('users_groups ug','u.idusers = ug.idusers'); 
		$this->db->join('group g','ug.idgroup = g.idgroup');
		$this->db->join('far_subalmacen fsa','em.idsubalmacenfarmacia = fsa.idsubalmacen','left');
		$this->db->where('u.idusers', $userId);
		$this->db->where('sea.estado_sea', 1); // empresa por sede activa 
		if( $sedeId ){ 
			$this->db->where('s.idsede', $sedeId);
			$this->db->where('sea.idsede', $sedeId);
		}
		// if( $empresaId ){ 
		// 	$this->db->where('e.idempresa', $empresaId);
		// }
		$this->db->where('estado_ups', 1); // HABILITADO 
		$this->db->where('estado_usuario <>', '0');
		$this->db->where('estado_se <>', '0'); 
		$this->db->where('em.estado_empl', 1); // empleado 
		// $this->db->where('estado_em <>', '0');
		$this->db->order_by('ea.nombre_legal', 'ASC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	/* 
		AL LOGEARTE COMO USUARIO SALUD 
		LA VARIABLE SESSION "id_empresa_admin" sirve para vincular la atencion a la EMPRESA ADMINISTRADORA  
		LA VARIABLE SESSION "idempresamedico" sirve para vincular la atencion a la EMPRESA-ESPECIALIDAD 
	*/ 
	public function m_listar_perfil_usuario_salud( $userId, $empresaMedico = FALSE, $empresaAdmin = FALSE)
	{
		$this->db->select('u.idusers, ip_address, username, email, u.real_time_huella, "g"."name" AS grupo, "g"."idgroup", g.key_group, g.vista_sede_empresa, 
			ead.idempresa AS id_empresa_admin, ead.descripcion AS empresa_admin, ead.aleas_empresa, ead.ruc_empresa AS ruc_empresa_admin, 
			ema.idempresa, ema.descripcion AS empresa, (ema.ruc_empresa) AS ruc_tercero, ee.idempresaespecialidad, 
			es.idespecialidad, es.nombre AS especialidad, te.descripcion AS tipoEspecialidad, ca.agrega_horario_especial, 
			em.idempleado, em.nombre_foto, em.nombres, em.apellido_paterno, em.apellido_materno, em.idcargo, em.idcargosuperior, 
			med.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med.colegiatura_profesional, eme.idempresamedico, 
			ead.isea AS idsedeempresaadmin, ead.idsede, ead.aleas_empresa AS sede',FALSE);
		$this->db->select('em.idcentrocosto');
		$this->db->from('users u');
		$this->db->join('rh_empleado em','u.idusers = em.iduser');
		$this->db->join('rh_cargo ca','em.idcargo = ca.idcargo','left');

		/* $this->db->join('users_por_sede ups','u.idusers = ups.idusers'); 
		$this->db->join('sede_empresa_admin sea','ups.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','ups.idsede = s.idsede'); */
		
		/* SOLO USUARIO SALUD */ 
		$this->db->join('medico med','u.idusers = med.iduser');
		$this->db->join('empresa_medico eme','med.idmedico = eme.idmedico AND estado_emme = 1');
		// $this->db->join('empresa_especialidad ee','eme.idempresaespecialidad = ee.idempresaespecialidad AND eme.idsede = ee.idsede AND eme.idempresa = ee.idempresa AND eme.idespecialidad = ee.idespecialidad AND ee.estado_emes = 1');
		$this->db->join('empresa_especialidad ee','eme.idempresaespecialidad = ee.idempresaespecialidad 
			AND eme.idempresa = ee.idempresa AND eme.idespecialidad = ee.idespecialidad AND ee.estado_emes = 1'); 

		$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle AND ed.estado_ed = 1'); 
		$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa AND ema.estado_em = 1'); // EMPRESA EMA 
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa AND ead.estado_em = 1'); // NUEVA EMPRESA ADMIN 
		// $this->db->join('empresa_admin ea','sea.idempresaadmin = ed.idempresaadmin');
		$this->db->join('especialidad es','ee.idespecialidad = es.idespecialidad AND es.estado <> 0'); 
		$this->db->join('tipo_especialidad te','es.idtipoespecialidad = te.idtipoespecialidad'); 

		$this->db->join('users_groups ug','u.idusers = ug.idusers');
		$this->db->join('group g','ug.idgroup = g.idgroup');
		$this->db->where('u.idusers', $userId);
		// $this->db->where('sea.estado_sea', 1); // empresa por sede activa 
		if( $empresaMedico ){
			$this->db->where('eme.idempresamedico', $empresaMedico); // POR AQUI SELECCONAMOS UNA SOLA FILA, LA ELEGIDA PARA EMPRESA ESPECIALIDAD MEDICO.
		}
		if( $empresaAdmin ){
			$this->db->where('ead.idempresa', $empresaAdmin); // POR AQUI SELECCONAMOS UNA SOLA FILA, LA ELEGIDA PARA EMPRESA ADMIN.
		}
		// if( $sedeId ){
		// 	$this->db->where('s.idsede', $sedeId); // POR AQUI SELECCONAMOS UNA SOLA FILA, LA ELEGIDA PARA SEDE EMPRESA ADMIN - SOLO SEDE.
		// }
		$this->db->where('estado_usuario <>', '0'); // usuario 
		$this->db->where('em.estado_empl', 1); // empleado 
		$this->db->order_by('ema.idempresa','DESC'); 
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	/* PARA SESSION */
	public function m_cargar_empresa_admin_matriz_session() 
	{
		$this->db->distinct(); 
		$this->db->select('u.idusers, username, ead.idempresa AS id_empresa_admin, ead.descripcion AS empresa_admin, ead.aleas_empresa, ead.ruc_empresa AS ruc_empresa_admin',FALSE);
		$this->db->from('users u');
		$this->db->join('rh_empleado em','u.idusers = em.iduser'); 
		$this->db->join('medico med','u.idusers = med.iduser'); 
		$this->db->join('empresa_medico eme','med.idmedico = eme.idmedico AND eme.estado_emme = 1'); 
		$this->db->join('empresa_especialidad ee','eme.idempresaespecialidad = ee.idempresaespecialidad 
			AND eme.idempresa = ee.idempresa AND eme.idespecialidad = ee.idespecialidad AND ee.estado_emes = 1'); 
		$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle AND ed.estado_ed = 1'); 
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa AND ead.estado_em = 1'); // NUEVA EMPRESA ADMIN 
		$this->db->where('u.idusers', $this->sessionHospital['idusers']); 
		$this->db->where('estado_usuario', 1); // usuario 
		$this->db->where('em.estado_empl', 1); // empleado 
		$this->db->order_by('ead.idempresa','DESC'); 
		return $this->db->get()->result_array();
	}
	public function m_obtener_sede_empresa($idsedeempresaadmin)
	{
		$this->db->select('se.idsede, se.descripcion AS sede, sea.idsedeempresaadmin, ea.idempresaadmin, ea.razon_social, ea.nombre_legal, ea.ruc, ea.ruc AS ruc_empresa_admin, ea.nombre_logo');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede se','sea.idsede = se.idsede'); 
		$this->db->where('sea.idsedeempresaadmin',$idsedeempresaadmin);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// public function m_obtener_empresa_matriz($idempresaadminmatriz)
	// {
	// 	$this->db->select('u.idusers, ip_address, username, email, u.real_time_huella, "g"."name" AS grupo, "g"."idgroup", g.key_group, g.vista_sede_empresa, 
	// 		ead.idempresa AS id_empresa_admin, ead.descripcion AS empresa_admin, ead.aleas_empresa, ead.ruc_empresa AS ruc_empresa_admin, 
	// 		ema.idempresa, ema.descripcion AS empresa, (ema.ruc_empresa) AS ruc_tercero, ee.idempresaespecialidad, 
	// 		es.idespecialidad, es.nombre AS especialidad, te.descripcion AS tipoEspecialidad, ca.agrega_horario_especial, 
	// 		em.idempleado, em.nombre_foto, em.nombres, em.apellido_paterno, em.apellido_materno, em.idcargo, em.idcargosuperior, 
	// 		med.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med.colegiatura_profesional, eme.idempresamedico',FALSE);
	// 	$this->db->from('users u');
	// 	$this->db->join('rh_empleado em','u.idusers = em.iduser');
	// 	$this->db->join('rh_cargo ca','em.idcargo = ca.idcargo','left');
		
	// 	/* SOLO USUARIO SALUD */ 
	// 	$this->db->join('medico med','u.idusers = med.iduser');
	// 	$this->db->join('empresa_medico eme','med.idmedico = eme.idmedico AND estado_emme = 1');
	// 	$this->db->join('empresa_especialidad ee','eme.idempresaespecialidad = ee.idempresaespecialidad 
	// 		AND eme.idempresa = ee.idempresa AND eme.idespecialidad = ee.idespecialidad AND ee.estado_emes = 1'); 

	// 	$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle AND ed.estado_ed = 1'); 
	// 	$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa AND ema.estado_em = 1'); // EMPRESA EMA 
	// 	$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa AND ead.estado_em = 1'); // NUEVA EMPRESA ADMIN 

	// 	$this->db->join('especialidad es','ee.idespecialidad = es.idespecialidad AND es.estado <> 0'); 
	// 	$this->db->join('tipo_especialidad te','es.idtipoespecialidad = te.idtipoespecialidad'); 

	// 	$this->db->join('users_groups ug','u.idusers = ug.idusers');
	// 	$this->db->join('group g','ug.idgroup = g.idgroup');
	// 	$this->db->where('u.idusers', $userId); 

	// 	if( $idempresaadminmatriz ){ 
	// 		$this->db->where('eme.idempresamedico', $idempresaadminmatriz); // POR AQUI SELECCONAMOS UNA SOLA FILA, LA ELEGIDA PARA EMPRESA ESPECIALIDAD MEDICO.
	// 	}
	// 	$this->db->where('estado_usuario <>', '0'); // usuario 
	// 	$this->db->where('em.estado_empl', 1); // empleado 
	// 	$this->db->order_by('ema.idempresa','DESC'); 
	// 	$this->db->limit(1);
	// 	return $this->db->get()->row_array();
	// }
	// public function m_obtener_sede($idsede)
	// {
	// 	$this->db->select('se.idsede, se.descripcion AS sede, sea.idsedeempresaadmin, ea.idempresaadmin, ea.razon_social, ea.nombre_legal, ea.ruc, ea.nombre_logo');
	// 	$this->db->from('sede_empresa_admin sea');
	// 	$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
	// 	$this->db->join('sede se','sea.idsede = se.idsede'); 
	// 	$this->db->where('se.idsede',$idsede);
	// 	$this->db->limit(1);
	// 	return $this->db->get()->row_array();
	// } 
	public function m_actualizar_fecha_ultima_sesion($datos)
	{
		$data = array(
			'ultimo_inicio_sesion' => date('Y-m-d H:i:s')
		);
		$this->db->where('idusers',$datos['idusers']);
		return $this->db->update('users', $data);
	}
	public function m_registrar_log_sesion($datos)
	{
		$data = array(
			'ip_address' => $this->input->ip_address(),
			'iduser' => $datos['idusers'],
			'fecha_login' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('login_session', $data);
	}
}
?>