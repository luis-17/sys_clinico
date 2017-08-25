<style type="text/css">
    .modal{z-index: 9999999999!important;}
</style>
<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formDetalleCampania"> 
        <div class="form-group col-xs-12 m-n">
            <div ui-grid="gridOptionsProductoAdd" ui-grid-selection ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;">
                <div class="waterMarkEmptyData" ng-show="!gridOptionsProductoAdd.data.length"> No hay Productos </div>
            </div>
        </div>
        <div class="form-inline col-md-4 pull-right">
            <div class="form-group mb-md mt-md col-md-12 col-sm-6">
                <label class="control-label mb-xs text-danger"><strong> Monto Total :</strong></label>
                <input type="text" class="form-control input-sm" style="font-weight:bold;color:green;" ng-model="fDataVenta.temporal.monto_total" placeholder="0.00" readonly="true" /> 
            </div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" tabindex="104" ng-click="aceptar(); $event.preventDefault();" ng-disabled="!gridOptionsProductoAdd.data.length">Guardar</button>
    <button class="btn btn-warning" tabindex="105" ng-click="cancel()">Salir</button>
</div>