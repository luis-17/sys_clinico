<?php
class Model_programacion_ambiente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_horas_dia_ambiente($paramDatos, $paramPaginate){ 
		$this->db->select('idambientefecha, idambiente, fecha_evento, hora_evento, idresponsable, comentario, estado_fecha');
		$this->db->from('pa_ambiente_fecha');
		$this->db->where('estado_afe', 1); // activo
		$this->db->where('idambiente', $paramDatos['ambiente']['id']);
		$this->db->where('fecha_evento', $paramDatos['fecha_evento']);
		$this->db->order_by('hora_evento ASC');
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}

		return $this->db->get()->result_array();
	}
	public function m_count_horas_dia_ambiente($paramDatos, $paramPaginate){ 
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_ambiente_fecha');
		$this->db->where('estado_afe', 1); // activo
		$this->db->where('idambiente', $paramDatos['ambiente']['id']);
		$this->db->where('fecha_evento', $paramDatos['fecha_evento']);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_verificar_si_existe_programacion($datos){ 
		$this->db->select('idambientefecha');
		$this->db->from('pa_ambiente_fecha');
		$this->db->where('estado_afe', 1); // activo
		$this->db->where('idambiente',  $datos['idambiente']);
		$this->db->where('fecha_evento', $datos['fecha_evento']);
		$this->db->where('hora_evento', date('H:i', strtotime($datos['descripcion'])));
		$this->db->limit(1);
		$fData = $this->db->get()->row_array();
		return $fData['idambientefecha'];
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idambiente' => $datos['idambiente'],
			'fecha_evento' => $datos['fecha_evento'],
			'hora_evento' => date('H:i', strtotime($datos['descripcion'])),
			'idresponsable' => $datos['idresponsable'],
			'comentario' => $datos['comentario'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('pa_ambiente_fecha', $data);
	}
	function m_editar($datos){
		$data = array(
			'comentario' => $datos['comentario'],
			'updatedAt' => date('Y-m-d H:i:s')
		); 
		
		$this->db->where('idambientefecha',$datos['idambientefecha']);
		return $this->db->update('pa_ambiente_fecha', $data);
	}
	function m_anular($datos){
		$data = array(
			'estado_afe' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		); 
		
		$this->db->where('idambientefecha',$datos['idambientefecha']);
		return $this->db->update('pa_ambiente_fecha', $data);
	}

	public function m_listar_plannig_dias($datos){ 
		$this->db->select('afe.idambiente, (amb.numero_ambiente) AS ambiente, afe.fecha_evento, afe.estado_fecha, count(afe.hora_evento) AS total_horas');
		$this->db->from('pa_ambiente_fecha afe');
		$this->db->join('pa_ambiente amb','afe.idambiente = amb.idambiente');
		$this->db->where('afe.estado_afe', 1); // activo
		$this->db->where(' amb.idsede', $datos['idsede']); // activo
		$this->db->where('afe.fecha_evento >= ', $datos['fecha1']);
		$this->db->where('afe.fecha_evento <= ', $datos['fecha2']);
		$this->db->group_by(array("afe.idambiente","amb.numero_ambiente", "afe.fecha_evento", "afe.estado_fecha"));
		$this->db->order_by('amb.numero_ambiente ASC, afe.idambiente ASC, afe.fecha_evento ASC');
		return $this->db->get()->result_array();
	}

	public function m_listar_detalle_plannig_dias($datos){ 
		$this->db->select('afe.idambiente, afe.fecha_evento, afe.estado_fecha, afe.hora_evento, afe.comentario, afe.idresponsable');
		$this->db->select("(emp.apellido_paterno || ' ' || emp.apellido_materno || ', ' || emp.nombres) AS responsable ");
		$this->db->from('pa_ambiente_fecha afe');
		//$this->db->join('pa_ambiente amb','afe.idambiente = amb.idambiente');
		$this->db->join('rh_empleado emp','afe.idresponsable = emp.idempleado');
		$this->db->where('afe.estado_afe', 1); // activo
		//$this->db->where(' amb.idsede', $datos['idsede']); // sede
		$this->db->where(' afe.idambiente', $datos['idambiente']); // ambiente
		$this->db->where('afe.fecha_evento', $datos['fecha']); //fecha seleccionada
		$this->db->order_by('afe.hora_evento ASC');
		return $this->db->get()->result_array();
	}

	public function m_listar_plannig_horas($datos){ 
		$this->db->select('afe.idambiente, afe.fecha_evento, afe.estado_fecha, afe.hora_evento, afe.comentario, afe.idresponsable');
		$this->db->select('(amb.numero_ambiente) AS ambiente');
		$this->db->select("(emp.apellido_paterno || ' ' || emp.apellido_materno || ', ' || emp.nombres) AS responsable ");
		$this->db->from('pa_ambiente_fecha afe');
		$this->db->join('pa_ambiente amb','afe.idambiente = amb.idambiente');
		$this->db->join('rh_empleado emp','afe.idresponsable = emp.idempleado');
		$this->db->where('afe.estado_afe', 1); // activo
		$this->db->where(' amb.idsede', $datos['idsede']); // sede
		//$this->db->where(' afe.idambiente', $datos['idambiente']); // ambient
		$this->db->where('afe.fecha_evento', $datos['fecha1']); //fecha seleccionada
		$this->db->order_by('afe.hora_evento ASC, amb.numero_ambiente ASC');
		return $this->db->get()->result_array();
	}

	public function m_verificar_operatividad_ambiente($datos){
		$this->db->select('count(*) AS result');
		$this->db->from('pa_ambiente_fecha');
		$this->db->where('estado_afe', 1); //activo
		$this->db->where('idambiente', $datos['idambiente']); // ambiente
		$this->db->where("fecha_evento = '". $datos['fecha_programada']  . "'"); //fecha
		
		//horas no estan en rango de inoperatividad		
		$where1 = "('". $datos['hora_inicio']. "' = hora_evento )" ;		
		$where2 = "('". $datos['hora_fin_comparar']. "' = hora_evento )" ;		

		$this->db->where('( ' . $where1 . ' OR ' . $where2 . ' )');
		$fData = $this->db->get()->row_array();
		
		return $fData['result'];
	}
}