<?php
class Model_principio_activo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_principio_activo_cbo($datos = FALSE){ 
		$this->db->select('idprincipioactivo, descripcion, estado_pa'); 
		$this->db->from('far_principio_activo'); 
		$this->db->where('estado_pa', 1); // activo 
		return $this->db->get()->result_array();
	}
	public function m_cargar_principio_activo($paramPaginate,$paramComponenteFormula){
		$this->db->from('far_principio_activo');
		$this->db->where('estado_pa <>', 0);
		if( $paramComponenteFormula['id'] != 0) {
			$this->db->where('es_componente_formula = ', $paramComponenteFormula['id']);
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
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_principio_activo($paramPaginate,$paramComponenteFormula)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_principio_activo');
		$this->db->where('estado_pa <>', 0);
		if( $paramComponenteFormula['id'] != 0) {
			$this->db->where('es_componente_formula = ', $paramComponenteFormula['id']);
		}
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
	public function m_editar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'es_componente_formula' => $datos['es_componente_formula'] == 0 ? 2 : 1
		);
		$this->db->where('idprincipioactivo',$datos['id']);
		return $this->db->update('far_principio_activo', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion' => strtoupper($datos['descripcion']),
			'es_componente_formula' => $datos['es_componente_formula'] == 0 ? 2 : 1,
			'estado_pa' => 1,
		);
		return $this->db->insert('far_principio_activo', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pa' => 0
		);
		$this->db->where('idprincipioactivo',$id);
		if($this->db->update('far_principio_activo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar($id)
	{
		$data = array(
			'estado_pa' => 1
		);
		$this->db->where('idprincipioactivo',$id);
		if($this->db->update('far_principio_activo', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar($id)
	{
		$data = array(
			'estado_pa' => 2
		);
		$this->db->where('idprincipioactivo',$id);
		if($this->db->update('far_principio_activo', $data)){
			return true;
		}else{
			return false;
		}
	}

	/************************************************/
	/**         PRINCIPIO ACTIVO X MEDICAMENTO     **/
	/************************************************/
	public function m_cargar_principio_activo_medicamento($datos){ 
		$this->db->select('mp.idmedicamentoprincipio,pa.descripcion,mp.idprincipioactivo,mp.idmedicamento,mp.estado_mp,pa.abreviatura'); 
		$this->db->from('far_medicamento_principio mp'); 
		$this->db->join('far_principio_activo pa','mp.idprincipioactivo = pa.idprincipioactivo');
		$this->db->where('pa.estado_pa', 1); // activo 
		$this->db->where('mp.estado_mp', 1); // activo 
		$this->db->where('mp.idmedicamento', $datos['id']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_sin_principio_activo_medicamento($paramPaginate,$datos){ 
		$this->db->select('*')->from('far_principio_activo');
		$this->db->where('"idprincipioactivo" NOT IN (SELECT "idprincipioactivo" from "far_medicamento_principio" where idmedicamento ='.$datos['id'].' and estado_mp=1 )',NULL,FALSE);
		$this->db->where('estado_pa <>', 0); // no anulados
		if( $datos['idtipoproducto'] == 22) {
			$this->db->where('es_componente_formula = ', 1);
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
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_sin_principio_activo_medicamento($paramPaginate,$datos)
	{
		$this->db->select('COUNT(*) AS contador')->from('far_principio_activo');
		$this->db->where('"idprincipioactivo" NOT IN (SELECT "idprincipioactivo" from "far_medicamento_principio" where idmedicamento ='.$datos['id'].' and estado_mp=1 )',NULL,FALSE);
		$this->db->where('estado_pa <>', 0); // no anulados 
		if( $datos['idtipoproducto'] == 22) {
			$this->db->where('es_componente_formula = ', 1);
		}
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
	public function m_registrar_principio_activo_medicamento($datos)
	{
		$data = array(
			'idprincipioactivo' => $datos['idprincipio'],
			'idmedicamento' => $datos['idmedicamento'] ,
			'estado_mp' => 1
		);
		return $this->db->insert('far_medicamento_principio', $data);
	}
	public function m_anular_principio_activo_medicamento($id)
	{
		$data = array(
			'estado_mp' => 0
		);
		$this->db->where('idmedicamentoprincipio',$id);
		if($this->db->update('far_medicamento_principio', $data)){
			return true;
		}else{
			return false;
		}
	}
	/******* Busqueda principio Activo **************************/
	public function m_cargar_busqueda_principio_activo($paramPaginate,$paramDatos){

		$this->db->select('me.idmedicamento, me.denominacion, me.idtipoproducto');
		$this->db->select("STRING_AGG(pa.descripcion, '; ' ORDER BY pa.descripcion) AS principios",FALSE);
		$this->db->select('ma.idmedicamentoalmacen, ma.stock_actual_malm, ma.precio_venta');
		$this->db->from('medicamento me');
		$this->db->join('far_medicamento_principio mp','me.idmedicamento = mp.idmedicamento AND mp.estado_mp=1','left');
		$this->db->join('far_principio_activo pa','pa.idprincipioactivo = mp.idprincipioactivo AND estado_pa = 1','left');

		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->join('far_medicamento_almacen ma','ma.idmedicamento = me.idmedicamento and ma.idalmacen='.$this->sessionHospital['idalmacenfarmacia'].' AND ma.idsubalmacen='.$this->sessionHospital['idsubalmacenfarmacia'].' AND ma.estado_fma=1'  ,'left');
		}else{
			$this->db->join('far_medicamento_almacen ma','ma.idmedicamento = me.idmedicamento AND ma.idsubalmacen='.$paramDatos['idsubalmacen'].' AND ma.estado_fma=1'  ,'left');
		}

		$this->db->where('estado_med', 1); // activo
		$this->db->group_by("me.idmedicamento, ma.idmedicamentoalmacen");
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
					//$this->db->ilike('CAST('.$key.' AS TEXT )', $value,'after'); 
					//$this->db->ilike($key, $value,'after');
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
	/******  Fin de Busqueda ***********************************/
	public function m_count_busqueda_principio_activo($paramPaginate,$paramDatos)
	{
		$this->db->select('me.idmedicamento, me.denominacion');
		// $this->db->select("me.denominacion,STRING_AGG(pa.descripcion, '; ' ORDER BY pa.descripcion ) AS principios,ma.idmedicamento as medicamentoalmacen", FALSE);
		$this->db->from('medicamento me');
		$this->db->join('far_medicamento_principio mp','me.idmedicamento = mp.idmedicamento AND mp.estado_mp=1','left');
		$this->db->join('far_principio_activo pa','pa.idprincipioactivo = mp.idprincipioactivo','left');
		//$this->db->where("(mp.estado_mp=1 OR mp.estado_mp isnull)");
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->join('far_medicamento_almacen ma','ma.idmedicamento = me.idmedicamento and ma.idalmacen='.$this->sessionHospital['idalmacenfarmacia'].' AND ma.idsubalmacen='.$this->sessionHospital['idsubalmacenfarmacia'].' AND ma.estado_fma=1'  ,'left');
		}else{
			$this->db->join('far_medicamento_almacen ma','ma.idmedicamento = me.idmedicamento AND ma.idsubalmacen='.$paramDatos['idsubalmacen'].' AND ma.estado_fma=1'  ,'left');
		}
		$this->db->group_by("me.idmedicamento,ma.idmedicamento");
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		return $this->db->get()->num_rows();
	}

	public function m_cargar_principio_activo_medicamento_similar($paramPaginate,$paramDatos){
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$idsubalmacen = $this->sessionHospital['idsubalmacenfarmacia'];
		}else{
			$idsubalmacen = $paramDatos['idsubalmacen'];
		}

		$this->db->select('mp.idmedicamento, fma.idmedicamentoalmacen, fma.stock_actual_malm, fma2.stock_actual_malm AS stock_central,
			fma.precio_venta, m.idtipoproducto, lab.idlaboratorio, nombre_lab,');
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		$this->db->from('far_medicamento_principio mp');
		$this->db->join('medicamento m', 'mp.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);
		$this->db->join('far_medicamento_almacen fma2', 'm.idmedicamento = fma2.idmedicamento');
		$this->db->join('far_subalmacen fsa' ,'fma2.idsubalmacen = fsa.idsubalmacen AND fsa.idalmacen = ' . $this->sessionHospital['idalmacenfarmacia'] . ' AND fsa.idtiposubalmacen = 1');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('mp.estado_mp', 1);
		$this->db->where('mp.idmedicamento <>', $paramDatos['temporal']['producto']['id']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		$this->db->group_by('mp.idmedicamento, medicamento, fma.idmedicamentoalmacen, fma.stock_actual_malm, stock_central, fma.precio_venta, m.idtipoproducto, lab.idlaboratorio, nombre_lab');
		$this->db->having("STRING_AGG(CAST(mp.idprincipioactivo as TEXT), ';' ORDER BY mp.idprincipioactivo) = ", $paramDatos['concatenado']);
		if( $paramPaginate['sortName'] ){ 
				$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
				$this->db->order_by('fma.precio_venta', 'DESC');
		}
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_principio_activo_medicamento_similar($paramPaginate,$paramDatos)
	{
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$idsubalmacen = $this->sessionHospital['idsubalmacenfarmacia'];
		}else{
			$idsubalmacen = $paramDatos['idsubalmacen'];
		}

		$this->db->select('mp.idmedicamento');
		$this->db->from('far_medicamento_principio mp');
		$this->db->join('medicamento m', 'mp.idmedicamento = m.idmedicamento');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);
		$this->db->join('far_medicamento_almacen fma2', 'm.idmedicamento = fma2.idmedicamento');
		$this->db->join('far_subalmacen fsa' ,'fma2.idsubalmacen = fsa.idsubalmacen AND fsa.idalmacen = ' . $this->sessionHospital['idalmacenfarmacia'] . ' AND fsa.idtiposubalmacen = 1');
		$this->db->where('mp.estado_mp', 1);
		$this->db->where('mp.idmedicamento <>', $paramDatos['temporal']['producto']['id']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		$this->db->group_by('mp.idmedicamento');
		$this->db->having("STRING_AGG(CAST(mp.idprincipioactivo as TEXT), ';' ORDER BY mp.idprincipioactivo) = ", $paramDatos['concatenado']);
		return $this->db->get()->num_rows();
	}
	public function m_cargar_principio_activo_este_medicamento($idmedicamento){
		$this->db->select('pa.idprincipioactivo, pa.descripcion');
		$this->db->from('far_medicamento_principio mp');
		$this->db->join('far_principio_activo pa','mp.idprincipioactivo = pa.idprincipioactivo'); 
		$this->db->where('mp.idmedicamento', $idmedicamento);
		$this->db->where('mp.estado_mp', 1);
		$this->db->order_by('idprincipioactivo', 'ASC'); // IMPORTANTE: NO CAMBIAR ESTE ORDENAMIENTO PORQUE AFECTA LA BUSQUEDA POR PRINC ACTIVO
		return $this->db->get()->result_array();
	}

}