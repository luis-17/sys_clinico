<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formEmpresaCliente"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Empresa O Razón Social <small class="text-danger">(*)</small> </label>
				<input ng-if="accion=='reg'" type="text" ng-model="fData.empresa" placeholder="Registre la empresa" uib-typeahead="descripcion for descripcion in getEmpresasAutocomplete($viewValue)" 
					typeahead-loading="loading" class="form-control input-sm" tabindex="500" required focus-me />
				<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.empresa" placeholder="Registre la empresa" tabindex="510" focus-me required /> 
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> RUC <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.ruc_empresa" placeholder="Registre su RUC" required tabindex="520" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Salud Ocupacional </label>  
			<label class=""> 
				<input type="checkbox" ng-disabled="disabledPSO" class="" ng-model="fData.pertenece_salud_ocup" ng-false-value="2" ng-true-value="1" tabindex="525"/> 
				<small style="font-size: 11px;">¿Asigna a Salud Ocup.?</small>
			</label>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Domicilio Fiscal <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.domicilio_fiscal" placeholder="Registre su domicilio fiscal" required tabindex="530" />
		</div>
		
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Teléfono </label>
			<input type="text" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" tabindex="540" />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formEmpresaCliente.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>