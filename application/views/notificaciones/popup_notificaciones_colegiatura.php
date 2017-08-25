<div class="modal-header">
    <h4 class="modal-title" style="font-size: 22px"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formNotificacionesColegiatura" novalidate > 
        <div class="form-group mb-md col-md-12">
            <div class="form-group mb-md col-md-12">
                <span class="notification-icon" style="background: #f36c60; height: 40px; width: 40px;
                display: block; border-radius: 21px; ">
                    <i class="{{fDataAviso.icono}}" style="display: block; text-align: center; line-height: 40px; 
                    width: 40px; color: #fff;"></i>
                </span>
            </div>

            <div class="form-group mb-md col-md-12" >
                <strong class="mb-xs"> Empleado: </strong>
                <p class="help-block">{{fDataAviso.empleado}}</p>
            </div>
            <div class="form-group mb-md col-md-12" >
                <strong class="mb-xs"> Colegiatura: </strong>
                <p class="help-block">{{fDataAviso.colegiatura}}</p>
            </div>
            <div class="form-group mb-md col-md-8">
                <label class="control-label mb-xs">
                    <strong class="mb-xs">Fecha Caducidad: </strong><small class="text-danger">(*)</small> </label> 
                <input tabindex="2" type="text" class="form-control input-sm mask" ng-model="fDataAviso.fecha_caducidad" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required/>
            </div>
        </div>              
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formNotificacionesColegiatura.$invalid" tabindex="6">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="7">Salir</button>
</div>