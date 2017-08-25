<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formZona"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo de Zona </label>
			
			<select class="form-control input-sm" ng-model="fData.idtipozona" ng-options="item.id as item.descripcion for item in listaTiposZona" tabindex="1" required focus-me> </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Zona <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre la zona" tabindex="2"  required />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formZona.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>