<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formRoles" novalidate > 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Rol <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre el rol" focus-me tabindex="1" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Controlador <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.url" placeholder="Registre el controlador" tabindex="2" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Icono(clase) <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.icono" placeholder="Registre el icono" tabindex="3" required />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formRoles.$invalid" tabindex="4">Aceptar</button>
    <button type="button" class="btn btn-warning" ng-click="cancel()" tabindex="5">Cancelar</button>
</div>