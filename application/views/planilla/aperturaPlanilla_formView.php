<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPlanillaMaster"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> EMPRESA: </label> 
            <p class="help-block m-n" > {{fData.empresa.descripcion}} </p> 
        </div>
        <div class="form-group mb-md col-md-12">
            <label class="control-label mb-xs">PLANILLA: <small class="text-danger">(*)</small> </label>
            <select class="form-control input-sm" ng-model="fData.planillaMaster" ng-change="cargaNombrePlanilla();" required focus
                        ng-options="item.descripcion for item in listaPlanillasMaster" ng-disabled="typeEdit" > </select> 
        </div>
        <div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> DESCRIPCIÓN: <small class="text-danger">(*)</small></label> 
            <input type="text" class="form-control input-sm" ng-model="fData.descripcion" tabindex="1" required disabled />
        </div>

        <div class="form-group mb-md col-md-4 pr-n">
            <label class="control-label mb-xs"> SELECCIONAR PERIODO: <small class="text-danger">(*)</small></label> 
            <select class="form-control input-sm" ng-model="fData.periodoPlanilla" ng-change="cargaNombrePlanilla();" required 
                        ng-options="item.descripcion for item in comboMeses" > </select> 
        </div>
        <div class="form-group mb-md col-md-2 pl-xs" style="top:24px;">
            <select class="form-control input-sm" ng-model="fData.anioPlanilla" ng-change="cargaNombrePlanilla();" required 
                        ng-options="item.descripcion for item in comboAnios" > </select> 
        </div>

        <div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> DESDE: <small class="text-danger">(*)</small></label> 
			<input type="text" class="form-control input-sm" ng-model="fData.desde" data-inputmask="'alias': 'dd-mm-yyyy'" readonly="true" tabindex="10" required />
        </div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> HASTA: <small class="text-danger">(*)</small></label> 
            <input type="text" class="form-control input-sm" ng-model="fData.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" readonly="true" tabindex="20" required /> 
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPlanillaMaster.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>