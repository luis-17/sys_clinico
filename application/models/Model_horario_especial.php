<?php
class Model_horario_especial extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_horario_especial($datos){ 
		$this->db->select('mh.idmotivohe, mh.descripcion_mh, he.idhorarioespecial, fecha_especial, hora_entrada, hora_salida, hora_desde_entrada, hora_hasta_salida, 
			 hora_hasta_entrada, hora_desde_salida, si_licencia, tiempo_tolerancia, horas_trabajadas, e.idempleado, nombres, apellido_paterno, apellido_materno, smh.idsubmotivohe , smh.descripcion_smh');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_especial he','e.idempleado = he.idempleado');
		$this->db->join('rh_motivo_he mh','he.idmotivohe = mh.idmotivohe');
		$this->db->join('rh_submotivo_he smh','he.idsubmotivohe = smh.idsubmotivohe','left'); /* correccion para q se carguen los datos anteriores que se hayan guardado sin submotivo*/
		$this->db->where('estado_empl', 1); // habilitado
		$this->db->where('estado_hesp', 1); // habilitado
		// $this->db->where('estado_mh', 1); // habilitado
		$this->db->where('e.idempleado', $datos['id']); 
		$this->db->order_by('fecha_especial','DESC'); 
		return $this->db->get()->result_array();
	}

	public function m_obtener_horario_especial_de_empleado($idEmpleado,$fechaEspecial) 
	{
		$this->db->select('mh.idmotivohe, mh.descripcion_mh, he.idhorarioespecial, fecha_especial, hora_entrada, hora_salida, hora_desde_entrada, hora_desde_salida, 
			 hora_hasta_entrada, hora_hasta_salida, si_licencia, tiempo_tolerancia, horas_trabajadas, 
			e.idempleado, nombres, apellido_paterno, apellido_materno');
		$this->db->from('rh_empleado e');
		$this->db->join('rh_horario_especial he','e.idempleado = he.idempleado');
		$this->db->join('rh_motivo_he mh','he.idmotivohe = mh.idmotivohe');
		$this->db->where('estado_empl', 1); // habilitado 
		$this->db->where('estado_hesp', 1); // habilitado 
		// $this->db->where('estado_mh', 1); // habilitado 
		$this->db->where('e.idempleado', $idEmpleado); 
		$this->db->where('fecha_especial', $fechaEspecial); 
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}	
	public function m_agregar_horario_empleado($datos)
	{
		$data = array(
			'idempleado'=> $datos['idempleado'],
			'idmotivohe'=> $datos['idmotivo'],
			'fecha_especial'=> $datos['fecha_especial'],
			'hora_hasta_entrada'=> ($datos['hasta_entrada'] == '-') ? NULL:$datos['hasta_entrada'],
			'hora_desde_salida'=> ($datos['desde_salida'] == '-') ? NULL:$datos['desde_salida'], 
			'hora_desde_entrada'=> ($datos['desde_entrada'] == '-') ? NULL:$datos['desde_entrada'],
			'hora_hasta_salida'=> ($datos['hasta_salida'] == '-') ? NULL:$datos['hasta_salida'], 
			'hora_entrada'=> ($datos['entrada'] == '-') ? NULL:$datos['entrada'],
			'hora_salida'=> ($datos['salida'] == '-') ? NULL:$datos['salida'], 
			'tiempo_tolerancia'=> ($datos['tiempo_tolerancia'] == '-') ? NULL:$datos['tiempo_tolerancia'],
			'horas_trabajadas'=> ($datos['horas_trabajadas'] == '-') ? '00:00':$datos['horas_trabajadas'],
			'idsubmotivohe'=> empty($datos['idsubmotivo']) ? NULL : $datos['idsubmotivo']
			// 'si_licencia'=> ($datos['asistencia'] == 'NA') ? 1 : 2
		);
		return $this->db->insert('rh_horario_especial',$data);
	}
	public function m_eliminar_horario_de_empleado($idhorarioespecial)
	{
		$data = array( 
			'estado_hesp'=> 0 
		);
		$this->db->where('idhorarioespecial', $idhorarioespecial);
		return $this->db->update('rh_horario_especial',$data);
	}
}