<?php
class Model_medicamento_almacen extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_medicamento_subalmacen_venta($paramPaginate,$paramDatos){
		$this->db->select('med.idmedicamento, med.denominacion AS medicamento, pr.descripcion_pres AS presentacion'); 
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto <> 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_presentacion pr','med.idpresentacion = pr.idpresentacion','left');
		$this->db->where('fma.estado_fma', 1); 
		$this->db->where('med.estado_med', 1); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacen']['id']);
		$this->db->where('fma.stock_entradas > 0'); 
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
	public function m_count_medicamento_subalmacen_venta($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto <> 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->where('fma.estado_fma', 1); 
		$this->db->where('med.estado_med', 1); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacen']['id']);
		$this->db->where('fma.stock_entradas > 0'); 
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamentos_almacen($paramPaginate,$paramDatos)
	{
		// OPTIMIZADO  precio_ultima_compra
		$this->db->select('med.denominacion, (precio_compra::numeric) AS precio_compra_str, (precio_venta::numeric) AS precio_venta_str, (porcentaje_venta_kairos::numeric) as porcentaje_venta_kairos_str,
			(precio_venta_kairos::numeric) AS precio_venta_kairos_str, (utilidad_valor::numeric) AS utilidad_valor_str', FALSE); 
		$this->db->select('idmedicamentoalmacen, estado_fma, med.idmedicamento, fa.idalmacen, fsa.idsubalmacen, precio_compra, precio_ultima_compra, (precio_ultima_compra::NUMERIC) AS precio_ultima_compra_str, 
			utilidad_porcentaje, utilidad_valor, precio_venta, precio_venta_kairos, med.stock_actual,stock_inicial, stock_entradas, stock_salidas, stock_actual_malm, stock_minimo, stock_critico, 
			stock_maximo, costo_medio_malm, costo_min_malm, costo_max_malm, margen_utilidad');
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto <> 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->where('fma.estado_fma <> 0'); 
		$this->db->where('med.estado_med <> 0'); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacen']['id']);
		if( $paramDatos['allStocks'] )
			$this->db->where('stock_actual_malm > 0');
		if( !empty($paramDatos['laboratorio']) ){
			if( $paramDatos['laboratorio']['id'] != '0'){ 
				$this->db->where('med.idlaboratorio', $paramDatos['laboratorio']['id']); 
			}	
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
	public function m_count_medicamentos_almacen($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto <> 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->where('fma.estado_fma <> 0'); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacen']['id']);
		if( $paramDatos['allStocks'] )
			$this->db->where('stock_actual_malm > 0');
		if( !empty($paramDatos['laboratorio']) ){
			if( $paramDatos['laboratorio']['id'] != '0'){ 
				$this->db->where('med.idlaboratorio', $paramDatos['laboratorio']['id']); 
			} 
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamentos_almacen_para_pdf($paramPaginate,$paramDatos)
	{ 
		$this->db->select(" (SELECT STRING_AGG(pa.descripcion, ' | ' ORDER BY pa.descripcion ) AS pas 
			FROM far_principio_activo pa 
			INNER JOIN far_medicamento_principio mp ON pa.idprincipioactivo = mp.idprincipioactivo 
			WHERE mp.idmedicamento = med.idmedicamento AND estado_mp = 1 
			GROUP BY idmedicamento) AS principios_activos", FALSE); 
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE); 
		$this->db->select('idmedicamentoalmacen, estado_fma, med.idmedicamento, med.registro_sanitario, fa.idalmacen, fsa.idsubalmacen, med.stock_actual, stock_inicial, stock_entradas, stock_salidas, stock_actual_malm, precio_ultima_compra, utilidad_porcentaje, utilidad_valor, (fma.precio_venta)::NUMERIC, stock_minimo, stock_critico, stock_maximo, lab.nombre_lab as laboratorio, ff.idformafarmaceutica, ff.descripcion_ff AS forma_farmaceutica');
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_laboratorio lab', 'med.idlaboratorio = lab.idlaboratorio','left');
		$this->db->join('far_forma_farmaceutica ff','ff.idformafarmaceutica = med.idformafarmaceutica');
		$this->db->where('fma.estado_fma <> 0');
		$this->db->where('med.estado_med <> 0');
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacen']['id']);
		$this->db->where('med.idtipoproducto <>',22);
		if( $paramDatos['allStocks'] )
			$this->db->where('stock_actual_malm > 0');
		if( $paramDatos['laboratorio']['id'] != '0'){
			$this->db->where('med.idlaboratorio', $paramDatos['laboratorio']['id']);
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
		
		return $this->db->get()->result_array();
	}
	//PREPARADOS
	public function m_cargar_preparados_almacen($paramPaginate,$paramDatos)
	{
		$this->db->select("med.denominacion AS medicamento,stock_inicial, stock_entradas, stock_salidas, stock_actual_malm,", FALSE); 
		$this->db->select('(precio_venta::NUMERIC) AS precio_venta_sf, (precio_compra::NUMERIC) AS precio_compra_sf');
		$this->db->select('idmedicamentoalmacen, estado_fma, med.idmedicamento, fa.idalmacen, fsa.idsubalmacen, precio_venta, precio_compra');
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto = 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->where('fma.estado_fma <> 0'); 
		$this->db->where('med.estado_med <> 0'); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacenpreparado']['id']);
		/*if( $paramDatos['allStocks'] )
			$this->db->where('stock_actual_malm > 0');
		if( !empty($paramDatos['laboratorio']) ){
			if( $paramDatos['laboratorio']['id'] != '0'){ 
				$this->db->where('med.idlaboratorio', $paramDatos['laboratorio']['id']); 
			}	
		}*/
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', trim($value));
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
	public function m_count_preparados_almacen($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento med');
		$this->db->join('far_medicamento_almacen fma','med.idmedicamento = fma.idmedicamento AND med.idtipoproducto = 22');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->where('fma.estado_fma <> 0'); 
		$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']); 
		$this->db->where('fsa.idsubalmacen', $paramDatos['subalmacenpreparado']['id']);
		// if( $paramDatos['allStocks'] )
		// 	$this->db->where('stock_actual_malm > 0');
		// if( !empty($paramDatos['laboratorio']) ){
		// 	if( $paramDatos['laboratorio']['id'] != '0'){ 
		// 		$this->db->where('med.idlaboratorio', $paramDatos['laboratorio']['id']); 
		// 	} 
		// }
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamento_almacen_venta_session($datos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		//	SE AGREGO EL STOCK_TEMPORAL PARA LAS VENTAS TEMPORALES
		$this->db->select('m.idmedicamento, m.idtipoproducto, precio_venta, stock_actual_malm,stock_temporal, (precio_venta::numeric) AS precio_venta_sf, fma.idmedicamentoalmacen ,fma.stock_minimo, fma.stock_maximo, sea.idempresaadmin'); 
		$this->db->from('medicamento m'); 
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left'); 
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento'); 
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen'); 
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->where('fsa.idsubalmacen', $this->sessionHospital['idsubalmacenfarmacia']);
		}else{
			$this->db->where('fsa.idsubalmacen', $datos['subalmacen']);
		}
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		$this->db->where('estado_med', 1);
		$this->db->where('estado_fma', 1); 
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamento_almacen_venta_session_autocomplete($datos)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (denominacion || ' ' || descripcion) ELSE denominacion END) AS medicamento",FALSE); 
		$this->db->select('(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END) AS presentacion',FALSE); 
		//	SE AGREGO EL STOCK_TEMPORAL PARA LAS VENTAS TEMPORALES
		$this->db->select('m.idmedicamento, m.idtipoproducto, precio_venta, stock_actual_malm, stock_temporal,
			(precio_venta::numeric) AS precio_venta_sf, fma.idmedicamentoalmacen, fma.stock_minimo, fma.stock_maximo,
			sea.idempresaadmin, m.excluye_igv, nombre_lab, m.si_bonificacion, m.edicion_precio_en_venta, fma.utilidad_porcentaje'); 
		$this->db->from('medicamento m');
		$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$this->db->where('fsa.idsubalmacen', $this->sessionHospital['idsubalmacenfarmacia']);
		}else{
			$this->db->where('fsa.idsubalmacen', $datos['subalmacen']);
		}
		$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		$this->db->where('estado_med', 1); 
		$this->db->where('estado_fma', 1);
		
		if($datos['boolPreparado']){
			$this->db->where('m.idtipoproducto', 22); // muestra solo los preparados
		}else{
			$this->db->where('m.idtipoproducto <>', 22);
		}
		$this->db->order_by('precio_venta', 'DESC');
		$this->db->order_by('stock_actual_malm', 'DESC');
		$this->db->limit(20);
		return $this->db->get()->result_array();
	}
	public function m_cargar_preparado_almacen_venta_session_autocomplete($datos)
	{
		$this->db->select('m.denominacion AS medicamento'); 
		$this->db->select('m.idmedicamento, m.idtipoproducto, m.categoria_jj, m.uso_jj, precio_venta, stock_actual_malm, stock_temporal,
			(precio_venta::numeric) AS precio_venta_sf, fma.idmedicamentoalmacen, fma.stock_minimo, fma.stock_maximo,
			sea.idempresaadmin, m.excluye_igv, m.si_bonificacion, m.edicion_precio_en_venta, fma.precio_compra::NUMERIC', FALSE); 
		$this->db->from('medicamento m');
		//$this->db->join('far_presentacion pr','m.idpresentacion = pr.idpresentacion','left');
		//$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_tipo_subalmacen fts','fsa.idtiposubalmacen = fts.idtiposubalmacen AND fts.venta_a_cliente = 1');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		//*******$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		//if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
		// $this->db->where('sea.idsede', $this->sessionHospital['idsede']);
		//}
		$this->db->ilike($datos['searchColumn'], $datos['searchText']); 
		$this->db->where('estado_med', 1); 
		$this->db->where('estado_fma', 1);
		$this->db->where('m.idtipoproducto', 22);

		$this->db->order_by('precio_venta', 'DESC');
		$this->db->order_by('stock_actual_malm', 'DESC');
		$this->db->limit(15);
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_medicamento_almacen($idmedicamentoalmacen)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento, 
			(utilidad_valor::NUMERIC) AS utilidad_valor_num, (utilidad_porcentaje::NUMERIC) AS utilidad_porcentaje_num, 
			(precio_ultima_compra::NUMERIC) AS precio_ultima_compra_num, (precio_venta::NUMERIC) AS precio_venta_num", FALSE); 
		$this->db->select('m.idmedicamento, stock_actual_malm, stock_actual, fma.stock_temporal, utilidad_porcentaje, precio_ultima_compra'); 
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento'); 
		$this->db->where('idmedicamentoalmacen', $idmedicamentoalmacen);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_este_medicamento_almacen_tipo_farmacia($idalmacen,$idmedicamento)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento, 
			(utilidad_valor::NUMERIC) AS utilidad_valor_num, (utilidad_porcentaje::NUMERIC) AS utilidad_porcentaje_num, 
			(precio_ultima_compra::NUMERIC) AS precio_ultima_compra_num, (precio_venta::NUMERIC) AS precio_venta_num", FALSE); 
		$this->db->select('m.idmedicamento, stock_actual_malm, stock_actual, fma.stock_temporal, utilidad_porcentaje, precio_ultima_compra'); 
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento'); 
		$this->db->join('far_almacen alm','fma.idalmacen = alm.idalmacen'); 
		$this->db->join('far_subalmacen salm','fma.idsubalmacen = salm.idsubalmacen'); 
		$this->db->join('far_tipo_subalmacen tsa','salm.idtiposubalmacen = tsa.idtiposubalmacen'); 
		$this->db->where('alm.idalmacen', $idalmacen); 
		$this->db->where('m.idmedicamento', $idmedicamento); 
		$this->db->where('tsa.venta_a_cliente',1); // TIPO FARMACIA 
		$this->db->limit(1); 
		return $this->db->get()->row_array(); 
	}
	public function m_cargar_stock_subalmacen_central($paramDatos)
	{
		$this->db->select("stock_actual_malm AS stock_central", FALSE); 
		//$this->db->select('m.idmedicamento, fma.stock_actual_malm, fma2.stock_actual_malm AS stock_central, stock_actual, fma.stock_temporal, fma.precio_venta'); 
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');

		$this->db->where('idmedicamento', $paramDatos['temporal']['producto']['id']);
		$this->db->where('fsa.idtiposubalmacen', 1); // central
		$this->db->where('fsa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_listar_este_medicamento_en_almacenes($idmedicamento)
	{
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento",FALSE);
		$this->db->select('m.idmedicamento, stock_actual_malm, stock_actual'); 
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento'); 
		$this->db->where('m.idmedicamento', $idmedicamento);
		$this->db->where('estado_fma <> 0');
		return $this->db->get()->result_array();
	}
	public function m_cargar_stocks_medicamento($paramPaginate,$paramDatos){
		$this->db->select('fma.idmedicamentoalmacen, s.descripcion as sede, ea.razon_social as empresa, alm.idsedeempresaadmin, fma.idalmacen, alm.nombre_alm, fma.idsubalmacen, salm.nombre_salm, stock_actual_malm, stock_actual'); 
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen alm','fma.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fma.idsubalmacen = salm.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','alm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('m.idmedicamento', $paramDatos['id']);
		$this->db->where('estado_fma <> 0');
		$this->db->order_by('salm.idsubalmacen','ASC');
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_stocks_medicamento($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma','m.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen alm','fma.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fma.idsubalmacen = salm.idsubalmacen');
		$this->db->join('sede_empresa_admin sea','alm.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('m.idmedicamento', $paramDatos['id']);
		$this->db->where('estado_fma <> 0');

		return $this->db->get()->row_array();
	}
	public function m_cargar_historial_precios($paramDatos){
		$this->db->select('fma.precio_venta, hp.precio_venta_anterior, hp.precio_venta_actual, hp.fecha_cambio, hp.motivo');
		$this->db->select('u.idusers, e.idempleado, e.nombres, e.apellido_paterno, e.apellido_materno');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('far_historial_precio hp','fma.idmedicamentoalmacen = hp.idmedicamentoalmacen','left');
		// $this->db->join('far_almacen alm','fma.idalmacen = alm.idalmacen');
		// $this->db->join('far_subalmacen salm','fma.idsubalmacen = salm.idsubalmacen');
		$this->db->join('users u','hp.iduser = u.idusers','left');
		$this->db->join('rh_empleado e','hp.idempleado = e.idempleado','left');
		
		$this->db->where('fma.idmedicamentoalmacen', $paramDatos['idmedicamentoalmacen']);
		$this->db->where('estado_fma <> 0');
		$this->db->order_by('fecha_cambio', 'DESC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_tarifario_farmacia($paramDatos){
		$this->db->select('m.idmedicamento, m.denominacion, (fma.precio_venta)::NUMERIC, fma.stock_actual_malm, lab.nombre_lab as laboratorio');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento m','fma.idmedicamento = m.idmedicamento');
		$this->db->join('far_laboratorio lab', 'm.idlaboratorio = lab.idlaboratorio','left');
		$this->db->where('m.estado_med',1);
		$this->db->where('m.idtipoproducto <>',22);
		$this->db->where('fma.estado_fma',1);
		$this->db->where('fma.idsubalmacen',$paramDatos['subalmacen']['id']);
		$this->db->where('(fma.precio_venta)::NUMERIC <> 0');
		$this->db->order_by('denominacion','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_stock_medicamentos_por_condicion_venta($datos){ 
		$this->db->select('fma.idmedicamentoalmacen, m.idmedicamento, m.denominacion AS medicamento, fma.stock_actual_malm'); 
		$this->db->select('cv.idcondicionventa, cv.descripcion_cv'); 
		$this->db->from('far_medicamento_almacen fma'); 
		$this->db->join('medicamento m','fma.idmedicamento = m.idmedicamento');
		$this->db->join('far_condicion_venta cv','m.idcondicionventa = cv.idcondicionventa'); 
		$this->db->where_in('m.idcondicionventa', $datos['arrCondicionesVenta']);
		$this->db->where('fma.idsubalmacen',$datos['subalmacen']['id']);
		if( $datos['allStocks'] )
			$this->db->where('stock_actual_malm > 0');
		$this->db->order_by('cv.descripcion_cv, m.denominacion', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_medicamento_almacen_busqueda_venta($paramPaginate,$paramDatos){
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$idsubalmacen = $this->sessionHospital['idsubalmacenfarmacia'];
		}else{
			$idsubalmacen = $paramDatos['idsubalmacen'];
		}

		$this->db->select('m.idmedicamento, fma.idmedicamentoalmacen, fma.stock_actual_malm, fma2.stock_actual_malm AS stock_central,
			fma.precio_venta, m.idtipoproducto, lab.idlaboratorio, nombre_lab,');
		$this->db->select("(CASE WHEN generico = 1 THEN (COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,'')) ELSE denominacion END) AS medicamento", FALSE);
		// $this->db->from('far_medicamento_principio mp');
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);
		$this->db->join('far_medicamento_almacen fma2', 'm.idmedicamento = fma2.idmedicamento');
		$this->db->join('far_subalmacen fsa' ,'fma2.idsubalmacen = fsa.idsubalmacen AND fsa.idalmacen = ' . $this->sessionHospital['idalmacenfarmacia'] . ' AND fsa.idtiposubalmacen = 1');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('m.estado_med', 1);

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		// $this->db->group_by('m.idmedicamento, medicamento, fma.idmedicamentoalmacen, fma.stock_actual_malm, stock_central, fma.precio_venta, m.idtipoproducto, lab.idlaboratorio, nombre_lab');
		
		if( $paramPaginate['sortName'] ){ 
				$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		$this->db->order_by('fma.precio_venta', 'DESC');
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_medicamento_almacen_busqueda_venta($paramPaginate,$paramDatos)
	{
		if($this->sessionHospital['key_group'] == 'key_caja_far' || $this->sessionHospital['key_group'] == 'key_asis_far'){
			$idsubalmacen = $this->sessionHospital['idsubalmacenfarmacia'];
		}else{
			$idsubalmacen = $paramDatos['idsubalmacen'];
		}
		$this->db->select('m.idmedicamento');
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);
		$this->db->join('far_medicamento_almacen fma2', 'm.idmedicamento = fma2.idmedicamento');
		$this->db->join('far_subalmacen fsa' ,'fma2.idsubalmacen = fsa.idsubalmacen AND fsa.idalmacen = ' . $this->sessionHospital['idalmacenfarmacia'] . ' AND fsa.idtiposubalmacen = 1');
		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('m.estado_med', 1);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		// $this->db->group_by('m.idmedicamento');
		
		return $this->db->get()->num_rows();
	}
	public function m_cargar_medicamento_almacen_busqueda_atencion_medica($paramPaginate,$paramDatos){
		$idsubalmacen = $paramDatos['idsubalmacen'];

		$this->db->select('m.idmedicamento, m.denominacion AS medicamento, fma.idmedicamentoalmacen, fma.stock_actual_malm, 
			m.idtipoproducto, lab.idlaboratorio, nombre_lab,');
		
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);

		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('m.estado_med', 1);
		$this->db->where('m.idtipoproducto', 18);

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
		
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_medicamento_almacen_busqueda_atencion_medica($paramPaginate,$paramDatos)
	{
		$idsubalmacen = $paramDatos['idsubalmacen'];
		$this->db->select('COUNT(*) AS contador', FALSE);
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento AND fma.idsubalmacen = ' . $idsubalmacen);

		$this->db->join('far_laboratorio lab','m.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('m.estado_med', 1);
		$this->db->where('m.idtipoproducto', 18);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		// $this->db->group_by('m.idmedicamento');
		
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_editar_inline_medicamento_en_almacen($datos)
	{
		$data = array(
			'precio_compra' => $datos['precio_compra_str'],
			'utilidad_porcentaje' => $datos['utilidad_porcentaje'],
			'utilidad_valor' => $datos['utilidad_valor'],
			'precio_venta' => $datos['precio_venta'],
			'porcentaje_venta_kairos' => $datos['porcentaje_venta_kairos_str'],
			'precio_venta_kairos' => $datos['precio_venta_kairos_str'],
			'stock_inicial' => $datos['stock_inicial'],
			'stock_entradas' => $datos['stock_entradas'],
			'stock_salidas' => $datos['stock_salidas'],
			'stock_actual_malm' => $datos['stock_actual_malm'],
			'stock_minimo' => $datos['stock_minimo'],
			'stock_critico' => $datos['stock_critico'],
			'stock_maximo' => $datos['stock_maximo'],
		);
		if( $datos['column'] == 'stock_inicial' ){ 
			$data['fecha_stock_inicial'] = '2016-03-01';
		}
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		return $this->db->update('far_medicamento_almacen', $data);
	}
	public function m_actualizar_stock_medicamento_almacen_salida($datos,$estadoMov='V')
	{
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if( $estadoMov === 'V' ){		// SI ES UNA VENTA
			$this->db->set('stock_salidas', 'stock_salidas+'.$datos['stock_salidas'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_actual_malm-'.$datos['stock_salidas'], FALSE);
		}elseif( $estadoMov === 'A' ){	// SI ES UNA ANULACION
			$this->db->set('stock_salidas', 'stock_salidas-'.$datos['stock_salidas'], FALSE);
			$this->db->set('stock_actual_malm', 'stock_actual_malm+'.$datos['stock_salidas'], FALSE);
		}
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_actualizar_stock_medicamento_almacen_salida_temporal($datos,$estadoMov='V')
	{
		$this->db->where('idmedicamentoalmacen', $datos['idmedicamentoalmacen']);
		if( $estadoMov === 'V' ){		// SI ES UNA VENTA TEMPORAL
			$this->db->set('stock_temporal', 'stock_temporal-'.$datos['stock_salidas'], FALSE);
		}elseif( $estadoMov === 'A' ){	// SI ES UNA ANULACION TEMPORAL
			$this->db->set('stock_temporal', 'stock_temporal+'.$datos['stock_salidas'], FALSE);
		}
		return $this->db->update('far_medicamento_almacen');
	}
	public function m_deshabilitar_medicamento_almacen($datos)
	{
		$data = array(
			'estado_fma' => 2
		);
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		return $this->db->update('far_medicamento_almacen', $data);
	}
	public function m_habilitar_medicamento_almacen($datos)
	{
		$data = array(
			'estado_fma' => 1
		);
		$this->db->where('idmedicamentoalmacen',$datos['idmedicamentoalmacen']);
		return $this->db->update('far_medicamento_almacen', $data);
	}
	public function m_anular_medicamento_almacen($id)
	{
		$data = array(
			'estado_fma' => 0
		);
		$this->db->where('idmedicamentoalmacen',$id);
		return $this->db->update('far_medicamento_almacen', $data);
	}
	public function m_buscar_medicamento_movimiento_detalle($paramDatos)
	{
		$this->db->select("fdm.idmedicamento,(COALESCE(m.denominacion,'') || ' ' || COALESCE(m.descripcion,'')) AS medicamento",FALSE); 
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('medicamento m','m.idmedicamento = fdm.idmedicamento','left'); 
		$this->db->where('fdm.estado_detalle', 1);
		$this->db->where('fdm.idmedicamento',$paramDatos);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_verificar_subalmacen_venta_a_cliente($datos)
	{
		$this->db->select("fsa.idsubalmacen,tsa.venta_a_cliente",FALSE); 
		$this->db->from('far_subalmacen fsa');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen','left'); 
		$this->db->where('fsa.idsubalmacen', $datos['subalmacen']['id']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// ================================ KARDEX ======================================================
	public function m_cargar_stock_inicial($paramDatos)
	{
		$this->db->select('(precio_compra)::NUMERIC, stock_inicial, fecha_stock_inicial, (med.contenido)::NUMERIC'); 
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('estado_fma', 1);
		$this->db->where('idmedicamentoalmacen', $paramDatos['idmedicamentoalmacen']);
		$this->db->limit(1);
		return $this->db->get()->row_array();

	}
	public function m_cargar_kardex($paramDatos) // Se utiliza en: bd_helper
	{
		$this->db->select("med.idmedicamento, med.denominacion, fm.idmovimiento, fm.fecha_movimiento, tipo_movimiento,
			orden_venta, ticket_venta, dir_movimiento, fm.idalmacen, fm.idsubalmacen, fma.stock_inicial, 
			(d.cantidad)::NUMERIC, (d.precio_unitario)::NUMERIC, (med.contenido)::NUMERIC, fm.tipo_nota_credito"); 
		$this->db->from('medicamento med');
		$this->db->join('far_detalle_movimiento d','med.idmedicamento = d.idmedicamento');
		$this->db->join('far_movimiento fm','d.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma','d.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where_in('fm.tipo_movimiento',array(1,2,3,4,5,6));
		// $this->db->where('fm.es_temporal <>', 1);

		$this->db->where('d.estado_detalle', 1);
		$this->db->where('d.idmedicamentoalmacen', $paramDatos['idmedicamentoalmacen']);
		$this->db->order_by('fm.fecha_movimiento','ASC');
		//$this->db->order_by('med.idmedicamento','ASC');
		return $this->db->get()->result_array();

	}
	public function m_cargar_inventario_por_medicamento_almacen($paramDatos){ // Se utiliza en: bd_helper -- para contabilidad

		$this->db->select("CASE WHEN fm.tipo_movimiento = 1 THEN 'VENTA' ELSE (CASE WHEN fm.tipo_movimiento = 2 THEN 'COMPRA' END ) END AS TIPO", FALSE);
		$this->db->select('med.idmedicamento, med.denominacion, med.contenido::NUMERIC, med.excluye_igv', FALSE);
		$this->db->select('d.cantidad, d.precio_unitario::NUMERIC, d.total_detalle::NUMERIC', FALSE);
		$this->db->select('fm.fecha_movimiento, fm.idmovimiento, fm.tipo_movimiento, fm.ticket_venta, fm.idtipodocumento, fm.idcliente, fm.idproveedor');
		$this->db->select('fm.dir_movimiento, fm.idalmacen, fm.idsubalmacen, fma.stock_inicial,');
		$this->db->from('medicamento med');
		$this->db->join('far_detalle_movimiento d','med.idmedicamento = d.idmedicamento');
		$this->db->join('far_movimiento fm','d.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma','d.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where_in('fm.tipo_movimiento',array(1,2,5,6));
		// $this->db->where('fm.es_temporal <>', 1);
		$this->db->where('d.estado_detalle', 1);
		$this->db->where('med.idmedicamento', $paramDatos['medicamento']['idmedicamento']);
		$this->db->where('fma.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where("((DATE_PART('YEAR', fm.fecha_movimiento) = ".$paramDatos['anioDesdeCbo'].
			" AND DATE_PART('MONTH', fm.fecha_movimiento) <= ".$paramDatos['mes']['id'].")".
			" OR DATE_PART('YEAR', fm.fecha_movimiento) < ".$paramDatos['anioDesdeCbo'].")"
			); 
		$this->db->order_by('fm.fecha_movimiento','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_stock_monetizado($paramDatos){
		/*
		subconsulta para obtener el stock de un medicamento en todos los subalmacenes del almacen seleccionado

		SELECT 
			SUM( CASE WHEN (sc_fm.tipo_movimiento = 2 OR sc_fm.tipo_movimiento = 4 OR sc_fm.tipo_movimiento = 6) THEN (sc_fdm.cantidad)::NUMERIC END	) -
			SUM( CASE WHEN (sc_fm.tipo_movimiento = 1 OR sc_fm.tipo_movimiento = 5 ) THEN (sc_fdm.cantidad)::NUMERIC ELSE 0 END	)
		FROM far_detalle_movimiento sc_fdm
		INNER JOIN far_movimiento sc_fm ON sc_fdm.idmovimiento = sc_fm.idmovimiento
		INNER JOIN far_medicamento_almacen sc_fma ON sc_fdm.idmedicamento = sc_fma.idmedicamento
		WHERE 
			"sc_fma"."idmedicamentoalmacen" = "fma"."idmedicamentoalmacen"
		AND "sc_fm"."idalmacen" = '13'
		AND "sc_fm"."estado_movimiento" = 1
		AND sc_fdm.estado_detalle = 1
		AND sc_fm.fecha_movimiento :: TIMESTAMP <= '15-12-2016 23:59' 
		*/
		$this->db->select('SUM( CASE WHEN (sc_fm.tipo_movimiento = 2 OR sc_fm.tipo_movimiento = 6) THEN (sc_fdm.cantidad)::NUMERIC END	) -	SUM( CASE WHEN (sc_fm.tipo_movimiento = 1 OR sc_fm.tipo_movimiento = 5 ) THEN (sc_fdm.cantidad)::NUMERIC ELSE 0 END	)');
		// $this->db->select('SUM( CASE WHEN (sc_fm.tipo_movimiento = 2 OR sc_fm.tipo_movimiento = 4 OR sc_fm.tipo_movimiento = 6) THEN (sc_fdm.cantidad)::NUMERIC END	) -	SUM( CASE WHEN (sc_fm.tipo_movimiento = 1 OR sc_fm.tipo_movimiento = 5 ) THEN (sc_fdm.cantidad)::NUMERIC ELSE 0 END	)');
		$this->db->from('far_detalle_movimiento sc_fdm');
		$this->db->join('far_movimiento sc_fm','sc_fdm.idmovimiento = sc_fm.idmovimiento');
		$this->db->join('far_medicamento_almacen sc_fma','sc_fdm.idmedicamento = sc_fma.idmedicamento');
		$this->db->where('sc_fma.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->where('sc_fm.idalmacen', $paramDatos['almacen']['id']);
		$this->db->where('sc_fm.estado_movimiento', 1);
		$this->db->where('sc_fm.fecha_movimiento::TIMESTAMP <= '.$this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']),NULL,FALSE); // HASTA LA FECHA DE CORTE 
		$this->db->where('sc_fdm.estado_detalle', 1);
		$sqlStock = $this->db->get_compiled_select();
		$this->db->reset_query();
		/* CONSULTA PRINCIPAL */
		$this->db->select("fma.idmedicamentoalmacen, med.idmedicamento, med.denominacion, lab.nombre_lab AS laboratorio, fm.idmovimiento, fm.fecha_movimiento, tipo_movimiento,
			ticket_venta AS factura, dir_movimiento, fm.idalmacen, fm.idsubalmacen, fma.stock_inicial, d.iddetallemovimiento,
			(d.cantidad)::NUMERIC, (d.precio_unitario)::NUMERIC, (d.total_detalle)::NUMERIC");
		$this->db->select('('. $sqlStock .') AS stock_actual_total');
		$this->db->from('medicamento med');
		$this->db->join('far_detalle_movimiento d','med.idmedicamento = d.idmedicamento');
		$this->db->join('far_movimiento fm','d.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma','d.idmedicamentoalmacen = fma.idmedicamentoalmacen');
		$this->db->join('far_laboratorio lab','med.idlaboratorio = lab.idlaboratorio','left'); 
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where_in('fm.tipo_movimiento',array(2)); // solo las compras porque de estos movimientos vamos a obtener el precio ponderado
		$this->db->where('fma.idalmacen', $paramDatos['almacen']['id']); 
		// $this->db->where('fm.es_temporal <>', 1); 
		$this->db->where('d.estado_detalle', 1);
		$this->db->where('fm.fecha_movimiento::TIMESTAMP <= '.$this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']),NULL,FALSE); // HASTA LA FECHA DE CORTE 
		$this->db->order_by('med.denominacion','ASC');
		$this->db->order_by('fm.fecha_movimiento','DESC');
		// $this->db->limit(200,1000); 
		return $this->db->get()->result_array();

	}
	
	public function m_cargar_ultimo_precio_unitario_a_la_fecha($paramDatos){
		$this->db->select('(precio_unitario)::NUMERIC, iddetallemovimiento'); 
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fdm', 'fm.idmovimiento = fdm.idmovimiento');
		$this->db->where('estado_movimiento', 1);
		$this->db->where('tipo_movimiento', 2);
		$this->db->where('fdm.idmedicamento', $paramDatos['idmedicamento']);
		$this->db->where('DATE(fm.fecha_compra) <= ' . $this->db->escape($paramDatos['fecha_movimiento']));
		$this->db->order_by('fm.fecha_movimiento','DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	// ================================ HISTORIAL DE PRECIOS ==========================================
	public function m_registrar_historial_precio($paramDatos)
	{
		$data = array(
			'idmedicamentoalmacen' => $paramDatos['idmedicamentoalmacen'],
			'precio_venta_anterior' => $paramDatos['precio_venta_anterior'],
			'precio_venta_actual' => $paramDatos['precio_venta'],
			'fecha_cambio' => date('Y-m-d H:i:s'),
			'iduser' => $this->sessionHospital['idusers'],
			'idempleado' => $this->sessionHospital['idempleado'],
			'motivo' => empty($paramDatos['motivo'])? NULL : $paramDatos['motivo'],
		);
		
		return $this->db->insert('far_historial_precio', $data);
	}
	public function m_listar_precio_venta($idmedicamentoalmacen){
		$this->db->select('(precio_venta::NUMERIC)');
		$this->db->from('far_medicamento_almacen');
		$this->db->where('idmedicamentoalmacen', $idmedicamentoalmacen);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	//  ================================ ALERTAS ==========================================
	public function m_cargar_medicamento_almacen_por_vencer($paramPaginate=FALSE, $paramDatos=FALSE){
		// 1: VENCIDO
		// 2: MES ACTUAL
		// 3: 2 MESES
		$this->db->select('fdm.num_lote, fdm.iddetallemovimiento, fdm.idmovimiento, fdm.idmedicamento, fdm.idmedicamentoalmacen, me.denominacion, fdm.cantidad,
			fa.nombre_alm as almacen, fsa.nombre_salm as subalmacen, fdm.fecha_vencimiento, me.idlaboratorio, lab.nombre_lab, pr.descripcion_pres');
		$this->db->select("(CASE WHEN current_date > fecha_vencimiento THEN 1 
			WHEN date_part('month', fecha_vencimiento) = date_part('month', now()) THEN 2
			ELSE 3 END) AS estado_vencer", FALSE);
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fm', 'fdm.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen'); 
		$this->db->join('medicamento me','fdm.idmedicamento = me.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_laboratorio lab', 'me.idlaboratorio = lab.idlaboratorio');
		$this->db->join('far_presentacion pr','me.idpresentacion = pr.idpresentacion','left'); 
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fdm.estado_detalle', 1);
		$this->db->where('fdm.alerta_visible', 1);
		$this->db->where("date_part('month', fecha_vencimiento) > date_part('month', now())-2");
		$this->db->where("date_part('month', fecha_vencimiento) < date_part('month', now())+3");
		$this->db->where("date_part('year', fecha_vencimiento) = date_part('year', now())");
		if($paramDatos){
			if( $paramDatos['tipoVence']['id'] != 0 ){
				$this->db->where("(CASE WHEN current_date > fecha_vencimiento THEN 1 
					WHEN date_part('month', fecha_vencimiento) = date_part('month', now()) THEN 2
					ELSE 3 END) = ".$paramDatos['tipoVence']['id']);
			}
		}
		if($paramPaginate){
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
				$this->db->order_by('estado_vencer', 'ASC');
			}
			if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}else{
			$this->db->order_by('estado_vencer', 'ASC');
		}
		
		return $this->db->get()->result_array();
	}
	public function m_count_medicamento_almacen_por_vencer($paramPaginate=FALSE, $paramDatos=FALSE){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_detalle_movimiento fdm');
		$this->db->join('far_movimiento fm', 'fdm.idmovimiento = fm.idmovimiento');
		$this->db->join('far_medicamento_almacen fma','fdm.idmedicamentoalmacen = fma.idmedicamentoalmacen'); 
		$this->db->join('medicamento me','fdm.idmedicamento = me.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_laboratorio lab', 'me.idlaboratorio = lab.idlaboratorio');
		$this->db->join('far_presentacion pr','me.idpresentacion = pr.idpresentacion','left');
		$this->db->where('fm.estado_movimiento', 1);
		$this->db->where('fdm.estado_detalle', 1);
		$this->db->where('fdm.alerta_visible', 1);
		$this->db->where("date_part('month', fecha_vencimiento) > date_part('month', now())-2");
		$this->db->where("date_part('month', fecha_vencimiento) < date_part('month', now())+3");
		$this->db->where("date_part('year', fecha_vencimiento) = date_part('year', now())");
		if($paramDatos){
			if( $paramDatos['tipoVence']['id'] != 0 ){
				$this->db->where("(CASE WHEN current_date > fecha_vencimiento THEN 1 
					WHEN date_part('month', fecha_vencimiento) = date_part('month', now()) THEN 2
					ELSE 3 END) = ".$paramDatos['tipoVence']['id']);
			}
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_cargar_medicamento_almacen_por_agotarse($paramPaginate=FALSE,$paramDatos=FALSE){
		$this->db->select('fma.idmedicamentoalmacen, me.idmedicamento, me.denominacion,
			fa.nombre_alm AS almacen, fsa.nombre_salm AS subalmacen,
			fma.stock_actual_malm, fma.stock_minimo, fma.stock_critico, fma.stock_maximo,
			lab.idlaboratorio,	lab.nombre_lab, tp.idtipoproducto');
		$this->db->select("(CASE WHEN stock_actual_malm > stock_minimo THEN 1
			WHEN (stock_actual_malm <= stock_minimo AND stock_actual_malm > 0) THEN 2 ELSE 3 END) AS estado", FALSE);
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento me', 'fma.idmedicamento = me.idmedicamento');
		$this->db->join('tipo_producto tp', 'tp.idtipoproducto = me.idtipoproducto AND tp.idtipoproducto <> 22');
		$this->db->join('far_almacen fa', 'fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa', 'fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_laboratorio lab', 'me.idlaboratorio = lab.idlaboratorio');
		$this->db->where('fma.estado_fma', 1);
		// $this->db->where('fsa.es_principal', 1);
		$this->db->where('fsa.es_notificable', 1);
		$this->db->where('stock_actual_malm <= stock_critico');
		$this->db->where('stock_entradas > 0');
		if($paramDatos){
			$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']);
		}else{
			if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
				if( $this->sessionHospital['key_group'] == 'key_dir_far' ){
					$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
				}
			}
		}
		if($paramPaginate){
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
				$this->db->order_by('estado', 'ASC');
			}
			if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
				$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			}
		}else{
			$this->db->order_by('estado', 'ASC');
		}
		return $this->db->get()->result_array();
	}
	public function m_count_medicamento_almacen_por_agotarse($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento me', 'fma.idmedicamento = me.idmedicamento');
		$this->db->join('far_almacen fa', 'fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa', 'fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_laboratorio lab', 'me.idlaboratorio = lab.idlaboratorio');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fsa.es_principal', 1);
		$this->db->where('stock_actual_malm <= stock_critico');
		$this->db->where('stock_entradas > 0');
		if($paramDatos){
			$this->db->where('fa.idalmacen', $paramDatos['almacen']['id']);
		}else{
			if( $this->sessionHospital['key_group'] != 'key_sistemas' ){
				if( $this->sessionHospital['key_group'] == 'key_dir_far' ){
					$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
				}
			}
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}
	public function m_quitar_alerta($id){
		$data = array(
			'alerta_visible' => 2
		);
		$this->db->where('iddetallemovimiento',$id);
		if($this->db->update('far_detalle_movimiento', $data)){
			return true;
		}else{
			return false;
		}
	}
	
	public function m_cargar_preparado_almacen_busqueda_venta($paramPaginate,$paramDatos){

		$this->db->select('m.idmedicamento, fma.idmedicamentoalmacen, fma.precio_venta, m.idtipoproducto, fma.precio_venta::NUMERIC AS precio_venta_sf',FALSE);
		$this->db->select("denominacion AS medicamento");
		// $this->db->from('far_medicamento_principio mp');
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_tipo_subalmacen fts','fsa.idtiposubalmacen = fts.idtiposubalmacen AND fts.venta_a_cliente = 1');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('sea.idsede', $this->sessionHospital['idsede']);
		$this->db->where('m.estado_med', 1);
		$this->db->where('estado_med', 1); 
		$this->db->where('estado_fma', 1);
		$this->db->where('m.idtipoproducto', 22);

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
		$this->db->order_by('fma.precio_venta', 'DESC');
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_preparado_almacen_busqueda_venta($paramPaginate,$paramDatos)
	{
		$this->db->select('m.idmedicamento');
		$this->db->from('medicamento m');
		$this->db->join('far_medicamento_almacen fma', 'm.idmedicamento = fma.idmedicamento');
		$this->db->join('far_almacen fa','fma.idalmacen = fa.idalmacen');
		$this->db->join('far_subalmacen fsa','fma.idsubalmacen = fsa.idsubalmacen');
		$this->db->join('far_tipo_subalmacen fts','fsa.idtiposubalmacen = fts.idtiposubalmacen AND fts.venta_a_cliente = 1');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->where('sea.idsede', $this->sessionHospital['idsede']);
		$this->db->where('m.estado_med', 1);
		$this->db->where('estado_med', 1); 
		$this->db->where('estado_fma', 1);
		$this->db->where('m.idtipoproducto', 22);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				} 
			} 
		}
		return $this->db->get()->num_rows();
	}
}