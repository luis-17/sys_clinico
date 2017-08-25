<?php
class Model_caja_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_caja_actual_de_usuario($idModulo = 1){ // var_dump($idModulo); 
		if(empty($idModulo)){
			$idModulo = 1;
		} // var_dump($idModulo); exit();
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
			$this->db->where('idempresaadmin', $this->sessionHospital['idempresaadmin']); 
		}
		
		$this->db->order_by('c.idcaja','DESC'); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_apertura_caja($paramPaginate=FALSE, $paramDatos)
	{
		// exit();
		$this->db->select('c.idcaja, MAX(c.fecha_apertura) AS fecha_apertura, MAX(c.fecha_cierre) AS fecha_cierre, MAX(cm.idcajamaster) AS idcajamaster, MAX(serie_caja) AS serie_caja, 
			MAX(cm.descripcion_caja) AS descripcion_caja, MAX(cm.numero_caja) AS numero_caja, MAX(u.username) AS username,
			MAX(rhe.nombres) AS nombres, MAX(rhe.apellido_paterno) AS apellido_paterno, MAX(rhe.apellido_materno) AS apellido_materno');
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::NUMERIC) ELSE 0 END) AS total_importe',FALSE);
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 1 THEN 1 ELSE 0 END) AS cantidad_venta',FALSE);
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 0 THEN 1 ELSE 0 END) AS cantidad_anulado',FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC') THEN 1 ELSE 0 END ) AS cantidad_salidas",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC') THEN total_a_pagar::NUMERIC ELSE 0 END ) AS total_salidas",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC' AND fm.tipo_nota_credito = 1) THEN 1 ELSE 0 END ) AS cantidad_ncr",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC' AND fm.tipo_nota_credito = 1) THEN total_a_pagar::NUMERIC ELSE 0 END ) AS total_ncr",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC' AND fm.tipo_nota_credito = 2) THEN 1 ELSE 0 END ) AS cantidad_extorno",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC' AND fm.tipo_nota_credito = 2) THEN total_a_pagar::NUMERIC ELSE 0 END ) AS total_extorno",FALSE);
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster'); 
		$this->db->join('users u','c.iduser = u.idusers'); 
		$this->db->join('rh_empleado rhe','c.iduser = rhe.iduser'); 
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento'); 
		$this->db->where('estado_caja', 1); 
		$this->db->where_in('fm.estado_movimiento', array(0,1)); 
		$this->db->where('fm.tipo_movimiento = 1'); // VENTA 
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		if( empty($paramDatos['sedeempresa']) ){
			$this->db->where('c.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}else{
			$this->db->where('c.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $paramPaginate ){
			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
			if(!empty($paramDatos['tipodocumento'])){
				$this->db->where_in('fm.idtipodocumento', $paramDatos['tipodocumento']);
			}
			if( $paramPaginate['sortName'] ){ 
				$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
			}
			if( $paramPaginate['pageSize'] ){ 
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}else{ // SOLO REPORTES 
			$this->db->order_by('fecha_apertura','DESC');
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}

		$this->db->group_by('c.idcaja');
		return $this->db->get()->result_array();
	}
	public function m_count_sum_apertura_caja($paramPaginate=FALSE, $paramDatos) 
	{
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::NUMERIC) ELSE 0 END) AS total_importe',FALSE);
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 1 THEN 1 ELSE 0 END) AS cantidad_venta',FALSE);
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 0 THEN 1 ELSE 0 END) AS cantidad_anulado',FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC') THEN 1 ELSE 0 END ) AS cantidad_salidas",FALSE);
		$this->db->select("SUM( CASE WHEN (fm.estado_movimiento = 1 AND td.abreviatura = 'NC') THEN total_a_pagar::NUMERIC ELSE 0 END ) AS total_salidas",FALSE);
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster'); 
		$this->db->join('users u','c.iduser = u.idusers'); 
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento'); 
		$this->db->where('estado_caja', 1); 
		$this->db->where_in('fm.estado_movimiento', array(0,1)); 
		$this->db->where('fm.tipo_movimiento = 1'); // VENTA 
		$this->db->where('c.estado <>', 0); // abierta y cerrada 
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		if( empty($paramDatos['sedeempresa']) ){
			$this->db->where('c.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}else{
			$this->db->where('c.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $paramPaginate ){ 
			if( $paramPaginate['search'] ){ 
				foreach ($paramPaginate['searchColumn'] as $key => $value) { 
					if( !empty($value) ){ 
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					}
				}
			}
			if(!empty($paramDatos['tipodocumento'])){ 
				$this->db->where_in('fm.idtipodocumento', $paramDatos['tipodocumento']);
			}
		} 
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja'); 
		$sqlVentas = $this->db->get_compiled_select();
		$this->db->reset_query();

		$sqlMaster = 'SELECT 
			SUM(dc.total_importe) AS total_importe, 
			SUM(dc.cantidad_venta) AS cantidad_venta,
			SUM(dc.cantidad_anulado) AS cantidad_anulado, 
			SUM(dc.cantidad_salidas) AS cantidad_salidas,
			SUM(dc.total_salidas) AS total_salidas,
			COUNT(*) AS contador FROM ( '.$sqlVentas.' ) AS dc';
		$query = $this->db->query($sqlMaster);
		$fData = $query->row_array();
		return $fData;
	}
	public function m_cargar_ventas_por_medio_pago($paramPaginate=FALSE, $allInputs) 
	{
		// SUBCONSULTA 1
		$this->db->select('(total_a_pagar)::NUMERIC AS monto, mp.descripcion_med');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->where('estado_caja', 1);
		$this->db->where('fm.estado_movimiento', 1); //
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('fm.idmediopago <>',6); // AL CONTADO Y TARJETA
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$sqlVentas1 = $this->db->get_compiled_select();
		$this->db->reset_query();

		// SUBCONSULTA 1
		$this->db->select('(monto)::NUMERIC AS monto, mp.descripcion_med');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser');
		$this->db->join('far_pago_mixto fpm','fm.idmovimiento = fpm.idmovimiento AND fpm.estado_pago = 1','left');
		$this->db->join('medio_pago mp','fpm.idmediopago = mp.idmediopago','left');
		$this->db->where('estado_caja', 1);
		$this->db->where('fm.estado_movimiento', 1); //
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']);
		$this->db->where('fm.idmediopago',6); // PAGO MIXTO
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$sqlVentas2 = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('SUM( monto ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('descripcion_med');
		$this->db->from( '('. $sqlVentas1 . ' UNION ALL ' . $sqlVentas2 . ') AS foo' );
		$this->db->group_by('descripcion_med'); 
		/* 
		// CONSULTA ANTERIOR	
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('mp.descripcion_med');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->where('estado_caja', 1);
		$this->db->where('fm.estado_movimiento', 1); //
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		// $this->db->where('DATE(c.fecha_apertura) BETWEEN '. $this->db->escape($allInputs['desde']) .' AND ' . $this->db->escape($allInputs['hasta']));
		$this->db->where('c.idsedeempresaadmin', $allInputs['sedeempresa']); 
		if(!empty($allInputs['tipodocumento'])){ 
			$this->db->where_in('fm.idtipodocumento', $allInputs['tipodocumento']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('mp.idmediopago'); 
		// $this->db->order_by('mp.idmediopago'); */
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_tipo_documento($paramDatos=FALSE) 
	{
		/* VENTAS */
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('cm.numero_caja,td.descripcion_td, MAX(c.fecha_apertura) AS fecha_apertura, MAX(u.username) AS username, MAX(fm.idtipodocumento) AS idtipodocumento');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where_in('fm.estado_movimiento', array(1)); // VENTA ACTIVA
		$this->db->where('fm.tipo_movimiento = 1'); // VENTA 
		$this->db->where('td.estado_td',1);
		$this->db->where('"c".fecha_apertura BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		//$this->db->where('c.idsedeempresaadmin', $paramDatos['sedeempresa']); 
		if( empty($paramDatos['sedeempresa']) ){
			$this->db->where('c.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}else{
			$this->db->where('c.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster, td.idtipodocumento'); 
		$this->db->order_by('numero_caja','ASC');
		$this->db->order_by('idtipodocumento','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_por_caja_y_tipo_documento($allInputs)
	{
		$this->db->select('SUM( total_a_pagar::numeric ) AS total',FALSE);
		$this->db->select('COUNT(*) AS cantidad',FALSE);
		$this->db->select('cm.numero_caja,td.descripcion_td, MAX(fm.idtipodocumento) AS idtipodocumento');
		$this->db->from('caja_master cm');
		$this->db->join('caja c','cm.idcajamaster = c.idcajamaster');
		$this->db->join('users u','c.iduser = u.idusers');
		$this->db->join('far_movimiento fm','c.idcaja = fm.idcaja AND fm.iduser = c.iduser');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento');
		$this->db->where('estado_caja', 1);
		$this->db->where_in('fm.estado_movimiento', array(1)); // VENTA ACTIVA
		$this->db->where('fm.tipo_movimiento = 1'); // VENTA 
		$this->db->where('td.estado_td',1);
		$this->db->where('fm.idcaja', $allInputs['id']); 
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ){ 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']);
		}
		$this->db->group_by('c.idcaja, cm.idcajamaster, td.idtipodocumento'); 
		$this->db->order_by('numero_caja','ASC');
		$this->db->order_by('idtipodocumento','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_apertura_caja($paramPaginate=FALSE, $paramDatos)
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, (total_a_pagar::NUMERIC) AS total_a_pagar_format,fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		//$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // vendido y anulado 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.idcaja', $paramDatos['id']); 
		$this->db->where('cm.estado_caja', 1); // caja master 
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
	public function m_count_sum_detalle_apertura_caja($paramPaginate=FALSE, $paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::NUMERIC) ELSE 0 END) AS total_importe',FALSE); 
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 1 THEN 1 ELSE 0 END) AS cantidad_venta',FALSE);
		$this->db->select('SUM( CASE WHEN fm.estado_movimiento = 0 THEN 1 ELSE 0 END) AS cantidad_anulado',FALSE);
		$this->db->select("SUM( CASE WHEN td.abreviatura = 'NC' THEN total_a_pagar::NUMERIC ELSE 0 END ) AS total_salidas",FALSE);
		$this->db->select("SUM( CASE WHEN td.abreviatura = 'NC' THEN 1 ELSE 0 END ) AS cantidad_salidas",FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		//$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		//$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // vendido y anulado 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.idcaja', $paramDatos['id']); 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}
		$fData = $this->db->get()->row_array(); 
		return $fData;
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
	public function m_cargar_cajas_diarias_usuario($allInputs)
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
		//$this->db->where('c.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('DATE(c.fecha_apertura)', $allInputs['fecha']);
		$this->db->where('u.idusers', $allInputs['usuario']['id']);
		$this->db->where('cm.idcajamaster', $allInputs['caja']['id']);
		$this->db->order_by('fecha_apertura');
		return $this->db->get()->result_array();
	}

	public function m_cargar_ventas_usuario_caja($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		
		$this->db->select('fm.idmovimiento, fm.fecha_movimiento, cjm.numero_caja,');
		$this->db->select('fm.idtipodocumento, td.descripcion_td, fm.total_a_pagar, (fm.total_a_pagar::numeric) AS monto');
		$this->db->select("(empl.nombres || ' ' ||empl.apellido_paterno || ' ' || empl.apellido_materno) AS empleado",FALSE);		 
		$this->db->from('far_movimiento fm');
		$this->db->join('users us','us.idusers = fm.iduser AND us.estado_usuario = 1');
		$this->db->join('rh_empleado empl','empl.iduser = us.idusers AND empl.estado_empl = 1'); 
		$this->db->join('tipo_documento td','td.idtipodocumento = fm.idtipodocumento AND td.estado_td  <> 0');
		$this->db->join('caja cj','cj.idcaja = fm.idcaja AND cj.estado <> 0'); 
		$this->db->join('caja_master cjm','cjm.idcajamaster = cj.idcajamaster AND cj.estado <> 0'); 
		$this->db->where('fm.estado_movimiento <>', 0);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($allInputs['desde']).' AND ' . $this->db->escape($allInputs['hasta']));				

		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->order_by("(empl.nombres || ' ' ||empl.apellido_paterno || ' ' || empl.apellido_materno) ASC ",FALSE);
		return $this->db->get()->result_array();
	}

	public function m_cargar_ventas_usuario_caja_detalle($allInputs){ 
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		if($allInputs['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$allInputs['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		
		$this->db->select('fm.idmovimiento, fm.fecha_movimiento, cjm.numero_caja, med.denominacion');
		$this->db->select('fm.idtipodocumento, td.descripcion_td, lab.nombre_lab');
		$this->db->select('fdm.total_detalle,(fdm.total_detalle::numeric) AS monto');
		$this->db->select("(empl.nombres || ' ' ||empl.apellido_paterno || ' ' || empl.apellido_materno) AS empleado",FALSE);		 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm','fdm.idmovimiento = fm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('medicamento med','med.idmedicamento = fdm.idmedicamento');
		$this->db->join('far_laboratorio lab','lab.idlaboratorio = med.idlaboratorio');
		$this->db->join('users us','us.idusers = fm.iduser AND us.estado_usuario = 1');
		$this->db->join('rh_empleado empl','empl.iduser = us.idusers AND empl.estado_empl = 1'); 
		$this->db->join('tipo_documento td','td.idtipodocumento = fm.idtipodocumento AND td.estado_td  <> 0');
		$this->db->join('caja cj','cj.idcaja = fm.idcaja AND cj.estado <> 0'); 
		$this->db->join('caja_master cjm','cjm.idcajamaster = cj.idcajamaster AND cj.estado <> 0'); 
		$this->db->where('fm.estado_movimiento <>', 0);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($allInputs['desde']).' AND ' . $this->db->escape($allInputs['hasta']));				

		$this->db->where('fm.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->order_by("(empl.nombres || ' ' ||empl.apellido_paterno || ' ' || empl.apellido_materno) ASC ",FALSE);
		$this->db->order_by("fm.fecha_movimiento ASC, fdm.idmovimiento",FALSE);
		return $this->db->get()->result_array();
	}
} 