<?php
class Model_analisis extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_analisis_cbo($datos=FALSE)
	{
		$this->db->select('idanalisis, descripcion_anal');
		$this->db->from('analisis');
		$this->db->where('estado_anal', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_analisis($paramPaginate){
		$this->db->select('idanalisis, s.idseccion, descripcion_sec, descripcion_anal, abreviatura, m.idmetodo, m.descripcion, pm.idproductomaster, pm.descripcion as producto, a.tiene_sub, estado_anal');
		$this->db->from('analisis a');
		$this->db->join('seccion s','a.idseccion = s.idseccion');
		$this->db->join('metodo m','a.idmetodo = m.idmetodo','left');
		$this->db->join('producto_master pm','a.idproductomaster = pm.idproductomaster','left');
		$this->db->where('estado_anal <>', 0);

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
	public function m_count_analisis($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('analisis a');
		$this->db->join('seccion s','a.idseccion = s.idseccion');
		$this->db->join('metodo m','a.idmetodo = m.idmetodo','left');
		$this->db->join('producto_master pm','a.idproductomaster = pm.idproductomaster','left');
		$this->db->where('estado_anal <>', 0);
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
	public function m_cargar_pdtos_lab($datos=FALSE)
	{
		$this->db->select('idproductomaster, descripcion');
		$this->db->from('producto_master');
		$this->db->where('idtipoproducto', '15');
		$this->db->where('estado_pm', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}
		$this->db->order_by('descripcion','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_pdtos_lab_auto($datos=FALSE)
	{
		$this->db->select('idproductomaster, descripcion');
		$this->db->from('producto_master');
		$this->db->where('idtipoproducto', '15');
		$this->db->where('estado_pm', 1);
		if( $datos ){ 
			$this->db->ilike('descripcion', $datos['search']);
		}
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_parametros_lab($datos=FALSE)
	{
		$this->db->select('idparametro, descripcion_par, valor_normal_h, valor_normal_m, valor_ambos, separador, descripcion_adicional');
		$this->db->from('parametro');
		$this->db->where('estado_par', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}
		$this->db->order_by('descripcion_par','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_parametros_lab_auto($datos=FALSE)
	{
		$this->db->select('idparametro, descripcion_par, valor_normal_h, valor_normal_m, valor_ambos, separador, descripcion_adicional');
		$this->db->from('parametro');
		$this->db->where('estado_par', 1);
		if( $datos ){
			$this->db->where('separador', $datos['agrupador']);
			$this->db->ilike('descripcion_par', $datos['search']);
		}
		
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_analisis_lab_auto($datos=FALSE) // ANALISIS QUE NO SEAN DE LA SECCION PERFILES
	{
		$this->db->select('anal.idanalisis, anal.descripcion_anal, s.descripcion_sec as seccion');
		$this->db->from('analisis anal');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->where('estado_anal', 1);
		$this->db->where('anal.idseccion <>', 9);
		if( $datos ){ 
			$this->db->ilike('descripcion_anal', $datos['search']);
		}
		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_analisis_perfil($datos=FALSE)
	{
		$this->db->select('anal.idanalisis, anal.descripcion_anal, s.descripcion_sec as seccion');
		$this->db->from('detalle_perfil dp');
		$this->db->join('analisis anal','dp.idanalisis = anal.idanalisis');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->where('dp.idanalisis_perfil', $datos['idanalisis']);
		$this->db->order_by('s.descripcion_sec','ASC');
		$this->db->order_by('anal.descripcion_anal','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_parametros_analisis_id($datos=FALSE)
	{
		$this->db->select('idanalisisparametro, ap.idparametro, ap.idanalisis, par.descripcion_par, pvs.valor_normal_h, pvs.valor_normal_m, pvs.valor_ambos, par.separador, ap.idparent, orden_parametro');
		$this->db->from('analisis_parametro ap');
		$this->db->join('parametro par','ap.idparametro = par.idparametro');
		$this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin'], 'left' );
		$this->db->where('ap.idanalisis', $datos['idanalisis']);
		$this->db->where('par.estado_par', 1);
		$this->db->where('ap.estado_apar', 1);
		$this->db->where('ap.idparent', 0);
		$this->db->where('ap.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->order_by('orden_parametro','ASC');
		$this->db->order_by('orden_subparametro','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_parametros_separador_id($datos=FALSE)
	{
		$this->db->select('idanalisisparametro, ap.idparametro, ap.idanalisis, par.descripcion_par, pvs.valor_normal_h, pvs.valor_normal_m, pvs.valor_ambos, par.separador, ap.idparent');
		$this->db->from('analisis_parametro ap');
		$this->db->join('parametro par','ap.idparametro = par.idparametro');
		$this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin'], 'left' );
		//$this->db->where('ap.idanalisis', $datos['idanalisis']);
		$this->db->where('par.estado_par', 1);
		$this->db->where('ap.estado_apar', 1);
		$this->db->where('ap.idparent', $datos['idanalisisparametro']);
		$this->db->where('ap.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->order_by('orden_parametro','ASC');
		$this->db->order_by('orden_subparametro','ASC');
		$this->db->order_by('idanalisisparametro','ASC');
		return $this->db->get()->result_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_anal' => strtoupper($datos['descripcion']),
			'abreviatura' => strtoupper($datos['abreviatura']),
			'idproductomaster' => (empty($datos['idproductomaster']) ? NULL : $datos['idproductomaster']),
			'idmetodo' => $datos['idmetodo'],
			'tiene_sub' => $datos['subanalisis'],
			'idseccion' => $datos['idseccion'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idanalisis',$datos['id']);
		return $this->db->update('analisis', $data);
	}
	public function m_asignar_parametro($datos)
	{
		$data = array(
			'idanalisis' => $datos['idanalisis'],
			'idparametro' => $datos['idparametro'],
			'idparent' => $datos['idparent'],
			'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin']
		);
		return $this->db->insert('analisis_parametro', $data);
	}
	public function m_asignar_analisis_a_perfil($datos)
	{
		$data = array(
			'idanalisis' => $datos['idanalisis'],
			'idanalisis_perfil' => $datos['idanalisis_perfil']
		);
		return $this->db->insert('detalle_perfil', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_anal' => strtoupper($datos['descripcion']),
			'abreviatura' => (empty($datos['abreviatura']) ? NULL : strtoupper($datos['abreviatura'])),
			'estado_anal' => 1,
			'idproductomaster' => (empty($datos['idproductomaster']) ? NULL : $datos['idproductomaster']),
			'tiene_sub' => $datos['subanalisis'],
			'idseccion' => $datos['idseccion'],
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('analisis', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_anal' => 0
		);
		$this->db->where('idanalisis',$id);
		if($this->db->update('analisis', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular_analisis_parametro($datos)
	{
		$data = array(
			'estado_apar' => 0
		);
		$this->db->where('idanalisis',$datos['idanalisis']);
		$this->db->where('idparametro',$datos['idparametro']);
		$this->db->where('idsedeempresaadmin',$this->sessionHospital['idsedeempresaadmin']);
		if($this->db->update('analisis_parametro', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_anal' => 1
		);
		$this->db->where('idanalisis',$id);
		if($this->db->update('analisis', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_anal' => 2
		);
		$this->db->where('idanalisis',$id);
		if($this->db->update('analisis', $data)){
			return true;
		}else{
			return false;
		}
	}



}