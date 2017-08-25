<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li> 
  <li> INFORMES </li> 
  <li class="active"> RESUMEN DE ATENCIONES </li> 
</ol>
<div class="container-fluid" ng-controller="resumenAtencionController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Resumen de Atenciones - HOSPITAL </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Especialidad </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.idespecialidad" ng-change="getPaginationServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaEspecialidades" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" placeholder="Hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> 
                      <div class="input-group" style="width: 230px;"> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> 
                      </div> 
                    </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click="btnDetalle('xlg')">Ver Detalle</button></li>
                </ul>
                <div class="row">
                  <div class="col-xs-12">
                    <div  ui-grid="gridOptionsRA" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                  </div>
                </div>
                <!-- <div class="col-md-4 col-xs-12"> </div> -->
                <div class="row">
                  <div class="col-md-3 col-sm-6 col-xs-12">
                      <div class="text-right">
                        <h4 class="well well-sm"> CANT. VENDIDOS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countCancelados }} </strong> </h4>
                      </div>
                  </div>
                  
                  <div class="col-md-3 col-sm-6 col-xs-12">
                      <div class="text-right">
                        <h4 class="well well-sm"> CANT. ATENDIDOS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countAtendido }} </strong> </h4>
                      </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-12">
                      <div class="text-center">
                        <h4 class="well well-sm"> CANT. RESTANTES <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countRestante }} </strong> </h4>
                      </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-12"> 
                      <div class="text-right">
                        <h4 class="well well-sm"> TOTAL INGRESOS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.sumIngresos }} </strong> </h4>
                      </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Resumen de Atenciones - FARMACIA </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns row">
                    <!-- <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Especialidad </label> 
                      <div class="input-group block"> 
                        <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.idespecialidad" ng-change="getPaginationServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaEspecialidades" > </select> 
                      </div>
                    </li> -->
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Empresa / Sede </label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusquedaFarm.sedeempresa" ng-change="getTotalesFarmServerSide();" 
                          ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusquedaFarm.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaFarm.desdeHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaFarm.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusquedaFarm.hasta" placeholder="Hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusquedaFarm.hastaHora" style="width: 45px; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusquedaFarm.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> 
                      <div class="input-group" style="width: 230px;"> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getTotalesFarmServerSide();" /> 
                      </div> 
                    </li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click="btnDetalle('xlg')">Ver Detalle</button></li> -->
                </ul>
                <!-- <div class="row">
                  <div class="col-xs-12">
                    <div  ui-grid="gridOptionsFarm" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                  </div>
                </div> -->
                <!-- <div class="col-md-4 col-xs-12"> </div> -->
                <div class="row">
                  <div class="col-md-3 col-sm-6 col-xs-12">
                      <div class="text-right">
                        <h4 style="margin-bottom: 0px;" class="well well-sm"> CANT. VENDIDOS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsFarm.countVentas }} </strong> </h4>
                      </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-12"> 
                      <div class="text-right">
                        <h4 style="margin-bottom: 0px;" class="well well-sm"> TOTAL INGRESOS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsFarm.sumIngresos }} </strong> </h4>
                      </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>