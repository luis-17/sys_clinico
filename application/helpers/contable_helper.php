<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  function ValorEnLetras($x, $Moneda ){ 

      $Void = ""; 
      $Dot = "."; 
      $Zero = "0";
      $Neg = "Menos";

      $s=""; 
      $Ent=""; 
      $Frc=""; 
      $Signo=""; 
           
      if(floatVal($x) < 0) 
       $Signo = $Neg . " "; 
      else 
       $Signo = ""; 
       
      if(intval(number_format($x,2,'.','') )!=$x) //<- averiguar si tiene decimales 
        $s = number_format($x,2,'.',''); 
      else 
        $s = number_format($x,2,'.',''); 
          
      $Pto = strpos($s, $Dot); 
           
      if ($Pto === false) 
      { 
        $Ent = $s; 
        $Frc = $Void; 
      } 
      else 
      { 
        $Ent = substr($s, 0, $Pto ); 
        $Frc =  substr($s, $Pto+1); 
      } 

      if($Ent == $Zero || $Ent == $Void) 
         $s = "Cero "; 
      elseif( strlen($Ent) > 7) 
      { 
         $s = SubValLetra(intval( substr($Ent, 0,  strlen($Ent) - 6))) .  
               "Millones " . SubValLetra(intval(substr($Ent,-6, 6))); 
      } 
      else 
      { 
        $s = SubValLetra(intval($Ent)); 
      } 

      if (substr($s,-9, 9) == "Millones " || substr($s,-7, 7) == "Millón ") 
         $s = $s . "de "; 

      

      if($Frc != $Void) 
      { 
         $s = $s . " con " . $Frc. "/100 "; 
         //$s = $s . " " . $Frc . "/100"; 
      } 
      $s = strtoupper($s) . $Moneda; 
      $letrass=$Signo . $s . ""; 
      return ($Signo . $s . ""); 
  } 
  function SubValLetra($numero){ 
      $Void = ""; 
      $SP = " "; 
      $Zero = "0";

      $Ptr=""; 
      $n=0; 
      $i=0; 
      $x =""; 
      $Rtn =""; 
      $Tem =""; 

      $x = trim("$numero"); 
      $n = strlen($x); 

      $Tem = $Void; 
      $i = $n; 
       
      while( $i > 0) 
      { 
         $Tem = Parte(intval(substr($x, $n - $i, 1).  
                             str_repeat($Zero, $i - 1 ))); 
         If( $Tem != "Cero" ) 
            $Rtn .= $Tem . $SP; 
         $i = $i - 1; 
      } 

       
      //--------------------- GoSub FiltroMil ------------------------------ 
      $Rtn=str_replace(" Mil Mil", " Un Mil", $Rtn ); 
      while(1) 
      { 
         $Ptr = strpos($Rtn, "Mil ");        
         If(!($Ptr===false)) 
         { 
            If(! (strpos($Rtn, "Mil ",$Ptr + 1) === false )) 
              ReplaceStringFrom($Rtn, "Mil ", "", $Ptr); 
            Else 
             break; 
         } 
         else break; 
      } 

      //--------------------- GoSub FiltroCiento ------------------------------ 
      $Ptr = -1; 
      do{ 
         $Ptr = strpos($Rtn, "Cien ", $Ptr+1); 
         if(!($Ptr===false)) 
         { 
            $Tem = substr($Rtn, $Ptr + 5 ,1); 
            if( $Tem == "M" || $Tem == $Void) 
               ; 
            else           
               ReplaceStringFrom($Rtn, "Cien", "Ciento", $Ptr); 
         } 
      }while(!($Ptr === false)); 

      //--------------------- FiltroEspeciales ------------------------------ 
      $Rtn=str_replace("Diez Un", "Once", $Rtn ); 
      $Rtn=str_replace("Diez Dos", "Doce", $Rtn ); 
      $Rtn=str_replace("Diez Tres", "Trece", $Rtn ); 
      $Rtn=str_replace("Diez Cuatro", "Catorce", $Rtn ); 
      $Rtn=str_replace("Diez Cinco", "Quince", $Rtn ); 
      $Rtn=str_replace("Diez Seis", "Dieciseis", $Rtn ); 
      $Rtn=str_replace("Diez Siete", "Diecisiete", $Rtn ); 
      $Rtn=str_replace("Diez Ocho", "Dieciocho", $Rtn ); 
      $Rtn=str_replace("Diez Nueve", "Diecinueve", $Rtn ); 
      $Rtn=str_replace("Veinte Un", "Veintiun", $Rtn ); 
      $Rtn=str_replace("Veinte Dos", "Veintidos", $Rtn ); 
      $Rtn=str_replace("Veinte Tres", "Veintitres", $Rtn ); 
      $Rtn=str_replace("Veinte Cuatro", "Veinticuatro", $Rtn ); 
      $Rtn=str_replace("Veinte Cinco", "Veinticinco", $Rtn ); 
      $Rtn=str_replace("Veinte Seis", "Veintiseís", $Rtn ); 
      $Rtn=str_replace("Veinte Siete", "Veintisiete", $Rtn ); 
      $Rtn=str_replace("Veinte Ocho", "Veintiocho", $Rtn ); 
      $Rtn=str_replace("Veinte Nueve", "Veintinueve", $Rtn ); 

      //--------------------- FiltroUn ------------------------------ 
      If(substr($Rtn,0,1) == "M") $Rtn = "Un " . $Rtn; 
      //--------------------- Adicionar Y ------------------------------ 
      for($i=65; $i<=88; $i++) 
      { 
        If($i != 77) 
           $Rtn=str_replace("a " . Chr($i), "* y " . Chr($i), $Rtn); 
      } 
      $Rtn=str_replace("*", "a" , $Rtn); 
      return($Rtn); 
  } 
  function ReplaceStringFrom(&$x, $OldWrd, $NewWrd, $Ptr){ 
    $x = substr($x, 0, $Ptr)  . $NewWrd . substr($x, strlen($OldWrd) + $Ptr); 
  } 
  function Parte($x){ 
      $Void = ""; 

      $Rtn=''; 
      $t=''; 
      $i=''; 
      Do 
      { 
        switch($x) 
        { 
           Case 0:  $t = "Cero";break; 
           Case 1:  $t = "Un";break; 
           Case 2:  $t = "Dos";break; 
           Case 3:  $t = "Tres";break; 
           Case 4:  $t = "Cuatro";break; 
           Case 5:  $t = "Cinco";break; 
           Case 6:  $t = "Seis";break; 
           Case 7:  $t = "Siete";break; 
           Case 8:  $t = "Ocho";break; 
           Case 9:  $t = "Nueve";break; 
           Case 10: $t = "Diez";break; 
           Case 20: $t = "Veinte";break; 
           Case 30: $t = "Treinta";break; 
           Case 40: $t = "Cuarenta";break; 
           Case 50: $t = "Cincuenta";break; 
           Case 60: $t = "Sesenta";break; 
           Case 70: $t = "Setenta";break; 
           Case 80: $t = "Ochenta";break; 
           Case 90: $t = "Noventa";break; 
           Case 100: $t = "Cien";break; 
           Case 200: $t = "Doscientos";break; 
           Case 300: $t = "Trescientos";break; 
           Case 400: $t = "Cuatrocientos";break; 
           Case 500: $t = "Quinientos";break; 
           Case 600: $t = "Seiscientos";break; 
           Case 700: $t = "Setecientos";break; 
           Case 800: $t = "Ochocientos";break; 
           Case 900: $t = "Novecientos";break; 
           Case 1000: $t = "Mil";break; 
           Case 1000000: $t = "Millón";break; 
        } 

        If($t == $Void) 
        { 
          $i = $i + 1; 
          $x = $x / 1000; 
          If($x== 0) $i = 0; 
        } 
        else 
           break; 
              
      }while($i != 0); 
      
      $Rtn = $t; 
      Switch($i) 
      { 
         Case 0: $t = $Void;break; 
         Case 1: $t = " Mil";break; 
         Case 2: $t = " Millones";break; 
         Case 3: $t = " Billones";break; 
      } 
      return($Rtn . $t); 
  } 
  function ObtenerTipoCambio()
  {
    $ci =& get_instance();
    $arrTipoCambio = $ci->model_config->m_cargar_tipo_cambio();
    // foreach ($lista as $key => $row) { 
    //   $arrTipoCambio[$row['key_cf']] = $row['valor_cf'];
    // }
    return $arrTipoCambio;
  }
  function GetVariableLey(){
    $ci =& get_instance();
    $varibales = $ci->model_config->m_cargar_variables_ley();
    return $varibales;
  }
  function CalculoRentaQuinta($mes, $remuneraciones, $gratificaciones = 0, $remuneracion_anterior = 0, $retencion_anterior = 0){
    $remuneraciones = str_replace(',','',$remuneraciones);
    $gratificaciones = str_replace(',','',$gratificaciones);
    $remuneracion_anterior = str_replace(',','',$remuneracion_anterior);

    if(!is_numeric($remuneraciones) || !is_numeric($gratificaciones) || !is_numeric($remuneracion_anterior)|| !is_numeric($mes)){
      return null;
    }
    $bonificacion = 0;    
    $mes = 12 - $mes; // para proyeccion
    $meses_faltantes = $mes + 1;
    $variable = GetVariableLey();
     
    $tasas= array(
      array('porc' => '8',  'uits' => ($variable['uit'] * 5)),
      array('porc' => '14', 'uits' => ($variable['uit'] * 20)),
      array('porc' => '17', 'uits' => ($variable['uit'] * 35)),
      array('porc' => '20', 'uits' => ($variable['uit'] * 45)),
      array('porc' => '30', 'uits' => ($variable['uit'] * 45)),
    );


    $bonificacion = floatval($gratificaciones * (9/100)); 
    //  var_dump("bonificacion ".$bonificacion);
    $gratificaciones = $gratificaciones * 2; 
    $bonificacion = $bonificacion * 2;
    // var_dump("bonificacion ".$bonificacion);

    $remuneraciones = ($remuneraciones * $mes) + $remuneraciones;
    $remuneracion_bruta_anual = $remuneraciones + $gratificaciones + $bonificacion + $remuneracion_anterior; 
    $deduccion7uit = (-$variable['uit'] * 7);

    if($remuneracion_bruta_anual < abs($deduccion7uit)){
      return 0;
    }
    
    $remuneracion_neta_anual = floatval($remuneracion_bruta_anual) + floatval($deduccion7uit);
    $restante = floatval($remuneracion_neta_anual);
    $impuesto = 0; $band=false;
    foreach ($tasas as $key => $value) {
      $aux=0;
      if($remuneracion_neta_anual > $value['uits']){
        $aux = floatval($value['uits'] * ($value['porc']/100));
        $impuesto = $impuesto + $aux;
        $restante = floatval($restante) - floatval($value['uits']);
      }else{
        if(!$band){
          $aux = floatval($restante * ($value['porc']/100));
          $impuesto = $impuesto + $aux;
          $band=true;
        }        
      }
    }
    // var_dump($impuesto);
    //  exit();
    // $impuesto = round((($impuesto ) / 12 ), 0);
    $impuesto = round((($impuesto - $retencion_anterior ) / $meses_faltantes ), 0);
    return $impuesto;
  }

  function fnOrdering($a, $b) { 
        return $a['fecha_timestamp'] - $b['fecha_timestamp'];
  }

  function creacionEstructuraAsistencia($lista,$allInputs,$fEmpleado = FALSE)
  {
    $ci =& get_instance();
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
      $listaFE = $ci->model_asistencia->m_cargar_estas_fechas_especiales_de_empleado($arrParams); 
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
    $listaDF = $ci->model_feriado->m_cargar_feriados_entre_fechas($allInputs); 
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

  function CalculoFaltasTardanzasEmpleado($empleado, $fechaDesde, $fechaHasta){    
    $sumEstadoFalta = 0;
    $sumEstadoVacaciones=0;
    $inicioVac = NULL;
    $finVac = NULL;
    $sumTardanza = '00:00:00';
    $sumTardanzaBreak = '00:00:00';
    $ci =& get_instance();

    /* CARGAR DATOS */     
    $empleado['id'] = $empleado['idempleado'];
    $datos['empleado'] = $empleado;

    $fechaFinMes = date('Y-m-d',strtotime(substr($fechaHasta, 0,7).'-'.date('t',strtotime($fechaHasta)) ));

    $datos['desde'] = $fechaDesde;
    //$datos['hasta'] =  $fechaHasta;
    $datos['hasta'] =  $fechaFinMes;
    $datos['desdeHora'] = '00';
    $datos['desdeMinuto'] = '00';
    $datos['hastaHora'] = '23';  
    $datos['hastaMinuto'] = '59';
    $lista = $ci->model_asistencia->m_cargar_asistencias_de_empleado_reporte($datos);

    $arrMainArray = creacionEstructuraAsistencia($lista,$datos,$datos['empleado']);
        
    foreach ($arrMainArray as $key => $row) { 
      $rowFecha = $row['fecha']; 
         
      /* PRIMERA ENTRADA  */
      $horaMarcadoE = NULL;
      if( !empty($arrMainArray[$key]['bloques']['entradas']) ){
        $horaMarcadoE = $arrMainArray[$key]['bloques']['entradas'][0]['hora'];
      }
      
      /* BREAK */
      $horaMarcado1B = NULL;
      if( !empty($arrMainArray[$key]['bloques']['break']) ){
        $horaMarcado1B = $arrMainArray[$key]['bloques']['break'][0]['hora'];
      }
      $horaMarcado2B = NULL;
      if( !empty($arrMainArray[$key]['bloques']['break']) && !empty($arrMainArray[$key]['bloques']['break'][1]) ){
        $horaMarcado2B = $arrMainArray[$key]['bloques']['break'][count($arrMainArray[$key]['bloques']['break']) - 1]['hora'];
      }
      $totalBreakB = NULL;
      if( !empty($horaMarcado1B) && !empty($horaMarcado2B) && $rowFecha <= $fechaHasta){ 
        $totalBreakB = dif_horas_transcurridas($horaMarcado1B,$horaMarcado2B);
        
        if( strtotime($totalBreakB)  > strtotime("01:00:00")){
          $totalBreakB = dif_horas_transcurridas('01:00:00',$totalBreakB);
          $sumTardanzaBreak = suma_horas_minutos($sumTardanzaBreak, $totalBreakB );
        }       
      }
      $estadoPuntual = array(
        'simbolo'=> NULL,
        'diferencia'=> NULL
      );
      $estadoTardanza = array(
        'simbolo'=> NULL,
        'diferencia'=> NULL
      );

      /* VALIDAR TARDANZAS */
      $estadoFalta = NULL;
      if( !empty($horaMarcadoE) && $rowFecha <= $fechaHasta){ 
       if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 1 ){
          $estadoPuntual['simbolo'] = 'X';
          $estadoPuntual['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
        }
        if( $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 2 || $arrMainArray[$key]['bloques']['entradas'][0]['numEstado'] == 3 ){
          $estadoTardanza['simbolo'] = 'X';
          $estadoTardanza['diferencia'] = $arrMainArray[$key]['bloques']['entradas'][0]['diferencia_tiempo'];
          $sumTardanza = suma_horas_minutos($sumTardanza, $estadoTardanza['diferencia'] ); 
        }
      }

      /* VALIDAR SI ES FALTA */ 
      if( empty($estadoPuntual['simbolo']) && empty($estadoTardanza['simbolo']) ){ 
        $tieneHorarioEspecial = TRUE;
        $fHorarioEspecial = $ci->model_horario_especial->m_obtener_horario_especial_de_empleado($datos['empleado']['idempleado'],$row['fecha']); 
        if( empty($fHorarioEspecial) ){ 
          $tieneHorarioEspecial = FALSE; 
        }
        if($tieneHorarioEspecial === FALSE){ 
          /* HORARIO GENERAL */
          $tieneHorarioGeneral = TRUE;
          $arrDiasSemana = array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'); 
          $diaSemana = date('w',strtotime("$rowFecha")); 
          $fHorarioGeneral = $ci->model_horario_general->m_obtener_horario_general_de_empleado($datos['empleado']['idempleado'],$arrDiasSemana[$diaSemana]);
          if( empty($fHorarioGeneral) ){ 
            $tieneHorarioGeneral = FALSE; 
          }
        }
        $motivo = $row['motivo_fecha_especial'];
        $array_motivos = array(
          'FERIADO',
          'VACACIONES-VACACIONES PARCIALES',
          'VACACIONES-VACACIONES COMPLETAS',
          );
        if( ($tieneHorarioEspecial || $tieneHorarioGeneral) && !in_array($motivo,$array_motivos) && $rowFecha <= $fechaHasta){ 
          if( strtotime($rowFecha) < strtotime(date('Y-m-d')) ){
            $estadoFalta = 'X';
            $sumEstadoFalta = $sumEstadoFalta + 1;
          }
        }

        $array_vac = array(
          'VACACIONES-VACACIONES PARCIALES',
          'VACACIONES-VACACIONES COMPLETAS',
          );

        if( $tieneHorarioEspecial && in_array($motivo,$array_vac) && $rowFecha >= $fechaDesde && $rowFecha <= $fechaFinMes){ 
          if( strtotime($rowFecha) < strtotime(date('Y-m-d')) ){
            if(empty($inicioVac)){
              $inicioVac = $rowFecha;
            } 
            $finVac = $rowFecha;

            $sumEstadoVacaciones++;
          }
        } 
      }   
    }

    $resul = array(
      "tardanza" => (int)date('i', strtotime($sumTardanza)),
      "falta" => $sumEstadoFalta,
      "diasVacaciones" => $sumEstadoVacaciones,
      "inicioVacaciones" => $inicioVac,
      "finVacaciones" => $finVac,
      "tardanzaBreak" => (int)date('i', strtotime($sumTardanzaBreak)),
    );

    /*if($idempleado == 823){
      print_r($resul);
    }*/

    return $resul;
  } 

  function objectToArray($d) {
      if (is_object($d)) {
          // Gets the properties of the given object
          // with get_object_vars function
          $d = get_object_vars($d);
      }
  
      if (is_array($d)) {
          /*
          * Return array converted to object
          * Using __FUNCTION__ (Magic constant)
          * for recursive call
          */
          return array_map(__FUNCTION__, $d);
      }
      else {
          // Return array
          return $d;
      }
  }

  function asignarValorConcepto($arrayConceptos, $codigo_plame, $value){
    foreach ($arrayConceptos as $index => $tipoConcepto) {
      foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {
        foreach ($categoria['conceptos'] as $indexConcepto => $concepto) {
          if($concepto['codigo_plame'] == $codigo_plame){
            if($concepto['estado_pc_empleado'] == 1){
              $arrayConceptos[$index]['categorias'][$indexCat]['conceptos'][$indexConcepto]['valor_empleado'] = $value;
            }else{
              $arrayConceptos[$index]['categorias'][$indexCat]['conceptos'][$indexConcepto]['valor_empleado'] = 0;
            }                    
            return $arrayConceptos;
          }
        }
      }
    }
    return $arrayConceptos;
  }   

  function obtenerValorConcepto($arrayConceptos, $codigo_plame){
    $valor=0;
    foreach ($arrayConceptos as $index => $tipoConcepto) {
      foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {
        foreach ($categoria['conceptos'] as $indexConcepto => $concepto) {
          if($concepto['codigo_plame'] == $codigo_plame){
            if($concepto['estado_pc_empleado'] == 1){
              $valor = $arrayConceptos[$index]['categorias'][$indexCat]['conceptos'][$indexConcepto]['valor_empleado'];
            }else{
              $valor = 0;
            }  
            return $valor;                  
          }
        }
      }
    }
    return $valor;
  }  

  function asignarEstadoConcepto($arrayConceptos, $codigo_plame, $estado){
    foreach ($arrayConceptos as $index => $tipoConcepto) {
      foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {
        foreach ($categoria['conceptos'] as $indexConcepto => $concepto) {
          if($concepto['codigo_plame'] == $codigo_plame){
              $arrayConceptos[$index]['categorias'][$indexCat]['conceptos'][$indexConcepto]['estado_pc_empleado'] = $estado;  
              return $arrayConceptos;                 
          }
        }
      }
    }
    return $arrayConceptos;
  }

  function obtenerEstadoConcepto($arrayConceptos, $codigo_plame){
    $estado=0;
    foreach ($arrayConceptos as $index => $tipoConcepto) {
      foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {
        foreach ($categoria['conceptos'] as $indexConcepto => $concepto) {
          if($concepto['codigo_plame'] == $codigo_plame){
            $estado = $concepto['estado_pc_empleado'];
            return $estado;                   
          }
        }
      }
    }
    return $estado;
  }  

  function existeConcepto($arrayConceptos, $codigo_plame){
    $estado=FALSE;
    foreach ($arrayConceptos as $index => $tipoConcepto) {
      if(!$estado){
        foreach ($tipoConcepto['categorias'] as $indexCat => $categoria) {
          foreach ($categoria['conceptos'] as $indexConcepto => $concepto) {
            if($concepto['codigo_plame'] == $codigo_plame){
              $estado = TRUE;
              return $estado;                   
            }
          }
        }        
      }
    }
    return $estado;
  }