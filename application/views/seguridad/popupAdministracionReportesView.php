<div class="modal-header pt-md pb-md">
	<h4 class="modal-title"> {{ titleFormAR }} </h4>
</div>
<div class="modal-body pt-sm">
    <form class="row"> 
		
		<div class="form-group col-md-1 mb-xs text-center">
			<img class="mt-xs" style="margin: auto; height: 50px;" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGrid[0].nombre_foto }}" />
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n">USUARIO: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].usuario }} </p>
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n">EMPLEADO: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].empleado }} </p>
		</div>
		<div class="col-md-6 col-sm-12"> 
			<div class="block text-info"> SELECCIONE EL REPORTE A AGREGAR </div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsReportes" ui-grid-pagination ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> 
				<div class="waterMarkEmptyData" ng-show="!gridOptionsReportes.data.length"> No se encontraron datos. </div>
			</div>
		</div> 
		<div class="col-md-6 col-sm-12"> 
			<div class="block text-info"> REPORTES DEL USUARIO: </div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsReportesAddU" ui-grid-pagination ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> 
				<div class="waterMarkEmptyData" ng-show="!gridOptionsReportesAddU.data.length"> No se encontraron datos. </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>-->
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>