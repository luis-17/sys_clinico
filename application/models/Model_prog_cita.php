<?php
class Model_prog_cita extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_registrar($data){
		return $this->db->insert('pa_prog_cita', $data);
	}

	public function m_anular_cita($id){
		$data = array(
			'estado_cita' => 0 
		);
		$this->db->where('idprogcita',$id);
		return $this->db->update('pa_prog_cita', $data);
	}

	public function m_cambiar_estado_cita($datos){
		$data = array(
			'estado_cita' => $datos['estado_cita'],
			'motivo_cancelacion' => empty($datos['descripcion_motivo']) ? null : $datos['descripcion_motivo'],
			'fecha_cancelacion' => empty($datos['descripcion_motivo']) ? null : date('Y-m-d H:i:s'),
		);
		$this->db->where('idprogcita',$datos['idprogcita']);
		return $this->db->update('pa_prog_cita', $data);
	}

	public function m_conculta_cita_cupo($iddetalleprogmedico){
		$this->db->select('*'); 
		$this->db->from("pa_prog_cita ppc");		
		$this->db->where("ppc.iddetalleprogmedico", intval($iddetalleprogmedico)); //cupo
		return $this->db->get()->row_array();
	}

	public function m_cita_tiene_atencion($datos){
		$this->db->select('COUNT(*) AS result'); 
		$this->db->from("pa_prog_cita ppc, detalle d, atencion_medica am");		
		$this->db->where("d.idprogcita = ppc.idprogcita");	
		$this->db->where("am.iddetalle = d.iddetalle");
		$this->db->where("ppc.idprogcita", intval($datos['idprogcita'])); //cita
		$this->db->where("d.paciente_atendido_det = 1"); //detalle
		$this->db->where("am.estado_am = 1"); //atencion

		$fData = $this->db->get()->row_array();
		return empty($fData['result']) ? FALSE : TRUE; 	
	}

	public function m_cambiar_estado_todas_cita_prog($datos){
		$data = array( 
			'estado_cita'=> $datos['estado_cita'], 
		);

		$query = ("select pc.idprogcita 
					from pa_prog_cita pc, pa_detalle_prog_medico dpm, pa_prog_medico prm 
					where pc.iddetalleprogmedico = dpm.iddetalleprogmedico 
					and dpm.idprogmedico =  prm.idprogmedico 
					and prm.idprogmedico = ". intval($datos['idprogmedico']));

		$this->db->where("idprogcita IN (" . $query . " )");
		if($datos['estado_cita'] == 3){
			$this->db->where("estado_cita = 2 ");
		}

		return $this->db->update('pa_prog_cita', $data ); 
	}

	public function m_cambiar_datos_en_cita($datos){
		$data = array( 
			'iddetalleprogmedico'=> $datos['iddetalleprogmedico'], 
			'fecha_atencion_cita'=> $datos['fecha_atencion_cita'], 
		);
		$this->db->where("idprogcita", $datos['idprogcita']);
		return $this->db->update('pa_prog_cita', $data ); 
	}

/*	public function m_actualizar_cita_a_atendida($iddetalle){		
		$query = ("select de.idprogcita 
					from detalle de 
					where de.iddetalle = " .$iddetalle );

		$data = array('estado_cita' =>  5,);
		$this->db->where("idprogcita IN (" . $query . " )");
		return $this->db->update('pa_prog_cita', $data); 
	}*/
	public function m_actualizar_cita_a_atendida_new($idprogcita){		
		$data = array('estado_cita' =>  5,);
		$this->db->where("idprogcita",$idprogcita);
		return $this->db->update('pa_prog_cita', $data); 
	}

	public function m_actualizar_cita_a_no_atendida($iddetalle){		
		$query = ("select de.idprogcita 
					from detalle de 
					where de.iddetalle = " .$iddetalle );

		$data = array('estado_cita' =>  2,);
		$this->db->where("idprogcita IN (" . $query . " )");
		return $this->db->update('pa_prog_cita', $data); 
	}

}