<div class="modal-header pb-xs pt-md">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
	<div class="row">
		
		<div class="form-group mb-xs col-md-3">
			<label class="control-label col-md-12 pl-n mb-n "> Almacen Origen: </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.almacen }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-3">
			<label class="control-label col-md-12 pl-n mb-n "> Sub Almacen Origen : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.subAlmacenOrigen }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-3">
			<label class="control-label col-md-12 pl-n mb-n "> Almacen Destino: </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.almacen2 }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-3">
			<label class="control-label col-md-12 pl-n mb-n "> Sub Almacen Destino : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.subAlmacenDestino }}</strong></label>
		</div>
		<div class="form-group mb-xs col-md-4">
			<label class="control-label col-md-12 pl-n mb-n "> Fecha : </label>
			<label class="control-label col-md-12 pl-n" ><strong>{{ fTraslado.fecha_movimiento }}</strong></label>			
		</div>		
		<div class="form-group mb-md col-xs-12">
			<ul class="form-group demo-btns col-xs-12">	
				<li class="pull-right" ng-if="mySelectionGridGR.length > 0 && (((fSessionCI.key_group == 'key_dir_far' || fSessionCI.key_group == 'key_admin_far') && estado_guia_remision) || (fSessionCI.key_group == 'key_sistemas'))">
					<button type="button" class="btn btn-danger" ng-click='btnAnularGuiaRemision();'> 
					<i class="fa fa-times-circle"> </i> Anular Guía Remisión</button>
				</li>          	
	          	<li class="pull-right" ng-if="mySelectionGridGR.length == 1">
	          		<button type="button" class="btn btn-info" ng-click='btnEditarGuiaRemision();'> Editar Guía Remisión</button>
	          	</li>
	          	<li class="pull-right" ng-if="mySelectionGridGR.length == 1">
	          		<button type="button" class="btn btn-info ml-sm" ng-click="btnImprimirGuiaRemision();"> 
    				<i class="fa fa-print" ></i> [F4] IMPRIMIR</button> 
    			</li>       
	        </ul>
			<div ui-grid="gridOptionsListaGR" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>