<?php
class Model_salida_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	/* SALIDAS */
	public function m_cargar_salidas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento, fm.tipo_movimiento , fm.estado_movimiento , 
			fm.dir_movimiento AS dir_movimiento, fm.idalmacen, alm.nombre_alm, 
			fm.idsubalmacen AS idsubalmacen, salm.nombre_salm AS subAlmacen,
		 	fm.fecha_movimiento, fm.motivo_movimiento');
		$this->db->select('fm.iduser, empl.nombres, empl.apellido_paterno, empl.apellido_materno'); // usuario que registra la baja
		$this->db->select('fm.iduseraprobacion, aprob.nombres aprob_nombres, aprob.apellido_paterno aprob_apellido_paterno, aprob.apellido_materno aprob_apellido_materno'); // usuario que aprueba la baja
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('rh_empleado empl','fm.iduser = empl.iduser');
		$this->db->join('rh_empleado aprob','fm.iduseraprobacion = aprob.iduser','left');
		//$this->db->where('fm.estado_movimiento ', 3); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		//$this->db->where('fd.estado_detalle ', 1); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
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
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_count_salidas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento');  
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('users u','fm.iduser = u.idusers');
		$this->db->join('rh_empleado empl','u.idusers = empl.iduser');
		//$this->db->where('fm.estado_movimiento', 1); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
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
	/* SALIDAS ANULADAS */
	public function m_cargar_salidas_anuladas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento, fm.tipo_movimiento , fm.estado_movimiento , 
			fm.dir_movimiento AS dir_movimiento, fm.idalmacen, alm.nombre_alm, 
			fm.idsubalmacen AS idsubalmacen, salm.nombre_salm AS subAlmacen,
		 	fm.fecha_movimiento, fm.motivo_movimiento, u.idusers, u.username, empl.nombres, empl.apellido_paterno, empl.apellido_materno');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('users u','fm.iduser = u.idusers');
		$this->db->join('rh_empleado empl','u.idusers = empl.iduser');
		$this->db->where('fm.estado_movimiento ', 0); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		//$this->db->where('fd.estado_detalle ', 1); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
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
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_count_salidas_anuladas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento');  
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->join('users u','fm.iduser = u.idusers');
		$this->db->join('rh_empleado empl','u.idusers = empl.iduser');
		$this->db->where('fm.estado_movimiento', 0); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
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
	public function m_cargar_producto_salidas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento, fm.tipo_movimiento , fm.estado_movimiento , 
			fm.dir_movimiento AS dir_movimiento, fm.idalmacen, alm.nombre_alm, 
			fm.idsubalmacen AS idsubalmacen, salm.nombre_salm AS subAlmacen,
		 	fm.fecha_movimiento, fm.motivo_movimiento, fd.iddetallemovimiento, fd.cantidad ,
		 	fd.idmedicamentoalmacen,fd.estado_detalle , med.idmedicamento , med.denominacion');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fd','fm.idmovimiento = fd.idmovimiento');
		$this->db->join('medicamento med','fd.idmedicamento = med.idmedicamento');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		//$this->db->where('fm.estado_movimiento ', 3); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		//$this->db->where('fd.estado_detalle ', 1); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_count_producto_salidas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento');  
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fd','fm.idmovimiento = fd.idmovimiento');
		$this->db->join('medicamento med','fd.idmedicamento = med.idmedicamento');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->where('fm.estado_movimiento', 1); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
		$this->db->where('alm.idalmacen', $paramDatos['almacen']['id']);
		if($paramDatos['idsubalmacen'] != 0){
			$this->db->where('fm.idsubalmacen', $paramDatos['idsubalmacen']);
		}
	
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		/*------------------------------------------------------------------------------------------*/
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
	public function m_cargar_salida_por_id($idmovimiento){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, motivo_movimiento,
		 	fm.fecha_movimiento, fm.estado_movimiento, fm.fecha_aprobacion,
		 	alm.nombre_alm, salm.nombre_salm, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno');
		$this->db->select('fm.iduseraprobacion, aprob.nombres aprob_nombres, aprob.apellido_paterno aprob_apellido_paterno, aprob.apellido_materno aprob_apellido_materno'); // usuario que aprueba la baja
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->join('rh_empleado aprob','fm.iduseraprobacion = aprob.iduser','left');

		//$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');

		$this->db->where('fm.idmovimiento', $idmovimiento ); // ORDEN COMPRA
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_detalle_salidas($paramDatos){
		$this->db->select('fdm.iddetallemovimiento, fdm.idmovimiento, fdm.idmedicamento, fdm.cantidad, fdm.fecha_vencimiento, fdm.num_lote,
		 	 fdm.idmedicamentoalmacen, fdm.estado_detalle, med.denominacion ');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento med','fdm.idmedicamento = med.idmedicamento');
		//$this->db->where('estado_detalle', 1); // ESTADO
		$this->db->where('fdm.idmovimiento ', $paramDatos['idmovimiento']); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_subalmacen($datos, $paramPaginate){
		$this->db->select('fma.idmedicamentoalmacen, med.idmedicamento, med.denominacion, stock_actual_malm');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen']);
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
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen']);
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
	public function m_cargar_salida_en_espera($paramDatos,$paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento, fm.tipo_movimiento , 
			fm.idalmacen, alm.nombre_alm, fm.idsubalmacen, 
			salm.nombre_salm AS subAlmacen,	fm.fecha_movimiento , fm.motivo_movimiento');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->where('fm.estado_movimiento', 3); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS
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
	public function m_count_salida_en_espera($datos,$paramPaginate){
		$this->db->from('far_movimiento fm');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');
		$this->db->where('fm.estado_movimiento', 3); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento ', 5); // 5 BAJA U OTROS

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
	public function m_cargar_esta_salida_por_solicitud($datos)
	{
		$this->db->select('idmovimiento,tipo_movimiento,estado_movimiento');
		$this->db->from('far_movimiento');
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		$this->db->where('estado_movimiento', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	/* MANTENIMIENTO DE SALIDAS */
	/* LO PONEMOS EN ESPERA PARA SU APROBACION */
	public function m_registrar_salida($datos){
		$data = array( 
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 2,
			'tipo_movimiento' => 5,
			'idtipodocumento' => null,
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacen']['id'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'fecha_movimiento' => $datos['fecha_salida'],
			'estado_movimiento' => 3,
			'motivo_movimiento' => $datos['motivo_movimiento'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_aprobar_salida($datos){
		$data = array( 
			'estado_movimiento' => 1,
			'iduseraprobacion' => $this->sessionHospital['idusers'],
			'fecha_aprobacion' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmovimiento',$datos);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_registrar_detalle_salida($datos){
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'],
			'idmedicamento' => $datos['idmedicamento'],
			'idmedicamentoalmacen' => $datos['idmedicamentoalmacen'],
			'cantidad' => $datos['cantidad'],
			'fecha_vencimiento' => $datos['fecha_vencimiento'],
			'num_lote' => empty($datos['lote'])? NULL : $datos['lote'],
			'estado_detalle' => 1,
			'createdAt' => date('Y-m-d H:i:s') ,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_anular_detalle_salida($datos){
		$data = array( 
			'estado_detalle' => 0,
		);
		$this->db->where('iddetallemovimiento',$datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
	public function m_stock_general($datos){
		$this->db->select('sum(stock_actual_malm) as contador');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamento',$datos['idmedicamento']);
		$this->db->where('estado_fma', 1);
		$this->db->group_by('idmedicamento');
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_actualizar_medicamento_almacen_salida($datos,$opc){
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if($opc==1){ // SALIDA
			$this->db->set('stock_salidas', 'stock_salidas+'.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', '((stock_inicial+stock_entradas)-stock_salidas)-'.$datos['cantidad'], FALSE);
		}else{ 		// ANULACION
			$this->db->set('stock_salidas', 'stock_salidas-'.$datos['cantidad'], FALSE);
			$this->db->set('stock_actual_malm', '((stock_inicial+stock_entradas)-stock_salidas)+'.$datos['cantidad'], FALSE);
		}
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('far_medicamento_almacen');
	}

	public function m_actualizar_medicamento_stock_general($datos){
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db->set('stock_actual', $datos['stock_general'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('medicamento');
	}

	public function m_obtener_stock_producto($datos){
		$this->db->select('stock_actual_malm');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		$this->db->where('estado_fma', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	/* ====================================== */
}