<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>GESTION DE ALMACÉN</li>
  <li class="active">SALIDA DE PRODUCTOS</li>
</ol>
<div class="container-fluid" ng-controller="salidasFarmController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Salida de Productos</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <!-- -->
                <form name="formSalida" novalidate>
                  <ul class="form-group demo-btns col-xs-12">  
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                          <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora"/>
                          <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                          <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora"/>
                          <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
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
                        <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="listarSubAlmacenesAlmacen(fBusqueda.almacen.id);procesar('true');" 
                          ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Sub-Almacén </label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.idsubalmacen" ng-change="procesar('true');" 
                          ng-options="item.id as item.descripcion for item in listaSubAlmacen" > </select> 
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
                              <a data-target="#home" href="" data-toggle="tab" >Bajas </a> 
                            </li>
                            <li><a data-target="#tab2" href="" data-toggle="tab" >Bajas Anuladas 
                              <label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label> </a> 
                            </li>
                            <li><a data-target="#tab3" href="" data-toggle="tab" > Productos dados de baja <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-star"></i> </label> </a></li>
                            
                          </ul>
                        </h2>
                      </div>
                      <div class="panel-body pt-n">
                        <div class="tab-content">
                          <div class="tab-pane active" id="home">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 

                              <li class="pull-right col-md-2 col-sm-2 pr-n pl-n" >
                                <div class="btn-group" style="min-width: 100%">
                                    <a href="" class="btn btn-info-alt" tabindex="111" ng-click="btnNuevaSalida(); $event.preventDefault();" style="min-width: 87%;">Nueva Baja</a>
                                    <a href="" class="btn btn-info-alt dropdown-toggle" tabindex="112" data-toggle="dropdown" style="min-width: 10%;"><span class="caret"></span></a>
                                    <ul class="dropdown-menu sm" role="menu" style="padding:0;">
                                        <li><a ng-click="btnOtraSalida(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="114">Otros</a></li>
                                    </ul>
                                </div>
                              </li>

                              <li class="pull-right" ng-if="mySelectionGrid.length == 1 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_admin_far' || fSessionCI.key_group == 'key_dir_far')"><button type="button" class="btn btn-danger" ng-click='btnAnularSalida();'> <i class="fa fa-times-circle"> </i> Anular Baja </button></li>

                              <li class="pull-right">
                                <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGrid[0].idmovimiento,  mySelectionGrid[0].estado_movimiento);" ng-if="mySelectionGrid.length == 1" ><i class="fa fa-print"></i> [F4] IMPRIMIR </button>
                              </li>
                              <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-warning" ng-click='btnVerDetalleBaja(mySelectionGrid[0]);'>Ver Detalle</button></li>
                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                          </div>
                          <div class="tab-pane" id="tab2">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringSA();'>Buscar</button></li> 
                              <li class="pull-right" ng-if="mySelectionGridSA.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleBaja(mySelectionGridSA[0]);'>Ver Detalle</button></li>

                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsSalidasAnuladas" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsSalidasAnuladas.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            
                          </div>
                          <div class="tab-pane" id="tab3">
                            <!-- ============================================================================== --> 
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                              <li class="pull-right col-md-2 col-sm-2 pr-n pl-n" >
                                <div class="btn-group" style="min-width: 100%">
                                    <a href="" class="btn btn-info-alt" tabindex="111" ng-click="btnNuevaSalida(); $event.preventDefault();" style="min-width: 87%;">Nueva Baja</a>
                                    <a href="" class="btn btn-info-alt dropdown-toggle" tabindex="112" data-toggle="dropdown" style="min-width: 10%;"><span class="caret"></span></a>
                                    <ul class="dropdown-menu sm" role="menu" style="padding:0;">
                                        <li><a ng-click="btnOtraSalida(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="114">Otros</a></li>
                                    </ul>
                                </div>
                              </li>
                              

                              <li class="pull-right" ng-if="mySelectionGrid.length > 0 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_admin_far')  &&  mySelectionGrid[0].estado_detalle == 1 "><button type="button" class="btn btn-primary" ng-click='btnVerDetalleSalida(mySelectionGrid[0]);'> <i class="fa fa-exchange"> </i> Ver Detalle</button></li>
                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsProductosBaja" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
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