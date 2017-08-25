<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formProveedorFarmacia"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Empresa o Razón Social <small class="text-danger">(*)</small> </label>
			<!-- <input ng-if="accion=='reg'" type="text" ng-model="fData.razon_social" placeholder="Registre el Proveedor" uib-typeahead="razon_social for razon_social in getProveedorAutocomplete($viewValue)" 
				typeahead-loading="loading" class="form-control input-sm" tabindex="1" required focus-me autocomplete ="off"/> -->
			<!-- ng-if="accion=='edit'" --> 
			<input type="text" class="form-control input-sm" ng-model="fData.razon_social" placeholder="Registre el Proveedor" tabindex="1" focus-me required /> 
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Nombre Comercial <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre_comercial" placeholder="Registre el Nombre Comercial" tabindex="2" required /> 
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> RUC <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.ruc" ng-minlength="11" placeholder="Registre su RUC" tabindex="3" ng-pattern="pRUC" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo Proveedor <small class="text-danger">(*)</small> </label>
			<select required class="form-control input-sm"  ng-model="fData.idtipoproveedor" ng-options="item.id as item.descripcion for item in listaTipoProveedor" tabindex="4" required></select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Representante Legal </label>
			<input type="text" class="form-control input-sm" ng-model="fData.representante" placeholder="Registre su representante legal" tabindex="5" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Direccion Fiscal </label>
			<input type="text" class="form-control input-sm" ng-model="fData.direccion_fiscal" placeholder="Registre su Direccion Fiscal" tabindex="5" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Celular </label>
			<input type="text" class="form-control input-sm" ng-model="fData.celular" placeholder="Registre su Celular" tabindex="6" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Teléfono </label>
			<input type="text" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" tabindex="7" maxlength="10" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Fax </label>
			<input type="text" class="form-control input-sm" ng-model="fData.fax" placeholder="Registre su Fax" tabindex="8" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Email </label>
			<input type="text" class="form-control input-sm" ng-model="fData.email" placeholder="Registre su Email" tabindex="9" />	
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Forma de Pago </label>
			<select required class="form-control input-sm"  ng-model="fData.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" tabindex="10" required></select>
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Moneda </label>
			<select required class="form-control input-sm"  ng-model="fData.moneda" ng-options="item.id as item.descripcion for item in listaMoneda" tabindex="11" required></select>
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Observaciones </label>
			<textarea class="form-control input-sm" ng-model="fData.observaciones"></textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formProveedorFarmacia.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>