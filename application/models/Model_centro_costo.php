<?php
class Model_centro_costo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_listar_centro_costo($paramPaginate){ 
		$this->db->select('cc.idcentrocosto, cc.codigo_cc, cc.nombre_cc, cc.descripcion_cc, cc.idsubcatcentrocosto, scc.descripcion_scc, ccc.descripcion_ccc'); 
		$this->db->from('ct_centro_costo cc');
		$this->db->where('estado_cc', 1); // activo 
		$this->db->join('ct_subcat_centro_costo scc','cc.idsubcatcentrocosto = scc.idsubcatcentrocosto');
		$this->db->join('ct_cat_centro_costo ccc',' scc.idcatcentrocosto = ccc.idcatcentrocosto');
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
		$this->db->order_by('nombre_cc', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_centro_costo($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_centro_costo cc');
		$this->db->where('estado_cc', 1); // activo 
		$this->db->join('ct_subcat_centro_costo scc','cc.idsubcatcentrocosto = scc.idsubcatcentrocosto');
		$this->db->join('ct_cat_centro_costo ccc',' scc.idcatcentrocosto = ccc.idcatcentrocosto');
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
	public function m_cargar_centro_costo_grilla($paramPaginate, $paramDatos){ 
		$this->db->select('cc.idcentrocosto, cc.codigo_cc, cc.nombre_cc, cc.descripcion_cc'); 
		$this->db->from('ct_centro_costo cc');
		// $this->db->join('ct_subcat_centro_costo scc','cc.idsubcatcentrocosto = scc.idsubcatcentrocosto');
		// $this->db->join('ct_cat_centro_costo ccc',' scc.idcatcentrocosto = ccc.idcatcentrocosto');
		$this->db->where('estado_cc', 1); // activo 
		$this->db->where('idsubcatcentrocosto', $paramDatos['id']);
		$this->db->order_by('nombre_cc', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_centro_costo_cbo($id){ 
		$this->db->select('cc.idcentrocosto, cc.codigo_cc, cc.nombre_cc, cc.descripcion_cc'); 
		$this->db->from('ct_centro_costo cc');
		$this->db->where('estado_cc', 1); // activo 
		$this->db->where('idsubcatcentrocosto', $id);
		$this->db->order_by('nombre_cc', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_cat_centro_costo_cbo()
	{
		$this->db->select('idcatcentrocosto, codigo_ccc, descripcion_ccc');
		$this->db->from('ct_cat_centro_costo');
		$this->db->where('estado_ccc', 1);
		$this->db->order_by('idcatcentrocosto', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_subcat_centro_costo_cbo($datos)
	{
		$this->db->select('idsubcatcentrocosto, codigo_scc, descripcion_scc');
		$this->db->from('ct_subcat_centro_costo');
		$this->db->where('estado_scc', 1);
		$this->db->where('idcatcentrocosto', $datos['id']);
		$this->db->order_by('idsubcatcentrocosto', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_categoria_subcat_centro_costo_cbo()
	{
		$this->db->select('ccc.idcatcentrocosto, ccc.codigo_ccc, ccc.descripcion_ccc');
		$this->db->select('scc.idsubcatcentrocosto, scc.codigo_scc, scc.descripcion_scc');
		$this->db->from('ct_subcat_centro_costo scc');
		$this->db->join('ct_cat_centro_costo ccc', 'scc.idcatcentrocosto = ccc.idcatcentrocosto');
		$this->db->where('estado_ccc', 1);
		$this->db->where('estado_scc', 1);
		$this->db->order_by('idcatcentrocosto', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_registrar_tipo_cambio($datos)
	{
		$data = array(
			'fecha_cambio' => $datos['fecha_cambio'],
			'compra' => $datos['compra'],
			'venta' => $datos['venta'],
			'idusercreacion' => $this->sessionHospital['idusers'],
			'vigente' => 1,
			'createdAt' => date('Y-m-d H:i:s'),
		);
		return $this->db->insert('ct_tipo_cambio', $data);
	}
	public function m_actualizar_tipo_cambio_vigente($datos)
	{
		$data = array(
			'vigente' => 2,
		);
		$this->db->where('idtipocambio',$datos['id']);
		return $this->db->update('ct_tipo_cambio', $data);
	}
	public function m_registrar_centro_costo($datos)
	{
		$data = array(
			'nombre_cc' => $datos['nombre'],
			'descripcion_cc' => $datos['descripcion'],
			'codigo_cc' => $datos['codigo'],
			'idsubcatcentrocosto' => $datos['idsubcat'],

		);
		return $this->db->insert('ct_centro_costo', $data);
	}
	public function m_editar_centro_costo($datos)
	{
		$data = array(
			'nombre_cc' => $datos['nombre'],
			'descripcion_cc' => $datos['descripcion'],
			'codigo_cc' => $datos['codigo'],
			'idsubcatcentrocosto' => $datos['idsubcat'],
		);
		$this->db->where('idcentrocosto',$datos['id']);
		return $this->db->update('ct_centro_costo', $data);
	}
	public function m_anular_centro_costo($id)
	{
		$data = array(
			'estado_cc' => 0
		);
		$this->db->where('idcentrocosto',$id);
		if($this->db->update('ct_centro_costo', $data)){
			return true;
		}else{
			return false;
		}
	}

}