<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Caja</li>
  <li class="active">Descuentos</li>
</ol>
<div class="container-fluid" ng-controller="ventaDescuentoController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Descuentos </h2> 
              </div>
              <div class="panel-body">
                <div ng-show="cajaAbiertaPorMiSession || !(fSessionCI.key_group == 'key_caja')">
                  <ul class="row demo-btns">
                      <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresas / Sedes </label> 
                        <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="onChangeEmpresaSede();" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Cajas Abiertas </label> 
                        <select class="form-control input-sm" ng-model="fBusqueda.cajamaster" ng-change="getPaginationServerSide(); getPaginationVAServerSide(); getPaginationEEServerSide(); getPaginationPVServerSide();" ng-options="item.id as item.descripcion for item in listaCajaMaster" > </select> 
                      </li>
                  </ul>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                        <div class="panel-heading">
                          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                          <h2>
                            <ul class="nav nav-tabs">
                              <li class="active">
                                <a data-target="#home" href="" data-toggle="tab" ng-click="reloadGrid();"> Descuentos por Aprobar </a> 
                              </li>
                            </ul>
                          </h2>
                        </div>
                        <div class="panel-body">
                          <div class="tab-content">
                            <div class="tab-pane active" id="home"> 
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
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringEE()'>Buscar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnDesaprobarVenta();'>Desaprobar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length > 0" ><button type="button" class="btn btn-warning" ng-click='btnAprobarVenta();'>Aprobar</button></li>
                                <li class="pull-right" ng-if="mySelectionGridEE.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridEE[0]);'>Ver Detalle</button></li>
                              </ul>
                              <div ui-grid="gridOptionsVentasEnEspera" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsVentasEnEspera.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div ng-show="!cajaAbiertaPorMiSession && (fSessionCI.key_group == 'key_caja')"> <!--  -->
                  <div class="waterMarkEmptyData"> Proceda a abrir caja para comenzar... </div>
                </div>
              </div>
            </div>
        </div>
        <!-- <div class="col-md-12">
            
        </div> -->
    </div>
</div>