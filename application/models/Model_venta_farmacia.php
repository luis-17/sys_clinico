<?php
class Model_venta_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ventas_esta_caja_farmacia($paramDatos=FALSE,$paramPaginate=FALSE) 
	{ 
		/* VENTAS */
		$this->db->select("(CASE WHEN fm.estado_movimiento = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("total_a_pagar::numeric",FALSE);
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, orden_venta, fm.idtipocliente,
			fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td,
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email");  
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago','left');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // activos y anulados
		$this->db->where('tipo_movimiento', 1);
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('fm.idtipodocumento <>', 7); // SOLO VENTAS
		$this->db->order_by('td.idtipodocumento');
		$this->db->order_by('ticket_venta');		

		return $this->db->get()->result_array();
	}
	public function m_cargar_nc_esta_caja_farmacia($paramDatos=FALSE,$paramPaginate=FALSE) 
	{
		/* NOTAS DE CREDITO */
		$this->db->select("fm.total_a_pagar::numeric",FALSE);
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, fm.orden_venta, fm.idtipocliente, fm.fecha_movimiento, 
			fm.ticket_venta, td.idtipodocumento, descripcion_td, fm.idventaorigen,
			fm_or.orden_venta AS orden_venta_origen, fm_or.ticket_venta AS ticket_venta_origen, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email");  
		$this->db->from('far_movimiento fm'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('far_movimiento fm_or', 'fm.idventaorigen = fm_or.idmovimiento AND fm_or.estado_movimiento = 1 AND fm_or.es_preparado = 2');
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // activos y anulados
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('cj.estado <>', 0); // caja abierta y cerrada
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.idcaja', $paramDatos['idcaja']);
		$this->db->where('fm.idtipodocumento', 7); // SOLO NOTAS DE CREDITO
		//$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if($paramPaginate){
			if( $paramPaginate['search'] ){ 
				foreach ($paramPaginate['searchColumn'] as $key => $value) { 
					if( !empty($value) ){ 
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					}
				}
			} 
			// var_dump($sqlVentas,$sqlNotaCredito); exit();
			if( $paramPaginate['sortName'] ){
				$sqlMaster.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
			}else{
				$sqlMaster.= ' ORDER BY ticket_venta';
			}
			if($paramPaginate['pageSize'] ){
				$sqlMaster.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
			}
		}
		

		return $this->db->get()->result_array();
	}
	public function m_cargar_pedidos_ventas_por_aprobar($paramPaginate,$paramDatos)
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, es_pedido,
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			fm.orden_pedido, fm.iduser,fm.es_temporal, rhe.nombres as nombre_vendedor, rhe.apellido_paterno as apellido_vendedor, 
			fm.idtipocliente
		');

		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->where_in('fm.estado_movimiento', array(1,3)); // solo pedidos activos
		$this->db->where('tipo_movimiento', 1); // venta  
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('fm.es_pedido', 1); // pedidos de venta
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));
		if( !empty($paramDatos['sedeempresa']) ){
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		// if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
		// 	$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		// } 
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
	public function m_count_pedidos_ventas_por_aprobar($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total'); 
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
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.es_pedido', 1); // pedidos de venta
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		} 
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
	public function m_cargar_ventas_caja_actual($paramPaginate,$paramDatos)
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, fm.idtipocliente, tc.descripcion_tc,
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, (total_a_pagar)::NUMERIC AS total_a_pagar_sf, fm.es_preparado
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago', 'left');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('tipo_cliente tc','fm.idtipocliente = tc.idtipocliente','left');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // vendido y anulado 
		//$this->db->where_in('fm.es_temporal', array(2,3)); // NO ES TEMPORAL 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.es_pedido', 2); // que no sea pedido
		// $this->db->where('fm.es_preparado', 2); // que no sea preparado
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
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
	public function m_count_sum_ventas_caja_actual($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago','left');
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
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.es_pedido', 2); // que no sea pedido
		// $this->db->where('fm.es_preparado', 2); // que no sea preparado
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		} 
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
	public function m_cargar_ventas_anuladas_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
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
		$this->db->where_in('fm.estado_movimiento', array(0)); // anulado 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.es_pedido', 2); // que no sea pedido
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
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
	public function m_count_sum_ventas_anuladas_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_a_pagar) AS suma_total'); 
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
		$this->db->where_in('fm.estado_movimiento', array(0)); // vendido y anulado 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.es_pedido', 2); // que no sea pedido
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		} 
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
	public function m_cargar_ventas_en_espera_caja_actual($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email'); 
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
		
		$this->db->where_in('fm.estado_movimiento', array(3)); // en espera 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
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
	public function m_count_ventas_en_espera_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
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
		$this->db->where_in('fm.estado_movimiento', array(3)); // en espera 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
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
	public function m_count_sum_ventas_en_espera_caja_actual($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_a_pagar) AS suma_total'); 
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
		$this->db->where_in('fm.estado_movimiento', array(3)); // vendido y anulado 
		$this->db->where('tipo_movimiento', 1); // venta  
		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		} 
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
	public function m_cargar_producto_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, 
			m.idmedicamento, m.denominacion, 
			fl.idlaboratorio, fl.nombre_lab 
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio AND estado_lab = 1','left'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left'); 
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where('fdm.estado_detalle', 1); // 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']); 
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
		} 
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
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
	public function m_count_sum_producto_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle) AS suma_total'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio AND estado_lab = 1','left'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left'); 
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->where('fm.estado_movimiento', 1); //  
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']); 
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
		} 
		if( $this->sessionHospital['key_group'] === 'key_caja_far' ) { 
			$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		} 
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
	/* REIMPRESIONES */
	public function m_cargar_ventas_con_solicitud_impresion($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('idmedico, med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres');
		$this->db->select("(emp.nombres) AS caj_nombre, (emp.apellido_paterno) AS caj_apellido_pat, (emp.apellido_materno) AS caj_apellido_mat", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, fm.fecha_movimiento, fm.sub_total, fm.total_igv, fm.total_a_pagar , ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, solicita_impresion, tiene_impresion, tiene_reimpresion
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
		$this->db->join('rh_empleado emp','u.idusers = emp.iduser'); 
		$this->db->join('medico med','fm.idmedicosolicitud = med.idmedico','left');
		//$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('fm.solicita_impresion', 1); // si solicita impresión 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
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
	public function m_count_ventas_con_solicitud_impresion($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('rh_empleado emp','u.idusers = emp.iduser'); 
		$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('fm.solicita_impresion', 1); // si solicita impresión 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_cargar_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('idmedico, med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres');
		$this->db->select("(emp.nombres) AS caj_nombre, (emp.apellido_paterno) AS caj_apellido_pat, (emp.apellido_materno) AS caj_apellido_mat", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, fm.fecha_movimiento, fm.sub_total, fm.total_igv, fm.total_a_pagar , ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, solicita_impresion, tiene_impresion, tiene_reimpresion
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
		$this->db->join('rh_empleado emp','u.idusers = emp.iduser'); 
		$this->db->join('medico med','fm.idmedicosolicitud = med.idmedico','left');
		//$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('fm.solicita_impresion', array(1,3)); // si solicita impresión 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
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
	public function m_count_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador', FALSE);
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
		$this->db->join('rh_empleado emp','u.idusers = emp.iduser'); 
		//$this->db->join('medico med','fm.idmedicosolicitud = med.idmedico','left');
		//$this->db->where('fm.estado_movimiento', 1); // 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('fm.solicita_impresion', array(1,3)); // si solicita impresión 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']); 
		} 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultimo_pedido_venta($paramDatos)
	{ 
		$this->db->select('fm.idmovimiento, orden_pedido');
		$this->db->from('far_movimiento fm');
		// $this->db->where('estado', 1); // ya no se pone filtro porque el codigo generado tendrá que ser diferente asi esté anulado
		$this->db->where('idsedeempresaadmin', $paramDatos['idsedeempresaadmin']); 
		// $this->db->where('dir_movimiento', $paramDatos['dir_movimiento']);
		$this->db->where('orden_pedido <>', NULL);
		// $this->db->order_by('orden_venta','DESC'); 
		$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultima_venta_caja($paramDatos)
	{ 
		$this->db->select('fm.idmovimiento, orden_venta');
		$this->db->from('far_movimiento fm');
		// $this->db->where('estado', 1); // ya no se pone filtro porque el codigo generado tendrá que ser diferente asi esté anulado
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('idcaja', $paramDatos['idcaja']); 
		$this->db->where('dir_movimiento', $paramDatos['dir_movimiento']);
		$this->db->where('idtipodocumento <>', 7);
		$this->db->order_by('orden_venta','DESC'); 
		//$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultimo_codigo_pedido_formula()
	{ 
		$this->db->select('fm.idmovimiento, codigo_pedido');
		$this->db->from('far_movimiento fm');
		$this->db->where('es_preparado', 1);
		$this->db->where('codigo_pedido IS NOT NULL');
		$this->db->where('idventaorigen IS NULL');
		$this->db->order_by('codigo_pedido','DESC'); 
		//$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_venta_por_columna($paramDatos) // estado_acuenta idventaorigen 
	{
		$this->db->select('fm.idmovimiento, orden_venta,  estado_movimiento,
			(sub_total)::NUMERIC, (total_igv)::NUMERIC, (total_a_pagar)::NUMERIC, (total_igv_exonerado)::NUMERIC, 
			fecha_movimiento, ticket_venta, es_pedido, puntos_ganados, puntos_no_ganados, (total_sin_redondeo)::NUMERIC, (redondeo)::NUMERIC, 
			ec.idempresacliente, ec.descripcion AS empresa_cliente, ec.ruc_empresa AS ruc_cliente,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa, ea.nombre_legal, ea.ruc, ea.domicilio_fiscal, s.direccion_se, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, cm.maquina_registradora, numero_caja, serie_caja, descripcion_caja, 
			mp.idmediopago, descripcion_med, td.idtipodocumento, descripcion_td, 
			rhe.nombres as nombres_vendedor, rhe.apellido_paterno as apellido_vendedor, tiene_impresion, tiene_reimpresion, solicita_impresion',FALSE);
		$this->db->select('fm.idsolicitudformula, fm.idventaorigen, fm.es_preparado, fm.saldo::NUMERIC, codigo_pedido', FALSE);
		$this->db->select("c.idcliente, UPPER(CONCAT(c.apellido_paterno,' ',c.apellido_materno,', ',c.nombres)) AS cliente,
			fm.idcliente_afiliado, UPPER(CONCAT(ca.apellido_paterno,' ',ca.apellido_materno,', ',ca.nombres)) AS cliente_afiliado",FALSE); 
		$this->db->select("(SELECT SUM(CASE WHEN med.excluye_igv = 1 THEN fdm.total_detalle::NUMERIC ELSE 0 END )
			FROM far_detalle_movimiento fdm 
			INNER JOIN medicamento med ON fdm.idmedicamento = med.idmedicamento 
			WHERE fdm.idmovimiento = fm.idmovimiento) AS total_monto_exonerado, (fm.vuelto)::NUMERIC");
		$this->db->from('far_movimiento fm');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente ca','fm.idcliente_afiliado = ca.idcliente','left');
		$this->db->join('cliente c','fm.idcliente = c.idcliente','left');
		$this->db->join('empresa_cliente ec','fm.idempresacliente = ec.idempresacliente', 'left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where($paramDatos['searchColumn'], $paramDatos['searchText']); 
		// $this->db->group_by('fm.idmovimiento');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_movimiento_anterior($idMovimientoAnterior)
	{
		$this->db->select('fm.idmovimiento, orden_venta, estado_movimiento, (sub_total)::NUMERIC, (total_igv)::NUMERIC, (total_a_pagar)::NUMERIC, (total_igv_exonerado)::NUMERIC, 
			fecha_movimiento, ticket_venta, es_pedido, puntos_ganados, puntos_no_ganados, (total_sin_redondeo)::NUMERIC, (redondeo)::NUMERIC, 
			ec.idempresacliente, ec.descripcion AS empresa_cliente, ec.ruc_empresa AS ruc_cliente,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa, ea.nombre_legal, ea.ruc, ea.domicilio_fiscal, s.direccion_se, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, cm.maquina_registradora, numero_caja, serie_caja, descripcion_caja, 
			mp.idmediopago, descripcion_med, td.idtipodocumento, descripcion_td, 
			rhe.nombres as nombres_vendedor, rhe.apellido_paterno as apellido_vendedor, tiene_impresion, tiene_reimpresion, solicita_impresion');
		$this->db->select('fm.idsolicitudformula, fm.idventaorigen, fm.es_preparado, fm.saldo::NUMERIC, codigo_pedido', FALSE);
		$this->db->select("c.idcliente, UPPER(CONCAT(c.apellido_paterno,' ',c.apellido_materno,', ',c.nombres)) AS cliente,
			fm.idcliente_afiliado, UPPER(CONCAT(ca.apellido_paterno,' ',ca.apellido_materno,', ',ca.nombres)) AS cliente_afiliado",FALSE); 
		$this->db->select("(SELECT SUM(CASE WHEN med.excluye_igv = 1 THEN fdm.total_detalle::NUMERIC ELSE 0 END )
			FROM far_detalle_movimiento fdm 
			INNER JOIN medicamento med ON fdm.idmedicamento = med.idmedicamento 
			WHERE fdm.idmovimiento = fm.idmovimiento) AS total_monto_exonerado, (fm.vuelto)::NUMERIC");
		$this->db->from('far_movimiento fm');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente ca','fm.idcliente_afiliado = ca.idcliente','left');
		$this->db->join('cliente c','fm.idcliente = c.idcliente','left');
		$this->db->join('empresa_cliente ec','fm.idempresacliente = ec.idempresacliente', 'left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('fm.idmovimiento', $idMovimientoAnterior);
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_pedido_por_columna($paramDatos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('fm.idmovimiento, orden_venta, estado_movimiento, sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, es_pedido,
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa, ea.ruc, 
			
			ec.idempresacliente, ec.descripcion AS empresa_cliente, ec.ruc_empresa AS ruc_cliente');
		$this->db->from('far_movimiento fm');
		$this->db->join('cliente c','fm.idcliente = c.idcliente','left');
		$this->db->join('empresa_cliente ec','fm.idempresacliente = ec.idempresacliente', 'left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where($paramDatos['searchColumn'], $paramDatos['searchText']);
		$this->db->where_in('estado_movimiento', array(1,3));
		//$this->db->where('es_pedido', 1);
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_venta_con_detalle_por_columna($paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		// if($paramDatos['es_preparado']){
			// $this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			// sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, fm.idsolicitudformula, fm.saldo,
			// (fdm.cantidad)::NUMERIC, (fdm.precio_unitario)::NUMERIC, (fdm.descuento_asignado)::NUMERIC, fdm.total_detalle, 
			// m.idmedicamento, td.idtipodocumento, descripcion_td, (fdm.total_detalle)::NUMERIC AS monto_sf'
			// );
		// } else {
		// 	$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
		// 	sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, 
		// 	(fdm.cantidad)::NUMERIC, (fdm.precio_unitario)::NUMERIC, (fdm.descuento_asignado)::NUMERIC, fdm.total_detalle, 
		// 	m.idmedicamento, td.idtipodocumento, descripcion_td, ff.descripcion_ff, fl.nombre_lab, (fdm.total_detalle)::NUMERIC AS monto_sf'
		// 	);
		// }
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, fm.ticket_venta,
			fdm.idmedicamentoalmacen,fdm.iddetallemovimiento,
			(fdm.cantidad)::NUMERIC, (fdm.precio_unitario)::NUMERIC, (fdm.descuento_asignado)::NUMERIC, fdm.total_detalle, 
			m.idmedicamento, td.idtipodocumento, descripcion_td, ff.descripcion_ff, fl.nombre_lab, (fdm.total_detalle)::NUMERIC AS monto_sf'
		);
		$this->db->from('far_movimiento fm');
		// if($paramDatos['es_preparado']){
		// 	$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1 AND fm.idtipodocumento <> 7'); 
		// 	$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		// 	$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		// } else {
			$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1 AND fm.idtipodocumento <> 7'); 
			$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
			$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');

			$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left');
			$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		// }
		
		$this->db->where('fdm.estado_detalle', 1); // venta 
		$this->db->where($paramDatos['searchColumn'], $paramDatos['searchText']); 
		return $this->db->get()->result_array();		
	}
	public function m_cargar_productos_almacen_de_esta_venta($idmovimiento)
	{
		$this->db->select('fma.idmedicamentoalmacen, fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, 
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, 
			m.idmedicamento, denominacion, fdm.idrecetamedicamento, fdm.idreceta, fm.idsolicitudformula
		');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen'); 
		$this->db->join('medicamento m','fma.idmedicamento = m.idmedicamento'); 
		$this->db->where('fm.idmovimiento', $idmovimiento); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_preparados_almacen_de_esta_venta($idmovimiento)
	{
		$this->db->select('fma.idmedicamentoalmacen, fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, 
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, 
			m.idmedicamento, denominacion, fdm.idrecetamedicamento, fdm.idreceta, fm.idsolicitudformula, fds.iddetallesolicitud
		');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen'); 
		$this->db->join('medicamento m','fma.idmedicamento = m.idmedicamento');
		$this->db->join('far_detalle_solicitud fds','fm.idsolicitudformula = fds.idsolicitudformula AND fma.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->where('fm.idmovimiento', $idmovimiento); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_detalle_venta_caja_actual($paramPaginate,$paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, ticket_venta,
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, 
			m.idmedicamento, m.denominacion, 
			fl.idlaboratorio, fl.nombre_lab
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['id']);
		$this->db->where('fdm.estado_detalle', 1); 
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_sum_detalle_venta_caja_actual($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle) AS sumatotal'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['id']); 
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	public function m_cargar_detalle_pedido($paramPaginate,$paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.idtipocliente,
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, fdm.idmedicamentoalmacen,
			m.idmedicamento, m.denominacion, 
			fl.idlaboratorio, fl.nombre_lab
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['id']); 
		$this->db->where('fdm.estado_detalle', 1); // activo
		// if( $paramPaginate['sortName'] ){ 
		// 	$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		// }
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_sum_detalle_pedido($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle) AS sumatotal'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['id']);
		$this->db->where('fdm.estado_detalle', 1); // activo 
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	public function m_cargar_ordenes_venta_cajas_cerradas($datos)
	{
		$this->db->select('fm.idmovimiento, fm.idcliente,idempresacliente,orden_venta, ticket_venta, cl.nombres, cl.apellido_paterno, cl.apellido_materno, fm.total_a_pagar, (fm.total_a_pagar::numeric) AS total_a_pagar_format');
		$this->db->from('far_movimiento fm');
		$this->db->join('caja c','fm.idcaja = c.idcaja'); 
		$this->db->join('cliente cl','fm.idcliente = cl.idcliente','left');
		$this->db->where('fm.estado_movimiento', 1); // venta // activo
		$this->db->where('c.estado', 2); // caja // cerrada
		$this->db->where('fm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->ilike('orden_venta', $datos['search']); 
		$this->db->order_by('idmovimiento','DESC');
		$this->db->limit(6); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_ordenes_venta($datos)
	{
		$this->db->select('fm.idmovimiento, fm.idcliente, fm.idempresacliente, fm.orden_venta, fm.ticket_venta, fm.fecha_movimiento AS fecha_venta');
		$this->db->select('fm.total_a_pagar, (fm.total_a_pagar::numeric) AS total_a_pagar_format, es_preparado');
		$this->db->select('cl.nombres, cl.apellido_paterno, cl.apellido_materno');
		$this->db->select('ec.descripcion AS empresa_cliente');
		$this->db->from('far_movimiento fm');
		$this->db->join('caja c','fm.idcaja = c.idcaja'); 
		$this->db->join('cliente cl','fm.idcliente = cl.idcliente','left');
		$this->db->join('empresa_cliente ec','fm.idempresacliente = ec.idempresacliente','left');
		$this->db->where('fm.estado_movimiento', 1); // venta // activo
		$this->db->where('fm.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->ilike('orden_venta', $datos['search']); 
		$this->db->order_by('idmovimiento','DESC');
		$this->db->limit(6); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_venta_descuento($idmovimiento)
	{
		$this->db->select('idmedicamento, cantidad, idmedicamentoalmacen');
		$this->db->from('far_detalle_movimiento');
		$this->db->where('idmovimiento', $idmovimiento);
		return $this->db->get()->result_array();
	}
	public function m_cargar_ventas_farm_desde_hasta($paramDatos=FALSE)
	{
		/* VENTAS */
		$this->db->select("(CASE WHEN fm.estado_movimiento = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("total_a_pagar::NUMERIC, sub_total::NUMERIC, total_igv::NUMERIC, redondeo::NUMERIC, total_sin_redondeo::NUMERIC",FALSE);
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, orden_venta, fm.idtipocliente,
			fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td,
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, u.username, u.email");
		$this->db->select("(SELECT SUM(CASE WHEN med.excluye_igv = 1 THEN fdm.total_detalle::NUMERIC ELSE 0 END )
			FROM far_detalle_movimiento fdm 
			INNER JOIN medicamento med ON fdm.idmedicamento = med.idmedicamento 
			WHERE fdm.idmovimiento = fm.idmovimiento) AS total_monto_exonerado");
		$this->db->from('far_movimiento fm');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago','left');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // activos y anulados
		$this->db->where('tipo_movimiento', 1);
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master
		$this->db->where('fm.idsedeempresaadmin',$paramDatos['sedeempresa'] );
		$this->db->where('fm.idtipodocumento <>', 7); // SOLO VENTAS
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( $paramDatos['modalidadTipo']['id'] != 'ALL'){
			$this->db->where('fm.es_preparado',$paramDatos['modalidadTipo']['id'] );
		}
		$this->db->order_by('td.idtipodocumento');
		$this->db->order_by('ticket_venta');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nc_farmacia($paramDatos=FALSE) 
	{
		/* NOTAS DE CREDITO */
		$this->db->select("fm.total_a_pagar::numeric",FALSE);
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, fm.orden_venta, fm.idtipocliente, fm.fecha_movimiento, 
			fm.ticket_venta, td.idtipodocumento, descripcion_td, fm.idventaorigen,
			fm_or.orden_venta AS orden_venta_origen, fm_or.ticket_venta AS ticket_venta_origen, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email");  
		$this->db->from('far_movimiento fm'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('far_movimiento fm_or', 'fm.idventaorigen = fm_or.idmovimiento AND fm_or.estado_movimiento = 1');
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // activos y anulados
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('cj.estado <>', 0); // caja abierta y cerrada
		$this->db->where('cm.estado_caja', 1); // caja master
		//$this->db->where('fm.idcaja', $paramDatos['idcaja']);
		$this->db->where('fm.idtipodocumento', 7); // SOLO NOTAS DE CREDITO
		//$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamentos_vendidos_desde_hasta($paramDatos){
		$this->db->select("m.idmedicamento, m.denominacion, fl.nombre_lab, SUM(fdm.cantidad)::NUMERIC AS cantidad, SUM(fdm.total_detalle)::NUMERIC AS monto",FALSE);
		 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_laboratorio fl','fl.idlaboratorio = m.idlaboratorio');
		if (!empty($paramDatos['formula_derma']) && $paramDatos['formula_derma']) {
			$this->db->join('far_forma_farmaceutica fff','fff.idformafarmaceutica = m.idformafarmaceutica');
		}

		$this->db->where('fm.estado_movimiento <>', 0); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if (!empty($paramDatos['formula_derma']) && $paramDatos['formula_derma']) {
			$this->db->where("fff.descripcion_ff NOT LIKE '%CREMA%'");
		}
		$this->db->group_by('m.idmedicamento, m.denominacion, fl.nombre_lab');
		if($paramDatos['modalidad']){
			$this->db->order_by($paramDatos['modalidad']['id'], $paramDatos['ordenamiento']);
			$this->db->order_by('m.denominacion', 'ASC');
		}else{
			$this->db->order_by('m.denominacion', 'ASC');
		}
		if($paramDatos['top']){
				$this->db->limit($paramDatos['top']['id']);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamentos_comprados_desde_hasta($paramDatos){
		$this->db->select("m.idmedicamento, m.denominacion, SUM(fdm.cantidad)::NUMERIC AS cantidad, SUM(fdm.total_detalle)::NUMERIC AS monto",FALSE);
		 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 2);
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->group_by('m.idmedicamento, m.denominacion');
		if($paramDatos['modalidad']){
			$this->db->order_by($paramDatos['modalidad']['id'], $paramDatos['ordenamiento']);
			$this->db->order_by('m.denominacion', 'ASC');
		}else{
			$this->db->order_by('m.denominacion', 'ASC');
		}
		if($paramDatos['top']){
				$this->db->limit($paramDatos['top']['id']);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_en_ventas_desde_hasta($paramDatos){
		$this->db->select("ms.med_numero_documento, ms.med_apellido_paterno, ms.med_apellido_materno, ms.med_nombres, SUM(cantidad)::NUMERIC AS cantidad, SUM(total_detalle)::NUMERIC AS monto",FALSE);
		 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medico ms','fm.idmedicosolicitud = ms.idmedico');
		
		$this->db->where('fm.estado_movimiento <>', 0); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->group_by('ms.med_numero_documento, ms.med_apellido_paterno, ms.med_apellido_materno, ms.med_nombres');
		$this->db->order_by('ms.med_apellido_paterno', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicos_en_ventas_detalle_desde_hasta($paramDatos){ 
		$this->db->select("m.idmedicamento, m.denominacion AS medicamento, ms.idmedico, ms.med_numero_documento, ms.med_apellido_paterno, ms.med_apellido_materno, 
			ms.med_nombres, SUM(cantidad)::NUMERIC AS cantidad, SUM(total_detalle)::NUMERIC AS monto",FALSE); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medico ms','fm.idmedicosolicitud = ms.idmedico');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->where('fm.estado_movimiento <>', 0); // activos
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) 
			.' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		$this->db->group_by('m.idmedicamento, m.denominacion, ms.idmedico, ms.med_numero_documento, ms.med_apellido_paterno, ms.med_apellido_materno, ms.med_nombres');
		$this->db->order_by('ms.med_apellido_paterno', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_ventas_medicamentos_por_condicion_venta($datos){ 
		$this->db->select("m.idmedicamento, (m.denominacion) AS medicamento, fm.idmovimiento, fecha_movimiento, fdm.iddetallemovimiento, fdm.cantidad, fdm.precio_unitario, fma.stock_actual_malm,
			fdm.total_detalle, (fdm.total_detalle::NUMERIC) AS total_detalle_str, cv.idcondicionventa, cv.descripcion_cv",FALSE); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.estado_movimiento <>', 0); // activos 
		$this->db->where('fm.tipo_movimiento', 1); 
		$this->db->where_in('m.idcondicionventa', $datos['arrCondicionesVenta']); 
		$this->db->where('fm.idsedeempresaadmin', $datos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) 
			.' AND ' . $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		$this->db->order_by('cv.descripcion_cv, m.denominacion', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_laboratorios_vendidos_desde_hasta($paramDatos){
		$this->db->select("fl.idlaboratorio, fl.nombre_lab, SUM(fdm.total_detalle)::NUMERIC AS monto",FALSE);
		 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio');
		
		$this->db->where('fm.estado_movimiento <>', 0); // activos
		$this->db->where('fm.tipo_movimiento', 1); // venta
		$this->db->where('fm.dir_movimiento', 2); // salida de medicamentos (venta)
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->group_by('fl.idlaboratorio, fl.nombre_lab');
		if($paramDatos['ordenamiento']){
			$this->db->order_by('monto', $paramDatos['ordenamiento']);
			$this->db->order_by('fl.nombre_lab', 'ASC');
		}else{
			$this->db->order_by('fl.nombre_lab', 'ASC');
		}
		if($paramDatos['top']){
			$this->db->limit($paramDatos['top']['id']);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_compras_proveedor_desde_hasta($paramDatos){
		$this->db->select("fm.idproveedor, fp.razon_social, SUM(fm.total_a_pagar)::NUMERIC AS monto",FALSE);
		 
		$this->db->from('far_movimiento fm');
		//$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		//$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_proveedor fp','fm.idproveedor = fp.idproveedor');
		
		$this->db->where('fm.estado_movimiento', 1); // activos
		$this->db->where('fm.tipo_movimiento', 2); // compras
		$this->db->where('fm.dir_movimiento', 1); // entrada de medicamentos (compra)
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->group_by('fm.idproveedor, fp.razon_social');
		if($paramDatos['ordenamiento']){
			$this->db->order_by('monto', $paramDatos['ordenamiento']);
			$this->db->order_by('fp.razon_social', 'ASC');
		}else{
			$this->db->order_by('fp.razon_social', 'ASC');
		}
		if($paramDatos['top']){
			$this->db->limit($paramDatos['top']['id']);
		}
		return $this->db->get()->result_array();
	}
	  // *******************************************************************************
	 // MANTENIMIENTO medico 
	// *******************************************************************************
	public function m_registrar_venta($datos)
	{
		$data = array(
			'idcliente' => empty($datos['cliente']['id']) ? NULL : $datos['cliente']['id'],
			'idtipocliente' => empty($datos['cliente']['idtipocliente']) ? NULL : $datos['cliente']['idtipocliente'],
			'idproveedor' => NULL,
			'tipo_movimiento'=> 1,
			'dir_movimiento'=> 2,
			'idempresacliente'=> empty($datos['empresa']['id']) ? NULL : $datos['empresa']['id'],
			'iduser'=> $this->sessionHospital['idusers'],
			'idcaja'=> empty($datos['idcaja'])? NULL : $datos['idcaja'],
			'idtipodocumento'=> $datos['idtipodocumento'],
			'idmediopago'=> $datos['idmediopago'],
			'idsedeempresaadmin'=> $datos['idsedeempresaadmin'],
			'idalmacen'=> $datos['idalmacen'],
			'idsubalmacen'=> $datos['idsubalmacen'],
			'ticket_venta'=> empty($datos['ticket'])? NULL : $datos['ticket'],
			'fecha_movimiento'=> date('Y-m-d H:i:s'),
			'sub_total'=> $datos['subtotal'],
			'total_igv'=> $datos['igv'],
			'total_igv_exonerado'=> $datos['igv_exonerado'],
			'total_a_pagar'=> $datos['total'],
			'total_sin_redondeo' => $datos['total_sin_redondeo'],
			'redondeo' => $datos['redondeo'],
			'estado_movimiento'=> 1,
			'orden_venta'=> empty($datos['orden'])? NULL : $datos['orden'],
			'orden_pedido'=> empty($datos['orden_pedido'])? NULL : $datos['orden_pedido'], 
			'createdAt'=> date('Y-m-d H:i:s'), 
			'updatedAt'=> date('Y-m-d H:i:s'),
			'tiene_descuento'=> 2,
			'es_temporal' => (@$datos['estemporal']== true) ? 1 : 2,
			'iduserpedido' => empty($datos['iduserpedido'])? NULL : $datos['iduserpedido'],
			'puntos_ganados' => intval(@$datos['puntos_ganados']),
			'puntos_no_ganados' => intval(@$datos['puntos_no_ganados']),
			'idcliente_afiliado' => @$datos['cliente_afiliado']['idcliente'],
			'idmedicosolicitud' => empty($datos['medico']['id'])? NULL:$datos['medico']['id'],
			'vuelto'=> empty($datos['vuelto'])? NULL : $datos['vuelto'],
			'idsolicitudformula'=> empty($datos['idsolicitudformula'])? NULL : $datos['idsolicitudformula'],
			'saldo'=> empty($datos['saldo'])? NULL : $datos['saldo'],
			'estado_acuenta'=> empty($datos['saldo'])? 2 : 1, // 1: pago a cuenta
			'idventaorigen'=> empty($datos['idventaorigen'])? NULL : $datos['idventaorigen'], // 1: pago a cuenta
			'es_preparado' => $datos['esPreparado']? 1 : 2,
			'codigo_pedido' => empty($datos['codigo_pedido'])? NULL : $datos['codigo_pedido']
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_registrar_detalle($datos)
	{
		$data = array( 
			'idmovimiento'=> $datos['idmovimiento'],
			'idmedicamento'=> $datos['id'],
			'idmedicamentoalmacen'=> $datos['idmedicamentoalmacen'],
			'cantidad'=> $datos['cantidad'],
			'precio_unitario'=> $datos['precio'],
			'descuento_asignado'=> $datos['descuento'],
			'total_detalle'=> $datos['total'],
			'idtipoclientedescuento' =>$datos['idtipoclientedescuento'],
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s'),
			'idrecetamedicamento' => $datos['idrecetamedicamento'],
			'idreceta' => $datos['idreceta'],
			'estado_preparado' => empty($datos['estado_preparado'])? NULL : $datos['estado_preparado'],
			'es_convenio_detalle' => $datos['tiene_convenio_detalle'],
			'es_convenio_detalle_efectivo' => $datos['tiene_convenio_detalle_efectivo']

		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_registrar_pago_mixto($datos)
	{
		$data = array( 
			'idmovimiento'=> $datos['idmovimiento'],
			'idmediopago' => $datos['id'],
			'monto' => $datos['monto'],
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s'),
		);
		return $this->db->insert('far_pago_mixto', $data);
	}
	public function m_editar_venta_a_espera($id)
	{
		$data = array(
			'estado_movimiento' => 3,
			'tiene_descuento' => 1 // si tiene descuento 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_pedido_a_espera($id)
	{
		$data = array(
			'es_pedido' => 1, // es un pedido, a la espera que se apruebe en caja
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_a_aprobado($id)
	{
		$data = array(
			'estado_movimiento' => 1 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_estado_pedido($datos)
	{
		$data = array( 
			'es_pedido' => 2,
			'idcaja' => $datos['idcaja'],
			'ticket_venta' => $datos['ticket'],
			'orden_venta' => $datos['orden'],
			'sub_total' => $datos['subtotal'],
			'total_igv' => $datos['igv'],
			'total_a_pagar' => $datos['total'],
			'idmediopago' => $datos['idmediopago'],
			'idtipodocumento' => $datos['idtipodocumento'],
			'iduser' => $this->sessionHospital['idusers'],
			'iduserpedido' => $datos['idusuario'],
			'fecha_movimiento' => date('Y-m-d H:i:s')
		); 
		$this->db->where('idmovimiento',$datos['id']);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_anular_venta_caja_actual($id)
	{
		$data = array(
			'estado_movimiento' => 0,
			'fecha_anulacion'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_actualizar_detalle_venta_pedido($datos)
	{
		$data = array(
			'cantidad' => $datos['cantidad'],
			'descuento_asignado' => $datos['descuento'],
			'total_detalle' => $datos['total_detalle'],
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
	public function m_anular_detalle_venta_pedido($datos)
	{
		$data = array(
			'estado_detalle' => 0,
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
	public function m_actualizar_total_pedido($datos)
	{
		$data = array(
			'total_a_pagar' => $datos['total_a_pagar'],
			'total_igv' => $datos['total_igv'],
			'sub_total' => $datos['sub_total'],
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		return $this->db->update('far_movimiento', $data);
	}

	public function m_cerrar_caja_farmacia($datos)
	{
		$data = array( 
			'caja_en_sistema' => @$datos['totalCaja'],
			'caja_en_fisico' => @$datos['totalFisico'],
			'diferencia_caja' => @$datos['diferencia'],
			'observaciones' => @$datos['observacion'],
			'estado' => 2, // cerrada 
			'fecha_cierre' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcaja',$datos['idcaja']);
		return $this->db->update('caja', $data);
	}
	/* REIMPRESIONES */ 
	public function m_editar_venta_a_impreso($id)
	{
		$data = array( 
			'tiene_impresion' => 1 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_a_reimpreso($id)
	{
		$data = array( 
			'tiene_reimpresion' => 1 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_a_sin_solicitud_impresion($id)
	{
		$data = array(
			'solicita_impresion' => 2 // NO  
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_a_solicitud_impresion($id)
	{
		$data = array(
			'solicita_impresion' => 1 // MANDO SOLICITUD  
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_editar_venta_a_solicitud_impresion_aprobada($id)
	{
		$data = array(
			'solicita_impresion' => 3 // ACEPTA SOLICITUD   
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
	  /* ************* */
	 /*  PAGOS MIXTOS */
	/* ************* */
	public function m_cargar_pago_mixto($datos)
	{
		$this->db->select('fpm.idpagomixto, fpm.idmovimiento, fpm.idmediopago, fpm.monto, (fpm.monto::numeric) AS monto_sf');
		$this->db->select('mp.descripcion_med');
		$this->db->from('far_pago_mixto fpm');
		$this->db->join('medio_pago mp','fpm.idmediopago = mp.idmediopago');
		$this->db->where('fpm.estado_pago', 1);
		$this->db->where('mp.estado_med', 1);
		$this->db->where('fpm.idmovimiento', $datos['id']);
		return $this->db->get()->result_array();
	}
	public function m_editar_pago_mixto($datos)
	{
		$data = array(
			'monto' => $datos['monto'],
		);
		$this->db->where('idpagomixto',$datos['idpagomixto']);
		return $this->db->update('far_pago_mixto', $data);
	}
	public function m_verificar_caja_por_idmovimiento($id){
		$this->db->from('far_movimiento fm');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja');
		$this->db->where('fm.idmovimiento', $id);
		$this->db->where('cj.estado', 1); // caja abierta
		$this->db->limit(1);
		$totalRows = $this->db->get()->num_rows();
		if( $totalRows > 0 )
			return true; // caja abierta
		else
			return false; // caja cerrada
	}
	  /* ************* */
	 /*  PREPARADOS   */
	/* ************* */
	public function m_cargar_detalle_venta_formula_a_cuenta($paramPaginate,$paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, ticket_venta,
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, 
			m.idmedicamento, m.denominacion, fdm.estado_preparado, fm.idcliente, fdm.idmedicamentoalmacen,
			(fdm.precio_unitario)::NUMERIC AS precio_unitario_sf, (fm.saldo)::NUMERIC AS saldo
		');
		$this->db->select("concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente, c.num_documento");
		// $this->db->select("concat_ws(' ', med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno) AS medico");
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente');
		// $this->db->join('medico med','fm.idmedicosolicitud = med.idmedico');
		// $this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idsolicitudformula', $paramDatos['idsolicitudformula']);
		$this->db->where('fm.es_preparado', 1);
		$this->db->where('fm.saldo IS NOT NULL');
		$this->db->where('fm.estado_acuenta', 1);
		$this->db->where('fdm.estado_detalle', 1);
		$this->db->where('fm.estado_movimiento', 1);
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_sum_detalle_venta_formula_a_cuenta($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle::NUMERIC) AS sumatotal'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente');
		// $this->db->join('medico med','fm.idmedicosolicitud = med.idmedico');
		// $this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idsolicitudformula', $paramDatos['idsolicitudformula']);
		$this->db->where('fm.es_preparado', 1);
		$this->db->where('fm.saldo IS NOT NULL');
		$this->db->where('fdm.estado_detalle', 1);
		$this->db->where('fm.estado_movimiento', 1);
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	public function m_actualizar_estado_movimiento_origen($datos)
	{
		$data = array(
			'estado_acuenta' => 2,
		);
		$this->db->where('idmovimiento',$datos['idventaorigen']);
		return $this->db->update('far_movimiento', $data);
	}
	
	// FORMULAS Y PREPARADOS
	public function m_cargar_formulas_pagadas($paramDatos){
		//$this->db->distinct();
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula, me.idmedicamento, me.denominacion, me.idformula_jj, fm.codigo_pedido');
		$this->db->select('fdm.cantidad, fds.precio_costo::NUMERIC, fdm.precio_unitario::NUMERIC, fdm.total_detalle::NUMERIC',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico",FALSE);
		$this->db->select("fm.idtipodocumento, td.descripcion_td, fm.ticket_venta, fm.orden_venta, fm.estado_acuenta",FALSE);
		$this->db->select("fm.fecha_movimiento, CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		$this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);
		$this->db->select('fm.fecha_movimiento::DATE AS fecha, fm.fecha_movimiento::TIME AS hora', FALSE);
		$this->db->select("COALESCE(cli.telefono,'') || ' ' || COALESCE(cli.celular,'') AS telefono", FALSE);
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		$this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1);
		$this->db->where('fm.idventaorigen IS NULL');
		$this->db->where('fm.estado_movimiento',1);

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		$this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->order_by('fm.codigo_pedido', 'ASC');
		$this->db->order_by('fm.idsolicitudformula', 'ASC');
		$this->db->order_by('fm.idmovimiento', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_formulas_pagadas_para_recepcion($paramPaginate,$paramDatos){
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula,fm.fecha_movimiento, me.idmedicamento, me.denominacion AS medicamento, fm.codigo_pedido');
		$this->db->select('fdm.iddetallemovimiento,fdm.cantidad, fdm.idmedicamentoalmacen');
		$this->db->select('fdm.precio_unitario::NUMERIC, fdm.total_detalle::NUMERIC',FALSE);
		// $this->db->select('fds.precio_costo::NUMERIC',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico",FALSE);
		$this->db->select("fm.idtipodocumento, td.descripcion_td, fm.ticket_venta, fm.orden_venta, fm.estado_acuenta",FALSE);
		$this->db->select("COALESCE(cli.telefono,'') || ' ' || COALESCE(cli.celular,'') AS telefono", FALSE);
		// $this->db->select(" CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		// $this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);
		$this->db->select('fm.fecha_movimiento::DATE AS fecha, fm.fecha_movimiento::TIME AS hora', FALSE);
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		// $this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1); // formulas
		$this->db->where('fdm.estado_preparado', 1); // pedido
		$this->db->where('fm.idventaorigen IS NULL'); // solo primeros pagos, movimiento q corresponde con los pedidos a JJ
		$this->db->where('fm.estado_movimiento',1);

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		// $this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
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
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_formulas_pagadas_para_recepcion($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador');
		// $this->db->select('SUM( (fds.precio_costo::NUMERIC) * (fdm.cantidad::NUMERIC) ) AS sumatotal');
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		// $this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1); // formulas
		$this->db->where('fdm.estado_preparado', 1); // pedido
		$this->db->where('fm.idventaorigen IS NULL'); // solo primeros pagos, movimiento q corresponde con los pedidos a JJ
		$this->db->where('fm.estado_movimiento',1);

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		// $this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
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
	public function m_cargar_formulas_recibidas($paramPaginate,$paramDatos){
		//$this->db->distinct();
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula,fm.fecha_movimiento, me.idmedicamento, me.denominacion AS medicamento, fm.codigo_pedido');
		$this->db->select('fdm.iddetallemovimiento,fdm.cantidad, fdm.idmedicamentoalmacen, fdm.guia_remision');
		$this->db->select('fdm.precio_unitario::NUMERIC, fdm.total_detalle::NUMERIC, fds.precio_costo::NUMERIC,',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("COALESCE(cli.telefono,'') || ' ' || COALESCE(cli.celular,'') AS telefono", FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico",FALSE);
		$this->db->select("fm.idtipodocumento, td.descripcion_td, fm.ticket_venta, fm.orden_venta, fm.estado_acuenta,fdm.estado_preparado",FALSE);
		// $this->db->select(" CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		// $this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);
		$this->db->select('fdm.fecha_recepcion, fdm.fecha_recepcion::DATE AS fecha, fdm.fecha_recepcion::TIME AS hora', FALSE);
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		$this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1); // formulas
		$this->db->where('fdm.estado_preparado <>', 1); // recibidos y entregados
		$this->db->where('fm.idventaorigen IS NULL'); // solo primeros pagos, movimiento q corresponde con los pedidos a JJ
		$this->db->where('fm.estado_movimiento',1);

		if(!empty($paramDatos['estadoRecibido']['id'])){
			$this->db->where('fdm.estado_preparado', $paramDatos['estadoRecibido']['id']);
		}

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		// $this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		// $this->db->where('fdm.fecha_recepcion BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
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
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_formulas_recibidas($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador, SUM( (fds.precio_costo::NUMERIC) * (fdm.cantidad::NUMERIC) ) AS sumatotal'); 
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		$this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1); // formulas
		$this->db->where('fdm.estado_preparado <>', 1); // recibidos y entregados
		$this->db->where('fm.idventaorigen IS NULL'); // solo primeros pagos, movimiento q corresponde con los pedidos a JJ
		$this->db->where('fm.estado_movimiento',1);
		if(!empty($paramDatos['estadoRecibido']['id'])){
			$this->db->where('fdm.estado_preparado', $paramDatos['estadoRecibido']['id']);
		}
		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		// $this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		// $this->db->where('fdm.fecha_recepcion BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
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
	public function m_cargar_venta_formulas_desde_hasta($paramDatos=FALSE)
	{
		/* VENTAS */
		// $this->db->select("(CASE WHEN fm.estado_movimiento = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("total_a_pagar::NUMERIC, sub_total::NUMERIC, total_igv::NUMERIC, redondeo::NUMERIC, total_sin_redondeo::NUMERIC",FALSE);
		$this->db->select("fm.idmovimiento, fm.idsolicitudformula,
			DATE(fm.fecha_movimiento) AS fecha, to_char(fm.fecha_movimiento, 'HH24:MI:SS') AS hora, fm.fecha_movimiento, fm.codigo_pedido,
			orden_venta, ticket_venta, td.idtipodocumento, descripcion_td, CASE WHEN fm.idtipodocumento = 12 THEN 'PAGO A CUENTA' ELSE 'CANCELADO' END AS estado,
			mp.idmediopago, descripcion_med, 
			c.idcliente, concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, u.username",FALSE);

		$this->db->from('far_movimiento fm');
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago','left');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND estado_cli = 1','left');

		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(1)); // activos y anulados
		$this->db->where('tipo_movimiento', 1);
		$this->db->where('es_preparado', 1); // solo formulas
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master

		$this->db->where('fm.idsedeempresaadmin',$paramDatos['sedeempresa'] );
		$this->db->where('fm.idtipodocumento <>', 7); // SOLO VENTAS
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->order_by('fm.idsolicitudformula');
		$this->db->order_by('fm.idmovimiento');
		return $this->db->get()->result_array();
	}
	public function m_cargar_nc_formulas($paramDatos=FALSE) 
	{
		/* NOTAS DE CREDITO */
		$this->db->select("fm.total_a_pagar::numeric",FALSE);
		$this->db->select("fm.fecha_movimiento::DATE AS fecha, fm.fecha_movimiento::TIME AS hora",FALSE);
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, fm.orden_venta, fm.idtipocliente, fm.fecha_movimiento, 
			fm.ticket_venta, td.idtipodocumento, descripcion_td, fm.idventaorigen,
			fm_or.orden_venta AS orden_venta_origen, fm_or.ticket_venta AS ticket_venta_origen, fm_or.idsolicitudformula, fm_or.codigo_pedido,
			c.idcliente, concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS cliente, c.num_documento, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email");  
		$this->db->from('far_movimiento fm'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('far_movimiento fm_or', 'fm.idventaorigen = fm_or.idmovimiento AND fm_or.estado_movimiento = 1 AND fm_or.es_preparado = 1');
		$this->db->join('cliente c','fm_or.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // activos y anulados
		$this->db->where('fm.tipo_movimiento', 1);
		$this->db->where('cj.estado <>', 0); // caja abierta y cerrada
		$this->db->where('cm.estado_caja', 1); // caja master
		//$this->db->where('fm.idcaja', $paramDatos['idcaja']);
		$this->db->where('fm.idtipodocumento', 7); // SOLO NOTAS DE CREDITO
		//$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		return $this->db->get()->result_array();
	}
	public function m_cargar_formulas_vendidas_costo($paramDatos){
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula, me.idmedicamento, me.denominacion, fdm.cantidad, fma.precio_compra::NUMERIC AS precio_compra',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico",FALSE);
		$this->db->select("fm.idtipodocumento, td.descripcion_td, fm.ticket_venta, fm.orden_venta, fm.estado_acuenta, fm.total_a_pagar::NUMERIC as monto",FALSE);
		$this->db->select("fm.fecha_movimiento, CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		$this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);

		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		$this->db->join('far_medicamento_almacen fma', 'fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1);
		$this->db->where('fm.idventaorigen IS NULL');
		$this->db->where('fm.estado_movimiento',1);

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		$this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->order_by('fm.idsolicitudformula', 'ASC');
		$this->db->order_by('fm.idmovimiento', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_formulas_recibidas_desde_hasta($paramDatos,$paramPaginate=FALSE){
		//$this->db->distinct();
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula,fm.fecha_movimiento::DATE AS fecha_venta, fm.codigo_pedido', FALSE);
		$this->db->select('fdm.iddetallemovimiento,fdm.cantidad, fdm.idmedicamentoalmacen, fdm.guia_remision');
		$this->db->select('me.idmedicamento, me.denominacion AS medicamento, me.idformula_jj');
		$this->db->select('fdm.precio_unitario::NUMERIC, fdm.total_detalle::NUMERIC, fds.precio_costo::NUMERIC,',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("COALESCE(cli.telefono,'') || ' ' || COALESCE(cli.celular,'') AS telefono", FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico",FALSE);
		$this->db->select("fm.idtipodocumento, td.descripcion_td, fm.ticket_venta, fm.orden_venta, fm.estado_acuenta,fdm.estado_preparado",FALSE);
		// $this->db->select(" CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		// $this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);
		$this->db->select('fdm.fecha_recepcion::DATE AS fecha_recepcion, fdm.fecha_recepcion::TIME AS hora', FALSE);
		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('tipo_documento td', 'fm.idtipodocumento = td.idtipodocumento');
		$this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1); // formulas
		$this->db->where_in('fdm.estado_preparado', array(3,2)); // recibidos y entregados
		$this->db->where('fm.idventaorigen IS NULL'); // solo primeros pagos, movimiento q corresponde con los pedidos a JJ
		$this->db->where('fm.estado_movimiento',1);

		// if(!empty($paramDatos['estadoRecibido']['id'])){
		// 	$this->db->where('fdm.estado_preparado', $paramDatos['estadoRecibido']['id']);
		// }

		// $this->db->where_in('fm.estado_acuenta', array(1,2)); //2 = CANCELADO
		// $this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
		// $this->db->where('fdm.fecha_recepcion BETWEEN '. $desde . ' AND ' . $hasta);
		// if( $paramPaginate['search'] ){ 
		// 	foreach ($paramPaginate['searchColumn'] as $key => $value) { 
		// 		if( !empty($value) ){ 
		// 			$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
		// 		} 
		// 	} 
		// }
		$this->db->order_by('fm.fecha_movimiento', 'ASC');
		return $this->db->get()->result_array(); 
	}
	public function m_verificar_movimiento_pendiente_pago($arrMovimientos)
	{
		$this->db->select('*');
		$this->db->from('far_movimiento');
		$this->db->where_in('idmovimiento', $arrMovimientos);
		$this->db->where('estado_acuenta', 1);
		// $count = $this->db->get()->num_rows();
		if( $this->db->get()->num_rows() > 0 ){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	public function m_entregar_formula($datos)
	{
		$data = array(
			'estado_preparado' => 2, // recibido
			'fecha_entrega' => date('Y-m-d H:i:s'),
		);
		$this->db->where('iddetallemovimiento', $datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
	public function m_cargar_detalle_por_id($id)
	{
		$this->db->select('*');
		$this->db->from('far_detalle_movimiento');
		$this->db->where('iddetallemovimiento', $id);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_pedidos_cabecera($paramDatos){ // para reporte excel que interconceta con sistema JJ
		//$this->db->distinct();
		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		
		$this->db->select('fm.idmovimiento, fm.idsolicitudformula, fm.codigo_pedido,fdm.iddetallemovimiento');
		$this->db->select('me.idmedicamento, me.denominacion, me.idformula_jj, me.fecha_asigna_idformula_jj, me.uso_jj');
		$this->db->select('fdm.cantidad, fds.precio_costo::NUMERIC, fdm.precio_unitario::NUMERIC, fdm.total_detalle::NUMERIC',FALSE);
		$this->db->select("fm.idcliente, concat_ws(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno) AS paciente,cli.num_documento",FALSE);
		$this->db->select("m.idmedico, concat_ws(' ', m.med_nombres, m.med_apellido_paterno, m.med_apellido_materno) AS medico,",FALSE);
		$this->db->select('m.colegiatura_profesional, m.codigo_jj, m.fecha_asigna_codigo_jj');
		$this->db->select("fm.fecha_movimiento, CASE WHEN estado_acuenta = 1 THEN 'A CUENTA' ELSE 'CANCELADO' END AS estado",FALSE);
		$this->db->select("CASE WHEN fdm.estado_preparado = 2 THEN 'ENTREGADO' ELSE '' END AS estado_formula",FALSE);
		$this->db->select('fm.fecha_movimiento::DATE AS fecha, fm.fecha_movimiento::TIME AS hora', FALSE);
		$this->db->select("COALESCE(cli.telefono,'') || ' ' || COALESCE(cli.celular,'') AS telefono", FALSE);
		$this->db->select("CASE WHEN m.fecha_asigna_codigo_jj BETWEEN ". $desde . " AND " . $hasta . " THEN 'SI' ELSE 'NO' END AS medico_nuevo", FALSE);
		$this->db->select("CASE WHEN me.fecha_asigna_idformula_jj BETWEEN ". $desde . " AND " . $hasta . " THEN 'SI' ELSE 'NO' END AS formula_nueva", FALSE);

		$this->db->from('far_movimiento fm');		
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento AND fdm.estado_detalle = 1');
		$this->db->join('medicamento me', 'fdm.idmedicamento = me.idmedicamento');		
		$this->db->join('cliente cli', 'fm.idcliente = cli.idcliente');
		$this->db->join('far_detalle_solicitud fds', 'fm.idsolicitudformula = fds.idsolicitudformula AND fdm.idmedicamentoalmacen = fds.idmedicamentoalmacen');
		$this->db->join('far_solicitud_formula fsf', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fsf.estado_sol = 1');
		$this->db->join('medico m','fm.idmedicosolicitud = m.idmedico','left');
		$this->db->where('fm.es_preparado', 1);
		$this->db->where('fm.idventaorigen IS NULL');
		$this->db->where('fm.estado_movimiento',1);
		if( $this->sessionHospital['key_group'] == 'key_derma' ){
			$this->db->where('fdm.estado_preparado',1);
			$this->db->where('fdm.pedido_descargado',2);
		}
		$this->db->where('fm.codigo_pedido IS NOT NULL'); // para generar excel importa solo las que tienen codigo de pedido jj.
		$this->db->where('idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->order_by('fm.codigo_pedido', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_marcar_pedido_formula_descargado($arrId)
	{
		$data = array(
			'pedido_descargado' => 1,
			'updatedAt' =>  date('Y-m-d H:i:s'),
		);
		$this->db->where_in('iddetallemovimiento',$arrId);
		return $this->db->update('far_detalle_movimiento', $data);
	}

}