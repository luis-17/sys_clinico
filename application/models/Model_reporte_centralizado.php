<?php
class Model_reporte_centralizado extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_reportes_agregados_al_usuario($paramPaginate,$datos)
	{
		$this->db->select("upr.idusersporreporte, tr.idtiporeporte, tr.descripcion_trp, re.idreporte, nombre_rp, descripcion_rp, abreviatura_rp",FALSE);
		$this->db->from('reporte re');
		$this->db->join('tipo_reporte tr','re.idtiporeporte = tr.idtiporeporte AND estado_trp = 1');
		$this->db->join('users_por_reporte upr','re.idreporte = upr.idreporte');
		$this->db->where('estado_rp', 1);
		$this->db->where('upr.idusers', $datos['id']);
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_reportes_agregados_al_usuario($paramPaginate,$datos)
	{
		$this->db->select("COUNT(*) AS contador",FALSE);
		$this->db->from('reporte re');
		$this->db->join('tipo_reporte tr','re.idtiporeporte = tr.idtiporeporte AND estado_trp = 1');
		$this->db->join('users_por_reporte upr','re.idreporte = upr.idreporte');
		$this->db->where('estado_rp', 1);
		$this->db->where('idusers', $datos['id']);
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_cargar_reportes_no_agregados_al_usuario($paramPaginate,$datos){ 
		$sql = 'SELECT tr.idtiporeporte, tr.descripcion_trp ,re.idreporte, nombre_rp, descripcion_rp, abreviatura_rp 
		FROM reporte re 
		INNER JOIN tipo_reporte tr ON re.idtiporeporte = tr.idtiporeporte 
		WHERE re.idreporte NOT IN( 
			SELECT upr.idreporte 
			FROM users_por_reporte upr 
			WHERE upr.idusers = '.$datos['id'].'
		)
		AND re.estado_rp = 1';
		if( $paramPaginate['sortName'] ){
			$sql.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}
		$query = $this->db->query($sql);
		return $query->result_array();
	}
	public function m_count_reportes_no_agregados_al_usuario($paramPaginate,$datos)
	{
		$sql = 'SELECT COUNT(*) AS contador
		FROM reporte re 
		INNER JOIN tipo_reporte tr ON re.idtiporeporte = tr.idtiporeporte 
		WHERE re.idreporte NOT IN( 
			SELECT upr.idreporte 
			FROM users_por_reporte upr 
			WHERE upr.idusers = '.$datos['id'].'
		)
		AND re.estado_rp = 1';
		$query = $this->db->query($sql);
		$fila = $query->row_array();
		return $fila;
	}
	public function m_cargar_reportes_de_usuario_session()
	{
		$this->db->select("trp.idtiporeporte, abreviatura_trp, trp.descripcion_trp, rp.idreporte, abreviatura_rp, nombre_rp, descripcion_rp",FALSE);
		$this->db->from('reporte rp');
		$this->db->join('tipo_reporte trp','rp.idtiporeporte = trp.idtiporeporte AND estado_trp = 1');
		$this->db->join('users_por_reporte upr','rp.idreporte = upr.idreporte');
		$this->db->where('estado_rp', 1);
		$this->db->where('upr.idusers', $this->sessionHospital['idusers']);
		$this->db->order_by('rp.idreporte', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_agregar_reporte_a_usuario($datos) 
	{
		$data = array(
			'idusers' => $datos['iduser'],
			'idreporte' => $datos['idreporte'],
			'createdAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('users_por_reporte',$data);
	}
	public function m_quitar_reporte_a_usuario($id)
	{
		$query_result = $this->db->delete('users_por_reporte', array('idusersporreporte' => $id )); 
		if(!$query_result) {
		    return false;
		}else{
			return true;
		}
	}
}