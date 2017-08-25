<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body"> 
    <form  class="row" name="formGridProgMedico" >
      <div class="mb-n col-md-12" >
        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">PROFESIONAL: </strong>
          <p class="help-block m-n"> {{ fDataGestion.medico }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">SERVICIO: </strong>
          <p class="help-block m-n"> {{ fDataGestion.especialidad }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">EMPRESA: </strong>
          <p class="help-block m-n"> {{ fDataGestion.empresa }} </p>
        </div>
      </div>

      <div class="mb-n col-md-12" >
        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">FECHA: </strong>
          <p class="help-block m-n"> {{ fDataGestion.fecha_programada }} </p>
        </div>

        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">AMBIENTE: </strong>
          <p class="help-block m-n"> {{ fDataGestion.ambiente.numero_ambiente }} </p>
        </div>

        <div class="col-md-4 p-n">
          <strong class="control-label mb-n">TURNO: </strong>
          <p class="help-block m-n"> {{ fDataGestion.turno }} </p>
        </div> 
        <div class="col-md-3 p-n">
          <strong class="control-label mb-n">INTERVALO ATENCIÓN: </strong>
          <p class="help-block m-n"> {{ fDataGestion.intervalo_hora_int }} </p>
        </div>
      </div>

      <div class="mb-n col-md-12" >
        <div class="col-md-2 p-n">
          <strong class="control-label mb-n">TOTAL CUPOS: </strong>
          <p class="help-block m-n"> {{ fDataGestion.total_cupos_master }} </p>
        </div>

        <div class="col-md-3 p-n">
          <strong class="control-label mb-n">TOTAL CUPOS ADICIONALES: </strong>
          <p class="help-block m-n"> {{ fDataGestion.cupos_adicionales }} </p>
        </div>
      </div>    
    	<div class="col-md-12">
  			<div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns ui-grid-edit class="grid table-responsive"></div>
		  </div>
    </form>    
   </div>  

  <div class="modal-footer"> 
      <button class="btn btn-success" ng-click="guardarGestion();">GUARDAR</button>
      <button class="btn btn-warning" ng-click="cancelGestion();">SALIR</button>
  </div>
</div>
