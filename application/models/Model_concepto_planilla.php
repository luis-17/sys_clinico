<?php
class Model_concepto_planilla extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_conceptos($paramPaginate){ 
		$this->db->select('c.idconcepto, c.descripcion_co, c.idcategoriaconcepto, c.si_snp, c.si_spp, c.si_5cat, c.si_essalud, c.si_sctr,
			c.si_senati, c.es_calculable, c.abreviatura, c.formula, c.codigo_plan, c.estado_co, c.codigo_plame');
		$this->db->select('cc.idcategoriaconcepto, cc.descripcion as categoria_concepto, cc.tipo_concepto,c.nivel_reporte');
		$this->db->from('rh_concepto c');
		$this->db->join('rh_categoria_concepto cc', 'cc.idcategoriaconcepto = c.idcategoriaconcepto');
		$this->db->where('c.estado_co <>', 0); // activo
		if( !empty($paramPaginate['search']) ){
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

	public function m_count_conceptos($paramPaginate){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_concepto c');
		$this->db->join('rh_categoria_concepto cc', 'cc.idcategoriaconcepto = c.idcategoriaconcepto');
		$this->db->where('c.estado_co <>', 0);
		if( !empty($paramPaginate['search']) ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_cargar_conceptos_agregados($paramPaginate, $idplanillamaster, $cat_concepto){ 
		$this->db->select('pc.idplanillaconcepto, pc.idplanillamaster, c.idconcepto, pc.estado_pc, c.descripcion_co, c.codigo_plame, pc.valor_referencial');
		$this->db->from('rh_planilla_concepto pc');
		$this->db->join('rh_concepto c', 'pc.idconcepto = c.idconcepto AND estado_co = 1');
		$this->db->join('rh_categoria_concepto cc', ' c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('pc.estado_pc <>', 0);
		$this->db->where('pc.idplanillamaster', $idplanillamaster);
		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);
		}
		if( !empty($paramPaginate['search']) ){
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

	public function m_count_conceptos_agregados($paramPaginate, $idplanillamaster,$cat_concepto){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_planilla_concepto pc');
		$this->db->join('rh_concepto c', 'pc.idconcepto = c.idconcepto AND estado_co = 1');
		$this->db->join('rh_categoria_concepto cc', ' c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('pc.estado_pc <>', 0); // activo
		$this->db->where('pc.idplanillamaster', $idplanillamaster);

		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);
		}
		
		if( !empty($paramPaginate['search']) ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_cargar_conceptos_no_agregados($paramPaginate, $idplanillamaster,$cat_concepto){

		// SUBCONSULTA 
		$this->db->select('c.idconcepto');
		$this->db->from('rh_planilla_concepto pc');
		$this->db->join('rh_concepto c', 'pc.idconcepto = c.idconcepto');
		$this->db->join('rh_categoria_concepto cc', 'c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('c.estado_co',1);
		$this->db->where('pc.estado_pc <> 0');
		$this->db->where('pc.idplanillamaster',$idplanillamaster);
		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);
		}
		$c_idconcepto = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('c.idconcepto, c.codigo_plame, c.descripcion_co');
		$this->db->from('rh_concepto c');
		$this->db->join('rh_categoria_concepto cc', ' c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('c.estado_co', 1);
		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);	
		}
		$this->db->where('c.idconcepto NOT IN (' . $c_idconcepto . ')');

		if( !empty($paramPaginate['search']) ){
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

	public function m_count_conceptos_no_agregados($paramPaginate, $idplanillamaster, $cat_concepto){
		
		// SUBCONSULTA 
		$this->db->select('c.idconcepto');
		$this->db->from('rh_planilla_concepto pc');
		$this->db->join('rh_concepto c', 'pc.idconcepto = c.idconcepto');
		$this->db->join('rh_categoria_concepto cc', 'c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('c.estado_co',1);
		$this->db->where('pc.estado_pc',1);
		$this->db->where('pc.idplanillamaster',$idplanillamaster);
		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);
		}
		$c_idconcepto = $this->db->get_compiled_select();
		$this->db->reset_query();

		// CONSULTA PRINCIPAL
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('rh_concepto c');
		$this->db->join('rh_categoria_concepto cc', ' c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('c.estado_co', 1);
		if($cat_concepto != 0){
			$this->db->where('c.idcategoriaconcepto', $cat_concepto);	
		}
		$this->db->where('c.idconcepto NOT IN (' . $c_idconcepto . ')');

		if( !empty($paramPaginate['search']) ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
				$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_cargar_conceptos_planilla_master($idplanillamaster){
		$this->db->select('c.idconcepto, c.descripcion_co as descripcion, c.es_calculable, c.orden as indice, c.codigo_plame, c.codigo_plan');
		$this->db->select('pc.idplanillaconcepto, pc.valor_referencial, pc.idplanillamaster, pc.estado_pc, pc.valor_referencial');
		$this->db->select('cc.idcategoriaconcepto, cc.descripcion as categoria , cc.tipo_concepto ');
		$this->db->from('rh_planilla_concepto pc');
		$this->db->join('rh_concepto c', 'pc.idconcepto = c.idconcepto');
		$this->db->join('rh_categoria_concepto cc', 'c.idcategoriaconcepto = cc.idcategoriaconcepto');
		$this->db->where('c.estado_co <>',0);
		$this->db->where('pc.estado_pc <>',0);
		$this->db->where('pc.idplanillamaster',$idplanillamaster);
		$this->db->order_by('cc.tipo_concepto ASC,c.orden ASC');	
		
		return $this->db->get()->result_array();	
	}

	public function m_editar($datos){
		$data = array(
			'descripcion_co' => strtoupper($datos['descripcion']),
			'idcategoriaconcepto' => $datos['categoria_concepto']['id'],
			'nivel_reporte' => $datos['nivel_reporte']['id'],
			'si_snp' => ($datos['si_snp']) ? 1 : 0,
			'si_spp' => ($datos['si_spp']) ? 1 : 0,
			'si_5cat' => ($datos['si_5cat']) ? 1 : 0,
			'si_essalud' => ($datos['si_essalud']) ? 1 : 0,
			'si_sctr' => ($datos['si_sctr']) ? 1 : 0,
			'si_senati' => ($datos['si_senati']) ? 1 : 0,
			'es_calculable' => ($datos['es_calculable']) ? 1 : 0,
			'abreviatura' =>  empty($datos['abreviatura']) ? NULL: strtoupper($datos['abreviatura']),
			'codigo_plan' => empty($datos['codigo_plan']) ? NULL: strtoupper($datos['codigo_plan']),
			'formula' => empty($datos['formula']) ? NULL: $datos['formula'], 
			'codigo_plame' => empty($datos['codigo_plame']) ? NULL: $datos['codigo_plame'], 
			'iduser_modifica' => $this->sessionHospital['idusers'],
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		$this->db->where('idconcepto',$datos['id']);
		return $this->db->update('rh_concepto', $data);
	}

	public function m_registrar($datos){
		$data = array(
			'descripcion_co' => strtoupper($datos['descripcion']),
			'idcategoriaconcepto' => $datos['categoria_concepto']['id'],
			'nivel_reporte' => $datos['nivel_reporte']['id'],
			'si_snp' => ($datos['si_snp']) ? 1 : 0,
			'si_spp' => ($datos['si_spp']) ? 1 : 0,
			'si_5cat' => ($datos['si_5cat']) ? 1 : 0,
			'si_essalud' => ($datos['si_essalud']) ? 1 : 0,
			'si_sctr' => ($datos['si_sctr']) ? 1 : 0,
			'si_senati' => ($datos['si_senati']) ? 1 : 0,
			'es_calculable' => ($datos['es_calculable']) ? 1 : 0,
			'abreviatura' =>  empty($datos['abreviatura']) ? NULL: strtoupper($datos['abreviatura']),
			'codigo_plan' => empty($datos['codigo_plan']) ? NULL: strtoupper($datos['codigo_plan']),
			'formula' => empty($datos['formula']) ? NULL: $datos['formula'], 
			'codigo_plame' => empty($datos['codigo_plame']) ? NULL: $datos['codigo_plame'], 
			'iduser_creacion' => $this->sessionHospital['idusers'],
			'iduser_modifica' => $this->sessionHospital['idusers'],

			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s') 
		);
		return $this->db->insert('rh_concepto', $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_co' => 0
		);
		$this->db->where('idconcepto',$id);
		return $this->db->update('rh_concepto', $data);
	}

	public function m_agregar_concepto($datos){
		$data = array(
		'idplanillamaster' => $datos['idplanillamaster'],
		'idconcepto' => $datos['idconcepto'],
		'estado_pc' => 1 
		);
		return $this->db->insert('rh_planilla_concepto', $data);
	}

	public function m_quitar_activar_concepto($id, $estado){
		$data = array(
		'estado_pc' => $estado
		);
		$this->db->where('idplanillaconcepto',$id);
		return $this->db->update('rh_planilla_concepto', $data);
	}

	public function m_consultar_concepto($datos){
		$this->db->select('idplanillaconcepto');
		$this->db->from('rh_planilla_concepto pc');
		$this->db->where('pc.idplanillamaster', $datos['idplanillamaster']);
		$this->db->where('pc.idconcepto', $datos['idconcepto']);

	  return $this->db->get()->row_array();
	}

	public function m_actualizar_valor_referencial($datos){
		$data = array(
			'valor_referencial' => $datos['valor_referencial'] 
		);
		$this->db->where('idplanillaconcepto',$datos['id']);
		return $this->db->update('rh_planilla_concepto', $data);
	}
}