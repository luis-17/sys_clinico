<?php
class Model_solicitud_formula extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_solicitud_formula_por_id($paramPaginate,$paramDatos)
	{
		$this->db->select('fsf.idsolicitudformula, fsf.fecha_solicitud, fsf.idcliente, fsf.total_solicitud, fsf.referencia, fsf.estado_sol');
		$this->db->select("concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente, c.num_documento");
		$this->db->select("concat_ws(' ', me.med_nombres, me.med_apellido_paterno, me.med_apellido_materno) AS medico, me.idmedico");
		$this->db->select('(fds.precio_unitario)::NUMERIC AS precio_unitario_sf, fds.cantidad, fds.total_detalle_solicitud, fds.estado_detalle_sol
			, fds.idmedicamentoalmacen, fds.categoria_jj');
		$this->db->select('m.idmedicamento, m.denominacion AS medicamento, idformula_jj, fecha_asigna_idformula_jj, fds.iddetallesolicitud, fds.precio_unitario');
		$this->db->select('codigo_jj, fecha_asigna_codigo_jj');
		$this->db->from('far_solicitud_formula fsf');
		$this->db->join('far_detalle_solicitud fds','fsf.idsolicitudformula = fds.idsolicitudformula');
		$this->db->join('cliente c','fsf.idcliente = c.idcliente');
		$this->db->join('medicamento m','fds.idmedicamento = m.idmedicamento'); 
		$this->db->join('medico me','fsf.idmedico = me.idmedico','left');
		
		$this->db->where('fsf.idsolicitudformula', $paramDatos['idsolicitudformula']);
		$this->db->where('fsf.estado_sol', 1); 
		$this->db->where('fds.estado_detalle_sol <>', 0);
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		} 
		return $this->db->get()->result_array(); 
	}
	public function m_count_sum_detalle_solicitud_formula_por_id($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle_solicitud::NUMERIC) AS sumatotal'); 
		$this->db->from('far_solicitud_formula fsf');
		$this->db->join('far_detalle_solicitud fds','fsf.idsolicitudformula = fds.idsolicitudformula');
		$this->db->join('cliente c','fsf.idcliente = c.idcliente');
		$this->db->join('medicamento m','fds.idmedicamento = m.idmedicamento'); 
		// $this->db->join('far_movimiento fm','fsf.idsolicitudformula = fm.idsolicitudformula','left'); 
		
		$this->db->where('fsf.idsolicitudformula', $paramDatos['idsolicitudformula']);
		$this->db->where('fsf.estado_sol', 1); 
		$this->db->where('fds.estado_detalle_sol <>', 0); 
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	  // *******************************************************************************
	 // MANTENIMIENTO
	// *******************************************************************************
	public function m_registrar_solicitud($datos)
	{
		$data = array(
			'idcliente' => $datos['cliente']['id'],
			'iduser_creacion' => $this->sessionHospital['idusers'],
			'total_solicitud'=> $datos['total'],
			'fecha_solicitud' => date('Y-m-d H:i:s'),
			'idmedico' => empty($datos['idmedico']) ? NULL : $datos['idmedico'],
			'createdAt'=> date('Y-m-d H:i:s'), 
			'updatedAt'=> date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_solicitud_formula', $data);
	}
	public function m_registrar_detalle($datos)
	{
		$data = array( 
			'idsolicitudformula'=> $datos['idsolicitudformula'],
			'idmedicamento'=> $datos['id'],
			'cantidad'=> $datos['cantidad'],
			'precio_unitario'=> $datos['precio'],
			'precio_costo'=> $datos['precio_costo'],
			'total_detalle_solicitud'=> $datos['total'],
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s'),
			'idmedicamentoalmacen' => $datos['idmedicamentoalmacen'],
			'categoria_jj' => $datos['categoria'],
		);
		return $this->db->insert('far_detalle_solicitud', $data);
	}
	
	public function m_actualizar_estado_detalle_solicitud($datos)
	{
		$data = array(
			'estado_detalle_sol' => $datos['estado_detalle_sol'], // 0:anulado; 1: disponible para venta 2: No disponible (ya fue utilizado en una venta)
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('iddetallesolicitud',$datos['iddetallesolicitud']);
		return $this->db->update('far_detalle_solicitud', $data);
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
	public function m_verificar_estado_detalle_solicitud($datos){
		$this->db->select('fds.estado_detalle_sol, fds.idsolicitudformula, fsf.idcliente');
		$this->db->from('far_detalle_solicitud fds');
		$this->db->join('far_solicitud_formula fsf', 'fds.idsolicitudformula = fsf.idsolicitudformula');
		$this->db->where('iddetallesolicitud', $datos['iddetallesolicitud']);
		$this->db->limit(1);
		// $fData = $this->db->get()->row_array();
		// return $fData['estado_detalle_sol'];
		return $this->db->get()->row_array();
	}
	
}