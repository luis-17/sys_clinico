<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAvisoImportante"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Título <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.titulo" placeholder="Título del Aviso" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Redacción <small class="text-danger">(*)</small> </label>
			<textarea type="text" class="form-control input-sm" ng-model="fData.redaccion" placeholder="Redacte el Aviso" tabindex="2" required ></textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAvisoImportante.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>