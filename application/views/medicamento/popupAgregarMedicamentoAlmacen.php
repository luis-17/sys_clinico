<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="form-group col-md-3 mb-xs">
			<label class="control-label mb-n"> ALMACEN </label> 
			<select ng-change="listarSubAlmacenesAlmacen(fDataAdd.idalmacen,'FA');" class="form-control" ng-model="fDataAdd.idalmacen" ng-options="item.id as item.descripcion for item in listaAlmacen"> </select> 
		</div>
		<div class="form-group col-md-3 mb-xs">
			<label class="control-label mb-n"> SUB-ALMACEN </label> 
			<select class="form-control" ng-model="fDataAdd.idsubalmacen" ng-options="item.id as item.descripcion for item in listaSubAlmacen" ng-change="metodos.getPaginationMedServerSide();"> </select>
		</div>
		<div class="form-inline col-md-12 m-n"></div>
		<div class="form-inline col-md-6 m-n">
			<label class="control-label">Seleccione Medicamentos no agregados: </label> 
		</div>
		<div class="form-inline col-md-6 m-n">
			<label class="control-label">Edite el precio, con doble click en las celdas amarillas: </label> 
		</div>
		<div class="form-group mb-md col-md-6 col-sm-12"> 
			<div ui-grid="gridOptionsMedicamentos" ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
		<div class="form-group mb-md col-md-6 col-sm-12"> 
			<div ui-grid="gridOptionsAddMedicamento" ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();"> AGREGAR TODOS </button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>