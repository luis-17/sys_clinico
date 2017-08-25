<?php
defined('BASEPATH') OR exit('No direct script access allowed'); 

require_once APPPATH.'third_party/spout242/src/Spout/Autoloader/autoload.php';

//lets Use the Spout Namespaces 
use Box\Spout\Writer\WriterFactory; 
use Box\Spout\Common\Type; 
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

class CentralReportes extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->helper(array('security','reportes_helper','imagen_helper','fechas_helper','otros_helper','contable'));
    $this->load->model(array('model_config','model_atencion_medica','model_caja','model_venta',
                              'model_producto','model_empresa_admin','model_empresa','model_empleado','model_asistencia',
                              'model_feriado','model_horario_especial','model_horario_general','model_solicitud_procedimiento',
                              'model_solicitud_examen','model_especialidad', 'model_campania', 'model_prog_medico',
                              'model_planilla','model_empleado_planilla','model_resultadoAnalisis',
                              'model_usuario'));


    $this->load->library('excel');
    //cache 
    $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
    $this->output->set_header("Pragma: no-cache");

    $this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
    date_default_timezone_set("America/Lima");
    //if(!@$this->user) redirect ('inicio/login');
    //$permisos = cargar_permisos_del_usuario($this->user->idusuario);
  } 
  public function ver_popup_reporte()
  {
    $this->load->view('centralReporte/popup_reporte');
  }
  public function ver_popup_grafico()
  {
    $this->load->view('centralReporte/popup_grafico');
  }
  public function guardar_pdf_en_temporal()
  {
    $data = substr($_POST['data'], strpos($_POST['data'], ",") + 1); 
    $decodedData = base64_decode($data);
    // subir_fichero();
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    if(file_put_contents('assets/img/dinamic/pdfTemporales/tempPDF_'.date('YmdHis').'.pdf', $decodedData)){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'.date('YmdHis').'.pdf'
    );
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_detalle_por_venta_caja_excel()
  {
    // $file="demo.xls";
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // var_dump($allInputs); exit(); 
    $listaCajas = $this->model_caja->m_cargar_cajas_de_dia_usuario($allInputs);
    $arrContent = array();
    $cont = 0;
    foreach ($listaCajas as $key => $value) { 
      $listaVentasDeCaja = $this->model_venta->m_cargar_ventas_esta_caja($value); 
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
      //var_dump($arrSoloMediosPago); exit(); 
      //var_dump($arrSoloMediosPago); exit(); 
      foreach ($listaVentasDeCaja as $row) { 
        $strFechaVenta = $row['fecha_venta'];
        if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' ){ // VENTAS  
          array_push($arrListadoProd, 
            array( 
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['orden_venta'],
              $row['ticket_venta'],
              strtoupper($row['descripcion_td']),
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              strtoupper($row['descripcion_med']),
              //($row['tipofila'] == 'a' ? '0' : $row['total_a_pagar']),
              $row['total_a_pagar'],
              ($row['tipofila'] == 'a' ? 'ANULADO' : ' ')
            )
          );
          if( $row['tipofila'] == 'v' ){
            $sumTotalVenta += $row['total_a_pagar'];
          }
          // if( $row['tipofila'] == 'a' ){ 
          //   $countAnulados++;
          // }
        }
        if( $row['tipofila'] == 'nc' ){ 
          $sumTotalNCR += $row['total_a_pagar'];
          $countNCR++;
          array_push($arrListadoNCR, 
            array(
              // date('d/m/Y',strtotime($strFechaVenta)),
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['ticket_venta'],
              $row['especialidad'],
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              $row['orden_venta'],
              $row['total_a_pagar']
            )
          );
        }
      }

      //activate worksheet number 1
      $this->excel->setActiveSheetIndex($cont);
      
      //name the worksheet
      $this->excel->getActiveSheet()->setTitle('DETALLE DE CAJAS ('.$cont.')');
      $this->excel->getActiveSheet()->setAutoFilter('B3:I3');

      $styleArrayTitle = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 14,
            'name'  => 'Verdana'
        )
      );

      $this->excel->getActiveSheet()->getCell('F1')->setValue('DETALLE DE CAJAS D');
      $this->excel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArrayTitle);

      $cont++;

      $dataColumnsTP = array( 
        array('HORA', 'N° DE ORDEN', 'TICKET', 'TIPO DOCUMENTO', 'PACIENTE', 'MEDIO DE PAGO', 'MONTO', 'ESTADO')
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
            // 'color' => array('rgb' => 'FF0000'),
            'size'  => 10,
            'name'  => 'Verdana'
        )
      );
      foreach(range('B','Z') as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
      }
      $this->excel->getActiveSheet()->getStyle('B3:I3')->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'B3');
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'B4');
      $currentCellTotal = count($arrListadoProd) + 5;
      $currentCellNCR = count($arrListadoProd) + 10;

      $styleTitleNCR = array(
          'font' => array(
              'bold'  => true,
              'size'  => 14,
          )
      );
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellNCR.':G'.$currentCellNCR)->applyFromArray($styleTitleNCR);
      $dataColumnsNCR = array( 
        array('HORA', 'TICKET', 'ESPECIALIDAD', 'PACIENTE', 'ORDEN', 'MONTO')
      );
      $this->excel->getActiveSheet()->getCell('B'.$currentCellNCR)->setValue('NOTAS DE CREDITO');
      if( empty($arrListadoNCR) ) {
        $arrListadoNCR = array(
          array('No se encontró notas de crédito.')
        );
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
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellTotal + 1).':H'.($currentCellTotal + 3))->applyFromArray($styleArrayTotales);
      $arrTotalesFooter = array(
        array('CANT. VENTAS', $countVentas),
        array('CANT. ANULADOS', $countAnulados),
        array('TOTAL VENTAS', empty($sumTotalVenta) ? '0.00' : number_format($sumTotalVenta,2) )
      ); 
      $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'G'.($currentCellTotal + 1));

      $this->excel->getActiveSheet()->getStyle('B'.($currentCellNCR + 1).':G'.($currentCellNCR + 1))->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsNCR, null, 'B'.($currentCellNCR + 1));
      $this->excel->getActiveSheet()->fromArray($arrListadoNCR, null, 'B'.($currentCellNCR + 2));
      
      $currentCellTotalesNCR = $currentCellTotal + count($arrTotalesFooter) + count($arrListadoNCR) + 2; 
      $arrTotalesNCRFooter = array(
        array('CANT. N.CREDITO', $countNCR ),
        array('TOTAL N.CREDITO', empty($sumTotalNCR) ? '0.00' : number_format($sumTotalNCR,2))
      ); 
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellTotalesNCR + 2).':H'.($currentCellTotalesNCR + 3))->applyFromArray($styleArrayTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesNCRFooter, null, 'G'.($currentCellTotalesNCR + 2));

      $arrTotalesSumFooter = array(
        array('TOTAL EN CAJA: ', number_format($sumTotalVenta+$sumTotalNCR,2) )
      ); 
      $styleArraySumTotales = array(
        'font'=>  array(
          'bold'  => true,
          'size'  => 16
        )
      );
      $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotalesNCR + 5).':G'.($currentCellTotalesNCR + 5))->applyFromArray($styleArraySumTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesSumFooter, null, 'F'.($currentCellTotalesNCR + 5));

    }

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
  public function report_detalle_por_venta_caja_fechas_excel()
  {
    ini_set('max_execution_time', 600); /* IMPORTANTE */
    ini_set('memory_limit','3G'); /* IMPORTANTE */
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    $arrContent = array();
    $cont = 0;
    //foreach ($listaCajas as $key => $value) { 
      $listaVentasDeCaja = $this->model_venta->m_cargar_ventas_desde_hasta($allInputs); 
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
        $strFechaVenta = $row['fecha_venta'];
        if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' ){ // VENTAS  
          array_push($arrListadoProd, 
            array( 
              date('d/m/Y',strtotime($strFechaVenta)),
              strtoupper($row['descripcion_td']),
              substr($row['ticket_venta'], 0, 3),
              substr($row['ticket_venta'], -9),
              $row['dniruc'],
              strtoupper($row['paciente']),
              ($row['tipofila'] == 'a' ? '0' : $row['sub_total']),
              ($row['tipofila'] == 'a' ? '0' : $row['total_igv']),
              ($row['tipofila'] == 'a' ? '0' : $row['total_a_pagar']),
              strtoupper($row['descripcion_med']),
              ($row['tipofila'] == 'a' ? 'ANULADO' : ' '), 
              strtoupper($row['especialidad']),
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
        if( $row['tipofila'] == 'nc' ){ 
          $sumTotalNCR += $row['total_a_pagar'];
          $countNCR++;
          array_push($arrListadoNCR, 
            array(
              date('d/m/Y',strtotime($strFechaVenta)),
              strtoupper($row['descripcion_td']),
              // date('h:i:s a',strtotime($strFechaVenta)),
              substr($row['ticket_venta'], 0, 3),
              substr($row['ticket_venta'], -9),
              $row['dniruc'],
              strtoupper($row['paciente']),
              
              $row['total_a_pagar'],
              strtoupper($row['especialidad']),
              strtoupper($row['username'])
            )
          );
        }
      }
      $this->excel->setActiveSheetIndex($cont);
      $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']); 
      $this->excel->getActiveSheet()->setAutoFilter('B3:N3');

      $styleArrayTitle = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 14,
            'name'  => 'Verdana'
        )
      );

      $this->excel->getActiveSheet()->getCell('F1')->setValue('DETALLE DE CAJAS '.$allInputs['desde'].' - '.$allInputs['hasta']); 
      $this->excel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArrayTitle);

      $cont++;

      $dataColumnsTP = array( 
        array('FECHA', 'TIPO DOCUMENTO', 'SERIE', 'NUMERO DE COMPROBANTE', 'DNI O RUC', 'NOMBRES Y APELLIDOS / RAZON SOCIAL', 'SUB TOTAL', 'IGV', 'TOTAL', 'MEDIO DE PAGO', 'ESTADO', 'ESPECIALIDAD','CAJERA(USUARIO)')
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
      foreach(range('B','Z') as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
      }
      
      $this->excel->getActiveSheet()->getStyle('B3:N3')->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'B3');
      // var_dump($arrListadoProd); exit(); 
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'B4');
      // var_dump($arrListadoProd);
      $currentCellTotal = count($arrListadoProd) + 5;
      $currentCellNCR = count($arrListadoProd) + 10;

      $styleTitleNCR = array(
          'font' => array(
              'bold'  => true,
              'size'  => 14,
          )
      );
      
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellNCR.':J'.$currentCellNCR)->applyFromArray($styleTitleNCR);
      $dataColumnsNCR = array( 
        array('FECHA', 'TIPO DOCUMENTO', 'SERIE', 'NUMERO DE COMPROBANTE', 'DNI O RUC', 'NOMBRES Y APELLIDOS / RAZON SOCIAL', 'MONTO', 'ESPECIALIDAD','CAJERA(USUARIO)')
      );
      $this->excel->getActiveSheet()->getCell('B'.$currentCellNCR)->setValue('NOTAS DE CREDITO');
      if( empty($arrListadoNCR) ) {
        $arrListadoNCR = array(
          array('No se encontró notas de crédito.')
        );
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
      $this->excel->getActiveSheet()->getStyle('H'.($currentCellTotal + 1).':I'.($currentCellTotal + 3))->applyFromArray($styleArrayTotales);
      $arrTotalesFooter = array(
        array('CANT. VENTAS', $countVentas),
        array('CANT. ANULADOS', $countAnulados),
        array('TOTAL VENTAS', empty($sumTotalVenta) ? '0.00' : number_format($sumTotalVenta,2) )
      ); 
      $this->excel->getActiveSheet()->fromArray($arrTotalesFooter, null, 'G'.($currentCellTotal + 1));

      $this->excel->getActiveSheet()->getStyle('B'.($currentCellNCR + 1).':G'.($currentCellNCR + 1))->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsNCR, null, 'B'.($currentCellNCR + 1));
      $this->excel->getActiveSheet()->fromArray($arrListadoNCR, null, 'B'.($currentCellNCR + 2));
      
      $currentCellTotalesNCR = $currentCellTotal + count($arrTotalesFooter) + count($arrListadoNCR) + 2; 
      $arrTotalesNCRFooter = array(
        array('CANT. N.CREDITO', $countNCR ),
        array('TOTAL N.CREDITO', empty($sumTotalNCR) ? '0.00' : number_format($sumTotalNCR,2))
      ); 
      $this->excel->getActiveSheet()->getStyle('H'.($currentCellTotalesNCR + 2).':I'.($currentCellTotalesNCR + 3))->applyFromArray($styleArrayTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesNCRFooter, null, 'G'.($currentCellTotalesNCR + 2));

      $arrTotalesSumFooter = array( 
        array('TOTAL EN CAJA: ', number_format($sumTotalVenta+$sumTotalNCR,2) ) 
      ); 
      $styleArraySumTotales = array( 
        'font'=>  array( 
          'bold'  => true, 
          'size'  => 16 
        ) 
      ); 
      $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotalesNCR + 5).':G'.($currentCellTotalesNCR + 5))->applyFromArray($styleArraySumTotales);
      $this->excel->getActiveSheet()->fromArray($arrTotalesSumFooter, null, 'F'.($currentCellTotalesNCR + 5));

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
  public function report_listado_atenciones()  
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    // $z = new XMLReader;
    // var_dump($z); exit();
    ini_set('max_execution_time', 600); 
    ini_set('memory_limit','2G'); 
    $writer = WriterFactory::create(Type::XLSX); 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xlsx'; 
    $writer->openToFile($filePath); 
    

    $singleRow = array('ACTO MÉDICO','COD. ORDEN','TICKET','FECHA ATENCIÓN', 'FECHA VENTA','IDHISTORIA','PACIENTE','IDESPECIALIDAD','ESPECIALIDAD', 
      'TIPO ESPECIALIDAD', 'IDEMPRESA','EMPRESA', 'IDCAMPAÑA', 'CAMPAÑA', 'IDPAQUETE', 'PAQUETE', 'MONTO PAQUETE', 'IDPRODUCTO', 'PRODUCTO','TIPO PRODUCTO', 'IMPORTE', 'PERSONAL DE SALUD', 'SEDE'); 
    $writer->addRow($singleRow); 
    
    $arrData['flag'] = 0; 
    $arrContent = array();
    $cont = 0; 
    $lista = $this->model_atencion_medica->m_cargar_atenciones_reporte($allInputs); 
    //var_dump($arrSoloMediosPago); exit(); 
    foreach ($lista as $row) { 
      $strFechaVenta = $row['fecha_venta'];
      $strFechaAtencion = $row['fecha_atencion'];
      $writer->addRow( 
        array( 
          $row['idatencionmedica'],
          $row['orden_venta'],
          $row['ticket_venta'],
          date('d/m/Y H:i:s a',strtotime($strFechaAtencion)),
          date('d/m/Y H:i:s a',strtotime($strFechaVenta)),
          $row['idhistoria'],
          strtoupper($row['paciente']),
          $row['idespecialidad'],
          $row['especialidad'],
          $row['tipo_especialidad'],
          $row['idempresa'],
          $row['empresa'],
          $row['idcampania'],
          $row['campania'],
          $row['idpaquete'],
          $row['paquete'],
          $row['total_paquete'],
          $row['idproductomaster'],
          $row['producto'],
          $row['nombre_tp'],
          $row['total_detalle'],
          $row['medicoatencion'],
          $row['sede'] 
        ) 
      ); 
    } 
    $arrData = array(
      'urlTempEXCEL'=> $filePath,
      'flag'=> 1
    ); 
    $writer->close();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
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
  public function report_resumen_cajas()
  { 
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    /* SET VAR */
    $allInputs['tipodocumento'] = null;
    /* DATA RESUMEN DE CAJA */
    $lista = $this->model_caja->m_cargar_apertura_caja(FALSE,$allInputs);
    $totalRows = $this->model_caja->m_count_sum_apertura_caja(FALSE,$allInputs);
    
    $arrListado = array();
    $sumTotalVenta = 0;
    $sumNotasCredito = 0;
    foreach ($lista as $row) { 
      $sumNotasCredito += ($row['suma_nota_credito'] + $row['suma_extorno']);
      $sumTotalVenta += $row['total_venta'];
      $sumaTotalVenta = $row['total_venta'];
      $strFechaAperturaCaja = $row['fecha_apertura'];
      $strFechaCierreCaja = $row['fecha_cierre']; 
      // var_dump("<pre>",$row['suma_nota_credito'],$row['suma_extorno'],$row['suma_nota_credito'] + $row['suma_extorno']); exit(); 
      if( !empty($row['suma_nota_credito']) || !empty($row['suma_extorno']) ){ 
        $sumaTotalVenta = $row['total_venta'] + $row['suma_nota_credito'] + $row['suma_extorno']; 
      }
      $rowCantidadNotaCredito = ($row['cantidad_nota_credito'] + $row['cantidad_extorno'] > 0 ? $row['cantidad_nota_credito'] + $row['cantidad_extorno'] : '-' ); 
      // var_dump("<pre>",$row['cantidad_nota_credito'],$row['cantidad_extorno']); exit(); 
      array_push($arrListado, 
        array(
          date('d/m/Y',strtotime($strFechaAperturaCaja)),
          'Caja N° '.$row['numero_caja'],
          strtoupper($row['username']),
          date('h:i:s a',strtotime($strFechaAperturaCaja)),
          date('h:i:s a',strtotime($strFechaCierreCaja)),
          array('text'=>(empty($row['cantidad_anulado']) ? '-' : $row['cantidad_anulado']),'alignment'=>'center'),
          array('text'=> (string)$rowCantidadNotaCredito ,'alignment'=>'center'),
          number_format($sumaTotalVenta,2)
          //$row['cantidad_venta'],
          
        )
      );
    }
    /* DATA NOTA CREDITO COMPARACION */ 
    $arrListadoNCR = array( 
      array(
          array('text'=>'NOTA DE CREDITO','style'=>'tableHeader'), 
          array('text'=>number_format($sumNotasCredito,2),'style'=>'tableHeader'), 
      ),

      array(
          array('text'=>'TOTAL EN CAJA','style'=>'tableHeader'), 
          array('text'=>number_format($sumTotalVenta+$sumNotasCredito,2),'style'=>'tableHeader')
      )
    );

    /* DATA TOTALIZADO POR MEDIO DE PAGO */ 
    $listaPorMedioPago = $this->model_caja->m_cargar_ventas_por_medio_pago(FALSE,$allInputs);
    $arrListadoMP = array();
    $totalMP = 0;
    foreach ($listaPorMedioPago as $row) { 
      $totalMP += $row['total'];
      array_push($arrListadoMP, 
        array(
          $row['descripcion_med'],
          array('text'=>(empty($row['cantidad']) ? '-' : $row['cantidad']),'alignment'=>'center'),
          array('text'=>(empty($row['total']) ? '-' : number_format($row['total'],2)),'alignment'=>'center')
        )
      );
    }
    $arrListadoMP[] = array(
      array('text'=>'TOTAL','style'=>'tableHeader'), 
      array('text'=>'','style'=>'tableHeader'), 
      array('text'=> number_format($totalMP,2),'style'=>'tableHeader')
    );
    $arrColumnsMP = array(
      array('text'=>'MEDIO DE PAGO','style'=>'tableHeader'), 
      array('text'=>'CANTIDAD','style'=>'tableHeader'), 
      array('text'=>'TOTAL','style'=>'tableHeader'), 
    );
    array_unshift($arrListadoMP, $arrColumnsMP);

    $arrColumnsRV = array(
      array('text'=>'FECHA','style'=>'tableHeader'), 
      array('text'=>'CAJA','style'=>'tableHeader'), 
      array('text'=>'CAJERA','style'=>'tableHeader'), 
      array('text'=>'HORA APER.','style'=>'tableHeader'), 
      array('text'=>'HORA CIERRE','style'=>'tableHeader'), 
      array('text'=>'N° ANULADOS','style'=>'tableHeader'),  /*, 'N° EMITIDOS '*/ 
      array('text'=>'N° N.C.R.','style'=>'tableHeader'), 
      array('text'=>'MONTO','style'=>'tableHeader') 
    );
    $arrWidths = array(
      '*','*','*','*','*','*','*','*'
    );
    array_unshift($arrListado, $arrColumnsRV);
    $arrFiltros = array( 
      array(
        'text'=> array(' ')
      ),
      // array(
      //   'text'=> array(
      //       array(
      //         'text'=>'SEDE: ',
      //         'style'=> 'filterTitle'
      //       ),
      //       'VILLA EL SALVADOR'
      //   )
      // ),
      array(
        'text'=> array(
            array(
              'text'=>'DESDE: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['desde']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'HASTA: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['hasta']
        )
      ),
      array(
        'text'=> array(' ')
      )
    );
    $arrTablePrincipal = array(
      'table'=> array( 
          'widths'=> $arrWidths,
          'body' => $arrListado,
      ),
      'fontSize'=> 9
    );
    $arrTotalesFooter = array(
      'text'=> array(
            array(
              'text'=>'TOTAL: '
            ),
            empty($totalRows['total_importe']) ? '0.00' : number_format($totalRows['total_importe'],2)
        ),
        'style'=> array( 
            'alignment'=> 'right',
            'bold'=> true,
            'fontSize'=> 18
        ),
        'margin'=> array(0,0,12,0)
    );
    $arrOtherTables = array(
      array( 
        'style'=> 'tableStyle',
        'table'=> array( 
          'body'=> $arrListadoMP
        )
      ),
      array(
        'text'=> array(' ')
      ),
      array( 
        'style'=> 'tableStyle',
        'table'=> array( 
          'body'=> $arrListadoNCR
        )
      )
    );
    $arrContent[] = array( 
      $arrFiltros,
      $arrTablePrincipal,
      $arrTotalesFooter,
      $arrOtherTables
    );
    $arrData['message'] = '';
    $arrData['flag'] = 1;
    // SE AGREGA ESTA LINEA PARA OBTENER LA IMAGEN DEL LOGO DE LA EMPRESA SELECCIONADA
    $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs,'landscape',FALSE,FALSE,$empresa_admin);
    // $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs,'landscape');
    
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }  
  public function report_detalle_por_venta_caja()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $listaCajas = $this->model_caja->m_cargar_cajas_de_dia_usuario($allInputs);
    $arrContent = array();
    foreach ($listaCajas as $key => $value) { 
      $listaVentasDeCaja = $this->model_venta->m_cargar_ventas_esta_caja($value); 
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
      //var_dump($arrSoloMediosPago); exit(); 
      //var_dump($arrSoloMediosPago); exit(); 
      foreach ($listaVentasDeCaja as $row) { 
        $strFechaVenta = $row['fecha_venta'];
        if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' ){ // VENTAS  
          array_push($arrListadoProd, 
            array( 
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['orden_venta'],
              $row['ticket_venta'],
              strtoupper($row['descripcion_td']),
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              strtoupper($row['descripcion_med']),
              $row['total_a_pagar'],
              ($row['tipofila'] == 'a' ? 'ANULADO' : ' ')
            )
          );
          if( $row['tipofila'] == 'v' ){
            $sumTotalVenta += $row['total_a_pagar'];
          }
          // if( $row['tipofila'] == 'a' ){ 
          //   $countAnulados++;
          // }
        }
        if( $row['tipofila'] == 'nc' ){ 
          $sumTotalNCR += $row['total_a_pagar'];
          $countNCR++;
          array_push($arrListadoNCR, 
            array(
              // date('d/m/Y',strtotime($strFechaVenta)),
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['ticket_venta'],
              $row['especialidad'],
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              $row['orden_venta'],
              $row['total_a_pagar']
            )
          );
        }
      }
      $arrColumnsTablaPrinc = array(
        // array('text'=>'FECHA','style'=>'tableHeader'), 
        array('text'=>'HORA','style'=>'tableHeader'), 
        array('text'=>'N° DE ORDEN','style'=>'tableHeader'), 
        array('text'=>'TICKET','style'=>'tableHeader'), 
        array('text'=>'TIPO DOCUMENTO','style'=>'tableHeader'), 
        array('text'=>'PACIENTE','style'=>'tableHeader'), 
        array('text'=>'MEDIO DE PAGO','style'=>'tableHeader'), 
        array('text'=>'MONTO','style'=>'tableHeader'),  /*, 'N° EMITIDOS '*/ 
        array('text'=>'ESTADO','style'=>'tableHeader')
      );
      $arrWidths = array(
        '8%','14%','10%','12%','*','12%','6%','7%'
      );

      array_unshift($arrListadoProd, $arrColumnsTablaPrinc);

      $arrColumnsTablaNCR = array(
        // array('text'=>'FECHA','style'=>'tableHeader'), 
        array('text'=>'HORA','style'=>'tableHeader'), 
        array('text'=>'TICKET','style'=>'tableHeader'), 
        array('text'=>'ESPECIALIDAD','style'=>'tableHeader'), 
        array('text'=>'PACIENTE','style'=>'tableHeader'), 
        array('text'=>'ORDEN','style'=>'tableHeader'),
        array('text'=>'MONTO','style'=>'tableHeader')
      );
      $arrWidthsNCR = array(
        '8%','12%','20%','38%','15%','7%'
      );
      if( empty($arrListadoNCR) ){ 
        $arrListadoNCR = array(
          array(
            array(
              'text'=> 'No se encontró notas de crédito.',
              'style'=> array( 'fontSize'=> 9, 'alignment'=> 'center' ),
              'colSpan'=> 6
            ),array(),array(),array(),array(),array()
          )
        );
      }
      array_unshift($arrListadoNCR, $arrColumnsTablaNCR);
      array_unshift($arrListadoNCR, array(
          //array( 
            array( 
              'text'=> 'NOTAS DE CREDITO',
              'style'=> 'tableHeader',
              'fontSize'=> 12,
              'alignment'=> 'center',
              'colSpan'=> 6
            ),
            array(),array(),array(),array(),array()
          //)
        )
      );
      $arrFiltros = array( 
        array(
          'text'=> array(' ')
        ),
        array(
          'text'=> array(
              array(
                'text'=>'SEDE: ',
                'style'=> 'filterTitle'
              ),
              'VILLA EL SALVADOR'
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'FECHA: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['fecha']
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'CAJA: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['caja']['descripcion']
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'USUARIO: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['usuario']['descripcion']
          )
        ),
        array(
          'text'=> array(' ')
        )
      );
      $arrTablePrincipal = array(
        'table'=> array( 
            'widths'=> $arrWidths,
            'body' => $arrListadoProd,
        ),
        'style'=> array(
          'fontSize'=> 9
        )
      );
      //var_dump($arrSoloMediosPago); exit(); 
      $arrFormatSoloMediosPago = array( array(
        array('text'=> 'MEDIO DE PAGO'),
        array('text'=> 'CANT.'),
        array('text'=> 'MONTO')  
        )
      );
      foreach ($arrSoloMediosPago as $key => $valueSMP) {
        array_push($arrFormatSoloMediosPago, array(
            array('text'=> $valueSMP['descripcion_med'] ),
            array('text'=> (string)$valueSMP['cantidad_gen']),
            array('text'=> number_format($valueSMP['monto_gen'],2))  
          )
        );
      }
      $arrTotalesMedioPago = array( 
        'table'=> array( 
          'body'=> $arrFormatSoloMediosPago
        ),
        'margin'=> array(0,5,0,5),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 210
      );
      $arrTotalesFooter = array( 
        'table'=> array(
          'body' => array(
            array(
              array('text'=>'CANT. VENTAS'),
              array('text'=> (string)$countVentas )
            ),
            array(
              array('text'=>'CANT. ANULADOS'),
              array('text'=> (string)$countAnulados )
            ),
            array(
              array('text'=>'TOTAL VENTAS'),
              array('text'=> empty($sumTotalVenta) ? '0.00' : number_format($sumTotalVenta,2) )
            )
          )
        ),
        'margin'=> array(5,5,0,5),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 210
      );

      $arrTableNCR = array(
        'table'=> array( 
            'widths'=> $arrWidthsNCR,
            'body' => $arrListadoNCR
        ),
        'style'=> array(
          'fontSize'=> 9 
          // 'alignment'=> 'right'
        )
        //'alignment'=> 'right'
      );
      $arrTotalesFooterNCR = array( 
        'table'=> array(
          'body' => array(
            array(
              array('text'=>'CANT. N.CREDITO'),
              array('text'=> (string)$countNCR )
            ),
            array(
              array('text'=>'TOTAL N.CREDITO'),
              array('text'=> empty($sumTotalNCR) ? '0.00' : number_format($sumTotalNCR,2) )
            )
          )
        ),
        'margin'=> array(0,5,0,10),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 300
      );
      $arrMainTotalesFooter = array(
        'text'=> array(
          array(
            'text'=>'TOTAL EN CAJA: '
          ),
          number_format($sumTotalVenta+$sumTotalNCR,2)
        ),
        'style'=> array( 
          'alignment'=> 'right',
          'bold'=> true,
          'fontSize'=> 20,
          'color'=> 'black'
        ),
        'margin'=> array(0,0,12,0)
      );
      $arrContent[] = array( 
        $arrFiltros,
        $arrTablePrincipal,
        array( 'columns'=>
          array( 
              array('text'=> '', 'width'=> 390 ),
              $arrTotalesMedioPago,
              $arrTotalesFooter
          ) 
        ),
        $arrTableNCR,
        array( 'columns'=>
          array( 
              array('text'=> '', 'width'=> 605 ),
              $arrTotalesFooterNCR
          ) 
        ),
        $arrMainTotalesFooter
      );
    }
    // var_dump($listaCajas);  exit(); 
    
    $arrData['message'] = '';
    $arrData['flag'] = 1;
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs,'landscape');
    
    $arrData['dataPDF'] = $arrDataPDF;
    $this->output
        ->set_content_type('application/pdf') 
        ->set_output(json_encode($arrData));
  }
  public function report_detalle_por_producto_caja()
  { 
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $listaCajas = $this->model_caja->m_cargar_cajas_de_dia_usuario($allInputs);
    $arrContent = array();
    foreach ($listaCajas as $key => $value) {
      $listaVentasProductoDeCaja = $this->model_venta->m_cargar_ventas_detalle_esta_caja($value); 
      $arrListadoProd = array();
      $arrListadoNCR = array();
      $sumTotalVenta = 0;
      $countAnulados = 0;
      $countVentas = 0;
      $sumTotalNCR = 0;
      $countNCR = 0;
      //$arrSumTotalMedioPago = array();
      $arrSoloVentas = array();
      $arrSoloMediosPago = array();
      $valueDetGen = array();
      foreach ($listaVentasProductoDeCaja as $key => $valueDet) { 
        $arrSoloVentas[$valueDet['idventa']] = $valueDet; 
        if( $valueDet['tipofila'] == 'v' ){ 
          $valueDetGen['idmediopago'] = $valueDet['idmediopago'];
          $valueDetGen['descripcion_med'] = $valueDet['descripcion_med'];
          $valueDetGen['cantidad_gen'] = 0;
          $valueDetGen['monto_gen'] = 0;
          $arrSoloMediosPago[$valueDet['idmediopago']] = $valueDetGen; 
        }
        
      }
      //var_dump($arrSoloMediosPago); exit();
      foreach ($arrSoloVentas as $key => $valueDet) {
        if( $valueDet['tipofila'] == 'a' ){ 
          $countAnulados++;
        }
        if( $valueDet['tipofila'] == 'v' ){ 
          $countVentas++;
          if( $arrSoloMediosPago[$valueDet['idmediopago']]['idmediopago'] == $valueDet['idmediopago'] ){ 
            $arrSoloMediosPago[$valueDet['idmediopago']]['monto_gen'] += $valueDet['total_a_pagar'];
            $arrSoloMediosPago[$valueDet['idmediopago']]['cantidad_gen']++;
          }
        }
        
      }
      //var_dump($arrSoloMediosPago); exit(); 
      foreach ($listaVentasProductoDeCaja as $row) { 
        $strFechaVenta = $row['fecha_venta'];
        if( $row['tipofila'] == 'v' || $row['tipofila'] == 'a' ){ // VENTAS  
          array_push($arrListadoProd, 
            array(
              // date('d/m/Y',strtotime($strFechaVenta)),
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['ticket_venta'],
              $row['especialidad'],
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              strtoupper($row['producto']),
              $row['total_detalle'],
              ($row['tipofila'] == 'a' ? 'ANULADO' : ' ')
            )
          );
          if( $row['tipofila'] == 'v' ){
            $sumTotalVenta += $row['total_detalle'];
          }
          // if( $row['tipofila'] == 'a' ){ 
          //   $countAnulados++;
          // }
        }
        if( $row['tipofila'] == 'nc' ){ 
          $sumTotalNCR += $row['total_detalle'];
          $countNCR++;
          array_push($arrListadoNCR, 
            array(
              // date('d/m/Y',strtotime($strFechaVenta)),
              date('h:i:s a',strtotime($strFechaVenta)),
              $row['ticket_venta'],
              $row['especialidad'],
              strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
              $row['orden_venta'],
              $row['total_detalle']
            )
          );
        }
      }
      $arrColumnsTablaPrinc = array(
        // array('text'=>'FECHA','style'=>'tableHeader'), 
        array('text'=>'HORA','style'=>'tableHeader'), 
        array('text'=>'TICKET','style'=>'tableHeader'), 
        array('text'=>'ESPECIALIDAD','style'=>'tableHeader'), 
        array('text'=>'PACIENTE','style'=>'tableHeader'), 
        array('text'=>'PRODUCTO','style'=>'tableHeader'), 
        array('text'=>'MONTO','style'=>'tableHeader'),  /*, 'N° EMITIDOS '*/ 
        array('text'=>'ESTADO','style'=>'tableHeader')
      );
      $arrWidths = array(
        '8%','10%','15%','22%','*','6%','7%'
      );

      array_unshift($arrListadoProd, $arrColumnsTablaPrinc);

      $arrColumnsTablaNCR = array(
        // array('text'=>'FECHA','style'=>'tableHeader'), 
        array('text'=>'HORA','style'=>'tableHeader'), 
        array('text'=>'TICKET','style'=>'tableHeader'), 
        array('text'=>'ESPECIALIDAD','style'=>'tableHeader'), 
        array('text'=>'PACIENTE','style'=>'tableHeader'), 
        array('text'=>'ORDEN','style'=>'tableHeader'),
        array('text'=>'MONTO','style'=>'tableHeader')
      );
      $arrWidthsNCR = array(
        '8%','12%','20%','38%','15%','7%'
      );
      if( empty($arrListadoNCR) ){ 
        $arrListadoNCR = array(
          array(
            array(
              'text'=> 'No se encontró notas de crédito.',
              'style'=> array( 'fontSize'=> 9, 'alignment'=> 'center' ),
              'colSpan'=> 6
            ),array(),array(),array(),array(),array()
          )
        );
      }
      array_unshift($arrListadoNCR, $arrColumnsTablaNCR);
      array_unshift($arrListadoNCR, array(
          //array( 
            array( 
              'text'=> 'NOTAS DE CREDITO',
              'style'=> 'tableHeader',
              'fontSize'=> 12,
              'alignment'=> 'center',
              'colSpan'=> 6
            ),
            array(),array(),array(),array(),array()
          //)
        )
      );
      $arrFiltros = array( 
        array(
          'text'=> array(' ')
        ),
        array(
          'text'=> array(
              array(
                'text'=>'SEDE: ',
                'style'=> 'filterTitle'
              ),
              'VILLA EL SALVADOR'
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'FECHA: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['fecha']
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'CAJA: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['caja']['descripcion']
          )
        ),
        array(
          'text'=> array(
              array(
                'text'=>'USUARIO: ',
                'style'=> 'filterTitle'
              ),
              $allInputs['usuario']['descripcion']
          )
        ),
        array(
          'text'=> array(' ')
        )
      );
      $arrTablePrincipal = array(
        'table'=> array( 
            'widths'=> $arrWidths,
            'body' => $arrListadoProd,
        ),
        'style'=> array(
          'fontSize'=> 9
        )
      );
      //var_dump($arrSoloMediosPago); exit(); 
      $arrFormatSoloMediosPago = array( array(
        array('text'=> 'MEDIO DE PAGO'),
        array('text'=> 'CANT.'),
        array('text'=> 'MONTO')  
        )
      );
      foreach ($arrSoloMediosPago as $key => $valueSMP) {
        array_push($arrFormatSoloMediosPago, array(
            array('text'=> $valueSMP['descripcion_med'] ),
            array('text'=> (string)$valueSMP['cantidad_gen']),
            array('text'=> number_format($valueSMP['monto_gen'],2))  
          )
        );
      }
      $arrTotalesMedioPago = array( 
        'table'=> array( 
          'body'=> $arrFormatSoloMediosPago
        ),
        'margin'=> array(0,5,0,5),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 210
      );
      $arrTotalesFooter = array( 
        'table'=> array(
          'body' => array(
            array(
              array('text'=>'CANT. VENTAS'),
              array('text'=> (string)$countVentas )
            ),
            array(
              array('text'=>'CANT. ANULADOS'),
              array('text'=> (string)$countAnulados )
            ),
            array(
              array('text'=>'TOTAL VENTAS'),
              array('text'=> empty($sumTotalVenta) ? '0.00' : number_format($sumTotalVenta,2) )
            )
          )
        ),
        'margin'=> array(5,5,0,5),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 210
      );

      $arrTableNCR = array(
        'table'=> array( 
            'widths'=> $arrWidthsNCR,
            'body' => $arrListadoNCR
        ),
        'style'=> array(
          'fontSize'=> 9 
          // 'alignment'=> 'right'
        )
        //'alignment'=> 'right'
      );
      $arrTotalesFooterNCR = array( 
        'table'=> array(
          'body' => array(
            array(
              array('text'=>'CANT. N.CREDITO'),
              array('text'=> (string)$countNCR )
            ),
            array(
              array('text'=>'TOTAL N.CREDITO'),
              array('text'=> empty($sumTotalNCR) ? '0.00' : number_format($sumTotalNCR,2) )
            )
          )
        ),
        'margin'=> array(0,5,0,10),
        'color'=> 'black',
        'bold'=> true,
        'fontSize'=> 12,
        'width'=> 300
      );
      $arrMainTotalesFooter = array(
        'text'=> array(
          array(
            'text'=>'TOTAL EN CAJA: '
          ),
          number_format($sumTotalVenta+$sumTotalNCR,2)
        ),
        'style'=> array( 
          'alignment'=> 'right',
          'bold'=> true,
          'fontSize'=> 20,
          'color'=> 'black'
        ),
        'margin'=> array(0,0,12,0)
      );
      $arrContent[] = array( 
        $arrFiltros,
        $arrTablePrincipal,
        array( 'columns'=>
          array( 
              array('text'=> '', 'width'=> 390 ),
              $arrTotalesMedioPago,
              $arrTotalesFooter
          ) 
        ),
        $arrTableNCR,
        array( 'columns'=>
          array( 
              array('text'=> '', 'width'=> 605 ),
              $arrTotalesFooterNCR
          ) 
        ),
        $arrMainTotalesFooter
      );
    }
    // var_dump($listaCajas);  exit(); 
    
    $arrData['message'] = '';
    $arrData['flag'] = 1;
    
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs,'landscape');
    
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ficha_atencion()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    if( empty($allInputs[0]['num_acto_medico']) ){ 
      $arrNumActoMedico = array($allInputs['num_acto_medico']);
      $listaAtenciones = $this->model_atencion_medica->m_cargar_esta_atencion_medica($arrNumActoMedico);
      foreach ($listaAtenciones as $key => $row) {
        $rowFechaVenta = $row['fecha_venta'];
        $strSexo = '-';
        if( $row['sexo'] == 'M' ){ 
          $strSexo = 'MASCULINO';
        }elseif( $row['sexo'] == 'F' ){
          $strSexo = 'FEMENINO';
        }
        $strTipoResultado = '-';
        if( $row['ex_tipo_resultado'] == 1 ){
          $strTipoResultado = 'NORMAL';
        }elseif ( $row['ex_tipo_resultado'] == 2 ) {
          $strTipoResultado = 'PATOLOGICO';
        }
      
        array_push($allInputs, 
          array(
            'id' => $row['idventa'],
            'iddetalle' => $row['iddetalle'],
            'num_acto_medico' => $row['idatencionmedica'], 
            'orden' => $row['orden_venta'],
            'ticket' => $row['ticket_venta'],
            'fecha_atencion' => formatoFechaReporte($row['fecha_atencion']),
            'idcliente' => $row['idcliente'],
            'idhistoria' => $row['idhistoria'],
            'cliente' => strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
            'sexo' => $strSexo,
            'boolSexo' => $row['sexo'],
            'numero_documento' => $row['num_documento'],
            'edad' => $row['edad'],
            'edadEnAtencion' => strtoupper(devolverEdadDetalle($row['fecha_nacimiento'])),
            'idespecialidad' => $row['idespecialidad'],
            'especialidad' => $row['especialidad'],
            'idproductomaster' => $row['idproductomaster'],
            'producto' => $row['producto'],
            'idtipoproducto' => $row['idtipoproducto'],
            'tipo_producto' => $row['nombre_tp'],
            'idareahospitalaria' => $row['idareahospitalaria'],
            'area_hospitalaria' => $row['descripcion_aho'],
            'anamnesis' => $row['anamnesis'],
            'presion_arterial_mm' => $row['presion_arterial_mm'],
            'presion_arterial_hg' => $row['presion_arterial_hg'],
            'frecuencia_cardiaca_lxm' => $row['frec_cardiaca'],
            'frecuencia_respiratoria' => $row['frec_respiratoria'],
            'temperatura_corporal' => $row['temperatura_corporal'],
            'peso' => $row['peso'],
            'talla' => $row['talla'],
            'imc' => $row['imc'],
            'gestando' => (int)$row['gestando'],
            'fur' => darFormatoDMY($row['fecha_ultima_regla']),
            'fpp' => darFormatoFecha($row['fecha_probable_parto']),
            // 'semana_gestacion' => $this->calcular_semana_gestacion($row['fecha_ultima_regla'],'other'),
            'perimetro_abdominal' => $row['perimetro_abdominal'],
            'antecedentes' => $row['antecedentes'],
            'observaciones' => $row['observaciones'],
            'examen_clinico' => $row['examen_clinico'],
            'atencion_control' => $row['atencion_control'],
            'fecha_atencion' => $row['fecha_atencion'],
            'fechaAtencion' => darFormatoFecha($row['fecha_atencion']),
            'horaAtencion' => darFormatoHora($row['fecha_atencion']),
            'orden_venta' => $row['orden_venta'],
            'ticket_venta' => $row['ticket_venta'],
            'personalatencion' => array( 
              'id' => $row['idmedicoatencion'],
              'descripcion' => $row['medicoatencion']
            ),
            /* PARA PROCEDIMIENTO */
            'observacion' => strip_tags($row['proc_observacion']),
            'proc_informe' => strip_tags($row['proc_informe']),

            /* PARA EXAMEN AUXILIAR */
            'indicaciones' => strip_tags($row['ex_indicaciones']),
            'ex_informe' => strip_tags($row['ex_informe']),
            'tipoResultado' => (int)$row['ex_tipo_resultado'],
            'strTipoResultado' => $strTipoResultado,
            'personal' => array(
              'id' => $row['ex_responsable_medico'],
              'descripcion' => $row['medico']
            ),
            /* PARA DOCUMENTOS */
            'fecha_otorgamiento' => empty($row['fec_otorgamiento'])? null: date('d-m-Y',strtotime($row['fec_otorgamiento'])),
            'fecha_iniciodescanso' => empty($row['fec_iniciodescanso'])? null: date('d-m-Y',strtotime($row['fec_iniciodescanso'])),
            'dias' => empty($row['total_dias'])? null: $row['total_dias'],
            'idcontingencia' => empty($row['idcontingencia'])? null: $row['idcontingencia'],
            'contingencia' => empty($row['contingencia'])? null: $row['contingencia'],
            'doc_informe' => strip_tags(@$row['doc_informe'])
          )
        );
      }
    }
    $allInputs['titulo'] = 'ACTO MEDICO N° '.$allInputs[0]['num_acto_medico'];
    $arrContent = array();
    $arrTableDiagnostico = array();
    $arrTableReceta = array();

    $arrTableConsultaMedica = array();
    $arrTableExamenAuxiliar = array();
    $arrTableProcedimiento = array();
    $arrTableDocumento = array();

    $arrTableActoMedico = array( 
      array(
        'table'=> array( 
          'widths'=> array('*','*','*','*'),
          'body' => array(
            array(
              array(
                'text'=>'DATOS DEL PACIENTE',
                'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                'colSpan'=> 4
              ),array(),array(),array()
            ),
            array(
              array(
                'text'=>'APELLIDOS Y NOMBRES'
              ),
              array(
                'text'=>'DOC. DE IDENTIDAD'
              ),
              array(
                'text'=>'SEXO'
              ),
              array(
                'text'=>'N° HISTORIA CLINICA'
              )
            ),
            array(
              array(
                'text'=> $allInputs[0]['cliente'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> empty($allInputs[0]['numero_documento']) ? ' ':$allInputs[0]['numero_documento'] ,
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> empty($allInputs[0]['sexo']) ? ' ':$allInputs[0]['sexo'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['idhistoria'],
                'style'=> array('fontSize'=>6) 
              )
            )
          ),
        ),
        'style'=> array( 
          'fontSize'=> 8,
        ),
        'margin'=> array(0,5,0,5)
      ),
      array(
        'table'=> array( 
          'widths'=> array('*','*','*','*'),
          'body' => array(
            array(
              array(
                'text'=>'ACTO MEDICO',
                'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                'colSpan'=> 4
              ),array(),array(),array()
            ),
            array(
              array(
                'text'=>'N° ACTO MEDICO'
              ),
              array(
                'text'=>'N° DE ORDEN'
              ),
              array(
                'text'=>'AREA HOSPITALARIA'
              ),
              array(
                'text'=>'PROFESIONAL'
              )
            ),
            array(
              array(
                'text'=> $allInputs[0]['num_acto_medico'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['orden_venta'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['area_hospitalaria'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['personalatencion']['descripcion'],
                'style'=> array('fontSize'=>6) 
              )
            ),
            array(
              array(
                'text'=>'FECHA DE ATENCION'
              ),
              array(
                'text'=>'EDAD EN ATENCION'
              ),
              array(
                'text'=>'ESPECIALIDAD'
              ),
              array(
                'text'=>'ACTIVIDAD ESPECIFICA'
              )
            ),
            array(
              array(
                'text'=> $allInputs[0]['fechaAtencion'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['edadEnAtencion'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['especialidad'],
                'style'=> array('fontSize'=>6) 
              ),
              array(
                'text'=> $allInputs[0]['producto'],
                'style'=> array('fontSize'=>6) 
              )
            )
          ),
        ),
        'style'=> array( 
          'fontSize'=> 8,
        ),
        'margin'=> array(0,5,0,5)
      )
    );
    if($allInputs[0]['idtipoproducto'] == 12){ // CONSULTA MEDICA 
      $arrTableConsultaMedica = array(
        array(
          'table'=> array( 
            'widths'=> array('*'),
            'body' => array(
              array(
                array(
                  'text'=>'ANAMNESIS',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                )
              ),
              array(
                array(
                  'text'=> $allInputs[0]['anamnesis'],
                  'style'=> array('fontSize'=>6) 
                )
              )
            ),
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        ),
        array(
          'table'=> array( 
            'widths'=> array('*','*','*','*'),
            'body' => array(
              array(
                array(
                  'text'=>'SIGNOS VITALES',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                  'colSpan'=> 4
                ),array(),array(),array()
              ),
              array(
                array(
                  'text'=>'PRESION ARTERIAL'
                ),
                array(
                  'text'=>'FRECUENCIA CARDIACA'
                ),
                array(
                  'text'=>'TEMPERATURA CORPORAL'
                ),
                array(
                  'text'=>'FRECUENCIA RESPIRATORIA'
                )
              ), 
              array( 
                array(
                  'text'=> $allInputs[0]['presion_arterial_hg'].'/'.$allInputs[0]['presion_arterial_mm'],
                  'style'=> array('fontSize'=>6) 
                ),
                array(
                  'text'=> empty($allInputs[0]['frecuencia_cardiaca_lxm']) ? ' ' : $allInputs[0]['frecuencia_cardiaca_lxm'],
                  'style'=> array('fontSize'=>6) 
                ),
                array(
                  'text'=> empty($allInputs[0]['temperatura_corporal']) ? ' ' : $allInputs[0]['temperatura_corporal'],
                  'style'=> array('fontSize'=>6) 
                ),
                array(
                  'text'=> empty($allInputs[0]['frecuencia_respiratoria']) ? ' ' : $allInputs[0]['frecuencia_respiratoria'],
                  'style'=> array('fontSize'=>6)  
                )
              )/**/
            )
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        ), 
        array(
          'table'=> array( 
            'widths'=> array('*','*','*','*'),
            'body' => array(
              array(
                array(
                  'text'=>'ANTROPOMETRIA',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                  'colSpan'=> 4
                ),array(),array(),array()
              ),
              array(
                array(
                  'text'=>'PESO'
                ),
                array(
                  'text'=>'TALLA'
                ),
                array(
                  'text'=>'IMC'
                ),
                array(
                  'text'=>'PERIMETRO ABDOMINAL'
                )
              ), 
              array( 
                array(
                  'text'=> empty($allInputs[0]['peso']) ? ' ' : $allInputs[0]['peso'],
                  'style'=> array('fontSize'=>6) 
                ),
                array(
                  'text'=> empty($allInputs[0]['talla']) ? ' ' : $allInputs[0]['talla'],
                  'style'=> array('fontSize'=>6) 
                ),
                array( 
                  'text'=> empty($allInputs[0]['imc']) ? ' ' : $allInputs[0]['imc'],
                  'style'=> array('fontSize'=>6) 
                ),
                array(
                  'text'=> empty($allInputs[0]['perimetro_abdominal']) ? ' ' : $allInputs[0]['perimetro_abdominal'],
                  'style'=> array('fontSize'=>6)  
                )
              ),
              array( 
                array(
                  'text'=> 'EXAMEN CLINICO',
                  'colSpan'=> 2
                ),
                array(),
                array(
                  'text'=> 'ANTECEDENTES',
                  'colSpan'=> 2
                ),
                array()
              ),
              array( 
                array(
                  'text'=> empty($allInputs[0]['examen_clinico']) ? ' ' : $allInputs[0]['examen_clinico'],
                  'style'=> array('fontSize'=>6),
                  'colSpan'=> 2
                ),
                array(),
                array(
                  'text'=> empty($allInputs[0]['antecedentes']) ? ' ' : $allInputs[0]['antecedentes'],
                  'style'=> array('fontSize'=>6), 
                  'colSpan'=> 2
                )
                ,array()
              )
            )
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        ),
        array(
          'table'=> array( 
            'widths'=> array('*','*','*','*'),
            'body' => array(
              array(
                array(
                  'text'=>'PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true ),
                  'colSpan'=> 4
                ),array(),array(),array()
              ),
              array(
                array(
                  'text'=>'PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES',
                  'colSpan'=> 2
                ),
                array(),
                array(
                  'text'=>'¿ATENCION DE CONTROL?',
                  'colSpan'=> 2
                ),
                array()
              ), 
              array(
                array(
                  'text'=> empty($allInputs[0]['observaciones']) ? ' ' : $allInputs[0]['observaciones'],
                  'style'=> array('fontSize'=>6), 
                  'colSpan'=> 2
                ),
                array(),
                array(
                  'text'=> ($allInputs[0]['atencion_control'] == 2 ? 'NO' : 'SI'),
                  'style'=> array('fontSize'=>6), 
                  'colSpan'=> 2
                ),
                array()
              ), 
            )
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        )
      );
    }elseif($allInputs[0]['idtipoproducto'] == 16){ // PROCEDIMIENTO 
      $arrTableProcedimiento = array(
        array(
          'table'=> array( 
            'widths'=> array('*'),
            'body' => array(
              array(
                array(
                  'text'=>'OBSERVACIONES',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true )
                )
              ),
              array(
                array(
                  'text'=> empty($allInputs[0]['proc_observacion']) ? ' ':$allInputs[0]['proc_observacion'],
                  'style'=> array('fontSize'=>6) 
                )
              ),
              array(
                array(
                  'text'=>'INFORME',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true )
                )
              ),
              array(
                array(
                  'text'=> empty($allInputs[0]['proc_informe']) ? ' ':$allInputs[0]['proc_informe'],
                  'style'=> array('fontSize'=>6) 
                )
              )
            )
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        )
      );
    }elseif( $allInputs[0]['idtipoproducto'] == 11 || $allInputs[0]['idtipoproducto'] == 14 || $allInputs[0]['idtipoproducto'] == 15){ // EXAMEN AUXILIAR 
      $arrTableExamenAuxiliar = array(

      );
    }elseif($allInputs[0]['idtipoproducto'] == 13){ // DOCUMENTOS 
      $arrTableDocumento = array( 
        array(
          'table'=> array( 
            'widths'=> array('*'),
            'body' => array(
              array(
                array(
                  'text'=>'INFORME',
                  'style'=> array( 'alignment'=> 'center', 'bold'=> true )
                )
              ),
              array(
                array(
                  'text'=> empty($allInputs[0]['doc_informe']) ? ' ':strip_tags($allInputs[0]['doc_informe']),
                  'style'=> array('fontSize'=>6) 
                )
              )
            )
          ),
          'style'=> array( 
            'fontSize'=> 8,
          ),
          'margin'=> array(0,5,0,5)
        )
      );
    }
    $arrFooter = array(
      
      array(
        'columns'=> array( 
          array(
            'text'=> '',
            'width'=> 200
          ),
          array( 
            'text'=> $allInputs[0]['personalatencion']['descripcion'],
            'fontSize'=> 12,
            'margin'=> array(0,90,0,0),
            'alignment'=> 'right'
          )
        )
      ),
      array(
        'columns'=> array( 
          array(
            'text'=> '',
            'width'=> 340
          ),
          array( 
            'text'=> 'Sello y firma',
            'fontSize'=> 10,
            'alignment'=> 'center'
          )
        )
      ),
      array( 
        'text'=> 'COMPROMETIDOS CON TU SALUD',
        'alignment'=> 'center',
        'fontSize'=> 7,
        'margin'=> array(0,60,0,0)
      )
    );
    //array_push($arrTableActoMedico,$arrTableConsultaMedica); var_dump($arrTableActoMedico); exit();
    $arrContent[] = array( 
      $arrTableActoMedico,
      $arrTableConsultaMedica,
      $arrTableExamenAuxiliar,
      $arrTableProcedimiento,
      $arrTableDocumento,
      $arrTableDiagnostico,
      $arrTableReceta,
      $arrFooter
    );
    $arrData['message'] = '';
    $arrData['flag'] = 1;
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs);
    
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
    // var_dump($allInputs); exit(); 
  }
  public function report_detalle_por_tipo_documento_caja()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrFiltros = array();
    $arrTablesPrincipales = array();
    $arrTotalesFooter = array();

    $arrFiltros = array( 
      array(
        'text'=> array(' ')
      ),
      array(
        'text'=> array(
            array(
              'text'=>'SEDE: ',
              'style'=> 'filterTitle'
            ),
            'VILLA EL SALVADOR'
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'DESDE: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['desde']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'HASTA: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['hasta']
        )
      ),
      array(
        'text'=> array(' ')
      )
    );
    /* DATA RESUMEN DE CAJA POR TIPO DOC */ 
    //var_dump($allInputs['tipodocumento']); exit();
    $listaDocumentosVendidos = array(); 
    if( $allInputs['tipodocumento'][0]['id'] == 'all' ){ 
      $listaDocumentosVendidos = $this->model_caja->m_cargar_documentos_vendidos_distinct($allInputs);
    }else{
      foreach ($allInputs['tipodocumento'] as $key => $row) {
        $listaDocumentosVendidos[] = array(
          'idtipodocumento'=> $row['id'],
          'descripcion_td'=> $row['descripcion']
        );
      }
      
    }

    $sumTotalesMontoGen = 0;
    $sumTotalesAnuladosGen = 0;
    foreach ($listaDocumentosVendidos as $keyDT => $rowTD) { 
      //$arrListado = array();
      $arrListado = array(
        array(
          array(
            'text'=> $rowTD['descripcion_td'], 
            'style'=>'tableHeaderLG',
            'colSpan'=> 7
          ),array(),array(),array(),array(),array(),array(),
        ),
        array(
            array('text'=>'FECHA','style'=>'tableHeader'), 
            array('text'=>'CAJA','style'=>'tableHeader'), 
            array('text'=>'CAJERA','style'=>'tableHeader'), 
            array('text'=>'HORA APER.','style'=>'tableHeader'), 
            array('text'=>'HORA CIERRE','style'=>'tableHeader'), 
            array('text'=>'N° ANULADOS','style'=>'tableHeader'), 
            array('text'=>'MONTO','style'=>'tableHeader') 
        )
      );
      // var_dump($rowTD['idtipodocumento']); 
      $allInputs['tipodocumento'] = $rowTD['idtipodocumento']; 

      $listaDetalle = $this->model_caja->m_cargar_apertura_caja(FALSE,$allInputs); 
      //$arrListadoDetalle = array();
      $sumAnuladosAU = 0;
      $sumTotalVentaAU = 0;
      foreach ($listaDetalle as $key => $row) { 
        $strFechaAperturaCaja = $row['fecha_apertura'];
        $strFechaCierreCaja = $row['fecha_cierre'];
        $totalVenta = $row['total_venta'];
        $sumAnuladosAU += $row['cantidad_anulado']; 
        if( $rowTD['idtipodocumento'] == 1 ){ // tipo documento ticket 
          if( !empty($row['suma_nota_credito']) ){ 
            $totalVenta = $row['total_venta'] + $row['suma_nota_credito']; 
          }
        }
        if( $rowTD['idtipodocumento'] == 3 ){ // tipo documento operacion 
          if( !empty($row['suma_extorno']) ){ 
            $totalVenta = $row['total_venta'] + $row['suma_extorno']; 
          }
        }
        $sumTotalVentaAU += $totalVenta; 
        array_push($arrListado, 
          array(
            date('d/m/Y',strtotime($strFechaAperturaCaja)),
            'Caja N° '.$row['numero_caja'],
            strtoupper($row['username']),
            date('h:i:s a',strtotime($strFechaAperturaCaja)),
            date('h:i:s a',strtotime($strFechaCierreCaja)),
            array('text'=>(empty($row['cantidad_anulado']) ? '-' : $row['cantidad_anulado']),'alignment'=>'center'),
            number_format($totalVenta,2)
            //$row['cantidad_venta'],
            
          )
        );
      }
      // array_push($arrListado,$arrListadoDetalle);
      $arrTablesPrincipales[] = array(
        'table'=> array( 
            'widths'=> array( '*','*','*','*','*','*','*','*' ),
            'body' => $arrListado
        ),
        'margin'=> array(0,0,0,5),
        'fontSize'=> 9
      );
      // var_dump($sumAnuladosAU); exit();
      $arrTablesPrincipales[] = array(
        'columns'=> array( 
          array(
            'text'=> ' ',
            'width'=> 484
          ),
          array(
            'table'=> array( 
                'widths'=> array('32%','32%'),
                'body' => array(
                  array(
                    array('text'=>(string)$sumAnuladosAU, 'alignment'=> 'center'),
                    array('text'=>number_format($sumTotalVentaAU,2))
                  )
                )

            ),
            'margin'=> array(0,0,0,15),
            'bold'=> true,
            'fontSize'=> 12
          ) 
        )
      );
      $sumTotalesMontoGen+= $sumTotalVentaAU;
      $sumTotalesAnuladosGen+= $sumAnuladosAU;
    }
    // exit();
    $arrTotalesFooter = array(
      array(
        'columns'=> array( 
          array(
            'text'=> ' ',
            'width'=> 484
          ),
          array(
            'table'=> array( 
              'widths'=> array('32%','32%'),
              'body' => array(
                array(
                  array('text'=>(string)$sumTotalesAnuladosGen, 'alignment'=> 'center'),
                  array('text'=>number_format($sumTotalesMontoGen,2))
                )
              )
            ),
            'margin'=> array(0,0,0,15),
            'bold'=> true,
            'fontSize'=> 15
          ) 
        )
      )
    );
    $arrContent[] = array( 
      $arrFiltros,
      $arrTablesPrincipales,
      $arrTotalesFooter
    );

    $arrData['message'] = '';
    $arrData['flag'] = 1;
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs,'landscape');
    
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_consolidado_medico()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrFiltros = array();
    $arrTablesPrincipales = array();
    $arrTotalesFooter = array(); 
    $arrFiltros = array( 
      array(
        'text'=> array(' ')
      ),
      array(
        'text'=> array(
            array(
              'text'=>'DESDE: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['desde']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'HASTA: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['hasta']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'ESPECIALIDAD: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['especialidad']['descripcion']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'MEDICO: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['medico']['medico']
        )
      ),
      array(
        'text'=> array(' ')
      )
    );
    //var_dump("<pre>",$allInputs); exit(); 
    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $listaEspecialidadesVendidas = $this->model_atencion_medica->lista_especialidades_vendidas_entre_fechas($allInputs);
    }else{ 
      $listaEspecialidadesVendidas[] = array(
        'idespecialidad' => $allInputs['especialidad']['id'],
        'especialidad' => $allInputs['especialidad']['descripcion']
      );
    }
    foreach ($listaEspecialidadesVendidas as $key => $row) { 
      $arrTablesPrincipales[] = array(
        array(
          array( 
            'table'=> array( 
              'widths'=> array('100%'),
              'body'=> array(
                array(
                  array('text'=>$row['especialidad'], 'alignment'=> 'center') 
                )
              )
            ),
            'margin'=> array(0,14,0,1),
            'bold'=> true,
            'fontSize'=> 13
          )
        ), 
        array()
      ); 
      
      if( $allInputs['medico']['idmedico'] == 'ALL' ){ 
        $listaMedicosAtencion = $this->model_atencion_medica->lista_medicos_atenciones_entre_fechas_de_especialidad($allInputs,$row); 
      }else{ 
        $listaMedicosAtencion[] = array(
          'idmedico' => $allInputs['medico']['idmedico'],
          'medico' => $allInputs['medico']['medico']
        );
      }
      foreach ($listaMedicosAtencion as $keyDet => $rowDet) {
        $arrTablesPrincipales[] = array( 
          array(
            array( 
              'table'=> array( 
                'widths'=> array('100%'),
                'body'=> array(
                  array(
                    array('text'=>$rowDet['medico'], 'alignment'=> 'left')
                  )
                )
              ),
              'margin'=> array(0,10,0,1),
              'layout'=> 'headerLineOnly',
              'bold'=> true,
              'fontSize'=> 11
            )
          ), 
          array()
        ); 
        $listaTotalizadoAtencion = $this->model_atencion_medica->lista_totalizado_atenciones_entre_fechas_de_especialidad($allInputs,$row,$rowDet);
        $arrGroupByFecha = array();
        foreach ($listaTotalizadoAtencion as $keyAM => $rowAM) { 
          if( (int)$rowAM['hora'] >=  (int)$allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana'] && (int)$rowAM['hora'] <  (int)$allInputs['horaHastaManana'].$allInputs['minutoHastaManana'] ){
            $rowAMAux['fecha'] = $rowAM['fecha'];
            $arrGroupByFecha[$rowAM['fecha']] = $rowAMAux;
          }
          if( (int)$rowAM['hora'] >=  (int)$allInputs['horaDesdeTarde'].$allInputs['minutoDesdeTarde'] && (int)$rowAM['hora'] <  (int)$allInputs['horaHastaTarde'].$allInputs['minutoHastaTarde'] ){
            $rowAMAux['fecha'] = $rowAM['fecha'];
            $arrGroupByFecha[$rowAM['fecha']] = $rowAMAux;
          }
        }
        foreach ($arrGroupByFecha as $keyAux => $rowAux) { 
          // $contadorAM = 0;
          $arrGroupByFecha[$rowAux['fecha']]['am'] = array('cantidad' => null,'monto'=>null);
          $arrGroupByFecha[$rowAux['fecha']]['pm'] = array('cantidad' => null,'monto'=>null);
          foreach ($listaTotalizadoAtencion as $keyAM => $rowAM) { 
            if($rowAux['fecha'] == $rowAM['fecha']){ 
              if( (int)$rowAM['hora'] >= (int)($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) && (int)$rowAM['hora'] <  (int)($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']) ){
                $arrGroupByFecha[$rowAM['fecha']]['am']['cantidad'] += $rowAM['cantidad']; 
                $arrGroupByFecha[$rowAM['fecha']]['am']['monto'] += $rowAM['total_detalle']; 
              }
              if( (int)$rowAM['hora'] >= (int)($allInputs['horaDesdeTarde'].$allInputs['minutoDesdeTarde']) && (int)$rowAM['hora'] <  (int)($allInputs['horaHastaTarde'].$allInputs['minutoHastaTarde']) ){ 
                $arrGroupByFecha[$rowAM['fecha']]['pm']['cantidad'] += $rowAM['cantidad']; 
                $arrGroupByFecha[$rowAM['fecha']]['pm']['monto'] += $rowAM['total_detalle']; 
              }
            }
          }
        }
        /* HORAS */
        $arrTableDetalleHora = array( array(
            array( 'text'=> 'FECHA', 'alignment'=>'left' ),
            array( 'text'=> 'HORA', 'alignment'=>'center' ),
            array( 'text'=> 'CANT.', 'alignment'=>'center' ),
            array( 'text'=> 'MONTO', 'alignment'=>'center' )
            )
        );
        
        foreach ($arrGroupByFecha as $keyAux => $rowAux) { 
          $rowFechaAux = $rowAux['fecha'];
          $arrTableDetalleHora[] = array(
                array( 'text'=> date('d/m/Y',strtotime("$rowFechaAux")), 'alignment'=>'left' ),
                array( 'text'=> $allInputs['horaDesdeManana'].':'.$allInputs['minutoDesdeManana'].' - '.$allInputs['horaHastaManana'].':'.$allInputs['minutoHastaManana'], 'alignment'=>'center' ),
                array( 'text'=> (string)$rowAux['am']['cantidad'], 'alignment'=>'center' ),
                array( 'text'=> number_format($rowAux['am']['monto'],2), 'alignment'=>'center' )
          );
          $arrTableDetalleHora[] = array( 
            array( 'text'=> date('d/m/Y',strtotime("$rowFechaAux")), 'alignment'=>'left' ),
            array( 'text'=> $allInputs['horaDesdeTarde'].':'.$allInputs['minutoDesdeTarde'].' - '.$allInputs['horaHastaTarde'].':'.$allInputs['minutoHastaTarde'], 'alignment'=>'center' ),
            array( 'text'=> (string)$rowAux['pm']['cantidad'], 'alignment'=>'center' ),
            array( 'text'=> number_format($rowAux['pm']['monto'],2), 'alignment'=>'center' )
          );
        } 
        $arrTablesPrincipales[] = array( 
          array(
            array( 
              'table'=> array( 
                'widths'=> array('*','*','*','*'),
                'body'=> $arrTableDetalleHora
              ),
              'layout'=> 'lightHorizontalLines',
              'fontSize'=> 10,
              'margin'=> array(0,0,0,4)
            )
          ), 
          array()
        ); 
        /* PRODUCTOS */
        $arrTableDetalleProducto = array( 
          array(
            array( 'text'=> 'PRODUCTO', 'alignment'=>'left' ),
            array( 'text'=> 'CANT.', 'alignment'=>'center' ),
            array( 'text'=> 'MONTO', 'alignment'=>'center' )
          )
        );
        $listaTotalizadoProducto = $this->model_atencion_medica->lista_totalizado_producto_de_especialidad($allInputs,$row,$rowDet);
        $cantidadTotal = 0;
        $montoTotal = 0;
        foreach ($listaTotalizadoProducto as $keyAux => $rowDetProd) { 
          $arrTableDetalleProducto[] = array( 
            array( 'text'=> $rowDetProd['descripcion'], 'alignment'=>'left' ),
            array( 'text'=> (string)$rowDetProd['cantidad'], 'alignment'=>'center' ),
            array( 'text'=> $rowDetProd['monto'], 'alignment'=>'center' )
          ); 
          $cantidadTotal += $rowDetProd['cantidad'];
          $montoTotal += $rowDetProd['monto_num'];
        } 
        $arrTablesPrincipales[] = array( 
          array(
            array( 
              'table'=> array( 
                'widths'=> array('52%','*','*'),
                'body'=> $arrTableDetalleProducto
              ),
              'layout'=> 'lightHorizontalLines',
              'fontSize'=> 10
            )
          ), 
          array(
            array( 'text'=> 'CANTIDAD:                 '.(string)$cantidadTotal, 'alignment'=>'right' ),
            array( 'text'=> 'MONTO:      '.number_format($montoTotal,2), 'alignment'=>'right', 'bold'=>true ) 
          )
        );
        $arrTotalesFooter[] = array( );
      } // fin foreach listaMedicosAtencion
    }
    $arrContent[] = array( 
      $arrFiltros,
      $arrTablesPrincipales,
      $arrTotalesFooter
    );
    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs);    
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_consolidado_medico_excel()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    ini_set('max_execution_time', 600); 
    ini_set('memory_limit','2G'); 
    $writer = WriterFactory::create(Type::XLSX); 
    $fileName = $allInputs['titulo'].'.xlsx'; 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xlsx'; 
    $writer->openToFile($filePath); 
    // CREACION DE ESTILOS 
    $styleH1 = (new StyleBuilder())
           ->setFontBold()
           ->setFontSize(15)
           ->setFontColor(Color::WHITE)
           ->setShouldWrapText(FALSE) 
           ->setBackgroundColor(Color::rgb(142, 169, 219))
           ->build(); 

    $styleFootersHeaders = (new StyleBuilder())
           ->setFontBold()
           ->setFontColor(Color::BLACK) 
           ->setShouldWrapText(FALSE) 
           ->build(); 

    $styleH2 = (new StyleBuilder())
           ->setFontBold()
           ->setFontSize(15)
           ->setFontColor(Color::BLACK)
           ->setShouldWrapText(FALSE) 
           ->build(); 

    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $listaEspecialidadesVendidas = $this->model_atencion_medica->lista_especialidades_vendidas_entre_fechas($allInputs);
    }else{ 
      $listaEspecialidadesVendidas[] = array( 
        'idespecialidad' => $allInputs['especialidad']['id'],
        'especialidad' => $allInputs['especialidad']['descripcion']
      );
    }
    foreach ($listaEspecialidadesVendidas as $key => $row) { 

      if( $allInputs['medico']['idmedico'] == 'ALL' ){ 
        $listaMedicosAtencion = $this->model_atencion_medica->lista_medicos_atenciones_entre_fechas_de_especialidad($allInputs,$row); 
      }else{ 
        $listaMedicosAtencion[] = array(
          'idmedico' => $allInputs['medico']['idmedico'],
          'medico' => $allInputs['medico']['medico']
        );
      }
      $writer->addRowWithStyle(array($row['especialidad']),$styleH1); 
      foreach ($listaMedicosAtencion as $keyDet => $rowDet) { 
        $writer->addRowWithStyle(array($rowDet['medico']),$styleH2); 
        $listaTotalizadoAtencion = $this->model_atencion_medica->lista_totalizado_atenciones_entre_fechas_de_especialidad($allInputs,$row,$rowDet);
        $arrGroupByFecha = array();
        foreach ($listaTotalizadoAtencion as $keyAM => $rowAM) { 
          if( (int)$rowAM['hora'] >=  (int)$allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana'] && (int)$rowAM['hora'] <  (int)$allInputs['horaHastaManana'].$allInputs['minutoHastaManana'] ){
            $rowAMAux['fecha'] = $rowAM['fecha'];
            $arrGroupByFecha[$rowAM['fecha']] = $rowAMAux;
          }
          if( (int)$rowAM['hora'] >=  (int)$allInputs['horaDesdeTarde'].$allInputs['minutoDesdeTarde'] && (int)$rowAM['hora'] <  (int)$allInputs['horaHastaTarde'].$allInputs['minutoHastaTarde'] ){
            $rowAMAux['fecha'] = $rowAM['fecha'];
            $arrGroupByFecha[$rowAM['fecha']] = $rowAMAux;
          }
        }
        foreach ($arrGroupByFecha as $keyAux => $rowAux) { 
          $arrGroupByFecha[$rowAux['fecha']]['am'] = array('cantidad' => null,'monto'=>null);
          $arrGroupByFecha[$rowAux['fecha']]['pm'] = array('cantidad' => null,'monto'=>null);
          foreach ($listaTotalizadoAtencion as $keyAM => $rowAM) { 
            if($rowAux['fecha'] == $rowAM['fecha']){ 
              if( (int)$rowAM['hora'] >= (int)($allInputs['horaDesdeManana'].$allInputs['minutoDesdeManana']) && (int)$rowAM['hora'] <  (int)($allInputs['horaHastaManana'].$allInputs['minutoHastaManana']) ){
                $arrGroupByFecha[$rowAM['fecha']]['am']['cantidad'] += $rowAM['cantidad']; 
                $arrGroupByFecha[$rowAM['fecha']]['am']['monto'] += $rowAM['total_detalle']; 
              }
              if( (int)$rowAM['hora'] >= (int)($allInputs['horaDesdeTarde'].$allInputs['minutoDesdeTarde']) && (int)$rowAM['hora'] <  (int)($allInputs['horaHastaTarde'].$allInputs['minutoHastaTarde']) ){ 
                $arrGroupByFecha[$rowAM['fecha']]['pm']['cantidad'] += $rowAM['cantidad']; 
                $arrGroupByFecha[$rowAM['fecha']]['pm']['monto'] += $rowAM['total_detalle']; 
              }
            }
          }
        }
        /* HORAS */
        $writer->addRowWithStyle(array('FECHA', 'HORA', 'CANT.', 'MONTO'),$styleFootersHeaders); 
        foreach ($arrGroupByFecha as $keyAux => $rowAux) { 
          $rowFechaAux = $rowAux['fecha']; 
          $writer->addRow( 
            array(
              date('d/m/Y',strtotime("$rowFechaAux")), 
              $allInputs['horaDesdeManana'].':'.$allInputs['minutoDesdeManana'].' - '.$allInputs['horaHastaManana'].':'.$allInputs['minutoHastaManana'], 
              (string)$rowAux['am']['cantidad'], 
              number_format($rowAux['am']['monto'],2)
            )
            
          ); 
          $writer->addRow( 
            array( 
              date('d/m/Y',strtotime("$rowFechaAux")), 
              $allInputs['horaDesdeTarde'].':'.$allInputs['minutoDesdeTarde'].' - '.$allInputs['horaHastaTarde'].':'.$allInputs['minutoHastaTarde'], 
              (string)$rowAux['pm']['cantidad'], 
              number_format($rowAux['pm']['monto'],2) 
            ) 
          ); 
        }
        $writer->addRowWithStyle(array('PRODUCTO', 'CANT.', 'MONTO'),$styleFootersHeaders); 
        $listaTotalizadoProducto = $this->model_atencion_medica->lista_totalizado_producto_de_especialidad($allInputs,$row,$rowDet);
        $cantidadTotal = 0;
        $montoTotal = 0;
        foreach ($listaTotalizadoProducto as $keyAux => $rowDetProd) { 
          $writer->addRow(array($rowDetProd['descripcion'], (string)$rowDetProd['cantidad'], $rowDetProd['monto'])); 
          $cantidadTotal += $rowDetProd['cantidad'];
          $montoTotal += $rowDetProd['monto_num'];
        } 
        $writer->addRowWithStyle(array("TOTAL", (string)$cantidadTotal, number_format($montoTotal,2) ) ,$styleFootersHeaders); 
      } // fin foreach listaMedicosAtencion 
      $writer->addRow(array(''));
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
  public function report_consolidado_especialidad(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
     

    $arrFiltros = array();
    $arrTablesPrincipales = array();
    $arrTotalesFooter = array();

    $arrFiltros = array( 
      array(
        'text'=> array(' ')
      ),
      array(
        'text'=> array(
            array(
              'text'=>'DESDE: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['desde']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'HASTA: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['hasta']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'ESPECIALIDAD: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['especialidad']['descripcion']
        )
      ),
      // array(
      //   'text'=> array(
      //       array(
      //         'text'=>'MEDICO: ',
      //         'style'=> 'filterTitle'
      //       ),
      //       $allInputs['medico']['medico']
      //   )
      // ),
      array(
        'text'=> array(' ')
      )
    );
    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $listaEspecialidadesVendidas = $this->model_atencion_medica->lista_especialidades_vendidas_entre_fechas($allInputs);
    }else{ 
      $listaEspecialidadesVendidas[] = array(
        'idespecialidad' => $allInputs['especialidad']['id'],
        'especialidad' => $allInputs['especialidad']['descripcion']
      );
    }
    //var_dump($listaEspecialidadesVendidas); exit();
     foreach ($listaEspecialidadesVendidas as $key => $row) { 
      $arrTablesPrincipales[] = array(
        array(
          array( 
            'table'=> array( 
              'widths'=> array('100%'),
              'body'=> array(
                array(
                  array('text'=>$row['especialidad'], 'alignment'=> 'center'),

                )
              )
            ),
            'margin'=> array(0,14,0,1),
            'bold'=> true,
            'fontSize'=> 13
          )
        ), 
        array()
      ); 
      /* PRODUCTOS */
      $arrTableDetalleProducto = array( 
        array(
          array( 'text'=> 'PRODUCTO', 'alignment'=>'center', 'bold'=>true ),
          array( 'text'=> 'CANT.', 'alignment'=>'center', 'bold'=>true ),
          array( 'text'=> 'MONTO', 'alignment'=>'center', 'bold'=>true )
        )
      );
      $listaTotalizadoProducto = $this->model_atencion_medica->lista_totalizado_producto_por_especialidades($allInputs,$row);
      $cantidadTotal = 0;
      $montoTotal = 0;
      $arrmonto = array();
      foreach ($listaTotalizadoProducto as $keyAux => $rowDetProd) {
        if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
          $rowDetProd['monto']='-';
        }
        $arrTableDetalleProducto[] = array( 
          array( 'text'=> $rowDetProd['descripcion'], 'alignment'=>'left' ),
          array( 'text'=> (string)$rowDetProd['cantidad'], 'alignment'=>'center' ),
          array( 'text'=> $rowDetProd['monto'], 'alignment'=>'center' )
        ); 
        $cantidadTotal += $rowDetProd['cantidad'];
        $montoTotal += $rowDetProd['monto_num'];
      }
      if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
        $arrmonto = array();
      }else{
        $arrmonto = array( 'text'=> 'MONTO:      '.number_format($montoTotal,2), 'alignment'=>'right', 'bold'=>true );
      }
      $arrTableDetalleProducto[] = array( 
        array( 'text'=> 'TOTAL', 'alignment'=>'right', 'bold'=> true ), 
        array( 'text'=> (string)$cantidadTotal, 'alignment'=>'center', 'bold'=> true ),
        array( 'text'=> number_format($montoTotal,2), 'alignment'=>'center', 'bold'=> true )
      );
      $arrTablesPrincipales[] = array( 
        array(
          array( 
            'table'=> array( 
              'widths'=> array('52%','*','*'),
              'body'=> $arrTableDetalleProducto,
            ),
            //'layout'=> 'lightHorizontalLines',
            'fontSize'=> 10
          )
        ), 
        array()
      );
    }
    //var_dump($arrTablesPrincipales); exit();
    $arrContent[] = array( 
      $arrFiltros,
      $arrTablesPrincipales
      //$arrTotalesFooter
    );

    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs);
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_consolidado_especialidad_excel(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    //$arrFiltros = array();
    $arrTablesPrincipales = array();
    $arrTotalesFooter = array();
    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $listaEspecialidadesVendidas = $this->model_atencion_medica->lista_especialidades_vendidas_entre_fechas($allInputs);
    }else{ 
      $listaEspecialidadesVendidas[] = array(
        'idespecialidad' => $allInputs['especialidad']['id'],
        'especialidad' => $allInputs['especialidad']['descripcion']
      );
    }
    // var_dump($allInputs); exit(); 
    foreach ($listaEspecialidadesVendidas as $key => $row) { 
    
      /* PRODUCTOS */
      $arrTableDetalleProducto = array( 
        array(
          array( 'text'=> 'PRODUCTO', 'alignment'=>'left' ),
          array( 'text'=> 'CANT.', 'alignment'=>'center' ),
          array( 'text'=> 'MONTO', 'alignment'=>'center' )
        )
      );
      $listaTotalizadoProducto = $this->model_atencion_medica->lista_totalizado_producto_por_especialidades($allInputs,$row);
      $cantidadTotal = 0;
      $montoTotal = 0;
      $arrmonto = array();
      //var_dump($listaTotalizadoProducto); exit();
      foreach ($listaTotalizadoProducto as $keyAux => $rowDetProd) {
        if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
          $rowDetProd['monto']='-';
        }
        $arrTableDetalleProducto[] = array( 
          array( 'text'=> $rowDetProd['descripcion'], 'alignment'=>'left' ),
          array( 'text'=> (string)$rowDetProd['cantidad'], 'alignment'=>'center' ),
          array( 'text'=> $rowDetProd['monto'], 'alignment'=>'center' )
        ); 
        $cantidadTotal += $rowDetProd['cantidad'];
        $montoTotal += $rowDetProd['monto_num'];
      }
      if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
        $arrmonto = array();
      }else{
        $arrmonto = array( 'text'=> 'MONTO:      '.number_format($montoTotal,2), 'alignment'=>'right', 'bold'=>true );
      }
      //activate worksheet number 1
      $this->excel->setActiveSheetIndex(0);
      
      //name the worksheet
      $this->excel->getActiveSheet()->setTitle('CONSOLIDADO ()');
      //$this->excel->getActiveSheet()->setAutoFilter('B3:I3');

      $styleArrayTitle = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 14,
            'name'  => 'Verdana'
        )
      );

      $this->excel->getActiveSheet()->getCell('F1')->setValue('CONSOLIDADO');
      $this->excel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArrayTitle);

      //$cont++;

      $dataColumnsTP = array( 
        array('ESPECIALIDAD', 'PRODUCTO', 'CANTIDAD', 'MONTO')
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
            // 'color' => array('rgb' => 'FF0000'),
            'size'  => 10,
            'name'  => 'Verdana'
        )
      );
      foreach(range('B','Z') as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
      }
      $this->excel->getActiveSheet()->getStyle('B3:I3')->applyFromArray($styleArray);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'B3');
      $this->excel->getActiveSheet()->fromArray($arrTableDetalleProducto, null, 'C4');
     

      $styleTitleNCR = array(
          'font' => array(
              'bold'  => true,
              'size'  => 14,
          )
      );
      
      
      
    }
    //var_dump($arrTablesPrincipales); exit();
    
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
  public function report_consolidado_ventas_especialidad(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrFiltros = array();
    $arrTablesPrincipales = array();
    $arrTotalesFooter = array();

    $arrFiltros = array( 
      array(
        'text'=> array(' ')
      ),
      array(
        'text'=> array(
            array(
              'text'=>'DESDE: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['desde']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'HASTA: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['hasta']
        )
      ),
      array(
        'text'=> array(
            array(
              'text'=>'ESPECIALIDAD: ',
              'style'=> 'filterTitle'
            ),
            $allInputs['especialidad']['descripcion']
        )
      ),
      array(
        'text'=> array(' ')
      )
    );
    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $listaEspecialidadesVendidas = $this->model_atencion_medica->lista_productos_vendidos_por_especialidad_entre_fechas($allInputs);
    }else{ 
      $listaEspecialidadesVendidas[] = array(
        'idespecialidad' => $allInputs['especialidad']['id'],
        'especialidad' => $allInputs['especialidad']['descripcion']
      );
    }
    //var_dump($listaEspecialidadesVendidas); exit();
     foreach ($listaEspecialidadesVendidas as $key => $row) { 
      $arrTablesPrincipales[] = array(
        array(
          array( 
            'table'=> array( 
              'widths'=> array('100%'),
              'body'=> array(
                array(
                  array('text'=>$row['especialidad'], 'alignment'=> 'center'),

                )
              )
            ),
            'margin'=> array(0,14,0,1),
            'bold'=> true,
            'fontSize'=> 13
          )
        ), 
        array()
      ); 
      /* PRODUCTOS */
      $arrTableDetalleProducto = array( 
        array(
          array( 'text'=> 'PRODUCTO', 'alignment'=>'center', 'bold'=>true ),
          array( 'text'=> 'CANT.', 'alignment'=>'center', 'bold'=>true ),
          array( 'text'=> 'MONTO', 'alignment'=>'center', 'bold'=>true )
        )
      );
      $listaTotalizadoProducto = $this->model_atencion_medica->lista_totalizado_producto_vendido_por_especialidades($allInputs,$row);
      $cantidadTotal = 0;
      $montoTotal = 0;
      $arrmonto = array();
      foreach ($listaTotalizadoProducto as $keyAux => $rowDetProd) {
        if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
          $rowDetProd['monto']='-';
        }
        $arrTableDetalleProducto[] = array( 
          array( 'text'=> $rowDetProd['descripcion'], 'alignment'=>'left' ),
          array( 'text'=> (string)$rowDetProd['cantidad'], 'alignment'=>'center' ),
          array( 'text'=> $rowDetProd['monto'], 'alignment'=>'center' )
        ); 
        $cantidadTotal += $rowDetProd['cantidad'];
        $montoTotal += $rowDetProd['monto_num'];
      }
      if($this->sessionHospital['key_group'] != 'key_sistemas' && $this->sessionHospital['key_group'] != 'key_gerencia' ){ 
        $arrmonto = array();
      }else{
        $arrmonto = array( 'text'=> 'MONTO:      '.number_format($montoTotal,2), 'alignment'=>'right', 'bold'=>true );
      }
      $arrTableDetalleProducto[] = array( 
        array( 'text'=> 'TOTAL', 'alignment'=>'right', 'bold'=> true ), 
        array( 'text'=> (string)$cantidadTotal, 'alignment'=>'center', 'bold'=> true ),
        array( 'text'=> number_format($montoTotal,2), 'alignment'=>'center', 'bold'=> true )
      );
      $arrTablesPrincipales[] = array( 
        array(
          array( 
            'table'=> array( 
              'widths'=> array('52%','*','*'),
              'body'=> $arrTableDetalleProducto,
            ),
            //'layout'=> 'lightHorizontalLines',
            'fontSize'=> 10
          )
        ), 
        array()
      );
    }
    //var_dump($arrTablesPrincipales); exit();
    $arrContent[] = array( 
      $arrFiltros,
      $arrTablesPrincipales
      //$arrTotalesFooter
    );

    $arrDataPDF = getPlantillaGeneralReporte($arrContent,$allInputs);
    $arrData['dataPDF'] = $arrDataPDF;
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_consolidado_ventas_especialidad_excel(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
  }
  public function report_kardex(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    //var_dump($allInputs); exit();
    //activate worksheet number 1
    $this->excel->setActiveSheetIndex(0);
    //name the worksheet
    $this->excel->getActiveSheet()->setTitle('KARDEX VALORIZADO');
    //$this->excel->getActiveSheet()->setAutoFilter('B3:I3');
    // ESTILOS
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
      // $styleArrayEncabezado = array(
      //   'font'=>  array(
      //       'bold'  => true,
      //       'size'  => 11,
      //       'name'  => 'calibri'
      //   ),
      //   'fill' => array( 
      //       'type' => PHPExcel_Style_Fill::FILL_SOLID,
      //       'startcolor' => array( 'rgb' => 'FFF2CC', ),
      //    ),
      //   'borders' => array(
      //     'allborders' => array( 
      //       'style' => PHPExcel_Style_Border::BORDER_THIN,
      //       'color' => array('rgb' => '000000') 
      //     ) 
      //   ),
      // );
      $styleArray = array(
        'font'=>  array(
            'bold'  => true,
            // 'color' => array('rgb' => 'FF0000'),
            'size'  => 10,
            'name'  => 'Verdana'
        )
      );
    $hoy = date('d/m/Y - H:i:s');
    $this->excel->getActiveSheet()->getCell('A2')->setValue('KARDEX VALORIZADO AL ' . $hoy);
    $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A2:J2');


    $this->excel->getActiveSheet()->getStyle('A4:A7')->applyFromArray($styleArray);
    
    $this->excel->getActiveSheet()->getCell('A4')->setValue('');
    $this->excel->getActiveSheet()->getCell('A5')->setValue('PRODUCTO');
    $this->excel->getActiveSheet()->getCell('A6')->setValue('ALMACEN');
    $this->excel->getActiveSheet()->getCell('A7')->setValue('SUBALMACEN');
    
    $this->excel->getActiveSheet()->getCell('B4')->setValue('');
    $this->excel->getActiveSheet()->getCell('B5')->setValue($allInputs['busqueda']['producto']);
    $this->excel->getActiveSheet()->getCell('B6')->setValue($allInputs['busqueda']['almacen']['descripcion']);
    $this->excel->getActiveSheet()->getCell('B7')->setValue($allInputs['busqueda']['subalmacen']['descripcion']);

    if( !empty($allInputs['busqueda']['desde']) && !empty($allInputs['busqueda']['hasta']) ){
        $this->excel->getActiveSheet()->getStyle('D4:D6')->applyFromArray($styleArray);
        $this->excel->getActiveSheet()->getCell('D4')->setValue('PERIODO');
        $this->excel->getActiveSheet()->getCell('D5')->setValue('DESDE');
        $this->excel->getActiveSheet()->getCell('D6')->setValue('HASTA');
        $this->excel->getActiveSheet()->getCell('E5')->setValue($allInputs['busqueda']['desde']);
        $this->excel->getActiveSheet()->getCell('E6')->setValue($allInputs['busqueda']['hasta']);
    }

    $dataColumnsTP = array( 
      array('FECHA MOV.', 'MOVIMIENTO ', 'P.U.', 'ENTRADAS ', '', 'SALIDAS ', '', 'SALDO ', '', 'PRECIO PROMEDIO ')
    );
    $dataColumnsSUB = array( 
      array('CANTIDAD ', 'VALORES ', 'CANTIDAD ', 'VALORES ', 'CANTIDAD ', 'VALORES ')
    );
    $arrTableDetalleKardex = array();
    foreach ($allInputs['lista'] as $key => $row) {
      array_push($arrTableDetalleKardex, array(
          $row['fecha_movimiento'], 
          $row['tipo_movimiento'], 
          (string)$row['precio_unitario'], 
          (string)$row['entrada'],
          (string)$row['valor_entrada'],
          (string)$row['salida'],
          (string)$row['valor_salida'], 
          (string)$row['cantidad_saldo'],
          (string)$row['valor_saldo'],
          (string)$row['promedio']
        )
      );
    }

    $styleArrayEncabezado = array(
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
          'color' => array('rgb' => 'FFFFFF'),
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '4472c4', ),
         )
    );
    $styleArrayEncabezado2 = array(
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
          'color' => array('rgb' => 'FFFFFF'),
          'size'  => 10,
          'name'  => 'Verdana'
      ),
      'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '5b9bd5', ),
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
        'horizontal' => array( 
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => '00d4d4d4') 
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
      )
    );
    $endRows = count($arrTableDetalleKardex) + 11;

    $this->excel->getActiveSheet()->getStyle('A9:I10')->applyFromArray($styleArrayEncabezado);
    $this->excel->getActiveSheet()->getStyle('J9:J10')->applyFromArray($styleArrayEncabezado2);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A9');
    $this->excel->getActiveSheet()->fromArray($dataColumnsSUB, null, 'D10');

    $this->excel->getActiveSheet()->getStyle('A11:C' . $endRows)->applyFromArray($styleArrayTable);
    $this->excel->getActiveSheet()->getStyle('D11:E' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('D11:D' . $endRows)->applyFromArray($styleArrayTable3);
    $this->excel->getActiveSheet()->getStyle('F11:G' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('F11:F' . $endRows)->applyFromArray($styleArrayTable3);
    $this->excel->getActiveSheet()->getStyle('H11:I' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('H11:H' . $endRows)->applyFromArray($styleArrayTable3);
    $this->excel->getActiveSheet()->getStyle('J11:J' . $endRows)->applyFromArray($styleArrayTable2);
    $this->excel->getActiveSheet()->getStyle('A11:J' . $endRows)->applyFromArray($styleArrayContorno);

    $this->excel->getActiveSheet()->getStyle('D11:D' . $endRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $this->excel->getActiveSheet()->getStyle('D11:D' . $endRows)->getFill()->getStartColor()->setARGB('FFFFF2CC');

    $this->excel->getActiveSheet()->getStyle('H11:H' . $endRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $this->excel->getActiveSheet()->getStyle('H11:H' . $endRows)->getFill()->getStartColor()->setARGB('FFFFF2CC');

    $this->excel->getActiveSheet()->getStyle('F11:F' . $endRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $this->excel->getActiveSheet()->getStyle('F11:F' . $endRows)->getFill()->getStartColor()->setARGB('FFFFF2CC');

    $this->excel->getActiveSheet()->getStyle('C11:C' . $endRows)->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('E11:E' . $endRows)->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('G11:G' . $endRows)->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('I11:J' . $endRows)->getNumberFormat()->setFormatCode('#,##0.00');


    $this->excel->getActiveSheet()->fromArray($arrTableDetalleKardex, null, 'A11');

    $this->excel->getActiveSheet()->mergeCells('A9:A10');
    $this->excel->getActiveSheet()->mergeCells('B9:B10');
    $this->excel->getActiveSheet()->mergeCells('C9:C10');
    $this->excel->getActiveSheet()->mergeCells('D9:E9');
    $this->excel->getActiveSheet()->mergeCells('F9:G9');
    $this->excel->getActiveSheet()->mergeCells('H9:I9');
    $this->excel->getActiveSheet()->mergeCells('J9:J10');

    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(22);
    //$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    foreach(range('C','J') as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(12);
    }
    
    // TOTALES
    $this->excel->getActiveSheet()->getCell('D' . ($endRows + 2) )->setValue('=SUM(D11:D' . $endRows.')');
    $this->excel->getActiveSheet()->getCell('F' . ($endRows + 2) )->setValue('=SUM(F11:F' . $endRows.')');
    $this->excel->getActiveSheet()->getCell('H' . ($endRows + 2) )->setValue('=(D' . ($endRows + 2) . '-F' . ($endRows + 2) . ')');

    $this->excel->getActiveSheet()->getStyle('J9:J10')->getAlignment()->setWrapText(true); 
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
  public function report_tarifario_sede_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    if( !empty($allInputs['resultado']) ){
      $paramDatos = $allInputs['resultado'];
    }else{
      $paramDatos = $allInputs;
    }
    if( !empty($allInputs['paginate']) ){
      $paramPaginate = $allInputs['paginate'];
      $paramPaginate['firstRow'] = 0;
      $paramPaginate['pageSize'] = 0;
    }else{
      $paramPaginate = FALSE;
    }
    $arrData['flag'] = 0;
    
    $cont = 0;
    $i = 1;

    $lista = $this->model_producto->m_cargar_productos($paramPaginate,$paramDatos);
    $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($paramDatos['sedeempresa']);
    $empresa_sede = $empresa_admin['razon_social'] . ' / ' . $empresa_admin['sede'];
    $currentCellEncabezado = 6;
    $maxCol = 'G';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      switch ($row['estado_pps']) {
          case 1:
              $estado = '';
              break;
          case 2:
              $estado = 'DESHABILITADO';
              break;
          default:
              break;
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          strtoupper(trim($row['idproductomaster'])),
          strtoupper(trim($row['producto'])),
          $row['precio_sf'],
          strtoupper(trim($row['especialidad'])),
          strtoupper(trim($row['nombre_tp'])),
          $estado

        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','COD. PROD.', 'PRODUCTO','PRECIO (S/.)', 'ESPECIALIDAD', 'TIPO DE PRODUCTO','ESTADO')
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle('Tarifario ' . date('d-m-Y'));
    $this->excel->getActiveSheet()->getTabColor()->setRGB('4472C4');
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
          'startcolor' => array( 'rgb' => '4472C4', ),
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
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo'] . ' AL ' . date('d-m-Y')); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    $this->excel->getActiveSheet()->getCell('A3')->setValue('EMPRESA / SEDE:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($empresa_sede);
    $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayEncabezado);
    $this->excel->getActiveSheet()->mergeCells('A3:B3');

    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(75);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(32);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');



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
  public function report_empleados_lista_general()
  {
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    // if( !empty($allInputs['resultado']) ){
    //   $paramDatos = $allInputs['resultado'];
    // }else{
    //   $paramDatos = $allInputs;
    // }
    // if( !empty($allInputs['paginate']) ){
    //   $paramPaginate = $allInputs['paginate'];
    //   $paramPaginate['firstRow'] = 0;
    //   $paramPaginate['pageSize'] = 0;
    // }else{
    //   $paramPaginate = FALSE;
    // } 
    $arrData['flag'] = 0;
    
    $cont = 0;
    //$i = 1;

    $lista = $this->model_empleado->m_cargar_empleados_general_excel();
    //$empresa_sede = $empresa_admin['razon_social'] . ' / ' . $empresa_admin['sede']; 
    //$currentCellEncabezado = 6;
    
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) { 
      array_push($arrListadoProd, 
        array(
          //$i++,
          $row['idempleado'],
          $row['numero_documento'],
          $row['nombres'],
          $row['apellido_paterno'],
          $row['apellido_materno'],
          // $row['operador_movil'],
          $row['telefono'],
          $row['telefono_fijo'],
          $row['correo_electronico'],
          $row['direccion'],
          $row['estado_civil'],
          // $row['referencia'],
          $row['salario_basico'],
          $row['sexo'],
          $row['fecha_nacimiento'],
          $row['ruc_empleado'],
          $row['grupo_sanguineo'],
          $row['fecha_caducidad_coleg'],
          $row['departamento'],
          $row['provincia'],
          $row['distrito'],
          $row['area'],
          $row['descripcion_ca'],
          $row['cargo_superior'],
          $row['empresa'],
          $row['sede'],
          $row['especialidad'],
          $row['colegiatura_profesional'],
          // $row['reg_nac_especialista'],
          $row['descripcion_prf'],
          $row['reg_pensionario'],
          $row['descripcion_afp'],
          $row['condicion_laboral'],
          $row['fecha_ingreso'],
          $row['fecha_inicio_contrato'],
          $row['fecha_fin_contrato'],
          $row['es_tercero'],
          $row['marca_asistencia'],
          $row['es_ipress'],
          $row['es_privado'],
          $row['username'],
          $row['centro_estudio'],
          $row['estudio_completo'],
          $row['grado_academico'],
          $row['fecha_desde_estudio'],
          $row['fecha_hasta_estudio']
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('COD.','DNI', 'NOMBRES','AP. PATERNO', 'AP. MATERNO'/*, 'OPERADOR'*/,'CELULAR','TEL. FIJO','CORREO','DIRECCIÓN','ESTADO CIVIL'/*,'REFERENCIA'*/,'SUELDO','SEXO','FECHA NAC.','RUC','TIPO DE SANGRE',
        'FECHA CAD. HABILIDAD','DEPARTAMENTO','PROVINCIA','DISTRITO','AREA','CARGO','CARGO DEL SUPERIOR','EMPRESA','SEDE','ESPECIALIDAD','CPM',/*'RNE',*/'PROFESIÓN','REG. PENSIONARIO','AFP','CONDICIÓN LAB.',
        'FECHA ING.','FECHA INICIO CONTRATO VIG.','FECHA FIN CONTRATO VIG.','ES TERCERO','MARCA ASISTENCIA','ES IPRESS','ES PRIVADO','USUARIO','CENTRO ESTUDIOS','STATUS','GRADO ACAD.','DESDE','HASTA') 
    );
    // $this->excel->setActiveSheetIndex($cont);
    // $this->excel->getActiveSheet()->setTitle('Tarifario ' . date('d-m-Y'));
    // $this->excel->getActiveSheet()->getTabColor()->setRGB('4472C4');
    // $styleArrayTitle = array(
    //   'font'=>  array(
    //       'bold'  => true,
    //       'size'  => 14,
    //       'name'  => 'Verdana'
    //   ),
    //   'alignment' => array(
    //       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    //       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    //   ),
    // );
    
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
          'startcolor' => array( 'rgb' => '4472C4', ),
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
    // $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo'] . ' AL ' . date('d-m-Y')); 
    // $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    // $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    // $this->excel->getActiveSheet()->getCell('A3')->setValue('EMPRESA / SEDE:');
    // $this->excel->getActiveSheet()->getCell('C3')->setValue($empresa_sede);
    // $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArrayEncabezado);
    // $this->excel->getActiveSheet()->mergeCells('A3:B3');

    // $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    // SETEO DE ANCHO DE COLUMNAS
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AF')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AH')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AI')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AK')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AL')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AM')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AN')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AO')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AP')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AQ')->setWidth(20);
    $this->excel->getActiveSheet()->getColumnDimension('AR')->setWidth(20);

    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    $currentCellEncabezado = 1; 
    $maxCol = 'AR';
    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    // $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');



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
  public function report_medicos_por_especialidad_rne_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    // if( @$allInputs['allEmpleados'] === TRUE ){
    //   $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia($allInputs['empresa']);
    // }else{
    //   $listaEmpleados[] = $this->model_empleado->m_cargar_este_empleado_por_codigo($allInputs['empleado']);
    // }

    $arrData['flag'] = 0;
    
    $cont = 0;
    $maxCol = 'H';
    $currentCellEncabezado = 3;
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle('Asistencia Por Empleado');
    $this->excel->getActiveSheet()->getTabColor()->setRGB('4472C4');
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
    $styleArrayEncabezado2 = array(
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $styleArrayEncabezado3 = array(
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    // TITULO Y ENCABEZADO
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    /* datos de cabecera*/
    $this->excel->getActiveSheet()->getCell('A'.$currentCellEncabezado)->setValue('SEDE');
    $this->excel->getActiveSheet()->getCell('C'.$currentCellEncabezado)->setValue(':');
    $this->excel->getActiveSheet()->getCell('D'.$currentCellEncabezado)->setValue($allInputs['sede']['descripcion']);
    $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+1))->setValue('ESPECIALIDAD(ES)');
    $this->excel->getActiveSheet()->getCell('D'.($currentCellEncabezado+1))->setValue(':');

    $allInputs['arrEspecialidadesSeleccionadas'] = array();
    $indiceEsp = 2;
    foreach ($allInputs['especialidadesSeleccionadas'] as $key => $row) {
      $this->excel->getActiveSheet()->getCell('B'.($currentCellEncabezado+$indiceEsp))->setValue($row['descripcion']);
      $allInputs['arrEspecialidadesSeleccionadas'][] = $row['id'];
      $indiceEsp++;
    }

    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':A'.($currentCellEncabezado+1))->applyFromArray($styleArrayEncabezado);
    $this->excel->getActiveSheet()->getStyle('C'.$currentCellEncabezado.':C'.($currentCellEncabezado+1))->applyFromArray($styleArrayEncabezado2);
    $this->excel->getActiveSheet()->getStyle('D'.$currentCellEncabezado.':D'.($currentCellEncabezado+1))->applyFromArray($styleArrayEncabezado3);
    // var_dump($lista); exit();
    $currentCellEncabezado = $currentCellEncabezado+$indiceEsp+2;
    
    $dataColumnsTP = array( 
      array('ITEM','DNI','RNE', 'EMPLEADO','CARGO','EMPRESA', 'FECHA NAC.','PROFESION')
    );
    // SETEO DE ANCHO DE COLUMNAS
    $colWidth = array(10, 12, 15, 50, 50, 50, 15, 50);
    $i=0;
    foreach(range('A',$maxCol) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($colWidth[$i++]);
    }
    
    // TRATAMIENTO DE DATOS //
    //$allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empleado->m_cargar_medicos_reporte_especialidad($allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array(
        'idespecialidad'=> $row['idespecialidad'],
        'especialidad'=> $row['especialidad'],
        'colaboradores'=> array()
      );
      $arrMainArray[$row['idespecialidad']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idempleado'=> $row['idempleado'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'reg_nac_especialista'=> $row['reg_nac_especialista'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'nombre_foto'=> $row['nombre_foto'],
        'descripcion_prf'=> $row['descripcion_prf'],
        'fecha_nacimiento'=> $row['fecha_nacimiento']
      );
      $arrMainArray[$row['idespecialidad']]['colaboradores'][$row['idempleado']] = $rowAux;
    }
    // CREACION DE LA GRILLA
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
          'size'  => 12,
          'name'  => 'Verdana',
          'color' => array('rgb' => '000000') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => 'FFFFFF', ),
       ),
    );
    $styleArrayHeader2 = array(
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
          'startcolor' => array( 'rgb' => '4472C4', ),
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
      'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      ),
    );
    foreach ($arrMainArray as $keyPrin => $rowPrin) {
      $arrListadoProd = array();
      $contador = 1;
      $this->excel->getActiveSheet()->getCell('A'.$currentCellEncabezado)->setValue(strtoupper($rowPrin['especialidad'])); 
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->mergeCells('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado);
      // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
      $currentCellEncabezado++;
      $currentCellTotal = count($rowPrin['colaboradores']) + $currentCellEncabezado;

      // ENCABEZADO PRINCIPAL DE LA LISTA
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader2);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) {
        array_push($arrListadoProd, array(
          $contador++,
          $row['numero_documento'],
          $row['reg_nac_especialista'],
          strtoupper($row['empleado']),
          strtoupper($row['cargo']),
          strtoupper($row['empresa']),
          $row['fecha_nacimiento'],
          strtoupper($row['descripcion_prf'])
          )
        );
      }

      // DATOS
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

      $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $currentCellEncabezado = $currentCellTotal+6;
      //$cont++;
    }
    /**/
    
    

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
  public function report_solicitudes_procedimiento_excel(){
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

    $lista = $this->model_solicitud_procedimiento->m_cargar_solicitudes_procedimiento_session($paramPaginate,$paramDatos);


    $currentCellEncabezado = 4;
    $maxCol = 'I';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      if( $row['estado_sp'] == 1 ){
        if( $row['paciente_atendido_det'] == 1 || $row['paciente_atendido_det'] == 2 ){
          $estado = 'VENDIDO';
        }else{
          $estado = 'SOLICITADO';
        }
      }elseif( $row['estado_sp'] == 0 ){ // ANULADO
        $estado = 'ANULADO';
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['idsolicitudprocedimiento'],
          formatoFechaReporte3($row['fecha_solicitud']),
          strtoupper($row['idhistoria']),
          strtoupper($row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', '. $row['nombres']),
          strtoupper($row['producto']),
          strtoupper($row['especialidad']),
          strtoupper($row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', '. $row['med_nombres']),
          
          $estado
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','COD.','FECHA SOL.','HISTORIA', 'PACIENTE','PRODUCTO', 'ESPECIALIDAD','MEDICO','ESTADO' )
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
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(13);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    // $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':J'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('C'.($currentCellEncabezado+1).':C'.($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    



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
  public function report_solicitudes_examen_auxiliar_excel(){
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

    $lista = $this->model_solicitud_examen->m_cargar_solicitudes_examen_session($paramPaginate,$paramDatos);
    $currentCellEncabezado = 4;
    $maxCol = 'I';
    // var_dump($lista); exit(); 
    $arrListadoProd = array();
    foreach ($lista as $row) {
      if( $row['estado_sex'] == 1 ){
        if( $row['paciente_atendido_det'] == 1 || $row['paciente_atendido_det'] == 2 ){
          $estado = 'VENDIDO';
        }else{
          $estado = 'SOLICITADO';
        }
      }elseif( $row['estado_sex'] == 0 ){ // ANULADO
        $estado = 'ANULADO';
      }
      array_push($arrListadoProd, 
        array(
          $i++,
          $row['idsolicitudexamen'],
          formatoFechaReporte3($row['fecha_solicitud']),
          strtoupper($row['idhistoria']),
          strtoupper($row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', '. $row['nombres']),
          strtoupper($row['producto']),
          strtoupper($row['especialidad']),
          strtoupper($row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', '. $row['med_nombres']),
          
          $estado
        )
      );
      
    }
    $dataColumnsTP = array( 
      array('ITEM','COD.','FECHA SOL.','HISTORIA', 'PACIENTE','PRODUCTO', 'ESPECIALIDAD','MEDICO','ESTADO' )
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
    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(13);
    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(37);
    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(13);
    // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // ENCABEZADO DE LA LISTA
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    // DATOS
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

    // $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':J'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('C'.($currentCellEncabezado+1).':C'.($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    



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
  public function report_ingresos_por_especialidad()
  { 
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    /* SET VAR */
    $currentCellEncabezado = 4;
    $longMonthArray = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    // $arrMesesNumerico = array('1','2','3','4','5','6','7','8','9','10','11','12',)
    $maxCol = 'M';
    $cont = 0;
    $i = 1;
    $dataColumnsTP = array( 
      $longMonthArray
    );
    $dataColumnsConsultas = array( 
      array('Consultas' )
    );
    $this->excel->setActiveSheetIndex($cont);
    $this->excel->getActiveSheet()->setTitle($allInputs['sede']['descripcion']); 
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
    $styleArrayHeaderSecundario = array(
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
          'color' => array('rgb' => '000000') 
      ),
      'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '92CDDC', ),
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
    // SETEO DE ANCHO DE COLUMNAS
    $columnas = range('B',$maxCol);
    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
    foreach($columnas as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(13);
    }
    // OBTENCION DE DATOS FIJOS
    $especialidades = $this->model_especialidad->m_cargar_especialidades();
    $arrEspecialidades = array();
    foreach ($especialidades as $key => $especialidad) {
      array_push($arrEspecialidades, 
        array(
          $especialidad['nombre']
        )
      );
    }
    /* titulo */
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo'] . ' - ' . $allInputs['anioDesdeCbo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');

    // ENCABEZADO DE LA LISTA (meses)
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'B'.$currentCellEncabezado); // meses
    // CONSULTAS
    $currentCellDatos1 = $currentCellEncabezado+3;
    $this->excel->getActiveSheet()->getCell('A'.($currentCellDatos1))->setValue('Consulta');
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellDatos1).':'.$maxCol.($currentCellDatos1))->applyFromArray($styleArrayHeaderSecundario);
    $currentCellTotal = count($arrEspecialidades) + $currentCellDatos1+1;
    // datos
    $this->excel->getActiveSheet()->fromArray($arrEspecialidades, null, 'A'.($currentCellDatos1+1));
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellDatos1+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellDatos1).':'. $maxCol .($currentCellTotal))->getNumberFormat()->setFormatCode('_ "S/." * #,##0.00_ ;_ "S/." * -#,##0.00_ ;_ "S/." * "-"??_ ;_ @_ ');
    // PROCEDIMIENTOS
    $currentCellDatos2 = $currentCellTotal+2;
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellDatos2).':'.$maxCol.($currentCellDatos2))->applyFromArray($styleArrayHeaderSecundario);
    $this->excel->getActiveSheet()->getCell('A'.($currentCellDatos2))->setValue('Procedimientos/Exámenes');
    $currentCellTotalProced = count($arrEspecialidades) + $currentCellDatos2 + 1;
    //datos
    $this->excel->getActiveSheet()->fromArray($arrEspecialidades, null, 'A'.($currentCellDatos2+1));
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellDatos2+1).':'.$maxCol.$currentCellTotalProced)->applyFromArray($styleArrayProd);
     $this->excel->getActiveSheet()->getStyle('B'.($currentCellDatos2).':'. $maxCol .($currentCellTotalProced))->getNumberFormat()->setFormatCode('_ "S/." * #,##0.00_ ;_ "S/." * -#,##0.00_ ;_ "S/." * "-"??_ ;_ @_ ');
    // TOTALIZADO
    $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+1))->setValue('MONTO RECAUDADO');
  
    
    //var_dump($especialidades);exit();


    // PREPARACION DE DATOS

    foreach ($longMonthArray as $keyMaster => $mes) { // por meses
      $currentCellEncabezado = 4;
      $allInputs['mes']['id'] = $keyMaster + 1;
      $allInputs['mes']['mes'] = $mes;

      $lista = $this->model_venta->m_cargar_ingresos_mensuales_por_especialidad($allInputs);
      $arrListadoConsultas = array();
      $arrListadoProcedimientos = array();
      $monto_consulta = '0';
      $monto_lo_demas = '0';

      foreach ($lista as $row) {
        if( floatval($row['solo_consultas']) + floatval($row['nota_credito']) >= 0){
          // $monto_consulta = floatval($row['solo_consultas']) + floatval($row['nota_credito']);
          // $monto_lo_demas = floatval($row['lo_demas']);
          if( floatval($row['nota_credito']) < 0 ){
            $monto_consulta = '=' . floatval($row['solo_consultas']) .  floatval($row['nota_credito']);
          }else{
            $monto_consulta = '=' . floatval($row['solo_consultas']) . '+' .  floatval($row['nota_credito']);
          }
          $monto_lo_demas = '=' . floatval($row['lo_demas']);
        }
        else{
          if( floatval($row['nota_credito']) < 0 ){
            $monto_lo_demas = '=' . floatval($row['lo_demas']) . floatval($row['nota_credito']);
          }else{
            $monto_lo_demas = '=' . floatval($row['lo_demas']) . '+' .  floatval($row['nota_credito']);
          }
          $monto_consulta = '=' . floatval($row['solo_consultas']);
          
          // $monto_consulta = floatval($row['solo_consultas']);
          // $monto_lo_demas = floatval($row['lo_demas']) + floatval($row['nota_credito']);
        }
        array_push($arrListadoConsultas, 
          array(
            'especialidad' => strtoupper($row['especialidad']),
            'monto' => $monto_consulta,
          )
        );
        array_push($arrListadoProcedimientos, 
          array(
            'especialidad' => strtoupper($row['especialidad']),
            'monto' => $monto_lo_demas,
          )
        );
      }
      foreach ($especialidades as $key1 => $row) {
        $especialidades[$key1]['monto_consulta'] =  '0';
        $especialidades[$key1]['monto_procedimiento'] =  '0';
        foreach ($arrListadoConsultas as $key2 => $row2) {
          if( $row2['especialidad'] == $row['nombre']){
            $especialidades[$key1]['monto_consulta'] =  $row2['monto'];
          }
        }
        foreach ($arrListadoProcedimientos as $key3 => $row3) {
          if( $row3['especialidad'] == $row['nombre']){
            $especialidades[$key1]['monto_procedimiento'] =  $row3['monto'];
          }
        }
      }
      // if( $keyMaster == 2 ){
      //   var_dump($especialidades)
      // }
      // ARREGLO DEL ARRAY MENSUAL
      $arrMontoConsultas = array();
      $arrMontoProcedimientos = array();
      foreach ($especialidades as $key => $value) {
        array_push($arrMontoConsultas, 
          array(
            $value['monto_consulta'],
          )
        );
        array_push($arrMontoProcedimientos, 
          array(
            $value['monto_procedimiento'],
          )
        );
      }
      // var_dump($arrListadoConsultas);  var_dump($arrListadoProcedimientos); 
      // exit();

      // CONSULTAS
      $currentCellDatos1 = $currentCellEncabezado+3;
      $this->excel->getActiveSheet()->fromArray($arrMontoConsultas, null, $columnas[$keyMaster].($currentCellDatos1+1));
      $this->excel->getActiveSheet()->getCell($columnas[$keyMaster].($currentCellDatos1))->setValue('=SUM('. $columnas[$keyMaster] .($currentCellDatos1+1) .':'.$columnas[$keyMaster].($currentCellTotal).')');

      // PROCEDIMIENTOS
      $currentCellDatos2 = $currentCellTotal+2;

      // $currentCellTotalProced = count($arrListadoProcedimientos) + $currentCellDatos2 + 1;
      $currentCellTotalProced = count($arrEspecialidades) + $currentCellDatos2 + 1;
      //datos
      $this->excel->getActiveSheet()->fromArray($arrMontoProcedimientos, null, $columnas[$keyMaster].($currentCellDatos2+1));
      $this->excel->getActiveSheet()->getCell($columnas[$keyMaster].($currentCellDatos2))->setValue('=SUM('. $columnas[$keyMaster] .($currentCellDatos2+1) .':'.$columnas[$keyMaster].($currentCellTotalProced).')');

      // TOTALIZADO
      $this->excel->getActiveSheet()->getCell($columnas[$keyMaster].($currentCellEncabezado+1))->setValue('=('.$columnas[$keyMaster].($currentCellDatos1) .' + '.$columnas[$keyMaster].($currentCellDatos2).')');

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
  public function report_listado_campanias(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);    
    
    ini_set('max_execution_time', 600); 
    ini_set('memory_limit','2G'); 
    $writer = WriterFactory::create(Type::XLSX); 
    $filePath = 'assets/img/dinamic/excelTemporales/'.$allInputs['titulo'].'.xlsx'; 
    $writer->openToFile($filePath); 

    $singleRow = array('EMPRESA', 'SEDE',  'IDCAMPAÑA', 'NOMBRE DE CAMPAÑA', 'ESPECIALIDAD DE CAMPAÑA', 'FECHA INICIO', 'FECHA FIN',
                        'IDPAQUETE', 'PAQUETE', 'MONTO DE PAQUETE' , 'PRODUCTO', 'PRECIO NORMAL', 'PRECIO CAMPAÑA', 'CANT. VENDIDA', 'MONTO VENDIDO');
    $writer->addRow($singleRow); 
    
    $arrData['flag'] = 0; 
    $arrContent = array(); 
    $lista = $this->model_campania->m_carga_report_listado_campanias($allInputs);
    //print_r( $lista);

    foreach ($lista as $row) { 
      $fecha_inicio = $row['fecha_inicio'];
      $fecha_final = $row['fecha_final'];
      $writer->addRow( 
        array( 
          $row['empresaadmin'],
          $row['sede'],
          $row['idcampania'],
          strtoupper($row['nombre_campania']), 
          $row['especialidad'],
          date('d/m/Y H:i:s a',strtotime($fecha_inicio)),
          date('d/m/Y H:i:s a',strtotime($fecha_final)),
          $row['idpaquete'],
          strtoupper($row['nombre_paquete']),
          $row['monto_total'],          
          $row['producto'],          
          $row['precio_normal'],
          $row['precio_campania'],
          $row['cantidad_vendida'],
          $row['monto_vendido']
        ) 
      ); 
    } 
    $arrData = array(
      'urlTempEXCEL'=> $filePath,
      'flag'=> 1
    );

    $writer->close();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_especialidades_medicos_por_emas_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    $arrData['flag'] = 0;
    
    
    $cont = 0;
    $maxCol = 'G';
    $currentCellEncabezado = 3;
    // ESTILOS
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
            'size'  => 12,
            'name'  => 'Verdana',
            'color' => array('rgb' => '000000') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'FFFFFF', ),
         ),
      );
      $styleArrayHeader2 = array(
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
            'startcolor' => array( 'rgb' => '4472C4', ),
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
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
      );
    
    
    
    // TRATAMIENTO DE DATOS //
    //$allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empresa->m_cargar_especialidad_medico_por_ema($allInputs);
    // var_dump($lista); exit();

    $arrMainArray = array();
    $arrListadoProd = array();
    foreach ($lista as $row) {
      array_push($arrListadoProd, 
        array(
          $row['id_ea'],
          strtoupper($row['empresa_admin']),
          $row['id_ema'],
          strtoupper($row['ema']),
          $row['idespecialidad'],
          strtoupper($row['especialidad']),
          $row['idmedico'],
          strtoupper($row['medico']),
          strtoupper($row['cmp']),
        )
      );
    }
    $rowAux = array();
    foreach ($lista as $key => $row1) {
      $rowAux = array(
        'id_ea'=> $row['id_ea'],
        'empresa_admin'=> $row1['empresa_admin'],
        'emas'=> array()
      );
      $arrMainArray[$row1['id_ea']] = $rowAux;
    }
    foreach ($lista as $key => $row2) {
      $rowAux = array( 
        'id_ema' => $row2['id_ema'],
        'ema' => strtoupper($row2['ema']),
        'especialidades' => array()
      );
      $arrMainArray[$row2['id_ea']]['emas'][$row2['id_ema']] = $rowAux;
    }
    foreach ($lista as $key => $row3) {
      $rowAux = array(
        'idespecialidad' => $row3['idespecialidad'],
        'especialidad' => strtoupper($row3['especialidad']),
        'medicos' => array()
      );
      $arrMainArray[$row3['id_ea']]['emas'][$row3['id_ema']]['especialidades'][$row3['idespecialidad']] = $rowAux;
    }
    foreach ($lista as $key => $row4) {
      $rowAux = array(
        'idmedico' => $row4['idmedico'],
        'medico' => strtoupper($row4['medico']),
        'cmp' => strtoupper($row4['cmp']),
      );
      $arrMainArray[$row4['id_ea']]['emas'][$row4['id_ema']]['especialidades'][$row4['idespecialidad']]['medicos'][$row4['idmedico']] = $rowAux;
    }
    // REORDENAMIENTO DE INDICES
    foreach ($arrMainArray as $key1 => $emp_Adm) {
      foreach ($emp_Adm['emas'] as $key2 => $ema) {
        foreach ($ema['especialidades'] as $key3 => $especialidad) {
          $arrMainArray[$key1]['emas'][$key2]['especialidades'][$key3]['medicos'] = array_values($arrMainArray[$key1]['emas'][$key2]['especialidades'][$key3]['medicos']);
        }
        $arrMainArray[$key1]['emas'][$key2]['especialidades'] = array_values($arrMainArray[$key1]['emas'][$key2]['especialidades']);
      }
      $arrMainArray[$key1]['emas'] = array_values($arrMainArray[$key1]['emas']);
    }
    $arrMainArray = array_values($arrMainArray);
    // FORMACION DEL ARRAY DE LISTADO
    // var_dump($arrMainArray); exit();
    foreach ($arrMainArray as $rowDet) {
      $this->excel->createSheet();
      $this->excel->setActiveSheetIndex($cont++);
      $this->excel->getActiveSheet()->setTitle($rowDet['empresa_admin']);
      $this->excel->getActiveSheet()->getTabColor()->setRGB('4472C4');
      $arrListadoProd = array();
      foreach ($rowDet['emas'] as $rowEm) {
        foreach ($rowEm['especialidades'] as $rowEsp) {
          foreach ($rowEsp['medicos'] as $key => $rowMed) {
            array_push($arrListadoProd, 
              array(
                $rowEm['id_ema'],
                strtoupper($rowEm['ema']),
                $rowEsp['idespecialidad'],
                strtoupper($rowEsp['especialidad']),
                $rowMed['idmedico'],
                strtoupper($rowMed['medico']),
                strtoupper($rowMed['cmp']),
              )
            );
            
          }
          
        }
      }
      
      // var_dump($arrListadoProd); exit();
      $dataColumnsTP = array( 
        array('ID EMA', 'EMA','ID ESPEC.','ESPECIALIDAD', 'ID MEDICO','MEDICO','CMP')
      );
      // SETEO DE ANCHO DE COLUMNAS
      $colWidth = array(10, 50, 10, 50, 10, 50,10);
      $i=0;
      foreach(range('A',$maxCol) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($colWidth[$i++]);
      }
      // TITULO
        $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo'] . ' DE ' . $rowDet['empresa_admin']); 
        $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
        $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
    
      // ENCABEZADO DE LA LISTA
        $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
        $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
        $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
        $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
      // DATOS
        $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
        $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));

        $inicioMerge = $currentCellEncabezado+1;
        $finMerge = $inicioMerge;
        $iniMer = $currentCellEncabezado+1;
        foreach ($rowDet['emas'] as $rowEm) {
          foreach ($rowEm['especialidades'] as $rowEsp) {
              $finMerge = $inicioMerge + count($rowEsp['medicos']) - 1;
              $this->excel->getActiveSheet()->mergeCells('C'.$inicioMerge.':C'.$finMerge);
              $this->excel->getActiveSheet()->mergeCells('D'.$inicioMerge.':D'.$finMerge);
              // var_dump('C'.$inicioMerge.':C'.$finMerge);
              $inicioMerge = $finMerge+1;

          }
          $i = 0;
          foreach ($arrListadoProd as $row) {
            if($row[0] == $rowEm['id_ema']){
              $i++;
            }
          }
          $finMer = $iniMer + $i - 1;
          $this->excel->getActiveSheet()->mergeCells('A'.$iniMer.':A'.$finMer);
          $this->excel->getActiveSheet()->mergeCells('B'.$iniMer.':B'.$finMer);
          $iniMer = $finMer+1;
        }


    }
   
    

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls',
      'flag'=> 1
    );
    // >>>>>>> refs/remotes/origin/b_desarrollo

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData)); 
  }
  public function report_empleado_contrato_vence_mes_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    
    
    // TRATAMIENTO DE DATOS
      $lista = $this->model_empleado->m_cargar_empleado_con_contrato_vence_hasta_mes($allInputs);
      $arrListadoProd = array();

      $i = 1;
      foreach ($lista as $row) {
        if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
          $sede_empresa = $row['empresa'];
        }else{
          $sede_empresa = $row['sede'];
        }
        array_push($arrListadoProd, 
          array(
            $i++,
            $row['idempleado'],
            $row['numero_documento'],
            strtoupper_total($row['empleado']),
            strtoupper_total($row['cargo']),
            strtoupper_total($sede_empresa),
            DarFormatoDMY($row['fecha_inicio_contrato']),
            DarFormatoDMY($row['fecha_fin_contrato'])
          )
        );
      }
    // SETEO DE VARIABLES
    if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
      $dataColumnsTP = array('#', 'ID EMPLEADO', 'NUMERO DOCUMENTO', 'EMPLEADO', 'CARGO', 'EMPRESA', 'F. INICIO CONTRATO', 'F. FIN CONTRATO');
    }else{
      $dataColumnsTP = array('#', 'ID EMPLEADO', 'NUMERO DOCUMENTO', 'EMPLEADO', 'CARGO', 'SEDE', 'F. INICIO CONTRATO', 'F. FIN CONTRATO'); 
    }

    $endColum = 'H';
    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['porEmpresaOSede']['descripcion']);
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(8,12,15,60,40,30,15,15);
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
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
    $this->excel->getActiveSheet()->getStyle('A3:A4')->applyFromArray($styleArrayEncabezado);

    if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
      $this->excel->getActiveSheet()->getCell('A3')->setValue('SEDE:');
      $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['sede']['descripcion']);
    }else{
      $this->excel->getActiveSheet()->getCell('A3')->setValue('EMPRESA:');
      $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['empresa']['descripcion']);
    }
    
    
    $this->excel->getActiveSheet()->getCell('A4')->setValue('VENCIDOS HASTA :');
    $this->excel->getActiveSheet()->getCell('C4')->setValue( strtoupper_total($allInputs['mes']['mes']) . ' / ' . $allInputs['anioDesdeCbo'] );
    
    // ENCABEZADO DE LA LISTA
    $currentCellEncabezado = 6; // donde inicia el encabezado del listado
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    // $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':G'.$currentCellEncabezado);
    // LISTADO
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('C'.$currentCellEncabezado.':C'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':H' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


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
  public function report_programaciones_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    
    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';
      $lista = $this->model_prog_medico->m_cargar_programaciones_reporte($allInputs);
      //print_r($lista); 

      $arrListadoProd = array();
      $i = 1;
      foreach ($lista as $row) {
        /*0:anulado;
          1:registrado; 
          2:cancelado/reprogramado;*/
        if($row['estado_prm'] == 0){
          $estado = 'ANULADA';
        }else if($row['estado_prm'] == 1){
          $estado = 'REGISTRADA';
        }else if($row['estado_prm'] == 2){
          $estado = 'CANCELADA';
        }

        if($row['estado_proc'] == 0){
          $estado_proc = 'ANULADA';
        }else if($row['estado_proc'] == 1){
          $estado_proc = 'REGISTRADA';
        }else if($row['estado_proc'] == 2){
          $estado_proc = 'CANCELADA';
        }

        if($row['activo'] == 1){
          $activo = 'SI';
        }else if($row['activo'] == 2){
          $activo = 'NO';
        }

        if($row['activo_proc'] == 1){
          $activo_proc = 'SI';
        }else if($row['activo_proc'] == 2){
          $activo_proc = 'NO';
        }

        if($row['tipo_atencion_medica'] == 'CM'){
          $cupos = $this->model_prog_medico->m_count_cupos_programacion($row['idprogmedico']);
        }else if($row['tipo_atencion_medica'] == 'P'){          
          $cupos['total_cupos_web'] = '';
          $cupos['disponibles_web'] = '';
          $cupos['no_disponibles_web'] ='';
          $cupos['adicionales_web'] = '';
          $cupos['total_cupos'] = '';
          $cupos['disponibles'] = '';
          $cupos['no_disponibles'] = '';
        }

        $ventas = $this->model_prog_medico->m_count_atencion_proc_programacion($row['idprogmedico_proc']);

        $cumplimiento = (empty($cupos['total_cupos'])) ? 0 : $cupos['no_disponibles']/$cupos['total_cupos'];
        $cumplimiento_proc = (empty($ventas['total_vendido'])) ? 0 : $ventas['total_atendido']/$ventas['total_vendido'];
        $operacion = ($row['tipo_atencion_medica'] == 'CM') ? strtotime($row['hora_inicio']) < strtotime('12:00:00') : strtotime($row['hora_inicio_proc']) < strtotime('12:00:00');
        $turno = ($operacion) ? 'MAÑANA' : 'TARDE';
        $cant_horas = ($row['tipo_atencion_medica'] == 'CM') ? $row['hora_fin'] - $row['hora_inicio'] : $row['hora_fin_proc'] - $row['hora_inicio_proc'];

        array_push($arrListadoProd, 
          array(
            date('d-m-Y',strtotime($row['fecha_programada'])),
            $row['numero_ambiente'],
            strtoupper_total($row['especialidad']),            
            ($row['tipo_atencion_medica'] == 'CM') ? darFormatoHora2($row['hora_inicio']) : darFormatoHora2($row['hora_inicio_proc']),
            ($row['tipo_atencion_medica'] == 'CM') ? darFormatoHora2($row['hora_fin']) : darFormatoHora2($row['hora_fin_proc']),
            strtoupper_total($row['empresa']),
            strtoupper_total($row['medico']),            
            $cupos['total_cupos_web'],
            $cupos['disponibles_web'],
            $cupos['no_disponibles_web'],
            $cupos['adicionales_web'],
            $cupos['total_cupos'],
            $cupos['disponibles'],
            $cupos['no_disponibles'],
            ($row['tipo_atencion_medica'] == 'CM') ? $row['cupos_adicionales'] : '',
            round($cumplimiento*100,2) . '%',        
            $estado,
            $activo, 
            strtoupper_total($row['empresa_proc']),
            strtoupper_total($row['medico_proc']),
            $ventas['total_vendido'],
            $ventas['total_atendido'],            
            round($cumplimiento_proc*100,2) . '%',        
            empty($row['idprogmedico_proc']) ? '' : $estado_proc,
            empty($row['idprogmedico_proc']) ? '' : $activo_proc,
            $cant_horas,
            $turno, 
          )
        );
      }

    // SETEO DE VARIABLES
      $dataColumnsTP = array('FECHA', 'CONSULT.', 'ESPECIALIDAD','INICIO', 'FIN',  
                              'EMPRESA', 'MEDICO',
                              'TOTAL', 'DISP', 'NO DISP','AD.','TOTAL', 'DISP', 'NO DISP','AD.',
                              '% CUMPL.','ESTADO','ACTIVA',
                              'EMPRESA P', 'MEDICO P',
                              'VENDIDOS','ATENDIDOS','% CUMPL.', 'ESTADO','ACTIVA',
                              'HORAS','TURNO');    

      $endColum = 'AA';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle('PROGRAMACIONES');
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array( 10,10,28,8,8,
                          28,30,                          
                          10,10,10,10,10,10,10,10,
                          10,10,10,
                          28,30,
                          10,10,10,10,10,
                          10,10);
      $i = 0;
      $columnas = createColumnsArray($endColum);

      foreach($columnas  as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
        $i++;
      }
    
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
      $styleArraySubEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'FFF2CC', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
    
    // ENCABEZADO DE LA LISTA
    
      $this->excel->getActiveSheet()->getCell('F2')->setValue('CONSULTAS'); 
      $this->excel->getActiveSheet()->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('F2:R2');
      
      $this->excel->getActiveSheet()->getCell('S2')->setValue('PROCEDIMIENTOS'); 
      $this->excel->getActiveSheet()->getStyle('S2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('S2:Y2');
      $this->excel->getActiveSheet()->getStyle('F2:Y2')->applyFromArray($styleArrayEncabezado);  

      $this->excel->getActiveSheet()->getCell('F3')->setValue('PROGRAMACION'); 
      $this->excel->getActiveSheet()->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('F3:G3');      
      $this->excel->getActiveSheet()->getStyle('F3:G3')->applyFromArray($styleArraySubEncabezado); 

      $this->excel->getActiveSheet()->getCell('H3')->setValue('CUPOS WEB'); 
      $this->excel->getActiveSheet()->getStyle('H3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('H3:K3');
      $this->excel->getActiveSheet()->getStyle('H3:K3')->applyFromArray($styleArraySubEncabezado);  

      $this->excel->getActiveSheet()->getCell('L3')->setValue('TODOS LOS CUPOS'); 
      $this->excel->getActiveSheet()->getStyle('L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('L3:O3');
      $this->excel->getActiveSheet()->getStyle('L3:O3')->applyFromArray($styleArraySubEncabezado); 

      $this->excel->getActiveSheet()->getCell('P3')->setValue('ESTADO'); 
      $this->excel->getActiveSheet()->getStyle('P3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('P3:R3');
      $this->excel->getActiveSheet()->getStyle('P3:R3')->applyFromArray($styleArraySubEncabezado);  

      $this->excel->getActiveSheet()->getCell('S3')->setValue('PROGRAMACION'); 
      $this->excel->getActiveSheet()->getStyle('S3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('S3:T3');      
      $this->excel->getActiveSheet()->getStyle('S3:T3')->applyFromArray($styleArraySubEncabezado); 

      $this->excel->getActiveSheet()->getCell('U3')->setValue('ESTADO'); 
      $this->excel->getActiveSheet()->getStyle('U3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->mergeCells('U3:Y3');      
      $this->excel->getActiveSheet()->getStyle('U3:Y3')->applyFromArray($styleArraySubEncabezado); 

      $currentCellEncabezado = 4; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);
    
    // LISTADO
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    
    //ALINEACIONES  
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':A' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('P'.($currentCellEncabezado+1).':P' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

    // SALIDA 
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
  public function report_pacientes_especialidad_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    
    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';
      $lista = $this->model_venta->m_cargar_clientes_por_especialidad($allInputs);
      //print_r($lista); 

      $arrListadoProd = array();
      $i = 1;
      foreach ($lista as $row) {
        array_push($arrListadoProd, 
          array(
            $i++,
            $row['num_documento'],
            strtoupper_total($row['nombres']),
            strtoupper_total($row['apellidos']),
            $row['edad'],
            $row['celular'],
            $row['telefono'],
            strtoupper_total($row['producto']),
            // darFormatoDMY($row['fecha_venta']),
            darFormatoDMYhora($row['fecha_venta']),
            $row['estado']
          )
        );
      }

    // SETEO DE VARIABLES
      $dataColumnsTP = array('#', 'Nº DOC', 'NOMBRES', 'APELLIDOS', 'EDAD', 'CELULAR', 'TELEFONO', 'PRODUCTO','FECHA VENTA', 'ESTADO');    

      $endColum = 'J';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle($allInputs['sede']['descripcion']);
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(7,13,25,35,10,15,15,50,18,18);
      $i = 0;
      foreach(range('A',$endColum) as $columnID) {
        //print_r($columnID);
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
        $i++;
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
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
      $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
      $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
      //$this->excel->getActiveSheet()->getStyle('A3:A4')->applyFromArray($styleArrayEncabezado);

      $this->excel->getActiveSheet()->getCell('A3')->setValue('EMPRESA:');
      $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['empresaAdmin']['descripcion']);
      $this->excel->getActiveSheet()->getCell('A4')->setValue('ESPECIALIDAD:');
      $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['especialidad']['descripcion']);
    
    // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 6; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
      $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);
    
    // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellEncabezado.':B'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':J' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //SALIDA
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
  public function report_cumplimiento_programaciones_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    
    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';

      $lista = $this->model_prog_medico->m_cargar_programaciones_cumplimiento_reporte($allInputs);  
      if(!empty($lista)){
        $idsedeempresaadmin =  $lista[0]['idsedeempresaadmin']; 
        if($allInputs['especialidad']['id'] != "ALL"){
          $precio = $this->model_especialidad->m_cargar_precio_consulta($allInputs['especialidad']['id'], $idsedeempresaadmin);
          $precio['precio_sede'] = (float)$precio['precio_sede'];
        }else{
          $precio['precio_sede'] = 0;
        }
      }else{
        $precio['precio_sede'] = 0;
      }
      //print_r($lista); 

      $arrListadoProd = array();
      $arrEncabezado = array();
      $totales = array(
        'total_cupos_ma' => 0,
        'atendidos_ma' => 0,
        'total_cupos_ta' => 0,
        'atendidos_ta' => 0,
        'total_monto_ma' => 0,
        'total_monto_ta' => 0,
        );
      
      $index=0;
      foreach ($lista as $ind => $row) {   
        array_push($arrEncabezado, formatoConDiaYNombreDia($row['fecha_programada']));

        $arrListadoProd[0][$ind] = (int)$row['total_cupos_ma'];
        $arrListadoProd[1][$ind] = (int)$row['atendidos_ma'];
        $arrListadoProd[2][$ind] = (!empty($row['total_cupos_ma'])) ? round(((int)$row['atendidos_ma'] / (int)$row['total_cupos_ma'])*100,2).'%' : '';
        $arrListadoProd[3][$ind] = (int)$row['total_cupos_ta'];
        $arrListadoProd[4][$ind] = (int)$row['atendidos_ta'];
        $arrListadoProd[5][$ind] = (!empty($row['total_cupos_ta'])) ? round(((int)$row['atendidos_ta'] / (int)$row['total_cupos_ta'])*100,2).'%' : '';
        $arrListadoProd[6][$ind] = (int)$row['total_cupos_ma'] + (int)$row['total_cupos_ta'];
        $arrListadoProd[7][$ind] = (int)$row['atendidos_ma'] + (int)$row['atendidos_ta'];
        $arrListadoProd[8][$ind] = ((int)$row['total_cupos_ma'] + (int)$row['total_cupos_ta'] != 0) ? round((((int)$row['atendidos_ma'] + (int)$row['atendidos_ta'] )/ ((int)$row['total_cupos_ma'] + (int)$row['total_cupos_ta']))*100,2).'%' : '';
        if($allInputs['especialidad']['id'] != "ALL"){
          $arrListadoProd[9][$ind] = 'S/.' . round((float)$row['atendidos_ma'] * $precio['precio_sede'],2);
          $arrListadoProd[10][$ind] = 'S/.' . round((float)$row['atendidos_ta'] * $precio['precio_sede'],2);
          $arrListadoProd[11][$ind] = 'S/.' . round(((float)$row['atendidos_ma'] + (float)$row['atendidos_ta']) * $precio['precio_sede'],2);
        }
        $index = $ind;

        $totales['total_cupos_ma'] += (int)$row['total_cupos_ma'];
        $totales['atendidos_ma'] += (int)$row['atendidos_ma'];
        $totales['total_cupos_ta'] += (int)$row['total_cupos_ta'];
        $totales['atendidos_ta'] += (int)$row['atendidos_ta'];
        $totales['total_monto_ma'] += ((float)$row['atendidos_ma'] * $precio['precio_sede']);
        $totales['total_monto_ta'] += ((float)$row['atendidos_ta'] * $precio['precio_sede']);
      }
      $index++;
      $arrListadoProd[0][$index] = (int)$totales['total_cupos_ma'];
      $arrListadoProd[1][$index] = (int)$totales['atendidos_ma'];
      $arrListadoProd[2][$index] = (!empty($totales['total_cupos_ma'])) ? round(((int)$totales['atendidos_ma'] / (int)$totales['total_cupos_ma'])*100,2).'%' : '';
      $arrListadoProd[3][$index] = (int)$totales['total_cupos_ta'];
      $arrListadoProd[4][$index] = (int)$totales['atendidos_ta'];
      $arrListadoProd[5][$index] = (!empty($totales['total_cupos_ta'])) ? round(((int)$totales['atendidos_ta'] / (int)$totales['total_cupos_ta'])*100,2).'%' : '';
      $arrListadoProd[6][$index] = (int)$totales['total_cupos_ma'] + (int)$totales['total_cupos_ta'];
      $arrListadoProd[7][$index] = (int)$totales['atendidos_ma'] + (int)$totales['atendidos_ta'];
      $arrListadoProd[8][$index] = ((int)$totales['total_cupos_ma'] + (int)$totales['total_cupos_ta'] != 0) ? round((((int)$totales['atendidos_ma'] + (int)$totales['atendidos_ta'] )/ ((int)$totales['total_cupos_ma'] + (int)$totales['total_cupos_ta']))*100,2).'%' : '';
      if($allInputs['especialidad']['id'] != "ALL"){
        $arrListadoProd[9][$index] = 'S/.' . round((float)$totales['total_monto_ma'],2);
        $arrListadoProd[10][$index] = 'S/.' .round((float)$totales['total_monto_ta'],2);
        $arrListadoProd[11][$index] = 'S/.' .round((float)$totales['total_monto_ma'] + (float)$totales['total_monto_ta'],2);
      }
      array_push($arrEncabezado, 'TOTAL');

    // ESTILOS
      $styleArrayTitle = array(
        'font'=>  array(
          'bold'  => true,
          'size'  => 12,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '000000', ),
        ),
      );
      $styleArraySubEncabezado = array(
        'font'=>  array(
            'bold'  => false,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'FFF2CC', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
    
    // SETEO DE VARIABLES
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle('PROGRAMACIONES');

      $this->excel->getActiveSheet()->getCell('A1')->setValue('SEDE'); 
      $this->excel->getActiveSheet()->getCell('B1')->setValue($allInputs['sede']['descripcion']);
      $this->excel->getActiveSheet()->mergeCells('B1:E1'); 
      $this->excel->getActiveSheet()->getCell('A2')->setValue('ESPECIALIDAD'); 
      $this->excel->getActiveSheet()->getCell('B2')->setValue($allInputs['especialidad']['descripcion']);
      $this->excel->getActiveSheet()->mergeCells('B2:E2'); 
      $this->excel->getActiveSheet()->getStyle('A1:D2')->applyFromArray($styleArrayTitle);      

      $this->excel->getActiveSheet()->getCell('A3')->setValue('Médico'); 
      $this->excel->getActiveSheet()->getCell('B3')->setValue($allInputs['medico']['medico']);
      $this->excel->getActiveSheet()->mergeCells('B3:E3'); 
      $this->excel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($styleArrayTitle);   

      if($allInputs['especialidad']['id'] != "ALL"){
        $this->excel->getActiveSheet()->getCell('A4')->setValue('Precio Consulta');         
        $this->excel->getActiveSheet()->getCell('B4')->setValue($precio['precio_sede']);
        $this->excel->getActiveSheet()->mergeCells('B4:E4'); 
        $this->excel->getActiveSheet()->getStyle('A4:E4')->applyFromArray($styleArrayTitle); 
      }   
      
      $inicioReporte = 6;
      $dataColumns = array('TURNO','MAÑANA','TARDE', 'TODO EL DIA');    
      $dataColumns2 = array( 'DISPONIBLE', 'ATENDIDO', 'CUMPLIMIENTO',
                             'DISPONIBLE', 'ATENDIDO', 'CUMPLIMIENTO',
                             'DISPONIBLE', 'ATENDIDO', 'CUMPLIMIENTO');
      if($allInputs['especialidad']['id'] != "ALL"){
        array_push($dataColumns, 'TOTAL MONTO');
        array_push($dataColumns2, 'MAÑANA'); 
        array_push($dataColumns2, 'TARDE');
        array_push($dataColumns2, 'DIA');
      }

      $currentCellTotal = count($dataColumns2) + 1;
      $columna = 'A';
      $columna2 = 'B';
      $i = $inicioReporte;
      $countSubColumnas = 2;
      foreach($dataColumns  as $key => $value) {
        if($key == 0){
          $this->excel->getActiveSheet()->getCell($columna.($key+$i))->setValue($value);  
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i))->applyFromArray($styleArrayHeader);   
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $this->excel->getActiveSheet()->getCell($columna2.($key+$i))->setValue('CUPOS');  

        }else{
          $this->excel->getActiveSheet()->getCell($columna.($key+$i))->setValue($value); 
          $cellFila = $key+$i+$countSubColumnas;
          $this->excel->getActiveSheet()->mergeCells($columna.($key+$i).':'.$columna.$cellFila);
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i).':'.$columna.$cellFila)->applyFromArray($styleArraySubEncabezado); 
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i).':'.$columna.$cellFila)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
          $this->excel->getActiveSheet()->getStyle($columna.($key+$i).':'.$columna.$cellFila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $i += $countSubColumnas;
        }
      }
      $this->excel->getActiveSheet()->getColumnDimension($columna)->setWidth(25);    
      $this->excel->getActiveSheet()->getStyle($columna)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
    
      $fila = $inicioReporte+1;
      foreach ($dataColumns2 as $index => $val) {  
        $this->excel->getActiveSheet()->getCell($columna2.$fila)->setValue($val);
        $fila++;
        /*$this->excel->getActiveSheet()->getStyle($columna2.($ind+$inicioReporte+1))->applyFromArray($styleArraySubEncabezado);   
        $this->excel->getActiveSheet()->getStyle($columna2.($ind+$inicioReporte+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);*/
      }
      $this->excel->getActiveSheet()->getStyle($columna2.'5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
      $this->excel->getActiveSheet()->getStyle($columna2.'6:'.$columna2.($fila-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
      $this->excel->getActiveSheet()->getStyle($columna2.'6:'.$columna2.($fila-1))->applyFromArray($styleArraySubEncabezado);
      $this->excel->getActiveSheet()->getColumnDimension($columna2)->setWidth(20); 

      $this->excel->getActiveSheet()->fromArray($arrEncabezado, null, 'C'.$inicioReporte);
      $endColum = $this->excel->getActiveSheet()->getHighestColumn();

      $this->excel->getActiveSheet()->getStyle('A'.$inicioReporte.':'.$endColum.$inicioReporte)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'C'.($inicioReporte+1));

    //ALINEACIONES
      $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      if($allInputs['especialidad']['id'] != "ALL"){        
        $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'16')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'17')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->excel->getActiveSheet()->getStyle('C'.$inicioReporte.':'.$endColum.'18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      }


    // SALIDA 
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

  public function report_cumplimiento_programaciones_grafico(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);

    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';

      $lista = $this->model_prog_medico->m_cargar_programaciones_cumplimiento_reporte($allInputs);

    /* JSON DE GRAFICO */
      if($allInputs['logicaGraficoCumplProg']['id'] == 'APG'){
        $arrSeries[0] = array(
            'name'=> 'Total Programado',
            'data' => array()
          );
        $arrSeries[1] = array(
            'name'=> 'Total Atendido',
            'data' => array()
          );
      }else if($allInputs['logicaGraficoCumplProg']['id'] == 'APM'){
        $arrSeries[0] = array(
            'name'=> 'Total Programado mañana',
            'data' => array()
          );
        $arrSeries[1] = array(
            'name'=> 'Total Atendido mañana',
            'data' => array()
          );
      }else if($allInputs['logicaGraficoCumplProg']['id'] == 'APT'){
        $arrSeries[0] = array(
            'name'=> 'Total Programado tarde',
            'data' => array()
          );
        $arrSeries[1] = array(
            'name'=> 'Total Atendido tarde',
            'data' => array()
          ); 
      }else if($allInputs['logicaGraficoCumplProg']['id'] == 'AMAT'){
        $arrSeries[0] = array(
            'name'=> 'Total Atendido mañana',
            'data' => array()
          );
        $arrSeries[1] = array(
            'name'=> 'Total Atendido tarde',
            'data' => array()
          );
      }else if($allInputs['logicaGraficoCumplProg']['id'] == 'PCT'){
        $arrSeries[0] = array(
            'name'=> '% Cumplimiento mañana',
            'data' => array()
          );
        $arrSeries[1] = array(
            'name'=> '% Cumplimiento tarde',
            'data' => array()
          );
      }

      $arrAxis = array();
      foreach ($lista as $ind => $row) {   
        array_push($arrAxis, formatoConDiaYNombreDia($row['fecha_programada']));

        if($allInputs['logicaGraficoCumplProg']['id'] == 'APG'){
          //TODOS DIARIO
          $arrSeries[0]['data'][] = (int)$row['total_cupos_ma'] + (int)$row['total_cupos_ta'];
          $arrSeries[1]['data'][] = (int)$row['atendidos_ma'] + (int)$row['atendidos_ta'];
        }else if($allInputs['logicaGraficoCumplProg']['id'] == 'APM'){
          //PROGRAMADO VS ATENDIDO MAÑANA 
          $arrSeries[0]['data'][] = (int)$row['total_cupos_ma'];
          $arrSeries[1]['data'][] = (int)$row['atendidos_ma'];
        }else if($allInputs['logicaGraficoCumplProg']['id'] == 'APT'){
          //PROGRAMADO VS ATENDIDO TARDE 
          $arrSeries[0]['data'][] = (int)$row['total_cupos_ta'];
          $arrSeries[1]['data'][] = (int)$row['atendidos_ta'];
        }else if($allInputs['logicaGraficoCumplProg']['id'] == 'AMAT'){
          //ATENDIDOS MAÑANA VS TARDE
          $arrSeries[0]['data'][] = (int)$row['atendidos_ma'];
          $arrSeries[1]['data'][] = (int)$row['atendidos_ta'];
        }else if($allInputs['logicaGraficoCumplProg']['id'] == 'PCT'){
          //% cumplimiento turno
          $arrSeries[0]['data'][] = (!empty($row['total_cupos_ma'])) ? round(((int)$row['atendidos_ma'] / (int)$row['total_cupos_ma'])*100,2) : 0;
          $arrSeries[1]['data'][] = (!empty($row['total_cupos_ta'])) ? round(((int)$row['atendidos_ta'] / (int)$row['total_cupos_ta'])*100,2) : 0;
        }
      }
      
    /* JSON DE TABLA */
    /*$tablaDatos = array();
      foreach ($arrTipoClientes as $keyAno => $rowTC) { 
        $tablaDatos[$keyAno]['ano'] = '<b>'.$rowTC.'</b>'; 
        $totaFilas = 0; 
        foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
          $tuvoVentas = FALSE;
          foreach ($arrLista as $key => $row) { 
            if( trim($rowTC) == 'Clientes Nuevos' && $rowMes == $row['nro_mes']){ 
              $tablaDatos[$keyAno][$rowTC.'-'.$rowMes] = $row['pn']; 
              $tuvoVentas = TRUE;
              $totaFilas += (float)$row['pn'];
            }
            if( trim($rowTC) == 'Clientes Continuadores' && $rowMes == $row['nro_mes']){
              $tablaDatos[$keyAno][$rowTC.'-'.$rowMes] = ((float)$row['ptodos'] - (float)$row['pn']); 
              $tuvoVentas = TRUE;
              $totaFilas += ((float)$row['ptodos'] - (float)$row['pn']);
            }
          }
          if(!$tuvoVentas){
            $tablaDatos[$keyAno][$rowTC.'-'.$rowMes] = '0.00'; 
          }
        }
        $tablaDatos[$keyAno]['sumtotal'] = $totaFilas; 
        $tablaDatos[$keyAno]['dif'] = 0; 
      }
      // var_dump($tablaDatos);  exit();
      foreach ($tablaDatos as $key => $row) { 
        $tablaDatos[$key]['dif'] = 0; 
        $preKey = $key - 1; 
        if( array_key_exists($preKey, $tablaDatos) ) { 
          if( $tablaDatos[$key]['sumtotal'] > 0 && $tablaDatos[$preKey]['sumtotal'] > 0 ){ 
            $difCrecimiento = round(($row['sumtotal'] - $tablaDatos[$preKey]['sumtotal']) / $tablaDatos[$preKey]['sumtotal'],4); 
            $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
          }else{ 
            $tablaDatos[$key]['dif'] = 0; 
          }
        }
      } 
      foreach ($tablaDatos as $key => $row) { 
        $tablaDatos[$key]['sumtotal'] = '<b>'.number_format($tablaDatos[$key]['sumtotal'] ,2).'</b>'; 
        if( $row['dif'] == 0){
          $tablaDatos[$key]['dif'] = '<b> - </b>'; 
        }else{
          $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
        }
      }
      
      foreach ($tablaDatos as $key => $row) {
        $tablaDatos[$key] = array_values($tablaDatos[$key]);
      }*/
    //var_dump("<pre>",$tablaDatos); exit();
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $arrAxis,
      'series'=> $arrSeries,
      'columns'=> [],
      'tablaDatos'=> [],
      'tipoGraphic'=> 'line',
      'tieneTabla'=> FALSE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  public function report_detalle_ventas_web(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    
    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';
      $lista = $this->model_prog_medico->m_cargar_detallado_ventas_web($allInputs);
      //print_r($lista); 

      $arrListadoProd = array();
      $i = 1;
      $sumPrecio = 0;
      foreach ($lista as $row) {
        //0:anulado;
        //1:reservado
        //2:registrada
        //3:cancelado
        //4:reprogramado
        //5:atendido
        //$estado = '';
        if($row['estado_cita'] == 0){
          $estado = 'ANULADA';
        }else if($row['estado_cita'] == 1){
          $estado = 'RESERVADA';
        }else if($row['estado_cita'] == 2){
          $estado = 'SIN ATENDER';
        }else if($row['estado_cita'] == 3){
          $estado = 'CANCELADA';
        }else if($row['estado_cita'] == 4){
          $estado = 'REPROGRAMADA';
        }else if($row['estado_cita'] == 5){
          $estado = 'ATENDIDA';
        }

        array_push($arrListadoProd, 
          array(
            date('d-m-Y',strtotime($row['fecha_venta'])),
            $row['orden_venta'],            
            $row['numero_ambiente'],
            strtoupper_total($row['especialidad']),            
            strtoupper_total($row['empresa']),
            strtoupper_total($row['medico']),            
            date('d-m-Y',strtotime($row['fecha_programada'])),
            darFormatoHora2($row['hora_inicio_det']) .' - '. darFormatoHora2($row['hora_fin_det']),
            $estado,
            $row['paciente'],
            $row['num_documento'],
            (empty($row['idparentesco'])) ? 'TITULAR' : $row['tipo_familiar'],
            $row['cliente'],
            $row['celular'],
            $row['telefono'],
            $row['email'],
            ($row['sexo'] == 'F') ? 'FEMENINO' : 'MASCULINO',
            ($row['si_registro_web'] == 1) ? 'SI' : 'NO',
            $row['precio'],
          )
        );
        $sumPrecio += (float)$row['precio'];
      }
      
    // SETEO DE VARIABLES
      $dataColumnsTP = array( 'FECHA COMPRA', 'ORDEN VENTA', 
                              'CONSULT.', 'ESPECIALIDAD',  
                              'EMPRESA', 'MEDICO','FECHA CITA', 'TURNO', 'ESTADO',
                              'PACIENTE', 'N° DOCUMENTO', 'TIPO FAMILIAR', 'CLIENTE','CELULAR', 'TELEFONO','CORREO', 'SEXO', 
                              'ES REGISTRO WEB',  'PRECIO CONSULTA (S/.)');    

      $endColum = 'S';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle('VENTAS WEB');
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array( 13,18,
                          10,28,
                          28,30,13,15,15,                         
                          28,12,10,28,12,12,25,12,
                          20,20);
      $i = 0;
      $columnas = createColumnsArray($endColum);

      foreach($columnas  as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
        $i++;
      }
    
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
      $styleArraySubEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'FFF2CC', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
    
    // LISTADO
      $currentCellEncabezado = 3; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);

      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    

    //SUMATORIA
      $this->excel->getActiveSheet()->setCellValue('R'.($currentCellTotal+1), 'TOTAL: ');
      $this->excel->getActiveSheet()->setCellValue('S'.($currentCellTotal+1), $sumPrecio);
    
    //ALINEACIONES  
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':C' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':I' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('K'.($currentCellEncabezado+1).':L' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('N'.($currentCellEncabezado+1).':O' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('Q'.($currentCellEncabezado+1).':R' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('S'.($currentCellEncabezado+1).':S' .($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');

    // SALIDA 
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

  public function reporte_registro_usuarios_web(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    
    // TRATAMIENTO DE DATOS
      $allInputs['fecha_desde'] = $allInputs['desde'].':'.$allInputs['desdeHora'].':'.$allInputs['desdeMinuto'].':00';
      $allInputs['fecha_hasta'] = $allInputs['hasta'].':'.$allInputs['hastaHora'].':'.$allInputs['hastaMinuto'].':00';

      $lista = $this->model_usuario->m_cargar_registro_usuario_web($allInputs);  

      $arrListadoProd = array();
      $i = 1;
      foreach ($lista as $row) {
        array_push($arrListadoProd, 
          array(
            $i,
            strtoupper_total($row['nombres']),
            strtoupper_total($row['apellido_paterno']),
            strtoupper_total($row['apellido_materno']),
            (string)$row['num_documento'],
            $row['celular'],
            $row['telefono'],
            $row['email'],
            $row['sexo'],
            $row['registro_web'],
            $row['estado_usuario_web'],
            empty($row['total_citas']) ? 0 : $row['total_citas'],
            $row['createdAt'],
          )
        );
        $i++;
      }

    // ESTILOS
      $styleArrayTitle = array(
        'font'=>  array(
          'bold'  => true,
          'size'  => 12,
          'name'  => 'calibri',
          'color' => array('rgb' => 'FFFFFF') 
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => '000000', ),
        ),
      );
      $styleArraySubEncabezado = array(
        'font'=>  array(
            'bold'  => false,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
        ),
      );
      $styleArrayEncabezado = array(
        'font'=>  array(
            'bold'  => true,
            'size'  => 11,
            'name'  => 'calibri'
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'FFF2CC', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
    
    // SETEO DE VARIABLES
      $inicioReporte = 3;
      $dataColumns = array('ITEM','NOMBRE','APELLIDO PATERNO','APELLIDO MATERNO', 'NUM. DOCUMENTO', 'CELULAR', 'TELEFONO',
                           'CORREO', 'SEXO', 'PACIENTE NUEVO', 'ESTADO USUARIO WEB', 'CANTIDAD COMPRAS', 'FECHA DE REGISTRO'); 

      $endColum = 'M';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle('REGISTRO DE USUARIOS WEB');
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array( 10,30,30,30,20,12,12,
                          30,10,20,20,20,20);
      $i = 0;
      $columnas = createColumnsArray($endColum);

      foreach($columnas  as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
        $i++;
      }

      $this->excel->getActiveSheet()->fromArray($dataColumns, null, 'A'.$inicioReporte);
      $this->excel->getActiveSheet()->getStyle('A'.$inicioReporte.':'.$endColum.$inicioReporte)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->setAutoFilter('A'.$inicioReporte.':'.$endColum.$inicioReporte);

      $totalFilas = count($arrListadoProd);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($inicioReporte+1));
      $this->excel->getActiveSheet()->getStyle('A'.($inicioReporte + 1).':'.$endColum.$inicioReporte);

    //ALINEACIONES y FORMATOS
      $this->excel->getActiveSheet()->getStyle('E'.($inicioReporte + 1).':E'.$inicioReporte)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );;

    // SALIDA 
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
  public function report_planillas(){
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;
    $lista = $this->model_planilla->m_listar_planilla($allInputs);
    $arrEncabezado = array();
    $arrListadoProd = array();
    $arrConceptos = json_decode(trim($lista[0]['concepto_valor_json']),true);
    $dataColumnsTP = array('#', 'Nº DOC', 'APELLIDOS Y NOMBRES', 'SEDE', 'CENTRO DE COSTO', 'FONDO DE PENSIONES', 'CUSPP','REMUN. DADA');    
    foreach ($arrConceptos['conceptos'] as $key1 => $tipo) {
      foreach ($tipo['categorias'] as $key2 => $categoria) {
        array_push($arrEncabezado, 
          array(
            'descripcion' => $categoria['descripcion_categoria'],
          )
        );
        foreach ($categoria['conceptos'] as $key => $concepto) {
          $dataColumnsTP[]=$concepto['descripcion'];
        }
      }
    }
    // var_dump($dataColumnsTP); exit();
    // SETEO DE VARIABLES
    $this->excel->setActiveSheetIndex(0);
    $this->excel->getActiveSheet()->setTitle($allInputs['empresaSoloAdmin']['descripcion']);
    // $highestColumn = $this->excel->getActiveSheet()->getHighestDataColumn();
    
    $endColum = 'H';
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(7,13,40,15,15,20,15,20);
    $i = 0;
    foreach(range('A',$endColum) as $columnID) {
      //print_r($columnID);
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
      $i++;
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
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
    $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
    $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
    $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
    //$this->excel->getActiveSheet()->getStyle('A3:A4')->applyFromArray($styleArrayEncabezado);

    $this->excel->getActiveSheet()->getCell('A3')->setValue('EMPRESA:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['empresaSoloAdmin']['descripcion']);
    
    // ENCABEZADO DE LA LISTA
    

    $currentCellEncabezado = 6; // donde inicia el encabezado del listado
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $endColum = $this->excel->getActiveSheet()->getHighestColumn();

    // $columnIndex = PHPExcel_Cell::columnIndexFromString($endColum);

    $columnas = createColumnsArray($endColum);
    var_dump($endColum); var_dump($columnas); exit();
    foreach($columnas as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth(25);
    }
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);
    //var_dump($endColum); exit();

    $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
    $dateTime = date('YmdHis');
    $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx');


    // $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
    //force user to download the Excel file without writing it to server's HD 
    // $dateTime = date('YmdHis');
    // $objWriter->save('assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xls'); 
    $arrData = array(
      'urlTempEXCEL'=> 'assets/img/dinamic/excelTemporales/tempEXCEL_'.$dateTime.'.xlsx',
      'flag'=> 1
    );

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($arrData));

  }
  public function planilla_empleados(){
    ini_set('xdebug.var_display_max_depth', 10); 
        ini_set('xdebug.var_display_max_children', 1024); 
        ini_set('xdebug.var_display_max_data', 1024);
      $allInputs = json_decode(trim($this->input->raw_input_stream),true);
      $arrData['flag'] = 0;
      // TRATAMIENTO DE DATOS
      $idplanilla = $allInputs['planilla']['id'];
      $listaEmpleados = $this->model_empleado_planilla->m_cargar_empleados_planilla_calculada($idplanilla);
      $tieneVacaciones = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0118');
      $tieneGratificaciones = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0406');
      $tieneCTS = existeConcepto($allInputs['planilla']['conceptos_json']['conceptos'], '0904');
      $arrayExcel = array();
      $item = 1;
      foreach ($listaEmpleados as $key => $empl) {
        $provision_vacacion = 0;
        $valor_vacacion = 0;
        $arrJson = objectToArray(json_decode($empl['concepto_valor_json']));
        if(!empty($arrJson['calculos']['vacacionesComputables'])){
          $valor_vacacion = (float)$arrJson['calculos']['vacacionesComputables'] + (float)$arrJson['calculos']['vacacionesNoComputables'];
        }
        $arrayFilaExcel = array(
          $item++,
          $empl['tipo_documento'],
          $empl['numero_documento'],
          $empl['empleado'],
          DarFormatoDMY($empl['fecha_ingreso']),
          DarFormatoDMY($empl['fecha_inicio_contrato']),
          DarFormatoDMY($empl['fecha_fin_contrato']),
          '', //fecha cese
          $empl['descripcion_ca'],
          $empl['sede'],
          $empl['centro_costo'],
          ($empl['reg_pensionario'] == 'AFP') ? $empl['reg_pensionario'] . ' - ' . $empl['descripcion_afp'] :  $empl['reg_pensionario'],
          $empl['cuspp'],
          $empl['sueldo_contrato'],
          $arrJson['calculos']['remComputable'],
          $arrJson['calculos']['faltas'],
          empty($arrJson['calculos']['importeFaltas'] ) ? 0 : $arrJson['calculos']['importeFaltas'],
          $arrJson['calculos']['remBasica'],
          $arrJson['calculos']['costoHoraTrabajada'],
          $arrJson['configuracion']['horas_diarias'],
          $arrJson['calculos']['totalHorasEx'],
          empty($arrJson['configuracion']['horas_extras25']) ? 0 : $arrJson['configuracion']['horas_extras25'],
          empty($arrJson['configuracion']['horas_extras35']) ? 0 : $arrJson['configuracion']['horas_extras35'],
          $arrJson['calculos']['costoHora25'],
          $arrJson['calculos']['costoHora35'],
          $arrJson['calculos']['importeHoras25'],
          $arrJson['calculos']['importeHoras35'],
          (float)$arrJson['calculos']['importeHoras25'] + (float)$arrJson['calculos']['importeHoras35'],
          empty($arrJson['calculos']['importeTardanzas']) ? 0 : (float)$arrJson['calculos']['importeTardanzas'],
          0,//pago domingos - feriados
          (empty($arrJson['calculos']['asignacionFamiliar']) ? 0 : (float)$arrJson['calculos']['asignacionFamiliar']),
          $valor_vacacion, //vacaciones
          obtenerValorConcepto($arrJson['conceptos'], '0406') + obtenerValorConcepto($arrJson['conceptos'], '0312'), // grati + bono
          0, //'comisiones',
          0, //reintegros
          $arrJson['calculos']['totalRemuneracionComputable'],
          $arrJson['calculos']['movilidad'],
          $arrJson['calculos']['refrigerio'],
          $arrJson['calculos']['condicion_trabajo'],     
          $arrJson['calculos']['totalRemuneracion'],       
          (empty($arrJson['calculos']['aporteONP'])? 0 : (float)$arrJson['calculos']['aporteONP']),         
          (empty($arrJson['calculos']['aporteObligatorio'])? 0 : (float)$arrJson['calculos']['aporteObligatorio']), 
          (empty($arrJson['calculos']['seguro'])? 0 : (float)$arrJson['calculos']['seguro']), 
          (empty($arrJson['calculos']['comision'])? 0 : (float)$arrJson['calculos']['comision']), 
          (empty($arrJson['calculos']['aporteONP'])? 0 : (float)$arrJson['calculos']['aporteONP']) + (empty($arrJson['calculos']['totalAporteAFP'])? 0 : (float)$arrJson['calculos']['totalAporteAFP']), 
          (empty($arrJson['calculos']['rentaQuinta'])? 0 : (float)$arrJson['calculos']['rentaQuinta']),   
          0,
          0,
          0,
          0,
          (float)empty($arrJson['calculos']['totalDescuentos']) ? 0 : $arrJson['calculos']['totalDescuentos'],
          obtenerValorConcepto($arrJson['conceptos'], '0406') + obtenerValorConcepto($arrJson['conceptos'], '0312'), // grati + bono
          $arrJson['calculos']['netoDepositar'],
          empty($arrJson['calculos']['aporteEsSalud']) ? 0 : $arrJson['calculos']['aporteEsSalud'],
          obtenerValorConcepto($arrJson['conceptos'], '0904')      
        );
        // var_dump($cts);
        if($tieneVacaciones){
          $provision_vacacion = (float)$arrJson['provisiones']['vacaciones']['computable']+(float)$arrJson['provisiones']['vacaciones']['no_computable'];

          array_push($arrayFilaExcel, $provision_vacacion);
        }

        if($tieneGratificaciones){
          array_push($arrayFilaExcel, $arrJson['provisiones']['gratificacion']);
        }

        if($tieneCTS){
          array_push($arrayFilaExcel, $arrJson['provisiones']['cts']);                
        }
        array_push($arrayExcel, $arrayFilaExcel);
      }
       // exit();
      //var_dump($arrayExcel); exit();
      if(empty($listaEmpleados)){
        $arrayExcel = array('Aun no se ha calculado la planilla de empleados');
      }
      $dataColumnsTP = array(); 
      $cont = 0;
      $currentCellEncabezado = 7;
      //armando datos cabecera
      $arrayCabecera = array();
      $arrayCabecera2 = array();
      array_push($arrayCabecera, 'N°', 'DOCUMENTO', '', 'APELLIDOS Y NOMBRES', 'FECHA DE INGRESO', 'RENOVACION', '','FECHA DE CESE',
          'CARGO QUE OCUPA', 'SEDE', 'CENTRO COSTO', 'FONDO DE PENSIONES', 'CUSPP','REMUN. DADA', 'REMUN COMPUTABLE',
          'FALTAS', 'IMPORTE POR FALTAS', 'REMUN BASICA', 'COSTO HORA TRABAJADA', 'CALCULO DE HORAS EXTRAS', '',
          '', '', '', '', '', '', '',
          'TARDANZAS - PERMISOS', 'PAGO DOMINGOS - FERIADOS', 'ASIGNACION FAMILIAR', 'VACACIONES', 'GRATIFICACION', 'COMISIONES', 'REINTEGROS',
          'TOTAL REMUNERACION COMPUTABLE','REMUNERACION NO COMPUTABLE', '', '', 'TOTAL REMUNERACION',
          'ONP', 'AFP', '', '', 'TOTAL DESCUENTOS ONP - AFP', 
          'OTROS DESCUENTOS', '', '', '', '', 'TOTAL DESCUENTOS', 'GRATIFICACION PAGADA',
          'SUELDO NETO (POR DEPOSITAR)', 'ESSALUD','PAGO CTS', 'PROVISIONES');
      array_push($arrayCabecera2, '', 'TIPO', 'Nº DOC.','', '', 'DESDE', 'HASTA','',
          '', '', '', '', '','', '',
          '', '', '', '', 'HORAS DIARIAS', 'TOTAL HORAS EXTRAS AL MES',
          '2 PRIMERAS HORAS', 'MAS DE 2 HORAS', 'MAS 25%', 'MAS 35%', 'TOTAL CON 25%', 'TOTAL CON 35%', 'TOTAL HORAS EXTRAS',
          '', '', '', '', '', '', '',
          '','MOVILIDAD', 'REFRIGERIO' ,'COND. DE TRABAJO', '',
          '', 'FONDO PENSION 10%', 'SEGURO %', 'COMISION %', '', 
          'IMPSTO RENTA 5TA CTG', 'OTROS', 'DCTO JUDICICIAL', 'ADELANTO', 'PRESTAMOS', '',
          '', '', '', '');

      $arrWidths = array(
        7,  10, 15, 52, 15, 15, 15, 15, // Nº, TIPO, Nº DOC., EMPLEADO, F. INGRESO, DESDE, HASTA, F. CESE
        30, 20, 20, 20, 20, 15, 15, // CARGO, SEDE, CENTRO COSTO, FONDO DE PENSIONES, CUSPP,REM. DADA, REM COMPUTABLE
        15, 15, 15, 15, 15, 15,   // CANT. FALTAS, FALTAS(S/), REMUN BASICA, COSTO H.TRAB., HORAS DIARIAS, TOTAL HORAS AL MES,
        15, 15, 15, 15, 15, 15, 15, // 2 PRIM. HORAS, MAS DE 2 HORAS, MAS 25%, MAS 35%, TOTAL CON 25%, TOTAL CON 35%, TOTAL HORAS EXTRAS,
        15, 15, 15, 15, 16, 15, 15,   // TARD-PERMISOS, DOMINGOS-FERIADOS, ASIG.FAMILIAR, VACACIONES, COMISIONES, REINTEGROS,
        20, 15, 15, 15, 18,     // TOTAL REM.COMP., MOVILIDAD,REFRIGERIO, COND.TRABAJO, TOTAL-REM.,
        15, 15, 15, 15, 15,     // ONP, PENSION 10%, SEGURO %, COMISION %, TOTAL ONP - AFP,
        15, 15, 15, 15, 15, 15, 16,  // 5TA CTG, OTROS, DCTO JUDICICIAL, ADELANTO, PRESTAMOS, TOTAL DESCUENTOS,
        15, 15, 15          // NETO A PAGAR, ESSALUD, PAGO DE CTS
      );

      if($tieneVacaciones){
        array_push($arrayCabecera2, 'VACACIONES');
        array_push($arrWidths, 18); 
      }

      if($tieneGratificaciones){
        array_push($arrayCabecera2, 'GRATIFICACIONES');
        array_push($arrWidths, 22); 
      }

      if($tieneCTS){
        array_push($arrayCabecera2, 'CTS');
        array_push($arrWidths, 15); 
      }

      array_push($dataColumnsTP, $arrayCabecera);    
      array_push($dataColumnsTP, $arrayCabecera2);    
      // var_dump($dataColumnsTP); exit();
      //print_r($arrayCabecera);  
      //print_r($arrWidths);  
      //exit();
      
    //cuerpo
    // $calculosPlanilla = $this->calcular_planilla_empleado($allInputs);
      
        //titulo
      $this->excel->setActiveSheetIndex($cont);
      $this->excel->getActiveSheet()->setTitle(substr($allInputs['titulo'],0,30)); 
      //Estilos
        $styleArrayTitle = array(
          'font'=>  array(
              'bold'  => true,
              'size'  => 14,
              'name'  => 'Verdana',
              'color' => array('rgb' => '4f81bd')
          ),
          'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
        );

        // header 
        $styleArrayHeader = array(
          'borders' => array(
            'allborders' => array( 
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('rgb' => 'FFFFFF') 
            ) 
          ),
          'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
          'font'=>  array(
              'bold'  => false,
              'size'  => 10,
              'name'  => 'Verdana',
              'color' => array('rgb' => 'ffffff') 
          ),
          'fill' => array( 
              'type' => PHPExcel_Style_Fill::FILL_SOLID,
              'startcolor' => array( 'rgb' => '4f81bd', ),
           ),
        );
        // header provisiones
        $styleArrayHeaderProv = array(
          'borders' => array(
            'allborders' => array( 
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('rgb' => 'FFFFFF') 
            ) 
          ),
          'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
          'font'=>  array(
              'bold'  => false,
              'size'  => 10,
              'name'  => 'Verdana',
              'color' => array('rgb' => '000000') 
          ),
          'fill' => array( 
              'type' => PHPExcel_Style_Fill::FILL_SOLID,
              'startcolor' => array( 'rgb' => 'b8cce4', ),
           ),
        ); 
            
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);  
      // $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado); 
      //merge y aplicar estilos
      $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['planilla']['descripcion']); 
      $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A1:F1');     
    
      $endColum = $this->excel->getActiveSheet()->getHighestColumn();
      $columnas = createColumnsArray($endColum);
      // var_dump($endColum); var_dump($columnas); exit();

      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.($currentCellEncabezado+1))->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.'BB'.($currentCellEncabezado+1))->applyFromArray($styleArrayHeader); 
      $this->excel->getActiveSheet()->getStyle('BC'.$currentCellEncabezado.':'.$endColum.($currentCellEncabezado+1))->applyFromArray($styleArrayHeaderProv); 

      foreach ($columnas as $col) {
        if( $col != 'B' && $col != 'C' &&
          $col != 'F' && $col != 'G' &&
          $col != 'T' && $col != 'U' && $col != 'V' && $col != 'W' && $col != 'X' && $col != 'Y' && $col != 'Z' && $col != 'AA' && $col != 'AB' &&
          $col != 'AK' && $col != 'AL' && $col != 'AM' && 
          $col != 'AP' && $col != 'AQ' && $col != 'AR' && 
          $col != 'AT' && $col != 'AU' && $col != 'AV' && $col != 'AW' && $col != 'AX' && 
          $col != 'BD' && $col != 'BE' && $col != 'BF' ){
          // MERGE VERTICAL
          $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . $col . ($currentCellEncabezado+1)  );  
        }else{
          // MERGE HORIZONTAL
          if($col == 'B'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'C' . ($currentCellEncabezado)  ); 
          }elseif($col == 'F'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'G' . ($currentCellEncabezado)  ); 
          }elseif($col == 'T'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'AB' . ($currentCellEncabezado)  );  
          }elseif($col == 'AK'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'AM' . ($currentCellEncabezado)  );  
          }elseif($col == 'AP'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'AR' . ($currentCellEncabezado)  );  
          }elseif($col == 'AT'){
            $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'AX' . ($currentCellEncabezado)  );  
          }elseif($col == 'BD'){
            if($tieneGratificaciones && $tieneCTS){
              $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'BF' . ($currentCellEncabezado)  );
            }else if($tieneGratificaciones || $tieneCTS){
              $this->excel->getActiveSheet()->mergeCells($col . $currentCellEncabezado . ':' . 'BE' . ($currentCellEncabezado)  );
            }     
          }
        }
        
      }

    $currentCellEncabezado++;
     
    $cellTotal = count($arrayExcel) + $currentCellEncabezado;
      
      // var_dump($columnas);
      // var_dump($arrWidths);
      //  exit();
    $this->excel->getActiveSheet()->fromArray($arrayExcel, null, 'A'.($currentCellEncabezado+1));
      
    //adicionales
      $styleArrayAdic = array(
        'borders' => array(
            'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '00bcd4') 
          ) 
        ),        
        'font'=>  array(
          'bold'  => false,
          'size'  => 8,
          'name'  => 'Verdana',
          'color' => array('rgb' => 'd60000') 
        ),
        'fill' => array( 
          'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'rgb' => 'e1f9fc', ),
        ),       
      );
      $styleArrayTotales = array(
        'borders' => array(
            'outline' => array( 
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array('rgb' => '000000') 
          ) 
        ),        
        'font'=>  array(
          'bold'  => true,
          'size'  => 10,
          'name'  => 'Verdana',
          'color' => array('rgb' => '000000') 
        ),
              
      );  
      
    //estilo celdas general
      $this->excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
      $this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
    
      $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
      
      $index = 0;
      foreach($columnas as $columnID) {
        
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$index++]);
      }

      $this->excel->getActiveSheet()->getStyle('C'.($currentCellEncabezado+1).':C'.($cellTotal+2))->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->getStyle('M'.($currentCellEncabezado+1).':'.$endColum .($cellTotal+2))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('P'.($currentCellEncabezado+1).':P'.($cellTotal+2))->getNumberFormat()->setFormatCode('#,##0');
      $this->excel->getActiveSheet()->getStyle('T'.($currentCellEncabezado+1).':W'.($cellTotal+2))->getNumberFormat()->setFormatCode('#,##0');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':C'.($cellTotal+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      /*AGRUPAMIENTO y COLAPSO*/
      $this->excel->getActiveSheet()->getColumnDimension('E')->setOutlineLevel(1);
      $this->excel->getActiveSheet()->getColumnDimension('E')->setCollapsed(TRUE);
      $this->excel->getActiveSheet()->getColumnDimension('E')->setVisible(FALSE);
      $this->excel->getActiveSheet()->getColumnDimension('F')->setOutlineLevel(1);
      $this->excel->getActiveSheet()->getColumnDimension('F')->setCollapsed(TRUE);
      $this->excel->getActiveSheet()->getColumnDimension('F')->setVisible(FALSE);
      $this->excel->getActiveSheet()->getColumnDimension('G')->setOutlineLevel(1);
      $this->excel->getActiveSheet()->getColumnDimension('G')->setCollapsed(TRUE);
      $this->excel->getActiveSheet()->getColumnDimension('G')->setVisible(FALSE);
      $this->excel->getActiveSheet()->getColumnDimension('H')->setOutlineLevel(1);
      $this->excel->getActiveSheet()->getColumnDimension('H')->setCollapsed(TRUE);
      $this->excel->getActiveSheet()->getColumnDimension('H')->setVisible(FALSE);

      /*cálculo de mes de la planilla */
        $fechaUT = strtotime($allInputs['planilla']['fecha_cierre']);
        $mes  = (int)date('m', $fechaUT);
      /*cts*/
        $this->excel->getActiveSheet()->getStyle('BC7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $this->excel->getActiveSheet()->getStyle('BC7')->getFill()->getStartColor()->setARGB('FFDAEEF3');
        // var_dump($mes); exit();
        if( ($mes != 5 && $mes != 11) || !$tieneCTS ){
          $this->excel->getActiveSheet()->getColumnDimension('BC')->setVisible(false);
        }
      /*gratificaciones*/
        if( ($mes != 7 && $mes != 12) || !$tieneGratificaciones ){
          $this->excel->getActiveSheet()->getColumnDimension('AG')->setVisible(false);
          $this->excel->getActiveSheet()->getColumnDimension('AZ')->setVisible(false);
        }
      /*TOTALES*/
        $this->excel->getActiveSheet()->getStyle('A'.($cellTotal+2).':'.$endColum .($cellTotal+2))->applyFromArray($styleArrayTotales);
        $this->excel->getActiveSheet()->getCell('M'.($cellTotal+2))->setValue('TOTALES');
        foreach($columnas as $key => $columnID) {
          if($key > 12){
            $this->excel->getActiveSheet()->getCell($columnID.($cellTotal+2))->setValue('=SUM('.$columnID.($currentCellEncabezado+1) .':'.$columnID.($cellTotal+1).')');
            
          }
          // if($key > 12){
          //   var_dump($columnID.($cellTotal+2)); 
          //   var_dump($key); 
          //   var_dump($columnID); 
          // }
        }
        // exit();
      $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
      // $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
      //force user to download the Excel file without writing it to server's HD 
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
  public function report_resumen_analisis_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $arrData['flag'] = 0;    
    // TRATAMIENTO DE DATOS
      $datos = $allInputs['filtro'];
      $paginate = $allInputs['paginate'];
      // $datos['fecha_desde'] = $datos['desde'].':'.$datos['desdeHora'].':'.$datos['desdeMinuto'].':00';
      // $datos['fecha_hasta'] = $datos['hasta'].':'.$datos['hastaHora'].':'.$datos['hastaMinuto'].':00';
      $paginate['firstRow'] = FALSE;
      $paginate['pageSize'] = FALSE;

      $lista = $this->model_resultadoAnalisis->m_cargar_resumen_analisis($paginate,$datos);
      // var_dump($lista); exit();


      $arrListadoProd = array();
      $i = 1;
      foreach ($lista as $row) {
        array_push($arrListadoProd, 
          array(
            $i++,
            strtoupper_total($row['seccion']),
            strtoupper_total($row['descripcion_anal']),
            $row['count_ingresados'],
            $row['count_atendido'],
            $row['count_restante'],
            $row['count_entregados']
          )
        );
      }

    // SETEO DE VARIABLES
      $dataColumnsTP = array('#', 'SECCION', 'ANALISIS', 'REGISTRADOS', 'CON RESULTADOS', 'SIN RESULTADOS', 'ENTREGADOS');  

      $endColum = 'G';
      $this->excel->setActiveSheetIndex(0);
      $this->excel->getActiveSheet()->setTitle($datos['desde'] . '-' . $datos['hasta']);
      // SETEO DE ANCHO DE COLUMNAS
      $arrWidths = array(7,20,40,15,15,15,15);
      $i = 0;
      foreach(range('A',$endColum) as $columnID) {
        //print_r($columnID);
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i]);
        $i++;
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
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => 'A9D08E', ),
         ),
        'borders' => array(
          'allborders' => array( 
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000') 
          ) 
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
      $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
      $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A1:'. $endColum .'1');
    // DATOS DE LA CABECERA
      //$this->excel->getActiveSheet()->getStyle('A3:A4')->applyFromArray($styleArrayEncabezado);

      $this->excel->getActiveSheet()->getCell('A3')->setValue('SEDE:');
      $this->excel->getActiveSheet()->getCell('B3')->setValue($this->sessionHospital['sede']);
      // $this->excel->getActiveSheet()->getCell('A4')->setValue('ESPECIALIDAD:');
      // $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['especialidad']['descripcion']);
    
    // ENCABEZADO DE LA LISTA
      $currentCellEncabezado = 6; // donde inicia el encabezado del listado
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
      $this->excel->getActiveSheet()->setAutoFilter('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado);
    
    // LISTADO
      // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellEncabezado.':B'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
      // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
      $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      // $this->excel->getActiveSheet()->getStyle('I'.($currentCellEncabezado+1).':J' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //SALIDA
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
}