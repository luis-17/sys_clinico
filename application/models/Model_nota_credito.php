<?php
class Model_nota_credito extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_notas_credito($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('nc.idnotacredito, v.idventa, v.estado, orden_venta, v.total_a_pagar, 
			sub_total, total_igv, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, 
			sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, 
			cj.idcaja, cj.descripcion, cm.idcajamaster, numero_caja, serie_caja, descripcion_caja, ticket_nc, 
			e.idespecialidad, (e.nombre) AS especialidad, fecha_creacion_nc, nc.monto, (nc.monto::numeric) AS monto_format, estado_nc, u.idusers, username
		'); 
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		//$this->db->join('empresa_especialidad em','nc.idempresaespecialidad = em.idempresaespecialidad'); 
		$this->db->join('especialidad e','nc.idespecialidad = e.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente'); 
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','nc.idcaja = cj.idcaja'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->where('v.estado', 1); // venta 
		$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja no anulada 
		$this->db->where('nc.estado_nc', 1); // nota credito habilitada 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('DATE(nc.fecha_creacion_nc) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['fHasta'] . ' 23:00' )  );
		// if( !empty($paramDatos['cajamaster']) ){ 
		// 	$this->db->where('cm.idcajamaster', $paramDatos['cajamaster']);
		// }
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
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_notas_credito($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('venta v'); 
		$this->db->join('nota_credito nc','v.idventa = nc.idventa');
		$this->db->join('especialidad e','nc.idespecialidad = e.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento');
		$this->db->join('cliente c','v.idcliente = c.idcliente');
		$this->db->join('sede_empresa_admin sea','v.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('caja cj','v.idcaja = cj.idcaja AND cj.iduser = v.iduser');
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster');
		$this->db->where('v.estado', 1); // venta 
		$this->db->where('estado_cli', 1); // cliente 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado', 1); // caja abierta 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('DATE(nc.fecha_creacion_nc) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));

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
		$fila = $this->db->get()->row_array();
		return $fila['contador'];
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idventa' => $datos['idventa'],
			'idespecialidad' => $datos['idespecialidad'],
			'monto' => ($datos['monto_format'] * (-1)), 
			'descripcion' => empty($datos['descripcion']) ? NULL : $datos['descripcion'],
			'fecha_creacion_nc' => date('Y-m-d H:i:s'),
			'idcaja' => $datos['idcaja'],
			'ticket_nc' => $datos['ticket'],
			'tipo_salida' => $datos['tipo_salida'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin']
		);
		return $this->db->insert('nota_credito', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_nc' => 0 
		);
		$this->db->where('idnotacredito',$id);
		return $this->db->update('nota_credito', $data);
	}
	public function m_cargar_detalle_venta_nc($datos) 
	{
		$this->db->select('dt.iddetalle, (dt.total_detalle::numeric) AS total, dt.total_detalle, prm.descripcion, dt.paciente_atendido_det, ncd.idnotacreditodetalle'); 
		$this->db->from('venta v');  
		$this->db->join('detalle dt','dt.idventa = v.idventa');
		$this->db->join('producto_master prm','prm.idproductomaster = dt.idproductomaster');
		$this->db->join('nota_credito nc','v.idventa = nc.idventa AND nc.estado_nc = 1');
		$this->db->join('nota_credito_detalle ncd','dt.iddetalle = ncd.iddetalle AND nc.idnotacredito = ncd.idnotacredito AND ncd.estado_ncd = 1');
		$this->db->where('v.orden_venta', $datos['orden']);
		$this->db->where('v.idventa', $datos['id']);

		$sqlConNotaCredito = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('dt.iddetalle, (dt.total_detalle::numeric) AS total, dt.total_detalle, prm.descripcion, dt.paciente_atendido_det, ncd.idnotacreditodetalle'); 
		$this->db->from('venta v');  
		$this->db->join('detalle dt','dt.idventa = v.idventa');
		$this->db->join('producto_master prm','prm.idproductomaster = dt.idproductomaster');
		$this->db->join('nota_credito nc','v.idventa = nc.idventa AND nc.estado_nc = 1','left');
		$this->db->join('nota_credito_detalle ncd','dt.iddetalle = ncd.iddetalle AND nc.idnotacredito = ncd.idnotacredito AND ncd.estado_ncd = 1','left');
		$this->db->where('v.orden_venta', $datos['orden']);
		$this->db->where('v.idventa', $datos['id']);
		$this->db->where('ncd.iddetalle IS NULL');

		$sqlSinNotaCredito = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('*',FALSE);
		$this->db->from( '('. $sqlConNotaCredito . ' UNION ALL ' . $sqlSinNotaCredito . ') AS foo' );
		$this->db->order_by('iddetalle','ASC');
	
		return $this->db->get()->result_array();
	}
	public function m_registrar_detalle_nc($datos)
	{
		$data = array(
			'idnotacredito' => $datos['idnotacredito'],
			'iddetalle' => $datos['iddetalle'],
			'monto_detalle_nc' => $datos['monto_detalle_nc'], 
			'createdat' => date('Y-m-d H:i:s'),
			'updatedat' => date('Y-m-d H:i:s'),
			'estado_ncd' => 1,
		);
		return $this->db->insert('nota_credito_detalle', $data);
	}
	public function m_consultar_detalle_nc($datos) 
	{ 
		$this->db->select('ncd.iddetalle'); 
		$this->db->from('nota_credito nc');  
		$this->db->join('venta v','nc.idventa = v.idventa');
		$this->db->join('nota_credito_detalle ncd','nc.idnotacredito = ncd.idnotacredito');
		$this->db->where('ncd.iddetalle', $datos['iddetalle']);
		$this->db->where('v.orden_venta', $datos['orden']);
		$this->db->where('nc.estado_nc', 1);
		$this->db->limit(1);
		$result = $this->db->get()->row_array();
		return (!empty($result['iddetalle']) ? TRUE : FALSE );
	}
	public function m_consultar_detalle_nc_atendido($datos) 
	{ 
		$this->db->select('dt.paciente_atendido_det'); 
		$this->db->from('venta v');  
		$this->db->join('detalle dt','dt.idventa = v.idventa');
		$this->db->where('dt.iddetalle', $datos['iddetalle']);
		$this->db->where('v.orden_venta', $datos['orden']);
		$this->db->where('v.estado <>', 0);
		$this->db->limit(1);
		$result = $this->db->get()->row_array();
		return (($result['paciente_atendido_det'] == 1) ? TRUE : FALSE );
	}
}