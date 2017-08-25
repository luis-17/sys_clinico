<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
  	<div class="modal-title">
		<h5 class="modal-title text-danger">{{ titleMedicamento}}</h5>
   	</div>
    <form class="row"> 
		<div class="form-group mb-md col-md-6 col-sm-12"> 
			<div class="form-inline col-md-12 mt-sm mb-sm" style="padding:0 !important;">
				Elija el Principio Activo para agregar al Medicamento:  
			</div>

			<div ui-grid="gridOptionsMedicamentos" ui-grid-pagination ui-grid-resize-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
		<div class="form-group mb-md col-md-6 col-sm-12"> 
			<div class="form-inline col-md-12 mt-sm mb-sm" style="padding:0 !important;">
				Principios Activos del Medicamento:  
			</div>

			<div ui-grid="gridOptionsAddMedicamento" ui-grid-resize-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();"> AGREGAR TODOS </button>-->
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>