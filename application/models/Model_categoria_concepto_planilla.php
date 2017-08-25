<?php
class Model_categoria_concepto_planilla extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_categoria_concepto_cbo(){ 
		$this->db->select('cc.idcategoriaconcepto, cc.descripcion, cc.tipo_concepto ');
		$this->db->from('rh_categoria_concepto cc');
		$this->db->order_by('cc.tipo_concepto ASC');
		$this->db->order_by('cc.idcategoriaconcepto ASC');
		return $this->db->get()->result_array();
	}
}