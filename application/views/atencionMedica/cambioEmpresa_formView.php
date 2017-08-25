<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAvisoImportante" novalidate> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Empresa/Especialidad <small class="text-danger">(*)</small> </label>
			<select ng-options="item as item.descripcion for item in listaEMES" class="form-control input-sm" ng-model="fData.empresa_especialidad" tabindex="1" focus-me required > </select>
		</div> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Motivo (opcional) </label> 
			<textarea type="text" class="form-control input-sm" ng-model="fData.motivo" placeholder="Descripción" tabindex="10" ></textarea>
		</div> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAvisoImportante.$invalid">ACEPTAR</button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>