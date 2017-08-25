<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li class="active">Historial de Caja Chica</li>
</ol>
<div class="container-fluid" ng-controller="historialCajaChicaController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Historial de Caja Chica </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns col-xs-12" >
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                  <li class="pull-right" ng-if="mySelectionGridHC.length == 1 && mySelectionGridHC[0].estado_acc == 2">
                    <button type="button" class="btn btn-danger" ng-click='btnCerrarCaja(mySelectionGridHC[0]);'>
                      CERRAR CAJA
                    </button>
                  </li>
                  <li class="pull-right" ng-if="mySelectionGridHC.length == 1">
                    <button type="button" class="btn btn-info" ng-click='btnVerMovimientos(mySelectionGridHC[0]);'>
                      VER MOVIMIENTOS 
                    </button>
                  </li>
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>               
              </div>
            </div>
        </div>
    </div>
</div>