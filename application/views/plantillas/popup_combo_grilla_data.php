<div class="modal-header" ng-init="focusIndex = 0;">
	<h4 class="modal-title"> Selección de {{ fpc.titulo }} </h4>
</div>
<div class="modal-body row">
	<div class="form-inline mb-md col-md-12" >
		<input style="min-width: 42%;" type="text" ng-change="fpc.buscar()" class="form-control" ng-model="fpc.search" placeholder="Busque {{ fpc.titulo }}" focus-me="{{ focusIndex }}" ng-enter="fpc.seleccionar()" />
		<button class="btn btn-success pull-right" ng-click="fpc.aceptar()">Aceptar</button> 
	</div>
	<div class="col-md-12" >
		<div ui-grid="gridComboOptions" ui-grid-pagination ui-grid-selection ui-grid-cellNav class="grid table-responsive"></div> 
    </div>
</div>
<div class="modal-footer">
</div>