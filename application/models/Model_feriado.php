<?php
class Model_feriado extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_feriados($paramPaginate, $paramDatos){ 
		$this->db->select('idferiado, fecha, estado_fe, descripcion');
		$this->db->from('rh_feriado');
		$this->db->where('estado_fe', 1); // activo
		$this->db->where("date_part('year', fecha) = ".$paramDatos['anyo']['descripcion']);
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}else{
			$this->db->order_by('fecha', 'ASC');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_feriados($paramPaginate, $paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_feriado');
		$this->db->where('estado_fe', 1); // activo
		$this->db->where("date_part('year', fecha) = ".$paramDatos['anyo']['descripcion']);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_feriados_entre_fechas($datos)
	{
		$this->db->select('idferiado, fecha, estado_fe, descripcion');
		$this->db->from('rh_feriado');
		$this->db->where('estado_fe', 1); // activo
		$this->db->where('fecha BETWEEN '. $this->db->escape($datos['desde']) .' AND '. $this->db->escape($datos['hasta']));
		return $this->db->get()->result_array();
	}
	public function m_registrar($datos)
	{
		$data = array(
			'fecha' => $datos,

		);
		return $this->db->insert('rh_feriado', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_fe' => 0
		);
		$this->db->where('idferiado',$id);
		if($this->db->update('rh_feriado', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_lista_feriados_cbo($paramDatos){ 
		$anioSig = intval($paramDatos['anyo'] + 1);
		$this->db->select('idferiado, fecha, estado_fe, descripcion');
		$this->db->from('rh_feriado');
		$this->db->where('estado_fe', 1); // activo
		$this->db->where("date_part('year', fecha) = ".$paramDatos['anyo'] . " OR  date_part('year', fecha) = " . $anioSig);
		$this->db->order_by('fecha', 'ASC');
		
		return $this->db->get()->result_array();
	}

	public function m_count_feriados_entre_fechas($datos){
		$this->db->select('COUNT(idferiado) as contador');
		$this->db->from('rh_feriado');
		$this->db->where('estado_fe', 1); // activo
		$this->db->where('fecha BETWEEN '. $this->db->escape($datos['desde']) .' AND '. $this->db->escape($datos['hasta']));
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
}