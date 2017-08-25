<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Caja</li>
  <li class="active">Resumen de Ventas</li>
</ol>
<div class="container-fluid" ng-controller="liquidacionController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Resumen de Ventas </h2> 
              </div>
              <div class="panel-body">
                <ul class="row demo-btns">
                    <li class="form-group mr mt-sm col-sm-3 p-n" > <label> Empresas / Sedes </label> 
                      <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="onChangeEmpresaSede();" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </li> 
                    <li class="form-group mr mt-sm col-md-2 col-sm-3 p-n" > <label> Desde </label> 
                      <div class="input-group" > 
                        <input type="text" class="form-control input-sm" ng-model="fBusqueda.desde" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="3" style="width: 120px;" />
                        <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" /> 
                        <input tabindex="7" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" /> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-md-2 col-sm-3 p-n" > <label> Hasta </label> 
                      <div class="input-group" > 
                        <input type="text" class="form-control input-sm" ng-model="fBusqueda.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="20" style="width: 120px;" /> 
                        <input tabindex="22" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" /> 
                        <input tabindex="24" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" /> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-4 p-n mb-n" > 
                        <button type="button" class="btn btn-success" ng-click="btnConsultar();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                        <!-- <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir();"><i class="fa fa-print"></i> IMPRIMIR </button>  -->
                    </li>
                    
                </ul>
                <!-- <ul class="row demo-btns">
                  <li class="form-group col-sm-5 p-n" > 
                        <a href="" ng-click="verMasFiltros();">Ver más filtros</a>
                    </li>
                </ul>
                <ul class="row demo-btns" ng-show="contMasFiltros"> 
                  <li class="form-group col-md-1 col-sm-2 col-xs-12 p-n"> 
                    <select multiple class="form-control input-sm" ng-model="fBusqueda.tipodocumento" seleted ng-options="item.id as item.descripcion for item in listaTipoDocumento" > </select> 
                  </li>
                </ul>  -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                      <div class="panel-heading">
                        <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                        <h2>
                          <ul class="nav nav-tabs">
                            <li class="active"><a data-target="#home" href="" data-toggle="tab" ng-click="reloadGrid();">Liquidacion de Cajas</a></li> 
                            <li ><a data-target="#txtd" href="" data-toggle="tab" ng-click="reloadGrid();"> Totalizado por Tipo de Documento</a></li> 
                          </ul>
                        </h2>
                      </div>
                      <div class="panel-body">
                        <div class="tab-content">
                          <div class="tab-pane active" id="home">
                            <ul class="form-group demo-btns col-lg-12">
                                <!-- <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> --> 
                                <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li> -->
                                <li class="pull-right" ng-show="gridOptionsLiquid.data.length">
                                  <button type="button" class="btn btn-default btn-sm" ng-click="exportarLiquidacion('pdf')"> <i class="fa fa-file-pdf-o"></i> EXPORTAR A PDF </button>
                                </li>
                                <li class="pull-right" ng-show="gridOptionsLiquid.data.length">
                                  <button type="button" class="btn btn-default btn-sm" ng-click="exportarLiquidacion('csv');"> <i class="fa fa-file-excel-o"></i> EXPORTAR A EXCEL </button>
                                </li> 
                                <li class="pull-right" ng-if="mySelectionGrid.length == 1">
                                  <button type="button" class="btn btn-info btn-sm" ng-click="verDetalleTotalizadoTipoDocEnPopUp();"> <!-- <i class="fa fa-file-excel-o"></i> --> VER DETALLE TIPO DE DOC. </button>
                                </li> 
                            </ul> 
                            <div ui-grid="gridOptionsLiquid" ui-grid-pagination ui-grid-selection ui-grid-exporter ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                              <div class="waterMarkEmptyData" style="font-size: 24px; top: 60px;" ng-show="!gridOptionsLiquid.data.length"> No se encontraron datos. </div>
                            </div> 
                            <div class="col-md-4 col-xs-12"> </div>
                            <div class="col-md-2 col-xs-12">
                                <div class="text-right">
                                  <h4 class="well well-sm"> CANT. VENTAS <strong style="font-weight: 400; text-decoration: underline;" class="text-success"> : {{ gridOptionsLiquid.sumCantV }} </strong> </h4>
                                </div>
                            </div>
                            
                            <div class="col-md-2 col-xs-12">
                                <div class="text-right">
                                  <h4 class="well well-sm"> CANT. ANULADOS <strong style="font-weight: 400; text-decoration: underline;" class="text-danger"> : {{ gridOptionsLiquid.sumCantA }} </strong> </h4>
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <div class="text-center">
                                  <h4 class="well well-sm"> CANT. N.C.R. <strong style="font-weight: 400; text-decoration: underline;" class="text-success"> : {{ gridOptionsLiquid.sumCantNC }} </strong> </h4>
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <div class="text-right">
                                  <h4 class="well well-sm"> TOTAL VENTAS <strong style="font-weight: 400; text-decoration: underline;" class="text-success"> : {{ gridOptionsLiquid.sumTotalV }} </strong> </h4>
                                </div>
                            </div>
                          </div>
                          <div class="tab-pane" id="txtd">
                            <ul class="form-group demo-btns col-lg-12">
                                <!-- <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> --> 
                                <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li> -->
<!--                                 <li class="pull-right" ng-show="gridOptionsLiquid.data.length">
                                  <button type="button" class="btn btn-default btn-sm" ng-click="exportarLiquidacion('pdf')"> <i class="fa fa-file-pdf-o"></i> EXPORTAR A PDF </button>
                                </li>
                                <li class="pull-right" ng-show="gridOptionsLiquid.data.length">
                                  <button type="button" class="btn btn-default btn-sm" ng-click="exportarLiquidacion('csv');"> <i class="fa fa-file-excel-o"></i> EXPORTAR A EXCEL </button>
                                </li>  -->
                            </ul> 
                            <div ui-grid="gridOptionsTXTD" ui-grid-pagination ui-grid-selection ui-grid-exporter ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                              <div class="waterMarkEmptyData" style="font-size: 24px; top: 60px;" ng-show="!gridOptionsTXTD.data.length"> No se encontraron datos. </div>
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
        <!-- <div class="col-md-12">
            
        </div> -->
    </div>
</div>