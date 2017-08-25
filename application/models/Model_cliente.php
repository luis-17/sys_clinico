<?php
class Model_cliente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_clientes($paramPaginate){ 
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria, nombres, apellido_paterno, apellido_materno, num_documento, sexo, cl.iddepartamento, cl.idprovincia, cl.iddistrito, cl.telefono, cl.celular, email, fecha_nacimiento,
			nombre_via, dir_numero, dir_kilometro, dir_manzana, dir_interior, dir_departamento, dir_lote, referencia, direccion, (ec.descripcion) AS empresa_salud_ocup, 
			dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito,idempresacliente_cli, idprocedencia,
			z.idzona, z.descripcion_zo, tz.idtipozona, tz.descripcion_tz, tv.idtipovia, tv.descripcion_tv, tc.idtipocliente, tc.descripcion_tc, tc.idsedeempresaadmin, cl.si_afiliado_puntos, cl.si_salud_ocupacional');
		$this->db->from('cliente cl');
		$this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		$this->db->join('historia h','cl.idcliente = h.idcliente','left');
		$this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		$this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente AND tc.estado_tc = 1', 'left');
		$this->db->join('empresa_cliente ec','cl.idempresacliente_cli = ec.idempresacliente', 'left');
		$this->db->where('estado_cli', 1); // activo
		// SI ES USUARIO SALUD OCUPACIONAL, SOLO VE SUS CLIENTES
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$this->db->where('cl.si_salud_ocupacional', 1); // SI 
		}
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
	public function m_count_clientes($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('cliente cl');
		//$this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		//$this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		//$this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		//$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		//$this->db->join('historia h','cl.idcliente = h.idcliente','left');
		//$this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		//$this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		//$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente AND tc.estado_tc = 1', 'left');
		$this->db->where('estado_cli', 1); // activo 
		// SI ES USUARIO SALUD OCUPACIONAL, SOLO VE SUS CLIENTES
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$this->db->where('cl.si_salud_ocupacional', 1); // SI 
		}
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
	public function m_cargar_empresas_cliente($paramPaginate){
		$this->db->select('*');
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo
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
	public function m_count_empresas_cliente($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa_cliente');
		$this->db->where('estado_ec', 1); // activo
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
	public function m_cargar_este_cliente($datos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, nombres, apellido_paterno, apellido_materno, num_documento, sexo, cl.iddepartamento, cl.idprovincia, cl.iddistrito, telefono, celular, email, fecha_nacimiento,
			nombre_via, dir_numero, dir_kilometro, dir_manzana, dir_interior, dir_departamento, dir_lote, referencia, direccion,
			dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito, hi.idhistoria,
			z.idzona, z.descripcion_zo, tz.idtipozona, tz.descripcion_tz, tv.idtipovia, tv.descripcion_tv, tc.idtipocliente, tc.descripcion_tc, tc.idsedeempresaadmin, tc.porcentaje_farmacia, 
			si_afiliado_puntos, si_salud_ocupacional');
		$this->db->from('cliente cl');
		$this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		$this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		$this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		$this->db->join('historia hi','cl.idcliente = hi.idcliente', 'left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente AND tc.estado_tc = 1', 'left');
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('cl.idcliente', $datos['idcliente']);
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_cliente_por_num_documento($datos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria ,nombres, apellido_paterno, apellido_materno, num_documento, sexo, cl.iddepartamento, cl.idprovincia, cl.iddistrito, telefono, celular, email, fecha_nacimiento,
			nombre_via, dir_numero, dir_kilometro, dir_manzana, dir_interior, dir_departamento, dir_lote, referencia, direccion,
			dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito,
			z.idzona, z.descripcion_zo, tz.idtipozona, tz.descripcion_tz, tv.idtipovia, tv.descripcion_tv, tc.idtipocliente, tc.descripcion_tc, tc.idsedeempresaadmin, si_afiliado_puntos');
		$this->db->from('cliente cl');
		$this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		$this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente AND tc.estado_tc = 1', 'left');
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('cl.num_documento', $datos['numero_documento']);
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_cliente_por_historia($datos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria ,nombres, apellido_paterno, apellido_materno, num_documento, sexo, cl.iddepartamento, cl.idprovincia, cl.iddistrito, telefono, celular, email, fecha_nacimiento,
			nombre_via, dir_numero, dir_kilometro, dir_manzana, dir_interior, dir_departamento, dir_lote, referencia, direccion,
			dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito,
			z.idzona, z.descripcion_zo, tz.idtipozona, tz.descripcion_tz, tv.idtipovia, tv.descripcion_tv, tc.idtipocliente, tc.descripcion_tc, tc.idsedeempresaadmin, si_afiliado_puntos');
		$this->db->from('cliente cl');
		$this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		$this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente AND tc.estado_tc = 1', 'left');
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('h.idhistoria', $datos['idhistoria']);
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_validar_si_cliente_existe($datos){
		$nombres = strtoupper_total($datos['nombres']);
		$apellido_paterno = strtoupper_total($datos['apellido_paterno']);
		$apellido_materno = strtoupper_total($datos['apellido_materno']);
		$fecha_nacimiento = $datos['fecha_nacimiento'];
		$this->db->where('nombres',$nombres);
		$this->db->where('apellido_paterno',$apellido_paterno);
		$this->db->where('apellido_materno',$apellido_materno);
		$this->db->where('fecha_nacimiento',$fecha_nacimiento);
		$this->db->where('estado_cli', 1); // activo
		if(isset($datos['id'])){
			$this->db->where_not_in('idcliente', $datos['id']);
		}
		$rows = $this->db->get('cliente')->num_rows();
		if($rows > 0){
			return true;
		}else{
			return false;
		}
	}
	public function m_buscar_dni_cliente_con_excepcion($datos, $excepcion = FALSE){
		$this->db->where('num_documento',$datos['num_documento']);
		$this->db->where('estado_cli', 1); // activo
		if($excepcion){
			$this->db->where('idcliente <> '. $datos['id']);
		}
		$rows = $this->db->get('cliente')->num_rows();
		if($rows > 0){
			return true;
		}else{
			return false;
		}
	}
	public function m_cargar_por_dni($datos) // NO BORRAR¡¡¡ 
	{
		$serverName = "192.168.0.43";
		$connectionInfo = array( "Database"=>"dni", "UID"=>"sa", "PWD"=>"b3nx1976" );
		// $serverName = "192.168.0.251";
		// $connectionInfo = array( "Database"=>"dni", "UID"=>"sa", "PWD"=>"012223Jf2016" ); 
		$conn = sqlsrv_connect( $serverName, $connectionInfo); 

		// if( $conn ) { 
		//      echo "Conectado a la Base de Datos.<br />";
		//      die();
		// }else{ 
		//      echo "NO se puede conectar a la Base de Datos.<br />"; 
		//      die( print_r( sqlsrv_errors(), true)); 
		// } 

		if($conn){ 
	    	$sql = 'SELECT * FROM dbo.Padron WHERE DNI=?';
	    	$params = array($datos['num_documento']);

	    	$result = sqlsrv_query( $conn, $sql, $params);
	  		if( $result === false ) {
			    die( print_r( sqlsrv_errors(), true));
			}
			$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			return $row;
		}else{
			//var_dump('no conectó');
			return 0;
		}
	}
	public function m_cargar_clientes_con_historia_autocomplete($datos)
	{ 
		$this->db->select("c.idcliente, c.num_documento, UPPER(CONCAT(c.apellido_paterno,' ',c.apellido_materno,', ',c.nombres)) AS paciente, h.idhistoria", FALSE); 
		$this->db->from('cliente c'); 
		$this->db->join('historia h', 'c.idcliente = h.idcliente');
		$this->db->where('c.estado_cli', 1); // ACTIVO 
		if( $datos ){ 
			$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		}
		$this->db->limit(10); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_clientes_venta_autocomplete($datos)
	{ 
		// var_dump($this->sessionHospital['idsedeempresaadmin']); exit(); 
		$this->db->distinct(); 
		$this->db->select("c.idcliente, hi.idhistoria, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			 UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)) AS paciente", FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('historia hi','c.idcliente = hi.idcliente'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede se','sea.idsede = se.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->where('paciente_atendido_det', 2); // NO 
		$this->db->where('v.idespecialidad', $this->sessionHospital['idespecialidad']); 
		//$this->db->where('se.idsede', $this->sessionHospital['idsede']); 
		//if($this->sessionHospital['es_empresa_admin'] === '1'){ 
		$this->db->where('ea.ruc', $this->sessionHospital['ruc_empresa_admin']); 
		//} 
		
		// $this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// if( @$datos['es_empresa_nuestra'] === TRUE ){ 
		// 	$this->db->where('v.idempresaadmin', $datos['idempresaadmin']); 
		// }
		if($datos['arrTipoProductos']){ 
			$this->db->where_in('pm.idtipoproducto', $datos['arrTipoProductos']); 
		}
		$this->db->where('v.estado', 1); // ACTIVO 
		if( $datos ){ 
			$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		}
		$this->db->order_by("paciente");
		$this->db->limit(5); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_clientes_ocupacional_autocomplete($datos)
	{
		$this->db->select("c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)) AS paciente", FALSE); 
		$this->db->from('cliente c'); 
		$this->db->where('estado_cli', 1); // HABILITADO 
		$this->db->where('c.si_salud_ocupacional', 1); 
		$this->db->where('c.idempresacliente_cli', $datos['empresa']['idempresacliente']); 
		if( $datos ){ 
			$this->db->ilike("UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))", $datos['search']); 
		}
		$this->db->limit(10); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_clientes_ocupacional_con_perfiles_autocomplete($datos)
	{
		$this->db->distinct();
		$this->db->select("c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)) AS paciente", FALSE); 
		$this->db->from('cliente c'); 
		$this->db->join('so_producto_cliente pc','c.idcliente = pc.idcliente'); 
		$this->db->where('estado_cli', 1); // HABILITADO 
		$this->db->where('estado_pc', 1); // HABILITADO 
		$this->db->where('c.si_salud_ocupacional', 1); 
		$this->db->where('c.idempresacliente_cli', $datos['empresa']['idempresacliente']); 
		if( $datos ){ 
			$this->db->ilike("UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))", $datos['search']); 
		}
		$this->db->limit(10); 
		return $this->db->get()->result_array();
	}
	public function m_editar($datos)
	{
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$datos['pertenece_salud_ocup'] = 1;
		}
		$data = array(
			'num_documento' => $datos['num_documento'],
			'nombres' => strtoupper_total($datos['nombres']),
			'apellido_paterno' => strtoupper_total($datos['apellido_paterno']),
			'apellido_materno' => strtoupper_total($datos['apellido_materno']),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'celular' => (empty($datos['celular']) ? NULL : $datos['celular']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'fecha_nacimiento' => (empty($datos['fecha_nacimiento']) ? NULL : $datos['fecha_nacimiento']),
			'sexo' => (empty($datos['sexo']) ? NULL : strtoupper_total($datos['sexo'])),
			'idtipozona' => (empty($datos['idtipozona']) ? NULL : $datos['idtipozona']),
			'idtipovia' => (empty($datos['idtipovia']) ? NULL : $datos['idtipovia']),
			'nombre_via' => (empty($datos['nombre_via']) ? NULL : $datos['nombre_via']),
			'idzona' => (empty($datos['idzona']) ? NULL : $datos['idzona']),
			'idtipocliente' => (empty($datos['idtipocliente']) ? NULL : $datos['idtipocliente']),
			'dir_numero' => (empty($datos['numero']) ? NULL : $datos['numero']),
			'dir_kilometro' => (empty($datos['kilometro']) ? NULL : $datos['kilometro']),
			'dir_manzana' => (empty($datos['manzana']) ? NULL : $datos['manzana']),
			'dir_interior' => (empty($datos['interior']) ? NULL : $datos['interior']),
			'dir_departamento' => (empty($datos['numero_departamento']) ? NULL : $datos['numero_departamento']),
			'dir_lote' => (empty($datos['lote']) ? NULL : $datos['lote']),
			'dir_sector' => (empty($datos['sector']) ? NULL : $datos['sector']),
			'dir_grupo' => (empty($datos['grupo']) ? NULL : $datos['grupo']),
			'referencia' => (empty($datos['referencia']) ? NULL : $datos['referencia']),
			'direccion' => (empty($datos['direccion']) ? NULL : $datos['direccion']),
			'iddepartamento' => (empty($datos['iddepartamento']) ? NULL : $datos['iddepartamento']),
			'idprovincia' => (empty($datos['idprovincia']) ? NULL : $datos['idprovincia']),
			'iddistrito' => (empty($datos['iddistrito']) ? NULL : $datos['iddistrito']),
			'si_salud_ocupacional' => (empty($datos['pertenece_salud_ocup']) ? 2 : $datos['pertenece_salud_ocup']),
			'idempresacliente_cli' => (empty($datos['empresacliente']['id']) ? NULL : $datos['empresacliente']['id']),
			'idprocedencia' => (empty($datos['idprocedencia']) ? NULL : $datos['idprocedencia']),
			'idusuariocreacion' => $this->sessionHospital['idusers'],
			// 'idusuariocreacion' =>(empty($datos['idusuariocreacion']) ? NULL : $datos['idusuariocreacion']),
			'idubigeo' => (empty($datos['idubigeo']) ? NULL : $datos['idubigeo']),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['id']);
		return $this->db->update('cliente', $data);
	}
	public function m_registrar($datos)
	{
		if( $this->sessionHospital['key_group'] == 'key_salud_ocup' ){
			$datos['pertenece_salud_ocup'] = 1;
		}
		$data = array(
			'num_documento' => (empty($datos['num_documento']) ? NULL : $datos['num_documento']),
			'nombres' => strtoupper_total($datos['nombres']),
			'apellido_paterno' => strtoupper_total($datos['apellido_paterno']),
			'apellido_materno' => strtoupper_total($datos['apellido_materno']),
			'telefono' => (empty($datos['telefono']) ? NULL : $datos['telefono']),
			'celular' => (empty($datos['celular']) ? NULL : $datos['celular']),
			'email' => (empty($datos['email']) ? NULL : $datos['email']),
			'fecha_nacimiento' => (empty($datos['fecha_nacimiento']) ? NULL : $datos['fecha_nacimiento']),
			'sexo' => (empty($datos['sexo']) ? NULL : strtoupper_total($datos['sexo'])),
			'idtipozona' => (empty($datos['idtipozona']) ? NULL : $datos['idtipozona']),
			'idtipovia' => (empty($datos['idtipovia']) ? NULL : $datos['idtipovia']),
			'nombre_via' => (empty($datos['nombre_via']) ? NULL : $datos['nombre_via']),
			'idzona' => (empty($datos['idzona']) ? NULL : $datos['idzona']),
			'idtipocliente' => (empty($datos['idtipocliente']) ? NULL : $datos['idtipocliente']),
			'dir_numero' => (empty($datos['numero']) ? NULL : $datos['numero']),
			'dir_kilometro' => (empty($datos['kilometro']) ? NULL : $datos['kilometro']),
			'dir_manzana' => (empty($datos['manzana']) ? NULL : $datos['manzana']),
			'dir_interior' => (empty($datos['interior']) ? NULL : $datos['interior']),
			'dir_departamento' => (empty($datos['numero_departamento']) ? NULL : $datos['numero_departamento']),
			'dir_lote' => (empty($datos['lote']) ? NULL : $datos['lote']),
			'dir_sector' => (empty($datos['sector']) ? NULL : $datos['sector']),
			'dir_grupo' => (empty($datos['grupo']) ? NULL : $datos['grupo']),
			'referencia' => (empty($datos['referencia']) ? NULL : $datos['referencia']),
			'direccion' => (empty($datos['direccion']) ? NULL : $datos['direccion']),
			'iddepartamento' => (empty($datos['iddepartamento']) ? NULL : $datos['iddepartamento']),
			'idprovincia' => (empty($datos['idprovincia']) ? NULL : $datos['idprovincia']),
			'iddistrito' => (empty($datos['iddistrito']) ? NULL : $datos['iddistrito']),
			'si_salud_ocupacional' => (empty($datos['pertenece_salud_ocup']) ? 2 : $datos['pertenece_salud_ocup']),
			'idempresacliente_cli' => (empty($datos['empresacliente']['id']) ? NULL : $datos['empresacliente']['id']),
			'idprocedencia' => (empty($datos['idprocedencia']) ? NULL : $datos['idprocedencia']),
			'idusuariocreacion' =>(empty($datos['idusuariocreacion']) ? NULL : $datos['idusuariocreacion']),
			'idubigeo' => (empty($datos['idubigeo']) ? NULL : $datos['idubigeo']),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('cliente', $data);
	}
	public function m_registrar_historia($datos)
	{
		$data = array(
			'idcliente' => $datos['idcliente'],
			'fecha_creacion' => date('Y-m-d'),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('historia', $data);
	}
	public function m_actualizar_codigo_historia($datos)
	{
		$data = array(
			'codigo_historia' => $datos['codigo_historia']
		);
		$this->db->where('idhistoria',$datos['idhistoria']);
		return $this->db->update('historia', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_cli' => 0,
		);
		$this->db->where('idcliente',$id);
		if($this->db->update('cliente', $data)){
			return true;
		}else{
			return false;
		}
	}
	/***************** TIPO DE CLIENTE *********************/
	public function m_cargar_tipo_cliente_cbo(){ 
		$this->db->select('*');
		$this->db->from('tipo_cliente');
		$this->db->where('estado_tc', 1); // activo
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_cliente($paramPaginate){ 
		$this->db->select('*');
		$this->db->from('tipo_cliente');
		$this->db->where('estado_tc', 1); // activo
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
	public function m_cargar_convenio_cliente($datos){ 
		$this->db->select('idtipocliente, descripcion_tc, estado_tc');
		$this->db->from('tipo_cliente');
		$this->db->where('estado_tc', 1); // activo
		$this->db->where('idtipocliente', $datos['idtipocliente']); // activo
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_producto($datos){ 
		$this->db->select('idtipoproducto, nombre_tp');
		$this->db->from('tipo_producto');
		$this->db->where('estado_tp', 1); // activo
		if (!empty($datos['idmodulo'])) {
			$this->db->where('idmodulo', $datos['idmodulo']);
			$this->db->order_by("nombre_tp");
		}
		return $this->db->get()->result_array();
	}
	/*
	public function m_cargar_tipo_cliente_descuento($datos){ 
		$this->db->select('tcd.idtipoclientedescuento, tp.idtipoproducto, tp.nombre_tp, tcd.porcentaje_dcto');
		$this->db->from('tipo_producto tp');
		$this->db->join('tipo_cliente_descuento tcd', 'tp.idtipoproducto = tcd.idtipoproducto AND estado_tcd = 1 AND idempresaadmin = ' . $this->db->escape($datos['empresaadmin']["id"]) . 'AND idtipocliente = ' . $this->db->escape($datos["idtipocliente"]), 'left');

		$this->db->where('estado_tp', 1); // activo
		$this->db->order_by('tp.idtipoproducto', 'ASC');
		return $this->db->get()->result_array();
	}
	*/
	/*
	public function m_cargar_descuento_tipocliente_por_tipoproducto($datos){ 
		$this->db->select('idtipoclientedescuento, idtipoproducto, porcentaje_dcto');
		$this->db->from('tipo_cliente_descuento');
		$this->db->where('idtipoproducto', $datos['idtipoproducto']);
		$this->db->where('idtipocliente', $datos['idtipocliente']);
		$this->db->where('idempresaadmin', $datos['idempresaadmin']);
		$this->db->where('estado_tcd', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	*/
	public function m_count_tipo_cliente($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_cliente');
		$this->db->where('estado_tc', 1); // activo
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
	public function m_registrar_tipo_cliente($datos)
	{
		$data = array(
			'descripcion_tc' => strtoupper_total($datos['descripcion']),
			'numero_contrato' => $datos['contrato'],
			'fecha_inicial' => $datos['fec_inicial'],
			'fecha_vigencia' => $datos['fec_vigencia'],
			'idsedeempresaadmin'=> $datos['empresaadmin']['id'],
			'estado_tc' => 1

		);
		return $this->db->insert('tipo_cliente', $data);
	}
	/*
	public function m_registrar_tipo_cliente_descuento($datos)
	{
		$data = array(
			'idtipoproducto' => $datos['id'],
			'idtipocliente' => $datos['idtipocliente'],
			'idempresaadmin' => $datos['idempresaadmin'],
			'porcentaje_dcto' => $datos['porcentaje'],
			'estado_tcd' => 1

		);
		return $this->db->insert('tipo_cliente_descuento', $data);
	}
	*/
	public function m_editar_tipo_cliente($datos)
	{
		$data = array(
			'descripcion_tc' => strtoupper_total($datos['descripcion']),
			'numero_contrato' => $datos['contrato'],
			'fecha_inicial' => $datos['fec_inicial'],
			'fecha_vigencia' => $datos['fec_vigencia'],
		);
		$this->db->where('idtipocliente',$datos['idtipocliente']);
		return $this->db->update('tipo_cliente', $data);
	}
	/*
	public function m_editar_tipo_cliente_descuento($datos)
	{
		$data = array(
			// 'idtipoproducto' => $datos['id'],
			// 'idtipocliente' => $datos['idtipocliente'],
			//'idempresaadmin' => $datos['idempresaadmin'],
			'porcentaje_dcto' => $datos['porcentaje']

		);
		$this->db->where('idtipoclientedescuento',$datos['idtipoclientedescuento']);
		return $this->db->update('tipo_cliente_descuento', $data);
	}
	*/
	public function m_anular_tipo_cliente($id)
	{
		$data = array(
			'estado_tc' => 0
		);
		$this->db->where('idtipocliente',$id);
		if($this->db->update('tipo_cliente', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_afiliar_cliente_a_puntos($id)
	{
		$data = array(
			'si_afiliado_puntos' => 1,
			'fecha_afiliacion_puntos' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$id);
		return $this->db->update('cliente', $data);
	}
	public function m_iniciar_puntaje_cliente($id)
	{
		$data = array(
			'idcliente' => $id,
			'puntos_acumulados' => 0,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_pd' => 1
		);
		return $this->db->insert('far_punto_descuento', $data);
	}
	public function m_comprobar_afiliacion_puntos($datos)
	{
		$this->db->select('num_documento, idcliente');
		$this->db->from('cliente');
		$this->db->where('num_documento',$datos['num_documento']);
		$this->db->where('si_afiliado_puntos', 1);
		$this->db->where('estado_cli', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_puntaje_cliente($datos)
	{
		$this->db->select('idpuntodescuento, idcliente, puntos_acumulados, estado_pd');
		$this->db->from('far_punto_descuento');
		$this->db->where('idcliente',$datos['idcliente']);
		$this->db->where('estado_pd', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_puntaje_cliente($datos)
	{
		$data = array(
			'puntos_acumulados' => $datos['puntaje_obtenido'],
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idpuntodescuento',$datos['idpuntodescuento']);
		return $this->db->update('far_punto_descuento', $data);
	}
	public function m_actualizar_puntaje_con_canje($datos)
	{
		$data = array(
			'fecha_canje' => date('Y-m-d H:i:s'),
			'estado_pd'=> 2
		);
		$this->db->where('idpuntodescuento',$datos['idpuntodescuento']);
		return $this->db->update('far_punto_descuento', $data);
	}
	public function m_iniciar_nuevo_puntaje($datos){
		$data = array(
			'idcliente' => $datos['idcliente'],
			'puntos_acumulados' => intval($datos['puntaje_obtenido']) - 1000,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_pd' => 1
		);
		return $this->db->insert('far_punto_descuento', $data);
	}

	public function m_actualizar_datos_cliente($datos){
		$data = array(
			'celular' => $datos['celular'],
			'telefono' => empty($datos['telefono']) ? NULL : $datos['telefono'],
			'updatedAt' =>  date('Y-m-d H:i:s'),
		);
		$this->db->where('idcliente',$datos['id']);
		return $this->db->update('cliente', $data);
	}
}