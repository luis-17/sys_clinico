<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formProfesion"> 
		<div class="form-group mb-md col-md-10">
			<label class="control-label mb-xs">Nombre</label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" required> </input>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formProfesion.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>