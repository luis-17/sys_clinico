<?php
class Model_cargo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_cargos_cbo($datos = FALSE){ 
		$this->db->select('idcargo, descripcion_ca, estado_ca');
		$this->db->from('rh_cargo');
		$this->db->where('estado_ca', 1); // activo
		if( $datos ){
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}

	public function m_cargar_cargos_por_autocompletado($datos)
	{
		$this->db->select('idcargo, descripcion_ca, estado_ca');
		$this->db->from('rh_cargo');
		if( $datos ){ 
			$this->db->ilike('descripcion_ca', $datos['search']);
		}
		$this->db->where('estado_ca', 1); // activo 
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_cargo($paramPaginate){ 
		$this->db->select('idcargo, descripcion_ca, estado_ca, agrega_horario_especial');
		$this->db->from('rh_cargo');
		$this->db->where('estado_ca', 1); // activo
		if( !empty($paramPaginate['search']) ){
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
	public function m_count_cargo($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_cargo');
		$this->db->where('estado_ca', 1); // activo
		if( !empty($paramPaginate['search']) ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_ca' => strtoupper_total($datos['descripcion']),
			'agrega_horario_especial' => $datos['agrega_horario_especial'] ? 1 : 2,
			// 'updatedAt' => date('Y-m-d H:i:s')

		);
		$this->db->where('idcargo',$datos['id']);
		return $this->db->update('rh_cargo', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_ca' => strtoupper_total($datos['descripcion']),
			'agrega_horario_especial' => $datos['agrega_horario_especial'] ? 1 : 2,
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('rh_cargo', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ca' => 0
		);
		$this->db->where('idcargo',$id);
		if($this->db->update('rh_cargo', $data)){
			return true;
		}else{
			return false;
		}
	}
}