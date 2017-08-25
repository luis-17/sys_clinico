<ol class="breadcrumb"> 
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>GESTION DE CAJA</li>
  <li class="active"> NOTAS DE CREDITO </li>
</ol> 
<div class="container-fluid" ng-controller="notaCreditoFarmController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Notas de Crédito </h2> 
              </div>
              <div class="panel-body">
                <ul class="row demo-btns">
                    <li class="form-group mr-xl mt-sm col-md-3 col-sm-6 col-xs-12 p-n" > <label> Empresas / Sedes </label> 
                      <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="onChangeEmpresaSede();" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </li> 
                    <li class="form-group mr mt-sm col-md-2 col-sm-6 p-n"> <label> Desde </label> 
                      <div class="input-group " style="width: 230px;"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 140px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-md-2 col-sm-6 p-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="111" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 140px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      </div>
                    </li>
                    <!--<li class="form-group mr mt-sm col-md-3 col-sm-6 col-xs-12 p-n" > <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input type="text" class="form-control input-sm datepicker" datepicker-popup="{{dateUIDesde.format}}" ng-model="fBusqueda.desde" 
                          is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" close-text="Close" placeholder="Desde" tabindex="1" />
                        <div class="input-group-btn">
                          <button type="button" class="btn btn-default btn-sm" ng-click="dateUIDesde.openDP($event)" tabindex="2"><i class="ti ti-calendar"></i></button>
                        </div>
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-md-3 col-sm-6 col-xs-12 p-n" > <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input type="text" class="form-control input-sm datepicker" datepicker-popup="{{dateUIHasta.format}}" ng-model="fBusqueda.hasta" 
                          is-open="dateUIHasta.opened" datepicker-options="dateUIHasta.datePikerOptions" close-text="Close" placeholder="Hasta" tabindex="3" />
                        <div class="input-group-btn">
                          <button type="button" class="btn btn-default btn-sm" ng-click="dateUIHasta.openDP($event)" tabindex="4"><i class="ti ti-calendar"></i></button>
                        </div>
                      </div>
                    </li>-->
                    <li class="form-group mr mt-sm col-md-2 col-sm-5 col-xs-11 p-n" > 
                        <button type="button" class="btn btn-success" ng-click="getPaginationServerSide();"> CONSULTAR </button> 
                    </li>
                </ul> 
                <div class="row">
                  <div class="col-md-12">
                    <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                      <div class="panel-heading">
                        <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                        <h2>
                          <ul class="nav nav-tabs">
                            <li class="active"><a data-target="#home" href="" data-toggle="tab">Notas de Crédito por Empresa - Sede</a></li> 
                          </ul>
                        </h2>
                      </div>
                      <div class="panel-body">
                        <div class="tab-content">
                          <div class="tab-pane active" id="home">
                            <ul class="form-group demo-btns">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                              <li class="pull-right" ><button type="button" class="btn btn-success" ng-click='btnNuevo();'>Nuevo</button></li>
                              <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular();'>Anular</button></li>
                                
                            </ul> 
                            <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                              <div class="waterMarkEmptyData" style="font-size: 24px; top: 60px;" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                            </div> 
                            <div class="col-md-10 col-xs-12"> </div>
                            <div class="col-md-2 col-xs-12">
                                <div class="text-right">
                                  <h4 class="well well-sm"> TOTAL <strong style="font-weight: 400; text-decoration: underline;" class="text-success"> : {{ gridOptions.suma_total }} </strong> </h4>
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
        <!-- <div class="col-md-12">
            
        </div> -->
    </div>
</div>