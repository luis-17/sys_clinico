<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li>
  <li>CAJA FARMACIA</li>
  <li class="active"> Liquidación - Caja Actual </li>
</ol>
<div class="container-fluid" ng-controller="liquidacionFarmController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Liquidación de Caja Actual </h2>
              </div>
              <div class="panel-body">
                <div ng-show="cajaAbiertaPorMiSession || !(fSessionCI.key_group == 'key_caja_far')">
                  <ul class="row demo-btns">
                      <li class="form-group mr mt-sm col-sm-3 col-md-2 p-n" > <label> Empresas / Sedes </label> 
                        <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="onChangeEmpresaSede();" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </li> 
                      <li class="form-group mr mt-sm col-sm-3 col-md-2 p-n" > <label> Cajas Abiertas </label> 
                        <select class="form-control input-sm" ng-model="fBusqueda.cajamaster" ng-change="getPaginationServerSide(); getPaginationVAServerSide(); getPaginationEEServerSide(); getPaginationPVServerSide(); getPaginationRIServerSide();" ng-options="item.id as item.descripcion for item in listaCajaMaster" > </select> 
                      </li> 
                      <li class="form-group mt-sm col-sm-6 p-n pull-right text-right mr-n" > 
                        <button ng-if="cajaAbiertaPorMiSession && ventaNormal" type="button" class="btn btn-primary-alt btn-lg" ng-click="goToUrl('/nueva-venta-farm');" > <i class="ti ti-file"></i> NUEVA VENTA </button>
                        <button ng-if="cajaAbiertaPorMiSession && !ventaNormal" type="button" class="btn btn-primary-alt btn-lg" ng-click="goToUrl('/nuevo-pedido-farm');" > <i class="ti ti-file"></i> NUEVO PEDIDO </button>
                        <button ng-if="cajaAbiertaPorMiSession" type="button" class="btn btn-inverse btn-lg" ng-click="btnCerrarCaja();" > <i class="ti ti-close"></i> CERRAR CAJA </button> 
                        <button ng-if="!cajaAbiertaPorMiSession" type="button" class="btn btn-success btn-lg" ng-click="abrirCaja();" > <i class="ti ti-plus"></i> ABRIR CAJA </button>
                      </li>
                  </ul>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                        <div class="panel-heading">
                          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                          <h2>
                            <ul class="nav nav-tabs">
                              <li class="active" ng-show="!ventaNormal">
                                <a data-target="#home" href="" data-toggle="tab">Pedidos Por Aprobar</a> 
                              </li>
                              <li ng-class="{'active': ventaNormal}"><a data-target="#tab1" href="" data-toggle="tab" ng-click="getPaginationServerSide(true);">Ventas 
                                <label class="label label-danger" style="margin: 7px;opacity: 0.5;"> </label> </a> 
                              </li>
                              <li><a data-target="#tab2" href="" data-toggle="tab" ng-click="getPaginationVAServerSide(true);">Ventas Anuladas 
                                <label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label> </a> 
                              </li>
                              <li><a data-target="#tab3" href="" data-toggle="tab" ng-click="getPaginationEEServerSide(true);"> Ventas con Dscto. </a></li>
                              <li><a data-target="#tab4" href="" data-toggle="tab" ng-click="getPaginationPVServerSide(true);"> Productos por Venta <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-star"></i> </label> </a></li>
                              <li><a data-target="#tab5" href="" data-toggle="tab" ng-click="getPaginationRIServerSide(true);"> Solicitudes de Impresión </a></li>
                            </ul>
                          </h2>
                        </div>
                        <div class="panel-body">
                          <div class="tab-content">
                            <div class="tab-pane active" id="home" ng-if="!ventaNormal"> 
                              <div class="" ng-show="gridOptionsVentasPedidos.data[0].empresa_admin">
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasPedidos.data[0].empresa_admin }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasPedidos.data[0].sede }} </strong> </h4> 
                                </div>
                              </div>
                              <ul class="form-group demo-btns col-xs-12">
                                <li><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                                <li class="pull-right"><button class="btn btn-warning" type="button" ng-click="getPaginationPedServerSide();" tooltip-placement="bottom" tooltip="Actualizar" title="" > <i class="ti ti-reload "></i> </button></li> 
                                
                                <li class="pull-right" ng-if="mySelectionGridPed.length == 1 && cajaAbiertaPorMiSession" && ><button type="button" class="btn btn-danger" ng-click='btnAnularPedido();'>Anular</button></li>
                                <li class="pull-right" ng-if="mySelectionGridPed.length == 1" ><button type="button" class="btn btn-info" ng-click='btnProcesarVenta(mySelectionGridPed[0]);'>Procesar Venta</button></li>
                              </ul> 
                              
                              <div ui-grid="gridOptionsVentasPedidos" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsVentasPedidos.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="tab-pane" ng-class="{'active': ventaNormal}" id="tab1"> 
                              <div class="" ng-show="gridOptions.data[0].empresa_admin">
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptions.data[0].empresa_admin }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptions.data[0].sede }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> CAJA <strong style="font-weight: 100;" class="text-info"> : N° {{ gridOptions.data[0].numero_caja }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> USUARIO <strong style="font-weight: 100;" class="text-info"> : {{ gridOptions.data[0].username }} </strong> </h4> 
                                </div>
                              </div>
                              <ul class="form-group demo-btns col-xs-12">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                                <li class="pull-right" ng-if="mySelectionGridV.length > 0" > 
                                  <button type="button" class="btn btn-warning" ng-click='btnSolicitudImprimirTicket();'> <i class="fa fa-share"> </i> Solicitar Re-impresión </button> 
                                </li>
                                <li class="pull-right" ng-if="mySelectionGridV.length == 1 && 
                                  (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_dir_far' || fSessionCI.key_group == 'key_admin_far' || fSessionCI.key_group == 'key_caja_far') && 
                                  mySelectionGridV[0].estado_movimiento == '1' " ><button type="button" class="btn btn-danger" ng-click='btnAnular();'>Anular</button></li>
                                <li class="pull-right" ng-if="mySelectionGridV.length > 0" ><button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionGridV[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button></li>
                                <li class="pull-right" ng-if="mySelectionGridV.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridV[0]);'>Ver Detalle</button></li>
                                <li class="pull-right" ng-if="mySelectionGridV[0].idmediopago == 6" ><button type="button" class="btn btn-default" ng-click='pagoMixto();'>Ver División Pagos</button></li>
                              </ul> 
                              
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                              </div>
                              <div class="col-xs-12">
                                  <div class="text-right">
                                    <h2> CAJA ACTUAL <strong style="font-weight: 400;" class="text-success"> : {{ gridOptions.sumTotal }} </strong> </h2>
                                  </div>
                              </div>
                            </div>

                            <div class="tab-pane" id="tab2">
                              <div class="" ng-show="gridOptionsVentasAnuladas.data[0].empresa_admin">
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasAnuladas.data[0].empresa_admin }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasAnuladas.data[0].sede }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> CAJA <strong style="font-weight: 100;" class="text-info"> : N° {{ gridOptionsVentasAnuladas.data[0].numero_caja }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> USUARIO <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasAnuladas.data[0].username }} </strong> </h4> 
                                </div>
                              </div>
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringVA()'>Buscar</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGrid[0]);'>Ver Detalle</button></li>
                              </ul>
                              <div ui-grid="gridOptionsVentasAnuladas" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsVentasAnuladas.data.length"> No se encontraron datos. </div>
                              </div>
                              <div class="col-xs-12">
                                  <div class="text-right">
                                    <h2> ANULADOS <strong style="font-weight: 400;" class="text-danger"> : {{ gridOptionsVentasAnuladas.sumTotal }} </strong> </h2>
                                  </div>
                              </div>
                            </div>

                            <div class="tab-pane" id="tab3"> 
                              <div class="" ng-show="gridOptionsVentasEnEspera.data[0].empresa_admin">
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasEnEspera.data[0].empresa_admin }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasEnEspera.data[0].sede }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> CAJA <strong style="font-weight: 100;" class="text-info"> : N° {{ gridOptionsVentasEnEspera.data[0].numero_caja }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> USUARIO <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasEnEspera.data[0].username }} </strong> </h4> 
                                </div>
                              </div>
                              
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringEE()'>Buscar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length > 0 && fSessionCI.key_group == 'key_sistemas'" ><button type="button" class="btn btn-warning" ng-click='btnAprobarVenta();'>Aprobar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridEE[0]);'>Ver Detalle</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length > 0" ><button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionGridEE[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button></li>
                              </ul>
                              <div ui-grid="gridOptionsVentasEnEspera" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsVentasEnEspera.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>

                            <div class="tab-pane" id="tab4"> 
                              <div class="" ng-show="gridOptionsProductosVenta.data[0].empresa_admin">
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsProductosVenta.data[0].empresa_admin }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsProductosVenta.data[0].sede }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> CAJA <strong style="font-weight: 100;" class="text-info"> : N° {{ gridOptionsProductosVenta.data[0].numero_caja }} </strong> </h4> 
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                  <h4 class="m-xs"> USUARIO <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsProductosVenta.data[0].username }} </strong> </h4> 
                                </div>
                              </div>
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPV()'>Buscar</button></li>
                              </ul>
                              <div ui-grid="gridOptionsProductosVenta" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsProductosVenta.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="tab-pane" id="tab5"> 
                              <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasImpresion.data[0].empresa_admin }} </strong> </h4> 
                              </div>
                              <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                <h4 class="m-xs"> SEDE <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasImpresion.data[0].sede }} </strong> </h4> 
                              </div>
                              <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                <h4 class="m-xs"> CAJA <strong style="font-weight: 100;" class="text-info"> : N° {{ gridOptionsVentasImpresion.data[0].numero_caja }} </strong> </h4> 
                              </div>
                              <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                                <h4 class="m-xs"> USUARIO <strong style="font-weight: 100;" class="text-info"> : {{ gridOptionsVentasImpresion.data[0].username }} </strong> </h4> 
                              </div>
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringRI()'>Buscar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridRI.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridRI[0]);'>Ver Detalle</button></li>
                                <li class="pull-right" ng-if="mySelectionGridRI.length > 0" ><button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionGridRI[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button></li>
                              </ul>
                              <div ui-grid="gridOptionsVentasImpresion" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsVentasImpresion.data.length"> No se encontraron datos. </div> 
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div ng-show="!cajaAbiertaPorMiSession && (fSessionCI.key_group == 'key_caja_far')" class="text-center"> <!--  -->
                  <div style="position: relative; top: inherit;" class="waterMarkEmptyData"> Proceda a abrir caja para comenzar...  </div> 
                  <button ng-click="abrirCaja();" class="btn btn-success btn-lg ng-scope" type="button" ng-if="!cajaAbiertaPorMiSession" style=""> <i class="ti ti-plus"></i> ABRIR CAJA </button>
                </div>
              </div>
            </div>
        </div>
        <!-- <div class="col-md-12">
            
        </div> -->
    </div>
</div>