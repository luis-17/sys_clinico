<div class="modal-header pb-n pt-md">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body pt-sm pb-sm">
	<div class="row">
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 2">
			<label class="control-label mb-n"> Factura: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.ticket_venta }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 2">
			<label class="control-label mb-n"> Guia de Remisión: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.guia_remision }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 2">
			<label class="control-label mb-n"> Proveedor: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.razon_social }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 2">
			<label class="control-label mb-n"> Fecha de Compra: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 1">
			<label class="control-label mb-n"> Fecha de Venta: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 3">
			<label class="control-label mb-n"> Fecha de Traslado: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 3">
			<label class="control-label mb-n"> Almacen : </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDatosTrasladoOrigen.nombre_alm}} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 3">
			<label class="control-label mb-n"> SubAlmacen Origen: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDatosTrasladoOrigen.nombre_salm}} </p> 
		</div>
		<div class="form-group col-md-3 mb-md" ng-if="fMovimiento.idtipomovimiento == 3">
			<label class="control-label mb-n"> SubAlmacen Destino: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fMovimiento.nombre_salm }} </p> 
		</div>
		<div class="form-group mb-sm col-xs-12">
			<div ui-grid="gridOptionsDetalleMovimiento" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div> 
		</div>
		<div class="col-xs-12">
			<div class="col-md-6 col-sm-6 p-n">
                <label class="control-label mb-xs"> Observaciones </label>
                <textarea class="form-control input-sm" readonly="readonly">{{ fMovimiento.motivo_movimiento }}</textarea>
	        </div>
	        <div class="text-right" ng-if="fMovimiento.idtipomovimiento != 3">
	          <h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : {{  fMovimiento.total_a_pagar }} </strong> </h2>
	        </div>
	    </div>
	</div>
</div> 
<div class="modal-footer pt-sm"> 
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 