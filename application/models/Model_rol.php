<?php
class Model_rol extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_roles($paramPaginate){
		$this->db->select('idrol, orden, descripcion_rol, url_rol, icono_rol, estado_rol,idparent');
		$this->db->from('rol');
		$this->db->where('estado_rol', 1); // activo
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		$this->db->order_by('orden', 'ASC');
		return $this->db->get()->result_array();
	}
	public function m_count_roles()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rol');
		$this->db->where('estado_rol', 1); // activo
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_roles_session($idmodulo=FALSE){
		// subconsulta
		$this->db->select('jq_r.idrol, jq_r.descripcion_rol, jq_r.url_rol, jq_r.idparent, jq_r.orden');
		$this->db->from('rol jq_r');
		$this->db->join('groups_roles jq_gr','jq_r.idrol = jq_gr.idrol AND jq_gr.estado_gr = 1 AND jq_gr.idgroup = ' . $this->sessionHospital['idgroup'] );
		$this->db->where('jq_r.estado_rol = 1 AND jq_r.idparent IS NOT NULL');
		$subrol = $this->db->get_compiled_select();
		$this->db->reset_query();
		// CONSULTA PRINCIPAL
		$this->db->select('m.idmodulo, m.descripcion_mod AS modulo, m.abreviatura, m.es_unidad_negocio,
			r.idrol AS idrol, r.orden, r.descripcion_rol AS rol, r.icono_rol AS icono, r.url_rol AS url,
			sub_r.idrol AS subidrol, sub_r.orden AS suborden, sub_r.idparent, sub_r.descripcion_rol AS subrol,
			sub_r.url_rol AS suburl, gr.idgrouproles');
		$this->db->from('rol r');
		$this->db->join('modulo m','r.idmodulo = m.idmodulo');
		$this->db->join('groups_roles gr','r.idrol = gr.idrol');
		$this->db->join('group g','gr.idgroup = g.idgroup');
		$this->db->join('('.$subrol.') AS sub_r','r.idrol = sub_r.idparent','left');
		$this->db->where('r.estado_rol', 1);
		$this->db->where('r.pos_fuera_modulo', 0);
		$this->db->where('gr.estado_gr', 1);
		// $this->db->where('m.es_unidad_negocio', 1);
		$this->db->where('r.idparent IS NULL');
		$this->db->where('gr.idgroup', $this->sessionHospital['idgroup']);
		if($idmodulo){
			$this->db->where('m.idmodulo', $idmodulo);
		}
		$this->db->order_by('m.idmodulo','ASC');
		$this->db->order_by('r.orden','ASC');
		$this->db->order_by('sub_r.orden','ASC');
		return $this->db->get()->result_array();
		/*
		$sql = 'SELECT
			"m"."idmodulo",
			"m"."descripcion_mod" AS modulo,
			"m"."abreviatura",
			"r"."idrol" AS idrol,
			"r"."orden",
			"r"."descripcion_rol" AS rol,
			"r"."icono_rol" AS icono,
			"r".url_rol AS url,
			"sub_r"."idrol" AS subidrol,
			"sub_r"."orden" AS suborden,
			"sub_r"."idparent",
			("sub_r"."descripcion_rol") AS subrol,
			"sub_r"."url_rol" AS suburl,
			"gr"."idgrouproles"
		FROM
			"rol" "r"
		JOIN "modulo" "m" ON "r"."idmodulo" = "m"."idmodulo"
		JOIN "groups_roles" "gr" ON "r"."idrol" = "gr"."idrol"
		JOIN "group" "g" ON "gr"."idgroup" = "g"."idgroup"
		LEFT JOIN (
			SELECT "jq_r"."idrol", "jq_r"."descripcion_rol", "jq_r"."url_rol", "jq_r"."idparent", "jq_r"."orden"
			FROM rol jq_r
			JOIN "groups_roles" "jq_gr" ON "jq_r"."idrol" = "jq_gr"."idrol" AND "jq_gr"."idgroup" = ?
			WHERE "jq_r"."estado_rol" = 1 AND jq_r."idparent" IS NOT NULL
		) AS sub_r ON "r"."idrol" = "sub_r"."idparent"
		WHERE
			"r"."estado_rol" = 1
		AND "gr"."estado_gr" = 1
		AND r."idparent" IS NULL
		AND "gr"."idgroup" = ?';
		if($idmodulo){
			$sql .= ' AND "m"."idmodulo" = ?';
		}
		$sql .= ' ORDER BY
			"m"."idmodulo" ASC,
			"r"."orden" ASC,
			"sub_r"."orden" ASC';
		if($idmodulo)
			$query = $this->db->query($sql,array($idgroup,$idgroup,$idmodulo));
		else
			$query = $this->db->query($sql,array($idgroup,$idgroup));
		return $query->result_array();*/

	}
	// public function m_cargar_roles_de_modulos_session($idgroup,$idmodulo=FALSE){
	// 	// subconsulta
	// 	$this->db->select('jq_r.idrol, jq_r.descripcion_rol, jq_r.url_rol, jq_r.idparent, jq_r.orden');
	// 	$this->db->from('rol jq_r');
	// 	$this->db->join('groups_roles jq_gr','jq_r.idrol = jq_gr.idrol AND jq_gr.idgroup = ' . $idgroup);
	// 	$this->db->where('jq_r.estado_rol = 1 AND jq_r.idparent IS NOT NULL');
	// 	$subrol = $this->db->get_compiled_select();
	// 	$this->db->reset_query();
	// 	// CONSULTA PRINCIPAL
	// 	$this->db->select('m.idmodulo, m.descripcion_mod AS modulo, m.abreviatura, m.es_unidad_negocio,
	// 		r.idrol AS idrol, r.orden, r.descripcion_rol AS rol, r.icono_rol AS icono, r.url_rol AS url,
	// 		sub_r.idrol AS subidrol, sub_r.orden AS suborden, sub_r.idparent, sub_r.descripcion_rol AS subrol,
	// 		sub_r.url_rol AS suburl, gr.idgrouproles');
	// 	$this->db->from('rol r');
	// 	$this->db->join('modulo m','r.idmodulo = m.idmodulo');
	// 	$this->db->join('groups_roles gr','r.idrol = gr.idrol');
	// 	$this->db->join('group g','gr.idgroup = g.idgroup');
	// 	$this->db->join('('.$subrol.') AS sub_r','r.idrol = sub_r.idparent','left');
	// 	$this->db->where('r.estado_rol', 1);
	// 	$this->db->where('r.pos_fuera_modulo', 0);
	// 	$this->db->where('gr.estado_gr', 1);
	// 	$this->db->where('m.es_unidad_negocio', 2);
	// 	$this->db->where('r.idparent IS NULL');
	// 	$this->db->where('gr.idgroup', $idgroup);
	// 	if($idmodulo){
	// 		$this->db->where('m.idmodulo', $idmodulo);
	// 	}
	// 	$this->db->order_by('m.idmodulo','ASC');
	// 	$this->db->order_by('r.orden','ASC');
	// 	$this->db->order_by('sub_r.orden','ASC');
	// 	return $this->db->get()->result_array();

	// }
	public function m_cargar_roles_menu_externo_session($posicion){
		
		// CONSULTA PRINCIPAL
		$this->db->select('r.idrol AS idrol, r.orden, r.descripcion_rol AS rol, r.icono_rol AS icono, r.url_rol AS url, r.pos_fuera_modulo,	gr.idgrouproles');
		$this->db->from('rol r');
		$this->db->join('groups_roles gr','r.idrol = gr.idrol');
		$this->db->join('group g','gr.idgroup = g.idgroup');
		$this->db->where('r.estado_rol', 1);
		$this->db->where_in('r.pos_fuera_modulo', array(1,2));
		$this->db->where('gr.estado_gr', 1);
		$this->db->where('r.idparent IS NULL');
		$this->db->where('gr.idgroup', $this->sessionHospital['idgroup']);


		$this->db->order_by('r.orden','ASC');

		return $this->db->get()->result_array();

	}
	public function m_cargar_roles_favoritos_usuario($idusers){
		$this->db->select('rf.idrolfavorito, r.idrol, r.descripcion_rol, url_rol, icono_rol AS icono');
		$this->db->from('rol_favorito rf');
		$this->db->join('rol r','rf.idrol = r.idrol');
		$this->db->join('groups_roles gr','rf.idrol = gr.idrol');
		$this->db->join('users_groups ug','gr.idgroup = ug.idgroup AND ug.idusers = '.$idusers);
		$this->db->where('gr.estado_gr',1);
		$this->db->where('rf.idusers',$idusers);
		$this->db->order_by('idrolfavorito');
		return $this->db->get()->result_array();
	}
	public function m_count_favoritos_usuario($idusers)
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rol_favorito rf');
		$this->db->join('rol r','rf.idrol = r.idrol');
		$this->db->join('groups_roles gr','rf.idrol = gr.idrol');
		$this->db->join('users_groups ug','gr.idgroup = ug.idgroup AND ug.idusers = '.$idusers);
		$this->db->where('gr.estado_gr',1);
		$this->db->where('rf.idusers',$idusers);
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_rol_por_url($datos){
		$this->db->select('idrol');
		$this->db->from('rol');
		$this->db->where('url_rol',$datos['url']);
		$this->db->where('estado_rol',1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_verificar_si_existe_rol_favorito($datos){
		$this->db->select('idrol');
		$this->db->from('rol_favorito');
		$this->db->where('idrol',$datos['idrol']);
		$this->db->where('idusers',$datos['iduser']);
		if ( $this->db->get()->num_rows() > 0 )
			return TRUE;
		else
			return FALSE;
	}
	public function m_registrar_rol_favorito($datos)
	{
		$data = array(
			'idrol' => $datos['idrol'],
			'idusers' => $datos['iduser'],
			'creado' => date('Y-m-d H:i:s'),
		);
		return $this->db->insert('rol_favorito', $data);
	}
	public function m_eliminar_favorito($datos)
	{

		$this->db->where('idrolfavorito',$datos['idrolfavorito']);
		return $this->db->delete('rol_favorito');
	}
	public function m_editar($datos)
	{
		$data = array(
			'descripcion_rol' => $datos['descripcion'],
			'url_rol' => $datos['url'],
			'icono_rol' => $datos['icono']
		);
		$this->db->where('idrol',$datos['id']);
		return $this->db->update('rol', $data);
	}
	public function m_registrar($datos)
	{
		$data = array(
			'descripcion_rol' => $datos['descripcion'],
			'url_rol' => $datos['url'],
			'icono_rol' => $datos['icono']
		);
		return $this->db->insert('rol', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_rol' => 0,
		);
		$this->db->where('idrol',$id);
		if($this->db->update('rol', $data)){
			return true;
		}else{
			return false;
		}
	}
}