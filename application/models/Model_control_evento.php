<?php
class Model_control_evento extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_registrar_evento($data){ 
		return $this->db->insert('control_evento', $data );
	}

	public function m_registrar_notificacion_evento($data){ 
		return $this->db->insert('control_evento_usuario', $data);
	}

	public function m_listar_control_evento($datos, $paramPaginate){
		$this->db->select('ce.idcontrolevento, ce.fecha_evento, ce.idresponsable, ce.comentario, ce.idtipoevento, ce.identificador, 
				ce.texto_notificacion, ce.estado_ce, ce.texto_log, te.descripcion_te, te.si_notificacion, te.estado_te, te.key_evento,
				emp.nombres, emp.apellido_paterno, emp.apellido_materno'); 
		$this->db->from('control_evento ce'); 
		$this->db->join('tipo_evento te','te.idtipoevento = ce.idtipoevento AND te.estado_te = 1'); 		
		$this->db->join('rh_empleado emp','emp.idempleado = ce.idresponsable');
		$this->db->where('te.key_evento', $datos['key_evento']); 
		$this->db->where('ce.estado_ce <> 0'); 
		$this->db->where('ce.idcontrolevento IN (select a.idcontrolevento from control_evento_usuario a group by a.idcontrolevento)'); 

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
		$this->db->order_by('ce.idcontrolevento', 'DESC');

		return $this->db->get()->result_array();
	}

	public function m_contar_control_evento($datos, $paramPaginate){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('control_evento ce'); 
		$this->db->join('tipo_evento te','te.idtipoevento = ce.idtipoevento AND te.estado_te = 1'); 		
		$this->db->join('rh_empleado emp','emp.idempleado = ce.idresponsable');
		$this->db->where('te.key_evento', $datos['key_evento']); 
		$this->db->where('ce.estado_ce <> 0'); 
		$this->db->where('ce.idcontrolevento IN (select a.idcontrolevento from control_evento_usuario a group by a.idcontrolevento)');

		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}

		$fData = $this->db->get()->row_array();
		return $fData['result']; 
	}

	public function m_anular($idcontrolevento){
		$data = array( 
			'estado_ce'=> 0 
		);
		$this->db->where('idcontrolevento',$idcontrolevento); 
		return $this->db->update('control_evento', $data ); 
	}

	public function m_anular_todo_detalle($idcontrolevento){
		$data = array( 
			'estado_ceu'=> 0 
		);
		$this->db->where('idcontrolevento',$idcontrolevento); 
		return $this->db->update('control_evento_usuario', $data ); 
	}

	public function m_cambiar_estado($idcontrolevento, $estado){
		$data = array( 
			'estado_ce'=> $estado 
		);
		$this->db->where('idcontrolevento',$idcontrolevento); 
		return $this->db->update('control_evento', $data ); 
	}

	public function m_cargar_grupos_notificacion_desde_usuarios($idcontrolevento){
		$this->db->distinct();
		$this->db->select('ug.idgroup, g.key_group');
		$this->db->from('users_groups ug');
		$this->db->join('users u', 'u.idusers = ug.idusers');
		$this->db->join('group g', 'ug.idgroup = g.idgroup AND estado_g = 1');
		$this->db->where('u.estado_usuario <>', '0');
		$this->db->where('u.idusers IN (select a.idusers from control_evento_usuario a where a.idcontrolevento = '.$idcontrolevento.')');
		return $this->db->get()->result_array();
	}

	public function m_cargar_notificaciones_usuario($idusuario){
		$this->db->select('ceu.idcontroleventousuario, ceu.fecha_notificado, ceu.fecha_leido, ceu.idusers, ceu.estado_ceu');
		$this->db->select('ce.idcontrolevento, ce.fecha_evento, ce.idresponsable, ce.comentario, ce.idtipoevento, 
			ce.identificador, ce.texto_notificacion, ce.texto_log'); 
		$this->db->select('te.descripcion_te, te.key_evento');
		$this->db->select('em.nombres, em.apellido_paterno, em.apellido_materno');
		$this->db->from('control_evento_usuario ceu');
		$this->db->join('control_evento ce', 'ceu.idcontrolevento = ce.idcontrolevento AND estado_ce = 1');
		$this->db->join('tipo_evento te', 'te.idtipoevento = ce.idtipoevento AND estado_te = 1');
		$this->db->join('rh_empleado em', 'em.idempleado = ce.idresponsable');
		$this->db->where('ceu.idusers', intval($idusuario));
		$this->db->where('ceu.estado_ceu <> 0');
		$this->db->order_by('ce.fecha_evento DESC');
		return $this->db->get()->result_array();
	}

	public function m_count_notificaciones_sin_leer_usuario($idusuario){
		$this->db->select('COUNT(*) AS result');
		$this->db->from('control_evento_usuario ceu');
		$this->db->join('control_evento ce', 'ceu.idcontrolevento = ce.idcontrolevento AND estado_ce = 1');
		$this->db->join('tipo_evento te', 'te.idtipoevento = ce.idtipoevento AND estado_te = 1');
		$this->db->join('rh_empleado em', 'em.idempleado = ce.idresponsable');
		$this->db->where('ceu.idusers', intval($idusuario));
		$this->db->where('ceu.estado_ceu = 1');
		$fData = $this->db->get()->row_array();
		return $fData['result'];
	}

	public function m_update_leido_notificacion($idcontroleventousuario){
		$data = array( 
			'estado_ceu'=> 2,
			'fecha_leido'=> date('Y-m-d H:i:s') 
		);
		$this->db->where('idcontroleventousuario',$idcontroleventousuario); 
		return $this->db->update('control_evento_usuario', $data ); 
	}
}