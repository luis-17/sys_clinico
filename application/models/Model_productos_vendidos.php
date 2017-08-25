<?php
class Model_productos_vendidos extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_productos_venta($paramPaginate,$paramDatos=FALSE) 
	{
		/* VENTAS */ 
		$this->db->select('v.idventa, v.estado, orden_venta, de.paciente_atendido_det, de.total_detalle, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			mp.idmediopago, descripcion_med, c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, 
			u.idusers, username, u.email, pm.idproductomaster, (pm.descripcion) AS producto, esp.idespecialidad, (esp.nombre) AS especialidad, 
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
		$this->db->join('detalle de','v.idventa = de.idventa');
		$this->db->join('producto_master pm','de.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->where('v.estado <>', 2); // 
		// $this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // no poner caja anulada. 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('v.idespecialidad', $paramDatos['especialidad']['id']);
		} 
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
		} 
		// if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		// } 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$sqlVentas = $this->db->get_compiled_select();
		if( $paramPaginate['sortName'] ){
			$sqlVentas.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			$sqlVentas.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		} 
		$query = $this->db->query($sqlVentas);
		return $query->result_array(); 
	}
	public function m_count_productos_venta($paramPaginate,$paramDatos=FALSE)
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
		$this->db->join('detalle de','v.idventa = de.idventa');
		$this->db->join('producto_master pm','de.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->where('v.estado <>', 2); // entrantes 
		$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 

		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('v.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['convenio']) && $paramDatos['convenio']['id'] !== 'ALL' ){ 
			$this->db->where('v.idconvenio', $paramDatos['convenio']['id']); 
		} 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}
		$sqlVentas = $this->db->get_compiled_select(); 
		$query = $this->db->query($sqlVentas);
		$fData = $query->row_array(); 
		return $fData;
	}
}
?>