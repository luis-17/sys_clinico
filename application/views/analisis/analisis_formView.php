<link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formAnalisis"> 
		
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs"> Sección <small class="text-danger">(*)</small> </label>
			<select class="form-control input-sm" ng-model="fData.idseccion" ng-options="item.id as item.descripcion for item in listaSeccion" required tabindex="1"> </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Análisis <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Análisis" tabindex="2" required />
		</div>
		<div class="form-group mb-md col-md-2">
			<label class="control-label mb-xs"> Abreviatura </label>
			<input type="text" class="form-control input-sm" ng-model="fData.abreviatura" placeholder="Registre abreviatura" tabindex="3"  />
		</div>
		<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs"> Método </small> </label>
			<select class="form-control input-sm" ng-model="fData.idmetodo" ng-options="item.id as item.descripcion for item in listaMetodo" tabindex="4"> </select>
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Asignar un Producto al Análisis</label>
			<div class="input-group">
				<span class="input-group-btn ">
					<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idproductomaster" placeholder="ID" min-length="4" tabindex="5" disabled/>
				</span>
				<input id="fDataproducto" type="text" class="form-control input-sm" ng-model="fData.producto" placeholder="Ingrese el Producto o Click en Seleccionar" typeahead-loading="loadingLocationsPdto" uib-typeahead="item as item.descripcion for item in getProductoAutocomplete($viewValue)" typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" ng-change="limpiaId()" tabindex="6" autocomplete ="off"/>
				<span class="input-group-btn">
					<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaPdtos('md')">Seleccionar</button>
				</span>
			</div>
			<div style="min-height:20px">
              	<i ng-show="loadingLocationsPdto" class="fa fa-refresh"></i>
	            <div ng-show="noResultsLP && !loadingLocationsPdto">
	              <i class="fa fa-remove"></i> No se encontró resultados 
	            </div>
            </div>
		</div>
		<div class="form-group mb-md col-md-2" ng-if="false">
			<label class="control-label mb-xs"> ¿Tiene sub-Analisis? </label>
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-red" style="position: relative;">
						<input icheck="minimal-red" type="radio" id="inlineradio1" value="0" ng-model="fData.subanalisis">
					</div> No
				</label>
			</div>
			
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-blue" style="position: relative;">
						<input icheck="minimal-blue" type="radio" id="inlineradio2" value="1" ng-model="fData.subanalisis">
					</div> Si
				</label>
			</div>
		</div>

		<!-- <div class="form-group mb-md col-md-6" ng-if="fData.subanalisis == 1">
			<label class="control-label mb-xs"> Sub - Análisis <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.parametro" placeholder="Registre SubAnálisis" tabindex="10" required />
		</div> -->

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAnalisis.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>