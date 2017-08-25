<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormDetalle }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Usuario: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fCabecera.usuario }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Caja: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fCabecera.numero_caja }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Fecha Apertura: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fCabecera.fecha_apertura }} </p> 
		</div>
		<div class="form-group mb-md col-md-12">
			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                <!-- <li class="pull-right" ng-if="mySelectionDetalleVentaGrid.length == 1" ><button type="button" class="btn btn-warning" ng-click='btnSolicitudImprimirTicket(mySelectionDetalleVentaGrid[0]);'> <i class="fa fa-share"> </i> Solicitar Re-impresión </button></li>  -->
                <li class="pull-right" ng-if="mySelectionDetalleVentaGrid.length == 1" ><button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionDetalleVentaGrid[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button></li> 
                <!-- <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>-->
                <!-- <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo();'>Ver Detalle</button></li>  -->
            </ul> 
			<!-- <label class="control-label"> Detalle </label> -->
			<div ui-grid="gridOptionsDetalleCaja" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
		</div> 
		<div class="col-xs-12">
            <div class="text-right">
              <h2 class="mt-n"> TOTAL DE CAJA <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumTotalV }} </strong> </h2>
            </div>
        </div> 
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>