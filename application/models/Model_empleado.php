<?php
class Model_empleado extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_empleados($paramPaginate,$paramDatos){ 
		$this->db->distinct();
		$this->db->select('e.idempleado, e.idtipodocumentorh, td.descripcion_rtd, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.fecha_nacimiento'); 
		$this->db->select('e.operador_movil, e.telefono,e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, e.sexo'); 
		$this->db->select('e.grupo_sanguineo, e.estado_civil, e.nombre_foto,e.iddepartamento, e.idprovincia, e.iddistrito'); 

		$this->db->select('e.es_tercero, e.es_ipress, e.es_privado, e.marca_asistencia,e.codigo_asistencia, e.idbanco, e.cuenta_corriente'); 

		$this->db->select('e.salario_basico::numeric, e.condicion_laboral, e.fecha_ingreso, e.fecha_inicio_contrato, e.fecha_fin_contrato',FALSE);
		$this->db->select('e.carnet_extranjeria, e.ruc_empleado, e.codigo_essalud, e.centro_essalud, e.fecha_caducidad_coleg');
		$this->db->select('e.reg_pensionario, e.cuspp, e.fecha_afiliacion, e.documento_afiliacion, e.si_activo, e.nombre_cv, ');
		$this->db->select('em.idempresa, (em.descripcion) AS empresa, s.idsede, (s.descripcion) AS sede');
		$this->db->select('UPPER(dpto.descripcion_ubig) AS departamento, UPPER(prov.descripcion_ubig) AS provincia, UPPER(dist.descripcion_ubig) AS distrito');
		$this->db->select('e.es_personal_salud, e.es_personal_farmacia, e.es_personal_administrativo, e.idsedeempleado');
		$this->db->select('e.apellido_paterno_cy, e.apellido_materno_cy, e.nombres_cy, e.lugar_labores_cy, e.fecha_nacimiento_cy');
		$this->db->select('e.idalmacenfarmacia, e.idsubalmacenfarmacia, e.colegiatura_profesional_emp, m.idmedico');
		$this->db->select('ae.idareaempresa, (ae.descripcion_ae) AS area, pr.idprofesion, pr.descripcion_prf');
		$this->db->select('c.idcargo, c.descripcion_ca, u.idusers, u.username,  esp.idespecialidad, (esp.nombre) AS especialidad');
		$this->db->select('cj.idcargo AS idcargosuperior, cj.descripcion_ca AS cargo_superior');
		$this->db->select('afp.idafp, afp.descripcion_afp, colegiatura_profesional, hc.nombre_archivo, e.tipo_comision');
		$this->db->select('m.idcategoriapersonalsalud, cc.idcentrocosto, cc.idsubcatcentrocosto');
		$this->db->select("ej.idempleado AS idempleadojefe, concat_ws(' ',  ej.nombres, ej.apellido_paterno, ej.apellido_materno) AS jefe_inmediato");
		$this->db->from('rh_empleado e');
		$this->db->join('rh_tipo_documento td', 'e.idtipodocumentorh = td.idtipodocumentorh');
		$this->db->join('ct_centro_costo cc', 'e.idcentrocosto = cc.idcentrocosto', 'left');
		$this->db->join("ubigeo dpto","e.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","e.idprovincia = prov.idprovincia AND prov.iddepartamento = e.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"e.iddistrito = dist.iddistrito AND dist.iddepartamento = e.iddepartamento AND dist.idprovincia = e.idprovincia", 'left');
		$this->db->join('rh_afp afp','e.idafp = afp.idafp AND estado_afp = 1','left');
		$this->db->join('rh_cargo c','e.idcargo = c.idcargo AND estado_ca = 1','left');
		$this->db->join('users u','e.iduser = u.idusers AND estado_usuario = 1','left');
		$this->db->join('rh_area_empresa ae','e.idareaempresa = ae.idareaempresa','left');
		$this->db->join('medico m','e.idempleado = m.idempleado','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->join('especialidad esp','e.idespecialidad = esp.idespecialidad AND esp.estado = 1','left');
		$this->db->join('rh_historial_contrato hc','e.idempleado = hc.idempleado AND hc.estado_hc = 1 AND contrato_actual = 1','left');
		$this->db->join('rh_empleado ej', 'e.idempleadojefe = ej.idempleado AND ej.estado_empl = 1','left');
		$this->db->join('rh_cargo cj', 'e.idcargosuperior = cj.idcargo','left');		
		$this->db->where('e.estado_empl', 1); // habilitado 
		if( !empty($paramDatos['activo']) ){
			if( !($paramDatos['activo'] == 'all') ){
				$this->db->where('e.si_activo', $paramDatos['activo']);
			}
		}
		if( !empty($paramDatos['tercero']) ){
			if( !($paramDatos['tercero'] == 'all') ){
				$this->db->where('e.es_tercero', $paramDatos['tercero']);
			}
		}
		if( !empty($paramDatos['modulo']) ){
			if( $paramDatos['modulo'] == 'asist' ){
				$this->db->where('e.marca_asistencia', 1); 
				$this->db->where('e.si_activo', 1);
			}
		}
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $this->sessionHospital['agrega_horario_especial'] == 1){ // CARGO QUE PUEDE AGREGAR HORARIO ESPECIAL
			if( $paramDatos['modulo'] == 'asist' ){
				//$this->db->where('em.ruc_empresa',$this->sessionHospital['ruc_empresa_admin']); 
				// -- $this->db->where('s.idsede',$this->sessionHospital['idsede']); 
				$this->db->where('e.idsedeempleado',$this->sessionHospital['idsede']);
			}
			$this->db->where('e.idcargosuperior', $this->sessionHospital['idcargo']);
			$this->db->or_where('u.idusers', $this->sessionHospital['idusers']);			
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_empleados($paramPaginate,$paramDatos)
	{
		//$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo c','e.idcargo = c.idcargo AND estado_ca = 1','left');
		$this->db->join('users u','e.iduser = u.idusers AND estado_usuario = 1','left');
		$this->db->join('medico m','e.idempleado = m.idempleado','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('especialidad esp','e.idespecialidad = esp.idespecialidad AND esp.estado = 1','left');
		$this->db->join('rh_historial_contrato hc','e.idespecialidad = hc.idempleado AND hc.estado_hc = 1 AND contrato_actual = 1','left');
		$this->db->join('rh_empleado ej', 'e.idempleadojefe = ej.idempleado AND ej.estado_empl = 1','left');
		$this->db->join('rh_cargo cj', 'e.idcargosuperior = cj.idcargo','left');
		$this->db->join('rh_tipo_documento td', 'e.idtipodocumentorh = td.idtipodocumentorh');
		$this->db->where('e.estado_empl', 1); // habilitado 
		if( !empty($paramDatos['activo']) ){
			if( !($paramDatos['activo'] == 'all') ){
				$this->db->where('e.si_activo', $paramDatos['activo']);
			}
		}
		if( !empty($paramDatos['tercero']) ){
			if( !($paramDatos['tercero'] == 'all') ){
				$this->db->where('e.es_tercero', $paramDatos['tercero']);
			}
		}
		if( !empty($paramDatos['modulo']) ){
			if( $paramDatos['modulo'] == 'asist' ){
				$this->db->where('e.marca_asistencia', 1);
				$this->db->where('e.si_activo', 1);
				 
			}
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $this->sessionHospital['agrega_horario_especial'] == 1){ // CARGO QUE PUEDE AGREGAR HORARIO ESPECIAL
			if( $paramDatos['modulo'] == 'asist' ){
				//$this->db->where('em.ruc_empresa',$this->sessionHospital['ruc_empresa_admin']); 
				// $this->db->where('s.idsede',$this->sessionHospital['idsede']); 
				$this->db->where('e.idsedeempleado',$this->sessionHospital['idsede']);
			}
			$this->db->where('e.idcargosuperior', $this->sessionHospital['idcargo']);
			$this->db->or_where('u.idusers', $this->sessionHospital['idusers']);
			
		}
		$totalRows = $this->db->get()->num_rows(); // condicion_laboral 
		return $totalRows;
	}
	public function m_cargar_empleados_general_excel() // descripcion_ca sc_de.fecha_desde_estudio
	{
		$SQL = "SELECT e.idempleado,e.numero_documento,e.nombres,e.apellido_paterno,e.apellido_materno,e.operador_movil,e.telefono,e.telefono_fijo,e.correo_electronico,
			e.direccion,e.referencia,salario_basico,e.sexo,fecha_nacimiento,nombre_foto,e.estado_civil,e.carnet_extranjeria,e.ruc_empleado,e.codigo_essalud,e.centro_essalud,
			e.grupo_sanguineo,e.apellido_paterno_cy,e.apellido_materno_cy,e.nombres_cy,e.lugar_labores_cy,e.fecha_nacimiento_cy,fecha_caducidad_coleg,e.iddepartamento,
			e.idprovincia,e.iddistrito,es_personal_salud,es_personal_farmacia,es_personal_administrativo,idalmacenfarmacia,idsubalmacenfarmacia,colegiatura_profesional_emp,
			UPPER (dpto.descripcion_ubig) AS departamento,UPPER (prov.descripcion_ubig) AS provincia,UPPER (dist.descripcion_ubig) AS distrito,ae.idareaempresa,(ae.descripcion_ae) AS area,c.idcargo,
			c.descripcion_ca,u.idusers,username,em.idempresa,(em.descripcion) AS empresa,s.idsede,(s.descripcion) AS sede,esp.idespecialidad,(esp.nombre) AS especialidad,m.idmedico,
			colegiatura_profesional,codigo_asistencia,es_tercero,marca_asistencia,idsedeempleado,pr.idprofesion,pr.descripcion_prf,reg_pensionario,afp.idafp,
			afp.descripcion_afp,hc.condicion_laboral,e.fecha_ingreso,cuspp,fecha_afiliacion,documento_afiliacion,
			hc.nombre_archivo,hc.fecha_inicio_contrato,hc.fecha_fin_contrato, cj.idcargo AS idcargosuperior, cj.descripcion_ca AS cargo_superior, e.es_ipress, e.es_privado, 
			( 
				SELECT 
					(sc_de.centro_estudio)
				FROM
					rh_detalle_estudio sc_de
				WHERE
					sc_de.idempleado = e.idempleado
				ORDER BY
					sc_de.iddetalleestudio DESC
				LIMIT 1  
			) AS centro_estudio, 
			( 
				SELECT 
					(sc_de.estudio_completo)
				FROM
					rh_detalle_estudio sc_de
				WHERE
					sc_de.idempleado = e.idempleado
				ORDER BY
					sc_de.iddetalleestudio DESC
				LIMIT 1  
			) AS estudio_completo, 
			( 
				SELECT 
					(sc_de.grado_academico)
				FROM
					rh_detalle_estudio sc_de
				WHERE
					sc_de.idempleado = e.idempleado
				ORDER BY
					sc_de.iddetalleestudio DESC
				LIMIT 1  
			) AS grado_academico, 
			( 
				SELECT 
					(sc_de.fecha_desde)
				FROM
					rh_detalle_estudio sc_de
				WHERE
					sc_de.idempleado = e.idempleado
				ORDER BY
					sc_de.iddetalleestudio DESC
				LIMIT 1  
			) AS fecha_desde_estudio, 
			( 
				SELECT 
					(sc_de.fecha_hasta)
				FROM
					rh_detalle_estudio sc_de
				WHERE
					sc_de.idempleado = e.idempleado
				ORDER BY
					sc_de.iddetalleestudio DESC
				LIMIT 1  
			) AS fecha_hasta_estudio 
			FROM rh_empleado e 
			LEFT JOIN ubigeo dpto ON e.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00' 
			LEFT JOIN ubigeo prov ON e.idprovincia = prov.idprovincia AND prov.iddepartamento = e.iddepartamento AND prov.iddistrito = '00' 
			LEFT JOIN ubigeo dist ON e.iddistrito = dist.iddistrito AND dist.iddepartamento = e.iddepartamento AND dist.idprovincia = e.idprovincia 
			LEFT JOIN rh_afp afp ON e.idafp = afp.idafp AND estado_afp = 1 
			LEFT JOIN rh_cargo c ON e.idcargo = c.idcargo AND estado_ca = 1 
			LEFT JOIN users u ON e.iduser = u.idusers AND estado_usuario = 1 
			LEFT JOIN rh_area_empresa ae ON e.idareaempresa = ae.idareaempresa 
			LEFT JOIN medico m ON e.idempleado = m.idempleado 
			LEFT JOIN empresa em ON e.idempresa = em.idempresa AND estado_em = 1 
			LEFT JOIN sede s ON e.idsedeempleado = s.idsede 
			LEFT JOIN rh_profesion pr ON e.idprofesion = pr.idprofesion 
			LEFT JOIN especialidad esp ON e.idespecialidad = esp.idespecialidad AND esp.estado = 1 
			LEFT JOIN rh_historial_contrato hc ON e.idempleado = hc.idempleado AND hc.estado_hc = 1 AND contrato_actual = 1 
			LEFT JOIN rh_cargo cj ON e.idcargosuperior = cj.idcargo 
			WHERE e.estado_empl = 1 AND e.si_activo = 1  
		";
		$query = $this->db->query($SQL); 
		return $query->result_array(); 
	}
	public function m_cargar_empleado_cbo($datos,$empresa = FALSE)
	{
		$this->db->select("idempleado, (nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado");
		$this->db->from('rh_empleado');
		$this->db->where('estado_empl', 1); // habilitado
		// $this->db->where('es_personal_salud', 2); // si salud
		if( $datos ){ 
			$this->db->ilike("nombres || ' ' || apellido_paterno || ' ' || apellido_materno", strtolower($datos['search']));
		}
		if($empresa){ 
			$this->db->where("idempresa", $datos['empresa']['id']);
		}
		//$this->db->limit(25);
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleado_todos_autocomplete($datos)
	{
		$this->db->distinct(); 
		$this->db->select("idempleado, concat_ws(' ',nombres, apellido_paterno, apellido_materno) AS empleado", FALSE);
		$this->db->from('rh_empleado e');
		$this->db->where('estado_empl', 1); // empleado

		$this->db->ilike("concat_ws(' ',nombres, apellido_paterno, apellido_materno)", strtolower($datos['search']));
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_telefono()
	{
		$this->db->select("idempleado, (nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado, numero_documento, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto, fecha_nacimiento, e.telefono");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('si_activo', 1); // activo
		$this->db->order_by("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_cumpleaneros_mes($mes = FALSE)
	{
		$this->db->select("idempleado, (nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado, codigo_asistencia, numero_documento, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto, fecha_nacimiento");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		if( $mes ){
			$this->db->where('EXTRACT(MONTH FROM e.fecha_nacimiento) =', $mes);
		}else{
			$this->db->where('EXTRACT(MONTH FROM e.fecha_nacimiento) =', date('m'));
		}
		
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('si_activo', 1); // activo
		$this->db->order_by("EXTRACT(DAY FROM e.fecha_nacimiento)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_cumpleaneros_dia()
	{
		$this->db->select("e.idempleado, (e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno) AS empleado, codigo_asistencia, numero_documento, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto, fecha_nacimiento");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->where('EXTRACT(MONTH FROM e.fecha_nacimiento) =', date('m'));
		$this->db->where('EXTRACT(DAY FROM e.fecha_nacimiento) =', date('d'));
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('si_activo', 1); // activo
		$this->db->order_by("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_de_empresa_asistencia($empresa=FALSE,$arrCondicionLab=FALSE,$empActivos=FALSE,$sede=FALSE)
	{
		$this->db->select("e.idempleado, (e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno) AS empleado, codigo_asistencia, e.numero_documento, e.fecha_nacimiento, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto, si_activo");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left'); 
		if( !empty($arrCondicionLab) ){ 
			$this->db->join('rh_historial_contrato rhc','e.idempleado = rhc.idempleado AND contrato_actual = 1','left'); 
			$this->db->where_in('rhc.condicion_laboral', $arrCondicionLab);
		}
		$this->db->where('e.estado_empl', 1); // habilitado 
		$this->db->where('emp.estado_em', 1); // habilitado empresa
		if( !empty($empActivos) ){ 
			$this->db->where('e.si_activo', 1); // habilitado 
		}
		
		$this->db->where('marca_asistencia', 1); // habilitado asistencia 
		if($empresa){ 
			$this->db->where("emp.idempresa", $empresa['id']); 
		}
		if($sede){ 
			$this->db->where("e.idsedeempleado", $sede['id']); 
		}
		// $this->db->order_by("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno)");
		$this->db->order_by('e.apellido_paterno');
		$this->db->order_by('e.apellido_materno');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_reporte_distrito($datos)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado",FALSE);
		$this->db->select("(em.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.operador_movil, e.telefono, 
			e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, salario_basico,fecha_nacimiento, nombre_foto, e.estado_civil, 
			e.carnet_extranjeria, e.ruc_empleado, e.codigo_essalud, e.centro_essalud,e.grupo_sanguineo,fecha_caducidad_coleg, dist.idubigeo, descripcion_prf, 
			e.iddepartamento, e.idprovincia, e.iddistrito, UPPER(dpto.descripcion_ubig) AS departamento, UPPER(prov.descripcion_ubig) AS provincia, UPPER(dist.descripcion_ubig) AS distrito'); 
		$this->db->from('rh_empleado e');
		$this->db->join("ubigeo dpto","e.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","e.idprovincia = prov.idprovincia AND prov.iddepartamento = e.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"e.iddistrito = dist.iddistrito AND dist.iddepartamento = e.iddepartamento AND dist.idprovincia = e.idprovincia", 'left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		// var_dump($datos['arrUbigeosSeleccionado']); exit(); 
		$this->db->where_in('dist.idubigeo', $datos['arrUbigeosSeleccionado']);
		//$this->db->where_in('dist.idubigeo', $datos['arrUbigeosSeleccionado']);
		// if($datos['empresa']){ 
		// 	$this->db->where("em.idempresa", $datos['empresa']['id']); 
		// }
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->order_by('idubigeo,empleado');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_reporte_profesion($datos)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado",FALSE);
		$this->db->select("(em.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.operador_movil, e.telefono, 
			e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, salario_basico,fecha_nacimiento, nombre_foto, e.estado_civil, 
			e.carnet_extranjeria, e.ruc_empleado, e.codigo_essalud, e.centro_essalud,e.grupo_sanguineo,fecha_caducidad_coleg, pr.idprofesion, descripcion_prf'); 
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where_in('pr.idprofesion', $datos['arrProfesionesSeleccionadas']); 
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->order_by('descripcion_prf,empleado');
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_reporte_especialidad($datos)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado",FALSE);
		$this->db->select("(em.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, esp.idespecialidad, (esp.nombre) AS especialidad, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.operador_movil, e.telefono, 
			e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, salario_basico,fecha_nacimiento, nombre_foto, e.estado_civil, 
			e.carnet_extranjeria, e.ruc_empleado, e.codigo_essalud, e.centro_essalud,e.grupo_sanguineo,fecha_caducidad_coleg, pr.idprofesion, descripcion_prf, 
			med.idmedico, med.reg_nac_especialista'); 
		$this->db->from('rh_empleado e');
		$this->db->join('medico med','e.idempleado = med.idempleado');
		$this->db->join('empresa_medico emme','med.idmedico = emme.idmedico');
		$this->db->join('especialidad esp','emme.idespecialidad = esp.idespecialidad');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where_in('esp.idespecialidad', $datos['arrEspecialidadesSeleccionadas']); 
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->order_by('especialidad,empleado');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_reporte_empresa_tercero($datos)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado",FALSE);
		$this->db->select("(em.descripcion) AS empresa, em.idempresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.operador_movil, e.telefono, 
			e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, salario_basico,fecha_nacimiento, nombre_foto, e.estado_civil, 
			e.carnet_extranjeria, e.ruc_empleado,e.grupo_sanguineo, pr.idprofesion, descripcion_prf'); 
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where_in('em.idempresa', $datos['arrEmpresasSeleccionadas']); 
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->order_by('empresa,empleado');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_reporte_tipo_contrato($datos)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado",FALSE);
		$this->db->select("(em.descripcion) AS empresa, em.idempresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, e.nombres, e.apellido_paterno, e.apellido_materno, e.operador_movil, e.telefono, 
			e.telefono_fijo, e.correo_electronico, e.direccion, e.referencia, salario_basico,fecha_nacimiento, nombre_foto, e.estado_civil, 
			e.carnet_extranjeria, e.ruc_empleado,e.grupo_sanguineo, pr.idprofesion, descripcion_prf, condicion_laboral'); 
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where('condicion_laboral IS NOT NULL');  
		$this->db->where_in('condicion_laboral', $datos['arrTipoContratosSeleccionadas']); 
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->order_by('condicion_laboral,empleado');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_reporte_rango_edad($datos,$rango=NULL)
	{
		$this->db->select($rango." AS rango_edad", FALSE); 
		$this->db->select("(e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno) AS empleado",FALSE);
		$this->db->select("(par.nombres || ' ' || par.apellido_paterno || ' ' || par.apellido_materno) AS hijo",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(par.fecha_nacimiento)) AS edad_hijo");
		$this->db->select("(em.descripcion) AS empresa, em.idempresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE);
		$this->db->select('e.idempleado, e.numero_documento, descripcion_prf, condicion_laboral, idpariente, par.fecha_nacimiento, par.vive'); 
		$this->db->from('rh_empleado e');
		$this->db->join('rh_pariente par','e.idempleado = par.idempleado');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo AND estado_ca = 1','left');
		$this->db->join('empresa em','e.idempresa = em.idempresa AND estado_em = 1','left');
		$this->db->join('sede s','e.idsedeempleado = s.idsede','left');
		$this->db->join('rh_profesion pr','e.idprofesion = pr.idprofesion','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where("par.parentesco = 'HIJO(A)'");  // solo hijos 
		if($datos['sede']){ 
			$this->db->where("e.idsedeempleado", $datos['sede']['id']); 
		}
		$this->db->where("DATE_PART('YEAR',AGE(par.fecha_nacimiento)) >=",$datos['edadInicio']);
		$this->db->where("DATE_PART('YEAR',AGE(par.fecha_nacimiento)) <",$datos['edadFin']);
		$this->db->order_by('condicion_laboral,empleado');
		return $this->db->get()->result_array();
	}
	/*
		AND DATE_PART('YEAR',AGE(fecha_nacimiento)) >= 1 
		AND DATE_PART('YEAR',AGE(fecha_nacimiento)) < 3; 
	*/
	public function m_cargar_avance_profesional_empleado($datos)
	{
		$this->db->select("(CASE WHEN tipo_ne = 1 THEN 'BASICO' WHEN tipo_ne = 2 THEN 'TECNICO' WHEN tipo_ne = 3 THEN 'SUPERIOR'  END) AS tipo_ne",FALSE);
		$this->db->select("(CASE WHEN (estudio_completo = 1) THEN 'COMPLETO' ELSE 'INCOMPLETO' END) AS estudio_completo",FALSE);
		$this->db->select("codigo_asistencia, numero_documento, nombre_foto, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, 
			especialidad, centro_estudio, fecha_desde, fecha_hasta, grado_academico,
			ne.idnivelestudio, descripcion_ne, orden");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->join('rh_detalle_estudio de','e.idempleado = de.idempleado');
		$this->db->join('rh_nivel_estudio ne','de.idnivelestudio = ne.idnivelestudio');
		$this->db->where('e.idempleado', $datos['id']);
		$this->db->where('estado_de', 1);
		$this->db->where('estado_ne', 1);
		$this->db->order_by('orden,fecha_desde', 'DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_empleado_por_codigo($datos)
	{
		$this->db->select("e.idempleado, (nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado, codigo_asistencia, numero_documento, nombre_foto, 
			(emp.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, e.fecha_nacimiento");
		$this->db->from('rh_empleado e');
		$this->db->join('empresa emp','e.idempresa = emp.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->where('idempleado', $datos['id']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_empleado_por_codigo_asistencia($codigoAsis)
	{
		$this->db->select("idempleado, (nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado, codigo_asistencia, nombre_foto");
		$this->db->from('rh_empleado');
		$this->db->where('codigo_asistencia', $codigoAsis);
		$this->db->where('estado_empl',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_empleado_salud_cbo($datos)
	{
		$this->db->select("m.idmedico, concat_ws(' ', m.med_apellido_paterno, m.med_apellido_materno, m.med_nombres) AS medico");
		$this->db->select(" 'NO' AS medico_externo");
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo c','e.idcargo = c.idcargo AND estado_ca = 1','left');
		$this->db->join('medico m','e.idempleado = m.idempleado','left');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		if( $datos ){ 
			$this->db->ilike("med_apellido_paterno || ' ' || med_apellido_materno || ' ' || med_nombres", strtolower($datos['search']));
		}
		$sqlMedico = $this->db->get_compiled_select();
		$this->db->reset_query();
		if( !empty($datos['habilita_externo']) ){ // si habilita medico externo hacemos una union con esa tabla.
			if( $datos['habilita_externo'] ){
				$this->db->select("m.idmedicoexterno AS idmedico, concat_ws(' ', m.apellido_paterno, m.apellido_materno, m.nombres) AS medico");
				$this->db->select(" 'SI' AS medico_externo ");
				$this->db->from('medico_externo m');
				$this->db->where('estado_mext', 1); // habilitado
				if( $datos ){ 
					$this->db->ilike("apellido_paterno || ' ' || apellido_materno || ' ' || nombres", strtolower($datos['search']));
				}
				$sqlMedicoExt = $this->db->get_compiled_select();
				$sqlMaster = $sqlMedico.' UNION ALL '.$sqlMedicoExt;
			}
		}else{
			$sqlMaster = $sqlMedico;
		}
		$sqlMaster.= ' LIMIT 10';
		$this->db->reset_query(); // var_dump($sqlMaster); exit(); 
		$query = $this->db->query($sqlMaster);
		return $query->result_array();

		// $this->db->limit(10);
		// return $this->db->get()->result_array();
	}
	public function m_cargar_empleado_salud_externo_cbo($datos)
	{
		$this->db->select("m.idmedicoexterno AS idmedico, concat_ws(' ', m.apellido_paterno, m.apellido_materno, m.nombres) AS medico");
		$this->db->from('medico_externo m');
		$this->db->where('estado_mext', 1); // habilitado
		if( $datos ){ 
			$this->db->ilike("apellido_paterno || ' ' || apellido_materno || ' ' || nombres", strtolower($datos['search']));
		}


		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_salud($paramPaginate,$paramDatos){ 
		$this->db->select('m.idmedico, e.idempleado, numero_documento, nombres, apellido_paterno, apellido_materno, telefono, 
			correo_electronico, direccion, fecha_nacimiento, nombre_foto, es_personal_salud, 
			c.idcargo, descripcion_ca, u.idusers, username, colegiatura_profesional, fecha_caducidad_coleg');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo c','e.idcargo = c.idcargo AND estado_ca = 1','left');
		$this->db->join('users u','e.iduser = u.idusers','left');
		$this->db->join('medico m','e.idempleado = m.idempleado','left');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		if( !empty($paramDatos['tercero']) ){
			if( !($paramDatos['tercero'] == 'all') ){
				$this->db->where('es_tercero', $paramDatos['tercero']);
			}
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
	public function m_count_empleados_salud($paramPaginate,$paramDatos)
	{
		$this->db->from('rh_empleado e');
		$this->db->join('rh_cargo c','e.idcargo = c.idcargo AND estado_ca = 1','left');
		$this->db->join('users u','e.iduser = u.idusers','left');
		$this->db->join('medico m','e.idempleado = m.idempleado','left');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		if( !empty($paramDatos['tercero']) ){
			if( !($paramDatos['tercero'] == 'all') ){
				$this->db->where('es_tercero', $paramDatos['tercero']);
			}
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_cargar_especialidades_del_medico($datos)
	{
		$this->db->select('emme.idempresamedico, m.idmedico, e.idempleado, esp.idespecialidad, (esp.nombre) AS especialidad, 
			emp.idempresa, (emp.descripcion) AS empresa, emes.idempresaespecialidad, estado_emme, me.idmedicoespecialidad');
		$this->db->select(' me.reg_nacional_esp, sac.idsituacionacademica, sac.descripcion_sac');
		// $this->db->select('s.idsede, (s.descripcion) AS sede');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');
		$this->db->join('pa_medico_especialidad me','m.idmedico = me.idmedico AND esp.idespecialidad = me.idespecialidad','left');
		$this->db->join('pa_situacion_academica sac','me.idsituacionacademica = sac.idsituacionacademica', 'left');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa');
		//$this->db->join('sede s','emp.idsede = s.idsede');
		$this->db->where('m.idmedico', $datos['datos']['id']); // habilitado
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_em', 1); // habilitado
		$this->db->where_in('estado_emme', array(1,2) ); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		if( isset($datos['search']) ){ 
			$this->db->ilike($datos['nameColumn'], $datos['search']);
		}else{ 
			$this->db->limit(100);
		}
		$this->db->order_by('esp.nombre'); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_cargar_especialidades_del_medico_combo_master($arrData)
	{
		$this->db->select('emme.idempresamedico, m.idmedico, e.idempleado, esp.idespecialidad, (esp.nombre) AS especialidad, 
			ead.idempresa AS id_empresa_admin, ead.descripcion AS empresa_admin, ead.aleas_empresa, ead.ruc_empresa AS ruc_empresa_admin, 
			ema.idempresa, ema.descripcion AS empresa, ema.es_empresa_admin, (ema.ruc_empresa) AS ruc_tercero, 
			ee.idempresaespecialidad, emme.estado_emme');
		$this->db->select(' me.reg_nacional_esp, sac.idsituacionacademica, sac.descripcion_sac');
		// $this->db->select('s.idsede, (s.descripcion) AS sede');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico AND emme.estado_emme = 1');
		//$this->db->join('empresa_especialidad ee','emme.idempresaespecialidad = ee.idempresaespecialidad');

		$this->db->join('empresa_especialidad ee','emme.idempresaespecialidad = ee.idempresaespecialidad 
			AND emme.idempresa = ee.idempresa AND emme.idespecialidad = ee.idespecialidad AND ee.estado_emes = 1'); 
		$this->db->join('pa_empresa_detalle ed','ee.idempresadetalle = ed.idempresadetalle AND ed.estado_ed = 1'); 
		$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa AND ema.estado_em = 1'); // EMPRESA EMA 
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa AND ead.estado_em = 1'); // NUEVA EMPRESA ADMIN 

		$this->db->join('especialidad esp','ee.idespecialidad = esp.idespecialidad');
		$this->db->join('pa_medico_especialidad me','m.idmedico = me.idmedico AND esp.idespecialidad = me.idespecialidad','left');
		$this->db->join('pa_situacion_academica sac','me.idsituacionacademica = sac.idsituacionacademica', 'left');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa');
		//$this->db->join('sede s','emp.idsede = s.idsede');
		$this->db->where('m.idmedico', $arrData['idmedico']); // habilitado 
		// if( $arrData ){
		$this->db->where('ead.idempresa', $arrData['id_empresa_admin']); // habilitado 
		// }
		
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where_in('estado_emes', array(1,2)); // empresa_especialidad 1:activo; 2:deshabilitado 
		$this->db->where('estado_emme', 1 ); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud 
		$this->db->order_by('esp.nombre'); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_de_empresa_especialidad_cbo($datos, $search = FALSE)
	{
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->select('emme.idempresamedico, m.idmedico, e.idempleado, esp.idespecialidad, (esp.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad'); 
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad'); 
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa'); 
		//$this->db->join('sede s','emp.idsede = s.idsede'); 
		
		$this->db->where('emes.idempresaespecialidad', $datos['id']); 
		// $this->db->where('m.idmedico', $datos['datos']['id']); // habilitado
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_em', 1); // habilitado
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		// $this->db->order_by('esp.nombre', 1); // especialidad
		if( $search ){ 
			$this->db->ilike("concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno)", strtolower($search));
			$this->db->limit(10);
		}
		$this->db->order_by('m.med_nombres','ASC');
		$this->db->order_by('m.med_apellido_paterno','ASC');
		return $this->db->get()->result_array();
	}
	
	public function m_cargar_medicos_de_empresa_especialidad($datos)
	{
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->select('emme.idempresamedico, m.idmedico, e.idempleado, m.colegiatura_profesional, esp.idespecialidad, (esp.nombre) AS especialidad, reg_nacional_esp AS rne, me.idsituacionacademica, estado_emme, me.idmedicoespecialidad');
		$this->db->select('e.numero_documento');
		$this->db->select('cps.idcategoriapersonalsalud, cps.descripcion_cps AS descripcion_cps ');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad', 'left'); 
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad'); 
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa'); 
		$this->db->join('pa_medico_especialidad me','m.idmedico = me.idmedico AND esp.idespecialidad = me.idespecialidad','left');
		$this->db->join('pa_situacion_academica sac','me.idsituacionacademica = sac.idsituacionacademica', 'left');
		$this->db->join('pa_categoria_personal_salud cps','m.idcategoriapersonalsalud = cps.idcategoriapersonalsalud', 'left'); 

		if(!empty($datos['idempresaespecialidad'])){
			$this->db->where('emes.idempresaespecialidad', $datos['idempresaespecialidad']);
		}
		 
		// $this->db->where('m.idmedico', $datos['datos']['id']); // habilitado
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes <>', 0); // empresa_especialidad
		$this->db->where('estado_em', 1); // habilitado
		$this->db->where_in('estado_emme', array(1,2) );
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		$this->db->order_by('medico', 'ASC'); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_count_medicos_de_empresa_especialidad($datos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad', 'left'); 
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad'); 
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa'); 
		$this->db->join('pa_categoria_personal_salud cps','m.idcategoriapersonalsalud = cps.idcategoriapersonalsalud', 'left'); 
		// $this->db->join('sede s','emp.idsede = s.idsede'); 
		$this->db->where('emes.idempresaespecialidad', $datos['idempresaespecialidad']); 
		// $this->db->where('m.idmedico', $datos['datos']['id']); // habilitado
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes <>', 0); // empresa_especialidad
		$this->db->where('estado_em', 1); // habilitado
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_medico_no_agreg_empresa_autocomplete($datos)
	{
		// SUBCONSULTA 
		$this->db->select('c_emme.idmedico');
		$this->db->from('empresa_medico c_emme');
		$this->db->where('c_emme.idempresaespecialidad',$datos['idempresaespecialidad']);
		$this->db->where_in('c_emme.estado_emme', array(1,2));
		$medicos_agregados = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->from('medico m');
		$this->db->join('rh_empleado e','m.idempleado = e.idempleado');
		$this->db->where('m.idmedico NOT IN (' . $medicos_agregados . ')');
		$this->db->where('e.estado_empl', 1);
		$this->db->ilike("concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno)", $datos['search']);
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_de_especialidad($datos)
	{
		$this->db->distinct(); 
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico", FALSE);
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');
		$this->db->where('esp.idespecialidad', $datos['id']); // habilitado
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		//$this->db->where('estado_em', 1); // habilitado
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		$this->db->order_by('medico'); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_atencion_todos_autocomplete($datos)
	{
		$this->db->distinct(); 
		$this->db->select("m.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres) AS medico", FALSE);
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		$this->db->ilike("med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres", strtolower($datos['search']));
		// $this->db->order_by('medico');
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_esta_especialidad_medico($datos) 
	{
		$this->db->select('emme.idempresamedico, m.idmedico, e.idempleado, esp.idespecialidad, (esp.nombre) AS especialidad, s.idsede, (s.descripcion) AS sede, emp.idempresa, (emp.descripcion) AS empresa');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		$this->db->join('sede s','emp.idsede = s.idsede');
		$this->db->where('emme.idempresamedico', $datos['id']); // empresa_medico
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_em', 1); // habilitado
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_empleados_colegiatura_vencimiento()
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado", FALSE);
		$this->db->select('m.idmedico, e.idempleado, m.colegiatura_profesional, e.fecha_caducidad_coleg');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->where('e.fecha_caducidad_coleg <', date('Y-m-d',strtotime("+30days")));
		$this->db->where('e.estado_empl', 1); // empleado
		$this->db->where('e.es_personal_salud', 1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_contrato_vencimiento()
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado", FALSE);
		$this->db->select('e.idempleado, hc.fecha_fin_contrato, hc.idempresaadmin');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_historial_contrato hc','e.idempleado = hc.idempleado'); 
		$this->db->join('rh_cargo ca','hc.idcargo = ca.idcargo'); 
		$this->db->join('empresa_admin ea','hc.idempresaadmin = ea.idempresaadmin');
		$this->db->where('hc.fecha_fin_contrato <', date('Y-m-d',strtotime("+30days")));
		$this->db->where('hc.estado_hc <>', 0); 
		$this->db->where('ca.estado_ca <>', 0); 
		$this->db->where('ea.estado_emp <>', 0); // empresa_admin 
		$this->db->where('e.estado_empl <>', 0); // empleado
		$this->db->where('hc.contrato_actual', 1);
		return $this->db->get()->result_array();
	}

	public function m_consultar_empleado($idempleado, $salud = FALSE)
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado", FALSE);
		$this->db->select('m.idmedico, e.idempleado, m.colegiatura_profesional, e.fecha_caducidad_coleg, e.fecha_fin_contrato');
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado');
		$this->db->where('e.idempleado', $idempleado);
		$this->db->where('e.estado_empl', 1); // empleado
		
		if(!empty($salud) && $salud == 'si'){	
			$this->db->where('e.es_personal_salud', 1);
			$this->db->where('e.fecha_caducidad_coleg <', date('Y-m-d',strtotime("+30days")));
		}else{
			$this->db->where('e.fecha_fin_contrato <', date('Y-m-d',strtotime("+30days")));
		}
		return $this->db->get()->row_array();
	}

	public function m_cargar_especialidades_no_agregados_a_medico($paramPaginate,$datos)
	{
		// SUBCONSULTA
		$this->db->select('c_emes.idempresaespecialidad');
		$this->db->from('empresa_especialidad c_emes');
		$this->db->join('empresa_medico c_emme','c_emes.idempresaespecialidad = c_emme.idempresaespecialidad');
		$this->db->where('c_emme.idmedico', $datos['idmedico']);
		$this->db->where('c_emes.estado_emes', 1);
		$this->db->where_in('c_emme.estado_emme', array(1,2));
		$especialidades_agregadas = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, em.idempresa, (em.descripcion) AS empresa');
		$this->db->select('ed.idempresadetalle');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad AND estado_emes = 1');
		$this->db->join('pa_empresa_detalle ed','emes.idempresa = ed.idempresatercera AND estado_ed = 1');
		$this->db->join('empresa em','ed.idempresatercera = em.idempresa AND estado_em = 1');
		$this->db->where('emes.idempresaespecialidad NOT IN(' . $especialidades_agregadas . ')');
		$this->db->where('esp.estado', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		//$this->db->group_by('emes.idempresaespecialidad, esp.idespecialidad, em.idempresa,em.descripcion');
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
		/*
		$sql = 'SELECT emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, s.idsede, (s.descripcion) AS sede, em.idempresa, (em.descripcion) AS empresa
		FROM especialidad esp 
		INNER JOIN empresa_especialidad emes ON esp.idespecialidad = emes.idespecialidad AND estado_emes = 1 
		INNER JOIN empresa em ON emes.idempresa = em.idempresa AND emes.idsede = em.idsede AND estado_em = 1 
		INNER JOIN sede s ON em.idsede = s.idsede AND estado_se = 1 
		LEFT JOIN empresa_medico emme ON emes.idempresaespecialidad = emme.idempresaespecialidad AND estado_emme = 1 AND emme.idmedico = ? 
		WHERE emes.idempresaespecialidad NOT IN( 
			SELECT c_emes.idempresaespecialidad 
			FROM empresa_especialidad c_emes 
			INNER JOIN empresa_medico c_emme ON c_emes.idempresaespecialidad = c_emme.idempresaespecialidad 
			WHERE c_emme.idmedico = ? AND c_emes.estado_emes = 1 AND c_emme.estado_emme = 1 
		)
		AND esp.estado = 1'; 
		if( $paramPaginate['search'] ){ 
			$sql.= " AND " .$paramPaginate['searchColumn']." ILIKE '%".strtolower($paramPaginate['searchText'])."%' ESCAPE '!'";
		}
		$sql .= ' GROUP BY emes.idempresaespecialidad, esp.idespecialidad,s.idsede,s.descripcion,em.idempresa,em.descripcion';
		if( $paramPaginate['sortName'] ){
			$sql.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			$sql.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		$query = $this->db->query($sql,array($datos['idmedico'],$datos['idmedico']));
		return $query->result_array();*/
	}
	public function m_count_especialidades_no_agregados_a_medico($paramPaginate,$datos)
	{
		// SUBCONSULTA
		$this->db->select('c_emes.idempresaespecialidad');
		$this->db->from('empresa_especialidad c_emes');
		$this->db->join('empresa_medico c_emme','c_emes.idempresaespecialidad = c_emme.idempresaespecialidad');
		$this->db->where('c_emme.idmedico', $datos['idmedico']);
		$this->db->where('c_emes.estado_emes', 1);
		$this->db->where_in('c_emme.estado_emme', array(1,2));
		$especialidades_agregadas = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad AND estado_emes = 1');
		$this->db->join('empresa em','emes.idempresa = em.idempresa AND estado_em = 1');
		$this->db->where('emes.idempresaespecialidad NOT IN(' . $especialidades_agregadas . ')');
		$this->db->where('esp.estado', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];

		/*
		$sql = 'SELECT COUNT(*) AS contador 
		FROM especialidad esp 
		INNER JOIN empresa_especialidad emes ON esp.idespecialidad = emes.idespecialidad AND estado_emes = 1 
		INNER JOIN empresa em ON emes.idempresa = em.idempresa AND emes.idsede = em.idsede AND estado_em = 1 
		INNER JOIN sede s ON em.idsede = s.idsede AND estado_se = 1 
		LEFT JOIN empresa_medico emme ON emes.idempresaespecialidad = emme.idempresaespecialidad AND estado_emme = 1 AND emme.idmedico = ? 
		WHERE emes.idempresaespecialidad NOT IN( 
			SELECT c_emes.idempresaespecialidad 
			FROM empresa_especialidad c_emes 
			INNER JOIN empresa_medico c_emme ON c_emes.idempresaespecialidad = c_emme.idempresaespecialidad 
			WHERE c_emme.idmedico = ? AND c_emes.estado_emes = 1 AND c_emme.estado_emme = 1 
		)
		AND esp.estado = 1'; 
		$query = $this->db->query($sql,array($datos['idmedico'],$datos['idmedico']));
		$fEmpresa = $query->row_array();
		return $fEmpresa['contador'];*/
	}
	public function m_editar($datos)
	{
		$data = array(			
			'idtipodocumentorh' => $datos['tipo_documento']['id'],
			'numero_documento' => $datos['num_documento'],
			'codigo_asistencia' => $datos['num_documento'],
			'nombres' => strtoupper($datos['nombre']),
			'apellido_paterno' => strtoupper($datos['apellido_paterno']),
			'apellido_materno' => strtoupper($datos['apellido_materno']),
			'telefono' => $datos['telefono'],
			'operador_movil' => $datos['operador_movil'],
			'telefono_fijo' => $datos['telefono_fijo'],
			'sexo' => $datos['sexo'],
			'correo_electronico' => $datos['email'],
			'direccion' => $datos['direccion'],
			'fecha_nacimiento' => empty($datos['fecha_nacimiento']) || $datos['fecha_nacimiento'] == 'null' ? NULL:$datos['fecha_nacimiento'],
			'nombre_foto' => $datos['nombre_foto'],
			'idcargo' => $datos['idcargo'],
			'iduser' => $datos['idusuario'],
			'es_personal_salud' => $datos['personalSalud'],
			'es_personal_farmacia' => $datos['personalFarmacia'],
			'es_personal_administrativo' => $datos['personalAdministrativo'],
			'idalmacenfarmacia' => @$datos['idalmacenfarmacia'],
			'idsubalmacenfarmacia' => @$datos['idsubalmacenfarmacia'],
			'idempresa' => empty($datos['idempresa']) || $datos['idempresa'] == 'null' ? NULL:$datos['idempresa'],
			'idsedeempleado' => empty($datos['idsedeempleado']) || $datos['idsedeempleado'] == 'null' ? NULL:$datos['idsedeempleado'],
			'idespecialidad' => empty($datos['idespecialidad']) || $datos['idespecialidad'] == 'null' ? NULL:$datos['idespecialidad'],
			'idareaempresa' => empty($datos['area_empresa']['id']) || $datos['area_empresa']['id'] == 'all' ? NULL : $datos['area_empresa']['id'],
			'es_tercero' => $datos['es_tercero'],
			'marca_asistencia' => $datos['marca_asistencia'],
			'es_ipress' => $datos['es_ipress'],
			'es_privado' => $datos['es_privado'],
			'codigo_essalud' => @$datos['codigo_essalud'],
			'carnet_extranjeria' => @$datos['carnet_extranjeria'],
			'referencia' => @$datos['referencia'],
			'estado_civil' => @$datos['estado_civil'],
			'grupo_sanguineo' => @$datos['grupo_sanguineo'],
			'ruc_empleado' => @$datos['ruc_empleado'],
			'centro_essalud' => @$datos['centro_essalud'],
			'nombres_cy' => @$datos['nombres_cy'],
			'apellido_paterno_cy' => @$datos['apellido_paterno_cy'],
			'apellido_materno_cy' => @$datos['apellido_materno_cy'],
			'fecha_nacimiento_cy' => empty($datos['fecha_nacimiento_cy']) ? NULL : $datos['fecha_nacimiento_cy'],
			'lugar_labores_cy' => @$datos['lugar_labores_cy'],
			'reg_pensionario' => ($datos['reg_pensionario'] == 'NONE' ? NULL:$datos['reg_pensionario'] ),
			'idafp' => empty($datos['afp']['id']) || $datos['afp']['id'] == 'all' ? NULL : $datos['afp']['id'],
			'tipo_comision' => empty($datos['comision_afp']['id']) || $datos['comision_afp']['id'] == 'NONE' ? NULL : $datos['comision_afp']['id'],
			'condicion_laboral' => @$datos['condicion_laboral'],
			'fecha_ingreso' => @$datos['fecha_ingreso'],
			'fecha_inicio_contrato' => empty($datos['fecha_inicio_contrato']) ? NULL : $datos['fecha_inicio_contrato'], //@$datos['fecha_inicio_contrato'], 
			'fecha_fin_contrato' => empty($datos['fecha_fin_contrato']) ? NULL : $datos['fecha_fin_contrato'], //@$datos['fecha_fin_contrato'],
			'cuspp' => @$datos['cuspp'],
			'fecha_afiliacion' => @$datos['fecha_afiliacion'],
			'documento_afiliacion' => @$datos['documento_afiliacion'],
			'fecha_caducidad_coleg' => @$datos['fecha_caducidad_coleg'],
			'colegiatura_profesional_emp' => empty($datos['colegiatura_profesional_emp']) || $datos['colegiatura_profesional_emp'] == 'null' ? NULL:$datos['colegiatura_profesional_emp'], // solo empleados no medicos 
			'iddepartamento' => @$datos['iddepartamento'],
			'idprovincia' => @$datos['idprovincia'],
			'iddistrito' => @$datos['iddistrito'],
			'idprofesion' => $datos['idprofesion'],
			'salario_basico' => @$datos['salario_basico'],
			'idempleadojefe' => @$datos['idempleadojefe'],
			'idcargosuperior' => @$datos['idcargosup'],
			'idcentrocosto' => empty($datos['idcentrocosto'])? NULL : $datos['idcentrocosto'],
			'idbanco' => empty($datos['banco']['id']) || $datos['banco']['id'] == '' ? NULL : $datos['banco']['id'],
			'cuenta_corriente' => empty($datos['cuenta_corriente'])? NULL : $datos['cuenta_corriente']
		);
		$this->db->where('idempleado',$datos['id']);
		return $this->db->update('rh_empleado', $data);
	}
	public function m_registrar($datos) // empleado 
	{
		$data = array(
			'idtipodocumentorh' => $datos['tipo_documento']['id'],
			'numero_documento' => $datos['num_documento'],
			'codigo_asistencia' => $datos['num_documento'],
			'nombres' => strtoupper($datos['nombre']),
			'apellido_paterno' => strtoupper($datos['apellido_paterno']),
			'apellido_materno' => strtoupper($datos['apellido_materno']),
			'telefono' => $datos['telefono'],
			'telefono_fijo' => $datos['telefono_fijo'],
			'sexo' => $datos['sexo'],
			'correo_electronico' => $datos['email'],
			'direccion' => $datos['direccion'],
			'fecha_nacimiento' => empty($datos['fecha_nacimiento']) || $datos['fecha_nacimiento'] == 'null' ? NULL:$datos['fecha_nacimiento'],
			'nombre_foto' => $datos['nombre_foto'],
			'idcargo' => $datos['idcargo'],
			'iduser' => $datos['idusuario'],
			'es_personal_salud' => $datos['personalSalud'],
			'es_personal_farmacia' => $datos['personalFarmacia'],
			'es_personal_administrativo' => $datos['personalAdministrativo'],
			'idalmacenfarmacia' => @$datos['idalmacenfarmacia'],
			'idsubalmacenfarmacia' => @$datos['idsubalmacenfarmacia'],
			'idempresa' => empty($datos['idempresa']) || $datos['idempresa'] == 'null' ? NULL:$datos['idempresa'],
			'idsedeempleado' => empty($datos['idsedeempleado']) || $datos['idsedeempleado'] == 'null' ? NULL:$datos['idsedeempleado'],
			'idespecialidad' => empty($datos['idespecialidad']) || $datos['idespecialidad'] == 'null' ? NULL:$datos['idespecialidad'],
			'es_tercero' => $datos['es_tercero'],
			'marca_asistencia' => $datos['marca_asistencia'],
			'es_ipress' => $datos['es_ipress'],
			'es_privado' => $datos['es_privado'],
			'codigo_essalud' => $datos['codigo_essalud'],
			'carnet_extranjeria' => $datos['carnet_extranjeria'],
			'referencia' => $datos['referencia'],
			'estado_civil' => $datos['estado_civil'],
			'grupo_sanguineo' => $datos['grupo_sanguineo'],
			'ruc_empleado' => $datos['ruc_empleado'],
			'centro_essalud' => $datos['centro_essalud'],
			'nombres_cy' => $datos['nombres_cy'],
			'apellido_paterno_cy' => $datos['apellido_paterno_cy'],
			'apellido_materno_cy' => $datos['apellido_materno_cy'],
			'fecha_nacimiento_cy' => $datos['fecha_nacimiento_cy'],
			'lugar_labores_cy' => $datos['lugar_labores_cy'],
			'reg_pensionario' => ($datos['reg_pensionario'] == 'NONE' ? NULL:$datos['reg_pensionario'] ),
			'idafp' => empty($datos['afp']['id']) || $datos['afp']['id'] == 'all' ? NULL : $datos['afp']['id'],
			'tipo_comision' => empty($datos['comision_afp']['id']) || $datos['comision_afp']['id'] == 'NONE' ? NULL : $datos['comision_afp']['id'],
			'condicion_laboral' => empty($datos['condicion_laboral']) || $datos['condicion_laboral'] == 'NONE' ? NULL : $datos['condicion_laboral'],
			'idareaempresa' => empty($datos['area_empresa']['id']) || $datos['area_empresa']['id'] == 'all' ? NULL : $datos['area_empresa']['id'],
			'fecha_ingreso' => $datos['fecha_ingreso'],
			'fecha_inicio_contrato' => $datos['fecha_inicio_contrato'],
			'fecha_fin_contrato' => $datos['fecha_fin_contrato'],
			'cuspp' => $datos['cuspp'],
			'fecha_afiliacion' => $datos['fecha_afiliacion'],
			'documento_afiliacion' => $datos['documento_afiliacion'],
			'fecha_caducidad_coleg' => @$datos['fecha_caducidad_coleg'],
			'colegiatura_profesional_emp' => empty($datos['colegiatura_profesional_emp']) || $datos['colegiatura_profesional_emp'] == 'null' ? NULL:$datos['colegiatura_profesional_emp'], // solo empleados no medicos 
			'iddepartamento' => @$datos['iddepartamento'],
			'idprovincia' => @$datos['idprovincia'],
			'iddistrito' => @$datos['iddistrito'],
			'idprofesion' => empty($datos['idprofesion']) || $datos['idprofesion'] == 'null' ? NULL:$datos['idprofesion'],
			'salario_basico' => empty($datos['salario_basico']) || $datos['salario_basico'] == 'null' ? NULL:$datos['salario_basico'],
			'idempleadojefe' => empty($datos['idempleadojefe']) || $datos['idempleadojefe'] == 'null' ? NULL:$datos['idempleadojefe'],
			'idcargosuperior' => empty($datos['idcargosup']) || $datos['idcargosup'] == 'null' ? NULL:$datos['idcargosup'],
			'idcentrocosto' => empty($datos['idcentrocosto'])? NULL : $datos['idcentrocosto'],
			'idbanco' => empty($datos['banco']['id']) || $datos['banco']['id'] == '' ? NULL : $datos['banco']['id'],
			'cuenta_corriente' => empty($datos['cuenta_corriente'])? NULL : $datos['cuenta_corriente']
		);
		return $this->db->insert('rh_empleado', $data);
	}
	public function m_registrar_medico($datos)
	{
		$data = array(
			'med_nombres' => $datos['nombre'],
			'med_apellido_paterno' => $datos['apellido_paterno'],
			'med_apellido_materno' => $datos['apellido_materno'],
			'med_numero_documento' => $datos['num_documento'],
			'colegiatura_profesional' => $datos['colegiatura_profesional'],			
			//'reg_nac_especialista' => $datos['registro_nacional_especialista'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'idempleado' => $datos['idempleado'],
			'iduser' => $datos['idusuario'],
			'idcategoriapersonalsalud' => empty($datos['idcategoriapersonalsalud']) ? NULL : $datos['idcategoriapersonalsalud'],
		);
		return $this->db->insert('medico', $data);
	}
	public function m_editar_medico($datos)
	{
		$data = array(
			'med_nombres' => $datos['nombre'],
			'med_apellido_paterno' => $datos['apellido_paterno'],
			'med_apellido_materno' => $datos['apellido_materno'],
			'med_numero_documento' => $datos['num_documento'],
			'colegiatura_profesional' => $datos['colegiatura_profesional'],
			//'reg_nac_especialista' => $datos['registro_nacional_especialista'],
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser' => $datos['idusuario'],
			'idcategoriapersonalsalud' => empty($datos['idcategoriapersonalsalud']) ? NULL : $datos['idcategoriapersonalsalud'],
		);
		$this->db->where('idempleado',$datos['id']);
		return $this->db->update('medico', $data);
	}
	public function m_agregar_especialidad_medico($datos)
	{
		$data = array(
			'idmedico' => $datos['idmedico'],
			'idempresa' => $datos['idempresa'],
			'idespecialidad' => $datos['idespecialidad'],
			'idempresaespecialidad' => $datos['id'],
			//'idsede' => $datos['idsede'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser_asigna' => $this->sessionHospital['idusers'],
		);
		return $this->db->insert('empresa_medico', $data);
	}
	public function m_agregar_situacion_rne_especialidad($datos)
	{
		$data = array(
			'idmedico' => $datos['idmedico'],
			'idespecialidad' => $datos['idespecialidad'],
			'reg_nacional_esp' => empty($datos['rne'])? NULL : $datos['rne'],
			'idsituacionacademica' => empty($datos['situacion'])? NULL : $datos['situacion'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('pa_medico_especialidad', $data);
	}
	public function m_verificar_rne_medico_esp($datos)
	{
		$this->db->select("reg_nacional_esp", FALSE);
		$this->db->from('pa_medico_especialidad');
		$this->db->where('reg_nacional_esp', $datos['rne']);
		$this->db->where('idmedicoespecialidad <>', $datos['idmedicoespecialidad']);
		// $this->db->where('idmedico <>', $datos['idmedico']);
		// $this->db->where('idespecialidad <>', $datos['idespecialidad']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_editar_situacion_rne_especialidad($datos)
	{
		$data = array(
			'reg_nacional_esp' => empty($datos['rne'])? NULL : $datos['rne'],
			'idsituacionacademica' => empty($datos['situacion'])? NULL : $datos['situacion'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedicoespecialidad', $datos['idmedicoespecialidad']);
		return $this->db->update('pa_medico_especialidad', $data);
	}
	public function m_quitar_especialidad_medico($id)
	{
		$data = array(
			'estado_emme' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresamedico',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_dar_baja($id)
	{
		$data = array(
			'si_activo' => 2,
			
		);
		$this->db->where('idempleado',$id);
		if($this->db->update('rh_empleado', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_revertir_baja($id)
	{
		$data = array(
			'si_activo' => 1,
			
		);
		$this->db->where('idempleado',$id);
		if($this->db->update('rh_empleado', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_empl' => 0,
			'codigo_asistencia' => NULL,
			'iduser' => NULL
		);
		$this->db->where('idempleado',$id);
		if($this->db->update('rh_empleado', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_empl' => 1,
		);
		$this->db->where('idempleado',$id);
		if($this->db->update('rh_empleado', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular_especialidad_medico($id)
	{
		$data = array(
			'estado_emme' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresamedico',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar_especialidad_medico($id)
	{
		$data = array(
			'estado_emme' => 1,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresamedico',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar_especialidad_medico($id)
	{
		$data = array(
			'estado_emme' => 2,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresamedico',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_get_empresa_especialidad($id_empresa_especialidad)
	{
		
		$this->db->from('empresa_especialidad');
		
		$this->db->where('idempresaespecialidad',$id_empresa_especialidad);
		$this->db->where('estado_emes', 1); // empresa_especialidad 
		
		return $this->db->get()->row_array();
	}
	public function m_verificar_si_existe_empleado_por_numero_documento($num_documento)
	{
		$this->db->from('rh_empleado');
		$this->db->where('numero_documento', $num_documento);
		$this->db->where('estado_empl', 1); // que esté habilitado
		
		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_verificar_si_existe_otro_empleado_por_numero_documento($datos)
	{
		$this->db->from('rh_empleado');
		$this->db->where('numero_documento', $datos['num_documento']);
		$this->db->where('idempleado <>'. $datos['idempleado']);
		$this->db->where('estado_empl', 1); // que esté habilitado
		
		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_verificar_si_tiene_asistencia_por_id($id){
		$this->db->from('rh_asistencia');
		$this->db->where('idempleado', $id);
		$this->db->where('estado_as', 1); // que esté habilitado
		
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_si_tiene_atencion_medica_idmedico($id){
		$this->db->from('atencion_medica');
		$this->db->where('idmedico', $id);
		$this->db->where('estado_am', 1); // que esté habilitado
		
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_si_tiene_atencion_medica_por_especialidad($datos){
		$this->db->from('atencion_medica');
		$this->db->where('idmedico', $datos['idmedico']);
		$this->db->where('idempresa', $datos['idempresa']);
		$this->db->where('idespecialidad', $datos['idespecialidad']);
		$this->db->where('estado_am', 1); // que esté habilitado
		
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_especialidad_medico($datos){
		$this->db->select('idmedicoespecialidad');
		$this->db->from('pa_medico_especialidad');
		$this->db->where('idmedico', $datos['idmedico']);
		$this->db->where('idespecialidad', $datos['idespecialidad']);
		$this->db->limit(1);
		$fData = $this->db->get()->row_array();
		return $fData['idmedicoespecialidad'];
	}
	public function m_verificar_empresa_especialidad_medico($datos){
		$this->db->from('empresa_medico em, empresa_especialidad ee');
		$this->db->where('em.idmedico', $datos['idmedico']);
		$this->db->where('em.idespecialidad', $datos['idespecialidad']);		
		$this->db->where('em.idempresa', $datos['idempresa']);
		$this->db->where_in('estado_emme', array(1,2));
		$this->db->where('em.idempresaespecialidad =  ee.idempresaespecialidad');
		$this->db->where('ee.idempresadetalle', $datos['idempresadetalle']);
		
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_verificar_empresa_especialidad_medico_anulado($datos){
		$this->db->from('empresa_medico');
		$this->db->where('idempresamedico', $datos['id']);
		$this->db->where('estado_emme', 0);
		
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true;
		else
			return false;
	}
	public function m_quitar_num_documento_medico($numDocumento) 
	{ 
		$data = array( 
			'med_numero_documento' => NULL,
			'updatedAt'=> date('Y-m-d') 
		);
		$this->db->where('med_numero_documento',$numDocumento);
		if($this->db->update('medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	// VACACIONES 
	public function m_cargar_vacaciones_empleado($idEmpleado)
	{
		$this->db->select('mh.idmotivohe, mh.descripcion_mh, smh.descripcion_smh, he.idhorarioespecial, fecha_especial, si_licencia, 
			e.idempleado, nombres, apellido_paterno, apellido_materno');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_especial he','e.idempleado = he.idempleado');
		$this->db->join('rh_motivo_he mh','he.idmotivohe = mh.idmotivohe');
		$this->db->join('rh_submotivo_he smh','he.idsubmotivohe = smh.idsubmotivohe AND smh.estado_smh = 1', 'left');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_hesp', 1); // habilitado
		// $this->db->where('estado_mh', 1); // habilitado
		$this->db->where('e.idempleado', $idEmpleado);
		$this->db->where('mh.idmotivohe', 8); // vacaciones
		$this->db->order_by('fecha_especial','ASC');
		return $this->db->get()->result_array();
	}


	public function m_cargar_medicos_especialidad_autocomplete($datos){
		//$this->db->distinct(); 
		$this->db->select("m.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres ) AS medico", FALSE);
		$this->db->select("esp.idespecialidad, esp.nombre AS especialidad", FALSE); //especialidad		
		$this->db->select("em.idempresa, em.descripcion AS empresa_admin", FALSE); //empresa admin
		$this->db->select("ed.idempresatercera, (SELECT d.descripcion from empresa d where d.idempresa = ed.idempresatercera ) AS empresa", FALSE); //empresa EMA
		$this->db->select("emme.idempresamedico, em.ruc_empresa, e.correo_electronico", FALSE); //empresa_medico
		$this->db->select("(select seesp.tiene_prog_cita from pa_sede_especialidad seesp where esp.idespecialidad = seesp.idespecialidad AND idsede = ". $this->sessionHospital['idsede'] . ") as tiene_prog_cita "); //tiene_prog_cita 
		$this->db->select("(select seesp.tiene_prog_proc from pa_sede_especialidad seesp where esp.idespecialidad = seesp.idespecialidad AND idsede = ". $this->sessionHospital['idsede'] . ") as tiene_prog_proc "); //tiene_prog_proc 
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado'); //medico
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico'); //empresa_medico
		if(!empty($datos['esReprogramacion'])){ 
			//para reprogramar debe ser médico misma especialidad
			$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad AND emes.idespecialidad = ' . $datos['idespecialidad']); 
		}else{
			$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		}
		
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');	

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');//empresa detalle
		$this->db->join('empresa em',"ed.idempresaadmin = em.idempresa AND em.ruc_empresa = '" . $datos['ruc'] . "'");//empresa  and ruc empresa admin	
		$this->db->where("em.es_empresa_admin ", 1);
		//$this->db->where("  " ,  );
		$this->db->where('emme.idempresa = ed.idempresatercera'); 
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado idsede 
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('ed.estado_ed', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		//$this->db->where("emme.idsede", $datos['idsede']); //misma sede

		$this->db->where("(med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres LIKE '%" .strtoupper_total($datos['search']). "%' ESCAPE '!'
							OR med_nombres LIKE '%" .strtoupper_total($datos['search']). "%' ESCAPE '!'
							OR med_apellido_materno LIKE '%" .strtoupper_total($datos['search']). "%' ESCAPE '!'
							OR med_nombres || ' ' || med_apellido_paterno || ' ' || med_apellido_materno LIKE '%" .strtoupper_total($datos['search']). "%' ESCAPE '!'
							OR med_apellido_paterno || ' ' || med_apellido_materno || ' ' || med_nombres LIKE '%" .strtoupper_total($datos['search']). "%' ESCAPE '!')");
		
		/*
		$this->db->ilike("med_apellido_paterno  || ', ' || med_nombres", strtoupper($datos['search']));
		$this->db->or_like("med_apellido_materno",strtoupper($datos['search']));		
		$this->db->or_like("med_nombres || ' ' ||med_apellido_paterno || ' ' ||med_apellido_materno",strtoupper($datos['search']));
		$this->db->or_like("med_apellido_paterno || ' ' ||med_apellido_materno || ' ' ||med_nombres",strtoupper($datos['search']));*/
		// $this->db->order_by('medico');
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}


	public function m_cargar_medicos_filtro_autocomplete($datos){
		$this->db->distinct(); 
		$this->db->select("m.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres ) AS medico", FALSE);
		
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado'); //medico
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico'); //empresa_medico
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');		
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');	
		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');//empresa detalle
		$this->db->join('empresa em',"ed.idempresaadmin = em.idempresa AND em.ruc_empresa = '" . $datos['ruc'] . "'");//empresa  and ruc empresa admin	
		$this->db->where("em.es_empresa_admin ", 1);
		//$this->db->where("  " ,  );
		$this->db->where('emme.idempresa = ed.idempresatercera'); 
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('ed.estado_ed', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		//$this->db->where("emme.idsede", $datos['idsede']); //misma sede

		$this->db->ilike("med_apellido_paterno  || ' ' || med_apellido_paterno || ', ' || med_nombres", strtoupper($datos['search']));		
		$this->db->or_like("med_nombres",strtoupper($datos['search']));
		$this->db->or_like("med_apellido_materno",strtoupper($datos['search']));
		$this->db->or_like("med_nombres || ' ' ||med_apellido_paterno || ' ' ||med_apellido_materno",strtoupper($datos['search']));
		$this->db->or_like("med_apellido_paterno || ' ' ||med_apellido_materno || ' ' ||med_nombres",strtoupper($datos['search']));
		// $this->db->order_by('medico');
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}

	public function m_cargar_medicos_especialidad_prog($paramPaginate, $datos){
		$this->db->select("m.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres ) AS medico", FALSE);
		$this->db->select("esp.idespecialidad, esp.nombre AS especialidad", FALSE); //especialidad		
		$this->db->select("em.idempresa, em.descripcion AS empresa_admin", FALSE); //empresa admin
		$this->db->select("ed.idempresatercera, (SELECT d.descripcion from empresa d where d.idempresa = ed.idempresatercera ) AS empresa", FALSE); //empresa EMA
		$this->db->select("emme.idempresamedico, em.ruc_empresa, e.correo_electronico", FALSE); //empresa_medico
		$this->db->select("(select seesp.tiene_prog_cita from pa_sede_especialidad seesp where esp.idespecialidad = seesp.idespecialidad AND idsede = ". $this->sessionHospital['idsede'] . ") as tiene_prog_cita "); //tiene_prog_cita 
		
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado'); //medico
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico'); //empresa_medico
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');	

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');//empresa detalle
		$this->db->join('empresa em',"ed.idempresaadmin = em.idempresa AND em.ruc_empresa = '" . $datos['ruc'] . "'");//empresa  and ruc empresa admin	
		$this->db->where("em.es_empresa_admin ", 1);
		//$this->db->where("  " ,  );
		$this->db->where('emme.idempresa = ed.idempresatercera'); 
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('ed.estado_ed', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		//$this->db->where("emme.idsede", $datos['idsede']); //misma sede
		
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

		$this->db->order_by('medico');
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}

	public function m_cargar_medicos_especialidad_prog_info($datos){
		$this->db->select("m.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres ) AS medico", FALSE);
		//$this->db->select("esp.idespecialidad, esp.nombre AS especialidad", FALSE); //especialidad		
		//$this->db->select("em.idempresa, em.descripcion AS empresa_admin", FALSE); //empresa admin
		//$this->db->select("ed.idempresatercera, (SELECT d.descripcion from empresa d where d.idempresa = ed.idempresatercera ) AS empresa", FALSE); //empresa EMA
		//$this->db->select("emme.idempresamedico, em.ruc_empresa, e.correo_electronico", FALSE); //empresa_medico
		//$this->db->select("(select seesp.tiene_prog_cita from pa_sede_especialidad seesp where esp.idespecialidad = seesp.idespecialidad AND idsede = ". $this->sessionHospital['idsede'] . ") as tiene_prog_cita "); //tiene_prog_cita 		
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado'); //medico
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico'); //empresa_medico
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');	

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');//empresa detalle
		$this->db->join('empresa em',"ed.idempresaadmin = em.idempresa AND em.ruc_empresa = '" . $datos['ruc'] . "'");//empresa  and ruc empresa admin	
		$this->db->join('pa_prog_medico ppm', 'm.idmedico = ppm.idmedico');
		$this->db->where("em.es_empresa_admin ", 1);
		//$this->db->where("  " ,  );
		$this->db->where('emme.idempresa = ed.idempresatercera'); 
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('ed.estado_ed', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		$this->db->where('esp.idespecialidad', $datos['id']); // si salud

		$this->db->group_by('m.idmedico');
		$this->db->order_by('medico');
		
		return $this->db->get()->result_array();
	}

	public function m_count_medicos_especialidad_prog($paramPaginate, $datos){ 
		$this->db->select('COUNT(*) AS contador');	
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado'); //medico
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico'); //empresa_medico
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');	

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');//empresa detalle
		$this->db->join('empresa em',"ed.idempresaadmin = em.idempresa AND em.ruc_empresa = '" . $datos['ruc'] . "'");//empresa  and ruc empresa admin	
		$this->db->where("em.es_empresa_admin ", 1);
		//$this->db->where("  " ,  );
		$this->db->where('emme.idempresa = ed.idempresatercera'); 
		$this->db->where('estado_empl', 1); // empleado
		$this->db->where('estado_emes', 1); // empresa_especialidad
		$this->db->where('estado_emme', 1); // habilitado
		$this->db->where('esp.estado', 1); // habilitado
		$this->db->where('ed.estado_ed', 1); // habilitado
		$this->db->where('es_personal_salud', 1); // si salud
		//$this->db->where("emme.idsede", $datos['idsede']); //misma sede
		
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
	public function m_cargar_solicitudes_medico($paramDatos){
		// de momento ya no se usa en el reporte, se cambio por las solicitudes desde la venta
		// SUBCONSULTA para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		// if($paramDatos['empresaAdmin']['id'] != 0){ 
		// 	$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		// }
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// SUBCONSULTA para obtener los medicos
		$this->db->select('m.idmedico');
		$this->db->from('medico m');
		if($paramDatos['medico']['idmedico'] != 'ALL'){ 
			$this->db->where('idmedico',$paramDatos['medico']['idmedico']);
		}else{
			$this->db->join('rh_empleado e', 'm.idempleado = e.idempleado');
			$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico');
			$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad');
			$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad');
			$this->db->where('esp.idespecialidad', $paramDatos['especialidad']['id']); // habilitado
			$this->db->where('estado_empl', 1); // empleado
			$this->db->where('estado_emes', 1); // empresa_especialidad
			//$this->db->where('estado_em', 1); // habilitado
			$this->db->where('estado_emme', 1); // habilitado
			$this->db->where('esp.estado', 1); // habilitado
			$this->db->where('es_personal_salud', 1); // si salud	
		}
		$medico = $this->db->get_compiled_select();
		$this->db->reset_query();
		// SUBCONSULTA 1 - PROCEDIMIENTOS
		$this->db->select('pm.idproductomaster, pm.descripcion producto, tp.nombre_tp tipo_producto');
		$this->db->select("concat_ws(' ', med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres) AS medico");
		$this->db->from('solicitud_procedimiento sp');
		$this->db->join('medico med', 'sp.idmedicosolicitud = med.idmedico');
		$this->db->join('producto_master pm', 'sp.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_sp', 1);
		$this->db->where('sp.idmedicosolicitud IN ('. $medico . ')');
		$this->db->where('sp.idsedeempresaadmin_sp IN ('.$sedeempresa . ')');
		$this->db->where("DATE_PART('YEAR', sp.fecha_solicitud) = ".$paramDatos['anioDesdeCbo']); 
		$this->db->where("DATE_PART('MONTH', sp.fecha_solicitud) = ".$paramDatos['mes']['id']); 
		$procedimientos = $this->db->get_compiled_select();
		$this->db->reset_query();

		// SUBCONSULTA 2 - EXAMEN AUXILIAR
		$this->db->select('pm.idproductomaster, pm.descripcion producto, tp.nombre_tp tipo_producto');
		$this->db->select("concat_ws(' ', med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres) AS medico");
		$this->db->from('solicitud_examen se');
		$this->db->join('medico med', 'se.idmedicosolicitud = med.idmedico');
		$this->db->join('producto_master pm', 'se.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_sex', 1);
		$this->db->where('se.idmedicosolicitud IN ('. $medico . ')');
		$this->db->where('se.idsedeempresaadmin_se IN ('.$sedeempresa . ')');
		$this->db->where("DATE_PART('YEAR', se.fecha_solicitud) = ".$paramDatos['anioDesdeCbo']); 
		$this->db->where("DATE_PART('MONTH', se.fecha_solicitud) = ".$paramDatos['mes']['id']); 
		$examenes = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('idproductomaster, producto, tipo_producto, medico');
		$this->db->from( '('. $procedimientos . ' UNION ALL ' . $examenes . ') AS foo' );
		$this->db->group_by('idproductomaster, producto, tipo_producto, medico'); 
		$this->db->order_by('tipo_producto','ASC');
		$this->db->order_by('producto','ASC');
		return $this->db->get()->result_array();
	} 
	public function m_cargar_solicitudes_medico_venta($paramDatos){
		// SUBCONSULTA para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		// if($paramDatos['empresaAdmin']['id'] != 0){ 
		// 	$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		// }
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL 
		$this->db->select(" (
				SELECT STRING_AGG(sc.especialidad, ' | ' ORDER BY sc.especialidad ) 
				FROM (
					SELECT DISTINCT esp.nombre AS especialidad 
					FROM medico sc_med 
					INNER JOIN empresa_medico emme ON sc_med.idmedico = emme.idmedico 
					INNER JOIN empresa_especialidad emes ON emme.idempresaespecialidad = emes.idempresaespecialidad 
					INNER JOIN especialidad esp ON emme.idespecialidad = esp.idespecialidad 
					WHERE sc_med.idmedico = med.idmedico 
					AND esp.estado = 1 AND emme.estado_emme IN (1,2) AND emes.estado_emes IN (1,2) 
				) AS sc
			) AS especialidades",FALSE); 
		$this->db->select('COUNT(*) AS cantidad',FALSE); 
		$this->db->select('SUM(d.total_detalle::numeric) AS monto',FALSE);
		$this->db->select('pm.idproductomaster, pm.descripcion producto, tp.idtipoproducto, tp.nombre_tp AS tipo_producto');
		$this->db->select("concat_ws(' ', med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres) AS medico");
		$this->db->from('venta v');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('medico med', 'd.idmedico = med.idmedico');
		$this->db->where('v.si_paciente_externo', 'NO'); 
		$this->db->where('v.estado', 1);
		$this->db->where('pm.idtipoproducto IN (11,12,14,15,16)');
		if( empty($paramDatos['mostrarTodasSolicitudes']) ){ 
			if($paramDatos['medico']['idmedico'] != 'ALL'){ 
				$this->db->where('d.idmedico',$paramDatos['medico']['idmedico']);
			}else{
				if( !empty($paramDatos['arrMedicos']) ){
					$this->db->where('d.idmedico IN ('. $paramDatos['arrMedicos'] . ')');
				}
			}
		}/*else{
			
		}*/
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( $paramDatos['modalidadTiempo']['id'] == 'meses' ){
			$this->db->where("DATE_PART('YEAR', v.fecha_venta) = ".$paramDatos['anioDesdeCbo']); 
			$this->db->where("DATE_PART('MONTH', v.fecha_venta) = ".$paramDatos['mes']['id']); 
		}elseif( $paramDatos['modalidadTiempo']['id'] == 'dias' ){
			$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
			$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
			$this->db->where('v.fecha_venta BETWEEN '. $desde . ' AND ' . $hasta);	
		}
		
		$this->db->group_by('pm.idproductomaster, producto, tp.idtipoproducto, tipo_producto, med.idmedico '); 
		$this->db->order_by('medico','ASC');
		$this->db->order_by('producto','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_solicitudes_paciente_externo_venta($paramDatos){
		// SUBCONSULTA para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		// if($paramDatos['empresaAdmin']['id'] != 0){ 
		// 	$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		// }
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('SUM(d.total_detalle::numeric) AS monto',FALSE);
		$this->db->select('pm.idproductomaster, (pm.descripcion) AS producto, (tp.nombre_tp) AS tipo_producto, (esp.nombre) AS especialidad',FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad esp', 'pm.idespecialidad = esp.idespecialidad');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('v.estado', 1);
		$this->db->where('pm.idtipoproducto IN (11,12,14,15,16)'); 
		$this->db->where('v.si_paciente_externo', 'SI'); 
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')'); 
		$this->db->where("DATE_PART('YEAR', v.fecha_venta) = ".$paramDatos['anioDesdeCbo']); 
		$this->db->where("DATE_PART('MONTH', v.fecha_venta) = ".$paramDatos['mes']['id']); 
		// if( !($paramDatos['especialidad']['id'] == 'ALL') ){ 
		// 	$this->db->where("esp.idespecialidad",$paramDatos['especialidad']['id']); 
		// } 
		$this->db->group_by('pm.idproductomaster, producto, tipo_producto, especialidad');
		$this->db->order_by('tipo_producto','ASC');
		$this->db->order_by('producto','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_solicitudes_medico_especialidad_venta($paramDatos){
		// SUBCONSULTA para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		// if($paramDatos['empresaAdmin']['id'] != 0){ 
		// 	$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		// }
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL 
		
		$this->db->select('COUNT(*) AS cantidad',FALSE); 
		$this->db->select('SUM(d.total_detalle::numeric) AS monto',FALSE);
		$this->db->select('pm.idproductomaster, pm.descripcion producto, tp.idtipoproducto, tp.nombre_tp AS tipo_producto');
		$this->db->select("concat_ws(' ', med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres) AS medico, med.idmedico",FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('medico med', 'd.idmedico = med.idmedico');
		$this->db->where('v.si_paciente_externo', 'NO'); 
		$this->db->where('v.estado', 1);
		$this->db->where('pm.idtipoproducto IN (11,12,14,15,16)');
		$this->db->where('pm.idespecialidad', $paramDatos['especialidadSolicitud']['id']);
		if($paramDatos['medico']['idmedico'] != 'ALL'){ 
			$this->db->where('d.idmedico',$paramDatos['medico']['idmedico']);
		}else{
			if( !empty($paramDatos['arrMedicos']) ){
				$this->db->where('d.idmedico IN ('. $paramDatos['arrMedicos'] . ')');
			}
		}

		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( $paramDatos['modalidadTiempo']['id'] == 'meses' ){
			$this->db->where("DATE_PART('YEAR', v.fecha_venta) = ".$paramDatos['anioDesdeCbo']); 
			$this->db->where("DATE_PART('MONTH', v.fecha_venta) = ".$paramDatos['mes']['id']); 
		}elseif( $paramDatos['modalidadTiempo']['id'] == 'dias' ){
			$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
			$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
			$this->db->where('v.fecha_venta BETWEEN '. $desde . ' AND ' . $hasta);	
		}
		
		$this->db->group_by('pm.idproductomaster, producto, tp.idtipoproducto, tipo_producto, med.idmedico '); 
		$this->db->order_by('cantidad','DESC');
		$this->db->order_by('producto','ASC');
		return $this->db->get()->result_array();
	}
	// OBTENEMOS LOS TIPOS DE DOCUMENTOS
	public function m_cargar_tipo_documento()
	{
		return $this->db->get("rh_tipo_documento")->result_array();
	}
	/* PREPARADOS Y FORMULAS */
	public function m_cargar_medico_especialidad_por_arrId($arrId)
	{
		$this->db->select("m.idmedico, m.codigo_jj, concat_ws(' ', m.med_apellido_paterno, m.med_apellido_materno, m.med_nombres) AS medico, m.desbloq_por_sys_medico", FALSE);
		$this->db->select('MAX(esp.nombre) AS especialidad, colegiatura_profesional');
		$this->db->from('medico m');
		$this->db->join('empresa_medico emme','m.idmedico = emme.idmedico','left');
		$this->db->join('empresa_especialidad emes','emme.idempresaespecialidad = emes.idempresaespecialidad','left');
		$this->db->join('especialidad esp','emes.idespecialidad = esp.idespecialidad','left');
		$this->db->where_in('m.idmedico', $arrId);
		$this->db->group_by("m.idmedico, m.codigo_jj, concat_ws(' ', m.med_apellido_paterno, m.med_apellido_materno, m.med_nombres), colegiatura_profesional");
		$this->db->order_by('codigo_jj','ASC');
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_ultimo_codigo_medico()
	{
		$this->db->select('codigo_jj');
		$this->db->from('medico');
		$this->db->where('codigo_jj IS NOT NULL'); 
		$this->db->ilike('codigo_jj','V','after');
		$this->db->order_by('codigo_jj','DESC');
		$this->db->limit(1);
		$row = $this->db->get()->row_array();
		return $row['codigo_jj'];
	}
	public function m_asignar_codigo_jj($datos)
	{
		$data = array(
			'codigo_jj' => $datos['codigo_jj'],
			'fecha_asigna_codigo_jj' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedico',$datos['id']);
		return $this->db->update('medico', $data);
	}
	public function m_editar_colegiatura_prof($datos)
	{
		$data = array(
			'colegiatura_profesional' => $datos['colegiatura'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedico',$datos['idmedico']);
		return $this->db->update('medico', $data);
	}
	public function m_editar_categoria_ps($datos)
	{
		$data = array(
			'idcategoriapersonalsalud' => empty($datos['categoria_ps']) ? NULL : $datos['categoria_ps'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmedico',$datos['idmedico']);
		return $this->db->update('medico', $data);
	}
	public function m_verificar_medico_tiene_programacion($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_prog_medico pm, empresa_medico em, empresa_especialidad ee');
		$this->db->where('pm.idmedico', $datos['idmedico']);
		$this->db->where('pm.idempresamedico', $datos['idempresamedico']);
		$this->db->where('pm.estado_prm', 1);

		$this->db->where('pm.idempresamedico = em.idempresamedico');
		$this->db->where('em.idempresaespecialidad = ee.idempresaespecialidad');
		$this->db->where('ee.idempresadetalle', $datos['idempresadetalle']);
		
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? FALSE : TRUE );
	}

	public function m_get_correo_medico($datos){
		$this->db->select("e.correo_electronico, m.idempleado ", FALSE); //empresa_medico
		$this->db->from('rh_empleado e');
		$this->db->join('medico m','e.idempleado = m.idempleado AND m.idmedico = ' . $datos['idmedico']); //medico
		
		$this->db->where('e.estado_empl', 1); // empleado
		$this->db->where('e.es_personal_salud', 1); // si salud

		return $this->db->get()->result_array();
	}
	// REPORTE : LISTADO DE EMPLEADOS CON CONTRATOS QUE VENCEN EL MES
	public function m_cargar_empleado_con_contrato_vence_hasta_mes($datos)
	{
		$this->db->select('emp.idempleado, emp.numero_documento, ca.descripcion_ca AS cargo');
		$this->db->select("concat_ws(' ',emp.nombres,emp.apellido_paterno,emp.apellido_materno) AS empleado",FALSE);
		$this->db->select('s.descripcion AS sede, emp.idempresa, e.descripcion as empresa,
			hc.fecha_inicio_contrato::DATE, hc.fecha_fin_contrato::DATE, ea.razon_social',FALSE);
		$this->db->from('rh_empleado emp');
		$this->db->join('empresa e', 'emp.idempresa = e.idempresa');
		$this->db->join('rh_historial_contrato hc', 'emp.idempleado = hc.idempleado');
		$this->db->join('rh_cargo ca', 'hc.idcargo = ca.idcargo');
		$this->db->join('empresa_admin ea', 'hc.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s', 'emp.idsedeempleado = s.idsede');
		$this->db->where('emp.estado_empl', 1);
		if($datos['soloEmpActivos']){
			$this->db->where('emp.si_activo', 1);
		}
		$this->db->where('hc.estado_hc', 1);
		$this->db->where('hc.contrato_actual', 1);
		$this->db->where('( (EXTRACT(MONTH FROM hc.fecha_fin_contrato) <= '. $datos['mes']['id'] . ' AND ' .
			'EXTRACT(YEAR FROM hc.fecha_fin_contrato) = ' . $datos['anioDesdeCbo'] .') OR ' .
			'(EXTRACT(YEAR FROM hc.fecha_fin_contrato) < ' . $datos['anioDesdeCbo'] .') )'
			);

		if( $datos['porEmpresaOSede']['id'] == 'PS' ){ // por sede
			$this->db->where('emp.idsedeempleado', $datos['sede']['id']);
		}else{
			$this->db->where('ea.ruc', $datos['empresa']['ruc_empresa']);
		}

		$this->db->order_by('hc.fecha_fin_contrato','ASC');
		return $this->db->get()->result_array();
	}

	public function m_carga_cc_empleado(){
		$this->db->select('emp.idcentrocosto AS result');
		$this->db->from('rh_empleado emp'); 
		$this->db->where('emp.estado_empl', 1);
		$this->db->where('emp.idempleado', $this->sessionHospital['idempleado']);
		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_empleado_profesion($dato){
		$this->db->select('emp.idprofesion AS result');
		$this->db->from('rh_empleado emp'); 
		$this->db->join('rh_profesion prf', 'emp.idprofesion = prf.idprofesion');
		$this->db->where('emp.estado_empl', 1);
		$this->db->where('emp.idprofesion', $dato);

		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_actualizar_fecha_caducidad($datos)
	{
		$data = array(
			'fecha_caducidad_coleg' => $datos['fecha_caducidad']
		);
		$this->db->where('idempleado',$datos['idempleado']);
		return $this->db->update('rh_empleado', $data);
	}
}