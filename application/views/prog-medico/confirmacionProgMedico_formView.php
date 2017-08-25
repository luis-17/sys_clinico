<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formConfirmacion"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Contraseña <small class="text-danger">(*)</small> </label>
			<input type="password" class="form-control input-sm" ng-model="fDataUsuario.clave" placeholder="Ingrese su contraseña actual" 
			required tabindex="1" ng-enter="modalAceptar();" focus-me />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="modalAceptar(); $event.preventDefault();" ng-disabled="formConfirmacion.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="modalCancel()">Cerrar</button>
</div>