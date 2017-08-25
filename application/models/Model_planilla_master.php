<?php
class Model_planilla_master extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_planillas($paramPaginate,$paramEmpresa){ 
		$this->db->select('plm.idplanillamaster, plm.descripcion_plm, plm.estado_plm');
		$this->db->select('emp.descripcion as empresa, emp.idempresa');
		$this->db->from('rh_planilla_master plm');
		$this->db->join('empresa emp','emp.idempresa = plm.idempresa');
		$this->db->where('plm.estado_plm <>', 0); //no anuladas
		if(!empty( $paramEmpresa['id'])){
			$this->db->where('plm.idempresa', $paramEmpresa['id']); // empresa seleccionada
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}

	public function m_count_planillas($paramEmpresa){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_planilla_master plm');
		$this->db->join('empresa emp','emp.idempresa = plm.idempresa');
		$this->db->where('plm.estado_plm <>', 0); //no anuladas
		if(!empty( $paramEmpresa['id'])){
			$this->db->where('plm.idempresa', $paramEmpresa['id']); // empresa seleccionada
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_planillas_master_cbo($paramEmpresa){
		$this->db->select('plm.idplanillamaster, plm.descripcion_plm, plm.estado_plm, plm.idempresa');
		$this->db->from('rh_planilla_master plm');
		$this->db->where('plm.estado_plm', 1); //activa
		$this->db->where('plm.idempresa', $paramEmpresa['id']); // empresa seleccionada		

		return $this->db->get()->result_array();
	}
	public function m_consultar($datos){
		$this->db->select('plm.idplanillamaster');
		$this->db->from('rh_planilla_master plm');
		$this->db->join('empresa emp','emp.idempresa = plm.idempresa');
		$this->db->where('plm.estado_plm <>', 0); //no anuladas
		$this->db->where('plm.descripcion_plm', $datos['descripcion']);
		$this->db->where('emp.idempresa', $datos['empresa']['id']); //no anuladas

		return $this->db->get()->row_array();
	}

	public function m_registrar($datos){
		$data = array(
			'descripcion_plm' => $datos['descripcion'],
			'idempresa' => $datos['empresa']['id']
		);

		return $this->db->insert('rh_planilla_master', $data);
	}	

	public function m_editar($datos){
		$data = array(
			'descripcion_plm' => $datos['descripcion'],
		);

		$this->db->where('idplanillamaster',$datos['idplanillamaster']);
		return $this->db->update('rh_planilla_master', $data);
	}	

	public function m_anular($datos){
		$data = array(
			'estado_plm' => 0,
		);

		$this->db->where('idplanillamaster',$datos['idplanillamaster']);
		return $this->db->update('rh_planilla_master', $data);
	}
}