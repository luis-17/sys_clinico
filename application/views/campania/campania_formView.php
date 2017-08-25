<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCampania">
    	<div class="form-group mb-md col-md-4" >
			<label class="control-label mb-xs"> Empresa / Sede </label> 
	        <div class="input-group block">
	            <select class="form-control input-sm" ng-model="fData.sedeempresa" 
	              ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" tabindex="1"> </select> 
	        </div>
		</div>
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">	Nombre de la Campaña <small class="text-danger">(*)</small> </label>
			<input type="text" ng-model="fData.campania" placeholder="Registre el nombre de la campaña"  
				typeahead-loading="loading" class="form-control input-sm" tabindex="2" required focus-me />
			<!-- <input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre el nombre de la campaña" tabindex="1" focus-me required />  -->
		</div>
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">	Tipo Campaña <small class="text-danger">(*)</small> </label>
            <select class="form-control input-sm" ng-model="fData.tipocampania" ng-options="item.id as item.descripcion for item in listaTipoCampania" tabindex="3"></select> 
		</div>
		<div class="form-group mb-md col-md-4" >
			<label class="control-label mb-xs">Asignar una Especialidad <small class="text-danger">(*)</small> </label>
			<div class="input-group">
				<span class="input-group-btn ">
					<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idespecialidad" placeholder="ID" readonly="true" required />
				</span>
				<input type="text" class="form-control input-sm" ng-model="fData.especialidad" placeholder="" typeahead-loading="loadingLocations" uib-typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedEspecialidad($item, $model, $label)" typeahead-min-length="1" ng-change="fData.idespecialidad = null" tabindex="4"/>
			</div>
			<i ng-show="loadingLocations" class="fa fa-refresh"></i>
            <div ng-show="noResultsLCargo">
              <i class="fa fa-remove"></i> No se encontró resultados 
            </div>
		</div>
		<div class="form-group mb-md col-md-4 mt-md" > 
			<button class="btn btn-block btn-success" type="button" ng-click="btnRegistroFechas(accion); $event.preventDefault();"><i class="fa fa-calendar pull-left"></i><i class="fa fa-check-square-o pull-right" ng-show="!errfec.valor"></i><i class="fa fa-exclamation-triangle pull-right" ng-show="errfec.valor" style="color:red;"></i>FECHAS DE CAMPAÑA </button>
		</div>
		<div class="clearfix"> </div>
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">Agregar Paquete </label>
			<input id="paquete" type="text" class="form-control input-sm" ng-model="fData.temporal.paquete" placeholder="Agregar Paquete" tabindex="11" />
		</div>
		<div class="form-group mb-n mt-lg col-md-8">
			<button class="btn btn-primary btn-sm" ng-click="agregarPaqueteItem(); $event.preventDefault();" tabindex="12">Agregar</button>
		</div>
		<div class="form-group mb-md col-md-12">
			<div ui-grid="gridOptionsPaqueteAdd" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive" style="overflow: hidden;">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsPaqueteAdd.data.length"> No hay Datos </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button class="btn btn-info" ng-click="aceptar('g'); $event.preventDefault();" ng-disabled="formCampania.$invalid" tabindex="13">Guardar</button>
    <button class="btn btn-primary" ng-click="aceptar('gc'); $event.preventDefault();" ng-disabled="formCampania.$invalid" tabindex="14">Guardar y Cerrar</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="15">Cerrar</button>
</div>