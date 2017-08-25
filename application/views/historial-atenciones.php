<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Citas</li>
  <li class="active">Historial de Atenciones</li>
</ol>
<div class="container-fluid" ng-controller="historialAtencionesController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Historial de Atenciones Médicas </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <!-- -->
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Especialidad </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusquedaPAH.empresaespecialidad" ng-change="getListaMedicos(); limpiarGrilla();" 
                          ng-options="item as item.descripcion for item in listaEmpresaEspecialidades" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-1 col-md-2 p-n" ng-show="contFiltroMedico" > <label> Médico </label> 
                      <div class="input-group block"> 
                        <select tabindex="105" class="form-control input-sm" ng-model="fBusquedaPAH.medico" ng-change="getPaginationServerSidePAH(true);" 
                          ng-options="item as item.medico for item in listaMedicos" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-lg-1 col-md-2 col-sm-3 col-xs-12 p-n" > <label> Tipo de Atención </label> 
                      <div class="input-group block"> 
                        <select tabindex="108" class="form-control input-sm" ng-model="fBusquedaPAH.idTipoAtencion" ng-change="getPaginationServerSidePAH(true);" 
                          ng-options="item.id as item.descripcion for item in listaTipoAtencionMedica" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusquedaPAH.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaPAH.desdeHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaPAH.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusquedaPAH.hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusquedaPAH.hastaHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusquedaPAH.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-md col-sm-2 p-n"> 
                      <div class="input-group" style=""> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSidePAH(true);" /> 
                        <input type="button" ng-show="gridOptionsPAH.data.length > 0" class="btn btn-success" value="VER REPORTE" 
                          ng-if="!(fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_coord_salud')" ng-click="imprimirProduccionMedico();" /> 
                      </div> 
                    </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid gray;"> 
                    <li class="pull-right" ng-if="mySelectionPAHGrid.length > 0 && fSessionCI.key_group == 'key_sistemas'"> 
                      <button type="button" class="btn btn-danger" ng-click='btnAnularAtencion();'>Anular Atención</button>
                    </li>
                    <li class="pull-right" ng-if="mySelectionPAHGrid.length > 0 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_admin' || fSessionCI.key_group == 'key_coord_salud'
                      || fSessionCI.key_group == 'key_dir_salud' || fSessionCI.key_group == 'key_gerencia' || fSessionCI.key_group == 'key_aud_salud' || fSessionCI.key_group == 'key_salud_caja')"> 
                      <button type="button" class="btn btn-success" ng-click='btnImprimirFichaAtencion(mySelectionPAHGrid)'> Imprimir Ficha </button>
                    </li>
                    <li class="pull-right" ng-if="mySelectionPAHGrid.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerFichaAtencion(mySelectionPAHGrid)'>Ver Ficha</button></li>
                    <li class="pull-right" ng-if="mySelectionPAHGrid.length == 1 && fSessionCI.key_group == 'key_sistemas'"> 
                      <button type="button" class="btn btn-warning" ng-click='btnCambiarEmpresa(mySelectionPAHGrid[0]);'>CAMBIAR EMPRESA</button>
                    </li>
                    <li class="pull-right" ng-if="mySelectionPAHGrid.length == 1 && ( fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_aud_salud' || fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_dir_salud' )" > 
                      <button type="button" class="btn btn-warning" ng-click='btnEditarFichaAtencion()'>Editar Ficha</button> 
                    </li>
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptionsPAH" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid"></div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right" ng-if="!(fSessionCI.key_group == 'key_salud') && !(fSessionCI.key_group == 'key_lab')"> 
                    <div class="text-center">
                      <h4 class="well well-sm"> TOTAL IMPORTE <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsPAH.totalImporte }} </strong> </h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pull-right p-n">
                    <div class="text-center">
                      <h4 class="well well-sm"> CANT. ATENCIONES <strong style="font-size: 20px;" class="text-success"> : {{ gridOptionsPAH.totalItems }} </strong> </h4>
                    </div>
                </div>
                
              </div>
            </div>
        </div>
    </div>
</div>