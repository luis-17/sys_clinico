<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Programación Asistencial</li>
  <li class="active">Historial de Citas</li>
</ol>
<div class="container-fluid" ng-controller="historialCitasController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Historial de Citas </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <!-- -->
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr col-sm-2 p-n" style="width: 18%"> <label> Empresa / Sede </label> 
                      <div class="input-group block"> 
                        <p class="bold truncate" style="height: 15px; padding-top: 10px; font-size: 14px;"> {{ fSessionCI.razon_social }} / {{ fSessionCI.sede }} </p> 
                      </div>
                    </li>
                    <li class="form-group mr col-sm-2 p-n" style="width: 13%"> <label> Especialidad </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusquedaCitas.especialidad" ng-change="getPaginationServerSide();" 
                          ng-options="item as item.descripcion for item in listaEspecialidades" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr col-sm-1 p-n" > <label> Tipo de Atención </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusquedaCitas.tipoAtencion" ng-change="getPaginationServerSide();" 
                          ng-options="item as item.descripcion for item in listaTipoAtencion" > </select> 
                      </div>
                    </li>
                    <!-- <li class="form-group mr col-sm-2 p-n" > <label> Convenios </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusquedaCitas.convenio" ng-change="getPaginationServerSide();" 
                          ng-options="item as item.descripcion for item in listaConvenios" > </select> 
                      </div>
                    </li> -->
                    <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Desde </label> 
                      <div class="input-group col-xs-12"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusquedaCitas.desde" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaCitas.desdeHora" style="width: 20%; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaCitas.desdeMinuto" style="width: 20%; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr col-md-2 col-sm-4 p-n"> <label> Hasta </label> 
                      <div class="input-group col-xs-12"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusquedaCitas.hasta" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusquedaCitas.hastaHora" style="width: 20%; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusquedaCitas.hastaMinuto" style="width: 20%; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr col-md-1 col-sm-4 col-xs-12 p-n" style="margin-top: 12px;" > 
                      <div class="input-group" style=""> 
                        <button type="button" class="btn btn-info" ng-click="getPaginationServerSide(true);" > 
                          <i class="fa fa-refresh"></i> PROCESAR 
                        </button> 
                      </div> 
                    </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;">
                  <li class="pull-right" ng-if="mySelectionGridHC.length == 1 && (mySelectionGridHC[0].estado_cita == 2)" >
                    <button type="button" class="btn btn-info" ng-click='btnModificarCita();'>
                      <i class="fa fa-edit"> </i> Modificar Cita 
                    </button>
                  </li>
                  <!-- <li class="pull-right" ng-if="(mySelectionGridHC.length > 0) && (fSessionCI.key_group == 'key_admin' ||  fSessionCI.key_group == 'key_sistemas' )" >
                    <button type="button" class="btn btn-midnightblue" ng-click='btnImprimirTicketManual();'>
                      <i class="fa fa-print"> </i>[F8] Imprimir Ticket Manual
                    </button>
                  </li> -->
                  <!-- <li class="pull-right" ng-if="mySelectionGridHC.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleVenta(mySelectionGridHC[0]);'>Ver Detalle</button> </li> -->
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptionsCitas" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid"></div>
                </div>
                <!-- <div class="col-lg-3 col-md-4 col-sm-12 pull-right"> 
                    <div class="text-center">
                      <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : {{ gridOptions.totalImporte }} </strong> </h4>
                    </div>
                </div> -->
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                    <div class="text-center">
                      <h4 class="well well-sm"> CANT. CITAS <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsCitas.totalItems }} </strong> </h4>
                    </div>
                </div> 
              </div>
            </div>
        </div>
    </div>
</div>