<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSede"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre la sede" tabindex="1" focus-me required />
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Hora inicio atención <small class="text-danger">(*)</small> </label>
			<input type="time" class="form-control input-sm" ng-model="fData.hora1"  tabindex="1" placeholder="HH:mm:ss" required />
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Hora fin atención <small class="text-danger">(*)</small> </label>
			<input type="time" class="form-control input-sm" ng-model="fData.hora2"  tabindex="1" placeholder="HH:mm:ss" required />
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Intervalo sede </label>
			<input type="number" class="form-control input-sm" ng-model="fData.intervalo_sede"  tabindex="1" min="0"/>
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formSede.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>