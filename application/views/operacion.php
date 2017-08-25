<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Contabilidad</li>
  <li class="active">Operaciones</li>
</ol>
<div class="container-fluid" ng-controller="operacionController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestion de Operaciones</h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">                
                <form name="formOperacion">
                  <div class="row">
                    <div class="col-sm-6 col-xs-12 form-inline mb">
                      <div class="form-group mr" style="vertical-align: bottom;">
                        <button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button>
                      </div>
                      <div class="form-group"> 
                        <label class="m-n"> TIPO OPERACIÓN </label> 
                        <div class="input-group block" style="width: 230px;"> 
                          <select class="form-control" ng-model="fBusqueda.tipo" 
                             ng-change="getPaginationServerSide();"
                             ng-options="item.descripcion for item in listaTipo ">
                          </select></div>
                      </div>                    
                    </div>
                    <div class="col-sm-6 col-xs-12 mb" style="position: relative;top: 17px;">
                      <ul class="m-xs demo-btns">
                        <li class="form-group pull-right" style="vertical-align: bottom;">
                          <button type="button" ng-if="mySelectionGrid.length > 0" class="btn btn-danger" ng-click='btnAnular()'>Anular</button>
                          <button type="button" ng-if="mySelectionGrid.length == 1" class="btn btn-warning" ng-click='btnEditar()'>Editar</button>
                          <button type="button" ng-if="mySelectionGrid.length == 1" class="btn btn-primary" ng-click='btnSubOperaciones()'>Sub Operaciones</button>                    
                          <button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button>
                        </li>
                      </ul>
                    </div>                    
                  </div>
                </form>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>