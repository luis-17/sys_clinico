<?php
class Model_desbloqueoTickets extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_pacientes_bloqueados($paramPaginate,$paramDatos){
		$this->db->select('v.idventa, orden_venta, ticket_venta, v.fecha_venta,	v.estado, h.idhistoria,
			iddetalle, d.tiene_autorizacion, d.paciente_atendido_det, d.cantidad, d.total_detalle,
			fecha_atencion_det, d.si_tipo_campania, e.dias_libres, cl.idcliente,
			pm.descripcion as producto, tp.nombre_tp');
		$this->db->select("concat_ws(' ', cl.nombres, cl.apellido_paterno, cl.apellido_materno) AS paciente");
		$this->db->select("DATE_PART('YEAR',AGE(fecha_nacimiento)) AS edad", FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('cliente cl', 'v.idcliente = cl.idcliente');
		$this->db->join('historia h', 'cl.idcliente = h.idcliente');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('especialidad e', 'v.idespecialidad = e.idespecialidad');
		if(!empty($paramDatos['orden_venta']))
			$this->db->ilike('v.orden_venta',$paramDatos['orden_venta'],'before');
		$this->db->where('v.idespecialidad', $paramDatos['idespecialidad']);
		$this->db->where('v.estado', 1);
		// $this->db->where('d.paciente_atendido_det', '2');
		
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		if( $paramPaginate['sortName'] ){
			$this->db->order_by('d.paciente_atendido_det','DESC');
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}else{
			$this->db->order_by('d.paciente_atendido_det','DESC');
			$this->db->order_by('v.fecha_venta','DESC');
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();

	}
	public function m_count_pacientes_bloqueados($paramPaginate,$paramDatos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('venta v');
		$this->db->join('detalle d', 'v.idventa = d.idventa');
		$this->db->join('cliente cl', 'v.idcliente = cl.idcliente');
		$this->db->join('historia h', 'cl.idcliente = h.idcliente');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		if(!empty($paramDatos['orden_venta']))
			$this->db->ilike('v.orden_venta',$paramDatos['orden_venta'],'before');
		$this->db->where('v.idespecialidad', $paramDatos['idespecialidad']);
		$this->db->where('v.estado', 1);
		// $this->db->where('d.paciente_atendido_det', '2');
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
	public function m_desbloquear_venta_paciente($datos){
		if($datos['tiene_autorizacion'] == 1){
			$data = array(
				'tiene_autorizacion' => 2,
			);
		}else{
			$data = array(
				'tiene_autorizacion' => 1,
				'idempleado_desbloqueo' => $this->sessionHospital['idempleado'],
				'fecha_desbloqueo' => date('Y-m-d H:i:s'),
			);
		}
		$this->db->where('idventa',$datos['idventa']);
		$this->db->where('paciente_atendido_det',2);
		return $this->db->update('detalle', $data);
	}
}
