<?php
class Model_almacen extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_registrar_ingreso($datos)
	{
		$data = array( 
			'idempresaadmin' => $datos['idempresa'],
			'fecha' => date('Y-m-d H:i:s'), // se rellena del detalle 
			'idempleado' => 0,
			'idproveedor' => $datos['idproveedor'],
			'doc_referencia' => empty($datos['numeroDocumento']) ? null : $datos['numeroDocumento'] ,
			'idtipodocumento' => $datos['idtipodocumento'],
			'idmotivomovimiento' => $datos['idmotivomovimiento'],
			'costo_total' => $datos['total'],
			'iduser' => 1,
			'observaciones' => empty($datos['observaciones']) ? null : $datos['observaciones'] ,
			'estado_k' => 1
			// 'tiene_descuento' => $datos['tiene_descuento']
		);
		return $this->db->insert('kardex', $data);
	}
	public function m_registrar_detalle($datos,$datosParent) 
	{
		$data = array( 
			'idkardex' => $datos['idkardex'],
			'idreactivoinsumo' => $datos['id'],
			'precio' => $datos['precio'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_k' => 1,
			'cantidad' => $datos['cantidad'],
			'importe' => $datos['importe'],
			'fecha_vencimiento' => $datos['fechavencimiento'],
			'gestion_vencimiento' => 1 ,
			'numero_lote' => empty($datos['numerolote']) ? null : $datos['numerolote'] ,

		);
		return $this->db->insert('detalle_kardex', $data);
	}
	public function m_cargar_ingresoAlmacen($paramPaginate){
		$this->db->select('k.idkardex,k.fecha,e.descripcion,p.razon_social as proveedor,k.doc_referencia,td.descripcion_td,k.costo_total,mm.descripcion_mm,k.estado_k');
		$this->db->from('kardex k');
		$this->db->join('empresa e','e.idempresa = k.idempresaadmin','left');
		$this->db->join('proveedor p','p.idproveedor = k.idproveedor','left');
		$this->db->join('tipo_documento td','td.idtipodocumento = k.idtipodocumento','left');
		$this->db->join('motivo_movimiento mm','mm.idmotivomovimiento = k.idmotivomovimiento');
		$this->db->where('k.estado_k <>', 0);
		$this->db->where('mm.tipo_movimiento', 1);
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
	public function m_count_ingresoAlmacen($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('kardex k');
		$this->db->join('empresa e','e.idempresa = k.idempresaadmin','left');
		$this->db->join('proveedor p','p.idproveedor = k.idproveedor','left');
		$this->db->join('tipo_documento td','td.idtipodocumento = k.idtipodocumento','left');
		$this->db->join('motivo_movimiento mm','mm.idmotivomovimiento = k.idmotivomovimiento');
		$this->db->where('k.estado_k <>', 0);
		$this->db->where('mm.tipo_movimiento', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_detalleingresoAlmacen($paramPaginate,$paramDatos){
		$this->db->select('d.iddetallekardex,d.idreactivoinsumo,d.idkardex,ri.descripcion,d.precio,d.cantidad,d.importe,k.costo_total,d.estado_k,d.fecha_vencimiento,d.numero_lote');
		$this->db->from('detalle_kardex d');
		$this->db->join('reactivo_insumo ri','ri.idreactivoinsumo = d.idreactivoinsumo');
		$this->db->join('kardex k','k.idkardex = d.idkardex');
		$this->db->where('d.idkardex',$paramDatos);
		$this->db->where('d.estado_k <>', 0);
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
	public function m_count_detalleingresoAlmacen($paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle_kardex');
		$this->db->where('estado_k <>', 0);
		$this->db->where('idkardex',$paramDatos);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_registrar_salida($datos)
	{
		$data = array( 
			'fecha' => date('Y-m-d H:i:s'), // se rellena del detalle 
			'idempleado' => $datos['idempleado'],
			'doc_referencia' => empty($datos['numeroDocumento']) ? null : $datos['numeroDocumento'] ,
			'idmotivomovimiento' => $datos['idmotivomovimiento'],
			'iduser' => 1,
			'observaciones' => empty($datos['observaciones']) ? null : $datos['observaciones'] ,
			'estado_k' => 1
		);
		return $this->db->insert('kardex', $data);
	}
	public function m_registrar_detalle_salida($datos,$datosParent) 
	{
		$data = array( 
			'idkardex' => $datos['idkardex'],
			'idreactivoinsumo' => $datos['id'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_k' => 1,
			'cantidad' => $datos['cantidad'],
		);
		return $this->db->insert('detalle_kardex', $data);
	}

	public function m_cargar_salidaAlmacen($paramPaginate){
		$this->db->select("k.idkardex,k.fecha,e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno as empleado,k.doc_referencia,mm.descripcion_mm,k.estado_k");
		$this->db->from('kardex k');
		$this->db->join('rh_empleado e','e.idempleado = k.idempleado','left');
		$this->db->join('motivo_movimiento mm','mm.idmotivomovimiento = k.idmotivomovimiento');
		$this->db->where('estado_k <>', 0);
		$this->db->where('mm.tipo_movimiento',2);
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
	public function m_count_salidaAlmacen($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('kardex k');
		$this->db->join('rh_empleado e','e.idempleado = k.idempleado','left');
		$this->db->join('motivo_movimiento mm','mm.idmotivomovimiento = k.idmotivomovimiento');
		$this->db->where('estado_k <>', 0);
		$this->db->where('mm.tipo_movimiento',2);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_detallesalidaAlmacen($paramPaginate,$paramDatos){
		$this->db->select('d.iddetallekardex,d.idkardex,d.idreactivoinsumo,ri.descripcion,d.cantidad,u.descripcion as unidad,d.estado_k');
		$this->db->from('detalle_kardex d');
		$this->db->join('reactivo_insumo ri','ri.idreactivoinsumo = d.idreactivoinsumo');
		$this->db->join('unidad_laboratorio u','u.idunidadlaboratorio = ri.idunidadlaboratorio');
		$this->db->where('idkardex',$paramDatos);
		$this->db->where('estado_k <>', 0);
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

	public function m_count_detallesalidaAlmacen($paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle_kardex');
		$this->db->where('estado_k <>', 0);
		$this->db->where('idkardex',$paramDatos);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_cargar_rivencidosAlmacen($paramPaginate){
		$this->db->select('d.iddetallekardex,k.fecha,d.idreactivoinsumo,ri.descripcion,d.fecha_vencimiento,d.cantidad,d.estado_k');
		$this->db->from('detalle_kardex d');
		$this->db->join('reactivo_insumo ri','ri.idreactivoinsumo = d.idreactivoinsumo');
		$this->db->join('kardex k','k.idkardex = d.idkardex');
		$this->db->where('d.estado_k <>', 0);
		$this->db->where('d.gestion_vencimiento', 1);
		$this->db->where("TO_CHAR(fecha_vencimiento,'YYYY-MM-DD HH24:MI:SS') < TO_CHAR(NOW()+'15 day', 'YYYY-MM-DD HH24:MI:SS')");
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

	public function m_anular_movimientoAlmacen($id)
	{
		$data = array( 
			'estado_k' => 0 
		);
		$this->db->where('idkardex',$id);
		if($this->db->update('kardex', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular_detalle_movimientoAlmacen($id)
	{
		$data = array( 
			'estado_k' => 0 
		);
		$this->db->where('iddetallekardex',$id);
		if($this->db->update('detalle_kardex', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_anular_todo_detalle_movimientoAlmacen($id)
	{
		$data = array( 
			'estado_k' => 0 
		);
		$this->db->where('idkardex',$id);
		if($this->db->update('detalle_kardex', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_count_reactivoInsumo_vencidos()
	{
		$dt_15DiasAntes = date('Y-m-d', strtotime('-10 day')) ; // Suma 5 días
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle_kardex');
		$this->db->where('estado_k <>', 0);
		$this->db->where('gestion_vencimiento', 1);
		$this->db->where("TO_CHAR(fecha_vencimiento,'YYYY-MM-DD HH24:MI:SS') < TO_CHAR(NOW()+'15 day', 'YYYY-MM-DD HH24:MI:SS')");
		//$this->db->where("fecha_vencimiento < date_part()TEDIFF('dd',now(),-15)");
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_stock_actual_reactivo_insumo($id)
	{
		$this->db->select('stock as stock_actual');
		$this->db->from('reactivo_insumo');
		$this->db->where('idreactivoinsumo', $id);
		$fData = $this->db->get()->row_array();
		return $fData['stock_actual'];
	}

	public function m_actualizar_stock_precio($datos,$est)
	{
		$canti = $datos['cantidad'];
		if(isset($datos['precio'])){
			$prec = $datos['precio'];
		}
		$this->db->where('idreactivoinsumo',$datos['id']);
		$this->db->where('estado_ri', 1);
		if($est == 1){ // ingreso - aumentamos el stock
			$this->db->set('stock', 'stock + '.$canti, FALSE);
		}else 		   // salida - disminuimos el stock
		{
			$this->db->set('stock', 'stock - '.$canti, FALSE);
		}
		if (isset($datos['precio'])) { // si es un ingreso editamos el precio
			$this->db->set('precio', $prec, FALSE);
		}
		return $this->db->update('reactivo_insumo');
	}
	public function m_actualizar_costo_total($datos,$est)
	{
		$importe = $datos['importe'];
		$this->db->where('idkardex',$datos['idkardex']);
		$this->db->where('estado_k', 1);
		if($est == 1){ // ingreso - detalle // aumentamos el costo total
			$this->db->set("costo_total", "costo_total + '".$importe."'", FALSE);
		}else 		   // anular - detalle // disminuimos el costo total
		{
			$this->db->set("costo_total", "costo_total - '".$importe."'", FALSE);
		}
		return $this->db->update('kardex');
	}

	public function m_tratamiento_reactivoinsumo_vencido($datos)
	{
		$data = array( 
			'gestion_vencimiento' => 2 
		);
		$this->db->where('iddetallekardex',$datos['id']);
		if($this->db->update('detalle_kardex', $data)){
			return true;
		}else{
			return false;
		}

	}

}