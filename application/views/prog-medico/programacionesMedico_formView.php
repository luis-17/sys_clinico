<div class="modal-header">
  <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body"> 
  <div class="row">
    <div class="col-xs-7"> 
      <!-- <div class="row">  -->
        <!-- <div class="help-inline" >                     
            <button type="button" class="btn btn-info" ng-click="btnToggleFiltering();"> BUSCAR</button>      
        </div> -->
        <div class="help-inline" > 
          <div class="input-group" style="width: 220px;"> 
            <select ng-change="getPaginationProgramacionesServerSide()" 
                    class="form-control" ng-model="grid.estado" 
                    ng-options="item as item.estado for item in estadoOptions"> 
            </select> 
          </div>
        </div>
        <div class="help-inline" > 
          <div class="input-group" style="width: 160px;"> 
            <input type="text" placeholder="Desde"  class="form-control datepicker" uib-datepicker-popup="{{dateUIDesde.format}}" popup-placement="auto right-top" ng-model="grid.fecha_desde" 
              is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" close-text="Cerrar" alt-input-formats="altInputFormats" ng-change="getPaginationProgramacionesServerSide()" />
            <span class="input-group-btn">
              <button type="button" class="btn btn-default " ng-click="dateUIDesde.openDP($event)"><i class="ti ti-calendar"></i></button>
            </span>
          </div>           
        </div>
        <div class="help-inline" >
          <div class="input-group" style="width: 160px;"> 
            <input type="text" placeholder="Hasta" class="form-control datepicker" uib-datepicker-popup="{{dateUIHasta.format}}" popup-placement="auto top" ng-model="grid.fecha_hasta" is-open="dateUIHasta.opened" 
              datepicker-options="dateUIHasta.datePikerOptions" close-text="Cerrar" alt-input-formats="altInputFormats" ng-change="getPaginationProgramacionesServerSide()"/>
            <div class="input-group-btn">
              <button type="button" class="btn btn-default" ng-click="dateUIHasta.openDP($event)" tabindex="4"><i class="ti ti-calendar"></i></button>
            </div>
          </div>             
        </div> 
    </div>
    <div class="col-xs-5">
        <button type="button" class="btn btn-success pull-right ml-xs" ng-click="btnReprogramar();" ng-if="mySelectionGrid.length == 1 && grid.estado.id == 2"> REPROGRAMAR</button>
        <button type="button" class="btn btn-warning pull-right ml-xs" ng-click="btnVerComentario();" ng-if="mySelectionGrid.length == 1 && grid.estado.id != 1"> VER MOTIVO</button>
        <button type="button" class="btn btn-info pull-right ml-xs" ng-click="verListaPacientes();" ng-if="mySelectionGrid.length == 1 && grid.estado.id != 0" > VER LISTA PACIENTES</button>
    </div> 
  </div> 
  <div class="row">
  	<div class="col-xs-12">
  		<div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
    </div>
  </div> 
</div> 
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancelVerTodas();">SALIR</button>
</div>