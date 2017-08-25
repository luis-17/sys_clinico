<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row"> 
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Nombre</label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre" placeholder="Registre el nombre" focus-me />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Descripcion</label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre la descripción" ></textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>