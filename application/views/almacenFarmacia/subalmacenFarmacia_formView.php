<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSubAlmacenFarmacia"> 
    	<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> Nombre de Almacen : </label>
			<input type="text" class="form-control input-sm" ng-model="fDataAdd.nombre_salm" placeholder="Ingrese nombre de Sub almacen" required tabindex="1" focus-me/>
		</div>
		<div class="form-group col-md-4 mb-md ">
			<label class="control-label mb-n">	Tipo SubAlmacen <small class="text-danger">(*)</small> </label>
            <select class="form-control input-sm" ng-model="fDataAdd.tiposubalmacen" ng-options="item.id as item.descripcion for item in listaTipoSubAlmacen" ng-change="evalua(fDataAdd.tiposubalmacen);" required tabindex="2"></select> 
		</div>
        <div class="form-group mb-sm mt-md pt-xs col-md-2 col-sm-12"> 
            <input type="button" class="btn btn-info col-md-12 btn-sm" ng-disabled="formSubAlmacenFarmacia.$invalid" ng-click="agregarItem(); $event.preventDefault();" tabindex="3" value="Agregar" /> 
        </div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label">Agregar Sub Almacen : </label>
			<div ui-grid="gridOptionsSubAlmacen" ui-grid-edit ui-grid-selection ui-grid-pagination ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formSubAlmacenFarmacia.$invalid">Aceptar</button>-->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>