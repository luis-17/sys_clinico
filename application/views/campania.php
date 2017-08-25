<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Mantenimiento</li>
  <li class="active"> Gestion de Campañas </li>
</ol>
<div class="container-fluid" ng-controller="campaniaController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Mantenimiento de Campañas</h2> 
              </div>
              <div class="panel-body">
                <!--<div ng-show="cajaAbiertaPorMiSession || !(fSessionCI.key_group == 'key_caja')">-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                        <div class="panel-heading">
                          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                          <h2>
                            <ul class="nav nav-tabs">
                              <li class="active">
                                <a data-target="#home" href="" data-toggle="tab" ng-click="reloadGrid();">Campañas</a> 
                              </li>
                              <li><a data-target="#tab2" href="" data-toggle="tab" ng-click="reloadGrid();">Detalle Campañas<label class="label label-danger" style="margin: 7px;opacity: 0.5;"> </label> </a></li>
                            </ul>
                          </h2>
                        </div>
                        <div class="panel-body">
                          <div class="tab-content">
                            <div class="tab-pane active" id="home"> 
                              <ul class="form-group demo-btns col-xs-12">
                                <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                                  <div class="input-group block"> 
                                    <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="getPaginationServerSide();getPaginationServerSideDetalle();" 
                                      ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                                  </div>
                                </li>
                                <!-- 
                                <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                                  <div class="input-group" style="width: 230px;"> 
                                    <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                                    <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" />
                                    <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                                  </div>
                                </li>
                                <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                                  <div class="input-group" style="width: 230px;"> 
                                    <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                                    <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" />
                                    <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                                  </div> 
                                </li>
                                <li class="form-group mr mt-sm col-sm-2 p-n"> 
                                  <div class="input-group" style=""> 
                                    <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> 
                                  </div> 
                                </li> -->

                                <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-primary" ng-click='btnClonar()'>Clonar</button></li>                                
                                <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitar()'>Deshabilitar</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar()'>Habilitar</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnRegEdit("edit")'>Editar</button></li>
                                <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnRegEdit("reg")'>Nuevo</button></li>
                              </ul> 
                              
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
                            </div>

                            <div class="tab-pane" id="tab2">
                              <ul class="row demo-btns">
                                <!-- <li class="form-group mr mt-sm col-sm-3 col-md-2 p-n" > <label> Campañas </label> 
                                  <select class="form-control input-sm" ng-model="fData.idcampania" ng-options="item.id as item.descripcion for item in listaCampania" ng-change="OnChangeCampania(fData.idcampania)"></select>
                                </li> 
                                <li class="form-group mr mt-sm col-sm-3 col-md-2 p-n" > <label> Paquetes </label> 
                                  <select class="form-control input-sm" ng-model="fData.idpaquete" ng-options="item.id as item.descripcion for item in listaPaquete" ng-change="OnChangePaquete(fData.idpaquete)"></select> 
                                </li> -->
                                <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                                  <div class="input-group block"> 
                                    <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="getPaginationServerSide();getPaginationServerSideDetalle();" 
                                      ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                                  </div>
                                </li>
                              </ul>
                              <ul class="form-group demo-btns">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringDetalle()'>Buscar</button></li>
                                <!--<li class="pull-right" ng-if="mySelectionGrid2.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnularDet()'>Anular</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid2.length > 0 " ><button type="button" class="btn btn-default" ng-click='btnDeshabilitarDet()'>Deshabilitar</button></li>
                                <li class="pull-right" ng-if="mySelectionGrid2.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitarDet()'>Habilitar</button></li>-->
                                <!-- <li class="pull-right" ng-if="mySelectionGrid2.length == 1"><button type="button" class="btn btn-warning" ng-click='btnAccionDetalle("lg","edit")'>Editar</button></li>
                                <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnAccionDetalle("lg","reg")'>Nuevo</button></li> -->
                              </ul>
                             <div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>

                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <!--</div>-->
 
              </div>
            </div>
        </div>
        <!-- <div class="col-md-12">
            
        </div> -->
    </div>
</div>