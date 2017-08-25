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
	<div class="row" style="margin-top: 8px; padding-top: 10px; border-top:1px solid #e5e5e5;"> 
		<div class="p-n col-md-4 col-sm-6 col-xs-12" ng-repeat="(key, row) in listaAreasOC"> 
			<div class="form-group col-xs-12 text-center mb-xs"> 
				<h3 class="mt-n text-gray" style="font-weight: bold;"> {{ row.estado }} 
					<span style="font-size: 60px;" class="block" ng-bind-html="row.strHtml"></span> 
					<strong class="block f-14 text-gray" style="margin-top: -10px;"> {{row.area}} </strong> 
				</h3> 
				<p class="f-12 help-block" style="margin-top: -20px;"> {{ row.fecha_estado }} </p>
	        </div> 
	        <div class="form-group col-xs-12 mb-md"> 
	            <label class="control-label mb-xs"> COMENTARIOS </label> 
	            <textarea disabled="true" class="form-control input-sm" ng-model="row.comentario" > </textarea> 
	        </div>
		</div>
	</div>
	<div class="row">
		<div class="form-group col-xs-12">
            <label class="control-label m-n"> ENVIAR LA ORDEN DE COMPRA A ESTOS CORREO: </label>
            <small class="help-block m-n">Si desea agregar mas de un correo, sepárelos con punto y coma.</small>
            <input id="correoProveedor" class="form-control input-sm" ng-model="fDataDetalle.correo_proveedor" placeholder="Digite correo electrónico." focus-me /> 
        </div>
	</div>
</div> 
<div class="modal-footer"> 
	<button class="btn btn-primary" ng-click="aceptar();" > ENVIAR CORREO A PROVEEDOR </button> 
    <button class="btn btn-warning" ng-click="cancel();" > SALIR </button> 
</div> 