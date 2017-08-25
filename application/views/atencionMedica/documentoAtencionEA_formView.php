<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAvisoImportante" novalidate> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Título <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fDataDEA.titulo" placeholder="Título del Archivo" tabindex="1" focus-me required />
		</div>
		<!-- <div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Fecha Entregado (opcional) </label>
			<input tabindex="5" type="text" class="form-control input-sm mask" ng-model="fData.fecha_entrega" data-inputmask="'alias': 'dd-mm-yyyy'" />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Descripción (opcional) </label> 
			<textarea type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Descripción" tabindex="10" ></textarea>
		</div>  -->
		<div class="form-group mb-md col-sm-12">
			<label class="control-label mb-xs"> Seleccione archivo a subir (Peso Máximo: 5MB)</label>
			<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
				<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="width: 100%; height: 150px;"></div>
				<div>
					<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
					<span class="btn btn-default btn-file p-n"><span class="fileinput-new">Seleccionar archivo</span> 
						<input type="file" name="file" file-model="fDataDEA.archivo" /> 
					</span>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAvisoImportante.$invalid">ACEPTAR</button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>