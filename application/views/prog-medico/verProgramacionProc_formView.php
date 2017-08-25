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
            <div class="p mb" style="border: 1px solid #e1e1e1;min-height: 310px;"> 
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
 
              <div class="col-xs-12 p-n">
                <div class="block pull-right">
                  <div class="btn-group" ng-show='!esReprogramacion'>
                      <button type="button" class="btn btn-default-alt dropdown-toggle" data-toggle="dropdown">
                         </i> MAS OPCIONES <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                          <li><a ng-hide='!esReprogramacion && fila.estado_prm == 2' href="" style="color: red;" ng-click='anularProgramacion(fila);'>ANULAR PROG. </a></li>
                          <li><a href="" ng-click='verListaPacientesProc(fila);'>VER LISTA PACIENTES </a></li> 
                      </ul>
                  </div> 
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