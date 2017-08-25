<?php
class Model_entrada_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	/* TRASLADOS */
	public function m_cargar_entradas_traslado($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento, 
			fm.idalmacen, alm.nombre_alm, 
			fm.idsubalmacen AS idsubalmacen, salm.nombre_salm AS subAlmacen,
			fm.idproveedor,
		 	fm.fecha_movimiento, fm.fecha_compra');
		$this->db->from('far_movimiento fm');

		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen');


		$this->db->where('fm.estado_movimiento', 1); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento', 2); // ENTRADA
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		// if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			
		// }elseif( $this->sessionHospital['key_group'] == 'key_admin_far' ){ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }else{ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		/*------------------------------------------------------------------------------------------*/
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		// if( $paramPaginate['sortName'] ){
		// 	$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		// }
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_subalmacen($datos, $paramPaginate){
		$this->db->select('fma.idmedicamentoalmacen, med.idmedicamento, med.denominacion, stock_actual_malm');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen1']);
		$this->db->where('med.estado_med', 1);

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
	public function m_count_productos_subalmacen($datos, $paramPaginate){

		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen1']);
		$this->db->where('med.estado_med', 1);

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	/* MANTENIMIENTO DE TRASLADOS - SALIDAS */
	public function m_registrar_salida($datos){

		$data = array( 
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 2,
			'tipo_movimiento' => 3,
			'idtipodocumento' => 1,
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacen']['id'],
			'idsubalmacen' => $datos['idsubalmacen1'],
			'fecha_movimiento' => $datos['fecha_traslado'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_registrar_detalle_salida($datos){
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'],
			'idmedicamento' => $datos['idmedicamento'],
			'idmedicamentoalmacen' => $datos['idmedicamentoalmacen'],
			'cantidad' => $datos['cantidad'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_actualizar_medicamento_almacen_salida($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		$this->db->set('stock_salidas', 'stock_salidas+'.$datos['cantidad'], FALSE);
		$this->db->set('stock_actual_malm', 'stock_actual_malm-'.$datos['cantidad'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	/* MANTENIMIENTO DE TRASLADOS - ENTRADAS */ 
	public function m_registrar_entrada_traslado($datos){
		$data = array( 
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 1,
			'tipo_movimiento' => 3,
			'idtipodocumento' => 1,
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacen']['id'],
			'idsubalmacen' => $datos['idsubalmacen2'],
			'idtrasladoorigen' => $datos['idtrasladoorigen'],
			'fecha_movimiento' => $datos['fecha_traslado'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_registrar_detalle_entrada($datos){
		// if( !empty($datos['importe']) ){
		// 	$datos['subtotal'] = $datos['importe'];
		// }
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'],
			'idmedicamento' => $datos['idmedicamento'],
			'idmedicamentoalmacen' => $datos['idmedicamentoalmacen'],
			'cantidad' => $datos['cantidad'],
			'precio_unitario' => empty($datos['precio'])? null : $datos['precio'],
			'total_detalle' => empty($datos['subtotal'])? null : $datos['subtotal'],
			'num_lote' => empty($datos['lote'])? null : $datos['lote'],
			'fecha_vencimiento' => empty($datos['fecha_vencimiento'])? null : $datos['fecha_vencimiento'],
			'descuento_asignado' => empty($datos['descuento_valor'])? null : $datos['descuento_valor'],
			'descuento_porcentaje' => empty($datos['descuento_porcentaje'])? 0 : $datos['descuento'],
			'cantidad_caja' => empty($datos['cantidad_caja'])? 0 : $datos['cantidad_caja'],
			'caja_unidad' => $datos['caja_unidad'],
			'precio_unitario_por_caja' => @$datos['precio_unitario_por_caja'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_actualizar_utilidades_compra($datos)
	{
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db->where('idalmacen', $datos['idalmacen']);
		$this->db->set('utilidad_valor', $datos['utilidad_valor'], FALSE);
		$this->db->set('utilidad_porcentaje', $datos['utilidad_porcentaje'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_verificar_producto_destino($datos){
		$this->db->select('idmedicamentoalmacen');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idsubalmacen',$datos['idsubalmacen']);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('estado_fma', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_medicamento_almacen_entrada($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if(!empty($datos['estemporal'])){	// COMPRA TEMPORAL
			if( $datos['estemporal'] ){
				$this->db->set('stock_temporal', 'stock_temporal+'.$datos['cantidad'], FALSE);
			}
		}else{
			$this->db->set('stock_entradas', 'stock_entradas+'.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_actual_malm+'.$datos['cantidad'], FALSE);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_actualizar_ultimo_precio_compra($datos){ 
		// $this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		$this->db->where('idalmacen', $datos['idalmacen']);
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		if( !empty($datos['precio']) ){
			$this->db->set('precio_ultima_compra', $datos['precio']);
		} 
		if( !empty($datos['precio_unitario_por_caja']) ){
			$this->db->set('precio_caja_ultima_compra', $datos['precio_unitario_por_caja'] );
		} 
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_registrar_medicamento_almacen_entrada($datos){
		$data = array(
			'idmedicamento' => $datos['idmedicamento'],
			'idalmacen' => $datos['idalmacen'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'precio_compra' => empty($datos['precio'])? 0 : $datos['precio'],
			'utilidad_porcentaje' => 0,
			'precio_venta' => 0,
			'stock_inicial' => 0,
			'stock_entradas' => (@$datos['estemporal'] == true) ? 0 :$datos['cantidad'],
			'stock_salidas' => 0,
			'stock_actual_malm' => (@$datos['estemporal'] == true) ? 0 :$datos['cantidad'],
			'stock_minimo' => 0,
			'stock_maximo' => 0,
			'stock_temporal' => (@$datos['estemporal'] != true) ? 0 :$datos['cantidad'],
			'costo_medio_malm' => 0,
			'costo_min_malm' => 0,
			'costo_max_malm' => 0,
			'precio_venta_kairos' => 0,
			'utilidad_valor' => 0,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'precio_ultima_compra' => empty($datos['precio'])? 0 : $datos['precio'],
			'precio_caja_ultima_compra' => empty($datos['precio_unitario_por_caja'])? 0 : $datos['precio_unitario_por_caja'],
		);
		return $this->db->insert('far_medicamento_almacen', $data);
	}
	public function m_registrar_medicamento_nuevo_almacen_central($datos){
		$data = array(
			'idmedicamento' => $datos['idmedicamento'],
			'idalmacen' => $datos['idalmacen'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'precio_compra' => empty($datos['precio'])? 0 : $datos['precio'],
			'utilidad_porcentaje' => 0,
			'precio_venta' => 0,
			'stock_inicial' => 0,
			'stock_entradas' => 0,
			'stock_salidas' => 0,
			'stock_actual_malm' => 0,
			'stock_minimo' => 0,
			'stock_maximo' => 0,
			'costo_medio_malm' => 0,
			'costo_min_malm' => 0,
			'costo_max_malm' => 0,
			'precio_venta_kairos' => 0,
			'utilidad_valor' => 0,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_medicamento_almacen', $data);
	}
	public function m_obtener_stock_producto($datos){
		$this->db->select('stock_actual_malm');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		$this->db->where('estado_fma', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	/* ==================== ANULAR TRASLADO ================== */
	public function m_cargar_detalle_movimiento($idmovimiento)
	{
		$this->db->select('idmedicamento, cantidad, idmedicamentoalmacen, iddetallemovimiento');
		$this->db->from('far_detalle_movimiento');
		$this->db->where('idmovimiento', $idmovimiento);
		return $this->db->get()->result_array();
	}
	public function m_anular_movimiento($idmovimiento){
		$this->db->where('idmovimiento', $idmovimiento);
		$this->db->set('estado_movimiento', 0);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		$this->db->set('fecha_anulacion', date('Y-m-d H:i:s'));
		return $this->db->update('far_movimiento');
	}
	public function m_anular_detalle($iddetallemovimiento){
		$this->db->where('iddetallemovimiento', $iddetallemovimiento);
		$this->db->set('estado_detalle', 0);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_detalle_movimiento');
	}
	/* ==================== ENTRADAS ================== */ 
	public function m_cargar_entradas($paramDatos, $paramPaginate=FALSE){ 
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.guia_remision, fm.ticket_venta as factura, fm.motivo_movimiento,
			p.idproveedor, p.razon_social, p.ruc, p.telefono, p.direccion_fiscal,
			fm.sub_total, fm.total_igv, fm.total_a_pagar,
			(fm.sub_total)::NUMERIC AS sub_total_sf,
			(fm.total_igv)::NUMERIC AS total_igv_sf,
			(fm.total_a_pagar)::NUMERIC AS total_a_pagar_sf,
		 	fm.fecha_movimiento, fm.fecha_compra, fm.fecha_vence_factura,fm.estado_movimiento, fm.orden_compra');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		//$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		// $this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // MOVIMIENTO
		// $this->db->where('fm.es_temporal',2); // SOLO REALES - (NO TEMPORALES)
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		// if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			
		// }elseif( $this->sessionHospital['key_group'] == 'key_admin_far' ){ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }else{ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		/*------------------------------------------------------------------------------------------*/
		if($paramPaginate){
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
		}
		
		return $this->db->get()->result_array();
	}
	public function m_count_sum_entradas($paramDatos, $paramPaginate=FALSE){
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN fm.estado_movimiento = 1 THEN (total_a_pagar::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		//$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		// $this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(1,0)); // MOVIMIENTO
		// $this->db->where('fm.es_temporal',2); // SOLO REALES - (NO TEMPORALES)
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));

		if($paramPaginate){
			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_entradas_anuladas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, 
			fm.guia_remision, fm.ticket_venta as factura, fm.motivo_movimiento,
			p.idproveedor, p.razon_social, p.ruc, p.telefono, p.direccion_fiscal,
			fm.sub_total, fm.total_igv, fm.total_a_pagar,
		 	fm.fecha_movimiento, fm.estado_movimiento, fm.fecha_compra');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->where_in('fm.estado_movimiento', array(0)); // MOVIMIENTO
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		
		$this->db->where('fm.fecha_anulacion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
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
	public function m_count_sum_entradas_anuladas($paramDatos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador,SUM(total_a_pagar) AS suma_total');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->where_in('fm.estado_movimiento', array(0)); // MOVIMIENTO
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		
		$this->db->where('fm.fecha_anulacion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
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
	public function m_cargar_entrada_por_id($idmovimiento){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra, fm.ticket_venta AS factura, fm.guia_remision,
			p.idproveedor, p.razon_social, p.ruc, p.direccion_fiscal, p.telefono,
			fm.sub_total, fm.total_igv, fm.total_a_pagar, motivo_movimiento,
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega, fm.fecha_compra, fm.fecha_vence_factura, fm.estado_movimiento, 
		 	fm.idtipomaterial, ftm.descripcion_tm, fm.estado_orden_compra, fm.forma_pago, fm.moneda, fm.letras,
		 	alm.nombre_alm, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');

		$this->db->where('fm.idmovimiento', $idmovimiento ); // ORDEN COMPRA
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_detalle_entrada($paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE);
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, orden_venta, ticket_venta, fdm.caja_unidad,
			fdm.cantidad, fdm.precio_unitario, fdm.descuento_asignado, fdm.total_detalle, fdm.fecha_vencimiento, fdm,num_lote,
			m.idmedicamento, m.denominacion, ff.acepta_caja_unidad,
			fl.idlaboratorio, fl.nombre_lab
		'); 
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN (fdm.precio_unitario_por_caja)::NUMERIC ELSE (fdm.precio_unitario)::NUMERIC END) AS precio_unitario", FALSE);
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN ( fdm.cantidad_caja ) ELSE (fdm.cantidad) END) AS cantidad", FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica');
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['idmovimiento']);
		$this->db->where('estado_detalle', 1);
		$this->db->order_by('m.denominacion', 'ASC');
		// if( $paramPaginate['sortName'] ){ 
		// 	$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		// }
		// if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
		// 	$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		// } 
		return $this->db->get()->result_array(); 
	}
	public function m_count_sum_detalle_entrada($paramDatos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle) AS sumatotal'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['idmovimiento']);
		$this->db->where('estado_detalle', 1);
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	public function m_cargar_producto_entrada($paramDatos, $paramPaginate)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.fecha_movimiento, fm.ticket_venta, fm.guia_remision,
			p.idproveedor, p.razon_social, p.ruc, 
			fdm.cantidad, fdm.precio_unitario, fdm.total_detalle, fdm.num_lote, fdm.fecha_vencimiento, 
			m.idmedicamento,
			fl.idlaboratorio, fl.nombre_lab 
		'); 
		$this->db->from('far_movimiento fm'); 
		//$this->db->join('medio_pago mp','fm.idmediopago = mp.idmediopago'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio AND estado_lab = 1','left'); 
		//$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left'); 
		//$this->db->join('sede_empresa_admin sea','fm.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		//$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		//$this->db->join('sede s','sea.idsede = s.idsede'); 
		// $this->db->join('caja cj','fm.idcaja = cj.idcaja AND cj.iduser = fm.iduser'); 
		// $this->db->join('caja_master cm','cj.idcajamaster = cm.idcajamaster'); 
		// $this->db->join('users u','cj.iduser = u.idusers'); 
		$this->db->where('fm.estado_movimiento', 1); //
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
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
	public function m_count_sum_producto_entrada($paramDatos, $paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador, SUM(total_detalle) AS suma_total'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio AND estado_lab = 1','left'); 
		//$this->db->join('tipo_documento td','fm.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->where('fm.estado_movimiento', 1); //
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idtipoentrada'] == 0){
			$this->db->where_in('fm.tipo_movimiento', array(2,4,6)); // (2,4,6)
		}else{
			$this->db->where_in('fm.tipo_movimiento', $paramDatos['idtipoentrada']);
		}
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
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
	public function m_obtener_subalmacen_principal($idalmacen){
		$this->db->select('idsubalmacen, nombre_salm, idtiposubalmacen');
		$this->db->from('far_subalmacen');
		$this->db->where('idalmacen', $idalmacen);
		$this->db->where('estado_salm', 1);
		$this->db->where('es_principal', 1); //--> CONSULTA SI ES PRINCIPAL
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_cantidad_ingresada_con_orden_compra($paramDatos)
	{
		if( $paramDatos['caja_unidad'] == 'UNIDAD'){
			$this->db->select('SUM(cantidad) AS cantidad_ingresada');
		}else{
			$this->db->select('SUM(cantidad_caja) AS cantidad_ingresada');
		}
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->where('fm.tipo_movimiento', 2);
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fm.orden_compra', $paramDatos['orden_compra']);
		$this->db->where('fdm.idmedicamento', $paramDatos['id']);
		$row = $this->db->get()->row_array();
		if( $row['cantidad_ingresada'] ){
			return $row['cantidad_ingresada'];
		}else{
			return 0;
		}
	}
	public function m_registrar_entrada($datos){
		$data = array( 
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 1,
			'tipo_movimiento' => $datos['idtipoentrada'],
			'idtipodocumento' => 1,
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacen']['id'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'idtrasladoorigen' => empty($datos['idtrasladoorigen'])? null : $datos['idtrasladoorigen'],
			'fecha_movimiento' => $datos['fecha_entrada'],
			'fecha_compra' => $datos['fecha_compra'],
			'idproveedor' => empty($datos['proveedor']['id'])? null : $datos['proveedor']['id'],
			'ticket_venta' => empty($datos['factura'])? null : $datos['factura'],
			'guia_remision' => empty($datos['guia_remision'])? null : $datos['guia_remision'],
			'sub_total' =>  empty($datos['subtotal'])? null : $datos['subtotal'],
			'total_igv' =>  empty($datos['igv'])? null : $datos['igv'],
			'total_a_pagar' =>  empty($datos['total'])? null : $datos['total'],
			'motivo_movimiento' =>  empty($datos['motivo_movimiento'])? null : $datos['motivo_movimiento'],
			'orden_compra' =>  ($datos['orden_compra']['id'] == 0 ? null : $datos['orden_compra']['descripcion']),
			'forma_pago' => @$datos['forma_pago'],
			'moneda' => @$datos['moneda'],
			'letras' => @$datos['letras'],
			'modo_igv' => @$datos['modo_igv'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'fecha_vence_factura' => $datos['fecha_vence_factura']
			// 'es_temporal' => ($datos['estemporal'] == true ? 1 : 2) 
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_verificar_estado($idmovimiento){
		$this->db->select('estado_movimiento');
		$this->db->from('far_movimiento');
		$this->db->where('idmovimiento', $idmovimiento);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_factura_proveedor($datos){
		//var_dump($datos['proveedor']['id'],$datos['factura']); exit();
		$this->db->select('estado_movimiento');
		$this->db->from('far_movimiento');
		$this->db->where('idproveedor', $datos['proveedor']['id']);
		$this->db->where('ticket_venta', $datos['factura']);
		$this->db->where('estado_movimiento', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_stock_medicamento($datos){ // no es apropiado utilizar este metodo, mejor usar model_medicamento->m_actualizar_stock_medicamento
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		if($datos['estemporal'] != true){
			$this->db->set('stock_actual', 'stock_actual+'.$datos['cantidad'], FALSE);
		}		
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('medicamento');
	}
	public function actualizar_movimiento($datos){
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		$data = array( 
			'sub_total' => $datos['sub_total'],
			'total_igv' => $datos['total_igv'],
			'total_a_pagar' => $datos['total_a_pagar'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_anular_entrada_medicamento_almacen($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		$this->db->set('stock_entradas', 'stock_entradas-'.$datos['cantidad'], FALSE);
		$this->db->set('stock_actual_malm', 'stock_actual_malm-'.$datos['cantidad'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_anular_salida_medicamento_almacen($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		$this->db->set('stock_salidas', 'stock_salidas-'.$datos['cantidad'], FALSE);
		$this->db->set('stock_actual_malm', 'stock_actual_malm+'.$datos['cantidad'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	// FORMULAS Y PREPARADOS
	public function m_recepcionar_formulas($datos){
		$data = array(
			'estado_preparado' => 3, // recibido
			'fecha_recepcion' => $datos['fecha_recepcion'],
			'guia_remision' => $datos['guia_remision'],
		);
		$this->db->where('iddetallemovimiento',$datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}	

	public function m_recepcionar_formulas_tecnica($datos){
		$data = array(
			'estado_preparado' => 4, // recibido por confirmar
			'fecha_recepcion' => $datos['fecha_recepcion'],
			'guia_remision' => $datos['guia_remision'],
		);
		$this->db->where('iddetallemovimiento',$datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}

	public function m_confirmar_recepcion_formulas($datos){
		$data = array(
			'estado_preparado' => 3, // recibido
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetallemovimiento',$datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
}