<?php
class Model_asistencia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_asistencias($paramDatos,$paramPaginate){ 
		$this->db->select('asi.idasistencia, fecha, hora, diferencia_tiempo, marcado_asistencia, tipo_asistencia, 
			e.idempleado, e.nombres, e.apellido_paterno, e.apellido_materno, e.numero_documento, 
			eas.idestadoasistencia, eas.descripcion, eas.clase_css'); 
		$this->db->from('rh_asistencia asi'); 
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		// $this->db->join('empresa empr','e.idempresa = empr.idempresa'); 
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1','left'); 
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado
		// $this->db->where('e.', 1); // habilitado 
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' 
			. $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
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
	public function m_count_asistencias($paramDatos,$paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1','left'); 
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado 
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' 
			. $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	/* HUELLERO */
	public function m_listar_asistencia_temporal_sin_marcar()
	{
		$this->db->select('idasistenciatemporal, fecha_hora, numero_documento, estado_at'); 
		$this->db->from('rh_asistencia_temporal'); 
		$this->db->where('estado_at', 2); 
		return $this->db->get()->result_array(); 
	}
	public function m_actualizar_asistencia_temporal_a_marcado($id)
	{
		$data = array(
			'estado_at' => 1 
		);
		$this->db->where('idasistenciatemporal',$id);
		return $this->db->update('rh_asistencia_temporal', $data);
	}
	/* END HUELLERO */

	public function m_cargar_asistencias_de_empleado($paramDatos,$paramPaginate)
	{
		$this->db->select('asi.idasistencia, fecha, hora, diferencia_tiempo, marcado_asistencia, tipo_asistencia, idhorarioempleado, 
			e.idempleado, e.nombres, e.apellido_paterno, e.apellido_materno, e.numero_documento, asi.hora_real,
			eas.idestadoasistencia, eas.descripcion, eas.clase_css'); 
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1','left'); 
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado 
		$this->db->where('e.idempleado', $paramDatos['idempleado']);
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($paramDatos['filtros']['desde'].' '.$paramDatos['filtros']['desdeHora'].':'.$paramDatos['filtros']['desdeMinuto']) .' AND ' 
			. $this->db->escape($paramDatos['filtros']['hasta'].' '.$paramDatos['filtros']['hastaHora'].':'.$paramDatos['filtros']['hastaMinuto'])); 
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
		$this->db->order_by('hora');
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_asistencias_de_empleado($paramDatos,$paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1','left'); 
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado 
		$this->db->where('e.idempleado', $paramDatos['idempleado']);
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($paramDatos['filtros']['desde'].' '.$paramDatos['filtros']['desdeHora'].':'.$paramDatos['filtros']['desdeMinuto']) .' AND ' 
			. $this->db->escape($paramDatos['filtros']['hasta'].' '.$paramDatos['filtros']['hastaHora'].':'.$paramDatos['filtros']['hastaMinuto']));
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_cargar_asistencias_de_empleado_reporte($datos)
	{
		$this->db->select('asi.idasistencia, fecha, hora, diferencia_tiempo, marcado_asistencia, tipo_asistencia, idhorarioempleado, 
			e.idempleado, e.nombres, e.apellido_paterno, e.apellido_materno, e.numero_documento, e.fecha_nacimiento, mhe.descripcion_mh, smh.descripcion_smh,
			eas.idestadoasistencia, eas.descripcion, eas.clase_css, hora_maestra_entrada, hora_maestra_salida, tiempo_tolerancia_maestra'); 
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1','left'); 
		$this->db->join('rh_horario_especial he','asi.idhorarioespecial = he.idhorarioespecial AND estado_hesp = 1','left');
		$this->db->join('rh_motivo_he mhe','he.idmotivohe = mhe.idmotivohe','left');
		$this->db->join('rh_submotivo_he smh','he.idsubmotivohe = smh.idsubmotivohe','left');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where('estado_as', 1); // habilitado 
		if( !empty($datos['empleado']['id']) ){ 
			$this->db->where('e.idempleado', $datos['empleado']['id']);
		}
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) .' AND ' 
			. $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		
		$this->db->order_by('asi.fecha,hora','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_ranking($datos,$reporte)
	{
		$this->db->select('COUNT(*) AS contador, SUM(diferencia_tiempo) AS suma_diferencia');
		$this->db->select("e.idempleado, (e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno) AS empleado, e.codigo_asistencia, e.numero_documento, 
			(empr.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE); 
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('empresa empr','e.idempresa = empr.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1'); 
		$this->db->where('tipo_asistencia', 'E'); // 
		if( $reporte == 'puntual' ){ 
			$this->db->where('eas.idestadoasistencia', 1); // ASISTENCIA 
		}
		if( $reporte == 'tardanza' ){ 
			$this->db->where_in('eas.idestadoasistencia', array(2,3) ); // TARDANZA J I 
		}
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado 
		// var_dump("<pre>",$datos['allEmpresas']); exit(); 
		if( $datos['allEmpresas'] === FALSE ){ 
			if( !empty($datos['empresa']['id']) ){
				$this->db->where('empr.idempresa', $datos['empresa']['id']);
			}
		}
		
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) .' AND ' 
			. $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		$this->db->group_by('e.idempleado, empr.idempresa, empr.descripcion, ca.descripcion_ca');
		$this->db->order_by('COUNT(*),SUM(diferencia_tiempo)','DESC');
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_empleados_ranking_horas_extra($datos)
	{
		$this->db->select('COUNT(*) AS contador, SUM(diferencia_tiempo) AS suma_diferencia');
		$this->db->select("e.idempleado, (e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno) AS empleado, e.codigo_asistencia, e.numero_documento, 
			(empr.descripcion) AS empresa, (ca.descripcion_ca) AS cargo, nombre_foto",FALSE); 
		$this->db->from('rh_asistencia asi');
		$this->db->join('rh_empleado e','asi.idempleado = e.idempleado');
		$this->db->join('empresa empr','e.idempresa = empr.idempresa','left');
		$this->db->join('rh_cargo ca','e.idcargo = ca.idcargo','left');
		$this->db->join('rh_estado_asistencia eas','asi.idestadoasistencia = eas.idestadoasistencia AND estado_ea = 1'); 
		$this->db->where('tipo_asistencia', 'E'); // 
		$this->db->where_in('eas.idestadoasistencia', array(1,2,3) ); // PUNTUAL, TARDANZA J, I 
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_as', 1); // habilitado 
		// var_dump("<pre>",$datos['allEmpresas']); exit(); 
		if( $datos['allEmpresas'] === FALSE ){ 
			if( !empty($datos['empresa']['id']) ){ 
				$this->db->where('empr.idempresa', $datos['empresa']['id']);
			}
		}
		
		$this->db->where('asi.fecha BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) .' AND ' 
			. $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		$this->db->group_by('e.idempleado, empr.idempresa, empr.descripcion, ca.descripcion_ca');
		$this->db->order_by('SUM(diferencia_tiempo)','DESC');
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_estas_fechas_especiales_de_empleado($datos)
	{
		$this->db->select('he.idhorarioespecial,he.fecha_especial, mhe.idmotivohe, mhe.descripcion_mh, smh.descripcion_smh'); 
		$this->db->from('rh_horario_especial he');
		$this->db->join('rh_motivo_he mhe','he.idmotivohe = mhe.idmotivohe','left');
		$this->db->join('rh_submotivo_he smh','he.idsubmotivohe = smh.idsubmotivohe','left');
		$this->db->where('estado_mh', 1); // habilitado 
		$this->db->where('estado_hesp', 1);
		$this->db->where('he.idempleado', $datos['idempleado']);
		// $this->db->where('asi.fecha BETWEEN '. $this->db->escape($datos['desde'].' '.$datos['desdeHora'].':'.$datos['desdeMinuto']) .' AND ' 
		// 	. $this->db->escape($datos['hasta'].' '.$datos['hastaHora'].':'.$datos['hastaMinuto'])); 
		if( !empty($datos['arrFechasEsp']) ){
			$this->db->where_in('he.fecha_especial', $datos['arrFechasEsp']);
		}
		
		// $this->db->order_by('asi.fecha,hora','ASC');
		return $this->db->get()->result_array();
	}
	public function m_actualizar_asistencias_con_horario($datos)
	{
		$data = array(
			'idestadoasistencia' => $datos['idestadoasistencia'],
			'idhorarioempleado' => $datos['idhorarioempleado'],
			'diferencia_tiempo' => $datos['diferencia_tiempo'],
			'tipo_asistencia' => $datos['tipo_asistencia'],
			'hora_maestra_entrada' => $datos['hora_maestra_entrada'],
			'hora_maestra_salida' => $datos['hora_maestra_salida'],
			'tiempo_tolerancia_maestra' => $datos['tiempo_tolerancia_maestra'],
			'hora' => $datos['hora'],
		);
		$this->db->where('idasistencia',$datos['id']);
		return $this->db->update('rh_asistencia', $data);
	}
	public function m_actualizar_asistencias_especial_con_horario($datos)
	{
		$data = array(
			'idestadoasistencia' => $datos['idestadoasistencia'],
			'idhorarioespecial' => $datos['idhorarioespecial'],
			'diferencia_tiempo' => $datos['diferencia_tiempo'],
			'tipo_asistencia' => $datos['tipo_asistencia'],
			'hora_maestra_entrada' => $datos['hora_maestra_entrada'],
			'hora_maestra_salida' => $datos['hora_maestra_salida'],
			'tiempo_tolerancia_maestra' => $datos['tiempo_tolerancia_maestra']
		);
		$this->db->where('idasistencia',$datos['id']);
		return $this->db->update('rh_asistencia', $data);
	}
	public function m_registrar_asistencia_empleado($datos)
	{
		$data = array(
			'idempleado' => $datos['idempleado'],
			'idestadoasistencia' => $datos['idestadoasistencia'],
			'idhorarioempleado' => $datos['idhorarioempleado'],
			'idhorarioespecial' => $datos['idhorarioespecial'],
			'fecha' => $datos['fecha'],
			'hora_real' => $datos['hora_real'],
			'hora' => $datos['hora'],
			'diferencia_tiempo' => $datos['diferencia_tiempo'],
			'marcado_asistencia' => $datos['marcado_asistencia'],
			'tipo_asistencia' => $datos['tipo_asistencia'],
			'observaciones' => $datos['observaciones'],
			'codigo_asistencia' => $datos['codigo_asistencia'],
			'hora_maestra_entrada' => $datos['hora_maestra_entrada'],
			'hora_maestra_salida' => $datos['hora_maestra_salida'],
			'tiempo_tolerancia_maestra' => $datos['tiempo_tolerancia_maestra'],
			'createdAt' => date('Y-m-d H:i:s'),
			'iduser' => $datos['iduser'],
		); 
		return $this->db->insert('rh_asistencia', $data);
	}
}
?>