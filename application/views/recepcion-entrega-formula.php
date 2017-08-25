<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Farmacia</li>
  <li class="active">Recepcion de Fórmulas y Preparados</li>
</ol>
<div class="container-fluid" ng-controller="recepcionFormulaController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Recepcion de Fórmulas y Preparados </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form class="row" name="formRecepcion" novalidate>
                  <div class="col-xs-12">
                    <ul class="form-group demo-btns col-xs-12">                    
                        <li class="form-group mr-n col-md-2 col-sm-4 p-n"> <label> Desde </label> 
                          <div class="input-group col-xs-12"> 
                            <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 50%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required />
                            <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;"  ng-pattern="pHora" required/>
                            <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;"  ng-pattern="pMinuto" required/>
                          </div>
                        </li>
                        <li class="form-group mr-n col-md-2 col-sm-4 p-n"> <label> Hasta </label>
                          <div class="input-group col-xs-12"> 
                            <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 50%;" data-inputmask="'alias': 'dd-mm-yyyy'"  ng-pattern="pFecha" required/>
                            <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;"  ng-pattern="pHora" required/>
                            <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" ng-pattern="pMinuto" required />
                          </div> 
                        </li>
                       <!--  <li class="form-group mr col-md-1 col-sm-4 p-n"> <label> ESTADO </label> 
                          <div class="input-group col-xs-12" >
                            <select class="form-control input-sm"  ng-model="fBusqueda.estadoPreparado" ng-options="item as item.descripcion for item in listaEstadoPreparado" tabindex="115" ng-change="getPaginationServerSide();"></select>
                          </div>
                        </li> -->
                        <li class="mr col-md-1 col-sm-4 col-xs-12 p-n" style="margin-top: 17px;" ng-if="tabRecibidas"> 
                          <select class="form-control input-sm" ng-model="fBusqueda.estadoRecibido"
                              ng-change="btnProcesar();" 
                              ng-options="item.descripcion for item in listaEstados">
                            </select>
                        </li>
                        <li class="form-group mr col-md-1 col-sm-4 col-xs-12 p-n" style="margin-top: 12px;" > 
                          <div class="input-group" style=""> 
                            <input type="button" class="btn btn-info" value="PROCESAR" ng-click="btnProcesar();" ng-disabled="formRecepcion.$invalid"/> 
                          </div> 
                        </li>
                        
                    </ul>
                  </div>
                    
                </form>

                <div class="row">
                  <div class="col-md-12">
                    <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                      <div class="panel-heading">
                        <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                        <h2>
                          <ul class="nav nav-tabs">
                            
                            <li class="active"><a data-target="#tab1" href="" data-toggle="tab" ng-click="getPaginationServerSide(true);tabRecibidas = false;">POR RECIBIR 
                              <label class="label label-warning" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-clock-o"></i> </label> </a> 
                            </li>
                            <li><a data-target="#tab2" href="" data-toggle="tab" ng-click="getPaginationFRServerSide(true);tabRecibidas = true;">FORMULAS RECIBIDAS 
                              <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-thumbs-o-up"></i> </label> </a> 
                            </li>

                          </ul>
                          
                        </h2>
                        <div class="pull-right m-xs mt-n" ng-if="gridOptionsFR.data.length > 0 && tabRecibidas">
                          <button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' style="padding: 2px 4px;" title="Exportar a Excel">
                            <i class="fa fa-file-excel-o text-success f-24" ></i>
                          </button>
                        </div>
                        <div class="pull-right" ng-if="mySelectionGrid.length > 0 && ((fSessionCI.key_group == 'key_derma'))" ><button type="button" class="btn btn-info" ng-click="btnRecibirFormula('tecnica');">Recibir Fórmula</button></div>
                        <div class="pull-right" ng-if="mySelectionGrid.length > 0 && ((fSessionCI.key_group == 'key_sistemas') || (fSessionCI.key_group == 'key_caja_far') || (fSessionCI.key_group == 'key_dir_far'))" ><button type="button" class="btn btn-info" ng-click='btnRecibirFormula();'>Recibir Fórmula</button></div>
                        <div class="pull-right" ng-if="mySelectionGridFR.length > 0 && ((fSessionCI.key_group == 'key_sistemas') || (fSessionCI.key_group == 'key_caja_far') || (fSessionCI.key_group == 'key_dir_far'))" >
                        <button type="button" class="btn btn-success" ng-click='btnEntregarPedido();'>Entregar Fórmula</button></div>
                        <div class="pull-right" ng-if="mySelectionGridFR.length > 0 && ((fSessionCI.key_group == 'key_sistemas') || (fSessionCI.key_group == 'key_caja_far') || (fSessionCI.key_group == 'key_dir_far'))" >
                        <button type="button" class="btn btn-info mr-sm" ng-click='btnConfirmarRecibido();'>Confirmar Recepción</button></div>
                        
                      </div>
                      <div class="panel-body">
                        <div class="tab-content">
                          <div class="tab-pane active" id="tab1">
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData"  ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="col-md-9 col-xs-12"></div>
                            <div class="col-md-3 col-xs-12" ng-if="(fSessionCI.key_group == 'key_sistemas') || (fSessionCI.key_group == 'key_gerencia')">
                              <div class="text-center">
                                <h4 class="well well-sm"> TOTAL COSTO <strong style="font-weight: 400; " class="text-success"> : S/. {{ gridOptions.sumTotal }} </strong> </h4>
                              </div>
                            </div>  
                          </div>
                          <div class="tab-pane" id="tab2">
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsFR" ui-grid-pagination ui-grid-selection ui-grid-pinning ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData"  ng-show="!gridOptionsFR.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="col-md-9 col-xs-12"></div>
                            <div class="col-md-3 col-xs-12" ng-if="(fSessionCI.key_group == 'key_sistemas') || (fSessionCI.key_group == 'key_gerencia')">
                              <div class="text-center">
                                <h4 class="well well-sm"> TOTAL COSTO <strong style="font-weight: 400; " class="text-success"> : S/. {{ gridOptionsFR.sumTotal }} </strong> </h4>
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
</div>