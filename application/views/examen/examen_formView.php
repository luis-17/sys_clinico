<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formExamen"> 
		<!-- <div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Examen <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.tipoexamen" placeholder="Registre Examen" tabindex="1" focus-me required />
		</div> -->

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo de Examen </label>
			
			<select class="form-control input-sm" ng-model="fData.idtipoexamen" ng-options="item.id as item.descripcion for item in listaTiposExamen" tabindex="1" required focus-me> </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Examen <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre el examen" tabindex="2"  required />
		</div>

		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Descripcion </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder="Ingrese una descripción del examen" tabindex="3" rows="10"></textarea>
		</div>
		

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formExamen.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>