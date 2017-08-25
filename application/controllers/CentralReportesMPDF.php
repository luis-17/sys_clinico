<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CentralReportesMPDF extends CI_Controller { 

  public function __construct()
  {
    parent::__construct();
    $this->load->helper(array('security','reportes_helper','imagen_helper','fechas_helper','otros_helper','pdf_helper','contable_helper'));
    $this->load->model(array('model_config','model_atencion_medica','model_caja','model_estadisticas','model_empleado','model_asistencia', 'model_egresos', 'model_receta_medica', 'model_venta','model_empleado_planilla', 'model_especialidad',
      'model_horario_especial','model_horario_general', 'model_orden_compra', 'model_traslado_farmacia', 'model_atencion_salud_ocup','model_empresa','model_feriado','model_producto','model_empresa_admin')); 
    //cache 
    $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
    $this->output->set_header("Pragma: no-cache");
    $this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
    // $this->load->library('pdf'); 
    $this->load->library('excel');
    $this->load->library('fpdfext');
    date_default_timezone_set("America/Lima");
    //if(!@$this->user) redirect ('inicio/login');
    //$permisos = cargar_permisos_del_usuario($this->user->idusuario);
  } 
  public function ver_popup_reporte()
  {
    $this->load->view('centralReporte/popup_reporte');
  }
  public function report_produccion_medicos()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->Cell(40,4,'DESDE: ');
    $this->pdf->Cell(40,4,$allInputs['desde']); 
    $this->pdf->Ln();
    $this->pdf->Cell(40,4,'HASTA: ');
    $this->pdf->Cell(40,4,$allInputs['hasta']);
    $this->pdf->Ln();
    $this->pdf->Cell(40,4,'TIPO DE ATENCION: ');
    $this->pdf->Cell(40,4,$allInputs['idTipoAtencion'] == 'ALL'? 'TODOS': utf8_decode($allInputs['idTipoAtencion']));

    /* TRATAMIENTO DE DATOS */
    $allInputs['reporte'] = TRUE;
    $lista = $this->model_atencion_medica->m_cargar_historial_ventas_atendidas(FALSE,$allInputs);
    // $lista = $this->model_atencion_medica->m_cargar_ventas_atendidas_historial(FALSE,$allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array(
        'idempresaespecialidad'=> $row['idempresaespecialidad'],
        'especialidad'=> $row['especialidad'],
        'empresa'=> $row['empresa']
      );
      $arrMainArray[$row['idempresaespecialidad']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idmedico'=> $row['idmedicoatencion'],
        'medico'=> $row['medicoatencion']
      );
      $arrMainArray[$row['idempresaespecialidad']]['medicos'][$row['idmedicoatencion']] = $rowAux;
    }
    foreach ($lista as $key => $row) { 
      $rowAux = array( 
        'idatencionmedica'=> $row['idatencionmedica'],
        'fecha_atencion'=> $row['fecha_atencion'],
        'ticket_venta'=> $row['ticket_venta'],
        'orden_venta'=> $row['orden_venta'],
        'paciente'=> strtoupper_total($row['cliente']),
        'tipo_producto'=> $row['nombre_tp'],
        'producto'=> $row['producto'],
        'total_detalle_sf'=> $row['total_detalle_sf']
      );
      $arrMainArray[$row['idempresaespecialidad']]['medicos'][$row['idmedicoatencion']]['atenciones'][$row['idatencionmedica']] = $rowAux;
    }
    
    /* CREACION DEL PDF */
    $headerDetalle = array('FECHA/HORA', 'NRO ORDEN', 'NRO TICKET', 'PACIENTE', 'TIPO PROD.', 'PRODUCTO', 'ACT. MED.', 'IMPORTE'); 
    $this->pdf->Ln(1);
    $this->pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'R', 'R'));
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,strtoupper_total($rowPrin['especialidad'].' / '.$rowPrin['empresa']),'',0,'C',TRUE);
      $this->pdf->Ln(5);
      $mainContAtenciones = 0;
      $mainTotalAtenciones = 0;
      
      foreach ($rowPrin['medicos'] as $keyMed => $rowMed) { 
        $this->pdf->Ln(3);
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->Cell(0,6,utf8_decode(strtoupper_total($rowMed['medico'])),'',0,'L');
        $this->pdf->Ln();
        $contAtenciones = 0; 
        $totalAtenciones = 0; 
        // $this->pdf->SetFillColor(0); 
        $this->pdf->SetWidths(array(27, 29, 24, 65, 40, 58, 16, 18));
        $wDetalle = $this->pdf->GetWidths();
        $this->pdf->SetFont('Arial','B',8);
        for($i=0;$i<count($headerDetalle);$i++)
          $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');

        $this->pdf->Ln();
        $this->pdf->SetFillColor(224,235,255);

        $fill = false;
        foreach ($rowMed['atenciones'] as $keyAte => $rowAte) { 
          $this->pdf->SetFont('Arial','',7);
          $rowFechaHoraAtencion = $rowAte['fecha_atencion'];
          $strFechaHoraAtencion = date('d/m/Y H:i:s',strtotime($rowFechaHoraAtencion)); 
          $this->pdf->Row( 
            array($strFechaHoraAtencion,
              $rowAte['orden_venta'],
              $rowAte['ticket_venta'],
              utf8_decode($rowAte['paciente']),
              utf8_decode($rowAte['tipo_producto']),
              utf8_decode($rowAte['producto']),
              $rowAte['idatencionmedica'],
              number_format($rowAte['total_detalle_sf'],2))
            ,$fill
          );
          $fill = !$fill;
          $contAtenciones++;
          $totalAtenciones += $rowAte['total_detalle_sf'];
        } 
        $this->pdf->SetWidths(array(27, 29, 24, 125, 30, 10, 18, 16));
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->Row( 
            array(
              '',
              '',
              '',
              '',
              'ATENCIONES: ',
              $contAtenciones,
              'MONTO: ',
              number_format($totalAtenciones,2))
            
          );
        $mainContAtenciones += $contAtenciones;
        $mainTotalAtenciones += $totalAtenciones;
      }
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_liquidacion_terceros()
  { 
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    
    /* LIMPIEZA DE DATOS POST */ 
    if( empty($allInputs['desde']) || empty($allInputs['hasta']) ){ 
      $arrFechas = get_fecha_inicio_y_fin($allInputs['anio'],$allInputs['mes']['id']);
      $allInputs['desde'] = $arrFechas['inicio'];
      $allInputs['desdeHora'] = '00';
      $allInputs['desdeMinuto'] = '00';
      $allInputs['hasta'] = $arrFechas['fin'];
      $allInputs['hastaHora'] = '23';
      $allInputs['hastaMinuto'] = '59';
    }
    // var_dump($allInputs['empresaespecialidad']); exit(); 
    if( empty($allInputs['empresaespecialidad']) ){ 
      $fEmpresa = $this->model_empresa->m_cargar_esta_empresa_por_ruc($allInputs);
      if( empty($fEmpresa) ){
        $arrData['message'] = 'No se encontró la empresa con el ruc Num. '.$allInputs['ruc']; 
        $arrData['flag'] = 0;
        $this->output
          ->set_content_type('application/json')
          ->set_output(json_encode($arrData));
        return;
      }
      $arrParams = array( 
        'idempresa'=> $fEmpresa['idempresa'],
        'idespecialidad'=> $allInputs['servicio']['id']
      );
      $fEmpresaEsp = $this->model_empresa->m_validar_empresa_especialidad($arrParams);
      if( empty($fEmpresaEsp) ){ 
        $arrData['message'] = 'No se encontró la empresa-especialidad'; 
        $arrData['flag'] = 0;
        $this->output
          ->set_content_type('application/json')
          ->set_output(json_encode($arrData));
        return;
      }
      $allInputs['empresaespecialidad']['id'] = $fEmpresaEsp['idempresaespecialidad'];
    }
    $allInputs['titulo'] = 'LIQUIDACIÓN DE TERCEROS';

    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_empresa_admin_por_ruc($allInputs['empresaSoloAdmin']);
    
    //$allInputs['idempresaadmin'] = $empresaAdmin['idempresaadmin'];

    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = FALSE;
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv'],$empresaAdmin);
    $this->pdf->SetFont('Arial','',11);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->Cell(0,4,'PERIODO: '.strtoupper(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta']))); 
    /* TRATAMIENTO DE DATOS */ 
    $allInputs['reporte'] = TRUE;
    $boolPorcentaje = TRUE;
    // SI ES SALUD OCUPACIONAL 

    if( $allInputs['empresaespecialidad']['idespecialidad'] == 39 ){ // SALUD OCUPACIONAL 
      $lista = $this->model_atencion_medica->m_cargar_ventas_atendidas_para_terceros($allInputs);
    }else{
      $lista = $this->model_atencion_medica->m_cargar_ventas_atendidas_para_terceros($allInputs);
    }
    $listaWeb = $this->model_atencion_medica->m_cargar_ventas_on_line_atendidas($allInputs);
    $lista = array_merge($lista,$listaWeb);
    
    // if( $this->sessionHospital['idsedeempresaadmin'] == 9 ){ // SEDE LURIN // TEMPORALMENTE DE MANEJARÁ ASÍ. 
    //   foreach ($lista as $key => $row) {
    //     $lista[$key]['pertenece_tercero'] = 2; 
    //   }
    // }
    // LO ANTERIOR YA NO SIRVE PORQUE ODONTOLOGIA ORTIZ YA NO ESTA EN LURIN SINO EN VILLA.
    // ES MEJOR MANEJARLO POR EL CAMPO PRODUCTOS TERCEROS PARA INDICAR Q ESA EMPRESA CUENTA CON PRODUCTOS PROPIOS DEL TERCERO
    foreach ($lista as $key => $row) {
      if($row['productos_tercero'] == '2'){
        $lista[$key]['pertenece_tercero'] = 2; 
      }
    }

    $arrMainArray = array();
    
    foreach ($lista as $key => $row) { 
      $rowAux = array(
        'idempresaespecialidad'=> $row['idempresaespecialidad'],
        'especialidad'=> $row['especialidad'],
        'empresa'=> $row['empresa'],
        'porcentaje'=> $row['porcentaje'],
        'productos_tercero'=> $row['productos_tercero']
      );
      $arrMainArray[$row['idempresaespecialidad']] = $rowAux; 
    }
    foreach ($lista as $key => $row) { 
      $rowAuxTerc = array( 
        'id'=> $row['pertenece_tercero'],
        'descripcion'=> ($row['pertenece_tercero'] == 1 ? 'LIQUIDACION 100%':'LIQUIDACION '.$arrMainArray[$row['idempresaespecialidad']]['porcentaje'].'%'),
        'porcentaje_terc'=> ($row['pertenece_tercero'] == 1 ? 100:$arrMainArray[$row['idempresaespecialidad']]['porcentaje'])
      );
      $arrMainArray[$row['idempresaespecialidad']]['si_terceros'][$row['pertenece_tercero']] = $rowAuxTerc; 
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idmediopago'=> $row['idmediopago'],
        'mediopago'=> $row['descripcion_med']
      );
      $arrMainArray[$row['idempresaespecialidad']]['si_terceros'][$row['pertenece_tercero']]['mediopago'][$row['idmediopago']] = $rowAux; 
    }
    foreach ($lista as $key => $row) { 
      $rowAux = array( 
        'idatencionmedica'=> $row['idatencionmedica'],
        'iddetalle'=> $row['iddetalle'],
        'fecha_atencion'=> $row['fecha_atencion_det'],
        'ticket_venta'=> $row['ticket_venta'],
        'orden_venta'=> $row['orden_venta'],
        'paciente'=> strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
        'producto'=> $row['producto'],
        'total_detalle_str'=> $row['total_detalle_str'],
        'total_detalle_costo'=> $row['total_detalle_costo'],
      );
      if($row['porcentaje_o_fijo'] == 1){
        $boolPorcentaje = TRUE;
      }else{
        $boolPorcentaje = FALSE;
      }
      $arrMainArray[$row['idempresaespecialidad']]['si_terceros'][$row['pertenece_tercero']]['mediopago'][$row['idmediopago']]['atenciones'][$row['iddetalle']] = $rowAux;
    }

    /* CREACION DEL PDF */
    if($boolPorcentaje){
      $headerDetalle = array('FECHA/HORA', 'NRO ORDEN', 'NRO TICKET', 'PACIENTE', 'PRODUCTO', 'IMPORTE'); 
      $this->pdf->SetAligns(array('L', 'C', 'C', 'L', 'L', 'R'));
    }else{
      $headerDetalle = array('FECHA/HORA', 'NRO ORDEN', 'NRO TICKET', 'PACIENTE', 'PRODUCTO', 'COSTO', 'IMPORTE'); 
      $this->pdf->SetAligns(array('L', 'C', 'C', 'L', 'L', 'R', 'R'));
    }
    $this->pdf->Ln(1);

    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(203,223,253);
      $this->pdf->Cell(0,10,utf8_decode(strtoupper_total($rowPrin['especialidad'].' - '.$rowPrin['empresa'])),'1',0,'C',TRUE);
      $this->pdf->Ln(8);
      $mainContAtenciones = 0; 
      $mainTotalAtenciones = 0; 

      foreach ($rowPrin['si_terceros'] as $keyTerc => $rowTerc) { 
        if( $rowPrin['productos_tercero'] == 1 ){ 
          $this->pdf->Ln(6);
          $this->pdf->SetFont('Arial','B',12);
          $this->pdf->SetFillColor(203,223,253);
          $this->pdf->Cell(0,10,utf8_decode(strtoupper_total($rowTerc['descripcion'])),'1',0,'C',TRUE);
          $this->pdf->Ln(8);
        }
        $arrSoloMediosPago = array(); 
        $valueDetGen = array(); 
        foreach ($rowTerc['mediopago'] as $keyMed => $rowMed) { 
          $valueDetGen['idmediopago'] = $rowMed['idmediopago'];
          $valueDetGen['descripcion'] = $rowMed['mediopago'];
          $valueDetGen['cantidad'] = 0;
          $valueDetGen['monto'] = 0;
          $valueDetGen['monto_tercero'] = 0;
          $arrSoloMediosPago[$rowMed['idmediopago']] = $valueDetGen;
          foreach ($rowMed['atenciones'] as $keyAte => $rowAte) {
            if( $arrSoloMediosPago[$rowMed['idmediopago']]['idmediopago'] == $rowMed['idmediopago'] ){ 
              $arrSoloMediosPago[$rowMed['idmediopago']]['monto'] += $rowAte['total_detalle_str'];
              $arrSoloMediosPago[$rowMed['idmediopago']]['monto_tercero'] += $rowAte['total_detalle_costo'];
              $arrSoloMediosPago[$rowMed['idmediopago']]['cantidad']++;
            }
          }
        }
        $totalProd = 0;
        $totalProdTerc = 0;
        $this->pdf->Ln(3); 
        $this->pdf->SetFont('Arial','B',11); 

        foreach ($arrSoloMediosPago as $keyMP => $rowMP) { 
          $strTexto = '';
          $montoTotalizado = $rowMP['monto'];
          if( $rowMP['descripcion'] == 'VISA' ){ 
            $montoTotalizado = ($montoTotalizado - ($montoTotalizado * (0.05))); 
            $strTexto = ' (-5% COMISION VISA)';
          }
          $this->pdf->SetFillColor(228,235,245);
          $this->pdf->Cell(70,7,strtoupper($rowMP['descripcion'].$strTexto).': ','1',0,'R',TRUE);
          if($rowMP['descripcion'] == 'VISA'){
            $this->pdf->Cell(35,7,'S/. '.number_format($rowMP['monto'],2),'1',0,'C',TRUE);
            $this->pdf->Cell(35,7,'S/. '.number_format($montoTotalizado,2),'1',0,'R',TRUE);
          }else{
            $this->pdf->Cell(70,7,'S/. '.number_format($montoTotalizado,2),'1',0,'R',TRUE);
          }
          
          $this->pdf->Ln(); 
          $totalProd += $montoTotalizado;
          $totalProdTerc += $rowMP['monto_tercero'];
        }
        //$this->pdf->Ln(); 
        $this->pdf->Cell(70,7,'TOTAL PRODUCCION: ','1',0,'R',TRUE);
        $this->pdf->Cell(70,7,'S/. '.number_format($totalProd,2),'1',0,'R',TRUE);
        $this->pdf->Ln();
        $this->pdf->SetFillColor(203,223,253);
        // $this->pdf->SetFillColor(177,203,242);
        $this->pdf->SetFont('Arial','B',13);
        $this->pdf->Cell(70,8,'TOTAL TERCERO: ','1',0,'R',TRUE);
        if($boolPorcentaje){
          $this->pdf->Cell(70,8,'S/. '.number_format(($totalProd * (@$rowTerc['porcentaje_terc'] / 100) ),2),'1',0,'R',TRUE);
        }else{
          $this->pdf->Cell(70,8,'S/. '.number_format($totalProdTerc,2),'1',0,'R',TRUE);
        }

        $this->pdf->Ln(7);

        foreach ($rowTerc['mediopago'] as $keyMed => $rowMed) { 
          $this->pdf->Ln(3);
          $this->pdf->SetFont('Arial','B',13);
          $this->pdf->Cell(0,6,strtoupper($rowMed['mediopago']),'',0,'L');
          $this->pdf->Ln();
          $contAtenciones = 0; 
          $totalAtenciones = 0;
          $totalCosto = 0;
          $columnas = count($headerDetalle);
          // $this->pdf->SetFillColor(0);
          if($boolPorcentaje){
            $this->pdf->SetWidths(array(29, 31, 25, 90, 80, 20));
          }else{
            $this->pdf->SetWidths(array(29, 31, 25, 80, 80, 15, 15));
          }
          $wDetalle = $this->pdf->GetWidths();
          $this->pdf->SetFont('Arial','B',8);
          for($i=0;$i<$columnas;$i++)
            $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');

          $this->pdf->Ln();
          $this->pdf->SetFillColor(224,235,255);
          $fill = TRUE;
          foreach ($rowMed['atenciones'] as $keyAte => $rowAte) { 
            $this->pdf->SetFont('Arial','',7);
            $rowFechaHoraAtencion = $rowAte['fecha_atencion'];
            $strFechaHoraAtencion = date('d/m/Y H:i:s',strtotime($rowFechaHoraAtencion));
            $arrData = array(
              $strFechaHoraAtencion,
              $rowAte['orden_venta'],
              $rowAte['ticket_venta'],
              utf8_decode($rowAte['paciente']),
              utf8_decode($rowAte['producto']),
            );
            if($boolPorcentaje){
              $arrData[] = number_format($rowAte['total_detalle_str'],2);
            }else{
              $arrData[] = number_format($rowAte['total_detalle_costo'],2);
              $arrData[] = number_format($rowAte['total_detalle_str'],2);
              $totalCosto += $rowAte['total_detalle_costo'];
              
            }
            // var_dump($arrData); exit();
            
            $this->pdf->Row($arrData,$fill);
            $fill = !$fill;
            $contAtenciones++;
            $totalAtenciones += $rowAte['total_detalle_str'];
          } 
          $this->pdf->SetFont('Arial','B',13);
          if($boolPorcentaje){
            $this->pdf->SetWidths(array(24, 150, 40, 15, 31, 15));
            $arrTotales = array(
                  '',
                  '',
                  'ATENCIONES: ',
                  $contAtenciones,
                  'MONTO: ',
                  number_format($totalAtenciones,2)
                );
          }else{
            $this->pdf->SetWidths(array(24, 150, 40, 15, 16, 15, 15));
            $arrTotales = array(
                  '',
                  '',
                  'ATENCIONES: ',
                  $contAtenciones,
                  'MONTO: ',
                  number_format($totalCosto,2),
                  number_format($totalAtenciones,2)
                );
          }
          $this->pdf->Row($arrTotales);
          $mainContAtenciones += $contAtenciones;
          $mainTotalAtenciones += $totalAtenciones; 
        }
      }
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  public function report_consolidado_terceros()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    
    /* LIMPIEZA DE DATOS POST */ 
    if( empty($allInputs['desde']) || empty($allInputs['hasta']) ){ 
      $arrFechas = get_fecha_inicio_y_fin($allInputs['anio'],$allInputs['mes']['id']);
      $allInputs['desde'] = $arrFechas['inicio'];
      $allInputs['desdeHora'] = '00';
      $allInputs['desdeMinuto'] = '00';
      $allInputs['hasta'] = $arrFechas['fin'];
      $allInputs['hastaHora'] = '23';
      $allInputs['hastaMinuto'] = '59';
    }
    $allInputs['titulo'] = 'CONSOLIDADO DE TERCEROS';

    // var_dump($allInputs); exit();
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',11);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    // $this->pdf->Ln();
    /* TRATAMIENTO DE DATOS */ 
    $allInputs['reporte'] = TRUE;
    $lista = $this->model_atencion_medica->m_cargar_consolidado_atendidos_para_terceros($allInputs);
    
    /* ELIMINAR DUPLICADO DE DOBLE PRODUCCION(odontología) */
    $arrAux = array();
    foreach ($lista as $key => $row) { 
      if($row['pertenece_tercero'] == 1){ 
        $arrAux[] = $row;
        unset($lista[$key]);
      }
    }
    foreach ($lista as $key => $row) { 
      foreach ($arrAux as $keyDet => $rowDet) { 
        if( $row['idempresaespecialidad'] == $rowDet['idempresaespecialidad'] ){ 
          $lista[$key]['monto_total_tercero'] += $rowDet['monto_total_tercero'];
        }
      }
    }
    // $listaIngresos = $this->model_egresos->m_cargar_consolidado_atendidos_para_terceros($allInputs);
    
    /* CREACION DEL PDF */
    $fill = TRUE;
    $headerDetalle = array('N°', 'SERVICIO', 'RAZÓN SOCIAL', 'RUC', 'REP. LEGAL', 'T. REPORTE', 'T. FACTURA','DETRACCIÓN','DEPÓSITO','N° FACT.'); 
    // $this->pdf->SetWidths(array(29, 31, 25, 90, 80, 20));
    // $wDetalle = $this->pdf->GetWidths();
    
    $this->pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'R', 'R', 'R', 'R', 'C'));
    // $this->pdf->SetFont('Arial','',7);

    $this->pdf->SetWidths(array(5, 42, 56, 18, 55, 20, 22, 22, 22, 20));
    $wDetalle = $this->pdf->GetWidths();
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->SetFillColor(199,218,250);
    $this->pdf->Cell(282,6,'PERIODO '.strtoupper($allInputs['mes']['descripcion'].' - '.$allInputs['anio']),1,0,'C',TRUE );
    $this->pdf->Ln();
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->SetFillColor(224,235,255);
    // $fill = TRUE;
    for($i=0;$i<count($headerDetalle);$i++)
      $this->pdf->Cell($wDetalle[$i],7,utf8_decode($headerDetalle[$i]),1,0,'C',TRUE);

    $this->pdf->Ln();
    // $fill = FALSE;
    $this->pdf->SetFillColor(243,247,255);

    $arrParams['periodo'] = $allInputs['mes']['descripcion'].'-'.$allInputs['anio']; 
    $i = 0;
    $totalTerceros = 0;
    $totalFactura = 0; 
    $fill = FALSE;
    foreach ($lista as $key => $row) { 
      /* Se meterá una consulta en el foreach para terminar rápido, lo adecuado es hacerlo afuera */
      $arrParams['idempresatercero'] = $row['idempresa']; 
      $fEgreso = $this->model_egresos->m_obtener_este_egreso($arrParams);
      $this->pdf->Row( 
        array( 
          ++$i,
          $row['especialidad'],
          $row['empresa'],
          $row['ruc'],
          $row['representante_legal'],
          'S/. '.number_format(round($row['monto_total_tercero'],2),2),
          $fEgreso['total_a_pagar'],
          $fEgreso['detraccion'],
          $fEgreso['deposito'],
          $fEgreso['ticket_venta']
        )
        ,$fill
      );
      $fill = !$fill;
      $totalTerceros += $row['monto_total_tercero'];
    } 
    $this->pdf->SetWidths(array(176, 20, 22, 22, 22, 20));
    $this->pdf->SetFont('Arial','B',12);
    $this->pdf->SetFillColor(224,235,255);
    $this->pdf->SetAligns(array('C', 'R', 'R', 'R', 'R', 'C'));
    $arrBolds = array('B', 'B', 'B', 'B', 'B', 'B');
    $this->pdf->RowSmall( 
      array( 
        'TOTALES',
        number_format(round($totalTerceros,2),2),
        number_format(round($totalFactura,2),2),
        '',
        '',
        ''
      ),TRUE,0,$arrBolds
    );
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_venta_mes_anio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    if( $allInputs['unidadNegocio']['id'] == 'hos'){
      $listaV = $this->model_estadisticas->m_cargar_ventas_por_anio_mes($allInputs); 
      $listaNC = $this->model_estadisticas->m_cargar_nota_credito_por_anio_mes($allInputs); 
      
      // CARGAR ESTADISTICAS DE AÑOS ANTERIORES SOLO PARA LA SEDE DE VILLA
      if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
        $listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_anos_anteriores($allInputs); 
        $arrAnteriores = array();
        foreach ($listaAnteriores as $key => $row) { 
          array_push($arrAnteriores, 
            array(
              'ano'=> $row['anio'],
              'mes'=> $row['mes'],
              'nro_mes'=> $row['num_mes'],
              'total'=> $row['monto']
            )
          ); 
        }
      
        $listaV = array_merge($arrAnteriores,$listaV);
      }
      // $listaV = array_merge($arrAnteriores,$listaV);
      
    }else{
      $listaV = $this->model_estadisticas->m_cargar_ventas_farmacia_por_anio_mes($allInputs); 
      $listaNC = $this->model_estadisticas->m_cargar_nota_credito_farmacia_por_anio_mes($allInputs);
    }
    foreach ( $listaV as $key => $row ) { 
      foreach ($listaNC as $keyNC => $rowNC) { 
        if( $row['ano'] == $rowNC['ano'] && $row['mes'] == $rowNC['mes'] ){ 
          $listaV[$key]['total'] = $listaV[$key]['total'] + $rowNC['total'];
        }
      }
    }
    if( $allInputs['tipoCuadro'] === 'reporte' ){
      $this->report_estadistico_venta_mes_anio_PDF($allInputs,$listaV); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) {
      $this->report_estadistico_venta_mes_anio_GRAPH($allInputs,$listaV); 
    }
  }
  private function report_estadistico_venta_mes_anio_PDF($allInputs,$listaV)
  {
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'monto_anual' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }    
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($listaV as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            $arrTable[$rowMes][$rowAno['ano']]['monto'] = 'S/. '.number_format($row['total'],2); 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $preKey = $key - 1;
            if( array_key_exists($preKey, $listaV) ) { 
              if( $listaV[$preKey]['total'] > 0 ){
                $difCrecimiento = round(($row['total'] - $listaV[$preKey]['total']) / $listaV[$preKey]['total'],4);
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }else{
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
              }
            }
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['monto_anual'] += $row['total'];
          }
          if( $row['ano'] == $rowAno['ano'] ){ 
            //$arrAnos[$keyAno]['monto_anual'];
            
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = 'S/. 0.00';
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    // var_dump($arrAnos); exit(); 
    foreach ($arrAnos as $key => $row) { 
      $arrTable['footer'][$row['ano']] = 'S/. '.number_format($row['monto_anual'],2);
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4,'PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['monto'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_venta_mes_anio_GRAPH($allInputs,$listaV)
  {
    ini_set('xdebug.var_display_max_depth', 5);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");

    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 
    $contDesde = (int)$allInputs['anioDesde'];
    $arrAnos = array(); 
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    // var_dump("<pre>",$listaV); exit(); 
    $arrSeries = array();
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
          'name'=> $value,
          'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($listaV as $keyDet => $rowDet) { 
            if( $value == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){
                if( trim($rowDet['ano']) == trim($value)){
                   $arrSeries[$key]['data'][] = (float)$rowDet['total'];
                   $tuvoVentas = TRUE;
                }
            }
        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }
    }
    
    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totalAno = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($listaV as $key => $row) {  
          if( $rowAno == $row['ano'] && $rowMes == $row['nro_mes'] ){ 
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = 'S/. '.number_format($row['total'],2); 
            $tuvoVentas = TRUE;
            $totalAno += (float)$row['total'];  
          }
        }
        if(!$tuvoVentas){
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = '0.00'; 
          // $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = (float)('0.00'); 
        }
        
      }
      $tablaDatos[$keyAno]['sumtotal'] = $totalAno; 
      $tablaDatos[$keyAno]['dif'] = 0; 
    }
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['dif'] = 0; 
      $preKey = $key - 1; 
      if( array_key_exists($preKey, $tablaDatos) ) { 
        if( $tablaDatos[$key]['sumtotal'] > 0 && $tablaDatos[$preKey]['sumtotal'] != 0 ){ 
          $difCrecimiento = round(($row['sumtotal'] - $tablaDatos[$preKey]['sumtotal']) / $tablaDatos[$preKey]['sumtotal'],4); 
          $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
        }else{ 
          $tablaDatos[$key]['dif'] = 0; 
        }
      }
      //$tablaDatos[$key]['sumtotal'] = '<b>S/. '.number_format($tablaDatos[$key]['sumtotal'] ,2).'</b>'; 
    } 
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['sumtotal'] = '<b>'.number_format($tablaDatos[$key]['sumtotal'] ,2).'</b>'; 
      
      if( $row['dif'] == 0){
        $tablaDatos[$key]['dif'] = '<b> - </b>'; 
      }else{
        $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
        
      }
      
    }
    //var_dump($tablaDatos); exit();
    foreach ($tablaDatos as $key => $row) {
      $tablaDatos[$key] = array_values($tablaDatos[$key]);
    }
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    //var_dump($arrData); exit();
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_prestacion_mes_anio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $listaV = $this->model_estadisticas->m_cargar_prestaciones_por_anio_mes($allInputs); 
    // CARGAR ESTADISTICAS DE AÑOS ANTERIORES SOLO PARA LA SEDE DE VILLA
    if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
      $listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_anos_anteriores($allInputs); 
      $arrAnteriores = array();
      foreach ($listaAnteriores as $key => $row) { 
        array_push($arrAnteriores, 
          array(
            'ano'=> $row['anio'],
            'mes'=> $row['mes'],
            'nro_mes'=> $row['num_mes'],
            'cantidad'=> $row['cantidad']
          )
        ); 
      }
      $listaV = array_merge($arrAnteriores,$listaV);
    }
    
    if( $allInputs['tipoCuadro'] === 'reporte' ){
      $this->report_estadistico_prestacion_mes_anio_PDF($allInputs,$listaV); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) {
      $this->report_estadistico_prestacion_mes_anio_GRAPH($allInputs,$listaV); 
    }
  }
  private function report_estadistico_prestacion_mes_anio_PDF($allInputs,$listaV) 
  {
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'cantidad_anual' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }    
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($listaV as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            $arrTable[$rowMes][$rowAno['ano']]['cantidad'] = $row['cantidad']; 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $preKey = $key - 1;
            if( array_key_exists($preKey, $listaV) ) { 
              if( $listaV[$preKey]['cantidad'] > 0 ){
                $difCrecimiento = round(($row['cantidad'] - $listaV[$preKey]['cantidad']) / $listaV[$preKey]['cantidad'],4);
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }else{
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
              }
            }
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['cantidad_anual'] += $row['cantidad'];
          }
          if( $row['ano'] == $rowAno['ano'] ){ 
            //$arrAnos[$keyAno]['cantidad_anual'];
            
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = 0;
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    // var_dump($arrAnos); exit(); 
    foreach ($arrAnos as $key => $row) { 
      $arrTable['footer'][$row['ano']] = $row['cantidad_anual'];
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4,'PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['cantidad'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
        
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $this->pdf->Ln(4);

    $this->pdf->SetFont('Arial','',14);
    $this->pdf->SetTextColor(45,161,82);
    $this->pdf->Cell(0,5,'PRESTACION: *CANTIDAD DE PRODUCTOS VENDIDOS.'); 
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_prestacion_mes_anio_GRAPH($allInputs,$listaV)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 

    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");

    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 
    $contDesde = (int)$allInputs['anioDesde'];
    $arrAnos = array(); 
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    // var_dump("<pre>",$listaV); exit(); 
    $arrSeries = array(); 
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($listaV as $keyDet => $rowDet) {
            if( $value == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){
                if( trim($rowDet['ano']) == trim($value)){
                   $arrSeries[$key]['data'][] = (float)$rowDet['cantidad'];
                   $tuvoVentas = TRUE;
                }
            }
        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }  
    }
    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totalAno = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) {
        $tuvoVentas = FALSE;
        foreach ($listaV as $key => $row) {  
          if( $rowAno == $row['ano'] && $rowMes == $row['nro_mes'] ){ 
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $row['cantidad']; 
            $tuvoVentas = TRUE;
            $totalAno += (float)$row['cantidad'];  
          }
        }
        if(!$tuvoVentas){
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = '0'; 
        }
        
      }
      $tablaDatos[$keyAno]['cantidadtotal'] = $totalAno; 
      $tablaDatos[$keyAno]['dif'] = 0; 
    }
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['dif'] = 0; 
      $preKey = $key - 1; 
      if( array_key_exists($preKey, $tablaDatos) ) { 
        if( $tablaDatos[$key]['cantidadtotal'] > 0 && $tablaDatos[$preKey]['cantidadtotal'] != 0 ){ 
          $difCrecimiento = round(($row['cantidadtotal'] - $tablaDatos[$preKey]['cantidadtotal']) / $tablaDatos[$preKey]['cantidadtotal'],4); 
          $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
        }else{ 
          $tablaDatos[$key]['dif'] = 0; 
        }
      }
      //$tablaDatos[$key]['cantidadtotal'] = '<b>S/. '.number_format($tablaDatos[$key]['cantidadtotal'] ,2).'</b>'; 
    } 
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['cantidadtotal'] = '<b>'.number_format($tablaDatos[$key]['cantidadtotal'] ,2).'</b>'; 
      
      if( $row['dif'] == 0){
        $tablaDatos[$key]['dif'] = '<b> - </b>'; 
      }else{
        $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
        
      }
      
    }
    //var_dump("<pre>",$tablaDatos); exit();
    foreach ($tablaDatos as $key => $row) {
      $tablaDatos[$key] = array_values($tablaDatos[$key]);
    }
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_ticket_promedio_mes_anio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrLista = array();
    $lista = $this->model_estadisticas->m_cargar_ventas_por_anio_mes($allInputs); 
    $listaNC = $this->model_estadisticas->m_cargar_nota_credito_por_anio_mes($allInputs); 
    $listaPr = $this->model_estadisticas->m_cargar_prestaciones_por_anio_mes($allInputs); 
    
    // CARGAR ESTADISTICAS DE AÑOS ANTERIORES SOLO PARA LA SEDE DE VILLA
    if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
      $listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_anos_anteriores($allInputs); 
      $arrAnteriores = array();
      foreach ($listaAnteriores as $key => $row) { 
        array_push($arrAnteriores, 
          array(
            'ano'=> $row['anio'],
            'mes'=> $row['mes'],
            'nro_mes'=> $row['num_mes'],
            'total'=> $row['monto'],
            'cantidad'=> $row['cantidad'],
            'ticket'=> $row['ticket_promedio']  
          )
        ); 
      }
      $lista = array_merge($arrAnteriores,$lista);
      $listaPr = array_merge($arrAnteriores,$listaPr);
      // foreach ( $lista as $key => $row ) { 
      //   foreach ($listaNC as $keyNC => $rowNC) { 
      //     if( $row['ano'] == $rowNC['ano'] && $row['mes'] == $rowNC['mes'] ){ 
      //       $lista[$key]['total'] = $lista[$key]['total'] + $rowNC['total'];
      //     }
      //   }
      // }
    }
    foreach ( $lista as $key => $row ) { 
      foreach ($listaNC as $keyNC => $rowNC) { 
        if( $row['ano'] == $rowNC['ano'] && $row['mes'] == $rowNC['mes'] ){ 
          $lista[$key]['total'] = $lista[$key]['total'] + $rowNC['total'];
          // se agrega la cantidad de ventas para reemplazar a la cantidad de prestaciones hasta determinar bien la logica 
          $lista[$key]['cantidad'] = $lista[$key]['cantidad_venta'] - $rowNC['cantidad_nc'];
        }
      }
    }
    
    foreach ($lista as $key => $row) {
      foreach ($listaPr as $key => $rowPr) { 
        if( $row['ano'] == $rowPr['ano'] && $row['mes'] == $rowPr['mes']){ 
          if(!empty($row['ticket'])){
            $rowPromedioTicket = $row['ticket'];
          }

          if( empty($row['ticket']) ){ 
            $rowPromedioTicket = 0;
            if($rowPr['cantidad'] > 0){
              // $rowPromedioTicket = round($row['total'] / $rowPr['cantidad'],2);
              $rowPromedioTicket = round($row['total'] / $row['cantidad'],2);
            }
            
          }
          array_push($arrLista, 
            array(
              'ano'=> $row['ano'],
              'mes'=> $row['mes'],
              'nro_mes'=> $row['nro_mes'],
              'total'=> $row['total'],
              // 'cantidad'=> $rowPr['cantidad'],
              'cantidad'=> $row['cantidad'],
              'ticket'=> $rowPromedioTicket // TICKET PROMEDIO 
            )
          );
        }
      }
    }
    if( $allInputs['tipoCuadro'] === 'reporte' ){
      $this->report_estadistico_ticket_promedio_mes_anio_PDF($allInputs,$arrLista); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) {
      $this->report_estadistico_ticket_promedio_mes_anio_GRAPH($allInputs,$arrLista); 
    }
  }
  public function report_estadistico_ticket_promedio_mes_anio_PDF($allInputs,$arrLista)
  {
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'monto_anual' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }
    $valorCeroDefault = 0;
    $nameIndexValue = 'ticket';
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($arrLista as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            $strValor = $row[$nameIndexValue]; 
            $arrTable[$rowMes][$rowAno['ano']]['valor'] = $strValor; 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $preKey = $key - 1;
            if( array_key_exists($preKey, $arrLista) ) { 
              if( $arrLista[$preKey][$nameIndexValue] > 0 ){
                $difCrecimiento = round(($row[$nameIndexValue] - $arrLista[$preKey][$nameIndexValue]) / $arrLista[$preKey][$nameIndexValue],4);
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }else{
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
              }
            }
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['monto_anual'] += $row[$nameIndexValue];
          }
          if( $row['ano'] == $rowAno['ano'] ){ 
            //$arrAnos[$keyAno]['monto_anual'];
            
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = $valorCeroDefault;
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    // var_dump($arrAnos); exit(); 
    foreach ($arrAnos as $key => $row) { 
      $valorTotalAno = number_format($row['monto_anual'],2);
      $arrTable['footer'][$row['ano']] = $valorTotalAno;
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4, $allInputs['especialidad']['descripcion'].' / PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['valor'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_ticket_promedio_mes_anio_GRAPH($allInputs,$arrLista)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");

    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 
    $contDesde = (int)$allInputs['anioDesde']; 
    $arrAnos = array(); 
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    $valorCeroDefault = 0;
    $nameIndexValue = 'ticket';
    // var_dump("<pre>",$lista); exit(); 
    $arrSeries = array(); 
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $keyDet => $rowDet) {
            if( $value == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){
                if( trim($rowDet['ano']) == trim($value)){
                   $arrSeries[$key]['data'][] = (float)$rowDet[$nameIndexValue];
                   $tuvoVentas = TRUE;
                }
            }
        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }  
    }

    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totalAno = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $key => $row) {  
          if( $rowAno == $row['ano'] && $rowMes == $row['nro_mes'] ){ 
            $strValor = $row[$nameIndexValue]; 
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $strValor; 
            $tuvoVentas = TRUE;
            $totalAno += (float)$row[$nameIndexValue]; 
          }
        }
        if(!$tuvoVentas){ 
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $valorCeroDefault; 
        }
      }
      $tablaDatos[$keyAno]['valor'] = $totalAno; 
      $tablaDatos[$keyAno]['dif'] = 0; 
    }
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['dif'] = 0; 
      $preKey = $key - 1; 
      if( array_key_exists($preKey, $tablaDatos) ) { 
        if( $tablaDatos[$key]['valor'] > 0 && $tablaDatos[$preKey]['valor'] > 0 ){ 
          $difCrecimiento = round(($row['valor'] - $tablaDatos[$preKey]['valor']) / $tablaDatos[$preKey]['valor'],4); 
          $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
        }else{ 
          $tablaDatos[$key]['dif'] = 0; 
        }
      }
      //$tablaDatos[$key]['valor'] = '<b>S/. '.number_format($tablaDatos[$key]['valor'] ,2).'</b>'; 
    } 
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['valor'] = '<b>'.number_format($tablaDatos[$key]['valor'] ,2).'</b>'; 
      if( $row['dif'] == 0){
        $tablaDatos[$key]['dif'] = '<b> - </b>'; 
      }else{
        $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
        
      }
      
    }
    //var_dump("<pre>",$tablaDatos); exit();
    foreach ($tablaDatos as $key => $row) {
      $tablaDatos[$key] = array_values($tablaDatos[$key]);
    }
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_especialidad_mes_anio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrLista = array();
    $lista = array();
    if( $allInputs['ventaPrestacion'] == 'v' ){ 
      $lista = $this->model_estadisticas->m_cargar_ventas_por_especialidad_anio_mes($allInputs); 
      $listaNC = $this->model_estadisticas->m_cargar_nota_credito_por_especialidad_anio_mes($allInputs); 
    }elseif ( $allInputs['ventaPrestacion'] == 'p' ) {
      $listaPr = $this->model_estadisticas->m_cargar_prestaciones_por_especialidad_anio_mes($allInputs); 
      $listaNC = array();
    }elseif ( $allInputs['ventaPrestacion'] == 'tp' ) {
      $lista = $this->model_estadisticas->m_cargar_ventas_por_especialidad_anio_mes($allInputs); 
      $listaNC = $this->model_estadisticas->m_cargar_nota_credito_por_especialidad_anio_mes($allInputs); 
      $listaPr = $this->model_estadisticas->m_cargar_prestaciones_por_especialidad_anio_mes($allInputs); 
    }

    // CARGAR ESTADISTICAS DE AÑOS ANTERIORES SOLO PARA LA SEDE DE VILLA
    if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
      $listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_por_especialidad_anos_anteriores($allInputs); 
      $arrAnteriores = array();
      foreach ($listaAnteriores as $key => $row) { 
        array_push($arrAnteriores, 
          array(
            'ano'=> $row['anio'],
            'mes'=> $row['mes'],
            'nro_mes'=> $row['num_mes'],
            'total'=> $row['monto'],
            'cantidad'=> $row['cantidad'],
            'ticket'=> $row['ticket_promedio']  
          )
        ); 
      }
      $lista = array_merge($arrAnteriores,$lista);
    }
    foreach ( $lista as $key => $row ) { 
      foreach ($listaNC as $keyNC => $rowNC) { 
        if( $row['ano'] == $rowNC['ano'] && $row['mes'] == $rowNC['mes'] ){ 
          $lista[$key]['total'] = $lista[$key]['total'] + $rowNC['total'];
        }
      }
    }
    if( $allInputs['ventaPrestacion'] == 'v' ){
      $arrLista = $lista;
    }elseif ( $allInputs['ventaPrestacion'] == 'p' ) {
      if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
        $arrLista = array_merge($arrAnteriores,$listaPr);
      }else{
        $arrLista = $listaPr;
      }
      
    }elseif ( $allInputs['ventaPrestacion'] == 'tp' ) { 
      if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
        $listaPr = array_merge($arrAnteriores,$listaPr);

      }
      // var_dump($lista);
      // var_dump($listaPr);
      foreach ($lista as $key => $row) {
        foreach ($listaPr as $key => $rowPr) { 
          if( $row['ano'] == $rowPr['ano'] && $row['mes'] == $rowPr['mes']){ 
            if(!empty($row['ticket'])){
              $rowPromedioTicket = $row['ticket'];
            }
            if( empty($row['ticket']) ){
              $rowPromedioTicket = round($row['total'] / $rowPr['cantidad'],2);
            }
            array_push($arrLista, 
              array(
                'ano'=> $row['ano'],
                'mes'=> $row['mes'],
                'nro_mes'=> $row['nro_mes'],
                'total'=> $row['total'],
                'cantidad'=> $rowPr['cantidad'],
                'ticket'=> $rowPromedioTicket // TICKET PROMEDIO 
              )
            );
          }
        }
      }
      // var_dump($arrLista); exit();
    }
    if( $allInputs['tipoCuadro'] === 'reporte' ){
      $this->report_estadistico_especialidad_mes_anio_PDF($allInputs,$arrLista); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) {
      $this->report_estadistico_especialidad_mes_anio_GRAPH($allInputs,$arrLista); 
    }
  }
  private function report_estadistico_especialidad_mes_anio_PDF($allInputs,$lista)
  {
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'monto_anual' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }
    $valorCeroDefault = 0;
    if($allInputs['ventaPrestacion'] == 'v' ){
      $nameIndexValue = 'total';
      $valorCeroDefault = 'S/. 0.00';
    }elseif ( $allInputs['ventaPrestacion'] == 'p' ) {
      $nameIndexValue = 'cantidad';
    }elseif ( $allInputs['ventaPrestacion'] == 'tp' ) {
      $nameIndexValue = 'ticket';
    }
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($lista as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            $strValor = $row[$nameIndexValue]; 
            if( $allInputs['ventaPrestacion'] == 'v' ){
              $strValor = 'S/. '.number_format($row[$nameIndexValue],2); 
            }
            $arrTable[$rowMes][$rowAno['ano']]['valor'] = $strValor; 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $preKey = $key - 1;
            if( array_key_exists($preKey, $lista) ) { 
              if( $lista[$preKey][$nameIndexValue] > 0 ){
                $difCrecimiento = round(($row[$nameIndexValue] - $lista[$preKey][$nameIndexValue]) / $lista[$preKey][$nameIndexValue],4);
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }else{
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
              }
            }
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['monto_anual'] += $row[$nameIndexValue];
          }
          if( $row['ano'] == $rowAno['ano'] ){ 
            //$arrAnos[$keyAno]['monto_anual'];
            
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = $valorCeroDefault;
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    // var_dump($arrAnos); exit(); 
    foreach ($arrAnos as $key => $row) { 
      $valorTotalAno = number_format($row['monto_anual'],0);
      if( $allInputs['ventaPrestacion'] == 'v' ){
        $valorTotalAno = 'S/. '.number_format($row['monto_anual'],2);
      }
      if( $allInputs['ventaPrestacion'] == 'tp' ){
        $valorTotalAno = number_format($row['monto_anual'],2);
      }
      $arrTable['footer'][$row['ano']] = $valorTotalAno;
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4, $allInputs['especialidad']['descripcion'].' / PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['valor'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_especialidad_mes_anio_GRAPH($allInputs,$lista)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");

    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 
    $contDesde = (int)$allInputs['anioDesde'];
    $arrAnos = array(); 
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    $valorCeroDefault = 0;
    if($allInputs['ventaPrestacion'] == 'v' ){
      $nameIndexValue = 'total';
      $valorCeroDefault = 'S/. 0.00';
    }elseif ( $allInputs['ventaPrestacion'] == 'p' ) {
      $nameIndexValue = 'cantidad';
    }elseif ( $allInputs['ventaPrestacion'] == 'tp' ) {
      $nameIndexValue = 'ticket';
    }
    $arrSeries = array(); 
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $valueMes) { 
        $boolFillValue = FALSE;
        foreach ($lista as $keyDet => $rowDet) {
          if( trim($rowDet['ano']) == trim($value) && trim($rowDet['nro_mes']) == trim($valueMes) ){ 
            $valorIndicado = (float)$rowDet[$nameIndexValue];
            $boolFillValue = TRUE;
          }
        }
        
        if( $boolFillValue ){
          $arrSeries[$key]['data'][] = $valorIndicado;
          $valorIndicado = NULL;
        }else{
          $arrSeries[$key]['data'][] = NULL;
        }
      }
      
    }
    //var_dump($lista); exit();
    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totalAno = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($lista as $key => $row) {  
          if( $rowAno == $row['ano'] && $rowMes == $row['nro_mes'] ){ 
            $strValor = $row[$nameIndexValue]; 
            if( $allInputs['ventaPrestacion'] == 'v' ){
              $strValor = 'S/. '.number_format($row[$nameIndexValue],2); 
            }
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $strValor; 
            $tuvoVentas = TRUE;
            $totalAno += (float)$row[$nameIndexValue]; 
          }
        }
        if(!$tuvoVentas){ 
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $valorCeroDefault; 
        }
      }
      $tablaDatos[$keyAno]['valor'] = $totalAno; 
      $tablaDatos[$keyAno]['dif'] = 0; 
    }
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['dif'] = 0; 
      $preKey = $key - 1; 
      if( array_key_exists($preKey, $tablaDatos) ) { 
        if( $tablaDatos[$key]['valor'] > 0 && $tablaDatos[$preKey]['valor'] > 0 ){ 
          $difCrecimiento = round(($row['valor'] - $tablaDatos[$preKey]['valor']) / $tablaDatos[$preKey]['valor'],4); 
          $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
        }else{ 
          $tablaDatos[$key]['dif'] = 0; 
        }
      }
    } 
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['valor'] = '<b>'.number_format($tablaDatos[$key]['valor'] ,2).'</b>'; 
      if( $row['dif'] == 0){
        $tablaDatos[$key]['dif'] = '<b> - </b>'; 
      }else{
        $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
      }
    }
    //var_dump("<pre>",$tablaDatos); exit();
    foreach ($tablaDatos as $key => $row) {
      $tablaDatos[$key] = array_values($tablaDatos[$key]);
    }
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_especialidad_detallado_mes_anio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $fechaMergeDesde = '01-'.$allInputs['mesDesdeCbo'].'-'.$allInputs['anioDesdeCbo']; 
    $fechaMergeHasta = date('Y-m',strtotime("$fechaMergeDesde+2month")); 
    if($allInputs['tiposalida'] === 'grafico'){
      $fechaMergeHasta = date('Y-m',strtotime("$fechaMergeDesde+5month")); 
    }
    
    $allInputs['arrStrMeses'] = get_rangomeses($fechaMergeDesde,$fechaMergeHasta,FALSE);
    $allInputs['arrMeses'] = array();
    foreach ($allInputs['arrStrMeses'] as $key => $value) { 
      $allInputs['arrMeses'][] = date('m',strtotime($value."-01"));
    }
    $allInputs['anioHastaCbo'] = date('Y',strtotime("$fechaMergeHasta")); 
    $arrLista['detallado'] = $this->model_estadisticas->m_cargar_detallado_por_especialidad_anio_mes($allInputs); 
    $arrLista['nota_credito'] = $this->model_estadisticas->m_cargar_total_nota_credito_por_especialidad_anio_mes($allInputs); 
    $arrLista['group_producto'] = array();
    foreach ( $arrLista['detallado'] as $key => $row ) { 
      $arrLista['group_producto'][$row['idproductomaster']] = array( 
        'idproductomaster'=> $row['idproductomaster'],
        'descripcion'=> $row['descripcion'],
        'total'=> NULL
      );
    }
    foreach ($arrLista['group_producto'] as $key => $row) {
      foreach ($arrLista['detallado'] as $keyDet => $rowDet) {
        if( $row['idproductomaster'] == $rowDet['idproductomaster'] ){
          $arrLista['group_producto'][$rowDet['idproductomaster']]['total'] += $rowDet['monto'];
        }
      }
    }
    
    // var_dump($arrLista['group_producto']); exit(); 
    if( $allInputs['tiposalida'] === 'pdf' ){ 
      $this->report_estadistico_especialidad_detallado_mes_anio_PDF($allInputs,$arrLista); 
    }elseif ( $allInputs['tiposalida'] === 'grafico' ) { 
      /*function fnOrdering($a, $b) { 
        return $b['total'] - $a['total'];
      }*/
      usort($arrLista['group_producto'],'fnOrdering');
      $arrLista['group_producto'] = array_slice($arrLista['group_producto'], 0, 5);
      $this->report_estadistico_especialidad_detallado_mes_anio_GRAPH($allInputs,$arrLista); 
    }elseif ( $allInputs['tiposalida'] === 'excel' ) {
      $this->report_estadistico_especialidad_detallado_mes_anio_EXCEL($allInputs,$arrLista); 
    }
  }
  private function report_estadistico_especialidad_detallado_mes_anio_PDF($allInputs,$arrLista)
  {
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrMeses = array();
    foreach ($allInputs['arrMeses'] as $key => $value){ 
      $arrMeses[] = array(
        'nro_mes'=> (int)$value,
        'mes'=> strtoupper($longMonthArray[(int)$value]),
        'monto_mensual' => NULL,
        'cant_mensual' => NULL
      );
    }
    $arrProductos = array();
    $arrTable[0] = array(
      'producto'=> utf8_decode('PRODUCTO')
    );
    foreach ($arrMeses as $key => $row) { 
      $arrTable[0][$row['mes']] = $row['mes'];
    }
    $lista = $arrLista['detallado']; 
    $valorCeroDefault = 0;
    $nameIndexValue = 'monto';
    $nameIndexCant = 'cantidad'; 
    // var_dump($arrLista['nota_credito']); exit();
    foreach ($arrLista['group_producto'] as $keyPrinc => $rowPrinc) { 
      $arrTable[$rowPrinc['idproductomaster']]['producto'] = strtoupper($rowPrinc['descripcion']); 
      foreach ($arrMeses as $keyMes => $rowMes) { 
        $boolNoData = FALSE;
        foreach ($lista as $key => $row) { 
          if( $row['mes'] == $rowMes['nro_mes'] && $row['idproductomaster'] == $rowPrinc['idproductomaster'] ) { 
            $strValor = 'S/. '.number_format($row[$nameIndexValue],2); 
            $strCant = $row[$nameIndexCant]; 
            $arrTable[$rowPrinc['idproductomaster']][$rowMes['nro_mes']]['valor'] = $strValor; 
            $arrTable[$rowPrinc['idproductomaster']][$rowMes['nro_mes']]['cantidad'] = $strCant; 
            $arrTable[$rowPrinc['idproductomaster']][$rowMes['nro_mes']]['dif_crecimiento'] = ''; 
            $boolNoData = TRUE;
            $arrMeses[$keyMes]['monto_mensual'] += $row[$nameIndexValue];
            $arrMeses[$keyMes]['cant_mensual'] += $row[$nameIndexCant];
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowPrinc['idproductomaster']][$rowMes['nro_mes']] = array(
            'valor'=> 'S/. 0.00',
            'cantidad'=> 0,
            'dif_crecimiento'=> '-'
          );
        }
        $boolNoDataNC = FALSE;
        foreach ($arrLista['nota_credito'] as $keyNC => $rowNC){ 
          if( $rowMes['nro_mes'] == $rowNC['mes'] ){ // var_dump('djkasfn'); exit();
            $arrMeses[$keyMes]['monto_mensual_nc'] = $rowNC[$nameIndexValue];
            $arrMeses[$keyMes]['cant_mensual_nc'] = $rowNC[$nameIndexCant]; 
            $boolNoDataNC = TRUE;
          }
        }
        if( !($boolNoDataNC) ){ 
          $arrMeses[$keyMes]['monto_mensual_nc'] = 0;
          $arrMeses[$keyMes]['cant_mensual_nc'] = 0; 
        }
        
      }
    } 
    $arrTable['footerST'] = array(
      'producto'=> 'SUBTOTAL'
    ); 
    $arrTable['footerNC'] = array(
      'producto'=> 'NOTA DE CREDITO'
    ); 
    $arrTable['footerT'] = array(
      'producto'=> 'TOTAL'
    ); 
    // var_dump($arrMeses); exit(); 
    $strMeses = '';
    foreach ($arrMeses as $key => $row) { 
      $arrTable['footerST'][$row['mes']]['valor'] = 'S/. '.number_format($row['monto_mensual'],2);
      $arrTable['footerST'][$row['mes']]['cantidad'] = $row['cant_mensual']; 

      $arrTable['footerNC'][$row['mes']]['valor'] = 'S/. '.number_format($row['monto_mensual_nc'],2);
      $arrTable['footerNC'][$row['mes']]['cantidad'] = $row['cant_mensual_nc']; 

      $arrTable['footerT'][$row['mes']]['valor'] = 'S/. '.number_format(($row['monto_mensual'] + $row['monto_mensual_nc']),2);
      $arrTable['footerT'][$row['mes']]['cantidad'] = '';

      $strMeses .= $row['mes'].' ';
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4, $allInputs['especialidad']['descripcion'].' / PERIODO: [ '.$strMeses.']','','','C'); 
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    //var_dump($arrTable); //exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        // $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 50;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        
        if($key === 'footerNC'){ //var_dump($row,$rowValue,$key); exit(); 
            $this->pdf->SetFont('Arial','',14); 
            $this->pdf->SetTextColor(137,23,23);
            $textAlign = 'C';
            $this->pdf->SetFillColor(243,247,255);
            $fill = TRUE;
        }
        if($key === 'footerST'){ //var_dump($row,$rowValue,$key); exit(); 
            $this->pdf->SetFont('Arial','',14); 
            $this->pdf->SetTextColor(55);
            $textAlign = 'C';
            $this->pdf->SetFillColor(243,247,255);
            $fill = TRUE;
        }
        if($key === 'footerT'){ //var_dump($row,$rowValue,$key); exit(); 
            $this->pdf->SetFont('Arial','B',14); 
            $this->pdf->SetTextColor(0);
            $textAlign = 'C';
            $this->pdf->SetFillColor(243,247,255);
            $fill = TRUE;
        }
        if($key === 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          $this->pdf->SetTextColor(0);
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 50;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 32;
          $widthCellPorc = 18;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['valor'],1,0,'R', $fill);
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['cantidad'],1,0,$textAlign, $fill); 
          //$this->pdf->SetTextColor(0);
          // if( $rowValue['dif_crecimiento'] < 0 ){ 
          //   $this->pdf->SetTextColor(225,22,22);
          // }
          // if( $rowValue['dif_crecimiento'] != "-" ){
          //   $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          // }
          //$this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue === 'producto' ){
            $widthCell = 126;
          } 
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_especialidad_detallado_mes_anio_GRAPH($allInputs,$arrLista)
  {
    $longMonthArray = array("","ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $arrMesesX = array(); // var_dump($arrLista['group_producto']); exit(); 
    foreach($allInputs['arrMeses'] as $key => $value){
      $arrMesesX[] = $longMonthArray[(int)$value];
    }
    $nameIndexValue = 'monto';
    $arrSeries = array(); // var_dump($allInputs['arrMeses']); exit(); 
    foreach ($arrLista['group_producto'] as $keyProd => $rowProd) { 
      $arrSeries[$keyProd] = array( 
        'name'=> $rowProd['descripcion'],
        'data' => array()
      );
      //$tieneData = FALSE;
      foreach ($allInputs['arrMeses'] as $key => $value) { 
        $arrFields = array('mes','idproductomaster');
        $arrValues = array((int)$value,$rowProd['idproductomaster']);
        $keyObt = getIndexArrayByValue($arrLista['detallado'],$arrFields,$arrValues); 
        if( $keyObt === FALSE ){ 
          $arrSeries[$keyProd]['data'][] = 0;
        }else{
          $arrSeries[$keyProd]['data'][] = (float)$arrLista['detallado'][$keyObt][$nameIndexValue];
        }
      }
    }
    //var_dump($arrSeries); exit(); 
    $arrSeries = array_values($arrSeries);
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $arrMesesX,
      'series'=> $arrSeries,
      'tipoGraphic'=> 'bar',
      'tieneTabla'=> FALSE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_concentracion_de_uso()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrLista = array();
    $sc_idEspecialidad = 'sc_v.idespecialidad';
    $idEspecialidad = 'v.idespecialidad';
    $idEspecialidadAA = FALSE;
    $allInputs['logicaPacienteNC']['id'] == 'RVA';
    $lista = $this->model_estadisticas->m_cargar_paciente_nuevo_logica_3_y_consulta_externa($allInputs,$sc_idEspecialidad,$idEspecialidad); // RVA 
    if( $allInputs['tipoCuadro'] === 'reporte' ){ 
      $this->report_estadistico_concentracion_de_uso_PDF($allInputs,$lista); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) { 
      $this->report_estadistico_concentracion_de_uso_GRAPH($allInputs,$lista); 
    }
  }
  public function report_estadistico_concentracion_de_uso_PDF($allInputs,$arrLista) 
  {
    $listaV = $arrLista;
    $indexListaCCE = 'cce';
    $indexListaPN = 'pn';
    $allInputs['titulo'] = ('CONCENTRACIÓN DE USO');
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = array( 
        'ano'=> $contDesde,
        'valor_anual_cce' => NULL,
        'valor_anual_pn' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($listaV as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            if(empty($row[$indexListaCCE])){
              $row[$indexListaCCE] = 0;
            }
            if(empty($row[$indexListaPN])){
              $row[$indexListaPN] = 0;
            }
            $arrTable[$rowMes][$rowAno['ano']]['cant_cce'] = ($row[$indexListaCCE]); 
            $arrTable[$rowMes][$rowAno['ano']]['cant_pn'] = ($row[$indexListaPN]); 
            $arrTable[$rowMes][$rowAno['ano']]['cant_cu'] = '-';
            if( !empty($row[$indexListaPN]) ){
              $arrTable[$rowMes][$rowAno['ano']]['cant_cu'] = number_format(round($row[$indexListaCCE] / $row[$indexListaPN],2),2); 
            }
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 

            $boolNoData = TRUE;
            $arrAnos[$keyAno]['valor_anual_cce'] += $row[$indexListaCCE];
            $arrAnos[$keyAno]['valor_anual_pn'] += $row[$indexListaPN];
            //$arrAnos[$keyAno]['valor_anual_cu'] += round($row[$indexListaCCE] / $row[$indexListaPN],2);
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = '0';
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'PROMEDIO'
    ); 
    foreach ($arrAnos as $key => $row) { 
      $arrTable['footer'][$row['ano']]['cant_cce'] = $row['valor_anual_cce'];
      $arrTable['footer'][$row['ano']]['cant_pn'] = $row['valor_anual_pn'];
      if( empty($row['valor_anual_pn']) ){ 
        $arrTable['footer'][$row['ano']]['cant_cu'] = '-';
      }else{ 
        $arrTable['footer'][$row['ano']]['cant_cu'] = round($row['valor_anual_cce']/$row['valor_anual_pn'],2);
      }
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4,'PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(8); 

    $this->pdf->SetFillColor(224,235,255);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellCant = 20;
          $this->pdf->SetTextColor(13,172,19);
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_cce'],1,0,$textAlign, $fill);
          $this->pdf->SetTextColor(20,37,153);
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_pn'],1,0,$textAlign, $fill);
          $this->pdf->SetTextColor(0,0,0);
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_cu'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $this->pdf->SetFont('Times','I',10);
    $this->pdf->setTextColor(13,172,19); 
    $this->pdf->Cell(0,10,'* CANTIDAD DE CONSULTAS EXTERNAS.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(20,37,153); 
    $this->pdf->Cell(0,10,'* CANTIDAD DE PACIENTES NUEVOS.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(0,0,0);
    $this->pdf->SetFont('Times','IB',10); 
    $this->pdf->Cell(0,10,utf8_decode('* CONCENTRACIÓN DE USO.'));
    $this->pdf->Ln(5);

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_concentracion_de_uso_GRAPH($allInputs,$arrLista)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");
    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 

    /* JSON DE GRAFICO */
    $arrAnos = array(); 
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    $arrSeries = array(); 
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $keyDet => $row) {
          // if( trim($value) == 'Clientes Nuevos'){ 
          //    $arrSeries[$key]['data'][] = (float)$rowDet['pn'];
          // }
          if( trim($value) == $row['ano'] && $rowMes == $row['nro_mes']){ 
            if( empty($row['pn']) ){
              $concentracionUso = 0;
            }else{
              $concentracionUso = round($row['cce'] / $row['pn'],2);
            }
            $arrSeries[$key]['data'][] = (float)$concentracionUso;
            $tuvoVentas = TRUE;
          }
        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }
    }
    /* JSON DE TABLA */
    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totaFilas = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $key => $row) { 
          if( trim($rowAno) == trim($row['ano']) && $rowMes == $row['nro_mes']){ 
            if( empty($row['pn']) ){
              $concentracionUso = 0;
            }else{
              $concentracionUso = round($row['cce'] / $row['pn'],2);
            }
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $concentracionUso; 
            $tuvoVentas = TRUE;
            $totaFilas += (float)$concentracionUso;
          }
        }
        if(!$tuvoVentas){
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = '0.00'; 
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
    }
    //var_dump("<pre>",$tablaDatos); exit();
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_cliente_nuevo_continuador()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrLista = array();
    if( $allInputs['especialidad']['id'] == 'ALL' ){ 
      $sc_idEspecialidad = 'sc_v.idespecialidad';
      $idEspecialidad = 'v.idespecialidad';
      $idEspecialidadAA = FALSE;
    }else{
      $sc_idEspecialidad = $allInputs['especialidad']['id'];
      $idEspecialidad = $allInputs['especialidad']['id'];
      $idEspecialidadAA = $allInputs['especialidad']['id'];
    } 
    // if($allInputs['tipoCuadro'] === 'grafico'){
    if($allInputs['idTipoRango'] === '1'){
      $allInputs['anioDesde'] = $allInputs['anio'];
      $allInputs['anioHasta'] = $allInputs['anio'];
    }
   
    // var_dump('LOGICA');
    // var_dump($allInputs['logicaPacienteNC']);
    if( $allInputs['logicaPacienteNC']['id'] == 'R' ){
      $lista = $this->model_estadisticas->m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_1($allInputs,$sc_idEspecialidad,$idEspecialidad);
    }elseif ( $allInputs['logicaPacienteNC']['id'] == 'RV' ) {
      $lista = $this->model_estadisticas->m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_2($allInputs,$sc_idEspecialidad,$idEspecialidad); 
    }elseif ( $allInputs['logicaPacienteNC']['id'] == 'RVA' ) {
      $lista = $this->model_estadisticas->m_cargar_paciente_nuevo_y_continuador_por_anio_mes_logica_3($allInputs,$sc_idEspecialidad,$idEspecialidad); 
    }
    
    // CARGAR ESTADISTICAS DE AÑOS ANTERIORES SOLO PARA LA SEDE DE VILLA
    if( $allInputs['sede']['id'] == 1 && $allInputs['empresaAdmin']['id'] == 0){
      $listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_anos_anteriores($allInputs,$idEspecialidadAA); 
      $arrAnteriores = array();
      foreach ($listaAnteriores as $key => $row) { 
        array_push($arrAnteriores, 
          array(
            'ano'=> $row['anio'],
            'mes'=> $row['mes'],
            'nro_mes'=> $row['num_mes'],
            'pc'=> ($row['ptodos'] - $row['pacientes_nuevos']),
            'pn'=> $row['pacientes_nuevos'],
            'ptodos'=> $row['ptodos']
          )
        ); 
      } 
      $lista = array_merge($arrAnteriores,$lista); // exit();
    }
      // var_dump($lista); exit();
    

    foreach ($lista as $key => $row) { 
      $lista[$key]['pc'] = ($row['ptodos'] - $row['pn']); 
    }
    // var_dump($lista); exit();
    if( $allInputs['tipoCuadro'] === 'reporte' ){ 
      if( $allInputs['pacienteNC']['id'] == 'PN' || $allInputs['pacienteNC']['id'] == 'PC' ){
        $this->report_estadistico_cliente_nuevo_o_continuador_PDF($allInputs,$lista); 
      }elseif( $allInputs['pacienteNC']['id'] == 'ALL' ){
        $this->report_estadistico_cliente_nuevo_y_continuador_PDF($allInputs,$lista); 
      }
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' && $allInputs['idTipoRango'] === '1' && $allInputs['pacienteNC']['id'] == 'ALL' ) { 
      $this->report_estadistico_cliente_nuevo_continuador_GRAPH($allInputs,$lista); 
    }elseif ( $allInputs['tipoCuadro'] === 'grafico' && $allInputs['idTipoRango'] === '2' && $allInputs['pacienteNC']['id'] != 'ALL' ) { 
      $this->report_estadistico_cliente_nuevo_o_continuador_GRAPH($allInputs,$lista); 
    }
  }
  private function report_estadistico_cliente_nuevo_o_continuador_PDF($allInputs,$arrLista)
  {
    $listaV = $arrLista;
    $indexLista = NULL;
    if( $allInputs['pacienteNC']['id'] == 'PN' ){ 
      $indexLista = 'pn';
      $allInputs['titulo'] = 'PACIENTES NUEVOS';
    }elseif( $allInputs['pacienteNC']['id'] == 'PC' ){ 
      $indexLista = 'pc';
      $allInputs['titulo'] = 'PACIENTES CONTINUADORES';
    }
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'valor_anual' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($listaV as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            if(empty($row[$indexLista])){
              $row[$indexLista] = 0;
            }
            $arrTable[$rowMes][$rowAno['ano']]['monto'] = ($row[$indexLista]); 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $preKey = $key - 1;
            if( array_key_exists($preKey, $listaV) ) { 
              if( $listaV[$preKey][$indexLista] > 0 ){ 
                $difCrecimiento = ($row[$indexLista] - $listaV[$preKey][$indexLista]) / $listaV[$preKey][$indexLista];
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }else{
                $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
              }
            }
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['valor_anual'] += $row[$indexLista];
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = '0';
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    foreach ($arrAnos as $key => $row) { 
      $arrTable['footer'][$row['ano']] = $row['valor_anual'];
    } 
    
    // var_dump($arrTable); exit(); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4,'PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(10); 
    if( !empty($allInputs['especialidad']['id']) ){ 
      $this->pdf->SetFont('Arial','',12); 
      $this->pdf->Cell(40,5,utf8_decode('ESPECIALIDAD:'));
      $this->pdf->Cell(120,5,utf8_decode($allInputs['especialidad']['descripcion']));
      $this->pdf->Ln(5); 
    }
    if( !empty($allInputs['logicaPacienteNC']['descripcion']) ){ 
      $this->pdf->SetFont('Arial','',12); 
      $this->pdf->Cell(40,5,utf8_decode('LÓGICA DE C.N: '));
      $this->pdf->Cell(120,5,utf8_decode($allInputs['logicaPacienteNC']['descripcion']));
      $this->pdf->Ln(5); 
    }
    
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['monto'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
        
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_cliente_nuevo_y_continuador_PDF($allInputs,$arrLista)
  {
    $listaV = $arrLista;
    //$indexLista = NULL;
    //elseif( $allInputs['pacienteNC']['id'] == 'ALL' ){ 
    $indexListaPC = 'pc';
    $indexListaPN = 'pn';
    $allInputs['titulo'] = 'PACIENTES NUEVOS Y CONTINUADORES';
    //}
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = array(
        'ano'=> $contDesde,
        'valor_anual_pc' => NULL,
        'valor_anual_pn' => NULL,
        'valor_anual_all' => NULL
      );
      $contDesde++;
    }
    $arrMeses = array();
    $contDesdeMes = 1;
    while ( $contDesdeMes <= 12 ) {
      $arrMeses[] = $contDesdeMes;
      $contDesdeMes++;
    }
    $arrTable[0] = array(
      'mes'=> utf8_decode('MES/AÑO')
    );
    foreach ($arrAnos as $key => $row) { 
      $arrTable[0][$row['ano']] = $row['ano'];
    }
    foreach ($arrMeses as $keyMes => $rowMes) { 
      $arrTable[$rowMes]['mes'] = strtoupper($longMonthArray[$rowMes]);
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $boolNoData = FALSE;
        foreach ($listaV as $key => $row) { 
          if( $row['nro_mes'] == $rowMes && $row['ano'] == $rowAno['ano'] ) { 
            if(empty($row[$indexListaPC])){
              $row[$indexListaPC] = 0;
            }
            if(empty($row[$indexListaPN])){
              $row[$indexListaPN] = 0;
            }
            $arrTable[$rowMes][$rowAno['ano']]['cant_pc'] = ($row[$indexListaPC]); 
            $arrTable[$rowMes][$rowAno['ano']]['cant_pn'] = ($row[$indexListaPN]); 
            $arrTable[$rowMes][$rowAno['ano']]['cant_all'] = ( $row[$indexListaPN] + $row[$indexListaPC] ); 
            $arrTable[$rowMes][$rowAno['ano']]['dif_crecimiento'] = '-'; 
            $boolNoData = TRUE;
            $arrAnos[$keyAno]['valor_anual_pc'] += $row[$indexListaPC];
            $arrAnos[$keyAno]['valor_anual_pn'] += $row[$indexListaPN];
            $arrAnos[$keyAno]['valor_anual_all'] += ( $row[$indexListaPN] + $row[$indexListaPC] );
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowMes][$rowAno['ano']] = '0';
        }
      }
    } 
    $arrTable['footer'] = array(
      'mes'=> 'TOTAL'
    ); 
    // var_dump($arrAnos); exit(); 
    foreach ($arrAnos as $key => $row) { 
      $arrTable['footer'][$row['ano']] = $row['valor_anual_all'];
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4,'PERIODO: '.$allInputs['anioDesde'].' - '.$allInputs['anioHasta'],'','','C'); 
    $this->pdf->Ln(8); 

    if( !empty($allInputs['especialidad']['id']) ){ 
      $this->pdf->SetFont('Arial','B',12); 
      $this->pdf->Cell(40,5,utf8_decode('ESPECIALIDAD'));
      $this->pdf->Cell(5,5,'  : ');
      $this->pdf->SetFont('Arial','',12); 
      $this->pdf->Cell(120,5,utf8_decode($allInputs['especialidad']['descripcion']));
      $this->pdf->Ln(5); 
    }
    if( !empty($allInputs['logicaPacienteNC']['descripcion']) ){ 
      $this->pdf->SetFont('Arial','B',12); 
      $this->pdf->Cell(40,5,utf8_decode('LÓGICA DE C.N: '));
      $this->pdf->Cell(5,5,'  : ');
      $this->pdf->SetFont('Arial','',12); 
      $this->pdf->Cell(120,5,utf8_decode($allInputs['logicaPacienteNC']['descripcion']));
      $this->pdf->Ln(5); 
    }
    $this->pdf->Ln(5); 

    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellCant = 20;
          //$widthCellPorc = 22;
          
          $this->pdf->SetTextColor(13,172,19);
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_pn'],1,0,$textAlign, $fill);
          $this->pdf->SetTextColor(20,37,153);
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_pc'],1,0,$textAlign, $fill);
          $this->pdf->SetTextColor(0,0,0);
          $this->pdf->SetFont('Arial','B',12); 
          $this->pdf->Cell($widthCellCant,$heightCell,$rowValue['cant_all'],1,0,$textAlign, $fill);
          $this->pdf->SetFont('Arial','',12); 
        }else{ 
          if( $keyValue == 'mes' ){
            $widthCell = 38;
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $this->pdf->SetFont('Times','I',10);
    $this->pdf->setTextColor(13,172,19); 
    $this->pdf->Cell(0,10,'* CANTIDAD DE PACIENTES NUEVOS.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(20,37,153); 
    $this->pdf->Cell(0,10,'* CANTIDAD DE PACIENTES CONTINUADORES.');
    $this->pdf->Ln(5);
    // $this->pdf->setTextColor(0); 
    // $this->pdf->Cell(0,10,'* (%) TOTAL DE PACIENTES.');
    // $this->pdf->Ln(5);
    $this->pdf->setTextColor(0,0,0);
    $this->pdf->SetFont('Times','IB',10); 
    $this->pdf->Cell(0,10,'* TOTAL DE PACIENTES.');
    $this->pdf->Ln(5);

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_cliente_nuevo_continuador_GRAPH($allInputs,$arrLista)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");
    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 

    /* JSON DE GRAFICO */
    $arrTipoClientes = array('Clientes Nuevos','Clientes Continuadores');
    $arrSeries = array(); 
    foreach ($arrTipoClientes as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $keyDet => $rowDet) {

            if( trim($value) == 'Clientes Nuevos' && $rowMes == $rowDet['nro_mes']){ 
               $arrSeries[$key]['data'][] = (float)$rowDet['pn'];
               $tuvoVentas = TRUE;
            }
            if( trim($value) == 'Clientes Continuadores' && $rowMes == $rowDet['nro_mes']){ 
               $arrSeries[$key]['data'][] = ((float)$rowDet['ptodos'] - (float)$rowDet['pn']);
               $tuvoVentas = TRUE;
            } 

        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }
    }
    /* JSON DE TABLA */
    $tablaDatos = array();
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
    }
    //var_dump("<pre>",$tablaDatos); exit();
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  private function report_estadistico_cliente_nuevo_o_continuador_GRAPH($allInputs,$arrLista){ /* GRAFICA NUEVA */
    $listaV = $arrLista;
    $indexLista = NULL;
    if( $allInputs['pacienteNC']['id'] == 'PN' ){ 
      $indexLista = 'pn';
      $allInputs['titulo'] = 'PACIENTES NUEVOS';
    }elseif( $allInputs['pacienteNC']['id'] == 'PC' ){ 
      $indexLista = 'pc';
      $allInputs['titulo'] = 'PACIENTES CONTINUADORES';
    }
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");
    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12);   
    // TRATAMIENTO DE DATOS
    $arrAnos = array();
    
    $contDesde = (int)$allInputs['anioDesde'];
    while ( $contDesde <= $allInputs['anioHasta'] ) {
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    /* JSON DE GRAFICO */
      $arrSeries = array();
      foreach ($arrAnos as $key => $value) { 
        $arrSeries[$key] = array(
            'name'=> $value,
            'data' => array()
        );
        foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
          $tuvoVentas = FALSE;
          foreach ($listaV as $keyDet => $rowDet) { 
            if( $value == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){
              // if( trim($rowDet['ano']) == trim($value)){
                 $arrSeries[$key]['data'][] = (float)$rowDet[$indexLista];
                 $tuvoVentas = TRUE;
              // }
            }
          }
          if(!$tuvoVentas){
            $arrSeries[$key]['data'][] = NULL; 
          }
        }
      }
    /* JSON DE TABLA DE DATOS */ 
      $tablaDatos = array();
      // if($anioAnt != null){
      //   $valorAnt = $anioAnt[0]['diciembre'];
      // }else{
      //   $valorAnt = 0;
      // }
      
      /*foreach ($arrAnos as $key => $value) { 
        $band = false;
        $i=1;

        foreach ($arrLista as $keyLista => $rowLista) {         
          if( $value['anio'] == $rowLista['anio'] ){
            $band = true;
            $tablaDatos[$key][$i-1] = $value['anio'];
            if ($keyLista > 0) {
              $valorAnt = $arrLista[$keyLista-1]['diciembre'];
            }           
            foreach ($rowLista as $keyRow => $valueRow) {
              $tablaDatos[$key][$i] = (float)$valueRow;
              if($valueRow != $value && $i < ($countArrayMesesTabla-2)){                
                if($valorAnt != 0 ){
                  $tablaDatos[$key][$i+1] = round((($valueRow/$valorAnt)-1)*100);
                }else{
                  $tablaDatos[$key][$i+1] = ' - ';
                }  
 
                if ($i < ($countArrayMesesTabla-2)) {
                  $valorAnt = (float)$valueRow; 
                }
                
                $i+=2;  
              }             
            }
          }
        } 

        if(!$band){  
          for ($i=0; $i <= $countArrayMesesTabla-2; $i++) {           
            if ($i == 0) {
              $tablaDatos[$key][$i] = $value;
            }else{
              $tablaDatos[$key][$i] = 0;
            }      
          }
        }            
      }*/

      $tablaDatos = array();
      foreach ($arrAnos as $keyAno => $rowAno) { 
        $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
        $totalAno = 0; 
        foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
          $tuvoVentas = FALSE;
          foreach ($listaV as $keyDet => $rowDet) {
            $preKey = $keyDet - 1;
            $porcentajeStr = '<br>(-)'; 
            if( array_key_exists($preKey, $listaV) ) { 
              if( $listaV[$preKey][$indexLista] > 0 ){ 
                $difCrecimiento = round( ( ($rowDet[$indexLista] - $listaV[$preKey][$indexLista]) / $listaV[$preKey][$indexLista] * 100 ), 2);
                if($difCrecimiento >= 0){
                  $color = '#a1ffa5'; // verde claro
                }else{
                  $color = '#ffb7b9'; // rojo claro
                }
                $porcentajeStr =  '<br>(<span style="color:'. $color .'">'.$difCrecimiento.'%</span>)';
              }
            }


            if( $rowAno == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){ 
              $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $rowDet[$indexLista] . $porcentajeStr; 
              $tuvoVentas = TRUE;
              $totalAno += (float)$rowDet[$indexLista];  
            }
          }
          if(!$tuvoVentas){
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = '0.00'; 
            // $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = (float)('0.00'); 
          }
          
        }
        $tablaDatos[$keyAno]['sumtotal'] = $totalAno; 
        $tablaDatos[$keyAno]['dif'] = 0; 
      }
      foreach ($tablaDatos as $key => $row) { 
        $tablaDatos[$key]['dif'] = 0; 
        $preKey = $key - 1; 
        if( array_key_exists($preKey, $tablaDatos) ) { 
          if( $tablaDatos[$key]['sumtotal'] > 0 && $tablaDatos[$preKey]['sumtotal'] != 0 ){ 
            $difCrecimiento = round(($row['sumtotal'] - $tablaDatos[$preKey]['sumtotal']) / $tablaDatos[$preKey]['sumtotal'],4); 
            $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
          }else{ 
            $tablaDatos[$key]['dif'] = 0; 
          }
        }
        //$tablaDatos[$key]['sumtotal'] = '<b>S/. '.number_format($tablaDatos[$key]['sumtotal'] ,2).'</b>'; 
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
      }    
    /*var_dump($longMonthTableArray);
    var_dump($tablaDatos);  
    exit();*/
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  /* REPORTE INDICADORES */
  public function report_indicadores_ordenes_medico()
  {
    ini_set('xdebug.var_display_max_depth', 5);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $fechaMergeDesde = '01-'.$allInputs['mesDesdeCbo'].'-'.$allInputs['anioDesdeCbo']; 
    $fechaMergeHasta = date('Y-m',strtotime("$fechaMergeDesde+2month")); 
    $allInputs['arrStrMeses'] = get_rangomeses($fechaMergeDesde,$fechaMergeHasta,FALSE);
    $allInputs['arrMeses'] = array();
    foreach ($allInputs['arrStrMeses'] as $key => $value) { 
      $allInputs['arrMeses'][] = date('m',strtotime($value."-01"));
    }
    $allInputs['anioHastaCbo'] = date('Y',strtotime("$fechaMergeHasta"));

    $datosMeta['especialidad']['id'] = $allInputs['especialidad']['id']; 
    $datosMeta['anio'] = $allInputs['anioDesdeCbo'];
    $arrKeyGruposProd = array();
    foreach ($allInputs['productosSeleccionados'] as $key => $row) {
      $arrKeyGruposProd[] = $row['id'];
    }
    $datosMeta['productosInd'] = $arrKeyGruposProd;
    $arrLista['listaidsproductomaster'] = $this->model_estadisticas->m_cargar_productos_de_especialidad_indicador($datosMeta);
    $arrIdProductoMasters = array();
    foreach ($arrLista['listaidsproductomaster'] as $key => $row) {
      $arrIdProductoMasters[] = $row['idproductomaster'];
    }
    $datosValor = array( 
      $allInputs['medico']['idmedico'],
      $arrIdProductoMasters,
      $allInputs['arrMeses'],
      $allInputs['anioDesdeCbo'],
      $allInputs['anioHastaCbo'],
      $allInputs['sedeempresa'],
      $allInputs['medico']['idmedico'],
      $arrIdProductoMasters,
      $allInputs['arrMeses'],
      $allInputs['anioDesdeCbo'],
      $allInputs['anioHastaCbo'],
      $allInputs['sedeempresa']
    );
    $arrLista['meta'] = $this->model_estadisticas->m_cargar_indicadores_meta_orden_medico($datosMeta);
    $arrLista['valor'] = $this->model_estadisticas->m_cargar_indicadores_valor_orden_medico($datosValor); // ORDENES VENDIDAS
    $arrLista['valor_solo_orden'] = $this->model_estadisticas->m_cargar_indicadores_valor_solo_orden_medico($datosValor); // SOLO ORDENES 
    // var_dump($arrLista['valor_solo_orden']); exit();
    // var_dump($arrLista['valor']); exit();
    /* mezclar ambos arrays */
    $arrMerge = array_merge($arrLista['valor'],$arrLista['valor_solo_orden']);
    // agrupar por producto 
    $arrGroupBy = array();
    foreach ($arrMerge as $key => $row) { 
      $arrGroupBy[$row['idproductomaster'].$row['mes'].$row['anio']] = $row;
    }
    /* Reindexar Agrupador */
    $arrGroupBy = array_values($arrGroupBy); 

    //var_dump($arrGroupBy); exit();
    /* Agregar los valores */
    foreach ($arrGroupBy as $keyGB => $rowGB) { 
      $arrGroupBy[$keyGB]['cant_ordenes'] = 0;
      $arrGroupBy[$keyGB]['cant_ordenes_venta'] = 0;
      /* Agregar "solo órdenes" a valores */
      foreach ($arrLista['valor_solo_orden'] as $keyDet1 => $rowDet1) { 
        if( $rowGB['key_indicador'] ==  $rowDet1['key_indicador'] && $rowGB['anio'] ==  $rowDet1['anio'] && $rowGB['mes'] ==  $rowDet1['mes'] ){ 
          $arrGroupBy[$keyGB]['cant_ordenes'] += $rowDet1['cant_ordenes']; 
        }
      }
      /* Agregar "órdenes vendidas" a valores */
      foreach ($arrLista['valor'] as $keyDet2 => $rowDet2) { 
        if( $rowGB['key_indicador'] ==  $rowDet2['key_indicador'] && $rowGB['anio'] ==  $rowDet2['anio'] && $rowGB['mes'] ==  $rowDet2['mes'] ){
          $arrGroupBy[$keyGB]['cant_ordenes_venta'] += $rowDet2['cant_ordenes_venta']; 
        }
      }
    }
    //var_dump($arrGroupBy); exit();
    $arrLista['valor'] = $arrGroupBy;
    /* Agregar key_indicador a valores */
    foreach ($arrLista['valor'] as $key => $row) { 
      foreach ($arrLista['listaidsproductomaster'] as $keyDet => $rowDet) {
        if( $row['idproductomaster'] ==  $rowDet['idproductomaster']){
          $arrLista['valor'][$key]['key_indicador'] = $rowDet['key_indicador'];
        }
      }
    }

    // var_dump($arrLista['valor']); exit();
    $arrLista['group_producto'] = array();
    foreach ( $arrLista['meta'] as $key => $row ) { 
      $arrLista['group_producto'][$row['key_indicador']] = array( 
        'key_indicador'=> $row['key_indicador'],
        'descripcion'=> utf8_decode($row['str_indicador']), 
        'total'=> NULL
      );
    }
    foreach ($arrLista['group_producto'] as $key => $row) { 
      foreach ($arrLista['meta'] as $keyDet => $rowDet) {
        if( $row['key_indicador'] == $rowDet['key_indicador'] ){
          $arrLista['group_producto'][$rowDet['key_indicador']]['total'] += 0; // $rowDet['monto'];
        }
      }
    }
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $arrMeses = array();
    foreach ($allInputs['arrMeses'] as $key => $value){ 
      $arrMeses[] = array(
        'nro_mes'=> (int)$value,
        'mes'=> strtoupper($longMonthArray[(int)$value]),
        'monto_mensual' => NULL,
        'cant_mensual' => NULL
      );
    }
    $arrProductos = array();
    $arrTable[0] = array(
      'producto'=> utf8_decode('PRODUCTO')
    );
    foreach ($arrMeses as $key => $row) { 
      $arrTable[0][$row['mes']] = $row['mes'];
    }
    $valorCeroDefault = 0;
    $nameIndexValue = 'monto';
    $nameIndexCant = 'meta'; 
    // var_dump($arrLista['valor']); exit(); 
    //var_dump($arrLista['group_producto']); exit();
    foreach ($arrLista['group_producto'] as $keyPrinc => $rowPrinc) { 
      $arrTable[$rowPrinc['key_indicador']]['producto'] = strtoupper($rowPrinc['descripcion']); 
      foreach ($arrMeses as $keyMes => $rowMes) { 
        $boolNoData = FALSE;
        foreach ($arrLista['meta'] as $key => $row) { 
          $valorIndicadorSoloOrden = 0;
          $valorIndicador = 0;
          if( $row['num_mes'] == $rowMes['nro_mes'] && $row['key_indicador'] == $rowPrinc['key_indicador'] ) { 
            
            foreach ($arrLista['valor'] as $keyVal => $rowVal) { 
              if( $row['key_indicador'] == $rowVal['key_indicador'] && $row['anio'] == $rowVal['anio'] && (int)$row['num_mes'] == (int)$rowVal['mes'] ){ 
                $valorIndicador = $rowVal['cant_ordenes_venta'];
                $valorIndicadorSoloOrden = $rowVal['cant_ordenes'];
              }
            }
            $strCant = $row[$nameIndexCant]; 
            if( empty($strCant) ){
              $strCant = '-';
            }
            $arrTable[$rowPrinc['key_indicador']][$rowMes['nro_mes']]['valor'] = $valorIndicador; 
            $arrTable[$rowPrinc['key_indicador']][$rowMes['nro_mes']]['valor_orden'] = $valorIndicadorSoloOrden; 
            $arrTable[$rowPrinc['key_indicador']][$rowMes['nro_mes']]['meta'] = $strCant; 
            $boolNoData = TRUE;
          }
        }
        if( !($boolNoData) ){ 
          $arrTable[$rowPrinc['key_indicador']][$rowMes['nro_mes']] = array(
            'valor'=> '0',
            'meta'=> 0
          );
        }
      }
    } 
    $strMeses = '';
    foreach ($arrMeses as $key => $row) { 
      $strMeses .= $row['mes'].' ';
    } 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12); 
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $this->pdf->Cell(0,4, 'PERIODO: [ '.$strMeses.']','','','C'); 
    $this->pdf->Ln(10); 

    $this->pdf->SetFont('Arial','B',12);
    $this->pdf->Cell(60,5,'ESPECIALIDAD');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',11);
    $this->pdf->Cell(0,5,$allInputs['especialidad']['descripcion']);
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',12);
    $this->pdf->Cell(60,5,'PROFESIONAL A EVALUAR');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',11);
    $this->pdf->Cell(0,5,$allInputs['medico']['medico']);
    $this->pdf->Ln();
    $this->pdf->Ln();

    $this->pdf->SetFillColor(224,235,255);
    $fill = FALSE; 
    // var_dump($arrTable); exit(); 
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        // $this->pdf->SetTextColor(0);
        $textAlign = '';
        $widthCell = 50;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key === 0 ){ 
          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          $this->pdf->SetTextColor(0);
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 50;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 12.5;
          // $widthCellMonto2 = 18;
          // $widthCellPorc = 14;
          $porcentajeDif = 0;
          if( !empty($rowValue['valor_orden']) && !empty($rowValue['valor']) ){
            $porcentajeDif = ( $rowValue['valor'] * 100 ) / $rowValue['valor_orden'];
            // var_dump($rowValue['valor_orden'],$rowValue['valor']); exit();
          }
          $this->pdf->setTextColor(176,30,5); 
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['valor_orden'],1,0,$textAlign, $fill); 
          $this->pdf->setTextColor(20,37,153); 
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['valor'],1,0,$textAlign, $fill);
          $this->pdf->setTextColor(0); 
          $this->pdf->Cell($widthCellMonto,$heightCell,round($porcentajeDif).'%',1,0,$textAlign, $fill); 
          $this->pdf->setTextColor(13,172,19); 
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['meta'],1,0,$textAlign, $fill); 
          
          $this->pdf->setTextColor(0); 
        }else{ 
          if( $keyValue === 'producto' ){
            $widthCell = 126;
          } 
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }
    $this->pdf->SetFont('Times','I',10);
    $this->pdf->setTextColor(176,30,5); 
    $this->pdf->Cell(0,10,'* ORDENES ENVIADAS POR EL PROFESIONAL.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(20,37,153); 
    $this->pdf->Cell(0,10,'* ORDENES ENVIADAS POR EL PROFESIONAL - VENDIDAS.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(0); 
    $this->pdf->Cell(0,10,'* (%) PORCENTAJE DE EFECTIVIDAD EN EL TRABAJO.');
    $this->pdf->Ln(5);
    $this->pdf->setTextColor(13,172,19);
    $this->pdf->Cell(0,10,'* METAS.');
    $this->pdf->Ln(5);
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_estadistico_venta_dia_mes()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $this->pdf->SetFont('Arial','B',16); 
    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $longDayArray = array("","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
    $this->pdf->Cell(0,4,'PERIODO: '.$longMonthArray[$allInputs['mes']['id']] . '-' . $allInputs['anioDesdeCbo'],'','','C'); 
    $listaV = $this->model_estadisticas->m_cargar_ventas_por_mes_dia($allInputs); 
    $listaNC = $this->model_estadisticas->m_cargar_nota_credito_por_mes_dia($allInputs); 
    // var_dump("<pre>",$listaV,"<pre>",$listaNC); exit();
    
    // $arrAnos = array();
    $contDesde = (int)$allInputs['anioDesde'];
    //while ( $contDesde <= $allInputs['anioHasta'] ) {
    $arrMeses[] = array(
      'mes'=> $longMonthArray[$allInputs['mes']['id']],
      'monto_mensual' => NULL
    );
    $contDesde++;
    //}
    $arrDias = array();
    $contDesdeDia = 1;
    while ( $contDesdeDia <= cal_days_in_month(CAL_GREGORIAN, $allInputs['mes']['id'], $allInputs['anioDesdeCbo']) ) {
      // CAMBIAR LUEGO EL AÑO PARA QUE SEA AUTOSOSTENIBLE 
      $fechaDeDia = $allInputs['anioDesdeCbo'].'-'.str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.str_pad($contDesdeDia,2,0,STR_PAD_LEFT);
      // $fechaDeDia = '2016-'.str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.str_pad($contDesdeDia,2,0,STR_PAD_LEFT);
      $numDiaSemana = date('N', strtotime("$fechaDeDia")); 
      if($numDiaSemana != 7 ) {
        $arrDias[] = $contDesdeDia;
      }
      $contDesdeDia++;
    }
    // var_dump($arrAnos); exit(); 
    foreach ( $listaV as $key => $row ) { 
      foreach ($listaNC as $keyNC => $rowNC) { 
        if( $row['mes'] == $rowNC['mes'] && $row['dia'] == $rowNC['dia'] ){ 
          $listaV[$key]['total'] = $listaV[$key]['total'] + $rowNC['total'];
        }
      }
    }
    $arrTable[0] = array(
      'dia'=> utf8_decode('DIA/MES')
    );
    // var_dump($arrAnos); exit();
    foreach ($arrMeses as $key => $row) { 
      $arrTable[0][$row['mes']] = $row['mes'];
    }    
    
    // setlocale(LC_TIME, "C");
    // var_dump("<pre>",$arrDias,"<pre>",$listaV); exit();
    foreach ($arrDias as $keyDia => $rowDia) { 
      //
      $fechaDeDia = $allInputs['anioDesdeCbo'].'-'.str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.str_pad($rowDia,2,0,STR_PAD_LEFT); // 
      $numDiaSemana = date('N', strtotime("$fechaDeDia")); 
      //if($numDiaSemana != 7 ){ 
        $nombreDeDia = $longDayArray[$numDiaSemana]; 
        // var_dump($numDiaSemana); exit(); 
        $arrTable[$rowDia]['dia'] = strtoupper($nombreDeDia)." ".str_pad($rowDia,2,0,STR_PAD_LEFT);
        foreach ($arrMeses as $keyMeses => $rowMes) { 
          $boolNoData = FALSE;
          foreach ($listaV as $key => $row) { 
            // var_dump($row['dia'],$rowDia,$row['mes'],$rowMes['mes']); exit();
            if( $row['dia'] == $rowDia && $row['mes'] == $allInputs['mes']['id'] ){ 
              $arrTable[$rowDia][$rowMes['mes']]['monto'] = 'S/. '.number_format($row['total'],2); 
              $arrTable[$rowDia][$rowMes['mes']]['dif_crecimiento'] = '-'; 
              $preKey = $key - 1;
              if( array_key_exists($preKey, $listaV) && $listaV[$preKey]['total'] != 0) { 
                $difCrecimiento = round(($row['total'] - $listaV[$preKey]['total']) / $listaV[$preKey]['total'],4);
                $arrTable[$rowDia][$rowMes['mes']]['dif_crecimiento'] = ($difCrecimiento * 100);
              }
              $boolNoData = TRUE;
              $arrMeses[$keyMeses]['monto_mensual'] += $row['total'];
            }
            if( $row['mes'] == $rowMes['mes'] ){ 

            }
          }
          if( !($boolNoData) ){ 
            $arrTable[$rowDia][$rowMes['mes']] = ' ';
          }
        }
      // } 
    } 
    $arrTable['footer'] = array(
      'dia'=> 'TOTAL'
    ); 
    // var_dump("<pre>",$arrTable); exit();
    foreach ($arrMeses as $key => $row) { 
      $arrTable['footer'][$row['mes']] = 'S/. '.number_format($row['monto_mensual'],2);
    }  
    $this->pdf->Ln(10); 
    $this->pdf->SetFillColor(224,235,255);
    //$this->pdf->SetFillColor(221,233,248);
    $fill = FALSE;
    foreach ($arrTable as $key => $row) { 
      foreach ($row as $keyValue => $rowValue) { 
        $this->pdf->SetTextColor(0);
        $textAlign = 'R';
        $widthCell = 60;
        $heightCell = 8;
        $this->pdf->SetFont('Arial','',12);
        if($key == 0 ){ 

          $textAlign = 'C';
          $this->pdf->SetFont('Arial','B',14); 
          $heightCell = 10;
          // $widthCell = 30;
        } 
        if( !($keyValue == 0) ){ 
          $textAlign = 'C';
          $widthCell = 60;
        }
        if( is_array($rowValue) ){ 
          $widthCellMonto = 38;
          $widthCellPorc = 22;
          $this->pdf->Cell($widthCellMonto,$heightCell,$rowValue['monto'],1,0,$textAlign, $fill);
          //$this->pdf->SetTextColor(225,22,22);
          if( $rowValue['dif_crecimiento'] < 0 ){ 
            $this->pdf->SetTextColor(225,22,22);
          }
          //var_dump('pre',$rowValue['dif_crecimiento']);
          if( $rowValue['dif_crecimiento'] != "-" ){
            $rowValue['dif_crecimiento'] = number_format($rowValue['dif_crecimiento'],2).'%';
          }
          $this->pdf->Cell($widthCellPorc,$heightCell,$rowValue['dif_crecimiento'],1,0,$textAlign, $fill);
        }else{ 
          $textAlign = 'L'; 
          if($key == 0 ){
            $textAlign = 'C'; 
          }
          $this->pdf->Cell($widthCell,$heightCell,$rowValue,1,0,$textAlign, $fill);
        }    
      } 
      $fill = !$fill;
      $this->pdf->Ln();
    }

    // var_dump("<pre>",$listaV); exit();
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  } 
  public function report_resultado_laboratorio()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    $fechaRecepcion = $allInputs['resultado']['fecha_muestra']; 
    $formatFechaRecepcion = date('d/m/Y',strtotime("$fechaRecepcion"));
    $this->pdf->setNumeroHistoria($allInputs['resultado']['idhistoria']);  
    $this->pdf->setNumeroExamen($allInputs['resultado']['orden_lab']);  
    $this->pdf->setSexoPaciente($allInputs['resultado']['sexo']);
    $this->pdf->setEdadPaciente($allInputs['resultado']['edad']); 
    $this->pdf->setFechaRecepcion($formatFechaRecepcion); 
    $this->pdf->setPaciente(utf8_decode($allInputs['resultado']['paciente'])); 
 
    
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    // $this->pdf->SetFont('Courier','',12);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages(); 
    
    /* CABECERA $this->pdf->SetMargins(8,8,7); SetFillColor */ 
    /* END */
    
    $this->pdf->Ln();
    $this->pdf->SetFont('Arial','B',9); 
    // var_dump($allInputs['resultado']); exit();
    if (!empty($allInputs['resultado']['arrSecciones'])){
      foreach ($allInputs['resultado']['arrSecciones'] as $seccion) { 
        $this->pdf->SetFont('Arial','B',9);
        if( $seccion['seleccionado'] ){
          $this->pdf->Ln(4);
          $this->pdf->Cell(0,7,$seccion['seccion'],1,0,'C'); 
          $this->pdf->Ln(10);
        //var_dump($seccion['seleccionado']); exit(); 
        
          $this->pdf->SetFont('Arial','',8);
          $analisisRepetido = FALSE;
          foreach ($seccion['analisis'] as $key => $analisis) {
            if($analisis['seleccionado']){ 
              if( $analisis['descripcion_anal'] ==  $analisis['parametros'][0]['parametro']){ 
                $analisisRepetido = TRUE;
              }else{
                $this->pdf->SetFont('Arial','B',8);
                $this->pdf->Cell(0,6,$analisis['descripcion_anal'],0,0,'L'); 
                $this->pdf->Ln();
                $this->pdf->SetFont('Arial','',7);
                $analisisRepetido = FALSE;
              }
              
              foreach ($analisis['parametros'] as $keyParam => $parametro) { 
                if(@trim($parametro['resultado']) == '--Seleccione Opcion--'){ 
                  @$parametro['resultado'] = '';
                }
                $arrBolds = FALSE;
                if($analisisRepetido){
                  $arrBolds = array('B','','','');
                  $this->pdf->Ln(2);
                }else{
                  $parametro['parametro'] = '  '.$parametro['parametro'];
                }
                if( $parametro['separador'] == 1 ){ 
                  $arrBolds = array('B','','',''); 
                  //$analisis['metodo'] = 'ASD';
                }else{
                  
                }
                $breaks = array("<br />","<br>","<br/>");  
                $parametroFormat = trim(str_ireplace($breaks, "\r\n", @$parametro['valor_normal']));
                $this->pdf->Row( 
                  array(
                    utf8_decode($parametro['parametro']),
                    @utf8_decode($parametro['resultado']),
                    @utf8_decode($parametroFormat),
                    strtoupper($analisis['metodo'])
                  ),
                  FALSE,
                  0,
                  $arrBolds,
                  3
                );
                $this->pdf->Ln(1);
                if( !empty($parametro['subparametros']) ){ 
                  if( $parametro['idparametro'] == 57 ){
                    $arrGroupByRes = array();
                    foreach ($parametro['subparametros'] as $keySP => $rowSP) { 

                      if(  trim($rowSP['resultado']) != '--Seleccione Opcion--' ){
                        $arrGroupByRes[$rowSP['resultado']] = array(
                          // 'resultado'=> $rowSP['resultado'],
                          'detalle'=> array() 
                        );
                      }
                    }
                    foreach ($parametro['subparametros'] as $keySP => $rowSP) { 
                      if(  trim($rowSP['resultado']) != '--Seleccione Opcion--' ){
                        $arrGroupByRes[$rowSP['resultado']]['detalle'][] = array( 
                          'parametro' => $rowSP['subparametro']
                        );
                      }
                    }
                    $arrFormatGB = array();
                    foreach ($arrGroupByRes as $key => $rowRes) { 
                      $arrFormatGB[0][$key] = $key;
                    }
                    foreach ($arrGroupByRes as $key => $rowRes) {
                      foreach ($rowRes['detalle'] as $keyDet => $rowDet) { 
                        $arrFormatGB[$keyDet+1][$key] = $rowDet['parametro'];
                      }
                    }
                    foreach ($arrFormatGB as $key => $row) { 
                      $this->pdf->SetFont('Arial','',7);
                      
                      if( $key === 0 ){
                        $this->pdf->SetFont('Arial','B',8); 
                        @$row['INTERMEDIO'] = '    '.@$row['INTERMEDIO']; 
                      }else{
                        @$row['INTERMEDIO'] = '      '.@$row['INTERMEDIO'];
                        @$row['RESISTENTE'] = '  '.@$row['RESISTENTE'];
                        @$row['SENSIBLE'] = '  '.@$row['SENSIBLE'];
                      }
                      $this->pdf->Cell(70,4,@$row['INTERMEDIO'],0,0,'TB'); 
                      $this->pdf->Cell(70,4,@$row['SENSIBLE'],0,0,'TB'); 
                      $this->pdf->Cell(70,4,@$row['RESISTENTE'],0,0,'TB'); 
                      $this->pdf->Ln(); 
                    } 
                    // var_dump($arrFormatGB); exit(); 
                  }else{ 
                    foreach ($parametro['subparametros'] as $keySubParam => $subparametro) { 
                      if( trim($subparametro['resultado']) !== '--Seleccione Opcion--' ){ 
                        $breaks = array("<br />","<br>","<br/>");  
                        $subParametroFormat = trim(str_ireplace($breaks, "\r\n", $subparametro['valor_normal']));
                        $this->pdf->Row( 
                          array(
                            '    '.utf8_decode($subparametro['subparametro']),
                            @utf8_decode($subparametro['resultado']),
                            utf8_decode($subParametroFormat),
                            strtoupper($analisis['metodo'])
                          ),
                          FALSE,
                          0,
                          FALSE,
                          3
                        ); 
                        $this->pdf->Ln(0); 
                        // $htmlData .= '<tr> <td  class="col_examen" style="padding-left: 40px;">- '. $subparametro['subparametro'] .' </td> <td class="col_resultado"> ' . @$subparametro['resultado'] .' </td> <td class="col_valor_normal">'. @$subparametro['valor_normal'] .' </td> <td class="col_metodo">  '. $analisis['metodo'] .' </td></tr>';
                      }
                    }
                  }
                }

              }
            }
          }
        }
        
      }
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/LAB_tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/LAB_tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function historias_clinicas_anteriores(){
    $this->load->model('model_atencion_medica_anterior');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();

    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();

    // APARTADO: DATOS DEL PACIENTE
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(0,7,'DATOS DEL PACIENTE',1,0,'C');
    $this->pdf->Ln(8);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(90,6,'Apellidos y Nombre');
    $this->pdf->Cell(40,6,'Num Documento');
    $this->pdf->Cell(30,6,'Sexo:');
    $this->pdf->Cell(30,6,utf8_decode('Historia Nº'));
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(90,6,$allInputs['resultado']['cliente']);
    $this->pdf->Cell(40,6,$allInputs['resultado']['numero_documento']);
    $this->pdf->Cell(30,6,$allInputs['resultado']['sexo']);
    $this->pdf->Cell(30,6,$allInputs['resultado']['idhistoria']);
    $this->pdf->Ln(10);
    // APARTADO: ACTO MEDICO
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(0,7,'ACTO MEDICO',1,0,'C');
    $this->pdf->Ln(8);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(50,6,utf8_decode('Nº Acto Médico'));
    $this->pdf->Cell(40,6,utf8_decode('Nº Orden'));
    $this->pdf->Cell(40,6,'Especialidad:');
    $this->pdf->Cell(60,6,utf8_decode('Profesional'));
    
    
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(50,6,$allInputs['resultado']['num_acto_medico']);
    $this->pdf->Cell(40,6,$allInputs['resultado']['orden']);
    $this->pdf->Cell(40,6,$allInputs['resultado']['especialidad']);
    $this->pdf->Cell(60,6,utf8_decode($allInputs['resultado']['personalatencion']['descripcion']));
    

    $this->pdf->Ln(10);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(50,6,utf8_decode('Fecha de Atención'));
    $this->pdf->Cell(40,6,utf8_decode('Edad en la Atención'));
    $this->pdf->Cell(40,6,'Area Hospitalaria');
    $this->pdf->Cell(60,6,utf8_decode('Actividad Específica'));
   

    $this->pdf->Ln(4);
    
    
    $this->pdf->SetFont('Arial','',8);
    
    $this->pdf->Cell(50,6,$allInputs['resultado']['fechaAtencion']);
    $this->pdf->Cell(40,6,utf8_decode($allInputs['resultado']['edadEnAtencion']));
    $this->pdf->Cell(40,6,$allInputs['resultado']['area_hospitalaria']);
    // $this->pdf->Cell(60,6,utf8_decode($allInputs['resultado']['producto']));
    $this->pdf->SetWidths(array(60));
    $this->pdf->Row( 
      array(
        utf8_decode($allInputs['resultado']['producto'])
      )
    );
    $this->pdf->Ln(10);

    // CONSULTA MEDICA
    if($allInputs['resultado']['idtipoproducto'] == 12){
      if($allInputs['resultado']['boolSexo'] == 'F'){
        if( $allInputs['resultado']['gestando'] == 1 ) $allInputs['resultado']['gestando'] = 'Si';
        else $allInputs['resultado']['gestando'] = 'No';
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Gestando?'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(50,6,$allInputs['resultado']['gestando']);
        $this->pdf->Ln(10);
      }
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(0,6,utf8_decode('ANAMNESIS'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['anamnesis']));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(0,6,utf8_decode('SIGNOS VITALES'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(47,6,utf8_decode('Presión Arterial (Mm/Hg)'));
      $this->pdf->Cell(48,6,utf8_decode('Frec. Cardiaca (Lat. x Min.)'));
      $this->pdf->Cell(47,6,utf8_decode('Temperatura Corporal (ºC)'));
      $this->pdf->Cell(48,6,utf8_decode('Frec. Respiratoria (x Min)'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(47,6,$allInputs['resultado']['presion_arterial_mm'].'/'.$allInputs['resultado']['presion_arterial_hg']);
      $this->pdf->Cell(48,6,$allInputs['resultado']['frecuencia_cardiaca_lxm']);
      $this->pdf->Cell(47,6,$allInputs['resultado']['temperatura_corporal']);
      $this->pdf->Cell(48,6,$allInputs['resultado']['frecuencia_respiratoria']);
      $this->pdf->Ln(10);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(0,6,utf8_decode('ANTROPOMETRÍA'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(47,6,utf8_decode('Peso (Kg)'));
      $this->pdf->Cell(48,6,utf8_decode('Talla (m)'));
      $this->pdf->Cell(47,6,utf8_decode('IMC (%)'));
      $this->pdf->Cell(48,6,utf8_decode('Perímetro Abdo (cm)'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(47,6,$allInputs['resultado']['peso']);
      $this->pdf->Cell(48,6,$allInputs['resultado']['talla']);
      $this->pdf->Cell(47,6,$allInputs['resultado']['imc']);
      $this->pdf->Cell(48,6,$allInputs['resultado']['perimetro_abdominal']);
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Examen Clínico'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['examen_clinico']));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(0,6,utf8_decode('PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['observaciones']));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(0,6,utf8_decode('DIAGNÓSTICOS'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(47,6,utf8_decode('CÓDIGO'));
      $this->pdf->Cell(95,6,utf8_decode('DESCRIPCIÓN'));
      $this->pdf->Cell(48,6,utf8_decode('TIPO'));
      $this->pdf->Ln(4);
      $diagnosticos = $this->model_atencion_medica_anterior->m_cargar_diagnosticos_de_atencion($allInputs['resultado']['diagnosticos']);
      $this->pdf->SetFont('Arial','',8);
      foreach ($diagnosticos as $key => $value) {
        $this->pdf->SetWidths(array(25, 117, 48));
        $this->pdf->Row( 
          array(
            strtoupper($value['codigo_cie']),
            strtoupper(iconv("windows-1252", "utf-8", $value['descripcion_cie'])),
            'PRESUNTIVO'
          )
        );

        // $this->pdf->Cell( 47,6,strtoupper($value['codigo_cie']),1 );
        // $this->pdf->Cell( 95,6,strtoupper(iconv("windows-1252", "utf-8", $value['descripcion_cie'])),1 );
        // $this->pdf->Cell( 48,6,'PRESUNTIVO',1 );
        // $this->pdf->Ln(6);  
      }
    }
    // EXAMEN AUXILIAR
    elseif($allInputs['resultado']['idtipoproducto'] == 11 || $allInputs['resultado']['idtipoproducto'] == 14 || $allInputs['resultado']['idtipoproducto'] == 15){
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'EXAMEN AUXILIAR',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['ex_informe']));
      $this->pdf->Ln(4);
    }
    // PROCEDIMIENTO
    elseif($allInputs['resultado']['idtipoproducto'] == 16){
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,utf8_decode('PROCEDIMIENTO CLÍNICO'),1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Observaciones'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['proc_observacion']));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,utf8_decode($allInputs['resultado']['proc_informe']));
      $this->pdf->Ln(4);
    }
    $this->pdf->Ln(30);
    $this->pdf->SetFont('Arial','',11);
    $this->pdf->Cell(100,6,'');
    $this->pdf->Cell(90,6,utf8_decode($allInputs['resultado']['personalatencion']['descripcion']),0,0,'C');
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(100,6,'');
    $this->pdf->Cell(90,6,utf8_decode('Sello y firma'),0,0,'C');
    $this->pdf->Ln(8);
    $this->pdf->Cell(0,6,utf8_decode('COMPROMETIDOS CON TU SALUD'),0,0,'C');
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ficha_atencion()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    // var_dump($allInputs); exit();
    // $allInputs['id']
    $idsedeempresaadmin = $this->sessionHospital['idsedeempresaadmin'];
    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($idsedeempresaadmin);
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = FALSE;

    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
    //$this->pdf->SetFont('Arial','',12);
    // $this->pdf->AddPage('P','A4');
    // $this->pdf->AliasNbPages();
    $arrNumActoMedico = array();
    if( !empty($allInputs['filas']) ){ 
      foreach ($allInputs['filas'] as $key => $row) {
        $arrNumActoMedico[] = $row['num_acto_medico'];
      }
    } 
    //$arrNumActoMedico = array($allInputs['num_acto_medico']);
    $listaAtenciones = $this->model_atencion_medica->m_cargar_esta_atencion_medica($arrNumActoMedico);
    
    foreach ($listaAtenciones as $key => $fAtencion) {
      if($fAtencion['edad'] > '1' ){
        $edadEnAtencion = $fAtencion['edad'] . ' AÑOS';
      }elseif($fAtencion['edad'] == '1' ){
        $edadEnAtencion = $fAtencion['edad'] . ' AÑO';
      }else{
        $edadEnAtencion = strtoupper_total(devolverEdadAtencion($fAtencion['fecha_nacimiento'],$fAtencion['fecha_atencion']));
      }
      $this->pdf->AddPage('P','A4');
      $this->pdf->AliasNbPages();

      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'DATOS DEL PACIENTE',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(90,6,'Nombres y Apellidos');
      $this->pdf->Cell(40,6,'Num Documento');
      $this->pdf->Cell(30,6,'Sexo:');
      $this->pdf->Cell(30,6,utf8_decode('Historia Nº'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(90,6,utf8_decode($fAtencion['cliente']));
      $this->pdf->Cell(40,6,$fAtencion['num_documento']);
      $this->pdf->Cell(30,6,$fAtencion['sexo']);
      $this->pdf->Cell(30,6,$fAtencion['idhistoria']);
      $this->pdf->Ln(10);
      // APARTADO: ACTO MEDICO
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'ACTO MEDICO',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Nº Acto Médico'));
      $this->pdf->Cell(40,6,utf8_decode('Nº Orden'));
      $this->pdf->Cell(40,6,'Especialidad:');
      $this->pdf->Cell(60,6,utf8_decode('Profesional'));
      
      
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(50,6,$fAtencion['idatencionmedica']);
      $this->pdf->Cell(40,6,$fAtencion['orden_venta']);
      $this->pdf->Cell(40,6,utf8_decode($fAtencion['especialidad']));
      $this->pdf->Cell(60,6,utf8_decode($fAtencion['medicoatencion']));
      

      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Fecha de Atención'));
      $this->pdf->Cell(40,6,utf8_decode('Edad en la Atención'));
      $this->pdf->Cell(40,6,'Area Hospitalaria');
      $this->pdf->Cell(60,6,utf8_decode('Actividad Específica'));
     

      $this->pdf->Ln(4);
      
      
      $this->pdf->SetFont('Arial','',8);
      
      $this->pdf->Cell(50,6,formatoConDiaHora($fAtencion['fecha_atencion']));
      $this->pdf->Cell(40,6,utf8_decode($edadEnAtencion));
      $this->pdf->Cell(40,6,utf8_decode($fAtencion['descripcion_aho']));
      // $this->pdf->Cell(60,6,utf8_decode($fAtencion['producto']));
      $this->pdf->SetWidths(array(60));
      $this->pdf->Row( 
        array(
          utf8_decode($fAtencion['producto'])
        )
      );
      // $this->pdf->Ln(4);

      // CONSULTA MEDICA
      if($fAtencion['idtipoproducto'] == 12){
        if(strtoupper($fAtencion['sexo']) == 'F'){
          if( $fAtencion['gestando'] == 1 ) $fAtencion['gestando'] = 'SI';
          else $fAtencion['gestando'] = 'NO';
          $this->pdf->SetFont('Arial','B',9);
          $this->pdf->Cell(50,6,utf8_decode('Gestando?'));
          $this->pdf->Ln(4);
          $this->pdf->SetFont('Arial','',8);
          $this->pdf->Cell(50,6,$fAtencion['gestando']);
          $this->pdf->Ln(10);
        }else{
          $this->pdf->Ln(4); // para q no salga pegado al apartado ANAMNESIS, el Row del producto ya le coloca Ln(6)
        }
        
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(0,6,utf8_decode('ANAMNESIS'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,utf8_decode($fAtencion['anamnesis']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(0,6,utf8_decode('SIGNOS VITALES'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(47,6,utf8_decode('Presión Arterial (Mm/Hg)'));
        $this->pdf->Cell(48,6,utf8_decode('Frec. Cardiaca (Lat. x Min.)'));
        $this->pdf->Cell(47,6,utf8_decode('Temperatura Corporal (ºC)'));
        $this->pdf->Cell(48,6,utf8_decode('Frec. Respiratoria (x Min)'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(47,6,$fAtencion['presion_arterial_mm'].'/'.$fAtencion['presion_arterial_hg']);
        $this->pdf->Cell(48,6,$fAtencion['frec_cardiaca']);
        $this->pdf->Cell(47,6,$fAtencion['temperatura_corporal']);
        $this->pdf->Cell(48,6,$fAtencion['frec_respiratoria']);
        $this->pdf->Ln(10);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(0,6,utf8_decode('ANTROPOMETRÍA'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(47,6,utf8_decode('Peso (Kg)'));
        $this->pdf->Cell(48,6,utf8_decode('Talla (m)'));
        $this->pdf->Cell(47,6,utf8_decode('IMC (%)'));
        $this->pdf->Cell(48,6,utf8_decode('Perímetro Abdo (cm)'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(47,6,$fAtencion['peso']);
        $this->pdf->Cell(48,6,$fAtencion['talla']);
        $this->pdf->Cell(47,6,$fAtencion['imc']);
        $this->pdf->Cell(48,6,$fAtencion['perimetro_abdominal']);
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Examen Clínico'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,utf8_decode($fAtencion['examen_clinico']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(0,6,utf8_decode('PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,utf8_decode($fAtencion['observaciones']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(0,6,utf8_decode('DIAGNÓSTICOS'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(47,6,utf8_decode('CÓDIGO'));
        $this->pdf->Cell(95,6,utf8_decode('DESCRIPCIÓN'));
        $this->pdf->Cell(48,6,utf8_decode('TIPO'));
        $this->pdf->Ln(6);
        $diagnosticos = $this->model_atencion_medica->m_cargar_diagnosticos_de_atencion($fAtencion);
        $this->pdf->SetFont('Arial','',8);
        foreach ($diagnosticos as $key => $value) {
          $this->pdf->SetWidths(array(25, 117, 48));
          $this->pdf->Row( 
            array(
              strtoupper($value['codigo_cie']),
              strtoupper(iconv("windows-1252", "utf-8", $value['descripcion_cie'])),
              $value['tipo_diagnostico']
            )
          );
        }

        $this->pdf->Ln(4);
        $recetas = $this->model_atencion_medica->m_cargar_recetas_de_atencion($fAtencion);
        if( !empty($recetas) ){
          $this->pdf->SetFont('Arial','B',9);
          $this->pdf->Cell(0,6,utf8_decode('RECETA MÉDICA'),1,0,'C');
          $this->pdf->Ln(8);
          $this->pdf->SetFont('Arial','B',9);
          $this->pdf->Cell(80,6,utf8_decode('MEDICAMENTO'),0,0);
          $this->pdf->Cell(25,6,utf8_decode('CANTIDAD'),0,0);
          $this->pdf->Cell(117,6,strip_tags(utf8_decode('INDICACIONES')),0,0);
          $this->pdf->Ln(6);
          
          $this->pdf->SetFont('Arial','',8);
          foreach ($recetas as $key => $value) { 
            $this->pdf->SetWidths(array(80, 25, 117));
            $this->pdf->Row( 
              array(
                strtoupper($value['denominacion']),
                $value['cantidad'],
                nl2br($value['indicaciones'])
              )
            );
          }
        }
        
      }
      // EXAMEN AUXILIAR
      elseif($fAtencion['idtipoproducto'] == 11 || $fAtencion['idtipoproducto'] == 14 || $fAtencion['idtipoproducto'] == 15){
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->Cell(0,7,'EXAMEN AUXILIAR',1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['ex_informe'])));
        $this->pdf->Ln(4);
      }
      // PROCEDIMIENTO
      elseif($fAtencion['idtipoproducto'] == 16){
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->Cell(0,7,utf8_decode('PROCEDIMIENTO CLÍNICO'),1,0,'C');
        $this->pdf->Ln(8);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Observaciones'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['proc_observacion'])));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['proc_informe'])));
        $this->pdf->Ln(4);
      }
      // DOCUMENTOS
      elseif($fAtencion['idtipoproducto'] == 13){
        // $this->pdf->SetFont('Arial','B',10);
        // $this->pdf->Cell(0,7,utf8_decode('PROCEDIMIENTO CLÍNICO'),1,0,'C');
        // $this->pdf->Ln(8);
        // $this->pdf->SetFont('Arial','B',9);
        // $this->pdf->Cell(50,6,utf8_decode('Observaciones'));
        // $this->pdf->Ln(4);
        // $this->pdf->SetFont('Arial','',8);
        // $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['proc_observacion'])));
        // $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['doc_informe'])));
        $this->pdf->Ln(4);
      }
      $this->pdf->Ln(30);
      $this->pdf->SetFont('Arial','',11);
      $this->pdf->Cell(100,6,'');
      $this->pdf->Cell(90,6,utf8_decode($fAtencion['medicoatencion']),0,0,'C');
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(100,6,'');
      $this->pdf->Cell(90,6,utf8_decode('Sello y firma'),0,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->Cell(0,6,utf8_decode('COMPROMETIDOS CON TU SALUD'),0,0,'C');
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ficha_atencion_salud_ocup()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    // var_dump($allInputs); exit(); 
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    // $this->pdf->AddPage('P','A4');
    // $this->pdf->AliasNbPages();
    $arrIds = array();
    if( !empty($allInputs['filas']) ){ 
      foreach ($allInputs['filas'] as $key => $row) {
        $arrIds['arrIds'][] = $row['idatencionocupacional'];
      }
    } 
    //var_dump($arrIds); exit();
    //$arrIds = array($allInputs['num_acto_medico']);
    $listaAtenciones = $this->model_atencion_salud_ocup->m_cargar_estas_atencion_salud_ocupacional($arrIds); 
    
    foreach ($listaAtenciones as $key => $fAtencion) { 
      $this->pdf->AddPage('P','A4');
      $this->pdf->AliasNbPages();

      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'DATOS DEL PACIENTE',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(90,6,'Apellidos y Nombre');
      $this->pdf->Cell(40,6,'Num Documento');
      $this->pdf->Cell(30,6,'Sexo:');
      $this->pdf->Cell(30,6,utf8_decode('Historia Nº'));
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(90,6,utf8_decode($fAtencion['cliente']));
      $this->pdf->Cell(40,6,$fAtencion['num_documento']);
      $this->pdf->Cell(30,6,$fAtencion['sexo']);
      $this->pdf->Cell(30,6,$fAtencion['idhistoria']);
      $this->pdf->Ln(10);
      // APARTADO: ACTO MEDICO
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'ACTO MEDICO',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Nº Acto Médico'));
      $this->pdf->Cell(40,6,utf8_decode('Nº Orden'));
      $this->pdf->Cell(40,6,'Especialidad:');
      $this->pdf->Cell(60,6,utf8_decode('Profesional Responsable'));
      
      
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(50,6,$fAtencion['idatencionocupacional']);
      $this->pdf->Cell(40,6,$fAtencion['orden_venta']);
      $this->pdf->Cell(40,6,utf8_decode($fAtencion['especialidad']));
      $this->pdf->Cell(60,6,utf8_decode($fAtencion['medico_responsable']));
      

      $this->pdf->Ln(10);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Fecha de Atención'));
      $this->pdf->Cell(40,6,utf8_decode('Edad en la Atención'));
      $this->pdf->Cell(40,6,'Area Hospitalaria');
      $this->pdf->Cell(60,6,utf8_decode('Perfil'));
     

      $this->pdf->Ln(4);
      
      
      $this->pdf->SetFont('Arial','',8);
      
      $this->pdf->Cell(50,6,$fAtencion['fecha_atencion']);
      $this->pdf->Cell(40,6,utf8_decode(devolverEdadAtencion($fAtencion['fecha_nacimiento'],$fAtencion['fecha_atencion'])));
      $this->pdf->Cell(40,6,utf8_decode('CONSULTA EXTERNA'));
      // $this->pdf->Cell(60,6,utf8_decode($fAtencion['producto'])); CONSULTA EXTERNA
      $this->pdf->SetWidths(array(60));
      $this->pdf->Row( 
        array(
          utf8_decode($fAtencion['producto'])
        )
      );
      $this->pdf->Ln(10);
      // SALUD OCUPACIONAL
      $strArchivoAdjuntoBool = 'SI';
      if(empty($fAtencion['nombre_archivo'])){
        $strArchivoAdjuntoBool = 'NO';
      }
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(0,7,'INFORME',1,0,'C');
      $this->pdf->Ln(8);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Informe / Resultado'));
      
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,strip_tags(utf8_decode($fAtencion['informe'])));
      
      $this->pdf->Ln(2);
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(50,6,utf8_decode('Archivo Adjunto')); // mostrar_plantilla_pdf
      $this->pdf->Ln(4);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(0,6,$strArchivoAdjuntoBool);
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_orden_compra($arrParams=FALSE){ 
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $allInputs['idmovimiento'] = $allInputs['resultado'];
    // var_dump($allInputs); exit(); 
    // RECUPERACIÓN DE DATOS
    $orden = $this->model_orden_compra->m_cargar_orden_compra_por_id($allInputs['idmovimiento']);
    // var_dump($orden); exit(); 
    // CONFIGURACION DEL PDF
    $this->pdf = new Fpdfext();
    $this->pdf->setNombreEmpresaFarm($orden['nombreEmpresaFarm']);
    $this->pdf->setRucEmpresaFarm($orden['rucEmpresaFarm']);
    $this->pdf->setIdEmpresaFarm($orden['idempresaadmin']); // AQUI ME QUEDÉ 
    $arrConfig = array( 
      'razon_social' => $orden['nombreEmpresaFarm'],
      'domicilio_fiscal' => $orden['domicilio_fiscal'],
      'nombre_logo' => $orden['nombre_logo'],
      'mode_report' => 'F',
      'estado' => $allInputs['estado']
    );
    
    // SETEO DE DATOS DEL USUARIO 
    $orden['usuario'] = $orden['nombres'] . ' ' .$orden['apellido_paterno'] . ' ' .$orden['apellido_materno'];

    switch ($orden['forma_pago']) { 
      case '1': $forma_pago = 'AL CONTADO';
        break;
      case '2': $orden['letras'] == NULL? $forma_pago = 'CREDITO' : $forma_pago = 'CREDITO EN ' . $orden['letras'] . ' DÍAS';
        break;
      case '3': $forma_pago = 'EN '. $orden['letras'] . ' LETRAS';
        break;
      default: $forma_pago = '';
        break;
    }
    switch ($orden['moneda']) { 
      case '1':
        $moneda = 'SOLES';
        $simbolo = 'S/. ';
        break;
      case '2':
        $moneda = 'DÓLARES';
        $simbolo = 'US$ ';
        break;
      default:
        $moneda = '';
        $simbolo = '';
        break;
    }

    /* P.O.O. */ 
    $this->pdf->setRazonSocialOC(utf8_decode($orden['razon_social']));
    $this->pdf->setRucOC($orden['ruc']);
    $this->pdf->setNombreComercialOC(utf8_decode($orden['nombre_comercial']));
    $this->pdf->setDireccionFiscalOC(utf8_decode($orden['direccion_fiscal']));
    $this->pdf->setTelefonoOC($orden['telefono']);
    $this->pdf->setFaxOC($orden['fax']);
    $this->pdf->setFormaPagoOC(utf8_decode($forma_pago));
    $this->pdf->setMonedaOC(utf8_decode($moneda));
    $this->pdf->setOrdenCompraOC($orden['orden_compra']);
    $this->pdf->setFechaMovimientoOC(formatoFechaReporte3($orden['fecha_movimiento']));
    $this->pdf->setFechaEmisionCorreoOC(formatoFechaReporte3($orden['fecha_emision_correo']));
    $this->pdf->setFechaEntregaOC(formatoFechaReporte3($orden['fecha_entrega']));
    $this->pdf->setNombreAlmOC(utf8_decode($orden['nombre_alm']));
    $this->pdf->setUsuarioRespOC(utf8_decode($orden['usuario']));
    $this->pdf->setAlmacenDirStr($orden['direccion_anexo']);


    $fValidateLOG = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],2,'APROBADO');
    $fValidateFAR = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],4,'APROBADO');
    $fValidateAF = $this->model_orden_compra->m_comprobar_estado_oc($allInputs['idmovimiento'],6,'APROBADO');
    $this->pdf->setFirmaLogistica(NULL);
    if( !empty($fValidateLOG['firma_del_area']) ){ 
      $this->pdf->setFirmaLogistica('assets/img/dinamic/firmaEmpleado/'.$fValidateLOG['firma_del_area']);
    }
    $this->pdf->setFirmaFarmacia(NULL);
    if( !empty($fValidateFAR['firma_del_area']) ){ 
      $this->pdf->setFirmaFarmacia('assets/img/dinamic/firmaEmpleado/'.$fValidateFAR['firma_del_area']);
    }
    $this->pdf->setFirmaFinanzas(NULL);
    if( !empty($fValidateAF['firma_del_area']) ){ 
      $this->pdf->setFirmaFinanzas('assets/img/dinamic/firmaEmpleado/'.$fValidateAF['firma_del_area']);
    }
    
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$arrConfig);

    $this->pdf->AddPage('P','A4');//var_dump($allInputs['tituloAbv']); exit();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(true,65);

    // APARTADO: DATOS DEL DETALLE
    $i = 1;
    $detalle = $this->model_orden_compra->m_cargar_detalle_entrada($allInputs);
    //var_dump($detalle); exit();
    $exonerado = 0;
    $fill = TRUE;
    $this->pdf->SetDrawColor(204,204,204); // gris fill
    $this->pdf->SetLineWidth(.2);

    foreach ($detalle as $key => $value) {
      // if( $value['caja_unidad'] == 'UNIDAD' &&  $value['acepta_caja_unidad'] == '1'){
      //   $value['cantidad'] = $value['cantidad'] . ' (F)';
      // }
      $igv = 0;
      if($value['excluye_igv'] == 2){
        $importe_sin = round( floatval($value['total_detalle'])/1.18, 2 );
        $igv = round( floatval($value['total_detalle'])*0.18/1.18, 2 );
        $inafecto = ' ';
      }else{
        $exonerado += round( floatval($value['total_detalle'])*0.18, 2 );
        $importe_sin = $value['total_detalle'];
        $inafecto = 'X';
      }
      if( strlen($value['nombre_lab']) >= 17){
        $laboratorio = substr($value['nombre_lab'], 0,17) . '...';
      }else{
        $laboratorio = $value['nombre_lab'];
      }
      $fill = !$fill;
      $this->pdf->SetWidths(array(8, 53, 27, 15, 10, 12, 12, 16, 13, 16, 8));
      $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C', 'R', 'R', 'R','R','R','C'));
      //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
      $this->pdf->SetFillColor(230, 240, 250);
      $this->pdf->SetFont('Arial','',6);
      $this->pdf->RowSmall( 
        array(
          $i,
          utf8_decode($value['medicamento']),
          utf8_decode($laboratorio),
          utf8_decode($value['caja_unidad']),
          $value['cantidad'],
          $value['precio_unitario'],
          $value['descuento_asignado'],
          $importe_sin,
          $igv,
          $value['total_detalle'],
          $inafecto
        ),
        $fill,1
      );
      $i++;
    }
    $this->pdf->Ln(1);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(140,5,'Observaciones');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);

    $this->pdf->SetWidths(array(138));
    $this->pdf->TextArea(array(empty($orden['motivo_movimiento'])? '':$orden['motivo_movimiento']),0,0,FALSE,5,20);

    $this->pdf->Cell(2,20,'');
    $this->pdf->Cell(20,6,'SUBTOTAL:','LT',0,'R');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6,$orden['sub_total'],'TR',0,'R');
    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(140,6,'');
    $this->pdf->Cell(20,6,'IGV:','L',0,'R');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6,$orden['total_igv'],'R',0,'R');
    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(140,8,'');
    $this->pdf->Cell(20,8,'TOTAL:','TLB',0,'R');
    $this->pdf->Cell(30,8,$simbolo . $orden['total_a_pagar'],'TRB',0,'R');
    // $this->pdf->Cell(30,8,$simbolo . substr($orden['total_a_pagar'], 4),'TRB',0,'R');
    $this->pdf->Ln(15);
    // $monto = new EnLetras();
    $en_letra = ValorEnLetras($orden['total_a_pagar'],$moneda);
    $this->pdf->Cell(0,8,'TOTAL SON: ' . $en_letra ,'',0);

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_compra(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();

    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['empresa']['idsedeempresaadmin']);
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = 'F';
    $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'], $empresaAdmin);

    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();
    // RECUPERACION DE DATOS
    $this->load->model('model_entrada_farmacia');
    $orden = $this->model_entrada_farmacia->m_cargar_entrada_por_id($allInputs['resultado']);

    // var_dump($orden); exit();
    // SETEO DE DATOS DEL USUARIO
    $orden['usuario'] = $orden['nombres'] . ' ' .$orden['apellido_paterno'] . ' ' .$orden['apellido_materno'];

    switch ($orden['forma_pago']) {
      case '1': $forma_pago = 'AL CONTADO';
        break;
      case '2': $forma_pago = 'CREDITO EN ' . $orden['letras'] . ' DÍAS';
        break;
      case '3': $forma_pago = 'EN '. $orden['letras'] . ' LETRAS';
        break;
      default: $forma_pago = '';
        break;
    }
    switch ($orden['moneda']) {
      case '1':
        $moneda = 'SOLES';
        $simbolo = 'S/. ';
        break;
      case '2':
        $moneda = 'DÓLARES';
        $simbolo = 'US$ ';
        break;
      default:
        $moneda = '';
        $simbolo = '';
        break;
    }
    switch ($orden['tipo_movimiento']) {
      case '1': $tipo_movimiento = 'VENTA';
        break;
      case '2': $tipo_movimiento = 'COMPRA';
        break;
      case '3': $tipo_movimiento = 'TRASLADO';
        break;
      case '4': $tipo_movimiento = 'REGALO';
        break;
      case '5': $tipo_movimiento = 'BAJA';
        break;
      case '6': $tipo_movimiento = 'REINGRESO';
        break;
      case '7': $tipo_movimiento = 'ORDEN DE COMPRA';
        break;
      default: $tipo_movimiento = '';
        break;
    }
    // APARTADO: DATOS DEL PROVEEDOR
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6,utf8_decode('Proveedor'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->MultiCell(75,4,utf8_decode($orden['razon_social']));
    //$this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6,'RUC');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,$orden['ruc']);
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Dirección'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->MultiCell(75,4,utf8_decode($orden['direccion_fiscal']));
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Teléfono'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,$orden['telefono']);
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Tipo Ingreso'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,$tipo_movimiento);
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Forma de Pago'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,utf8_decode($forma_pago));
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Moneda'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,utf8_decode($moneda));
    $this->pdf->Ln(4);
    $x_final_izquierda = $this->pdf->GetX();
    $y_final_izquierda = $this->pdf->GetY();
    
    
    // APARTADO: DATOS DE LA COMPRA
    $this->pdf->SetXY(122,25);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,utf8_decode('Número O/C'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','B',9);
    if( empty($orden['orden_compra']) || $orden['orden_compra'] == '--Seleccione una Orden--' ){
      $this->pdf->Cell(18,6, '-');
    }else{
      $this->pdf->Cell(30,6, $orden['orden_compra']);
    }
    $this->pdf->Ln(4);
    //$x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,utf8_decode('Factura Nº'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',9);
    if(empty($orden['factura'])){
      $this->pdf->Cell(18,6, '-');
    }else{
      $this->pdf->Cell(30,6, $orden['factura']);
    }
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6, utf8_decode('Guia de Remisión'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    if(empty($orden['guia_remision'])){
      $this->pdf->Cell(18,6, '-');
    }else{
      $this->pdf->Cell(30,6, $orden['guia_remision']);
    }
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,'Fecha de Compra');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6, formatoFechaReporte3($orden['fecha_compra']));
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,'Fecha de Ingreso');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6, formatoFechaReporte3($orden['fecha_movimiento']));
    $this->pdf->Ln(4);

    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,'Fec. Venc. Fact.');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6, formatoFechaReporte3($orden['fecha_vence_factura']));
    $this->pdf->Ln(4);

    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,utf8_decode('Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->MultiCell(47,4,utf8_decode($orden['nombre_alm']),0,'L');
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6, utf8_decode('Responsable'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->MultiCell(47,4,utf8_decode($orden['usuario']),0,'L');
    //$this->pdf->Ln(4);
    //$x_final_derecha = $this->pdf->GetX();
    $y_final_derecha  = $this->pdf->GetY();
    
    if($y_final_izquierda >= $y_final_derecha){
      $y = $y_final_izquierda;
    }else{
      $y = $y_final_derecha;
    }
    $x = $x_final_izquierda;
    
    // APARTADO: DATOS DEL DETALLE DE LA COMPRA
    $this->pdf->SetXY($x,$y);
    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','',6);
    $this->pdf->SetFillColor(128, 174, 220);
    $this->pdf->Cell(8,10,'ITEM',1,0,'L',TRUE);
    $this->pdf->Cell(60,10,'PRODUCTO',1,0,'L',TRUE);
    $this->pdf->Cell(31,10,'LABORATORIO',1,0,'L',TRUE);
    $this->pdf->Cell(15,10,'U.M',1,0,'C',TRUE);
    $this->pdf->Cell(10,10,'CANT.',1,0,'C',TRUE);
    
    $this->pdf->Cell(12,10,'P.U.',1,0,'C',TRUE);
   
    $this->pdf->MultiCell(17,5,'FECHA VENCIMIENTO',1,'C',TRUE);
    // $x=$this->pdf->GetX();
    // $y=$this->pdf->GetY();
    // $this->pdf->SetXY($x+137,$y-10);

    // $this->pdf->MultiCell(16,5,'IMPORTE sin IGV',1,'C',TRUE);

    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+153,$y-10);
    $this->pdf->Cell(13,10,utf8_decode('LOTE Nº'),1,0,'C',TRUE);
    $this->pdf->Cell(16,10,'IMPORTE',1,0,'C',TRUE);
    $this->pdf->MultiCell(8,5,'INAFECTO',1,'L',TRUE);
    $this->pdf->Ln(1);

    $this->pdf->SetFont('Arial','',8);
    $fill = TRUE;
    $this->pdf->SetDrawColor(204,204,204); // gris
    $this->pdf->SetLineWidth(.2);
   
    $i = 1;
    $allInputs['idmovimiento'] = $allInputs['resultado'];
    $detalle = $this->model_orden_compra->m_cargar_detalle_entrada($allInputs);
    //var_dump($detalle); exit();
    $exonerado = 0;
    foreach ($detalle as $key => $value) {
      $igv = 0;
      // if( $value['caja_unidad'] == 'UNIDAD' &&  $value['acepta_caja_unidad'] == '1'){
      //   $value['cantidad'] = $value['cantidad'] . ' (F)';
      // }
      if($value['excluye_igv'] == 2){
        $importe_sin = number_format( floatval($value['total_detalle'])/1.18, 2 );
        $igv = number_format( floatval($value['total_detalle'])*0.18/1.18, 2 );
        $inafecto = ' ';
      }else{
        $exonerado += number_format( floatval($value['total_detalle'])*0.18, 2 );
        $importe_sin = $value['total_detalle'];
        $inafecto = 'X';
      }
      if( strlen($value['nombre_lab']) >= 17){
        $laboratorio = substr($value['nombre_lab'], 0,17) . '...';
      }else{
        $laboratorio = $value['nombre_lab'];
      }
      $fill = !$fill;
      $this->pdf->SetWidths(array(8, 60, 31, 15, 10, 12, 17, 13, 16, 8));
      $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C', 'R', 'R','R','R','C'));
      //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
      $this->pdf->SetFillColor(230, 240, 250);
      $this->pdf->SetFont('Arial','',6);
      $this->pdf->RowSmall( 
        array(
          $i,
          utf8_decode($value['medicamento']),
          utf8_decode($laboratorio),
          utf8_decode($value['caja_unidad']),
          $value['cantidad'],
          
          $value['precio_unitario'],
          formatoFechaReporte3($value['fecha_vencimiento']),
          $value['num_lote'],
          $value['total_detalle'],
          $inafecto
        ),
        $fill,1
      );
      $i++;
    }

    $this->pdf->Ln(1);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(140,5,'Observaciones');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);

    $this->pdf->SetWidths(array(138));
    $this->pdf->TextArea(array(empty($orden['motivo_movimiento'])? '':$orden['motivo_movimiento']),0,0,FALSE,5,20);

    // $this->pdf->Cell(2,20,'');
    // $this->pdf->Cell(20,5,'EXONERADO:','LT',0,'R');
    // $this->pdf->SetFont('Arial','',8);
    // $this->pdf->Cell(30,5,number_format($exonerado, 2),'TR',0,'R');
    // $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    //$this->pdf->Cell(140,5,'');
    $this->pdf->Cell(2,20,'');
    $this->pdf->Cell(20,5,'SUBTOTAL:','LT',0,'R');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,5,substr($orden['sub_total'], 4),'RT',0,'R');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(140,5,'');
    $this->pdf->Cell(20,5,'IGV:','L',0,'R');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,5,substr($orden['total_igv'], 4),'R',0,'R');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(140,5,'');
    $this->pdf->Cell(20,5,'Total:','TLB',0,'R');
    $this->pdf->Cell(30,5,$simbolo . substr($orden['total_a_pagar'], 4),'TRB',0,'R');
    $this->pdf->Ln(15);

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_salida(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();
    // RECUPERACION DE DATOS
    $orden = $this->model_salida_farmacia->m_cargar_salida_por_id($allInputs['resultado']);

    // var_dump($orden); exit();
    // SETEO DE DATOS DEL USUARIO
    $orden['usuario'] = $orden['nombres'] . ' ' .$orden['apellido_paterno'] . ' ' .$orden['apellido_materno'];
    $orden['usuario_aprobacion'] = $orden['aprob_nombres'] . ' ' .$orden['aprob_apellido_paterno'] . ' ' .$orden['aprob_apellido_materno'];
    
    switch ($orden['tipo_movimiento']) {
      case '1': $tipo_movimiento = 'VENTA';
        break;
      case '2': $tipo_movimiento = 'COMPRA';
        break;
      case '3': $tipo_movimiento = 'TRASLADO';
        break;
      case '4': $tipo_movimiento = 'REGALO';
        break;
      case '5': $tipo_movimiento = 'BAJA';
        break;
      case '6': $tipo_movimiento = 'REINGRESO';
        break;
      case '7': $tipo_movimiento = 'ORDEN DE COMPRA';
        break;
      default: $tipo_movimiento = '';
        break;
    }
    // APARTADO: DATOS DE LA CABECERA
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6,utf8_decode('Cód. de Salida'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,utf8_decode($orden['idmovimiento']));
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->MultiCell(75,4,$orden['nombre_alm']);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Sub-Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,utf8_decode($orden['nombre_salm']));
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Tipo Salida'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(75,6,$tipo_movimiento);
    $x_final_izquierda = $this->pdf->GetX();
    $y_final_izquierda = $this->pdf->GetY();

    $this->pdf->SetXY(122,25);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,utf8_decode('Fecha de Salida'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6, formatoFechaReporte($orden['fecha_movimiento']));
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,'Responsable');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->MultiCell(47,4, utf8_decode($orden['usuario']),0,'L');
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,utf8_decode('Fecha aprobación'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,6, formatoFechaReporte($orden['fecha_aprobacion']));
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(122,$y);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(28,6,'Aprobado por');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->MultiCell(47,4, utf8_decode($orden['usuario_aprobacion']),0,'L');
     $y_final_derecha  = $this->pdf->GetY();
    
    if($y_final_izquierda >= $y_final_derecha){
      $y = $y_final_izquierda;
    }else{
      $y = $y_final_derecha;
    }
    $x = $x_final_izquierda;
    
    // APARTADO: DATOS DEL DETALLE
    $this->pdf->SetXY($x,$y);
    $this->pdf->Ln(6);
    
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->SetFillColor(128, 174, 220);
    
    $this->pdf->Cell(20,7,'ITEM',1,0,'C',TRUE);
    $this->pdf->Cell(20,7,utf8_decode('CÓD. PROD.'),1,0,'L',TRUE);
    $this->pdf->Cell(80,7,'PRODUCTO',1,0,'L',TRUE);
    $this->pdf->Cell(30,7,'FEC. VENCIMIENTO',1,0,'L',TRUE);
    $this->pdf->Cell(20,7,utf8_decode('Nº LOTE'),1,0,'C',TRUE);
    $this->pdf->Cell(20,7,utf8_decode('CANTIDAD'),1,0,'C',TRUE);
    $this->pdf->Ln(7);

    $this->pdf->SetFont('Arial','',8);
    $fill = TRUE;
    $this->pdf->SetDrawColor(204,204,204); // gris
    $this->pdf->SetLineWidth(.2);
   
    $i = 1;
    $allInputs['idmovimiento'] = $allInputs['resultado'];
    $detalle = $this->model_salida_farmacia->m_cargar_detalle_salidas($allInputs);
    //var_dump($detalle); exit();
    foreach ($detalle as $key => $value) {
      $igv = 0;
     
      $fill = !$fill;
      $this->pdf->SetWidths(array(20,20,80,30,20,20));
      $this->pdf->SetAligns(array('C', 'C', 'L', 'C', 'C', 'C'));
      //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
      $this->pdf->SetFillColor(230, 240, 250);
      $this->pdf->SetFont('Arial','',6);
      $this->pdf->Row( 
        array(
          $i,
          utf8_decode($value['idmedicamento']),
          utf8_decode($value['denominacion']),
          formatoFechaReporte3($value['fecha_vencimiento']),
          utf8_decode($value['num_lote']),
          $value['cantidad'],
        ),
        $fill,1
      );
      $i++;
    }
    $this->pdf->SetAligns(array('J'));
    $this->pdf->Ln(10);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(140,5,'Observaciones');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);

    $this->pdf->SetWidths(array(190));
    $this->pdf->TextArea(array(empty($orden['motivo_movimiento'])? '':$orden['motivo_movimiento']),0,0,FALSE,5,20);

    

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  
  public function report_kardex(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();

    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    // var_dump($allInputs); exit();
    /*** DATOS DE LA CABECERA ***/
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(22,6,utf8_decode('Producto'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(129,6,utf8_decode($allInputs['busqueda']['producto']));
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(36,6,utf8_decode('Período'),0,0,'C');

    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(22,6,utf8_decode('Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(129,6, $allInputs['busqueda']['almacen']['descripcion']);

    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(16,6,utf8_decode('Desde'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(17,6, @$allInputs['busqueda']['desde'],0,0,'R');

    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(22,6,utf8_decode('Sub-Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(129,6, $allInputs['busqueda']['subalmacen']['descripcion']);

    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(16,6,utf8_decode('Hasta'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(17,6, @$allInputs['busqueda']['hasta'],0,0,'R');

    $this->pdf->Ln(10);

    // APARTADO: MOVIMIENTOS DEL KARDEX
    $this->pdf->SetFont('Arial','',6);
    $this->pdf->SetFillColor(128, 174, 220);
    $this->pdf->Cell(50,10,'FECHA MOVIMIENTO',1,0,'C',TRUE);
    $this->pdf->Cell(80,10,'MOVIMIENTO',1,0,'C',TRUE);
    $this->pdf->Cell(14,10,'P.U.',1,0,'C',TRUE);

    $this->pdf->Cell(34,5,'ENTRADAS',1,0,'C',TRUE);
    $this->pdf->Cell(34,5,'SALIDAS',1,0,'C',TRUE);
    $this->pdf->Cell(34,5,'SALDO',1,0,'C',TRUE);

    $this->pdf->MultiCell(14,5,'PRECIO PROMEDIO',1,'C',TRUE);

    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+144,$y-5);
    $this->pdf->Cell(17,5,'CANTIDAD',1,0,'C',TRUE);
    $this->pdf->Cell(17,5,'VALORES',1,0,'C',TRUE);
    $this->pdf->Cell(17,5,'CANTIDAD',1,0,'C',TRUE);
    $this->pdf->Cell(17,5,'VALORES',1,0,'C',TRUE);
    $this->pdf->Cell(17,5,'CANTIDAD',1,0,'C',TRUE);
    $this->pdf->Cell(17,5,'VALORES',1,0,'C',TRUE);
    $this->pdf->Ln(5);
    $fill = TRUE;
    $this->pdf->SetDrawColor(204,204,204); // gris
    $this->pdf->SetLineWidth(.2);
    foreach ($allInputs['lista'] as $row) {
      $fill = !$fill;
      $this->pdf->SetWidths(array(50, 80, 14, 17, 17, 17, 17, 17, 17, 14)); // ANCHO TOTAL: 276
      $this->pdf->SetAligns(array('C', 'L', 'R', 'C', 'R', 'C', 'R','C','R','R'));
      //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
      $this->pdf->SetFillColor(230, 240, 250);
      $this->pdf->SetFont('Arial','',6);
      $this->pdf->RowSmall( 
        array(
          // utf8_decode($row['fecha']),
          utf8_decode($row['fecha_movimiento']),
          utf8_decode($row['tipo_movimiento']),
          number_format($row['precio_unitario'],2),
          utf8_decode($row['entrada']),
          number_format($row['valor_entrada'],2),
          utf8_decode($row['salida']),
          number_format($row['valor_salida'],2),
          utf8_decode($row['cantidad_saldo']),
          number_format($row['valor_saldo'],2),
          number_format($row['promedio'],2)
        ),
        $fill,1
      );
      
    }

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_inventario_farmacia(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    // RECUPERACION DE DATOS
    $this->load->model('model_medicamento_almacen');
    $lista = $this->model_medicamento_almacen->m_cargar_medicamentos_almacen_para_pdf($allInputs['paginate'],$allInputs['resultado']);

    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->MultiCell(190,4,$allInputs['resultado']['almacen']['descripcion']);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(24,6, utf8_decode('Sub Almacén'));
    $this->pdf->Cell(3,6,':',0,0,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x,$y+1);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->MultiCell(75,4,$allInputs['resultado']['subalmacen']['descripcion']);
    if( $allInputs['resultado']['laboratorio']['id'] != 0 ){
      $this->pdf->SetFont('Arial','B',9);
      $this->pdf->Cell(24,6, utf8_decode('Laboratorio'));
      $this->pdf->Cell(3,6,':',0,0,'C');
      $x=$this->pdf->GetX();
      $y=$this->pdf->GetY();
      $this->pdf->SetXY($x,$y+1);
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->MultiCell(75,4,$allInputs['resultado']['laboratorio']['descripcion']);
    }
    

    $this->pdf->Ln(7);
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor(128, 174, 220);
     $this->pdf->SetDrawColor(204,204,204); // gris
    $this->pdf->SetLineWidth(.2);
    $this->pdf->Cell(10,7, utf8_decode('ITEM'),1,0,'L',TRUE);
    $this->pdf->Cell(15,7, utf8_decode('CÓD.'),1,0,'L',TRUE);
    
    if( $allInputs['resultado']['laboratorio']['id'] == 0 ){
      $this->pdf->Cell(40,7, utf8_decode('FORMULA FARMACEUTICA'),1,0,'L',TRUE);
      $this->pdf->Cell(80,7, utf8_decode('PRODUCTO O MEDICAMENTO'),1,0,'L',TRUE);
      $this->pdf->Cell(50,7, utf8_decode('LABORATORIO'),1,0,'L',TRUE);
    }else{
      $this->pdf->Cell(50,7, utf8_decode('FORMULA FARMACEUTICA'),1,0,'L',TRUE);
      $this->pdf->Cell(120,7, utf8_decode('PRODUCTO O MEDICAMENTO'),1,0,'L',TRUE);
    }
   
    $this->pdf->Cell(14,7, utf8_decode('S.INI'),1,0,'C',TRUE);
    $this->pdf->Cell(15,7, utf8_decode('ENTR.'),1,0,'C',TRUE);
    $this->pdf->Cell(12,7, utf8_decode('SAL.'),1,0,'C',TRUE);
    $this->pdf->Cell(20,7, utf8_decode('S.ACTUAL'),1,0,'C',TRUE);
    $this->pdf->Cell(20,7, utf8_decode('P. VENTA'),1,0,'C',TRUE);
    $this->pdf->Ln(7);

    $this->pdf->SetFont('Arial','',8);
    $fill = TRUE;

    $i = 1;
    //$this->pdf->fill(array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
    $this->pdf->SetFillColor(230, 240, 250);
    $this->pdf->SetFont('Arial','',7);
    $i = 1;
    if( $allInputs['resultado']['laboratorio']['id'] == 0 ){
      $this->pdf->SetWidths(array(10,15,40,80,50,14,15,12,20,20));
      $this->pdf->SetAligns(array('C','C', 'L', 'L', 'L', 'C', 'C','C','C','R'));
      foreach ($lista as $row) {
        $fill = !$fill;
        if( strlen($row['laboratorio']) >= 17){
          $laboratorio = substr($row['laboratorio'], 0,17) . '...';
        }else{
          $laboratorio = $row['laboratorio'];
        }
        
        $this->pdf->Row( 
          array(
            $i,
            utf8_decode($row['idmedicamento']),
            utf8_decode($row['forma_farmaceutica']),
            utf8_decode($row['medicamento']),
            utf8_decode($laboratorio),
            utf8_decode($row['stock_inicial']),
            utf8_decode($row['stock_entradas']),
            utf8_decode($row['stock_salidas']),
            utf8_decode($row['stock_actual_malm']),
            utf8_decode($row['precio_venta'])
          ),
          $fill,1
        );
        $i++;
      }
    }else{
       $this->pdf->SetWidths(array(10,15,50,120,14,15,12,20,20));
      $this->pdf->SetAligns(array('C','C','L','L','C','C','C','C','R'));
      foreach ($lista as $row) {
        $fill = !$fill;
        $this->pdf->Row( 
          array(
            $i,
            utf8_decode($row['idmedicamento']),
            utf8_decode($row['forma_farmaceutica']),
            utf8_decode($row['medicamento']),
            utf8_decode($row['stock_inicial']),
            utf8_decode($row['stock_entradas']),
            utf8_decode($row['stock_salidas']),
            utf8_decode($row['stock_actual_malm']),
            utf8_decode($row['precio_venta'])
          ),
          $fill,1
        );
        $i++;
      }
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_' . $timestamp . '.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_' . $timestamp . '.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  /* MODULO ASISTENCIA - RRHH */
  public function ficha_datos_empleado(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    //var_dump($allInputs['resultado']); exit();

    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',12);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();
    //PREPARADO DE DATOS
    if( empty($allInputs['resultado']['fecha_nacimiento']) ){
      $dia = '';
      $mes = '';
      $año = '';
    }else{
      $fecha_nacimiento = explode('-', $allInputs['resultado']['fecha_nacimiento']);
      $dia = $fecha_nacimiento[0];
      $mes = $fecha_nacimiento[1];
      $año = $fecha_nacimiento[2];
    }
    if( empty($allInputs['resultado']['fecha_nacimiento_cy']) ){
      $dia_cy = '';
      $mes_cy = '';
      $año_cy = '';
    }else{
      $fecha_nacimiento_cy = explode('-', $allInputs['resultado']['fecha_nacimiento_cy']);
      $dia_cy = $fecha_nacimiento_cy[0];
      $mes_cy = $fecha_nacimiento_cy[1];
      $año_cy = $fecha_nacimiento_cy[2];
    } 
    $soltero = '';
    $casado = '';
    $viudo = '';
    $divorciado = '';
    $conviviente = '';
    switch ($allInputs['resultado']['estado_civil']) {
      case '1':
        $soltero = 'X';
        $casado = '';
        $viudo = '';
        $divorciado = '';
        $conviviente = '';
        break;
      case '2':
        $soltero = '';
        $casado = 'X';
        $viudo = '';
        $divorciado = '';
        $conviviente = '';
        break;
      case '3':
        $soltero = '';
        $casado = '';
        $viudo = 'X';
        $divorciado = '';
        $conviviente = '';
        break;
      case '4':
        $soltero = '';
        $casado = '';
        $viudo = '';
        $divorciado = 'X';
        $conviviente = '';
        break;
      case '5':
        $soltero = '';
        $casado = '';
        $viudo = '';
        $divorciado = '';
        $conviviente = 'X';
        break;
      case '6':
        $soltero = '';
        $casado = '';
        $viudo = '';
        $divorciado = '';
        $conviviente = 'X';
        break;
      default:
        break;
    }
    $this->pdf->Cell(165,8,'');
    $this->pdf->Cell(25,25,'',1,0,'C');
    //$this->pdf->Ln(8);
    if( $allInputs['resultado']['nombre_foto'] != 'noimage.jpg' && !empty($allInputs['resultado']['nombre_foto']) && file_exists('./assets/img/dinamic/empleado/' . $allInputs['resultado']['nombre_foto']) ){
      $this->pdf->Image( $allInputs['resultado']['dirImagesEmpleados'] . $allInputs['resultado']['nombre_foto'],175,25, 25, 25 );
    }
   
    $this->pdf->Ln(8);
    // APARTADO: DATOS DEL EMPLEADO
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(0,5,'DATOS PERSONALES',0,0,'L');
    $this->pdf->Ln(5);

    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(50,5,utf8_decode($allInputs['resultado']['apellido_paterno']),1,0);
    $this->pdf->Cell(50,5,utf8_decode($allInputs['resultado']['apellido_materno']),1,0);
    $this->pdf->Cell(57,5,utf8_decode($allInputs['resultado']['nombres']),1,0);
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(50,5,utf8_decode('APELLIDO PATERNO'),1,0,'C');
    $this->pdf->Cell(50,5,utf8_decode('APELLIDO MATERNO'),1,0,'C');
    $this->pdf->Cell(57,5,utf8_decode('NOMBRES'),1,0,'C');
    $this->pdf->Ln(8);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(45,5,'FECHA DE NACIMIENTO',0,0,'L');
    $this->pdf->Cell(60,5,'LUGAR DE NACIMIENTO',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(10,5,$dia,1,0,'C');
    $this->pdf->Cell(10,5,$mes,1,0,'C');
    $this->pdf->Cell(20,5,$año,1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(47,5,utf8_decode($allInputs['resultado']['lugar_nacimiento']['departamento']),1,0,'L');
    $this->pdf->Cell(49,5,utf8_decode($allInputs['resultado']['lugar_nacimiento']['provincia']),1,0,'L');
    $this->pdf->Cell(49,5,utf8_decode($allInputs['resultado']['lugar_nacimiento']['distrito']),1,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(10,5,utf8_decode('DIA'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('MES'),1,0,'C');
    $this->pdf->Cell(20,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(47,5,utf8_decode('DEPARTAMENTO'),1,0,'L');
    $this->pdf->Cell(49,5,utf8_decode('PROVINCIA'),1,0,'L');
    $this->pdf->Cell(49,5,utf8_decode('DISTRITO'),1,0,'L');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(60,5,$allInputs['resultado']['num_documento'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['carnet_extranjeria'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['ruc'],1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(60,5,utf8_decode('DNI'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('CARNÉ DE EXTRANJERIA'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('Nº R.U.C.'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(60,5,$allInputs['resultado']['codigo_essalud'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['centro_essalud'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['grupo_sanguineo'],1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(60,5,utf8_decode('Nº DE ESSALUD (AUTOGENERADO)'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('CENTRO DE ATENCION DE ESSALUD'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('GRUPO SANGUINEO'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(60,5,$allInputs['resultado']['telefono'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['telefono'],1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,$allInputs['resultado']['email'],1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(60,5,utf8_decode('Nº TELEF. DOMICILIO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('Nº TELEF. CELULAR'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(60,5,utf8_decode('CORREO ELECTRONICO'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,'DOMICILIO ACTUAL',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(0,5,utf8_decode($allInputs['resultado']['direccion']),1,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(33,5,utf8_decode('AVENIDA'),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('CALLE'),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('PASAJE'),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('JIRON'),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('URB. O LUGAR'),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->Cell(0,5,utf8_decode($allInputs['resultado']['referencia']),1,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,utf8_decode('REFERENCIA'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,'ESTADO CIVIL Y/O CONYUGAL',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(33,5,utf8_decode('SOLTERO(A)'),1,0,'C');
    $this->pdf->Cell(5,5,$soltero,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('CASADO(A)'),1,0,'C');
    $this->pdf->Cell(5,5,$casado,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('VIUDO(A)'),1,0,'C');
    $this->pdf->Cell(5,5,$viudo,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('DIVORCIADO(A)'),1,0,'C');
    $this->pdf->Cell(5,5,$divorciado,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('CONVIVIENTE(A)'),1,0,'C');
    $this->pdf->Cell(5,5,$conviviente,1,0,'C');
    $this->pdf->Ln(10);

    // APARTADO: DATOS FAMILIARES
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(0,6,'DATOS FAMILIARES',0,0,'L');
    $this->pdf->Ln(8);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,'DATOS DEL CONYUGE DEL TRABAJADOR',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(50,5,utf8_decode($allInputs['resultado']['apellido_paterno_cy']),1,0);
    $this->pdf->Cell(50,5,utf8_decode($allInputs['resultado']['apellido_materno_cy']),1,0);
    $this->pdf->Cell(90,5,utf8_decode($allInputs['resultado']['nombres_cy']),1,0);
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(50,5,utf8_decode('APELLIDO PATERNO'),1,0,'C');
    $this->pdf->Cell(50,5,utf8_decode('APELLIDO MATERNO'),1,0,'C');
    $this->pdf->Cell(90,5,utf8_decode('NOMBRES'),1,0,'C');
    $this->pdf->Ln(8);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(52,5,'FECHA DE NACIMIENTO',0,0,'L');
    $this->pdf->Cell(60,5,'',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(10,5,$dia_cy,1,0,'C');
    $this->pdf->Cell(10,5,$mes_cy,1,0,'C');
    $this->pdf->Cell(20,5,$año_cy,1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(145,5,utf8_decode($allInputs['resultado']['lugar_labores_cy']),1,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(10,5,utf8_decode('DIA'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('MES'),1,0,'C');
    $this->pdf->Cell(20,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(145,5,utf8_decode('Lugar donde Labora el Cónyuge'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,utf8_decode('DATOS REFERENTES A LOS PADRES E HIJOS DEL TRABAJADOR'),0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->Cell(65,10,utf8_decode('APELLIDOS Y NOMBRES'),1,0,'C');
    $this->pdf->Cell(25,10,utf8_decode('PARENTESCO'),1,0,'C');
    $this->pdf->Cell(30,5,utf8_decode('FECHA NACIMIENTO'),1,0,'C');
    $this->pdf->Cell(30,10,utf8_decode('OCUPACION'),1,0,'C');
    $this->pdf->MultiCell(20,5,utf8_decode('ESTADO CIVIL'),1,'C','');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+170,$y-10);
    $this->pdf->Cell(20,5,utf8_decode('VIVE'),1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->Cell(90,5,'');
    $this->pdf->Cell(10,5,'DIA',1,0,'C');
    $this->pdf->Cell(10,5,'MES',1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(50,5,'');
    $this->pdf->Cell(10,5,utf8_decode('SI'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('NO'),1,0,'C');
    $this->pdf->Ln(5);
    // PARIENTES 
    $this->load->model('model_pariente');
    $parientes = $this->model_pariente->m_cargar_parientes_de_empleado_para_pdf($allInputs['resultado']);
    $this->pdf->SetWidths(array(65, 25, 10, 10, 10, 30, 20, 10, 10));
    $this->pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'L', 'L', 'C', 'C'));
    if( empty($parientes) ){
      $this->pdf->Row(array('', '', '', '', '', '', '', '', '' ),'',1);
      $this->pdf->Row(array('', '', '', '', '', '', '', '', '' ),'',1);
      $this->pdf->Row(array('', '', '', '', '', '', '', '', '' ),'',1);
    }else{
      foreach ($parientes as $row) {
        if( empty($row['fecha_nacimiento']) ){
          $dia = '';
          $mes = '';
          $año = '';
        }else{
          $fecha_nacimiento = explode('-', $row['fecha_nacimiento']);
          $dia = $fecha_nacimiento[2];
          $mes = $fecha_nacimiento[1];
          $año = $fecha_nacimiento[0];
          
        }
        switch ($row['estado_civil']) {
          case '1': $estado_civil = 'Soltero(a)';
            break;
          case '2': $estado_civil = 'Casado(a)';
            break;
          case '3': $estado_civil = 'Viudo(a)';
            break;
          case '4': $estado_civil = 'Divorciado(a)';
            break;
          case '5': $estado_civil = 'Conviviente';
            break;
          case '6': $estado_civil = 'Conviv. no Reg.';
            break;
        }
        if($row['vive'] == '1'){
          $si_vive = 'X';
          $no_vive = '';
        }else{
          $si_vive = '';
          $no_vive = 'X';
        }
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array(
            utf8_decode($row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ' ' . $row['nombres']),
            utf8_decode($row['parentesco']),
            $dia, $mes, $año,
            utf8_decode($row['ocupacion']),
            utf8_decode($estado_civil),
            $si_vive,
            $no_vive
          ),
          '',1
        );
      }
    }
    

    $this->pdf->Ln(10);

    $this->pdf->AddPage('P','A4');
    //$this->pdf->AliasNbPages();
    $this->pdf->Ln(10);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,utf8_decode('INDIQUE DATOS DE DOS FAMILIARES A QUIENES NOTIFICAR EN UNA SITUACIÓN DE EMERGENCIA'),0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->Cell(55,5,utf8_decode('APELLIDOS Y NOMBRES'),1,0,'C');
    $this->pdf->Cell(25,5,utf8_decode('PARENTESCO'),1,0,'C');
    $this->pdf->Cell(80,5,utf8_decode('DIRECCION'),1,0,'C');
    $this->pdf->Cell(30,5,utf8_decode('TELEFONO'),1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetWidths(array(55, 25, 80, 30));
    $this->pdf->SetAligns(array('L', 'L', 'L', 'C'));
    $this->pdf->SetFont('Arial','',8);
   
    $i = 0;
    
    foreach ($parientes as $row2) {
      if( $row2['notificar_emergencia'] == '1' ){
        $this->pdf->Row(
          array(
            utf8_decode($row2['apellido_paterno'] . ' ' . $row2['apellido_materno'] . ' ' . $row2['nombres']),
            utf8_decode($row2['parentesco']),
            utf8_decode($row2['direccion']),
            $row2['telefono']
          ),
          '',1
        );
        $i++;
      }
    }
    
    if( $i == 0 ){
      $this->pdf->Row(array('', '', '', '' ),'',1);
      $this->pdf->Row(array('', '', '', '' ),'',1);
    }
    if($allInputs['resultado']['reg_pensionario'] == 'ONP' ){
      $onp = 'X';
      $afp = '';
    }elseif( $allInputs['resultado']['reg_pensionario'] == 'AFP' ){
      $onp = '';
      $afp = 'X';
    }else{
      $onp = '';
      $afp = '';
    }
    $horizonte = '';
    $integra = '';
    $profuturo = '';
    $prima = '';
    switch ($allInputs['resultado']['afp']['id']) {
      case '1': $horizonte = 'X';
        break;
      case '2': $integra = 'X';
        break;
      case '3': $profuturo = 'X';
        break;
      case '4': $prima = 'X';
        break;
    }
    $planilla = '';
    $contrato = '';

    switch ($allInputs['resultado']['condicion_laboral']) {
      case 'EN PLANILLA': $planilla = 'X';
        break;
      case 'POR CONTRATO': $contrato = 'X';
        break;
      
    }
    $this->pdf->Ln(10);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(60,5,utf8_decode('REGIMEN PENSIONARIO'),0,0,'L');
    $this->pdf->Cell(70,5,utf8_decode('AFP\'s'),0,0,'L');

    $this->pdf->Ln(5);
    $this->pdf->Cell(50,5,utf8_decode('AFP'),1,0,'L');
    $this->pdf->Cell(5,5,$afp,1,0,'C');
    $this->pdf->Cell(5,5,'');

    $this->pdf->Cell(28,5,utf8_decode('HORIZONTE'),1,0,'L');
    $this->pdf->Cell(5,5,$horizonte,1,0,'C');
    $this->pdf->Cell(28,5,utf8_decode('INTEGRA'),1,0,'L');
    $this->pdf->Cell(5,5,$integra,1,0,'C');
    $this->pdf->Cell(27,5,utf8_decode('PROFUTURO'),1,0,'L');
    $this->pdf->Cell(5,5,$profuturo,1,0,'C');
    $this->pdf->Cell(27,5,utf8_decode('PRIMA'),1,0,'L');
    $this->pdf->Cell(5,5,$prima,1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(50,5,utf8_decode('ONP'),1,0,'L');
    $this->pdf->Cell(5,5,$onp,1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->Cell(60,5,utf8_decode($allInputs['resultado']['cuspp']),1,0,'C');
    $this->pdf->Cell(60,5,utf8_decode($allInputs['resultado']['fecha_afiliacion']),1,0,'C');
    $this->pdf->Cell(70,5,utf8_decode($allInputs['resultado']['documento_afiliacion']),1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(60,5,utf8_decode('CARNET (CUSPP)'),1,0,'C');
    $this->pdf->Cell(60,5,utf8_decode('FECHA DE AFILIACIÓN'),1,0,'C');
    $this->pdf->Cell(70,5,utf8_decode(' DOCUMENTO DE AFILIACIÓN'),1,0,'C');
    $this->pdf->Ln(10);

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(0,5,'CONDICION LABORAL',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(33,5,utf8_decode('EN PLANILLA'),1,0,'C');
    $this->pdf->Cell(5,5,$planilla,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode('POR CONTRATO'),1,0,'C');
    $this->pdf->Cell(5,5,$contrato,1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode(''),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode(''),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Cell(33,5,utf8_decode(''),1,0,'C');
    $this->pdf->Cell(5,5,'',1,0,'C');
    $this->pdf->Ln(10);

    if( empty($allInputs['resultado']['fecha_ingreso']) ){
      $dia_ing = '';
      $mes_ing = '';
      $año_ing = '';
    }else{
      $fecha_ingreso = explode('-', $allInputs['resultado']['fecha_ingreso']);
      $dia_ing = $fecha_ingreso[0];
      $mes_ing = $fecha_ingreso[1];
      $año_ing = $fecha_ingreso[2];
    }
    if( empty($allInputs['resultado']['fecha_inicio_contrato']) ){
      $dia_ini = '';
      $mes_ini = '';
      $año_ini = '';
    }else{
      $fecha_inicio = explode('-', $allInputs['resultado']['fecha_inicio_contrato']);
      $dia_ini = $fecha_inicio[0];
      $mes_ini = $fecha_inicio[1];
      $año_ini = $fecha_inicio[2];
    }
    if( empty($allInputs['resultado']['fecha_fin_contrato']) ){
      $dia_fin = '';
      $mes_fin = '';
      $año_fin = '';
    }else{
      $fecha_fin = explode('-', $allInputs['resultado']['fecha_fin_contrato']);
      $dia_fin = $fecha_fin[0];
      $mes_fin = $fecha_fin[1];
      $año_fin = $fecha_fin[2];
    }

    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(45,5,'FECHA DE INGRESO',0,0,'L');
    $this->pdf->Cell(45,5,'FECHA INICIO CONTRATO',0,0,'L');
    $this->pdf->Cell(45,5,'FECHA FIN CONTRATO',0,0,'L');
    $this->pdf->Cell(55,5,'',0,0,'L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(10,5,$dia_ing,1,0,'C');
    $this->pdf->Cell(10,5,$mes_ing,1,0,'C');
    $this->pdf->Cell(20,5,$año_ing,1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(10,5,$dia_ini,1,0,'C');
    $this->pdf->Cell(10,5,$mes_ini,1,0,'C');
    $this->pdf->Cell(20,5,$año_ini,1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(10,5,$dia_fin,1,0,'C');
    $this->pdf->Cell(10,5,$mes_fin,1,0,'C');
    $this->pdf->Cell(20,5,$año_fin,1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(55,5,utf8_decode($allInputs['resultado']['cargo']),1,0,'C');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(10,5,utf8_decode('DIA'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('MES'),1,0,'C');
    $this->pdf->Cell(20,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(10,5,utf8_decode('DIA'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('MES'),1,0,'C');
    $this->pdf->Cell(20,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(10,5,utf8_decode('DIA'),1,0,'C');
    $this->pdf->Cell(10,5,utf8_decode('MES'),1,0,'C');
    $this->pdf->Cell(20,5,utf8_decode('AÑO'),1,0,'C');
    $this->pdf->Cell(5,5,'');
    $this->pdf->Cell(55,5,utf8_decode('CARGO ACTUAL'),1,0,'C');
    $this->pdf->Ln(10);
    // DATOS DE ESTUDIO
    $this->load->model('model_nivel_estudios');
    $estudios = $this->model_nivel_estudios->m_cargar_estudios_empleado($allInputs['resultado']);

    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(0,6,'DATOS DE ESTUDIO',0,0,'L');
    $this->pdf->Ln(8);

    
    /***** EDUCACION BASICA *****/
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(30,10,utf8_decode('EDUCACIÓN'),1,0,'C');
    $this->pdf->MultiCell(35,5,utf8_decode('COMPLETA O INCOMPLETA'),1,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+65,$y-10);
    $this->pdf->Cell(65,10,utf8_decode('CENTRO DE ESTUDIOS'),1,0,'C');
    $this->pdf->Cell(30,10,utf8_decode('DESDE'),1,0,'C');
    $this->pdf->Cell(30,10,utf8_decode('HASTA'),1,0,'C');
    $this->pdf->Ln(10);
    $this->pdf->SetWidths(array(30, 35, 65, 30, 30));
    $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C'));
    $this->pdf->SetFont('Arial','',7);
    if( empty($estudios) ){
      $this->pdf->Row(array('', '', '', '', '' ),'',1);
      $this->pdf->Row(array('', '', '', '', '' ),'',1);
    }else{
      foreach ($estudios as $row) {
        if( $row['tipo_ne'] == '1'){
          $this->pdf->Row( 
            array(
              utf8_decode($row['descripcion_ne']),
              $row['estudio_completo'] == 1? utf8_decode('COMPLETA'): utf8_decode('INCOMPLETA'),
              utf8_decode($row['centro_estudio']),
              darFormatoDMY($row['fecha_desde']),
              darFormatoDMY($row['fecha_hasta'])
            ),
            '',1
          );
        }
      }
    }
    $this->pdf->Ln(10);
    /***** EDUCACION SUPERIOR *****/
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->MultiCell(30,5,utf8_decode('EDUCACIÓN SUPERIOR'),1,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+30,$y-10);
    $this->pdf->Cell(40,10,utf8_decode('ESPECIALIDAD'),1,0,'C');
    $this->pdf->Cell(50,10,utf8_decode('CENTRO DE ESTUDIOS'),1,0,'C');
    $this->pdf->Cell(15,10,utf8_decode('DESDE'),1,0,'C');
    $this->pdf->Cell(15,10,utf8_decode('HASTA'),1,0,'C');
    $this->pdf->MultiCell(20,5,utf8_decode('COMPLETA O INCOMPLETA'),1,'C');
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+170,$y-10);
    $this->pdf->MultiCell(20,5,utf8_decode('GRADO ACADEMICO'),1,'C');
    //$this->pdf->Ln(10);
    $this->pdf->SetWidths(array(30, 40, 50, 15, 15, 20, 20));
    $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C', 'L', 'L'));
    $this->pdf->SetFont('Arial','',7);
    if( empty($estudios) ){
      $this->pdf->Row(array('', '', '', '', '' ,'',''),'',1);
      $this->pdf->Row(array('', '', '', '', '' ,'',''),'',1);
      $this->pdf->Row(array('', '', '', '', '' ,'',''),'',1);
    }else{
      $separador = true;
      foreach ($estudios as $row) {
        if( $row['tipo_ne'] == '2'){
          $this->pdf->Row( 
            array(
              utf8_decode($row['descripcion_ne']),
              utf8_decode($row['especialidad']),
              utf8_decode($row['centro_estudio']),
              darFormatoDMY($row['fecha_desde']),
              darFormatoDMY($row['fecha_hasta']),
              $row['estudio_completo'] == 1? utf8_decode('COMPLETA'): utf8_decode('INCOMPLETA'),
              utf8_decode($row['grado_academico'])
            ),
            '',1
          );
        }elseif( $row['tipo_ne'] == '3' ){ /***** SEGUNDA CARRERA *****/
          if($separador){
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(0,5,utf8_decode('SEGUNDA CARRERA PROFESIONAL'),1,0,'C');
            $this->pdf->Ln(5);
            $separador = false;
            $this->pdf->SetFont('Arial','',7);
          }
          $this->pdf->Row( 
            array(
              utf8_decode($row['descripcion_ne']),
              utf8_decode($row['especialidad']),
              utf8_decode($row['centro_estudio']),
              darFormatoDMY($row['fecha_desde']),
              darFormatoDMY($row['fecha_hasta']),
              $row['estudio_completo'] == 1? utf8_decode('COMPLETA'): utf8_decode('INCOMPLETA'),
              utf8_decode($row['grado_academico'])
            ),
            '',1
          );
          
        }
      }
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function avance_profesional_empleado()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();

    $fEmpleado = $this->model_empleado->m_cargar_este_empleado_por_codigo($allInputs);
    $this->pdf->SetFont('Arial','',13);
    $this->pdf->Cell(80,8,'');// EMPUJA A LA CELDA
    $this->pdf->Cell(30,30,'',1,0,'C'); // CELDA QUE CONTENDRA A LA IMAGEN 
    if( !empty($fEmpleado['nombre_foto']) && file_exists('./assets/img/dinamic/empleado/' . $fEmpleado['nombre_foto']) ){
      $this->pdf->Image('assets/img/dinamic/empleado/' . $fEmpleado['nombre_foto'],90,25,30,30);
    }else{
      $this->pdf->Image('assets/img/dinamic/empleado/noimage.jpg'); 
    } 
    $this->pdf->Ln(); 
    $this->pdf->Cell(0,10,strtoupper(utf8_decode($fEmpleado['empleado'])),0,1,'C'); 
    $this->pdf->SetTextColor(170);
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->Cell(0,0,strtoupper(utf8_decode($fEmpleado['cargo'])),0,1,'C'); 
    $this->pdf->SetTextColor(0);
    
    $this->pdf->Ln();
    $this->pdf->Cell(0,5,'',0);
    $listaAvanceProf = $this->model_empleado->m_cargar_avance_profesional_empleado($allInputs);

    foreach ($listaAvanceProf as $key => $row) { 
      // $this->pdf->Cell(40,4,'CENTRO DE ESTUDIOS: ',0,1,'C');
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Ln();
      $this->pdf->Cell(0,3,'','T');
      $this->pdf->Ln();
      $this->pdf->Cell(0,3,strtoupper($row['centro_estudio']),0,1,'C'); 
     
      if( !empty($row['especialidad']) ){ 
        $this->pdf->Ln();
        $this->pdf->SetFont('Arial','B',11);
        $this->pdf->Cell(56,5,'DENOMINACION'); 
        $this->pdf->Cell(2,5,':'); 
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->Cell(0,5,strtoupper($row['especialidad'])); 
      }
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(56,5,'TIPO DE ESTUDIO');
      $this->pdf->Cell(2,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(0,5,$row['tipo_ne']);
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(56,5,'NIVEL DE ESTUDIO: ');
      $this->pdf->Cell(2,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(0,5,$row['descripcion_ne']);
      $this->pdf->Ln();
      if( !empty($row['grado_academico']) ){ 
        $this->pdf->SetFont('Arial','B',11);
        $this->pdf->Cell(56,5,utf8_decode('GRADO ACADÉMICO: '));
        $this->pdf->Cell(2,5,':'); 
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->Cell(0,5,$row['grado_academico']);
        $this->pdf->Ln();
      }
      
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(56,5,'DESDE: ');
      $this->pdf->Cell(2,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(0,5,darFormatoMesAno($row['fecha_desde']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(56,5,'HASTA: ');
      $this->pdf->Cell(2,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(0,5,darFormatoMesAno($row['fecha_hasta']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(56,5,'COMPLETO/INCOMPLETO ');
      $this->pdf->Cell(2,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(0,5,$row['estudio_completo']);
      $this->pdf->Ln();
      // $this->pdf->Cell(0,5,'','B');
      // $this->pdf->Ln();
    }
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function creacionEstructuraAsistencia($lista,$allInputs,$fEmpleado = FALSE)
  {
    // Agrupar por fecha 
    $arrMainArray = array(); 
    foreach ($lista as $key => $row) { 
      $rowAux = array( 
        'fecha' => $row['fecha'],
        'fecha_timestamp'=> strtotime($row['fecha']),
        'motivo_fecha_especial' => $row['descripcion_mh']. '-' . $row['descripcion_smh'],
        'hora_maestra_entrada' => $row['hora_maestra_entrada'],
        'hora_maestra_salida' => $row['hora_maestra_salida'],
        'tiempo_tolerancia_maestra' => $row['tiempo_tolerancia_maestra']
      );
      $arrMainArray[$row['fecha']] = $rowAux;
    }
    // var_dump("<pre>",$arrMainArray); exit();
    /* Obtener fechas no contempladas */ 
    $arrFechas = get_rangofechas($allInputs['desde'],$allInputs['hasta'],TRUE);
    $arrFechasFaltantes = array();
    //$arrSoloFechas = array();
    foreach ($arrFechas as $keyDet => $rowDet) { 
      $fechaEnLista = 'NO';
      foreach ($arrMainArray as $key => $row) { 
        if( $rowDet == $row['fecha'] ){
          $fechaEnLista = 'SI';
        }
      }
      if( $fechaEnLista == 'NO' ){
        $arrFechasFaltantes[] = $rowDet;
        //$arrSoloFechas[] = 
      }
    }
    /* Agregar y ordenar fechas no contempladas */ 
    foreach ($arrFechasFaltantes as $keyDet => $rowDet) { 
      $arrInsertar = array(
          'fecha' => $rowDet,
          'fecha_timestamp'=> strtotime($rowDet),
          'motivo_fecha_especial'=> NULL,
          'hora_maestra_entrada' => NULL,
          'hora_maestra_salida' => NULL,
          'tiempo_tolerancia_maestra' => NULL
      );
      $arrMainArray[$rowDet] = $arrInsertar;
    }
    usort($arrMainArray,'fnOrdering');
    
    /* Agregar fechas especiales en faltas */ 
    $arrParams['arrFechasEsp'] = $arrFechasFaltantes;
    $arrParams['idempleado'] = $fEmpleado['idempleado'];
    $listaFE = array(); 
    if( !empty($arrParams['arrFechasEsp']) ){
      $listaFE = $this->model_asistencia->m_cargar_estas_fechas_especiales_de_empleado($arrParams); 
    }
    
    foreach ($arrMainArray as $key => $row) { 
      foreach ($listaFE as $keyDet => $rowDet) {
        if( $row['fecha'] === $rowDet['fecha_especial'] ){
          if($rowDet['descripcion_mh'] === $rowDet['descripcion_smh'])
            $arrMainArray[$key]['motivo_fecha_especial'] = $rowDet['descripcion_mh'];
          else
            $arrMainArray[$key]['motivo_fecha_especial'] = $rowDet['descripcion_mh']. '-' . $rowDet['descripcion_smh'];
        } 
      }
    }

    /* Agregar feriados y dias festivos */
    $listaDF = $this->model_feriado->m_cargar_feriados_entre_fechas($allInputs); 
    foreach ($arrMainArray as $key => $row) { 
      foreach ($listaDF as $keyDet => $rowDet) { 
        if( $row['fecha'] === $rowDet['fecha'] ){ 
          $arrMainArray[$key]['motivo_fecha_especial'] = 'FERIADO';
        } 
      }
    }
    /* Agregar cumpleaños de empleado */ 
    // var_dump("<pre>",$arrMainArray); exit();
    foreach ($arrMainArray as $key => $row) { 

      $fechaNacimiento = $fEmpleado['fecha_nacimiento'];
      $diaNacimiento = date('d',strtotime($fechaNacimiento));
      $mesNacimiento = date('m',strtotime($fechaNacimiento));
      
      $fechaHoy = $row['fecha'];
      $diaHoy = date('d',strtotime($fechaHoy));
      $mesHoy = date('m',strtotime($fechaHoy));
      // var_dump($diaNacimiento,$mesNacimiento,$diaHoy,$mesHoy); 
      if( $diaNacimiento == $diaHoy && $mesNacimiento == $mesHoy ){ 
        $arrMainArray[$key]['motivo_fecha_especial'] .= ' CUMPLEAÑOS'; 
      } 
    }

    // exit(); 
    // Agregar entrada break y salida 
    foreach ($arrMainArray as $key => $row) { 
      $arrAuxBloques = array(
        'entradas' => array(),
        'salidas' => array(),
        'break' => array(),
        'visitas' => array(),
      );
      $arrAuxBSalidas = array();
      $arrAuxBBreak = array();
      $arrAuxBVisitas = array();
      foreach ($lista as $keyDet => $rowDet) { 
        if( $row['fecha'] == $rowDet['fecha'] ){ 
          if( $rowDet['tipo_asistencia'] == 'E' ){
            array_push($arrAuxBloques['entradas'], 
              array(
                'idasistencia'=> $rowDet['idasistencia'],
                'estado'=> $rowDet['descripcion'],
                'numEstado'=> $rowDet['idestadoasistencia'],
                'hora'=> $rowDet['hora'],
                'diferencia_tiempo'=> $rowDet['diferencia_tiempo']
              )
            );
          }
          if( $rowDet['tipo_asistencia'] == 'S' ){
            array_push($arrAuxBloques['salidas'], 
              array(
                'idasistencia'=> $rowDet['idasistencia'],
                'hora'=> $rowDet['hora'],
                'diferencia_tiempo'=> $rowDet['diferencia_tiempo']
              )
            );
          }
          if( $rowDet['tipo_asistencia'] == 'B' || $rowDet['tipo_asistencia'] == 'V'){ 
            $indexBloque = 'break'; 
            // if( $rowDet['tipo_asistencia'] == 'V' ){
            //   $indexBloque = 'visitas';
            // }
            // if( $rowDet['tipo_asistencia'] == 'B' ){
            //   $indexBloque = 'break'; 
            // }
            array_push($arrAuxBloques[$indexBloque], 
              array(
                'idasistencia'=> $rowDet['idasistencia'],
                'hora'=> $rowDet['hora']
              )
            );
          }
        }
      }
      $arrMainArray[$key]['bloques'] = $arrAuxBloques;
    }
    // var_dump($arrMainArray); exit();
    return $arrMainArray;
  }
  public function report_asistencia_por_empleado()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    //$arrResult = CalculoFaltasTardanzasEmpleado('332','25-05-2017','26-06-2017');
    //var_dump($arrResult); exit();
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']); 
    $this->pdf->SetFont('Arial','',11); 
    if( $allInputs['porEmpresaOSede']['id'] == 'PE' ){ 
      if( @$allInputs['allEmpleados'] === TRUE ){ 
        $arrCondicionLab = NULL;
        if( !empty($allInputs['tipoContratosSeleccionadas']) ){ 
          $arrCondicionLab = array(); 
          foreach ($allInputs['tipoContratosSeleccionadas'] as $key => $row) {
            $arrCondicionLab[] = $row['id'];
          }
        }
        $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia($allInputs['empresa'],$arrCondicionLab,@$allInputs['soloEmpActivos']);
      }else{
        $listaEmpleados[] = $this->model_empleado->m_cargar_este_empleado_por_codigo($allInputs['empleado']);
      }
    }elseif( $allInputs['porEmpresaOSede']['id'] == 'PS' ){ 
      $arrCondicionLab = NULL;
      if( !empty($allInputs['tipoContratosSeleccionadas']) ){ 
        $arrCondicionLab = array();
        foreach ($allInputs['tipoContratosSeleccionadas'] as $key => $row) {
          $arrCondicionLab[] = $row['id'];
        }
      }
      $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia(NULL,$arrCondicionLab,@$allInputs['soloEmpActivos'],$allInputs['sede']);
    }
    
    /*function fnOrdering($a, $b) { 
        return $a['fecha_timestamp'] - $b['fecha_timestamp'];
    }*/

    foreach ($listaEmpleados as $key => $fEmpleado) { 
      $this->pdf->AddPage('L','A4');
      $this->pdf->AliasNbPages();
      // var_dump("<pre>",$fEmpleado); exit(); 
      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->Cell(50,5,'DNI');
      $this->pdf->SetFont('Arial', '', 11);
      $this->pdf->Cell(5,5,':');
      $this->pdf->Cell(0,5,$fEmpleado['numero_documento']);
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->Cell(50,5,'EMPLEADO');
      $this->pdf->SetFont('Arial', '', 11);
      $this->pdf->Cell(5,5,':');
      $this->pdf->Cell(0,5,utf8_decode($fEmpleado['empleado']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->Cell(50,5,'EMPRESA');
      $this->pdf->SetFont('Arial', '', 11);
      $this->pdf->Cell(5,5,':');
      $this->pdf->Cell(0,5,utf8_decode($fEmpleado['empresa']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->Cell(50,5,'CARGO');
      $this->pdf->SetFont('Arial', '', 11);
      $this->pdf->Cell(5,5,':');
      $this->pdf->Cell(0,5,utf8_decode($fEmpleado['cargo']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->Cell(50,4,'PERIODO'); 
      $this->pdf->SetFont('Arial', '', 11);
      $this->pdf->Cell(5,5,':');
      $this->pdf->Cell(0,5,strtoupper(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta'])) );

      $this->pdf->SetFont('Arial', '', 11);

      /* TRATAMIENTO DE DATOS */ 
      $allInputs['empleado']['id'] = $fEmpleado['idempleado'];
      $lista = $this->model_asistencia->m_cargar_asistencias_de_empleado_reporte($allInputs);
      // var_dump("<pre>",$lista); exit();
      $arrMainArray = $this->creacionEstructuraAsistencia($lista,$allInputs,$fEmpleado);      
      // var_dump("<pre>",$arrMainArray); exit();
      $this->pdf->Cell(0,10,'',0,0);
      $this->pdf->Ln(5);
      
      $this->pdf->SetWidths(array(26, 54, 54, 36, 54, 30, 20));
      $wDetalle = $this->pdf->GetWidths();
      $hDetalle = array(10, 6, 6, 6, 6, 10, 10);

      /* H. EXTRA = 9 - (FINAL - INICIAL) donde 9 es igual al número de horas trab(8) + 1hora de break */ 
      $headerDetalle = array('FECHA', 'ENTRADA', 'BREAK', 'SALIDA', 'ESTADO', 'F. ESPECIAL', 'H. EXTRA.'); 

      $this->pdf->SetFont('Arial', 'B', 11);
      $this->pdf->SetFillColor(190);
      for($i=0;$i<count($headerDetalle);$i++){
        $this->pdf->Cell($wDetalle[$i],$hDetalle[$i],$headerDetalle[$i],1,0,'C',true);
      }
      $this->pdf->SetFillColor(210);
      $this->pdf->Ln(6);
      for($i=0;$i<count($headerDetalle);$i++){ 
        if( $headerDetalle[$i] == 'FECHA' || $headerDetalle[$i] == 'H TRAB.'){
          $this->pdf->Cell($wDetalle[$i],0,'',0,0,true);
        }
        
        if( $hDetalle[$i] == '6' && $headerDetalle[$i] == 'ENTRADA' ){
          $this->pdf->SetFont('Arial', 'B', 7);
          $this->pdf->Cell(22,4,'H. ENTRADA',1,0,'C',true);
          $this->pdf->Cell(10,4,'TOL.',1,0,'C',true);
          $this->pdf->Cell(22,4,'H. MARCADO',1,0,'C',true);
          $this->pdf->SetFont('Arial', 'B', 10);
        }
        if( $hDetalle[$i] == '6' && $headerDetalle[$i] == 'BREAK' ){
          $this->pdf->SetFont('Arial', 'B', 7);
          $this->pdf->Cell(20,4,'H.MARCADO 1',1,0,'C',true);
          $this->pdf->Cell(20,4,'H.MARCADO 2',1,0,'C',true);
          $this->pdf->Cell(14,4,'TOTAL B.',1,0,'C',true);
          $this->pdf->SetFont('Arial', 'B', 10);
        }
        if( $hDetalle[$i] == '6' && $headerDetalle[$i] == 'SALIDA' ){
          $this->pdf->SetFont('Arial', 'B', 7);
          $this->pdf->Cell(($wDetalle[$i] / 2),4,'H.SALIDA',1,0,'C',true);
          $this->pdf->Cell(($wDetalle[$i] / 2),4,'H.MARCADO',1,0,'C',true);
          $this->pdf->SetFont('Arial', 'B', 10);
        }
        if( $hDetalle[$i] == '6' && $headerDetalle[$i] == 'ESTADO' ){
          $this->pdf->SetFont('Arial', 'B', 7);
          $this->pdf->Cell(($wDetalle[$i] / 3),4,'PUNTUAL',1,0,'C',true);
          $this->pdf->Cell(($wDetalle[$i] / 3),4,'TARDANZA',1,0,'C',true);
          $this->pdf->Cell(($wDetalle[$i] / 3),4,'FALTA',1,0,'C',true);
          $this->pdf->SetFont('Arial', 'B', 10);
        }
      }
      $this->pdf->SetFillColor(255);
      $this->pdf->SetWidths(array(18,8, 22, 10, 22, 20, 20, 14, 18, 18, 5, 13, 5, 13, 18, 30, 20));
      $this->pdf->SetAligns(array('C','C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
      //$this->pdf->Ln();

      $fill = TRUE;
      $this->pdf->SetY(60);
      $this->pdf->SetFont('Arial','',7); // var_dump($arrMainArray); exit(); 
      foreach ($arrMainArray as $key => $row) { 
        $rowFecha = $row['fecha']; 
        $fechaUT = strtotime($rowFecha); // obtengo una fecha UNIX ( integer )
        $numDiaSemana  = date('N', $fechaUT); //obtiene los dias en formato 1 - 7
        $shortDayArray = array("","L","M","M","J","V","S","D");
        $strNombreDia = $shortDayArray[$numDiaSemana];
        // $strNombreDia
        /* PRIMERA ENTRADA  */
        $horaMarcadoE = NULL;
        if( !empty($arrMainArray[$key]['bloques']['entradas']) ){
          $horaMarcadoE = $arrMainArray[$key]['bloques']['entradas'][0]['hora'];
        }
        /* ULTIMA SALIDA */
        $horaMarcadoS = NULL;
        if( !empty($arrMainArray[$key]['bloques']['salidas']) ){
          $horaMarcadoS = $arrMainArray[$key]['bloques']['salidas'][count($arrMainArray[$key]['bloques']['salidas']) - 1]['hora'];
        }
        /* BREAK */
        $horaMarcado1B = NULL;
        if( !empty($arrMainArray[$key]['bloques']['break']) ){
          $horaMarcado1B = $arrMainArray[$key]['bloques']['break'][0]['hora'];
          //var_dump($horaMarcado1B); exit();
        }
        $horaMarcado2B = NULL;
        if( !empty($arrMainArray[$key]['bloques']['break']) && !empty($arrMainArray[$key]['bloques']['break'][1]) ){
          $horaMarcado2B = $arrMainArray[$key]['bloques']['break'][count($arrMainArray[$key]['bloques']['break']) - 1]['hora'];
        }
        $totalBreakB = NULL;
        if( !empty($horaMarcado1B) && !empty($horaMarcado2B) ){ 
          $totalBreakB = minutos_transcurridos($horaMarcado1B,$horaMarcado2B).' Min.';
        }
        $estadoPuntual = array(
          'simbolo'=> NULL,
          'diferencia'=> NULL
        );
        $estadoTardanza = array(
          'simbolo'=> NULL,
          'diferencia'=> NULL
        );
        // $estadoTardanza = array();
        $estadoFalta = NULL;
        if( !empty($horaMarcadoE) ){ 
          if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 1 ){
            $estadoPuntual['simbolo'] = 'X';
            $estadoPuntual['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
          }
          if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 2 || $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 3 ){
            $estadoTardanza['simbolo'] = 'X';
            $estadoTardanza['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
          }
        }
        /* VALIDAR SI ES FALTA */ 
        if( empty($estadoPuntual['simbolo']) && empty($estadoTardanza['simbolo']) ){ 
          /* ¿TIENE HORARIO EN ESTE DIA? */
          /* HORARIO ESPECIAL */
          $tieneHorarioEspecial = TRUE;
          $fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($fEmpleado['idempleado'],$row['fecha']); 
          if( empty($fHorarioEspecial) ){ 
            $tieneHorarioEspecial = FALSE; 
          }
          if($tieneHorarioEspecial === FALSE){ 
            /* HORARIO GENERAL */
            $tieneHorarioGeneral = TRUE;
            $arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'); 
            $diaSemana = date('w',strtotime("$rowFecha")); //var_dump($fEmpleado['idempleado']); exit(); 
            $fHorarioGeneral = $this->model_horario_general->m_obtener_horario_general_de_empleado($fEmpleado['idempleado'],$arrDiasSemana[$diaSemana]);
            if( empty($fHorarioGeneral) ){ 
              $tieneHorarioGeneral = FALSE; 
            }
          }
          if( $tieneHorarioEspecial || $tieneHorarioGeneral ){ 
            if( strtotime($rowFecha) < strtotime(date('Y-m-d')) ){
              $estadoFalta = 'X';
            }
          }
        }
        if( !empty($row['tiempo_tolerancia_maestra']) ){
          $row['tiempo_tolerancia_maestra'] = $row['tiempo_tolerancia_maestra'].' Min';
        }
        $horasTrabajadas = NULL;
        $horasExtra = NULL;
        if( !empty($arrMainArray[$key]['bloques']['entradas']) && !empty($arrMainArray[$key]['bloques']['salidas']) ){
          $dtHoraEntrada = $row['fecha'].' '.$horaMarcadoE;
          $dtHoraSalida = $row['fecha'].' '.$horaMarcadoS;
          $horasTrabajadas = timestampToHuman( strtotime($dtHoraSalida) - strtotime($dtHoraEntrada) ); 
          $horasTrabajadasTS = strtotime($dtHoraSalida) - strtotime($dtHoraEntrada);
          /* H. EXTRA = (FINAL - INICIAL) - 9 donde 9 es igual al número de horas trab(8) + 1hora de break */ 
          $horasExtra = timestampToHuman(strtotime('-9 hour' , $horasTrabajadasTS)); 
          // strtotime ( '+1 hour' , strtotime ( $fecha ) )
        }
        // var_dump($horasExtra,strtotime($dtHoraSalida),strtotime($dtHoraEntrada),strtotime(.' 09:00:00')); 
        // var_dump(($dtHoraSalida),($dtHoraEntrada)); 
        // exit(); 
        $this->pdf->RowSmall( 
          array( 
            $row['fecha'],
            $strNombreDia,
            $row['hora_maestra_entrada'],
            $row['tiempo_tolerancia_maestra'],
            $horaMarcadoE,
            $horaMarcado1B,
            $horaMarcado2B,
            $totalBreakB,
            $row['hora_maestra_salida'],
            $horaMarcadoS,
            $estadoPuntual['simbolo'],
            $estadoPuntual['diferencia'],
            $estadoTardanza['simbolo'],
            $estadoTardanza['diferencia'],
            $estadoFalta,
            $row['motivo_fecha_especial'],
            $horasExtra
          )
          ,$fill,1,array('B','B', '', '', 'B', '', '', '', '', 'B', 'B', '', 'B', '', 'B', '', ''),5
          ,FALSE
          ,array('p3','p3', '', '', 'p5', '', '', '', '', 'p5', 'p4', 'p4', 'p4', 'p4', 'p4', '', '')
        );
      }
    }
    //var_dump("<pre>",$arrMainArray); exit();
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_asistencia_por_empleado_excel(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    if( $allInputs['porEmpresaOSede']['id'] == 'PE' ){
      if( @$allInputs['allEmpleados'] === TRUE ){ 
        $arrCondicionLab = NULL;
        if( !empty($allInputs['tipoContratosSeleccionadas']) ){
          $arrCondicionLab = array();
          foreach ($allInputs['tipoContratosSeleccionadas'] as $key => $row) {
            $arrCondicionLab[] = $row['id'];
          }
        }
        $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia($allInputs['empresa'],$arrCondicionLab);
      }else{ 
        $listaEmpleados[] = $this->model_empleado->m_cargar_este_empleado_por_codigo($allInputs['empleado']);
      }
    }else{
      $arrCondicionLab = NULL;
      if( !empty($allInputs['tipoContratosSeleccionadas']) ){ 
        $arrCondicionLab = array(); 
        foreach ($allInputs['tipoContratosSeleccionadas'] as $key => $row) {
          $arrCondicionLab[] = $row['id'];
        } 
      }
      $allInputs['soloEmpActivos'] = empty($allInputs['soloEmpActivos'])?  NULL : $allInputs['soloEmpActivos'];
      $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia(NULL,$arrCondicionLab,$allInputs['soloEmpActivos'],$allInputs['sede']);
    }
      

    $arrData['flag'] = 0;
    
    $cont = 0;
    $maxCol = 'Q';
    // var_dump($lista); exit(); 
    
    $dataColumnsTP = array( 
      array('FECHA','', 'ENTRADA','','', 'BREAK','','', 'SALIDA','', 'ESTADO','','','','', 'F. ESPECIAL', 'H. EXTRAS.')
    );
    $dataColumnsTP2 = array( 
      array('','', 'H. ENTRADA','TOL','H. MARCADO', 'H. MARCADO 1','H. MARCADO 2','TOTAL B.', 'H. SALIDA','H. MARCADO',
        'PUNTUAL','','TARDANZA','','FALTA', '', '')
    );
    $currentCellEncabezado = 3;
    /*function fnOrdering($a, $b) { 
        return $a['fecha_timestamp'] - $b['fecha_timestamp'];
    }*/
    foreach ($listaEmpleados as $key => $fEmpleado) {
      
      $arrListadoProd = array();
      $this->excel->setActiveSheetIndex($cont);
      $this->excel->getActiveSheet()->setTitle('Asistencia Por Empleado');
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
            'size'  => 8,
            'name'  => 'Verdana',
            'color' => array('rgb' => 'FFFFFF') 
        ),
        'fill' => array( 
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'rgb' => '5B9BD5', ),
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
      /* titulo */
      $this->excel->getActiveSheet()->getCell('A1')->setValue($allInputs['titulo']); 
      $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
      $this->excel->getActiveSheet()->mergeCells('A1:'.$maxCol.'1');
      /* datos de cabecera*/
      $this->excel->getActiveSheet()->getCell('A'.$currentCellEncabezado)->setValue('DNI');
      $this->excel->getActiveSheet()->getCell('B'.$currentCellEncabezado)->setValue(':');
      $this->excel->getActiveSheet()->getCell('C'.$currentCellEncabezado)->setValue($fEmpleado['numero_documento'].'');
      $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+1))->setValue('EMPLEADO');
        $this->excel->getActiveSheet()->getCell('B'.($currentCellEncabezado+1))->setValue(':');
      $this->excel->getActiveSheet()->getCell('C'.($currentCellEncabezado+1))->setValue($fEmpleado['empleado']);
      $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+2))->setValue('EMPRESA');
        $this->excel->getActiveSheet()->getCell('B'.($currentCellEncabezado+2))->setValue(':');
      $this->excel->getActiveSheet()->getCell('C'.($currentCellEncabezado+2))->setValue($fEmpleado['empresa']);
      $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+3))->setValue('CARGO');
        $this->excel->getActiveSheet()->getCell('B'.($currentCellEncabezado+3))->setValue(':');
      $this->excel->getActiveSheet()->getCell('C'.($currentCellEncabezado+3))->setValue($fEmpleado['cargo']);
      $this->excel->getActiveSheet()->getCell('A'.($currentCellEncabezado+4))->setValue('PERIODO');
        $this->excel->getActiveSheet()->getCell('B'.($currentCellEncabezado+4))->setValue(':');
      $this->excel->getActiveSheet()->getCell('C'.($currentCellEncabezado+4))->setValue(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta']));

      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':A'.($currentCellEncabezado+4))->applyFromArray($styleArrayEncabezado);
      $this->excel->getActiveSheet()->getStyle('B'.$currentCellEncabezado.':B'.($currentCellEncabezado+4))->applyFromArray($styleArrayEncabezado2);
      $this->excel->getActiveSheet()->getStyle('C'.$currentCellEncabezado.':C'.($currentCellEncabezado+4))->applyFromArray($styleArrayEncabezado3);

      $currentCellEncabezado = $currentCellEncabezado+6;

      // SETEO DE ANCHO DE COLUMNAS
      $colWidth = array(15,5, 15,5,15, 15,15,10, 15,15, 5,10,5,10,15, 20,15);
      $i=0;
      foreach(range('A',$maxCol) as $columnID) {
        $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($colWidth[$i++]);
      }

      // $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
      // $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;

      // ENCABEZADO PRINCIPAL DE LA LISTA
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
      /*COMBINAR*/
      $this->excel->getActiveSheet()->mergeCells('A'.$currentCellEncabezado.':'.'B'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->mergeCells('C'.$currentCellEncabezado.':'.'E'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->mergeCells('F'.$currentCellEncabezado.':'.'H'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->mergeCells('I'.$currentCellEncabezado.':'.'J'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->mergeCells('K'.$currentCellEncabezado.':'.'O'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->mergeCells('P'.$currentCellEncabezado.':'.'P'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->mergeCells('Q'.$currentCellEncabezado.':'.'Q'.($currentCellEncabezado+1));
      $currentCellEncabezado = $currentCellEncabezado+1;

      // ENCABEZADO SECUNDARIO DE LA LISTA
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->getAlignment()->setWrapText(true);
      $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$maxCol.$currentCellEncabezado)->applyFromArray($styleArrayHeader2);
      $this->excel->getActiveSheet()->fromArray($dataColumnsTP2, null, 'A'.$currentCellEncabezado);
      /*COMBINAR*/
      $this->excel->getActiveSheet()->mergeCells('K'.$currentCellEncabezado.':'.'L'.$currentCellEncabezado);
      $this->excel->getActiveSheet()->mergeCells('M'.$currentCellEncabezado.':'.'N'.$currentCellEncabezado);
      
      /* TRATAMIENTO DE DATOS */ 
      $allInputs['empleado']['id'] = $fEmpleado['idempleado'];
      $lista = $this->model_asistencia->m_cargar_asistencias_de_empleado_reporte($allInputs);
      // var_dump("<pre>",$lista); exit();
      $arrMainArray = $this->creacionEstructuraAsistencia($lista,$allInputs,$fEmpleado);

      foreach ($arrMainArray as $key => $row) { 
        $rowFecha = $row['fecha'];

        $fechaUT = strtotime($rowFecha); // obtengo una fecha UNIX ( integer )
        $numDiaSemana  = date('N', $fechaUT); //obtiene los dias en formato 1 - 7
        $shortDayArray = array("","L","M","M","J","V","S","D");
        $strNombreDia = $shortDayArray[$numDiaSemana];
        // $strNombreDia
        /* PRIMERA ENTRADA  */
        $horaMarcadoE = NULL;
        if( !empty($arrMainArray[$key]['bloques']['entradas']) ){
          $horaMarcadoE = $arrMainArray[$key]['bloques']['entradas'][0]['hora'];
        }
        /* ULTIMA SALIDA */
        $horaMarcadoS = NULL;
        if( !empty($arrMainArray[$key]['bloques']['salidas']) ){
          $horaMarcadoS = $arrMainArray[$key]['bloques']['salidas'][count($arrMainArray[$key]['bloques']['salidas']) - 1]['hora'];
        }
        /* BREAK */
        $horaMarcado1B = NULL;
        if( !empty($arrMainArray[$key]['bloques']['break']) ){
          $horaMarcado1B = $arrMainArray[$key]['bloques']['break'][0]['hora'];
          //var_dump($horaMarcado1B); exit();
        }
        $horaMarcado2B = NULL;
        if( !empty($arrMainArray[$key]['bloques']['break']) && !empty($arrMainArray[$key]['bloques']['break'][1]) ){
          $horaMarcado2B = $arrMainArray[$key]['bloques']['break'][count($arrMainArray[$key]['bloques']['break']) - 1]['hora'];
        }
        $totalBreakB = NULL;
        if( !empty($horaMarcado1B) && !empty($horaMarcado2B) ){ 
          $totalBreakB = minutos_transcurridos($horaMarcado1B,$horaMarcado2B).' Min.';
        }
        $estadoPuntual = array(
          'simbolo'=> NULL,
          'diferencia'=> NULL
        );
        $estadoTardanza = array(
          'simbolo'=> NULL,
          'diferencia'=> NULL
        );
        // $estadoTardanza = array();
        $estadoFalta = NULL;
        if( !empty($horaMarcadoE) ){ 
          if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 1 ){
            $estadoPuntual['simbolo'] = 'X';
            $estadoPuntual['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
          }
          if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 2 || $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 3 ){
            $estadoTardanza['simbolo'] = 'X';
            $estadoTardanza['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
          }
        }
        /* VALIDAR SI ES FALTA */ 
        if( empty($estadoPuntual['simbolo']) && empty($estadoTardanza['simbolo']) ){ 
          /* ¿TIENE HORARIO EN ESTE DIA? */
          /* HORARIO ESPECIAL */
          $tieneHorarioEspecial = TRUE;
          $fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($fEmpleado['idempleado'],$row['fecha']); 
          if( empty($fHorarioEspecial) ){ 
            $tieneHorarioEspecial = FALSE; 
          }
          if($tieneHorarioEspecial === FALSE){ 
            /* HORARIO GENERAL */
            $tieneHorarioGeneral = TRUE;
            $arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'); 
            $diaSemana = date('w',strtotime("$rowFecha")); //var_dump($fEmpleado['idempleado']); exit(); 
            $fHorarioGeneral = $this->model_horario_general->m_obtener_horario_general_de_empleado($fEmpleado['idempleado'],$arrDiasSemana[$diaSemana]);
            if( empty($fHorarioGeneral) ){ 
              $tieneHorarioGeneral = FALSE; 
            }
          }
          if( $tieneHorarioEspecial || $tieneHorarioGeneral ){ 
            if( strtotime($rowFecha) < strtotime(date('Y-m-d')) ){
              $estadoFalta = 'X';
            }
          }
        }
        if( !empty($row['tiempo_tolerancia_maestra']) ){
          $row['tiempo_tolerancia_maestra'] = $row['tiempo_tolerancia_maestra'].' Min';
        }
        $horasTrabajadas = NULL;
        $horasExtra = NULL;
        if( !empty($arrMainArray[$key]['bloques']['entradas']) && !empty($arrMainArray[$key]['bloques']['salidas']) ){
          $dtHoraEntrada = $row['fecha'].' '.$horaMarcadoE;
          $dtHoraSalida = $row['fecha'].' '.$horaMarcadoS;
          $horasTrabajadas = timestampToHuman( strtotime($dtHoraSalida) - strtotime($dtHoraEntrada) );

          $horasTrabajadasTS = strtotime($dtHoraSalida) - strtotime($dtHoraEntrada);
          /* H. EXTRA = (FINAL - INICIAL) - 9 donde 9 es igual al número de horas trab(8) + 1hora de break */ 
          $horasExtra = timestampToHuman(strtotime('-9 hour' , $horasTrabajadasTS)); 
        }

        array_push($arrListadoProd, 
          array( 
            $row['fecha'],
            $strNombreDia,
            $row['hora_maestra_entrada'],
            $row['tiempo_tolerancia_maestra'],
            $horaMarcadoE,
            $horaMarcado1B,
            $horaMarcado2B,
            $totalBreakB,
            $row['hora_maestra_salida'],
            $horaMarcadoS,
            $estadoPuntual['simbolo'],
            $estadoPuntual['diferencia'],
            $estadoTardanza['simbolo'],
            $estadoTardanza['diferencia'],
            $estadoFalta,
            $row['motivo_fecha_especial'],
            $horasExtra
          )
        );
      }
      $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
      // DATOS
      $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
      $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$maxCol.$currentCellTotal)->applyFromArray($styleArrayProd);
      // $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':G'.($currentCellTotal+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':E'.($currentCellTotal))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':E'.($currentCellTotal))->getFill()->getStartColor()->setARGB('FFDDEBF7');
      $this->excel->getActiveSheet()->getStyle('J'.($currentCellEncabezado+1).':J'.($currentCellTotal))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      $this->excel->getActiveSheet()->getStyle('J'.($currentCellEncabezado+1).':J'.($currentCellTotal))->getFill()->getStartColor()->setARGB('FFDDEBF7');
      /*$this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.($currentCellTotal+1))->getNumberFormat()->setFormatCode('#,##0.00');*/
     
      $currentCellEncabezado = $currentCellTotal+6;
      //$cont++;
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
  public function report_ranking_empleado_asistencia($paramReport)
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'].' (20 primeros)',FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',11);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $fill = TRUE;
    //$this->pdf->Ln();
    if( !($allInputs['allEmpresas'] === TRUE) ){ 
      if( !empty($allInputs['empresa']['descripcion']) ){ 
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(50,5,'EMPRESA');
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(5,5,':');
        $this->pdf->Cell(0,5,$allInputs['empresa']['descripcion']);
        $this->pdf->Ln();
      }
    }
    $this->pdf->SetFont('Arial', 'B', 11);
    $this->pdf->Cell(50,4,'PERIODO'); 
    $this->pdf->SetFont('Arial', '', 11);
    $this->pdf->Cell(5,5,':');
    $this->pdf->Cell(0,5,strtoupper(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta'])) );

    $this->pdf->Ln(10);
    $this->pdf->SetAligns(array('C', 'C', 'C', 'L', 'L', 'L', 'R'));
    //$this->pdf->SetWidths(array(22, 26, 26, 70, 50, 56, 30));
    $this->pdf->SetWidths(array(22, 26, 26, 70, 50, 56, 30));
    $wDetalle = $this->pdf->GetWidths();
    $hDetalle = array(10, 10, 10, 10, 10, 10, 10);
    $headerDetalle = array('PUESTO', 'CANT.', 'TIEMPO', 'EMPLEADO', 'EMPRESA', 'CARGO','FOTO'); 
    $this->pdf->SetFont('Arial', 'B', 11);
    $this->pdf->SetFillColor(160);
    for($i=0;$i<count($headerDetalle);$i++){
      $this->pdf->Cell($wDetalle[$i],$hDetalle[$i],$headerDetalle[$i],1,0,'C',true);
    }
    $this->pdf->Ln();
    $this->pdf->SetFillColor(255);
    //$fill = TRUE;
    //$this->pdf->SetY(45);

    $listaRanking = $this->model_asistencia->m_cargar_empleados_ranking($allInputs,$paramReport);

    foreach ($listaRanking as $key => $row) { 
      $variable  = '';
      $heightCell = 12;
      $this->pdf->Row( 
          array(
            $key,
            $row['contador'],
            $row['suma_diferencia'],
            utf8_decode($row['empleado']).$variable,
            utf8_decode($row['empresa']),
            utf8_decode($row['cargo']),
            $row['nombre_foto']
          )
          ,$fill
          ,1
          ,array('B', '', '', '', '', '', '')
          ,$heightCell
          ,FALSE
          ,array('p2', 'p4', 'p4', '', '', '', '')
          ,array(FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE)
          ,TRUE
        );
      //$this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ranking_empleado_faltas()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    /*function fnOrdering($a, $b) { 
        return $a['fecha_timestamp'] - $b['fecha_timestamp'];
    }*/
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'].' (10 primeros)',FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',11);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $fill = TRUE;
    //$this->pdf->Ln();
    if( !($allInputs['allEmpresas'] === TRUE) ){ 
      if( !empty($allInputs['empresa']['descripcion']) ){ 
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(50,5,'EMPRESA');
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(5,5,':');
        $this->pdf->Cell(0,5,$allInputs['empresa']['descripcion']);
        $this->pdf->Ln();
      }
    }
    $this->pdf->SetFont('Arial', 'B', 11);
    $this->pdf->Cell(50,4,'PERIODO'); 
    $this->pdf->SetFont('Arial', '', 11);
    $this->pdf->Cell(5,5,':');
    $this->pdf->Cell(0,5,strtoupper(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta'])) );

    $this->pdf->Ln(10);
    $this->pdf->SetAligns(array('C', 'C', 'L', 'L', 'L', 'L', 'R'));
    // $this->pdf->SetWidths(array(22, 26, 26, 70, 50, 56, 30));
    $this->pdf->SetWidths(array(22, 26, 86, 50, 60, 22));
    $wDetalle = $this->pdf->GetWidths();
    $hDetalle = array(10, 10, 10, 10, 10, 10, 10);
    $headerDetalle = array('PUESTO', utf8_decode('N° FALTAS'), 'EMPLEADO', 'EMPRESA', 'CARGO','FOTO'); 
    $this->pdf->SetFont('Arial', 'B', 11);
   
    $this->pdf->SetFillColor(160);
    for($i=0;$i<count($headerDetalle);$i++){
      $this->pdf->Cell($wDetalle[$i],10,$headerDetalle[$i],1,0,'C',true);
    }
    $this->pdf->Ln();
    $this->pdf->SetFillColor(255);
    //$fill = TRUE;
    //$this->pdf->SetY(50);

    $arrMainReporte = array();
    if( !empty($allInputs['allEmpresas']) ){
      $allInputs['empresa'] = FALSE;
    }
    $listaEmpleados = $this->model_empleado->m_cargar_empleados_de_empresa_asistencia($allInputs['empresa']);
    foreach ($listaEmpleados as $key => $fEmpleado) { 
      $allInputs['empleado']['id'] = $fEmpleado['idempleado'];
      $lista = $this->model_asistencia->m_cargar_asistencias_de_empleado_reporte($allInputs);
      $arrEstructura = $this->creacionEstructuraAsistencia($lista,$allInputs);
      $contadorFaltas = 0;
      $arrMainReporte[$key] = array(
        'contador' => null,
        'empleado' => $fEmpleado['empleado'],
        'empresa' => $fEmpleado['empresa'],
        'cargo' => $fEmpleado['cargo'],
        'nombre_foto' => $fEmpleado['nombre_foto']
      );
      foreach ($arrEstructura as $keyDet => $row) {
        $rowFecha = $row['fecha'];
        /* PRIMERA ENTRADA  */
        $horaMarcadoE = NULL;
        if( !empty($arrEstructura[$keyDet]['bloques']['entradas']) ){
          $horaMarcadoE = $arrEstructura[$keyDet]['bloques']['entradas'][0]['hora'];
        }
        $estadoPuntual = array(
          'simbolo'=> NULL
        );
        $estadoTardanza = array(
          'simbolo'=> NULL
        );
        
        if( !empty($horaMarcadoE) ){ 
          if( $arrEstructura[$keyDet]['bloques']['entradas'][0]['numEstado'] == 1 ){
            $estadoPuntual['simbolo'] = 'X';
          }
          if( $arrEstructura[$keyDet]['bloques']['entradas'][0]['numEstado'] == 2 || $arrEstructura[$keyDet]['bloques']['entradas'][0]['numEstado'] == 3 ){
            $estadoTardanza['simbolo'] = 'X';
          }
        }
        /* VALIDAR SI ES FALTA */ 
        if( empty($estadoPuntual['simbolo']) && empty($estadoTardanza['simbolo']) ){ 
          /* ¿TIENE HORARIO EN ESTE DIA? */
          /* HORARIO ESPECIAL */
          $tieneHorarioEspecial = TRUE;
          $fHorarioEspecial = $this->model_horario_especial->m_obtener_horario_especial_de_empleado($fEmpleado['idempleado'],$row['fecha']); 
          if( empty($fHorarioEspecial) ){ 
            $tieneHorarioEspecial = FALSE; 
          }
          if($tieneHorarioEspecial === FALSE){ 
            /* HORARIO GENERAL */
            $tieneHorarioGeneral = TRUE;
            $arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'); 
            $diaSemana = date('w',strtotime("$rowFecha")); //var_dump($fEmpleado['idempleado']); exit(); 
            $fHorarioGeneral = $this->model_horario_general->m_obtener_horario_general_de_empleado($fEmpleado['idempleado'],$arrDiasSemana[$diaSemana]);
            if( empty($fHorarioGeneral) ){ 
              $tieneHorarioGeneral = FALSE; 
            }
          }
          if( $tieneHorarioEspecial || $tieneHorarioGeneral ){ 
            if( strtotime($rowFecha) < strtotime(date('Y-m-d')) ){
              $contadorFaltas +=1;
            }
          }
        }
      }
      $arrMainReporte[$key]['contador'] = $contadorFaltas;
    }
    function fnOrderingMR($a, $b) { 
        return $b['contador'] - $a['contador'];
    }
    usort($arrMainReporte,'fnOrderingMR'); 
    foreach ($arrMainReporte as $key => $row) {
      if( $key > 9 ){
        unset($arrMainReporte[$key]);
      }
    }
    foreach ($arrMainReporte as $key => $row) { 
      $heightCell = 12;
      $this->pdf->Row( 
          array(
            $key,
            $row['contador'],
            utf8_decode($row['empleado']),
            utf8_decode($row['empresa']),
            utf8_decode($row['cargo']),
            $row['nombre_foto']
          )
          ,$fill
          ,1
          ,array('B', '', '', '', '', '')
          ,$heightCell
          ,FALSE
          ,array('p2', 'p4', '', '', '', '')
          ,array(FALSE, FALSE, FALSE, FALSE, FALSE, TRUE)
          ,TRUE
          ,8
        );
      //$this->pdf->Ln();
    }

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_ranking_empleado_horas_extra()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'].' (20 primeros)',FALSE,$allInputs['tituloAbv']);
    //$this->pdf->SetFont('Arial','',11);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    $fill = TRUE;
    //$this->pdf->Ln();
    if( !($allInputs['allEmpresas'] === TRUE) ){ 
      if( !empty($allInputs['empresa']['descripcion']) ){ 
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(50,5,'EMPRESA');
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(5,5,':');
        $this->pdf->Cell(0,5,$allInputs['empresa']['descripcion']);
        $this->pdf->Ln();
      }
    }
    $this->pdf->SetFont('Arial', 'B', 11);
    $this->pdf->Cell(50,4,'PERIODO'); 
    $this->pdf->SetFont('Arial', '', 11);
    $this->pdf->Cell(5,5,':');
    $this->pdf->Cell(0,5,strtoupper(darFormatoFecha($allInputs['desde']).' - '.darFormatoFecha($allInputs['hasta'])) );

    $this->pdf->Ln(10);
    $this->pdf->SetAligns(array('C', 'C', 'L', 'L', 'L', 'R'));
    //$this->pdf->SetWidths(array(22, 26, 26, 70, 50, 56, 30));
    $this->pdf->SetWidths(array(22, 26, 70, 50, 56, 30));
    $wDetalle = $this->pdf->GetWidths();
    $hDetalle = array(10, 10, 10, 10, 10, 10);
    $headerDetalle = array('PUESTO', 'TIEMPO', 'EMPLEADO', 'EMPRESA', 'CARGO','FOTO'); 
    $this->pdf->SetFont('Arial', 'B', 11);
    $this->pdf->SetFillColor(160);
    for($i=0;$i<count($headerDetalle);$i++){
      $this->pdf->Cell($wDetalle[$i],$hDetalle[$i],$headerDetalle[$i],1,0,'C',true);
    }
    $this->pdf->Ln();
    $this->pdf->SetFillColor(255);
    $listaRanking = $this->model_asistencia->m_cargar_empleados_ranking_horas_extra($allInputs);
    foreach ($listaRanking as $key => $row) { 
      $variable  = '';
      $heightCell = 12;
      $this->pdf->Row( 
          array(
            $key,
            $row['suma_diferencia'],
            utf8_decode($row['empleado']).$variable,
            utf8_decode($row['empresa']),
            utf8_decode($row['cargo']),
            $row['nombre_foto']
          )
          ,$fill
          ,1
          ,array('B', '', '', '', '', '', '')
          ,$heightCell
          ,FALSE
          ,array('p2', 'p4', 'p4', '', '', '', '')
          ,array(FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE)
          ,TRUE
        );
      //$this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleados_por_distrito()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'DEPARTAMENTO');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(40,4,utf8_decode($allInputs['departamento']));
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'PROVINCIA: ');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['provincia']));
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'DISTRITO(S): '); 
    $this->pdf->Ln();
    $allInputs['arrUbigeosSeleccionado'] = array();
    foreach ($allInputs['distritosSeleccionados'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',11);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrUbigeosSeleccionado'][] = $row['idubigeo'];
    }
    
    /* TRATAMIENTO DE DATOS */
    $allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empleado->m_cargar_empleados_reporte_distrito($allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array(
        'idubigeo'=> $row['idubigeo'],
        'departamento'=> $row['departamento'],
        'provincia'=> $row['provincia'],
        'distrito'=> $row['distrito'],
        'colaboradores'=> array()
      );
      $arrMainArray[$row['idubigeo']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idempleado'=> $row['idempleado'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'nombre_foto'=> $row['nombre_foto'],
        'fecha_nacimiento'=> $row['fecha_nacimiento'],
        'salario_basico'=> $row['salario_basico'],
        'descripcion_prf'=> $row['descripcion_prf'] 
      );
      $arrMainArray[$row['idubigeo']]['colaboradores'][$row['idempleado']] = $rowAux;
    }

    /* CREACION DEL PDF */ 
    $headerDetalle = array('DNI', 'EMPLEADO', 'CARGO', 'EMPRESA', 'FECHA NAC.', 'PROFESION', 'SALARIO'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('C', 'L', 'L', 'L', 'C', 'L', 'R'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['departamento'].' - '.$rowPrin['provincia'].' - '.$rowPrin['distrito'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');

      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array($row['numero_documento'],
            utf8_decode(strtoupper($row['empleado'])),
            utf8_decode(strtoupper($row['cargo'])),
            utf8_decode(strtoupper($row['empresa'])),
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper($row['descripcion_prf'])),
            $row['salario_basico'])
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleados_por_profesion()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11); 
    $this->pdf->Cell(40,4,'PROFESION(ES): '); 
    $this->pdf->Ln();
    $allInputs['arrProfesionesSeleccionadas'] = array();
    foreach ($allInputs['profesionesSeleccionadas'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrProfesionesSeleccionadas'][] = $row['id'];
    }
    
    /* TRATAMIENTO DE DATOS */
    $allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empleado->m_cargar_empleados_reporte_profesion($allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array(
        'idprofesion'=> $row['idprofesion'],
        'descripcion_prf'=> $row['descripcion_prf'],
        'colaboradores'=> array()
      );
      $arrMainArray[$row['idprofesion']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idempleado'=> $row['idempleado'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'nombre_foto'=> $row['nombre_foto'],
        'fecha_nacimiento'=> $row['fecha_nacimiento'],
        'salario_basico'=> $row['salario_basico'],
        'descripcion_prf'=> $row['descripcion_prf'] 
      );
      $arrMainArray[$row['idprofesion']]['colaboradores'][$row['idempleado']] = $rowAux;
    }

    /* CREACION DEL PDF */ 
    $headerDetalle = array('DNI', 'EMPLEADO', 'CARGO', 'EMPRESA', 'FECHA NAC.', 'PROFESION', 'SALARIO'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('C', 'L', 'L', 'L', 'C', 'L', 'R'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['descripcion_prf'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array($row['numero_documento'],
            utf8_decode(strtoupper($row['empleado'])),
            utf8_decode(strtoupper($row['cargo'])),
            utf8_decode(strtoupper($row['empresa'])),
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper($row['descripcion_prf'])),
            $row['salario_basico'])
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_medicos_por_especialidad_rne()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11); 
    $this->pdf->Cell(40,4,'ESPECIALIDAD(ES)'); 
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->Ln();
    $allInputs['arrEspecialidadesSeleccionadas'] = array();
    foreach ($allInputs['especialidadesSeleccionadas'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrEspecialidadesSeleccionadas'][] = $row['id'];
    }
    
    /* TRATAMIENTO DE DATOS */
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

    /* CREACION DEL PDF */ 
    $headerDetalle = array('DNI', 'RNE', 'EMPLEADO', 'CARGO', 'EMPRESA', 'FECHA NAC.', 'PROFESION'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('C', 'C', 'L', 'L', 'L', 'C', 'L'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['especialidad'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(18, 15, 56, 56, 60, 20, 52));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array($row['numero_documento'],
            $row['reg_nac_especialista'],
            utf8_decode(strtoupper($row['empleado'])),
            utf8_decode(strtoupper($row['cargo'])),
            utf8_decode(strtoupper($row['empresa'])),
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper($row['descripcion_prf']))
          )
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(18, 15, 56, 56, 60, 20, 52));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleados_por_empresa_tercero()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11); 
    $this->pdf->Cell(40,4,'EMPRESA(S)'); 
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->Ln();
    $allInputs['arrEmpresasSeleccionadas'] = array();
    foreach ($allInputs['empresasSeleccionadas'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrEmpresasSeleccionadas'][] = $row['id'];
    }
    
    /* TRATAMIENTO DE DATOS */
    //$allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empleado->m_cargar_empleados_reporte_empresa_tercero($allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array(
        'idempresa'=> $row['idempresa'],
        'empresa'=> $row['empresa'],
        'colaboradores'=> array()
      );
      $arrMainArray[$row['idempresa']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idempleado'=> $row['idempleado'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'nombre_foto'=> $row['nombre_foto'],
        'fecha_nacimiento'=> $row['fecha_nacimiento'],
        'salario_basico'=> $row['salario_basico'],
        'descripcion_prf'=> $row['descripcion_prf'] 
      );
      $arrMainArray[$row['idempresa']]['colaboradores'][$row['idempleado']] = $rowAux;
    }

    /* CREACION DEL PDF */ 
    $headerDetalle = array('DNI', 'EMPLEADO', 'CARGO', 'EMPRESA', 'FECHA NAC.', 'PROFESION', 'SALARIO'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('C', 'L', 'L', 'L', 'C', 'L', 'R'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['empresa'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array($row['numero_documento'],
            utf8_decode(strtoupper($row['empleado'])),
            utf8_decode(strtoupper($row['cargo'])),
            utf8_decode(strtoupper($row['empresa'])),
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper($row['descripcion_prf'])),
            $row['salario_basico'])
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleados_por_tipo_contrato()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11); 
    $this->pdf->Cell(40,4,'TIPO DE CONTRATO'); 
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->Ln();
    $allInputs['arrTipoContratosSeleccionadas'] = array();
    foreach ($allInputs['tipoContratosSeleccionadas'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrTipoContratosSeleccionadas'][] = $row['id'];
    }
    
    /* TRATAMIENTO DE DATOS */
    //$allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista = $this->model_empleado->m_cargar_empleados_reporte_tipo_contrato($allInputs);
    $arrMainArray = array();
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'condicion_laboral'=> $row['condicion_laboral'],
        'colaboradores'=> array()
      );
      $arrMainArray[$row['condicion_laboral']] = $rowAux;
    }
    foreach ($lista as $key => $row) {
      $rowAux = array( 
        'idempleado'=> $row['idempleado'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'nombre_foto'=> $row['nombre_foto'],
        'fecha_nacimiento'=> $row['fecha_nacimiento'],
        'salario_basico'=> $row['salario_basico'],
        'descripcion_prf'=> $row['descripcion_prf'] 
      );
      $arrMainArray[$row['condicion_laboral']]['colaboradores'][$row['idempleado']] = $rowAux;
    }

    /* CREACION DEL PDF */ 
    $headerDetalle = array('DNI', 'EMPLEADO', 'CARGO', 'EMPRESA', 'FECHA NAC.', 'PROFESION', 'SALARIO'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('C', 'L', 'L', 'L', 'C', 'L', 'R'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['condicion_laboral'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['colaboradores'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array($row['numero_documento'],
            utf8_decode(strtoupper($row['empleado'])),
            utf8_decode(strtoupper($row['cargo'])),
            utf8_decode(strtoupper($row['empresa'])),
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper($row['descripcion_prf'])),
            $row['salario_basico'])
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(18, 56, 50, 60, 24, 50, 18));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleados_por_rango_edad()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(40,4,'SEDE');
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,4,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','B',11); 
    $this->pdf->Cell(40,4,'RANGO DE EDADES'); 
    $this->pdf->Cell(2,5,':'); 
    $this->pdf->Ln();
    $allInputs['arrRangoEdadSeleccionadas'] = array();
    foreach ($allInputs['rangoEdadSeleccionadas'] as $key => $row) { 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
      $this->pdf->Ln();
      $allInputs['arrRangoEdadSeleccionadas'][] = $row['id'];
    }
    
    /* TRATAMIENTO DE DATOS */
    //$allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit();
    $lista0a5 = array();
    $lista5a10 = array();
    $lista10a15 = array();
    $lista15a18 = array();

    if( in_array('0-5',$allInputs['arrRangoEdadSeleccionadas']) ){ 
      $allInputs['edadInicio'] = 0;
      $allInputs['edadFin'] = 5;
      $lista0a5 = $this->model_empleado->m_cargar_empleados_reporte_rango_edad($allInputs,'5');
      // var_dump('05');
    }
      
    if( in_array('5-10',$allInputs['arrRangoEdadSeleccionadas']) ){ 
      $allInputs['edadInicio'] = 5;
      $allInputs['edadFin'] = 10;
      $lista5a10 = $this->model_empleado->m_cargar_empleados_reporte_rango_edad($allInputs,'510');
      // var_dump('510');
    }
    if( in_array('10-15',$allInputs['arrRangoEdadSeleccionadas']) ){ 
      $allInputs['edadInicio'] = 10;
      $allInputs['edadFin'] = 15;
      $lista10a15 = $this->model_empleado->m_cargar_empleados_reporte_rango_edad($allInputs,'1015'); 
      // var_dump('1015');
    }
    if( in_array('15-18',$allInputs['arrRangoEdadSeleccionadas']) ){ 
      $allInputs['edadInicio'] = 15;
      $allInputs['edadFin'] = 18;
      $lista15a18 = $this->model_empleado->m_cargar_empleados_reporte_rango_edad($allInputs,'1518');
      // var_dump('1518');
    }
    // exit();
    //var_dump($lista0a5,$lista5a10,$lista10a15,$lista15a18); exit();
    $lista = array_merge($lista0a5,$lista5a10,$lista10a15,$lista15a18);
    //$lista2 = $lista0a5+$lista5a10+$lista10a15+$lista15a18;
    // var_dump("<pre>",$lista); exit();
    $arrMainArray = array();
    foreach ($lista as $key => $row) { 
      $strRangoEdad = '';
      if( $row['rango_edad'] == '5' ){
        $strRangoEdad = 'DE 0 A 5 AÑOS';
      }
      if( $row['rango_edad'] == '510' ){ 
        $strRangoEdad = 'DE 5 A 10 AÑOS';
      }
      if( $row['rango_edad'] == '1015' ){
        $strRangoEdad = 'DE 10 A 15 AÑOS';
      }
      if( $row['rango_edad'] == '1518' ){
        $strRangoEdad = 'DE 15 A 18 AÑOS';
      }
      $rowAux = array( 
        'rango_edad'=> $row['rango_edad'],
        'descripcion'=> $strRangoEdad,
        'hijos'=> array()
      );
      $arrMainArray[$row['rango_edad']] = $rowAux;
    }
    foreach ($lista as $key => $row) { 
      $rowAux = array( 
        'idpariente'=> $row['idpariente'],
        'empleado'=> $row['empleado'],
        'numero_documento'=> $row['numero_documento'],
        'cargo'=> $row['cargo'],
        'empresa'=> $row['empresa'],
        'fecha_nacimiento'=> $row['fecha_nacimiento'],
        'hijo'=> $row['hijo'],
        'edad_hijo'=> $row['edad_hijo'],
        'descripcion_prf'=> $row['descripcion_prf'] 
      );
      $arrMainArray[$row['rango_edad']]['hijos'][$row['idpariente']] = $rowAux;
    }

    /* CREACION DEL PDF */ 
    $headerDetalle = array('HIJO DEL COLABORADOR', 'EDAD', 'FECHA NAC.', 'COLABORADOR'); 
    $this->pdf->Ln(1);
    
    foreach ($arrMainArray as $keyPrin => $rowPrin) { 
      $this->pdf->SetAligns(array('L', 'C', 'C', 'L'));
      $this->pdf->Ln(6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->SetFillColor(214,225,242);
      $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['descripcion'])),'',0,'C',TRUE);
      $this->pdf->Ln(7);
      $contador = 0; 
      $this->pdf->SetWidths(array(80, 40, 60, 80));
      $wDetalle = $this->pdf->GetWidths();
      $this->pdf->SetFont('Arial','B',8);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
      $this->pdf->Ln();
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      foreach ($rowPrin['hijos'] as $keyAte => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row( 
          array(
            utf8_decode(strtoupper($row['hijo'])),
            $row['edad_hijo'],
            $row['fecha_nacimiento'],
            utf8_decode(strtoupper( $row['empleado'])) 
          )
          ,$fill
        );
        $fill = !$fill;
        $contador++;
      } 
      $this->pdf->SetWidths(array(80, 40, 60, 80));
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
      $this->pdf->Cell(20,5,$contador,0,0,'C');
      $this->pdf->Ln();
    }
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function receta_atencion_medica()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    // RECUPERACION DE DATOS
    $receta = $this->model_receta_medica->m_cargar_receta_medica_para_imprimir($allInputs['resultado']);
    $detalle = $this->model_receta_medica->m_cargar_detalle_receta_medica_para_imprimir($allInputs['resultado']);
    //var_dump($detalle); exit();
    // ------ CABECERA - LADO IZQUIERDO -------
    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(87,5,utf8_decode('RECETA - CONSULTA EXTERNA')); 
    $this->pdf->Cell(15,5,utf8_decode('Nº')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(30,5,utf8_decode($receta['idreceta']),0,0);
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(87,5,utf8_decode('VILLA SALUD - VILLA EL SALVADOR')); 
    $this->pdf->Cell(15,5,utf8_decode('Fecha')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(30,5,formatoFechaReporte3($receta['fecha_receta']),0);

    $this->pdf->Ln(4);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);

    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Paciente'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(110,5,utf8_decode($receta['paciente']),0,0);
    $this->pdf->Ln();

    //$this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Doc. Id'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(63,5,utf8_decode($receta['num_documento']));
    $this->pdf->Cell(27,5,utf8_decode('Historia Clínica')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(18,5,($receta['idhistoria']),0);
    $this->pdf->Ln(4);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);

    //$this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Servicio'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(63,5,utf8_decode($receta['especialidad']),0);
    $this->pdf->Cell(27,5,utf8_decode('Acto Médico')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(18,5,($receta['idatencionmedica']),0);
    $this->pdf->Ln();

    $this->pdf->Cell(22,5,utf8_decode('Prof. Médico')); 
    $this->pdf->Cell(2,5,':');
    //$this->pdf->Cell(10,5,utf8_decode($receta['idmedico']),1);
    $this->pdf->Cell(110,5,utf8_decode($receta['medico']),0);

    $this->pdf->Ln(4);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $this->pdf->Cell(134,3,'Medicamentos',0,0,'C');
    $this->pdf->Ln();
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    // ------ DETALLE - LADO IZQUIERDO -------
    $item = 1;
    foreach ($detalle as $row) {
      $this->pdf->Cell(6,5, ($item++) . '.- ');
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(22,5, utf8_decode($row['denominacion']));
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(6,5, ' ');
      $this->pdf->Cell(22,5, utf8_decode('Código: ') . $row['idmedicamento']);
      $this->pdf->Ln();
      $this->pdf->Cell(6,5, ' ');
      $this->pdf->Cell(22,5, utf8_decode('Cantidad: ') . $row['cantidad'] . ' Unds.');
      $this->pdf->Ln();
    }
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(22,5, 'Indicaciones Generales');
    $this->pdf->Ln();
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(6,5, utf8_decode($receta['indicaciones_generales']));
    $this->pdf->Ln(4);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $this->pdf->Cell(134,3,'*** NO PERDER SU RECETA ***',0,0,'C');
    $this->pdf->Ln(20);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(130,3,utf8_decode('Firma/Médico'),0,0,'R');

    // ------ CABECERA - LADO DERECHO -------
    $this->pdf->SetXY(152,25);
    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(87,5,utf8_decode('RECETA - CONSULTA EXTERNA')); 
    $this->pdf->Cell(15,5,utf8_decode('Nº')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(30,5,utf8_decode($receta['idreceta']),0,0);
    $this->pdf->Ln();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);

    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(87,5,utf8_decode('VILLA SALUD - VILLA EL SALVADOR')); 
    $this->pdf->Cell(15,5,utf8_decode('Fecha')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(30,5,formatoFechaReporte3($receta['fecha_receta']));
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);

    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);

    $this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Paciente'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(110,5,utf8_decode($receta['paciente']),0,0);
    $this->pdf->Ln();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);

    //$this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Doc. Id'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(63,5,utf8_decode($receta['num_documento']));
    $this->pdf->Cell(27,5,utf8_decode('Historia Clínica')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(18,5,($receta['idhistoria']),0);
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);

    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    //$this->pdf->SetFont('Arial','',10); 
    $this->pdf->Cell(22,5,'Servicio'); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(63,5,utf8_decode($receta['especialidad']),0);
    $this->pdf->Cell(27,5,utf8_decode('Acto Médico')); 
    $this->pdf->Cell(2,5,':');
    $this->pdf->Cell(18,5,($receta['idatencionmedica']),0);
    $this->pdf->Ln();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);

    $this->pdf->Cell(22,5,utf8_decode('Prof. Médico')); 
    $this->pdf->Cell(2,5,':');
    //$this->pdf->Cell(10,5,utf8_decode($receta['idmedico']),1);
    $this->pdf->Cell(110,5,utf8_decode($receta['medico']),0);

    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,'Indicaciones',0,0,'C');
    $this->pdf->Ln();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    
    // ------ DETALLE - LADO DERECHO -------
    $item = 1;
    foreach ($detalle as $row) {
      $this->pdf->Cell(6,5, ($item++) . '.- ');
      $this->pdf->SetFont('Arial','B',10);
      $this->pdf->Cell(22,5, utf8_decode($row['denominacion']));
      $this->pdf->Ln();
      $y=$this->pdf->GetY();
      $this->pdf->SetXY(152,$y);
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(6,5, ' ');
      $this->pdf->Cell(22,5, 'Cantidad: ' . $row['cantidad'] . ' Unds.');
      $this->pdf->Ln();
      $y=$this->pdf->GetY();
      $this->pdf->SetXY(152,$y);
      $this->pdf->Cell(6,5, ' ');
      $this->pdf->Cell(22,5, utf8_decode($row['indicaciones']));
      $this->pdf->Ln();
      $y=$this->pdf->GetY();
      $this->pdf->SetXY(152,$y);
    }
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(22,5, 'Indicaciones Generales');
    $this->pdf->Ln();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(6,5, utf8_decode($receta['indicaciones_generales']));
    $this->pdf->Ln(4);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,'*** NO PERDER SU RECETA ***',0,0,'C');
    $this->pdf->Ln(20);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->Cell(134,3,' ','B'); // ---------
    $this->pdf->Ln(6);
    $y=$this->pdf->GetY();
    $this->pdf->SetXY(152,$y);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(130,3,utf8_decode('Firma/Médico'),0,0,'R');

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_tarifario_sede(){
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
        
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $lista = $this->model_producto->m_cargar_productos($paramPaginate,$paramDatos);
        $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($paramDatos['sedeempresa']);
        // var_dump($empresa_admin); exit();

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresa_admin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresa_admin['sede']));
        $this->pdf->Ln(4);

        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);

        //$this->pdf->Cell(10,8,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(8,8,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell(80,8,utf8_decode('PRODUCTO'),1,0,'L',TRUE);
        $this->pdf->Cell(15,8,utf8_decode('PRECIO'),1,0,'L',TRUE);
        $this->pdf->Cell(45,8,utf8_decode('ESPECIALIDAD'),1,0,'L',TRUE);
        $this->pdf->Cell(30,8,utf8_decode('TIPO PRODUCTO'),1,0,'L',TRUE);

        /*$this->pdf->MultiCell(18,4,'FECHA VENCIMTO.',1,'C',TRUE);

        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+173,$y-8);*/
        $this->pdf->Cell(12,8,utf8_decode('ESTADO'),1,0,'C',TRUE);
        $this->pdf->Ln(8);

        $this->pdf->SetFont('Arial','',12);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths(array(8,80,15,45,30,12));
        $this->pdf->SetAligns(array('L', 'L', 'R','L','L','C'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        foreach ($lista as $row) {
            // 1: VENCIDO
            // 2: MES ACTUAL
            // 3: 2 MESES
            if( $row['estado_pps'] == 1 ){
              $estado = 'HAB';
            }
            if( $row['estado_pps'] == 2 ){
              $estado = 'DESHAB';
            }
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    //$item,
                    utf8_decode(trim($row['idproductomaster'])),
                    utf8_decode(trim($row['producto'])),
                    $row['precio'],
                    utf8_decode(trim($row['especialidad'])),
                    utf8_decode(trim($row['nombre_tp'])),
                    $estado
                ),
                $fill,1,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,6
            );
            $item++;
        }
       

        $arrData['message'] = 'ERROR';
        $arrData['flag'] = 2;
        $timestamp = date('YmdHis');
        if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
          $arrData['message'] = 'OK';
          $arrData['flag'] = 1;
        }
        $arrData = array(
          'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
        );
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($arrData));
  }
  public function report_solicitudes_medico()
  {
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,'SEDE');
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();
    // $this->pdf->SetFont('Arial','B',11);
    // $this->pdf->Cell(35,5,'ESPECIALIDAD');
    // $this->pdf->Cell(3,5,':'); 
    // $this->pdf->SetFont('Arial','',10);
    // $this->pdf->Cell(40,5,utf8_decode($allInputs['especialidad']['descripcion'])); 
    // $this->pdf->Ln();
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('DESDE '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,$allInputs['desde'] . ' ' . $allInputs['desdeHora'] .':'. $allInputs['desdeMinuto'] ); 
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('HASTA '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,$allInputs['hasta'] . ' ' . $allInputs['hastaHora'] .':'. $allInputs['hastaMinuto'] ); 
      $this->pdf->Ln(8);
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('MES / AÑO '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode(strtoupper_total($allInputs['mes']['mes'])) . ' / ' . $allInputs['anioDesdeCbo'] ); 
      $this->pdf->Ln(8);
    }
      
    
    /* TRATAMIENTO DE DATOS */ 
    //$allInputs['reporte'] = TRUE; 
    $allInputs['arrMedicos'] = array(); 
    // $allInputs['arrMedicos'] = ''; 
    if( empty($allInputs['mostrarTodasSolicitudes']) ){ 
      if($allInputs['medico']['idmedico'] == 'ALL'){ // si no se seleccionó un medico, debemos obtener el listado en un array para enviarlo al modelo
        $contador = count($allInputs['listaMedicos']);
        foreach ($allInputs['listaMedicos'] as $key => $row) {
          if($row['idmedico'] != 'ALL' ){
            $allInputs['arrMedicos'][] = $row['idmedico'];
          }
        }
        $allInputs['arrMedicos'] = implode(",", $allInputs['arrMedicos']);
      }
    }
    
    $lista = $this->model_empleado->m_cargar_solicitudes_medico_venta($allInputs);
    $arrTPAux = array();
    $arrPrincipal = array();
    $arrListado = array();
    $cantidad_total = 0;
    foreach ($lista as $row) {
      $arrTPAux = array(
        'idtipoproducto' => $row['idtipoproducto'],
        'tipo_producto' => $row['tipo_producto'],
        'cantidadtotal' => 0,
        'listado' => array(),
      );
      $arrPrincipal[$row['idtipoproducto']] = $arrTPAux;
    }
    foreach ($arrPrincipal as $key1 => $row1) {
      $arrListado = array();
      foreach ($lista as $key2 => $row2) {
        if( $row2['idtipoproducto'] == $key1 ){
          array_push($arrListado, array(
            'especialidades' => $row2['especialidades'],
            'medico' => $row2['medico'],
            'producto' => $row2['producto'],
            'cantidad' => $row2['cantidad'],
            'monto' => $row2['monto'],
            )
          );
          $arrPrincipal[$key1]['listado'] = $arrListado;
        }
      }
    }
    // var_dump($arrPrincipal); exit();
    /* CREACION DEL PDF */
    foreach ($arrPrincipal as $rowPr) {
      $this->pdf->Cell(40,5,$rowPr['tipo_producto']);
      $this->pdf->Ln();
      $headerDetalle = array('ITEM', 'ESPECIALIDADES', 'MEDICO', 'PRODUCTO SOLICITADO', 'CANT.', 'MONTO'); 
      $this->pdf->Ln(1);
      // $this->pdf->SetWidths(array(10,90,100,50,20));
      // $wDetalle = $this->pdf->GetWidths();
      $wDetalle = array(10,85,65,90,13,16);// 270
      $this->pdf->SetFont('Arial','B',10);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
        
      $this->pdf->Ln();
      $this->pdf->SetWidths($wDetalle);
      $this->pdf->SetAligns(array('R', 'L', 'L', 'L','C','R'));
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      $i = 1;
      $cantidadTotal = 0;
      $montoTotal = 0;
      foreach ($rowPr['listado'] as $key => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row(
          array(
            $i++,
            utf8_decode(strtoupper($row['especialidades'])),
            utf8_decode(strtoupper($row['medico'])),
            utf8_decode(strtoupper($row['producto'])),
            $row['cantidad'],
            $row['monto']
          )
          ,$fill
        );
        $fill = !$fill;
        $cantidadTotal += $row['cantidad'];
        $montoTotal += $row['monto'];
      }
      $alinear = 'R';
      $width = $this->pdf->GetStringWidth($montoTotal);
      $this->pdf->Ln(5);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(220-$width,6,'');
      $this->pdf->Cell(28,6,'CANT. TOTAL',0,0,'R');
      $this->pdf->Cell(3,6,':',0,0,'C');
      $this->pdf->SetFont('Arial','',12);
      $this->pdf->Cell(round($width+20),6,$cantidadTotal,0,0,$alinear);
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(220-$width,6,'');
      $this->pdf->Cell(28,6,'MONTO TOTAL',0,0,'R');
      $this->pdf->Cell(3,6,':',0,0,'C');
      $this->pdf->SetFont('Arial','',12);
      $this->pdf->Cell(round($width+20),6,'S/. '.number_format($montoTotal,2),0,0,$alinear);
      $this->pdf->Ln();
    }

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_solicitudes_medico_excel(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // TRATAMIENTO DE DATOS
      $allInputs['arrMedicos'] = '';
      if( empty($allInputs['mostrarTodasSolicitudes']) ) { 
        if($allInputs['medico']['idmedico'] == 'ALL'){ // si no se seleccionó un medico, debemos obtener el listado en un array para enviarlo al modelo
          $contador = count($allInputs['listaMedicos']);
          foreach ($allInputs['listaMedicos'] as $key => $row) {
            if($row['idmedico'] != 'ALL' ){
              if( $key < ($contador - 1) ){
                $allInputs['arrMedicos'] .= $row['idmedico'] . ', ';
              }else{
                $allInputs['arrMedicos'] .= $row['idmedico'];
              }
            }
          }
        }
      } 
      
      $lista = $this->model_empleado->m_cargar_solicitudes_medico_venta($allInputs);
      // var_dump($lista); exit();
      $arrListadoProd = array();
      $monto_total = 0;
      $cantidad_total = 0;
      $i = 1;
      foreach ($lista as $row) { 
        array_push($arrListadoProd, 
          array(
            $i++,
            strtoupper_total($row['especialidades']),
            strtoupper_total($row['medico']),
            strtoupper_total($row['producto']),
            strtoupper_total($row['tipo_producto']),
            $row['cantidad'],
            $row['monto']
          )
        );
        $monto_total += $row['monto'];
        $cantidad_total += ($row['cantidad']);
      }
    // SETEO DE VARIABLES
    $dataColumnsTP = array( 
      'ITEM', 'ESPECIALIDADES', 'MEDICO', 'PRODUCTO SOLICITADO', 'TIPO PRODUCTO', 'CANT.', 'MONTO'
    );
    $endColum = 'G';
    $this->excel->setActiveSheetIndex(0);
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']);
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->excel->getActiveSheet()->setTitle($allInputs['mes']['mes'].' - '.$allInputs['anioDesdeCbo']);
    }
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(10,40,40,55,35,15,20);
    $i = 0;
    foreach(range('A',$endColum) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
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
    $this->excel->getActiveSheet()->getStyle('B3:B7')->applyFromArray($styleArrayEncabezado);

    $this->excel->getActiveSheet()->getCell('B3')->setValue('SEDE:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['sede']['descripcion']);
    
    $this->excel->getActiveSheet()->getCell('B4')->setValue('ESPECIALIDAD:');
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['especialidad']['descripcion']);
    
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->excel->getActiveSheet()->getCell('B5')->setValue('DESDE:');
      $this->excel->getActiveSheet()->getCell('C5')->setValue( $allInputs['desde'] . ' | ' . $allInputs['desdeHora'] .':'. $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getCell('B6')->setValue('HASTA:');
      $this->excel->getActiveSheet()->getCell('C6')->setValue( $allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] .':'. $allInputs['hastaMinuto'] );
      $currentCellEncabezado = 8; // donde inicia el encabezado del listado
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->excel->getActiveSheet()->getCell('B5')->setValue('MES / AÑO:');
      $this->excel->getActiveSheet()->getCell('C5')->setValue( $allInputs['mes']['mes'].' / '.$allInputs['anioDesdeCbo']  );
      $currentCellEncabezado = 7; // donde inicia el encabezado del listado
    }
    // ENCABEZADO DE LA LISTA
    // $currentCellEncabezado = 7; // donde inicia el encabezado del listado
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':E'.$currentCellEncabezado);
    // LISTADO
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellEncabezado+1).':'.$endColum .($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('E'.($currentCellEncabezado+1).':E' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


    // TOTAL
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2)  )->applyFromArray($styleArrayTotales);
    $this->excel->getActiveSheet()->getCell('F'.($currentCellTotal+2) )->setValue('TOTAL');
    $this->excel->getActiveSheet()->getStyle('G'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2) )->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
    $this->excel->getActiveSheet()->getCell('G'.($currentCellTotal+2))->setValue('=SUM(G'.($currentCellEncabezado+1) .':G'.($currentCellTotal+1).')');
    // $this->excel->getActiveSheet()->getCell('L'.($currentCellTotal+2))->setValue('=SUM(L'.($currentCellEncabezado+1) .':L'.($currentCellTotal+1).')');

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
  public function report_solicitudes_paciente_externo()
  {
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024);

    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    //var_dump($allInputs) ; exit();
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,'SEDE');
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();
    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,'ESPECIALIDAD');
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode($allInputs['especialidad']['descripcion'])); 
    $this->pdf->Ln();
    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,utf8_decode('MES / AÑO '));
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode(strtoupper_total($allInputs['mes']['mes'])) . ' / ' . $allInputs['anioDesdeCbo'] ); 
    $this->pdf->Ln(8);
    
    /* TRATAMIENTO DE DATOS */
    //$allInputs['reporte'] = TRUE;
    $allInputs['arrMedicos'] = '';
    if($allInputs['medico']['idmedico'] == 'ALL'){ // si no se seleccionó un medico, debemos obtener el listado en un array para enviarlo al modelo
      $allInputs['listaMedicos'] = $this->model_empleado->m_cargar_medicos_de_empresa_especialidad($allInputs);
      $contador = count($allInputs['listaMedicos']);
      foreach ($allInputs['listaMedicos'] as $key => $row) {
        if($row['idmedico'] != 'ALL' ){
          if( $key < ($contador - 1) ){
            $allInputs['arrMedicos'] .= $row['idmedico'] . ', ';
          }else{
            $allInputs['arrMedicos'] .= $row['idmedico'];
          }
        }
      }
    }
    
    $lista = $this->model_empleado->m_cargar_solicitudes_medico_venta($allInputs);

    /* CREACION DEL PDF */ 
    $headerDetalle = array('ITEM', 'ESPECIALIDAD', 'MEDICO', 'PRODUCTO SOLICITADO', 'TIPO PRODUCTO', 'CANT.', 'MONTO'); 
    $this->pdf->Ln(1);
    $wDetalle = array(10,85,100,40,50,15,20);// 270
    $this->pdf->SetFont('Arial','B',10);
    for($i=0;$i<count($headerDetalle);$i++)
      $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
    $this->pdf->Ln();
    $this->pdf->SetWidths($wDetalle);
    $this->pdf->SetAligns(array('R', 'L', 'L', 'L','L','C','R'));
    $this->pdf->SetFillColor(224,235,255);
    $fill = false;
    $i = 1;
    $montoTotal = 0;
    foreach ($lista as $key => $row) { 
      $this->pdf->SetFont('Arial','',7);
      $this->pdf->Row( 
        array(
          $i++,
          utf8_decode(strtoupper($row['especialidad'])),
          utf8_decode(strtoupper($row['producto'])),
          utf8_decode(strtoupper($row['tipo_producto'])),
          $row['cantidad'],
          $row['monto']
        )
        ,$fill
      );
      $fill = !$fill;
      $montoTotal += $row['monto'];
    }
    $alinear = 'R';
    $width = $this->pdf->GetStringWidth($montoTotal);
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',12);
    $this->pdf->Cell(220-$width,6,'');
    $this->pdf->Cell(28,6,'TOTAL',0,0,'R');
    $this->pdf->Cell(3,6,':',0,0,'C');
    $this->pdf->SetFont('Arial','',12);
    $this->pdf->Cell(round($width+20),6,'S/. '.number_format($montoTotal,2),0,0,$alinear);
    $this->pdf->Ln();
    
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/'.$allInputs['titulo'] .'-'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/'.$allInputs['titulo'] .'-'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
   public function report_solicitudes_medico_especialidad()
  {
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext(); 
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,'SEDE');
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode($allInputs['sede']['descripcion'])); 
    $this->pdf->Ln();
    // $this->pdf->SetFont('Arial','B',11);
    // $this->pdf->Cell(35,5,'ESPECIALIDAD');
    // $this->pdf->Cell(3,5,':'); 
    // $this->pdf->SetFont('Arial','',10);
    // $this->pdf->Cell(40,5,utf8_decode($allInputs['especialidad']['descripcion'])); 
    // $this->pdf->Ln();
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('DESDE '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,$allInputs['desde'] . ' ' . $allInputs['desdeHora'] .':'. $allInputs['desdeMinuto'] ); 
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('HASTA '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,$allInputs['hasta'] . ' ' . $allInputs['hastaHora'] .':'. $allInputs['hastaMinuto'] ); 
      $this->pdf->Ln(8);
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,utf8_decode('MES / AÑO '));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode(strtoupper_total($allInputs['mes']['mes'])) . ' / ' . $allInputs['anioDesdeCbo'] ); 
      $this->pdf->Ln(8);
    }
    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(60,5,utf8_decode('ESPECIALIDAD DE SOLICITUD'));
    $this->pdf->Cell(3,5,':'); 
    // $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode( strtoupper_total( $allInputs['especialidadSolicitud']['descripcion'] ) ) ); 
    $this->pdf->Ln(8);
    
    /* TRATAMIENTO DE DATOS */ 
    //$allInputs['reporte'] = TRUE; 
    $allInputs['arrMedicos'] = array(); 
    if($allInputs['medico']['idmedico'] == 'ALL'){ // si no se seleccionó un medico, debemos obtener el listado en un array para enviarlo al modelo
      $contador = count($allInputs['listaMedicos']);
      foreach ($allInputs['listaMedicos'] as $key => $row) {
        if($row['idmedico'] != 'ALL' ){
          $allInputs['arrMedicos'][] = $row['idmedico'];
        }
      }
      $allInputs['arrMedicos'] = implode(",", $allInputs['arrMedicos']);
    }

    
    $lista = $this->model_empleado->m_cargar_solicitudes_medico_especialidad_venta($allInputs);
    // var_dump($lista); exit();
    $arrTPAux = array();
    $arrPrincipal = array();
    $arrListado = array();
    $cantidad_total = 0;
    foreach ($lista as $row) {
      $arrTPAux = array(
        'idmedico' => $row['idmedico'],
        'medico' => $row['medico'],
        'cantidadtotal' => 0,
        'listado' => array(),
      );
      $arrPrincipal[$row['idmedico']] = $arrTPAux;
    }
    foreach ($arrPrincipal as $key1 => $row1) {
      $arrListado = array();
      foreach ($lista as $key2 => $row2) {
        if( $row2['idmedico'] == $key1 ){
          array_push($arrListado, array(
            'producto' => $row2['producto'],
            'tipo_producto' => $row2['tipo_producto'],
            'cantidad' => $row2['cantidad'],
            'monto' => $row2['monto'],
            )
          );
          $arrPrincipal[$key1]['listado'] = $arrListado;
        }
      }
    }
    // var_dump($arrPrincipal); exit();
    /* CREACION DEL PDF */
    foreach ($arrPrincipal as $rowPr) {
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(40,5,utf8_decode(strtoupper_total($rowPr['medico'])));
      $this->pdf->Ln();
      $headerDetalle = array('ITEM', 'PRODUCTO SOLICITADO', 'TIPO DE PRODUCTO','CANT.', 'MONTO'); 
      $this->pdf->Ln(1);
      // $this->pdf->SetWidths(array(10,90,100,50,20));
      // $wDetalle = $this->pdf->GetWidths();
      $wDetalle = array(15,130,90,15,20);// 270
      $this->pdf->SetFont('Arial','B',10);
      for($i=0;$i<count($headerDetalle);$i++)
        $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
        
      $this->pdf->Ln();
      $this->pdf->SetWidths($wDetalle);
      $this->pdf->SetAligns(array('R', 'L', 'L', 'C','R'));
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;
      $i = 1;
      $cantidadTotal = 0;
      $montoTotal = 0;
      foreach ($rowPr['listado'] as $key => $row) { 
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Row(
          array(
            $i++,
            utf8_decode(strtoupper($row['producto'])),
            utf8_decode(strtoupper($row['tipo_producto'])),
            $row['cantidad'],
            $row['monto']
          )
          ,$fill
        );
        $fill = !$fill;
        $cantidadTotal += $row['cantidad'];
        $montoTotal += $row['monto'];
      }
      $alinear = 'R';
      $width = $this->pdf->GetStringWidth($montoTotal);
      $this->pdf->Ln(5);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(220-$width,6,'');
      $this->pdf->Cell(28,6,'CANT. TOTAL',0,0,'R');
      $this->pdf->Cell(3,6,':',0,0,'C');
      $this->pdf->SetFont('Arial','',12);
      $this->pdf->Cell(round($width+20),6,$cantidadTotal,0,0,$alinear);
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(220-$width,6,'');
      $this->pdf->Cell(28,6,'MONTO TOTAL',0,0,'R');
      $this->pdf->Cell(3,6,':',0,0,'C');
      $this->pdf->SetFont('Arial','',12);
      $this->pdf->Cell(round($width+20),6,'S/. '.number_format($montoTotal,2),0,0,$alinear);
      $this->pdf->Ln();
    }

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_solicitudes_medico_especialidad_excel(){
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 1024);
    ini_set('xdebug.var_display_max_data', 1024);
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $arrData['flag'] = 0;
    // TRATAMIENTO DE DATOS
      $allInputs['arrMedicos'] = '';
        if($allInputs['medico']['idmedico'] == 'ALL'){ // si no se seleccionó un medico, debemos obtener el listado en un array para enviarlo al modelo
          $contador = count($allInputs['listaMedicos']);
          foreach ($allInputs['listaMedicos'] as $key => $row) {
            if($row['idmedico'] != 'ALL' ){
              if( $key < ($contador - 1) ){
                $allInputs['arrMedicos'] .= $row['idmedico'] . ', ';
              }else{
                $allInputs['arrMedicos'] .= $row['idmedico'];
              }
            }
          }
        }
      
      $lista = $this->model_empleado->m_cargar_solicitudes_medico_especialidad_venta($allInputs);
      // agrupamiento por medicos
      $arrTPAux = array();
      $arrPrincipal = array();
      $arrListado = array();
      $cantidad_total = 0;
      foreach ($lista as $row) {
        $arrTPAux = array(
          'idmedico' => $row['idmedico'],
          'medico' => $row['medico'],
          'cantidadtotal' => 0,
          'listado' => array(),
        );
        $arrPrincipal[$row['idmedico']] = $arrTPAux;
      }
      foreach ($arrPrincipal as $key1 => $row1) {
        $arrListado = array();
        foreach ($lista as $key2 => $row2) {
          if( $row2['idmedico'] == $key1 ){
            array_push($arrListado, array(
              'producto' => $row2['producto'],
              'tipo_producto' => $row2['tipo_producto'],
              'cantidad' => $row2['cantidad'],
              'monto' => $row2['monto'],
              )
            );
            $arrPrincipal[$key1]['listado'] = $arrListado;
          }
        }
      }
      $arrPrincipal = array_values($arrPrincipal);
      // FORMACION DEL ARRAY DE LISTADO
      $arrListadoProd = array();
      $monto_total = 0;
      $cantidad_total = 0;
      $i = 1;
      foreach ($arrPrincipal as $rowMed) {
        foreach ($rowMed['listado'] as $row) {
          array_push($arrListadoProd, 
            array(
              $i++,
              strtoupper_total($rowMed['medico']),
              strtoupper_total($row['producto']),
              strtoupper_total($row['tipo_producto']),
              $row['cantidad'],
              $row['monto']
            )
          );
          
        }
      }
      // var_dump($arrListadoProd); exit();

      // foreach ($lista as $row) { 
      //   array_push($arrListadoProd, 
      //     array(
      //       $i++,
      //       strtoupper_total($row['medico']),
      //       strtoupper_total($row['producto']),
      //       strtoupper_total($row['tipo_producto']),
      //       $row['cantidad'],
      //       $row['monto']
      //     )
      //   );
      //   $monto_total += $row['monto'];
      //   $cantidad_total += ($row['cantidad']);
      // }
    // SETEO DE VARIABLES
    $dataColumnsTP = array( 
      'ITEM', 'MEDICO', 'PRODUCTO SOLICITADO', 'TIPO PRODUCTO', 'CANT.', 'MONTO', 'CANT. TOTAL', 'MONTO TOTAL'
    );
    $endColum = 'H';
    $this->excel->setActiveSheetIndex(0);
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->excel->getActiveSheet()->setTitle($allInputs['desde'].' - '.$allInputs['hasta']);
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->excel->getActiveSheet()->setTitle($allInputs['mes']['mes'].' - '.$allInputs['anioDesdeCbo']);
    }
    // SETEO DE ANCHO DE COLUMNAS
    $arrWidths = array(10,40,55,35,15,20,15,20);
    $i = 0;
    foreach(range('A',$endColum) as $columnID) {
      $this->excel->getActiveSheet()->getColumnDimension($columnID)->setWidth($arrWidths[$i++]);
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
        // 'alignment' => array(
        //     'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        //     'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        // ),
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
    $this->excel->getActiveSheet()->getStyle('B3:B7')->applyFromArray($styleArrayEncabezado);

    $this->excel->getActiveSheet()->getCell('B3')->setValue('SEDE:');
    $this->excel->getActiveSheet()->getCell('C3')->setValue($allInputs['sede']['descripcion']);
    
    $this->excel->getActiveSheet()->getCell('B4')->setValue('ESPECIALIDAD DE LA SOLICITUD:');
    $this->excel->getActiveSheet()->getCell('C4')->setValue($allInputs['especialidadSolicitud']['descripcion']);
    
    if( $allInputs['modalidadTiempo']['id'] == 'dias' ){
      $this->excel->getActiveSheet()->getCell('B5')->setValue('DESDE:');
      $this->excel->getActiveSheet()->getCell('C5')->setValue( $allInputs['desde'] . ' | ' . $allInputs['desdeHora'] .':'. $allInputs['desdeMinuto'] );
      $this->excel->getActiveSheet()->getCell('B6')->setValue('HASTA:');
      $this->excel->getActiveSheet()->getCell('C6')->setValue( $allInputs['hasta'] . ' | ' . $allInputs['hastaHora'] .':'. $allInputs['hastaMinuto'] );
      $currentCellEncabezado = 8; // donde inicia el encabezado del listado
    }elseif( $allInputs['modalidadTiempo']['id'] == 'meses' ){
      $this->excel->getActiveSheet()->getCell('B5')->setValue('MES / AÑO:');
      $this->excel->getActiveSheet()->getCell('C5')->setValue( $allInputs['mes']['mes'].' / '.$allInputs['anioDesdeCbo']  );
      $currentCellEncabezado = 7; // donde inicia el encabezado del listado
    }
    // ENCABEZADO DE LA LISTA
    // $currentCellEncabezado = 7; // donde inicia el encabezado del listado
    $currentCellTotal = count($arrListadoProd) + $currentCellEncabezado;
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->getAlignment()->setWrapText(true);
    $this->excel->getActiveSheet()->getStyle('A'.$currentCellEncabezado.':'.$endColum.$currentCellEncabezado)->applyFromArray($styleArrayHeader);
    $this->excel->getActiveSheet()->getStyle('A'.($currentCellEncabezado+1).':'.$endColum.$currentCellTotal)->applyFromArray($styleArrayProd);
    $this->excel->getActiveSheet()->setAutoFilter('B'.$currentCellEncabezado.':E'.$currentCellEncabezado);
    // LISTADO
    // $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D'.$currentCellTotal)->getNumberFormat()->setFormatCode('00000000');
    $this->excel->getActiveSheet()->fromArray($dataColumnsTP, null, 'A'.$currentCellEncabezado);
    $this->excel->getActiveSheet()->fromArray($arrListadoProd, null, 'A'.($currentCellEncabezado+1));
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellEncabezado+1).':F'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('H'.($currentCellEncabezado+1).':H'.($currentCellTotal))->getNumberFormat()->setFormatCode('#,##0.00');
    $this->excel->getActiveSheet()->getStyle('B'.($currentCellEncabezado+1).':B' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $this->excel->getActiveSheet()->getStyle('D'.($currentCellEncabezado+1).':D' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $this->excel->getActiveSheet()->getStyle('G'.($currentCellEncabezado+1).':H' .($currentCellTotal))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    // TOTAL
    $this->excel->getActiveSheet()->getStyle('D'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2)  )->applyFromArray($styleArrayTotales);
    $this->excel->getActiveSheet()->getCell('D'.($currentCellTotal+2) )->setValue('TOTAL');
    $this->excel->getActiveSheet()->getStyle('F'.($currentCellTotal+2) .':'.$endColum .($currentCellTotal+2) )->getNumberFormat()->setFormatCode('"S/."#,##0.00_-');
    $this->excel->getActiveSheet()->getCell('E'.($currentCellTotal+2))->setValue('=SUM(E'.($currentCellEncabezado+1) .':E'.($currentCellTotal+1).')');
    $this->excel->getActiveSheet()->getCell('F'.($currentCellTotal+2))->setValue('=SUM(F'.($currentCellEncabezado+1) .':F'.($currentCellTotal+1).')');
    // $this->excel->getActiveSheet()->getCell('L'.($currentCellTotal+2))->setValue('=SUM(L'.($currentCellEncabezado+1) .':L'.($currentCellTotal+1).')');

    // COMBINADO DE CELDAS
      $inicioMerge = $currentCellEncabezado+1;
      $finMerge = $inicioMerge;
      foreach ($arrPrincipal as $rowMed) {
        $finMerge = $inicioMerge + count($rowMed['listado']) - 1;
        $this->excel->getActiveSheet()->mergeCells('B'.$inicioMerge.':B'.$finMerge);
        // $this->excel->getActiveSheet()->mergeCells('D'.$inicioMerge.':D'.$finMerge);
        // var_dump('C'.$inicioMerge.':C'.$finMerge);
        $this->excel->getActiveSheet()->getCell('G'.($inicioMerge) )->setValue('=SUM(E'.($inicioMerge) .':E'.($finMerge).')');
        $this->excel->getActiveSheet()->mergeCells('G'.$inicioMerge.':G'.$finMerge);
        $this->excel->getActiveSheet()->getCell('H'.($inicioMerge) )->setValue('=SUM(F'.($inicioMerge) .':F'.($finMerge).')');
        $this->excel->getActiveSheet()->mergeCells('H'.$inicioMerge.':H'.$finMerge);
        $inicioMerge = $finMerge+1;
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
  
  //METODOS PARA EL REPORTE DE LA ENCUESTA MEDIANTE TABLETS
  public function report_porcentaje_encuesta()
  {
    $allInputs = json_decode(trim($this->input->raw_input_stream), true); 
    $fDesde = $allInputs['fDesdeEncuesta'];
    $fHasta = $allInputs['fHastaEncuesta'];
    $fTablet = $allInputs['tablet']['id'];

    $listaPreguntas = $this->model_estadisticas->m_listar_preguntas();
    $listaRespuestas = $this->model_estadisticas->m_listar_respuestas_por_pregunta($fDesde, $fHasta, $fTablet);
       
    if ($allInputs['tiposalida'] === 'grafico') {
      $this->report_porcentaje_encuesta_GRAPH($allInputs, $listaPreguntas, $listaRespuestas);
    }
  }

  public function report_evolucion_encuesta(){
    $allInputs = json_decode(trim($this->input->raw_input_stream), true);
    $fDesde = $allInputs['fDesdeEncuesta'];
    $fHasta = $allInputs['fHastaEncuesta'];
    $fTablet = $allInputs['tablet']['id'];
    $fPregunta = $allInputs['pregunta']['id'];

    //$desdeDia = fecha_format($this->input->post('desdeDia'));
    //$hastaDia = fecha_format($this->input->post('hastaDia'));

    //$desdeMes = fecha_format($this->input->post('desdeMes'));
    //$hastaMes = fecha_format($this->input->post('hastaMes'));

    $groupBy = $allInputs['agrupar']['id'];

    if( $groupBy == 'dia' ){
      $arrYAxis = get_rangofechas($fDesde, $fHasta, TRUE);
      $listaRespuestas = $this->model_estadisticas->m_listar_respuestas_por_fecha($fPregunta,$fDesde,$fHasta,$fTablet,$groupBy);
    }

    /*if( $groupBy == 'mes' ){
      $arrYAxis = get_rangomeses($desdeMes,$hastaMes); // var_dump($arrYAxis,$desdeMes,$hastaMes); exit();
      $listaRespuestas = $this->reporte_encuesta_model->m_listar_respuestas_por_fecha($desdeMes,$hastaMes,$tablet,$todos,$groupBy)->result_array();
    }*/    

    if ($allInputs['tiposalida'] === 'grafico') {
      $this->report_evolucion_encuesta_GRAPH($allInputs, $listaRespuestas, $arrYAxis, $fPregunta, $groupBy);
    }    
  }

  public function report_porcentaje_encuesta_GRAPH($allInputs, $listaPreguntas, $listaRespuestas){
    foreach ($listaPreguntas as $key => $row) {
        $totalPorPie = 0;
        foreach ( $listaRespuestas as $keyDet => $rowDet ) { 
          if($row['idpregunta'] == $rowDet['idpregunta']){ 
            if ($rowDet['respuesta'] == 0) {
              $respuestaStr = 'NO ATENDIDO';
            }elseif ($rowDet['respuesta'] == 1) {
              $respuestaStr = 'BIEN';
            }elseif ($rowDet['respuesta'] == 2) {
              $respuestaStr = 'REGULAR';
            }elseif ($rowDet['respuesta'] == 3) {
              $respuestaStr = 'MAL';
            }
            
            $arrAux = array(
              'name'=> $respuestaStr,
              'y'=> (int)$rowDet['contador']
            ); 
            $listaPreguntas[$key]['respuestas'][] = $arrAux;
            $totalPorPie += $arrAux['y'];
          }
        }
        $listaPreguntas[$key]['totalPorPie'] = $totalPorPie;
      }
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
      $arrData = array( 
      'xAxis'=> '',
      'series'=> $listaPreguntas,
      'columns'=> '',
      'tablaDatos'=> '',
      'tipoGraphic'=> 'pie',
      'tieneTabla'=> FALSE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  public function report_evolucion_encuesta_GRAPH($allInputs, $listaRespuestas, $arrYAxis, $idPregunta, $groupBy){
    $arrYAxisStr = array();
    foreach ($arrYAxis as $row => $value) {
      $arrYAxisStr[] = formatoConDia($value);
    }
      $listaPreguntas = $this->model_estadisticas->m_listar_preguntas($idPregunta);
      $arrSoloPreguntas = array(
        array( 
          'id'=> 0,
          'descripcion'=> 'NO ATENDIDO'
        ),
        array( 
          'id'=> 1,
          'descripcion'=> 'BIEN' 
        ),
        array( 
          'id'=> 2,
          'descripcion'=> 'REGULAR' 
        ),
        array( 
          'id'=> 3,
          'descripcion'=> 'MAL' 
        )
      );
      foreach ($listaPreguntas as $row) {
        $arrAux = array(
          'id'=> $row['idpregunta'],
          'descripcion'=> $row['descripcion_pr']
        );
        $arrPreguntas[] = $arrAux;
      }

      foreach ($arrPreguntas as $key => $row) {
        foreach ( $arrSoloPreguntas as $rowSP ) {
            if ($rowSP['id'] == 0) {
              $respuestaStr = 'NO ATENDIDO';
            }elseif ($rowSP['id'] == 1) {
              $respuestaStr = 'BIEN';
            }elseif ($rowSP['id'] == 2) {
              $respuestaStr = 'REGULAR';
            }elseif ($rowSP['id'] == 3) {
              $respuestaStr = 'MAL';
            }
            $arrDataEvolucion = array();
            $arrAux = array(
              'name'=> $respuestaStr,
              'data'=> $arrDataEvolucion,
              'indice' => $rowSP['id']
            ); 
            $arrPreguntas[$key]['respuestas'][] = $arrAux;
        }
        $arrPreguntas[$key]['yAxis'] = $arrYAxisStr;
      }
      foreach ($arrPreguntas as $key => $row) {
        foreach ($row['respuestas'] as $keyDet => $rowDet) {
          $arrDataEvolucion = array();
          foreach ($arrYAxis as $keyYA => $rowYA) { 
            $boolNulo = FALSE;
            foreach ($listaRespuestas as $keyData => $rowData) {
              if($groupBy == 'mes'){
                $fechaAMes = $rowData['fechaAMes'];
                $dateCompare = darFormatoMesAno(date('Y-m', strtotime("$fechaAMes") ));
              }else{
                $dateCompare = $rowData['fechaadia'];
              }
              if( $rowData['idpregunta'] == $row['id'] && $dateCompare == $rowYA && $rowData['respuesta'] == $rowDet['indice'] ){
                $arrDataEvolucion[] = (int)$rowData['contador'];
                $boolNulo = TRUE;
              }
            }
            if(!$boolNulo){
              $arrDataEvolucion[] = 0;
            }
          }
          $arrPreguntas[$key]['respuestas'][$keyDet]['data'] = $arrDataEvolucion;
        }
      }
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
      $arrData = array( 
      'xAxis'=> $arrYAxisStr,
      'series'=> $arrPreguntas,
      'columns'=> '',
      'tablaDatos'=> '',
      'tipoGraphic'=> 'line_encuesta',
      'tieneTabla'=> FALSE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  
  public function report_estadistico_encuesta_mes_dia_GRAPH($allInputs,$arrLista)
  {
    $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC"); 
    $longMonthTableArray = $longMonthArray; 
    array_unshift($longMonthTableArray, " ");
    array_push($longMonthTableArray, "TOTAL","BALANCE");

    $longMonthTableNumArray = array(1,2,3,4,5,6,7,8,9,10,11,12); 
    $contDesde = (int)$allInputs['anioDesde']; 
    $arrAnos = array(); 
    while ( $contDesde <= $allInputs['anioHasta'] ) { 
      $arrAnos[] = $contDesde;
      $contDesde++;
    }
    $valorCeroDefault = 0;
    $nameIndexValue = 'ticket';
    // var_dump("<pre>",$lista); exit(); 
    $arrSeries = array(); 
    foreach ($arrAnos as $key => $value) { 
      $arrSeries[$key] = array(
        'name'=> $value,
        'data' => array()
      );
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $keyDet => $rowDet) {
            if( $value == $rowDet['ano'] && $rowMes == $rowDet['nro_mes'] ){
                if( trim($rowDet['ano']) == trim($value)){
                   $arrSeries[$key]['data'][] = (float)$rowDet[$nameIndexValue];
                   $tuvoVentas = TRUE;
                }
            }
        }
        if(!$tuvoVentas){
          $arrSeries[$key]['data'][] = NULL; 
        }
      }  
    }

    $tablaDatos = array();
    foreach ($arrAnos as $keyAno => $rowAno) { 
      $tablaDatos[$keyAno]['ano'] = '<b>'.$rowAno.'</b>'; 
      $totalAno = 0; 
      foreach ($longMonthTableNumArray as $keyMes => $rowMes) { 
        $tuvoVentas = FALSE;
        foreach ($arrLista as $key => $row) {  
          if( $rowAno == $row['ano'] && $rowMes == $row['nro_mes'] ){ 
            $strValor = $row[$nameIndexValue]; 
            $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $strValor; 
            $tuvoVentas = TRUE;
            $totalAno += (float)$row[$nameIndexValue]; 
          }
        }
        if(!$tuvoVentas){ 
          $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = $valorCeroDefault; 
        }
      }
      $tablaDatos[$keyAno]['valor'] = $totalAno; 
      $tablaDatos[$keyAno]['dif'] = 0; 
    }
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['dif'] = 0; 
      $preKey = $key - 1; 
      if( array_key_exists($preKey, $tablaDatos) ) { 
        if( $tablaDatos[$key]['valor'] > 0 && $tablaDatos[$preKey]['valor'] > 0 ){ 
          $difCrecimiento = round(($row['valor'] - $tablaDatos[$preKey]['valor']) / $tablaDatos[$preKey]['valor'],4); 
          $tablaDatos[$key]['dif'] = ($difCrecimiento * 100); 
        }else{ 
          $tablaDatos[$key]['dif'] = 0; 
        }
      }
      //$tablaDatos[$key]['valor'] = '<b>S/. '.number_format($tablaDatos[$key]['valor'] ,2).'</b>'; 
    } 
    foreach ($tablaDatos as $key => $row) { 
      $tablaDatos[$key]['valor'] = '<b>'.number_format($tablaDatos[$key]['valor'] ,2).'</b>'; 
      if( $row['dif'] == 0){
        $tablaDatos[$key]['dif'] = '<b> - </b>'; 
      }else{
        $tablaDatos[$key]['dif'] = '<b>'.number_format($tablaDatos[$key]['dif'] ,2).'%</b>'; 
        
      }
      
    }
    //var_dump("<pre>",$tablaDatos); exit();
    foreach ($tablaDatos as $key => $row) {
      $tablaDatos[$key] = array_values($tablaDatos[$key]);
    }
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_empleado_contrato_vence_mes()
  {
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    // var_dump($allInputs['porEmpresaOSede']);
    // var_dump($allInputs['sede']);
    // var_dump($allInputs['empresa']);
    // var_dump($allInputs['mes']);
    // var_dump($allInputs['anioDesdeCbo']);
    // var_dump($allInputs['soloEmpActivos']);

    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);

    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();
    if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,'SEDE');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['sede']['descripcion'])); 
      $this->pdf->Ln();
    }else{
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,'EMPRESA');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['empresa']['descripcion'])); 
      $this->pdf->Ln();
    }

    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(35,5,utf8_decode('MES / AÑO '));
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(40,5,utf8_decode(strtoupper_total($allInputs['mes']['mes'])) . ' / ' . $allInputs['anioDesdeCbo'] ); 
    $this->pdf->Ln(8);
      
    
    /* TRATAMIENTO DE DATOS */ 
    $lista = $this->model_empleado->m_cargar_empleado_con_contrato_vence_hasta_mes($allInputs);
    // var_dump($lista); exit();

    // var_dump($arrPrincipal); exit();
    /* CREACION DEL PDF */

    if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
      $arrHeaderText = array('ITEM', 'ID EMPLEADO', 'NUMERO DOCUMENTO', 'EMPLEADO', 'CARGO', 'EMPRESA', 'F. INICIO CONTRATO', 'F. FIN CONTRATO');
    }else{
      $arrHeaderText = array('ITEM', 'ID EMPLEADO', 'NUMERO DOCUMENTO', 'EMPLEADO', 'CARGO', 'SEDE', 'F. INICIO CONTRATO', 'F. FIN CONTRATO'); 
    }
    $this->pdf->Ln(1);
    // $this->pdf->SetWidths(array(10,90,100,50,20));
    // $wDetalle = $this->pdf->GetWidths();

    $arrWidthCol = array(12,18,20,94,60,30,18,18);

    $arrHeaderAligns = array('C', 'C', 'C', 'C','C','C','C','C');
    $arrBoolMultiCell = array(0,1,1,0,0,0,1,1); // colocar 1 donde deseas utilizar multicell
    $countArray = count($arrWidthCol);
    $acumWidth = 0;
    $this->pdf->Ln(6);
    
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor(150, 190, 240);

    for ($i=0; $i < $countArray ; $i++) {
        if($arrBoolMultiCell[$i] == 1 ){
            $this->pdf->MultiCell($arrWidthCol[$i],4,utf8_decode($arrHeaderText[$i]),1,$arrHeaderAligns[$i],TRUE);
            $x=$this->pdf->GetX();
            $y=$this->pdf->GetY();
            $acumWidth += $arrWidthCol[$i];
            $this->pdf->SetXY($x+$acumWidth,$y-8);
        }else{
          $this->pdf->Cell($arrWidthCol[$i],8,utf8_decode($arrHeaderText[$i]),1,0,$arrHeaderAligns[$i],TRUE); 
          $acumWidth += $arrWidthCol[$i]; 
        }
        
    }
    $this->pdf->Ln(8);

    // $wDetalle = array(15,15,20,100,60,30,15,15);// 270
    // $this->pdf->SetFont('Arial','B',10);
    // for($i=0;$i<count($headerDetalle);$i++)
    //   $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
    $this->pdf->Ln();
    $this->pdf->SetWidths($arrWidthCol);
    $this->pdf->SetAligns(array('C', 'R', 'C', 'L','L','L','C','C'));
    $this->pdf->SetFillColor(224,235,255);
    $fill = false;
    $i = 1;

    foreach ($lista as $row) {
      if( $allInputs['porEmpresaOSede']['id'] == 'PS' ){
        $sede_empresa = $row['empresa'];
      }else{
        $sede_empresa = $row['sede'];
      }
      $this->pdf->SetFont('Arial','',7);
      $this->pdf->Row(
        array(
          $i++,
          $row['idempleado'],
          $row['numero_documento'],
          utf8_decode(strtoupper($row['empleado'])),
          utf8_decode(strtoupper($row['cargo'])),
          utf8_decode(strtoupper($sede_empresa)),
          $row['fecha_inicio_contrato'],
          $row['fecha_fin_contrato']
        )
        ,$fill
      );
      $fill = !$fill;
    }


    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function report_pacientes_especialidad(){
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    // var_dump($allInputs['porEmpresaOSede']);
    // var_dump($allInputs['sede']);
    // var_dump($allInputs['empresa']);
    // var_dump($allInputs['mes']);
    // var_dump($allInputs['anioDesdeCbo']);
    // var_dump($allInputs['soloEmpActivos']);
    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_empresa_por_codigo($allInputs['empresaAdmin']);
    // $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($idsedeempresaadmin);
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = FALSE;
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);

    $this->pdf->SetFont('Arial','',10);
    $this->pdf->AddPage('L','A4');
    $this->pdf->AliasNbPages();

      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,'EMPRESA');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['empresaAdmin']['descripcion'])); 
      $this->pdf->Ln();
      $this->pdf->SetFont('Arial','B',11);
      $this->pdf->Cell(35,5,'ESPECIALIDAD');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',10);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['especialidad']['descripcion'])); 
      $this->pdf->Ln();


    
    /* TRATAMIENTO DE DATOS */ 
    $lista = $this->model_venta->m_cargar_clientes_por_especialidad($allInputs);
    // var_dump($lista); exit();

    // var_dump($arrPrincipal); exit();
    /* CREACION DEL PDF */
    $arrHeaderText = array('ITEM', 'Nº DOC', 'NOMBRES', 'APELLIDOS', 'EDAD', 'CELULAR', 'TELEFONO', 'PRODUCTO','FECHA VENTA', 'ESTADO');

    $arrWidthCol = array(12,15,38,50,10,18,18,69,18,22);

    $arrHeaderAligns = array('C', 'C', 'C', 'C','C','C','C','C','C','C');
    $arrBoolMultiCell = array(0,0,0,0,0,0,0,0,0,0); // colocar 1 donde deseas utilizar multicell
    $countArray = count($arrWidthCol);
    $acumWidth = 0;
    $this->pdf->Ln(6);
    
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor(150, 190, 240);

    for ($i=0; $i < $countArray ; $i++) {
        if($arrBoolMultiCell[$i] == 1 ){
            $this->pdf->MultiCell($arrWidthCol[$i],4,utf8_decode($arrHeaderText[$i]),1,$arrHeaderAligns[$i],TRUE);
            $x=$this->pdf->GetX();
            $y=$this->pdf->GetY();
            $acumWidth += $arrWidthCol[$i];
            $this->pdf->SetXY($x+$acumWidth,$y-8);
        }else{
          $this->pdf->Cell($arrWidthCol[$i],8,utf8_decode($arrHeaderText[$i]),1,0,$arrHeaderAligns[$i],TRUE); 
          $acumWidth += $arrWidthCol[$i]; 
        }
        
    }
    $this->pdf->Ln(8);

    // $wDetalle = array(15,15,20,100,60,30,15,15);// 270
    // $this->pdf->SetFont('Arial','B',10);
    // for($i=0;$i<count($headerDetalle);$i++)
    //   $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');
      
    //$this->pdf->Ln();
    $this->pdf->SetWidths($arrWidthCol);
    $this->pdf->SetAligns(array('C', 'C', 'L', 'L','C','R','R','L','C','C'));
    $this->pdf->SetFillColor(224,235,255);
    $fill = false;
    $i = 1;

    foreach ($lista as $row) {
      $this->pdf->SetFont('Arial','',7);
      $this->pdf->Row(
        array(
          $i++,
          $row['num_documento'],
          utf8_decode(strtoupper($row['nombres'])),
          utf8_decode(strtoupper($row['apellidos'])),
          $row['edad'],
          $row['celular'],
          $row['telefono'],
          utf8_decode(strtoupper($row['producto'])),
          darFormatoDMY($row['fecha_venta']),
          $row['estado']
        )
        ,$fill
      );
      $fill = !$fill;
    }


    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
  public function boleta_pago(){
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $this->pdf = new Fpdfext();
    $periodo = strtoupper_total(darFormatoMesAnoPlanilla($allInputs['planilla']['fecha_cierre']));   

    $diasMes = date("t", strtotime($allInputs['planilla']['fecha_cierre']));

    $datosMes['desde']= date("Y-m-d", strtotime('01'. substr($allInputs['planilla']['fecha_cierre'], 2,8)) );
    $datosMes['hasta']= date("Y-m-d", strtotime($diasMes . substr($allInputs['planilla']['fecha_cierre'], 2,8)) );

    $totalFeriadosMes = $this->model_feriado->m_count_feriados_entre_fechas($datosMes);

    $startDate = new DateTime($datosMes['desde']);
    $endDate = new DateTime($datosMes['hasta']);
    $totalDomingos = 0;
    while($startDate->getTimestamp() <= $endDate->getTimestamp()){
        if($startDate->format('l')== 'Sunday'){
            $totalDomingos++;
        }
        $startDate->modify("+1 days");
    }

    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_empresa_admin_por_idempresa($allInputs['planilla']);
    // var_dump($empresaAdmin); exit();
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    if( $empresaAdmin['nombre_logo'] == 'phar-salud.png' ){
      $empresaAdmin['mode_report'] = 'F';
      $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);
      
    }else{
      $empresaAdmin['mode_report'] = FALSE;
    }
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);

    foreach ($allInputs['empleados'] as $row) {
      $this->pdf->AddPage('P','A4');
      $this->pdf->AliasNbPages();
      $alto = $this->pdf->GetPageHeight();
      $this->pdf->SetXY(10,6);
      $this->pdf->SetFont('Arial','B',8);
      $this->pdf->Cell(50,3,utf8_decode($empresaAdmin['razon_social']),0,3,'C');
      $this->pdf->SetFont('Arial','',6);
      $this->pdf->Cell(50,3,utf8_decode($empresaAdmin['ruc']),0,3,'C');
      $this->pdf->MultiCell(50,3,utf8_decode(ucwords(strtolower_total($empresaAdmin['domicilio_fiscal']))),0,'C');

      $this->pdf->SetXY(10,6);
      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(0,6,utf8_decode($allInputs['titulo']),0,0,'C');
      $this->pdf->Ln(5);
      $this->pdf->SetFont('Arial','B',7);
      $this->pdf->Cell(0,5,utf8_decode('MES DE ' .$periodo),0,0,'C');
      $this->pdf->Ln(8);


      if($row['reg_pensionario'] == 'AFP'){
        $reg_pensionario = $row['descripcion_afp'];
      }elseif($row['reg_pensionario'] == 'ONP'){
        $reg_pensionario = 'ONP';
      }else{
        $reg_pensionario = '--';
      }
      if( $row['idtipodocumentorh'] == 1 ){
        $tipo_documento = 'DNI';
      }elseif( $row['idtipodocumentorh'] == 2 ){
        $tipo_documento = 'C.E.';
      }elseif( $row['idtipodocumentorh'] == 3 ){
        $tipo_documento = 'PAS.';
      }

      $this->pdf->SetFillColor(183, 222, 232);

      /* ************** Seccion ***************** */
        // $this->pdf->SetFont('Arial','B',7);
        // $this->pdf->SetFillColor(183, 222, 232);
        // $this->pdf->Cell(50,4,utf8_decode('DATOS DE LA EMPRESA'),1,0,'C');
        // $this->pdf->Ln(5);
        // $this->pdf->SetFont('Arial','B',6);
        // $this->pdf->Cell(30,4,utf8_decode('RUC'),1,0,'C',TRUE);
        // $this->pdf->Cell(60,4,utf8_decode('RAZON SOCIAL'),1,0,'C',TRUE);
        // $this->pdf->Cell(100,4,utf8_decode('DIRECCION'),1,1,'C',TRUE);
        // $this->pdf->SetFont('Arial','',7);
        // $this->pdf->Cell(30,5,utf8_decode($empresaAdmin['ruc']),1,0,'C');
        // $this->pdf->Cell(60,5,utf8_decode($empresaAdmin['razon_social']),1,0,'C');
        // $this->pdf->Cell(100,5,utf8_decode(ucwords(strtolower_total($empresaAdmin['domicilio_fiscal']))),1,0,'C');
        // $this->pdf->Ln(7);
      /* ************** Seccion ***************** */
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->Cell(50,4,utf8_decode('DATOS DEL TRABAJADOR'),0,0,'L');
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','B',6);
        $this->pdf->Cell(10,4,utf8_decode('CÓDIGO'),1,0,'C',TRUE);
        $this->pdf->Cell(80,4,utf8_decode('NOMBRES Y APELLIDOS'),1,0,'C',TRUE);
        $this->pdf->Cell(30,4,utf8_decode('DOC. IDENTIDAD'),1,0,'C',TRUE);
        //$this->pdf->Cell(20,4,utf8_decode('F.NAC'),1,0,'C',TRUE);
        //$this->pdf->Cell(10,4,utf8_decode('HIJOS'),1,0,'C',TRUE);
        //$this->pdf->Cell(70,4,utf8_decode('DIRECCIÓN'),1,0,'C',TRUE);
        $this->pdf->Cell(50,4,utf8_decode('CARGO'),1,0,'C',TRUE);
        $this->pdf->Cell(20,4,utf8_decode('CATEGORIA'),1,0,'C',TRUE);

        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(10,4,utf8_decode($row['idempleado']),1,0,'C');
        $this->pdf->Cell(80,4,utf8_decode($row['empleado']),1,0,'C'); // max 48 caracteres
        $this->pdf->Cell(10,4,$tipo_documento,1,0,'C');
        $this->pdf->Cell(20,4,utf8_decode($row['numero_documento']),1,0,'C');
        // $this->pdf->Cell(20,4,'',1,0,'C');
        $this->pdf->Cell(50,4,utf8_decode($row['descripcion_cargo']),1,0,'C'); // max 34 caracteres
        // $this->pdf->Cell(55,4,utf8_decode('ESPECIALISTA EN DERECHO ADMINISTRATIVO Y CONTRATAC'),1,0,'L');
        $this->pdf->Cell(20,4,'EMPLEADO',1,0,'C');
        // $this->pdf->Ln(8);
      /* ************** Seccion ***************** */
        // $this->pdf->SetFont('Arial','B',7);
        // $this->pdf->Cell(90,4,utf8_decode('DATOS DEL TRABAJADOR VINCULADOS A LA RELACION LABORAL'),1,0,'C');
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',6);
        $arrWidthCol1 = array(25,20,15,15,15,15,15,16,16,18,20);
        $arrHeaderText1 = array(
          'REG. PENSIONARIO',
          'CUSPP',
          'F.ING',
          'F.CESE',
          'INI.VAC.',
          'FIN VAC.',
          'DIAS VAC.',
          'DIAS LABORADOS',
          'DIAS NO LABORADOS',
          'TOTAL HORAS LABORADAS',
          'TOTAL HORAS EXTRAS'
        );
        // $arrHeaderAligns = array('C','C','C','C');
        $arrBoolMultiCell1 = array(0,0,0,0,0,0,0,1,1,1,1); // colocar 1 donde deseas utilizar multicell
        $countArray = count($arrWidthCol1);
        $acumWidth = 0;
        $h = 3; // altura de la celda
        $this->pdf->SetFont('Arial','B',6);
        // $this->pdf->SetFillColor(150, 190, 240);
        for ($i=0; $i < $countArray ; $i++) {
            if($arrBoolMultiCell1[$i] == 1 ){
                $this->pdf->MultiCell($arrWidthCol1[$i],$h,utf8_decode($arrHeaderText1[$i]),1,'C',TRUE);
                $x=$this->pdf->GetX();
                $y=$this->pdf->GetY();
                $acumWidth += $arrWidthCol1[$i];
                $this->pdf->SetXY($x+$acumWidth,$y-($h*2));
            }else{
              $this->pdf->Cell($arrWidthCol1[$i],($h*2),utf8_decode($arrHeaderText1[$i]),1,0,'C',TRUE); 
              $acumWidth += $arrWidthCol1[$i]; 
            }
            
        }

        $this->pdf->Ln(($h*2));
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(25,4,utf8_decode($reg_pensionario),1,0,'C');
        $this->pdf->Cell(20,4,utf8_decode($row['cuspp']),1,0,'C');
        $this->pdf->Cell(15,4,utf8_decode($row['fecha_ingreso']),1,0,'C');
        $this->pdf->Cell(15,4,'',1,0,'C');
        $this->pdf->Cell(15,4,'',1,0,'C');
        $this->pdf->Cell(15,4,'',1,0,'C');
        $this->pdf->Cell(15,4,'',1,0,'C');
        
        if(obtenerEstadoConcepto($row['concepto_valor_json']['conceptos'], '0705') == 1){
          // $resultado = CalculoFaltasTardanzasEmpleado($row['idempleado'],  
          //                   date('Y-m-d', strtotime($allInputs['planilla']['fecha_apertura'])), 
          //                   date('Y-m-d', strtotime($allInputs['planilla']['fecha_cierre'])));  
          // $faltas = $resultado['falta'];
          $faltas = $row['concepto_valor_json']['configuracion']['faltas'];
        }else{
          $faltas = 0;
        }
        $this->pdf->Cell(16,4,utf8_decode($diasMes-$faltas),1,0,'C');
        $this->pdf->Cell(16,4,utf8_decode($faltas),1,0,'C');
        $diasLaborados = $diasMes - $totalFeriadosMes - $totalDomingos - $faltas;
        $horasLaboradas = $diasLaborados * (int)$row['concepto_valor_json']['configuracion']['horas_diarias'];

        $this->pdf->Cell(18,4,utf8_decode($horasLaboradas),1,0,'C');
        $total_horas = (int)$row['concepto_valor_json']['configuracion']['horas_extras25'] + (int)$row['concepto_valor_json']['configuracion']['horas_extras35'];
        $this->pdf->Cell(20,4,utf8_decode($total_horas),1,0,'C');
        $this->pdf->Ln(4);
      /* ************** Seccion ***************** */
        $this->pdf->SetFont('Arial','B',6);
        $arrWidthCol2 = array(45,75);
        $arrHeaderText2 = array(
          'OTROS EMPLEADORES POR RENTAS DE 5ta CATEGORIA',
          'CUENTA CTE. DEPOSITO'
        );
        // $arrHeaderAligns = array('C','C','C','C');
        $arrBoolMultiCell2 = array(1,0); // colocar 1 donde deseas utilizar multicell
        $countArray = count($arrWidthCol2);
        $acumWidth = 0;
        $h = 3; // altura de la celda
        $this->pdf->SetFont('Arial','B',6);
        // $this->pdf->SetFillColor(150, 190, 240);
        for ($i=0; $i < $countArray ; $i++) {
            if($arrBoolMultiCell2[$i] == 1 ){
                $this->pdf->MultiCell($arrWidthCol2[$i],$h,utf8_decode($arrHeaderText2[$i]),1,'C',TRUE);
                $x=$this->pdf->GetX();
                $y=$this->pdf->GetY();
                $acumWidth += $arrWidthCol2[$i];
                $this->pdf->SetXY($x+$acumWidth,$y-($h*2));
            }else{
              $this->pdf->Cell($arrWidthCol2[$i],($h*2),utf8_decode($arrHeaderText2[$i]),1,0,'C',TRUE); 
              $acumWidth += $arrWidthCol2[$i]; 
            }
            
        }
        $this->pdf->Ln(($h*2));

        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(45,4,'',1,0,'C');
        $banco = empty($row['concepto_valor_json']['datos_bancarios']['descripcion_banco']) ? '' : $row['concepto_valor_json']['datos_bancarios']['descripcion_banco'];
        $this->pdf->Cell(45,4,utf8_decode($banco),1,0,'C');

        $cuenta = empty($row['concepto_valor_json']['datos_bancarios']['cuenta_corriente']) ? '' : $row['concepto_valor_json']['datos_bancarios']['cuenta_corriente'];
        $this->pdf->Cell(30,4,utf8_decode($cuenta),1,0,'C');

        $this->pdf->Ln(5);
      /* ************** CONCEPTOS ***************** */
        //print_r($row['concepto_valor_json']);

        $this->pdf->SetFont('Arial','B',6);
        $ancho = ($this->pdf->GetPageWidth() - 20) / 3;
        $this->pdf->Ln(3);
        foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
          $this->pdf->Cell($ancho,6,utf8_decode(strtoupper_total($tipoConcepto['descripcion_tipo'])),1,0,'C', TRUE);
        }
        $this->pdf->Ln(6);

        $this->pdf->SetFont('Arial','',5);
        //remuneraciones
        $xInicial = $this->pdf->getX();
        $yInicial = $this->pdf->getY();

        $yMayor = 0;
        $xActual= $xInicial;
        foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
          if($indexTipoConcepto == 1){
            $this->pdf->SetY($yInicial+2);
          }
          if($indexTipoConcepto > 1){
            $xActual = (float)$xActual + (float)$ancho;
            $this->pdf->SetXY($xActual, $yInicial+2);
          }

          $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['rectangulo']['x'] = $this->pdf->getX();
          $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['rectangulo']['y'] = $this->pdf->getY();
          $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['total'] = 0;
          foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {          
            foreach ($categoria['conceptos'] as $indexCon => $concepto) {
              $valor = 0;
                switch ($concepto['codigo_plame']) {
                  case '0909':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['movilidad'];
                    break;
                  case '0914':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['refrigerio'];
                    break;
                  case '0917':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['condicion_trabajo'];
                    break;
                  default:
                    $valor = (float)$concepto['valor_empleado'];
                    break;
                }
                if($concepto['estado_pc_empleado'] == 1 && $valor > 0){
                  $this->pdf->SetX($xActual);
                  $y = $this->pdf->GetY();
                  $this->pdf->MultiCell(10,3,$concepto['codigo_plame'],0,'L',FALSE);

                  $this->pdf->SetXY($xActual + 6,$y);
                  $this->pdf->MultiCell($ancho-16,3,iconv("UTF-8", "CP1252", strtoupper_total($concepto['descripcion'])),0,'L',FALSE);
                  $y2 = $this->pdf->GetY(); //si genera varias lineas hay que saber en que $y finaliza, esto sirve para hacer el salto de linea

                  $this->pdf->SetXY($xActual + $ancho - 10,$y);
                  $this->pdf->MultiCell(10,3,utf8_decode(number_format($valor, 2)),0,'R',FALSE);

                  $this->pdf->SetY($y2);
                }  
                $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['total'] += $valor;
            }                   
          }
          // exit();

          $yMayor = ($this->pdf->GetY()>$yMayor) ? $this->pdf->GetY() : $yMayor; 
        }
        // var_dump($row['concepto_valor_json']['conceptos'][1]['rectangulo']);
        foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
          $this->pdf->Rect($tipoConcepto['rectangulo']['x'], $tipoConcepto['rectangulo']['y']-2, $ancho, $yMayor - $yInicial+4, 'D');
          $yFinal = $this->pdf->GetY() + ($yMayor - $yInicial+4);       
        }

        //$this->pdf->Ln(0);
        $this->pdf->SetX($xInicial);
        $this->pdf->SetY($yFinal-8);
        $this->pdf->SetFont('Arial','B',7);
        /* TOTALES */
        foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
          if( $indexTipoConcepto != 3 ){
            $this->pdf->Cell($ancho,5,utf8_decode('Total ' . $tipoConcepto['descripcion_tipo']),1,0,'L', TRUE);
            $this->pdf->SetX($this->pdf->GetX()-10);
            $this->pdf->Cell(10,5,utf8_decode('S/.' . number_format((float)$tipoConcepto['total'], 2)),0,0,'R', FALSE);
          }else{
            $this->pdf->Cell($ancho,5,utf8_decode('Neto a Pagar '),1,0,'L', TRUE);
            $this->pdf->SetX($this->pdf->GetX()-10);
            $ingresos = (float)$row['concepto_valor_json']['conceptos'][1]['total'];
            $descuentos = (float)$row['concepto_valor_json']['conceptos'][2]['total'];
            $this->pdf->Cell(10,5,'S/.' . number_format(($ingresos - $descuentos),2),0,0,'R', FALSE);
          }
        }
      /* FIRMAS */
        // $this->pdf->Ln(30);
        $x = 10;
        $y = ($alto/2)-10;
        $this->pdf->SetXY($x,$y);
        $this->pdf->SetFont('Arial','',5);
        $this->pdf->Cell(95,5,utf8_decode('Empleador'),0,0,'C');
        $this->pdf->Cell(95,5,utf8_decode('Trabajador'),0,0,'C');
      /* ================== SEPARACION ========================== */
        $yFinal = $alto / 2;
        // var_dump($yFinal); exit();
        $this->pdf->SetLineWidth(0.06);
        $this->pdf->SetDash(2,1); //5mm on, 5mm off
        $this->pdf->Line(0, $yFinal, 210, $yFinal);
        $this->pdf->SetDash();
        $this->pdf->SetY($yFinal);
        
      /* ===================> COPIA <============================ */
        $this->pdf->Ln(10);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x,$y);
        $this->pdf->SetFont('Arial','B',8);
        $this->pdf->Cell(50,3,utf8_decode($empresaAdmin['razon_social']),0,3,'C');
        $this->pdf->SetFont('Arial','',6);
        $this->pdf->Cell(50,3,utf8_decode($empresaAdmin['ruc']),0,3,'C');
        $this->pdf->MultiCell(50,3,utf8_decode(ucwords(strtolower_total($empresaAdmin['domicilio_fiscal']))),0,'C');

        $this->pdf->SetXY($x,$y);
        $this->pdf->SetFont('Arial','B',12);
        $this->pdf->Cell(0,6,utf8_decode($allInputs['titulo']),0,0,'C');
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->Cell(0,5,utf8_decode('MES DE ' .$periodo),0,0,'C');
        $this->pdf->Ln(8);


        // if($row['reg_pensionario'] == 'AFP'){
        //   $reg_pensionario = $row['descripcion_afp'];
        // }elseif($row['reg_pensionario'] == 'ONP'){
        //   $reg_pensionario = 'ONP';
        // }else{
        //   $reg_pensionario = '--';
        // }
        // if( $row['idtipodocumentorh'] == 1 ){
        //   $tipo_documento = 'DNI';
        // }elseif( $row['idtipodocumentorh'] == 2 ){
        //   $tipo_documento = 'C.E.';
        // }elseif( $row['idtipodocumentorh'] == 3 ){
        //   $tipo_documento = 'PAS.';
        // }

        /* ************** Seccion ***************** */

        /* ************** Seccion ***************** */
          $this->pdf->SetFont('Arial','B',7);
          $this->pdf->Cell(50,4,utf8_decode('DATOS DEL TRABAJADOR'),0,0,'L');
          $this->pdf->Ln(5);
          $this->pdf->SetFont('Arial','B',6);
          $this->pdf->Cell(10,4,utf8_decode('CÓDIGO'),1,0,'C',TRUE);
          $this->pdf->Cell(80,4,utf8_decode('NOMBRES Y APELLIDOS'),1,0,'C',TRUE);
          $this->pdf->Cell(30,4,utf8_decode('DOC. IDENTIDAD'),1,0,'C',TRUE);
          //$this->pdf->Cell(20,4,utf8_decode('F.NAC'),1,0,'C',TRUE);
          //$this->pdf->Cell(10,4,utf8_decode('HIJOS'),1,0,'C',TRUE);
          //$this->pdf->Cell(70,4,utf8_decode('DIRECCIÓN'),1,0,'C',TRUE);
          $this->pdf->Cell(50,4,utf8_decode('CARGO'),1,0,'C',TRUE);
          $this->pdf->Cell(20,4,utf8_decode('CATEGORIA'),1,0,'C',TRUE);

          $this->pdf->Ln(4);
          $this->pdf->SetFont('Arial','',7);
          $this->pdf->Cell(10,4,utf8_decode($row['idempleado']),1,0,'C');
          $this->pdf->Cell(80,4,utf8_decode($row['empleado']),1,0,'C'); // max 48 caracteres
          $this->pdf->Cell(10,4,$tipo_documento,1,0,'C');
          $this->pdf->Cell(20,4,utf8_decode($row['numero_documento']),1,0,'C');
          // $this->pdf->Cell(20,4,'',1,0,'C');
          $this->pdf->Cell(50,4,utf8_decode($row['descripcion_cargo']),1,0,'C'); // max 34 caracteres
          // $this->pdf->Cell(55,4,utf8_decode('ESPECIALISTA EN DERECHO ADMINISTRATIVO Y CONTRATAC'),1,0,'L');
          $this->pdf->Cell(20,4,'EMPLEADO',1,0,'C');
          // $this->pdf->Ln(8);
        /* ************** Seccion ***************** */
          $this->pdf->Ln(4);
          $this->pdf->SetFont('Arial','B',6);
          $countArray = count($arrWidthCol1);
          $acumWidth = 0;
          $h = 3; // altura de la celda
          $this->pdf->SetFont('Arial','B',6);
          for ($i=0; $i < $countArray ; $i++) {
              if($arrBoolMultiCell1[$i] == 1 ){
                  $this->pdf->MultiCell($arrWidthCol1[$i],$h,utf8_decode($arrHeaderText1[$i]),1,'C',TRUE);
                  $x=$this->pdf->GetX();
                  $y=$this->pdf->GetY();
                  $acumWidth += $arrWidthCol1[$i];
                  $this->pdf->SetXY($x+$acumWidth,$y-($h*2));
              }else{
                $this->pdf->Cell($arrWidthCol1[$i],($h*2),utf8_decode($arrHeaderText1[$i]),1,0,'C',TRUE); 
                $acumWidth += $arrWidthCol1[$i]; 
              }
              
          }
          $this->pdf->Ln(($h*2));
          $this->pdf->SetFont('Arial','',7);
          $this->pdf->Cell(25,4,utf8_decode($reg_pensionario),1,0,'C');
          $this->pdf->Cell(20,4,utf8_decode($row['cuspp']),1,0,'C');
          $this->pdf->Cell(15,4,utf8_decode($row['fecha_ingreso']),1,0,'C');
          $this->pdf->Cell(15,4,'',1,0,'C');
          $this->pdf->Cell(15,4,'',1,0,'C');
          $this->pdf->Cell(15,4,'',1,0,'C');
          $this->pdf->Cell(15,4,'',1,0,'C');
          
         
          $this->pdf->Cell(16,4,utf8_decode($diasMes-$faltas),1,0,'C');
          $this->pdf->Cell(16,4,utf8_decode($faltas),1,0,'C');

          $this->pdf->Cell(18,4,utf8_decode($horasLaboradas),1,0,'C');
          $total_horas = (int)$row['concepto_valor_json']['configuracion']['horas_extras25'] + (int)$row['concepto_valor_json']['configuracion']['horas_extras35'];
          $this->pdf->Cell(20,4,utf8_decode($total_horas),1,0,'C');
          $this->pdf->Ln(4);
        /* ************** Seccion ***************** */
          $this->pdf->SetFont('Arial','B',6);
          $countArray = count($arrWidthCol2);
          $acumWidth = 0;
          $h = 3; // altura de la celda
          $this->pdf->SetFont('Arial','B',6);
          for ($i=0; $i < $countArray ; $i++) {
              if($arrBoolMultiCell2[$i] == 1 ){
                  $this->pdf->MultiCell($arrWidthCol2[$i],$h,utf8_decode($arrHeaderText2[$i]),1,'C',TRUE);
                  $x=$this->pdf->GetX();
                  $y=$this->pdf->GetY();
                  $acumWidth += $arrWidthCol2[$i];
                  $this->pdf->SetXY($x+$acumWidth,$y-($h*2));
              }else{
                $this->pdf->Cell($arrWidthCol2[$i],($h*2),utf8_decode($arrHeaderText2[$i]),1,0,'C',TRUE); 
                $acumWidth += $arrWidthCol2[$i];
              }
              
          }
          $this->pdf->Ln(($h*2));

          $this->pdf->SetFont('Arial','',7);
          $this->pdf->Cell(45,4,'',1,0,'C');
          $banco = empty($row['concepto_valor_json']['datos_bancarios']['descripcion_banco']) ? '' : $row['concepto_valor_json']['datos_bancarios']['descripcion_banco'];
          $this->pdf->Cell(45,4,utf8_decode($banco),1,0,'C');

          $cuenta = empty($row['concepto_valor_json']['datos_bancarios']['cuenta_corriente']) ? '' : $row['concepto_valor_json']['datos_bancarios']['cuenta_corriente'];
          $this->pdf->Cell(30,4,utf8_decode($cuenta),1,0,'C');

          $this->pdf->Ln(5);
        /* ************** CONCEPTOS ***************** */
          
          $this->pdf->SetFont('Arial','B',6);
          $ancho = ($this->pdf->GetPageWidth() - 20) / 3;
          $this->pdf->Ln(3);
          foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
            $this->pdf->Cell($ancho,6,utf8_decode(strtoupper_total($tipoConcepto['descripcion_tipo'])),1,0,'C', TRUE);
          }
          $this->pdf->Ln(6);
          
          $this->pdf->SetFont('Arial','',5);
          //remuneraciones
          $xInicial = $this->pdf->getX();
          $yInicial = $this->pdf->getY();

          $yMayor = 0;
          $xActual= $xInicial;
          foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
            if($indexTipoConcepto == 1){
              $this->pdf->SetY($yInicial+2);
            }
            if($indexTipoConcepto > 1){
              $xActual = (float)$xActual + (float)$ancho;
              $this->pdf->SetXY($xActual, $yInicial+2);
            }

            $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['rectangulo']['x'] = $this->pdf->getX();
            $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['rectangulo']['y'] = $this->pdf->getY();
            $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['total'] = 0;
            foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {          
              foreach ($categoria['conceptos'] as $indexCon => $concepto) {
                $valor = 0;
                switch ($concepto['codigo_plame']) {
                  case '0909':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['movilidad'];
                    break;
                  case '0914':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['refrigerio'];
                    break;
                  case '0917':
                    $valor = (float)$row['concepto_valor_json']['configuracion']['condicion_trabajo'];
                    break;
                  default:
                    $valor = (float)$concepto['valor_empleado'];
                    break;
                }
                if($concepto['estado_pc_empleado'] == 1 && $valor > 0){
                  $this->pdf->SetX($xActual);
                  $y = $this->pdf->GetY();
                  $this->pdf->MultiCell(10,3,$concepto['codigo_plame'],0,'L',FALSE); 

                  $this->pdf->SetXY($xActual + 6,$y);
                  $this->pdf->MultiCell($ancho-16,3,iconv("UTF-8", "CP1252", strtoupper_total($concepto['descripcion'])),0,'L',FALSE);
                  $y2 = $this->pdf->GetY(); //si genera varias lineas hay que saber en que $y finaliza, esto sirve para hacer el salto de linea

                  $this->pdf->SetXY($xActual + $ancho - 10,$y);
                  $this->pdf->MultiCell(10,3,utf8_decode(number_format($valor, 2)),0,'R',FALSE); 

                  $this->pdf->SetY($y2); 
                }  
                $row['concepto_valor_json']['conceptos'][$indexTipoConcepto]['total'] += $valor;                
              }                     
            }

            $yMayor = ($this->pdf->GetY()>$yMayor) ? $this->pdf->GetY() : $yMayor; 
          }
          // var_dump($yMayor - $yInicial); exit();
          foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
            $this->pdf->Rect($tipoConcepto['rectangulo']['x'], $tipoConcepto['rectangulo']['y']-2, $ancho, $yMayor - $yInicial+4, 'D');
            $yFinal = $this->pdf->GetY() + ($yMayor - $yInicial+4);       
          }

          //$this->pdf->Ln(0);
          $this->pdf->SetX($xInicial);
          $this->pdf->SetY($yFinal-8);
          $this->pdf->SetFont('Arial','B',7);
          /* TOTALES */
          foreach ($row['concepto_valor_json']['conceptos'] as $indexTipoConcepto => $tipoConcepto) {
            if( $indexTipoConcepto != 3 ){
              $this->pdf->Cell($ancho,5,utf8_decode('Total ' . $tipoConcepto['descripcion_tipo']),1,0,'L', TRUE);
              $this->pdf->SetX($this->pdf->GetX()-10);
              $this->pdf->Cell(10,5,utf8_decode('S/.' . number_format((float)$tipoConcepto['total'], 2)),0,0,'R', FALSE);
            }else{
              $this->pdf->Cell($ancho,5,utf8_decode('Neto a Pagar '),1,0,'L', TRUE);
              $this->pdf->SetX($this->pdf->GetX()-10);
              $ingresos = (float)$row['concepto_valor_json']['conceptos'][1]['total'];
              $descuentos = (float)$row['concepto_valor_json']['conceptos'][2]['total'];
              $this->pdf->Cell(10,5,'S/.' . number_format(($ingresos - $descuentos),2),0,0,'R', FALSE);
            }
          }
        /* FIRMAS */
        $x = 10;
        $y = ($alto)-10;
        $this->pdf->SetXY($x,$y);
          $this->pdf->SetFont('Arial','',5);
          $this->pdf->Cell(95,5,utf8_decode('Empleador'),0,0,'C');
          $this->pdf->Cell(95,5,utf8_decode('Trabajador'),0,0,'C');
    }


    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));

  }
  public function reporte_cts(){
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true);
    $this->pdf = new Fpdfext();
    $periodo = strtoupper_total(darFormatoMesAnoPlanilla($allInputs['planilla']['fecha_cierre']));

    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_empresa_admin_por_idempresa($allInputs['planilla']);
    // var_dump($empresaAdmin); exit();
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = FALSE;
    mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
    $idplanilla = $allInputs['planilla']['id'];
    $listaEmpleados = $this->model_empleado_planilla->m_cargar_empleados_planilla_calculada($idplanilla);

    foreach ($listaEmpleados as $row) {
      $this->pdf->AddPage('P','A4');
      $this->pdf->AliasNbPages();

      $this->pdf->SetFont('Arial','B',12);
      $this->pdf->Cell(0,6,utf8_decode('LIQUIDACION DE COMPENSACION POR TIEMPO DE SERVICIO'),0,0,'C');
      $this->pdf->Ln(5);
      $this->pdf->SetFont('Arial','B',7);
      $this->pdf->Cell(0,5,utf8_decode('(ART. 29 D.S. Nº 001-97-TR)'),0,0,'C');
      $this->pdf->Ln(8);

      $descripcion = $empresaAdmin['razon_social'] . ' con RUC ' . $empresaAdmin['ruc'] .', domiciliado en ' . $empresaAdmin['domicilio_fiscal'] . '
      representada por su Gerente General Sra. Elizabeth Cristina Ramirez Palacios con DNI Nº 10797984,
      otorga la presente constancia al Sr.(a/ta.). ' . $row['empleado'] .' del depósito de su CTS al Banco BBVA Continental en Cuenta Nº ' .'' . ' en Moneda
      Nacional por el siguiente monto y periodo:';
      $this->pdf->SetFont('Arial','',7);
      $this->pdf->MultiCell(0,5,utf8_decode($descripcion),0,'L');


    }


    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  public function report_produccion_consulta_externa(){
    ini_set('xdebug.var_display_max_depth', 10); 
    ini_set('xdebug.var_display_max_children', 1024); 
    ini_set('xdebug.var_display_max_data', 1024); 

    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
 
    $this->pdf->SetFont('Arial','',8);
    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo']);
    if($allInputs['idTipoRango'] == 1){
      $this->pdf->AddPage('L','A4');  
    }else{
      $this->pdf->AddPage('P','A4'); 
    } 
    $this->pdf->AliasNbPages();
       
    $this->pdf->SetFont('Arial','B',8);
    $this->pdf->Cell(20,5,'SEDE');
    $this->pdf->Cell(3,5,':'); 
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(40,5,utf8_decode($allInputs['sede']['descripcion']));
    $this->pdf->Ln();

    if($allInputs['idTipoRango'] == 1){
      $this->pdf->SetFont('Arial','B',8);
      $this->pdf->Cell(20,5,utf8_decode('AÑO'));
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['anio'])); 
      $this->pdf->Ln();
    }else{
      $this->pdf->SetFont('Arial','B',8);
      $this->pdf->Cell(20,5,'DESDE');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['anioDesde'])); 
      $this->pdf->Ln();

      $this->pdf->SetFont('Arial','B',8);
      $this->pdf->Cell(20,5,'HASTA');
      $this->pdf->Cell(3,5,':'); 
      $this->pdf->SetFont('Arial','',8);
      $this->pdf->Cell(40,5,utf8_decode($allInputs['anioHasta'])); 
      $this->pdf->Ln(); 
    }
    
  
    $this->pdf->Ln(8);
         
    /* TRATAMIENTO DE DATOS */ 
    //Lista de Especialidades
    $especialidades = $this->model_especialidad->m_cargar_especialidades();
    $arrEspecialidades = array();
    foreach ($especialidades as $key => $especialidad) {
      array_push($arrEspecialidades, $especialidad['nombre']);
    }

    if($allInputs['idTipoRango'] == 1){
      $lista_consulta = $this->model_venta->m_cargar_consulta_externa($allInputs);
      $allInputs['dic_ant'] = 1;
      $dic_ant = $this->model_venta->m_cargar_consulta_externa($allInputs);
      // var_dump($lista); exit();
   
      /* CREACION DEL PDF */
      $arrHeaderText = array('ESPECIALIDAD', 'ENE', '%', 'FEB', '%', 'MAR','%', 'ABR','%', 'MAY','%', 'JUN','%', 'JUL','%','AGO','%','SEP','%', 'OCT','%','NOV','%','DIC','%','TOTAL');
      $arrWidthCol = array(35,10,9,10,9,10,9,10,9,10,9,10,9,10,9,10,9,10,9,10,9,10,9,10,9,12);
      $arrHeaderAligns = array('L','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C');
      $arrBoolMultiCell = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0); // colocar 1 donde deseas utilizar multicell
      $countArray = count($arrWidthCol);
      $countList = count($lista_consulta);
      $acumWidth = 0;        
    }else{
      $lista_consulta = $this->model_venta->m_cargar_consulta_externa_por_rango($allInputs);
      $allInputs['dic_ant'] = 1;
      $dic_ant = $this->model_venta->m_cargar_consulta_externa_por_rango($allInputs);
      //var_dump($lista_consulta);
      $arrHeaderText = array('ESPECIALIDAD');
      $anio = $allInputs['anioDesde'];
      $arrWidthCol = array(50);
      $arrHeaderAligns = array('L');
      $arrBoolMultiCell = array(0); // colocar 1 donde deseas utilizar multicell
      $anioTotal = array();
      $numAnios = 0;
      while ($anio <= $allInputs['anioHasta']) {
        array_push($arrHeaderText, $anio); array_push($arrHeaderText, '%');
        array_push($arrWidthCol, '18'); array_push($arrWidthCol, '13');    
        array_push($arrHeaderAligns, 'C'); array_push($arrHeaderAligns, 'C');
        array_push($arrBoolMultiCell, '0'); array_push($arrBoolMultiCell, '0');
        array_push($anioTotal, array('anio'.$anio =>'0')); array_push($anioTotal, array('por'.$anio =>'0'));
        $anio++;
        $numAnios++;
      }

      array_push($anioTotal, array('total' =>'0'));
      array_push($arrHeaderText, 'TOTAL');
      array_push($arrWidthCol, '15');
      array_push($arrHeaderAligns, 'C');
      array_push($arrBoolMultiCell, '0');
      $countArray = count($arrWidthCol);
      $countList = count($lista_consulta);
      $acumWidth = 0;

    }
    
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor(150, 190, 240);

    for ($i=0; $i < $countArray ; $i++) {
        if($arrBoolMultiCell[$i] == 1 ){
            $this->pdf->MultiCell($arrWidthCol[$i],4,utf8_decode($arrHeaderText[$i]),1,$arrHeaderAligns[$i],TRUE);
            $x=$this->pdf->GetX();
            $y=$this->pdf->GetY();
            $acumWidth += $arrWidthCol[$i];
            $this->pdf->SetXY($x+$acumWidth,$y-8);
        }else{
          $this->pdf->Cell($arrWidthCol[$i],8,utf8_decode($arrHeaderText[$i]),1,0,$arrHeaderAligns[$i],TRUE); 
          $acumWidth += $arrWidthCol[$i]; 
        }
        
    }
     
    if($allInputs['idTipoRango'] == 1){
      $this->pdf->Ln();
      $this->pdf->SetWidths($arrWidthCol);
      $this->pdf->SetAligns(array('L','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;

      $eneroTotal = 0; $eneTotal = 0; $febreroTotal = 0;
      $febTotal = 0; $marzoTotal = 0; $marTotal = 0; $abrilTotal = 0;
      $abrTotal = 0; $mayoTotal = 0; $mayTotal = 0; $junioTotal = 0;
      $junTotal = 0; $julioTotal = 0; $julTotal = 0; $agostoTotal = 0;
      $agoTotal = 0; $septiembreTotal = 0; $sepTotal = 0;
      $octubreTotal = 0; $octTotal = 0; $noviembreTotal = 0;
      $novTotal = 0; $diciembreTotal = 0; $dicTotal = 0; $total = 0;
      $this->pdf->SetFont('Arial','',6);
      
      $arrDatos = array();
      $arrDatosDicAnt = array();
      $i=0; $j=0;

      foreach ($arrEspecialidades as $especialidad) {     
        if($especialidad == $lista_consulta[$i]['especialidad']){
          array_push($arrDatos, 
            array('especialidad' => $especialidad,
              'enero' => ($lista_consulta[$i]['enero'] == 0) ? ' - ' : $lista_consulta[$i]['enero'],
              'febrero' => ($lista_consulta[$i]['febrero'] == 0) ? ' - ' : $lista_consulta[$i]['febrero'],
              'marzo' => ($lista_consulta[$i]['marzo'] == 0) ? ' - ' : $lista_consulta[$i]['marzo'],
              'abril' => ($lista_consulta[$i]['abril'] == 0) ? ' - ' : $lista_consulta[$i]['abril'],
              'mayo' => ($lista_consulta[$i]['mayo'] == 0) ? ' - ' : $lista_consulta[$i]['mayo'],
              'junio' => ($lista_consulta[$i]['junio'] == 0) ? ' - ' : $lista_consulta[$i]['junio'],
              'julio' => ($lista_consulta[$i]['julio'] == 0) ? ' - ' : $lista_consulta[$i]['julio'],
              'agosto' => ($lista_consulta[$i]['agosto'] == 0) ? ' - ' : $lista_consulta[$i]['agosto'],
              'septiembre' => ($lista_consulta[$i]['septiembre'] == 0) ? ' - ' : $lista_consulta[$i]['septiembre'],
              'octubre' => ($lista_consulta[$i]['octubre'] == 0) ? ' - ' : $lista_consulta[$i]['octubre'],
              'noviembre' => ($lista_consulta[$i]['noviembre'] == 0) ? ' - ' : $lista_consulta[$i]['noviembre'],
              'diciembre' => ($lista_consulta[$i]['diciembre'] == 0) ? ' - ' : $lista_consulta[$i]['diciembre'],
              'total' => ($lista_consulta[$i]['total'] == 0) ? ' - ' : $lista_consulta[$i]['total'],
            )
          );
          $i++;
        }else{
          array_push($arrDatos, 
            array('especialidad' => $especialidad,
              'enero' => ' - ',
              'febrero' => ' - ',
              'marzo' => ' - ',
              'abril' => ' - ',
              'mayo' => ' - ',
              'junio' => ' - ',
              'julio' => ' - ',
              'agosto' => ' - ',
              'septiembre' => ' - ',
              'octubre' => ' - ',
              'noviembre' => ' - ',
              'diciembre' => ' - ',
              'total' => ' - ',
            )
          );
        }
        if($dic_ant != null){
          if($especialidad == $dic_ant[$j]['especialidad']){
            array_push($arrDatosDicAnt, $dic_ant[$j]);
            $j++;
          }else{
            array_push($arrDatosDicAnt, 
              array('especialidad' => $especialidad,
                'dic_ant' => ' - ',
              )
            );
          }
        }
          
      }       
      //var_dump($arrDatos);
      foreach ($arrDatos as $key => $row) {
        $ene='-';$feb='-';$mar='-';$abr='-';$may='-';$jun='-';$jul='-';$ago='-';$sep='-';$oct='-';$nov='-';$dic='-';
       // var_dump($row);
        if($dic_ant != null){
          if($arrDatosDicAnt[$key]['dic_ant'] != 0 && $arrDatosDicAnt[$key]['dic_ant'] != ' - ' && $row['enero'] != ' - '){
            $ene = round((($row['enero']/$arrDatosDicAnt[$key]['dic_ant'])-1)*100);
          }
        }
        if($row['enero'] != 0 && $row['enero'] != ' - ' && $row['febrero'] != ' - '){
          $feb = round((($row['febrero']/$row['enero'])-1)*100);
        }
        if($row['febrero'] != 0 && $row['febrero'] != ' - ' && $row['marzo'] != ' - '){
          $mar = round((($row['marzo']/$row['febrero'])-1)*100);
        }
        if($row['marzo'] != 0 && $row['marzo'] != ' - ' && $row['abril'] != ' - '){
          $abr = round((($row['abril']/$row['marzo'])-1)*100);
        }
        if($row['abril'] != 0 && $row['abril'] != ' - ' && $row['mayo'] != ' - '){
          $may = round((($row['mayo']/$row['abril'])-1)*100);
        }
        if($row['mayo'] != 0 && $row['mayo'] != ' - ' && $row['junio'] != ' - '){
          $jun = round((($row['junio']/$row['mayo'])-1)*100);
        }
        if($row['junio'] != 0 && $row['junio'] != ' - ' && $row['julio'] != ' - '){
          $jul = round((($row['julio']/$row['junio'])-1)*100);
        }
        if($row['julio'] != 0 && $row['julio'] != ' - ' && $row['agosto'] != ' - '){
          $ago = round((($row['agosto']/$row['julio'])-1)*100);
        }
        if($row['agosto'] != 0 && $row['agosto'] != ' - ' && $row['septiembre'] != ' - '){
          $sep = round((($row['septiembre']/$row['agosto'])-1)*100);
        }
        if($row['septiembre'] != 0 && $row['septiembre'] != ' - ' && $row['octubre'] != ' - '){
          $oct = round((($row['octubre']/$row['septiembre'])-1)*100);
        }
        if($row['octubre'] != 0 && $row['octubre'] != ' - ' && $row['noviembre'] != ' - '){
          $nov = round((($row['noviembre']/$row['octubre'])-1)*100);
        }
        if($row['noviembre'] != 0 && $row['noviembre'] != ' - ' && $row['diciembre'] != ' - '){
          $dic = round((($row['diciembre']/$row['noviembre'])-1)*100);
        }
        $this->pdf->Row(
          array(
            $row['especialidad'],
            $row['enero'],
            $ene,
            $row['febrero'],
            $feb,
            $row['marzo'],
            $mar,
            $row['abril'],
            $abr,
            $row['mayo'],
            $may,
            $row['junio'],
            $jun,
            $row['julio'],
            $jul,
            $row['agosto'],
            $ago,
            $row['septiembre'],
            $sep,
            $row['octubre'],
            $oct,
            $row['noviembre'],
            $nov,
            $row['diciembre'],
            $dic,
            $row['total']
          )
          ,$fill
        );
       
        $eneroTotal += $row['enero'];
        if($ene != '-'){
          $eneTotal += $ene; 
        }
        
        $febreroTotal += $row['febrero']; $febTotal += $feb;
        $marzoTotal += $row['marzo']; $marTotal += $mar;
        $abrilTotal += $row['abril']; $abrTotal += $abr;
        $mayoTotal += $row['mayo']; $mayTotal += $may;
        $junioTotal += $row['junio']; $junTotal += $jun;
        $julioTotal += $row['julio']; $julTotal += $jul;
        $agostoTotal += $row['agosto']; $agoTotal += $ago;
        $septiembreTotal += $row['septiembre']; $sepTotal += $sep;
        $octubreTotal += $row['octubre']; $octTotal += $oct;
        $noviembreTotal += $row['noviembre']; $novTotal += $nov;
        $diciembreTotal += $row['diciembre']; $dicTotal += $dic;
        $total += $row['total'];
          
        $fill = !$fill;
      }

      $this->pdf->Row(
        array(
          'TOTAL', number_format($eneroTotal, 0, '.', ','), number_format($eneTotal, 0, '.', ','), 
          number_format($febreroTotal, 0, '.', ','),number_format($febTotal, 0, '.', ','), 
          number_format($marzoTotal, 0, '.', ','), number_format($marTotal, 0, '.', ','), 
          number_format($abrilTotal, 0, '.', ','), number_format($abrTotal, 0, '.', ','), 
          number_format($mayoTotal, 0, '.', ','), number_format($mayTotal, 0, '.', ','), 
          number_format($junioTotal, 0, '.', ','), number_format($junTotal, 0, '.', ','), 
          number_format($julioTotal, 0, '.', ','), number_format($julTotal, 0, '.', ','), 
          number_format($agostoTotal, 0, '.', ','), number_format($agoTotal, 0, '.', ','), 
          number_format($septiembreTotal, 0, '.', ','), number_format($sepTotal, 0, '.', ','),
          number_format($octubreTotal, 0, '.', ','), number_format($octTotal, 0, '.', ','), 
          number_format($noviembreTotal, 0, '.', ','), number_format($novTotal, 0, '.', ','), 
          number_format($diciembreTotal, 0, '.', ','), number_format($dicTotal, 0, '.', ','), 
          number_format($total, 0, '.', ',')
        )
        ,$fill
      );
    }else{
      $this->pdf->Ln();
      $this->pdf->SetWidths($arrWidthCol);
      $anio = $allInputs['anioDesde'];
      $arrHeader = array('L');
      while ($anio <= $allInputs['anioHasta']) {   
        array_push($arrHeader, 'R'); array_push($arrHeader, 'C');
        $anio++;
      }
      array_push($arrHeader, 'R');
      $this->pdf->SetAligns($arrHeader);
      $this->pdf->SetFillColor(224,235,255);
      $fill = false;

      foreach ($lista_consulta as $key => $row) {
        $this->pdf->SetFont('Arial','',7);
        $anio = $allInputs['anioDesde'];
        $array_row = array($row['especialidad']);
        $i = 0;
        while ($anio <= $allInputs['anioHasta']) {
          
          array_push($array_row, number_format($row['anio'.$anio], 0, '.', ','));
          $anioTotal[$i]['anio'.$anio]+=$row['anio'.$anio];
          $anioPor = ' - ';   
          $anioAnt = $anio - 1;
        
          if($anio == $allInputs['anioDesde']){
            if ($dic_ant != null && $dic_ant[$key]['anio'.$anioAnt] != 0) {
              $anioPor = round((($row['anio'.$anio]/$dic_ant[$key]['anio'.$anioAnt])-1)*100);
            }        
          }else if($row['anio'.$anioAnt] != 0){
           // var_dump($row['anio'.$anio-1]);
            $anioPor = round((($row['anio'.$anio]/$row['anio'.$anioAnt])-1)*100);
          }

          array_push($array_row, $anioPor);
          $anioTotal[$i+1]['por'.$anio]+=$anioPor;
          $anio++;
          $i+=2;
        }

        array_push($array_row, number_format($row['total'], 0, '.', ','));
        $anioTotal[$i]['total']+=$row['total'];
        $this->pdf->Row($array_row ,$fill);    
        $fill = !$fill;
      }

      $totales = array('TOTAL');
      $j=1;$i=0;$n=0;
      while ( $n < $numAnios) { 
        array_push($totales, number_format($anioTotal[$i]['anio'.$arrHeaderText[$j]], 0, '.', ','));
        array_push($totales, number_format($anioTotal[$i+1]['por'.$arrHeaderText[$j]], 0, '.', ','));
        $j+=2;$i+=2;$n++;
      }
      array_push($totales, number_format($anioTotal[$i]['total'], 0, '.', ','));
      $this->pdf->Row($totales ,$fill);
    }  
  
    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array( 
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  public function report_produccion_consulta_externa_GRAPH(){
    ini_set('max_execution_time', 300);
    ini_set('memory_limit','160M');
    $allInputs = json_decode(trim($this->input->raw_input_stream),true);    
    

    // TRATAMIENTO DE DATOS
      $arrLista = $this->model_venta->m_cargar_consulta_externa_GRAPH($allInputs);
      if($allInputs['idTipoRango'] == 1){
        $allInputs['anio'] = $allInputs['anio']-1;
        $anioAnt = $this->model_venta->m_cargar_consulta_externa_GRAPH($allInputs);
        $allInputs['anio'] = $allInputs['anio']+1;
      }else{
        $allInputs['idTipoRango'] = 1; $allInputs['anio'] = $allInputs['anioDesde']-1;
        $anioAnt = $this->model_venta->m_cargar_consulta_externa_GRAPH($allInputs);
        $allInputs['idTipoRango'] = 2;
      }
      
      $longMonthArray = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SET","OCT","NOV","DIC");
      $longMonthTableArray = array(" ","ENE","E%","FEB","F%","MAR","MR%","ABR","AB%","MAY","MY%","JUN","JN%","JUL","JL%","AGO","AG%","SET","S%","OCT","O%","NOV","N%","DIC","D%","TOTAL");
      $countArrayMesesTabla = count($longMonthTableArray);
      $arrAnos = array();
      if($allInputs['idTipoRango'] == 2){
        $contDesde = (int)$allInputs['anioDesde'];        
        while ( $contDesde <= $allInputs['anioHasta'] ) { 
          $arrAnos[] = $contDesde;
          $contDesde++;
        }
      }else{
         $arrAnos[]=$allInputs['anio']; 
      }

    /* JSON DE GRAFICO */
   
      foreach ($arrAnos as $key => $value) { 
        $arrSeries[$key] = array(
          'name'=> $value,
          'data' => array()
        );
        $band = false;
        foreach ($arrLista as $keyLista => $rowLista) {
         // var_dump('$value', $value);
          //var_dump('anio', $row['anio']);          
          if( $value == $rowLista['anio'] ){
            $band = true;
            foreach ($rowLista as $keyRow => $valueRow) {
              //var_dump('$keyRow', $keyRow);
              if($valueRow != $value && $keyRow != 'total'){
                $arrSeries[$key]['data'][] = (float)$valueRow; 
              }
            }
          }
        } 

        if(!$band){
          //var_dump('$band', $band);
          //var_dump('$value', $value);
          foreach ($longMonthTableNumArray as $keylong => $valuelong) {    
            $arrSeries[$key]['data'][] = 0;     
          }
        }            
      }

      $tablaDatos = array();
      if($anioAnt != null){
        $valorAnt = $anioAnt[0]['diciembre'];
      }else{
        $valorAnt = 0;
      }
      
      foreach ($arrAnos as $key => $value) { 
        $band = false;
        $i=1;

        foreach ($arrLista as $keyLista => $rowLista) {         
          if( $value == $rowLista['anio'] ){
            $band = true;
            $tablaDatos[$key][$i-1] = $value;
            if ($keyLista > 0) {
              $valorAnt = $arrLista[$keyLista-1]['diciembre'];
            }           
            foreach ($rowLista as $keyRow => $valueRow) {
              $tablaDatos[$key][$i] = (float)$valueRow;
              if($valueRow != $value && $i < ($countArrayMesesTabla-2)){                
                if($valorAnt != 0 ){
                  $tablaDatos[$key][$i+1] = round((($valueRow/$valorAnt)-1)*100);
                }else{
                  $tablaDatos[$key][$i+1] = ' - ';
                }  
 
                if ($i < ($countArrayMesesTabla-2)) {
                  $valorAnt = (float)$valueRow; 
                }
                
                $i+=2;  
              }             
            }
          }
        } 

        if(!$band){  
          for ($i=0; $i <= $countArrayMesesTabla-2; $i++) {           
            if ($i == 0) {
              $tablaDatos[$key][$i] = $value;
            }else{
              $tablaDatos[$key][$i] = 0;
            }      
          }
        }            
      }
    /*var_dump($longMonthTableArray);
    var_dump($tablaDatos);  
    exit();*/
    $arrData['message'] = 'OK';
    $arrData['flag'] = 1;
    $arrData = array( 
      'xAxis'=> $longMonthArray,
      'series'=> $arrSeries,
      'columns'=> $longMonthTableArray,
      'tablaDatos'=> $tablaDatos,
      'tipoGraphic'=> 'line',
      'tieneTabla'=> TRUE
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }

  private function cabecera_receta_pdf($allInputs){
    $receta = $this->model_receta_medica->m_cargar_receta_medica_para_imprimir($allInputs['id']); // id = idreceta
    $diagnosticos = $this->model_atencion_medica->m_cargar_diagnosticos_de_atencion($allInputs);

    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(35,6,'NOMBRES Y APELLIDOS',0,0,'L');
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(113,6,utf8_decode($receta['paciente']),'B',0,'C');

    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(12,6,'Edad',0,0,'L');
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(30,6,utf8_decode(devolverEdad($receta['fecha_nacimiento']).' AÑOS'),'B',0,'C');

    $this->pdf->Ln(8);
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(10,6,'DNI',0,0,'L');
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(50,6,utf8_decode($receta['num_documento']),'B',0,'C');

    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(22,6,'ESPECIALIDAD',0,0,'L');
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(60,6,utf8_decode($receta['especialidad']),'B',0,'C');
    
    $this->pdf->Ln(8);
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(75,6,utf8_decode('DIAGNÓSTICO'),0,0,'L');
    $this->pdf->Cell(18,6,'CIE - 10',0,0,'C');
    $this->pdf->Cell(76,6,'');
    $this->pdf->Cell(18,6,'CIE - 10',0,0,'C');
    $this->pdf->Ln();

    $this->pdf->SetFont('Arial','',7);
    for ($i = 0; $i < 4; $i++) { 
      if(!empty($diagnosticos[$i])){
        if( strlen($diagnosticos[$i]['descripcion_cie']) >= 45){
          $diagnostico = substr($diagnosticos[$i]['descripcion_cie'], 0,45) . '...';
        }else{
          $diagnostico = $diagnosticos[$i]['descripcion_cie'];
        }
        $this->pdf->Cell(73,6,utf8_decode(strtoupper($diagnostico)),'B',0,'L');
        $this->pdf->Cell(3,6,'',0,0,'C');
        $this->pdf->Cell(18,6,utf8_decode(strtoupper($diagnosticos[$i]['codigo_cie'])),1,0,'C'); 
        $this->pdf->Cell(2,6,'',0,0,'C'); 
      }else{
        $this->pdf->Cell(73,6,'','B',0,'L');
        $this->pdf->Cell(3,6,'',0,0,'C');
        $this->pdf->Cell(18,6,'',1,0,'C'); 
        $this->pdf->Cell(2,6,'',0,0,'C');
      }
      if($i == 1){$this->pdf->Ln(8);}
    }
    $this->pdf->Ln(8);
     
    $this->pdf->SetTextColor(250,250,250); 
    $this->pdf->SetFont('Arial','BI',9);  
    $this->pdf->SetFillColor(277,30,30);
    $this->pdf->Cell(130,8,utf8_decode('DATOS DEL MEDICAMENTO O INSUMO MÉDICO'),1,0,'C',TRUE);
    $this->pdf->Cell(60,8,'INDICACIONES',1,0,'C',TRUE);
    
    $this->pdf->Ln();
    $this->pdf->SetTextColor(3,3,3);  
    $this->pdf->SetFillColor(250,250,250);
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(10,8,utf8_decode('N°'),1,0,'C',TRUE);
    $this->pdf->Cell(67,8,'NOMBRE DEL MEDICAMENTO (OBLIGATORIO DCI)',1,0,'C',TRUE);
    $this->pdf->Cell(17,8,utf8_decode('CONCENTR.'),1,0,'C',TRUE);
    $this->pdf->MultiCell(21,4,'FORMA FARMACEUT.',1,'C',TRUE);
    $x=$this->pdf->GetX();
    $y=$this->pdf->GetY();
    $this->pdf->SetXY($x+115,$y-8);
    $this->pdf->Cell(15,8,utf8_decode('CANTIDAD'),1,0,'C',TRUE);
    $this->pdf->SetFont('Arial','B',6);
    $this->pdf->MultiCell(60,8,utf8_decode('DOSIS, VÍA DE ADMINIST., FRECUENCIA, DURACIÓN'),1,'C',TRUE);
  }
  private function footer_receta_pdf($allInputs, $pagination){
    $receta = $this->model_receta_medica->m_cargar_receta_medica_para_imprimir($allInputs['id']); // id = idreceta
    $this->pdf->Ln(2);
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(38,5,utf8_decode('FECHA DE EXPEDICIÓN'),0,0,'L');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,5,date('d',strtotime($receta['fecha_receta'])).' / '.date('m',strtotime($receta['fecha_receta'])).' / '.date('Y',strtotime($receta['fecha_receta'])),1,0,'C');

    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(38,5,utf8_decode('FECHA DE VALIDEZ'),0,0,'L');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(30,5,'  /      /        ',1,0,'C');
    $this->pdf->Cell(35,6,'',0,0,'C');
    $this->pdf->Cell(80,6,'','B',0,'C');
    $this->pdf->Ln();
    $this->pdf->Cell(103,6,'',0,0,'C');
    $this->pdf->SetFont('Arial','B',7);
    $this->pdf->Cell(80,6,utf8_decode('SELLO / FIRMA / N° COLEGIO PROFESIONAL'),0,0,'C');
    $this->pdf->Ln();
    
    $this->pdf->SetFont('Arial','',6);
    $this->pdf->SetXY(10,138); 
    $this->pdf->Cell(100,4,utf8_decode($this->sessionHospital['username'] . ' - ' . date('d/M/Y - H:i:s')),0,0,'L');
    $this->pdf->SetXY(110,138); 
    $this->pdf->Cell(90,4,utf8_decode($pagination),0,0,'R');
  }
  public function report_imprimir_receta_pdf(){
    $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
    $this->pdf = new Fpdfext();
    $idsedeempresaadmin = $this->sessionHospital['idsedeempresaadmin'];
    $sede = $this->model_config->m_cargar_empresa_sede_activa(); 
    $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($idsedeempresaadmin);
    $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
    $empresaAdmin['mode_report'] = FALSE;

    mostrar_plantilla_pdf($this->pdf,$allInputs['titulo'],FALSE,$allInputs['tituloAbv'],$empresaAdmin,$sede);
    $this->pdf->AddPage('P','A4');
    $this->pdf->AliasNbPages();
    $this->cabecera_receta_pdf($allInputs);

    // RECUPERACION DE DATOS
    $detalle = $this->model_receta_medica->m_cargar_detalle_receta_medica_para_imprimir($allInputs['id']); // id = idreceta   
    $fill = TRUE;
    $j=1;
    $arrText = array();
    $cant = count($detalle);
    $pag = $cant/4;
    if ($pag > intval($pag)) {
      $pag = intval($pag) + 1;
    }
    $numPag = 1;
    $pagination='';

    // LINEA A LA MITAD
    /*$alto = $this->pdf->GetPageWidth();
    $yFinal = $alto / 2;
    $this->pdf->SetLineWidth(0.06);
    $this->pdf->SetDash(2,1); 
    $this->pdf->Line(0, $yFinal, 210, $yFinal);*/
    $cant -= 4;
    //var_dump($cant);
    foreach ($detalle as $key => $row) {
      if ( ($j%5) == 0 ) { 
        $pagination = $numPag .'/'. $pag;
        $this->footer_receta_pdf($allInputs, $pagination);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $this->cabecera_receta_pdf($allInputs);
        $numPag++;
        $cant -= 4;
       // var_dump($cant);
      }

      $this->pdf->SetFont('Arial','',6);
      $arrWidthCol = array(10,67,17,21,15,60);
      $arrHeaderAligns = array('C', 'L', 'C', 'C', 'C', 'L');
      $arrBoolMultiCell = array(0,1,0,1,0,1); // colocar 1 donde deseas utilizar multicell
      $countArray = count($arrWidthCol);
      $acumWidth = 0;
      $arrText = array( $j++, utf8_decode($row['denominacion']), utf8_decode($row['concentracion']),
        utf8_decode($row['forma_farmaceutica']), utf8_decode($row['cantidad']), utf8_decode($row['indicaciones']));
      $widthCell = $this->pdf->GetStringWidth($arrText[3]);
      $multiCell = 4;
      $cell = $multiCell*2;

      if($widthCell > 30){
        $multiCell = 4;  
        $cell = $multiCell*3; 
      }

      for ($i=0; $i < $countArray ; $i++) {
        $width = $this->pdf->GetStringWidth($arrText[$i]);
        if($arrBoolMultiCell[$i] == 1 && (($i == 1 && $width > 65 ) || ($i == 3 && $width > 20 ) || ($i == 5 && $width > 65 ))) {
            $this->pdf->MultiCell($arrWidthCol[$i],$multiCell,$arrText[$i],1,$arrHeaderAligns[$i],TRUE);
            $x=$this->pdf->GetX();
            $y=$this->pdf->GetY();
            $acumWidth += $arrWidthCol[$i];
            $this->pdf->SetXY($x+$acumWidth,$y-$cell);           
        }else{
          $this->pdf->Cell($arrWidthCol[$i],$cell,$arrText[$i],1,0,$arrHeaderAligns[$i],TRUE); 
          $acumWidth += $arrWidthCol[$i]; 
        } 
      }
      $this->pdf->Ln($cell);    
    }
 //exit();
    $cant = $cant*(-1);
    $cell = 8;
    for ($i=0; $i < $cant ; $i++) { 
      for ($k=0; $k < $countArray; $k++) { 
        if($k == 0){
          $this->pdf->Cell($arrWidthCol[$k],$cell,$j,1,0,$arrHeaderAligns[$k],TRUE);    
        }else{   
          $this->pdf->Cell($arrWidthCol[$k],$cell,' ',1,0);    
        }
      }    
      $this->pdf->Ln($cell);
      $j++;
    }
    $pagination = $numPag .'/'. $pag;
    $this->footer_receta_pdf($allInputs,$pagination);
    

    $arrData['message'] = 'ERROR';
    $arrData['flag'] = 2;
    $timestamp = date('YmdHis');
    if($this->pdf->Output( 'F','assets/img/dinamic/pdfTemporales/tempPDF_' . $timestamp . '.pdf' )){
      $arrData['message'] = 'OK';
      $arrData['flag'] = 1;
    }
    $arrData = array(
      'urlTempPDF'=> 'assets/img/dinamic/pdfTemporales/tempPDF_' . $timestamp . '.pdf'
    );
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($arrData));
  }
}
?>
