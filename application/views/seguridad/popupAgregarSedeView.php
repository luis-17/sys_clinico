<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
	    <div class="form-inline mb-md col-md-12" >
			<input style="min-width: 42%;" type="text" ng-change="buscar()" class="form-control" ng-model="searchText" 
				placeholder="Busque sede" focus-me />
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n">Usuario: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].usuario }} </p>
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label">Agregar sedes</label>
			<div ui-grid="gridOptionsSede" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>