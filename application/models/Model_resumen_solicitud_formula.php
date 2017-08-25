<?php
class Model_resumen_solicitud_formula extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_estado_solicitud($idsolicitudformula) 
	{		
		$this->db->select("fsf.idsolicitudformula, fm.estado_acuenta, fm.idmovimiento"); 
		$this->db->from('far_solicitud_formula fsf');		
		$this->db->join('far_movimiento fm', 'fm.idsolicitudformula = fsf.idsolicitudformula AND fm.idventaorigen IS NULL', 'left', FALSE);
		$this->db->where('fsf.idsolicitudformula', $idsolicitudformula);
		$this->db->where('fsf.estado_sol <>', 0);		
				
		return $this->db->get()->result_array();
	}
	public function m_cargar_resumen_solicitud($paramPaginate,$paramDatos=FALSE) 
	{	
		//$this->db->distinct();
		$this->db->select("fsf.idsolicitudformula, fsf.iduser_creacion, CONCAT_WS(' ', e.nombres, e.apellido_paterno, e.apellido_materno) AS encargado, fsf.idcliente, CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente, c.num_documento, fsf.total_solicitud, fsf.fecha_solicitud, fm.estado_acuenta, fm.idmovimiento, fdm.estado_preparado"); 
		$this->db->from('far_solicitud_formula fsf');
		$this->db->join('rh_empleado e', 'e.iduser = fsf.iduser_creacion');
		$this->db->join('cliente c', 'c.idcliente = fsf.idcliente');
		$this->db->join('far_movimiento fm', 'fm.idsolicitudformula = fsf.idsolicitudformula AND estado_movimiento = 1 AND fm.idventaorigen IS NULL', 'left', FALSE);
		$this->db->join('far_detalle_movimiento fdm', 'fdm.idmovimiento = fm.idmovimiento', 'left', FALSE);
		$this->db->where('fsf.estado_sol <>', 0);
		if($paramDatos['estadoPreparado']['id'] != 'all'){
			if($paramDatos['estadoPreparado']['id'] == 1){//1 = ENTREGADOS
				$this->db->where('fdm.estado_preparado ', 2);// 2 = ENTREGADO
			} elseif($paramDatos['estadoPreparado']['id'] == 2) {//CANCELADOS
				$this->db->where('fm.estado_acuenta', 2); //2 = CANCELADO
				$this->db->where('fdm.estado_preparado', 1); // 1 = PEDIDO
			} elseif ($paramDatos['estadoPreparado']['id'] == 3){// A CUENTA
				$this->db->where('fm.estado_acuenta', 1); // 1 = A CUENTA
				$this->db->where('fdm.estado_preparado', 1); // 1 PEDIDO
			} elseif ($paramDatos['estadoPreparado']['id'] == 4) {//PENDIENTE DE PAGO
				$this->db->where('fm.estado_acuenta IS NULL');
			}
		}

		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fsf.fecha_solicitud BETWEEN '. $desde . ' AND ' . $hasta);
		
		$this->db->group_by("fsf.idsolicitudformula, fsf.iduser_creacion, CONCAT_WS(' ', e.nombres, e.apellido_paterno, e.apellido_materno),
			fsf.idcliente, CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno), c.num_documento, fsf.total_solicitud, fsf.fecha_solicitud, fm.estado_acuenta, fm.idmovimiento, fdm.estado_preparado
			");
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
			$this->db->limit($paramPaginate['pageSize'], $paramPaginate['firstRow']);
		}
		return $this->db->get()->result_array();
	}
	public function m_count_resumen_solicitud($paramPaginate,$paramDatos=FALSE)	
	{
		//$this->db->distinct();
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_solicitud_formula fsf');
		$this->db->join('rh_empleado e', 'e.iduser = fsf.iduser_creacion');
		$this->db->join('cliente c', 'c.idcliente = fsf.idcliente');
		$this->db->join('far_movimiento fm', 'fm.idsolicitudformula = fsf.idsolicitudformula AND estado_movimiento = 1 AND fm.idventaorigen IS NULL', 'left', FALSE);
		$this->db->join('far_detalle_movimiento fdm', 'fdm.idmovimiento = fm.idmovimiento', 'left', FALSE);
		$this->db->where('fsf.estado_sol <>', 0);
		if($paramDatos['estadoPreparado']['id'] != 'all'){
			if($paramDatos['estadoPreparado']['id'] == 1){//1 = ENTREGADOS
				$this->db->where('fdm.estado_preparado ', 2);// 2 = ENTREGADO
			} elseif($paramDatos['estadoPreparado']['id'] == 2) {//CANCELADOS
				$this->db->where('fm.estado_acuenta', 2); //2 = CANCELADO
				$this->db->where('fdm.estado_preparado', 1); // 1 = PEDIDO
			} elseif ($paramDatos['estadoPreparado']['id'] == 3){// A CUENTA
				$this->db->where('fm.estado_acuenta', 1); // 1 = A CUENTA
				$this->db->where('fdm.estado_preparado', 1); // 1 PEDIDO
			} elseif ($paramDatos['estadoPreparado']['id'] == 4) {//PENDIENTE DE PAGO
				$this->db->where('fm.estado_acuenta IS NULL');
			}
		}

		$desde = $this->db->escape($paramDatos['desde'] . ' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']);
		$hasta = $this->db->escape($paramDatos['hasta']. ' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']);
		$this->db->where('fsf.fecha_solicitud BETWEEN '. $desde . ' AND ' . $hasta);
		$this->db->group_by("fsf.idsolicitudformula, fsf.iduser_creacion, CONCAT_WS(' ', e.nombres, e.apellido_paterno, e.apellido_materno),
			fsf.idcliente, CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno), c.num_documento, fsf.total_solicitud, fsf.fecha_solicitud, fm.estado_acuenta, fm.idmovimiento, fdm.estado_preparado
			");
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}
		$fData = $this->db->get()->num_rows();
		return $fData;
	}

	public function m_cargar_detalle_solicitud($paramPaginate,$paramDatos=FALSE) 
	{
		
		$this->db->select("fds.idsolicitudformula, fds.idmedicamento, m.denominacion, fds.cantidad, fds.precio_unitario, fds.total_detalle_solicitud, fds.estado_detalle_sol "); 
		$this->db->from('far_detalle_solicitud fds');
		$this->db->join('medicamento m', 'm.idmedicamento = fds.idmedicamento');
		$this->db->where('fds.idsolicitudformula', $paramDatos['idsolicitudformula']);
		
		/*if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		}*/ 
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
			$this->db->limit($paramPaginate['pageSize'], $paramPaginate['firstRow']);
		}
		return $this->db->get()->result_array();
	}

	public function m_count_detalle_solicitud($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_detalle_solicitud fds');
		$this->db->join('medicamento m', 'm.idmedicamento = fds.idmedicamento');
		$this->db->where('fds.idsolicitudformula', $paramDatos['idsolicitudformula']);
		
		/*if( $this->sessionHospital['key_group'] === 'key_caja' ) { 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); // solo la empresa_admin logueada 
		}*/
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

	public function m_entregar_preparado($idmovimiento) // para ser eliminado
	{
		$data = array(
			'estado_preparado' => 2,
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento', $idmovimiento);
		return $this->db->update('far_detalle_movimiento', $data);
	}

	public function m_anular_solicitud($id)
	{
		$data = array(
			'estado_sol' => 0,
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idsolicitudformula', $id);
		return $this->db->update('far_solicitud_formula', $data);
	}
}
?>