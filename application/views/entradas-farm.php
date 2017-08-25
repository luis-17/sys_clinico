<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>GESTION DE ALMACÉN</li>
  <li class="active">ENTRADA DE PRODUCTOS</li>
</ol>
<div class="container-fluid" ng-controller="entradasFarmController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Entrada de productos</h2>
              </div>
              <div class="panel-body" ng-show="false">
                <div class="col-xs-12" style="text-align: center">
                  <button type="button"  class="btn btn-success btn-lg" ng-click='btnNuevaEntrada();fDataEntrada.idtipoentrada = 2' > <i class="ti ti-plus"></i> NUEVA COMPRA TEMPORAL </button>
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> Proceda a realizar la compra temporal ... </div>
                </div>
              </div>

              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body" ng-show="!(fSessionCI.key_group === 'key_caja_far')" >
                <!-- -->
                <form name="formEntrada" novalidate>
                  <ul class="form-group demo-btns col-xs-12">  
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                      <div class="input-group col-sm-12 col-md-12"> 
                        <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="2" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora" />
                        <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                      <div class="input-group col-sm-12 col-md-12"> 
                        <input tabindex="4" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora"/>
                        <input tabindex="6" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div> 
                    </li>
                    <li class="form-group col-sm-2 p-n" style="margin-top: 17px;"> 
                      <div class="input-group" style=""> 
                        <button type="submit" class="btn btn-info" ng-click="procesar('true');" ng-disabled="formSalida.$invalid">
                          <i class="ti ti-reload"> </i> PROCESAR
                        </button> 
                      </div> 
                    </li>
                  </ul>
                  <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Almacén </label>
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="procesar('true')" 
                          ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                      </div>
                    </li>
                  
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Tipo Ingreso</label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.idtipoentrada" ng-change="procesar('true')" 
                          ng-options="item.id as item.descripcion for item in listaTipoEntrada" > </select> 
                      </div>
                    </li>
                  </ul>
                </form>
                <div class="row">  
                  <div class="col-md-12">
                    <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                      <div class="panel-heading">
                        <h2>
                          <ul class="nav nav-tabs">
                            <li class="active">
                              <a data-target="#home" href="" data-toggle="tab" >Ingresos </a> 
                            </li>
                            <li><a data-target="#tab2" href="" data-toggle="tab" >Ingresos Anulados 
                              <label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label> </a> 
                            </li>
                            <li><a data-target="#tab3" href="" data-toggle="tab" > Productos Ingresados <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-star"></i> </label> </a></li>
                            <!-- <li><a data-target="#tab5" href="" data-toggle="tab" ng-click="reloadGrid();"> Solicitudes de Impresión </a></li> -->
                          </ul>
                        </h2>
                      </div>
                      <div class="panel-body pt-n">
                        <div class="tab-content">
                          <div class="tab-pane active" id="home">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                              <li class="pull-right">
                                  <div class="btn-group">
                                      <button type="button" class="btn btn-success-alt dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-file-text"> </i>  Nuevo Ingreso <span class="caret"></span>
                                      </button>
                                      <ul class="dropdown-menu" role="menu">
                                          <li><a href="" ng-click='btnNuevaEntrada();fDataEntrada.idtipoentrada = 2'>COMPRAS</a></li>
                                          <li><a href="" ng-click='btnNuevaEntrada();fDataEntrada.idtipoentrada = 4'>REGALOS</a></li>
                                          <li><a href="" ng-click='btnNuevaEntrada();fDataEntrada.idtipoentrada = 6'>REINGRESOS</a></li> 
                                      </ul>
                                  </div>
                              </li>

                              <li class="pull-right" ng-if="mySelectionGridIngr.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-danger" ng-click='btnAnularEntrada();'> <i class="fa fa-times-circle"> </i> Anular Entrada </button></li>
                              <li class="pull-right">
                                <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGridIngr[0].idmovimiento, mySelectionGridIngr[0].estado_movimiento);" ng-if="mySelectionGridIngr.length == 1" ><i class="fa fa-print"></i> [F4] IMPRIMIR </button>
                              </li>
                              <li class="pull-right" ng-if="mySelectionGridIngr.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleEntrada(mySelectionGridIngr[0]);'>Ver Detalle</button></li>
                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="text-right">
                                  <h2> TOTAL A PAGAR <strong style="font-weight: 400;" class="text-success"> : S/. {{ gridOptions.sumTotal }} </strong> </h2>
                                </div>
                            </div>
                          </div>
                          <div class="tab-pane" id="tab2">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringIA();'>Buscar</button></li> 
                              <li class="pull-right" ng-if="mySelectionGridIA.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleEntrada(mySelectionGridIA[0]);'>Ver Detalle</button></li>

                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsIngresosAnulados" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsIngresosAnulados.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="text-right">
                                  <h2> ANULADOS <strong style="font-weight: 400;" class="text-danger"> : {{ gridOptionsIngresosAnulados.sumTotal }} </strong> </h2>
                                </div>
                            </div>
                          </div>
                          <div class="tab-pane" id="tab3">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPC();'>Buscar</button></li> 
                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsProductosCompra" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsProductosCompra.data.length"> No se encontraron datos. </div>
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