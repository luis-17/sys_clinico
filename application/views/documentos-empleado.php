<ol class="breadcrumb m-n">
  <li><a href="#/">RR.HH</a></li>
  <li class="active">Documentos del Empleado</li>
</ol>
<div class="container-fluid" ng-controller="documentoEmpleadoController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger m-n" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>SELECCIONE EMPLEADO PARA VER SUS DOCUMENTOS</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-success" ng-click='btnVerFicha();'>Ver Ficha del Empleado</button></li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li> -->
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
            </div>
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>DOCUMENTOS DEL EMPLEADO</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-success" ng-click='btnNuevo();'>SUBIR ARCHIVO</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGridDE.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>ELIMINAR ARCHIVO</button></li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li> -->
                </ul>
                <div ui-grid="gridOptionsDocumento" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                  <div class="waterMarkEmptyData" ng-show="!gridOptionsDocumento.data.length"> {{ gridOptionsDocumento.message }} </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>