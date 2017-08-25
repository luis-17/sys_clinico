<style type="text/css">
	#gridEspecialidades .ui-grid-pager-panel .ui-grid-pager-container .ui-grid-pager-row-count-picker .ui-grid-pager-row-count-label{display: none!important;}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row">
    	<div class="form-group col-md-5 mb-md">
			<label class="control-label mb-n"> Personal de Salud: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].personal_salud }} </p>
		</div>
    	<div class="form-group col-md-2 mb-md">
			<label class="control-label mb-n"> Número de Documento: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].num_documento }} </p>
		</div>
		
		<div class="form-group col-md-2 mb-md">
			<label class="control-label mb-n"> Colegiatura Prof.: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].colegiatura }} </p>
		</div>
		<div class="form-inline col-md-3 mb-n" style="margin-top: -10px;"> 
			<img width="80px;" class="center-block img-responsive img-thumbnail  pull-right" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGrid[0].nombre_foto }}" /> 
		</div>
		<fieldset class="col-lg-5 col-xs-12">
			<div class="row">
				<div class="form-inline col-md-12  mb-xs">
					<label class="control-label mt">Especialidades no asignadas: </label>
					<button class="btn btn-success pull-right" ng-click="agregarEspecialidadesACesta();"> AGREGAR ESPECIALIDADES >> </button>
				</div>

				<!-- <button class="btn btn-success" style="position:absolute; top:113px;left:289px; padding: 4px 7px;z-index:100;" ng-click="agregarEspecialidadesACesta();"> AGREGAR ESPECIALIDADES >> </button> -->
				<div class="form-group mb-md col-md-12">
					<div id="gridEspecialidades" ui-grid="gridOptionsEspecialidades" ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div>
				</div>
			</div>
		</fieldset>
		
		<fieldset class="col-lg-7 col-xs-12">
			<div class="row">
				<div class="form-inline col-md-12 mb-xs">
					<label class="control-label mt">Especialidades asignadas: </label>
					<button class="btn btn-danger pull-right ml-xs" ng-click="anularEspecialidad();" ng-if="mySelectionEspecialidadesAddGrid.length > 0"> <i class="fa fa-times-circle"></i>  ANULAR </button>
					<button class="btn btn-info pull-right ml-xs" ng-click="habilitarEspecialidad();" ng-if="mySelectionEspecialidadesAddGrid.length > 0"> <i class="fa fa-check"></i>  HABILITAR </button>
					<button class="btn btn-default pull-right ml-xs" ng-click="deshabilitarEspecialidad();" ng-if="mySelectionEspecialidadesAddGrid.length > 0"> <i class="fa fa-power-off"></i> DESHABILITAR </button>
				</div>
				<div class="form-group mb-md col-md-12">
					<div ui-grid="gridOptionsEspecialidadesAdd" ui-grid-selection ui-grid-edit ui-grid-resize-columns class="grid table-responsive fs-mini-grid scroll-x-none"style="height: 377px!important; overflow-x: hidden;"></div>
				</div>
			</div>
		</fieldset>
		
		
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>