<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="" name="formCliente" novalidate>
    	<div class="row">
    		<div class="form-group mb-md col-md-3">
				<strong class="control-label mb-n">N° de Doc: </strong>
				<p class="help-block m-n">{{fDataVenta.cliente.num_documento}}</p>
			</div>
    		<div class="form-group mb-md col-md-9">
				<strong class="control-label mb-xs">Nombres y Apellidos: </strong>
				<p class="help-block m-n">{{fDataVenta.cliente.nombres}} {{fDataVenta.cliente.apellido_paterno}} {{fDataVenta.cliente.apellido_materno}}</p>
			</div>
		</div>
		<div class="row">			
			<div class="form-group mb-md col-md-4">
				<strong class="control-label mb-xs">Teléfono Móvil <small class="text-danger">(*)</small></strong>
				<input type="tel" class="form-control input-sm" ng-model="fDataVenta.cliente.celular" placeholder="Registre su celular" ng-pattern="/^[0-9]{9}$/" ng-minlength="9" ng-maxlength="9" tabindex="6" focus-me required />
			</div>
			<div class="form-group mb-md col-md-4">
				<strong class="control-label mb-xs">Teléfono Casa  </strong>
				<input type="tel" class="form-control input-sm" ng-model="fDataVenta.cliente.telefono" placeholder="Registre su teléfono" ng-pattern="/^[0-9]{7}$/" ng-minlength="7" ng-maxlength="7" tabindex="5" />
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-success" ng-click="imprimir()" tabindex="28" > <i class="fa fa-print"></i> Imprimir </button> -->
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCliente.$invalid" tabindex="32" > Aceptar </button>
    <button class="btn btn-warning" ng-click="cancel();" tabindex="33"> Cancelar </button>
</div>
