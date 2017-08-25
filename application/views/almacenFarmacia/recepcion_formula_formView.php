<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formRecepcionFormula"> 
		<div class="form-group mb-md col-md-6">
			<label class="control-label mb-xs">	Guia de Remisión <small class="text-danger">(*)</small> </label>
			<input tabindex="100" type="text" class="form-control input-sm " ng-model="fDataEntrada.guia" placeholder="Registre la guia de Remisión" required focus-me />
		</div>
		<div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" >
            <label class="control-label mb-xs"> Fecha de Recepción <small class="text-danger">(*)</small></label>
            <input tabindex="101" type="text" class="form-control input-sm mask" ng-model="fDataEntrada.fecha_recepcion" data-inputmask="'alias': 'dd-mm-yyyy'" required ng-pattern="pFecha"/>
        </div>
        <div class="form-group mb-xs col-md-3 col-sm-3 pl-xs" >
            <label class="control-label mb-xs" style="display: block"> Hora <small class="text-danger">(*)</small></label>
            <!-- <div class="form-group mb-xs col-md-12 col-sm-12 pl-xs" > -->
	            <input tabindex="115" type="text" class="form-control input-sm" ng-model="fDataEntrada.hora" style="width: 30%; display: inline-block" required ng-pattern="pHora"/>
	            <input tabindex="116" type="text" class="form-control input-sm" ng-model="fDataEntrada.minuto" style="width: 30%; margin-left: 4px; display: inline-block" required ng-pattern="pMinuto"/>
            <!-- </div> -->
        </div>
		
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formRecepcionFormula.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>