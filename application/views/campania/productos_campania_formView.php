<style type="text/css">
    .modal{z-index: 9999999999!important;}
</style>
<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDetalleCampania"> 
        <div class="form-group mb-md col-md-4 col-sm-12"> 
            <label class="control-label mb-xs"> Especialidad </label> 
            <div >
                <input id="temporalEspecialidad" type="text" ng-model="fDataVenta.temporal.especialidad" class="form-control input-sm" tabindex="100" placeholder="Busque Especialidad." typeahead-loading="loadingLocations" 
                  uib-typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedEspecialidad($item, $model, $label)" typeahead-min-length="2" focus-me /> 
                <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                <div ng-show="noResultsLEESS">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
            </div>
        </div>
        <div class="form-group mb-md col-md-4 col-sm-6">
            <label class="control-label mb-xs"> Producto/Servicio </label><button type="button" class="btn btn-xs btn-danger-alt btn-label ml-md" ng-click="btnNuevoProducto();$event.preventDefault();"><i class="fa fa-pencil"></i> Nuevo</button>
            <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" autocomplete="off" class="form-control input-sm" tabindex="101" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
             uib-typeahead="item as item.descripcion for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" /> 
            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
            <div ng-show="noResultsLPSC">
                <i class="fa fa-remove"></i> No se encontró resultados
            </div>
        </div>
        <div class="form-group mb-md col-md-2 col-sm-6">
            <label class="control-label mb-xs"> Precio </label>
            <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.precio" placeholder="Precio" tabindex="102"/>
        </div>
        <div class="form-group mb-sm mt-lg col-md-2 col-sm-12"> 
            <input type="button" class="btn btn-info col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="103" value="Agregar" />
        </div>
        <div class="form-group col-xs-12 m-n">
            <label class="control-label">Agregar al detalle: </label>
            <div ui-grid="gridOptionsProductoAdd" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;">
                <div class="waterMarkEmptyData" ng-show="!gridOptionsProductoAdd.data.length"> No hay Productos </div>
            </div>
        </div>
        <div class="form-inline col-md-4 pull-right">
            <div class="form-group mb-md mt-md col-md-12 col-sm-6">
                <label class="control-label mb-xs text-danger"><strong> Monto Total :</strong></label>
                <input type="text" class="form-control input-sm" style="font-weight:bold;color:green;" ng-model="fDataVenta.temporal.monto_total" placeholder="0.00" readonly="true" /> 
            </div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" tabindex="104" ng-click="aceptar(); $event.preventDefault();" ng-disabled="!gridOptionsProductoAdd.data.length">Guardar y cerrar</button>
    <button class="btn btn-warning" tabindex="105" ng-click="cancel()">Cerrar</button>
</div>