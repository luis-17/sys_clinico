<?php
class Model_orden_compra extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_material_cbo($datos)
	{
		$this->db->select('*');
		$this->db->from('far_tipo_material');
		$this->db->where('estado_tm', 1);
		$this->db->order_by('idtipomaterial');
		return $this->db->get()->result_array();
	}
	public function m_cargar_ultima_orden_compra_de_almacen($datos)	{ 
		$this->db->select('fm.idmovimiento, orden_compra');
		$this->db->from('far_movimiento fm');
		// $this->db->where('estado', 1); // ya no se pone filtro porque el codigo generado tendrá que ser diferente asi esté anulado
		$this->db->where('idtipomaterial', $datos['tipoMaterial']['id']);
		$this->db->where('idalmacen', $datos['almacen']['id']); 
		// $this->db->order_by('orden_venta','DESC'); 
		$this->db->order_by('fm.idmovimiento','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_cargar_orden_compra_cbo($datos){
		$this->db->select('idmovimiento, orden_compra, estado_movimiento, idproveedor, forma_pago, moneda, letras, modo_igv, (total_a_pagar::numeric) AS total_a_pagar');
		$this->db->from('far_movimiento');
		$this->db->where('estado_movimiento', 2); // pendiente
		$this->db->where('tipo_movimiento', 7); // orden de compra
		$this->db->where('estado_orden_compra', 2); // orden aprobada
		$this->db->where('idalmacen', $datos['almacen']['id']);
		$this->db->order_by('idmovimiento');
		return $this->db->get()->result_array();
	}
	/* ==================== ORDENES DE COMPRA  ================== */
	public function m_cargar_orden_compra($paramDatos, $paramPaginate){
		// SUBCONSULTA
		// para q no se repitan las ordenes de compra, para la fecha de ingreso utilizamos la ultima fecha del movimiento
		$this->db->select_max('fm2.idmovimiento');
		$this->db->select_max('fm2.fecha_movimiento','fecha_entrega_real');
		$this->db->select('fm2.orden_compra');
		$this->db->from('far_movimiento fm2');
		$this->db->where('fm2.tipo_movimiento',2);
		$this->db->where('fm2.estado_movimiento',1);
		$this->db->where('fm2.orden_compra IS NOT NULL');
		$this->db->group_by('fm2.orden_compra');
		$sqlOC = $this->db->get_compiled_select();
		$this->db->reset_query();
		// consulta principal
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra, 
			p.idproveedor, p.razon_social, p.ruc, p.direccion_fiscal, p.telefono, 
			fm.sub_total, fm.total_igv, fm.total_a_pagar, fm.motivo_movimiento, 
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega, fm.estado_movimiento, fm.modo_igv, 
		 	fm.idtipomaterial, ftm.descripcion_tm, fm.estado_orden_compra, fm.forma_pago, fm.moneda, fm.letras,
		 	alm.nombre_alm, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno, fmc.fecha_entrega_real'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		// $this->db->join('far_movimiento fmc','fm.orden_compra = fmc.orden_compra AND fmc.tipo_movimiento = 2 AND fmc.estado_movimiento = 1','left');
		$this->db->join("(" . $sqlOC. ") AS fmc", 'fm.orden_compra = fmc.orden_compra','left');
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(2,1,0)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
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
		return $this->db->get()->result_array();
	}
	public function m_count_sum_orden_compra($paramDatos, $paramPaginate){
		// subconsulta
		// para q no se repitan las ordenes de compra, para la fecha de ingreso utilizamos la ultima fecha del movimiento
		$this->db->select_max('fm2.idmovimiento');
		$this->db->select_max('fm2.fecha_movimiento','fecha_entrega_real');
		$this->db->select('fm2.orden_compra');
		$this->db->from('far_movimiento fm2');
		$this->db->where('fm2.tipo_movimiento',2);
		$this->db->where('fm2.estado_movimiento',1);
		$this->db->where('fm2.orden_compra IS NOT NULL');
		$this->db->group_by('fm2.orden_compra');
		$sqlOC = $this->db->get_compiled_select();
		$this->db->reset_query();
		// consulta principal
		$this->db->select('COUNT(*) AS contador,SUM(CASE WHEN fm.estado_movimiento IN (1, 2) THEN (fm.total_a_pagar::numeric) ELSE 0 END) AS suma_total');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		// $this->db->join('far_movimiento fmc','fm.orden_compra = fmc.orden_compra AND fmc.tipo_movimiento = 2 AND fmc.estado_movimiento = 1','left');
		$this->db->join("(" . $sqlOC. ") AS fmc", 'fm.orden_compra = fmc.orden_compra','left');
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(2,1,0)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
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
	public function m_cargar_solo_orden_compra_habilitadas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra, 
			p.idproveedor, p.razon_social, p.ruc, p.direccion_fiscal, p.telefono, 
			fm.sub_total, fm.total_igv, fm.total_a_pagar, fm.motivo_movimiento, fm.conteo_mensaje_oc, 
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega, fm.estado_movimiento, fm.modo_igv, 
		 	fm.idtipomaterial, ftm.descripcion_tm, fm.estado_orden_compra, fm.forma_pago, fm.moneda, fm.letras, 
		 	alm.nombre_alm, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		// $this->db->join('far_movimiento fmc','fm.orden_compra = fmc.orden_compra AND fmc.tipo_movimiento = 2 AND fmc.estado_movimiento = 1','left');
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(2)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
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
		return $this->db->get()->result_array();
	}
	public function m_cargar_ingresos_por_orden_compra($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento, fm.fecha_movimiento,fm.guia_remision, fm.ticket_venta as factura, fm.orden_compra');
		$this->db->from('far_movimiento fm');
		$this->db->where('tipo_movimiento',2); // ingreso por compra
		$this->db->where('estado_movimiento',1); // activo
		$this->db->where('fm.orden_compra', $paramDatos['orden_compra']);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}else{
			$this->db->order_by('fecha_movimiento');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();

	}
	public function m_count_ingresos_por_orden_compra($paramDatos){
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('far_movimiento fm');
		$this->db->where('tipo_movimiento',2); // ingreso por compra
		$this->db->where('estado_movimiento',1); // activo
		$this->db->where('fm.orden_compra', $paramDatos['orden_compra']);
		
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	/* LOGISTICA */
	public function m_cargar_ordenes_compra_para_reporte($paramDatos){
		// subconsulta para q no se repitan las ordenes de compra, para la fecha de ingreso utilizamos la ultima fecha del movimiento
		$this->db->select_max('fm2.idmovimiento');
		$this->db->select_max('fm2.fecha_movimiento','fecha_entrega_real');
		$this->db->select('fm2.orden_compra');
		$this->db->from('far_movimiento fm2');
		$this->db->where('fm2.tipo_movimiento',2);
		$this->db->where('fm2.estado_movimiento',1);
		$this->db->where('fm2.orden_compra IS NOT NULL');
		$this->db->group_by('fm2.orden_compra');
		$sqlOC = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra, 
			p.idproveedor, p.razon_social, p.ruc, p.direccion_fiscal, p.telefono, 
			(fm.sub_total)::NUMERIC, (fm.total_igv)::NUMERIC, (fm.total_a_pagar)::NUMERIC, fm.motivo_movimiento, 
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega,fmc.fecha_entrega_real, fm.estado_movimiento, fm.modo_igv, 
		 	fm.idtipomaterial, ftm.descripcion_tm, fm.estado_orden_compra, fm.forma_pago, fm.moneda, fm.letras,
		 	alm.nombre_alm, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');
		$this->db->join("(" . $sqlOC. ") AS fmc", 'fm.orden_compra = fmc.orden_compra','left');
		$this->db->where('fm.idsedeempresaadmin', $paramDatos['sedeempresa']);
		$this->db->where_in('fm.estado_movimiento', array(2,1)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		$this->db->order_by('fecha_movimiento','ASC');
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_etapas_oc($paramDatos, $paramPaginate=FALSE){ 
		$this->db->select('fm.idmovimiento, ao.idareaoc, ao.descripcion, ao.mail_1, ao.mail_2, epa.idestadoporarea, epa.descripcion_estado, oce.idordencompraestado, oce.fecha_estado, 
			oce.control_cambios, oce.comentario'); 
		$this->db->from('far_movimiento fm');
		
		$this->db->join('log_orden_compra_estado oce', 'fm.idmovimiento = oce.idmovimiento');
		$this->db->join('log_estado_por_area epa', 'oce.idestadoporarea = epa.idestadoporarea AND estado_lea = 1');
		$this->db->join('log_area_oc ao', 'epa.idareaoc = ao.idareaoc AND estado_la = 1');
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.estado_movimiento', array(2,1,0)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		return $this->db->get()->result_array();
	}
	public function m_cargar_etapas_de_esta_oc($datos){ 
		$this->db->select('fm.idmovimiento, ao.idareaoc, ao.descripcion, ao.mail_1, ao.mail_2, epa.idestadoporarea, epa.descripcion_estado, oce.idordencompraestado, oce.fecha_estado, 
			oce.control_cambios, oce.comentario, pro.razon_social, pro.email'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor pro', 'fm.idproveedor = pro.idproveedor');
		$this->db->join('log_orden_compra_estado oce', 'fm.idmovimiento = oce.idmovimiento');
		$this->db->join('log_estado_por_area epa', 'oce.idestadoporarea = epa.idestadoporarea AND estado_lea = 1');
		$this->db->join('log_area_oc ao', 'epa.idareaoc = ao.idareaoc AND estado_la = 1');
		$this->db->where_in('fm.idmovimiento', $datos['idmovimiento']); // MOVIMIENTO 
		return $this->db->get()->result_array();
	}
	
	public function m_cargar_ordenes_anuladas($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra,
			p.idproveedor, p.razon_social, p.ruc, p.direccion_fiscal, p.telefono,
			fm.sub_total, fm.total_igv, fm.total_a_pagar, motivo_movimiento,
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega, fm.fecha_anulacion, fm.estado_movimiento,
		 	rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->where_in('fm.estado_movimiento', array(0)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
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
	public function m_count_sum_ordenes_anuladas($paramDatos, $paramPaginate){
		$this->db->select('COUNT(*) AS contador,SUM(total_a_pagar) AS suma_total');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->where_in('fm.estado_movimiento', array(0)); // MOVIMIENTO
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
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
	public function m_cargar_orden_compra_por_id($idmovimiento){
		$this->db->select('fm.idmovimiento, fm.tipo_movimiento, fm.orden_compra,
			p.idproveedor, p.razon_social, p.ruc, p.nombre_comercial, p.direccion_fiscal, p.telefono, p.fax,
			(fm.sub_total)::NUMERIC, (fm.total_igv)::NUMERIC, (fm.total_a_pagar)::NUMERIC, motivo_movimiento,
		 	fm.fecha_movimiento, fm.fecha_aprobacion, fm.fecha_entrega, fm.estado_movimiento, fm.fecha_emision_correo, 
		 	fm.idtipomaterial, ftm.descripcion_tm, fm.estado_orden_compra, fm.forma_pago, fm.moneda, fm.letras,
		 	alm.nombre_alm, alm.direccion_anexo, rhe.nombres, rhe.apellido_paterno, rhe.apellido_materno, 
		 	ea.razon_social as nombreEmpresaFarm, ea.ruc as rucEmpresaFarm, ea.domicilio_fiscal, nombre_logo, ea.idempresaadmin');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('rh_empleado rhe','fm.iduser = rhe.iduser');
		$this->db->join('far_tipo_material ftm', 'fm.idtipomaterial = ftm.idtipomaterial AND ftm.estado_tm = 1', 'left');
		$this->db->join('sede_empresa_admin sea', 'alm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea', 'sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('fm.idmovimiento', $idmovimiento ); // ORDEN COMPRA
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_detalle_entrada($paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE);
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.motivo_movimiento,
			fdm.descuento_porcentaje, (fdm.descuento_asignado)::NUMERIC,
			(fdm.total_detalle)::NUMERIC, fdm.caja_unidad, ff.descripcion_ff, ff.acepta_caja_unidad,
			m.idmedicamento, m.denominacion, m.idunidadmedida, m.excluye_igv, m.contenido,
			fl.idlaboratorio, fl.nombre_lab, fdm.iddetallemovimiento, fdm.fecha_vencimiento, fdm.num_lote, fdm.estado_detalle');
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN (fdm.precio_unitario_por_caja)::NUMERIC ELSE (fdm.precio_unitario)::NUMERIC END) AS precio_unitario", FALSE);
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN ( fdm.cantidad_caja ) ELSE (fdm.cantidad) END) AS cantidad", FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['idmovimiento']);
		$this->db->where_in('estado_detalle', array(1,2));
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
		$this->db->where_in('estado_detalle', array(1,2));
		$fData = $this->db->get()->row_array();
		return $fData; 
	}
	public function m_cargar_detalle_orden_cbo($paramDatos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE);
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.motivo_movimiento,
			fdm.descuento_porcentaje, (fdm.descuento_asignado)::NUMERIC,
			(fdm.total_detalle)::NUMERIC, fdm.caja_unidad, ff.descripcion_ff, ff.acepta_caja_unidad,
			m.idmedicamento, m.denominacion, m.idunidadmedida, m.excluye_igv, m.contenido,
			fl.idlaboratorio, fl.nombre_lab, fdm.iddetallemovimiento, fdm.fecha_vencimiento, fdm.num_lote, fdm.estado_detalle');
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN (fdm.precio_unitario_por_caja)::NUMERIC ELSE (fdm.precio_unitario)::NUMERIC END) AS precio_unitario", FALSE);
		$this->db->select("(CASE WHEN caja_unidad = 'CAJA' THEN ( fdm.cantidad_caja ) ELSE (fdm.cantidad) END) AS cantidad", FALSE);
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio','left'); 
		$this->db->where('fm.idmovimiento', $paramDatos['idmovimiento']);
		$this->db->where('estado_detalle', 1);
		$this->db->order_by('m.denominacion', 'ASC');
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_producto_entrada($paramDatos, $paramPaginate)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('fm.idmovimiento, fm.estado_movimiento, fm.fecha_movimiento, fm.orden_compra,
			p.idproveedor, p.razon_social, p.ruc, 
			fdm.cantidad, fdm.precio_unitario, fdm.total_detalle, 
			m.idmedicamento,
			fl.idlaboratorio, fl.nombre_lab 
		'); 
		$this->db->from('far_movimiento fm'); 
		$this->db->join('far_detalle_movimiento fdm','fm.idmovimiento = fdm.idmovimiento'); 
		$this->db->join('medicamento m','fdm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_laboratorio fl','m.idlaboratorio = fl.idlaboratorio AND estado_lab = 1','left'); 
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left'); 
		$this->db->where_in('fm.estado_movimiento', array(1,2)); //
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
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
		$this->db->join('far_proveedor p','fm.idproveedor = p.idproveedor','left');
		$this->db->where_in('fm.estado_movimiento', array(1,2)); //
		$this->db->where('fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where_in('fm.tipo_movimiento', array(7)); // ORDEN COMPRA
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
	// public function m_obtener_subalmacen_principal($idalmacen){
	// 	$this->db->select('idsubalmacen, nombre_salm, idtiposubalmacen');
	// 	$this->db->from('far_subalmacen');
	// 	$this->db->where('idalmacen', $idalmacen);
	// 	$this->db->where('estado_salm', 1);
	// 	$this->db->limit(1);
	// 	return $this->db->get()->row_array();
	// }
	public function m_obtener_ultimo_precio_compra($paramDatos){
		$this->db->select('(fdm.precio_unitario)::NUMERIC, (fdm.precio_unitario_por_caja)::NUMERIC, fdm.caja_unidad');
		$this->db->from('far_detalle_movimiento fdm');
		if($this->sessionHospital['key_group'] == 'key_sistemas')
			$this->db->join('far_movimiento fm', 'fdm.idmovimiento = fm.idmovimiento');
		else
			$this->db->join('far_movimiento fm', 'fdm.idmovimiento = fm.idmovimiento' .
			' AND fm.idalmacen = ' . $paramDatos['idalmacen']);

		$this->db->where('fdm.idmedicamento', $paramDatos['idmedicamento']);
		$this->db->where('fm.tipo_movimiento', 2); // solo compra
		$this->db->where('fm.estado_movimiento <>', 0);

		$this->db->order_by('fecha_compra', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();

	}
	public function m_registrar_orden_compra($datos){
		$data = array(
			'orden_compra' => $datos['orden_compra'],
			'idsedeempresaadmin' => $datos['almacen']['idsedeempresaadmin'],
			'dir_movimiento' => 0,
			'tipo_movimiento' => 7,
			'idtipodocumento' => 1,
			'idtipomaterial' => $datos['tipoMaterial']['id'],
			'iduser' => $this->sessionHospital['idusers'],
			'idalmacen' => $datos['almacen']['id'],
			'idsubalmacen' => $datos['idsubalmacen'],
			'fecha_movimiento' => $datos['fecha_movimiento'],
			'fecha_aprobacion' => NULL,
			'fecha_entrega' => $datos['fecha_entrega'],
			'idproveedor' => empty($datos['proveedor']['id'])? null : $datos['proveedor']['id'],
			'sub_total' =>  empty($datos['subtotal'])? null : $datos['subtotal'],
			'total_igv' =>  empty($datos['igv'])? null : $datos['igv'],
			'total_a_pagar' =>  empty($datos['total'])? null : $datos['total'],
			'motivo_movimiento' =>  empty($datos['motivo_movimiento'])? null : $datos['motivo_movimiento'],
			'estado_movimiento' => 2,
			'forma_pago' => $datos['forma_pago'],
			'moneda' => $datos['moneda'],
			'letras' => @$datos['letras'],
			'estado_orden_compra' => $datos['estado_orden'],
			'modo_igv' => @$datos['modo_igv'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_movimiento', $data);
	}
	public function m_editar_orden_compra($datos){
		$data = array(
			'sub_total' =>  empty($datos['subtotal'])? null : $datos['subtotal'],
			'total_igv' =>  empty($datos['igv'])? null : $datos['igv'],
			'total_a_pagar' =>  empty($datos['total'])? null : $datos['total'],
			'motivo_movimiento' =>  empty($datos['motivo_movimiento'])? null : $datos['motivo_movimiento'],
			'forma_pago' => $datos['forma_pago'],
			'letras' => @$datos['letras'],
			'moneda' => $datos['moneda'],
			'estado_orden_compra' => $datos['estado_orden'],
			'modo_igv' => @$datos['modo_igv'],
			'fecha_entrega' => $datos['fecha_entrega'], 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		return $this->db->update('far_movimiento', $data);
	}
	public function m_registrar_detalle_orden_compra($datos){
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'],
			'idmedicamento' => $datos['idmedicamento'],
			'idmedicamentoalmacen' => $datos['idmedicamentoalmacen'],
			'cantidad' => $datos['cantidad'],
			'precio_unitario' => empty($datos['precio'])? null : $datos['precio'],
			'total_detalle' => empty($datos['importe'])? null : $datos['importe'],
			'descuento_porcentaje' => empty($datos['descuento'])? 0 : $datos['descuento'],
			'descuento_asignado' => empty($datos['descuento_valor'])? 0 : $datos['descuento_valor'],
			'cantidad_caja' => empty($datos['cantidad_caja'])? 0 : $datos['cantidad_caja'],
			'caja_unidad' => $datos['caja_unidad'],
			'precio_unitario_por_caja' => empty($datos['precio_unitario_por_caja'])? null : $datos['precio_unitario_por_caja'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_detalle_movimiento', $data);
	}
	public function m_editar_detalle_orden_compra($datos){
		//var_dump($datos); exit();
		$data = array( 
			'cantidad' => $datos['cantidad'],
			'precio_unitario' => empty($datos['precio'])? null : $datos['precio'],
			'total_detalle' => empty($datos['importe'])? null : $datos['importe'],
			'caja_unidad' => $datos['caja_unidad'],
			'cantidad_caja' => empty($datos['cantidad_caja'])? 0 : $datos['cantidad_caja'],
			'precio_unitario_por_caja' => empty($datos['precio_unitario_por_caja'])? null : $datos['precio_unitario_por_caja'],
			'descuento_porcentaje' => empty($datos['descuento'])? 0 : $datos['descuento'],
			'descuento_asignado' => empty($datos['descuento_valor'])? 0 : $datos['descuento_valor'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetallemovimiento', $datos['iddetallemovimiento']);
		return $this->db->update('far_detalle_movimiento', $data);
	}
	public function m_actualizar_stock_medicamento($datos){
		$this->db->where('idmedicamento', $datos['idmedicamento']);
		$this->db('stock_actual', 'stock_actual+'.$datos['cantidad'], FALSE);
		$this->db->set('updatedAt', date('Y-m-d H:i:s'));
		return $this->db->update('medicamento');
	}
	public function m_actualizar_estado_orden_compra($datos){
		$this->db->where('idmovimiento', $datos['orden_compra']['idmovimiento']);
		$this->db->set('estado_movimiento', 1);
		return $this->db->update('far_movimiento');
	}
	public function m_actualizar_estado_detalle_orden_compra($datos){
		$this->db->where('idmovimiento', $datos['orden_compra']['idmovimiento']);
		$this->db->where_in('idmedicamento', $datos['arrIdMedicamentos']);
		$this->db->set('estado_detalle', 2);
		return $this->db->update('far_detalle_movimiento');
	}
	public function m_verificar_existe_orden_compra($datos){
		$this->db->from('far_movimiento');
		$this->db->where('orden_compra', $datos['orden_compra']);
		$this->db->where('tipo_movimiento', 7);
		$this->db->where('estado_movimiento <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_aprobacion_oc($datos) // si no está aprobado
	{
		$this->db->select('idmovimiento,estado_orden_compra');
		$this->db->from('far_movimiento');
		$this->db->where('orden_compra', $datos['orden_compra']);
		$this->db->where('tipo_movimiento', 7);
		$this->db->where('estado_movimiento <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_aprobar_orden_compra($datos)
	{
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		$this->db->set('estado_orden_compra', 2);
		$this->db->set('fecha_aprobacion', date('Y-m-d H:i:s'));
		return $this->db->update('far_movimiento');
	}
	public function m_deshacer_aprobar_orden_compra($datos)
	{
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		$this->db->set('estado_orden_compra', 1);
		$this->db->set('fecha_aprobacion', NULL);
		return $this->db->update('far_movimiento');
	}
	public function m_verificar_existe_medicamento_en_orden_compra($idmedicamento,$idmovimiento){
		$this->db->from('far_detalle_movimiento');
		$this->db->where('idmovimiento', $idmovimiento);
		$this->db->where('idmedicamento', $idmedicamento);
		$this->db->where('estado_detalle <> 0');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_detalles_disponibles_orden_compra($datos){
		$this->db->from('far_detalle_movimiento');
		$this->db->where('idmovimiento', $datos['orden_compra']['idmovimiento']);
		$this->db->where('estado_detalle', 1);
		return $this->db->get()->num_rows();
	}
	public function m_obtener_estado_detalle_orden_compra($iddetallemovimiento){
		$this->db->select('estado_detalle');
		$this->db->from('far_detalle_movimiento');
		$this->db->where('iddetallemovimiento', $iddetallemovimiento);
		return $this->db->get()->row_array();
	}
	/* SEGUIMIENTO OC */
	public function m_comprobar_varios_estados_oc($idmovimiento,$arrEstado)
	{
		$this->db->select('fm.idmovimiento');
		$this->db->from('far_movimiento fm');
		$this->db->join('log_orden_compra_estado oce', 'fm.idmovimiento = oce.idmovimiento');
		$this->db->join('log_estado_por_area epa', 'oce.idestadoporarea = epa.idestadoporarea AND estado_lea = 1');
		$this->db->join('log_area_oc ao', 'epa.idareaoc = ao.idareaoc AND estado_la = 1');
		$this->db->where('fm.idmovimiento', $idmovimiento);
		// $this->db->where('ao.idareaoc', $idarea);
		$this->db->where_in('epa.descripcion_estado', $arrEstado);
		$this->db->where('control_cambios', 1);
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_comprobar_estado_oc($idmovimiento,$idarea,$estado)
	{
		$this->db->select('fm.idmovimiento, ao.firma_del_area');
		$this->db->from('far_movimiento fm');
		$this->db->join('log_orden_compra_estado oce', 'fm.idmovimiento = oce.idmovimiento');
		$this->db->join('log_estado_por_area epa', 'oce.idestadoporarea = epa.idestadoporarea AND estado_lea = 1');
		$this->db->join('log_area_oc ao', 'epa.idareaoc = ao.idareaoc AND estado_la = 1');
		$this->db->where('fm.idmovimiento', $idmovimiento);
		$this->db->where('ao.idareaoc', $idarea);
		$this->db->where('epa.descripcion_estado', $estado);
		$this->db->where('control_cambios', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_oc_enviada($idmovimiento)
	{
		$this->db->select('fm.idmovimiento');
		$this->db->from('far_movimiento fm');
		$this->db->where('fm.idmovimiento', $idmovimiento);
		$this->db->where('conteo_mensaje_oc',0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cambiar_estado_oc($datos)
	{
		$data = array( 
			'idmovimiento' => $datos['idmovimiento'],
			'idestadoporarea' => $datos['idestadoporarea'],
			'fecha_estado' => date('Y-m-d H:i:s'),
			'control_cambios' => 1,
			'comentario' => $datos['comentario'],
		);
		return $this->db->insert('log_orden_compra_estado', $data);
	}
	public function m_deshacer_cambio_estado_oc($datos)
	{
		$data = array( 
			'control_cambios' => 0  
		);
		$this->db->where('idordencompraestado',$datos['idordencompraestado']);
		return $this->db->update('log_orden_compra_estado', $data);
	}
	public function m_contar_mensajes_enviados_proveedor($datos)
	{ 
		$this->db->where('idmovimiento',$datos['idmovimiento']); 
		$this->db->set('conteo_mensaje_oc', 'conteo_mensaje_oc+1', FALSE); 
		return $this->db->update('far_movimiento'); 
	}
	public function m_actualizar_fecha_envio_correo_oc($datos)
	{
		$data = array( 
			'fecha_emision_correo' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']); 
		return $this->db->update('far_movimiento', $data);
	}
	public function m_sum_total_entrada_farmacia($datos)
	{
		// $this->db->select('SUM(fm.total_a_pagar::numeric) as total',FALSE);
		$this->db->select('SUM(fdm.total_detalle::numeric) as total',FALSE);
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento');
		$this->db->where('fm.orden_compra', $datos['descripcion']);
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fm.tipo_movimiento', 2);
		return $this->db->get()->row_array();
	}
}