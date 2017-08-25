<?php
class Model_atencionMuestra extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_pacientes($paramPaginate){ 
		//$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria, nombres, apellido_paterno, apellido_materno, num_documento, sexo, fecha_nacimiento');
		$this->db->from('cliente cl');
		// $this->db->join("ubigeo dpto","cl.iddepartamento = dpto.iddepartamento  AND dpto.idprovincia = '00' AND dpto.iddistrito = '00'", 'left');
		// $this->db->join("ubigeo prov","cl.idprovincia = prov.idprovincia AND prov.iddepartamento = cl.iddepartamento AND prov.iddistrito = '00'", 'left');
		// $this->db->join('ubigeo dist',"cl.iddistrito = dist.iddistrito AND dist.iddepartamento = cl.iddepartamento AND dist.idprovincia = cl.idprovincia", 'left');
		//$this->db->join('zona z','cl.idzona = z.idzona', 'left');
		$this->db->join('historia h','cl.idcliente = h.idcliente','left');
		// $this->db->join('tipo_zona tz','cl.idtipozona = tz.idtipozona', 'left');
		// $this->db->join('tipo_via tv','cl.idtipovia = tv.idtipovia', 'left');
		$this->db->where('estado_cli', 1); // activo
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
	public function m_count_pacientes($paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('cliente cl');
		$this->db->join('historia h','cl.idcliente = h.idcliente','left');
		$this->db->where('estado_cli', 1); // activo
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

	public function m_obtener_ordenes_paciente_por_historia($datos)
	{
		$ordenes_reg_paciente = $this->m_obtener_ordenes_registradas_paciente($datos['paciente']['idhistoria']);
		// var_dump($ordenes_reg_paciente); exit();
		//$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria, fecha_nacimiento, v.orden_venta ,nombres, apellido_paterno, apellido_materno, num_documento, sexo, v.fecha_venta');
		$this->db->distinct();
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('cliente cl','v.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->where('v.estado', 1); // activo
		$this->db->where('v.idespecialidad', 21); // Laboratorio
		$this->db->where(' h.idhistoria',$datos['paciente']['idhistoria'] );
		$this->db->where('d.paciente_atendido_det', 2);
		$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		if(!empty($ordenes_reg_paciente)){
			$this->db->where_not_in('v.orden_venta', $ordenes_reg_paciente);
		}
		
		$this->db->order_by('v.fecha_venta','DESC');
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	/* no eliminar se usa en el modelo anterior*/
	public function m_obtener_ordenes_registradas_paciente($idhistoria){
		$arrayOrden = array();
		$this->db->select('orden_venta');
		$this->db->from('muestra_paciente mp');
		$this->db->join('cliente cl','mp.idcliente = cl.idcliente');
		$this->db->where('idhistoria', $idhistoria);
		$this->db->where('mp.estado_mp <>', 0);
		$ordenes = $this->db->get()->result_array();
		foreach ($ordenes as $row) {
			array_push($arrayOrden, $row['orden_venta']);
			//$arrayOrden .= $row['orden_venta'] . ', ';
		}
		return $arrayOrden;
	}
	public function m_verificar_si_existe_orden($datos){
		$this->db->from('muestra_paciente');
		$this->db->where('orden_venta',$datos['ordenventa']);
		$this->db->where('estado_mp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_si_existe_orden_laboratorio($orden_lab){
		$this->db->select('idmuestrapaciente, idcliente, fecha_recepcion, observaciones, estado_mp, idtipomuestra, idhistoria, orden_venta,
			prioridad, idmedico, motivorechazo, fecha_rechazo, user_registro, user_rechazo, orden_lab, idsedeempresaadmin, idempresaadminmatriz');	
		$this->db->from('muestra_paciente');
		$this->db->where('orden_lab', $orden_lab);
		$this->db->where('idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
		$this->db->where('estado_mp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_orden_laboratorio_por_venta($orden_venta){
		$this->db->select('idmuestrapaciente, idcliente, fecha_recepcion, observaciones, estado_mp, idtipomuestra, idhistoria, orden_venta,
			prioridad, idmedico, motivorechazo, fecha_rechazo, user_registro, user_rechazo, orden_lab, idsedeempresaadmin, idempresaadminmatriz');	
		$this->db->from('muestra_paciente');
		$this->db->where('orden_venta', $orden_venta);
		$this->db->where('estado_mp <>', 0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_obtener_productos_orden($datos)
	{
		$this->db->select('pm.idproductomaster, pm.descripcion as producto, idanalisis, descripcion_anal, iddetalle, cantidad, anal.idseccion, descripcion_sec as seccion');
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->join('analisis anal','pm.idproductomaster = anal.idproductomaster AND anal.estado_anal = 1','left');
		$this->db->join('seccion s','anal.idseccion = s.idseccion','left');
		$this->db->where('v.estado', 1); // activo
		$this->db->where('d.paciente_atendido_det', 2); // activo
		$this->db->where('pm.idtipoproducto', 15); // Laboratorio
		$this->db->where(' v.orden_venta',$datos );
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_obtener_paciente_por_historia($datos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria, v.orden_venta ,nombres, apellido_paterno, apellido_materno, num_documento, sexo, email, fecha_nacimiento, pm.idproductomaster, pm.descripcion as producto, idanalisis, descripcion_anal');
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('cliente cl','v.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->join('analisis anal','pm.idproductomaster = anal.idproductomaster','left');
		$this->db->where('v.estado', 1); // activo
		$this->db->where('pm.idtipoproducto', 15); // Laboratorio
		$this->db->where(' h.idhistoria',$datos['paciente']['idhistoria']);
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_obtener_paciente_por_orden($datos)
	{
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('cl.idcliente, h.idhistoria , v.orden_venta, nombres, apellido_paterno, apellido_materno, num_documento, sexo, email, fecha_nacimiento, pm.idproductomaster, pm.descripcion as producto');
		$this->db->from('venta v');
		$this->db->join('detalle d','v.idventa = d.idventa');
		$this->db->join('cliente cl','v.idcliente = cl.idcliente');
		$this->db->join('historia h','cl.idcliente = h.idcliente');
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster');
		$this->db->where('v.estado', 1); // activo
		$this->db->where('pm.idtipoproducto', 15); // Laboratorio
		$this->db->where('orden_venta',$datos['paciente']['ordenventa'] );
		//$this->db->limit(1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_orden_lab_paciente($paramPaginate,$paramDatos=FALSE){
		//$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad",FALSE);
		$this->db->select('idmuestrapaciente, cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno, num_documento, cl.fecha_nacimiento, mp.idhistoria, cl.sexo, fecha_recepcion, observaciones, mp.orden_venta, estado_mp, prioridad, orden_lab');
		$this->db->from('muestra_paciente mp');
		$this->db->join('cliente cl','mp.idcliente = cl.idcliente');
		$this->db->where('estado_mp', 1);
		$this->db->where('idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
		 $this->db->where('fecha_recepcion BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
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
	public function m_count_orden_lab_paciente($paramPaginate,$paramDatos=FALSE)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('muestra_paciente mp');
		$this->db->join('cliente cl','mp.idcliente = cl.idcliente');

		$this->db->where('estado_mp', 1);
		$this->db->where('idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
		$this->db->where( 'fecha_recepcion BETWEEN '. $this->db->escape($paramDatos['desde']).' AND ' . $this->db->escape($paramDatos['hasta']) );
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
	public function m_listar_analisis_perfil($idanalisis_perfil){
		$this->db->from('detalle_perfil');
		$this->db->where('idanalisis_perfil', $idanalisis_perfil);
		$this->db->where('estado_dp', 1);
		return $this->db->get()->result_array();
	}
	// ==============================▼
	public function m_cargar_Examenes_por_orden($paramPaginate,$paramDatos){
		$this->db->select('mp.idmuestrapaciente, mp.orden_lab, mp.idcliente, pm.descripcion, s.descripcion_sec, anal.idanalisis, anal.descripcion_anal, anal.estado_anal, ap.estado_ap');
		$this->db->from('muestra_paciente mp');
		$this->db->join('analisis_paciente ap','mp.idmuestrapaciente = ap.idmuestrapaciente');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->join('producto_master pm','ap.idproductomaster = pm.idproductomaster');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->where('mp.orden_lab', $paramDatos['orden_lab']);
		$this->db->where('mp.idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
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
	public function m_count_Examenes_por_orden($paramPaginate,$paramDatos)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('muestra_paciente mp');
		$this->db->join('analisis_paciente ap','mp.idmuestrapaciente = ap.idmuestrapaciente');
		$this->db->join('analisis anal','ap.idanalisis = anal.idanalisis');
		$this->db->join('producto_master pm','ap.idproductomaster = pm.idproductomaster');
		$this->db->join('seccion s','anal.idseccion = s.idseccion');
		$this->db->where('mp.orden_lab', $paramDatos['orden_lab']);
		$this->db->where('mp.idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
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
	// ==============================▲
	public function m_cargar_ultima_orden_laboratorio(){
		$this->db->select('orden_lab');
		$this->db->from('muestra_paciente');
		$this->db->where('idempresaadminmatriz', $this->sessionHospital['id_empresa_admin']);
		$this->db->order_by('idmuestrapaciente','DESC');
		$this->db->limit(1); 
		return $this->db->get()->row_array();
	}
	public function m_registrar_muestra($datos)
	{
		$data = array(
			//'idtipomuestra' => $datos['idtipomuestra'],
			'orden_lab' => $datos['orden_lab'],
			'observaciones' => empty($datos['observaciones'])? null : $datos['observaciones'],
			'orden_venta' => $datos['ordenventa'],
			'idcliente' => $datos['idcliente'],
			'idhistoria' => $datos['idhistoria'],
			'idmedico' => empty($datos['medico']['id'])? null : $datos['medico']['id'],
			'prioridad' => $datos['prioridad'], //0:Normal; 1:Alta; 2:Muy Alta
			'estado_mp' => 1, // 0:anulado; 1:registrada; 2:en proceso; 3:finalizado; 4:rechazada;
			'fecha_recepcion' => date('Y-m-d H:i:s'),
			'user_registro' => $this->sessionHospital['idusers'],
			// 'idsedeempresaadmin' => $this->sessionHospital['idsedeempresaadmin']
			'idempresaadminmatriz' => $this->sessionHospital['id_empresa_admin']
		);
		return $this->db->insert('muestra_paciente', $data);
	}
	public function m_registrar_analisis_paciente($datos_anal, $datos_pac)
	{
		$data = array(
			'idanalisis' => $datos_anal['idanalisis'],
			'idproductomaster' => $datos_anal['idproductomaster'],
			'iddetalle'	=> $datos_anal['iddetalle'],
			'idmuestrapaciente'	=> $datos_pac['id'],
			'idcliente'	=> $datos_pac['idcliente'],
			'idhistoria'	=> $datos_pac['idhistoria'],
			'fecha_examen' => date('Y-m-d H:i:s')
			
		);
		return $this->db->insert('analisis_paciente', $data);
	}
	public function m_registrar_analisis_hijos($datos_perfil, $datos_anal, $datos_pac)
	{
		$data = array(
			'idanalisis' => $datos_anal['idanalisis'],
			'idproductomaster' => $datos_perfil['idproductomaster'],
			'iddetalle'	=> $datos_perfil['iddetalle'],
			'idmuestrapaciente'	=> $datos_pac['id'],
			'idcliente'	=> $datos_pac['idcliente'],
			'idhistoria'	=> $datos_pac['idhistoria'],
			'fecha_examen' => date('Y-m-d H:i:s')
			
		);
		return $this->db->insert('analisis_paciente', $data);
	}
	public function m_actualizar_estado_muestra($datos)
	{
		$data = array(
			'estado_mp' => 2 // en proceso
		);
		$this->db->where('idmuestrapaciente',$datos['id']);
		return $this->db->update('muestra_paciente', $data);
	}
	public function m_rechazar_muestra($datos)
	{
		$data = array(
			'motivorechazo' => $datos['motivorechazo'],
			'fecha_rechazo' => date('Y-m-d H:i:s'),
			'estado_mp' => 4 // en proceso
		);
		$this->db->where('idmuestrapaciente',$datos['id']);
		return $this->db->update('muestra_paciente', $data);
	}
}
