<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AtencionOdontologica extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('security','imagen_helper','otros_helper'));
		$this->load->model(array('model_cliente'));
		//cache
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		$this->output->set_header("Pragma: no-cache");
		date_default_timezone_set("America/Lima");
		//if(!@$this->user) redirect ('inicio/login');
		//$permisos = cargar_permisos_del_usuario($this->user->idusuario);
	}
	public function ver_odontograma(){
		$this->load->view('atencionOdontologica/odontograma_formView');
	}
	public function ver_odontograma_procedimiento(){
		$this->load->view('atencionOdontologica/odontograma_proc_formView');
	}
}