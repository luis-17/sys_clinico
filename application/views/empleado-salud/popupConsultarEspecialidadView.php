<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Número de Documento: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].num_documento }} </p>
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Personal de Salud: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].personal_salud }} </p>
		</div>
		<div class="form-inline col-md-4 mb-sm" style="margin-top: -10px;"> 
			<img width="80px;" class="center-block img-responsive img-thumbnail  pull-right" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGrid[0].nombre_foto }}" /> 
		</div> 
		<div class="form-inline col-md-5 mt-sm">
			<label class="control-label"> Especialidades del Personal de Salud: </label>
		</div>
		<div class="form-group col-md-7 mb-sm" >
			<input type="text" ng-change="buscar()" class="form-control pull-right" ng-model="searchText" 
				placeholder="Busque especialidad" focus-me style="width: 92%;" />
		</div>
		<div class="form-group mb-md col-md-12">
			<ul class="form-group demo-btns">
                <li class="pull-right" ng-if="mySelectionEspecialidadesGrid.length == 1"><button type="button" class="btn btn-danger" ng-click='btnAnularEspecialidadMedico()'>ANULAR</button></li>
            </ul>
			<div ui-grid="gridOptionsEspecialidades" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>