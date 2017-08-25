<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li>
  <li>LOGÍSTICA</li>
  <li class="active">ORDEN DE COMPRA</li>
</ol>
<div class="container-fluid" ng-controller="ordenCompraController"> 
  <div class="row">
      <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Ordenes de Compra </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body">
              <!-- -->
              <form class="row" name="formOrdenCompra" novalidate>
                <div class="col-xs-12">
                  <ul class="form-group demo-btns col-xs-12">  
                    <li class="m-n"> <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="2" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora" />
                        <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div>
                    </li>
                    <li class="m-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="4" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora"/>
                        <input tabindex="6" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div>
                    </li>
                    <li class="mb-n" style="margin-top: -30px;"> 
                      <input type="button" class="btn btn-info" value="PROCESAR" ng-click="procesar();" tabindex="7" ng-disabled="formOrdenCompra.$invalid" style="margin-top: 32px;" /> 
                      <button ng-show="" type="button" class="btn btn-default" ng-click="importarExcel();" tabindex="7" ng-disabled="formOrdenCompra.$invalid" style="margin-top: 32px;">
                        <i class="fa fa-file-excel-o text-success"></i> 
                      </button> 
                    </li>
                  </ul>
                  <ul class="form-group demo-btns col-xs-12">
                    <li class="mr mt-sm col-sm-3 p-n" > <label> Almacén </label>
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="procesar()" 
                          ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                      </div>
                    </li>
                  </ul> 
                </div>
                   
              </form>
              <div class="row">  
                <div class="col-xs-12">
                  <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                    <div class="panel-heading">
                      <h2>
                        <ul class="nav nav-tabs">
                          <li class="active"> <a data-target="#home" href="" data-toggle="tab" > Ordenes de Compra </a> </li>
                          <li><a data-target="#tab2" href="" data-toggle="tab" > Productos de las Ordenes <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-star"></i> </label> </a></li>
                          <li><a data-target="#tab3" href="" data-toggle="tab" >Ordenes Anuladas 
                              <label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label> </a> 
                          </li>
                        </ul>
                      </h2>
                    </div>
                    <div class="panel-body pt-n">
                      <div class="tab-content">
                        <div class="tab-pane active" id="home">
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                            <li class="pull-right" ng-if="mySelectionGridOC.length == 1 && (fSessionCI.key_group === 'key_logistica' || fSessionCI.key_group === 'key_sistemas')"><button type="button" class="btn btn-danger" ng-click='btnAnularEntrada();'> <i class="fa fa-times-circle"> </i> Anular Orden </button>
                            </li>
                            <li class="pull-right">
                              <button type="button" class="btn btn-success" ng-click="btnNuevaOC();" ng-if="(fSessionCI.key_group === 'key_logistica' || fSessionCI.key_group === 'key_sistemas')" >
                                <i class="fa fa-file-text"> </i> NUEVO 
                              </button>
                            </li>
                            <li class="pull-right">
                              <button type="button" class="btn btn-warning ml-sm" ng-click="btnEditarOC();" ng-if="mySelectionGridOC.length == 1 && mySelectionGridOC[0].estado_orden != 2 && (fSessionCI.key_group === 'key_logistica' || fSessionCI.key_group === 'key_sistemas')" >
                                <i class="fa fa-edit"></i> EDITAR 
                              </button>
                            </li>
                            <li class="pull-right">
                              <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGridOC[0].idmovimiento, mySelectionGridOC[0].estado_movimiento);" ng-if="mySelectionGridOC.length == 1" >
                              <i class="fa fa-print"></i> [F4] IMPRIMIR</button>
                            </li>
                            <li class="pull-right" ng-if="mySelectionGridOC.length == 1">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary-alt dropdown-toggle" data-toggle="dropdown" style="padding-bottom: 3px;">
                                      <span class="fa-stack fa-xs">
                                        <i class="fa fa-square-o fa-stack-2x"> </i>
                                        <i class="fa fa-plus fa-stack-1x"> </i>
                                      </span>
                                        Mas <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li ><a href="" ng-click='btnVerDetalleOC(mySelectionGridOC[0]);'><i class="fa fa-bars"></i> Ver Detalle</a></li>
                                        <li><a href="" ng-click='btnVerIngresosOC(mySelectionGridOC[0]);'><i class="fa fa-mail-forward"></i> Ver Ingresos</a></li>
                                        <li class="divider"></li>
                                        <li><a href="" ng-click='btnClonarOC();'><i class="fa fa-magic"></i> Clonación OC</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- <li class="pull-right" ng-if="mySelectionGridOC.length == 1" > 
                              <button type="button" class="btn btn-info" ng-click='btnVerDetalleOC(mySelectionGridOC[0]);'>Ver Detalle</button>
                            </li>
                            <li class="pull-right" ng-if="mySelectionGridOC.length == 1" > 
                              <button type="button" class="btn btn-primary" ng-click='btnVerIngresosOC(mySelectionGridOC[0]);'>Ver Ingresos</button>
                            </li> -->

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
                        <div class="tab-pane" id="tab2">
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPC();'>Buscar</button></li> 
                          </ul> 
                          <div class="col-xs-12 p-n">
                            <div ui-grid="gridOptionsProductosCompra" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                              <div class="waterMarkEmptyData" ng-show="!gridOptionsProductosCompra.data.length"> No se encontraron datos. </div>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane" id="tab3">
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringOCA();'>Buscar</button></li> 
                            <li class="pull-right" ng-if="mySelectionGridOCA.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleOC(mySelectionGridOCA[0]);'>Ver Detalle</button></li>

                          </ul> 
                          <div class="col-xs-12 p-n">
                            <div ui-grid="gridOptionsOCAnulados" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                              <div class="waterMarkEmptyData" ng-show="!gridOptionsOCAnulados.data.length"> No se encontraron datos. </div>
                            </div>
                          </div>
                          <div class="col-xs-12">
                              <div class="text-right">
                                <h2> ANULADOS <strong style="font-weight: 400;" class="text-danger"> : {{ gridOptionsOCAnulados.sumTotal }} </strong> </h2>
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