<?php
class Model_estadisticas extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	/* logica 1 => TODOS LOS REGISTRADOS EN EL SISTEMA, EN DETERMINADA FECHA */
	public function m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_1($allInputs,$sc_idEspecialidad,$idEspecialidad)
	{
		$allParams = array( 
			$allInputs['anioDesde'],
			$allInputs['anioHasta']
		);
		$sql = "SELECT COUNT (*) AS pn,
			'-' AS pc,
			EXTRACT (YEAR FROM hi.fecha_creacion) AS ano,
			TO_CHAR(hi.fecha_creacion, 'Month') AS mes,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) AS nro_mes, 
			(
				SELECT COUNT(*) FROM ( 
						SELECT COUNT(*) AS contador 
						FROM cliente sc_cl 
						JOIN venta sc_v ON sc_cl.idcliente = sc_v.idcliente AND sc_v.idespecialidad =  $sc_idEspecialidad 
						WHERE estado = 1 
							AND estado_cli = 1 
							AND EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta) 
							AND EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)
						GROUP BY sc_cl.idcliente
				) AS sc_contador
			) AS ptodos 
		FROM cliente cl 
		JOIN historia hi ON cl.idcliente = hi.idcliente
		WHERE 
			cl.estado_cli = 1 
		AND EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ? AND ? 
		GROUP BY 
			EXTRACT (YEAR FROM hi.fecha_creacion),
			TO_CHAR(hi.fecha_creacion, 'Month')
		ORDER BY
			EXTRACT (YEAR FROM hi.fecha_creacion) ASC,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) ASC"; 
		$query = $this->db->query($sql,$allParams);
		return $query->result_array();
	}
	/* logica 2 => TODOS LOS REGISTRADOS EN EL SISTEMA, Y COMPRARON AL MENOS UN TICKET */
	public function m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_2($allInputs,$sc_idEspecialidad,$idEspecialidad)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta para sc_contador
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('cliente sc_cl');
		$this->db->join('venta sc_v','sc_cl.idcliente = sc_v.idcliente');
		if($allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('sc_v.idespecialidad = ',$sc_idEspecialidad);
		}
		$this->db->where('estado',1);
		$this->db->where('estado_cli',1);
		$this->db->where('EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)');
		$this->db->where('sc_v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by('sc_cl.idcliente');
		$sc_contador = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta para ptodos
		$this->db->select('COUNT(*)');
		$this->db->from('('. $sc_contador .') AS sc_contador');
		$ptodos = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta venta en sede
		$this->db->select('v.idsedeempresaadmin');
		$this->db->from('venta v');
		$this->db->where('v.idcliente = cl.idcliente');
		if($allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('v.idespecialidad', $idEspecialidad);
		}
		$this->db->where('EXTRACT (YEAR FROM (hi.fecha_creacion)) = EXTRACT (YEAR FROM v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM (hi.fecha_creacion)) = EXTRACT (MONTH FROM v.fecha_venta)');
		
		$this->db->where('v.estado', 1);
		$this->db->limit(1);
		$idsedeempresaadmin = $this->db->get_compiled_select();
		$this->db->reset_query();

		// consulta principal
		$this->db->select("COUNT (*) AS pn, '-' AS pc");
		$this->db->select("EXTRACT (YEAR FROM hi.fecha_creacion) AS ano, TO_CHAR(hi.fecha_creacion, 'Month') AS mes");
		$this->db->select("EXTRACT (MONTH FROM MAX (hi.fecha_creacion) ) AS nro_mes");
		$this->db->select('('. $ptodos .') AS ptodos');
		$this->db->from('cliente cl');
		$this->db->join('historia hi','cl.idcliente = hi.idcliente');
		$this->db->where('cl.estado_cli', 1);
		$this->db->where('EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ' .$allInputs['anioDesde']. ' AND ' .$allInputs['anioHasta']);
		$this->db->where('('. $idsedeempresaadmin .') IN ('. $sedeempresa . ')');
		$this->db->group_by("EXTRACT (YEAR FROM hi.fecha_creacion),	TO_CHAR(hi.fecha_creacion, 'Month')");
		$this->db->order_by('EXTRACT (YEAR FROM hi.fecha_creacion)','ASC');
		$this->db->order_by('EXTRACT (MONTH FROM MAX (hi.fecha_creacion) )','ASC');
		return $this->db->get()->result_array();
		/*
		$allParams = array(
			$sedeempresa,
			$allInputs['anioDesde'],
			$allInputs['anioHasta'],
			$sedeempresa,
		);
		$sql = "SELECT COUNT (*) AS pn,
			'-' AS pc,
			EXTRACT (YEAR FROM hi.fecha_creacion) AS ano,
			TO_CHAR(hi.fecha_creacion, 'Month') AS mes,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) AS nro_mes, 
			(
				SELECT COUNT(*) FROM ( 
						SELECT COUNT(*) AS contador 
						FROM cliente sc_cl 
						JOIN venta sc_v ON sc_cl.idcliente = sc_v.idcliente AND sc_v.idespecialidad =  $sc_idEspecialidad 
						WHERE estado = 1 
							AND estado_cli = 1 
							AND EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta) 
							AND EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)
							AND sc_v.idsedeempresaadmin IN ?
						GROUP BY sc_cl.idcliente
				) AS sc_contador
			) AS ptodos 
		FROM cliente cl 
		JOIN historia hi ON cl.idcliente = hi.idcliente
		WHERE 
			cl.estado_cli = 1 
		AND EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ?
		AND ?
		AND ( 
			SELECT v.idsedeempresaadmin 
			FROM venta v  
			WHERE v.idcliente = cl.idcliente AND v.idespecialidad = $idEspecialidad 
			AND v.estado = 1 
			LIMIT 1 
		) IN ?
		GROUP BY 
			EXTRACT (YEAR FROM hi.fecha_creacion),
			TO_CHAR(hi.fecha_creacion, 'Month')
		ORDER BY
			EXTRACT (YEAR FROM hi.fecha_creacion) ASC,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) ASC"; 
		$query = $this->db->query($sql,$allParams);
		return $query->result_array();*/
	}
	/* logica 3 => TODOS LOS REGISTRADOS EN EL SISTEMA, COMPRARON AL MENOS UN TICKET Y SE ATENDIERON */
	public function m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_3($allInputs,$sc_idEspecialidad,$idEspecialidad)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta para sc_contador
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('cliente sc_cl');
		$this->db->join('venta sc_v','sc_cl.idcliente = sc_v.idcliente');
		if($allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('sc_v.idespecialidad = ',$sc_idEspecialidad);
		}
		$this->db->where('estado',1);
		$this->db->where('estado_cli',1);
		$this->db->where('sc_v.paciente_atendido_v', 1); // solo los atendidos
		$this->db->where('EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)');
		$this->db->where('sc_v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by('sc_cl.idcliente');
		$sc_contador = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta para ptodos
		$this->db->select('COUNT(*)');
		$this->db->from('('. $sc_contador .') AS sc_contador');
		$ptodos = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta venta en sede
		$this->db->select('v.idsedeempresaadmin');
		$this->db->from('venta v');
		$this->db->where('v.idcliente = cl.idcliente');
		$this->db->where('v.paciente_atendido_v', 1); // solo los atendidos
		if($allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('v.idespecialidad', $idEspecialidad);
		}
			// agregado para que no salgan pacientes nuevos en meses diferentes a cuando empezo la empresa
		$this->db->where('EXTRACT (YEAR FROM (hi.fecha_creacion)) = EXTRACT (YEAR FROM v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM (hi.fecha_creacion)) = EXTRACT (MONTH FROM v.fecha_venta)');
		
		$this->db->where('v.estado', 1);
		$this->db->limit(1);
		$idsedeempresaadmin = $this->db->get_compiled_select();
		$this->db->reset_query();

		// consulta principal
		$this->db->select("COUNT (*) AS pn, '-' AS pc");
		$this->db->select("EXTRACT (YEAR FROM hi.fecha_creacion) AS ano, TO_CHAR(hi.fecha_creacion, 'Month') AS mes");
		$this->db->select("EXTRACT (MONTH FROM MAX (hi.fecha_creacion) ) AS nro_mes");
		$this->db->select('('. $ptodos .') AS ptodos');
		$this->db->from('cliente cl');
		$this->db->join('historia hi','cl.idcliente = hi.idcliente');
		$this->db->where('cl.estado_cli', 1);
		$this->db->where('EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ' .$allInputs['anioDesde']. ' AND ' .$allInputs['anioHasta']);
		$this->db->where('('. $idsedeempresaadmin .') IN ('. $sedeempresa . ')');
		$this->db->group_by("EXTRACT (YEAR FROM hi.fecha_creacion),	TO_CHAR(hi.fecha_creacion, 'Month')");
		$this->db->order_by('EXTRACT (YEAR FROM hi.fecha_creacion)','ASC');
		$this->db->order_by('EXTRACT (MONTH FROM MAX (hi.fecha_creacion) )','ASC');
		return $this->db->get()->result_array();
		/*
		$allParams = array(
			$allInputs['anioDesde'],
			$allInputs['anioHasta'],
			$allInputs['sedeempresa']
		);
		$sql = "SELECT COUNT (*) AS pn,
			'-' AS pc,
			EXTRACT (YEAR FROM hi.fecha_creacion) AS ano,
			TO_CHAR(hi.fecha_creacion, 'Month') AS mes,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) AS nro_mes, 
			(
				SELECT COUNT(*) FROM ( 
						SELECT COUNT(*) AS contador 
						FROM cliente sc_cl 
						JOIN venta sc_v ON sc_cl.idcliente = sc_v.idcliente AND sc_v.paciente_atendido_v = 1 AND sc_v.idespecialidad =  $sc_idEspecialidad 
						WHERE estado = 1 
							AND estado_cli = 1 
							AND EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta) 
							AND EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)
						GROUP BY sc_cl.idcliente
				) AS sc_contador
			) AS ptodos 
		FROM cliente cl 
		JOIN historia hi ON cl.idcliente = hi.idcliente
		WHERE 
			cl.estado_cli = 1 
		AND EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ? AND ? 
		AND ( 
			SELECT v.idsedeempresaadmin 
			FROM venta v  
			WHERE v.idcliente = cl.idcliente AND v.paciente_atendido_v = 1 AND v.idespecialidad = $idEspecialidad 
			AND v.estado = 1 
			LIMIT 1 
		) = ?  
		GROUP BY 
			EXTRACT (YEAR FROM hi.fecha_creacion),
			TO_CHAR(hi.fecha_creacion, 'Month')
		ORDER BY
			EXTRACT (YEAR FROM hi.fecha_creacion) ASC,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) ASC"; 
		$query = $this->db->query($sql,$allParams);
		return $query->result_array();
		*/
	}
	public function m_cargar_paciente_nuevo_logica_3_y_consulta_externa($allInputs)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta para sc_contador
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('historia sc_h');
		$this->db->join('atencion_medica sc_am','sc_h.idhistoria = sc_am.idhistoria AND sc_am.estado_am = 1');
		$this->db->join('detalle sc_d','sc_am.iddetalle = sc_d.iddetalle');
		$this->db->join('venta sc_v','sc_d.idventa = sc_v.idventa AND sc_v.paciente_atendido_v = 1');
		$this->db->where('estado',1);
		$this->db->where('tipo_atencion_medica','CM');
		$this->db->where('EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)');
		$this->db->where('sc_v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$cce = $this->db->get_compiled_select();
		$this->db->reset_query();

		// subconsulta venta en sede
		$this->db->select('v.idsedeempresaadmin');
		$this->db->from('venta v');
		$this->db->where('v.idcliente = cl.idcliente');
		$this->db->where('v.paciente_atendido_v', 1); // solo los atendidos
		if($allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('v.idespecialidad', $idEspecialidad);
		}
			// agregado para que no salgan pacientes nuevos en meses diferentes a cuando empezo la empresa
		$this->db->where('EXTRACT (YEAR FROM (hi.fecha_creacion)) = EXTRACT (YEAR FROM v.fecha_venta)');
		$this->db->where('EXTRACT (MONTH FROM (hi.fecha_creacion)) = EXTRACT (MONTH FROM v.fecha_venta)');
			// --
		$this->db->where('v.estado', 1);
		$this->db->limit(1);
		$idsedeempresaadmin = $this->db->get_compiled_select();
		$this->db->reset_query();

		// consulta principal
		$this->db->select("COUNT (*) AS pn, '-' AS pc");
		$this->db->select("EXTRACT (YEAR FROM hi.fecha_creacion) AS ano, TO_CHAR(hi.fecha_creacion, 'Month') AS mes");
		$this->db->select("EXTRACT (MONTH FROM MAX (hi.fecha_creacion) ) AS nro_mes");
		$this->db->select('('. $cce .') AS cce');
		$this->db->from('cliente cl');
		$this->db->join('historia hi','cl.idcliente = hi.idcliente');
		$this->db->where('cl.estado_cli', 1);
		$this->db->where('EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ' .$allInputs['anioDesde']. ' AND ' .$allInputs['anioHasta']);
		$this->db->where('('. $idsedeempresaadmin .') IN ('. $sedeempresa . ')');
		$this->db->group_by("EXTRACT (YEAR FROM hi.fecha_creacion),	TO_CHAR(hi.fecha_creacion, 'Month')");
		$this->db->order_by('EXTRACT (YEAR FROM hi.fecha_creacion)','ASC');
		$this->db->order_by('EXTRACT (MONTH FROM MAX (hi.fecha_creacion) )','ASC');
		return $this->db->get()->result_array();
		
		/*
		$allParams = array(
			$allInputs['anioDesde'],
			$allInputs['anioHasta'],
			$allInputs['sedeempresa']
		);
		$sql = "SELECT COUNT (*) AS pn,
			'-' AS pc,
			EXTRACT (YEAR FROM hi.fecha_creacion) AS ano,
			TO_CHAR(hi.fecha_creacion, 'Month') AS mes,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) AS nro_mes, 
			(
				
						SELECT COUNT(*) AS contador 
						FROM historia sc_h 
						JOIN atencion_medica sc_am ON sc_h.idhistoria = sc_am.idhistoria AND sc_am.estado_am = 1 
						JOIN detalle sc_d ON sc_am.iddetalle = sc_d.iddetalle 
						JOIN venta sc_v ON sc_d.idventa = sc_v.idventa AND sc_v.paciente_atendido_v = 1 
						WHERE estado = 1 
							AND EXTRACT (YEAR FROM MAX(hi.fecha_creacion)) = EXTRACT (YEAR FROM sc_v.fecha_venta) 
							AND EXTRACT (MONTH FROM MAX(hi.fecha_creacion)) = EXTRACT (MONTH FROM sc_v.fecha_venta)
							AND tipo_atencion_medica = 'CM' 
				
			) AS cce 
		FROM cliente cl 
		JOIN historia hi ON cl.idcliente = hi.idcliente
		WHERE 
			cl.estado_cli = 1 
		AND EXTRACT (YEAR FROM hi.fecha_creacion) BETWEEN ?
		AND ?
		AND ( 
			SELECT v.idsedeempresaadmin 
			FROM venta v  
			WHERE v.idcliente = cl.idcliente AND v.paciente_atendido_v = 1
			AND v.estado = 1 
			LIMIT 1 
		) = ?  
		GROUP BY 
			EXTRACT (YEAR FROM hi.fecha_creacion),
			TO_CHAR(hi.fecha_creacion, 'Month')
		ORDER BY
			EXTRACT (YEAR FROM hi.fecha_creacion) ASC,
			EXTRACT (
				MONTH
				FROM
					MAX (hi.fecha_creacion)
			) ASC"; 
		$query = $this->db->query($sql,$allParams);
		return $query->result_array();*/
	}
	public function m_cargar_prestaciones_por_anio_mes($allInputs)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( d.cantidad ) AS cantidad',FALSE);
		$this->db->select("EXTRACT(YEAR FROM v.fecha_venta) AS ano, TO_CHAR(v.fecha_venta, 'Month') AS mes,EXTRACT(MONTH FROM MAX(v.fecha_venta)) AS nro_mes"); 
		$this->db->from('venta v'); 
		$this->db->join('detalle d','d.idventa = v.idventa'); 
		$this->db->where('v.estado', 1); // activos 
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		// $this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(YEAR FROM v.fecha_venta), TO_CHAR(v.fecha_venta, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM v.fecha_venta)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(v.fecha_venta))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_anio_mes($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('COUNT( * ) AS cantidad_venta',FALSE);
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM v.fecha_venta) AS ano, TO_CHAR(v.fecha_venta, 'Month') AS mes,EXTRACT(MONTH FROM MAX(v.fecha_venta)) AS nro_mes"); 
		$this->db->from('venta v'); 
		$this->db->where('v.estado', 1); // activos 
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));

		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(YEAR FROM v.fecha_venta), TO_CHAR(v.fecha_venta, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM v.fecha_venta)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(v.fecha_venta))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nota_credito_por_anio_mes($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('COUNT( * ) AS cantidad_nc',FALSE);
		$this->db->select('SUM( monto::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM nc.fecha_creacion_nc) AS ano, TO_CHAR(nc.fecha_creacion_nc, 'Month') AS mes");
		$this->db->from('nota_credito nc');
		$this->db->where('nc.estado_nc', 1); // activos
		$this->db->where('EXTRACT(YEAR FROM nc.fecha_creacion_nc) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		$this->db->where('nc.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT( YEAR FROM nc.fecha_creacion_nc),TO_CHAR(nc.fecha_creacion_nc, 'Month')"); 
		$this->db->order_by("EXTRACT( YEAR FROM nc.fecha_creacion_nc)",'ASC');
		$this->db->order_by("EXTRACT( MONTH FROM MAX(nc.fecha_creacion_nc))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_prestaciones_por_especialidad_anio_mes($allInputs)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('SUM( d.cantidad ) AS cantidad',FALSE);
		$this->db->select("EXTRACT(YEAR FROM v.fecha_venta) AS ano, TO_CHAR(v.fecha_venta, 'Month') AS mes,EXTRACT(MONTH FROM MAX(v.fecha_venta)) AS nro_mes"); 
		$this->db->from('venta v'); 
		$this->db->join('detalle d','d.idventa = v.idventa'); 
		$this->db->where('v.estado', 1); // activos 
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		
		// $this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){
			$this->db->where('v.idespecialidad', $allInputs['especialidad']['id']);
		}
		$this->db->group_by("EXTRACT(YEAR FROM v.fecha_venta), TO_CHAR(v.fecha_venta, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM v.fecha_venta)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(v.fecha_venta))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_especialidad_anio_mes($allInputs)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM v.fecha_venta) AS ano, TO_CHAR(v.fecha_venta, 'Month') AS mes,EXTRACT(MONTH FROM MAX(v.fecha_venta)) AS nro_mes"); 
		$this->db->from('venta v'); 
		$this->db->where('v.estado', 1); // activos 
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		// $this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){
			$this->db->where('v.idespecialidad', $allInputs['especialidad']['id']);
		}
		$this->db->group_by("EXTRACT(YEAR FROM v.fecha_venta), TO_CHAR(v.fecha_venta, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM v.fecha_venta)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(v.fecha_venta))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nota_credito_por_especialidad_anio_mes($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( monto::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM nc.fecha_creacion_nc) AS ano, TO_CHAR(nc.fecha_creacion_nc, 'Month') AS mes");
		$this->db->from('nota_credito nc');
		$this->db->where('nc.estado_nc', 1); // activos
		$this->db->where('EXTRACT(YEAR FROM nc.fecha_creacion_nc) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		// $this->db->where('nc.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('nc.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){
			$this->db->where('nc.idespecialidad', $allInputs['especialidad']['id']);
		}
		$this->db->group_by("EXTRACT( YEAR FROM nc.fecha_creacion_nc),TO_CHAR(nc.fecha_creacion_nc, 'Month')"); 
		$this->db->order_by("EXTRACT( YEAR FROM nc.fecha_creacion_nc)",'ASC');
		$this->db->order_by("EXTRACT( MONTH FROM MAX(nc.fecha_creacion_nc))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_detallado_por_especialidad_anio_mes($allInputs)
	{	
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( d.cantidad ) AS cantidad',FALSE);
		$this->db->select('SUM( d.total_detalle::numeric ) AS monto',FALSE);
		$this->db->select("MAX(EXTRACT(YEAR FROM v.fecha_venta)) ano, EXTRACT(MONTH FROM v.fecha_venta) AS mes ,pm.idproductomaster, pm.descripcion",FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d','d.idventa = v.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->where('v.estado', 1); // activos
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) BETWEEN '. $this->db->escape($allInputs['anioDesdeCbo']) .' AND ' . $this->db->escape($allInputs['anioHastaCbo']));
		$this->db->where_in('EXTRACT(MONTH FROM v.fecha_venta)',$allInputs['arrMeses']);
		// $this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){ 
			$this->db->where('v.idespecialidad', $allInputs['especialidad']['id']);
		} 
		$this->db->group_by("EXTRACT(YEAR FROM v.fecha_venta), EXTRACT(MONTH FROM v.fecha_venta), pm.idproductomaster"); 
		$this->db->order_by("pm.descripcion",'ASC');
		$this->db->order_by("EXTRACT (MONTH FROM v.fecha_venta)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_total_nota_credito_por_especialidad_anio_mes($allInputs)
	{	
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( monto::numeric ) AS monto, COUNT(*) AS cantidad',FALSE);
		$this->db->select("MAX(EXTRACT(YEAR FROM nc.fecha_creacion_nc)) AS ano, EXTRACT(MONTH FROM nc.fecha_creacion_nc) AS mes");
		$this->db->from('nota_credito nc');
		$this->db->where('nc.estado_nc', 1); // activos
		$this->db->where('EXTRACT(YEAR FROM nc.fecha_creacion_nc) BETWEEN '. $this->db->escape($allInputs['anioDesdeCbo']) .' AND ' . $this->db->escape($allInputs['anioHastaCbo']));
		$this->db->where_in('EXTRACT(MONTH FROM nc.fecha_creacion_nc)',$allInputs['arrMeses']);
		// $this->db->where('nc.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('nc.idsedeempresaadmin IN ('.$sedeempresa . ')');
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){
			$this->db->where('nc.idespecialidad', $allInputs['especialidad']['id']);
		}
		$this->db->group_by("EXTRACT(MONTH FROM nc.fecha_creacion_nc)"); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_mes_dia($allInputs)
	{	
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(MONTH FROM v.fecha_venta) AS mes, EXTRACT(DAY FROM v.fecha_venta) AS dia"); 
		$this->db->from('venta v'); 
		$this->db->where('v.estado', 1); // activos 
		$this->db->where('EXTRACT(MONTH FROM v.fecha_venta) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT(YEAR FROM v.fecha_venta) = ',$allInputs['anioDesdeCbo']);
		// $this->db->where('v.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(MONTH FROM v.fecha_venta), EXTRACT(DAY FROM v.fecha_venta)"); 
		$this->db->order_by("EXTRACT(MONTH FROM v.fecha_venta)",'ASC');
		$this->db->order_by("EXTRACT(DAY FROM v.fecha_venta)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nota_credito_por_mes_dia($allInputs)
	{	
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( monto::numeric ) AS total',FALSE); 
		$this->db->select("EXTRACT(MONTH FROM nc.fecha_creacion_nc) AS mes, EXTRACT(DAY FROM nc.fecha_creacion_nc) AS dia"); 
		$this->db->from('nota_credito nc');
		$this->db->where('nc.estado_nc', 1); // activos 
		$this->db->where('EXTRACT(MONTH FROM nc.fecha_creacion_nc) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT(YEAR FROM nc.fecha_creacion_nc) = ',$allInputs['anioDesdeCbo']);
		// $this->db->where('nc.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('nc.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(MONTH FROM nc.fecha_creacion_nc), EXTRACT(DAY FROM nc.fecha_creacion_nc)"); 
		$this->db->order_by("EXTRACT(MONTH FROM nc.fecha_creacion_nc)",'ASC');
		$this->db->order_by("EXTRACT(DAY FROM nc.fecha_creacion_nc)",'ASC');
		return $this->db->get()->result_array();
	}
	// FARMACIA //
	public function m_cargar_ventas_farmacia_por_anio_mes($allInputs){
		
		
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM fm.fecha_movimiento) AS ano, TO_CHAR(fm.fecha_movimiento, 'Month') AS mes,EXTRACT(MONTH FROM MAX(fm.fecha_movimiento)) AS nro_mes"); 
		$this->db->from('far_movimiento fm'); 
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.tipo_nota_credito IS NULL');
		$this->db->where('EXTRACT(YEAR FROM fm.fecha_movimiento) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(YEAR FROM fm.fecha_movimiento), TO_CHAR(fm.fecha_movimiento, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM fm.fecha_movimiento)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(fm.fecha_movimiento))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nota_credito_farmacia_por_anio_mes($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$allInputs['sede']['id']);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(YEAR FROM fm.fecha_movimiento) AS ano, TO_CHAR(fm.fecha_movimiento, 'Month') AS mes,EXTRACT(MONTH FROM MAX(fm.fecha_movimiento)) AS nro_mes"); 
		$this->db->from('far_movimiento fm'); 
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.tipo_nota_credito IS NOT NULL');
		$this->db->where('EXTRACT(YEAR FROM fm.fecha_movimiento) BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("EXTRACT(YEAR FROM fm.fecha_movimiento), TO_CHAR(fm.fecha_movimiento, 'Month')"); 
		$this->db->order_by("EXTRACT(YEAR FROM fm.fecha_movimiento)",'ASC');
		$this->db->order_by("EXTRACT(MONTH FROM MAX(fm.fecha_movimiento))",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_farmacia_por_mes_dia($allInputs)
	{
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(MONTH FROM fm.fecha_movimiento) AS mes, EXTRACT(DAY FROM fm.fecha_movimiento) AS dia"); 
		$this->db->from('far_movimiento fm'); 
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.tipo_nota_credito IS NULL');
		$this->db->where('EXTRACT(MONTH FROM fm.fecha_movimiento) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT(YEAR FROM fm.fecha_movimiento) = ',$allInputs['anioDesdeCbo']);
		$this->db->where('fm.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->group_by("EXTRACT(MONTH FROM fm.fecha_movimiento), EXTRACT(DAY FROM fm.fecha_movimiento)"); 
		$this->db->order_by("EXTRACT(MONTH FROM fm.fecha_movimiento)",'ASC');
		$this->db->order_by("EXTRACT(DAY FROM fm.fecha_movimiento)",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nota_credito_farmacia_por_mes_dia($allInputs)
	{
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select("EXTRACT(MONTH FROM fm.fecha_movimiento) AS mes, EXTRACT(DAY FROM fm.fecha_movimiento) AS dia"); 
		$this->db->from('far_movimiento fm'); 
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.tipo_nota_credito IS NOT NULL');
		$this->db->where('EXTRACT(MONTH FROM fm.fecha_movimiento) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT(YEAR FROM fm.fecha_movimiento) = ',$allInputs['anioDesdeCbo']);
		$this->db->group_by("EXTRACT(MONTH FROM fm.fecha_movimiento), EXTRACT(DAY FROM fm.fecha_movimiento)"); 
		$this->db->order_by("EXTRACT(MONTH FROM fm.fecha_movimiento)",'ASC');
		$this->db->order_by("EXTRACT(DAY FROM fm.fecha_movimiento)",'ASC');
		return $this->db->get()->result_array();
	}
	/* AÑOS PASADOS */ 
	public function m_cargar_estadisticas_anos_anteriores($allInputs,$idEspecialidad=FALSE)
	{	
		$this->db->select('(monto::numeric) AS monto');
		$this->db->select('idgraficoestadistico, anio, mes, num_mes, (prestaciones) AS cantidad, ticket_promedio, pacientes_nuevos, pacientes_continuadores, (pacientes_continuadores + pacientes_nuevos) AS ptodos'); 
		$this->db->from('grafico_estadistico ge');
		$this->db->where('anio BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta']));
		if( $idEspecialidad ){
			$this->db->where('idespecialidad',$idEspecialidad);
		}else{
			$this->db->where('idespecialidad IS NULL');
		}
		
		$this->db->order_by("anio",'ASC');
		$this->db->order_by("num_mes",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_estadisticas_por_especialidad_anos_anteriores($allInputs)
	{
		$this->db->select('(monto::numeric) AS monto');
		$this->db->select('idgraficoestadistico, anio, mes, num_mes, (prestaciones) AS cantidad, ticket_promedio'); 
		$this->db->from('grafico_estadistico ge');
		$this->db->where('anio BETWEEN '. $this->db->escape($allInputs['anioDesde']) .' AND ' . $this->db->escape($allInputs['anioHasta'])); 
		if( !empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] !== 'ALL'  ){
			$this->db->where('ge.idespecialidad', $allInputs['especialidad']['id']);
		}else{
			$this->db->where('idespecialidad IS NULL');	
		}
		
		$this->db->order_by("anio",'ASC');
		$this->db->order_by("num_mes",'ASC');
		return $this->db->get()->result_array();
	}

	/* INDICADORES ORDEN DE MEDICOS OBSTETRICIA */
	public function m_cargar_productos_de_especialidad_indicador($allInputs)
	{
		$this->db->select('DISTINCT idproductomaster, pi.key_indicador',FALSE); 
		$this->db->from('est_producto_indicador pi');
		$this->db->join('est_producto_ind_detalle pid','pi.key_indicador = pid.key_indicador');
		// if( $allInputs['especialidad']['id'] != 'ALL')
			$this->db->where('pid.idespecialidadindicador', $allInputs['especialidad']['id']);
		$this->db->where('estado_ind', 1); // activos 
		return $this->db->get()->result_array();
	}
	public function m_cargar_indicadores_meta_orden_medico($allInputs)
	{
		$this->db->select('idindicador, anio, mes, num_mes, (meta_indicador) AS meta, key_indicador, str_indicador',FALSE); 
		$this->db->from('est_producto_indicador pi');
		$this->db->where('idespecialidadindicador', $allInputs['especialidad']['id']);
		$this->db->where('anio',$allInputs['anio']);
		$this->db->where_in('key_indicador', $allInputs['productosInd']);
		$this->db->where('estado_ind', 1); // activos 
		$this->db->order_by("anio",'ASC');
		$this->db->order_by("num_mes",'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_indicadores_valor_orden_medico($allInputs) // 228
	{
		$sql = 'SELECT EXTRACT(YEAR FROM v.fecha_venta) AS anio, EXTRACT(MONTH FROM v.fecha_venta) AS mes, COUNT(*) AS cant_ordenes_venta, pm.idproductomaster, pm.descripcion, key_indicador 
			FROM solicitud_examen se 
			INNER JOIN detalle dt ON se.idsolicitudexamen = dt.idsolicitud 
			INNER JOIN venta v ON dt.idventa = v.idventa 
			LEFT JOIN atencion_medica am ON se.idatencionmedica = am.idatencionmedica AND am.estado_am = 1
			INNER JOIN producto_master pm ON se.idproductomaster = pm.idproductomaster 
			INNER JOIN est_producto_ind_detalle pid ON pm.idproductomaster = pid.idproductomaster 
			WHERE se.estado_sex = 1 AND v.estado = 1 
			AND se.idmedicosolicitud = ? 
			AND pm.idproductomaster IN ? 
			AND EXTRACT(MONTH FROM v.fecha_venta) IN ? 
			AND EXTRACT(YEAR FROM v.fecha_venta) BETWEEN ? AND ? 
			AND v.idsedeempresaadmin = ? 
			GROUP BY EXTRACT(YEAR FROM v.fecha_venta), EXTRACT(MONTH FROM v.fecha_venta), pm.idproductomaster, key_indicador 
			UNION ALL 
			SELECT EXTRACT(YEAR FROM v.fecha_venta) AS anio, EXTRACT(MONTH FROM v.fecha_venta) AS mes, COUNT(*) AS cant_ordenes_venta, pm.idproductomaster, pm.descripcion, key_indicador 
			FROM solicitud_procedimiento spr 
			INNER JOIN detalle dt ON spr.idsolicitudprocedimiento = dt.idsolicitud 
			INNER JOIN venta v ON dt.idventa = v.idventa 
			LEFT JOIN atencion_medica am ON spr.idatencionmedica = am.idatencionmedica AND am.estado_am = 1 
			INNER JOIN producto_master pm ON spr.idproductomaster = pm.idproductomaster 
			INNER JOIN est_producto_ind_detalle pid ON pm.idproductomaster = pid.idproductomaster 
			WHERE spr.estado_sp = 1 AND v.estado = 1 
			AND spr.idmedicosolicitud = ? 
			AND pm.idproductomaster IN ? 
			AND EXTRACT(MONTH FROM v.fecha_venta) IN ? 
			AND EXTRACT(YEAR FROM v.fecha_venta) BETWEEN ? AND ? 
			AND v.idsedeempresaadmin = ? 
			GROUP BY EXTRACT(YEAR FROM v.fecha_venta), EXTRACT(MONTH FROM v.fecha_venta), pm.idproductomaster, key_indicador '; 
		$query = $this->db->query($sql,$allInputs);
		// var_dump($allInputs); 
		// print_r($sql); exit();
		return $query->result_array();
	}
	public function m_cargar_indicadores_valor_solo_orden_medico($allInputs) // 228 
	{
		$sql = 'SELECT EXTRACT(YEAR FROM se.fecha_solicitud) AS anio, EXTRACT(MONTH FROM se.fecha_solicitud) AS mes, COUNT(*) AS cant_ordenes, pm.idproductomaster, pm.descripcion, key_indicador  
			FROM solicitud_examen se 
			LEFT JOIN atencion_medica am ON se.idatencionmedica = am.idatencionmedica AND am.estado_am = 1 
			INNER JOIN producto_master pm ON se.idproductomaster = pm.idproductomaster 
			INNER JOIN est_producto_ind_detalle pid ON pm.idproductomaster = pid.idproductomaster 
			WHERE se.estado_sex = 1 
			AND se.idmedicosolicitud = ? 
			AND pm.idproductomaster IN ? 
			AND EXTRACT(MONTH FROM se.fecha_solicitud) IN ? 
			AND EXTRACT(YEAR FROM se.fecha_solicitud) BETWEEN ? AND ? 
			AND se.idsedeempresaadmin_se = ? 
			GROUP BY EXTRACT(YEAR FROM se.fecha_solicitud), EXTRACT(MONTH FROM se.fecha_solicitud), pm.idproductomaster, key_indicador
			UNION ALL 
			SELECT EXTRACT(YEAR FROM spr.fecha_solicitud) AS anio, EXTRACT(MONTH FROM spr.fecha_solicitud) AS mes, COUNT(*) AS cant_ordenes, pm.idproductomaster, pm.descripcion, key_indicador 
			FROM solicitud_procedimiento spr 
			LEFT JOIN atencion_medica am ON spr.idatencionmedica = am.idatencionmedica AND am.estado_am = 1 
			INNER JOIN producto_master pm ON spr.idproductomaster = pm.idproductomaster 
			INNER JOIN est_producto_ind_detalle pid ON pm.idproductomaster = pid.idproductomaster 
			WHERE spr.estado_sp = 1
			AND spr.idmedicosolicitud = ? 
			AND pm.idproductomaster IN ? 
			AND EXTRACT(MONTH FROM spr.fecha_solicitud) IN ? 
			AND EXTRACT(YEAR FROM spr.fecha_solicitud) BETWEEN ? AND ? 
			AND spr.idsedeempresaadmin_sp = ? 
			GROUP BY EXTRACT(YEAR FROM spr.fecha_solicitud), EXTRACT(MONTH FROM spr.fecha_solicitud), pm.idproductomaster, key_indicador '; 
		$query = $this->db->query($sql,$allInputs);
		// var_dump($allInputs);
		// print_r($sql); exit();
		return $query->result_array();
	}

	public function m_listar_preguntas($idpregunta = 0)
	{
		$this->db->select('idpregunta, descripcion_pr'); 
		$this->db->from('enc_pregunta');
		//ESTA CONDICIÓN SE DA PARA LISTAR SOLO POR UNA PREGUNTA: REVISAR "CENTRAL REPORTES->GERENCIA COMERCIAL->EVOLUCION DE LAS RESPUESTAS EN EL TIEMPO"
		if($idpregunta != 0){		
			$this->db->where('idpregunta', $idpregunta);	
		}
		$this->db->where('estado', 1);
		return $this->db->get()->result_array();
	}

	public function m_listar_respuestas_por_pregunta($desde, $hasta, $tablet)
    {
    	$this->db->select('p.idpregunta, p.descripcion_pr, r.estado AS respuesta, COUNT(*) AS contador');
    	$this->db->from('enc_pregunta p');
    	$this->db->join('enc_respuesta r', 'p.idpregunta = r.idpregunta');
    	$this->db->where('p.estado = 1 AND r.estado <> 0');
    	if( $tablet <> 'ALL' ){
    		$this->db->where('tablet_sesion = '. $tablet);    		
    	}
    	if(!empty($desde) && !empty($hasta)){
    		$this->db->where("fecha >= '".$desde."' AND fecha <= '".$hasta." 23:59:59'");
    	}
    	$this->db->group_by('p.idpregunta, r.estado');
    	// var_dump($sql);
        return $this->db->get()->result_array();
    }

	public function m_listar_respuestas_por_fecha($idpregunta, $desde, $hasta, $tablet, $groupByMesOrDia)
    {
    	$this->db->select('p.idpregunta, p.descripcion_pr, r.estado AS respuesta');
    	$this->db->select("to_char(fecha,'YYYY-mm') AS fechaAMes");
    	$this->db->select("to_char(fecha,'YYYY-mm-dd') AS fechaADia");
    	$this->db->select('COUNT(*) AS contador');
    	$this->db->from('enc_pregunta p');
    	$this->db->join('enc_respuesta r', 'p.idpregunta = r.idpregunta AND r.idpregunta = ' . $idpregunta);
    	$this->db->where('p.estado', 1);
    	if( $tablet <> 'ALL' ){
    		$this->db->where('tablet_sesion', $tablet);
    	}    	
    	if( $groupByMesOrDia == 'mes' ){
    		$this->db->group_by('p.idpregunta, MONTH(fecha), r.estado');			
    	}
    	if($groupByMesOrDia == 'dia'){
    		$this->db->group_by("p.idpregunta, to_char(r.fecha,'YYYY-mm-dd'), to_char(fecha, 'YYYY-mm'), r.estado");
    	}
        return $this->db->get()->result_array();
    }

}
?>