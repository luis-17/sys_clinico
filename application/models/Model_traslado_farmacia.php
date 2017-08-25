<?php
class Model_traslado_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	// alm.idalmacen, alm.nombre_alm, alm2.idalmacen AS id_almacen_destino, alm2.nombre_alm AS almacen_destino, 
	public function m_cargar_traslados($paramDatos, $paramPaginate=FALSE){
		$this->db->select('fm.idmovimiento AS idmovimiento1, fm2.idmovimiento AS idmovimiento2, 
			fm.dir_movimiento AS dir_movimiento1, fm2.dir_movimiento AS dir_movimiento2, 
			alm.idalmacen, alm.nombre_alm, alm2.idalmacen AS idalmacen2, alm2.nombre_alm AS nombre_alm2,
			fm.idsubalmacen AS idsubalmacen1, salm.nombre_salm AS subAlmacenOrigen,
			fm2.idsubalmacen AS idsubalmacen2, salm2.nombre_salm AS subAlmacenDestino,
		 	fm.fecha_movimiento, fm.motivo_movimiento, fm.estado_movimiento,
		 	empad.idempresaadmin, empad.nombre_legal, empad.ruc, empad.domicilio_fiscal, empad.razon_social ');
		$this->db->select("(rhe.apellido_paterno || ' ' || rhe.apellido_materno  || ', ' || rhe.nombres) AS usuario", FALSE);
		$this->db->select("COUNT (gr.idguiaremision) AS guias", FALSE);
		 	//fd.cantidad, med.idmedicamento, med.denominacion
		$this->db->from('far_movimiento fm');
		// $this->db->join('far_detalle_movimiento fd','fm.idmovimiento = fd.idmovimiento');
		// $this->db->join('medicamento med','fd.idmedicamento = med.idmedicamento');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('guia_remision gr','fm.idmovimiento = gr.idmovimiento AND gr.estado_gr <> 0','left');

		$this->db->join('far_movimiento fm2','fm.idmovimiento = fm2.idtrasladoorigen');
		$this->db->join('far_subalmacen salm2','fm2.idsubalmacen = salm2.idsubalmacen AND fm2.dir_movimiento = 1');
		$this->db->join('rh_empleado rhe', 'fm.iduser = rhe.iduser');
		$this->db->join('far_almacen alm2','fm2.idalmacen = alm2.idalmacen');
		$this->db->join('sede_empresa_admin sempad','sempad.idsedeempresaadmin = alm2.idsedeempresaadmin');
		$this->db->join('empresa_admin empad','sempad.idempresaadmin = empad.idempresaadmin');
		//$this->db->where('fm.estado_movimiento', 1); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento', 3); // TRASLADO
		$this->db->where('fm.es_temporal', 2); // SOLO REALES - (NO TEMPORALES) group_

		if($paramDatos['almacen']['id'] != 0){ 
			$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
			// $this->db->where('fm.idsubalmacen', $paramDatos['almacen']['id']);
		}
		if($paramDatos['almacenDestino']['id'] != 0){ 
			$this->db->where('fm2.idalmacen', $paramDatos['almacenDestino']['id']);
			// $this->db->where('fm2.idsubalmacen', $paramDatos['idsubalmacen2']);
		}
		
		if($paramDatos['idsubalmacen1'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen1']);
		}
		if($paramDatos['idsubalmacen2'] != 0){
			$this->db->where('fm2.idsubalmacen', $paramDatos['idsubalmacen2']);
		}
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		// if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			
		// }elseif( $this->sessionHospital['key_group'] == 'key_admin_far' ){ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }else{ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }

		$this->db->group_by('fm.idmovimiento, fm2.idmovimiento, fm.dir_movimiento , fm2.dir_movimiento, 
			alm.idalmacen, alm.nombre_alm, alm2.idalmacen, alm2.nombre_alm,fm.idsubalmacen , 
			salm.nombre_salm ,fm2.idsubalmacen , salm2.nombre_salm,fm.fecha_movimiento, 
			fm.motivo_movimiento, fm.estado_movimiento,empad.idempresaadmin, empad.nombre_legal, 
			empad.ruc, empad.domicilio_fiscal, empad.razon_social, rhe.apellido_paterno, 
			rhe.apellido_materno, rhe.nombres');
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
		}else{
			$this->db->order_by('fm.fecha_movimiento', 'ASC');
		}
		
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_count_traslados($paramDatos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('far_movimiento fm2','fm.idmovimiento = fm2.idtrasladoorigen');
		$this->db->join('far_subalmacen salm2','fm2.idsubalmacen = salm2.idsubalmacen AND fm2.dir_movimiento = 1');
		$this->db->join('rh_empleado rhe', 'fm.iduser = rhe.iduser');
		$this->db->join('far_almacen alm2','fm2.idalmacen = alm2.idalmacen');
		$this->db->where('fm.tipo_movimiento', 3); // TRASLADO
		$this->db->where('fm.es_temporal', 2); // SOLO REALES - (NO TEMPORALES)
		if($paramDatos['almacen']['id'] != 0){ 
			$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
			// $this->db->where('fm.idsubalmacen', $paramDatos['almacen']['id']);
		}
		if($paramDatos['almacenDestino']['id'] != 0){ 
			$this->db->where('fm2.idalmacen', $paramDatos['almacenDestino']['id']);
			// $this->db->where('fm2.idsubalmacen', $paramDatos['idsubalmacen2']);
		}
		if($paramDatos['idsubalmacen1'] != 0){ 
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen1']);
		}
		if($paramDatos['idsubalmacen2'] != 0){
			$this->db->where('fm2.idsubalmacen', $paramDatos['idsubalmacen2']);
		}
		
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		return $this->db->get()->row_array();
	}
	public function m_cargar_productos_subalmacen($datos, $paramPaginate){
		$this->db->select('fma.idmedicamentoalmacen, med.idmedicamento, med.denominacion, fma.stock_actual_malm, (fma2.precio_venta::numeric) AS precio');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->join('far_medicamento_almacen fma2', 'fma.idmedicamento = fma2.idmedicamento AND fma2.idsubalmacen = '. $datos['idsubalmacen2'], 'left' );
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacenorigen']);
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
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->join('far_medicamento_almacen fma2', 'fma.idmedicamento = fma2.idmedicamento AND fma2.idsubalmacen = '. $datos['idsubalmacen2'], 'left' );
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacenorigen']);
		$this->db->where('med.estado_med', 1);

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		return $this->db->get()->row_array();
		// $totalRows = $this->db->get()->num_rows();
		// return $totalRows;
	}
	// public function m_cargar_traslado_por_id($idmovimiento){ // idmovimiento destino
	// 	$this->db->select('fm.idmovimiento AS idmovimiento2, fm_or.idmovimiento AS idmovimiento1, fm.tipo_movimiento, fm.fecha_movimiento, fm.motivo_movimiento,
	// 		fm.idalmacen, alm.nombre_alm, 
	// 		fm_or.idsubalmacen AS idsubalmacen1, salm_or.nombre_salm AS subAlmacenOrigen,
	// 		fm.idsubalmacen AS idsubalmacen2, salm.nombre_salm AS subAlmacenDestino,
	// 	 	rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno');
	// 	$this->db->from('far_movimiento fm'); // movimiento destino
	// 	$this->db->join('far_movimiento fm_or','fm.idtrasladoorigen = fm_or.idmovimiento');
	// 	$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
	// 	$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 1');
	// 	$this->db->join('far_subalmacen salm_or','fm_or.idsubalmacen = salm_or.idsubalmacen AND fm_or.dir_movimiento = 2');
	// 	$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');

	// 	$this->db->where('fm.tipo_movimiento', 3); // TRASLADO
	// 	$this->db->where('fm.idmovimiento', $idmovimiento); 
	// 	$this->db->limit(1);
		
	// 	return $this->db->get()->row_array();
	// }
	public function m_cargar_detalle_traslado($datos, $paramPaginate=FALSE){ 
		$this->db->select('fma.precio_ultima_compra::NUMERIC AS precio_ultima_compra_str', FALSE);
		$this->db->select('fm.idmovimiento, fdm.iddetallemovimiento, m.idmedicamento, m.denominacion, lab.idlaboratorio, lab.nombre_lab, fdm.cantidad');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fm', 'fdm.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma', 'fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('medicamento m', 'fdm.idmedicamento = m.idmedicamento');
		$this->db->join('far_laboratorio lab', 'm.idlaboratorio = lab.idlaboratorio AND lab.estado_lab = 1', 'left');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento1']);
		$this->db->where('estado_detalle', 1);
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
			}else{
				$this->db->order_by('lab.idlaboratorio', 'ASC');
				$this->db->order_by('m.denominacion', 'ASC'); 
			}
			if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}else{
			$this->db->order_by('iddetallemovimiento', 'ASC');
		}
		
		
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_count_detalle_traslado($datos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m', 'fdm.idmedicamento = m.idmedicamento');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento1']);
		$this->db->where('estado_detalle', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		return $this->db->get()->row_array();
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
			'idsubalmacen' => $datos['idsubalmacenorigen'],
			'fecha_movimiento' => $datos['fecha_traslado'],
			'es_temporal' => ($datos['estemporal'] == true)? 1 : 2,
			'motivo_movimiento' => empty($datos['motivo_movimiento']) ? NULL : $datos['motivo_movimiento'],
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
			'createdAt' => date('Y-m-d H:i:s') ,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_actualizar_medicamento_almacen_salida($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if($datos['estemporal']){
			$this->db->set('stock_temporal', 'stock_temporal - '.$datos['cantidad'], FALSE);
			
		}else{
			$this->db->set('stock_salidas', 'stock_salidas + '.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_inicial + stock_entradas - stock_salidas - '.$datos['cantidad'], FALSE);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	/* MANTENIMIENTO DE TRASLADOS - ENTRADAS */
	public function m_registrar_entrada($datos){
		$data = array( 
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 1,
			'tipo_movimiento' => 3,
			'idtipodocumento' => 1,
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacenDestino']['id'],
			'idsubalmacen' => $datos['idsubalmacen2'],
			'idtrasladoorigen' => $datos['idtrasladoorigen'],
			'fecha_movimiento' => $datos['fecha_traslado'],
			'es_temporal' => ($datos['estemporal'] == true)? 1 : 2 ,
			'motivo_movimiento' => empty($datos['motivo_movimiento'])? NULL : $datos['motivo_movimiento'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_registrar_detalle_entrada($datos){
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
	public function m_verificar_producto_destino($datos){
		$this->db->select('idmedicamentoalmacen');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idalmacen',$datos['idalmacen']);
		$this->db->where('idsubalmacen',$datos['idsubalmacen']);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('estado_fma', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_producto_destino_temporal($datos){
		$this->db->select('idmedicamentoalmacen');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idalmacen',$datos['idalmacen']);
		$this->db->where('idsubalmacen',$datos['idsubalmacen']);
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('estado_fma', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_medicamento_almacen_entrada($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if(@$datos['estemporal']){
			$this->db->set('stock_temporal', 'stock_temporal + '.$datos['cantidad'], FALSE); 
		}else{
			$this->db->set('stock_entradas', 'stock_entradas + '.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_inicial + stock_entradas - stock_salidas + '.$datos['cantidad'], FALSE);
		}
		if(isset($datos['precio'])){
			$this->db->set('precio_venta', $datos['precio']);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_registrar_medicamento_almacen_entrada($datos){
		$data = array(
			'idmedicamento' => $datos['idmedicamento'],
			'idalmacen' => $datos['idalmacen'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'precio_compra' => 0,
			'utilidad_porcentaje' => 0,
			'precio_venta' => empty($datos['precio'])? 0 : $datos['precio'],
			'stock_inicial' => 0,
			'stock_entradas' => ($datos['estemporal'] == true) ? 0 : $datos['cantidad'],
			'stock_salidas' => 0,
			'stock_actual_malm' =>($datos['estemporal'] == true) ? 0 : $datos['cantidad'],
			'stock_temporal' => ($datos['estemporal'] == true) ? $datos['cantidad'] : 0,
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
		$this->db->select('idmedicamento, cantidad, idmedicamentoalmacen');
		$this->db->from('far_detalle_movimiento');
		$this->db->where('idmovimiento', $idmovimiento);
		return $this->db->get()->result_array();
	}
	public function m_anular_movimiento($idmovimiento){
		$this->db->where('idmovimiento', $idmovimiento);
		$this->db->set('estado_movimiento', 0);
		$this->db->set('fecha_anulacion', date('Y-m-d H:i:s'));
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_movimiento');
	}
	public function m_anular_salida_medicamento_almacen($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if($datos['estemporal']){
			$this->db->set('stock_temporal', 'stock_temporal + '.$datos['cantidad'], FALSE);
			
		}else{
			$this->db->set('stock_salidas', 'stock_salidas - '.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_inicial + stock_entradas - stock_salidas + '.$datos['cantidad'], FALSE);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_anular_entrada_medicamento_almacen($datos){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if($datos['estemporal']){
			$this->db->set('stock_temporal', 'stock_temporal - '.$datos['cantidad'], FALSE);
			
		}else{
			$this->db->set('stock_entradas', 'stock_entradas - '.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_inicial + stock_entradas - stock_salidas - '.$datos['cantidad'], FALSE);
		}

		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}

	public function m_actualizar_movimiento_en_guia_remision($datos){
		$this->db->where('iddetallemovimiento', $datos['iddetallemovimiento']);		
		$this->db->set('en_guia_remision', $datos['en_guia_remision']);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_detalle_movimiento');
	}
	
}