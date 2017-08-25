<?php
class Model_examen extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_examen($paramPaginate){
		//$this->db->select('idexamen, descripcion, estado_ex');
		$this->db->from('examen ex');
		$this->db->join('tipo_examen tex','ex.idtipoexamen = tex.idtipoexamen', 'left');
		$this->db->where('estado_ex <>', 0);
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
	public function m_count_examen($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('examen');
		$this->db->where('estado_ex <>', 0);
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

	public function m_editar($datos)
	{
		$data = array(
			'idtipoexamen' => $datos['idtipoexamen'],
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idexamen',$datos['id']);
		return $this->db->update('examen', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idtipoexamen' => $datos['idtipoexamen'],
			'nombre' => strtoupper($datos['nombre']),
			'descripcion' => $datos['descripcion'],
			'estado_ex' => 1,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
			

		);
		return $this->db->insert('examen', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ex' => 0
		);
		$this->db->where('idexamen',$id);
		if($this->db->update('examen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_ex' => 1
		);
		$this->db->where('idexamen',$id);
		if($this->db->update('examen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_ex' => 2
		);
		$this->db->where('idexamen',$id);
		if($this->db->update('examen', $data)){
			return true;
		}else{
			return false;
		}
	}
}