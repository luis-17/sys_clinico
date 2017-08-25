<?php
class Model_guia_remision extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_guias_remision($paramDatos, $paramPaginate){ // 
		$this->db->select('idguiaremision, idmovimiento, numero_serie, numero_correlativo, idmotivotraslado, marca_transporte, placa_transporte, num_constancia_inscripcion, num_licencia_conducir,nombres_razon_social, punto_partida, punto_llegada, estado_gr, fecha_inicio_traslado, costo_minimo, motivo_otros '); 
		$this->db->from('guia_remision');
		$this->db->where('idmovimiento', $paramDatos['idmovimiento']); 
		$this->db->where('estado_gr <>', 0);
		$this->db->order_by('idguiaremision', 'ASC');
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
	public function m_count_guias_remision($paramDatos, $paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('guia_remision');
		$this->db->where('idmovimiento', $paramDatos['idmovimiento']); 
		$this->db->where('estado_gr <>', 0);
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		
		return $this->db->get()->row_array();
	}
	public function m_cargar_traslados_para_guia_limite($datos){

		$this->db->select('fdm.idmovimiento, fdm.iddetallemovimiento, fdm.cantidad, lab.idlaboratorio, lab.nombre_lab');
		$this->db->select('med.idmedicamento, med.denominacion, fdm.caja_unidad, fdm.en_guia_remision');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fmo','fmo.idmovimiento = fdm.idmovimiento');
		$this->db->join('medicamento med','fdm.idmedicamento = med.idmedicamento'); 	
		$this->db->join('far_laboratorio lab', 'med.idlaboratorio = lab.idlaboratorio AND lab.estado_lab = 1', 'left');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento']);
		$this->db->where('fmo.tipo_movimiento', 3); // traslado 
		$this->db->where('fmo.dir_movimiento', 2); // salida 
		$this->db->where('fdm.en_guia_remision', 2); // no esta en una guia	
		$this->db->order_by('lab.nombre_lab', 'ASC');
		$this->db->order_by('med.denominacion', 'ASC');		
		$this->db->limit($datos['limite']);
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_guia($datos){ 
		$this->db->select('lab.idlaboratorio, med.idmedicamento, med.denominacion, grd.cantidad, lab.nombre_lab, gr.numero_guia');
		$this->db->from('guia_remision_detalle grd');	
 		$this->db->join('guia_remision gr','gr.idguiaremision = grd.idguiaremision'); 
		$this->db->join('medicamento med','grd.idmedicamento = med.idmedicamento');	
		$this->db->join('far_laboratorio lab','lab.idlaboratorio = med.idlaboratorio AND lab.estado_lab = 1', 'left'); 
		$this->db->where('gr.idguiaremision', $datos['idguiaremision']);
		$this->db->where('gr.estado_gr <>', 0);
		$this->db->order_by('lab.nombre_lab', 'ASC');
		$this->db->order_by('med.denominacion', 'ASC'); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_items_detalle_traslados($datos){ 
		$this->db->select('fdm.idmovimiento, fdm.en_guia_remision');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fmo','fmo.idmovimiento = fdm.idmovimiento');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento']);
		$this->db->where('fmo.tipo_movimiento', 3); // traslado 
		$this->db->where('fmo.dir_movimiento', 2); // salida
		$this->db->where('fdm.en_guia_remision', 2);
		$this->db->where('fmo.estado_movimiento <>', 0);  
		return $this->db->get()->result_array();
	}
	public function m_cargar_detalle_traslado_liberar($datos){ 
		$this->db->select('fdm.iddetallemovimiento');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fmo','fmo.idmovimiento = fdm.idmovimiento');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento']);
		$this->db->where('fdm.idmedicamento', $datos['idmedicamento']);
		$this->db->where('fmo.tipo_movimiento', 3); // traslado 
		$this->db->where('fmo.dir_movimiento', 2); // salida
		$this->db->where('fmo.estado_movimiento <>', 0);  
		return $this->db->get()->row_array();
	}
	public function m_cargar_numero_serie($datos)
	{
		$this->db->select('cm.idcajamaster, cm.descripcion_caja, cm.maquina_registradora, cm.numero_caja, cm.serie_caja, td.idtipodocumento, td.descripcion_td, td.abreviatura, dc.iddocumentocaja, dc.numero_serie, ea.idempresaadmin, ea.razon_social, ea.nombre_legal'); 
		$this->db->from('caja_master cm'); 
		$this->db->join('empresa_admin ea','cm.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('documento_caja dc','cm.idcajamaster = dc.idcajamaster'); 
		$this->db->join('tipo_documento td','dc.idtipodocumento = td.idtipodocumento');  
		//$this->db->where('td.estado_td', 1);
		//$this->db->where('estado_emp <>', 0);  
		$this->db->where('cm.estado_caja', 1); 
		$this->db->where('td.idtipodocumento', 5); 
		$this->db->where('ea.idempresaadmin', $datos['idempresaadmin']); 
		$this->db->order_by('cm.serie_caja','ASC');

		if(!empty($datos['idcajamaster'])){
			$this->db->where('cm.idcajamaster', $datos['idcajamaster']);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_esta_guia($datos) 
	{
		$this->db->select('idguiaremision, idmovimiento, numero_serie, numero_correlativo'); 
		$this->db->from('guia_remision');	
		$this->db->where('estado_gr <>', 0);	
		$this->db->where($datos['searchColumn'], $datos['searchText']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_consultar_serie_guia($datos) 
	{
		$this->db->select('idguiaremision, idmovimiento, numero_serie, numero_correlativo'); 
		$this->db->from('guia_remision');		
		$this->db->where('numero_serie', $datos['numero_serie']);
		$this->db->where('numero_correlativo', $datos['numero_correlativo']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idmovimiento' => $datos['idmovimiento'],
			'numero_serie' => $datos['serie']['id'],
			'numero_correlativo' => $datos['numero_serie'],
			'tipo_guia' => 1,
			'idmotivotraslado' => $datos['motivo_traslado'],
			'marca_transporte' => empty($datos['marca_vehiculo']) ? NULL : $datos['marca_vehiculo'],
			'placa_transporte' => empty($datos['placa_vehiculo']) ? NULL : $datos['placa_vehiculo'],
			'num_constancia_inscripcion' => empty($datos['constancia_inscripcion']) ? NULL : $datos['constancia_inscripcion'],
			'num_licencia_conducir' => empty($datos['licencia_conducir']) ? NULL : $datos['licencia_conducir'],
			'nombres_razon_social' => empty($datos['razon_social_nombre']) ? NULL : $datos['razon_social_nombre'],
			'punto_partida' => $datos['punto_partida'],
			'punto_llegada' => $datos['punto_llegada'],
			'estado_gr' => $datos['estado'],
			'fecha_registro' => date('Y-m-d H:i:s'),
			'fecha_inicio_traslado' => $datos['fecha_guia'],
			'idusuarioreg' => $this->sessionHospital['idusers'],
			'costo_minimo' => empty($datos['costo_minimo']) ? NULL : $datos['costo_minimo'],
			'createdat' => date('Y-m-d H:i:s'),
			'updatedat' => date('Y-m-d H:i:s'),
			'motivo_otros' => empty($datos['motivo_otros']) ? NULL : $datos['motivo_otros'],
			'numero_guia' => $datos['guia']
		);
		return $this->db->insert('guia_remision', $data);
	}
	public function m_registrar_detalle($datos)
	{
		$data = array(
			'idguiaremision' => $datos['idguiaremision'],
			'idmedicamento' => $datos['codigo'],
			'cantidad' => $datos['cantidad'],
			'estado_grd' => 1
		);
		return $this->db->insert('guia_remision_detalle', $data);
	}
	public function m_editar($datos)
	{
		$data = array(
			'idmotivotraslado' => $datos['motivo_traslado'],
			'marca_transporte' => empty($datos['marca_vehiculo']) ? NULL : $datos['marca_vehiculo'],
			'placa_transporte' => empty($datos['placa_vehiculo']) ? NULL : $datos['placa_vehiculo'],
			'num_constancia_inscripcion' => empty($datos['constancia_inscripcion']) ? NULL : $datos['constancia_inscripcion'],
			'num_licencia_conducir' => empty($datos['licencia_conducir']) ? NULL : $datos['licencia_conducir'],
			'nombres_razon_social' => empty($datos['razon_social_nombre']) ? NULL : $datos['razon_social_nombre'],
			'punto_partida' => $datos['punto_partida'],
			'punto_llegada' => $datos['punto_llegada'],
			'estado_gr' => $datos['estado'],
			'fecha_inicio_traslado' => $datos['fecha_guia'],
			'costo_minimo' => empty($datos['costo_minimo']) ? NULL : $datos['costo_minimo'],
			'motivo_otros' => empty($datos['motivo_otros']) ? NULL : $datos['motivo_otros'],
			'updatedat' => date('Y-m-d H:i:s')
		);

		$this->db->where('idguiaremision',$datos['idguiaremision']);
		if($this->db->update('guia_remision', $data)){ 
			return true;
		}else{
			return false;
		}
	}
	public function m_verificar_estado_guia($idguiaremision){
		$this->db->select('estado_gr');
		$this->db->from('guia_remision');
		$this->db->where('idguiaremision', $idguiaremision);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_items_detalle_guia($idguiaremision){ 
		$this->db->select('idguiaremisiondetalle, idmedicamento');
		$this->db->from('guia_remision_detalle grd');
		$this->db->join('guia_remision gr','gr.idguiaremision = grd.idguiaremision');
		$this->db->where('gr.idguiaremision', $idguiaremision);
		$this->db->where('gr.estado_gr', 0);
		return $this->db->get()->result_array();
	}
	public function m_anular_detalle_guia($idguiaremisiondetalle){
		$this->db->where('idguiaremisiondetalle', $idguiaremisiondetalle);
		$this->db->set('estado_grd', 0);
		return $this->db->update('guia_remision_detalle');
	}
	public function m_anular($idguiaremision){
		$this->db->where('idguiaremision', $idguiaremision);
		$this->db->set('estado_gr', 0);
		$this->db->set('numero_guia', 0);
		$this->db->set('updatedat', date('Y-m-d H:i:s'));
		return $this->db->update('guia_remision');
	}
	public function m_cargar_correlativo($datos){
		$this->db->select('COUNT(*) AS correlativo');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fmo','fmo.idmovimiento = fdm.idmovimiento');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento']);
		$this->db->where('fmo.tipo_movimiento', 3); // traslado 
		$this->db->where('fmo.dir_movimiento', 2); // salida 
		$this->db->where('fdm.en_guia_remision', 1); 
		
		$result = $this->db->get()->row_array();
		return $result['correlativo'];
	}
	public function m_count_items_traslado($datos){
		$this->db->select('COUNT(*) AS items');
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fmo','fmo.idmovimiento = fdm.idmovimiento');
		$this->db->where('fdm.idmovimiento', $datos['idmovimiento']);
		$this->db->where('fmo.tipo_movimiento', 3); // traslado 
		$this->db->where('fmo.dir_movimiento', 2); // salida

		$result = $this->db->get()->row_array();
		return $result['items'];
	}
	public function m_count_guias($datos){
		$this->db->select('COUNT(*) AS guias');
		$this->db->from('guia_remision');
		$this->db->where('idmovimiento', $datos['idmovimiento']);
		$this->db->where('estado_gr <>', 0); 	
		$result = $this->db->get()->row_array();
		return $result['guias'];
	}
	public function m_actualizar_numero_guia($datos){
		$this->db->where('idguiaremision', $datos['idguiaremision']);
		$this->db->set('numero_guia', $datos['numero_guia']);
		$this->db->set('updatedat', date('Y-m-d H:i:s'));
		return $this->db->update('guia_remision');
	}
	public function m_cargar_guias_mayores($id) 
	{
		$this->db->select('idguiaremision, numero_guia'); 
		$this->db->from('guia_remision');	
		$this->db->where('idguiaremision >', $id);	
		$this->db->where('estado_gr ', 1);
		return $this->db->get()->result_array();
	}
}