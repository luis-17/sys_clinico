<style type="text/css">
	#formSolicitud ul{width: 300px!important;}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSolicitud" id="formSolicitud"> 
		<div class="form-group col-md-6 mb-md">
			<label class="control-label mb-xs"> Profesional Médico <small class="text-danger">(*)</small></label>
			<div class="input-group">
				<span class="input-group-btn ">
					<input type="text" class="form-control input-sm" style="width:45px;text-align:right;margin-right:4px;" ng-model="fData.idmedico" placeholder="ID" tabindex="101" ng-enter="obtenerMedicoPorCodigo(); $event.preventDefault();" ng-readonly="true" required="true" />
				</span>
				<input id="fDataMedico" type="text" class="form-control input-sm" ng-model="fData.medico" placeholder="Ingrese el medico" typeahead-loading="loadingMedico" uib-typeahead="item as item.medico for item in getMedicoAutocomplete($viewValue)" typeahead-on-select="getSelectedMedico($item, $model, $label)" typeahead-min-length="2" tabindex="102" ng-change="fData.idmedico = null; noResultsLM = false"/>
			</div>
			<i ng-show="loadingMedico" class="fa fa-refresh"></i>
            <div ng-show="noResultsLM">
              <i class="fa fa-remove"></i> No se encontró resultados 
            </div>
		</div>
		<div class="form-group col-md-6 mb-md">
			<label class="control-label mb-xs"> Examen Auxiliar <small class="text-danger">(*)</small></label>
			<div class="input-group">
				<span class="input-group-btn ">
					<input type="text" class="form-control input-sm" style="width:45px;text-align:right;margin-right:4px;" ng-model="fData.idproductomaster" placeholder="ID" tabindex="103" ng-enter="obtenerExamenPorCodigo(); $event.preventDefault();" ng-readonly="true" required="true"/>
				</span>
				<input id="fDataExamen" type="text" class="form-control input-sm" ng-model="fData.examen_auxiliar" placeholder="Ingrese el Examen" typeahead-loading="loadingExamen" uib-typeahead="item as item.descripcion for item in getExamenAutocomplete($viewValue)" typeahead-on-select="getSelectedExamen($item, $model, $label)" typeahead-min-length="2" tabindex="104" ng-change="fData.idproductomaster = null; noResultsLE = false"/>
			</div>
			<i ng-show="loadingExamen" class="fa fa-refresh"></i>
            <div ng-show="noResultsLE">
              <i class="fa fa-remove"></i> No se encontró resultados 
            </div>
		</div>
		<div class="form-group col-md-6 mb-md">
			<label class="control-label mb-xs"> Historia / Paciente <small class="text-danger">(*) <i class="fa fa-info-circle text-gray" uib-tooltip="Ingrese el Nº de Historia y pulse Enter, o seleccione al paciente luego de escribir parte de su nombre o apellido"></i></small></label>
			<div class="input-group">
				<span class="input-group-btn ">
					<input type="text" class="form-control input-sm" style="width:45px;text-align:right;margin-right:4px;" ng-model="fData.idhistoria" placeholder="Hist." tabindex="103" ng-enter="obtenerClientePorHistoria();" ng-change="fData.cliente = null; noResultsLC = false" required="true"/>
				</span>
				<input id="fDataCliente" type="text" class="form-control input-sm" ng-model="fData.cliente" placeholder="Ingrese el Paciente" typeahead-loading="loadingPaciente" uib-typeahead="item as item.descripcion for item in getPacienteAutocomplete($viewValue)" typeahead-on-select="getSelectedPaciente($item, $model, $label)" typeahead-min-length="2" tabindex="104" ng-change="fData.idhistoria = null; noResultsLC = false" required="true"/>
			</div>
			<i ng-show="loadingPaciente" class="fa fa-refresh"></i>
            <div ng-show="noResultsLC">
              <i class="fa fa-remove"></i> No se encontró resultados 
            </div>
		</div>
		<div class="form-group col-md-6 mb-md">
			<label class="control-label mb-xs"> Fecha <small class="text-danger">(*)</small></label>
	        <input tabindex="100" type="text" class="form-control input-sm mask" ng-model="fData.fecha_solicitud" data-inputmask="'alias': 'dd-mm-yyyy'" />
		</div>
		<div class="form-group col-md-12 mb-md">
			<label class="control-label mb-xs"> Indicaciones <small class="text-danger">(*)</small></label>
			<textarea class="form-control input-sm" ng-model="fData.indicaciones" tabindex="105" required="true"></textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formSolicitud.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>