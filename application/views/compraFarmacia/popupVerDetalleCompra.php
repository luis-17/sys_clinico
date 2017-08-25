<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Factura: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.factura }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Guia de Remisión: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.guia_remision }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Proveedor: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.razon_social }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de Compra: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fEntrada.fecha_movimiento }} </p> 
		</div>
		<div class="form-group mb-md col-xs-12">
<!-- 			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> 
            </ul> -->
			<div ui-grid="gridOptionsDetalleEntrada" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>

		<div class="col-xs-12">
			<div class="col-md-6 col-sm-6 p-n">

                <label class="control-label mb-xs"> Observaciones </label>
                <textarea class="form-control input-sm" readonly="readonly">{{ fEntrada.motivo_movimiento }}</textarea>
	        </div>
	        <div class="text-right">
	          <h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : {{  gridOptionsDetalleEntrada.sumTotal }} </strong> </h2>
	        </div>
	    </div>
	</div>
</div> 
<div class="modal-footer">
	<button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(fEntrada.idmovimiento, fEntrada.estado_movimiento);" ><i class="fa fa-print"></i> [F4] IMPRIMIR</button>
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 