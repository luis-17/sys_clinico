<?php
class Model_motivomovimiento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_motivomovimiento_cbo($datos=FALSE)
	{
		$this->db->select('idmotivomovimiento, descripcion_mm');
		$this->db->from('motivo_movimiento');
		$this->db->where('estado_mm', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_motivomovimiento_tipo($datos=FALSE)
	{
		$this->db->select('idmotivomovimiento, descripcion_mm,tipo_movimiento');
		$this->db->from('motivo_movimiento');
		$this->db->where('estado_mm', 1);
		if( $datos ){ 
			$this->db->where($datos['nameColumn'], $datos['search']);
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_motivomovimiento($paramPaginate){
		$this->db->select('idmotivomovimiento,descripcion_mm,tipo_movimiento,estado_mm');
		$this->db->from('motivo_movimiento');
		$this->db->where('estado_mm <>', 0);
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
		return $this->db->get()->result_array();
	}
	public function m_count_motivomovimiento($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('motivo_movimiento');
		$this->db->where('estado_mm <>', 0);
		if( $paramPaginate['search'] ){
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
			'descripcion_mm' => strtoupper($datos['descripcion']),
			'tipo_movimiento' => ($datos['tipomovimiento']),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idmotivomovimiento',$datos['id']);
		return $this->db->update('motivo_movimiento', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_mm' => strtoupper($datos['descripcion']),
			'tipo_movimiento' => ($datos['tipomovimiento']),
			'estado_mm' => 1,
		);
		return $this->db->insert('motivo_movimiento', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_mm' => 0
		);
		$this->db->where('idmotivomovimiento',$id);
		if($this->db->update('motivo_movimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_mm' => 1
		);
		$this->db->where('idmotivomovimiento',$id);
		if($this->db->update('motivo_movimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_mm' => 2
		);
		$this->db->where('idmotivomovimiento',$id);
		if($this->db->update('motivo_movimiento', $data)){
			return true;
		}else{
			return false;
		}
	}



}