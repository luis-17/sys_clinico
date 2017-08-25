<link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formParametro"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs"> Parámetro <small class="text-danger">(*)</small> </label>
			<input type="text" class="form-control input-sm" ng-model="fData.descripcion" placeholder="Registre Parámetro" tabindex="1" focus-me required />
		</div>
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs block"> Agrupador <i class="fa fa-question-circle" tooltip="Si el parámetro contendrá subparámetros"></i></label>
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-red" style="position: relative;">
						<input icheck="minimal-red" type="radio" id="inlineradio1" value="0" ng-model="fData.separador">
					</div> No
				</label>
			</div>
			
			<div class="radio-inline icheck">
				<label class="icheck-label">
					<div class="iradio_minimal-blue" style="position: relative;">
						<input icheck="minimal-blue" type="radio" id="inlineradio2" value="1" ng-model="fData.separador">
					</div> Si
				</label>
			</div>
		</div>
		<div class="form-group mb-md col-md-12" ng-show="fSessionCI.idsedeempresaadmin == '9' && fData.separador == 0">
			<fieldset class="row" >
				<legend class="col-lg-12 pb-n mb-md lead"> Valores Normales </legend>
				<div class="form-group mb-xs col-md-1 col-sm-6 pr-n">
	                <label class="control-label mb-xs"> Desde </label>
	                <input id="temporalDesde" type="number" class="form-control input-sm" ng-model="fData.temporal.desde" tabindex="10" placeholder="Nº"  ng-change=""/> 
	            </div>
	            <div class="form-group mb-xs col-md-1 col-sm-6 pr-n">
	                <label class="control-label mb-xs"> Hasta </label>
	                <input id="temporalHasta" type="number" class="form-control input-sm" ng-model="fData.temporal.hasta" tabindex="11" placeholder="Nº"  ng-change=""/> 
	            </div>
	            <div class="form-group mb-xs col-md-2 col-sm-6">
	                <label class="control-label mb-xs"> Und. Tiempo</label>
	                <select class="form-control input-sm" ng-model="fData.temporal.tipo_rango" ng-options="item as item.descripcion for item in listadoUnidadTiempo" tabindex="" ng-change=""> </select> 
		        </div>
				<div class="form-group mb-xs col-md-3 col-sm-6 pr-n">
	                <label class="control-label mb-xs"> Valor Normal Hombre </label>
	                <input type="text" class="form-control input-sm" ng-model="fData.temporal.valor_etario_h" tabindex="12" placeholder="Valor Normal"  ng-change=""/>
	            </div>
				<div class="form-group mb-xs col-md-3 col-sm-6 pr-n">
	                <label class="control-label mb-xs"> Valor Normal Mujer </label>
	                <input type="text" class="form-control input-sm" ng-model="fData.temporal.valor_etario_m" tabindex="13" placeholder="Valor Normal"  ng-change=""/> 
	            </div>
	            <div class="form-group mb-sm mt-md col-md-1 col-sm-12"> 
	              	<div class="btn-group" style="min-width: 100%">
		                <a href="" class="btn btn-info-alt" ng-click="agregarValorEtario(); $event.preventDefault();" style="min-width: 80%;"  tabindex="115">Agregar</a>
	              	</div>
	              	<!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
	            </div>
				<div class="form-group col-xs-12 m-n">
	              <label class="control-label m-n">Agregar a Valores: </label>
					<div ui-grid="gridOptionsValores" ui-grid-pagination ui-grid-resize-columns ui-grid-auto-resize ui-grid-edit class="grid table-responsive"></div>
				</div>
			</fieldset>
		</div>
		<div class="well well-transparent boxDark col-xs-12 m-n" ng-show="fSessionCI.idsedeempresaadmin != '9' ">
			<div class="row">
		

	    		<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Valor Normal - Hombres  </label>
					<textarea class="form-control input-sm" ng-model="fData.valorNormalHombres" placeholder="" ng-disabled="fData.separador == 1" >
						
					</textarea>
				</div>
				<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Valor Normal - Mujeres  </label>
					<textarea class="form-control input-sm" ng-model="fData.valorNormalMujeres" placeholder="" ng-disabled="fData.separador == 1"  >
						
					</textarea>
				</div>
			</div>

			<div class="row col-sm-6 icheck-minimal">
		    	<div class="checkbox icheck">
		    		<label class="icheck-label">
		    			<div class="icheckbox_minimal-blue">
		    				<input icheck="minimal-blue" ng-model="fData.valorAmbos.bool" type="checkbox"  ng-true-value="'1'" ng-false-value="'0'" ng-disabled="fData.separador == 1"/>
		    			</div> Ambos Sexos
		    		</label>
		    		
		    	</div>
		    </div>
		</div>
    	
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formParametro.$invalid">GUARDAR Y SALIR</button>
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>