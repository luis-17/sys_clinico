<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento</li>
  <li class="active">Convenios</li>
</ol>
<div class="container-fluid" ng-controller="convenioController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Convenios</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="ml" > <label> Empresas / Sedes </label> 
                      <select class="form-control input-sm" ng-change="getPaginationServerSide()" ng-model="fBusqueda.sedeempresa" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnRegEdit("Reg")'>Nuevo</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnRegEdit("Edit")'>Editar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-primary" ng-click='btnVerClientesConvenio()'>Ver Clientes</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-info" ng-click='btnVerProductosConvenio()'>Ver Productos</button></li>
                    
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>