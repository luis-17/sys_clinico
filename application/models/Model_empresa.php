<?php
class Model_empresa extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_empresas($paramPaginate, $datos = FALSE, $tipoEmpresa = FALSE){ 
		$this->db->select('e.idempresa, e.descripcion AS empresa, e.estado_em, e.descripcion AS empresa, e.num_cuenta_detraccion, e.num_cuenta');
		$this->db->select('e.ruc_empresa, e.domicilio_fiscal, e.representante_legal, e.telefono, e.es_empresa_admin, e.descripcion_corta');
		$this->db->select('ba.idbanco, ba.descripcion_banco');
		$this->db->from('empresa e');
		$this->db->join('ct_banco ba','e.idbanco = ba.idbanco AND ba.estado_banco = 1','left');
		$this->db->where_in('estado_em', array(1,2)); // habilitado,deshabilitadp
		if($datos){
			$this->db->select('emd.idempresaadmin, emd.idempresadetalle, emd.estado_ed');
			$this->db->join('pa_empresa_detalle emd', 'e.idempresa = emd.idempresatercera AND emd.idempresaadmin = '. $datos['idempresaadmin'] .' AND emd.estado_ed <> 0'); 
		}

		if ($tipoEmpresa){
			$this->db->where('es_empresa_admin', $tipoEmpresa['id']); 
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
	public function m_count_empresas($paramPaginate, $datos = FALSE, $tipoEmpresa = FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa e');
		$this->db->join('ct_banco ba','e.idbanco = ba.idbanco AND ba.estado_banco = 1','left');
		$this->db->where_in('estado_em', array(1,2)); // habilitado,deshabilitado
		if($datos){
			$this->db->join('pa_empresa_detalle emd', 'e.idempresa = emd.idempresatercera AND emd.idempresaadmin = '. $datos['idempresaadmin'] .' AND emd.estado_ed <> 0'); 
		}
		
		if ($tipoEmpresa){
			$this->db->where('es_empresa_admin', $tipoEmpresa['id']); 
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
	public function m_cargar_especialidades_no_agregados_a_empresa($paramPaginate,$datos) // ya no se usa
	{
		// SUBCONSULTA 
		$this->db->select('c_esp.idespecialidad');
		$this->db->from('especialidad c_esp');
		$this->db->join('empresa_especialidad c_emes','c_esp.idespecialidad = c_emes.idespecialidad');
		$this->db->where('c_emes.idempresa',$datos['idempresa']);
		$this->db->where('c_esp.estado',1);
		$especialidades_agregadas = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('esp.idespecialidad, esp.nombre');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad AND estado_emes = 1','left');
		$this->db->join('empresa em','emes.idempresa = em.idempresa AND estado_em = 1 AND em.idempresa = ' . $datos['idempresa'],'left');
		$this->db->where('esp.idespecialidad NOT IN (' . $especialidades_agregadas . ')');
		$this->db->where('esp.estado', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$this->db->group_by('esp.idespecialidad');
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_especialidades_no_agregados_a_empresa($paramPaginate,$datos) // ya no se usa
	{
		// SUBCONSULTA 
		$this->db->select('c_esp.idespecialidad');
		$this->db->from('especialidad c_esp');
		$this->db->join('empresa_especialidad c_emes','c_esp.idespecialidad = c_emes.idespecialidad');
		$this->db->where('c_emes.idempresa',$datos['idempresa']);
		$this->db->where('c_esp.estado',1);
		$especialidades_agregadas = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad AND estado_emes = 1','left');
		$this->db->join('empresa em','emes.idempresa = em.idempresa AND estado_em = 1 AND em.idempresa = ' . $datos['idempresa'],'left');
		$this->db->where('esp.idespecialidad NOT IN (' . $especialidades_agregadas . ')');
		$this->db->where('esp.estado', 1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$this->db->group_by('esp.idespecialidad');
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_especialidades_no_agregados_a_empresa_autocompletado($datos)
	{
		// SUBCONSULTA 
		$this->db->select('c_esp.idespecialidad');
		$this->db->from('especialidad c_esp');
		$this->db->join('empresa_especialidad c_emes','c_esp.idespecialidad = c_emes.idespecialidad');
		$this->db->where('c_emes.idempresa',$datos['idempresa']);
		$this->db->where('c_emes.idempresadetalle',$datos['idempresadetalle']);
		$this->db->where('c_esp.estado',1);
		$this->db->where_in('c_emes.estado_emes',array(1,2));
		$especialidades_agregadas = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('esp.idespecialidad, esp.nombre');
		$this->db->from('especialidad esp');
		$this->db->where('esp.idespecialidad NOT IN (' . $especialidades_agregadas . ')');
		$this->db->where('esp.estado', 1);
		$this->db->ilike('esp.nombre', $datos['search']);
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_especialidades_empresa($paramPaginate,$datos)
	{
		$this->db->select('esp.idespecialidad, esp.nombre, emes.porcentaje, emes.idempresaespecialidad, emes.idempresa, emes.idempresadetalle, emes.estado_emes');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad');
		$this->db->where('emes.idempresa',$datos['idempresa']);
		$this->db->where('emes.idempresadetalle',$datos['idempresadetalle']);
		$this->db->where('esp.estado',1);
		$this->db->where('emes.estado_emes <>',0);		
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		// $this->db->group_by('esp.idespecialidad');
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresas_de_especialidad($datos)
	{
		$this->db->select('esp.idespecialidad, esp.nombre, emes.porcentaje, emes.idempresaespecialidad, emes.idempresadetalle, emp.idempresa, emp.descripcion AS empresa');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad');
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa');
		$this->db->where('esp.idespecialidad',(int)$datos['idespecialidad']);
		// $this->db->where('emes.idempresadetalle',$datos['idempresadetalle']);
		$this->db->where('esp.estado',1);
		$this->db->where('emp.estado_em',1);
		$this->db->where('emes.estado_emes',1);	
		return $this->db->get()->result_array();
	}
	public function m_count_especialidades_empresa($paramPaginate,$datos)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad');
		$this->db->where('emes.idempresa',$datos['idempresa']);
		$this->db->where('emes.idempresadetalle',$datos['idempresadetalle']);
		$this->db->where('esp.estado',1);
		$this->db->where('emes.estado_emes',1);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		// $this->db->group_by('esp.idespecialidad');
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_empresas_cbo($datos = FALSE){ 
		$this->db->distinct();
		$this->db->select('idempresa, descripcion, estado_em');
		$this->db->select('ruc_empresa, domicilio_fiscal, representante_legal, telefono, idbanco, num_cuenta, num_cuenta_detraccion');
		$this->db->from('empresa');
		$this->db->where('estado_em', 1); // activo
		if( $datos ){
			$this->db->ilike($datos['nameColumn'], $datos['search']);
		}else{
			$this->db->limit(100);
		}
		$this->db->order_by('descripcion');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresa_por_columna($datos){ 
		$this->db->select('idempresa, descripcion, estado_em');
		$this->db->from('empresa');
		$this->db->where_in('estado_em', array(1,2)); // activo
		$this->db->where('UPPER(CAST('. $datos['nameColumn'] . ' AS TEXT )) = ', $datos['search'] );
		if( !empty($datos['excepto']) && !empty($datos['valor_excepto']) ){ // esto se usa en el editar para que no se encuentre asi mismo
			$this->db->where('CAST('. $datos['excepto'] . ' AS TEXT ) <> ', $datos['valor_excepto'] );
		}
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_empresas_hab_deshab_cbo($datos = FALSE){ 
		$this->db->distinct();
		$this->db->select('idempresa, descripcion, estado_em, descripcion_corta');
		$this->db->select('ruc_empresa, domicilio_fiscal, representante_legal, telefono, idbanco, num_cuenta, num_cuenta_detraccion');
		$this->db->from('empresa');
		$this->db->where_in('estado_em', array(1,2)); // activo
		if($datos){
			$this->db->ilike($datos['nameColumn'], $datos['search']); 
		}
		$this->db->order_by('descripcion');
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresas_solo_admin_cbo(){ 
		// $this->db->distinct();
		$this->db->select('emp.idempresa, emp.descripcion, emp.ruc_empresa, emp.estado_em, emp.descripcion_corta, ea.regimen');
		$this->db->from('empresa emp');
		$this->db->join('empresa_admin ea', 'emp.ruc_empresa = ea.ruc');
		$this->db->where('estado_em', 1); // activo
		$this->db->where('es_empresa_admin', 1); // 1: empresa admin
		// $this->db->limit(100);

		$this->db->order_by('descripcion');
		return $this->db->get()->result_array();
	}
	public function m_cargar_esta_empresa_por_codigo($datos)
	{
		$this->db->select('e.idempresa,e.descripcion,e.ruc_empresa,e.domicilio_fiscal, e.representante_legal');
		$this->db->select('e.telefono, e.num_cuenta, e.num_cuenta_detraccion, e.es_empresa_admin, e.descripcion_corta');
		$this->db->select('ba.idbanco, ba.descripcion_banco');
		$this->db->from('empresa e');
		$this->db->join('ct_banco ba','e.idbanco = ba.idbanco AND ba.estado_banco = 1','left');
		$this->db->where('idempresa', $datos['id']);
		$this->db->where('estado_em', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_esta_empresa_por_ruc($datos)
	{
		$this->db->select('idempresa,descripcion');
		$this->db->from('empresa');
		$this->db->where('ruc_empresa', $datos['ruc']);
		$this->db->where('estado_em', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_validar_empresa_sede($datos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa e');
		$this->db->join('sede s','e.idsede = s.idsede AND estado_se = 1','left');
		$this->db->where('e.idempresa', $datos['idempresa']);
		$this->db->where('s.idsede', $datos['idsede']);
		$this->db->where('estado_em', 1);
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? TRUE : FALSE );
	}
	public function m_validar_empresa_especialidad($datos)
	{
		$this->db->select('idempresaespecialidad, porcentaje, productos_tercero',FALSE);
		$this->db->from('empresa_especialidad ee');
		// $this->db->join('especialidad esp','e.idsede = s.idsede AND estado_se = 1','left');
		$this->db->where('idempresa', $datos['idempresa']);
		$this->db->where('idespecialidad', $datos['idespecialidad']);
		$this->db->where('idempresadetalle', $datos['idempresadetalle']);
		$this->db->where_in('estado_emes', array(1,2)); // busca si la relacion empresa-especialidad ya existe (habilitada o deshabilitada)
		$this->db->limit(1);
		$fData = $this->db->get()->row_array();
		return ( empty($fData) ? FALSE : $fData );
	}
	public function m_validar_si_hay_atencion_medica_con_empresa_especialidad($datos)
	{
		$this->db->select('idventa',FALSE);
		$this->db->from('venta');
		$this->db->where('idempresaespecialidad', $datos['idempresaespecialidad']);
		$this->db->limit(1);
		$fData = $this->db->get()->row_array();
		return ( empty($fData) ? FALSE : TRUE );
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper_total($datos['empresa']),
			'descripcion_corta' => empty($datos['nombre_corto'])? NULL : strtoupper_total($datos['nombre_corto']),
			'ruc_empresa' => $datos['ruc_empresa'],
			'domicilio_fiscal' => $datos['domicilio_fiscal'],
			'representante_legal' => $datos['representante_legal'],
			'telefono' => $datos['telefono'],
			'idbanco' => empty($datos['banco']['id'])? NULL : $datos['banco']['id'],
			'num_cuenta_detraccion' => empty($datos['cuenta_detraccion'])? NULL : $datos['cuenta_detraccion'],
			'num_cuenta' => empty($datos['cuenta'])? NULL : $datos['cuenta'],
			'es_empresa_admin' => $datos['es_empresa_admin'],
			//'tiene_contrato' => $datos['tiene_contrato'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idempresa',$datos['idempresa']);
		return $this->db->update('empresa', $data);
	}
	public function m_registrar($datos)
	{
		return $this->db->insert('empresa', $datos);
	}

	public function m_agregar_especialidad_empresa($datos)
	{
		$data = array(
			'idempresa' => $datos['idempresa'],
			'idespecialidad' => $datos['idespecialidad'],
			'idempresadetalle' => $datos['idempresadetalle'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'iduser_asigna' => $this->sessionHospital['idusers'],
		);
		return $this->db->insert('empresa_especialidad', $data);
	}
	public function m_quitar_todo_medico_empresa_especialidad($id)
	{
		$data = array(
			'estado_emme' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresaespecialidad',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar_todo_medico_empresa_especialidad($id)
	{
		$data = array(
			'estado_emme' => 2,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresaespecialidad',$id);
		if($this->db->update('empresa_medico', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_quitar_especialidad_empresa($id)
	{
		$data = array(
			'estado_emes' => 0,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresaespecialidad',$id);
		if($this->db->update('empresa_especialidad', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabiitar_especialidad_empresa($id)
	{
		$data = array(
			'estado_emes' => 2,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresaespecialidad',$id);
		if($this->db->update('empresa_especialidad', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar_especialidad_empresa($id)
	{
		$data = array(
			'estado_emes' => 1,
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresaespecialidad',$id);
		if($this->db->update('empresa_especialidad', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular($idSede, $idEmpresa)
	{
		$data = array(
			'estado_em' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idempresa',$idEmpresa);
		$this->db->where('idsede',$idSede);
		if($this->db->update('empresa', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_editar_porcentaje($datos)
	{
		$data = array(
			'porcentaje' => $datos['porcentaje'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idempresaespecialidad',$datos['idempresaespecialidad']);
		if($this->db->update('empresa_especialidad', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_cargar_empresas_admin($datos){ 
		$this->db->select('e.idempresa, e.descripcion AS empresa, e.ruc_empresa, e.es_empresa_admin');
		$this->db->from('empresa e');
		$this->db->where('e.estado_em', 1); // activo
		$this->db->where('e.es_empresa_admin', 1); // si es empresa admin
		$this->db->where('e.ruc_empresa', $datos['ruc']); // es empresa en session
				
		return $this->db->get()->result_array();
	}


	public function m_validar_empresa_empresaadmin($empresaAdmin, $empresa){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_empresa_detalle emd');
		$this->db->where('emd.idempresaadmin', $empresaAdmin);
		$this->db->where('emd.idempresatercera', $empresa);
		$this->db->where_in('emd.estado_ed', array(1,2));
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? TRUE : FALSE );
	}

	public function m_registrar_empresa_det ($datos){
		return $this->db->insert('pa_empresa_detalle', $datos);
	}

	public function m_editar_empresa_det($datos){
		$data = array(
			'idempresaadmin' => $datos['empresaAdmin']['idempresa'],
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresadetalle',$datos['idempresadetalle']);
		return $this->db->update('pa_empresa_detalle', $data);
	}

	public function m_cambiar_estado($datos){
		$data = array(
			'estado_em' => $datos['nuevo_estado'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idempresa',$datos['idempresa']);
		if($this->db->update('empresa', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_es_empresaadmin($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_empresa_detalle emd');
		$this->db->where('emd.idempresaadmin', $datos['idempresa']);
		$this->db->where('emd.estado_ed', 1);
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? FALSE : TRUE );
	}

	public function m_cambiar_estado_empresa_det($datos){
		$data = array(
			'estado_ed' => $datos['nuevo_estado'],
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresadetalle',$datos['idempresadetalle']);
		if($this->db->update('pa_empresa_detalle', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_cambiar_estado_relaciones_empresa_det($datos){
		$data = array(
			'estado_ed' => $datos['nuevo_estado'],
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresatercera',$datos['idempresa']);
		if($this->db->update('pa_empresa_detalle', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_empresa_tiene_especialidades($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa_especialidad emes');
		$this->db->where('emes.idempresadetalle', $datos['idempresadetalle']);
		$this->db->where('emes.estado_emes', 1);
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? FALSE : TRUE );
	}


	public function m_especialidad_tiene_medico($datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('empresa_medico emme');
		$this->db->where('emme.idempresaespecialidad', $datos['idempresaespecialidad']);
		$this->db->where('emme.estado_emme', 1);
		$fData = $this->db->get()->row_array();
		return (empty($fData['contador']) ? FALSE : TRUE );
	}

	public function m_cambiar_estado_contrato($datos){
		$data = array(
			'tiene_contrato' => $datos['nuevo_estado'],
			'updatedAt' => date('Y-m-d H:i:s'),
		);
		$this->db->where('idempresadetalle',$datos['idempresadetalle']);
		return($this->db->update('pa_empresa_detalle', $data));
	}
	/* REPORTE ESPECIALIDADES Y MEDICOS EN EMAS */
	public function m_cargar_especialidad_medico_por_ema($datos)
	{
		/*
			SELECT ea.idempresa id_ea, ea.descripcion AS empresa_admin, ed.estado_ed, et.idempresa id_ema, et.descripcion AS ema
				, emes.estado_emes, esp.idespecialidad, esp.nombre AS especialidad
				, emme.estado_emme, med.idmedico, concat_ws (' ',med_nombres, med_apellido_paterno, med_apellido_materno) AS medico
				, med.colegiatura_profesional cmp, me.reg_nacional_esp rne, sac.descripcion_sac situacion_academica
				, empl.si_activo
			FROM pa_empresa_detalle ed
			JOIN empresa ea ON ed.idempresaadmin = ea.idempresa
			JOIN empresa et ON ed.idempresatercera = et.idempresa
			JOIN empresa_especialidad emes ON ed.idempresadetalle = emes.idempresadetalle
			--JOIN empresa_especialidad emes ON et.idempresa = emes.idempresa 
			JOIN especialidad esp ON emes.idespecialidad = esp.idespecialidad
			LEFT JOIN empresa_medico emme ON emes.idempresaespecialidad = emme.idempresaespecialidad AND emme.estado_emme IN (1,2)
			LEFT JOIN medico med ON emme.idmedico = med.idmedico
			LEFT JOIN rh_empleado empl ON med.idempleado = empl.idempleado AND empl.estado_empl = 1
			LEFT JOIN pa_medico_especialidad me ON med.idmedico = me.idmedico AND emes.idespecialidad = me.idespecialidad
			LEFT JOIN pa_situacion_academica sac ON me.idsituacionacademica = sac.idsituacionacademica
			WHERE ea.estado_em IN (1)
				AND ed.estado_ed IN (1,2)
				AND ea.es_empresa_admin = 1
				AND emes.estado_emes IN (1,2)

			ORDER BY ea.descripcion, et.descripcion
		*/
		$this->db->select('ea.idempresa AS id_ea, ea.descripcion AS empresa_admin, ed.estado_ed, et.idempresa AS id_ema, et.descripcion AS ema');
		$this->db->select('emes.estado_emes, esp.idespecialidad, esp.nombre AS especialidad');
		$this->db->select("emme.estado_emme, med.idmedico, concat_ws (' ',med_nombres, med_apellido_paterno, med_apellido_materno) AS medico",FALSE);
		$this->db->select('med.colegiatura_profesional cmp, me.reg_nacional_esp rne, sac.descripcion_sac situacion_academica, empl.si_activo');
		$this->db->from('pa_empresa_detalle ed');
		$this->db->join('empresa ea', 'ed.idempresaadmin = ea.idempresa');
		$this->db->join('empresa et', 'ed.idempresatercera = et.idempresa');
		$this->db->join('empresa_especialidad emes', 'ed.idempresadetalle = emes.idempresadetalle');
		$this->db->join('especialidad esp', 'emes.idespecialidad = esp.idespecialidad');
		$this->db->join('empresa_medico emme', 'emes.idempresaespecialidad = emme.idempresaespecialidad AND emme.estado_emme IN (1,2)','left');
		$this->db->join('medico med', 'emme.idmedico = med.idmedico','left');
		$this->db->join('rh_empleado empl', 'med.idempleado = empl.idempleado AND empl.estado_empl = 1','left');
		$this->db->join('pa_medico_especialidad me', 'med.idmedico = me.idmedico AND emes.idespecialidad = me.idespecialidad','left');
		$this->db->join('pa_situacion_academica sac', 'me.idsituacionacademica = sac.idsituacionacademica','left');
		$this->db->where('ea.estado_em', 1);
		$this->db->where('ea.es_empresa_admin', 1);
		$this->db->where_in('ed.estado_ed', array(1,2));
		$this->db->where_in('emes.estado_emes', array(1,2));
		$this->db->order_by('ea.descripcion', 'ASC');
		$this->db->order_by('et.descripcion', 'ASC');

		return $this->db->get()->result_array();
	}
}