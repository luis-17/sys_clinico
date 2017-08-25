<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Capital Humano</li>
  <li class="active">Planillas</li>
</ol>
<div class="container-fluid" ng-controller="aperturaPlanillaController">
    <div class="row">
        <div class="col-md-12" ng-show="!viewEmpleados">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Planilla</h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-sm-2 p-n" >
                    <label class="control-label mb-xs"> EMPRESA: </label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.empresa" ng-change="getPaginationServerSide();" 
                        ng-options="item.descripcion for item in listaEmpresaAdmin" > </select> 
                    </div>
                  </li>
                  <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li> -->
                  
                  <li class="pull-right" ><button type="button" class="btn" ng-click='btnVariablesLey()'>VARIABLES DE LEY</button></li>
                  <li class="pull-right" ><button type="button" class="btn btn-success" ng-click='btnApertura()'>APERTURAR PLANILLA</button></li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-primary" ng-click='btnEmpleados()'>VER EMPLEADOS</button></li>
                  
                  <li class="pull-right ng-scope" ng-if="mySelectionGrid.length == 1">
                    <div class="btn-group open">
                      <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="true">
                          REPORTES <span class="caret"></span>
                      </button>
                      <ul role="menu" class="dropdown-menu">
                          <li><a href="" ng-click='btnExportarExcel()'>IMPRIMIR PLANILLA - EXCEL</a></li>
                          <li ng-show="mySelectionGrid[0].tiene_cts == 1"><a href="" ng-click='btnImprimirCTS()'>IMPRIMIR CTS - PDF</a></li>
                      </ul>
                    </div>
                  </li>

                  <li class="pull-right ng-scope" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_pl == 1 ">
                    <div class="btn-group open">
                      <button data-toggle="dropdown" class="btn btn-info dropdown-toggle" type="button" aria-expanded="true">
                          MAS OPCIONES <span class="caret"></span>
                      </button>
                      <ul role="menu" class="dropdown-menu">
                          <li><a href="" ng-click='btnGeneraGratificaciones(mySelectionGrid[0])'>CALCULAR GRATIFICACIONES</a></li>
                          <li><a href="" ng-click='btnGeneraCTS(mySelectionGrid[0])'>CALCULAR CTS</a></li>
                          <li><a href="" ng-click='btnActualizarJSON()'>ACTUALIZAR CONCEPTOS</a></li>
                          <li class="divider"></li>
                          <li><a href="" ng-click='btnCierre()' style="color:red !important;" >CERRAR PLANILLA</a></li>
                      </ul>
                    </div>
                  </li>
                  <li class="pull-right" ng-if="mySelectionGrid.length && mySelectionGrid[0].estado_pl == 2">
                    <button type="button" class="btn btn-info" ng-click="btnAsientosContables();"> 
                      <i class="fa fa-eye"> </i> ASIENTOS CONTABLES 
                    </button> 
                  </li>

                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>

        <div class="col-md-12" ng-show="viewEmpleados" >
              <div class="panel panel-danger" >
                <div class="panel-heading">
                  <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                  <h2>Gestión de Empleados en planilla</h2> 
                </div>
                <div class="panel-editbox" data-widget-controls=""></div>
                <div class="panel-body">
                <div class="col-md-3 col-sm-6 col-xs-12 p-n">
                  <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ mySelectionGrid[0].descripcion_empresa }} </strong> </h4> 
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 p-n">
                  <h4 class="m-xs"> PLANILLA <strong style="font-weight: 100;" class="text-info"> : {{ mySelectionGrid[0].descripcion }} </strong> </h4> 
                </div>
                <ul class="form-group demo-btns col-md-6 col-xs-12 clear">
                  <li class="pull-left">
                    <button type="button" class="btn btn-info" ng-click='btnToggleEmplFiltering()'>BUSCAR</button>
                  </li>    
                </ul>
                <ul class="form-group demo-btns col-md-6 col-xs-12">
                  <li class="pull-right">
                    <button type="button" class="btn btn-warning" ng-click='changeViewEmpleado(false)'>REGRESAR</button>
                  </li>
                  <li class="pull-right">
                    <button type="button" class="btn btn-info" ng-click='getPaginationEmplServerSide(true)'>
                      <i class="fa fa-refresh"></i>
                    </button>
                  </li>
                  <li class="pull-right" ng-if="fSessionCI.key_group == 'key_sistemas'">
                    <button type="button" class="btn" ng-click='btnVariablesLey()'>VARIABLES DE LEY</button>
                  </li>
                  <!-- <li class="pull-right">
                    <button type="button" ng-if="mySelectionGrid[0].estado_pl == 1"   ng-disabled="mySelectionEmplGrid.length < 1" class="btn btn-success" ng-click='calcularPlanilla()'>CALCULAR</button>
                  </li> -->
                  <li class="pull-right" ng-if="fSessionCI.key_group == 'key_sistemas'">
                    <button type="button" class="btn btn-primary" ng-click='btnImprimirboleta()' ng-disabled="mySelectionEmplGrid.length < 1">IMPRIMIR BOLETA</button>
                  </li>
                  <!-- <li class="pull-right" ng-if="mySelectionGrid[0].estado_pl == 1 && mySelectionEmplGrid.length == 1">
                    <button type="button" class="btn btn-info" ng-click='btnImprimirboleta()'>CALCULAR LIQUIDACION</button>
                  </li> -->
                  <li class="pull-right ng-scope" ng-if="mySelectionEmplGrid.length > 0 && mySelectionGrid[0].estado_pl == 1 ">
                    <div class="btn-group open">
                      <button data-toggle="dropdown" class="btn btn-info dropdown-toggle" type="button" aria-expanded="true">
                          OPERACIONES <span class="caret"></span>
                      </button>
                      <ul role="menu" class="dropdown-menu">
                          <li><a href="" ng-click='calcularPlanilla()' style="color: #7cb342 !important;">CALCULAR</a></li>
                          <li class="divider"></li>
                          <li><a href="" ng-click="calcularPlanilla('liquidacion')" style="color:red !important;" >CALCULAR LIQUIDACION</a></li>
                      </ul>
                    </div>
                  </li>
                </ul>
                  <div ui-grid="gridOptionsEmpl" 
                        ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-pinning ui-grid-resize-columns ui-grid-move-columns 
                        class="grid table-responsive"></div> 
                </div>
              </div>
        </div>
    </div>
</div>