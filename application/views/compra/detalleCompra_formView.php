<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° RUC: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.ruc }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Empresa: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.empresa }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Tipo de Documento: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.descripcion_td }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° Documento: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDataDetalle.numero_documento }} </p> 
		</div>

		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de registro: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDataDetalle.fecha_registro }} </p> 
		</div>

		<div class="col-xs-12"> 
			<div ui-grid="gridOptionsDetalleUnES" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div> 
		</div>
		<div class="col-xs-12">
			<!-- <div class="col-md-6 col-sm-6 p-n">
                <label class="control-label mb-xs"> Observaciones </label>
                <textarea class="form-control input-sm" readonly="readonly">{{ fDataDetalle.motivo_movimiento }}</textarea>
	        </div> -->
	        <div class="text-right">
	          <h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : {{  fDataDetalle.total }} </strong> </h2>
	        </div>
	    </div>
	</div>
</div> 
<div class="modal-footer"> 
	<!-- <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(fDataDetalle.idmovimiento);" ><i class="fa fa-print"></i> [F4] IMPRIMIR</button> -->
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 