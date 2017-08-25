<?php
class Model_venta_web extends CI_Model {
	public function __construct(){
		parent::__construct();
	}


	/*venta web*/
	public function m_actualizar_venta_web_a_atendido($idVenta){
		$data = array(
			'paciente_atendido_v' => 1, // atendido
			'fecha_atencion_v' => date('Y-m-d H:i:s')
		);
		$this->db->where('idventa',$idVenta);
		return $this->db->update('ce_venta', $data);
	}

	public function m_actualizar_detalle_venta_web_a_atendido($idDetalle){
		$data = array(
			'paciente_atendido_det' => 1, // atendido 
			'fecha_atencion_det' => date('Y-m-d H:i:s')
		);
		$this->db->where('iddetalle',$idDetalle);
		return $this->db->update('ce_detalle', $data);
	}
	
	public function m_actualizar_empresa_especialidad_de_venta_web($idDetalle){
		$data = array(
			'idempresaespecialidad' => $this->sessionHospital['idempresaespecialidad'], 
		);
		$this->db->where('iddetalle',$idDetalle);
		return $this->db->update('ce_detalle', $data);
	}	

	public function m_actualizar_comprobante_web_en_venta($datos){
		$data = array(
			'ticket_venta' => $datos['nro_comprobante'], 
		);
		$this->db->where('idventa',$datos['idventa']);
		return $this->db->update('ce_venta', $data);
	}

	public function m_cargar_detalle_venta($idventa){
		$this->db->select('d.iddetalle, d.paciente_atendido_det'); 
		$this->db->from('ce_detalle d');
		$this->db->where('d.idventa', $idventa);
		return $this->db->get()->result_array();
	}
}