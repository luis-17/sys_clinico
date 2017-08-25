<div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formTipoCambio" novalidate > 
        <div class="form-group mb-md col-md-12">
            <label class="control-label mb-xs">Fecha de Cambio </label> 
            <input tabindex="1" type="text" class="form-control input-sm mask" ng-model="fDataCambio.fecha_cambio" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Compra <small class="text-danger">(*)</small> </label> 
            <input tabindex="2" id="clave" required type="text" class="form-control input-sm" ng-model="fDataCambio.compra" placeholder="Registre compra" />
        </div>
        <div class="form-group mb-md col-md-6">
            <label class="control-label mb-xs">Venta <small class="text-danger">(*)</small> </label> 
            <input tabindex="3" id="nuevoPass" required type="text" class="form-control input-sm" ng-model="fDataCambio.venta" placeholder="Registre venta" />
        </div>
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formTipoCambio.$invalid">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>