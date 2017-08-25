<?php
class Model_campania extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_campanias($paramPaginate, $paramDatos){ 
		$this->db->select('camp.idcampania, camp.descripcion, camp.fecha_inicio, camp.fecha_final, camp.tipo_campania, camp.estado, camp.ca_idsedeempresaadmin, MIN(fc.fecha) as fecha_inicio , MAX(fc.fecha) as fecha_final');
		$this->db->select('e.idespecialidad, e.nombre');
		// $this->db->from('producto p');
		$this->db->from('campania camp'); 
		$this->db->join('especialidad e','camp.idespecialidad = e.idespecialidad'); 
		$this->db->join('fecha_campania fc','fc.idcampania = camp.idcampania','left');		
		$this->db->where('e.estado', 1); // habilitado
		$this->db->where_in('camp.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where('camp.ca_idsedeempresaadmin', $paramDatos['sedeempresa']);
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
		$this->db->group_by(array('camp.idcampania','e.idespecialidad'));
		return $this->db->get()->result_array();
	}
	public function m_count_campanias($paramPaginate, $paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('campania camp'); 
		$this->db->join('especialidad e','camp.idespecialidad = e.idespecialidad'); 
		$this->db->where('e.estado', 1); // habilitado
		$this->db->where_in('camp.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where('camp.ca_idsedeempresaadmin', $paramDatos['sedeempresa']);
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
	public function m_cargar_detalle_campanias($paramPaginate,$paramDatos){ // listado de detalle
		$this->db->select('d.iddetallepaquete,c.descripcion as campania,c.idcampania,pq.descripcion as paquete ,d.idpaquete,pm.descripcion as producto,d.precio,c.estado'); 
		$this->db->from('detalle_paquete d'); 
		$this->db->join('paquete pq','pq.idpaquete = d.idpaquete'); 
		$this->db->join('campania c','pq.idcampania = c.idcampania'); 
		$this->db->join('producto_master pm','pm.idproductomaster = d.idproductomaster'); 
		$this->db->where('pq.estado', 1); // habilitado
		$this->db->where_in('d.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where_in('c.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where('c.ca_idsedeempresaadmin', $paramDatos['sedeempresa']);
		
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
	public function m_count_detalle_campanias($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle_paquete d'); 
		$this->db->join('paquete pq','pq.idpaquete = d.idpaquete'); 
		$this->db->join('campania c','pq.idcampania = c.idcampania'); 
		$this->db->join('producto_master pm','pm.idproductomaster = d.idproductomaster'); 
		$this->db->where('pq.estado', 1); // habilitado
		$this->db->where_in('d.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where_in('c.estado', array(1,2) ); // habilitado o deshabilitado
		$this->db->where('c.ca_idsedeempresaadmin', $paramDatos['sedeempresa']);
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
	public function m_cargar_detalle_paquetes_id($datos){ 

		$this->db->select('d.iddetallepaquete,d.idpaquete ,d.idproductomaster,(e.nombre) as especialidad, (pm.descripcion) as descripcion, (d.precio)::NUMERIC, d.estado, (pq.monto_total)::NUMERIC'); 
		$this->db->from('detalle_paquete d'); 
		$this->db->join('paquete pq','pq.idpaquete = d.idpaquete'); 
		$this->db->join('producto_master pm','pm.idproductomaster = d.idproductomaster'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad'); 
		$this->db->where('d.estado', 1); // habilitado
		$this->db->where('pq.estado',1); // id del paquete
		$this->db->where('d.idpaquete',$datos); // id del paquete
		return $this->db->get()->result_array();
	}

	public function m_cargar_campanias_cbo($datos = FALSE){ 
		$this->db->select('idcampania,descripcion');
		$this->db->from('campania');
		$this->db->where('estado <>', 0); // habilitado o deshabilitado 
		if( $datos ){
			$this->db->ilike($datos['nameColumn'], $datos['search']);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_paquetes_cbo($datos){ 
		$this->db->select('idpaquete, descripcion, (monto_total)::NUMERIC');
		$this->db->from('paquete');
		$this->db->where('estado <>', 0); // habilitado o deshabilitado 
		$this->db->where('idcampania', $datos['datos']); // habilitado o deshabilitado
		$this->db->order_by('idpaquete', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_fechas($datos){ 
		$this->db->select('idfechacampania, fecha, tipo_fecha');
		$this->db->from('fecha_campania');
		$this->db->where('estado <>', 0); // habilitado o deshabilitado 
		$this->db->where('idcampania', $datos['datos']); // habilitado o deshabilitado
		$this->db->order_by('idfechacampania', 'ASC');
		return $this->db->get()->result_array();
	}	
	public function m_editar($datos){
		$data = array(
			'descripcion' => strtoupper_total($datos['campania']),
			'idespecialidad' => $datos['idespecialidad'],
			//'fecha_inicio' => $datos['fecha_inicio'] . ' ' . $datos['desdeHora'] . ':' . $datos['desdeMinuto'],
			//'fecha_final' => $datos['fecha_final'] . ' ' . $datos['hastaHora'] . ':' . $datos['hastaMinuto'],
			'tipo_campania' => $datos['tipocampania'],
			'ca_idsedeempresaadmin' => $datos['sedeempresa'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcampania',$datos['id']);
		return $this->db->update('campania', $data);
	}
	public function m_registrar_campania($datos){
		$data = array(
			'descripcion' => strtoupper_total($datos['campania']), 
			'idespecialidad' => $datos['idespecialidad'], 
			//'fecha_inicio' => $datos['fecha_inicio'] . ' ' . $datos['desdeHora'] . ':' . $datos['desdeMinuto'],
			//'fecha_final' => $datos['fecha_final'] . ' ' . $datos['hastaHora'] . ':' . $datos['hastaMinuto'],
			'tipo_campania' => $datos['tipocampania'],
			'ca_idsedeempresaadmin' => $datos['sedeempresa'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')

		);
		return $this->db->insert('campania', $data);
	}
	public function m_registrar_paquete($datos)
	{
		$data = array(
			'descripcion' => strtoupper_total($datos['paquete']), 
			'idcampania' => $datos['idcampania'], 
			'monto_total' => $datos['monto_total'],
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('paquete', $data);
	}

	public function m_registrar_fechas($datos)
	{
		$data = array(
			'idcampania' => $datos['idcampania'], 
			'fecha' => $datos['fecha'],
			'tipo_fecha' => $datos['tipo_fecha'] 
		);
		return $this->db->insert('fecha_campania', $data);
	}	

	public function m_actualizar_fecha($datos)
	{
		$data = array(
			'estado' => 1 ,
		);

		$this->db->where('idcampania',$datos['idcampania']);
		$this->db->where('fecha',$datos['fecha']);
		$this->db->where('tipo_fecha',$datos['tipo_fecha']);
		if($this->db->update('fecha_campania', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_editar_paquete($datos)
	{
		$data = array(
			'descripcion' => strtoupper_total($datos['paquete']), 
			'idcampania' => $datos['idcampania'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idpaquete',$datos['idpaquete']);
		if($this->db->update('paquete', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_buscar_fechas_campania_venta($datos)
	{
		$this->db->select('idfechacampania, fecha');
		$this->db->from('fecha_campania');
		$this->db->where('estado <>', 0); // habilitado o deshabilitado 
		$this->db->where('tipo_fecha', 1); // venta	
		$this->db->where('idcampania', $datos); 
		$this->db->order_by('fecha', 'ASC');
		return $this->db->get()->result_array();		
	}

	public function m_buscar_fechas_campania_atencion($datos)
	{
		$this->db->select('idfechacampania, fecha');
		$this->db->from('fecha_campania');
		$this->db->where('estado <>', 0); // habilitado o deshabilitado 
		$this->db->where('tipo_fecha', 2); // atencion
		$this->db->where('idcampania', $datos);
		$this->db->order_by('fecha', 'ASC');
		return $this->db->get()->result_array();		
	}

	public function m_anular_fecha($datos)
	{
		$data = array(
			'estado' => 0
		);
		$this->db->where('idfechacampania',$datos);
		if($this->db->update('fecha_campania', $data)){
			return true;
		}else{
			return false;
		}		
	}

	public function m_registrar_fecha($datos)
	{
		$data = array(
			'idcampania' => $datos['idcampania'], 
			'fecha' => $datos['fecha'], 
			'tipo_fecha' => $datos['tipo_fecha']
		);
		return $this->db->insert('fecha_campania', $data);
	}

	public function m_registrar_paquete_detalle($datos)
	{
		$data = array(
			'idpaquete' => $datos['idpaquete'], 
			'idproductomaster' => $datos['idproductomaster'], 
			'precio' => $datos['precio'],
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('detalle_paquete', $data);
	}
	public function m_actualiza_monto_paquete($datos)
	{
		$data = array(
			'monto_total' => $datos['monto_total'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idpaquete',$datos['idpaquete']);
		if($this->db->update('paquete', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado' => 1
		);
		$this->db->where('idcampania',$id);
		if($this->db->update('campania', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado' => 2
		);
		$this->db->where('idcampania',$id);
		if($this->db->update('campania', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular($id)
	{
		$data = array( 
			'estado' => 0 
		);
		$this->db->where('idcampania',$id);
		if($this->db->update('campania', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular_detalle($id)
	{
		$data = array( 
			'estado' => 0 
		);
		$this->db->where('iddetallepaquete',$id);
		if($this->db->update('detalle_paquete', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_lista_fecha_anulada($datos){
		$this->db->select('*');
		$this->db->from('fecha_campania');
		$this->db->where('estado', 0); // habilitado o deshabilitado 
		$this->db->where('tipo_fecha', $datos['tipo_fecha']); // atencion
		$this->db->where('idcampania', $datos['idcampania']);
		$this->db->where('fecha', $datos['fecha']);
		return $this->db->get()->result_array();		
	}
	public function m_verificar($campania){
		$this->db->where('descripcion',$campania);
		$row = $this->db->count_all_results('campania');
		if($row > 0){
			return true;
		}else{
			return false;
		}
	}
	/* LUIS 16_12_2015 */
	public function m_cargar_campanias_paquetes_cbo($datos){ // SOLO CAMPAÑAS DE LA EMPRESA ADMIN SELECCIONADA 
		$this->db->select('c.idcampania, (c.descripcion) AS campania , e.nombre , c.idespecialidad, c.fecha_inicio,c.fecha_final,c.estado, p.idpaquete, (p.descripcion) AS paquete, monto_total'); 
		$this->db->from('campania c'); 
		$this->db->join('especialidad e','c.idespecialidad = e.idespecialidad'); 
		$this->db->join('paquete p','c.idcampania = p.idcampania'); 
		$this->db->join('fecha_campania fc','c.idcampania = fc.idcampania'); 		
		$this->db->where('e.estado', 1); // habilitado 
		$this->db->where('c.estado', 1); // habilitado 
		$this->db->where('p.estado', 1); // habilitado 
		$this->db->where('fc.estado', 1); // habilitado 
		$this->db->where('fc.tipo_fecha', 1); // tipo venta 
		$this->db->where('c.ca_idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		$this->db->where('e.idespecialidad', $datos['especialidad']['idespecialidad']); 
		$this->db->where('fc.fecha', date('Y-m-d')); 		
		//$this->db->where("TO_CHAR(fecha_inicio,'YYYY-MM-DD HH24:MI:SS') <= TO_CHAR(NOW(), 'YYYY-MM-DD HH24:MI:SS')"); 
		//$this->db->where("TO_CHAR(fecha_final,'YYYY-MM-DD HH24:MI:SS') >= TO_CHAR(NOW(), 'YYYY-MM-DD HH24:MI:SS')"); 
		// $this->db->where('fecha_final <= NOW()'); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_campanias_paquetes_detalle($datos)
	{ 
		/*$this->db->select("
			(
			SELECT seesp.tiene_prog_cita 
			FROM pa_sede_especialidad seesp 
			WHERE e.idespecialidad = seesp.idespecialidad 
			AND idsede = ". $this->sessionHospital['idsede'] . " 
			LIMIT 1 ) 
			AS tiene_prog_cita 
		"); //tiene_prog_cita */
		$this->db->select("(CASE WHEN (c.tipo_campania = 1) THEN 'CAMPAÑA' ELSE 'CUPON' END) AS tipo_campania",FALSE); 
		$this->db->select('c.idcampania, (c.descripcion) AS campania, observacion_cp, e.idespecialidad, e.nombre, c.fecha_inicio, c.fecha_final,c.estado, 
			p.idpaquete, (p.descripcion) AS paquete, monto_total, dp.iddetallepaquete, dp.precio, pm.idproductomaster, (pm.descripcion) AS producto, (tipo_campania) AS idtipocampania, 
			tp.idtipoproducto, tp.nombre_tp'); 

		$this->db->select("seesp.tiene_prog_cita, seesp.tiene_venta_prog_cita, seesp.tiene_prog_proc, seesp.tiene_venta_prog_proc"); //tiene_prog_cita 
		$this->db->from('campania c'); 
		//$this->db->join('especialidad e','c.idespecialidad = e.idespecialidad'); 
		$this->db->join('paquete p','c.idcampania = p.idcampania'); 
		$this->db->join('detalle_paquete dp','p.idpaquete = dp.idpaquete'); 
		$this->db->join('producto_master pm','dp.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad'); 
		$this->db->join('pa_sede_especialidad seesp', 'e.idespecialidad = seesp.idespecialidad AND seesp.idsede = '.$this->sessionHospital['idsede'], 'left');
		
		$this->db->where('c.estado', 1); // habilitado 
		$this->db->where('p.estado', 1); // habilitado 
		$this->db->where('dp.estado', 1); // habilitado 
		$this->db->where('e.estado', 1); // habilitado 
		$this->db->where('p.idpaquete', $datos['campaniapaquete']['id']); 
		//$this->db->where('e.idespecialidad', $datos['especialidad']['id']); 
		return $this->db->get()->result_array(); 
		/*
			AND TO_CHAR("fecha_inicio",'YYYY-MM-DD HH:II:SS') <= TO_CHAR(NOW(), 'YYYY-MM-DD HH:II:SS') 
			AND TO_CHAR("fecha_final",'YYYY-MM-DD HH:II:SS') >= TO_CHAR(NOW(), 'YYYY-MM-DD HH:II:SS') 
		*/
	}
	/* RUBEN 10_11_2016 */
	public function m_anular_paquete($id)
	{
		$data = array( 
			'estado' => 0
		);
		$this->db->where('idpaquete',$id);
		if($this->db->update('paquete', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_actualizar_paquete_detalle($datos)
	{
		$data = array(
			'precio' => $datos['precio'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetallepaquete',$datos['id']);
		if($this->db->update('detalle_paquete', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_carga_report_listado_campanias($datos){
		$this->db->select('(
			SELECT SUM(det.total_detalle) AS monto 
			FROM detalle det 
			INNER JOIN venta ve ON det.idventa = ve.idventa 
			WHERE ve.estado = 1 AND det.idpaquete = pq.idpaquete AND det.idproductomaster = pm.idproductomaster 
		) AS monto_vendido',FALSE); 
		$this->db->select('( 
			SELECT COUNT(*) AS cantidad 
			FROM detalle det 
			INNER JOIN venta ve ON det.idventa = ve.idventa 
			WHERE ve.estado = 1 AND det.idpaquete = pq.idpaquete AND det.idproductomaster = pm.idproductomaster 
			GROUP BY det.idpaquete 
		) AS cantidad_vendida',FALSE); 
		$this->db->select("ea.razon_social as empresaadmin, ea.idempresaadmin, se.descripcion as sede, se.idsede",FALSE); 
		$this->db->select("esp.idespecialidad, esp.nombre as especialidad",FALSE); 
		$this->db->select("ca.idcampania, ca.descripcion as nombre_campania, ca.fecha_inicio, ca.fecha_final",FALSE); 
		$this->db->select("pq.idpaquete, pq.descripcion as nombre_paquete, pq.monto_total",FALSE); 
		$this->db->select("pm.idproductomaster, pm.descripcion as producto, pps.precio_sede as precio_normal, dpq.precio as precio_campania",FALSE); 
		$this->db->from('campania ca'); 
		$this->db->join('paquete pq','ca.idcampania = pq.idcampania');
		$this->db->join('detalle_paquete dpq','pq.idpaquete = dpq.idpaquete');
		$this->db->join('producto_master pm','dpq.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad esp','esp.idespecialidad = ca.idespecialidad');
		$this->db->join('sede_empresa_admin sea','sea.idsedeempresaadmin = ca.ca_idsedeempresaadmin');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede se','sea.idsede = se.idsede');
		$this->db->join('producto_precio_sede pps','sea.idsedeempresaadmin = pps.idsedeempresaadmin AND pm.idproductomaster = pps.idproductomaster'); 
		$this->db->where("ca.fecha_inicio BETWEEN '". $datos['desde'] ."' AND '" . $datos['hasta'] . "'"); 
		$this->db->where('ca.estado', 1); // habilitado 
		$this->db->where('pq.estado', 1); // habilitado 
		$this->db->where('dpq.estado', 1); // habilitado 
		$this->db->where('ca.ca_idsedeempresaadmin', $datos['sedeempresa']); 

		$this->db->order_by('ca.fecha_inicio DESC, ca.idcampania DESC, pq.idpaquete ASC, dpq.iddetallepaquete ASC');
 
		return $this->db->get()->result_array(); 
	}
}