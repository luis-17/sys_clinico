<?php 
class Model_solicitud_procedimiento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_solicitud_procedimientos_paciente($paramPaginate,$paramDatos){
		$this->db->select('idsolicitud');
		$this->db->from('detalle d');
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1');
		$this->db->join('historia h', 'v.idcliente = h.idcliente AND h.idhistoria = ' . $paramDatos['idhistoria']);
		$this->db->where('d.tiposolicitud', 2); // procedimiento
		$sqlSolicitudesVendidas = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('sp.idsolicitudprocedimiento AS id');
		$this->db->select('sp.idsolicitudprocedimiento, nro_solicitud, cantidad, informe, fecha_solicitud, 
			h.idhistoria, sp.idatencionmedica, pm.idproductomaster, (descripcion) AS producto, pps.precio_sede AS precio, fecha_realizacion, estado_sp,
			tp.idtipoproducto, tp.nombre_tp, es.nombre, es.idespecialidad'); 
		$this->db->select("seesp.tiene_prog_cita, seesp.tiene_venta_prog_cita, seesp.tiene_prog_proc, seesp.tiene_venta_prog_proc"); //tiene_prog_cita
		$this->db->from('solicitud_procedimiento sp'); 
		$this->db->join('historia h','sp.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sp.idproductomaster = pm.idproductomaster');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND pps.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('especialidad es','sp.idespecialidad = es.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->join('pa_sede_especialidad seesp', 'es.idespecialidad = seesp.idespecialidad AND seesp.idsede = '.$this->sessionHospital['idsede'], 'left');
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("sp".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		}
		if (@$paramDatos['atendido'] === 'no') { 
			$this->db->where('estado_sp', 1); 	// SOLICITADO 
		}else{
			$this->db->where('estado_sp <>', 0);
		}
		$this->db->where('estado_pps', 1);
		$this->db->where('h.idhistoria', $paramDatos['idhistoria']);
		$this->db->where('sp.idsolicitudprocedimiento NOT IN ('. $sqlSolicitudesVendidas . ')');

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
	public function m_count_solicitud_procedimientos_paciente($paramPaginate,$paramDatos)
	{	
		$this->db->select('idsolicitud');
		$this->db->from('detalle d');
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1');
		$this->db->join('historia h', 'v.idcliente = h.idcliente AND h.idhistoria = ' . $paramDatos['idhistoria']);
		$this->db->where('d.tiposolicitud', 2); // procedimiento
		$sqlSolicitudesVendidas = $this->db->get_compiled_select();
		$this->db->reset_query();


		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('solicitud_procedimiento sp'); 
		$this->db->join('historia h','sp.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sp.idproductomaster = pm.idproductomaster'); 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("sp".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		}
		if (@$paramDatos['atendido'] === 'no') { 
			$this->db->where('estado_sp', 1); 	// SOLICITADO 
		}else{
			$this->db->where('estado_sp <>', 0);
		}
		$this->db->where('h.idhistoria', $paramDatos['idhistoria']);
		$this->db->where('sp.idsolicitudprocedimiento NOT IN ('. $sqlSolicitudesVendidas . ')');
		
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
	public function m_cargar_procedimientos_de_especialidad_session_autocomplete($searchColumn, $searchText)
	{
		$this->db->select('idproductomaster, descripcion, p.idespecialidad'); 
		$this->db->from('producto_master p'); 
		$this->db->where('estado_pm', 1); 
		$this->db->where('idtipoproducto', 16); // PROCEDIMIENTO CLINICO 
		$this->db->where('p.idespecialidad', $this->sessionHospital['idespecialidad']); // solo procedimientos de esa especialidad 
		$this->db->ilike($searchColumn, $searchText); 
		$this->db->order_by('LENGTH(descripcion)','ASC');
		$this->db->limit(8); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_procedimientos_para_orden_autocomplete($searchColumn, $searchText, $especialidad = FALSE)
	{
		$this->db->select('idproductomaster, descripcion, p.idespecialidad'); 
		$this->db->from('producto_master p'); 
		$this->db->where('estado_pm', 1); 
		$this->db->where('idtipoproducto', 16); // PROCEDIMIENTO CLINICO 
		if (!empty($especialidad)) {
			$this->db->where('p.idespecialidad', $especialidad['id']); // solo procedimientos de una especialidad especifica 
		}	
		$this->db->ilike($searchColumn, $searchText); 
		$this->db->order_by('LENGTH(descripcion)','ASC');
		$this->db->limit(8); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_solicitudes_procedimiento_session($paramPaginate,$paramDatos){ 
		$this->db->select('sp.idsolicitudprocedimiento, sp.nro_solicitud, sp.cantidad, sp.informe, sp.fecha_solicitud, 
			h.idhistoria, sp.idatencionmedica, pm.idproductomaster, (descripcion) AS producto, estado_sp,
			tp.idtipoproducto, tp.nombre_tp, es.nombre AS especialidad, es.idespecialidad, idmedicosolicitud');
		$this->db->select('cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno');
		$this->db->select('med.idmedico, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('d.paciente_atendido_det, fecha_atencion_det');

		$this->db->from('solicitud_procedimiento sp'); 
		$this->db->join('historia h','sp.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sp.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad es','sp.idespecialidad = es.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('cliente cl', 'h.idcliente = cl.idcliente');
		$this->db->join('medico med', 'med.idmedico = sp.idmedicosolicitud');
		$this->db->join('detalle d', 'sp.idsolicitudprocedimiento = d.idsolicitud AND d.tiposolicitud = 2','left'); // 2: procedimiento
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1','left');

		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){
			$this->db->where('DATE("sp".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));
		}
		if ( $this->sessionHospital['key_group'] == 'key_salud' ) { 
			$this->db->where('sp.idespecialidad', @$this->sessionHospital['idespecialidad']);
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
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_solicitudes_procedimiento_session($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('solicitud_procedimiento sp'); 
		$this->db->join('historia h','sp.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sp.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad es','sp.idespecialidad = es.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('cliente cl', 'h.idcliente = cl.idcliente');
		$this->db->join('medico med', 'med.idmedico = sp.idmedicosolicitud');
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("sp".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		}
		if ( $this->sessionHospital['key_group'] == 'key_salud' ) { 
			$this->db->where('sp.idespecialidad', @$this->sessionHospital['idespecialidad']);
		}

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
	public function m_registrar_solicitud_procedimiento($datos)
	{
		if( $this->sessionHospital['key_group'] == 'key_sistemas' ){
			$idmedico = $datos['idmedico'];
			$fecha = $datos['fecha_solicitud'] . ' ' . date('H:i:s');
		}else{
			$idmedico = @$this->sessionHospital['idmedico'];
			$fecha = date('Y-m-d H:i:s');
		}
		
		$data = array(
			'idproductomaster' => $datos['procedimiento']['id'], 
			'idespecialidad' => $datos['procedimiento']['idespecialidad'], 
			'idatencionmedica' => $datos['idatencionmedica'],
			'idhistoria' => $datos['idhistoria'],
			'idmedicosolicitud' => $idmedico,
			'cantidad' => $datos['cantidad'], 
			'observacion' => (empty($datos['observacion']) ? NULL : $datos['observacion']), 
			'fecha_solicitud' => $fecha, 
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'idsedeempresaadmin_sp' => @$this->sessionHospital['idsedeempresaadmin']
		);
		return $this->db->insert('solicitud_procedimiento', $data);
	}
	public function m_editar_inline_solicitud_procedimiento($datos)
	{
		$data = array(
			'cantidad' => $datos['cantidad']
		);
		$this->db->where('idsolicitudprocedimiento',$datos['id']);
		return $this->db->update('solicitud_procedimiento', $data);
	}
	public function m_editar_solicitud_procedimiento($datos)
	{
		$data = array(
			'cantidad' => $datos['cantidad'],
			'observacion' => $datos['observacion']
		);
		$this->db->where('idsolicitudprocedimiento',$datos['id']);
		return $this->db->update('solicitud_procedimiento', $data);
	}
	public function m_anular_solicitud_procedimiento($id)
	{
		$data = array(
			'estado_sp' => 0 
		);
		$this->db->where('idsolicitudprocedimiento',$id); 
		if($this->db->update('solicitud_procedimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
}