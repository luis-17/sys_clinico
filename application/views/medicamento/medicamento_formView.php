<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formMedicamento">
    	<div ng-if="modulo!='solicitudFormula'" class="form-group mb-md col-md-2">
			<label class="control-label mb-xs"> Cod. Barra </label>
			<input type="text" class="form-control input-sm" ng-model="fData.codigo_barra" placeholder="Código de Barra" focus-me tabindex="90" /> 
		</div> 
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs"> Denominación <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.medicamento" placeholder="Registre Medicamento" tabindex="100" required />
		</div>
		<div ng-if="modulo!='solicitudFormula'">
			<div class="form-group mb-md col-md-3">
				<label class="control-label mb-xs"> Reg. Sanitario </label>
				<input type="text" class="form-control input-sm" ng-model="fData.registro_sanitario" placeholder="Registro Sanitario" tabindex="103" /> 
			</div>
			<div class="form-group pt-md mt-sm col-md-3">
		        <input type="checkbox" ng-model="fData.excluyeigv" tabindex="104" > Excluye IGV
			</div>
			
			<!-- <div class="form-group mb-md col-md-6" ng-if="fData.generico == 1">
				<label class="control-label mb-xs"><a ng-click="nuevaPresentacionGenerico('md')">Presentación</a><small class="text-danger">(*)</small> </label>
				<select required class="form-control input-sm"  ng-model="fData.idpresentacion" ng-options="item.id as item.descripcion for item in listaPresentacionGenerico" tabindex="110"></select>
			</div> -->
			<!-- <div class="form-group mb-md col-md-6" ng-if="fData.generico == 2"> -->
			<div class="form-group mb-md col-md-6" >
				<label class="control-label mb-xs"><a ng-click="nuevaPresentacionMarca('md')">Presentación</a><small class="text-danger" ng-if="fData.idtipoproducto != 22">(*)</small> </label>
				<select ng-required="fData.idtipoproducto != 22" class="form-control input-sm"  ng-model="fData.idpresentacion" ng-options="item.id as item.descripcion for item in listaPresentacionMarca" tabindex="115"></select>
			</div>
			<div class="form-group mb-md col-md-3">
				<label class="control-label mb-xs"><a ng-click="nuevoLaboratorio('md')">Laboratorio</a></label> 
				<input id="laboratorio" type="text" class="form-control input-sm" ng-model="fData.laboratorio" placeholder="Digite Laboratorio para autocompletar" tabindex="120" typeahead-min-length="2" 
					typeahead-loading="loadingLocationsDpto" uib-typeahead="item as item.descripcion for item in getLaboratoriosAutocomplete($viewValue)" typeahead-on-select="getSelectedLaboratorio($item, $model, $label);" autocomplete ="off" /> 
			</div>
			<div class="form-group mb-md col-md-3">
				<label class="control-label mb-xs"> Tipo </label>
				<select class="form-control input-sm"  ng-model="fData.idtipoproducto" ng-options="item.id as item.descripcion for item in listaTipoProductos" tabindex="135"></select>
			</div>
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"><a ng-click="nuevaMedida('md')">Medida de Concentración </a></label>
				<div class="input-group" > 
					<input type="text" class="form-control input-sm mr" ng-model="fData.val_concentracion" placeholder="Digite Valor" tabindex="123" style="width: auto;" /> 
					<select class="form-control input-sm"  ng-model="fData.idmedidaconcentracion" ng-options="item.id as item.descripcion for item in listaMedidasConcentracion" tabindex="125" style="width: 272px;"></select>
				</div>
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs"> Contenido <small class="text-danger" ng-if="fData.idtipoproducto != 22">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.contenido" placeholder="Digite Valor" ng-required="fData.idtipoproducto != 22" tabindex="128"/> 
			</div>
			<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs"><a ng-click="nuevaCondicionVenta('md')"> Condición de Venta</a> <small class="text-danger" ng-if="fData.idtipoproducto != 22">(*)</small> </label>
				<select ng-required="fData.idtipoproducto != 22" class="form-control input-sm"  ng-model="fData.idcondicionventa" ng-options="item.id as item.descripcion for item in listaCondicionesVenta" tabindex="130"></select>
			</div>
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"><a ng-click="nuevaViaAdministracion('md')"> Via de Administración </a> <small class="text-danger" ng-if="fData.idtipoproducto != 22">(*)</small> </label>
				<select ng-required="fData.idtipoproducto != 22" class="form-control input-sm"  ng-model="fData.idviaadministracion" ng-options="item.id as item.descripcion for item in listaViasAdministracion" tabindex="135"></select>
			</div>
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"><a ng-click="nuevaFormaFarmaceutica('md')"> Forma Farmacéutica </a> <small class="text-danger" ng-if="fData.idtipoproducto != 22">(*)</small> </label>
				<select ng-required="fData.idtipoproducto != 22" class="form-control input-sm"  ng-model="fData.idformafarmaceutica" ng-options="item.id as item.descripcion for item in listaFormasFarmaceuticas" tabindex="140"></select>
			</div>
		</div>

		<div class="form-group mb-sm col-md-12" ng-if="accion == 'reg'"> 
			<label class=""> 
				<input type="checkbox" class="" ng-model="fData.agregarMedicamento" ng-true-value="'si'" ng-false-value="'no'" tabindex="105" /> 
					<span class="text-primary"> AGREGAR MEDICAMENTO A ALMACEN </span> 
			</label>
			<span></span>
		</div>
		<div class="form-group mb-md col-md-12" ng-if="fData.agregarMedicamento == 'si'"> 
			<label class="control-label mb-xs text-default text-right block"> <strong> EDITE EL PRECIO DEL PRODUCTO </strong> <small class="text-info"> Con doble click en la celda </small> </label>
			<div  ui-grid="gridOptionsAlmacenes" ui-grid-edit ui-grid-move-columns 
				ui-grid-auto-resize class="grid table-responsive fs-mini-grid" ng-style="getTableHeight();">
				<div class="waterMarkEmptyData" style="font-size: 20px; top: 70px;" ng-if="!gridOptionsAlmacenes.data.length"> No se encontraron almacenes. </div>
			</div>
		</div>
		<!-- <div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">Descripcion </label>
			<textarea class="form-control input-sm" ng-model="fData.descripcion" placeholder="Ingrese una descripción" tabindex="3" rows="10"></textarea>
		</div> --> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formMedicamento.$invalid" tabindex="150">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel(); $event.preventDefault();" tabindex="152">Cancelar</button>
</div>