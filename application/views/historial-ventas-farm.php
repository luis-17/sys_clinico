<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Ventas</li>
  <li class="active">Historial de Ventas de Farmacia</li>
</ol>
<div class="container-fluid" ng-controller="historialVentasFarmController"> 
    <div class="row">
        <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"draggable": "false"}'>
          <div class="panel-heading">
            <h2>
              <ul class="nav nav-tabs">
                <li class="active"><a data-target="#home" href="" data-toggle="tab">Historial de Ventas de Farmacia</a></li>
                <li><a data-target="#tab2" href="" data-toggle="tab">Historial de Ventas Por Medicamentos</a></li>
                <!-- <li><a data-target="#tab3" href="" data-toggle="tab">Historial de Ventas Por Preparados</a></li> -->
              </ul>
            </h2>
          </div>
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active" id="home">
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="getPaginationServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Desde </label> 
                      <div class="input-group col-sm-12"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Hasta </label> 
                      <div class="input-group col-sm-12"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-md col-sm-2 p-n"> 
                      <div class="input-group" style=""> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide('true');" /> 
                      </div>
                    </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                  <li class="pull-right" ><button type="button" class="btn btn-danger" ng-click='btnAnular();' style="height: 0; display: none;">Anular</button></li>
                  <li class="pull-right" ng-if="mySelectionGridV.length > 0" ><button type="button" class="btn btn-warning" ng-click='btnSolicitudImprimirTicket(mySelectionGridV[0]);'> <i class="fa fa-share"> </i> Solicitar Re-impresión </button></li>
                  <li class="pull-right" ng-if="mySelectionGridV.length > 0" ><button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionGridV[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button></li>
                  <li class="pull-right" ng-if="mySelectionGridV.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridV[0]);'>Ver Detalle</button></li>
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                    <div class="text-center">
                      <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : S/. {{ gridOptions.totalImporte }} </strong> </h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                    <div class="text-center">
                      <h4 class="well well-sm"> CANT. VENTAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptions.totalItems }} </strong> </h4>
                    </div>
                </div>
              </div>
              <!-- HISTORIAL DE VENTAS POR MEDICAMENTO -->
              <div class="tab-pane" id="tab2">
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="getPaginationMedServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Desde </label> 
                      <div class="input-group col-sm-12 col-md-12" > 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Hasta </label> 
                      <div class="input-group col-sm-12 col-md-12" > 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-md col-sm-2 p-n"> 
                      <div class="input-group col-sm-12 col-md-12" style=""> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationMedServerSide('true');" /> 
                        <button ng-if="gridOptionsMed.data.length>0" type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button> 
                      </div> 
                    </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringMed();'>Buscar</button></li> 
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptionsMed" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                    <div class="text-center">
                      <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : S/. {{ gridOptionsMed.totalImporte }} </strong> </h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                    <div class="text-center">
                      <h4 class="well well-sm"> CANT. VENTAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsMed.totalVentas }} </strong> </h4>
                    </div>
                </div>
              </div>
              <!-- FIN -->
              <!-- HISTORIAL DE VENTAS POR PREPARADOS -->
              <div class="tab-pane" id="tab3">
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="getPaginationMedServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Desde </label> 
                      <div class="input-group col-sm-12 col-md-12" > 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-2 col-sm-3 p-n"> <label> Hasta </label> 
                      <div class="input-group col-sm-12 col-md-12" > 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-md col-sm-2 p-n"> 
                      <div class="input-group col-sm-12 col-md-12" style=""> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationPreServerSide('true');" /> 
                        <button ng-if="gridOptionsMed.data.length>0" type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button> 
                      </div> 
                    </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPre();'>Buscar</button></li> 
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptionsPre" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                    <div class="text-center">
                      <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : S/. {{ gridOptionsPre.totalImporte }} </strong> </h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                    <div class="text-center">
                      <h4 class="well well-sm"> CANT. VENTAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsPre.totalVentas }} </strong> </h4>
                    </div>
                </div>
              </div>
              <!-- FIN -->
            </div>
          </div>
        </div>
        </div>
    </div>
</div>