<?php
class Model_planilla extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_planillas($paramPaginate,$paramEmpresa){ 
		$this->db->select('p.idplanilla, p.descripcion_pl, p.estado_pl, p.conceptos_json::JSON as conceptos_json',FALSE);
		$this->db->select('p.idplanilla, p.fecha_apertura, p.fecha_cierre, p.iduser_apertura, p.iduser_cierre, p.tiene_cts, p.tiene_gratificacion');
		$this->db->select('pm.idempresa, pm.idplanillamaster, emp.descripcion as empresa');
		$this->db->from('rh_planilla p');
		$this->db->join('rh_planilla_master pm','p.idplanillamaster = pm.idplanillamaster');
		$this->db->join('empresa emp','emp.idempresa = pm.idempresa');
		$this->db->where('p.estado_pl <>', 0); //no anuladas
		$this->db->where('pm.idempresa', $paramEmpresa['id']); // empresa seleccionada
		
		/*if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}else{
			$this->db->order_by('p.fecha_cierre DESC');
		}*/
		$this->db->order_by('p.fecha_cierre DESC');
		
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_planillas($paramEmpresa){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_planilla p');		
		$this->db->join('rh_planilla_master pm','p.idplanillamaster = pm.idplanillamaster');
		$this->db->join('empresa emp','emp.idempresa = pm.idempresa');
		$this->db->where('p.estado_pl <>', 0); //no anuladas
		$this->db->where('pm.idempresa', $paramEmpresa['id']); // empresa seleccionada
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_consulta_estado_planilla($datos){
		$this->db->select('p.estado_pl');
		$this->db->from('rh_planilla p');
		$this->db->where('p.idplanilla', $datos['idplanilla']);

		return $this->db->get()->row_array();
	}

	public function m_consulta_planilla_activa($datos){
		$this->db->select('MAX(p.idplanilla) as idplanilla, p.idplanillamaster');
		$this->db->from('rh_planilla p');
		$this->db->join('rh_planilla_master pm','p.idplanillamaster = pm.idplanillamaster');
		$this->db->where('p.estado_pl', 1); // activa
		$this->db->where('pm.idempresa', $datos['empresa']['id']); // empresa seleccionada
		$this->db->where('p.idplanillamaster', $datos['planillaMaster']['id']); // empresa seleccionada
		$this->db->group_by('p.idplanillamaster'); 

		return $this->db->get()->row_array();
	}

	public function m_es_planilla_duplicada($datos){
		$this->db->select('COUNT(*) as contador');
		$this->db->from('rh_planilla p');
		$this->db->join('rh_planilla_master pm','p.idplanillamaster = pm.idplanillamaster');
		$this->db->where('p.estado_pl <>', 0); // activa
		$this->db->where('pm.idempresa', $datos['empresa']['id']); // empresa seleccionada
		$this->db->where('p.idplanillamaster', $datos['planillaMaster']['id']); // empresa seleccionada
		$this->db->where('p.descripcion_pl', $datos['descripcion']);
		$this->db->group_by('p.idplanillamaster'); 
		$fData = $this->db->get()->row_array();
		return ($fData['contador'] > 0) ? TRUE : FALSE;
	}

	public function m_consultar_planilla_anterior($planillaMaster){
		$subquery = "select max(p.fecha_cierre) 
					 from rh_planilla p 	
					 where p.estado_pl = 2 
					 AND p.idplanillamaster = " . $planillaMaster['id'];

		$this->db->select('pl.idplanilla');
		$this->db->from('rh_planilla pl');
		$this->db->where('pl.estado_pl', 2); // activa
		$this->db->where('pl.idplanillamaster', $planillaMaster['id']); // empresa seleccionada
		$this->db->where('pl.fecha_cierre = ('. $subquery .')'); // empresa seleccionada

		return $this->db->get()->row_array();
	}

	
	public function m_cerrar_planilla($datos){
		$data = array(
			'estado_pl' => 2,
			'conceptos_json' 	=> $datos['conceptos_json'],
			//'fecha_cierre' => date('Y-m-d H:i:s'),
			'iduser_cierre' => $this->sessionHospital['idusers'],
			'updatedAt' 	=> date('Y-m-d H:i:s'),
			);

		$this->db->where('idplanilla', $datos['id']); // planilla activa
		return $this->db->update('rh_planilla', $data);
	}	

	public function m_aperturar_planilla($datos){
		$data = array(
			'idplanillamaster' 	=> $datos['planillaMaster']['id'],
			'fecha_apertura' 	=> date('Y-m-d', strtotime($datos['desde'])),
			'fecha_cierre' 		=> date('Y-m-d', strtotime($datos['hasta'])),
			'iduser_apertura' 	=> $this->sessionHospital['idusers'],
			'descripcion_pl' 	=> $datos['descripcion'],
			'estado_pl' 		=> 1,
			'conceptos_json' 	=> $datos['conceptos_json'],
			'createdAt' 		=> date('Y-m-d H:i:s'),
			'updatedAt' 		=> date('Y-m-d H:i:s'),
			'idempresa'			=> $datos['planillaMaster']['idempresa']
			);

		return $this->db->insert('rh_planilla', $data);
	}

	public function m_actualizar_conceptos_planilla($datos){
		$data = array(
			'conceptos_json' 	=> $datos['conceptos_json'],
			'updatedAt' 		=> date('Y-m-d H:i:s'),
			);

		$this->db->where('idplanilla', $datos['id']); // planilla 
		return $this->db->update('rh_planilla', $data);
	}

	public function m_agregar_empleados($paramEmpresa, $idplanilla){
		/*$subquery = "select b.idhistorialcontrato 
					from rh_historial_contrato b	
					where b.idempleado = hc.idempleado 
					AND b.estado_hc = 1 
					AND b.contrato_actual = 1
					AND b.condicion_laboral = 'EN PLANILLA'
					LIMIT 1 ";*/

		return $this->db->query("insert into rh_planilla_empleado (idempleado, idplanilla)
		                           SELECT empl.idempleado, ". $idplanilla." as idplanilla
		                           FROM rh_empleado empl
									JOIN empresa empr ON empr.idempresa = " . $paramEmpresa['id'] .
								"	JOIN empresa_admin emprad ON emprad.ruc = empr.ruc_empresa
									JOIN rh_historial_contrato hh ON hh.idempleado = empl.idempleado
									AND hh.estado_hc = 1
									AND hh.contrato_actual = 1
									AND hh.condicion_laboral = 'EN PLANILLA'
									AND hh.idempresaadmin = emprad.idempresaadmin"); 
	}

	public function m_listar_planilla($datos){
		$this->db->select('p.idplanilla, p.descripcion_pl, pe.concepto_valor_json::JSON',FALSE);
		$this->db->select("empl.idempleado, concat_ws(' ', empl.apellido_paterno, empl.apellido_materno, empl.nombres) AS empleado",FALSE);
		$this->db->from('rh_planilla p');
		$this->db->join('rh_planilla_master pm','p.idplanillamaster = pm.idplanillamaster');
		$this->db->join('rh_planilla_empleado pe','p.idplanilla = pe.idplanilla');
		$this->db->join('rh_empleado empl','pe.idempleado = empl.idempleado');
		$this->db->where('p.estado_pl <>', 0); // activa
		$this->db->where('pm.idempresa', $datos['empresaSoloAdmin']['id']); // empresa seleccionada
		$this->db->where("DATE_PART('YEAR', p.fecha_cierre) = ".$datos['anioDesdeCbo']); 
		$this->db->where("DATE_PART('MONTH', p.fecha_cierre) = ".$datos['mes']['id']);
		$this->db->where('pe.concepto_valor_json IS NOT NULL');
		// $this->db->where('pe.neto_a_pagar IS NOT NULL');
		// $this->db->group_by('p.idplanillamaster'); 

		return $this->db->get()->result_array();
	}

	// public function m_registrar_movimiento_planilla($datos){
	// 	return $this->db->insert('ct_movimiento', $datos);
	// }
	// public function m_registrar_detalle_movimiento_planilla($datos){
	// 	$data = array(
	// 		'idmovimiento' 		=> $datos['idmovimiento'],
	// 		'idcentrocosto' 	=> empty($datos['idcentrocosto'])? NULL : $datos['idcentrocosto'],
	// 		'codigo_plan' 		=> $datos['codigo_plan'],
	// 		'importe_local' 	=> $datos['importe_local'],
	// 		'glosa' 			=> $datos['glosa'],
	// 		'importe_local' 	=> $datos['importe_local']

	// 	);
	// 	return $this->db->insert('ct_detalle_movimiento', $data);
	// }
	public function m_registrar_asiento_planilla($datos)
	{
		$data = array( 
			'idmovimiento'=> $datos['idplanilla'],
			'codigo_plan'=> $datos['codigo_plan'],
			'glosa'=> $datos['glosa'], 
			'monto'=> $datos['monto'],
			'fecha_emision'=> $datos['fecha_emision'],
			'debe_haber'=> $datos['debe_haber'],
			'tipo_asiento' => 'P',
			'origen' => 'PL'
		);
		return $this->db->insert('ct_asiento_contable', $data);
	}

}