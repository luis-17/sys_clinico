<?php 
tcpdf();

class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-20);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $foot = 'Hospital Villa Salud';
        $foot2 = 'Teléfonos: (01) 713-0044';
        $foot3 = 'Juan Velasco Alvarado Cdra 01 Frente a la Sunat,';
        $foot4 = 'Entrada del parque industrial - Villa El Salvador';
        $foot5 = 'www.hospitalvillasalud.com';
        $foot6 = 'COMPROMETIDOS CON TU SALUD';
        $medtrat = 'MEDICO TRATANTE';
        if($_POST["medicotr"] != "")
           $medtrat = $_POST["medicotr"];
        $foot7 = $medtrat;
        $foot8 = 'Sello y Firma';
        $this->MultiCell(0, 1, $foot, 0, 'L');
        $this->MultiCell(0, 1, $foot2, 0, 'L');
        $this->MultiCell(0, 1, $foot3, 0, 'L');
        $this->MultiCell(0, 1, $foot4, 0, 'L');
        $this->MultiCell(0, 1, $foot5, 0, 'L');

        $this->SetFont('helvetica', 'B', 9);
        $this->SetY(-30);
        $this->MultiCell(0, 1, $foot6, 0, 'C');

        $this->SetY(-20);
        $this->MultiCell(0, 1, $foot7, 0, 'R');
        $this->MultiCell(0, 1, $foot8, 0, 'R');
    }
 }



$obj_pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$obj_pdf->SetCreator(PDF_CREATOR);
// $width = 175;  
// $height = 266; 
// $orientation = ($height>$width) ? 'P' : 'L';  
// $obj_pdf->addFormat("custom", $width, $height);  
// $obj_pdf->reFormat("custom", $orientation);  
$title = "Historia Clínica Nro " . $_POST["nrohistoria"];
$obj_pdf->SetTitle($title);
$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$obj_pdf->SetDefaultMonospacedFont('helvetica');
$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$obj_pdf->SetFont('helvetica', '', 8);
$obj_pdf->setFontSubsetting(false);

$obj_pdf->AddPage();





/*diagnostico descripcion*/

 $didesc1 = '';
 if(isset($_POST["descripcion1"]))
   $didesc1 = $_POST["descripcion1"];

 $didesc2 = '';
 if(isset($_POST["descripcion2"]))
   $didesc2 = $_POST["descripcion2"];

 $didesc3 = '';
 if(isset($_POST["descripcion3"]))
   $didesc3 = $_POST["descripcion3"];

 $didesc4 = '';
 if(isset($_POST["descripcion4"]))
   $didesc4 = $_POST["descripcion4"];

 $didesc5 = '';
 if(isset($_POST["descripcion5"]))
   $didesc5 = $_POST["descripcion5"];

  $didesc6 = '';
 if(isset($_POST["descripcion6"]))
   $didesc6 = $_POST["descripcion6"];

  $didesc7 = '';
 if(isset($_POST["descripcion7"]))
   $didesc7 = $_POST["descripcion7"];

  $didesc8 = '';
 if(isset($_POST["descripcion8"]))
   $didesc8 = $_POST["descripcion8"];



/*diagnostico cie10*/
 $cie101 = '';
 if(isset($_POST["cie101"]))
   $cie101 = $_POST["cie101"];

 $cie102 = '';
 if(isset($_POST["cie102"]))
   $cie102 = $_POST["cie102"];

 $cie103 = '';
 if(isset($_POST["cie103"]))
   $cie103 =  $_POST["cie103"];

 $cie104 = '';
 if(isset($_POST["cie104"]))
   $cie104 =  $_POST["cie104"];

 $cie105 = '';
 if(isset($_POST["cie105"]))
   $cie105 =$_POST["cie105"];

  $cie106 = '';
 if(isset($_POST["cie106"]))
   $cie106 =  $_POST["cie106"];

  $cie107 = '';
 if(isset($_POST["cie107"]))
   $cie107 =  $_POST["cie107"];

  $cie108 = '';
 if(isset($_POST["cie108"]))
   $cie108 = $_POST["cie108"];




/*diagnostico tipo*/
 $tipodi1 = '';
 if(isset($_POST["tdiag1"]))
   $tipodi1 = $_POST["tdiag1"];

 $tipodi2 = '';
 if(isset($_POST["tdiag2"]))
   $tipodi2 =  $_POST["tdiag2"];

 $tipodi3 = '';
 if(isset($_POST["tdiag3"]))
   $tipodi3 =  $_POST["tdiag3"];

 $tipodi4 = '';
 if(isset($_POST["tdiag4"]))
   $tipodi4 =  $_POST["tdiag4"];

 $tipodi5 = '';
 if(isset($_POST["tdiag5"]))
   $tipodi5 = $_POST["tdiag5"];

  $tipodi6 = '';
 if(isset($_POST["tdiag6"]))
   $tipodi6 = $_POST["tdiag6"];

  $tipodi7 = '';
 if(isset($_POST["tdiag7"]))
   $tipodi7 =  $_POST["tdiag7"];

  $tipodi8 = '';
 if(isset($_POST["tdiag8"]))
   $tipodi8 = $_POST["tdiag8"];



/*examenes auxiliares*/
 $aux1 = '';
 if(isset($_POST["procedimiento1"]))
   $aux1 = $_POST["procedimiento1"];

 $aux2 = '';
 if(isset($_POST["procedimiento2"]))
   $aux2 = $_POST["procedimiento2"] . '.';

 $aux3 = '';
 if(isset($_POST["procedimiento3"]))
   $aux3 =  $_POST["procedimiento3"] . '.';

 $aux4 = '';
 if(isset($_POST["procedimiento4"]))
   $aux4 =  $_POST["procedimiento4"] . '.';

 $aux5 = '';
 if(isset($_POST["procedimiento5"]))
   $aux5 =  $_POST["procedimiento5"] . '.';

  $aux6 = '';
 if(isset($_POST["procedimiento6"]))
   $aux6 =  $_POST["procedimiento6"] . '.';

  $aux7 = '';
 if(isset($_POST["procedimiento7"]))
   $aux7 =  $_POST["procedimiento7"] . '.';

  $aux8 = '';
 if(isset($_POST["procedimiento8"]))
   $aux8 =  $_POST["procedimiento8"] . '.';




/*MEDICAMENTOS !*/


/*medicamento*/
 $med1 = '';
 if(isset($_POST["medicamento1"]))
   $med1 = $_POST["medicamento1"];

 $med2 = '';
 if(isset($_POST["medicamento2"]))
   $med2 =  $_POST["medicamento2"];

 $med3 = '';
 if(isset($_POST["medicamento3"]))
   $med3 = $_POST["medicamento3"];

 $med4 = '';
 if(isset($_POST["medicamento4"]))
   $med4 =  $_POST["medicamento4"];

 $med5 = '';
 if(isset($_POST["medicamento5"]))
   $med5 =  $_POST["medicamento5"];

  $med6 = '';
 if(isset($_POST["medicamento6"]))
   $med6 =  $_POST["medicamento6"];

/*cant*/
 $cant1 = '';
 if(isset($_POST["cantidad1"]))
   $cant1 = $_POST["cantidad1"];

 $cant2 = '';
 if(isset($_POST["cantidad2"]))
   $cant2 = $_POST["cantidad2"];

 $cant3 = '';
 if(isset($_POST["cantidad3"]))
   $cant3 = $_POST["cantidad3"];

 $cant4 = '';
 if(isset($_POST["cantidad4"]))
   $cant4 = $_POST["cantidad4"];

 $cant5 = '';
 if(isset($_POST["cantidad5"]))
   $cant5 =  $_POST["cantidad5"];

  $cant6 = '';
 if(isset($_POST["cantidad6"]))
   $cant6 = $_POST["cantidad6"];


/*dosis*/
 $dosis1 = '';
 if(isset($_POST["dosis1"]))
   $dosis1 = $_POST["dosis1"];

 $dosis2 = '';
 if(isset($_POST["dosis2"]))
   $dosis2 =  $_POST["dosis2"];

 $dosis3 = '';
 if(isset($_POST["dosis3"]))
   $dosis3 = $_POST["dosis3"];

 $dosis4 = '';
 if(isset($_POST["dosis4"]))
   $dosis4 = $_POST["dosis4"];

 $dosis5 = '';
 if(isset($_POST["dosis5"]))
   $dosis5 =  $_POST["dosis5"];

  $dosis6 = '';
 if(isset($_POST["dosis6"]))
   $dosis6 =  $_POST["dosis6"];


/*dias*/
 $dias1 = '';
 if(isset($_POST["dias1"]))
   $dias1 = $_POST["dias1"];

 $dias2 = '';
 if(isset($_POST["dias2"]))
   $dias2 = $_POST["dias2"];

 $dias3 = '';
 if(isset($_POST["dias3"]))
   $dias3 = $_POST["dias3"];

 $dias4 = '';
 if(isset($_POST["dias4"]))
   $dias4 =  $_POST["dias4"];

 $dias5 = '';
 if(isset($_POST["dias5"]))
   $dias5 =  $_POST["dias5"];

  $dias6 = '';
 if(isset($_POST["dias6"]))
   $dias6 =  $_POST["dias6"];



/*MEDICAMENTOS ROWS*/

$row_m1 = '';
if($med1 != ''){
 $row_m1 = 
 '<tr nobr="true">
  <td colspan="2">' . $med1 . '</td>
  <td colspan="1">' . $cant1 . '</td>
  <td colspan="2">' . $dosis1 . '</td>
  <td colspan="1">' . $dias1 . '</td>  
 </tr>';
}
$row_m2 = '';
if($med2 != ''){
 $row_m2 = 
 '<tr nobr="true">
  <td colspan="2">' . $med2 . '</td>
  <td colspan="1">' . $cant2 . '</td>
  <td colspan="2">' . $dosis2 . '</td>
  <td colspan="1">' . $dias2 . '</td>  
 </tr>';
}
$row_m3 = '';
if($med3 != ''){
 $row_m3 = 
 '<tr nobr="true">
  <td colspan="2">' . $med3 . '</td>
  <td colspan="1">' . $cant3 . '</td>
  <td colspan="2">' . $dosis3 . '</td>
  <td colspan="1">' . $dias3 . '</td>  
 </tr>';
}
$row_m4 = '';
if($med4 != ''){
 $row_m4 = 
 '<tr nobr="true">
  <td colspan="2">' . $med4 . '</td>
  <td colspan="1">' . $cant4 . '</td>
  <td colspan="2">' . $dosis4 . '</td>
  <td colspan="1">' . $dias4 . '</td>  
 </tr>';
}
$row_m5 = '';
if($med5 != ''){
 $row_m5 = 
 '<tr nobr="true">
  <td colspan="2">' . $med5 . '</td>
  <td colspan="1">' . $cant5 . '</td>
  <td colspan="2">' . $dosis5 . '</td>
  <td colspan="1">' . $dias5 . '</td>  
 </tr>';
}
$row_m6 = '';
if($med6 != ''){
 $row_m6 = 
 '<tr nobr="true">
  <td colspan="2">' . $med6 . '</td>
  <td colspan="1">' . $cant6 . '</td>
  <td colspan="2">' . $dosis6 . '</td>
  <td colspan="1">' . $dias6 . '</td>  
 </tr>';
}



/*DIAGNOSTICO ROWS*/

$row_t1 = '';
if($didesc1 != ''){
 $row_t1 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc1  . '</td>
  <td colspan="1">' . $cie101. '</td>
  <td colspan="1">' . $tipodi1. '</td>
  <td colspan="2">' . $aux1 . '</td>
 </tr>';
}
$row_t2 = '';
if($didesc2 != ''){
 $row_t2 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc2  . '</td>
  <td colspan="1">' . $cie102. '</td>
  <td colspan="1">' . $tipodi2. '</td>
  <td colspan="2">' . $aux2 . '</td>
 </tr>';
}
$row_t3 = '';
if($didesc3 != ''){
 $row_t3 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc3  . '</td>
  <td colspan="1">' . $cie103. '</td>
  <td colspan="1">' . $tipodi3. '</td>
  <td colspan="2">' . $aux3 . '</td>
 </tr>';
}
$row_t4 = '';
if($didesc4 != ''){
 $row_t4 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc4  . '</td>
  <td colspan="1">' . $cie104. '</td>
  <td colspan="1">' . $tipodi4. '</td>
  <td colspan="2">' . $aux4 . '</td>
 </tr>';
}
$row_t5 = '';
if($didesc5 != ''){
 $row_t5 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc5  . '</td>
  <td colspan="1">' . $cie105 . '</td>
  <td colspan="1">' . $tipodi5 . '</td>
  <td colspan="2">' . $aux5 . '</td>
 </tr>';
}
$row_t6 = '';
if($didesc6 != ''){
 $row_t6 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc6  . '</td>
  <td colspan="1">' . $cie106 . '</td>
  <td colspan="1">' . $tipodi6 . '</td>
  <td colspan="2">' . $aux6 . '</td>
 </tr>';
}
$row_t7 = '';
if($didesc7 != ''){
 $row_t7 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc7  . '</td>
  <td colspan="1">' . $cie107 . '</td>
  <td colspan="1">' . $tipodi7 . '</td>
  <td colspan="2">' . $aux7 . '</td>
 </tr>';
}
$row_t8 = '';
if($didesc8 != ''){
 $row_t8 = 
 '<tr nobr="true">
  <td colspan="2">' . $didesc8  . '</td>
  <td colspan="1">' . $cie108. '</td>
  <td colspan="1">' . $tipodi8. '</td>
  <td colspan="2">' . $aux8 . '</td>
 </tr>';
}



$tbl = '
<table border="1" cellpadding="2" cellspacing="2" align="center">
 <tr nobr="true">
  <td colspan="2"><b>FECHA</b><br /> ' . $_POST["his_fecha"] . '</td>
  <td colspan="2"><b>ESPECIALIDAD</b><br />' . $_POST["his_esp"] . '</td>
  <td colspan="2"><b>CONSULTORIO</b><br />' . $_POST["his_cons"] . '</td>
 </tr>
 <tr nobr="true">
  <th colspan="6" ><p style="font-size:11px"><b>FILIACION</b></p></th>
 </tr>
 <tr nobr="true">
  <td colspan="2"><b>APELLIDO PATERNO</b><br />' . $_POST["his_app"] . '</td>
  <td colspan="2"><b>APELLIDO MATERNO</b><br />' . $_POST["his_apm"] . '</td>
  <td colspan="2"><b>NOMBRES</b><br />' . $_POST["his_nom"] . '</td>
 </tr>
 <tr nobr="true">
  <td><b>DNI</b><br />' . $_POST["his_dni"] . '</td>
  <td><b>FECHA NAC.</b><br />' . $_POST["his_fnac"] . '</td>
  <td><b>EDAD</b><br />' . $_POST["his_edad"] . '</td>
  <td><b>SEXO</b><br />' . $_POST["his_sex"] . '</td>
  <td><b>TELEFONO</b><br />' . $_POST["his_tel"] . '</td>
  <td><b>TIPO PACIENTE</b><br />' . $_POST["his_tpac"] . '</td>
 </tr>

 <tr nobr="true">
  <th colspan="6" ><p style="font-size:11px"><b>ANAMNESIS</b></p></th>
 </tr>

 <tr nobr="true">
  <td colspan="3"><b>INICIO</b><br />' . $_POST["en_ac_ini"] . '</td>
  <td colspan="3"><b>CURSO</b><br />' . $_POST["en_ac_cur"] . '</td>
 </tr>

 <tr nobr="true">
  <th colspan="6" ><b>RELATO</b><br />' . $_POST["en_ac_rel"] . '</th>
 </tr>

 <tr nobr="true">
  <th colspan="6" ><p style="font-size:11px"><b>ANTECEDENTES</b></p></th>
 </tr>

 <tr nobr="true">
  <td><b>PERSONALES</b><br />' . $_POST["an_per"] . '</td>
  <td><b>PATOLOGIA</b><br />' . $_POST["an_epi"] . '</td>
  <td><b>QUIRURGICOS</b><br />' . $_POST["an_qui"] . '</td>
  <td><b>FAMILIARES</b><br />' . $_POST["an_fam"] . '</td>
  <td colspan="2"><b>OTROS</b><br />' . $_POST["an_otr"] . '</td>
 </tr>

 <tr nobr="true">
  <th colspan="6" ><p style="font-size:11px"><b>EXAMEN CLINICO</b></p></th>
 </tr>

 <tr nobr="true">
  <td><b>Peso</b><br />' . $_POST["ex_cl_ps"] . '</td>
  <td><b>Talla</b><br />' . $_POST["ex_cl_tl"] . '</td>
  <td><b>P.A.</b><br />' . $_POST["ex_cl_pa"] . '</td>
  <td><b>FC(x min)</b><br />' . $_POST["ex_cl_fc"] . '</td>
  <td><b>FR(x min)</b><br />' . $_POST["ex_cl_fr"] . '</td>
  <td><b>TEMP(C)</b><br />' . $_POST["ex_cl_tm"] . '</td>
 </tr>

 <tr nobr="true">
  <th colspan="6" ><b>EXAMEN CLINICO GENERAL</b><br />' . $_POST["ex_cl_eg"] . '</th>
 </tr>


 <tr nobr="true">
  <th colspan="6" ><p style="font-size:11px"><b>EXAMEN ESPECIFICO</b></p></th>
 </tr>

 <tr nobr="true">
  <td colspan="2"><b>DIAGNOSTICO</b></td>
  <td colspan="1"><b>CIE 10</b></td>
  <td colspan="1"><b>TIPO</b></td>
  <td colspan="2"><b>Examenes Auxiliares</b></td>
 </tr>
' . $row_t1 . $row_t2. $row_t3. $row_t4. $row_t5. $row_t6. $row_t7. $row_t8. '


 <tr nobr="true" style="border-collapse: collapse;">
  <th colspan="6" ><p style="font-size:11px"><b>TRATAMIENTO</b></p></th>
 </tr>
 <tr nobr="true">
  <td colspan="2"><b>MEDICAMENTO</b></td>
  <td colspan="1"><b>CANT.</b></td>
  <td colspan="2"><b>DOSIS</b></td>
  <td colspan="1"><b>DIAS</b></td>  
 </tr>
' . $row_m1 . $row_m2. $row_m3. $row_m4. $row_m5. $row_m6. '


</table>
';



$obj_pdf->writeHTML($tbl, true, false, false, false, '');
// ob_start();
//     $content = ob_get_contents();
// ob_end_clean();
// $obj_pdf->writeHTML($content, true, false, true, false, '');
$obj_pdf->Output('output.pdf', 'I');