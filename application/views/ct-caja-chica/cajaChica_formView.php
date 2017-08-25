<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form name="formCajaChica">
    	<div class="row">
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre un nombre para Caja Chica" tabindex="1" focus-me required />
			</div>
			<div class="form-group mb-md col-md-6" > <label> Empresas / Sedes </label> 
		        <select class="form-control input-sm" ng-model="fData.idsedeempresa" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
		    </div> 
    	</div>
    	<div class="row">
    		<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs">Cat / SubCategoria <small class="text-danger">(*)</small> </label>
				<select class="form-control input-sm" ng-model="fData.idsubcatcentrocosto" ng-options="item.id as item.descripcion for item in listaSubCatCentroCosto" ng-change="cargarCentroCosto(fData.idsubcatcentrocosto,true);"> </select> 
			</div>
			<div class="form-group mb-md col-md-6" > <label> Centro Costo <small class="text-danger">(*)</small></label> 
		       <select class="form-control input-sm" ng-model="fData.idcentrocosto" ng-options="item.id as item.descripcion for item in listaCentroCosto" > </select> 
		    </div> 
    	</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formcajachica.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>