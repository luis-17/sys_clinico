<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>

<div class="modal-body">  
	<form class="row" name="formAmbiente">   
    	<!--<div class="form-group col-md-4 mb-md ">
			<label class="control-label mb-n">	Sede: <small class="text-danger"></small> </label>
            <select class="form-control input-sm" ng-model="fDataAdd.sede" ng-options="item.id as item.descripcion for item in listaSede" required tabindex="2" readonly ></select> 
		</div>-->

		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Sede: </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.sede" readonly tabindex="1"  />
		</div>

		<div class="form-group col-md-4 mb-md ">
			<label class="control-label mb-n">	Categoría: <small class="text-danger">(*)</small> </label>
            <select class="form-control input-sm" ng-model="fDataAdd.categoriaConsul" ng-options="item as item.descripcion for item in listaCategoriaConsul" ng-change="cargaSubCategoriaConsul(fDataAdd.categoriaConsul.id);" required tabindex="2" focus-me></select> 
		</div>

		<div class="form-group col-md-4 mb-md ">
			<label class="control-label mb-n">	Subcategoría: <small class="text-danger">(*)</small> </label>
            <select class="form-control input-sm" ng-model="fDataAdd.subCategoriaConsul" ng-options="item as item.descripcion for item in listaSubCategoriaConsul" ng-change="evaluaCategoria(fDataAdd.categoriaConsul.id);" required tabindex="2"></select> 
		</div>

    	<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> Número ambiente: </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.numero_ambiente" placeholder="Ingrese número ambiente" required tabindex="1" maxlength="10" />
		</div>
		<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> Piso: </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.piso" placeholder="Ingrese piso" required tabindex="1" maxlength="10" />
		</div>

		<div class="form-group col-md-12 mb-md">
			<label class="control-label mb-n"> Comentario: </label>
			<textarea class="form-control " ng-model="fDataAdd.comentario" placeholder="Ingrese comentario" tabindex="1" ></textarea>
		</div>
	</form>
</div>
	
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAmbiente.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>

