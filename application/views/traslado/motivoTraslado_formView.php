<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formMotivoTraslado"> 

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Descripción </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion"> </textarea>
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formMotivoTraslado.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>