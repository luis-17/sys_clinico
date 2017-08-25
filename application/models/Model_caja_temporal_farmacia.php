<?php
class Model_caja_temporal_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_movimientos($paramDatos,$paramPaginate)
	{
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.fecha_movimiento ,fm.idtrasladoorigen, fm.es_temporal');
		$this->db->select("(COALESCE(rh.nombres,'') || ' ' || COALESCE(rh.apellido_paterno,'') || ' ' || COALESCE(rh.apellido_materno,'')) as usuario");
		$this->db->select('fm.total_a_pagar, fm.ticket_venta, fm.guia_remision, fm.orden_venta');
		$this->db->select('fp.razon_social, fa.idalmacen, fa.nombre_alm, fsa.idsubalmacen, fsa.nombre_salm');
		$this->db->from('far_movimiento fm');
		$this->db->join('rh_empleado rh','fm.iduser = rh.iduser');
		$this->db->join('far_almacen fa','fa.idalmacen = fm.idalmacen');
		$this->db->join('far_subalmacen fsa','fsa.idsubalmacen = fm.idsubalmacen');
		$this->db->join('far_proveedor fp','fp.idproveedor = fm.idproveedor','left');
		$this->db->where("(fm.es_temporal=1 OR fm.es_temporal=3) AND ((fm.tipo_movimiento=3 AND fm.idtrasladoorigen IS NOT NULL) OR fm.tipo_movimiento=1 OR fm.tipo_movimiento=2) AND fm.estado_movimiento=1"); 
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if($paramPaginate['search'] ){
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
	public function m_count_movimientos($paramDatos,$paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_movimiento fm');
		$this->db->join('rh_empleado rh','fm.iduser = rh.iduser');
		$this->db->join('far_almacen fa','fa.idalmacen = fm.idalmacen');
		$this->db->join('far_subalmacen fsa','fsa.idsubalmacen = fm.idsubalmacen');
		$this->db->join('far_proveedor fp','fp.idproveedor = fm.idproveedor','left');
		$this->db->where("(fm.es_temporal=1 OR fm.es_temporal=3) AND ((fm.tipo_movimiento=3 AND fm.idtrasladoorigen IS NOT null) OR fm.tipo_movimiento=1 OR fm.tipo_movimiento=2) AND fm.estado_movimiento=1"); 
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if($paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_productos_movimientos_temporales($paramDatos,$paramPaginate)
	{
		$this->db->select("fdm.iddetallemovimiento,fdm.idmedicamento,(COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) as medicamento,fdm.precio_unitario, (fdm.precio_unitario)::NUMERIC precio_unitario_sf,fdm.cantidad,fdm.total_detalle,(fdm.total_detalle)::NUMERIC total_detalle_sf, fma.stock_actual_malm, fma.idmedicamentoalmacen, fm.fecha_movimiento");
		$this->db->select('fl.idlaboratorio, fl.nombre_lab AS laboratorio');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma', 'fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('far_movimiento fm','fdm.idmovimiento = fm.idmovimiento');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->where('estado_detalle',1); 
		$this->db->where("(fm.es_temporal=1 OR fm.es_temporal=3) AND ((fm.tipo_movimiento=3 AND fm.idtrasladoorigen IS NOT NULL) OR fm.tipo_movimiento=1 OR fm.tipo_movimiento=2) AND fm.estado_movimiento=1"); 
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if($paramPaginate['search'] ){
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
	public function m_count_productos_movimientos_temporales($paramDatos,$paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma', 'fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('far_movimiento fm','fdm.idmovimiento = fm.idmovimiento');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left');
		$this->db->where('estado_detalle',1); 
		$this->db->where("(fm.es_temporal=1 OR fm.es_temporal=3) AND ((fm.tipo_movimiento=3 AND fm.idtrasladoorigen IS NOT NULL) OR fm.tipo_movimiento=1 OR fm.tipo_movimiento=2) AND fm.estado_movimiento=1"); 
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if($paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_detalle_movimientos($paramDatos)
	{
		$this->db->select("fdm.iddetallemovimiento,fdm.idmedicamento,(COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) as medicamento,fdm.precio_unitario,fdm.cantidad,fdm.total_detalle, fma.stock_actual_malm, fma.idmedicamentoalmacen");
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma', 'fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->where('estado_detalle',1); 
		$this->db->where('idmovimiento',$paramDatos['idmovimiento']);
		// $this->db->where('')
		return $this->db->get()->result_array();
	}
	public function m_count_detalle_movimientos($paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m','fdm.idmedicamento=m.idmedicamento');
		$this->db->where('estado_detalle',1); 
		$this->db->where('idmovimiento',$paramDatos['idmovimiento']); 
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_este_movimiento_temporal($paramDatos)
	{
		$this->db->from('far_movimiento');
		$this->db->where('idmovimiento',$paramDatos['idmovimiento']); 
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_movimiento_temporal_almacen($paramDatos)
	{
		$this->db->select('fm.idmovimiento,fa.nombre_alm,fsa.nombre_salm');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen fa','fa.idalmacen = fm.idalmacen');
		$this->db->join('far_subalmacen fsa','fsa.idsubalmacen = fm.idsubalmacen');
		$this->db->where('idmovimiento',$paramDatos['idtrasladoorigen']); 
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_actualizar_movimiento_temporal($paramDatos)
	{
		$this->db->where('idmovimiento', $paramDatos['idmovimiento']);
		$this->db->set('es_temporal', 3, FALSE);	// REGULARIZADO 
		//$this->db->set('fecha_movimiento', date('Y-m-d H:i:s'));
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_movimiento');
	}
	public function m_cargar_detalle_movimiento_almacen($paramDatos)
	{
		$this->db->select("fdm.iddetallemovimiento,fdm.idmedicamento,(m.denominacion || ' ' || m.descripcion) as medicamento,fdm.precio_unitario,fdm.cantidad,fdm.total_detalle,fm.idalmacen,fm.idsubalmacen,fm.tipo_movimiento,fm.dir_movimiento");
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fm','fdm.idmovimiento=fm.idmovimiento');
		$this->db->join('medicamento m','fdm.idmedicamento=m.idmedicamento');
		$this->db->where('fdm.estado_detalle',1); 
		$this->db->where('fdm.idmovimiento',$paramDatos['idmovimiento']); 
		return $this->db->get()->result_array();
	}
	public function m_actualizar_stock_medicamento_almacen($datos)
	{
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db->where('idalmacen', $datos['idalmacen']);
		$this->db->where('idsubalmacen', $datos['idsubalmacen']);
		if($datos['dir_movimiento'] == 1){	// ENTRADA
			$this->db->set('stock_entradas', 'stock_entradas+'.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_actual_malm+'.$datos['cantidad'], FALSE);
			$this->db->set('stock_temporal', 'stock_temporal-'.$datos['cantidad'], FALSE);
		}else{		// SALIDA
			$this->db->set('stock_salidas', 'stock_salidas+'.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_actual_malm-'.$datos['cantidad'], FALSE);
			$this->db->set('stock_temporal', 'stock_temporal+'.$datos['cantidad'], FALSE);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_stock_general($datos)
	{
		$this->db->select('sum(stock_actual_malm) as contador');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('estado_fma', 1);
		$this->db->group_by('idmedicamento');
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_actualizar_stock_medicamento_general($datos)
	{
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db->set('stock_actual', $datos['stock_general'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('medicamento');
	}
	public function m_stock_medicamento($datos)
	{
		$this->db->select('stock_actual_malm');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('idalmacen',$datos['idalmacen']);
		$this->db->where('idsubalmacen',$datos['idsubalmacen']);
		$this->db->where('estado_fma', 1);
		return $this->db->get()->row_array();
	}

} 