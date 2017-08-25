<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formTipoProducto"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Tipo de Producto <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre Tipo de Producto" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Módulo <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idmodulo" ng-options="item.id as item.descripcion for item in listaModulos" tabindex="2" required></select> 		
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Descripcion </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder="Ingrese una descripción" tabindex="3" ></textarea>
		</div>
		

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formTipoProducto.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>