<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formEspecialidad"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre la especialidad" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo de Especialidad <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idtipoespecialidad" ng-options="item.id as item.descripcion for item in listaTipoEspecialidad" required tabindex="2"> </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Días Libres </label>
			<input type="text" class="form-control input-sm" ng-model="fData.dias_libres" placeholder="Registre los dias que estará libre para atender" tabindex="3"  />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formEspecialidad.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>