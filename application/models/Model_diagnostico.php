<?php
class Model_diagnostico extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_diagnostico($paramPaginate){

		$this->db->select('iddiagnosticocie, codigo_cie, descripcion_cie, estado_cie');
		$this->db->from('diagnostico_cie');
		$this->db->where('estado_cie <>', 0);
		if( $paramPaginate['search'] ){

			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value);

				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		//sleep(1);
		return $this->db->get()->result_array();
	}
	public function m_count_diagnostico($paramPaginate)
	{
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('diagnostico_cie');
		$this->db->where('estado_cie <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					
				}
			}
		}
		$filas = $this->db->get()->num_rows();
		return $filas;
	}
	public function m_cargar_diagnostico_grilla_modal($paramPaginate){

		$this->db->select('iddiagnosticocie, codigo_cie, descripcion_cie, estado_cie, length(trim(descripcion_cie)) AS cantidad_caracteres',FALSE);
		$this->db->from('diagnostico_cie');
		$this->db->where('estado_cie <>', 0);
		if( $paramPaginate['search'] ){

			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value);

				}
			}
			$this->db->order_by('cantidad_caracteres', 'ASC');
		}
		
		//$this->db->order_by('', );

		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		//sleep(1);
		return $this->db->get()->result_array();
	}
	public function m_count_diagnostico_grilla_modal($paramPaginate)
	{
		// $this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('diagnostico_cie');
		$this->db->where('estado_cie <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					
				}
			}
		}
		$filas = $this->db->get()->num_rows();
		return $filas;
	}
	public function m_cargar_diagnostico_autocomplete($searchColumn,$searchText)
	{
		$this->db->select('iddiagnosticocie, codigo_cie, descripcion_cie, estado_cie');
		$this->db->from('diagnostico_cie');
		$this->db->where('estado_cie', 1);
		$this->db->ilike($searchColumn, $searchText);
		$this->db->limit(8);
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_diagnostico_por_codigo($searchText) 
	{
		$this->db->select('iddiagnosticocie, codigo_cie, descripcion_cie, estado_cie');
		$this->db->from('diagnostico_cie');
		$this->db->where('codigo_cie', $searchText);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_editar($datos)
	{
		$data = array(
			'codigo_cie' => strtoupper($datos['codigo']),
			'descripcion_cie' => $datos['descripcion']
		);
		$this->db->where('iddiagnosticocie',$datos['id']);
		return $this->db->update('diagnostico_cie', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'codigo_cie' => strtoupper($datos['codigo']),
			'descripcion_cie' => $datos['descripcion'],
			'estado_cie' => 1			
		);
		return $this->db->insert('diagnostico_cie', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_cie' => 0
		);
		$this->db->where('iddiagnosticocie',$id);
		if($this->db->update('diagnostico_cie', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_cie' => 1
		);
		$this->db->where('iddiagnosticocie',$id);
		if($this->db->update('diagnostico_cie', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_cie' => 2
		);
		$this->db->where('iddiagnosticocie',$id);
		if($this->db->update('diagnostico_cie', $data)){
			return true;
		}else{
			return false;
		}
	}
}