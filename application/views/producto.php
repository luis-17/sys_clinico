<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento</li>
  <li class="active">Productos</li>
</ol>
<div class="container-fluid" ng-controller="productoController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestion de Productos </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li class="mb-xs"><button class="btn btn-info mb" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="mb-xs ml" > <label> Empresas / Sedes </label> 
                      <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="onChangeEmpresaSede();" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </li>
                    <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaPdf()' title="Exportar a PDF"><i class="fa fa-file-pdf-o text-danger" ></i> </button></div>
                    <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button></div>
                    <li class="pull-right m-xs"><button type="button" class="btn btn-success" ng-click='btnNuevo("lg")'>Nuevo</button></li>
                    <li class="pull-right m-xs" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <li class="pull-right m-xs" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitar()'>Deshabilitar</button></li>
                    <li class="pull-right m-xs" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar()'>Habilitar</button></li>
                    <li class="pull-right m-xs" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-primary" ng-click='btnHistorialPrecios()'>Historial de Precios</button></li>
                    <li class="pull-right m-xs" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar("lg")'>Editar</button></li>
                    
                </ul>
                <div  ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns ui-grid-auto-resize class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>