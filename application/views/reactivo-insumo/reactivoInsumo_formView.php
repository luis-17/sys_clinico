<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formReactivoInsumo"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Descripción <small class="text-danger">(*)</small> </label>
			<input ng-if="accion=='reg'" type="text" ng-model="fData.descripcion" placeholder="Registre el Reactivo-Insumo" uib-typeahead="descripcion for descripcion in getreactivoInsumoAutocomplete($viewValue)" 
				typeahead-loading="loading" class="form-control input-sm" tabindex="0" required focus-me />
			<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre el Reactivo-Insumo" tabindex="0" focus-me required /> 
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Tipo <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idtipo" ng-options="item.id as item.descripcion for item in listaFiltroTipo" tabindex="1" required ></select> 		
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Marca <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idmarca" ng-options="item.id as item.descripcion for item in listaMarcaLaboratorio" tabindex="2" required></select> 		
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Presentación <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idpresentacion" ng-options="item.id as item.descripcion for item in listaFiltroPresentacion" tabindex="3" required></select> 		
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Unidad <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idunidadlaboratorio" ng-options="item.id as item.descripcion for item in listaUnidadesLaboratorio" tabindex="4" required ></select> 		
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Precio </label>
			<input type="text" class="form-control input-sm" ng-model="fData.precio" placeholder="Registre Precio" tabindex="5" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Stock Minimo </label>
			<input type="text" class="form-control input-sm" ng-model="fData.stockminimo" placeholder="Registre Stock Min." tabindex="6" />	
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Stock Maximo </label>
			<input type="text" class="form-control input-sm" ng-model="fData.stockmaximo" placeholder="Registre Stock Max." tabindex="7" />	
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> Pruebas x Pres.</label>
			<input type="text" class="form-control input-sm" ng-model="fData.pruebas" placeholder="Registre Num.Pruebas" tabindex="8" />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formReactivoInsumo.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>