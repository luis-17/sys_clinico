<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formMotivoCambioPV">
    	
		<div class="form-group mb-md col-md-12">
			<h5 class="text-gray">{{mensaje}}</h5>
			<label class="control-label mb-xs"> Motivo de Cambio de P. Venta <small class="text-danger">(*)</small> </label>
			<textarea ng-model="temporal.motivo" class="form-control" rows="2" required focus-me> </textarea>
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formMotivoCambioPV.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>