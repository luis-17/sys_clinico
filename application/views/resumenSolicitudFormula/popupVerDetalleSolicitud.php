<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">

		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> N° SOLICITUD: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDetalle.idsolicitudformula }} </p> 
		</div>
		
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> ENCARGADO: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDetalle.encargado }} </p> 
		</div>
		
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Paciente: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDetalle.paciente }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fDetalle.fecha_solicitud }} </p> 
		</div>
		<div class="form-group mb-md col-xs-12">
			<!--<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li> 
            </ul>-->
			<div ui-grid="gridOptionsDetalleSolicitud" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns class="grid table-responsive"></div> 
		</div>
	</div>
</div> 
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div> 