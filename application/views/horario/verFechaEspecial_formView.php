<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formHorarioGeneral"> 
    	<div class="col-xs-8">
    		<div class="row">
    			<!-- <div class="col-xs-12"> -->
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
    			<!-- </div> -->
    		</div>
    	</div>
    	<!--  -->
		<div class="form-group col-xs-4 mb-xs text-center" style="margin-top: -20px;">
			<img class="mt-xs img-responsive" style="margin: auto;" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGridEM[0].nombre_foto }}" />
		</div>
		<div class="col-md-12 pt" style="border-top: 1px solid #e5e5e5;">
			<div class="row group-labels"> 
				<div class="form-group col-xs-12 mb-md"> 
					<h1 class="" style="text-align: center;"> {{ mySelectionGridHE[0].fecha_especial_formato }} 
						<small class="block"> {{ mySelectionGridHE[0].motivo }} </small> 
					</h1>
				</div>
				<div class="form-group col-xs-3 mb-md">
					<label class="control-label mb-n"> ASISTENCIA </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].comentario }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n"> ENTRADA DESDE </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].desde_entrada }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n" style="font-weight: bold; color:black;"> ENTRADA </label>
					<p class="help-block mt-xs" style="color:black;"> {{ mySelectionGridHE[0].entrada }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n"> ENTRADA HASTA </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].hasta_entrada }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md">
					<label class="control-label mb-n"> HORAS TRABAJADAS </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].horas_trabajadas }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n"> SALIDA DESDE </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].desde_salida }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n" style="font-weight: bold; color:black;"> SALIDA </label>
					<p class="help-block mt-xs" style="color:black;"> {{ mySelectionGridHE[0].salida }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n"> SALIDA HASTA </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].hasta_salida }} </p>
				</div>
				<div class="form-group col-xs-3 mb-md" ng-show="mySelectionGridHE[0].si_licencia == '2'">
					<label class="control-label mb-n"> TOLERANCIA </label>
					<p class="help-block mt-xs"> {{ mySelectionGridHE[0].tiempo_tolerancia }} </p>
				</div>
			</div>
		</div>
		
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-general'); $event.preventDefault();"> <i class="ti ti-timer"></i> HORARIO GENERAL</button>
	<button type="button" class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-especial'); $event.preventDefault();"> <i class="fa fa-calendar-o"></i> HORARIO ESPECIAL</button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>