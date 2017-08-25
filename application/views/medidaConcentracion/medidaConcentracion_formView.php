<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formMedidaConcentracion"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Medida de Concentración <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Medida de Concentración" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Abreviatura <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.abreviatura" placeholder="Registre Abreviatura" tabindex="2" required />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formMedidaConcentracion.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>