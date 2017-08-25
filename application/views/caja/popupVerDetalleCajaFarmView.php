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
		<div class="form-group m-n col-md-12">
			<ul class="form-group demo-btns">
                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                <li class="pull-right" ng-if="mySelectionDetalleVentaGrid.length == 1" > 
                	<button type="button" class="btn btn-success" ng-click='btnImprimirTicket(mySelectionDetalleVentaGrid[0]);'> <i class="fa fa-print"> </i> Imprimir Ticket</button>
                </li> 
            </ul> 
			<!-- <label class="control-label"> Detalle </label> -->
			<div ui-grid="gridOptionsDetalleCaja" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
		</div> 
		<div class="col-md-2 col-xs-12"> </div>
        <div class="col-md-2 col-xs-12">
            <div class="text-center">
              <h5 class="well well-sm"> CANT. VENTAS <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumCantV }} </strong> </h5>
            </div>
        </div>
        <div class="col-md-2 col-xs-12">
            <div class="text-center">
              <h5 class="well well-sm"> CANT. ANULADOS <strong style="font-weight: 400;" class="text-danger"> : {{ gridOptionsDetalleCaja.sumCantA }} </strong> </h5>
            </div>
        </div>
        <div class="col-md-2 col-xs-12">
            <div class="text-center">
              <h5 class="well well-sm"> CANT. N.C.R. <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumCantNC }} </strong> </h5> 
            </div>
        </div>
        <div class="col-md-2 col-xs-12">
            <div class="text-center">
              <h5 class="well well-sm"> TOTAL N.C.R. <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumTotalNC }} </strong> </h5>
            </div>
        </div>
        <div class="col-md-2 col-xs-12">
            <div class="text-center">
              <h5 class="well well-sm"> TOTAL DE CAJA <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumTotalV }} </strong> </h5>
            </div>
        </div>
		<!-- <div class="col-xs-12">
            <div class="text-right">
              <h2 class="mt-n"> TOTAL DE CAJA <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsDetalleCaja.sumTotalV }} </strong> </h2>
            </div>
        </div>  -->
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>