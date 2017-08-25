<?php
class Model_trabajador_perfil extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_perfiles_trabajadores($paramPaginate,$paramDatos){ 
		$this->db->select("(cl.nombres || ' ' || cl.apellido_paterno || ' ' || cl.apellido_materno) AS cliente, (pm.descripcion) AS producto, (ec.descripcion) AS empresa", FALSE);
		$this->db->select('pc.idproductocliente, ec.ruc_empresa, pm.idproductomaster, pps.precio_sede AS precio, cl.idcliente, cl.num_documento');
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND pps.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('so_producto_cliente pc','pm.idproductomaster = pc.idproductomaster');
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente');
		$this->db->join('empresa_cliente ec','cl.idempresacliente_cli = ec.idempresacliente');
		$this->db->where('estado_pm', 1);
		$this->db->where('estado_pc', 1);
		$this->db->where('estado_pps', 1);
		$this->db->where('ec.idempresacliente', $paramDatos['empresa']['idempresacliente']);
		// var_dump($paramDatos['perfil']['id']); exit();
		if( $paramDatos['perfil']['id'] != 'all' ){ 
			$this->db->where('pm.idproductomaster', $paramDatos['perfil']['id']);
		}
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
		return $this->db->get()->result_array();
	}
	public function m_count_perfiles_trabajadores($paramPaginate,$paramDatos)
	{
		$this->db->select("COUNT(*) AS contador", FALSE);
		$this->db->from('producto_master pm');
		$this->db->join('producto_precio_sede pps','pm.idproductomaster = pps.idproductomaster AND pps.idsedeempresaadmin = '.$this->sessionHospital['idsedeempresaadmin']);
		$this->db->join('so_producto_cliente pc','pm.idproductomaster = pc.idproductomaster');
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente');
		$this->db->join('empresa_cliente ec','cl.idempresacliente_cli = ec.idempresacliente');
		$this->db->where('estado_pm', 1);
		$this->db->where('estado_pc', 1);
		$this->db->where('estado_pps', 1);
		$this->db->where('ec.idempresacliente', $paramDatos['empresa']['idempresacliente']);
		if( $paramDatos['perfil']['id'] != 'all' ){ 
			$this->db->where('pm.idproductomaster', $paramDatos['perfil']['id']);
		}
		if( $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value);
				}
			}
		}
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	public function m_validar_trabajador_en_perfil($idcliente,$idproductomaster,$idempresacliente)
	{
		$this->db->select("pc.idproductocliente", FALSE);
		$this->db->from('so_producto_cliente pc');
		$this->db->where('estado_pc', 1);
		$this->db->where('idcliente', $idcliente);
		$this->db->where('idproductomaster', $idproductomaster);
		$this->db->where('idempresacliente', $idempresacliente);
		$this->db->limit(1);
		$fila = $this->db->get()->row_array();
		return $fila;
	}
	// public function m_editar($datos)
	// {
	// 	$data = array(
	// 		'titulo' => strtoupper($datos['titulo']),
	// 		'redaccion' => nl2br($datos['redaccion']),
	// 		'updatedAt'=> date('Y-m-d H:i:s') 
	// 	);
	// 	$this->db->where('idaviso',$datos['id']);
	// 	return $this->db->update('intr_aviso', $data);
	// }

	public function m_agregar_cliente_a_perfil($datos)
	{
		$data = array(
			'idcliente'=> $datos['idcliente'],
			'idproductomaster'=> $datos['perfil']['id'],
			'idempresacliente'=> $datos['empresa']['idempresacliente'],
			'createdAt'=> date('Y-m-d H:i:s'),
			'updatedAt'=> date('Y-m-d H:i:s')	
		);
		return $this->db->insert('so_producto_cliente', $data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_pc' => 0
		);
		$this->db->where('idproductocliente',$id);
		if($this->db->update('so_producto_cliente', $data)){
			return true;
		}else{
			return false;
		}
	}
}