<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form name="formCajaChica">
    	<div class="row">            
            <div class="form-group mb-md col-md-4"> 
              <label class="m-n"><h4 class="m-n">EMPRESA - SEDE</h4></label> 
              <div class="input-group block"> 
                <p class="text-info m-n">{{fData.empresa}}</p>
                <p class="text-info m-n">{{fData.sede}}</p>
               </div>
            </div>
            <div class="form-group mb-md col-md-4"> 
              <label class="m-n"><h4 class="m-n">CENTRO DE COSTO</h4></label> 
              <div class="input-group block"> 
                <p class="text-info m-n">{{fData.codigo_cc}}</p>
                <p class="text-info m-n">{{fData.centro_costo}}</p>
               </div>
            </div>
            <div class="form-group mb-md col-md-4"> 
              <label class="m-n"><h4 class="m-n"> CAJA </h4></label> 
              <div class="input-group block"> 
                <p class="text-info m-n">{{fData.descripcion}}</p>
               </div>
            </div>
    	</div>
    	<div class="row">
    		<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> N° de Cheque <small class="text-danger">(*)</small> </label> 
				<input type="text" class="form-control input-sm" required ng-model="fData.numero_cheque" placeholder="N° de Cheque"   tabindex="3" />
			</div>
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Monto de cheque <small class="text-danger">(*)</small> </label>
				<div class="input-group">
					<span class="input-group-addon input-sm">S/.</span>
					<input type="text" class="form-control input-sm" required ng-model="fData.monto_cheque" placeholder="Monto de cheque" ng-change="calcularMontoInicio();" tabindex="4" />
				</div>
			</div>
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Saldo anterior <small class="text-danger">(*)</small>  </label>
				<div class="input-group">
					<span class="input-group-addon input-sm">S/.</span> 
					<input type="text" class="form-control input-sm" required ng-model="fData.saldo_anterior" placeholder="Saldo anterior" ng-readonly="true"  tabindex="5" /> 

				</div>
			</div>			
			<div class="form-group mb-md col-md-3"> 
				<label class="control-label mb-xs"> Monto de inicio <small class="text-danger">(*)</small> </label>
				<div class="input-group">
					<span class="input-group-addon input-sm">S/.</span>
					<input type="text" class="form-control input-sm" ng-model="fData.monto_inicial" placeholder="Monto inicial" ng-readonly="true" tabindex="6" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-12 mb-n">
				<label class="control-label mb-n">Responsable: </label> 
			</div>
			<div class="form-group col-md-2 mb-n pr-n">
				<input type="text" class="form-control input-sm" ng-model="fData.idresponsable" placeholder="Id" readonly="true"  />
			</div>
			<div class="form-group col-md-6 mb-n pl-n">
				<input type="text" ng-model="fData.responsable" class="form-control input-sm" focus-me autocomplete="off"
					placeholder="Digite el responsable para autocompletar" 
					typeahead-loading="loadingUsuarios"  tabindex="7"
					uib-typeahead="item as item.descripcion for item in getResponsableAutocomplete($viewValue)" 
					typeahead-min-length="2" 
					typeahead-on-select="getSelectedResponsable($item, $model, $label)"
					ng-change="fData.idresponsable = null" /> 
				<i ng-show="loadingUsuarios" class="fa fa-refresh"></i>
				<div ng-show="noResultsLM">
					<i class="fa fa-remove"></i> No se encontró resultados 
				</div> 
			</div>
			<!-- <div class="form-group mb-md col-md-12">
				<label class="control-label mb-xs"> Observaciones/Comentarios </label> 
				<textarea class="form-control input-sm" ng-model="fData.observaciones_acc" placeholder='Observaciones/Comentarios' tabindex="7" >  </textarea>
			</div> -->
    	</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formcajachica.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>