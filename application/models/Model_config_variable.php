<?php
class Model_config_variable extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_listar_config_variable(){ 
		$this->db->select('cv.idconfigvariable, cv.essalud, cv.onp, cv.asignacion_familiar, (cv.rmv::numeric) AS rmv, (cv.uit::numeric) AS uit, (cv.remun_max_asegurable::numeric) AS rma , cv.fecha_registro'); 
		$this->db->from('rh_config_variable cv');
		$this->db->where('vigente', 1); // activo
		
		return $this->db->get()->row_array();
	}
	public function m_registrar_config_variable($datos)
	{
		$data = array(
			'essalud' => $datos['essalud'],
			'asignacion_familiar' => $datos['asignacion_familiar'],
			'rmv' => $datos['rmv'],
			'uit' => $datos['uit'],
			'onp' => $datos['onp'],
			'remun_max_asegurable' => $datos['rma'],
			'iduser_registro' => $this->sessionHospital['idusers'],
			'vigente' => 1,
			'fecha_registro' => date('Y-m-d H:i:s'),
		);
		return $this->db->insert('rh_config_variable', $data);
	}
	public function m_actualizar_config_variable_vigente($datos)
	{
		$data = array(
			'vigente' => 2,
		);
		$this->db->where('idconfigvariable',$datos['id']);
		return $this->db->update('rh_config_variable', $data);
	}
}	