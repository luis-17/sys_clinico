<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formConvenio"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Titulo <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Tipo de Cliente" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Empresa Administradora <small class="text-danger">(*)</small>
			</label>
			<select class="form-control input-sm" ng-model="fData.sede_convenio" 
            	ng-options="item.id as item.descripcion for item in listaEmpresasAdmin" > </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Fecha Inicio <small class="text-danger">(*)</small>
			</label>
			<input class="form-control input-sm" ng-model="fData.fec_inicial" tabindex="2" data-inputmask="'alias': 'dd-mm-yyyy'" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Fecha Vigencia <small class="text-danger">(*)</small>
			</label>
			<input class="form-control input-sm" ng-model="fData.fec_vigencia" tabindex="2" data-inputmask="'alias': 'dd-mm-yyyy'" required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Nº Contrato <small class="text-danger">(*)</small>
			</label>
			<input class="form-control input-sm" ng-model="fData.contrato" placeholder="Registre Nº de contrato" tabindex="2" required />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formConvenio.$invalid">Guardar Cambios</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>