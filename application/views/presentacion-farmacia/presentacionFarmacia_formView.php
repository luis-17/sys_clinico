<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPresentacionFarmacia"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Presentaciones <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Presentación" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Abreviatura <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.abreviatura" placeholder="Registre abreviatura" tabindex="2" required ng-maxlength="3"/>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPresentacionFarmacia.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>