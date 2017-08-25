<?php
class Model_nota_credito_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_notas_credito($paramPaginate,$paramDatos=FALSE) 
	{
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.orden_venta,  
			fm.ticket_venta, fm.fecha_movimiento, fm.tipo_nota_credito as tipo_nota_credito_nc,cm.idcajamaster,cm.numero_caja, 
			c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, 
			td.descripcion_td,fm.total_a_pagar, (fm.total_a_pagar::numeric) AS monto_format,
			u.idusers, username , fm1.ticket_venta as ticketventa , fm1.fecha_movimiento as fechaventa , fm1.orden_venta as ordenventa
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_movimiento fm1','fm.idventaorigen = fm1.idmovimiento AND fm.es_preparado = 2'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND c.estado_cli=1','left'); 
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','fm.idcaja = cj.idcaja'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->where('fm.estado_movimiento', 1); // venta 
		$this->db->where('fm.idtipodocumento', 7); // tipo-documento 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja no anulada 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('DATE(fm.fecha_movimiento) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));
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
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_movimiento fm1','fm.idventaorigen = fm1.idmovimiento AND fm1.es_preparado = 2'); 
		$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento'); 
		$this->db->join('cliente c','fm.idcliente = c.idcliente AND c.estado_cli=1','left'); 
		$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('caja cj','fm.idcaja = cj.idcaja'); 
		$this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		$this->db->where('fm.estado_movimiento', 1); // venta 
		$this->db->where('fm.idtipodocumento', 7); // tipo-documento 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('estado_se', 1); // sede 
		$this->db->where('estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where('cj.estado <>', 0); // caja no anulada 
		$this->db->where('cm.estado_caja', 1); // caja master 
		$this->db->where('DATE(fm.fecha_movimiento) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));

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
	public function m_registrar_nc($datos)
	{
		$data = array(
			'idcliente' => empty($datos['idcliente']) ? NULL : $datos['idcliente'],
			'tipo_movimiento' => 1,
			'dir_movimiento' => 2,
			'idempresacliente' =>empty($datos['idempresacliente']) ? NULL : $datos['idempresacliente'],
			'iduser' => $this->sessionHospital['idusers'],
			'idcaja' => $datos['idcaja'],
			'idtipodocumento' => 7,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin'],
			'idalmacen' => $this->sessionHospital['idalmacenfarmacia'], 
			'idsubalmacen' => $this->sessionHospital['idsubalmacenfarmacia'], 
			'ticket_venta' => $datos['ticket'],
			'fecha_movimiento' => date('Y-m-d H:i:s'),
			'total_a_pagar' => ($datos['monto'] * (-1)), 
			'estado_movimiento' => 1 ,
			'orden_venta' => null,
			'tipo_nota_credito' => $datos['tipo_nota_credito'],
			'idventaorigen' => $datos['idventaorigen'],
			'motivo_movimiento' => empty($datos['motivomovimiento']) ? NULL : $datos['motivomovimiento']

		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_registrar_detalle_nc($datos)
	{
		$data = array( 
			'idmovimiento'=> $datos['idmovimiento'],
			'idmedicamento'=> $datos['idmedicamento'],
			'idmedicamentoalmacen'=> $datos['idmedicamentoalmacen'],
			'cantidad'=> $datos['cantidad'],
			'precio_unitario'=> $datos['precio'],
			'total_detalle'=> $datos['monto_sf'],
			'iddetalle_origen'=> $datos['iddetallemovimiento'],
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s'),
			// 'estado_preparado' => empty($datos['estado_preparado'])? NULL : $datos['estado_preparado'],

		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_movimiento' => 0 
		);
		$this->db->where('idmovimiento',$id);
		return $this->db->update('far_movimiento', $data);
	}
}