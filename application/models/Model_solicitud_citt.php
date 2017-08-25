<?php 
class Model_solicitud_citt extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	

	public function m_cargar_solicitud_citt_paciente($paramPaginate,$paramDatos){ 
		$this->db->select('c.idsolicitudcitt AS id,c.idatencionmedica,ho.descripcion_aho,c.idcontingencia,co.descripcion_ctg,c.fec_otorgamiento,c.fec_iniciodescanso,c.total_dias,c.estado_citt, tp.idtipoproducto, pm.idproductomaster, (pm.descripcion) AS producto, pps.precio_sede AS precio, tp.nombre_tp, es.nombre, es.idespecialidad'); 
		$this->db->from('solicitud_citt c'); 
		$this->db->join('contingencia co','c.idcontingencia = co.idcontingencia'); 
		$this->db->join('area_hospitalaria ho','c.idtipoatencion = ho.idareahospitalaria'); 
		$this->db->join('atencion_medica at','at.idatencionmedica = c.idatencionmedica');
		$this->db->join('producto_master pm','c.idproductomaster = pm.idproductomaster');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND pps.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('especialidad es','c.idespecialidad = es.idespecialidad'); 
		// $this->db->join('especialidad es','pm.idespecialidad = es.idespecialidad'); /* cualquiera de los dos vale */ 
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto'); 
		$this->db->where('c.estado_citt <>',0); 
		$this->db->where('at.idhistoria',$paramDatos['idhistoria']);
		$this->db->where('estado_pps', 1);
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
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_solicitud_citt_paciente($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('solicitud_citt'); 
		$this->db->where('estado_citt <>', 0); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_get_producto_citt($idespecialidad){
		$this->db->from('producto_master');
		$this->db->where('idespecialidad', $idespecialidad);
		$this->db->where('idtipoproducto', 13);
		$this->db->like('descripcion','DESCANSO MEDICO');
		return $this->db->get()->result_array();
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_citt' => 0
		);
		$this->db->where('idsolicitudcitt',$id);
		if($this->db->update('solicitud_citt', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_registrar_solicitud_citt($datos)
	{
		$data = array(
			'idatencionmedica' => $datos['idatencionmedica'],
			'idtipoatencion' => $datos['idtipoatencion'],
			'idcontingencia' => $datos['idcontingencia'],
			'fec_otorgamiento' => $datos['fecha_otorgamiento'],
			'fec_iniciodescanso' => $datos['fecha_inicio'],
			'total_dias' => $datos['dias'],
			'createdAt' => date('Y-m-d H:i:s'), 
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_citt' => 1,
			'idproductomaster' => $datos['idproducto'],
			'idespecialidad' => $datos['idespecialidad'],
			'idsedeempresaadmin_sd' => @$this->sessionHospital['idsedeempresaadmin']
		);
		return $this->db->insert('solicitud_citt', $data);
	}

}