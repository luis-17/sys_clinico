<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDiagnostico"> 
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">Código Científico <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.codigo" placeholder="Registre Código" tabindex="1" focus-me required />
		</div>

		<div class="form-group mb-md col-md-8">
			<label class="control-label mb-xs">Descripcion <small class="text-danger">(*)</small></label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre un diagnostico" tabindex="2" required />
		</div>
		

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formDiagnostico.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>