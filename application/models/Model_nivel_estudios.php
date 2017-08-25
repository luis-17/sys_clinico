<?php
class Model_nivel_estudios extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_nivel_estudio_por_tipo($tipo) 
	{
		$this->db->select('idnivelestudio, descripcion_ne, tipo_ne, orden, estado_ne'); 
		$this->db->from('rh_nivel_estudio'); 
		$this->db->where('estado_ne', 1);
		$this->db->where('tipo_ne', $tipo);
		$this->db->order_by('orden', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_estudios_empleado($paramDatos) 
	{
		$this->db->select('de.iddetalleestudio, rne.idnivelestudio, rne.descripcion_ne, de.especialidad, de.centro_estudio, de.fecha_desde, de.fecha_hasta, de.estudio_completo, de.grado_academico, rne.tipo_ne'); 
		$this->db->from('rh_detalle_estudio de');
		$this->db->join('rh_nivel_estudio rne', 'de.idnivelestudio = rne.idnivelestudio');
		$this->db->where('estado_ne', 1);
		$this->db->where('estado_de', 1);
		$this->db->where('idempleado', $paramDatos['id']);
		$this->db->order_by('orden', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_agregar_estudio_a_empleado($datos) 
	{
		$data = array(
			'idempleado' => $datos['idempleado'],
			'idnivelestudio' => $datos['id'],
			'especialidad' => strtoupper($datos['especialidad']),
			'centro_estudio' => strtoupper($datos['centro_estudio']),
			'fecha_desde' => $datos['fecha_desde'],
			'fecha_hasta' => $datos['fecha_hasta'],
			'estudio_completo' => $datos['estudio_completo'],
			'grado_academico' => $datos['grado_academico'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);	
		return $this->db->insert('rh_detalle_estudio', $data);
	}
	public function m_editar_estudio_a_empleado($datos)
	{
		$data = array(
			'idnivelestudio' => $datos['nivel_estudio']['id'],
			'especialidad' => strtoupper($datos['especialidad']),
			'centro_estudio' => strtoupper($datos['centro_estudio']),
			'fecha_desde' => $datos['fecha_desde'],
			'fecha_hasta' => $datos['fecha_hasta'],
			'estudio_completo' => $datos['estudio_completo'],
			'grado_academico' => $datos['grado_academico'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetalleestudio', $datos['iddetalleestudio']);
		return $this->db->update('rh_detalle_estudio', $data);
	}
	public function m_anular_estudio($id)
	{
		$data = array(
			'estado_de' => 0 
		);
		$this->db->where('iddetalleestudio',$id);
		return $this->db->update('rh_detalle_estudio', $data);
	}
	
}