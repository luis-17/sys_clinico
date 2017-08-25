<ol class="breadcrumb">
  <li><a href="#/">Farmacia</a></li>
  <li>Mantenimiento</li>
  <li class="active">Principio Activo</li>
</ol>
<div class="container-fluid" ng-controller="principioActivoController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Principio Activo</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <!--<li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>-->
                    <li >
                    <label> BUSCAR: </label>
                    <select tabindex="50" class="form-control input-sm inline-block" ng-model="fBusqueda.componenteFormula" ng-change="getPaginationServerSide();" ng-options="item as item.descripcion for item in listaComponenteFormula"> </select></li>               
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                     <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitar()'>Deshabilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar()'>Habilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>