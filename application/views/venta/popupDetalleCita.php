<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormCita }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="col-md-12 col-sm-12  mb-xs">
	    	<div class="row">
	    		<div class="form-group col-md-12 mb-xs">
					<label class="control-label mb-n">MÉDICO: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.medico }} </p> 
				</div>

				<!-- <div class="form-group col-md-12 mb-xs">
					<label class="control-label mb-n">ESPECIALIDAD: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.especialidad }} </p> 
				</div> -->

				<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n">FECHA: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.fecha_str }} </p> 
				</div>

				<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n">AMBIENTE: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.ambiente }} </p> 
				</div>

				<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n"> N° CUPO: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.numero_cupo }} </p> 
				</div>

				<div class="form-group col-md-12 mb-xs">
					<label class="control-label mb-n">TURNO: </label> 
					<p class="help-block mt-xs"> {{ rowCupo.turno }} </p> 
				</div>
			</div>
		</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancelDetCita();"> CERRAR </button>
</div>