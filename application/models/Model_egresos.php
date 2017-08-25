<?php
class Model_egresos extends CI_Model {
	public function __construct()
	{
		parent::__construct(); // AS
	}
	public function m_cargar_egresos($paramDatos, $paramPaginate){ // 
		$this->db->select("UPPER(CONCAT_WS(' ', empl.nombres, empl.apellido_paterno, empl.apellido_materno)) AS empleado, (emp.descripcion) AS empresa, 
			(total_a_pagar::NUMERIC) AS total_a_pagar_str, (sub_total::NUMERIC) AS sub_total_str",FALSE);
		$this->db->select('mo.idmovimiento, mo.dir_movimiento, mo.serie_documento, mo.numero_documento, mo.fecha_registro, mo.fecha_emision, mo.fecha_credito, mo.forma_pago, mo.orden_compra, mo.codigo_plan, 
			mo.servicio_asignado, mo.periodo_asignado, mo.modo_igv, mo.total_impuesto_inafecto, mo.sub_total, mo.total_impuesto, mo.total_a_pagar, mo.fecha_pago, mo.fecha_aprobacion, mo.detraccion, mo.deposito, 
			mo.estado_movimiento, emp.idempresa, emp.ruc_empresa, op.idoperacion, op.idoperacion, op.descripcion_op, 
			td.idtipodocumento, td.descripcion_td, td.abreviatura, td.porcentaje_imp, 
			sop.idsuboperacion, sop.descripcion_sop, mon.idmoneda, mon.moneda, mon.simbolo, 
			tc.idtipocambio'); 
		$this->db->from('ct_movimiento mo');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('users us','mo.idusuario = us.idusers');
		$this->db->join('rh_empleado empl','us.idusers = empl.iduser AND empl.estado_empl = 1');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('mo.fecha_emision BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		//$this->db->where_in('mo.estado_movimiento', array(1,0) ); // activo y anulado estado_egreso
		//$this->db->where('mo.tipo_movimiento', 8); // egreso por servicio  idtipodocumento
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('sea.idempresaadmin', $this->sessionHospital['idempresaadmin']);
		$this->db->where('op.tipo_operacion', 1); // egreso
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
		if( $paramDatos['operacion']['id'] != 'all' && !empty($paramDatos['operacion']['id']) ){ 
			$this->db->where('op.idoperacion', $paramDatos['operacion']['id']);
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
	public function m_count_egresos($paramDatos, $paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN mo.estado_movimiento IN (1) THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('ct_movimiento mo');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('users us','mo.idusuario = us.idusers');
		$this->db->join('rh_empleado empl','us.idusers = empl.iduser');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('mo.fecha_emision BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		//$this->db->where_in('mo.estado_movimiento', array(1,0) ); // activo y anulado estado_egreso
		//$this->db->where('mo.tipo_movimiento', 8); // egreso por servicio  idtipodocumento
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('sea.idempresaadmin', $this->sessionHospital['idempresaadmin']);
		$this->db->where('op.tipo_operacion', 1); // egreso
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
		if( $paramDatos['operacion']['id'] != 'all' && !empty($paramDatos['operacion']['id']) ){ 
			$this->db->where('op.idoperacion', $paramDatos['operacion']['id']);
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		return $this->db->get()->row_array();
	}
	public function m_cargar_egreso_autocomplete($datos) // sub_total
	{ 
		$this->db->select('mo.idmovimiento, mo.numero_documento, mo.idoperacion, mo.idsuboperacion, (mo.total_a_pagar::numeric) as total_a_pagar'); 
		$this->db->select(' (SELECT glosa FROM ct_detalle_movimiento dm WHERE dm.idmovimiento = mo.idmovimiento LIMIT 1) AS glosa',FALSE);
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_operacion op', 'mo.idoperacion = op.idoperacion');
		$this->db->where('mo.estado_movimiento <> ', 0);
		$this->db->where('mo.estado_movimiento', 1);
		$this->db->where('op.tipo_operacion', 1); // egreso
		$this->db->where_not_in('mo.idtipodocumento', array(7,14));
		$this->db->where('mo.idempresa', $datos['empresa']['idempresa']);
		$this->db->where('mo.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin'] );
		$this->db->ilike($datos['searchColumn'], $datos['searchText']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_de_un_egreso($paramDatos)
	{
		$this->db->select('dmo.iddetallemovimiento,dmo.idmovimiento,dmo.idcentrocosto,dmo.codigo_plan,dmo.importe_local, 
			(dmo.importe_local::numeric) AS num_importe_local,dmo.monto_inafecto,dmo.tipo_cambio_venta, 
			dmo.tipo_cambio_compra,dmo.glosa, (dmo.importe_local_con_igv)::numeric AS importe_local_con_igv, mo.total_a_pagar, mo.inafecto, 
			td.idtipodocumento, td.descripcion_td, td.abreviatura, td.porcentaje_imp, td.nombre_impuesto, (td.codigo_plan) AS codigo_plan_referencia'); 
		$this->db->select('cc.nombre_cc, cc.codigo_cc, (mo.sub_total::numeric) AS sub_total'); 
		$this->db->from('ct_detalle_movimiento dmo');
		$this->db->join('ct_movimiento mo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('ct_centro_costo cc','cc.idcentrocosto = dmo.idcentrocosto');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->where('mo.idmovimiento', $paramDatos['idmovimiento']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_egresos($paramDatos, $paramPaginate)  
	{
		$this->db->select('mo.idmovimiento, fecha_pago, fecha_aprobacion, fecha_registro, fecha_emision,total_a_pagar, detraccion,
			deposito, estado_movimiento, dir_movimiento, mo.numero_documento, dmo.glosa, cc.idcentrocosto, cc.nombre_cc,
			scc.descripcion_scc, ccc.descripcion_ccc, mo.serie_documento, dmo.importe_local_con_igv,
			emp.idempresa, emp.ruc_empresa, emp.descripcion, emp.domicilio_fiscal, emp.telefono, dmo.importe_local');
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_centro_costo cc', 'dmo.idcentrocosto = cc.idcentrocosto');
		$this->db->join('ct_subcat_centro_costo scc', 'cc.idsubcatcentrocosto = scc.idsubcatcentrocosto');
		$this->db->join('ct_cat_centro_costo ccc', 'scc.idcatcentrocosto = ccc.idcatcentrocosto');
		$this->db->where('mo.fecha_emision BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('mo.estado_movimiento', 1); // activo 
		$this->db->where('op.tipo_operacion', 1); // egreso
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! ¿entrada de stock? 
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
	public function m_count_detalle_egresos($paramDatos, $paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN mo.estado_movimiento IN (1) THEN (importe_local::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');

		$this->db->where('mo.fecha_emision BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('mo.estado_movimiento', 1); // activo 
		$this->db->where('op.tipo_operacion', 1); // egreso
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! ¿entrada de stock? 
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultimo_egreso($datos)
	{ 
		$this->db->select('fm.idmovimiento, orden_compra'); 
		$this->db->from('far_movimiento fm');
		$this->db->where('categoria_concepto_abv',$datos['categoria']); 
		$this->db->where('fm.tipo_movimiento', 8);  // egreso por servicio 
		$this->db->where('fm.dir_movimiento', 1); // ¡salida de dinero! ¿entrada de stock? 
		$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_egreso($datos)
	{
		$this->db->select('emp_reg.nombres AS reg_nombres, emp_reg.apellido_paterno AS reg_apellido_paterno, emp_reg.apellido_materno AS reg_apellido_materno'); 
		$this->db->select('emp_apro.nombres AS apro_nombres, emp_apro.apellido_paterno AS apro_apellido_paterno, emp_apro.apellido_materno AS apro_apellido_materno'); 
		$this->db->select('emp_pag.nombres AS pag_nombres, emp_pag.apellido_paterno AS pag_apellido_paterno, emp_pag.apellido_materno AS pag_apellido_materno'); 
		$this->db->select('emp_obs.nombres AS obs_nombres, emp_obs.apellido_paterno AS obs_apellido_paterno, emp_obs.apellido_materno AS obs_apellido_materno'); 
		$this->db->select('emp_anu.nombres AS anu_nombres, emp_anu.apellido_paterno AS anu_apellido_paterno, emp_anu.apellido_materno AS anu_apellido_materno'); 
		$this->db->select('fm.idmovimiento, estado_egreso, fecha_movimiento, fecha_aprobacion, fecha_observacion, fecha_pago, fecha_anulacion'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('users u_reg','fm.iduser = u_reg.idusers');
		$this->db->join('rh_empleado emp_reg','u_reg.idusers = emp_reg.iduser');
		$this->db->join('users u_apro','fm.iduseraprobacion = u_apro.idusers','left');
		$this->db->join('rh_empleado emp_apro','u_apro.idusers = emp_apro.iduser','left');
		$this->db->join('users u_pag','fm.iduserpago = u_pag.idusers','left');
		$this->db->join('rh_empleado emp_pag','u_pag.idusers = emp_pag.iduser','left');
		$this->db->join('users u_obs','fm.iduserobservacion = u_obs.idusers','left');
		$this->db->join('rh_empleado emp_obs','u_obs.idusers = emp_obs.iduser','left');
		$this->db->join('users u_anu','fm.iduserobservacion = u_anu.idusers','left');
		$this->db->join('rh_empleado emp_anu','u_anu.idusers = emp_anu.iduser','left');
		// $this->db->where('fm.dir_movimiento', 1); // ¡salida de dinero! ¿entrada de stock? 
		$this->db->where('fm.idmovimiento', $datos['idmovimiento']); 
		//$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_verificar_existe_orden_egreso($datos)
	{
		$this->db->select('idmovimiento, ticket_venta'); 
		$this->db->from('far_movimiento');
		$this->db->where('ticket_venta',$datos['orden_egreso']); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_obtener_este_egreso($datos)
	{
		$this->db->select('fm.idmovimiento, ticket_venta, total_a_pagar, detraccion, deposito'); 
		$this->db->from('far_movimiento fm');
		//$this->db->where('categoria_concepto_abv',$datos['categoria']); 
		$this->db->where('fm.idempresatercero', $datos['idempresatercero']); 
		$this->db->where('fm.periodo_asignado', $datos['periodo']);
		$this->db->where('fm.tipo_movimiento', 8);  // egreso por servicio 
		$this->db->where('fm.dir_movimiento', 1); // ¡salida de dinero! ¿entrada de stock?  guia_remision 
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	} 
	// public function m_cargar_notas_credito_existentes_egreso($datos)
	// {
	// 	$this->db->select('SUM(CASE WHEN mo.estado_movimiento IN (1) THEN (importe_local::numeric) ELSE 0 END) AS suma'); 
	// 	$this->db->from('ct_detalle_movimiento dmo');
	// 	$this->db->join('ct_movimiento mo','mo.idmovimiento = dmo.idmovimiento');
	// 	$this->db->where('mo.estado_movimiento', 1); // activo 
	// 	$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! ¿entrada de stock? 
	// 	$this->db->where('mo.idmovimiento_ref', $datos['idmovimiento']);
	// 	$this->db->where('dmo.iddetallemovimiento_ref', $datos['item']);
	// 	$fData = $this->db->get()->row_array();
	// 	return $fData['suma'];
	// }
	public function m_registrar_asiento($datos)
	{
		$data = array( 
			'idmovimiento'=> $datos['idmovimiento'],
			'codigo_plan'=> $datos['codigo_plan'],
			'glosa'=> $datos['glosa'], 
			'monto'=> $datos['monto'],
			'fecha_emision'=> $datos['fecha_emision'],
			'debe_haber'=> $datos['debe_haber']
		);
		return $this->db->insert('ct_asiento_contable', $data);
	}
	public function m_registrar_egreso($datos) // 
	{
		$data = array( 
			'idoperacion'=> $datos['operacion']['id'],
			'idsuboperacion'=> $datos['suboperacion']['id'],
			'idmoneda'=> $datos['idmoneda'], 
			'idtipodocumento'=> $datos['tipodocumento']['id'],
			'idtipocambio'=> $datos['idtipocambio'],
			'idusuario'=> $this->sessionHospital['idusers'],
			'dir_movimiento'=> 2, // salida de dinero 
			'idorigen'=> NULL,
			'idempresa'=> $datos['proveedor']['idempresa'], 
			'numero_documento'=> $datos['numero_documento'],
			'fecha_registro'=> date('Y-m-d H:i:s'),
			'fecha_emision'=> $datos['fecha_emision'],
			'fecha_credito'=> empty($datos['fecha_venc_credito']) ? NULL : $datos['fecha_venc_credito'], 
			'forma_pago' => $datos['forma_pago'],
			'modo_igv'=> 1, // SIN IGV 
			'sub_total'=> $datos['subtotal'],
			'total_impuesto'=> $datos['impuesto'],
			'total_a_pagar'=> $datos['total'],
			'tipo_cambio_compra'=> $datos['compra'],
			'tipo_cambio_venta'=> $datos['venta'],
			'guia_remision'=> empty($datos['guia_remision']) ? NULL:$datos['guia_remision'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'], 
			'createdat' => date('Y-m-d H:i:s'), 
			'updatedat' => date('Y-m-d H:i:s'), 
			'detraccion'=> $datos['detraccion'], 
			'deposito'=> $datos['deposito'],
			'codigo_plan'=> $datos['codigo_plan'], 
			// 'orden_egreso'=> $datos['orden_egreso']
			'serie_documento'=>$datos['serie_documento'],
			'idmovimiento_ref'=> empty($datos['numero_egreso']) ? NULL : $datos['numero_egreso']['idmovimiento'],
		);
		return $this->db->insert('ct_movimiento', $data);
	}
	public function m_registrar_detalle_egreso($datos)
	{
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'], 
			'idcentrocosto' => $datos['idcentrocosto'], 
			'codigo_plan' => $datos['codigo'], 
			'importe_local' => $datos['importe'], 
			'tipo_cambio_compra'=> $datos['compra'], 
			'tipo_cambio_venta'=> $datos['venta'], 
			'glosa'=> strtoupper($datos['glosa']), 
			'importe_local_con_igv'=> $datos['importe_local_con_igv']
			//'iddetallemovimiento_ref'=> $datos['item']  
		);
		return $this->db->insert('ct_detalle_movimiento', $data); 
	}
	public function m_anular_egreso($id)
	{
		$data = array( 
			'estado_movimiento' => 0, 
			'fecha_anulacion' => date('Y-m-d H:i:s'), 
			'iduseranulacion' => $this->sessionHospital['idusers']
		);
		$this->db->where('idmovimiento',$id);
		if($this->db->update('ct_movimiento', $data)){ 
			return true;
		}else{
			return false;
		}
	}
	public function m_cambiar_estado_egreso_a_aprobado($datos)
	{
		$data = array(
			'estado_egreso' => 3, // APROBADO
			'fecha_aprobacion' => date('Y-m-d H:i:s'), 
			'iduseraprobacion' => $this->sessionHospital['idusers']

		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		if($this->db->update('far_movimiento', $data)){ 
			return true;
		}else{
			return false;
		}
	}
	public function m_cambiar_estado_egreso_a_pagado($datos)
	{
		$data = array(
			'estado_egreso' => 4, // PAGADO
			'fecha_pago' => date('Y-m-d H:i:s'),  
			'iduserpago' => $this->sessionHospital['idusers']

		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		if($this->db->update('far_movimiento', $data)){ 
			return true;
		}else{
			return false;
		}
	}
	public function m_cambiar_estado_egreso_a_observado($datos)
	{
		$data = array(
			'estado_egreso' => 2, // OBSERVADO 
			'fecha_observacion' => date('Y-m-d H:i:s'),  
			'iduserobservacion' => $this->sessionHospital['idusers']
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']);
		if($this->db->update('far_movimiento', $data)){ 
			return true;
		}else{
			return false;
		}
	}
}