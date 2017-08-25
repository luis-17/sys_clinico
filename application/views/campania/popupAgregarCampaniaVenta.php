<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAddCampania }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="form-group col-md-4 mb-xs">
			<label class="control-label mb-n"> ESPECIALIDAD: </label>
			<p class="help-block mt-xs"> {{ fDataVenta.temporal.especialidad.descripcion }} </p>
		</div>
		<div class="form-group col-md-4 mb-xs"> 
			<label class="control-label mb-n"> Seleccione Campaña/Paquete </label>
			<select class="form-control input-sm" ng-model="fDataCampania.campaniaPaquete" ng-change="getPaginationDETPAQServerSide(); $event.preventDefault();" 
				ng-options="item as item.descripcion for item in listaCampaniaPaquete" required > </select> 
		</div>
		<div class="form-inline col-md-12 mt-sm">
			<label class="control-label">Seleccione Productos del Paquete: </label> 
		</div>
		<div class="form-group mb-md col-md-12">
			<div ui-grid="gridOptionsDETPAQ" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div> 
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();"> AGREGAR </button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>