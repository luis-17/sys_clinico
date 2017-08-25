<?php
class Model_documento_interno extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_documento_interno_intranet()
	{
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado", FALSE);
		$this->db->select('iddocumentointerno, em.idempleado, nombre_documento, nombre_archivo, idsedeempresaadmin,fecha_subida');
		$this->db->from('intr_documento_interno di');
		$this->db->join('rh_empleado em','di.idempleado = em.idempleado');
		$this->db->where('estado_di', 1);
		$this->db->order_by('fecha_subida', 'DESC');
		// $this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_documento_interno($paramPaginate){
		$this->db->select("(nombres || ' ' || apellido_paterno || ' ' || apellido_materno) AS empleado", FALSE);
		$this->db->select('iddocumentointerno, emp.idempleado, nombre_documento, nombre_archivo, idsedeempresaadmin,fecha_subida');
		$this->db->from('intr_aviso av');
		$this->db->join('rh_empleado emp','av.idempleado = emp.idempleado');
		$this->db->where('estado_av <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		//sleep(1);
		return $this->db->get()->result_array();
	}
	public function m_count_avisos($paramPaginate)
	{
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('intr_aviso');
		$this->db->where('estado_av <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
				}
			}
		}
		$filas = $this->db->get()->num_rows();
		return $filas;
	}
	public function m_editar($datos)
	{
		$data = array(
			'titulo' => strtoupper($datos['titulo']),
			'redaccion' => nl2br($datos['redaccion']),
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idaviso',$datos['id']);
		return $this->db->update('intr_aviso', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idsedeempresaadmin'=> $this->sessionHospital['idsedeempresaadmin'],
			'iduser'=> $this->sessionHospital['idusers'],
			'idempleado'=> $this->sessionHospital['idempleado'],
			'titulo'=> strtoupper($datos['titulo']),
			'redaccion'=> nl2br($datos['redaccion']),
			'fecha_creacion'=> date('Y-m-d H:i:s'),
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s')			
		);
		return $this->db->insert('intr_aviso', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_av' => 0
		);
		$this->db->where('idaviso',$id);
		if($this->db->update('intr_aviso', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_av' => 1
		);
		$this->db->where('idaviso',$id);
		if($this->db->update('intr_aviso', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_av' => 2
		);
		$this->db->where('idaviso',$id);
		if($this->db->update('intr_aviso', $data)){
			return true;
		}else{
			return false;
		}
	}
}