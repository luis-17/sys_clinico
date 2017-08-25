<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ComprobanteWeb extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper(array('security','fechas_helper','imagen_helper','otros_helper'));
		$this->load->model(array('model_comprobante_web','model_venta_web','model_atencion_medica'));
		//cache 
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0"); 
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		$this->sessionHospital = @$this->session->userdata('sess_vs_'.substr(base_url(),-8,7));
	}

	public function lista_ventas_web(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_comprobante_web->m_cargar_lista_ventas_web($allInputs['paginate'], $allInputs['filtros']);
		//print_r($this->sessionHospital);
		$totalRows = $this->model_comprobante_web->m_count_lista_ventas_web($allInputs['paginate'], $allInputs['filtros']);
		
		$arrListado = array();
		foreach ($lista as $row) {
			$estado = array();
			$mail = array();
			if($row['estado_comprobante'] == 1){
				$estado['string'] = 'EMITIDO';
				$estado['clase'] = 'label-info';
				$estado['boolean'] = $row['estado_comprobante'];
				$estado['nombre_archivo'] =  $row['nombre_archivo'];
			}else if($row['estado_comprobante'] == 2){
				$estado['string'] = 'POR EMITIR';
				$estado['clase'] = 'label-warning';
				$estado['boolean'] = $row['estado_comprobante'];
				$estado['nombre_archivo'] = '';
			}

			$cliente = $row['nombres'] .' '. $row['apellido_paterno'] .' '. $row['apellido_materno'];

			$especialidades = $this->model_comprobante_web->m_cargar_especialidades_venta($row['idventa']);
			$especialidad = '';
			foreach ($especialidades as $key => $esp) {
				$especialidad .= $esp['especialidad'] . ', ';
			}
			$especialidad = substr($especialidad, 0,-2);


			array_push($arrListado, 
				array(
					'idusuariowebpago' => $row['idusuariowebpago'],
					'idusuarioweb' => $row['idusuarioweb'],
					'idculqitracking' => $row['idculqitracking'],
					'codigo_referencia_culqi' => $row['codigo_referencia_culqi'],
					'fecha_pago' => formatoFechaReporte($row['fecha_pago']), //date('d-m-Y ',strtotime($row['fecha_pago'])),
					'descripcion_cargo' => explode('-', $row['descripcion_cargo'])[0] . '- Servicios: ' .$especialidad,
					'numero_comprobante' => $row['numero_comprobante'],
					'fecha_comprobante' => $row['fecha_comprobante'],
					'nombre_archivo' => $row['nombre_archivo'],
					'estado_comprobante' => $estado,
					'idsedeempresaadmin' => $row['idsedeempresaadmin'],
					'idtipodocumento' => $row['idtipodocumento'],
					'orden_venta' => $row['orden_venta'],
					'idventa' => $row['idventa'],
					'idmediopago' => $row['idmediopago'],
					'total_a_pagar' => $row['total_a_pagar'],
					'total_igv' => $row['total_igv'],
					'sub_total' => $row['sub_total'],
					'ticket_venta' => $row['ticket_venta'],
					'idcliente' => $row['idcliente'],
					'cliente' => $cliente,					
					'num_documento' => $row['num_documento'],
					'telefono' => $row['telefono'],
					'email' => $row['email'],
					'celular' => $row['celular'],
					//'especialidad' => $row['especialidad'],
					//'idespecialidad' => $row['idespecialidad'],
				)
			);
		}

    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($listaCbo)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function ver_popup_formulario(){
		$this->load->view('comprobante-web/popupSubirComprobanteWeb_formView');
	}

	public function subir_comprobante_web(){
		$allInputs['idusuariowebpago'] = $this->input->post('idpago');
		$allInputs['nro_comprobante'] = $this->input->post('nro_comprobante');
		$allInputs['idventa'] = $this->input->post('idventa');
		$arrData['message'] = 'Error al subir los archivos.';
    	$arrData['flag'] = 0;

    	$duplicado = $this->model_comprobante_web->m_es_comprobante_duplicado($allInputs['nro_comprobante']);
    	if($duplicado){
    		$arrData['message'] = 'N° de Comprobante duplicado. Verifique los datos e intente nuevamente.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if(empty($_FILES) ){
    		$arrData['message'] = 'No se ha cargado ningun archivo. Cargue el archivo por favor.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}

    	if($_FILES['archivo']['type'] != 'application/pdf' ){
    		$arrData['message'] = 'Debe cargar un archivo de tipo PDF.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}    	

    	if($_FILES['archivo']['size'] > 1048576 ){
    		$arrData['message'] = 'El tamaño del archivo no puede exceder 1MB.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
    	}  	

    	$configFTP = getConfig('ftp');

    	$cid = ftp_connect($configFTP['FTP_HOST'],$configFTP['FTP_PORT']);
    	$resultado = ftp_login($cid, $configFTP['FTP_USER'],$configFTP['FTP_PASS']);

    	if ((!$cid) || (!$resultado)) {
			$arrData['message'] = 'Fallo de conexión FTP.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		} 

		$allInputs['nuevoNombreArchivo'] = 'Comprobante_'.date('YmdHis').'.pdf';
		$allInputs['estado'] = 1;
		$ruta = "/citasenlinea/comprobantesWeb/";

		ftp_pasv($cid, true);
		if(@ftp_chdir($cid,$ruta)){
			if(@ftp_put($cid,$allInputs['nuevoNombreArchivo'],$_FILES["archivo"]["tmp_name"],FTP_BINARY)){
				$this->db->trans_start();
				if($this->model_comprobante_web->m_actualizar_comprobante_web($allInputs)){ 
					$this->model_venta_web->m_actualizar_comprobante_web_en_venta($allInputs);
					$listaDetalle = $this->model_venta_web->m_cargar_detalle_venta($allInputs['idventa']);						
					foreach ($listaDetalle as $key => $detalle) {
						$atencion = $this->model_atencion_medica->m_verifica_tiene_atencion($detalle);
						if(!empty($atencion['idatencionmedica'])){
							$data['nro_comprobante'] = $allInputs['nro_comprobante'];
							$data['idatencionmedica'] = $atencion['idatencionmedica'];
							$this->model_atencion_medica->m_actualizar_comprobante_web_en_atencion($data);
						}
					}

					$arrData['message'] = 'Se cargó el archivo correctamente.';
					$arrData['flag'] = 1;
				}
				$this->db->trans_complete();
			}			
		}else{
			$arrData['message'] = 'No existe el directorio especificado.';
    		$arrData['flag'] = 0;
		}

		ftp_close($cid);

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function borrar_comprobante_web(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);

		$configFTP = getConfig('ftp');

    	$cid = ftp_connect($configFTP['FTP_HOST'],$configFTP['FTP_PORT']);
    	$resultado = ftp_login($cid, $configFTP['FTP_USER'],$configFTP['FTP_PASS']);

    	if ((!$cid) || (!$resultado)) {
			$arrData['message'] = 'Fallo de conexión FTP.';
    		$arrData['flag'] = 0;
    		$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
    		return;
		} 
		$ruta = "/citasenlinea/comprobantesWeb/";

		ftp_pasv($cid, true);
		if(@ftp_chdir($cid,$ruta)){
			if(ftp_delete($cid, $ruta . $allInputs['nombre_archivo'])) {
				$allInputs['nuevoNombreArchivo'] = '';
				$allInputs['nro_comprobante'] = '';
				$allInputs['estado'] = 2;
				if($this->model_comprobante_web->m_actualizar_comprobante_web($allInputs)){ 
					$arrData['message'] = 'Se borró el archivo correctamente.';
					$arrData['flag'] = 1;
				}
			}			
		}else{
			$arrData['message'] = 'No existe el directorio especificado.';
    		$arrData['flag'] = 0;
		}
		ftp_close($cid);

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function enviar_mail_comprobante(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al enviar email.';
    	$arrData['flag'] = 0;

		$this->load->library('My_PHPMailer');
		date_default_timezone_set('UTC');
		define('SMTP_HOST','mail.villasalud.pe');
		$correo = 'sistemas.ti@villasalud.pe';
		$pass = 'franzsheskoli';
		$setFromAleas = 'Villa Salud';

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

		$archivo = 'https://citasenlinea.villasalud.pe/comprobantesWeb/' . $allInputs['nombre_archivo'];
		$mail->addStringAttachment(file_get_contents($archivo), 'ComprobantePago.pdf');

		$mail->Subject = 'Comprobante de Pago - Hospital Villa Salud';

		$cuerpo = '<!DOCTYPE html>
					<html lang="es">
					<head>
					    <meta charset="utf-8">
					    <meta name="author" content="Villa Salud">								    
					</head>';
        $cuerpo .= '<body style="font-family: sans-serif;padding: 10px 40px;" > 
	                  <div style="text-align: center;">
	                    <img style="max-width: 800px;" alt="Hospital Villa Salud" src="https://citasenlinea.villasalud.pe/assets/img/dinamic/empresa/header-mail.jpg">
	                  </div>';
	    $cuerpo .= '  <div style="max-width: 780px;align-content: center;margin-left: auto; margin-right: auto;padding-left: 5%; padding-right: 5%;">';
        $cuerpo .= '  <div style="font-size:16px;">  
                        Estimado(a) usuario: '. ucwords(strtolower($allInputs['cliente'])) .',';
        $cuerpo .= '    Nos complace imformarte que tu comprobante de pago ha sido generado exitosamente.';
        $cuerpo .=    '</div>';
        $cuerpo .= '<div style="text-align: center;margin: 25px 0 25px 0;">
							<a href="'. $archivo  .'" style="width: 200px;
                                                        padding: 5px 10px;
                                                        margin-left: auto;
                                                        margin-right: auto;
                                                        color: #616161;
                                                        border-radius: 5px;
                                                        font-weight: bold;
                                                        text-decoration: none;
                                                        background: #6dd1de;">
		                        VER COMPROBANTE <i class="fa fa-angle-right"></i>
		                    </a>
						</div>';
        $cuerpo .=    '</div>';
  	
	    $cuerpo .= '<div style="text-align: center;">
	    				<img style="max-width: 800px;" alt="Hospital Villa Salud" src="https://citasenlinea.villasalud.pe/assets/img/dinamic/empresa/footer-mail.jpg">
	    			</div>';
      	$cuerpo .= '</body>';
        $cuerpo .= '</html>';

		$mail->AltBody = $cuerpo;
		$mail->MsgHTML($cuerpo);		 
		$mail->CharSet = 'UTF-8';

		if($allInputs['email'] != null && $allInputs['email'] != ''){
			if(comprobar_email($allInputs['email'])){
				$mail->AddAddress($allInputs['email']);
				if($mail->Send()){
					$arrData['message'] = 'Notificación de correo enviada exitosamente.';
					$arrData['flag'] = 1;
				}
			}else{
			  	$arrData['message'] = 'Notificación de correo NO enviada. Correo de Cliente inválido.';
				$arrData['flag'] = 0;
			}
		}else{
			$arrData['message'] = 'Notificación de correo NO enviada. Correo de Cliente no registrado.';
			$arrData['flag'] = 0;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
?>