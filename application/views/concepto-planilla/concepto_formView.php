<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formConcepto"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">DESCRIPCIÓN <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Digite la descripción" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">CATEGORÍA <small class="text-danger">(*)</small> </label>
		    <select class="form-control input-sm" ng-model="fData.categoria_concepto" ng-options="item as item.descripcion for item in listaCategoriaConceptos" tabindex="2" required> </select> 
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">CÓDIGO DE PLAN </label>
			<input type="text" class="form-control input-sm" ng-model="fData.codigo_plan" placeholder="Digite el código de plan" tabindex="2"  />
		</div>
		
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">CÓDIGO PLAME </label>
			<input type="text" class="form-control input-sm" ng-model="fData.codigo_plame" 
					placeholder="Digite el código plame" tabindex="3" />
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">NIVEL REPORTE <small class="text-danger">(*)</small> </label>
		    <select class="form-control input-sm" ng-model="fData.nivel_reporte" ng-options="item as item.descripcion for item in listaNivelReporte" tabindex="4" required> </select> 
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs">ABREVIATURA </label>
			<input type="text" class="form-control input-sm" ng-model="fData.abreviatura" placeholder="Digite la abreviatura" tabindex="5"  />
		</div>

		<div class="form-group mb-md col-md-6" ng-show="fData.categoria_concepto.tipo_concepto == '1'">
			<legend class="m-xs"><h5 class="m-n">Aportes Empleador</h5></legend>
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_essalud" tabindex="6" > ¿ESSALUD? 
				</div>
			</div>
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_sctr" tabindex="7" > ¿SCTR? 
				</div>
			</div>
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_senati" tabindex="8" > ¿SENATI? 
				</div>
			</div>		
		</div>

		<div class="form-group mb-md col-md-6" ng-show="fData.categoria_concepto.tipo_concepto == '1'">
			<legend class="m-xs"><h5 class="m-n">Descuentos Empleado</h5></legend> 
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_snp" tabindex="9" > ¿SNP?  
				</div>
			</div>		
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_spp" tabindex="10" > ¿SPP? 
				</div>
			</div>
			<div class="form-group mb-md mt-sm col-md-12">
				<div class="input-group"> 
					<input type="checkbox" ng-model="fData.si_5cat" tabindex="11" > ¿Quinta categoría? 
				</div>
			</div>		
		</div>		

		<!-- <div class="form-group mb-n mt-lg col-md-3">
			<div class="input-group"> 
				<input type="checkbox" ng-model="fData.es_calculable" tabindex="55"> ¿Es calculable? 
			</div>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">FORMULA </label>
			<input type="text" class="form-control input-sm" ng-model="fData.formula" 
					ng-disabled="!fData.es_calculable"	placeholder="Digite la formula" tabindex="60" />
		</div> -->
		
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formConcepto.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>