<div class="modal-header"> <!-- MODULO HOSPITAL -->
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formStocks">
    	<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width: 80px;">EMPLEADO: </label>
			<span>{{fDataVac.personal}}</span>
		</div>
		<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width:120px;">CARGO ACTUAL: </label>
			<span>{{fDataVac.cargo}}</span>
		</div>
		<div class="form-group mb-md col-md-12">
			<div ui-grid="gridOptions" ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
				<div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
			</div>
		</div>
	</form>
	
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>