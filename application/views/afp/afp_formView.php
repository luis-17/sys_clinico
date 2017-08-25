<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCargo"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">DESCRIPCIÓN <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Digite la descripción" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">A. OBLIG </label>
			<input type="text" class="form-control input-sm" ng-model="fData.a_oblig" placeholder="Digite el porcentaje"  tabindex="2"  />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">COMISION </label>
			<input type="text" class="form-control input-sm" ng-model="fData.comision" placeholder="Digite el porcentaje" tabindex="3" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">P. SEGURO </label>
			<input type="text" class="form-control input-sm" ng-model="fData.p_seguro" placeholder="Digite el porcentaje" tabindex="4"   />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">COMISION M. </label>
			<input type="text" class="form-control input-sm" ng-model="fData.comision_m" placeholder="Digite el porcentaje" tabindex="5"   />
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCargo.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>