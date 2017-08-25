<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAlmacenFarmacia"> 
    	<div class="form-group col-md-7 mb-md">
			<label class="control-label mb-n"> Nombre de Almacen : </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.nombre_alm" placeholder="Ingrese nombre de almacen" required />
		</div>
		<!--<div class="form-group mb-md col-md-12" ng-if="titleForm == 'Registro de Almacen'">-->
		<div class="form-group mb-md col-md-12" >
			<label class="control-label">Agregar Sede - Empresa : </label>
			<div ui-grid="gridOptionsEmpresaSede" ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAlmacenFarmacia.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>