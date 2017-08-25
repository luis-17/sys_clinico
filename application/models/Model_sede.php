<?php
class Model_sede extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_sedes($paramPaginate){ 
		$this->db->select('idsede, descripcion, estado_se, hora_inicio_atencion, hora_final_atencion, intervalo_sede, tiene_prog_cita');
		$this->db->from('sede');
		$this->db->where('estado_se', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sedes()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('sede');
		$this->db->where('estado_se', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_sedes_cbo($datos = FALSE){ 
		$this->db->select('idsede, descripcion, estado_se, hora_inicio_atencion, hora_final_atencion');
		$this->db->from('sede');
		$this->db->where('estado_se', 1); // activo
		if($this->sessionHospital['key_group'] == 'key_dir_salud'){
			$this->db->where('idsede', $this->sessionHospital['idsede']);
		}
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		$this->db->order_by('idsede','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_sedes_por_empresa_cbo($datos){ 
		$this->db->select('idsedeempresaadmin, ea.idempresaadmin, s.idsede, razon_social, s.descripcion');
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_se', 1);
		$this->db->where('ea.idempresaadmin', $datos['id']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_sedes_no_agregados_a_empresa_admin($paramPaginate,$datos){
		$this->db->select('sea.idsede');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('s.estado_se',1);
		$this->db->where('sea.estado_sea',1);
		$this->db->where('sea.idempresaadmin',$datos['id']);
		$sqlSedes = $this->db->get_compiled_select();

		$this->db->select('idsede, descripcion');
		$this->db->from('sede s');
		if($sqlSedes)
			$this->db->where('idsede NOT IN ('. $sqlSedes . ')');
		$this->db->where('estado_se', 1);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sedes_no_agregados_a_empresa_admin($datos)
	{
		$this->db->select('sea.idsede');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('s.estado_se',1);
		$this->db->where('sea.estado_sea',1);
		$this->db->where('sea.idempresaadmin',$datos['id']);
		$sqlSedes = $this->db->get_compiled_select();

		$this->db->select('COUNT(*) AS contador');
		$this->db->from('sede s');
		if($sqlSedes)
			$this->db->where('idsede NOT IN ('. $sqlSedes . ')');
		$this->db->where('estado_se', 1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_sedes_agregados_a_empresa_admin($paramPaginate,$datos){
		$this->db->select('s.idsede, s.descripcion, sea.idsedeempresaadmin');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('s.estado_se',1);
		$this->db->where('sea.estado_sea',1);
		$this->db->where('sea.idempresaadmin',$datos['id']);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sedes_agregados_a_empresa_admin($datos)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('sede_empresa_admin sea');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('s.estado_se',1);
		$this->db->where('sea.estado_sea',1);
		$this->db->where('sea.idempresaadmin',$datos['id']);
		
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_sede_por_id($id)
	{
		$this->db->select('idsede, descripcion, direccion_se, hora_inicio_atencion, hora_final_atencion, intervalo_sede');
		$this->db->from('sede');
		$this->db->where('estado_se',1);
		$this->db->where('idsede',$id);
		return $this->db->get()->row_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'hora_inicio_atencion' => $datos['hora_inicio'],
			'hora_final_atencion' => $datos['hora_fin'],
			'intervalo_sede' => $datos['intervalo_sede'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idsede',$datos['id']);
		return $this->db->update('sede', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'hora_inicio_atencion' => $datos['hora_inicio'],
			'hora_final_atencion' => $datos['hora_fin'],
			'intervalo_sede' => $datos['intervalo_sede'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('sede', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_se' => 0
		);
		$this->db->where('idsede',$id);
		if($this->db->update('sede', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_consultar($i){
		$this->db->select('idsede, descripcion, estado_se, hora_inicio_atencion, hora_final_atencion, intervalo_sede');
		$this->db->from('sede');
		$this->db->where('idsede', $i); 
		return $this->db->get()->row_array();
	}	

	public function m_update_tiene_prog_cita($datos){
		$data = array(
			'tiene_prog_cita' => $datos['value']
		);
		$this->db->where('idsede',$datos['id']);
		return $this->db->update('sede', $data);
	}

	
}