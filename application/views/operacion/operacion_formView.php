<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formConcepto"> 
		<div class="form-group mb-md col-md-8">
			<label class="control-label mb-xs">Operación <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre la operación" tabindex="1" focus-me required />
		</div>
 		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">Tipo <small class="text-danger">(*)</small></label>{{fData.categoria_cbo}}
			<select class="form-control input-sm" ng-model="fData.tipo_operacion" ng-options="item as item.descripcion for item in listaTipo" required > </select>
		</div>
		<!--<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Observaciones </label>
			<textarea class="form-control col-md-12 p-n" rows="2" tabindex="2" ng-model="fData.observaciones" placeholder="Registre observaciones"></textarea>
		</div> -->
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formConcepto.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>