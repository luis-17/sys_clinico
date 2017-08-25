<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Capital Humano</li>
  <li class="active">Planillas</li>
</ol>
<div class="container-fluid" ng-controller="planillaMasterController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Planilla</h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-sm-2 p-n" >
                    <label class="control-label mb-xs"> EMPRESA: </label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.empresa" ng-change="getPaginationServerSide();" 
                        ng-options="item.descripcion for item in listaEmpresaAdmin" > </select> 
                    </div>
                  </li>
                  <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li> 
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                  <li class="pull-right" ><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn" ng-click='btnConfigPlanilla()'>Configurar</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>