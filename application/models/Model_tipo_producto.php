<?php
class Model_tipo_producto extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	public function m_cargar_tipoproducto_cbo($datos=FALSE)
	{
		//$this->db->select('idtipovia, descripcion_tv, abreviatura_tv');
		$this->db->from('tipo_producto');
		$this->db->where('estado_tp', 1);
		if( $datos ){ 
			$this->db->where('idmodulo', $datos['modulo']);
		}
		$this->db->where('estado_tp',1);
		$this->db->order_by("nombre_tp");
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipoproducto($paramPaginate){
		$this->db->select('tp.idtipoproducto, tp.nombre_tp, mod.idmodulo, mod.descripcion_mod, tp.estado_tp');
		$this->db->from('tipo_producto tp');
		$this->db->join('modulo mod', 'tp.idmodulo = mod.idmodulo');
		$this->db->where('estado_tp <>', 0);
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
	public function m_count_tipoproducto()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_producto tp');
		$this->db->join('modulo mod', 'tp.idmodulo = mod.idmodulo');
		$this->db->where('estado_tp <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'nombre_tp' => strtoupper($datos['nombre']),
			'descripcion_tp' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idtipoproducto',$datos['id']);
		return $this->db->update('tipo_producto', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre_tp' => strtoupper($datos['nombre']),
			'descripcion_tp' => isset($datos['descripcion'])? $datos['descripcion']:null,
			'estado_tp' => 1,
		);
		return $this->db->insert('tipo_producto', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_tp' => 0
		);
		$this->db->where('idtipoproducto',$id);
		if($this->db->update('tipo_producto', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_tp' => 1
		);
		$this->db->where('idtipoproducto',$id);
		if($this->db->update('tipo_producto', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_tp' => 2
		);
		$this->db->where('idtipoproducto',$id);
		if($this->db->update('tipo_producto', $data)){
			return true;
		}else{
			return false;
		}
	}
}