<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCanal"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Canal <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre canal" tabindex="1" focus-me required maxlength="200" />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Porcentaje <small class="text-danger">(*)</small> </label>
			<input type="number" class="form-control input-sm" ng-model="fData.porcentaje" placeholder="Digite porcentaje de venta" tabindex="1" required min="0" max="100" />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCanal.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>