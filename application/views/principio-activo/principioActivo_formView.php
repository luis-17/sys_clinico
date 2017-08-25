<link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPrincipioActivo"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Principio Activo <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Via de Administracion" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs block"> ¿Es componente de fórmula? <i class="fa fa-question-circle" tooltip=""></i></label>
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-red" style="position: relative;">
						<input icheck="minimal-red" type="radio" id="inlineradio1" value="0" ng-model="fData.es_componente_formula" checked="true">
					</div> No
				</label>
			</div>
			
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-blue" style="position: relative;">
						<input icheck="minimal-blue" type="radio" id="inlineradio2" value="1" ng-model="fData.es_componente_formula">
					</div> Si
				</label>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPrincipioActivo.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>