<?php
class Model_receta_medica extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_receta_medica_por_id($paramPaginate,$paramDatos){ 
		// $this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('r.idreceta, r.fecha_receta, rm.idrecetamedicamento, rm.cantidad, r.idatencionmedica, rm.atendido'); 
		$this->db->select('stock_actual_malm, stock_temporal, (precio_venta::numeric) AS precio_venta_sf,
			fma.idmedicamentoalmacen, fma.stock_minimo, fma.stock_maximo, sea.idempresaadmin, fma.utilidad_porcentaje, 
			m.idmedicamento, m.excluye_igv, m.si_bonificacion, tp.idtipoproducto');
		$this->db->select('c.idcliente, c.num_documento, h.idhistoria, am.idmedico, tc.idtipocliente, tc.descripcion_tc, tc.porcentaje_farmacia');
		// $this->db->select("concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente");
		$this->db->select("concat_ws(' ', c.apellido_paterno, c.apellido_materno, c.nombres) AS paciente");
		$this->db->select("concat_ws(' ', med.med_apellido_paterno, med.med_apellido_materno, med.med_nombres) AS medico");
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta');
		$this->db->join('atencion_medica am','r.idatencionmedica = am.idatencionmedica');
		$this->db->join('medico med','am.idmedico = med.idmedico');
		$this->db->join('historia h','am.idhistoria = h.idhistoria');
		$this->db->join('cliente c','h.idcliente = c.idcliente'); 
		$this->db->join('tipo_cliente tc','c.idtipocliente = tc.idtipocliente','left'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->join('tipo_producto tp','m.idtipoproducto = tp.idtipoproducto','left'); 
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');

		$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->where('fsa.idsubalmacen', $this->sessionHospital['idsubalmacenfarmacia']);
		}else{
			$this->db->where('fsa.idsubalmacen', $paramDatos['idsubalmacen']);
		}
		$this->db->where('r.idreceta', $paramDatos['idreceta'] ); 
		$this->db->where('estado_rem', 1);
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
	public function m_count_receta_medica_por_id($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta');
		$this->db->join('atencion_medica am','r.idatencionmedica = am.idatencionmedica');
		$this->db->join('medico med','am.idmedico = med.idmedico');
		$this->db->join('historia h','am.idhistoria = h.idhistoria');
		$this->db->join('cliente c','h.idcliente = c.idcliente'); 
		$this->db->join('tipo_cliente tc','c.idtipocliente = tc.idtipocliente','left'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->join('tipo_producto tp','m.idpresentacion = tp.idtipoproducto','left'); 
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->where('fsa.idsubalmacen', $this->sessionHospital['idsubalmacenfarmacia']);
		}else{
			$this->db->where('fsa.idsubalmacen', $paramDatos['idsubalmacen']);
		}
		$this->db->where('r.idreceta', $paramDatos['idreceta'] ); 
		$this->db->where('estado_rem', 1);
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
	public function m_cargar_receta_medica($paramPaginate,$paramDatos){ 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('r.idreceta, indicaciones_generales, fecha_receta, idrecetamedicamento, cantidad, indicaciones, 
			m.idmedicamento, r.idatencionmedica, ff.descripcion_ff'); 
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		$this->db->where('DATE(fecha_receta) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		$this->db->where('estado_rem', 1);
		$this->db->where('r.idhistoria', $paramDatos['idhistoria']);
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
	public function m_cargar_ultimas_recetas_medicas($datos){ 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('r.idreceta, indicaciones_generales, fecha_receta, idrecetamedicamento, cantidad, indicaciones, 
			m.idmedicamento, r.idatencionmedica, ff.descripcion_ff'); 
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left'); 
		 
		$this->db->where('estado_rem', 1);
		$this->db->where('r.idhistoria', $datos['idhistoria']);
		$this->db->order_by('fecha_receta','DESC');
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_count_receta_medica($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('receta r'); 
		$this->db->join('receta_medicamento rm','r.idreceta = rm.idreceta'); 
		$this->db->join('medicamento m','rm.idmedicamento = m.idmedicamento'); 
		$this->db->where('DATE(fecha_receta) BETWEEN '. $this->db->escape($paramDatos['desde']) .' AND ' . $this->db->escape($paramDatos['hasta'])); 
		$this->db->where('estado_rem', 1);
		$this->db->where('r.idhistoria', $paramDatos['idhistoria']);
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
	public function m_cargar_receta_medica_para_imprimir($id){
		$this->db->select('r.idreceta, r.indicaciones_generales, r.fecha_receta, r.idatencionmedica, r.idhistoria');
		$this->db->select("c.idcliente, c.num_documento, c.fecha_nacimiento");
		$this->db->select("concat_ws(' ', c.nombres, c.apellido_paterno, c.apellido_materno) AS paciente");
		// $this->db->select("concat_ws(' ', c.apellido_paterno, c.apellido_materno, c.nombres) AS paciente");
		$this->db->select("med.idmedico, concat_ws(' ', med_apellido_paterno, med_apellido_materno, med_nombres) AS medico");
		//$this->db->select("med.idmedico, (med_apellido_paterno || ' ' || med_apellido_materno || ' ' || med_nombres) AS medico");
		$this->db->select('e.idespecialidad, e.nombre AS especialidad');
		$this->db->from('receta r');
		$this->db->join('historia h','r.idhistoria = h.idhistoria');
		$this->db->join('cliente c', 'h.idcliente = c.idcliente');
		$this->db->join('atencion_medica am','r.idatencionmedica = am.idatencionmedica');
		$this->db->join('medico med', 'am.idmedico = med.idmedico');
		$this->db->join('especialidad e', 'am.idespecialidad = e.idespecialidad');
		$this->db->where('idreceta',$id);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_detalle_receta_medica_para_imprimir($id){
		$this->db->select('rm.cantidad, rm.indicaciones, m.idmedicamento, m.denominacion, ff.descripcion_ff AS forma_farmaceutica');
		$this->db->select('m.val_concentracion AS concentracion');
		//$this->db->select('pr.descripcion_pres AS presentacion, ff.descripcion_ff AS forma_farmaceutica');
		$this->db->from('receta_medicamento rm');
		$this->db->join('medicamento m', 'rm.idmedicamento = m.idmedicamento');
		// $this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_forma_farmaceutica ff','m.idformafarmaceutica = ff.idformafarmaceutica','left');
		$this->db->where('estado_rem', 1);
		$this->db->where('rm.idreceta', $id);
		return $this->db->get()->result_array();

	}
	public function m_registrar_receta_medica($datos)
	{
		$data = array(
			'idatencionmedica' => $datos['idatencionmedica'],
			'idhistoria' => $datos['idhistoria'],
			'fecha_receta' => date('Y-m-d H:i:s'),
			'indicaciones_generales' => empty($datos['indicaciones_generales']) ? NULL:$datos['indicaciones_generales'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('receta', $data);
	}
	public function m_registrar_detalle_receta_medica($datos)
	{
		$data = array(
			'idreceta' => $datos['idreceta'],
			'idmedicamento' => $datos['medicamento']['id'],
			'cantidad' => $datos['cantidad'],
			'indicaciones' => empty($datos['indicacion']) ? NULL:$datos['indicacion'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('receta_medicamento', $data);
	}
	public function m_actualizar_atencion_receta_medicamento($id,$estadoMov='V')
	{
		if( $estadoMov =='A' ){ // VENTA ANULADA
			$data = array(
				'atendido' => 2,
			);
		}else{
			$data = array(
				'atendido' => 1,
			);
		}
		
		$this->db->where('idrecetamedicamento',$id);
		return $this->db->update('receta_medicamento', $data);
	}
	public function m_anular_medicamento_receta($id)
	{
		$data = array(
			'estado_rem' => 0
		);
		$this->db->where('idrecetamedicamento',$id); 
		if($this->db->update('receta_medicamento', $data)){
			return true;
		}else{
			return false;
		}
	}
}