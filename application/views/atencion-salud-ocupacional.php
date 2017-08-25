<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Salud Ocupacional</li>
  <li class="active"> Atención Médica</li>
</ol>
<div class="container-fluid" ng-controller="atencionSaludOcupController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger m-n" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>PERFILES VENDIDOS</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  <li class="" style="margin-right: 16px;"> <label> Empresa/Cliente </label> 
                    <div class="input-group block"> 
                      <select tabindex="105" class="form-control input-sm" ng-model="fBusquedaAT.empresa" ng-change="getPaginationServerSide();" 
                        ng-options="item as item.descripcion for item in listaEmpresas" > </select> 
                    </div>
                  </li>
                  <li class=""> <label> Desde </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusquedaAT.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaAT.desdeHora" style="width: 45px; margin-left: 4px;" />
                      <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaAT.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class=""> <label> Hasta </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusquedaAT.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusquedaAT.hastaHora" style="width: 45px; margin-left: 4px;" />
                      <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusquedaAT.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class=""> 
                    <label> </label> 
                    <div class="input-group" style=""> 
                      <button type="button" class="btn btn-info" ng-click="getPaginationServerSide();"> PROCESAR </button> 
                    </div> 
                  </li>
                </ul>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-success" ng-click='btnVerFicha();'>Ver Ficha del Empleado</button></li> -->
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>-->
                    
                <ul class="demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringAT();'>Buscar</button></li> 
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-success" ng-click='btnSubirInformeGeneral();'> SUBIR INFORME </button></li> 
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
            </div>
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>ATENCIONES DEL PERFIL</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns col-xs-12">
                    <li >
                      <label></label>
                      <div class="input-group" style=""> 
                        <button class="btn btn-info" type="button" ng-click='btnToggleFilteringDE();'>Buscar</button>
                      </div>
                    </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGridDE.length > 0" > 
                      <label></label>
                      <div class="input-group">
                        <button type="button" class="btn btn-danger" ng-click='btnAnular();'> <i class="fa fa-trash"></i> ANULAR ATENCION </button>
                      </div>
                    </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGridDE.length > 0" > 
                      <label></label>
                      <div class="input-group">
                        <button type="button" class="btn btn-success" ng-click='btnImprimirFicha();'> <i class="fa fa-trash"></i> IMPRIMIR FICHA </button>
                      </div>
                    </li>
                    <li class="pull-right"> 
                      <label> </label>
                      <div class="input-group" style=""> 
                        <button type="button" ng-disabled="mySelectionGrid.length != 1 || !(fBusquedaAT.idcliente)" class="btn btn-success" ng-click="btnAgregarAtencionMedica();" ><i class="fa fa-plus"></i> AGREGAR ATENCIÓN MÉDICA </button> 
                        <!-- <button type="button" class="btn btn-default" ng-click="btnNuevoCliente('xlg');" ><i class="fa fa-file"></i> NUEVO TRABAJADOR </button>  -->
                      </div> 
                    </li>
                    <li class="ml-md pull-right"> 
                      <label> Seleccione Trabajador: </label> 
                      <div class="input-group" style="width: 300px;">
                        <span class="input-group-btn ">
                          <input disabled type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fBusquedaAT.idcliente" placeholder="ID" min-length="2" />
                        </span>
                        <input  ng-disabled="mySelectionGrid.length != 1" type="text" class="form-control input-sm" ng-model="fBusquedaAT.cliente" placeholder="Ingrese el texto para autocompletar." ng-change="getClearInputCliente();" 
                          typeahead-loading="loadingLocationsCliente" uib-typeahead="item as item.descripcion for item in getClienteAutocomplete($viewValue)" 
                          typeahead-on-select="getSelectedCliente($item, $model, $label)" typeahead-min-length="2" tabindex="7"/>
                      </div>
                      <i ng-show="loadingLocationsCliente" class="fa fa-refresh"></i>
                      <div ng-show="noResultsCL">
                        <i class="fa fa-remove"></i> No se encontró resultados 
                      </div>
                    </li>
                    
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-success" ng-click='btnNuevo();'> <i class="fa fa-file"></i> NUEVA ATENCION </button></li> -->
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li> -->
                </ul>
                <div ui-grid="gridOptionsAtencion" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                  <div style="font-size: 24px;" class="waterMarkEmptyData" ng-show="!gridOptionsAtencion.data.length"> {{ gridOptionsAtencion.message }} </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>