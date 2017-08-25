<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCentroCosto"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" tabindex="1" focus-me required />
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Código <small class="text-danger">(*)</small></label>
			<input type="text" class="form-control input-sm" ng-model="fData.codigo"  tabindex="1" required/>
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Cat / SubCategoria <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idsubcat" ng-options="item.id as item.descripcion for item in listaSubCatCentroCosto" ng-change="cargarCentroCosto(fData.idsubcatcentrocosto,true);"> </select> 
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Descripción </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion"> </textarea>
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCentroCosto.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>