<?php
class Model_afp extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_afp_cbo(){ 
		$this->db->select('idafp, descripcion_afp, estado_afp');
		$this->db->from('rh_afp');
		$this->db->where('estado_afp', 1); // activo
		return $this->db->get()->result_array();
	}

	public function m_cargar_afp($paramPaginate){ 
		$this->db->select('afp.idafp, afp.descripcion_afp, afp.estado_afp, afp.a_oblig, afp.comision, afp.p_seguro, afp.comision_m');
		$this->db->from('rh_afp afp');
		$this->db->where('afp.estado_afp', 1); // activo
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

	public function m_count_afp($paramPaginate){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_afp afp');
		$this->db->where('afp.estado_afp', 1); // activo
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

	public function m_editar($datos){
		$data = array(
			'descripcion_afp' => strtoupper($datos['descripcion']),
			'a_oblig' => empty($datos['a_oblig']) ? NULL : $datos['a_oblig'], 
			'comision' => empty($datos['comision']) ? NULL : $datos['comision'],
			'p_seguro' => empty($datos['p_seguro']) ? NULL : $datos['p_seguro'], 
			'comision_m' => empty($datos['comision_m']) ? NULL : $datos['comision_m'],
			// 'updatedAt' => date('Y-m-d H:i:s')

		);
		$this->db->where('idafp',$datos['id']);
		return $this->db->update('rh_afp', $data);
	}
	public function m_registrar($datos){
		$data = array(
			'descripcion_afp' => strtoupper($datos['descripcion']),
			'a_oblig' => empty($datos['a_oblig']) ? NULL : $datos['a_oblig'], 
			'comision' => empty($datos['comision']) ? NULL : $datos['comision'],
			'p_seguro' => empty($datos['p_seguro']) ? NULL : $datos['p_seguro'], 
			'comision_m' => empty($datos['comision_m']) ? NULL : $datos['comision_m'],
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('rh_afp', $data);
	}
	public function m_anular($id){
		$data = array(
			'estado_afp' => 0
		);
		$this->db->where('idafp',$id);
		return $this->db->update('rh_afp', $data);
	}
}