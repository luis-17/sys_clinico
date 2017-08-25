<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
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
			<input type="text" ng-model="fData.orden" class="form-control input-sm" tabindex="10" placeholder="Busque la Orden de Venta." typeahead-loading="loadingLocations" ng-change="gridOptionsDt.data=[]"
                uib-typeahead="item as item.orden for item in getOrdenesVentaAutocomplete($viewValue)" typeahead-min-length="1" required focus-me typeahead-on-select='onSelect($item, $model, $label)'/> 
             <!-- <i ng-show="loadingLocations" class="fa fa-refresh"></i> -->
          	<div ng-show="noResultsOV" class="text-red">
            	<i class="fa fa-remove"></i> No se encontró resultados 
          	</div>
		</div> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Paciente <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.orden.cliente" placeholder="Paciente" disabled />
		</div>
		<div class="form-group mb-md col-md-6 clear">
			<label class="control-label mb-xs"> Saldo <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.orden.saldo" placeholder="Saldo" disabled />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Monto <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.monto" placeholder="Monto" tabindex="11" required disabled/>
		</div>
		<div class="form-group mb-md col-md-12">
			<div ui-grid="gridOptionsDt" ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive">
	          <div class="waterMarkEmptyData" style="font-size: 24px; top: 60px;" ng-show="!gridOptionsDt.data.length"> No se encontraron datos. </div>
	        </div>
        </div>
		<div class="form-group col-md-12">
			<label class="control-label "> Motivo/Descripción <small class="text-danger">(*)</small> </label>
			<textarea type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Digite la descripción" tabindex="12" required > </textarea>
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
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>