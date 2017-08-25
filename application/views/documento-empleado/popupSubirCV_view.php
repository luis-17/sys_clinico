<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAvisoImportante" novalidate>		
		<div class="form-group mb-md col-sm-12">
			<label class="control-label mb-xs"> Seleccione archivo a subir (Peso Máximo: 5MB)<small class="text-danger"> Word o PDF</small></label>
			<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
				<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="width: 100%; height: 150px;"></div>
				<div>
					<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
					<span class="btn btn-default btn-file p-n"><span class="fileinput-new">Seleccionar archivo</span> 
						<input type="file" name="file" file-model="fData.archivo" /> 
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