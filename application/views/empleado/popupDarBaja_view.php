<div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDarBaja" novalidate >       
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Empresa</label>
            <select class="form-control input-sm" ng-model="fData.empresaadmin" ng-options="item as item.descripcion for item in listaEmprDarBaja" ng-change='cambiocontrato(fData.empresaadmin)'> </select>
        </div>
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Condición Laboral</label>
            <!--<select class="form-control input-sm" ng-model="fData.condicion_laboral" ng-options="item as item.descripcion for item in listaCondicionLaboral" disabled> </select>-->
            <input type="text" class="form-control input-sm" ng-model="fData.cond_laboral"  readonly/> 
        </div>
        <div class="form-group mb-md col-md-6" >
            <label class="control-label mb-xs">Cargo</label>
            <div class="input-group">
                <span class="input-group-btn ">
                    <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idcargo" placeholder="ID" readonly />
                </span>
                <input type="text" class="form-control input-sm" ng-model="fData.cargo" readonly/> 
            </div>
        </div>
        <div class="form-group mb-md col-sm-6" >
            <label class="control-label mb-xs"> Sueldo </label>
            <input type="text" class="form-control input-sm" ng-model="fData.sueldo" placeholder="Sueldo S/. " readonly/> 
        </div>
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Fecha Ingreso</label>
            <input type="text" class="form-control input-sm" ng-model="fData.fecha_ingreso" data-inputmask="'alias': 'dd-mm-yyyy'" readonly="true"/> 
        </div>
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Fecha de Cese <small class="text">(*)</small></label>
            <input type="text" class="form-control input-sm" ng-model="fData.fecha_cese" data-inputmask="'alias': 'dd-mm-yyyy'" required placeholder="Fecha de Cese"/> 
        </div>
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Fecha Inicio Contrato </label>
            <input type="text" class="form-control input-sm" ng-model="fData.fecha_inicio_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" readonly/> 
        </div>
        <div class="form-group mb-md col-sm-6">
            <label class="control-label mb-xs"> Fecha Fin Contrato </label>
            <input type="text" class="form-control input-sm" ng-model="fData.fecha_fin_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" readonly/> 
        </div>
        <div class="form-group mb-n col-sm-6"> 
            <div class="input-group" style="font-size: 13px;"> 
                <label> <input type="checkbox" ng-model="fData.contrato_vigente" ng-true-value="1" ng-false-value="2" disabled /> 
                    <small style="display: block; line-height: 1;"> ¿Es Contrato Vigente? </small> 
                </label>
            </div>
        </div>
        
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formDarBaja.$invalid" tabindex="6">Dar de Baja</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="7">Cancelar</button>
</div>