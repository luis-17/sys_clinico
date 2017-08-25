<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body">  
    <form class="row" name="formCanalProgMedico"> 
      <div class="mb-n col-md-12" >
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">PROFESIONAL: </strong>
          <p class="help-block m-n"> {{fData.medico}} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">SERVICIO: </strong>
          <p class="help-block m-n"> {{ fData.especialidad }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">EMPRESA: </strong>
          <p class="help-block m-n"> {{ fData.empresa }} </p>
        </div>
      </div>

      <div class="mb-n col-md-12" >
        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">FECHA: </strong>
          <p class="help-block m-n"> {{ fDataGestion.fecha_item }} </p>
        </div>

        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">AMBIENTE: </strong>
          <p class="help-block m-n"> {{ fData.ambiente.numero_ambiente }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">TURNO: </strong>
          <p class="help-block m-n"> {{ fDataGestion.turno }} </p>
        </div> 
        <div class="col-md-3 p-n">
          <strong class="control-label mb-n">INTERVALO ATENCIÓN: </strong>
          <p class="help-block m-n"> {{ fDataGestion.intervalo }} </p>
        </div>
      </div>

      <div class="mb-n col-md-12" >
        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">TOTAL CUPOS: </strong>
          <p class="help-block m-n"> {{fDataGestion.total_cupos }} </p>
        </div>

        <div class="col-md-3 p-n">
          <strong class="control-label mb-n">TOTAL CUPOS ADICIONALES: </strong>
          <p class="help-block m-n"> {{ fDataGestion.cupos_adicionales }} </p>
        </div>
      </div> 

      <div class="form-group mb-md col-md-12" ng-hide="total_cupos > 0" > 
        <div class="alert alert-warning m-n p-sm mt" >No ha seleccionado horario para este turno</div>
      </div>

      <div class="form-group mb-md col-md-12" ng-hide="total_cupos == 0">
        <div ui-grid="gridOptionsCanal" ui-grid-edit ui-grid-selection ui-grid-pagination ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
      </div>
    </form>
  </div>
    
  <div class="modal-footer">
      <button class="btn btn-primary" ng-click="aceptarCanal(); ">Aceptar</button>
  </div>
</div>