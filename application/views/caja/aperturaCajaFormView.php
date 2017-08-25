<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAperturaCaja"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Caja <small class="text-danger">(*)</small> </label> 
			<select class="form-control input-sm" ng-model="fData.idcajamaster" ng-options="item.id as item.descripcion for item in listaCajaMaster" required tabindex="1" > </select>
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Descripción </label> 
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder='Digite la descripción' tabindex="2" >  </textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAperturaCaja.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel(); $event.preventDefault();">Cancelar</button>
</div>