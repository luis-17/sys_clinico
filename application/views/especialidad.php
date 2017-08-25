<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento</li>
  <li class="active">Especialidad</li>
</ol>
<div class="container-fluid" ng-controller="especialidadController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">                
                <h2>
                  <ul class="nav nav-tabs">
                    <li class="active"><a data-target="#home" href="" data-toggle="tab">Gestión de Especialidades</a></li>
                    <li><a data-target="#tab2" href="" data-toggle="tab">Gestión de Demanda de Especialidades por Sede</a></li>
                  </ul>
                </h2>
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              </div>

              <div class="panel-body">
                <div class="tab-content">
                  <div class="tab-pane active" id="home">
                    <div class="panel-editbox" data-widget-controls=""></div>
                    <div class="panel-body">
                      <ul class="form-group demo-btns">
                          <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                          <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'> Anular</button></li>
                          <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                          <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
                      </ul>
                      <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive"></div>
                    </div>
                  </div>

                  <!-- Gestion de demanda por especialidad y sede -->
                  <div class="tab-pane" id="tab2">
                    <ul class="form-group demo-btns col-xs-12">
                        <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Demanda </label> 
                          <div class="input-group block"> 
                            <select class="form-control input-sm" ng-model="fBusqueda.demanda" ng-change="getPaginationMedServerSide();" 
                              ng-options="item.id as item.demanda for item in listaDemanda" > </select> 
                          </div>
                        </li>                        
                    </ul>
                    <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                      <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringMed();'>Buscar</button></li> 
                    </ul> 
                    <div class="col-xs-12 p-n list-esp-demanda">
                      <div ui-grid="gridOptionsMed" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                    </div>                    
                  </div>
                </div>
              </div>              
            </div>
        </div>
    </div>
</div>