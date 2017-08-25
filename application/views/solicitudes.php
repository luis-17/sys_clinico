<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>GESTION DE CITAS</li>
  <li class="active">SOLICITUDES</li>
</ol>
<div class="container-fluid" ng-controller="solicitudesController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Solicitudes</h2>
              </div>
              
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body" ng-show="!(fSessionCI.key_group === 'key_caja_far')" >
                <!-- -->
                <form name="formSolicitud" novalidate>
                  <ul class="form-group demo-btns col-xs-12">  
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="2" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora" />
                        <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                      <div class="input-group" style="width: 230px;"> 
                        <input tabindex="4" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
                        <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" required ng-pattern="pHora"/>
                        <input tabindex="6" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" required ng-pattern="pMinuto"/>
                      </div> 
                    </li>
                    <li class="form-group mr mt-md col-sm-2 p-n"> 
                      <div class="input-group" style=""> 
                        <input type="button" class="btn btn-info" value="PROCESAR" ng-click="procesar()" tabindex="7" ng-disabled="formSolicitud.$invalid"/> 
                      </div> 
                    </li>
                  </ul>
                  <!--<ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Almacén </label>
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="procesar()" 
                          ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                      </div>
                    </li>
                  
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Tipo Ingreso</label> 
                      <div class="input-group block"> 
                        <select class="form-control input-sm" ng-model="fBusqueda.idtipoentrada" ng-change="procesar()" 
                          ng-options="item.id as item.descripcion for item in listaTipoEntrada" > </select> 
                      </div>
                    </li>
                  </ul> -->
                </form>
                <div class="row">  
                  <div class="col-md-12">
                    <div class="panel panel-default" data-widget='{"id" : "wiget10001"}'>
                      <div class="panel-heading">
                        <h2>
                          <ul class="nav nav-tabs">
                            <li class="active">
                              <a data-target="#home" href="" data-toggle="tab" >Solicitudes de Procedimientos </a> 
                            </li>
                            <li><a data-target="#tab2" href="" data-toggle="tab" >Solicitudes de Exámenes Auxiliares 
                              </a> 
                            </li>
                          </ul>
                        </h2>
                      </div>
                      <div class="panel-body pt-n">
                        <div class="tab-content">

                          <div class="tab-pane active" id="home">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li> 
                              <li class="pull-right">
                                  <div class="pull-right ml-sm" ng-if="gridOptions.data.length>0"><button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button></div>
                                  <div class="btn-group">
                                      <button type="button" class="btn btn-success" ng-click='btnNuevoProcedimiento();'>
                                        <i class="fa fa-file-text"> </i>  Nuevo Procedimiento 
                                      </button>
                                  </div>
                              </li>

                              <li class="pull-right" ng-if="mySelectionGridIngr.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-danger" ng-click='btnAnularEntrada();'> <i class="fa fa-times-circle"> </i> Anular Solicitud </button></li>
                              <li class="pull-right">
                                <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGridIngr[0].idmovimiento, mySelectionGridIngr[0].estado_movimiento);" ng-if="mySelectionGridIngr.length == 1" ><i class="fa fa-print"></i> [F4] IMPRIMIR </button>
                              </li>
                              <li class="pull-right" ng-if="mySelectionGridIngr.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerDetalleEntrada(mySelectionGridIngr[0]);'>Ver Detalle</button></li>
                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                              </div>
                            </div>
                          </div>

                          <div class="tab-pane" id="tab2">
                            <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ddd;">
                              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringEA();'>Buscar</button></li> 
                              <li class="pull-right">
                                <div class="pull-right ml-sm" ng-if="gridOptionsEA.data.length>0"><button type="button" class="btn btn-default" ng-click='btnExportarListaEAExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button></div>  
                                 <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                      <i class="fa fa-file-text"> </i>  Nuevo Examen <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="" ng-click="btnNuevoExamen('','I');">IMAGENOLOGIA</a></li>
                                        <li><a href="" ng-click="btnNuevoExamen('','PC');">LABORATORIO</a></li>
                                        <li><a href="" ng-click="btnNuevoExamen('','AP');">ANATOMIA PATOLOGICA</a></li>
                                        
                                    </ul>
                                  </div>
                              </li>

                            </ul> 
                            <div class="col-xs-12 p-n">
                              <div ui-grid="gridOptionsEA" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
                                <div class="waterMarkEmptyData" ng-show="!gridOptionsEA.data.length"> No se encontraron datos. </div>
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
</div>