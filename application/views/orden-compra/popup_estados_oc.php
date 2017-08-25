<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° ORDEN: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGridOC[0].orden_compra }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> FECHA DE CREACION: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGridOC[0].fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> PROVEEDOR: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGridOC[0].razon_social }} </p> 
		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-4 col-sm-6 col-xs-12">
            <label class="control-label mb-xs"> ÁREA DE INTERÉS </label>
            <select ng-disabled="disabledListaAreasOC" class="form-control input-sm" ng-model="fData.area_interes" ng-options="item as item.descripcion for item in metodos.listaAreasOC" > </select>
        </div>
        <div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> CAMBIAR A ESTADO: </label>
			<p class="{{ fData.classEstado }}" style="font-weight: bold;"> {{ fData.estado_cambio }} </p> 
		</div>
	</div>
	<div class="row">
		<div class="form-group col-xs-12">
            <label class="control-label mb-xs"> COMENTARIOS/OBSERVACIONES </label>
            <textarea class="form-control input-sm" ng-model="fData.comentario" > </textarea>
        </div>
	</div>
</div> 
<div class="modal-footer"> 
	<button class="btn btn-primary" ng-click="aceptar();" > ACEPTAR </button> 
    <button class="btn btn-warning" ng-click="cancel();" > SALIR </button> 
</div> 