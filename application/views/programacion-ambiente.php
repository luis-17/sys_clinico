<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Programación Asistencial</li>
  <li class="active">Programación de Ambientes Físicos</li>
</ol>
<div class="container-fluid" ng-controller="programacionAmbienteController">
  <div class="row">
      <div class="col-md-12">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
              <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
              <h2>Programación de Ambientes Físicos</h2>
            </div>
            <div class="panel-editbox" data-widget-controls=""></div>
            <div class="panel-body">
              <div class="row">              
                <div class="demo-btns col-md-12 " style="border-bottom: 1px solid #e4e1e3; border-color: #e4e1e3; padding-bottom: 6px;"> 
                  <form  name="formPlanningMedico">
                    <ul class="m-xs demo-btns ">
                      <li class="form-group m-xs" >                     
                        <div class="input-group">
                        <strong>Sede: 
                          <select style="height: 26px;" ng-change="" class="" ng-model="fBusqueda.sede" ng-options="item as item.descripcion for item in listaSede"> </select>
                          <!-- <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacen(fBusqueda.idalmacen);" class="" ng-model="fBusqueda.idalmacen" ng-options="item.id as item.descripcion for item in listaAlmacen"> </select>  -->
                        </strong>
                        </div>
                      </li>

                      <li class="form-group m-xs" > 
                        <div class="input-group">
                          <input type="text" placeholder="Desde"  class="form-control datepicker" 
                                          uib-datepicker-popup="{{dateUIDesde.format}}" popup-placement="auto right-top"
                                          ng-model="fBusqueda.desde" is-open="dateUIDesde.opened" 
                                          datepicker-options="dateUIDesde.datePikerOptions" ng-required="true" 
                                          close-text="Cerrar" alt-input-formats="altInputFormats"
                                           />
                          <span class="input-group-btn">
                            <button type="button" class="btn btn-default " ng-click="dateUIDesde.openDP($event)"><i class="ti ti-calendar"></i></button>
                          </span>
                        </div>           
                      </li>

                      <li class="form-group m-xs " ng-show="mostrar_fecha2">
                        <div class="input-group">
                          <input type="text" placeholder="Hasta" class="form-control datepicker" 
                                          uib-datepicker-popup="{{dateUIHasta.format}}" popup-placement="auto top"
                                          ng-model="fBusqueda.hasta" is-open="dateUIHasta.opened" 
                                          datepicker-options="dateUIHasta.datePikerOptions"  ng-required="mostrar_fecha2"
                                          close-text="Cerrar" alt-input-formats="altInputFormats" 
                                           />
                          <div class="input-group-btn">
                            <button type="button" class="btn btn-default" ng-click="dateUIHasta.openDP($event)" tabindex="4"><i class="ti ti-calendar"></i></button>
                          </div>
                        </div>             
                      </li> 
                      
                      <li class="form-group m-xs " > 
                        <div class="input-group">
                          <button type="button" class="btn btn-success" ng-click="btnProcesar(); $event.preventDefault();" ng-disabled="formPlanningMedico.$invalid " ><i class="fa fa-refresh"></i> PROCESAR</button>
                        </div>
                      </li>
                      <li class="form-group m-xs pull-right"> 
                          <div class="btn-group-btn " >
                              <button type="button" resetscroller class="btn btn-{{color_clase_dia}} " ng-click="verDia();">VISTA POR DÍAS</button>
                              <button type="button" resetscroller class="btn btn-{{color_clase_mes}} mr-sm" ng-click="verMes();">VISTA POR HORAS</button>
                              <button type="button" class="btn btn-warning mr-sm" ng-click="btnEditar();"><i class="fa fa-edit"></i> EDITAR</button>
                              <button type="button" class="btn btn-success" ng-click="btnNuevo();"><i class="fa fa-file"></i> NUEVO</button>
                          </div>
                      </li>
                    </ul>
                  </form>
                </div>               
              </div>
              <div class="row" style="  min-height: 400px; ">
                <div class="col-xs-12" ng-show="ver_planning1">
                  <div class="planning" id="planning-amb">
                    <div class="amb-header">
                      AMB./DÍAS
                    </div>
                    <div class="header">
                      <table class="table">
                        <thead>
                          <tr>
                            <th ng-repeat="fecha in planning.header" class="{{fecha.class}}">
                              <div>{{fecha.formatFecha}}<p class="m-n"> {{fecha.mesAbv}} </p></div>
                            </th>
                          </tr>                      
                        </thead>                    
                      </table>
                    </div>

                    <aside class="sidebar">
                      <table class="table">
                        <tbody>
                          <tr ng-repeat="ambiente in planning.ambientes"  >
                            <td  class="item-ambiente">
                              <div class="cell-ambiente">{{ambiente.dato}}<span class="badge {{ ambiente.classTag }}">{{ ambiente.tag }}</span></div>
                            </td>
                          </tr>                      
                        </tbody>                    
                      </table>
                    </aside>

                    <div class="body" scroller >
                      <table class="table table-bordered">
                        <tbody>
                          <tr ng-repeat="grid in planning.gridTotal"  >
                            <td ng-repeat="item in grid" class="{{item.class}} {{item.tipo_evento}}" ng-click="verDetalleAmbiente(item)"  >
                                <div class="label"> {{item.dato}}</div>
                            </td >                      
                          </tr>                      
                        </tbody>                    
                      </table>              
                    </div> 
                  </div>
                </div>

                <div class="col-xs-12 " ng-show="ver_planning2">
                  <div class="planning" id="planning-amb">
                    <div class="hora-header">
                      HORAS
                    </div>
                    <div class="header">
                      <table class="table table-bordered">
                        <thead>
                           <tr>
                            <th ng-repeat="ambiente in planning.ambientes" class="item-ambiente">
                              <div class="cell-ambiente">{{ambiente.dato}}<span class="badge {{ ambiente.classTag }}">{{ ambiente.tag }}</span></div>
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
                            <td ng-repeat="item in grid" class="{{item.class}} {{item.tipo_evento}}" >
                                <div class="label" uib-tooltip="{{item.comentario}} - Responsable: {{item.responsable}}" 
                                  tooltip-placement="bottom"
                                  tooltip-enable="item.tooltip">
                                  {{item.dato}} 
                                  <span class="fa fa-info-circle" ng-show="item.tooltip" ></span>
                              </div>
                            </td >                      
                          </tr>                      
                        </tbody>                    
                      </table>              
                    </div> 

                  </div>
                </div>
              </div>
            </div>
          </div>
          
      </div>
  </div>
</div>