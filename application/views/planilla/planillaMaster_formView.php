<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPlanillaMaster"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">EMPRESA <small class="text-danger">(*)</small> </label>
          	<select class="form-control input-sm" ng-model="fData.empresa" ng-change="asignarDescripcion();" 
                        ng-options="item.descripcion for item in listaEmpresaAdmin" ng-disabled="typeEdit" > </select> 
        </div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">DESCRIPCIÓN <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Digite la descripción" tabindex="1" focus-me required />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPlanillaMaster.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>