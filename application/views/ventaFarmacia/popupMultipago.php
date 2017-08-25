<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-6 mb-xs">
			<label class="control-label mb-n"> Total: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGridV[0].total }} </p> 
		</div>
		<div class="form-group col-md-6 mb-xs mt-md" >
			<button type="button" class="btn btn-success pull-right" ng-click="getPaginationServerSideDetallePagoMixto();"><i class="fa fa-refresh"></i></button>
		</div>
		<div class="form-group mb-md col-md-12" >
            <div ui-grid="gridOptionsDetPagoMixto" ui-grid-auto-resize ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid scroll-x-none" style="overflow-x: hidden;"></div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button class="btn btn-success" ng-click="guardar()" > Guardar y Salir </button> 
    <button class="btn btn-warning" ng-click="cancel()" > Cancelar </button> 
</div> 