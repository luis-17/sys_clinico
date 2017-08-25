<div class="modal-header">
	<h4 class="modal-title" ng-bind-html="titleForm"></h4>	
</div>
<div class="modal-body">
	<form class="row" name="formPacientesConvenio">
		<div class="col-md-8" style="margin-bottom: 10px;">
            <div class="input-group">
            	<span class="input-group-btn ">
            		<input type="text" placeholder="ID" class="form-control input-sm" ng-model="fDataAdd.idcliente" disabled style="width: 40px;" /> 
            	</span>
				<input type="text" placeholder="Paciente" class="form-control input-sm" ng-model="fDataAdd.cliente" typeahead-loading="loadingPaciente"
						uib-typeahead="item as item.paciente for item in getClienteNoAgregAutocomplete($viewValue)" 
						typeahead-on-select="getSelectedPaciente($item, $model, $label)" typeahead-min-length="2" 
						tabindex="104" ng-change="fDataAdd.idcliente = null; noResultsLCliente = false" />
				<span class="input-group-btn">
					<button class="btn btn-success btn-sm" type="button" ng-click="btnAgregarClienteConvenio();"><i class="fa  fa-plus"></i> Agregar</button>
				</span>	
				<i ng-show="loadingPaciente" class="fa fa-refresh"></i>
				<div ng-show="noResultsLCliente">
	              	<i class="fa fa-remove"></i> No se encontró resultados 
	            </div>
			</div>
		</div>
		<div class="col-md-4">
	        <button type="button" class="btn btn-primary btn-sm pull-right" ng-click='btnNuevoClienteConvenio();'>Nuevo</button>
		</div>
		<div class="col-md-12">
			<div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-selection ui-grid-edit ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button class="btn btn-warning " ng-click="cancel()">Cerrar</button>
</div>