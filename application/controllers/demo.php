<?php 
  public function report_detalle_por_producto_fechas_marketing_excel() // KARINA 
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    // $z = new XMLReader;
    // var_dump($z); exit();
    ini_set('max_execution_time', 600); 
    ini_set('memory_limit','2G'); 
    $writer = WriterFactory::create(Type::XLSX); 
    $fileName = $allInputs['titulo'].'.xls'; 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xls'; 
    $writer->openToFile($filePath); 
    
    // $writer->openToBrowser($fileName); // stream data directly to the browser 
    $singleRow = array('FECHA','COD. CAJA','CAJERA','COD. PACIENTE', 'N° HISTORIA','TIPO DOCUMENTO','N° DOCUMENTO','PACIENTE','EDAD','SEXO', 'DIRECCION', 'DISTRITO','PROVINCIA', 'DEPARTAMENTO', 
      'PROCEDENCIA','TELEFONO', 'FECHA NAC.', 'E-MAIL', 'MEDICO SOLICITUD', 'COMPROBANTE', 'N° COMPROBANTE', 'FECHA MOV.', 'FECHA ATENCION.', 'FORMA DE PAGO', 'COD. EMPRESA', 'EMPRESA', 'COD. ESPECIALIDAD', 
      'ESPECIALIDAD', 'COD. TIPO PROD.', 'TIPO PROD.', 'COD. PROD.', 'DESCRIPCION DE PRODUCTO', 'CANTIDAD', 'PRECIO NETO', 'IGV', 'PRECIO TOTAL', 'ESTADO', 'REGULAR/CAMPAÑA', 'NOMBRE DE CAMPAÑA'); 
    $writer->addRow($singleRow); 
    
    $arrData['flag'] = 0;
    $arrContent = array();
    $cont = 0;
    //foreach ($listaCajas as $key => $value) { 
    $listaVentasDeCaja = $this->model_venta->m_cargar_ventas_y_atenciones_desde_hasta($allInputs); 
    $arrListadoProd = array();
    $arrListadoNCR = array();
    $sumTotal = 0;
    $sumTotalVenta = 0;
    $countAnulados = 0;
    $countVentas = 0;
    $sumTotalNCR = 0;
    $countNCR = 0;

    $arrSoloVentas = array();
    foreach ($listaVentasDeCaja as $key => $valueDet) { 
      if( $valueDet['tipofila'] == 'v' ){ 
        $countVentas++;
      }
      if( $valueDet['tipofila'] == 'a' ){ 
        $countAnulados++;
      }
    }
    //var_dump($arrSoloMediosPago); exit(); 
    foreach ($listaVentasDeCaja as $row) { 
      $strFechaVenta = $row['fecha_venta'];
      $strFechaAtencion = $row['fecha_atencion_det'];
      $strFechaNac = $row['fecha_nacimiento'];
      $strEstado = '';
      if( $row['paciente_atendido_det'] == 2 ){
        $strEstado = 'NO ATENDIDO';
      }
      if( $row['paciente_atendido_det'] == 1 ){
        $strEstado = 'ATENDIDO';
      }
      if( $row['tipofila'] == 'a' ){ 
        $strEstado = 'ANULADO';
      }
      if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' || $row['tipofila'] == 'nc' ){ // VENTAS  // NOTA CREDITO 
        if( $row['tipofila'] == 'v' ){ 
          $igvDivi = round($row['total_detalle'] - ( $row['total_detalle'] / 1.18 ),2);
          $subTotal = $row['total_detalle'] - $igvDivi;
        }elseif( $row['tipofila'] == 'a' ){
          $igvDivi = 0;
          $subTotal = 0;
          $row['total_detalle'] = 0;
        }elseif( $row['tipofila'] == 'nc' ){
          $igvDivi = 0;
          $subTotal = 0;
          //$row['total_detalle'] = 0;
        }
        $rowEdad = devolverEdad($row['fecha_nacimiento']);

        $writer->addRow( 
          array( 
            date('m/Y',strtotime($strFechaVenta)),
            $row['idcaja'],
            $row['username'],
            $row['idcliente'],
            $row['idhistoria'],
            'DNI/RUC',
            $row['dniruc'],
            strtoupper($row['paciente']),
            $rowEdad,
            $row['sexo'],
            $row['direccion'],
            strtoupper($row['distrito']),
            strtoupper($row['provincia']),
            strtoupper($row['departamento']),
            $row['procedencia'],
            $row['telefono'].' '.$row['celular'],
            date('d/m/Y',strtotime($strFechaNac)),
            $row['email'],
            $row['medico'],
            $row['descripcion_td'],
            $row['ticket_venta'],
            date('d/m/Y',strtotime($strFechaVenta)),
            date('d/m/Y',strtotime($strFechaAtencion)),
            $row['descripcion_med'],
            $row['idempresa'],
            $row['empresa'],
            $row['idespecialidad'],
            $row['especialidad'],
            $row['idtipoproducto'],
            $row['nombre_tp'],
            $row['idproductomaster'],
            $row['producto'],
            $row['cantidad'],
            $subTotal,
            $igvDivi,
            $row['total_detalle'],
            $strEstado,
            $row['tipo_campania'],
            $row['campania']
          ) 
        ); 
        $sumTotal += $row['total_detalle'];
      }
    } 
    $singleRow = array(NULL,NULL,NULL,NULL, NULL,NULL,NULL,NULL,NULL,NULL, NULL, NULL,NULL, NULL, 
      NULL,NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 
      NULL, NULL, NULL, NULL, NULL, NULL, 'TOTAL: ', $sumTotal, NULL, NULL, NULL); 
    // $sumTotal 
    $writer->addRow( $singleRow );
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xls',
      'flag'=> 1
    ); 
    $writer->close();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }