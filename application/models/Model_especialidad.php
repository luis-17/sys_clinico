<?php
class Model_especialidad extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_especialidades_bloqueadas_dia($paramPaginate){ 
		$this->db->select('e.idespecialidad, e.nombre especialidad, nro_consultorio');
		// $this->db->select('te.idtipoespecialidad, te.descripcion tipoespecialidad');
		$this->db->from('especialidad e');
		// $this->db->join('tipo_especialidad te','e.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		$this->db->where('estado', 1); // activo 
		$this->db->where('atencion_dia', 1);
		$this->db->order_by('e.nombre');
		return $this->db->get()->result_array();
	}
	public function m_cargar_especialidades($paramPaginate=FALSE){ 
		$this->db->select('idespecialidad, te.idtipoespecialidad, descripcion, nombre, atencion_dia, dias_libres');
		$this->db->from('especialidad e');
		$this->db->join('tipo_especialidad te','e.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		$this->db->where('estado', 1); // activo
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
			$this->db->order_by('nombre','ASC');
		}
		
		return $this->db->get()->result_array();
	}
	public function m_count_especialidades()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('especialidad e');
		$this->db->join('tipo_especialidad te','e.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		$this->db->where('estado', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_especialidades_busqueda($paramPaginate)
	{
		$this->db->select('esp.idespecialidad, (esp.nombre) AS especialidad, te.idtipoespecialidad, te.descripcion');
		$this->db->from('especialidad esp');
		$this->db->join('tipo_especialidad te','esp.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		// $this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad ');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		// $this->db->join('sede s','emp.idsede = s.idsede');
		// $this->db->where('estado_emes', 1); // empresa_especialidad 
		// $this->db->where('estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		if( $paramPaginate['search'] ){ 
			$this->db->like('LOWER('.$paramPaginate['searchColumn'].')', strtolower($paramPaginate['searchText']));
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_especialidades_busqueda()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('especialidad esp');
		$this->db->join('tipo_especialidad te','esp.idtipoespecialidad = te.idtipoespecialidad AND estado_te = 1','left');
		// $this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idempresaespecialidad ');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		// $this->db->join('sede s','emp.idsede = s.idsede');
		// $this->db->where('estado_emes', 1); // empresa_especialidad 
		// $this->db->where('estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_especialidades_sedes_empresas_de_session($datos=FALSE)
	{ 
		$this->db->distinct();
		$this->db->select('esp.idespecialidad, esp.nombre AS especialidad');
		$this->db->select('emes.idempresaespecialidad');
		$this->db->select("seesp.tiene_prog_cita, seesp.tiene_venta_prog_cita, seesp.tiene_prog_proc, seesp.tiene_venta_prog_proc"); //tiene_prog_cita 
		// $this->db->select('MAX(seesp.tiene_prog_cita) AS tiene_prog_cita ');
		$this->db->from('empresa emp');
		$this->db->join('pa_empresa_detalle ed', 'emp.idempresa = ed.idempresaadmin');
		$this->db->join('empresa e_terc', 'ed.idempresatercera = e_terc.idempresa');
		$this->db->join('empresa_especialidad emes', 'ed.idempresadetalle = emes.idempresadetalle');
		// $this->db->join('pa_sede_especialidad seesp','esp.idespecialidad = seesp.idespecialidad');
		$this->db->join('especialidad esp', 'emes.idespecialidad = esp.idespecialidad');
		$this->db->join('pa_sede_especialidad seesp', 'esp.idespecialidad = seesp.idespecialidad AND seesp.idsede = '.$this->sessionHospital['idsede'], 'left');
		$this->db->where('emp.ruc_empresa', $this->sessionHospital['ruc']);
		$this->db->where('estado_ed', 1);
		$this->db->where('estado_emes', 1); // empresa_especialidad 
		$this->db->where('emp.estado_em', 1); // empresa 
		$this->db->where('e_terc.estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad
		if( $datos ){ 
			$this->db->ilike('esp.nombre', $datos['search']);
		}
		$this->db->limit(5);
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_especialidades_por_empresa_admin_cbo($datos=FALSE)
	{
		$this->db->select('esp.idespecialidad, (esp.nombre) AS especialidad', FALSE);
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad ');

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa');
		$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa');

		if($datos){
			$this->db->where('ead.ruc_empresa', $datos['ruc']); // 
		}else{
			if($this->sessionHospital['key_group'] == 'key_dir_salud' || $this->sessionHospital['key_group'] == 'key_salud' ){
				$this->db->where('ead.ruc_empresa', $this->sessionHospital['ruc_empresa_admin']);
			}else{
				$this->db->where('ead.ruc_empresa', $this->sessionHospital['ruc']);
			}
		}
		//$this->db->join('sede s','emp.idsede = s.idsede');
		//$this->db->where('emes.idempresa ', $this->sessionHospital['idempresa']);
		// $this->db->where('emes.idsede ', $this->sessionHospital['idsede']);
		$this->db->where_in('estado_emes', array(1,2)); // empresa_especialidad 
		$this->db->where('ema.estado_em', 1); // empresa 
		$this->db->where('ead.estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		$this->db->group_by('esp.idespecialidad'); // SE AGRUPA PORQUE NO QUEREMOS QUE SALGAN DUPLICADOS DE EMPRESA/ESPECIALIDAD, QUE ESCOJA EL PRIMERO QUE SALE
		$this->db->order_by('especialidad');
		return $this->db->get()->result_array();
	}

	
	public function m_cargar_empresa_especialidades_con_restricciones($datos=FALSE) 
	{
		$this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, (ead.idempresa) AS id_empresa_admin, (ead.descripcion) AS empresa_admin', FALSE);
		$this->db->select('ema.idempresa,ema.descripcion AS empresa');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad');

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa');
		$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa');

		if($datos){
			$this->db->where('ead.idempresa', $datos['id']); // 
		}else{
			if( empty($this->sessionHospital['idempresaespecialidad']) ){ // SI NO ES USUARIO SALUD 
				// NO FILTRAMOS NADA 
				$this->db->where('ead.ruc_empresa', $this->sessionHospital['ruc']);
			}else{
				if($this->sessionHospital['key_group'] == 'key_salud' || $this->sessionHospital['key_group'] == 'key_lab' ){ 
					// $this->db->join('empresa_medico emme','emes.idempresaespecialidad = emme.idempresaespecialidad AND emes.idsede = emme.idsede AND emes.idempresa = emme.idempresa'); 
					$this->db->join('empresa_medico emme','emes.idempresaespecialidad = emme.idempresaespecialidad AND emes.idempresa = emme.idempresa'); 
					$this->db->where('emme.idmedico', $this->sessionHospital['idmedico']);
					$this->db->where_in('emme.estado_emme', array(1,2)); // empresa_medico 
				}elseif($this->sessionHospital['key_group'] == 'key_dir_esp'){ 
					$this->db->where('emes.idempresa', $this->sessionHospital['idempresa']);
				}elseif($this->sessionHospital['key_group'] == 'key_dir_salud'){
					// NO FILTRAMOS NADA 
				}elseif($this->sessionHospital['key_group'] == 'key_coord_salud'){ 
					// NO FILTRAMOS NADA 
				}
				$this->db->where('ead.idempresa', $this->sessionHospital['id_empresa_admin']);
			}
		}
		
		$this->db->where_in('estado_emes', array(1,2)); // empresa_especialidad 
		$this->db->where_in('estado_ed', array(1,2)); // empresa_detalle
		$this->db->where_in('ead.estado_em', array(1,2)); // empresa 
		$this->db->where_in('ema.estado_em', array(1,2)); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		
		$this->db->order_by('esp.nombre', 'ASC'); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_cargar_empresa_especialidades_sin_session($datos = FALSE)
	{
		$this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, (ead.idempresa) AS id_empresa_admin, (ead.descripcion) AS empresa_admin', FALSE);
		$this->db->select('ema.idempresa,ema.descripcion AS empresa');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad');

		$this->db->join('pa_empresa_detalle ed','emes.idempresadetalle = ed.idempresadetalle');
		$this->db->join('empresa ead','ed.idempresaadmin = ead.idempresa');
		$this->db->join('empresa ema','ed.idempresatercera = ema.idempresa');

		//if( empty($this->sessionHospital['idempresaespecialidad']) ){ // SI NO ES USUARIO SALUD 
			// NO FILTRAMOS NADA 
		// $this->db->where('ead.ruc_empresa', $this->sessionHospital['ruc']);
		//}
		if($datos){
			$this->db->where('ead.idempresa', $datos['id']); // 
		}
		
		$this->db->where_in('estado_emes', array(1,2)); // empresa_especialidad 
		$this->db->where_in('estado_ed', array(1,2)); // empresa_detalle
		$this->db->where_in('ead.estado_em', array(1,2)); // empresa 
		$this->db->where_in('ema.estado_em', array(1,2)); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		
		$this->db->order_by('esp.nombre', 1); // especialidad 
		return $this->db->get()->result_array();
	}
	public function m_cargar_especialidades_por_autocompletado($datos=FALSE)
	{
		
		$this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa, esp.nombre');
		// $this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, s.idsede, (s.descripcion) AS sede, emp.idempresa, (emp.descripcion) AS empresa, esp.nombre');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad ');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa');
		// $this->db->join('sede s','emp.idsede = s.idsede');
		$this->db->where('estado_emes', 1); // empresa_especialidad 
		$this->db->where('estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		if( $datos ){ 
			$this->db->ilike('nombre', $datos['search']);
		}
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_solo_especialidades_por_autocompletado($datos=FALSE)
	{
		$this->db->select('esp.idespecialidad, (esp.nombre) AS especialidad');
		$this->db->from('especialidad esp');
		$this->db->where('esp.estado', 1); // especialidad 
		if( $datos ){ 
			$this->db->ilike('esp.nombre', $datos['search']);
		}
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_especialidades_por_combo()
	{
		
		$this->db->select('emes.idempresaespecialidad, esp.idespecialidad, (esp.nombre) AS especialidad, emp.idempresa, (emp.descripcion) AS empresa, esp.nombre');
		$this->db->from('especialidad esp');
		$this->db->join('empresa_especialidad emes','esp.idespecialidad = emes.idespecialidad ');
		// $this->db->join('empresa emp','emes.idempresa = emp.idempresa AND emes.idsede = emp.idsede');
		$this->db->join('empresa emp','emes.idempresa = emp.idempresa');
		// $this->db->join('sede s','emp.idsede = s.idsede');
		$this->db->where('estado_emes', 1); // empresa_especialidad 
		$this->db->where('estado_em', 1); // empresa 
		$this->db->where('esp.estado', 1); // especialidad 
		$this->db->order_by('esp.nombre');
		return $this->db->get()->result_array();
	}

	// MANTENIMIENTO
	public function m_editar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'dias_libres' => strtoupper($datos['dias_libres']),
			'idtipoespecialidad' => $datos['idtipoespecialidad'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idespecialidad',$datos['id']);
		return $this->db->update('especialidad', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre' => strtoupper($datos['nombre']),
			'dias_libres' => strtoupper($datos['dias_libres']),
			'idtipoespecialidad' => $datos['idtipoespecialidad'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('especialidad', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado' => 0
		);
		$this->db->where('idespecialidad',$id);
		if($this->db->update('especialidad', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_cambiar_atencion_dia($datos){
		if($datos['atencion_dia'] == 1){
			$data = array(
				'atencion_dia' => 2,
			);
		}else{
			$data = array(
				'atencion_dia' => 1,
			);
		}
		$this->db->where('idespecialidad',$datos['id']);
		return $this->db->update('especialidad', $data);
	}
	// PROGRAMACION ASISTENCIAL
	public function m_cargar_especialidades_con_programacion()
	{
		$this->db->distinct(); 
		$this->db->select('esp.idespecialidad, (esp.nombre) AS especialidad', FALSE);
		$this->db->from('especialidad esp');
		$this->db->join('pa_sede_especialidad ses','esp.idespecialidad = ses.idespecialidad ');
		$this->db->where('esp.estado', 1); // activo 
		$this->db->where('ses.estado_sees', 1); // activo 
		$this->db->where('ses.tiene_prog_cita', 1); // activo 
		$this->db->order_by('especialidad');
		return $this->db->get()->result_array();
	}
	public function m_cargar_demanda_especialidad_sede($paramPaginate, $datos) {
		//query 1
		$this->db->select('sees.idsedeespecialidad AS idsedeespecialidad, sees.idsede AS sede, (se.descripcion) AS descripcion_sede, sees.idespecialidad AS idespecialidad, (esp.nombre) AS nombre_esp, sees.demanda AS demanda, 
			sees.estado_sees AS estado_sees, sees.tiene_prog_cita, sees.tiene_venta_prog_cita, sees.tiene_prog_proc, sees.tiene_venta_prog_proc', FALSE);
		$this->db->from('pa_sede_especialidad sees');
		$this->db->join('especialidad esp','sees.idespecialidad = esp.idespecialidad');
		$this->db->join('sede se','sees.idsede = se.idsede');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);
		//$this->db->join('sede_empresa_admin sea','se.idsede = sea.idsede');
		//$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);

		$this->db->where('se.estado_se', 1); // sede
		$this->db->where('esp.estado', 1); // especialidad 
		$this->db->where('sees.estado_sees', 1); // estatus demanda 

		$subQuery1 = $this->db->get_compiled_select();

	
		//query 2
		$this->db->select("null AS idsedeespecialidad, se.idsede AS sede, (se.descripcion) AS descripcion_sede, esp.idespecialidad AS idespecialidad, (esp.nombre) AS nombre_esp, ('N') AS demanda, 
			(1) AS estado_sees, (2) AS tiene_prog_cita, (2) AS tiene_venta_prog_cita,
			(2) AS tiene_prog_proc, (2) AS tiene_venta_prog_proc", FALSE);
		$this->db->from('especialidad esp, sede se');
		//$this->db->join('sede_empresa_admin sea','se.idsede = sea.idsede');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);
		// $this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('NOT EXISTS (SELECT 1 FROM pa_sede_especialidad sees WHERE sees.idespecialidad = esp.idespecialidad and sees.idsede = se.idsede)', '', FALSE);
				
		$this->db->where('se.estado_se', 1); // sede
		$this->db->where('esp.estado', 1); // especialidad 
		$subQuery2 = $this->db->get_compiled_select();

		//union
		$this->db->select("a.idsedeespecialidad, a.sede AS idsede, a.descripcion_sede, a.idespecialidad, a.nombre_esp, a.demanda, a.estado_sees, a.tiene_prog_cita, a.tiene_venta_prog_cita, a.tiene_prog_proc, a.tiene_venta_prog_proc", FALSE);
		$this->db->from("($subQuery1 UNION $subQuery2) AS a");
		$this->db->where('a.estado_sees', 1);
		if( isset($datos['demanda']) && $datos['demanda'] != '0' ){
			$this->db->where('a.demanda', $datos['demanda']);
		}

		//$this->db->order_by('a.idsedeespecialidad','DESC');

		if( isset($paramPaginate['search']) && $paramPaginate['search'] ){
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
	public function m_count_demanda_especialidad_sede($paramPaginate, $datos){
		//query 1
		$this->db->select('sees.idsedeespecialidad AS idsedeespecialidad, sees.idsede AS sede, (se.descripcion) AS descripcion_sede, sees.idespecialidad AS idespecialidad, (esp.nombre) AS nombre_esp, sees.demanda AS demanda, sees.estado_sees AS estado_sees', FALSE);
		$this->db->from('pa_sede_especialidad sees');
		$this->db->join('especialidad esp','sees.idespecialidad = esp.idespecialidad');
		$this->db->join('sede se','sees.idsede = se.idsede');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);
		//$this->db->join('sede_empresa_admin sea','se.idsede = sea.idsede');
		//$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);

		$this->db->where('se.estado_se', 1); // sede
		$this->db->where('esp.estado', 1); // especialidad 
		$this->db->where('sees.estado_sees', 1); // estatus demanda 
		$subQuery1 = $this->db->get_compiled_select();

	
		//query 2
		$this->db->select("null AS idsedeespecialidad, se.idsede AS sede, (se.descripcion) AS descripcion_sede, esp.idespecialidad AS idespecialidad, (esp.nombre) AS nombre_esp, ('N') AS demanda, (1) AS estado_sees", FALSE);
		$this->db->from('especialidad esp, sede se');
		$this->db->join('sede_empresa_admin sea','se.idsede = sea.idsede');
		$this->db->where('se.idsede', $this->sessionHospital['idsede']);
		// $this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->where('NOT EXISTS (SELECT 1 FROM pa_sede_especialidad sees WHERE sees.idespecialidad = esp.idespecialidad and sees.idsede = se.idsede)', '', FALSE);
				
		$this->db->where('se.estado_se', 1); // sede
		$this->db->where('esp.estado', 1); // especialidad 
		$subQuery2 = $this->db->get_compiled_select();

		//union
		$this->db->select("COUNT(*) AS contador", FALSE);$this->db->from("($subQuery1 UNION $subQuery2) AS a");
		$this->db->where('a.estado_sees', 1);
		if( isset($datos['demanda']) && $datos['demanda'] != '0' ){
			$this->db->where('a.demanda', $datos['demanda']);
		}

		if( isset($paramPaginate['search']) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar_demanda_en_grid($datos){
		$result = true;
		if($datos['column'] == 'demanda'){
			if($datos['id'] == null){
				$data = array(
					'demanda' => strtoupper($datos['demanda']),
					'idespecialidad' => $datos['idespecialidad'],
					'idsede' => $datos['idsede']			
				);	
				$result = $this->db->insert('pa_sede_especialidad', $data);		
			}else{
				$data = array(
					'demanda' => strtoupper($datos['demanda']),
				);
				$this->db->where('idsedeespecialidad',$datos['id']);
				$result = $this->db->update('pa_sede_especialidad', $data);
			}			
		}		

		return $result;
	}
	public function m_editar_prog_asistencial_especialidad_sede($datos){
		$result = true;
		$datos['tiene_prog_cita']['value'] = ($datos['tiene_prog_cita']['value'] == 1) ? 2:1;
		if($datos['id'] == null){
			$data = array(
				'tiene_prog_cita' => $datos['tiene_prog_cita']['value'],
				'idespecialidad' => $datos['idespecialidad'],
				'idsede' => $datos['idsede'],
				'demanda' => 'N',		
			);	
			$result = $this->db->insert('pa_sede_especialidad', $data);		
		}else{
			$data = array(
				'tiene_prog_cita' => $datos['tiene_prog_cita']['value'],
			);
			$this->db->where('idsedeespecialidad',$datos['id']);
			$result = $this->db->update('pa_sede_especialidad', $data);
		}	
		return $result;
	}
	public function m_editar_venta_prog_asistencial_especialidad_sede($datos){
		$result = true;
		$datos['tiene_venta_prog_cita']['value'] = ($datos['tiene_venta_prog_cita']['value'] == 1) ? 2:1;
		if($datos['id'] == null){
			$data = array(
				'tiene_venta_prog_cita' => $datos['tiene_venta_prog_cita']['value'],
				'idespecialidad' => $datos['idespecialidad'],
				'idsede' => $datos['idsede'],
				'demanda' => 'N',		
			);	
			$result = $this->db->insert('pa_sede_especialidad', $data);		
		}else{
			$data = array(
				'tiene_venta_prog_cita' => $datos['tiene_venta_prog_cita']['value'],
			);
			$this->db->where('idsedeespecialidad',$datos['id']);
			$result = $this->db->update('pa_sede_especialidad', $data);
		}	
		return $result;		
	}
	public function m_editar_prog_proc_especialidad_sede($datos){
		$result = true;
		$datos['tiene_prog_proc']['value'] = ($datos['tiene_prog_proc']['value'] == 1) ? 2:1;
		if($datos['id'] == null){
			$data = array(
				'tiene_prog_proc' => $datos['tiene_prog_proc']['value'],
				'idespecialidad' => $datos['idespecialidad'],
				'idsede' => $datos['idsede'],
				'demanda' => 'N',		
			);	
			$result = $this->db->insert('pa_sede_especialidad', $data);		
		}else{
			$data = array(
				'tiene_prog_proc' => $datos['tiene_prog_proc']['value'],
			);
			$this->db->where('idsedeespecialidad',$datos['id']);
			$result = $this->db->update('pa_sede_especialidad', $data);
		}	
		return $result;
	}
	public function m_editar_venta_prog_proc_especialidad_sede($datos){
		$result = true;
		$datos['tiene_venta_prog_proc']['value'] = ($datos['tiene_venta_prog_proc']['value'] == 1) ? 2:1;
		if($datos['id'] == null){
			$data = array(
				'tiene_venta_prog_proc' => $datos['tiene_venta_prog_proc']['value'],
				'idespecialidad' => $datos['idespecialidad'],
				'idsede' => $datos['idsede'],
				'demanda' => 'N',		
			);	
			$result = $this->db->insert('pa_sede_especialidad', $data);		
		}else{
			$data = array(
				'tiene_venta_prog_proc' => $datos['tiene_venta_prog_proc']['value'],
			);
			$this->db->where('idsedeespecialidad',$datos['id']);
			$result = $this->db->update('pa_sede_especialidad', $data);
		}	
		return $result;		
	}
	public function m_tiene_prog_asistencial($datos){
		$this->db->select('seesp.tiene_venta_prog_cita' );
		$this->db->from('pa_sede_especialidad seesp'); 
		$this->db->where('seesp.idespecialidad', $datos['idespecialidad']);
		$this->db->where('seesp.idsede', $this->sessionHospital['idsede']);
		$fData = $this->db->get()->row_array();
		return ($fData['tiene_venta_prog_cita'] == 1) ? TRUE : FALSE;	
	}
	public function m_tiene_prog_asistencial_proc($datos){
		$this->db->select('seesp.tiene_venta_prog_proc' );
		$this->db->from('pa_sede_especialidad seesp'); 
		$this->db->where('seesp.idespecialidad', $datos['idespecialidad']);
		$this->db->where('seesp.idsede', $this->sessionHospital['idsede']);
		$fData = $this->db->get()->row_array();
		return ($fData['tiene_venta_prog_proc'] == 1) ? TRUE : FALSE;	
	}
	public function m_tiene_programaciones_cargadas($datos){
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('pa_prog_medico prm'); 
		$this->db->where('prm.idespecialidad', $datos['idespecialidad']);
		$this->db->where('prm.idsede', $this->sessionHospital['idsede']);
		$this->db->where('prm.estado_prm', 1);
		$this->db->where('prm.fecha_programada >=', date('d-m-Y'));
		$fData = $this->db->get()->row_array();
		return ($fData['contador'] > 0) ? TRUE : FALSE;
	}
	public function m_cargar_especialidades_prog_asistencial()
	{
		$this->db->select('esp.idespecialidad, (esp.nombre) AS especialidad');
		$this->db->from('especialidad esp');
		$this->db->join('pa_sede_especialidad seesp','esp.idespecialidad = seesp.idespecialidad AND seesp.idsede = '.$this->sessionHospital['idsede'], 'left');
		$this->db->where('esp.estado', 1); // especialidad
		$this->db->order_by('esp.nombre');
		// $this->db->where('seesp.tiene_prog_cita', 1);
		$this->db->where('seesp.tiene_venta_prog_cita', 1);
		
		return $this->db->get()->result_array();
	}

	public function m_cargar_precio_consulta($idespecialidad, $idsedeempresaadmin){
		$this->db->select('pps.precio_sede::numeric as precio_sede',FALSE);
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pps.idproductomaster = pm.idproductomaster
													AND pps.estado_pps = 1
													AND pps.es_precio_web = 1
													AND pps.idsedeempresaadmin = '.$idsedeempresaadmin); 
		$this->db->where('pm.idespecialidad', $idespecialidad); 
		$this->db->where('pm.idtipoproducto',12); 
		$this->db->where('pm.solo_para_campania',2); 
		return $this->db->get()->row_array();
	}

	public function m_tiene_prog_citas($idespecialidad, $idsede){
		$this->db->select('seesp.tiene_venta_prog_cita' );
		$this->db->from('pa_sede_especialidad seesp'); 
		$this->db->where('seesp.idespecialidad', $idespecialidad);
		$this->db->where('seesp.idsede', $idsede);
		$fData = $this->db->get()->row_array();
		return ($fData['tiene_venta_prog_cita'] == 1) ? TRUE : FALSE;	
	}

}