<?php
class Model_historial_venta_farm extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ventas_historial($paramPaginate,$paramDatos=FALSE) 
	{
		/* VENTAS */
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, es_preparado
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // anulado y vendido
		$this->db->where('tipo_movimiento', 1); // venta

		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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
	public function m_count_sum_ventas_historial($paramPaginate,$paramDatos=FALSE)
	{
		/* VENTAS */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','fm.idcliente = c.idcliente', 'left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(0, 1)); // vendido
		$this->db->where('tipo_movimiento', 1); // venta

		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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

	public function m_cargar_ventas_historial_medicamento($paramPaginate,$paramDatos=FALSE){
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_movimiento, ticket_venta, td.idtipodocumento, descripcion_td,
			fdm.cantidad, fdm.precio_unitario, fdm.total_detalle,
			(fdm.precio_unitario)::NUMERIC AS precio_unitario_sf, (fdm.total_detalle)::NUMERIC AS total_detalle_sf,
			mp.idmediopago, descripcion_med, m.idmedicamento, m.denominacion,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email
		');
		$this->db->select('fl.idlaboratorio, fl.nombre_lab AS laboratorio');
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento AND m.idtipoproducto <> 22');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // anulado y vendido
		$this->db->where('tipo_movimiento', 1); // venta

		//$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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
	public function m_count_sum_ventas_historial_medicamento($paramPaginate,$paramDatos=FALSE){
		/* VENTAS */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_detalle::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->select('SUM(CASE WHEN fm.estado_movimiento = 1 THEN (cantidad::numeric) ELSE 0 END) AS sumacantidad',FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento  AND m.idtipoproducto <> 22');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where_in('fm.estado_movimiento', array(0,1)); // anulado y vendido
		$this->db->where('tipo_movimiento', 1); // venta
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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
	public function m_cargar_ventas_historial_preparado($paramPaginate,$paramDatos=FALSE){
		$this->db->select("fm.idmovimiento, fm.estado_movimiento, orden_venta, CONCAT_WS(' ',c.nombres, c.apellido_paterno,c.apellido_materno) AS cliente,
			sub_total, total_igv, total_a_pagar, fecha_movimiento, fm.idsolicitudformula, fm.saldo, td.idtipodocumento, descripcion_td, fm.ticket_venta,
			fdm.cantidad, fdm.precio_unitario, fdm.total_detalle,
			(fdm.precio_unitario)::NUMERIC AS precio_unitario_sf, (fdm.total_detalle)::NUMERIC AS total_detalle_sf,
			mp.idmediopago, descripcion_med, m.idmedicamento, m.denominacion,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email
		");
		$this->db->select('fl.idlaboratorio, fl.nombre_lab AS laboratorio');
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento AND m.idtipoproducto = 22');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('cliente c', 'c.idcliente = fm.idcliente', 'left');
		$this->db->where_in('fm.estado_movimiento', array(1)); // anulado y vendido
		$this->db->where('tipo_movimiento', 1); // venta
		
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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
	public function m_count_sum_ventas_historial_preparado($paramPaginate,$paramDatos=FALSE){
		/* VENTAS */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_detalle::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->select('SUM(CASE WHEN fm.estado_movimiento = 1 THEN (cantidad::numeric) ELSE 0 END) AS sumacantidad',FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento AND m.idtipoproducto = 22');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('cliente c', 'c.idcliente = fm.idcliente', 'left');
		$this->db->where_in('fm.estado_movimiento', array(1)); // vendido
		$this->db->where('tipo_movimiento', 1); // venta
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta y caja cerrada
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
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
}
?>