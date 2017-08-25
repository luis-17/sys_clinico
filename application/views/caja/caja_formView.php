<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCaja"> 
		<div class="form-group mb-md col-md-6" ng-show="accion == 'reg'">
			<label class="control-label mb-xs"> Empresa <small class="text-danger">(*)</small> </label> 
			<select class="form-control input-sm" ng-model="fData.idempresa" ng-options="item.id as item.descripcion for item in listaEmpresaAdmin" required tabindex="1" > </select>
		</div>
		<div class="form-group mb-md col-md-3">
			<label class="control-label mb-xs"> N° Caja <small class="text-danger">(*)</small> </label> 
			<input type="text" ng-change="fData.serie = (fData.numero | numberFixedLen:3)" class="form-control input-sm" ng-model="fData.numero" placeholder="N° de Caja" required ng-minlength="1" tabindex="2" />
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> N° Serie <small class="text-danger">(*)</small> </label> 
			<input type="text" class="form-control input-sm" required ng-model="fData.serie" placeholder="N° de Caja" disabled ng-minlength="3" tabindex="3" />
		</div>
		<div class="form-group mb-md col-md-3"> 
			<label class="control-label mb-xs"> N° Maq. Registradora </label> 
			<input type="text" class="form-control input-sm" ng-model="fData.maquina_registradora" placeholder="N° Caja Registradora" ng-minlength="3" tabindex="4" />
		</div>
		<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs"> Descripción </label> 
			<textarea class="form-control input-sm" ng-model="fData.caja" placeholder='Ejm: "Caja del primer piso"' tabindex="5">  </textarea>
		</div>
		<div class="form-group mb-md col-md-12" ng-if="accion == 'reg'" >
			<label class="control-label mb-xs text-default"> <strong> EDITE LOS NUMEROS DE LA SERIE </strong> <small class="text-info"> Con doble click en la celda </small> </label>
			<div ui-grid="gridOptionsTipoDocs" ui-grid-edit ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCaja.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel(); $event.preventDefault();">Cancelar</button>
</div>