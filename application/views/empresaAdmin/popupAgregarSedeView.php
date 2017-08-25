<div class="modal-header pt-md pb-md">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body pt-sm">
    <form class="row"> 
		<div class="form-group col-md-12 mb-md">
			<label class="control-label mb-n">Empresa Administradora: </label>
			<h4 class="text-info mt-xs mb-n"> {{ mySelectionGrid[0].razon_social }} </h4>
		</div>
		<div class="col-md-6 col-sm-12"> 
			<div class="col-md-12 mb-sm" style="padding:0 !important;">
				Elija la Sede para agregar a la Empresa Administradora:  
			</div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsSedes" ui-grid-pagination ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
		<div class="col-md-6 col-sm-12"> 
			<div class="col-md-5 mb-sm" style="padding:0 !important;">
				Sedes asignadas:  
			</div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsAddSedes" ui-grid-pagination ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>-->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>