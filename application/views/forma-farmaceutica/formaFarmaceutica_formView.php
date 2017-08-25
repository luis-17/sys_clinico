<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formFormaFarmaceutica"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">Forma Farmaceutica <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Tipo de Zona" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs block">Acepta Caja?</label>
			<label class="radio-inline" >
				<input type="radio" name="optionsRadios" id="optionsRadios1" value="1" ng-model="fData.cajaUnidad">
				Si
			</label>

			<label class="radio-inline" >
				<input type="radio" name="optionsRadios" id="optionsRadios2" value="2" ng-model="fData.cajaUnidad">
				No
			</label>
		</div>
		<div class="form-group mb-xs col-md-6 pull-right">
			<p class="helpblock mb-xs"><b>Si : </b> Se registra por caja o por unidad.<br>
			<b>No : </b> Solo se registra por unidades</p>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formFormaFarmaceutica.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>