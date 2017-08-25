<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Empresa: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ gridOptions.data[0].empresa_admin }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Sede: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ gridOptions.data[0].sede }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Caja:</label>
			<p class="help-block mt-xs" style="font-weight: bold;">  N° {{ gridOptions.data[0].numero_caja }}</p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Usuario: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ gridOptions.data[0].username }} </p> 
		</div>
		<div class="">
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Caja en Fisico </label> 
				<input type="text" class="form-control input-sm" ng-model="fData.totalFisico" placeholder="Ingrese el monto físico" tabindex="101" ng-change="calcularDiferencia();"/>
			</div>
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Caja Sistema </label> 
				<input type="text" class="form-control input-sm" ng-model="fData.totalCaja" placeholder="" tabindex="103" readonly="true" />
			</div>
			
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Diferencia </label> 
				<input type="text" class="form-control input-sm {{fData.clase}}" ng-model="fData.diferencia" placeholder="" tabindex="105" readonly="true"/>
			</div>
			<div class="form-group mb-md col-md-9"> 
				<label class="control-label mb-xs"> Observaciones (opcional) </label> 
				<textarea ng-model="fData.observacion" class="form-control input-sm" placeholder="" tabindex="110"></textarea>
				<!-- <input type="text" class="form-control input-sm" ng-model="fData.observacion" placeholder="" tabindex="107" readonly="true"/> -->
			</div>
			<div class="form-group mb-md col-md-12"> 
				<p class="help-block mt-xs" style="font-weight: bold;"> * Todos los montos incluyen visa y mastercard </p>
			</div>
		</div>
	</div>
</div>

<div class="modal-footer"> 
    <button class="btn btn-primary" ng-click="aceptar()" > Confirmar </button>
    <button class="btn btn-warning" ng-click="cancel()" > Cancelar </button> 
</div> 