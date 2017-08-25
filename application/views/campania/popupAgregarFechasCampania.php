<style type="text/css">
    .modal{z-index: 9999999999!important; }
    .modal-content .fechas {background-color : #FFFBE8;}
</style>
<div class="modal-content fechas">
	<div class="modal-header">
		<h4 class="modal-title"> {{ titleForm }}  </h4>
	</div>
	<div class="modal-body">
	    <form class="row" name="formHorarioEspecial"> 
			<div class="col-md-12">

				<div class="col-xs-6 p-n text-center">
					<label class="control-label mb-md text-primary"><i class="fa fa-database"></i><strong> FECHAS DE VENTA </strong></label> 
					<uib-datepicker class="date-table" style="margin-left:55px;" starting-day="1" show-weeks="false" ng-model='fData.activeDateV' multi-select='fDataTemp.temporal.arrFechasVen' select-range='false' ></uib-datepicker>
				</div>
				<div class="col-xs-6 p-n text-center">
					<label class="control-label mb-md text-primary"><i class="fa fa-edit"></i><strong> FECHAS DE ATENCION </strong></label> 
					<uib-datepicker class="date-table" style="margin-left:55px;" starting-day="1" show-weeks="false" ng-model='fData.activeDateA' multi-select='fDataTemp.temporal.arrFechasAte' select-range='false' ></uib-datepicker>
				</div>
				 
			</div>
		</form>
	</div>
	<div class="modal-footer">
	    <button class="btn btn-primary" ng-click="aceptarfechas(); $event.preventDefault();" ng-disabled="formHorarioEspecial.$invalid">ACEPTAR</button>
	    <button class="btn btn-warning" ng-click="cancel();">SALIR</button>
	</div>
</div>