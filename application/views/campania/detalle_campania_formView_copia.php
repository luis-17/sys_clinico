<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDetalleCampania"> 
		<!--<div class="form-group mb-md col-md-6">
            <ul class="row demo-btns">
                <li class="form-group mr mt-sm col-sm-3 col-md-12 p-n" > <label> Campañas </label> 
                   <select class="form-control input-sm" ng-model="fData.idcampania" ng-options="item.id as item.descripcion for item in listaCampania" ></select> 
                </li> 
            </ul>
			<label class="control-label mb-xs">	Nombre del Paquete <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.paquete" placeholder="Digite el nombre del paquete" tabindex="1" focus-me required /> 
		</div>-->
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs"> Nombre de la Campaña <small class="text-danger">(*)</small> </label>
                <input ng-if="accion=='reg'" type="text" ng-model="fData.campania" placeholder="Registre el nombre de la campaña"  
                    typeahead-loading="loading" class="form-control input-sm" tabindex="1" required focus-me />
                <input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre el nombre de la campaña" tabindex="1" focus-me required /> 
        </div>
        <div class="form-group mb-md col-md-3" >
            <label class="control-label mb-xs">Fecha de Inicio <small class="text-danger">(*)</small> </label>  
            <div class="input-group"> 
                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fData.fecha_inicio" required tabindex="8"/> 
            </div>
        </div>
        <div class="form-group mb-md col-md-3" >
            <label class="control-label mb-xs">Fecha de Final <small class="text-danger">(*)</small> </label>  
            <div class="input-group"> 
                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fData.fecha_final" required tabindex="8"/> 
            </div>
        </div>
        <div class="form-group mb-md col-md-6" >
            <label class="control-label mb-xs">Asignar una Especialidad <small class="text-danger">(*)</small> </label>
            <div class="input-group">
                <span class="input-group-btn ">
                    <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idespecialidad" placeholder="ID" readonly="true" required />
                </span>
                <input type="text" class="form-control input-sm" ng-model="fData.especialidad" placeholder="" typeahead-loading="loadingLocations" uib-typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedEspecialidad($item, $model, $label)" typeahead-min-length="1"/>
            </div>
            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
            <div ng-show="noResultsLCargo">
              <i class="fa fa-remove"></i> No se encontró resultados 
            </div>
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
                        <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" autocomplete="off" tabindex="107" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
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
                        
            </div>
        </div>


	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formDetalleCampania.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>