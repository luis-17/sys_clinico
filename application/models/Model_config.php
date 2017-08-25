<?php
class Model_config extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_empresa_activa(){ 
		$this->db->select('idempresaadmin, razon_social, nombre_legal, domicilio_fiscal, 
			direccion, ruc, nombre_logo, estado_emp, rs_facebook, rs_twitter, rs_youtube, correo_gerencia_finanzas, correo_administrador',FALSE);
		$this->db->from('empresa_admin');
		$this->db->where('estado_emp', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
		public function m_cargar_empresa_usuario_activa(){ 
		$this->db->select('idempresaadmin, razon_social, nombre_legal, domicilio_fiscal, 
			direccion, ruc, nombre_logo, estado_emp, rs_facebook, rs_twitter, rs_youtube, idusers, username, email',FALSE);
		$this->db->from('empresa_admin, users');
		$this->db->where('estado_emp', 1); // activo 
		$this->db->where('idusers', $this->sessionHospital['idusers']); 
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_empresa_sede_activa(){ 
		$this->db->select('idsedeempresaadmin, s.idsede, ea.correo_gerencia_finanzas, ea.correo_administrador, s.direccion_se, s.referencia_se, s.descripcion',FALSE);
		$this->db->from('empresa_admin ea');
		$this->db->join('sede_empresa_admin sea','ea.idempresaadmin = sea.idempresaadmin');
		$this->db->join('sede s','sea.idsede = s.idsede');
		$this->db->where('estado_sea', 1); // activo
		$this->db->where('sea.idsede', $this->sessionHospital['idsede']); // activo 
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_listar_configuraciones(){
		$this->db->select();
		$this->db->from('configuracion');
		$this->db->where('estado_cf', 1); // activo
		return $this->db->get()->result_array();
	}
	public function m_cargar_tipo_cambio()
	{
		$this->db->select('tc.idtipocambio, tc.compra, tc.venta, tc.fecha_cambio',FALSE);
		$this->db->from('ct_tipo_cambio tc');
		$this->db->where('vigente', 1); // activo
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_variables_ley()
	{
		$this->db->select('(uit::numeric) AS uit, (rmv::numeric) AS rmv, essalud, onp', FALSE);
		$this->db->select('asignacion_familiar, (remun_max_asegurable::numeric) AS remun_max_asegurable', FALSE);
		$this->db->from('rh_config_variable');
		$this->db->where('vigente', 1); // activo
		return $this->db->get()->row_array();
	}

}