<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>RR.HH</li>
  <li class="active">Horario Especial</li>
</ol>
<div class="container-fluid" ng-controller="horarioEspecialController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div> 
                <h2>Gestión de Horarios Especiales </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body"> 
                <div class="row">
                  <div class="col-lg-6 col-md-12 col-xs-12">
                    <ul class="form-group demo-btns">
                        <li ><select ng-change="getPaginationServerSideEM();" class="form-control" ng-model="fBusqueda.tercero" ng-options="item.id as item.descripcion for item in listaTercero" > </select></li>
                        <li class="pull-right" >
                          <button type="button" class="btn btn-success" ng-click="btnVerFichaEmpleado();" ng-if="mySelectionGridEM.length == 1"> 
                            <i class="fa fa-file"></i> VER FICHA 
                          </button>
                          <button type="button" class="btn btn-warning" ng-click='getPaginationServerSideEM();$event.preventDefault();'> 
                          <i class="fa fa-refresh"></i> ACTUALIZAR </button>
                        </li>
                    </ul>
                    <div class="row">
                      <div class="col-xs-12"> 
                        <div ui-grid="gridOptionsEmpleado" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 col-md-12 col-xs-12"> 
                    <ul class="form-group demo-btns">
                        <li class="text-right block" style="height: 32px;"> 
                          <button type="button" class="btn btn-success" ng-click="btnNuevo();" ng-if="mySelectionGridEM.length == 1"> 
                            <i class="fa fa-calendar-o"></i> HORARIO ESPECIAL 
                          </button>
                          <button type="button" class="btn btn-default" ng-click="btnActualizarMarcado();" ng-if="mySelectionGridEM.length == 1"> 
                            <i class="fa fa-file"></i> ACTUALIZAR MARCACIONES 
                          </button>
                          <button type="button" class="btn btn-info" ng-click="btnVerFechaEspecial();" ng-if="mySelectionGridEM.length == 1 && mySelectionGridHE.length == 1"> 
                            <i class="fa fa-file"></i> VER FECHA ESPECIAL
                          </button>
                        </li>
                    </ul>
                    <div ui-grid="gridOptionsHorario" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                      <div class="waterMarkEmptyData" ng-show="!gridOptionsHorario.data.length"> No se encontraron datos. </div>
                    </div> 
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>