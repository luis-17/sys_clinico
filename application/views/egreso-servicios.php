<ol class="breadcrumb m-n">
  <li><a href="#/">CONTABILIDAD</a></li>
  <li>EGRESOS</li>
  <li class="active">SERVICIOS</li>
</ol>
<div class="container-fluid" ng-controller="egresosServicioController"> 
  <div class="row">
      <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Egresos por Servicio </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body"> 
              <form name="formEgresos" novalidate>
                <div class="row">
                  <div class="col-xs-12 form-inline mb">  
                    <div class="form-group"> <label class="m-n"> DESDE </label> 
                      <div class="input-group block" style="width: 230px;"> 
                        <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="2" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora" />
                        <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div>
                    </div>
                    <div class="form-group"> <label class="m-n"> HASTA </label> 
                      <div class="input-group block" style="width: 230px;"> 
                        <input tabindex="4" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" required ng-pattern="pHora"/>
                        <input tabindex="6" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div> 
                    </div>
                    <div class="form-group"> <label class="m-n"> CONCEPTO </label> 
                      <select class="form-control input-sm block" ng-model="fBusqueda.concepto" ng-change="procesar(); $event.preventDefault();" ng-options="item as item.descripcion for item in metodos.listaConceptos" > </select>
                    </div> 
                    <div class="form-group ml" style="vertical-align: bottom;"> 
                      <input type="button" class="btn btn-info" value="PROCESAR" ng-click="procesar(); $event.preventDefault();" tabindex="7" ng-disabled="formEgresos.$invalid" /> 
                    </div>
                    <div class="form-group" ng-show="fBusqueda.concepto.id == 1" style="vertical-align: bottom;"> 
                      <input type="button" class="btn btn-info" value="REPORTE E.M.A" ng-click="verPopupReporteTerceros(); $event.preventDefault();" tabindex="7" ng-disabled="formEgresos.$invalid" /> 
                    </div>
                  </div>
                </div>
              </form>
              <div class="row">  
                <div class="col-xs-12">
                  <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                    <div class="panel-heading">
                      <h2>
                        <ul class="nav nav-tabs">
                          <li class="active"> <a data-target="#tab1" href="" data-toggle="tab" > Listado de Egresos </a> </li>
                          <li><a data-target="#tab2" href="" data-toggle="tab" > Detalle de Egresos 
                            <label class="label label-info" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-star"></i> </label> </a></li>
                        </ul>
                      </h2>
                    </div>
                    <div class="panel-body pt-n">
                      <div class="tab-content">
                        <div class="tab-pane active" id="tab1"> 
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li >
                              <button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button> 
                            </li> 
                            <li class="pull-right" ng-if="mySelectionGridES.length == 1"> 
                                <button type="button" class="btn btn-danger" ng-click='btnAnularEgreso();'> <i class="fa fa-times-circle"> </i> Anular Egreso </button>
                            </li>
                            <li class="pull-right">
                              <button type="button" class="btn btn-success" ng-click="btnNuevoES();">
                                <i class="fa fa-file-text"> </i>  Nuevo Egreso 
                              </button>
                            </li>
                            <li class="pull-right" ng-if="mySelectionGridES.length == 1">
                              <button type="button" class="btn btn-primary" ng-click="btnSeguimientoEstados();"> 
                                <i class="fa fa-eye"> </i> Seguimiento 
                              </button> 
                            </li>
                            <li class="pull-right" ng-if="mySelectionGridES.length == 1" >
                              <button type="button" class="btn btn-info" ng-click="btnVerDetalleES();">Ver Detalle</button>
                            </li>
                          </ul> 
                          <div class="col-xs-12 p-n">
                            <div ui-grid="gridOptionsES" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                              <div class="waterMarkEmptyData" ng-show="!gridOptionsES.data.length"> No se encontraron datos. </div>
                            </div>
                          </div>
                          <div class="col-xs-12">
                              <div class="text-right">
                                <h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : S/. {{ gridOptionsES.sumTotal }} </strong> </h2>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane" id="tab2">
                          <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                            <li>
                              <button class="btn btn-info" type="button" ng-click='btnToggleFilteringDES();'>Buscar</button> 
                            </li> 
                            
                          </ul> 
                          <div class="col-xs-12 p-n">
                            <div ui-grid="gridOptionsDetalleES" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                              <div class="waterMarkEmptyData" ng-show="!gridOptionsDetalleES.data.length"> No se encontraron datos. </div>
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