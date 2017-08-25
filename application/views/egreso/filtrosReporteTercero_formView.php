<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formFiltroReporte"> 
		<!-- <div class="col-xs-12"> --> 
            <div class="form-group mb-xs col-xs-12">
                <label class="control-label mb-xs"> MES </label>
                <select class="form-control input-sm" ng-model="fBusquedaFiltro.mes" ng-options="item as item.descripcion for item in metodos.listaMeses" tabindex="110"> </select> 
            </div>
            <div class="form-group mb-xs col-xs-12">
                <label class="control-label mb-xs"> AÑO </label>
                <input type="text" class="form-control input-sm" ng-model="fBusquedaFiltro.anio" tabindex="120" /> 
            </div>
		<!-- </div> -->
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formFiltroReporte.$invalid" tabindex="130"><i class="fa fa-save"> </i> PROCESAR </button>
    <button class="btn btn-warning" ng-click="cancel(); $event.preventDefault();" tabindex="140">Cerrar</button>
</div>