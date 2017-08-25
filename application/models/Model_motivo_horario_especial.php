<?php
class Model_motivo_horario_especial extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_motivos_horario_especial_cbo($datos){ 
		$this->db->select('idmotivohe, descripcion_mh, estado_mh');
		$this->db->from('rh_motivo_he');
		$this->db->where('estado_mh', 1); // activo
		if( $this->sessionHospital['key_group'] == 'key_dir_far' || $this->sessionHospital['key_group'] == 'key_admin' ||
			$this->sessionHospital['key_group'] == 'key_dir_salud' || $this->sessionHospital['key_group'] == 'key_aud_salud' ||
			$this->sessionHospital['key_group'] == 'key_salud_caja' ){
			$this->db->where('agregar_a_jefes', 1);
		}
		return $this->db->get()->result_array();
	}
	
	public function m_cargar_motivo_horario_especial($paramPaginate){ 
		$this->db->select('idmotivohe, descripcion_mh AS descripcion, estado_mh, agregar_a_jefes');
		$this->db->from('rh_motivo_he');
		$this->db->where('estado_mh', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_motivo_horario_especial()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_motivo_he');
		$this->db->where('estado_mh', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_mh' => strtoupper($datos['descripcion']),
			// 'updatedAt' => date('Y-m-d H:i:s')
			'agregar_a_jefes' => $datos['agregarAJefes']? 1 : 2,
		);
		$this->db->where('idmotivohe',$datos['id']);
		return $this->db->update('rh_motivo_he', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_mh' => strtoupper($datos['descripcion']),
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
			'agregar_a_jefes' => $datos['agregarAJefes']? 1 : 2,
		);
		return $this->db->insert('rh_motivo_he', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_mh' => 0
		);
		$this->db->where('idmotivohe',$id);
		if($this->db->update('rh_motivo_he', $data)){
			return true;
		}else{
			return false;
		}
	}
	/* ************************* SUB MOTIVOS ************************** */
	public function m_cargar_submotivos_horario_especial_cbo($datos){ 
		$this->db->select('mhe.idmotivohe, descripcion_mh, estado_mh, she.idsubmotivohe, she.descripcion_smh');
		$this->db->from('rh_motivo_he mhe');
		$this->db->join('rh_submotivo_he she','mhe.idmotivohe = she.idmotivohe');
		$this->db->where('mhe.idmotivohe', $datos['idmotivo']); // activo 
		$this->db->where('estado_smh', 1); // activo
		return $this->db->get()->result_array();
	}
	public function m_registrar_submotivo($datos)
	{
		$data = array(
			'descripcion_smh' => strtoupper($datos['submotivo']),
			'idmotivohe' => $datos['idmotivo']
		);
		return $this->db->insert('rh_submotivo_he', $data);
	}
	public function m_anular_submotivo($id)
	{
		$data = array(
			'estado_smh' => 0
		);
		$this->db->where('idsubmotivohe',$id);
		if($this->db->update('rh_submotivo_he', $data)){
			return true;
		}else{
			return false;
		}
	}
}