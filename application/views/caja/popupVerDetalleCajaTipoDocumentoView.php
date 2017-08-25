<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormDetalleTD }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Usuario: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGrid[0].usuario }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Caja: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGrid[0].numero_caja }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Fecha Apertura: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ mySelectionGrid[0].fecha_apertura }} </p> 
		</div>
		<div class="form-group mb-md col-md-12">
			<ul class="form-group demo-btns">
            </ul> 
			<!-- <label class="control-label"> Detalle </label> -->
			<div ui-grid="gridOptionsPopUpTXTD" ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
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
    <button class="btn btn-warning" ng-click="cancel();">Salir</button>
</div>