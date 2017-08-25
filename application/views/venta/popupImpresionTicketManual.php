<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
        <ul class="form-group demo-btns col-xs-12 ml">
        	<li class="form-group mr mt-sm col-sm-12 p-n"> <label> Fecha de Venta </label> 
              <div class="input-group" style="width: 230px;"> 
                <input id="fechaVenta" tabindex="1" type="text" class="form-control input-sm mask" ng-model="fVenta.fecha_venta" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                <input tabindex="2" type="text" class="form-control input-sm" ng-model="fVenta.hora_venta" style="width: 45px; margin-left: 4px;" />
                <input tabindex="2" type="text" class="form-control input-sm" ng-model="fVenta.minuto_venta" style="width: 45px; margin-left: 4px;" />

              </div>
            </li>
        </ul>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" tabindex="3"> IMPRIMIR </button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="4">SALIR</button>
</div>