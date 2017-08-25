<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAfeccionesMedicas"> 
		<div class="form-group mb-md col-md-12">
			<div class="form-group col-md-4">
            	<label >Tipo de Afeccion <small class="text-danger">(*)</small> </label>
	            <select class="form-control input-sm" ng-model="fDataAfe.temporal.tipoAfeccion" ng-options="item.id as item.descripcion for item in listaBoolAfeccion" tabindex="1" ></select> 
			</div>
			<div class="form-group col-md-4">
				<label >Descripcion Afeccion <small class="text-danger">(*)</small> </label>
				<input ng-if="accion=='reg'" type="text" ng-model="fDataAfe.temporal.descripcion" placeholder="Registre el nombre del paquete" class="form-control input-sm" tabindex="2" required focus-me />
				<input ng-if="accion=='edit'" type="text" class="form-control input-sm" ng-model="fDataAfe.temporal.descripcion" placeholder="Registre el nombre del paquete" tabindex="2" focus-me required /> 
			</div>
			<div class="form-group col-md-4">
				<label>.</label>
				<input type="button" class="btn btn-info col-md-12 btn-sm" style="vertical-align: bottom;" ng-click="agregarItemAfeccion(); $event.preventDefault();" tabindex="110" value="Agregar" /> 
			</div>
		</div>
        <div class="well well-transparent boxDark col-xs-12 m-n" >
            <div class="row">
                <div class="form-group col-xs-12 m-n">
                    <label class="control-label">Agregar al detalle: </label>
                        <div ui-if="gridOptionsAfe.data.length>0" 
                        ui-grid="gridOptionsAfe" ui-grid-cellNav ui-grid-resize-columns ui-grid-auto-resize ui-grid-selection class="grid table-responsive" style="overflow: hidden;" ></div>
                </div>
            </div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsAfe.data.length==0">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>