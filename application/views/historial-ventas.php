<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Ventas</li>
  <li class="active">Historial de Ventas</li>
</ol>
<div class="container-fluid" ng-controller="historialVentasController"> 
    <div class="row">
        <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Historial de Ventas </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body">
              <!-- -->
              <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr col-sm-2 p-n" > <label> Empresa / Sede </label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.sedeempresa" ng-change="cargarEspecialidades(fBusqueda.sedeempresa)" 
                        ng-options="item as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr col-sm-2 p-n" > <label> Especialidad </label> 
                    <div class="input-group block"> 
                      <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.especialidad" ng-change="getPaginationServerSide();" 
                        ng-options="item as item.descripcion for item in listaEspecialidades" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr col-sm-2 p-n" > <label> Convenios </label> 
                    <div class="input-group block"> 
                      <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.convenio" ng-change="getPaginationServerSide();" 
                        ng-options="item as item.descripcion for item in listaConvenios" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Desde </label> 
                    <div class="input-group col-xs-12"> 
                      <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 20%; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Hasta </label> 
                    <div class="input-group col-xs-12"> 
                      <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 20%; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class="form-group mr col-md-1 col-sm-4 col-xs-12 p-n" style="margin-top: 12px;" > 
                    <div class="input-group" style=""> 
                      <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> 
                    </div> 
                  </li>
              </ul>
              <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                <li class="pull-right" ><button type="button" class="btn btn-danger" ng-click='btnAnular();' style="height: 0; display: none;">Anular</button></li>
                <!-- <li class="pull-right" ng-if="mySelectionGridV.length > 0" ><button type="button" class="btn btn-warning" ng-click='btnSolicitudImprimirTicket();'> <i class="fa fa-share"> </i> Solicitar Re-impresión </button></li> -->

                <li class="pull-right" ng-if="mySelectionGridV.length > 0" >
                  <button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionGridV[0]);'>
                    <i class="fa fa-print"> </i> Imprimir Ticket
                  </button>
                </li>
                <li class="pull-right" ng-if="(mySelectionGridV.length > 0) && (fSessionCI.key_group == 'key_admin' ||  fSessionCI.key_group == 'key_sistemas' )" >
                  <button type="button" class="btn btn-midnightblue" ng-click='btnImprimirTicketManual();'>
                    <i class="fa fa-print"> </i>[F8] Imprimir Ticket Manual
                  </button>
                </li>
                <li class="pull-right" ng-if="mySelectionGridV.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridV[0]);'>Ver Detalle</button></li>
              </ul> 
              <div class="col-xs-12 p-n">
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                  <div class="text-center">
                    <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : {{ gridOptions.totalImporte }} </strong> </h4>
                  </div>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                  <div class="text-center">
                    <h4 class="well well-sm"> CANT. VENTAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptions.totalItems }} </strong> </h4>
                  </div>
              </div> 
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2> Historial de Ventas Web </h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body"> 
              <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr col-sm-2 p-n" > <label> Empresa / Sede </label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusquedaWeb.sedeempresa" ng-change="cargarEspecialidades(fBusquedaWeb.sedeempresa)" 
                        ng-options="item as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Desde </label> 
                    <div class="input-group col-xs-12"> 
                      <input tabindex="210" type="text" class="form-control input-sm mask" ng-model="fBusquedaWeb.desde" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="215" type="text" class="form-control input-sm" ng-model="fBusquedaWeb.desdeHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="216" type="text" class="form-control input-sm" ng-model="fBusquedaWeb.desdeMinuto" style="width: 20%; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Hasta </label> 
                    <div class="input-group col-xs-12"> 
                      <input tabindex="220" type="text" class="form-control input-sm mask" ng-model="fBusquedaWeb.hasta" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="222" type="text" class="form-control input-sm" ng-model="fBusquedaWeb.hastaHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="223" type="text" class="form-control input-sm" ng-model="fBusquedaWeb.hastaMinuto" style="width: 20%; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class="form-group mr col-md-1 col-sm-4 col-xs-12 p-n" style="margin-top: 12px;" > 
                    <div class="input-group" style=""> 
                      <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSideWeb();" /> 
                    </div> 
                  </li>
              </ul>
              <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                <li class="pull-right" ng-if="mySelectionGridV.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridV[0]);'>Ver Detalle</button></li>
              </ul> 
              <div class="col-xs-12 p-n">
                <div ui-grid="gridOptionsWeb" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                  <div class="text-center">
                    <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsWeb.totalImporte }} </strong> </h4>
                  </div>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                  <div class="text-center">
                    <h4 class="well well-sm"> CANT. VENTAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsWeb.totalItems }} </strong> </h4>
                  </div>
              </div> 
            </div>
          </div>
        </div>
    </div>
</div>