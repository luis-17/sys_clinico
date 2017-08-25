  <style type="text/css">
    .popover-content {
        padding: 9px;
    }
  </style>
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>
  <div class="modal-body"> 
    <form  class="row" name="formListaPacientesProgMedico" >
      <div class="mb-n col-md-12" >
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">PROFESIONAL PROGRAMADO: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.medico }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">SERVICIO: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.especialidad }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">EMPRESA: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.empresa }} </p>
        </div>
      </div>

      <div class="mb-n col-md-12" >
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">FECHA: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.fecha_programada }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">AMBIENTE: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.ambiente.numero_ambiente }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">TURNO: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.turno }} </p>
        </div> 
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">INTERVALO DE ATENCIÓN: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.intervalo_hora_int }} Min. </p>
        </div> 
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">CUPOS: </strong>
          <p class="help-block m-n"> {{ fDataListaPaciente.total_cupos_master }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">CUPOS ADICIONALES: </strong>
          <p class="help-block m-n"> +{{ fDataListaPaciente.cupos_adicionales }} </p>
        </div>
      </div>
      <div class="col-md-12">
        <div class="row">
          <div class="col-md-1 pull-right" style="margin-bottom: 10px; padding-right: 17px; padding-left: 0px;">
            <button tooltip-placement="bottom" tooltip="Actualizar Lista de Pacientes" type="button" class="btn btn-sm btn-warning"  
                    style="float: right;" ng-click="getListadoPacientes(); $event.preventDefault();"> 
              <i class="ti ti-reload"></i> ACTUALIZAR 
            </button> 
          </div>
        </div>
        <div ui-grid="gridOptionsPac" ui-grid-pagination ui-grid-auto-resize class="grid table-responsive"></div>
	    </div>
    </form> 
  </div>
  <div class="modal-footer"> 
      <button class="btn btn-info" ng-click="exportExcelPacientes();">EXPORTAR EXCEL</button>
      <button class="btn btn-warning" ng-click="cancelVerPacientes();">SALIR</button>
  </div>