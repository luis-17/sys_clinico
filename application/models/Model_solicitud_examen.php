<?php 
class Model_solicitud_examen extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_solicitud_examenes_paciente($paramPaginate,$paramDatos){
		$this->db->select('idsolicitud');
		$this->db->from('detalle d');
		$this->db->join('venta v', 'd.idventa = v.idventa AND v.estado = 1');
		$this->db->join('historia h', 'v.idcliente = h.idcliente AND h.idhistoria = ' . $paramDatos['idhistoria']);
		$this->db->where('d.tiposolicitud', 1); // examen auxiliar
		$sqlSolicitudesVendidas = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select('se.idsolicitudexamen AS id');
		$this->db->select('se.idsolicitudexamen, se.idatencionmedica, fecha_realizacion, fecha_solicitud, h.idhistoria, pm.idproductomaster, 
			(pm.descripcion) AS producto, pps.precio_sede AS precio, indicaciones, estado_sex, tp.idtipoproducto, tp.nombre_tp, es.nombre, es.idespecialidad'); 
		$this->db->from('solicitud_examen se'); 
		$this->db->join('historia h','se.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','se.idproductomaster = pm.idproductomaster');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND pps.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('especialidad es','se.idespecialidad = es.idespecialidad'); 
		// $this->db->join('especialidad es','pm.idespecialidad = es.idespecialidad'); /* cualquiera de los dos vale */ 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("se".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));
		}
		$this->db->where('estado_pps', 1);
		$this->db->where('h.idhistoria', $paramDatos['idhistoria']);
		$this->db->where('se.idsolicitudexamen NOT IN ('. $sqlSolicitudesVendidas . ')');
		if (@$paramDatos['atendido'] === 'no') { 
			$this->db->where('se.estado_sex', 1); 	// SOLICITADO 
		}else{
			$this->db->where('estado_sex <>', 0); 
		}
		if (@$paramDatos['tipoExamen'] === 'I') { 
			$this->db->where('pm.idtipoproducto', 14); 	// 14 : IMAGENOLOGIA 
		} elseif (@$paramDatos['tipoExamen'] === 'PC') { 
			$this->db->where('pm.idtipoproducto', 15); 	// 15 : LABORATORIO 
		} elseif (@$paramDatos['tipoExamen'] === 'AP') { 
			$this->db->where('pm.idtipoproducto', 11); 	// 11 : ANATOMIA PATOLOGICA 
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
	public function m_count_solicitud_examenes_paciente($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('solicitud_examen se'); 
		$this->db->join('historia h','se.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','se.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad es','se.idespecialidad = es.idespecialidad'); 
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("se".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		}
		$this->db->where('estado_sex <>', 0); 
		$this->db->where('h.idhistoria', $paramDatos['idhistoria']); 
		if (@$paramDatos['atendido'] === 'no') { 
			$this->db->where('se.estado_sex', 1); 	// SOLICITADO 
		}else{
			$this->db->where('estado_sex <>', 0); 
		}

		if (@$paramDatos['tipoExamen'] === 'I') { 
			$this->db->where('pm.idtipoproducto', 14); 	// 14 : IMAGENOLOGIA 
		} elseif (@$paramDatos['tipoExamen'] === 'PC') { 
			$this->db->where('pm.idtipoproducto', 15); 	// 15 : LABORATORIO
		} elseif (@$paramDatos['tipoExamen'] === 'AP') { 
			$this->db->where('pm.idtipoproducto', 11); 	// 11 : ANATOMIA PATOLOGICA 
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
	public function m_cargar_solicitudes_examen_session($paramPaginate,$paramDatos){ 
		$this->db->select('sex.idsolicitudexamen, sex.indicaciones, sex.fecha_solicitud, 
			h.idhistoria, sex.idatencionmedica, pm.idproductomaster, (descripcion) AS producto, estado_sex,
			tp.idtipoproducto, tp.nombre_tp, es.nombre AS especialidad, es.idespecialidad, idmedicosolicitud');
		$this->db->select('cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno');
		$this->db->select('med.idmedico, med.med_nombres, med.med_apellido_paterno, med.med_apellido_materno');
		$this->db->select('d.paciente_atendido_det, fecha_atencion_det');

		$this->db->from('solicitud_examen sex'); 
		$this->db->join('historia h','sex.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sex.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad es','sex.idespecialidad = es.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('cliente cl', 'h.idcliente = cl.idcliente');
		$this->db->join('medico med', 'med.idmedico = sex.idmedicosolicitud');
		$this->db->join('detalle d', 'sex.idsolicitudexamen = d.idsolicitud AND d.tiposolicitud = 1','left'); // 1: examen aux

		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){
			$this->db->where('DATE("sex".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta']));
		}
		if ( $this->sessionHospital['key_group'] == 'key_salud' ) { 
			$this->db->where('sex.idespecialidad', $this->sessionHospital['idespecialidad']);
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
	public function m_count_solicitudes_examen_session($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('solicitud_examen sex'); 
		$this->db->join('historia h','sex.idhistoria = h.idhistoria'); 
		$this->db->join('producto_master pm','sex.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad es','sex.idespecialidad = es.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('cliente cl', 'h.idcliente = cl.idcliente');
		$this->db->join('medico med', 'med.idmedico = sex.idmedicosolicitud');
		// $this->db->join('detalle d', 'sex.idsolicitudexamen = d.idsolicitud AND d.tiposolicitud = 1','left');
		if( !empty($paramDatos['desde']) && !empty($paramDatos['hasta']) ){ 
			$this->db->where('DATE("sex".fecha_solicitud) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		}
		if ( $this->sessionHospital['key_group'] == 'key_salud' ) { 
			$this->db->where('sex.idespecialidad', $this->sessionHospital['idespecialidad']);
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
	public function m_registrar_solicitud_examen_auxiliar($datos)
	{
		if( $this->sessionHospital['key_group'] == 'key_sistemas' ){
			$idmedico = $datos['idmedico'];
			$fecha = $datos['fecha_solicitud'] . ' ' . date('H:i:s');
		}else{
			$idmedico = @$this->sessionHospital['idmedico'];
			$fecha = date('Y-m-d H:i:s');
		}
		$data = array(
			'idatencionmedica' => $datos['idatencionmedica'], 
			'idproductomaster' => $datos['examen_auxiliar']['id'], 
			'idespecialidad' => $datos['examen_auxiliar']['idespecialidad'], 
			'idhistoria' => $datos['idhistoria'], 
			'idmedicosolicitud' => $idmedico,
			'indicaciones' => $datos['indicaciones'], 
			'fecha_solicitud' => $fecha, 
			'fecha_realizacion' => NULL, 
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'), 
			'idsedeempresaadmin_se' => @$this->sessionHospital['idsedeempresaadmin']
		);
		return $this->db->insert('solicitud_examen', $data);
	}
	public function m_cargar_examen_auxiliar_de_especialidad_session_autocomplete($searchColumn, $searchText, $tipoProducto, $especialidad = FALSE)
	{
		$this->db->select('idproductomaster, descripcion, p.idespecialidad'); 
		$this->db->from('producto_master p'); 
		$this->db->where('estado_pm', 1); 
		$this->db->where('idtipoproducto', $tipoProducto); 
		if (!empty($especialidad)) {
			$this->db->where('p.idespecialidad', $especialidad['id']); // solo procedimientos de una especialidad especifica 
		}
		// LOS EXAMENES AUXILIARES PUEDEN SER DE DIFERENTES ESPECIALIDADES 
		$this->db->ilike($searchColumn, $searchText); 
		$this->db->order_by('LENGTH(descripcion)','ASC');
		$this->db->limit(8); 
		return $this->db->get()->result_array(); 
	}
	public function m_anular_solicitud_examen_auxiliar($id)
	{
		$data = array(
			'estado_sex' => 0 
		);
		$this->db->where('idsolicitudexamen',$id); 
		if($this->db->update('solicitud_examen', $data)){
			return true;
		}else{
			return false;
		}
	}
}