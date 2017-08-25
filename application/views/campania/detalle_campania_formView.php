<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDetalleCampania"> 
		<div class="form-group mb-md col-md-6">
            <ul class="row demo-btns">
                <li class="form-group mr mt-sm col-sm-3 col-md-12 p-n" > <label> Campañas </label> 
                   <select class="form-control input-sm" ng-model="fData.idcampania" ng-options="item.id as item.descripcion for item in listaCampania" ></select> 
                </li> 
            </ul>
		</div>
        <div class="well well-transparent boxDark col-xs-12 m-n">
            <div class="row">
            
                <div class="form-group mb-md col-md-6" >
                    <label class="control-label mb-xs"> Nombre del Paquete <small class="text-danger">(*)</small> </label>
                    <input type="text" class="form-control input-sm" ng-model="fData.paquete" placeholder="Digite el nombre del paquete" tabindex="1" focus-me required /> 
                </div>
                <div class="form-group mb-md col-md-4 col-sm-12"> 
                    <label class="control-label mb-xs"> Especialidad </label> 
                        <div >
                            <input id="temporalEspecialidad" type="text" ng-model="fDataVenta.temporal.especialidad" class="form-control input-sm" tabindex="106" placeholder="Busque Especialidad." typeahead-loading="loadingLocations" 
                              uib-typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedEspecialidad($item, $model, $label)" typeahead-min-length="2" /> 
                            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                            <div ng-show="noResultsLEESS">
                              <i class="fa fa-remove"></i> No se encontró resultados 
                            </div>
                        </div>
                </div>
                <div class="form-group mb-md col-md-4 col-sm-6"> 
                    <label class="control-label mb-xs"> Producto/Servicio </label>
                        <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" tabindex="107" autocomplete="off" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
                         uib-typeahead="item as item.descripcion for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" /> 
                        <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                        <div ng-show="noResultsLPSC">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                        </div>
                </div> 
                <div class="form-group mb-md col-md-2 col-sm-6">
                    <label class="control-label mb-xs"> Precio </label>
                    <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.precio" placeholder="Precio" /> 
                </div>
                        
                <div class="form-group mb-sm mt-lg col-md-2 col-sm-12"> 
                    <input type="button" class="btn btn-info col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" /> 
                </div>
                <div class="form-group col-xs-12 m-n">
                    <label class="control-label">Agregar al detalle: </label>
                        <div ui-if="gridOptionsTab3.data.length>0" 
                        ui-grid="gridOptionsTab3" ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                </div>
                <div class="form-inline col-md-4 pull-right">
                    <div class="form-group mb-md mt-md col-md-12 col-sm-6">
                        <label class="control-label mb-xs text-danger"><strong> Monto Total :</strong></label>
                        <input type="text" class="form-control input-sm" style="font-weight:bold;color:green;" ng-model="fDataVenta.temporal.monto_total" placeholder="0.00" disabled="true" /> 
                    </div>
                </div>
            </div>
        </div>


	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formDetalleCampania.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>