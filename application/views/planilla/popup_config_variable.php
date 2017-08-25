<div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formVariablesLey" novalidate > 
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Última Actualización </label> 
            <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fDataVariables.fecha_registro" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" disabled />
        </div>

        <div class="form-group mb-md col-md-6 clear">
            <label class="control-label mb-xs">UIT (S/.)<small class="text-danger">(*)</small> </label> 
            <input tabindex="2" required type="number" class="form-control input-sm" ng-model="fDataVariables.uit" placeholder="Registre Unidad impositiva tributaria" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Remuneración Minima Vital (S/.)<small class="text-danger">(*)</small> </label> 
            <input tabindex="3" required type="number" class="form-control input-sm" ng-model="fDataVariables.rmv" placeholder="Registre venta" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">ESSALUD (%)<small class="text-danger">(*)</small> </label> 
            <input tabindex="4" required type="number" class="form-control input-sm" ng-model="fDataVariables.essalud" placeholder="Registre Porcentaje ESSALUD" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Asignación Familiar (%)<small class="text-danger">(*)</small> </label> 
            <input tabindex="5" required type="number" class="form-control input-sm" ng-model="fDataVariables.asignacion_familiar" placeholder="Registre Asignación familiar" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">ONP (%)<small class="text-danger">(*)</small> </label> 
            <input tabindex="5" required type="number" class="form-control input-sm" ng-model="fDataVariables.onp" placeholder="Registre ONP" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Remuneración Max. Asegurable (S/.)<small class="text-danger">(*)</small> </label> 
            <input tabindex="5" required type="number" class="form-control input-sm" ng-model="fDataVariables.rma" placeholder="Registre Remuneració Max. Asegurable" />
        </div>
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formVariablesLey.$invalid" tabindex="6">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="7">Salir</button>
</div>