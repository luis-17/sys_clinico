<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CentralReportesFarmaciaMPDF extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->helper(array('security','reportes_helper','imagen_helper','fechas_helper','otros_helper','pdf_helper'));
        $this->load->model(array('model_caja_farmacia','model_config','model_empresa_admin','model_venta_farmacia','model_medicamento_almacen','model_traslado_farmacia', 'model_estadisticas','model_entrada_farmacia','model_orden_compra')); 
        //cache 
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
        $this->output->set_header("Pragma: no-cache");
        $this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
        $this->load->library('excel');
        $this->load->library('fpdfext');
        date_default_timezone_set("America/Lima");
    }
    public function report_traslado(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        // RECUPERACION DE DATOS

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Cód. Movimiento'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['idmovimiento1']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6, utf8_decode('Tipo Movimiento'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,'TRASLADO');
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Fecha de Traslado'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(30,6, $allInputs['fecha_movimiento']);
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,'Responsable');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x,$y+1);
        $this->pdf->MultiCell(65,4, utf8_decode($allInputs['usuario']),0,'L');
        $x_final_izquierda = $this->pdf->GetX();
        $y_final_izquierda = $this->pdf->GetY();

        // ============ columna derecha
        $this->pdf->SetXY(110,25);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(37,6, utf8_decode('Almacén'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x,$y+1);
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->MultiCell(50,4,utf8_decode($allInputs['almacen']),0);
        $y=$this->pdf->GetY();
        $this->pdf->SetXY(110,$y);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(37,6, utf8_decode('Sub-Almacén origen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        //$this->pdf->SetXY($x,$y+1);
        $this->pdf->MultiCell(50,6,utf8_decode($allInputs['subAlmacenOrigen']));
        $y=$this->pdf->GetY();
        $this->pdf->SetXY(110,$y);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(37,6, utf8_decode('Sub-Almacén destino'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        //$this->pdf->SetXY($x,$y+1);
        $this->pdf->MultiCell(50,6,utf8_decode($allInputs['subAlmacenDestino'])); 

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
        
        $this->pdf->Cell(10,7,'ITEM',1,0,'C',TRUE);
        $this->pdf->Cell(18,7,utf8_decode('CÓD. PROD.'),1,0,'L',TRUE);
        $this->pdf->Cell(70,7,'PRODUCTO',1,0,'L',TRUE);
        $this->pdf->Cell(50,7,'LABORATORIO',1,0,'L',TRUE);
        $this->pdf->Cell(15,7,utf8_decode('CANT.'),1,0,'C',TRUE);
        $this->pdf->Cell(18,7,utf8_decode('IMPORTE'),1,0,'C',TRUE);
        $this->pdf->Ln(7);

        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(204,204,204); // gris 
        $this->pdf->SetLineWidth(.2);
        
        $i = 1;
        $detalle = $this->model_traslado_farmacia->m_cargar_detalle_traslado($allInputs,FALSE);
        $this->pdf->SetWidths(array(10,18,70,50,15,18));
        $this->pdf->SetAligns(array('C', 'C','L', 'L', 'C', 'C'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        foreach ($detalle as $row) { 
            $rowImporte = 0; 
            if( $row['precio_ultima_compra_str'] > 0 ){ // precio_ultima_compra_str_str 

                $rowImporte = $row['precio_ultima_compra_str'] * $row['cantidad'];
                //var_dump('aqui toy',$row['precio_ultima_compra_str'],$rowImporte); exit();
            } 
            $fill = !$fill;
            $this->pdf->Row( 
                array( 
                  $i,
                  $row['idmedicamento'],
                  utf8_decode(trim($row['denominacion'])),
                  utf8_decode($row['nombre_lab']),
                  $row['cantidad'],
                  number_format($rowImporte,2) 
                ),
                $fill,1
            );
            $i++;
            //var_dump($row['precio_ultima_compra_str']);
        } 
        //exit();
        $this->pdf->SetAligns(array('J'));
        $this->pdf->Ln(10);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(140,5,'Observaciones');
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',8);

        $this->pdf->SetWidths(array(190));
        $this->pdf->TextArea(array(empty($allInputs['motivo_movimiento'])? '':$allInputs['motivo_movimiento']),0,0,FALSE,5,20);

        

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
    public function report_control_stock_farmacia(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        
        // RECUPERACION DE DATOS
        $lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_agotarse(FALSE,$allInputs);
        //$empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']); 
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['empresa']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['sede']));
        $this->pdf->Ln(4);

        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);
        
        $this->pdf->Cell(10,8,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(70,8,utf8_decode('MEDICAMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell(30,8,utf8_decode('LABORATORIO'),1,0,'L',TRUE);
        $this->pdf->MultiCell(15,4,'STOCK MINIMO',1,'C',TRUE);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+125,$y-8);
        $this->pdf->MultiCell(15,4,'STOCK CRITICO',1,'C',TRUE);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+140,$y-8);
        $this->pdf->MultiCell(15,4,'STOCK MAXIMO',1,'C',TRUE);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+155,$y-8);
        $this->pdf->MultiCell(15,4,'STOCK ACTUAL',1,'C',TRUE);
        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+170,$y-8);
        $this->pdf->Cell(20,8,utf8_decode('ESTADO'),1,0,'C',TRUE);
        $this->pdf->Ln(8);
        // $this->pdf->Cell(20,6,utf8_decode('STOCK CRITICO'),1,0,'R',TRUE);
        // $this->pdf->Cell(20,6,utf8_decode('STOCK MAXIMO'),1,0,'R',TRUE);
        // $this->pdf->Cell(20,6,utf8_decode('STOCK ACTUAL'),1,0,'R',TRUE);

        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;
        $cantCritico = 0;
        $cantMinimo = 0;
        $cantAgotado = 0;

        $this->pdf->SetWidths(array(10,70,30,15,15,15,15,20));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'R', 'R', 'R', 'R','C'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
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
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item,
                    utf8_decode(trim($row['denominacion'])),
                    utf8_decode(trim($row['nombre_lab'])),
                    $row['stock_minimo'],
                    $row['stock_critico'],
                    $row['stock_maximo'],
                    $row['stock_actual_malm'],
                    $estado
                ),
                $fill,1
            );
            $item++;
        }
        //$total2 = 'S/. 1,000.00';
        // $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->Cell(140,5,'',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(35,5,utf8_decode('TOTAL CRITICOS'),0,0,'L');
        $this->pdf->Cell(3,5,':',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(12,5,$cantCritico,0,0,'R');
        $this->pdf->Ln(4);
        $this->pdf->Cell(140,5,'',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(35,5,utf8_decode('TOTAL MINIMOS'),0,0,'L');
        $this->pdf->Cell(3,5,':',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(12,5,$cantMinimo,0,0,'R');
        $this->pdf->Ln(4);
        $this->pdf->Cell(140,5,'',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(35,5,utf8_decode('TOTAL AGOTADOS'),0,0,'L');
        $this->pdf->Cell(3,5,':',0,0,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(12,5,$cantAgotado,0,0,'R');
        $this->pdf->Ln(4);


        

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
    // central de reportes
    public function report_resumen_ventas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();

        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
       
        //var_dump($empresaAdmin); exit();
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        // $empresaAdmin['mode_report'] = FALSE;
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);
   
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'], $empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        
        // RECUPERACION DE DATOS
        $lista = $this->model_caja_farmacia->m_cargar_apertura_caja(FALSE,$allInputs);
        // $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        // var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);
        
        $this->pdf->Cell(20,6,utf8_decode('FECHA'),1,0,'L',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('CAJA'),1,0,'L',TRUE);
        $this->pdf->Cell(50,6,utf8_decode('CAJERO'),1,0,'L',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('H.APERTURA'),1,0,'C',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('H.CIERRE'),1,0,'C',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('Nº ANULADOS'),1,0,'C',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('Nº NCR'),1,0,'C',TRUE);
        $this->pdf->Cell(20,6,utf8_decode('MONTO (S/.)'),1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $total = 0;
        $sumNotasCredito = 0;
        //$cantidad_venta = 0;
        foreach ($lista as $row) {
            $fill = !$fill;
            $this->pdf->SetWidths(array(20,20,50,20,20,20,20,20));
            $this->pdf->SetAligns(array('L', 'L', 'L', 'C', 'C', 'C', 'C', 'R'));
            $this->pdf->SetFillColor(230, 240, 250);
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Row(
                array(
                    formatoFechaReporte3($row['fecha_apertura']),
                    utf8_decode('Caja N° '. $row['numero_caja']),
                    utf8_decode($row['nombres'] . ' ' . $row['apellido_paterno']),
                    darFormatoHora($row['fecha_apertura']),
                    darFormatoHora($row['fecha_cierre']),
                    utf8_decode($row['cantidad_anulado']),
                    utf8_decode($row['cantidad_ncr']),
                    utf8_decode($row['total_importe']),
                ),
                $fill,1
            );
            $total += $row['total_importe'];
            $sumNotasCredito += ($row['total_ncr'] + $row['total_extorno']);
            //$cantidad_venta += $row['cantidad_venta'];
        }
        //$total2 = 'S/. 1,000.00';
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,utf8_decode('TOTAL'),0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,'S/. ' . number_format($total, 2),0,0,'R');
        $this->pdf->Ln(5);
        // $this->pdf->Cell(round($width+20),6,$total2,1,0,'R');

        $listaPorMedioPago = $this->model_caja_farmacia->m_cargar_ventas_por_medio_pago(FALSE,$allInputs);
        // var_dump($listaPorMedioPago); exit();
        $arrListadoMP = array();
        $totalMP = 0;
        $totalCant = 0;

        $this->pdf->SetFillColor(150, 190, 240); // celeste acero
        // $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetLineWidth(.2);

        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(30,5,utf8_decode('MEDIO DE PAGO'),1,0,'L',TRUE);
        $this->pdf->Cell(15,5,utf8_decode('CANTIDAD'),1,0,'C',TRUE);
        $this->pdf->Cell(30,5,utf8_decode('TOTAL'),1,0,'R',TRUE);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',7);
        foreach ($listaPorMedioPago as $row) { 
          $totalMP += $row['total'];
          $totalCant += $row['cantidad'];
          $cantidad_venta = empty($row['cantidad']) ? '-' : $row['cantidad'];
          $subtotal = empty($row['total']) ? '-' : number_format($row['total'],2);

          $this->pdf->Cell(30,5,utf8_decode($row['descripcion_med']),1,0,'L');
          $this->pdf->Cell(15,5,$cantidad_venta,1,0,'R');
          $this->pdf->Cell(30,5,$subtotal,1,0,'R');
          $this->pdf->Ln(5);
        }
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->Cell(30,5,utf8_decode('TOTAL'),1,0,'L');
        $this->pdf->Cell(15,5,$totalCant,1,0,'R');
        $this->pdf->Cell(30,5,number_format($totalMP, 2),1,0,'R');
        $this->pdf->Ln(10);

        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->Cell(30,5,utf8_decode('NOTA DE CRÉDITO'),1,0,'L');
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(15,5,number_format($sumNotasCredito, 2),1,0,'R');
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->Cell(30,5,utf8_decode('TOTAL EN CAJA'),1,0,'L');
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->Cell(15,5,number_format($totalMP + $sumNotasCredito, 2),1,0,'R');
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
    public function report_detalle_por_venta_caja_farmacia(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        
        // RECUPERACION DE DATOS
        $listaCajas = $this->model_caja_farmacia->m_cargar_cajas_diarias_usuario($allInputs);
        if(!empty($listaCajas)){
          $empresa_admin = $this->model_empresa_admin->m_cargar_sede_empresa_admin_de_esta_caja($listaCajas[0]['idcaja']);
        }
         
        // var_dump($listaCajas); exit();

        foreach ($listaCajas as $key => $value) {
            $listaVentasDeCaja = $this->model_venta_farmacia->m_cargar_ventas_esta_caja_farmacia($value);
            $listaNCDeCaja = $this->model_venta_farmacia->m_cargar_nc_esta_caja_farmacia($value);
            // var_dump($listaVentasDeCaja); exit();

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
            $this->pdf->SetFont('Arial','B',9);
            $this->pdf->Cell(28,6,utf8_decode('Fecha'));
            $this->pdf->Cell(3,6,':',0,0,'C');
            $this->pdf->SetFont('Arial','',8);
            $this->pdf->Cell(75,6,utf8_decode($allInputs['fecha']));
            $this->pdf->Ln(4);
            $this->pdf->SetFont('Arial','B',9);
            $this->pdf->Cell(28,6,utf8_decode('Caja'));
            $this->pdf->Cell(3,6,':',0,0,'C');
            $this->pdf->SetFont('Arial','',8);
            $this->pdf->Cell(75,6,utf8_decode($allInputs['caja']['descripcion']));
            $this->pdf->Ln(4);
            $this->pdf->SetFont('Arial','B',9);
            $this->pdf->Cell(28,6,utf8_decode('Usuario'));
            $this->pdf->Cell(3,6,':',0,0,'C');
            $this->pdf->SetFont('Arial','',8);
            $this->pdf->Cell(75,6,utf8_decode($allInputs['usuario']['descripcion']));
            $this->pdf->Ln(10);
            // APARTADO DETALLE
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->SetFillColor(150, 190, 240);
            
            $this->pdf->Cell(20,5,utf8_decode('HORA'),1,0,'L',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('Nº ORDEN'),1,0,'L',TRUE);
            $this->pdf->Cell(25,5,utf8_decode('TICKET'),1,0,'L',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('TIPO DOCUMENTO'),1,0,'L',TRUE);
            //$this->pdf->Cell(20,5,utf8_decode('CLIENTE'),1,0,'C',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('MEDIO DE PAGO'),1,0,'C',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('MONTO'),1,0,'R',TRUE);
            $this->pdf->Cell(25,5,utf8_decode('ESTADO'),1,0,'C',TRUE);

            
            $this->pdf->Ln(5);
            $this->pdf->SetFont('Arial','',8);
            $fill = TRUE;
            $this->pdf->SetDrawColor(31,31,31); // gris oscuro
            // $this->pdf->SetDrawColor(204,204,204); // gris
            $this->pdf->SetLineWidth(.2);
            // -- creacion de los listados del detalle
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
                if( $valueDetAux['tipofila'] == 'v' && $valueDetAux['idtipodocumento'] != '7'){ 
                  $valueDetGen['idmediopago'] = $valueDetAux['idmediopago'];
                  $valueDetGen['descripcion_med'] = $valueDetAux['descripcion_med'];
                  $valueDetGen['cantidad_gen'] = 0;
                  $valueDetGen['monto_gen'] = 0;
                  $arrSoloMediosPago[$valueDetAux['idmediopago']] = $valueDetGen;
                }
            }
            foreach ($listaVentasDeCaja as $key => $valueDet) { 
                // $arrSoloVentas[$valueDet['idventa']] = $valueDet; 
                if( $valueDet['tipofila'] == 'v' && $valueDet['idtipodocumento'] != '7' ){ 
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
            $this->pdf->SetWidths(array(20,30,25,30,30,30,25));
            $this->pdf->SetAligns(array('L', 'L', 'L', 'L', 'C', 'R', 'C'));
            $this->pdf->SetFillColor(230, 240, 250); // celeste bajito
            $this->pdf->SetFont('Arial','',7);
            foreach ($listaVentasDeCaja as $row) {
                $strFechaVenta = $row['fecha_movimiento'];
                if($row['tipofila'] == 'v'){
                    $sumTotalVenta += $row['total_a_pagar'];
                }
                
                $fill = !$fill;
                $this->pdf->Row(
                    array(
                        date('h:i:s a',strtotime($strFechaVenta)),
                        $row['orden_venta'],
                        $row['ticket_venta'],
                        strtoupper($row['descripcion_td']),
                        // strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
                        strtoupper($row['descripcion_med']),
                        $row['total_a_pagar'],
                        ($row['tipofila'] == 'a' ? 'ANULADO' : ' ')
                    ),
                    $fill,1
                );
            }
            $this->pdf->Ln(5);
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(75,5,'',0,0,'L');

            $this->pdf->Cell(25,5,utf8_decode('MEDIO DE PAGO'),1,0,'L');
            $this->pdf->Cell(10,5,utf8_decode('CANT.'),1,0,'L');
            $this->pdf->Cell(20,5,utf8_decode('MONTO'),1,0,'L');
            $this->pdf->Ln(5);
            $linea = 1;
            foreach ($arrSoloMediosPago as $key => $valueSMP) {
                $linea++;
                $this->pdf->Cell(75,5,'',0,0,'L');
                $this->pdf->Cell(25,5,utf8_decode($valueSMP['descripcion_med']),1,0,'L');
                $this->pdf->Cell(10,5,$valueSMP['cantidad_gen'],1,0,'L');
                $this->pdf->Cell(20,5,number_format($valueSMP['monto_gen'],2),1,0,'L');
                $this->pdf->Cell(5,5,'',0,0,'L');
                $this->pdf->Ln(5);
            }
            $y=$this->pdf->GetY();
            $this->pdf->SetXY(140,($y-5*$linea));
            $this->pdf->Cell(5,5,'',0,0,'L');
            $this->pdf->Cell(30,5,utf8_decode('CANT. VENTAS'),1,0,'L');
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Cell(25,5,$countVentas,1,0,'R');
            $this->pdf->Ln(5);

            
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(135,5,'',0,0,'L');
            $this->pdf->Cell(30,5,utf8_decode('CANT. ANULADOS'),1,0,'L');
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Cell(25,5,$countAnulados,1,0,'R');
            $this->pdf->Ln(5);

            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(135,5,'',0,0,'L');
            $this->pdf->Cell(30,5,utf8_decode('TOTAL VENTAS'),1,0,'L');
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Cell(25,5,number_format($sumTotalVenta,2),1,0,'R');
            $this->pdf->Ln(15);

            $this->pdf->SetFillColor(150, 190, 240);
            $this->pdf->SetFont('Arial','B',8);
            $this->pdf->Cell(0,5,utf8_decode('NOTAS DE CRÉDITO'),1,0,'C');
            $this->pdf->Ln(5);
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(20,5,utf8_decode('HORA'),1,0,'C',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('TICKET NC'),1,0,'L',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('Nº ORDEN ORIGEN'),1,0,'L',TRUE);
            $this->pdf->Cell(30,5,utf8_decode('TICKET ORIGEN'),1,0,'L',TRUE);
            $this->pdf->Cell(55,5,utf8_decode('CLIENTE'),1,0,'L',TRUE);
            $this->pdf->Cell(25,5,utf8_decode('MONTO'),1,0,'R',TRUE);
            $this->pdf->Ln(5);
            // DETALLE DE LAS NOTAS DE CREDITO
            $this->pdf->SetWidths(array(20,30,30,30,55,25));
            $this->pdf->SetAligns(array('C', 'L', 'L', 'L', 'L', 'R'));
            $this->pdf->SetFillColor(230, 240, 250); // celeste bajito
            $this->pdf->SetFont('Arial','',7);
            $fill = TRUE;
            if(count($listaNCDeCaja) > 0){
                foreach ($listaNCDeCaja as $row) {
                    $strFechaVenta = $row['fecha_movimiento'];
                    $sumTotalNCR += $row['total_a_pagar'];
                    $countNCR++;
                    $fill = !$fill;
                    $this->pdf->Row(
                        array(
                            date('h:i:s a',strtotime($strFechaVenta)),
                            $row['ticket_venta'],
                            $row['orden_venta_origen'],
                            $row['ticket_venta_origen'],
                            strtoupper($row['nombres'].' '.$row['apellido_paterno'].' '.$row['apellido_materno']),
                            $row['total_a_pagar']
                        ),
                        $fill,1
                    );
                }
            }else{
                $this->pdf->Cell(0,5,utf8_decode('No se encontró Notas de Crédito'),1,0,'C');
                $this->pdf->Ln(5);
            }
            
            $this->pdf->Ln(5);
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(135,5,'',0,0,'L');
            $this->pdf->Cell(30,5,utf8_decode('CANT. N.CRÉDITO'),1,0,'L');
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Cell(25,5,$countNCR,1,0,'R');
            $this->pdf->Ln(5);

            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->Cell(135,5,'',0,0,'L');
            $this->pdf->Cell(30,5,utf8_decode('TOTAL N.CRÉDITO'),1,0,'L');
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Cell(25,5,number_format($sumTotalNCR,2),1,0,'R');
            $this->pdf->Ln(10);

            $this->pdf->SetFont('Arial','B',14);
            $this->pdf->Cell(105,5,'',0,0,'L');
            $this->pdf->Cell(50,5,utf8_decode('TOTAL EN CAJA'),0,0,'L');
            $this->pdf->SetFont('Arial','',14);
            $this->pdf->Cell(5,5,'S/.',0,0,'L');
            
            $this->pdf->Cell(30,5,number_format($sumTotalVenta + $sumTotalNCR, 2),0,0,'R');
            $this->pdf->Ln(5);
            $this->pdf->AddPage();
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
    public function report_medicamentos_vendidos_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        // var_dump($allInputs); exit();
        // RECUPERACION DE DATOS
        $lista = $this->model_venta_farmacia->m_cargar_medicamentos_vendidos_desde_hasta($allInputs);

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->SetFillColor(150, 190, 240);
        $columna_laboratorio = 70;
        $columna_medicamento = 75;
        $columna_cantidad = 20;
        $columna_monto = 20;
        $texto_cantidad = utf8_decode('CANTIDAD');
        $texto_monto = utf8_decode('MONTO (S/.)');
        $texto_total = 'TOTAL';

        if( $allInputs['modalidad']['id'] == 'cantidad' ){
            $columna_monto = 0;
            $texto_monto = '';
            $texto_total = utf8_decode('CANTIDAD TOTAL');
        }elseif($allInputs['modalidad']['id'] == 'monto'){
            $columna_cantidad = 0.1;
            $texto_cantidad = '';
            $texto_total = utf8_decode('MONTO TOTAL');
        }else{
            $columna_medicamento = 105;
        }
        $this->pdf->Cell(10,6,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,6,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_laboratorio,6,utf8_decode('LABORATORIO'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_medicamento,6,utf8_decode('MEDICAMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_cantidad,6,$texto_cantidad,1,0,'C',TRUE);
        $this->pdf->Cell($columna_monto,6,$texto_monto,1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $monto_total = 0;
        $cantidad_total = 0;
        $this->pdf->SetWidths(array(10,15,$columna_laboratorio,$columna_medicamento,$columna_cantidad,$columna_monto));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'L','C', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill;
            if( $allInputs['modalidad']['id'] == 'monto' ){
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        ($row['nombre_lab']),
                        utf8_decode($row['denominacion']),
                        '',
                        number_format(utf8_decode($row['monto']),2)
                    ),
                    $fill,1
                );
            }elseif($allInputs['modalidad']['id'] == 'cantidad'){
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        ($row['nombre_lab']),
                        utf8_decode($row['denominacion']),
                        utf8_decode($row['cantidad']),
                        ''
                    ),
                    $fill,1
                );
            }else{
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        ($row['nombre_lab']),
                        utf8_decode($row['denominacion']),
                        utf8_decode($row['cantidad']),
                        number_format(utf8_decode($row['monto']),2)
                    ),
                    $fill,1
                );  
            }
            
            $monto_total += $row['monto'];
            $cantidad_total += ($row['cantidad']);
        }
        //$total2 = 'S/. 1,000.00';
        if( $allInputs['modalidad']['id'] == 'cantidad' ){
            $total = $cantidad_total;
            $alinear = 'C';
        }else{
            $total = 'S/. ' . number_format($monto_total, 2);
            $alinear = 'R';
        }
        
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,$texto_total,0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$total,0,0,$alinear);
        $this->pdf->Ln(5);
        // $this->pdf->Cell(round($width+20),6,$total2,1,0,'R');

        

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
    public function report_medicamentos_comprados_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        // var_dump($allInputs); exit();
        // RECUPERACION DE DATOS
        $lista = $this->model_venta_farmacia->m_cargar_medicamentos_comprados_desde_hasta($allInputs);

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->SetFillColor(150, 190, 240);
        $columna_medicamento = 135;
        $columna_cantidad = 30;
        $columna_monto = 30;
        $texto_cantidad = utf8_decode('CANTIDAD');
        $texto_monto = utf8_decode('MONTO (S/.)');
        $texto_total = 'TOTAL';

        if( $allInputs['modalidad']['id'] == 'cantidad' ){
            $columna_monto = 0;
            $texto_monto = '';
            $texto_total = utf8_decode('CANTIDAD TOTAL');
        }elseif($allInputs['modalidad']['id'] == 'monto'){
            $columna_cantidad = 0.1;
            $texto_cantidad = '';
            $texto_total = utf8_decode('MONTO TOTAL');
        }else{
            $columna_medicamento = 105;
        }
        $this->pdf->Cell(10,6,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,6,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_medicamento,6,utf8_decode('MEDICAMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_cantidad,6,$texto_cantidad,1,0,'C',TRUE);
        $this->pdf->Cell($columna_monto,6,$texto_monto,1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $monto_total = 0;
        $cantidad_total = 0;
        $this->pdf->SetWidths(array(10,15,$columna_medicamento,$columna_cantidad,$columna_monto));
        $this->pdf->SetAligns(array('L', 'L', 'L','C', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill;
            if( $allInputs['modalidad']['id'] == 'monto' ){
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        utf8_decode($row['denominacion']),
                        '',
                        number_format(utf8_decode($row['monto']),2)
                    ),
                    $fill,1
                );
            }elseif($allInputs['modalidad']['id'] == 'cantidad'){
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        utf8_decode($row['denominacion']),
                        utf8_decode($row['cantidad']),
                        ''
                    ),
                    $fill,1
                );
            }else{
                $this->pdf->Row(
                    array(
                        $item++,
                        ($row['idmedicamento']),
                        utf8_decode($row['denominacion']),
                        utf8_decode($row['cantidad']),
                        number_format(utf8_decode($row['monto']),2)
                    ),
                    $fill,1
                );  
            }
            
            $monto_total += $row['monto'];
            $cantidad_total += ($row['cantidad']);
        }
        //$total2 = 'S/. 1,000.00';
        if( $allInputs['modalidad']['id'] == 'cantidad' ){
            $total = $cantidad_total;
            $alinear = 'C';
        }else{
            $total = 'S/. ' . number_format($monto_total, 2);
            $alinear = 'R';
        }
        
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,$texto_total,0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$total,0,0,$alinear);
        $this->pdf->Ln(5);
        // $this->pdf->Cell(round($width+20),6,$total2,1,0,'R');

        

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
    public function report_medicos_en_venta_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        
        // RECUPERACION DE DATOS
        $lista = $this->model_venta_farmacia->m_cargar_medicos_en_ventas_desde_hasta($allInputs);
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);
        
        $this->pdf->Cell(30,6,utf8_decode('Nº DOCUMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell(100,6,utf8_decode('PROFESIONAL MEDICO'),1,0,'L',TRUE);
        $this->pdf->Cell(30,6,utf8_decode('CANTIDAD'),1,0,'L',TRUE);
        $this->pdf->Cell(30,6,utf8_decode('MONTO (S/.)'),1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $total = 0;
        $cantidad_venta = 0;
        $this->pdf->SetWidths(array(30,100,30,30));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        foreach ($lista as $row) {
            $medico = $row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', ' . $row['med_nombres'];
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    ($row['med_numero_documento']),
                    utf8_decode($medico),
                    utf8_decode($row['cantidad']),
                    number_format(utf8_decode($row['monto']),2)
                ),
                $fill,1
            );
            $total += $row['monto'];
            $cantidad_venta += ($row['cantidad']);
        }
        //$total2 = 'S/. 1,000.00';
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,utf8_decode('TOTAL'),0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,'S/. ' . number_format($total, 2),0,0,'R');
        $this->pdf->Ln(5);
        // $this->pdf->Cell(round($width+20),6,$total2,1,0,'R');

        

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
    public function report_medicos_medicamento_detalle_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        
        // RECUPERACION DE DATOS
        $lista = $this->model_venta_farmacia->m_cargar_medicos_en_ventas_detalle_desde_hasta($allInputs);
        $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $desde = str_replace("-", "/", $allInputs['desde']) . ' - ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' - ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        $arrListadoProd = array();
        $arrPrincipal = array();
        $arrDet = array();
        foreach ($lista as $key => $row) {

            $arrPrincipal[$row['idmedico']]['idmedico'] = $row['idmedico'];
            $arrPrincipal[$row['idmedico']]['medico'] = $row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', ' . $row['med_nombres'];
            $arrPrincipal[$row['idmedico']]['num_documento'] = $row['med_numero_documento'];
            $arrPrincipal[$row['idmedico']]['medicamentos'] = array();
        }
        foreach ($lista as $key => $det) {
            array_push($arrDet, array(
                'idmedicamento' => $det['idmedicamento'],
                'medicamento' => $det['medicamento'],
                'cantidad' => $det['cantidad'],
                'monto' => $det['monto']    
                )
            );
            $arrPrincipal[$det['idmedico']]['medicamentos'] = $arrDet;
        }
            
        $arrSort = array();
        $arrPrincipal = array_merge($arrSort,$arrPrincipal);

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
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(8);
        foreach ($arrPrincipal as $row) {
            $this->pdf->SetFillColor(247, 247, 247);
            $this->pdf->SetFont('Arial','',8);
            $this->pdf->Cell(0,6,utf8_decode($row['medico']),1,0,'C',TRUE);
            $this->pdf->Ln(8);
            // APARTADO MEDICAMENTOS
            $this->pdf->SetFont('Arial','B',7);
            $this->pdf->SetFillColor(150, 190, 240);
            
            $this->pdf->Cell(30,6,utf8_decode('COD.MED.'),1,0,'L',TRUE);
            $this->pdf->Cell(100,6,utf8_decode('MEDICAMENTO'),1,0,'L',TRUE);
            $this->pdf->Cell(30,6,utf8_decode('CANTIDAD'),1,0,'C',TRUE);
            $this->pdf->Cell(30,6,utf8_decode('MONTO (S/.)'),1,0,'R',TRUE);
            $this->pdf->Ln(6);
            $this->pdf->SetFont('Arial','',8);
            $fill = TRUE;
            $this->pdf->SetDrawColor(31,31,31); // gris oscuro
            // $this->pdf->SetDrawColor(204,204,204); // gris
            $this->pdf->SetLineWidth(.2);
            $total = 0;
            $cantidad_venta = 0;
            $this->pdf->SetWidths(array(30,100,30,30));
            $this->pdf->SetAligns(array('L', 'L', 'C', 'R'));
            $this->pdf->SetFillColor(230, 240, 250);
            $this->pdf->SetFont('Arial','',7);
            foreach ($row['medicamentos'] as $rowMed) {
                $fill = !$fill;
                $this->pdf->Row(
                    array(
                        ($rowMed['idmedicamento']),
                        utf8_decode(trim($rowMed['medicamento'])),
                        utf8_decode($rowMed['cantidad']),
                        number_format(utf8_decode($rowMed['monto']),2)
                    ),
                    $fill,1
                );
                $total += $rowMed['monto'];
                $cantidad_venta += ($rowMed['cantidad']);
            }
            $this->pdf->Ln(1);
            $this->pdf->SetFont('Arial','',10);
            $this->pdf->Cell(30,6,'');
            $this->pdf->Cell(97,6,utf8_decode('TOTAL'),0,0,'R');
            $this->pdf->Cell(3,6,':',0,0,'C');
            $this->pdf->SetFont('Arial','',10);
            $this->pdf->Cell(30,6,$cantidad_venta,0,0,'C');
            $this->pdf->Cell(30,6,'S/. ' . number_format($total, 2),0,0,'R');
            $this->pdf->Ln(10);
        }

       
        /*
        
        
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);
        
        $this->pdf->Cell(30,6,utf8_decode('Nº DOCUMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell(100,6,utf8_decode('PROFESIONAL MEDICO'),1,0,'L',TRUE);
        $this->pdf->Cell(30,6,utf8_decode('CANTIDAD'),1,0,'L',TRUE);
        $this->pdf->Cell(30,6,utf8_decode('MONTO (S/.)'),1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $total = 0;
        $cantidad_venta = 0;
        $this->pdf->SetWidths(array(30,100,30,30));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        foreach ($lista as $row) {
            $medico = $row['med_apellido_paterno'] . ' ' . $row['med_apellido_materno'] . ', ' . $row['med_nombres'];
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    ($row['med_numero_documento']),
                    utf8_decode($medico),
                    utf8_decode($row['cantidad']),
                    number_format(utf8_decode($row['monto']),2)
                ),
                $fill,1
            );
            $total += $row['monto'];
            $cantidad_venta += ($row['cantidad']);
        }
        //$total2 = 'S/. 1,000.00';
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,utf8_decode('TOTAL'),0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,'S/. ' . number_format($total, 2),0,0,'R');
        $this->pdf->Ln(5);
        // $this->pdf->Cell(round($width+20),6,$total2,1,0,'R');
        */
        

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
    public function report_ventas_medicamentos_por_condicion_venta(){ 
        ini_set('xdebug.var_display_max_depth', 10); 
        ini_set('xdebug.var_display_max_children', 1024); 
        ini_set('xdebug.var_display_max_data', 1024); 

        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        $fEmpresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']); 

        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->AddPage('L','A4');
        $this->pdf->AliasNbPages();

        $this->pdf->SetFont('Arial','B',11); 
        $this->pdf->Cell(40,4,'SEDE - EMPRESA'); 
        $this->pdf->Cell(2,5,':'); 
        $this->pdf->SetFont('Arial','',10); 
        $this->pdf->Cell(40,4,utf8_decode($fEmpresaAdmin['sede']).' - '.utf8_decode($fEmpresaAdmin['razon_social'])); 
        $this->pdf->Ln(); 

        $this->pdf->SetFont('Arial','B',11);
        $this->pdf->Cell(40,4,utf8_decode('DESDE'));
        $this->pdf->Cell(2,5,':',0,0,'C');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->Cell(40,4,utf8_decode($desde));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',11);
        $this->pdf->Cell(40,4,utf8_decode('HASTA'));
        $this->pdf->Cell(2,5,':',0,0,'C');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->Cell(40,4,utf8_decode($hasta));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',11); 
        $this->pdf->Cell(40,4,'CONDICIONES DE VENTA: '); 
        $this->pdf->Ln(); 
        $allInputs['arrCondicionesVenta'] = array();
        foreach ($allInputs['condicionVentaSeleccionadas'] as $key => $row) { 
          $this->pdf->SetFont('Arial','',10);
          $this->pdf->Cell(40,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
          $this->pdf->Ln();
          $allInputs['arrCondicionesVenta'][] = $row['id'];
        } 
        
        /* TRATAMIENTO DE DATOS */ 
        $allInputs['reporte'] = TRUE; //var_dump("<pre>",$allInputs); exit(); 
        $lista = $this->model_venta_farmacia->m_cargar_detalle_ventas_medicamentos_por_condicion_venta($allInputs); 
        $arrMainArray = array();
        foreach ($lista as $key => $row) { 
          $rowAux = array(
            'idcondicionventa'=> $row['idcondicionventa'],
            'descripcion_cv'=> $row['descripcion_cv'],
            'condicion_venta'=> array()
          );
          $arrMainArray[$row['idcondicionventa']] = $rowAux;
        } 
        foreach ($lista as $key => $row) {
          $rowAux = array( 
            'idmovimiento'=> $row['idmovimiento'],
            'iddetallemovimiento'=> $row['iddetallemovimiento'],
            'fecha_movimiento'=> $row['fecha_movimiento'],
            'medicamento'=> $row['medicamento'],
            'cantidad'=> $row['cantidad'],
            'precio_unitario'=> $row['precio_unitario'],
            'total_detalle'=> $row['total_detalle'],
            'total_detalle_str'=> $row['total_detalle_str'],
            'stock_actual_malm'=> $row['stock_actual_malm']
          );
          $arrMainArray[$row['idcondicionventa']]['condicion_venta'][$row['iddetallemovimiento']] = $rowAux;
        }

        /* CREACION DEL PDF */ 
        $headerDetalle = array('IDVENTA', 'ITEM', 'FECHA MOVIMIENTO', 'MEDICAMENTO', 'CANT.', 'PRECIO UNIT.', 'TOTAL DETALLE','STOCK'); 
        $this->pdf->Ln(1); 
        
        foreach ($arrMainArray as $keyPrin => $rowPrin) { 
          $this->pdf->SetAligns(array('L', 'L', 'L', 'L', 'C', 'R', 'R','R'));
          $this->pdf->Ln(6);
          $this->pdf->SetFont('Arial','B',12);
          $this->pdf->SetFillColor(214,225,242);
          $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['descripcion_cv'])),'',0,'C',TRUE);
          $this->pdf->Ln(7);
          $totalDetalle = 0; 
          $this->pdf->SetWidths(array(20, 15, 40, 107, 20, 30, 30, 15));
          $wDetalle = $this->pdf->GetWidths();
          $this->pdf->SetFont('Arial','B',8);
          for($i=0;$i<count($headerDetalle);$i++)
            $this->pdf->Cell($wDetalle[$i],7,$headerDetalle[$i],1,0,'C');

          $this->pdf->Ln();
          $this->pdf->SetFillColor(224,235,255);
          $fill = false;
          foreach ($rowPrin['condicion_venta'] as $keyAte => $row) { 
            $this->pdf->SetFont('Arial','',7);
            $this->pdf->Row( 
                array(
                    $row['idmovimiento'],
                    $row['iddetallemovimiento'],
                    darFormatoFecha($row['fecha_movimiento']),
                    utf8_decode(strtoupper($row['medicamento'])),
                    $row['cantidad'],
                    $row['precio_unitario'],
                    $row['total_detalle'],
                    $row['stock_actual_malm']
                    ),
                $fill
            );
            $fill = !$fill;
            $totalDetalle += $row['total_detalle_str']; 
          } 
          //$this->pdf->SetWidths(array(18, 18, 40, 70, 20, 25, 25));
          $this->pdf->SetFont('Arial','B',10);
          $this->pdf->Cell(260,5,'TOTAL: ',0,0,'R');
          $this->pdf->Cell(20,5,'S./ '.number_format($totalDetalle,2),0,0,'C');
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
    public function report_laboratorios_vendidos_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        
        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $lista = $this->model_venta_farmacia->m_cargar_laboratorios_vendidos_desde_hasta($allInputs);
        

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->SetFillColor(150, 190, 240);
        $columna_laboratorio = 135;
        $columna_monto = 30;
        $texto_monto = utf8_decode('MONTO (S/.)');
        $texto_total = 'TOTAL';

        
        $this->pdf->Cell(10,6,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,6,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_laboratorio,6,utf8_decode('LABORATORIO'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_monto,6,$texto_monto,1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $monto_total = 0;
        $this->pdf->SetWidths(array(10,15,$columna_laboratorio,$columna_monto));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill;
            $this->pdf->Row(
                array(
                    $item++,
                    ($row['idlaboratorio']),
                    utf8_decode($row['nombre_lab']),
                    number_format(utf8_decode($row['monto']),2)
                ),
                $fill,1
            );
            $monto_total += $row['monto'];
        }
        //$total2 = 'S/. 1,000.00';
        $total = 'S/. ' . number_format($monto_total, 2);
        $alinear = 'R';

        
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,$texto_total,0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$total,0,0,$alinear);
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
    public function report_compras_proveedor_fechas(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        
        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $lista = $this->model_venta_farmacia->m_cargar_compras_proveedor_desde_hasta($allInputs);

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];
        
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',7);
        $this->pdf->SetFillColor(150, 190, 240);
        $columna_proveedor = 135;
        $columna_monto = 30;
        $texto_monto = utf8_decode('MONTO (S/.)');
        $texto_total = 'TOTAL';

        
        $this->pdf->Cell(10,6,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,6,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_proveedor,6,utf8_decode('PROVEEDOR'),1,0,'L',TRUE);
        $this->pdf->Cell($columna_monto,6,$texto_monto,1,0,'R',TRUE);
        
        $this->pdf->Ln(6);
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $monto_total = 0;
        $this->pdf->SetWidths(array(10,15,$columna_proveedor,$columna_monto));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'R'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',7);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill;
            $this->pdf->Row(
                array(
                    $item++,
                    ($row['idproveedor']),
                    utf8_decode($row['razon_social']),
                    number_format(utf8_decode($row['monto']),2)
                ),
                $fill,1
            );
            $monto_total += $row['monto'];
        }
        //$total2 = 'S/. 1,000.00';
        $total = 'S/. ' . number_format($monto_total, 2);
        $alinear = 'R';

        
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,$texto_total,0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$total,0,0,$alinear);
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
    public function report_estadistico_venta_farmacia_dia_mes(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 

        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('L','A4');
        $this->pdf->AliasNbPages();
        $this->pdf->SetFont('Arial','B',16); 
        $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
        $longDayArray = array("","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
        $this->pdf->Cell(0,4,'PERIODO: '.$longMonthArray[$allInputs['mes']['id']]. ' ' .$allInputs['anioDesdeCbo'],'','','C'); 
        $listaV = $this->model_estadisticas->m_cargar_ventas_farmacia_por_mes_dia($allInputs); 
        $listaNC = $this->model_estadisticas->m_cargar_nota_credito_farmacia_por_mes_dia($allInputs); 
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
        $anio = $allInputs['anioDesdeCbo'];
        while ( $contDesdeDia <= cal_days_in_month(CAL_GREGORIAN, $allInputs['mes']['id'], $anio) ) { 
          $fechaDeDia = $anio.'-'.str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.str_pad($contDesdeDia,2,0,STR_PAD_LEFT); // 
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
            'dia'=> utf8_decode('DIA'),
            'monto' => utf8_decode('MONTO/DIF.CREC.'),
        );

        // foreach ($arrMeses as $key => $row) { 
        //   $arrTable[0][$row['mes']] = $row['mes'];
        // }    

        foreach ($arrDias as $keyDia => $rowDia) { 
          //
          $fechaDeDia = $anio.'-'.str_pad($allInputs['mes']['id'],2,0,STR_PAD_LEFT).'-'.str_pad($rowDia,2,0,STR_PAD_LEFT); // 
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
                  if( array_key_exists($preKey, $listaV) ) { 
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
        $i = 1;

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
    public function report_medicamentos_vencidos_farmacia(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        
        $this->pdf = new Fpdfext();
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv']);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $lista = $this->model_medicamento_almacen->m_cargar_medicamento_almacen_por_vencer(FALSE,$allInputs['resultado']);
        //$empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        //var_dump($lista); exit();

        // APARTADO: DATOS DE LA CABECERA
        /*$this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['empresa']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['sede']));
        $this->pdf->Ln(4);*/

        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetFillColor(150, 190, 240);

        $this->pdf->Cell(10,8,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,8,utf8_decode('LOTE'),1,0,'L',TRUE);
        $this->pdf->Cell(75,8,utf8_decode('MEDICAMENTO'),1,0,'L',TRUE);
        $this->pdf->Cell(30,8,utf8_decode('LABORATORIO'),1,0,'L',TRUE);
        $this->pdf->Cell(25,8,utf8_decode('ALMACEN'),1,0,'L',TRUE);

        $this->pdf->MultiCell(18,4,'FECHA VENCIMTO.',1,'C',TRUE);

        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+173,$y-8);
        $this->pdf->Cell(17,8,utf8_decode('ESTADO'),1,0,'C',TRUE);
        $this->pdf->Ln(8);

        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths(array(10,15,75,30,25,18,17));
        $this->pdf->SetAligns(array('L', 'L', 'L', 'L','L','L','C'));
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
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
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item,
                    utf8_decode(trim($row['num_lote'])),
                    utf8_decode(trim($row['denominacion'])),
                    utf8_decode(trim($row['nombre_lab'])),
                    utf8_decode(trim($row['almacen'])),
                    formatoFechaReporte3($row['fecha_vencimiento']),
                    $estado
                ),
                $fill,1
            );
            $item++;
        }
        //$total2 = 'S/. 1,000.00';
        // $width = $this->pdf->GetStringWidth($total);
        

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

    public function report_stock_monetizado(){
        // ini_set('xdebug.var_display_max_depth', 10);
        // ini_set('xdebug.var_display_max_children', 1024);
        // ini_set('xdebug.var_display_max_data', 1024);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit','2G');
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
        // PREPARACION DE DATOS PARA EL LOGO DEL PDF SEGUN EMPRESA-ADMIN
            $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']);
            $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
            $empresaAdmin['mode_report'] = 'F';
            $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $sumTotal = 0;
        $lista = $this->model_medicamento_almacen->m_cargar_stock_monetizado($allInputs);

        $arrPrincipal = array();
        $arrAuxMed = array();

        foreach ($lista as $key => $row) {
          $arrAuxMed = array(
            'idmedicamento' => $row['idmedicamento'],
            'medicamento' => $row['denominacion'],
            'stock_actual_total' => $row['stock_actual_total'],
            'movimientos' => array()
          );
          $arrPrincipal[$row['idmedicamento']] = $arrAuxMed;
        }

        foreach ($lista as $key => $row) { 
            $arrAuxMov = array(
                'idmovimiento' => $row['idmovimiento'],
                'fecha_movimiento' => $row['fecha_movimiento'],
                'cantidad' => $row['cantidad'],
                'precio_unitario' => $row['precio_unitario'],
            ); 
            $arrPrincipal[$row['idmedicamento']]['movimientos'][$row['iddetallemovimiento']] = $arrAuxMov; 
        }
        $arrPrincipal = array_values($arrPrincipal); 
        foreach ($arrPrincipal as $key => $row) {
            $arrPrincipal[$key]['movimientos'] = array_values($arrPrincipal[$key]['movimientos']); 
        }

        // MOSTRAR SOLO LOS DOS ULTIMOS MOVIMIENTOS DE CADA MEDICAMENTO 
        foreach ($arrPrincipal as $key => $row) { 
            foreach ($row['movimientos'] as $key2 => $row2) { 
                if($key2 > 1){
                    unset($arrPrincipal[$key]['movimientos'][$key2]);
                }
            }
        }
        // var_dump($arrPrincipal); exit();
        foreach ($arrPrincipal as $key => $medicamento) {
          if(count($medicamento['movimientos']) == 1){
            $arrPrincipal[$key]['precio_unitario_total'] = $medicamento['movimientos'][0]['precio_unitario'];
          }else{
            /* FORMULA PARA CALCULAR EL PRECIO UNITARIO PONDERADO*/
            $arrPrincipal[$key]['precio_unitario_total'] = 
              (
                ($medicamento['movimientos'][0]['precio_unitario'] * $medicamento['movimientos'][0]['cantidad']) + 
                ($medicamento['movimientos'][1]['precio_unitario'] * $medicamento['movimientos'][1]['cantidad'])
              ) / ($medicamento['movimientos'][0]['cantidad'] + $medicamento['movimientos'][1]['cantidad']);
          }
        }
        $arrListadoProd = array();
        $valor = 0;
        $i = 1;
        /* ARRAY PARA EL LISTADO DEL EXCEL */
        if($allInputs['allStocks']){
          foreach ($arrPrincipal as $row) {
            if($row['stock_actual_total'] > 0){
              $valor = (float)$row['stock_actual_total']*(float)$row['precio_unitario_total'];
              array_push($arrListadoProd, 
                array(
                  //$i++,
                  'idmedicamento' => $row['idmedicamento'],
                  'medicamento' => $row['medicamento'],
                  'stock_actual_total' => $row['stock_actual_total'],
                  'precio_unitario_total' => $row['precio_unitario_total'],
                  'valor' => $valor,
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
                //$i++,
                'idmedicamento' => $row['idmedicamento'],
                'medicamento' => $row['medicamento'],
                'stock_actual_total' => $row['stock_actual_total'],
                'precio_unitario_total' => $row['precio_unitario_total'],
                'valor' => $valor,
              )
            );
            $sumTotal += $valor;
          }
        }
        //var_dump($lista); exit();

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Almacen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);

        // APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(10,10,110,20,20,20);
        $arrHeaderText = array('ITEM','COD. MED.', 'MEDICAMENTO', 'STOCK AL ' . $allInputs['hasta'], 'P.U. PONDERADO', 'VALOR');
        $arrHeaderAligns = array('L','C','L','R','R','R');
        $arrBoolMultiCell = array(0,1,0,1,1,0); // colocar 1 donde deseas utilizar multicell
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
        
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrHeaderAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        
        foreach ($arrListadoProd as $row) {
            $fill = !$fill;
            $this->pdf->Row(
                array(
                    $item++,
                    $row['idmedicamento'],
                    strtoupper($row['medicamento']),
                    $row['stock_actual_total'],
                    number_format($row['precio_unitario_total'],2),
                    number_format($row['valor'],2)
                ),
                $fill,1
            );
        }
        $texto_total = 'Total';
        $total = 'S/. ' . number_format($sumTotal, 2);
        $alinear = 'R';

        
        $width = $this->pdf->GetStringWidth($total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(139-$width,6,'');
        $this->pdf->Cell(28,6,$texto_total,0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$total,0,0,$alinear);
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
    public function report_tarifario_farmacia(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
        // PREPARACION DE DATOS PARA EL LOGO DEL PDF SEGUN EMPRESA-ADMIN
            $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']);
            $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
            $empresaAdmin['mode_report'] = 'F';
            $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();

        $lista = $this->model_medicamento_almacen->m_cargar_tarifario_farmacia($allInputs);
        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Almacen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);
        

        // APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(10,20,70,70,20);
        $arrHeaderText = array('ITEM','COD. MED.', 'MEDICAMENTO', 'LABORATORIO', 'PRECIO VENTA');
        $arrHeaderAligns = array('L','C','L','L','R');
        $arrBoolMultiCell = array(0,0,0,0,0); // colocar 1 donde deseas utilizar multicell
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
        
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrHeaderAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        
        foreach ($lista as $row) {
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item,

                    $row['idmedicamento'],
                    strtoupper($row['denominacion']),
                    strtoupper($row['laboratorio']),
                    $row['precio_venta']
                ),
                $fill,1
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
    public function report_stock_medicamentos_por_condicion_venta(){
        ini_set('xdebug.var_display_max_depth', 10); 
        ini_set('xdebug.var_display_max_children', 1024); 
        ini_set('xdebug.var_display_max_data', 1024); 
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
        // PREPARACION DE DATOS PARA EL LOGO DEL PDF SEGUN EMPRESA-ADMIN
            $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']);
            $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
            $empresaAdmin['mode_report'] = 'F';
            $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);
        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        // APARTADO: DATOS DE LA CABECERA
        $col1 = 25; // ancho de la primera columna
        $this->pdf->SetFont('Arial','B',8);
        $this->pdf->Cell($col1,6,utf8_decode('ALMACEN'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',8);
        $this->pdf->Cell($col1,6,utf8_decode('SUBALMACEN'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode(@$allInputs['subalmacen']['descripcion']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',8);
        $this->pdf->Cell($col1,6,utf8_decode('FECHA Y HORA'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,date('d-m-Y') . '  ' . date('H:i a'));
        $this->pdf->Ln(6);

        // $this->pdf->SetFont('Arial','B',8);
        // $this->pdf->Cell($col1,6,utf8_decode('HORA'));
        // $this->pdf->Cell(3,6,':',0,0,'C');
        // $this->pdf->SetFont('Arial','',8);
        // $this->pdf->Cell(75,6,date('H:i a'));
        // $this->pdf->Ln(6);

        $this->pdf->SetFont('Arial','B',8);
        $this->pdf->Cell($col1,4,'COND. DE VENTA'); 
        $this->pdf->Cell(3,4,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $allInputs['arrCondicionesVenta'] = array();
        foreach ($allInputs['condicionVentaSeleccionadas'] as $key => $row) { 
            $y=$this->pdf->GetY();
            $this->pdf->SetXY(($col1 + 10),$y);
            $this->pdf->Cell(70,4,'   - '.utf8_decode(strtoupper($row['descripcion']))).'.'; 
            $this->pdf->Ln();
            $allInputs['arrCondicionesVenta'][] = $row['id'];
        }
        $this->pdf->Ln(6);
        // TRATAMIENTO DE DATOS
            $lista = $this->model_medicamento_almacen->m_cargar_stock_medicamentos_por_condicion_venta($allInputs);
            $arrMainArray = array();
            foreach ($lista as $key => $row) { 
              $rowAux = array(
                'idcondicionventa'=> $row['idcondicionventa'],
                'descripcion_cv'=> $row['descripcion_cv'],
                'condicion_venta'=> array()
              );
              $arrMainArray[$row['idcondicionventa']] = $rowAux;
            } 
            foreach ($lista as $key => $row) {
              $rowAux = array( 
                'idmedicamento'=> $row['idmedicamento'],
                'medicamento'=> $row['medicamento'],
                'stock_actual_malm'=> $row['stock_actual_malm']
              );
              $arrMainArray[$row['idcondicionventa']]['condicion_venta'][$row['idmedicamento']] = $rowAux;
            }
        // LISTADO DEL REPORTE
            $arrWidthCol = array(10,20,140,20); // total para un formato vertical = 190
            $arrHeaderText = array('#','COD. MED.', 'MEDICAMENTO', 'STOCK ACTUAL');
            $arrHeaderAligns = array('C','C','C','C');
            $arrDetalleAligns = array('C','C','L','R');
            $arrBoolMultiCell = array(0,0,0,1); // colocar 1 donde deseas utilizar multicell
            $countArray = count($arrWidthCol);
            foreach ($arrMainArray as $keyPrin => $rowPrin) { 
                // ENCABEZADO DEL LISTADO 
                    $this->pdf->SetFont('Arial','B',10);
                    $this->pdf->SetFillColor(214,225,242);
                    $this->pdf->Cell(0,7,utf8_decode(strtoupper($rowPrin['descripcion_cv'])),1,0,'C',TRUE);
                    $this->pdf->Ln(7);
                    $this->pdf->SetFont('Arial','',7);
                    $acumWidth = 0;
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
                // DETALLE
                    $this->pdf->SetWidths($arrWidthCol);
                    $this->pdf->SetAligns($arrDetalleAligns);
                    $this->pdf->SetFillColor(230, 240, 250);
                    $this->pdf->SetFont('Arial','',6);
                    $fill = false;
                    $item=1;
                    foreach ($rowPrin['condicion_venta'] as $keyAte => $row) {
                        $arrDatos = array(
                                        $item++,
                                        $row['idmedicamento'],
                                        utf8_decode($row['medicamento']),
                                        $row['stock_actual_malm'],
                                    );
                        $this->pdf->SetFont('Arial','',7);
                        //Row($data,$fill,$border,$arrBolds,$heigthCell,$arrTextColor,$arrBGColor,$arrImage,$bug,$fontSize)
                        $this->pdf->Row($arrDatos, $fill);
                        $fill = !$fill;
                    }
                $this->pdf->Ln(8);
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
    // LOGISTICA
    public function report_ordenes_compra(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);

        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();

        $lista = $this->model_orden_compra->m_cargar_ordenes_compra_para_reporte($allInputs);

       
        //var_dump($lista); exit();

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Almacen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['desde']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['hasta']));
        $this->pdf->Ln(4);
        

        // APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(8,20,67,20,20,20,20,15);
        $arrHeaderText = array('ITEM','Nº ORDEN.', 'PROVEEDOR', 'FECHA ORDEN','FECHA APROBACION', 'FECHA INGR. ESTIMADA','FECHA INGR. REAL', 'TOTAL');
        $arrHeaderAligns = array('L','L','L','C','C','C','C','R');
        $arrBoolMultiCell = array(0,0,0,0,1,1,1,0); // colocar 1 donde deseas utilizar multicell
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
        
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrHeaderAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        
        foreach ($lista as $row) {
            if( $row['tipo_movimiento'] == 2 ){
                $row['tipoingreso'] = 'COMPRA';
            }elseif( $row['tipo_movimiento'] == 4 ){
                $row['tipoingreso'] = 'REGALO';
            }elseif( $row['tipo_movimiento'] == 6 ){
                $row['tipoingreso'] = 'REINGRESO';
            }
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item,

                    $row['orden_compra'],
                    strtoupper($row['razon_social']),
                    formatoFechaReporte3($row['fecha_movimiento']),
                    formatoFechaReporte3($row['fecha_aprobacion']),
                    formatoFechaReporte3($row['fecha_entrega']),
                    formatoFechaReporte3($row['fecha_entrega_real']),
                    // $row['sub_total'],
                    // $row['total_igv'],
                    $row['total_a_pagar']

                  
                ),
                $fill,1
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
    public function report_ingresos_almacen(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        $this->pdf = new Fpdfext();
       

        // PREPARACION DE DATOS PARA EL LOGO DEL PDF SEGUN EMPRESA-ADMIN
            $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']);
            $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
            $empresaAdmin['mode_report'] = 'F';
            $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);

        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();

        $allInputs['idtipoentrada'] = 0; // todas las entradas
        $sumTotalVenta = 0;
        $lista = $this->model_entrada_farmacia->m_cargar_entradas($allInputs);

       
        //var_dump($lista); exit();

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Almacen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['desde']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['hasta']));
        $this->pdf->Ln(4);
        

        // APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(8,20,20,62,18,16,15,16,15);
        $arrHeaderText = array('ITEM','FACTURA','Nº ORDEN.', 'PROVEEDOR', 'FEC. ORDEN','SUB TOTAL', 'IGV', 'TOTAL','TIPO INGRESO');
        $arrHeaderAligns = array('L','L','L','L','C','R','R','R','C');
        $arrBoolMultiCell = array(0,0,0,0,0,0,0,0,1); // colocar 1 donde deseas utilizar multicell
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
        
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrHeaderAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        
        foreach ($lista as $row) {
            if( $row['tipo_movimiento'] == 2 ){
                $row['tipoingreso'] = 'COMPRA';
            }elseif( $row['tipo_movimiento'] == 4 ){
                $row['tipoingreso'] = 'REGALO';
            }elseif( $row['tipo_movimiento'] == 6 ){
                $row['tipoingreso'] = 'REINGRESO';
            }
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item++,
                    // $row['idmovimiento'],
                    $row['factura'],
                    $row['orden_compra'],
                    strtoupper($row['razon_social']),
                    formatoFechaReporte3($row['fecha_movimiento']),
                    $row['sub_total_sf'],
                    $row['total_igv_sf'],
                    $row['total_a_pagar_sf'],
                    $row['tipoingreso']
                ),
                $fill,1
            );
            $sumTotalVenta += $row['total_a_pagar_sf'];
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
    public function report_traslados(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true);
        
        $this->pdf = new Fpdfext();
        // PREPARACION DE DATOS PARA EL LOGO DEL PDF SEGUN EMPRESA-ADMIN
            $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['almacen']['idsedeempresaadmin']);
            $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
            $empresaAdmin['mode_report'] = 'F';
            $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('P','A4');
        $this->pdf->AliasNbPages();
        $allInputs['idsubalmacen1'] = 0; // 
        $allInputs['idsubalmacen2'] = 0; // 
        $lista = $this->model_traslado_farmacia->m_cargar_traslados($allInputs);

       
        //var_dump($lista); exit();

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Almacen'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['almacen']['descripcion']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['desde']));
        $this->pdf->Ln(4);

        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($allInputs['hasta']));
        $this->pdf->Ln(4);
        

        // APARTADO GRILLA
        //$this->pdf->SetXY($x,$y);
        $arrWidthCol = array(10,10,25,35,35,60,15);
        $arrHeaderText = array('ITEM','COD. MOV','FECHA MOV.','SUBALM. ORIGEN','SUBALM. DESTINO','RESPONSABLE','ESTADO');
        $arrHeaderAligns = array('L', 'C', 'C', 'L','L','L','C');
        $arrBoolMultiCell = array(0,1,0,0,0,0,0); // colocar 1 donde deseas utilizar multicell
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
        /*$this->pdf->Cell(10,8,utf8_decode('ITEM'),1,0,'L',TRUE);
        $this->pdf->Cell(15,8,utf8_decode('COD.'),1,0,'L',TRUE);
        $this->pdf->Cell(75,8,utf8_decode('FECHA MOV.'),1,0,'L',TRUE);
        $this->pdf->Cell(30,8,utf8_decode('SUBALM. ORIGEN'),1,0,'L',TRUE);
        $this->pdf->Cell(25,8,utf8_decode('SUBALM. DESTINO'),1,0,'L',TRUE);

        $this->pdf->MultiCell(18,4,'SUBALM. DESTINO',1,'C',TRUE);

        $x=$this->pdf->GetX();
        $y=$this->pdf->GetY();
        $this->pdf->SetXY($x+173,$y-8);
        $this->pdf->Cell(17,8,utf8_decode('ESTADO'),1,0,'C',TRUE);*/
        $this->pdf->Ln(8);
        
        $this->pdf->SetFont('Arial','',8);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        // $this->pdf->SetDrawColor(204,204,204); // gris
        $this->pdf->SetLineWidth(.2);
        $item = 1;

        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrHeaderAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',6);
        
        foreach ($lista as $row) {
            if( $row['estado_movimiento'] == 1 ){ // HABILITADO 
                $estado = '';
            }
            elseif( $row['estado_movimiento'] == 0 ){ // ANULADO 
                $estado = 'ANULADO';
            }
            $fill = !$fill;
            
            $this->pdf->Row(
                array(
                    $item,
                    utf8_decode(trim($row['idmovimiento1'])),
                    formatoFechaReporte4($row['fecha_movimiento']),
                    utf8_decode(trim($row['subAlmacenOrigen'])),
                    utf8_decode(trim($row['subAlmacenDestino'])),
                    utf8_decode(trim($row['usuario'])),
                    $estado
                ),
                $fill,1
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
    public function report_estadistico_venta_mes_anio(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $listaV = $this->model_estadisticas->m_cargar_ventas_farmacia_por_anio_mes($allInputs); 
        $listaNC = $this->model_estadisticas->m_cargar_nota_credito_farmacia_por_anio_mes($allInputs); 
        
        // CARGAR ESTADISTICAS DE AÑOS ANTERIORES 
        /*$listaAnteriores = $this->model_estadisticas->m_cargar_estadisticas_anos_anteriores($allInputs); 
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
        }*/
        // if( $allInputs['sedeempresa'] == 1 || $allInputs['sedeempresa'] == 8  ){
        //   $listaV = array_merge($arrAnteriores,$listaV);
        // }
        // $listaV = array_merge($arrAnteriores,$listaV);
        foreach ( $listaV as $key => $row ) { 
            foreach ($listaNC as $keyNC => $rowNC) { 
                if( $row['ano'] == $rowNC['ano'] && $row['mes'] == $rowNC['mes'] ){ 
                  $listaV[$key]['total'] = $listaV[$key]['total'] + $rowNC['total'];
                }
            }
        }
        //var_dump($listaV); exit();
        if( $allInputs['tipoCuadro'] === 'reporte' ){
            $this->report_estadistico_venta_mes_anio_PDF($allInputs,$listaV); 
        }elseif ( $allInputs['tipoCuadro'] === 'grafico' ) {
            $this->report_estadistico_venta_mes_anio_GRAPH($allInputs,$listaV); 
        }
    }
    private function report_estadistico_venta_mes_anio_PDF($allInputs,$listaV){
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
    private function report_estadistico_venta_mes_anio_GRAPH($allInputs,$listaV){
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
                  $arrSeries[$key]['data'][] = (float)('0.00'); 
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
                  $tablaDatos[$keyAno][$rowAno.'-'.$rowMes] = (float)('0.00'); 
                }
                
            }
            $tablaDatos[$keyAno]['sumtotal'] = $totalAno; 
            $tablaDatos[$keyAno]['dif'] = 0; 
        }
        foreach ($tablaDatos as $key => $row) { 
            $tablaDatos[$key]['dif'] = 0; 
            $preKey = $key - 1; 
            if( array_key_exists($preKey, $tablaDatos) ) { 
                if( $tablaDatos[$key]['sumtotal'] > 0 && $tablaDatos[$preKey]['sumtotal'] != 0){ 
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
        // var_dump($arrSeries); exit();
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
    //PREPARADOS
    public function report_preparados_pagados(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        // $this->pdf->SetLeftMargin(5);

        $empresaAdmin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresaAdmin['estado'] = $empresaAdmin['estado_emp'];
        $empresaAdmin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresaAdmin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresaAdmin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('L','A4');
        $this->pdf->AliasNbPages();
        // RECUPERACION DE DATOS
        $lista = $this->model_venta_farmacia->m_cargar_formulas_pagadas($allInputs);
        // $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);

        //var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];

        // APARTADO: DATOS DE LA CABECERA
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Empresa'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['razon_social']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Sede'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($empresaAdmin['sede']));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // $this->pdf->SetFont('Arial','B',9);
        // $this->pdf->Cell(28,6,utf8_decode('Estado'));
        // $this->pdf->Cell(3,6,':',0,0,'C');
        // $this->pdf->SetFont('Arial','',8);
        // $this->pdf->Cell(75,6,utf8_decode('PAGADAS'));
        // $this->pdf->Ln(4);
        
        $this->pdf->Ln(6);
        
        // $this->pdf->SetFont('Arial','B',5);
        // $this->pdf->SetFillColor(150, 190, 240);
        
        //CABECERAS DE LA TABLA// APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(7,10,18,25,55,14,10,65,6,50,16);  // ANCHO TOTAL: 276
        $arrHeaderText = array('Nº','N° SOLIC.', 'N° PEDIDO', 'FECHA VENTA', 'PACIENTE', 'TELEFONO','COD.', 'FORMULA', 'CANT', 'MEDICO', 'ESTADO');
        $arrHeaderAligns = array('C','C','C','C','C','C','C','C','C','C','C');
        $arrDataAligns = array('C','C','C','C','L','C','C','L','C','L','C');
        $arrBoolMultiCell = array(0,1,0,0,0,0,0,0,0,0,0); // colocar 1 donde deseas utilizar multicell
        $countArray = count($arrWidthCol);
        $acumWidth = 0;
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',6);
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
        $this->pdf->SetFont('Arial','',5);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        
        $this->pdf->SetLineWidth(.2);
        $monto_total = 0;
        $cantidad_total = 0;
        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrDataAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',5);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill; 
            // Row($data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=FALSE,$arrTextColor=FALSE,$arrBGColor=FALSE,$arrImage=FALSE,$bug=FALSE,$fontSize=FALSE)
            if( $row['estado_formula'] == '' ){
                $estado = $row['estado'];
            }else{
                $estado = $row['estado_formula'];
            }
            // Row($data,$fill,$border,$arrBolds,$heigthCell,$arrTextColor,$arrBGColor,$arrImage,$bug,$fontSize)
            $this->pdf->Row(
                array(
                    $item++,
                    str_pad($row['idsolicitudformula'], 6, '0', STR_PAD_LEFT),
                    $row['codigo_pedido'],
                    ( formatoFechaReporte($row['fecha_movimiento'])),
                    // utf8_decode($row['encargado']),
                    // strlen(utf8_decode($row['paciente'])),
                    utf8_decode($row['paciente']),
                    utf8_decode($row['telefono']),
                    utf8_decode($row['idformula_jj']),
                    utf8_decode($row['denominacion']),
                    utf8_decode($row['cantidad']),
                    utf8_decode($row['medico']),
                    utf8_decode($estado),                    
                                        
                    // number_format(utf8_decode($row['total']), 2)
                    ),
                $fill,1, FALSE,5,FALSE,FALSE,FALSE,FALSE,6);
            
            // $monto_total += $row['total'];
            $cantidad_total += ($row['cantidad']);
        }
        // $total = 'S/. ' . number_format($monto_total, 2);
        $alinear = 'R';
        
        $width = $this->pdf->GetStringWidth($cantidad_total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(150-$width,6,'');
        $this->pdf->Cell(28,6,'CANTIDAD DE FORMULAS',0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$cantidad_total,0,0,$alinear);
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
    public function report_formulas_vendidas_costo(){
        $allInputs = json_decode(trim($this->input->raw_input_stream),true); 
        $this->pdf = new Fpdfext();
        // $this->pdf->SetLeftMargin(5);

        $empresa_admin = $this->model_empresa_admin->m_cargar_esta_sede_empresa_admin($allInputs['sedeempresa']);
        $empresa_admin['estado'] = $empresa_admin['estado_emp'];
        $empresa_admin['mode_report'] = 'F';
        $this->pdf->setIdEmpresaFarm($empresa_admin['idempresaadmin']);

        mostrar_plantilla_pdf($this->pdf,utf8_decode($allInputs['titulo']),FALSE,$allInputs['tituloAbv'],$empresa_admin);
        //$this->pdf->SetFont('Arial','',12);
        $this->pdf->AddPage('L','A4');
        $this->pdf->AliasNbPages();
        // RECUPERACION DE DATOS
        // $lista = $this->model_venta_farmacia->m_cargar_formulas_vendidas_costo($allInputs);
        $lista = $this->model_venta_farmacia->m_cargar_formulas_pagadas($allInputs);

        // var_dump($lista); exit();
        $desde = str_replace("-", "/", $allInputs['desde']) . ' | ' . $allInputs['desdeHora'] . ':' . $allInputs['desdeMinuto'];
        $hasta = str_replace("-", "/", $allInputs['hasta']) . ' | ' . $allInputs['hastaHora'] . ':' . $allInputs['hastaMinuto'];

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
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Desde'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($desde));
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(28,6,utf8_decode('Hasta'));
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',8);
        $this->pdf->Cell(75,6,utf8_decode($hasta));
        $this->pdf->Ln(4);
        // $this->pdf->SetFont('Arial','B',9);
        // $this->pdf->Cell(28,6,utf8_decode('Estado'));
        // $this->pdf->Cell(3,6,':',0,0,'C');
        // $this->pdf->SetFont('Arial','',8);
        // $this->pdf->Cell(75,6,utf8_decode('PAGADAS'));
        // $this->pdf->Ln(4);
        
        $this->pdf->Ln(6);
        
        // $this->pdf->SetFont('Arial','B',5);
        // $this->pdf->SetFillColor(150, 190, 240);
        
        //CABECERAS DE LA TABLA// APARTADO GRILLA
        //encabezado de la grilla
        $arrWidthCol = array(7,10,27,96,90,6,10,10,10,10); // ANCHO TOTAL: 276
        $arrHeaderText = array('Nº','N° SOLIC.', 'FECHA VENTA', 'PACIENTE', 'FORMULA', 'CANT', 'P.U. COSTO', 'P.U. VENTA', 'TOTAL COSTO', 'TOTAL VENTA');
        $arrHeaderAligns = array('C','C','C','C','C','C','C','C','C','C');
        $arrDataAligns = array('C','C','C','L','L','C','R','R','R','R');
        $arrBoolMultiCell = array(0,1,0,0,0,0,1,1,1,1); // colocar 1 donde deseas utilizar multicell
        $countArray = count($arrWidthCol);
        $acumWidth = 0;
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial','B',6);
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
        $this->pdf->SetFont('Arial','',5);
        $fill = TRUE;
        $this->pdf->SetDrawColor(31,31,31); // gris oscuro
        
        $this->pdf->SetLineWidth(.2);
        $costo_total = 0;
        $monto_total = 0;
        $cantidad_total = 0;
        $this->pdf->SetWidths($arrWidthCol);
        $this->pdf->SetAligns($arrDataAligns);
        $this->pdf->SetFillColor(230, 240, 250);
        $this->pdf->SetFont('Arial','',5);
        $item = 1;
        foreach ($lista as $row) {
            $fill = !$fill; 
            // Row($data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=FALSE,$arrTextColor=FALSE,$arrBGColor=FALSE,$arrImage=FALSE,$bug=FALSE,$fontSize=FALSE)
            if( $row['estado_formula'] == '' ){
                $estado = $row['estado'];
            }else{
                $estado = $row['estado_formula'];
            }
            $total_detalle_costo = ($row['precio_costo'] * $row['cantidad']);
            // Row($data,$fill,$border,$arrBolds,$heigthCell,$arrTextColor,$arrBGColor,$arrImage,$bug,$fontSize)
            $this->pdf->Row(
                array(
                    $item++,
                    str_pad($row['idsolicitudformula'], 6, '0', STR_PAD_LEFT),
                    // $row['idsolicitudformula'],
                    ( formatoFechaReporte($row['fecha_movimiento'])),
                    // utf8_decode($row['encargado']),
                    // strlen(utf8_decode($row['paciente'])),
                    utf8_decode($row['paciente']),
                    // strlen(utf8_decode($row['denominacion'])),
                    utf8_decode($row['denominacion']),
                    utf8_decode($row['cantidad']),
                    $row['precio_costo'],
                    $row['precio_unitario'],
                    number_format($total_detalle_costo,2),
                    $row['total_detalle'],
                                        
                    // number_format(utf8_decode($row['total']), 2)
                    ),
                $fill,1, FALSE,5,FALSE,FALSE,FALSE,FALSE,6);
            
            $costo_total += $total_detalle_costo;
            $monto_total += $row['total_detalle'];
            $cantidad_total += ($row['cantidad']);
        }
        // $total = 'S/. ' . number_format($monto_total, 2);
        $alinear = 'R';
        
        $width = $this->pdf->GetStringWidth($cantidad_total);
        $widthCosto = $this->pdf->GetStringWidth($costo_total);
        $widthMonto = $this->pdf->GetStringWidth($monto_total);
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(110-$width,6,'');
        $this->pdf->Cell(20,6,'CANTIDAD DE FORMULAS',0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($width+20),6,$cantidad_total,0,0,$alinear);

        $this->pdf->Cell(67-$widthCosto,6,'');
        $this->pdf->Cell(20,6,'COSTO TOTAL',0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($widthCosto+20),6,number_format($costo_total,2),0,0,$alinear);
        $this->pdf->Ln(6);
        $this->pdf->Cell(220-$widthMonto,6,'');
        $this->pdf->Cell(20,6,'VENTA TOTAL',0,0,'R');
        $this->pdf->Cell(3,6,':',0,0,'C');
        $this->pdf->SetFont('Arial','',12);
        $this->pdf->Cell(round($widthMonto+20),6,number_format($monto_total,2),0,0,$alinear);


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
}