<link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formParametros">
    	<div class="col-md-12">
    		<div class="form-group mb-md col-md-12">
				<div class="radio-inline icheck">
					<label class="icheck-label">
						<div class="iradio_minimal-red" style="position: relative;">
							<input icheck="minimal-red" type="radio" id="inlineradio1" value="0" ng-model="fData.separador" ng-change="limpiaSeleccion()">
						</div> Parámetro
					</label>
				</div>
				
				<div class="radio-inline icheck">
					<label class="icheck-label">
						<div class="iradio_minimal-blue" style="position: relative;">
							<input icheck="minimal-blue" type="radio" id="inlineradio2" value="1" ng-model="fData.separador" ng-change="limpiaSeleccion()">
						</div> Agrupador
					</label>
				</div>
			</div>
    		<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs" ng-if="fData.separador == 0"> Elegir parámetro</label>
				<label class="control-label mb-xs" ng-if="fData.separador == 1"> Elegir agrupador</label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idparametro" placeholder="ID" min-length="4" disabled required/>
					</span>
					<input id="fDataParametro" type="text" class="form-control input-sm" ng-model="fData.parametro" placeholder="Ingrese el Parametro o Click en Seleccionar" typeahead-loading="loadingLocationsParametro" uib-typeahead="item as item.descripcion for item in getParametroAutocomplete($viewValue)" typeahead-on-select="getSelectedParametro($item, $model, $label)" typeahead-min-length="2" ng-change="limpiaId()" tabindex="1" focus-me autocomplete ="off"/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaParametro('md')" tabindex="2" title="Buscar" ><i class="fa  fa-search"></i></button>
					</span>
				</div>
				<div style="min-height:20px">
	              	<i ng-show="loadingLocationsParametro" class="fa fa-refresh"></i>
		            <div ng-show="noResultsLP && !loadingLocationsParametro">
		              <i class="fa fa-remove"></i> No se encontró resultados 
		            </div>
	            </div>
			</div>
			<div class="form-group mb-sm mt-lg col-md-2 col-sm-12"> 
                <input type="button" class="btn btn-info col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" ng-disabled="formParametros.$invalid" tabindex="110" value="Agregar" /> 
            </div>
    	</div>
    	<div class="col-md-12">
    		<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"> Valor Normal - Hombres  </label>
				<textarea class="form-control input-sm" ng-model="fData.valorNormalHombres" placeholder="" disabled >
					
				</textarea>
			</div>
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"> Valor Normal - Mujeres  </label>
				<textarea class="form-control input-sm" ng-model="fData.valorNormalMujeres" placeholder="" disabled >
					
				</textarea>
			</div>
    	</div>
		<div class="well well-transparent boxDark col-xs-12 m-n">
			<div class="row">
				 <div class="form-group col-xs-12 m-n">
                    <label class="control-label">Sub - Análisis y parámetros: </label>
                        <div ui-if="gridOptionsPar.data.length>0" 
                        ui-grid="gridOptionsPar" ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsPar.data.length==0">Guardar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>