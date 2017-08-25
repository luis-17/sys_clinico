<ol class="breadcrumb">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento Lab</li>
  <li class="active">Análisis</li>
</ol>
<div class="container-fluid" ng-controller="analisisController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Análisis </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 && fSessionCI.key_group == 'key_sistemas'" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                     <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitar()'>Deshabilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar()'>Habilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar("lg")'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo("lg")'>Nuevo</button></li>
                    
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].idseccion != 9"><button type="button" class="btn btn-primary" ng-click='btnAgregarPar("lg")'>Estructura</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].idseccion == 9"><button type="button" class="btn btn-primary" ng-click='btnAgregarAnal("lg")'>Estructura</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="height:390px"></div>
                
              </div>
            </div>
        </div>
    </div>
</div>