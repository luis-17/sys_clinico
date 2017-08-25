<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/spout242/src/Spout/Autoloader/autoload.php';
//lets Use the Spout Namespaces
use Box\Spout\Reader\ReaderFactory; 
use Box\Spout\Writer\WriterFactory; 
use Box\Spout\Common\Type; 
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

class CentralReportesFarmacia extends CI_Controller { 

  public function __construct()
  {
    parent::__construct();
    $this->load->helper(array('security','reportes_helper','imagen_helper','fechas_helper','otros_helper','bd_helper'));
    $this->load->model(array('model_config','model_atencion_medica','model_caja','model_venta_farmacia','model_medicamento_almacen','model_empresa_admin','model_orden_compra','model_entrada_farmacia','model_traslado_farmacia','model_historial_venta_farm','model_caja_temporal_farmacia','model_medicamento','model_caja_farmacia','model_empleado'));
    $this->load->library(array('excel'));
    //cache 
    $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
    $this->output->set_header("Pragma: no-cache");

    $this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
    date_default_timezone_set("America/Lima");
    //if(!@$this->user) redirect ('inicio/login');
    //$permisos = cargar_permisos_del_usuario($this->user->idusuario);
  } 

  public function report_farm_detalle_por_venta_caja_fechas_excel() { 
    ini_set('max_execution_time', 360);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $arrContent = array();
    $cont = 0;
    //foreach ($listaCajas as $key => $value) { 
      $listaVentasDeCaja = $this->model_venta_farmacia->m_cargar_ventas_farm_desde_hasta($allInputs);
      $listaNCCaja = $this->model_venta_farmacia->m_cargar_nc_farmacia($allInputs);
      $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
      $arrListadoProd = array();
      $arrListadoNCR = array();
      $sumTotalVenta = 0;
      $countAnulados = 0;
      $countVentas = 0;
      $sumTotalNCR = 0;
      $countNCR = 0;

      $arrSoloVentas = array();
      $arrSoloMediosPago = array();
      $valueDetGen = array();
      foreach ($listaVentasDeCaja as $key => $valueDetAux) { 
        if( $valueDetAux['tipofila'] == 'v' ){ 
          $valueDetGen['idmediopago'] = $valueDetAux['idmediopago'];
          $valueDetGen['descripcion_med'] = $valueDetAux['descripcion_med'];
          $valueDetGen['cantidad_gen'] = 0;
          $valueDetGen['monto_gen'] = 0;
          $arrSoloMediosPago[$valueDetAux['idmediopago']] = $valueDetGen;
        }
      }
      foreach ($listaVentasDeCaja as $key => $valueDet) { 
        // $arrSoloVentas[$valueDet['idventa']] = $valueDet; 
        if( $valueDet['tipofila'] == 'v' ){ 
          $countVentas++;
          if( $arrSoloMediosPago[$valueDet['idmediopago']]['idmediopago'] == $valueDet['idmediopago'] ){ 
            $arrSoloMediosPago[$valueDet['idmediopago']]['monto_gen'] += $valueDet['total_a_pagar'];
            $arrSoloMediosPago[$valueDet['idmediopago']]['cantidad_gen']++;
          } 
        }
        if( $valueDet['tipofila'] == 'a' ){ 
          $countAnulados++;
        }
      }
      foreach ($listaVentasDeCaja as $row) { 
        $strFechaVenta = $row['fecha_movimiento'];
        if(empty($row['idcliente'])){ 
          $cliente = '';
        }else{ 
          $cliente = strtoupper($row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombres']);
        }
        
        if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' ){ // VENTAS 

          /* LOGICA DE IGV PARA ESTILO DE TICKET "S2" */  
          $montoExonerado = $row['total_monto_exonerado'];
          $totalSinExonerado = $row['total_a_pagar'] - $montoExonerado;
          $subTotalSinExonerado = round($totalSinExonerado / 1.18 ,2);
          $igvTotalSinExonerado = round($totalSinExonerado - ($totalSinExonerado / 1.18),2);
          /* SETEO DE VARIABLES */  
          $row['sub_total'] = $subTotalSinExonerado;
          $row['total_igv'] = $igvTotalSinExonerado;
          $row['monto_exonerado'] = $montoExonerado;
          array_push($arrListadoProd, 
            array( 
              date('d/m/Y H:i:s',strtotime($strFechaVenta)),
              strtoupper($row['descripcion_td']),
              substr($row['ticket_venta'], 0, 3),
              substr($row['ticket_venta'], -9),
              $row['num_documento'],
              $cliente,
              ($row['tipofila'] == 'a' ? '0' : $row['sub_total']),
              ($row['tipofila'] == 'a' ? '0' : $row['monto_exonerado']),
              ($row['tipofila'] == 'a' ? '0' : $row['total_igv']),
              ($row['tipofila'] == 'a' ? '0' : $row['total_a_pagar']),
              ($row['tipofila'] == 'a' ? '0' : $row['total_sin_redondeo']),
              ($row['tipofila'] == 'a' ? '0' : $row['redondeo']),
              strtoupper($row['descripcion_med']),
              ($row['tipofila'] == 'a' ? 'ANULADO' : ' '), 
              strtoupper($row['username'])
            )
          );
          if( $row['tipofila'] == 'v' ){
            $sumTotalVenta += $row['total_a_pagar'];
          }
          // if( $row['tipofila'] == 'a' ){ 
          //   $countAnulados++;
          // }
        }
      }

      foreach ($listaNCCaja as $row) { 
        $strFechaVenta = $row['fecha_movimiento'];
        $sumTotalNCR += $row['total_a_pagar'];
        if(empty($row['idcliente'])){
          $cliente = '';
        }else{
          $cliente = strtoupper($row['apellido_paterno'] . ' ' . $row['apellido_paterno'] . ', ' . $row['nombres']);
        }
        $countNCR++;
        array_push($arrListadoNCR, 
          array(
            date('d/m/Y',strtotime($strFechaVenta)),
            strtoupper($row['descripcion_td']),
            // date('h:i:s a',strtotime($strFechaVenta)),
            substr($row['ticket_venta'], 0, 3),
            substr($row['ticket_venta'], -9),
            $row['num_documento'],
            $cliente,
            $row['total_a_pagar'],
            strtoupper($row['username'])
          )
        );
      }

      $this->excel->setActiveSheetIndex($cont);
      $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
      $this->excel->getActiveSheet()->setAutoFilter('B5:M5');

      $styleArrayTitle = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 14,
            'name'  => 'Verdana'
        ),
          'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
      );
      // ********************* TTIULO *************
      $this->excel->getActiveSheet()->getCell('B1')->setValue('DETALLE DE CAJAS '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
      $this->excel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('B1:M1');
      $this->excel->getActiveSheet()->getCell('B2')->setValue('TIPO PRODUCTO');
      if( $allInputs['modalidadTipo']['id'] == 1 ){
        $this->excel->getActiveSheet()->getCell('D2')->setValue('FORMULAS Y PREPARADOS');
      }elseif( $allInputs['modalidadTipo']['id'] == 2 ){
        $this->excel->getActiveSheet()->getCell('D2')->setValue('MEDICAMENTOS, VACUNAS, BAZAR Y PERFUMERIA');
      }else{
        $this->excel->getActiveSheet()->getCell('D2')->setValue('TODOS');
      }
      $this->excel->getActiveSheet()->getCell('B3')->setValue('EMPRESA');
      $this->excel->getActiveSheet()->getCell('D3')->setValue($empresa_admin['razon_social']);

      $cont++;

      $dataColumnsTP = array( 
        array('FECHA', 'TIPO DOCUMENTO', 'SERIE', 'NUMERO DE COMPROBANTE', 'DNI', 'CLIENTE', 'SUB TOTAL', 'INAFECTO', 'IGV', 'TOTAL', 'TOTAL SIN REDONDEO', 'REDONDEO', 'MEDIO DE PAGO', 'ESTADO', 'CAJERA (USUARIO)')
      ); 
      $styleArray = array(
        'borders' => array(
          'outline' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('argb' => '00000000') 
          ) 
        ),
        'font'=>  array(
            'bold'  => true,
            'size'  => 10,
            'name'  => 'Verdana'
        ),
          'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
      );
      
      // foreach(range('B','Z') as $columnID) {
      //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
      // }
      $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(18.71);
      $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18.71);
      $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(11.71);
      $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20.71);
      foreach(range('F','O') as $columnID) {
         $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(12.71);
      }
      //$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

      $this->excel->getActiveSheet()->getStyle('B5:P5')->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'B5');
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'B6');
      
      $currentCellTotal = count($arrListadoProd) + 5;
      $this->excel->getActiveSheet()->getStyle('D6:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
      $this->excel->getActiveSheet()->getStyle('E6:E'.$currentCellTotal)->getNumberFormat()->setFormatCode('000000000');
      $this->excel->getActiveSheet()->getStyle('B5:M5')->getAlignment()->setWrapText(true);

      $currentCellTotal += 4;
      $currentCellNCR = count($arrListadoProd) + 13;

      $styleTitleNCR = array( 
        'font' => array(
            'bold'  => true,
            'size'  => 14 
        )
      );
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellNCR.':J'.$currentCellNCR)->applyFromArray($styleTitleNCR);
      $dataColumnsNCR = array(
        array('FECHA', 'TIPO DOCUMENTO', 'SERIE', 'NUMERO DE COMPROBANTE', 'DNI', 'CLIENTE', 'MONTO', 'CAJERA (USUARIO)')
      );
      
      $this->excel->getActiveSheet()->getCell('B'.$currentCellNCR)->setValue('NOTAS DE CREDITO');
      $this->excel->getActiveSheet()->mergeCells('B'.$currentCellNCR.':C'.$currentCellNCR);
      if( empty($arrListadoNCR) ) {
        $arrListadoNCR = array(
          array('No se encontró notas de crédito.')
        );
        $this->excel->getActiveSheet()->mergeCells('B'.($currentCellNCR + 2) .':I'.($currentCellNCR + 2));
      }
      $dataColumnsMP = array( 
        array('MEDIO DE PAGO', 'CANT.', 'MONTO')
      );
      $arrListadoMP = array(); 
      foreach ($arrSoloMediosPago as $key => $valueSMP) {
        array_push($arrListadoMP, array(
            $valueSMP['descripcion_med'],
            (string)$valueSMP['cantidad_gen'],
            number_format($valueSMP['monto_gen'],2)
          )
        );
      }
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal + 1).':D'.($currentCellTotal + 1))->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'B'.($currentCellTotal + 1));
      $this->excel->getActiveSheet()->fromArray($arrListadoMP, null, 'B'.($currentCellTotal + 2));

      //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
      $styleArrayTotales = array(
        'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
        )
      ); 
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellTotal + 1).':H'.($currentCellTotal + 5))->applyFromArray($styleArrayTotales);
      $arrTotalesFooter = array(
        array('CANT. VENTAS', $countVentas),
        array('CANT. ANULADOS', $countAnulados),
        array('TOTAL VENTAS', empty($sumTotalVenta) ? '0.00' : number_format($sumTotalVenta,2) )
      ); 
      $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'G'.($currentCellTotal + 1));


      $this->excel->getActiveSheet()->getStyle('B'.($currentCellNCR + 1).':I'.($currentCellNCR + 1))->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsNCR, null, 'B'.($currentCellNCR + 1));
      $this->excel->getActiveSheet()->fromArray($arrListadoNCR, null, 'B'.($currentCellNCR + 2));
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellNCR + 1) .':I'.($currentCellNCR + 1))->getAlignment()->setWrapText(true);

      $currentCellTotalesNCR = $currentCellTotal + count($arrTotalesFooter) + count($arrListadoNCR) + 2; 
      $arrTotalesNCRFooter = array(
        array('CANT. N.CREDITO', $countNCR ),
        array('TOTAL N.CREDITO', empty($sumTotalNCR) ? '0.00' : number_format($sumTotalNCR,2))
      ); 
      $this->excel->getActiveSheet()->getStyle('H'.($currentCellTotalesNCR + 2).':I'.($currentCellTotalesNCR + 3))->applyFromArray($styleArrayTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesNCRFooter, null, 'G'.($currentCellTotalesNCR + 2));

      $arrTotalesSumFooter = array( 
        array('TOTAL EN CAJA: ', number_format(($sumTotalVenta+$sumTotalNCR),2) ) 
      ); 
      $styleArraySumTotales = array( 
        'font'=>  array( 
          'bold'  => true, 
          'size'  => 16 
        ) 
      ); 
      $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotalesNCR + 5).':G'.($currentCellTotalesNCR + 5))->applyFromArray($styleArraySumTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesSumFooter, null, 'F'.($currentCellTotalesNCR + 5));
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');

    //}

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );
    // if(  ){
    //   $arrData['flag'] = 0;
    // }
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicamentos_vendidos_fechas_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $arrContent = array();
    $cont = 0;
    // $item = 1;
    $listaMedicamentosVenta = $this->model_venta_farmacia->m_cargar_medicamentos_vendidos_desde_hasta($allInputs);

    $arrListadoProd = array();
    $sumTotalVenta = 0;
    $countVentas = 0;

   
    foreach ($listaMedicamentosVenta as $row) { 
      array_push($arrListadoProd, 
        array( 
          $row['idmedicamento'],
          ($row['nombre_lab']),
          strtoupper($row['denominacion']),
          $row['cantidad'],
          $row['monto']
        )
      );
      $sumTotalVenta += $row['monto'];
      $countVentas += ($row['cantidad']);
      // $item++;
    }

    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:E1');

    $cont++;
    /*foreach(range('A','Z') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }*/
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(16);

    $dataColumnsTP = array( 
      array('CÓDIGO', 'LABORATORIO','MEDICAMENTO', 'CANTIDAD', 'MONTO (S/.)')
    ); 
    $styleArray = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    
    
    $this->excel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A3');
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A4');
    
    $currentCellTotal = count($arrListadoProd) + 5;
    // $this->excel->getActiveSheet()->getStyle('D4:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
    // $this->excel->getActiveSheet()->getStyle('E4:E'.$currentCellTotal)->getNumberFormat()->setFormatCode('000000000');
    $this->excel->getActiveSheet()->getStyle('D4:D'.($currentCellTotal-2))->getNumberFormat()->setFormatCode('#,##0.00');
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('C'.($currentCellTotal + 1).':E'.($currentCellTotal + 1))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', $countVentas, number_format($sumTotalVenta,2))
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'C'.($currentCellTotal + 1));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicamentos_comprados_fechas_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $arrContent = array();
    $cont = 0;
    // $item = 1;
    $listaMedicamentosVenta = $this->model_venta_farmacia->m_cargar_medicamentos_comprados_desde_hasta($allInputs);

    $arrListadoProd = array();
    $sumTotalVenta = 0;
    $countVentas = 0;

   
    foreach ($listaMedicamentosVenta as $row) { 
      array_push($arrListadoProd, 
        array( 
          $row['idmedicamento'],
          strtoupper($row['denominacion']),
          $row['cantidad'],
          $row['monto']
        )
      );
      $sumTotalVenta += $row['monto'];
      $countVentas += ($row['cantidad']);
      // $item++;
    }

    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:D1');

    $cont++;
    foreach(range('A','Z') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }

    $dataColumnsTP = array( 
      array('CÓDIGO', 'MEDICAMENTO', 'CANTIDAD', 'MONTO (S/.)')
    ); 
    $styleArray = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    
    
    $this->excel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A3');
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A4');
    
    $currentCellTotal = count($arrListadoProd) + 5;
    // $this->excel->getActiveSheet()->getStyle('D4:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
    // $this->excel->getActiveSheet()->getStyle('E4:E'.$currentCellTotal)->getNumberFormat()->setFormatCode('000000000');
    $this->excel->getActiveSheet()->getStyle('D4:D'.($currentCellTotal-2))->getNumberFormat()->setFormatCode('#,##0.00');
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal + 1).':D'.($currentCellTotal + 1))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', $countVentas, number_format($sumTotalVenta,2))
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'B'.($currentCellTotal + 1));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_laboratorios_vendidos_fechas_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $arrContent = array();
    $cont = 0;
    // $item = 1;
    $lista = $this->model_venta_farmacia->m_cargar_laboratorios_vendidos_desde_hasta($allInputs);

    $arrListadoProd = array();
    $sumTotalVenta = 0;
    $countVentas = 0;

   // var_dump($lista); exit();
    foreach ($lista as $row) { 
      array_push($arrListadoProd, 
        array( 
          $row['idlaboratorio'],
          strtoupper($row['nombre_lab']),
          // $row['cantidad'],
          $row['monto']
        )
      );
      $sumTotalVenta += $row['monto'];
      // $countVentas += ($row['cantidad']);
      // $item++;
    }

    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:C1');

    $cont++;
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(66);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    // foreach(range('A','Z') as $columnID) {
    //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    // }

    $dataColumnsTP = array( 
      array('CÓDIGO', 'LABORATORIO', 'MONTO (S/.)')
    ); 
    $styleArray = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    
    
    $this->excel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A3');
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A4');
    
    $currentCellTotal = count($arrListadoProd) + 5;
    // $this->excel->getActiveSheet()->getStyle('D4:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
    // $this->excel->getActiveSheet()->getStyle('E4:E'.$currentCellTotal)->getNumberFormat()->setFormatCode('000000000');
    $this->excel->getActiveSheet()->getStyle('C4:C'.($currentCellTotal-2))->getNumberFormat()->setFormatCode('#,##0.00');
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal + 1).':C'.($currentCellTotal + 1))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', number_format($sumTotalVenta,2))
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'B'.($currentCellTotal + 1));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_compras_proveedor_fechas_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $arrContent = array();
    $cont = 0;
    // $item = 1;
    $lista = $this->model_venta_farmacia->m_cargar_compras_proveedor_desde_hasta($allInputs);

    $arrListadoProd = array();
    $sumTotalVenta = 0;
    $countVentas = 0;

   // var_dump($lista); exit();
    foreach ($lista as $row) { 
      array_push($arrListadoProd, 
        array( 
          $row['idproveedor'],
          strtoupper($row['razon_social']),
          // $row['cantidad'],
          $row['monto']
        )
      );
      $sumTotalVenta += $row['monto'];
      // $countVentas += ($row['cantidad']);
      // $item++;
    }

    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:C1');

    $cont++;
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(66);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    // foreach(range('A','Z') as $columnID) {
    //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    // }

    $dataColumnsTP = array( 
      array('CÓDIGO', 'PROVEEDOR', 'MONTO (S/.)')
    ); 
    $styleArray = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    
    
    $this->excel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A3');
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A4');
    
    $currentCellTotal = count($arrListadoProd) + 5;
    // $this->excel->getActiveSheet()->getStyle('D4:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
    // $this->excel->getActiveSheet()->getStyle('E4:E'.$currentCellTotal)->getNumberFormat()->setFormatCode('000000000');
    $this->excel->getActiveSheet()->getStyle('C4:C'.($currentCellTotal-2))->getNumberFormat()->setFormatCode('#,##0.00');
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal + 1).':C'.($currentCellTotal + 1))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', number_format($sumTotalVenta,2))
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'B'.($currentCellTotal + 1));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_control_stock_farmacia_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    $lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_agotarse(FALSE,$allInputs);

    $arrListadoProd = array();
    $item = 1;
    $cantCritico = 0;
    $cantMinimo = 0;
    $cantAgotado = 0;
    $hoja = 0;
    foreach ($lista as $row) { 
      switch ($row['estado']) {
          case 1:
              $estado = 'CRITICO';
              $cantCritico++;
              break;
          case 2:
              $estado = 'MINIMO';
              $cantMinimo++;
              break;
          case 3:
              $estado = 'AGOTADO';
              $cantAgotado++;
              break;
          default:
              break;
      }
      array_push($arrListadoProd, 
        array( 
          $item++,
          trim($row['denominacion']),
          trim($row['nombre_lab']),
          $row['stock_minimo'],
          $row['stock_critico'],
          $row['stock_maximo'],
          $row['stock_actual_malm'],
          $estado
        )
      );
    }

    $this->excel->setActiveSheetIndex($hoja);
    $this->excel->getActiveSheet()->setTitle('Control Stocks'); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    // foreach(range('A','Z') as $columnID) {
    //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    // }
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(12);

    // *********** TITULO
    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue( $allInputs['titulo'] ); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:H1');

    // *********** ENCABEZADO
    $styleArrayHeader = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    $this->excel->getActiveSheet()->getCell('A3')->setValue( 'Empresa' );
    $this->excel->getActiveSheet()->getCell('B3')->setValue( $allInputs['almacen']['empresa'] );
    $this->excel->getActiveSheet()->getCell('A4')->setValue( 'Sede' );
    $this->excel->getActiveSheet()->getCell('B4')->setValue( $allInputs['almacen']['sede'] );
    $this->excel->getActiveSheet()->getStyle('A3:A4')->applyFromArray($styleArrayHeader);


    $dataColumnsTP = array( 
      array('ITEM', 'MEDICAMENTO', 'LABORATORIO', 'STOCK MINIMO', 'STOCK CRITICO', 'STOCK MAXIMO', 'STOCK ACTUAL', 'ESTADO')
    ); 
    $styleArray = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      'fill' => array(
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'color' => array('argb' => 'FFBDD7EE'),
      ),
      'alignment' =>array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap' => true,  
      )
    );
    $this->excel->getActiveSheet()->getStyle('A6:H6')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A6');
    // *********** DATOS DETALLE
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A7');
    
    $currentCellTotal = count($arrListadoProd) + 8;
    
    //$currentCellTotal += 3;
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    // $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal).':D'.($currentCellTotal))->applyFromArray($styleArrayTotales);
    // $arrTotalesFooter = array(
    //   array('TOTALES', $countVentas, number_format($sumTotalVenta,2))
      
    // ); 
    // $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'B'.($currentCellTotal));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicos_en_venta_fechas_excel()  {
    ini_set('max_execution_time', 300);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    $lista = $this->model_venta_farmacia->m_cargar_medicos_en_ventas_desde_hasta($allInputs);

    $arrListadoProd = array();
    $sumTotalVenta = 0;
    $countVentas = 0;
    $hoja = 0;
    foreach ($lista as $row) { 
      $medico = strtoupper($row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', ' . $row['med_nombres']);
      array_push($arrListadoProd, 
        array( 
          $row['med_numero_documento'],
          strtoupper($medico),
          $row['cantidad'],
          $row['monto']
        )
      );
      $sumTotalVenta += $row['monto'];
      $countVentas += ($row['cantidad']);
      
    }

    $this->excel->setActiveSheetIndex($hoja);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    // foreach(range('A','Z') as $columnID) {
    //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    // }
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(17);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(80);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
    // *********** TITULO
    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:D1');
    
    
    // *********** ENCABEZADO
    $dataColumnsTP = array( 
      array('Nº DOCUMENTO', 'PROFESIONAL MEDICO', 'CANTIDAD', 'MONTO (S/.)')
    ); 
    $styleArray = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    
    
    $this->excel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A3');
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A4');
    
    $currentCellTotal = count($arrListadoProd) + 3;
    // $this->excel->getActiveSheet()->getStyle('D4:D'.$currentCellTotal)->getNumberFormat()->setFormatCode('000');
    $this->excel->getActiveSheet()->getStyle('A4:A'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->getStyle('D4:D'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $currentCellTotal += 3;
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellTotal).':D'.($currentCellTotal))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', $countVentas, number_format($sumTotalVenta,2))
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'B'.($currentCellTotal));

    //$this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR+5))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_inventario_farmacia_excel(){ 
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    $writer = WriterFactory::create(Type::XLSX); 
    $fileName = $allInputs['titulo'].'.xlsx'; 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xlsx'; 
    $writer->openToFile($filePath); 

    // CREACION DE ESTILOS 
      $styleH1 = (new StyleBuilder())
             ->setFontBold()
             ->setFontSize(14)
             ->setFontColor(Color::WHITE)
             ->setShouldWrapText(FALSE) 
             ->setBackgroundColor(Color::rgb(128, 174, 220))
             ->build();

    $lista = $this->model_medicamento_almacen->m_cargar_medicamentos_almacen_para_pdf($allInputs['paginate'],$allInputs['resultado']);
    if( $allInputs['resultado']['laboratorio']['id'] == 0 ){
      $singleRow = array('ITEM','COD.MED.','FORMA FARMACEUTICA','MEDICAMENTO','REG. SANITARIO','PRINCIPIOS ACTIVOS','LABORATORIO','STOCK INICIAL','STOCK ENTRADAS','STOCK SALIDAS', 'STOCK ACTUAL','ULT. PRECIO DE COMPRA','UTILIDAD %','UTILIDAD S/.','PRECIO VENTA'); 
      $writer->addRowWithStyle($singleRow, $styleH1);
      
      $i = 0;
      foreach ($lista as $row) { 
        $writer->addRow( 
          array( 
            ++$i,
            $row['idmedicamento'],
            $row['forma_farmaceutica'],
            strtoupper($row['medicamento']),
            $row['registro_sanitario'],
            $row['principios_activos'],
            $row['laboratorio'],
            $row['stock_inicial'],
            $row['stock_entradas'],
            $row['stock_salidas'],
            $row['stock_actual_malm'],
            $row['precio_ultima_compra'],
            $row['utilidad_porcentaje'],
            $row['utilidad_valor'],
            $row['precio_venta'],   
          )
        );
      }
    }else{
      $singleRow = array('ITEM','COD.MED.','FORMA FARMACEUTICA', 'MEDICAMENTO', 'REG. SANITARIO', 'PRINCIPIOS ACTIVOS', 'STOCK INICIAL', 'STOCK ENTRADAS', 'STOCK SALIDAS', 'STOCK ACTUAL','ULT. PRECIO DE COMPRA','UTILIDAD %','UTILIDAD S/.','PRECIO VENTA'); 
      $writer->addRow($singleRow);
      $i = 0;
      foreach ($lista as $row) { 
        $writer->addRow( 
          array( 
            ++$i,
            $row['idmedicamento'],
            $row['forma_farmaceutica'],
            strtoupper($row['medicamento']),
            $row['registro_sanitario'],
            $row['principios_activos'],
            $row['stock_inicial'],
            $row['stock_entradas'],
            $row['stock_salidas'],
            $row['stock_actual_malm'],
            $row['precio_ultima_compra'],
            $row['utilidad_porcentaje'],
            $row['utilidad_valor'],
            $row['precio_venta'],
          )
        );
      }
    }

    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xlsx',
      'flag'=> 1
    ); 
    $writer->close();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ordenes_compra_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $arrContent = array();
    $cont = 0;
    $i = 1;
    $lista = $this->model_orden_compra->m_cargar_ordenes_compra_para_reporte($allInputs);
    $currentCellEncabezado = 7;
    //var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) { 
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['orden_compra'],
          strtoupper($row['razon_social']),
          formatoFechaReporte($row['fecha_movimiento']),
          formatoFechaReporte($row['fecha_aprobacion']),
          formatoFechaReporte($row['fecha_entrega']),
          formatoFechaReporte($row['fecha_entrega_real']),
          $row['sub_total'],
          $row['total_igv'],
          $row['total_a_pagar']
        )
      );
    }
    $dataColumnsTP = array( 
      array('ITEM','Nº ORDEN.', 'PROVEEDOR', 'FECHA ORDEN','FECHA APROBACION', 'FECHA INGR. ESTIMADA','FECHA INGR. REAL','SUB TOTAL', 'IGV', 'TOTAL')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayEncabezado = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
      
    );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:J1');


    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(42);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(13);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':J'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':J'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':J'.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_ingresos_almacen_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $arrContent = array();
    $cont = 0;
    $i = 1;
    $allInputs['idtipoentrada'] = 0; // todas las entradas
    $sumTotalVenta = 0;
    $lista = $this->model_entrada_farmacia->m_cargar_entradas($allInputs);
    $currentCellEncabezado = 7;
    //var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      if( $row['tipo_movimiento'] == 2 ){
        $row['tipoingreso'] = 'COMPRA';
      }elseif( $row['tipo_movimiento'] == 4 ){
        $row['tipoingreso'] = 'REGALO';
      }elseif( $row['tipo_movimiento'] == 6 ){
        $row['tipoingreso'] = 'REINGRESO';
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['idmovimiento'],
          $row['factura'],
          $row['orden_compra'],
          strtoupper($row['razon_social']),
          formatoFechaReporte($row['fecha_movimiento']),
          $row['sub_total_sf'],
          $row['total_igv_sf'],
          $row['total_a_pagar_sf'],
          $row['tipoingreso']
        )
      );
      $sumTotalVenta += $row['total_a_pagar_sf'];
    }
    $dataColumnsTP = array( 
      array('ITEM','COD. INGR.', 'FACTURA','Nº ORDEN.', 'PROVEEDOR', 'FECHA ORDEN','SUB TOTAL', 'IGV', 'TOTAL','TIPO INGRESO')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayEncabezado = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
      
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:J1');
    /* datos de cabecera*/
    $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(42);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(12);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':J'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':J'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':J'.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':I'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');
    //$this->excel->getActiveSheet()->fromArray($dataColumnsMP, null, 'F'.($currentCellTotal + 1));
    $styleArrayTotales = array(
      'font'=>  array(
        'bold'  => true,
        'size'  => 11,
        'name'  => 'Verdana'
      )
    ); 
    $this->excel->getActiveSheet()->getStyle('H'.($currentCellTotal + 1).':I'.($currentCellTotal + 1))->applyFromArray($styleArrayTotales);
    $arrTotalesFooter = array(
      array('TOTALES', $sumTotalVenta)
      
    ); 
    $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'H'.($currentCellTotal + 1));


    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_traslados_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $arrContent = array();
    $cont = 0;
    $i = 1;
    $allInputs['idsubalmacen1'] = 0; // 
    $allInputs['idsubalmacen2'] = 0; // 
    $lista = $this->model_traslado_farmacia->m_cargar_traslados($allInputs);
    $currentCellEncabezado = 7;
    //var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
       $estado = '';
      }
      elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
        $estado = 'ANULADO';
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          'idmovimiento1' => $row['idmovimiento1'],
          'fecha_movimiento' => formatoFechaReporte($row['fecha_movimiento']),
          'subAlmacenOrigen' => strtoupper($row['subAlmacenOrigen']),
          'subAlmacenDestino' => strtoupper($row['subAlmacenDestino']),
          'usuario' => $row['usuario'],
          $estado
        )
      );
    }
    $dataColumnsTP = array( 
      array('ITEM','COD. MOV.', 'FECHA TRASLADO','SUBALMACEN ORIGEN', 'SUB ALMACEN DESTINO', 'RESPONSABLE','ESTADO')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayEncabezado = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
      
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:G1');
    /* datos de cabecera*/
    $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(45);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':G'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':G'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':G'.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    
       


    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_stock_monetizado_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    // ini_set('xdebug.var_display_max_depth', 5);
    // ini_set('xdebug.var_display_max_children', 256);
    // ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $arrContent = array();
    $cont = 0;
    $i = 1;
    $sumTotal = 0;
    $lista = $this->model_medicamento_almacen->m_cargar_stock_monetizado($allInputs);
    $arrPrincipal = array();
    $arrAuxMed = array();
    $currentCellEncabezado = 7;
    $arrListadoProd = array();
    
    if(!empty($lista)){
      foreach ($lista as $key => $row) {
        $arrAuxMed = array(
          'idmedicamento' => $row['idmedicamento'],
          'medicamento' => $row['denominacion'],
          'stock_actual_total' => $row['stock_actual_total'],
          'laboratorio' => $row['laboratorio'],
          'movimientos' => array()
        );
        $arrPrincipal[$row['idmedicamento']] = $arrAuxMed;
      }
      foreach ($arrPrincipal as $key => $medicamento) {
        $arrAuxMov = array();
        $i = 1;
        foreach ($lista as $key => $row) {
          if($medicamento['idmedicamento']==$row['idmedicamento']){
            if($i <= 2){
              array_push($arrAuxMov, array(
                'idmovimiento' => $row['idmovimiento'],
                'fecha_movimiento' => $row['fecha_movimiento'],
                'cantidad' => $row['cantidad'],
                'precio_unitario' => $row['precio_unitario'],
                'total_detalle' => $row['total_detalle'],
                )
              );
            }
            $i++;
            $arrPrincipal[$row['idmedicamento']]['movimientos'] = $arrAuxMov;
          }
        }  
      }
      foreach ($arrPrincipal as $key => $medicamento) {
        if(count($medicamento['movimientos']) == 1){
          // $arrPrincipal[$key]['precio_unitario_total'] = $medicamento['movimientos'][0]['precio_unitario'];
          $arrPrincipal[$key]['precio_unitario_total'] = $medicamento['movimientos'][0]['total_detalle']/$medicamento['movimientos'][0]['cantidad'];
        }else{
          /* FORMULA PARA CALCULAR EL PRECIO UNITARIO PONDERADO*/
          $arrPrincipal[$key]['precio_unitario_total'] = 
            (
              ($medicamento['movimientos'][0]['precio_unitario'] * $medicamento['movimientos'][0]['cantidad']) + 
              ($medicamento['movimientos'][1]['precio_unitario'] * $medicamento['movimientos'][1]['cantidad'])
            ) / ($medicamento['movimientos'][0]['cantidad'] + $medicamento['movimientos'][1]['cantidad']);
            (
              ($medicamento['movimientos'][0]['total_detalle'] + $medicamento['movimientos'][1]['total_detalle'])
            ) / ($medicamento['movimientos'][0]['cantidad'] + $medicamento['movimientos'][1]['cantidad']);
        }
      }
      // var_dump($arrPrincipal); exit();
      $valor = 0;
      $i = 1;
      /* ARRAY PARA EL LISTADO DEL EXCEL */
      if($allInputs['allStocks']){
        foreach ($arrPrincipal as $row) {
          if($row['stock_actual_total'] > 0){
            $valor = (float)$row['stock_actual_total']*(float)$row['precio_unitario_total'];
            // $valor = (float)$row['total_detalle'];
            array_push($arrListadoProd, 
              array(
                $i++,
                $row['idmedicamento'],
                $row['medicamento'],
                $row['stock_actual_total'],
                $row['precio_unitario_total'],
                $valor,
                $row['laboratorio']
              )
            );
            $sumTotal += $valor;  
          }
          
        }
      }else{
        foreach ($arrPrincipal as $row) {
          $valor = (float)$row['stock_actual_total']*(float)$row['precio_unitario_total'];
          array_push($arrListadoProd, 
            array(
              $i++,
              $row['idmedicamento'],
              $row['medicamento'],
              $row['stock_actual_total'],
              $row['precio_unitario_total'],
              $valor,
               $row['laboratorio']
            )
          );
          $sumTotal += $valor;
        }
      }
    }
    
    //var_dump($arrListadoProd); exit();
    $dataColumnsTP = array( 
      array('ITEM','COD. MED.', 'MEDICAMENTO', 'STOCK AL ' . $allInputs['hasta'], 'P.U. PONDERADO', 'VALOR', 'LABORATORIO')
    );
    $this->excel->setActiveSheetIndex($cont);
    
    $this->excel->getActiveSheet()->setTitle('Stock Monetizado');
    $this->excel->getActiveSheet()->getTabColor()->setRGB('5B9BD5');
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayEncabezado = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ), 
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:G1');
    /* datos de cabecera*/
    $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(50);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':F'.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':F'.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':F'.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':F'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');
    // $this->excel->getActiveSheet()->getCell('F825')->setValue('=SUM(F8:F824)');
    if($currentCellTotal>$currentCellEncabezado){
      $this->excel->getActiveSheet()->getCell('F'.($currentCellTotal+1))->setValue('=SUM(F'.($currentCellEncabezado+1) .':F'.($currentCellTotal).')');
    }
    //$this->excel->getActiveSheet()->getCell('F'.$currentCellTotal)->getCalculatedValue();
    //var_dump($arrListadoProd); exit(); 


    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_tarifario_farmacia_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $cont = 0;
    $i = 1;

    $lista = $this->model_medicamento_almacen->m_cargar_tarifario_farmacia($allInputs);
    $currentCellEncabezado = 4;
    $maxCol = 'E';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['idmedicamento'],
          strtoupper($row['denominacion']),
          strtoupper($row['laboratorio']),
          $row['precio_venta']
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','COD. MED.', 'MEDICAMENTO', 'LABORATORIO', 'PRECIO VENTA')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle('Tarifario'); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    // $styleArrayEncabezado = array(
    //   'font'=>  array(
    //       'bold'  => true,
    //       'size'  => 10,
    //       'name'  => 'Verdana'
    //   ),
    // );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo'] . ' AL ' . date('d-M-Y') ); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    // $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    // $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(55);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);

    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':I'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);




    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicamentos_vencidos_farmacia_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $cont = 0;
    $i = 1;

    $lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_vencer(FALSE,$allInputs['resultado']);
    $currentCellEncabezado = 4;
    $maxCol = 'G';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      // 1: VENCIDO
      // 2: MES ACTUAL
      // 3: 2 MESES
      switch ($row['estado_vencer']) {
          case 1:
              $estado = 'VENCIDO';
              break;
          case 2:
              $estado = 'MES ACTUAL';
              break;
          case 3:
              $estado = '2 - 3 MESES';
              break;
          default:
              break;
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['num_lote'],
          strtoupper($row['denominacion']),
          strtoupper($row['nombre_lab']),
          strtoupper($row['almacen']),       
          formatoFechaReporte3($row['fecha_vencimiento']),
          $estado
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','LOTE', 'MEDICAMENTO','LABORATORIO', 'ALMACEN', 'FECHA VENCIMIENTO','ESTADO')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle('Med. Venc.'); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    // $styleArrayEncabezado = array(
    //   'font'=>  array(
    //       'bold'  => true,
    //       'size'  => 10,
    //       'name'  => 'Verdana'
    //   ),
    // );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    // $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    // $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(55);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':I'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);




    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_historial_venta_medicamento_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $cont = 0;
    $i = 1;

    $paramPaginate = @$allInputs['paginate'];
    $paramPaginate['pageSize'] = 0;
    $paramPaginate['firstRow'] = 0;
    $paramDatos = @$allInputs['resultado'];
    $lista = $this->model_historial_venta_farm->m_cargar_ventas_historial_medicamento($paramPaginate,$paramDatos);


    $currentCellEncabezado = 4;
    $maxCol = 'J';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      // 1: VENCIDO
      // 2: MES ACTUAL
      // 3: 2 MESES
      /*switch ($row['estado_vencer']) {
          case 1:
              $estado = 'VENCIDO';
              break;
          case 2:
              $estado = 'MES ACTUAL';
              break;
          case 3:
              $estado = '2 - 3 MESES';
              break;
          default:
              break;
      }*/
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['orden_venta'],
          strtoupper($row['descripcion_td']),
          strtoupper($row['ticket_venta']),
          strtoupper($row['denominacion']),
          strtoupper($row['laboratorio']),
          formatoFechaReporte3($row['fecha_movimiento']),
          strtoupper($row['cantidad']),
          $row['precio_unitario_sf'],
          $row['total_detalle_sf'],
          
          // $estado
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','Nº ORDEN','TIPO DOCUMENTO','TICKET', 'MEDICAMENTO','LABORATORIO', 'FECHA VENTA','CANTIDAD','P. UNIT.', 'TOTAL' )
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($paramDatos['desde'].' - '.$paramDatos['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
    );
    $styleArrayTotales = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    // $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    // $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(19);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(65);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(13);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':J'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':G'.($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // TOTALES
    $this->excel->getActiveSheet()->getStyle('H'.($currentCellTotal+1).':J'.($currentCellTotal+1))->applyFromArray($styleArrayTotales);

    $this->excel->getActiveSheet()->getCell('H'.($currentCellTotal+1))->setValue('=SUM(H'.($currentCellEncabezado+1) .':H'.($currentCellTotal).')');
    $this->excel->getActiveSheet()->getCell('J'.($currentCellTotal+1))->setValue('=SUM(J'.($currentCellEncabezado+1) .':J'.($currentCellTotal).')');



    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicamentos_movimientos_temporales_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $cont = 0;
    $i = 1;
    // seteamos las variables de la paginacion para que salgan todos las filas
    $paramPaginate = @$allInputs['paginate'];
    $paramPaginate['pageSize'] = 0;
    $paramPaginate['firstRow'] = 0;
    $paramDatos = @$allInputs['resultado'];

    $lista = $this->model_caja_temporal_farmacia->m_cargar_productos_movimientos_temporales($paramDatos,$paramPaginate);
    // var_dump($lista);exit();

    $currentCellEncabezado = 4;
    $maxCol = 'I';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      array_push($arrListadoProd, 
        array(
          $i++,
          formatoFechaReporte3($row['fecha_movimiento']),
          $row['idmedicamento'],
          strtoupper($row['medicamento']),
          strtoupper($row['laboratorio']),
          $row['cantidad'],
          $row['precio_unitario_sf'],
          $row['total_detalle_sf'],
          $row['stock_actual_malm'],
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','FECHA VENTA','COD. MED.', 'MEDICAMENTO','LABORATORIO', 'CANTIDAD','P. UNIT.', 'TOTAL', 'STOCK ACTUAL' )
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($paramDatos['desde'].' - '.$paramDatos['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 14,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '5b9bd5', ),
       ),
    );
    $styleArrayProd = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          // 'color' => array('rgb' => '000000') 
      ),
    );
    $styleArrayTotales = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana'
      ),
    );
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    // $this->excel->getActiveSheet()->getCell('B3')->setValue('ALMACEN:');
    // $this->excel->getActiveSheet()->getCell('D3')->setValue($allInputs['almacen']['descripcion']);

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(65);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(13);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':H'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B'.($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // TOTALES
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotal+1).':H'.($currentCellTotal+1))->applyFromArray($styleArrayTotales);

    $this->excel->getActiveSheet()->getCell('H'.($currentCellTotal+1))->setValue('=SUM(H'.($currentCellEncabezado+1) .':H'.($currentCellTotal).')');




    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_medicamentos_movimientos_temporales_excel_copy(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    


    $writer = WriterFactory::create(Type::XLSX); // for XLSX files
    //$writer = WriterFactory::create(Type::CSV); // for CSV files
    //$writer = WriterFactory::create(Type::ODS); // for ODS files

    
    $writer->openToBrowser('prueba'); // stream data directly to the browser

    $writer->addRow($dataColumnsTP); // add a row at a time
    //$writer->addRows($multipleRows); // add multiple rows at a time

    $writer->close();


    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    //$objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_resumen_venta_formulas(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    $arrContent = array();
    $cont = 0;
    $i = 1;
    $lista = $this->model_venta_farmacia->m_cargar_venta_formulas_desde_hasta($allInputs);
    $listaNC = $this->model_venta_farmacia->m_cargar_nc_formulas($allInputs);
    // var_dump($listaNC); exit();
    $currentCellEncabezado = 9; // donde inicia el encabezado del listado
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    $arrListadoNC = array();
    foreach ($lista as $row) { 
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['idsolicitudformula'],
          $row['codigo_pedido'],
          strtoupper($row['cliente']),
          strtoupper($row['num_documento']),
          darFormatoDMY($row['fecha_movimiento']),
          $row['hora'],
          strtoupper($row['descripcion_td']),
          strtoupper($row['ticket_venta']),
          strtoupper($row['orden_venta']),
          strtoupper($row['descripcion_med']),
          $row['total_a_pagar'],
          strtoupper($row['estado']),
        )
      );
    }
    $i = 1;
    foreach ($listaNC as $row) { 
      array_push($arrListadoNC, 
        array(
          $i++,
          $row['idsolicitudformula'],
          $row['codigo_pedido'],
          strtoupper($row['cliente']),
          strtoupper($row['num_documento']),
          darFormatoDMY($row['fecha_movimiento']),
          $row['hora'],
          $row['orden_venta_origen'],
          $row['ticket_venta_origen'],
          $row['ticket_venta'],
          NULL,
          $row['total_a_pagar'],
        )
      );
    }
    $endColum = 'M';
    $dataColumnsTP = array( 
      array('#','Nº SOLICITUD', 'COD. PEDIDO', 'CLIENTE', 'D.N.I.','FECHA', 'HORA','TIPO DOCUMENTO','Nº DOCUMENTO', 'ORDEN VENTA','MEDIO DE PAGO', 'MONTO PAGADO', 'ESTADO')
    );
    $dataColumnsNC = array( 
      array('#','Nº SOLICITUD.', 'COD. PEDIDO','CLIENTE', 'D.N.I.','FECHA', 'HORA', 'ORDEN VENTA ORIGEN','TICKET VENTA ORIGEN', 'TICKET N.C.','', 'MONTO')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    // ESTILOS 
      $styleArrayTitle = array(
        'font'=>  array(
          'bold'  => false,
          'size'  => 18,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '00000000', ),
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
      );
      $styleArrayHeader = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri',
            'color' => array('rgb' => 'FFFFFF') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '70AD47', ),
         ),
      );
      $styleArrayProd = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'font'=>  array(
            'bold'  => false,
            'size'  => 10,
            'name'  => 'calibri',
            // 'color' => array('rgb' => '000000') 
        ),
      );
      $styleArrayTotales = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 15,
            'name'  => 'calibri'
        ),
      );
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$endColum.'1');
    // var_dump($allInputs); exit();
    // DATOS DE LA CABECERA
    $this->excel->getActiveSheet()->getStyle('B3:B7')->applyFromArray($styleArrayEncabezado);

    $this->excel->getActiveSheet()->getCell('B3')->setValue('EMPRESA:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['almacen']['empresa']);
    
    $this->excel->getActiveSheet()->getCell('B4')->setValue('SEDE:');
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['almacen']['sede']);
    
    $this->excel->getActiveSheet()->getCell('B6')->setValue('DESDE:');
    $this->excel->getActiveSheet()->getCell('C6')->setValue( $allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto']  );

    $this->excel->getActiveSheet()->getCell('B7')->setValue('HASTA:');
    $this->excel->getActiveSheet()->getCell('C7')->setValue( $allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(5,12,12,42,12,12,12,24,18,20,15,18,20);
      $i = 0;
      foreach(range('A',$endColum) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
      }


    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);
    // LISTADO
    $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('L'.($currentCellEncabezado+1).':L'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');

    // TOTAL
    $this->excel->getActiveSheet()->getStyle('K'.($currentCellTotal+2) .':L'.($currentCellTotal+2)  )->applyFromArray($styleArrayTotales);
    $this->excel->getActiveSheet()->getCell('K'.($currentCellTotal+2) )->setValue('TOTAL:');
    $this->excel->getActiveSheet()->getStyle('L'.($currentCellTotal+2))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
    $this->excel->getActiveSheet()->getCell('L'.($currentCellTotal+2))->setValue('=SUM(L'.($currentCellEncabezado+1) .':L'.($currentCellTotal+1).')');

    // NOTAS DE CREDITO
    if(!empty($listaNC)){
      $this->excel->getActiveSheet()->getCell('A'.($currentCellTotal+4))->setValue('NOTAS DE CREDITO');
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellTotal+4))->applyFromArray($styleArrayTotales);
      $currentCellEncabezadoNC = $currentCellTotal+5;
      $currentCellTotalNC = count($arrListadoNC) + $currentCellEncabezadoNC;
      // ENCABEZADO DE LA LISTA
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezadoNC.':L'.$currentCellEncabezadoNC)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezadoNC.':L'.$currentCellEncabezadoNC)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezadoNC+1).':L'.$currentCellTotalNC)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezadoNC.':L'.$currentCellEncabezadoNC);
      // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezadoNC+1).':D'.$currentCellTotalNC)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsNC, null, 'A'.$currentCellEncabezadoNC);
      $this->excel->getActiveSheet()->fromArray($arrListadoNC, null, 'A'.($currentCellEncabezadoNC+1));
      $this->excel->getActiveSheet()->getStyle('K'.($currentCellEncabezadoNC+1).':L'.($currentCellTotalNC))->getNumberFormat()->setFormatCode('#,##0.00');
      // TOTAL
      $this->excel->getActiveSheet()->getStyle('K'.($currentCellTotalNC+2) .':L'.($currentCellTotalNC+2)  )->applyFromArray($styleArrayTotales);
      $this->excel->getActiveSheet()->getCell('K'.($currentCellTotalNC+2) )->setValue('TOTAL N.C.:');
      $this->excel->getActiveSheet()->getStyle('L'.($currentCellTotalNC+2))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
      $this->excel->getActiveSheet()->getCell('L'.($currentCellTotalNC+2))->setValue('=SUM(L'.($currentCellEncabezadoNC+1) .':L'.($currentCellTotalNC+1).')');

      // TOTAL EN CAJA
      $this->excel->getActiveSheet()->getStyle('J'.($currentCellTotalNC+4) .':L'.($currentCellTotalNC+4)  )->applyFromArray($styleArrayTotales);
      $this->excel->getActiveSheet()->getCell('J'.($currentCellTotalNC+4) )->setValue('TOTAL EN CAJA:');
      $this->excel->getActiveSheet()->getStyle('L'.($currentCellTotalNC+4))->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
      $this->excel->getActiveSheet()->getCell('L'.($currentCellTotalNC+4))->setValue('=L'.($currentCellTotal+2) .'+L'.($currentCellTotalNC+2).')');
      $this->excel->getActiveSheet()->mergeCells('J'.($currentCellTotalNC+4) . ':K'.($currentCellTotalNC+4) );
      $this->excel->getActiveSheet()->getStyle('J'.($currentCellTotalNC+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

    }


    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_inventario_valorizado_medicamento_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    // var_dump($allInputs['medicamento']); exit();
    foreach ($allInputs['medicamento'] as $row) {
      $data = array();
      $data['medicamento'] = $row;
      $data['almacen'] = $allInputs['almacen'];
      $data['anioDesdeCbo'] = $allInputs['anioDesdeCbo'];
      $data['mes'] = $allInputs['mes'];
      $arrListado = obtenerKardexValorizadoContabilidad($data);
      
    }
    // var_dump($arrListado); exit();
    $arrFiltrado = array();
    $inicial = TRUE;
    $cantidadSaldoAnterior = 0;
    $precioUnitarioAnterior = 0;
    $promedioAnterior = 0;
    if(empty($arrFiltrado)){
      $excluye = NULL;
    }else{
      $excluye = $arrListado[0]['excluye_igv'] == 1 ? 'si excluye' : 'no excluye';
    }
    foreach ($arrListado as $row2) {
      $month = date('m', strtotime($row2['fecha']));
      $year = date('Y', strtotime($row2['fecha']));
      $fecha_inicial = '01-' . str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.$allInputs['anioDesdeCbo'];
      
      if( ($year == $allInputs['anioDesdeCbo']) && ($month == $allInputs['mes']['id']) ){
        if( $inicial ){
          // $entrada_inicial = $row2['cantidad_saldo'] + $row2['salida'] - $row2['entrada'];
          $entrada_inicial = $cantidadSaldoAnterior;
          array_push($arrFiltrado, array(
            'fecha_movimiento' => $fecha_inicial,
            'fecha' => $fecha_inicial,
            'tipo_documento' => 0,
            'serie' => NULL,
            'numero' => NULL,
            'tipo_movimiento' => 16, // SALDO INICIAL
            'entrada' => $entrada_inicial,
            'salida' => NULL,
            // 'cantidad' => $row2['cantidad'],
            'precio_unitario' => $precioUnitarioAnterior,
            'valor_entrada' => $entrada_inicial * $precioUnitarioAnterior,
            'valor_salida' => 0,
            'cantidad_saldo' => $entrada_inicial,
            'valor_saldo' => $entrada_inicial * $precioUnitarioAnterior,
            'promedio' => $precioUnitarioAnterior
            )
          ); 
        }                                       
        array_push($arrFiltrado, array(
          'fecha_movimiento' => $row2['fecha_movimiento'],
          'fecha' => $row2['fecha'],
          'tipo_documento' => $row2['tipo_documento'],
          'serie' => $row2['serie'],
          'numero' => $row2['numero'],
          'tipo_movimiento' => $row2['tipo_movimiento'],
          'entrada' => $row2['entrada'],
          'salida' => $row2['salida'],
          // 'cantidad' => $row2['cantidad'],
          'precio_unitario' => $row2['precio_unitario'],
          'valor_entrada' => $row2['valor_entrada'],
          'valor_salida' => $row2['valor_salida'],
          'cantidad_saldo' => $row2['cantidad_saldo'],
          'valor_saldo' => $row2['valor_saldo'],
          'promedio' => $row2['promedio']
          )
        );
        $inicial = FALSE;
      }
      $cantidadSaldoAnterior = $row2['cantidad_saldo'];
      $precioUnitarioAnterior = $row2['precio_unitario'];
      $promedioAnterior = $row2['promedio'];
      
      
    }
    // var_dump($year);  var_dump($allInputs['anioDesdeCbo']); var_dump($month); var_dump($allInputs['mes']['id']);
    // var_dump(($year == $allInputs['anioDesdeCbo']) && ($month <= $allInputs['mes']['id']));
    // var_dump( (int)$year < (int)$allInputs['anioDesdeCbo'] ); 
    // exit();
    if( empty($arrFiltrado) ){
      if( ($year == $allInputs['anioDesdeCbo']) && ($month <= $allInputs['mes']['id']) 
          || ($year < $allInputs['anioDesdeCbo'])
        ){
        array_push($arrFiltrado, array(
          'fecha_movimiento' => $fecha_inicial,
          'fecha' => $fecha_inicial,
          'tipo_documento' => 0,
          'serie' => NULL,
          'numero' => NULL,
          'tipo_movimiento' => 16, // SALDO INICIAL
          'entrada' => $cantidadSaldoAnterior,
          'salida' => NULL,
          // 'cantidad' => $row2['cantidad'],
          'precio_unitario' => $precioUnitarioAnterior,
          'valor_entrada' => $cantidadSaldoAnterior * $precioUnitarioAnterior,
          'valor_salida' => 0,
          'cantidad_saldo' => $cantidadSaldoAnterior,
          'valor_saldo' => $cantidadSaldoAnterior * $precioUnitarioAnterior,
          'promedio' => $promedioAnterior
          )
        );
      }
    }
    $this->excel->setActiveSheetIndex(0);
    //name the worksheet
    $this->excel->getActiveSheet()->setTitle('KARDEX VALORIZADO');
    

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 12,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );

    $this->excel->getActiveSheet()->getCell('A1')->setValue('FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO"');
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:N1');
    $styleArrayBold = array(
      'font'=>  array(
          'bold'  => true,
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    $styleArray = array(
      'font'=>  array(
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $filaGrilla = 12; // FILA DESDE DONDE SE PINTARA LA GRILLA
    $this->excel->getActiveSheet()->getStyle('A3:A' . ($filaGrilla - 1) )->applyFromArray($styleArrayBold);
    $this->excel->getActiveSheet()->getStyle('C3:C' . ($filaGrilla - 1) )->applyFromArray($styleArray);

    $this->excel->getActiveSheet()->getCell('A3')->setValue('PERIODO:');
    $this->excel->getActiveSheet()->getCell('A4')->setValue('RUC:');
    $this->excel->getActiveSheet()->getCell('A5')->setValue('RAZON SOCIAL:');
    $this->excel->getActiveSheet()->getCell('A6')->setValue('ESTABLECIMIENTO:');
    $this->excel->getActiveSheet()->getCell('A7')->setValue('CÓDIGO DE LA EXISTENCIA:');
    $this->excel->getActiveSheet()->getCell('A8')->setValue('TIPO (TABLA 5):');
    $this->excel->getActiveSheet()->getCell('A9')->setValue('DESCRIPCION:');
    $this->excel->getActiveSheet()->getCell('A10')->setValue('CÓD. DE LA UND. DE MED. (TABLA 6):');
    
    $this->excel->getActiveSheet()->getCell('C3')->setValue( $allInputs['mes']['id'].'/'.$allInputs['anioDesdeCbo'] );
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['almacen']['ruc']);
    $this->excel->getActiveSheet()->getCell('C5')->setValue($allInputs['almacen']['empresa']);
    $this->excel->getActiveSheet()->getCell('C6')->setValue($allInputs['almacen']['direccion']);
    $this->excel->getActiveSheet()->getCell('C7')->setValue($allInputs['medicamento'][0]['idmedicamento']);
    $this->excel->getActiveSheet()->getCell('C8')->setValue('01');
    $this->excel->getActiveSheet()->getCell('C9')->setValue($allInputs['medicamento'][0]['medicamento']);
    $this->excel->getActiveSheet()->getCell('C10')->setValue('99 ' . $allInputs['medicamento'][0]['unidad_medida']);
    // $this->excel->getActiveSheet()->getCell('B5')->setValue($allInputs['medicamento']['medicamento']);

    // $this->excel->getActiveSheet()->getStyle('D4:D6')->applyFromArray($styleArray);
    // $this->excel->getActiveSheet()->getCell('D4')->setValue('PERIODO');
    // $this->excel->getActiveSheet()->getCell('D5')->setValue('DESDE');
    // $this->excel->getActiveSheet()->getCell('D6')->setValue('HASTA');
    // $this->excel->getActiveSheet()->getCell('E5')->setValue($allInputs['busqueda']['desde']);
    // $this->excel->getActiveSheet()->getCell('E6')->setValue($allInputs['busqueda']['hasta']);
    
    $dataColumnsTP = array( 
      array(
        'DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR', '', '', '',
        'TIPO DE OPERACIÓN (TABLA 12)',
        'ENTRADAS', '', '',
        'SALIDAS', '', '',
        'SALDO FINAL', '', '')
    );
    $dataColumnsSUB = array( 
      array('FECHA', 'TIPO (TABLA 10)', 'SERIE', 'NÚMERO', '',
        'CANTIDAD ', 'COSTO UNITARIO ', 'COSTO TOTAL',
        'CANTIDAD ', 'COSTO UNITARIO ', 'COSTO TOTAL',
        'CANTIDAD ', 'COSTO UNITARIO ', 'COSTO TOTAL', )
    );
    $arrTableDetalleKardex = array();
    foreach ($arrFiltrado as $key => $row) {
      array_push($arrTableDetalleKardex, array(
          $row['fecha_movimiento'], 
          $row['tipo_documento'], 
          $row['serie'],
          $row['numero'],
          $row['tipo_movimiento'],
          (string)$row['entrada'],
          empty($row['entrada'])? NULL : $row['precio_unitario'], 
          (string)$row['valor_entrada'],
          (string)$row['salida'],
          empty($row['salida'])? NULL : $row['precio_unitario'], 
          (string)$row['valor_salida'], 
          (string)$row['cantidad_saldo'],
          (string)$row['promedio'],
          (string)$row['valor_saldo'],
          
        )
      );
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );

    $styleArrayTable = array(
      'borders' => array(
        'vertical' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayTable2 = array(
      'borders' => array(
        'left' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
         'right' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayTable3 = array(
      'borders' => array(
        'right' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayContorno = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
        'vertical' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $endRows = count($arrTableDetalleKardex) +  $filaGrilla + 2;

    $this->excel->getActiveSheet()->getStyle('A'. $filaGrilla .':N'. ($filaGrilla + 1) )->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A' .  $filaGrilla);
    $this->excel->getActiveSheet()->fromArray($dataColumnsSUB, null, 'A' .  ($filaGrilla + 1) );

    $this->excel->getActiveSheet()->getStyle('B'. ($filaGrilla + 2) .':M' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('D11:E' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('D11:D' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('F11:G' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('F11:F' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('H11:I' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('H11:H' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('J11:J' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('A'. ($filaGrilla + 2) .':N' . $endRows)->applyFromArray($styleArrayContorno);

    $this->excel->getActiveSheet()->fromArray($arrTableDetalleKardex, null, 'A'. ($filaGrilla + 2) .'');

    $this->excel->getActiveSheet()->getStyle('A'. ($filaGrilla) .':N'. ($filaGrilla + 1) .'')->getAlignment()->setWrapText(true); 

    $this->excel->getActiveSheet()->mergeCells('A'. ($filaGrilla) .':D'. ($filaGrilla) .'');
    $this->excel->getActiveSheet()->mergeCells('E'. ($filaGrilla) .':E'. ($filaGrilla + 1) .'');
    $this->excel->getActiveSheet()->mergeCells('F'. ($filaGrilla) .':H'. ($filaGrilla) .'');
    $this->excel->getActiveSheet()->mergeCells('I'. ($filaGrilla) .':K'. ($filaGrilla) .'');
    $this->excel->getActiveSheet()->mergeCells('L'. ($filaGrilla) .':N'. ($filaGrilla) .'');
   
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(22);
    // $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
    foreach(range('B','E') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(17);
    }
    foreach(range('F','N') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(12);
    }
    
    $this->excel->getActiveSheet()->getRowDimension($filaGrilla)->setRowHeight(30);

    $this->excel->getActiveSheet()->getStyle('G'.($filaGrilla+2).':H'.($endRows))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('J'.($filaGrilla+2).':K'.($endRows))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('M'.($filaGrilla+2).':N'.($endRows))->getNumberFormat()->setFormatCode('#,##0.00');

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_inventario_medicamento_unidades_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    $arrListado = obtenerKardexValorizadoContabilidad($allInputs);
    $arrFiltrado = array();
    $inicial = TRUE;
    foreach ($arrListado as $row2) {
      $month = date('m', strtotime($row2['fecha']));
      $year = date('Y', strtotime($row2['fecha']));
      if( ($year == $allInputs['anioDesdeCbo']) && ($month == $allInputs['mes']['id']) ){
        if( $inicial ){
          $entrada_inicial = $row2['cantidad_saldo'] + $row2['salida'] - $row2['entrada'];
          $fecha_inicial = '01-' . str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.$allInputs['anioDesdeCbo'];
          array_push($arrFiltrado, array(
            'fecha_movimiento' => $fecha_inicial,
            'fecha' => $fecha_inicial,
            'tipo_documento' => 0,
            'serie' => NULL,
            'numero' => NULL,
            'tipo_movimiento' => 16, // SALDO INICIAL
            'entrada' => $entrada_inicial,
            'salida' => NULL,
            // 'cantidad' => $row2['cantidad'],
            'precio_unitario' => $row2['precio_unitario'],
            'valor_entrada' => $entrada_inicial * $row2['precio_unitario'],
            'valor_salida' => 0,
            'cantidad_saldo' => $entrada_inicial,
            'valor_saldo' => $entrada_inicial * $row2['precio_unitario'],
            'promedio' => $row2['promedio']
            )
          ); 
        }                                       
        array_push($arrFiltrado, array(
          'fecha_movimiento' => $row2['fecha_movimiento'],
          'fecha' => $row2['fecha'],
          'tipo_documento' => $row2['tipo_documento'],
          'serie' => $row2['serie'],
          'numero' => $row2['numero'],
          'tipo_movimiento' => $row2['tipo_movimiento'],
          'entrada' => $row2['entrada'],
          'salida' => $row2['salida'],
          // 'cantidad' => $row2['cantidad'],
          'precio_unitario' => $row2['precio_unitario'],
          'valor_entrada' => $row2['valor_entrada'],
          'valor_salida' => $row2['valor_salida'],
          'cantidad_saldo' => $row2['cantidad_saldo'],
          'valor_saldo' => $row2['valor_saldo'],
          'promedio' => $row2['promedio']
          )
        );
        $inicial = FALSE;
      }
      
    }
    // var_dump($arrFiltrado); exit();
    // $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    //var_dump($allInputs); exit();
    //activate worksheet number 1
    $this->excel->setActiveSheetIndex(0);
    //name the worksheet
    

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 12,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );

    $this->excel->getActiveSheet()->getCell('A1')->setValue('FORMATO 12.1: "REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS"');
    $this->excel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true); 
    $this->excel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:H1');
    $styleArrayBold = array(
      'font'=>  array(
          'bold'  => true,
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );
    $styleArray = array(
      'font'=>  array(
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $filaGrilla = 12; // FILA DESDE DONDE SE PINTARA LA GRILLA
    $this->excel->getActiveSheet()->getStyle('A3:A' . ($filaGrilla - 1) )->applyFromArray($styleArrayBold);
    $this->excel->getActiveSheet()->getStyle('C3:C' . ($filaGrilla - 1) )->applyFromArray($styleArray);

    $this->excel->getActiveSheet()->getCell('A3')->setValue('PERIODO:');
    $this->excel->getActiveSheet()->getCell('A4')->setValue('RUC:');
    $this->excel->getActiveSheet()->getCell('A5')->setValue('RAZON SOCIAL:');
    $this->excel->getActiveSheet()->getCell('A6')->setValue('ESTABLECIMIENTO:');
    $this->excel->getActiveSheet()->getCell('A7')->setValue('CÓDIGO DE LA EXISTENCIA:');
    $this->excel->getActiveSheet()->getCell('A8')->setValue('TIPO (TABLA 5):');
    $this->excel->getActiveSheet()->getCell('A9')->setValue('DESCRIPCION:');
    $this->excel->getActiveSheet()->getCell('A10')->setValue('CÓD. DE LA UND. DE MED. (TABLA 6):');
    
    $this->excel->getActiveSheet()->getCell('C3')->setValue( $allInputs['mes']['id'].'/'.$allInputs['anioDesdeCbo'] );
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['almacen']['ruc']);
    $this->excel->getActiveSheet()->getCell('C5')->setValue($allInputs['almacen']['empresa']);
    $this->excel->getActiveSheet()->getCell('C6')->setValue($allInputs['almacen']['direccion']);
    $this->excel->getActiveSheet()->getCell('C7')->setValue($allInputs['medicamento']['idmedicamento']);
    $this->excel->getActiveSheet()->getCell('C8')->setValue('01');
    $this->excel->getActiveSheet()->getCell('C9')->setValue($allInputs['medicamento']['medicamento']);
    $this->excel->getActiveSheet()->getCell('C10')->setValue('99 ' . $allInputs['medicamento']['unidad_medida']);
    // $this->excel->getActiveSheet()->getCell('B5')->setValue($allInputs['medicamento']['medicamento']);

    // $this->excel->getActiveSheet()->getStyle('D4:D6')->applyFromArray($styleArray);
    // $this->excel->getActiveSheet()->getCell('D4')->setValue('PERIODO');
    // $this->excel->getActiveSheet()->getCell('D5')->setValue('DESDE');
    // $this->excel->getActiveSheet()->getCell('D6')->setValue('HASTA');
    // $this->excel->getActiveSheet()->getCell('E5')->setValue($allInputs['busqueda']['desde']);
    // $this->excel->getActiveSheet()->getCell('E6')->setValue($allInputs['busqueda']['hasta']);
    
    $dataColumnsTP = array( 
      array(
        'DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR', '', '', '',
        'TIPO DE OPERACIÓN (TABLA 12)',
        'ENTRADAS',
        'SALIDAS',
        'SALDO FINAL')
    );
    $dataColumnsSUB = array( 
      array('FECHA', 'TIPO (TABLA 10)', 'SERIE', 'NÚMERO', '','','','', )
    );
    $arrTableDetalleKardex = array();
    foreach ($arrFiltrado as $key => $row) {
      array_push($arrTableDetalleKardex, array(
          $row['fecha_movimiento'], 
          $row['tipo_documento'], 
          $row['serie'],
          $row['numero'],
          $row['tipo_movimiento'],
          (string)$row['entrada'],
          (string)$row['salida'],
          (string)$row['cantidad_saldo'],
        )
      );
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          // 'color' => array('rgb' => 'FF0000'),
          'size'  => 10,
          'name'  => 'Verdana'
      )
    );

    $styleArrayTable = array(
      'borders' => array(
        'vertical' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayTable2 = array(
      'borders' => array(
        'left' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
         'right' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayTable3 = array(
      'borders' => array(
        'right' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $styleArrayContorno = array(
      'borders' => array(
        'outline' => array( 
          'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
          'color' => array('argb' => '00000000') 
        ),
        'vertical' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00000000') 
        ),
      )
    );
    $endRows = count($arrTableDetalleKardex) +  $filaGrilla + 2;

    $this->excel->getActiveSheet()->getStyle('A'. $filaGrilla .':H'. ($filaGrilla + 1) )->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A' .  $filaGrilla);
    $this->excel->getActiveSheet()->fromArray($dataColumnsSUB, null, 'A' .  ($filaGrilla + 1) );

    $this->excel->getActiveSheet()->getStyle('B'. ($filaGrilla + 2) .':G' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('D11:E' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('D11:D' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('F11:G' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('F11:F' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('H11:I' . $endRows)->applyFromArray($styleArrayTable2);
    // $this->excel->getActiveSheet()->getStyle('H11:H' . $endRows)->applyFromArray($styleArrayTable3);
    // $this->excel->getActiveSheet()->getStyle('J11:J' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('A'. ($filaGrilla + 2) .':H' . $endRows)->applyFromArray($styleArrayContorno);

    $this->excel->getActiveSheet()->fromArray($arrTableDetalleKardex, null, 'A'. ($filaGrilla + 2) .'');

    $this->excel->getActiveSheet()->getStyle('A'. ($filaGrilla) .':H'. ($filaGrilla + 1) .'')->getAlignment()->setWrapText(true); 

    $this->excel->getActiveSheet()->mergeCells('A'. ($filaGrilla) .':D'. ($filaGrilla) );
    $this->excel->getActiveSheet()->mergeCells('E'. ($filaGrilla) .':E'. ($filaGrilla + 1) );
    $this->excel->getActiveSheet()->mergeCells('F'. ($filaGrilla) .':F'. ($filaGrilla + 1) );
    $this->excel->getActiveSheet()->mergeCells('G'. ($filaGrilla) .':G'. ($filaGrilla + 1) );
    $this->excel->getActiveSheet()->mergeCells('H'. ($filaGrilla) .':H'. ($filaGrilla + 1) );
   
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(22);
    // $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
    foreach(range('B','H') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(17);
    }
    // foreach(range('F','H') as $columnID) {
    //   $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(12);
    // }
    
    $this->excel->getActiveSheet()->getRowDimension($filaGrilla)->setRowHeight(30);

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_formulas_vendidas_costo_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    // TRATAMIENTO DE DATOS
      $lista = $this->model_venta_farmacia->m_cargar_formulas_pagadas($allInputs);
      $arrListadoProd = array();
      $costo_total = 0;
      $monto_total = 0;
      $cantidad_total = 0;
      $i = 1;
      foreach ($lista as $row) {
        $total_detalle_costo = ($row['precio_costo'] * $row['cantidad']);
        array_push($arrListadoProd, 
          array(
            $i++,
            $row['idsolicitudformula'],
            $row['codigo_pedido'],
            // ( formatoFechaReporte($row['fecha_movimiento'])),
            $row['fecha'],
            $row['hora'],
            strtoupper_total($row['paciente']),
            strtoupper_total($row['denominacion']),
            $row['cantidad'],
            $row['precio_costo'],
            $row['precio_unitario'],
            number_format($total_detalle_costo,2),
            $row['total_detalle'],
          )
        );
        $costo_total += $total_detalle_costo;
        $monto_total += $row['total_detalle'];
        $cantidad_total += ($row['cantidad']);
      }
    // SETEO DE VARIABLES
    $dataColumnsTP = array( 
      '#','Nº SOLICITUD.','CODIGO PEDIDO', 'FECHA VENTA', 'HORA', 'PACIENTE','FORMULA', 'CANT', 'P.U. COSTO', 'P.U. VENTA', 'TOTAL COSTO', 'TOTAL VENTA'
    );
    $endColum = 'L';
    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']);
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(5,12,15,12,12,50,50,12,12,12,20,20);
    $i = 0;
    foreach(range('A',$endColum) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
    }
    
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    // ESTILOS
      $styleArrayTitle = array(
        'font'=>  array(
          'bold'  => false,
          'size'  => 18,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '00000000', ),
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
      );
      $styleArrayHeader = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri',
            'color' => array('rgb' => 'FFFFFF') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '70AD47', ),
         ),
      );
      $styleArrayProd = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'font'=>  array(
            'bold'  => false,
            'size'  => 10,
            'name'  => 'calibri',
            // 'color' => array('rgb' => '000000') 
        ),
      );
      $styleArrayTotales = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 16,
            'name'  => 'calibri'
        ),
      );
    // TITULO
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' DEL '.strtr($allInputs['desde'], '-', '/').' AL '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
    $this->excel->getActiveSheet()->getStyle('B3:B7')->applyFromArray($styleArrayEncabezado);

    $this->excel->getActiveSheet()->getCell('B3')->setValue('EMPRESA:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['almacen']['empresa']);
    
    $this->excel->getActiveSheet()->getCell('B4')->setValue('SEDE:');
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['almacen']['sede']);
    
    $this->excel->getActiveSheet()->getCell('B6')->setValue('DESDE:');
    $this->excel->getActiveSheet()->getCell('C6')->setValue( $allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto']  );

    $this->excel->getActiveSheet()->getCell('B7')->setValue('HASTA:');
    $this->excel->getActiveSheet()->getCell('C7')->setValue( $allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );
    
    // ENCABEZADO DE LA LISTA
    $currentCellEncabezado = 9; // donde inicia el encabezado del listado
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
    // LISTADO
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('C'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


    // TOTAL
    $this->excel->getActiveSheet()->getStyle('J'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2)  )->applyFromArray($styleArrayTotales);
    $this->excel->getActiveSheet()->getCell('J'.($currentCellTotal+2) )->setValue('TOTAL');
    $this->excel->getActiveSheet()->getStyle('K'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2) )->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
    $this->excel->getActiveSheet()->getCell('K'.($currentCellTotal+2))->setValue('=SUM(K'.($currentCellEncabezado+1) .':K'.($currentCellTotal+1).')');
    $this->excel->getActiveSheet()->getCell('L'.($currentCellTotal+2))->setValue('=SUM(L'.($currentCellEncabezado+1) .':L'.($currentCellTotal+1).')');

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_preparados_pagados_excel(){ 
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    $writer = WriterFactory::create(Type::XLSX); 
    $fileName = $allInputs['titulo'].'.xls'; 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xls'; 
    $writer->openToFile($filePath); 

    $lista = $this->model_venta_farmacia->m_cargar_formulas_pagadas($allInputs);
    // $firstSheet = $writer->getCurrentSheet();
    //$newSheet = $writer->addNewSheetAndMakeItCurrent();

    $singleRow = array('ITEM','N° SOLICITUD', 'CODIGO PEDIDO', 'FECHA', 'HORA', 'PACIENTE', 'FORMULA', 'CANTIDAD','COSTO', 'MEDICO');
    $writer->addRow($singleRow);
    $item = 1;
    // var_dump($lista); exit();
    foreach ($lista as $row) {
      $writer->addRow( 
        array( 
          $item++,
          str_pad($row['idsolicitudformula'], 6, '0', STR_PAD_LEFT),
          $row['codigo_pedido'],
          // ( formatoFechaReporte($row['fecha_movimiento'])),
          $row['fecha'],
          $row['hora'],
          // utf8_decode($row['encargado']),
          // strlen(utf8_decode($row['paciente'])),
          $row['paciente'],
          // strlen(utf8_decode($row['denominacion'])),
          $row['denominacion'],
          $row['cantidad'],
          $row['precio_costo'],
          $row['medico'],
          // utf8_decode($estado), 
        ) 
      ); 
    }
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xls',
      'flag'=> 1
    ); 
    $writer->close();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_pedido_formulas_jj_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    // TRATAMIENTO DE DATOS
      $lista = $this->model_venta_farmacia->m_cargar_pedidos_cabecera($allInputs);
      // var_dump($lista); exit();
      $arrListadoProd = array();
      $arrAuxCab = array();
      $arrCabecera = array();
      $arrDetalle = array();
      $arrIdMedicosNuevos = array();
      $arrIdFormulasNuevas = array();
      $arrMedicosNuevos = array();
      $arrFormulasNuevas = array();
      $arrIdDetalle = array();
      $costo_total = 0;
      $monto_total = 0;
      $cantidad_total = 0;
      $i = 1;
      
      /*agrupar para obtener la cabecera*/
      foreach ($lista as $row) {
        $arrAuxCab = array(
          'codigo_pedido' => $row['codigo_pedido'],
          'idsolicitudformula' => $row['idsolicitudformula'],
          'fecha' => $row['fecha'],
          'hora' => $row['hora'],
          'cliente' => $row['paciente'],
          'codigo_jj' => $row['codigo_jj'],
          'medico' => $row['medico'],
          'costo_total' => 0,

          );
        $arrCabecera[$row['idmovimiento']] = $arrAuxCab;
        if($row['medico_nuevo'] == 'SI'){
          $arrIdMedicosNuevos[] = $row['idmedico'];
        }
        if($row['formula_nueva'] == 'SI'){
          $arrIdFormulasNuevas[] = $row['idmedicamento'];
        }
        $arrIdDetalle[]=$row['iddetallemovimiento']; // para poder marcar como descargado
      }
      // var_dump($arrIdDetalle); exit();
      if( !empty($arrIdDetalle) && ($this->sessionHospital['key_group'] == 'key_derma') ){
        $this->model_venta_farmacia->m_marcar_pedido_formula_descargado($arrIdDetalle);
      }

      $arrIdMedicosNuevos = array_unique($arrIdMedicosNuevos);
      $arrIdMedicosNuevos = array_values($arrIdMedicosNuevos);
      $arrIdFormulasNuevas = array_unique($arrIdFormulasNuevas);
      $arrIdFormulasNuevas = array_values($arrIdFormulasNuevas);
      foreach ($arrCabecera as $key => $rowPro) {
        $costo_total = 0;
        foreach ($lista as $row) {
          if($key == $row['idmovimiento']){
            $costo_total += $row['precio_costo'] * $row['cantidad'];
          }
        }
        $arrCabecera[$key]['costo_total'] = $costo_total;
      }
      
      foreach ($arrCabecera as $row) {
        // $total_detalle_costo = ($row['precio_costo'] * $row['cantidad']);
        array_push($arrListadoProd, 
          array(
            $i++,
            $row['codigo_pedido'],
            // ( formatoFechaReporte($row['fecha_movimiento'])),
            $row['fecha'],
            $row['hora'],
            strtoupper_total( empty($row['codigo_jj'])? 'D158' :  $row['codigo_jj'] ),  // D158 : LIBRE
            strtoupper_total($row['cliente']),
            //strtoupper_total($row['medico']),
            $row['costo_total'],

          )
        );
      }
      $i = 1;
      foreach ($lista as $key => $rowDet) {
        array_push($arrDetalle, 
          array(
            $i++,
            $rowDet['codigo_pedido'],
            $rowDet['idformula_jj'],
            $rowDet['cantidad'],
            $rowDet['precio_costo'],
            // strtoupper_total($rowDet['denominacion']),
            $rowDet['uso_jj'],
          )
        );
      }
      // DATOS PARA FORMULAS NUEVAS
      if (count($arrIdFormulasNuevas) > 0){
        $listaFormulas = $this->model_medicamento->m_cargar_formulas_por_arrId($arrIdFormulasNuevas);
        // var_dump($listaFormulas); exit();
        $i = 1;
        foreach ($listaFormulas as $row) {
          array_push($arrFormulasNuevas, 
            array(
              $i++,
              $row['idformula_jj'],
              strtoupper_total($row['denominacion']),
              $row['precio_compra'],
              $row['categoria_jj']
            )
          );
        }
      }
      // DATOS PARA MEDICOS NUEVOS
      if (count($arrIdMedicosNuevos) > 0){
        $listaMedicos = $this->model_empleado->m_cargar_medico_especialidad_por_arrId($arrIdMedicosNuevos);
        $i = 1;
        foreach ($listaMedicos as $row) {
          array_push($arrMedicosNuevos, 
            array(
              $i++,
              $row['codigo_jj'],
              strtoupper_total($row['medico']),
              $row['especialidad'],
              $row['colegiatura_profesional']
            )
          );
        }
      }
      // var_dump($listaMedicos); exit();
    // ESTILOS
        $styleArrayTitle = array(
          'font'=>  array(
            'bold'  => false,
            'size'  => 18,
            'name'  => 'calibri',
            'color' => array('rgb' => 'FFFFFF') 
          ),
          'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
          'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '00000000', ),
          ),
        );
        $styleArrayEncabezado = array(
          'font'=>  array(
              'bold'  => true,
              'size'  => 11,
              'name'  => 'calibri'
          ),
        );
        $styleArrayHeader = array(
          'borders' => array(
            'allborders' => array( 
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('rgb' => '000000') 
            ) 
          ),
          'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
          'font'=>  array(
              'bold'  => true,
              'size'  => 11,
              'name'  => 'calibri',
              'color' => array('rgb' => 'FFFFFF') 
          ),
          'fill' => array( 
              'type' => PHPExcel_Style_Fill::FILL_SOLID,
              'startcolor' => array( 'rgb' => '70AD47', ),
           ),
        );
        $styleArrayProd = array(
          'borders' => array(
            'allborders' => array( 
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('rgb' => '000000') 
            ) 
          ),
          'font'=>  array(
              'bold'  => false,
              'size'  => 10,
              'name'  => 'calibri',
              // 'color' => array('rgb' => '000000') 
          ),
        );
        $styleArrayTotales = array(
          'font'=>  array(
              'bold'  => true,
              'size'  => 16,
              'name'  => 'calibri'
          ),
        );
    /* HOJA 1: PEDIDO */
      // SETEO DE VARIABLES
      $dataColumnsTP = array( 
        '#','id_pedido', 'fecha', 'hora', 'destino','autorizado', 'total'  );
      $endColum = 'G';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle('pedido');
      
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(5,15,10,10,10,70,20);
      $i = 0;
      foreach(range('A',$endColum) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
      }
      
      //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
      
      // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 1; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
      // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    /* HOJA 2: DETALLE DE PEDIDOS */
      $this->excel->createSheet();
      $this->excel->setActiveSheetIndex(1);
      $this->excel->getActiveSheet()->setTitle('detalle');
      // SETEO DE VARIABLES
      $dataColumnsDet = array( 
        '#','id_pedido', 'cod_articulo', 'cantidad', 'precio','uso');
      $endColumDet = 'F';
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(5,15,12,10,10,20);
      $i = 0;
      foreach(range('A',$endColumDet) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
      }
      // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 1; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrDetalle) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumDet.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumDet.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColumDet.$currentCellTotal)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
      // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsDet, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrDetalle, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':'.$endColumDet .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    /* HOJA 3: FORMULAS NUEVAS */
      $this->excel->createSheet();
      $this->excel->setActiveSheetIndex(2);
      $this->excel->getActiveSheet()->setTitle('formulas nuevas');
      // SETEO DE VARIABLES
      $dataColumnsForm = array( 
        '#', 'CODIGO', 'DESCRIPCION', 'PRECIO', 'CATEGORIA');
      $endColumForm = 'E';
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(5,15,70,20,20);
      $i = 0;
      foreach(range('A',$endColumForm) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
      }
      // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 1; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrFormulasNuevas) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumForm.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumForm.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColumForm.$currentCellTotal)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
      // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsForm, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrFormulasNuevas, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':'.$endColumForm .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    /* HOJA 4: MEDICOS NUEVOS */
      $this->excel->createSheet();
      $this->excel->setActiveSheetIndex(3);
      $this->excel->getActiveSheet()->setTitle('medicos nuevos');
      // SETEO DE VARIABLES
      $dataColumnsMed = array( 
        '#', 'iddepartamento', 'Descripcion', 'Especialidad', 'Colegiatura');
      $endColumMed = 'E';
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(5,20,70,40,20);
      $i = 0;
      foreach(range('A',$endColumMed) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
      }
      // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 1; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrMedicosNuevos) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumMed.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColumMed.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColumMed.$currentCellTotal)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
      // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsMed, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrMedicosNuevos, null, 'A'.($currentCellEncabezado+1));
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':'.$endColumMed .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $pedido = 'pedido-' . $allInputs['hasta'] .'-'. $allInputs['hastaHora'] . $allInputs['hastaMinuto'];
    $pedido = 'villasalud';
    // $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $objWriter->save('assets/img/dinamic/excelTemporales/'.$pedido.'.xls'); 
    $arrData = array(
      // 'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/'.$pedido.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_formulas_recibidas_costo_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    // TRATAMIENTO DE DATOS
      $lista = $this->model_venta_farmacia->m_cargar_formulas_recibidas_desde_hasta($allInputs['filtro'],$allInputs['paginate']);
      // var_dump($lista); exit();
      $arrListadoProd = array();
      $costo_total = 0;
      //$monto_total = 0;
      $cantidad_total = 0;
      $i = 1;
      foreach ($lista as $row) {
        $total_detalle_costo = ($row['precio_costo'] * $row['cantidad']);
        array_push($arrListadoProd, 
          array(
            $i++,
            $row['idsolicitudformula'],
            $row['codigo_pedido'],
            // ( formatoFechaReporte($row['fecha_movimiento'])),
            DarFormatoDMY($row['fecha_venta']),
            DarFormatoDMY($row['fecha_recepcion']),
            $row['guia_remision'],
            strtoupper_total($row['paciente']),
            strtoupper_total($row['idformula_jj']),
            strtoupper_total($row['medicamento']),
            $row['cantidad'],
            $row['precio_costo'],
            //$row['precio_unitario'],
            number_format($total_detalle_costo,2),
            //$row['total_detalle'],
          )
        );
        $costo_total += $total_detalle_costo;
        //$monto_total += $row['total_detalle'];
        $cantidad_total += ($row['cantidad']);
      }
    // SETEO DE VARIABLES
    $dataColumnsTP = array( 
      '#','Nº SOLICITUD.','CODIGO PEDIDO', 'FECHA VENTA', 'FECHA RECEPCION', 'GUIA DE REMISION','PACIENTE','COD. FORMULA','FORMULA', 'CANT', 'P.U. COSTO', 'TOTAL COSTO'
    );
    $endColum = 'L';
    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['filtro']['desde'].' - '.$allInputs['filtro']['hasta']);
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(5,12,15,12,12,20,50,13,50,12,12,20);
    $i = 0;
    foreach(range('A',$endColum) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
    }
    
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');
    // ESTILOS
      $styleArrayTitle = array(
        'font'=>  array(
          'bold'  => false,
          'size'  => 18,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '00000000', ),
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
      );
      $styleArrayHeader = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri',
            'color' => array('rgb' => 'FFFFFF') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '70AD47', ),
         ),
      );
      $styleArrayProd = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'font'=>  array(
            'bold'  => false,
            'size'  => 10,
            'name'  => 'calibri',
            // 'color' => array('rgb' => '000000') 
        ),
      );
      $styleArrayTotales = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 16,
            'name'  => 'calibri'
        ),
      );
    // TITULO
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']. ' DEL '.strtr($allInputs['filtro']['desde'], '-', '/').' AL '.strtr($allInputs['filtro']['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
    $this->excel->getActiveSheet()->getStyle('B3:B7')->applyFromArray($styleArrayEncabezado);

    // $this->excel->getActiveSheet()->getCell('B3')->setValue('EMPRESA:');
    // $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['almacen']['empresa']);
    
    // $this->excel->getActiveSheet()->getCell('B4')->setValue('SEDE:');
    // $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['almacen']['sede']);
    
    $this->excel->getActiveSheet()->getCell('B6')->setValue('DESDE:');
    $this->excel->getActiveSheet()->getCell('C6')->setValue( $allInputs['filtro']['desde'] . ' | ' . $allInputs['filtro']['desdeHora'] . ':' . $allInputs['filtro']['desdeMinuto']  );

    $this->excel->getActiveSheet()->getCell('B7')->setValue('HASTA:');
    $this->excel->getActiveSheet()->getCell('C7')->setValue( $allInputs['filtro']['hasta'] . ' | ' . $allInputs['filtro']['hastaHora'] . ':' . $allInputs['filtro']['hastaMinuto']  );
    
    // ENCABEZADO DE LA LISTA
    $currentCellEncabezado = 9; // donde inicia el encabezado del listado
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
    // LISTADO
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('K'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':F' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('H'.($currentCellEncabezado+1).':H' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('J'.($currentCellEncabezado+1).':J' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getFill()->getStartColor()->setARGB('FFFFF2CC');

    // TOTAL
    $this->excel->getActiveSheet()->getStyle('I'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2)  )->applyFromArray($styleArrayTotales);
    $this->excel->getActiveSheet()->getCell('I'.($currentCellTotal+2) )->setValue('TOTAL');
    $this->excel->getActiveSheet()->getStyle('I'.($currentCellTotal+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $this->excel->getActiveSheet()->getStyle('J'.($currentCellTotal+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('L'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2) )->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
    $this->excel->getActiveSheet()->getCell('J'.($currentCellTotal+2))->setValue('=SUM(J'.($currentCellEncabezado+1) .':J'.($currentCellTotal+1).')');
    $this->excel->getActiveSheet()->getCell('L'.($currentCellTotal+2))->setValue('=SUM(L'.($currentCellEncabezado+1) .':L'.($currentCellTotal+1).')');

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }

  public function report_consumo_medicamentos_mes_anio(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;
    $dia=date("d",(mktime(0,0,0,str_pad($allInputs['mesHastaCbo'], 2, "0", STR_PAD_LEFT )+1,1,$allInputs['anioHastaCbo'])-1));
    $allInputs['desde'] = '01-'.str_pad($allInputs['mesDesdeCbo'], 2, "0", STR_PAD_LEFT ).'-'.$allInputs['anioDesdeCbo'];
    $allInputs['hasta'] = $dia.'-'.str_pad($allInputs['mesHastaCbo'], 2, "0", STR_PAD_LEFT ).'-'.$allInputs['anioHastaCbo'];
    $arrLista = $this->model_medicamento->m_cargar_venta_medicamento_mes_anio($allInputs);
    $arrMedicamentos = $this->model_medicamento->m_cargar_medicamento_reporte($allInputs);


    $listMonth = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");

    $arrMesesDesde = array();
    $contDesdeMes = (int)$allInputs['mesDesdeCbo'];
    while ( $contDesdeMes <= 12 ) {
      $arrMesesDesde[] = $contDesdeMes;
      $contDesdeMes++;
    }

    $arrMesesHasta = array();
    $contHastaMes = 1;
    while ( $contHastaMes <= (int)$allInputs['mesHastaCbo']) {
      $arrMesesHasta[] = $contHastaMes;
      $contHastaMes++;
    }

    $arrMesesTodo = array();
    $contMesTodo = 1;
    while ( $contMesTodo <= 12 ) {
      $arrMesesTodo[] = $contMesTodo;
      $contMesTodo++;
    }

    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesdeCbo'];
    while ( $contDesde <= $allInputs['anioHastaCbo'] ) {
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    
    $arrTable[0] = array(
      'medicamento'=> utf8_decode('MEDICAMENTO/MESES')
    );
    $arrAnosTitle = array();
    $valorCeroDefault = '0';
    $i=1;
    foreach ($arrAnos as $keyAno =>$rowAno) { 
      if ($rowAno == (int)$allInputs['anioDesdeCbo']) {
        $arrMeses = $arrMesesDesde;
      }elseif($rowAno == (int)$allInputs['anioHastaCbo']){
        $arrMeses = $arrMesesHasta;
      }else{
        $arrMeses = $arrMesesTodo;
      } 
      foreach ($arrMeses as $keyMes => $rowMes) { 
        $arrTable[0][$i] = strtoupper($listMonth[$rowMes]);
        $i++;
      }  
      $arrAnosTitle[$rowAno] = count($arrMeses);
    }
    $cantidad=$i;
    $i=1;
    foreach ($arrMedicamentos as $key => $rowMedic) { 
      $arrTable[$i]['medicamento'] = $rowMedic['denominacion'];
      $j=1;
      foreach ($arrAnos as $keyAno => $rowAno) { 

        if ($rowAno == (int)$allInputs['anioDesdeCbo']) {
          $arrMeses = $arrMesesDesde;
        }elseif($rowAno == (int)$allInputs['anioHastaCbo']){
          $arrMeses = $arrMesesHasta;
        }else{
          $arrMeses = $arrMesesTodo;
        }
        foreach ($arrMeses as $keyMes => $rowMes) {   
          $boolNoData = FALSE;
          
          foreach ($arrLista as $key => $row) { 
            if( $row['mes'] == $rowMes && $row['anio'] == $rowAno 
              && $row['idmedicamento'] == $rowMedic['idmedicamento']) {               
              $arrTable[$i][$j]['cantidad'] = $row['cantidad']; 
              $arrTable[$i][$j]['monto'] = $row['monto']; 
              $boolNoData = TRUE;
            }
          }
          if( !($boolNoData) ){ 
              $arrTable[$i][$j]['cantidad'] = $valorCeroDefault; 
              $arrTable[$i][$j]['monto'] = $valorCeroDefault;
          }
          $j++;
        }
      }
      $arrTable[$i][$j] = $rowMedic['stock_actual_total'];
      $i++;
    } 

    $arrListadoProd = array();
    $dataColumnsTP = array();
    $dataColumnsTP2 = array();

    foreach ($arrTable as $key =>$row) { 
      $linea = array();
      $linea2 = array();
      foreach ($row as $key1 => $row1) { 
        if($key != 0){
          if($key1 == 'medicamento' || $key1 == $cantidad){
            array_push($linea, $row1);            
          }else{
            array_push($linea, $row1['cantidad']);
            array_push($linea, $row1['monto']);
          }
        }else{
          array_push($linea, $row1);
          if($key1 == 'medicamento'){            
            array_push($linea2, 'MEDICAMENTOS');             
          }else{
            array_push($linea2, 'CANTIDAD');
            array_push($linea2, 'MONTO');  
          }
        }        
      }  
      if($key == 0 && $key1 ==  $cantidad-1){ array_push($linea2, 'STOCK ACTUAL'); }
      if($key != 0){  array_push($arrListadoProd, $linea); 
      }else{ array_push($dataColumnsTP, $linea); array_push($dataColumnsTP2, $linea2);}
      
    }

    $dataColumnsTP = array_slice($dataColumnsTP[0], 1); 
    $dataColumnsTP2 = $dataColumnsTP2[0];
    /*var_dump($dataColumnsTP);
    var_dump($dataColumnsTP2);
    var_dump($arrListadoProd);
    exit();*/
    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
    //$this->excel->getActiveSheet()->setAutoFilter('B3:M3');

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 11,
          'name'  => 'Verdana'
      )
    );
    //INSERTAR TITULO
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:G1');

    $this->excel->getActiveSheet()->getCell('A2')->setValue('FECHA: '.strtr($allInputs['desde'], '-', '/').' - '.strtr($allInputs['hasta'], '-', '/')); 
    $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A2:G2');

    $this->excel->getActiveSheet()->getCell('A3')->setValue('SEDE: '.$allInputs['sede']['descripcion']); 
    $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A3:G3');

    if($allInputs['laboratorio']['id'] != 0){
      $this->excel->getActiveSheet()->getCell('A4')->setValue('LABORATORIO: '.$allInputs['laboratorio']['descripcion']); 
      $this->excel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A4:G4');
    }

    if($allInputs['tipoProducto']['id'] != 0){
      $this->excel->getActiveSheet()->getCell('A5')->setValue('TIPO PRODUCTO: '.$allInputs['tipoProducto']['descripcion']); 
      $this->excel->getActiveSheet()->getStyle('A5')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A5:G5');
    }

      $styleArrayHeader = array(
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'font'=>  array(
            'bold'  => true,
            'size'  => 10,
            'name'  => 'calibri',
            'color' => array('rgb' => 'FFFFFF') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '328db7',  ),
         ),
      );
    
     //INSERTAR AÑOS
    $row = 7;
    $j = 'B';
    foreach($arrAnosTitle as $key=>$value) { 
        $value = ($value*2) -2;
        $i = $j;           
        $this->excel->getActiveSheet()->getCell($i.$row)->setValue($key); 
        $this->excel->getActiveSheet()->getStyle($i.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    
        for ($n=0; $n <= $value ; $n++) { 
          $j++;
        }
        $this->excel->getActiveSheet()->mergeCells($i.$row.':'.$j.$row);
        if($key < $allInputs['anioHastaCbo'])
          $j++;        
    }

    $this->excel->getActiveSheet()->getStyle('B7:'.$j.'7')->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('B8:'.$j.'8')->applyFromArray($styleArrayHeader);
    $j++;
    $this->excel->getActiveSheet()->getStyle('A9:'.$j.'9')->applyFromArray($styleArrayHeader);
    foreach(range('A',$j++) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }

    //INSERTAR MESE
    $row = 8;
    $j = 'B';
    foreach($dataColumnsTP as $key=>$value) {  
        $i = $j++; 
        $this->excel->getActiveSheet()->getCell($i.$row)->setValue($value);    
        $this->excel->getActiveSheet()->getStyle($i.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    
        $this->excel->getActiveSheet()->mergeCells($i.$row.':'.$j.$row);
        $j++;
    }

    //INSERTAR CABECERA CANTIDAD MONTO
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP2, null, 'A9');
    //INSERTAR DATOS
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A10');
    $row = 9;
    foreach ($arrListadoProd as $key1 => $value1) {
       $j = 'B';
      foreach ($dataColumnsTP2 as $key2 => $value2) {
        $this->excel->getActiveSheet()->getStyle($j.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    
        $j++;  
        $this->excel->getActiveSheet()->getStyle($j.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);    
        $j++;      
      }
      $row++;
    }
   // exit();
    $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');
 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
      'flag'=> 1
    );

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($arrData));

  }

  public function report_ventas_usuario_caja(){
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;
    $lista = $this->model_caja_farmacia->m_cargar_ventas_usuario_caja($allInputs);

    // TRATAMIENTO DE DATOS
    $arrayTabla=array();

    foreach ($lista as $key => $value) {
      array_push($arrayTabla, 
        array(
          'empleado' => $value['empleado'],
          'id' => $value['idmovimiento'],
          'fecha' => darFormatoDMY($value['fecha_movimiento']),
          'hora' => darFormatoHora2($value['fecha_movimiento']),
          'caja' => $value['numero_caja'],
          'tipo_documento' => $value['descripcion_td'],
          'idtipo_documento' => $value['idtipodocumento'],
          'turno' => (strtotime(darFormatoHora2($value['fecha_movimiento'])) < strtotime('13:00:00')) ? 'MAÑANA' : 'TARDE',
          'total_a_pagar' => $value['total_a_pagar'],
          'monto' => $value['monto']         
        ));
    }

    /*var_dump($arrayTabla);
    exit();*/


    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 11,
          'name'  => 'Verdana'
      )
    );
    // DATOS DE LA CABECERA

    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:G1');

    if ($allInputs['empresaAdmin']['id'] != 0) {
      $this->excel->getActiveSheet()->getCell('A2')->setValue('EMPRESA: '.$allInputs['empresaAdmin']['descripcion']);
      $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A2:G2');

      $this->excel->getActiveSheet()->getCell('A3')->setValue('DESDE: '.$allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A3:G3');

      $this->excel->getActiveSheet()->getCell('A4')->setValue('HASTA: '.$allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );
      $this->excel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A4:G4');
    }else{

      $this->excel->getActiveSheet()->getCell('A2')->setValue('DESDE: '.$allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A2:G2');

      $this->excel->getActiveSheet()->getCell('A3')->setValue('HASTA: '.$allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );
      $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A3:G3');
    }
    

    $arrEncabezado = array();
    $arrListadoProd = array();
    $arrEncabezado = array('ID','FECHA','HORA','CAJA','TIPO DE DOCUMENTO','N° DE DOCUMENTO','TURNO','MONTO'); 
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '328db7',  ),
       ),
    );

    $styleArray = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'font'=>  array(
          'size'  => 10,
          'name'  => 'calibri'
      ),
    );

    $row=8; $total = 0;
    foreach ($arrayTabla as $key => $value) {
      $arrRow=array();
      if($key == 0){
        $this->excel->getActiveSheet()->getCell('A7')->setValue($value['empleado']);
        $this->excel->getActiveSheet()->mergeCells('A7:D7');
        $this->excel->getActiveSheet()->getStyle('A7:D7')->applyFromArray($styleArrayHeader);
        $this->excel->getActiveSheet()->fromArray($arrEncabezado, null, 'A8');        
        $this->excel->getActiveSheet()->getStyle('A8:H8')->applyFromArray($styleArrayHeader);    
      }elseif($value['empleado'] != $arrayTabla[$key-1]['empleado']){
        //total
        $row++;
        $this->excel->getActiveSheet()->getCell('G'.$row)->setValue('TOTAL');
        $this->excel->getActiveSheet()->getCell('H'.$row)->setValue('S/. '.$total);
        $this->excel->getActiveSheet()->getStyle('G'.$row.':H'.$row)->applyFromArray($styleArray);
        $this->excel->getActiveSheet()->getStyle('G'.$row.':H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $total = 0;  $row+=2;
        //Siguiente cajero
        $this->excel->getActiveSheet()->getCell('A'.$row)->setValue($value['empleado']);
        $this->excel->getActiveSheet()->mergeCells('A'.$row.':D'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':D'.$row)->applyFromArray($styleArrayHeader);
        $row++;
        $this->excel->getActiveSheet()->fromArray($arrEncabezado, null, 'A'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray($styleArrayHeader);
      }else{
        if($allInputs['turno']['id'] != 0 && $allInputs['turno']['descripcion'] == $value['turno']){
          array_push($arrRow, 
            array(
              $value['id'],
              $value['fecha'],
              $value['hora'],
              $value['caja'],
              $value['tipo_documento'],
              $value['idtipo_documento'],
              $value['turno'],
              $value['total_a_pagar']         
            ));
          $total+=$value['monto']; $row++;
        }elseif($allInputs['turno']['id'] == 0){
          array_push($arrRow, 
            array(
              $value['id'],
              $value['fecha'],
              $value['hora'],
              $value['caja'],
              $value['tipo_documento'],
              $value['idtipo_documento'],
              $value['turno'],
              $value['total_a_pagar']         
            ));
          $total+=$value['monto']; $row++;
        }
        
        $this->excel->getActiveSheet()->fromArray($arrRow, null, 'A'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray($styleArray);
        $this->excel->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->excel->getActiveSheet()->getStyle('B'.$row.':G'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }  
    }
    $row++;
    $this->excel->getActiveSheet()->getCell('G'.$row)->setValue('TOTAL');
    $this->excel->getActiveSheet()->getCell('H'.$row)->setValue('S/. '.$total); 
    $this->excel->getActiveSheet()->getStyle('G'.$row.':H'.$row)->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->getStyle('G'.$row.':H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

    $arrWidths = array(10,15,15,8,20,20,15,15);
    $i=0;
    foreach(range('A','H') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
    }
     

    $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');


    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
      'flag'=> 1
    );

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($arrData));

  }

  public function report_ventas_usuario_caja_detalle(){
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;
    $lista = $this->model_caja_farmacia->m_cargar_ventas_usuario_caja_detalle($allInputs);

    // TRATAMIENTO DE DATOS
    $arrayTabla=array();

    foreach ($lista as $key => $value) {
      array_push($arrayTabla, 
        array(
          'empleado' => $value['empleado'],
          'id' => $value['idmovimiento'],
          'fecha' => darFormatoDMY($value['fecha_movimiento']),
          'hora' => darFormatoHora2($value['fecha_movimiento']),
          'caja' => $value['numero_caja'],
          'tipo_documento' => $value['descripcion_td'],
          'idtipo_documento' => $value['idtipodocumento'],
          'denominacion' => $value['denominacion'],
          'nombre_lab' => $value['nombre_lab'],
          'turno' => (strtotime(darFormatoHora2($value['fecha_movimiento'])) < strtotime('13:00:00')) ? 'MAÑANA' : 'TARDE',
          'total_detalle' => $value['total_detalle'],
          'monto' => $value['monto']         
        ));
    }

    /*var_dump($arrayTabla);
    exit();*/


    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 

    $styleArrayTitle = array(
      'font'=>  array(
          'bold'  => true,
          'size'  => 11,
          'name'  => 'Verdana'
      )
    );
    // DATOS DE LA CABECERA

    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:G1');

    if ($allInputs['empresaAdmin']['id'] != 0) {
      $this->excel->getActiveSheet()->getCell('A2')->setValue('EMPRESA: '.$allInputs['empresaAdmin']['descripcion']);
      $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A2:G2');

      $this->excel->getActiveSheet()->getCell('A3')->setValue('DESDE: '.$allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A3:G3');

      $this->excel->getActiveSheet()->getCell('A4')->setValue('HASTA: '.$allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );
      $this->excel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A4:G4');
    }else{

      $this->excel->getActiveSheet()->getCell('A2')->setValue('DESDE: '.$allInputs['desde'] . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A2:G2');

      $this->excel->getActiveSheet()->getCell('A3')->setValue('HASTA: '.$allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto']  );
      $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A3:G3');
    }
    

    $arrEncabezado = array();
    $arrListadoProd = array();
    $arrEncabezado = array('ID','FECHA','HORA','CAJA','TIPO DE DOCUMENTO','N° DE DOCUMENTO','TURNO','MEDICAMENTO','LABORATORIO','MONTO'); 
    $styleArrayHeader = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '328db7',  ),
       ),
    );

    $styleArray = array(
      'borders' => array(
        'allborders' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000') 
        ) 
      ),
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
      'font'=>  array(
          'size'  => 10,
          'name'  => 'calibri'
      ),
    );

    $row = 9; $total = 0;  $id = 0; $fin = count($arrayTabla);
    foreach ($arrayTabla as $key => $value) {
      $arrRow=array();
      if($key == 0){
        $this->excel->getActiveSheet()->getCell('A7')->setValue($value['empleado']);
        $this->excel->getActiveSheet()->mergeCells('A7:D7');
        $this->excel->getActiveSheet()->getStyle('A7:D7')->applyFromArray($styleArrayHeader);
        $this->excel->getActiveSheet()->fromArray($arrEncabezado, null, 'A8');        
        $this->excel->getActiveSheet()->getStyle('A8:J8')->applyFromArray($styleArrayHeader);    
      }elseif($value['empleado'] != $arrayTabla[$key-1]['empleado']){
        //total
        $this->excel->getActiveSheet()->getCell('I'.$row)->setValue('TOTAL');
        $this->excel->getActiveSheet()->getCell('J'.$row)->setValue('S/. '.$total);
        $this->excel->getActiveSheet()->getStyle('I'.$row.':J'.$row)->applyFromArray($styleArray);
        $this->excel->getActiveSheet()->getStyle('I'.$row.':J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $total = 0;  $row+=2;
        //Siguiente cajero
        $this->excel->getActiveSheet()->getCell('A'.$row)->setValue($value['empleado']);
        $this->excel->getActiveSheet()->mergeCells('A'.$row.':D'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':D'.$row)->applyFromArray($styleArrayHeader);
        $row++;
        $this->excel->getActiveSheet()->fromArray($arrEncabezado, null, 'A'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->applyFromArray($styleArrayHeader);
        $row++;
      }else{
        if($allInputs['turno']['id'] != 0 && $allInputs['turno']['descripcion'] == $value['turno']){
          
          array_push($arrRow, 
            array(
              $value['id'], $value['fecha'], $value['hora'], $value['caja'], $value['tipo_documento'],
              $value['idtipo_documento'], $value['turno'], $value['denominacion'], $value['nombre_lab'], 
              $value['total_detalle']         
            ));
          $total+=$value['monto']; 
        }elseif($allInputs['turno']['id'] == 0){
          array_push($arrRow, 
            array(
              $value['id'], $value['fecha'], $value['hora'], $value['caja'], $value['tipo_documento'],
              $value['idtipo_documento'], $value['turno'], $value['denominacion'], $value['nombre_lab'],
              $value['total_detalle']                 
            ));
          $total+=$value['monto']; 
        }
        if($key < $fin-1){


          if($value['id'] != $arrayTabla[$key-1]['id'] && $value['id'] == $arrayTabla[$key+1]['id']) {
            $id++;
          }elseif($value['id'] == $arrayTabla[$key-1]['id'] && $value['id'] == $arrayTabla[$key+1]['id']){
            $id++;
          }elseif($value['id'] == $arrayTabla[$key-1]['id'] && $value['id'] != $arrayTabla[$key+1]['id']){
           // var_dump($row, $row-$id);
            $this->excel->getActiveSheet()->mergeCells('A'.($row-$id).':A'.$row);
            $this->excel->getActiveSheet()->mergeCells('B'.($row-$id).':B'.$row);
            $this->excel->getActiveSheet()->mergeCells('C'.($row-$id).':C'.$row);
            $this->excel->getActiveSheet()->mergeCells('D'.($row-$id).':D'.$row);
            $this->excel->getActiveSheet()->mergeCells('E'.($row-$id).':E'.$row);
            $this->excel->getActiveSheet()->mergeCells('F'.($row-$id).':F'.$row);
            $this->excel->getActiveSheet()->mergeCells('G'.($row-$id).':G'.$row);
            $id=0;
          }
        }
        $this->excel->getActiveSheet()->fromArray($arrRow, null, 'A'.$row);
        $this->excel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->applyFromArray($styleArray);
        $this->excel->getActiveSheet()->getStyle('H'.$row.':I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $this->excel->getActiveSheet()->getStyle('J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $row++;
      }  
    }

    $this->excel->getActiveSheet()->getCell('I'.$row)->setValue('TOTAL');
    $this->excel->getActiveSheet()->getCell('J'.$row)->setValue('S/. '.$total); 
    $this->excel->getActiveSheet()->getStyle('I'.$row.':J'.$row)->applyFromArray($styleArray);
    $this->excel->getActiveSheet()->getStyle('I'.$row.':J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

    $arrWidths = array(10,15,15,8,20,20,16,55,30,16);
    $i=0;
    foreach(range('A','J') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
    }
     

    $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');


    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
      'flag'=> 1
    );

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($arrData));

  }
}