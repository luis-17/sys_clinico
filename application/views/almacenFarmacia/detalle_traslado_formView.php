<div class="modal-header pb-xs pt-md">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
	<div class="row">
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Fecha : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.fecha_movimiento }}</strong></label>			
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Almacen : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.almacen }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Sub Almacen Origen : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.subAlmacenOrigen }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Sub Almacen Destino : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.subAlmacenDestino }}</strong></label>
		</div>
		<div class="form-group mb-md col-xs-12">
			<div ui-grid="gridOptionsDetalleTraslado" ui-grid-auto-resize ui-grid-pagination ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>

		<div class="col-xs-12">
			<div class="col-md-6 col-sm-6 p-n">
                <label class="control-label mb-xs"> Observaciones </label>
                <textarea class="form-control input-sm" readonly="readonly">{{ fTraslado.motivo_movimiento }}</textarea>
	        </div>
	    </div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>