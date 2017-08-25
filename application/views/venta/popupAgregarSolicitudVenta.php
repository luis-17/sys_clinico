<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAddSolicitud }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="form-group col-md-4 mb-xs">
			<label class="control-label mb-n"> PACIENTE: </label> 
			<p class="help-block mt-xs"> {{ fDataVenta.cliente.nombres }} {{ fDataVenta.cliente.apellidos }} </p> 
		</div>
		<div class="form-group col-md-4 mb-xs"> 
			<label class="control-label mb-n"> Seleccione Tipo Solicitud </label> 
			<select class="form-control input-sm" ng-model="fDataSolicitud.tipoSolicitud" ng-change="getPaginationDETSOLServerSide(); $event.preventDefault();" 
				ng-options="item as item.descripcion for item in listaProductosSolicitud" required > </select> 
		</div>
		<div class="form-inline col-md-12 mt-sm">
			<label class="control-label">Seleccione Productos de las Solicitudes: </label> 
		</div>
		<div class="form-group mb-md col-md-12"> 
			<div ui-grid="gridOptionsDETSOL" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
			<!-- <div ui-grid="gridOptionsDETSOL" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div>  -->
		</div> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();"> AGREGAR </button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>