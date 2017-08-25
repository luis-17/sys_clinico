<?php
class Model_historial_contrato extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_historial_contratos($paramPaginate,$paramDatos){ 
		$this->db->select('hc.idhistorialcontrato,hc.fecha_ingreso, hc.fecha_cese, hc.fecha_inicio_contrato, hc.fecha_fin_contrato, hc.nombre_archivo, hc.contrato_actual, hc.sueldo_contrato, 
			emp.idempleado, emp.nombres, emp.apellido_paterno, emp.apellido_materno, ca.idcargo, ca.descripcion_ca, 
			ea.idempresaadmin, ea.razon_social, ea.ruc, hc.condicion_laboral'); 
		$this->db->from('rh_empleado emp'); 
		$this->db->join('rh_historial_contrato hc','emp.idempleado = hc.idempleado'); 
		$this->db->join('rh_cargo ca','hc.idcargo = ca.idcargo'); 
		$this->db->join('empresa_admin ea','hc.idempresaadmin = ea.idempresaadmin'); 
		$this->db->where('emp.idempleado', $paramDatos['id']); 
		$this->db->where('estado_hc <>', 0);
		$this->db->where('estado_ca <>', 0);
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('emp.estado_empl <>', 0); // empleado 

		if(!empty($paramDatos['contrato_actual'])){
			$this->db->where('hc.contrato_actual', $paramDatos['contrato_actual']);
		}

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			} 
		} 
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}else{
			$this->db->order_by('hc.fecha_inicio_contrato','DESC');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_historial_contratos($paramPaginate,$paramDatos){ 
		$this->db->select('COUNT(*) as contador'); 
		$this->db->from('rh_empleado emp'); 
		$this->db->join('rh_historial_contrato hc','emp.idempleado = hc.idempleado'); 
		$this->db->join('rh_cargo ca','hc.idcargo = ca.idcargo'); 
		$this->db->join('empresa_admin ea','hc.idempresaadmin = ea.idempresaadmin'); 
		$this->db->where('emp.idempleado', $paramDatos['id']); 
		$this->db->where('estado_hc <>', 0); 
		$this->db->where('estado_ca <>', 0); 
		$this->db->where('estado_emp <>', 0); // empresa_admin 
		$this->db->where('emp.estado_empl <>', 0); // empleado 
		$fData = $this->db->get()->row_array(); 
		return $fData;
	}	
	public function m_editar($datos)
	{
		$data = array(
			'idempresaadmin' => $datos['empresaadmin']['id'],
			'condicion_laboral' => $datos['condicion_laboral']['id'],
			'idcargo' => $datos['idcargo'],
			'fecha_ingreso' => empty($datos['fecha_ingreso']) ? NULL : $datos['fecha_ingreso'],
			'fecha_cese' => empty($datos['fecha_cese']) ? NULL : $datos['fecha_cese'],
			'fecha_inicio_contrato' => empty($datos['fecha_inicio_contrato']) ? NULL : $datos['fecha_inicio_contrato'],
			'fecha_fin_contrato' => empty($datos['fecha_fin_contrato']) ? NULL : $datos['fecha_fin_contrato'],
			'sueldo_contrato' => @$datos['sueldo'],
			'contrato_actual' => empty($datos['contrato_vigente']) ? 2 : $datos['contrato_vigente']
		);
		$this->db->where('idhistorialcontrato',$datos['codigo']);
		return $this->db->update('rh_historial_contrato', $data);
	}
	public function m_registrar($datos)
	{ 
		$data = array( 
			'idempleado' => $datos['idempleado'],
			'idempresaadmin' => $datos['empresa_obj']['id'],
			'condicion_laboral' => $datos['condicion_laboral_obj']['id'],
			'idcargo' => $datos['idcargo'],
			'fecha_ingreso' => empty($datos['fecha_ing']) ? NULL : $datos['fecha_ing'],
			'fecha_inicio_contrato' => empty($datos['fecha_ini_contrato']) ? NULL : $datos['fecha_ini_contrato'],
			'fecha_fin_contrato' => empty($datos['fecha_fin_contrato']) ? NULL : $datos['fecha_fin_contrato'],
			'contrato_actual' => $datos['vigenteBool'],
			// 'nombre_archivo' => $datos['nombre_archivo'],
			'sueldo_contrato' => empty($datos['sueldo']) ? NULL : $datos['sueldo']
		);
		return $this->db->insert('rh_historial_contrato', $data);
	}
	public function m_subir_documento_contrato($datos)
	{
		$data = array( 
			'nombre_archivo' => $datos['nuevoNombreArchivo']
		);
		$this->db->where('idhistorialcontrato',$datos['codigo']);
		return $this->db->update('rh_historial_contrato', $data);
	}
	public function m_quitar_documento_contrato($datos)
	{
		$data = array( 
			'nombre_archivo' => NULL 
		);
		$this->db->where('idhistorialcontrato',$datos['codigo']);
		return $this->db->update('rh_historial_contrato', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_hc' => 0 
		);
		$this->db->where('idhistorialcontrato',$id);
		if($this->db->update('rh_historial_contrato', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_actualizar_contratos_antiguos($datos, $id){
		$data = array(
			'contrato_actual' => 2 
		);
		$this->db->where('idempleado',$datos['idempleado']);
		$this->db->where('idempresaadmin',$datos['empresaadmin']['id']);
		$this->db->where('contrato_actual',1);
		$this->db->where('idhistorialcontrato <>',$id);
		return $this->db->update('rh_historial_contrato', $data);
	}
}