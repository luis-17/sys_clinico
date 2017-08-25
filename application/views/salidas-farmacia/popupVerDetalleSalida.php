<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-1 mb-md">
			<label class="control-label mb-n"> CÓDIGO: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fSalida.idmovimiento }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> ALMACÉN: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fSalida.almacen }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> SUB-ALMACÉN: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fSalida.subAlmacen }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> RESPONSABLE: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fSalida.usuario }} </p> 
		</div>
		<div class="form-group col-md-2 mb-md">
			<label class="control-label mb-n"> FECHA DE BAJA: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fSalida.fecha_movimiento }} </p> 
		</div>
		<div class="form-group mb-md col-xs-12">
<!-- 			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> 
            </ul> -->
			<div ui-grid="gridOptionsDetalleSalida" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>

		<div class="col-xs-12">
			<div class="col-md-6 col-sm-6 p-n">

                <label class="control-label mb-xs"> Observaciones </label>
                <textarea class="form-control input-sm" readonly="readonly">{{ fSalida.motivo_movimiento }}</textarea>
	        </div>
	       
	    </div>
	</div>
</div> 
<div class="modal-footer">
	<button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir();" ><i class="fa fa-print"></i> [F4] IMPRIMIR</button>
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 