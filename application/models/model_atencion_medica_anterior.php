<?php
class Model_atencion_medica_anterior extends CI_Model {
	public function __construct()
	{
		parent::__construct();
		
	}
	public function m_cargar_pacientes_old($datos){

		$username = "luis";
		$password = "luis1717";
		$hostname = '192.168.1.30';
		$dbname = "ACGestores";

		//connection to the database
		$dbcon = mssql_connect($hostname, $username, $password) or die("Unable to connect to MSSQL");
		if($dbcon){
			//echo "Connected to MSSQL <br>";
			mssql_select_db($dbname, $dbcon);
			$result=mssql_query("SELECT DISTINCT c.COD_HISCLI as idcliente, c.APE_PATPAC as apellido_paterno, c.APE_MATPAC as apellido_materno, c.NOMB_PAC as nombres, c.LE AS num_documento, CONVERT(varchar(50), c.FECH_NAC,  105) AS fecha_nacimiento, c.SEX_COD AS sexo, c.HISCLI AS idhistoria 
				FROM dbo.CA_INGR01 AS v 
				JOIN H_HISCLI AS c ON v.LAB_HIS = c.COD_HISCLI 
				WHERE c.APE_PATPAC like '" . $datos['ApellidoPaterno'] . "%' AND c.APE_MATPAC like '" . $datos['ApellidoMaterno'] . "%'  AND c.NOMB_PAC like '" . $datos['Nombres'] . "%' 
				ORDER BY COD_HISCLI ASC");
			$results= array();
		    while ($row = mssql_fetch_array($result)) {
		        $results[]= $row;
		    }

			return $results;
		}
	}
	public function m_cargar_pacientes($datos){
		$db_old = $this->load->database('old_vs', TRUE);
		$db_old->select('c.idcliente, c.apellido_paterno, c.apellido_materno, c.nombres, c.idhistoria, fecha_nacimiento, num_documento, sexo');
		$db_old->from('cliente c');
		
		// $db_old->join('historia h', 'c.idcliente = h.idcliente');
		$db_old->join('venta v', 'c.idcliente = v.idcliente');
		// $db_old->where('paciente_atendido_v', 1);

		if(isset($datos['Nombres'])) $db_old->ilike('nombres', $datos['Nombres']);
		if(isset($datos['ApellidoPaterno'])) $db_old->ilike('apellido_paterno', $datos['ApellidoPaterno']);
		if(isset($datos['ApellidoMaterno'])) $db_old->ilike('apellido_materno', $datos['ApellidoMaterno']);
		$db_old->order_by('c.idcliente','ASC');
		$db_old->distinct();
		$db_old->limit(10);
		return $db_old->get()->result_array();
	}
	public function m_cargar_pacientes2($datos){
		$this->db->select('c.idcliente, c.apellido_paterno, c.apellido_materno, c.nombres, h.idhistoria, fecha_nacimiento, num_documento, sexo');
		$this->db->from('cliente c');
		
		$this->db->join('historia h', 'c.idcliente = h.idcliente');
		$this->db->join('venta v', 'c.idcliente = v.idcliente');
		$this->db->where('paciente_atendido_v', 1);
		if(isset($datos['Nombres'])) $this->db->ilike('nombres', $datos['Nombres']);
		if(isset($datos['ApellidoPaterno'])) $this->db->ilike('apellido_paterno', $datos['ApellidoPaterno']);
		if(isset($datos['ApellidoMaterno'])) $this->db->ilike('apellido_materno', $datos['ApellidoMaterno']);
		$this->db->order_by('c.idcliente','ASC');
		$this->db->distinct();
		$this->db->limit(5);
		return $this->db->get()->result_array();
	}
	public function m_cargar_atenciones2($datos){
		//var_dump($datos[0]['idcliente']); exit();
		$this->db->select('v.orden_venta, pm.descripcion as producto, pm.idtipoproducto, d.fecha_atencion_det as fecha_atencion, e.nombre as especialidad, am.idatencionmedica, am.anamnesis, idhistoricoembarazo, presion_arterial_mm, presion_arterial_hg, frec_cardiaca, temperatura_corporal, peso, talla, imc, perimetro_abdominal, examen_clinico, observaciones, atencion_control');
		$this->db->select("(CASE WHEN (he.fecha_ultima_regla IS NULL) THEN 2 ELSE 1 END) AS gestando",FALSE);
		$this->db->from('detalle d');
		$this->db->join('venta v', 'd.idventa = v.idventa');
		$this->db->join('historia h', 'v.idcliente = h.idcliente');
		$this->db->join('atencion_medica am', 'd.iddetalle = am.iddetalle');
		$this->db->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster');
		//$this->db->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto');
		$this->db->join('especialidad e', 'd.idespecialidad = e.idespecialidad');
		$this->db->join('historico_embarazo he','am.idatencionmedica = he.idatencionmedica','left'); 
		//$this->db->where('paciente_atendido_det', 1);
		$this->db->where('v.idcliente', $datos[0]['idcliente']);
		$this->db->order_by('d.fecha_atencion_det','DESC');
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_atenciones($datos){
		$db_old = $this->load->database('old_vs', TRUE);
		//var_dump($datos[0]['idcliente']); exit();
		$db_old->select('v.orden_venta, pm.descripcion_pm as producto, pm.idtipoproducto, tp.descripcion_tp as tipoproducto, d.fecha_atencion, e.descripcion as especialidad, am.idatencionmedica, gestando, am.anamnesis, presion_arterial, frec_cardiaca, frec_respiratoria, temperatura, peso, talla, imc, periabdo as perimetro_abdominal, antropometria as examen_clinico, plantrabajo as observaciones, atencionctrl as atencion_control');
		$db_old->select('ciex, ciex1, ciex2, ciex3');
		$db_old->select('med.idmedico, med_nombres, med_apellido_paterno, med_apellido_materno');
		$db_old->from('detalle d');
		$db_old->join('venta v', 'd.orden_venta = v.orden_venta');
		$db_old->join('medico med', 'd.idmedico = med.idmedico','left');
		$db_old->join('atencion_medica am', 'd.idatencionmedica = am.idatencionmedica','left');
		$db_old->join('producto_master pm', 'd.idproductomaster = pm.idproductomaster','left');
		$db_old->join('tipo_producto tp', 'pm.idtipoproducto = tp.idtipoproducto','left');
		$db_old->join('especialidad e', 'd.idespecialidad = e.idespecialidad');

		//$db_old->where('paciente_atendido_det', 1);
		$db_old->where('v.idcliente', $datos[0]['idcliente']);
		$db_old->order_by('d.fecha_atencion','DESC');
		//$db_old->limit(10);
		return $db_old->get()->result_array();
	}
	function m_cargar_atenciones_old($datos){
  		$username = "luis";
		$password = "luis1717";
		$hostname = '192.168.1.30';
		$dbname = "ACGestores";

		//connection to the database
		$dbcon = mssql_connect($hostname, $username, $password) or die("Unable to connect to MSSQL");
		if($dbcon){
			mssql_select_db($dbname, $dbcon);

	    	//$result=mssql_query("SELECT * FROM dbo.CA_INGRSERV WHERE ANL_NUM = 'S1505110088'");
	    	$result=mssql_query("SELECT TOP 20 v.LAB_NUM AS orden_venta, pm.CAJ_DES AS producto, pm.CAJ_COD AS idtipoproducto, tp.CAJ_DES AS tipoproducto, CONVERT(varchar(50), d.FECFIN_EST,  120)  AS fecha_atencion, e.CEN_DES AS especialidad, am.ActoMedico AS idatencionmedica, gestando, anamnesis, PresionArterial, FrecCardiaca AS frec_cardiaca, FrecResp AS frec_respiratoria, Temperatura AS temperatura_corporal, peso, talla, imc, PeriAbdo AS perimetro_abdominal, Antropometria AS examen_clinico, PlanTrabajo AS observaciones, AtencionCtrl AS atencion_control, ciex, ciex1, ciex2, ciex3, MED_COD, MED_APA, MED_AMA, MED_NOM
	    		FROM dbo.CA_INGRSERV AS d
	    		INNER JOIN CA_INGR01 AS v ON d.ANL_NUM = v.LAB_NUM
	    		LEFT JOIN L_MEDICOS AS med ON d.CAJ_MED = med.MED_COD
	    		LEFT JOIN H_ActoMedico_01 AS am ON d.Actomedico = am.ActoMedico
	    		LEFT JOIN CA_INGR06 AS pm ON d.CAJ_SCO = pm.CAJ_SCO
	    		LEFT JOIN CA_INGR05 AS tp ON pm.CAJ_COD = tp.CAJ_COD
	    		JOIN H_CENPROD AS e ON d.COD_ESP = e.CEN_COD

	    		
	    		WHERE v.LAB_HIS ='".$datos[0]['idcliente']."'");
			$results= array();
		    while ($row = mssql_fetch_array($result)) {
		        $results[]= $row;
		    }
			return $results;	
		}
    }
  	public function m_cargar_diagnosticos_de_atencion_old($datos)
	{	
		$username = "luis";
		$password = "luis1717";
		$hostname = '192.168.0.176';
		$dbname = "ACGestores";

		//connection to the database
		$dbcon = mssql_connect($hostname, $username, $password) or die("Unable to connect to MSSQL");
		mssql_select_db($dbname, $dbcon);
		$arrResult = array();
		foreach ($datos as $dato) {

			if($dato != ''){
				if( substr($dato, -1) === 'x' ){
					$dato = substr ($dato, 0, strlen($dato) - 1);
					$result=mssql_query("SELECT ID AS codigo_cie, DESCRIP AS descripcion_cie
			    		FROM dbo.H_ENFERME
			    		WHERE ID LIKE'".$dato."%'");
					//$row = mssql_fetch_array($result);
					$results= array();
				    while ($row = mssql_fetch_array($result)) {
				        $results[]= $row;
				    }
				    foreach ($results as $value) {
				    	array_push($arrResult, $value);
				    }
					
				}else{
					$result=mssql_query("SELECT ID AS codigo_cie, DESCRIP AS descripcion_cie
			    		FROM dbo.H_ENFERME
			    		WHERE ID ='".$dato."'");
					$row = mssql_fetch_array($result);
					array_push($arrResult, $row);
				}
				
			}
		}
		return $arrResult;
	}
	public function m_cargar_diagnosticos_de_atencion($datos){
		$db_old = $this->load->database('old_vs', TRUE);
		$arrResult = array();
		foreach ($datos as $dato) {

			if($dato != ''){
				if( substr($dato, -1) === 'x' ){
					$dato = substr ($dato, 0, strlen($dato) - 1);
					$db_old->select('codigo codigo_cie, descripcion_cie');
					$db_old->from('diagnostico_cie');
					$db_old->like('codigo', $dato, 'after');

					$results = $db_old->get()->result_array();
					
				    foreach ($results as $value) {
				    	array_push($arrResult, $value);
				    }
					
				}else{
					$db_old->select('codigo codigo_cie, descripcion_cie');
					$db_old->from('diagnostico_cie');
					$db_old->where('codigo', $dato);
					$row = $db_old->get()->row_array();
					array_push($arrResult, $row);
				}
				
			}
		}
		return $arrResult; 
	}
	public function m_cargar_diagnosticos_de_atencion2($datos)
	{
		$this->db->select('am.idatencionmedica, anamnesis, fecha_atencion, am.idareahospitalaria, descripcion_aho, tipo_diagnostico, codigo_cie, descripcion_cie, dc.iddiagnosticocie'); 
		$this->db->from('atencion_medica am'); 
		$this->db->join('area_hospitalaria aho','am.idareahospitalaria = aho.idareahospitalaria'); 
		$this->db->join('atencion_por_diagnostico apd','am.idatencionmedica = apd.idatencionmedica'); 
		$this->db->join('diagnostico_cie dc','apd.iddiagnosticocie = dc.iddiagnosticocie'); 
		$this->db->where('am.idatencionmedica', $datos['idatencionmedica']); 
		$this->db->where('estado_am', 1); 
		return $this->db->get()->result_array(); 
	}
}