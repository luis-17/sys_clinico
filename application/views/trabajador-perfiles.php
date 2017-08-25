<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Salud Ocupacional</li>
  <li class="active">Trabajador/Perfiles</li>
</ol>
<div class="container-fluid" ng-controller="trabajadorPerfilesController" >
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
        <div class="panel-heading">
          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
          <h2>Gestión de Trabajadores/Perfiles </h2> 
        </div>
        <div class="panel-editbox" data-widget-controls=""></div>
        <div class="panel-body">
          <ul class="form-group demo-btns col-xs-12">
            <li class="form-group mr mt-sm col-md-2 col-xs-6 p-n" > <label> Empresa </label> 
              <div class="input-group block"> 
                <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.empresa" ng-change="getPaginationServerSideTP();" 
                  ng-options="item as item.descripcion for item in listaEmpresas" >  </select> 
              </div>
            </li>
            <li class="form-group mr mt-sm col-md-2 col-xs-6 p-n" > <label> Perfil/Producto </label> 
              <div class="input-group block"> 
                <select tabindex="105" class="form-control input-sm" ng-model="fBusqueda.perfil" ng-change="getPaginationServerSideTP();" 
                  ng-options="item as item.descripcion for item in listaPerfiles" >  </select> 
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-1 col-xs-12 p-n"> 
              <div class="input-group" style="margin-top: 12px;"> 
                <button type="button" class="btn btn-info" ng-click="getPaginationServerSideTP();"><i class="fa fa-refresh"></i> PROCESAR </button> 
              </div>
            </li>
            <li class="form-group mr mt-sm col-md-2 col-xs-12 p-n" > 
              <label> Seleccione Trabajador: </label> 
              <div class="input-group">
                <span class="input-group-btn ">
                  <input disabled type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fBusqueda.idcliente" placeholder="ID" min-length="2" />
                </span>
                <input type="text" class="form-control input-sm" ng-model="fBusqueda.cliente" placeholder="Ingrese el texto para autocompletar." ng-change="getClearInputCliente();" 
                  typeahead-loading="loadingLocationsCliente" uib-typeahead="item as item.descripcion for item in getClienteAutocomplete($viewValue)" 
                  typeahead-on-select="getSelectedCliente($item, $model, $label)" typeahead-min-length="2" tabindex="7"/>
              </div>
              <i ng-show="loadingLocationsCliente" class="fa fa-refresh"></i>
              <div ng-show="noResultsCL">
                <i class="fa fa-remove"></i> No se encontró resultados 
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-4 col-xs-12 p-n"> 
              <div class="input-group" style="margin-top: 12px;"> 
                <button type="button" ng-disabled="fBusqueda.perfil.id=='all' || !(fBusqueda.idcliente)" class="btn btn-success" ng-click="agregarClienteAPerfil();" ><i class="fa fa-plus"></i> AGREGAR TRABAJADOR </button> 
                <button type="button" class="btn btn-default" ng-click="btnNuevoCliente('xlg');" ><i class="fa fa-file"></i> NUEVO TRABAJADOR </button> 
              </div> 
            </li>
          </ul>
          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
              <li class="pull-right" ng-if="mySelectionGridTP.length > 0"> 
                <button type="button" class="btn btn-danger" ng-click='btnAnular();'> <i class="fa fa-remove"></i> Anular</button>
              </li>
              <!-- <li class="pull-right">
                <button type="button" class="btn btn-success" ng-click='btnAdministrarPerfiles();'>Administrar...</button>
              </li> -->
          </ul>
          <div ui-grid="gridOptionsTP" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid">
            <div class="waterMarkEmptyData" ng-show="!gridOptionsTP.data.length"> No se encontraron datos. </div>
          </div> 
        </div>
      </div>
    </div>
  </div>
</div>