<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li>
  <li>LOGÍSTICA</li>
  <li class="active">SEGUIMIENTO DE LA ORDEN</li>
</ol>
<div class="container-fluid" ng-controller="seguimientoOrdenController"> 
  <div class="row">
      <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Seguimiento de Órdenes </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body">
              <!-- -->
              <form name="formOrdenCompra" novalidate>
                <ul class="form-group demo-btns col-xs-12">  
                  <li class="form-group"> <label> Desde </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                      <input tabindex="2" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora" />
                      <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                    </div>
                  </li>
                  <li class="form-group"> <label> Hasta </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="4" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                      <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora"/>
                      <input tabindex="6" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                    </div> 
                  </li>
                  <li class="form-group" style="margin-top: -30px;"> 
                    <input type="button" class="btn btn-info" value="PROCESAR" ng-click="procesar();" tabindex="7" ng-disabled="formOrdenCompra.$invalid" style="margin-top: 32px;" /> 
                  </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-sm-3 p-n" > <label> Almacén </label>
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="procesar()" 
                        ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                    </div>
                  </li>
                </ul>    
              </form>
              <div class="row">  
                <div class="col-xs-12">
                  <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                    <div class="panel-heading">
                      <h2>
                        <ul class="nav nav-tabs">
                          <li class="active"> <a data-target="#ordenes" href="" data-toggle="tab" > Ordenes de Compra </a> </li>
                        </ul>
                      </h2>
                    </div>
                    <div class="panel-body pt-n">
                      <div class="tab-content">
                        <div class="tab-pane active" id="ordenes">
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                            <li class="pull-right" ng-if="mySelectionGridOC.length == 1"> 
                              <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    CAMBIAR ESTADO A <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li ng-if="permisoAprobado"><a ng-click="cambiarEstadoOC('a');" href="">APROBADO <i class="fa fa-check-square-o text-success va-top" style="margin-left: 18px;"></i> </a></li>
                                    <li ng-if="permisoObservado"><a ng-click="cambiarEstadoOC('o');" href="">OBSERVADO <i class="fa fa-exclamation-triangle text-warning va-top" style="margin-left: 10px;"></i> </a></li>
                                    <li ng-if="permisoRechazado"><a ng-click="cambiarEstadoOC('r');" href="">RECHAZADO <i class="fa fa-ban text-danger va-top" style="margin-left: 12px;"></i> </a></li> 
                                    <!-- <li class="divider"></li>
                                    <li ng-if="permisoAnulado"><a ng-click="cambiarEstadoOC('an');" href="">ANULADO</a></li> -->
                                </ul>
                              </div>
                            </li>
                            <!-- <li class="pull-right" ng-if="mySelectionGridOC.length == 1 && tienePermiso"><button type="button" class="btn btn-danger" ng-click='btnAnularEntrada();'> <i class="fa fa-times-circle"> </i> Anular Orden </button>
                            </li> -->
                            <li class="pull-right">
                              <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGridOC[0].idmovimiento, mySelectionGridOC[0].estado_movimiento);" ng-if="mySelectionGridOC.length == 1" >
                              <i class="fa fa-print"></i> [F4] IMPRIMIR</button>
                            </li>
                            <li class="pull-right" ng-if="mySelectionGridOC.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleOC(mySelectionGridOC[0]);'>Ver Detalle</button></li>
                            <!-- <li class="pull-right" ng-if="mySelectionGridOC[0].estado_orden == 1 && tienePermisoAprobacion">
                              <button type="button" class="btn btn-warning" ng-click='btnAprobarOC();'> APROBAR </button>
                            </li> -->
                            
                          </ul> 
                          <div class="col-xs-12 p-n">
                            <div ui-grid="gridOptionsOC" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                              <div class="waterMarkEmptyData" ng-show="!gridOptionsOC.data.length"> No se encontraron datos. </div>
                            </div>
                          </div>
                          <div class="col-xs-12">
                              <div class="text-right">
                                <h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : S/. {{ gridOptionsOC.sumTotal }} </strong> </h2>
                              </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
  </div>