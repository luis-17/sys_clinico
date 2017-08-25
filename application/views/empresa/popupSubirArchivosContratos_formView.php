<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormDet }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSubirContrato" novalidate> 
		<div class="form-group mb-md col-sm-6">
			<label class="control-label mb-xs"> EMPRESA ADMIN </label>
			<p class="m-n"> {{contratos.empresaadmin}} </p>
		</div>

		<div class="form-group mb-md col-sm-6">
			<label class="control-label mb-xs"> EMPRESA </label>
			<p class="m-n"> {{fDataContrato.empresa}} </p>
		</div>

		<div class="form-group mb-md col-sm-12">
			<span class="time"> <b>Duración: </b> {{fDataContrato.fecha_inicio_str}} - {{ fDataContrato.fecha_fin_str }} </span>
		</div>

		<div class="form-group mb-md col-sm-12">
			<label class="control-label mb-xs"> Seleccione archivo a subir (Peso Máximo: 5MB)</label>
			<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
				<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="width: 100%; height: 150px;"></div>
				<div>
					<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
					<span class="btn btn-default btn-file p-n"><span class="fileinput-new">Seleccionar archivo</span> 
						<input type="file" name="file" file-model="fDataSubida.archivo" /> 
					</span>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptarSubida(); $event.preventDefault();" ng-disabled="formSubirContrato.$invalid">ACEPTAR</button>
    <button class="btn btn-warning" ng-click="cancelSubida()">CERRAR</button>
</div>