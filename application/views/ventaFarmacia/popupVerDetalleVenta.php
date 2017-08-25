<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° Orden: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fVenta.orden }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Ticket: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fVenta.ticket }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Cliente:</label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fVenta.cliente }}<br>
			<span class="text-blue" ng-show = "fVenta.idtipocliente"><small>({{fVenta.tipocliente}})</small></span>
			</p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de Venta: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fVenta.fecha_movimiento }} </p> 
		</div>
		<div class="form-group mb-md col-xs-12">
<!-- 			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> 
            </ul> -->
			<div ui-grid="gridOptionsDetalleVenta" ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>
	</div>
</div> 
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 