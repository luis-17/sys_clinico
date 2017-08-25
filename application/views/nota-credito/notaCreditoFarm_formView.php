<div class="modal-header pb-xs pt-sm">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pb-xs">
    <form class="row" name="formNotaCredito" ng-show="cajaAbiertaPorMiSession"> 
    	<div class="form-group mb-md col-md-6"></div>
    	<div class="form-group mb-md col-md-6">
	    	<small class="text-default block mb-xs text-right" style="font-size: 18px;line-height: 1;">
	    		<div class="well well-sm m-n " style="display: inline;">
				<!-- Ticket --> N° <strong> {{ fData.ticket }} </strong> 
				<button class="btn btn-xs btn-warning" ng-click="generarCodigoTicket(); $event.preventDefault();" type="button" title="" tooltip="Actualizar" tooltip-placement="bottom">
					<i class="ti ti-reload "></i>
				</button>
				</div>
			</small>
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Búsqueda de Orden de Venta <small class="text-danger">(*)</small> </label>
			<input type="text" ng-model="fData.orden" class="form-control input-sm" tabindex="10" placeholder="Busque la Orden de Venta." typeahead-loading="loadingLocations" 
                uib-typeahead="item as item.orden for item in getOrdenesVentaAutocomplete($viewValue)" typeahead-min-length="1" typeahead-on-select="getPaginationServerSideDetalleVenta();" required focus-me ng-change="limpiarGrid();"/> 
             
          	<div ng-show="noResultsOV" class="text-red">
            	No se encontró resultados 

          	</div>
		</div> 
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Total Venta <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm text-center" ng-model="fData.orden.saldo" placeholder="Total Venta" required disabled />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Fecha Venta <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm text-center" ng-model="fData.orden.fecha_venta" placeholder="Fecha Venta" disabled />
		</div>
		
		<div class="form-group mb-sm col-md-6 clear">
			<label class="control-label mb-xs"> Cliente </label>
			<input type="text" class="form-control input-sm" ng-model="fData.orden.cliente" placeholder="Cliente" disabled />
		</div>
		<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs"> Empresa Cliente </label>
			<input type="text" class="form-control input-sm" ng-model="fData.orden.empresa_cliente" placeholder="Empresa" disabled />
		</div>
		
		<div class="form-group mb-xs col-md-12">
			<button type="button" class="btn btn-success input-sm pull-right" ng-click="getPaginationServerSideDetalleVenta();" ng-disabled="!fData.orden.saldo" style="padding: 2px 5px;" tooltip="Actualizar" tooltip-placement="bottom"><i class="fa fa-refresh"></i></button>
		</div>
		<div class="form-group mb-xs col-md-12">
            <div ui-grid="gridOptionsDetalleVenta" ui-grid-auto-resize ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid" >
            	<div class="waterMarkEmptyData" ng-show="!gridOptionsDetalleVenta.data.length && !fData.orden.saldo" style="top:50px"> Ingrese una orden de venta. </div>
            	<div class="waterMarkEmptyData" ng-show="!gridOptionsDetalleVenta.data.length && fData.orden.saldo" style="top:50px"> No hay datos. </div>
            </div>
		</div>
		<div class="form-inline mt-xs col-xs-12 text-right f-16">
          <label class="control-label mr-xs"> TOTAL N.C. </label> 
          <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fData.monto" required="true" placeholder="Total" style="width: 200px;font-size: 16px" /> 
        </div>

		<div class="form-group mb-xs col-md-12">
			<label class="control-label mb-xs"> Motivo/Descripción </label>
			<textarea type="text" class="form-control input-sm" ng-model="fData.orden.motivomovimiento" placeholder="Digite el motivo" tabindex="12" > </textarea>
		</div>
	</form>
	<div class="row">
		<div ng-show="!cajaAbiertaPorMiSession" class="col-xs-12">
			<div class="waterMarkEmptyData" style="position: relative; top: inherit; font-size: 20px;"> Proceda a abrir caja para comenzar... </div>
		</div> 
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formNotaCredito.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel();">Cancelar</button>
</div>