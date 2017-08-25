<link type="text/css" href="assets/plugins/iCheck/skins/square/_all.css" rel="stylesheet">
<link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Programación Asistencial</li>
  <li class="active">Programación de Médicos</li>
</ol>
<div class="container-fluid" ng-controller="progMedicoController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}' ng-show="confirmacion">
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Programación de Médicos</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <div class="row">
                  
                  <div class="col-xs-12">
                    <div class="row">
                      <div class="col-md-2 col-sm-12">
                        <div class="col-xs-12 p-n mb-xs"> 
                          <h3 class="m-n" style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"> <strong style="color: #8bc34a;"> SEDE: {{ fSessionCI.sede }} </strong> </h3>
                        </div>
                        <div class="col-xs-12 p-n m-n">
                          <div class="input-group" style="width: 100%;"> 
                            <input tabindex="110" type="text" placeholder="Desde" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 48%;" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
                            <div style="width: 4%; height: 30px; float: left;"></div>
                            <input tabindex="110" type="text" placeholder="Hasta" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 48%;" data-inputmask="'alias': 'dd-mm-yyyy'" 
                              ng-if="fBusqueda.tipoPlaning == 'VD'" />
                          </div>
                        </div>
                        <div class="col-xs-12 p-n m-n">
                          <uib-datepicker style="width: 100%;" class="date-table full-width" ng-model='fBusqueda.activeDate' select-range='false' 
                            ng-click="updateGridProgramas();" ng-change="cambiarFechas();" required > 
                          </uib-datepicker>
                        </div>
                        <div class="col-xs-12 p-n mb-xs mt-xs"> 
                          <button type="button" class="btn btn-info btn-block" ng-click="listarPlaningMedicos();">PROCESAR</button>
                        </div>
                        <div class="col-xs-12 p-n mb-xs">
                          <button type="button" class="btn btn-{{fToggle.colorHora}}" ng-click="verPlaningPorHora();" style="width: 49%;">{{ fToggle.textoHora }}</button>
                          <button type="button" class="btn btn-{{fToggle.colorDia}}" ng-click="verPlaningPorDia();" style="width: 49%;">{{ fToggle.textoDia }}</button>
                        </div> 
                        
                        <div class="col-xs-6 p-n mb-xs" style="margin-top:5px"> 
                          <div class="input-group" style="width:100%;" > 
                             <strong class="control-label mb-n">ESTADO: </strong>
                             <select class="form-control input-sm" ng-model="fBusqueda.itemEstado"
                               ng-change="listarPlaningMedicos();"
                               ng-options="item.descripcion for item in fArr.listaEstado ">
                            </select>                            
                          </div>
                        </div>

                        <!-- <div class="col-xs-6 p-n mb-xs" style="margin-top:5px" ng-if="fBusqueda.tipoPlaning == 'VD'"> 
                          <div class="input-group" style="width:100%;" > 
                             <strong class="control-label mb-n">AMBIENTES: </strong>
                             <select class="form-control input-sm" ng-model="fBusqueda.itemAmbiente"
                               ng-change="listarPlaningMedicos();"
                               ng-options="item.descripcion for item in fArr.listaCategoriaConsul " 
                               ng-if="fBusqueda.tipoPlaning == 'VD'">
                            </select>                            
                          </div>
                        </div> -->
                        <div class="col-xs-6 p-n mb-xs" style="margin-top:5px">
                          <div class="input-group" style="width:100%;" > 
                            <strong class="control-label mb-n">AMBIENTES: </strong> 
                            <select class="form-control input-sm" ng-model="fBusqueda.filtroAmbientes" 
                              ng-change="listarPlaningMedicos();"
                              ng-options="item as item.descripcion for item in fArr.listaMostrarTodosPl" tabindex="1"> 
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-12 p-n mb-xs" style="margin-top:5px" ng-if="fBusqueda.tipoPlaning == 'VD'"> 
                          <div class="input-group" style="width:100%;" > 
                             <strong class="control-label mb-n">MÉDICO: </strong>
                             <input type="text" ng-model="fBusqueda.medico" class="form-control input-sm" autocomplete="off"
                               placeholder="Digite el Médico para autocompletar" 
                                    typeahead-loading="loadingLocations" 
                                    uib-typeahead="item as item.medico for item in getMedicoBusquedaAutocomplete($viewValue)" 
                                    typeahead-min-length="2" 
                                    typeahead-on-select="getSelectedMedicoBusqueda($item, $model, $label)"
                                    ng-change="fBusqueda.itemMedico = null;" />                            
                          </div>
                        </div>
                        <div class="col-xs-12 p-n mb-xs" style="margin-top:5px " ng-if="fBusqueda.tipoPlaning == 'VD'"> 
                          <div class="input-group" style="width:100%;" > 
                             <strong class="control-label mb-n">ESPECIALIDAD: </strong>
                             <input type="text" ng-model="fBusqueda.especialidad" class="form-control input-sm" autocomplete="off"
                               placeholder="Digite Especialidad para autocompletar" 
                                    typeahead-loading="loadingLocations" 
                                    uib-typeahead="item as item.descripcion for item in getEspecialidadBusquedaAutocomplete($viewValue)" 
                                    typeahead-min-length="2" 
                                    typeahead-on-select="getSelectedEspecialidadBusqueda($item, $model, $label)"
                                    ng-change="fBusqueda.itemEspecialidad = null" />                            
                          </div>
                        </div>
                        <div class="col-xs-12 p-n mb-xs"> 
                          <button type="button" class="btn btn-success btn-block" ng-click='loadModalSelectTipoAtencion()'>Nuevo</button> 
                        </div>
                        <div class="col-xs-12 p-n mb-xs"> 
                          <button type="button" class="btn btn-primary btn-block" ng-click="verTodasProgramaciones();">VER LISTADO</button>
                        </div>
                        <div class="col-xs-12 p-n mb-xs"> 
                          <button class="btn btn-info btn-block" ng-click="btnExportarExcel();"><i class="fa fa-file-excel-o"></i> EXPORTAR A EXCEL</button>
                        </div>
                      </div>

                       <!-- Btn Filtrar por Tipo Atención -->
                      <div class="form-group mb-md col-md-10 col-sm-12 mb-n pl-n" >
                        <div class="checkbox icheck m-n inline">
                          <label> <span style="padding:2px 16px; background:#00bcd4; color: white"> CONSULTAS </span>
                            <input icheck="square-blue" type="checkbox" ng-init='fBusqueda.consultas = true' ng-model="fBusqueda.consultas" ng-change="filtrarConsProc(fBusqueda.consultas, 'CM')">
                          </label>
                        </div>
                        <div class="checkbox icheck m-n inline">
                          <label> <span style="padding:2px 16px; background:#8D54BC; color: white"> PROCEDIMIENTOS </span>
                            <input icheck="square-purple" type="checkbox" ng-init='fBusqueda.procedimientos = true' ng-model="fBusqueda.procedimientos" ng-change="filtrarConsProc(fBusqueda.procedimientos, 'P')" >
                          </label>
                        </div>
                      </div>

                      <!-- planning por días -->
                      <div class="col-md-10 col-sm-12 pl-n demo-icheck" ng-if="fBusqueda.tipoPlaning == 'VD'">
                        <div class="planning-medicos" >
                          <div class="wrapper-fixed-row">
                            <div class="fixed-row">
                              <div class="cell-planing {{ fila.clase }}" ng-repeat="fila in fPlanning.data.cabecera">
                                <div class="cell-section"> 
                                  {{ fila.formatFecha }} 
                                  <p class="m-n"> {{ fila.mesAbv }} </p>
                                </div> 
                              </div>
                            </div>
                          </div>
                          <div class="body-table"> 
                            <div class="wrapper-fixed-column">
                              <div class="fixed-column">
                                <div class="cell-inline" ng-repeat="fila in fPlanning.data.cuerpo">
                                  <div class="cell-planing ambiente" >
                                    <div class="cell-section"> {{ fila.numero }} <span class="badge {{ fila.classTag }}">{{ fila.tag }}</span></div> 
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="relative-column" scroller >
                              <div class="cell-block" ng-repeat="(indexFila, fila) in fPlanning.data.cuerpo">
                                <div class="cell-planing {{filaDet.class_feriado}}" ng-repeat="(indexFilaDet, filaDet) in fila.cell" ng-click="loadModalSelectTipoAtencion(filaDet, 'dia', fila);"> 
                                  <div class="cell-section"> 
                                    <div class="cell-text" ng-repeat="(indexSection,section) in filaDet.section.CM"> 
                                      <a href="" ng-class="{'label':true, 'label-info':true, 'opaco-disabled': !section.prog_activa}" ng-click="verProgramacion(fila,filaDet,section); $event.stopPropagation();">{{ section.especialidad }} ({{section.total_prog}}) </a> 
                                     
                                    </div> 
                                    <div class="cell-text" ng-repeat="(indexSection,section) in filaDet.section.P"> 
                                      <a href="" ng-class="{'label':true, 'label-info-p':true, 'opaco-disabled': !section.prog_activa}" ng-click="verProgramacion(fila,filaDet,section); $event.stopPropagation();">{{ section.especialidad }} ({{section.total_prog}}) </a> 
                                     
                                    </div>
                                  </div> 
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="clearfix"></div> 
                        </div>
                      </div>

                      <!-- planning por horas -->
                      <div class="col-md-10 col-sm-12 pl-n" ng-if="fBusqueda.tipoPlaning == 'VH'">
                        <div class="planning-medicos" >
                          <div>
                            <div class="planning" >
                              <div class="hora-header">
                                H./AMB.
                              </div>
                              <div class="header">
                                <table class="table table-bordered">
                                  <thead>
                                     <tr>
                                      <th ng-repeat="ambiente in planning.ambientes" class="item-ambiente">
                                        <div class="cell-ambiente">{{ambiente.dato}} <span class="badge {{ ambiente.classTag }}">{{ ambiente.tag }}</span></div>
                                      </th>
                                    </tr> 

                                  </thead>                    
                                </table>
                              </div>

                              <aside class="sidebar">
                                <table class="table table-bordered">
                                  <tbody>
                                    <tr ng-repeat="hora in planning.horas" >
                                      <td class="{{hora.class}}">
                                        <div>{{hora.dato}}</div>
                                      </td>
                                    </tr>                  
                                  </tbody>                    
                                </table>
                              </aside>
                              <div class="body" scroller >
                                <table class="table table-bordered">
                                  <tbody>
                                    <tr ng-repeat="grid in planning.gridTotal"  >
                                      <td ng-repeat="item in grid" class="{{item.class}} " ng-click="verificarCrearNuevoHoras(item);" rowspan="{{item.rowspan}}" style="height:{{item.rowspan*40}}px;" ng-if="!item.unset" >
                                          <div class="" >
                                            <a href="" ng-class="{'label':true, 'label-info':item.tipoAtencion == 'CM', 'label-info-p':item.tipoAtencion == 'P', 'opaco-disabled': !item.activo}" ng-click="verProgramacionHora(item); $event.stopPropagation();">{{item.dato}}  </a>
                                        </div>
                                      </td >                      
                                    </tr>                      
                                  </tbody>                    
                                </table>              
                              </div> 
                            </div>
                          </div>
                          <div class="clearfix"></div> 
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