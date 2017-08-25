<div class="modal-header pt-md pb-md">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body pt-sm">
    <form class="row"> 
		<div class="form-group col-md-12 mb-md">
			<label class="control-label mb-n">Grupo: </label>
			<h4 class="text-info mt-xs mb-n"> {{ mySelectionGrid[0].nombre }} </h4>
		</div>
		<div class="col-md-6 col-sm-12"> 
			<div class="col-md-5 mb-sm" style="padding:0 !important;">
				Elija el Rol para agregar al Grupo:  
			</div>
			<div class="col-md-3 mb-sm">
				<label class="col-md-6 pull-right">
					Modulo:
				</label>
			</div>
			<div class="col-md-4 mb-sm pr-n">
			    <select class="form-control input-sm " ng-model="fDataRol.idmodulo" ng-change="getPaginationRolesServerSide(fDataRol.idmodulo);" ng-options="item.idmodulo as item.descripcion_mod for item in listaModulo" required tabindex="102"> </select> 
			</div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsRoles" ui-grid-pagination ui-grid-selection ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div> 
		<div class="col-md-6 col-sm-12"> 
			<div class="col-md-5 mb-sm" style="padding:0 !important;">
				Roles de Grupo:  
			</div>
			<div class="col-md-3 mb-sm">
				<label class="col-md-6 pull-right">
					Modulo:
				</label>
			</div>
			<div class="col-md-4 mb-sm pr-n">
			    <select class="form-control input-sm " ng-model="fDataRol.idmoduloAdd" ng-change="getPaginationRolesAddServerSide(fDataRol.idmoduloAdd);" ng-options="item.idmodulo as item.descripcion_mod for item in listaModuloAdd" required tabindex="102"> </select> 
			</div>
			<div class="col-md-12 p-n" ui-grid="gridOptionsAddRoles" ui-grid-pagination ui-grid-selection ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>-->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>