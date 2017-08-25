<div class="modal-header">
  <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body" ng-controller="salidasAlmacenController">
	<div class="well well-transparent boxDark col-xs-12 m-n">
		<div class="row">
			<div class="form-group mb-md col-md-5" >
				<label class="control-label mb-xs"> Reactivo - Insumo <small class="text-danger">(*)</small></label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input id="idtemporalreactivoInsumo" type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.temporal.idreactivoInsumo" placeholder="ID" tabindex="9" ng-enter="obtenerReactivoInsumoPorCodigo(); $event.preventDefault();" min-length="1" />
					</span>
					<input id="temporalreactivoInsumo" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.reactivoInsumo" placeholder="Ingrese el Reactivo-Insumo o Click en Seleccionar" typeahead-loading="loadingLocationsReaIns" uib-typeahead="item as item.descripcion for item in getreactivoInsumoAutocomplete($viewValue)" typeahead-on-select="getSelectedReactivoInsumo($item, $model, $label)" typeahead-min-length="2" tabindex="10"/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupReactivoInsumo('md')">Seleccionar</button>
					</span>
				</div>
				<i ng-show="loadingLocationsReaIns" class="fa fa-refresh"></i>
				<div ng-show="noResultsLD">
					<i class="fa fa-remove"></i> No se encontró resultados 
				</div>
			</div>
			<div class="form-group mb-md col-md-1 col-sm-6">
				<label class="control-label mb-xs text-danger"> Stock </label>
				<input id="temporalStock" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.stock" tabindex="88" placeholder="Cantidad" disabled="true" /> 
			</div>
			<div class="form-group mb-md col-md-1 col-sm-6">
				<label class="control-label mb-xs"> Cant. <small class="text-danger">(*)</small></label>
				<input id="temporalCantidad" type="number" class="form-control input-sm" ng-model="fDataAlmacen.temporal.cantidad" tabindex="11" placeholder="Cantidad" /> 
			</div>
			<div class="form-group mb-md col-md-2 col-sm-6">
				<label class="control-label mb-xs"> Unidad </label>
				<input type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.unidadLaboratorio" disabled="true" /> 
			</div>
			<div class="form-group mb-sm mt-md col-md-2 col-sm-12"> 
				<div class="btn-group" style="min-width: 100%">
					<a href="" class="btn btn-info-alt" tabindex="13" ng-click="agregarItemEdicion(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
				</div>
				<!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
			</div>
		</div>
	</div>
  	<div class="panel-body">
    	<div class="form-group mb-md col-xs-12 pt-md">
   			<ul class="form-group demo-btns">
   				<li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
   				<li class="pull-right" ng-if="mySelectionDetalleSalidaGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnularDetalleSalida()'>Anular</button></li>
    		</ul>
	    	<div ui-grid="gridOptionsDetalleSalida" ui-grid-pagination ui-grid-selection class="grid table-responsive"></div> 
    	</div>
  	</div>
</div> 
<div class="modal-footer"> 
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCliente.$invalid" > Seleccionar </button>  -->
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div>