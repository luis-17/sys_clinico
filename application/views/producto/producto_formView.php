<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formProducto"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Nombre del Producto <small class="text-danger">(*)</small> </label>
				<input ng-if="accion=='reg'" type="text" ng-model="fData.producto" placeholder="Registre el nombre del producto" uib-typeahead="descripcion for descripcion in getProductosAutocomplete($viewValue)" 
					typeahead-loading="loading" class="form-control input-sm" tabindex="1" required focus-me autocomplete ="off"/>
				<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.producto" placeholder="Registre el nombre del producto" tabindex="1" focus-me required /> 
		</div>
		<!-- <div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Precio Base <small class="text-danger">(*)</small> </label>
			<input type="number" class="form-control input-sm" ng-model="fData.precio" placeholder="Registre el precio" tabindex="2" required /> 
		</div> -->
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">Categoria <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idtipoproducto" ng-options="item.id as item.descripcion for item in listaTiposProducto" tabindex="3" required> </select>
		</div>
		<!-- <div ng-if="accion=='edit'" class="form-group mb-md col-md-6" > -->
		<div class="form-group mb-md col-md-6" > 
			<label class="control-label mb-xs"> Especialidad </label> 
			<div class="input-group"> 
				<span class="input-group-btn "> 
					<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idespecialidad" placeholder="ID" readonly="true" /> 
				</span> 
				<input type="text" class="form-control input-sm" ng-model="fData.especialidad" placeholder="" typeahead-loading="loadingLocations" ng-change="getClearInputSoloEspecialidad();" 
					typeahead="item as item.descripcion for item in getSoloEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedSoloEspecialidad($item, $model, $label)"/> 
			</div>
			<i ng-show="loadingLocations" class="fa fa-refresh"></i>
            <div ng-show="noResultsSoloEspecialidad"> <i class="fa fa-remove"></i> No se encontró resultados </div> 
		</div>
		<div class="form-group mb-md col-md-6" > 
			<div class="input-group checkbox-inline mt-lg" style="font-size: 15px;"> 
				<label> 
					<input type="checkbox" name="productocampania" ng-disabled="fData.newProduct" ng-model="fData.solo_para_campania" ng-true-value="1" ng-false-value="2" /> 
					<small style="display: block; "> ¿Es Producto de Campaña? </small> 
				</label>
			</div>
		</div>
		<div class="form-inline col-md-12"> <hr> </div>
		<div class="form-inline col-md-5 mt-sm">
			<label class="control-label">Agregar Precios:</label>
		</div>
		<!-- <div ng-if="accion=='reg'" class="form-group col-md-7 mb-sm" >
			<input type="text" ng-change="buscar()" class="form-control pull-right" ng-model="fData.searchText" 
				placeholder="Busque Especialidad" style="width: 92%;" tabindex="4" />
		</div> -->
		<div class="form-group mb-md col-md-12"> 
			<div ui-grid="gridOptionsSedeEmpresa" ui-grid-pagination ui-grid-resize-columns ui-grid-auto-resize ui-grid-edit ui-grid-move-columns class="grid table-responsive"></div> 
		</div>

		<!-- <div class="form-inline col-md-12"> <hr> </div>
		<div class="form-inline col-md-5 mt-sm">
			<label class="control-label">Agregar Sede y Precio: </label>
		</div>
		<div class="form-group mb-md col-md-12"> 
			<div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ></div>
		</div> -->
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formProducto.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>