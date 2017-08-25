<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body" style="top: -10px;">  
    <form class="row" name="formProgMedico"> 
      <div class="form-group col-md-12 mb-n">
        <label class="control-label mb-n">PROFESIONAL: </label> 
      </div>
      <div class="form-group col-md-1 mb-n pr-n">
        <input type="text" class="form-control" ng-model="fData.idmedico" tabindex="1"  readonly="true"  />
      </div>
      <div class="form-group col-md-8 mb-n pr-n">
        <input type="text" id="fDataMedico" ng-model="fData.medico" class="form-control" focus-me autocomplete="off"
         placeholder="Digite el Médico para autocompletar" 
              typeahead-loading="loadingLocations" 
              uib-typeahead="item as item.descripcion for item in getMedicoAutocomplete($viewValue)" 
              typeahead-min-length="2" 
              typeahead-on-select="getSelectedMedico($item, $model, $label)"
              ng-change="fData.empresa =null; fData.especialidad=null; fData.idmedico = null; noResultsLM = false" /> 
        <i ng-show="loadingMedico" class="fa fa-refresh"></i>
        <div ng-show="noResultsLM">
            <i class="fa fa-remove"></i> No se encontró resultados 
        </div> 
      </div>
      <div class="form-group col-md-3 mb-n pl-n">
        <button type="button" class="btn btn-info" ng-click="btnBuscar()" style="width: 100%;">BUSCAR PROFESIONAL</button>
      </div> 
      <div class="form-group col-md-12 m-n mt-xs">
        <label class="control-label mb-n">EMPRESA: </label>
        <p class="help-inline m-n"> {{fData.empresa}} </p>
      </div>
      <div class="form-group col-md-12 m-n mt-n mb-sm">
        <label class="control-label mb-n">SERVICIO: </label>
        <p class="help-inline m-n"> {{fData.especialidad}} </p>
      </div>

      <div class="form-group  col-md-4 mb-md" id="sectionAmbiente"> 
        <div class="form-group col-md-3 mb-n" style=" padding-left: initial;" > 
          <label class="control-label mb-xs" > AMBIENTE </label> 
        </div> 
        <div class="form-group col-md-9 mb-n" style="padding-right: initial;">  
          <input type="text" class="form-control input-sm" placeholder="Filtrar ambientes..." ng-model="fData.searchAmb" autocomplete="off" />
        </div> 
        <select name="ambienteSelect" id="repeatSelect" style="width:100%; margin-top: 30px;height:150px;"  size="5"
                class="form-control input-sm animate-repeat" ng-model="fData.ambiente"
                ng-change="updateGridProgramas()" ng-click=" activarRenombrar(item)"
           ng-options="item.descripcion_amb for item in fData.listaAmbienteSede | filter:fData.searchAmb " >
        </select> 

        <div class="input-group" style="margin-top:10px;"> 
          <input type="checkbox" ng-model="fData.renombrar" ng-disabled="fData.habilitado" > ¿Renombrar? 
        </div>
        <div class="form-group col-mn-12 mb-n">
          <select name="catAmbienteSelect" size="1" style="margin-top:10px;width:100%;" class="form-control input-sm" ng-model="fData.categoriaConsul" 
             ng-show=" fData.renombrar" ng-required="fData.renombrar" disabled
             ng-options="item.descripcion for item in fData.listaCategoriaConsul" ng-change="getCargaSubCategoriaConsul(fData.categoriaConsul);" tabindex="2">
          </select> 

          <select name="subcatAmbienteSelect" size="1" style="margin-top:10px;width:100%;" class="form-control input-sm" ng-model="fData.subCategoriaConsul" 
              ng-show=" fData.renombrar" ng-required="fData.renombrar"
              ng-options="item.descripcion for item in fData.listaSubCategoriaConsul" tabindex="2"></select>           
        </div>
      </div>

      <div class="form-group col-md-4 mb-md" id="sectionCalendar">
        <label class="control-label mb-xs"> DIAS </label> 
        <uib-datepicker style="width: 100%;" class="date-table fullWidth" ng-model='fData.activeDate' multi-select='fData.arrFechas' 
          select-range='false' 
          ng-click="updateGridProgramas()"
          date-disabled="disabled(date, mode)" >    
        </uib-datepicker>
      </div>

      <div class="form-group  col-md-4 mb-n" id="sectionTurno"> 
        <label class="control-label mb-xs"> HORARIOS </label> 
        <select name="turnosSelect" size="9" style="width:100%" multiple 
              ng-model="fData.arrHoras" 
              ng-change="updateGridProgramas()"
              ng-options="option as option.hora_formato for option in fData.listaHoras" >
        </select>
      </div>

      <div class="form-group col-xs-12 mb-md " ng-show="fData.alertaAmbientes">
        <div class="block" ng-repeat="item in fData.alertaAmbientesMsj ">
          <span class="text-warning" ><i class="fa fa-exclamation-circle"></i> {{item.value}}</span>
        </div>
      </div>

      <div class="form-group  col-md-12 mb-md " id="sectionTurno"> 
        <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-edit ui-grid-pagination class="grid table-responsive fs-mini-grid"></div>
      </div>
      <div class="form-group col-md-8 mb-n">
        <label class="control-label mb-n"> Comentario: </label>
        <textarea class="form-control " ng-model="fData.comentario" placeholder="Ingrese comentario" tabindex="1" ></textarea>
      </div>
      <div class="form-group col-md-4 mb-n">
        <label class="control-label mb-n"> Estado: </label>
        <select class="form-control input-sm" ng-model="fData.activoRegistro" ng-options="item.descripcion for item in listaEstadosRegistro">
        </select>        
      </div>      
    </form>
  </div>
    
  <div class="modal-footer">
      <button class="btn btn-primary" ng-click="btnGuardar(false); $event.preventDefault();" ng-disabled="formProgMedico.$invalid ">Guardar</button>
      <button class="btn btn-primary" ng-click="btnGuardar(true); $event.preventDefault();" ng-disabled="formProgMedico.$invalid">Guardar y Limpiar</button>
      <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
  </div>
</div>