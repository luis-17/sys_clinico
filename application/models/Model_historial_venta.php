<?php
class Model_historial_venta extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ventas_historial($paramPaginate,$paramDatos=FALSE) 
	{
		/* VENTAS */
		$this->db->select('v.idventa, v.estado, orden_venta, v.paciente_atendido_v, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, 
			m.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno, med_numero_documento'); 
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
		$this->db->join('tipo_cliente tp','v.idconvenio = tp.idtipocliente', 'left'); // convenio 
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
		}
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('v.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
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
		$sqlVentas = $this->db->get_compiled_select();
		/* NOTA CREDITO */
		$this->db->select("v.idventa, v.estado, orden_venta, v.paciente_atendido_v, 
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
		$this->db->join('tipo_cliente tp','v.idconvenio = tp.idtipocliente', 'left'); // convenio 
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_creacion_nc BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
		}
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('nc.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
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
	public function m_count_sum_ventas_historial($paramPaginate,$paramDatos=FALSE)
	{
		/* VENTAS */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN v.estado = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('tipo_cliente tp','v.idconvenio = tp.idtipocliente', 'left'); // convenio 
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 

		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
		}
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('v.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
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
		$sqlVentas = $this->db->get_compiled_select();

		$this->db->reset_query();

		/* NOTA CREDITO */
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN (v.estado = 1 AND nc.estado_nc = 1 ) THEN (nc.monto::numeric) ELSE 0 END) AS sumaTotal',FALSE);
		$this->db->from('venta v'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','nc.idcaja = cj.idcaja');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->join('users u','cj.iduser = u.idusers');
		$this->db->join('tipo_cliente tp','v.idconvenio = tp.idtipocliente', 'left'); // convenio 
		$this->db->join('medico m','v.idmedico = m.idmedico','left');
		$this->db->where('v.estado <>', 2); // entrantes  
		$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_nc', 1); // nota crédito 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_creacion_nc BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
		}
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('nc.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
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

	public function m_cargar_ventas_web_historial($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('ce.idventa, ce.estado, ce.orden_venta, ce.paciente_atendido_v, 
			ce.sub_total, ce.total_igv, ce.total_a_pagar, ce.fecha_venta, ce.ticket_venta, td.idtipodocumento, td.descripcion_td, 
			mp.idmediopago, mp.descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, ce.monto_comision'); 
		$this->db->from('ce_venta ce'); 
		$this->db->join('medio_pago mp','ce.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','ce.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','ce.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','ce.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('ce.fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
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
		if($paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_ventas_web_historial($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador, SUM(CASE WHEN ce.estado = 1 THEN (ce.total_a_pagar::numeric) ELSE 0 END) AS sumaTotal'); 
		$this->db->from('ce_venta ce'); 
		$this->db->join('medio_pago mp','ce.idmediopago = mp.idmediopago');
		$this->db->join('tipo_documento td','ce.idtipodocumento = td.idtipodocumento AND td.estado_td = 1');
		$this->db->join('cliente c','ce.idcliente = c.idcliente AND estado_cli = 1','left');
		$this->db->join('sede_empresa_admin sea','ce.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('ce.fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( !empty($paramDatos['sedeempresa']) ){ 
			$this->db->where('sea.idsedeempresaadmin', $paramDatos['sedeempresa']['id']);
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