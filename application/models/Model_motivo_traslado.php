<?php
class Model_motivo_traslado extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_listar_motivo_traslado($paramPaginate){ 
		$this->db->select('idmotivotraslado, descripcion_mt, estado_mt'); 
		$this->db->from('motivo_traslado');
		$this->db->where('estado_mt', 1);
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
		$this->db->order_by('descripcion_mt', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_motivo_traslado($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('motivo_traslado');
		$this->db->where('estado_mt', 1);
		if( @$paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_motivo_traslado()
	{
		$this->db->select('idmotivotraslado, descripcion_mt');
		$this->db->from('motivo_traslado');
		$this->db->where('estado_mt', 1);
		$this->db->order_by('descripcion_mt','ASC');
		return $this->db->get()->result_array();
	}
	public function m_registrar_motivo_traslado($datos)
	{
		$data = array(
			'descripcion_mt' => strtoupper($datos['descripcion']),
			'estado_mt' => 1,
		);
		return $this->db->insert('motivo_traslado', $data);
	}
	public function m_editar_motivo_traslado($datos)
	{
		$data = array(
			'descripcion_mt' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idmotivotraslado',$datos['id']);
		return $this->db->update('motivo_traslado', $data);
	}
	public function m_anular_motivo_traslado($id)
	{
		$data = array(
			'estado_mt' => 0
		);
		$this->db->where('idmotivotraslado',$id);
		if($this->db->update('motivo_traslado', $data)){
			return true;
		}else{
			return false;
		}
	}

}