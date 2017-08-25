<?php
class Model_convenio extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_convenio_cbo(){ 
		$this->db->select('idtipocliente, descripcion_tc');
		$this->db->from('tipo_cliente');
		$this->db->where('estado_tc', 1); // activo
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	
	public function m_cargar_convenio($paramPaginate, $paramDatos){ 
		$this->db->select('tc.idtipocliente, tc.descripcion_tc, tc.numero_contrato, tc.fecha_inicial, tc.fecha_vigencia,  tc.estado_tc');
		$this->db->select('tc.idsedeempresaadmin, ea.razon_social, s.descripcion, tc.porcentaje');
		$this->db->from('tipo_cliente tc');
		$this->db->join('sede_empresa_admin sea','tc.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_tc', 1); // activo
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_se', 1);
		if( $paramDatos['sedeempresa'] != '0' ){
			$this->db->where('tc.idsedeempresaadmin', $paramDatos['sedeempresa']);
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
	public function m_count_convenio($paramPaginate, $paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('tipo_cliente tc');
		$this->db->join('sede_empresa_admin sea','tc.idsedeempresaadmin = sea.idsedeempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');
		$this->db->where('estado_tc', 1); // activo
		$this->db->where('estado_sea <>', 0);
		$this->db->where('estado_emp <>', 0);
		$this->db->where('estado_se', 1);
		$this->db->where('tc.idsedeempresaadmin', $paramDatos['sedeempresa']);
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
	public function m_cargar_cliente_convenio($paramPaginate, $paramDatos){ 

		$this->db->select('cli.idcliente, cli.nombres, cli.apellido_paterno, cli.apellido_materno, cli.num_documento, cli.fecha_nacimiento, cli.sexo');
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->from('cliente cli');
		$this->db->join('tipo_cliente tc','cli.idtipocliente = tc.idtipocliente');
		
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('estado_tc', 1);
		$this->db->where('cli.idtipocliente', $paramDatos['idtipocliente']);

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
	public function m_count_cliente_convenio($paramPaginate, $paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('cliente cli');
		$this->db->join('tipo_cliente tc','cli.idtipocliente = tc.idtipocliente');
		
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('estado_tc', 1);
		$this->db->where('cli.idtipocliente', $paramDatos['idtipocliente']);
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
	public function m_cargar_producto_convenio($paramPaginate, $paramDatos){ 

		$this->db->select('pm.idproductomaster, pm.descripcion AS producto, pps.precio_sede, cps.precio_variable, cps.estado_cps');
		$this->db->select('(pps.precio_sede)::NUMERIC AS precio_sede_sf, (cps.precio_variable)::NUMERIC AS precio_variable_sf');
		$this->db->select('e.nombre AS especialidad, tp.nombre_tp AS tipo_producto, cps.idconvenioproductosede, pps.idproductopreciosede' );
		$this->db->select('(CASE WHEN (pps.precio_sede)::NUMERIC = 0 THEN 0 ELSE ( 1 - ( (cps.precio_variable)::NUMERIC / (pps.precio_sede)::NUMERIC ) )*100 END ) AS porcentaje');
		$this->db->from('convenio_producto_sede cps');
		$this->db->join('producto_precio_sede pps','cps.idproductopreciosede = pps.idproductopreciosede');
		$this->db->join('tipo_cliente tc','pps.idsedeempresaadmin = tc.idsedeempresaadmin');
		$this->db->join('producto_master pm','pps.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');

		$this->db->where('estado_cps <>', 0); // activo
		$this->db->where('estado_pps', 1);
		$this->db->where('cps.idtipocliente = tc.idtipocliente');
		$this->db->where('tc.idtipocliente', $paramDatos['idtipocliente']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		if($paramDatos['soloDecimales']){
			$this->db->where('(cps.precio_variable)::NUMERIC % 1 != 0');
		}

		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_producto_convenio($paramPaginate, $paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('convenio_producto_sede cps');
		$this->db->join('producto_precio_sede pps','cps.idproductopreciosede = pps.idproductopreciosede');
		$this->db->join('tipo_cliente tc','pps.idsedeempresaadmin = tc.idsedeempresaadmin');
		$this->db->join('producto_master pm','pps.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');

		$this->db->where('estado_cps <>', 0); // activo
		$this->db->where('estado_pps', 1);
		$this->db->where('cps.idtipocliente = tc.idtipocliente');
		$this->db->where('tc.idtipocliente', $paramDatos['idtipocliente']);
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		
		if($paramDatos['soloDecimales']){
			$this->db->where('(cps.precio_variable)::NUMERIC % 1 != 0');
		}
		
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	// public function m_cargar_convenio_cliente($datos){ 
	// 	$this->db->select('idtipocliente, descripcion_tc, estado_tc');
	// 	$this->db->from('tipo_cliente');
	// 	$this->db->where('estado_tc', 1); // activo
	// 	$this->db->where('idtipocliente', $datos['idtipocliente']); // activo
	// 	return $this->db->get()->result_array();
	// }

	public function m_cargar_producto_no_estan_convenio($paramPaginate, $paramDatos){ 

		$this->db->select('pm.idproductomaster, pm.descripcion AS producto, pps.precio_sede, pps.idproductopreciosede');
		$this->db->select('(pps.precio_sede)::NUMERIC AS precio_sede_sf');
		$this->db->select('e.nombre AS especialidad, tp.nombre_tp AS tipo_producto, ');

		$this->db->from('producto_precio_sede pps');
		$this->db->join('tipo_cliente tc','pps.idsedeempresaadmin = tc.idsedeempresaadmin');
		$this->db->join('producto_master pm','pps.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_pps', 1);
		$this->db->where('tc.idtipocliente', $paramDatos['idtipocliente']);
		$this->db->where('pps.idproductopreciosede NOT IN (SELECT a.idproductopreciosede 
															from convenio_producto_sede a
															where a.estado_cps <> 0 
															AND a.idtipocliente = '.$paramDatos['idtipocliente'] .')');
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

	public function m_count_producto_no_estan_convenio($paramPaginate, $paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('producto_precio_sede pps');
		$this->db->join('tipo_cliente tc','pps.idsedeempresaadmin = tc.idsedeempresaadmin');
		$this->db->join('producto_master pm','pps.idproductomaster = pm.idproductomaster');
		$this->db->join('especialidad e','pm.idespecialidad = e.idespecialidad');
		$this->db->join('tipo_producto tp','pm.idtipoproducto = tp.idtipoproducto');
		$this->db->where('estado_pps', 1);
		$this->db->where('tc.idtipocliente', $paramDatos['idtipocliente']);
		$this->db->where('pps.idproductopreciosede NOT IN (SELECT a.idproductopreciosede 
															from convenio_producto_sede a
															where a.estado_cps <> 0 
															AND a.idtipocliente = '.$paramDatos['idtipocliente'] .')');
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

	public function m_cargar_tipo_producto($datos){ 
		$this->db->select('idtipoproducto, nombre_tp');
		$this->db->from('tipo_producto');
		$this->db->where('estado_tp', 1); // activo
		
		return $this->db->get()->result_array();
	}

	public function m_registrar_convenio($datos){
		$data = array(
			'descripcion_tc' => strtoupper_total($datos['descripcion']),
			'numero_contrato' => $datos['contrato'],
			'fecha_inicial' => $datos['fec_inicial'],
			'fecha_vigencia' => $datos['fec_vigencia'],
			'idsedeempresaadmin'=> $datos['sede_convenio'],
			'estado_tc' => 1

		);
		return $this->db->insert('tipo_cliente', $data);
	}
	public function m_editar_convenio($datos)
	{
		$data = array(
			'descripcion_tc' => strtoupper_total($datos['descripcion']),
			'numero_contrato' => $datos['contrato'],
			'fecha_inicial' => $datos['fec_inicial'],
			'fecha_vigencia' => $datos['fec_vigencia'],
			'idsedeempresaadmin'=> $datos['sede_convenio'],
		);
		$this->db->where('idtipocliente',$datos['idtipocliente']);
		return $this->db->update('tipo_cliente', $data);
	}
	public function m_anular_convenio($id)
	{
		$data = array(
			'estado_tc' => 0
		);
		$this->db->where('idtipocliente',$id);
		if($this->db->update('tipo_cliente', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_afiliar_cliente_a_puntos($id)
	{
		$data = array(
			'si_afiliado_puntos' => 1,
			'fecha_afiliacion_puntos' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$id);
		return $this->db->update('cliente', $data);
	}
	public function m_iniciar_puntaje_cliente($id)
	{
		$data = array(
			'idcliente' => $id,
			'puntos_acumulados' => 0,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_pd' => 1
		);
		return $this->db->insert('far_punto_descuento', $data);
	}
	public function m_comprobar_afiliacion_puntos($datos)
	{
		$this->db->select('num_documento, idcliente');
		$this->db->from('cliente');
		$this->db->where('num_documento',$datos['num_documento']);
		$this->db->where('si_afiliado_puntos', 1);
		$this->db->where('estado_cli', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_puntaje_cliente($datos)
	{
		$this->db->select('idpuntodescuento, idcliente, puntos_acumulados, estado_pd');
		$this->db->from('far_punto_descuento');
		$this->db->where('idcliente',$datos['idcliente']);
		$this->db->where('estado_pd', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_actualizar_puntaje_cliente($datos)
	{
		$data = array(
			'puntos_acumulados' => $datos['puntaje_obtenido'],
			'updatedAt'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idpuntodescuento',$datos['idpuntodescuento']);
		return $this->db->update('far_punto_descuento', $data);
	}
	public function m_actualizar_puntaje_con_canje($datos)
	{
		$data = array(
			'fecha_canje' => date('Y-m-d H:i:s'),
			'estado_pd'=> 2
		);
		$this->db->where('idpuntodescuento',$datos['idpuntodescuento']);
		return $this->db->update('far_punto_descuento', $data);
	}
	public function m_iniciar_nuevo_puntaje($datos){
		$data = array(
			'idcliente' => $datos['idcliente'],
			'puntos_acumulados' => intval($datos['puntaje_obtenido']) - 1000,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'estado_pd' => 1
		);
		return $this->db->insert('far_punto_descuento', $data);
	}	

	public function m_actualizar_estado_producto_convenio($datos){
		$data = array(
			'estado_cps'=> $datos['estado']
		);
		$this->db->where('idconvenioproductosede',$datos['idconvenioproductosede']);
		return $this->db->update('convenio_producto_sede', $data);
	}

	public function m_registrar_producto_convenio($datos){
		$data = array(
			'idproductopreciosede' => $datos['idproductopreciosede'],
			'idtipocliente' => $datos['idtipocliente'],
			'precio_variable' => $datos['precio_convenio'],
		);
		return $this->db->insert('convenio_producto_sede', $data);
	}	

	public function m_actualizar_producto_convenio($datos){
		$data = array(
			'precio_variable' => $datos['precio_convenio'],
		);
		$this->db->where('idconvenioproductosede',$datos['idconvenioproductosede']);
		return $this->db->update('convenio_producto_sede', $data);
	}

	public function m_update_porcentaje_convenio($datos){
		$data = array(
			'porcentaje' => $datos['porcentaje'],
		);
		$this->db->where('idtipocliente',$datos['idtipocliente']);
		return $this->db->update('tipo_cliente', $data);
	}

	public function m_cargar_cliente_no_agre_convenio_autocompletar($datos){ 

		$this->db->select('cli.idcliente, cli.nombres, cli.apellido_paterno, cli.apellido_materno, cli.num_documento, cli.fecha_nacimiento, cli.sexo');
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->from('cliente cli');		
		$this->db->where('estado_cli', 1); // activo
		$this->db->where('(cli.idtipocliente IS NULL OR cli.idtipocliente <> '. $datos['idtipocliente'] . ' )');
		$this->db->ilike("concat_ws(' ',cli.nombres, cli.apellido_paterno, cli.apellido_materno)", strtolower($datos['search']));		
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}

	public function m_update_cliente_convenio($datos){
		$data = array(
			'idtipocliente' => $datos['idtipocliente'],
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente', $data);
	}

	public function m_consulta_producto_convenios($datos){
		$this->db->select('cps.idconvenioproductosede, cps.idproductopreciosede, cps.idtipocliente' );
		$this->db->select('tc.porcentaje, tc.idtipocliente' );
		$this->db->from('convenio_producto_sede cps, producto_precio_sede pps, tipo_cliente tc');
		$this->db->where('pps.idsedeempresaadmin = tc.idsedeempresaadmin');
		$this->db->where('cps.idtipocliente = tc.idtipocliente');		
		$this->db->where('cps.idproductopreciosede = pps.idproductopreciosede'); // es ese producto		
		$this->db->where('cps.idproductopreciosede = '. $datos['idproductopreciosede']); // es ese producto		

		$this->db->where('estado_cps <>', 0); // activo o deshabilitado

		return $this->db->get()->result_array();
	}
}