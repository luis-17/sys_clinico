<?php
class Model_historial_citas extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_citas_historial($paramPaginate,$paramDatos=FALSE) 
	{
		/* CONSULTAS */ 
		$this->db->select("CONCAT_WS(' ',c.nombres,c.apellido_paterno,c.apellido_materno) AS cliente", FALSE); 
		$this->db->select("CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medico",FALSE); 
		$this->db->select('pci.idprogcita, pci.fecha_reg_cita, pci.fecha_reg_reserva, pci.fecha_atencion_cita, pci.estado_cita, pci.idempresacliente, pci.idproductomaster',FALSE);
		$this->db->select('dpm.hora_inicio_det, dpm.hora_fin_det, dpm.intervalo_hora_det, dpm.si_adicional, dpm.numero_cupo, dpm.estado_cupo, dpm.iddetalleprogmedico',FALSE);
		$this->db->select('ca.idcanal, ca.descripcion_can',FALSE);
		$this->db->select('pme.idprogmedico, pme.tipo_atencion_medica, pme.intervalo_hora,  pme.idespecialidad,pme.tipo_atencion_medica',FALSE);
		$this->db->select('amb.numero_ambiente, amb.piso, amb.idambiente',FALSE);
		$this->db->select('v.idventa, v.estado, v.orden_venta, v.paciente_atendido_v ,v.fecha_venta, v.ticket_venta',FALSE);
		$this->db->select('td.idtipodocumento, td.descripcion_td',FALSE);
		$this->db->select('mp.idmediopago, mp.descripcion_med',FALSE);
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, c.fecha_nacimiento, c.email',FALSE);
		$this->db->select('sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, emp.descripcion AS empresa',FALSE);
		$this->db->select('med.idmedico, med.med_nombres, med.med_apellido_paterno, med_apellido_materno, med_numero_documento',FALSE);
		$this->db->select('pm.idproductomaster, pm.descripcion, de.total_detalle, esp.nombre AS especialidad, de.iddetalle, de.paciente_atendido_det',FALSE); 
		
		$this->db->from('pa_prog_cita pci');
		$this->db->join('pa_detalle_prog_medico dpm','pci.iddetalleprogmedico = dpm.iddetalleprogmedico');
		$this->db->join('pa_canal ca','dpm.idcanal = ca.idcanal'); 
		$this->db->join('pa_prog_medico pme','dpm.idprogmedico = pme.idprogmedico'); 
		$this->db->join('medico med','pme.idmedico = med.idmedico'); 
		$this->db->join('pa_ambiente amb','pme.idambiente = amb.idambiente'); 
		$this->db->join('producto_master pm','pci.idproductomaster = pm.idproductomaster');
		$this->db->join('sede_empresa_admin sea','pci.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('cliente c','pci.idcliente = c.idcliente AND estado_cli = 1'); 

		$this->db->join('detalle de','pci.idprogcita = de.idprogcita','left'); 
		$this->db->join('venta v','de.idventa = v.idventa AND v.estado <> 2','left'); 
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago','left'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento','left'); 
		$this->db->join('especialidad esp','pme.idespecialidad = esp.idespecialidad','left');
		$this->db->join('empresa_medico emmed','pme.idempresamedico = emmed.idempresamedico','left');
		$this->db->join('empresa_especialidad emesp','emesp.idempresaespecialidad = emmed.idempresaespecialidad','left');
		$this->db->join('pa_empresa_detalle emdet','emesp.idempresadetalle = emdet.idempresadetalle','left');
		$this->db->join('empresa emp','emp.idempresa = emdet.idempresatercera','left');		

		$this->db->where('ea.estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('pme.estado_prm', array(1,2)); // 1 registrado, 2 cancelado 
		$this->db->where_in('pci.estado_cita', array(1,2,3,4,5)); // 1 reservado, 2 confirmado, 3 cancelado, 4 reprogramado, 5 atendido 
		$this->db->where('pci.fecha_atencion_cita BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('pme.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['tipoAtencion']) && $paramDatos['tipoAtencion']['id'] !== 'ALL' ){ 
			$this->db->where('pme.tipo_atencion_medica', $paramDatos['tipoAtencion']['id']);
		} 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
	
		$sqlConsultas = $this->db->get_compiled_select();
		$this->db->reset_query();


		/* PROCEDIMIENTOS */ 
		$this->db->select("CONCAT_WS(' ',c.nombres,c.apellido_paterno,c.apellido_materno) AS cliente", FALSE); 
		$this->db->select("CONCAT_WS(' ',med.med_nombres,med.med_apellido_paterno,med.med_apellido_materno) AS medico",FALSE); 
		$this->db->select('NULL AS idprogcita, v.fecha_venta AS fecha_reg_cita,  NULL AS fecha_reg_reserva, de.fecha_atencion_det AS fecha_atencion_cita, NULL AS estado_cita, v.idempresacliente, pm.idproductomaster',FALSE);
		$this->db->select('pme.hora_inicio AS hora_inicio_det, pme.hora_fin AS hora_fin_det, NULL AS intervalo_hora_det, NULL AS si_adicional, NULL AS numero_cupo, NULL AS estado_cupo, NULL AS iddetalleprogmedico',FALSE);
		$this->db->select('NULL AS idcanal, NULL AS descripcion_can',FALSE);
		$this->db->select('pme.idprogmedico, pme.tipo_atencion_medica, pme.intervalo_hora, pme.idespecialidad,pme.tipo_atencion_medica',FALSE);
		$this->db->select('amb.numero_ambiente, amb.piso, amb.idambiente',FALSE);
		$this->db->select('v.idventa, v.estado, v.orden_venta, v.paciente_atendido_v ,v.fecha_venta, v.ticket_venta',FALSE);
		$this->db->select('td.idtipodocumento, td.descripcion_td',FALSE);
		$this->db->select('mp.idmediopago, mp.descripcion_med',FALSE);
		$this->db->select('c.idcliente, c.nombres, c.apellido_paterno, c.apellido_materno, c.num_documento, c.fecha_nacimiento, c.email',FALSE);
		$this->db->select('sea.idsedeempresaadmin, s.idsede, s.descripcion AS sede, ea.idempresaadmin, ea.razon_social AS empresa_admin, emp.descripcion AS empresa',FALSE);
		$this->db->select('med.idmedico, med.med_nombres, med.med_apellido_paterno, med_apellido_materno, med_numero_documento',FALSE);
		$this->db->select('pm.idproductomaster, pm.descripcion, de.total_detalle, esp.nombre AS especialidad, de.iddetalle, de.paciente_atendido_det',FALSE); 
		
		$this->db->from('detalle de');
		$this->db->join('venta v','v.idventa = de.idventa');
		$this->db->join('pa_prog_medico pme','de.idprogmedico_prog = pme.idprogmedico'); 
		$this->db->join('medico med','pme.idmedico = med.idmedico'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1');
		$this->db->join('pa_ambiente amb','pme.idambiente = amb.idambiente'); 
		$this->db->join('producto_master pm','de.idproductomaster = pm.idproductomaster');
		$this->db->join('sede_empresa_admin sea','pme.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');   
		
		$this->db->join('medio_pago mp','v.idmediopago = mp.idmediopago','left'); 
		$this->db->join('tipo_documento td','v.idtipodocumento = td.idtipodocumento','left'); 
		$this->db->join('especialidad esp','pme.idespecialidad = esp.idespecialidad','left');
		$this->db->join('empresa_medico emmed','pme.idempresamedico = emmed.idempresamedico','left');
		$this->db->join('empresa_especialidad emesp','emesp.idempresaespecialidad = emmed.idempresaespecialidad','left');
		$this->db->join('pa_empresa_detalle emdet','emesp.idempresadetalle = emdet.idempresadetalle','left');
		$this->db->join('empresa emp','emp.idempresa = emdet.idempresatercera','left');		

		$this->db->where('ea.estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('pme.estado_prm', array(1,2)); // 1 registrado, 2 cancelado  
		$this->db->where('v.fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('pme.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['tipoAtencion']) && $paramDatos['tipoAtencion']['id'] !== 'ALL' ){ 
			$this->db->where('pme.tipo_atencion_medica', $paramDatos['tipoAtencion']['id']);
		} 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 

		$sqlProcedimientos = $this->db->get_compiled_select();
		$sqlMaster = $sqlConsultas.' UNION ALL '.$sqlProcedimientos;
		$sqlMaster.= ' ORDER BY fecha_atencion_cita ASC';

		if( $paramPaginate['sortName'] ){
			//$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
			$sqlMaster.= ' ,'.$paramPaginate['sortName'].' '.$paramPaginate['sort'];
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			//$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
			$sqlMaster.= ' LIMIT '.$paramPaginate['pageSize'].' OFFSET '.$paramPaginate['firstRow'];
		}

		$this->db->reset_query(); // var_dump($sqlMaster); exit(); 
		$query = $this->db->query($sqlMaster);
		return $query->result_array();

	}
	public function m_count_sum_citas_historial($paramPaginate,$paramDatos=FALSE)
	{
		/* CONSULTAS */
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('pa_prog_cita pci');
		$this->db->join('pa_detalle_prog_medico dpm','pci.iddetalleprogmedico = dpm.iddetalleprogmedico');
		$this->db->join('pa_canal ca','dpm.idcanal = ca.idcanal'); 
		$this->db->join('pa_prog_medico pme','dpm.idprogmedico = pme.idprogmedico'); 
		$this->db->join('medico med','pme.idmedico = med.idmedico'); 
		$this->db->join('pa_ambiente amb','pme.idambiente = amb.idambiente'); 
		$this->db->join('producto_master pm','pci.idproductomaster = pm.idproductomaster');
		$this->db->join('sede_empresa_admin sea','pci.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin'); 
		$this->db->join('cliente c','pci.idcliente = c.idcliente AND estado_cli = 1'); 
		$this->db->join('detalle de','pci.idprogcita = de.idprogcita','left'); 
		$this->db->join('venta v','de.idventa = v.idventa AND v.estado <> 2','left');
		$this->db->where('ea.estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('pme.estado_prm', array(1,2)); // 1 registrado, 2 cancelado 		
		$this->db->where_in('pci.estado_cita', array(1,2,3,4,5)); // 1 reservado, 2 confirmado, 3 cancelado, 4 reprogramado, 5 atendido 

		$this->db->where('pci.fecha_reg_cita BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('pme.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['tipoAtencion']) && $paramDatos['tipoAtencion']['id'] !== 'ALL' ){ 
			$this->db->where('pme.tipo_atencion_medica', $paramDatos['tipoAtencion']['id']);
		} 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		} 
		$countConsultas = $this->db->get()->row_array();
		$this->db->reset_query();

		/* PROCEDIMIENTOS */
		$this->db->select('COUNT(*) AS contador',FALSE);
		$this->db->from('detalle de');
		$this->db->join('venta v','v.idventa = de.idventa AND v.estado <> 0');
		$this->db->join('pa_prog_medico pme','de.idprogmedico_prog = pme.idprogmedico'); 
		$this->db->join('medico med','pme.idmedico = med.idmedico'); 
		$this->db->join('cliente c','v.idcliente = c.idcliente AND estado_cli = 1');
		$this->db->join('pa_ambiente amb','pme.idambiente = amb.idambiente'); 
		$this->db->join('producto_master pm','de.idproductomaster = pm.idproductomaster');
		$this->db->join('sede_empresa_admin sea','pme.idsedeempresaadmin = sea.idsedeempresaadmin'); 
		$this->db->join('sede s','sea.idsede = s.idsede'); 
		$this->db->join('empresa_admin ea','sea.idempresaadmin = ea.idempresaadmin');   	
		$this->db->where('ea.estado_emp <>', 0); // empresa_admin 
		$this->db->where('s.estado_se', 1); // sede 
		$this->db->where('sea.estado_sea <>', 0); // sede_empresa_admin 
		$this->db->where_in('pme.estado_prm', array(1,2)); // 1 registrado, 2 cancelado  
		$this->db->where('v.fecha_venta BETWEEN '. $this->db->escape($paramDatos['desde'].' '.$paramDatos['desdeHora'].':'.$paramDatos['desdeMinuto']) .' AND ' . $this->db->escape($paramDatos['hasta'].' '.$paramDatos['hastaHora'].':'.$paramDatos['hastaMinuto'])); 
		if(!empty($paramDatos['especialidad']) && $paramDatos['especialidad']['id'] !== 'ALL' ){ 
			$this->db->where('pme.idespecialidad', $paramDatos['especialidad']['id']);
		}
		if(!empty($paramDatos['tipoAtencion']) && $paramDatos['tipoAtencion']['id'] !== 'ALL' ){ 
			$this->db->where('pme.tipo_atencion_medica', $paramDatos['tipoAtencion']['id']);
		} 
		$this->db->where('sea.idsedeempresaadmin', $this->sessionHospital['idsedeempresaadmin']); 
		if( $paramPaginate['search'] ){ 
			foreach ($paramPaginate['searchColumn'] as $key => $value) { 
				if( !empty($value) ){ 
					$this->db->ilike('CAST('.$key.' AS TEXT )', $value); 
				}
			}
		}

		$countProcedimientos = $this->db->get()->row_array();
		$total['contador'] = $countConsultas['contador'] + $countProcedimientos['contador'];
		return $total; 
	}
}
?>