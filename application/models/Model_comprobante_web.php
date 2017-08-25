<?php
class Model_comprobante_web extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function m_cargar_lista_ventas_web($paramPaginate = FALSE, $datos){
		$this->db->select('uwp.idusuariowebpago, uwp.idusuarioweb, uwp.idculqitracking, uwp.fecha_pago,
					uwp.codigo_referencia_culqi,uwp.descripcion_cargo, uwp.estado_comprobante, uwp.numero_comprobante,
					uwp.fecha_comprobante, uwp.nombre_archivo');
		$this->db->select('cv.idventa,cv.orden_venta,cv.idtipodocumento, cv.sub_total, cv.total_igv, cv.total_a_pagar,
					cv.idmediopago, cv.idsedeempresaadmin,	cv.ticket_venta');
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno,c.apellido_materno,c.num_documento, c.iddepartamento, c.idprovincia, 
					c.iddistrito, c.telefono, c.email, c.idzona, c.nombre_via, c.dir_numero, c.dir_kilometro, c.dir_manzana, 
					c.dir_interior,	c.dir_departamento, c.dir_lote, c.referencia, c.idtipovia, c.direccion, c.celular, 
					c.idtipozona, c.dir_grupo, c.dir_sector');
		//$this->db->select('esp.idespecialidad, esp.nombre AS especialidad');
		$this->db->from('ce_usuario_web_pago uwp');
		$this->db->join('ce_venta cv', 'uwp.idventa = cv.idventa');
		$this->db->join('cliente c', 'cv.idcliente = c.idcliente');
		//$this->db->join('especialidad esp', 'cv.idespecialidad = esp.idespecialidad');
		$this->db->where('cv.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);

		if(!empty($datos['estado']['estado_comprobante'])){
			$this->db->where('uwp.estado_comprobante', $datos['estado']['estado_comprobante']);
		}

		if($paramPaginate){
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
		}else{
			$this->db->order_by('uwp.fecha_pago', 'ASC');
			$this->db->limit(10);
		}		
		return $this->db->get()->result_array();
	}	
	public function m_count_lista_ventas_web($paramPaginate = FALSE, $datos){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ce_usuario_web_pago uwp');
		$this->db->join('ce_venta cv', 'uwp.idventa = cv.idventa');
		$this->db->join('cliente c', 'cv.idcliente = c.idcliente');
		//$this->db->join('especialidad esp', 'cv.idespecialidad = esp.idespecialidad');
		$this->db->where('cv.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);

		if(!empty($datos['estado']['estado_comprobante'])){
			$this->db->where('uwp.estado_comprobante', $datos['estado']['estado_comprobante']);
		}

		if($paramPaginate){
			if( $paramPaginate['search'] ){
				foreach ($paramPaginate['searchColumn'] as $key => $value) {
					if( !empty($value) ){
						$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
					}
				}
			}
		}	
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}

	public function m_actualizar_comprobante_web($datos){
		$data = array( 
			'estado_comprobante' => $datos['estado'],
			'fecha_comprobante' => date('Y-m-d H:i:s'),			
			'nombre_archivo' => $datos['nuevoNombreArchivo'],
			'numero_comprobante' => $datos['nro_comprobante'],
			'idresponsable' => $this->sessionHospital['idempleado'],
		);
		$this->db->where('idusuariowebpago',$datos['idusuariowebpago']);
		return $this->db->update('ce_usuario_web_pago', $data);
	}

	public function m_es_comprobante_duplicado($nro_comprobante){
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('ce_venta v');
		$this->db->join('ce_usuario_web_pago uwp','v.idventa = uwp.idventa');
		$this->db->where('LOWER(uwp.numero_comprobante)', strtolower($nro_comprobante));
		$this->db->where('v.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']);
		
		$fData = $this->db->get()->row_array();
		return ($fData['contador'] > 0) ? TRUE : FALSE;
	}

	public function m_cargar_especialidades_venta($idventa){
		$this->db->select('esp.idespecialidad, esp.nombre AS especialidad');
		$this->db->from('ce_detalle d');
		$this->db->join('especialidad esp', 'd.idespecialidad = esp.idespecialidad');
		$this->db->where('d.idventa', $idventa);
		return $this->db->get()->result_array();
	}
}