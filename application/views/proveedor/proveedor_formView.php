<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formProveedor"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Empresa O Razón Social <small class="text-danger">(*)</small> </label>
				<input ng-if="accion=='reg'" type="text" ng-model="fData.razon_social" placeholder="Registre el Proveedor" uib-typeahead="razon_social for razon_social in getProveedorAutocomplete($viewValue)" 
					typeahead-loading="loading" class="form-control input-sm" tabindex="1" required focus-me autocomplete ="off"/>
				<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.razon_social" placeholder="Registre el Proveedor" tabindex="1" focus-me required /> 
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> RUC <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.ruc" ng-minlength="11" placeholder="Registre su RUC" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Representante Legal </label>
			<input type="text" class="form-control input-sm" ng-model="fData.representante_legal" placeholder="Registre su representante legal" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Teléfono </label>
			<input type="text" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Email </label>
			<input type="text" class="form-control input-sm" ng-model="fData.email" placeholder="Registre su Email" />	
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formProveedor.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>