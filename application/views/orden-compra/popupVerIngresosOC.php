<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Proveedor: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.razon_social }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de Creación: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de Aprobacion: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.fecha_aprobacion }} </p> 
		</div>
		<div class="form-group mb-md col-xs-12">
<!-- 			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> 
            </ul> -->
			<div ui-grid="gridOptionsIngresosPorOrden" ui-grid-pagination ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>

		
	</div>
</div> 
<div class="modal-footer"> 
	<button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(fEntrada.idmovimiento);" ><i class="fa fa-print"></i> [F4] IMPRIMIR</button>
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 