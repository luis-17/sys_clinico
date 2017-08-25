<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>
  <div class="modal-body">  
    <form name="formProgMedico"> 
      <div class="row" ng-repeat="(index,fila) in fArr.listaProgramaciones"> 
        <div class="col-md-12 mb">
          <button type="button" class="btn btn-default btn-block" ng-click="isCollapsed = !isCollapsed"> TURNO {{ fila.turno }} <i class="fa fa-sort-down ml" style="position: absolute;"></i> </button> 
        </div>
        <div class="col-md-12">
          <div uib-collapse="isCollapsed">
            <div class="p mb" style="border: 1px solid #e1e1e1;min-height: 400px;"> 
              <div class="col-md-2 p-n">
                <strong class="control-label mb-n"> TIPO DE ATENCIÓN: </strong>
                <p class="help-block m-n"> {{ fila.tipo_atencion_medica }} </p>
              </div>
              <div class="col-md-4 p-n" ng-show='!esReprogramacion'>
                <strong class="control-label mb-n"> PROFESIONAL: </strong>
                <p class="help-block m-n"> {{ fila.medico }} </p> 
              </div>

              <div class="col-md-4 p-n" ng-show='esReprogramacion'>
                <strong class="control-label mb-n"> PROFESIONAL: </strong>
                <input type="text" ng-model="fila.medico" class="form-control" focus-me autocomplete="off"
                      placeholder="Digite el profesional para autocompletar" 
                      typeahead-loading="loadingLocations" 
                      uib-typeahead="item as item.descripcion for item in getMedicoAutocomplete($viewValue, true, fila.idespecialidad)" 
                      typeahead-min-length="2" 
                      typeahead-on-select="getSelectedMedicoReprogramacion($item, $model, $label, index)"
                      ng-change="noResultsLM = false" /> 
                  <i ng-show="loadingMedico" class="fa fa-refresh"></i>
                  <div ng-show="noResultsLM">
                      <i class="fa fa-remove"></i> No se encontró resultados 
                  </div>
              </div>

              <div class="col-md-3 p-n">
                <strong class="control-label mb-n"> EMPRESA: </strong> 
                <p class="help-block m-n"> {{ fila.empresa }} </p>
              </div>
              <div class="col-md-3 p-n">
                <strong class="control-label mb-n"> SERVICIO: </strong>
                <p class="help-block m-n"> {{ fila.especialidad }} </p>
              </div>
              <div class="col-xs-12"></div> 
              <div class="col-sm-3 p-n">
                <strong class="control-label mb-n"> AMBIENTE: </strong>
                <p class="help-block m-n"> 
                  <select ng-change="switchRenombrado(index);" style="height: 152px;" size="5" focus-me class="form-control input-sm" ng-model="fila.ambiente" ng-options="item as item.descripcion for item in fila.ambientes" tabindex="1"> 
                  </select> 
                </p>
              </div>
              <div class="col-sm-3">
                <div class="row">
                  <div class="col-xs-12">
                    <strong class="control-label mb-n"> FECHA: </strong>
                    <p class="help-block m-n"> 
                      <input tabindex="110" type="text" placeholder="Desde" class="form-control input-sm mask" ng-model="fila.fecha_programada" data-inputmask="'alias': 'dd-mm-yyyy'"  />
                    </p>
                  </div>
                  <div class="col-xs-12"> 
                    <input type="checkbox" ng-model="fila.si_renombrado_scc" /> ¿Renombrar Ambiente? 
                  </div>
                  <div class="col-xs-12" ng-show="fila.si_renombrado_scc">
                    <strong class="control-label mb-n"> CATEGORIA: </strong>
                    <p class="help-block m-n"> 
                      ASISTENCIAL 
                    </p>
                  </div>
                  <div ng-show=" fila.si_renombrado_scc" class="col-xs-12">
                    <strong class="control-label mb-n"> SUBCATEGORIA: </strong>
                    <p class="help-block m-n"> 
                      <select name="subcatAmbienteSelect" size="1" class="form-control input-sm" ng-model="fila.subcategoriarenom" 
                          ng-required="fila.si_renombrado_scc" 
                          ng-options="item.descripcion for item in fila.subcategorias" tabindex="2"> 
                      </select> 
                    </p>
                  </div>
                </div> 
              </div>
              <div class="col-sm-6">
                <div class="row">
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> HORA INICIO: </strong>
                    <p class="help-block m-n input-group"> 
                      <input type="text" class="form-control input-sm" ng-pattern="/^\d+$/" ng-model="fila.hora_inicio_edit" style="width: 48px;" 
                        ng-change="calcularTurno(index,'hora_inicio');" /> 
                      <input type="text" class="form-control input-sm" ng-model="fila.minuto_inicio_edit" style="width: 48px; margin-left: 4px;" 
                        ng-change="calcularTurno(index,'hora_inicio');" /> 
                    </p>
                  </div> 
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> HORA FIN: </strong>
                    <p class="help-block m-n input-group" > 
                      <input type="text" class="form-control input-sm" ng-model="fila.hora_fin_edit" style="width: 48px;" ng-change="calcularTurno(index,'hora_fin');" /> 
                      <input type="text" class="form-control input-sm" ng-model="fila.minuto_fin_edit" style="width: 48px; margin-left: 4px;" ng-change="calcularTurno(index,'hora_fin');" /> 
                    </p>
                  </div> 
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> INTERVALO: </strong>
                    <p class="help-block m-n"> <input type="text" class="form-control input-sm" ng-model="fila.intervalo_hora_int" style="width: 100px;" ng-change="calcularTurno(index,'intervalo');" /> </p>
                  </div> 
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> CUPOS POR HORA: </strong>
                    <p class="help-block m-n"> <input type="text" class="form-control input-sm" ng-model="fila.cupos_por_hora" style="width: 100px;" ng-change="calcularTurno(index,'cupos_por_hora');" /> </p>
                  </div> 
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> CANT. DE CUPOS: </strong>
                    <p class="help-block m-n"> <input type="text" class="form-control input-sm" ng-model="fila.total_cupos_master" style="width: 100px;" ng-change="calcularTurno(index,'cant_cupos');" /> </p>
                  </div> 
                  <div class="col-md-4 p-n">
                    <strong class="control-label mb-n"> CUPOS ADIC.: </strong>
                    <p class="help-block m-n"> <input type="text" class="form-control input-sm" ng-model="fila.cupos_adicionales" style="width: 100px;" /> </p>
                  </div> 
                  <div class="col-md-7 p-n">
                    <strong class="control-label mb-n"> COMENTARIO.: </strong> 
                    <p class="help-block m-n"> <textarea type="text" class="form-control input-sm" ng-model="fila.comentario"> </textarea> </p>
                  </div>
                  <div class="col-md-3 col-md-offset-1 p-n">
                    <strong class="control-label mb-n"> ESTADO.: </strong> 
                    <select class="form-control input-sm" ng-model="fila.activo" ng-options="item.id as item.descripcion for item in listaEstadosRegistro">
                    </select>
                  </div>                    
                </div>
              </div>
              <div class="col-xs-12 p-n">
                <hr class="block" /> 
              </div>
              <div class="block">
                <div class="col-md-3 text-info"> 
                  <label class="control-label mb-n"> CUPOS GENERALES: </label>
                  <p class="m-n"> {{ fila.canales[1].cupos_ocupados }} / {{ fila.canales[1].total_cupos }} </p>
                </div>
                <div class="col-md-3 text-info"> 
                  <label class="control-label mb-n"> CUPOS ADICIONALES: </label>
                  <p class="m-n"> {{ fila.canales[1].total_adi_vendidos }} / {{ fila.canales[1].cupos_adicionales }} </p>
                </div>
                <div class="col-md-3 text-info"> 
                  <label class="control-label mb-n"> TOTAL DE CUPOS: </label>
                  <p class="m-n"> {{ fila.canales[1].cupos_ocupados_todos }} / {{ fila.canales[1].todos_los_cupos }} </p>
                </div>
              </div>
              <div class="col-xs-12 p-n">
                <hr class="block" /> 
              </div>
              <div class="col-xs-12 p-n" ng-repeat="(indexCanal,fCanal) in fila.canales" style="margin-bottom: 6px;">
                <strong class="control-label mb-n"> CANAL: {{ fCanal.canal }}</strong>
                <div class="row"> 
                  
                  <div class="col-xs-12" style="margin-left: 10px; font-style: oblique; font-size: 12px;">
                    <a href="" class="" ng-click="verCuposDeCanal(fCanal,indexCanal,index);"> {{fCanal.textContCupos}} </a> 
                  </div>
                </div> 
                <div class="row" ng-show="fCanal.contCuposDeCanal"> 
                  <div class="col-xs-12">
                    <div class="table-fixed" style="margin-left: 10px; margin-top: 10px; margin-bottom: 10px;width: 100%;"> 
                      <!-- <div class="wrapper-fixed-row"> -->
                        <div class="fixed-row">
                          <div class="cell-planing" style="width: 15%">
                            <div class="cell-section"> N° DE CUPO </div> 
                          </div>
                          <div class="cell-planing" style="width: 25%"> 
                            <div class="cell-section"> CUPO </div> 
                          </div>
                          <!-- <div class="cell-planing" style="width: 25%">
                            <div class="cell-section"> HORA FIN </div> 
                          </div> -->
                          <div class="cell-planing" style="width: 20%">
                            <div class="cell-section"> INTERVALO </div> 
                          </div>
                          <div class="cell-planing" style="width: 13%">
                            <div class="cell-section"> ADICIONAL </div> 
                          </div>
                          <div class="cell-planing" style="width: 20%">
                            <div class="cell-section"> ESTADO </div> 
                          </div>
                        </div>
                      <!-- </div> -->
                      <div class="body-table"> 
                        <div class="relative-column" >
                          <div class="cell-block {{fCupo.tipoCupo}}" ng-repeat="(indexCupo, fCupo) in fCanal.cupos"><!--  -->
                            <div class="cell-planing" style="width: 15%" > 
                              <div class="cell-section"> 
                                #{{ fCupo.numero_cupo }} 
                              </div> 
                            </div>
                            <div class="cell-planing" style="width: 25%" > 
                              <div class="cell-section"> 
                                {{ fCupo.hora_inicio_det }} - {{ fCupo.hora_fin_det }}
                              </div> 
                            </div>
                            <!-- <div class="cell-planing" style="width: 25%" > 
                              <div class="cell-section"> 
                                {{ fCupo.hora_fin_det }}
                              </div> 
                            </div> -->
                            <div class="cell-planing" style="width: 20%" > 
                              <div class="cell-section"> 
                                {{ fCupo.intervalo_hora_det }}
                              </div> 
                            </div>
                            <div class="cell-planing" style="width: 12%" > 
                              <div class="cell-section" ng-bind-html="fCupo.si_adicional"></div> 
                            </div>
                            <div class="cell-planing" style="width: 18%" > 
                              <div class="cell-section" >
                                <label style="width: 120px;" class="label {{ fCupo.estado_cupo.clase }}"> {{fCupo.estado_cupo.string}} </label> 
                              </div> 
                            </div>
                          </div> <!-- -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div> 
              </div> 
              <div class="col-xs-12 p-n">
                <div class="block pull-right">
                  <!-- <button class="btn btn-danger" ng-click="anularProgramacion(index);">ANULAR</button>
                  <button class="btn btn-danger-alt" ng-click="cancelarProgramacion(index);">CANCELAR</button> -->
                  <div class="btn-group" ng-show='!esReprogramacion'>
                      <button type="button" class="btn btn-default-alt dropdown-toggle" data-toggle="dropdown">
                         </i> MAS OPCIONES <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                          <li><a ng-hide='!esReprogramacion && fila.estado_prm == 2' href="" style="color: red;" ng-click='anularProgramacion(fila);'>ANULAR PROG. </a></li>
                          <li><a ng-hide='!esReprogramacion && fila.estado_prm == 2' href="" ng-click='cancelarProgramacion(fila);'>CANCELAR PROG. </a></li>
                          <li><a href="" ng-click='verListaPacientes(fila);'>VER LISTA PACIENTES </a></li>
                      </ul>
                  </div>
                  <button class="btn btn-info" ng-click="gestionarCupos(index,fila);" ng-disabled="formProgMedico.$invalid" > GESTION CUPOS </button>
                  <button class="btn btn-success" ng-click="editarProgramacion(index);" ng-disabled="formProgMedico.$invalid"> <i class="fa fa-file-text"> </i> GUARDAR </button>
                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div> 
  <div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancel();">SALIR</button>
  </div>
</div>