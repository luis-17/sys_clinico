<?php
class Model_operacion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
 	public function m_cargar_operacion_cbo($tipoOperacion){ 
		$this->db->select('idoperacion, descripcion_op, estado_op, tipo_operacion, codigo_amarre_cc');
		$this->db->from('ct_operacion');
		$this->db->where('tipo_operacion', $tipoOperacion); // activo 
		$this->db->where('estado_op', 1);
		$this->db->order_by('idoperacion','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_operaciones($paramPaginate=FALSE, $datos=FALSE){ 
		$this->db->select('idoperacion, descripcion_op, estado_op,tipo_operacion');
		$this->db->from('ct_operacion');
		$this->db->where('estado_op', 1); // activo

		if($datos && !empty($datos['tipo']['id'])){
			$this->db->where('tipo_operacion', $datos['tipo']['id']);
		}

		if($paramPaginate){
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
		}

		return $this->db->get()->result_array();
	}
	public function m_count_operaciones($paramPaginate=FALSE, $datos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_operacion');
		$this->db->where('estado_op', 1); // activo
		
		if($datos && !empty($datos['tipo']['id'])){
			$this->db->where('tipo_operacion', $datos['tipo']['id']);
		}

		if($paramPaginate){
			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
		}

		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_op' => strtoupper($datos['descripcion']),
			'tipo_operacion' => intval($datos['tipo_operacion']['id'])
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idoperacion',$datos['id']);
		return $this->db->update('ct_operacion', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_op' => strtoupper($datos['descripcion']),
			'tipo_operacion' => intval($datos['tipo_operacion']['id'])
			// 'createdAt' => date('Y-m-d H:i:s'),
			// 'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('ct_operacion', $data);
	}
	public function m_anular($id)
	{
		$data = array( 
			'estado_op' => 0
		);
		$this->db->where('idoperacion',$id);
		if($this->db->update('ct_operacion', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_buscar_operacion($datos){
		$this->db->select('*');
		$this->db->from('ct_operacion');
		$this->db->where('descripcion_op', strtoupper($datos)); // activo 
		return $this->db->get()->result_array();		
	}

	public function m_cargar_operaciones_so($paramPaginate,$id){ 
		$this->db->select('idsuboperacion,idoperacion,descripcion_sop,codigo_plan,estado_sop');
		$this->db->from('ct_suboperacion');
		$this->db->where('estado_sop', 1); // activo
		$this->db->where('idoperacion',$id); // idoperacion
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_operaciones_so($paramPaginate,$id)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_suboperacion');
		$this->db->where('estado_sop', 1); 
		$this->db->where('idoperacion',$id); 
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_buscar_operacion_so($datos){
		$this->db->select('*');
		$this->db->from('ct_suboperacion');
		$this->db->where('descripcion_sop', strtoupper($datos['descripcion'])); 
		$this->db->where('idoperacion', strtoupper($datos['idoperacion'])); 
		return $this->db->get()->result_array();		
	}
	public function m_buscar_operacion_so_codigo($datos){
		$this->db->select('*');
		$this->db->from('ct_suboperacion');
		$this->db->where('codigo_plan', strtoupper($datos['codigo'])); 
		$this->db->where('idoperacion', strtoupper($datos['idoperacion'])); 
		return $this->db->get()->result_array();		
	}	
	public function m_registrar_so($datos)
	{
		$data = array(
			'descripcion_sop' => strtoupper($datos['descripcion']),
			'idoperacion' => $datos['idoperacion'],
			'codigo_plan' => $datos['codigo']
		);
		return $this->db->insert('ct_suboperacion', $data);
	}	
	public function m_editar_so($datos)
	{
		$data = array(
			'descripcion_sop' => strtoupper($datos['descripcion']),
			//'idoperacion' => $datos['idoperacion'],
			'codigo_plan' => $datos['codigo']
		);
		$this->db->where('idsuboperacion',$datos['id']);
		return $this->db->update('ct_suboperacion', $data);
	}
	public function m_anular_so($id)
	{
		$data = array( 
			'estado_sop' => 0
		);
		$this->db->where('idsuboperacion',$id);
		if($this->db->update('ct_suboperacion', $data)){
			return true;
		}else{
			return false;
		}
	}	
}