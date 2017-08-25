<?php
class Model_areas_oc extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_areas_oc(){ 
		$this->db->select('ao.idareaoc, ao.descripcion, ao.estado_la, ao.mail_1, ao.mail_2, ao.clave_mail_1, sc_ao.mail_1 AS mail_receptor, sc_ao.descripcion AS aleas_receptor'); 
		$this->db->from('log_area_oc ao');
		$this->db->join('log_area_oc sc_ao','ao.idareaoc = sc_ao.envia_correo_a');
		$this->db->where('ao.estado_la', 1); // activo 
		$this->db->order_by('ao.orden_la', 'ASC'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_esta_area_oc($datos)
	{
		$this->db->select('idareaoc, descripcion, estado_la, mail_1, mail_2,clave_mail_1');
		$this->db->from('log_area_oc');
		$this->db->where('idareaoc', $datos['id']);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_estado_area_oc($idareaOC,$estadoOC)
	{
		$this->db->select('idestadoporarea, idareaoc, descripcion_estado');
		$this->db->from('log_estado_por_area');
		$this->db->where('estado_lea', 1); // activo 
		$this->db->where('idareaoc', $idareaOC);
		$this->db->where('descripcion_estado', $estadoOC);
		return $this->db->get()->row_array();
	}
}