<?php
class Model_almacen_farmacia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
		public function m_cargar_almacenes_session($paramPaginate = FALSE){ /* Aqui luego se pondrá la lógica de usuarios */ 
		$this->db->select('fa.idalmacen, estado_alm, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin', FALSE); // , fsa.idsubalmacen, fsa.nombre_salm
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		//$this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		//$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen AND tsa.venta_a_cliente = 1');
		$this->db->where('fa.estado_alm <>', 0); 
		//$this->db->where('fsa.estado_salm', 1); 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}
		/*------------------------------------------------------------------------------------------*/
		if( !empty($paramPaginate['search']) ){
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
		/*------------------------------------------------------------------------------------------*/
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_almacenes_session($paramPaginate = FALSE){

		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		// $this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		// $this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen AND tsa.venta_a_cliente = 1');
		$this->db->where('fa.estado_alm <>', 0); 
		//$this->db->where('fsa.estado_salm', 1); 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}
		/*------------------------------------------------------------------------------------------*/
		if($paramPaginate){
			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
				
		}

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_cargar_almacenes_medicamento_session($idmedicamento=FALSE){ /* Aqui luego se pondrá la lógica de usuarios */ 
		$this->db->select('fa.idalmacen, estado_alm, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin, fsa.idsubalmacen, fsa.nombre_salm', FALSE);
		if( $idmedicamento ){
			$this->db->select('fma.idmedicamentoalmacen, (fma.precio_venta)::NUMERIC');
		}
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen AND tsa.venta_a_cliente = 1');
		if( $idmedicamento ){
			$this->db->join('far_medicamento_almacen fma','fsa.idsubalmacen = fma.idsubalmacen AND fma.idmedicamento = '.$idmedicamento, 'left');
		}
		$this->db->where('fa.estado_alm <>', 0); 
		$this->db->where('fsa.estado_salm', 1); 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			$this->db->where('sea.idsede', $this->sessionHospital['idsede']);
		}
		if( $this->sessionHospital['key_group'] == 'key_derma' ){
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
			// $this->db->where->('ea.idempresaadmin');
		}
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_almacenes_medicamento_session($idmedicamento=FALSE){

		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen AND tsa.venta_a_cliente = 1');
		if( $idmedicamento ){
			$this->db->join('far_medicamento_almacen fma','fsa.idsubalmacen = fma.idsubalmacen AND fma.idmedicamento = '.$idmedicamento, 'left');
		}
		$this->db->where('fa.estado_alm <>', 0); 
		$this->db->where('fsa.estado_salm', 1); 
		if( $this->sessionHospital['key_group'] != 'key_sistemas' ){ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		}
		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_cargar_almacenes_edicion_session($idMedicamento){ /* Aqui luego se podrá la lógica de usuarios */ 
		$this->db->select('fa.idalmacen, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin, fma.idmedicamentoalmacen, fma.precio_venta', FALSE);
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->join('far_medicamento_almacen fma','fa.idalmacen = fma.idalmacen AND fma.idmedicamento = '.$idMedicamento,'left');
		$this->db->where('fa.estado_alm', 1); 
		if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			
		}elseif( $this->sessionHospital['key_group'] == 'key_admin_far' ){ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}else{ 
			$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		}
		$this->db->order_by('fa.idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_almacenes_destino_de_empresa_session($datos)
	{
		$this->db->select('fa.idalmacen, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin', FALSE);
		$this->db->select('ea.idempresaadmin ,ea.ruc, s.direccion_se AS direccion');
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('fa.estado_alm', 1); 
		$this->db->where('ea.idempresaadmin', $datos['idempresaadmin']); 
		if( $this->sessionHospital['key_group'] !== 'key_sistemas' &&
			$this->sessionHospital['key_group'] !== 'key_admin' &&
			$this->sessionHospital['key_group'] !== 'key_admin_far' &&
			$this->sessionHospital['key_group'] !== 'key_gerencia' &&
			$this->sessionHospital['key_group'] !== 'key_logistica' &&
			$this->sessionHospital['key_group'] !== 'key_rrhh' &&
			$this->sessionHospital['key_group'] !== 'key_rrhh_asistente' )
		{ 
			$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']); 
		}
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_almacenes_destino_de_empresa_para_traslado_session($datos)
	{
		$this->db->select('fa.idalmacen, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin', FALSE);
		$this->db->select('ea.idempresaadmin ,ea.ruc, s.direccion_se AS direccion');
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('fa.estado_alm', 1); 
		$this->db->where('ea.idempresaadmin', $datos['idempresaadmin']); 
		// if( $this->sessionHospital['key_group'] !== 'key_sistemas' &&
		// 	$this->sessionHospital['key_group'] !== 'key_admin' &&
		// 	$this->sessionHospital['key_group'] !== 'key_admin_far' &&
		// 	$this->sessionHospital['key_group'] !== 'key_gerencia' &&
		// 	$this->sessionHospital['key_group'] !== 'key_logistica' &&
		// 	$this->sessionHospital['key_group'] !== 'key_rrhh' &&
		// 	$this->sessionHospital['key_group'] !== 'key_rrhh_asistente' )
		// { 
		// 	$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']); 
		// }
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	/* COMBO MATRIZ PARA ACCESOS A CONSULTAS - FILTROS | SESSION */
	public function m_cargar_almacenes_cbo_session()
	{
		$this->db->select('fa.idalmacen, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin', FALSE);
		$this->db->select('ea.idempresaadmin ,ea.ruc, s.direccion_se AS direccion');
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('fa.estado_alm', 1); 
		if( $this->sessionHospital['key_group'] !== 'key_sistemas' &&
			$this->sessionHospital['key_group'] !== 'key_admin' &&
			$this->sessionHospital['key_group'] !== 'key_admin_far' &&
			$this->sessionHospital['key_group'] !== 'key_gerencia' &&
			$this->sessionHospital['key_group'] !== 'key_logistica' &&
			$this->sessionHospital['key_group'] !== 'key_rrhh' &&
			$this->sessionHospital['key_group'] !== 'key_rrhh_asistente' )
		{ 
			$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']); 
		}
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_almacenes_cbo_para_traslado_session()
	{
		$this->db->select('fa.idalmacen, sea.idsedeempresaadmin, nombre_alm, (s.descripcion) AS sede , (ea.razon_social) AS empresa_admin', FALSE);
		$this->db->select('ea.idempresaadmin ,ea.ruc, s.direccion_se AS direccion');
		$this->db->from('far_almacen fa');
		$this->db->join('sede_empresa_admin sea','fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('fa.estado_alm', 1); 
		// if( $this->sessionHospital['key_group'] !== 'key_sistemas' &&
		// 	$this->sessionHospital['key_group'] !== 'key_admin' &&
		// 	$this->sessionHospital['key_group'] !== 'key_admin_far' &&
		// 	$this->sessionHospital['key_group'] !== 'key_gerencia' &&
		// 	$this->sessionHospital['key_group'] !== 'key_logistica' &&
		// 	$this->sessionHospital['key_group'] !== 'key_rrhh' &&
		// 	$this->sessionHospital['key_group'] !== 'key_rrhh_asistente' )
		// { 
		// 	$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']); 
		// }
		$this->db->order_by('idalmacen','ASC');
		return $this->db->get()->result_array();
	}
	/* TRASLADOS */
	public function m_cargar_traslados($paramDatos, $paramPaginate){
		$this->db->select('fm.idmovimiento AS idmovimiento1, fm2.idmovimiento AS idmovimiento2, 
			fm.dir_movimiento AS dir_movimiento1, fm2.dir_movimiento AS dir_movimiento2, 
			fm.idalmacen, alm.nombre_alm, 
			fm.idsubalmacen AS idsubalmacen1, salm.nombre_salm AS subAlmacenOrigen,
			fm2.idsubalmacen AS idsubalmacen2, salm2.nombre_salm AS subAlmacenDestino,
		 	fm.fecha_movimiento, fd.cantidad, med.denominacion');
		$this->db->from('far_movimiento fm');
		$this->db->join('far_detalle_movimiento fd','fm.idmovimiento = fd.idmovimiento');
		$this->db->join('medicamento med','fd.idmedicamento = med.idmedicamento');
		$this->db->join('far_almacen alm','fm.idalmacen = alm.idalmacen');
		$this->db->join('far_subalmacen salm','fm.idsubalmacen = salm.idsubalmacen AND fm.dir_movimiento = 2');

		$this->db->join('far_movimiento fm2','fm.idmovimiento = fm2.idtrasladoorigen');
		$this->db->join('far_subalmacen salm2','fm2.idsubalmacen = salm2.idsubalmacen AND fm2.dir_movimiento = 1');

		$this->db->where('fm.estado_movimiento', 1); // MOVIMIENTO
		$this->db->where('fm.tipo_movimiento', 3); // TRASLADO
		$this->db->where('alm.idalmacen', $paramDatos['idalmacen']);
		$this->db->where('fm.fecha_movimiento BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		// if( $this->sessionHospital['key_group'] == 'key_sistemas' ){ 
			
		// }elseif( $this->sessionHospital['key_group'] == 'key_admin_far' ){ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }else{ 
		// 	$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		// }
		/*------------------------------------------------------------------------------------------*/
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		// if( $paramPaginate['sortName'] ){
		// 	$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		// }
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		/*------------------------------------------------------------------------------------------*/
		
		return $this->db->get()->result_array();
	}
	public function m_cargar_productos_subalmacen($datos, $paramPaginate){
		$this->db->select('fma.idmedicamentoalmacen, med.idmedicamento, med.denominacion, stock_actual_malm');
		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen1']);
		$this->db->where('med.estado_med', 1);

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
	public function m_count_productos_subalmacen($datos, $paramPaginate){

		$this->db->from('far_medicamento_almacen fma');
		$this->db->join('medicamento med', 'fma.idmedicamento = med.idmedicamento');
		$this->db->where('fma.estado_fma', 1);
		$this->db->where('fma.idsubalmacen', $datos['idsubalmacen1']);
		$this->db->where('med.estado_med', 1);

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	
	public function m_registrar_almacen($datos)
	{
		$data = array( 
			'idsedeempresaadmin' => $datos['idsedeempresaadmin'],
			'nombre_alm' => $datos['nombre_alm'],
			'estado_alm' => 1 ,
			'createdAt' => date('Y-m-d H:i:s') ,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('far_almacen', $data);
	}
	public function m_editar_almacen($datos)
	{
		$data = array( 
			'idsedeempresaadmin' => $datos['idsedeempresaadmin'],
			'nombre_alm' => $datos['nombre_alm'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idalmacen',$datos['id']);
		return $this->db->update('far_almacen', $data);
	}
	public function m_anular_almacen($id)
	{
		$data = array(
			'estado_alm' => 0
		);
		$this->db->where('idalmacen',$id);
		if($this->db->update('far_almacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar_almacen($id)
	{
		$data = array(
			'estado_alm' => 1
		);
		$this->db->where('idalmacen',$id);
		if($this->db->update('far_almacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar_almacen($id)
	{
		$data = array(
			'estado_alm' => 2
		);
		$this->db->where('idalmacen',$id);
		if($this->db->update('far_almacen', $data)){
			return true;
		}else{
			return false;
		}
	}


	//-------------  SUB ALMACEN -------------------//
	public function m_cargar_subalmacenes($paramPaginate,$paramDatos){ 
		$this->db->select('fsa.idsubalmacen,fsa.idalmacen,fsa.nombre_salm,fsa.estado_salm,fsa.es_principal,
							fsa.idtiposubalmacen , tsa.descripcion_tsa'); 
		$this->db->from('far_subalmacen fsa'); 
		$this->db->join('far_tipo_subalmacen tsa','tsa.idtiposubalmacen = fsa.idtiposubalmacen'); 
		$this->db->where('fsa.estado_salm', 1); // habilitado
		$this->db->where('fsa.idalmacen', $paramDatos); // habilitado
		//$this->db->where_in('c.estado', array(1,2) ); // habilitado o deshabilitado 
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
	public function m_count_subalmacenes($paramPaginate,$paramDatos){ 
		$this->db->select('*'); 
		$this->db->from('far_subalmacen fsa'); 
		$this->db->join('far_tipo_subalmacen tsa','tsa.idtiposubalmacen = fsa.idtiposubalmacen'); 
		$this->db->where('fsa.estado_salm', 1); // habilitado
		$this->db->where('fsa.idalmacen', $paramDatos); // habilitado
		//$this->db->where_in('c.estado', array(1,2) ); // habilitado o deshabilitado 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		
		$totalRows = $this->db->get()->num_rows();
		return $totalRows;
	}
	public function m_obtener_subalmacen_principal($idAlmacen)
	{
		$this->db->select('fa.idalmacen, fsa.idsubalmacen', FALSE);
		$this->db->from('far_almacen fa');
		$this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		$this->db->where('fa.idalmacen', $idAlmacen);
		$this->db->where('es_principal', 1);
		$this->db->order_by('idalmacen','ASC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_subalmacen_venta($idAlmacen)
	{
		$this->db->select('fa.idalmacen, fsa.idsubalmacen, nombre_salm');
		$this->db->from('far_almacen fa');
		$this->db->join('far_subalmacen fsa','fa.idalmacen = fsa.idalmacen');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen');
		$this->db->where('fa.idalmacen', $idAlmacen);
		$this->db->where('tsa.venta_a_cliente', 1);
		$this->db->where('fsa.estado_salm', 1);
		// $this->db->order_by('idalmacen','ASC');
		// $this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_sub_almacenes_cbo($datos)
	{
		$this->db->select('idsubalmacen, nombre_salm, fa.idalmacen, nombre_alm', FALSE);
		$this->db->from('far_subalmacen fas');
		$this->db->join('far_almacen fa','fas.idalmacen = fa.idalmacen');
		$this->db->where('fas.estado_salm', 1);
		$this->db->where('fa.idalmacen', $datos['idalmacen']);
		$this->db->where_in('fas.idtiposubalmacen', array(1,2)); 
		// $this->db->where('fa.estado_se', 1);
		$this->db->order_by('idsubalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_sub_almacenes_preparado_cbo($datos)
	{
		$this->db->select('idsubalmacen, nombre_salm, fa.idalmacen, nombre_alm', FALSE);
		$this->db->from('far_subalmacen fas');
		$this->db->join('far_almacen fa','fas.idalmacen = fa.idalmacen');
		$this->db->join('far_tipo_subalmacen ftas','ftas.venta_a_cliente = 1 AND fas.idtiposubalmacen = ftas.idtiposubalmacen');
		$this->db->where('fas.estado_salm', 1);
		$this->db->where('fa.idalmacen', $datos['idalmacen']);
		$this->db->where_in('fas.idtiposubalmacen', array(1,2)); 
		// $this->db->where('fa.estado_se', 1);
		$this->db->order_by('idsubalmacen','ASC');
		return $this->db->get()->result_array();
	}

	public function m_cargar_sub_almacenes_excepto_cbo($datos)
	{
		$this->db->select('fas.idsubalmacen, nombre_salm, fa.idalmacen, nombre_alm', FALSE);
		$this->db->from('far_subalmacen fas');
		$this->db->join('far_almacen fa','fas.idalmacen = fa.idalmacen');
		$this->db->where('fas.estado_salm', 1);
		$this->db->where('fa.idalmacen', $datos['idalmacen']);
		$this->db->where_in('fas.idtiposubalmacen', array(1,2)); 
		if(@$datos['idsubalmacen1'] != 0){
			$this->db->where('idsubalmacen <>', $datos['idsubalmacen1']);
			//$this->db->where('fa.idalmacen <>', $datos['idsubalmacenorigen']);
		}
		 
		// $this->db->where('fa.estado_se', 1);
		$this->db->order_by('idsubalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_subalmacenes_sin_central($datos)
	{
		$this->db->select('fa.idalmacen, fas.idsubalmacen, fas.nombre_salm, fa.nombre_alm', FALSE);
		$this->db->from('far_almacen fa');
		$this->db->join('far_subalmacen fas','fa.idalmacen = fas.idalmacen');
		$this->db->join('far_tipo_subalmacen fts','fas.idtiposubalmacen = fts.idtiposubalmacen');
		$this->db->where('fas.estado_salm', 1); 
		$this->db->where('fas.es_principal', 2); // NO ES CENTRAL 
		$this->db->where('fa.idalmacen', $datos['idalmacen']); 
		$this->db->where('fa.estado_alm', 1); 
		$this->db->order_by('idsubalmacen','ASC'); 
		return $this->db->get()->result_array(); 
	}
	public function m_cargar_sub_almacenes_para_venta_cbo()
	{
		$this->db->select('idsubalmacen, nombre_salm, fa.idalmacen, nombre_alm, fa.idsedeempresaadmin', FALSE);
		$this->db->from('far_subalmacen fsa');
		$this->db->join('far_almacen fa','fsa.idalmacen = fa.idalmacen');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen');
		$this->db->where('fsa.estado_salm', 1);
		$this->db->where('fa.idalmacen', $this->sessionHospital['idalmacenfarmacia']);
		if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_dir_far'){
			$this->db->where('fsa.idsubalmacen', $this->sessionHospital['idsubalmacenfarmacia']);
		}
		$this->db->where('tsa.venta_a_cliente', 1);
		$this->db->order_by('idsubalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_sub_almacenes_venta_sede_cbo()
	{
		$this->db->select('idsubalmacen, nombre_salm, fa.idalmacen, nombre_alm, fa.idsedeempresaadmin', FALSE);
		$this->db->from('far_subalmacen fsa');
		$this->db->join('far_almacen fa','fsa.idalmacen = fa.idalmacen');
		$this->db->join('far_tipo_subalmacen tsa','fsa.idtiposubalmacen = tsa.idtiposubalmacen');
		$this->db->join('sede_empresa_admin sea', 'fa.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s', 'sea.idsede = s.idsede');
		$this->db->where('fsa.estado_salm', 1);
		$this->db->where('fa.estado_alm', 1);
		$this->db->where('tsa.venta_a_cliente', 1);
		$this->db->where('s.idsede', $this->sessionHospital['idsede']);
		$this->db->order_by('idalmacen','DESC');
		$this->db->order_by('idsubalmacen','ASC');
		return $this->db->get()->result_array();
	}
	public function m_registrar_subalmacen($datos)
	{
		$data = array( 
			'idalmacen' => $datos['idalmacen'],
			'nombre_salm' => strtoupper($datos['nombre_salm']),
			'estado_salm' => 1 ,
			'createdAt' => date('Y-m-d H:i:s') ,
			'updatedAt' => date('Y-m-d H:i:s') ,
			'es_principal' => $datos['es_principal'] ,
			'idtiposubalmacen' => $datos['tiposubalmacen']
		);
		return $this->db->insert('far_subalmacen', $data);
	}
	public function m_editar_subalmacen_en_grid($datos)
	{
		if($datos['column'] == 'nombre_salm'){
			$data = array(
				'nombre_salm' => strtoupper($datos['nombre_salm']),
			);
		}elseif($datos['column'] == 'descripcion_tsa'){
			$data = array(
				'idtiposubalmacen' => $datos['newvalue'],
			);
		}
		$this->db->where('idsubalmacen',$datos['id']);
		if($this->db->update('far_subalmacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_anular_subalmacen($id)
	{
		$data = array(
			'estado_salm' => 0
		);
		$this->db->where('idsubalmacen',$id);
		if($this->db->update('far_subalmacen', $data)){
			return true;
		}else{
			return false;
		}
	}

	/*****************************************************/
	/*********     TIPO DE SUB-ALMACEN    ****************/
	/*****************************************************/
	public function m_cargar_tipo_subalmacen_cbo($datos=FALSE)
	{
		$this->db->select('idtiposubalmacen, descripcion_tsa, venta_a_cliente, estado_tsa');
		$this->db->from('far_tipo_subalmacen');
		$this->db->where('estado_tsa <>', 0);
		if( $datos ){ 
			$this->db->like('LOWER('.$datos['nameColumn'].')', strtolower($datos['search']));
		}else{ 
			$this->db->limit(100);
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_subalmacen($paramPaginate){
		//$this->db->select('idtipoExamen, descripcion, estado_tex');
		$this->db->from('far_tipo_subalmacen');
		$this->db->where('estado_tsa <>', 0);
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
	public function m_count_tipo_subalmacen($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('far_tipo_subalmacen');
		$this->db->where('estado_tsa <>', 0);
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

	public function m_editar_tipo_subalmacen($datos)
	{
		$data = array(
			'descripcion_tsa' => strtoupper($datos['descripcion']),
			'venta_a_cliente' => ($datos['venta_a_cliente'] == true ? 1 : 2)
		);
		$this->db->where('idtiposubalmacen',$datos['id']);
		return $this->db->update('far_tipo_subalmacen', $data);
	}
	public function m_registrar_tipo_subalmacen($datos)
	{
		$data = array(
			'descripcion_tsa' => strtoupper($datos['descripcion']),
			'venta_a_cliente' => ($datos['venta_a_cliente'] == true ? 1 : 2),
			'estado_tsa' => 1,
		);
		return $this->db->insert('far_tipo_subalmacen', $data);
	}
	public function m_anular_tipo_subalmacen($id)
	{
		$data = array(
			'estado_tsa' => 0
		);
		$this->db->where('idtiposubalmacen',$id);
		if($this->db->update('far_tipo_subalmacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_habilitar_tipo_subalmacen($id)
	{
		$data = array(
			'estado_tsa' => 1
		);
		$this->db->where('idtiposubalmacen',$id);
		if($this->db->update('far_tipo_subalmacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_deshabilitar_tipo_subalmacen($id)
	{
		$data = array(
			'estado_tsa' => 2
		);
		$this->db->where('idtiposubalmacen',$id);
		if($this->db->update('far_tipo_subalmacen', $data)){
			return true;
		}else{
			return false;
		}
	}
	/****************************************************/
	/*********  FIN DE TIPO SUBALMACEN   ****************/
	/****************************************************/

}