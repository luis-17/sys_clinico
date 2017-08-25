<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSubmotivo"> 
		<div class="form-group mb-md col-md-8">
			<label class="control-label mb-xs">Nombre <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre la sede" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-4">
			<label class="mt-lg"> 
                <small>  
                    <input type="checkbox" ng-model="fData.agregarAJefes" tabindex="2"/> Agregar a jefes 
                </small>  
            </label>
		</div>
		<div class="row mb-md col-md-12">
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs">Agregar SubMotivo </label>
				<input id="submotivo" type="text" class="form-control input-sm" ng-model="fData.temporal.submotivo" placeholder="Agregar SubMotivo" tabindex="3" />
			</div>
			<div class="form-group mb-n mt-lg col-md-6">
				<button class="btn btn-primary input-sm" ng-click="agregarSubMotivoItem(); $event.preventDefault();">Agregar</button>
			</div>
			<div class="form-group mb-md col-md-12">
				<div ui-grid="gridOptionsSubMotivoAdd" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive">
					<div class="waterMarkEmptyData" ng-show="!gridOptionsSubMotivoAdd.data.length"> No hay Datos </div>
				</div>

			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formSubmotivo.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>