<div class="modal-content">
	<div class="modal-header">
		<h4 class="modal-title"> {{ titleForm }}  </h4>
	</div>
	<div class="modal-body">
		<form class="row" name="formFeriado">
			<div class="col-md-6">
				
				<uib-datepicker class="date-table" ng-model='fData.feriadoDate' multi-select='fData.temporal.arrFechas' select-range='false'
					ng-change="selectedDates();" 
					min-date="optionsDP.minDate"
					max-date="optionsDP.maxDate"
	                show-weeks="optionsDP.showWeeks"
	                max-mode="optionsDP.maxMode"
	                starting-day="optionsDP.startingDay"
	                date-disabled="disabled(date, mode)"
				></uib-datepicker>
				
			</div>
			<div class="col-md-6" style="height:280px;overflow-y:auto">
				<label class="control-label mb-xs"> FERIADOS </label> 
				<div class="col-md-12 input-group" ng-repeat="fecha in fData.temporal.arrFeriados">
					<input type="text" ng-model="fecha" data-inputmask="'alias': 'dd-mm-yyyy'" class="input-sm mb" readonly="true" />
					<button class="btn btn-danger btn-sm" type="button" ng-click="eliminarFeriado($index)"><i class="fa fa-trash"></i></button>
				</div> 
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formFeriado.$invalid">Aceptar</button>
	    <button class="btn btn-warning" ng-click="cancel();">Cancelar</button>
	</div>

</div>