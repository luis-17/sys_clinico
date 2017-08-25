<?php
class Model_ct_caja_chica extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ct_caja_chica_cbo($datos=FALSE)
	{
		$this->db->select('idcajachica, nombre, idcentrocosto');
		$this->db->from('ct_caja_chica');
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_caja_chica($paramPaginate){
		$this->db->select('cch.idcajachica, cch.nombre, cch.idsedeempresaadmin, cch.idcentrocosto, cch.estado_cch, cc.idsubcatcentrocosto');
		$this->db->select("cc.nombre_cc centro_costo, cc.codigo_cc, concat_ws(' - ',ea.razon_social, s.descripcion) AS empresa_admin",FALSE);
		$this->db->select("ea.razon_social AS empresa, s.descripcion AS sede",FALSE);
		$this->db->select("cch.idusuarioresponsable, e.apellido_paterno, e.apellido_materno, e.nombres");
		$this->db->select("cch.numero_cheque, cch.monto_cheque::numeric as monto_cheque",FALSE);
		$this->db->from('ct_caja_chica cch');
		$this->db->join('ct_centro_costo cc', 'cch.idcentrocosto = cc.idcentrocosto');
		$this->db->join('sede_empresa_admin sea', 'cch.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('empresa_admin ea', 'sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('rh_empleado e','e.idempleado = cch.idusuarioresponsable', 'left');
		$this->db->where('estado_cch <>', 0);
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
	public function m_count_caja_chica()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ct_caja_chica');
		$this->db->where('estado_cch <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'nombre' => strtoupper_total($datos['descripcion']),
			'idsedeempresaadmin' => $datos['idsedeempresa'],
			'idcentrocosto' => $datos['idcentrocosto'],
		);
		$this->db->where('idcajachica',$datos['id']);
		return $this->db->update('ct_caja_chica', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre' => strtoupper_total($datos['descripcion']),
			'estado_cch' => 1,
			'idsedeempresaadmin' => $datos['idsedeempresa'],
			'idcentrocosto' => $datos['idcentrocosto'],
		);
		return $this->db->insert('ct_caja_chica', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_cch' => 0
		);
		$this->db->where('idcajachica',$id);
		if($this->db->update('ct_caja_chica', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_cch' => 1
		);
		$this->db->where('idcajachica',$id);
		if($this->db->update('ct_caja_chica', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_cch' => 2
		);
		$this->db->where('idcajachica',$id);
		if($this->db->update('ct_caja_chica', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_asignar($datos){
		$data = array(
			'numero_cheque' => $datos['numero_cheque'],
			'monto_cheque' => $datos['monto_cheque'],
			'idusuarioresponsable' => $datos['idresponsable'],
		);
		$this->db->where('idcajachica',$datos['id']);
		return($this->db->update('ct_caja_chica', $data));		
	}


}