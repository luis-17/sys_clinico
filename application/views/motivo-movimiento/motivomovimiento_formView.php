<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formMotivoMovimiento"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Motivo de Movimiento <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Motivo de Movimiento" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Tipo <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.tipomovimiento" ng-options="item.id as item.descripcion for item in listaFiltroTipo" tabindex="2" required></select> 		
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formMotivoMovimiento.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>