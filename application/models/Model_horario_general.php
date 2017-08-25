<?php
class Model_horario_general extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_horario_general($datos){ 
		$this->db->select('e.idempleado, numero_documento, nombres, apellido_paterno, apellido_materno, 
			idhorarioempleado, h.idhorario, hora_entrada, hora_salida, hora_desde_entrada, hora_hasta_salida, hora_hasta_entrada, hora_desde_salida, 
			tiempo_tolerancia, horas_trabajadas, h.descripcion');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_empleado he','e.idempleado = he.idempleado AND estado_he = 1');
		$this->db->join('rh_horario h','he.idhorario = h.idhorario AND estado_h = 1');
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('e.idempleado', $datos['id']);
		$this->db->order_by('idhorario','ASC');
		return $this->db->get()->result_array();
	}
	public function m_agregar_horario_empleado($datos)
	{
		$data = array(
			'idempleado'=> $datos['idempleado'],
			'idhorario'=> $datos['idhorario'],
			'hora_desde_entrada'=> $datos['entrada']['desde_entrada'],
			'hora_entrada'=> $datos['entrada']['entrada'],
			'hora_hasta_entrada'=> $datos['entrada']['hasta_entrada'],
			'hora_desde_salida'=> $datos['salida']['desde_salida'],
			'hora_salida'=> $datos['salida']['salida'],
			'hora_hasta_salida'=> $datos['salida']['hasta_salida'],
			'tiempo_tolerancia'=> $datos['tiempo_tolerancia'],
			'horas_trabajadas'=> $datos['horas_trabajadas']
		);
		return $this->db->insert('rh_horario_empleado',$data);
	}
	public function m_obtener_horario_de_empleado($idempleado,$idhorario)
	{
		$this->db->select('idhorarioempleado, h.idhorario, h.descripcion, hora_entrada, hora_salida, hora_desde_entrada, hora_hasta_entrada, hora_desde_salida, hora_hasta_salida , tiempo_tolerancia, horas_trabajadas');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_empleado he','e.idempleado = he.idempleado AND estado_he = 1');
		$this->db->join('rh_horario h','he.idhorario = h.idhorario AND estado_h = 1');
		$this->db->where('estado_he', 1); // habilitado
		$this->db->where('estado_h', 1); 
		$this->db->where('e.idempleado', $idempleado);
		$this->db->where('h.idhorario', $idhorario);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_horario_general_de_empleado($idempleado,$nombrehorario)
	{
		$this->db->select('idhorarioempleado, h.idhorario, h.descripcion, hora_entrada, hora_salida, hora_desde_entrada, hora_hasta_entrada, hora_desde_salida, hora_hasta_salida, tiempo_tolerancia, horas_trabajadas');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_empleado he','e.idempleado = he.idempleado AND estado_he = 1');
		$this->db->join('rh_horario h','he.idhorario = h.idhorario AND estado_h = 1');
		$this->db->where('estado_he', 1); // habilitado 
		$this->db->where('estado_h', 1); 
		$this->db->where('e.idempleado', $idempleado);
		$this->db->where('h.descripcion', $nombrehorario);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_eliminar_horario_de_empleado($idhorarioempleado)
	{
		$data = array(
			'estado_he'=> 0,
		);
		$this->db->where('idhorarioempleado', $idhorarioempleado);
		return $this->db->update('rh_horario_empleado',$data);
	}
}