<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Caja</li>
  <li class="active">Regularización de Stocks</li>
</ol>
<div class="container-fluid" ng-controller="cajaTemporalFarmController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>
                  <!-- Aprobación de Movimientos -->
                  <ul class="nav nav-tabs">
                    <li class="active"><a data-target="#home" href="" data-toggle="tab">Regularización de Stocks</a></li>
                    <li><a data-target="#tab2" href="" data-toggle="tab">Listado de Productos</a></li>
                  </ul>
                </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <div class="tab-content">
                  <!-- TAB PRINCIPAL: REG. DE STOCKS -->
                  <div class="tab-pane active" id="home">
                    <ul class="form-group demo-btns col-xs-12">  
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                          <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                          <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                          <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                          <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                        </div> 
                      </li>
                      <li class="form-group mr mt-md col-sm-2 p-n"> 
                        <div class="input-group" style=""> 
                          <!--<input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> -->
                          <button type="button" class="btn btn-info" ng-click="getPaginationServerSide();">
                              <i class="ti ti-reload"> </i> PROCESAR
                          </button>
                        </div> 
                      </li>
                    </ul>
                    <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                      <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                      <li class="pull-right" ng-if="mySelectionGrid.length > 0 && (fSessionCI.key_group != 'key_caja_far' || fSessionCI.key_group != 'key_asis_far')"><button type="button" class="btn btn-info" ng-click='btnVerDetalleMovimiento(mySelectionGrid[0]);'> <i class="ti ti-menu"> </i> Mostrar Detalle</button></li>
                      <li class="pull-right" ng-if="mySelectionGrid.length > 0 && (fSessionCI.key_group != 'key_caja_far' || fSessionCI.key_group != 'key_asis_far') &&  mySelectionGrid[0].es_temporal == 1"><button type="button" class="btn btn-success" ng-click='btnAprobarMovimiento();'> <i class="ti ti-check"> </i> Regularizar Movimiento</button></li>
                    </ul> 
                    <div class="col-xs-12 p-n">
                      <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                    </div>
                  </div>
                  <!-- TAB 2: DETALLADO -->
                  <div class="tab-pane" id="tab2">
                    <ul class="form-group demo-btns col-xs-12">  
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                          <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                          <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                        <div class="input-group col-sm-12 col-md-12" > 
                          <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                          <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                          <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                        </div> 
                      </li>
                      <!-- <li class="form-group mr mt-sm col-sm-2 p-n"> 
                        <div class="input-group" style=""> 
                          <button type="button" class="btn btn-info" ng-click="getPaginationServerSideLP();">
                              <i class="ti ti-reload"> </i> PROCESAR
                          </button>
                        </div> 
                      </li> -->
                      <li class="form-group mr mt-md col-sm-2 p-n"> 
                        <div class="input-group col-sm-12 col-md-12" style=""> 
                          <button type="button" class="btn btn-info" ng-click="getPaginationServerSideLP('true');">
                              <i class="ti ti-reload"> </i> PROCESAR
                          </button>
                          <button ng-if="gridOptionsLP.data.length>0" type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button> 
                        </div> 
                      </li>
                    </ul>
                    <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                      <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringLP();'>Buscar</button></li> 
                    </ul> 
                    <div class="col-xs-12 p-n">
                      <div ui-grid="gridOptionsLP" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                    </div>
                  </div>

                </div>

                <!-- -->
                

                
              </div>
            </div>
        </div>
    </div>
</div>