<?php
class Model_grupo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//ACCESO AL SISTEMA
	public function m_cargar_grupos($paramPaginate){ 
		$this->db->select('"g"."idgroup"');
		$this->db->select("name, description, estado_g, permite_notificacion_pa",FALSE);
		$this->db->from('group g');
		$this->db->join('groups_roles gr','g.idgroup = gr.idgroup AND gr.estado_gr = 1','left');
		$this->db->join('rol r','gr.idrol = r.idrol AND r.estado_rol = 1','left');
		$this->db->where('g.estado_g', 1);
		if( $this->sessionHospital['key_group'] == 'key_rrhh' || $this->sessionHospital['key_group'] == 'key_rrhh_asistente' ){ 
			$this->db->where('g.idgroup', 4); // SALUD 
		}
		if( !empty($paramPaginate['search']) ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$this->db->group_by('g.idgroup');
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['pageSize'] ){ 
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_cargar_grupos_cbo($datos = FALSE)
	{
		$this->db->select("idgroup, name, description, estado_g, vista_sede_empresa, key_group", FALSE);
		$this->db->from('group');
		$this->db->where('estado_g', 1);

		if($datos){
			$this->db->where($datos['campo'] . ' = '. $datos['value']);
		}

		if( $this->sessionHospital['key_group'] == 'key_rrhh' || $this->sessionHospital['key_group'] == 'key_rrhh_asistente' || $this->sessionHospital['key_group'] == 'key_gerencia' ){ 
			$this->db->where_in('group.vista_sede_empresa', array(1) ); // SALUD 
		}
		return $this->db->get()->result_array();
	}
	public function m_count_grupos()
	{
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('group');
		$this->db->where('estado_g', 1); // activo
		$fila = $this->db->get()->row_array();
		return $fila['contador'];
	}
	public function m_cargar_modulos_cbo()
	{
		$this->db->select("idmodulo, descripcion_mod, estado_mod", FALSE);
		$this->db->from('modulo');
		$this->db->where('estado_mod', 1);
		$this->db->order_by('idmodulo', 'asc');
		return $this->db->get()->result_array();
	}
	public function m_cargar_este_grupo($idGrupo)
	{
		$this->db->select("gr.idgroup, gr.description, gr.key_group, gr.vista_sede_empresa", FALSE);
		$this->db->from('group gr');
		$this->db->where('gr.idgroup',$idGrupo);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_roles_no_agregados_al_grupo($paramPaginate,$datos) 
	{
		$sql = 'SELECT rol.idrol, idparent, orden, descripcion_rol, url_rol, icono_rol 
		FROM "rol"
		WHERE rol.idrol NOT IN( 
			SELECT rol.idrol FROM groups_roles 
			LEFT JOIN rol ON rol.idrol = groups_roles.idrol AND groups_roles.estado_gr = 1 
			LEFT JOIN "group" ON groups_roles.idgroup="group".idgroup AND "group".estado_g = 1 
			WHERE rol.estado_rol=1 AND groups_roles.idgroup = '.$datos['idgroup'].'
		)
		AND estado_rol = 1 AND idmodulo='.$datos['idmodulo'];
		if( $paramPaginate['sortName'] ){
			$sql.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}

		$query = $this->db->query($sql); // var_dump($query); exit();
		return $query->result_array();
	}
	public function m_count_roles_no_agregados_al_grupo($datos)
	{
		$sql = 'SELECT COUNT(*) AS contador 
		FROM "rol"
		WHERE rol.idrol NOT IN( 
			SELECT rol.idrol FROM groups_roles 
			LEFT JOIN rol ON rol.idrol = groups_roles.idrol AND groups_roles.estado_gr = 1 
			LEFT JOIN "group" ON groups_roles.idgroup="group".idgroup AND "group".estado_g = 1 
			WHERE rol.estado_rol=1 AND groups_roles.idgroup = '.$datos['idgroup'].'
		)
		AND estado_rol = 1 AND idmodulo='.$datos['idmodulo'];
		$query = $this->db->query($sql,array($datos['id'],$datos['id']));
		$fRol = $query->row_array();
		return $fRol['contador'];
	}

	public function m_cargar_roles_agregados_al_grupo($paramPaginate,$datos) 
	{
		//$sql = 'SELECT rol.idrol, idparent, orden, descripcion_rol, url_rol, icono_rol 
		//FROM "rol"
		//LEFT JOIN groups_roles ON "rol".idrol = groups_roles.idrol AND estado_gr = 1 
		//LEFT JOIN "group" ON groups_roles.idgroup = "group".idgroup AND estado_g = 1 AND "group".idgroup = ? 
		//WHERE rol.idrol IN( 
		//	SELECT r.idrol FROM rol r JOIN groups_roles gr ON r.idrol = gr.idrol
		//	WHERE gr.idgroup = ? AND estado_rol = 1 AND estado_gr = 1
		//)
		//AND estado_rol = 1';
		$sql='SELECT rol.idrol,rol.idparent, rol.orden, rol.descripcion_rol, rol.url_rol, rol.icono_rol ,groups_roles.idgrouproles
			FROM groups_roles 
			LEFT JOIN rol ON rol.idrol = groups_roles.idrol AND groups_roles.estado_gr = 1 
			LEFT JOIN "group" ON groups_roles.idgroup="group".idgroup AND "group".estado_g = 1 
			WHERE rol.estado_rol=1 AND groups_roles.idgroup = '.$datos['idgroup'];
		if($datos['idmodulo'] > 0){
			$sql .= ' AND idmodulo='.$datos['idmodulo'];
		}
		//$sql .= ' GROUP BY rol.idrol';
		if( $paramPaginate['sortName'] ){
			$sql.= ' ORDER BY '.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if($paramPaginate['pageSize'] ){
			' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}

		$query = $this->db->query($sql,array($datos['id'],$datos['id']));
		return $query->result_array();
	}
	public function m_count_roles_agregados_al_grupo($datos)
	{
		$sql = 'SELECT COUNT(*) AS contador 
		FROM "rol"
		LEFT JOIN groups_roles ON "rol".idrol = groups_roles.idrol AND estado_gr = 1 
		LEFT JOIN "group" ON groups_roles.idgroup = "group".idgroup AND estado_g = 1 AND "group".idgroup = ? 
		WHERE rol.idrol IN( 
			SELECT r.idrol FROM rol r JOIN groups_roles gr ON r.idrol = gr.idrol
			WHERE gr.idgroup = ? AND estado_rol = 1 AND estado_gr = 1
		)
		AND estado_rol = 1';
		if($datos['idmodulo'] > 0){
			$sql .= ' AND idmodulo='.$datos['idmodulo'];
		}
		$sql .= ' GROUP BY rol.idrol';

		$query = $this->db->query($sql,array($datos['id'],$datos['id']));
		$fRol = $query->row_array();
		return $fRol['contador'];
	}
	public function m_agregar_rol_grupo($datos)
	{
		$data = array(
			'idgroup' => $datos['groupId'],
			'idrol' => $datos['id']
		);
		return $this->db->insert('groups_roles', $data);
	}
	public function m_quitar_rol_grupo($id)
	{
		$data = array(
			'estado_gr' => 0,
		);
		$this->db->where('idgrouproles',$id);
		if($this->db->update('groups_roles', $data)){
			return true;
		}else{
			return false;
		}
	}
	public function m_registrar($datos)
	{
		$data = array(
			'name' => $datos['nombre'],
			'description' => $datos['descripcion'],
			'createdAt' => date('Y-m-d'),
			'updatedAt' => date('Y-m-d')
		);
		return $this->db->insert('group', $data);
	}
	public function m_editar($datos)
	{
		$data = array(
			'name' => $datos['nombre'],
			'description' => $datos['descripcion'],
			'updatedAt' => date('Y-m-d')
		);
		$this->db->where('idgroup',$datos['id']);
		return $this->db->update('group', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_g' => 0,
		);
		$this->db->where('idgroup',$id);
		if($this->db->update('group', $data)){
			return true;
		}else{
			return false;
		}
	}

	public function m_update_permite_notificacion_pa($datos){
		$data = array(
			'permite_notificacion_pa' => $datos['value'],
		);
		$this->db->where('idgroup',$datos['id']);
		if($this->db->update('group', $data)){
			return true;
		}else{
			return false;
		}
	}
}