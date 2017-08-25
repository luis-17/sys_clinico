<?php
class Model_parametro extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_parametro_cbo($datos=FALSE)
	{
		$this->db->select('idparametro, descripcion_par');
		$this->db->from('parametro');
		$this->db->where('estado_par', 1);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	/* ANTERIOR */
	public function m_cargar_parametro($paramPaginate){
		if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
			$idsedeempresaadmin = 9;
		}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
			$idsedeempresaadmin = 8;
		}
		$this->db->select('par.idparametro, par.descripcion_par, par.estado_par');
		$this->db->select('pvs.valor_normal_h, pvs.valor_normal_m, pvs.valor_ambos, par.separador');
		$this->db->from('parametro par');
		$this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$idsedeempresaadmin, 'left' );
		$this->db->where('estado_par <>', 0);
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
	public function m_count_parametro($paramPaginate)
	{
		if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
			$idsedeempresaadmin = 9;
		}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
			$idsedeempresaadmin = 8;
		}
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('parametro par');
		$this->db->join('parametro_valor_sede pvs', 'par.idparametro = pvs.idparametro AND pvs.idsedeempresaadmin = '.$idsedeempresaadmin, 'left' );
		$this->db->where('estado_par <>', 0);
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
	/* NUEVO */
	public function m_cargar_parametros($paramPaginate){
		$this->db->select('par.idparametro, par.descripcion_par, par.estado_par, par.separador, par.combo, par.nombre_combo,
			par.texto_adicional, par.descripcion_adicional');
		$this->db->from('parametro par');
		$this->db->where('estado_par <>', 0);
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
	public function m_count_parametros($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('parametro par');
		$this->db->where('estado_par <>', 0);
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
	public function m_cargar_valores_parametro($datos){
		$this->db->select('idparametrovalorsede, idparametro, idsedeempresaadmin');
		// $this->db->select("CASE WHEN tipo_rango = 1 THEN 'DIAS' ELSE( CASE WHEN tipo_rango = 2 THEN 'MESES' ELSE 'AÑOS' END) END AS tipo_rango");
		$this->db->select('valor_normal_h, valor_normal_m, valor_ambos');
		// $this->db->select('min_rango, max_rango, valor_etario_h, valor_etario_m');
		$this->db->select('valor_json::json', FALSE);
		$this->db->from('parametro_valor_sede');
		$this->db->where('idparametro', $datos['id']);
		$this->db->where('idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	/* **** */
	public function m_buscar_valores_sede($datos){ // REVISAR SI SE VA A UTILIZAR CON LA NUEVA MODALIDAD

		if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
			$idsedeempresaadmin = 9;
		}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
			$idsedeempresaadmin = 8;
		}
		$this->db->select('*');
		$this->db->from('parametro_valor_sede');
		$this->db->where('idparametro', $datos['id']);
		$this->db->where('idsedeempresaadmin', $idsedeempresaadmin);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_par' => strtoupper($datos['descripcion']),
			// 'valor_normal_h' => $datos['valorNormalHombres'],
			// 'valor_normal_m' => $datos['valorNormalMujeres'],
			// 'valor_ambos' => $datos['valorAmbos']['bool'],
			'separador' => $datos['separador'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idparametro',$datos['id']);
		return $this->db->update('parametro', $data);
	}
	public function m_editar_valores($datos)
	{
		if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
			$idsedeempresaadmin = 9;
		}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
			$idsedeempresaadmin = 8;
		}
		$data = array(
			'valor_normal_h' => empty($datos['valorNormalHombres'])? NULL : $datos['valorNormalHombres'],
			'valor_normal_m' => empty($datos['valorNormalMujeres'])? NULL : $datos['valorNormalMujeres'],
			'valor_ambos' => empty($datos['valorAmbos']['bool'])? NULL : $datos['valorAmbos']['bool'],
			'valor_json' => empty($datos['valor_json'])? NULL : $datos['valor_json'],
		);
		$this->db->where('idparametrovalorsede',$datos['idparametrovalorsede']);
		$this->db->where('idsedeempresaadmin', $idsedeempresaadmin);
		return $this->db->update('parametro_valor_sede', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_par' => strtoupper($datos['descripcion']),
			// 'valor_normal_h' => isset($datos['valorNormalHombres'])? $datos['valorNormalHombres'] : null,
			// 'valor_normal_m' => isset($datos['valorNormalMujeres'])? $datos['valorNormalMujeres'] : null,
			// 'valor_ambos' => $datos['valorAmbos']['bool'],
			'separador' => $datos['separador'],
			'estado_par' => 1,
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('parametro', $data);
	}
	public function m_registrar_valores($datos)
	{
		if($this->sessionHospital['id_empresa_admin'] == 38){ // MEDICINA INTEGRAL
			$idsedeempresaadmin = 9;
		}elseif( $this->sessionHospital['id_empresa_admin'] == 39 ){ // GM GESTORES
			$idsedeempresaadmin = 8;
		}

		$data = array(
			'idparametro' => $datos['idparametro'],
			'valor_normal_h' => empty($datos['valorNormalHombres'])? null : $datos['valorNormalHombres'],
			'valor_normal_m' => empty($datos['valorNormalMujeres'])? null : $datos['valorNormalMujeres'],
			'valor_ambos' => $datos['valorAmbos']['bool'],
			'idsedeempresaadmin' => $idsedeempresaadmin,
		);
		return $this->db->insert('parametro_valor_sede', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_par' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idparametro',$id);
		if($this->db->update('parametro', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_par' => 1,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idparametro',$id);
		if($this->db->update('parametro', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_par' => 2,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idparametro',$id);
		if($this->db->update('parametro', $data)){
			return true;
		}else{
			return false;
		}
	}



}