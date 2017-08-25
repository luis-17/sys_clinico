<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="form-group row col-md-3 mb">
		<label class="control-label mb-n"> FARMACIA </label> 
		<select class="form-control input-sm" ng-model="fBusqueda.idsubalmacen" ng-options="item.id as item.descripcion for item in listaSubAlmacen" ng-change="getPaginationProductoEnVentaServerSide();"> </select>
	</div>
	<div class="row">
		<div class="form-group mb-md col-xs-12">
			<div ui-grid="gridOptionsMedicamentoBusqueda" ui-grid-pagination ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div> 
		</div>
	</div>
</div> 
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div>