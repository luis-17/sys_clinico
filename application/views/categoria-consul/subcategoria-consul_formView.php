<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">	
    <form class="row" name="formSubCategoriaConsul"> 
	    <div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> Nombre de Subcategoría : </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.descripcion_scco" placeholder="Ingrese nombre de subcategoría" required tabindex="1" focus-me/>
		</div>

		<div class="form-group mb-sm mt-md pt-xs col-md-5 col-sm-12"> 
            <input type="button" class="btn btn-info col-md-12 btn-sm" ng-disabled="formSubCategoriaConsul.$invalid" ng-click="agregarItem(); $event.preventDefault();" tabindex="3" value="Agregar" /> 
        </div>

		<div class="form-group mb-md col-md-12">
			<label class="control-label">Agregar Subcategoría : </label>
			<div ui-grid="gridOptionsSubAlmacen" ui-grid-edit ui-grid-selection ui-grid-pagination ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>