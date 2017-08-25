<?php
class Model_documento_empleado extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_documento_empleado($paramPaginate,$paramDatos){ 
		$this->db->select('iddocumentoempleado, u.idusers, idempleado, titulo_doc, descripcion_doc, nombre_archivo, fecha_entrega, fecha_subida, u.username'); 
		$this->db->from('rh_documento_empleado de');
		$this->db->join('users u','de.iduserssubida = u.idusers'); 
		$this->db->where('estado_de', 1); // activo 
		$this->db->where('idempleado', $paramDatos['idempleado']);
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
	public function m_count_documento_empleado($paramPaginate,$paramDatos) 
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_documento_empleado de');
		$this->db->join('users u','de.iduserssubida = u.idusers'); 
		$this->db->where('estado_de', 1); // activo 
		$this->db->where('idempleado', $paramDatos['idempleado']);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_editar($datos)
	{
		$data = array( 
			'titulo_doc' => strtoupper($datos['titulo_doc']),
			'descripcion_doc' => nl2br($datos['descripcion_doc']),
			'nombre_archivo' => $datos['nombre_archivo'],
			'fecha_entrega' => $datos['fecha_entrega'], 
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddocumentoempleado',$datos['id']); // idusersubida
		return $this->db->update('rh_documento_empleado', $data);
	}
	public function m_registrar($datos)
	{
		$data = array( 
			'iduserssubida' => $this->sessionHospital['idusers'],
			'idempleadosubida' => $this->sessionHospital['idempleado'],
			'idempleado' => $datos['idempleado'],
			'titulo_doc' => strtoupper($datos['titulo']),
			'descripcion_doc' => nl2br($datos['descripcion']),
			'nombre_archivo' => $datos['nombre_archivo'],
			'fecha_entrega' => empty($datos['fecha_entrega']) ? NULL : $datos['fecha_entrega'], 
			'fecha_subida' => date('Y-m-d H:i:s'),
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('rh_documento_empleado', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_de' => 0
		);
		$this->db->where('iddocumentoempleado',$id);
		if($this->db->update('rh_documento_empleado', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_cargar_cv($datos){
		$this->db->select("e.nombre_cv");
		$this->db->from('rh_empleado e');
		$this->db->where("e.idempleado", $datos['idempleado']);
		$fData = $this->db->get()->row_array();
    	return empty($fData['nombre_cv']) ? FALSE : $fData['nombre_cv']; 		
	}

	public function m_registrar_cv($datos)
	{
		$data = array(			
			'idempleado' => $datos['idempleado'],
			'nombre_cv' => $datos['nombre_archivo']);
		
		$this->db->where('idempleado', $datos['idempleado']);
		return $this->db->update('rh_empleado', $data);
	}
}