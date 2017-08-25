<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title" ng-bind-html ="titleForm"></h4>
</div>
<div class="modal-body">
    <form class="row" name="formAnalisis">
    	<div class="col-md-12">
    		<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"> Elegir Análisis</label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idanalisis" placeholder="ID" min-length="4" disabled required/>
					</span>
					<input id="fDataAnalisis" type="text" class="form-control input-sm" ng-model="fData.analisis" placeholder="Ingrese el analisis o Click en Seleccionar" typeahead-loading="loadingLocationsAnalisis" uib-typeahead="item as item.descripcion for item in getAnalisisAutocomplete($viewValue)" typeahead-on-select="getSelectedAnalisis($item, $model, $label)" typeahead-min-length="2" ng-change="limpiaId()" tabindex="1" focus-me autocomplete ="off"/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaAnalisis('md')" tabindex="2" title="Buscar" ><i class="fa  fa-search"></i></button>
					</span>
				</div>
				<div style="min-height:20px">
	              	<i ng-show="loadingLocationsAnalisis" class="fa fa-refresh"></i>
		            <div ng-show="noResultsLA && !loadingLocationsAnalisis">
		              <i class="fa fa-remove"></i> No se encontró resultados 
		            </div>
	            </div>
			</div>
			<div class="form-group mb-sm mt-lg col-md-2 col-sm-12"> 
                <input type="button" class="btn btn-info col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" ng-disabled="formAnalisis.$invalid" tabindex="110" value="Agregar" /> 
            </div>
    	</div>
    	
		<div class="well well-transparent boxDark col-xs-12 m-n">
			<div class="row">
				 <div class="form-group col-xs-12 m-n">
                    <label class="control-label">Análisis: </label>
                        <div ui-if="gridOptionsAnal.data.length>0" 
                        ui-grid="gridOptionsAnal" ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsAnal.data.length==0">Guardar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>