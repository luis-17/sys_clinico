<?php
class Model_atencion_salud_ocup extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_ventas_salud_ocup($paramDatos,$paramPaginate)
	{

		$this->db->select('d.iddetalle, v.idventa, v.estado, orden_venta, 
			sub_total, total_igv, total_a_pagar, fecha_venta, ticket_venta, td.idtipodocumento, descripcion_td, 
			d.cantidad, d.precio_unitario, d.descuento_asignado, d.total_detalle, mp.idmediopago, 
			pm.idproductomaster, pm.descripcion AS producto, (ec.descripcion) AS empresa, 
			esp.idespecialidad, esp.nombre AS especialidad, informe_texto_so, nombre_archivo_so
		'); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		//$this->db->join('cliente cl','v.idcliente = cl.idcliente'); 
		$this->db->where('ec.idempresacliente', $paramDatos['empresa']['idempresacliente']); 
		$this->db->where('esp.idespecialidad', 39); // SALUD OCUPACIONAL  
		$this->db->where('v.estado', 1); 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) { 
						//$this->db->ilike($key, $palabra);
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
				}
			}
		}
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		}
		return $this->db->get()->result_array(); 
	}
	public function m_count_ventas_salud_ocup($paramDatos,$paramPaginate)
	{
		$this->db->select('COUNT(*) AS contador',FALSE); 
		$this->db->from('venta v'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago'); 
		$this->db->join('empresa_cliente ec','v.idempresacliente = ec.idempresacliente'); 
		$this->db->join('detalle d','v.idventa = d.idventa'); 
		$this->db->join('producto_master pm','d.idproductomaster = pm.idproductomaster'); 
		$this->db->join('especialidad esp','pm.idespecialidad = esp.idespecialidad'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento AND td.estado_td = 1'); 
		$this->db->where('v.idempresacliente', $paramDatos['empresa']['idempresacliente']); 
		$this->db->where('esp.idespecialidad', 39); // SALUD OCUPACIONAL  
		$this->db->where('v.estado', 1); 
		$this->db->where('fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto']));
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
						// $this->db->ilike($key, $palabra);
					}
				}
			}
		}
		$this->db->limit(1);
		$fila = $this->db->get()->row_array(); 
		return $fila;
	}
	public function m_cargar_atenciones_perfiles_salud_ocupacional($paramDatos,$paramPaginate)
	{
		//var_dump($paramDatos['iddetalle']); exit();
		$this->db->select("(cl.nombres || ' ' || cl.apellido_paterno || ' ' || cl.apellido_materno) AS cliente",FALSE); 
		$this->db->select('ao.idatencionocupacional, fecha_atencion, informe, nombre_archivo, ao.iddetalle,
			pm.idproductomaster, pm.descripcion AS producto, (ec.descripcion) AS empresa
		'); 
		$this->db->from('so_atencion_ocupacional ao'); 
		$this->db->join('so_producto_cliente pc','ao.idproductocliente = pc.idproductocliente'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster'); 
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente'); 
		$this->db->join('empresa_cliente ec','pc.idempresacliente = ec.idempresacliente');
		$this->db->where('ao.iddetalle', (int)$paramDatos['iddetalle']); 
		$this->db->where('estado_pc', 1); 
		$this->db->where('estado_ao', 1); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
				}
			}
		}
		if( $paramPaginate['sortName'] ){ 
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){ 
			$this->db->limit( $paramPaginate['pageSize'],$paramPaginate['firstRow'] ); 
		}
		return $this->db->get()->result_array(); 
	}
	public function m_count_atenciones_perfiles_salud_ocupacional($paramDatos,$paramPaginate)
	{
		$this->db->select("COUNT(*) AS contador",FALSE);
		$this->db->from('so_atencion_ocupacional ao'); 
		$this->db->join('so_producto_cliente pc','ao.idproductocliente = pc.idproductocliente'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster'); 
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente'); 
		$this->db->join('empresa_cliente ec','pc.idempresacliente = ec.idempresacliente');
		$this->db->where('ao.iddetalle', $paramDatos['iddetalle']); 
		$this->db->where('estado_pc', 1); 
		$this->db->where('estado_ao', 1); 

		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if( !empty($value) ){
					$palabras = explode(' ', $value);
					foreach ($palabras as $key1 => $palabra) {
						$this->db->ilike('CAST('.$key.' AS TEXT )', $palabra);
					}
				}
			}
		}
		$this->db->limit(1); 
		$fila = $this->db->get()->row_array(); 
		return $fila;
	}
	public function m_cargar_estas_atencion_salud_ocupacional($datos)
	{ 

		$this->db->select("CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno) AS medico_responsable", FALSE);
		$this->db->select("CONCAT_WS(' ',cl.nombres,cl.apellido_paterno,cl.apellido_materno) AS cliente, num_documento, sexo, hi.idhistoria, cl.fecha_nacimiento",FALSE); 
		$this->db->select('ao.idatencionocupacional, fecha_atencion, informe, nombre_archivo, ao.iddetalle, v.orden_venta, 
			pm.idproductomaster, pm.descripcion AS producto, (ec.descripcion) AS empresa, (esp.nombre) AS especialidad
		'); 
		$this->db->from('so_atencion_ocupacional ao'); 
		$this->db->join('medico m','ao.idmedico = m.idmedico'); 
		$this->db->join('venta v','ao.idventa = v.idventa'); 
		$this->db->join('especialidad esp','v.idespecialidad = esp.idespecialidad'); 
		$this->db->join('so_producto_cliente pc','ao.idproductocliente = pc.idproductocliente'); 
		$this->db->join('producto_master pm','pc.idproductomaster = pm.idproductomaster'); 
		$this->db->join('cliente cl','pc.idcliente = cl.idcliente'); 
		$this->db->join('historia hi','cl.idcliente = hi.idcliente'); 
		$this->db->join('empresa_cliente ec','pc.idempresacliente = ec.idempresacliente');
		$this->db->where_in('ao.idatencionocupacional', $datos['arrIds']); 
		$this->db->where('estado_pc', 1); 
		$this->db->where('estado_ao', 1); 
		return $this->db->get()->result_array(); 
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idventa' => $datos['idventa'],
			'idproductocliente' => $datos['idproductocliente'],
			'fecha_atencion' => date('Y-m-d H:i:s'),
			'informe' => @$datos['informe_texto'],
			'nombre_archivo' => @$datos['nombre_archivo'],
			'iddetalle' => $datos['iddetalle'],
			'idmedico' => $datos['idmedico']
		);
		return $this->db->insert('so_atencion_ocupacional',$data);
	}
	public function m_actualizar_informe_general($datos)
	{
		$data = array(
			'nombre_archivo_so' => @$datos['nombre_archivo'],
			'informe_texto_so' => @$datos['informe_texto']
		);
		$this->db->where('idventa',$datos['idventa']);
		return $this->db->update('venta',$data);
	}
	public function m_anular($id)
	{
		$data = array(
			'estado_ao' => 0
		);
		$this->db->where('idatencionocupacional',$id);
		if($this->db->update('so_atencion_ocupacional', $data)){
			return true;
		}else{
			return false;
		}
	}
}
?>