<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formEmpresa"> 
		<div class="form-group mb-md col-md-6" ng-show="verEmpresaAdmin">
			<label class="control-label mb-xs">	Empresa Administradora: </label>
			<label class="control-label mb-xs">{{empresaAdmin.empresa}}</label>
		</div>

		<div class="form-group mb-md col-md-6" ng-show="verEmpresaAdmin" ng-if="accion=='reg'" >
			<!-- <label class="control-label mb-xs"> Empresa <small class="text-danger">(*)</small> </label> -->
			<div class="input-group"> 
				¿Nueva Empresa?  <input type="checkbox" ng-model="fData.esnueva" ng-change="limpiarCampos();" > 
			</div>
		</div>


		<div class="form-group mb-md col-md-6 clear">
			<label class="control-label mb-xs">	Empresa O Razón Social <small class="text-danger">(*)</small> </label>
				<input ng-if="accion=='reg' && verEmpresaAdmin && !fData.esnueva" type="text" ng-model="fData.empresa" placeholder="Autocompletar empresa" 
					uib-typeahead="item as item.descripcion for item in getEmpresasAutocomplete($viewValue)"
					uib-typeahead-loading="loading" 
					typeahead-on-select="getSelectedEmpresa($item, $model, $label)"	
					ng-change="fData.ruc_empresa=null; fData.domicilio_fiscal=null; fData.representante_legal = null; fData.telefono=null; fData.banco=0; fData.cuenta=null; fData.cuenta_detraccion=null;" 			 
					class="form-control input-sm" tabindex="1" required  />

				<input ng-if="accion=='reg' && verEmpresaAdmin && fData.esnueva" type="text" class="form-control input-sm" ng-model="fData.empresa" placeholder="Registre la empresa nueva" tabindex="1" focus-me required /> 

				<input ng-if="accion=='reg' && !verEmpresaAdmin" type="text" class="form-control input-sm" ng-model="fData.empresa" placeholder="Registre la empresa" tabindex="1" focus-me required /> 
				
				<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fData.empresa" placeholder="Registre la empresa" tabindex="1" focus-me required /> 
				
		</div>		
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Nombre Corto </label>
			<input type="text" class="form-control input-sm" ng-model="fData.nombre_corto" placeholder="Registre un nombre corto" ng-disabled="!fData.esnueva" />
			<!-- <div class="input-group"> 
				<input type="text" class="form-control input-sm" ng-model="fData.ruc_empresa" placeholder="Registre su RUC" required ng-disabled="!fData.esnueva" ng-minlength="11" ng-maxlength="11"/>
				<span class="input-group-btn">
					<button class="btn btn-default btn-sm" type="button" ng-click="verPopupConsultarSUNAT()">CONSULTAR RUC</button>
				</span>
			</div> -->
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> RUC <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.ruc_empresa" placeholder="Registre su RUC" required ng-disabled="!fData.esnueva" ng-minlength="11" ng-maxlength="11"/>
			<!-- <div class="input-group"> 
				<input type="text" class="form-control input-sm" ng-model="fData.ruc_empresa" placeholder="Registre su RUC" required ng-disabled="!fData.esnueva" ng-minlength="11" ng-maxlength="11"/>
				<span class="input-group-btn">
					<button class="btn btn-default btn-sm" type="button" ng-click="verPopupConsultarSUNAT()">CONSULTAR RUC</button>
				</span>
			</div> -->
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Domicilio Fiscal <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.domicilio_fiscal" placeholder="Registre su domicilio fiscal" required ng-disabled="!fData.esnueva" />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Representante Legal <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.representante_legal" placeholder="Registre su representante legal" required ng-disabled="!fData.esnueva" />
		</div>
		<!-- <div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Sede <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idsede" ng-options="item.id as item.descripcion for item in listaSede" required tabindex="2"> </select>
		</div> -->
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Teléfono </label>
			<input type="text" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" ng-disabled="!fData.esnueva"/>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Banco  </label> 
			<select class="form-control input-sm" ng-model="fData.banco" ng-options="item as item.descripcion for item in metodosEmp.listaBancos" ng-disabled="!fData.esnueva" > </select> 
		</div>
		<div class="form-group mb-md pr-sm col-md-3">
			<label class="control-label mb-xs"> N° Cuenta   </label>
			<input type="text" class="form-control input-sm" ng-model="fData.cuenta" placeholder="Registre N° Cuenta" ng-disabled="!fData.esnueva" />
		</div>
		<div class="form-group mb-md pl-n col-md-3">
			<label class="control-label mb-xs"> N° Cuenta Detracción  </label>
			<input type="text" class="form-control input-sm" ng-model="fData.cuenta_detraccion" placeholder="Registre N° Cuenta de Detracción" ng-disabled="!fData.esnueva" />
		</div>

		<div class="form-group mb-md col-md-6" ng-show="!verEmpresaAdmin" >
			<!-- <label class="control-label mb-xs"> Empresa <small class="text-danger">(*)</small> </label> -->
			<div class="input-group"> 
				¿Empresa Admin?  <input type="checkbox" ng-model="fData.es_empresa_admin" ng-change="validacionEmpresaAdmin();" ng-disabled="modulo == 'egresos' || modulo == 'compras'"> 
			</div>
		</div>

		<!-- <div class="form-group mb-md col-md-6" ng-show="verEmpresaAdmin" ng-required="verEmpresaAdmin" >			
			<div class="input-group"> 
				¿Tiene Contrato? <small class="text-danger">(*)</small>  <input type="checkbox" ng-model="fData.tiene_contrato" > 
			</div>
		</div> -->

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formEmpresa.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>