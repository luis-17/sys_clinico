<div class="modal-header pb-sm">
    <h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
    <form class="row" name="formSO">
        <div class="form-group col-md-12 m-n"><h4 class="m-n mb-sm">Operación : <strong>{{ titleOperacion }}</strong></h4></div>
        <div ng-class="operacionesSO.classEditPanel">
            <div class="form-group mb-md col-md-4">
                <label class="control-label mb-xs">Descripción : <span class="text-danger">(*)</span> </label>
                <input type="text" name="descripcion" class="form-control input-sm" ng-model="fDataSO.descripcion" placeholder="Agregar Descripcion" tabindex="1" required />
            </div>
            <div class="form-group mb-md col-md-4 pl-n">
                <label class="control-label mb-xs">Codigo : <span class="text-danger">(*)</span> </label>
                <input type="text" name="codigo" class="form-control input-sm" ng-model="fDataSO.codigo" placeholder="Agregar codigo" tabindex="2" required />
            </div>        
            <div class="form-group mb-n mt-lg col-md-4 pl-n">
                <button class="btn btn-primary btn-sm" type="button" ng-click="grabarSO(); $event.preventDefault();" ng-if="!operacionesSO.editarSOBool" tabindex="11" >Agregar</button>
                <button class="btn btn-primary btn-sm" type="button" ng-click="grabareditSO(); $event.preventDefault();" ng-if="operacionesSO.editarSOBool" tabindex="12" ng-disabled="formSO.$invalid">Grabar</button>
                <button class="btn btn-danger btn-sm" type="button" ng-click="cancelarSO(); $event.preventDefault();" ng-if="operacionesSO.editarSOBool" tabindex="13">Cancelar</button>
            </div>            
        </div>
        <div class="form-group mb-md col-md-12">
            <div ui-grid="gridOptionsSubOperaciones" ui-grid-pagination ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive" style="overflow: hidden;">
                <div class="waterMarkEmptyData" ng-show="!gridOptionsSubOperaciones.data.length"> No hay Datos </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()" tabindex="15">Cerrar</button>
</div>