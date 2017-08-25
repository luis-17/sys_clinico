<?php
class Model_reactivo_insumo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_reactivo_insumo_cbo($datos=FALSE)
	{
		$this->db->select('idreactivoinsumo, descripcion');
		$this->db->from('reactivo_insumo');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_reactivoInsumo($paramPaginate){
		$this->db->select('r.idreactivoinsumo,r.descripcion,r.tipo,r.stock,r.stock_minimo,r.stock_maximo,r.precio,r.idpresentacion,r.idmarca,m.descripcion_m as marca,p.descripcion_pr as presentacion,r.idunidadlaboratorio,u.descripcion as unidad,r.pruebas_presentacion,r.estado_ri');
		$this->db->from('reactivo_insumo r');
		$this->db->join('presentacion p','r.idpresentacion=p.idpresentacion');
		$this->db->join('unidad_laboratorio u','r.idunidadlaboratorio=u.idunidadlaboratorio');
		$this->db->join('marcalab m','r.idmarca=m.idmarca');
		$this->db->where('estado_ri <>', 0);
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
	public function m_count_reactivoInsumo($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('reactivo_insumo r');
		$this->db->join('presentacion p','r.idpresentacion=p.idpresentacion');
		$this->db->join('unidad_laboratorio u','r.idunidadlaboratorio=u.idunidadlaboratorio');
		$this->db->join('marcalab m','r.idmarca=m.idmarca');
		$this->db->where('estado_ri <>', 0);
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

	public function m_cargar_este_reactivoInsumo_por_codigo($datos)
	{
		$this->db->select('ri.idreactivoinsumo,ri.descripcion,ri.stock,u.descripcion as nombreunidad');
		$this->db->from('reactivo_insumo ri');
		$this->db->join('unidad_laboratorio u','u.idunidadlaboratorio = ri.idunidadlaboratorio'); 
		$this->db->where('idreactivoinsumo', $datos['id']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	
	public function m_editar($datos)
		{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'tipo' => $datos['idtipo'],
			'idpresentacion' =>  $datos['idpresentacion'],
			'precio' => (empty($datos['precio']) ? NULL : $datos['precio']),
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_ri' => 1 ,
			'idunidadlaboratorio' => $datos['idunidadlaboratorio'],
			'stock_minimo' => (empty($datos['stockminimo']) ? NULL : $datos['stockminimo']),
			'stock_maximo' => (empty($datos['stockmaximo']) ? NULL : $datos['stockmaximo']),
			'pruebas_presentacion' => (empty($datos['pruebas']) ? NULL : $datos['pruebas']),
			'idmarca' => $datos['idmarca'],
		);
		$this->db->where('idreactivoinsumo',$datos['id']);
		return $this->db->update('reactivo_insumo', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'tipo' => $datos['idtipo'],
			'stock' => 0,
			'idpresentacion' =>  $datos['idpresentacion'],
			'precio' => (empty($datos['precio']) ? NULL : $datos['precio']),
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_ri' => 1 ,
			'idunidadlaboratorio' => $datos['idunidadlaboratorio'],
			'stock_minimo' => (empty($datos['stockminimo']) ? NULL : $datos['stockminimo']),
			'stock_maximo' => (empty($datos['stockmaximo']) ? NULL : $datos['stockmaximo']),
			'pruebas_presentacion' => (empty($datos['pruebas']) ? NULL : $datos['pruebas']),
			'idmarca' => $datos['idmarca'],
		);
		return $this->db->insert('reactivo_insumo', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ri' => 0
		);
		$this->db->where('idreactivoinsumo',$id);
		if($this->db->update('reactivo_insumo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_ri' => 1
		);
		$this->db->where('idreactivoinsumo',$id);
		if($this->db->update('reactivo_insumo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_ri' => 2
		);
		$this->db->where('idreactivoinsumo',$id);
		if($this->db->update('reactivo_insumo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_busca_registro_detkardex($id)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle_kardex');
		$this->db->where('idreactivoinsumo',$id);
		$fData = $this->db->get()->row_array();
		if($fData['contador'] > 0){
			return true;
		}else {
			return false;
		}
	}

	// public function m_count_reactivoInsumo_vencidos()
	// {
	// 	$this->db->select('COUNT(*) AS contador',FALSE);
	// 	$this->db->from('detalle_kardex');
	// 	$this->db->where('estado_k <>', 0);
	// 	$this->db->where("TO_CHAR(fecha_vencimiento,'YYYY-MM-DD HH24:MI:SS') < TO_CHAR(NOW(), 'YYYY-MM-DD HH24:MI:SS')");
	// 	$fData = $this->db->get()->row_array();
	// 	return $fData['contador'];
	// }
	public function m_cargar_reactivoInsumo_stock_minimo($paramPaginate){
		$this->db->select('idreactivoinsumo,descripcion,stock,stock_minimo,estado_ri');
		$this->db->from('reactivo_insumo');
		$this->db->where('estado_ri <>', 0);
		$this->db->where('stock <= stock_minimo' );
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


	public function m_count_reactivoInsumo_stock_minimo()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('reactivo_insumo');
		$this->db->where('estado_ri <>', 0);
		$this->db->where('stock <= stock_minimo' );
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

}