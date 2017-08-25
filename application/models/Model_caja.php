<?php
class Model_caja extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	} 
	public function m_cargar_caja_actual_de_usuario($idModulo = 1){ // var_dump($idModulo); 
		if(empty($idModulo)){
			$idModulo = 1;
		} 
		$this->db->select('c.idcaja, c.iduser, descripcion, estado, numero_caja, descripcion_caja, serie_caja, cm.idcajamaster, username, (g.name) AS grupo, key_group'); 
		$this->db->from('caja c'); 
		$this->db->join('caja_master cm','c.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','c.iduser = u.idusers'); 
		$this->db->join('users_groups ug','u.idusers = ug.idusers'); 
		$this->db->join('group g','ug.idgroup = g.idgroup'); 
		$this->db->where('estado', 1); // abierta 
		$this->db->where('u.idusers', $this->sessionHospital['idusers']); 
		$this->db->where('cm.idmodulo', $idModulo); 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
			$this->db->where('cm.idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		}
		$this->db->order_by('c.idcaja','DESC'); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_cajas_master_tipo_doc($allInputs = FALSE, $idModulo = 1)
	{
		$this->db->select('cm.idcajamaster, descripcion_caja, cm.maquina_registradora, numero_caja, serie_caja, td.idtipodocumento, descripcion_td, 
			abreviatura, dc.iddocumentocaja, numero_serie, ea.idempresaadmin, razon_social, nombre_legal'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('documento_caja dc','cm.idcajamaster = dc.idcajamaster'); 
		$this->db->join('tipo_documento td','dc.idtipodocumento = td.idtipodocumento'); 
		//$this->db->where('idmodulo', $idModulo); 
		$this->db->where('estado_td', 1); 
		$this->db->where('estado_caja', 1); 
		$this->db->where('estado_emp <>', 0); 
		if( $allInputs ){
			$this->db->where('ea.idempresaadmin', $allInputs['empresa']); 
		}
		// if( $allInputs ){
		// 	$this->db->ilike('CAST(ea.idempresaadmin AS TEXT)', $allInputs['empresa']);
		// }
		$this->db->order_by('idcajamaster','DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_cajas_master($allInputs = FALSE)
	{
		$this->db->select('cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, ea.idempresaadmin, razon_social, nombre_legal'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_caja', 1); 
		$this->db->where('estado_emp <>', 0); 
		if( $allInputs ){
			$this->db->ilike('CAST(ea.idempresaadmin AS TEXT)', $allInputs['empresa']);
		}
		$this->db->order_by('idcajamaster','DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_cajas_master_abiertas($allInputs = FALSE)
	{
		$this->db->select('c.idcaja, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, ea.idempresaadmin, razon_social, nombre_legal, username, email'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->where('c.estado', 1); // SOLO CAJAS ABIERTAS 
		$this->db->where('estado_usuario', 1); // ACTIVO 
		$this->db->where('estado_caja', 1); 
		$this->db->where('estado_emp <>', 0); 
		if( $this->sessionHospital['key_group'] === 'key_caja' || $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('c.iduser', $this->sessionHospital['idusers']); 
		}
		if( $allInputs ){
			$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);
		}
		if( !(empty($allInputs['idmodulo'])) ){
			$this->db->where('cm.idmodulo', $allInputs['idmodulo']);
		}else{
			$this->db->where('cm.idmodulo', 1);
		}
		$this->db->order_by('idcajamaster','DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_cajas_todas_cajas_master_session($datos=FALSE)
	{
		$this->db->select('cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, ea.idempresaadmin, razon_social, nombre_legal'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_caja', 1); 
		$this->db->where('estado_emp <>', 0); 
		// if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
		$this->db->where('cm.idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		// }
		if( !empty($datos['idmodulo']) ){ 
			$this->db->where('idmodulo', $datos['idmodulo']); 
		}
		
		$this->db->order_by('idcajamaster','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_cajas_todas_cajas_master_usuario_session($datos=FALSE)
	{
		$this->db->select('cm.idcajamaster, descripcion_caja, numero_caja, serie_caja'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');

		// $this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_caja', 1); 
		$this->db->where('iduser', $datos['usuario']['id']); 
		// if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
		// 	$this->db->where('cm.idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		// }
		if( !empty($datos['idmodulo']) ){ 
			$this->db->where('idmodulo', $datos['idmodulo']); 
		}
		$this->db->where('DATE(c.fecha_apertura)', $datos['fecha']);
		
		$this->db->order_by('idcajamaster','DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_caja_por_este_numero_serie($idCajaMaster, $idTipoDocumento)
	{
		$this->db->select('cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, td.idtipodocumento, descripcion_td, 
			abreviatura, dc.iddocumentocaja, numero_serie, ea.idempresaadmin, razon_social, nombre_legal'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('documento_caja dc','cm.idcajamaster = dc.idcajamaster'); 
		$this->db->join('tipo_documento td','dc.idtipodocumento = td.idtipodocumento'); 
		$this->db->where('estado_td', 1); 
		$this->db->where('estado_caja', 1);
		$this->db->where('estado_emp <>', 0); 
		$this->db->where('cm.idcajamaster', $idCajaMaster); 
		$this->db->where('td.idtipodocumento', $idTipoDocumento); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_cajas_de_dia_usuario($allInputs)
	{
		$this->db->select('c.idcaja, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, ea.idempresaadmin, razon_social, nombre_legal, username, fecha_apertura, fecha_cierre'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->where('c.estado <>', 0); 
		$this->db->where('estado_usuario', 1); // ACTIVO 
		$this->db->where('estado_caja', 1); 
		$this->db->where('estado_emp <>', 0); 
		$this->db->where('c.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('DATE(c.fecha_apertura)', $allInputs['fecha']);
		$this->db->where('u.idusers', $allInputs['usuario']['id']);
		$this->db->where('cm.idcajamaster', $allInputs['caja']['id']);
		$this->db->order_by('fecha_apertura');
		return $this->db->get()->result_array();
	}
	public function m_cargar_documentos_vendidos_distinct($allInputs)
	{
		$this->db->distinct(); 
		$this->db->select('td.idtipodocumento, td.descripcion_td');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		// $this->db->where('DATE() BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		// $this->db->group_by('c.idcaja,c.descripcion, c.fecha_apertura'); 
		$this->db->where('td.estado_td',1);
		$this->db->order_by('td.idtipodocumento');
		return $this->db->get()->result_array();
	}
	/* =================================== */
	/* 		LIQUIDACION - APERTURA DE CAJA */
	/* =================================== */
	public function m_cargar_apertura_caja($paramPaginate=FALSE, $allInputs)
	{
		$this->db->select('SUM( CASE WHEN v.estado = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS total_venta',FALSE);
		$this->db->select('SUM( CASE WHEN v.estado = 1 THEN 1 ELSE 0 END) AS cantidad_venta',FALSE);
		$this->db->select('SUM( CASE WHEN v.estado = 0 THEN 1 ELSE 0 END) AS cantidad_anulado',FALSE);
		$this->db->select('	( 
			SELECT COUNT(*) AS cantidad_ncr 
			FROM nota_credito nc 
			INNER JOIN venta sc_v ON nc.idventa = sc_v.idventa 
			WHERE nc.idcaja = c.idcaja AND estado_nc = 1 AND tipo_salida = 1 
		) AS cantidad_nota_credito',FALSE);
		$this->db->select('	( 
			SELECT SUM(nc.monto::numeric) AS suma_ncr 
			FROM nota_credito nc 
			INNER JOIN venta sc_v ON nc.idventa = sc_v.idventa 
			WHERE nc.idcaja = c.idcaja AND estado_nc = 1 AND tipo_salida = 1 
		) AS suma_nota_credito',FALSE);

		$this->db->select('	( 
			SELECT COUNT(*) AS cantidad_ext 
			FROM nota_credito nc 
			INNER JOIN venta sc_v ON nc.idventa = sc_v.idventa 
			WHERE nc.idcaja = c.idcaja AND estado_nc = 1 AND tipo_salida = 2 
		) AS cantidad_extorno',FALSE);
		$this->db->select('	( 
			SELECT SUM(nc.monto::numeric) AS suma_ext 
			FROM nota_credito nc 
			INNER JOIN venta sc_v ON nc.idventa = sc_v.idventa 
			WHERE nc.idcaja = c.idcaja AND estado_nc = 1 AND tipo_salida = 2 
		) AS suma_extorno',FALSE);

		$this->db->select('c.idcaja, c.descripcion, c.fecha_apertura, c.fecha_cierre, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, u.idusers, u.username');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		if(!empty($allInputs['tipodocumento'])){ 
			$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser AND v.idtipodocumento IN (' . $allInputs['tipodocumento'] . ')','left');
		}else{
			$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser','left');
		}
		
		$this->db->where('estado_caja', 1);
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		// $this->db->where('DATE() BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);
		
		
		if( $this->sessionHospital['key_group'] === 'key_caja'){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja,c.descripcion, c.fecha_apertura, c.fecha_cierre, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, u.idusers, u.username'); 
		if( $paramPaginate ){ 
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
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}else{ // SOLO REPORTES 
			$this->db->order_by('c.fecha_apertura','DESC');
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sum_apertura_caja($paramPaginate=FALSE, $allInputs) 
	{
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('SUM( CASE WHEN v.estado = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS total_importe',FALSE);
		$this->db->select('SUM( CASE WHEN v.estado = 1 THEN 1 ELSE 0 END) AS cantidad_venta',FALSE);
		$this->db->select('SUM( CASE WHEN v.estado = 0 THEN 1 ELSE 0 END) AS cantidad_anulado',FALSE);
		$this->db->select('SUM(0) AS cantidad_ncr',FALSE);
		//$this->db->select('SUM( 0 ) AS cantidad_ncr',FALSE);
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		if(!empty($allInputs['tipodocumento'])){ 
			$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser AND v.idtipodocumento IN (' . $allInputs['tipodocumento'] . ')','left');
		}else{
			$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser','left');
		}
		// $this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser');
		$this->db->where('estado_caja', 1);
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		// $this->db->where('DATE(c.fecha_apertura) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);
		if( $paramPaginate ){ 
			if( $paramPaginate['search'] ){ 
				foreach ($paramPaginate['searchColumn'] as $key => $value) { 
					if( !empty($value) ){ 
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					}
				}
			}
			// if(!empty($allInputs['tipodocumento'])){ 
			// 	$this->db->where_in('v.idtipodocumento', $allInputs['tipodocumento']);
			// }
		} 
		if( $this->sessionHospital['key_group'] === 'key_caja'){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja,c.descripcion, c.fecha_apertura, c.fecha_cierre, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, u.idusers, u.username'); 
			// if( $paramPaginate['search'] ){ 
			// 	foreach ($paramPaginate['searchColumn'] as $key => $value) { 
			// 		if( !empty($value) ){ 
			// 			$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
			// 		}
			// 	}
			// }
		
		$sqlVentas = $this->db->get_compiled_select();
		$this->db->reset_query();

		/* NOTA CREDITO */
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('SUM( CASE WHEN nc.estado_nc = 1 THEN (nc.monto::numeric) ELSE 0 END) AS total_importe',FALSE);
		$this->db->select('SUM(0) AS cantidad_venta',FALSE);
		$this->db->select('SUM(0) AS cantidad_anulado',FALSE);
		$this->db->select('COUNT(*) AS cantidad_ncr',FALSE);
		$this->db->from('caja_master cm'); 
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('nota_credito nc','c.idcaja = nc.idcaja');
		$this->db->where('estado_nc', 1); // nota crédito 
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('DATE(c.fecha_apertura) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);

		// if(!empty($allInputs['tipodocumento'])){ 
		// 	$this->db->where_in('v.idtipodocumento', $allInputs['tipodocumento']);
		// }
		if( $this->sessionHospital['key_group'] === 'key_caja'){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja,c.descripcion, c.fecha_apertura, c.fecha_cierre, cm.idcajamaster, descripcion_caja, numero_caja, serie_caja, u.idusers, u.username'); 
		if( $paramPaginate ){ 
			if( $paramPaginate['search'] ){ 
				foreach ($paramPaginate['searchColumn'] as $key => $value) { 
					if( !empty($value) ){ 
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					}
				}
			}
		}
		$sqlNotaCredito = $this->db->get_compiled_select();
		$this->db->reset_query();

		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster = 'SELECT SUM( CASE WHEN cantidad_ncr > 0 THEN 0 ELSE 1 END) AS contador, SUM(total_importe) AS total_importe,
			 SUM(cantidad_venta) AS cantidad_venta, SUM(cantidad_anulado) AS cantidad_anulado, 
			 SUM(cantidad_ncr) AS cantidad_ncr FROM ( '.$sqlMaster.' ) AS groupTotal';
		$query = $this->db->query($sqlMaster);
		$fData = $query->row_array();
		// $this->db->get()->row_array();
		return $fData;

		// $fila = $this->db->get()->row_array();
		// return $fila['contador'];
	}
	public function m_cargar_ventas_por_medio_pago($paramPaginate=FALSE, $allInputs) 
	{
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('mp.descripcion_med');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser');
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->where('estado_caja', 1);
		$this->db->where('v.estado', 1); //
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		// $this->db->where('DATE(c.fecha_apertura) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if(!empty($allInputs['tipodocumento'])){ 
			$this->db->where_in('v.idtipodocumento', $allInputs['tipodocumento']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja'){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('mp.idmediopago'); 
		// $this->db->order_by('mp.idmediopago'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_tipo_documento($allInputs) 
	{
		/* VENTAS */
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('cm.numero_caja,td.descripcion_td, MAX(c.fecha_apertura) AS fecha_apertura, MAX(u.username) AS username, MAX(v.idtipodocumento) AS idtipodocumento');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where('v.estado', 1); //
		$this->db->where('td.estado_td',1);
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if( $this->sessionHospital['key_group'] === 'key_caja' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster, td.idtipodocumento'); 
		
		$sqlVentas = $this->db->get_compiled_select();
		$this->db->reset_query();

		/* NOTA DE CREDITO */
		$this->db->select('SUM(CASE WHEN (v.estado = 1 AND nc.estado_nc = 1 ) THEN (nc.monto::numeric) ELSE 0 END) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select("cm.numero_caja,'NOTA DE CREDITO', MAX(c.fecha_apertura) AS fecha_apertura, MAX(u.username) AS username,'7'");
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('nota_credito nc','c.idcaja = nc.idcaja');
		$this->db->join('venta v','nc.idventa = v.idventa');
		//$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where('v.estado', 1); //
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if( $this->sessionHospital['key_group'] === 'key_caja' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster'); 
		
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster.= ' ORDER BY numero_caja ASC, idtipodocumento ASC';
		$this->db->reset_query(); // var_dump($sqlMaster); exit(); 
		$query = $this->db->query($sqlMaster);
		return $query->result_array();

		// $query = $this->db->query($sqlMaster);
		// return $this->db->get()->result_array();
		// $this->db->get()->row_array();
		// return $fData;

		// $this->db->order_by('cm.numero_caja,td.idtipodocumento'); 
		// return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_caja_y_tipo_documento($allInputs)
	{
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('cm.numero_caja,td.descripcion_td, MAX(v.idtipodocumento) AS idtipodocumento');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('venta v','c.idcaja = v.idcaja AND v.iduser = c.iduser');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where('td.estado_td',1);
		$this->db->where('v.estado', 1); //
		$this->db->where('v.idcaja', $allInputs['id']); //
		//$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if( $this->sessionHospital['key_group'] === 'key_caja' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster, td.idtipodocumento'); 

		$sqlVentas = $this->db->get_compiled_select();
		$this->db->reset_query();

		/* NOTA DE CREDITO */
		$this->db->select('SUM(CASE WHEN (v.estado = 1 AND nc.estado_nc = 1 ) THEN (nc.monto::numeric) ELSE 0 END) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select("cm.numero_caja,'NOTA DE CREDITO','7'");
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('nota_credito nc','c.idcaja = nc.idcaja');
		$this->db->join('venta v','nc.idventa = v.idventa');
		//$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->where('nc.idcaja', $allInputs['id']); //
		$this->db->where('estado_caja', 1);
		$this->db->where('v.estado', 1); //
		// $this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if( $this->sessionHospital['key_group'] === 'key_caja' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster'); 
		
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster.= ' ORDER BY numero_caja ASC, idtipodocumento ASC';
		$this->db->reset_query(); // var_dump($sqlMaster); exit(); 
		$query = $this->db->query($sqlMaster);
		return $query->result_array();

		// $this->db->order_by('cm.numero_caja,td.idtipodocumento'); 
		// return $this->db->get()->result_array();
	}
	/* ======================================= */
	/* ====== APERTURA DETALLE DE CAJA ======= */
	/* ======================================= */
	public function m_cargar_detalle_apertura_caja($paramPaginate, $paramDatos)
	{ 
		$this->db->select('idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, (total_a_pagar::numeric) AS total_a_pagar_format, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 1); // venta 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja no anulada  
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['id']);
		
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
	public function m_count_detalle_apertura_caja($paramPaginate, $paramDatos) 
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 1); // venta 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['id']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_validar_usuario_session_caja_abierta()
	{
		$this->db->select('c.idcaja, c.iduser, descripcion, estado, numero_caja, descripcion_caja, serie_caja, cm.idcajamaster'); 
		$this->db->from('caja c'); 
		$this->db->join('caja_master cm','c.idcajamaster = cm.idcajamaster'); 
		$this->db->where('estado', 1); // abierta 
		$this->db->where('c.iduser', $this->sessionHospital['idusers']); 
		// $this->db->where('idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		// $this->db->order_by('c.idcaja','DESC'); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_validar_caja_master_abierta($datos)
	{
		$this->db->select('c.idcaja, c.iduser, descripcion, estado, numero_caja, descripcion_caja'); 
		$this->db->from('caja c'); 
		$this->db->join('caja_master cm','c.idcajamaster = cm.idcajamaster'); 
		$this->db->where('estado', 1); // abierta 
		$this->db->where('cm.idcajamaster', $datos['idcajamaster']); 
		// $this->db->where('idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		// $this->db->order_by('c.idcaja','DESC'); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idempresaadmin' => $datos['idempresa'],
			'numero_caja' => $datos['numero'],
			'serie_caja' => $datos['serie'],
			'idmodulo' => $datos['idmodulo'],
			'descripcion_caja' => $datos['caja'],
			'maquina_registradora' => @$datos['maquina_registradora'],
		);
		return $this->db->insert('caja_master', $data);
	}
	public function m_registrar_documento_caja($datos)
	{
		$data = array(
			'idcajamaster' => $datos['idcajamaster'],
			'idtipodocumento' => $datos['id'],
			'numero_serie' => $datos['numero'] 
		);
		return $this->db->insert('documento_caja', $data);
	}
	public function m_editar($datos)
	{
		$data = array( 
			'numero_caja' => $datos['numero'],
			'serie_caja' => $datos['serie'],
			'descripcion_caja' => $datos['caja'],
			'maquina_registradora' => @$datos['maquina_registradora'],
		);
		$this->db->where('idcajamaster',$datos['id']);
		return $this->db->update('caja_master', $data);
	}
	public function m_editar_numero_serie($datos)
	{
		$data = array( 
			'numero_serie' => $datos['numeroserie']
		);
		$this->db->where('idcajamaster',$datos['idcajamaster']);
		$this->db->where('idtipodocumento',$datos['idtipodocumento']);
		return $this->db->update('documento_caja', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_caja' => 0,
		);
		$this->db->where('idcajamaster',$id);
		if($this->db->update('caja_master', $data)){
			return true;
		}else{
			return false;
		}
	}
}