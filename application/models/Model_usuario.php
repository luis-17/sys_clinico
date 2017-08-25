<?php
class Model_usuario extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	public function m_cargar_usuarios($paramPaginate){
 		$this->db->select("UPPER(CONCAT_WS(' ', em.nombres, em.apellido_paterno, em.apellido_materno)) AS empleado, nombre_foto",FALSE);
 		$this->db->select('u.idusers, username, password, email, idusersgroups, g.idgroup, g.name, estado_usuario, g.vista_sede_empresa');
 		$this->db->from('users u');
 		$this->db->join('rh_empleado em','u.idusers = em.iduser AND estado_empl = 1','left');
 		$this->db->join('users_groups ug','u.idusers = ug.idusers','left');
 		if($this->sessionHospital['key_group'] != 'key_sistemas'){
			$this->db->join('group g','ug.idgroup = g.idgroup AND g.vista_sede_empresa = 1','left'); 
		}else{ 
			$this->db->join('group g','ug.idgroup = g.idgroup','left');
		}
		$this->db->where('estado_g', 1); // activo 
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
		if( $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow']);
		}
		return $this->db->get()->result_array();
 	}
 	public function m_count_usuarios($paramPaginate){
 		$this->db->select('COUNT(*) AS contador',FALSE);
 		$this->db->from('users u');
 		$this->db->join('rh_empleado em','u.idusers = em.iduser AND estado_empl = 1','left');
 		$this->db->join('users_groups ug','u.idusers = ug.idusers','left');
		// $this->db->join('group g','ug.idgroup = g.idgroup','left');
		if($this->sessionHospital['key_group'] != 'key_sistemas'){
			$this->db->join('group g','ug.idgroup = g.idgroup AND g.vista_sede_empresa = 1','left'); 
		}else{ 
			$this->db->join('group g','ug.idgroup = g.idgroup','left');
		}
		$this->db->where('estado_g', 1); // activo
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
 	}
 	/*
	public function m_cargar_usuarios($paramPaginate){ 
		//$this->db->distinct();
		$this->db->select("UPPER(CONCAT(em.nombres,' ',em.apellido_paterno,' ',em.apellido_materno)) AS empleado, nombre_foto",FALSE); // AQUI ME QUEDEEE  XD 
		$this->db->select('u.idusers, username, password, email, idusersgroups, idgroup, MAX(name) AS name, 
			MAX(s.descripcion) AS descripcion, MAX(ea.idempresaadmin) AS idempresaadmin, MAX(ea.razon_social) AS razon_social, estado_usuario, 
			s.idsede, ea.idempresaadmin, sea.idsedeempresaadmin'); 
		$this->db->select("STRING_AGG(s.descripcion, ', ' ORDER BY s.descripcion ) AS sedes, 
			ARRAY_TO_STRING(ARRAY_AGG(s.idsede ORDER BY s.descripcion),',') AS idsedes, 
			ARRAY_TO_STRING(ARRAY_AGG(ups.idusersporsede ORDER BY s.descripcion),',') AS idusersporsedes", FALSE);
		$this->db->from('users u');
		$this->db->join('rh_empleado em','u.idusers = em.iduser AND estado_empl = 1','left');
		$this->db->join('users_groups ug','u.idusers = ug.idusers','left');
		$this->db->join('group g','ug.idgroup = g.idgroup','left');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers AND estado_ups = 1','left');
		$this->db->join('sede_empresa_admin sea','ups.idsedeempresaadmin = sea.idsedeempresaadmin','left');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin','left');
		$this->db->join('sede s','sea.idsede = s.idsede','left'); 
		//$this->db->join('users_por_sede ups','u.idusers = ups.idusers AND estado_ups = 1','left');
		//$this->db->join('sede s','ups.idsede = s.idsede','left');
		$this->db->where('estado_g', 1); // activo
		$this->db->group_by('u.idusers,s.idsede, ea.idempresaadmin, sea.idsedeempresaadmin,em.nombres,em.apellido_paterno,em.apellido_materno,em.nombre_foto');
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
		if( $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow']);
		}
		return $this->db->get()->result_array();
	}
	public function m_count_usuarios($paramPaginate)
	{

		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('users u');
		$this->db->join('rh_empleado em','u.idusers = em.iduser AND estado_empl = 1','left');
		$this->db->join('users_groups ug','u.idusers = ug.idusers','left');
		$this->db->join('group g','ug.idgroup = g.idgroup','left');
		$this->db->join('users_por_sede ups','u.idusers = ups.idusers AND estado_ups = 1','left');
		$this->db->join('sede s','ups.idsede = s.idsede','left');
		$this->db->where('estado_g', 1); // activo
		$this->db->group_by('u.idusers');
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		
		$filas = $this->db->get()->num_rows();
		return $filas;
	}
	*/
	public function m_cargar_usuarios_cbo($datos = FALSE) // SOLO USUARIOS QUE FALTAN ASIGNAR 
	{
		$this->db->select('u.idusers, username, email, estado_usuario');
		$this->db->from('users u');
		$this->db->join('rh_empleado e','u.idusers = e.iduser AND e.estado_empl = 1','left');
		$this->db->where('estado_usuario', 1);
		$this->db->where('idempleado IS NULL');
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_usuarios_all()
	{
		$this->db->select('u.idusers, u.username, u.email, u.estado_usuario, u.ultimo_inicio_sesion'); 
		$this->db->from('users u');
		$this->db->join('rh_empleado e','u.idusers = e.iduser AND e.estado_empl = 1','left');
		$this->db->where('u.estado_usuario', 1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_usuarios_caja($datos = NULL) // solo los usuarios que han abierto caja alguna vez
	{
		$this->db->distinct();
		$this->db->select('u.idusers, username, email, estado_usuario');
		$this->db->select('e.apellido_paterno, e.apellido_materno, e.nombres');
		$this->db->from('users u');
		$this->db->join('caja c','u.idusers = c.iduser');
		$this->db->join('users_groups ug','u.idusers = ug.idusers');
		$this->db->join('group g','ug.idgroup = g.idgroup');
		$this->db->join('rh_empleado e', 'u.idusers = e.iduser');
		$this->db->where('estado_usuario', 1);
		if(@$datos['idtiporeporte'] == '11'){
			$this->db->where_in('key_group', array("key_caja_far","key_admin","key_dir_far"));
		}else{
			$this->db->where_in('key_group', array("key_caja","key_admin"));
		}
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_sedes_no_agregados_a_usuario($paramPaginate,$datos)
	{
		$sql = 'SELECT s.idsede, s.descripcion 
		FROM sede s 
		LEFT JOIN users_por_sede ups ON s.idsede = ups.idsede AND estado_ups = 1 
		LEFT JOIN users u ON ups.idusers = u.idusers AND estado_usuario = 1 AND u.idusers = ? 
		WHERE s.idsede NOT IN( 
			SELECT c_s.idsede 
			FROM sede c_s 
			JOIN users_por_sede c_ups ON c_s.idsede = c_ups.idsede  
			WHERE c_ups.idusers = ? AND estado_se = 1 AND estado_ups = 1
		)
		AND estado_se = 1'; 
		if( $paramPaginate['search'] ){ 
			$sql.= " AND LOWER(".$paramPaginate['searchColumn'].") LIKE '%".strtolower($paramPaginate['searchText'])."%' ESCAPE '!'";
		}
		$sql .= ' GROUP BY s.idsede';
		if( $paramPaginate['sortName'] ){
			$sql.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			$sql.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		//var_dump($paramPaginate); 

		$query = $this->db->query($sql,array($datos['id'],$datos['id']));
		return $query->result_array();
	}
	public function m_count_sedes_no_agregados_a_usuario($datos)
	{
		$sql = 'SELECT COUNT(*) AS contador 
		FROM sede s 
		LEFT JOIN users_por_sede ups ON s.idsede = ups.idsede AND estado_ups = 1 
		LEFT JOIN users u ON ups.idusers = u.idusers AND estado_usuario = 1 AND u.idusers = ? 
		WHERE s.idsede NOT IN( 
			SELECT c_s.idsede 
			FROM sede c_s 
			JOIN users_por_sede c_ups ON c_s.idsede = c_ups.idsede  
			WHERE c_ups.idusers = ? AND estado_se = 1 AND estado_ups = 1
		)
		AND estado_se = 1 GROUP BY s.idsede'; 
		$query = $this->db->query($sql,array($datos['id'],$datos['id']));
		$fEmpresa = $query->row_array();
		return $fEmpresa['contador'];
	}
	public function m_cargar_este_usuario($datos)
	{ 
		$this->db->select('u.idusers, username, password, email, estado_usuario, real_time_huella'); 
		$this->db->from('users u');
		$this->db->where('estado_usuario', 1); 
		$this->db->where('idusers',$datos['id']);
		return $this->db->get()->row_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'username' => $datos['usuario'],
			'email' => $datos['email'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idusers',$datos['id']);
		return $this->db->update('users', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'username' => $datos['usuario'], 
			'email' => empty($datos['email']) ? NULL:$datos['email'], 
			'password' => do_hash($datos['clave'],'md5'), 
			'password_watch' => $datos['clave'],
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'), 
			'ip_address' => $this->input->ip_address()
		);

		return $this->db->insert('users', $data);
	}
	public function m_editar_detalle($datos)
	{
		$data = array(
			'idgroup' => $datos['groupId']
		);
		$this->db->where('idusersgroups',$datos['iddetalle']);
		return $this->db->update('users_groups', $data);
	}
	public function m_registrar_detalle($datos)
	{
		$data = array(
			'idusers' => $datos['id'],
			'idgroup' => $datos['groupId'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('users_groups', $data);
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_usuario' => 0,
		);
		$this->db->where('idusers',$id);
		if($this->db->update('users', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_usuario' => 1,
		);
		$this->db->where('idusers',$id);
		if($this->db->update('users', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_agregar_sede_usuario($datos)
	{
		$data = array(
			'idusers' => $datos['iduser'],
			'idsede' => $datos['id'],
			'idsedeempresaadmin'=> $datos['idsedeempresaadmin']
		);
		return $this->db->insert('users_por_sede', $data);
	}
	public function m_editar_sede_usuario($datos)
	{
		$data = array(
			'idsede' => $datos['idsede'],
			'idsedeempresaadmin'=> $datos['idsedeempresaadmin']
		);
		$this->db->where('idusers',$datos['idusers']);
		return $this->db->update('users_por_sede', $data);
	}
	public function m_quitar_sede_usuario($id) 
	{
		$data = array(
			'estado_ups' => 0,
		);
		$this->db->where('idusersporsede',$id);
		if($this->db->update('users_por_sede', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_verifica_password($datos){ 
		$this->db->select('*');
		$this->db->from('users u');
		$this->db->where('idusers',$datos['id']);
		$this->db->where('password', do_hash($datos['clave'] , 'md5'));
		$this->db->where('estado_usuario <>', '0');
		$this->db->limit(1);

		/*
		$usuario = $this->db->get()->row_array();
		if( $this->encrypt->decode($usuario['password']) == $datos['clave'] ){
			return $usuario;
		}else{
			return FALSE;
		}
		*/
		
		return $this->db->get()->row_array();
	}
	public function m_actualizar_password($datos){

		$data = array(
			'password' => do_hash($datos['claveNueva'],'md5'),
			'password_watch' => $datos['claveNueva'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idusers',$datos['id']);
		return $this->db->update('users', $data);
	}

	public function m_actualizar_estado($id,$estado){

		$data = array(
			'estado_ups' => $estado,
		);
		$this->db->where('idusersporsede',$id);
		return $this->db->update('users_por_sede', $data);
		
	}

	public function m_confirma_password($datos){ 
		$this->db->select('1 as result');
		$this->db->from('users u');
		$this->db->where('idusers',$datos['id']);
		$this->db->where('password', do_hash($datos['clave'] , 'md5'));
		$this->db->where('estado_usuario <>', '0');
		$this->db->limit(1);

		$fData = $this->db->get()->row_array();
		return $fData['result'];
	}

	public function m_cargar_usuarios_notificacion($datos){
		$this->db->distinct();
		$this->db->select('u.idusers');
		$this->db->from('users_groups ug');
		$this->db->join('users u', 'u.idusers = ug.idusers');
		$this->db->where('u.estado_usuario <>', '0');
		$this->db->where_in('ug.idgroup',$datos['idgrupos']);		

		return $this->db->get()->result_array();
	}

	public function m_cargar_user_empleado_autocomplete($datos = FALSE){
		$this->db->select('u.idusers, u.username, u.email, e.idempleado');
		$this->db->select('e.apellido_paterno, e.apellido_materno, e.nombres');
		$this->db->from('users u');
		$this->db->join('rh_empleado e','u.idusers = e.iduser AND e.estado_empl = 1','left');
		$this->db->where('estado_usuario', 1);
		$this->db->like("LOWER(e.apellido_paterno || ' ' || e.apellido_materno || ' ' || e.nombres)", strtolower($datos['search']));
		$this->db->limit(10);
		
		return $this->db->get()->result_array();
	}

	public function m_cargar_registro_usuario_web($datos){
		$this->db->select('cl.nombres,cl.apellido_paterno,cl.apellido_materno,cl.num_documento,cl.celular,cl.telefono,cl.email,cl.sexo');
		$this->db->select("CASE	WHEN cl.si_registro_web = 1 THEN 'SI' ELSE 'NO'	END AS registro_web");
		$this->db->select("CASE	WHEN uw.estado_uw = 1 THEN 'ACTIVO' ELSE 'INACTIVO'	END AS estado_usuario_web");
		$this->db->select("( SELECT COUNT(uwc.idusuarioweb) as total_citas FROM ce_usuario_web_cita uwc WHERE uwc.idusuarioweb = uw.idusuarioweb )");
		$this->db->select("uw.createdAt");
		$this->db->from("ce_usuario_web uw");
		$this->db->join("cliente cl",'uw.idcliente = cl.idcliente');
		//$this->db->where("uw.idusuarioweb NOT IN ( SELECT uwc.idusuarioweb FROM	ce_usuario_web_cita uwc )");
		$this->db->where('DATE(uw."createdAt") BETWEEN '. $this->db->escape($datos['fecha_desde']). ' AND '. $this->db->escape($datos['fecha_hasta'])); 
		$this->db->where("uw.estado_uw <> 0");
		$this->db->order_by("uw.createdAt ASC");

		return $this->db->get()->result_array();
	}
}