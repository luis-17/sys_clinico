<ol class="breadcrumb">
  <li><a href="#/">Inicio</a></li>
  <li>Almacen Lab</li>
  <li class="active">Consulta Salidas Almacen</li>
  <div class="pull-right text-danger" ng-controller="consultasalidasAlmacenController"><i class="ti ti-alert p-sm"></i><strong>Reactivos Vencidos</strong><span style="cursor: pointer;" class="badge badge-primary m-sm" ng-click="VerRiVencidos()">{{ fDataVencidos }}</span></div>
  <div class="pull-right text-danger" ng-controller="consultasalidasAlmacenController"><i class="ti ti-stats-down p-sm"></i><strong>Reactivos en Stock Minimo</strong><span style="cursor: pointer;" class="badge badge-primary m-sm" ng-click="VerRiStockMinimo()">{{ fDataStockMinimo }}</span></div>
</ol>
<div class="container-fluid" ng-controller="consultasalidasAlmacenController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión Salidas de Almacen</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <!--<li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitar()'>Deshabilitar</button></li>-->
                    <!--<li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar()'>Habilitar</button></li>-->
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='VerDetalleSalida()'>Ver Detalle</button></li>
                    <!--<li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>-->
                </ul>
                <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>