<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function obtenerKardexValorizado($arrParams){ 
	$ci =& get_instance();
	// $arrConfig = array();
	// $lista = $ci->model_config->m_listar_configuraciones();
	// foreach ($lista as $key => $row) {
	// 	$arrConfig[$row['key_cf']] = $row['valor_cf'];
	// }
	// return $arrConfig;
	$arrListado = array();
	if( $row1 = $ci->model_medicamento_almacen->m_cargar_stock_inicial($arrParams) ){ 
		$lista = $ci->model_medicamento_almacen->m_cargar_kardex($arrParams);
		$cantidad_saldo = $row1['stock_inicial'];
		$valor_saldo = $row1['precio_compra'] * $row1['stock_inicial'];
		$promedio = $row1['precio_compra'];
		array_push($arrListado,
			array( 
				'fecha_movimiento' => formatoFechaReporte($row1['fecha_stock_inicial']),
				'fecha' => darFormatoDMY($row1['fecha_stock_inicial']),
				'tipo_movimiento' => 'INVENTARIO INICIAL',
				'entrada' => $row1['stock_inicial'],
				'salida' => NULL,
				'precio_unitario' => $row1['precio_compra'],
				'valor_entrada' => $valor_saldo,
				'valor_salida' => NULL,
				'cantidad_saldo' => $cantidad_saldo,
				'valor_saldo' => $valor_saldo,
				'promedio' => $promedio
			)
		);
		foreach ($lista as $row) { 
			$valor_entrada = 0;
			$valor_salida = 0;
			if($row['dir_movimiento'] == '1'){ // entrada
				if( $row['tipo_movimiento'] == '3' ){ // traslado
					$row3 = $ci->model_medicamento_almacen->m_cargar_ultimo_precio_unitario_a_la_fecha($row);
					$row['precio_unitario'] = $row3['precio_unitario'];
				}
				$entrada = $row['cantidad'];
				$salida = NULL;
				$valor_entrada = $row['precio_unitario'] * $entrada;
				$cantidad_saldo += $row['cantidad'];
				$valor_saldo += $valor_entrada;
				
			}elseif($row['dir_movimiento'] == '2'){ // salida
				if( $row['tipo_nota_credito'] == 1 || $row['tipo_nota_credito'] == 2 ){ // NOTA DE CREDITO O EXTORNO
					$signo = -1;
				}else{
					$signo = 1;
				}
				$row['cantidad'] = $signo * $row['cantidad'];
				$salida = $row['cantidad'];
				$entrada = NULL;
				$row['precio_unitario'] = $promedio;
				$valor_salida = $row['precio_unitario'] * $salida;
				$cantidad_saldo -= $row['cantidad'];
				$valor_saldo -= $valor_salida;
			}
			if( $cantidad_saldo != 0 ){
				$promedio = $valor_saldo / $cantidad_saldo;
			}
			if($row['tipo_movimiento'] == '1'){
				if( $row['tipo_nota_credito'] == 1 || $row['tipo_nota_credito'] == 2 ){
					$tipo_movimiento = 'NOTA DE CREDITO: ' . $row['ticket_venta'];
				}else{
					$tipo_movimiento = 'VENTA, Orden Venta Nº: ' . $row['orden_venta'];
				}
			}elseif($row['tipo_movimiento'] == '2'){
				$tipo_movimiento = 'COMPRA, Factura Nº: ' . $row['ticket_venta'];
			}elseif($row['tipo_movimiento'] == '3'){
				if( $row['dir_movimiento'] == '1'){ // entrada
					$tipo_movimiento = 'INGRESO POR TRASLADO';
				}else{
					$tipo_movimiento = 'SALIDA POR TRASLADO';
				}
			}elseif($row['tipo_movimiento'] == '4'){
				$tipo_movimiento = 'REGALO';
			}elseif($row['tipo_movimiento'] == '5'){
				$tipo_movimiento = 'BAJA';
			}elseif($row['tipo_movimiento'] == '6'){
				$tipo_movimiento = 'REINGRESO';
			}
			array_push($arrListado,
				array(
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fecha' => darFormatoDMY($row['fecha_movimiento']),
					'tipo_movimiento' => $tipo_movimiento,
					'entrada' => $entrada,
					'salida' => $salida,
					'precio_unitario' => $row['precio_unitario'],
					'valor_entrada' => $valor_entrada,
					'valor_salida' => $valor_salida,
					'cantidad_saldo' => $cantidad_saldo,
					'valor_saldo' => $valor_saldo,
					'promedio' => $promedio
				)
			);
		}
	}
	return $arrListado; 
}
function obtenerKardexValorizadoContabilidad($arrParams){ 
	$ci =& get_instance();
	$arrListado = array();
		$lista = $ci->model_medicamento_almacen->m_cargar_inventario_por_medicamento_almacen($arrParams);
		$cantidad_saldo = 0;
		$valor_saldo = 0;
		$promedio = 0;
		foreach ($lista as $row) { 
			$valor_entrada = 0;
			$valor_salida = 0;
			// if($row['excluye_igv'] == 2){
			// 	$row['precio_unitario'] = $row['precio_unitario']/1.18; // liz desea el precio unitario sin igv
			// }
			if($row['dir_movimiento'] == '1'){ // entrada

				$entrada = $row['cantidad'];
				$salida = NULL;
				// $valor_entrada = $row['precio_unitario'] * $entrada;
				$valor_entrada = $row['total_detalle'];
				$cantidad_saldo += $row['cantidad'];
				$valor_saldo += $valor_entrada;
				$row['precio_unitario'] = $valor_entrada/$entrada;
				
			}elseif($row['dir_movimiento'] == '2'){ // salida
				$salida = $row['cantidad'];
				$entrada = NULL;
				$row['precio_unitario'] = $promedio;
				$valor_salida = $row['precio_unitario'] * $salida;
				$cantidad_saldo -= $row['cantidad'];
				$valor_saldo -= $valor_salida;
			}
			if( $cantidad_saldo != 0 ){
				if($row['tipo_movimiento'] == 4){
					// $promedio = $row['precio_unitario'];
					$promedio = $valor_saldo / ($cantidad_saldo-$row['cantidad']);
				}else{
					$promedio = $valor_saldo / $cantidad_saldo;
				}
				
			}
			switch ($row['tipo_movimiento']) {
				case '1':// venta
					$tipo_movimiento = '1';
					break;
				case '2': // compra
					$tipo_movimiento = '2';
					break;
				case '4': // regalo - bonificacion
					$tipo_movimiento = 'regalo - bonificacion';
					break;
				case '5': // baja
					$tipo_movimiento = 'baja';
					break;
				case '6': // Reingreso
					$tipo_movimiento = '5 - Reingreso';
					break;
				
				default:
					# code...
					break;
			}
			
			// TIPO DOCUMENTO
			if( $row['idtipodocumento'] == 1 ){
				$tipo_documento = 12;
			}elseif( $row['idtipodocumento'] == 2 ){
				$tipo_documento = 1;
			}else{
				$tipo_documento = 0;
			}

			$porciones = explode("-", $row['ticket_venta']);
			$serie =  empty($porciones[0])? NULL : $porciones[0];
			$numero = empty($porciones[1])? NULL : $porciones[1];
			array_push($arrListado,
				array(
					'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
					'fecha' => darFormatoDMY($row['fecha_movimiento']),
					'tipo_documento' => $tipo_documento,
					'tipo_movimiento' => $tipo_movimiento,
					// 'tipo_movimiento' => $row['tipo_movimiento'],
					'ticket_venta' => $row['ticket_venta'],
					'serie' => $serie,
					'numero' => $numero,
					'entrada' => $entrada, // cantidad entrada
					'salida' => $salida, // cantidad salida
					'cantidad' => $row['cantidad'],
					'precio_unitario' => $row['precio_unitario'],
					'excluye_igv' => $row['excluye_igv'],
					'valor_entrada' => $valor_entrada,
					'valor_salida' => $valor_salida,
					'cantidad_saldo' => $cantidad_saldo,
					'valor_saldo' => $valor_saldo,
					'promedio' => $promedio
				)
			);
		}
	// }
	return $arrListado; 
}