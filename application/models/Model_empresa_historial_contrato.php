<?php
class Model_empresa_historial_contrato extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_historial_contratos($paramPaginate,$paramDatos){ 
		$this->db->select('ehc.idempresahistorialcontrato, ehc.idempresadetalle, ehc.nombre_archivo, ehc.fecha_inicio, ehc.fecha_fin, ehc.fecha_registro, ehc.contrato_actual ,ehc.condiciones , ehc.codigo, ed.tiene_contrato');
		$this->db->select('ed.idempresaadmin, ed.idempresatercera');		 
		$this->db->select('emp.descripcion as razon_social');		 

		$this->db->from('pa_empresa_historial_contrato ehc'); 
		$this->db->where('ehc.idempresadetalle', $paramDatos['idempresadetalle']);
		$this->db->where('ehc.estado_ehc <>', 0);
		
		$this->db->join('pa_empresa_detalle ed','ed.idempresadetalle = ehc.idempresadetalle'); 
		$this->db->where('ed.estado_ed <>', 0);

		$this->db->join('empresa emp','ed.idempresatercera = emp.idempresa'); 

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
			$this->db->order_by('ehc.fecha_inicio','DESC');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}

	public function m_cargar_historial_adendas_contratos($paramDatos){ 
		$this->db->select('pa.*');
		$this->db->from('pa_adenda pa'); 	
		$this->db->join('pa_empresa_historial_contrato ehc','pa.idempresahistorialcontrato = ehc.idempresahistorialcontrato');

		$this->db->where('pa.idempresahistorialcontrato', $paramDatos);
		$this->db->where('ehc.estado_ehc <>', 0);
		$this->db->where('pa.estado_adenda', 1);
		$this->db->order_by('pa.fecha_fin', 'DESC');

		return $this->db->get()->result_array();
	}

	public function m_count_historial_adendas_contratos($paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_adenda pa'); 	
		$this->db->join('pa_empresa_historial_contrato ehc','pa.idempresahistorialcontrato = ehc.idempresahistorialcontrato');

		$this->db->where('pa.idempresahistorialcontrato', $paramDatos);
		$this->db->where('ehc.estado_ehc <>', 0);
		$this->db->where('pa.estado_adenda', 1);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];		
	}

	public function m_agregar_contrato($datos){ 
		$data = array( 
			'idempresadetalle' => $datos['idempresadetalle'],
			'fecha_registro' => date('Y-m-d H:i:s'),
			'fecha_inicio' => empty($datos['fecha_inicio']) ? NULL : $datos['fecha_inicio'],
			'fecha_fin' => empty($datos['fecha_fin']) ? NULL : $datos['fecha_fin'],
			'condiciones' => empty($datos['condiciones']) ? NULL : $datos['condiciones'],
			'codigo' => empty($datos['codigo']) ? NULL : $datos['codigo']			

		);

		return $this->db->insert('pa_empresa_historial_contrato', $data);
	}

	public function m_editar_contrato($datos){ 
		$data = array( 
			'fecha_inicio' =>  $datos['fecha_inicio'],
			'fecha_fin' => $datos['fecha_fin'],
			'contrato_actual' => $datos['contrato_actual'],
			'condiciones' => $datos['condiciones'],
			'codigo' => $datos['codigo']			
		);
		$this->db->where('idempresahistorialcontrato' ,$datos['idcontrato']);
		return $this->db->update('pa_empresa_historial_contrato', $data);
	}

	public function m_editar_detalle_contrato($datos){
		$data = array( 
			'tiene_contrato' => $datos['contrato_formal'],
		);
		$this->db->where('idempresadetalle' ,$datos['idempresadetalle']);
		return $this->db->update('pa_empresa_detalle', $data);		
	}

	public function m_update_contrato_actual($datos){ 
		$data = array(
			'contrato_actual' => 2
		);
		$this->db->where('idempresadetalle = '. $datos['idempresadetalle'] . ' AND idempresahistorialcontrato <> ' . $datos['idempresahistorialcontrato']);		
		return $this->db->update('pa_empresa_historial_contrato', $data);
	}

	public function m_anular_contrato($datos){ 
		$data = array(
			'estado_ehc' => 0
		);
		$this->db->where('idempresahistorialcontrato ',$datos['idcontrato']);		
		return $this->db->update('pa_empresa_historial_contrato', $data);
	}

	public function m_subir_documento_contrato($datos){
		$data = array( 
			'nombre_archivo' => $datos['nuevoNombreArchivo']
		);
		$this->db->where('idempresahistorialcontrato',$datos['idcontrato']);
		return $this->db->update('pa_empresa_historial_contrato', $data);
	}

	public function m_quitar_documento_contrato($idcontrato){
		$data = array( 
			'nombre_archivo' => '' 
		);
		$this->db->where('idempresahistorialcontrato',$idcontrato);
		return $this->db->update('pa_empresa_historial_contrato', $data);
	}

	public function m_agregar_adenda($datos){ 
		$data = array( 
			'idempresahistorialcontrato' => $datos['idempresahistorialcontrato'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'condiciones' => empty($datos['condiciones']) ? NULL : $datos['condiciones'],
			'fecha_fin' => empty($datos['fecha_fin']) ? NULL : $datos['fecha_fin']
		);

		return $this->db->insert('pa_adenda', $data);
	}	
	public function m_editar_adenda($datos){ 
		$data = array( 
			'fecha_fin' => empty($datos['fecha_fin']) ? NULL : $datos['fecha_fin'],
			'condiciones' => $datos['condiciones'],
		);
		$this->db->where('idadenda' ,$datos['idadenda']);
		return $this->db->update('pa_adenda', $data);
	}	
	public function m_anular_adenda($id){ 
		$data = array( 
			'estado_adenda' => 0
		);
		$this->db->where('idadenda' ,$id);
		return $this->db->update('pa_adenda', $data);
	}	
}