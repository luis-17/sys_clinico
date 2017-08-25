<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormDet }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSubirComprobante" novalidate> 
		<!-- CLIENTE -->
		<div class="form-group mb-md col-sm-6">
			<strong class="control-label mb-n"> CLIENTE </strong>
			<p class="help-block m-n"> {{fDataPago.cliente}} </p>
		</div>

		<div class="form-group mb-md col-sm-3">
			<strong class="control-label mb-n"> N° DOCUMENTO </strong>
			<p class="help-block m-n"> {{fDataPago.num_documento}} </p>
		</div>
		<div class="form-group mb-md col-sm-3">
			<strong class="control-label mb-n"> CELULAR </strong>
			<p class="help-block m-n"> {{fDataPago.celular}} </p>
		</div>

		<!-- PAGO -->
		<div class="form-group mb-md col-sm-6">
			<strong class="control-label mb-n"> N° ORDEN </strong>
			<p class="help-block m-n"> {{fDataPago.orden_venta}} </p>
		</div>

		<div class="form-group mb-md col-sm-6">
			<strong class="control-label mb-n"> DESCRIPCION </strong>
			<p class="help-block m-n"> {{fDataPago.descripcion_cargo}} </p>
		</div>

		<div class="form-group mb-md col-sm-3">
			<strong class="control-label mb-n"> TOTAL </strong>
			<p class="help-block m-n"> {{fDataPago.total_a_pagar}} </p>
		</div>
		<div class="form-group mb-md col-sm-3">
			<strong class="control-label mb-n"> SUBTOTAL </strong>
			<p class="help-block m-n"> {{fDataPago.sub_total}} </p>
		</div>
		<div class="form-group mb-md col-sm-3">
			<strong class="control-label mb-n"> IGV </strong>
			<p class="help-block m-n"> {{fDataPago.total_igv}} </p>
		</div>	
		<div class="form-group mb-md col-sm-6">
			<strong class="control-label mb-n"> N° DE COMPROBANTE </strong>
			<input class="form-control input-sm help-block m-n" ng-model="fDataSubida.nro_comprobante" required ></input>
		</div>	

		<div class="form-group mb-md col-sm-12">
			<strong class="control-label mb-xs"> Seleccione archivo a subir (Peso Máximo: 1MB - Tipo: PDF)</strong>
			<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
				<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="width: 100%; height: 150px;"></div>
				<div>
					<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
					<span class="btn btn-default btn-file p-n"><span class="fileinput-new">Seleccionar archivo</span> 
						<input type="file" name="file" file-model="fDataSubida.archivo" /> 
					</span>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptarSubida(); $event.preventDefault();" ng-disabled="formSubirComprobante.$invalid">ACEPTAR</button>
    <button class="btn btn-warning" ng-click="cancelSubida()">CERRAR</button>
</div>