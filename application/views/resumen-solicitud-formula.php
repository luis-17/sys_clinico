<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Farmacia</li>
  <li class="active">Resumen de Solicitudes</li>
</ol>
<div class="container-fluid" ng-controller="resumenSolicitudFormulaController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Resumen de Solicitudes de Fórmulas </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">                
                <ul class="form-group demo-btns col-xs-12">                    
                    <li class="form-group mr-n col-md-2 col-sm-4 p-n"> <label> Desde </label> 
                      <div class="input-group col-xs-12"> 
                        <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 50%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                      </div>
                    </li>
                    <li class="form-group mr-n col-md-2 col-sm-4 p-n"> <label> Hasta </label> 
                      <div class="input-group col-xs-12"> 
                        <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 50%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                        <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                        <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                      </div> 
                    </li>
                    <li class="form-group mr col-md-1 col-sm-4 p-n"> <label> ESTADO </label> 
                      <div class="input-group col-xs-12" >
                        <select class="form-control input-sm"  ng-model="fBusqueda.estadoPreparado" ng-options="item as item.descripcion for item in listaEstadoPreparado" tabindex="115" ng-change="getPaginationServerSide();"></select>
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
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].es_anulable == 1 " ><button type="button" class="btn btn-danger" ng-click='btnAnular();'>Anular</button></li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleSolicitud(mySelectionGrid[0]);'>Ver Detalle</button></li>
                  <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_acuenta == 2 && mySelectionGrid[0].estado_preparado != 2 && fSessionCI.key_group != 'key_derma'" ><button type="button" class="btn btn-success" ng-click='btnEntregarPedido();'>Entregar Pedido</button></li> -->
                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>                
              </div>
            </div>
        </div>
    </div>
</div>