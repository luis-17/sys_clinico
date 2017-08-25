<?php
class Model_laboratorio extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_laboratorios_cbo($datos = FALSE){ 
		$this->db->select('idlaboratorio, nombre_lab, estado_lab');
		$this->db->from('far_laboratorio');
		$this->db->where('estado_lab', 1); // activo
		$this->db->order_by('nombre_lab','ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_laboratorios_por_autocompletado($datos = FALSE)
	{
		$this->db->select('idlaboratorio, nombre_lab, estado_lab');
		$this->db->from('far_laboratorio');
		if( $datos ){ 
			$this->db->ilike('nombre_lab', $datos['search']);
		}
		$this->db->where('estado_lab', 1); // activo 
		$this->db->limit(50);
		return $this->db->get()->result_array();
	}
	public function m_cargar_laboratorio($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('far_laboratorio');
		$this->db->where('estado_lab <>', 0);
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

	public function m_buscar_laboratorio($datos){ 
		$this->db->select('COUNT(*) as conteo'); 
		$this->db->from('far_laboratorio'); 
		$this->db->where('nombre_lab', strtoupper($datos['descripcion'])); // activo 
		$fData = $this->db->get()->row_array();
		return $fData['conteo'];
	}

	public function m_count_laboratorio()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_laboratorio');
		$this->db->where('estado_lab <>', 0);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_editar($datos)
	{
		$data = array(
			'nombre_lab' => strtoupper($datos['descripcion'])
		);
		$this->db->where('idlaboratorio',$datos['id']);
		return $this->db->update('far_laboratorio', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'nombre_lab' => strtoupper($datos['descripcion']),
			'estado_lab' => 1,
		);
		return $this->db->insert('far_laboratorio', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_lab' => 0
		);
		$this->db->where('idlaboratorio',$id);
		if($this->db->update('far_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_lab' => 1
		);
		$this->db->where('idlaboratorio',$id);
		if($this->db->update('far_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_lab' => 2
		);
		$this->db->where('idlaboratorio',$id);
		if($this->db->update('far_laboratorio', $data)){
			return true;
		}else{
			return false;
		}
	}

}