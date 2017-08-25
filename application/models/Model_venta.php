<?php
class Model_venta extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ventas_esta_caja($paramDatos=FALSE,$paramPaginate=FALSE) 
	{
		/* VENTAS */
		$this->db->select("(CASE WHEN v.estado = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("total_a_pagar::numeric",FALSE);
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v,
			fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, '' AS especialidad
		");  
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select(" ('nc') AS tipofila ");
		$this->db->select("nc.monto::numeric",FALSE);
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v, 
			fecha_creacion_nc, ticket_nc, 7,(CASE WHEN nc.tipo_salida = 1 THEN 'NOTA DE CRÉDITO' ELSE 'EXTORNO' END) , 
			'0', '', c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, esp.nombre AS especialidad
		", FALSE);
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa'); 
		$this->db->join('especialidad esp','esp.idespecialidad = nc.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left'); 
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','nc.idcaja = cj.idcaja'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('medico m','v.idmedico = m.idmedico','left'); 
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito  
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		// var_dump($sqlVentas,$sqlNotaCredito); exit();
		if( $paramPaginate['sortName'] ){
			$sqlMaster.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}else{
			$sqlMaster.= ' ORDER BY ticket_venta';
		}
		if($paramPaginate['pageSize'] ){
			$sqlMaster.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}
	public function m_cargar_ventas_detalle_esta_caja($paramDatos=FALSE,$paramPaginate=FALSE)
	{
		/* VENTAS */
		$this->db->select("(CASE WHEN v.estado = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE);
		$this->db->select("total_detalle::numeric, total_a_pagar::numeric",FALSE);
		$this->db->select('v.idventa, v.estado, orden_venta, v.paciente_atendido_v,
			fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			pm.descripcion AS producto, 
			esp.nombre AS especialidad
		'); 
		$this->db->from('venta v'); 
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		// $this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto','left');
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select(" ('nc') AS tipofila ");
		$this->db->select("nc.monto::numeric, nc.monto::numeric",FALSE);
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v, 
			fecha_creacion_nc, ticket_nc, 7,'NOTA DE CRÉDITO', 
			'0', '', c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			'',esp.nombre AS especialidad
		"); 
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa'); 
		$this->db->join('especialidad esp','nc.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito  
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		// var_dump($sqlVentas,$sqlNotaCredito); exit();
		if( $paramPaginate['sortName'] ){
			$sqlMaster.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}else{
			$sqlMaster.= ' ORDER BY ticket_venta';
		}
		if($paramPaginate['pageSize'] ){ 
			$sqlMaster.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}
	public function m_cargar_ventas_caja_actual($paramPaginate,$paramDatos=FALSE) 
	{
		/* VENTAS */
		$this->db->select('v.idventa, v.estado, orden_venta, v.paciente_atendido_v, v.idespecialidad,
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, u.username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
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
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v, v.idespecialidad,
			nc.monto, '', nc.monto, fecha_creacion_nc, ticket_nc, 7,(CASE WHEN nc.tipo_salida = 1 THEN 'NOTA DE CRÉDITO' ELSE 'EXTORNO' END), 
			'0', '', c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		"); 
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito  
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['sedeempresa']) ){ 
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
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		// var_dump($sqlVentas,$sqlNotaCredito); exit();
		if( $paramPaginate['sortName'] ){
			$sqlMaster.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			$sqlMaster.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}
	public function m_count_sum_ventas_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		/* VENTAS */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN v.estado = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes 
		//  $this->db->where('estado_cli', 1); // cliente 
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
		$sqlVentas = $this->db->get_compiled_select();

		$this->db->reset_query();

		/* NOTA CREDITO */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN (v.estado = 1 AND nc.estado_nc = 1 ) THEN (nc.monto::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito 
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
		$sqlNotaCredito = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CI_DB_query_builder::_reset_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster = 'SELECT SUM(contador) AS contador, SUM(sumatotal) AS sumatotal FROM ( '.$sqlMaster.' ) AS groupTotal';
		$query = $this->db->query($sqlMaster);
		$fData = $query->row_array();
		// $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_ventas_anuladas_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 0); // venta 
		// $this->db->where('estado_cli', 1); // cliente 
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
		$this->db->select('COUNT(*) AS contador, SUM(total_a_pagar) AS sumaTotal',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 0); // venta 
		// $this->db->where('estado_cli', 1); // cliente 
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
		return $fData;
	}
	public function m_cargar_ventas_en_espera_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 2); // EN ESPERA 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		//$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		}
		if( !empty($paramDatos['especialidad']) ){ 
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
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado', 2); // EN ESPERA 
		// $this->db->where('estado_cli', 1); // cliente 
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
	public function m_cargar_ventas_con_descuento_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('tiene_descuento', 1); // CON DESCUENTO 
		$this->db->where('v.estado <>', 0); // NO ANULADOS 
		// $this->db->where('estado_cli', 1); // cliente 
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
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_ventas_con_descuento_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left'); 
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('medico m','v.idmedico = m.idmedico','left'); 
		$this->db->where('tiene_descuento', 1); // CON DESCUENTO 
		$this->db->where('v.estado <>', 0); // NO ANULADOS 
		// $this->db->where('estado_cli', 1); // cliente 
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
	public function m_cargar_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('idventa, v.estado, orden_venta, solicita_impresion, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where_in('v.estado', array(1,0)); // ACTIVOS 
		$this->db->where_in('v.solicita_impresion', array(1,3)); // ENVIO DE SOLICITUD, ACEPTACION SOLICITUD 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		//$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		// if( !empty($paramDatos['cajamaster']) ){ 
		// 	$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		// }
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
			//$this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
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
	public function m_count_ventas_con_solicitud_impresion_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where_in('v.estado', array(1,0)); // ACTIVOS 
		$this->db->where_in('v.solicita_impresion', array(1,3)); // ENVIO DE SOLICITUD, ACEPTACION SOLICITUD 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		//$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		if( !empty($paramDatos['cajamaster']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']);
		}
		if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
			// $this->db->where('u.idusers', $this->sessionHospital['idusers']); // solo las ventas del usuario 
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
	public function m_cargar_producto_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('v.idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento, 
			d.cantidad, d.precio_unitario, d.descuento_asignado, d.total_detalle, 
			pm.idproductomaster, pm.descripcion AS producto,
			esp.idespecialidad, esp.nombre AS especialidad, 
			tp.idtipoproducto, tp.nombre_tp 
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto','left'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('medico m','v.idmedico = m.idmedico','left'); 
		$this->db->where('v.estado <>', 0); //  
		// $this->db->where('estado_cli', 1); // cliente 
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
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_producto_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto','left'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('medico m','v.idmedico = m.idmedico','left'); 
		$this->db->where('v.estado', 2); // EN ESPERA 
		// $this->db->where('estado_cli', 1); // cliente 
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
	public function m_cargar_ventas_y_atenciones_desde_hasta($paramDatos=FALSE)
	{
		// subconsulta para obtener los idsedeempresaadmin
		$this->db->select('idsedeempresaadmin');
		$this->db->from('sede_empresa_admin');
		$this->db->where('estado_sea',1);
		$this->db->where('idsede',$paramDatos['sede']['id']);
		if($paramDatos['empresaAdmin']['id'] != 0){ 
			$this->db->where('idempresaadmin',$paramDatos['empresaAdmin']['id']);
		}
		$sedeempresa = $this->db->get_compiled_select(); // (c.nombres || ' ' ||c.apellido_paterno || ' ' || c.apellido_materno)
		$this->db->reset_query();
		
		/* VENTAS */ 
		$this->db->select("('a') AS orden_abc,(CASE WHEN d.si_tipo_campania = 0 THEN 'REGULAR' WHEN d.si_tipo_campania = 1 OR d.si_tipo_campania = 2 THEN 'CAMPAÑIA'  END) AS tipo_campania",FALSE);
		$this->db->select("(CASE WHEN v.estado = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("(CASE WHEN ec.ruc_empresa IS NULL THEN c.num_documento ELSE ec.ruc_empresa END) AS dniruc",FALSE);
		$this->db->select("sub_total::numeric, total_igv::numeric, total_a_pagar::numeric,d.total_detalle::numeric",FALSE); 
		$this->db->select("(CASE WHEN ec.ruc_empresa IS NULL THEN CONCAT_WS(' ',c.nombres,c.apellido_paterno,c.apellido_materno) ELSE ec.descripcion END) AS paciente",FALSE);
		$this->db->select("CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medico",FALSE); 
		$this->db->select("v.idventa, d.iddetalle, d.cantidad, v.estado, v.orden_venta, v.paciente_atendido_v,
			fecha_venta, fecha_atencion_det, v.ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.num_documento, c.sexo, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, esp.nombre AS especialidad, pm.idproductomaster, (pm.descripcion) AS producto, tp.idtipoproducto, nombre_tp,
			c.direccion,dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito, c.telefono, c.celular, c.email, fecha_nacimiento,
			emp.idempresa ,(emp.descripcion) AS empresa, d.paciente_atendido_det, h.idhistoria, esp.idespecialidad, (cmp.descripcion) AS campania 
		");
		$this->db->select('sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, proc.idprocedencia, (proc.descripcion) AS procedencia');
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('especialidad esp','esp.idespecialidad = v.idespecialidad'); 
		$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad','left'); 
		$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1','left'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('historia h','c.idcliente = h.idcliente');
		$this->db->join('procedencia proc','c.idprocedencia = proc.idprocedencia','left');
		$this->db->join("ubigeo dpto","c.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","c.idprovincia = prov.idprovincia AND prov.iddepartamento = c.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"c.iddistrito = dist.iddistrito AND dist.iddepartamento = c.iddepartamento AND dist.idprovincia = c.idprovincia", 'left');
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('campania cmp','d.idcampania = cmp.idcampania','left'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');  
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('atencion_medica am','d.iddetalle = am.iddetalle AND am.estado_am = 1','left'); 
		$this->db->join('medico med','v.idmedico = med.idmedico','left'); // medico q genero la orden 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('v.estado <>', 2); // entrantes  

		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		//$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		// $this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select("('z') AS orden_abc,NULL,('nc') AS tipofila ",FALSE);
		$this->db->select("(CASE WHEN ec.ruc_empresa IS NULL THEN c.num_documento ELSE ec.ruc_empresa END) AS dniruc",FALSE);
		$this->db->select("NULL,NULL,NULL,nc.monto::numeric",FALSE);
		$this->db->select("(c.nombres || ' ' ||c.apellido_paterno || ' ' || c.apellido_materno) AS paciente",FALSE); 
		$this->db->select("NULL, v.idventa, NULL, NULL, v.estado, v.orden_venta, v.paciente_atendido_v, 
			fecha_creacion_nc, NULL, ticket_nc, 7,(CASE WHEN nc.tipo_salida = 1 THEN 'NOTA DE CRÉDITO' ELSE 'EXTORNO' END) , 
			'0', '', c.idcliente, c.num_documento, c.sexo, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, esp.nombre AS especialidad,NULL,NULL,NULL,NULL,
			c.direccion,dpto.descripcion_ubig AS departamento, prov.descripcion_ubig AS provincia, dist.descripcion_ubig AS distrito, c.telefono, c.celular, c.email, fecha_nacimiento, emp.idempresa ,(emp.descripcion) AS empresa, NULL,h.idhistoria, esp.idespecialidad,NULL
		", FALSE); 
		$this->db->select('sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, NULL,NULL',FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('empresa_especialidad ee','v.idempresaespecialidad = ee.idempresaespecialidad','left'); 
		$this->db->join('empresa emp','ee.idempresa = emp.idempresa AND estado_em = 1','left'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('especialidad esp','esp.idespecialidad = nc.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('historia h','c.idcliente = h.idcliente');
		$this->db->join("ubigeo dpto","c.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		$this->db->join("ubigeo prov","c.idprovincia = prov.idprovincia AND prov.iddepartamento = c.iddepartamento AND prov.iddistrito = '00'", 'left');
		$this->db->join('ubigeo dist',"c.iddistrito = dist.iddistrito AND dist.iddepartamento = c.iddepartamento AND dist.idprovincia = c.idprovincia", 'left');
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente','left');
		$this->db->join('sede_empresa_admin sea','nc.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		//$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('fecha_creacion_nc BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('nc.tipo_salida <>', 2); // TIPO SALIDA SIN EXTORNOS 

		// $this->db->where('estado_cli', 1); // cliente 
		// $this->db->where('estado_emp <>', 0); // empresa_admin 
		// $this->db->where('estado_se', 1); // sede 

		$this->db->where('estado_nc', 1); // nota crédito  
		// $this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		//$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		// $this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('nc.idsedeempresaadmin IN ('.$sedeempresa . ')');
		
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster.= ' ORDER BY orden_abc, ticket_venta';
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}
	public function m_cargar_ventas_desde_hasta($paramDatos=FALSE)
	{
		/* VENTAS */
		$this->db->select("(CASE WHEN v.estado = 0 THEN 'a' ELSE 'v' END) AS tipofila",FALSE); // especialidad
		$this->db->select("(CASE WHEN ec.ruc_empresa IS NULL THEN c.num_documento ELSE ec.ruc_empresa END) AS dniruc",FALSE);
		$this->db->select("sub_total::numeric, total_igv::numeric, total_a_pagar::numeric",FALSE); 
		$this->db->select("(CASE WHEN ec.ruc_empresa IS NULL THEN (c.nombres || ' ' ||c.apellido_paterno || ' ' || c.apellido_materno) ELSE ec.descripcion END) AS paciente",FALSE); 
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v,
			fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, esp.nombre AS especialidad
		");  
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('especialidad esp','esp.idespecialidad = v.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		//$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		//$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select(" ('nc') AS tipofila ");
		$this->db->select("c.num_documento AS dniruc",FALSE);
		$this->db->select("0,0,nc.monto::numeric",FALSE);
		$this->db->select("(c.nombres || ' ' ||c.apellido_paterno || ' ' || c.apellido_materno) AS paciente",FALSE); 
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v, 
			fecha_creacion_nc, ticket_nc, 7,(CASE WHEN nc.tipo_salida = 1 THEN 'NOTA DE CRÉDITO' ELSE 'EXTORNO' END) , 
			'0', '', c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, esp.nombre AS especialidad
		", FALSE);
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('especialidad esp','esp.idespecialidad = nc.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		//$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('fecha_creacion_nc BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->where('v.estado <>', 2); // entrantes  
		// $this->db->where('nc.tipo_salida <>', 2); // TIPO SALIDA SIN EXTORNOS 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito  
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		//$this->db->where('cj.idcaja', $paramDatos['idcaja']);
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		
		$sqlNotaCredito = $this->db->get_compiled_select();
		$sqlMaster = $sqlVentas.' UNION ALL '.$sqlNotaCredito;
		$sqlMaster.= ' ORDER BY ticket_venta';
		$query = $this->db->query($sqlMaster);
		return $query->result_array();
	}
	/* LISTA DETALLE DE VENTA */
	public function m_cargar_detalle_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('v.idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			d.cantidad, d.precio_unitario, d.descuento_asignado, d.total_detalle, 
			p.idproductomaster, p.descripcion AS producto,
			esp.idespecialidad, esp.nombre AS especialidad
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master p','d.idproductomaster = p.idproductomaster'); 
		$this->db->join('especialidad esp','p.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		// $this->db->where('v.estado <>', 0); // 
		// $this->db->where('cj.estado', 1); // caja abierta 
		// $this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('v.idventa', $paramDatos['id']); 
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_detalle_venta_caja_actual($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador', FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master p','d.idproductomaster = p.idproductomaster'); 
		$this->db->join('especialidad esp','p.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		// $this->db->where('v.estado <>', 0); // 
		// $this->db->where('cj.estado', 1); // caja abierta 
		// $this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('v.idventa', $paramDatos['id']); 
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_ultima_venta()
	{ 
		$this->db->select('idventa, orden_venta');
		$this->db->from('venta v');
		// $this->db->join('caja c','c.idcaja = v.idcaja'); 
		// $this->db->where('estado', 1); // ya no se pone filtro porque el codigo generado tendrá que ser diferente asi esté anulado
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->order_by('idventa','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultima_venta_caja($paramDatos)
	{ 
		$this->db->select('idventa, orden_venta');
		$this->db->from('venta v');
		//$this->db->join('caja c','c.idcaja = v.idcaja');
		// $this->db->where('estado', 1); // ya no se pone filtro porque el codigo generado tendrá que ser diferente asi esté anulado
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('idcaja', $paramDatos['idcaja']); 
		// $this->db->order_by('orden_venta','DESC');
		$this->db->order_by('idventa','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_venta_por_id($idventa)
	{ 
		$this->db->select('idventa, orden_venta, estado, tiene_impresion, tiene_reimpresion,paciente_atendido_v');
		$this->db->from('venta');
		$this->db->where('idventa', $idventa); 
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_venta_por_columna($datos) // nro_consultorio
	{
		$this->db->select("(SELECT MAX(idprogmedico_prog) FROM detalle sc_de WHERE sc_de.idventa = v.idventa LIMIT 1 ) AS si_procedimiento",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(c.fecha_nacimiento)) AS edad",FALSE);
		$this->db->select("sub_total::numeric AS sub_total_num, total_igv::numeric AS total_igv_num, total_a_pagar::numeric AS total_a_pagar_num, ",FALSE);
		$this->db->select('v.idventa, orden_venta, v.estado, v.tiene_impresion, v.tiene_reimpresion, solicita_impresion, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, v.es_convenio,
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, c.celular, 
			gr.name as grupo, gr.idgroup, tc.idtipocliente, tc.descripcion_tc AS convenio,
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, s.direccion_se, ea.idempresaadmin, ea.razon_social AS empresa, ea.ruc, ea.nombre_legal, ea.domicilio_fiscal, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, maquina_registradora, 
			mp.idmediopago, descripcion_med, td.idtipodocumento, td.descripcion_td, esp.nro_consultorio, 
			u.idusers, u.username, u.email, h.idhistoria, ec.idempresacliente, ec.descripcion AS empresa_cliente, ec.ruc_empresa AS ruc_cliente'); 
		$this->db->from('venta v');
		$this->db->join('especialidad esp','v.idespecialidad = esp.idespecialidad');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente','left');
		$this->db->join('historia h','c.idcliente = h.idcliente','left');
		$this->db->join('tipo_cliente tc','c.idtipocliente = tc.idtipocliente','left');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('users uc','c.idusuariocreacion = uc.idusers','left');
		$this->db->join('users_groups ugr','uc.idusers = ugr.idusers','left');		
		$this->db->join('group gr','gr.idgroup = ugr.idgroup','left');		
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente', 'left');		
		$this->db->where($datos['searchColumn'], $datos['searchText']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_venta_con_detalle_por_columna($datos) // fecha_atencion_cita especialidad 
	{
		$this->db->select('v.idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, 
			d.cantidad, d.precio_unitario, d.descuento_asignado, d.total_detalle, 
			pm.idproductomaster, pm.descripcion AS producto, td.idtipodocumento, descripcion_td, 
			esp.idespecialidad, esp.nombre AS especialidad, esp.nro_consultorio, d.idpaquete, pq.descripcion AS paquete, 
			pc.idprogcita, pc.fecha_atencion_cita, amb.idambiente, amb.numero_ambiente, 
			dpm.hora_inicio_det, dpm.hora_fin_det, dpm.si_adicional, dpm.numero_cupo 
		'); 
		$this->db->select("CONCAT(med.med_apellido_paterno,' ',med.med_apellido_materno,', ',med.med_nombres) AS medico",FALSE);
		$this->db->from('venta v'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad');
		$this->db->join('paquete pq','d.idpaquete = pq.idpaquete', 'left');
		$this->db->join('pa_prog_cita pc','d.idprogcita = pc.idprogcita', 'left');
		$this->db->join('pa_detalle_prog_medico dpm','pc.iddetalleprogmedico = dpm.iddetalleprogmedico', 'left');
		$this->db->join('pa_prog_medico pmed','dpm.idprogmedico = pmed.idprogmedico', 'left');
		$this->db->join('medico med','pmed.idmedico = med.idmedico', 'left');
		$this->db->join('pa_ambiente amb','pmed.idambiente = amb.idambiente', 'left');
		$this->db->where_in('v.estado', array( 1,0 )); // venta 
		$this->db->where($datos['searchColumn'], $datos['searchText']); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_esta_venta_con_detalle_por_columna_procedimiento($datos)
	{
		$this->db->select('v.idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, 
			d.cantidad, d.precio_unitario, d.descuento_asignado, d.total_detalle, 
			pm.idproductomaster, pm.descripcion AS producto, td.idtipodocumento, descripcion_td, 
			esp.idespecialidad, esp.nombre AS especialidad, esp.nro_consultorio, d.idpaquete, pq.descripcion AS paquete, 
			(pmed.fecha_programada) AS fecha_atencion_cita, amb.idambiente, amb.numero_ambiente, pmed.hora_inicio, pmed.hora_fin 
		'); 
		$this->db->select("CONCAT(med.med_apellido_paterno,' ',med.med_apellido_materno,', ',med.med_nombres) AS medico",FALSE);
		$this->db->from('venta v'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad');
		$this->db->join('paquete pq','d.idpaquete = pq.idpaquete','left');
		$this->db->join('pa_prog_medico pmed','d.idprogmedico_prog = pmed.idprogmedico');
		$this->db->join('medico med','pmed.idmedico = med.idmedico');
		$this->db->join('pa_ambiente amb','pmed.idambiente = amb.idambiente');
		$this->db->where_in('v.estado', array( 1,0 )); // venta 
		$this->db->where($datos['searchColumn'], $datos['searchText']); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_ordenes_venta($datos)
	{
		$this->db->select('idventa, orden_venta, ticket_venta');
		$this->db->from('venta v');
		$this->db->where('estado', 1); 
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->order_by('idventa','DESC');
		$this->db->limit(6); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_ordenes_venta_cajas_cerradas($datos)
	{
		$this->db->select('idventa, orden_venta, ticket_venta, cl.nombres, cl.apellido_paterno, cl.apellido_materno, v.total_a_pagar, (v.total_a_pagar::numeric) AS total_a_pagar_format');
		$this->db->from('venta v');
		$this->db->join('caja c','v.idcaja = c.idcaja'); 
		$this->db->join('cliente cl','v.idcliente = cl.idcliente');
		$this->db->where('v.estado', 1); // venta // activo
		//$this->db->where('c.estado', 2); // caja // cerrada
		$this->db->where('c.estado <>', 0); // caja // cerrada
		$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->ilike('orden_venta', $datos['search']); 
		$this->db->order_by('idventa','DESC');
		$this->db->limit(6); 
		return $this->db->get()->result_array();
	}

	public function m_registrar_venta($datos)
	{
		$idmedico = NULL;
		$idmedico_externo = NULL;
		if( !empty($datos['medico']) ){
			if($datos['medico']['medico_externo'] == 'SI'){
				$idmedico_externo = ($datos['medico']['id']);
			}else if($datos['medico']['medico_externo'] == 'NO'){
				$idmedico = ($datos['medico']['id']);
			}
			
		}
		$data = array( 
			'idcliente' => (empty($datos['cliente']['id']) ? NULL : $datos['cliente']['id']),
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
			'idespecialidad' => $datos['detalle'][0]['idespecialidad'], // se rellena del detalle 
			// 'idmedico' => (empty($datos['medico']['id']) ? NULL : $datos['medico']['id']),
			'idmedico' => $idmedico,
			'idmedicoexterno' => $idmedico_externo,
			'idcaja' => $datos['idcaja'],
			'iduser' => $this->sessionHospital['idusers'],
			'updatedAt' => date('Y-m-d H:i:s'),
			'createdAt' => date('Y-m-d H:i:s'),
			'orden_venta' => $datos['orden'],
			'ticket_venta' => $datos['ticket'],
			'idtipodocumento' => $datos['idtipodocumento'],
			'sub_total' => $datos['subtotal'],
			'total_igv' => $datos['igv'],
			'total_a_pagar' => $datos['total'],
			'fecha_venta' => date('Y-m-d H:i:s'),
			'idmediopago' => $datos['idmediopago'],
			'idempresacliente' => (empty($datos['empresa']['id']) ? NULL : $datos['empresa']['id']),
			'es_convenio' => $datos['convenio']? 1 : 2,
			'si_paciente_externo' => $datos['pacienteExterno']
			// 'tiene_descuento' => $datos['tiene_descuento']
		);
		return $this->db->insert('venta', $data);
	}
	public function m_registrar_detalle($datos,$datosParent) 
	{
		$data = array( 
			'idventa' => $datos['idventa'],
			'idespecialidad' => $datos['idespecialidad'],
			'idproductomaster' => $datos['id'],
			'idmedico' => (empty($datosParent['medico']['id']) ? NULL : $datosParent['medico']['id']),
			'idcaja' => $datosParent['idcaja'],
			'iduser' => $this->sessionHospital['idusers'],
			'cantidad' => $datos['cantidad'],
			'precio_unitario' => $datos['precio'],
			'descuento_asignado' => $datos['descuento'],
			'total_detalle' => $datos['total'],
			'si_tipo_campania' => $datos['si_tipo_campania'],
			'idcampania' => (empty($datos['idcampania']) ? NULL : $datos['idcampania']),
			'idpaquete' => (empty($datos['idpaquete']) ? NULL : $datos['idpaquete']),
			'si_solicitud' => $datos['si_solicitud'],
			'idsolicitud' => (empty($datos['idsolicitud']) ? NULL : $datos['idsolicitud']),
			'tiposolicitud' => (empty($datos['tiposolicitud']) ? NULL : $datos['tiposolicitud']),
			'precio_modificado' => empty($datos['precio_modificado']) ? 2 : $datos['precio_modificado'],
			'precio_original' => (empty($datos['precio_original']) ? NULL : $datos['precio_original']),
			'precio_costo' => (empty($datos['precio_costo']) ? NULL : $datos['precio_costo']),
			'idprogcita' => (empty($datos['idprogcita']) ? NULL : $datos['idprogcita']),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'idprogmedico_prog' => ($datos['producto']['idtipoproducto'] == 16) ? $datos['detalleCupo']['idprogmedico'] : NULL,
		);
		return $this->db->insert('detalle', $data);
	}

	/* ESTADOS DE LA VENTA  */
	public function m_editar_venta_a_espera($id)
	{
		$data = array(
			'estado' => 2,
			'tiene_descuento' => 1, // si tiene descuento 
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_editar_venta_a_aprobado($id) // DESCUENTO 
	{
		$data = array(
			'estado' => 1,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_anular_venta_caja_actual($id)
	{
		$data = array(
			'estado' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	/* END ESTADOS DE LA VENTA */ 

	public function m_editar_venta_a_impreso($id)
	{
		$data = array( 
			'tiene_impresion' => 1 
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_editar_venta_a_reimpreso($id)
	{
		$data = array(
			'tiene_reimpresion' => 1 
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_editar_venta_a_sin_solicitud_impresion($id)
	{
		$data = array(
			'solicita_impresion' => 2 // NO  
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_editar_venta_a_solicitud_impresion($id)
	{
		$data = array(
			'solicita_impresion' => 1 // MANDO SOLICITUD  
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_editar_venta_a_solicitud_impresion_aprobada($id)
	{
		$data = array(
			'solicita_impresion' => 3 // ACEPTA SOLICITUD   
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_cerrar_caja($id)
	{
		$data = array(
			'estado' => 2, // cerrada 
			'fecha_cierre' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcaja',$id);
		return $this->db->update('caja', $data);
	}

	public function m_abrir_caja($datos)
	{
		$data = array( 
			'iduser' => $this->sessionHospital['idusers'],
			'descripcion' => (empty($datos['descripcion']) ? NULL : $datos['descripcion']),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'idcajamaster' => $datos['idcajamaster'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
			'fecha_apertura' => date('Y-m-d H:i:s'),
			'fecha_cierre' => NULL
		);
		if(!empty($datos['idmodulo']) && $datos['idmodulo'] == 3 ){ 
			$data['modulo'] = 'FARMACIA'; 
		}
		
		return $this->db->insert('caja', $data);
	}
	public function m_actualizar_venta_a_atendido($idVenta)
	{
		$data = array(
			'paciente_atendido_v' => 1, // atendido
			'fecha_atencion_v' => date('Y-m-d H:i:s')
		);
		$this->db->where('idventa',$idVenta);
		return $this->db->update('venta', $data);
	}

	public function m_actualizar_detalle_venta_a_atendido($idDetalle)
	{
		$data = array(
			'paciente_atendido_det' => 1, // atendido 
			'fecha_atencion_det' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetalle',$idDetalle);
		return $this->db->update('detalle', $data);
	}
	public function m_actualizar_detalle_venta_a_atendido_desde_venta($idVenta) 
	{
		$data = array(
			'paciente_atendido_det' => 1, // atendido 
			'fecha_atencion_det' => date('Y-m-d H:i:s')
		);
		$this->db->where('idventa',$idVenta);
		return $this->db->update('detalle', $data);
	}
	/* IMPORTANTE */ 
	// Al registrar la atencion, ya se sabe a que empresa/especialidad a pasado, 
	// entonces se puede actualizar el campo idempresaespecialidad en la tabla VENTA 
	public function m_actualizar_empresa_especialidad_de_venta($idVenta) 
	{
		$data = array(
			'idempresaespecialidad' => $this->sessionHospital['idempresaespecialidad'], 
		);
		$this->db->where('idventa',$idVenta);
		return $this->db->update('venta', $data);
	}
	/* PARA EL REPORTE DE LA LIC RUBY*/
	public function m_cargar_ingresos_mensuales_por_especialidad($allInputs=FALSE)
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

		// subconsulta para obtener total nota de credito
		$this->db->select('SUM(monto::NUMERIC) AS tnc');
		$this->db->from('nota_credito sc_nc');
		$this->db->join('venta sc_v','sc_nc.idventa = sc_v.idventa');
		$this->db->where('sc_nc.idespecialidad = esp.idespecialidad');
		$this->db->where('estado_nc', 1);
		$this->db->where('EXTRACT(MONTH FROM (sc_nc.fecha_creacion_nc)) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT (YEAR FROM (sc_nc.fecha_creacion_nc)) = ', $allInputs['anioDesdeCbo']);
		$this->db->where('sc_v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$totalNC = $this->db->get_compiled_select();
		$this->db->reset_query();
		
		// CONSULTA PRINCIPAL
		$this->db->select('esp.idespecialidad, esp.nombre AS especialidad');
		$this->db->select('SUM( CASE WHEN tp.idtipoproducto = 12 THEN total_detalle::NUMERIC ELSE 0::NUMERIC END ) AS solo_consultas');
		$this->db->select('SUM( CASE WHEN tp.idtipoproducto <> 12 THEN total_detalle::NUMERIC ELSE 0::NUMERIC END ) AS lo_demas');
		// $this->db->select('SUM( CASE WHEN tp.idtipoproducto = 12 THEN total_detalle::NUMERIC ELSE 0::NUMERIC END ) + SUM( CASE WHEN tp.idtipoproducto <> 12 THEN total_detalle::NUMERIC ELSE 0::NUMERIC END ) AS subtotal');
		$this->db->select('(' . $totalNC . ') AS nota_credito');
		$this->db->from('venta v');
		$this->db->join('especialidad esp','v.idespecialidad = esp.idespecialidad');
		$this->db->join('detalle det','v.idventa = det.idventa');
		$this->db->join('producto_master pm','det.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('v.estado', 1);
		$this->db->where('EXTRACT(MONTH FROM (v.fecha_venta)) = ',$allInputs['mes']['id']);
		$this->db->where('EXTRACT (YEAR FROM (v.fecha_venta)) = ', $allInputs['anioDesdeCbo']);
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by('esp.idespecialidad');
		$this->db->order_by('esp.nombre','ASC');

		return $this->db->get()->result_array(); 

	}
	/* PARA EL REPORTE DE PACIENTES POR ESPECIALIDAD*/
	public function m_cargar_clientes_por_especialidad($allInputs=FALSE)
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
		$this->db->select("cli.num_documento,cli.nombres,concat_ws(' ', cli.apellido_paterno, cli.apellido_materno) AS apellidos",FALSE);
		$this->db->select("DATE_PART('YEAR',AGE(cli.fecha_nacimiento)) AS edad,cli.celular, cli.telefono",FALSE);
		$this->db->select("e.nombre as especialidad, pm.descripcion as producto, v.fecha_venta",FALSE);
		$this->db->select("CASE WHEN v.estado = '0' THEN 'ANULADO' ELSE (CASE WHEN v.paciente_atendido_v = '1' THEN 'ATENDIDO' ELSE 'POR ATENDER' END) END AS estado",FALSE);
		$this->db->from('venta v');
		$this->db->join('cliente cli', 'v.idcliente = cli.idcliente');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad e', 'pm.idespecialidad = e.idespecialidad');
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($allInputs['desde'].' '.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto']) .' AND ' . $this->db->escape($allInputs['hasta'].' '.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto']));
		$this->db->where('e.idespecialidad', $allInputs['especialidad']['id']);
		$this->db->where('v.idsedeempresaadmin IN (' . $sedeempresa . ')');
		$this->db->order_by('v.fecha_venta', 'ASC');
		return $this->db->get()->result_array();

	}
	public function m_editar_venta_a_desaprobado($id){ // DESCUENTO 	
		$data = array(
			'estado' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idventa',$id);
		return $this->db->update('venta', $data);
	}
	public function m_cargar_detalle_venta($idventa, $solo_cita){
		$this->db->select('d.iddetalle, d.idprogcita, d.paciente_atendido_det'); 
		$this->db->from('detalle d');

		if($solo_cita){
			$this->db->select('pc.iddetalleprogmedico');
			$this->db->join('pa_prog_cita pc' , 'd.idprogcita = pc.idprogcita');
		}

		$this->db->where('d.idventa',$idventa);
		return $this->db->get()->result_array();
	}

	public function m_actualizar_detalle_cita_repro($iddetalle, $idprogcita){
		$data = array(
			'idprogcita' => $idprogcita, //nueva cita
		);
		$this->db->where('iddetalle',$iddetalle);
		return $this->db->update('detalle', $data);
	}
	public function m_validar_venta_con_nota_credito($ordenVenta)
	{
		$this->db->select('nc.idnotacredito'); 
		$this->db->from('venta v');
		$this->db->join('nota_credito nc' , 'v.idventa = nc.idventa');
		$this->db->where('v.orden_venta',$ordenVenta);
		$this->db->where('nc.estado_nc',1);
		$this->db->where('nc.si_habilita_laboratorio',2);
		$this->db->limit(1);
		return $this->db->get()->result_array();
	}

	public function m_validar_venta_con_nota_credito_atencion($datos)
	{
		$this->db->select('ncd.idnotacredito'); 
		$this->db->from('venta v');
		$this->db->join('nota_credito nc' , 'v.idventa = nc.idventa AND "nc"."estado_nc" = 1');
		$this->db->join('nota_credito_detalle ncd' , 'nc.idnotacredito = ncd.idnotacredito  AND ncd.estado_ncd = 1');
		$this->db->where('ncd.iddetalle', $datos['iddetalle']);
		$this->db->where('v.orden_venta', $datos['orden']);
		$this->db->limit(1);
		$result = $this->db->get()->row_array();
		return (!empty($result['idnotacredito']) ? TRUE : FALSE );
	}

	public function m_cargar_consulta_externa($allInputs){ 
		//var_dump($allInputs); exit();
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
		$anio_ant = $allInputs['anio'] - 1;
		if(!empty($allInputs['dic_ant'])){
			$this->db->select("esp.nombre AS especialidad");
			$this->db->select("SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 12 THEN 1 ELSE 0 END) AS dic_ant",FALSE); 
		}else{
			$this->db->select("esp.nombre AS especialidad");
			$this->db->select("SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 1 THEN 1 ELSE 0 END) AS enero,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 2 THEN 1 ELSE 0 END) AS febrero,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 3 THEN 1 ELSE 0 END) AS marzo,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 4 THEN 1 ELSE 0 END) AS abril,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 5 THEN 1 ELSE 0 END) AS mayo,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 6 THEN 1 ELSE 0 END) AS junio,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 7 THEN 1 ELSE 0 END) AS julio,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 8 THEN 1 ELSE 0 END) AS agosto,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 9 THEN 1 ELSE 0 END) AS septiembre,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 10 THEN 1 ELSE 0 END) AS octubre,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 11 THEN 1 ELSE 0 END) AS noviembre,
							SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 12 THEN 1 ELSE 0 END) AS diciembre,
							COUNT(de.paciente_atendido_det) AS total",FALSE);
		}
		 
		$this->db->from('detalle de');
		$this->db->join('venta v','v.idventa = de.idventa','left');
		$this->db->join('especialidad esp','de.idespecialidad = esp.idespecialidad','left'); 
		$this->db->join('producto_master pm','pm.idproductomaster= de.idproductomaster');
		$this->db->join('tipo_producto tp','tp.idtipoproducto = pm.idtipoproducto'); 
		$this->db->where('v.estado', 1); // activos
		$this->db->where('v.paciente_atendido_v', 1);
		if(!empty($allInputs['idTipoAtencion']) && $allInputs['idTipoAtencion'] == 'CM'){
			$this->db->where('tp.idtipoproducto', 12); 
		}else{
			$this->db->where('tp.idtipoproducto', 16);
		}		
		if(!empty($allInputs['dic_ant'])){
			$this->db->where("date_part('year',de.fecha_atencion_det) = ". ($allInputs['anio']-1));
		}else{
			$this->db->where("date_part('year',de.fecha_atencion_det) = ". $allInputs['anio']);
		}

		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("esp.nombre");
		$this->db->order_by("esp.nombre",'ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_consulta_externa_por_rango($allInputs){ 
		//var_dump($allInputs); exit();
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
	
		$this->db->select("esp.nombre AS especialidad");
		$anio = $allInputs['anioDesde'];
		$this->db->select("esp.nombre AS especialidad");
		if(!empty($allInputs['dic_ant'])){
			$anio = $allInputs['anioDesde']-1;	
			$this->db->select("SUM(CASE date_part('year',de.fecha_atencion_det) WHEN ".$anio." THEN 1 ELSE 0 END) AS anio".$anio,FALSE); 
		}else{
			while ( $anio<= $allInputs['anioHasta']) {
				$this->db->select("SUM(CASE date_part('year',de.fecha_atencion_det) WHEN ".$anio." THEN 1 ELSE 0 END) AS anio".$anio,FALSE);
				$anio++;
			}
			$this->db->select("COUNT(de.paciente_atendido_det) AS total",FALSE);
		}
	
		$this->db->from('venta v');
		$this->db->join('detalle de','v.idventa = de.idventa','left');
		$this->db->join('especialidad esp','de.idespecialidad = esp.idespecialidad','left'); 
		$this->db->join('producto_master pm','pm.idproductomaster= de.idproductomaster','left');
		$this->db->join('tipo_producto tp','tp.idtipoproducto = pm.idtipoproducto','left'); 
		$this->db->where('v.estado', 1); // activos
		$this->db->where('v.paciente_atendido_v', 1);
		$this->db->where('tp.idtipoproducto', 12); 
		if(!empty($allInputs['dic_ant'])){
			$this->db->where("date_part('year',de.fecha_atencion_det) = ". ($allInputs['anioDesde']-1));
		}else{
			$this->db->where("date_part('year',de.fecha_atencion_det) BETWEEN ". $this->db->escape($allInputs['anioDesde']) . " AND ". 
			$this->db->escape($allInputs['anioHasta']));
		}
					
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("esp.nombre"); 
		$this->db->order_by("esp.nombre",'ASC');
		

		return $this->db->get()->result_array();
	}

	public function m_cargar_consulta_externa_GRAPH($allInputs){ 
		//var_dump($allInputs); exit();
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
		
		$this->db->select("date_part('year',de.fecha_atencion_det) AS anio");
		$this->db->select("SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 1 THEN 1 ELSE 0 END) AS enero,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 2 THEN 1 ELSE 0 END) AS febrero,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 3 THEN 1 ELSE 0 END) AS marzo,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 4 THEN 1 ELSE 0 END) AS abril,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 5 THEN 1 ELSE 0 END) AS mayo,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 6 THEN 1 ELSE 0 END) AS junio,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 7 THEN 1 ELSE 0 END) AS julio,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 8 THEN 1 ELSE 0 END) AS agosto,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 9 THEN 1 ELSE 0 END) AS septiembre,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 10 THEN 1 ELSE 0 END) AS octubre,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 11 THEN 1 ELSE 0 END) AS noviembre,
						SUM(CASE date_part('month',de.fecha_atencion_det) WHEN 12 THEN 1 ELSE 0 END) AS diciembre,
						COUNT(de.paciente_atendido_det) AS total",FALSE);
			 
		$this->db->from('detalle de');
		$this->db->join('venta v','v.idventa = de.idventa','left');
		$this->db->join('especialidad esp','de.idespecialidad = esp.idespecialidad','left'); 
		$this->db->join('producto_master pm','pm.idproductomaster= de.idproductomaster');
		$this->db->join('tipo_producto tp','tp.idtipoproducto = pm.idtipoproducto'); 
		$this->db->where('v.estado', 1); // activos
		$this->db->where('v.paciente_atendido_v', 1);
		if(!empty($allInputs['idTipoRango']) && $allInputs['idTipoRango'] == 1){
			$this->db->where("date_part('year',de.fecha_atencion_det) = ".$allInputs['anio']);
		}else{
			$this->db->where("date_part('year',de.fecha_atencion_det) BETWEEN ". $this->db->escape($allInputs['anioDesde']) . " AND ". 
			$this->db->escape($allInputs['anioHasta']));
		}
		if(!empty($allInputs['idTipoAtencion']) && $allInputs['idTipoAtencion'] == 'CM'){
			$this->db->where('tp.idtipoproducto', 12); 
		}else{
			$this->db->where('tp.idtipoproducto', 16);
		}		
		if(!empty($allInputs['especialidad']['id']) && $allInputs['especialidad']['id'] != 'ALL'){
			$this->db->where('esp.idespecialidad', $allInputs['especialidad']['id']);
		}
		$this->db->where('v.idsedeempresaadmin IN ('.$sedeempresa . ')');
		$this->db->group_by("date_part('year',de.fecha_atencion_det)");
		return $this->db->get()->result_array();
	}
}