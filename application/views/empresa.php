<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento</li>
  <li class="active">Empresa</li>
</ol>
<div class="container-fluid" ng-controller="empresaController" ng-init="initEmpresa();">
  <div class="row">
    <!-- VISTA DE PROGRAMACIÓN ASIS -->
    <div class="col-md-12" ng-if="!desdeModulo"> 
      <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
        <div class="panel-heading"> 
          <h2>
            <ul class="nav nav-tabs">
              <li class=" {{estilo_tabs.clasetab1}} " ng-show="estilo_tabs.ver_tab1"><a data-target="#home" href="" data-toggle="tab" ng-click="getPaginationTab1ServerSide()">Gestión de Empresas</a></li>
              <li class=" {{estilo_tabs.clasetab2}} "><a data-target="#tab2" href="" data-toggle="tab" ng-click="getPaginationServerSide()" >Gestión de Empresas EMA</a></li>
            </ul>
          </h2>
          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
        </div>
        <div class="panel-editbox" data-widget-controls=""></div>
        <div class="panel-body">
          <div class="tab-content">
            <div class="tab-pane {{estilo_tabs.clasetab1}}" id="home" ng-show="estilo_tabs.ver_tab1">
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringTab1()' style="position: relative;top: -3px;">Buscar</button></li>
                  <li ><select class="form-control" ng-model="filterTipoEmpresa" ng-change="getPaginationTab1ServerSide();" 
                              ng-options="option as option.tipoEmpresa for option in tipoEmpresa">
                       </select>
                  </li>

                  <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo("",false)'>Nuevo</button></li>                        
                  <li class="pull-right" ng-if="mySelectionGridTab1.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular("",false)'>Anular</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab1.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                </ul>
                <div ui-grid="gridOptionsTab1" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive tableRowDinamic"></div>
              </div>
            </div>

            <div class="tab-pane {{estilo_tabs.clasetab2}}" id="tab2">
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  <li class="pull-right" ng-if="estilo_tabs.ver_tab1" ><button type="button" class="btn btn-success" ng-click='btnNuevo("",true)'>Nuevo</button></li>
                  <!-- <li ><select style="" ng-change="getPaginationServerSide()" class="input" ng-model="empresaAdmin" ng-options="item as item.empresa for item in listaEmpresaAdmin"> </select></li> -->
                  <li ><button type="button" class="btn btn-info" ng-click='btnToggleFiltering()'>Buscar</button></li>
                  
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1 && estilo_tabs.ver_tab1"><button type="button" class="btn btn-warning" ng-click='btnEditar("",true)'>Editar</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1"><button type="button" class="btn btn-danger" ng-click='btnCambiarEstadoEmpresaDet(0)'>Anular</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1"><button type="button" class="btn btn-default" ng-click='btnCambiarEstadoEmpresaDet(2)'>DesHabilitar</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1"><button type="button" class="btn btn-success" ng-click='btnCambiarEstadoEmpresaDet(1)'>Habilitar</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1 "><button type="button" class="btn btn-primary" ng-click='btnConsultarEspecialidad()'> Servicios </button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab2.length == 1 && estilo_tabs.ver_tab1 && mySelectionGridTab2[0].idempresa != empresaAdmin.idempresa"><button type="button" class="btn btn-default" ng-click='btnGestionContrato()'> Gestión Contratos</button></li>
                </ul>
                
                <div ng-if="estilo_tabs.ver_tab1" ui-grid="gridOptions" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive tableRowDinamic"></div>
                
                <div ng-if="!estilo_tabs.ver_tab1" ui-grid="gridOptionsDrMed" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive tableRowDinamic"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- VISTA DE CONTABILIDAD -->
    <div class="col-md-12" ng-if="desdeModulo"> 
      <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
        <div class="panel-heading"> 
          <h2>
            <ul class="nav nav-tabs">
              <li class="active" >
                <a data-target="#home" href="" data-toggle="tab" ng-click="getPaginationTab1ServerSide()">Gestión de Proveedores</a></li>
            </ul>
          </h2>
          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
        </div>
        <div class="panel-editbox" data-widget-controls=""></div>
        <div class="panel-body">
          <div class="tab-content">
            <div class="tab-pane active" id="home" >
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringTab1()' style="position: relative;top: -3px;">Buscar</button></li> 
                  <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo("",false)'>Nuevo</button></li>                        
                  <li class="pull-right" ng-if="mySelectionGridTab1.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular("",false)'>Anular</button></li>
                  <li class="pull-right" ng-if="mySelectionGridTab1.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                </ul>
                <div ui-grid="gridOptionsTab1" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive tableRowDinamic"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>