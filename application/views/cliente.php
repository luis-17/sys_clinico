<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
<!--   <li>RR.HH</li> -->
  <li class="active">PACIENTES</li>
</ol>
<div class="container-fluid" ng-controller="clienteController" ng-init="initCliente();">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestion de Pacientes </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 && fSessionCI.key_group == 'key_sistemas'" ><button type="button" class="btn btn-danger" ng-click='btnAnular()' >Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar("xlg")'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevoCliente("xlg")'>Nuevo</button></li>
                </ul>
                <div  ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid"></div>
              </div>
            </div>
        </div>
    </div>
</div>