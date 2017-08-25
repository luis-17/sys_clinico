<ol class="breadcrumb m-n">
  <li><a href="#/">CONTABILIDAD</a></li>
  <li>CAJA CHICA</li> 
</ol>
<div class="container-fluid" ng-controller="cajaChicaController"> 
  <div class="row">
      <div class="col-md-12">
          <div ng-show="cajaAbiertaPorMiSession" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Caja Chica </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body f-14"> 
              <form name="formCompras" novalidate>
                <div class="row">
                  <div class="col-xs-10 form-inline mb text-left">  
                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n"> EMPRESA </h4></label> 
                      <div class="input-group block"> 
                        <p class="m-xs label label-info" style="font-size: 18px;">{{ fSessionCI.razon_social }}</p> 
                       </div>
                    </div>
                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n"> CAJA </h4></label> 
                      <div class="input-group block"> 
                        <p class="text-info m-xs">{{arr.cajaChica.nombre_caja}}</p>
                       </div>
                    </div>
                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n">CENTRO DE COSTO</h4></label> 
                      <div class="input-group block"> 
                        <p class="text-info m-xs">{{arr.cajaChica.codigo_cc}}-{{arr.cajaChica.nombre_cc}}</p>
                       </div>
                    </div>
                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n"># CHEQUE</h4></label> 
                      <div class="input-group block"> 
                        <p class="text-info m-xs">{{arr.cajaChica.numero_cheque}}</p>
                       </div>
                    </div>
                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n">FONDO FIJO</h4></label> 
                      <div class="input-group block"> 
                        <p class="text-info m-xs" >{{arr.cajaChica.monto_inicial}}</p>
                       </div>
                    </div>

                    <div class="form-group mr-xl"> 
                      <label class="m-n"><h4 class="m-n">RESPONSABLE</h4></label> 
                      <div class="input-group block"> 
                        <p class="m-xs text-info" >{{ fSessionCI.nombres + ' ' + fSessionCI.apellido_paterno + ' ' + fSessionCI.apellido_materno }} </p>
                       </div>
                    </div>
                    <div class="form-group mr-xl" ng-if="arr.cajaChica.estado_acc == 1"> 
                      <label class="m-n"> <h4 class="m-n"> ESTADO: </h4> </label> 
                      <div class="input-group block"> 
                        <label class="label label-success f-16" > CAJA ABIERTA </label>
                      </div>
                    </div>
                    <div class="form-group mr-xl" ng-if="arr.cajaChica.estado_acc == 2"> 
                      <label class="m-n"> <h4 class="m-n"> ESTADO: </h4> </label> 
                      <div class="input-group block"> 
                        <label class="label label-warning f-16" > CAJA LIQUIDADA </label>
                      </div> 
                    </div>
                  </div>
                  <div class="col-xs-2 text-right" >
                    <button type="button" ng-if="arr.cajaChica.estado_acc == 1 || arr.cajaChica.estado_acc == 2" class="btn btn-lg btn-default" ng-click="exportarExcel();">
                      <i class="fa fa-file-excel-o text-success"></i>
                    </button>
                    <button type="button" ng-if="arr.cajaChica.estado_acc == 1" class="btn btn-lg btn-default" ng-click="btnLiquidarCaja();">LIQUIDAR CAJA</button>
                  </div>
                </div>
              </form>
              <div class="row">  
                <div class="col-xs-12">
                  <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                    <div class="panel-heading">
                      <h2>
                        <ul class="nav nav-tabs">
                          <li class="active"> <a data-target="#tab1" href="" data-toggle="tab" > Listado de Movimientos </a> </li>                          
                        </ul>
                      </h2>
                    </div>
                    <div class="panel-body pt-n">
                      <div class="tab-content">
                        <div class="tab-pane active" id="tab1"> 
                          <div class="row">
                            <div class="form-group col-xs-12 mb-md" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <div class="pull-left">
                                <button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button> 
                              </div> 
                              <div class="pull-right" >
                                <button type="button" class="btn btn-success" ng-click="btnNuevoES();" ng-disabled="!(arr.cajaChica.estado_acc == 1)">
                                  <i class="fa fa-file-text"> </i>  Nuevo  
                                </button>
                              </div>
                              
                              <div class="pull-right mr" ng-if="mySelectionGridES.length == 1" > 
                                  <button type="button" class="btn btn-danger" ng-disabled="!(arr.cajaChica.estado_acc == 1)" ng-click='btnAnularMovimiento();'> <i class="fa fa-times-circle"> </i> Anular Movimiento </button>
                              </div>
                              <div class="pull-right"  ng-if="mySelectionGridES.length == 1">
                                <button type="button" class="btn btn-primary mr" ng-click="btnAsientosContables();"> 
                                  <i class="fa fa-eye"> </i> ASIENTOS CONTABLES </button> 
                              </div>
                              <div class="pull-right" ng-if="mySelectionGridES.length == 1"> 
                                  <button type="button" class="btn btn-info mr" ng-click='btnAbrirConversacion(mySelectionGridES[0]);'> <i class="fa fa-eye"> </i> CONVERSACIÓN </button> 
                              </div>
                              <!-- <div class="pull-right mr" ng-if="mySelectionGridES.length == 1">
                                <button type="button" class="btn btn-primary" ng-click="btnSeguimientoEstados();"> 
                                  <i class="fa fa-eye"> </i> ASIENTOS CONTABLES 
                                </button> 
                              </div> -->
                              <!-- <div class="pull-right mr" ng-if="mySelectionGridES.length == 1">
                                <button type="button" class="btn btn-info" ng-click="btnVerDetalleES();"> 
                                  <i class="fa fa-list"> </i> VER DETALLE 
                                </button> 
                              </div> -->
                            </div> 
                            <div class="col-xs-12">
                              <div ui-grid="gridOptionsES" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsES.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                            <div class="col-lg-offset-8 col-lg-4 col-md-offset-6 col-md-6 col-xs-12"> 
                              <div class="" style="line-height: 1.2; margin-top: 20px;">
                                <div class="">
                                  <h3 class="text-info mb-n inline" style="font-weight: 100;"> FONDO FIJO: </h3> 
                                  <small style="font-size: 28px;clear: both;" class="text-info pull-right"> {{ arr.cajaChica.monto_inicial }} </small>
                                </div>
                                <div class="">
                                  <h3 class="text-default m-n inline" style="font-weight: 100;"> IMPORTE TOTAL: </h3> 
                                  <small style="font-size: 28px;clear: both;text-decoration: underline;" class="text-default pull-right"> S/. {{ gridOptionsES.sumTotal }} </small>
                                </div>
                                <div class="">
                                  <h3 class="text-success m-n inline" style="font-weight: 100;"> SALDO: </h3> 
                                  <small style="font-size: 28px;clear: both;" class="text-success pull-right"> {{ arr.cajaChica.saldo }} </small>
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
      <div class="col-md-12">
        <div ng-show="!cajaAbiertaPorMiSession" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
          <div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
            <h2> La Caja está cerrada. </h2> 
          </div>
          <div class="panel-body">
            <div class="col-xs-12">
              <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> Proceda a abrir caja para comenzar... </div>
              <button ng-click="btnAperturaCaja();" class="btn btn-success btn-lg ng-scope block" type="button" ng-if="!cajaAbiertaPorMiSession" style="margin: auto;"> 
                    <i class="ti ti-plus"></i> APERTURAR CAJA </button> 
            </div>
          </div>
        </div>
      </div>
  </div>
</div>