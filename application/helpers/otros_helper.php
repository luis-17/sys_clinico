<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// if(empty($_SESSION['sess_vs_talario'])){
//    var_dump($_COOKIE);
//    // var_dump($_SESSION);
//    exit();
//   }

function GetLastId($campoId,$table){
    $ci2 =& get_instance();
    $ci2->db->select('MAX('.$campoId.') AS id',FALSE);
    $ci2->db->from($table);
    $fData = $ci2->db->get()->row_array();
    return $fData['id'];
}
function getIndexArrayByValue($arr,$arrFields,$arrValores)
{
	$arrKeys = array();
  foreach($arr as $key => $value){ 
  	$siCumple = TRUE;
		foreach ($arrValores as $keyV => $value2) { 
			if ( $value[$arrFields[$keyV]] == $value2 ){
				$arrKeys[] = $key;
			}else{
				$siCumple = FALSE;
			}
		}	
		if( $siCumple ){
			return $key;
		}
  }
  return false;
}
// para verificar si un string esta compuesto de solo numeros sin comas ni puntos
function soloNumeros($laCadena) {
    $carsValidos = "0123456789";
    for ($i=0; $i<strlen($laCadena); $i++) {
      if (strpos($carsValidos, substr($laCadena,$i,1))===false) {
         return false; 
      }
    }
    return true; 
}

function strtoupper_total($string){ 
  return strtr(strtoupper($string),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ");
}
function strtolower_total($string){ 
  return strtr(strtolower($string),"ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ","àèìòùáéíóúçñäëïöü");
}

function comprobar_email($email){ 
    $mail_correcto = FALSE; 
    //compruebo unas cosas primeras 
    if ((strlen($email) >= 6) && (substr_count($email,"@") == 1) && (substr($email,0,1) != "@") && (substr($email,strlen($email)-1,1) != "@")){ 
        if ((!strstr($email,"'")) && (!strstr($email,"\"")) && (!strstr($email,"\\")) && (!strstr($email,"\$")) && (!strstr($email," "))) { 
          //miro si tiene caracter . 
          if (substr_count($email,".")>= 1){ 
              //obtengo la terminacion del dominio 
              $term_dom = substr(strrchr ($email, '.'),1); 
              //compruebo que la terminación del dominio sea correcta 
              if (strlen($term_dom)>1 && strlen($term_dom)<5 && (!strstr($term_dom,"@")) ){ 
                //compruebo que lo de antes del dominio sea correcto 
                $antes_dom = substr($email,0,strlen($email) - strlen($term_dom) - 1); 
                $caracter_ult = substr($antes_dom,strlen($antes_dom)-1,1); 
                if ($caracter_ult != "@" && $caracter_ult != "."){ 
                    $mail_correcto = 1; 
                } 
              } 
          } 
        } 
    } 
    return $mail_correcto; 
}

function generar_notificacion_evento($idtipoevento, $key_evento, $data){
  //print_r($data);
  if($idtipoevento == 1 && $key_evento='key_prog_med'){
    return'Ha sido CARGADA la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_item'] . ' Turno de ' . $data['turno'] . ' en el ambiente ' . $data['ambiente'];
  }   

  if($idtipoevento == 2 && $key_evento='key_prog_med'){
    return 'Ha sido ANULADA la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_programada'] . ' Turno de ' . $data['hora_inicio'] . ' a ' . $data['hora_fin'] . ' en el ambiente ' . $data['ambiente']['numero_ambiente'];
  }  

  if($idtipoevento == 3 && $key_evento='key_prog_med'){
    return 'Ha sido CANCELADA la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_programada'] . ' Turno de ' . $data['hora_inicio'] . ' a ' . $data['hora_fin'] . ' en el ambiente ' . $data['ambiente']['numero_ambiente'];
  }

  if($idtipoevento == 4 && $key_evento='key_prog_med'){
    if(strtotime($data['fecha_item']) != strtotime($data['fecha_old_item']) ){
      $texto = 'Ha sido MODIFICADO EL TURNO de la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_old_item'] . ' en el ambiente ' . $data['ambiente'];
      $texto .= '. Nueva Fecha: ' . $data['fecha_item'];
    }else{
      $texto = 'Ha sido MODIFICADO EL TURNO de la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_item'] . ' en el ambiente ' . $data['ambiente'];
    }
    $texto .= '. Nuevo Turno: ' . $data['nuevo_turno'];
    return $texto;
  }  

  if($idtipoevento == 5 && $key_evento='key_prog_med'){
    $texto = 'Ha sido MODIFICADO LA CANTIDAD DE CUPOS ADICIONALES de la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_item'] . ' Turno ' . $data['turno'] . ' en el ambiente ' . $data['ambiente'];
    $texto .= '. Nueva CANTIDAD DE CUPOS ADICIONALES: ' . $data['cupos_adicionales'];
    return $texto;
  }

  if($idtipoevento == 9 && $key_evento='key_prog_med'){
    $texto = 'Ha sido MODIFICADO EL AMBIENTE de la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_item'] . ' Turno ' . $data['turno'];
    $texto .= '. Nuevo ambiente ' . $data['ambiente'];
    return $texto;
  } 

  if($idtipoevento == 11 && $key_evento='key_prog_med'){
    $texto = 'Ha sido MODIFICADO CUPOS/INTERVALO de la Programación del Médico '. $data['medico'] . ' de la Especialidad ' . $data['especialidad'] . ' de fecha ' . $data['fecha_item'] . ' Turno ' . $data['turno'];
    $texto .= '. Nueva CANTIDAD DE CUPOS: ' . $data['total_cupos'];
    $texto .= '. Nuevo INTERVALO DE ATENCIÓN: ' . $data['intervalo'];
    return $texto;
  } 
}

function enviar_mail_paciente($tipo, $citaPaciente){
  $ci2 =& get_instance();
  $ci2->load->library('My_PHPMailer');
  $hoydate = date("Y-m-d H:i:s");
  date_default_timezone_set('UTC');
  define('SMTP_HOST','mail.villasalud.pe');
  $correo = 'sistemas.ti@villasalud.pe';
  $pass = 'franzsheskoli';
  $setFromAleas = 'Dirección Médica';

  define('SMTP_PORT',25);
  define('SMTP_USERNAME',$correo);
  define('SMTP_PASSWORD',$pass);
  
  $mail = new PHPMailer();
  $mail->IsSMTP(true);
  //$mail->SMTPDebug = 2;
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = "tls";
  $mail->Host = SMTP_HOST;
  $mail->Port = SMTP_PORT;
  $mail->Username =  SMTP_USERNAME;
  $mail->Password = SMTP_PASSWORD;
  $mail->SetFrom(SMTP_USERNAME,$setFromAleas);
  $mail->AddReplyTo(SMTP_USERNAME,$setFromAleas);

  $mail->Subject = 'NOTIFICACIÓN HOSPITAL VILLA SALUD';

  $cuerpo = '<html> 
      <head>
        <title>PROGRAMACIÓN DE CITA MÉDICA</title> 
      </head>
      <body style="font-family: sans-serif;padding: 10px 40px;" > 
      <div style="text-align: right;">
        <img style="width: 160px;" alt="Hospital Villa Salud" src="'.base_url('assets/img/dinamic/empresa/gm_small.png').'">
        </div> <br />';
  $cuerpo .= '<div style="font-size:16px;">  
          Estimado(a) paciente: '. $citaPaciente['paciente'].' <br /> <br /> ';

  if($tipo == 1){
    $cuerpo .= 'Mediante el presente se le informa, que ha sido <b>registrada</b> una <u>NUEVA CITA MÉDICA</u>. <br /> ';     
  }else if($tipo == 2){
    $cuerpo .= 'Mediante el presente se le informa, que ha sido <b>reprogramada</b> su cita. A continuación los datos de su <u>NUEVA CITA MÉDICA</u>. <br /> ';
  }else if($tipo == 3){
    $cuerpo .= 'Mediante el presente se le informa, que ha sido <b>modificada</b> su cita. A continuación los datos de su <u>NUEVA CITA MÉDICA</u>. <br /> ';
  }else if($tipo == 4){
    $cuerpo .= 'Mediante el presente se le informa, que ha sido <b>cancelada</b> su <u>CITA MÉDICA</u>. <br /> ';
  }

  $cuerpo .= '<p>Especialidad: <b>'. $citaPaciente['especialidad'] .'</b></p>';
  $cuerpo .= '<p>Fecha: <b>'. $citaPaciente['fecha_programada'] .'</b> - Turno: '. $citaPaciente['turno'] .'</p>';
  $cuerpo .= '<p>Sede: <b>'.$citaPaciente['sede'] .'</b></p>';  
  $cuerpo .= '<p>Consultorio: <b>'.$citaPaciente['ambiente'] .'</b></p>';

  $cuerpo .= '<br /> Atte: <br /> <br /> DIRECCIÓN MÉDICA </div>';
  $cuerpo .= '</body></html>';
  $mail->AltBody = $cuerpo;
  $mail->MsgHTML($cuerpo);
  $correoPaciente = $citaPaciente['email']; 
  $mail->CharSet = 'UTF-8';
  //$mail->AddBCC("ymartinez@villasalud.pe");
  //$mail->AddBCC("rluna@villasalud.pe");

  if($correoPaciente != null && $correoPaciente != ''){
    if(comprobar_email($correoPaciente)){
      $mail->AddAddress($correoPaciente);
      if($mail->Send()){
        return array(
          'flag' => 1,
          'msgMail' => 'Notificación de correo enviada exitosamente.');
      }else{
        return array(
          'flag' => 0,
          'msgMail' => 'Notificación de correo NO enviada.');
      }
    }else{
      return array(
          'flag' => 2,
          'msgMail' => 'Notificación de correo NO enviada. Correo de Paciente invalido.'); 
    }
  }else{
    return array(
          'flag' => 3,
          'msgMail' => 'Notificación de correo NO enviada. Correo de Paciente no registrado.');
  }
}

function getConfig($tipo = FALSE, $id = FALSE, $incluye_privados = FALSE){
  $ci2 =& get_instance();
  $ci2->db->select('cc.key, cc.value');
  $ci2->db->from('ce_configuracion cc');

  if($tipo){
    $ci2->db->where('cc.tipo ', $tipo);

    if($id){
      $ci2->db->where('cc.idsedeempresaadmin IS NULL OR cc.idsedeempresaadmin = '.$id);
    }
  }
  
  if(!$incluye_privados){
    $ci2->db->where('cc.si_key_publico', 1);
  }

  $fData = $ci2->db->get()->result_array();

  $data = array();
  foreach ($fData as $key => $value) {
    $data[$value['key']] = $value['value'];
  }
  return $data;
}

function createColumnsArray($end_column, $first_letters = ''){
  $columns = array();
  $length = strlen($end_column);
  $letters = range('A', 'Z');

  // Iterate over 26 letters.
  foreach ($letters as $letter) {
    // Paste the $first_letters before the next.
    $column = $first_letters . $letter;

    // Add the column to the final array.
    $columns[] = $column;

    // If it was the end column that was added, return the columns.
    if ($column == $end_column)
      return $columns;
  }

  // Add the column children.
  foreach ($columns as $column) {
    // Don't itterate if the $end_column was already set in a previous itteration.
    // Stop iterating if you've reached the maximum character length.
    if (!in_array($end_column, $columns) && strlen($column) < $length) {
      $new_columns = createColumnsArray($end_column, $column);
      // Merge the new columns which were created with the final columns array.
      $columns = array_merge($columns, $new_columns);
    }
  }

  return $columns;
}
