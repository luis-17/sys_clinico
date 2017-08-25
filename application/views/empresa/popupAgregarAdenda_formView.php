<style type="text/css">
	/*.modal{z-index: 9999999999!important;}*/
	.grid {height: 310px !important;}
</style>

<div class="modal-header pt-sm pb-sm">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
		<div class="row col-md-12">
			<div>
				<button class="btn btn-sm btn-success pull-right ml-xs mr-md" ng-show="adendas.btnedit" ng-click="btnNewAdenda();$event.preventDefault();"><i class="fa fa-file-o"></i> Nuevo</button>
				<button ng-show="mySelectionAdendaGrid.length == 1" class="btn btn-sm btn-warning pull-right ml-xs" ng-click="btnEditarAdenda(); $event.preventDefault();"><i class="fa fa-edit"></i> Editar</button>
				<button ng-show="mySelectionAdendaGrid.length > 0 && adendas.btnedit" class="btn btn-sm btn-danger pull-right ml-xs" ng-click="btnAnularAdenda();$event.preventDefault();"><i class="fa fa-trash-o"></i> Anular</button>				
			</div>
		</div>
		<div class="row col-md-12">
				<div class="col-md-5">
					<div ng-class="adendas.classEditPanel">
						<div class="form-group col-md-12 mb-md">
							<label class="control-label mb-n"> Fecha Fin Contrato: <small class="text text-danger">(*)</small></label>
							<input tabindex="110" type="text" ng-disabled="adendas.edit" class="form-control input-sm mask" ng-model="fAdenda.fecha_fin" style="width: 45%;" data-inputmask="'alias': 'dd-mm-yyyy'"/>
						</div>
						<div class="form-group col-md-12 mb-md" >
							<label class="control-label mb-n"> Condiciones: </label>		
							<textarea rows="9" class="form-control col-md-12" ng-disabled="adendas.edit" ng-model="fAdenda.condiciones" placeholder="Agregue Condiciones de la adenda"></textarea>
						</div> 
						<div class="form-group col-md-12" style="text-align:center;"> 
							<button ng-disabled="adendas.edit" type="button" class="btn btn-warning" style="width: 49%;" ng-click="salirActualizarAdenda();$event.preventDefault();"> CANCELAR </button>				
							<button ng-if="!adendas.editarAdendaBool"  ng-disabled="adendas.edit" type="button" class="btn btn-success" style="width: 49%;" ng-click="agregarAdenda();$event.preventDefault();"> AGREGAR >>> </button>
							<button ng-if="adendas.editarAdendaBool" ng-disabled="adendas.edit" type="button" class="btn btn-success" style="width: 49%;" ng-click="actualizarAdenda();$event.preventDefault();"> ACTUALIZAR >>> </button>
												
						</div>
					</div>						
				</div>
				<div class="col-md-7">
					<label class="control-label"> Adendas del Contrato: </label>
					<div ui-grid="gridOptionsAdendas" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>					
				</div>					
		</div>
	</form>
</div>
<div class="modal-footer pt-sm pb-sm mr-md">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button>-->
    <button class="btn btn-warning mr-md" ng-click="cancel()">Salir</button>
</div>