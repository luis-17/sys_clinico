<?php
class Model_sub_operacion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_suboperacion_cbo($datos){ 
		$this->db->select('so.idsuboperacion, so.descripcion_sop, so.estado_sop, codigo_plan');
		$this->db->from('ct_suboperacion so');
		$this->db->where('so.idoperacion', $datos['idoperacion']); // activo 
		$this->db->where('so.estado_sop', 1);
		$this->db->order_by('so.descripcion_sop', 'ASC');
		return $this->db->get()->result_array();
	}
}