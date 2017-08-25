<?php
class Model_caja_chica extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
	public function m_cargar_caja_chica_disponible_cbo(){ 
		$this->db->select('cch.idcajachica, cch.nombre, cch.estado_cch,cch.idsedeempresaadmin,cch.idcentrocosto');
		$this->db->select('cch.numero_cheque, cch.monto_cheque::numeric, cch.idusuarioresponsable',FALSE);
		$this->db->from('ct_caja_chica cch');		
		$this->db->where('cch.estado_cch', 1); // activa	
		$this->db->where('cch.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // activa	
		$this->db->where('cch.idusuarioresponsable', $this->sessionHospital['idempleado']); // empleado sesion	
		$this->db->where('cch.idcajachica NOT IN 
										(select acc.idcajachica 
										 from ct_apertura_caja_chica acc 
										 where cch.idcajachica = acc.idcajachica AND acc.estado_acc IN (1,2) )');
		$this->db->order_by('cch.idcajachica','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_saldo_anterior($datos)
	{
		$this->db->select('idcajachica,idusuarioresponsable, idcentrocosto, fecha_apertura, saldo::NUMERIC',FALSE);
		$this->db->from('ct_apertura_caja_chica');
		$this->db->where('idcajachica', $datos['idcajachica']);
		$this->db->where('estado_acc', 3);
		$this->db->order_by('idaperturacajachica', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_caja_chica_usuario(){
		$this->db->select('(acc.saldo::NUMERIC) AS saldo_numeric, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric',FALSE);
		$this->db->select('acc.idaperturacajachica, acc.idcajachica, acc.idusuarioresponsable, acc.idcentrocosto, acc.fecha_apertura,
			acc.monto_inicial, acc.fecha_liquidacion, acc.saldo, acc.observaciones_acc,acc.numero_cheque, acc.estado_acc');
		$this->db->select('cch.idcentrocosto AS idcentrocostocaja, cch.nombre AS nombre_caja');
		$this->db->select('cc.nombre_cc, cc.codigo_cc');
		$this->db->from('ct_apertura_caja_chica acc');		
		$this->db->join('ct_caja_chica cch', 'cch.idcajachica = acc.idcajachica AND cch.idsedeempresaadmin = '. $this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('ct_centro_costo cc', 'cch.idcentrocosto = cc.idcentrocosto','left');
		$this->db->where_in('acc.estado_acc', array(1,2)); // abierta o liquidada	
		$this->db->where('acc.idusuarioresponsable', $this->sessionHospital['idempleado']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_apertura_caja_chica($idaperturacajachica)
	{
		$this->db->select('(acc.saldo::NUMERIC) AS saldo_numeric, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric',FALSE);
		$this->db->select('acc.idaperturacajachica, acc.idcajachica, acc.idusuarioresponsable, acc.idcentrocosto, acc.fecha_apertura,
			acc.monto_inicial, acc.fecha_liquidacion, acc.saldo, acc.observaciones_acc,acc.numero_cheque, acc.estado_acc');
		$this->db->select('cch.idcentrocosto AS idcentrocostocaja, cch.nombre AS nombre_caja');
		$this->db->select('cc.nombre_cc, cc.codigo_cc');
		$this->db->from('ct_apertura_caja_chica acc');		
		$this->db->join('ct_caja_chica cch', 'cch.idcajachica = acc.idcajachica AND cch.idsedeempresaadmin = '. $this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('ct_centro_costo cc', 'cch.idcentrocosto = cc.idcentrocosto','left');
		$this->db->where_in('acc.estado_acc', array(1,2)); // abierta o liquidada	
		$this->db->where('acc.idaperturacajachica', $idaperturacajachica);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_saldo_caja($datos)
	{
		$data = array( 
			'saldo' => $datos['saldo']
		);
		$this->db->where('idaperturacajachica',$datos['idaperturacajachica']); 
		return $this->db->update('ct_apertura_caja_chica', $data);	
	}
	public function m_actualizar_caja_maestra($idcajachica)
	{
		$data = array( 
			'numero_cheque' => NULL, 
			'monto_cheque' => NULL, 
			'idusuarioresponsable' => NULL 
		);
		$this->db->where('idcajachica',$idcajachica); 
		return $this->db->update('ct_caja_chica', $data); 
	}
	public function m_cargar_movimientos($paramDatos, $paramPaginate){ 
		$this->db->select("(emp.descripcion) AS empresa",FALSE);
		$this->db->select('mo.idmovimiento, mo.dir_movimiento, mo.numero_documento, mo.fecha_registro, mo.fecha_emision, mo.fecha_credito, mo.forma_pago, mo.orden_compra, mo.codigo_plan, 
			mo.servicio_asignado, mo.periodo_asignado, mo.modo_igv, mo.total_impuesto_inafecto, mo.sub_total, mo.total_impuesto, mo.total_a_pagar, mo.fecha_pago, mo.fecha_aprobacion, mo.detraccion, mo.deposito, 
			mo.estado_movimiento, emp.idempresa, emp.ruc_empresa, op.idoperacion, op.idoperacion, op.descripcion_op, 
			td.idtipodocumento, td.descripcion_td, td.abreviatura, td.porcentaje_imp, 
			sop.idsuboperacion, sop.descripcion_sop, mon.idmoneda, mon.moneda, mon.simbolo, 
			tc.idtipocambio, acc.idaperturacajachica, acc.estado_acc, mo.estado_color, empl.idempleado'); 
		$this->db->select('dmo.glosa,dmo.importe_local, dmo.importe_local_con_igv, dmo.iddetallemovimiento');
		$this->db->select("CONCAT_WS(' ', empl.nombres, empl.apellido_paterno, empl.apellido_materno) AS empleado");
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('ct_apertura_caja_chica acc','mo.idaperturacajachica = acc.idaperturacajachica');
		$this->db->join('users us','mo.idusuario = us.idusers');
		$this->db->join('rh_empleado empl','us.idusers = empl.iduser');
		
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('sea.idempresaadmin', $this->sessionHospital['idempresaadmin']);
		$this->db->where('op.tipo_operacion', 3); // caja chica 
		$this->db->where('mo.idaperturacajachica', $paramDatos['idaperturacajachica']); // caja chica 
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
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
	public function m_count_movimientos($paramDatos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN mo.estado_movimiento IN (1) THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('ct_apertura_caja_chica acc','mo.idaperturacajachica = acc.idaperturacajachica');
		$this->db->join('users us','mo.idusuario = us.idusers');
		$this->db->join('rh_empleado empl','us.idusers = empl.iduser');
		
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('sea.idempresaadmin', $this->sessionHospital['idempresaadmin']);
		$this->db->where('op.tipo_operacion', 3); // caja chica
		$this->db->where('mo.idaperturacajachica', $paramDatos['idaperturacajachica']); // caja chica
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
		if( @$paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_caja_abierta($datos){
		$this->db->select('idcajachica,idusuarioresponsable, idcentrocosto, fecha_apertura, monto_inicial, estado_acc');
		$this->db->from('ct_apertura_caja_chica');
		$this->db->where('idcajachica', $datos['cajaChica']['idcajachica']);
		$this->db->where('estado_acc', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// MOVIMIENTOS DE CAJA CHICA 
	public function m_registrar_apertura_caja($datos){
		$data = array(
			'idusuarioresponsable' 	=> $this->sessionHospital['idempleado'],
			'idcentrocosto' 		=> $datos['cajaChica']['idcentrocosto'],
			'fecha_apertura' 		=> date('Y-m-d H:i:s',strtotime($datos['fecha_apertura'])),
			'idcajachica' 			=> $datos['cajaChica']['idcajachica'],
			'monto_inicial' 		=> $datos['monto_inicial'],
			'saldo' 				=> $datos['monto_inicial'],
			'numero_cheque' 		=> $datos['numero_cheque'],
			'monto_cheque' 			=> $datos['monto_cheque'],
			'saldo_anterior' 		=> $datos['saldo_anterior'],
			'observaciones_acc' 	=> empty($datos['observaciones_acc']) ? null : $datos['observaciones_acc'],
			'createdat' 			=> date('Y-m-d H:i:s'), 
			'updatedat' 			=> date('Y-m-d H:i:s'),
			);
		return $this->db->insert('ct_apertura_caja_chica', $data);
	}
	public function m_registrar($datos){
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
			'fecha_credito'=> $datos['fecha_emision'], 
			'forma_pago' => 1, //AL CONTADO
			'modo_igv'=> 1, // SIN IGV 
			'sub_total'=> $datos['subtotal'],
			'total_impuesto'=> $datos['impuesto'],
			'total_a_pagar'=> $datos['total'],
			'tipo_cambio_compra'=> $datos['compra'],
			'tipo_cambio_venta'=> $datos['venta'],
			'guia_remision'=> empty($datos['guia_remision']) ? NULL:$datos['guia_remision'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'], 
			'idaperturacajachica' => $datos['idaperturacajachica'], 
			'createdat' => date('Y-m-d H:i:s'), 
			'updatedat' => date('Y-m-d H:i:s'), 
			'detraccion'=> $datos['detraccion'], 
			'deposito'=> $datos['deposito'],
			'codigo_plan'=> $datos['codigo_plan'],
			'serie_documento'=>$datos['serie_documento'] 
		);
		return $this->db->insert('ct_movimiento', $data);
	}
	public function m_registrar_detalle($datos){
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'], 
			'codigo_plan' => $datos['codigo'], 
			'importe_local' => $datos['importe'], 
			'tipo_cambio_compra'=> $datos['compra'], 
			'tipo_cambio_venta'=> $datos['venta'] ,
			'glosa'=>strtoupper($datos['descripcion']),
			'idcentrocosto'=>$datos['idcentrocosto'],
			'importe_local_con_igv'=> $datos['importe_local_con_igv'] 
		);
		return $this->db->insert('ct_detalle_movimiento', $data); 
	}
	public function m_anular_movimiento($idmovimiento){
		$data = array( 
			'estado_movimiento' => 0, 
			'fecha_anulacion' => date('Y-m-d H:i:s'), 
			'iduseranulacion' => $this->sessionHospital['idusers']
		);
		$this->db->where('idmovimiento',$idmovimiento);
		return $this->db->update('ct_movimiento', $data);		
	}

	public function m_revertir_anular_movimiento($idmovimiento){
		$data = array( 
			'estado_movimiento' => 1, 
			'fecha_anulacion' => NULL, 
			'iduseranulacion' => NULL
		);
		$this->db->where('idmovimiento',$idmovimiento);
		return $this->db->update('ct_movimiento', $data);		
	}
	public function m_tiene_caja_chica_usuario(){ 
		$this->db->select('COUNT(acc.idaperturacajachica) AS contador');
		$this->db->from('ct_apertura_caja_chica acc'); 
		$this->db->join('ct_caja_chica cc','acc.idcajachica = cc.idcajachica'); 
		$this->db->where_in('acc.estado_acc', array(1,2)); // activa	
		$this->db->where('acc.idusuarioresponsable', $this->sessionHospital['idempleado']); // activa	
		$this->db->where('cc.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->limit(1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'] > 0 ? TRUE : FALSE;
	}

	public function m_liquidar_caja_chica($datos){
		$data = array( 
			'estado_acc' => 2, 
			'fecha_liquidacion' => date('Y-m-d H:i:s'), 
		);
		$this->db->where('idaperturacajachica',$datos['idaperturacajachica']);
		return $this->db->update('ct_apertura_caja_chica', $data);		
	}	

	public function m_cerrar_caja_chica($datos){
		$data = array( 
			'estado_acc' => 3, 
			'idusuariocierre' =>  $this->sessionHospital['idempleado'], 
			'fecha_cierre' => date('Y-m-d H:i:s'), 
		);
		$this->db->where('idaperturacajachica',$datos['idaperturacajachica']);
		return $this->db->update('ct_apertura_caja_chica', $data);		
	}

	public function m_cargar_movimientos_una_caja($paramDatos, $paramPaginate){  
		$this->db->select("(emp.descripcion) AS empresa",FALSE);
		$this->db->select('mo.idmovimiento, mo.dir_movimiento, mo.numero_documento, mo.fecha_registro, mo.fecha_emision, mo.fecha_credito, 
			mo.forma_pago, mo.orden_compra, mo.codigo_plan, mo.servicio_asignado, mo.periodo_asignado, mo.modo_igv, mo.total_impuesto_inafecto, 
			mo.sub_total, mo.total_impuesto, mo.total_a_pagar, mo.fecha_pago, mo.fecha_aprobacion, mo.detraccion, mo.deposito, 
			mo.estado_movimiento, emp.idempresa, emp.ruc_empresa, op.idoperacion, op.idoperacion, op.descripcion_op, td.idtipodocumento, 
			td.descripcion_td, td.abreviatura, td.porcentaje_imp, sop.idsuboperacion, sop.descripcion_sop, mon.idmoneda, mon.moneda, 
			mon.simbolo, tc.idtipocambio, mo.idaperturacajachica, mo.estado_color, empl.idempleado'); 
		$this->db->select('dmo.glosa,dmo.importe_local, dmo.iddetallemovimiento, dmo.importe_local_con_igv, dmo.glosa');
		$this->db->select('(acc.saldo::NUMERIC) as saldo_caja, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric');
		$this->db->select("CONCAT_WS(' ', empl.nombres, empl.apellido_paterno, empl.apellido_materno) AS empleado"); 
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('ct_apertura_caja_chica acc','acc.idaperturacajachica = mo.idaperturacajachica');
		$this->db->join('users us','mo.idusuario = us.idusers','left');
		$this->db->join('rh_empleado empl','us.idusers = empl.iduser','left');
		
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('op.tipo_operacion', 3); // caja chica
		$this->db->where('mo.idaperturacajachica', $paramDatos['idaperturacajachica']); // caja chica
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
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
	public function m_count_movimientos_una_caja($paramDatos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN mo.estado_movimiento IN (1) THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('ct_movimiento mo');
		$this->db->join('ct_detalle_movimiento dmo','mo.idmovimiento = dmo.idmovimiento');
		$this->db->join('tipo_documento td','mo.idtipodocumento = td.idtipodocumento');
		$this->db->join('empresa emp','mo.idempresa = emp.idempresa');
		$this->db->join('ct_operacion op','mo.idoperacion = op.idoperacion');
		$this->db->join('ct_suboperacion sop','mo.idsuboperacion = sop.idsuboperacion');
		$this->db->join('ct_moneda mon','mo.idmoneda = mon.idmoneda');
		$this->db->join('ct_tipo_cambio tc','mo.idtipocambio = tc.idtipocambio');
		$this->db->join('sede_empresa_admin sea','mo.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('ct_apertura_caja_chica acc','acc.idaperturacajachica = mo.idaperturacajachica');
		
		$this->db->where('mo.dir_movimiento', 2); // ¡salida de dinero! 
		$this->db->where('op.tipo_operacion', 3); // caja chica
		$this->db->where('mo.idaperturacajachica', $paramDatos['idaperturacajachica']); // caja chica
		$this->db->where_in('mo.estado_movimiento', array(0,1,2,3,4));
		if( @$paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		return $this->db->get()->row_array();
	}

	public function m_esta_aperturada_caja($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_caja_chica cch');		
		$this->db->where('cch.estado_cch', 1); // activa	
		$this->db->where('cch.idcajachica', $datos['idcajachica']); // caja asignada	
		$this->db->where('cch.idsedeempresaadmin', $datos['idsedeempresa']); // empresa	
		$this->db->where('cch.idusuarioresponsable', $datos['idresponsable']); // empleado asignado	
		$this->db->where("cch.idcajachica IN 
										(select acc.idcajachica 
										 from ct_apertura_caja_chica acc 
										 where cch.idcajachica = acc.idcajachica AND acc.estado_acc IN (1,2) )");
		$fData = $this->db->get()->row_array();
		return ($fData['contador'] > 0) ? TRUE : FALSE;
	}

	public function m_tiene_caja_asignada($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_caja_chica cch');		
		$this->db->where('cch.estado_cch', 1); // activa	
		$this->db->where('cch.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // empresa	
		$this->db->where('cch.idusuarioresponsable', $datos['idresponsable']); // empleado asignado			
		$fData = $this->db->get()->row_array();
		return ($fData['contador'] > 0) ? TRUE : FALSE;
	}
}