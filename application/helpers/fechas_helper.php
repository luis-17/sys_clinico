<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	function restafechas($fecha_i,$fecha_f){
		$dias = (strtotime($fecha_i)-strtotime($fecha_f))/86400;
		$dias = abs($dias); $dias = floor($dias);
		if($dias < 1) {
			$dias = 1;
		}
		return $dias;
	}
	function agregardiasfecha($fecha_i,$dias){
		$nuevafecha = strtotime ( '+'.$dias.' day' , strtotime ( $fecha_i ) ) ;
		$nuevafecha = date ( 'd-m-Y' , $nuevafecha );
		return $nuevafecha;
	}
	function minutos_transcurridos($fecha_i,$fecha_f) {
		$minutos = (strtotime($fecha_i)-strtotime($fecha_f))/60;
		$minutos = abs($minutos); $minutos = floor($minutos);
		return $minutos;
	}
	function dif_horas_transcurridas($hora_i,$hora_f) {
		if(strtotime($hora_f) < strtotime($hora_i)){
			return false;
		}

		$dif_hora = date('H:i:s', strtotime("00:00:00") + strtotime($hora_f) - strtotime($hora_i) );
		return $dif_hora;
	}
	function suma_horas_minutos( $time1, $time2 ){
	    list($hour1, $min1, $sec1) = explode(":", $time1);
	    list($hour2, $min2, $sec2) = explode(":", $time2);
	    return date('H:i:s', mktime( $hour1 + $hour2, $min1 + $min2, $sec1 + $sec2));
	}
	/**
	 * Funcion que dado un valor timestamp, devuelve el numero de dias, horas
	 * minutos y segundos
	 * Ejemplo: timestampToHuman(strtotime(date1)-strtotime(date2))
	*/
	function timestampToHuman($timestamp)
	{
		$negativo = FALSE; 
		$return="";
		if( $timestamp < 0 ){ 
			$negativo = TRUE;
			$timestamp = $timestamp * -1;
		}
		# Obtenemos el numero de dias
		$days=floor((($timestamp/60)/60)/24);
		if($days>0)
		{
			$timestamp-=$days*24*60*60;
			$return.=$days." días ";
		}
		# Obtenemos el numero de horas
		$hours=floor(($timestamp/60)/60);
		if($hours>0)
		{
			$timestamp-=$hours*60*60;
			$return.=str_pad($hours, 2, "0", STR_PAD_LEFT).":";
		}else
			$return.="00:";
		# Obtenemos el numero de minutos
		$minutes=floor($timestamp/60);
		if($minutes>0)
		{
			$timestamp-=$minutes*60;
			$return.=str_pad($minutes, 2, "0", STR_PAD_LEFT).":";
		}else
			$return.="00:";
		# Obtenemos el numero de segundos
		$return.=str_pad($timestamp, 2, "0", STR_PAD_LEFT);
		if( $negativo ){
			$return = '-'.$return; 
		}
		return $return;
	}
	function get_rangofechas($start, $end,$onlyDate = FALSE) {
		//var_dump($end);
	    $range = array();
	    if (is_string($start) === true) $start = strtotime($start);
	    if (is_string($end) === true ) $end = strtotime($end);
	    //if ($start > $end) return createDateRangeArray($end, $start);
	    do {
	    	if($onlyDate) {
	    		$range[] = date('Y-m-d', $start);
	        	$start = strtotime("+ 1 day", $start);
	    	}else{
	    		$range[] = date('Y-m-d H:i:s', $start);
	        	$start = strtotime("+ 1 day", $start);
	    	}

	    } while($start <= $end);
	    if(count($range) < 1) {
	    	if($onlyDate) { $range[] = date('Y-m-d'); }
	    	else{ $range[] = date('Y-m-d H:i:s'); }
	    }
	    return $range;
	}
	function get_fecha_inicio_y_fin($anio,$mes)
	{
		$arrFechas['inicio'] = $anio.'-'.$mes.'-01'; 
		$numeroDias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio); 
		$arrFechas['fin'] = $anio.'-'.$mes.'-'.$numeroDias; 
		return $arrFechas; 
	}
	function get_dias_transcurridos($start, $end) {
		//var_dump($start,$end); exit();
		$diasTranscurridos = 0;
	    if (is_string($start) === true) $start = strtotime($start);
	    if (is_string($end) === true ) $end = strtotime($end);
	    //if ($start > $end) return createDateRangeArray($end, $start);
	    do {
	    	$diasTranscurridos++;
	    	$start = strtotime("+ 1 day", $start);
	    } while($start <= $end);
	    if($diasTranscurridos < 1) {
	    	return false;
	    }
	    return $diasTranscurridos;
	}
	function get_rangohoras($start, $end) {
		//var_dump($end);
	    $range = array();
	    if (is_string($start) === true) {
	    	$start = strtotime($start);
	    }
	    if (is_string($end) === true ) {
	    	$end = strtotime($end);
	    }
	    //if ($start > $end) return createDateRangeArray($end, $start);
	    do {
	        $range[] = date('H:i:s', $start);
	        $start = strtotime("+ 1 hour", $start);
	    } while($start <= $end);
	    return $range;
	}
	function get_rangohoras_am_pm($start, $end, $incluye = FALSE ) { // TRUE: hora final incluida; FALSE: no incluye la hora final
		// var_dump($end);
		if( empty($start) || empty($end) ){
			return FALSE;
		}
	    $range = array();
	    if (is_string($start) === true) {
	    	$start = strtotime($start);
	    }
	    if (is_string($end) === true ) {
	    	if($incluye){
	    		$end = strtotime($end);
	    	}else{
	    		$end = strtotime("- 1 hour", strtotime($end));
	    	}
	    }
	    //if ($start > $end) return createDateRangeArray($end, $start);
	    do {
	        $range[] = date('g:i a', $start);
	        $start = strtotime("+ 1 hour", $start);
	    } while($start <= $end);
	    return $range;
	}
	function get_rangomeses($start, $end, $format = 1) {
	    $range = array();
	    if (is_string($start) === true) $start = strtotime($start);
	    if (is_string($end) === true ) $end = strtotime($end);
	    do {
	    	if($format == 1){
	    		$range[] = darFormatoMesAno(date('Y-m', $start));
	    	}else{
	    		$range[] = date('Y-m', $start);
	    	}
	        $start = strtotime("+ 1 month", $start);
	    } while($start <= $end);
	    return $range;
	}
	function darFormatoDMY($fecha)
	{
		if(empty($fecha)){
			return null;
		}

		$fechaUT = strtotime($fecha); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $d."-".$m."-".$y;
		// var_dump("<pre>",$fecha,$result);
		return $result;
	}
	function darFormatoDMYhora($fecha)
	{
		if(empty($fecha)){
			return null;
		}

		$fechaUT = strtotime($fecha); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $d."-".$m."-".$y." ".$hr.":".$min."".$a;
		// var_dump("<pre>",$fecha,$result);
		return $result;
	}
	function darFormatoYMD($fecha)
	{
		if(empty($fecha)){
			return null;
		}

		$fechaUT = strtotime($fecha); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $y."-".$m."-".$d;
		// var_dump("<pre>",$fecha,$result);
		return $result;
	}
	function darFormatoHora($hora)
	{
		if(empty($hora)){
			return null;
		}
		$horaUT = strtotime($hora); // obtengo una fecha UNIX ( integer )
		$hr	= date('h', $horaUT);
		$min= date('i', $horaUT);
		$a= date('a', $horaUT);
		$result = $hr.":".$min." ".$a;
		return $result; // 06:30 pm
	}
	function darFormatoHora2($hora)
	{
		if(empty($hora)){
			return null;
		}
		$horaUT = strtotime($hora); // obtengo una fecha UNIX ( integer )
		$hr	= date('H', $horaUT);
		$min= date('i', $horaUT);
		$result = $hr.":".$min;
		return $result; // 18:30
	}
	function darFormatoFecha($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $d." de ".$longMonthArray[$m]." de ".$y;
		return $result; // 23 de Abril de 2016
	}
	function darFormatoMesAno($fechaSQL)
	{
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $longMonthArray[$m]."-".$y;
		return $result; // Abril-2016
	}
	function darFormatoMes($fechaSQL)
	{
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$m	= (int)date('m', $fechaUT);
		$result = strtoupper($longMonthArray[$m]);
		return $result; // Junio
	}
	function darFormatoMesAnoPlanilla($fechaSQL)
	{
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = strtoupper($longMonthArray[$m])." ".$y;
		return $result; // Junio 2017
	}
	function darFormatoFechaYHora($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $d." de ".$longMonthArray[$m]." a las ".$hr.":".$min." ".$a;
		return $result; // 01 de Junio a las 12:00 am
	}
	function formatoFechaReporte($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return ""; 
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT); //obtiene los dias en formato 1 - 31
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $d." ".$shortMonthArray[$m]." ".$y." - ".$hr.":".$min." ".$a;
		return $result; // 01 Jun 2016 - 12:00 am
	}
	function formatoFechaReporte2($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT); //obtiene los dias en formato 1 - 31
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $d.$shortMonthArray[$m]." ".$hr.":".$min.$a;
		return $result; // 01Jun 12:00am
	}
	function formatoFechaReporte3($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT); //obtiene los dias en formato 1 - 31
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $d." ".$shortMonthArray[$m]." ".$y;
		return $result; // 01 Jun 2016
	}
	function formatoFechaReporte4($fechaSQL)
	{
		if(empty($fechaSQL) || $fechaSQL == 0){
			return null;
		}
		
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$hr	= date('H', $fechaUT);
		$min= date('i', $fechaUT);

		$result = $d."/".$m."/".$y." ". $hr.":".$min;
		return $result; // 24/08/2016 18:30
	}
	function devolverEdad($fechaNacimiento){ 
		$edad = NULL;
		if( !empty($fechaNacimiento) ){ 
			$startDate = $fechaNacimiento;
			$endDate = date('Y-m-d');
			list($year, $month, $day) = explode('-', $startDate);
			$startDate = mktime(0, 0, 0, $month, $day, $year);
			list($year, $month, $day) = explode('-', $endDate);
			$endDate = mktime(0, 0, 0, $month, $day, $year);
			$edad = (int)(($endDate - $startDate)/(60 * 60 * 24 * 365));
		} 
		return $edad;
	}
	function devolverEdadDetalle($fechaNacimiento)
	{
	    // $localtime = getdate();
	    // $today = $localtime['mday']."-".$localtime['mon']."-".$localtime['year'];

	    $dob_a = explode("-", date('d-m-Y',strtotime("$fechaNacimiento")));

	    //$today_a = explode("-", $today);
	   	$today_a = explode("-", date('d-m-Y'));

	    $dob_d = $dob_a[0];
	    $dob_m = $dob_a[1];
	    $dob_y = $dob_a[2];

	    $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
	    $years = $today_y - $dob_y;

	    $months = $today_m - $dob_m;
	    // var_dump($today_m . ' - ' . $dob_m . ' = ' . $months); exit();
	    //var_dump($today_a); exit();
	    if ($today_m.$today_d < $dob_m.$dob_d)
	    {
	        $years--;
	        $months = 12 + $today_m - $dob_m;
	    }

	    if ($today_d < $dob_d)
	    {
	        $months--;
	    }

	    $firstMonths=array(1,3,5,7,8,10,12);
	    $secondMonths=array(4,6,9,11);
	    $thirdMonths=array(2);

	    if($today_m - $dob_m == 1)
	    {
	        if(in_array($dob_m, $firstMonths))
	        {
	            array_push($firstMonths, 0);
	        }
	        elseif(in_array($dob_m, $secondMonths))
	        {
	            array_push($secondMonths, 0);
	        }elseif(in_array($dob_m, $thirdMonths))
	        {
	            array_push($thirdMonths, 0);
	        }
	    }
	    if($years == 1 ){
	    	$string_año = 'año';
	    }else{
	    	$string_año = 'años';
	    }
	    if($months == 1 ){
	    	$string_mes = 'mes';
	    }else{
	    	$string_mes = 'meses';
	    }
	    return "$years $string_año, $months $string_mes.";
	}
	function darFechaCumple($fechaNacimiento){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		$fechaUT = strtotime($fechaNacimiento); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $d." de ".$longMonthArray[$m];
		return $result; // 04 de Junio
	}
	function formatoSimpleFecha($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$d	= date('d', $fechaUT);
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$result = $d." ".$shortMonthArray[$m];
		return $result; // 01 Jun
	}
	function formatoConDia($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$D	= date('j', $fechaUT); //obtiene los dias en formato 1 - 31
		$d	= date('N', $fechaUT); //obtiene los dias en formato 1 - 7
		$m	= (int)date('m', $fechaUT);
		$day = $shortDayArray[$d];
		$month = $shortMonthArray[$m];
		$result = $day." ".$D." ".$month;
		return $result; // Jue 4 Jun
	}
	function formatoConDiaHora($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$D	= date('j', $fechaUT); //obtiene los dias en formato 1 - 31
		$d	= date('N', $fechaUT); //obtiene los dias en formato 1 - 7
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$day = $shortDayArray[$d];
		$month = $shortMonthArray[$m];
		$hr	= date('h', $fechaUT);
		$min= date('i', $fechaUT);
		$a= date('a', $fechaUT);
		$result = $day." ".$D." ".$month." ".$y." ".$hr.":".$min." ".$a;
		return $result; // Jue 4 Jun 2016 05:00 pm
	}
	function formatoConDiaYano($fechaSQL)
	{
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lun","Mar","Mie","Jue","Vie","Sab","Dom");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$D	= date('j', $fechaUT); //obtiene los dias en formato 1 - 31
		$d	= date('N', $fechaUT); //obtiene los dias en formato 1 - 7
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$day = $shortDayArray[$d];
		$month = $shortMonthArray[$m];
		$result = $day." ".$D." ".$month." ".$y;
		return $result; // Jue 4 Jun 2016
	}
	function formatoConDiaYNombreDia($fechaSQL)
	{
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$shortDayArray = array("","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
		if ($fechaSQL == 0) return "";
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$D	= date('d', $fechaUT); //obtiene los dias en formato 1 - 31
		$d	= date('N', $fechaUT); //obtiene los dias en formato 1 - 7
		$day = $shortDayArray[$d];
		//$month = $shortMonthArray[$m];
		$result = $day." ".$D;
		return $result; // Jueves 04 
	}
	function darFormatoDiaFecha($fechaSQL){
		$longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
		$shortMonthArray = array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
		$longDayArray = array("","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo");
		$fechaUT = strtotime($fechaSQL); // obtengo una fecha UNIX ( integer )
		$D	= date('j', $fechaUT); //obtiene los dias en formato 1 - 31
		$d	= date('N', $fechaUT); //obtiene los dias en formato 1 - 7
		$m	= (int)date('m', $fechaUT);
		$y	= date('Y', $fechaUT);
		$day = $longDayArray[$d];
		$result = $day.", ".$D." de ".$longMonthArray[$m]." de ".$y;
		return $result; // 23 de Abril de 2016
	}
	function devolverEdadAtencion($fechaNacimiento,$fecha_atencion)
	{
	    // $localtime = getdate();
	    // $today = $localtime['mday']."-".$localtime['mon']."-".$localtime['year'];

	    $dob_a = explode("-", date('d-m-Y',strtotime("$fechaNacimiento")));
	    // $today_a = explode("-", $today);
	    $today_a = explode("-", date('d-m-Y',strtotime("$fecha_atencion")));
	    $dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
	    $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
	    $years = $today_y - $dob_y;
	    $months = $today_m - $dob_m;
	    if ($today_m.$today_d < $dob_m.$dob_d)
	    {
	        $years--;
	        $months = 12 + $today_m - $dob_m;
	    }

	    if ($today_d < $dob_d)
	    {
	        $months--;
	    }

	    $firstMonths=array(1,3,5,7,8,10,12);
	    $secondMonths=array(4,6,9,11);
	    $thirdMonths=array(2);

	    if($today_m - $dob_m == 1)
	    {
	        if(in_array($dob_m, $firstMonths))
	        {
	            array_push($firstMonths, 0);
	        }
	        elseif(in_array($dob_m, $secondMonths))
	        {
	            array_push($secondMonths, 0);
	        }elseif(in_array($dob_m, $thirdMonths))
	        {
	            array_push($thirdMonths, 0);
	        }
	    }
	    if($years == 1 ){
	    	$string_año = 'año';
	    }else{
	    	$string_año = 'años';
	    }
	    if($months == 1 ){
	    	$string_mes = 'mes';
	    }else{
	    	$string_mes = 'meses';
	    }
	    return "$years $string_año, $months $string_mes.";
	}
	function IsDate( $date ){ /* SI UNA VARIABLE ES UNA FECHA del tipo dd-mm-YYYY  RETORNA TRUE*/
		/*$fecha = explode('-', $date);
		if( count($fecha) != 3 ){
			return FALSE;
		}
		$dd = $fecha[0];
		$mm = $fecha[1];
		$yy = $fecha[2];
		// $Stamp = strtotime( $date );
		// $Month = date( 'm', $Stamp );
		// $Day   = date( 'd', $Stamp );
		// $Year  = date( 'Y', $Stamp );

		// return checkdate( $Month, $Day, $Year );
		return checkdate( $mm, $dd, $yy );
		*/

		// para probar
		 
	    $pattern="/^(0?[1-9]|[12][0-9]|3[01])[\/|-](0?[1-9]|[1][012])[\/|-]((19|20)?[0-9]{2})$/";

	    if(preg_match($pattern,$date))
	    {
	        $values=preg_split("[\/|-]",$date);
	        if(checkdate($values[1],$values[0],$values[2]))
	            return true;
	    }
	    return false;
	}
	function IsTime( $time ){ /* SI UNA VARIABLE ES UNA HORA del tipo HH:mm:ss  RETORNA TRUE*/
		$pattern="/^([0-1][0-9]|[2][0-3])[\:]([0-5][0-9])[\:]([0-5][0-9])$/";
	    if(preg_match($pattern,$time))
	        return true;
	    return false;
	}
	function diferenciaFechas($fecha_i,$fecha_f){
		$dias = (strtotime($fecha_i)-strtotime($fecha_f))/86400;
		//$dias = abs($dias);
		$dias = floor($dias);
		// if($dias < 1) {
		// 	$dias = 1;
		// }
		return $dias;
	}
	function dentro_de_horario($hms_inicio, $hms_fin, $hms_referencia=NULL){ // v2011-06-21
	    if( is_null($hms_referencia) ){
	        $hms_referencia = date('G:i:s');
	    }

	    list($h, $m, $s) = array_pad(preg_split('/[^\d]+/', $hms_inicio), 3, 0);
	    $s_inicio = 3600*$h + 60*$m + $s;

	    list($h, $m, $s) = array_pad(preg_split('/[^\d]+/', $hms_fin), 3, 0);
	    $s_fin = 3600*$h + 60*$m + $s;

	    list($h, $m, $s) = array_pad(preg_split('/[^\d]+/', $hms_referencia), 3, 0);
	    $s_referencia = 3600*$h + 60*$m + $s;

	    if($s_inicio<=$s_fin){
	        return $s_referencia>=$s_inicio && $s_referencia<=$s_fin;
	    }else{
	        return $s_referencia>=$s_inicio || $s_referencia<=$s_fin;
	    }
	}
	function bisiesto($anio){ 
	    $bisiesto=FALSE; 
	    // verificamos si el mes de febrero del año que queremos consultar tiene 29 días 
	    if (checkdate(2,29,$anio)) 
	        $bisiesto=TRUE; 
	    return $bisiesto; 
	}
	function devolverEdadArray($fechaNacimiento,$fecha_atencion){
		$arrEdad = array();
		// separamos en partes las fechas 
		$array_nacimiento = explode("-", date('d-m-Y',strtotime("$fechaNacimiento")));
	    $array_actual = explode("-", date('d-m-Y',strtotime("$fecha_atencion")));
		$dias =  $array_actual[0] - $array_nacimiento[0]; // calculamos días 
		$meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses 
		$anios =  $array_actual[2] - $array_nacimiento[2]; // calculamos años 
		//ajuste de posible negativo en $días 
		if ($dias < 0) 
		{ 
		    --$meses; 
		    //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual 
		    switch ($array_actual[1]) { 
		           case 1:     $dias_mes_anterior=31; break; 
		           case 2:     $dias_mes_anterior=31; break; 
		           case 3:  
			                if( bisiesto($array_actual[2]) ){
			                   $dias_mes_anterior=29; break; 
			                }
			                else{
		                       $dias_mes_anterior=28; break; 
			                }
		           case 4:     $dias_mes_anterior=31; break; 
		           case 5:     $dias_mes_anterior=30; break; 
		           case 6:     $dias_mes_anterior=31; break; 
		           case 7:     $dias_mes_anterior=30; break; 
		           case 8:     $dias_mes_anterior=31; break; 
		           case 9:     $dias_mes_anterior=31; break; 
		           case 10:    $dias_mes_anterior=30; break; 
		           case 11:    $dias_mes_anterior=31; break; 
		           case 12:    $dias_mes_anterior=30; break; 
		    } 

		    $dias=$dias + $dias_mes_anterior; 
		} 
		//ajuste de posible negativo en $meses 
		if ($meses < 0){ 
		    --$anios; 
		    $meses=$meses + 12; 
		}
		$arrEdad = array(
			'd' => $dias,
			'm' => $meses,
			'y' => $anios
		);

		return $arrEdad; 
	}

	function get_rangomeses_nombre($start, $end, $upper = FALSE, $dos_anios=FALSE){
	    $range = array();
	    $rangeNumber = array();
	    $longMonthArray = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	    if (is_numeric($start) === true) $start = (int)$start;
	    if (is_numeric($end) === true ) $end = (int)$end;
	    $finAnio1 = FALSE;
	    do {	    	
	        if($upper){
	        	array_push($range, strtoupper($longMonthArray[$start]));
	        }else{
	        	array_push($range, $longMonthArray[$start]);
	        }
	        array_push($rangeNumber, $start);
	        if($dos_anios){
	        	if($start == 12){
		        	$start = 1;
		        	$finAnio1 = TRUE;
		        }else{
		        	$start++;
		        }
	        }else{
		        $start++;
	        }
	    } while( (!$dos_anios && $start <= $end) || ($dos_anios && ((!$finAnio1 && $start <= 12) || $start <= $end) ) );
	    return array('meses' => $range, 'meses_num' => $rangeNumber);
	}
?>