<?php
class Model_historial_caja_chica extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_caja_chica_historial($paramPaginate,$paramDatos=FALSE){
		$this->db->select('acc.idaperturacajachica, acc.idusuarioresponsable, acc.fecha_apertura,
			acc.monto_inicial,acc.fecha_liquidacion, acc.saldo, acc.idusuariocierre, acc.fecha_cierre, acc.saldo_final, acc.diferencia,
			acc.estado_acc, acc.observaciones_acc, acc.numero_cheque, acc.monto_cheque, acc.saldo_anterior',FALSE);
		$this->db->select('(acc.saldo::NUMERIC) AS saldo_numeric, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric',FALSE);

		$this->db->select('cch.idcajachica,cch.idcentrocosto,cch.nombre as nombre_caja, cch.estado_cch, cch.idsedeempresaadmin',FALSE);
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable",FALSE);
		$this->db->select("CONCAT(emp2.nombres || ' ' || emp2.apellido_paterno || ' ' ||   emp2.apellido_materno) as responsable_cierre",FALSE);
		$this->db->select("cc.nombre_cc, cc.codigo_cc",FALSE);

		$this->db->from('ct_apertura_caja_chica acc');
		$this->db->join('ct_caja_chica cch','acc.idcajachica = cch.idcajachica AND cch.idsedeempresaadmin = ' . $this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('rh_empleado emp','emp.idempleado = acc.idusuarioresponsable','',FALSE);
		$this->db->join('rh_empleado emp2','emp2.idempleado = acc.idusuariocierre', 'left');
		$this->db->join('ct_centro_costo cc','cch.idcentrocosto = cc.idcentrocosto', 'left');
		//$this->db->order_by('acc.estado_acc ASC, acc.fecha_apertura DESC');

		$cajasAperturadas = $this->db->get_compiled_select();

		$this->db->select('null as idaperturacajachica, cch.idusuarioresponsable, null as fecha_apertura, 
			null as monto_inicial, null as fecha_liquidacion, null as saldo, null as idusuariocierre, null as fecha_cierre, null as saldo_final, null as diferencia,
			null as estado_acc, null as observaciones_acc, cch.numero_cheque, cch.monto_cheque, null as saldo_anterior', FALSE);
		$this->db->select('null as saldo_numeric,null AS monto_inicial_numeric', FALSE);
		$this->db->select('cch.idcajachica,cch.idcentrocosto,cch.nombre as nombre_caja, cch.estado_cch, cch.idsedeempresaadmin',FALSE);
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable",FALSE);
		$this->db->select("null as responsable_cierre",FALSE);
		$this->db->select("cc.nombre_cc, cc.codigo_cc",FALSE);

		$this->db->from('ct_caja_chica cch');
		$this->db->join('rh_empleado emp','emp.idempleado = cch.idusuarioresponsable','left',FALSE);
		$this->db->join('ct_centro_costo cc','cch.idcentrocosto = cc.idcentrocosto', 'left',FALSE);
		$this->db->where('cch.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		//$this->db->where('(select count(*) from ct_apertura_caja_chica b where (b.idcajachica = cch.idcajachica) = 0)');
		$this->db->where('( select count(*) from ct_apertura_caja_chica c where c.idcajachica = cch.idcajachica and c.estado_acc in (1,2) ) < 1');

		$cajasSinApertura = $this->db->get_compiled_select();

		$campos = 'x.idaperturacajachica, x.idusuarioresponsable, x.fecha_apertura,
			x.monto_inicial,x.fecha_liquidacion, x.saldo, x.idusuariocierre, x.fecha_cierre, x.saldo_final, x.diferencia,
			x.estado_acc, x.observaciones_acc, x.numero_cheque, x.monto_cheque, x.saldo_anterior, 
			x.saldo_numeric,x.monto_inicial_numeric, x.idcajachica,x.idcentrocosto,x.nombre_caja, x.estado_cch, x.idsedeempresaadmin,
			x.responsable, x.responsable_cierre, x.nombre_cc, x.codigo_cc';

		//$this->db->select($campos, FALSE);
		$query = 'select '. $campos .' FROM ( ' . $cajasAperturadas.' UNION ALL '.$cajasSinApertura . ' ) x ';
		$where = '';
		if( $paramPaginate['search'] ){ 			
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$where .= " CAST(".$key." AS TEXT ) ILIKE '%" .$value. "%' AND";
				}
			}
		}
		if(strlen( $where ) > 0){
			$where = substr($where, 0, -3);
			$where = ' WHERE '. $where;
		}

		$result = $this->db->query($query.$where);

		return $result->result_array();
	}
	public function m_count_caja_chica_historial($paramPaginate,$paramDatos=FALSE){
		/* CITAS */
		//$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->select('acc.idaperturacajachica, acc.idusuarioresponsable, acc.fecha_apertura,
			acc.monto_inicial,acc.fecha_liquidacion, acc.saldo, acc.idusuariocierre, acc.fecha_cierre, acc.saldo_final, acc.diferencia,
			acc.estado_acc, acc.observaciones_acc, acc.numero_cheque, acc.monto_cheque, acc.saldo_anterior',FALSE);
		$this->db->select('(acc.saldo::NUMERIC) AS saldo_numeric, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric',FALSE);

		$this->db->select('cch.idcajachica,cch.idcentrocosto,cch.nombre as nombre_caja, cch.estado_cch, cch.idsedeempresaadmin',FALSE);
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable",FALSE);
		$this->db->select("CONCAT(emp2.nombres || ' ' || emp2.apellido_paterno || ' ' ||   emp2.apellido_materno) as responsable_cierre",FALSE);
		$this->db->select("cc.nombre_cc, cc.codigo_cc",FALSE);

		$this->db->from('ct_apertura_caja_chica acc');
		$this->db->join('ct_caja_chica cch','acc.idcajachica = cch.idcajachica AND cch.idsedeempresaadmin = ' . $this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('rh_empleado emp','emp.idempleado = acc.idusuarioresponsable');
		$this->db->join('rh_empleado emp2','emp2.idempleado = acc.idusuariocierre', 'left');
		$this->db->join('ct_centro_costo cc','cch.idcentrocosto = cc.idcentrocosto', 'left');
		//$this->db->order_by('acc.estado_acc ASC, acc.fecha_apertura DESC');

		$cajasAperturadas = $this->db->get_compiled_select();

		$this->db->select('null as idaperturacajachica, cch.idusuarioresponsable, null as fecha_apertura, 
			null as monto_inicial, null as fecha_liquidacion, null as saldo, null as idusuariocierre, null as fecha_cierre, null as saldo_final, null as diferencia,
			null as estado_acc, null as observaciones_acc, cch.numero_cheque, cch.monto_cheque, null as saldo_anterior', FALSE);
		$this->db->select('null as saldo_numeric,null AS monto_inicial_numeric', FALSE);
		$this->db->select('cch.idcajachica,cch.idcentrocosto,cch.nombre as nombre_caja, cch.estado_cch, cch.idsedeempresaadmin',FALSE);
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable",FALSE);
		$this->db->select("null as responsable_cierre",FALSE);
		$this->db->select("cc.nombre_cc, cc.codigo_cc",FALSE);

		$this->db->from('ct_caja_chica cch');
		$this->db->join('rh_empleado emp','emp.idempleado = cch.idusuarioresponsable','left');
		$this->db->join('ct_centro_costo cc','cch.idcentrocosto = cc.idcentrocosto', 'left');
		$this->db->where('cch.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		//$this->db->where('(select count(*) from ct_apertura_caja_chica b where (b.idcajachica = cch.idcajachica) = 0)');
		$this->db->where('( select count(*) from ct_apertura_caja_chica c where c.idcajachica = cch.idcajachica and c.estado_acc in (1,2) ) < 1');

		$cajasSinApertura = $this->db->get_compiled_select();

		//$this->db->select($campos, FALSE);
		$query = 'select COUNT(*) as contador FROM ( ' . $cajasAperturadas.' UNION ALL '.$cajasSinApertura . ' ) x ';
		$where = '';
		if( $paramPaginate['search'] ){ 			
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$where .= " CAST(".$key." AS TEXT ) ILIKE '%" .$value. "%' AND";
				}
			}
		}
		if(strlen( $where ) > 0){
			$where = substr($where, 0, -3);
			$where = ' WHERE '. $where;
		}

		$result = $this->db->query($query.$where);

		return $result->result_array();
	}
	public function m_cargar_comentarios_estados_movimiento($datos)
	{
		$this->db->select('mo.idmovimiento,
			co.idcomentario, co.comentario, co.color_estado, co.fecha_registro, 
			us.idusers');
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable");
		$this->db->from('ct_comentario co');
		$this->db->join('ct_movimiento mo','co.idmovimiento = mo.idmovimiento');
		$this->db->join('users us','co.idusuario = us.idusers');
		$this->db->join('rh_empleado emp','us.idusers = emp.iduser');
		$this->db->where('mo.idmovimiento',$datos['idmovimiento']);
		$this->db->order_by('co.fecha_registro','ASC');
		// $this->db->query();
		return $this->db->get()->result_array();
	}
	public function m_agregar_comentario_estado($datos)
	{
		$data = array(
			'comentario' => empty($datos['comentario']) ? NULL : $datos['comentario'],
			'color_estado' => empty($datos['estado_color_obj']['flag']) ? NULL : $datos['estado_color_obj']['flag'],
			'idmovimiento' => $datos['idmovimiento'],
			'fecha_registro' => date('Y-m-d H:i:s'),
			'idusuario' => $this->sessionHospital['idusers']
		);
		return $this->db->insert('ct_comentario', $data);
	}
	public function m_actualizar_semaforo_mov($datos)
	{
		$data = array(
			'estado_color'=> $datos['estado_color_obj']['flag']
		);
		$this->db->where('idmovimiento',$datos['idmovimiento']); 
		return $this->db->update('ct_movimiento', $data);
	}
	public function m_cargar_esta_apertura_caja_chica_historial($datos)
	{
		$this->db->select('acc.idaperturacajachica, acc.idusuarioresponsable, acc.idcentrocosto, acc.idcajachica, acc.fecha_apertura,
			acc.monto_inicial,acc.fecha_liquidacion, acc.saldo, acc.idusuariocierre, acc.fecha_cierre, acc.saldo_final, acc.diferencia,
			acc.estado_acc, acc.observaciones_acc, acc.numero_cheque, acc.monto_cheque, acc.saldo_anterior');
		$this->db->select('(acc.saldo::NUMERIC) AS saldo_numeric, (acc.monto_inicial::NUMERIC) AS monto_inicial_numeric');
		$this->db->select('cch.idcajachica,cch.nombre as nombre_caja, cch.estado_cch, cch.idsedeempresaadmin');
		$this->db->select("CONCAT(emp.nombres || ' ' || emp.apellido_paterno || ' ' ||   emp.apellido_materno) as responsable");
		$this->db->select("CONCAT(emp2.nombres || ' ' || emp2.apellido_paterno || ' ' ||   emp2.apellido_materno) as responsable_cierre");
		$this->db->select("cc.nombre_cc, cc.codigo_cc");

		$this->db->from('ct_apertura_caja_chica acc');
		$this->db->join('ct_caja_chica cch','acc.idcajachica = cch.idcajachica' );
		$this->db->join('rh_empleado emp','emp.idempleado = acc.idusuarioresponsable');
		$this->db->join('rh_empleado emp2','emp2.idempleado = acc.idusuariocierre', 'left');
		$this->db->join('ct_centro_costo cc','cch.idcentrocosto = cc.idcentrocosto', 'left');
		$this->db->where('acc.idaperturacajachica',$datos['idaperturacajachica']);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
}
?>