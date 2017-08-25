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
 
      </div>
      <div class="col-md-12 pt-md">
        <div ui-grid="gridOptionsPac" ui-grid-auto-resize class="grid table-responsive"></div>
	    </div>
    </form> 
  </div>
  <div class="modal-footer">
      <button class="btn btn-info" ng-if="modulo == 'progMedico'" ng-click="exportExcelPacientes();">EXPORTAR EXCEL</button>
      <button class="btn btn-warning" ng-click="cancelVerPacientes();">SALIR</button>
  </div>