<?php
class Model_pariente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_parientes_de_empleado_para_pdf($paramDatos) 
	{
		$this->db->select('idpariente, idempleado, nombres, apellido_paterno, apellido_materno, parentesco, fecha_nacimiento, 
			ocupacion, vive, estado_civil, direccion, telefono, notificar_emergencia'); 
		$this->db->from('rh_pariente'); 
		$this->db->where('estado_par', 1);
		$this->db->where('idempleado', $paramDatos['id']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_parientes_de_empleado($paramPaginate,$paramDatos) 
	{
		$this->db->select('idpariente, idempleado, nombres, apellido_paterno, apellido_materno, parentesco, fecha_nacimiento, 
			ocupacion, vive, estado_civil, direccion, telefono, notificar_emergencia'); 
		$this->db->from('rh_pariente'); 
		$this->db->where('estado_par', 1);
		$this->db->where('idempleado', $paramDatos['id']);
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
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_parientes_de_empleado($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('rh_pariente'); 
		$this->db->where('estado_par', 1);
		$this->db->where('idempleado', $paramDatos['id']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_verificar_si_existe_pariente($datos){
		$this->db->select('*');
		$this->db->from('rh_pariente');
		$this->db->ilike("(nombres || ' ' ||apellido_paterno || ' ' || apellido_materno)", strtoupper($datos['pariente']) );
		$this->db->limit(1);
		if ( $this->db->get()->num_rows() > 0 ){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	public function m_agregar_pariente($datos)
	{
		$data = array(
			'idempleado' => $datos['idempleado'],
			'nombres' => strtoupper($datos['nombres']),
			'apellido_paterno' => strtoupper($datos['ap_paterno']),
			'apellido_materno' => strtoupper($datos['ap_materno']),
			'parentesco' => $datos['parentesco'],
			'fecha_nacimiento' => empty($datos['fecha_nac']) ? NULL : $datos['fecha_nac'],
			'ocupacion' => empty($datos['ocupacion']) ? NULL : $datos['ocupacion'],
			'vive' => $datos['vive_obj']['id'],
			'estado_civil' => empty($datos['estado_civil_obj']['id']) ? NULL : $datos['estado_civil_obj']['id'],
			'direccion' => empty($datos['direccion']) ? NULL : $datos['direccion'],
			'telefono' => empty($datos['telefono']) ? NULL : $datos['telefono'],
			'notificar_emergencia' => (@$datos['notificar_emergencia'] == 'SI' ? 1 : 2 )
		);
		return $this->db->insert('rh_pariente', $data);
	}
	public function m_anular_pariente($id)
	{
		$data = array(
			'estado_par' => 0 
		);
		$this->db->where('idpariente',$id);
		return $this->db->update('rh_pariente', $data);
	}
	public function m_editar_pariente($datos)
	{
		$data = array(
			'nombres' => $datos['nombres'],
			'apellido_paterno' => $datos['ap_paterno'],
			'apellido_materno' => $datos['ap_materno'],
			'parentesco' => $datos['parentesco'],
			'fecha_nacimiento' => empty($datos['fecha_nacimiento']) ? NULL : $datos['fecha_nacimiento'],
			'ocupacion' => empty($datos['ocupacion']) ? NULL : $datos['ocupacion'],
			'vive' => $datos['vive']['id'],
			'estado_civil' => empty($datos['estado_civil']['id']) ? NULL : $datos['estado_civil']['id'],
			'direccion' => empty($datos['direccion']) ? NULL : $datos['direccion'],
			'telefono' => empty($datos['telefono']) ? NULL : $datos['telefono'],
			'notificar_emergencia' => (@$datos['notificar_emergencia'] == 'SI' ? 1 : 2 )
		);
		$this->db->where('idpariente',$datos['idpariente']);
		return $this->db->update('rh_pariente', $data);
	}
}