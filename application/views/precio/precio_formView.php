<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPrecio"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre Tipo de Precio" tabindex="1" focus-me required />
		</div>

		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">Porcentaje <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.porcentaje" placeholder="Ingrese el Porcentaje" tabindex="2" required />
		</div>

		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">Tipo </label>
			
			<select class="form-control input-sm" ng-model="fData.tipo_precio" ng-options="item.id as item.name for item in listaTipoPrecio" tabindex="3"> </select>
		</div>

		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Descripcion </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder="Ingrese una descripción" tabindex="4" rows="10"></textarea>
		</div>


	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPrecio.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>