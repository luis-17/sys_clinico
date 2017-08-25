<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° ORDEN: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDataDetalle.orden_compra }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> FECHA DE CREACION: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDataDetalle.fecha_movimiento }} </p> 
		</div>
		<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> PROVEEDOR: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDataDetalle.razon_social }} </p> 
		</div>
	</div>
	<div class="row">
		<div class="form-group col-xs-12 text-center mb-xs">
			<h3 class="mt-n" style="font-weight: bold;"> {{ fDataDetalle.estado }}
				<span style="font-size: 60px;" class="block" ng-bind-html="fDataDetalle.strHtml"></span>
				<small style="margin-top: -8px;" class="block"> {{ fDataDetalle.fecha_estado }} </small> 
			</h3>
        </div>
	</div>
	<div class="row">
		<div class="form-group col-xs-12 mb-md">
            <label class="control-label mb-xs"> COMENTARIOS/OBSERVACIONES </label>
            <textarea disabled="true" class="form-control input-sm" ng-model="fDataDetalle.comentario" > </textarea>
        </div>
	</div>
	<div class="row">
		<div class="form-group col-xs-12 mb-md text-right">
            <button class="btn btn-danger" ng-click="deshacerAccionEstado();" > <i class="fa fa-undo"></i> DESHACER ESTE CAMBIO </button> 
        </div>
	</div>
</div> 
<div class="modal-footer"> 
	<!-- <button class="btn btn-primary" ng-click="aceptar();" > ACEPTAR </button>  -->
    <button class="btn btn-warning" ng-click="cancel();" > SALIR </button> 
</div>