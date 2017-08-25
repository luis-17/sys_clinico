<ol class="breadcrumb m-n">
  <li>RR.HH</li>
  <li class="active">Empleados</li>
</ol>
<div class="container-fluid" ng-controller="empleadoController" ng-init="initEmpleado();">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Empleados </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button style="margin-top: -6px;" class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>

                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevoEmpleado()'>Nuevo</button></li>
                    
                    <li > <select ng-change="getPaginationServerSide();" class="form-control" ng-model="fBusqueda.tercero" ng-options="item.id as item.descripcion for item in listaTercero" > </select> </li>
                    <li > <select ng-change="getPaginationServerSide();" class="form-control" ng-model="fBusqueda.activo" ng-options="item.id as item.descripcion for item in listaActivo" > </select> </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_activo.bool == 1" ><button type="button" class="btn btn-danger" ng-click='btnDarBaja();'>Dar de Baja</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_activo.bool == 2" ><button type="button" class="btn btn-success" ng-click='btnRevertirBaja();'>Revertir Baja</button></li> -->
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar("xlg");'>Editar</button></li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-info" ng-click='btnVerHistorialContratos();'>CONTRATOS...</button></li> -->
                   <!--  <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-info" ng-click='btnVerFichaPdf();'>Ver Ficha</button></li> -->
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1">
                      <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
                            REPORTES <span class="caret"></span>
                        </button>
                        <ul role="menu" class="dropdown-menu">
                            <li><a href="" ng-click="btnAvanceProfesionalEmpleado();">AVANCE PROFESIONAL</a></li>
                            <li><a href="" ng-click="btnVerFichaPdf();">VER FICHA</a></li>
                            <!-- <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li> -->
                            <li class="divider"></li>
                            <!-- <li><a href="#">Separated link</a></li> -->
                        </ul>
                      </div>
                    </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1">
                      <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" type="button">
                            MAS OPCIONES <span class="caret"></span>
                        </button>
                        <ul role="menu" class="dropdown-menu">
                            <li><a href="" ng-click="btnVerHistorialContratos();">CONTRATOS...</a></li>
                            <li><a href="" ng-click="btnVerVacaciones();">VACACIONES...</a></li>
                            <li><a href="" ng-click="btnSubirCV();">SUBIR CV</a></li>
                            <!-- <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li> -->
                            <li class="divider"></li>
                            <!-- <li><a href="#">Separated link</a></li> -->
                            <li><a href="" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_activo.bool == 1" ng-click="btnConfirDarBaja();">DAR DE BAJA</a></li>
                            <li><a href="" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_activo.bool == 2" ng-click="btnRevertirBaja();">REVERTIR BAJA</a></li>
                        </ul>
                      </div>
                    </li>
                    
                </ul>
                <div class="row">
                  <div class="col-xs-12"> 
                    <div  ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-pinning ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>