<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAperturaCaja"> 
		
		<div class="form-group mb-md col-md-12" style="text-align:right;" >
			<label class="control-label mb-xs"> Responsable: </label> 
			<h4 class="m-n text-info"> {{ fSessionCI.nombres + ' ' + fSessionCI.apellido_paterno + ' ' + fSessionCI.apellido_materno }} </h4>
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Fecha Apertura</label>
			<input type="text" placeholder="Fecha de apertura" class="form-control input-sm mask" style="font-weight:bold;" 
					ng-model="fData.fecha_apertura" data-inputmask="'alias': 'dd-mm-yyyy'" required tabindex="1" focus-me/> 
		</div>

		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Caja <small class="text-danger">(*)</small> </label> 
			<select class="form-control input-sm" ng-model="cajaChica" ng-options="item as item.nombre for item in listaCajas" required tabindex="2"  ng-readonly="true" ng-change="cargarSaldoAnterior(fData.cajaChica);"> </select>
			<!-- <select class="form-control input-sm" ng-model="fData.idcajachica" ng-options="item.idcajachica as item.nombre for item in listaCajas" required tabindex="1" focus> </select> -->
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> N° de Cheque <small class="text-danger">(*)</small> </label> 
			<input type="text" class="form-control input-sm" required ng-model="fData.numero_cheque" placeholder="N° de Cheque"  ng-readonly="true" tabindex="3" />
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> Saldo anterior <small class="text-danger">(*)</small>  </label>
			<div class="input-group">
				<span class="input-group-addon input-sm">S/.</span>
				<!-- <input type="text" class="form-control input-sm" required ng-model="fData.saldo_anterior" placeholder="Saldo anterior" ng-readonly="true" tabindex="3" /> --> 
				<input type="text" class="form-control input-sm" required ng-model="fData.saldo_anterior" placeholder="Saldo anterior" tabindex="4" ng-readonly="true" ng-change="calcularMontoInicio();" /> 

			</div>
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> Monto de cheque <small class="text-danger">(*)</small> </label>
			<div class="input-group">
				<span class="input-group-addon input-sm">S/.</span>
				<input type="text" class="form-control input-sm" required ng-model="fData.monto_cheque" placeholder="Monto de cheque" ng-readonly="true" ng-change="calcularMontoInicio();" tabindex="5" />
			</div>
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> Monto de inicio <small class="text-danger">(*)</small> </label>
			<div class="input-group">
				<span class="input-group-addon input-sm">S/.</span>
				<input type="text" class="form-control input-sm" ng-model="fData.monto_inicial" placeholder="Monto inicial" ng-readonly="true" tabindex="6" />
			</div>
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Observaciones/Comentarios </label> 
			<textarea class="form-control input-sm" ng-model="fData.observaciones_acc" placeholder='Observaciones/Comentarios' tabindex="7" >  </textarea>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAperturaCaja.$invalid" tabindex="8">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel(); $event.preventDefault();" tabindex="9">Cancelar</button>
</div>