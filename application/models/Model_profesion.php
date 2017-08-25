<?php
class Model_profesion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_profesion_por_autocompletado($datos)
	{
		$this->db->select('idprofesion, descripcion_prf, estado_prf');
		$this->db->from('rh_profesion');
		if( $datos ){ 
			$this->db->ilike('descripcion_prf', $datos['search']);
		}
		$this->db->where('estado_prf', 1); // activo 
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_profesion_cbo($datos)
	{
		$this->db->select('idprofesion, descripcion_prf, estado_prf');
		$this->db->from('rh_profesion');
		$this->db->where('estado_prf', 1); // activo 
		$this->db->order_by('descripcion_prf','ASC');
		return $this->db->get()->result_array();
	}
	public function m_listar_profesion($paramPaginate)
	{
		$this->db->select('idprofesion, descripcion_prf, estado_prf');
		$this->db->from('rh_profesion');
		$this->db->where('estado_prf', 1); // activo 
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->where("UPPER(translate(CAST(".$key." AS TEXT ) , 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC')) LIKE UPPER(translate('%".$value."%', 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC'))");
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		$this->db->order_by('descripcion_prf','ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_profesion($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_profesion');
		$this->db->where('estado_prf', 1); // activo
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->where("UPPER(translate(CAST(".$key." AS TEXT ) , 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC')) LIKE UPPER(translate('%".$value."%', 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC'))");
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_consultar_profesion($datos)
	{
		$this->db->select('idprofesion, descripcion_prf, estado_prf');
		$this->db->from('rh_profesion');
		$this->db->where('estado_prf', 1);
		$this->db->where("UPPER(translate(CAST(descripcion_prf AS TEXT ) , 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC')) = UPPER(translate('".$datos."', 'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ', 'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC'))");
		
		$this->db->order_by('descripcion_prf','ASC');
		return $this->db->get()->result_array();
	}
	public function m_registrar_profesion($datos)
	{
		$data = array(
			'descripcion_prf' => strtoupper_total($datos['descripcion']),
		);
		return $this->db->insert('rh_profesion', $data);
	}
	public function m_editar_profesion($datos)
	{
		$data = array(
			'descripcion_prf' =>strtoupper_total($datos['descripcion']),
		);
		$this->db->where('idprofesion',$datos['id']);
		return $this->db->update('rh_profesion', $data);
	}
	public function m_anular_profesion($id)
	{
		$data = array(
			'estado_prf' => 0
		);
		$this->db->where('idprofesion',$id);
		if($this->db->update('rh_profesion', $data)){
			return true;
		}else{
			return false;
		}
	}
}