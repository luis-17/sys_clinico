<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestión de Almacen</li>
  <li class="active">Aprobación de Bajas</li>
</ol>
<div class="container-fluid" ng-controller="aprobacionBajasController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Aprobación de Bajas </h2> 
              </div>
              <div class="panel-body">
                <div class="tab-content">
                  <div class="tab-pane active" id="home"> 
                    <ul class="form-group demo-btns">
                      <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                      <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-warning" ng-click='btnAprobarSolicitudSalida(mySelectionGrid[0]);'>Aprobar Solicitud</button></li>
                      <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleSalida(mySelectionGrid[0]);'>Ver Detalle</button></li>
                    </ul>
                    <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                      <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>