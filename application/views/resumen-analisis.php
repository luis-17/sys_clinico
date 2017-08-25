<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li> 
  <li> INFORMES </li> 
  <li class="active"> RESUMEN DE ANALISIS </li> 
</ol>
<div class="container-fluid" ng-controller="resumenAnalisisController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Resumen de Analisis </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="110" type="text" class="form-control input-sm" ng-model="fBusqueda.desde" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'"/>
                      <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px; margin-left: 4px;" />
                      <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                    <div class="input-group" style="width: 230px;"> 
                      <input tabindex="120" type="text" class="form-control input-sm" ng-model="fBusqueda.hasta" placeholder="Hasta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px; margin-left: 4px;" />
                      <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> 
                    <div class="input-group" style="width: 230px;"> 
                      <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> 
                    </div> 
                  </li>
                  <li class="pull-right mt-sm" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click="btnDetalle('xlg')">Ver Detalle</button></li>

                  <li class="pull-right m-xs" ng-if="gridOptionsRA.data.length > 0 " >
                    <button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' style="padding: 2px 4px;" title="Exportar a Excel">
                      <i class="fa fa-file-excel-o text-success f-24" ></i>
                    </button>
                  </li>

                  <li class="pull-right mt-sm" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click="btnDetalle('xlg')">Ver Detalle</button></li>
                </ul>
                <div class="col-xs-12 p-n">
                  <div  ui-grid="gridOptionsRA" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
                </div>
                <div class="col-md-4 col-xs-12"> </div>
                <div class="col-md-2 col-xs-12">
                    <div class="text-center">
                      <h4 class="well well-sm"> Total Registrados <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countIngresados }} </strong> </h4>
                    </div>
                </div>
                
                <div class="col-md-2 col-xs-12">
                    <div class="text-center">
                      <h4 class="well well-sm"> Total Con Resultados <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countAtendido }} </strong> </h4>
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="text-center">
                      <h4 class="well well-sm"> Total Sin Resultados <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countRestante }} </strong> </h4>
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="text-center">
                      <h4 class="well well-sm"> Total Entregados <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsRA.countEntregados }} </strong> </h4>
                    </div>
                </div>
                <!-- <div ui-grid="gridComboOptions" ui-grid-pagination ui-grid-selection ui-grid-cellNav class="grid table-responsive"></div> --> 
              </div>
            </div>
        </div>
    </div>
</div>