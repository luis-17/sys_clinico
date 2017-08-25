<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formUsuario" novalidate > 
    	
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Contraseña Actual <small class="text-danger">(*)</small> </label> 
			<input id="clave" required type="password" class="form-control input-sm" ng-model="fDataUsuario.clave" placeholder="Registre su contraseña" />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Nueva Contraseña <small class="text-danger">(*)</small> </label> 
			<input id="nuevoPass" required ng-minlength="6" type="password" class="form-control input-sm" ng-model="fDataUsuario.claveNueva" placeholder="Nueva contraseña (Min 6 caracteres)" />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Confirmar Nueva Contraseña <small class="text-danger">(*)</small> </label> 
			<input required ng-minlength="6" type="password" class="form-control input-sm" ng-model="fDataUsuario.claveConfirmar" placeholder="Confirme su nueva contraseña" />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formUsuario.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>