<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formHorarioGeneral"> 
    	<div class="col-xs-8">
    		<div class="row">
				<div class="form-group col-xs-6 mb-md">
					<label class="control-label mb-n">DNI: </label>
					<p class="help-block mt-xs"> {{ mySelectionGridEM[0].num_documento }} </p>
				</div>
				<div class="form-group col-xs-6 mb-md">
					<label class="control-label mb-n">PERSONAL: </label>
					<p class="help-block mt-xs"> {{ mySelectionGridEM[0].personal }} </p>
				</div>
				<div class="form-group col-xs-6 mb-md">
					<label class="control-label mb-n">CARGO: </label>
					<p class="help-block mt-xs"> {{ mySelectionGridEM[0].cargo }} </p>
				</div>
				<div class="form-group col-xs-6 mb-md">
					<label class="control-label mb-n">EMPRESA: </label>
					<p class="help-block mt-xs"> {{ mySelectionGridEM[0].empresa }} </p>
				</div>
    		</div>
    	</div>
		<div class="form-group col-xs-4 mb-xs text-center" style="margin-top: -20px;">
			<img class="mt-xs img-responsive" style="margin: auto; max-height: 130px;" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGridEM[0].nombre_foto }}" />
		</div>
		<div class="col-md-12 pt" style="border-top: 1px solid #e5e5e5;">
			<div class="row"> 
				<div class="form-group col-sm-5 col-xs-12 mb-md">
					<label class="control-label mb-n"> FECHA DESDE </label>
					<div class="input-group">
						<input type="text" class="form-control input-sm mask" ng-model="fBusquedaMC.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
	                    <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaMC.desdeHora" style="width: 17%; margin-left: 4px;" />
	                    <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaMC.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                    </div>
				</div>
				<div class="form-group col-sm-5 col-xs-12 mb-md">
					<label class="control-label mb-n"> FECHA HASTA </label>
					<div class="input-group">
						<input type="text" class="form-control input-sm mask" ng-model="fBusquedaMC.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
						<input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusquedaMC.hastaHora" style="width: 17%; margin-left: 4px;" />
		                <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusquedaMC.hastaMinuto" style="width: 17%; margin-left: 4px;" />
		            </div>
				</div>
				<div class="form-group col-sm-2"> 
					<button type="button" class="btn btn-info mt-md" ng-click="getPaginationServerSideMC();"> 
	                    <i class="ti ti-reload"> </i> PROCESAR 
	                </button>
	            </div>
			</div>
		</div>
		<div class="col-md-12">
			<div ui-grid="gridOptionsMC" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsMC.data.length"> No se encontraron datos. </div> 
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-general'); $event.preventDefault();"> <i class="ti ti-timer"></i> HORARIO GENERAL</button>
	<button type="button" class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-especial'); $event.preventDefault();"> <i class="fa fa-calendar-o"></i> HORARIO ESPECIAL</button>
    <button type="button" class="btn btn-success" ng-click="aceptar();">ACTUALIZAR MARCACIÓN</button>
    <button type="button" class="btn btn-warning" ng-click="cancel();">SALIR</button>
</div>