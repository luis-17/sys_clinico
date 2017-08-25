<div class="modal-header">
    <h4 class="modal-title" > {{ titleForm }} </h4>
</div>
<div class="form-group mb-md mt-md col-md-12" style="border-bottom: 1px solid #e5e5e5">
    <div class="form-group mb-md col-md-1 p-n">
        <span class="notification-icon" style="background: #7986cb; height: 40px; width: 40px;
        display: block; border-radius: 21px; ">
            <i class="{{fDataAviso.icono}}" style="display: block; text-align: center; line-height: 40px; 
            width: 40px; color: #fff;"></i>
        </span>
    </div>
    <div class="form-group mb-md col-md-5 pl-sm" >
        <strong class="mb-xs"> Empleado: </strong>
        <p class="help-block">{{fDataAviso.empleado}}</p>
    </div>
    <div class="form-group mb-md col-md-3" >
        <strong class="mb-xs"> Fecha fin contrato: </strong>
        <p class="help-block">{{fDataAviso.fin_contrato}}</p>
    </div>  
</div>
<div class="modal-body">
    <form class="row" name="formNotificacionesColegiatura" novalidate >        
        <div class="col-md-offset-1 col-md-10 col-sm-12 " >
            <h5 class="mt-n mb-lg">AGREGAR CONTRATO</h5>
            <div class="row">
                <div ng-class="classEditPanel"> 
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Empresa <small style="color: red;">(*)</small> </label>
                        <select class="form-control input-sm" ng-model="fDataAviso.empresaadmin" ng-options="item as item.descripcion for item in listaEmpresaAdmin" tabindex="1" disabled> </select>
                    </div>
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Condición Laboral <small style="color: red;">(*)</small> </label>
                        <select class="form-control input-sm" ng-model="fDataAviso.condicion_laboral" ng-options="item as item.descripcion for item in listaCondicionLaboral" tabindex="2" required> </select>
                    </div>
                    <div class="form-group mb-md col-md-12" >
                        <label class="control-label mb-xs"> Asignar Cargo <small style="color: red;">(*)</small> </label>
                        <div class="input-group">
                            <span class="input-group-btn ">
                                <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAviso.idcargo" placeholder="ID" readonly="true" />
                            </span>
                            <input placeholder="Digite el cargo" autocomplete="off" ng-change="getClearInputCargo();" type="text" class="form-control input-sm" ng-model="fDataAviso.cargo" placeholder="" typeahead-loading="loadingLocations" uib-typeahead="item as item.descripcion for item in getCargoAutocomplete($viewValue)" typeahead-on-select="getSelectedCargo($item, $model, $label)" tabindex="3" required/> 
                        </div>
                        <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                        <div ng-show="noResultsLCargo"> <i class="fa fa-remove"></i> No se encontró resultados </div>
                    </div>
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Fecha Ingreso <small style="color: red;">(*)</small> </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAviso.fecha_ing" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" tabindex="4" required/> 
                    </div>
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Fecha Inicio Contrato <small style="color: red;">(*)</small> </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAviso.fecha_ini_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" tabindex="5" required/> 
                    </div>
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Fecha Fin Contrato <small style="color: red;">(*)</small> </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAviso.fecha_fin_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" tabindex="6" required/> 
                    </div>
                    <div class="form-group mb-md col-sm-6">
                        <label class="control-label mb-xs"> Sueldo </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAviso.sueldo" placeholder="Sueldo S/. " tabindex="7" ng-pattern="/^[0-9]*$/"/> 
                    </div>
                </div>
            </div>
        </div>             
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formNotificacionesColegiatura.$invalid" tabindex="8">Agregar contrato</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="9">Salir</button>
</div>