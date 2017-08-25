<div class="modal-header pb-xs pt-md">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
	<div class="row">
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Fecha : </label>
			<label class="control-label col-md-12 pl-n text-primary" ><strong>{{ fData.fecha_movimiento }}</strong></label>			
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Almacen : </label>
			<label class="control-label col-md-12 pl-n text-primary" ><strong>{{ fData.almacen }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Sub Almacen : </label>
			<label class="control-label col-md-12 pl-n text-primary" ><strong>{{ fData.subAlmacen }}</strong></label>
		</div>
		<div class="clearfix"></div>
	 	<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Cantidad : </label>
			<label class="control-label col-md-12 pl-n text-primary" ><strong>{{ fData.cantidad }}</strong></label>
		</div>
	 	<div class="form-group mb-xs col-md-8">
			<label class="control-label col-md-12 pl-n mb-n "> Producto : </label>
			<label class="control-label col-md-12 pl-n text-primary" ><strong>{{ fData.producto }}</strong></label>
		</div>
   		<div class="form-group col-md-12 mt-md mb-n">
   			<label class="control-label mb-xs">Motivo : </label>
   			<textarea class="form-control col-md-12 p-n" rows="2" tabindex="4" disabled> {{ fData.motivo_movimiento }}</textarea>
   		</div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>