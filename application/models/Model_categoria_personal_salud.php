<?php
class Model_categoria_personal_salud extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_categoria_consul_cbo(){ 
		$this->db->select('idcategoriapersonalsalud, descripcion_cps', FALSE);
		$this->db->from('pa_categoria_personal_salud');
		$this->db->where('estado_cps ', 1);	//activo

		$this->db->order_by('idcategoriapersonalsalud','ASC');

		return $this->db->get()->result_array();
	}
}