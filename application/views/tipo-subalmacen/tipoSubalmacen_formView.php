<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formTipoSubalmacen"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo de Sub Almacen <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Tipo de Zona" tabindex="1" focus-me required />
		</div>
		<div class="form-group mt-md col-md-6">
			<div class="checkbox"> 
				<label><input type="checkbox" ng-model="fData.venta_a_cliente" /> ¿Venta al Público? </label> 
			</div> 
		</div> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formTipoSubalmacen.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>