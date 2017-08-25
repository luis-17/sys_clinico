<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    // Incluimos el archivo fpdf
    require_once APPPATH."/third_party/fpdf/fpdf.php";
 
    //Extendemos la clase Pdf de la clase fpdf para que herede todas sus variables y funciones
    class Fpdfext extends FPDF { 
      public function __construct() {
        parent::__construct();
      }
      var $widths;
      var $aligns;
      var $angle=0;
      // El encabezado del PDF 
      /*  
          $htmlPlantilla .= '<div class="header-mini"> USUARIO:'.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESIÓN: '.date('Y-m-d H:i:s') .'</div>';
          $htmlPlantilla .= '<div class="header-logo"> <img width="200" src="'.base_url('assets/img/dinamic/empresa/'.$fConfig['nombre_logo']).'" /> '; 
          $htmlPlantilla .= '<div class="block razon_social">'.$fConfig['razon_social'].'</div>';
          $htmlPlantilla .= '<div class="block domicilio_fiscal">'.$fConfig['domicilio_fiscal'].'</div>';
          $htmlPlantilla .= '</div>';
          $htmlPlantilla .= '<div class="headerTitle">'.$datos['titulo'].'</div> <hr />'; 
      */
      public function setModeReport($modeReport){ 
        $this->modeReport = $modeReport;
      }
      public function getModeReport()
      {
        return $this->modeReport;
      }
      public function setEstado($estado){
        $this->estado = $estado;
      }
      public function getEstado()
      {
        return $this->estado;
      }
      public function setTituloAbr($tituloAbr){
        $this->tituloAbr = $tituloAbr;
      }
      public function getTituloAbr()
      {
        return $this->tituloAbr;
      }
      public function setTitulo($title){
        $this->title = $title;
      }
      public function getTitulo()
      {
        return $this->title;
      }
      public function setImagenCab($param){
        $this->imagenCab = $param;
      }
      public function getImagenCab()
      {
        return $this->imagenCab;
      }
      public function setNombreEmpresa($param){
        $this->nombreEmpresa = $param;
      }
      public function getNombreEmpresa()
      {
        return $this->nombreEmpresa;
      }
      public function setRucEmpresa($param){
        $this->rucEmpresa = $param;
      }
      public function getRucEmpresa()
      {
        return $this->rucEmpresa;
      }
      public function setDireccion($param){
        $this->direccion = $param;
      }
      public function getDireccion()
      {
        return $this->direccion;
      }
      public function setNombreSede($param){
        $this->nombreSede = $param;
      }
      public function getNombreSede()
      {
        return $this->nombreSede;
      }
      public function setDireccionSede($param){
        $this->direccionSede = $param;
      }
      public function getDireccionSede()
      {
        return $this->direccionSede;
      }
      public function setReferenciaSede($param){
        $this->referenciaSede = $param;
      }
      public function getReferenciaSede()
      {
        return $this->referenciaSede;
      }
      public function SetWidths($w)
      {
          //Set the array of column widths
          $this->widths=$w;
      }
      public function GetWidths()
      {
          //Set the array of column widths
          return $this->widths;
      }
      public function SetAligns($a)
      {
          //Set the array of column alignments
          $this->aligns=$a;
      }
      public function GetAligns()
      {
          //Set the array of column widths
          return $this->aligns;
      }
      public function setNombreEmpresaFarm($param){
        $this->nombreEmpresaFarm = $param;
      }
      public function setRucEmpresaFarm($param){
        $this->rucEmpresaFarm = $param;
      }
      public function setIdEmpresaFarm($param){
        $this->idEmpresaFarm = $param;
      }
      public function Row($data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=FALSE,$arrTextColor=FALSE,$arrBGColor=FALSE,$arrImage=FALSE,$bug=FALSE,$fontSize=FALSE)
      {
          //Calculate the height of the row 
          //var_dump($heigthCell); exit();
          if(empty($fontSize)){
            $fontSize = 7;
          }
          if( empty($heigthCell) ){
            $heigthCell = 5;
          }
          $nb=0;
          for($i=0;$i<count($data);$i++)
              $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
          $h=($heigthCell)*$nb;
          //Issue a page break first if needed
          $this->CheckPageBreak($h);
          //Draw the cells of the row
          for($i=0;$i<count($data);$i++)
          {
              $w=$this->widths[$i];
              $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
              //Save the current position
              $x=$this->GetX();
              $y=$this->GetY();
              //Draw the border
              // $this->Rect($x,$y,$w,$h);
              //Print the text
              if( $arrBolds ){
                if( $arrBolds[$i] == 'B'){
                  $this->SetFont('Arial','B',$fontSize+1);
                }else{
                  $this->SetFont('Arial','',$fontSize);
                }
              }
              if( $arrTextColor ){
                if( $arrTextColor[$i] == 'red'){
                  $this->SetTextColor(225,22,22);
                }elseif( $arrTextColor[$i] == 'green'){
                  $this->SetTextColor(12,162,10);
                }else{
                  $this->SetTextColor(0);
                }
              }
              if( $arrBGColor ){ 
                $fill=TRUE;
                if( $arrBGColor[$i] == 'p1'){ 
                  $this->SetFillColor(130);
                }elseif( $arrBGColor[$i] == 'p2'){
                  $this->SetFillColor(160);
                }elseif( $arrBGColor[$i] == 'p3'){
                  $this->SetFillColor(190);
                }elseif( $arrBGColor[$i] == 'p4'){
                  $this->SetFillColor(220);
                }elseif( $arrBGColor[$i] == 'p5'){
                  $this->SetFillColor(240);
                }else{
                  $fill=FALSE;
                  $this->SetFillColor(255);
                }
              }
              $textoCell = $data[$i];
              if( !empty($arrImage[$i]) ){
                // var_dump($textoCell); exit();
                // $textoCell = $this->Image('assets/img/dinamic/empresa/'.$textoCell,2,2,50); 
                //$
                if( empty($textoCell) ){
                  $textoCell = 'noimage.jpg';
                }
                $textoCell = $this->Image('assets/img/dinamic/empleado/'.$textoCell,($x + 6),($y + 1),10,10); 
                //$fill= FALSE;
              }
              // if( empty($heigthCell) ){
              //   $heigthCell = 5;
              // }
              if( !empty($fontSize) ){

              }
              $this->MultiCell($w,$heigthCell,$textoCell,$border,$a,$fill);
              $this->SetFont('Arial','',$fontSize);
              //Put the position to the right of the cell
              $this->SetXY($x+$w,($y));
              // var_dump($i); 
          }// exit();
          //Go to the next line
          if($bug){
            $h = $heigthCell;
          }
          $this->Ln($h);
      }
      public function RowSmall($data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=5,$arrTextColor=FALSE,$arrBGColor=FALSE)
      {
          //Calculate the height of the row 
          $nb=0;
          for($i=0;$i<count($data);$i++)
              $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
          $h=5*$nb;
          //Issue a page break first if needed
          $this->CheckPageBreak($h);
          //Draw the cells of the row
          for($i=0;$i<count($data);$i++)
          {
              $w=$this->widths[$i];
              $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
              //Save the current position
              $x=$this->GetX();
              $y=$this->GetY();
              //Draw the border
              // $this->Rect($x,$y,$w,$h);
              //Print the text
              if( $arrBolds ){
                if( $arrBolds[$i] == 'B'){
                  $this->SetFont('Arial','B',7);
                }else{
                  $this->SetFont('Arial','',6);
                }
              }
              if( $arrTextColor ){
                if( $arrTextColor[$i] == 'red'){
                  $this->SetTextColor(225,22,22);
                }elseif( $arrTextColor[$i] == 'green'){
                  $this->SetTextColor(12,162,10);
                }else{
                  $this->SetTextColor(0);
                }
              }
              if( $arrBGColor ){
                if( $arrBGColor[$i] == 'p1'){
                  $this->SetFillColor(130);
                }elseif( $arrBGColor[$i] == 'p2'){
                  $this->SetFillColor(160);
                }elseif( $arrBGColor[$i] == 'p3'){
                  $this->SetFillColor(190);
                }elseif( $arrBGColor[$i] == 'p4'){
                  $this->SetFillColor(220);
                }elseif( $arrBGColor[$i] == 'p5'){
                  $this->SetFillColor(240);
                }else{
                  $this->SetFillColor(255);
                }
              }
              $this->MultiCell($w,$heigthCell,$data[$i],$border,$a,$fill);
              
              
              $this->SetFont('Arial','',6);
              //Put the position to the right of the cell
              $this->SetXY($x+$w,$y);
          }
          //Go to the next line
          $this->Ln($h);
      }
      public function TextArea($data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=5, $heightTextArea=20) //para una sola celda
      {
          //Calculate the height of the row 
          $nb=0;
          for($i=0;$i<count($data);$i++)
              $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
          $h=5*$nb;
          // calcular el alto del textarea
          if($h < $heightTextArea){
            $h = $heightTextArea;
          }

          //Issue a page break first if needed
          $this->CheckPageBreak($h);
          //Draw the cells of the row
          for($i=0;$i<count($data);$i++)
          {
              $w=$this->widths[$i];
              $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
              //Save the current position
              $x=$this->GetX();
              $y=$this->GetY();
              //Draw the border
              $this->Rect($x,$y,$w,$h);
              //Print the text
              if( $arrBolds ){
                if( $arrBolds[$i] == 'B'){
                  $this->SetFont('Arial','B',8);
                }else{
                  $this->SetFont('Arial','',7);
                }
              }
              $this->MultiCell($w,$heigthCell,$data[$i],$border,$a,$fill);
              $this->SetFont('Arial','',7);
              //Put the position to the right of the cell
              $this->SetXY($x+$w,$y);
          }
          //Go to the next line
          //$this->Ln($h);
      }
      /* CABECERA LABORATORIO */ 
        public function setPaciente($paciente){
          $this->paciente = $paciente;
        }
        public function getPaciente()
        {
          return $this->paciente;
        }
        public function setFechaRecepcion($fechaRecepcion){
          $this->fechaRecepcion = $fechaRecepcion;
        }
        public function getFechaRecepcion()
        {
          return $this->fechaRecepcion;
        }
        public function setEdadPaciente($edadPaciente){
          $this->edadPaciente = $edadPaciente;
        }
        public function getEdadPaciente()
        {
          return $this->edadPaciente;
        }
        public function setSexoPaciente($sexoPaciente){
          $this->sexoPaciente = $sexoPaciente;
        }
        public function getSexoPaciente()
        {
          return $this->sexoPaciente;
        }
        public function setNumeroExamen($numeroExamen){
          $this->numeroExamen = $numeroExamen;
        }
        public function getNumeroExamen()
        {
          return $this->numeroExamen;
        }
        public function setNumeroHistoria($numeroHistoria){
          $this->numeroHistoria = $numeroHistoria;
        }
        public function getNumeroHistoria()
        {
          return $this->numeroHistoria;
        }

      /* CABECERA ORDEN DE COMPRA */
        public function setRazonSocialOC($razonSocialOC){
          $this->razonSocialOC = $razonSocialOC;
        }
        public function getRazonSocialOC()
        {
          return $this->razonSocialOC;
        }
        public function setRucOC($rucOC){
          $this->rucOC = $rucOC;
        }
        public function getRucOC()
        {
          return $this->rucOC;
        }
        public function setNombreComercialOC($nombreComercialOC){
          $this->nombreComercialOC = $nombreComercialOC;
        }
        public function getNombreComercialOC()
        {
          return $this->nombreComercialOC;
        }
        public function setDireccionFiscalOC($direccionFiscal){
          $this->direccionFiscal = $direccionFiscal;
        }
        public function getDireccionFiscalOC()
        {
          return $this->direccionFiscal;
        }
        public function setTelefonoOC($telefonoOC){
          $this->telefonoOC = $telefonoOC;
        }
        public function getTelefonoOC()
        {
          return $this->telefonoOC;
        }
        public function setFaxOC($faxOC){
          $this->faxOC = $faxOC;
        }
        public function getFaxOC()
        {
          return $this->faxOC;
        }
        public function setFormaPagoOC($formaPagoOC){
          $this->formaPagoOC = $formaPagoOC;
        }
        public function getFormaPagoOC()
        {
          return $this->formaPagoOC;
        }
        public function setMonedaOC($monedaOC){
          $this->monedaOC = $monedaOC;
        }
        public function getMonedaOC()
        {
          return $this->monedaOC;
        }
        public function setOrdenCompraOC($ordenCompraOC){
          $this->ordenCompraOC = $ordenCompraOC;
        }
        public function getOrdenCompraOC()
        {
          return $this->ordenCompraOC;
        }

        public function setFechaMovimientoOC($fechaMovimiento){
          $this->fechaMovimiento = $fechaMovimiento;
        }
        public function getFechaMovimientoOC()
        {
          return $this->fechaMovimiento;
        }

        public function setFechaEmisionCorreoOC($fechaEmisionCorreo){
          $this->fechaEmisionCorreo = $fechaEmisionCorreo;
        }
        public function getFechaEmisionCorreoOC()
        {
          return $this->fechaEmisionCorreo;
        }

        public function setFechaEntregaOC($fechaEntrega){
          $this->fechaEntrega = $fechaEntrega;
        }
        public function getFechaEntregaOC()
        {
          return $this->fechaEntrega;
        }
        public function setNombreAlmOC($nombreAlm){
          $this->nombreAlm = $nombreAlm;
        }
        public function getNombreAlmOC()
        {
          return $this->nombreAlm;
        }
        public function setUsuarioRespOC($usuarioResp){
          $this->usuarioResp = $usuarioResp;
        }
        public function getUsuarioRespOC()
        {
          return $this->usuarioResp;
        }

        public function setFirmaFinanzas($firmaFinanzas){
          $this->firmaFinanzas = $firmaFinanzas;
        }
        public function getFirmaFinanzas(){
          return $this->firmaFinanzas;
        }

        public function setFirmaLogistica($firmaLogistica){
          $this->firmaLogistica = $firmaLogistica;
        }
        public function getFirmaLogistica(){
          return $this->firmaLogistica;
        }

        public function setFirmaFarmacia($firmaFarmacia){
          $this->firmaFarmacia = $firmaFarmacia;
        }
        public function getFirmaFarmacia(){
          return $this->firmaFarmacia;
        }

        public function setAlmacenDirStr($almacenDirStr){
          $this->almacenDirStr = $almacenDirStr;
        }
        public function getAlmacenDirStr(){
          return $this->almacenDirStr;
        }


      /*
        getFirmaFinanzas
        getFirmaLogistica
        getFirmaFarmacia
      */
      public function CheckPageBreak($h)
      {
          //If the height h would cause an overflow, add a new page immediately
          if($this->GetY()+$h>$this->PageBreakTrigger)
              $this->AddPage($this->CurOrientation);
      }

      public function NbLines($w,$txt)
      {
          //Computes the number of lines a MultiCell of width w will take
          $cw=&$this->CurrentFont['cw'];
          if($w==0)
              $w=$this->w-$this->rMargin-$this->x;
          $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
          $s=str_replace("\r",'',$txt);
          $nb=strlen($s);
          if($nb>0 and $s[$nb-1]=="\n")
              $nb--;
          $sep=-1;
          $i=0;
          $j=0;
          $l=0;
          $nl=1;
          while($i<$nb)
          {
              $c=$s[$i];
              if($c=="\n")
              {
                  $i++;
                  $sep=-1;
                  $j=$i;
                  $l=0;
                  $nl++;
                  continue;
              }
              if($c==' ')
                  $sep=$i;
              $l+=$cw[$c];
              if($l>$wmax)
              {
                  if($sep==-1)
                  {
                      if($i==$j)
                          $i++;
                  }
                  else
                      $i=$sep+1;
                  $sep=-1;
                  $j=$i;
                  $l=0;
                  $nl++;
              }
              else
                  $i++;
          }
          return $nl;
      }
      public function Header(){ 
        // var_dump($this->tituloAbr); exit(); 
        $this->SetAutoPageBreak(TRUE,25);
        if( $this->tituloAbr  == 'LAB-RL' ){ // y_final_izquierda
          $this->SetFillColor(234,236,239);
          $this->SetMargins(8,8,6); 
          $this->Ln(28);
          $this->SetFont('Arial','B',10); 
          $this->Cell(30,6,'NOMBRES: ','LT','','',1); 
          $this->Cell(100,6,strtoupper($this->getPaciente()),'T','','',1); 
          $this->SetFont('Arial','B',10); 
          $this->Cell(18,6,'FECHA: ','T','','',1); 
          $this->SetFont('Arial','',10); 
          $this->Cell( 33,6,$this->getFechaRecepcion() ,'T','','',1);
          $this->SetFont('Arial','I',8);
          $this->Cell( 15,6,'Pag. '.$this->PageNo().'/{nb}' ,'TR','','C',1); 
          $this->Ln();
          $this->SetFont('Arial','B',10); 
          $this->Cell(30,6,'EDAD: ','L','','',1);
          $this->SetFont('Arial','',10); 
          $this->Cell(30,6,@utf8_decode($this->getEdadPaciente()),'','','',1);
          $this->SetFont('Arial','B',10); 
          $this->Cell(15,6,'SEXO: ','','','',1); 
          $this->SetFont('Arial','',10); 
          $this->Cell(55,6,$this->getSexoPaciente(),'','','',1); 
          $this->SetFont('Arial','B',10); 
          $this->Cell(18,6,utf8_decode('Nº EXA.: '),'','','',1); 
          $this->SetFont('Arial','',10); 
          $this->Cell(48,6,$this->getNumeroExamen(),'R','','',1); 
          $this->Ln();
          $this->SetFont('Arial','B',10); 
          $this->Cell(130,6,'MEDICO: ','L','','',1);
          $this->SetFont('Arial','B',10); 
          $this->Cell(18,6,utf8_decode('H.C.: '),'','','',1); 
          $this->SetFont('Arial','',10); 
          $this->Cell(48,6,$this->getNumeroHistoria(),'R','','',1); 
          //$this->Cell(40,6,''); 
          $this->Ln();
          $this->SetFont('Arial','B',10); 
          $this->Cell(30,6,'PROCEDENCIA: ','LB','','',1); 
          $this->SetFont('Arial','',10); 
          $this->Cell(100,6,'HOSPITAL VILLA SALUD','B','','',1); 
          $this->SetFont('Arial','B',10); 
          $this->Cell(18,6,utf8_decode('F. IMP.:'),'B','','',1);
          $this->SetFont('Arial','',10); 
          $this->Cell(48,6,strtoupper(date('d/m/Y     H:i:s a')),'BR','','',1); 
          $this->Ln(8);
          $arrAligns = array('L', 'L', 'L', 'L');
          $arrAlignsHeader = array('C', 'L', 'L', 'L');
          $this->SetWidths(array(58, 56, 57, 25));
          $this->SetAligns($arrAligns);
          $wDetalle = $this->GetWidths();
          $headerDetalle = array(
                'EXAMEN',
                'RESULTADO',
                'VALOR NORMAL',
                'METODO'
          ); 
          $this->SetFont('Arial','B',8);
          $this->Cell(0,0,'','B');
          $this->Ln();
          $cantHeaderDetalle = count($headerDetalle);
          for($i=0;$i<$cantHeaderDetalle;$i++){ 
            $bordeHeader = '';
            if($i == 3){
              $bordeHeader = 'R';
            }
            if($i == 0){
              $bordeHeader = 'L';
            }
            // $this->SetFillColor(214,225,242);
            
            $this->Cell($wDetalle[$i],7,$headerDetalle[$i],$bordeHeader,0,$arrAlignsHeader[$i],1);
          }
          $this->Ln();
          $this->Cell(0,0,'','T');
          $this->Ln(2);
        }elseif( $this->tituloAbr  == 'O/C' ){ // var_dump('aw'); exit();
          $this->SetAutoPageBreak(TRUE,65);
          // APARTADO: DATOS DEL PROVEEDOR
          /*
          setRazonSocialOC
          setRucOC
          setDireccionFiscalOC
          setTelefonoOC
          setFormaPagoOC
          setMonedaOC
          setOrdenCompraOC
          setFechaMovimientoOC
          setFechaEntregaOC
          setNombreAlmOC
          setUsuarioRespOC
          */
          $ci2 =& get_instance(); 
          $this->SetFont('Arial','',6);
          $this->SetXY(-70,0);
          //$this->MultiCell(120,6,'USUARIO:'.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESION: '.date('Y-m-d H:i:s'));
          $this->Image($this->getImagenCab(),2,2,50); 
          $varXPositionNE= 16;
          $varXPositionDIR= 16;
          // MODO FARMACIAA
          if( $this->getModeReport() == 'F'  && $this->idEmpresaFarm == '12' ){ 
            $varXPositionNE= 2;
            $varXPositionDIR= 2;
            $this->SetTextColor(255,255,255);
          }
          $direccionOBJ = $this->getDireccion();
          if( $ci2->sessionHospital['username'] === '46091867'  ){
            $direccionOBJ = 'CALLE ANDREA DEL SARTO NRO. 247 URB. LA CALERA DE LA MERCED - SURQUILLO - LIMA';
          }
          $this->SetFont('Arial','',5);
          $this->SetXY($varXPositionNE,10);
          $this->MultiCell( 120,6,strtoupper( $this->getNombreEmpresa() ) ); 
          $this->SetXY($varXPositionDIR,12);
          $this->SetFont('Arial','',4);
          $this->MultiCell( 120,6,strtoupper( $direccionOBJ ) ); 
          $this->SetTextColor(0,0,0); // texto para el titulo: color negro
          $this->SetFont('Arial','B',13);
          $this->SetXY(100,10);
          $this->Cell(120,10,utf8_decode($this->getTitulo()),0,0);
          $this->Line(350,20,4,20);
          $this->Ln(15);
          // var_dump($this->getEstado()); exit();
          if( $this->getEstado()  == 0 ){ 
            $this->SetFont('Arial','B',50);
            $this->SetTextColor(255,192,203);
            $this->RotatedText(70,190,'A N U L A D O',45);  
          }
          $this->SetTextColor(0,0,0);

          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Proveedor'));
          $this->Cell(3,6,':',0,0,'C');
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x,$y+1);
          $this->SetFont('Arial','',8);
          $this->MultiCell(75,3,$this->getRazonSocialOC());

          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,'RUC');
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $this->Cell(75,6,$this->getRucOC());
          $this->Ln(4);

          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Nombre Com.'));
          $this->Cell(3,6,':',0,0,'C');
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x,$y+1);
          $this->SetFont('Arial','',8);
          $this->MultiCell(75,3,$this->getNombreComercialOC());

          $this->SetFont('Arial','B',9);
          $this->Cell(24,6, utf8_decode('Dirección'));
          $this->Cell(3,6,':',0,0,'C');
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x,$y+1);
          $this->SetFont('Arial','',8);
          if( $ci2->sessionHospital['username'] === '46091867' ){
            $direccionOBJ = 'CALLE ANDREA DEL SARTO NRO. 247 URB. LA CALERA DE LA MERCED - SURQUILLO - LIMA';
          }else{
            $direccionOBJ = $this->getDireccionFiscalOC(); 
          }
          $this->MultiCell(75,3,$direccionOBJ);
          //$this->MultiCell(75,3,$this->getDireccionFiscalOC());
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6, utf8_decode('Teléfono/Fax'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          if( $this->getFaxOC() )
            $this->Cell(75,6,$this->getTelefonoOC() . ' / ' . $this->getFaxOC());
          else
            $this->Cell(75,6,$this->getTelefonoOC());
          $this->Ln(4);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6, utf8_decode('Forma de Pago'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $this->Cell(75,6,$this->getFormaPagoOC());
          $this->Ln(4);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6, utf8_decode('Moneda'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $this->Cell(75,6,$this->getMonedaOC());
          $this->Ln(4);
          $x_final_izquierda = $this->GetX();
          $y_final_izquierda = $this->GetY();
          // APARTADO: DATOS DE LA  ORDEN DE COMPRA
          $this->SetXY(122,25);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Número O/C'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','B',9);
          $this->Cell(30,6,$this->getOrdenCompraOC());
          $this->Ln(4);
          $y=$this->GetY();
          $this->SetXY(122,$y);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Fecha Creación'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $this->Cell(30,6, $this->getFechaMovimientoOC());
          $this->Ln(4);
          $y=$this->GetY();
          $this->SetXY(122,$y);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Fecha Emisión'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $this->Cell(30,6, $this->getFechaEmisionCorreoOC());
          $this->Ln(4);
          $y=$this->GetY();
          $this->SetXY(122,$y);
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6,utf8_decode('Almacén'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x,$y+1);
          $this->MultiCell(51,3,$this->getNombreAlmOC(),0,'L');
          $y=$this->GetY();
          $this->SetXY(122,$y);
          
          $this->SetFont('Arial','B',9);
          $this->Cell(24,6, utf8_decode('Responsable'));
          $this->Cell(3,6,':',0,0,'C');
          $this->SetFont('Arial','',8);
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x,$y+1);
          $this->MultiCell(51,3,$this->getUsuarioRespOC(),0,'L');
          //$this->Ln();
          $this->SetXY($x,$y);

          // LOGICA POSICION 
          $y_final_derecha  = $this->GetY();
          
          if($y_final_izquierda >= $y_final_derecha){
            $y = $y_final_izquierda;
          }else{
            $y = $y_final_derecha;
          }
          $x = $x_final_izquierda;

          //$this->Ln(16);
          $this->SetXY($x,$y+2);
          $this->SetFont('Arial','',6);
          $this->SetFillColor(128, 174, 220);
          $this->Cell(8,10,'ITEM',1,0,'L',TRUE);
          $this->Cell(53,10,'PRODUCTO',1,0,'L',TRUE);
          $this->Cell(27,10,'LABORATORIO',1,0,'L',TRUE);
          $this->Cell(15,10,'U.M',1,0,'C',TRUE);
          $this->Cell(10,10,'CANT.',1,0,'C',TRUE);
          
          $this->Cell(12,10,'P.U.',1,0,'C',TRUE);
         
          $this->MultiCell(12,5,'DESC Valor',1,'C',TRUE);
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x+137,$y-10);
          //$this->Cell(16,10,'IMP.sin IGV',1,0,'C',TRUE);
          $this->MultiCell(16,5,'IMPORTE sin IGV',1,'C',TRUE);
          $x=$this->GetX();
          $y=$this->GetY();
          $this->SetXY($x+153,$y-10);
          $this->Cell(13,10,'IGV',1,0,'C',TRUE);
          $this->Cell(16,10,'IMPORTE',1,0,'C',TRUE);
          $this->MultiCell(8,5,'INAFECTO',1,'L',TRUE);
          $this->Ln(1);

          $this->SetFont('Arial','',8);
        }elseif( $this->tituloAbr  == 'FIC-EMPL' || $this->tituloAbr  == 'FAR_VT-DC' ||
                $this->tituloAbr  == 'AS-APE' || $this->tituloAbr  == 'AM-FAM' || $this->tituloAbr  == 'AM-FASO')
        { 
          $ci2 =& get_instance(); 
          $this->SetFont('Arial','',6);
          $this->SetXY(-70,0);
          $this->MultiCell(120,6,'USUARIO:'.strtoupper_total($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESION: '.date('Y-m-d H:i:s'));
          $this->Image($this->getImagenCab(),2,2,50); 
          $this->SetFont('Arial','',5);
          $this->SetXY(16,10);
          $this->MultiCell( 120,6,utf8_decode(strtoupper_total($this->getNombreEmpresa())) ); 
          $this->SetXY(16,12);
          $this->SetFont('Arial','',4);
          $this->MultiCell( 120,6,utf8_decode(strtoupper_total($this->getDireccion())) ); 
          $this->SetFont('Arial','B',13);
          $this->SetXY(100,10);
          $this->Cell(120,10,utf8_decode($this->getTitulo()),0,0);
          $this->Line(350,20,4,20);
          $this->Ln(15);
        }
        elseif( $this->tituloAbr  == 'AM-RCTA' ){
            $ci2 =& get_instance(); 
            $this->SetFont('Arial','',6);
            $this->SetXY(-70,0);
            $this->Image($this->getImagenCab(),2,2,50); 
            $varXPositionNE= 16;
            $varXPositionDIR= 16;
            $this->SetFont('Arial','',5);
            $this->SetXY($varXPositionNE,10);
            $this->MultiCell( 64,6,utf8_decode(strtoupper_total($this->getNombreEmpresa())) ); 
            $this->SetXY($varXPositionDIR,12);
            $this->SetFont('Arial','',4);
            $this->MultiCell( 64,6,utf8_decode(strtoupper_total($this->getDireccion())) );
            $this->SetFont('Arial','B',10);
            $this->SetXY(80,10);
            $this->Cell(64,10,utf8_decode($this->getTitulo()),0,0);

            $this->Image($this->getImagenCab(),154,2,50); 
            $varXPositionNE= 168;
            $varXPositionDIR= 168;
            $this->SetFont('Arial','',5);
            $this->SetXY($varXPositionNE,10);
            $this->MultiCell( 64,6,utf8_decode(strtoupper_total($this->getNombreEmpresa())) ); 
            $this->SetXY($varXPositionDIR,12);
            $this->SetFont('Arial','',4);
            $this->MultiCell( 64,6,utf8_decode(strtoupper_total($this->getDireccion())) );
            $this->SetFont('Arial','B',10);
            $this->SetXY(232,10);
            $this->Cell(64,10,utf8_decode($this->getTitulo()),0,0);

            $this->Line(292,20,4,20);
            $this->Ln(15);
        }elseif( $this->tituloAbr  == 'RH-BOL' ){ // BOLETA DE PAGO
          $this->SetAutoPageBreak(TRUE,5);
          //$ci2 =& get_instance(); 
          $this->SetFont('Arial','',5);
          $this->SetXY(-130,0);
          //$this->MultiCell(120,6,'USUARIO:'.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESION: '.date('Y-m-d H:i:s'),0,'R');
          $this->Cell( 126,3,'Pag. '.$this->PageNo().'/{nb}',0,0,'R');
          $this->Ln(1);
        }elseif( $this->tituloAbr  == 'RH-CTS'){
          $this->SetAutoPageBreak(TRUE,15);
          $this->SetXY(10,10);
          $this->SetFont('Arial','B',10);
          $this->MultiCell( 60,6,utf8_decode(strtoupper_total($this->getNombreEmpresa())),0,'C' );
          $this->SetFont('Arial','',8);
          $this->MultiCell( 60,6,utf8_decode('RUC Nº ' . strtoupper_total($this->getRucEmpresa())),0,'C' );
          $this->Ln(10);
        }
        elseif(  $this->tituloAbr  == 'AM-REC' ){
          $this->SetAutoPageBreak(TRUE,10);
          $ci2 =& get_instance(); 
          $this->SetFont('Arial','',6);
          $this->SetXY(-70,0);
          $this->Image($this->getImagenCab(),6,2,50); 
          $varXPositionNE = 20;
          $varXPositionDIR = 16;
          $this->SetFont('Arial','',6);
          $this->SetXY($varXPositionNE,11);
          $this->MultiCell( 64,6,utf8_decode($this->getNombreEmpresa()));
          $this->SetXY($varXPositionNE,14);
          $this->MultiCell( 64,6,utf8_decode($this->getNombreSede())); 

          $this->SetFont('Arial','B',10);
          $this->SetXY(80,6);
          $this->Cell(64,10,utf8_decode($this->getTitulo()),0,1);
         
          $this->SetFont('Arial','B',8);
          $this->SetXY(151,2);
          $direccionSede=explode(' - ', $this->getDireccionSede());
          $this->Cell(50,4,utf8_decode($direccionSede[0]),0,0,'R' );
          $i=6;
          $referenciaSede = $this->getReferenciaSede();
          if( !empty($referenciaSede) ){
            $this->SetXY(151,$i); 
            $this->Cell(50,4,utf8_decode($this->getReferenciaSede()),0,0,'R' );
            $i+=2; 
          }
          
          $this->SetXY(151,$i); $i+=3;
          $this->Cell(50,4,utf8_decode('Central Telefónica: 399 14 14'),0,0,'R' );
          $this->SetXY(151,$i);
          $this->Cell(50,4,utf8_decode('www.villasalud.pe'),0,0,'R' );
          
          $this->Ln(13);
        }
        else{
          if ( $this->PageNo() == 1 ) { 
            // MODO NORMAL 
            $ci2 =& get_instance(); 
            $this->SetFont('Arial','',6);
            $this->SetXY(-70,0);
            $this->MultiCell(120,6,'USUARIO:'.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESION: '.date('Y-m-d H:i:s'));
            $this->Image($this->getImagenCab(),2,2,50); 
            $varXPositionNE= 16;
            $varXPositionDIR= 16;
            // MODO FARMACIA

            if( $this->getModeReport() == 'F' && $this->idEmpresaFarm == '12' ){ 
              $varXPositionNE= 2;
              $varXPositionDIR= 2;
              $this->SetTextColor(255,255,255);
            }
            $this->SetFont('Arial','',5);
            $this->SetXY($varXPositionNE,10);
            $this->MultiCell( 120,6,utf8_decode(strtoupper_total($this->getNombreEmpresa())) ); 
            $this->SetXY($varXPositionDIR,12);
            $this->SetFont('Arial','',4);
            $this->MultiCell( 120,6,utf8_decode(strtoupper_total($this->getDireccion())),0,'L' );
            $this->SetTextColor(0,0,0);
            $this->SetFont('Arial','B',13);
            $this->SetXY(100,10);
            $this->Cell(120,10,utf8_decode($this->getTitulo()),0,0);
            $this->Line(292,20,4,20);
            $this->Ln(15);
            if( @$this->tituloAbr  === 0 ){ 
              $this->SetFont('Arial','B',50);
              $this->SetTextColor(255,192,203);
              $this->RotatedText(70,190,'A N U L A D O',45);  
            }
            
          }
        }
      }
       // El pie del pdf
      public function Footer(){
        $ci2 =& get_instance();
        if( $this->tituloAbr  == 'AM-LT' ){
          $this->SetY(-16);
          //$this->SetX(80);
          $this->SetFont('Arial','',8);
          $this->Cell(140,10,'_________________________________',0,0,'C');
          //$this->SetX(80);
          $this->Cell(140,10,'_________________________________',0,0,'C'); 
          $this->Ln(5);
          
          $this->Cell(140,10,'FRANZSHESKOLI CABRERA TORIBIO',0,0,'C');
          $posX = $this->GetX();
          $this->SetXY(60,-8);
          $this->Cell(0,10,'Jefe de Sistemas y Desarrollo',0,0);
          $this->SetXY($posX,-12);
          $this->Cell(140,10,'Jefe de Especialidad',0,0,'C');
        }elseif( $this->tituloAbr == 'O/C'){
          $this->SetY(-64);
          //$this->SetX(80);
           // NOTAS IMPORTANTES
          $this->SetFont('Arial','B',8);
          $this->Cell(0,5,'NOTAS IMPORTANTES','TRL');
          $this->Ln(5);
          $this->SetFont('Arial','',7);
          $this->Cell(0,5,utf8_decode(' - Facturar a Nombre de '. $this->nombreEmpresaFarm .' R.U.C. Nº '. $this->rucEmpresaFarm ),'LR');
          $this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - Los precios fijados en esta Orden de Compra no pueden sufrir variación '),'LR');
          $this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - La Factura debe remitirse en triplicado, adjuntándose la presente Orden de Compra '),'LR');
          $this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - La Mercadería viaja por cuenta y riesgo del vendedor '),'LR');
          $this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - Confirmar la recepción de la Orden de Compra y remitir la fecha de entrega de los productos '),'LR');
          $this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - La dirección de entrega es '.strtoupper($this->getAlmacenDirStr()) ),'LR'); 
          $this->Ln(4);
          //$this->Cell(0,5,utf8_decode(' - Agente de Retención designado mediante Resolución de Superintendencia Nº 395-2014/SUNAT con vigencia desde el 1/02/2015. '),'LR');
          //$this->Ln(4);
          $this->Cell(0,5,utf8_decode(' - Los Comprobantes de Retención serán remitidos por correo electrónico. '),'BLR');
          $this->Ln(5); // space 
          $this->SetFont('Arial','',8);

          // ; 
          $getFirmaFinanzas = $this->getFirmaFinanzas();
          if( !empty($getFirmaFinanzas) ){ 
            $this->Cell(62,10,$this->Image($this->getFirmaFinanzas(),16,274,50,20),0,0,'C');
          }
           
          // $this->Cell(62,10,'_______________________',0,0,'C'); 
          $getFirmaLogistica = $this->getFirmaLogistica();
          if( !empty( $getFirmaLogistica) ){ 
            $this->Cell(62,10,$this->Image($this->getFirmaLogistica(),80,274,50,20),0,0,'C'); 
          }
          // $this->Cell(62,10,'_______________________',0,0,'C'); 
          $getFirmaFarmacia = $this->getFirmaFarmacia();
          if( !empty($getFirmaFarmacia) ){ 
            $this->Cell(62,10,$this->Image($this->getFirmaFarmacia(),142,274,50,20),0,0,'C'); 
          }
          // $this->Cell(62,10,'_______________________',0,0,'C'); 
          $this->Ln(10);
          $this->Cell(62,10,'Gerente Finanzas',0,0,'C');
          $this->Cell(62,10,utf8_decode('Logística'),0,0,'C');
          $this->Cell(62,10,'Jefe Area Usuaria',0,0,'C');

          // $posX = $this->GetX();
          // $this->SetXY(30,-8);
          // $this->Cell(0,10,'Jefe de Sistemas y Desarrollo',0,0,'L');
          // $this->SetXY($posX,-12);
        }elseif($this->tituloAbr == 'AM-RCTA' ){
          $this->SetY(-16);
          $this->Cell(134,6, strtoupper($ci2->sessionHospital['username']).'  '.date('d/m/Y H:i:s'));
          $this->SetX(155);
          $this->Cell(134,6, strtoupper($ci2->sessionHospital['username']).'  '.date('d/m/Y H:i:s'));
        }
      }
      function RotatedText($x, $y, $txt, $angle){
          //Text rotated around its origin
          $this->Rotate($angle,$x,$y);
          $this->Text($x,$y,$txt);
          $this->Rotate(0);
      }


      function Rotate($angle,$x=-1,$y=-1)
      {
          if($x==-1)
              $x=$this->x;
          if($y==-1)
              $y=$this->y;
          if($this->angle!=0)
              $this->_out('Q');
          $this->angle=$angle;
          if($angle!=0)
          {
              $angle*=M_PI/180;
              $c=cos($angle);
              $s=sin($angle);
              $cx=$x*$this->k;
              $cy=($this->h-$y)*$this->k;
              $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
          }
      }

      function _endpage()
      {
          if($this->angle!=0)
          {
              $this->angle=0;
              $this->_out('Q');
          }
          parent::_endpage();
      }
      function SetDash($black=null, $white=null){
          if($black!==null)
              $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
          else
              $s='[] 0 d';
          $this->_out($s);
      }
      
    }
?>